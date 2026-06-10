<?php
/**
 * Ability: lightspeed/plugin-inventory
 *
 * Returns a plugin inventory for QA and dev debugging.
 * Does not include license keys or plugin options.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Ability_Plugin_Inventory
 */
class LSX_MCP_UI_Ability_Plugin_Inventory {

	public static function register() {
		wp_register_ability( 'lightspeed/plugin-inventory', array(
			'label'       => 'Plugin Inventory',
			'description' => 'Returns a list of plugins (name, version, URI, author) for QA and debugging. Optionally includes inactive plugins.',
			'category'    => 'lightspeed',
			'meta'        => array( 'mcp' => array( 'public' => false ) ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'include_inactive' => array( 'type' => 'boolean', 'description' => 'Include inactive plugins. Default false.' ),
				),
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'active_plugins'     => array( 'type' => 'array' ),
					'inactive_plugins'   => array( 'type' => 'array' ),
					'total_active'       => array( 'type' => 'integer' ),
					'woocommerce_active' => array( 'type' => 'boolean' ),
					'wordfence_active'   => array( 'type' => 'boolean' ),
					'mcp_adapter_active' => array( 'type' => 'boolean' ),
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
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins   = get_plugins();
		$active_files  = get_option( 'active_plugins', array() );
		$include_inactive = ! empty( $input['include_inactive'] );

		$active   = array();
		$inactive = array();

		foreach ( $all_plugins as $file => $data ) {
			$plugin = array(
				'name'       => $data['Name'],
				'version'    => $data['Version'],
				'plugin_uri' => $data['PluginURI'],
				'author'     => $data['Author'],
				'file'       => $file,
			);

			if ( in_array( $file, $active_files, true ) ) {
				$active[] = $plugin;
			} elseif ( $include_inactive ) {
				$inactive[] = $plugin;
			}
		}

		return array(
			'active_plugins'     => $active,
			'inactive_plugins'   => $inactive,
			'total_active'       => count( $active ),
			'woocommerce_active' => class_exists( 'WooCommerce' ),
			'wordfence_active'   => class_exists( 'WFWAF_AUTO_PREPEND' ) || defined( 'WORDFENCE_VERSION' ),
			'mcp_adapter_active' => LSX_MCP_UI_Environment::is_adapter_available(),
		);
	}
}
