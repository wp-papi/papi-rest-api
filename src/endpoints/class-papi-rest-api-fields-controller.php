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
				'callback'            => [$this, 'update_field'],
				'permission_callback' => [$this, 'update_field_permissions_check']
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [$this, 'delete_field'],
				'permission_callback' => [$this, 'delete_field_permissions_check']
			]
		] );
	}

	/**
	 * Check if we can delete a post.
	 *
	 * @param obj $post Post object.
	 * @return bool Can we delete it?
	 */
	protected function check_delete_permission( $post ) {
		$post_type = get_post_type_object( $post->post_type );

		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		return current_user_can( $post_type->cap->delete_post, $post->ID );
	}

	/**
	 * Check if a given post type should be viewed or managed.
	 *
	 * @param  object|string $post_type
	 *
	 * @return bool
	 */
	protected function check_is_post_type_allowed( $post_type ) {
		if ( ! is_object( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type ) && isset( $post_type->show_in_rest ) && $post_type->show_in_rest ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if we can edit a post.
	 *
	 * @param  object $post
	 *
	 * @return bool
	 */
	protected function check_update_permission( $post ) {
		$post_type = get_post_type_object( $post->post_type );

		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		return current_user_can( $post_type->cap->edit_post, $post->ID );
	}

	/**
	 * Delete a field value on a post.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function delete_field( WP_REST_Request $request ) {
		if ( papi_delete_field( $request['id'], $request['slug'] ) ) {
			return (object) ['deleted' => true];
		}

		return new WP_Error( 'papi_delete_property_error', __( 'Delete property value did not work. The property may not be found.', 'papi-rest-api' ), ['status' => 500] );
	}

	/**
	 * Check if a given request has access to delete a field value.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function delete_field_permissions_check( WP_REST_Request $request ) {
		$post = get_post( $request['id'] );

		if ( $post && ! $this->check_delete_permission( $post ) ) {
			return new WP_Error( 'papi_cannot_delete_property', __( 'Sorry, you are not allowed to delete the property value.', 'papi-rest-api' ), ['status' => rest_authorization_required_code()] );
		}

		return true;
	}

	/**
	 * Get page type property from a field slug.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return object
	 */
	public function get_field( WP_REST_Request $request ) {
		return $this->get_property( $request );
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
			return new WP_Error( 'papi_cannot_find_page_type', __( 'Cannot find page type.', 'papi-rest-api' ) , ['status' => 404] );
		}

		$boxes = $page_type->get_boxes();

		if ( empty( $boxes ) ) {
			return new WP_Error( 'papi_no_boxes', __( 'The page type doesn\'t have any boxes.', 'papi-rest-api' ), ['status' => 404] );
		}

		foreach ( $boxes as $box ) {
			foreach ( $box->properties as $property ) {
				if ( papi_is_property( $property ) ) {
					$properties[] = $this->create_property_item( $request, $property, [
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
	 * Get property.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return object|WP_Error
	 */
	protected function get_property( WP_REST_Request $request ) {
		$page     = new Papi_Post_Page( $request['id'] );
		$property = $page->get_property( $request['slug'] );

		if ( ! papi_is_property( $property ) ) {
			return new WP_Error( 'papi_property_slug_invalid', __( 'Property slug doesn\'t exist.', 'papi-rest-api' ) , ['status' => 404] );
		}

		// Since we are fetching data from a post
		// we need to set the post id the property aswell.
		$property->set_post_id( $request['id'] );

		return $this->create_property_item( $request, $property, [
			'page_type' => $page->get_page_type()->get_id()
		] );
	}

	/**
	 * Prepare links for the response.
	 *
	 * @param  object          $item
	 * @param  string          $slug
	 * @param  WP_REST_Request $request
	 *
	 * @return object
	 */
	protected function prepare_links( $items, $slug, WP_REST_Request $request ) {
		$items->_links = [
			'self' => [
				[
					'href' => rest_url( sprintf( '%s/%s/%d/%s', $this->namespace, $this->route, $request['id'], $slug ) )
				]
			],
			'collection' => [
				[
					'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->route, $request['id'] ) )
				]
			]
		];

		return $items;
	}

	/**
	 * Update field value on a post.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function update_field( WP_REST_Request $request ) {
		if ( papi_update_field( $request['id'], $request['slug'], $request['value'] ) ) {
			return $this->get_property( $request );
		}

		return new WP_Error( 'papi_update_property_error', __( 'Update property value did not work. The property may not be found.', 'papi-rest-api' ), ['status' => 500] );
	}

	/**
	 * Check if a given request has access to update a field value.
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function update_field_permissions_check( WP_REST_Request $request ) {
		$post = get_post( $request['id'] );
		$post_type = get_post_type_object( $post->post_type );

		if ( $post && ! $this->check_update_permission( $post ) ) {
			return new WP_Error( 'papi_cannot_update_property', __( 'Sorry, you are not allowed to update the property value.', 'papi-rest-api' ), ['status' => rest_authorization_required_code()] );
		}

		if ( ! empty( $request['author'] ) && get_current_user_id() !== $request['author'] && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
			return new WP_Error( 'papi_cannot_edit_others', __( 'You are not allowed to update posts as this user.', 'papi-rest-api' ), ['status' => rest_authorization_required_code()] );
		}

		if ( ! empty( $request['sticky'] ) && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
			return new WP_Error( 'papi_cannot_assign_sticky', __( 'You do not have permission to make posts sticky.', 'papi-rest-api' ), ['status' => rest_authorization_required_code()] );
		}

		return true;
	}
}
