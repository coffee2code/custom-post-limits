=== Custom Post Limits ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: posts, archives, listing, limit, query, front page, categories, tags, coffee2code
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 4.1

Independently control the number of posts listed on the front page, author/category/custom post type/custom taxonomy/tag archives, search results, etc.


== Description ==

Control the number of posts that appear on the front page, search results, and author, category, tag, custom post type, custom taxonomy, and date archives, independent of each other, including specific archives.

By default, WordPress provides a single configuration setting to control how many posts should be listed in each section of your blog. This value applies for the front page listing, author listings, archive listings, category listings, tag listings, custom post type listings, custom taxonomy listings, and search results. This plugin allows you to override that value for each of those different sections.

Specifically, this plugin allows you to define limits for:

* Authors archives (the archive listing of posts for any author)
* Authors archives non-first-page (when paging through authors archives listings, number of posts listed when not on the first page)
* Author archives (the archive listing of posts for any specific author)
* Categories archives (the archive listings of posts for any category)
* Categories archives non-first-page (when paging through categories archives listings, number of posts listed when not on the first page)
* Category archive (the archive listings of posts for any specific category)
* Custom post type archives (the archive listings of posts for any specific custom post type)
* Custom taxonomy (the archive listings of posts for any specific custom taxonomy)
* Day archives (the archive listings of posts for any day)
* Day archives non-first-page (when paging through day archives listings, number of posts listed when not on the first page)
* Front page (the listing of posts on the front page of the blog)
* Front page non-first-page (when paging through front page listings, number of posts listed when not on the first page)
* Month archives (the archive listings of posts for any month)
* Month archives non-first-page (when paging through month archives listings, number of posts listed when not on the first page)
* Search results (the listing of search results)
* Search results non-first-page (when paging through search results listings, number of posts listed when not on the first page)
* Tags archives (the archive listings of posts for any tag)
* Tags archives non-first-page (when paging through tags archives listings, number of posts listed when not on the first page)
* Tag archive (the archive listings of posts for any specific tag)
* Year archives (the archive listings of posts for any year)
* Year archives non-first-page (when paging through year archives listings, number of posts listed when not on the first page)

If the limit field is empty or 0 for a particular section type, then the default post limit will apply. If the value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown). The Archives Limit value is also treated as the default limit for Day, Month, and Year archives, unless those are explicitly defined.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/custom-post-limits/) | [Plugin Directory Page](https://wordpress.org/plugins/custom-post-limits/) | [GitHub](https://github.com/coffee2code/custom-post-limits/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Whether installing or updating, whether this plugin or any other, it is always advisable to back-up your data before starting
1. Install via the built-in WordPress plugin installer. Or download and unzip `custom-post-limits.zip` inside the plugins directory for your site (typically `/wp-content/plugins/`).
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Click the plugin's `Settings` link next to its `Deactivate` link (still on the Plugins page), or click on the `Settings` -> `Post Limits` link, to go to the plugin's admin settings page. Optionally customize the limits.


== Frequently Asked Questions ==

= Does this plugin introduce additional database queries (or excessively burden the primary query) to achieve its ends? =

No. The plugin filters the posts_per_page setting value (and, when necessary, the LIMIT SQL clause) as used by the primary WordPress post query as appropriate, resulting in retrieval of only the number of posts up to the limit you specified without significant alteration of the primary query itself and without additional queries. Bottom line: this should perform efficiently.

= Is this plugin unit-tested? =

Yes.


== Screenshots ==

1. A screenshot of the plugin's admin settings page (with individual categories limits expanded) (top half of page). (Note: Screenshot hasn't been updated to include fields for defining limits for custom taxonomies.)
2. A screenshot of the plugin's admin settings page (with individual categories limits expanded) (bottom half of page). (Note: Screenshot hasn't been updated to include fields for defining limits for custom taxonomies.)


== Changelog ==

= 4.1 (2018-07-10) =
* New: Add support for defining custom limits for custom taxonomies
* Change: Update plugin framework to 048
    * 048:
    * When resetting options, delete the option rather than setting it with default values
    * Prevent double "Settings reset" admin notice upon settings reset
    * 047:
    * Don't save default setting values to database on install
    * Change "Cheatin', huh?" error messages to "Something went wrong.", consistent with WP core
    * Note compatibility through WP 4.9+
    * Drop compatibility with version of WP older than 4.7
    * Update copyright date (2018)
    * 046:
    * Fix `reset_options()` to reference instance variable `$options`
    * Note compatibility through WP 4.7+
    * Update copyright date (2017)
* Change: Modify label text for custom post type fields to put post type name at end of label
* Change: Add missing mentions of custom post type support to readme.txt and README.md
* Unit tests:
    * Change: Improve test initialization
    * Bugfix: Fix factory syntax errors for a handful of tests
    * Change: Minor code tweaks
    * Change: Minor whitespace tweaks to bootstrap
