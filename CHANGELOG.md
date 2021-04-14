# Changelog

## 4.4.1 _(2021-04-14)_
* Fix: Update plugin framework to 061 to fix a bug preventing settings from getting saved

## 4.4 _(2021-04-07)_

### Highlights:

This minor release updates the plugin framework and notes compatibility through WP 5.7+.

### Details:

* Change: Update plugin framework to 060
    * 060:
    * Rename class from `c2c_{PluginName}_Plugin_051` to `c2c_Plugin_060`
    * Move string translation handling into inheriting class making the plugin framework code plugin-agnostic
        * Add abstract function `get_c2c_string()` as a getter for translated strings
        * Replace all existing string usage with calls to `get_c2c_string()`
    * Handle WordPress's deprecation of the use of the term "whitelist"
        * Change: Rename `whitelist_options()` to `allowed_options()`
        * Change: Use `add_allowed_options()` instead of deprecated `add_option_whitelist()` for WP 5.5+
        * Change: Hook `allowed_options` filter instead of deprecated `whitelist_options` for WP 5.5+
    * New: Add initial unit tests (currently just covering `is_wp_version_cmp()` and `get_c2c_string()`)
    * Add `is_wp_version_cmp()` as a utility to compare current WP version against a given WP version
    * Refactor `contextual_help()` to be easier to read, and correct function docblocks
    * Don't translate urlencoded donation email body text
    * Add inline comments for translators to clarify purpose of placeholders
    * Change PHP package name (make it singular)
    * Tweak inline function description
    * Note compatibility through WP 5.7+
    * Update copyright date (2021)
    * 051:
    * Allow setting integer input value to include commas
    * Use `number_format_i18n()` to format integer value within input field
    * Update link to coffee2code.com to be HTTPS
    * Update `readme_url()` to refer to plugin's readme.txt on plugins.svn.wordpress.org
    * Remove defunct line of code
* Change: Move translation of all parent class strings into main plugin file
* Change: Note compatibility through WP 5.7+
* Change: Update copyright date (2021)
* Change: Tweak formatting for readme.txt changelog entry for v4.3

## 4.3 _(2020-06-16)_

### Highlights:

This release prevents plugin settings page timeouts for sites with lots of authors/categories/tags, fixes pagination page counts, omits limit fields for non-authors, adds TODO.md file, updates a few URLs to be HTTPS, expands unit testing, changes compatibility to be WP 4.9-5.4+, and more.

### Details:

* Fix: Prevent plugin settings page timeouts for sites with lots of authors, categories, and/or tags. Fixes #2.
    * Change: Prevent `get_authors()`, `get_categories()`, and `get_tags()` from calling potentially resource-intensive functions when individual limits aren't enabled
* Fix: Ensure count of total number of pages accurately accounts for potentially differing first and non-first page limits. Fixes #3.
    * New: Add `adjust_max_num_pages()` to potentially adjust main query object's max_num_pages value
    * Change: Add optional argument to `custom_post_limits()` for forcing it to behave as if query was paged
* Change: Update plugin framework to 050
    * Allow a hash entry to literally have '0' as a value without being entirely omitted when saved
    * Output donation markup using `printf()` rather than using string concatenation
    * Update copyright date (2020)
    * Note compatibility through WP 5.4+
    * Drop compatibility with version of WP older than 4.9
* New: Add TODO.md and move existing TODO list from top of main plugin file into it (and add to it)
* Change: Exclude users from being returned by `get_author()` if they don't have the 'author' role
* Change: Note compatibility through WP 5.4+
* Change: Drop compatibility for version of WP older than 4.9
* Change: Remove unnecessary `type='text/javascript'` attribute from `<script>` tag
* Change: Use `is_main_query()` instead of replicating what it does
* Change: Explicitly escape an admin URL before output within a link attribute (hardening)
* Change: Add translator comment for string with multiple placeholders
* Change: Update links to coffee2code.com to be HTTPS
* Change: Fix a few typos in inline docs
* Unit tests:
    * New: Add tests for `get_authors()`, `get_categories()`
    * Fix: Fix two tests related to individual authors limit
    * Fix: Define explicit ordering of results for `test_get_custom_taxonomy()` to avoid occasional failure
    * Change: Alter `test_tags_paged_limit()` for versatility by accepting arguments and calculating assertion expectations based on those parameters
    * Change: Call `reset_caches()` in `tearDown()`
    * Change: Use HTTPS for link to WP SVN repository in bin script for configuring unit tests (and delete commented-out code)

## 4.2.2 _(2019-12-21)_

### Highlights:

This release fixes a number of minor bugs.

### Details:

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

## 4.2.1 _(2019-12-02)_
* Fix: Minor fix to avoid a PHP notice. Props Canic.
* Change: Update unit test install script and bootstrap to use latest WP unit test repo
* Change: Note compatibility through WP 5.3+
* Change: Update copyright date (2020)

## 4.2 _(2019-04-14)_
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

## 4.1 _(2018-07-10)_
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

## 4.0.2 _(2017-01-02)_
* Bugfix: Fix error in a unit test due to variable being used before being set.
* Change: Enable more error ourput for unit tests.
* Change: Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable.
* Change: Note compatibility through WP 4.7+.
* Change: Update copyright date (2017).

## 4.0.1 _(2016-07-11)_
* New: Add class constant `SETTING_NAME` (to store setting name) and use it in `uninstall()`.
* Change: Update plugin framework to 045.
    * Ensure `reset_options()` resets values saved in the database.
