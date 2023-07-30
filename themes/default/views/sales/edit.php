<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    isEditSale = true;
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    //var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    var allow_remove = true;
	<?php if((!$Owner && !$Admin) && !empty($inv->rental_id) && $inv->rental_id>0){ ?>
		var allow_remove = false;
	<?php } ?>
	$(document).ready(function () {
        <?php if ($inv) { ?>
        localStorage.setItem('sldate', '<?= $this->cus->hrld($inv->date) ?>');
        localStorage.setItem('slcustomer', '<?= $inv->customer_id ?>');
        localStorage.setItem('slbiller', '<?= $inv->biller_id ?>');
		localStorage.setItem('slsaleman', '<?= $inv->saleman_id ?>');
		localStorage.setItem('slagency', '<?= $inv->agency_id ?>');
		localStorage.setItem('slcommission', '<?= $inv->saleman_commission ?>');
        localStorage.setItem('slref', '<?= $inv->reference_no ?>');
        localStorage.setItem('slwarehouse', '<?= $inv->warehouse_id ?>');
        localStorage.setItem('slsale_status', '<?= $inv->sale_status ?>');
        localStorage.setItem('slpayment_status', '<?= $inv->payment_status ?>');
        localStorage.setItem('slpayment_term', '<?= $inv->payment_term ?>');
		localStorage.setItem('slnote', '<?= str_replace(array("\r", "\n", "'"), "", $this->cus->decode_html($inv->note)); ?>');
        localStorage.setItem('slinnote', '<?= str_replace(array("\r", "\n", "'"), "", $this->cus->decode_html($inv->staff_note)); ?>');
        localStorage.setItem('sldiscount', '<?= $inv->order_discount_id ?>');
        localStorage.setItem('sltax2', '<?= $inv->order_tax_id ?>');
        localStorage.setItem('slshipping', '<?= $inv->shipping ?>');
        localStorage.setItem('slitems', JSON.stringify(<?= $inv_items; ?>));
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
        $(document).on('change', '#sldate', function (e) {
            localStorage.setItem('sldate', $(this).val());
        });
        if (sldate = localStorage.getItem('sldate')) {
            $('#sldate').val(sldate);
        }
        <?php } ?>
        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val(),
						<?php if($this->config->item('room_rent') && isset($inv->rental_id) && $inv->rental_id){ ?>
							rental_id : "<?=$inv->rental_id?>",
						<?php } ?>
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
								warehouse_id: $("#slwarehouse").val(),
								customer_id: $("#slcustomer").val(),
							},
							success: function (result) {
								$.each( result, function(key, value) {
									var row = add_invoice_item(value);
									if (row)
										$(this).val('');
                                    $('.promotion').hide();
								});
							}
						});
						$(this).val('');
					}else {
						var row = add_invoice_item(ui.item);
						if (row)
							$(this).val('');
                        $('.promotion').hide();
					}
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(window).bind('beforeunload', function (e) {
            localStorage.setItem('remove_slls', true);
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function (e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_sale').click(function () {
            $(window).unbind('beforeunload');            
        });
        $('.promotion').hide();
    });
