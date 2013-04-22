<?php
/**
 * This is an example on how to use the WP_Query_Users class.
 * The implementation is just an example but carries all the functions
 * and how to use them.
 * 
 * This is pretty much the same as using WP_Query for posts.
 * The idea is just to loop throught specific/all users using
 * query parameters.
 * 
 * Some handy display and returning functions are available for
 * the most user data.
 * 
 */


$users = new WP_Query_Users( 
	array( 
		'role' => 'subscriber', // Specifying ONE role to retrieve. Currently WP_User_Query doesn't support array of roles. 
		'orderby' => 'login', // Ordering by 'ID', 'login', 'display_name', 'nicename', 'email', 'url', 'registered'. Default is 'login'
		'order' => 'ASC', // ASC or DESC. default is 'DESC'.
		'number' => 10, // Number of users to return for each page. Default is get_option( 'posts_per_page' ) set in options-reading.php
		'paged' => max( 1, get_query_var( 'paged' ) ) // Get the global page query var ( [link]/page/<number> ). Default is no pagination.
	)
); 

?>

<?php if ( $users->have_users() ) : ?>
	
<div id="allusers">
	
	<?php while ( $users->have_users() ) : $users->the_user(); ?>
	
	<div id="<?php echo $user->ID; ?>" class="user">
		
		<a href="<?php the_user_link(); ?>" class="avatar">
			<?php echo get_avatar( $user->ID ); ?>
		</a>
			
		<a href="<?php the_user_link(); ?>" class="name">
			<?php the_display_name(); ?>
		</a>
		
		<a class="description">
			<?php the_biography(); ?>
		</a>
		
		<?php echo get_custom_user_link( __( 'Link to this user profile' ) ); ?>
		
	</div>
	
	<?php endwhile; ?>
	
	<?php echo get_pagination( null, $users ); ?>
	
</div>

<?php endif; ?>
