<?php
/**
 * @package Custom_Post_Limits
 * @author Scott Reilly
 * @version 3.6
 */
/*
Plugin Name: Custom Post Limits
Version: 3.6
Plugin URI: http://coffee2code.com/wp-plugins/custom-post-limits/
Author: Scott Reilly
Author URI: http://coffee2code.com/
Text Domain: custom-post-limits
Domain Path: /lang/
Description: Independently control the number of posts listed on the front page, author/category/tag archives, search results, etc.

Compatible with WordPress 3.1+, 3.2+, 3.3+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/custom-post-limits/

TODO
	* Extract post limit determination logic from custom_post_limits() into get_custom_limit( $type, $specific = null)
	  (where specific can be a number to indicate a particular cat/tag/author or 'paged')

*/

/*
Copyright (c) 2008-2012 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if ( ! class_exists( 'c2c_CustomPostLimits' ) ) :

require_once( 'c2c-plugin.php' );

class c2c_CustomPostLimits extends C2C_Plugin_034 {

	public static $instance;

	private $authors           = '';
	private $categories        = '';
	private $tags              = '';
	private $first_page_offset = null;

	private static $individual_limits = array( 'all' => null, 'authors' => null, 'categories' => null, 'tags' => null );

	/**
	 * Class constructor: initializes class variables and adds actions and filters.
	 */
	public function __construct() {
		$this->c2c_CustomPostLimits();
	}

	public function c2c_CustomPostLimits() {
		// Be a singleton
		if ( ! is_null( self::$instance ) )
			return;

		parent::__construct( '3.5', 'custom-post-limits', 'c2c', __FILE__, array() );
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
		self::$instance = $this;
	}

	/**
	 * Handles activation tasks, such as registering the uninstall hook.
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function activation() {
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Handles uninstallation tasks, such as deleting plugin options.
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function uninstall() {
		delete_option( 'c2c_custom_post_limits' );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 *
	 * @return void
	 */
	public function load_config() {
		$this->name      = __( 'Custom Post Limits', $this->textdomain );
		$this->menu_name = __( 'Post Limits', $this->textdomain );

		$this->config = array(
			'archives_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Archives Limit', $this->textdomain ) ),
			'archives_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) ),
			'enable_individual_authors_limit' => array( 'input' => 'checkbox', 'default' => false,
					'label' => __( 'Enable individual authors limit?', $this->textdomain ),
					'help' => __( 'Allows you to set limits for specific authors. If enabled, a link will appear after the Authors Limit field.<br /><em>Warning: if you have a lot of authors this may prevent this page from loading</em>.', $this->textdomain ) ),
			'authors_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Authors Limit', $this->textdomain ) ),
			'authors_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) ),
			'enable_individual_categories_limit' => array( 'input' => 'checkbox', 'default' => false,
					'label' => __( 'Enable individual categories limit?', $this->textdomain ),
					'help' => __( 'Allows you to set limits for specific categories. If enabled, a link will appear after the Categories Limit field.<br /><em>Warning: if you have a lot of categories this may prevent this page from loading</em>.', $this->textdomain ) ),
			'categories_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Categories Limit', $this->textdomain ) ),
			'categories_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) ),
			'day_archives_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Day Archives Limits', $this->textdomain ) ),
			'day_archives_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) ),
			'front_page_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Front Page Limit', $this->textdomain ) ),
			'front_page_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) ),
			'month_archives_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Month Archives Limit', $this->textdomain ) ),
			'month_archives_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) ),
			'searches_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Searches Limit', $this->textdomain ) ),
			'searches_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) ),
			'enable_individual_tags_limit' => array( 'input' => 'checkbox', 'default' => false,
					'label' => __( 'Enable individual tags limit?', $this->textdomain ),
					'help' => __( 'Allows you to set limits for specific tags. If enabled, a link will appear after the Tags Limit field.<br /><em>Warning: if you have a lot of tags this may prevent this page from loading</em>.', $this->textdomain ) ),
			'tags_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Tags Limit', $this->textdomain ) ),
			'tags_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) ),
			'year_archives_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Year Archives Limits', $this->textdomain ) ),
			'year_archives_paged_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( ' &nbsp; &nbsp; &#8212; <em>paged (non first page)</em>', $this->textdomain ) )
		);
	}

	/**
	 * Override the plugin framework's register_filters() to register actions and filters.
	 *
	 * @return void
	 */
	public function register_filters() {
		if ( $this->is_plugin_admin_page() ) {
			// Add plugin settings page JS
			add_action( 'admin_print_footer_scripts', array( &$this, 'add_plugin_admin_js' ) );
			// Dynamically add help text to the plugin settings page
			add_filter( $this->get_hook( 'option_help' ), array( &$this, 'dynamic_option_help' ), 10, 2 );
		}

		if ( is_admin() ) {
			add_filter( $this->get_hook( 'options' ), array( &$this, 'load_individual_options' ) );
			// Hook the post-display of each plugin option in order to potentially output dynamically listed individual items
			add_action( $this->get_hook( 'post_display_option' ), array( &$this, 'display_individual_option' ) );
			// Hook post-updating of plugin option in order to save individually listed items
			add_action ( 'pre_update_option_' . $this->admin_options_name, array( &$this, 'save_individual_options' ), 10, 2 );
		} else {
			// Override the default post limits prior to the db being queried
			add_action( 'pre_option_posts_per_page', array( &$this, 'custom_post_limits' ) );
			// Possibly modify the offset within the LIMIT clause
			add_filter( 'post_limits', array( &$this, 'correct_paged_offset' ), 10, 2 );
		}
	}

	/**
	 * Outputs the text above the setting form
	 *
	 * @return void (Text will be echoed.)
	 */
	public function options_page_description() {
		$options = $this->get_options();
		$current_limit = get_option( 'posts_per_page' );
		$option_url = '<a href="' . admin_url( 'options-reading.php' ) . '">' . __( 'here', $this->textdomain ) . '</a>';
		parent::options_page_description( __( 'Custom Post Limits Settings', $this->textdomain ) );
		echo '<p>' . __( 'By default, WordPress provides a single configuration setting to control how many posts should be listed on your blog.  This value applies for the front page listing, archive listings, author listings, category listings, tag listings, and search results.  <strong>Custom Post Limits</strong> allows you to override that value for each of those different sections.', $this->textdomain ) . '</p>';
		echo '<p>' . __( 'If the limit field is empty or 0 for a particular section type, then the default post limit will apply. If the value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown). For instance, you could set your Front Page to list 5 posts, but then list 10 on subsequent pages.', $this->textdomain ) . '</p>';
		echo '<p>' . __( 'All but the individual archive limits support a "paged (non first page)" sub-setting that allows a different limit to apply for that listing type when not viewing the first page of the listing  (i.e. when on page 2 or later).', $this->textdomain ) . '</p>';
		echo '<p>' . sprintf( __( 'The default post limit as set in your settings is <strong>%1$d</strong>.  You can change this value %2$s, which is labeled as <em>Blog pages show at most</em>', $this->textdomain ), $current_limit, $option_url ) . '</p>';
	}

	/**
	 * Indicates if the individual limits are enabled for the given archive type.
	 *
	 * @since 3.6
	 *
	 * @param string $type One of: author, category, or tag
	 * @return bool True if the individual limits are enabled for the given archive type; false if not
	 */
	public function is_individual_limits_enabled( $type ) {
		$options = $this->get_options();

		if ( ! isset( self::$individual_limits['all'] ) || is_null( self::$individual_limits['all'] ) )
			self::$individual_limits['all'] = apply_filters( 'c2c_cpl_enable_all_individual_limits', false );

		if ( self::$individual_limits['all'] )
			return true;

		if ( ! isset( self::$individual_limits[$type] ) || is_null( self::$individual_limits[$type] ) )
			self::$individual_limits[$type] = apply_filters( "c2c_cpl_enable_all_individual_{$type}_limits", $options["enable_individual_{$type}_limit"] );

		return self::$individual_limits[$type];
	}

	/**
	 * Returns an array of limits for individual authors, categories, and/or tags.
	 *
	 * @param array $primary_options The plugin's primary array of options
	 * @param array $type (optional) Array containing the types of individual limits to return.  Can be any of: 'authors', 'categories', 'tags'. Default is an empty array, which returns the limits for all types.
	 * @return array Array of primary limits amended with limits for specified types.
	 */
	public function load_individual_options( $primary_options, $type = array() ) {
		$options = array();
		if ( ( empty( $type ) || in_array( 'authors', (array)$type ) ) && self::is_individual_limits_enabled( 'authors' ) ) {
			$this->get_authors();
			foreach ( (array) $this->authors as $author )
				$options['authors_' . $author->ID . '_limit'] = '';
		}
		if ( ( empty( $type ) || in_array( 'categories', (array)$type ) ) && self::is_individual_limits_enabled( 'categories' ) ) {
			$this->get_categories();
			foreach ( (array) $this->categories as $cat )
				$options['categories_' . $cat->cat_ID . '_limit'] = '';
		}
		if ( ( empty( $type ) || in_array( 'tags', (array)$type ) ) && self::is_individual_limits_enabled( 'tags' ) ) {
			$this->get_tags();
			foreach ( (array) $this->tags as $tag )
				$options['tags_' . $tag->term_id . '_limit'] = '';
		}
		return array_merge( $options, $primary_options );
	}

	/**
	 * Displays related individual item limits fields.
	 *
	 * @param string $opt The option just displayed.
	 * @return return Void
	 */
	public function display_individual_option( $opt ) {
		$options = $this->get_options();
		$parts   = explode( '_', $opt );
		$type    = $parts[0];
		$id      = $parts[1];

		if ( ( $id == 'paged' ) ||
			 ( $type == 'categories' && count( $this->categories ) < 1 ) ||
			 ( $type == 'tags' && count( $this->tags ) < 1 ) ||
			 ( $type == 'authors' && count( $this->authors ) < 1 ) ) {
				return;
		}

		$before = "<tr valign='top' class='cpl-$type'><th scope='row'> &nbsp; &nbsp; &#8212; ";

		if ( $type == 'categories' && self::is_individual_limits_enabled( 'categories' ) ) {
			foreach ( (array) $this->categories as $cat ) {
				$idx = $type . '_' . $cat->cat_ID . '_limit';
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[$idx] ) ? $options[$idx] : '';
				echo $before . get_cat_name( $cat->cat_ID ) . '</th>';
				echo "<td><input type='text' class='c2c_short_text small-text' name='$index' value='$value' /></td></tr>";
			}
		} elseif ( $type == 'tags' && self::is_individual_limits_enabled( 'tags' ) ) {
			foreach ( (array) $this->tags as $tag ) {
				$idx = $type . '_' . $tag->term_id . '_limit';
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[$index] ) ? $options[$index] : '';
				echo $before . $tag->name . '</th>';
				echo "<td><input type='text' class='c2c_short_text small-text' name='$index' value='$value' /></td></tr>";
			}
		} elseif ( $type == 'authors' && self::is_individual_limits_enabled( 'authors' ) ) {
			foreach ( (array) $this->authors as $author ) {
				$idx = $type . '_' . $author->ID . '_limit';
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[$index] ) ? $options[$index] : '';
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
	 * @param array $newvalue The value of the setting with current changes
	 * @param array $oldvalue The old value of the setting (before the current save)
	 * @return array The $newvalue array potentially amended with individual item limits
	 */
	public function save_individual_options( $newvalue, $oldvalue ) {
		if ( isset( $_POST[$this->admin_options_name] ) ) {
			foreach ( $_POST[$this->admin_options_name] as $key => $val ) {
				if ( strpos( $key, 'categories_' ) === 0 || strpos( $key, 'tags_' ) === 0 || strpos( $key, 'authors_') === 0 ) {
					if ( empty( $val ) ) // Allow empty value; otherwise it must be an int
						unset( $newvalue[$key] );
					else
						$newvalue[$key] = intval( $val );
				}
			}
		}
		return $newvalue;
	}

	/**
	 * Outputs dynamically generated help text for settings fields.
	 *
	 * @param string $helptext The original help text
	 * @param string $opt The option name
	 * @return void
	 */
	public function dynamic_option_help( $helptext, $opt ) {
		$options = $this->get_options();
		$current_limit = get_option( 'posts_per_page' );
		$parts = explode( '_', $opt );

		if ( 'paged' == $parts[1] || 'enable' == $parts[0] || ( isset( $parts[2] ) && 'paged' == $parts[2] ) || intval( $parts[1] ) > 0 )
			return $helptext;

		$opt_name = implode(' ', array_map( 'ucfirst', $parts ) );
		$opt_value = $options[$opt];
		$is_archive = in_array( $opt, array( 'day_archives_limit', 'month_archives_limit', 'year_archives_limit' ) );

		$echo = '';

		if ( ! $opt_value ) {
			if ( $is_archive && $options['archives_limit'] )
				$echo .= sprintf( __( '(Archives Limit of %s is being used)', $this->textdomain ), $options['archives_limit'] );
			else
				$echo .= sprintf( __( '(The WordPress default of %d is being used)', $this->textdomain ), $current_limit );
		} elseif ( $opt_value == '-1' ) {
			$echo .= __( '(ALL posts are set to be displayed for this)', $this->textdomain );
		}

		$type = strtolower( array_shift( explode( ' ', $opt_name ) ) );

		if ( in_array( $type, array( 'authors', 'categories', 'tags' ) ) && self::is_individual_limits_enabled( $type ) && count( $this->$type ) > 0 )
			$echo .= " &#8211; <a id='cpl-{$type}-link' href='#' style='display:none;'>" . sprintf( __( 'Show/hide individual %s', $this->textdomain ), strtolower( $opt_name ) ) . '</a>';

		if ( $is_archive )
			$echo .= '<br />' . __( 'If not defined, it assumes the value of Archives Limit.', $this->textdomain );
		elseif ( $opt == 'archives_limit' )
			$echo .= '<br />' . __( 'This is the default for Day, Month, and Year archives, unless those are defined explicitly below.', $this->textdomain );

		if ( ! empty( $echo ) )
			echo "<span class='c2c-input-help'>$echo</span>";

		return $helptext;
	}

	/**
	 * Outputs the JavaScript used by the plugin, within script tags.
	 *
	 * @return void (Text is echoed; nothing is returned)
	 */
	public function add_plugin_admin_js() {
		echo <<<JS
		<script type="text/javascript">
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
	 * @param int $limit The default limit value for the current posts query.
	 * @return int The limit value for the current posts query.
	 */
	public function custom_post_limits( $limit ) {
		global $wp_query; // Only used for individual (author, category, tag) limits

		if ( is_admin() )
			return $limit;

		$old_limit = $limit;
		$options = $this->get_options();
		$query_vars = $wp_query->query_vars;

		if ( is_home() ) {
			if ( is_paged() && ! empty( $options['front_page_paged_limit'] ) ) {
				$limit = $options['front_page_paged_limit'];
				$this->first_page_offset = $options['front_page_limit'];
			} else {
				$limit = $options['front_page_limit'];
			}
		} elseif ( is_search() ) {
			if ( is_paged() && ! empty( $options['searches_paged_limit'] ) ) {
				$limit = $options['searches_paged_limit'];
				$this->first_page_offset = $options['searches_limit'];
			} else {
				$limit = $options['searches_limit'];
			}
		} elseif ( is_category() ) {
			if ( is_paged() && ! empty( $options['categories_paged_limit'] ) ) {
				$limit = $options['categories_paged_limit'];
				$this->first_page_offset = $options['categories_limit'];
			} else {
				$limit = $options['categories_limit'];
			}
			$this->get_categories();
			foreach ( $this->categories as $cat ) {
				$opt = 'categories_' . $cat->cat_ID . '_limit';
				if ( isset( $options[$opt] ) && $options[$opt] &&
					( $query_vars['cat'] == $cat->cat_ID || $query_vars['category_name'] == $cat->slug ||
						preg_match( "/\/{$cat->slug}\/?$/", $query_vars['category_name'] ) ) ) {
					$limit = $options[$opt];
// TODO: Individual archive limits apply to all pagings; consider doing front-page/non-front-page values for each
$this->first_page_offset = null;
					break;
				}
			}
		} elseif ( is_tag() ) {
			if ( is_paged() && ! empty( $options['tags_paged_limit'] ) ) {
				$limit = $options['tags_paged_limit'];
				$this->first_page_offset = $options['tags_limit'];
			} else {
				$limit = $options['tags_limit'];
			}
			$this->get_tags();
			foreach ( $this->tags as $tag ) {
				$opt = 'tags_' . $tag->term_id . '_limit';
				if ( isset( $options[$opt] ) && $options[$opt] &&
					( $query_vars['tag_id'] == $tag->term_id || $query_vars['tag'] == $tag->slug ) ) {
					$limit = $options[$opt];
// TODO: Individual archive limits apply to all pagings; consider doing front-page/non-front-page values for each
$this->first_page_offset = null;
					break;
				}
			}
		} elseif ( is_author() ) {
			if ( is_paged() && ! empty( $options['authors_paged_limit'] ) ) {
				$limit = $options['authors_paged_limit'];
				$this->first_page_offset = $options['authors_limit'];
			} else {
				$limit = $options['authors_limit'];
			}
			$this->get_authors();
			foreach ( $this->authors as $author ) {
				$opt = 'authors_' . $author->ID . '_limit';
				if ( isset( $options[$opt] ) && $options[$opt] &&
					( $query_vars['author'] == $author->ID || $query_vars['author_name'] == $author->user_nicename ) ) {
					$limit = $options[$opt];
// TODO: Individual archive limits apply to all pagings; consider doing front-page/non-front-page values for each
$this->first_page_offset = null;
					break;
				}
			}
		} elseif ( is_year() ) {
			$front_limit = $options['year_archives_limit'] ? $options['year_archives_limit'] : $options['archives_limit'];
			if ( is_paged() && ! empty( $options['year_archives_paged_limit'] ) ) {
				$limit = $options['year_archives_paged_limit'];
				$this->first_page_offset = $front_limit;
			} else {
				$limit = $front_limit;
			}
		} elseif ( is_month() ) {
			$front_limit = $options['month_archives_limit'] ? $options['month_archives_limit'] : $options['archives_limit'];
			if ( is_paged() && ! empty( $options['month_archives_paged_limit'] ) ) {
				$limit = $options['month_archives_paged_limit'];
				$this->first_page_offset = $front_limit;
			} else {
				$limit = $front_limit;
			}
		} elseif ( is_day() ) {
			$front_limit = $options['day_archives_limit'] ? $options['day_archives_limit'] : $options['archives_limit'];
			if ( is_paged() && ! empty( $options['day_archives_paged_limit'] ) ) {
				$limit = $options['day_archives_paged_limit'];
				$this->first_page_offset = $front_limit;
			} else {
				$limit = $front_limit;
			}
		} elseif ( is_archive() ) {
			if ( is_paged() && ! empty( $options['archives_paged_limit'] ) ) {
				$limit = $options['archives_paged_limit'];
				$this->first_page_offset = $options['archives_limit'];
			} else {
				$limit = $options['archives_limit'];
			}
		}

		if ( ! $limit )
			$limit = $old_limit;
		elseif ( $limit == '-1' )
			$limit = '18446744073709551615';	// Hacky magic number, but it's what the MySQL docs suggest!

		return $limit;
	}

	/**
	 * Possibly modifies the offset value for LIMIT query clause.
	 *
	 * @since 3.5
	 *
	 * @param string $limit The SQL LIMIT clause
	 * @param object $query_obj The WP_Query object performing the query
	 * @return string The potentially modified LIMIT clause
	 */
	public function correct_paged_offset( $limit, $query_obj ) {
		// Only intercede if on the main query. (Once WP3.3+ only, this can simply be is_main_query())
		global $wp_the_query;
		if ( $wp_the_query !== $query_obj )
			return $limit;

		// Shorthand.
		$q = $query_obj->query_vars;
		if ( $this->first_page_offset && empty( $q['offset'] ) ) {
			$parts = explode( ' ', $limit );
			if ( count( $parts ) == 3 ) {
				$page = absint( $q['paged'] );
				if ( $page == 0 )
					$page = 1;
				$new_offset = ( $page - 2 ) * $q['posts_per_page'] + $this->first_page_offset;
//				$limit = implode( ' ', array( $parts[0], $new_offset . ',', $parts[2] ) );
				$limit = "LIMIT {$parts[2]} OFFSET $new_offset";
			}
		}
		return $limit;
	}

	/**
	 * Returns either the buffered array of all authors, or obtains the authors
	 * and buffers the value.
	 *
	 * @return array Array of authors.  Authors without posts are included.
	 */
	public function get_authors() {
		if ( ! $this->authors )
			$this->authors = get_users( array( 'fields' => array( 'ID', 'display_name', 'user_nicename' ), 'order' => 'display_name' ) );

		return $this->authors;
	}

	/**
	 * Returns either the buffered array of all categories, or obtains the
	 * categories and buffers the value.
	 *
	 * @return array Array of categories.  Categories without posts are included.
	 */
	public function get_categories() {
		if ( ! $this->categories )
			$this->categories = get_categories( array( 'hide_empty' => false ) );

		return $this->categories;
	}

	/**
	 * Returns either the buffered array of all tags, or obtains the tags and
	 * buffers the value.
	 *
	 * @return array Array of tags.  Tags without posts are included.
	 */
	public function get_tags() {
		if ( ! $this->tags )
			$this->tags = get_tags( array( 'hide_empty' => false ) );

		return $this->tags;
	}

} // end c2c_CustomPostLimits

// To access plugin object instance use: c2c_CustomPostLimits::$instance
new c2c_CustomPostLimits();

endif; // end if !class_exists()

?>