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
function wp_rename_admin_menu_item( $old_label, $new_label ) {
	global $menu;

	if ( ! is_array( $menu ) )
		return;
	
	array_walk( $menu, '_rename_admin_menu_item_walk', compact( 'old_label', 'new_label' ) );

}

function _rename_admin_menu_item_walk( &$item, $key, $labels ) {
	if ( $labels['old_label'] == $item[0] )
		$item[0] = $labels['new_label'];

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

