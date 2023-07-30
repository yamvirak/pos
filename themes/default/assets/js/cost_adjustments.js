$(document).ready(function () {
    if (!localStorage.getItem('caref')) {
        localStorage.setItem('caref', '');
    }

    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function (e) {
        $('#add_item').focus();
    });
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }

    //localStorage.clear();
    // If there is any item in localStorage
    if (localStorage.getItem('caitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('caitems')) {
                    localStorage.removeItem('caitems');
                }
                if (localStorage.getItem('caref')) {
                    localStorage.removeItem('caref');
                }
                if (localStorage.getItem('canote')) {
                    localStorage.removeItem('canote');
                }
                if (localStorage.getItem('cadate')) {
                    localStorage.removeItem('cadate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#caref').change(function (e) {
        localStorage.setItem('caref', $(this).val());
    });
    if (caref = localStorage.getItem('caref')) {
        $('#caref').val(caref);
    }


    //$(document).on('change', '#canote', function (e) {
        $('#canote').redactor('destroy');
        $('#canote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('canote', v);
            }
        });
        if (canote = localStorage.getItem('canote')) {
            $('#canote').redactor('set', canote);
        }

    // prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });


    /* ---------------------- 
     * Delete Row Method 
     * ---------------------- */

    $(document).on('click', '.cadel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete caitems[item_id];
        row.remove();
        if(caitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('caitems', JSON.stringify(caitems));
            loadItems();
            return;
        }
    });
	$(document).on("focus", '.new_cost', function () {
        old_new_cost = $(this).val();
    }).on("change", '.new_cost', function () {

        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) /*|| parseFloat($(this).val()) < 0*/) {
            $(this).val(old_new_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_new_cost = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        caitems[item_id].row.new_cost = new_new_cost;
        localStorage.setItem('caitems', JSON.stringify(caitems));
        loadItems();
    });
	



});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {
	

    if (localStorage.getItem('caitems')) {
		
		count = 1;
        an = 1;
        $("#caTable tbody").empty();
        caitems = JSON.parse(localStorage.getItem('caitems'));
        $.each(caitems, function () {
			
            var item = this;
			var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
			if(item.row.old_cost > 0){
				tr_html += '<td class="text-center">' +formatMoney(item.row.old_cost)+ ' ( ' +formatMoney(item.row.cost)+ ' )<input type="hidden" name="old_cost[]" value="'+item.row.old_cost+'"/></td>';
			}else{
				tr_html += '<td class="text-center">' +formatMoney(item.row.cost)+ '<input type="hidden" name="old_cost[]" value="'+item.row.cost+'"/></td>';
			}
			if(!item.row.new_cost){
				item.row.new_cost = formatDecimal(item.row.cost);
			}
            tr_html += '<td><input class="form-control text-center new_cost"  name="new_cost[]" type="text" value="' + (item.row.new_cost) + '" data-id="' + row_no + '" data-item="' + item_id + '"  onClick="this.select();"></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip cadel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#caTable");
            count ++;
            an++;
        });

        var col = 2;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#caTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}


/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_adjustment_item(item) {
    if (count == 1) {
        caitems = {};
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (caitems[item_id]) {
        var new_qty = parseFloat(caitems[item_id].row.qty) + 1;
        caitems[item_id].row.base_quantity = new_qty;
        if(caitems[item_id].row.unit != caitems[item_id].row.base_unit) {
            $.each(caitems[item_id].units, function(){
                if (this.id == caitems[item_id].row.unit) {
                    caitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        caitems[item_id].row.qty = new_qty;
    } else {
        caitems[item_id] = item;
    }
    caitems[item_id].order = new Date().getTime();
    localStorage.setItem('caitems', JSON.stringify(caitems));
    loadItems();
    return true;
}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}