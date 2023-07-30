$(document).ready(function () {
    if (!localStorage.getItem('mtref')) {
        localStorage.setItem('mtref', '');
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
    if (localStorage.getItem('mtitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('mtitems')) {
                    localStorage.removeItem('mtitems');
                }
                if (localStorage.getItem('mtref')) {
                    localStorage.removeItem('mtref');
                }
				if (localStorage.getItem('mtcustomer')) {
					localStorage.removeItem('mtcustomer');
				}
                if (localStorage.getItem('mtwarehouse')) {
                    localStorage.removeItem('mtwarehouse');
                }
                if (localStorage.getItem('mtnote')) {
                    localStorage.removeItem('mtnote');
                }
                if (localStorage.getItem('mtdate')) {
                    localStorage.removeItem('mtdate');
                }
                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#mtref').change(function (e) {
        localStorage.setItem('mtref', $(this).val());
    });
    if (mtref = localStorage.getItem('mtref')) {
        $('#mtref').val(mtref);
    }
	
	$('#mtexpiry').change(function (e) {
        localStorage.setItem('mtexpiry', $(this).val());
    });
    if (mtexpiry = localStorage.getItem('mtexpiry')) {
        $('#mtexpiry').val(mtexpiry);
    }
	
	$('#mtcustomer').change(function (e) {
        localStorage.setItem('mtcustomer', $(this).val());
    });
    if (mtcustomer = localStorage.getItem('mtcustomer')) {
        $('#mtcustomer').select2("val", mtcustomer);
    }
	
    $('#mtwarehouse').change(function (e) {
        localStorage.setItem('mtwarehouse', $(this).val());
    });
    if (mtwarehouse = localStorage.getItem('mtwarehouse')) {
        $('#mtwarehouse').select2("val", mtwarehouse);
    }

    //$(document).on('change', '#mtnote', function (e) {
        $('#mtnote').redactor('destroy');
        $('#mtnote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('mtnote', v);
            }
        });
        if (mtnote = localStorage.getItem('mtnote')) {
            $('#mtnote').redactor('set', mtnote);
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

    $(document).on('click', '.mtdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete mtitems[item_id];
        row.remove();
		
        if(mtitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('mtitems', JSON.stringify(mtitems));
            loadItems();
            return;
        }
    });

    /* --------------------------
     * Edit Row Quantity Method 
     -------------------------- */
	var old_row_qty = 0;
    $(document).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
		var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        mtitems[item_id].row.base_quantity = new_qty;
        if(mtitems[item_id].row.unit != mtitems[item_id].row.base_unit) {
            $.each(mtitems[item_id].units, function(){
                if (this.id == mtitems[item_id].row.unit) {
                    mtitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        mtitems[item_id].row.qty = new_qty;		       
        localStorage.setItem('mtitems', JSON.stringify(mtitems));
        loadItems();
    });
	
	$(document).on("change", '.warranty', function () {
        var row = $(this).closest('tr');
		var out_of_warranty = 0;
		if($(this).is(':checked')){
			out_of_warranty = 1;
		}
		row.find(".out_of_warranty").val(out_of_warranty);
        var item_id = row.attr('data-item-id');
        mtitems[item_id].row.out_of_warranty = out_of_warranty;
        localStorage.setItem('mtitems', JSON.stringify(mtitems));
    });
	
	$(document).on("change", '.product_note', function () {
        var row = $(this).closest('tr');
        var product_note = $(this).val(),
        item_id = row.attr('data-item-id');
        mtitems[item_id].row.product_note = product_note;
        localStorage.setItem('mtitems', JSON.stringify(mtitems));
    });
	
    $(document).on("change", '.rvariant', function () {
        var row = $(this).closest('tr');
        var new_opt = $(this).val(),
        item_id = row.attr('data-item-id');
        mtitems[item_id].row.option = new_opt;
        localStorage.setItem('mtitems', JSON.stringify(mtitems));
    });
	
	$(document).on('change', '.punit', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');	
		var parent = $(this).parent().parent();
		var rquantity = parent.find(".rquantity").val();
		
		if (!is_numeric(rquantity) || parseFloat(rquantity) < 0) {
			$(this).val(old_row_qty);
			bootbox.alert(lang.unexpected_value);
			return;
		}		
		var unit = $(this).val();
		var base_quantity = rquantity;
		if(unit != mtitems[item_id].row.base_unit) {
			$.each(mtitems[item_id].units, function() {				
				if (this.id == unit) {
					base_quantity = unitToBaseQty(parseFloat(rquantity), this);
				}
			});
		} 
		mtitems[item_id].row.qty = parseFloat(rquantity),
		mtitems[item_id].row.base_quantity = parseFloat(base_quantity),
		mtitems[item_id].row.unit = unit,
		localStorage.setItem('mtitems', JSON.stringify(mtitems));
        loadItems();
	});

});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {

    if (localStorage.getItem('mtitems')) {
        count = 1;
        an = 1;
        $("#mtTable tbody").empty();
        mtitems = JSON.parse(localStorage.getItem('mtitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(mtitems, function(o){return [parseInt(o.order)];}) : mtitems;
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_qty = item.row.qty, item_option = item.row.option,item_expiry = item.row.expiry, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var type = item.row.type ? item.row.type : '';            			
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
			var product_note = (item.row.product_note?item.row.product_note:""), out_of_warranty = item.row.out_of_warranty;
			
            var opt = $("<select id=\"poption\" name=\"variant\[\]\" class=\"form-control select rvariant\" />");
            if(item.options !== false) {
                $.each(item.options, function () {
                    if (item.row.option == this.id)
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt);
                    else
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt);
                opt = opt.hide();
            }			
			
			var opt2 = $("<select id=\"unit\" name=\"unit\[\]\" class=\"form-control select punit\" />");
            if(item.units !== false) {
                $.each(item.units, function () {
                    if (item.row.unit == this.id)
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt2);
                    else
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt2);
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt2);
                opt2 = opt2.hide();
            }
			
            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
			tr_html += '<td>'+(opt.get(0).outerHTML)+'</td>';
			tr_html += '<td>'+(opt2.get(0).outerHTML)+'<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
			tr_html += '<td class="text-center"><center><input type="checkbox"  class="warranty" '+ (out_of_warranty==1?"checked":"") +' /> <input type="hidden" class="out_of_warranty" name="out_of_warranty[]" value="'+out_of_warranty+'" /></center></td>';
			tr_html += '<td class="text-center"><input type="text" name="product_note[]" class="form-control product_note" value="'+product_note+'" /></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip mtdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#mtTable");
            count += parseFloat(item_qty);
            an++;
        });
		
        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
		tfoot += '<th></th><th></th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#mtTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
		if (count > 1) {
            $('#mtcustomer').select2("readonly", true);
			$('#mtwarehouse').select2("readonly", true);
        }
        set_page_focus();
    }
}


/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_mt_item(item) {

    if (count == 1) {
        mtitems = {};
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (mtitems[item_id]) {
        var new_qty = parseFloat(mtitems[item_id].row.qty) + 1;
        mtitems[item_id].row.base_quantity = new_qty;
        if(mtitems[item_id].row.unit != mtitems[item_id].row.base_unit) {
            $.each(mtitems[item_id].units, function(){
                if (this.id == mtitems[item_id].row.unit) {
                    mtitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        mtitems[item_id].row.qty = new_qty;

    } else {
        mtitems[item_id] = item;
    }
    mtitems[item_id].order = new Date().getTime();
    localStorage.setItem('mtitems', JSON.stringify(mtitems));
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