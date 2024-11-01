<?php
/*
Plugin Name: AJAX Calendar Future POSTS
Plugin URI: http://webmasterbulletin.net/wordpress-plugin-ajax-calendar/
Description: A version of the WordPress calendar that uses AJAX to allow the user to step through the months without updating the page.  Additionally, a click on the 'expand' link shows all the posts within that month, inside the calendar.  Caching of content can be enabled to increase speed. extended from John Godley's ajax calendar
Version: 1.01
Author: Erwan Pianezza   / John Godley
Author URI: http://webmasterbulletin.net/ 
*/

class AJAX_Calendar_Future_Widget extends WP_Widget {
	var $category_ids = array();
function get_calendar_future($initial = true, $echo = true) {
    global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

    $cache = array();
    $key = md5( $m . $monthnum . $year );
    if ( $cache = wp_cache_get( 'get_calendar_future', 'calendar' ) ) {
        if ( is_array($cache) && isset( $cache[ $key ] ) ) {
            if ( $echo ) {
                echo apply_filters( 'get_calendar',  $cache[$key] );
                return;
            } else {
                return apply_filters( 'get_calendar',  $cache[$key] );
            }
        }
    }

    if ( !is_array($cache) )
        $cache = array();

    // Quick check. If we have no posts at all, abort!
    if ( !$posts ) {
        $gotsome = $wpdb->get_var("SELECT 1 as test FROM $wpdb->posts WHERE post_type = 'post'  and (post_status = 'publish' or post_status='future') LIMIT 1");
        if ( !$gotsome ) {
            $cache[ $key ] = '';
            wp_cache_set( 'get_calendar_future', $cache, 'calendar' );
            return;
        }
    }

    if ( isset($_GET['w']) )
        $w = ''.intval($_GET['w']);

    // week_begins = 0 stands for Sunday
    $week_begins = intval(get_option('start_of_week'));

    // Let's figure out when we are
    if ( !empty($monthnum) && !empty($year) ) {
        $thismonth = ''.zeroise(intval($monthnum), 2);
        $thisyear = ''.intval($year);
    } elseif ( !empty($w) ) {
        // We need to get the month from MySQL
        $thisyear = ''.intval(substr($m, 0, 4));
        $d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
        $thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('${thisyear}0101', INTERVAL $d DAY) ), '%m')");
    } elseif ( !empty($m) ) {
        $thisyear = ''.intval(substr($m, 0, 4));
        if ( strlen($m) < 6 )
                $thismonth = '01';
        else
                $thismonth = ''.zeroise(intval(substr($m, 4, 2)), 2);
    } else {
        $thisyear = gmdate('Y', current_time('timestamp'));
        $thismonth = gmdate('m', current_time('timestamp'));
    }

    $unixmonth = mktime(0, 0 , 0, $thismonth, 1, $thisyear);

    // Get the next and previous month and year with at least one post
    $previous = $wpdb->get_row("SELECT DISTINCT MONTH(post_date) AS month, YEAR(post_date) AS year
        FROM $wpdb->posts
        WHERE post_date < '$thisyear-$thismonth-01'
        AND post_type = 'post'   and (post_status = 'publish' or post_status='future')
            ORDER BY post_date DESC
            LIMIT 1");
    $next = $wpdb->get_row("SELECT    DISTINCT MONTH(post_date) AS month, YEAR(post_date) AS year
        FROM $wpdb->posts
        WHERE post_date >    '$thisyear-$thismonth-01'
        AND MONTH( post_date ) != MONTH( '$thisyear-$thismonth-01' )
        AND post_type = 'post'   and (post_status = 'publish' or post_status='future')
            ORDER    BY post_date ASC
            LIMIT 1");

    /* translators: Calendar caption: 1: month name, 2: 4-digit year */
    $calendar_caption = _x('%1$s %2$s', 'calendar caption');
    $calendar_output = '<table id="wp-calendar" summary="' . esc_attr__('Calendar') . '">
    <caption><a href="' . get_month_link($thisyear,$thismonth) .'">' . sprintf($calendar_caption, $wp_locale->get_month($thismonth), date('Y', $unixmonth)) . '</a></caption>
    <thead>
    <tr>';

    $myweek = array();

    for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
        $myweek[] = $wp_locale->get_weekday(($wdcount+$week_begins)%7);
    }

    foreach ( $myweek as $wd ) {
        $day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
        $wd = esc_attr($wd);
        $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
    }

    $calendar_output .= '
    </tr>
    </thead>

    <tfoot>
    <tr>';

    if ( $previous ) {
        $calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . get_month_link($previous->year, $previous->month) . '" title="' . sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($previous->month), date('Y', mktime(0, 0 , 0, $previous->month, 1, $previous->year))) . '">&laquo; ' . $wp_locale->get_month_abbrev($wp_locale->get_month($previous->month)) . '</a></td>';
    } else {
        $calendar_output .= "\n\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
    }

    $calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';

    if ( $next ) {
        $calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . get_month_link($next->year, $next->month) . '" title="' . esc_attr( sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($next->month), date('Y', mktime(0, 0 , 0, $next->month, 1, $next->year))) ) . '">' . $wp_locale->get_month_abbrev($wp_locale->get_month($next->month)) . ' &raquo;</a></td>';
    } else {
        $calendar_output .= "\n\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
    }

    $calendar_output .= '
    </tr>
    </tfoot>

    <tbody>
    <tr>';

    // Get days with posts
    $dayswithposts = $wpdb->get_results("SELECT DISTINCT DAYOFMONTH(post_date)
        FROM $wpdb->posts WHERE MONTH(post_date) = '$thismonth'
        AND YEAR(post_date) = '$thisyear'
        AND post_type = 'post' AND (post_status = 'publish' or post_status='future')", ARRAY_N);
      //  AND post_date < '" . current_time('mysql') . '\'', ARRAY_N);
    if ( $dayswithposts ) {
        foreach ( (array) $dayswithposts as $daywith ) {
            $daywithpost[] = $daywith[0];
        }
    } else {
        $daywithpost = array();
    }

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'camino') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false)
        $ak_title_separator = "\n";
    else
        $ak_title_separator = ', ';

    $ak_titles_for_day = array();
    $ak_post_titles = $wpdb->get_results("SELECT ID, post_title, DAYOFMONTH(post_date) as dom "
        ."FROM $wpdb->posts "
        ."WHERE YEAR(post_date) = '$thisyear' "
        ."AND MONTH(post_date) = '$thismonth' "
       // ."AND post_date < '".current_time('mysql')."' "
        ."AND post_type = 'post' AND  (post_status = 'publish' or post_status='future')"
    );
    if ( $ak_post_titles ) {
        foreach ( (array) $ak_post_titles as $ak_post_title ) {

                $post_title = esc_attr( apply_filters( 'the_title', $ak_post_title->post_title, $ak_post_title->ID ) );

                if ( empty($ak_titles_for_day['day_'.$ak_post_title->dom]) )
                    $ak_titles_for_day['day_'.$ak_post_title->dom] = '';
                if ( empty($ak_titles_for_day["$ak_post_title->dom"]) ) // first one
                    $ak_titles_for_day["$ak_post_title->dom"] = $post_title;
                else
                    $ak_titles_for_day["$ak_post_title->dom"] .= $ak_title_separator . $post_title;
        }
    }


    // See how much we should pad in the beginning
    $pad = calendar_week_mod(date('w', $unixmonth)-$week_begins);
    if ( 0 != $pad )
        $calendar_output .= "\n\t\t".'<td colspan="'. esc_attr($pad) .'" class="pad">&nbsp;</td>';

    $daysinmonth = intval(date('t', $unixmonth));
    for ( $day = 1; $day <= $daysinmonth; ++$day ) {
        if ( isset($newrow) && $newrow )
            $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
        $newrow = false;

        if ( $day == gmdate('j', current_time('timestamp')) && $thismonth == gmdate('m', current_time('timestamp')) && $thisyear == gmdate('Y', current_time('timestamp')) )
            $calendar_output .= '<td id="today">';
        elseif ( in_array($day, $daywithpost) ) 
          $calendar_output .= '<td class="active">';
        else
            $calendar_output .= '<td>';

        if ( in_array($day, $daywithpost) ) // any posts today?
                $calendar_output .= '<a href="' . get_day_link($thisyear, $thismonth, $day) . "\" title=\"" . esc_attr($ak_titles_for_day[$day]) . "\">$day</a>";
        else
            $calendar_output .= $day;
        $calendar_output .= '</td>';

        if ( 6 == calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins) )
            $newrow = true;
    }

    $pad = 7 - calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins);
    if ( $pad != 0 && $pad != 7 )
        $calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';
        

    $calendar_output .= "\n\t</tr>";
    $calendar_output .= "<tr><td colspan='7'>" . poleouestEventMapDisplay($thismonth, $thisyear) . "</td></tr>";
    $calendar_output .= "\n\t</tbody>\n\t</table>";

    
    //<echo map>";"
     
    //<echo map>
    
    $cache[ $key ] = $calendar_output;
    wp_cache_set( 'get_calendar_future', $cache, 'calendar' );

    if ( $echo )
        echo apply_filters( 'get_calendar',  $calendar_output );
    else
        return apply_filters( 'get_calendar',  $calendar_output );

}
 
	function AJAX_Calendar_Future_Widget() {
		$widget_ops  = array( 'classname' => 'ajax_calendar_future_widget', 'description' => __( 'AJAX Calendar with Future Posts', 'ajax-calendar-future' ) );
		$control_ops = array( 'width' => 300, 'height' => 300 );

		$this->WP_Widget( 'ajax-calendar-future', __( 'AJAX Calendar Future', 'ajax-calendar-future' ), $widget_ops, $control_ops );

		add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
	}
	
	function template_redirect() {
		if ( is_date() && isset( $_GET['ajax'] ) && $_GET['ajax'] == 'true' ) {
			$settings = $this->get_settings();
			$settings = $settings[$this->number];
			
			$instance     = wp_parse_args( $settings, array( 'title' => __( 'AJAX Calendar', 'ajax-calendar' ), 'category_id' => '' ) );
			$this->category_ids = array_filter( explode( ',', $instance['category_id'] ) );
			
			echo $this->get_calendar();
			die();
		}
	}
	
	/**
	 * Display the widget
	 *
	 * @param string $args Widget arguments
	 * @param string $instance Widget instance
	 * @return void
	 **/
	function widget( $args, $instance ) {
		extract( $args );
	
		$instance     = wp_parse_args( (array)$instance, array( 'title' => __( 'AJAX Calendar', 'ajax-calendar' ), 'category_id' => '' ) );
		$title        = apply_filters( 'widget_title', $instance['title'] );
		$category_id  = $instance['category_id'];

		$this->category_ids = array_filter( explode( ',', $category_id ) );
		
		echo $before_widget;
	
		if ( $title )
			echo $before_title . stripslashes( $title ) . $after_title;

		echo $this->get_calendar();

		// MicroAJAX: http://www.blackmac.de/index.php?/archives/31-Smallest-JavaScript-AJAX-library-ever!.html
?>
<script type="text/javascript">
 
function microAjax(url,cF){
    alert("");
    $.ajax({
  url:url,
  success: function(data) {
    $('#wp-calendar').html(data);
    alert('Load was performed.');
  }
});
    
    
}
</script>
<?php
		// After
		echo $after_widget;
	}
	
	function get_calendar() {
		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

		add_filter( 'query', array( &$this, 'modify_calendar_query' ) );
		
		$text = $this->get_calendar_future( true, false );
		
		remove_filter( 'query', array( &$this, 'modify_calendar_query' ) );
		
		$text = str_replace( '<td colspan="3" id="next"><a', '<td colspan="3" id="next"><a onclick="microAjax(this.href + \'?ajax=true\',show_micro_ajax); return false"', $text );
		$text = str_replace( '<td colspan="3" id="prev"><a', '<td colspan="3" id="prev"><a onclick="microAjax(this.href + \'?ajax=true\',show_micro_ajax); return false"', $text );
		return $text;
	}
		
	function modify_calendar_query( $query ) {
		if ( !empty( $this->category_ids ) ) {
			global $wpdb;
			
			$query = str_replace( 'WHERE', "LEFT JOIN {$wpdb->prefix}term_relationships ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id INNER JOIN {$wpdb->prefix}term_taxonomy ON ({$wpdb->prefix}term_relationships.term_taxonomy_id = {$wpdb->prefix}term_taxonomy.term_taxonomy_id AND {$wpdb->prefix}term_taxonomy.taxonomy='category') WHERE", $query );
			if ( strpos( $query, 'ORDER' ) !== false )
				$query = str_replace( "ORDER", "AND {$wpdb->prefix}term_taxonomy.term_id IN (".implode (',', $this->category_ids ).') ORDER', $query );
			else
				$query .= "AND {$wpdb->prefix}term_taxonomy.term_id IN (".implode (',', $this->category_ids ).')';
		}

		return $query;
	}
	
	/**
	 * Display config interface
	 *
	 * @param string $instance Widget instance
	 * @return void
	 **/
	function form( $instance ) {
		$instance = wp_parse_args( (array)$instance, array( 'title' => __( 'AJAX Calendar Future Posts', 'ajax-calendar-future' ), 'category_id' => '' ) );

		$title        = stripslashes( $instance['title'] );
		$category_id  = $instance['category_id'];

		?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ajax-calendar' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
<p><label for="<?php echo $this->get_field_id( 'category_id' ); ?>"><?php _e( 'Category IDs:', 'ajax-calendar' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'category_id' ); ?>" name="<?php echo $this->get_field_name( 'category_id' ); ?>" type="text" value="<?php echo esc_attr( $category_id ); ?>" /></label></p>
		<?php
	}
		
	/**
	 * Save widget data
	 *
	 * @param string $new_instance
	 * @param string $old_instance
	 * @return void
	 **/
	function update( $new_instance, $old_instance ) {
		$instance     = $old_instance;
		$instance = wp_parse_args( (array)$instance, array( 'title' => __( 'AJAX Calendar Future Posts', 'ajax-calendar-future' ), 'category_id' => '' ) );

		$instance['title']        = wp_filter_nohtml_kses( $new_instance['title'] );
		$instance['category_id']  = implode( ',', array_filter( array_map( 'intval', explode( ',', $new_instance['category_id'] ) ) ) );
		
		return $instance;
	}
}

