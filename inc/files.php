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


// wp-includes/media.php

if ( ! function_exists( 'wp_upload_attach_file' ) ) :
/**
 * Uploads the images and unites with the post
 * 
 * @deprecated 1.1
 * @since 1.1
 */
function wp_upload_attach_file( $post_id, $file ) {

	$overrides = array( 'test_form' => false);
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	
	
	if ( is_array( $file ) ) {
		$wp_file = wp_handle_upload( $file, $overrides );
	
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
		
		$wp_file = wp_handle_sideload( $file_array, array( 'test_form' => false, 'test_upload' => false, 'test_type' => false ) );
		unlink( $filename );
		
	}

	$wp_file['id'] = wp_insert_attachment( array(
		'guid' => $wp_file['url'],
		'post_title' => get_post( $post_id )->post_title . '-' . preg_replace( '/\.[^.]+$/', '', basename( $wp_file['file'] ) ),
		'post_mime_type' => $wp_file['type'],
		'post_content' => '',
		'post_status' => 'inherit',

	), $wp_file['file'], (int) $post_id );


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

if ( ! function_exists( 'media_upload' ) ) :
/**
 * Adds an attachment to the media gallery
 * 
 * @since 1.1
 * 
 * @param array|string $file Can be URL, Local path or a single $_FILES array
 * @param int $post_id Attach to this post ID. Optional.
 * @param array $args A set of args to pass.
 * @return int The attachment ID
 *  
 */
function media_upload( $file, $post_id = '', $args = '' ) {
	
	if ( empty( $file ) )
		return new WP_Error( 'file_empty', __( 'The file argument is empty.' ) );
	
	extract( wp_parse_args( $args, array(
		'attachment_title' => '',
		)
	));
	
	
	// Is a localpath
	if ( file_exists( $file ) ) {
		
	
	// Is a $_FILES single array
	} elseif ( is_array( $file ) ) {
		
		
	} elseif ( is_url( $file ) ) {
		
		
	}
	
	

	$overrides = array( 'test_form' => false);
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	
	
	if ( is_array( $file ) ) {
		$wp_file = wp_handle_upload( $file, $overrides );
		$wp_file['id'] = wp_insert_attachment( array(
			'guid' => $wp_file['url'],
			'post_title' => get_post( $post_id )->post_title . '-' . preg_replace( '/\.[^.]+$/', '', basename( $wp_file['file'] ) ),
			'post_mime_type' => $wp_file['type'],
			'post_content' => '',
			'post_status' => 'inherit',
	
		), $wp_file['file'], (int) $post_id );
	
	
		// If image handle the image stuff
	
		if ( wp_is_file_image( $wp_file['file'] ) ) {
			require_once( ABSPATH . '/wp-admin/includes/image.php' );
			$image_data = wp_generate_attachment_metadata( $wp_file['id'], $wp_file['file'] );
			wp_update_attachment_metadata( $wp_file['id'], $image_data );
	
		}
	
		return $wp_file['id'];
	
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

}
endif;



if ( ! function_exists( 'wp_is_file_image' ) ) :
/**
 * Check if the file is an image
 *
 * @since 3.6
 */
function wp_is_file_image( $file ) {
	if ( @getimagesize( $file ) )
		return true;

	return false;

}
endif;

