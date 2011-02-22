jQuery(function($){
    function sidebar_change(){
        var div = $(this).parents('.sidebar-list');
        
        var disabled = !this.checked;
        $('.widget_checkbox', div).each(function(){
            this.disabled = disabled;
        });
    }
    $('#active-page-widgets .sidebar_checkbox')
        .change(sidebar_change)
        .each(sidebar_change);
    
    $('#addtag, #edittag, #post').submit( function(){
        $('#active-page-widgets .widget_checkbox').each(function(){
            this.disabled = false;
        });
    });
    $('#active-page-widgets .all-none .all, #active-page-widgets .all-none .none').click(function(){
        var checked = $(this).hasClass('all');
        var list = $(this).parents('.sidebar-list');
        $('.widget_checkbox', list).each(function(){
            this.checked = checked;
        });
    });
    
});