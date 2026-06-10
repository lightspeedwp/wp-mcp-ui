<?php
/**
 * Plugin settings stored in wp_options.
 *
 * Constants take precedence over saved options for all keys. This lets
 * wp-config.php act as a hard override while leaving the admin UI as the
 * default configuration path — meaning .lightspeedwp.dev sites never need
 * to edit wp-config.php unless they want to lock a value.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Settings
 */
class LSX_MCP_UI_Settings {

	const OPTION_KEY    = 'lsx_mcp_settings';
	const NONCE_ACTION  = 'lsx_mcp_save_settings';
	const NONCE_NAME    = '_lsx_mcp_nonce';

	// ── Getters ───────────────────────────────────────────────────────────────

	/**
	 * Get a single setting. Constants override the saved option.
	 *
	 * Keys: enabled, enable_application_passwords, enable_custom_server,
	 *       dedicated_user_login, allowed_dev_suffix
	 *
	 * @param string $key
	 * @param mixed  $default  Returned only when the key is unknown.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$options = get_option( self::OPTION_KEY, array() );

		switch ( $key ) {
			case 'enabled':
				if ( defined( 'LSX_MCP_ENABLED' ) ) {
					return true === LSX_MCP_ENABLED;
				}
				return (bool) ( $options['enabled'] ?? false );

			case 'enable_application_passwords':
				if ( defined( 'LSX_MCP_ENABLE_APPLICATION_PASSWORDS' ) ) {
					return true === LSX_MCP_ENABLE_APPLICATION_PASSWORDS;
				}
				return (bool) ( $options['enable_application_passwords'] ?? false );

			case 'enable_custom_server':
				if ( defined( 'LSX_MCP_ENABLE_CUSTOM_SERVER' ) ) {
					return true === LSX_MCP_ENABLE_CUSTOM_SERVER;
				}
				// Default true when MCP is enabled and no option is saved yet.
				return (bool) ( $options['enable_custom_server'] ?? true );

			case 'dedicated_user_login':
				if ( defined( 'LSX_MCP_DEDICATED_USER_LOGIN' ) ) {
					return (string) LSX_MCP_DEDICATED_USER_LOGIN;
				}
				$v = $options['dedicated_user_login'] ?? 'mcp-dev-agent';
				return sanitize_text_field( $v );

			case 'allowed_dev_suffix':
				if ( defined( 'LSX_MCP_ALLOWED_DEV_DOMAIN_SUFFIX' ) ) {
					return (string) LSX_MCP_ALLOWED_DEV_DOMAIN_SUFFIX;
				}
				$v = $options['allowed_dev_suffix'] ?? '.lightspeedwp.dev';
				return sanitize_text_field( $v );
		}

		return $default;
	}

	/**
	 * Returns the raw saved option value (no constant override).
	 * Used to pre-fill admin form fields.
	 */
	public static function get_raw( $key, $default = null ) {
		$options = get_option( self::OPTION_KEY, array() );
		return $options[ $key ] ?? $default;
	}

	/**
	 * Whether the given key is currently overridden by a wp-config constant.
	 * When true, the admin form field should be shown as read-only.
	 */
	public static function is_constant_override( $key ) {
		static $map = null;
		if ( null === $map ) {
			$map = array(
				'enabled'                       => 'LSX_MCP_ENABLED',
				'enable_application_passwords'  => 'LSX_MCP_ENABLE_APPLICATION_PASSWORDS',
				'enable_custom_server'          => 'LSX_MCP_ENABLE_CUSTOM_SERVER',
				'dedicated_user_login'          => 'LSX_MCP_DEDICATED_USER_LOGIN',
				'allowed_dev_suffix'            => 'LSX_MCP_ALLOWED_DEV_DOMAIN_SUFFIX',
			);
		}
		return isset( $map[ $key ] ) && defined( $map[ $key ] );
	}

	// ── Save ─────────────────────────────────────────────────────────────────

	/**
	 * Save settings from POST data.
	 *
	 * Must be called inside an admin context. Verifies nonce and capability.
	 * Keys overridden by constants are not written (the constant value remains
	 * authoritative and is not persisted to the database).
	 *
	 * @return true|WP_Error  true on success, WP_Error on failure.
	 */
	public static function save_from_post() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'permission', __( 'Insufficient permissions.', 'wp-mcp-ui' ) );
		}

		$nonce = isset( $_POST[ self::NONCE_NAME ] ) ? sanitize_key( $_POST[ self::NONCE_NAME ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return new WP_Error( 'nonce', __( 'Security check failed. Please reload and try again.', 'wp-mcp-ui' ) );
		}

		// Read existing to preserve any keys we are not writing.
		$existing = get_option( self::OPTION_KEY, array() );

		$data = $existing;

		if ( ! self::is_constant_override( 'enabled' ) ) {
			$data['enabled'] = ! empty( $_POST['lsx_mcp_enabled'] );
		}
		if ( ! self::is_constant_override( 'enable_application_passwords' ) ) {
			$data['enable_application_passwords'] = ! empty( $_POST['lsx_mcp_enable_application_passwords'] );
		}
		if ( ! self::is_constant_override( 'enable_custom_server' ) ) {
			$data['enable_custom_server'] = ! empty( $_POST['lsx_mcp_enable_custom_server'] );
		}
		if ( ! self::is_constant_override( 'dedicated_user_login' ) ) {
			$v = sanitize_text_field( $_POST['lsx_mcp_dedicated_user_login'] ?? 'mcp-dev-agent' );
			$data['dedicated_user_login'] = $v ?: 'mcp-dev-agent';
		}
		if ( ! self::is_constant_override( 'allowed_dev_suffix' ) ) {
			$v = sanitize_text_field( $_POST['lsx_mcp_allowed_dev_suffix'] ?? '.lightspeedwp.dev' );
			// Ensure it starts with a dot.
			if ( ! empty( $v ) && '.' !== $v[0] ) {
				$v = '.' . $v;
			}
			$data['allowed_dev_suffix'] = $v ?: '.lightspeedwp.dev';
		}

		update_option( self::OPTION_KEY, $data, false );
		return true;
	}
}