* Change: Note compatibility through WP 4.6+.
* New: Add 'License' and 'License URI' header tags to readme.

## 4.0 _(2016-06-22)_

### Highlights:

This release revives active development of the plugin after many years and includes many, many changes. Backwards compatilibility has been maintained; it just handles things better and introduces a number of new features. Some notable changes:

* Introduced support for defining custom limits for custom post type archives.
* Now treat 'archives_paged_limit', if specified, as secondary fallback for paged limits for day, month, and year archives.
* Added fairly comprehensive unit tests.

### Details:

* New: Add support for defining custom limits for custom post type archives.
* New: Add `get_individual_limit_setting_name()` as a helper function to determine the individual limit setting name for authors, categories, custom post types, and tags.
* New: Add `has_individual_limits()` to indicate if a setting type has individual limits.
* Change: Refactor `custom_post_limits()` handling for author, category, and tag individual limits.
* Change: Treat `archives_paged_limit`, if specified, as secondary fallback for paged limits for day, month, and year archives.
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

## 3.6
* Update plugin framework to version 034
* Fix problem where plugin settings page won't load for sites with a lot of authors, categories, and/or tags
* Fix correct_paged_offset() to only operate against main query
* By default, disable listing of limits for individual authors, categories, and tags
* Add filter `c2c_cpl_enable_all_individual_limits` to allow enabling limits for all individual authors, categories, and tags (supersedes the specific limits)
* Add filter `c2c_cpl_enable_all_individual_author_limits` to allow enabling limits for individual authors
* Add filter `c2c_cpl_enable_all_individual_category_limits`  to allow enabling limits for individual categories
* Add filter `c2c_cpl_enable_all_individual_tag_limits`  to allow enabling limits for individual tags
* Remove support for `c2c_custom_post_limits` global
* Note compatibility through WP 3.3+
* Regenerate .pot
* Change plugin description
* Add 'Domain Path' directive to top of main plugin file
* Add link to plugin directory page to readme.txt
* Tweak installation instructions in readme.txt
* Update screenshot for WP 3.3
* Add second screenshot
* Update copyright date (2012)

## 3.5
* Add support for different paged (non-first page) limits for each section (each requires separate setting)
* Re-implement/fix display and saving of individual authors/categories/tags limit
* Hide individual author/category/tag limits by default (via JS, so still works for non-JSers)
* Update plugin framework to version 027
* Fix to properly register activation and uninstall hooks
* Save a static version of itself in class variable $instance
* Rename class from `CustomPostLimits` to `c2c_CustomPostLimits`
* Remove placeholder settings: `individual_authors`, `individual_categories`, and `individual_tags`
* Remove functions: `register_individual_archive_options()`, `pre_display_option()`
* Add functions: `save_individual_options()`, `correct_paged_offset()`
* Use `get_users()` in `get_authors()` rather than constructing query
* Explicitly declare all functions as public
* Note compatibility through WP 3.2+
* Drop compatibility with versions of WP older than 3.1
* Add more PHPDoc
* Update screenshot
* Regenerate .pot
* Minor tweaks to code formatting (spacing)
* Update copyright date (2011)

## 3.0
* Only output plugin's admin JS on its own page
* Better localization support
* Store plugin instance in global variable, `$c2c_custom_post_limits`, to allow for external manipulation
* Re-implementation by extending `C2C_Plugin_009`, which adds support for:
    * Better sanitization of input values
    * Offload of core/basic functionality to generic plugin framework
    * Additional hooks for various stages/places of plugin operation
    * Easier localization support
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Note compatibility with WP 3.0+
* Minor tweaks to code formatting (spacing)
* Add Upgrade Notice section to readme.txt
* Remove trailing whitespace

## 2.6
* Revert post limiting back to hooking `pre_option_posts_per_page` rather than filtering `post_limits` (fixes bug introduced in v2.5)
* Fix bug related to individual author/category/tag limits not applying (the primary intent of the v2.5 release, but needed re-fixing due to reversion)
* Fix bug preventing value of individual limits from appearing on settings page (the value had been saved and used properly, though)
* Add 'Reset Settings' button to facilitate resetting all limits configured via the plugin
* Internal: add `get_authors()`, `get_categories()`, `get_tags()` to retrieve and buffer those respective values if actually needed
* Update object's option buffer after saving changed submitted by user
* Add PHPDoc documentation
* Minor documentation tweaks

## 2.5
* Reverted post limiting method used to filtering `post_limits` again rather than hooking `pre_option_posts_per_page`
* Fixed bug related to individual author/category/tag limits not applying
* Changed invocation of plugin's install function to action hooked in constructor rather than in global space
* Changed unobtrusively added JavaScript click events to return false, rather than depending on an embedded JS call in link (fixes IE8 compatibility)
* Added full support for localization
* Used `admin_url()` instead of hardcoded admin path
* Removed compatibility with versions of WP older than 2.8
* Noted compatibility with WP 2.9+

## 2.0
* Changed how post limiting is achieved by hooking `pre_option_posts_per_page` rather than filtering `post_limits`
* Simplified `custom_post_limits()`
* Changed permission check to access settings page
* Used `plugins_url()` instead of hardcoded path
* Removed compatibility with versions of WP older than 2.6
* Noted compatibility with WP2.8
* Began initial effort for localization
* Fixed edge-case bug causing limiting to occur when not appropriate
* Fixed bug with tag names not appearing

## 1.5
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
* Fixed post paging (`next_posts_link()`/`previous_posts_link()`) was not taking post limit into account

## 1.0
* Initial release
