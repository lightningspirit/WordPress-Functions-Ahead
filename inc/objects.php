<?php
/**
 * Post object functions
 * 
 * @package WordPress
 * @subpackage Post Type Functions
 * 
 * @since 3.5.5
 * 
 */

// Checks if it is accessed from Wordpress' index.php
if ( ! function_exists( 'add_action' ) ) {
	die( 'I\'m just a plugin. I must not do anything when called directly!' );

}




// wp-includes/post.php

if ( ! function_exists( 'unregister_taxonomy_from_object_type' ) ) :
/**
 * Remove an already registered taxonomy from an object type.
 *
 * @since 3.5
 *
 * @uses $wp_taxonomies Modifies taxonomy object
 *
 * @param string $taxonomy Name of taxonomy object
 * @param string $object_type Name of the object type
 * @return bool True if successful, false if not
 */
function unregister_taxonomy_from_object_type($taxonomy, $object_type) {

	global $wp_taxonomies;

	if ( ! isset( $wp_taxonomies[ $taxonomy ]) )
		return false;

	if ( ! get_post_type_object( $object_type ) )
		return false;

	foreach ( array_keys( $wp_taxonomies['category']->object_type ) as $array_key ) {
		if ( $wp_taxonomies['category']->object_type[ $array_key ] == $object_type ) {
			unset( $wp_taxonomies['category']->object_type[ $array_key ] );
			return true;
		}
	}
	return false;

}
endif;



// wp-includes/post.php

if ( ! function_exists( 'remove_post_type' ) ) :
/**
 * Removes post types
 * 
 * Hooks: remove_post_type[ $post_type ]
 * 
 * @since 3.5
 *
 * @uses $wp_post_types 
 *
 * @param string $post_type Post type key, must not exceed 20 characters
 * @return boolean 
 */
function remove_post_type( $post_type ) {
	global $wp_post_types, $wp_rewrite;

	if ( !is_array($wp_post_types) )
		$wp_post_types = array();

	if ( ! post_type_exists( $post_type ) )
		return false;

	foreach ( (array) $wp_post_types[$post_type]->taxonomies as $taxonomy ) {
		unregister_taxonomy_from_object_type( $taxonomy, $post_type );
	}
	
	if ( isset( $wp_post_types[ $post_type ] ) )
		unset( $wp_post_types[$post_type] );
	
	do_action( 'remove_post_type', $post_type );
	
	return true;
	
}
endif;


// wp-includes/taxonomy.php

if ( ! function_exists( 'remove_taxonomy' ) ) :
/**
 * Removes taxonomies
 *
 * Notice that removing default category taxonomy from WordPress
 * can lead into unpredictable experiences since WP_Query
 * uses category as a builtin object and dont check for its availability.
 * 
 * Hooks: remove_taxonomy( $taxonomy )
 * 
 * @since 3.5
 *
 * @uses $wp_taxonomies 
 *
 * @param string $taxonomy 
 * @return boolean 
 */
function remove_taxonomy( $taxonomy ) {
	global $wp_taxonomies;

	if ( !is_array($wp_taxonomies) )
		$wp_taxonomies = array();

	if ( ! taxonomy_exists( $taxonomy ) )
		return false;
	
	
	if ( isset( $wp_taxonomies[$taxonomy]  ) )
		unset( $wp_taxonomies[$taxonomy] );
	
	do_action( 'remove_taxonomy', $taxonomy );
	
	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'remove_post_status' ) ) :
/**
 * Removes post status
 * 
 * Hooks: remove_post_status( $post_status )
 * 
 * @since 3.6
 *
 * @uses $wp_post_statuses 
 *
 * @param string $post_status 
 * @return boolean 
 */
function remove_post_status( $post_status ) {
	global $wp_post_statuses;

	if ( !is_array($wp_post_statuses) )
		$wp_post_statuses = array();

	if ( ! post_status_exists( $post_status ) )
		return false;
	
	
	if ( isset( $wp_post_statuses[ $post_status ]  ) )
		unset( $wp_post_statuses[ $post_status ] );
	
	do_action( 'remove_post_status', $post_status );
	
	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'post_status_exists' ) ) :
/**
 * Checks that the post status exists.
 * 
 * @since 3.6
 * 
 * @uses $wp_post_statuses 
 *
 * @param string $post_status 
 * @return boolean 
 */
function post_status_exists( $post_status ) {
	global $wp_post_statuses;

	return isset( $wp_post_statuses[ $post_status ] );
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'register_status_for_post_type' ) ) :
/**
 * Associates a post status to a post type
 * 
 * @since 3.6
 * 
 * @uses $wp_post_types
 * @uses $wp_post_statuses 
 *
 * @param string $post_status 
 * @param string $post_type 
 * @return boolean 
 */
