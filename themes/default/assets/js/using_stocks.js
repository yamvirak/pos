$(document).ready(function () {
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}


//localStorage.clear();
// If there is any item in localStorage
if (localStorage.getItem('using')) {
    loadItems();
}

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('using')) {
					localStorage.removeItem('using');
				}
				if (localStorage.getItem('ref')) {
					localStorage.removeItem('ref');
				}
				if (localStorage.getItem('usnote')) {
					localStorage.removeItem('usnote');
				}
				if (localStorage.getItem('warehouse_id')) {
					localStorage.removeItem('warehouse_id');
				}
				if (localStorage.getItem('usdate')) {
					localStorage.removeItem('usdate');
				}
				if (localStorage.getItem('usstaff')) {
					localStorage.removeItem('usstaff');
				}
				if (localStorage.getItem('uscustomer')) {
					localStorage.removeItem('uscustomer');
				}
                $('#modal-loading').show();
                location.reload();
             }
         });
});

// save and load the fields in and/or from localStorage

$('#ref').change(function (e) {
    localStorage.setItem('ref', $(this).val());
});
if (ref = localStorage.getItem('ref')) {
    $('#ref').val(ref);
}
$('#warehouse_id').change(function (e) {
    localStorage.setItem('warehouse_id', $(this).val());
});
if (warehouse_id = localStorage.getItem('warehouse_id')) {
    $('#warehouse_id').select2("val", warehouse_id);
}
$('#warehouse_id').change(function (e) {
    localStorage.setItem('warehouse_id', $(this).val());
});
if (warehouse_id = localStorage.getItem('warehouse_id')) {
    $('#warehouse_id').select2("val", warehouse_id);
    if (count > 1) {
        $('#warehouse_id').select2("readonly", true);
    }
}

