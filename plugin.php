<?php
/*
Plugin Name: Functions Ahead
Plugin URI: http://www.vcarvalho.com/
Version: 3.5.5
Text Domain: functions
Domain Path: /languages/
Author: lightningspirit
Author URI: http://profiles.wordpress.org/lightningspirit
Description: A set of proposed new functions and tweaks for future versions of WordPress
License: GPLv2
*/


//
// Checks if it is accessed from Wordpress' index.php
//
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}

//
// Only fire for WordPress > 3.5
//
if ( version_compare( get_bloginfo( 'version' ), '3.5', '>=' ) ) {
	
	define( 'WP_FUNCTIONS_AHEAD_INC', plugin_dir_path( __FILE__ ) . 'inc/' );
	
	include( WP_FUNCTIONS_AHEAD_INC . 'functions.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'post-types.php' );
	include( WP_FUNCTIONS_AHEAD_INC . 'template-tags.php' );
	
	if ( is_admin() ) {
		include( WP_FUNCTIONS_AHEAD_INC . 'admin-menu.php' );
		
	}
	
	// Include the deprecated functions
	include( plugin_dir_path( __FILE__ ) . 'deprecated.php' );
	
}
