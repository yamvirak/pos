$(document).ready(function () {
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}
// Order level shipping and discoutn localStorage
    if (sodiscount = localStorage.getItem('sodiscount')) {
        $('#sodiscount').val(sodiscount);
    }
    $('#sotax2').change(function (e) {
        localStorage.setItem('sotax2', $(this).val());
        $('#sotax2').val($(this).val());
    });
    if (sotax2 = localStorage.getItem('sotax2')) {
        $('#sotax2').select2("val", sotax2);
    }
    $('#sostatus').change(function (e) {
        localStorage.setItem('sostatus', $(this).val());
    });
    if (sostatus = localStorage.getItem('sostatus')) {
        $('#sostatus').select2("val", sostatus);
    }
	if (saleman_id = localStorage.getItem('sosaleman')) {
		$("#saleman_id").select2("val",saleman_id);	
    }
	$('#saleman_id').change(function (e) {
        localStorage.setItem('sosaleman', $(this).val());
        $('#saleman_id').val($(this).val());
    });
    var old_shipping;
    $('#soshipping').focus(function () {
        old_shipping = $(this).val();
    }).change(function () {
        if (!is_numeric($(this).val())) {
            $(this).val(old_shipping);
            bootbox.alert(lang.unexpected_value);
            return;
        } else {
            shipping = $(this).val() ? parseFloat($(this).val()) : '0';
        }
        localStorage.setItem('soshipping', shipping);
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#gtotal').text(formatMoney(gtotal));
        $('#tship').text(formatMoney(shipping));
    });
    if (soshipping = localStorage.getItem('soshipping')) {
        shipping = parseFloat(soshipping);
        $('#soshipping').val(shipping);
    } else {
        shipping = 0;
    }

    $('#sosupplier').change(function (e) {
        localStorage.setItem('sosupplier', $(this).val());
        $('#supplier_id').val($(this).val());
    });
    if (sosupplier = localStorage.getItem('sosupplier')) {
        $('#sosupplier').val(sosupplier).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
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
        nsSupplier();
    }

    // If there is any item in localStorage
    if (localStorage.getItem('soitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('soitems')) {
                        localStorage.removeItem('soitems');
                    }
                    if (localStorage.getItem('sodiscount')) {
                        localStorage.removeItem('sodiscount');
                    }
                    if (localStorage.getItem('sotax2')) {
                        localStorage.removeItem('sotax2');
                    }
                    if (localStorage.getItem('soshipping')) {
                        localStorage.removeItem('soshipping');
                    }
                    if (localStorage.getItem('soref')) {
                        localStorage.removeItem('soref');
                    }
                    if (localStorage.getItem('sowarehouse')) {
                        localStorage.removeItem('sowarehouse');
                    }
                    if (localStorage.getItem('sonote')) {
                        localStorage.removeItem('sonote');
                    }
                    if (localStorage.getItem('quinnote')) {
                        localStorage.removeItem('quinnote');
                    }
                    if (localStorage.getItem('socustomer')) {
                        localStorage.removeItem('socustomer');
                    }
                    if (localStorage.getItem('qucurrency')) {
                        localStorage.removeItem('qucurrency');
                    }
                    if (localStorage.getItem('sodate')) {
                        localStorage.removeItem('sodate');
                    }
                    if (localStorage.getItem('sostatus')) {
                        localStorage.removeItem('sostatus');
                    }
                    if (localStorage.getItem('sobiller')) {
                        localStorage.removeItem('sobiller');
                    }

                    $('#modal-loading').show();
                    location.reload();
                }
            });
    });