</script>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_sale'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-so-form');
                echo form_open_multipart("sales/edit/" . $inv->id, $attrib);
				echo form_hidden('delivery_id', $inv->delivery_id);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "sldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->cus->hrld($inv->date)), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "slref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="slref"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "slbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'slbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
						<?php if($Settings->project == 1){ ?>
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("project", "project"); ?>
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
												if($right_project){
													foreach ($projects as $project) {
														if(in_array($project->id, $right_project)){
															$pj[$project->id] = $project->name;
														}
													}
												}
												
											}
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $inv->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
										</div>
									</div>
								</div>
							<?php } ?>
						<?php } ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("warehouse", "slwarehouse"); ?>
								<?php
								foreach ($warehouses as $warehouse) {
									$wh[$warehouse->id] = $warehouse->name;
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $inv->warehouse_id), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
						<?php 
							$sale_currencies = false;
							if(json_decode($inv->currencies)){
								foreach(json_decode($inv->currencies) as $sale_currency){
									$sale_currencies[$sale_currency->currency] = $sale_currency;
								}
							}
							foreach($currencies as $currency){ ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("exchange_rate"." (".$currency->code.")", "exchange_rate"); ?>
									<?php echo form_input('exchange_rate_'.$currency->code, (isset($sale_currencies[$currency->code]) ? $sale_currencies[$currency->code]->rate : $currency->rate), 'class="form-control input-tip exchange_rate"'); ?>
								</div>
							</div>	
						<?php } ?>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-4">
                                        <div class="form-group" style="margin-bottom: 13px;">
                                            <?= lang("customer", "slcustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                    echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
											<div class="form-group">
											<?= lang("move_to_room", "move_to_room"); ?>
											<?php 
												$opsRooms[""] = lang('select').' '.lang('move_to_room');
												foreach($rooms_checked_in as $room){
													$opsRooms[$room->id] = $room->room_name;
												}
											?>
											<?= form_dropdown('rental_service_id', $opsRooms, $inv->rental_service_id, ' id="rental_service_id" class="form-control" required="required"'); ?>
											</div>
									</div>

									<?php if ($this->config->item('saleman')==true) { 
										if($Owner || $Admin || $GP['sales-assign_sales']){
											$hide_saleman = '';
										}else{
											$hide_saleman = ' style="display:none !important;"';
										}
									?>		
										<div class="col-md-4" <?= $hide_saleman ?>>
											<div class="form-group">
											<?= lang("saleman", "saleman"); ?>
											<?php 
												$opsalemans[""] = lang('select').' '.lang('saleman');
												foreach($salemans as $saleman){
													$opsalemans[$saleman->id] = $saleman->first_name .' '.$saleman->last_name;
												}
											?>
											<?= form_dropdown('saleman_id', $opsalemans, $inv->saleman_id, ' id="saleman_id" class="form-control" required="required"'); ?>
											</div>
										</div>
										<?php if ($this->config->item('saleman_commission')==true){ ?>
											<div class="col-md-4" <?= $hide_saleman ?>>
												<div class="form-group">
												<?= lang("commission", "commission"); ?>
												<?php echo form_input('commission', $inv->saleman_commission, 'class="form-control input-tip" id="commission"'); ?>
												</div>
											</div>
										<?php } ?>
									<?php } ?>
									<?php if($this->config->item("agency")){ 
                                            $agency_details = '';
                                            $agency_commission = json_decode($inv->agency_commission);
                                            if($agency_commission){
                                                $agency_details .= "<strong> ( <small>".lang('current')." : ";
                                                foreach($agency_commission as $agency_com){
                                                    $agency_details .= $agency_com."% ";
                                                }
                                                $agency_details .= "</small> ) </strong>";
                                            }
                                        ?>
										<div class="col-md-4">
											<div class="form-group">
											<?= lang("agency", "agency"); ?> <?=$agency_details?>
                                            <?php
												$opagencies[""] = array();
												if(isset($agencies) && $agencies){
													foreach($agencies as $agency){
														$opagencies[$agency->id] = $agency->first_name .' '.$agency->last_name .' '.$agency->agency_commission."%";
													}
												}
											?>
											<?= form_dropdown('agency_id[]', $opagencies, json_decode($inv->agency_id), ' id="agency_id" multiple class="form-control" required="required"'); ?>
											</div>
										</div>
									<?php } ?>
									<?php if($this->config->item("room_rent") && (isset($inv->rental_id) && $inv->rental_id > 0)){ ?>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("from_date", "from_date"); ?>
												<div class="input-group">
													<?= form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] : $this->cus->hrld($inv->from_date)), 'class="form-control datetime" id="from_date" autocomplete="off" required="required"'); ?>
													<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
														<i class="fa fa-calendar" aria-hidden="true"></i>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("to_date", "to_date"); ?>
												<div class="input-group">
													<?= form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] : $this->cus->hrld($inv->to_date)), 'class="form-control datetime" id="to_date" autocomplete="off" '); ?>
													<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
														<i class="fa fa-calendar" aria-hidden="true"></i>
													</div>
												</div>
											</div>
										</div>
									<?php } ?>
									<?php if($receivable_account){ $sale_payment = $this->sales_model->getPaymentBySaleID($inv->id); ?>
                                        <?php if (!$sale_payment) { ?>
                                            <div class="col-md-4">    
                                                <div class="form-group">
                                                    <?= lang("receivable_account", "receivable_account"); ?>
                                                    <select name="receivable_account" id="receivable_account" class="form-control receivable_account" style="width:100%">
                                                        <?= $receivable_account ?>
                                                    </select>  
                                                </div>
                                            </div> 
                                        <?php }else{ 
                                            echo form_hidden('receivable_account', $inv->ar_account);
                                        } ?>
                                    <?php } if($Settings->car_operation == 1){ ?>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("vehicle_model", "vehicle_model"); ?>
												<?php echo form_input('vehicle_model', (isset($_POST['vehicle_model']) ? $_POST['vehicle_model'] : $inv->vehicle_model), 'class="form-control input-tip" id="vehicle_model"'); ?>
											</div>
										</div>
										
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("vehicle_kilometers", "vehicle_kilometers"); ?>
												<?php echo form_input('vehicle_kilometers', (isset($_POST['vehicle_kilometers']) ? $_POST['vehicle_kilometers'] : $inv->vehicle_kilometers), 'class="form-control input-tip" id="vehicle_kilometers"'); ?>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("vehicle_vin_no", "vehicle_vin_no"); ?>
												<?php echo form_input('vehicle_vin_no', (isset($_POST['vehicle_vin_no']) ? $_POST['vehicle_vin_no'] : $inv->vehicle_vin_no), 'class="form-control input-tip" id="vehicle_vin_no"'); ?>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("vehicle_plate", "vehicle_plate"); ?>
												<?php echo form_input('vehicle_plate', (isset($_POST['vehicle_plate']) ? $_POST['vehicle_plate'] : $inv->vehicle_plate), 'class="form-control input-tip" id="vehicle_plate"'); ?>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("job_number", "job_number"); ?>
												<?php echo form_input('job_number', (isset($_POST['job_number']) ? $_POST['job_number'] : $inv->job_number), 'class="form-control input-tip" id="job_number"'); ?>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("mechanic", "mechanic"); ?>
												<?php echo form_input('mechanic', (isset($_POST['mechanic']) ? $_POST['mechanic'] : $inv->mechanic), 'class="form-control input-tip" id="mechanic"'); ?>
											</div>
										</div>
									<?php } ?>
                                </div>
                            </div>
                        </div>
						<div class="clearfix"></div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually">
                                                <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                            </a>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="slTable"
                                           class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
										<?php if ($Settings->qty_operation) {
												$head_row = 'rowspan="2"';
											}else{
												$head_row = '';
											}
										?>
                                        <tr>
                                            <?php if($Settings->search_by_category==1){ ?>
												<th <?= $head_row ?> class="col-md-4"><?= lang('category') . ' (' . lang('product') .')'; ?></th>
											<?php }else{?>
												<th <?= $head_row ?> class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>	
											<?php } if ($Settings->show_qoh == 1) { ?>	
												<th <?= $head_row ?> class="col-md-1"><?= lang("qoh"); ?></th>
											<?php } if($this->config->item('saleman_commission') && $Settings->product_commission == 1){
												echo '<th '.$head_row.' class="col-md-1">' . lang("saleman") . '</th>';
											}
											if ($Settings->product_expiry) {
                                                echo '<th '.$head_row.' class="col-md-1">' . lang("expiry_date") . '</th>';
                                            }
                                            if ($Settings->product_serial) {
                                                echo '<th '.$head_row.' class="col-md-1">' . lang("serial_no") . '</th>';
                                            }
                                            ?>
                                            <th <?= $head_row ?> class="col-md-1"><?= lang("unit_price"); ?></th>
											<?php if ($Settings->qty_operation) { ?>											
												<th class="col-md-4" colspan="4"><?= lang("quantity_operation"); ?></th>
												<th <?= $head_row ?> class="col-md-1"><?= lang("total_quantity"); ?></th>
											<?php }else{ ?>
												<th <?= $head_row ?> class="col-md-1"><?= lang("quantity"); ?></th>
                                            <?php } if ($Settings->show_unit == 1) { ?>	
												<th <?= $head_row ?> class="col-md-1"><?= lang("unit"); ?></th>
                                            <?php } if ($Settings->foc == 1) {
												echo '<th '.$head_row.' class="col-md-1">' . $this->lang->line("foc") . '</th>';
                                            }if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th '.$head_row.' class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                            }
                                            ?>
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th '.$head_row.' class="col-md-1">' . lang("product_tax") . '</th>';
                                            }
                                            ?>
                                            <th <?= $head_row ?> ><?= lang("subtotal"); ?> (<span
                                                    class="currency"><?= $default_currency->code ?></span>)
                                            </th <?= $head_row ?> >
                                            <th <?= $head_row ?> style="width: 30px !important; text-align: center;"><i
                                                    class="fa fa-trash-o"
                                                    style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
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

                        <?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_tax", "sltax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang("select") . ' ' . lang("order_tax") . '" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (($Owner || $Admin || $this->session->userdata('allow_discount')) || $inv->order_discount_id) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("order_discount", "sldiscount"); ?>
                                <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount" '.(($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"')); ?>
                            </div>
                        </div>
                        <?php } ?>
                    
                        <?php if(!$inv->repair_id){ ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("shipping", "slshipping"); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

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

                        <div style="display:none !important" class="col-sm-4">
                            <div class="form-group">
                                <?= lang("sale_status", "slsale_status"); ?>
                                <?php $sst = array('pending' => lang('pending'), 'completed' => lang('completed'));
                                echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_term", "slpayment_term"); ?>
                                <?php 
								$pt[''] = '';
								foreach ($paymentterms as $paymentterm) {
									$pt[$paymentterm->id] = $paymentterm->description;
								}
                                echo form_dropdown('payment_term', $pt,(isset($_POST['payment_term']) ? $_POST['payment_term'] : $inv->payment_term), 'class="form-control input-tip" id="slpayment_term"'); ?>

                            </div>
                        </div>
						<?php if($inv->delivery_status == 'pending' && !($inv->repair_id)){ ?>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("stock_deduction", "stock_deduction"); ?>
									<?php $sd = array(1 => lang('completed'), 0 => lang('pending'));
									echo form_dropdown('stock_deduction', $sd, $inv->stock_deduction, 'class="form-control input-tip" id="stock_deduction"'); ?>

								</div>
							</div>
						<?php } else { 
							echo form_hidden('stock_deduction', $inv->stock_deduction);	
						} ?>
						
                                                
						<div class="clearfix"></div>						
						
						<?= form_hidden('payment_status', $inv->payment_status); ?>						

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("sale_note", "slnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("staff_note", "slinnote"); ?>
                                        <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ""), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
																
                            </div>
							
                        </div>
                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('edit_sale', lang("submit"), 'id="edit_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
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
			
				<?php if($suspend_option != 0){ ?>
					<div class="form-group">
						<?= lang('tags', 'tags'); ?>
						<?php
						$tags1 = array(0 => lang('no'));
						foreach($tags as $tag){
							$tags1[$tag->name] = $tag->name;
						}
						?>
						<?= form_dropdown('tags', $tags1, '', 'class="form-control" id="tags" style="width:100%;"'); ?>
					</div>
					<script type="text/javascript">
						$(function(){
							$("#tags").on("change",function(){
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
				
                <input type="hidden" id="irow_id" value=""/>
				
            </div>
			
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
			
        </div>
    </div>
</div>

<div class="modal" id="comboModal" tabindex="-1" role="dialog" aria-labelledby="comboModalLabel" aria-hidden="true" >
    <div class="modal-dialog" style="width:50%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="comboModalLabel"></h4>
            </div>
            <div class="modal-body" style="margin-top:-15px !important;">
				<label class="table-label"><?= lang("combo_products"); ?></label>
				<table id="comboProduct" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
					<thead>
						<tr>
							<th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
							<?php if ($Settings->qty_operation) { ?>
								<th><?= lang('width') ?></th>
								<th><?= lang('height') ?></th>
							<?php } ?>
							<th><?= lang('quantity') ?></th>
							<th><?= lang('price') ?></th>
							<th width="3%">
								<a id="add_comboProduct" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
							</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
            </div>
			
			
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editCombo"><?=lang('submit')?></button>
            </div>
			
        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
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
                    <?php if ($Settings->product_serial) { ?>
                       <div class="form-group hidden">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial">
                            </div>
                        </div>
						
						<div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <div id="pserials-div"></div>
								<input type="hidden" class="form-control" id="pserial">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="pservice_types" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <div id="pservice_types-div"></div>
								<input type="hidden" class="form-control" id="pservice_types">
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
					<?php if(in_array('bom',$this->config->item('product_types'))) { ?>
                        <div class="form-group">
                            <label for="pbom_type" class="col-sm-4 control-label"><?= lang('bom_type') ?></label>
                            <div class="col-sm-8">
                                <div id="pbom_type-div"></div>
                            </div>
                        </div>    
                            
					<?php } if ($Settings->attributes == 1) { ?>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php } if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount" <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
                    <?php } ?>
					
					<?php if($this->config->item('product_currency')==true) { ?>
                        <div class="form-group">
                            <label for="pproduct_currency" class="col-sm-4 control-label"><?= lang('product_currency') ?></label>
                            <div class="col-sm-8">
                                <div id="pproduct_currency-div"></div>
                            </div>
                        </div>
					<?php } ?>
					
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
					
					<?php if ($Settings->product_additional){ ?>
						<div class="form-group">
							<label for="paditional" class="col-sm-4 control-label"><?= lang('product_additional') ?></label>
							<div class="col-sm-8">
								<div id="paditional-div"></div>
							</div>
						</div>
                    <?php } if ($Settings->product_formulation){ ?>
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
					
					<?php if($this->config->item("room_rent")){ ?>
						<div id="electricity">
							<div class="panel panel-default">
								<div class="panel-heading"><?= lang('electricity_or_water'); ?></div>
								<div class="panel-body">
									<div class="form-group">
										<label for="old_number" class="col-sm-4 control-label"><?= lang('old_number') ?></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" id="old_number" <?=(!$Admin && !$Owner?'readonly':'')?>>
										</div>
									</div>
									<div class="form-group">
										<label for="new_number" class="col-sm-4 control-label"><?= lang('new_number') ?></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" id="new_number">
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					
					<div class="panel panel-default">
                        <div class="panel-heading"> <?= lang('calculate_unit_price'); ?></div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="pprice" class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="psubtotal">
                                        <div class="input-group-addon" style="padding: 2px 8px;">
                                            <a href="#" id="calculate_unit_price" class="tip" title="<?= lang('calculate_unit_price'); ?>">
                                                <i class="fa fa-calculator"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					
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
                            class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
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
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="mserial" class="col-sm-4 control-label"><?= lang('product_serial') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mserial">
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="mdiscount" class="col-sm-4 control-label">
                                <?= lang('product_discount') ?>
                            </label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount" <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
					<div class="form-group">
                        <label for="mcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcost">
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


<script type="text/javascript">
	$(function(){
		
		$(".combo_product:not(.ui-autocomplete-input)").live("focus", function (event) {
			$(this).autocomplete({
				source: '<?= site_url('products/suggestions'); ?>',
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
						var parent = $(this).parent().parent();
						parent.find(".combo_product_id").val(ui.item.id);
						parent.find(".combo_name").val(ui.item.name);
						parent.find(".combo_code").val(ui.item.code);
						parent.find(".combo_price").val(formatDecimal(ui.item.price));
						parent.find(".combo_qty").val(formatDecimal(1));
						if (site.settings.qty_operation == 1) {
							parent.find(".combo_width").val(formatDecimal(1));
							parent.find(".combo_height").val(formatDecimal(1));
						}					
						$(this).val(ui.item.label);
					} else {
						bootbox.alert('<?= lang('no_match_found') ?>');
					}
				}
			});
		});
		
		$('#saleman_id').change(function (e) {
			var saleman_id = $(this).val();
			$.ajax({
				url : site.base_url + "sales/getUser",
				dataType : "JSON",
				type : "GET",
				data : { saleman_id : saleman_id },
				success : function(data){
					$('#commission').val(data.saleman_commission);
				}
			});

		});
		$('#slcustomer').on("select2-selecting", function(e) { 		   
		   var customer = e.val;
		  
		   $.ajax({
				  url : site.base_url + "sales/get_company",
				  dataType : "JSON",
				  type : "GET",
				  data : { 
						customer : customer,
						sale_id : "<?=$inv->id?>"
 				  },
				  success : function(data){
					if(data.saleman_id > 0){
						$("#saleman_id").select2("val",data.saleman_id);	
						$("#commission").val(data.saleman_commission);					
					}
					
					<?php if($Settings->installment==1){ ?>
						var installment = JSON.parse(data.installment);
						if(installment.id > 0){
							var reference_no = installment.reference_no;
							bootbox.confirm({
								message: "<?= lang("customer_has_installment") ?>",
								buttons: {
									confirm: {
										label: 'Yes',
										className: 'btn-success'
									},
									cancel: {
										label: 'No',
										className: 'btn-danger'
									}
								},
								callback: function (result) {
									if(result){
										$(this).select2('val',customer);
									}else{
										$('#slcustomer').select2('val','<?=$inv->customer_id?>');
									}
								}
							});
						}
					<?php } ?>
				}
		   });
		   $.ajax({
				  url : site.base_url + "sales/get_credit",
				  dataType : "JSON",
				  type : "GET",
				  data : { customer : customer },
				  success : function(balance){
					 if(balance > 0){
						  bootbox.alert("Customer have over credit limit");
						  $("#slcustomer").select2("val","");	
						  localStorage.removeItem('slcustomer');
						  return false;
					 }			
				  }
		   });
		});
		
		
		$("#slbiller").change(biller); biller();
		function biller(){
			var biller = $("#slbiller").val();
			var project = "<?= $inv->project_id ?>";
			$.ajax({
				url : "<?= site_url("sales/get_project") ?>",
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


        setTimeout(
          function() 
          {
            $(".promotion").hide();
            isEditSale = true;
          }, 1000);

		<?php if((!$Owner && !$Admin) && !empty($inv->rental_id) && $inv->rental_id>0){ ?>
			$("#add_item, #to_date").prop("readonly","readonly");
			$("#to_date").prop("class","form-control");
			$("#addManually, #sellGiftCard").parent().hide();
		<?php } ?>
	});
</script>
