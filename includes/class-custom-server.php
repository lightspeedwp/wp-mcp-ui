<?php
/**
 * Registers the LightSpeed custom MCP server.
 *
 * Only registers when:
 * - LSX_MCP_ENABLED = true
 * - LSX_MCP_ENABLE_CUSTOM_SERVER = true (default)
 * - Environment is local or development
 * - WP\MCP\Core\McpAdapter class is available
 *
 * The server exposes only the read-only LightSpeed testing abilities.
 * It does not use meta.mcp.public discovery – abilities are listed explicitly.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Custom_Server
 */
class LSX_MCP_UI_Custom_Server {

	const SERVER_ID        = 'lightspeed-testing-mcp-server';
	const SERVER_NAMESPACE = 'lightspeed-testing-mcp-server';
	const SERVER_ROUTE     = 'mcp';
	const SERVER_NAME      = 'LightSpeed Testing MCP Server';
	const SERVER_DESC      = 'Read-only LightSpeed MCP server for WordPress development and QA diagnostics.';

	/**
	 * Ability slugs this server exposes as tools.
	 */
	const TOOLS = array(
		'lightspeed/site-summary',
		'lightspeed/plugin-inventory',
		'lightspeed/theme-audit',
		'lightspeed/url-inventory',
		'lightspeed/content-readiness',
		'lightspeed/block-theme-audit',
	);

	/**
	 * Called on mcp_adapter_init.
	 *
	 * @param mixed $adapter WP\MCP\Core\McpAdapter instance.
	 */
	public static function register_server( $adapter ) {
		if ( ! self::should_register() ) {
			return;
		}

		if ( ! class_exists( 'WP\\MCP\\Transport\\HttpTransport' ) ) {
			return;
		}
		if ( ! class_exists( 'WP\\MCP\\Infrastructure\\ErrorHandling\\ErrorLogMcpErrorHandler' ) ) {
			return;
		}
		if ( ! class_exists( 'WP\\MCP\\Infrastructure\\Observability\\NullMcpObservabilityHandler' ) ) {
			return;
		}

		$result = $adapter->create_server(
			self::SERVER_ID,
			self::SERVER_NAMESPACE,
			self::SERVER_ROUTE,
			self::SERVER_NAME,
			self::SERVER_DESC,
			LSXMCPUI_VERSION,
			array( 'WP\\MCP\\Transport\\HttpTransport' ),
			'WP\\MCP\\Infrastructure\\ErrorHandling\\ErrorLogMcpErrorHandler',
			'WP\\MCP\\Infrastructure\\Observability\\NullMcpObservabilityHandler',
			self::TOOLS,
			array(), // no resources
			array(), // no prompts
			array( __CLASS__, 'transport_permission_callback' )
		);

		if ( is_wp_error( $result ) ) {
			// Log but do not fatal.
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				'LightSpeed MCP: Failed to register custom server. ' . $result->get_error_message()
			);
		}
	}

	/**
	 * Transport-level permission callback.
	 *
	 * Requires the current user to be logged in with the required capability.
	 */
	public static function transport_permission_callback() {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		return current_user_can( LSX_MCP_UI_Environment::get_testing_server_capability() );
	}

	/**
	 * Whether it is currently safe to register the server.
	 */
	private static function should_register() {
		return LSX_MCP_UI_Environment::is_safe_for_custom_server();
	}
}
