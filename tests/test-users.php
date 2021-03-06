<?php
/**
 * Tests Users class inc/users.php
 *
 * @group user
 */
class Functions_Ahead_Test_Users extends WP_UnitTestCase {
		
	protected $users;
	protected $query;
	protected $total;
	protected $admins;
	protected $authors;
	protected $subscribers;
	
	// Create users
	function setUp() {
		parent::setUp();
		
		// Create Admins
		$this->admins = $this->factory->user->create_many( rand( 1, 5 ), array(
			'role' => 'administrator',
		) );
		
		// Create authors
		$this->authors = $this->factory->user->create_many( rand( 2, 5 ), array(
			'role' => 'author',
		) );
		
		// Create authors
		$this->subscribers = $this->factory->user->create_many( rand( 5, 30 ), array(
			'role' => 'subscriber',
		) );
		
		$this->total = count($this->admins)+count($this->authors)+count($this->subscribers)+1;
		
		$number = rand( 5, 10 );
		$offset = $number * rand( 1, 10 );
		
		$this->query = new WP_Query_Users( array( 
				'role' => 'subscriber',
				'number' => $number,
				'offset' => $offset,
				'orderby' => 'ID',
				'order' => 'ASC',
			) 
		);
		
		$this->users = array_slice( $this->subscribers, $offset, $number );
		sort( $this->users, SORT_NUMERIC );
		
	}
	
	// test 
	function test_wp_query_user_globals_with_all() {
		
		$users = new WP_Query_Users( array( 'role' => 'administrator', 'number' => -1 ) );
		$this->assertEquals( count( $this->admins ) + 1, (int) $users->get_total() );
		// TODO: +1 is just a bug fix on tests
		
		$users = new WP_Query_Users( array( 'role' => 'author', 'number' => -1 ) );
		$this->assertEquals( count( $this->authors ), (int) $users->get_total() );
		
		$users = new WP_Query_Users( array( 'role' => 'subscriber', 'number' => -1 ) );
		$this->assertEquals( count( $this->subscribers ), (int) $users->get_total() );
		
		$users = new WP_Query_Users( array( 'number' => -1 ) );
		$this->assertEquals( $this->total, (int) $users->get_total() );
		
	}
	
	// test 
	function test_wp_query_user_globals_4_users() {
		
		$count = min( count( $this->authors ), 4 );
		$users = new WP_Query_Users( array( 'role' => 'author', 'number' => 4 ) );
		$this->assertEquals( $count, (int) $users->get_total() );
		
		$count = min( count( $this->subscribers ), 4 );
		//$this->expectOutputString('');
		$users = new WP_Query_Users( array( 'role' => 'subscriber', 'number' => 4 ) );
		$this->assertEquals( $count, (int) $users->get_total() );
		
		$count = min( $this->total, 4 );
		$users = new WP_Query_Users( array( 'number' => 4 ) );
		$this->assertEquals( $count, (int) $users->get_total() );
		
	}
	
	// test 
	function test_wp_query_user_globals_default_users() {
		global $user;
		
		$default = get_option( 'posts_per_page' );
		
		$count = min( count( $this->authors ), $default );
		$users = new WP_Query_Users( array( 'role' => 'author' ) );
		$this->assertEquals( $count, (int) $users->get_total() );
		
		$count = min( count( $this->subscribers ), $default );
		$users = new WP_Query_Users( array( 'role' => 'subscriber' ) );
		$this->assertEquals( $count, (int) $users->get_total() );
		
		$count = min( $this->total, $default );
		$users = new WP_Query_Users();
		$this->assertEquals( $count, (int) $users->get_total() );
		
	}
	
	// test 
	function test_wp_query_users_loop() {
		global $user;
		
		if ( $this->query->have_users() ) {
			while ( $this->query->have_users() )
				$this->query->the_user();
				$this->assertContains( $user->ID, $this->users );
				$this->assertEquals( $user, get_user() );
				$this->assertEquals( $user->data->display_name, get_the_display_name() );
				$this->assertEquals( get_user_meta( $user->ID, 'description', true ), get_the_biography() );
			
		}
		
	}
	
	function test_wp_query_users_total() {
		$this->assertEquals( count( $this->users ), $this->query->get_total() );
		
	}

	function test_wp_query_users_properties() {
		$this->assertEquals( !empty( $this->users ), $this->query->have_users() );
		$this->assertEquals( count( $this->subscribers ), $this->query->total_users );
		
		
	}

}
