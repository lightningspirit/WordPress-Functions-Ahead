<?php
/**
 * User Functions
 * 
 * @package WordPress
 * @subpackage Users
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}




if ( ! function_exists( 'wp_count_users' ) ) :
/**
 * Count the total number of users using WP_Query_Users parameters
 * 
 * @since 3.5.5
 * @param array $args WP_Query_Users parameters
 * 
 * @return int
 */
function wp_count_users( $args = '' ) {
	wp_parse_args(
		$args, array(
			'role' => ''
		)
	);
	
	$users = new WP_Query_Users( $args );
	return $users->get_total();
	
}
endif;


if ( ! function_exists( 'wp_count_user_objects' ) ) :
/**
 * Return the number of user objects (post types and comments )
 * 
 * @since 3.5.5
 * @uses $user
 * 
 * @param string $object Post type or comments
 * @param object|int $user WP_User object or user ID
 * @return int Count
 */
function wp_count_user_objects( $object, $user = '' ) {
	global $wpdb;
	
	if ( '' == $user ) {
		global $user;
		if ( empty( $user ) )
			$user = get_current_user_id();
		else
			$user = $user->ID;
		
	}
	
	switch ( $object ) {
		case 'comments' :
			$return = (int) $wpdb->get_var( 
				$wpdb->prepare( 
					"SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = %d", (int) $user
				)
			);
			break;
			
		default :
			if ( !post_type_exists( $object ) )
				return false;
			
			$return = (int) $wpdb->get_var( 
				$wpdb->prepare( 
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d",
					$object, (int) $user
				) 
			);
			break;
		
	}
	
	return $return;
	
}
endif;



if ( ! function_exists( 'get_user_roles_names' ) ) :
/**
 * Get all available user roles names
 * 
 * @since 3.5.5
 * @uses $wp_roles
 * 
 * @return array User roles
 */
function get_user_roles_names() {
	global $wp_roles;

	if ( ! isset( $wp_roles ) )
    	$wp_roles = new WP_Roles();

	return $wp_roles->get_names();
	
}
endif;
