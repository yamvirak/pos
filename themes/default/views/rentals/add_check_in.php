<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
        <?php if($this->session->userdata('remove_rtls')) { ?>
        if (localStorage.getItem('rtitems')) {
            localStorage.removeItem('rtitems');
        }
        if (localStorage.getItem('rtdiscount')) {
            localStorage.removeItem('rtdiscount');
        }
        if (localStorage.getItem('rttax2')) {
            localStorage.removeItem('rttax2');
        }
        if (localStorage.getItem('rtref')) {
            localStorage.removeItem('rtref');
        }
        if (localStorage.getItem('rtwarehouse')) {
            localStorage.removeItem('rtwarehouse');
        }
        if (localStorage.getItem('rtnote')) {
            localStorage.removeItem('rtnote');
        }
        if (localStorage.getItem('rctcustomer')) {
            localStorage.removeItem('rctcustomer');
        }
        if (localStorage.getItem('rtbiller')) {
            localStorage.removeItem('rtbiller');
        }
        if (localStorage.getItem('rtcurrency')) {
            localStorage.removeItem('rtcurrency');
        }
        if (localStorage.getItem('rtdate')) {
            localStorage.removeItem('rtdate');
        }
        if (localStorage.getItem('room_type')) {
            localStorage.removeItem('room_type');
        }
        if (localStorage.getItem('rtroom')) {
            localStorage.removeItem('rtroom');
        }
        if (localStorage.getItem('rtfrom_date')) {
            localStorage.removeItem('rtfrom_date');
        }
        if (localStorage.getItem('rtto_date')) {
            localStorage.removeItem('rtto_date');
        }
        if (localStorage.getItem('rtfrequency')) {
            localStorage.removeItem('rtfrequency');
        }
        if (localStorage.getItem('rtcontract_period')) {
            localStorage.removeItem('rtcontract_period');
        }
        if (localStorage.getItem('rtstatus')) {
            localStorage.removeItem('rtstatus');
        }
        if (localStorage.getItem('rtstaff_note')) {
            localStorage.removeItem('rtstaff_note');
        }
      
        if (localStorage.getItem('tlleavetypes')) {
                localStorage.removeItem('tlleavetypes');
            }
        <?php $this->cus->unset_data('remove_rtls'); } ?>

        <?php if($leave_type){ ?>
                localStorage.setItem('tlleavetypes', '<?= $leave_type ?>');
        <?php } ?>
        
        
        <?php if($this->input->get('customer')) { ?>
            if (!localStorage.getItem('rtitems')) {
                localStorage.setItem('rctcustomer', <?=$this->input->get('customer');?>);
            }
        <?php } ?>


  
        <?php if ($Owner || $Admin || $GP['rentals-date']) { ?>
        if (!localStorage.getItem('rtdate')) {
            $("#rtdate").datetimepicker({
                <?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
                fontAwesome: true,
                language: 'cus',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#rtdate', function (e) {
            localStorage.setItem('rtdate', $(this).val());
        });
        if (rtdate = localStorage.getItem('rtdate')) {
            $('#rtdate').val(rtdate);
        }
        <?php } ?>
        $(document).on('change', '#rtbiller', function (e) {
            localStorage.setItem('rtbiller', $(this).val());
        });
        if (rtbiller = localStorage.getItem('rtbiller')) {
            $('#rtbiller').val(rtbiller);
        }
        if (!localStorage.getItem('rttax2')) {
            localStorage.setItem('rttax2', <?=$Settings->default_tax_rate2;?>);
        }
        $(document).on('change', '#room_type', function (e) {
            localStorage.setItem('room_type', $(this).val());
        });
        if (room_type = localStorage.getItem('room_type')) {
            $('#room_type').val(room_type);
        }
        $(document).on('change', '#rtroom', function (e) {
            localStorage.setItem('rtroom', $(this).val());
        });
        if (rtroom = localStorage.getItem('rtroom')) {
            $('#rtroom').val(rtroom);
        }
        $(document).on('change', '#rtfrequency', function (e) {
            localStorage.setItem('rtfrequency', $(this).val());
        });
        if (rtfrequency = localStorage.getItem('rtfrequency')) {
            $('#rtfrequency').val(rtfrequency);
        }
        $(document).on('change', '#rtcontract_period', function (e) {
            localStorage.setItem('rtcontract_period', $(this).val());
        });
        if (rtcontract_period = localStorage.getItem('rtcontract_period')) {
            $('#rtcontract_period').val(rtcontract_period);
        }
        $(document).on('change', '#rtfrom_date', function (e) {
            localStorage.setItem('rtfrom_date', $(this).val());
        });
        if (rtfrom_date = localStorage.getItem('rtfrom_date')) {
            $('#rtfrom_date').val(rtfrom_date);
        }
        $(document).on('change', '#rtto_date', function (e) {
            localStorage.setItem('rtto_date', $(this).val());
        });
        if (rtto_date = localStorage.getItem('rtto_date')) {
            $('#rtto_date').val(rtto_date);
        }
        
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#rctcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }else if (!$('#rtroom').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }else if (!$('#rtto_date').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }else if (!$('#rtfrom_date').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('rentals/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#rtwarehouse").val(),
                        customer_id: $("#rctcustomer").val(),
                        room_id: $("#rtroom").val(),
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var product_type = ui.item.row.type;
                    if (product_type == 'digital') {
                        $.ajax({
                            type: 'get',
                            url: '<?= site_url('sales/suggestionsDigital'); ?>',
                            dataType: "json",
                            data: {
                                term : ui.item.item_id,
                                warehouse_id: $("#rtwarehouse").val(),
                                customer_id: $("#rctcustomer").val(),
                            },
                            success: function (result) {
                                $.each( result, function(key, value) {
                                    var row = add_invoice_item(value);
                                    if (row)
                                        $(this).val('');
                                });
                            }
                        });
                        $(this).val('');
                    }else {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                    }
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        
        // $(window).bind('beforeunload', function (e) {
        //     $.get('<?= site_url('welcome/set_data/remove_rtls/1'); ?>');
        //     if (count > 1) {
        //         var message = "You will loss data!";
        //         return message;
        //     }
        // });
        
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_check_in'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                    $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                    echo form_open_multipart("rentals_check_in/add", $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['rentals-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "rtdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="rtdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "rtref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $rtnumber), 'class="form-control input-tip" id="rtref"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "rtbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="rtbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'rtbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>

                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_service') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group" style="margin-bottom:10px;">
                                            <?= lang("customer", "rctcustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                    echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="rctcustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="toogle-customer-read-attr" class="external">
                                                        <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="<?= site_url('customers/add'); ?>" id="add-customer"class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("warehouse", "rtwarehouse"); ?>
                                            <?php
                                            foreach ($warehouses as $warehouse) {
                                                $wh[$warehouse->id] = $warehouse->name;
                                            }
                                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="rtwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('sources', 'sources'); ?>
                                            <?php
                                                $opt_src[''] = lang("select") . ' ' . lang("source_type");
                                                foreach ($sources as $source) {
                                                    $opt_src[$source->id] = $source->name;
                                                }
                                                 echo form_dropdown('source_type', $opt_src, '', 'class="tip form-control" id="source_type"');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="room_type"><?php echo $this->lang->line("room_type"); ?></label>
                                            <div class="no-room_type">
                                                <?php
                                                $room_tp[''] = '';
                                                if($room_types){
                                                    foreach ($room_types as $room_type) {
                                                        $room_tp[$room_type->id] = $room_type->name;
                                                    }
                                                }
                                                echo form_dropdown('room_type', $room_tp, '', 'id="room_type" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("room_type") . '" style="width:100%;" ');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 ">
                                        <div class="form-group">
                                            <?= lang("room", "rtroom"); ?>
                                            <div class="no-room">
                                                <select name="room" class="form-control"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("frequency", "rtfrequency"); ?>
                                            <?php
                                            foreach ($frequencies as $frequency) {
                                                $fr[$frequency->day] = $frequency->description;
                                            }
                                            echo form_dropdown('frequency', $fr, (isset($_POST['frequency']) ? $_POST['frequency'] : ''), 'id="rtfrequency" class="form-control" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("frequency") . '" required="required" style="width:100%;" ');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("from_date", "rtfrom_date"); ?>
                                            <div class="input-group">
                                                <?php echo form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] : $this->cus->hrsd(date("Y-m-d"))), 'class="form-control input-tip date bold" id="rtfrom_date" required="required"'); ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("to_date", "rtto_date"); ?>
                                            <div class="input-group">
                                                <?php echo form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] : ""), 'class="form-control input-tip date bold" id="rtto_date" required="required"'); ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <?= lang("adult", "adult"); ?>
                                            <div class="input-group">
                                                <input name="adult" type="number" id="adult" min="0" class="form-control" placeholder="1" value="<?= set_value("adult", '');?>" />
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <i class="fa fa-users" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <?= lang("kid", "kid"); ?>
                                            <div class="input-group">
                                                <input name="kid" type="number" id="kid" min="0" class="form-control" placeholder="0" value="<?= set_value("kid", '');?>"/>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <i class="fa fa-users" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_service_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="rtTable"
                                           class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                        <tr>
                                            <th class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                            <?php if ($Settings->show_qoh == 1) { ?>    
                                                <th class="col-md-1"><?= lang("qoh"); ?></th>
                                            <?php } ?>
                                            <th class="col-md-1"><?= lang("net_unit_price"); ?></th>
                                            <th class="col-md-2"><?= lang("date"); ?></th>
                                            <th class="col-md-2"><?= lang("service_type"); ?></th>
                                            <th class="col-md-1"><?= lang("quantity"); ?></th>
                                            <?php if ($Settings->show_unit == 1) { ?>   
                                                <th class="col-md-2"><?= lang("unit"); ?></th>  
                                            <?php }if($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                            }
                                            ?>
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th class="col-md-2">' . $this->lang->line("product_tax") . '</th>';
                                            }
                                            ?>
                                            <th><?= lang("subtotal"); ?> (<span
                                                    class="currency"><?= $default_currency->code ?></span>)
                                            </th>
                                            <th style="width: 30px !important; text-align: center;"><i
                                                    class="fa fa-trash-o"
                                                    style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("status", "rtstatus"); ?>
                                <?php $st = array('checked_in' => lang('checked_in'));
                                echo form_dropdown('status', $st, 'checked_in', 'class="form-control input-tip" id="rtstatus"'); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-4 hidden">
                            <div class="form-group">
                                <?= lang("contract_period", "rtcontract_period"); ?>
                               <?php echo form_input('contract_period', '', 'class="form-control input-tip" id="rtcontract_period"'); ?>
                            </div>
                        </div>
                        
                        <?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_tax", "rttax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['tax2']) ? $_POST['tax2'] : $Settings->default_tax_rate2), 'id="rttax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("discount", "rtdiscount"); ?>
                                <?php echo form_input('discount', '', 'class="form-control input-tip" id="rtdiscount"'); ?>
                            </div>
                        </div>
                        <?php } ?>

                        
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        
                        <div class="clearfix"></div>
                        
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <?= lang("note", "rtnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="rtnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <?= lang("staff_note", "rtstaff_note"); ?>
                                        <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ""), 'class="form-control" id="rtstaff_note" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_rental', $this->lang->line("submit"), 'id="add_rental" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <?php echo form_submit('add_rental_next', $this->lang->line("submit_and_next"), 'id="add_rental_next" class="btn btn-info" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> : <span class="totals_val pull" id="titems">0</span></td>
                            <td><?= lang('total') ?> : <span class="totals_val pull" id="total">0.00</span></td>
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <td><?= lang('order_discount') ?> : <span class="totals_val pull" id="tds">0.00</span></td>
                            <?php } ?>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> : <span class="totals_val pull" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('grand_total') ?> : <span class="totals_val pull" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>

                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <?php if($Settings->attributes==1){ ?>
                        <div class="form-group">
                            <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                            <div class="col-sm-8">
                                <div id="poptions-div"></div>
                            </div>
                        </div>
                    <?php } if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['rentals-edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                    
                    <?php if($this->config->item("room_rent")){ ?>
                        <div id="electricity">
                            <div class="form-group">
                                <label for="old_number" class="col-sm-4 control-label" style="font-size:11px;"><?= lang('old_number') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="old_number">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new_number" class="col-sm-4 control-label" style="font-size:11px;"><?= lang('new_number') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="new_number" readonly />
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="cmModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="form-group">
                    <?= lang('comment', 'icomment'); ?>
                    <?= form_textarea('comment', '', 'class="form-control skip" id="icomment" style="height:80px;"'); ?>
                </div>
                <div class="form-group hidden">
                    <?= lang('ordered', 'iordered'); ?>
                    <?php
                    $opts = array(0 => lang('no'), 1 => lang('yes'));
                    ?>
                    <?= form_dropdown('ordered', $opts, '', 'class="form-control" id="iordered" style="width:100%;"'); ?>
                </div>
                <input type="hidden" id="irow_id" value=""/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#room_type").on("change", room_type);
        function room_type(e){
            $('.rtdel').each(function(i,e){
                var row = $(this).closest('tr');
                var item_id = row.attr('data-item-id');
                delete rtitems[item_id];
                row.remove();
            });
            var customer_id = $('#rctcustomer').val();
            var room_type_id = $("#room_type").val();
            var room_id = $("#rtroom").val()?$("#rtroom").val():0;

            if (!customer_id) {
                bootbox.alert('<?=lang('select_above');?>');
                $('#room_type').select2('val','');
                return false;
            }
            $.ajax({
                type: "get",
                url: "<?=site_url('rentals/get_room_type')?>",
                data: { room_type_id: room_type_id, room_id : room_id },
                dataType: "json",
                success: function (data) {
                    if(data){
                        $(".no-room").html(data.result);
                        $("#rtroom").select2();
                    }
                }
            });
        }
        
        $(document).on('change', '#rtroom', function (e) {
            
            $('.rtdel').each(function(i,e){
                var row = $(this).closest('tr');
                var item_id = row.attr('data-item-id');
                delete rtitems[item_id];
                row.remove();
            });
            
            var room_id = $("#rtroom").val();
            var customer_id = $('#rctcustomer').val();
            var warehouse_id = $('#rtwarehouse').val();
            if (!customer_id) {
                bootbox.alert('<?=lang('select_above');?>');
                $('#rtroom').select2('val','');
                return false;
            }
            $.ajax({
                type: "get",
                url: "<?=site_url('rentals/get_product_room')?>",
                data: {
                        room_id: room_id, 
                        warehouse_id: warehouse_id, 
                        customer_id: customer_id,
                },
                dataType: "json",
                success: function (data) {
                    if (data.id !== 0) {
                        var row = add_invoice_item(data);
                        if (row){
                            $(this).val('');
                        }
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                    }
                }
            });
        });
        
        $("#rtfrequency, #rtfrom_date").on("change",frequency); frequency();
        function frequency(){
            var frequency = $("#rtfrequency").val();
            var from_date = $("#rtfrom_date").val().split("/");
            var rfrom_date = new Date(from_date[2], from_date[1] - 1, from_date[0]);
            if(frequency==30){
                var to_date = moment(rfrom_date).add(1,'months').format('01/MM/YYYY');
            }else{
                var to_date = moment(rfrom_date).add(parseInt(frequency),'days').format('DD/MM/YYYY');
            }
            $("#rtto_date").val(to_date).trigger('change');
        }
        
        $("#rtfrom_date").datetimepicker({
            format: site.dateFormats.js_sdate,
            fontAwesome: true,
            language: 'cus',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 3,
            todayHighlight: 1,
            startView: 2,
            minView: 2,
            forceParse: 0,
            numberOfMonths: 0,
            minDate: 0,
            startDateHighlight:'-0m',
            startDate: '-0m'

        });
        $("#rtto_date").datetimepicker({
            format: site.dateFormats.js_sdate,
            fontAwesome: true,
            language: 'cus',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 3,
            todayHighlight: 1,
            startView: 2,
            minView: 2,
            forceParse: 0,
            numberOfMonths: 0,
            minDate: 0,
            startDateHighlight:'-0m',
            startDate: '-0m'

        });
        
    });
</script>   
