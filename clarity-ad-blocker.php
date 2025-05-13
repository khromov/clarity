<?php
/*
 * Plugin Name: Clarity - Ad blocker for WordPress
 * Plugin URI: https://github.com/khromov/wp-clarity
 * GitHub Plugin URI: khromov/clarity
 * Description: Remove nags and upsells from popular WordPress plugins.
 * Author:      khromov
 * Version:     1.4.2
 * Requires at least: 5.0
 * Tested up to: 6.7
 * Requires PHP: 7.0
 * Text Domain: clarity-ad-blocker
 * Domain Path: /languages/
 * License:     GPL v2 or later
 */


// For debugging purposes only
// define('CLARITY_DEBUG', true);

define( 'WP_CLARITY_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'CLARITY_AD_BLOCKER_ENABLED', true );

/**
 * Class WP_Clarity
 */
class WP_Clarity {
	/**
	 * Option name for storing the definitions
	 */
	private $option_name = 'wp_clarity_definitions';

	/**
	 * CRON hook name
	 */
	private $cron_hook = 'wp_clarity_update_definitions';

	/**
	 * URL to the remote definitions file
	 */
	private $definitions_url = 'https://khromov.github.io/clarity/definitions.txt';

	function __construct() {
		register_activation_hook( __FILE__, [ $this, 'activate_plugin' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate_plugin' ] );

		add_action( 'admin_head', [ $this, 'admin_head' ] );
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
		add_action( 'after_setup_theme', [ $this, 'themes_loaded' ] );
		add_action( $this->cron_hook, [ $this, 'update_definitions_from_remote' ] );
		add_filter( 'plugin_action_links_clarity-ad-blocker/clarity-ad-blocker.php', [ $this, 'filter_plugin_action_links' ] );
		add_action( 'cli_init', [ $this, 'cli_init' ] );
	}

	/**
	 * Plugin activation hook
	 */
	function activate_plugin() {
		if ( ! wp_next_scheduled( $this->cron_hook ) ) {
			wp_schedule_event( time(), 'daily', $this->cron_hook );
		}

		$this->update_definitions_from_remote();
	}

	/**
	 * Plugin deactivation hook
	 */
	function deactivate_plugin() {
		wp_clear_scheduled_hook( $this->cron_hook );
		delete_option( $this->option_name );
	}

	/**
	 * Process definitions text into CSS selectors
	 */
	function process_definitions_text( $content ) {
		$filter_empty_lines = function ( $item ) {
			return (bool) $item;
		};

		$filter_comments = function ( $item ) {
			return trim( preg_replace( '/(--.*)/', '', $item ) );
		};

		$rules_file = explode( "\n", $content );

		return implode(
			', ',
			apply_filters(
				'wp_clarity_rules',
				array_filter( array_filter( $rules_file, $filter_comments ), $filter_empty_lines )
			)
		);
	}

	/**
	 * Get definitions from cache or local file
	 */
	function getDefinitions( $force_refresh = false ) {
		// If debug mode is enabled, always use local definitions
		if ( defined( 'CLARITY_DEBUG' ) && CLARITY_DEBUG ) {
			do_action( 'qm/info', __( 'Debug mode enabled, using local definitions', 'clarity-ad-blocker' ) );
			return $this->getLocalDefinitions();
		}

		$cached = get_option( $this->option_name );

		if ( $force_refresh || $cached === false ) {
			do_action( 'qm/info', __( 'No cached definitions found or refresh forced', 'clarity-ad-blocker' ) );
			do_action( 'qm/info', __( 'Using local definitions as fallback', 'clarity-ad-blocker' ) );
			return $this->getLocalDefinitions();
		}

		do_action( 'qm/info', __( 'Using cached definitions from database', 'clarity-ad-blocker' ) );
		return $cached;
	}

	/**
	 * Get definitions from local file
	 */
	function getLocalDefinitions() {
		do_action( 'qm/info', __( 'Loading definitions from local text file', 'clarity-ad-blocker' ) );

		$content = file_get_contents( WP_CLARITY_PATH . 'definitions.txt' );
		return $this->process_definitions_text( $content );
	}

	/**
	 * Update definitions from remote source
	 */
	function update_definitions_from_remote() {
		// Don't update from remote in debug mode
		if ( defined( 'CLARITY_DEBUG' ) && CLARITY_DEBUG ) {
			do_action( 'qm/info', __( 'Debug mode enabled, skipping remote definitions update', 'clarity-ad-blocker' ) );
			return false;
		}

		do_action( 'qm/info', __( 'Attempting to fetch remote definitions', 'clarity-ad-blocker' ) );

		$response = wp_remote_get( $this->definitions_url );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			do_action( 'qm/info', 'Failed to fetch remote definitions' );
			return false;
		}

		$content = wp_remote_retrieve_body( $response );

		if ( empty( $content ) ) {
			do_action( 'qm/info', __( 'Remote definitions were empty', 'clarity-ad-blocker' ) );
			return false;
		}

		$processed_definitions = $this->process_definitions_text( $content );
		update_option( $this->option_name, $processed_definitions, false );

		do_action( 'qm/info', __( 'Updated remote definitions successfully', 'clarity-ad-blocker' ) );

		return true;
	}

	/**
	 * Special handling for plugins that can't rely on CSS rules
	 */
	function plugins_loaded() {
		/* Google XML Sitemaps */
		add_filter(
			'option_sm_options',
			function ( $option ) {
				$option['sm_i_hide_survey'] = true;
				return $option;
			}
		);

		/* wp-smtp */
		add_filter(
			'pre_option_postman_release_version',
			function ( $option ) {
				return true;
			}
		);

		/* Members review notice */
		if ( ! defined( 'MEMBERS_DISABLE_REVIEW_PROMPT' ) ) {
			define( 'MEMBERS_DISABLE_REVIEW_PROMPT', true );
		}

		/* MetaSlider */
		if ( ! defined( 'METASLIDER_DISABLE_SEASONAL_NOTICES' ) ) {
			define( 'METASLIDER_DISABLE_SEASONAL_NOTICES', true );
		}
		
		// Check if CRON job is scheduled and schedule it if not
		if ( ! wp_next_scheduled( $this->cron_hook ) ) {
			wp_schedule_event( time(), 'daily', $this->cron_hook );
			do_action( 'qm/info', __( 'Scheduled definitions update CRON job in plugins_loaded', 'clarity-ad-blocker' ) );
		}
	}

	/**
	 * Hides stuff via CSS in the admin header
	 */
	function admin_head() {
		$selectors = $this->getDefinitions();
		if ( strlen( $selectors ) === 0 ) { return;
		}
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
	 * Special handling for themes that can't rely on CSS rules
	 */
	function themes_loaded() {
		/* VisualBusiness */
		remove_action( 'admin_notices', 'visualbusiness_notice' );
	}

	/**
	 * Registers WP CLI commands
	 */
	function cli_init() {
		if ( ! class_exists( 'WP_CLI' ) ) {
			return;
		}

		WP_CLI::add_command( 'clarity-update', [ $this, 'cli_update' ] );
	}

	/**
	 * Update definitions from remote source via CLI
	 */
	function cli_update( $args, $assoc_args ) {
		$result = $this->update_definitions_from_remote();

		if ( $result ) {
			WP_CLI::success( __( 'Remote definitions updated successfully', 'clarity-ad-blocker' ) );
		} else {
			WP_CLI::warning( __( 'Failed to update remote definitions, using local definitions', 'clarity-ad-blocker' ) );
		}
	}

	/**
	 * Filter plugin action links
	 */
	public function filter_plugin_action_links( array $actions ) {
		return array_merge(
			array(
				'website'                => '<a href="https://wp-clarity.dev/" target="_blank">' . esc_html__( 'Website', 'clarity-ad-blocker' ) . '</a>',
				'faq'                    => '<a href="https://wordpress.org/plugins/clarity-ad-blocker/#faq" target="_blank">' . esc_html__( 'FAQ', 'clarity-ad-blocker' ) . '</a>',
				'report-unwanted-banner' => '<a href="https://github.com/khromov/clarity/issues/new?assignees=khromov&labels=filter-request&template=1-report-notification.md&title=Plugin%2FTheme+name%3A+" target="_blank">' . esc_html__( 'Report unwanted banner', 'clarity-ad-blocker' ) . '</a>',
			),
			$actions
		);
	}
}

$wp_clarity = new WP_Clarity();