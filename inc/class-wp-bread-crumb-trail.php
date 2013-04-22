<?php
/**
 * WP_Bread_Crumb_Trail Class
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}





// wp-includes/template.php

if ( ! class_exists( 'WP_Bread_Crumb_Trail' ) ) :
/**
 * WP_Bread_Crumb_Trail
 * 
 * $args:
 * 'show_on_home' true
 * 'delimiter' &raquo;
 * 'home' 
 * ...
 * 
 * @since 3.5.5
 */
class WP_Bread_Crumb_Trail {
	
	public $args;
	
	public $trail;
	
	public function __construct( $args = '' ) {
		$this->args = wp_parse_args( $args, array(
			'show_on_home' => true,
			'delimiter' => ' &raquo; ',
			'home' => __( 'Home' ),
			'link_current' => true,
			'wrapper' => '<div id="breadcrumbs">%s</div>',
			'class_link' => 'breadcrumb-item',
			'before_link' => '',
			'after_link' => '',
			'before_current' => '<span class="current">',
			'after_current' => '</span>',
			'echo' => true,
			)
		);
		
		
		
	}
	
}
endif;


// wp-includes/template.php

if ( ! function_exists( 'wp_breadcrumb_trail' ) ) :
/**
 * Adds a breadcrumb trail
 *
 * $args:
 * 'show_on_home' true
 * 'delimiter' &raquo;
 * 'home' 
 * ...
 *
 * @since 3.5.5
 * 
 * @param array $args
 * @return The crumbs html
 */
function wp_breadcrumb_trail( $args = array() ) {
	global $post;
	
	$trail = new WP_Bread_Crumb_Trail( $args );
	
	echo $trail->render();
	
}
	
