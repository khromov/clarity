<?php
/*
 * Plugin Name: Clarity - Ad blocker for WordPress
 * Plugin URI: https://github.com/khromov/wp-clarity
 * GitHub Plugin URI: khromov/wp-clarity
 * Description: Remove nags and upsells from popular WordPress plugins.
 * Author:      khromov
 * Version:     1.0.210207
 * Requires at least: 5.0
 * Tested up to: 5.6
 * Requires PHP: 7.0
 * Text Domain: wp-clarity
 * Domain Path: /languages/
 * License:     GPL v2 or later
 */

 define('WP_CLARITY_PATH', trailingslashit(plugin_dir_path(__FILE__)));
/**
 * Class WP_Hush
 */
class WP_Clarity {
  function __construct() {
    add_action('admin_head', array($this, 'admin_head'));
    add_action('plugins_loaded', array($this, 'plugins_loaded'));
  }

  function getDefinitions() {
    $filterEmptyLines = function($item) { return !!$item; };
    $filterComments = function($item) {
      return trim(preg_replace('/(--.*)/', '', $item));
    };

    $rulesFile = explode(PHP_EOL, file_get_contents(WP_CLARITY_PATH . 'definitions.txt'));

    return implode(', ', apply_filters('wp_clarity_rules', array_filter(array_filter($rulesFile, $filterComments), $filterEmptyLines)));
  }

  /**
   *  Hides stuff via CSS in the admin header
   * 
   * @return void
   */
  function admin_head() {
    $selectors = $this->getDefinitions();
    if(strlen($selectors) === 0) return;
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
    add_filter('option_sm_options', function($option) {
      $option['sm_i_hide_survey'] = true;
      return $option;
    });

    /* wp-smtp */
    add_filter('pre_option_postman_release_version', function($option) {
      return true;
    });
  }
}

$wp_clarity = new WP_Clarity();
