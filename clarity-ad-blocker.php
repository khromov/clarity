<?php
/*
 * Plugin Name: Clarity - Ad blocker for WordPress
 * Plugin URI: https://github.com/khromov/wp-clarity
 * GitHub Plugin URI: khromov/clarity
 * Description: Remove nags and upsells from popular WordPress plugins.
 * Author:      khromov
 * Version:     1.4
 * Requires at least: 5.0
 * Tested up to: 6.7
 * Requires PHP: 7.0
 * Text Domain: clarity-ad-blocker
 * Domain Path: /languages/
 * License:     GPL v2 or later
 */

define('WP_CLARITY_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('CLARITY_AD_BLOCKER_ENABLED', true);

/**
 * Class WP_Clarity
 */
class WP_Clarity {
  /**
   * Option name for storing the definitions
   *
   * @var string
   */
  private $option_name = 'wp_clarity_definitions';

  /**
   * CRON hook name
   *
   * @var string
   */
  private $cron_hook = 'wp_clarity_update_definitions';

  /**
   * URL to the remote definitions file
   *
   * @var string
   */
  private $definitions_url = 'https://khromov.github.io/clarity/definitions.txt';

  /**
   * Constructor
   */
  function __construct() {
    // Register activation and deactivation hooks
    register_activation_hook(__FILE__, [$this, 'activate_plugin']);
    register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);

    // Core functionality
    add_action('admin_head', [$this, 'admin_head']);
    add_action('plugins_loaded', [$this, 'plugins_loaded']);
    add_action('after_setup_theme', [$this, 'themes_loaded']);

    // CRON event handler
    add_action($this->cron_hook, [$this, 'update_definitions_from_remote']);

    // Admin-related hooks
    add_action('admin_init', [$this, 'admin_init']);
    add_filter('plugin_action_links_clarity-ad-blocker/clarity-ad-blocker.php', [$this, 'filter_plugin_action_links']);
    
    // WP-CLI integration
    add_action('cli_init', [$this, 'cli_init']);
  }

  /**
   * Plugin activation hook
   *
   * @return void
   */
  function activate_plugin() {
    // Schedule the CRON job if not already scheduled
    if (!wp_next_scheduled($this->cron_hook)) {
      wp_schedule_event(time(), 'daily', $this->cron_hook);
    }
    
    // Force an initial update from remote
    $this->update_definitions_from_remote();
  }

  /**
   * Plugin deactivation hook
   *
   * @return void
   */
  function deactivate_plugin() {
    // Remove the CRON job
    wp_clear_scheduled_hook($this->cron_hook);
  }

  /**
   * Admin initialization
   *
   * @return void
   */
  function admin_init() {
    // Handle manual refresh of definitions
    if (isset($_GET['action']) && $_GET['action'] === 'clarity-refresh' && check_admin_referer('clarity_refresh')) {
      $success = $this->update_definitions_from_remote();
      
      // Redirect back to plugins page with status
      wp_redirect(add_query_arg(
        ['clarity-updated' => $success ? 'success' : 'error'],
        admin_url('plugins.php')
      ));
      exit;
    }
    
    // Show admin notice after definition refresh
    if (isset($_GET['clarity-updated'])) {
      add_action('admin_notices', function() {
        $status = $_GET['clarity-updated'];
        $class = $status === 'success' ? 'notice-success' : 'notice-error';
        $message = $status === 'success' 
          ? __('Clarity definitions updated successfully.', 'clarity-ad-blocker')
          : __('Failed to update Clarity definitions. Using local definitions.', 'clarity-ad-blocker');
          
        printf('<div class="notice %s is-dismissible"><p>%s</p></div>', esc_attr($class), esc_html($message));
      });
    }
  }

  /**
   * Get definitions from cache or local file
   *
   * @param bool $forceRefresh Force refresh from remote
   * @return string CSS selectors string
   */
  function getDefinitions($forceRefresh = false) {
    // If force refresh or no cached definitions exist
    if ($forceRefresh || false === ($cached = get_option($this->option_name))) {
      // Log info if debugging is enabled
      do_action('qm/info', 'No cached definitions found or refresh forced');
      
      // Try to update from remote
      $this->update_definitions_from_remote();
      
      // Get the potentially updated option
      $cached = get_option($this->option_name);
      
      // If still no cached definitions, fallback to local
      if (false === $cached) {
        do_action('qm/info', 'Using local definitions as fallback');
        return $this->getLocalDefinitions();
      }
      
      return $cached;
    }
    
    do_action('qm/info', 'Using cached definitions from database');
    return $cached;
  }

  /**
   * Get definitions from local file
   *
   * @return string CSS selectors string
   */
  function getLocalDefinitions() {
    do_action('qm/info', 'Loading definitions from local text file');
    
    $filterEmptyLines = function ($item) {
      return !!$item;
    };
    
    $filterComments = function ($item) {
      return trim(preg_replace('/(--.*)/', '', $item));
    };

    $rulesFile = explode("\n", file_get_contents(WP_CLARITY_PATH . 'definitions.txt'));
    
    return implode(', ', apply_filters('wp_clarity_rules', array_filter(array_filter($rulesFile, $filterComments), $filterEmptyLines)));
  }

  /**
   * Update definitions from remote source
   *
   * @return bool Success or failure
   */
  function update_definitions_from_remote() {
    do_action('qm/info', 'Attempting to fetch remote definitions');
    
    $response = wp_remote_get($this->definitions_url);
    
    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
      // If unable to fetch, use local definitions but don't update cache
      do_action('qm/info', 'Failed to fetch remote definitions');
      return false;
    }
    
    $content = wp_remote_retrieve_body($response);
    
    if (empty($content)) {
      do_action('qm/info', 'Remote definitions were empty');
      return false;
    }
    
    // Process the content
    $filterEmptyLines = function ($item) {
      return !!$item;
    };
    
    $filterComments = function ($item) {
      return trim(preg_replace('/(--.*)/', '', $item));
    };

    $rulesFile = explode("\n", $content);
    $processedDefinitions = implode(', ', apply_filters('wp_clarity_rules', array_filter(array_filter($rulesFile, $filterComments), $filterEmptyLines)));
    
      
    update_option($this->option_name, $processedDefinitions, false);
    
    do_action('qm/info', 'Updated remote definitions successfully');
    
    return true;
  }

  /**
   * Hides stuff via CSS in the admin header
   * 
   * @return void
   */
  function admin_head() {
    $selectors = $this->getDefinitions();
    if (strlen($selectors) === 0) return;
?>
    <!-- Clarity - Ad blocker for WordPress -->
    <style type="text/css">
      <?php echo $selectors; ?> {
        display: none !important;
      }
    </style>
<?php
  }

  /**
   * Special handling for plugins that can't rely on CSS rules
   *
   * @return void
   */
  function plugins_loaded() {
    /* Google XML Sitemaps */
    add_filter('option_sm_options', function ($option) {
      $option['sm_i_hide_survey'] = true;
      return $option;
    });

    /* wp-smtp */
    add_filter('pre_option_postman_release_version', function ($option) {
      return true;
    });

    /* Members review notice */
    if (!defined('MEMBERS_DISABLE_REVIEW_PROMPT')) {
      define('MEMBERS_DISABLE_REVIEW_PROMPT', true);
    }

    /* MetaSlider */
    if (!defined('METASLIDER_DISABLE_SEASONAL_NOTICES')) {
      define('METASLIDER_DISABLE_SEASONAL_NOTICES', true);
    } 
  }

  /**
   * Special handling for themes that can't rely on CSS rules
   *
   * @return void
   */
  function themes_loaded() {
    /* VisualBusiness */
    remove_action('admin_notices', 'visualbusiness_notice');
  }

  /**
   * Registers WP CLI commands
   *
   * @return void
   */
  function cli_init() {
    if (!class_exists('WP_CLI')) {
      return;
    }
    
    WP_CLI::add_command('clarity-build', [$this, 'cli_build']);
    WP_CLI::add_command('clarity-update', [$this, 'cli_update']);
  }

  /**
   * Build WP Clarity definition file for production.
   *
   * @param array $args Command args
   * @param array $assoc_args Command assoc args
   * @return void
   */
  function cli_build($args, $assoc_args) {
    $definitions = var_export($this->getLocalDefinitions(), true);
    file_put_contents(WP_CLARITY_PATH . 'definitions.php', "<?php\n/* This file is automatically generated, do not update manually! Use 'wp clarity-build' to generate. */ \nreturn {$definitions};");
    WP_CLI::success("Built definitions.php");
  }
  
  /**
   * Update definitions from remote source via CLI
   *
   * @param array $args Command args
   * @param array $assoc_args Command assoc args
   * @return void
   */
  function cli_update($args, $assoc_args) {
    $result = $this->update_definitions_from_remote();
    
    if ($result) {
      WP_CLI::success("Remote definitions updated successfully");
    } else {
      WP_CLI::warning("Failed to update remote definitions, using local definitions");
    }
  }

  /**
   * Filter plugin action links
   *
   * @param array $actions Existing actions
   * @return array Modified actions
   */
  public function filter_plugin_action_links(array $actions) {
    return array_merge(array(
      'website' => '<a href="https://wp-clarity.dev/" target="_blank">' . esc_html__('Website', 'clarity-ad-blocker') . '</a>',
      'faq' => '<a href="https://wordpress.org/plugins/clarity-ad-blocker/#faq" target="_blank">' . esc_html__('FAQ', 'clarity-ad-blocker') . '</a>',
      'refresh' => '<a href="' . wp_nonce_url(admin_url('admin.php?action=clarity-refresh'), 'clarity_refresh') . '">' . esc_html__('Refresh definitions', 'clarity-ad-blocker') . '</a>',
      'report-unwanted-banner' => '<a href="https://github.com/khromov/clarity/issues/new?assignees=khromov&labels=filter-request&template=1-report-notification.md&title=Plugin%2FTheme+name%3A+" target="_blank">' . esc_html__('Report unwanted banner', 'clarity-ad-blocker') . '</a>',
    ), $actions);
  }
}

$wp_clarity = new WP_Clarity();