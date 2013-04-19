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

// If you are using a paged query
$page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$users = new WP_Query_Users( 
	array( 
		'role' => 'subscriber',
		'orderby' => 'login',
		'order' => 'ASC',
		'number' => 10,
		'offset' => ( $page == 1 ) ? 0 : $page * 10
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
			
		<a class="name">
			<?php the_display_name(); ?>
		</a>
		
		<a class="description">
			<?php the_biography(); ?>
		</a>
		
	</div>
	
	<?php endwhile; ?>
	
</div>

<?php wp_reset_userdata(); endif; ?>