function register_ajax_calendar_future_widget() {
	register_widget( 'AJAX_Calendar_Future_Widget' );
}

add_action( 'widgets_init', 'register_ajax_calendar_future_widget' );

function ajax_calendar_future ($categories = '') {
	// $calendar = AJAX_Calendar::get ();
	// $calendar->show ( $categories );
}


//allow display of future posts in calendar (archive post page and in single post)

class WB_PostEvent // 
{
    
    
    public function __construct()
    {
    add_filter( 'pre_get_posts', 'wb_get_future_posts' );
    add_filter('the_posts', 'show_future_posts');
  add_action('admin_menu',array(&$this, 'wp_add_menu'));
    }
    function wp_add_menu() {      //from private
    add_submenu_page('options-general.php', 'WB POST EVENT','WB POST EVENT', 10, __FILE__, array($this, 'optionsPage'));
    }
    function optionsPage()
    {}
}
function wb_get_future_posts( $query ) {

   
     if ((  is_date() || is_day()) &&  false == $query->query_vars['suppress_filters'] )
        {
         
$query->set( 'post_status',  'publish,future') ;
         }
        

    return $query;
}

$_wb_postevent = new WB_PostEvent();
function show_future_posts($posts)
{
   global $wp_query, $wpdb;
 
   if(is_single() && $wp_query->post_count == 0)
   {
      $posts = $wpdb->get_results($wp_query->request);
   }

   return $posts;
}

 function poleouestEventMapDisplay($month="",$year="")
 {
           global $mappress, $wp_query; 
   
       $agendaCat="agenda";  
    wp_reset_query();
  // remove_filter('pre_get_posts', 'poleouest_exclude_category');
    // add_filter( 'posts_where', 'poleouest_filter_where_events' );
      //add_filter( 'posts_join', 'poleouest_posts_join_events' ); 
     //      
      if ($month!="" && $year!="") $date="&monthnum=" . $month . "&year="  . $year; else $date="";
     query_posts("post_status=publish,future&posts_per_page=-1&order=asc" . $date); // attention effecture  2 fois la requete
 
//$atts = array('width'=>300, 'height'=>250, "show"=>"query","show_query"=>"category_name=$agendaCAt&post_status=future","marker_title"=>"postdate","marker_body"=>  "excerpt", "initialopeninfo"=>false); 
$atts = array('width'=>300, 'height'=>250, "show"=>"current","marker_link"=>false, "marker_title"=>"postdate","marker_body"=>  "excerpt" , "initialopeninfo"=>false); 
return  $mappress->shortcode_mashup($atts);  
 }