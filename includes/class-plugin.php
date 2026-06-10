<?php
/**
 * Main plugin bootstrap for the LightSpeed MCP UI layer.
 *
 * Initialises optional modules based on the active constants and environment.
 * The existing wpmcpui_* ability registry is loaded separately via the main
 * plugin file and is not affected by this class.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Plugin
 */
class LSX_MCP_UI_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Returns the singleton.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Bootstrap all modules.
	 */
	private function init() {
		// Application Password compatibility – always register filters so the
		// environment check inside each filter fires at request time.
		LSX_MCP_UI_Application_Passwords::init();

		// Admin page – always register so it is visible even when MCP is off,
		// making it easy for devs to see what constants they need to set.
		LSX_MCP_UI_Admin_Page::init();

		// LightSpeed testing abilities – register on the standard hook.
		// The abilities themselves only register when MCP is enabled.
		add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'register_lightspeed_category' ) );
		add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_lightspeed_abilities' ) );

		// Custom MCP server – hook into mcp_adapter_init.
		add_action( 'mcp_adapter_init', array( 'LSX_MCP_UI_Custom_Server', 'register_server' ) );
	}

	/**
	 * Register the lightspeed ability category.
	 */
	public static function register_lightspeed_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}
		wp_register_ability_category( 'lightspeed', array(
			'label'       => 'LightSpeed Testing',
			'description' => 'Read-only diagnostic abilities for LightSpeed development and QA.',
		) );
	}

	/**
	 * Register all LightSpeed testing abilities.
	 *
	 * Abilities are only registered when LSX_MCP_ENABLED is true and the
	 * environment is dev/local. They are deliberately not exposed to the
	 * default MCP server (meta.mcp.public = false) – they are only available
	 * via the custom LightSpeed testing server.
	 */
	public static function register_lightspeed_abilities() {
		if ( ! LSX_MCP_UI_Environment::is_mcp_enabled() ) {
			return;
		}
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		LSX_MCP_UI_Ability_Site_Summary::register();
		LSX_MCP_UI_Ability_Plugin_Inventory::register();
		LSX_MCP_UI_Ability_Theme_Audit::register();
		LSX_MCP_UI_Ability_URL_Inventory::register();
		LSX_MCP_UI_Ability_Content_Readiness::register();
		LSX_MCP_UI_Ability_Block_Theme_Audit::register();
	}
}
