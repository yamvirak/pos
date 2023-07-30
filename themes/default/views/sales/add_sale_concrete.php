<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_sale_concrete'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo form_open_multipart("sales/add_sale_concrete", $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<?php if ($Owner || $Admin || $GP['sales-date']) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("date", "csdate"); ?>
									<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="csdate" required="required"'); ?>
								</div>
							</div>
						<?php } ?>
						<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "csref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="csref"'); ?>
                            </div>
                        </div>
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "csbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="csbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'csbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
						<?php if($Settings->project == 1){ ?>
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group" style="margin-bottom: 13px;">
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
												echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
												?>
											</div>
											<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
												<a href="<?= site_url('system_settings/projects'); ?>" class="external" target="_blank">
													<i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
												</a>
											</div>						
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
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
										</div>
									</div>
								</div>
							<?php } ?>
						<?php } ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("warehouse", "cswarehouse"); ?>
								<?php
								foreach ($warehouses as $warehouse) {
									$wh[$warehouse->id] = $warehouse->name;
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="cswarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
						<div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
										<div class="form-group">
											<?= lang("customer", "cscustomer"); ?>
											<?php
											$cs[''] = lang("select")." ".lang("customer");
											foreach ($customers as $customer) {
												$cs[$customer->id] = $customer->company;
											}
											echo form_dropdown('customer', $cs, (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="cscustomer" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" style="width:100%;" ');
											?>
										</div>
                                    </div>
									<div class="col-md-4">
										<?= lang("location", "cslocation"); ?>
										<div class="location_box form-group">
											<?php
												$gp[""] = lang("select")." ".lang("location");
												echo form_dropdown('location', $gp, (isset($_POST['location']) ? $_POST['location'] : ''), 'id="cslocation" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("location") . '"  class="form-control input-tip select" style="width:100%;"  required="required"');
											?>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("from_date", "csfrom_date"); ?>
											<?php echo form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] : ""), 'class="form-control input-tip datetime" id="csfrom_date" required="required"'); ?>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("to_date", "csto_date"); ?>
											<?php echo form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] : ""), 'class="form-control input-tip datetime" id="csto_date" required="required"'); ?>
										</div>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="conTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
										<thead>
											<tr>
												<th><?= lang("date") ?></th>
												<th><?= lang("reference") ?></th>
												<th><?= lang("total") ?></th>
												<th><?= lang("truck_charge") ?></th>
												<th><?= lang("pump_charge") ?></th>
												<th><?= lang("subtotal") ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</thead>
                                        <tbody id="dataDel"></tbody>
                                        <tfoot>
											<tr>
												<th class="text-right" colspan="5"><?= lang("total") ?></th>
												<th class="text-right" id="total"></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
						<?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
							<div class="col-md-2">
								<div class="form-group">
									<?= lang("discount", "csdiscount"); ?>
									<?php echo form_input('order_discount', '', 'class="form-control input-tip" id="csdiscount"'); ?>
								</div>
							</div>
						<?php } if ($Settings->tax2) { ?>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <?= lang("order_tax", "cstax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['tax2']) ? $_POST['tax2'] : $Settings->default_tax_rate2), 'id="cstax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                         <?php } ?>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						<div class="col-sm-2">
                            <div class="form-group">
                                <?= lang("payment_term", "cspayment_term"); ?>
                                <?php 
								$pt[''] = '';
								foreach ($paymentterms as $paymentterm) {
									$pt[$paymentterm->id] = $paymentterm->description;
								}
                                echo form_dropdown('payment_term', $pt, (isset($_POST['payment_term']) ? $_POST['payment_term'] : $Settings->default_payment_term), 'class="form-control input-tip" id="cspayment_term"'); ?>

                            </div>
                        </div>
						<div class="clearfix"></div>
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <?= lang("sale_note", "csnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="csnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
								<div class="col-sm-6">
                                    <div class="form-group">
                                        <?= lang("staff_note", "cssnote"); ?>
                                        <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ""), 'class="form-control" id="cssnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('add_sale_concrete', $this->lang->line("submit"), 'id="add_sale_concrete" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
							</div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
		<div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
			<table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
				<tr class="warning">
					<td><?= lang('items') ?> : <span class="totals_val pull" id="titems">0</span></td>
					<td><?= lang('total') ?> : <span class="totals_val pull" id="ttotal">0.00</span></td>
					<?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
						<td><?= lang('order_discount') ?> : <span class="totals_val pull" id="tds">0.00</span></td>
					<?php } if ($Settings->tax2) { ?>
						<td><?= lang('order_tax') ?> : <span class="totals_val pull" id="ttax2">0.00</span></td>
					<?php } ?>
					<td><?= lang('grand_total') ?> : <span class="totals_val pull" id="gtotal">0.00</span></td>
				</tr>
			</table>
		</div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		<?php if ($Owner || $Admin || $GP['sales-date']) { ?>
			$("#csdate").datetimepicker({
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
		<?php } ?>
		$('#cscustomer').on("select2-selecting", function(e) {		   
		   var customer = e.val; $customer = e.val;
			$.ajax({
				url : site.base_url + "sales/get_customer_locations",
				dataType : "JSON",
				type : "GET",
				data : { customer : customer},
				success : function(data){
					var location_sel = "<select class='form-control' id='cslocation' name='location'><option value=''><?= lang('select').' '.lang('location') ?></option>";
					if (data.locations != false) {
						$.each(data.locations, function () {
							location_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
					}
					location_sel += "</select>"
					$(".location_box").html(location_sel);
					$('select').select2();	
				}
			});
		   
		});
		$(document).on("change", "#csbiller, #project, #cswarehouse, #cscustomer, #cslocation, #csfrom_date, #csto_date", function () {	
			getConSales();
		});
		function getConSales(){
			var biller_id = $("#csbiller").val();
			var project_id = $("#project").val();
			var warehouse_id = $("#cswarehouse").val();
			var customer_id = $("#cscustomer").val();
			var location_id = $("#cslocation").val();
			var from_date = $("#csfrom_date").val();
			var to_date = $("#csto_date").val();
			if(biller_id && warehouse_id && customer_id && location_id && from_date && to_date){
				$.ajax({
					type: "get", 
					async: true,
					url: site.base_url + "sales/get_concrete_sales/",
					data : { 
							biller_id : biller_id,
							project_id : project_id,
							warehouse_id : warehouse_id,
							customer_id : customer_id,
							location_id : location_id,
							from_date : from_date,
							to_date : to_date
					},
					dataType: "json",
					success: function (data) {
						var dataDel = "";
						if (data != false) {
							$.each(data, function () {
								dataDel += "<tr class='con_sale_link' id='"+this.id+"'>";
									dataDel += "<td class='text-center'><input class='con_sale_id' name='con_sale_id[]' value='"+this.id+"' type='hidden'/><input class='con_date' name='con_date[]' value='"+this.date+"' type='hidden'/>"+this.date+"</td>";
									dataDel += "<td><input class='con_reference' name='con_reference[]' value='"+this.reference_no+"' type='hidden'/>"+this.reference_no+"</td>";
									dataDel += "<td class='text-right'><input class='con_total' name='con_total[]' value='"+this.total+"' type='hidden'/>"+formatMoney(this.total)+"</td>";
									dataDel += "<td class='text-right'><input class='con_truck_charge' name='con_truck_charge[]' value='"+this.truck_charge+"' type='hidden'/>"+formatMoney(this.truck_charge)+"</td>";
									dataDel += "<td class='text-right'><input class='con_pump_charge' name='con_pump_charge[]' value='"+this.pump_charge+"' type='hidden'/>"+formatMoney(this.pump_charge)+"</td>";
									dataDel += "<td class='text-right'><input class='con_subtotal' name='con_subtotal[]' value='"+this.grand_total+"' type='hidden'/>"+formatMoney(this.grand_total)+"</td>";
									dataDel += "<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";
								dataDel += "</tr>";
							});
						}
						$("#dataDel").html(dataDel);
						loadItems();
					}
				});
			}else{
				$("#dataDel").html("");
				loadItems();
			}
		}
		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
			loadItems();
		});
		$("#csbiller").change(biller); biller();
		function biller(){
			var biller = $("#csbiller").val();
			var project = 0;
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
			});
			getConSales()
		}

		$('#cstax2').change(function () {
			loadItems();
		});
		
		var old_csdiscount;
		$('#csdiscount').focus(function () {
			old_csdiscount = $(this).val();
		}).change(function () {
			var new_discount = $(this).val() ? $(this).val() : '0';
			if (is_valid_discount(new_discount)) {
				loadItems();
				return;
			} else {
				$(this).val(old_csdiscount);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});


		function loadItems(){
			var tax_rates = <?= json_encode($tax_rates) ?>;
			var total = 0;
			var item_amount = 0;
			var item = 0;
			var order_discount = 0;
			var invoice_tax = 0;
			var csdiscount = $("#csdiscount").val();
			var cstax2 = $("#cstax2").val();
			$(".con_sale_id").each(function(){
				var row = $(this).closest('tr');
				var subtotal = row.find(".con_subtotal").val() - 0;
				total += subtotal;
				item++;
			});
			if (csdiscount) {
				var ds = csdiscount;
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
			if (cstax2) {
				$.each(tax_rates, function () {
					if (this.id == cstax2) {
						if (this.type == 2) {
							invoice_tax = formatDecimalRaw(this.rate);
						}
						if (this.type == 1) {
							invoice_tax = formatDecimalRaw((((total - order_discount) * this.rate) / 100), 4);
						}
					}
				});
			}
			var gtotal = parseFloat((total + invoice_tax) - order_discount);
			$("#total").html(formatMoney(total));
			$('#ttotal').text(formatMoney(total));
			$('#titems').text(item);
			$('#tds').text(formatMoney(order_discount));
			if (site.settings.tax2 != 0) {
				$('#ttax2').text(formatMoney(invoice_tax));
			}
			$('#gtotal').text(formatMoney(gtotal));
		}

	});
</script>