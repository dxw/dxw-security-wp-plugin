=== dxw Security ===
Contributors: dxw, dgmstuart
Tags: security, security plugin, plugin security, wordpress security, security vulnerabilities, vulnerability, exploit, code review, security review, CSRF, XSS, injection, SQL injection, arbitrary code
Requires at least: 3.8.1
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv2 or later

Displays a security rating against each of the plugins you have installed

== Description ==

The dxw Security plugin helps you to judge whether or not the plugins installed on your site are safe to use.

It displays a security rating against each plugin on your plugins page, based on the security reviews on https://security.dxw.com/.

It also displays a widget on the dashboard showing the totals of each plugin review status.

== Installation ==

1. Installing alternatives:

    * via Admin Dashboard: Go to 'Plugins > Add New', search for "dxw Security", click "install"
    * _OR_ via direct zip upload: Upload the zip package via 'Plugins > Add New > Upload' in your WP Admin
    * _OR_ via FTP upload: Upload the `dxw-security' folder to the /wp-content/plugins/ directory

2. Activate the plugin through the 'Plugins' menu in WordPress
3. Look at your plugins page - a box will show on the far right showing the result of security reviews of your plugins
4. Look at the dashboard - a box will show detailing the numbers of each status of review.

== Screenshots ==

1. The security ratings displayed on your plugins page
2. Information about a potentially unsafe plugin on the plugins page
3. The dashboard widget showing the security ratings of your plugins


== Changelog ==

= 0.2.0 =
* Updated to use the new api request format: /api/directory_plugins/(:plugin_id)/reviews/(:plugin_version)
* Displays reviews of past versions as well as of the installed version
* Added a dashboard widget displaying the numbers of plugins in each state

= 0.1.0 =
* Initial version - uses api request format: /api?codex_link=(:directory_link)&version=(:plugin_version)