<?php

/**
 * Tests functions ahead
 *
 * @package functions-ahead
 */
class Functions_Ahead_Test_Functions extends WP_UnitTestCase {

	/**
	 * Run simple tests
	 */
	function test___return_empty_string() {
		$this->assertEquals( '', __return_empty_string() );

	}
	
	function test_current_url() {
		$this->assertEquals( home_url(), current_url() );

	}
	
	function test_time_diference() {
		for ( $i = 0; $i < 100; $i++ ) {
			$time_a = rand( 10, 10000 ) * time();
			$time_b = rand( 10, 10000 ) * time();
			
			// Test MySQL
			$mysql = date( 'Y-m-d H:i:s', abs( $time_a - $time_b ) );
			$this->assertEquals( $mysql, time_diference( $time_a, $time_b, 'mysql' ) );
			
			// Test Timestamp
			$this->assertEquals( abs( $time_a - $time_b ), time_diference( $time_a, $time_b ) );
			
			// Test Object
			$time = time_diference( $time_a, $time_b, 'object' );
			$this->assertGreaterThanOrEqual( 0, $time->years );
			
		}

	}
	
	function test_filter_url() {
		$url  = 'https://username:password@hostname.com:443/path/to/dir/';
		$url .= '?arg1=value1&arg2[]=value21&arg2[]=value22#anchor/to/this';
		
		$expected = (object) array(
			'ssl' => true,
			'scheme' => 'https', 
			'host' => 'hostname.com', 
			'user' => 'username', 
			'pass' => 'password',
			'port' => 443, 
			'path' => array( 'path', 'to', 'dir' ),
			'query' => array( 
				'arg1' => 'value1', 
				'arg2' => array( 'value21', 'value22' )
			), 
			'fragment' => 'anchor/to/this'
		);
		
		$this->assertEquals( $expected, filter_url( $url ) );
	
	
	}
	
	function test_concatenate() {
		$lorem  = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, ';
		$lorem .= 'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ';
		$lorem .= 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi';
		$lorem .= ' ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit ';
		$lorem .= 'in voluptate velit esse cillum dolore eu fugiat nulla pariatur. ';
		$lorem .= 'Excepteur sint occaecat';
		
		$this->assertEquals( 'Lorem ipsum dolor sit amet, consectetur adipisicing eli[...]', concatenate( $lorem ) );
	
	}
	
	function test_wp_parse_attrs() {
		$object = new stdClass;
		$object->property1 = 'hey man!';
		$object->prop2 = array( 
			'p1', 'p2', 'a' => 'p3', 8 => array( 'string', $object ) 
		);
		
		$arrr = array(
			'one', 'a' => 'two', 'three', 0 => 'four', array(
				'five, six' => 'seven', 'eight', 'nine' => $object
			)
		);
		
		$array = array(
			'attr1' => 'value1',
			'attr2' => 'value2',
			'attr3' => $arrr,
			'no_key',
			'object' => $object,
			$object,
 		);
		
		$parsed = wp_parse_attrs( $array, array( 
			'attr1' => 'qwert123', 'type1' => 'This will be there!' )
		);
		
		// The expected
		$serialized_object = maybe_serialize( $object );
		$serialized_array  = maybe_serialize( $arrr );
		$expect = "attr1=\"value1\" type1=\"This will be there!\" attr2=\"value2\" attr3=\"{$serialized_array}\" 0=\"no_key\" object=\"{$serialized_object}\" 1=\"{$serialized_object}\"";		
		
		$this->assertEquals( $parsed, $expect );
	
	}

}
