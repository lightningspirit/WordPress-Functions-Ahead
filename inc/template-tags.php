<?php
/**
 * Template tags
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}



// wp-includes/template.php

if ( ! function_exists( 'is_paginated' ) ) :
/**
 * Evaluate if actual query is paginated
 * 
 * @since 3.5.1
 * 
 * @return bool
 */
function is_paginated() {
	global $wp_query;
	
	return ( $wp_query->max_num_pages > 1 );
	
}
endif;




// wp-includes/template.php

if ( ! function_exists( 'get_template_section' ) ) :
/**
 * Include a template section
 *
 * @since 3.5.1
 * 
 * @param string $slug Prefix of the filename
 * @param string $name The template name. Defaults to null.
 * @param string $dir Directory relative to the theme. Defaults to 'template-sections'.
 * @return void
 */
function get_template_section( $slug, $name = null, $dir = 'template-sections' ) {
	do_action( "get_template_part_{$slug}", $slug, $name );
	
	$dir = trailingslashit( $dir );

	$templates = array();
	if ( isset( $name ) )
		$templates[] = $dir."{$slug}-{$name}.php";

	$templates[] = $dir."{$slug}.php";

	locate_template( $templates, true, false );
	
}
endif;





// wp-includes/taxonomy.php

if ( ! function_exists( 'get_term_parents' ) ) :
/**
 * Retrieve term parents with separator.
 * 
 * 'term_id' The term id
 * 'taxonomy' The taxonomy
 * 'link' Link every parent. Default is false
 * 'separator' Default is /
 * 'nicename' Default is false
 * 'visited' To prevent duplications. Default is array()
 *
 * @since 3.5.1
 *
 * @param int $id Term ID.
 * @param array $args
 * @return string
 */
