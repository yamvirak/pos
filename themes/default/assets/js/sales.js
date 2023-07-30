$(document).ready(function (e) {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    var $customer = $('#slcustomer');
    $customer.change(function (e) {
        localStorage.setItem('slcustomer', $(this).val());
    });
    
    if (saleman_id = localStorage.getItem('slsaleman')) {
        $("#saleman_id").select2("val",saleman_id); 
    }
    
    if (slcommission = localStorage.getItem('slcommission')) {
        $("#commission").val(slcommission); 
    }

    if (slcustomer = localStorage.getItem('slcustomer')) {
        $customer.val(slcustomer).select2({
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

// Order level shipping and discount localStorage
if (sldiscount = localStorage.getItem('sldiscount')) {
    $('#sldiscount').val(sldiscount);
}
$('#sltax2').change(function (e) {
    localStorage.setItem('sltax2', $(this).val());
    $('#sltax2').val($(this).val());
});
if (sltax2 = localStorage.getItem('sltax2')) {
    $('#sltax2').select2("val", sltax2);
}
$('#slsale_status').change(function (e) {
    localStorage.setItem('slsale_status', $(this).val());
});
if (slsale_status = localStorage.getItem('slsale_status')) {
    $('#slsale_status').select2("val", slsale_status);
}
$('#slpayment_status').change(function (e) {
    var ps = $(this).val();
    localStorage.setItem('slpayment_status', ps);
    if (ps == 'partial' || ps == 'paid') {
        if(ps == 'paid') {
            $('#amount_1').val(formatDecimalRaw(parseFloat(((total + invoice_tax) - order_discount) + shipping)));
        }
        $('#payments').slideDown();
        $('#pcc_no_1').focus();
    } else {
        $('#payments').slideUp();
    }
    if($('#slpayment_term').val() > 0){
        $('#slpayment_term').change();
    }
    
});

$("#slpayment_term").change(function(){
    var pt = $(this).val();
    var grand_total = parseFloat($("#gtotal").text().replace('$',''));
    $.ajax({
        url: site.base_url + "sales/payment_term",
        type : "GET",
        dataType : "JSON",
        data : { term_id : pt },
        success : function(data){           
            var discount = 0;
            if(data.discount_type == "Percentage"){
                discount = (data.discount * grand_total) / 100;
            }else{
                discount = data.discount;
            }           
            $("#amount_1").val(grand_total- discount);
            $("#payment_discount").val(discount);
        }
    })
    
});


if (slpayment_status = localStorage.getItem('slpayment_status')) {
    $('#slpayment_status').select2("val", slpayment_status);
    var ps = slpayment_status;
    if (ps == 'partial' || ps == 'paid') {
        $('#payments').slideDown();
        $('#pcc_no_1').focus();
    } else {
        $('#payments').slideUp();
    }
}

$(document).on('change', '.paid_by', function () {
    var p_val = $(this).val();
    localStorage.setItem('paid_by', p_val);
    $('#rpaidby').val(p_val);
    if (p_val == 'cash' ||  p_val == 'other') {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').show();
        $('#payment_note_1').focus();
    } else if (p_val == 'CC') {
        $('.pcheque_1').hide();
        $('.pcash_1').hide();
        $('.pcc_1').show();
        $('#pcc_no_1').focus();
    } else if (p_val == 'Cheque') {
        $('.pcc_1').hide();
        $('.pcash_1').hide();
        $('.pcheque_1').show();
        $('#cheque_no_1').focus();
    } else {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').hide();
    }
    if (p_val == 'gift_card') {
        $('.gc').show();
        $('.ngc').hide();
        $('#gift_card_no').focus();
    } else {
        $('.ngc').show();
        $('.gc').hide();
        $('#gc_details').html('');
    }
});

if (paid_by = localStorage.getItem('paid_by')) {
    var p_val = paid_by;
    $('.paid_by').select2("val", paid_by);
    $('#rpaidby').val(p_val);
    if (p_val == 'cash' ||  p_val == 'other') {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').show();
        $('#payment_note_1').focus();
    } else if (p_val == 'CC') {
        $('.pcheque_1').hide();
        $('.pcash_1').hide();
        $('.pcc_1').show();
        $('#pcc_no_1').focus();
    } else if (p_val == 'Cheque') {
        $('.pcc_1').hide();
        $('.pcash_1').hide();
        $('.pcheque_1').show();
        $('#cheque_no_1').focus();
    } else {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').hide();
    }
    if (p_val == 'gift_card') {
        $('.gc').show();
        $('.ngc').hide();
        $('#gift_card_no').focus();
    } else {
        $('.ngc').show();
        $('.gc').hide();
        $('#gc_details').html('');
    }
}

if (gift_card_no = localStorage.getItem('gift_card_no')) {
    $('#gift_card_no').val(gift_card_no);
}
$('#gift_card_no').change(function (e) {
    localStorage.setItem('gift_card_no', $(this).val());
});

if (amount_1 = localStorage.getItem('amount_1')) {
    $('#amount_1').val(amount_1);
}
$('#amount_1').change(function (e) {
    localStorage.setItem('amount_1', $(this).val());
});

if (paid_by_1 = localStorage.getItem('paid_by_1')) {
    $('#paid_by_1').val( paid_by_1);
}
$('#paid_by_1').change(function (e) {
    localStorage.setItem('paid_by_1', $(this).val());
});

if (pcc_holder_1 = localStorage.getItem('pcc_holder_1')) {
    $('#pcc_holder_1').val(pcc_holder_1);
}
$('#pcc_holder_1').change(function (e) {
    localStorage.setItem('pcc_holder_1', $(this).val());
});

if (pcc_type_1 = localStorage.getItem('pcc_type_1')) {
    $('#pcc_type_1').select2("val", pcc_type_1);
}
$('#pcc_type_1').change(function (e) {
    localStorage.setItem('pcc_type_1', $(this).val());
});

if (pcc_month_1 = localStorage.getItem('pcc_month_1')) {
    $('#pcc_month_1').val( pcc_month_1);
}
$('#pcc_month_1').change(function (e) {
    localStorage.setItem('pcc_month_1', $(this).val());
});

if (pcc_year_1 = localStorage.getItem('pcc_year_1')) {
    $('#pcc_year_1').val(pcc_year_1);
}
$('#pcc_year_1').change(function (e) {
    localStorage.setItem('pcc_year_1', $(this).val());
});

if (pcc_no_1 = localStorage.getItem('pcc_no_1')) {
    $('#pcc_no_1').val(pcc_no_1);
}
$('#pcc_no_1').change(function (e) {
    var pcc_no = $(this).val();
    localStorage.setItem('pcc_no_1', pcc_no);
    var CardType = null;
    var ccn1 = pcc_no.charAt(0);
    if(ccn1 == 4)
        CardType = 'Visa';
    else if(ccn1 == 5)
        CardType = 'MasterCard';
    else if(ccn1 == 3)
        CardType = 'Amex';
    else if(ccn1 == 6)
        CardType = 'Discover';
    else
        CardType = 'Visa';

    $('#pcc_type_1').select2("val", CardType);
});

if (cheque_no_1 = localStorage.getItem('cheque_no_1')) {
    $('#cheque_no_1').val(cheque_no_1);
}
$('#cheque_no_1').change(function (e) {
    localStorage.setItem('cheque_no_1', $(this).val());
});

if (payment_note_1 = localStorage.getItem('payment_note_1')) {
    $('#payment_note_1').redactor('set', payment_note_1);
}
$('#payment_note_1').redactor('destroy');
$('#payment_note_1').redactor({
    buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
    formattingTags: ['p', 'pre', 'h3', 'h4'],
    minHeight: 100,
    changeCallback: function (e) {
        var v = this.get();
        localStorage.setItem('payment_note_1', v);
    }
});

var old_payment_term;
$('#slpayment_term').focus(function () {
    old_payment_term = $(this).val();
}).change(function (e) {
    var new_payment_term = $(this).val() ? parseFloat($(this).val()) : 0;
    if (!is_numeric($(this).val())) {
        $(this).val(old_payment_term);
        bootbox.alert(lang.unexpected_value);
        return;
    } else {
        localStorage.setItem('slpayment_term', new_payment_term);
        $('#slpayment_term').val(new_payment_term);
    }
});
if (slpayment_term = localStorage.getItem('slpayment_term')) {
    $('#slpayment_term').val(slpayment_term);
}

var old_shipping;
$('#slshipping').focus(function () {
    old_shipping = $(this).val();
}).change(function () {
    if (!is_numeric($(this).val())) {
        $(this).val(old_shipping);
        bootbox.alert(lang.unexpected_value);
        return;
    } else {
        shipping = $(this).val() ? parseFloat($(this).val()) : '0';
    }
    localStorage.setItem('slshipping', shipping);
    var gtotal = ((total + invoice_tax) - order_discount) + shipping;
    $('#gtotal').text(formatMoney(gtotal));
    $('#g_total').val(gtotal);
    $('#tship').text(formatMoney(shipping));
});
if (slshipping = localStorage.getItem('slshipping')) {
    shipping = parseFloat(slshipping);
    $('#slshipping').val(shipping);
} else {
    shipping = 0;
}
$('#add_sale, #edit_sale, #add_sale_next').attr('disabled', true);
$(document).on('change', '.rserial', function () {
    var item_id = $(this).closest('tr').attr('data-item-id');
    slitems[item_id].row.serial = $(this).val();
    localStorage.setItem('slitems', JSON.stringify(slitems));
});

$(document).on("change", '.rexpired', function () {
    var new_expired = $(this).val();
    var item_id = $(this).closest('tr').attr('data-item-id');
    slitems[item_id].row.expired = new_expired;
    localStorage.setItem('slitems', JSON.stringify(slitems));
    loadItems();
});

$(document).on("change", '.rsalesman', function () {
    var new_saleman = $(this).val();
    var item_id = $(this).closest('tr').attr('data-item-id');
    slitems[item_id].row.salesman_id = new_saleman;
    localStorage.setItem('slitems', JSON.stringify(slitems));
    loadItems();
});


// If there is any item in localStorage
if (localStorage.getItem('slitems')) {
    loadItems();
}

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('slitems')) {
                    localStorage.removeItem('slitems');
                }
                if (localStorage.getItem('sldiscount')) {
                    localStorage.removeItem('sldiscount');
                }
                if (localStorage.getItem('sltax2')) {
                    localStorage.removeItem('sltax2');
                }
                if (localStorage.getItem('slshipping')) {
                    localStorage.removeItem('slshipping');
                }
                if (localStorage.getItem('slref')) {
                    localStorage.removeItem('slref');
                }
                if (localStorage.getItem('slwarehouse')) {
                    localStorage.removeItem('slwarehouse');
                }
                if (localStorage.getItem('slnote')) {
                    localStorage.removeItem('slnote');
                }
                if (localStorage.getItem('slinnote')) {
                    localStorage.removeItem('slinnote');
                }
                if (localStorage.getItem('slcustomer')) {
                    localStorage.removeItem('slcustomer');
                }
                if (localStorage.getItem('slcurrency')) {
                    localStorage.removeItem('slcurrency');
                }
                if (localStorage.getItem('sldate')) {
                    localStorage.removeItem('sldate');
                }
                if (localStorage.getItem('slstatus')) {
                    localStorage.removeItem('slstatus');
                }
                if (localStorage.getItem('slbiller')) {
                    localStorage.removeItem('slbiller');
                }
                if (localStorage.getItem('gift_card_no')) {
                    localStorage.removeItem('gift_card_no');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
});

// save and load the fields in and/or from localStorage

$('#slref').change(function (e) {
    localStorage.setItem('slref', $(this).val());
});
if (slref = localStorage.getItem('slref')) {
    $('#slref').val(slref);
}

$('#slwarehouse').change(function (e) {
    localStorage.setItem('slwarehouse', $(this).val());
});
if (slwarehouse = localStorage.getItem('slwarehouse')) {
    $('#slwarehouse').select2("val", slwarehouse);
}

    $('#slnote').redactor('destroy');
    $('#slnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('slnote', v);
        }
    });
    if (slnote = localStorage.getItem('slnote')) {
        $('#slnote').redactor('set', slnote);
    }
    $('#slinnote').redactor('destroy');
    $('#slinnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('slinnote', v);
        }
    });
    if (slinnote = localStorage.getItem('slinnote')) {
        $('#slinnote').redactor('set', slinnote);
    }

    // prevent default action usln enter
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
        $('#sltax2').change(function () {
            localStorage.setItem('sltax2', $(this).val());
            loadItems();
            return;
        });
    }

    // Order discount calculation
    var old_sldiscount;
    $('#sldiscount').focus(function () {
        old_sldiscount = $(this).val();
    }).change(function () {
        var new_discount = $(this).val() ? $(this).val() : '0';
        if (is_valid_discount(new_discount)) {
            localStorage.removeItem('sldiscount');
            localStorage.setItem('sldiscount', new_discount);
            loadItems();
            return;
        } else {
            $(this).val(old_sldiscount);
            bootbox.alert(lang.unexpected_value);
            return;
        }

    });


    /* ----------------------
     * Delete Row Method
     * ---------------------- */


    $(document).on('click', '.sldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete slitems[item_id];
        row.remove();
        if(slitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('slitems', JSON.stringify(slitems));
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
        item = slitems[item_id];
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

        if(site.settings.product_additional != 0){
            if(item.additional_products){
                opt_additional = $("<select id=\"opt_additional\" name=\"opt_additional\" class=\"form-control select\" multiple/>");
                var pro_add = row.children().children('.r_product_additional').val();
                var myarray = pro_add.split(",");
                $.each(item.additional_products, function () {
                    if(jQuery.inArray(this.id, myarray) !== -1){
                        $("<option />", { value: this.id, text: this.name, selected:true}).appendTo(opt_additional);
                        unit_price -= formatDecimalRaw(this.price * this.product_additional);
                    }else{
                        $("<option />", { value: this.id, text: this.name}).appendTo(opt_additional);
                    }
                });
                
            }else{
                opt_additional = '<p style="margin: 12px 0 0 0;">n/a</p>';
            }
            $('#paditional-div').html(opt_additional);
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

                            if (slitems[item_id].row.tax_method == 0) {
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
        if (site.settings.product_serial != 0) {
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
        
        
        if(site.settings.product_serial != 0){
            if(item.product_serials!=''){
                uopt1 = $("<select id=\"product_serial\" name=\"product_serial\" class=\"form-control select\" multiple/>");
                var serialno = row.children().children('.rserial').val();
                var myarray = serialno.split("#");
                $.each(item.product_serials, function () {
                    if(jQuery.inArray(this.serial, myarray) !== -1){
                        $("<option />", { value: this.id, text: this.serial, selected:true}).appendTo(uopt1);
                    }else{
                        $("<option />", { value: this.id, text: this.serial}).appendTo(uopt1);
                    }
                });
            }else{
                uopt1 = '<p style="margin: 12px 0 0 0;">n/a</p>';
            }
            $("#pserials-div").html(uopt1);
        }
        

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

        var bom_type = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.bom_typies !== false){
            var bom_type = $("<select id=\"bom_type\" name=\"bom_type\" class=\"form-control select\" />");
            $.each(item.bom_typies, function () {
                if(this.bom_type == item.row.bom_type) {
                    $("<option />", {value: this.bom_type, text: this.bom_type, selected:true}).appendTo(bom_type);
                } else {
                    $("<option />", {value: this.bom_type, text: this.bom_type}).appendTo(bom_type);
                }
            });
        }
        if(item.enable_bom){
            $('#pbom_type-div').html(bom_type);
        }
        
        var bproduct_currency = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.currencies !== false){
            var bproduct_currency = $("<select id=\"pproduct_currency\" name=\"product_currency\" class=\"form-control select\" />");
            $.each(item.currencies, function () {
                if(this.code == item.row.currency_code) {
                    $("<option />", {value: this.code, text: this.name, selected:true}).appendTo(bproduct_currency);
                } else {
                    $("<option />", {value: this.code, text: this.name}).appendTo(bproduct_currency);
                }
            });
        }
        if(item.currencies){
            $('#pproduct_currency-div').html(bproduct_currency);
        }
        
        if(item.room_rent == true){
            var electricity = item.row.electricity;
            var old_number = item.row.old_number;
            var new_number = item.row.new_number;
            var service_types = item.row.service_types;

            if(electricity==1 || electricity==2){
                $("#electricity").show();
                $('#old_number').val(old_number);
                $('#new_number').val(new_number);
                $('#service_types').val(service_types);
            }else{
                $("#electricity").hide();
                $('#old_number').val('');
                $('#new_number').val('');
                $('#service_types').val(service_types);
            }
        }
        
        var total = (net_price+pr_tax_val) * qty;
        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimalRaw(parseFloat(unit_price)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#service_types').val(service_types);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#pro_total').text(formatMoney(total));
        $('#hpro_total').val(total);
        $('#prModal').appendTo("body").modal('show');

    });
    
    $(document).on('change', '#pproduct_currency', function () {
        var row = $('#' + $('#row_id').val()), ccode = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
        var real_unit_price = item.row.real_unit_price;
        var real_currency_rate = item.row.real_currency_rate;
        
        if(item.currencies !== false) {
            $.each(item.currencies, function () {
                if(this.code == ccode) {
                    var pprice = (parseFloat(real_unit_price) / real_currency_rate) * parseFloat(this.rate);
                    $('#pprice').val(pprice).trigger('change');
                }
            });
        }
    });

    $(document).on('click', '#add_formulation', function () {
        var tr_html = '<tr>';
        tr_html +=  '<td><input type="hidden" class="for_product_id" name="for_product_id[]" /><input class="form-control tip pro_name" id="pro_name" type="text" name="pro_name[]"/></td>';
        tr_html +=  '<td><input value="1" class="form-control text-right tip for_free_quantity" id="for_free_quantity" type="text" name="for_free_quantity[]"/></td>';
        tr_html += '<td class="text-center"><i class="fa fa-times tip for_del"  title="Remove" style="cursor:pointer;"></i></td></tr>';
        $("#promotionTable > tbody").append(tr_html);
    });
    

    $(document).on('click', '.for_del', function () {
        var id = $(this).attr('id');
        $(this).parent().parent().remove();
    });
    
    $(document).on('click', '.delete_promotion_product', function () {
        var parent = $(this).parent().parent();
        parent.remove();
        return false;
    });
    
    $(document).on('click', '#add_proProduct', function () {
        var td_promotion = '<tr>';
            td_promotion += '<td><input type="hidden" class="promotion_product_id"/><input class="form-control tip promotion_product" type="text"/></td>';
            td_promotion += '<td><input type="text" value="1" class="form-control text-right promotion_quantity"/></td>';
            td_promotion += '<td class="text-center"><a href="#" class="btn btn-sm delete_promotion_product"><i class="fa fa-trash"></i></a></td>';
            td_promotion += '</tr>';
        $('#proProduct tbody').append(td_promotion);    
    });
    
    $(document).on('click', '.promotion', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = slitems[item_id];
        $('#irow_id').val(row_id);
        var pro_header = '';
        var td_condition = '';
        var td_promotion = '';
        if(item.product_promotions){
            $.each(item.product_promotions,function(){
                td_condition += '<tr>'
                    td_condition += '<td>'+this.product_code+' - '+this.product_name+'</td>';
                    td_condition += '<td class="text-right">'+formatNumber(this.min_qty)+'</td>';
                    td_condition += '<td class="text-right">'+formatNumber(this.max_qty)+'</td>';
                    td_condition += '<td class="text-right">'+formatNumber(this.free_qty)+'</td>';
                td_condition += '/tr>'
                
                pro_header = this.promotion_name;
                pro_header += ' : '+fsd(this.start_date) +' to '+ fsd(this.end_date);
            });
        }
        if(item.row.product_frees){
            $.each(item.row.product_frees,function(){
                td_promotion += '<tr>';
                    td_promotion += '<td><input type="hidden" value="'+this.product_id+'" class="promotion_product_id"/><input value="'+this.product_name+'" class="form-control tip promotion_product" type="text"/></td>';
                    td_promotion += '<td><input type="text" value="'+this.product_quantity+'" class="form-control text-right promotion_quantity"/></td>';
                    td_promotion += '<td class="text-center"><a href="#" class="btn btn-sm delete_promotion_product"><i class="fa fa-trash"></i></a></td>';
                td_promotion += '</tr>';
            })

        }
    
        $('#proProduct tbody').html(td_promotion);
        $('#proCondition tbody').html(td_condition);
        $('#prProModalLabel').text(item.row.code + ' - ' + item.row.name + ' (' + pro_header +')');
        $('#prProModal').appendTo("body").modal('show');
    });
    
    
    $(document).on('click', '#editPromotion', function () {
        var row = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        var product_frees = [];
        $('.promotion_product_id').each(function(){
            var parent = $(this).parent().parent();
            var product_id = $(this).val();
            var product_name = parent.find('.promotion_product').val();
            var product_quantity = parent.find('.promotion_quantity').val();
            if(product_id > 0){
                var product_free = { 
                        product_id:product_id,  
                        product_name: product_name, 
                        product_quantity : product_quantity
                        };
                product_frees.push(product_free);
            }
        });
        slitems[item_id].row.product_frees = product_frees;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        $('#prProModal').modal('hide');
        loadItems();
        return;
    });
    


    $(document).on('click', '.comment', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = slitems[item_id];
        
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
        slitems[item_id].row.order = parseFloat($('#iorders').val()),
        slitems[item_id].row.comment = $('#icomment').val() ? $('#icomment').val() : '',
        localStorage.setItem('slitems', JSON.stringify(slitems));
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
    
    $(document).on('change', '#pprice, #ptax, #pdiscount, #pquantity', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var quantity = parseFloat($('#pquantity').val());
        var item = slitems[item_id];
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
        
        var total = (unit_price+pr_tax_val) * quantity;
        $('#net_price').text(formatMoney(unit_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#pro_total').text(formatMoney(total));
        $('#hpro_total').val(total);
    });

    
    
    $(document).on('change', '#product_serial', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
        var newprice = [], serialno = [] , pscost = [];
        var serials = '';
        var cost = 0;
        var price = 0;
        var qty = 0;
        $.each(item.product_serials, function(){
            newprice[this.id] = this.price;
            serialno[this.id] = this.serial;
            pscost[this.id] = this.cost;
        });

        
        $('#product_serial > option:selected').each(function() {
            serials += serialno[$(this).val()];
            serials += '#';
            cost += (pscost[$(this).val()])-0;
            price += (newprice[$(this).val()])-0;
            qty++;
        });
        
        price = price / qty;
        cost = cost / qty;
        
        $('#pquantity').val(qty);
        $('#pserial').val(serials);
        $('#pscost').val(cost);
        $('#pprice').val(formatDecimalRaw(price)).change();
    });
    
    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
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
        
        if(unit != slitems[item_id].row.base_unit) {
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
    
    $(document).on('click', '#calculate_unit_price', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var subtotal = parseFloat($('#psubtotal').val()),
        qty = parseFloat($('#pquantity').val());
        $('#pprice').val(formatDecimalRaw((subtotal/qty))).change();
        return false;
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
        if (!is_numeric($('#pquantity').val())) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if(unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        
        if (site.settings.product_serial == 1 && $('#pserial').val() &&  $('#pserial').val() !='') {
            slitems[item_id].row.cost = parseFloat($('#pscost').val());
        }
        if(item.bom_typies !== false) {
            slitems[item_id].row.bom_type = $('#bom_type').val();
        }
        
        if(site.settings.product_promotions && slitems[item_id].product_promotions){
            var product_frees = [];
            $.each(slitems[item_id].product_promotions,function(){
                if(base_quantity >= this.min_qty && base_quantity <= this.max_qty){
                    var product_free = { 
                            product_id: this.product_id,  
                            product_name: this.product_name+' ('+this.product_code+')', 
                            product_quantity : this.free_qty
                        };
                    product_frees.push(product_free);
                }
            });
            slitems[item_id].row.product_frees = product_frees;
        }
        
        if(item.room_rent !== false){
            slitems[item_id].row.old_number = $("#old_number").val() ? $("#old_number").val() : ''; 
            slitems[item_id].row.new_number = $("#new_number").val() ? $("#new_number").val() : '';    
            slitems[item_id].row.service_types = $("#service_types").val() ? $("#service_types").val() : '';    
        }
        
        if(item.currencies !== false){
            var currency_code = []; 
            var currency_rate = [];
            var product_currency = $("#pproduct_currency").val()?$("#pproduct_currency").val():null;
            $.each(slitems[item_id].currencies,function(){
                currency_code[this.code] = this.code;
                currency_rate[this.code] = this.rate;
            });
            slitems[item_id].row.currency_code = currency_code[product_currency]?currency_code[product_currency]:null;
            slitems[item_id].row.currency_rate = currency_rate[product_currency]?currency_rate[product_currency]:null;
        }
        
        slitems[item_id].row.fup = 1,
        slitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        slitems[item_id].row.base_quantity = parseFloat(base_quantity),
        slitems[item_id].row.unit_price = price,
        slitems[item_id].row.unit = unit,
        slitems[item_id].row.tax_rate = new_pr_tax,
        slitems[item_id].tax_rate = new_pr_tax_rate,
        slitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        slitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
        slitems[item_id].row.serial = $('#pserial').val();
        slitems[item_id].row.service_types = $('#pservice_types').val();
        slitems[item_id].row.product_formulation = $('#opt_formulation').val();
        slitems[item_id].row.product_additional = $('#opt_additional').val();
        localStorage.setItem('slitems', JSON.stringify(slitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });
    /* -----------------------
     * Product room rent change
     ----------------------- */
    $(document).on('change keyup blur', '#new_number, #old_number', function () {
        var old_number = $("#old_number").val();
        var new_number = $("#new_number").val();
        var service_types = $("#service_types").val();

        var pquantity = new_number - old_number;
        $("#pquantity").val(pquantity);
    });
    /* -----------------------
     * Product option change
     ----------------------- */
    $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
        var unit = $('#punit').val(),  real_unit_price = item.row.real_unit_price;
        if(unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function(){
                if (this.id == unit) {
                    real_unit_price = formatDecimalRaw((parseFloat(item.row.real_unit_price)*(unitToBaseQty(1, this))), 4)
                }
            });
        }
        $('#pprice').val(parseFloat(real_unit_price)).trigger('change');
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(real_unit_price)+(parseFloat(this.price))).trigger('change');
                }
            });
        }
    });

     /* ------------------------------
     * Sell Gift Card modal
     ------------------------------- */
     $(document).on('click', '#sellGiftCard', function (e) {
        if (count == 1) {
            slitems = {};
            if ($('#slwarehouse').val() && $('#slcustomer').val()) {
                $('#slcustomer').select2("readonly", true);
                $('#slwarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#gcModal').appendTo("body").modal('show');
        return false;
    });

     $(document).on('click', '#addGiftCard', function (e) {
        var mid = (new Date).getTime(),
        gccode = $('#gccard_no').val(),
        gcname = $('#gcname').val(),
        gcvalue = $('#gcvalue').val(),
        gccustomer = $('#gccustomer').val(),
        gcexpiry = $('#gcexpiry').val() ? $('#gcexpiry').val() : '',
        gcprice = parseFloat($('#gcprice').val());
        if(gccode == '' || gcvalue == '' || gcprice == '' || gcvalue == 0 || gcprice == 0) {
            $('#gcerror').text('Please fill the required fields');
            $('.gcerror-con').show();
            return false;
        }

        var gc_data = new Array();
        gc_data[0] = gccode;
        gc_data[1] = gcvalue;
        gc_data[2] = gccustomer;
        gc_data[3] = gcexpiry;
        //if (typeof slitems === "undefined") {
        //    var slitems = {};
        //}

        $.ajax({
            type: 'get',
            url: site.base_url+'sales/sell_gift_card',
            dataType: "json",
            data: { gcdata: gc_data },
            success: function (data) {
                if(data.result === 'success') {
                    slitems[mid] = {"id": mid, "item_id": mid, "label": gcname + ' (' + gccode + ')', "row": {"id": mid, "code": gccode, "name": gcname, "quantity": 1, "price": gcprice, "real_unit_price": gcprice, "tax_rate": 0, "qty": 1, "type": "manual", "discount": "0", "serial": "", "option":""}, "tax_rate": false, "options":false};
                    localStorage.setItem('slitems', JSON.stringify(slitems));
                    loadItems();
                    $('#gcModal').modal('hide');
                    $('#gccard_no').val('');
                    $('#gcvalue').val('');
                    $('#gcexpiry').val('');
                    $('#gcprice').val('');
                } else {
                    $('#gcerror').text(data.message);
                    $('.gcerror-con').show();
                }
            }
        });
        return false;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            slitems = {};
            if ($('#slwarehouse').val() && $('#slcustomer').val()) {
                $('#slcustomer').select2("readonly", true);
                $('#slwarehouse').select2("readonly", true);
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
        unit_cost = parseFloat($('#mcost').val()),
        add_product = $('#add_product').val(),
        mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function () {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            slitems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname,"add_product": add_product, "quantity": mqty, "price": unit_price, "cost": unit_cost, "unit_price": unit_price, "real_unit_price": unit_price, "tax_rate": mtax, "tax_method": 0, "qty": mqty, "type": "manual", "discount": mdiscount, "serial": "", "option":""}, "tax_rate": mtax_rate, 'units': false, "options":false, 'product_expiries': false};
            localStorage.setItem('slitems', JSON.stringify(slitems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        $('#mcost').val('');
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
                            pr_tax_val = formatDecimalRaw((((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
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

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
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
        slitems[item_id].row.swidth = swidth;
        slitems[item_id].row.sheight = sheight;
        slitems[item_id].row.square = square;
        slitems[item_id].row.square_qty = square_qty;
        slitems[item_id].row.qty = quantity;
        if(slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
        $.each(slitems[item_id].units, function(){
                if (this.id == slitems[item_id].row.unit) {
                    slitems[item_id].row.base_quantity = unitToBaseQty(quantity, this);
                }
            });
        }else{
            slitems[item_id].row.base_quantity = quantity;
        }
        localStorage.setItem('slitems', JSON.stringify(slitems));
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
        slitems[item_id].row.square = square;
        slitems[item_id].row.square_qty = square_qty;
        slitems[item_id].row.qty = quantity;
        if(slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
        $.each(slitems[item_id].units, function(){
                if (this.id == slitems[item_id].row.unit) {
                    slitems[item_id].row.base_quantity = unitToBaseQty(quantity, this);
                }
            });
        }else{
            slitems[item_id].row.base_quantity = quantity;
        }
        localStorage.setItem('slitems', JSON.stringify(slitems));
        loadItems();
    });
    
    
    /* --------------------------
     * Edit Row Quantity Method
    --------------------------- */
    var old_row_qty;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) /*|| parseFloat($(this).val()) < 0*/) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        base_qty = new_qty,
        item_id = row.attr('data-item-id');
        slitems[item_id].row.base_quantity = new_qty;
        if(slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function(){
                if (this.id == slitems[item_id].row.unit) {
                    base_qty = unitToBaseQty(new_qty, this);
                    slitems[item_id].row.base_quantity = base_qty;
                }
            });  
        }  
        
        if(site.settings.product_promotions && slitems[item_id].product_promotions){
            var product_frees = [];
            $.each(slitems[item_id].product_promotions,function(){
                if(base_qty >= this.min_qty && base_qty <= this.max_qty){
                    var product_free = { 
                            product_id:this.product_id,  
                            product_name: this.product_name+' ('+this.product_code+')', 
                            product_quantity : this.free_qty
                            };
                    product_frees.push(product_free);
                }
            });
            slitems[item_id].row.product_frees = product_frees;
        }
        
        if(slitems[item_id].room_rent==true){
            if(slitems[item_id].row.electricity==1 || slitems[item_id].row.electricity==2){
                var old_number = parseFloat(slitems[item_id].row.old_number);
                slitems[item_id].row.new_number = parseFloat(new_qty) + parseFloat(old_number);
            }
        }
        slitems[item_id].row.qty = new_qty;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        loadItems();
    });    

    $(document).on('change', '.sunit', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
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
        slitems[item_id].row.base_quantity = base_quantity;
        slitems[item_id].row.unit_price = unit_price;
        slitems[item_id].row.unit = new_unit;
        localStorage.setItem('slitems', JSON.stringify(slitems));
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
        slitems[item_id].row.foc = new_foc;
        localStorage.setItem('slitems', JSON.stringify(slitems));
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
        slitems[item_id].row.price = new_price;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        loadItems();
    });          
                 
    $(document).on("click", '#removeReadonly', function () {
        $('#slcustomer').select2('readonly', false);
        //$('#slwarehouse').select2('readonly', false);
        return false;
    });          
                 
                 
});              
/* -----------------------
 * Misc Actions  
 ----------------------- */
                 
// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#slcustomer').select2({
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

    $(document).on('click', '.combo', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = slitems[item_id];
        $('#irow_id').val(row_id);
        var td_combo = '';
        if(item.combo_items){
            $.each(item.combo_items,function(){
                td_combo += '<tr>'
                    td_combo += '<td><input value="'+this.id+'" type="hidden" class="combo_product_id"/><input type="hidden" class="combo_code" value="'+this.code+'"/><input type="hidden" class="combo_name" value="'+this.name+'"/><input value="'+this.name+' ('+this.code+')" class="form-control tip combo_product" type="text"/></td>';
                    if (site.settings.qty_operation == 1) {
                        td_combo += '<td class="text-center"><input value="'+formatDecimal(this.width)+'" class="form-control text-right combo_width" type="text"/></td>';
                        td_combo += '<td class="text-center"><input value="'+formatDecimal(this.height)+'" class="form-control text-right combo_height" type="text"/></td>';
                    }
                    td_combo += '<td class="text-center"><input value="'+formatDecimal(this.qty)+'" class="form-control text-right combo_qty" type="text"/></td>';
                    td_combo += '<td class="text-right"><input class="form-control combo_price text-right" type="text" value="'+formatDecimal(this.price)+'"/></td>';
                    td_combo += '<td class="text-center"><a href="#" class="btn btn-sm delete_combo_product"><i class="fa fa-trash"></i></a></td>';
                td_combo += '/tr>';
            });
        }

        $('#comboProduct tbody').html(td_combo);
        $('#comboModalLabel').text(item.row.code + ' - ' + item.row.name);
        $('#comboModal').appendTo("body").modal('show');
    });


    $(document).on('click', '#add_comboProduct', function () {
        var td_combo = '<tr>';
            td_combo += '<td><input type="hidden" class="combo_product_id"/><input type="hidden" class="combo_name"/><input type="hidden" class="combo_code" /><input class="form-control tip combo_product" type="text"/></td>';
            if (site.settings.qty_operation == 1) {
                td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_width" type="text"/></td>';
                td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_height" type="text"/></td>';
            }
            td_combo += '<td class="text-right"><input value="1" class="form-control text-right combo_qty" type="text"/></td>';
            td_combo += '<td class="text-right"><input class="form-control combo_price text-right" type="text"/></td>';
            td_combo += '<td class="text-center"><a href="#" class="btn btn-sm delete_combo_product"><i class="fa fa-trash"></i></a></td>';
            td_combo += '</tr>';
        $('#comboProduct tbody').append(td_combo);  
    });


    $(document).on('click', '.delete_combo_product', function () {
        var parent = $(this).parent().parent();
        parent.remove();
        return false;
    });
    
    $(document).on('click', '#editCombo', function () {
        var row = $('#' + $('#irow_id').val());
        var item_id = row.attr('data-item-id');
        var combo_items = [];
        var unit_price = 0;
        $('.combo_product_id').each(function(){
            var parent = $(this).parent().parent();
            var product_id = $(this).val();
            var product_name = parent.find('.combo_name').val();
            var product_code = parent.find('.combo_code').val();
            var product_price = parent.find('.combo_price').val() - 0;
            var product_qty = parent.find('.combo_qty').val() - 0;
            var product_width = parent.find('.combo_width').val() - 0;
            var product_height = parent.find('.combo_height').val() - 0;
            if(product_id > 0){
                var combo_product = { 
                        id:product_id,  
                        name: product_name, 
                        code : product_code,
                        price : product_price,
                        qty : product_qty,
                        width : product_width,
                        height : product_height,
                        };
                combo_items.push(combo_product);
                unit_price += (product_price * product_qty);
            }
        });
        slitems[item_id].row.unit_price = unit_price,
        slitems[item_id].combo_items = combo_items;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        $('#comboModal').modal('hide');
        loadItems();
        return;
    });

    
    var old_value;
    $(document).on("focus", '.combo_qty, .combo_price', function () {
        old_value = $(this).val();
    }).on("change", '.combo_qty, .combo_price', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_value);
            bootbox.alert(lang.unexpected_value);
            return;
        }
    });
    
    var old_combo_w_h;
    $(document).on("focus", '.combo_width, .combo_height', function () {
        old_combo_w_h = $(this).val();
    }).on("change", '.combo_width, .combo_height', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_combo_w_h);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var parent = $(this).parent().parent();
        var combo_width = parent.find('.combo_width').val() - 0;
        var combo_height = parent.find('.combo_height').val() - 0;
        var combo_square = combo_width * combo_height;
        parent.find('.combo_qty').val(combo_square);
    });
    

    

         

