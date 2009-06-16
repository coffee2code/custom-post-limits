<?php
/*
Plugin Name: Custom Post Limits
Version: 2.0
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

Compatible with WordPress 2.2+, 2.3+, 2.5+, 2.6+, 2.7+, 2.8.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://coffee2code.com/wp-plugins/custom-post-limits.zip and unzip it into your 
/wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Click the plugin's 'Settings' link next to its 'Deactivate' link (still on the Plugins page), or click on the 
Settings -> Post Limits, to go to the plugin's admin settings page.  Optionally customize the limits.

If no limit is defined, then the default limit as defined in your WordPress configuration is used (accessible via 
	the WordPress admin settings page at Settings -> Reading), the setting labeled "Blog Pages: Show at most:").
*/

/*
Copyright (c) 2008-2009 by Scott Reilly (aka coffee2code)

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
	var $show_admin = true;	// Change this to false if you don't want the plugin's admin page shown.
	var $plugin_name = '';
	var $short_name = '';
	var $plugin_basename = '';
	var $authors = '';
	var $categories = '';
	var $tags = '';
	var $options = array(); // Don't use this directly

	function CustomPostLimits() {
		$this->plugin_name = __('Custom Post Limits');
		$this->short_name = __('Post Limits');
		$this->plugin_basename = plugin_basename(__FILE__); 
		add_action('admin_footer', array(&$this, 'add_js'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('pre_option_posts_per_page', array(&$this, 'custom_post_limits'));
	}

	function install() {
		$this->options = $this->get_options();
		update_option($this->admin_options_name, $this->options);
	}

	function add_js() {
		echo <<<JS
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.cpl-categories, .cpl-tags, .cpl-authors').hide();
				$('#cpl-categories-link').click(function() { $(".cpl-categories").toggle(); });
				$('#cpl-tags-link').click(function() { $(".cpl-tags").toggle(); });
				$('#cpl-authors-link').click(function() { $(".cpl-authors").toggle(); });
			});
		</script>
JS;
	}

	function admin_menu() {
		if ( $this->show_admin ) {
			global $wp_version;
			if ( current_user_can('manage_options') ) {
				if ( version_compare( $wp_version, '2.6.999', '>' ) )
					add_filter( 'plugin_action_links_' . $this->plugin_basename, array(&$this, 'plugin_action_links') );
				add_options_page($this->plugin_name, $this->short_name, 9, $this->plugin_basename, array(&$this, 'options_page'));
			}
		}
	}

	function plugin_action_links($action_links) {
		$settings_link = '<a href="options-general.php?page='.$this->plugin_basename.'">' . __('Settings') . '</a>';
		array_unshift( $action_links, $settings_link );
		return $action_links;
	}

	function get_options() {
		if (!empty($this->options)) return $this->options;
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
		
		if (!$this->authors) {
			global $wpdb;
			$this->authors = $wpdb->get_results("SELECT ID, display_name from $wpdb->users ORDER BY display_name");
		}
		foreach ( (array) $this->authors as $author ) {
			$options['authors_' . $author->ID . '_limit'] = '';
		}

		if (!$this->categories)
			$this->categories = get_categories(array('hide_empty' => false));
		foreach ( (array) $this->categories as $cat ) {
			$options['categories_' . $cat->cat_ID . '_limit'] = '';
		}
		
		if (!$this->tags)
			$this->tags = get_tags(array('hide_empty' => false));
		foreach ( (array) $this->tags as $tag)  {
			$options['tags_' . $tag->term_id . '_limit'] = '';
		}

        $existing_options = get_option($this->admin_options_name);
		$this->options = wp_parse_args($existing_options, $options);
		return $this->options;
	}

	function options_page() {
		$options = $this->get_options();
		// See if user has submitted form
		if ( isset($_POST['submitted']) ) {
			check_admin_referer($this->nonce_field);

			foreach (array_keys($options) AS $opt) {
				$options[$opt] = $_POST[$opt] ? (int) $_POST[$opt] : '';
			}
			// Remember to put all the other options into the array or they'll get lost!
			update_option($this->admin_options_name, $options);

			echo "<div id='message' class='updated fade'><p><strong>" . __('Settings saved.') . '</strong></p></div>';
		}

		$action_url = $_SERVER[PHP_SELF] . '?page=' . $this->plugin_basename;
		$logo = get_option('siteurl') . '/wp-content/plugins/' . basename($_GET['page'], '.php') . '/c2c_minilogo.png';

		$current_limit = get_option('posts_per_page');
		$option_url = "<a href='" . get_option('siteurl') . "/wp-admin/options-reading.php'>here</a>";
		
		echo <<<END
		<div class='wrap'>
			<div class="icon32" style="width:44px;"><img src='$logo' alt='A plugin by coffee2code' /><br /></div>
			<h2>{$this->plugin_name} Settings</h2>
			<p>By default, WordPress provides a single configuration setting to control how many posts should be listed on your
			blog.  This value applies for the front page listing, archive listings, author listings, category listings, tag listings, and search results.
			<strong>Custom Post Limits</strong> allows you to override that value for each of those different sections.</p>

			<p>If the limit field is empty or 0 for a particular section type, then the default post limit will apply. If the
			value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown).</p>
			
			<p>The default post limit as set in your settings is <strong>$current_limit</strong>.  You can change this value
			$option_url.  It's under the <em>Blog Pages</em>, labeled <em>Show at most: [ ] posts</em></p>
			
			<form name="custom_post_limits" action="$action_url" method="post">	
END;
				wp_nonce_field($this->nonce_field);
		echo '<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform form-table">';
				foreach (array_keys($options) as $opt) {

					if (strpos($opt, 'individual_') !== false) {
						$type = array_pop(explode('_', $opt, 2));
						if ( ($type == 'categories' && count($this->categories) < 1) ||
							($type == 'tags' && count($this->tags) < 1) ||
							($type == 'authors' && count($this->authors) < 1) ) {
								continue;
						}
						if ($type == 'categories') {
							foreach ( (array) $this->categories as $cat ) {
								$index = $type . '_' . $cat->cat_ID . '_limit';
								$value = $options[$index];
								echo "<tr valign='top' class='cpl-$type'><th width='33%' scope='row'>&#8212;&#8212; ".get_cat_name($cat->cat_ID)."</th>";
								echo "<td><input type='text' class='small-text' name='$index' value='$value' /></td></tr>";
							}
						} elseif ($type == 'tags') {
							foreach ( (array) $this->tags as $tag ) {
								$index = $type . '_' . $tag->term_id . '_limit';
								$value = $options[$index];
								echo "<tr valign='top' class='cpl-$type'><th width='33%' scope='row'>&#8212;&#8212; $tag->term_name</th>";
								echo "<td><input type='text' class='small-text' name='$index' value='$value' /></td></tr>";
							}
						} elseif ($type == 'authors') {
							foreach ( (array) $this->authors as $author ) {
								$index = $type . '_' . $author->ID . '_limit';
								$value = $options[$index];
								echo "<tr valign='top' class='cpl-$type'><th width='33%' scope='row'>&#8212;&#8212; $author->display_name</th>";
								echo "<td><input type='text' class='small-text' name='$index' value='$value' /></td></tr>";
							}
						}
					} else {
						$parts = explode('_', $opt);
						if ((int)$parts[1] > 0) continue;
						$opt_name = implode(' ', array_map('ucfirst', $parts));
						$opt_value = $options[$opt];
						echo "<tr valign='top'><th width='33%' scope='row'>$opt_name</th>";
						echo "<td><input name='$opt' type='text' class='small-text' id='$opt' value='$opt_value' />";
						echo " <span style='color:#777; font-size:x-small;'>";
						$is_archive = in_array($opt, array('day_archives_limit', 'month_archives_limit', 'year_archives_limit'));
						if (!$opt_value) {
							if ($is_archive && $options['archives_limit'])
								echo "(Archives Limit of {$options['archives_limit']} is being used)";
							else
								echo "(The WordPress default of $current_limit is being used)";
						} elseif ($opt_value == '-1') {
							echo "(ALL posts are set to be displayed for this)";
						}
						$type = strtolower(array_shift(explode(' ', $opt_name)));
						if ( array_key_exists('individual_'.$type, $options) && count($this->$type) > 0)
							echo " &#8211; <a id='cpl-{$type}-link' href='javascript:return false;'>Show/hide individual ".strtolower($opt_name)."</a>";
						
						if ($is_archive)
							echo "<br />If not defined, it assumes the value of Archives Limit.";
						elseif ($opt == 'archives_limit')
							echo '<br />This is the default for Day, Month, and Year archives, unless those are defined explicitly below.';
						echo '</span>';
						echo "</td></tr>\n";
					}
				}
		echo <<<END
			</table>
			<input type="hidden" name="submitted" value="1" />
			<div class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></div>
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
			This plugin brought to you by <a href="http://coffee2code.com" title="coffee2code.com">Scott Reilly, aka coffee2code</a>.
			<span><a href="http://coffee2code.com/donate" title="Please consider a donation">Did you find this plugin useful?</a></span>
			</div>
		</div>
END;
	}

	function custom_post_limits($old_limit) {
		if (is_admin()) return $old_limit;
		$options = $this->get_options();
		if (is_home())
			$limit = $options['front_page_limit'];
		elseif (is_category()) {
			$limit = $options['categories_limit'];
			foreach ($this->categories as $cat) {
				$opt = 'categories_' . $cat->cat_ID . '_limit';
				if ( $options[$opt] && is_category($cat->cat_ID) ) {
					$limit = $options[$opt];
					break;
				}
			}
		} elseif (is_tag()) {
			$limit = $options['tags_limit'];
			foreach ($this->tags as $tag) {
				$opt = 'tags_' . $tag->term_id . '_limit';
				if ( $options[$opt] && is_tag($tag->term_id) ) {
					$limit = $options[$opt];
					break;
				}
			}
		} elseif (is_search())
			$limit = $options['searches_limit'];
		elseif (is_author()) {
			$limit = $options['authors_limit'];
			foreach ($this->authors as $author) {
				$opt = 'authors_' . $author->ID . '_limit';
				if ( $options[$opt] && is_author($author->ID) ) {
					$limit = $options[$opt];
					break;
				}
			}
		} elseif (is_year())
			$limit = $options['year_archives_limit'] ? $options['year_archives_limit'] : $options['archives_limit'];
		elseif (is_month())
			$limit = $options['month_archives_limit'] ? $options['month_archives_limit'] : $options['archives_limit'];
		elseif (is_day())
			$limit = $options['day_archives_limit'] ? $options['day_archives_limit'] : $options['archives_limit'];
		elseif (is_archive())
			$limit = $options['archives_limit'];

		if (!$limit)
			$limit = $old_limit;
		elseif ($limit == '-1')
			$limit = '18446744073709551615';	// Hacky, but it's what the MySQL docs suggest!

		return $limit;
	}
} // end CustomPostLimits

endif; // end if !class_exists()
if ( class_exists('CustomPostLimits') ) :
	// Get the ball rolling
	$custom_post_limits = new CustomPostLimits();
	// Actions and filters
	if (isset($custom_post_limits)) {
		register_activation_hook( __FILE__, array(&$custom_post_limits, 'install') );
	}
endif;

?>
