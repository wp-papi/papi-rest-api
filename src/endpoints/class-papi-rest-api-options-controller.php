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

		register_rest_route( $this->namespace, '/options/(?P<slug>.+)', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [$this, 'callback']
		] );
	}

	/**
	 * Handle the options endpoint callback.
	 *
	 * @param  \WP_REST_Request $request
	 *
	 * @return array
	 */
	public function callback( WP_REST_Request $request ) {
		if ( $request['slug'] ) {
			return $this->get_option_property( $request['slug'] );
		}

		return $this->get_options_properties();
	}

	/**
	 * Get option property.
	 *
	 * @param  string $slug
	 *
	 * @return array
	 */
	protected function get_option_property( $slug ) {
		$page     = new Papi_Option_Page();
		$property = $page->get_property( $slug );

		if ( ! papi_is_property( $property ) ) {
			return;
		}

		return $this->create_property_item( $property );
	}

	/**
	 * Get all options properties.
	 *
	 * @return array
	 */
	protected function get_options_properties() {
		$properties   = [];
		$option_types = papi_get_all_content_types( [
			'type' => 'option'
		] );

		foreach ( $option_types as $option_type ) {
			if ( ! papi_is_option_type( $option_type ) ) {
				continue;
			}

			foreach ( $option_type->get_boxes() as $box ) {
				foreach ( $box->properties as $property ) {
					$properties[] = $this->create_property_item( $property );
				}
			}
		}

		return $properties;
	}

	/**
	 * Create property item that is returned to the REST API.
	 *
	 * @param  Papi_Core_Property $property
	 *
	 * @return object
	 */
	protected function create_property_item( Papi_Core_Property $property ) {
		$item = [
			'title' => $property->title,
			'slug'  => $property->get_slug( true ),
			'type'  => $property->type,
			'value' => papi_get_option( $property->get_slug( true ), null )
		];

		/**
		 * Modify the property item that is returned to the REST API.
		 *
		 * @param  array $item
		 */
		if ( $output = apply_filters( 'papi/rest/property_item', $item ) ) {
			$item = is_array( $output ) || is_object( $output ) ? $output : $item;
		}

		return $this->prepare_links( (object) $item );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param  object $items
	 *
	 * @return object
	 */
	protected function prepare_links( $items ) {
		$items->_links = [
			'self' => [
				[
					'href' => rest_url( sprintf( '%s/%s/%s', $this->namespace, 'options', $items->slug ) )
				]
			],
			'collection' => [
				[
					'href' => rest_url( sprintf( '%s/%s', $this->namespace, 'options' ) )
				]
			]
		];

		return $items;
	}
}
