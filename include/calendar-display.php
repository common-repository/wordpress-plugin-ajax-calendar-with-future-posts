<?php

class AJAX_Calendar_Future_Display
{
        function __construct() 
        {
            add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
            $this->options = get_option( 'ajax_calendar_future_options' );
        }

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
   // $calendar_output='<div id="wpCalendarFuture">';
    $calendar_output='<h4 class="widgettitle">Agenda ';
    $monthname= sprintf($calendar_caption, $wp_locale->get_month($thismonth), date('Y', $unixmonth));
    $calendar_output .='<div class="calendarpicker">' . $monthname . '</div>';
    
    //$calendar_output .='<div style="float:right"><a href="' . get_month_link($thisyear,$thismonth) .'">' .$monthname . '</a></div> ';
     $calendar_output .= '</h4>';
      $calendar_output .= "<div id='calendarpickermonths'><ul>" . wp_get_archives("type=monthly&format=html&echo=0") . "</ul></div>";
     
    $calendar_output .= '<table id="wp-calendar" summary="' . esc_attr__('Calendar') . '">';
   // <caption><a href="' . get_month_link($thisyear,$thismonth) .'">' . sprintf($calendar_caption, $wp_locale->get_month($thismonth), date('Y', $unixmonth)) . '</a></caption>
      $calendar_output .= '<thead><tr>';

    $myweek = array();

    for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
        $myweek[] = $wp_locale->get_weekday(($wdcount+$week_begins)%7);
    }

    foreach ( $myweek as $wd ) {
        $day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
        $wd = esc_attr($wd);
        $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
    }

     $calendar_output .= '</tr></thead><tfoot><tr>';

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

        
        //<poleouest display long events
      /*    $expireFieldName="date_de_fin";
       $join =      " LEFT JOIN $wpdb->postmeta dateFin
     ON ({$wpdb->posts}.ID = dateFin.post_id  and dateFin.meta_key = '" . $expireFieldName . "'  )";
        
          $long_event_query="SELECT ID, post_title, DAYOFMONTH(post_date) as dom, 
          dateFin.meta_value as dateFinValue "
        ."FROM $wpdb->posts "  . $join 
        ."WHERE  post_date  <= '$thisyear-$thismonth-$day' "
        . " AND dateFin.meta_value>= '$thisyear-$thismonth-$day'"  
        ." AND post_type = 'post' AND  (post_status = 'publish' or post_status='future')"
    ;
     // echo     "<br>" .   $long_event_query;
    $ak_post_titles_long_event = $wpdb->get_results($long_event_query); 
          if ( $ak_post_titles_long_event ) { 
               $daywithpost[] =$day;
               foreach($ak_post_titles_long_event as $ak_post_title)
              { $post_title=  esc_attr( apply_filters( 'the_title', $ak_post_title->post_title, $ak_post_title->ID ) );
               $ak_titles_for_day[$day] .= $ak_title_separator . $post_title; }
          }    */
        //</poleouest>
        
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
  //$calendar_output .= "<tr><td colspan='7'>" . poleouestEventMapDisplay($thismonth, $thisyear) . "</td></tr>";
    $calendar_output .= "\n\t</tbody>\n\t</table>";
   // $calendar_output .= "\n\t</div><!--wpCalendarFuture>";

    
    //<echo map>";"
     
    //<echo map>
    
    $cache[ $key ] = $calendar_output;
    wp_cache_set( 'get_calendar_future', $cache, 'calendar' );

    if ( $echo )
        echo apply_filters( 'get_calendar',  $calendar_output );
    else
        return apply_filters( 'get_calendar',  $calendar_output );

}
function get_calendar() {
        global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

        //add_filter( 'query', array( &$this, 'modify_calendar_query' ) );
        
        $text = self::get_calendar_future( true, false );
        
       // remove_filter( 'query', array( &$this, 'modify_calendar_query' ) );
        
        $ajax=true; //set to true if you want to enable AJAX. Disable for integration with mappress  (poleouestEventMapDisplay)
        if ($this->options["ajax_calendar_main"]["settings"]["ajax_months"])
        {
        $text = str_replace( '<td colspan="3" id="next"><a', '<td colspan="3" id="next"><a onclick="ajaxCalendar(this.href   ); return false"', $text );
        $text = str_replace( '<td colspan="3" id="prev"><a', '<td colspan="3" id="prev"><a onclick="ajaxCalendar(this.href   ); return false "', $text );}
 
        return $text;
    }
//for category
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

    function template_redirect() {
        if ( is_date() && isset( $_GET['ajax'] ) && $_GET['ajax'] == 'true' ) {
           
            
            echo $this->get_calendar();
            die();
        }
    }
}