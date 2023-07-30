<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1,
        an = 1,
        product_variant = 0,
        DT = <?= $Settings->default_tax_rate ?>,
        allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        product_tax = 0,
        invoice_tax = 0,
        total_discount = 0,
        total = 0,
        shipping = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function() {

        <?php if ($this->session->userdata('remove_quls')) { ?>
            if (localStorage.getItem('quitems')) {
                localStorage.removeItem('quitems');
            }
            if (localStorage.getItem('qudiscount')) {
                localStorage.removeItem('qudiscount');
            }
            if (localStorage.getItem('qutax2')) {
                localStorage.removeItem('qutax2');
            }
            if (localStorage.getItem('qushipping')) {
                localStorage.removeItem('qushipping');
            }
            if (localStorage.getItem('quref')) {
                localStorage.removeItem('quref');
            }
            if (localStorage.getItem('quwarehouse')) {
                localStorage.removeItem('quwarehouse');
            }
            if (localStorage.getItem('qusupplier')) {
                localStorage.removeItem('qusupplier');
            }
            if (localStorage.getItem('qunote')) {
                localStorage.removeItem('qunote');
            }
            if (localStorage.getItem('qucustomer')) {
                localStorage.removeItem('qucustomer');
            }
            if (localStorage.getItem('qubiller')) {
                localStorage.removeItem('qubiller');
            }
            if (localStorage.getItem('qucurrency')) {
                localStorage.removeItem('qucurrency');
            }
            if (localStorage.getItem('qudate')) {
                localStorage.removeItem('qudate');
            }
            if (localStorage.getItem('qustatus')) {
                localStorage.removeItem('qustatus');
            }
            if (localStorage.getItem('qusaleman')) {
                localStorage.removeItem('qusaleman');
            }
            if (localStorage.getItem('quvalid_day')) {
                localStorage.removeItem('quvalid_day');
            }
        <?php $this->cus->unset_data('remove_quls');
        } ?>

        <?php if ($this->input->get('customer')) { ?>
            if (!localStorage.getItem('quitems')) {
                localStorage.setItem('qucustomer', <?= $this->input->get('customer'); ?>);
            }
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['quotes-date']) { ?>
            if (!localStorage.getItem('qudate')) {
                $("#qudate").datetimepicker({
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
            $(document).on('change', '#qudate', function(e) {
                localStorage.setItem('qudate', $(this).val());
            });
            if (qudate = localStorage.getItem('qudate')) {
                $('#qudate').val(qudate);
            }
        <?php } ?>
        $(document).on('change', '#qubiller', function(e) {
            localStorage.setItem('qubiller', $(this).val());
        });
        if (qubiller = localStorage.getItem('qubiller')) {
            $('#qubiller').val(qubiller);
        }
        if (!localStorage.getItem('qutax2')) {
            localStorage.setItem('qutax2', <?= $Settings->default_tax_rate2; ?>);
        }

        $(document).on('change', '#qupayment_term', function(e) {
            localStorage.setItem('qupayment_term', $(this).val());
        });
        if (qupayment_term = localStorage.getItem('qupayment_term')) {
            $('#qupayment_term').val(qupayment_term);
        }

        if (quvalid_day = localStorage.getItem('quvalid_day')) {
            $('#quvalid_day').val(quvalid_day);
        }

        $('#quvalid_day').focus(function() {
            old_valid_day = $(this).val();
        }).change(function() {
            if (!is_numeric($(this).val())) {
                $(this).val(old_valid_day);
                bootbox.alert(lang.unexpected_value);
                return;
            } else {
                localStorage.setItem('quvalid_day', $(this).val());
            }

        });



        ItemnTotals();
        $("#add_item").autocomplete({
            source: function(request, response) {
                if (!$('#qucustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('select_above'); ?>');
                    //response('');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('quotes/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#quwarehouse").val(),
                        customer_id: $("#qucustomer").val()
                    },
                    success: function(data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                /* if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else  */
                if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                /* else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');

                } */
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var product_type = ui.item.row.type;
                    if (product_type == 'digital') {
                        $.ajax({
                            type: 'get',
                            url: '<?= site_url('sales/suggestionsDigital'); ?>',
                            dataType: "json",
                            data: {
                                term: ui.item.item_id,
                                warehouse_id: $("#quwarehouse").val(),
                                customer_id: $("#qucustomer").val(),
                            },
                            success: function(result) {
                                $.each(result, function(key, value) {
                                    var row = add_invoice_item(value);
                                    if (row)
                                        $(this).val('');
                                });
                            }
                        });
                        $(this).val('');
                    } else {
                        var row = add_invoice_item(ui.item);
                        if (row)
                            $(this).val('');
                    }
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_quote'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("quotes/add", $attrib)
                ?>

                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['quotes-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "qudate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="qudate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "quref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $qunumber), 'class="form-control input-tip" id="quref"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "qubiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="qubiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'qubiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
                        <?php if ($Settings->project == 1) { ?>
                            <?php if ($Owner || $Admin) { ?>
                                <div class="col-md-4">
                                    <div class="form-group" style="margin-bottom: 13px;">
                                        <?= lang("project", "project"); ?>
                                        <div class="no-project">
                                            <?php
                                            $pj[''] = '';
                                            if (isset($projects) && $projects) {
                                                foreach ($projects as $project) {
                                                    $pj[$project->id] = $project->name;
                                                }
                                            }
                                            echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id) ? $Settings->project_id : ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="col-md-4" style="margin-bottom: 13px;">
                                    <div class="form-group">
                                        <?= lang("project", "project"); ?>
                                        <div class="no-project">
                                            <?php
                                            $pj[''] = '';
                                            if (isset($user) && isset($projects) && $projects) {
                                                $right_project = json_decode($user->project_ids);
                                                if ($right_project) {
                                                    foreach ($projects as $project) {
                                                        if (in_array($project->id, $right_project)) {
                                                            $pj[$project->id] = $project->name;
                                                        }
                                                    }
                                                }
                                            }
                                            echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("warehouse", "quwarehouse"); ?>
                                <?php

                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="quwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group" style="margin-bottom: 13px;">
                                            <?= lang("customer", "qucustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="qucustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
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
                                                        <a href="<?= site_url('customers/add'); ?>" id="add-customer" class="external" data-toggle="modal" data-target="#myModal">
                                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($this->config->item('saleman') == true && ($Owner || $Admin || $GP['sales-assign_sales'])) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("saleman", "saleman"); ?>
                                                <?php
                                                $opsalemans[""] = lang('select') . ' ' . lang('saleman');
                                                foreach ($salemans as $saleman) {
                                                    $opsalemans[$saleman->id] = $saleman->first_name . ' ' . $saleman->last_name;
                                                }
                                                ?>
                                                <?= form_dropdown('saleman_id', $opsalemans, (isset($quote->saleman_id) ? $quote->saleman_id : $this->session->userdata('user_id')), ' id="saleman_id" class="form-control" required="required"'); ?>
                                            </div>
                                        </div>

                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                        </div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>"><i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a>
                                            </div>
                                        <?php } ?>
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
                                    <table id="quTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <?php if ($Settings->qty_operation) {
                                                $head_row = 'rowspan="2"';
                                            } else {
                                                $head_row = '';
                                            }
                                            ?>
                                            <tr>
                                                <th <?= $head_row ?> class="col-md-4"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                <?php if ($Settings->show_qoh == 1) { ?>
                                                    <th <?= $head_row ?> class="col-md-1"><?= lang("qoh"); ?></th>
                                                <?php } ?>
                                                <th <?= $head_row ?> class="col-md-1"><?= lang("net_unit_price"); ?></th>
                                                <?php if ($Settings->qty_operation) { ?>
                                                    <th class="col-md-4" colspan="4"><?= lang("quantity_operation"); ?></th>
                                                    <th <?= $head_row ?> class="col-md-1"><?= lang("total_quantity"); ?></th>
                                                <?php } else { ?>
                                                    <th <?= $head_row ?> class="col-md-1"><?= lang("quantity"); ?></th>
                                                <?php }
                                                if ($Settings->show_unit == 1) { ?>
                                                    <th <?= $head_row ?> class="col-md-1"><?= lang("unit"); ?></th>
                                                <?php }
                                                if ($Settings->foc == 1) {
                                                    echo '<th ' . $head_row . '  class="col-md-1">' . $this->lang->line("foc") . '</th>';
                                                }
                                                if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                    echo '<th ' . $head_row . ' class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                                }
                                                ?>
                                                <?php
                                                if ($Settings->tax1) {
                                                    echo '<th ' . $head_row . '  class="col-md-2">' . $this->lang->line("product_tax") . '</th>';
                                                }
                                                ?>
                                                <th <?= $head_row ?>><?= lang("subtotal"); ?> (<span class="currency"><?= $default_currency->code ?></span>)
                                                </th>
                                                <th <?= $head_row ?> style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                            </tr>
                                            <?php if ($Settings->qty_operation) { ?>
                                                <tr>
                                                    <th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('width') ?></th>
                                                    <th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('height') ?></th>
                                                    <th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('square') ?></th>
                                                    <th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('quantity') ?></th>
                                                </tr>
                                            <?php } ?>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />


                        <?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_tax", "qutax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['tax2']) ? $_POST['tax2'] : $Settings->default_tax_rate2), 'id="qutax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("discount", "qudiscount"); ?>
                                    <?php echo form_input('discount', '', 'class="form-control input-tip" id="qudiscount"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("shipping", "qushipping"); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="qushipping"'); ?>

                            </div>
                        </div>

                        <?php echo form_hidden("status", "pending"); ?>

                        <div class="col-md-4 hidden">
                            <div class="form-group">
                                <?= lang("status", "qustatus"); ?>
                                <?php $st = array('pending' => lang('pending'), 'sent' => lang('sent'));
                                echo form_dropdown('status', $st, '', 'class="form-control input-tip" id="qustatus"'); ?>

                            </div>
                        </div>

                        <div class="col-md-4 hidden">
                            <div class="form-group">
                                <?= lang("supplier", "qusupplier"); ?>
                                <input type="hidden" name="supplier" value="" id="qusupplier" class="form-control" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_term", "qupayment_term"); ?>
                                <?php
                                $pt[''] = '';
                                foreach ($paymentterms as $paymentterm) {
                                    $pt[$paymentterm->id] = $paymentterm->description;
                                }
                                echo form_dropdown('payment_term', $pt, (isset($_POST['payment_term']) ? $_POST['payment_term'] : $Settings->default_payment_term), 'class="form-control input-tip" id="qupayment_term"'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("valid_day", "quvalid_day"); ?>
                                <?php echo form_input('valid_day', '', 'class="form-control input-tip" id="quvalid_day"'); ?>

                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "qunote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="qunote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group"><?php echo form_submit('add_quote', $this->lang->line("submit"), 'id="add_quote" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <?php echo form_submit('add_quote_next', $this->lang->line("submit_and_next"), 'id="add_quote_next" class="btn btn-info" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
                            </div>
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
                            <td><?= lang('shipping') ?> : <span class="totals_val pull" id="tship">0.00</span></td>
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
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
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
                    <?php if ($Settings->attributes == 1) { ?>
                        <div class="form-group">
                            <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                            <div class="col-sm-8">
                                <div id="poptions-div"></div>
                            </div>
                        </div>
                    <?php }
                    if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
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
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>

                    <?php if ($Settings->product_formulation) { ?>
                        <div class="form-group">
                            <label for="pformulation" class="col-sm-4 control-label"><?= lang('product_formulation') ?></label>
                            <div class="col-sm-8">
                                <div id="pformulation-div"></div>
                            </div>
                        </div>
                    <?php } ?>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value="" />
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_price" value="" />
                    <input type="hidden" id="row_id" value="" />
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
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
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
                            <label for="mdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

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
                    <span class="sr-only"><?= lang('close'); ?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">

                <?php if ($suspend_option != 0) { ?>
                    <div class="form-group">
                        <?= lang('tags', 'tags'); ?>
                        <?php
                        $tags1 = array(0 => lang('no'));
                        foreach ($tags as $tag) {
                            $tags1[$tag->name] = $tag->name;
                        }
                        ?>
                        <?= form_dropdown('tags', $tags1, '', 'class="form-control" id="tags" style="width:100%;"'); ?>
                    </div>
                    <script type="text/javascript">
                        $(function() {
                            $("#tags").on("change", function() {
                                var tags = $(this).val();
                                $("#icomment").val(tags);
                            });
                        });
                    </script>

                <?php } ?>
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

                <input type="hidden" id="irow_id" value="" />

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?= lang('submit') ?></button>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#qubiller").change(biller);
        biller();

        function biller() {
            var biller = $("#qubiller").val();
            var project = 0;
            $.ajax({
                url: "<?= site_url("sales/get_project") ?>",
                type: "GET",
                dataType: "JSON",
                data: {
                    biller: biller,
                    project: project
                },
                success: function(data) {
                    if (data) {
                        $(".no-project").html(data.result);
                        $("#project").select2();
                    }
                }
            })
        }

        $customer = 0;
        $('#qucustomer').on("select2-selecting", function(e) {
            var customer = e.val;
            $customer = e.val;
            $.ajax({
                url: site.base_url + "sales/get_company",
                dataType: "JSON",
                type: "GET",
                data: {
                    customer: customer
                },
                success: function(data) {
                    if (data.saleman_id > 0) {
                        $("#saleman_id").select2("val", data.saleman_id);
                        localStorage.setItem('qusaleman', data.saleman_id);
                    }

                }
            });
        });
    });
</script>