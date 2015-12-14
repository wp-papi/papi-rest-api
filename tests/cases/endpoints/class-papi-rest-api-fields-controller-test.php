<?php

class Papi_REST_API_Fields_Controller_Test extends WP_Test_REST_TestCase {

	public function setUp() {
		parent::setUp();
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );
	}

	public function tearDown() {
		parent::tearDown();
		unset( $this->server );
		global $wp_rest_server;
		$wp_rest_server = null;
	}

	public function test_register_routes() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/papi/v1/fields/(?P<id>[\d]+)', $routes );
		$this->assertCount( 2, $routes['/papi/v1/fields/(?P<id>[\d]+)'] );
		$this->assertArrayHasKey( '/papi/v1/fields/(?P<id>[\d]+)/(?P<slug>.+)', $routes );
		$this->assertCount( 3, $routes['/papi/v1/fields/(?P<id>[\d]+)/(?P<slug>.+)'] );
	}

	public function test_create_fields() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_Type_key(), 'simple-content-page-type' );

		$request = new WP_REST_Request( 'POST', sprintf( '/papi/v1/fields/%d', $post_id ) );
		$request->set_param( 'properties', [
			[
				'slug'  => 'name',
				'value' => 'Fredrik'
			],
			[
				'slug'  => 'text',
				'value' => 'Hello, world!'
			]
		] );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			(object) [
				'title'  => 'Name',
				'type'   => 'string',
				'slug'   => 'name',
				'value'  => 'Fredrik',
				'_links' => [
					'self' => [
						[
							'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d/name', $post_id )
						]
					],
					'collection' => [
						[
							'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d', $post_id )
						]
					]
				]
			],
			(object) [
				'title'  => 'Text',
				'type'   => 'text',
				'slug'   => 'text',
				'value'  => 'Hello, world!',
				'_links' => [
					'self' => [
						[
							'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d/text', $post_id )
						]
					],
					'collection' => [
						[
							'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d', $post_id )
						]
					]
				]
			]
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}

	public function test_delete_option_value_access_denied() {
		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, papi_get_page_type_key(), 'simple-content-page-type' );
		add_post_meta( $post_id, 'name', 'Fredrik' );

		$request = new WP_REST_Request( 'DELETE', sprintf( '/papi/v1/fields/%d/name', $post_id ) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_cannot_delete_property',
			'message' => 'Sorry, you are not allowed to delete the property value.',
			'data'    => ['status' => 401]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_delete_field_value_error() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, papi_get_page_type_key(), 'simple-content-page-type' );
		add_post_meta( $post_id, 'name', 'Fredrik' );

		$request = new WP_REST_Request( 'DELETE', sprintf( '/papi/v1/fields/%d/name_missing', $post_id ) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_delete_property_error',
			'message' => 'Delete property value did not work. The property may not be found.',
			'data'    => ['status' => 500]
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}

	public function test_delete_field_value() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create();
		add_post_meta( $post_id, papi_get_page_type_key(), 'simple-content-page-type' );
		add_post_meta( $post_id, 'name', 'Fredrik' );

		$request = new WP_REST_Request( 'DELETE', sprintf( '/papi/v1/fields/%d/name', $post_id ) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = (object) [
			'deleted' => true
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}

	public function test_get_fields_without_page_type() {
		$post_id = $this->factory->post->create();
		$request = new WP_REST_Request( 'GET', sprintf( '/papi/v1/fields/%d', $post_id ) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_cannot_find_page_type',
			'message' => 'Cannot find page type.',
			'data'    => ['status' => 404]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_get_fields_with_page_type() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_Type_key(), 'simple-content-page-type' );

		$request = new WP_REST_Request( 'GET', sprintf( '/papi/v1/fields/%d', $post_id ) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = (object) [
			'title'  => 'Name',
			'type'   => 'string',
			'slug'   => 'name',
			'value'  => null,
			'_links' => [
				'self' => [
					[
						'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d/name', $post_id )
					]
				],
				'collection' => [
					[
						'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d', $post_id )
					]
				]
			]
		];

		$this->assertEquals( $expected, $data[0] );
	}

	public function test_get_missing_field_value() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_Type_key(), 'simple-content-page-type' );

		$request = new WP_REST_Request( 'GET', sprintf( '/papi/v1/fields/%d/name_missing', $post_id ) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_property_slug_invalid',
			'message' => 'Property slug doesn\'t exist.',
			'data'    => ['status' => 404]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_get_field_value() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_Type_key(), 'simple-content-page-type' );
		update_post_meta( $post_id, 'name', 'Fredrik' );

		$this->assertSame( 'Fredrik', papi_get_field( $post_id, 'name' ) );

		$request = new WP_REST_Request( 'GET', sprintf( '/papi/v1/fields/%d/name', $post_id ) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = (object) [
			'title'  => 'Name',
			'type'   => 'string',
			'slug'   => 'name',
			'value'  => 'Fredrik',
			'_links' => [
				'self' => [
					[
						'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d/name', $post_id )
					]
				],
				'collection' => [
					[
						'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d', $post_id )
					]
				]
			]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_get_field_value_with_fields_query_string() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_Type_key(), 'simple-content-page-type' );
		update_post_meta( $post_id, 'name', 'Fredrik' );

		$this->assertSame( 'Fredrik', papi_get_field( $post_id, 'name' ) );

		$request = new WP_REST_Request( 'GET', sprintf( '/papi/v1/fields/%d/name', $post_id ) );
		$request->set_param( 'fields', 'title,type,sort_order' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = (object) [
			'title'      => 'Name',
			'type'       => 'string',
			'sort_order' => 1000,
			'_links'     => [
				'self' => [
					[
						'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d/name', $post_id )
					]
				],
				'collection' => [
					[
						'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d', $post_id )
					]
				]
			]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_update_field_value_access_denied() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_Type_key(), 'simple-content-page-type' );
		update_post_meta( $post_id, 'name', 'Fredrik' );

		$this->assertSame( 'Fredrik', papi_get_field( $post_id, 'name' ) );

		$request = new WP_REST_Request( 'PUT', sprintf( '/papi/v1/fields/%d/name', $post_id ) );
		$request->set_param( 'value', 'Elli' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_cannot_update_property',
			'message' => 'Sorry, you are not allowed to update the property value.',
			'data'    => ['status' => 401]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_update_field_value() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create( ['post_author' => $user_id] );
		update_post_meta( $post_id, papi_get_page_Type_key(), 'simple-content-page-type' );
		update_post_meta( $post_id, 'name', 'Fredrik' );

		$this->assertSame( 'Fredrik', papi_get_field( $post_id, 'name' ) );

		$request = new WP_REST_Request( 'PUT', sprintf( '/papi/v1/fields/%d/name', $post_id ) );
		$request->set_param( 'value', 'Elli' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = (object) [
			'title'  => 'Name',
			'type'   => 'string',
			'slug'   => 'name',
			'value'  => 'Elli',
			'_links' => [
				'self' => [
					[
						'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d/name', $post_id )
					]
				],
				'collection' => [
					[
						'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d', $post_id )
					]
				]
			]
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}

	public function test_update_fields_empty_array() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_Type_key(), 'simple-content-page-type' );

		$request = new WP_REST_Request( 'POST', sprintf( '/papi/v1/fields/%d', $post_id ) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_cannot_update_properties',
			'message' => 'Empty properties array.',
			'data'    => ['status' => 500]
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}

	public function test_update_fields() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_type_key(), 'simple-content-page-type' );
		update_post_meta( $post_id, 'name', 'Fredrik' );
		update_post_meta( $post_id, 'text', 'Hello, world!' );

		$this->assertSame( 'Fredrik', papi_get_field( $post_id, 'name' ) );
		$this->assertSame( 'Hello, world!', papi_get_field( $post_id, 'text' ) );

		$request = new WP_REST_Request( 'POST', sprintf( '/papi/v1/fields/%d', $post_id ) );
		$request->set_param( 'properties', [
			[
				'slug'  => 'name',
				'value' => 'Elli'
			],
			[
				'slug'  => 'text',
				'value' => 'Hello, Papi!'
			]
		] );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			(object) [
				'title'  => 'Name',
				'type'   => 'string',
				'slug'   => 'name',
				'value'  => 'Elli',
				'_links' => [
					'self' => [
						[
							'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d/name', $post_id )
						]
					],
					'collection' => [
						[
							'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d', $post_id )
						]
					]
				]
			],
			(object) [
				'title'  => 'Text',
				'type'   => 'text',
				'slug'   => 'text',
				'value'  => 'Hello, Papi!',
				'_links' => [
					'self' => [
						[
							'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d/text', $post_id )
						]
					],
					'collection' => [
						[
							'href' => sprintf( 'http://example.org/?rest_route=/papi/v1/fields/%d', $post_id )
						]
					]
				]
			]
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}
}
