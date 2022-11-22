<?php
/*
 * Plugin Name: Clarity - Ad blocker for WordPress
 * Plugin URI: https://github.com/khromov/wp-clarity
 * GitHub Plugin URI: khromov/clarity
 * Description: Remove nags and upsells from popular WordPress plugins.
 * Author:      khromov
 * Version:     1.3.221026
 * Requires at least: 5.0
 * Tested up to: 6.0
 * Requires PHP: 7.0
 * Text Domain: clarity-ad-blocker
 * Domain Path: /languages/
 * License:     GPL v2 or later
 */

define('WP_CLARITY_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('CLARITY_AD_BLOCKER_ENABLED', true);

/**
 * Class WP_Hush
 */
class WP_Clarity {
  function __construct() {
    add_action('admin_head', [$this, 'admin_head']);
    add_action('plugins_loaded', [$this, 'plugins_loaded']);
    add_action('cli_init', [$this, 'cli_init']);
    add_filter('plugin_action_links_clarity-ad-blocker/clarity-ad-blocker.php', [$this, 'filter_plugin_action_links']);
  }

	/**
	 * Generate definitions from definitions.txt
	 *
	 * @return string
	 */
	function getDefinitions() {
		do_action( 'qm/info', 'Loading definitions.php file' );
		$rules = include( WP_CLARITY_PATH . 'definitions.php' );

		return implode( ', ', apply_filters( 'wp_clarity_rules', $rules ) );
	}

  /**
   *  Hides stuff via CSS in the admin header
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
  }

  public function filter_plugin_action_links(array $actions) {
    return array_merge(array(
      'website' => '<a href="https://wp-clarity.dev/" target="_blank">' . esc_html__('Website', 'clarity-ad-blocker') . '</a>',
      'faq' => '<a href="https://wordpress.org/plugins/clarity-ad-blocker/#faq" target="_blank">' . esc_html__('FAQ', 'clarity-ad-blocker') . '</a>',
      'report-unwanted-banner' => '<a href="https://github.com/khromov/clarity/issues/new?assignees=khromov&labels=filter-request&template=1-report-notification.md&title=Plugin%2FTheme+name%3A+" target="_blank">' . esc_html__('Report unwanted banner', 'clarity-ad-blocker') . '</a>',
    ), $actions);
  }
}

$wp_clarity = new WP_Clarity();
