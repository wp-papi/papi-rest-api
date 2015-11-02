<?php

class Papi_REST_API_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->api = Papi_REST_API::instance();
	}

	public function tearDown() {
		parent::tearDown();
		unset( $this->api );
	}

	public function test_if_papi_exists() {
		$this->assertTrue( function_exists( 'papi' ) );
	}
}
