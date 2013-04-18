<?php
/**
 * Admin functions and tweaks
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}




// wp-admin/includes/menu.php

if ( ! function_exists( 'wp_rename_admin_menu_item' ) ) :
/**
 * Change admin menu label
 *
 * Useful for renaming items in the admin menu.
 *
 * @since 3.6
 * @param $old_label
 * @param $new_label
 * @return void
 */
function wp_rename_admin_menu_item( $slug, $label = '', $title = '', $cap = '' ) {
	global $menu;

	if ( ! is_array( $menu ) )
		return;
	
	array_walk( $menu, '_rename_admin_menu_item_walk', compact( 'slug', 'label', 'title', 'cap' ) );

}
endif;



// wp-admin/includes/menu.php

if ( ! function_exists( 'wp_rename_admin_submenu_item' ) ) :
/**
 * Change admin menu label
 *
 * Useful for renaming items in the admin menu.
 *
 * @since 3.6
 * @param $old_label
 * @param $new_label
 * @return void
 */
function wp_rename_admin_submenu_item( $parent, $slug, $label = '', $title = '', $cap = '' ) {
	global $submenu;

	if ( ! is_array( $submenu ) )
		return false;
	
	if ( ! array_key_exists( $parent, $submenu ) )
		return false;
	
	
	array_walk( $submenu[ $parent ], '_rename_admin_menu_item_walk', compact( 'slug', 'label', 'title', 'cap' ) );

}
endif;


if ( ! function_exists( '_rename_admin_menu_item_walk' ) ) :
/**
 * @since 3.5.1
 */
function _rename_admin_menu_item_walk( &$item, $key, $page ) {
	extract( $page );
	
	if ( $slug == $item[2] ) {
		
		if ( !empty( $label ) )
			$item[0] = $label;
		
		if ( !empty( $title ) && isset( $item[3] ) )
			$item[3] = $title;
		
		if ( !empty( $cap ) && isset( $item[1] ) )
			$item[1] = $cap;
		
	}
	
}
endif;



// wp-admin/link-template.php

if ( ! function_exists( 'get_action_post_link' ) ) :
/**
 * Returns a custom action post link action
 * 
 * @since 3.5.1
 * 
 * @param int $post_id
 * @param string $action a custom action
 * @return string The link
 */
function get_action_post_link( $post_id, $action = 'trash' ) {
	$post = get_post( $post_id);
	$post_type_object = get_post_type_object( $post->post_type );

	if ( $action == 'trash' )
		return get_delete_post_link( $post->ID );
	

	$admin_link = admin_url( sprintf( $post_type_object->_edit_link.'&amp;action='.$action, $post->ID ) );

	switch ( strtolower( $action ) ) {
		case 'edit' :
			return $admin_link;

		default :
			return wp_nonce_url( $admin_link, $action.'-'.$post->post_type.'_'.$post->ID );


	}

}
endif;

