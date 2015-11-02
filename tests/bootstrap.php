<?php

// Load Composer autoload.
require __DIR__ . '/../vendor/autoload.php';

// Define fixtures directory constant
#define( 'PAPI_FIXTURE_DIR', __DIR__ . '/data' );

// Load Papi loader file as plugin.
WP_Test_Suite::load_plugins( [
	__DIR__ . '/../../papi/papi-loader.php',
	__DIR__ . '/../plugin.php'
] );

// Load our helpers file.
#WP_Test_Suite::load_files( [
#	__DIR__ . '/data/functions.php',
#	__DIR__ . '/framework/helpers.php',
#	__DIR__	. '/framework/class-papi-property-test-case.php'
#] );

// Run the WordPress test suite.
WP_Test_Suite::run();
