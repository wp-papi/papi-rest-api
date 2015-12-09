<?php

/**
 * Papi REST API options controller.
 */
class Papi_REST_API_Options_Controller extends Papi_REST_API_Controller {

	/**
	 * Register the options-related routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/options', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [$this, 'callback']
		] );

		register_rest_route( $this->namespace, '/options/(?P<option>.+)', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [$this, 'callback']
		] );
	}

	/**
	 * Handle the options endpoint callback.
	 *
	 * @param  \WP_REST_Request $request
	 */
	public function callback( WP_REST_Request $request ) {
		if ( $request['option'] ) {
			return [
				'value' => papi_get_option( $request['option'] )
			];
		}

		return $this->get_options_slugs();
	}

	/**
	 * Get all options slugs.
	 *
	 * @return array
	 */
	private function get_options_slugs() {
		$slugs        = [];
		$option_types = papi_get_all_content_types( [
			'type' => 'option'
		] );

		foreach ( $option_types as $option_type ) {
			if ( ! papi_is_option_type( $option_type ) ) {
				continue;
			}

			foreach ( $option_type->get_boxes() as $box ) {
				foreach ( $box->properties as $property ) {
					$slugs[] = $this->create_property_item( $property );
				}
			}
		}

		return $slugs;
	}

	/**
	 * Create property item that is returned to the REST API.
	 *
	 * @param  Papi_Core_Property $property
	 *
	 * @return object
	 */
	private function create_property_item( Papi_Core_Property $property ) {
		$item = [
			'title' => $property->title,
			'slug'  => $property->get_slug( true ),
			'type'  => $property->type
		];

		/**
		 * Modify the property item that is returned to the REST API.
		 *
		 * @param  array $item
		 */
		if ( $output = apply_filters( 'papi/rest/property_item', $item ) ) {
			$item = is_array( $output ) || is_object( $output ) ? $output : $item;
		}

		return (object) $item;
	}
}
