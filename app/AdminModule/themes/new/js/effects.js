var x = 0;
var y =0;
var interval = parseInt({{$session_expiration}}*1000);
var timeout = null;
function ResetTimeout()
		{
			timeout = setTimeout(function(){
				jQuery.get('{{$login_url}}');
				clearTimeout(timeout);
			}, interval );
		}	
	
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
			},
			overlayCSS:  { 
		        backgroundColor: '#fff', 
		        opacity:         0 
		    }
		};
		
		$(document).ajaxStart( function(){$.blockUI(block_options)}).ajaxStop($.unblockUI);
		/*
		$("form").live('submit',function (event) {
				$().ajaxStart( function(){
					$.blockUI(block_options)
				});
				//$.blockUI();
                $(this).ajaxSubmit();
				event.preventDefault();
        });
	*/
	    $('form input:submit').live("click",function (event) {
	        $(this).ajaxSubmit();
		event.preventDefault();
	    });
	    
	    $('form input[name="btnClose"]').live("click",function (event) {
                //$('#snippet--form :first-child').hide('drop',{},'fast', function(){ $('#snippet--form').html('');});
                $('#snippet--form').html('');
		//event.preventDefault();
	    });
    
		$('a.ajax, a.datagrid-ajax').live('click', function(event) {
                        x = event.pageX;
                        y = event.pageY;
                        //if (e.button != 0) return true;
			$.get(this.href);
			event.preventDefault();
		});
		/*
		$(".inline-edit").editInPlace({
        	url: "?do=edit",
        	show_buttons: true
    	});
		*/
		
		$('#frmlangSelector-lang').livequery('change', function(){$(this).parent('form').submit()});
		
		ResetTimeout(interval);	
});