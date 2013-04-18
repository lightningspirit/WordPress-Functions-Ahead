<?php
/**
 * User Query Class
 * 
 * @package WordPress
 * @subpackage WP_Query_Users
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}


global $user;


if ( ! class_exists( 'WP_Query_Users' ) ) :
/**
 * WP_Query_Users
 * 
 * @since 3.5.5
 * 
 */
class WP_Query_Users extends WP_User_Query {
	
	/**
	 * @var object $user Current user position (index)
	 */
	var $current_user = -1;
	
	/**
	 * @var object $user Current user object
	 */
	var $user = false;
	
	/**
	 * @var bool $is_author_page If the loop is the main loop of an author page
	 */
	var $is_author_page = false;
	
	/**
	 * @var bool $in_the_loop
	 */
	var $in_the_loop = false;
	
	/**
	 *
	 * @since 3.5.5
	 *
	 * @param string|array $args The query variables
	 * @return WP_User_Query
	 */
	function __construct( $query = null ) {
		$this->query_vars = wp_parse_args( $query, array(
			'blog_id' => $GLOBALS['blog_id'],
			'meta_key' => '',
			'meta_value' => '',
			'meta_compare' => '',
			'include' => array(),
			'exclude' => array(),
			'search' => '',
			'search_columns' => array(),
			'orderby' => 'registered',
			'order' => 'DESC',
			'offset' => '',
			'number' => '',
			'count_total' => true,
			'fields' => 'all',
			'role' => '',
		) );
		
		if ( array_key_exists( 'ID', $this->query_vars ) ) {
			$this->query_vars['search'] = $this->query_vars['ID'];
			$this->query_vars['search_column'] = array( 'ID' );
			
			unset( $this->query_vars['ID'] );
			
		}
		
		$this->query_vars['orderby'] = strtolower( $this->query_vars['orderby'] );
		
		$this->prepare_query();
		$this->query();
		
	}
	
	/**
	 * Retrieve the users based on query variables
	 * 
	 * @since 3.5.5
	 * @access public
	 * 
	 * @return array List of WP_User objects
	 */
	function get_users() {
		return $this->get_results();
		
	}
	
	/**
	 * Get total users count
	 * 
	 * @since 3.5.5
	 * @access public
	 * 
	 * @return int Number of users
	 */
	function get_total() {
		return count( $this->results );
		
	}
	
	/**
	 * Whether there are more users available in the loop.
	 * 
	 * @since 3.5.5
	 * @access public
	 * @uses do_action_ref_array() Calls 'loop_end' if loop is ended
	 * 
	 * @return bool True if users are available, false if end of loop
	 */
	function have_users() {
		if ( 0 == $this->get_total() ) {
			return false;
			
		} elseif ( $this->current_user + 1 < $this->get_total() ) {
			return true;
			
		} elseif ( $this->current_user + 1 == $this->total_users && $this->total_users > 0 ) {
			do_action_ref_array( 'loop_end', array( &$this ) );
			// Do some cleaning up after the loop
			$this->rewind_users();
			
			return false;

		}
		
		$this->in_the_loop = false;
		return false;
		
	}
	
	/**
	 * Sets up the current user.
	 * 
	 * Retrieves the next user, sets up the user, sets the 'in the loop'
	 * property to true.
	 * 
	 * @since 3.5.5
	 * @access public
	 * @uses do_action_ref_array() Calls 'loop_start' if loop has just started
	 * 
	 * @return WP_User object
	 */
	function the_user() {
		global $user;
		$this->in_the_loop = true;
		
		if ( $this->current_user == -1 ) // Loop has just started
			do_action_ref_array( 'loop_start', array( &$this ) );
		
		$user = $this->next_user();
		
	}
	
	/**
	 * Set up the next user and iterate current user index.
	 * 
	 * @since 3.5.5
	 * @access public
	 * 
	 * @return WP_User Next user
	 */
	function next_user() {
		$this->current_user++;
		$this->user = $this->results[ $this->current_user ];
		
		return $this->user;
		
	}
	
	/**
	 * Set up the previous user and iterate current user index.
	 * 
	 * @since 3.5.5
	 * @access public
	 * 
	 * @return WP_User Previous user
	 */
	function previous_user() {
		$this->current_user--;
		$this->user = $this->results[ $this->current_user ];
		
		return $this->user;
		
	}
	
	/**
	 * Rewind the users and reset user index.
	 * 
	 * @since 3.5.5
	 * @access public
	 */
	function rewind_users() {
		$this->current_user = -1;
		if ( $this->total_users > 0 )
			$this->user = $this->results[0];
		
	}
	
}
endif;


if ( ! function_exists( 'have_users' ) ) :
/**
 * Whether there are more users available in the loop.
 * 
 * @since 3.5.5
 * @access public
 * @uses do_action_ref_array() Calls 'loop_end' if loop is ended
 * 
 * @return bool True if users are available, false if end of loop
 */
function have_users() {
	global $wp_query_users;
	return $wp_query_users->have_users();
	
}
endif;


if ( ! function_exists( 'the_user' ) ) :
/**
 * Sets up the current user.
 * 
 * Retrieves the next user, sets up the user, sets the 'in the loop'
 * property to true.
 * 
 * @since 3.5.5
 * 
 * @return WP_User object
 */
function the_user() {
	global $wp_query_users;
	return $wp_query_users->the_user();
	
}
endif;


if ( ! function_exists( 'wp_reset_userdata' ) ) :
/**
 * After looping through a separate query, this function restores
 * the $user global to the current user in the main query
 * 
 * @since 3.5.5
 * @uses $wp_query_users
 */
function wp_reset_userdata() {
	global $user, $wp_query_users;
	$user = $wp_query_users->user;
	
}
endif;


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
