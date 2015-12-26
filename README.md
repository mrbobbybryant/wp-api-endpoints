# wp-api-endpoints

This library consists of two main classes. An abstract class which can
be used as a base for setting up ajax endpoints in WordPress. It utilizes the rewrite endpoints
instead of admin-ajax requests. The second class provides an api layout above the Base Class. This class
makes it easy to create and remove endpoints. It also makes it easy create localized data for use with your
newly created Endpoints.

There are a number of benefits to this approach.

1. A unique url that is server cachable for high traffic, reader facing endpoints such as infinite scroll.
2. Allows organization of api endpoints into a use specific class with a unique endpoint. For better code organization.
3. Makes API building and testing easier because you can request the url directly in the browser.

### Create an Endpoint.
To get started lets look at a very basic example.

```
use WP_API_ENDPOINTS\ENDPOINT_HANDLER as Handler;

function get_author_list() {
$authors = get_users('role=author');
wp_send_json_success( json_encode( $authors ) );
}

$ajax_handler = new Handler\Ajax_Handler();
$ajax_handler->add_admin_endpoint( 'get_author_list' );
```
In the above example, we have created our first endpoint.
  - Define the callback function which will handle any requests to the endpoint.
  - Initialize the Endpoint API Class.
  - Leveraged a method in the Endpoint API class called ```add_admin_endpoint()```

Thats it. Now if you navigate to yoursite.com/ajax/get_author_list, you will see a JSON feed of all users who have the role of author assigned. Another thing to point out is that this endpoint is an Admin Endponit, so only logged in users are able to hit it.

### Localization
Another common requirement by most WordPress Endpoints/AJAX processes is localization. In WordPress this is handled by a function call ```wp_localize_script()```.

And at a bare minimum, you will generally need at least two pieces of data.
  - The endpoint URL for your Ajax request.
  - A NONCE to confirm any incoming Ajax requests are save.

The Endpoint API classes provides a useful interface for creating those localizations. By default it will include the endpoint URL and a NONCE. Lets look at an example of this.
```
use WP_API_ENDPOINTS\ENDPOINT_HANDLER as Handler;
$ajax_handler = new Handler\Ajax_Handler();

$ajax_handler->add_admin_localization(
	'my_admin_endpoint',
	'ajax_handler_js',
	'ajaxAdminHandler',
	$localized_data
);
```

The API Method ```add_admin_localization``` accepts have paramenters:
  - ```endpoint (string) ``` **required** - The related Endpoint for this localized data.
  - ```Javascript Handler (string) ``` **required** - Just like ```wp_localized_script``` we need to associate this data to a specific javascript file/handle.
  - ```Unique Handle (string)``` **required** - This will be used to identify the localized data. In our example the unique name for this data will be ```ajaxAdminHandle```. Once registered, if you type that into a browser console, you will see the data listed.
  - ```Data (array)``` **optional** This is the array of data you would like to pass via this localization. The next example will show this in more detail. This param is optionl. By default it is an empty array.

### Passing Additional Data
Let's look at a more useful example of localizing data.
```
$data = array(
    'siteURL'   => esc_url( site_url() ),
    'siteTitle' => esc_html( bloginfo( 'name' ) ),
    'myCustomQuery' => esc_html( my_custom_query() )
);

$ajax_handler = new Handler\Ajax_Handler();

$ajax_handler->add_admin_localization(
	'my_admin_endpoint',
	'ajax_handler_js',
	'ajaxAdminHandler',
	$data
);

```

Here you can see another example of using ```add_admin_endpoint```. This time I actually spelled out an example of using localization to pass data inside of WordPress for use by our Javascript file ```ajax_handler_js```.
