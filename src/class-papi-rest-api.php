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
	 * Adds extra post type registration arguments.
	 */
	public function add_extra_api_post_type_arguments() {
		global $wp_post_types;

		if ( isset( $wp_post_types['post'] ) && ! isset( $wp_post_types['post']->show_in_rest ) ) {
			$wp_post_types['post']->show_in_rest = true;
		}

		if ( isset( $wp_post_types['page'] ) && ! isset( $wp_post_types['page']->show_in_rest ) ) {
			$wp_post_types['page']->show_in_rest = true;
		}

		if ( isset( $wp_post_types['attachment'] ) && ! isset( $wp_post_types['attachment']->show_in_rest ) ) {
			$wp_post_types['attachment']->show_in_rest = true;
		}
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
		require_once __DIR__ . '/class-papi-rest-api-additional-fields.php';
	}

	/**
	 * Setup actions.
	 */
	private function setup_actions() {
		add_filter( 'init', [$this, 'add_extra_api_post_type_arguments'], 11 );
		add_action( 'rest_api_init', [$this, 'register_routes'] );
	}
}
