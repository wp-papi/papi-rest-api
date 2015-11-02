<?php

class Site_Option_Type extends Papi_Option_Type {

	public function option_type() {
		return [
			'name' => 'Site',
			'menu' => 'options-general.php'
		];
	}

	public function register() {
		$this->box( 'Content', [
			papi_property( [
				'title'    => 'Name',
				'slug'     => 'name',
				'type'     => 'string'
			] )
		] );
	}
}
