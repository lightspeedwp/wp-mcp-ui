<?php
/**
 * Ability: lightspeed/site-summary
 *
 * Returns a safe summary of the current WordPress site for agent context.
 * Does not expose secrets, credentials, filesystem paths, or env vars.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Ability_Site_Summary
 */
class LSX_MCP_UI_Ability_Site_Summary {

	public static function register() {
		wp_register_ability( 'lightspeed/site-summary', array(
			'label'       => 'Site Summary',
			'description' => 'Returns a safe summary of the current WordPress site: name, URLs, versions, environment, active theme, multisite status, and MCP adapter availability.',
			'category'    => 'lightspeed',
			'meta'        => array( 'mcp' => array( 'public' => false ) ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(),
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'site_name'              => array( 'type' => 'string' ),
					'site_url'               => array( 'type' => 'string' ),
					'home_url'               => array( 'type' => 'string' ),
					'wp_version'             => array( 'type' => 'string' ),
					'php_version'            => array( 'type' => 'string' ),
					'environment_type'       => array( 'type' => 'string' ),
					'active_theme'           => array( 'type' => 'object' ),
					'is_multisite'           => array( 'type' => 'boolean' ),
					'debug_mode'             => array( 'type' => 'boolean' ),
					'search_engine_visible'  => array( 'type' => 'boolean' ),
					'permalink_structure'    => array( 'type' => 'string' ),
					'active_plugin_count'    => array( 'type' => 'integer' ),
					'woocommerce_active'     => array( 'type' => 'boolean' ),
					'mcp_adapter_available'  => array( 'type' => 'boolean' ),
					'lightspeed_server_available' => array( 'type' => 'boolean' ),
				),
			),
			'permission_callback' => array( __CLASS__, 'permission_callback' ),
			'execute_callback'    => array( __CLASS__, 'execute' ),
		) );
	}

	public static function permission_callback() {
		return is_user_logged_in() && current_user_can( LSX_MCP_UI_Environment::get_testing_server_capability() );
	}

	public static function execute( $input ) {
		$theme        = wp_get_theme();
		$parent_theme = $theme->parent();

		$theme_data = array(
			'name'    => $theme->get( 'Name' ),
			'version' => $theme->get( 'Version' ),
		);
		if ( $parent_theme ) {
			$theme_data['parent_name']    = $parent_theme->get( 'Name' );
			$theme_data['parent_version'] = $parent_theme->get( 'Version' );
		}

		return array(
			'site_name'             => get_bloginfo( 'name' ),
			'site_url'              => get_site_url(),
			'home_url'              => get_home_url(),
			'wp_version'            => get_bloginfo( 'version' ),
			'php_version'           => PHP_VERSION,
			'environment_type'      => LSX_MCP_UI_Environment::get_environment_type(),
			'active_theme'          => $theme_data,
			'is_multisite'          => is_multisite(),
			'debug_mode'            => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'search_engine_visible' => '1' === get_option( 'blog_public' ),
			'permalink_structure'   => get_option( 'permalink_structure' ),
			'active_plugin_count'   => count( get_option( 'active_plugins', array() ) ),
			'woocommerce_active'    => class_exists( 'WooCommerce' ),
			'mcp_adapter_available' => LSX_MCP_UI_Environment::is_adapter_available(),
			'lightspeed_server_available' => LSX_MCP_UI_Environment::is_safe_for_custom_server(),
		);
	}
}
