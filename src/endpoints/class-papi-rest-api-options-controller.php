<?php

/**
 * Papi REST API options controller.
 */
class Papi_REST_API_Options_Controller extends Papi_REST_API_Controller {

	/**
	 * Register the options-related routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/options', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [$this, 'callback']
		] );

		register_rest_route( $this->namespace, '/options/(?P<option>.+)', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [$this, 'callback']
		] );
	}

	/**
	 * Handle the options endpoint callback.
	 *
	 * @param  \WP_REST_Request $request
	 */
	public function callback( WP_REST_Request $request ) {
		if ( $request['option'] ) {
			return [
				'value' => papi_get_option( $request['option'] )
			];
		}

		return $this->get_options_slugs();
	}

	/**
	 * Get all options slugs.
	 *
	 * @return array
	 */
	private function get_options_slugs() {
		$slugs        = [];
		$option_types = papi_get_all_content_types( [
			'type' => 'option'
		] );

		foreach ( $option_types as $option_type ) {
			if ( ! papi_is_option_type( $option_type ) ) {
				continue;
			}

			foreach ( $option_type->get_boxes() as $box ) {
				foreach ( $box->properties as $property ) {
					$slugs[] = $property->get_slug( true );
				}
			}
		}

		return $slugs;
	}
}
