<?php

class Simple_Content_Page_Type extends Papi_Page_Type {

	public function meta() {
		return [
			'name' => 'Simple Content'
		];
	}

	public function register() {
		$this->box( 'Content', [
			papi_property( [
				'title'    => 'Name',
				'slug'     => 'name',
				'type'     => 'string'
			] ),
			papi_property( [
				'title'    => 'Text',
				'slug'     => 'text',
				'type'     => 'text'
			] )
		] );
	}
}
