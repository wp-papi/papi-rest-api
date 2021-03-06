<?php

// Load Composer autoload.
require __DIR__ . '/../vendor/autoload.php';

// Travis path to Papi plugin.
$papi_path = '/tmp/wordpress/src/wp-content/plugins/papi/papi-loader.php';

// On development environment, use another path to Papi plugin.
if ( file_exists( __DIR__ . '/../../papi/papi-loader.php' ) ) {
	$papi_path = __DIR__ . '/../../papi/papi-loader.php';
}

// Load Papi loader file as plugin.
WP_Test_Suite::load_plugins( [
	$papi_path,
	__DIR__ . '/../plugin.php'
] );

// Run the WordPress test suite.
WP_Test_Suite::run( function () {

	/**
	 * Register Papi directory.
	 *
	 * @return string
	 */
	add_filter( 'papi/settings/directories', function () {
		return [__DIR__ . '/data/option-types', __DIR__ . '/data/page-types'];
	} );

} );
