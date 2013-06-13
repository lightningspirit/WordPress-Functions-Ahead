<?php
/*
Plugin Name: Underscore WP
Plugin URI: http://www.vcarvalho.com/
Version: 3.6alpha3
Text Domain: functions
Domain Path: /languages/
Author: lightningspirit
Author URI: http://profiles.wordpress.org/lightningspirit
Description: A framework that extends WordPress functionalities.
License: GPLv2
*/

/*
 * To replace any of this functions please use 'register_pluggable_functions' action hook.
 * Example:
 * 
 * 		function my_bundle_functions() {
 * 			include( path/to/my/functions/file.php );
 * 			
 * 		}
 * 		add_action( '_wp_load', 'my_bundle_functions' );
 * 
 *
 * Use this if you need the framework fully loaded:
 * 
 * 		function my_dependent_function() {
 * 			// do the stuff here
 * 			
 * 		}
 * 		add_action( '_wp_loaded', 'my_dependent_function' );
 * 
 */



//
// Checks if it is accessed from Wordpress' index.php
//
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}

/** 
 * {@internal Missing Short Description}}
 * 
 * @since 3.5.1
 * 
 * @return void
 */
function _wp_init() {
	// Load the text domain to support translations
	load_plugin_textdomain( 'functions', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	
	// if new upgrade
	if ( version_compare( (int) get_option( 'wp_functions_ahead_plugin_version' ), '3.6alpha3', '<' ) )
		add_action( 'init', '_wp_do_upgrade' );


	define( 'WP_FUNCTIONS_AHEAD_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WP_FUNCTIONS_AHEAD_INC', plugin_dir_path( __FILE__ ) . 'inc/' );
	define( 'WP_FUNCTIONS_AHEAD_URI', plugin_dir_url( __FILE__ ) );
	
	do_action( '_wp_load' );
	
}

add_action( 'plugins_loaded', '_wp_init' );

/** 
 * {@internal Missing Short Description}}
 * 
 * @since 3.5.1
 * 
 * @return void
 */
function _wp_register_functions_files() {
	
	include( WP_FUNCTIONS_AHEAD_INC . 'functions.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'objects.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'files.php' );
	
	include( WP_FUNCTIONS_AHEAD_INC . 'class-wp-query-users.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'users.php' );
	
	//include( WP_FUNCTIONS_AHEAD_INC . 'class-wp-bread-crumb-trail.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'class-wp-form.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'class-wp-post-type.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'template-tags.php' );
	
	if ( is_admin() ) {
		include( WP_FUNCTIONS_AHEAD_INC . 'admin.php' );
		
	}
	
	// Include the deprecated functions
	include( WP_FUNCTIONS_AHEAD_DIR . 'deprecated.php' );

	// Fire bootstrap
	include( WP_FUNCTIONS_AHEAD_INC . 'bootstrap.php' );

	// Fire your own stuff here...
	do_action( '_wp_loaded' );
	
}

add_action( '_wp_load', '_wp_register_functions_files', 100 );



/** 
 * {@internal Missing Short Description}}
 * 
 * @since 3.5.9
 * 
 * @return void
 */
function _wp_do_upgrade() {
	global $wpdb;
	
	// if activated...
	_wp_setup_blog();
	
	update_option( 'wp_functions_ahead_plugin_version', '3.6alpha3' );

}


/**
 * Installs table for blog
 *
 * @since 3.6
 *
 */
function _wp_setup_blog() {
	global $wpdb;

	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	
	if ( ! empty( $wpdb->collate ) )
		$charset_collate .= " COLLATE $wpdb->collate";

	$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}term_taxonomymeta'" );
	
	if ( ! count( $tables ) )
		$wpdb->query("CREATE TABLE {$wpdb->prefix}term_taxonomymeta (
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			term_taxonomy_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY	(meta_id),
			KEY term_taxonomy_id (taxonomy_id),
			KEY meta_key (meta_key)
		) $charset_collate;");

}
