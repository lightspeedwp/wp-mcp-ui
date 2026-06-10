<?php
/**
 * Ability: lightspeed/block-theme-audit
 *
 * Returns block-theme implementation signals: templates, template parts,
 * patterns, style variations, theme.json key sections, and dark mode presence.
 * Uses file existence and safe JSON decoding — does not fully parse large files.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Ability_Block_Theme_Audit
 */
class LSX_MCP_UI_Ability_Block_Theme_Audit {

	public static function register() {
		wp_register_ability( 'lightspeed/block-theme-audit', array(
			'label'       => 'Block Theme Audit',
			'description' => 'Returns block-theme signals: templates, parts, patterns, style variations, and theme.json section presence.',
			'category'    => 'lightspeed',
			'meta'        => array( 'mcp' => array( 'public' => false ) ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(),
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'is_block_theme'        => array( 'type' => 'boolean' ),
					'has_theme_json'        => array( 'type' => 'boolean' ),
					'has_templates'         => array( 'type' => 'boolean' ),
					'has_parts'             => array( 'type' => 'boolean' ),
					'has_patterns'          => array( 'type' => 'boolean' ),
					'has_style_variations'  => array( 'type' => 'boolean' ),
					'templates'             => array( 'type' => 'array' ),
					'parts'                 => array( 'type' => 'array' ),
					'patterns'              => array( 'type' => 'array' ),
					'style_variations'      => array( 'type' => 'array' ),
					'theme_json_analysis'   => array( 'type' => 'object' ),
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
		$theme_dir = get_template_directory();

		$templates_dir = $theme_dir . '/templates';
		$parts_dir     = $theme_dir . '/parts';
		$patterns_dir  = $theme_dir . '/patterns';
		$styles_dir    = $theme_dir . '/styles';
		$theme_json    = $theme_dir . '/theme.json';

		$templates        = self::list_dir( $templates_dir, '*.html' );
		$parts            = self::list_dir( $parts_dir, '*.html' );
		$patterns         = self::list_dir( $patterns_dir, '*.php' );
		$style_variations = self::list_dir( $styles_dir, '*.json' );

		$has_theme_json = file_exists( $theme_json );
		$json_analysis  = self::analyse_theme_json( $has_theme_json ? $theme_json : null );

		return array(
			'is_block_theme'       => function_exists( 'wp_is_block_theme' ) ? wp_is_block_theme() : false,
			'has_theme_json'       => $has_theme_json,
			'has_templates'        => ! empty( $templates ),
			'has_parts'            => ! empty( $parts ),
			'has_patterns'         => ! empty( $patterns ),
			'has_style_variations' => ! empty( $style_variations ),
			'templates'            => $templates,
			'parts'                => $parts,
			'patterns'             => $patterns,
			'style_variations'     => $style_variations,
			'theme_json_analysis'  => $json_analysis,
		);
	}

	/**
	 * Returns a flat list of basenames matching a glob pattern in a directory.
	 */
	private static function list_dir( $dir, $pattern ) {
		if ( ! is_dir( $dir ) ) {
			return array();
		}
		$files = glob( $dir . '/' . $pattern );
		if ( ! $files ) {
			return array();
		}
		return array_values( array_map( 'basename', $files ) );
	}

	/**
	 * Safely decodes theme.json and reports which top-level setting sections exist.
	 */
	private static function analyse_theme_json( $path ) {
		$result = array(
			'has_color_palette'    => false,
			'has_typography'       => false,
			'has_spacing'          => false,
			'has_dark_variation'   => false,
			'schema_version'       => null,
		);

		// Check dark.json style variation separately (not part of theme.json).
		$styles_dir = get_template_directory() . '/styles';
		$result['has_dark_variation'] = file_exists( $styles_dir . '/dark.json' );

		if ( null === $path || ! file_exists( $path ) ) {
			return $result;
		}

		// Limit read to first 64 KB to avoid parsing huge files.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$raw = file_get_contents( $path, false, null, 0, 65536 );
		if ( false === $raw ) {
			return $result;
		}

		$json = json_decode( $raw, true );
		if ( ! is_array( $json ) ) {
			return $result;
		}

		$result['schema_version']   = $json['version'] ?? null;
		$settings                    = $json['settings'] ?? array();
		$result['has_color_palette'] = ! empty( $settings['color']['palette'] );
		$result['has_typography']    = ! empty( $settings['typography'] );
		$result['has_spacing']       = ! empty( $settings['spacing'] );

		return $result;
	}
}
