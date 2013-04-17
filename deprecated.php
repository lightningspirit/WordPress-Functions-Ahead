<?php
/**
 * Deprecated functions
 * 
 * Where functions come to die alone...
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}




// wp-admin/includes/dashboard.php

if ( ! function_exists( 'remove_dashboard_widget' ) ) :
/**
 * Removes dashboard widget
 * 
 * @since 3.5
 * @uses $wp_meta_boxes 
 *
 * @deprecated 3.5.5
 * 
 * @param string $id 
 * @param string $position
 * @param string $priority
 * @return boolean 
 */
function remove_dashboard_widget( $id, $position = 'normal', $priority = 'core' ) {
	_deprecated_function( __FUNCTION__, '3.5.5', 'remove_meta_box( $slug, "dashboard" )' );
	
	return remove_meta_box( $id, 'dashboard', $priority );
	
	/*
	global $wp_meta_boxes;
	
	if ( isset( $wp_meta_boxes['dashboard'][ $position ][ $priority ][ $id ] ) )
		unset( $wp_meta_boxes['dashboard'][ $position ][ $priority ][ $id ] );
	
	do_action( 'remove_dashboard_widget', $id, $position, $priority );
	
	return;*/
	
}
endif;




// wp-includes/post.php

if ( ! function_exists( 'save_post_metas' ) ) :
/**
 * Save post metas
 * @since 3.6
 * 
 * @deprecated 3.5.5
 * 
 */
function save_post_metas( $post_id, $metas ) {
	_deprecated_function( __FUNCTION__, '3.5.5', '' );
	
	foreach ( (array) $metas as $field => $value ) {
		$meta = get_post_meta( $post_id, $field, true );
			
		if ( $meta && '' == $value )
			delete_post_meta( $post_id, $field );
			
		elseif ( $meta && $value )
			update_post_meta( $post_id, $field, $value, $meta );
			
		else
			add_post_meta( $post_id, $field, $value );
			
	}

}
endif;




// wp-includes/functions.php

if ( ! function_exists( 'seconds_to_time' ) ) :
/**
 * Transform timestamp into 
 *
 * @deprecated 3.5.5
 * 
 * @since 3.5.1
 */
function seconds_to_time( $seconds, $format = 'H:m:s' ) {
	_deprecated_function( __FUNCTION__, '3.5.5', 'format_time()' );
	
	return format_time( (int) $seconds, $format, false );
	
	/*
	// extract hours
	$hours = floor($seconds / (60 * 60));
	
	// extract minutes
	$divisor_for_minutes = $seconds % ( 60 * 60 );
	$minutes = floor( $divisor_for_minutes / 60 );
	
	// extract the remaining seconds
	$divisor_for_seconds = $divisor_for_minutes % 60;
	$seconds = ceil( $divisor_for_seconds );
	
	$format = str_replace( 'H', str_pad( $hours, 2, "0", STR_PAD_LEFT ), $format );
	$format = str_replace( 'm', str_pad( $minutes, 2, "0", STR_PAD_LEFT ), $format );
	$format = str_replace( 's', str_pad( $seconds, 2, "0", STR_PAD_LEFT ), $format );
	
	return $format;*/
	
}
endif;



