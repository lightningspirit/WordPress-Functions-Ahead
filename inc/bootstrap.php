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
 * Fixes DB
 *
 * @since 3.6
 *
 */
function _wp_db_fix() {
	global $wpdb;
	$wpdb->term_taxonomymeta = "{$wpdb->prefix}term_taxonomymeta";

}

add_action( 'init', '_wp_db_fix' );
add_action( 'switch_blog', '_wp_db_fix' );


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

//add_action( 'admin_init', '_wp_admin_bootstrap' );



/** 
 * {@internal Missing Short Description}}
 * 
 * @since 3.5.1
 * 
 * @return void
 */
function _wp_admin_enqueue_scripts() {
	wp_enqueue_script( 'wp-color-picker' ); 
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-widget' );
	wp_enqueue_script( 'jquery-ui-mouse' );
	wp_enqueue_script( 'jquery-ui-slider' );
	wp_enqueue_script( 'jquery-ui-progressbar' );
	wp_enqueue_script( 'jquery-ui-spinner' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-selectable' );
	wp_enqueue_script( 'jquery-ui-resizable' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_script( 'jquery-ui-datetimepicker', WP_FUNCTIONS_AHEAD_URI . '/js/jquery.ui.datetimepicker.min.js', array( 'jquery-ui-datepicker' ), '2013060901' );

	// Load globalize
	wp_enqueue_script( 'globalize', WP_FUNCTIONS_AHEAD_URI . '/js/globalize.min.js', array( 'jquery' ), '2013060901' );

	if ( file_exists( WP_FUNCTIONS_AHEAD_DIR . '/js/cultures/globalize.culture.' . str_replace( '_', '-', get_locale() ) . '.js' ) )
		$load = WP_FUNCTIONS_AHEAD_URI . '/js/cultures/globalize.culture.' . str_replace( '_', '-', get_locale() )  . '.js';
	else
		$load = WP_FUNCTIONS_AHEAD_URI . '/js/cultures/globalize.culture.' . strtolower( substr( get_locale(), 0, 2 ) ) . '.js';

	wp_enqueue_script( 'globalize-culture', $load, array( 'globalize' ), '2013060901' );


	// Load jQuery Validate plugin
	wp_enqueue_script( 'jquery-validate', WP_FUNCTIONS_AHEAD_URI . '/js/jquery.validate.min.js', array( 'jquery' ), '2013060901' );
	wp_enqueue_script( 'jquery-validate-methods', WP_FUNCTIONS_AHEAD_URI . '/js/additional-methods.min.js', array( 'jquery-validate' ), '2013060901' );

	if ( 'en' != substr( geT_locale(), 0, 2 ) ) {
		if ( file_exists( WP_FUNCTIONS_AHEAD_DIR . '/js/localization/messages_' . get_locale() . '.js' ) )
			$load = WP_FUNCTIONS_AHEAD_URI . '/js/localization/messages_' . get_locale() . '.js';
		else
			$load = WP_FUNCTIONS_AHEAD_URI . '/js/localization/messages_' . strtolower( substr( get_locale(), 0, 2 ) ) . '.js';

		wp_enqueue_script( 'jquery-validate-l10n', $load, array( 'jquery-validate' ), '2013060901' );

	}


	// Load underscore WP admin JS file
	wp_enqueue_script( '_wp_admin', WP_FUNCTIONS_AHEAD_URI . '/js/_wp_admin.min.js', array( 
		'wp-color-picker', 'jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-slider', 'jquery-ui-progressbar', 'jquery-ui-spinner', 
		'jquery-ui-sortable', 'jquery-ui-selectable', 'jquery-ui-resizable', 'jquery-ui-datepicker', 'jquery-ui-autocomplete', 'globalize', 'globalize-culture' 
		), '2013061001' );

	if ( file_exists( WP_PLUGIN_DIR . '/mp6/mp6.php' ) )
		wp_enqueue_style( '_wp_mp6', WP_FUNCTIONS_AHEAD_URI . '/css/mp6/jquery-ui-mp6.min.css', null, '2013060701' );

}

add_action( 'admin_enqueue_scripts', '_wp_admin_enqueue_scripts' );



/**
 * Get locale for Globalize JS
 *
 * @since 3.6
 *
 */
function get_globalize_locale() {

	$file = WP_FUNCTIONS_AHEAD_DIR . '/js/cultures/globalize.culture.' . str_replace( '_', '-', get_locale() ) . '.js';

	if ( file_exists( $file ) )
		return str_replace( '_', '-', get_locale() );

	return strtolower( substr( get_locale(), 0, 2 ) );

}



/**
 * Change core stuff in admin
 *
 * @since 3.6
 *
 */
/*function _wp_set_post_type_messages( $messages ) {
	global $wp_post_types;


	foreach ( (array) $wp_post_types as $post_type => $object ) {
		var_dump( $post_type );

		// Get singular name
		$singular_name = $object->labels->singular_name;

		// Fill with default
		$messages[ $post_type ] = array_map( '_replace_messages_singular_name', $messages['post'], compact( $singular_name ) );

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
