<?php
/**
 * Customize arbitrary functions
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}



// wp-includes/functions.php

if ( ! function_exists( '__return_empty_string' ) ) :
/**
 * Returns an empty string.
 *
 * Useful for returning an empty string to filters easily.
 *
 * @since 3.5.1
 * @see __return_empty_string()
 * @return string Empty string
 */
function __return_empty_string() {
	return '';
}
endif;



// wp-includes/functions.php

if ( ! function_exists( 'current_url' ) ) :
/**
 * Returns the current URL
 * 
 * @since 3.5.1
 * 
 * @return string The current URL
 */
function current_url() {
	global $wp;
	return add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
	
}
endif;




// wp-includes/functions.php

if ( ! function_exists( 'time_diference' ) ) :
/**
 * Computes the diference of unix times
 *
 * @since 3.5.1
 *
 * @param int|string $time_a The first time
 * @param int $time_b The second time
 * @param string $format Can take object, mysql, timestamp. Defaults to timestamp
 *
 * @return mixed
 *
 */
function time_diference( $time_a, $time_b, $format = '' ) {
		
	if ( !is_numeric( $time_a ) )
		$time_a = strtotime( $time_a );
	
	if ( !is_numeric( $time_b ) )
		$time_b = strtotime( $time_b );
	
	// COmputes the diference
	$timediff = $time_a - $time_b;

	switch ( strtolower( $format ) ) {

		case 'mysql' :
			return date( 'Y-m-d H:i:s', abs( $timediff ) );
			break;

		case 'object' :
			return (object) array(
				'signal' => ( abs( $timediff ) == $timediff ? '+' : '-' ),
				'years' => round( abs( (int) $timediff / ( 24*60*60*365 ) ) ),
				'months' => date( 'n', $timediff ),
				'weeks' => round( abs( (int) $timediff / ( 24*60*60*7 ) ) ),
				'days' => round( abs( (int) ( $timediff / ( 24*60*60 ) ) ) ),
				'hours' => date( 'H', $timediff ),
				'minutes' => date( 'i', $timediff ),
				'seconds' => date( 's', $timediff ),
				'microseconds' => date( 'u', $timediff ),
				'iso8601' => date( 'c', $timediff ),
				'rfc2822' => date( 'r', $timediff ),
			);
			break;
		
		case 'timestamp' :
		default :
			return abs( $timediff );
			break;

	}

	return false;

}
endif;


// wp-includes/functions.php

if ( ! function_exists( 'format_time' ) ) :
/**
 * Computes the diference of unix times
 *
 * @since 3.5.1
 *
 * @param int|string $time The time string/int
 * @param string $return Can take mysql, default (format set in wp-admin), object or any other format
 * @param bool $i18n If returned attend to the current timezone (Default true)
 *
 * @return mixed If is a string retunr int unixtimestamp. If numeric returns formated string or object.
 *
 */
