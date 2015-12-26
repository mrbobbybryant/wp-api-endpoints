<?php
/*
Plugin Name: Ajax Handler
Description: A library for creating Ajax processes in WordPress.
Version:     0.0.1
Author:      Bobby Bryant
Author URI:  http://mrbobbybryant.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: ajax-handler
*/

// Useful global constants
define( 'AJAX_EP_HANDLER_VERSION', '0.1.0' );
define( 'AJAX_EP_HANDLER_URL',     plugin_dir_url( __FILE__ ) );
define( 'AJAX_EP_HANDLER_PATH',    dirname( __FILE__ ) . '/' );

require_once( 'includes/class-base-api.php' );
require_once( 'includes/class-ajax-handler.php' );

function ajax_enqueue_scripts() {

	wp_enqueue_script('ajax_handler_js',
			AJAX_EP_HANDLER_URL . 'assets/js/ajax-handler.js',
			array(),
			AJAX_EP_HANDLER_VERSION,
			true);

	wp_enqueue_style(
			'ajax_handler_css',
			AJAX_EP_HANDLER_URL . 'assets/css/ajax-handler.css',
			array(),
			AJAX_EP_HANDLER_VERSION
	);

}
add_action( 'wp_enqueue_scripts', 'ajax_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'ajax_enqueue_scripts' );

/**
 * Arguments can also be used directly in the method
 *
 * @param $arg1
 * @param $arg2
 */
function another_endpoint( $arg1 = 'default', $arg2 = array() ) {
//		if ( $response = another_function_call( $arg1, $arg2 ) ) {
//			wp_send_json( $response );
//		} else {
//			wp_send_json_error( 'No fake results' );
//		}
	wp_send_json_success( 'I am the another endpoint' );
}

function documentation() {
	$args = func_get_args();
	wp_send_json_success( 'i am the Docs' );
}

function admin_endpoint() {
	wp_send_json_success( 'i am the admin endpoint' );
}
$ajax_handler = new Ajax_Handler();


$ajax_handler->add_frontend_endpoint( 'documentation' );
$ajax_handler->add_admin_endpoint( 'admin_endpoint' );
$debug_data = array(
		'front_endpoints'    => $ajax_handler->get_frontend_endpoints(),
		'admin_endpoints'    => $ajax_handler->get_admin_endpoints(),
		'front_localization' => $ajax_handler->get_frontend_localizations(),
		'admin_localization' => $ajax_handler->get_admin_localizations(),
		'siteURL'            => site_url()
);

$ajax_handler->add_frontend_localization(
		'another_endpoint',
		'ajax_handler_js',
		'ajaxHandlerDefault',
		$debug_data
);

$ajax_handler->add_admin_localization(
		'admin_endpoint',
		'ajax_handler_js',
		'ajaxAdminHandler',
		$debug_data
);

function ajax_bootstrap() {
	$ajax_handler = new Ajax_Handler();
	$ajax_handler->setup();
}
add_action( 'wp_loaded', 'ajax_bootstrap' );
