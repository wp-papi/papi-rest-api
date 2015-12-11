<?php

/**
 * Main Papi REST API class.
 */
class Papi_REST_API {

	/**
	 * The instance of Papi REST API class.
	 *
	 * @var Papi_REST_API
	 */
	private static $instance;

	/**
	 * Get the Papi REST API instance.
	 *
	 * @return Papi_REST_API
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * The constructor.
	 *
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		spl_autoload_register( [$this, 'autoload'] );
		$this->require_files();
		$this->setup_actions();
	}

	/**
	 * Autoload Papi REST API classes.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string $class
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
		$file  = 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
		$path  = __DIR__ . '/';

		if ( preg_match( '/^papi\_rest\_\w+\_controller$/', $class ) ) {
			$path .= 'endpoints/';
		}

		if ( is_readable( $path . $file ) ) {
			require_once $path . $file;
		}
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		// Options controller.
		$controller = new Papi_REST_API_Options_Controller;
		$controller->register_routes();

		// Fields controller.
		$controller = new Papi_REST_API_Fields_Controller;
		$controller->register_routes();
	}

	/**
	 * Require files.
	 */
	public function require_files() {
		require_once __DIR__ . '/functions.php';
	}

	/**
	 * Setup actions.
	 */
	private function setup_actions() {
		add_action( 'rest_api_init', [$this, 'register_routes'] );
	}
}
