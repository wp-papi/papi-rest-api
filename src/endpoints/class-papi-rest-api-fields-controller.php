<?php

/**
 * Papi REST API fields controller.
 */
class Papi_REST_API_Fields_Controller extends Papi_REST_API_Controller {

	/**
	 * The rest route.
	 *
	 * @var string
	 */
	protected $route = 'fields';

	/**
	 * Register the options-related routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, $this->route . '/(?P<id>[\d]+)', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [$this, 'get_fields']
		] );

		register_rest_route( $this->namespace, $this->route . '/(?P<id>[\d]+)/(?P<slug>.+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [$this, 'get_field']
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [$this, 'update_field']
				// 'permission_callback' => [$this, 'update_option_permissions_check']
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [$this, 'delete_field']
				// 'permission_callback' => [$this, 'delete_option_permissions_check']
			]
		] );
	}

	/**
	 * Delete a field value on a post.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function delete_field( WP_REST_Request $request ) {

	}

	/**
	 * Get page type property from a field slug.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return object
	 */
	public function get_field( WP_REST_Request $request ) {
		return $this->get_page_property( $request );
	}

	/**
	 * Get page types properties.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function get_fields( WP_REST_Request $request ) {
		$filters    = $this->get_filters( $request );
		$properties = [];
		$page_type  = papi_get_page_type_by_post_id( $request['id'] );

		if ( ! papi_is_page_type( $page_type ) ) {
			return new WP_Error( 'papi_cannot_find_page_type', __( 'Cannot find page type', 'papi-rest-api' ) , ['status' => 404] );
		}

		$boxes = $page_type->get_boxes();

		if ( empty( $boxes ) ) {
			return new WP_Error( 'papi_no_boxes', __( 'The page type doesn\'t have any boxes', 'papi-rest-api' ), ['status' => 404] );
		}

		foreach ( $boxes as $box ) {
			foreach ( $box->properties as $property ) {
				if ( papi_is_property( $property ) ) {
					$properties[] = $this->create_property_item( $property, [
						'page_type' => $page_type->get_id()
					] );
				}
			}
		}

		return $properties;
	}

	/**
	 * Get filters.
	 *
	 * @return array
	 */
	protected function get_filters( WP_REST_Request $request ) {
		$filters = [
			'page_type' => ''
		];

		if ( is_array( $request['filter'] ) ) {
			$filters = array_merge( $filters, $request['filter'] );
		}

		return $filters;
	}

	/**
	 * Get page property.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return object|WP_Error
	 */
	protected function get_page_property( WP_REST_Request $request ) {
		$page     = new Papi_Post_Page( $request['id'] );
		$property = $page->get_property( $request['slug'] );

		if ( ! papi_is_property( $property ) ) {
			return new WP_Error( 'papi_slug_invalid', __( 'Field slug doesn\'t exist', 'papi-rest-api' ) , ['status' => 404] );
		}

		// Since we are fetching data from a post
		// we need to set the post id the property aswell.
		$property->set_post_id( $request['id'] );

		return $this->create_property_item( $property, [
			'page_type' => $page->get_page_type()->get_id()
		] );
	}

	/**
	 * Update field value on a post.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function update_field( WP_REST_Request $request ) {

	}
}
