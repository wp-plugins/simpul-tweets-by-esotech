=== Plugin Name ===
Contributors: geilt
Donate link: http://www.esotech.org/
Tags: twitter, feed, widget, tweets, simpul, esotech, json, api, oauth
Requires at least: 3.4
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Enables a widget that will pull a twitter feed feed via API by Twitter @UserName and display them. 

Since Twitter updated to v1.1 OAUTH, Twitter Application credentials are now required to be registered to authorize use of the Twitter API. 

[Create an Application](https://dev.twitter.com/apps/new)

[More Details](http://www.esotech.org/plugins/simpul/simpul-tweets/)

== Installation ==

1. Upload the plugin folder into to the `/wp-content/plugins/` directory or search "Simpul Tweets by Esotech" and install.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Appearance -> Widgets
4. Drag the "Twitter" Widget into the Sidebar you want to use.
5. Make sure to enter the Twitter Consumer Key, Consumer Secret, Access Token and Access Token Secret. Choose other options as you need them.

== Frequently Asked Questions ==

= How do I get my tweets to Display? =

Look for "Twitter" under Appearance -> Widgets. Drag it into a sidebar, type your twitter name ex: esotech into tweets and choose the amount of tweets you wan't to display. Click Save.

= How does Caching Work? =

Caching keeps a local copy of your tweets at the interval specified so you don't have to constantly contact twitter. This saves on site load speed because you don't have to make an external connection.

= I am updating my widget settings, but nothing is changing, why? =

Try disabling the cache, saving, refreshing the page where you widget displays, then turning cache back on. Cache saves data into wordpress, so updating settings won't reflect until the next cache update. 

== Changelog ==
= 2.0.0 =
* Now works with OAUTH v1.1. Required Twitter Application credentials from dev.twitter.com. Configurable PER Widget so you can register multiple accounts. Including and using TwitterAPIExchange http://github.com/j7mbo/twitter-api-php
= 1.8.3 =
* Fixed something due ot Twitter API change that broke links.
= 1.8.2 =
* Class name is now simpul-tweets and ID is simpul_tweets as it should be.
= 1.8.1 =
* Cache wasnt being passed instance, which stopped it from getting the proper number of tweets. 
= 1.8.0 =
* Added many new features including showing the date, setting the date format, linking the entire element to the tweet. Also update the cURL call because Twitter apparently removed the previous method to recieve the JSON feed. The Json feed now limits the amount of tweets given back as oppossed to a counter. It may allow for extra tweets beyond the basic 20 but has not been tested. Cleaned up some other bits of code and made defaults easier for the system to manage. Update License also to GPLv3+
= 1.6.0 =
* Added Dynamic Title Element, Container Class, Tweet Element. Cleaned up code. Fixed before widget and after widget. 
= 1.5.5 =
* Changed header to h3.
= 1.5.4 =
* More code cleanup.
= 1.5.3 =
* Cleaned up cache code.
= 1.5.2 =
* Fixed PEBKAC programmer bug.
= 1.5.1 =
* Fixed a huge compatibility issue with SimpulFacebook.
= 1.5 =
* Added caching.
= 1.0 =
* First Upload
