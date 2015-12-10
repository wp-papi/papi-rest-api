<?php

/**
 * Papi REST API base controller.
 */
abstract class Papi_REST_API_Controller {

	/**
	 * The rest route.
	 *
	 * @var string
	 */
	protected $route = '';

	/**
	 * Papi REST API namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'papi/v1';

	/**
	 * Register controller related routes.
	 */
	abstract public function register_routes();

	/**
	 * Create property item that is returned to the REST API.
	 *
	 * @param  WP_REST_Request    $request
	 * @param  Papi_Core_Property $property
	 * @param  array              $extra
	 *
	 * @return object
	 */
	protected function create_property_item( WP_REST_Request $request, Papi_Core_Property $property, array $extra = [] ) {
		$item   = [];
		$fields = ['slug', 'title', 'type', 'value'];

		if ( ! empty ( $request['fields'] ) && is_string( $request['fields'] ) ) {
			$fields = explode( ',', trim( $request['fields'] ) );
		}

		foreach ( $fields as $field ) {
			if ( $field === 'slug' ) {
				$item[$field] = $property->get_slug( true );

				continue;
			}

			if ( $field === 'value' ) {
				if ( $this->route === 'options' ) {
					$item[$field] = papi_get_option( $property->get_slug( true ), null );
				} else {
					$item[$field] = papi_get_field( $property->get_post_id(), $property->get_slug( true ), null );
				}

				continue;
			}

			$value = $property->$field;

			if ( is_null( $value ) && isset( $extra[$field] ) ) {
				$value = $extra[$field];
			}

			if ( is_callable( $value ) ) {
				continue;
			}

			if ( is_object( $value ) && get_class( $value ) !== 'stdClass' ) {
				continue;
			}

			$item[$field] = $value;
		}

		/**
		 * Modify the property item that is returned to the REST API.
		 *
		 * @param  array $item
		 */
		if ( $output = apply_filters( 'papi/rest/property_item', $item ) ) {
			$item = is_array( $output ) || is_object( $output ) ? (array) $output : $item;
		}

		ksort( $item );

		// $item = array_merge( $item, $extra );

		return $this->prepare_links( (object)$item, $property->get_slug( true ) );
	}

	/**
	 * Prepare fields that should be returned to the client.
	 *
	 * @param  WP_REST_Request $request
	 * @param  string          $item
	 * @param  array           $extra
	 *
	 * @return object
	 */
	protected function prepare_fields( WP_REST_Request $request, Papi_Core_Property $property, $item, array $extra = [] ) {
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param  object $items
	 * @param  string $slug
	 *
	 * @return object
	 */
	protected function prepare_links( $items, $slug ) {
		$items->_links = [
			'self' => [
				[
					'href' => rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->route, $slug ) )
				]
			],
			'collection' => [
				[
					'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->route ) )
				]
			]
		];

		return $items;
	}

}
