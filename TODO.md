# TODO

The following list comprises ideas, suggestions, and known issues, all of which are in consideration for possible implementation in future releases.

***This is not a roadmap or a task list.*** Just because something is listed does not necessarily mean it will ever actually get implemented. Some might be bad ideas. Some might be impractical. Some might either not benefit enough users to justify the effort or might negatively impact too many existing users. Or I may not have the time to devote to the task.

* Extract post limit determination logic from `custom_post_limits()` into `get_custom_limit( $type, $specific = null)`
  (where `$specific` can be a number to indicate a particular cat/tag/author or 'paged')
* Document in readme the order of precedence for limit fallbacks
* For Author, Category, and Tag individual limits, either show the fields via show/hide if they are within a reasonable amount, or don't show at all and warn user that they have too many. The checkboxes are still needed so keep them. Add filters so that even if not shown, individual limits can be added programatically. This removes the potential for the user to cause the page to timeout. Could also consider another set of hooks to indicate the maximum number of items to safely display, so they could up it if they want.
* Add more unit tests
* Replace direct variable reference `$this->$type`
* Consider using a notice-like rendering on settings page for instances where a message is reporting the current inherited limit e.g. "Tags Limit of 4 is being used."
* Provide a mechanism for disabling individual limits when loading settings page for cases where individual limits got enabled and now prevent settings page from loading, e.g. query parameter, constant. Or auto-detect pageload failure (e.g. set a flag prior to doing the get_authors()/get_categories()/get_tags() calls, then unset after; if on a pageload the flag is already set, assume the page never fully loaded so disable the settings) Disabling should be in the form of functionally disabling them on the page, but not actually changing the setting values; the checkboxes should still reflect their values even though the page will not show individual limits.
* Add help text to individual limit checkboxes to indicate that once the form must is saved, a link will appear to toggle display of individual (authors|categories|tags).
* Enqueue JS rather than outputting directly from PHP

Feel free to make your own suggestions or champion for something already on the list (via the [plugin's support forum on WordPress.org](https://wordpress.org/support/plugin/custom-post-limits/) or on [GitHub](https://github.com/coffee2code/custom-post-limits/) as an issue or PR).