<?php
/**
 * This is an example on how to use the WP_Post_type class.
 * The implementation is just an example but carries all the functions
 * and how to use them.
 * 
 */


/**
 * Use this if you want to create a new
 * rewrite endpoint type.
 * 
 * You can use EP_NONE for post types that
 * are intented to not be public (ie, viewed in frontside).
 *
 */
define( 'EP_BOOK', 8192 );


/**
 * Define the Book Post Type class
 *
 * @since 3.6
 */
class Book {

	/**
	 * 
	 * @since 3.6
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'init' ) );
		add_action( 'load-edit.php', array( __CLASS__, 'admin_head_edit' ) );


	}

	/**
	 * 
	 * @since 3.6
	 */
	public static function init() {

		$args = array(

			/* Declare Labels, messages and Help */
			
			'label' => __( 'Books', '_wp' ),
			'labels' => array(
				'name' => __( 'Books', '_wp' ),
				'singular_name' => __( 'Book', '_wp' ),
				'menu_name' => __( 'Books', '_wp' ),
				'all_items' => __( 'All Books', '_wp' ),
				'add_new' => _x( 'Add New', 'book', '_wp' ),
				'add_new_item' => __( 'Add New Book', '_wp' ),
				'edit_item' => __( 'Edit Book', '_wp' ),
				'new_item' => __( 'New Book', '_wp' ),
				'view_item' => __( 'View Book', '_wp' ),
				'search_items' => __( 'Search Books', '_wp' ),
				'not_found' => __( 'No Books found', '_wp' ),
				'not_found_in_trash' => __( 'No Books found in Trash', '_wp' ),
				'parent_item_colon' => __( 'Parent Book', '_wp' ),
			),
			'messages' => array(
				'updated_view' => __( 'Book updated. <a href="%s">View Book</a>', '_wp' ),
				'updated_field' => __( 'Custom Field updated.', '_wp' ),
				'deleted_field' => __( 'Custom Field deleted', '_wp' ),
				'saved' => __( 'Book saved.', '_wp' ),
				'revision_restored' => __( 'Book restored to revision from %s', '_wp' ),
				'published' => __( 'Book published. <a href="%s">View Book</a>', '_wp' ),
				'submitted' => __( 'Book submitted. <a target="_blank" href="%s">Preview Book</a>', '_wp' ),
				'scheduled' => __( 'Book scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Book</a>', '_wp' ),
				'draft_updated' => __( 'Book draft updated. <a target="_blank" href="%s">Preview Book</a>', '_wp' ),
			),
			'help' => array(
				array(
					'id' => 'help_1',
					'title' => __( 'A Little Help', '_wp' ),
					'content' => 
						__( '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed est eros, vulputate eget sodales nec.</p>', '_wp' )
				),
				array(
					'id' => 'help_2',
					'title' => __( 'A Little Help 2', '_wp' ),
					'content' => 
						__( '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed est eros, vulputate eget sodales nec.</p>', '_wp' )
				)
			),

			/* Description */
			'description' => __( 'Book post type', '_wp' ),

			/* Meta Fields */

			'post_meta' => array(

				// Display
				'option0' => array(
					'type' => 'display',
					'label' => __( 'Display field', '_wp' ),
					'value' => __( 'This value is displayed here like a paragraph.', '_wp' ),
					'description' => __( 'It accepts a little note here...', '_wp' ),
					'save' => false,
				),

				// Yes or No
				'option1' => array(
					'type' => 'yesorno',
					'label' => __( 'Yes or No', '_wp' ),
					'description' => __( 'Select either Yes or No as values.', '_wp' ),
				),

				// Custom/dynamic HTML output
				'option2' => array(
					'type' => 'custom',
					'label' => __( 'Custom HTML field', '_wp' ),
					'value' => function() {
						global $post;
						return call_user_func( 'get_the_category', $post->ID );
					},
					'description' => __( 'It accepts a little note here...', '_wp' ),
				),

				// Text
				'option3' => array(
					'type' => 'text',
					'label' => __( 'Text Field', '_wp' ),
					'placeholder' => __( 'Write something here', '_wp' ),
					'maxlength' => 40,
					'style' => 'width: 300px;',
					'class' => 'my-class',
					'required' => true,
					'data' => array(
						'id' => 'someid',
						'label' => __( 'Can translate this', '_wp' ),
					),
					'description' => __( 'A native description here', '_wp' ),
				),

				// Number
				'option4' => array(
					'type' => 'number',
					'label' => __( 'Number Field', '_wp' ),
					'value' => function( $field ) {
						return date( 'Ymd' );
					}
					
				),

				// Email
				'option5' => array(
					'type' => 'email',
					'label' => __( 'Email field', '_wp' ),					
				),

				// URL
				'option6' => array(
					'type' => 'url',
					'label' => __( 'URL field', '_wp' ),
					'placeholder' => 'http://',				
				),

				// Color
				'option7' => array(
					'type' => 'color',
					'label' => __( 'Color field', '_wp' ),
				),

				// Name
				'option8' => array(
					'type' => 'name',
					'label' => __( 'Name field', '_wp' ),
				),

				// Code
				'option9' => array(
					'type' => 'code',
					'label' => __( 'Code field', '_wp' ),
				),

				// Date
				'option10' => array(
					'type' => 'date',
					'label' => __( 'Date input', '_wp' ),		
				),

				// Datetime
				'option11' => array(
					'type' => 'datetime',
					'label' => __( 'Date Time input', '_wp' ),		
				),

				// Time
				'option12' => array(
					'type' => 'time',
					'label' => __( 'Time input', '_wp' ),		
				),

				// Currency
				'option13' => array(
					'type' => 'currency',
					'label' => __( 'Currency Field', '_wp' ),
					'value' => function( $field ) {
						return rand( 0, 30 );
					}			
				),
				
				// Sortable
				'option14' => array(
					'type' => 'sortable',
					'label' => __( 'Sortable', '_wp' ),
					'options' => array(
						'sort1' => __( 'Sortable 1' ),
						'sort2' => __( 'Sortable 2' ),
						'sort3' => __( 'Sortable 3' ),
					)
				)

			),

			/* Register metaboxes for post metas */
			'metaboxes' => array(
				array(
					'id' => 'general',
					'title' => __( 'General', '_wp' ),
					'context' => 'normal', // advanced, normal, side
					'priority' => 'high', // high, low, default, core
					'metas' => array( 
						'option0', 'option1', 'option2', 'option3', 'option4', 'option5', 'option6', 'option7', 
						'option0', 'option0', 'option0', 'option0', 'option0', 'option0', 'option0', 
					)
				),
				array(
					'id' => 'general2',
					'title' => __( 'Another General', '_wp' ),
					'context' => 'normal', // advanced, normal, side
					'priority' => 'high', // high, low, default, core
					'metas' => array( 
						'option8', 'option9', 'option10', 'option11', 'option12', 'option13', 'option14', 
					)
				)

			),

			/* Custom Edit Columns */
			'edit_columns' => array(
				'cb' => null,
				'thumbnail' => '',
				'title' => __( 'Book Title', '_wp' ),
				'color' => __( 'Color Field', '_wp' ),
				'author' => __( 'Author' ),
				'date' => __( 'Published Date' )

			),

			'columns_cb' => array( __CLASS__, 'custom_columns' ),

			/* Sort Columns */
			'sortable_columns' => array(
				'title', 
				'date',
				'color' => array(
					'meta_key' => 'option7',
					'orderby' => 'meta_value',
				),
				'author' => array(
					'orderby' => 'author',
				)
			),

			/* Parameters */

			'public' => true,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 20,
			'menu_icon' => '',
			'capability_type' => array( 'book', 'books' ),
			'map_meta_cap' => true,
			'hierarchical' => true,
			'can_export' => true,


			/* Support */

			'supports' => array(
				'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 
				'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats'
			),
			'taxonomies' => array( 
				'category', 'post_tag' 
			),


			/* Rewrite */

			'has_archive' => 'books',
			'rewrite' => array(
				'slug' => 'books',
				'with_front' => false,
				'feeds' => 'books',
				'pages' => true,
				'ep_mask' => EP_BOOK,
			),

			/* Query */

			'query_var' => 'book',
			

			/* Register Other Metaboxes */

			'register_meta_box_cb' => array( __CLASS__, 'meta_boxes' ),
			
			/* Advanced */

			'_builtin' => false,
			'_edit_link' => 'post.php?post=%d',

		);

		register_post_type( 'book', $args );

	}