function register_status_for_post_type( $post_status, $post_type ) {
	global $wp_post_types, $wp_post_statuses;

	if ( ! post_status_exists( $post_status ) )
		return false;

	if ( ! post_type_exists( $post_type ) )
		return false;

	if ( ! isset( $wp_post_types[ $post_type ]->statuses ) )
		$wp_post_types[ $post_type ]->statuses = array();

	if ( ! in_array( $post_status, $wp_post_types[ $post_type ]->statuses ) )
		$wp_post_types[ $post_type ]->statuses[] = $post_status;

	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'register_post_type_statuses' ) ) :
/**
 * Associates a post status to a post type
 * 
 * @since 3.6
 * 
 * @uses $wp_post_types
 * @uses $wp_post_statuses 
 *
 * @param string $post_type 
 * @param array|string $post_status es
 * @return boolean 
 */
function register_post_type_statuses( $post_type, $post_statuses, $default = '' ) {
	global $wp_post_types, $wp_post_statuses;

	if ( ! post_type_exists( $post_type ) )
		return false;

	if ( ! is_array( $post_statuses ) )
		$post_statuses[] = $post_statuses;


	foreach ( $post_statuses as $post_status ) {
		if ( post_status_exists( $post_status ) )
			register_status_for_post_type( $post_status, $post_type );

	}

	if ( ! empty( $default ) && post_status_exists( $default ) )
		set_default_post_type_status( $post_type, $default );


	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'set_default_post_type_status' ) ) :
/**
 * Sets the default post type status
 * 
 * @since 3.6
 * 
 * @uses $wp_post_types
 * @uses $wp_post_statuses
 *
 * @param string $post_type 
 * @param string $post_status 
 * @return boolean 
 */
function set_default_post_type_status( $post_type, $post_status ) {
	global $wp_post_types, $wp_post_statuses;

	if ( ! post_type_exists( $post_type ) )
		return false;

	if ( ! post_status_exists( $post_status ) )
		return false;

	$wp_post_types[ $post_type ]->default_status = $post_status;

	return true;
	
}
endif;


// wp-includes/post.php

if ( ! function_exists( 'get_default_post_type_status' ) ) :
/**
 * Get the default post type status
 * 
 * @since 3.6
 * 
 * @uses $wp_post_types
 * @uses $wp_post_statuses
 *
 * @param string $post_type
 * @return boolean 
 */
function set_default_post_type_status( $post_type ) {
	global $wp_post_types, $wp_post_statuses;

	if ( ! post_type_exists( $post_type ) )
		return false;

	if ( isset( $wp_post_types[ $post_type ]->default_status ) )
		return $wp_post_types[ $post_type ]->default_status;
	else
		return 'draft';
	
}
endif;




if ( ! function_exists( 'register_post_metas' ) ) :
/**
 * Register meta fields to a given post type
 * 
 * @since 3.6
 * 
 * @param string $post_type The post type to register
 * @param array $post_metas The array of metafields
 * @return bool|WP_Error
 */
function register_post_metas( $post_type, $post_metas ) {

	if ( !post_type_exists( $post_type ) )
		return new WP_Error( 'invalid_post_type', __( 'Invalid post type.' ) );
	

	if ( empty( $post_metas ) )
		return new WP_Error( 'no_post_metas', __( 'No meta fields found.' ) );

	if ( is_array( $post_metas ) ) {
		foreach ( $post_metas as $meta => $args )
			register_post_meta( $meta, $args, $post_type );

		return true;

	}

	return false;
		
}
endif;


if ( ! function_exists( 'register_post_meta' ) ) :
/**
 * Register one meta field to a given post type
 * 
 * @since 3.6
 * 
 * @param string $post_type The post type to register
 * @param string $meta_key Key
 * @param array $meta_args Args
 * @return bool|WP_Error
 */
function register_post_meta( $post_type, $meta_key, $meta_args ) {
	global $wp_post_types;

	if ( !post_type_exists( $post_type ) )
		return new WP_Error( 'invalid_post_type', __( 'Invalid post type.' ) );
	

	if ( isset( $wp_post_types[ $post_type ] ) ) {
		$wp_post_types[ $post_type ]->post_metas[ $meta_key ] = (object) $meta_args;
		return true;

	}
	
	return false;
		
}
endif;


if ( ! function_exists( 'set_post_type_messages' ) ) :
/**
 * Sets the post type messages
 * 
 * @since 3.6
 * 
 * @param string $post_type
 * @param array $messages
 * @return bool|WP_Error
 */
function set_post_type_messages( $post_type, $messages ) {
	global $wp_post_types;

	if ( !post_type_exists( $post_type ) )
		return new WP_Error( 'invalid_post_type', __( 'Invalid post type.' ) );

	if ( isset( $wp_post_types[ $post_type ] ) ) {
		$wp_post_types[ $post_type ]->messages = $messages;
		return true;

	}

	return false;
		
}
endif;





