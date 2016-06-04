<?php

defined( 'ABSPATH' ) or die();

class Custom_Post_Limits_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->set_option();

		update_option( 'posts_per_page', 5 );
	}


	/*
	 *
	 * DATA PROVIDERS
	 *
	 */


	//
	//
	// HELPER FUNCTIONS
	//
	//


	protected function set_option( $settings = array() ) {
		$defaults = array(
			'archives_limit'                     => '',
			'archives_paged_limit'               => '',
			'enable_individual_authors_limit'    => false,
			'authors_limit'                      => '',
			'authors_paged_limit'                => '',
			'enable_individual_categories_limit' => false,
			'categories_limit'                   => '',
			'categories_paged_limit'             => '',
			'day_archives_limit'                 => '',
			'day_archives_paged_limit'           => '',
			'front_page_limit'                   => '',
			'front_page_paged_limit'             => '',
			'month_archives_limit'               => '',
			'month_archives_paged_limit'         => '',
			'searches_limit'                     => '',
			'searches_paged_limit'               => '',
			'enable_individual_tags_limit'       => false,
			'tags_limit'                         => '',
			'tags_paged_limit'                   => '',
			'year_archives_limit'                => '',
			'year_archives_paged_limit'          => '',
		);
		$settings = wp_parse_args( $settings, $defaults );
		c2c_CustomPostLimits::get_instance()->update_option( $settings, true );
	}


	//
	//
	// TESTS
	//
	//


	public function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_CustomPostLimits' ) );
	}

	public function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_CustomPostLimits_Plugin_043' ) );
	}

	public function test_plugin_framework_version() {
		$this->assertEquals( '043', c2c_CustomPostLimits::get_instance()->c2c_plugin_version() );
	}

	public function test_version() {
		$this->assertEquals( '4.0', c2c_CustomPostLimits::get_instance()->version() );
	}

	public function test_instance_object_is_returned() {
		$this->assertTrue( is_a( c2c_CustomPostLimits::get_instance(), 'c2c_CustomPostLimits' ) );
	}

	public function test_default_posts_per_page() {
		$this->assertEquals( 5, get_option( 'posts_per_page' ) );
	}

	/* Archives */

	public function test_archives_limit() {
		$limit = 2;
		$this->set_option( array( 'archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year = get_the_date( 'Y', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	public function test_archives_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'archives_limit' => $offset, 'archives_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year = get_the_date( 'Y', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -($limit+$offset), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$offset ] ), get_post( $q->posts[0] ) );
	}

	/* Day */

	public function test_day_archives_limit() {
		$limit = 2;
		$this->set_option( array( 'day_archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );
		$month = get_the_date( 'm', $post_ids[0] );
		$day   = get_the_date( 'd', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&monthnum=$month&day=$day" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_day() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	public function test_day_archives_limit_applies_when_paged() {
		$limit = 2;
		$this->set_option( array( 'day_archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );
		$month = get_the_date( 'm', $post_ids[0] );
		$day   = get_the_date( 'd', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&monthnum=$month&day=$day&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_day() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -(2*$limit), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$limit ] ), get_post( $q->posts[0] ) );
	}

	public function test_day_archives_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'day_archives_limit' => $offset, 'day_archives_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );
		$month = get_the_date( 'm', $post_ids[0] );
		$day   = get_the_date( 'd', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&monthnum=$month&day=$day&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_day() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -($limit+$offset), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$offset ] ), get_post( $q->posts[0] ) );
	}

	public function test_archives_paged_limit_applies_when_day_archives_paged_without_day_archives_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'day_archives_limit' => $offset, 'archives_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );
		$month = get_the_date( 'm', $post_ids[0] );
		$day   = get_the_date( 'd', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&monthnum=$month&day=$day&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_day() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -($limit+$offset), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$offset ] ), get_post( $q->posts[0] ) );
	}

	/* Front page */

	public function test_front_page_limit() {
		$limit = 3;
		$this->set_option( array( 'front_page_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );

		$this->go_to( home_url() );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_front_page() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	public function test_front_page_paged_limit() {
		$offset = 3;
		$limit  = 2;
		$this->set_option( array( 'front_page_limit' => $offset, 'front_page_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );

		$this->go_to( home_url() . '?paged=2' );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_front_page() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -($limit+$offset), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$offset ] ), get_post( $q->posts[0] ) );
	}

	/* Month */

	public function test_month_archives_limit() {
		$limit = 2;
		$this->set_option( array( 'month_archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );
		$month = get_the_date( 'm', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&monthnum=$month" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_month() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	public function test_month_archives_limit_applies_when_paged() {
		$limit = 2;
		$this->set_option( array( 'month_archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );
		$month = get_the_date( 'm', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&monthnum=$month&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_month() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -(2*$limit), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$limit ] ), get_post( $q->posts[0] ) );
	}

	public function test_month_archives_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'month_archives_limit' => $offset, 'month_archives_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );
		$month = get_the_date( 'm', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&monthnum=$month&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_month() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -($limit+$offset), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$offset ] ), get_post( $q->posts[0] ) );
	}

	public function test_archives_paged_limit_applies_when_month_archives_paged_without_month_archives_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'month_archives_limit' => $offset, 'archives_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );
		$month = get_the_date( 'm', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&monthnum=$month&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_month() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -($limit+$offset), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$offset ] ), get_post( $q->posts[0] ) );
	}

	/* Searches */

	public function test_searches_limit() {
		$limit = 2;
		$this->set_option( array( 'searches_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );

		$this->go_to( home_url() . "?s=content" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_search() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_searches_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'searches_limit' => $offset, 'searches_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );

		$this->go_to( home_url() . "?s=content&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_search() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, $offset, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ $offset ] ), get_post( $q->posts[0] ) );
	}

	/* Year */

	public function test_year_archives_limit() {
		$limit = 2;
		$this->set_option( array( 'year_archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year = get_the_date( 'Y', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_year() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	public function test_year_archives_limit_applies_when_paged() {
		$limit = 3;
		$this->set_option( array( 'year_archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_year() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -(2*$limit), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$limit ] ), get_post( $q->posts[0] ) );
	}

	public function test_years_archives_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'year_archives_limit' => $offset, 'year_archives_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year = get_the_date( 'Y', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_year() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -($limit+$offset), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$offset ] ), get_post( $q->posts[0] ) );
	}

	public function test_archives_paged_limit_applies_when_year_archives_paged_without_year_archives_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'year_archives_limit' => $offset, 'archives_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		$year  = get_the_date( 'Y', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_year() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -($limit+$offset), $limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6-$offset ] ), get_post( $q->posts[0] ) );
	}

	/* Other */

	public function test_negative_one_limit_returns_all_posts() {
		$this->set_option( array( 'archives_limit' => -1 ) );
		$post_ids = $this->factory->post->create_many( 40 );
		$year = get_the_date( 'Y', $post_ids[0] );

		$this->go_to( home_url() . "?year=$year" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_archive() );
		$this->assertTrue( $q->is_year() );
		$this->assertEquals( 40, count( $q->posts ) );
	}

	public function test_uninstall_deletes_option() {
		$option = 'c2c_custom_post_limits';
		c2c_CustomPostLimits::get_instance()->get_options();

		$this->assertNotFalse( get_option( $option ) );

		c2c_CustomPostLimits::uninstall();

		$this->assertFalse( get_option( $option ) );
	}
}
