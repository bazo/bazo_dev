$(document).ready(function(){
	$.each($('#menu nav a'), function(){
		if($(this).attr('href') == window.location.pathname + window.location.search)
		{
			$(this).addClass('active');
		}
	});
});
