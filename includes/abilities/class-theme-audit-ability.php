<?php
/**
 * Ability: lightspeed/theme-audit
 *
 * Returns useful theme information for block theme development.
 * Uses relative paths by default; only shows absolute paths when
 * debug_paths=true and the user has manage_options.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Ability_Theme_Audit
 */
class LSX_MCP_UI_Ability_Theme_Audit {

	public static function register() {
		wp_register_ability( 'lightspeed/theme-audit', array(
			'label'       => 'Theme Audit',
			'description' => 'Returns theme info useful for block theme development: theme.json, template/part/pattern/style counts, and supported features.',
			'category'    => 'lightspeed',
			'meta'        => array( 'mcp' => array( 'public' => false ) ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'debug_paths' => array( 'type' => 'boolean', 'description' => 'Include absolute filesystem paths (requires manage_options). Default false.' ),
				),
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'active_theme'          => array( 'type' => 'object' ),
					'parent_theme'          => array( 'type' => 'object' ),
					'is_block_theme'        => array( 'type' => 'boolean' ),
					'has_theme_json'        => array( 'type' => 'boolean' ),
					'directories'           => array( 'type' => 'object' ),
					'theme_supports'        => array( 'type' => 'object' ),
					'counts'                => array( 'type' => 'object' ),
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
		$debug_paths  = ! empty( $input['debug_paths'] ) && current_user_can( 'manage_options' );

		$theme_dir   = get_template_directory();
		$style_dir   = get_stylesheet_directory();

		$dirs = array(
			'patterns'   => $theme_dir . '/patterns',
			'templates'  => $theme_dir . '/templates',
			'parts'      => $theme_dir . '/parts',
			'styles'     => $theme_dir . '/styles',
			'theme_json' => $theme_dir . '/theme.json',
		);

		$has_theme_json = file_exists( $dirs['theme_json'] );

		$dir_info = array();
		foreach ( $dirs as $key => $path ) {
			if ( $key === 'theme_json' ) {
				$dir_info['theme_json_exists'] = $has_theme_json;
				continue;
			}
			$dir_info[ $key . '_exists' ] = is_dir( $path );
		}

		$active_theme_data = array(
			'name'    => $theme->get( 'Name' ),
			'version' => $theme->get( 'Version' ),
		);
		if ( $debug_paths ) {
			$active_theme_data['template_directory']  = $theme_dir;
			$active_theme_data['stylesheet_directory'] = $style_dir;
		}

		if ( $parent_theme ) {
			$parent_data = array(
				'has_parent' => true,
				'name'       => $parent_theme->get( 'Name' ),
				'version'    => $parent_theme->get( 'Version' ),
			);
		} else {
			$parent_data = array( 'has_parent' => false );
		}

		$supports = array();
		$features = array( 'post-thumbnails', 'custom-logo', 'custom-header', 'custom-background', 'menus', 'widgets', 'editor-styles', 'responsive-embeds', 'html5', 'wp-block-styles', 'align-wide' );
		foreach ( $features as $feature ) {
			$supports[ $feature ] = current_theme_supports( $feature );
		}

		$counts = array(
			'php_patterns'         => is_dir( $dirs['patterns'] ) ? count( glob( $dirs['patterns'] . '/*.php' ) ?: array() ) : 0,
			'html_templates'       => is_dir( $dirs['templates'] ) ? count( glob( $dirs['templates'] . '/*.html' ) ?: array() ) : 0,
			'html_template_parts'  => is_dir( $dirs['parts'] ) ? count( glob( $dirs['parts'] . '/*.html' ) ?: array() ) : 0,
			'style_variations'     => is_dir( $dirs['styles'] ) ? count( glob( $dirs['styles'] . '/*.json' ) ?: array() ) : 0,
		);

		return array(
			'active_theme'   => $active_theme_data,
			'parent_theme'   => $parent_data,
			'is_block_theme' => function_exists( 'wp_is_block_theme' ) ? wp_is_block_theme() : false,
			'has_theme_json' => $has_theme_json,
			'directories'    => $dir_info,
			'theme_supports' => $supports,
			'counts'         => $counts,
		);
	}
}
