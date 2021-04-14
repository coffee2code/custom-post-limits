<?php

defined( 'ABSPATH' ) or die();

class Custom_Post_Limits_Test extends WP_UnitTestCase {

	protected static $post_type = 'test-custom-post-limit';

	public static function setUpBeforeClass() {
		c2c_CustomPostLimits::get_instance()->install();
	}

	public function setUp() {
		parent::setUp();

		c2c_CustomPostLimits::get_instance()->reset_options();

		update_option( 'posts_per_page', 5 );
	}

	public function tearDown() {
		parent::tearDown();

		// Reset options
		c2c_CustomPostLimits::get_instance()->reset_options();
		c2c_CustomPostLimits::get_instance()->reset_caches();
	}


	/*
	 *
	 * DATA PROVIDERS
	 *
	 */

	public static function get_settings_and_defaults() {
		return array(
			array( 'archives_limit' ),
			array( 'archives_paged_limit' ),
			array( 'enable_individual_authors_limit' ),
			array( 'authors_limit' ),
			array( 'authors_paged_limit' ),
			array( 'enable_individual_categories_limit' ),
			array( 'categories_limit' ),
			array( 'categories_paged_limit' ),
			array( 'day_archives_limit' ),
			array( 'day_archives_paged_limit' ),
			array( 'front_page_limit' ),
			array( 'front_page_paged_limit' ),
			array( 'month_archives_limit' ),
			array( 'month_archives_paged_limit' ),
			array( 'searches_limit' ),
			array( 'searches_paged_limit' ),
			array( 'enable_individual_tags_limit' ),
			array( 'tags_limit' ),
			array( 'tags_paged_limit' ),
			array( 'year_archives_limit' ),
			array( 'year_archives_paged_limit' ),
		);
	}


	//
	//
	// HELPER FUNCTIONS
	//
	//


	protected function set_option( $settings = array() ) {
		$options = c2c_CustomPostLimits::get_instance()->get_options();

		foreach ( $settings as $setting => $val ) {
			$options[ $setting ] = $val;
		}

		c2c_CustomPostLimits::get_instance()->update_option( $options, true );
	}

	protected function register_taxonomy_writer() {
		global $wp_rewrite;

		register_taxonomy( 'writers', array( 'post' ), array(
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'update_count_callback' => '_update_post_term_count',
			'slug'                  => 'elements',
		) );

		wp_insert_term( 'alice', 'writers' );
		wp_insert_term( 'bob',   'writers' );
		wp_insert_term( 'cher',  'writers' );

		$wp_rewrite->flush_rules();
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
		$this->assertTrue( class_exists( 'c2c_Plugin_061' ) );
	}

	public function test_plugin_framework_version() {
		$this->assertEquals( '061', c2c_CustomPostLimits::get_instance()->c2c_plugin_version() );
	}

	public function test_version() {
		$this->assertEquals( '4.4.1', c2c_CustomPostLimits::get_instance()->version() );
	}

	public function test_instance_object_is_returned() {
		$this->assertTrue( is_a( c2c_CustomPostLimits::get_instance(), 'c2c_CustomPostLimits' ) );
	}

	public function test_hooks_plugins_loaded() {
		$this->assertEquals( 10, has_action( 'plugins_loaded', array( 'c2c_CustomPostLimits', 'get_instance' ) ) );
	}

