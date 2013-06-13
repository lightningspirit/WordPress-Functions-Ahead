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
 * Helper to construct administrator fields
 *
 * @since 3.6
 */
final class WP_Post_Type_UI {

	/**
	 * Post type of the object
	 *
	 * @since 3.6
	 *
	 * @var $post_type
	 */
	public $post_types;

	/**
	 * Construct
	 *
	 * @since 3.6
	 *
	 * @var $post_type
	 */
	public function __construct() {

		/* Get all UI enabled post types */
		$post_types = get_post_types( array( 'show_ui' => true ) );
		$post_types = (array) apply_filters( '_wp_post_types', $post_types );

		/* Copy all post types */
		$this->post_types = $post_types;

		/* Organize columns */
		foreach ( $post_types as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );

			add_action( "manage_edit-{$post_type}_columns", array( $this, 'manage_post_columns' ) );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'manage_sortable_columns' ) );

			if ( isset( $post_type_obj->columns_cb ) && is_callable( $post_type_obj->columns_cb ) )
				add_action( "manage_{$post_type}_posts_custom_column", $post_type_obj->columns_cb );

		}

		/* Customize messages */
		add_filter( 'post_updated_messages', array( $this, 'set_post_types_messages' ) );

		/* Customize Help Tabs */
		add_action( 'load-post.php', array( $this, 'help_tabs' ) );
		add_action( 'load-post-new.php', array( $this, 'help_tabs' ) );

		/* Post request and save */
		add_filter( 'request', array( $this, 'intersect_request' ) );
		add_action( 'edit_post', array( $this, 'save_edit_post_metas' ), 10, 2 );
		add_action( 'edit_form_after_title', array( $this, 'render_nonce_field' ) );

		/* Display post type metaboxes */
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		

	}

	/**
	 * @since 3.6
	 */
	public function set_post_types_messages( $messages ) {
		global $post, $post_ID;

		/* Get the post types */
		foreach ( (array) $this->post_types as $post_type ) {

			// Get post type object
			$post_type_obj = get_post_type_object( $post_type );

			// Replace Post by singular_name and plural names
			if ( 'post' != $post_type ) {

				// If is not set
				if ( ! isset( $messages[ $post_type ] ) ) {
					foreach ( $messages['post'] as $key => $message ) {
						$message = str_replace( __( 'Post' ), $post_type_obj->labels->singular_name, $message );
						$message = str_replace( __( 'Posts' ), $post_type_obj->labels->name, $message );
						$messages[ $post_type ][ $key ] = $message;

					}

				}

			}				

			/* If customized messages are set */
			if ( isset( $post_type_obj->messages ) )
				$messages[ $post_type ] = self::set_post_type_messages( $post_type, $messages[ $post_type ] );


		}

		return $messages;

	}

	/**
	 * @since 3.6
	 */
	public function set_post_type_messages( $post_type, $messages ) {
		global $post, $post_ID;

		// Get post type object
		$post_type_obj = get_post_type_object( $post_type );
		$msgs = $post_type_obj->messages;

		// Update with View link
		if ( isset( $msgs['updated_view'] ) )
			$messages[ $post_type ][1] = sprintf( $msgs['updated_view'], esc_url( get_permalink( $post_ID ) ) );

		// Updated custom field
		if ( isset( $msgs['updated'] ) ) {
			$messages[ $post_type ][2] = $msgs['updated'];
			$messages[ $post_type ][4] = $msgs['updated'];
		}

		// Deleted custom field
		if ( isset( $msgs['deleted'] ) )
			$messages[ $post_type ][3] = $msgs['deleted'];

		// Saved
		if ( isset( $msgs['saved'] ) )
			$messages[ $post_type ][7] = $msgs['saved'];

		if ( isset( $msgs['revision_restored'] ) )
			$messages[ $post_type ][5] = isset( $_GET['revision'] ) ? sprintf( $msgs['revision_restored'], wp_post_revision_title( (int) $_GET['revision'], false ) ) : false;

		if ( isset( $msgs['published'] ) )
			$messages[ $post_type ][6] = sprintf( $msgs['published'], esc_url( get_permalink($post_ID) ) );

		if ( isset( $msgs['submitted'] ) )
			$messages[ $post_type ][8] = sprintf( $msgs['submitted'], esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );

		if ( isset( $msgs['scheduled'] ) )
			$messages[ $post_type ][9] =  sprintf( $msgs['scheduled'],
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) );

		if ( isset( $msgs['draft_updated'] ) )
			$messages[ $post_type ][8] = sprintf( $msgs['draft_updated'], esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );
		
		return $messages;

	}

	/**
	 * @since 3.6
	 */
	public function manage_post_columns( $columns ) {
		global $post_type;

		// Get post type object
		$post_type_obj = get_post_type_object( $post_type );

		if ( ! isset( $post_type_obj->edit_columns ) )
			return $columns;


		foreach ( (array) $post_type_obj->edit_columns as $column_id => $column_label )

			// If key is a int, it means it is a core column
			if ( is_int( $column_id ) && $column_label ) {
				if ( isset( $columns[ $column_label ] ) )
					$post_type_obj->edit_columns = array_key_swap( $post_type_obj->edit_columns, $column_id, $column_label );

			}

		return $post_type_obj->edit_columns;

	}

	/**
	 * @since 3.6
	 */
	public function manage_sortable_columns( $columns ) {
		global $post_type;

		// Get post type object
		$post_type_obj = get_post_type_object( $post_type );

		if ( ! isset( $post_type_obj->sortable_columns ) )
			return $columns;

		
		foreach ( (array) $post_type_obj->sortable_columns as $column => $vars ) {
			if ( is_int( $column ) )
				$columns[ $vars ] = (string) $vars;
			else
				$columns[ $column ] = $column;
		
		}

		return $columns;

	}

	/**
	 * @since 3.6
	 */
	public function intersect_request( $vars ) {
		global $post_type;

		if ( ! isset( $vars['orderby'] ) )
			return $vars;

		// Get post type object
		$post_type_obj = get_post_type_object( $post_type );

		if ( ! isset( $post_type_obj->sortable_columns ) )
			return $vars;
	
		$sortable = $post_type_obj->sortable_columns;
		$order_by = $vars['orderby'];
		
		if ( isset( $sortable[ $order_by ] ) && is_array( $sortable[ $order_by ] ) ) {
			$vars = array_merge( $vars, $sortable[ $order_by ] );
			
		}
	 
	    return $vars;

	}

	/**
	 * @since 3.6
	 */
	public function save_edit_post_metas( $post_id, $post ) {

		// Get post type object
		$post_type_object = get_post_type_object( $post->post_type );

		// Check for nonce field
		if ( ! empty( $_POST ) && !check_admin_referer( "_{$post->post_type}_nonce_field", '_wp_metanonce' ) )
			return;

		if ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) )
			return;

		if ( ! isset( $post_type_object->post_meta ) )
			return;
		
		// For each meta field
		foreach ( (array) $post_type_object->post_meta as $meta => $field ) {
			if ( isset( $field['save'] ) && false == $field['save'] )
				continue;

			if ( 'display' == $field['type'] )
				continue;
			
			if ( isset( $_REQUEST[ $meta ] ) && isset( $field['validate'] ) && is_callable( $field['validate'] ) )
				$value = call_user_func( $field['validate'], $_REQUEST[ $meta ], $meta, $field, $post_id );

			elseif ( isset( $_REQUEST[ $meta ] ) )
				$value = call_user_func( 'esc_html', $_REQUEST[ $meta ] );
					
			else
				$value = '';
				
			
			/// Save field
			$old = get_post_meta( $post_id, $meta, true );
		
			if ( $old && '' == $value )
				delete_post_meta( $post_id, $meta );
				
			elseif ( $old && $value )
				update_post_meta( $post_id, $meta, $value, $old );
				
			elseif ( $value )
				add_post_meta( $post_id, $meta, $value );
			
		}
		
	}

	/**
	 * Add post meta boxes
	 *
	 * @since 3.6
	 *
	 */
	public function add_meta_boxes() {
		global $post_type;

		// Get post type object
		$post_type_object = get_post_type_object( $post_type );

		if ( isset( $post_type_object->metaboxes ) )
			foreach ( $post_type_object->metaboxes as $metabox ) {
				$metas = isset( $metabox['metas'] ) ? $metabox['metas'] : '';
				$position = isset( $metabox['context'] ) ? $metabox['context'] : 'normal';
				$priority = isset( $metabox['priority'] ) ? $metabox['priority'] : 'default';
				add_meta_box( $metabox['id'], $metabox['title'], array( $this, 'render_post_metas' ), $post_type, $position, $priority, $metas );

			}
		

	}

	/**
	 * Renders post metas
	 *
	 * @since 3.6
	 *
	 */
	public function render_post_metas( $post, $metas = '' ) {
		global $post, $post_type;

		// Get post type object
		$post_type_object = get_post_type_object( $post->post_type );

		// Get all the meta fields
		if ( ! isset( $post_type_object->post_meta ) )
			return;

		$post_metas = $post_type_object->post_meta;

		if ( ! empty( $metas['args'] ) ) {
			// Intersect and filter by the post meta keys asked
			$post_metas = array_intersect_key( 
				$post_metas, array_combine( $metas['args'], $metas['args'] ) 
			);

		}

		// If value parameter is not set pre-fill it with value from DB
		foreach ( $post_metas as $meta => $args ) {

			// The value is already set
			if ( isset( $args['value'] ) )
				continue;

			$post_metas[ $meta ]['value'] = get_post_meta( $post->ID, $meta, true );

		}

		// Render the table
		wp_form_table( $post_metas );
		
	}

	/**
	 * Renders a nonce field to be used for metadata saving.
	 *
	 * @since 3.6
	 *
	 */
	public function render_nonce_field() {
		global $post_type;

		wp_nonce_field( "_{$post_type}_nonce_field", '_wp_metanonce' );
		
	}


	/**
	 * @since 3.6
	 */
	public function help_tabs() {
		global $post_type;

		// Get post type object
		$post_type_obj = get_post_type_object( get_current_screen()->post_type );

		if ( isset( $post_type_obj->help ) ) {
			foreach ( $post_type_obj->help as $helptab )
				get_current_screen()->add_help_tab( $helptab );
			
		}
	
	}

}
