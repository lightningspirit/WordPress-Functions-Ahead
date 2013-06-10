<?php
/**
 * Post object functions
 * 
 * @package WordPress
 * @subpackage Post Type Functions
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}




// wp-includes/post.php

if ( ! function_exists( 'unregister_taxonomy_from_object_type' ) ) :
/**
 * Remove an already registered taxonomy from an object type.
 *
 * @since 3.5
 *
 * @uses $wp_taxonomies Modifies taxonomy object
 *
 * @param string $taxonomy Name of taxonomy object
 * @param string $object_type Name of the object type
 * @return bool True if successful, false if not
 */
function unregister_taxonomy_from_object_type($taxonomy, $object_type) {

	global $wp_taxonomies;

	if ( ! isset( $wp_taxonomies[ $taxonomy ]) )
		return false;

	if ( ! get_post_type_object( $object_type ) )
		return false;

	foreach ( array_keys( $wp_taxonomies['category']->object_type ) as $array_key ) {
		if ( $wp_taxonomies['category']->object_type[ $array_key ] == $object_type ) {
			unset( $wp_taxonomies['category']->object_type[ $array_key ] );
			return true;
		}
	}
	return false;

}
endif;



// wp-includes/post.php

if ( ! function_exists( 'remove_post_type' ) ) :
/**
 * Removes post types
 * 
 * Hooks: remove_post_type[ $post_type ]
 * 
 * @since 3.5
 *
 * @uses $wp_post_types 
 *
 * @param string $post_type Post type key, must not exceed 20 characters
 * @return boolean 
 */
function remove_post_type( $post_type ) {
	global $wp_post_types, $wp_rewrite;

	if ( !is_array($wp_post_types) )
		$wp_post_types = array();

	if ( ! post_type_exists( $post_type ) )
		return false;

	foreach ( (array) $wp_post_types[$post_type]->taxonomies as $taxonomy ) {
		unregister_taxonomy_from_object_type( $taxonomy, $post_type );
	}
	
	if ( isset( $wp_post_types[ $post_type ] ) )
		unset( $wp_post_types[$post_type] );
	
	do_action( 'remove_post_type', $post_type );
	
	return true;
	
}
endif;


// wp-includes/taxonomy.php

if ( ! function_exists( 'remove_taxonomy' ) ) :
/**
 * Removes taxonomies
 *
 * Notice that removing default category taxonomy from WordPress
 * can lead into unpredictable experiences since WP_Query
 * uses category as a builtin object and dont check for its availability.
 * 
 * Hooks: remove_taxonomy( $taxonomy )
 * 
 * @since 3.5
 *
 * @uses $wp_taxonomies 
 *
 * @param string $taxonomy 
 * @return boolean 
 */
function remove_taxonomy( $taxonomy ) {
	global $wp_taxonomies;

	if ( !is_array($wp_taxonomies) )
		$wp_taxonomies = array();

	if ( ! taxonomy_exists( $taxonomy ) )
		return false;
	
	
	if ( isset( $wp_taxonomies[$taxonomy]  ) )
		unset( $wp_taxonomies[$taxonomy] );
	
	do_action( 'remove_taxonomy', $taxonomy );
	
	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'remove_post_status' ) ) :
/**
 * Removes post status
 * 
 * Hooks: remove_post_status( $post_status )
 * 
 * @since 3.6
 *
 * @uses $wp_post_statuses 
 *
 * @param string $post_status 
 * @return boolean 
 */
function remove_post_status( $post_status ) {
	global $wp_post_statuses;

	if ( !is_array($wp_post_statuses) )
		$wp_post_statuses = array();

	if ( ! post_status_exists( $post_status ) )
		return false;
	
	
	if ( isset( $wp_post_statuses[ $post_status ]  ) )
		unset( $wp_post_statuses[ $post_status ] );
	
	do_action( 'remove_post_status', $post_status );
	
	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'post_status_exists' ) ) :
/**
 * Checks that the post status exists.
 * 
 * @since 3.6
 * 
 * @uses $wp_post_statuses 
 *
 * @param string $post_status 
 * @return boolean 
 */
function post_status_exists( $post_status ) {
	global $wp_post_statuses;

	return isset( $wp_post_statuses[ $post_status ] );
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'register_status_for_post_type' ) ) :
/**
 * Associates a post status to a post type
 * 
 * @since 3.6
 * 
 * @uses $wp_post_types
 * @uses $wp_post_statuses 
 *
 * @param string $post_status 
 * @param string $post_type 
 * @return boolean 
 */
function register_status_for_post_type( $post_status, $post_type ) {
	global $wp_post_types, $wp_post_statuses;

	if ( ! post_status_exists( $post_status ) )
		return false;

	if ( ! post_type_exists( $post_type ) )
		return false;

	if ( ! isset( $wp_post_types[ $post_type ]->statuses ) )
		$wp_post_types[ $post_type ]->statuses = array();

	if ( ! in_array( $post_status, $wp_post_types[ $post_type ]->statuses ) )
		$wp_post_types[ $post_type ]->statuses[] = $post_status;

	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'register_post_type_statuses' ) ) :
/**
 * Associates a post status to a post type
 * 
 * @since 3.6
 * 
 * @uses $wp_post_types
 * @uses $wp_post_statuses 
 *
 * @param string $post_type 
 * @param array|string $post_status es
 * @return boolean 
 */