var $customer = $('#uscustomer');
$customer.change(function (e) {
	localStorage.setItem('uscustomer', $(this).val());
});
if (uscustomer = localStorage.getItem('uscustomer')) {
	$customer.val(uscustomer).select2({
		minimumInputLength: 1,
		data: [],
		initSelection: function (element, callback) {
			$.ajax({
				type: "get", async: false,
				url: site.base_url+"customers/getCustomer/" + $(element).val(),
				dataType: "json",
				success: function (data) {
					callback(data[0]);
				}
			});
		},
		ajax: {
			url: site.base_url + "customers/suggestions",
			dataType: 'json',
			quietMillis: 15,
			data: function (term, page) {
				return {
					term: term,
					limit: 10
				};
			},
			results: function (data, page) {
				if (data.results != null) {
					return {results: data.results};
				} else {
					return {results: [{id: '', text: 'No Match Found'}]};
				}
			}
		}
	});
} else {
	nsCustomer();
}

    //$(document).on('change', '#usnote', function (e) {
        $('#usnote').redactor('destroy');
        $('#usnote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('usnote', v);
            }
        });
        if (usnote = localStorage.getItem('usnote')) {
            $('#usnote').redactor('set', usnote);
        }

        $(document).on('change', '.rexpiry', function () { 
            var item_id = $(this).closest('tr').attr('data-item-id');
            using[item_id].row.expiry = $(this).val();
            localStorage.setItem('using', JSON.stringify(using));
        });


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

    $(document).on('click', '.todel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete using[item_id];
        row.remove();
        if(using.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('using', JSON.stringify(using));
            loadItems();
            return;
        }
    });
	
	
	$(document).on("change", '.rexpired', function () {
		var new_expired = $(this).val();
		var item_id = $(this).closest('tr').attr('data-item-id');
		using[item_id].row.expired = new_expired;
		localStorage.setItem('using', JSON.stringify(using));
		loadItems();
	});
    /* --------------------------
     * Edit Row Quantity Method 
     -------------------------- */
    var old_row_qty;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        using[item_id].row.base_quantity = new_qty;
        if(using[item_id].row.unit != using[item_id].row.base_unit) {
            $.each(using[item_id].units, function(){
                if (this.id == using[item_id].row.unit) {
                    using[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        using[item_id].row.qty = new_qty;
        localStorage.setItem('using', JSON.stringify(using));
        loadItems();
    });
	
	$(document).on('change', '.sunit', function () {
        var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		var item = using[item_id];
		var qty = item.row.qty;
		var new_unit = parseFloat($(this).val());
		var base_quantity = qty;
		if(new_unit != item.row.base_unit) {
            $.each(item.units, function(){
                if (this.id == new_unit) {
                    base_quantity = unitToBaseQty(qty, this);
				}
            });
        }
		using[item_id].row.base_quantity = base_quantity;
		using[item_id].row.unit = new_unit;
		localStorage.setItem('using', JSON.stringify(using));
        loadItems();
    });
    
    /* --------------------------
     * Edit Row Cost Method 
     -------------------------- */
     var old_cost;
     $(document).on("focus", '.rcost', function () {
        old_cost = $(this).val();
    }).on("change", '.rcost', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_cost = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        using[item_id].row.cost = new_cost;
        localStorage.setItem('using', JSON.stringify(using));
        loadItems();
    });
    
    $(document).on("click", '#removeReadonly', function () { 
     $('#warehouse_id').select2('readonly', false); 
     return false;
 });
    
    
});
function nsCustomer() {
    $('#uscustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {
                    return {results: data.results};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}
/* -----------------------
 * Edit Row Modal Hanlder 
 ----------------------- */
 $(document).on('click', '.edit', function () {
    var row = $(this).closest('tr');
    var row_id = row.attr('id');
    item_id = row.attr('data-item-id');
    item = using[item_id];
    var qty = row.children().children('.rquantity').val(), 
    product_option = row.children().children('.roption').val(),
    cost = row.children().children('.rucost').val();
    $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
	if (site.settings.product_serial !== 0) {
		$('#pserial').val(row.children().children('.rserial').val());
	}

    var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
    if(item.options !== false) {
        var o = 1;
        opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
        $.each(item.options, function () {
            if(o == 1) {
                if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
            }
            $("<option />", {value: this.id, text: this.name}).appendTo(opt);
            o++;
        });
    } 
    uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if(this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });

	if(item.product_serials){
		uopt1 = $("<select class='form-control input-tip select' data-placeholder='Select Serial' id='product_serial' name='product_serial'  />");
		$("<option />", { value: '', text: ''}).appendTo(uopt1);		
		$.each(item.product_serials, function () {
			var serialno = row.children().children('.rserial').val();
			if(this.serial == serialno) {
				$("<option />", { value: this.id, text: this.serial, selected:true}).appendTo(uopt1);
			} else {
				$("<option />", { value: this.id, text: this.serial}).appendTo(uopt1);
			}
		});			
		$("#pserials-div").html(uopt1);
	}	
    $('#poptions-div').html(opt);
    $('#punits-div').html(uopt);
    $('select.select').select2({minimumResultsForSearch: 7});
    $('#pquantity').val(qty);
    $('#old_qty').val(qty);
    $('#pprice').val(cost);
    $('#poption').select2('val', item.row.option);
    $('#old_price').val(cost);
    $('#row_id').val(row_id);
    $('#item_id').val(item_id);
    $('#pserial').val(row.children().children('.rserial').val());
    $('#pproduct_tax').select2('val', row.children().children('.rproduct_tax').val());
    $('#pdiscount').val(row.children().children('.rdiscount').val());
    $('#prModal').appendTo("body").modal('show');

});

$('#prModal').on('shown.bs.modal', function (e) {
    if($('#poption').select2('val') != '') {
        $('#poption').select2('val', product_variant);
        product_variant = 0;
    }
});

