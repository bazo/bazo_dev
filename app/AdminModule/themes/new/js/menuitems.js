/**
 * dragDropMenu 
 *
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Written by Martin Bazik <martin@bazo.sk>
 * Last updated: 22.11.2009
 *
 */
jQuery.extend({
	menuDragDrop: function(action_url){
		$('#dragDropMenu ul#root li ul li').prepend('<div class="dropzone"></div>');
	
    $('#dragDropMenu ul#root li ul li dl, .dropzone').droppable({
        accept: '#dragDropMenu li',
        tolerance: 'pointer',
        drop: function(e, ui) {
            var li = $(this).parent();
            var child = !$(this).hasClass('dropzone');
            if (child && li.children('ul').length == 0) {
                li.append('<ul/>');
            }
            if (child) {
                li.addClass('liOpen').removeClass('liClosed').children('ul').append(ui.draggable);
            }
            else {
                li.before(ui.draggable);
            }
			$('#dragDropMenu ul#root li ul li.liOpen').not(':has(li:not(.ui-draggable-dragging))').removeClass('liOpen');
            li.find('dl,.dropzone').css({backgroundColor: '', borderColor: ''});
			
			
			if($(this).is('li .dropzone'))
			{
				var parent = $(this).parent().parent().parent();
			}
			else var parent = li;
			var parent_id = parent.attr('data-id');
			var parent_level = parent.attr('data-level');
			var prev_position = ui.draggable.prev('li').attr('data-position');
			var item_id = ui.draggable.attr('data-id');
			var level = ui.draggable.attr('data-level');
			
			$.post(action_url,{item_id : item_id,prev_position: prev_position,  parent_id: parent_id, parent_level: parent_level, level: level}, function(payload){
				$.nette.success(payload);
			} );
        },
        over: function() {
            $(this).filter('dl').css({backgroundColor: '#ccc'});
            $(this).filter('.dropzone').css({'background-color': '#aaa', 'height': '6px'});
            //$(this).filter('.dropzone').html('Drop here');
        },
        out: function() {
            $(this).filter('dl').css({backgroundColor: ''});
            $(this).filter('.dropzone').css({'background-color': '', height: '6px'});
            $(this).filter('.dropzone').html('');
        }
    });
    $('#dragDropMenu ul#root li ul li').draggable({
        handle: ' > dl',
        opacity: .8,
        addClasses: false,
        helper: 'clone',
        zIndex: 100,
        start: function(e, ui) {
           
        }
    });
	$('.expander').live('click', function(e) {
		$(this).parent().parent().parent().toggleClass('liOpen').toggleClass('liClosed');
		e.preventDefault();
	});
	$('#dragDropMenu a').live('click', function(e){
		e.preventDefault();
	});
	$('#dragDropMenu a.delete').click(function(e){
		var item = $(this).parent().parent().parent().attr('data-id');
		$.post(this.href, {item: item}, function(payload){
			$.nette.success(payload);
		});
	});
}
});