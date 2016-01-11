<?php

class Papi_REST_API_Additional_Fields_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->class = new Papi_REST_API_Additional_Fields;
	}

	public function tearDown() {
		parent::tearDown();
		unset( $this->class );
	}

	public function test_get_fields_empty() {
		$post_id = $this->factory->post->create();
		$fields = $this->class->get_fields( ['ID' => $post_id], 'fields', new WP_REST_Request );
		$this->assertEmpty( $fields );
	}

	public function test_get_fields() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, papi_get_page_type_key(), 'simple-content-page-type' );
		update_post_meta( $post_id, 'name', 'Fredrik' );
		update_post_meta( $post_id, 'text', 'Hello, world!' );

		$this->assertSame( 'Fredrik', papi_get_field( $post_id, 'name' ) );
		$this->assertSame( 'Hello, world!', papi_get_field( $post_id, 'text' ) );

		$fields = $this->class->get_fields( ['ID' => $post_id], 'fields', new WP_REST_Request );
		$this->assertEquals( ['name' => 'Fredrik', 'text' => 'Hello, world!'], $fields );
	}

	public function test_get_page_type() {
		$post_id = $this->factory->post->create();

		$page_type = $this->class->get_page_type( ['ID' => $post_id], 'page_type', new WP_REST_Request );
		$this->assertSame( $page_type, '' );

		update_post_meta( $post_id, papi_get_page_type_key(), 'simple-content-page-type' );

		$page_type = $this->class->get_page_type( ['ID' => $post_id], 'page_type', new WP_REST_Request );
		$this->assertSame( $page_type, get_post_meta( $post_id, papi_get_page_type_key(), true ) );
	}
}
