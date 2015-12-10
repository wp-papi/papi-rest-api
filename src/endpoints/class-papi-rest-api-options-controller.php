<?php

/**
 * Papi REST API options controller.
 */
class Papi_REST_API_Options_Controller extends Papi_REST_API_Controller {

	/**
	 * The rest route.
	 *
	 * @var string
	 */
	protected $route = 'options';

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
	 * Get filters.
	 *
	 * @return array
	 */
	protected function get_filters( WP_REST_Request $request ) {
		$filters = [
			'option_type' => ''
		];

		if ( is_array( $request['filter'] ) ) {
			$filters = array_merge( $filters, $request['filter'] );
		}

		return $filters;
	}

	/**
	 * Get option type property from a option slug.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return object
	 */
	public function get_option( WP_REST_Request $request ) {
		return $this->get_option_property( $request );
	}

	/**
	 * Get option property.
	 *
	 * @param  WP_REST_Request $request
	 * @param  string $slug
	 *
	 * @return object|WP_Error
	 */
	protected function get_option_property( WP_REST_Request $request ) {
		$page     = new Papi_Option_Page();
		$property = $page->get_property( $request['slug'] );

		if ( ! papi_is_property( $property ) ) {
			return new WP_Error( 'papi_slug_invalid', __( 'Option slug doesn\'t exist', 'papi-rest-api' ) , ['status' => 404] );
		}

		return $this->create_property_item( $request, $property, [
			'option_type' => $page->get_option_type()->get_id()
		] );
	}

	/**
	 * Get option types properties.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return array
	 */
	public function get_options( WP_REST_Request $request ) {
		$filters      = $this->get_filters( $request );
		$properties   = [];
		$option_types = papi_get_all_content_types( [
			'types' => 'option'
		] );

		foreach ( $option_types as $option_type ) {
			if ( ! papi_is_option_type( $option_type ) ) {
				continue;
			}

			// Allow empty option type filter. If page type id is not empty only the option type
			// that has the right id that match with the filter should be used.
			if ( ! empty( $filters['option_type'] ) && $filters['option_type'] !== $option_type->get_id() ) {
				continue;
			}

			foreach ( $option_type->get_boxes() as $box ) {
				foreach ( $box->properties as $property ) {
					if ( papi_is_property( $property ) ) {
						$properties[] = $this->create_property_item( $request, $property, [
							'option_type' => $option_type->get_id()
						] );
					}
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
	 * Register the options-related routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, $this->route, [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [$this, 'get_options']
		] );

		register_rest_route( $this->namespace, $this->route . '/(?P<slug>.+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [$this, 'get_option']
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
			return $this->get_option_property( $request );
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
