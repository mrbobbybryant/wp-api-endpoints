<?php
namespace WP_API_ENDPOINTS\BASE_API;

/**
 * Class Base_API
 * Sets up scaffold for creating ajax endpoints, allows cachable GET requests
 */
abstract class Base_API {

	/**
	 * Rewrite url endpoint for api to answer to overrideable in implementation class
	 * @var string
	 */
	protected static $rewrite_endpoint = 'api';

	/**
	 * Method names that follow $rewrite_endpoint ex. /api/$front_endpoint
	 * for unauthenticated use.
	 * @var array
	 */
	protected static $front_endpoints = array();

	/**
	 * Method names that follow $rewrite_endpoint ex. /api/$admin_endpoints
	 * for authenticated use
	 * @var array
	 */
	protected static $admin_endpoints = array();

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'template_redirect', array( $this, 'api_endpoint_template_redirect' ) );
	}

	/**
	 * Sets up rewrite endpoint
	 */
	function init() {
		//adds api endpoint
		add_rewrite_endpoint( static::$rewrite_endpoint, EP_ROOT );
	}

	/**
	 * Helper function for checking request type
	 * @return mixed
	 */
	function request_type() {
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Helper function for checking authentication status
	 * @return array
	 */
	function is_user_admin() {
		$user          = wp_get_current_user();
		$allowed_roles = array( 'editor', 'administrator', 'author' );
		if ( is_super_admin( $user->ID ) ) {
			return true;
		}
		return ( array_intersect( $allowed_roles, $user->roles ) );
	}

	/**
	 * Handles template redirect requests. Checks if endpoint is valid and if
	 * user should be authenticated, routes request to proper handler function
	 */
	function api_endpoint_template_redirect() {
		global $wp_query;

		if ( empty( $wp_query->query_vars[ static::$rewrite_endpoint ] ) ) {
			return;
		}

		//allows use of DOING_AJAX content just like admin-ajax requests
		define( 'DOING_AJAX', true );

		$api      = explode( '/', $wp_query->query_vars[ static::$rewrite_endpoint ] );
		$endpoint = array_shift( $api );
		$admin = in_array( $endpoint, static::$admin_endpoints);
		$is_function = function_exists( $endpoint );
		$is_method = method_exists( $this, $endpoint );

		if ( ! in_array( $endpoint, static::$front_endpoints ) && ! $admin ) {
			wp_send_json_error( 'Endpoint does not exist. Verify Endpoint exists. You may need to flush rewrites manually.' );
		}

		if ( ! $is_function && ! $is_method ) {
			wp_send_json_error( 'Endpoint function/method does not exist.' );
		}

		if ( $admin && ! $this->is_user_admin() ) {
			wp_send_json_error( 'This is an Admin Endpoint. You must be logged in to access it.' );
		}

		if ( $is_method ) {
			call_user_func_array( array( $this, $endpoint ), $api );
		}

		if ( $is_function ) {
			call_user_func( $endpoint, $api );
		}

	}

	/**
	 * With "/" delimited params there are times when defaults are needed in the url, but they aren't needed
	 * once there are keys mapped to the values. This function combined with array_walk dumps default keys
	 * and values
	 *
	 * @param $item
	 * @param $key
	 */
	function empty_defaults(&$item, $key) {
		$item = ( 'default' === $item ) ? null : $item;
	}
}
