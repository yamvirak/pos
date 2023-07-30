$(document).ready(function () {
$('body a, body button').attr('tabindex', -1);
check_add_item_val();
if (site.settings.set_focus != 1) {
    $('#add_item').focus();
}
// Order level shipping and discoutn localStorage
    if (pordiscount = localStorage.getItem('pordiscount')) {
        $('#pordiscount').val(pordiscount);
    }
    $('#portax2').change(function (e) {
        localStorage.setItem('portax2', $(this).val());
        $('#portax2').val($(this).val());
    });
    if (portax2 = localStorage.getItem('portax2')) {
        $('#portax2').select2("val", portax2);
    }
    $('#porstatus').change(function (e) {
        localStorage.setItem('porstatus', $(this).val());
    });
    if (porstatus = localStorage.getItem('porstatus')) {
        $('#porstatus').select2("val", porstatus);
    }
    var old_shipping;
    $('#porshipping').focus(function () {
        old_shipping = $(this).val();
    }).change(function () {
        if (!is_numeric($(this).val())) {
            $(this).val(old_shipping);
            bootbox.alert(lang.unexpected_value);
            return;
        } else {
            shipping = $(this).val() ? parseFloat($(this).val()) : '0';
        }
        localStorage.setItem('porshipping', shipping);
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#gtotal').text(formatMoney(gtotal));
        $('#tship').text(formatMoney(shipping));
    });
    if (porshipping = localStorage.getItem('porshipping')) {
        shipping = parseFloat(porshipping);
        $('#porshipping').val(shipping);
    } else {
        shipping = 0;
    }

    $('#porsupplier').change(function (e) {
        localStorage.setItem('porsupplier', $(this).val());
        $('#supplier_id').val($(this).val());
		$('#add_purchase_order, #add_purchase_order_next').attr('disabled', false);
    });
    if ((porsupplier = localStorage.getItem('porsupplier')) && porsupplier != 0) {
        $('#porsupplier').val(porsupplier).select2({
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
		$('#add_purchase_order, #add_purchase_order_next').attr('disabled', false);
    } else {
		$('#add_purchase_order, #add_purchase_order_next').attr('disabled', true);
        nsSupplier();
    }

    // If there is any item in localStorage
    if (localStorage.getItem('poritems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('poritems')) {
                        localStorage.removeItem('poritems');
                    }
                    if (localStorage.getItem('pordiscount')) {
                        localStorage.removeItem('pordiscount');
                    }
                    if (localStorage.getItem('portax2')) {
                        localStorage.removeItem('portax2');
                    }
                    if (localStorage.getItem('porshipping')) {
                        localStorage.removeItem('porshipping');
                    }
                    if (localStorage.getItem('porref')) {
                        localStorage.removeItem('porref');
                    }
                    if (localStorage.getItem('porwarehouse')) {
                        localStorage.removeItem('porwarehouse');
                    }
                    if (localStorage.getItem('pornote')) {
                        localStorage.removeItem('pornote');
                    }
                    if (localStorage.getItem('quinnote')) {
                        localStorage.removeItem('quinnote');
                    }
                    if (localStorage.getItem('qucurrency')) {
                        localStorage.removeItem('qucurrency');
                    }
                    if (localStorage.getItem('pordate')) {
                        localStorage.removeItem('pordate');
                    }
                    if (localStorage.getItem('porstatus')) {
                        localStorage.removeItem('porstatus');
                    }
                    if (localStorage.getItem('porbiller')) {
                        localStorage.removeItem('porbiller');
                    }

                    $('#modal-loading').show();
                    location.reload();
                }
            });
    });

