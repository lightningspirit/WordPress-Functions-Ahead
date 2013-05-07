<?php
/*
Plugin Name: Functions Ahead
Plugin URI: http://www.vcarvalho.com/
Version: 3.5.8
Text Domain: functions
Domain Path: /languages/
Author: lightningspirit
Author URI: http://profiles.wordpress.org/lightningspirit
Description: Package of some new WordPress functions and classes to help plugin and theme developers
License: GPLv2
*/

/*
 * To replace any of this functions please use 'register_pluggable_functions' action hook.
 * Example:
 * 
 * 		function my_bundle_functions() {
 * 
 * 			// Register an alternate version of concatenate
 * 			function concatenate( $arg, $arg2 ) {
 * 				[...]
 * 			}
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


function functions_ahead_init() {
	define( 'WP_FUNCTIONS_AHEAD_INC', plugin_dir_path( __FILE__ ) . 'inc/' );
	
	do_action( 'register_pluggable_functions' );
	
}

add_action( 'plugins_loaded', 'functions_ahead_init' );


function register_functions_ahead() {
	
	include( WP_FUNCTIONS_AHEAD_INC . 'functions.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'objects.php' );
	
	include( WP_FUNCTIONS_AHEAD_INC . 'class-wp-query-users.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'users.php' );
	
	include( WP_FUNCTIONS_AHEAD_INC . 'template-tags.php' );
	
	if ( is_admin() ) {
		include( WP_FUNCTIONS_AHEAD_INC . 'admin.php' );
		
	}
	
	// Include the deprecated functions
	include( plugin_dir_path( __FILE__ ) . 'deprecated.php' );
	
}

add_action( 'register_pluggable_functions', 'register_functions_ahead' );