function register_post_type_statuses( $post_type, $post_statuses, $default = '' ) {
	global $wp_post_types, $wp_post_statuses;

	if ( ! post_type_exists( $post_type ) )
		return false;

	if ( ! is_array( $post_statuses ) )
		$post_statuses[] = $post_statuses;


	foreach ( $post_statuses as $post_status ) {
		if ( post_status_exists( $post_status ) )
			register_status_for_post_type( $post_status, $post_type );

	}

	if ( ! empty( $default ) && post_status_exists( $default ) )
		set_default_post_type_status( $post_type, $default );


	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'set_default_post_type_status' ) ) :
/**
 * Sets the default post type status
 * 
 * @since 3.6
 * 
 * @uses $wp_post_types
 * @uses $wp_post_statuses
 *
 * @param string $post_type 
 * @param string $post_status 
 * @return boolean 
 */
function set_default_post_type_status( $post_type, $post_status ) {
	global $wp_post_types, $wp_post_statuses;

	if ( ! post_type_exists( $post_type ) )
		return false;

	if ( ! post_status_exists( $post_status ) )
		return false;

	$wp_post_types[ $post_type ]->default_status = $post_status;

	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'get_default_post_type_status' ) ) :
/**
 * Get the default post type status
 * 
 * @since 3.6
 * 
 * @uses $wp_post_types
 * @uses $wp_post_statuses
 *
 * @param string $post_type
 * @return boolean 
 */
function get_default_post_type_status( $post_type ) {
	global $wp_post_types, $wp_post_statuses;

	if ( ! post_type_exists( $post_type ) )
		return false;

	if ( isset( $wp_post_types[ $post_type ]->default_status ) )
		return $wp_post_types[ $post_type ]->default_status;
	else
		return 'draft';
	
}
endif;




if ( ! function_exists( 'register_post_metas' ) ) :
/**
 * Register meta fields to a given post type
 * 
 * @since 3.6
 * 
 * @param string $post_type The post type to register
 * @param array $post_metas The array of metafields
 * @return bool|WP_Error
 */
function register_post_metas( $post_type, $post_metas ) {

	if ( !post_type_exists( $post_type ) )
		return new WP_Error( 'invalid_post_type', __( 'Invalid post type.' ) );
	

	if ( empty( $post_metas ) )
		return new WP_Error( 'no_post_metas', __( 'No meta fields found.' ) );

	if ( is_array( $post_metas ) ) {
		foreach ( $post_metas as $meta => $args )
			register_post_meta( $meta, $args, $post_type );

		return true;

	}

	return false;
		
}
endif;


if ( ! function_exists( 'register_post_meta' ) ) :
/**
 * Register one meta field to a given post type
 * 
 * @since 3.6
 * 
 * @param string $post_type The post type to register
 * @param string $meta_key Key
 * @param array $meta_args Args
 * @return bool|WP_Error
 */
function register_post_meta( $post_type, $meta_key, $meta_args ) {
	global $wp_post_types;

	if ( !post_type_exists( $post_type ) )
		return new WP_Error( 'invalid_post_type', __( 'Invalid post type.' ) );
	

	if ( isset( $wp_post_types[ $post_type ] ) ) {
		$wp_post_types[ $post_type ]->post_metas[ $meta_key ] = (object) $meta_args;
		return true;

	}
	
	return false;
		
}
endif;


if ( ! function_exists( 'set_post_type_messages' ) ) :
/**
 * Sets the post type messages
 * 
 * @since 3.6
 * 
 * @param string $post_type
 * @param array $messages
 * @return bool|WP_Error
 */
function set_post_type_messages( $post_type, $messages ) {
	global $wp_post_types;

	if ( !post_type_exists( $post_type ) )
		return new WP_Error( 'invalid_post_type', __( 'Invalid post type.' ) );

	if ( isset( $wp_post_types[ $post_type ] ) ) {
		$wp_post_types[ $post_type ]->messages = $messages;
		return true;

	}

	return false;
		
}
endif;


if ( ! function_exists( 'add_term_meta' ) ) :
/**
 * Add meta data field to a term.
 *
 * @since 3.6
 * 
 * @param int $term_id Post ID.
 * @param string $key Metadata name.
 * @param mixed $value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 * @return bool False for failure. True for success.
 */
function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'term_taxonomy', $term_id, $meta_key, $meta_value, $unique );
}
endif;


if ( ! function_exists( 'delete_term_meta' ) ) :
/**
 * Remove metadata matching criteria from a term.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @since 3.6
 * 
 * @param int $term_id term ID
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value.
 * @return bool False for failure. True for success.
 */
function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'term_taxonomy', $term_id, $meta_key, $meta_value );
}
endif;


if ( ! function_exists( 'get_term_meta' ) ) :
/**
 * Retrieve term meta field for a term.
 *
 * @since 3.6
 * 
 * @param int $term_id Term ID.
 * @param string $key The meta key to retrieve.
 * @param bool $single Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function get_term_meta( $term_id, $key, $single = false ) {
	return get_metadata( 'term_taxonomy', $term_id, $key, $single );
}
endif;


if ( ! function_exists( 'update_term_meta' ) ) :
/**
 * Update term meta field based on term ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and term ID.
 *
 * If the meta field for the term does not exist, it will be added.
 *
 * @since 3.6
 * 
 * @param int $term_id Term ID.
 * @param string $key Metadata key.
 * @param mixed $value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 * @return bool False on failure, true if success.
 */
function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'term_taxonomy', $term_id, $meta_key, $meta_value, $prev_value );
}
endif;
