<?php
class WB_PostEvent // 
{
    
    
    public function __construct()
    {
    add_filter( 'pre_get_posts',array( &$this, 'wb_get_future_posts' )   );
    add_filter('the_posts', array( &$this, 'show_future_posts' )  );
  add_filter('getarchives_where',array( &$this, 'wb_customarchives_where'));


    }
function wb_customarchives_where($x) {
    
    return " WHERE post_type = 'post' AND (post_status = 'publish' or post_status = 'future')";
}     

function wb_get_future_posts( $query ) {

   if (!isset( $query->query_vars['suppress_filters'] ))
     if ((  is_date() || is_day() ||is_category())  )
        {
         $query->set( 'post_status',  'publish,future') ;
         }
        

    return $query;
}


function show_future_posts($posts)
{
   global $wp_query, $wpdb;
 
   if(is_single() && $wp_query->post_count == 0)
   {
      $posts = $wpdb->get_results($wp_query->request);
   }

   return $posts;
}

}$_wb_postevent = new WB_PostEvent();