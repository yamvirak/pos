<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_salesman_commission'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo form_open_multipart("sales/add_salesman_commission", $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<?php if ($Owner || $Admin || $GP['sales-salesman_commission-date']) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("date", "date"); ?>
									<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
								</div>
							</div>
						<?php } ?>
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'biller',
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
						<div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("commission_type", "commission_type"); ?>
											<?php
												$tpopts["Normal"] = lang("normal");
												$tpopts["Target"] = lang("target");
												echo form_dropdown('commission_type', $tpopts, (isset($_POST['commission_type']) ? $_POST['commission_type'] : ''), 'id="commission_type" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("commission_type") . '" required="required" class="form-control input-tip select" style="width:100%;"');
											?>
										</div>
									</div>
									
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("salesman_group", "salesman_group"); ?>
											<?php
												$smgopts[""] = lang("select")." ".lang("salesman_group");
												if($salesman_groups){
													foreach($salesman_groups as $salesman_group){
														$smgopts[$salesman_group->id] = $salesman_group->name;
													}
												}
												echo form_dropdown('salesman_group', $smgopts, (isset($_POST['salesman_group']) ? $_POST['salesman_group'] : ''), 'id="salesman_group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("salesman_group") . '" required="required" class="form-control input-tip select" style="width:100%;"');
											?>
										</div>
									</div>
									<div class="col-md-4">
										<?= lang("salesman", "salesman"); ?>
										<div class="salesman_box form-group">
											<?php
												$smopts[""] = lang("select")." ".lang("salesman");
												echo form_dropdown('salesman', $smopts, (isset($_POST['salesman']) ? $_POST['salesman'] : ''), 'id="salesman" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("salesman") . '"  class="form-control input-tip select" style="width:100%;"');
											?>
										</div>
									</div>

									<div class="col-md-4">
										<div class="form-group">
											<?= lang("from_date", "from_date"); ?>
											<?php echo form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] : ''), 'class="form-control input-tip date" id="from_date"'); ?>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("to_date", "to_date"); ?>
											<?php echo form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] : ''), 'class="form-control input-tip date" id="to_date"'); ?>
										</div>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-md-12 box_normal">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="conTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
										<thead>
											<tr>
												<th><?= lang("salesman") ?></th>
												<th><?= lang("reference") ?></th>
												<th><?= lang("grand_total") ?></th>
												<th><?= lang("amount") ?></th>
												<th><?= lang("rate") ?></th>
												<th><?= lang("commission") ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</thead>
                                        <tbody id="dataSale"></tbody>
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
						
						<div class="col-md-12 box_target" style="display:none">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="conTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
										<thead>
											<tr>
												<th><?= lang("salesman") ?></th>
												<th><?= lang("grand_total") ?></th>
												<th><?= lang("amount") ?></th>
												<th><?= lang("target") ?></th>
												<th><?= lang("rate") ?></th>
												<th><?= lang("commission") ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</thead>
                                        <tbody id="dataTarget"></tbody>
                                        <tfoot>
											<tr>
												<th class="text-right" colspan="5"><?= lang("total") ?></th>
												<th class="text-right" id="ttotal"></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
						
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						<div class="clearfix"></div>
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "csnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="csnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('add_salesman_commission', $this->lang->line("submit"), 'id="add_salesman_commission" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
							</div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		<?php if ($Owner || $Admin || $GP['sales-salesman_commission-date']) { ?>
			$("#date").datetimepicker({
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

		function getSalesmanSales(){
			var commission_type = $("#commission_type").val();
			if(commission_type=="Target"){
				getSalesmanTarget();
			}else{
				var biller_id = $("#biller").val();
				var salesman_group_id = $("#salesman_group").val();
				var salesman_id = $("#salesman").val();
				var project_id = $("#project").val();
				var from_date = $("#from_date").val();
				var to_date = $("#to_date").val();
				if(biller_id && salesman_group_id){
					$.ajax({
						type: "get", 
						async: true,
						url: site.base_url + "sales/get_salesman_sales/",
						data : { 
								biller_id : biller_id,
								salesman_group_id : salesman_group_id,
								salesman_id : salesman_id,
								from_date : from_date,
								to_date : to_date,
								project_id : project_id
						},
						dataType: "json",
						success: function (data) {
							var dataSale = "";
							if (data != false) {
								$.each(data, function () {
									var commission = 0;
									var d_rate = formatMoney(this.rate);
									if (this.rate.indexOf("%") >= 0){
										d_rate = this.rate;
										var d =  this.rate.split('%');
										var a = this.amount * d[0];
										if(a > 0){
											commission = a / 100;
										}
									}else{
										commission = this.rate;
									}
									dataSale += "<tr class='invoice_link' id='"+this.sale_id+"'>";
										dataSale += "<td class='text-left'>"+this.salesman;
											dataSale += "<input type='hidden' class='salesman_id' name='salesman_id[]' value='"+this.salesman_id+"'/>";	
											dataSale += "<input type='hidden' class='sale_id' name='sale_id[]' value='"+this.sale_id+"'/>";	
											dataSale += "<input type='hidden' class='grand_total' name='grand_total[]' value='"+this.grand_total+"'/>";
											dataSale += "<input type='hidden' class='amount' name='amount[]' value='"+this.amount+"'/>";
											dataSale += "<input type='hidden' class='rate' name='rate[]' value='"+this.rate+"'/>";
											dataSale += "<input type='hidden' class='commission' name='commission[]' value='"+commission+"'/>";
										dataSale += "</td>";
										dataSale += "<td class='text-center'>"+this.reference_no+"</td>";
										dataSale += "<td class='text-right'>"+formatMoney(this.grand_total)+"</td>";
										dataSale += "<td class='text-right'>"+formatMoney(this.amount)+"</td>";
										dataSale += "<td class='text-right'>"+d_rate+"</td>";
										dataSale += "<td class='text-right'>"+formatMoney(commission)+"</td>";
										dataSale += "<td class='text-right'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";	
									dataSale += "</tr>";
								});
							}
							$("#dataSale").html(dataSale);
							loadItems();
						}
					});
				}else{
					$("#dataSale").html("");
					loadItems();
				}
			}
		}
		
		
		function getSalesmanTarget(){
			var biller_id = $("#biller").val();
			var salesman_group_id = $("#salesman_group").val();
			var salesman_id = $("#salesman").val();
			var project_id = $("#project").val();
			var from_date = $("#from_date").val();
			var to_date = $("#to_date").val();
			if(biller_id && salesman_group_id){
				$.ajax({
					type: "get", 
					async: true,
					url: site.base_url + "sales/get_salesman_targets/",
					data : { 
							biller_id : biller_id,
							salesman_group_id : salesman_group_id,
							salesman_id : salesman_id,
							from_date : from_date,
							to_date : to_date,
							project_id : project_id
					},
					dataType: "json",
					success: function (data) {
						var dataTarget = "";
						if (data != false) {
							$.each(data, function () {
								var commission = 0;
								var d_rate = formatMoney(this.rate);
								if (this.rate.indexOf("%") >= 0){
									d_rate = this.rate;
									var d =  this.rate.split('%');
									var a = this.amount * d[0];
									if(a > 0){
										commission = a / 100;
									}
								}else{
									commission = this.rate;
								}
								dataTarget += "<tr>";
									dataTarget += "<td class='text-left'>"+this.salesman;
										dataTarget += "<input type='hidden' class='salesman_id' name='salesman_id[]' value='"+this.salesman_id+"'/>";	
										dataTarget += "<input type='hidden' class='sale_id' name='sale_ids[]' value='"+this.sale_ids+"'/>";	
										dataTarget += "<input type='hidden' class='grand_total' name='grand_total[]' value='"+this.grand_total+"'/>";
										dataTarget += "<input type='hidden' class='amount' name='amount[]' value='"+this.amount+"'/>";
										dataTarget += "<input type='hidden' class='rate' name='rate[]' value='"+this.rate+"'/>";
										dataTarget += "<input type='hidden' class='commission' name='commission[]' value='"+commission+"'/>";
										dataTarget += "<input type='hidden' class='target' name='target[]' value='"+this.target+"'/>";	
									dataTarget += "</td>";
									dataTarget += "<td class='text-right'>"+formatMoney(this.grand_total)+"</td>";
									dataTarget += "<td class='text-right'>"+formatMoney(this.amount)+"</td>";
									dataTarget += "<td class='text-left'>"+this.target+"</td>";
									dataTarget += "<td class='text-right'>"+d_rate+"</td>";
									dataTarget += "<td class='text-right'>"+formatMoney(commission)+"</td>";
									dataTarget += "<td class='text-right'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";	
								dataTarget += "</tr>";
							});
						}
						$("#dataTarget").html(dataTarget);
						loadItems();
					}
				});
			}else{
				$("#dataTarget").html("");
				loadItems();
			}
		}
		
		
		$("#biller").change(biller); biller();
		function biller(){
			var biller = $("#biller").val();
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
			getSalesmanSales();
		}
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
			loadItems();
		});

		function loadItems(){
			var tcommission = 0;
			$(".sale_id").each(function(){
				var row = $(this).closest('tr');
				var commission = row.find(".commission").val() - 0;
				tcommission += commission;
			});
			$('#total').html(formatMoney(tcommission));
			$('#ttotal').html(formatMoney(tcommission));
		}
		
		$("#commission_type").change(function (){
			$("#dataTarget").html("");
			$("#dataSale").html("");
			var commission_type = $(this).val();
			if(commission_type == "Normal"){
				$(".box_target").slideUp();
				$(".box_normal").slideDown();
			}else{
				$(".box_normal").slideUp();
				$(".box_target").slideDown();
			}
			getSalesmanSales();
		});
		
		$(document).on("change", "#salesman,#biller,#project,#from_date,#to_date", function () {	
			getSalesmanSales();
		});
		$(document).on("change", "#salesman_group", function () {
			var salesman_group_id = $(this).val();
			$("#salesman").val("");
			$.ajax({
				url : site.base_url + "sales/get_salesmans",
				dataType : "JSON",
				type : "GET",
				data : { salesman_group_id : salesman_group_id},
				success : function(data){
					var salesman_sel = "<select class='form-control' id='salesman' name='salesman'><option value=''><?= lang('select').' '.lang('salesman') ?></option>";
					if (data != false) {
						$.each(data, function () {
							salesman_sel += "<option value='"+this.id+"'>"+this.last_name+" "+this.first_name+"</option>";
						});
					}
					salesman_sel += "</select>"
					$(".salesman_box").html(salesman_sel);
					$('select').select2();	
				}
			});
			getSalesmanSales();
		});

	});
</script>