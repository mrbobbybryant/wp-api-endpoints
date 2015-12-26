<?php
/*
Plugin Name: WP API ENDPOINT
Description: A library for creating and managing custom endpoints in WordPress.
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
			true
	);

	wp_enqueue_script('prism_js',
			AJAX_EP_HANDLER_URL . 'assets/vendor/prismjs/prism.js',
			array(),
			'',
			true
	);

	wp_enqueue_style(
			'ajax_handler_css',
			AJAX_EP_HANDLER_URL . 'assets/css/ajax-handler.css',
			array(),
			AJAX_EP_HANDLER_VERSION
	);

	wp_enqueue_style(
			'prism_css',
			AJAX_EP_HANDLER_URL . 'assets/vendor/prismjs/prism.css',
			array(),
			''
	);

}
add_action( 'wp_enqueue_scripts', 'ajax_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'ajax_enqueue_scripts' );

function documentation() {
	?>
		<div style="width: 70%; margin: 0 auto;">
			<h1>Documentation</h1>
			<h3>Overview</h3>
			<p>
				The WP-API_endpoints Library provides an esy to use interface for creating and
				using custom API Endpoints.

				The heart of the library is two classes. A Base Abstract class that contains the
				core logic for creating endpoints. An API class to interacting with the base enpoint class.

				This library has been designed to allow you the ability to create classes in a procedural
				fashion via the Endpoint API Class.

				However, because the core logic is in an Abstract class, you can also simply extend it with your
				own class based approach.

				But to get started let take a quick look at how we can create endpoints using the Endpoint API Class.
			</p>
			<pre>
				<code class="language-php">
					function get_author_list() {
						$authors = get_users('role=author');
						wp_send_json_success( json_encode( $authors ) );
					}

					$ajax_handler = new Ajax_Handler();
					$ajax_handler->add_admin_endpoint( 'get_author_list' );
				</code>
			</pre>
			<p>
				In the above example, we have created our first endpoint.
			</p>
			<ul>
				<li>
					Step one is to define the
					callback function which will handle any requests to the endpoint.
				</li>
				<li>
					The second step is to initialize the Endpoint API Class.
				</li>
				<li>
					The third step leverages a method in the Endpoint API class
					called <code class="language-php">add_admin_endpoint()</code>
				</li>
			</ul>
			<p>
				Thats it. Now if you navigate to yoursite.com/ajax/get_author_list, you will see
				a JSON feed of all users who have the role of author assigned. Another thing to point
				out is that this endpoint is an Admin Endponit, so only logged in users are able
				to hit it.
			</p>
		</div>

	<?php
}

function admin_endpoint() {
	$users = get_users();
	wp_send_json_success( json_encode( $users ) );
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
