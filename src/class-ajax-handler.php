<?php
namespace WP_API_ENDPOINTS\ENDPOINT_HANDLER;
use WP_API_ENDPOINTS\BASE_API as BASE_API;

class Ajax_Handler extends BASE_API\Base_API {

	/**
	 * Used internal to set the current functions context to the admin.
	 */
	const ADMIN = 'admin_endpoints';

	/**
	 * Used internal to set the current functions context to the frontend.
	 */
	const FRONT = 'front_endpoint';

	/**
	 * Used internally to create a nonce for each localized script.
	 */
	const NONCE = 'ajax_handler_nonce';

	/**
	 * All Endpoints will be prefixed by this variable.
	 * @var string
	 */
	protected static $rewrite_endpoint = 'ajax';

	/**
	 * Possible action endpoints "/ajax/$action" any $action that doesn't exist in the array will return an error
	 * @var array
	 */
	protected static $front_endpoints = array( 'ajax_handler' );

	/**
	 * Possible admin only endpoints, a user without permissions will get an error trying to request this
	 * @var array
	 */
	protected static $admin_endpoints = array( 'an_admin_endpoint' );

	/**
	 * Consists of an array of all ADMIN localized scripts registered.
	 * @var array
	 */
	protected static $admin_localized_data = array();

	/**
	 * Consists of an array of all FRONTEND localized scripts registered.
	 * @var array
	 */
	protected static $front_localized_data = array();

	/**
	 * Consists of an array of all endpoint URL's
	 * @var array
	 */
	protected static $endpoint_urls = array();

