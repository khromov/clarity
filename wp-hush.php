<?php
/*
 * Plugin Name: WP Hush
 * Plugin URI: https://github.com/khromov/wp-hush
 * GitHub Plugin URI: khromov/wp-hush
 * Description: Remove obnoxious nags and donation links from popular plugins.
 * Author:      khromov
 * Version:     1.0.1
 * Text Domain: wp-hush
 * Domain Path: /languages/
 * License:     GPL v2 or later
 */

/**
 * Class WP_Hush
 */
class WP_Hush {
  var $js_selectors = array();
  var $css_selectors = array();

  function __construct() {
    $this->hush();

    add_action('admin_head', array($this, 'admin_head'));
    add_action('admin_footer', array($this, 'admin_footer'));
  }

  /**
   * Hushes plugins
   */
  function hush() {

    //Yoast sidebar nag
    $this->add_css_selector('.wpseo_content_wrapper #sidebar-container');

    //Better WordPress ReCAPTCHA
    $this->add_css_selector('#bwp-get-social');
    $this->add_css_selector('.bwp-button-paypal');
    $this->add_css_selector('#bwp-sidebar-right');
    
    //TJ Custom CSS
    $this->add_css_selector('.tjcc-custom-css #postbox-container-1');
    
    //CBX Custom Taxonomy Filter
    $this->add_css_selector('.settings_page_wpcustomtaxfilterinadmin #postbox-container-1');
    
    //Duplicate Posts
    $this->add_css_selector('.settings_page_duplicatepost #wpbody-content .wrap > div');
  }

  /**
   *  Hides stuff via CSS in the admin header
   */
  function admin_head() {
    if(sizeof($this->css_selectors) === 0) return;

    ?>
      <style type="text/css">
        <?=implode(', ', $this->css_selectors);?> {
          display: none !important;
        }
      </style>
    <?php
  }

  /**
   * Hides stuff via jQuery in the admin footer
   */
  function admin_footer() {
    if(sizeof($this->css_selectors) === 0) return;

    ?>
      <script type="text/javascript">
        jQuery(document).ready(function() {
          jQuery('<?=implode(', ', $this->js_selectors)?>').hide();
        });
      </script>
    <?php
  }

  /**
   * Adds a jQuery selector to hide
   *
   * @param $selector
   */
  function add_js_selector($selector) {
    $this->js_selectors[] = $selector;
  }

  /**
   * Add CSS selector to hide
   *
   * @param $selector
   */
  function add_css_selector($selector) {
    $this->css_selectors[] = $selector;
  }
}

$wp_hush = new WP_Hush();
