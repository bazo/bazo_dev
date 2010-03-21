$(document).ready(function(){
	//category change
	$('.category-change').livequery('change', (function(){
		var page = $(this).attr('data-id');
		var new_cat = $(this).val();
		$.post('?do=changeCategory',{page : page, new_cat: new_cat});
	}));
    
    $('.template-change').livequery('change', (function(){
        var page = $(this).attr('data-id');
        var template = $(this).val();
        $.post('?do=changeTemplate',{page : page, template: template});
    }));
	
	//publish change
	$('.chboxPublished').live('click', function(){
		var page = $(this).attr('data-id');
		var published = $(this).attr('checked');
		$.post('?do=changePublished',{page : page, published: published});
	});
	
	//timepicker
	$('#frmformNewPage-publish_time, #frmformEditPage-publish_time').livequery(function(){
		$(this).datepicker({dateFormat: 'dd.mm.yy'});
	});
    
	$('#frmnew-time, #frmedit-time').livequery(function(){
	
    });
    
    $('#frmformNewPage-content, #frmformEditPage-content').livequery(function(){
        $(this).htmlarea();
        var area = $(this);
        $('div.jHtmlArea').mouseleave(function(){
            area.htmlarea('updateTextArea');
           area.htmlarea('updateHtmlArea'); 
        });
    });
     
    $('#frmformPreviewToolbar-btnClose').live('click', function(){Shadowbox.close()});
    /*
    $('#frmformEditPage-content, #frmformNewPage-content').livequery(function(){
        $(this).htmlarea();    
    });
     */
    
    //SHADOWBOX    
	Shadowbox.init({
	    skipSetup: true,
		players: ["html"],
		modal: false
	});
    
});
