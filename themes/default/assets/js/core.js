$(window).load(function () {
    $("#loading").fadeOut("slow");
});
function cssStyle() {
    if ($.cookie('cus_style') == 'light') {
        $('link[href="'+site.base_url+'themes/default/assets/styles/blue.css"]').attr('disabled', 'disabled');
        $('link[href="'+site.base_url+'themes/default/assets/styles/blue.css"]').remove();
        $('<link>')
        .appendTo('head')
        .attr({type: 'text/css', rel: 'stylesheet'})
        .attr('href', site.base_url+'themes/default/assets/styles/light.css');
    }
    else if ($.cookie('cus_style') == 'blue') {
        $('link[href="'+site.base_url+'themes/default/assets/styles/light.css"]').attr('disabled', 'disabled');
        $('link[href="'+site.base_url+'themes/default/assets/styles/light.css"]').remove();
        $('<link>')
        .appendTo('head')
        .attr({type: 'text/css', rel: 'stylesheet'})
        .attr('href', ''+site.base_url+'themes/default/assets/styles/blue.css');
    }
    else if ($.cookie('cus_style') == 'pink') {
        $('link[href="'+site.base_url+'themes/default/assets/styles/blue.css"]').attr('disabled', 'disabled');
        $('link[href="'+site.base_url+'themes/default/assets/styles/blue.css"]').remove();
        $('<link>')
        .appendTo('head')
        .attr({type: 'text/css', rel: 'stylesheet'})
        .attr('href', ''+site.base_url+'themes/default/assets/styles/pink.css');
    }
    else if ($.cookie('cus_style') == 'green') {
        $('link[href="'+site.base_url+'themes/default/assets/styles/pink.css"]').attr('disabled', 'disabled');
        $('link[href="'+site.base_url+'themes/default/assets/styles/pink.css"]').remove();
        $('<link>')
        .appendTo('head')
        .attr({type: 'text/css', rel: 'stylesheet'})
        .attr('href', ''+site.base_url+'themes/default/assets/styles/green.css');
    }
    else {
        $('link[href="'+site.base_url+'themes/default/assets/styles/light.css"]').attr('disabled', 'disabled');
        $('link[href="'+site.base_url+'themes/default/assets/styles/blue.css"]').attr('disabled', 'disabled');
        $('link[href="'+site.base_url+'themes/default/assets/styles/pink.css"]').attr('disabled', 'disabled');
        $('link[href="'+site.base_url+'themes/default/assets/styles/green.css"]').attr('disabled', 'disabled');
        
        $('link[href="'+site.base_url+'themes/default/assets/styles/light.css"]').remove();
        $('link[href="'+site.base_url+'themes/default/assets/styles/blue.css"]').remove();
        $('link[href="'+site.base_url+'themes/default/assets/styles/pink.css"]').remove();
        $('link[href="'+site.base_url+'themes/default/assets/styles/green.css"]').remove();
    }

    // Customer theme fixed.
    $.cookie('cus_theme_fixed', 'yes', { path: '/' });
    if($('#sidebar-left').hasClass('minified')) {
        $.cookie('cus_theme_fixed', 'no', { path: '/' });
        $('#content, #sidebar-left, #header').removeAttr("style");
        $('#sidebar-left').removeClass('sidebar-fixed');
        $('#content').removeClass('content-with-fixed');
        $('#fixedText').text('Fixed');
        $('#main-menu-act').addClass('full visible-md visible-lg').show();
        $('#fixed').removeClass('fixed');
    } else {
        if(site.settings.rtl == 1) {
            $.cookie('cus_theme_fixed', 'no', { path: '/' });
        }
        if ($.cookie('cus_theme_fixed') == 'yes') {
             $('#content').css('margin-left', $('#sidebar-left').outerWidth(true)).css('margin-top', '34px').css('margin-left', '0px');
            $('#content').addClass('content-with-fixed');
            $('#sidebar-left').addClass('sidebar-fixed').css('height', $(window).height()- 80);
            $('#header').css('position', 'fixed').css('top', '0').css('width', '100%');
            $('#fixedText').text('Static');
            //$('#main-menu-act').removeAttr("class").hide();
            $('#fixed').addClass('fixed');
            $("#sidebar-left").css("overflow","hidden");
            $('#sidebar-left').perfectScrollbar({suppressScrollX: true});
        } else {
            $('#content, #sidebar-left, #header').removeAttr("style");
            $('#sidebar-left').removeClass('sidebar-fixed');
            $('#content').removeClass('content-with-fixed');
            $('#fixedText').text('Fixed');
            $('#main-menu-act').addClass('full visible-md visible-lg').show();
            $('#fixed').removeClass('fixed');
            $('#sidebar-left').perfectScrollbar('destroy');
        }
    }
    widthFunctions();
}
$('#csv_file').change(function(e) {
    v = $(this).val();
    if (v != '') {
        var validExts = new Array(".csv");
        var fileExt = v;
        fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
        if (validExts.indexOf(fileExt) < 0) {
            e.preventDefault();
            bootbox.alert("Invalid file selected. Only .csv file is allowed.");
            $(this).val(''); $(this).fileinput('clear');
            $('form[data-toggle="validator"]').bootstrapValidator('updateStatus', 'csv_file', 'NOT_VALIDATED');
            return false;
        }
        else
            return true;
    }
});

