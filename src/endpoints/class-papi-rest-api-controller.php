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
	 * @param  Papi_Core_Property $property
	 * @param  array $extra
	 *
	 * @return object
	 */
	protected function create_property_item( Papi_Core_Property $property, array $extra = [] ) {
		$func = $this->route === 'options' ? 'papi_get_option' : 'papi_get_field';

		$item = [
			'title' => $property->title,
			'slug'  => $property->get_slug( true ),
			'type'  => $property->type
		];

		if ( $this->route === 'options' ) {
			$item['value'] = papi_get_option( $property->get_slug( true ), null );
		} else {
			$item['value'] = papi_get_field( $property->get_post_id(), $property->get_slug( true ), null );
		}

		$item = array_merge( $item, $extra );

		/**
		 * Modify the property item that is returned to the REST API.
		 *
		 * @param  array $item
		 */
		if ( $output = apply_filters( 'papi/rest/property_item', $item ) ) {
			$item = is_array( $output ) || is_object( $output ) ? (array) $output : $item;
		}

		ksort( $item );

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
					'href' => rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->route, $items->slug ) )
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
