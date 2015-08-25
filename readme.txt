=== Faster with Stats ===
Contributors: easycpmods
Donate link: http://www.easycpmods.com
Tags: speed, load, faster, stats, jobroller, clipper, classipress, slow, lightweight
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 1.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Faster with Stats is a lightweight plugin that will make your AppThemes theme load faster. It works with <a href="https://www.appthemes.com/themes/classipress/" target="_blank">Classipress</a>, <a href="https://www.appthemes.com/themes/clipper/" target="_blank">Clipper</a> and <a href="https://www.appthemes.com/themes/jobroller/" target="_blank">Jobroller</a>. 

== Description ==

There is a table in <a href="https://www.appthemes.com/?aid=26553" target="_blank">Appthemes</a> <a href="https://www.appthemes.com/themes/classipress/?aid=26553" target="_blank">Classipress</a>, <a href="https://www.appthemes.com/themes/clipper/?aid=26553" target="_blank">Clipper</a> and <a href="https://www.appthemes.com/themes/jobroller/?aid=26553" target="_blank">Jobroller</a> installation that can become huge after some time. This table stores daily counters for every ad. Here is a plugin to speed up your AppThemes installation called <strong>Faster with Stats</strong> that will clear this table on daily basis (or manually) with parameters that you specify.

This table is used for showing daily hits per ad, so moving old values has no effect on total statistics, because for that purpose there is another table.
If you are not using history data of daily statistic for some extensive reports, you don't need this data. And this table can get really huge. My table had more than 122.000 records and was slowing my site down.

Why is your site getting slower and slower? This table stores hit counts for every ad on daily basis, which means for every ad a new record is added every day if the ad was seen by anyone on that day. So, if you have many ads on your site, it could mean that even 1000 records will be added to this table daily, so in a few months this table will have more than 100.000 records.

It doesn't sound a lot, but here is what happens when a user visits your web page:<br>
Default theme in Classipress uses 3 tabs on front page and on every tab there are 10 ads by default. So this means that there will be 30 selects only on this table for every customer and this is a big impact on SQL server.

There is also a table that stores total counters for posts, and the records are not getting clared after a post is deleted. So, this plugin will also optimize that table.

<a href="https://www.appthemes.com/?aid=26553" target="_blank">Appthemes</a> <a href="https://www.appthemes.com/themes/classipress/?aid=26553" target="_blank">Classipress</a>, <a href="https://www.appthemes.com/themes/clipper/?aid=26553" target="_blank">Clipper</a> or <a href="https://www.appthemes.com/themes/jobroller/?aid=26553" target="_blank">Jobroller</a> child theme is required to be installed on your WordPress for this plugin to work.

== Installation ==

1. Extract the folder into your WordPress plugins directory
2. Enable the plugin
3. Config the plugin under Settings->Faster with Stats
 
== Frequently Asked Questions ==
Waiting for first question.

== Screenshots ==

1. Main functionality of the plugin - gained speed
2. Number of hits you are getting on your posts - PRO version
3. Statuses of posts - PRO version
4. Top users - PRO version
5. Options dialog

== Changelog ==

= 1.1.3 =
* Small correction for WP 4.3

= 1.1.2 =
* Fixed a bug with css formating

= 1.1.1 =
* Added performance checking for total counts table
 
= 1.0.5 =
* Fixed statistics about speed
* Data removal is moved to uninstall stage
* Minor GUI changes

= 1.0.4 =
* Added new statistic about dead users (users without posts)

= 1.0.3 =
* Added links to user's ads and user profile on the top user table

= 1.0.0 =
* Initial version

== Upgrade Notice ==
No special care is required for upgrade.

== A brief Markdown Example ==

Feature list:

<strong>Basic version</strong>

* Speed up your <a href="https://www.appthemes.com/?aid=26553" target="_blank">Appthemes</a> <a href="https://www.appthemes.com/themes/classipress/?aid=26553" target="_blank">Classipress</a>, <a href="https://www.appthemes.com/themes/clipper/?aid=26553" target="_blank">Clipper</a> or <a href="https://www.appthemes.com/themes/jobroller/?aid=26553" target="_blank">Jobroller</a> instalation
* Show you how much time you gained with this plugin
* Language files if you would like to translate the plugin
* Moving of data can only be performed manually
* A graph showing you how much time you have gained with this plugin in last 3 months
* Option to move the data back on plugin deactivation

<strong>PRO version</strong>

Has the same functions as Basic version plus:

* Moving of data <strong>automatically on daily basis - recommended</strong>
* Show you useful <strong>statistics</strong> that you can not see from main <a href="https://www.appthemes.com/?aid=26553" target="_blank">Appthemes</a> <a href="https://www.appthemes.com/themes/classipress/?aid=26553" target="_blank">Classipress</a>, <a href="https://www.appthemes.com/themes/clipper/?aid=26553" target="_blank">Clipper</a> or <a href="https://www.appthemes.com/themes/jobroller/?aid=26553" target="_blank">Jobroller</a> installation
* A graph about daily hits on posts also with number of daily shown posts
* A graph showing you posts from users grouped by status of the post
* A graph showing you user data like registered users by time, number of ads by users, number of dead users and also a table of top users

To purchase a <strong>PRO version</strong> please visit plugin's <a href="http://www.easycpmods.com/plugin-faster-with-stats">website</a>.