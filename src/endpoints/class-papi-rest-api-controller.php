<?php

/**
 * Papi REST API base controller.
 */
abstract class Papi_REST_API_Controller {

	/**
	 * Papi REST API namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'papi/v1';

	/**
	 * Register controller related routes.
	 */
	abstract public function register_routes();

}
