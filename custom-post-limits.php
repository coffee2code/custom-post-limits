<?php
/**
 * @package Custom_Post_Limits
 * @author Scott Reilly
 * @version 3.0
 */
/*
Plugin Name: Custom Post Limits
Version: 3.0
Plugin URI: http://coffee2code.com/wp-plugins/custom-post-limits
Author: Scott Reilly
Author URI: http://coffee2code.com
Text Domain: custom-post-limits
Description: Control the number of posts that appear on the front page, search results, and author, category, tag, and date archives, independent of each other, including specific archives.

Compatible with WordPress 2.8+, 2.9+, 3.0+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/custom-post-limits/

*/

/*
Copyright (c) 2008-2010 by Scott Reilly (aka coffee2code)

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

if ( !class_exists( 'CustomPostLimits' ) ) :

require_once( 'c2c-plugin.php' );

class CustomPostLimits extends C2C_Plugin_009 {
	var $authors = '';
	var $categories = '';
	var $tags = '';

	/**
	 * Class constructor: initializes class variables and adds actions and filters.
	 */
	function CustomPostLimits() {
		$this->C2C_Plugin_009( '3.0', 'custom-post-limits', 'c2c', __FILE__, array() );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 *
	 * @return void
	 */
	function load_config() {
		$this->name = __( 'Custom Post Limits', $this->textdomain );
		$this->menu_name = __( 'Post Limits', $this->textdomain );

		$this->config = array(
			'archives_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Archives Limit', $this->textdomain ) ),
			'authors_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Authors Limit', $this->textdomain ) ),
			'individual_authors' => array( 'input' => 'custom' ),
			'categories_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Categories Limit', $this->textdomain ) ),
			'individual_categories' => array( 'input' => 'custom' ),
			'day_archives_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Day Archives Limits', $this->textdomain ) ),
			'front_page_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Front Page Limit', $this->textdomain ) ),
			'month_archives_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Month Archives Limit', $this->textdomain ) ),
			'searches_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Searches Limit', $this->textdomain ) ),
			'tags_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Tags Limit', $this->textdomain ) ),
			'individual_tags' => array( 'input' => 'custom' ),
			'year_archives_limit' => array( 'input' => 'short_text', 'datatype' => 'int',
					'label' => __( 'Year Archives Limits', $this->textdomain ) )
		);
	}

	/**
	 * Override the plugin framework's register_filters() to register actions and filters.
	 *
	 * @return void
	 */
	function register_filters() {
		if ( $this->is_plugin_admin_page() )
			add_action( 'admin_print_footer_scripts', array( &$this, 'add_plugin_admin_js' ) );
		add_action( 'pre_option_posts_per_page', array( &$this, 'custom_post_limits' ) );
		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'register_individual_archive_options' ) );
			add_filter( $this->get_hook( 'options' ), array( &$this, 'load_individual_options' ) );
		}
