<?php
/**
 * Environment helper for LSX MCP UI.
 *
 * All safety checks for MCP features are centralised here. Feature flags
 * are resolved from LSX_MCP_UI_Settings (which itself prefers constants
 * over saved options), so callers should never read constants directly.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Environment
 */
class LSX_MCP_UI_Environment {

	/** Hosts that are always safe regardless of WP_ENVIRONMENT_TYPE. */
	private static $safe_host_exact = array( 'localhost', '127.0.0.1', '::1' );

	/** Suffixes that indicate a local development host. */
	private static $safe_host_suffixes = array( '.local', '.test', '.localhost' );

	/** Valid WordPress environment type strings. */
	private static $valid_environment_types = array( 'local', 'development', 'staging', 'production' );

	// ── Feature flags ─────────────────────────────────────────────────────────

	/**
	 * Is the MCP feature layer enabled?
	 * Resolved from LSX_MCP_UI_Settings (constant overrides saved option).
	 */
	public static function is_mcp_enabled() {
		return LSX_MCP_UI_Settings::get( 'enabled' );
	}

	/**
	 * Is Application Password compatibility enabled?
	 * Resolved from LSX_MCP_UI_Settings.
	 */
	public static function is_application_passwords_enabled() {
		return LSX_MCP_UI_Settings::get( 'enable_application_passwords' );
	}

	/**
	 * BC alias — kept so existing callers do not break.
	 */
	public static function is_application_passwords_constant_set() {
		return self::is_application_passwords_enabled();
	}

	/**
	 * Is the custom LightSpeed MCP server enabled?
	 * Defaults to true when MCP is enabled and no option is saved.
	 */
	public static function is_custom_server_enabled() {
		if ( ! self::is_mcp_enabled() ) {
			return false;
		}
		return LSX_MCP_UI_Settings::get( 'enable_custom_server' );
	}

	// ── WordPress environment ─────────────────────────────────────────────────

	/**
	 * Returns the WordPress environment type string.
	 * Falls back to 'production' when wp_get_environment_type() is unavailable.
	 */
	public static function get_environment_type() {
		if ( function_exists( 'wp_get_environment_type' ) ) {
			return wp_get_environment_type();
		}
		return defined( 'WP_ENVIRONMENT_TYPE' ) ? WP_ENVIRONMENT_TYPE : 'production';
	}

	// ── Operational environment ───────────────────────────────────────────────

	/**
	 * Returns a plugin-specific environment classification based on the host name,
	 * not solely on WP_ENVIRONMENT_TYPE.
	 *
	 * This lets .lightspeedwp.dev sites be treated as development infrastructure
	 * even when their server provisioning sets WP_ENVIRONMENT_TYPE = production.
	 *
	 * Return values:
	 *   'local'               — localhost / 127.0.0.1 / *.local / *.test / *.localhost
	 *   'lightspeed-dev-domain' — host ends with the configured dev suffix (default .lightspeedwp.dev)
	 *   'development'         — WP env = local or development, not a LightSpeed domain
	 *   'staging'             — WP env = staging, not a LightSpeed domain
	 *   'blocked'             — production or unrecognised public host
	 *
	 * @return string
	 */
	public static function get_operational_environment() {
		$host    = self::get_current_host();
		$wp_env  = self::get_environment_type();
		$suffix  = self::get_allowed_dev_suffix();

		// 1. Exact-match local hosts.
		if ( in_array( $host, self::$safe_host_exact, true ) ) {
			return 'local';
		}

		// 2. Suffix-match local hosts.
		foreach ( self::$safe_host_suffixes as $s ) {
			if ( self::host_ends_with( $host, $s ) ) {
				return 'local';
			}
		}

		// 3. LightSpeed dev domain — overrides WP_ENVIRONMENT_TYPE.
		if ( self::host_ends_with( $host, $suffix ) ) {
			return 'lightspeed-dev-domain';
		}

		// 4. Fall back to WordPress environment type for other hosts.
		if ( in_array( $wp_env, array( 'local', 'development' ), true ) ) {
			return 'development';
		}
		if ( 'staging' === $wp_env ) {
			return 'staging';
		}

		return 'blocked';
	}

	/**
	 * Whether MCP features are permitted in the current environment.
	 *
	 * local and lightspeed-dev-domain are always allowed.
	 * development is allowed.
	 * staging requires 'staging' to be in LSX_MCP_ALLOWED_ENVIRONMENTS.
	 * blocked is never allowed.
	 */
	public static function is_dev_environment() {
		$op = self::get_operational_environment();

		if ( in_array( $op, array( 'local', 'lightspeed-dev-domain', 'development' ), true ) ) {
			return true;
		}
		if ( 'staging' === $op ) {
			return in_array( 'staging', self::get_allowed_environments(), true );
		}
		return false;
	}