	/**
	 * 
	 * @since 3.6
	 */
	public static function custom_columns( $column ) {
		global $post;
		
		switch ( $column ) {
			case 'thumbnail' :
				the_post_thumbnail( array( '60', '60' ) );
				break;
				
			case 'color' :
				printf( '<div style="width:30px; height: 30px; background-color: %s;"></div>', 
					get_post_meta( $post->ID, 'option7', true ) 

				);
				break;
				
			case 'author' :
				echo get_the_author();
				break;
			
		}
		
	}

	/**
	 * 
	 * @since 3.6
	 */
	public static function meta_boxes() {
		add_meta_box( 'one_metabox', __( 'One Meta Box', '_wp' ), array( __CLASS__, 'general_meta_box' ), 'book', 'normal', 'high' );

	}

	/**
	 * 
	 * @since 3.6
	 */
	public static function general_meta_box( $post ) {
		?>

		<p><?php _e( 'This is the content of the metabox.', '_wp' ); ?><p>

		<?php

	}

	/**
	 * 
	 * @since 3.6
	 */
	public static function admin_head_edit() {
		global $post_type;
		
		if ( ! in_array( 'book', array( $post_type, isset( $_GET['post_type'] ) ? $_GET['post_type'] : '' ) ) )
			return;
		
		?>
		<style type="text/css">
			.manage-column.column-thumbnail { width: 90px; }
			.column-thumbnail.thumbnail img { border:1px solid #ccc;}
			.manage-column.column-likecount { width: 10%; }
			.manage-column.column-info { width: 30%; }
		</style>
		<?php
	}


}

new Book;
