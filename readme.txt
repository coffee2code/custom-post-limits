=== Custom Post Limits ===
Contributors: coffee2code
Donate link: http://coffee2code.com
Tags: posts, archives, listing, limit, query, front page, categories, tags
Requires at least: 2.2
Tested up to: 2.7.1
Stable tag: trunk
Version: 1.5

Control the number of posts that appear on the front page, search results, and author, category, tag, and date archives, independent of each other, including specific archives.

== Description ==

Control the number of posts that appear on the front page, search results, and author, category, tag, and date archives, independent of each other, including specific archives.

By default, WordPress provides a single configuration setting to control how many posts should be listed on your blog.  This value applies for the front page listing, author listings, archive listings, category listings, tag listings, and search results.  This plugin allows you to override that value for each of those different sections.

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

If the limit field is empty or 0 for a particular section type, then the default post limit will apply.  If the value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown).  The Archives Limit value is also treated as the default limit for Day, Month, and Year archives, unless those are explicitly defined.

== Installation ==

1. Unzip `custom-post-limits.zip` inside the `/wp-content/plugins/` directory, or upload `custom-post-limits.php` to `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Click the plugin's `Settings` link next to its `Deactivate` link (still on the Plugins page), or click on the `Settings` -> `Post Limits` link, to go to the plugin's admin settings page.  Optionally customize the limits.

== Frequently Asked Questions ==

= Does this plugin introduce additional database queries (or excessively burden the primary query) to achieve its ends? =

No.  The plugin filters the LIMIT clause of the primary WordPress post query as appropriate, resulting in retrieval of only the number of posts up to the limit you specified without significant alteration of the primary query itself and without additional queries.  Bottom line: this should perform efficiently.

== Screenshots ==

1. A screenshot of the plugin's admin settings page (with individual authors limits expanded).
