<?php
/**
 * Bootstrap (change core stuff)
 * 
 * @package WordPress
 * 
 * @since 3.6
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}



/**
 * Creates initial post status for builtin post types
 *
 * @since 3.6
 *
 */
function create_initial_post_status() {

	/* New post status Archived */
	register_post_status( 'archive', array(
		'label' => __( 'Archived', '_wp' ),
		'label_count' => _n_noop( 
			__( 'Archived <span class="count">(%s)</span>', '_wp' ),
			__( 'Archived <span class="count">(%s)</span>', '_wp' )
			),
		'_builtin' => true,
		'public' => false,
		'exclude_from_search' => true,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		)
	);

	/* Register default post statuses for post */
	register_post_type_statuses( 'post', 
		array_merge( get_post_statuses(), array( 'archived') )
	);

	/* Register default post statuses for page */
	register_post_type_statuses( 'page', 
		array_merge( get_page_statuses(), array( 'archived') )
	);

}

add_action( 'init', 'create_initial_post_status', 0 );



/**
 * Change core stuff in admin
 *
 * @since 3.6
 *
 */
function _wp_admin_bootstrap() {

	add_filter( 'post_updated_messages', '_wp_set_post_type_messages' );

	foreach ( get_post_types() as $post_type ) {
		add_action( "manage_edit-{$post_type}_columns", '_wp_manage_post_columns' );
		add_action( "manage_edit-{$post_type}_sortable_columns", '_wp_manage_sortable_columns' );

	}

	add_filter( 'request', '_wp_intersect_request' );
	add_action( 'edit_post', '_wp_save_edit_post_metas' );
	add_action( 'edit_form_after_title', '_wp_render_nonce_field' );

}

add_action( 'admin_init', '_wp_admin_bootstrap' );



/**
 * Change core stuff in admin
 *
 * @since 3.6
 *
 */
function _wp_set_post_type_messages( $messages ) {
	global $wp_post_types;

	// Get singular name
	$singular_name = get_post_type_object( $post_type )->labels->singular_name;

	// Fill with default
	$messages[ $this->post_type ] = array_map( '_replace_messages_singular_name', $messages['post'], compact( $singular_name ) );

	// Hacks and more hacks... wouldn't be nice if PHP 5.3 was a requirement?
	function _replace_messages_singular_name( $message, $args ) {
		explode( $args );
		return str_replace( 'Post', $singular_name, $message );

	}

	if ( isset( $wp_post_types[ $post_type ]->messages ) ) {
		$msgs = $wp_post_types[ $post_type ]->messages;

		if ( isset( $msgs['updated_view'] ) )
			$messages[ $this->post_type ][1] = sprintf( $messages['updated_view'], esc_url( get_permalink( $post_ID ) ) );

		if ( isset( $msgs['updated'] ) ) {
			$messages[ $this->post_type ][2] = $msgs['updated'];
			$messages[ $this->post_type ][4] = $msgs['updated'];
		}

		if ( isset( $msgs['deleted'] ) )
			$messages[ $this->post_type ][3] = $msgs['deleted'];

		if ( isset( $msgs['saved'] ) )
			$messages[ $this->post_type ][7] = $msgs['saved'];

		if ( isset( $msgs['revision_restored'] ) )
			$messages[ $this->post_type ][5] = isset( $_GET['revision'] ) ? sprintf( $messages['revision_restored'], wp_post_revision_title( (int) $_GET['revision'], false ) ) : false;

		if ( isset( $msgs['published'] ) )
			$messages[ $this->post_type ][6] = sprintf( $messages['published'], esc_url( get_permalink($post_ID) ) );

		if ( isset( $msgs['submitted'] ) )
			$messages[ $this->post_type ][8] = sprintf( $messages['submitted'], esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );

		if ( isset( $msgs['scheduled'] ) )
			$messages[ $this->post_type ][9] =  printf( __('Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) );

		if ( isset( $msgs['draft_updated'] ) )
			$messages[ $this->post_type ][8] = sprintf( $messages['draft_updated'], esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );
		

	}
	
	return $messages; 

}



/**
 * Change core stuff in admin
 *
 * @since 3.6
 *
 */
function _wp_manage_post_columns( $columns ) {
	$new_columns = $this->args->manage_edit_columns;
	
	foreach ( (array) $new_columns as $column_id => $column_label ) {
		if ( null == $column_label || false == $column_label && isset( $columns[ $column_id ] ) )
			$new_columns[ $column_id ] = $columns[ $column_id ];
			
	}
	
	return $new_columns;

}



/**
 * Change core stuff in admin
 *
 * @since 3.6
 *
 */
function _wp_manage_sortable_columns() {
	$sortable = $this->args->manage_sortable_columns;
		
	foreach ( (array) $sortable as $column => $vars ) {
		if ( is_int( $column ) )
			$columns[ $vars ] = (string) $vars;
		else
			$columns[ $column ] = $column;
		
	}
	
	return $columns;
	
}

/**
 * Change core stuff in admin
 *
 * @since 3.6
 *
 */
function _wp_intersect_request( $vars ) {

	if ( ! isset( $vars['orderby'] ) )
		return $vars;
	
	$sortable = $this->args->manage_sortable_columns;
	$order_by = $vars['orderby'];
	
	if ( isset( $sortable[ $order_by ] ) && is_array( $sortable[ $order_by ] ) ) {
		$vars = array_merge( $vars, $sortable[ $order_by ] );
		
	}
 
    return $vars;

}

/**
 * Change core stuff in admin
 *
 * @since 3.6
 *
 */
function _wp_save_edit_post_metas( $object_id ) {

	$post_type_object = get_post_type_object( get_post_type( $object_id ) );
	if ( ! current_user_can( $post_type_object->cap->edit_post, $object_id ) )
		return;
	
	// Save all metas now
	foreach ( $this->meta_fields as $meta => $field ) {
		if ( isset( $field->handle_save ) && false == $field->handle_save )
			continue;
		
		if ( isset( $_REQUEST[ $meta ] ) ) {
			if ( '' != $field->validate )
				$value = call_user_func( $field->validate, $_REQUEST[ $meta ], $meta, $field, $object_id );
			else
				$value = $_REQUEST[ $meta ];
				
		} else {
			$value = '';
			
		}
			
		$metas[ "_{$this->post_type}_{$meta}" ] = $value;
		
	}
	
	foreach ( $metas as $field => $value ) {
		if ( is_array( $value ) ) {
			$this->save_multi_meta( $object_id, $field, $value );
			
		} else {
			$this->save_meta( $object_id, $field, $value );
			
		}
		
	}

	$meta = get_post_meta( $object_id, $field, true );
		
	if ( $meta && '' == $value )
		delete_post_meta( $object_id, $field );
		
	elseif ( $meta && $value )
		update_post_meta( $object_id, $field, $value, $meta );
		
	elseif ( $value )
		add_post_meta( $object_id, $field, $value );

}


/**
 * Renders a nonce field to be used for metadata saving.
 *
 * @since 3.6
 *
 */
function _wp_render_nonce_field() {
	global $post_type;

	wp_nonce_field( "_{$post_type}_nonce_field", '_wp_metanonce' );
	
}
