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
				'get_callback'    => [$this, 'get_fields']
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
		$boxes  = papi_get_slugs( $data['ID'] );
		$fields = [];

		foreach ( $boxes as $list ) {
			foreach ( $list as $slug ) {
				$property = $page->get_property( $slug );

				if ( papi_is_property( $property ) && $property->current_user_can() ) {
					$fields[$slug] = papi_get_field( $data['ID'], $slug );
				}
			}
		}

		return $fields;
	}
}

new Papi_REST_API_Additional_Fields;
