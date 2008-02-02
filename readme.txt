=== Custom Post Limits ===
Contributors: coffee2code
Donate link: http://coffee2code.com
Tags: posts, archives, listing, front page, categories
Requires at least: 2.0.2
Tested up to: 2.3.2
Stable tag: trunk
Version: 1.0

Control the number of posts that appear on the front page, search results, and author, category, tag, and date archives, independent of each other.

== Description ==

Control the number of posts that appear on the front page, search results, and author, category, tag, and date archives, independent of each other.

By default, WordPress provides a single configuration option to control how many posts should be listed on your blog.  This value applies for the front page listing, archive listings, category listings, and search results.  This plugin allows you to override that value for each of those different sections.

If the limit field is empty or 0 for a particular section type, then the default post limit will apply.  The Archives Limit value is also treated as the default limit for Day, Month, and Year archives, unless those are explicitly defined.

== Installation ==

1. Unzip `custom-post-limits.zip` inside the `/wp-content/plugins/` directory, or upload `custom-post-limits.php` to `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to the new Options -> Post Limits admin options page.  Optionally customize the limits.

== Frequently Asked Questions ==

= Does this plugin introduce additional database queries (or excessively burden the primary query) to achieve its ends? =

No.  The plugin filters the LIMIT clause of the primary WordPress post query as appropriate, resulting in retrieval of only the number of posts up to the limit you specified without significant alteration of the primary query itself and without additional queries.  Bottom line: this should perform efficiently.

== Screenshots ==

1. A screenshot of the plugin's admin options page.
