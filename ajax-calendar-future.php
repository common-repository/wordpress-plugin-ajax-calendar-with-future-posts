<?php
/*
Plugin Name: AJAX Calendar Future POSTS
Plugin URI: http://webmasterbulletin.net/wordpress-plugin-ajax-calendar/
Description: A version of the WordPress calendar that uses AJAX to allow the user to step through the months without updating the page.  Additionally, a click on the 'expand' link shows all the posts within that month, inside the calendar.  Caching of content can be enabled to increase speed. extended from John Godley's ajax calendar
Version: 1.3.5	
Author: Erwan Pianezza   
Author URI: http://webmasterbulletin.net/ 
*/ 
$pluginDir = plugin_dir_path( __FILE__ );
 	
//include($pluginDir ."pro/pro.php"); //pro version only

include("jss.php");
include("include/query.php");
include("include/calendar-display.php");
include("include/widget.php");
//include("include/admin-page-framework/admin-page-framework-init.php");