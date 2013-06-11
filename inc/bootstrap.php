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
function _wp_initial_post_stuff() {
	global $wp_post_types;

	/* Support and init WP_Post_Type instance */
	$post_types = (array) apply_filters( '_wp_post_types', get_post_types( array( 'show_ui' => true ), 'objects' ) );

	foreach ( $post_types as $post_type => $object ) {
		$wp_post_types[ $post_type ]->wp_post_type = new WP_Post_Type( $post_type, $object );

	}

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

add_action( 'wp_loaded', '_wp_initial_post_stuff', 0 );



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
