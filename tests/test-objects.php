<?php

/**
 * Tests objects
 *
 * @package functions-ahead
 */
class Functions_Ahead_Test_Objects extends WP_UnitTestCase {

	/**
	 * Run simple tests
	 */
	function test_remove_post_type() {
		remove_post_type( 'post' );
		$this->assertNull( get_post_type_object( 'post' ) );

	}
	
	function test_remove_taxonomy_category() {
		remove_taxonomy( 'category' );
		$this->assertFalse( taxonomy_exists( 'category' ) );

	}
	
	function test_remove_taxonomy_post_tag() {
		remove_taxonomy( 'post_tag' );
		$this->assertFalse( taxonomy_exists( 'post_tag' ) );

	}

}
