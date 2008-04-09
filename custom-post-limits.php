<?php
/*
Plugin Name: Custom Post Limits
Version: 1.1
Plugin URI: http://coffee2code.com/wp-plugins/custom-post-limits
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Control the number of posts that appear on the front page, search results, and author, category, tag, and date archives, independent of each other.

Compatible with WordPress 2.2+, 2.3+, and 2.5.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://coffee2code.com/wp-plugins/custom-post-limits.zip and unzip it into your 
/wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Go to the new Options -> Post Limits (or in WP 2.5: Settings -> Post Limits) admin options page.
Optionally customize the limits.

If no limit is defined, then the default limit as defined in your WordPress configuration is used (accessible via 
	the WordPress admin options page at Options -> Reading (or in WP 2.5: Settings -> Reading), the setting 
	labeled "Blog Pages: Show at most:").
*/

/*
Copyright (c) 2008 by Scott Reilly (aka coffee2code)

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

	function CustomPostLimits() {
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('post_limits', array(&$this, 'custom_post_limits'));
	}

	function install() {
		$options = $this->get_options();
		update_option($this->admin_options_name, $options);
	}

	function admin_menu() {
		add_options_page('Custom Post Limits', 'Post Limits', 9, basename(__FILE__), array(&$this, 'options_page'));
	}

	function get_options() {
	    $options = array(
			'archives_limit' => '',
			'authors_limit' => '',
			'categories_limit' => '',
			'day_archives_limit' => '',
			'front_page_limit' => '',
			'month_archives_limit' => '',
			'searches_limit' => '',
			'tags_limit' => '',
			'year_archives_limit' => ''
		);
        $existing_options = get_option($this->admin_options_name);
        if (!empty($existing_options)) {
            foreach ($existing_options as $key => $option)
                $options[$key] = $option;
        }            
        return $options;
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

			echo "<div class='updated'><p>Plugin settings saved.</p></div>";
		}

		$action_url = $_SERVER[PHP_SELF] . '?page=' . basename(__FILE__);

		$current_limit = get_option('posts_per_page');
		$option_url = "<a href='" . get_option('siteurl') . "/wp-admin/options-reading.php'>Options &raquo; Reading</a>";
		
		echo <<<END
		<div class='wrap'>
			<h2>Custom Post Limits Plugin Options</h2>
			<p>By default, WordPress provides a single configuration option to control how many posts should be listed on your
			blog.  This value applies for the front page listing, archive listings, category listings, tag listings, and search results.
			<strong>Custom Post Limits</strong> allows you to override that value for each of those different sections.</p>

			<p>If the limit field is empty or 0 for a particular section type, then the default post limit will apply. If the
			value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown).</p>
			
			<p>The default post limit as set in your options is <strong>$current_limit</strong>.  You can change this value
			at $option_url.  It's under the <em>Blog Pages</em>, labeled <em>Show at most: [ ] posts</em></p>
			
			<form name="custom_post_limits" action="$action_url" method="post">	
END;
				wp_nonce_field($this->nonce_field);
		echo '<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform form-table">';
				foreach (array_keys($options) as $opt) {
					$opt_name = implode(' ', array_map('ucfirst', explode('_', $opt)));
					$opt_value = $options[$opt];
					echo "<tr valign='top'><th width='33%' scope='row'>$opt_name</th>";
					echo "<td><input name='$opt' type='text' id='$opt' value='$opt_value' />";
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
					if ($is_archive)
						echo "<br />If not defined, it assumes the value of Archives Limit.";
					elseif ($opt == 'archives_limit')
						echo '<br />This is the default for Day, Month, and Year archives, unless those are defined explicitly below.';
					echo '</span>';
					echo "</td></tr>";
				}
		echo <<<END
			</table>
			<input type="hidden" name="submitted" value="1" />
			<div class="submit"><input type="submit" name="Submit" value="Save Changes" /></div>
		</form>
			</div>
END;
		$logo = get_option('siteurl') . '/wp-content/plugins/' . basename($_GET['page'], '.php') . '/c2c_minilogo.png';
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

	function custom_post_limits($sql_limit) {
		// $sql_limit should look like: LIMIT 0, 10
		// WP takes a few things into account when determining the offset part of the LIMIT,
		//	so refrain from re-determining it
		if (!$sql_limit) return;
		$options = $this->get_options();
		list($offset, $old_limit) = explode(',', $sql_limit, 2);
		if (is_home())
			$limit = $options['front_page_limit'];
		elseif (is_category())
			$limit = $options['categories_limit'];
		elseif (is_tag())
			$limit = $options['tags_limit'];
		elseif (is_search())
			$limit = $options['searches_limit'];
		elseif (is_author())
			$limit = $options['authors_limit'];
		elseif (is_year())
			$limit = $options['year_archives_limit'] ? $options['year_archives_limit'] : $options['archives_limit'];
		elseif (is_month())
			$limit = $options['month_archives_limit'] ? $options['month_archives_limit'] : $options['archives_limit'];
		elseif (is_day())
			$limit = $options['day_archives_limit'] ? $options['day_archives_limit'] : $options['archives_limit'];
		elseif (is_archive())
			$limit = $options['archives_limit'];

		if (!$limit)
			$limit = trim($old_limit);
		elseif ($limit == '-1')
			$limit = '18446744073709551615';	// Hacky, but it's what the MySQL docs suggest!
		return ($limit ? "$offset, $limit" : '');
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