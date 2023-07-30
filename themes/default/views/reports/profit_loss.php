<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>$(document).ready(function () {
        CURI = '<?= site_url('reports/profit_loss'); ?>';
    });</script>
<style>@media print {
        .fa {
            color: #EEE;
            display: none;
        }

        .small-box {
            border: 1px solid #CCC;
        }
    }</style>
<div class="box">
    <div class="box-header">
		<div class="sub_menu">&nbsp&nbsp&nbsp&nbsp&nbsp</div>
        <div class="sub_menu">
			<a href="javascript:;" onclick="window.print();" id ="print" class="tip btn btn-success btn-block box_sub_menu" 
				title="<?= lang('print') ?>">
				<i class="icon fa fa-file-fa fa-print"></i>&nbsp;</i><?=lang('print')?>
			</a>
        </div>
		<div class="sub_menu">
			<a href="#" id="xls" class="tip btn btn-warning btn-block box_sub_menu" title="<?= lang('download_xls') ?>">
				<i class="icon fa fa-file-excel-o"></i>&nbsp;</i><?=lang('download_xls')?>
			</a>
		</div>
		<div class="sub_menu">
			<a href="#" class="toggle_down tip btn btn-info btn-block box_sub_menu" title="<?= lang('show_form') ?>">
				<i class="icon fa fa-eye"></i>&nbsp;</i><?=lang('show_form')?>
			</a>
		</div>
		<div class="sub_menu">
			<a href="#" class="toggle_up tip btn btn-danger btn-block box_sub_menu" title="<?= lang('hide_form') ?>">
				<i class="icon fa fa-eye-slash"></i>&nbsp;</i><?=lang('hide_form')?>
			</a>
		</div>

        <div class="box-icon">
            <ul class="btn-tasks">
				<li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-bars tip"></i><?= lang('profit_loss'); ?>
						<?php
							if ($this->input->post('start_date')) {
								echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
							}
						?>
					</h2>
				</li>

                <!-- <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
				<li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:;" onclick="window.print();" id ="print" class="tip" title="<?= lang('print') ?>">
						<i class="icon fa fa-file-fa fa-print"></i>
					</a>
                </li> -->
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
					<!-- <p class="introtext"><?= lang('view_pl_report'); ?></p> -->
					<div id="form">
                    <?php echo form_open("reports/profit_loss"); ?>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="biller"><?= lang("biller"); ?></label>
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
	                                <?= lang("start_date", "start_date"); ?>
	                                <div class="input-group input-append">
	                                        <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
	                                    <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
	                                </div>
	                            </div>
	                        </div>
							

							<div class="col-sm-4">
	                            <div class="form-group">
	                                <?= lang("end_date", "end_date"); ?>
	                                <div class="input-group input-append">
	                                        <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
	                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
	                                </div>
	                            </div>
	                        </div>

						</div>
						<div class="form-group">
							<div
								class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
						</div>
						<?php echo form_close(); ?>

					</div>
					<div class="clearfix"></div>

					<table style="margin-top: 5px; width:100%;">
                    <th>
                        <tr>  


                            <?php 
                                $biller_id = (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller);
                                $biller_id_all = lang('all_selected');
                                $biller_id_detail = $this->site->getCompanyByID($biller_id);
                                if($biller_id_detail){
                                ?>
                                <td class="text_left" style="width: 10%">
                                    <div>
                                        <?= !empty($biller_id_detail->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller_id_detail->logo).'" alt="">' : ''; ?>
                                    </div>
                                </td>
                                <td></td>
                                <td class="text_center" style="width:100%">
                                    <div>
                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller_id_detail->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller_id_detail->name;?></strong>
                                    </div>
                                <br>

                                <?php 
                                }else{
                                ?>

                                <td class="text_left" style="width: 10%">
                                    <div>
                                        <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                    </div>
                                </td>
                                <td></td>
                                <td class="text_center" style="width:100%">
                                    <div>
                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
                                    </div>
                                <br>

                                <?php } ?>

                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('profit_loss_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('profit_loss_en')?>
                                </div><br>
                            </td> 
                        </tr>
                </table>
					
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									<th rowspan="2" style="width:20%"><?= lang('function') ?></th>
									<th colspan="2" style="width:60%"><?= lang('amount') ?></th>
								</tr>
								<tr>
									<th style="border: 1px solid #4CAF50; color: white; background-color:#4CAF50; text-align:center; width:30%"><?= lang('in') ?> <i class="fa fa-plus-circle"></i></th>
									<th style="border: 1px solid #FF5454; color: white; background-color:#FF5454; text-align:center; width:30%" ><?= lang('out') ?> <i class="fa fa-minus-circle"></i> </th>
								</tr>
							</thead>
							<tr>
								<td><b><?= lang('sales') ?></b></td>
								<td class="text-right"><?= $this->cus->formatMoney($total_sales->total_amount) ?></td>
								<td class="text-right">-</td>
							</tr>
							
							<tr>
								<td><b><?= lang('purchases') ?></b></td>
								<td class="text-right">-</td>
								<td class="text-right"><?= $this->cus->formatMoney($total_purchases->total_amount) ?></td>
							</tr>
							<tr>
								<td><b><?= lang('freight') ?></b></td>
								<td class="text-right">-</td>
								<td class="text-right"><?= $this->cus->formatMoney($total_freight->total_amount) ?></td>
							</tr>
							<tr>
								<td><b><?= lang('expenses') ?></b></td>
								<td class="text-right">-</td>
								<td class="text-right"><?= $this->cus->formatMoney($total_expenses->total_amount) ?></td>
							</tr>
							
							
							<tr>
								<td><b><?= lang('profit_loss') ?></b> ( <?= lang('sales') ?> - <?= lang('purchases') ?> - <?= lang('expenses') ?> )</td>
								<?php
									$profit_loss = $total_sales->total_amount - $total_purchases->total_amount  - $total_freight->total_amount - $total_expenses->total_amount;
									if($profit_loss > 0){ ?>
										<td class="text-right"><?= $this->cus->formatMoney($profit_loss) ?></td>
										<td class="text-right">-</td>
									<?php }else{ ?>
										<td class="text-right">-</td>
										<td class="text-right"><?= $this->cus->formatMoney(abs($profit_loss)) ?></td>
								<?php } ?>
							</tr>
							
							<tr>
								<td><b><?= lang('net_margin') ?></b> ( <?= lang('sale') ?> - <?= lang('cost') ?> - <?= lang('expenses') ?> )</td>
								<?php
									$net_margin = $total_gross_margin->grand_total - $total_gross_margin->total_cost - $total_expenses->total_amount;
									if($net_margin > 0){ ?>
										<td class="text-right"><?= $this->cus->formatMoney($net_margin) ?></td>
										<td class="text-right">-</td>
									<?php }else{ ?>
										<td class="text-right">-</td>
										<td class="text-right"><?= $this->cus->formatMoney(abs($net_margin)) ?></td>
								<?php } ?>
							</tr>
							
							<tr>
								<td><b><?= lang('sale_payments_received') ?></b></td>
								<td class="text-right"><?= $this->cus->formatMoney($total_received->total_amount) ?></td>
								<td class="text-right">-</td>
							</tr>
							
							<tr>
								<td><b><?= lang('purchase_payments_sent') ?></b></td>
								<td class="text-right">-</td>
								<td class="text-right"><?= $this->cus->formatMoney($total_paid->total_amount) ?></td>
							</tr>
							<tr>
								<td><b><?= lang('sale_return_payments_sent') ?></b></td>
								<td class="text-right">-</td>
								<td class="text-right"><?= $this->cus->formatMoney($total_returned->total_amount) ?></td>
							</tr>
							
							<?php if($this->config->item('pawn')){ ?>
								<tr>
									<td><b><?= lang('commission_payments_sent') ?></b></td>
									<td class="text-right">-</td>
									<td class="text-right"><?= $this->cus->formatMoney($total_commission->total_amount) ?></td>
								</tr>
							<?php }?>
							<tr>
								<td><b><?= lang('purchase_return_payments_received') ?></b></td>
								<td class="text-right"><?= $this->cus->formatMoney($total_purchase_returned->total_amount) ?></td>
								<td class="text-right">-</td>
							</tr>
							
							<tr>
								<td><b><?= lang('expense_payments_sent') ?></b></td>
								<td class="text-right">-</td>
								<td class="text-right"><?= $this->cus->formatMoney($total_expenses_amount->total_amount) ?></td>
							</tr>
							<?php if($this->config->item('pawn')){ ?>
								<tr>
									<td><b><?= lang('pawn_payments_sent') ?></b></td>
									<td class="text-right">-</td>
									<td class="text-right"><?= $this->cus->formatMoney($pawn_payment->total_amount) ?></td>
								</tr>
								
								<tr>
									<td><b><?= lang('pawn_return_payments_received') ?></b></td>
									<td class="text-right"><?= $this->cus->formatMoney($pawn_return_payment->total_amount) ?></td>
									<td class="text-right">-</td>
								</tr>
								
								<tr>
									<td><b><?= lang('pawn_rate_payments_received') ?></b></td>
									<td class="text-right"><?= $this->cus->formatMoney($pawn_rate_payment->total_amount) ?></td>
									<td class="text-right">-</td>
								</tr>
							<?php } ?>
							
							<?php if($Settings->installment){ ?>
								<tr>
									<td><b><?= lang('installment_interest_payments_received') ?></b></td>
									<td class="text-right"><?= $this->cus->formatMoney($installment_payment->interest_paid) ?></td>
									<td class="text-right">-</td>
								</tr>
								<tr>
									<td><b><?= lang('installment_penalty_payments_received') ?></b></td>
									<td class="text-right"><?= $this->cus->formatMoney($installment_payment->penalty_paid) ?></td>
									<td class="text-right">-</td>
								</tr>
							<?php } ?>
							<tr>
								<td><b><?= lang('cash') ?></b> ( <?= lang('payment_received') ?> - <?= lang('payment_sent') ?> )</td>
								<?php
									$cash = $installment_payment->interest_paid + $installment_payment->penalty_paid + $pawn_rate_payment->total_amount + $pawn_return_payment->total_amount + $total_received->total_amount + $total_purchase_returned->total_amount - $total_paid->total_amount - $total_returned->total_amount - $total_expenses_amount->total_amount - $pawn_payment->total_amount - $total_commission->total_amount;
									if($cash > 0){ ?>
										<td class="text-right"><?= $this->cus->formatMoney($cash) ?></td>
										<td class="text-right">-</td>
									<?php }else{ ?>
										<td class="text-right">-</td>
										<td class="text-right"><?= $this->cus->formatMoney(abs($cash)) ?></td>
								<?php } ?>
							</tr>
						</table>
					</div>
            </div>
			
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		$('#form').hide();
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( $('.table-responsive').html());
			this.href = result;
			this.download = "profit_and_loss.xls";
			return true;			
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
					}else{
						
					}
				}
			})
		}
    });
</script>