	/**
	 * Bootstraps the Class and contains all required WordPress Hooks.
	 */
	public function setup() {
		$url = $this->get_endpoint_url() . '/ajax_handler';
		array_push( static::$endpoint_urls, $url );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_localized_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'localize_general_endpoint_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_localized_data' ) );
	}

	/**
	 * Loops through and runs wp_localize_script for all registered localizations.
	 */
	public function enqueue_localized_data() {

		if ( is_admin() && ! empty( static::$admin_localized_data ) ) {
			$this->register_localizations( static::$admin_localized_data );
		}

		if ( ! is_admin() && ! empty( static::$front_localized_data ) ) {
			$this->register_localizations( static::$front_localized_data );
		}
	}

	/**
	 * Internal localization which provides a snapshot of all data register with the
	 * Ajax Handler class.
	 */
	public function localize_general_endpoint_data() {
		$data = $this->get_default_localization_data();
		wp_localize_script( 'jquery-core', 'endpointData', $data );
	}

	public function ajax_handler() {
		wp_send_json_success( 'I\'m am the ajax Handler' );
	}


	//======================================================================
	// EXTERNAL API METHODS
	//======================================================================

	/**
	 * Allows you to register a FRONTEND Endpoint
	 * @param $func_name (string) - Name of endpoint callback function.
	 *
	 * @return (int) - Returns the new number of elements in the array.
	 * @throws \Exception
	 */
	public function add_frontend_endpoint( $func_name ) {
		return $this->add_endpoint( $func_name, self::FRONT );
	}

	/**
	 * Allows you to unregister a FRONTEND Endpoint
	 * @param $func_name (string) - Name of endpoint callback function.
	 *
	 * @return (array) Returns an array consisting of the extracted element.
	 * @throws \Exception
	 */
	public function remove_frontend_endpoint( $func_name ) {
		return $this->remove_endpoint( $func_name, self::FRONT );
	}

	/**
	 * Allows you to register a ADMIN Endpoint
	 * @param $func_name (string) - Name of endpoint callback function.
	 *
	 * @return (int) - Returns the new number of elements in the array.
	 * @throws \Exception
	 */
	public function add_admin_endpoint( $func_name ) {
		return $this->add_endpoint( $func_name, self::ADMIN );
	}

	/**
	 * Allows you to unregister a ADMIN Endpoint
	 * @param $func_name (string) - Name of endpoint callback function.
	 *
	 * @return (array) Returns an array consisting of the extracted element.
	 * @throws \Exception
	 */
	public function remove_admin_endpoint( $func_name ) {
		return $this->remove_endpoint( $func_name, self::ADMIN );
	}

	/**
	 * Allows you to add an ADMIN localization for use with Ajax.
	 * @param $endpoint (string) Endpoint Name
	 * @param $js_handle (string) Javascript File to associate with this localization.
	 * @param $local_handle (string) Unique Name for this Localization.
	 * @param $data (array) Associative array of data to include with this localization.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function add_admin_localization( $endpoint, $js_handle, $local_handle, $data ) {
		return $this->add_localization( $endpoint, $js_handle, $local_handle, $data, self::ADMIN );
	}

	/**
	 * Allows you to remove an ADMIN localization from the current array.
	 * @param $js_handle (string) localization name
	 *
	 * @return (bool) true if item was successfully removed.
	 * @throws \Exception
	 */
	public function remove_admin_localization( $js_handle ) {
		return $this->remove_localization( $js_handle, self::ADMIN );
	}

	/**
	 * Allows you to add an FRONTEND localization for use with Ajax.
	 * @param $endpoint (string) Endpoint Name
	 * @param $js_handle (string) Javascript File to associate with this localization.
	 * @param $local_handle (string) Unique Name for this Localization.
	 * @param $data (array) Associative array of data to include with this localization.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function add_frontend_localization( $endpoint, $js_handle, $local_handle, $data ) {
		return $this->add_localization( $endpoint, $js_handle, $local_handle, $data, self::FRONT );
	}

	/**
	 * Allows you to remove a FRONTEND localization from the current array.
	 * @param $js_handle (string) localization name
	 *
	 * @return (bool) true if item was successfully removed.
	 * @throws \Exception
	 */
	public function remove_frontend_localization( $js_handle ) {
		return $this->remove_localization( $js_handle, self::FRONT );
	}

	/**
	 * Return an array of all Front-end Endpoints, or false if none exist.
	 * @return array|bool
	 */
	public function get_frontend_endpoints() {
		if ( ! empty( self::$front_endpoints ) ) {
			return self::$front_endpoints;
		}
		return false;
	}

	/**
	 * Return an array of all Admin Endpoints, or false if none exist.
	 * @return array|bool
	 */
	public function get_admin_endpoints() {
		if ( ! empty( self::$admin_endpoints ) ) {
			return self::$admin_endpoints;
		}
		return false;
	}

	/**
	 * Return an array of all Front-end Localizations, or false if none exist.
	 * @return array|bool
	 */
	public function get_frontend_localizations() {
		if ( ! empty( static::$front_localized_data ) ) {
			return static::$front_localized_data;
		}
		return false;
	}

	/**
	 * Return an array of all Admin Localizations, or false if none exist.
	 * @return array|bool
	 */
	public function get_admin_localizations() {
		if ( ! empty( static::$admin_localized_data ) ) {
			return static::$admin_localized_data;
		}
		return false;
	}

	//======================================================================
	// INTERNAL API METHODS
	//======================================================================

	/**
	 * Internal API Method used by add_front_endpoint and add_admin_endpoint,
	 * to register an endpoint with Wordpress.
	 *
	 * @param $func_name
	 * @param $context
	 *
	 * @return int
	 * @throws \Exception
	 */
	private function add_endpoint( $func_name, $context ) {
		if ( function_exists( $func_name ) || method_exists( $this, $func_name ) ) {
			$context = $this->set_context( $context );

			$url = $this->get_endpoint_url() . '/' . $func_name;
			array_push( static::$endpoint_urls, $url );

			return array_push( static::$$context['endpoint'], $func_name );
		}
		throw new \Exception( 'Endpoint function does not exist' );
	}

	/**
	 * Internal API Method used by remove_front_endpoint and remove_admin_endpoint,
	 * to de-register an endpoint with Wordpress.
	 * @param $func_name
	 * @param $context
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function remove_endpoint( $func_name, $context ) {
		if ( in_array( $func_name, $context ) ) {
			$context = $this->set_context( $context );
			return $this->remove_element( $func_name, $this->$context['collection'] );
		}
		throw new \Exception( 'Cannot remove an endpoint that doesn\'t exist' );
	}

	/**
	 * Internal API Method used by remove_front_localization and remove_admin_localization,
	 * to de-register a localization with Wordpress.
	 * @param $js_handle
	 * @param $context
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function remove_localization( $js_handle, $context ) {
		$context = $this->set_context( $context );

		if ( empty( $context['collection'] ) ) {
			throw new \Exception( 'No localizations exist for the ' . $context['collection'] );
		}

		foreach ( $context['collection'] as $key => $el ) {
			if ( $el['js_handle'] === $js_handle ) {
				unset( $this->$context['collection'][$key] );
				return true;
			}
		}
		return false;
	}

	/**
	 * Internal API Method used to register localizations with WordPress.
	 * @param $endpoint
	 * @param $js_handle
	 * @param $local_handle
	 * @param array $data
	 * @param $context
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function add_localization( $endpoint, $js_handle, $local_handle, $data = array(), $context ) {

		if ( ! wp_script_is( $js_handle ) ) {
			throw new \Exception( 'Javascript handle has not been registered with WordPress.' );
		}

		if ( ! $this->endpoint_exists( $endpoint ) ) {
			throw new \Exception( 'Endpoint to add_localization has not been registered with WordPress.' );
		}

		$context = $this->set_context( $context );

		if ( $this->localization_exists( $local_handle, $context )  ) {
			return wp_parse_args(
					static::$$context['collection'][ $local_handle ]['data'],
					$data
			);
		}
		$temp[$local_handle] = array(
				'endpointUrl'  => $context['url'] . '/' . $endpoint,
				'jsHandle'     => $js_handle,
				'localHandle'  => $local_handle,
				'data'         => $data,
				'security'     => wp_create_nonce( static::NONCE )
		);
		return static::$$context['collection'] = array_merge(
				static::$$context['collection'],
				array_filter( $temp )
		);
	}


	//======================================================================
	// CLASS SPECIFIC HELPER FUNCTIONS
	//======================================================================

	/**
	 * Method used internally to determine whether a given function should be used
	 * to register or de-register Admin or Frontend processes..
	 * @param $context
	 *
	 * @return array
	 */
	private function set_context( $context ) {
		if ( $context === 'admin_endpoints' ) {
			return array(
					'endpoint'   => 'admin_endpoints',
					'collection' => 'admin_localized_data',
					'method'     => 'add_admin_endpoint',
					'url'        => $this->get_endpoint_url()
			);
		}
		return array(
				'endpoint'   => 'front_endpoints',
				'collection' => 'front_localized_data',
				'method'     => 'add_front_endpoint',
				'url'        => $this->get_endpoint_url()
		);
	}

	/**
	 * Interal method used to help build a registered endpoints URL.
	 * @return string|void
	 */
	private function get_endpoint_url() {
		return site_url( 'ajax' );
	}

	/**
	 * Allows a quick check to see if an endpoint exists.
	 * @param $endpoint
	 *
	 * @return bool
	 */
	public function endpoint_exists( $endpoint ) {
		return in_array( $endpoint, static::$front_endpoints );
	}

	/**
	 * Allows  quick check to see if a localization exists.
	 * @param $local_handle
	 * @param $context
	 *
	 * @return bool|int|string
	 */
	public function localization_exists( $local_handle, $context ) {
		return $this->recursive_array_search( $local_handle, static::$$context['collection'] );
	}

	/**
	 * Internal Method used to actually register all localizations with WordPress.
	 *
	 * @uses wp_localize_script
	 * @param $localized_array
	 */
	private function register_localizations( $localized_array ) {
		foreach ( $localized_array as $handle ) {
			wp_localize_script( $handle['jsHandle'], $handle['localHandle'], $handle );
		}
	}

	/**
	 * Method makes multiple queries internally and returns an array of data for use
	 * by the internal localization method.
	 * @return array
	 */
	private function get_default_localization_data() {
		return array(
				'frontEndpoints'    => $this->get_frontend_endpoints(),
				'adminEndpoints'    => $this->get_admin_endpoints(),
				'frontLocalization' => $this->get_frontend_localizations(),
				'adminLocalization' => $this->get_admin_localizations(),
				'siteURL'            => site_url(),
				'security'           => wp_nonce_field( static::NONCE ),
				'endpointURLs'       => static::$endpoint_urls,
				'endpointPrefix'     => static::$rewrite_endpoint
		);
	}

	//======================================================================
	// GENERIC HELPER FUNCTIONS
	//======================================================================

	/**
	 * Generic Method for removing an element or elements from an array.
	 * @param $el
	 * @param $collection
	 * @param int $length
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function remove_element( $el, $collection, $length = 1 ) {
		$key = array_search( $el, $collection );

		if ( false === $key ) {
			throw new \Exception( 'Array value does not exist' );
		}

		return array_splice( $collection, $key, $length );
	}

	/**
	 * Recursively search an array for a give key.
	 * @param $needle
	 * @param $haystack
	 *
	 * @return bool|int|string
	 */
	private function recursive_array_search( $needle, $haystack ) {
		foreach ( $haystack as $key => $value ) {
			$current_key = $key;
			if ( $needle === $value ) {
				return $current_key;
			}

			if ( is_array( $value ) && $this->recursive_array_search( $needle, $value ) !== false ) {
				return $current_key;
			}
		}
		return false;
	}
}
