<?php

/**
 * Papi REST API options controller.
 */
class Papi_REST_API_Options_Controller extends Papi_REST_API_Controller {

	/**
	 * Delete option.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return object|WP_Error
	 */
	public function delete_option( WP_REST_Request $request ) {
		if ( papi_delete_option( $request['slug'], $request['value'] ) ) {
			return (object) ['deleted' => true];
		}

		return new WP_Error( 'papi_delete_option_error', __( 'Delete option value did not work. The property may not be found', 'papi-rest-api' ), ['status' => 500] );
	}

	/**
	 * Check if a given request has access to delete a option value.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function delete_option_permissions_check( WP_REST_Request $request ) {
		foreach ( $this->get_option_types_capabilities() as $capability ) {
			if ( ! current_user_can( $capability ) ) {
				return new WP_Error( 'papi_cannot_delete_option', __( 'Sorry, you are not allowed to delete the option value', 'papi-rest-api' ), ['status' => 403] );
			}
		}

		return true;
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
	 * Get options.
	 *
	 * @param  \WP_REST_Request $request
	 *
	 * @return array
	 */
	public function get_options( WP_REST_Request $request ) {
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
	 * @return object|WP_Error
	 */
	protected function get_option_property( $slug ) {
		$page     = new Papi_Option_Page();
		$property = $page->get_property( $slug );

		if ( ! papi_is_property( $property ) ) {
			return new WP_Error( 'papi_slug_invalid', __( 'Option slug doesn\'t exist', 'papi-rest-api' ) , ['status' => 404] );
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
	 * Get option types capabilities.
	 *
	 * @return array
	 */
	protected function get_option_types_capabilities() {
		$capabilities = [];
		$option_types = papi_get_all_content_types( [
			'type' => 'option'
		] );

		foreach ( $option_types as $option_type ) {
			$capabilities[] = empty( $option_type->capability ) || ! is_string( $option_type->capability ) ? 'manage_options' : $option_type->capability;
		}

		return array_unique( $capabilities );
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

	/**
	 * Register the options-related routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/options', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [$this, 'get_options']
		] );

		register_rest_route( $this->namespace, '/options/(?P<slug>.+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [$this, 'get_options']
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [$this, 'update_option'],
				'permission_callback' => [$this, 'update_option_permissions_check']
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [$this, 'delete_option'],
				'permission_callback' => [$this, 'delete_option_permissions_check']
			]
		] );
	}

	/**
	 * Update option.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return object|WP_Error
	 */
	public function update_option( WP_REST_Request $request ) {
		if ( papi_update_option( $request['slug'], $request['value'] ) ) {
			return $this->get_option_property( $request['slug'] );
		}

		return new WP_Error( 'papi_update_option_error', __( 'Update option value did not work. The property may not be found', 'papi-rest-api' ), ['status' => 500] );
	}

	/**
	 * Check if a given request has access to update a option value.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function update_option_permissions_check( WP_REST_Request $request ) {
		foreach ( $this->get_option_types_capabilities() as $capability ) {
			if ( ! current_user_can( $capability ) ) {
				return new WP_Error( 'papi_cannot_update_option', __( 'Sorry, you are not allowed to update the option value', 'papi-rest-api' ), ['status' => 403] );
			}
		}

		return true;
	}
}
