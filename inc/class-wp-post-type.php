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
final class WP_Post_Type {

	/**
	 * Post type of the object
	 *
	 * @since 3.6
	 *
	 * @var $post_type
	 */
	public $post_type;

	/**
	 * Post type labels
	 *
	 * @since 3.6
	 *
	 * @var $labels
	 */
	public $labels;

	/**
	 * Construct
	 *
	 * @since 3.6
	 *
	 * @var $post_type
	 */
	public function __construct( $post_type ) {
		if ( !post_type_exists( $post_type ) )
			return false;

		$this->post_type = $post_type;
		add_filter( 'post_updated_messages', array( $this, 'set_post_type_messages' ) );
		add_action( "manage_edit-{$post_type}_columns", array( $this, 'manage_post_columns' ) );
		add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'manage_sortable_columns' ) );
		add_filter( 'request', array( $this, 'intersect_request' ) );
		add_action( 'edit_post', array( $this, 'save_edit_post_metas' ) );
		add_action( 'edit_form_after_title', array( $this, 'render_nonce_field' ) );

		if ( isset( get_post_type_object( $post_type )->columns_cb ) && is_callable( get_post_type_object( $post_type )->columns_cb ) )
			add_action( "manage_{$post_type}_posts_custom_column", get_post_type_object( $post_type )->columns_cb );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

	}

	/**
	 * @since 3.6
	 */
	public function set_post_type_messages( $messages ) {
		global $post, $post_ID;

		//var_dump( $messages );
		
		/* Declare objects */
		$post_type = $this->post_type;
		$post_type_object = get_post_type_object( $post_type );
		$this->labels = $post_type_object->labels;
		

		/* Fill with default */
		$messages[ $post_type ] = array_map( array( $this, '_replace_messages_singular_name' ), $messages['post'] );

		/**
		 * If any post type messages are set we use them here
		 */
		if ( isset( $post_type_object->messages ) ) {
			$msgs = $post_type_object->messages;

			if ( isset( $msgs['updated_view'] ) )
				$messages[ $post_type ][1] = sprintf( $msgs['updated_view'], esc_url( get_permalink( $post_ID ) ) );

			if ( isset( $msgs['updated'] ) ) {
				$messages[ $post_type ][2] = $msgs['updated'];
				$messages[ $post_type ][4] = $msgs['updated'];
			}

			if ( isset( $msgs['deleted'] ) )
				$messages[ $post_type ][3] = $msgs['deleted'];

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
					date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) );

			if ( isset( $msgs['draft_updated'] ) )
				$messages[ $post_type ][8] = sprintf( $msgs['draft_updated'], esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );
			

		}

		return $messages;

	}

	/**
	 * @since 3.6
	 */
	private function _replace_messages_singular_name( $messages ) {
		return str_replace( __( 'Post' ), $this->labels->singular_name, $messages );

	}

	/**
	 * @since 3.6
	 */
	public function manage_post_columns( $columns ) {
		$post_type_object = get_post_type_object( $this->post_type );

		if ( !isset( $post_type_object->edit_columns ) )
			return $columns;


		foreach ( (array) $post_type_object->edit_columns as $column_id => $column_label )
			if ( null == $column_label || false == $column_label && isset( $columns[ $column_id ] ) )
				$post_type_object->edit_columns[ $column_id ] = $columns[ $column_id ];

		return $post_type_object->edit_columns;

	}

	/**
	 * @since 3.6
	 */
	public function manage_sortable_columns( $columns ) {
		$post_type_object = get_post_type_object( $this->post_type );

		if ( !isset( $post_type_object->sortable_columns ) )
			return $columns;

		
		foreach ( (array) $post_type_object->sortable_columns as $column => $vars ) {
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

		if ( $this->post_type != $post_type )
			return $vars;

		if ( ! isset( $vars['orderby'] ) )
			return $vars;

		$post_type_object = get_post_type_object( $this->post_type );
	
		$sortable = $post_type_object->sortable_columns;
		$order_by = $vars['orderby'];
		
		if ( isset( $sortable[ $order_by ] ) && is_array( $sortable[ $order_by ] ) ) {
			$vars = array_merge( $vars, $sortable[ $order_by ] );
			
		}
	 
	    return $vars;

	}

	/**
	 * @since 3.6
	 */
	public function save_edit_post_metas( $post_id ) {

		if ( get_post_type( $post_id ) != $this->post_type )
			return;

		$post_type_object = get_post_type_object( get_post_type( $post_id ) );

		// Check for nonce field
		if ( !empty( $_POST ) && !check_admin_referer( "_{$this->post_type}_nonce_field", '_wp_metanonce' ) )
			return;

		if ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) )
			return;

		if ( !isset( $post_type_object->post_meta ) )
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

		$object = get_post_type_object( $post_type );

		if ( isset( $object->metaboxes ) )
			foreach ( $object->metaboxes as $metabox ) {
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

		// Get all the meta fields
		$post_metas = get_post_type_object( $post_type )->post_meta;

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

		if ( $post_type == $this->post_type )
			wp_nonce_field( "_{$post_type}_nonce_field", '_wp_metanonce' );
		
	}

}