	/**
	 * Returns the list of WP environment types in which MCP features are permitted.
	 * Reads LSX_MCP_ALLOWED_ENVIRONMENTS. Invalid entries are silently dropped.
	 * Defaults to ['local', 'development'].
	 *
	 * 'production' is accepted here but still triggers a critical notice — it is
	 * never silently allowed.
	 */
	public static function get_allowed_environments() {
		if ( ! defined( 'LSX_MCP_ALLOWED_ENVIRONMENTS' ) || ! is_array( LSX_MCP_ALLOWED_ENVIRONMENTS ) ) {
			return array( 'local', 'development' );
		}

		$validated = array();
		foreach ( LSX_MCP_ALLOWED_ENVIRONMENTS as $entry ) {
			if ( is_string( $entry ) && in_array( $entry, self::$valid_environment_types, true ) ) {
				$validated[] = $entry;
			}
		}

		return empty( $validated ) ? array( 'local', 'development' ) : $validated;
	}

	/**
	 * Whether 'production' is explicitly listed in LSX_MCP_ALLOWED_ENVIRONMENTS.
	 */
	public static function is_production_in_allowed_list() {
		if ( ! defined( 'LSX_MCP_ALLOWED_ENVIRONMENTS' ) ) {
			return false;
		}
		return in_array( 'production', self::get_allowed_environments(), true );
	}

	// ── Host detection ────────────────────────────────────────────────────────

	/**
	 * Returns the current HTTP host.
	 */
	public static function get_current_host() {
		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
			// HTTP_HOST includes the port for non-standard ports (e.g. localhost:8900).
			// Strip it so host comparisons work correctly.
			$host = preg_replace( '/:\d+$/', '', $host );
			return $host;
		}
		return (string) parse_url( get_site_url(), PHP_URL_HOST );
	}

	/**
	 * Returns the configured allowed dev domain suffix.
	 * Resolved from LSX_MCP_UI_Settings (constant overrides saved option).
	 */
	public static function get_allowed_dev_suffix() {
		return LSX_MCP_UI_Settings::get( 'allowed_dev_suffix' );
	}

	/**
	 * Returns true when the current host is safe for MCP traffic:
	 * local-like hosts or hosts that end in the configured dev suffix.
	 */
	public static function is_allowed_host() {
		$host = self::get_current_host();

		if ( in_array( $host, self::$safe_host_exact, true ) ) {
			return true;
		}
		foreach ( self::$safe_host_suffixes as $suffix ) {
			if ( self::host_ends_with( $host, $suffix ) ) {
				return true;
			}
		}
		return self::host_ends_with( $host, self::get_allowed_dev_suffix() );
	}

	// ── Composite safety checks ───────────────────────────────────────────────

	/**
	 * Application Passwords are safe when:
	 * - MCP is enabled
	 * - Application Password compatibility is enabled
	 * - The operational environment is not 'blocked'
	 * - The host matches an allowed pattern
	 */
	public static function is_safe_for_application_passwords() {
		return self::is_mcp_enabled()
			&& self::is_application_passwords_enabled()
			&& 'blocked' !== self::get_operational_environment()
			&& self::is_allowed_host();
	}

	/**
	 * The custom server is safe when:
	 * - It is enabled
	 * - The environment is permitted (dev / local / lightspeed-dev-domain, or staging if opted in)
	 * - The MCP Adapter class is available
	 */
	public static function is_safe_for_custom_server() {
		return self::is_custom_server_enabled()
			&& self::is_dev_environment()
			&& self::is_adapter_available();
	}

	// ── Capability helpers ────────────────────────────────────────────────────

	public static function get_required_capability() {
		return apply_filters( 'lsx_mcp_required_capability', 'manage_options' );
	}

	public static function get_testing_server_capability() {
		return apply_filters( 'lsx_mcp_testing_server_capability', 'manage_options' );
	}

	// ── Dependency checks ─────────────────────────────────────────────────────

	public static function is_adapter_available() {
		return class_exists( 'WP\\MCP\\Core\\McpAdapter' );
	}

	/**
	 * Returns the configured dedicated MCP user login.
	 * Resolved from LSX_MCP_UI_Settings.
	 */
	public static function get_dedicated_user_login() {
		return LSX_MCP_UI_Settings::get( 'dedicated_user_login' );
	}

	/**
	 * Returns the WP_User object for the dedicated MCP user, or null.
	 */
	public static function get_dedicated_user() {
		$login = self::get_dedicated_user_login();
		$user  = get_user_by( 'login', $login );
		return $user instanceof WP_User ? $user : null;
	}

	// ── Private helpers ───────────────────────────────────────────────────────

	private static function host_ends_with( $host, $suffix ) {
		$len = strlen( $suffix );
		if ( 0 === $len ) {
			return true;
		}
		return substr( $host, -$len ) === $suffix;
	}
}
