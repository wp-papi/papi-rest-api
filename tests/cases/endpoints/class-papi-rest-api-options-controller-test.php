<?php

class Papi_REST_API_Options_Controller_Test extends WP_Test_REST_TestCase {

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

		$this->assertArrayHasKey( '/papi/v1/options', $routes );
		$this->assertCount( 1, $routes['/papi/v1/options'] );
		$this->assertArrayHasKey( '/papi/v1/options/(?P<slug>.+)', $routes );
		$this->assertCount( 3, $routes['/papi/v1/options/(?P<slug>.+)'] );
	}

	public function test_delete_option_value_access_denied() {
		update_option( 'name', 'Fredrik' );
		$request = new WP_REST_Request( 'DELETE', '/papi/v1/options/name' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_cannot_delete_option',
			'message' => 'Sorry, you are not allowed to delete the option value',
			'data'    => ['status' => 403]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_delete_option_value_error() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		update_option( 'name', 'Fredrik' );
		$request = new WP_REST_Request( 'DELETE', '/papi/v1/options/name_missing' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_delete_option_error',
			'message' => 'Delete option value did not work. The property may not be found',
			'data'    => ['status' => 500]
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}

	public function test_delete_option_value() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		update_option( 'name', 'Fredrik' );
		$request = new WP_REST_Request( 'DELETE', '/papi/v1/options/name' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = (object) [
			'deleted' => true
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}

	public function test_get_options_slugs() {
		$request = new WP_REST_Request( 'GET', '/papi/v1/options' );
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
						'href' => 'http://example.org/?rest_route=/papi/v1/options/name'
					]
				],
				'collection' => [
					[
						'href' => 'http://example.org/?rest_route=/papi/v1/options'
					]
				]
			]
		];

		$this->assertEquals( $expected, $data[0] );
	}

	public function test_get_missing_option_value() {
		$request = new WP_REST_Request( 'GET', '/papi/v1/options/missing' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_slug_invalid',
			'message' => 'Option slug doesn\'t exist',
			'data'    => ['status' => 404]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_get_empty_option_value() {
		$request = new WP_REST_Request( 'GET', '/papi/v1/options/name' );
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
						'href' => 'http://example.org/?rest_route=/papi/v1/options/name'
					]
				],
				'collection' => [
					[
						'href' => 'http://example.org/?rest_route=/papi/v1/options'
					]
				]
			]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_get_option_value() {
		update_option( 'name', 'Fredrik' );
		$request = new WP_REST_Request( 'GET', '/papi/v1/options/name' );
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
						'href' => 'http://example.org/?rest_route=/papi/v1/options/name'
					]
				],
				'collection' => [
					[
						'href' => 'http://example.org/?rest_route=/papi/v1/options'
					]
				]
			]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_update_option_value_access_denied() {
		update_option( 'name', 'Fredrik' );
		$request = new WP_REST_Request( 'POST', '/papi/v1/options/name' );
		$request->set_param( 'value', 'Elli' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_cannot_update_option',
			'message' => 'Sorry, you are not allowed to update the option value',
			'data'    => ['status' => 403]
		];

		$this->assertEquals( $expected, $data );
	}

	public function test_update_option_value_error() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		update_option( 'name', 'Fredrik' );
		$request = new WP_REST_Request( 'POST', '/papi/v1/options/name_missing' );
		$request->set_param( 'value', 'Elli' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$expected = [
			'code'    => 'papi_update_option_error',
			'message' => 'Update option value did not work. The property may not be found',
			'data'    => ['status' => 500]
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}

	public function test_update_option_value() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		update_option( 'name', 'Fredrik' );
		$request = new WP_REST_Request( 'POST', '/papi/v1/options/name' );
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
						'href' => 'http://example.org/?rest_route=/papi/v1/options/name'
					]
				],
				'collection' => [
					[
						'href' => 'http://example.org/?rest_route=/papi/v1/options'
					]
				]
			]
		];

		$this->assertEquals( $expected, $data );
		wp_set_current_user( 0 );
	}
}
