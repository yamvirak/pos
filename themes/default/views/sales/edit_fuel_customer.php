<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
	var count = 1, an = 1, total = 0, edit_price = 0, truck = <?= ($this->config->item('customer_truck') ? 1 : 0) ?>;
	<?php if($Admin || $Owner || $GP['edit_price']){ ?>
		edit_price = 1;
	<?php } ?>
	$(document).ready(function () {
		<?php if ($fuel_customer) { ?>
			localStorage.setItem('fcdate', '<?= date($dateFormats[($Settings->date_with_time == 0 ? 'php_sdate' : 'php_ldate')], strtotime($fuel_customer->date))?>');
			localStorage.setItem('fcbiller', '<?=$fuel_customer->biller_id?>');
			localStorage.setItem('fcreference', '<?=$fuel_customer->reference?>');
			localStorage.setItem('fccustomer', '<?=$fuel_customer->customer_id?>');
			localStorage.setItem('fcwarehouse', '<?=$fuel_customer->warehouse_id?>');
			localStorage.setItem('fcsalesman', '<?=$fuel_customer->saleman_id?>');
			localStorage.setItem('fctime', '<?=$fuel_customer->time_id?>');
			localStorage.setItem('fcnote', '<?= str_replace(array("\r", "\n"), "", $this->cus->decode_html($fuel_customer->note)); ?>');
			localStorage.setItem('fcitems', JSON.stringify(<?=$fuel_customer_items?>));
        <?php } ?>
		
		
		<?php if ($Owner || $Admin || $GP['sales-fuel_sale-date']) { ?>
			$(document).on('change', '#fcdate', function (e) {
				localStorage.setItem('fcdate', $(this).val());
			});
			if (fcdate = localStorage.getItem('fcdate')) {
				$('#fcdate').val(fcdate);
			}
        <?php } ?>
		$(document).on('change', '#fcbiller', function (e) {
            localStorage.setItem('fcbiller', $(this).val());
        });
        if (fcbiller = localStorage.getItem('fcbiller')) {
            $('#fcbiller').val(fcbiller);
        }
		$(document).on('change', '#fcsalesman', function (e) {
            localStorage.setItem('fcsalesman', $(this).val());
        });
        if (fcsalesman = localStorage.getItem('fcsalesman')) {
			$('#fcsalesman').select2("val", fcsalesman);
        }
		$(document).on('change', '#fcwarehouse', function (e) {
            localStorage.setItem('fcwarehouse', $(this).val());
        });
        if (fcwarehouse = localStorage.getItem('fcwarehouse')) {
			$('#fcwarehouse').select2("val", fcwarehouse);
        }
        $(document).on('change', '#fctime', function (e) {
            localStorage.setItem('fctime', $(this).val());
        });
        if (fctime = localStorage.getItem('fctime')) {
			$('#fctime').select2("val", fctime);
        }
		ItemnTotals();
		$("#add_item").autocomplete({
            source: function (request, response) {
				if (!$('#fcsalesman').val() || !$('#fcwarehouse').val() || !$('#fccustomer').val()) {
					bootbox.alert('<?=lang('select_above');?>');
					$('#fcsalesman').focus();
					return false;
				}
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggesion_fuel_sale'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
						customer_id: $("#fccustomer").val(),
						warehouse_id: $("#fcwarehouse").val()
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
                    var row = add_item(ui.item);
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
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_fuel_customer'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo form_open_multipart("sales/edit_fuel_customer/".$id, $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['sales-fuel_sale-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "fcdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="fcdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
						
						<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference", "fcreference"); ?>
                                <?php echo form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : ''), 'class="form-control input-tip" id="fcreference"'); ?>
                            </div>
                        </div>
						
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "fcbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="fcbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'fcbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("warehouse", "fcwarehouse"); ?>
								<?php
								if($warehouses){
									foreach ($warehouses as $warehouse) {
										$wh[$warehouse->id] = $warehouse->name;
									}
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="fcwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-4">
                                        <div class="form-group" style="margin-bottom: 13px;">
                                            <?= lang("customer", "fccustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="fccustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
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
                                                        <i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
									<?php if ($this->config->item('saleman')==true) { ?>
										<div class="col-md-4">
											<div class="form-group">
											<?= lang("saleman", "fcsalesman"); ?>
											<?php 
												$opsalemans[""] = lang('select').' '.lang('saleman');
												foreach($salemans as $saleman){
													$opsalemans[$saleman->id] = $saleman->first_name .' '.$saleman->last_name;
												}
											?>
											<?= form_dropdown('saleman_id', $opsalemans, (isset($_GET['saleman_id'])?$_GET['saleman_id']:0), ' id="fcsalesman" class="form-control" required="required"'); ?>
											</div>
										</div>
									<?php } ?>
									
									<div class="col-md-4 hidden">
										<div class="form-group">
											<?= lang("time", "fctime"); ?>
											<?php
											$optt[""] = "";
											if($times){
												foreach ($times as $time) {
													$optt[$time->id] = $time->open_time.' - '.$time->close_time;
												}
											}
											echo form_dropdown('time_id', $optt, (isset($_POST['time_id']) ? $_POST['time_id'] : ""), 'id="fctime" required data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("time") . '" class="form-control input-tip select" style="width:100%;"');
											?>
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
										<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_tank_to_order") . '"'); ?>
									</div>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>
				
                        <div class="clearfix"></div>
						<div class="col-sm-12">
							<div class="control-group table-group">
                                <label class="table-label"><?= lang("nozzle_no"); ?> *</label>
								<div class="controls table-controls">
									<table id="glTable" class="table table-bordered table-striped" style="margin-top:10px;">
										<thead>
											<th style="width:200px;"><?= lang("tank") ?></th>
											<th style="width:150px;"><?= lang("nozzle_no") ?></th>
											<?php if($this->config->item('customer_truck')){ ?>
												<th style="width:150px;"><?= lang("truck") ?></th>
											<?php } ?>
											<th style="width:100px;"><?= lang("unit_price") ?></th>
											<th style="width:100px;"><?= lang("quantity") ?></th>
											<th style="width:100px;"><?= lang("subtotal") ?></th>
											<th style="width:30px;">
												<i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
											</th>
										</thead>
										<tbody></tbody>
										<tfoot></tfoot>
									</table>
								</div>
							</div>
						</div>
						<input type="hidden" name="total_items" value="" id="total_items" required="required"/>
                        <div class="row" id="bt">
                            <div class="col-md-12">
								<div class="col-md-6">
									<div class="form-group">
                                        <?= lang("note", "fcnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="fcnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
								</div>	
								<div class="col-md-6">
									<div class="form-group">
										<?= lang("document", "document") ?>
										<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false"
											   data-show-preview="false" class="form-control file">
									</div>
								</div>
                            </div>
                        </div>
						<div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('edit_fuel_customer', $this->lang->line("submit"), 'id="edit_fuel_customer" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
								<button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
							</div>
						</div>
					</div>
                </div>
				<div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> : <span class="totals_val" id="titems">0</span></td>
							<td class="text-right"><?= lang('total') ?> : <span class="totals_val" id="total">0.00</span></td>
                        </tr>
                    </table>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
