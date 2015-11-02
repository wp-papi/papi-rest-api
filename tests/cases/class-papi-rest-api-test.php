<?php

class Papi_REST_API_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->api = new Papi_REST_API;
	}

	public function tearDown() {
		parent::tearDown();
		unset( $this->api );
	}

	public function test_actions() {
		$this->assertGreaterThan( 0, has_action( 'rest_api_init', [$this, 'register_routes'] ) );
	}

	public function test_papi() {
		$this->assertTrue( function_exists( 'papi' ) );
	}
}
