$(document).ready(function () {
    if (!localStorage.getItem('ivoref')) {
        localStorage.setItem('ivoref', '');
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
    if (localStorage.getItem('ivoitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('ivoitems')) {
                    localStorage.removeItem('ivoitems');
                }
                if (localStorage.getItem('ivoref')) {
                    localStorage.removeItem('ivoref');
                }
                if (localStorage.getItem('ivonote')) {
                    localStorage.removeItem('ivonote');
                }
                if (localStorage.getItem('ivowarehouse')) {
                    localStorage.removeItem('ivowarehouse');
                }
                if (localStorage.getItem('ivonote')) {
                    localStorage.removeItem('ivonote');
                }
                if (localStorage.getItem('ivodate')) {
                    localStorage.removeItem('ivodate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#ivoref').change(function (e) {
        localStorage.setItem('ivoref', $(this).val());
    });
    if (ivoref = localStorage.getItem('ivoref')) {
        $('#ivoref').val(ivoref);
    }
    
    $('#ivowarehouse').change(function (e) {
        localStorage.setItem('ivowarehouse', $(this).val());
    });
    if (ivowarehouse = localStorage.getItem('ivowarehouse')) {
        $('#ivowarehouse').select2("val", ivowarehouse);
    }


    //$(document).on('change', '#ivonote', function (e) {
        $('#ivonote').redactor('destroy');
        $('#ivonote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('ivonote', v);
            }
        });
        if (ivonote = localStorage.getItem('ivonote')) {
            $('#ivonote').redactor('set', ivonote);
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

    $(document).on('click', '.ivodel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete ivoitems[item_id];
        row.remove();
        if(ivoitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('ivoitems', JSON.stringify(ivoitems));
            loadItems();
            return;
        }
    });
    
    
    $(document).on('change', '.rexpiry', function () { 
        var item_id = $(this).closest('tr').attr('data-item-id');
        ivoitems[item_id].row.expiry = $(this).val();
        localStorage.setItem('ivoitems', JSON.stringify(ivoitems));
    });
    
    $(document).on("focus", '.unit_qty', function () {
        old_unit_qty = $(this).val();
    }).on("change", '.unit_qty', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_unit_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        item_id = row.attr('data-item-id');
        var new_unit_qty = parseFloat($(this).val());
        var unit = ivoitems[item_id].row.unit;
        var quantity = ivoitems[item_id].row.quantity;
        if(ivoitems[item_id].units !== false) {
            $.each(ivoitems[item_id].units, function () {
                if (unit == this.id){
                    quantity = formatDecimalRaw(unitToBaseQty(new_unit_qty, this));
                }
            });
        }
        ivoitems[item_id].row.quantity = quantity,
        ivoitems[item_id].row.unit_qty = new_unit_qty;
        localStorage.setItem('ivoitems', JSON.stringify(ivoitems));
        loadItems();
    });
    
    
    $(document).on("focus", '.unit_cost', function () {
        old_unit_cost = $(this).val();
    }).on("change", '.unit_cost', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_unit_cost);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        item_id = row.attr('data-item-id');
        var new_unit_cost = parseFloat($(this).val());
        var cost = ivoitems[item_id].row.cost;
        var unit = ivoitems[item_id].row.unit;
        if(ivoitems[item_id].units !== false) {
            $.each(ivoitems[item_id].units, function () {
                if (unit == this.id){
                    cost = formatDecimalRaw((parseFloat(new_unit_cost)/(unitToBaseQty(1, this))));      
                }
            });
        }
        ivoitems[item_id].row.cost = cost,
        ivoitems[item_id].row.unit_cost = new_unit_cost;
        localStorage.setItem('ivoitems', JSON.stringify(ivoitems));
        loadItems();
    });
    
    
    
    $(document).on('change', '.sunit', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');         
        var unit = $(this).val();
        var quantity = ivoitems[item_id].row.quantity;
        var unit_qty = ivoitems[item_id].row.unit_qty;
        var cost = ivoitems[item_id].row.cost;
        var unit_cost = ivoitems[item_id].row.unit_cost;
        if(ivoitems[item_id].units !== false) {
            $.each(ivoitems[item_id].units, function () {
                if (unit == this.id){
                    quantity = formatDecimalRaw(unitToBaseQty(unit_qty, this));
                    unit_cost = formatDecimalRaw((parseFloat(cost)*(unitToBaseQty(1, this))));          
                }
            });
        }
        ivoitems[item_id].row.quantity = quantity,
        ivoitems[item_id].row.unit_cost = unit_cost,
        ivoitems[item_id].row.unit = unit,
        localStorage.setItem('ivoitems', JSON.stringify(ivoitems));
        loadItems();
    });



});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {
    if (localStorage.getItem('ivoitems')) {
        count = 1;
        an = 1;
        var total_qty = 0;
        $("#ivoTable tbody").empty();
        ivoitems = JSON.parse(localStorage.getItem('ivoitems'));
        $.each(ivoitems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var row_no = (new Date).getTime();
            var item_expiry = (item.row.expiry?item.row.expiry:"");
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
            if (site.settings.product_expiry == 1) {
                tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' + item_expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '"></td>';
            }
            tr_html += '<td><input type="hidden" class="quantity" value="' + (item.row.quantity) + '" name="quantity[]"/><input class="form-control text-center unit_qty"  name="unit_qty[]" type="text" value="' + (item.row.unit_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '"  onClick="this.select();"></td>';
            
            var opt_unit = $("<select name=\"unit[]\" class=\"form-control select sunit\" />");
            if(item.units !== false) {
                $.each(item.units, function () {
                    if (item.row.unit == this.id){
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt_unit);
                    }else{
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt_unit);
                    }
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt_unit);
                opt_unit = opt_unit.hide();
            }
            tr_html += '<td>'+(opt_unit.get(0).outerHTML)+'</td>';
            tr_html += '<td><input type="hidden" value="' + (item.row.cost) + '" class="cost" name="cost[]"/><input class="form-control text-center unit_cost"  name="unit_cost[]" type="text" value="' + (item.row.unit_cost) + '" data-id="' + row_no + '" data-item="' + item_id + '" ></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip ivodel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#ivoTable");
            count ++;
            an++;
            total_qty += (item.row.quantity - 0);
        });

        var col = 1;
        if (site.settings.product_expiry == 1) {col++;}
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(total_qty)) + '</th>';
        tfoot += '<th colspan="2"></th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#ivoTable tfoot').html(tfoot);
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
function add_opening_balance_item(item) {
    if (count == 1) {
        ivoitems = {};
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (ivoitems[item_id]) {
        var new_qty = parseFloat(ivoitems[item_id].row.quantity) + 1;
        ivoitems[item_id].row.base_quantity = new_qty;
        if(ivoitems[item_id].row.unit != ivoitems[item_id].row.base_unit) {
            $.each(ivoitems[item_id].units, function(){
                if (this.id == ivoitems[item_id].row.unit) {
                    ivoitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        ivoitems[item_id].row.quantity = new_qty;
    } else {
        ivoitems[item_id] = item;
    }
    ivoitems[item_id].order = new Date().getTime();
    localStorage.setItem('ivoitems', JSON.stringify(ivoitems));
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