$(document).on('change', '#punit', function () {
    var row = $('#' + $('#row_id').val());
    var item_id = row.attr('data-item-id');
    var item = using[item_id];
    if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
        $(this).val(old_row_qty);
        bootbox.alert(lang.unexpected_value);
        return;
    }
    var unit = $('#punit').val();
    if(unit != using[item_id].row.base_unit) {
        $.each(item.units, function() {
            if (this.id == unit) {
                $('#pprice').val(formatDecimal((parseFloat(item.row.base_unit_cost)*(unitToBaseQty(1, this))), 4)).change();
            }
        });
    } else {
        $('#pprice').val(formatDecimal(item.row.base_unit_cost)).change();
    }
});

/* -----------------------
 * Edit Row Method 
 ----------------------- */
 $(document).on('click', '#editItem', function () {
    var row = $('#' + $('#row_id').val());
    var item_id = row.attr('data-item-id');
    if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
        $(this).val(old_row_qty);
        bootbox.alert(lang.unexpected_value);
        return;
    }
    var unit = $('#punit').val();
    var base_quantity = parseFloat($('#pquantity').val());

	
	if(unit != using[item_id].row.base_unit) {
        $.each(using[item_id].units, function(){
            if (this.id == unit) {
                base_quantity = unitToBaseQty($('#pquantity').val(), this);
            }
        });
    }
	if (site.settings.product_serial == 1) {
		using[item_id].row.cost = parseFloat($('#pscost').val());
	}
	
    using[item_id].row.fup = 1,
    using[item_id].row.qty = parseFloat($('#pquantity').val()),
    using[item_id].row.base_quantity = parseFloat(base_quantity),
    using[item_id].row.unit = unit,
    using[item_id].row.cost = parseFloat($('#pprice').val()),
    // using[item_id].row.tax_rate = new_pr_tax_rate,
    using[item_id].row.discount = $('#pdiscount').val(),
    using[item_id].row.option = $('#poption').val(),
	using[item_id].row.serial = $('#pserial').val();
    // using[item_id].row.tax_method = 1;
    localStorage.setItem('using', JSON.stringify(using));
    $('#prModal').modal('hide');
    
    loadItems();
    return;
});

