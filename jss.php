<?php
function ajax_calendar_future_css_and_js() {
wp_register_style('ajax_calendar_future_css', plugins_url('calendar-future.css',__FILE__ ));
wp_enqueue_style('ajax_calendar_future_css');

wp_register_script( 'ajax_calendar_future-js', plugins_url('calendar-future.js',__FILE__ ), array( 'jquery' ));
wp_enqueue_script('ajax_calendar_future-js');
}
add_action( 'wp_enqueue_scripts','ajax_calendar_future_css_and_js');