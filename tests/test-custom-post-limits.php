<?php

defined( 'ABSPATH' ) or die();

class Custom_Post_Limits_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->set_option();
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

	public function test_uninstall_deletes_option() {
		$option = 'c2c_custom_post_limits';
		c2c_CustomPostLimits::get_instance()->get_options();

		$this->assertNotFalse( get_option( $option ) );

		c2c_CustomPostLimits::uninstall();

		$this->assertFalse( get_option( $option ) );
	}
}