/* -----------------------
 * Misc Actions
 ----------------------- */

 function loadItems() {

    if (localStorage.getItem('using')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        $("#toTable tbody").empty();
        $('#add_transfer, #edit_transfer').attr('disabled', false);
        using = JSON.parse(localStorage.getItem('using'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(using, function(o){return [parseInt(o.order)];}) :   using;

        var order_no = new Date().getTime();
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : order_no++;
            var warehouse_id = localStorage.getItem('warehouse_id'), check = false;
            var product_id = item.row.id, item_type = item.row.type, item_cost = item.row.cost, item_qty = item.row.qty, item_bqty = item.row.quantity_balance, item_oqty = item.row.ordered_quantity, item_expiry = item.row.expiry, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
			var unit_cost = item.row.cost;
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;

            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            
            item_cost = item_tax_method == 0 ? formatDecimal(unit_cost-pr_tax_val, 4) : formatDecimal(unit_cost);
            unit_cost = formatDecimal(unit_cost+item_discount, 4);
            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
			
			if(item.row.fup != 1 && product_unit != item.row.base_unit) {
				$.each(item.units, function(){
					if (this.id == product_unit) {
						base_quantity = formatDecimalRaw(unitToBaseQty(item.row.qty, this));
					}
				});
			}
			var qoh = item_aqty;  
			if (site.settings.product_expiry == 1) {
				var expiry_select = '<select name="expired_data[]"  class="form-control select rexpired" style="width:100%;">';
				var expiry_option = '';
				$.each(item.product_expiries, function () {
					if((this.quantity -0) > 0){
						if(item.row.expired == this.expiry){
							expiry_option += '<option selected value="'+this.expiry+'">'+this.expiry+'</option>';
							qoh = formatDecimalRaw(this.quantity);
						}else{
							expiry_option += '<option value="'+this.expiry+'">'+this.expiry+'</option>';
						}
						
					}
                });
				expiry_select += expiry_option;
				expiry_select += '</select>';
				
			}

            var row_no = item_id;//(new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            
			tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-edit tip tointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';           
			if(site.settings.show_qoh == 1){
				tr_html += '<td class="text-center"><span>'+(item_type=='standard' ? formatDecimal(qoh) : '')+'</span></td>'
			}
			if (site.settings.product_expiry == 1) {
                tr_html += '<td>'+expiry_select+'</td>';
            }
			if (site.settings.product_serial == 1) {
                tr_html += '<td  class="text-right"><input readonly="true" class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="'+item_serial+'"></td>';
            }
			
			tr_html += '<td><input name="quantity_balance[]" type="hidden" class="rbqty" value="' + formatDecimal(item_bqty, 4) + '"><input name="ordered_quantity[]" type="hidden" class="roqty" value="' + formatDecimal(item_oqty, 4) + '"><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
			if (site.settings.show_unit == 1) {
				uopt = $("<select name=\"sunit\" class=\"form-control sunit select\" />");
				$.each(item.units, function () {
					if(this.id == item.row.unit) {
						$("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
					} else {
						$("<option />", {value: this.id, text: this.name}).appendTo(uopt);
					}
				});
				tr_html +='<td>'+(uopt.get(0).outerHTML)+'</td>';
			}
			tr_html += '<td class="text-center"><i class="fa fa-times tip todel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#toTable");
            total += formatDecimal(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;
            if (item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                        if(site.settings.overselling != 1) { $('#add_using_stock, #edit_using_stock').attr('disabled', true); }
                    }
                });
            } else if(item_type == 'standard' && site.settings.product_expiry == 1 && item.product_expiries){
				base_quantity = base_quantity - 0;
				item_aqty = item_aqty - 0;
				if(base_quantity > qoh || base_quantity > item_aqty){
					$('#row_' + row_no).addClass('danger');
					if(site.settings.overselling != 1) { $('#add_using_stock, #edit_using_stock').attr('disabled', true); }
				}
			} else if(base_quantity > item_aqty) { 
                $('#row_' + row_no).addClass('danger');
				if(site.settings.overselling != 1) { $('#add_using_stock, #edit_using_stock').attr('disabled', true); }
            }
            
        });
		var col = 1;
		if (site.settings.product_expiry == 1) { col++; }
		if (site.settings.product_serial == 1) { col++; }
		if (site.settings.show_qoh == 1) { col++; }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'" >Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
		if (site.settings.show_unit == 1) { 
			tfoot += '<th></th>';	
		}
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#toTable tfoot').html(tfoot);
		$('select').select2();
        // Totals calculations after item addition
        var gtotal = total + shipping;
        $('#total').text(formatMoney(total));
        $('#titems').text((an-1)+' ('+(parseFloat(count)-1)+')');

        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_transfer_item(item) {

    if (count == 1) {
        using = {};
        if ($('#warehouse_id').val()) {
            $('#warehouse_id').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (using[item_id]) {

        var new_qty = parseFloat(using[item_id].row.qty) + 1;
        using[item_id].row.base_quantity = new_qty;
        if(using[item_id].row.unit != using[item_id].row.base_unit) {
            $.each(using[item_id].units, function(){
                if (this.id == using[item_id].row.unit) {
                    using[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        using[item_id].row.qty = new_qty;

    } else {
        using[item_id] = item;
    }
    using[item_id].order = new Date().getTime();
    localStorage.setItem('using', JSON.stringify(using));
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

$(document).on('change', '#product_serial', function () {
	var row = $('#' + $('#row_id').val());
	var item_id = row.attr('data-item-id');
	var item = using[item_id];
	var serialno = [];
	$.each(item.product_serials, function(){
		serialno[this.id] = this.serial;
	});
	$('#pserial').val(serialno[$(this).val()]);

});
