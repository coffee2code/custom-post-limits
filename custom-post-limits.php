<?php
/**
 * Plugin Name: Custom Post Limits
 * Version:     4.4.1
 * Plugin URI:  https://coffee2code.com/wp-plugins/custom-post-limits/
 * Author:      Scott Reilly
 * Author URI:  https://coffee2code.com/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-post-limits
 * Description: Independently control the number of posts listed on the front page, author/category/tag archives, search results, etc.
 *
 * Compatible with WordPress 4.9 through 5.7+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/custom-post-limits/
 *
 * @package Custom_Post_Limits
 * @author  Scott Reilly
 * @version 4.4.1
 */

/*
	Copyright (c) 2008-2021 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'c2c_CustomPostLimits' ) ) :

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'c2c-plugin.php' );

final class c2c_CustomPostLimits extends c2c_Plugin_061 {

	/**
	 * Name of plugin's setting.
	 *
	 * @var string
	 */
	const SETTING_NAME = 'c2c_custom_post_limits';

	/**
	 * The one true instance.
	 *
	 * @var c2c_CustomPostLimits
	 */
	private static $instance;

	/**
	 * Memoized array of authors.
	 *
	 * @since 2.0
	 * @access private
	 * @var array
	 */
	private $authors           = array();

	/**
	 * Memoized array of categories.
	 *
	 * @since 2.0
	 * @access private
	 * @var array
	 */
	private $categories        = array();

	/**
	 * Memoized array of tags.
	 *
	 * @since 2.0
	 * @access private
	 * @var array
	 */
	private $tags              = array();

	/**
	 * The paging offset of the first page (which can be a different value than all
	 * subsequent pages).
	 *
	 * @since 3.5
	 * @access private
	 * @var int|null
	 */
	private $first_page_offset = null;

	/**
	 * Array of individual limits.
	 *
	 * @since 3.6
	 * @access private
	 * @var array
	 */
	private static $individual_limits = array( 'all' => null, 'authors' => null, 'categories' => null, 'tags' => null );

	/**
	 * Get singleton instance.
	 *
	 * @since 4.0
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct( '4.4.1', 'custom-post-limits', 'c2c', __FILE__, array() );
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );

		// Handle custom post types.
		add_action( 'registered_post_type', array( $this, 'registered_post_type' ), 10, 2 );

		// Handle custom taxonomies.
		add_action( 'registered_taxonomy',  array( $this, 'registered_taxonomy' ),  10, 3 );

		return self::$instance = $this;
	}

	/**
	 * Handles activation tasks, such as registering the uninstall hook.
	 *
	 * @since 3.5
	 */
	public static function activation() {
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Handles uninstallation tasks, such as deleting plugin options.
	 *
	 * @since 3.5
	 */
	public static function uninstall() {
		delete_option( self::SETTING_NAME );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 */
	public function load_config() {
		$this->name      = __( 'Custom Post Limits', 'custom-post-limits' );
		$this->menu_name = __( 'Post Limits', 'custom-post-limits' );

		$this->add_option( 'archives_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Archives Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'archives_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );

		$this->add_option( 'enable_individual_authors_limit', array(
			'input'    => 'checkbox',
			'default'  => false,
			'label'    => __( 'Enable individual authors limit?', 'custom-post-limits' ),
			'help'     => __( 'Allows you to set limits for specific authors. If enabled, a link will appear after the Authors Limit field.<br /><em>Warning: if you have a lot of authors this may prevent this page from loading</em>.', 'custom-post-limits' ),
		) );

		$this->add_option( 'authors_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Authors Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'authors_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );

		$this->add_option( 'enable_individual_categories_limit', array(
			'input'    => 'checkbox',
			'default'  => false,
			'label'    => __( 'Enable individual categories limit?', 'custom-post-limits' ),
			'help'     => __( 'Allows you to set limits for specific categories. If enabled, a link will appear after the Categories Limit field.<br /><em>Warning: if you have a lot of categories this may prevent this page from loading</em>.', 'custom-post-limits' ),
		) );

		$this->add_option( 'categories_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Categories Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'categories_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );

		$this->add_option( 'day_archives_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Day Archives Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'day_archives_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );

		$this->add_option( 'front_page_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Front Page Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'front_page_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );

		$this->add_option( 'month_archives_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Month Archives Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'month_archives_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );

		$this->add_option( 'searches_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Searches Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'searches_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );

		$this->add_option( 'enable_individual_tags_limit', array(
			'input'    => 'checkbox',
			'default'  => false,
			'label'    => __( 'Enable individual tags limit?', 'custom-post-limits' ),
			'help'     => __( 'Allows you to set limits for specific tags. If enabled, a link will appear after the Tags Limit field.<br /><em>Warning: if you have a lot of tags this may prevent this page from loading</em>.', 'custom-post-limits' ),
		) );

		$this->add_option( 'tags_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Tags Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'tags_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );

		$this->add_option( 'year_archives_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( 'Year Archives Limit', 'custom-post-limits' ),
		) );

		$this->add_option( 'year_archives_paged_limit', array(
			'input'    => 'short_text',
			'datatype' => 'int',
			'label'    => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', 'custom-post-limits' ),
		) );
	}

	/**
	 * Override plugin framework's register_filters() to register actions and filters.
	 */
	public function register_filters() {
		if ( $this->is_plugin_admin_page() ) {
			// Add plugin settings page JS
			add_action( 'admin_print_footer_scripts',                      array( $this, 'add_plugin_admin_js' ) );
			// Dynamically add help text to the plugin settings page
			add_filter( $this->get_hook( 'option_help' ),                  array( $this, 'dynamic_option_help' ), 10, 2 );
			// Hook the post-display of each plugin option in order to potentially output dynamically listed individual items.
			add_action( $this->get_hook( 'post_display_option' ),          array( $this, 'display_individual_option' ) );
		}

		if ( ! is_admin() ) {
			// Override the default post limits prior to the db being queried
			add_action( 'pre_option_posts_per_page',                       array( $this, 'custom_post_limits' ) );
			// Possibly modify the offset within the LIMIT clause
			add_filter( 'post_limits',                                     array( $this, 'correct_paged_offset' ), 10, 2 );
			// Possibly adjust query's determined number of pages.
			add_filter( 'the_posts',                                       array( $this, 'adjust_max_num_pages' ), 5, 2 );
		}

		// Hook option retrieval to instantiate settings for individual items.
		add_filter( $this->get_hook( 'options' ),                          array( $this, 'load_individual_options' ) );
		// Hook post-updating of plugin option in order to save individually listed items.
		add_action( 'pre_update_option_' . $this->admin_options_name,      array( $this, 'save_individual_options' ), 10, 2 );
		// Hook sanitized option names retrieval to insert settings for individual items.
		add_filter( $this->get_hook( 'sanitized_option_names' ),           array( $this, 'permit_individual_option_names' ), 10, 2 );
	}

	/**
	 * Returns translated strings used by c2c_Plugin parent class.
	 *
	 * @since 4.4
	 *
	 * @param string $string Optional. The string whose translation should be
	 *                       returned, or an empty string to return all strings.
	 *                       Default ''.
	 * @return string|string[] The translated string, or if a string was provided
	 *                         but a translation was not found then the original
	 *                         string, or an array of all strings if $string is ''.
	 */
	public function get_c2c_string( $string = '' ) {
		$strings = array(
			'A value is required for: "%s"'
				/* translators: %s: Label for setting. */
				=> __( 'A value is required for: "%s"', 'custom-post-limits' ),
			'Click for more help on this plugin'
				=> __( 'Click for more help on this plugin', 'custom-post-limits' ),
			' (especially check out the "Other Notes" tab, if present)'
				=> __( ' (especially check out the "Other Notes" tab, if present)', 'custom-post-limits' ),
			'Coffee fuels my coding.'
				=> __( 'Coffee fuels my coding.', 'custom-post-limits' ),
			'Did you find this plugin useful?'
				=> __( 'Did you find this plugin useful?', 'custom-post-limits' ),
			'Donate'
				=> __( 'Donate', 'custom-post-limits' ),
			'Expected integer value for: %s'
				=> __( 'Expected integer value for: %s', 'custom-post-limits' ),
			'Invalid file specified for C2C_Plugin: %s'
				/* translators: %s: Path to the plugin file. */
				=> __( 'Invalid file specified for C2C_Plugin: %s', 'custom-post-limits' ),
			'More information about %1$s %2$s'
				/* translators: 1: plugin name 2: plugin version */
				=> __( 'More information about %1$s %2$s', 'custom-post-limits' ),
			'More Help'
				=> __( 'More Help', 'custom-post-limits' ),
			'More Plugin Help'
				=> __( 'More Plugin Help', 'custom-post-limits' ),
			'Please consider a donation'
				=> __( 'Please consider a donation', 'custom-post-limits' ),
			'Reset Settings'
				=> __( 'Reset Settings', 'custom-post-limits' ),
			'Save Changes'
				=> __( 'Save Changes', 'custom-post-limits' ),
			'See the "Help" link to the top-right of the page for more help.'
				=> __( 'See the "Help" link to the top-right of the page for more help.', 'custom-post-limits' ),
			'Settings'
				=> __( 'Settings', 'custom-post-limits' ),
			'Settings reset.'
				=> __( 'Settings reset.', 'custom-post-limits' ),
			'Something went wrong.'
				=> __( 'Something went wrong.', 'custom-post-limits' ),
			'The plugin author homepage.'
				=> __( 'The plugin author homepage.', 'custom-post-limits' ),
			"The plugin configuration option '%s' must be supplied."
				/* translators: %s: The setting configuration key name. */
				=>__( "The plugin configuration option '%s' must be supplied.", 'custom-post-limits' ),
			'This plugin brought to you by %s.'
				/* translators: %s: Link to plugin author's homepage. */
				=> __( 'This plugin brought to you by %s.', 'custom-post-limits' ),
		);

		if ( ! $string ) {
			return array_values( $strings );
		}

		return ! empty( $strings[ $string ] ) ? $strings[ $string ] : $string;
	}

	/**
	 * Resets caches and memoized data.
	 *
	 * @since 4.0
	 */
	public function reset_caches() {
		parent::reset_caches();

		$this->authors           = array();
		$this->categories        = array();
		$this->tags              = array();
		$this->first_page_offset = null;
		self::$individual_limits = array( 'all' => null, 'authors' => null, 'categories' => null, 'tags' => null );
	}

	/**
	 * Returns the name for the limit setting of a given individual item associated
	 * with a given type.
	 *
	 * Note: Does not verify if the given item is valid for given type or that
	 * the setting exists. It merely returns what the setting should be for the
	 * given type and value combination.
	 *
	 * @since 4.0
	 *
	 * @param string $type The type of setting. One of: authors, categories, customposttypes, tags.
	 * @param string $item The id for the item, or in the case for a custom post type, the slug.
	 * @return string
	 */
	public static function get_individual_limit_setting_name( $type, $value ) {
		if ( ! $value ) {
			return '';
		}

		if ( self::has_individual_limits( $type ) ) {
			$prefix = $type;
		} else {
			$prefix = '';
		}

		return $prefix ? "{$prefix}_{$value}_limit" : '';
	}

	/**
	 * Recognizes post type registration so that the post limits setting for the
	 * post type gets created.
	 *
	 * @since 4.0
	 *
	 * @param string $post_type The post type.
	 * @param array  $args      The post type configuration array.
	 */
	public function registered_post_type( $post_type, $args ) {
		$setting = self::get_individual_limit_setting_name( 'customposttypes', $post_type );

		if ( $args->has_archive && ! isset( $this->config[ $setting ] ) ) {
			$post_type_label = $args->labels->name !== 'Posts' ? $args->label : ucwords( str_replace( '-', ' ', $post_type ) );

			$this->add_option( $setting, array(
				'input'    => 'short_text',
				'datatype' => 'int',
				'label'    => sprintf( __( 'Custom Post Type Limit: %s', 'custom-post-limits' ), $post_type_label ),
			) );
		}
	}

	/**
	 * Fires after a taxonomy is registered.
	 *
	 * @since 4.1
	 *
	 * @param string       $taxonomy    Taxonomy slug.
	 * @param array|string $object_type Object type or array of object types.
	 * @param array        $args        Array of taxonomy registration arguments.
	 */
	public function registered_taxonomy( $taxonomy, $object_type, $args ) {
		// Skip core taxonomies, which are handled specially.
		if ( in_array( $taxonomy, array( 'category', 'post_format', 'post_tag' ) ) ) {
			return;
		}

		$setting = self::get_individual_limit_setting_name( 'customtaxonomies', $taxonomy );
		if ( $args['publicly_queryable'] && ! isset( $this->config[ $setting ] ) ) {
			$taxonomy_label = $args['labels']->name !== 'Posts' ? $args['label'] : ucwords( str_replace( '-', ' ', $post_type ) );

			$this->add_option( $setting, array(
				'input'    => 'short_text',
				'datatype' => 'int',
				'label'    => sprintf( __( 'Custom Taxonomy Limit: %s', 'custom-post-limits' ), $taxonomy_label ),
			) );
		}
	}

	/**
	 * Outputs the text above the setting form.
	 *
	 * @param string $localized_heading_text Optional. Localized page heading text.
	 */
	public function options_page_description( $localized_heading_text = '' ) {
		$options = $this->get_options();
		$current_limit = get_option( 'posts_per_page' );
		$option_url = '<a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '">' . __( 'here', 'custom-post-limits' ) . '</a>';

		parent::options_page_description( __( 'Custom Post Limits Settings', 'custom-post-limits' ) );
		echo '<p>';
		_e( 'By default, WordPress provides a single configuration setting to control how many posts should be listed on your blog.  This value applies for the front page listing, archive listings, author listings, category listings, tag listings, and search results.  <strong>Custom Post Limits</strong> allows you to override that value for each of those different sections.', 'custom-post-limits' );
		echo "</p>\n<p>";
		_e( 'If the limit field is empty or 0 for a particular section type, then the default post limit will apply. If the value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown). For instance, you could set your Front Page to list 5 posts, but then list 10 on subsequent pages.', 'custom-post-limits' );
		echo "</p>\n<p>";
		_e( 'All but the individual archive limits support a "paged (non first page)" sub-setting that allows a different limit to apply for that listing type when not viewing the first page of the listing  (i.e. when on page 2 or later).', 'custom-post-limits' );
		echo "</p>\n<p>";
		printf(
			/* translators: 1: integer value representing the configured post limit, 2: link to the Settings -> Reading page */
			__( 'The default post limit as set in your settings is <strong>%1$d</strong>.  You can change this value %2$s, which is labeled as <em>Blog pages show at most</em>', 'custom-post-limits' ),
			$current_limit,
			$option_url
		);
		echo "</p>\n";
	}

	/**
	 * Indicates if the individual limits are enabled for the given archive type.
	 *
	 * @since 3.6
	 *
	 * @param string $type One of: authors, categories, customposttypes, customtaxonomies, or tags.
	 * @return bool  True if the individual limits are enabled for the given archive type; false if not
	 */
	public function is_individual_limits_enabled( $type ) {
		$options = $this->get_options();

		// Custom post types and custom taxonomies never have individual limits enabled.
		if ( in_array( $type, array ( 'customposttypes', 'customtaxonomies' ) ) ) {
			return false;
		}

		if ( ! self::has_individual_limits( $type ) ) {
			return false;
		}

		if ( ! empty( self::$individual_limits['all'] ) ) {
			/**
			 * Filters if inidividual limits are enabled for all archive types.
			 *
			 * The ability to set individual limits (e.g. for per-author or per-category
			 * archives) isn't simply enabled by default because it can have a negative
			 * performance impact depending on the number of items. Especially for
			 * something most sites are unlikely to need.
			 *
			 * @since 3.6
			 *
			 * @param bool $enabled Enable individual limits for all archive types?
			 *                      Default false.
			 */
			self::$individual_limits['all'] = (bool) apply_filters( 'c2c_cpl_enable_all_individual_limits', false );
		}

		if ( self::$individual_limits['all'] ) {
			return true;
		}

		if ( empty( self::$individual_limits[ $type ] ) ) {
			/**
			 * Filters if individual limits are enabled for a specific archive type.
			 *
			 * The dynamic portion of the hook name, `$type`, refers to the type of
			 * archive with constituent individual archives. Can be 'authors',
			 * 'categories', or 'tags'.
			 *
			 * The ability to set individual limits (e.g. for per-author or per-category
			 * archives) isn't simply enabled by default because it can have a negative
			 * performance impact depending on the number of items. Especially for
			 * something most sites are unlikely to need.
			 *
			 * @since 3.6
			 *
			 * @param bool $enabled Enable individual limits for given archive type?
			 *                      Default false.
			 */
			self::$individual_limits[ $type ] = (bool) apply_filters( "c2c_cpl_enable_all_individual_{$type}_limits", $options[ "enable_individual_{$type}_limit" ] );
		}

		return self::$individual_limits[ $type ];
	}

	/**
	 * Indicates if the given option type supports individual limits.
	 *
	 * Note: This does not determine if the individual limits are enabled or that
	 * any have any explicit limits defined.
	 *
	 * @since 4.0
	 *
	 * @param string $type The option type. One of authors, categories, customposttypes, tags.
	 * @return bool  True if the option type supports individual limits, false otherwise.
	 */
	public static function has_individual_limits( $type ) {
		return $type && in_array( $type, array( 'authors', 'categories', 'customposttypes', 'customtaxonomies', 'tags' ) );
	}

	/**
	 * Returns an array of limits for individual authors, categories, and/or tags.
	 *
	 * @param array  $primary_options The plugin's primary array of options
	 * @param array  $type            Optional. Array containing the types of individual limits to return.  Can be any of: 'authors', 'categories', 'tags'.
	 *                                Default is an empty array, which returns the limits for all types.
	 * @return array Array of primary limits amended with limits for specified types.
	 */
	public function load_individual_options( $primary_options, $type = array() ) {
		$options = array();

		if ( ( ! $type || in_array( 'authors', (array) $type ) ) && $this->is_individual_limits_enabled( 'authors' ) ) {
			foreach ( $this->get_authors() as $author ) {
				$options[ self::get_individual_limit_setting_name( 'authors', $author->ID ) ] = '';
			}
		}

		if ( ( ! $type || in_array( 'categories', (array) $type ) ) && $this->is_individual_limits_enabled( 'categories' ) ) {
			foreach ( $this->get_categories() as $cat ) {
				$options[ self::get_individual_limit_setting_name( 'categories', $cat->cat_ID ) ] = '';
			}
		}

		if ( ( ! $type || in_array( 'tags', (array) $type ) ) && $this->is_individual_limits_enabled( 'tags' ) ) {
			foreach ( $this->get_tags() as $tag ) {
				$options[ self::get_individual_limit_setting_name( 'tags', $tag->term_id ) ] = '';
			}
		}

		return array_merge( $options, $primary_options );
	}

	/**
	 * Prevents individual option setting names from being sanitized since they
	 * aren't all explicitly registerd as settings.
	 *
	 * @since 4.0
	 *
	 * @param array $option_names The registered option names.
	 * @param array $inputs       The options and their values attempting to get saved.
	 * @return array
	 */
	public function permit_individual_option_names( $option_names, $inputs ) {
		foreach ( array_keys( $inputs ) as $input ) {
			if ( in_array( $input, $option_names ) ) {
				continue;
			}
			$parts = explode( '_', $input, 3 );
			if ( 3 === count( $parts ) && self::has_individual_limits( $parts[0] ) && 'limit' === $parts[2] ) {
				$option_names[] = $input;

				$this->add_option( $input, array(
					'input'    => 'short_text',
					'datatype' => 'int',
				) );
			}
		}

		return $option_names;
	}

	/**
	 * Displays related individual item limits fields.
	 *
	 * @param string $opt The option just displayed.
	 */
	public function display_individual_option( $opt ) {
		$options = $this->get_options();
		$parts   = explode( '_', $opt );
		$type    = $parts[0];
		$id      = $parts[1];

		if ( ( 'paged' == $id ) ||
			 ( 'categories' == $type && ! $this->get_categories() ) ||
			 ( 'tags'       == $type && ! $this->get_tags() ) ||
			 ( 'authors'    == $type && ! $this->get_authors() )
		) {
				return;
		}

		$before = "<tr valign='top' class='cpl-$type'><th scope='row'> &nbsp; &nbsp; &#8212; ";

		if ( 'categories' == $type && $this->is_individual_limits_enabled( 'categories' ) ) {
			foreach ( $this->get_categories() as $cat ) {
				$idx = self::get_individual_limit_setting_name( 'categories', $cat->cat_ID );
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[ $idx ] ) ? $options[ $idx ] : '';
				echo $before . get_cat_name( $cat->cat_ID ) . '</th>';
				echo "<td><input type='text' class='c2c_short_text small-text' name='$index' value='$value' /></td></tr>";
			}
		} elseif ( 'tags' == $type && $this->is_individual_limits_enabled( 'tags' ) ) {
			foreach ( $this->get_tags() as $tag ) {
				$idx = self::get_individual_limit_setting_name( 'tags', $tag->term_id );
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[ $idx ] ) ? $options[ $idx ] : '';
				echo $before . $tag->name . '</th>';
				echo "<td><input type='text' class='c2c_short_text small-text' name='$index' value='$value' /></td></tr>";
			}
		} elseif ( 'authors' == $type && $this->is_individual_limits_enabled( 'authors' ) ) {
			foreach ( $this->get_authors() as $author ) {
				$idx = self::get_individual_limit_setting_name( 'authors', $author->ID );
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[ $idx ] ) ? $options[ $idx ] : '';
				echo $before . $author->display_name . '</th>';
				echo "<td><input type='text' class='c2c_short_text small-text' name='$index' value='$value' /></td></tr>";
			}
		}
	}

	/**
	 * Saves individual limits.
	 *
	 * This is done specially because individual items (category, tag, author)
	 * aren't specifically registered via add_settings_field() so they have to
	 * be captured and stored into the settings array before the setting gets
	 * updated. Don't worry; the data is sanitized.
	 *
	 * @param array  $newvalue The value of the setting with current changes.
	 * @param array  $oldvalue The old value of the setting (before the current save).
	 * @return array The $newvalue array potentially amended with individual item limits.
	 */
	public function save_individual_options( $newvalue, $oldvalue ) {
		if ( isset( $_POST[ $this->admin_options_name ] ) ) {
			foreach ( $_POST[ $this->admin_options_name ] as $key => $val ) {
				if ( strpos( $key, 'categories_' ) === 0 || strpos( $key, 'tags_' ) === 0 || strpos( $key, 'authors_') === 0 ) {
					if ( ! $val ) { // Allow empty value; otherwise it must be an int
						unset( $newvalue[ $key ] );
					} else {
						$newvalue[ $key ] = intval( $val );
					}
				}
			}
		}

		return $newvalue;
	}

	/**
	 * Outputs dynamically generated help text for settings fields.
	 *
	 * @param string $helptext The original help text.
	 * @param string $opt      The option name.
	 * @return string
	 */
	public function dynamic_option_help( $helptext, $opt ) {
		$options = $this->get_options();
		$current_limit = get_option( 'posts_per_page' );
		$parts = explode( '_', $opt );

		if ( count( $parts ) < 2 || 'enable' == $parts[0] || intval( $parts[1] ) > 0 ) {
			return $helptext;
		}

		$opt_name = implode(' ', array_map( 'ucfirst', $parts ) );
		$opt_value = $options[ $opt ];
		$is_archive = in_array( $opt, array( 'day_archives_limit', 'month_archives_limit', 'year_archives_limit' ) );
		$is_paged_archive = in_array( $opt, array( 'day_archives_paged_limit', 'month_archives_paged_limit', 'year_archives_paged_limit' ) );

		$echo = '';

		if ( ! $opt_value ) {
			if ( $is_archive && $options['archives_limit'] ) {
				$echo .= sprintf( __( 'Archives Limit of %s is being used.', 'custom-post-limits' ), $options['archives_limit'] );
			} elseif ( $is_paged_archive && $options[ $parts[0] . '_archives_limit' ] ) {
				switch ( $opt ) {
					case 'day_archives_paged_limit':
						$echo .= sprintf( __( 'Day Archives Limit of %s is being used.', 'custom-post-limits' ), $options['day_archives_limit'] );
						break;
					case 'month_archives_paged_limit':
						$echo .= sprintf( __( 'Month Archives Limit of %s is being used.', 'custom-post-limits' ), $options['month_archives_limit'] );
						break;
					case 'year_archives_paged_limit':
						$echo .= sprintf( __( 'Year Archives Limit of %s is being used.', 'custom-post-limits' ), $options['year_archives_limit'] );
						break;
				}
			} elseif ( $is_paged_archive && $options['archives_paged_limit'] ) {
				$echo .= sprintf( __( 'Archives Paged Limit of %s is being used.', 'custom-post-limits' ), $options['archives_paged_limit'] );
			} elseif ( 'paged' == $parts[1] && $options[ $parts[0] . '_limit' ] ) {
				switch ( $opt ) {
					case 'authors_paged_limit':
						$echo .= sprintf( __( 'Authors Limit of %s is being used.', 'custom-post-limits' ), $options['authors_limit'] );
						break;
					case 'categories_paged_limit':
						$echo .= sprintf( __( 'Categories Limit of %s is being used.', 'custom-post-limits' ), $options['categories_limit'] );
						break;
					case 'searches_paged_limit':
						$echo .= sprintf( __( 'Seaches Limit of %s is being used.', 'custom-post-limits' ), $options['searches_limit'] );
						break;
					case 'tags_paged_limit':
						$echo .= sprintf( __( 'Tags Limit of %s is being used.', 'custom-post-limits' ), $options['tags_limit'] );
						break;
				}
			} elseif ( 'front_page_paged_limit' == $opt && $options['front_page_limit'] ) {
				$echo .= sprintf( __( 'Front Page Limit of %s is being used.', 'custom-post-limits' ), $options['front_page_limit'] );
			} else {
				$echo .= sprintf( __( 'The WordPress default of %d is being used.', 'custom-post-limits' ), $current_limit );
			}
		} elseif ( '-1' == $opt_value ) {
			$echo .= __( '(<strong>ALL</strong> posts are set to be displayed for this)', 'custom-post-limits' );
		}

		$opt_parts = explode( ' ', $opt_name );
		$type = strtolower( reset( $opt_parts ) );

		if ( self::has_individual_limits( $type ) && $this->is_individual_limits_enabled( $type ) && ( false === strpos( $opt, '_paged_limit' ) ) && count( $this->$type ) > 0 ) {
			$echo .= " &#8211; <a id='cpl-{$type}-link' href='#' style='display:none;'>" . sprintf( __( 'Show/hide individual %s', 'custom-post-limits' ), strtolower( $opt_name ) ) . '</a>';
		}

		if ( $is_archive ) {
			$echo .= '<p class="description">' . __( 'If not defined, it assumes the value of Archives Limit.', 'custom-post-limits' ) . "</p>\n";
		} elseif ( 'archives_limit' == $opt ) {
			$echo .= '<p class="description">' . __( 'This is the default for Day, Month, and Year archives, unless those are defined explicitly below.', 'custom-post-limits' ) . "</p>\n";
		}

		if ( $echo ) {
			echo "<span class='c2c-input-help description'>$echo</span>";
		}

		return $helptext;
	}

	/**
	 * Outputs the JavaScript used by the plugin, within script tags.
	 */
	public function add_plugin_admin_js() {
		echo <<<JS
		<script>
			jQuery(document).ready(function($) {
				$('#cpl-categories-link').click(function() { $(".cpl-categories").toggle(); return false; });
				$('#cpl-tags-link').click(function() { $(".cpl-tags").toggle(); return false; });
				$('#cpl-authors-link').click(function() { $(".cpl-authors").toggle(); return false; });

				$(".cpl-authors, .cpl-categories, .cpl-tags, #cpl-authors-link, #cpl-categories-link, #cpl-tags-link").toggle()
			});
		</script>

JS;
	}

	/**
	 * Returns a potentially overridden limit value for the currently queried posts.
	 *
	 * The `$forced_paged` argument is intended for internal use so function behaves
	 * as if the current request was paged.
	 *
	 * @since 4.3 Added `$forced_paged` argumentn.
	 *
	 * @param int  $limit The default limit value for the current posts query.
	 * @param bool $forced_paged Optional. Calculate the post limits as if query
	 *                           was paged. For internal use. Default false.
	 * @return int The limit value for the current posts query.
	 */
	public function custom_post_limits( $limit, $force_paged = false ) {
		if ( is_admin() ) {
			return $limit;
		}

		$old_limit = $limit;
		$options = $this->get_options();
		$this->first_page_offset = null;

		$is_paged = $force_paged || is_paged();

		if ( is_home() ) {
			if ( $is_paged && ! empty( $options['front_page_paged_limit'] ) ) {
				$limit = $options['front_page_paged_limit'];
				$this->first_page_offset = $options['front_page_limit'];
			} else {
				$limit = $options['front_page_limit'];
			}
		} elseif ( is_search() ) {
			if ( $is_paged && ! empty( $options['searches_paged_limit'] ) ) {
				$limit = $options['searches_paged_limit'];
				$this->first_page_offset = $options['searches_limit'];
			} else {
				$limit = $options['searches_limit'];
			}
		} elseif ( is_category() ) {
			if ( $is_paged && ! empty( $options['categories_paged_limit'] ) ) {
				$limit = $options['categories_paged_limit'];
				$this->first_page_offset = $options['categories_limit'];
			} else {
				$limit = $options['categories_limit'];
			}

			if ( $this->is_individual_limits_enabled( 'categories' ) ) {
				if ( ! $cat_id = get_query_var( 'cat' ) ) {
					if ( $cat_name = get_query_var( 'category_name' ) ) {
						if ( $cat = get_category_by_slug( $cat_name ) ) {
							$cat_id = $cat->term_id;
						}
					}
				}

				$opt = self::get_individual_limit_setting_name( 'categories', $cat_id );
				if ( $opt && ! empty( $options[ $opt ] ) ) {
					$limit = $options[ $opt ];
// TODO: Individual archive limits apply to all pagings; consider doing front-page/non-front-page values for each
$this->first_page_offset = null;
				}
			}
		} elseif ( is_tag() ) {
			if ( $is_paged && ! empty( $options['tags_paged_limit'] ) ) {
				$limit = $options['tags_paged_limit'];
				$this->first_page_offset = $options['tags_limit'];
			} else {
				$limit = $options['tags_limit'];
			}

			if ( $this->is_individual_limits_enabled( 'tags' ) ) {
				if ( ! $tag_id = get_query_var( 'tag_id' ) ) {
					if ( $tag_name = get_query_var( 'tag' ) ) {
						if ( $tag = get_term_by( 'slug', $tag_name, 'post_tag' ) ) {
							$tag_id = $tag->term_id;
						}
					}
				}

				$opt = self::get_individual_limit_setting_name( 'tags', $tag_id );
				if ( $opt && ! empty( $options[ $opt ] ) ) {
					$limit = $options[ $opt ];
// TODO: Individual archive limits apply to all pagings; consider doing front-page/non-front-page values for each
$this->first_page_offset = null;
				}
			}
		} elseif ( is_tax() && isset( get_queried_object()->taxonomy ) ) {
			$custom_taxonomy_setting = self::get_individual_limit_setting_name( 'customtaxonomies', get_queried_object()->taxonomy );
			if ( isset( $options[ $custom_taxonomy_setting ] ) ) {
				$limit = $options[ $custom_taxonomy_setting ];
			}
		} elseif ( is_author() ) {
			if ( $is_paged && ! empty( $options['authors_paged_limit'] ) ) {
				$limit = $options['authors_paged_limit'];
				$this->first_page_offset = $options['authors_limit'];
			} else {
				$limit = $options['authors_limit'];
			}

			if ( $this->is_individual_limits_enabled( 'authors' ) ) {
				if ( ! $author_id = get_query_var( 'author' ) ) {
					if ( $author_name = get_query_var( 'author_name' ) ) {
						if ( $author = get_user_by( 'slug', $author_name ) ) {
							$author_id = $author->ID;
						}
					}
				}

				$opt = self::get_individual_limit_setting_name( 'authors', $author_id );
				if ( $opt && ! empty( $options[ $opt ] ) ) {
					$limit = $options[ $opt ];
// TODO: Individual archive limits apply to all pagings; consider doing front-page/non-front-page values for each
$this->first_page_offset = null;
				}
			}
		} elseif ( is_year() ) {
			// Order of precedence:
			// If paged:
			// * year_archives_paged_limit
			// * year_archives_limit
			// * archives_page_limit
			// * archives_limit
			// Non-paged:
			// * year_archives_limit
			// * archives_limit
			$front_limit = $options['year_archives_limit'] ? $options['year_archives_limit'] : $options['archives_limit'];
			if ( $is_paged ) {
				if ( $options['year_archives_paged_limit'] ) {
					$limit = $options['year_archives_paged_limit'];
				} elseif ( $options['year_archives_limit'] ) {
					$limit = $options['year_archives_limit'];
				} elseif ( $options['archives_paged_limit'] ) {
					$limit = $options['archives_paged_limit'];
				} else {
					$limit = $front_limit;
				}

				if ( $limit != $front_limit ) {
					$this->first_page_offset = $front_limit;
				}
			}
			// Otherwise, subsequent pages have same limit as first page.
			else {
				$limit = $front_limit;
			}
		} elseif ( is_month() ) {
			// Order of precedence:
			// If paged:
			// * month_archives_paged_limit
			// * month_archives_limit
			// * archives_page_limit
			// * archives_limit
			// Non-paged:
			// * month_archives_limit
			// * archives_limit
			$front_limit = $options['month_archives_limit'] ? $options['month_archives_limit'] : $options['archives_limit'];
			if ( $is_paged ) {
				if ( $options['month_archives_paged_limit'] ) {
					$limit = $options['month_archives_paged_limit'];
				} elseif ( $options['month_archives_limit'] ) {
					$limit = $options['month_archives_limit'];
				} elseif ( $options['archives_paged_limit'] ) {
					$limit = $options['archives_paged_limit'];
				} else {
					$limit = $front_limit;
				}

				if ( $limit != $front_limit ) {
					$this->first_page_offset = $front_limit;
				}
			}
			// Otherwise, subsequent pages have same limit as first page.
			else {
				$limit = $front_limit;
			}
		} elseif ( is_day() ) {
			// Order of precedence:
			// If paged:
			// * day_archives_paged_limit
			// * day_archives_limit
			// * archives_page_limit
			// * archives_limit
			// Non-paged:
			// * day_archives_limit
			// * archives_limit
			$front_limit = $options['day_archives_limit'] ? $options['day_archives_limit'] : $options['archives_limit'];
			if ( $is_paged ) {
				if ( $options['day_archives_paged_limit'] ) {
					$limit = $options['day_archives_paged_limit'];
				} elseif ( $options['day_archives_limit'] ) {
					$limit = $options['day_archives_limit'];
				} elseif ( $options['archives_paged_limit'] ) {
					$limit = $options['archives_paged_limit'];
				} else {
					$limit = $front_limit;
				}

				if ( $limit != $front_limit ) {
					$this->first_page_offset = $front_limit;
				}
			}
			// Otherwise, subsequent pages have same limit as first page.
			else {
				$limit = $front_limit;
			}
		} elseif ( is_post_type_archive() ) {
			$post_type_setting = self::get_individual_limit_setting_name( 'customposttypes', get_query_var( 'post_type' ) );

			if ( isset( $options[ $post_type_setting ] ) ) {
				$limit = $options[ $post_type_setting ];
			}
		} elseif ( is_archive() ) {
			if ( $is_paged && ! empty( $options['archives_paged_limit'] ) ) {
				$limit = $options['archives_paged_limit'];
				$this->first_page_offset = $options['archives_limit'];
			} else {
				$limit = $options['archives_limit'];
			}
		}

		if ( ! $limit ) {
			$limit = $old_limit;
		} elseif ( '-1' == $limit ) {
			// Hacky magic number, but it's what the MySQL docs suggest!
			$limit = '18446744073709551615';
		}

		// If simulating paging, ensure first_page_offset has been set.
		if ( $force_paged && ! $this->first_page_offset ) {
			$this->first_page_offset = $limit;
		}

		return $limit;
	}

	/**
	 * Possibly modifies the offset value for LIMIT query clause.
	 *
	 * @since 3.5
	 *
	 * @param string  $limit     The SQL LIMIT clause
	 * @param object  $query_obj The WP_Query object performing the query
	 * @return string The potentially modified LIMIT clause
	 */
	public function correct_paged_offset( $limit, $query_obj ) {
		// Only intercede if on the main query.
		if ( ! $query_obj->is_main_query() ) {
			return $limit;
		}

		// Shorthand.
		$q = $query_obj->query_vars;
		if ( $this->first_page_offset && empty( $q['offset'] ) ) {
			$parts = explode( ' ', $limit );
			if ( count( $parts ) == 3 ) {
				$page = absint( $q['paged'] );
				if ( $page == 0 ) {
					$page = 1;
				}
				$new_offset = ( $page - 2 ) * $q['posts_per_page'] + $this->first_page_offset;
//				$limit = implode( ' ', array( $parts[0], $new_offset . ',', $parts[2] ) );
				$limit = "LIMIT {$parts[2]} OFFSET $new_offset";
			}
		}

		return $limit;
	}

	/**
	 * Overrides the query's determination of max_num_pages if queried view has
	 * different first page and non-first page limits.
	 *
	 * @since 4.3
	 *
	 * @param WP_Posts[] $posts    Queried posts.
	 * @param WP_Query   $wp_query Query object.
	 */
	public function adjust_max_num_pages( $posts, $wp_query ) {
		$new_max_num_pages = null;

		// Bail if not the main query or no posts have been found.
		if ( ! $wp_query->is_main_query() || ! $wp_query->found_posts ) {
			return $posts;
		}

		// Store actual first_page_offset value since the call to custom_post_limits()
		// could change its value, which shouldn't matter since this happens after it
		// is legitimately needed, but don't risk it.
		$orig_first_page_offset = $this->first_page_offset;

		// Get the limits by forcing a paged context.
		$non_first_page_limit = $this->custom_post_limits( -2, true );

		// Save the first page limit.
		$first_page_limit = $this->first_page_offset ? $this->first_page_offset : 0;

		// Restore original first page offset.
		$this->first_page_offset = $orig_first_page_offset;

		// Bail if query context doesn't involve custom limits.
		if ( -2 === $non_first_page_limit ) {
			return $posts;
		}

		// Calculate number of pages.
		$total_posts = (int) $wp_query->found_posts;
		$non_first_page_posts = $total_posts - $first_page_limit;
		$paged_pages = $non_first_page_posts > 0 ? ceil( $non_first_page_posts / $non_first_page_limit ) : 0;
		$new_max_num_pages = 1 + $paged_pages;

		// Override max_num_pages value.
		$wp_query->max_num_pages = (int) $new_max_num_pages;

		return $posts;
	}

	/**
	 * Returns either the buffered array of all authors, or obtains the authors
	 * and buffers the value.
	 *
	 * @return array|true Array of authors; authors without posts are included.
	 *                    True if individual limits for categories are disabled.
	 */
	public function get_authors() {
		if ( ! $this->authors ) {
			$this->authors = $this->is_individual_limits_enabled( 'authors' )
				? get_users( array( 'fields' => array( 'ID', 'display_name', 'user_nicename' ), 'order' => 'display_name', 'who' => 'authors' ) )
				: true;
		}

		return $this->authors;
	}

	/**
	 * Returns either the buffered array of all categories, or obtains the
	 * categories and buffers the value.
	 *
	 * @return array|true Array of categories; categories without posts are
	 *                    included. True if individual limits for categories
	 *                    are disabled.
	 */
	public function get_categories() {
		if ( ! $this->categories ) {
			$this->categories = $this->is_individual_limits_enabled( 'categories' )
				? get_categories( array( 'hide_empty' => false ) )
				: true;
		}

		return $this->categories;
	}

	/**
	 * Returns either the buffered array of all tags, or obtains the tags and
	 * buffers the value.
	 *
	 * @return array|true Array of tags; tags without posts are included. True
	 *                     if individual limits for tags are disabled.
	 */
	public function get_tags() {
		if ( ! $this->tags ) {
			$this->tags = $this->is_individual_limits_enabled( 'tags' )
				? get_tags( array( 'hide_empty' => false ) )
				: true;
		}

		return $this->tags;
	}

} // end c2c_CustomPostLimits

add_action( 'plugins_loaded', array( 'c2c_CustomPostLimits', 'get_instance' ) );

endif; // end if !class_exists()