// wp-admin/includes/meta-boxes.php

if ( ! function_exists( '_post_submit_meta_box' ) ) :
/**
 * Display post submit form fields.
 *
 * @since 3.6
 *
 * @param object $post
 */
function _post_submit_meta_box( $post ) {
	global $wp_post_types, $action;

	$post_type = $post->post_type;
	$post_status = $post->post_status;
	$post_type_object = get_post_type_object( $post_type );

	$can_publish = current_user_can( $post_type_object->cap->publish_posts );
	$can_save = current_user_can( $post_type_object->cap->edit_posts );
	

	if ( isset( $wp_post_types[ $post->post_type ]->statuses ) )
		$post_statuses = array_intersect( get_post_stati( null, 'objects' ), $wp_post_types[ $post->post_type ]->statuses );

	else
		$post_statuses = array_intersect_key( get_post_stati( null, 'objects' ), get_post_statuses() );


?>
<div class="submitbox" id="submitpost">

<div id="minor-publishing">

<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
<div style="display:none;">
<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
</div>

<div id="minor-publishing-actions">
<div id="save-action">

<?php if ( 'publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status ) { ?>
<input <?php if ( 'private' == $post->post_status ) { ?>style="display:none"<?php } ?> type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save Draft'); ?>" class="button" />
<?php } elseif ( 'pending' == $post->post_status && $can_publish ) { ?>
<input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save as Pending'); ?>" class="button" />
<?php } ?>
<span class="spinner"></span>
</div>
<?php if ( $post_type_object->public ) : ?>
<div id="preview-action">
<?php
if ( 'publish' == $post->post_status ) {
	$preview_link = esc_url( get_permalink( $post->ID ) );
	$preview_button = __( 'Preview Changes' );
} else {
	$preview_link = set_url_scheme( get_permalink( $post->ID ) );
	$preview_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ) ) );
	$preview_button = __( 'Preview' );
}
?>
<a class="preview button" href="<?php echo $preview_link; ?>" target="wp-preview" id="post-preview"><?php echo $preview_button; ?></a>
<input type="hidden" name="wp-preview" id="wp-preview" value="" />
</div>
<?php endif; // public post type ?>
<div class="clear"></div>
</div><!-- #minor-publishing-actions -->


<!-- Post status display -->
<div id="misc-publishing-actions">
	<div class="misc-pub-section">
		<label for="post_status"><?php _e( 'Status:' ) ?></label>
		<span id="post-status-display">
			<?php echo isset( $post_statuses[ $post_status ] ) ? 
				$post_statuses[ $post_status ]->label : 
				$post_statuses[ get_default_post_type_status( $post_type ) ]->label;
			?>
		</span>

<?php if ( 'publish' == $post->post_status || 'private' == $post->post_status || $can_publish ) { ?>
<a href="#post_status" <?php if ( 'private' == $post->post_status ) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js"><?php _e('Edit') ?></a>

<div id="post-status-select" class="hide-if-js">
<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
<select name='post_status' id='post_status'>
<?php if ( 'publish' == $post->post_status ) : ?>
<option<?php selected( $post->post_status, 'publish' ); ?> value='publish'><?php _e('Published') ?></option>
<?php elseif ( 'private' == $post->post_status ) : ?>
<option<?php selected( $post->post_status, 'private' ); ?> value='publish'><?php _e('Privately Published') ?></option>
<?php elseif ( 'future' == $post->post_status ) : ?>
<option<?php selected( $post->post_status, 'future' ); ?> value='future'><?php _e('Scheduled') ?></option>
<?php endif; ?>
<option<?php selected( $post->post_status, 'pending' ); ?> value='pending'><?php _e('Pending Review') ?></option>
<?php if ( 'auto-draft' == $post->post_status ) : ?>
<option<?php selected( $post->post_status, 'auto-draft' ); ?> value='draft'><?php _e('Draft') ?></option>
<?php else : ?>
<option<?php selected( $post->post_status, 'draft' ); ?> value='draft'><?php _e('Draft') ?></option>
<?php endif; ?>
</select>
 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
 <a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e('Cancel'); ?></a>
</div>

<?php } ?>
</div><!-- .misc-pub-section -->

<div class="misc-pub-section" id="visibility">
<?php _e('Visibility:'); ?> <span id="post-visibility-display"><?php

if ( 'private' == $post->post_status ) {
	$post->post_password = '';
	$visibility = 'private';
	$visibility_trans = __('Private');
} elseif ( !empty( $post->post_password ) ) {
	$visibility = 'password';
	$visibility_trans = __('Password protected');
} elseif ( $post_type == 'post' && is_sticky( $post->ID ) ) {
	$visibility = 'public';
	$visibility_trans = __('Public, Sticky');
} else {
	$visibility = 'public';
	$visibility_trans = __('Public');
}