function format_time( $time, $format = 'default', $i18n = true ) {
	
	if ( ! is_numeric( $time ) )
		$time = strtotime( $time );
	
	
	switch ( strtolower( $format ) ) {

		case 'mysql' :
			return $i18n ? date_i18n( 'Y-m-d H:i:s', (int) $time ) : date( 'Y-m-d H:i:s', (int) $time );
			break;

		case 'object' :
			$std = new stdClass();
			
			foreach ( array( 
				'L', 'Y', 'y', 'W', 'F', 'm', 'M', 'n', 't', 'd', 'D', 'j', 'l', 'N', 'S', 'w', 'z',
				'a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'e', 'I', 'O', 'P', 'T', 'Z', 'r', 'U' ) as $ch )
			
				$std->$ch = $i18n ? date_i18n( $ch, (int) $time ) : date( $ch, (int) $time );
				
			return $std;
			break;
			
		case 'default' :
			return $i18n ? date_i18n( get_option( 'date_format' ), (int) $time ) : date( get_option( 'date_format' ), (int) $time );
			break;
			
		default :
			return $i18n ? date_i18n( $format, (int) $time ) : date( $format, (int) $time );
			break;

	}

	return false;

}
endif;



// wp-includes/functions.php

if ( ! function_exists( 'filter_url' ) ) :
/**
 * Filters the URL returning an object of domain, paths and query string
 * 
 * Returns an object with the folllowing information
 * scheme, SSL, username, password, host, port, array of path, array of query parameters and fragment
 * 
 * { 'scheme', 'ssl', 'user', 'pass', 'host', 'port', 'query', 'fragment' }
 * 
 * @param string $url The URL
 * @return object
 * @since 3.5.5
 */
function filter_url( $url ) {
	$url = parse_url( $url );
	
	// Detect SSL
	if ( isset( $url['scheme'] ) && in_array( $url['scheme'], array( 'https', 'ftps', 'ssl' ) ) ) {
		$url['ssl'] = true;
	} elseif ( isset( $url['port'] ) && 443 == $url['port'] ) {
		$url['ssl'] = true;
	} else {
		$url['ssl'] = false;
	}
	
	// Arrayfy the path
	if ( isset( $url['path'] ) && false !== $pos = strpos( $url['path'], '/' ) ) {
		$url['path'] = split( '/', trim( $url['path'], '/' ) );
		
	}
	
	// Parse the query string
	if ( isset( $url['query'] ) ) {
		parse_str( $url['query'], $url['query'] );
	}
	
	return (object) $url;
	
}
endif;



// wp-includes/functions.php

if ( ! function_exists( 'display_url' ) ) :
/**
 * Removes the protocol from a URL
 * If $remove_www is set than removes www. too
 * 
 * @param string $url The URL
 * @param bool $remove_www True removes the www. (Defaults to true)
 * 
 * @return string the filtered URL
 *
 * @since 3.5.1
 */
function display_url( $url, $remove_www = true ) {
	$url = preg_replace( "/\s*[^:]+:\/\/?/", "$2", $url );
	
	if ( $remove_www )
		$url = preg_replace( "/^www\.(?)/", "$2", $url );
	
	return $url;
	
}
endif;


// wp-includes/functions.php

if ( ! function_exists( 'wp_parse_attrs' ) ) : 
/**
 * Parses an array of attr => value pairs
 * and returns an HTML version for tag attributes.
 * 
 * It is possible to modify the output format for each $key/$value pair,
 * using the third parameter. You can modify the global
 * output with 'wp_parse_attr_output' filter too.
 * 
 * If $value is an array or object, the return $value will be serialized.
 * 
 * @param array $attrs The array to parse
 * @param array $defaults Key/value pair of default values. Default is empty.
 * @param string $format The format for each pair. Use %key% and %value% tags to be replace. Default is `%key%="%value%" `
 * @param string $glue the separator. Type ARRAY if you want to return the array. Default is space.
 * 
 * @return string the output
 *
 * @since 3.5.7
 */
function wp_parse_attrs( $attrs, $defaults = '', $format = '%key%="%value%"', $glue = ' ' ) {
	$attrs = wp_parse_args( $attrs, $defaults );
	
	if ( empty( $attrs ) )
		return false;
	
	$r = array();
	foreach ( $attrs as $key => $value ) {
		if ( is_array( $value ) || is_object( $value ) )
			$value = maybe_serialize( $value );
		
		$r[] = str_replace( array( '%key%', '%value%' ), array( $key, $value ), $format );
		
	}
	
	
	// Allow devs to be able to hack the output
	$r = apply_filters( 'wp_parse_attr_output', $r, $attrs, $format );
	
	switch ( $glue ) {
		case 'ARRAY' :
			return $r;
			
		default :
			return trim( join( $glue, $r ) );
		
	}
	
}
endif;


// wp-includes/functions.php

if ( ! function_exists( 'get_http_host' ) ) :
/**
 * Get the HTTP Host
 * 
 * @return string The HTTP Host
 *
 * @since 3.5.1
 */
function get_http_host() {
	return isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	
}
endif;



// wp-includes/functions.php

/**
 * Redirect with POST data.
 *
 * @since 3.5.1
 * 
 * @param string $url URL.
 * @param array $post_data POST data. Example: array('foo' => 'var', 'id' => 123)
 * @param array $headers Optional. Extra headers to send.
 * @return void 
 */
function wp_redirect_post( $url, array $data, array $headers = null ) {
	$params = array(
		'http' => array(
			'method' => 'POST',
			'content' => http_build_query( $data )
		)
	);
	
	if ( ! is_null( $headers ) ) {
		$params['http']['header'] = '';
		foreach ( $headers as $k => $v ) {
			$params['http']['header'] .= "$k: $v\n";
	
		}
	
	}
	
	$ctx = stream_context_create( $params );
	$fp = @fopen( $url, 'rb', false, $ctx );
	
	if ( $fp ) {
		echo @stream_get_contents( $fp );
		die();
	
	}
	
}



// wp-includes/functions.php

if ( ! function_exists( 'concatenate' ) ) :
/**
 * Return a concatenated string
 *
 * @param string $excerpt The string to concatenate 
 * @param integer $limit Limiting excerpt
 * @return excerpt
 */
function concatenate( $excerpt, $limit = false ) {
	
	if ( ! $limit )
		$limit = apply_filters( 'excerpt_length', 55 );


	mb_internal_encoding( 'UTF-8' );

	if ( mb_strlen( $excerpt ) > $limit )
		$dots = apply_filters( 'excerpt_more', '[...]' );


	$excerpt = mb_substr( esc_html( $excerpt ), 0, $limit );

	return $excerpt.$dots;

}
endif;




// wp-includes/functions.php

if ( ! function_exists( 'compare' ) ) :
/**
 * Compare anything: strings, arrays, objects, etc...
 *
 * @since 3.5.3
 *
 * @param mixed $a
 * @param mixed $b
 * @return int
 */
function compare( $a, $b ) {
	if ( $a == $b ) return 0;
    if ( $a > $b ) return 1;
    if ( $a < $b ) return -1;
	
}
endif;




if ( ! function_exists( 'is_url' ) ) :
/**
 * Check if the given string is a url
 * 
 * @since 3.5.5
 * 
 * @param string $url URL
 * @return bool 
 */
function is_url( $url ) {
	if ( filter_var( $url, FILTER_VALIDATE_URL ) )
		return true;

	return false;

}
endif;




if ( ! function_exists( 'is_IP' ) ) :
/**
 * Check if the given string is a IP address
 * 
 * @since 3.5.5
 * 
 * @param string $IP URL
 * @return bool 
 */
function is_IP( $IP ) {
	if ( filter_var( $IP, FILTER_VALIDATE_IP ) )
		return true;

	return false;

}
endif;