function loadItems() {
                 
    if (localStorage.getItem('slitems')) {
        total = 0;
        count = 1;
        an = 1;  
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        var t_quantity = 0;
        var c_product_comission = 0;
        var total_foc = 0;
  
        $("#slTable tbody").empty();
        slitems = JSON.parse(localStorage.getItem('slitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(slitems, function(o){  return [parseInt(o.order)];}) :   slitems;
        $('#add_sale, #edit_sale, #add_sale_next').attr('disabled', false);
        
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
                item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            var unit_price = item.row.unit_price;
            var item_comment = item.row.comment ? item.row.comment : '';
            var add_product = (item.row.add_product)?item.row.add_product:0;
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
            

            if(site.settings.product_additional != 0){
                if(item.row.product_additional == undefined){
                    item.row.product_additional = "";
                }
                var product_additional_td = '<input value="'+item.row.product_additional+'" class="r_product_additional" type="hidden" name="product_additional[]"/>';
                var pro_add = item.row.product_additional+'';;
                var myarray = pro_add.split(",");
                $.each(item.additional_products, function () {
                    if(jQuery.inArray(this.id, myarray) !== -1){
                        unit_price += formatDecimalRaw(this.price * this.product_additional);
                    }
                });
            }else{
                var product_additional_td = '';
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
            if(item.category && site.settings.search_by_category == 1){
                var unit_name = '';
                $.each(item.units, function(){
                    if (this.id == product_unit) {
                        unit_name = ' ('+this.name+')';
                    }
                });
                var label_name = item.category +' ('+item_name+') '+unit_name;
            }else{
                var label_name = item_code +' - '+ item_name + (sel_opt != '' ? ' ('+sel_opt+')' : '');
            }
            if(item.room_rent==true){
                if(item.row.electricity==1 || item.row.electricity==2){
                    var old_number = item.row.old_number?item.row.old_number:0;
                    var new_number = item.row.new_number?item.row.new_number:0;
                    label_name += ' &nbsp; [ ' + old_number +' - ' + new_number +' ]';
                }
            }
            if(site.settings.product_promotions && item.product_promotions){
                var button_promotion = '<i class="pull-right fa fa-gift tip pointer promotion" id="' + row_no + '" data-item="' + item_id + '" title="Promotion" style="cursor:pointer;margin-right:5px;"></i>'; 
                var product_promotion = "<input type='hidden' name='product_promotion[]' value='"+JSON.stringify(item.row.product_frees)+"'/>";
            }else{
                var button_promotion = '';
                var product_promotion = '<input type="hidden" name="product_promotion[]"/>';
            }

            var button_combo = '';
            var product_combo = '<input type="hidden" name="product_combo[]"/>';
            if(item_type=='combo' && combo_items){
                button_combo = '<i class="pull-right fa fa-object-group tip pointer combo" id="' + row_no + '" data-item="' + item_id + '" title="Combo" style="cursor:pointer;margin-right:5px;"></i>'; 
                product_combo = "<input type='hidden' name='product_combo[]' value='"+JSON.stringify(combo_items)+"'/>";
            }
            tr_html = '<td><input name="fuel_customer_date[]" type="hidden" value="' + item.row.fuel_customer_date + '"><input name="fuel_customer_reference[]" type="hidden" value="' + item.row.fuel_customer_reference + '"><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="consignment_item_id[]" type="hidden" value="' + (item.row.consignment_item_id  > 0 ? item.row.consignment_item_id : '')+ '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_comment[]" type="hidden" class="rcomment" value="' + item_comment + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + label_name +'</span><i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i><i class="pull-right fa fa-comment'+(item_comment != '' ? '' :'-o')+' tip pointer comment" id="' + row_no + '" data-item="' + item_id + '" title="Comment" style="cursor:pointer;margin-right:5px;"></i>'+button_promotion+button_combo+'<input type="hidden" name="add_product[]" value="'+add_product+'"/> &nbsp; <input type="hidden" name="cost[]" value="'+formatDecimalRaw(item.row.cost)+'" /></td>';
            
            if(site.settings.show_qoh == 1){
                tr_html += '<td class="text-center"><span>'+(item_type=='standard' ? formatDecimal(qoh) : '')+'</span></td>'
            }
            
            
            if(item.currencies !== false){
                tr_html += '<input type="hidden" class="currency_rate" name="currency_rate[]"  value="' + item.row.currency_rate + '"/>';
                tr_html += '<input type="hidden" class="currency_code" name="currency_code[]"  value="' + item.row.currency_code + '"/>';
            }
            if(item.room_rent !== false){
                tr_html += '<input type="hidden" name="electricity[]"  value="' + item.row.electricity + '"/>';
                tr_html += '<input type="hidden" name="old_number[]"  value="' + item.row.old_number + '"/>';
                tr_html += '<input type="hidden" name="new_number[]"  value="' + item.row.new_number + '"/>';
                tr_html += '<input type="hidden" name="service_types[]"  value="' + item.row.service_types + '"/>';

            }
            
            if(item.product_commission){
                c_product_comission = 1;
                tr_html += '<td><select name="product_salesmans[]"  class="form-control select rsalesman" style="width:100%;">';
                var salesman_opt = '<option selected value="0">Select Salesman</option>';
                if(item.salesmans){
                    $.each(item.salesmans, function () {
                        if(item.row.salesman_id == this.id){
                            salesman_opt += '<option selected value="'+this.id+'">'+this.last_name+' '+this.first_name+'</option>';
                        }else{
                            salesman_opt += '<option value="'+this.id+'">'+this.last_name+' '+this.first_name+'</option>';
                        }
                    });
                }
                
                tr_html += salesman_opt;
                tr_html += '</select></td>';
            }
            
            
            if (site.settings.product_expiry == 1) {
                tr_html += '<td>'+expiry_select+'</td>';
            }    
            if (site.settings.product_serial == 1) {
                tr_html += '<td class="text-right"><input readonly="true" class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="'+item_serial+'"></td>';
            }       
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + formatDecimalRaw(item.row.real_unit_price) + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
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

                 
            if(item.bom_typies){
                var bom_type = '<input type="hidden" value="'+item.row.bom_type+'" name="bom_type[]" class="bom_type"/>';
            }else{
                var bom_type = '';
            }    

            if(item_serial && item_serial != '' && typeof(item_serial) != "undefined" && site.settings.product_serial == 1){    
                squantity = 'readonly';
            } 
            
            tr_html += '<td>' + product_combo +  product_promotion + product_additional_td + bom_type +'<input ' + squantity + ' class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimalRaw(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            
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
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (parseFloat(pr_tax_rate) != 0 ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }
            var allow_remove_c = '';
            if(!allow_remove){
                allow_remove_c = ' hidden';
            }
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer sldel '+allow_remove_c+'" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            tr_html += '<input type="hidden" name="parent_id[]" value="'+item.row.parent_id+'" />';
            tr_html += '<input type="hidden" name="item_note[]" value="'+item.row.item_note+'" />';
            newTr.html(tr_html);
            newTr.prependTo("#slTable");
            $('select').select2();
            var currency_rate = (item.row.currency_rate?item.row.currency_rate:1);
            total += formatDecimalRaw(((parseFloat(item_price / currency_rate) + parseFloat(pr_tax_val / currency_rate)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;
                 
            t_quantity += base_quantity;
            
            if (typeof(item.row.fuel_customer_reference) == "undefined" || item.row.fuel_customer_reference == "") {
                if (item_type == 'standard' && item.options !== false) {
                    $.each(item.options, function () {
                        if(this.id == item_option && base_quantity > this.quantity) {
                            $('#row_' + row_no).addClass('danger');
                            if(site.settings.overselling != 1) { $('#add_sale, #edit_sale, #add_sale_next').attr('disabled', true); }
                        }
                    });
                } else if(item_type == 'standard' && site.settings.product_expiry == 1 && item.product_expiries){
                    base_quantity = base_quantity - 0;
                    item_aqty = item_aqty - 0;
                    if(base_quantity > qoh || base_quantity > item_aqty){
                        $('#row_' + row_no).addClass('danger');
                        if(site.settings.overselling != 1) { $('#add_sale, #edit_sale, #add_sale_next').attr('disabled', true); }
                    }
                } else if(item_type == 'standard' && base_quantity > item_aqty) {               
                    $('#row_' + row_no).addClass('danger');
                    if(site.settings.overselling != 1) { $('#add_sale, #edit_sale, #add_sale_next').attr('disabled', true); }
                } else if (item_type == 'combo') {
                    if(combo_items === false) {
                        $('#row_' + row_no).addClass('danger');
                        if(site.settings.overselling != 1) { $('#add_sale, #edit_sale, #add_sale_next').attr('disabled', true); }
                    } else {
                        $.each(combo_items, function() {
                           if(parseFloat(this.quantity) < (parseFloat(this.qty)*base_quantity) && this.type == 'standard') {
                               $('#row_' + row_no).addClass('danger');
                               if(site.settings.overselling != 1) { $('#add_sale, #edit_sale, #add_sale_next').attr('disabled', true); }
                           }
                       });
                    }
                }
            }   
        });      
                 
        var col = 2;
        
        if (c_product_comission){col++;}
        if (site.settings.show_qoh == 1) { col++; }
        if (site.settings.product_expiry == 1) { col++; }
        if (site.settings.product_serial == 1) { col++; }
        if (site.settings.qty_operation == 1) { col += 4 }
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
        $('#slTable tfoot').html(tfoot);
                 
        // Order level discount calculations
        if (sldiscount = localStorage.getItem('sldiscount')) {
            var ds = sldiscount;
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
                 
            //total_discount += parseFloat(order_discount);
        }        
                 
        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (sltax2 = localStorage.getItem('sltax2')) {
                $.each(tax_rates, function () {
                    if (this.id == sltax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimalRaw(this.rate);
                        } else if (this.type == 1) {
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
        //$('#tds').text('('+formatMoney(product_discount)+'+'+formatMoney(order_discount)+')'+formatMoney(total_discount));
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }        
        $('#tship').text(formatMoney(shipping));
        $('#gtotal').text(formatMoney(gtotal));
        $('#g_total').val(gtotal);
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }        
        if (count > 1) {
            $('#slcustomer').select2("readonly", true);
            $('#slwarehouse').select2("readonly", true);
        }else{
            $('#slcustomer').select2("readonly", false);
            $('#slwarehouse').select2("readonly", false);
        }       
        set_page_focus();
    }            
}                
                 
/* -----------------------------
 * Add Sale Order Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_invoice_item(item) {
                 
    if (count == 1) {
        slitems = {};
        if ($('#slwarehouse').val() && $('#slcustomer').val()) {
            $('#slcustomer').select2("readonly", true);
            $('#slwarehouse').select2("readonly", true);
        } else { 
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }        
    }            
    if (item == null)
        return;  
                 
    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (slitems[item_id]) {
                 
        var new_qty = parseFloat(slitems[item_id].row.qty) + 1;
        slitems[item_id].row.base_quantity = new_qty;
        if(slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function(){
                if (this.id == slitems[item_id].row.unit) {
                    slitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });  
        }        
        slitems[item_id].row.qty = new_qty;
                 
    } else {     
        slitems[item_id] = item;
    }            
                 
    slitems[item_id].order = new Date().getTime();
    localStorage.setItem('slitems', JSON.stringify(slitems));
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