=== Custom Post Limits ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: posts, archives, listing, limit, query, front page, categories, tags, coffee2code
Requires at least: 2.8
Tested up to: 2.9.1
Stable tag: 2.6
Version: 2.6

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

1. Unzip `custom-post-limits.zip` inside the `/wp-content/plugins/` directory (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Click the plugin's `Settings` link next to its `Deactivate` link (still on the Plugins page), or click on the `Settings` -> `Post Limits` link, to go to the plugin's admin settings page.  Optionally customize the limits.

== Frequently Asked Questions ==

= Does this plugin introduce additional database queries (or excessively burden the primary query) to achieve its ends? =

No.  The plugin filters the posts_per_page setting value as used by the primary WordPress post query as appropriate, resulting in retrieval of only the number of posts up to the limit you specified without significant alteration of the primary query itself and without additional queries.  Bottom line: this should perform efficiently.

== Screenshots ==

1. A screenshot of the plugin's admin settings page (with individual authors limits expanded).

== Changelog ==

= 2.6 =
* Revert post limiting back to hooking 'pre_option_posts_per_page' rather than filtering 'post_limits' (fixes bug introduced in v2.5)
* Fix bug related to individual author/category/tag limits not applying (the primary intent of the v2.5 release, but needed re-fixing due to reversion)
* Fix bug preventing value of individual limits from appearing on settings page (the value had been saved and used properly, though)
* Add 'Reset Settings' button to facilitate resetting all limits configured via the plugin
* Internal: add get_authors(), get_categories(), get_tags() to retrieve and buffer those respective values if actually needed
* Update object's option buffer after saving changed submitted by user
* Add PHPDoc documentation
* Minor documentation tweaks

= 2.5 =
* Reverted post limiting method used to filtering 'post_limits' again rather than hooking 'pre_option_posts_per_page'
* Fixed bug related to individual author/category/tag limits not applying
* Changed invocation of plugin's install function to action hooked in constructor rather than in global space
* Changed unobtrusively added JavaScript click events to return false, rather than depending on an embedded JS call in link (fixes IE8 compatibility)
* Added full support for localization
* Used admin_url() instead of hardcoded admin path
* Removed compatibility with versions of WP older than 2.8
* Noted compatibility with WP 2.9+

= 2.0 =
* Changed how post limiting is achieved by hooking 'pre_option_posts_per_page' rather than filtering 'post_limits'
* Simplified custom_post_limits()
* Changed permission check to access settings page
* Used plugins_url() instead of hardcoded path
* Removed compatibility with versions of WP older than 2.6
* Noted compatibility with WP2.8
* Began initial effort for localization
* Fixed edge-case bug causing limiting to occur when not appropriate
* Fixed bug with tag names not appearing

= 1.5 =
* NEW:
* Added ability to specify limit on a per-category, per-author, and per-tag basis
* Added ability to show all posts (i.e no limit, via a limit of -1)
* Added "Settings" link next to "Activate"/"Deactivate" link next to the plugin on the admin plugin listings page
* CHANGED:
* Tweaked plugin's admin options page to conform to newer WP 2.7 style
* Extended compatibility to WP 2.7+
* Updated installation instructions, extended description, copyright
* Facilitated translation of some text
* Memoized options
* In admin options page, due to difference b/w WP <2.5 and >2.5, link text for options page is just referred to as "here"
* FIXED:
* Prevent post limiting from occurring in the admin listing
* Fixed plugin path problem in recent versions of WP
* Fixed post paging (next_posts_link()/previous_posts_link()) was not taking post limit into account

= 1.0 =
* Initial release