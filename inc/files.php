<?php
/**
 * Functions as layers for WP Filesystem
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}


// wp-includes/file.php

if ( ! function_exists( 'wp_upload_file' ) ) :
/**
 * Uploads one generic file
 * 
 * @deprecated 1.1
 * @since 1.1
 */
function wp_upload_file( $post_id, $file, $info = '' ) {

	$overrides = array( 'test_form' => false);
	require_once(ABSPATH . 'wp-admin/includes/file.php');

	if ( !isset( $info['time'] ) )
		$info['time'] = date( 'Y/m' );

	if ( is_array( $file ) ) {
		$wp_file = wp_handle_upload( $file, array( 'test_form' => false ), $info['time'] );

	} elseif ( is_string( $file ) ) {

		$filename = download_url( esc_url( $file ) );
		chmod( $filename, 0755 );
		$mime = wp_check_filetype( $filename );
		
		$file_array = array(
			'name'      => get_post( $post_id )->post_title,
			'type'      => $mime['type'],
			'size'      => filesize( $filename ),
			'tmp_name'  => $filename
		);
		
		$wp_file = wp_handle_sideload( $file_array, array( 'test_form' => false, 'test_upload' => false, 'test_type' => false ), $info['time'] );
		unlink( $filename );

	}

	$wp_file['id'] = wp_insert_attachment( 
		array(
			'guid' => $wp_file['url'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $wp_file['file'] ) ),
			'post_mime_type' => $wp_file['type'],
			'post_content' => '',
			'post_status' => 'inherit',
		), 
		$wp_file['file'], (int) $post_id 
	);


	// If image handle the image stuff

	if ( wp_is_file_image( $wp_file['file'] ) ) {
		require_once( ABSPATH . '/wp-admin/includes/image.php' );
		$image_data = wp_generate_attachment_metadata( $wp_file['id'], $wp_file['file'] );
		wp_update_attachment_metadata( $wp_file['id'], $image_data );

	}

	return $wp_file['id'];
	
}
endif;


// wp-includes/media.php

if ( ! function_exists( 'wp_upload_media' ) ) :
/**
 * Uploads one generic media (audio, video)
 * 
 * @deprecated 1.1
 * @since 1.1
 */
function wp_upload_media( $post_id, $file, $info = '' ) {

	media_handle_upload($file_id, $post_id, $post_data = array(), $overrides = array( 'test_form' => false ));
	media_handle_sideload($file_array, $post_id, $desc = null, $post_data = array());
	
}
endif;


// wp-includes/image.php

if ( ! function_exists( 'wp_upload_image' ) ) :
/**
 * Uploads one image
 * 
 * @deprecated 1.1
 * @since 1.1
 */
function wp_upload_image( $post_id, $file, $info = '' ) {

	$overrides = array( 'test_form' => false);
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');

	if ( !isset( $info['time'] ) )
		$info['time'] = date( 'Y/m' );

	if ( is_array( $file ) ) {
		$_FILES[0] = array(

		);
		return media_handle_upload( $file, array( 'test_form' => false ), $info['time'] );

	} elseif ( is_string( $file ) ) {

		$tmp = download_url( esc_url( $file ) );
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
		$file_array['name'] = basename($matches[0]);
		$file_array['tmp_name'] = $tmp;
		
		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';

		}

		return media_handle_sideload( $file_array, $post_id );

	}

	media_handle_upload($file_id, $post_id, $post_data = array(), $overrides = array( 'test_form' => false ));
	media_handle_sideload($file_array, $post_id, $desc = null, $post_data = array());
	
}
endif;



// wp-includes/template-tags.php

if ( ! function_exists( 'is_attachment_image' ) ) :
/**
 * Check if the the attachment is an image
 *
 * @since 3.6
 */
function is_attachment_image( $attachment_id ) {
	return ( 'image' == substr( get_post( $attachment_id )->post_mime_type, 0, 5 ) );

}
endif;
