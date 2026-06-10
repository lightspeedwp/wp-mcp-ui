<?php
/**
 * Ability: lightspeed/content-readiness
 *
 * Returns content QA signals: missing titles, empty content, missing featured
 * images, missing excerpts, and missing SEO meta (Yoast-compatible if available).
 * Does not require Yoast – detects common SEO meta keys safely if present.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Ability_Content_Readiness
 */
class LSX_MCP_UI_Ability_Content_Readiness {

	public static function register() {
		wp_register_ability( 'lightspeed/content-readiness', array(
			'label'       => 'Content Readiness',
			'description' => 'Returns content QA signals: missing titles, empty content/excerpt, missing featured image, and missing SEO meta per post.',
			'category'    => 'lightspeed',
			'meta'        => array( 'mcp' => array( 'public' => false ) ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'post_types' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => 'Post type slugs. Defaults to all public types.' ),
					'limit'      => array( 'type' => 'integer', 'description' => 'Maximum items. Default 100, max 500.' ),
				),
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'items'   => array( 'type' => 'array' ),
					'summary' => array( 'type' => 'object' ),
					'total'   => array( 'type' => 'integer' ),
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
		$limit = min( 500, max( 1, isset( $input['limit'] ) ? intval( $input['limit'] ) : 100 ) );

		$public_types = array_keys( get_post_types( array( 'public' => true ), 'names' ) );
		unset( $public_types[ array_search( 'attachment', $public_types, true ) ] );

		if ( ! empty( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$requested  = array_map( 'sanitize_key', $input['post_types'] );
			$post_types = array_values( array_intersect( $requested, $public_types ) );
		} else {
			$post_types = array_values( $public_types );
		}

		if ( empty( $post_types ) ) {
			return array( 'items' => array(), 'summary' => array(), 'total' => 0 );
		}

		$q = new WP_Query( array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );

		$items   = array();
		$summary = array(
			'total'                    => 0,
			'missing_title'            => 0,
			'empty_content'            => 0,
			'missing_excerpt'          => 0,
			'missing_featured_image'   => 0,
			'missing_seo_title'        => 0,
			'missing_meta_description' => 0,
		);

		foreach ( $q->posts as $post ) {
			$missing_title          = '' === trim( $post->post_title );
			$empty_content          = '' === trim( $post->post_content );
			$missing_excerpt        = '' === trim( $post->post_excerpt );
			$missing_featured_image = ! has_post_thumbnail( $post->ID );

			// Detect SEO meta without hard-coding a dependency.
			$seo_title = self::get_seo_title( $post->ID );
			$seo_desc  = self::get_seo_description( $post->ID );

			$missing_seo_title = ( null !== $seo_title ) && '' === trim( $seo_title );
			$missing_seo_desc  = ( null !== $seo_desc ) && '' === trim( $seo_desc );

			if ( $missing_title )          $summary['missing_title']++;
			if ( $empty_content )          $summary['empty_content']++;
			if ( $missing_excerpt )        $summary['missing_excerpt']++;
			if ( $missing_featured_image ) $summary['missing_featured_image']++;
			if ( $missing_seo_title )      $summary['missing_seo_title']++;
			if ( $missing_seo_desc )       $summary['missing_meta_description']++;
			$summary['total']++;

			$items[] = array(
				'id'                       => $post->ID,
				'type'                     => $post->post_type,
				'title'                    => $post->post_title,
				'url'                      => get_permalink( $post->ID ),
				'missing_title'            => $missing_title,
				'empty_content'            => $empty_content,
				'missing_excerpt'          => $missing_excerpt,
				'missing_featured_image'   => $missing_featured_image,
				'seo_title_missing'        => $missing_seo_title,
				'seo_description_missing'  => $missing_seo_desc,
				'modified'                 => get_the_modified_date( 'Y-m-d', $post->ID ),
			);
		}

		return array(
			'items'   => $items,
			'summary' => $summary,
			'total'   => $q->found_posts,
		);
	}

	/**
	 * Safely retrieves the Yoast/RankMath/AIOSEO SEO title without a hard dependency.
	 * Returns null when no SEO plugin meta is present, empty string when the key exists but is blank.
	 */
	private static function get_seo_title( $post_id ) {
		// Yoast SEO
		$val = get_post_meta( $post_id, '_yoast_wpseo_title', true );
		if ( '' !== $val ) return $val;
		if ( metadata_exists( 'post', $post_id, '_yoast_wpseo_title' ) ) return '';

		// Rank Math
		$val = get_post_meta( $post_id, 'rank_math_title', true );
		if ( '' !== $val ) return $val;
		if ( metadata_exists( 'post', $post_id, 'rank_math_title' ) ) return '';

		// AIOSEO
		$val = get_post_meta( $post_id, '_aioseo_title', true );
		if ( '' !== $val ) return $val;
		if ( metadata_exists( 'post', $post_id, '_aioseo_title' ) ) return '';

		return null;
	}

	/**
	 * Safely retrieves the SEO meta description without a hard dependency.
	 */
	private static function get_seo_description( $post_id ) {
		// Yoast SEO
		$val = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
		if ( '' !== $val ) return $val;
		if ( metadata_exists( 'post', $post_id, '_yoast_wpseo_metadesc' ) ) return '';

		// Rank Math
		$val = get_post_meta( $post_id, 'rank_math_description', true );
		if ( '' !== $val ) return $val;
		if ( metadata_exists( 'post', $post_id, 'rank_math_description' ) ) return '';

		// AIOSEO
		$val = get_post_meta( $post_id, '_aioseo_description', true );
		if ( '' !== $val ) return $val;
		if ( metadata_exists( 'post', $post_id, '_aioseo_description' ) ) return '';

		return null;
	}
}
