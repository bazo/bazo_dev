jQuery(function($) {
		var block_options = {
			message: 'Please wait',
			css: {
		         border: 'none', 
	             padding: '15px', 
	             backgroundColor: '#000', 
	             '-webkit-border-radius': '10px', 
	             '-moz-border-radius': '10px', 
	             opacity: .5, 
	             color: '#fff' 
			} 
		};
		
		$(document).ajaxStart( function(){$.blockUI(block_options)}).ajaxStop($.unblockUI);
		
		$("form").live('submit',function (event) {
				$().ajaxStart( function(){
					$.blockUI(block_options)
				});
                $(this).ajaxSubmit();
				event.preventDefault();
        });
	
	    $("form :submit").live("click",function (event) {
	        $(this).ajaxSubmit();
			event.preventDefault();
	    });
    
		$('input[type="submit"]').livequery( function(){$(this).addClass('ui-state-default');}).live('mouseover', function(){$(this).addClass("ui-state-hover");}).live('mouseout',function(){$(this).removeClass("ui-state-hover");})
  			.live('mousedown',function(){$(this).addClass("ui-state-active");}).live('mouseup',function(){$(this).removeClass("ui-state-active");});
		
});