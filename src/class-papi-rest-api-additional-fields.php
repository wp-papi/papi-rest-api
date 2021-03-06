<?php

/**
 * Class to add additional fields to existing
 * WordPress object type.
 */
class Papi_REST_API_Additional_Fields {

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->setup_actions();
		$this->controller = new Papi_REST_API_Fields_Controller;
	}

	/**
	 * Setup actions.
	 */
	protected function setup_actions() {
		add_action( 'rest_api_init', [$this, 'setup_fields'] );
	}

	/**
	 * Setup REST API fields.
	 */
	public function setup_fields() {
		if ( ! function_exists( 'register_api_field' ) ) {
			return;
		}

		$post_types = papi_get_post_types();

		foreach ( $post_types as $post_type ) {
			register_api_field( $post_type, 'fields', [
				'get_callback' => [$this, 'get_fields']
			] );

			register_api_field( $post_type, 'page_type', [
				'get_callback' => [$this, 'get_page_type']
			] );
		}
	}

	/**
	 * Get fields.
	 *
	 * @param  array           $data
	 * @param  string          $field_name
	 * @param  WP_REST_Request $request
	 *
	 * @return array
	 */
	public function get_fields( array $data, $field_name, WP_REST_Request $request ) {
		$page   = papi_get_page( $data['ID'] );
		$list   = papi_get_slugs( $data['ID'], true );
		$fields = [];

		foreach ( $list as $slug ) {
			$property = $page->get_property( $slug );

			if ( papi_is_property( $property ) && $property->current_user_can() ) {
				$fields[$slug] = papi_get_field( $data['ID'], $slug );
			}
		}

		return $fields;
	}

	/**
	 * Get page type.
	 *
	 * @param  array           $data
	 * @param  string          $field_name
	 * @param  WP_REST_Request $request
	 *
	 * @return array
	 */
	public function get_page_type( array $data, $field_name, WP_REST_Request $request ) {
		return papi_get_page_type_id( $data['ID'] ) ?: '';
	}
}

new Papi_REST_API_Additional_Fields;
