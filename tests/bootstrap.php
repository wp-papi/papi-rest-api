<?php

// Load Composer autoload.
require __DIR__ . '/../vendor/autoload.php';

$papi_path = '/tmp/wordpress/src/wp-content/plugins/papi/papi-loader.php';

if ( file_exists( __DIR__ . '/../../papi/papi-loader.php' ) ) {
	$papi_path = __DIR__ . '/../../papi/papi-loader.php';
}

// Load Papi loader file as plugin.
WP_Test_Suite::load_plugins( [
	$papi_path,
	__DIR__ . '/../plugin.php'
] );

// Run the WordPress test suite.
WP_Test_Suite::run();