echo esc_html( $visibility_trans ); ?></span>
<?php if ( $can_publish ) { ?>
<a href="#visibility" class="edit-visibility hide-if-no-js"><?php _e('Edit'); ?></a>

<div id="post-visibility-select" class="hide-if-js">
<input type="hidden" name="hidden_post_password" id="hidden-post-password" value="<?php echo esc_attr($post->post_password); ?>" />
<?php if ($post_type == 'post'): ?>
<input type="checkbox" style="display:none" name="hidden_post_sticky" id="hidden-post-sticky" value="sticky" <?php checked(is_sticky($post->ID)); ?> />
<?php endif; ?>
<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>" />
<input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php checked( $visibility, 'public' ); ?> /> <label for="visibility-radio-public" class="selectit"><?php _e('Public'); ?></label><br />
<?php if ( $post_type == 'post' && current_user_can( 'edit_others_posts' ) ) : ?>
<span id="sticky-span"><input id="sticky" name="sticky" type="checkbox" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?> /> <label for="sticky" class="selectit"><?php _e( 'Stick this post to the front page' ); ?></label><br /></span>
<?php endif; ?>
<input type="radio" name="visibility" id="visibility-radio-password" value="password" <?php checked( $visibility, 'password' ); ?> /> <label for="visibility-radio-password" class="selectit"><?php _e('Password protected'); ?></label><br />
<span id="password-span"><label for="post_password"><?php _e('Password:'); ?></label> <input type="text" name="post_password" id="post_password" value="<?php echo esc_attr($post->post_password); ?>" /><br /></span>
<input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php checked( $visibility, 'private' ); ?> /> <label for="visibility-radio-private" class="selectit"><?php _e('Private'); ?></label><br />

<p>
 <a href="#visibility" class="save-post-visibility hide-if-no-js button"><?php _e('OK'); ?></a>
 <a href="#visibility" class="cancel-post-visibility hide-if-no-js"><?php _e('Cancel'); ?></a>
</p>
</div>
<?php } ?>

</div><!-- .misc-pub-section -->

<?php
// translators: Publish box date format, see http://php.net/date
$datef = __( 'M j, Y @ G:i' );
if ( 0 != $post->ID ) {
	if ( 'future' == $post->post_status ) { // scheduled for publishing at a future date
		$stamp = __('Scheduled for: <b>%1$s</b>');
	} else if ( 'publish' == $post->post_status || 'private' == $post->post_status ) { // already published
		$stamp = __('Published on: <b>%1$s</b>');
	} else if ( '0000-00-00 00:00:00' == $post->post_date_gmt ) { // draft, 1 or more saves, no date specified
		$stamp = __('Publish <b>immediately</b>');
	} else if ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // draft, 1 or more saves, future date specified
		$stamp = __('Schedule for: <b>%1$s</b>');
	} else { // draft, 1 or more saves, date specified
		$stamp = __('Publish on: <b>%1$s</b>');
	}
	$date = date_i18n( $datef, strtotime( $post->post_date ) );
} else { // draft (no saves, and thus no date specified)
	$stamp = __('Publish <b>immediately</b>');
	$date = date_i18n( $datef, strtotime( current_time('mysql') ) );
}

if ( $can_publish ) : // Contributors don't get to choose the date of publish ?>
<div class="misc-pub-section curtime">
	<span id="timestamp">
	<?php printf($stamp, $date); ?></span>
	<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js"><?php _e('Edit') ?></a>
	<div id="timestampdiv" class="hide-if-js"><?php touch_time(($action == 'edit'), 1); ?></div>
</div><?php // /misc-pub-section ?>
<?php endif; ?>

<?php do_action('post_submitbox_misc_actions'); ?>
</div>
<div class="clear"></div>
</div>

<div id="major-publishing-actions">
<?php do_action('post_submitbox_start'); ?>
<div id="delete-action">
<?php
if ( current_user_can( "delete_post", $post->ID ) ) {
	if ( !EMPTY_TRASH_DAYS )
		$delete_text = __('Delete Permanently');
	else
		$delete_text = __('Move to Trash');
	?>
<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
} ?>
</div>

<div id="publishing-action">
<span class="spinner"></span>
<?php
if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
	if ( $can_publish ) :
		if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>" />
		<?php submit_button( __( 'Schedule' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	else : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
		<?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php	endif;
	elseif ( $can_save ) : ?>

<?php
	else : ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Save' ) ?>" />
		<?php submit_button( __( 'Save' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
<?php
	endif;
} else { ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
		<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update') ?>" />
<?php
} ?>
</div>
<div class="clear"></div>
</div>
</div>

<?php
}
endif;
