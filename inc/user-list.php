<?php


$page = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
					
	$authors = new WP_Query_Users( 
		array( 
			'role' => 'subscriber',
			'orderby' => 'login',
			'order' => 'ASC',
			'number' => 12,
			'offset' => ( $page == 1) ? 0 : $page * 12
		)
	); 

	if ( $authors->have_users() ) : while ( $authors->have_users() ) : $authors->the_user();
	?>

	 <div class="listusers">
		<?php echo get_avatar( $user->ID, $size = '80' ); ?>	
		<div class="listusersnome"><?php echo $user->data->first_name; ?></div>
		<div class="listusersother" style="text-transform: none;"><?php echo $author_info->data->cidade; ?>, <?php echo $author_info->data->pais; ?></div>
		<a href="/author/<?php echo $user->data->user_nicename; ?>"><div class="listusersvercovers">ver perfil</div></a>
		
	</div>
	
	<?php endwhile; 

	paginate_links( array(
		'total' => $authors->total_users / 12,
		'current' => $page,
		)
	);

	endif;

?>