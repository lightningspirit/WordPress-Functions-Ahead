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
 * @since 3.5.5
 * @param string $slug The menu page slug
 * @param string $label The new label
 * @param string $title Chege the title
 * @param string $cap Change capability
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
 * @since 3.5.5
 * @param string $parent Parent's slug
 * @param string $slug The menu page slug
 * @param string $label The new label
 * @param string $title Chege the title
 * @param string $cap Change capability
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




if ( ! function_exists( 'wp_form_table' ) ) :
/**
 * Create a form table given an array of fields
 * 
 * @since 3.6
 * 
 * @param array $fields Array of organized fields
 * @param bool $echo. Default is true.
 * @return bool|WP_Error
 */
function wp_form_table( $fields, $echo = true ) {
	global $_form_table;
	
	$_form_table = new WP_Admin_Table_Form();
	$_form_table->get_header();
	$_form_table->add_fields( $fields );
	$_form_table->organize_fields();
	$_form_table->get_footer();
	
	if ( $echo )
		$_form_table->render();
	
	return $_form_table;
	
}
endif;




/**
 * Constructor of admin tables. Helper.
 *
 * @since 3.6
 */

class WP_Admin_Table_Form extends WP_Admin_Form {

	public function get_header() {
		$this->html .= "\n<table class=\"form-table\">\n\t<tbody>";
		
	}
	
	public function get_footer( $show_hidden_fields = true ) {
		$this->html .= "\n\t</tbody>\n</table>\n";
		
		if ( $show_hidden_fields )
			$this->get_hidden_fields();
		
	}
	
	public function get_hidden_fields() {
		if ( ! isset( $this->fields ) )
			return;
		
		foreach ( (array) $this->fields as $field ) {
			if ( ! is_object( $field ) )
				$field = (object) $field;
			
			if ( 'hidden' != $field->type )
				continue;
			
			$this->html .= "\n{$field->field}";
			
		}
		
	}
	
	public function organize_fields() {
		if ( ! isset( $this->fields ) )
			return;
		
		foreach ( (array) $this->fields as $field ) {
			if ( ! is_object( $field ) )
				$field = (object) $field;
			
			if ( 'hidden' == $field->type )
				continue;
			
			$this->html .= sprintf( "\n\t\t<tr valign=\"top\">\n\t\t\t<th scope=\"row\">{$field->label}</th>\n\t\t\t<td>{$field->field}</td>\n\t\t</tr>" );
			
		}
		
	}
	
	public function render() {
		echo "\n".$this->html."\n";
		
	}
	
}