// save and load the fields in and/or from localStorage

    $('#porref').change(function (e) {
        localStorage.setItem('porref', $(this).val());
    });
    if (porref = localStorage.getItem('porref')) {
        $('#porref').val(porref);
    }
    $('#porwarehouse').change(function (e) {
        localStorage.setItem('porwarehouse', $(this).val());
    });
    if (porwarehouse = localStorage.getItem('porwarehouse')) {
        $('#porwarehouse').select2("val", porwarehouse);
    }

    $('#pornote').redactor('destroy');
    $('#pornote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('pornote', v);
        }
    });
    if (pornote = localStorage.getItem('pornote')) {
        $('#pornote').redactor('set', pornote);
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

// Order tax calculation
if (site.settings.tax2 != 0) {
    $('#portax2').change(function () {
        localStorage.setItem('portax2', $(this).val());
        loadItems();
        return;
    });
}

// Order discount calculation
var old_qudiscount;
$('#pordiscount').focus(function () {
    old_qudiscount = $(this).val();
}).change(function () {
    var new_discount = $(this).val() ? $(this).val() : '0';
    if (is_valid_discount(new_discount)) {
        localStorage.removeItem('pordiscount');
        localStorage.setItem('pordiscount', new_discount);
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
    delete poritems[item_id];
    row.remove();
    if(poritems.hasOwnProperty(item_id)) { } else {
        localStorage.setItem('poritems', JSON.stringify(poritems));
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
        item = poritems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_price = formatDecimalRaw(row.children().children('.ruprice').val()),
		pnote = row.children().children('.pnote').val(),
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

                            if (poritems[item_id].row.tax_method == 0) {
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
		$('#pnote').val(pnote);
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
        var item = poritems[item_id];
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
        var item = poritems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(), unit = $('#punit').val(), base_quantity = $('#pquantity').val(), aprice = 0;
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
        if(unit != poritems[item_id].row.base_unit) {
            $.each(item.units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                    $('#pprice').val(formatDecimalRaw(((parseFloat(item.row.base_unit_price+aprice))*unitToBaseQty(1, this)), 4)).change();
                }
            });
        } else {
            $('#pprice').val(formatDecimalRaw(item.row.base_unit_price+aprice)).change();
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
		var real_unit_price = price;
        if(unit != poritems[item_id].row.base_unit) {
            $.each(poritems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
					real_unit_price = unitToBasePrice(price, this);
				}
            });
        }
		var pnote = $("#pnote").val();
		poritems[item_id].row.pnote = (pnote?pnote:"");

        poritems[item_id].row.fup = 1,
        poritems[item_id].row.qty = parseFloat($('#pquantity').val()),
        poritems[item_id].row.base_quantity = parseFloat(base_quantity),
        poritems[item_id].row.real_unit_price = real_unit_price,
		poritems[item_id].row.unit_price = price,
        poritems[item_id].row.unit = unit,
        poritems[item_id].row.tax_rate = new_pr_tax,
        poritems[item_id].tax_rate = new_pr_tax_rate,
        poritems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        poritems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
        poritems[item_id].row.serial = $('#pserial').val();
        localStorage.setItem('poritems', JSON.stringify(poritems));
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
        var item = poritems[item_id];
        var unit = $('#punit').val(), base_quantity = parseFloat($('#pquantity').val()), base_unit_price = item.row.base_unit_price;
        if(unit != poritems[item_id].row.base_unit) {
            $.each(poritems[item_id].units, function(){
                if (this.id == unit) {
                    base_unit_price = formatDecimalRaw((parseFloat(item.row.base_unit_price)*(unitToBaseQty(1, this))), 4)
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(base_unit_price)+(parseFloat(this.price))).trigger('change');
                }
            });
        }
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            poritems = {};
            if ($('#porwarehouse').val()) {
                $('#porwarehouse').select2("readonly", true);
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

            poritems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname, "quantity": mqty, "price": unit_price, "unit_price": unit_price, "real_unit_price": unit_price, "tax_rate": mtax, "tax_method": 0, "qty": mqty, "type": "manual", "discount": mdiscount, "serial": "", "option":""}, "tax_rate": mtax_rate, "options":false};
            localStorage.setItem('poritems', JSON.stringify(poritems));
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
        poritems[item_id].row.base_quantity = new_qty;
        if(poritems[item_id].row.unit != poritems[item_id].row.base_unit) {
            $.each(poritems[item_id].units, function(){
                if (this.id == poritems[item_id].row.unit) {
                    poritems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        poritems[item_id].row.qty = new_qty;
        localStorage.setItem('poritems', JSON.stringify(poritems));
        loadItems();
    });
	
	$(document).on('change', '.sunit', function () {
        var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		var item = poritems[item_id];
		var qty = item.row.qty;
		var new_unit = parseFloat($(this).val());
		var base_quantity = qty;
		if(new_unit != item.row.base_unit) {
            $.each(item.units, function(){
                if (this.id == new_unit) {
					base_quantity = formatDecimalRaw(unitToBaseQty(qty, this), 4);
                    unit_price = formatDecimalRaw((parseFloat(item.row.base_unit_price)*(unitToBaseQty(1, this))), 4);					
				}
            });
        }else{
			unit_price = item.row.real_unit_price;
		}
		poritems[item_id].row.base_quantity = base_quantity;
		poritems[item_id].row.unit_price = unit_price;
		poritems[item_id].row.unit = new_unit;
		localStorage.setItem('poritems', JSON.stringify(poritems));
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
        poritems[item_id].row.price = new_price;
        localStorage.setItem('poritems', JSON.stringify(poritems));
        loadItems();
    });



});
/* -----------------------
 * Misc Actions
 ----------------------- */


function nsSupplier() {
    $('#porsupplier').select2({
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

    if (localStorage.getItem('poritems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
		grand_cbm = 0;

        $("#quTable tbody").empty();
        poritems = JSON.parse(localStorage.getItem('poritems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(poritems, function(o){return [parseInt(o.order)];}) :   poritems;
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var unit_price = item.row.unit_price;
			var pnote = (item.row.pnote?item.row.pnote:"");
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            if(item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function(){
                    if (this.id == product_unit) {
                        base_quantity = formatDecimalRaw(unitToBaseQty(item.row.qty, this), 4);
                        unit_price = formatDecimalRaw((parseFloat(item.row.base_unit_price)*(unitToBaseQty(1, this))), 4);
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
            var row_no = item_id;//(new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + formatDecimalRaw(item_price) + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="pnote" name="pnote[]" type="hidden" value="' + pnote + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + formatDecimalRaw(item.row.real_unit_price) + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
            tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimalRaw(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
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
			if(site.settings.cbm == 1){
				var total_cbm = 0;
				if(item.row.p_length > 0 && item.row.p_width > 0 && item.row.p_height > 0){
					var p_length = item.row.p_length; 
					var p_width = item.row.p_width; 
					var p_height = item.row.p_height ; 
					var p_unit_qty = item.row.p_unit_qty;
					$.each(item.units, function(){
						if (this.id == product_unit) {
							var unit_qty = unitToBaseQty(1, this);
							p_unit_qty = p_unit_qty / unit_qty;
						}
					});
					var cbm = (p_length * p_width * p_height) / 1000000;
					total_cbm = (cbm * item_qty) / p_unit_qty;
					grand_cbm += total_cbm;
				}
				tr_html += '<td class="text-right"><input name="total_cbm[]" type="hidden" value="'+total_cbm+'"/>' + formatNumber(total_cbm) + '</td>';
			}
			
			if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>';
            }
            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer qudel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#quTable");
			
			$('select').select2();
			
            total += formatDecimalRaw(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;
        });

        var col = 2;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (site.settings.show_unit == 1) { 
			tfoot += '<th></th>';	
		}
		if(site.settings.cbm == 1){
			tfoot += '<th class="text-right">'+formatNumber(grand_cbm)+'</th>';
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
        if (pordiscount = localStorage.getItem('pordiscount')) {
            var ds = pordiscount;
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
            if (portax2 = localStorage.getItem('portax2')) {
                $.each(tax_rates, function () {
                    if (this.id == portax2) {
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
        poritems = {};
        if ($('#porwarehouse').val()) {
            $('#porwarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (poritems[item_id]) {

        var new_qty = parseFloat(poritems[item_id].row.qty) + 1;
        poritems[item_id].row.base_quantity = new_qty;
        if(poritems[item_id].row.unit != poritems[item_id].row.base_unit) {
            $.each(poritems[item_id].units, function(){
                if (this.id == poritems[item_id].row.unit) {
                    poritems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        poritems[item_id].row.qty = new_qty;

    } else {
        poritems[item_id] = item;
    }
    poritems[item_id].order = new Date().getTime();
    localStorage.setItem('poritems', JSON.stringify(poritems));
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
