<?php
class ZIOR_Blocks_Routes {
	/**
	 * Endpoint namespace.
	 *
	 */
	const REST_NAMESPACE = 'zior-blocks/v1';

	/**
	 * Get things started.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Registers REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		$routes = $this->get_routes();
		foreach( $routes as $key => $route ){
			register_rest_route( self::REST_NAMESPACE, $key, $route );
		}
	}

	/**
	 * Handles request permission. 
	 * @param \WP_REST_Request $request
	 * @return boolean
	 */
	public function check_permission( WP_REST_Request $request ) {
		$permission = true;
		$nonce      = $request->get_header( 'x_wp_nonce' );
		if ( wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			$permission = true;
		}

		return $permission;
	}

	public function acf_fields( \WP_REST_Request $request ) {
		$acf_fields = $this->get_acf_field_groups( $this->get_supported_fields() );
		return new \WP_REST_Response( $acf_fields, 200 );
	}

	public function get_routes() {
		$routes = [
			'acf-fields' => [
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'acf_fields' ],
				'permission_callback' => [ $this, 'check_permission' ]
			],
		];
	
		return apply_filters( 'zior_blocks_get_routes', $routes );
	}

		/**
	 * @param array $types
	 *
	 * @return array
	 */
	public static function get_acf_field_groups( $types ) {

		// ACF >= 5.0.0
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$acf_groups = acf_get_field_groups();
		} else {
			$acf_groups = apply_filters( 'acf/get_field_groups', [] );
		}

		$groups = [];

		$options_page_groups_ids = [];

		if ( function_exists( 'acf_options_page' ) ) {
			$pages = acf_options_page()->get_pages();
			foreach ( $pages as $slug => $page ) {
				$options_page_groups = acf_get_field_groups( [
					'options_page' => $slug,
				] );

				foreach ( $options_page_groups as $options_page_group ) {
					$options_page_groups_ids[] = $options_page_group['ID'];
				}
			}
		}

		foreach ( $acf_groups as $acf_group ) {
			// ACF >= 5.0.0
			if ( function_exists( 'acf_get_fields' ) ) {
				if ( isset( $acf_group['ID'] ) && ! empty( $acf_group['ID'] ) ) {
					$fields = acf_get_fields( $acf_group['ID'] );
				} else {
					$fields = acf_get_fields( $acf_group );
				}
			} else {
				$fields = apply_filters( 'acf/field_group/get_fields', [], $acf_group['id'] );
			}

			$options = [];

			if ( ! is_array( $fields ) ) {
				continue;
			}

			$has_option_page_location = in_array( $acf_group['ID'], $options_page_groups_ids, true );
			$is_only_options_page = $has_option_page_location && 1 === count( $acf_group['location'] );

			foreach ( $fields as $field ) {
				if ( ! in_array( $field['type'], $types, true ) ) {
					continue;
				}
				
				$options[] = [
					'label' => $field['label'],
					'value' => $field['key']
				];
			}

			if ( empty( $options ) ) {
				continue;
			}

			$groups[] = [
				'label' => $acf_group['title'],
				'value' => $options,
			];
		}

		return $groups;
	}

	public function get_supported_fields() {
		return [
			'text',
			'image',
			'file',
			'page_link',
			'post_object',
			'relationship',
			'taxonomy',
			'url',
		];
	}
}

( new ZIOR_Blocks_Routes() );
