<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
	var count = 1, an = 1;
    $(document).ready(function () {
        <?php if ($Owner || $Admin || $GP['accountings-bank_reconciliation-date']) { ?>
			if (!localStorage.getItem('brdate')) {
				$("#brdate").datetimepicker({
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
        <?php } ?>
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_bank_reconciliation'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("accountings/add_bank_reconciliation", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['accountings-bank_reconciliation-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "brdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="brdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "brref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="brref"'); ?>
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
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="brproject" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
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
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="brproject" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
										</div>
									</div>
								</div>
								
							<?php } ?>
						
						<?php } ?>
						

						
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						
						<div class="col-md-4">
                            <div class="form-group">
                                <?= lang("account", "account") ?>
                                <select name="account_code" class="form-control select" id="account" style="width:100%">
									<?= $accounts ?>
								</select>
                            </div>
                        </div>
						
                        <div class="clearfix"></div>
						
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("statement_date", "statement_date"); ?>
								<?php echo form_input('statement_date', (isset($_POST['statement_date']) ? $_POST['statement_date'] : date('d/m/Y')), 'class="form-control input-tip date" id="statement_date" required="required"'); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("beginning_balance", "beginning_balance"); ?>
								<?php echo form_input('beginning_balance', (isset($_POST['beginning_balance']) ? $_POST['beginning_balance'] : ""), 'class="form-control input-tip text-right" id="beginning_balance" readonly="true"'); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("ending_balance", "ending_balance"); ?>
								<?php echo form_input('ending_balance', (isset($_POST['ending_balance']) ? $_POST['ending_balance'] : ""), 'class="form-control input-tip text-right" id="ending_balance" required="required"'); ?>
							</div>
						</div>
						<div class="col-md-12">
							<label class="table-label"><?= lang("enter_service_interest"); ?></label>
							<table id="brTable" class="table items table-striped table-bordered table-condensed table-hover">
								<thead>
									<tr>
										<th><?= lang('type') ?></th>
										<th><?= lang('account') ?></th>
										<th><?= lang('amount') ?></th>
									</tr>
								</thead>
								<tr>
									<td><?= lang('service_charge') ?></td>
									<td>
										<select name="service_charge_acc" class="form-control select" id="service_charge_acc" style="width:100%">
											<?= $expense ?>
										</select>
									</td>
									<td><input class="form-control text-right" value="0" type="text" name="service_charge" id="service_charge"/></td>
								</tr>
								<tr>
									<td><?= lang('interest_earned') ?></td>
									<td>
										<select name="interest_earned_acc" class="form-control select" id="interest_earned_acc" style="width:100%">
											<?= $income ?>
										</select>
									</td>
									<td><input class="form-control text-right" value="0" type="text" name="interest_earned" id="interest_earned"/></td>
								</tr>
							</table>
						</div>
						
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("transactions"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="brTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
											<tr>
												<th style="min-width:30px; width: 30px; text-align: center;">
													<input class="checkbox checkth" type="checkbox" name="check"/>
												</th>
												<th><?= lang("transaction")?></th>                                            
												<th><?= lang("date"); ?></th>
												<th><?= lang("reference"); ?></th>
												<th><?= lang("narrative"); ?></th>
												<th><?= lang("description"); ?></th>
												<th class="col-md-1"><?= lang("debit"); ?></th>
												<th class="col-md-1"><?= lang("credit"); ?></th>
											</tr>
                                        </thead>
                                        <tbody id="brBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
						<div class="col-md-12">
							<table style="width:100% !important; font-weight:bold !important">
								<tr>
									<td><?= lang('service_charge') ?> : <span id="v_service_charge"></span></td>
									<td><?= lang('interest_earned') ?> : <span id="v_interest_earned"></span></td>
									<td><?= lang('ending_balance') ?> : <span id="v_ending_balance"></span></td>
									<td><?= lang('cleared_balance') ?> : <span id="cleared_balance"></span></td>
									<td><input type="hidden" name="diffrence_amount" id="diffrence_amount" /><?= lang('difference') ?> : <span id="difference"></span></td>
								</tr>
							</table>
						</div>

                        <div class="clearfix"></div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= lang("note", "brnote"); ?>
                                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="brnote" style="margin-top: 10px; height: 100px;"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_bank_reconciliation', lang("submit"), 'id="submit_journal" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
	$(function(){
		
		$("#account, #statement_date").change(function(){
			get_trans();
		});
		
		$('#ending_balance').change(function(){
			calculateBalance();
		});
		
		$('body').on('ifChecked  ifUnchecked', '.check_clear', function() {
			var parent = $(this).closest('tr');
			if ($(this).is(':checked')) {
				parent.find('.cleared').val(1);
			}else{
				parent.find('.cleared').val(0);
			}
			calculateBalance();

		});
		
		var old_interest_earned;
		$(document).on("focus", '#interest_earned', function () {
			old_interest_earned = $(this).val();
		}).on("change", '#interest_earned', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_interest_earned);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			calculateBalance();
		});
		
		var old_service_charge;
		$(document).on("focus", '#service_charge', function () {
			old_service_charge = $(this).val();
		}).on("change", '#service_charge', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_service_charge);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			calculateBalance();
		});
		
		function calculateBalance(){
			var interest_earned = $('#interest_earned').val()-0;
			var service_charge = $('#service_charge').val()-0;
			var beginning_balance = $('#beginning_balance').val()-0;
			var clear_balance = 0;
			var ending_balance = $('#ending_balance').val()-0;
			$('.check_clear').each(function(){
				if ($(this).is(':checked')) {
					var parent = $(this).closest('tr');
					var clear_amount = parent.find('.amount').val()-0;
					clear_balance += clear_amount;
				}
			});
			var difference = beginning_balance -  ending_balance + clear_balance + interest_earned - service_charge;
			$('#v_interest_earned').html(formatMoney(interest_earned));
			$('#v_service_charge').html(formatMoney(service_charge));
			$('#v_ending_balance').html(formatMoney(ending_balance));
			$('#cleared_balance').html(formatMoney(clear_balance));
			$('#difference').html(formatMoney(difference));
			$('#diffrence_amount').val(formatDecimal(difference));
		}
		
		function get_trans(){
			var account_code = $("#account").val()-0;
			var statement_date = $("#statement_date").val();
			var biller_id = $('#slbiller').val();
			var project_id = $('#brproject').val();
			$.ajax({
				url : site.base_url + "accountings/getAccTrans",
				dataType : "JSON",
				type : "GET",
				data : { 
					biller_id : biller_id,
					project_id : project_id,
					account_code : account_code,
					statement_date : statement_date
				},
				success : function(data){
					if(data){
						$('#beginning_balance').val(formatDecimal(data.beginning_balance));
						var brtbody = '';
						if(data.current_trans){
							$.each(data.current_trans, function () {
								if(this.amount != 0 && this.amount !=''){
									brtbody += '<tr>';
									brtbody += '<td><input name="cleared[]" class="cleared" type="hidden" value="0"/><input class="checkbox multi-select input-xs check_clear" value="'+this.id+'" type="checkbox" name="val[]""/></td>';
									brtbody += '<td><input type="hidden" value="'+this.transaction_id+'" name="transaction_id[]" class="transaction_id"/><input type="hidden" value="'+this.transaction+'" name="transaction[]" class="transaction"/>'+this.transaction+'</td>';
									brtbody += '<td class="text-center"><input type="hidden" value="'+this.transaction_date+'" name="transaction_date[]" class="transaction_date"/>'+fld(this.transaction_date)+'</td>';
									brtbody += '<td><input type="hidden" value="'+this.reference+'" name="reference[]" class="reference"/>'+this.reference+'</td>';
									brtbody += '<td><input type="hidden" value="'+this.narrative+'" name="narrative[]" class="narrative"/>'+this.narrative+'</td>';
									brtbody += '<td><input type="hidden" value="'+this.description+'" name="description[]" class="description"/>'+this.description+'</td>';
									if(this.amount > 0){
										brtbody += '<td class="text-right"><input type="hidden" value="'+this.amount+'" name="amount[]" class="amount"/>'+formatMoney(this.amount)+'</td>';
										brtbody += '<td></td>';
									}else {
										brtbody += '<td></td>';
										brtbody += '<td class="text-right"><input type="hidden" value="'+this.amount+'" name="amount[]" class="amount"/>'+formatMoney((this.amount * (-1)))+'</td>';
									}
									brtbody += '</tr>>';
								}
								
							});
							
							$('#brBody').html(brtbody);
							$('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
								checkboxClass: 'icheckbox_square-blue',
								radioClass: 'iradio_square-blue',
								increaseArea: '20%'
							});
						}else{
							$('#brBody').html('');
						}
					}else{
						$('#brBody').html('');
					}

				}
			});
			calculateBalance();
		}
		
		
		
		
		$("#slbiller").change(biller); biller();
		function biller(){
			var biller = $("#slbiller").val();
			var project = 0;
			$.ajax({
				url : "<?= site_url("accountings/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						if (project = localStorage.getItem('project')) {
							$('#brproject').val(project);
						}else{
							$("#brproject").select2();
						}
					}
				}
			})
		}
		
	});
</script>