$(document).ready(function() {
    $("#suggest_product").autocomplete({
        source: site.base_url+'reports/suggestions',
        select: function (event, ui) {
            $('#report_product_id').val(ui.item.id);
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function (event, ui) {
            if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).val(ui.item.label);
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            }
        },
    });
    $(document).on('blur', '#suggest_product', function(e) {
        if (! $(this).val()) {
            $('#report_product_id').val('');
        }
    });
    
    
    
    
    $("#suggest_employee").autocomplete({
        source: site.base_url+'hr/suggestions',
        select: function (event, ui) {
            $('#suggest_employee_id').val(ui.item.id);
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function (event, ui) {
            if(ui.content == null){
                return false;
            }else if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).val(ui.item.label);
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            }
        },
    });
    $(document).on('blur', '#suggest_employee', function(e) {
        if (! $(this).val()) {
            $('#suggest_employee_id').val('');
        }
    });

    $('body').on('click', '.register_link td:not(:last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pos/close_register2/' + $(this).parent('.register_link').attr('id')});
        $('#myModal').modal('show');
    });
    

    $('body').on('click', '.register_link td:last-child', function() {
        $('#myModal').modal({remote: site.base_url + 'pos/register_items_report/' + $(this).parent('.register_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('#random_num').click(function(){
        $(this).parent('.input-group').children('input').val(generateCardNo(8));
    });
    $('#toogle-customer-read-attr').click(function () {
        var icus = $(this).closest('.input-group').find("input[name='customer']");
        var nst = icus.is('[readonly]') ? false : true;
        icus.select2("readonly", nst);
        return false;
    });
    $('.top-menu-scroll').perfectScrollbar();
    $('#fixed').click(function(e) {
        e.preventDefault();
        if($('#sidebar-left').hasClass('minified')) {
            bootbox.alert('Unable to fix minified sidebar');
        } else {
            if($(this).hasClass('fixed')) {
                $.cookie('cus_theme_fixed', 'no', { path: '/' });
            } else {
                $.cookie('cus_theme_fixed', 'yes', { path: '/' });
            }
            cssStyle();
        }
    });
});

function widthFunctions(e) {
    var l = $("#sidebar-left").outerHeight(true),
    c = $("#content").height(),
    co = $("#content").outerHeight(),
    h = $("header").height(),
    f = $("footer").height(),
    wh = $(window).height(),
    ww = $(window).width();
    if (ww < 992) {
        $("#main-menu-act").removeClass("minified").addClass("full").find("i").removeClass("icon fa fa-tasks tip").addClass("icon fa fa-tasks tip");
        $("body").removeClass("sidebar-minified");
        $("#content").removeClass("sidebar-minified");
        $("#sidebar-left").removeClass("minified")
        if ($.cookie('cus_theme_fixed') == 'yes') {
            $.cookie('cus_theme_fixed', 'no', { path: '/' });
            $('#content, #sidebar-left, #header').removeAttr("style");
            $("#sidebar-left").css("overflow-y","visible");
            $('#fixedText').text('Fixed');
            $('#main-menu-act').addClass('full visible-md visible-lg').show();
            $('#fixed').removeClass('fixed');
            $('#sidebar-left').perfectScrollbar('destroy');
        }
    }
    if (ww < 998 && ww > 750) {
        $('#main-menu-act').hide();
        $("body").addClass("sidebar-minified");
        $("#content").addClass("sidebar-minified");
        $("#sidebar-left").addClass("minified");
        $(".dropmenu > .chevron").removeClass("opened").addClass("closed");
        $(".dropmenu").parent().find("ul").hide();
        $("#sidebar-left > div > ul > li > a > .chevron").removeClass("closed").addClass("opened");
        $("#sidebar-left > div > ul > li > a").addClass("open");
        $('#fixed').hide();
    }
    if (ww > 1024 && $.cookie('cus_sidebar') != 'minified') {
        $('#main-menu-act').removeClass("minified").addClass("full").find("i").removeClass("icon fa fa-tasks tip").addClass("icon fa fa-tasks tip");
        $("body").removeClass("sidebar-minified");
        $("#content").removeClass("sidebar-minified");
        $("#sidebar-left").removeClass("minified");
        $("#sidebar-left > div > ul > li > a > .chevron").removeClass("opened").addClass("closed");
        $("#sidebar-left > div > ul > li > a").removeClass("open");
        $('#fixed').show();
    }
    if ($.cookie('cus_theme_fixed') == 'yes') {
        $('#content').addClass('content-with-fixed');
        $('#sidebar-left').addClass('sidebar-fixed').css('height', $(window).height()- 80);
    }
    // 767 old one
    if (ww > 992) {
        wh - 80 > l && $("#sidebar-left").css("min-height", wh - h - f - 30);
        wh - 80 > c && $("#content").css("min-height", wh - h - f - 30);
    } else {
        $("#sidebar-left").css("min-height", "0px");
        $(".content-con").css("max-width", ww);
    }
    $(window).scrollTop($(window).scrollTop() + 1);
}

jQuery(document).ready(function(e) {
    window.location.hash ? e('#myTab a[href="' + window.location.hash + '"]').tab('show') : e("#myTab a:first").tab("show");
    e("#myTab2 a:first, #dbTab a:first").tab("show");
    e("#myTab a, #myTab2 a, #dbTab a").click(function(t) {
        t.preventDefault();
        e(this).tab("show");
    });
    e('[rel="popover"],[data-rel="popover"],[data-toggle="popover"]').popover();
    e("#toggle-fullscreen").button().click(function() {
        var t = e(this),
        n = document.documentElement;
        if (!t.hasClass("active")) {
            e("#thumbnails").addClass("modal-fullscreen");
            n.webkitRequestFullScreen ? n.webkitRequestFullScreen(window.Element.ALLOW_KEYBOARD_INPUT) : n.mozRequestFullScreen && n.mozRequestFullScreen()
        } else {
            e("#thumbnails").removeClass("modal-fullscreen");
            (document.webkitCancelFullScreen || document.mozCancelFullScreen || e.noop).apply(document)
        }
    });
    e(".btn-close").click(function(t) {
        t.preventDefault();
        e(this).parent().parent().parent().fadeOut()
    });
    e(".btn-minimize").click(function(t) {
        t.preventDefault();
        var n = e(this).parent().parent().next(".box-content");
        n.is(":visible") ? e("i", e(this)).removeClass("fa-chevron-up").addClass("fa-chevron-down") : e("i", e(this)).removeClass("fa-chevron-down").addClass("fa-chevron-up");
        n.slideToggle("slow", function() {
            widthFunctions();
        })
    });
});

jQuery(document).ready(function(e) {
    e("#main-menu-act").click(function() {
        if (e(this).hasClass("full")) {
            $.cookie('cus_sidebar', 'minified', { path: '/' });
            e(this).removeClass("full").addClass("minified").find("i").removeClass("icon fa fa-tasks tip").addClass("icon fa fa-tasks tip");
            e("body").addClass("sidebar-minified");
            e("#content").addClass("sidebar-minified");
            e("#sidebar-left").addClass("minified");
            e(".dropmenu > .chevron").removeClass("opened").addClass("closed");
            e(".dropmenu").parent().find("ul").hide();
            e("#sidebar-left > div > ul > li > a > .chevron").removeClass("closed").addClass("opened");
            e("#sidebar-left > div > ul > li > a").addClass("open");
            $('#fixed').hide();
        } else {
            $.cookie('cus_sidebar', 'full', { path: '/' });
            e(this).removeClass("minified").addClass("full").find("i").removeClass("icon fa fa-tasks tip").addClass("icon fa fa-tasks tip");
            e("body").removeClass("sidebar-minified");
            e("#content").removeClass("sidebar-minified");
            e("#sidebar-left").removeClass("minified");
            e("#sidebar-left").removeClass("sidebar-fixed");
            e("#sidebar-left > div > ul > li > a > .chevron").removeClass("opened").addClass("closed");
            e("#sidebar-left > div > ul > li > a").removeClass("open");
            $('#fixed').show();
        }
        return false;
    });
e(".dropmenu").click(function(t) {
    t.preventDefault();
    if (e("#sidebar-left").hasClass("minified")) {
        if (!e(this).hasClass("open")) {
            e(this).parent().find("ul").first().slideToggle();
            e(this).find(".chevron").hasClass("closed") ? e(this).find(".chevron").removeClass("closed").addClass("opened") : e(this).find(".chevron").removeClass("opened").addClass("closed")
        }
    } else {
        e(this).parent().find("ul").first().slideToggle();
        e(this).find(".chevron").hasClass("closed") ? e(this).find(".chevron").removeClass("closed").addClass("opened") : e(this).find(".chevron").removeClass("opened").addClass("closed")
    }
});
if (e("#sidebar-left").hasClass("minified")) {
    e("#sidebar-left > div > ul > li > a > .chevron").removeClass("closed").addClass("opened");
    e("#sidebar-left > div > ul > li > a").addClass("open");
    e("body").addClass("sidebar-minified")
}
});

$(document).ready(function() {
    cssStyle();
    $('select, .select').select2({minimumResultsForSearch: 7});
    $('#customer, #rcustomer').select2({
       minimumInputLength: 1,
       ajax: {
        url: site.base_url+"customers/suggestions",
        dataType: 'json',
        quietMillis: 15,
        data: function (term, page) {
            return {
                term: term,
                limit: 10
            };
        },
        results: function (data, page) {
            if(data.results != null) {
                return { results: data.results };
            } else {
                return { results: [{id: '', text: 'No Match Found'}]};
            }
        }
    }
});
    $('#supplier, #rsupplier, .rsupplier').select2({
       minimumInputLength: 1,
       ajax: {
        url: site.base_url+"suppliers/suggestions",
        dataType: 'json',
        quietMillis: 15,
        data: function (term, page) {
            return {
                term: term,
                limit: 10
            };
        },
        results: function (data, page) {
            if(data.results != null) {
                return { results: data.results };
            } else {
                return { results: [{id: '', text: 'No Match Found'}]};
            }
        }
    }
});
    $('.input-tip').tooltip({placement: 'top', html: true, trigger: 'hover focus', container: 'body',
        title: function() {
            return $(this).attr('data-tip');
        }
    });
    $('.input-pop').popover({placement: 'top', html: true, trigger: 'hover', container: 'body',
        content: function() {
            return $(this).attr('data-tip');
        },
        title: function() {
            return '<b>' + $('label[for="' + $(this).attr('id') + '"]').text() + '</b>';
        }
    });
});

$(document).on('click', '*[data-toggle="lightbox"]', function(event) {
    event.preventDefault();
    $(this).ekkoLightbox();
});
$(document).on('click', '*[data-toggle="popover"]', function(event) {
    event.preventDefault();
    $(this).popover();
});

$(document).ajaxStart(function(){
  $('#ajaxCall').show();
}).ajaxStop(function(){
  $('#ajaxCall').hide();
});

$(document).ready(function() {
    $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%'
    });
    $('textarea').not('.skip').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', /*'image', 'video',*/ 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function(e) {
            var editor = this.$editor.next('textarea');
            if($(editor).attr('required')){
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', $(editor).attr('name'));
            }
        }
    });
    $(document).on('click', '.file-caption', function(){
        $(this).next('.input-group-btn').children('.btn-file').children('input.file').trigger('click');
    });
});

