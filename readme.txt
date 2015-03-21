=== Mongoose ===
Contributors: dxw, dgmstuart, harrym
Tags: security, security plugin, plugin security, wordpress security, security vulnerabilities, vulnerability, exploit, code review, security review, CSRF, XSS, injection, SQL injection, arbitrary code
Requires at least: 3.8.1
Tested up to: 4.1.1
Stable tag: 0.2.8
License: GPLv2 or later

MongooseWP alerts you about vulnerabilities in your plugins and shows you what they are. MongooseWP is currently in Alpha.

== Description ==

MongooseWP helps you to judge whether or not the plugins installed on your site are safe to use.

It displays information about known vulnerabilities in the plugins you're using, and sends you email alerts when new vulnerabilities are found.

MongooseWP requires a paid subscription to work. for more information, visit www.mongoosewp.com.

### Developers

If you'd like to have a good look at this plugin before you install it (which you should!), you can find the code on github (https://github.com/dxw/dxw-security-wp-plugin) on the mongoosifying_plugin branch.

== Installation ==

1. Installation options:

    * Direct zip upload: Upload the zip package (http://www.mongoosewp.com/mongoose-latest.zip) via 'Plugins > Add New > Upload' in your WP Admin
    * _OR_ via FTP upload: Upload the `dxw-security' folder to the /wp-content/plugins/ directory
    * _OR_ via git: clone this repository into your plugins directory and move to the mongoosifying_plugin branch.

2. Activate the plugin through the 'Plugins' menu in WordPress
3. Look at your plugins page - a box will show on the far right showing any vulnerabilities we're currently aware of.
4. Look at the dashboard - a box will show giving you an overview of what we've found.

== Frequently Asked Questions ==

= Do you collect information about the plugins I'm using? =

Yes. Every day, we save a list of the plugins you're using. When we find new vulnerabilities, we use this list to check if we need to send you an alert.

= How do you keep it safe? =

We realise that keeping data about vulnerable plugins and the sites using them might make us a target. We've given this a lot of thought!

Behind the scenes, there are several systems that make MongooseWP work. The systems that store user data, lists of plugins and vulnerability data are all built and hosted separately. To be able to join up data about plugin use to a particular site would require an attacker to compromise at least two systems. 

Even then, they would hopefully struggle - because we don't save the name or domain name of your site anywhere. We identify you using a randomly generated API key. But we took the steps above anyway, because we realise that a lot of customers' sites will be indirectly identified via their email address.

All that said: MongooseWP is currently in Alpha. We have a lot of experience delivering and hosting secure sites, but we don't expect you to take our word for it and MongooseWP remains under active development. 

If you have feedback, comments or concerns about the security of your data, we would love to talk to you. You can email the team on contact@mongoosewp.com.

= Why do you save plugin data at all? Why not email the alerts locally? =

We went back and forth on this one! In the end, we decided that we didn't want the reliablility of our product to depend on the mailserver configurations of hundreds of WordPress sites hosted by third parties. It's very important that we can reliably contact our customers to alert them when we find problems, and we want to maintain control of that process.
   

== Changelog ==
= 0.3.0 =
* Rebranded as MongooseWP
* Under-the-hood changes to error handling
* Add functionality to subscribe to security alerts about installed plugins 
* Remove functionality around displaying inspections and reviews

= 0.2.8 =
* The plugin now uses version 2 of the api. Mostly this involves under-the-hood changes
* Reports now include advisories (initially only those published on security.dxw.com)

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
