<?php
/**
 * Pre-defined selectors
 * Don't modify this file, use `wp_clarity_rules` filter instead
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return [
	// Yoast premium nags
	'.wpseo_content_wrapper #sidebar-container',
	'.yoast_premium_upsell',
	'#wpseo-local-seo-upsell',
	'.yoast-settings-section-upsell',
	// Rank Math SEO
	'#rank_math_review_plugin_notice',
	// Better WordPress ReCAPTCHA
	'#bwp-get-social',
	'.bwp-button-paypal',
	'#bwp-sidebar-right',
	// TJ Custom CSS
	'.tjcc-custom-css #postbox-container-1',
	// CBX Custom Taxonomy Filter
	'.settings_page_wpcustomtaxfilterinadmin #postbox-container-1',
	// Duplicate Posts
	'#duplicate-post-notice #newsletter-subscribe-form',
	// WPS Hide Login
	'div[id^="dnh-wrm"]',
	// Ad Inserter
	'.notice-info.dst-notice',
	// googleanalytics
	'#googleanalytics_terms_notice',
	// Unyson
	'.fw-brz-dismiss',
	// Elementor
	'div.elementor-message[data-notice_id="elementor_dev_promote"]',
	// Redirection for Contact Form 7
	'.notice-success.wpcf7r-notice',
	// Disable Comments
	'.dc-text__block.disable__comment__alert',
	// Admin Menu Editor
	'#ws_sidebar_pro_ad',
	// Premium Addons for Elementor
	'.pa-new-feature-notice',
	// Redux
	'#redux-connect-message',
	// Smush
	'.frash-notice-email',
	'.frash-notice-rate',
	'#smush-box-pro-features',
	'#wp-smush-bulk-smush-upsell-row',
	// Easy Updates Manager
	'#easy-updates-manager-dashnotice',
	// Metaslider
	'#metaslider-optin-notice',
	'#extendifysdk_announcement',
	// WP User Avatar "recommends" MailOption
	'.mo-admin-notice',
	// post-smtp-donation
	'.post-smtp-donation',
	// Widget Area Block
	'div[data-dismissible="notice-owa-sale-forever"]',
	// Themeisle Companion
	'.neve-notice-upsell',
	// Pagelayer Pro promo
	'#pagelayer_promo',
	// Simple Custom Post Order rate nag
	'#simple-custom-post-order-epsilon-review-notice',
	// Ultimate Social Media icons nags in various places
	'.sfsi_new_prmium_follw',
	// The Events Calendar analytics nag
	'div.fs-slug-the-events-calendar[data-id="connect_account"]',
	// WebP Converter for Media
	'div.notice[data-notice="webp-converter-for-media"]',
	'.webpLoader__popup.webpPopup',
	// Generic "Plugin Usage Tracker" class
	'.put-dismiss-notice',
	// WP Mail SMTP
	'.wp-mail-smtp-review-notice',
	'#wp-mail-smtp-pro-banner',
	// Hopefully most Freemius ads
	'body div.promotion.fs-notice',
	// Analytify
	'.analytify-review-thumbnail',
	'.analytify-review-notice',
	// Jetpack
	'.jitm-banner.is-upgrade-premium',
	// Webcraftic Local Google Analytics
	'div[data-name*="wbcr_factory_notice_adverts"]',
	// Forminator
	'.sui-subscription-notice',
	'#sui-cross-sell-footer',
	'.sui-cross-sell-modules',
	'.forminator-rating-notice',
	'.sui-dashboard-upsell-upsell',
	// AnWP Post Grid and Post Carousel Slider for Elementor
	'.anwp-post-grid__rate',
	// Smash Balloon Social Post Feed
	'.cff-settings-cta',
	'.cff-header-upgrade-notice',
	'.cff_notice.cff_review_notice_step_1',
	'.cff_get_pro_highlight',
	// Activity Log
	'.aal-install-elementor',
	// Admin Columns
	'#ws_sidebar_pro_ad',
	// Bold Timeline Lite
	'.bold-timeline-lite-feedback-notice-wrapper',
	// ElementsKit Lite
	'#elementskit-lite-go-pro-noti2ce',
	'#elementskit-lite-_plugin_rating_msg_used_in_day',
	// YARPP Related Posts
	'.yarpp-review-notice',
	// Pretty Links
	'#prli_review_notice',
	// Webdados plugins
	'#webdados_invoicexpress_nag',
	// Visual Composer
	'#vc_license-activation-notice',
	// ALD - Dropshipping and Fulfillment for AliExpress and WooCommerce
	'.villatheme-dashboard.updated',
	// Filebird
	'#njt-FileBird-review',
	// Redis Object Cache
	'.notice[data-dismissible="pro_release_notice"]',
	// Variation Swatches for WooCommerce
	'#thwvsf_review_request_notice',
	// BetterDocs
	'.wpdeveloper-review-notice',
	// TI WooCommerce Wishlist
	'div[data-notice_type="tinvwl-user-review"]',
	'div[data-notice_type="tinvwl-user-premium"]',
	// BackupGuard
	'#sg-backup-review-wrapper',
];
