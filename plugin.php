<?php

/**
 * Plugin Name: Papi REST API
 * Description: Add-on for the WordPress REST API
 * Author: Fredrik Forsmo
 * Author URI: https://frozzare.com
 * Version: 1.0.0
 * Plugin URI: https://github.com/wp-papi/papi-rest-api
 * Textdomain: papi-rest-api
 * Domain Path: /languages/
 */

// Load main Papi REST API class.
require_once __DIR__ . '/src/class-papi-rest-api.php';

/**
 * Load Papi REST API plugin.
 */
add_action( 'papi/loaded', function () {
	Papi_REST_API::instance();
} );
