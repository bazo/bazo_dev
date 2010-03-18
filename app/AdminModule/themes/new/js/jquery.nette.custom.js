/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright  Copyright (c) 2009 Jan Marek
 * @copyright  Copyright (c) 2009 David Grudl
 * @license    MIT
 * @link       http://nettephp.com/cs/extras/jquery-ajax
 */

/*
if (typeof jQuery != 'function') {
	alert('jQuery was not loaded');
}
*/
function log() {
    if ($.fn.ajaxSubmit.debug && window.console && window.console.log)
        window.console.log(Array.prototype.join.call(arguments,''));
};
jQuery.extend({
	nette: {
		updateSnippet: function (id, html) {
			$("#" + id).html(html);
		},

		executeCmd: function(cmd, payload){
			//jQuery.unblockUI();
			switch(cmd)
			{
			case 'reload':
			  window.location.reload();
			 break;
			 
			 case 'login':
			 	var form = payload.snippets['snippet--formLoginAjax'];
				//$('frmformLoginAjax-btn_login').live('click', funct)
				
			 	jQuery.blockUI({message: '<div id="ajax_login_form">'+form+'</div>'});   
				$("#frmformLoginAjax-btn_login").closest('form').effect('highlight').live('submit',function () {
					$().ajaxStart( function(){
						$.blockUI({message: ''});
					});
	                $(this).ajaxSubmit();
	               // return false;
        		});        
			 break;
			 
			 case 'login_ok':
				ResetTimeout();
			 break;
			 
			 case 'preview':
				Shadowbox.open({
                                    content:    payload.snippets['snippet--preview'],
                                    player:     "html",
                                    title:      "Preview",
                                    width:      1440,
                                            height:     900,
                                            modal: 		true
                                });
			    
			break;
			
			case 'closePreview':
				Shadowbox.close();
			break;
			
			case 'copyToEditor':
				$('*', $('#frame-editor').contents().find('#page-content')).draggable( 'disable' );
				document.getElementById('frame-editor').contentWindow.disableDragging(); 
				var html = $('#frame-editor').contents().find('#page-content').html();
				html = jQuery.trim(html);
				$('#frmnew-content').val(html);
			break;
			 
			case 'openForm':
                            //$('#pageEditor').show('clip');
			break; 

            case 'openConfirmationDialog':
                $('.confirm_dialog').css('visibility', 'visible');
                $('.confirm_dialog').show('fast');
                var width = $('.confirm_dialog').width();
                var height = $('.confirm_dialog').height();
                var newX = x - width - 100;
                var newY = y - height - 100;
                $('.confirm_dialog').css('left', newX+'px');
                $('.confirm_dialog').css('top', newY+'px');
            break;
			 
			default:
                        break;
			}
		},

		success: function (payload) {

			jQuery.unblockUI();
			//commands
            // redirect
            if (payload.redirect) {
                window.location.href = payload.redirect;
                return;
            }
			// flash
			if(payload.flashes)
			{
				for (var i in payload.flashes) {
					jQuery.jGrowl(payload.flashes[i].msg, {
						life: 10000
					});
				}
			}
			// snippets
			if (payload.snippets) {
				for (var i in payload.snippets) {
					jQuery.nette.updateSnippet(i, payload.snippets[i]);
				}
			}
			
			
			
			
			if (payload.cmds) {
				for (var i in payload.cmds) {
					jQuery.nette.executeCmd(payload.cmds[i], payload);
				}
			}
			
		}
	}
});

jQuery.ajaxSetup({
	success: jQuery.nette.success,
	//error: jQuery.nette.error,
	dataType: "json"
});

jQuery.nette.updateSnippet = function (id, html) {
	var snippet = $("#" + id);
	var effect = snippet.attr('data-effect');
	snippet.html(html).effect(effect);
};