/*	extract( wp_parse_args( $args, 
		array(
			'show_on_home' => true,
			'delimiter' => ' &raquo; ',
			'home' => __( 'Home' ),
			'link_current' => true,
			'wrapper' => '<div id="breadcrumbs">%s</div>',
			'class_link' => 'breadcrumb-item',
			'before_link' => '',
			'after_link' => '',
			'before_current' => '<span class="current">',
			'after_current' => '</span>',
			'echo' => true,
		)
	) );

	
	// Get all the breadcrumbs
	$crumbs = array();	

	// Show on home or front-page
	if ( is_front_page() ) {
		if ( $show_on_home )
			$crumbs['home'] = $before_current . '<a class="' . $class_link . '" href="' . home_url() . '">' . $home . '</a>' . $after_current;

	} elseif ( is_home() ) {
		$crumbs['home'] = $before_current . '<a class="' . $class_link . '" href="' . home_url() . '">' . $home . '</a>' . $after_current;
		
		the_post();
		
		$title = get_the_title();
		if ( $link_current == 1 )
			$title = sprintf( '<a href="%1$s">%2$s</a>', get_permalink(), $title );
		
		$crumbs[ $term->term_id ] = $before_current . $title . $after_current;
		
		rewind_posts();
		

	} else {

		// Show home link
		$crumbs['home'] = $before_link . '<a class="' . $class_link . '" href="' . home_url() . '">' . $home . '</a>' . $after_link;

		// Is current page category?
		if ( is_category() ) {
			$category = get_category( get_query_var('cat'), false );
			if ( $category->parent != 0 ) 
				$crumbs['catparents'] = get_category_parents( $category->parent, true, ' ' . $delimiter . ' ' );
			
			$title = single_cat_title( '', false );
			if ( $link_current == 1 )
				$title = sprintf( '<a href="%1$s">%2$s</a>', get_category_link( $category ), $title );
			
			$crumbs[ $category->term_id ] = $before_current . $title . $after_current;
		
		} elseif ( is_tag() ) {
			$crumbs['tag'] = $before . sprintf( __( 'Posts tagged &ldquo;%s&rdquo;' ), single_tag_title( '', false ) ) . $after;
			
		// Is current page category?
		} elseif ( is_tax() ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			
			if ( is_taxonomy_hierarchical( get_query_var( 'taxonomy' ) ) ) {
				if ( $category->parent != 0 ) 
					$crumbs['termparents'] = get_term_parents( array(
						'term_id' => $category->parent, 'taxonomy' => get_query_var( 'taxonomy' ), 
						'link' => true, 'separator' => ' ' . $delimiter . ' '
					) );
				
			}
			
			$title = single_term_title( '', false );
			if ( $link_current == 1 )
				$title = sprintf( '<a href="%1$s">%2$s</a>', get_term_link( $term ), $title );
			
			$crumbs[ $term->term_id ] = $before_current . $title . $after_current;

		// Is it search?
		} elseif ( is_search() ) {
			$crumbs['search'] = $before_current . __( 'Search results for "' . get_search_query() . '"' ) . $after_current;

		// Is it a date archive?
		} elseif ( is_day() ) {
			$crumbs['year'] = $before_link . '<a href="' . get_year_link( get_the_time('Y') ) . '">' . get_the_time('Y') . '</a>' . $after_link;
			$crumbs['month'] = $before_link . '<a href="' . get_month_link( get_the_time('Y'), get_the_time('m') ) . '">' . get_the_time('F') . '</a>' . $after_link;
			$crumbs['day'] = $before_current . get_the_time('d') . $after_current;

		} elseif ( is_month() ) {
			$crumbs['year'] = $before_link . '<a href="' . get_year_link( get_the_time('Y') ) . '">' . get_the_time('Y') . '</a>' . $after_link;
			$crumbs['month'] = $before_current . get_the_time('F') . $after_current;

		} elseif ( is_year() ) {
			$crumbs['year'] = $before_current . get_the_time('Y') . $after_current;

		} elseif ( is_post_type_archive() ) { 
			the_post();
			$post_type = get_post_type_object( get_post_type() );
			rewind_posts();
			
			$slug = $post_type->rewrite;
			$title = $post_type->labels->name;
			
			if ( $link_current == 1 )
				$title = $before_link . sprintf( '<a href="%1$s">%2$s</a>', home_url( $slug['slug'] ), $title ) . $after_link;
			
			$crumbs['posttype'] = $title; 
		
		} elseif ( is_single() || is_page() ) {
			global $post;
			
			the_post();
			$post_type = get_post_type();
			$post_type_o = get_post_type_object( $post_type );
			
			if ( $post_type_o->has_archive ) {
				$title = $post_type_o->labels->name;
				if ( $link_current == 1 )
					$title = $before_link . sprintf( '<a href="%1$s">%2$s</a>', home_url( $post_type_o->rewrite['slug'] ), $title ) . $after_link;
				
				$crumbs[ $post_type ] = $title;
				
			}
			
			if ( is_post_type_hierarchical( $post_type ) && $post->post_parent ) {
				$parent_id = $post->parent;
				
				$bread = array();
				while ( $parent_id ) {
					$post = get_post( $parent_id );
					$title = get_the_title( $parent_id );
					
					if ( $link_current == 1 )
						$title = $before_link . sprintf( '<a href="%1$s">%2$s</a>', get_permalink( $parent_id ), $title ) . $after_link;
						
					$bread[ $parent_id ] = $title;
					
				}
				$crumbs = array_merge( $crumbs, array_reverse( $bread ) );
				
			}
			
			$title = get_the_title();
			if ( $link_current == 1 )
				$title = sprintf( '<a href="%1$s">%2$s</a>', get_permalink( $post->ID ), $title );
			
			$crumbs[ $post->ID ] = $before_current . $title . $after_current;
			rewind_posts();
			
			
		} elseif ( is_author() ) {
			global $author;
			$userdata = get_userdata( $author );
			$crumbs['author'] = $before_current . sprintf( __( 'Articles posted by %s' ), $userdata->display_name ) . $after_current;

		} elseif ( is_404() ) {
			$crumbs['404'] = $before_current . __( 'Not found' ) . $after_current;
		
		}

		if ( get_query_var( 'paged' ) ) {
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()
				|| is_tax() || is_post_type_archive() || is_page() )
				 $crumbs['page'] = $before_current . __( 'Page' ) . ' ' . get_query_var( 'paged' ) . $after_current;
		
		}

	}

	$crumbs = apply_filters( 'wp_breadcrumb_trail', $crumbs );
	$crumbs_string = sprintf( $wrapper, implode( $delimiter, $crumbs ) );

	if ( $echo )
		echo $crumbs_string;
	
	return $crumbs_string;

}*/
endif;