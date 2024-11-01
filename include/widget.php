<?php
/**
 * Calendar widget class
 *
 * @since 2.8.0
 */
class AJAX_Calendar_Future_Widget extends WP_Widget {

	function __construct() {
		$desc =  __( 'AJAX Calendar Future', 'ajax-calendar-future' ) ;
		$widget_ops = array('classname' => 'ajax_calendar_future_widget', 'description' =>$desc);
		$this->ajaxCalendarFuture = new AJAX_Calendar_Future_Display();
		parent::__construct('ajax_calendar_future_widget', $desc, $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo '<div id="wpAjaxCalendarFuture">';
		
		echo $this->ajaxCalendarFuture ->get_calendar();
			echo apply_filters('ajax_calendar_future_calendar_after', '' );
		echo '</div>';
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
	}
}
function register_ajax_calendar_future_widget() {
	register_widget( 'AJAX_Calendar_Future_Widget' );
}

add_action( 'widgets_init', 'register_ajax_calendar_future_widget' );