function suppliers(ele) {
    $(ele).select2({
       minimumInputLength: 1,
       ajax: {
        url: site.base_url+"suppliers/suggestions",
        dataType: 'json',
        quietMillis: 15,
        data: function (term, page) {
            return {
                term: term,
                limit: 10
            };
        },
        results: function (data, page) {
            if(data.results != null) {
                return { results: data.results };
            } else {
                return { results: [{id: '', text: 'No Match Found'}]};
            }
        }
    }
});
}

$(function() {
    if(site.settings.date_with_time == 0 ){
        $('.datetime').datetimepicker({format: site.dateFormats.js_sdate, fontAwesome: true, language: 'cus', todayBtn: 1, autoclose: 1, minView: 2 });
        $(document).on('focus','.datetime', function(t) {
            $(this).datetimepicker({format: site.dateFormats.js_sdate, fontAwesome: true, todayBtn: 1, autoclose: 1, minView: 2 });
        });
    }else{
        $('.datetime').datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, language: 'cus', weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0});
        $(document).on('focus','.datetime', function() {
            $(this).datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0});
        });
    }
    $('.date_time').datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, language: 'cus', weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0});
    $(document).on('focus','.date_time', function() {
        $(this).datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0});
    });
    
    $('.date').datetimepicker({format: site.dateFormats.js_sdate, fontAwesome: true, language: 'cus', todayBtn: 1, autoclose: 1, minView: 2 });
    $(document).on('focus','.date', function(t) {
        $(this).datetimepicker({format: site.dateFormats.js_sdate, fontAwesome: true, todayBtn: 1, autoclose: 1, minView: 2 });
    });
   
    $(document).on('focus','.month', function(t) {
        $(this).datetimepicker({format: "mm/yyyy", fontAwesome: true, autoclose: 1,startView: 3, minView: 3 });
    });

    $(document).on('focus','.year', function(t) {
        $(this).datetimepicker({format: "yyyy", fontAwesome: true, autoclose: 1,startView: 4, minView: 4 });
    });
    
    $(document).on('focus','.month_only', function(t) {
        $(this).datetimepicker({format: "mm", fontAwesome: true, autoclose: 1,startView: 3, minView: 5 });
    });
    
    $('.timepicker').datetimepicker({ format: 'hh:ii:ss', fontAwesome: true, autoclose: 1, startView: 0,todayBtn: 1});
});

$(document).ready(function() {
    $('#dbTab a').on('shown.bs.tab', function(e) {
      var newt = $(e.target).attr('href');
      var oldt = $(e.relatedTarget).attr('href');
      $(oldt).hide();
      //$(newt).hide().fadeIn('slow');
      $(newt).hide().slideDown('slow');
  });
    $('.dropdown').on('show.bs.dropdown', function(e){
        $(this).find('.dropdown-menu').first().stop(true, true).slideDown('fast');
    });
    $('.dropdown').on('hide.bs.dropdown', function(e){
        $(this).find('.dropdown-menu').first().stop(true, true).slideUp('fast');
    });
    $('.hideComment').click(function(){
        $.ajax({ url: site.base_url+'welcome/hideNotification/'+$(this).attr('id')});
    });
    $('.tip').tooltip();
    $('body').on('click', '#delete', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form').submit();
    });
    $('body').on('click', '#sync_quantity', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#excel', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#pdf', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#labelProducts', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#barcodeProducts', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#combine', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
});

$(document).ready(function() {
    $('#product-search').click(function() {
        $('#product-search-form').submit();
    });
    //feedbackIcons:{valid: 'fa fa-check',invalid: 'fa fa-times',validating: 'fa fa-refresh'},
    $('form[data-toggle="validator"]').bootstrapValidator({ message: 'Please enter/select a value', submitButtons: 'input[type="submit"]' });
    fields = $('.form-control');
    $.each(fields, function() {
        var id = $(this).attr('id');
        var iname = $(this).attr('name');
        var iid = '#'+id;
        if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
            $("label[for='" + id + "']").append(' *');
            $(document).on('change', iid, function(){
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
            });
        }
    });
    $('body').on('click', 'label', function (e) {
        var field_id = $(this).attr('for');
        if (field_id) {
            if($("#"+field_id).hasClass('select')) {
                $("#"+field_id).select2("open");
                return false;
            }
        }
    });
    $('body').on('focus', 'select', function (e) {
        var field_id = $(this).attr('id');
        if (field_id) {
            if($("#"+field_id).hasClass('select')) {
                $("#"+field_id).select2("open");
                return false;
            }
        }
    });
    $('#myModal').on('hidden.bs.modal', function() {
        $(this).find('.modal-dialog').empty();
        //$(this).find('#myModalLabel').empty().html('&nbsp;');
        //$(this).find('.modal-body').empty().text('Loading...');
        //$(this).find('.modal-footer').empty().html('&nbsp;');
        $(this).removeData('bs.modal');
    });
    $('#myModal2').on('hidden.bs.modal', function () {
        $(this).find('.modal-dialog').empty();
        //$(this).find('#myModalLabel').empty().html('&nbsp;');
        //$(this).find('.modal-body').empty().text('Loading...');
        //$(this).find('.modal-footer').empty().html('&nbsp;');
        $(this).removeData('bs.modal');
        $('#myModal').css('zIndex', '1050');
        $('#myModal').css('overflow-y', 'scroll');
    });
    $('#myModal2').on('show.bs.modal', function () {
        $('#myModal').css('zIndex', '1040');
    });
    $('.modal').on('show.bs.modal', function () {
        $('#modal-loading').show();
        $('.blackbg').css('zIndex', '1041');
        $('.loader').css('zIndex', '1042');
    }).on('hide.bs.modal', function () {
        $('#modal-loading').hide();
        $('.blackbg').css('zIndex', '3');
        $('.loader').css('zIndex', '4');
    });
    $(document).on('click', '.po', function(e) {
        e.preventDefault();
        $('.po').popover({html: true, placement: 'left', trigger: 'manual'}).popover('show').not(this).popover('hide');
        return false;
    });
    $(document).on('click', '.po-close', function() {
        $('.po').popover('hide');
        return false;
    });
    $(document).on('click', '.po-delete', function(e) {
        var row = $(this).closest('tr');
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        var return_id = $(this).attr('data-return-id');
        $.ajax({type: "get", url: link,
            success: function(data) { $('#'+return_id).remove(); row.remove(); if (data) { addAlert(data, 'success'); if(oTable != '') { oTable.fnDraw(); } } },
            error: function(data) { addAlert('Failed', 'danger'); }
        });
        return false;
    });

    $(document).on('click', '.po-delete1', function(e) {
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        var s = $(this).attr('id'); var sp = s.split('__')
        $.ajax({type: "get", url: link,
            success: function(data) { if (data) { addAlert(data, 'success'); } $('#'+sp[1]).remove(); if(oTable != '') { oTable.fnDraw(); } },
            error: function(data) { addAlert('Failed', 'danger'); }
        });
        return false;
    });
    $('body').on('click', '.bpo', function(e) {
        e.preventDefault();
        $(this).popover({html: true, trigger: 'manual'}).popover('toggle');
        return false;
    });
    $('body').on('click', '.bpo-close', function(e) {
        $('.bpo').popover('hide');
        return false;
    });
    $('#genNo').click(function(){
        var no = generateCardNo();
        $(this).parent().parent('.input-group').children('input').val(no);
        return false;
    });
    $('#inlineCalc').calculator({layout: ['_%+-CABS','_7_8_9_/','_4_5_6_*','_1_2_3_-','_0_._=_+'], showFormula:true});
    $('.calc').click(function(e) { e.stopPropagation();});
    $(document).on('click', '.sname', function(e) {
        var row = $(this).closest('tr');
        var itemid = row.find('.rid').val();
        $('#myModal').modal({remote: site.base_url + 'products/modal_view/' + itemid});
        $('#myModal').modal('show');
    });
    $(document).on('click', '.pbname', function(e) {
        var row = $(this).closest('tr');
        var itemid = row.find('.rid').val();
        $('#myModal').modal({remote: site.base_url + 'repairs/view_problem/' + itemid});
        $('#myModal').modal('show');
    });
});

