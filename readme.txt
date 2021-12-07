=== Test Plugin ===
Contributors: user
Tags: tag, tag, tag
Donate link: http://github.com/victoraigbeghian/
Requires at least: 5.0
Tested up to: 5.8.2
Requires PHP: 7.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Intercessor is a WordPress prayer request management plugin that enables you receive, review and publish prayer requests
effectively on WordPress.

== Description ==
Manage prayer request submission, requesters (those who submit prayer) and prayed counts with Intercessor. Structured options panel to effectively
configure the plugin to suit your ministry needs. When installed, the plugin creates 3 pages for prayer submission,
history and to display the published public prayer requests. Prayers can be exported as CSV, e.g. when migrating your
site, and can also be imported to another site. Also equipped with captcha and Google ReCaptcha for spam control on the
submission form.

For backwards compatibility, if this section is missing, the full length of the short description will be used, and
markdown parsed.

More features:

1. Effective requester management section.
2. Extensive reports section.
3. Prayer history page which stores each requester's prayer requests. Users need to be logged in to your site to view
the prayer list page.

Prayer requests:

* Set initial submitted prayer status to "pending." This helps you review the request before approving it.
* Send prayed for counts to users who want to be informed when they are prayed for. You can specify if they should be
informed daily, weekly or monthly.
* You can decide to archive prayer requests after a certain period of time, e.g. 3 months, 1 year, etc.

Link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].


== Installation ==

= Minimum Requirements =

* WordPress 5.0 or greater
* PHP 7.0 or greater
* MySQL 5.6 or greater

= Automatic installation =

Automatic installation is the easiest option. Log in to your WordPress dashboard, navigate to the Plugins menu, and click "Add New"

In the search field type "Intercessor", then click "Search Plugins" Once you've found us, you can install it by click "Install Now" and WordPress will take it from there.

= Manual installation =

1. Upload "intercessor" to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.

== Frequently Asked Questions ==
= A question that someone might have =
An answer to that question.

= What about foo bar? =
Answer to foo bar dilemma.

== Screenshots ==
1. The screenshot description corresponds to screenshot-1.(png|jpg|jpeg|gif).
2. The screenshot description corresponds to screenshot-2.(png|jpg|jpeg|gif).
3. The screenshot description corresponds to screenshot-3.(png|jpg|jpeg|gif).

== Changelog ==
= 1.0.0 =
* First release.

= 1.1.0 =
* First upgrade

== Upgrade Notice ==
= 1.1.0 =
Upgraded prayed counts to a new database to allow for effective management of cron Email to requesters to inform them
of their prayed counts. Deletes prayed counts from prayer meta database. Updated Email tab on plugin settings page to
allow for email templates.

= 0.1 =
This version fixes usability issues with the plugin, upgrade immediately.
