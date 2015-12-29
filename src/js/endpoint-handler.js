var endpointPrefix = endpointData.siteURL + '/ajax/';
var jsEndpointUrl = endpointPrefix + 'ajax_handler';

var get = function(el) {
    return el;
};
var compose = function(f,g) {
    return function(x) {
        return f(g(x));
    };
};
var find = function(el) {
    return function(fn) {
        return el.map(fn);
    }
};

var getElement = function(data) {
    return data === el;
};
var findEndpoint = compose( find, getElement );

findElement( array, el );



var ajax_request = function( endpoint, type, data, successCallback, errorCallback  ) {
    $.ajax({
        url: endpoint,
        type: type,
        dataType: 'json',
        data: { data }
    }).done( successCallback )
        .error( errorCallback );
};

var findEndpoint = function( fn_name ) {
    var prefix = ;
    return ajax_request( 'ajax_handler', 'POST',  );
};

var addFrontEndpoint = function( fn_name ) {

};

var addAdminEndpoint = function( fn_name ) {

};

var addFrontLocalization = function( endpoint, js_handle, local_handle, data ) {

};

var addAdminLocalization = function( endpoint, js_handle, local_handle, data ) {

};