function addAlertModal(message, type) {
    $('.alerts-con-modal').empty().append(
        '<div class="alert alert-' + type + '">' +
        '<button type="button" class="close" data-dismiss="alert">' +
        '&times;</button>' + message + '</div>');
}

function addAlert(message, type) {
    $('.alerts-con').empty().append(
        '<div class="alert alert-' + type + '">' +
        '<button type="button" class="close" data-dismiss="alert">' +
        '&times;</button>' + message + '</div>');
}

$(document).ready(function() {
    if ($.cookie('cus_sidebar') == 'minified') {
        $('#main-menu-act').removeClass("full").addClass("minified").find("i").removeClass("icon fa fa-tasks tip").addClass("icon fa fa-tasks tip");
        $("body").addClass("sidebar-minified");
        $("#content").addClass("sidebar-minified");
        $("#sidebar-left").addClass("minified");
        $(".dropmenu > .chevron").removeClass("opened").addClass("closed");
        $(".dropmenu").parent().find("ul").hide();
        $("#sidebar-left > div > ul > li > a > .chevron").removeClass("closed").addClass("opened");
        $("#sidebar-left > div > ul > li > a").addClass("open");
        $('#fixed').hide();
    } else {

        $('#main-menu-act').removeClass("minified").addClass("full").find("i").removeClass("icon fa fa-tasks tip").addClass("icon fa fa-tasks tip");
        $("body").removeClass("sidebar-minified");
        $("#content").removeClass("sidebar-minified");
        $("#sidebar-left").removeClass("minified");
        $("#sidebar-left > div > ul > li > a > .chevron").removeClass("opened").addClass("closed");
        $("#sidebar-left > div > ul > li > a").removeClass("open");
        $('#fixed').show();
    }
});

$(document).ready(function() {
    $('#daterange').daterangepicker({
        timePicker: true,
        format: (site.dateFormats.js_sdate).toUpperCase()+' HH:mm',
        ranges: {
         'Today': [moment().hours(0).minutes(0).seconds(0), moment()],
         'Yesterday': [moment().subtract('days', 1).hours(0).minutes(0).seconds(0), moment().subtract('days', 1).hours(23).minutes(59).seconds(59)],
         'Last 7 Days': [moment().subtract('days', 6).hours(0).minutes(0).seconds(0), moment().hours(23).minutes(59).seconds(59)],
         'Last 30 Days': [moment().subtract('days', 29).hours(0).minutes(0).seconds(0), moment().hours(23).minutes(59).seconds(59)],
         'This Month': [moment().startOf('month').hours(0).minutes(0).seconds(0), moment().endOf('month').hours(23).minutes(59).seconds(59)],
         'Last Month': [moment().subtract('month', 1).startOf('month').hours(0).minutes(0).seconds(0), moment().subtract('month', 1).endOf('month').hours(23).minutes(59).seconds(59)]
     }
 },
 function(start, end) {
    refreshPage(start.format('YYYY-MM-DD HH:mm'), end.format('YYYY-MM-DD HH:mm'));
});
});

function refreshPage(start, end) {
    window.location.replace(CURI + '/' + encodeURIComponent(start) + '/' + encodeURIComponent(end));
}

function retina() {
    retinaMode = window.devicePixelRatio > 1;
    return retinaMode
}

