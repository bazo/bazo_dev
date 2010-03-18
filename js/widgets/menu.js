 $(document).ready(function(){
   var menu = $('.nav ul');
   menu.prepend('<div id="box"></div>');
   var style = 'easeOutExpo';   
   //style = 'easeOutCubic';    
    var active = $('.nav li.active');
    var default_left = Math.round(active.offset().left - $('.nav ul').offset().left);   
    var default_width = active.width();   
  
    $('#box').css({left: default_left, width: default_width});   
    $('.nav li').hover(function () {
        if(active.attr('id') != $(this).attr('id'))
        {
            active.removeClass('active');     
            left = Math.round($(this).offset().left - $('.nav ul').offset().left);   
            width = $(this).width();    
            $('#box').stop(false, true).animate({left: left, width: width},{duration:1000, easing: style});
        }
    }).click(function () {   
        if(active.attr('id') != $(this).attr('id'))
        {   
            //reset the active item   
            $('.nav li.active').removeClass('active');     
            active = $(this);           
            //select the current item   
            $(this).addClass('active');   
        }
    });   
       
    $('.nav li').mouseleave(function () {   
  
        if(active.attr('id') != $(this).attr('id'))
        {
            $('#box').stop(false, true).animate({left: default_left, width: default_width},1000, style, function(){active.addClass('active');});      
        }           
    });
});