* New: Add README.md
* Change: Add GitHub link to readme
* Change: Note compatibility through WP 4.9+
* Change: Drop compatibility with versions of WP older than 4.7
* Change: Update copyright date (2018)

= 4.0.2 (2017-01-02) =
* Bugfix: Fix error in a unit test due to variable being used before being set.
* Change: Enable more error ourput for unit tests.
* Change: Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable.
* Change: Note compatibility through WP 4.7+.
* Change: Update copyright date (2017).

= 4.0.1 (2016-07-11) =
* New: Add class constant `SETTING_NAME` (to store setting name) and use it in `uninstall()`.
* Change: Update plugin framework to 045.
    * Ensure `reset_options()` resets values saved in the database.
* Change: Note compatibility through WP 4.6+.
* New: Add 'License' and 'License URI' header tags to readme.

= 4.0 (2016-06-22) =
Highlights:

This release revives active development of the plugin after many years and includes many, many changes. Backwards compatilibility has been maintained; it just handles things better and introduces a number of new features. Some notable changes:

* Introduced support for defining custom limits for custom post type archives.
* Now treat 'archives_paged_limit', if specified, as secondary fallback for paged limits for day, month, and year archives.
* Added fairly comprehensive unit tests.

Details:

* New: Add support for defining custom limits for custom post type archives.
* New: Add `get_individual_limit_setting_name()` as a helper function to determine the individual limit setting name for authors, categories, custom post types, and tags.
* New: Add `has_individual_limits()` to indicate if a setting type has individual limits.
* Change: Refactor `custom_post_limits()` handling for author, category, and tag individual limits.
* Change: Treat 'archives_paged_limit', if specified, as secondary fallback for paged limits for day, month, and year archives.
* Change: On settings page, show help text indicating the value source or default for all (now to include paged) limits.
* Change: Update plugin framework to 044.
* Change: Rearrange when certain hooks are registered.
* Change: Refactor `is_individual_limits_enabled()` slightly.
* Change: Improve singleton implementation.
    * Add `get_instance()` static method for returning/creating singleton instance.
    * Make static variable 'instance' private.
    * Make constructor protected.
    * Make class final.
    * Additional related changes in plugin framework (protected constructor, erroring `__clone()` and `__wakeup()`).
* Fix: Initialize private instance variable `$first_page_offset` to null in `custom_post_limits()` to avoid pollution from potential previous invocation.
* Fix: Explicitly declare `activation()` and `uninstall()` static.
* Fix: For `options_page_description()`, match method signature of parent class.
* New: Add unit tests.
* Change: Discontinue use of PHP4-style constructor.
* Change: Discontinue use of explicit pass-by-reference for objects.
* Change: Reformat plugin header.
* Change: Add support for language packs:
    * Set textdomain using a string instead of a variable.
    * Remove .pot file and /lang subdirectory.
    * Remove 'Domain Path' from plugin header.
* Change: Use explicit path when requiring plugin framework.
* Change: Prevent execution of code if file is directly accessed.
* Change: Minor code reformatting (spacing, bracing, conditional comparison order).
* Change: Minor documentation reformatting (spacing, punctuation).
* Change: Re-license as GPLv2 or later (from X11).
* New: Add 'License' and 'License URI' header tags to readme.txt and plugin file.
* New: Add LICENSE file.
* New: Add empty index.php to prevent files from being listed if web server has enabled directory listings.
* Change: Note compatibility through WP 4.5+.
* Change: Drop compatibility with version of WP older than 4.1.
* Change: Update donate link.
* Change: Update copyright date (2016).
* New: Add assets to plugin's Plugin Directory SVN repo.
    * Add plugin icon.
    * Add banner image.
    * Update screenshots.
    * Add third screenshot.
    * Remove screenshots from plugin package.

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/custom-post-limits/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

= 4.1 =
Recommended update: added support for setting limits for custom taxonomies; updated plugin framework to v048; compatibility is now WP 4.7-4.9; added README.md; more.

= 4.0.2 =
Trivial update: updated unit test bootstrap file, noted compatibility through WP 4.7+, and updated copyright date

= 4.0.1 =
Minor update: noted compatibility through WP 4.6+; updated plugin framework.

= 4.0 =
Recommended major update: added support to set limits for custom post types; 'archives_paged_limit' is now a fallback for paged date archives; compatibility is now WP 4.1-4.5; added unit tests; lots of backend improvements.

= 3.6 =
Recommended update: disabled support for individual archive limits by default (configurable) to help sites with lots of authors/categories/tags; noted compatibility through WP 3.3+; updated plugin framework; and more.

= 3.5 =
Recommended update: support different non-first-page limits; re-implemented display/handling of individual category/tag/author limits; noted compatibility through WP 3.2+, drop compatibility with WP 3.0; and more.

= 3.0 =
Recommended update. Highlights: verified WP 3.0 compatibility.
