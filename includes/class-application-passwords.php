<?php
/**
 * Application Password compatibility for MCP dev use.
 *
 * Wordfence (and some hardening configs) can block Application Passwords.
 * This class restores availability ONLY for allowed local/dev environments
 * and only for the dedicated MCP user or manage_options admins.
 *
 * It never disables Wordfence globally and never enables Application Passwords
 * on staging or production.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Application_Passwords
 */
class LSX_MCP_UI_Application_Passwords {

	/**
	 * Register WordPress filters.
	 *
	 * Called once from the main plugin class on plugins_loaded.
	 */
	public static function init() {
		add_filter( 'wp_is_application_passwords_available', array( __CLASS__, 'allow_application_passwords' ), PHP_INT_MAX );
		add_filter( 'wp_is_application_passwords_available_for_user', array( __CLASS__, 'allow_application_passwords_for_user' ), PHP_INT_MAX, 2 );
	}

	/**
	 * Decides whether Application Passwords are available site-wide.
	 *
	 * Returns the original $available value in all cases except when:
	 * - LSX_MCP_ENABLED is true
	 * - LSX_MCP_ENABLE_APPLICATION_PASSWORDS is true
	 * - Environment type is local or development
	 * - The current host is local-like or ends in the allowed dev suffix
	 *
	 * @param bool $available Current availability value.
	 * @return bool
	 */
	public static function allow_application_passwords( $available ) {
		if ( ! LSX_MCP_UI_Environment::is_safe_for_application_passwords() ) {
			return $available;
		}
		return true;
	}

	/**
	 * Decides whether Application Passwords are available for a specific user.
	 *
	 * Only returns true for:
	 * - The dedicated MCP agent user (mcp-dev-agent or LSX_MCP_DEDICATED_USER_LOGIN).
	 * - Administrators with manage_options (fallback when no dedicated user exists).
	 *
	 * @param bool    $available Current availability value.
	 * @param WP_User $user      The user being checked.
	 * @return bool
	 */
	public static function allow_application_passwords_for_user( $available, $user ) {
		if ( ! LSX_MCP_UI_Environment::is_safe_for_application_passwords() ) {
			return $available;
		}

		if ( ! ( $user instanceof WP_User ) || ! $user->exists() ) {
			return $available;
		}

		if ( self::user_is_dedicated_mcp_agent( $user ) ) {
			return true;
		}

		if ( user_can( $user, LSX_MCP_UI_Environment::get_required_capability() ) ) {
			return true;
		}

		return $available;
	}

	/**
	 * Returns true when the given user is the configured dedicated MCP agent.
	 *
	 * @param WP_User $user
	 * @return bool
	 */
	private static function user_is_dedicated_mcp_agent( WP_User $user ) {
		$login = LSX_MCP_UI_Environment::get_dedicated_user_login();
		return $user->user_login === $login;
	}
}