// save and load the fields in and/or from localStorage

    $('#soref').change(function (e) {
        localStorage.setItem('soref', $(this).val());
    });
    if (soref = localStorage.getItem('soref')) {
        $('#soref').val(soref);
    }
    $('#sowarehouse').change(function (e) {
        localStorage.setItem('sowarehouse', $(this).val());
    });
    if (sowarehouse = localStorage.getItem('sowarehouse')) {
        $('#sowarehouse').select2("val", sowarehouse);
    }

    $('#sonote').redactor('destroy');
    $('#sonote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('sonote', v);
        }
    });
    if (sonote = localStorage.getItem('sonote')) {
        $('#sonote').redactor('set', sonote);
    }
    var $customer = $('#socustomer');
    $customer.change(function (e) {
        localStorage.setItem('socustomer', $(this).val());
    });
    if (socustomer = localStorage.getItem('socustomer')) {
        $customer.val(socustomer).select2({
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


// prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        /*if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }*/
    });

// Order tax calculation
if (site.settings.tax2 != 0) {
    $('#sotax2').change(function () {
        localStorage.setItem('sotax2', $(this).val());
        loadItems();
        return;
    });
}

// Order discount calculation
var old_qudiscount;
$('#sodiscount').focus(function () {
    old_qudiscount = $(this).val();
}).change(function () {
    var new_discount = $(this).val() ? $(this).val() : '0';
    if (is_valid_discount(new_discount)) {
        localStorage.removeItem('sodiscount');
        localStorage.setItem('sodiscount', new_discount);
        loadItems();
        return;
    } else {
        $(this).val(old_qudiscount);
        bootbox.alert(lang.unexpected_value);
        return;
    }

});

/* ----------------------
 * Delete Row Method
 * ---------------------- */
$(document).on('click', '.qudel', function () {
    var row = $(this).closest('tr');
    var item_id = row.attr('data-item-id');
    delete soitems[item_id];
    row.remove();
    if(soitems.hasOwnProperty(item_id)) { } else {
        localStorage.setItem('soitems', JSON.stringify(soitems));
        loadItems();
        return;
    }
});

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = soitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_price = formatDecimalRaw(row.children().children('.ruprice').val()),
        discount = row.children().children('.rdiscount').val();
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price)+parseFloat(this.price);
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimalRaw(parseFloat(((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if(this.id == pr_tax){
                        if (this.type == 1) {

                            if (soitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimalRaw((((net_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                                net_price -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimalRaw((((net_price) * parseFloat(this.rate)) / 100), 4);
                                pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            }

                        } else if (this.type == 2) {

                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;

                        }
                    }
                });
            }
        }
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
        } else {
            product_variant = 0;
        }

        uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if(this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });
		
		if(site.settings.product_formulation != 0){
            if(item.product_formulations){
                opt_formulation = $("<select id=\"opt_formulation\" name=\"opt_formulation\" class=\"form-control select\" multiple/>");
                var formu = row.children().children('.r_product_formulation').val();
                var myarray = formu.split(",");
                $.each(item.product_formulations, function () {
                    if(jQuery.inArray(this.for_product_id, myarray) !== -1){
                        $("<option />", { value: this.for_product_id, text: this.name, selected:true}).appendTo(opt_formulation);
                    }else{
                        $("<option />", { value: this.for_product_id, text: this.name}).appendTo(opt_formulation);
                    }
                });
                
            }else{
                opt_formulation = '<p style="margin: 12px 0 0 0;">n/a</p>';
            }
            $('#pformulation-div').html(opt_formulation);
        }
		
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimalRaw(parseFloat(unit_price)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price-item_discount));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo("body").modal('show');

    });
	
	$(document).on('click', '.comment', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = soitems[item_id];
		
        $('#irow_id').val(row_id);
        $('#icomment').val(item.row.comment);
        $('#iordered').val(item.row.ordered);
        $('#iordered').select2('val', item.row.ordered);
        $('#cmModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#cmModal').appendTo("body").modal('show');
    });

    $(document).on('click', '#editComment', function () {
        var row = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        soitems[item_id].row.order = parseFloat($('#iorders').val()),
        soitems[item_id].row.comment = $('#icomment').val() ? $('#icomment').val() : '',
        localStorage.setItem('soitems', JSON.stringify(soitems));
        $('#cmModal').modal('hide');
        loadItems();
        return;
    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pprice, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = soitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimalRaw(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#net_price').text(formatMoney(unit_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

	
    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = soitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(), unit = $('#punit').val(),  aprice = 0;
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
		
		if(unit != soitems[item_id].row.base_unit) {
            $.each(item.units, function(){
                if (this.id == unit) {
					if(this.unit_price != null && this.unit_price > 0){
						var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
						$('#pprice').val((this.unit_price - (this.unit_price * ppercent)) + aprice *  (unitToBaseQty(1, this))).change();
					}else{
						$('#pprice').val((item.row.real_unit_price+aprice) * unitToBaseQty(1, this)).change();
					}
				}
            });
        } else {
            $('#pprice').val(formatDecimalRaw(item.row.real_unit_price) + aprice).change();
        }
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
     $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = false;
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());
        if(item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price-parseFloat(this.price);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if(!is_valid_discount($('#pdiscount').val()) || $('#pdiscount').val() > price) {
                bootbox.alert(lang.unexpected_value);
                return false;
            }
        }
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if(unit != soitems[item_id].row.base_unit) {
            $.each(soitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
		
		soitems[item_id].row.product_formulation = $('#opt_formulation').val();
        soitems[item_id].row.fup = 1,
        soitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        soitems[item_id].row.base_quantity = parseFloat(base_quantity),
		soitems[item_id].row.unit_price = price,
        soitems[item_id].row.unit = unit,
        soitems[item_id].row.tax_rate = new_pr_tax,
        soitems[item_id].tax_rate = new_pr_tax_rate,
        soitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        soitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
        soitems[item_id].row.serial = $('#pserial').val();
        localStorage.setItem('soitems', JSON.stringify(soitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });

    /* -----------------------
     * Product option change
     ----------------------- */
     $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = soitems[item_id];
        var unit = $('#punit').val(), real_unit_price = item.row.real_unit_price;
        if(unit != soitems[item_id].row.base_unit) {
            $.each(soitems[item_id].units, function(){
                if (this.id == unit) {
                    real_unit_price = formatDecimalRaw((parseFloat(item.row.real_unit_price)*(unitToBaseQty(1, this))), 4)
                }
            });
        }
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(real_unit_price)+(parseFloat(this.price))).trigger('change');
                }
            });
        }
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            soitems = {};
            if ($('#sowarehouse').val() && $('#socustomer').val()) {
                $('#socustomer').select2("readonly", true);
                $('#sowarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo("body").modal('show');
        return false;
    });
	
	
	$(document).on("focus", '.swidth, .sheight', function () {
		old_value = $(this).val();
	}).on("change", '.swidth, .sheight', function () {
		if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
			$(this).val(old_value);
			bootbox.alert(lang.unexpected_value);
			return;
		}
		var parent = $(this).parent().parent();
		var row = $(this).closest('tr');
		var swidth = parent.find(".swidth").val();
		var sheight = parent.find(".sheight").val();
		var square_qty = parent.find(".square_qty").val();
		var square = parseFloat(swidth) * parseFloat(sheight);
		var quantity = square * square_qty;		
		
		item_id = row.attr('data-item-id');
		soitems[item_id].row.swidth = swidth;
		soitems[item_id].row.sheight = sheight;
		soitems[item_id].row.square = square;
		soitems[item_id].row.square_qty = square_qty;
		soitems[item_id].row.qty = quantity;
		if(soitems[item_id].row.unit != soitems[item_id].row.base_unit) {
		$.each(soitems[item_id].units, function(){
				if (this.id == soitems[item_id].row.unit) {
					soitems[item_id].row.base_quantity = unitToBaseQty(quantity, this);
				}
			});
		}else{
			soitems[item_id].row.base_quantity = quantity;
		}
		localStorage.setItem('soitems', JSON.stringify(soitems));
		loadItems();
	});


	$(document).on("focus", '.square, .square_qty', function () {
		old_value = $(this).val();
	}).on("change", '.square, .square_qty', function () {
		if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
			$(this).val(old_value);
			bootbox.alert(lang.unexpected_value);
			return;
		}
		var parent = $(this).parent().parent();
		var row = $(this).closest('tr');
		var square = parent.find(".square").val();
		var square_qty = parent.find(".square_qty").val();
		var quantity = square * square_qty;		

		item_id = row.attr('data-item-id');
		soitems[item_id].row.square = square;
		soitems[item_id].row.square_qty = square_qty;
		soitems[item_id].row.qty = quantity;
		if(soitems[item_id].row.unit != soitems[item_id].row.base_unit) {
		$.each(soitems[item_id].units, function(){
				if (this.id == soitems[item_id].row.unit) {
					soitems[item_id].row.base_quantity = unitToBaseQty(quantity, this);
				}
			});
		}else{
			soitems[item_id].row.base_quantity = quantity;
		}
		localStorage.setItem('soitems', JSON.stringify(soitems));
		loadItems();
	});
	

	
    $(document).on('click', '#addItemManually', function (e) {
        var mid = (new Date).getTime(),
        mcode = $('#mcode').val(),
        mname = $('#mname').val(),
        mtax = parseInt($('#mtax').val()),
        mqty = parseFloat($('#mquantity').val()),
        mdiscount = $('#mdiscount').val() ? $('#mdiscount').val() : '0',
        unit_price = parseFloat($('#mprice').val()),
        mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function () {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            soitems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname, "quantity": mqty, "price": unit_price, "unit_price": unit_price, "real_unit_price": unit_price, "tax_rate": mtax, "tax_method": 0, "qty": mqty, "type": "manual", "discount": mdiscount, "serial": "", "option":""}, "tax_rate": mtax_rate, "options":false};
            localStorage.setItem('soitems', JSON.stringify(soitems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(), item_tax_method = 0;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimalRaw(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)));
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimalRaw(((unit_price) * parseFloat(this.rate)) / 100);
                            pr_tax_rate = formatDecimalRaw(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
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
        soitems[item_id].row.base_quantity = new_qty;
        if(soitems[item_id].row.unit != soitems[item_id].row.base_unit) {
            $.each(soitems[item_id].units, function(){
                if (this.id == soitems[item_id].row.unit) {
                    soitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
		
        soitems[item_id].row.qty = new_qty;
        localStorage.setItem('soitems', JSON.stringify(soitems));
        loadItems();
    });
	
	
	
	$(document).on('change', '.sunit', function () {
        var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		var item = soitems[item_id];
		var qty = item.row.qty;
		var new_unit = parseFloat($(this).val());
		var base_quantity = qty;
		if(new_unit != item.row.base_unit) {
            $.each(item.units, function(){
                if (this.id == new_unit) {
                    base_quantity = unitToBaseQty(qty, this);
					if(this.unit_price != null && this.unit_price > 0){
						var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
						unit_price = this.unit_price - (this.unit_price * ppercent);
					}else{
						unit_price = item.row.real_unit_price * (unitToBaseQty(1, this));
					}
					
				}
            });
        }else{
			unit_price = item.row.real_unit_price;
		}
		soitems[item_id].row.base_quantity = base_quantity;
		soitems[item_id].row.unit_price = unit_price;
		soitems[item_id].row.unit = new_unit;
		localStorage.setItem('soitems', JSON.stringify(soitems));
        loadItems();
    });
	
	
	
	var old_foc;
    $(document).on("focus", '.foc', function () {
        old_foc = $(this).val();
    }).on("change", '.foc', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_foc);
            bootbox.alert(lang.unexpected_value);
            return;
        }
		var new_foc = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        soitems[item_id].row.foc = new_foc;
        localStorage.setItem('soitems', JSON.stringify(soitems));
        loadItems();
    });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */
    var old_price;
    $(document).on("focus", '.rprice', function () {
        old_price = $(this).val();
    }).on("change", '.rprice', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_price);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_price = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
        soitems[item_id].row.price = new_price;
        localStorage.setItem('soitems', JSON.stringify(soitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
       $('#socustomer').select2('readonly', false);
       //$('#sowarehouse').select2('readonly', false);
       return false;
    });


});
/* -----------------------
 * Misc Actions
 ----------------------- */

// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#socustomer').select2({
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

function nsSupplier() {
    $('#sosupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "suppliers/suggestions",
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

//localStorage.clear();
function loadItems() {

    if (localStorage.getItem('soitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
		total_foc = 0;

        $("#quTable tbody").empty();
        soitems = JSON.parse(localStorage.getItem('soitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(soitems, function(o){return [parseInt(o.order)];}) :   soitems;
        $('#add_sale, #edit_sale, #add_sale_order_next').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
				item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var unit_price = item.row.unit_price;
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
			var item_comment = item.row.comment ? item.row.comment : '';

			
			if(item.row.fup != 1 && product_unit != item.row.base_unit) {
				$.each(item.units, function(){
					if (this.id == product_unit) {
						base_quantity = unitToBaseQty(item.row.qty, this);
						if(this.unit_price != null && this.unit_price > 0){
							var ppercent = (item.row.base_unit_price - item.row.real_unit_price) / item.row.base_unit_price;
							unit_price = this.unit_price - (this.unit_price * ppercent);
						}else{
							unit_price = item.row.real_unit_price * (unitToBaseQty(1, this));
						}
					}
				});
			}
			
            if(item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                        item_price = unit_price+(parseFloat(this.price));
                        unit_price = item_price;
                    }
                });
            }

            var ds = item_ds ? item_ds : '0';
			
			
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimalRaw((((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = formatDecimalRaw(ds);
                }
            } else {
                 item_discount = formatDecimalRaw(ds);
            }
			if(item_discount>0){
				var item_discount_percent = '('+formatDecimalRaw((item_discount * 100)/unit_price)+'%)';
			}else{
				var item_discount_percent = '';
			}
		
            product_discount += parseFloat(item_discount * item_qty);

            unit_price = formatDecimalRaw(unit_price-item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {

                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))), 4);
                            pr_tax_rate = formatDecimalRaw(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(pr_tax.rate)) / 100), 4);
                            pr_tax_rate = formatDecimalRaw(pr_tax.rate) + '%';
                        }

                    } else if (pr_tax.type == 2) {

                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;

                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            item_price = item_tax_method == 0 ? formatDecimalRaw(unit_price-pr_tax_val, 4) : formatDecimalRaw(unit_price);
            unit_price = formatDecimalRaw(unit_price+item_discount, 4);
            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
			var row_no = item_id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_comment[]" type="hidden" class="rcomment" value="' + item_comment + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i><i class="pull-right fa fa-comment'+(item_comment != '' ? '' :'-o')+' tip pointer comment" id="' + row_no + '" data-item="' + item_id + '" title="Comment" style="cursor:pointer;margin-right:5px;"></i></td>';
            
			if(site.settings.show_qoh == 1){
				var qoh = item_aqty;
				tr_html += '<td class="text-center"><span>'+(item_type=='standard' ? formatDecimal(qoh) : '')+'</span></td>'
			}
			
			tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + formatDecimalRaw(item_price) + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
			tr_html += '<input type="hidden" class="currency_rate" name="currency_rate[]"  value="' + item.row.currency_rate + '"/>';
			tr_html += '<input type="hidden" class="currency_code" name="currency_code[]"  value="' + item.row.currency_code + '"/>';
			var squantity = '';
			if (site.settings.qty_operation == 1) {
				var squantity = 'readonly';
				tr_html += '<td>';										
					tr_html += '<input type="text" value="'+(item.row.swidth?parseFloat(item.row.swidth):1)+'" class="form-control swidth text-right" name="swidth[]" />';
				tr_html += '</td>';
				tr_html += '<td>';										
					tr_html += '<input type="text" value="'+(item.row.sheight?parseFloat(item.row.sheight):1)+'" class="form-control sheight text-right" name="sheight[]" />';
				tr_html += '</td>';
				tr_html += '<td>';										
					tr_html += '<input type="text" value="'+(item.row.square?parseFloat(item.row.square):1)+'" class="form-control square text-right" name="square[]" />';
				tr_html += '</td>';
				tr_html += '<td>';				
					if (site.settings.product_formulation == 1) {
						if(item.row.product_formulation == undefined){
                            item.row.product_formulation = "";
                        }
						tr_html += '<input value="'+item.row.product_formulation+'" class="r_product_formulation" type="hidden" name="product_formulation[]"/><input type="text" value="'+(item.row.square_qty?parseFloat(item.row.square_qty):1)+'" class="form-control square_qty text-right" name="square_qty[]" />';
					}else{
						tr_html += '<input type="text" value="'+(item.row.square_qty?parseFloat(item.row.square_qty):1)+'" class="form-control square_qty text-right" name="square_qty[]" />';
					}
				tr_html += '</td>';
			}
			tr_html += '<td><input ' + squantity + ' class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimalRaw(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            
			
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
			if (site.settings.foc == 1) {
				tr_html += '<td class="text-center"><input name="foc[]" class="form-control text-center foc" value="'+(item.row.foc > 0 ? item.row.foc : 0)+'"/></td>';
				if(item.row.foc > 0){
					total_foc += parseFloat(item.row.foc);
				}
				
			}
			if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + item_discount_percent+'</span></td>';
            }
            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer qudel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            tr_html += '<input type="hidden" name="parent_id[]" value="'+item.row.parent_id+'" />';
			newTr.html(tr_html);
            newTr.prependTo("#quTable");
			$('select').select2();
			
			var currency_rate = (item.row.currency_rate?item.row.currency_rate:1);
            total += formatDecimalRaw(((parseFloat(item_price / currency_rate) + parseFloat(pr_tax_val / currency_rate)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;
            if (item_type == 'standard' && item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                        if(site.settings.overselling != 1) { $('#add_sale, #edit_sale, #add_sale_order_next').attr('disabled', true); }
                    }
                });
            } else if(item_type == 'standard' && base_quantity > item_aqty) {
                $('#row_' + row_no).addClass('danger');
            } else if (item_type == 'combo') {
                if(combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                } else {
                    $.each(combo_items, function() {
                       if(parseFloat(this.quantity) < (parseFloat(this.qty)*base_quantity) && this.type == 'standard') {
                           $('#row_' + row_no).addClass('danger');
                       }
                   });
                }
            }

        });

        var col = 2;
		if (site.settings.qty_operation == 1) { col += 4 }
		if (site.settings.show_qoh == 1) { col += 1 }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (site.settings.show_unit == 1) { 
			tfoot += '<th></th>';	
		}
		if (site.settings.foc == 1) { 
			tfoot += '<th class="text-right">'+formatNumber(total_foc)+'</th>';	
		}
		if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
            tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_tax)+'</th>';
        }
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#quTable tfoot').html(tfoot);

        // Order level discount calculations
        if (sodiscount = localStorage.getItem('sodiscount')) {
            var ds = sodiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimalRaw((((total) * parseFloat(pds[0])) / 100), 4);
                } else {
                    order_discount = formatDecimalRaw(ds);
                }
            } else {
                order_discount = formatDecimalRaw(ds);
            }
        }

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (sotax2 = localStorage.getItem('sotax2')) {
                $.each(tax_rates, function () {
                    if (this.id == sotax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimalRaw(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimalRaw((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = parseFloat(((total + invoice_tax) - order_discount) + shipping);
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#tship').text(formatMoney(shipping));
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Quotation Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_invoice_item(item) {

    if (count == 1) {
        soitems = {};
        if ($('#sowarehouse').val() && $('#socustomer').val()) {
            $('#socustomer').select2("readonly", true);
            $('#sowarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (soitems[item_id]) {

        var new_qty = parseFloat(soitems[item_id].row.qty) + 1;
        soitems[item_id].row.base_quantity = new_qty;
        if(soitems[item_id].row.unit != soitems[item_id].row.base_unit) {
            $.each(soitems[item_id].units, function(){
                if (this.id == soitems[item_id].row.unit) {
                    soitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        soitems[item_id].row.qty = new_qty;

    } else {
        soitems[item_id] = item;
    }
    soitems[item_id].order = new Date().getTime();
    localStorage.setItem('soitems', JSON.stringify(soitems));
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
