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
	protected static $front_endpoints = array( 'another_endpoint' );

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
	 * Bootstraps the Class and contains all required WordPress Hooks.
	 */
	public function setup() {
		$this->add_frontend_endpoint( 'documentation' );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_localized_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_localized_data' ) );
	}

	/**
	 * Loops through and runs wp_localize_script for all registered localizations.
	 */
	public function enqueue_localized_data() {

		//TODO throw exception if script handle does not exist.
		if ( is_admin() && ! empty( static::$admin_localized_data ) ) {
			$this->register_localizations( static::$admin_localized_data );
		}

		if ( ! is_admin() && ! empty( static::$front_localized_data ) ) {
			$this->register_localizations( static::$front_localized_data );
		}
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
	 * @return array|bool false
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
	 * @return array|bool false
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

	public function get_frontend_endpoints() {
		return self::$front_endpoints;
	}

	public function get_admin_endpoints() {
		return self::$admin_endpoints;
	}

	public function get_frontend_localizations() {
		return static::$front_localized_data;
	}

	public function get_admin_localizations() {
		return static::$admin_localized_data;
	}

	//======================================================================
	// INTERNAL API METHODS
	//======================================================================

	private function add_endpoint( $func_name, $context ) {
		if ( function_exists( $func_name ) || method_exists( $this, $func_name ) ) {
			$context = $this->set_context( $context );
			return array_push( static::$$context['endpoint'], $func_name );
		}
		throw new \Exception( 'Endpoint function does not exist' );
	}

	private function remove_endpoint( $func_name, $context ) {
		if ( in_array( $func_name, $context ) ) {
			$context = $this->set_context( $context );
			return $this->remove_element( $func_name, $this->$context['collection'] );
		}
		throw new \Exception( 'Cannot remove an endpoint that doesn\'t exist' );
	}

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

	//TODO need to make it possible to localize no data.
	private function add_localization( $endpoint, $js_handle, $local_handle, $data = array(), $context ) {

		if ( true ) {
			$context = $this->set_context( $context );

			//Create an endpoint if one doesn't exist.
			if ( ! in_array( $endpoint, static::$$context['endpoint'] ) ) {
				call_user_func( array( $this, $context['method'] ), $endpoint );
			}

			if ( false !== $this->recursive_array_search( $local_handle, static::$$context['collection'] ) ) {
				return wp_parse_args(
						static::$$context['collection'][ $local_handle ]['data'],
						$data
				);
			}
			$temp[$local_handle] = array(
					'endpoint'     => $endpoint,
					'endpointUrl'  => $context['url'] . '/' . $endpoint,
					'jsHandle'     => $js_handle,
					'localHandle'  => $local_handle,
					'data'         => $data,
//					'security'     => wp_create_nonce( 'hewufihweu' )
			);
			return static::$$context['collection'] = array_merge( static::$$context['collection'], $temp );
		}
		return false;
	}

	//======================================================================
	// CLASS SPECIFIC HELPER FUNCTIONS
	//======================================================================

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

	private function get_endpoint_url() {
		return site_url( 'ajax' );
	}

	private function register_localizations( $localized_array ) {
		foreach ( $localized_array as $handle ) {
			wp_localize_script( $handle['jsHandle'], $handle['localHandle'], $handle );
		}
	}

	//======================================================================
	// GENERIC HELPER FUNCTIONS
	//======================================================================

	private function remove_element( $el, $collection, $length = 1 ) {
		$key = array_search( $el, $collection );

		if ( false === $key ) {
			throw new \Exception( 'Array value does not exist' );
		}

		return array_splice( $collection, $key, $length );
	}

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


class MyAjax extends BASE_API\Base_API {

	public function register_default_endpoint() {

		$data = $this->localize_debug_data();
		$this->add_admin_localization(
				'documentation',
				'ajax_handler_js',
				'ajaxHandlerDefault',
				$this->localize_debug_data()
		);
	}

	private function localize_debug_data() {
//		if ( defined( 'AJAX_HANDLER_DEBUG' ) && true === 'AJAX_HANDLER_DEBUG' ) {
//			return array(
//				'front_endpoints'    => $this->get_frontend_endpoints(),
//				'admin_endpoints'    => $this->get_admin_endpoints(),
//				'front_localization' => $this->get_frontend_localizations(),
//				'admin_localization' => $this->get_admin_localizations()
//			);
//		}
		return array(
				'front_endpoints'    => $this->get_frontend_endpoints(),
				'admin_endpoints'    => $this->get_admin_endpoints(),
				'front_localization' => $this->get_frontend_localizations(),
				'admin_localization' => $this->get_admin_localizations(),
				'siteURL'            => site_url()
		);

//		return array();
	}

	private function is_assoc( $array ) {
		return (bool)count( array_filter( array_keys( $array ), 'is_string' ) );
	}

	private function is_valid_localization( $js_handle, $data ) {

		if ( empty( $data ) ) {
			throw new \Exception( 'Data argument was empty' );
		}

		if ( ! is_array( $data ) ) {
			throw new \Exception( 'Data argument must be an array' );
		}

		if ( ! $this->is_assoc( $data ) ) {
			throw new \Exception( 'Data argument must be an associative array' );
		}

		$values = array_values( $data );
		if ( ! function_exists( $values[0] ) ) {
			throw new \Exception( 'Function referenced in the Data array does not exist.' );
		}

		if ( ! wp_script_is( $js_handle ) ) {
			throw new \Exception( 'Javascript handle has not been registered with WordPress.' );
		}

		return true;
	}

}
