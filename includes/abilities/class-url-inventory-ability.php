<?php
/**
 * Ability: lightspeed/url-inventory
 *
 * Returns a simple URL inventory for QA and testing agents.
 * Only returns public post types and published (or explicitly requested) posts.
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Ability_URL_Inventory
 */
class LSX_MCP_UI_Ability_URL_Inventory {

	public static function register() {
		wp_register_ability( 'lightspeed/url-inventory', array(
			'label'       => 'URL Inventory',
			'description' => 'Returns public URLs across registered post types for QA and testing. Includes post ID, type, title, status, URL, and modified date.',
			'category'    => 'lightspeed',
			'meta'        => array( 'mcp' => array( 'public' => false ) ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'post_types' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => 'Post type slugs to include. Defaults to all public types.' ),
					'limit'      => array( 'type' => 'integer', 'description' => 'Maximum items to return. Default 100, max 500.' ),
					'status'     => array( 'type' => 'string',  'description' => 'Post status. Default publish.' ),
				),
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'items'      => array( 'type' => 'array' ),
					'count_by_type' => array( 'type' => 'object' ),
					'total'      => array( 'type' => 'integer' ),
					'limit'      => array( 'type' => 'integer' ),
					'limit_reached' => array( 'type' => 'boolean' ),
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
		$limit  = min( 500, max( 1, isset( $input['limit'] ) ? intval( $input['limit'] ) : 100 ) );
		$status = isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'publish';

		// Restrict non-published statuses to users who can read private posts.
		if ( 'publish' !== $status && ! current_user_can( 'read_private_posts' ) ) {
			$status = 'publish';
		}

		// Resolve post types.
		$public_types = array_keys( get_post_types( array( 'public' => true ), 'names' ) );
		unset( $public_types[ array_search( 'attachment', $public_types, true ) ] );

		if ( ! empty( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$requested = array_map( 'sanitize_key', $input['post_types'] );
			$post_types = array_values( array_intersect( $requested, $public_types ) );
		} else {
			$post_types = array_values( $public_types );
		}

		if ( empty( $post_types ) ) {
			return array( 'items' => array(), 'count_by_type' => array(), 'total' => 0, 'limit' => $limit, 'limit_reached' => false );
		}

		$q = new WP_Query( array(
			'post_type'      => $post_types,
			'post_status'    => $status,
			'posts_per_page' => $limit,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'no_found_rows'  => false,
		) );

		$items        = array();
		$count_by_type = array();

		foreach ( $q->posts as $post ) {
			$items[] = array(
				'id'       => $post->ID,
				'type'     => $post->post_type,
				'title'    => $post->post_title,
				'status'   => $post->post_status,
				'url'      => get_permalink( $post->ID ),
				'modified' => get_the_modified_date( 'Y-m-d H:i:s', $post->ID ),
			);
			$count_by_type[ $post->post_type ] = ( $count_by_type[ $post->post_type ] ?? 0 ) + 1;
		}

		return array(
			'items'         => $items,
			'count_by_type' => $count_by_type,
			'total'         => $q->found_posts,
			'limit'         => $limit,
			'limit_reached' => count( $items ) >= $limit,
		);
	}
}
