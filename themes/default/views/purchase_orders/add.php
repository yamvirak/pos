<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, shipping = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
		
		<?php if($this->session->userdata('remove_porls')) { ?>
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
        if (localStorage.getItem('porsupplier')) {
            localStorage.removeItem('porsupplier');
        }
        if (localStorage.getItem('pornote')) {
            localStorage.removeItem('pornote');
        }
        if (localStorage.getItem('porbiller')) {
            localStorage.removeItem('porbiller');
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
        <?php $this->cus->unset_data('remove_porls'); } ?>
		
		<?php if ($inv) { ?>
        localStorage.setItem('pordate', '<?= date($dateFormats[($Settings->date_with_time == 0 ? 'php_sdate' : 'php_ldate')], strtotime($inv->date))?>');
        localStorage.setItem('porbiller', '<?=$inv->biller_id?>');
        localStorage.setItem('porsupplier', '<?=$inv->supplier_id?>');
        localStorage.setItem('porwarehouse', '<?=$inv->warehouse_id?>');
        localStorage.setItem('porstatus', '<?=$inv->status?>');
		localStorage.setItem('pornote', '<?= str_replace(array("\r", "\n", "'"), "", $this->cus->decode_html($inv->note)); ?>');
        localStorage.setItem('pordiscount', '<?=$inv->order_discount_id?>');
        localStorage.setItem('portax2', '<?=$inv->order_tax_id?>');
        localStorage.setItem('porshipping', '<?=$inv->shipping?>');
        localStorage.setItem('poritems', JSON.stringify(<?=$inv_items;?>));
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['purchase_orders-date']) { ?>
        if (!localStorage.getItem('pordate')) {
            $("#pordate").datetimepicker({
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
        $(document).on('change', '#pordate', function (e) {
            localStorage.setItem('pordate', $(this).val());
        });
        if (pordate = localStorage.getItem('pordate')) {
            $('#pordate').val(pordate);
        }
        <?php } ?>
        $(document).on('change', '#porbiller', function (e) {
            localStorage.setItem('porbiller', $(this).val());
        });
        if (porbiller = localStorage.getItem('porbiller')) {
            $('#porbiller').val(porbiller);
        }
        if (!localStorage.getItem('portax2')) {
            localStorage.setItem('portax2', <?=$Settings->default_tax_rate2;?>);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function (request, response) {
				if (!$('#porsupplier').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');                    
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('purchase_orders/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#porwarehouse").val(),
                        supplier_id: $("#porsupplier").val()
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
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_purchase_order'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("purchase_orders/add", $attrib);
				echo form_hidden('purchase_request_id', $id);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['purchase_orders-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "pordate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="pordate" required="required"'); ?>
                                </div>
                            </div>
						<?php } ?>
						<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "porref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="porref"'); ?>
                            </div>
                        </div>
						<?php if($this->config->item('purchase_request')) {  ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("pr_reference", "pr_reference"); ?>
									<?php
									$pr_opts[""] =  lang('select_pr') ;
									if($purchase_requests){
										foreach ($purchase_requests as $purchase_request) {
											$pr_opts[$purchase_request->id] = $purchase_request->reference_no;
										}
									}
									
									echo form_dropdown('pr_reference', $pr_opts, (isset($id) ? $id: ''), 'id="pr_reference" class="form-control input-tip select" style="width:100%;" ');
									?>
								</div>
							</div>
                        <?php } if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "porbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="porbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'porbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
						
						<?php if($Settings->project == 1){ ?>
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("project", "project"); ?>
										<div class="input-group">
											<div class="no-project">
												<?php
												$pj[''] = '';
												if(isset($projects) && $projects){
													foreach ($projects as $project) {
														$pj[$project->id] = $project->name;
													}
												}
												echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
												?>
											</div>
											<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
												<a href="<?= site_url('system_settings/projects'); ?>" class="external" target="_blank">
													<i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
												</a>
											</div>
											<div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
												<a href="<?= site_url('system_settings/add_project'); ?>" class="external" data-toggle="modal" data-target="#myModal">
													<i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
												</a>
											</div>
										</div>
									</div>
								</div>
							<?php } else { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("project", "project"); ?>
										<div class="no-project">
											<?php
											$pj[''] = ''; 
											if(isset($user) && isset($projects) && $projects){
												$right_project = json_decode($user->project_ids);
												foreach ($projects as $project) {
													if(in_array($project->id, $right_project)){
														$pj[$project->id] = $project->name;
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
								<?= lang("warehouse", "porwarehouse"); ?>
								<?php
								foreach ($warehouses as $warehouse) {
									$wh[$warehouse->id] = $warehouse->name;
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="porwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("supplier", "porsupplier"); ?>
											<input type="hidden" name="supplier" value="" id="porsupplier" required class="form-control" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
											<input type="hidden" name="supplier_id" value="" id="supplier_id" required class="form-control">
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
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually" class="tip"
                                               title="<?= lang('add_product_manually') ?>"><i
                                                    class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a></div>
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
                                    <table id="quTable"
                                           class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                        <tr>
                                            <th class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                            <th class="col-md-1"><?= lang("unit_cost"); ?></th>
                                            <th class="col-md-1"><?= lang("quantity"); ?></th>
											<?php if($Settings->show_unit == 1) { ?>	
												<th class="col-md-1"><?= lang("unit"); ?></th>	
                                            <?php } ?>
                                            <?php
												if($Settings->cbm==1){
													echo '<th class="col-md-1">' . $this->lang->line("cbm") . '</th>';
												}
												
												if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
													echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
												}
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
						
						<?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_tax", "portax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['tax2']) ? $_POST['tax2'] : $Settings->default_tax_rate2), 'id="portax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("discount", "pordiscount"); ?>
                                <?php echo form_input('discount', '', 'class="form-control input-tip" id="pordiscount"'); ?>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="col-md-4 hidden">
                            <div class="form-group">
                                <?= lang("shipping", "porshipping"); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="porshipping"'); ?>

                            </div>
                        </div>
						
						<?php echo form_hidden("status","pending"); ?>
						
                        <div class="col-md-4 hidden">
                            <div class="form-group">
                                <?= lang("status", "porstatus"); ?>
                                <?php $st = array('pending' => lang('pending'), 'sent' => lang('sent'));
                                echo form_dropdown('status', $st, 'pending', 'class="form-control input-tip" id="porstatus"'); ?>

                            </div>
                        </div>
                       

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "pornote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="pornote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_purchase_order', $this->lang->line("submit"), 'id="add_purchase_order" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <?php echo form_submit('add_purchase_order_next', $this->lang->line("submit_and_next"), 'id="add_purchase_order_next" class="btn btn-info" style="padding: 6px 15px; margin:15px 0;"'); ?>
								<button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> : <span class="totals_val" id="titems">0</span></td>
                            <td><?= lang('total') ?> : <span class="totals_val" id="total">0.00</span></td>
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <td><?= lang('order_discount') ?> : <span class="totals_val pull" id="tds">0.00</span></td>
                            <?php } ?>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> : <span class="totals_val pull" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <!--<td><?= lang('shipping') ?> : <span class="totals_val pull" id="tship">0.00</span></td>-->
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
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['products-cost']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
					<div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('note') ?></label>
                        <div class="col-sm-8">
                          <textarea class="form-control skip" style="resize:none" id="pnote"></textarea>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('unit_cost'); ?></th>
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
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_cost') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('unit_cost'); ?></th>
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
<script type="text/javascript">
	$(function(){
		
		$('#pr_reference').on('change',function(){
			var pr_reference = $(this).val();
			location.replace(site.base_url+"purchase_orders/add/"+pr_reference);
		});
		
		$("#porbiller").change(biller); biller();
		function biller(){
			var biller = $("#porbiller").val();
			var project = "<?= (($inv && $inv->project_id > 0 ) ? $inv->project_id : 0) ?>";
			$.ajax({
				url : "<?= site_url("purchases/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
	})
</script>