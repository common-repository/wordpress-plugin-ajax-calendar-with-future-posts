jQuery(document).ready(function(){ jQuery('.calendarpicker').live("click",function(){
	 
      var objk= jQuery('#calendarpickermonths');
     
      objk.toggle()})
});


function ajaxCalendar(theurl ){
   
    jQuery.ajax({
  url:theurl,
   data: { ajax: "true"},
  success: function(data) {
      //alert(data);
    jQuery('#wpAjaxCalendarFuture').html(data);
     
  } 
});
 
    
}