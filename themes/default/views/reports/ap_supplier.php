<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    .dfTable th, .dfTable td {
        text-align: center;
        vertical-align: middle;
    }
    .dfTable td {
        padding: 2px;
    }

    .data tr:nth-child(odd) td {
        color: #2FA4E7;
    }

    .data tr:nth-child(even) td {
        text-align: right;
    }

	.inv_td{
		background-color:#e2edff !important;
	}
</style>

 <?php echo form_open("reports/ap_supplier", ' id="form-submit" '); ?>
 
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('ap_supplier') ?></h2>
		
        <div class="box-icon">
            <ul class="btn-tasks">
			
				<li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
				<li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
				
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("ap_supplier") ?></p>
				
				<div id="form">
				
                   
					
					<div class="row">	
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						
						
						<?php if($Settings->project == 1){ ?>
									
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project">
										<?php
										$pj[''] = '';
										if (isset($projects) && $projects != false) {
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						
						<?php } ?>
						
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="supplier"><?= lang("supplier"); ?></label>
                                <?php
                                $su[""] = lang('select').' '.lang('supplier');
                                foreach ($suppliers as $supplier) {
                                    $su[$supplier->id] = $supplier->name.' ('.$supplier->code.')';
                                }
                                echo form_dropdown('supplier', $su, (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control"  data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("supplier") . '"');
                                ?>
                            </div>
                        </div>

						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="hide_zero_balance"><?= lang("hide_zero_balance"); ?></label>
                                <?php
								$hz["yes"] = lang('yes');
								$hz["no"] = lang('no');
                                echo form_dropdown('hide_zero_balance', $hz, (isset($_POST['hide_zero_balance']) ? $_POST['hide_zero_balance'] : ""), 'class="form-control" id="hide_zero_balance"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
						
					</div>
					
					
					<div class="form-group">
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("Search"), 'class="btn btn-primary"'); ?> 
						</div>
                    </div>
					
					<?php echo form_close(); ?>
					
				</div>
				
                <div class="table-responsive">
                    <table class="table table-bordered table-striped dfTable reports-table">
                        <thead>
							<tr>
								<th>
									<?= lang("supplier") ?>
									<i class="fa fa-angle-double-right" aria-hidden="true"></i>
									<?= lang("purchase_invoice") ?>
								</th>
								<th><?= lang("date") ?></th>
								<th width="400"><?= lang("note") ?></th>
								<th><?= lang("grand_total") ?></th>
								<th><?= lang("return") ?></th>								
								<th><?= lang("paid") ?></th>
								<th><?= lang("discount") ?></th>
								<th><?= lang("balance") ?></th>
							</tr>
                        </thead>
                        <tbody>							
							<?php
					
								$warehouse_id = $this->input->post("warehouse");
								$end_date = $this->input->post("end_date");
								$biller = $this->input->post("biller");
								$project = $this->input->post("project");
								$supplier = $this->input->post("supplier");
								$user = $this->input->post("user");
								$reference_no = $this->input->post("reference_no");
								$hide_zero_balance = $this->input->post("hide_zero_balance");
								$td_supplier = '';
								$ap_suppliers = $this->reports_model->getSupplierAP($end_date,$warehouse_id,$biller,$project,$supplier,$user, $reference_no);
								$pi_suppliers = $this->reports_model->getAPInv($end_date,$warehouse_id,$biller,$project, $user, $reference_no);
								$ex_suppliers = $this->reports_model->getAPExpense($end_date,$warehouse_id,$biller,$project, $user, $reference_no);
								$pi_payments = $this->reports_model->getPaymentPurchases($end_date);
								$ex_payments = $this->reports_model->getPaymentExpenses($end_date);
					
								
								if($ap_suppliers){
									$total_grand = 0;
									$total_return = 0;
									$total_paid = 0;
									$total_discount = 0;
									$total_balance = 0;
									if($hide_zero_balance == 'no'){
										$hide_con = true;
									}else{
										$hide_con = false;
									}
									foreach($ap_suppliers as $ap_supplier){
										if($hide_con || ($this->cus->formatMoney($ap_supplier->grand_total+($ap_supplier->return_paid)) <> $this->cus->formatMoney((($ap_supplier->return_total) + ($ap_supplier->amount_payment))))){
											$td_supplier .= '<tr>
																<td colspan="8" style="text-align:left"><b>'.$ap_supplier->supplier.'</b></td>
															</tr>';
											$sup_invs = isset($pi_suppliers[$ap_supplier->supplier_id]) ? $pi_suppliers[$ap_supplier->supplier_id] : false;
											$sup_expenses = isset($ex_suppliers[$ap_supplier->supplier_id]) ? $ex_suppliers[$ap_supplier->supplier_id] : false;								
											$supplier_grand_total = 0;
											$supplier_return = 0;
											$supplier_paid = 0;
											$supplier_discount = 0;
											
											if($sup_invs){
												foreach($sup_invs as $sup_inv){
													if($hide_con || ($this->cus->formatMoney($sup_inv->grand_total+abs($sup_inv->payment_return)) <> $this->cus->formatMoney((abs($sup_inv->grand_total_return) + abs($sup_inv->paid))))){
														$return_inv = false;
														$grand_total = $sup_inv->grand_total;	
														$balance = $grand_total;
														$td_supplier .= '<tr id="'.$sup_inv->id.'" class="purchase_link3">
																			<td class="inv_td" style="text-align:left; padding-left:20px">'.$sup_inv->reference_no.'</td>
																			<td class="inv_td" style="text-align:center">'.$this->cus->hrld($sup_inv->date).'</td>
																			<td class="inv_td" style="text-align:left">'.$this->cus->decode_html($sup_inv->note).'</td>
																			<td class="inv_td" style="text-align:right">'.$this->cus->formatMoney($grand_total).'</td>
																			<td class="inv_td" style="text-align:right">-</td>
																			<td class="inv_td" style="text-align:right">-</td>
																			<td class="inv_td" style="text-align:right">-</td>
																			<td class="inv_td" style="text-align:right">'.$this->cus->formatMoney($balance).'</td>
																		</tr>';
																		
														$supplier_grand_total += $grand_total;
														
														if($sup_inv->return_id > 0){
															$return_inv = $this->reports_model->getPurchaseReturn($sup_inv->id,$end_date);
														}
															
												
														$sup_payments = isset($pi_payments[$sup_inv->id]) ? $pi_payments[$sup_inv->id] : false;
														
														if($sup_payments){
															foreach($sup_payments as $sup_payment){															
																if($return_inv){
																	if($return_inv->date < $sup_payment->date){
																		$return = abs($return_inv->grand_total);	
																		$balance = $balance - $return;
																		$td_supplier .= '<tr id="'.$return_inv->id.'" class="return_link4">
																					<td style="text-align:left; padding-left:40px">'.$return_inv->return_purchase_ref.'</td>
																					<td style="text-align:center">'.$this->cus->hrld($return_inv->date).'</td>
																					<td style="text-align:left">'.$this->cus->decode_html($return_inv->note).'</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">'.$this->cus->formatMoney($return).'</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																				</tr>';
																		
											;
																		$return_payments = isset($pi_payments[$return_inv->id]) ? $pi_payments[$return_inv->id] : false;
																		if($return_payments){
																			foreach($return_payments as $return_payment){
																				$return_paid = abs($return_payment->amount);
																				$return_discount = abs($return_payment->discount);
																				$balance = $balance + ($return_paid + $return_discount);
																				$td_supplier .= '<tr id="'.$return_payment->id.'" class="payment_link4">
																									<td style="text-align:left; padding-left:60px">'.$return_payment->reference_no.'</td>
																									<td style="text-align:center">'.$this->cus->hrld($return_payment->date).'</td>
																									<td style="text-align:left">'.$this->cus->decode_html($return_payment->note).'</td>
																									<td style="text-align:right">-</td>
																									<td style="text-align:right">-</td>
																									<td style="text-align:right">'.($return_paid > 0 ? '( '.$this->cus->formatMoney($return_paid).' )' : '-').'</td>
																									<td style="text-align:right">'.($return_discount > 0 ? '( '.$this->cus->formatMoney($return_discount).' )' : '-').'</td>
																									<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																								</tr>';
																				$supplier_paid -= $return_paid;
																				$supplier_discount -= $return_discount;							
																			}
																		}
																		$supplier_return += $return;
																		$return_inv = false;			
																	}
																	
																}
																
																$paid = $sup_payment->amount;
																$discount = $sup_payment->discount;
																$balance = $balance - ($paid + $discount);
																$td_supplier .= '<tr id="'.$sup_payment->id.'" class="payment_link4">
																					<td style="text-align:left; padding-left:40px">'.$sup_payment->reference_no.'</td>
																					<td style="text-align:center">'.$this->cus->hrld($sup_payment->date).'</td>
																					<td style="text-align:left">'.$this->cus->decode_html($sup_payment->note).'</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">'.($paid > 0 ? $this->cus->formatMoney($paid) : '-').'</td>
																					<td style="text-align:right">'.($discount > 0 ? $this->cus->formatMoney($discount) : '-').'</td>
																					<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																				</tr>';
																				
																$supplier_paid += $paid;
																$supplier_discount += $discount;				
															}
														}
														
														if($return_inv){
															$return = abs($return_inv->grand_total);	
															$balance = $balance - $return;
															$td_supplier .= '<tr id="'.$return_inv->id.'" class="return_link4">
																		<td style="text-align:left; padding-left:40px">'.$return_inv->return_purchase_ref.'</td>
																		<td style="text-align:center">'.$this->cus->hrld($return_inv->date).'</td>
																		<td style="text-align:left">'.$this->cus->decode_html($return_inv->note).'</td>
																		<td style="text-align:right">-</td>
																		<td style="text-align:right">'.$this->cus->formatMoney($return).'</td>
																		<td style="text-align:right">-</td>
																		<td style="text-align:right">-</td>
																		<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																	</tr>';
														
															$return_payments = isset($pi_payments[$return_inv->id]) ? $pi_payments[$return_inv->id] : false;
															if($return_payments){
																foreach($return_payments as $return_payment){
																	$return_paid = abs($return_payment->amount);
																	$return_discount = abs($return_payment->discount);
																	$balance = $balance + ($return_paid + $return_discount);
																	$td_supplier .= '<tr id="'.$return_payment->id.'" class="payment_link4">
																						<td style="text-align:left; padding-left:60px">'.$return_payment->reference_no.'</td>
																						<td style="text-align:center">'.$this->cus->hrld($return_payment->date).'</td>
																						<td style="text-align:left">'.$this->cus->decode_html($return_payment->note).'</td>
																						<td style="text-align:right">-</td>
																						<td style="text-align:right">-</td>
																						<td style="text-align:right">'.($return_paid > 0 ? '( '.$this->cus->formatMoney($return_paid).' )' : '-').'</td>
																						<td style="text-align:right">'.($return_discount > 0 ? '( '.$this->cus->formatMoney($return_discount).' )' : '-').'</td>
																						<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																					</tr>';
																	$supplier_paid -= $return_paid;
																	$supplier_discount -= $return_discount;							
																}
															}
															$supplier_return += $return;
														}
													}	
												}
											}
											if($sup_expenses){
												foreach($sup_expenses as $sup_expense){
													if($hide_con || ($this->cus->formatMoney($sup_expense->grand_total) <> $this->cus->formatMoney($sup_expense->paid))){
														$expense_total = $sup_expense->grand_total;
														$expense_balance = $expense_total;
														$td_supplier .= '<tr id="'.$sup_expense->id.'" class="expense_link">
																				<td class="inv_td" style="text-align:left; padding-left:20px">'.$sup_expense->reference.'</td>
																				<td class="inv_td" style="text-align:center">'.$this->cus->hrld($sup_expense->date).'</td>
																				<td class="inv_td" style="text-align:left">'.$this->cus->decode_html($sup_expense->note).'</td>
																				<td class="inv_td" style="text-align:right">'.$this->cus->formatMoney($expense_total).'</td>
																				<td class="inv_td" style="text-align:right">-</td>
																				<td class="inv_td" style="text-align:right">-</td>
																				<td class="inv_td" style="text-align:right">-</td>
																				<td class="inv_td" style="text-align:right">'.$this->cus->formatMoney($expense_balance).'</td>
																			</tr>';
														$supplier_grand_total += $expense_total;					
														$expense_payments = isset($ex_payments[$sup_expense->id]) ? $ex_payments[$sup_expense->id] : false;
														if($expense_payments){
															foreach($expense_payments as $expense_payment){
																$expense_paid = $expense_payment->amount;
																$expense_discount = $expense_payment->discount;
																$expense_balance = $expense_balance - ($expense_paid + $expense_discount);
																$td_supplier .= '<tr id="'.$expense_payment->id.'" class="payment_link4">
																					<td style="text-align:left; padding-left:40px">'.$expense_payment->reference_no.'</td>
																					<td style="text-align:center">'.$this->cus->hrld($expense_payment->date).'</td>
																					<td style="text-align:left">'.$this->cus->decode_html($expense_payment->note).'</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">'.($expense_paid > 0 ? $this->cus->formatMoney($expense_paid) : '-').'</td>
																					<td style="text-align:right">'.($expense_discount > 0 ? $this->cus->formatMoney($expense_discount) : '-').'</td>
																					<td style="text-align:right">'.($expense_balance < 0 ? '( '.$this->cus->formatMoney(abs($expense_balance)).' )' : $this->cus->formatMoney($expense_balance)).'</td>
																				</tr>';
																$supplier_paid += $expense_paid;	
																$supplier_discount += $expense_discount;	
															}	
														}
													}
																	
												}	
											}
											if($sup_invs || $sup_expenses){
												$supplier_balance = $supplier_grand_total - ($supplier_return + $supplier_paid + $supplier_discount);
												$td_supplier .= '<tr style="font-weight:bold !important">
																	<td colspan="3" style="text-align:right">'.lang("total").'</td>
																	<td style="text-align:right">'.($supplier_grand_total < 0 ? '( '.$this->cus->formatMoney(abs($supplier_grand_total)).' )' : $this->cus->formatMoney($supplier_grand_total)).'</td>
																	<td style="text-align:right">'.($supplier_return < 0 ? '( '.$this->cus->formatMoney(abs($supplier_return)).' )' : $this->cus->formatMoney($supplier_return)).'</td>
																	<td style="text-align:right">'.($supplier_paid < 0 ? '( '.$this->cus->formatMoney(abs($supplier_paid)).' )' : $this->cus->formatMoney($supplier_paid)).'</td>
																	<td style="text-align:right">'.($supplier_discount < 0 ? '( '.$this->cus->formatMoney(abs($supplier_discount)).' )' : $this->cus->formatMoney($supplier_discount)).'</td>
																	<td style="text-align:right">'.($supplier_balance < 0 ? '( '.$this->cus->formatMoney(abs($supplier_balance)).' )' : $this->cus->formatMoney($supplier_balance)).'</td>
																</tr>';
																
												$total_grand += $supplier_grand_total;
												$total_return += $supplier_return;
												$total_paid += $supplier_paid;
												$total_discount += $supplier_discount;		
												$total_balance += $supplier_balance;
											}											
											
										}
									}
									$td_supplier .= '<tr style="font-weight:bold !important">
														<td colspan="3" style="text-align:right">'.lang("grand_total").'</td>
														<td style="text-align:right">'.($total_grand < 0 ? '( '.$this->cus->formatMoney(abs($total_grand)).' )' : $this->cus->formatMoney($total_grand)).'</td>
														<td style="text-align:right">'.($total_return < 0 ? '( '.$this->cus->formatMoney(abs($total_return)).' )' : $this->cus->formatMoney($total_return)).'</td>
														<td style="text-align:right">'.($total_paid < 0 ? '( '.$this->cus->formatMoney(abs($total_paid)).' )' : $this->cus->formatMoney($total_paid)).'</td>
														<td style="text-align:right">'.($total_discount < 0 ? '( '.$this->cus->formatMoney(abs($total_discount)).' )' : $this->cus->formatMoney($total_discount)).'</td>
														<td style="text-align:right">'.($total_balance < 0 ? '( '.$this->cus->formatMoney(abs($total_balance)).' )' : $this->cus->formatMoney($total_balance)).'</td>
													</tr>';
								}else{
									$td_supplier .= '<tr>
														<td colspan="8" style="text-align:left">'.lang("sEmptyTable").'</td>
													</tr>';
								}
								echo $td_supplier;
								
							?>							
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
    

		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( $('.table-responsive').html());
			this.href = result;
			this.download = "ap_supplier.xls";
			return true;			
		});
		
		$('#form').hide();
		
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		
		$("#biller").change(biller); biller();
		function biller(){
			var biller = $("#biller").val();
			var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
			$.ajax({
				url : "<?= site_url("reports/get_project") ?>",
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
		
    });
</script>
