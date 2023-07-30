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

 <?php echo form_open("reports/ar_customer", ' id="form-submit" '); ?>
 
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('ar_customer') ?></h2>
		
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
                <p class="introtext"><?= lang("ar_customer") ?></p>
				
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
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php
                                $cu[""] = lang('select').' '.lang('customer');
                                foreach ($customers as $customer) {
                                    $cu[$customer->id] = $customer->company.' - '.$customer->name.' ('.$customer->code.')';
                                }
                                echo form_dropdown('customer', $cu, (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control"  data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"');
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
									<?= lang("customer") ?>
									<i class="fa fa-angle-double-right" aria-hidden="true"></i>
									<?= lang("invoice") ?>
								</th>
								<th><?= lang("date") ?></th>
								<th><?= lang("aging") ?></th>
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
								$customer = $this->input->post("customer");
								$user = $this->input->post("user");
								$reference_no = $this->input->post("reference_no");
								$hide_zero_balance = $this->input->post("hide_zero_balance");
								

								$td_customer = '';
								$ar_customers = $this->reports_model->getCustomerAR($end_date,$warehouse_id,$biller,$project,$customer,$user, $reference_no);
								$iv_customers = $this->reports_model->getARInv($end_date,$warehouse_id,$biller,$project,$user, $reference_no);
								$pm_customers = $this->reports_model->getPaymentSales($end_date);
								

								if($ar_customers){
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
									
									foreach($ar_customers as $ar_customer){
										if($hide_con || ($this->cus->formatMoney($ar_customer->grand_total+($ar_customer->return_paid)) <> $this->cus->formatMoney((($ar_customer->return_total) + ($ar_customer->amount_payment))))){
											$td_customer .= '<tr>
																<td colspan="9" style="text-align:left"><b>'.$ar_customer->company.' - '.$ar_customer->customer.'</b></td>
															</tr>';
											$cus_invs = $iv_customers[$ar_customer->customer_id];	
											$customer_grand_total = 0;
											$customer_return = 0;
											$customer_paid = 0;
											$customer_discount = 0;
											
											if($cus_invs){
												foreach($cus_invs as $cus_inv){
													if($hide_con || ($this->cus->formatMoney($cus_inv->grand_total+abs($cus_inv->payment_return)) <> $this->cus->formatMoney((abs($cus_inv->grand_total_return) + abs($cus_inv->paid))))){
														$return_inv = false;
														$grand_total = $cus_inv->grand_total;	
														$balance = $grand_total;
														$invoice_date = date_create($cus_inv->date);
														$current_date = date_create(date('Y-m-d'));
														$diff=date_diff($invoice_date,$current_date);
														$aging = $diff->format("%a") + 1;
														$aging = ($aging > 1 ? $aging.' days' : $aging.' day');
														$td_customer .= '<tr id="'.$cus_inv->id.'" class="invoice_link3">
																			<td class="inv_td" style="text-align:left; padding-left:20px">'.$cus_inv->reference_no.'</td>
																			<td class="inv_td" style="text-align:center">'.$this->cus->hrld($cus_inv->date).'</td>
																			<td class="inv_td" style="text-align:center">'.$aging.'</td>
																			<td class="inv_td" style="text-align:left">'.$this->cus->decode_html($cus_inv->note).'</td>
																			<td class="inv_td" style="text-align:right">'.$this->cus->formatMoney($grand_total).'</td>
																			<td class="inv_td" style="text-align:right">-</td>
																			<td class="inv_td" style="text-align:right">-</td>
																			<td class="inv_td" style="text-align:right">-</td>
																			<td class="inv_td" style="text-align:right">'.$this->cus->formatMoney($balance).'</td>
																		</tr>';
																		
														$customer_grand_total += $grand_total;
														
														if($cus_inv->return_id > 0){
															$return_inv = $this->reports_model->getSaleReturn($cus_inv->id, $end_date);
														}
		
														$cus_payments = isset($pm_customers[$cus_inv->id]) ? $pm_customers[$cus_inv->id] : false;
														if($cus_payments){
															foreach($cus_payments as $cus_payment){															
																if($return_inv){
																	if($return_inv->date < $cus_payment->date){
																		$return = abs($return_inv->grand_total);	
																		$balance = $balance - $return;
																		$td_customer .= '<tr id="'.$return_inv->id.'" class="return_link3">
																					<td style="text-align:left; padding-left:40px">'.$return_inv->return_sale_ref.'</td>
																					<td style="text-align:center">'.$this->cus->hrld($return_inv->date).'</td>
																					<td></td>
																					<td style="text-align:left">'.$this->cus->decode_html($return_inv->note).'</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">'.$this->cus->formatMoney($return).'</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																				</tr>';
																		
																
																		$return_payments = isset($pm_customers[$return_inv->id]) ? $pm_customers[$return_inv->id] : false;
																		if($return_payments){
																			foreach($return_payments as $return_payment){
																				$return_paid = abs($return_payment->amount);
																				$return_discount = abs($return_payment->discount);
																				$balance = $balance + ($return_paid + $return_discount);
																				$td_customer .= '<tr id="'.$return_payment->id.'" class="payment_link3">
																									<td style="text-align:left; padding-left:60px">'.$return_payment->reference_no.'</td>
																									<td style="text-align:center">'.$this->cus->hrld($return_payment->date).'</td>
																									<td></td>
																									<td style="text-align:left">'.$this->cus->decode_html($return_payment->note).'</td>
																									<td style="text-align:right">-</td>
																									<td style="text-align:right">-</td>
																									<td style="text-align:right">'.($return_paid > 0 ? '( '.$this->cus->formatMoney($return_paid).' )' : '-').'</td>
																									<td style="text-align:right">'.($return_discount > 0 ? '( '.$this->cus->formatMoney($return_discount).' )' : '-').'</td>
																									<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																								</tr>';
																				$customer_paid -= $return_paid;
																				$customer_discount -= $return_discount;							
																			}
																		}
																		$customer_return += $return;
																		$return_inv = false;			
																	}
																	
																}
																
																$paid = $cus_payment->amount;
																$discount = $cus_payment->discount;
																$balance = $balance - ($paid + $discount);
																$td_customer .= '<tr id="'.$cus_payment->id.'" class="payment_link3">
																					<td style="text-align:left; padding-left:40px">'.$cus_payment->reference_no.'</td>
																					<td style="text-align:center">'.$this->cus->hrld($cus_payment->date).'</td>
																					<td></td>
																					<td style="text-align:left">'.$this->cus->decode_html($cus_payment->note).'</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">-</td>
																					<td style="text-align:right">'.($paid > 0 ? $this->cus->formatMoney($paid) : '-').'</td>
																					<td style="text-align:right">'.($discount > 0 ? $this->cus->formatMoney($discount) : '-').'</td>
																					<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																				</tr>';
																				
																$customer_paid += $paid;
																$customer_discount += $discount;				
															}
														}
														
														if($return_inv){
															$return = abs($return_inv->grand_total);	
															$balance = $balance - $return;
															$td_customer .= '<tr id="'.$return_inv->id.'" class="return_link3">
																		<td style="text-align:left; padding-left:40px">'.$return_inv->return_sale_ref.'</td>
																		<td style="text-align:center">'.$this->cus->hrld($return_inv->date).'</td>
																		<td></td>
																		<td style="text-align:left">'.$this->cus->decode_html($return_inv->note).'</td>
																		<td style="text-align:right">-</td>
																		<td style="text-align:right">'.$this->cus->formatMoney($return).'</td>
																		<td style="text-align:right">-</td>
																		<td style="text-align:right">-</td>
																		<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																	</tr>';
															
										
															$return_payments = isset($pm_customers[$return_inv->id]) ? $pm_customers[$return_inv->id] : false;
															if($return_payments){
																foreach($return_payments as $return_payment){
																	$return_paid = abs($return_payment->amount);
																	$return_discount = abs($return_payment->discount);
																	$balance = $balance + ($return_paid + $return_discount);
																	$td_customer .= '<tr id="'.$return_payment->id.'" class="payment_link3">
																						<td style="text-align:left; padding-left:60px">'.$return_payment->reference_no.'</td>
																						<td style="text-align:center">'.$this->cus->hrld($return_payment->date).'</td>
																						<td></td>
																						<td style="text-align:left">'.$this->cus->decode_html($return_payment->note).'</td>
																						<td style="text-align:right">-</td>
																						<td style="text-align:right">-</td>
																						<td style="text-align:right">'.($return_paid > 0 ? '( '.$this->cus->formatMoney($return_paid).' )' : '-').'</td>
																						<td style="text-align:right">'.($return_discount > 0 ? '( '.$this->cus->formatMoney($return_discount).' )' : '-').'</td>
																						<td style="text-align:right">'.($balance < 0 ? '( '.$this->cus->formatMoney(abs($balance)).' )' : $this->cus->formatMoney($balance)).'</td>
																					</tr>';
																	$customer_paid -= $return_paid;
																	$customer_discount -= $return_discount;							
																}
															}
															$customer_return += $return;
														}
													}	
												}
												$customer_balance = $customer_grand_total - ($customer_return + $customer_paid + $customer_discount);
												$td_customer .= '<tr style="font-weight:bold !important">
																	<td colspan="4" style="text-align:right">'.lang("total").'</td>
																	<td style="text-align:right">'.($customer_grand_total < 0 ? '( '.$this->cus->formatMoney(abs($customer_grand_total)).' )' : $this->cus->formatMoney($customer_grand_total)).'</td>
																	<td style="text-align:right">'.($customer_return < 0 ? '( '.$this->cus->formatMoney(abs($customer_return)).' )' : $this->cus->formatMoney($customer_return)).'</td>
																	<td style="text-align:right">'.($customer_paid < 0 ? '( '.$this->cus->formatMoney(abs($customer_paid)).' )' : $this->cus->formatMoney($customer_paid)).'</td>
																	<td style="text-align:right">'.($customer_discount < 0 ? '( '.$this->cus->formatMoney(abs($customer_discount)).' )' : $this->cus->formatMoney($customer_discount)).'</td>
																	<td style="text-align:right">'.($customer_balance < 0 ? '( '.$this->cus->formatMoney(abs($customer_balance)).' )' : $this->cus->formatMoney($customer_balance)).'</td>
																</tr>';
																
												$total_grand += $customer_grand_total;
												$total_return += $customer_return;
												$total_paid += $customer_paid;
												$total_discount += $customer_discount;		
												$total_balance += $customer_balance;			
											}
										}
									}
									$td_customer .= '<tr style="font-weight:bold !important">
														<td colspan="4" style="text-align:right">'.lang("grand_total").'</td>
														<td style="text-align:right">'.($total_grand < 0 ? '( '.$this->cus->formatMoney(abs($total_grand)).' )' : $this->cus->formatMoney($total_grand)).'</td>
														<td style="text-align:right">'.($total_return < 0 ? '( '.$this->cus->formatMoney(abs($total_return)).' )' : $this->cus->formatMoney($total_return)).'</td>
														<td style="text-align:right">'.($total_paid < 0 ? '( '.$this->cus->formatMoney(abs($total_paid)).' )' : $this->cus->formatMoney($total_paid)).'</td>
														<td style="text-align:right">'.($total_discount < 0 ? '( '.$this->cus->formatMoney(abs($total_discount)).' )' : $this->cus->formatMoney($total_discount)).'</td>
														<td style="text-align:right">'.($total_balance < 0 ? '( '.$this->cus->formatMoney(abs($total_balance)).' )' : $this->cus->formatMoney($total_balance)).'</td>
													</tr>';
								}else{
									$td_customer .= '<tr>
														<td colspan="9" style="text-align:left">'.lang("sEmptyTable").'</td>
													</tr>';
								}
								echo $td_customer;
								
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
			this.download = "ar_customer.xls";
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