function get_term_parents( $args ) {
	extract( wp_parse_args( $args, array(
		'term_id' => '',
		'taxonomy' => 'category',
		'link' => false,
		'separator' => '/',
		'nicename' => false,
		'visited' => array(),
		)
	));
	
	$chain = '';
	$parent = get_term( $term_id, $taxonomy );
	if ( is_wp_error( $parent ) )
		return $parent;

	if ( $nicename )
		$name = $parent->slug;
	else
		$name = $parent->name;

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		$chain .= get_term_parents( 
			array_merge( 
				array(	'term_id' => $parent->parent ), 
				compact( 'taxonomy', 'link', 'separator', 'nicename', 'visited' ) 
			) 
		);
	}

	if ( $link )
		$chain .= '<a href="' . esc_url( get_term_link( $parent->term_id, $taxonomy ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
	else
		$chain .= $name.$separator;
	
	return $chain;
	
}
endif;




if ( ! function_exists( 'get_the_terms_names' ) ) :
/**
 * Get a list of terms names
 * 
 * @since 3.5.4
 *
 * @param int $id
 * @param string $taxonomy
 * @param string $sep
 * @param string $before
 * @param string $after
 * @return string
 */
function get_the_terms_names( $id, $taxonomy, $sep = ', ', $before = '', $after = '' ) {
	
	$terms = get_the_terms( $id, $taxonomy );
	
	if ( is_wp_error( $terms ) )
		return $terms;
	
	if ( empty( $terms ) )
		return false;
	
	foreach ( $terms as $term )
		$term_links[] = $term->name;

	$term_links = apply_filters( "the_terms_names-$taxonomy", $term_links );

	return $before . join( $sep, $term_links ) . $after;

}
endif;




if ( ! function_exists( 'the_terms_names' ) ) :
/**
 * Get a list of terms names
 * 
 * @since 3.5.4
 *
 * @param int $id
 * @param string $taxonomy
 * @param string $sep
 * @param string $before
 * @param string $after
 * @return string
 */
function the_terms_names( $id, $taxonomy, $sep = ', ', $before = '', $after = '' ) {
	echo get_the_terms_names( $id, $taxonomy, $sep, $before, $after );

}
endif;



// wp-includes/template-tags.php

if ( ! function_exists( 'the_post_classes' ) ) :
/**
 * Display post classes
 * 
 * This version gives the possibility to include all taxonomies' slugs too.
 * 
 * @since 3.5.4
 *
 * @param string|array $classes Custom classes to add
 * @param string|array $include_taxonomies The taxonomies to include (array or comma separated)
 * @return void
 */
function the_post_classes( $classes = '', $include_taxonomies = '' ) {
	echo get_the_post_classes( '', $classes, '', $include_taxonomies );
	
}
endif;



// wp-includes/template-tags.php

if ( ! function_exists( 'get_the_post_classes' ) ) :
/**
 * Return post classes
 * 
 * This version gives the possibility to include all taxonomies' slugs too.
 * 
 * @since 3.5.4
 *
 * @param int|object $post
 * @param string|array $classes Custom classes to add
 * @param string $sep Separator. Default is space.
 * @param string|array $include_taxonomies The taxonomies to include (array or comma separated)
 * @return string All classes
 */
function get_the_post_classes( $post = '', $classes = '', $sep = '', $include_taxonomies = '' ) {
	if ( '' == $post )
		$post = get_post();
	
	if ( '' == $sep )
		$sep = ' ';
	
	
	// Get the classes
	$classes = get_post_class( $classes, $post );
	
	if ( is_array( $include_taxonomies ) && $include_taxonomies ) {
		if ( is_string( $include_taxonomies ) )
			$include_taxonomies = explode( ',', $include_taxonomies );
		
		foreach ( $include_taxonomies as $tax )
			$classes[] = _get_post_taxonomy_class( trim( $tax ), $post );
	
	}
	
	return join( $sep, $classes );
	
}
endif;



// wp-includes/template-tags.php

if ( ! function_exists( '_get_post_taxonomy_class' ) ) :
/**
 * Display post classes
 * 
 * Thsi version gives the possibility to include all taxonomies' slugs too.
 * 
 * @since 3.5.4
 *
 * @param string $taxonomy Default is category.
 * @param int|object $post
 * @param string $sep Separator. Default is space.
 * @return string All classes
 */
function _get_post_taxonomy_class( $taxonomy = '', $post = '', $sep = '' ) {
	if ( '' == $post )
		$post = get_post();
	
	if ( '' == $taxonomy )
		$taxonomy = 'category';
	
	if ( '' == $sep )
		$sep = ' ';
	
	
	if ( is_object_in_taxonomy( $post->post_type, $taxonomy ) ) {
		foreach ( (array) wp_get_object_terms( $post->ID, $taxonomy ) as $term ) {
			if ( empty( $term->slug ) )
				continue;
			
			$classes[] = $taxonomy . '-' . sanitize_html_class( $term->slug, $term->term_id );
			
		}
		
		return join( $sep, (array) $classes );
	}
	
	return false;
	
}
endif;




// wp-includes/template-tags.php

if ( ! function_exists( 'get_post_thumbnail_url' ) ) :
/**
 * Return post thumbnail url
 * 
 * @since 3.5.4
 *
 * @param string $thumbnail Thumbnail version name. Defaults to thumbnail
 * @param int $post_id
 * @param string $default The default thumbnail URL if current post does not have it. Relative to theme URI.
 * @return thumbnail url
 */
function get_post_thumbnail_url( $thumbnail = 'thumbnail', $post_id = '', $use_default = '' ) {
	
	if ( '' == $post_id )
		$post_id = get_post()->ID;

	if ( has_post_thumbnail( $post_id ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $thumbnail );
		return $src[0];

	} else {
		return apply_filters( 'get_post_thumbnail_url', get_template_directory_uri() . trailingslashit( $use_default ) );

	}

}
endif;


if ( !function_exists( 'get_avatar_uri' ) ) :
/**
 * Retrieve the avatar URL for a user who provided a user ID or email address.
 *
 * @since 3.5.4
 * 
 * @param int|string|object $id_or_email A user ID,  email address, or comment object
 * @param int $size Size in pixels of the avatar image. Default is 96.
 * @param string $default URL to a default image to use if no avatar is available
 * @return string The avatar image URL
 */
function get_avatar_uri( $id_or_email, $size = '96', $default = '' ) {
	$avatar = get_avatar( $id_or_email, $size, $default, '' );
	
	$matches = array();
	preg_match( '/src="([^"]*)"/i', $avatar, $matches );
	
	if ( isset( $matches[1] ) )	
		return html_entity_decode( $matches[1] );
	
	return false;
	
}
endif;