//		add_action( $this->get_hook( 'pre_display_option' ), array( &$this, 'pre_display_option' ) );
		add_filter( $this->get_hook( 'option_help' ), array( &$this, 'dynamic_option_help' ), 10, 2 );
	}

	/**
	 * Outputs the text above the setting form
	 *
	 * @return void (Text will be echoed.)
	 */
	function options_page_description() {
		$options = $this->get_options();
		$current_limit = get_option( 'posts_per_page' );
		$option_url = '<a href="' . admin_url('options-reading.php') . '">' . __('here', $this->textdomain) . '</a>';
		parent::options_page_description( __( 'Custom Post Limits Settings', $this->textdomain ) );
		echo '<p>' . __( 'By default, WordPress provides a single configuration setting to control how many posts should be listed on your blog.  This value applies for the front page listing, archive listings, author listings, category listings, tag listings, and search results.  <strong>Custom Post Limits</strong> allows you to override that value for each of those different sections.', $this->textdomain ) . '</p>';
		echo '<p>' . __( 'If the limit field is empty or 0 for a particular section type, then the default post limit will apply. If the value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown).', $this->textdomain ) . '</p>';
		echo '<p>' . sprintf( __( 'The default post limit as set in your settings is <strong>%1$d</strong>.  You can change this value %2$s, which is labeled as <em>Blog pages show at most</em>', $this->textdomain ), $current_limit, $option_url ) . '</p>';
	}

	function register_individual_archive_options() {
		$individual_options = $this->load_individual_options( array() );
		foreach ( array_keys( $individual_options ) as $opt )
			add_settings_field( $opt, $this->get_option_label( $opt ), array( &$this, 'display_individual_option' ), $this->plugin_file, 'default', $opt );
	}

	/**
	 * Returns an array of limits for individual authors, categories, and/or tags.
	 *
	 * @param array $primary_options The plugin's primary array of options
	 * @param array $type (optional) Array containing the types of individual limits to return.  Can be any of: 'authors', 'categories', 'tags'. Default is an empty array, which returns the limits for all types.
	 * @return array Array of primary limits amended with limits for specified types.
	 */
	function load_individual_options( $primary_options, $type = array() ) {
		$options = array();
		if ( !$type || in_array( 'authors', $type ) ) {
			$this->get_authors();
			foreach ( (array) $this->authors as $author ) {
				$options['authors_' . $author->ID . '_limit'] = '';
				$this->config['authors_' . $author->ID . '_limit']['label'] = "&#8212;&#8212; $author->display_name";
			}
		}
		if ( !$type || in_array( 'categories', $type ) ) {
			$this->get_categories();
			foreach ( (array) $this->categories as $cat ) {
				$options['categories_' . $cat->cat_ID . '_limit'] = '';
				$this->config['categories_' . $cat->cat_ID . '_limit']['label'] = "&#8212;&#8212; " . get_cat_name( $cat->cat_ID );
			}
		}
		if ( !$type || in_array( 'tags', $type ) ) {
			$this->get_tags();
			foreach ( (array) $this->tags as $tag )  {
				$options['tags_' . $tag->term_id . '_limit'] = '';
				$this->config['tags_' . $tag->term_id . '_limit']['label'] = "&#8212;&#8212; $tag->name";
			}
		}
		return array_merge( $options, $primary_options );
	}

	function display_individual_option( $opt ) {
		$options = $this->get_options();
		$parts = explode( '_', $opt );
		$type = $parts[0];
		$id = $parts[1];
		if ( $type == 'categories' ) {
			foreach ( (array) $this->categories as $cat ) {
				if ( $cat->cat_ID != $id )
					continue;
				$idx = $type . '_' . $cat->cat_ID . '_limit';
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[$idx] ) ? $options[$idx] : '';
//				echo "<tr valign='top' class='cpl-$type'><th scope='row'> &#8212;&#8212; " . get_cat_name( $cat->cat_ID ) . "</th>";
				echo "<input type='text' class='small-text' name='$index' value='$value' />";
				break;
			}
		} elseif ( $type == 'tags' ) {
			foreach ( (array) $this->tags as $tag ) {
				if ( $tag->term_id != $id )
					continue;
				$idx = $type . '_' . $tag->term_id . '_limit';
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[$index] ) ? $options[$index] : '';
//				echo "<tr valign='top' class='cpl-$type'><th scope='row'> &#8212;&#8212; $tag->name</th>";
				echo "<input type='text' class='small-text' name='$index' value='$value' />";
				break;
			}
		} elseif ( $type == 'authors' ) {
			foreach ( (array) $this->authors as $author ) {
				if ( $author->ID != $id )
					continue;
				$idx = $type . '_' . $author->ID . '_limit';
				$index = $this->admin_options_name . "[$idx]";
				$value = isset( $options[$index] ) ? $options[$index] : '';
//				echo "<tr valign='top' class='cpl-$type'><th scope='row'> &#8212;&#8212; $author->display_name</th>";
				echo "<input type='text' class='small-text' name='$index' value='$value' />";
				break;
			}
		}
	}

	function pre_display_option( $opt ) {
		$options = $this->get_options();
		if ( strpos($opt, 'individual_' ) !== false ) {
			$type = array_pop(explode('_', $opt, 2));
			if ( ($type == 'categories' && count($this->categories) < 1) ||
				($type == 'tags' && count($this->tags) < 1) ||
				($type == 'authors' && count($this->authors) < 1) ) {
					continue;
			}
			if ( $type == 'categories' ) {
				foreach ( (array) $this->categories as $cat ) {
					$idx = $type . '_' . $cat->cat_ID . '_limit';
					$index = $this->admin_options_name . "[$idx]";
					$value = isset( $options[$idx] ) ? $options[$idx] : '';
//					echo "<tr valign='top' class='cpl-$type'><th scope='row'> &#8212;&#8212; ".get_cat_name($cat->cat_ID)."</th>";
					echo "<input type='text' class='small-text' name='$index' value='$value' />";
				}
			} elseif ( $type == 'tags' ) {
				foreach ( (array) $this->tags as $tag ) {
					$idx = $type . '_' . $tag->term_id . '_limit';
					$index = $this->admin_options_name . "[$idx]";
					$value = isset( $options[$index] ) ? $options[$index] : '';
//					echo "<tr valign='top' class='cpl-$type'><th scope='row'> &#8212;&#8212; $tag->name</th>";
					echo "<input type='text' class='small-text' name='$index' value='$value' />";
				}
			} elseif ( $type == 'authors' ) {
				foreach ( (array) $this->authors as $author ) {
					$idx = $type . '_' . $author->ID . '_limit';
					$index = $this->admin_options_name . "[$idx]";
					$value = isset( $options[$index] ) ? $options[$index] : '';
//					echo "<tr valign='top' class='cpl-$type'><th scope='row'> &#8212;&#8212; $author->display_name</th>";
					echo "<input type='text' class='small-text' name='$index' value='$value' />";
				}
			}
		}
	}

	function dynamic_option_help( $helptext, $opt ) {
		$options = $this->get_options();
		$current_limit = get_option( 'posts_per_page' );
		$parts = explode('_', $opt);
		if ( (int)$parts[1] > 0 ) continue;
		$opt_name = implode(' ', array_map('ucfirst', $parts));
		$opt_value = $options[$opt];
		$is_archive = in_array( $opt, array( 'day_archives_limit', 'month_archives_limit', 'year_archives_limit' ) );
		if ( !$opt_value ) {
			if ( $is_archive && $options['archives_limit'] )
				echo sprintf( __( '(Archives Limit of %s is being used)', $this->textdomain ), $options['archives_limit'] );
			else
				echo sprintf( __( '(The WordPress default of %d is being used)', $this->textdomain ), $current_limit );
		} elseif ( $opt_value == '-1' ) {
			echo __( '(ALL posts are set to be displayed for this)', $this->textdomain );
		}
		$type = strtolower( array_shift( explode( ' ', $opt_name ) ) );
		if ( array_key_exists( 'individual_' . $type, $options ) && count( $this->$type ) > 0 )
			echo " &#8211; <a id='cpl-{$type}-link' href='#'>".sprintf( __( 'Show/hide individual %s', $this->textdomain ), strtolower( $opt_name ) ) . '</a>';

		if ( $is_archive )
			echo '<br />' . __( 'If not defined, it assumes the value of Archives Limit.', $this->textdomain );
		elseif ( $opt == 'archives_limit' )
			echo '<br />' . __( 'This is the default for Day, Month, and Year archives, unless those are defined explicitly below.', $this->textdomain );
	}

	/**
	 * Outputs the JavaScript used by the plugin, within script tags.
	 *
	 * @return void (Text is echoed; nothing is returned)
	 */
	function add_plugin_admin_js() {
		echo <<<JS
		<script type="text/javascript">
			jQuery(document).ready(function($) {
//				$('tr td:empty').parent().hide(); /* Hide the empty row produced by the way settings are being handled */
//				$('.cpl-categories, .cpl-tags, .cpl-authors').hide();
				$('#cpl-categories-link').click(function() { $(".cpl-categories").toggle(); return false; });
				$('#cpl-tags-link').click(function() { $(".cpl-tags").toggle(); return false; });
				$('#cpl-authors-link').click(function() { $(".cpl-authors").toggle(); return false; });
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
	function custom_post_limits( $limit ) {
		global $wp_query; // Only used for individual (author, category, tag) limits
		if ( is_admin() )
			return $limit;
		$old_limit = $limit;
		$options = $this->get_options();
		$query_vars = $wp_query->query_vars;
		if ( is_home() ) {
			$limit = $options['front_page_limit'];
		} elseif ( is_search() ) {
			$limit = $options['searches_limit'];
		} elseif ( is_category() ) {
			$limit = $options['categories_limit'];
			$this->get_categories();
			foreach ( $this->categories as $cat ) {
				$opt = 'categories_' . $cat->cat_ID . '_limit';
				if ( $options[$opt] &&
					( $query_vars['cat'] == $cat->cat_ID || $query_vars['category_name'] == $cat->slug ||
						preg_match( "/\/{$cat->slug}\/?$/", $query_vars['category_name'] ) ) ) {
					$limit = $options[$opt];
					break;
				}
			}
		} elseif ( is_tag() ) {
			$limit = $options['tags_limit'];
			$this->get_tags();
			foreach ( $this->tags as $tag ) {
				$opt = 'tags_' . $tag->term_id . '_limit';
				if ( $options[$opt] &&
					( $query_vars['tag_id'] == $tag->term_id || $query_vars['tag'] == $tag->slug ) ) {
					$limit = $options[$opt];
					break;
				}
			}
		} elseif ( is_author() ) {
			$limit = $options['authors_limit'];
			$this->get_authors();
			foreach ( $this->authors as $author ) {
				$opt = 'authors_' . $author->ID . '_limit';
				if ( $options[$opt] &&
					( $query_vars['author'] == $author->ID || $query_vars['author_name'] == $author->user_nicename ) ) {
					$limit = $options[$opt];
					break;
				}
			}
		} elseif ( is_year() ) {
			$limit = $options['year_archives_limit'] ? $options['year_archives_limit'] : $options['archives_limit'];
		} elseif ( is_month() ) {
			$limit = $options['month_archives_limit'] ? $options['month_archives_limit'] : $options['archives_limit'];
		} elseif ( is_day() ) {
			$limit = $options['day_archives_limit'] ? $options['day_archives_limit'] : $options['archives_limit'];
		} elseif ( is_archive() ) {
			$limit = $options['archives_limit'];
		}

		if ( !$limit )
			$limit = $old_limit;
		elseif ( $limit == '-1' )
			$limit = '18446744073709551615';	// Hacky magic number, but it's what the MySQL docs suggest!
		return $limit;
	}

	/**
	 * Returns either the buffered array of all authors, or obtains the authors
	 * and buffers the value.
	 *
	 * @return array Array of authors.  Authors without posts are included.
	 */
	function get_authors() {
		if ( !$this->authors ) {
			global $wpdb;
			$this->authors = $wpdb->get_results( "SELECT ID, display_name, user_nicename from $wpdb->users ORDER BY display_name" );
		}
		return $this->authors;
	}

	/**
	 * Returns either the buffered array of all categories, or obtains the
	 * categories and buffers the value.
	 *
	 * @return array Array of categories.  Categories without posts are included.
	 */
	function get_categories() {
		if ( !$this->categories )
			$this->categories = get_categories( array( 'hide_empty' => false ) );
		return $this->categories;
	}

	/**
	 * Returns either the buffered array of all tags, or obtains the tags and
	 * buffers the value.
	 *
	 * @return array Array of tags.  Tags without posts are included.
	 */
	function get_tags() {
		if ( !$this->tags )
			$this->tags = get_tags( array( 'hide_empty' => false ) );
		return $this->tags;
	}

} // end CustomPostLimits

$GLOBALS['c2c_custom_post_limits'] = new CustomPostLimits();

endif; // end if !class_exists()


?>