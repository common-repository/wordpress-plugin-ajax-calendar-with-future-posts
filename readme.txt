=== Plugin Name ===
Contributors: erwanpia
Donate link: http://webmasterbulletin.net
Tags: ajax, calendar, widget, event, future
Requires at least: 2.9
Tested up to: 5.2.2
Stable tag: 1.3.5		

AJAX Calendar is a plugin that will display an AJAXified WordPress calendar, include future posts for usage in event calendar.

== Description ==

AJAX Calendar is a plugin that will display an AJAXified WordPress calendar, include future posts for usage in event calendar. This enhances the functionality of the standard calendar by:

* Allowing the asynchronous navigation of months, without updating the page
* Added to blog as a widget
* displays future posts and includes query filters to allow display of posts even for planified/ future posts
* link to month archive
* display filter ajax_calendar_future_calendar_after can be used to add theme specific content below the calendar, like a google map of event (check out tiarvro.org for demo)
* documentation and plugin details : http://www.webmasterbulletin.net/wordpress-plugin-ajax-calendar-with-future-posts
* this plugin is the basis for our WPFESTIVAL theme development project : http://wpfestival.com. Bring your suggestions !

== Installation ==

The plugin is simple to install:

1. Download the zip file
1. Unpack the zip. You should have a directory called `ajax_calendar_future`, containing several PHP files
1. Upload the `ajax_calendar_future` directory to the `wp-content/plugins` directory on your WordPress installation. It is important you retain the `ajax_calendar_future` directory structure
1. Activate plugin and move  the AJAX CALENDAR widget in a sidebar
1. go to Settings /  Ajax Calendar Configuration Screen and check AJAX if you want bottom links to reload the calendar with AJAX
 

== Frequently Asked Questions ==

= Does this plugin provide any scheduling functionality? =

Yes, in fact it is a simple plugin that alters the main post query to let it display   posts with a date in the future, which wordpress in its default setting only shows to the identified author

= how do I customize the active day color =

The active day color is the most important stuff you want to start customizing after successfully installing the plugin
include the following in your css, replace the color by your main theme color : #wp-calendar td.active {background-color: #FFE10C;}

= It the plugin compatible with WPML  =

not 100% compatible  : can work with WPML but with issues if post dates differ in languages

== Screenshots ==

1. Example screenshot

== Documentation ==

Full documentation can be found on the [AJAX Calendar](http://webmasterbulletin.net/) page.

== Changelog ==
= 1.3.4 =
* just fix the pro include error

= 1.3.3 =
* [TODO] display post month if viewing post
* [TODO PRO ?] custom post type query / custom post type archive
* 20130902 get_archive only going in the past
* 20130902 apply ajax_calendar_future_calendar_after filter
* 20130901 activate today's date if has posts

= 1.3.2 =
* send back missing directories

= 1.3.1 =
* fix svn issues

= 1.3 =
* 20130827 
* add jquery dependency in jss.php
* fix ajax reload bug containeer
* fix ajax calendar popup bug (live event in javascript)
* calendar popup border

= 1.2   =
* refactor the plugin code for readability


= 1.01   =

* add the td.active class for days with posts

= 1.0   =
adaptation from AJAX CALENDAR
 