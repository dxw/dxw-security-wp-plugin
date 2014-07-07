=== dxw Security ===
Contributors: dxw, dgmstuart, harrym
Tags: security, security plugin, plugin security, wordpress security, security vulnerabilities, vulnerability, exploit, code review, security review, CSRF, XSS, injection, SQL injection, arbitrary code
Requires at least: 3.8.1
Tested up to: 3.9.1
Stable tag: 0.2.7
License: GPLv2 or later

Displays a security rating against each of the plugins you have installed

== Description ==

The dxw Security plugin helps you to judge whether or not the plugins installed on your site are safe to use.

It displays a security rating against each plugin on your plugins page, based on the security reviews on https://security.dxw.com/.

It also displays a widget on the dashboard showing the totals of each plugin review status.

### Developers

The code in WordPress' SVN is a deployable version. If you'd like to contribute to this plugin, you can find the development files on github: https://github.com/dxw/dxw-security-wp-plugin

== Installation ==

1. Installation options:

    * via Admin Dashboard: Go to 'Plugins > Add New', search for "dxw Security", click "install"
    * _OR_ via direct zip upload: Upload the zip package via 'Plugins > Add New > Upload' in your WP Admin
    * _OR_ via FTP upload: Upload the `dxw-security' folder to the /wp-content/plugins/ directory

2. Activate the plugin through the 'Plugins' menu in WordPress
3. Look at your plugins page - a box will show on the far right showing the result of security reviews of your plugins
4. Look at the dashboard - a box will show detailing the numbers of each status of review.

== Frequently Asked Questions ==

= Do you collect information about the plugins I'm using? =
In short, no. We value your privacy.

We do record aggregate numbers of requests for reviews of individual plugins (so that we can work out what we should be reviewing next)
but we don't associate these with individual users, sites or IP addresses (and we will never do so without your consent).

We only record the name of the plugin - not the version or any other specific information.

You can see the data we currently record by visiting http://app.security.dxw.com/api/plugin_requests

== Screenshots ==

1. The security ratings displayed on your plugins page
2. Information about a potentially unsafe plugin on the plugins page
3. The dashboard widget showing the security ratings of your plugins



== Changelog ==

= 0.2.7 =
* Plugin reviews are now fetched from the api through a daily wp_cron task. This warms the cache and helps to ensure consistent stats

= 0.2.6 =
* Cache keys are now namespaced to minimise the chance of name clashes and accidental overwrites
* The api now groups counts of plugin requests by the version of the dxw Sec plugin that requested it
* Minor bugfixes

= 0.2.5 =
* The api is now accessed over https

= 0.2.4 =
* Preventing full path disclosure. Contributed by [sergejmueller](http://profiles.wordpress.org/sergejmueller/). [Github pull request](https://github.com/dxw/dxw-security-wp-plugin/pull/1).

= 0.2.3 =
* Improvements to the display of the plugins page column where the current version hasn't been reviewed, but reviews exist for other versions.
* The dashboard plugin now links to the first plugin on the plugins page which has that review status

= 0.2.2 =
* Plugin reviews are now cached for 24 hours (rather than 5 minutes)

= 0.2.1 =
* Fixed a bug whereby plugins with multiple reviews weren't getting counted correctly in the dashboard widget

= 0.2.0 =
* Updated to use the new api request format: /api/directory_plugins/(:plugin_id)/reviews/(:plugin_version)
* Displays reviews of past versions as well as of the installed version
* Added a dashboard widget displaying the numbers of plugins in each state

= 0.1.0 =
* Initial version - uses api request format: /api?codex_link=(:directory_link)&version=(:plugin_version)