<?php
/*
Plugin Name: Functions Ahead
Plugin URI: http://www.vcarvalho.com/
Version: 3.5.9
Text Domain: functions
Domain Path: /languages/
Author: lightningspirit
Author URI: http://profiles.wordpress.org/lightningspirit
Description: A set of proposed new functions and tweaks for future versions of WordPress
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
 * 		add_action( 'register_pluggable_functions', 'my_bundle_functions' );
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
function functions_ahead_init() {
	// Load the text domain to support translations
	load_plugin_textdomain( 'functions', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	
	// if new upgrade
	if ( version_compare( (int) get_option( 'wp_functions_ahead_plugin_version' ), '3.5.9', '<' ) )
		add_action( 'init', 'functions_ahead_do_upgrade' );


	define( 'WP_FUNCTIONS_AHEAD_INC', plugin_dir_path( __FILE__ ) . 'inc/' );
	
	do_action( 'register_pluggable_functions' );
	
}

add_action( 'plugins_loaded', 'functions_ahead_init' );

/** 
 * {@internal Missing Short Description}}
 * 
 * @since 3.5.1
 * 
 * @return void
 */
function register_functions_ahead() {
	
	include( WP_FUNCTIONS_AHEAD_INC . 'functions.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'objects.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'files.php' );
	
	include( WP_FUNCTIONS_AHEAD_INC . 'class-wp-query-users.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'users.php' );
	
	//include( WP_FUNCTIONS_AHEAD_INC . 'class-wp-bread-crumb-trail.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'class-wp-admin-form.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'class-register-post-type.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'template-tags.php' );
	
	if ( is_admin() ) {
		include( WP_FUNCTIONS_AHEAD_INC . 'admin.php' );
		
	}
	
	// Include the deprecated functions
	include( plugin_dir_path( __FILE__ ) . 'deprecated.php' );
	
}

add_action( 'register_pluggable_functions', 'register_functions_ahead' );


/** 
 * {@internal Missing Short Description}}
 * 
 * @since 3.5.9
 * 
 * @return void
 */
function functions_ahead_do_upgrade() {
	update_option( 'wp_functions_ahead_plugin_version', '3.5.9' );

}