$(document).ready(function() {
    
    $('#standard_view').click(function(e) {
        $.ajax({ url: site.base_url+'welcome/style_view/standard_view'});
        location.reload(true);
    });
    $('#classic_view').click(function(e) {
        $.ajax({ url: site.base_url+'welcome/style_view/classic_view'});
        location.reload(true);
    });
    
    $('#cssLight').click(function(e) {
        $.ajax({ url: site.base_url+'welcome/cus_style/light'});
        e.preventDefault();
        $.cookie('cus_style', 'light', { path: '/' });
        cssStyle();
        location.reload(true);
    });
    $('#cssBlue').click(function(e) {
        $.ajax({ url: site.base_url+'welcome/cus_style/blue'});
        e.preventDefault();
        $.cookie('cus_style', 'blue', { path: '/' });
        cssStyle();
        location.reload(true);
    });
    $('#cssPink').click(function(e) {
        $.ajax({ url: site.base_url+'welcome/cus_style/pink'});
        e.preventDefault();
        $.cookie('cus_style', 'pink', { path: '/' });
        cssStyle();
        location.reload(true);
    });
    $('#cssGreen').click(function(e) {
        $.ajax({ url: site.base_url+'welcome/cus_style/green'});
        e.preventDefault();
        $.cookie('cus_style', 'green', { path: '/' });
        cssStyle();
        location.reload(true);
    });
    $('#cssBlack').click(function(e) {
        $.ajax({ url: site.base_url+'welcome/cus_style/black'});
        e.preventDefault();
        $.cookie('cus_style', 'black', { path: '/' });
        cssStyle();
        location.reload(true);
    });
    $("#toTop").click(function(e) {
        e.preventDefault();
        $("html, body").animate({scrollTop: 0}, 100);
    });
    $(document).on('click', '.delimg', function(e) {
        e.preventDefault();
        var ele = $(this), id = $(this).attr('data-item-id');
        bootbox.confirm(lang.r_u_sure, function(result) {
        if(result == true) {
            $.get(site.base_url+'products/delete_image/'+id, function(data) {
                if (data.error === 0) {
                    addAlert(data.msg, 'success');
                    ele.parent('.gallery-image').remove();
                }
            });
        }
        });
        return false;
    });
});
$(document).ready(function() {
    $(document).on('click', '.row_status', function(e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        if (row.hasClass('invoice_link')) {
            $('#myModal').modal({remote: site.base_url + 'sales/update_status/' + id});
            $('#myModal').modal('show');
        } else if (row.hasClass('purchase_link')) {
            $('#myModal').modal({remote: site.base_url + 'purchases/update_status/' + id});
            $('#myModal').modal('show');
        } else if (row.hasClass('quote_link')) {
            $('#myModal').modal({remote: site.base_url + 'quotes/update_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('repair_link')) {
            $('#myModal').modal({remote: site.base_url + 'repairs/view_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('item_link')) {
            $('#myModal').modal({remote: site.base_url + 'repairs/update_status/' + id});
            $('#myModal').modal('show');
        } else if (row.hasClass('transfer_link')) {
            $('#myModal').modal({remote: site.base_url + 'transfers/update_status/' + id});
            $('#myModal').modal('show');
        } else if (row.hasClass('sale_order_link')) {
            $('#myModal').modal({remote: site.base_url + 'sale_orders/update_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('rental_link')) {
            $('#myModal').modal({remote: site.base_url + 'rentals/update_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('rental_housekeeping_link')) {
            $('#myModal').modal({remote: site.base_url + 'rentals_housekeeping/update_status/' + id});
            $('#myModal').modal('show');
        }else if (row.hasClass('con_delivery_link')) {
            $('#myModal').modal({remote: site.base_url + 'concretes/update_status/' + id});
            $('#myModal').modal('show');
        }
        return false;
    });
    
    $(document).on('click', '.weight_status', function(e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        $('#myModal').modal({remote: site.base_url + 'concretes/update_weight/' + id});
        $('#myModal').modal('show');
        return false;
    });
});
/*
 $(window).scroll(function() {
    if ($(this).scrollTop()) {
        $('#toTop').fadeIn();
    } else {
        $('#toTop').fadeOut();
    }
 });
*/
$(document).on('ifChecked', '.checkth, .checkft', function(event) {
    $('.checkth, .checkft').iCheck('check');
    $('.multi-select').each(function() {
        $(this).iCheck('check');
    });
});
$(document).on('ifUnchecked', '.checkth, .checkft', function(event) {
    $('.checkth, .checkft').iCheck('uncheck');
    $('.multi-select').each(function() {
        $(this).iCheck('uncheck');
    });
});
$(document).on('ifUnchecked', '.multi-select', function(event) {
    $('.checkth, .checkft').attr('checked', false);
    $('.checkth, .checkft').iCheck('update');
});

function check_add_item_val() {
    $('#add_item').bind('keypress', function (e) {
        if (e.keyCode == 13 || e.keyCode == 9) {
            e.preventDefault();
            $(this).autocomplete("search");
        }
    });
}

function secToHour(x){
    if(x > 0){
        var hour = x / 3600;
        return hour;
    }
    return '';
}
function secTotime(x) {
    var format = '%02d:%02d:%02d';
    if (x < 1) {
        return '';
    }
    var hours = parseInt(x / 3600);
    var minutes = parseInt(x / 60 % 60);
    var seconds = parseInt(x % 60);
    return ("0" + hours).slice(-2) + ':' + ("0" + minutes).slice(-2) + ':'+ ("0" + seconds).slice(-2);
}

function d_secTotime(x) {
    var format = '%02d:%02d:%02d';
    if (x < 1) {
        return '';
    }
    var hours = parseInt(x / 3600);
    var minutes = parseInt(x / 60 % 60);
    var seconds = parseInt(x % 60);
    return '<div class="text-center">'+ ("0" + hours).slice(-2) + ':' + ("0" + minutes).slice(-2) + ':'+ ("0" + seconds).slice(-2) + '</div>';
}

function currencyFormatAcc(x) {
    if(x!=0){
        return '<div class="text-right">'+formatMoney(x != null ? x : 0)+'</div>';
    }else{
        return '<div class="text-right"></div>';
    }
}

function fd1(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('/');
        var bDate = aDate[2].split(' ');
        year = bDate[0], month = aDate[1], day = aDate[0];
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return day + "-" + month + "-" + year;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return day + "/" + month + "/" + year;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return day + "." + month + "." + year;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return month + "/" + day + "/" + year;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return month + "-" + day + "-" + year;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return month + "." + day + "." + yea;
        else
            return oObj;
    } else {
        return '';
    }
}

function fd(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        year = aDate[0], month = aDate[1], day = bDate[0];
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return day + "-" + month + "-" + year;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return day + "/" + month + "/" + year;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return day + "." + month + "." + year;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return month + "/" + day + "/" + year;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return month + "-" + day + "-" + year;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return month + "." + day + "." + yea;
        else
            return oObj;
    } else {
        return '';
    }
}

function fldt(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        year = aDate[0], month = aDate[1], day = bDate[0], time = bDate[1];
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return day + "-" + month + "-" + year + " " + time;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return day + "/" + month + "/" + year + " " + time;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return day + "." + month + "." + year + " " + time;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return month + "/" + day + "/" + year + " " + time;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return month + "-" + day + "-" + year + " " + time;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return month + "." + day + "." + year + " " + time;
        else
            return oObj;

    } else {
        return '';
    }
}

function fld(oObj) {
    if (oObj != null) {
        if(site.settings.date_with_time == 0) {
            return fd(oObj);
        }else{
            var aDate = oObj.split('-');
            var bDate = aDate[2].split(' ');
            year = aDate[0], month = aDate[1], day = bDate[0], time = bDate[1];
            if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
                return day + "-" + month + "-" + year + " " + time;
            else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
                return day + "/" + month + "/" + year + " " + time;
            else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
                return day + "." + month + "." + year + " " + time;
            else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
                return month + "/" + day + "/" + year + " " + time;
            else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
                return month + "-" + day + "-" + year + " " + time;
            else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
                return month + "." + day + "." + year + " " + time;
            else
                return oObj;
        }
        
    } else {
        return '';
    }
}

function fsd(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return aDate[2] + "-" + aDate[1] + "-" + aDate[0];
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return aDate[2] + "/" + aDate[1] + "/" + aDate[0];
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return aDate[2] + "." + aDate[1] + "." + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return aDate[1] + "/" + aDate[2] + "/" + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return aDate[1] + "-" + aDate[2] + "-" + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return aDate[1] + "." + aDate[2] + "." + aDate[0];
        else
            return oObj;
    } else {
        return '';
    }
}
function generateCardNo(x) {
    if(!x) { x = 16; }
    chars = "1234567890";
    no = "";
    for (var i=0; i<x; i++) {
       var rnum = Math.floor(Math.random() * chars.length);
       no += chars.substring(rnum,rnum+1);
   }
   return no;
}

function generateRoomRatesNo(x) {
    if(!x) { x = 4; }
    chars = "1234";
    no = "";
    for (var i=0; i<x; i++) {
       var rnum = Math.floor(Math.random() * chars.length);
       no += chars.substring(rnum,rnum+1);
   }
   return no;
}

function roundNumber(num, nearest) {
    if(!nearest) { nearest = 0.05; }
    return Math.round((num / nearest) * nearest);
}
function colorBox(x){
    return (x != null) ? '<div style="background-color:'+x+'" class="text-center">&nbsp;</div>' : '';
}
function getNumber(x) {
    return accounting.unformat(x);
}
function formatQuantity(x) {
    return (x != null) ? '<div class="text-right">'+formatNumber(x, site.settings.qty_decimals)+'</div>' : '';
}
function formatQuantity2(x) {
    return (x != null) ? formatNumber(x, site.settings.qty_decimals) : '';
}
function formatNumber(x, d) {
    if(!d && d != 0) { d = site.settings.decimals; }
    if(site.settings.sac == 1) {
        return formatSA(parseFloat(x).toFixed(d));
    }
    return accounting.formatNumber(x, d, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep);
}
function formatMoney(x, symbol) {
    if(!symbol) { symbol = ""; }
    if(site.settings.sac == 1) {
        return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
            ''+formatSA(parseFloat(x).toFixed(site.settings.decimals)) +
            (site.settings.display_symbol == 2 ? site.settings.symbol : '');
    }
    var fmoney = accounting.formatMoney(x, symbol, site.settings.decimals, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
    return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
        fmoney +
        (site.settings.display_symbol == 2 ? site.settings.symbol : '');
}
function is_valid_discount(mixed_var) {
    return (is_numeric(mixed_var) || (/([0-9]%)/i.test(mixed_var))) ? true : false;
}
function is_numeric(mixed_var) {
    var whitespace =
    " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
        1)) && mixed_var !== '' && !isNaN(mixed_var);
}
function is_float(mixed_var) {
  return +mixed_var === mixed_var && (!isFinite(mixed_var) || !! (mixed_var % 1));
}
function decimalFormat(x) {
    return '<div class="text-center">'+formatNumber(x != null ? x : 0)+'</div>';
}
function currencyFormat(x) {
    return '<div class="text-right">'+formatMoney(x != null ? x : 0)+'</div>';
}
function formatDecimalRaw(x) {
    return parseFloat(accounting.formatNumber(x, 16, '', '.'));
}
function formatDecimal(x, d) {
    if (!d) { d = site.settings.decimals; }
    return parseFloat(accounting.formatNumber(x, d, '', '.'));
}
function formatDecimals(x, d) {
    if (!d) { d = site.settings.decimals; }
    return parseFloat(accounting.formatNumber(x, d, '', '.')).toFixed(d);
}
function pqFormat(x) {
    if (x != null) {
        var d = '', pqc = x.split("___");
        for (index = 0; index < pqc.length; ++index) {
            var pq = pqc[index];
            var v = pq.split("__");
            d += v[0]+' ('+formatQuantity2(v[1])+')<br>';
        }
        return d;
    } else {
        return '';
    }
}
function checkbox(x) {
    return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + x + '" /></div>';
}
function decode_html(value){
    return $('<div/>').html(value).text();
}
function img_hl(x) {
    // return x == null ? '' : '<div class="text-center"><ul class="enlarge"><li><img src="'+site.base_url+'assets/uploads/thumbs/' + x + '" alt="' + x + '" style="width:30px; height:30px;" class="img-circle" /><span><a href="'+site.base_url+'assets/uploads/' + x + '" data-toggle="lightbox"><img src="'+site.base_url+'assets/uploads/' + x + '" alt="' + x + '" style="width:200px;" class="img-thumbnail" /></a></span></li></ul></div>';
    var image_link = (x == null || x == '') ? 'no_image.png' : x;
    return '<div class="text-center"><a href="'+site.base_url+'assets/uploads/' + image_link + '" data-toggle="lightbox"><img src="'+site.base_url+'assets/uploads/thumbs/' + image_link + '" alt="" style="width:30px; height:30px;" /></a></div>';
}
function attachment(x) {
    return x == null ? '' : '<div class="text-center"><a href="'+site.base_url+'welcome/download/' + x + '" class="tip" title="'+lang.download+'"><i class="fa fa-file"></i></a></div>';
}
function attachment2(x) {
    return x == null ? '' : '<div class="text-center"><a href="'+site.base_url+'welcome/download/' + x + '" class="tip" title="'+lang.download+'"><i class="fa fa-file-o"></i></a></div>';
}
function user_status(x) {
    var y = x.split("__");
    return y[0] == 1 ?
    '<a href="'+site.base_url+'auth/deactivate/'+ y[1] +'" data-toggle="modal" data-target="#myModal"><span class="label label-success"><i class="fa fa-check"></i> '+lang['active']+'</span></a>' :
    '<a href="'+site.base_url+'auth/activate/'+ y[1] +'"><span class="label label-danger"><i class="fa fa-times"></i> '+lang['inactive']+'</span><a/>';
}

function text_right(x){
    return '<div class="text-right">'+x+'</div>';
}
function text_left(x){
    return '<div class="text-left">'+x+'</div>';
}
function text_center(x){
    return '<div class="text-center">'+x+'</div>';
}
function row_status(x) {
    if(x == null) {
        return '';
    } else if(x == 'pending' || x == 'assigned' || x == 'cleared' || x == 'requested' || x == 'applied' || x == 'reservation' || x == 'repairing' || x == 'dirty') {
        return '<div class="text-center"><span class="row_status label label-warning">'+lang[x]+'</span></div>';
    } else if(x == 'expense' ||x == 'enrolled' || x == 'completed' || x == 'paid' || x == 'booked' || x == 'sent' || x == 'received' || x == 'checked_in' || x == 'active' || x == 'pawn_rate' || x == 'pawn_received' || x == 'yes' || x == 'cleaned') {
        return '<div class="text-center"><span class="row_status label label-success">'+lang[x]+'</span></div>';
    } else if(x == 'partial' || x == 'transferring' || x == 'checked_out' || x == 'ordered' || x == 'approved' || x == 'packaging' || x == 'fixed' || x == 'disbursed' || x == 'done' || x == 'free') {
        return '<div class="text-center"><span class="row_status label label-info">'+lang[x]+'</span></div>';
    } else if(x == 'spoiled' || x == 'difference' || x == 'due' || x == 'returned' || x == 'rejected' || x == 'inactive' ||  x == 'maintenance' || x == 'room_blocking' || x == 'payoff' || x == 'pawn_sent' || x == 'closed' || x == 'no' || x == 'expired' || x == 'deleted' || x == 'take_away' || x == 'declined' || x == 'suspended' || x == 'cancelled' || x == 'not_done') {
        return '<div class="text-center"><span class="row_status label label-danger">'+lang[x]+'</span></div>';
    } else {
        return '<div class="text-center"><span class="row_status label label-default">'+x+'</span></div>';
    }
}


function pay_status(x) {
    if(x == null) {
        return '';
    } else if(x == 'pending') {
        return '<div class="text-center"><span class="payment_status label label-warning">'+lang[x]+'</span></div>';
    } else if(x == 'completed' || x == 'paid' || x == 'sent' || x == 'received') {
        return '<div class="text-center"><span class="payment_status label label-success">'+lang[x]+'</span></div>';
    } else if(x == 'partial' || x == 'transferring' || x == 'ordered') {
        return '<div class="text-center"><span class="payment_status label label-info">'+lang[x]+'</span></div>';
    } else if(x == 'due' || x == 'returned') {
        return '<div class="text-center"><span class="payment_status label label-danger">'+lang[x]+'</span></div>';
    } else {
        return '<div class="text-center"><span class="payment_status label label-default">'+x+'</span></div>';
    }
}
function formatSA (x) {
    x=x.toString();
    var afterPoint = '';
    if(x.indexOf('.') > 0)
       afterPoint = x.substring(x.indexOf('.'),x.length);
    x = Math.floor(x);
    x=x.toString();
    var lastThree = x.substring(x.length-3);
    var otherNumbers = x.substring(0,x.length-3);
    if(otherNumbers != '')
        lastThree = ',' + lastThree;
    var res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;

    return res;
}

function unitToBaseQty(qty, unitObj) {
    switch(unitObj.operator) {
        case '*':
            return parseFloat(qty)*parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(qty)/parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(qty)+parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(qty)-parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(qty);
    }
}

function unitToBasePrice(price, unitObj) {
    switch(unitObj.operator) {
        case '*':
            return parseFloat(price)/parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(price)*parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(price)-parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(price)+parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(s);
    }
}

function baseToUnitQty(qty, unitObj) {
    switch(unitObj.operator) {
        case '*':
            return parseFloat(qty)/parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(qty)*parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(qty)-parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(qty)+parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(qty);
    }
}

function set_page_focus() {
    if (site.settings.set_focus == 1) {
        $('#add_item').attr('tabindex', an);
        $('[tabindex='+(an-1)+']').focus().select();
    } else {
        $('#add_item').attr('tabindex', 1);
        $('#add_item').focus();
    }
    $('.rquantity').bind('keypress', function (e) {
        if (e.keyCode == 13) {
            $('#add_item').focus();
        }
    });
}

$(document).ready(function() {
    $('#view-customer').click(function(){
        $('#myModal').modal({remote: site.base_url + 'customers/view/' + $("input[name=customer]").val()});
        $('#myModal').modal('show');
    });
    $('#view-supplier').click(function(){
        $('#myModal').modal({remote: site.base_url + 'suppliers/view/' + $("input[name=supplier]").val()});
        $('#myModal').modal('show');
    });
    $('#view-patient').click(function(){
        $('#myModal').modal({remote: site.base_url + 'hospitals/view_patient/' + $("input[name=patient]").val()});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.customer_details_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'customers/view/' + $(this).parent('.customer_details_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.supplier_details_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'suppliers/view/' + $(this).parent('.supplier_details_link').attr('id')});
        $('#myModal').modal('show');
    });

    $('body').on('click', '.bom_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'converts/view_boms/' + $(this).parent('.bom_link').attr('id')});
        $('#myModal').modal('show');
    });

    $('body').on('click', '.product_link td:not(:first-child, :nth-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/modal_view/' + $(this).parent('.product_link').attr('id')});
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'products/view/' + $(this).parent('.product_link').attr('id');
    });
    $('body').on('click', '.product_link2 td:first-child, .product_link2 td:nth-child(2)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/modal_view/' + $(this).closest('tr').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.product_link3 td:first-child, .product_link3 td:nth-child(2), .product_link3 td:nth-child(3)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/modal_view/' + $(this).closest('tr').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.purchase_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/modal_view/' + $(this).parent('.purchase_link').attr('id')});
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'purchases/view/' + $(this).parent('.purchase_link').attr('id');
    });
    $('body').on('click', '.purchase_link2 td', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/modal_view/' + $(this).closest('tr').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.purchase_link3 td:not(:nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/modal_view/' + $(this).parent('.purchase_link3').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.purchase_order_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'purchase_orders/modal_view/' + $(this).parent('.purchase_order_link').attr('id')});
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'purchases/view/' + $(this).parent('.purchase_link').attr('id');
    });
    $('body').on('click', '.purchase_request_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'purchase_requests/modal_view/' + $(this).parent('.purchase_request_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.receive_link2 td:not(:first-child,:last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/receive_note/' + $(this).closest('tr').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.expense_link td:not(:nth-child(5), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'expenses/expense_note/' + $(this).parent('.expense_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.transfer_link td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'transfers/view/' + $(this).parent('.transfer_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.transfer_link2', function() {
        $('#myModal').modal({remote: site.base_url + 'transfers/view/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.installment_payment_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'installments/view_payments/' + $(this).parent('.installment_payment_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.sell_fuel_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_sell_fuel/' + $(this).parent('.sell_fuel_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.maintenance_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_maintenance/' + $(this).parent('.maintenance_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.fuel_sale_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_fuel_sale/' + $(this).parent('.fuel_sale_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.fuel_customer_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_fuel_customer/' + $(this).parent('.fuel_customer_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.invoice_link td:not(:first-child, :nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/modal_view/' + $(this).parent('.invoice_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.invoice_link td:not(:first-child, :nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/modal_view/' + $(this).parent('.invoice_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.sale_concrete_link td:not(:first-child, :nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/modal_view_sale_concrete/' + $(this).parent('.sale_concrete_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.inventory_opening_balance_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'system_settings/modal_ivo/' + $(this).parent('.inventory_opening_balance_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.rental_link td:not(:first-child, :nth-last-child(2), :nth-last-child(3), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'rentals/modal_view/' + $(this).parent('.rental_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.commission_target_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/modal_commission_taget/?ids=' + $(this).parent('.commission_target_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.invoice_link2 td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/modal_view/' + $(this).closest('tr').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.invoice_link3 td:not(:nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/modal_view/' + $(this).parent('.invoice_link3').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.sale_order_link td:not(:first-child, :nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sale_orders/modal_view/' + $(this).parent('.sale_order_link').attr('id')});
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sale_orders/view/' + $(this).parent('.sale_order_link').attr('id');
    });
    $('body').on('click', '.sale_order_link2 td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sale_orders/modal_view/' + $(this).closest('tr').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.ar_aging td:nth-child(3)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ar_aging_view/' + $(this).parent('.ar_aging').attr('id')+"/?p=5"});
    });
    
    $('body').on('click', '.ar_aging td:nth-child(4)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ar_aging_view/' + $(this).parent('.ar_aging').attr('id')+"/?p=1"});
    });
    
    $('body').on('click', '.ar_aging td:nth-child(5)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ar_aging_view/' + $(this).parent('.ar_aging').attr('id')+"/?p=2"});  
    });
    
    $('body').on('click', '.ar_aging td:nth-child(6)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ar_aging_view/' + $(this).parent('.ar_aging').attr('id')+"/?p=3"}); 
    });
    
    $('body').on('click', '.ar_aging td:nth-child(7)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ar_aging_view/' + $(this).parent('.ar_aging').attr('id')+"/?p=4"}); 
    });
    $('body').on('click', '.ar_aging td:nth-child(8)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ar_aging_view/' + $(this).parent('.ar_aging').attr('id')+"/?p=total"}); 
    });
    
    /*================*/
    
    $('body').on('click', '.ap_aging td:nth-child(3)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ap_aging_view/' + $(this).parent('.ap_aging').attr('id')+"/?p=5"});
    });

    $('body').on('click', '.ap_aging td:nth-child(4)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ap_aging_view/' + $(this).parent('.ap_aging').attr('id')+"/?p=1"});
    });

    $('body').on('click', '.ap_aging td:nth-child(5)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ap_aging_view/' + $(this).parent('.ap_aging').attr('id')+"/?p=2"});  
    });

    $('body').on('click', '.ap_aging td:nth-child(6)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ap_aging_view/' + $(this).parent('.ap_aging').attr('id')+"/?p=3"}); 
    });

    $('body').on('click', '.ap_aging td:nth-child(7)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ap_aging_view/' + $(this).parent('.ap_aging').attr('id')+"/?p=4"}); 
    });
    $('body').on('click', '.ap_aging td:nth-child(8)', function() {
        $('#myModal').modal({ remote: site.base_url + 'reports/ap_aging_view/' + $(this).parent('.ap_aging').attr('id')+"/?p=total"}); 
    });
    
    /*================*/
    
    $('body').on('click', '.receipt_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({ remote: site.base_url + 'pos/view/' + $(this).parent('.receipt_link').attr('id') + '/1' });
    });
    
    $('body').on('click', '.customer_stock_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({ remote: site.base_url + 'pos/view_customer_stock/' + $(this).parent('.customer_stock_link').attr('id') + '/1' });
    });
    
    $('body').on('click', '.return_link td:not(:first-child, :nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_return/' + $(this).parent('.return_link').attr('id')});
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.return_link').attr('id');
    });
    $('body').on('click', '.return_link3 td:not(:nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/view_return/' + $(this).parent('.return_link3').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.return_link4 td:not(:nth-child(6), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/modal_view/' + $(this).parent('.return_link4').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.po_deposit_link td', function() {
        $('#myModal').modal({remote: site.base_url + 'purchase_orders/deposit_note/' + $(this).parent('.po_deposit_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.so_deposit_link td', function() {
        $('#myModal').modal({remote: site.base_url + 'sale_orders/deposit_note/' + $(this).parent('.so_deposit_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.fuel_expense_payment_link td', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/fuel_expense_payment_note/' + $(this).parent('.fuel_expense_payment_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.commission_payment_link td', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/commission_payment_note/' + $(this).parent('.commission_payment_link').attr('id')});
        $('#myModal').modal('show');
    });

    $('body').on('click', '.return_purchase_link td', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/view_return/' + $(this).parent('.return_purchase_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payment_link td', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/payment_note/' + $(this).parent('.payment_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.payment_link_last td:not(:last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/payment_note/' + $(this).parent('.payment_link_last').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.salesman_payment_link td', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/salesman_payment_note/' + $(this).parent('.salesman_payment_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.audit_trail_link td:not(:nth-child(4), :nth-child(5), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'reports/audit_trail_view/' + $(this).parent('.audit_trail_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payment_link2 td', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/payment_note/' + $(this).parent('.payment_link2').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payment_link3 td', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/payment_note/' + $(this).parent('.payment_link3').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payment_link4 td', function() {
        $('#myModal').modal({remote: site.base_url + 'purchases/payment_note/' + $(this).parent('.payment_link4').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.expense_link2 td:not(:last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'expenses/expense_note/' + $(this).closest('tr').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.quote_link td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'quotes/modal_view/' + $(this).parent('.quote_link').attr('id')});
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'quotes/view/' + $(this).parent('.quote_link').attr('id');
    });
    
    $('body').on('click', '.product_promotion_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'system_settings/modal_product_promotion/' + $(this).parent('.product_promotion_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.repair_link td:not(:first-child, :nth-last-child(4), :nth-last-child(3), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'repairs/modal_view/' + $(this).parent('.repair_link').attr('id')});
        $('#myModal').modal('show');
    });

    $('body').on('click', '.check_link td:not(:first-child, :nth-last-child(4), :nth-last-child(3), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'repairs/modal_view_check/' + $(this).parent('.check_link').attr('id')});
        $('#myModal').modal('show');
    });

    $('body').on('click', '.consignment_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/modal_view_consignment/' + $(this).parent('.consignment_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.loan_schedule_link td:not(:first-child, :last-child)', function() {
       $('#myModal').modal({remote: site.base_url + 'loans/payment_schedule/' + $(this).closest('tr').attr('id')});
       $('#myModal').modal('show');
    });
    
    $('body').on('click', '.installment_link', function() {
       window.open(site.base_url + 'installments/view/' + $(this).closest('tr').attr('id'));
    });
    
    $('body').on('click', '.installment_schedule_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'installments/payment_schedule/' + $(this).parent('.installment_schedule_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.rental_deposit_link', function() {
        $('#myModal').modal({remote: site.base_url + 'rentals/deposit_note/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.quote_link2', function() {
        $('#myModal').modal({remote: site.base_url + 'quotes/modal_view/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.delivery_link td:not(:first-child, :nth-last-child(2), :nth-last-child(3), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'deliveries/view/' + $(this).parent('.delivery_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.customer_link td:not(:first-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'customers/edit/' + $(this).parent('.customer_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.supplier_link td:not(:first-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'suppliers/edit/' + $(this).parent('.supplier_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.adjustment_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/view_adjustment/' + $(this).parent('.adjustment_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.convert_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/view_convert/' + $(this).parent('.convert_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.cost_adjustment_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/view_cost_adjustment/' + $(this).parent('.cost_adjustment_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.enter_journal_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'accountings/view_enterjournal/' + $(this).parent('.enter_journal_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.bank_reconciliation_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'accountings/view_bank_reconciliation/' + $(this).parent('.bank_reconciliation_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.adjustment_link2', function() {
        $('#myModal').modal({remote: site.base_url + 'products/view_adjustment/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.cost_adjustment_link2', function() {
        $('#myModal').modal({remote: site.base_url + 'products/view_cost_adjustment/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.using_stock_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'products/view_using_stock/' + $(this).parent('.using_stock_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    
    $('body').on('click', '.pawn_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pawns/modal_view/' + $(this).parent('.pawn_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.pur_pawn_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pawns/purchase_modal_view/' + $(this).parent('.pur_pawn_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.return_pawn_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pawns/return_modal_view/' + $(this).parent('.return_pawn_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.pawn_payment td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'pawns/modal_payment/' + $(this).parent('.pawn_payment').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.accounting_link', function() {
        $('#myModal').modal({remote: site.base_url + 'accountings/modal_view/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    
    
    $('body').on('click', '.take_leave_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/view_take_leave/' + $(this).parent('.take_leave_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.montly_time_card_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/monthly_time_card/' + $(this).parent('.montly_time_card_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.daily_time_card_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/daily_time_card/' + $(this).parent('.daily_time_card_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    
    $('body').on('click', '.department_time_card_link', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/department_time_card/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.employee_detail_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'hr/employee_details/' + $(this).parent('.employee_detail_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.id_card_link td:not(:first-child, :last-child)', function() {
        window.open(site.base_url + 'hr/view_employee_id_card/' + $(this).parent('.id_card_link').attr('id')); 
    });
    
    $('body').on('click', '.employee_leave_link', function() {
        $('#myModal').modal({remote: site.base_url + 'attendances/view_employee_leave/' + $(this).attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.kpi_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'hr/modal_view_kpi/' + $(this).parent('.kpi_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_review_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'hr/modal_view_salary_review/' + $(this).parent('.salary_review_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.cash_advance_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_cash_advance/' + $(this).parent('.cash_advance_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.benefit_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_benefit/' + $(this).parent('.benefit_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_salary/' + $(this).parent('.salary_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_13_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_salary_13/' + $(this).parent('.salary_13_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.salary_employee_13_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_salary_employee_13/' + $(this).parent('.salary_employee_13_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payslip_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_payslip/' + $(this).parent('.payslip_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payroll_payment_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'payrolls/modal_view_payment/' + $(this).parent('.payroll_payment_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    
    $('body').on('click', '.con_delivery_link td:not(:first-child,:nth-last-child(3), :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_delivery/' + $(this).parent('.con_delivery_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_delivery_link2 td', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_delivery/' + $(this).parent('.con_delivery_link2').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_sale_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_sale/' + $(this).parent('.con_sale_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_fuel_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_fuel/' + $(this).parent('.con_fuel_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_fuel_link2 td', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_fuel/' + $(this).parent('.con_fuel_link2').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_moving_waiting_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_moving_waiting/' + $(this).parent('.con_moving_waiting_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_mission_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_mission/' + $(this).parent('.con_mission_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_absent_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_absent/' + $(this).parent('.con_absent_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_fuel_expense_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_fuel_expense/' + $(this).parent('.con_fuel_expense_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_commission_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_commission/' + $(this).parent('.con_commission_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_adjustment_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_adjustment/' + $(this).parent('.con_adjustment_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_error_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_error/' + $(this).parent('.con_error_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.con_error_link2 td', function() {
        $('#myModal').modal({remote: site.base_url + 'concretes/modal_view_error/' + $(this).parent('.con_error_link2').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.salesman_commission_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/modal_view_salesman_commission/' + $(this).parent('.salesman_commission_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.receive_payment_link td:not(:first-child, :nth-last-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'sales/modal_view_receive_payment/' + $(this).parent('.receive_payment_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    
    $('body').on('click', '.student_link td:not(:first-child, :nth-child(2), :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_student/' + $(this).parent('.student_link').attr('id')});
        $('#myModal').modal('show');
    });
    $('body').on('click', '.study_info_link td:not(:first-child,  :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'schools/modal_view_study/' + $(this).parent('.study_info_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('body').on('click', '.patient_link td:not(:first-child, :last-child)', function() {
        $('#myModal').modal({remote: site.base_url + 'hospitals/view_patient/' + $(this).parent('.patient_link').attr('id')});
        $('#myModal').modal('show');
    });
    
    $('#clearLS').click(function(event) {
        bootbox.confirm(lang.r_u_sure, function(result) {
        if(result == true) {
            localStorage.clear();
            location.reload();
        }
        });
        return false;
    });
    $(document).on('click', '[data-toggle="ajax"]', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $.get(href, function( data ) {
            $("#myModal").html(data).modal();
        });
    });
    $(".sortable_rows").sortable({
        items: "> tr",
        appendTo: "parent",
        helper: "clone",
        placeholder: "ui-sort-placeholder",
        axis: "x",
        update: function(event, ui) {
            var item_id = $(ui.item).attr('data-item-id');
            console.log(ui.item.index());
        }
    }).disableSelection();
    
});


// Rate products
function rate(id){
    $.ajax({
        url : site.base_url + "products/rate",
        type : "GET",
        dataType : "JSON",
        data : { id : id },
        success : function(data){
            if(data){
                if(data.rate>0){
                    $(".d-rate").find("i").prop("class","fa fa-star");
                }else{
                    $(".d-rate").find("i").prop("class","fa fa-star-o");
                }
            }
        }
    });
}

function fixAddItemnTotals() {
    var ai = $("#sticker");
    var aiTop = (ai.position().top)+250;
    var bt = $("#bottom-total");
    $(window).scroll(function() {
        var windowpos = $(window).scrollTop();
        if (windowpos >= aiTop) {
            ai.addClass("stick").css('width', ai.parent('form').width()).css('zIndex', 2);
            if ($.cookie('cus_theme_fixed') == 'yes') { ai.css('top', '40px'); } else { ai.css('top', 0); }
            $('#add_item').removeClass('input-lg');
            $('.addIcon').removeClass('fa-2x');
        } else {
            ai.removeClass("stick").css('width', bt.parent('form').width()).css('zIndex', 2);
            if ($.cookie('cus_theme_fixed') == 'yes') { ai.css('top', 0); }
            $('#add_item').addClass('input-lg');
            $('.addIcon').addClass('fa-2x');
        }
        if (windowpos <= ($(document).height() - $(window).height() - 120)) {
            bt.css('position', 'fixed').css('bottom', 0).css('width', bt.parent('form').width()).css('zIndex', 2);
        } else {
            bt.css('position', 'static').css('width', ai.parent('form').width()).css('zIndex', 2);
        }
    });
}
function ItemnTotals() {
    //fixAddItemnTotals();
    //$(window).bind("resize", fixAddItemnTotals);
}                       
function formatDateYMD(date){
    var result = date.split('/');
    return result[2]+'-'+result[1]+'-'+result[0];
}

if(site.settings.auto_detect_barcode == 1) {
    $(document).ready(function() {
        var pressed = false;
        var chars = [];
        $(window).keypress(function(e) {
            chars.push(String.fromCharCode(e.which));
            if (pressed == false) {
                setTimeout(function(){
                    if (chars.length >= 8) {
                        var barcode = chars.join("");
                        $( "#add_item" ).focus().autocomplete( "search", barcode );
                    }
                    chars = [];
                    pressed = false;
                },200);
            }
            pressed = true;
        });
    });
}
$('.sortable_table tbody').sortable({
    containerSelector: 'tr'
});
$(window).bind("resize", widthFunctions);
$(window).load(widthFunctions);


