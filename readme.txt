=== Custom Post Limits ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: posts, archives, listing, limit, query, front page, categories, tags, coffee2code
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.7
Tested up to: 5.3
Stable tag: 4.2.2

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


== Hooks ==

The plugin exposes a number of filters for hooking. Typically, code making use of filters should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain).

**c2c_cpl_enable_all_individual_limits (filter)**

The 'c2c_cpl_enable_all_individual_limits' hook allows you to filter if individual limits are enabled for all archive types.

The ability to set individual limits (e.g. for per-author or per-category archives) isn't simply enabled by default because it can have a negative performance impact depending on the number of items. Especially for a something most sites are unlikely to need.

Arguments:

* $enabled (boolean): Enable individual limits for all archive types? Default false.

Example:

`
// Enable individual limits for all archives for Custom Post Limits plugin.
add_filter( 'c2c_cpl_enable_all_individual_limits', '__return_true' );
`

**c2c_cpl_enable_all_individual_{$type}_limits (filter)**

The 'c2c_cpl_enable_all_individual_{$type}_limits' hook allows you to filter if individual limits are enabled for a specific archive type. The dynamic portion of the hook name, `$type`, refers to the type of archive with constituent individual archives. Can be 'authors', 'categories', or 'tags'.

Arguments:

* $enabled (boolean): Enable individual limits for given archive type? Default false.

Example:

`
// Enable individual limits for author archives for Custom Post Limits plugin.
add_filter( 'c2c_cpl_enable_all_individual_authors_limits', '__return_true' );
`


== Changelog ==

= 4.2.2 (2019-12-21) =
Highlights:

This release fixes a number of minor bugs.

Details:

* Fix: Don't assume variables initialized as strings have since become arrays
* Fix: Don't call non-static method `is_individual_limits_enabled()` statically
* Fix: Use accessor functions rather than directly accessing class variables
* Fix: Don't add a show/hide link for paged limits
* Change: Don't make unnecessary consecutive calls to the same function
* Change: Explicitly cast return values of `get_authors()`, `get_categories()`, and `get_tags()` as arrays
* Change: Initialize class variables expected to be arrays as arrays
* Unit tests:
    * New: Add tests for `display_individual_option()`
    * New: Add tests for `get_tags()`
    * New: Add test that plugin initializes itself on `plugins_loaded`
* New: Add inline documentation for class variables

= 4.2.1 (2019-12-02) =
* Fix: Minor fix to avoid a PHP notice.
* Change: Update unit test install script and bootstrap to use latest WP unit test repo
* Change: Note compatibility through WP 5.3+
* Change: Update copyright date (2020)

= 4.2 (2019-04-14) =
* Change: Initialize plugin on `plugins_loaded` action instead of on load
* Change: Update plugin framework to 049
    * 049:
    * Correct last arg in call to `add_settings_field()` to be an array
    * Wrap help text for settings in `label` instead of `p`
    * Only use `label` for help text for checkboxes, otherwise use `p`
    * Ensure a `textarea` displays as a block to prevent orphaning of subsequent help text
    * Note compatibility through WP 5.1+
    * Update copyright date (2019)
* Change: Cast return value of both hooks as booleans
* New: Add CHANGELOG.md file and move all but most recent changelog entries into it
* New: Add inline documentation for hooks
* New: Unit tests: Add unit test for defaults for settings
* Change: Add 'Hooks' section to readme.txt with documentation for hooks
* Change: Note compatibility through WP 5.1+
* Change: Update copyright date (2019)
* Change: Update License URI to be HTTPS
* Change: Split paragraph in README.md's "Support" section into two

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/custom-post-limits/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

= 4.2.2 =
Recommended bugfix update: This release fixes a number of minor bugs.

= 4.2.1 =
Trivial update: modernized unit tests, noted compatibility through WP 5.3+, and updated copyright date (2020)

= 4.2 =
Minor update: tweaked plugin initialization, updated plugin framework to v049, noted compatibility through WP 5.1+, created CHANGELOG.md to store historical changelog outside of readme.txt, and updated copyright date (2019)

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
