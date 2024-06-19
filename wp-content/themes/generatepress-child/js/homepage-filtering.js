
// Functions used for the homepage product filtering

jQuery(document).ready(function($) {


    // Redirect if param not exist
    let cpu_name_array = [];
    let isParamFound = false
    let found = 0
    let url_string = window.location.href;
    var url = new URL(url_string);
    var cpu_name_value = url.searchParams.get("cpu_name");
    if(cpu_name_value != null){
        cpu_name_array = cpu_name_value.split(',')
    }

    var listItems = $("#menu-attributes-menu li");
    listItems.each(function(idx, li) {
        for (i = 0 ; i<cpu_name_array.length; i++){
            if(cpu_name_array[i] == li.firstElementChild.title){
                isParamFound = true
                break
            }
        }
        if(isParamFound){
            found++
            isParamFound = false
        }
    });

    if(found != cpu_name_array.length && cpu_name_array.length > 0){
        window.location.href = "http://woocommerce-689526-4599505.cloudwaysapps.com/";  // Redirect end
    }


    // Allow closing and opening of filter boxes
    $('.sidebar .widget .widget-title').click(function() {
        $(this).toggleClass('close_filter');

        if($(this).hasClass('close_filter')) {
            $(this).parents('.widget').find('.textwidget, .menu-menu-2-container, .menu-attributes-menu-container, .menu-product-type-container').slideUp();
        } else {
            $(this).parents('.widget').find('.textwidget, .menu-menu-2-container, .menu-attributes-menu-container, .menu-product-type-container').slideDown();
        }
    });

    // MISC:
    // Add custom event to detect pushState and replaceState
    var pushHistoryEvent = function(type) {
        var orig = history[type];

        return function() {
            var rv = orig.apply(this, arguments),
                e = new Event(type);

            e.arguments = arguments;
            window.dispatchEvent(e);

            return rv;
        };
    };
    history.pushState = pushHistoryEvent('pushState'), history.replaceState = pushHistoryEvent('replaceState');
});