	/**
	 * @dataProvider get_settings_and_defaults
	 */
	public function test_default_settings( $setting ) {
		$options = c2c_CustomPostLimits::get_instance()->get_options();

		$this->assertEmpty( $options[ $setting ] );
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

	/* Authors */

	public function test_authors_limit() {
		$limit  = 3;
		$this->set_option( array( 'authors_limit' => $limit ) );
		$user_id = $this->factory->user->create();
		$post_ids = $this->factory->post->create_many( 7, array( 'post_author' => $user_id ) );

		$this->go_to( home_url() . "?author=$user_id" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_author( $user_id ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	public function test_authors_paged_limit() {
		$offset = 2;
		$limit  = 4;
		$this->set_option( array( 'authors_limit' => $offset, 'authors_paged_limit' => $limit ) );
		$user_id = $this->factory->user->create();
		$post_ids = $this->factory->post->create_many( 7, array( 'post_author' => $user_id ) );

		$this->go_to( home_url() . "?author=$user_id&paged=2&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_author( $user_id ) );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, $offset, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ $offset ] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_authors_limit_via_author() {
		$limit = 3;
		$user_id = $this->factory->user->create();
		$this->set_option( array(
			'authors_limit' => 6,
			'enable_individual_authors_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'authors', $user_id ) => $limit,
		) );
		$post_ids = $this->factory->post->create_many( 7, array( 'post_author' => $user_id ) );

		$this->go_to( home_url() . "?author=$user_id&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_author( $user_id ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 0 ] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_authors_limit_via_author_name() {
		$limit = 3;
		$author_name = 'sampleguy';
		$user_id = $this->factory->user->create( array( 'user_nicename' => $author_name ) );
		$this->set_option( array(
			'authors_limit' => 6,
			'enable_individual_authors_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'authors', $user_id ) => $limit,
		) );
		$post_ids = $this->factory->post->create_many( 7, array( 'post_author' => $user_id ) );

		$this->go_to( home_url() . "?author_name=$author_name&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_author( $user_id ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 0 ] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_authors_limit_ignored_if_not_enabled() {
		$limit  = 3;
		$user_id = $this->factory->user->create();
		$post_ids = $this->factory->post->create_many( 7, array( 'post_author' => $user_id ) );
		$this->set_option( array(
			'authors_limit' => $limit,
			'enable_individual_authors_limit' => false,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'authors', $user_id ) => 6,
		) );

		$this->go_to( home_url() . "?author=$user_id" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_author( $user_id ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	public function test_authors_limit_with_multiple_authors() {
		$limit  = 3;
		$this->set_option( array( 'authors_limit' => $limit ) );
		$user_ids = array();
		$user_ids[] = $this->factory->user->create();
		$user_ids[] = $this->factory->user->create();
		$post_ids = $this->factory->post->create_many( 7, array( 'post_author' => $user_ids[0] ) );

		$this->go_to( home_url() . "?author=" . $user_ids[0] );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_author() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	public function test_multiple_authors_specified_uses_authors_limit_and_not_individual_limit() {
		$limit = 3;
		$user_ids = array();
		$user_ids[] = $this->factory->user->create();
		$user_ids[] = $this->factory->user->create();
		$this->set_option( array(
			'authors_limit' => $limit,
			'enable_individual_authors_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'authors', $user_ids[0] ) => 5,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'authors', $user_ids[1] ) => 6,
		) );
		$post_ids = $this->factory->post->create_many( 7, array( 'post_author' => $user_ids[0] ) );

		$this->go_to( home_url() . "?author=" . implode( ',', $user_ids ) );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_author() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, -$limit ), wp_list_pluck( array_reverse( $q->posts ), 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ 6 ] ), get_post( $q->posts[0] ) );
	}

	/* Categories */

	public function test_categories_limit() {
		$limit  = 3;
		$cat    = 'vehicle';
		$cat_id = $this->factory->category->create( array( 'slug' => $cat ) );
		$this->set_option( array( 'categories_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_set_post_categories( $pid, array( $cat_id ) );
		}

		$this->go_to( home_url() . "?category_name=$cat&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_category( $cat ) );
		$this->assertFalse( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'categories' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_categories_paged_limit() {
		$offset  = 2;
		$limit   = 4;
		$cat     = 'vehicle';
		$cat_id  = $this->factory->category->create( array( 'slug' => $cat ) );
		$this->set_option( array( 'categories_limit' => $offset, 'categories_paged_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_set_post_categories( $pid, array( $cat_id ) );
		}

		$this->go_to( home_url() . "?category_name=$cat&paged=2&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_category( $cat ) );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, $offset, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ $offset ] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_categories_limit_via_cat() {
		$limit  = 3;
		$cat    = 'vehicle';
		$cat_id = $this->factory->category->create( array( 'slug' => $cat ) );
		$this->set_option( array(
			'categories_limit' => 6,
			'enable_individual_categories_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'categories', $cat_id ) => $limit,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_set_post_categories( $pid, array( $cat_id ) );
		}

		$this->go_to( home_url() . "?cat=$cat_id&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_category( $cat ) );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'categories' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_categories_limit_via_category_name() {
		$limit  = 3;
		$cat    = 'vehicle';
		$cat_id = $this->factory->category->create( array ( 'slug' => $cat ) );
		$this->set_option( array(
			'categories_limit' => 6,
			'enable_individual_categories_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'categories', $cat_id ) => $limit,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_set_post_categories( $pid, array( $cat_id ) );
		}

		$this->go_to( home_url() . "?category_name=$cat&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_category( $cat ) );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'categories' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_categories_paged_limit() {
		$limit   = 4;
		$cat     = 'vehicle';
		$cat_id  = $this->factory->category->create( array( 'slug' => $cat ) );
		$this->set_option( array(
			'categories_limit' => 6,
			'categories_paged_limit' => 2,
			'enable_individual_categories_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'categories', $cat_id ) => $limit,
		) );
		$post_ids = $this->factory->post->create_many( 13 );
		foreach ( $post_ids as $pid ) {
			wp_set_post_categories( $pid, array( $cat_id ) );
		}

		$this->go_to( home_url() . "?category_name=$cat&paged=2&orderby=ID&order=ASC&paged=2" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_category( $cat ) );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'categories' ) );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, $limit, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ $limit ] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_categories_limit_ignored_if_not_enabled() {
		$limit  = 3;
		$cat    = 'vehicle';
		$cat_id = $this->factory->category->create( array( 'slug' => $cat ) );
		$this->set_option( array(
			'categories_limit' => $limit,
			'enable_individual_categories_limit' => false,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'categories', $cat_id ) => 6,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_set_post_categories( $pid, array( $cat_id ) );
		}

		$this->go_to( home_url() . "?category_name=$cat&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_category( $cat ) );
		$this->assertFalse( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'categories' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_multiple_categories_specified_uses_categories_limit() {
		$limit  = 3;
		$cats   = array( 'cars', 'motocycles' );
		$cat_ids = array();
		foreach ( $cats as $cat ) {
			$cat_ids[] = $this->factory->category->create( array( 'slug' => $cat ) );
		}
		$this->set_option( array( 'categories_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_set_post_categories( $pid, $cat_ids );
		}

		$this->go_to( home_url() . "?orderby=ID&order=ASC&cat=" . implode( ',', $cat_ids ) );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_category() );
		$this->assertFalse( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'categories' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_multiple_categories_specified_uses_categories_limit_and_not_individual_limit() {
		$limit  = 3;
		$cats   = array( 'cars', 'motocycles' );
		$cat_ids = array();
		foreach ( $cats as $cat ) {
			$cat_ids[] = $this->factory->category->create( array( 'slug' => $cat ) );
		}
		$this->set_option( array(
			'categories_limit' => $limit,
			'enable_individual_categories_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'categories', $cat_ids[0] ) => 5,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'categories', $cat_ids[1] ) => 6,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_set_post_categories( $pid, $cat_ids );
		}

		$this->go_to( home_url() . "?orderby=ID&order=ASC&cat=" . implode( ',', $cat_ids ) );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_category() );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'categories' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
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
		$this->set_option( array( 'day_archives_limit' => $offset, 'day_archives_paged_limit' => $limit, 'archives_paged_limit' => 1 ) );
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

	public function test_day_archives_limit_applies_when_paged_even_with_archives_paged_limit() {
		$limit = 2;
		$this->set_option( array( 'day_archives_limit' => $limit, 'archives_paged_limit' => 3 ) );
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
		$this->set_option( array( 'month_archives_limit' => $offset, 'month_archives_paged_limit' => $limit, 'archives_paged_limit' => 1 ) );
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

	public function test_month_archives_limit_applies_when_paged_even_with_archives_paged_limit() {
		$limit = 2;
		$this->set_option( array( 'month_archives_limit' => $limit, 'archives_paged_limit' => 3 ) );
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

	/* Searches */

	public function test_searches_limit() {
		$limit = 2;
		$this->set_option( array( 'searches_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );

		$this->go_to( home_url() . "?s=content&orderby=ID&order=ASC" );
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

		$this->go_to( home_url() . "?s=content&paged=2&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_search() );
		$this->assertTrue( $q->is_paged() );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, $offset, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ $offset ] ), get_post( $q->posts[0] ) );
	}

	/* Tags */

	public function test_tags_limit() {
		$limit = 3;
		$tag   = 'family';
		$this->set_option( array( 'tags_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 21 );
		foreach ( $post_ids as $pid ) {
			wp_add_post_tags( $pid, $tag );
		}

		$this->go_to( home_url() . "?tag=$tag&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_tag( $tag ) );
		$this->assertFalse( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'tags' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_tags_paged_limit( $page_num = 2, $first_page_limit = 2, $paginated_limit = 4, $total_posts = 21 ) {
		$tag = 'family';

		$config = array();
		if ( $first_page_limit ) {
			$config['tags_limit'] = $first_page_limit;
		}
		if ( $paginated_limit ) {
			$config['tags_paged_limit'] = $paginated_limit;
		} else {
			$paginated_limit = $first_page_limit;
		}
		$this->set_option( $config );

		$post_ids = $this->factory->post->create_many( $total_posts );
		foreach ( $post_ids as $pid ) {
			wp_add_post_tags( $pid, $tag );
		}

		$this->go_to( home_url() . "?tag={$tag}&paged={$page_num}&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_tag( $tag ) );
		if ( 1 !== $page_num ) {
			$this->assertTrue( $q->is_paged() );
		}

		$limit = 1 === $page_num ? $first_page_limit : $paginated_limit;

		$offset = 0;
		if ( $page_num > 1 ) {
			$offset += $first_page_limit;
		}
		if ( $page_num > 2 ) {
			$offset += ceil( 0, $paginated_limit * ( $page_num - 2 ) );
		}

		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, $offset, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ $offset ] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_tags_limit_via_tag_id() {
		$limit = 3;
		$tag   = 'family';
		$tag_id = $this->factory->tag->create( array( 'slug' => $tag ) );
		$this->set_option( array(
			'tags_limit' => 6,
			'enable_individual_tags_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'tags', $tag_id ) => $limit,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_add_post_tags( $pid, $tag );
		}

		$this->go_to( home_url() . "?tag_id=$tag_id&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_tag( $tag ) );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'tags' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_individual_tags_limit_via_tag() {
		$limit  = 3;
		$tag    = 'family';
		$tag_id = $this->factory->tag->create( array( 'slug' => $tag ) );
		$this->set_option( array(
			'tags_limit' => 6,
			'enable_individual_tags_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'tags', $tag_id ) => $limit,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_add_post_tags( $pid, $tag );
		}

		$this->go_to( home_url() . "?tag=$tag&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_tag( $tag_id ) );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'tags' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	/**
	 * Note: This verifies existing behavior. In the future the plugin may allow for paged individual
	 * archives to be explicitly defined and have a more consistent fallback, e.g.
	 * individual paged archive -> individual limit -> tags paged limit -> tags limit
	 */
	public function test_individual_tags_paged_limit() {
		$limit = 3;
		$tag   = 'family';
		$tag_id = $this->factory->tag->create( array( 'slug' => $tag ) );
		$this->set_option( array(
			'tags_limit' => 6,
			'tags_paged_limit' => 2,
			'enable_individual_tags_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'tags', $tag_id ) => $limit,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_add_post_tags( $pid, $tag );
		}

		$this->go_to( home_url() . "?tag_id=$tag_id&paged=2&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_tag( $tag ) );
		$this->assertTrue( $q->is_paged() );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'tags' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, $limit, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[ $limit ] ), get_post( $q->posts[0] ) );
	}


	public function test_individual_tags_limit_ignored_if_not_enabled() {
		$limit  = 3;
		$tag    = 'family';
		$tag_id = $this->factory->tag->create( array( 'slug' => $tag ) );
		$this->set_option( array(
			'tags_limit' => $limit,
			'enable_individual_tags_limit' => false,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'tags', $tag_id ) => 6,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_add_post_tags( $pid, $tag );
		}

		$this->go_to( home_url() . "?tag=$tag&orderby=ID&order=ASC" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_tag( $tag ) );
		$this->assertFalse( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'tags' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_tags_limit_with_multiple_tags() {
		$limit = 3;
		$tags  = array( 'family', 'work' );
		$this->set_option( array( 'tags_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_add_post_tags( $pid, $tags );
		}

		$this->go_to( home_url() . "?orderby=ID&order=ASC&tag=" . implode( ',', $tags ) );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_tag() );
		$this->assertFalse( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'tags' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
	}

	public function test_multiple_tags_specified_uses_tags_limit_and_not_individual_limit() {
		$limit   = 3;
		$tags    = array( 'family', 'work' );
		$tag_ids = array();
		foreach( $tags as $tag ) {
			$tag_ids[] = $this->factory->tag->create( array( 'slug' => $tag ) );
		}
		$this->set_option( array(
			'tags_limit' => $limit,
			'enable_individual_tags_limit' => true,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'tags', $tag_ids[0] ) => 5,
			c2c_CustomPostLimits::get_individual_limit_setting_name( 'tags', $tag_ids[1] ) => 6,
		) );
		$post_ids = $this->factory->post->create_many( 7 );
		foreach ( $post_ids as $pid ) {
			wp_add_post_tags( $pid, $tags[0] );
			wp_add_post_tags( $pid, $tags[1] );
		}

		$this->go_to( home_url() . "?orderby=ID&order=ASC&tag=" . implode( ',', $tags ) );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_tag() );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'tags' ) );
		$this->assertEquals( $limit, count( $q->posts ) );
		$this->assertEquals( array_slice( $post_ids, 0, $limit ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertEquals( get_post( $post_ids[0] ), get_post( $q->posts[0] ) );
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
		$this->set_option( array( 'year_archives_limit' => $offset, 'year_archives_paged_limit' => $limit, 'archives_paged_limit' => 1 ) );
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

	public function test_year_archives_limit_applies_when_paged_even_with_archives_paged_limit() {
		$limit = 2;
		$this->set_option( array( 'year_archives_limit' => $limit, 'archives_paged_limit' => 3 ) );
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

	/* Custom post type */

	public function test_get_post_type() {
		global $wp_rewrite;
		$limit = 2;
		register_post_type( 'guide', array( 'name' => 'Guide', 'public' => true, 'has_archive' => true ) );
		$wp_rewrite->flush_rules();
		$post_type_setting = c2c_CustomPostLimits::get_individual_limit_setting_name( 'customposttypes', 'guide' );
		$this->set_option( array( $post_type_setting => $limit, 'archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7, array( 'post_type' => 'guide' ) );

		$this->go_to( home_url() . "?post_type=guide" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_post_type_archive() );
		$this->assertEquals( $limit, count( $q->posts ) );
	}

	public function test_post_type_limit_works_for_subsequent_post_types() {
		global $wp_rewrite;
		$limit = 3;
		register_post_type( 'guide', array( 'name' => 'Guide', 'public' => true, 'has_archive' => true ) );
		register_post_type( 'sample', array( 'name' => 'Sample', 'public' => true, 'has_archive' => true ) );
		$wp_rewrite->flush_rules();
		$post_type_setting = c2c_CustomPostLimits::get_individual_limit_setting_name( 'customposttypes', 'sample' );
		$this->set_option( array( $post_type_setting => $limit, 'archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7, array( 'post_type' => 'sample' ) );

		$this->go_to( home_url() . "?post_type=sample" );
		$q = $GLOBALS['wp_query'];

		$this->assertTrue( $q->is_post_type_archive() );
		$this->assertEquals( $limit, count( $q->posts ) );
	}

	public function test_public_post_types_gets_option_initialized() {
		register_post_type( 'guide1', array( 'name' => 'Guide1', 'public' => true, 'has_archive' => true ) );

		$options = c2c_CustomPostLimits::get_instance()->get_options();

		$this->assertTrue( isset( $options['customposttypes_guide1_limit'] ) );
	}

	public function test_private_post_types_dont_get_option_initialized() {
		register_post_type( 'guide2', array( 'name' => 'Guide2', 'public' => false, 'has_archive' => false ) );

		$options = c2c_CustomPostLimits::get_instance()->get_options();

		$this->assertFalse( isset( $options['customposttypes_guide2_limit'] ) );
	}

	/* Custom taxonomy */

	public function test_get_custom_taxonomy() {
		$this->register_taxonomy_writer();

		$limit = 3;
		$custom_tax_setting = c2c_CustomPostLimits::get_individual_limit_setting_name( 'customtaxonomies', 'writers' );
		$this->set_option( array( $custom_tax_setting => $limit, 'archives_limit' => 4 ) );
		$post_ids = $this->factory->post->create_many( 7 );

		$alice_posts = array( $post_ids[1], $post_ids[3], $post_ids[4], $post_ids[5] );

		foreach ( $alice_posts as $p ) {
			wp_set_object_terms( $p, 'alice', 'writers' );
		}

		$url = add_query_arg(
			array(
				'writers' => 'alice',
				'orderby' => 'ID',
				'order'   => 'ASC',
			), '/'
		);
		$this->go_to( $url );

		$wp_query = $GLOBALS['wp_query'];
		$this->assertTrue( $wp_query->is_tax( 'writers') );
		$this->assertEquals( $limit, count( $wp_query->posts ) );
		$this->assertEqualSets( array_slice( $alice_posts, 0, $limit ), wp_list_pluck( $wp_query->posts, 'ID' ) );
	}

	public function test_custom_taxonomy_limit_works_does_not_affect_another_taxonomy() {
		$this->register_taxonomy_writer();

		$limit = 2;
		$custom_tax_setting = c2c_CustomPostLimits::get_individual_limit_setting_name( 'customtaxonomies', 'sample' );
		$this->set_option( array( $custom_tax_setting => 3, 'archives_limit' => $limit ) );
		$post_ids = $this->factory->post->create_many( 7 );

		$alice_posts = array_slice( $post_ids, 0, 6 );

		foreach ( $post_ids as $p ) {
			wp_set_object_terms( $p, 'alice', 'writers' );
		}

		$url = add_query_arg(
			array(
				'writers' => 'alice',
			), '/'
		);
		$this->go_to( $url );

		$wp_query = $GLOBALS['wp_query'];
		$this->assertTrue( $wp_query->is_tax( 'writers') );
		$this->assertEquals( 5, count( $wp_query->posts ) );
	}

	public function test_custom_taxonomy_gets_option_initialized() {
		$this->register_taxonomy_writer();

		$options = c2c_CustomPostLimits::get_instance()->get_options();

		$this->assertTrue( isset( $options['customtaxonomies_writers_limit'] ) );
	}

	/* has_individual_limits() */

	public function test_has_individual_limits_for_valid_types() {
		$types = array(
			'authors',
			'categories',
			'customposttypes',
			'tags',
		);

		foreach ( $types as $type ) {
			$this->assertTrue( c2c_CustomPostLimits::has_individual_limits( $type ) );
		}
	}

	public function test_has_individual_limits_for_invalid_types() {
		$types = array(
			'author',
			'category',
			'custom_post_types',
			'customposttype',
			'nonsense',
			'tag',
			0,
			'',
		);

		foreach ( $types as $type ) {
			$this->assertFalse( c2c_CustomPostLimits::has_individual_limits( $type ) );
		}
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

	/*
	 * display_individual_option()
	 */

	public function test_display_individual_option_with_empty_type() {
		$this->expectOutputRegex(
			'/^$/',
			c2c_CustomPostLimits::get_instance()->display_individual_option( 'tags_1' )
		);
	}

	public function test_display_individual_option_with_type_but_individual_limits_not_enabled() {
		$this->test_tags_limit();
		$this->expectOutputRegex(
			'/^$/',
			c2c_CustomPostLimits::get_instance()->display_individual_option( 'tags_1' )
		);
	}

	public function test_display_individual_option_with_type_and_individual_limits_enabled() {
		$this->test_individual_tags_limit_via_tag();
		$limit = 3; // From test_individual_tags_limit_via_tag()
		$tag = get_term_by( 'slug', 'family', 'post_tag' );
		$this->assertTrue( c2c_CustomPostLimits::get_instance()->is_individual_limits_enabled( 'tags' ) );

		$expected = sprintf(
			"<tr valign='top' class='cpl-tags'><th scope='row'> &nbsp; &nbsp; &#8212; %s</th><td><input type='text' class='c2c_short_text small-text' name='c2c_custom_post_limits[tags_%d_limit]' value='%s' /></td></tr>",
			$tag->name,
			$tag->term_id,
			$limit
		);

		$this->expectOutputRegex(
			'|' . preg_quote( $expected) . '|',
			c2c_CustomPostLimits::get_instance()->display_individual_option( 'tags_' . $tag->term_id )
		);
	}

	/*
	 * adjust_max_num_pages()
	 */

	public function test_adjust_max_num_pages_first_page_without_paginated_limits() {
		$this->test_tags_paged_limit( 1, 3, '', 21 );

		$this->assertEquals( 7, $GLOBALS['wp_query']->max_num_pages );
	}

	public function test_adjust_max_num_pages_second_page_without_paginated_limits() {
		$this->test_tags_paged_limit( 2, 3, '', 21 );

		$this->assertEquals( 7, $GLOBALS['wp_query']->max_num_pages );
	}

	public function test_adjust_max_num_pages_first_page_with_matching_paginated_limits() {
		$this->test_tags_paged_limit( 1, 3, 3, 21 );

		$this->assertEquals( 7, $GLOBALS['wp_query']->max_num_pages );
	}

	public function test_adjust_max_num_pages_second_page_with_matching_paginated_limits() {
		$this->test_tags_paged_limit( 2, 3, 3, 21 );

		$this->assertEquals( 7, $GLOBALS['wp_query']->max_num_pages );
	}

	public function test_adjust_max_num_pages_first_page_with_paginated_limits() {
		$this->test_tags_paged_limit( 1, 3, 10, 21 );

		$this->assertEquals( 3, $GLOBALS['wp_query']->max_num_pages );
	}

	public function test_adjust_max_num_pages_first_page_with_paginated_limits2() {
		$this->test_tags_paged_limit( 1, 3, 7, 21 );

		$this->assertEquals( 4, $GLOBALS['wp_query']->max_num_pages );
	}

	public function test_adjust_max_num_pages_second_page_with_paginated_limits() {
		$this->test_tags_paged_limit( 2, 3, 10, 21 );

		$this->assertEquals( 3, $GLOBALS['wp_query']->max_num_pages );
	}

	public function test_adjust_max_num_pages_second_page_with_paginated_limits2() {
		$this->test_tags_paged_limit( 2, 3, 7, 21 );

		$this->assertEquals( 4, $GLOBALS['wp_query']->max_num_pages );
	}

	/*
	 * get_authors()
	 */

	public function test_get_authors_with_no_authors_and_individual_authors_enabled() {
		$this->set_option( array(
			'authors_limit' => 5,
			'enable_individual_authors_limit' => true,
		) );

		$value = c2c_CustomPostLimits::get_instance()->get_authors();

		$this->assertNotEmpty( $value );
		$this->assertTrue( is_array( $value ) );
		$this->assertEquals( 1, count( $value ) );
		$this->assertEquals( 'admin', $value[0]->user_nicename );
	}

	public function test_get_authors_with_no_authors_and_individual_authors_not_enabled() {
		$value = c2c_CustomPostLimits::get_instance()->get_authors();

		$this->assertIsBool( $value );
		$this->assertTrue( $value );
	}

	public function test_get_authors_with_authors_and_individual_authors_enabled() {
		$this->set_option( array(
			'authors_limit' => 5,
			'enable_individual_authors_limit' => true,
		) );
		$user_id1 = $this->factory->user->create( array( 'role' => 'author' ) );
		$post_id1 = $this->factory->post->create( array( 'post_author' => $user_id1 ) );
		$user_id2 = $this->factory->user->create( array( 'role' => 'author' ) );
		$post_id2 = $this->factory->post->create( array( 'post_author' => $user_id2 ) );

		$value = c2c_CustomPostLimits::get_instance()->get_authors();

		$this->assertNotEmpty( $value );
		$this->assertTrue( is_array( $value ) );
		$this->assertEquals( 3, count( $value ) );
		$this->assertEquals( $value, get_users( array( 'fields' => array( 'ID', 'display_name', 'user_nicename' ), 'order' => 'display_name' ) ) );
	}

	public function test_get_authors_with_authors_and_individual_authors_not_enabled() {
		$cat_id1 = $this->factory->category->create( array( 'slug' => 'color' ) );
		$cat_id2 = $this->factory->category->create( array( 'slug' => 'texture' ) );

		$value = c2c_CustomPostLimits::get_instance()->get_authors();

		$this->assertIsBool( $value );
		$this->assertTrue( $value );
	}

	public function test_get_authors_ignores_non_authors() {
		$this->set_option( array(
			'authors_limit' => 5,
			'enable_individual_authors_limit' => true,
		) );

		$user_id1 = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$post_id1 = $this->factory->post->create( array( 'post_author' => $user_id1 ) );
		$user_id2 = $this->factory->user->create( array( 'role' => 'author' ) );
		$post_id2 = $this->factory->post->create( array( 'post_author' => $user_id2 ) );
		$user_id3 = $this->factory->user->create( array( 'role' => 'editor' ) );
		$post_id3 = $this->factory->post->create( array( 'post_author' => $user_id3 ) );

		$value = c2c_CustomPostLimits::get_instance()->get_authors();

		$this->assertNotEmpty( $value );
		$this->assertIsArray( $value );
		$this->assertEquals( 3, count( $value ) );
		$this->assertEquals( $value, get_users( array( 'fields' => array( 'ID', 'display_name', 'user_nicename' ), 'order' => 'display_name', 'who' => 'authors' ) ) );
	}

	/*
	 * get_categories()
	 */

	public function test_get_categories_with_no_categories_and_individual_categories_enabled() {
		$this->set_option( array(
			'categories_limit' => 5,
			'enable_individual_categories_limit' => true,
		) );

		$value = c2c_CustomPostLimits::get_instance()->get_categories();

		$this->assertNotEmpty( $value );
		$this->assertTrue( is_array( $value ) );
		$this->assertEquals( 1, count( $value ) );
		$this->assertEquals( 'Uncategorized', $value[0]->cat_name );
	}

	public function test_get_categories_with_no_categories_and_individual_categories_not_enabled() {
		$value = c2c_CustomPostLimits::get_instance()->get_categories();

		$this->assertIsBool( $value );
		$this->assertTrue( $value );
	}

	public function test_get_categories_with_categories_and_individual_categories_enabled() {
		$this->set_option( array(
			'categories_limit' => 5,
			'enable_individual_categories_limit' => true,
		) );
		$cat_id1 = $this->factory->category->create( array( 'slug' => 'color' ) );
		$cat_id2 = $this->factory->category->create( array( 'slug' => 'texture' ) );

		$value = c2c_CustomPostLimits::get_instance()->get_categories();

		$this->assertNotEmpty( $value );
		$this->assertTrue( is_array( $value ) );
		$this->assertEquals( 3, count( $value ) );
		$this->assertEquals( $value, get_categories( array( 'hide_empty' => false ) ) );
	}

	public function test_get_categories_with_categories_and_individual_categories_not_enabled() {
		$cat_id1 = $this->factory->category->create( array( 'slug' => 'color' ) );
		$cat_id2 = $this->factory->category->create( array( 'slug' => 'texture' ) );

		$value = c2c_CustomPostLimits::get_instance()->get_categories();

		$this->assertIsBool( $value );
		$this->assertTrue( $value );
	}

	/*
	 * get_tags()
	 */

	public function test_get_tags_with_no_tags_and_individual_tags_enabled() {
		$this->set_option( array(
			'tags_limit' => 5,
			'enable_individual_tags_limit' => true,
		) );

		$value = c2c_CustomPostLimits::get_instance()->get_tags();

		$this->assertEmpty( $value );
		$this->assertTrue( is_array( $value ) );
	}

	public function test_get_tags_with_no_tags_and_individual_tags_not_enabled() {
		$value = c2c_CustomPostLimits::get_instance()->get_tags();

		$this->assertIsBool( $value );
		$this->assertTrue( $value );
	}

	public function test_get_tags_with_tags_and_individual_tags_enabled() {
		$this->set_option( array(
			'tags_limit' => 5,
			'enable_individual_tags_limit' => true,
		) );
		$tag     = 'family';
		$tag_ids = array(
			$this->factory->tag->create( array( 'slug' => $tag . '1' ) ),
			$this->factory->tag->create( array( 'slug' => $tag . '2' ) ),
		);

		$value = c2c_CustomPostLimits::get_instance()->get_tags();

		$this->assertNotEmpty( $value );
		$this->assertEquals( $value, get_tags( array( 'hide_empty' => false ) ) );
	}

	public function test_get_tags_with_tags_and_individual_tags_not_enabled() {
		$tag     = 'family';
		$tag_ids = array(
			$this->factory->tag->create( array( 'slug' => $tag . '1' ) ),
			$this->factory->tag->create( array( 'slug' => $tag . '2' ) ),
		);

		$value = c2c_CustomPostLimits::get_instance()->get_tags();

		$this->assertIsBool( $value );
		$this->assertTrue( $value );
	}

	/*
	 * Setting handling
	 */

	public function test_does_not_immediately_store_default_settings_in_db() {
		$option_name = c2c_CustomPostLimits::SETTING_NAME;
		// Get the options just to see if they may get saved.
		$options     = c2c_CustomPostLimits::get_instance()->get_options();

		$this->assertFalse( get_option( $option_name ) );
	}

	public function test_uninstall_deletes_option() {
		$option_name = c2c_CustomPostLimits::SETTING_NAME;
		$options     = c2c_CustomPostLimits::get_instance()->get_options();

		// Explicitly set an option to ensure options get saved to the database.
		$this->set_option( array( 'archives_limit' => -1 ) );

		$this->assertNotEmpty( $options );
		$this->assertNotFalse( get_option( $option_name ) );

		c2c_CustomPostLimits::uninstall();

		$this->assertFalse( get_option( $option_name ) );
	}

}
