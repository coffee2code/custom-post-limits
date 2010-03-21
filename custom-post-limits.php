<?php
/**
 * @package Custom_Post_Limits
 * @author Scott Reilly
 * @version 2.6
 */
/*
Plugin Name: Custom Post Limits
Version: 2.6
Plugin URI: http://coffee2code.com/wp-plugins/custom-post-limits
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Control the number of posts that appear on the front page, search results, and author, category, tag, and date archives, independent of each other, including specific archives.

By default, WordPress provides a single configuration setting to control how many posts should be listed on your blog.  This
value applies for the front page listing, author listings, archive listings, category listings, tag listings, and search
results.  This plugin allows you to override that value for each of those different sections.

Specifically, this plugin allows you to define limits for:

* Authors archives (the archive listing of posts for any author)
* Author archives (the archive listing of posts for any specific author)
* Categories archives (the archive listings of posts for any category)
* Category archive (the archive listings of posts for any specific category)
* Date-based archives (the archive listings of posts for any date)
* Day archives (the archive listings of posts for any day)
* Front page (the listing of posts on the front page of the blog)
* Month archives (the archive listings of posts for any month)
* Search results (the listing of search results)
* Tags archives (the archive listings of posts for any tag)
* Tag archive (the archive listings of posts for any specific tag)
* Year archives (the archive listings of posts for any year)

Compatible with WordPress 2.8+, 2.9+.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://coffee2code.com/wp-plugins/custom-post-limits.zip and unzip it into your 
/wp-content/plugins/ directory (or install via the built-in WordPress plugin installer).
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Click the plugin's 'Settings' link next to its 'Deactivate' link (still on the Plugins page), or click on 
Settings -> Post Limits, to go to the plugin's admin settings page.  Optionally customize the limits.

If no limit is defined, then the default limit as defined in your WordPress configuration is used (accessible via 
	the WordPress admin settings page at Settings -> Reading), the setting labeled "Blog pages show at most").
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

if ( !class_exists('CustomPostLimits') ) :

class CustomPostLimits {
	var $admin_options_name = 'c2c_post_limits';
	var $nonce_field = 'update-custom_post_limits';
	var $textdomain = 'custom-post-limits';
	var $show_admin = true;	// Change this to false if you don't want the plugin's admin page shown.
	var $plugin_name = '';
	var $short_name = '';
	var $plugin_basename = '';
	var $authors = '';
	var $categories = '';
	var $tags = '';
	var $options = array(); // Don't use this directly

	/**
	 * Class constructor: initializes class variables and adds actions and filters.
	 */
	function CustomPostLimits() {
		$this->plugin_name = __('Custom Post Limits', $this->textdomain);
		$this->short_name = __('Post Limits', $this->textdomain);
		$this->plugin_basename = plugin_basename(__FILE__);

		add_action('activate_' . str_replace(trailingslashit(WP_PLUGIN_DIR), '', __FILE__), array(&$this, 'install'));
		add_action('admin_footer', array(&$this, 'add_js'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('init', array(&$this, 'load_textdomain'));
		add_action('pre_option_posts_per_page', array(&$this, 'custom_post_limits'));
	}

	function install() {
		$this->options = $this->get_options();
		update_option($this->admin_options_name, $this->options);
	}

	/**
	 * Loads the localization textdomain for the plugin.
	 *
	 * @return void
	 */
	function load_textdomain() {
		load_plugin_textdomain( $this->textdomain, false, basename(dirname(__FILE__)) );
	}

	/**
	 * Outputs the JavaScript used by the plugin, within script tags.
	 *
	 * @return void (Text is echoed; nothing is returned)
	 */
	function add_js() {
		echo <<<JS
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.cpl-categories, .cpl-tags, .cpl-authors').hide();
				$('#cpl-categories-link').click(function() { $(".cpl-categories").toggle(); return false; });
				$('#cpl-tags-link').click(function() { $(".cpl-tags").toggle(); return false; });
				$('#cpl-authors-link').click(function() { $(".cpl-authors").toggle(); return false; });
			});
		</script>
JS;
	}

	/**
	 * Registers the admin options page and the Settings link.
	 *
	 * @return void
	 */
	function admin_menu() {
		if ( $this->show_admin && current_user_can('manage_options') ) {
			add_filter( 'plugin_action_links_' . $this->plugin_basename, array(&$this, 'plugin_action_links') );
			add_options_page($this->plugin_name, $this->short_name, 'manage_options', $this->plugin_basename, array(&$this, 'options_page'));
		}
	}

	/**
	 * Adds a 'Settings' link to the plugin action links.
	 *
	 * @param int $limit The default limit value for the current posts query.
	 * @return array Links associated with a plugin on the admin Plugins page
	 */
	function plugin_action_links( $action_links ) {
		$settings_link = '<a href="options-general.php?page='.$this->plugin_basename.'">' . __('Settings', $this->textdomain) . '</a>';
		array_unshift( $action_links, $settings_link );
		return $action_links;
	}

	/**
	 * Returns either the buffered array of all options for the plugin, or
	 * obtains the options and buffers the value.
	 *
	 * @param bool $with_existing Should the actual current option values be returned? Default is true.
	 * @return array The options array for the plugin (which is also stored in $this->options if !$with_options).
	 */
	function get_options( $with_existing = true ) {
		if ( $with_existing && !empty($this->options) ) return $this->options;
	    $options = array(
			'archives_limit' => '',
			'authors_limit' => '',
			'individual_authors' => '',
			'categories_limit' => '',
			'individual_categories' => '',
			'day_archives_limit' => '',
			'front_page_limit' => '',
			'month_archives_limit' => '',
			'searches_limit' => '',
			'tags_limit' => '',
			'individual_tags' => '',
			'year_archives_limit' => ''
		);
		if ( !$with_existing )
			return $options;
		$existing_options = get_option($this->admin_options_name);
		$this->options = wp_parse_args($existing_options, $options);
		return $this->options;
	}

	/**
	 * Returns an array of limits for individual authors, categories, and/or tags.
	 *
	 * @param array $type (optional) Array containing the types of individual limits to return.  Can be any of: 'authors', 'categories', 'tags'. Default is an empty array, which returns the limits for all types.
	 * @return array Array of limits for specified types.
	 */
	function load_individual_options( $type = array() ) {
		$options = array();
		if ( !$type || in_array('authors', $type) ) {
			$this->get_authors();
			foreach ( (array) $this->authors as $author ) {
				$options['authors_' . $author->ID . '_limit'] = '';
			}
		}
		if ( !$type || in_array('categories', $type) ) {
			$this->get_categories();
			foreach ( (array) $this->categories as $cat ) {
				$options['categories_' . $cat->cat_ID . '_limit'] = '';
			}
		}
		if ( !$type || in_array('tags', $type) ) {
			$this->get_tags();
			foreach ( (array) $this->tags as $tag)  {
				$options['tags_' . $tag->term_id . '_limit'] = '';
			}
		}
		return $options;
	}

	/**
	 * Saves user's submitted settings changes and outputs the options page
	 */
	function options_page() {
		$options = array_merge($this->load_individual_options(), $this->get_options());
		// See if user has submitted form
		if ( isset($_POST['submitted']) ) {
			check_admin_referer($this->nonce_field);

			if ( isset($_POST['Reset']) ) {
				$options = $this->get_options(false);
				$msg = __('Settings reset.', $this->textdomain);
			} else {
				foreach ( array_keys($options) AS $opt ) {
					$options[$opt] = $_POST[$opt] ? intval($_POST[$opt]) : '';
				}
				$msg = __('Settings saved.', $this->textdomain);
			}
			// Remember to put all the other options into the array or they'll get lost!
			update_option($this->admin_options_name, $options);
			$this->options = $options;

			echo "<div id='message' class='updated fade'><p><strong>" . $msg . '</strong></p></div>';
		}

		$action_url = $_SERVER['PHP_SELF'] . '?page=' . $this->plugin_basename;
		$logo = plugins_url(basename($_GET['page'], '.php') . '/c2c_minilogo.png');

		$current_limit = get_option('posts_per_page');
		$option_url = '<a href="' . admin_url('options-reading.php') . '">' . __('here', $this->textdomain) . '</a>';

		echo "<div class='wrap'><div class='icon32' style='width:44px;'><img src='$logo' alt='" . esc_attr__('A plugin by coffee2code', $this->textdomain) . "' /><br /></div>";
		echo '<h2>' . __('Custom Post Limits Settings', $this->textdomain) . '</h2>';
		echo '<p>' . __('By default, WordPress provides a single configuration setting to control how many posts should be listed on your blog.  This value applies for the front page listing, archive listings, author listings, category listings, tag listings, and search results.  <strong>Custom Post Limits</strong> allows you to override that value for each of those different sections.', $this->textdomain) . '</p>';
		echo '<p>' . __('If the limit field is empty or 0 for a particular section type, then the default post limit will apply. If the value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown).', $this->textdomain) . '</p>';

		echo '<p>' . sprintf(__('The default post limit as set in your settings is <strong>%1$d</strong>.  You can change this value %2$s, which is labeled as <em>Blog pages show at most</em>', $this->textdomain), $current_limit, $option_url) . '</p>';

		echo "<form name='custom_post_limits' action='$action_url' method='post'>";
			wp_nonce_field($this->nonce_field);
			echo '<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform form-table">';
				foreach (array_keys($options) as $opt) {

					if ( strpos($opt, 'individual_' ) !== false ) {
						$type = array_pop(explode('_', $opt, 2));
						if ( ($type == 'categories' && count($this->categories) < 1) ||
							($type == 'tags' && count($this->tags) < 1) ||
							($type == 'authors' && count($this->authors) < 1) ) {
								continue;
						}
						if ( $type == 'categories' ) {
							foreach ( (array) $this->categories as $cat ) {
								$index = $type . '_' . $cat->cat_ID . '_limit';
								$value = $options[$index];
								echo "<tr valign='top' class='cpl-$type'><th width='33%' scope='row'>&#8212;&#8212; ".get_cat_name($cat->cat_ID)."</th>";
								echo "<td><input type='text' class='small-text' name='$index' value='$value' /></td></tr>";
							}
						} elseif ( $type == 'tags' ) {
							foreach ( (array) $this->tags as $tag ) {
								$index = $type . '_' . $tag->term_id . '_limit';
								$value = $options[$index];
								echo "<tr valign='top' class='cpl-$type'><th width='33%' scope='row'>&#8212;&#8212; $tag->name</th>";
								echo "<td><input type='text' class='small-text' name='$index' value='$value' /></td></tr>";
							}
						} elseif ( $type == 'authors' ) {
							foreach ( (array) $this->authors as $author ) {
								$index = $type . '_' . $author->ID . '_limit';
								$value = $options[$index];
								echo "<tr valign='top' class='cpl-$type'><th width='33%' scope='row'>&#8212;&#8212; $author->display_name</th>";
								echo "<td><input type='text' class='small-text' name='$index' value='$value' /></td></tr>";
							}
						}
					} else {
						$parts = explode('_', $opt);
						if ( (int)$parts[1] > 0 ) continue;
						$opt_name = implode(' ', array_map('ucfirst', $parts));
						$opt_value = $options[$opt];
						echo "<tr valign='top'><th width='33%' scope='row'>" . __($opt_name, $this->textdomain) . '</th>';
						echo "<td><input name='$opt' type='text' class='small-text' id='$opt' value='$opt_value' />";
						echo " <span style='color:#777; font-size:x-small;'>";
						$is_archive = in_array($opt, array('day_archives_limit', 'month_archives_limit', 'year_archives_limit'));
						if ( !$opt_value ) {
							if ($is_archive && $options['archives_limit'])
								echo sprintf(__('(Archives Limit of %s is being used)', $this->textdomain), $options['archives_limit']);
							else
								echo sprintf(__('(The WordPress default of %d is being used)', $this->textdomain), $current_limit);
						} elseif ( $opt_value == '-1' ) {
							echo __('(ALL posts are set to be displayed for this)', $this->textdomain);
						}
						$type = strtolower(array_shift(explode(' ', $opt_name)));
						if ( array_key_exists('individual_'.$type, $options) && count($this->$type) > 0 )
							echo " &#8211; <a id='cpl-{$type}-link' href='#'>".sprintf(__('Show/hide individual %s', $this->textdomain), strtolower($opt_name)) . '</a>';
						
						if ( $is_archive )
							echo '<br />' . __('If not defined, it assumes the value of Archives Limit.', $this->textdomain);
						elseif ( $opt == 'archives_limit' )
							echo '<br />' . __('This is the default for Day, Month, and Year archives, unless those are defined explicitly below.', $this->textdomain);
						echo '</span>';
						echo "</td></tr>\n";
					}
				}
		$txt = __('Save Changes', $this->textdomain);
		$reset_txt = __('Reset Settings', $this->textdomain);
		echo <<<END
			</table>
			<input type="hidden" name="submitted" value="1" />
			<div class="submit"><input type="submit" name="Submit" class="button-primary" value="{$txt}" />
			<input type="submit" name="Reset" class="button" value="{$reset_txt}" /></div>
		</form>
			</div>
END;
		echo <<<END
		<style type="text/css">
			#c2c {
				text-align:center;
				color:#888;
				background-color:#ffffef;
				padding:5px 0 0;
				margin-top:12px;
				border-style:solid;
				border-color:#dadada;
				border-width:1px 0;
			}
			#c2c div {
				margin:0 auto;
				padding:5px 40px 0 0;
				width:45%;
				min-height:40px;
				background:url('$logo') no-repeat top right;
			}
			#c2c span {
				display:block;
				font-size:x-small;
			}
		</style>
		<div id='c2c' class='wrap'>
			<div>
