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
		$this->assertArrayHasKey( '/papi/v1/options/(?P<option>.+)', $routes );
		$this->assertCount( 1, $routes['/papi/v1/options/(?P<option>.+)'] );
	}

	public function test_get_options_slugs() {
		$request = new WP_REST_Request( 'GET', '/papi/v1/options' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEmpty( $data );
	}

	public function test_get_option_value() {
		$request = new WP_REST_Request( 'GET', '/papi/v1/options' );
		$request->set_param( 'option', 'name_test' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( ['value' => null], $data );
	}
}
