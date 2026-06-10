<?php
/**
 * Generates MCP client configuration snippets for VS Code, Claude Code,
 * Claude Desktop, and Codex — for both STDIO (local Studio) and HTTP
 * (.lightspeedwp.dev development sites).
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Config_Generator
 */
class LSX_MCP_UI_Config_Generator {

	// ── Endpoint helpers ──────────────────────────────────────────────────────

	/**
	 * REST URL for the official default server.
	 */
	public static function get_default_server_rest_url() {
		return untrailingslashit( rest_url( 'mcp/mcp-adapter-default-server' ) );
	}

	/**
	 * REST URL for the custom LightSpeed testing server.
	 */
	public static function get_lightspeed_server_rest_url() {
		return untrailingslashit( rest_url( 'lightspeed-testing-mcp-server/mcp' ) );
	}

	// ── STDIO (local Studio) configs ──────────────────────────────────────────

	/**
	 * Returns the VS Code STDIO config for the default server.
	 *
	 * @param string $wp_path  Absolute path to the Studio site's WordPress root.
	 * @param string $wp_user  WordPress username to run as.
	 * @param string $server   Server ID (default: mcp-adapter-default-server).
	 * @return string JSON snippet
	 */
	public static function vscode_stdio_default( $wp_path = '/Users/YOUR_USER/Studio/YOUR_SITE', $wp_user = 'admin', $server = 'mcp-adapter-default-server' ) {
		$config = array(
			'servers' => array(
				'wordpress-local-default' => array(
					'command' => 'wp',
					'args'    => array(
						'--path=' . $wp_path,
						'mcp-adapter',
						'serve',
						'--server=' . $server,
						'--user=' . $wp_user,
					),
				),
			),
		);
		return wp_json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Returns the VS Code STDIO config for the LightSpeed testing server.
	 */
	public static function vscode_stdio_lightspeed( $wp_path = '/Users/YOUR_USER/Studio/YOUR_SITE', $wp_user = 'admin' ) {
		$config = array(
			'servers' => array(
				'wordpress-local-testing' => array(
					'command' => 'wp',
					'args'    => array(
						'--path=' . $wp_path,
						'mcp-adapter',
						'serve',
						'--server=lightspeed-testing-mcp-server',
						'--user=' . $wp_user,
					),
				),
			),
		);
		return wp_json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Returns the Claude Code / Claude Desktop STDIO config for the default server.
	 */
	public static function claude_stdio_default( $wp_path = '/Users/YOUR_USER/Studio/YOUR_SITE', $wp_user = 'admin' ) {
		$config = array(
			'mcpServers' => array(
				'wordpress-local-default' => array(
					'command' => 'wp',
					'args'    => array(
						'--path=' . $wp_path,
						'mcp-adapter',
						'serve',
						'--server=mcp-adapter-default-server',
						'--user=' . $wp_user,
					),
				),
			),
		);
		return wp_json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Returns the Claude Code / Claude Desktop STDIO config for the LightSpeed server.
	 */
	public static function claude_stdio_lightspeed( $wp_path = '/Users/YOUR_USER/Studio/YOUR_SITE', $wp_user = 'admin' ) {
		$config = array(
			'mcpServers' => array(
				'wordpress-local-testing' => array(
					'command' => 'wp',
					'args'    => array(
						'--path=' . $wp_path,
						'mcp-adapter',
						'serve',
						'--server=lightspeed-testing-mcp-server',
						'--user=' . $wp_user,
					),
				),
			),
		);
		return wp_json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	// ── HTTP (.lightspeedwp.dev) configs ──────────────────────────────────────

	/**
	 * Returns the VS Code HTTP config for a development site.
	 *
	 * @param string $server_key  Key name for the server entry.
	 * @param string $api_url     Full REST URL of the MCP server.
	 * @param string $username    WordPress username.
	 * @param string $password    Application Password placeholder.
	 * @return string JSON snippet
	 */
	public static function vscode_http( $server_key, $api_url, $username, $password = 'xxxx xxxx xxxx xxxx xxxx xxxx' ) {
		$config = array(
			'servers' => array(
				$server_key => array(
					'command' => 'npx',
					'args'    => array( '-y', '@automattic/mcp-wordpress-remote@latest' ),
					'env'     => array(
						'WP_API_URL'      => $api_url,
						'WP_API_USERNAME' => $username,
						'WP_API_PASSWORD' => $password,
						'OAUTH_ENABLED'   => 'false',
					),
				),
			),
		);
		return wp_json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Returns the Claude Code / Claude Desktop HTTP config for a development site.
	 */
	public static function claude_http( $server_key, $api_url, $username, $password = 'xxxx xxxx xxxx xxxx xxxx xxxx' ) {
		$config = array(
			'mcpServers' => array(
				$server_key => array(
					'command' => 'npx',
					'args'    => array( '-y', '@automattic/mcp-wordpress-remote@latest' ),
					'env'     => array(
						'WP_API_URL'      => $api_url,
						'WP_API_USERNAME' => $username,
						'WP_API_PASSWORD' => $password,
						'OAUTH_ENABLED'   => 'false',
					),
				),
			),
		);
		return wp_json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	// ── wp-config.php constants block ─────────────────────────────────────────

	/**
	 * Returns the recommended wp-config.php constants block for dev sites.
	 */
	public static function wp_config_constants() {
		return "define( 'WP_ENVIRONMENT_TYPE', 'development' );\n"
			. "define( 'LSX_MCP_ENABLED', true );\n"
			. "define( 'LSX_MCP_ENABLE_APPLICATION_PASSWORDS', true );\n"
			. "define( 'LSX_MCP_ENABLE_CUSTOM_SERVER', true );";
	}
}