END;
		$c2c = '<a href="http://coffee2code.com" title="coffee2code.com">' . __('Scott Reilly, aka coffee2code', $this->textdomain) . '</a>';
		echo sprintf(__('This plugin brought to you by %s.', $this->textdomain), $c2c);
		echo '<span><a href="http://coffee2code.com/donate" title="' . esc_attr__('Please consider a donation', $this->textdomain) . '">' .
		__('Did you find this plugin useful?', $this->textdomain) . '</a></span>';
		echo '</div></div>';
	}

	/**
	 * Returns a potentially overridden limit value for the currently queried posts.
	 *
	 * @param int $limit The default limit value for the current posts query.
	 * @return int The limit value for the current posts query.
	 */
	function custom_post_limits( $limit ) {
		global $wp_query; // Only used for individual (author, category, tag) limits
		if ( is_admin() ) return $limit;
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
					($query_vars['cat'] == $cat->cat_ID || $query_vars['category_name'] == $cat->slug ||
						preg_match("/\/{$cat->slug}\/?$/", $query_vars['category_name'])) ) {
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
					($query_vars['tag_id'] == $tag->term_id || $query_vars['tag'] == $tag->slug) ) {
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
					($query_vars['author'] == $author->ID || $query_vars['author_name'] == $author->user_nicename) ) {
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
			$this->authors = $wpdb->get_results("SELECT ID, display_name, user_nicename from $wpdb->users ORDER BY display_name");
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
			$this->categories = get_categories(array('hide_empty' => false));
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
			$this->tags = get_tags(array('hide_empty' => false));
		return $this->tags;
	}

} // end CustomPostLimits

endif; // end if !class_exists()

if ( class_exists('CustomPostLimits') )
	new CustomPostLimits();

?>