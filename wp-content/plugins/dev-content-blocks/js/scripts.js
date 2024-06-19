$ = jQuery.noConflict();
//        ace.require("ace/ext/language_tools");

if($('#dc_dcb_html_editor').length) {
    var HTMLeditor = ace.edit("dc_dcb_html_editor");
    HTMLeditor.getSession().setMode("ace/mode/html");
    HTMLeditor.setOptions({enableBasicAutocompletion: true, enableLiveAutocompletion: true});
    HTMLeditor.$blockScrolling = Infinity;
    HTMLeditor.getSession().setUseWrapMode(true);
    HTMLeditor.getSession().setValue($('#dc_dcb_html').val());
    HTMLeditor.getSession().on('change', function () {
        $('#dc_dcb_html').val(HTMLeditor.getValue());
    });

    $(".dc_dcb_html_editor_container").resizable({
        resize: function (event, ui) {
            HTMLeditor.resize();
        }
    });
}

if($('#dc_dcb_css_editor').length) {
    var CSSeditor = ace.edit("dc_dcb_css_editor");
    CSSeditor.getSession().setMode("ace/mode/css");
    CSSeditor.setOptions({enableBasicAutocompletion: true, enableLiveAutocompletion: true});
    CSSeditor.$blockScrolling = Infinity;
    CSSeditor.getSession().setUseWrapMode(true);
    CSSeditor.getSession().setValue($('#dc_dcb_css').val());
    CSSeditor.getSession().on('change', function () {
        $('#dc_dcb_css').val(CSSeditor.getValue());
    });

    $(".dc_dcb_css_editor_container").resizable({
        resize: function (event, ui) {
            CSSeditor.resize();
        }
    });
}

if($('#dc_dcb_js_editor').length) {
    var JSeditor = ace.edit("dc_dcb_js_editor");
    JSeditor.getSession().setMode("ace/mode/javascript");
//JSeditor.setTheme("ace/theme/dawn");
    JSeditor.setOptions({enableBasicAutocompletion: true, enableLiveAutocompletion: true});
    JSeditor.$blockScrolling = Infinity;
    JSeditor.getSession().setUseWrapMode(true);
    JSeditor.getSession().setValue($('#dc_dcb_js').val());
    JSeditor.getSession().on('change', function () {
        $('#dc_dcb_js').val(JSeditor.getValue());
    });

    $(".dc_dcb_js_editor_container").resizable({
        resize: function (event, ui) {
            JSeditor.resize();
        }
    });
}
$('#dc_dcb_enable_content .dc_dcb_tgl-btn').click(function(){
    $('#dc_dcb_enable_content .dc_dcb_show_post').click();
});
$('#dc_dcb_enable_content .dc_dcb_show_post').on('change', function(){
    if($('#dc_dcb_enable_content .dc_dcb_show_post').is(':checked')){
        $('#postdivrich').slideDown();
        $(window).scrollTop($(window).scrollTop() + 1);
        $(window).scrollTop($(window).scrollTop() - 1);
    } else {
        $('#postdivrich').slideUp();
    }
});

(function(b,c){var $=b.jQuery||b.Cowboy||(b.Cowboy={}),a;$.throttle=a=function(e,f,j,i){var h,d=0;if(typeof f!=="boolean"){i=j;j=f;f=c}function g(){var o=this,m=+new Date()-d,n=arguments;function l(){d=+new Date();j.apply(o,n)}function k(){h=c}if(i&&!h){l()}h&&clearTimeout(h);if(i===c&&m>e){l()}else{if(f!==true){h=setTimeout(i?k:l,i===c?e-m:e)}}}if($.guid){g.guid=j.guid=j.guid||$.guid++}return g};$.debounce=function(d,e,f){return f===c?a(d,e,false):a(d,f,e!==false)}})(this);

$(window).scroll($.debounce( 250, true, function(){
    $('.dc_dcb_mask').addClass('scrolling');
}));
$(window).scroll($.debounce( 250, function(){
    $('.dc_dcb_mask').removeClass('scrolling');
}));