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
 * @uses $wp_taxonomies Modifies taxonomy object
 *
 * @param string $taxonomy Name of taxonomy object
 * @param string $object_type Name of the object type
 * @return bool True if successful, false if not
 */
function unregister_taxonomy_from_object_type($taxonomy, $object_type) {

	global $wp_taxonomies;

	if ( !isset($wp_taxonomies[$taxonomy]) )
		return false;

	if ( ! get_post_type_object($object_type) )
		return false;

	foreach (array_keys($wp_taxonomies['category']->object_type) as $array_key) {
		if ($wp_taxonomies['category']->object_type[$array_key] == $object_type) {
			unset ($wp_taxonomies['category']->object_type[$array_key]);
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
 * @uses $wp_post_types 
 *
 * @param string $post_type Post type key, must not exceed 20 characters
 * @return boolean 
 */
function remove_post_type( $post_type ) {
	global $wp_post_types, $wp_rewrite, $wp;

	if ( !is_array($wp_post_types) )
		$wp_post_types = array();

	if ( ! post_type_exists( $post_type ) )
		return;

	foreach ( (array) $wp_post_types[$post_type]->taxonomies as $taxonomy ) {
		unregister_taxonomy_from_object_type( $taxonomy, $post_type );
	}
	
	if ( isset( $wp_post_types[ $post_type ] ) )
		unset( $wp_post_types[$post_type] );
	
	do_action( 'remove_post_type', $post_type );
	
	return;
	
}
endif;


// wp-includes/taxonomy.php

if ( ! function_exists( 'remove_taxonomy' ) ) :
/**
 * Removes taxonomies
 * 
 * Hooks: remove_taxonomy[ $taxonomy ]
 * 
 * @since 3.5
 * @uses $wp_taxonomies 
 *
 * @param string $taxonomy 
 * @return boolean 
 */
function remove_taxonomy( $taxonomy ) {
	global $wp_taxonomies, $wp;

	if ( !is_array($wp_taxonomies) )
		$wp_taxonomies = array();

	if ( ! taxonomy_exists( $taxonomy ) )
		return;
	
	
	if ( isset( $wp_taxonomies[$taxonomy]  ) )
		unset( $wp_taxonomies[$taxonomy] );
	
	do_action( 'remove_taxonomy', $taxonomy );
	
	return;
	
}
endif;
