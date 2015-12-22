<?php

class Papi_REST_API_Functions_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->api = Papi_REST_API::instance();
	}

	public function tearDown() {
		parent::tearDown();
		unset( $this->api );
	}

	public function test_rest_authorization_required_code_401() {
		$this->assertSame( 401, rest_authorization_required_code() );
	}

	public function test_rest_authorization_required_code_403() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$this->assertSame( 403, rest_authorization_required_code() );
		wp_set_current_user( 0 );
	}
}
