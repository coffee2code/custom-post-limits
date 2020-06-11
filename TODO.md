# TODO

The following list comprises ideas, suggestions, and known issues, all of which are in consideration for possible implementation in future releases.

***This is not a roadmap or a task list.*** Just because something is listed does not necessarily mean it will ever actually get implemented. Some might be bad ideas. Some might be impractical. Some might either not benefit enough users to justify the effort or might negatively impact too many existing users. Or I may not have the time to devote to the task.

* Extract post limit determination logic from `custom_post_limits()` into `get_custom_limit( $type, $specific = null)`
  (where specific can be a number to indicate a particular cat/tag/author or 'paged')
* Document in readme the order of precedence for limit fallbacks
* For Author, Category, and Tag individual limits, either show the fields via show/hide if they are within a reasonable amount, or don't show at all and warn user that they have too many. The checkboxes are still needed so keep them. Add filters so that even if not shown, individual limits can be added programatically. This removes the potential for the user to cause the page to timeout. Could also consider another set of hooks to indicate the maximum number of items to safely display, so they could up it if they want.
* Add more unit tests
* Replace direct variable reference `$this->$type`

Feel free to make your own suggestions or champion for something already on the list (via the [plugin's support forum on WordPress.org](https://wordpress.org/support/plugin/custom-post-limits/) or on [GitHub](https://github.com/coffee2code/custom-post-limits/) as an issue or PR).