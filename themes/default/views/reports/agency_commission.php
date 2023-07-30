<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->get('reference_no')) {
    $v .= "&reference_no=" . $this->input->get('reference_no');
}
if ($this->input->get('biller')) {
    $v .= "&biller=" . $this->input->get('biller');
}
if ($this->input->get('customer')) {
    $v .= "&customer=" . $this->input->get('customer');
}
if ($this->input->get('user')) {
    $v .= "&agency=" . $this->input->get('agency');
}
if ($this->input->get('warehouse')) {
    $v .= "&warehouse=" . $this->input->get('warehouse');
}
if ($this->input->get('user')) {
    $v .= "&user=" . $this->input->get('user');
}
if ($this->input->get('agency')) {
    $v .= "&agency=" . $this->input->get('agency');
}
if ($this->input->get('start_date')) {
    $v .= "&start_date=" . $this->input->get('start_date');
}
if ($this->input->get('end_date')) {
    $v .= "&end_date=" . $this->input->get('end_date');
}
?>
<style type="text/css" media="all">
	@media print{
		.print-hidden{
			display:none;
		}
		.show-heading{
			display:block !important;
		}
		table th, table td{
			font-size:7px;
		}
	}
	.show-heading{
		display:none;
	}
</style>
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
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('agency_commission_report'); ?>
			<?php
				if ($this->input->get('start_date')) {
					echo "From " . $this->input->get('start_date') . " to " . $this->input->get('end_date');
				}
            ?>
        </h2>
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
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                
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
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo form_open("reports/agency_commission", 'method="GET"'); ?>
                    <div class="row">
					
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="product_name"><?= lang("product_name"); ?></label>
                                <?php echo form_input('product_name', (isset($_GET['product_name']) ? $_GET['product_name'] : ""), 'class="form-control tip" id="product_name"'); ?>
                            </div>
                        </div>

						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("agency"); ?></label>
                                <?php
                                $ag[""] = lang('select').' '.lang('agency');
                                foreach ($agencies as $agency) {
                                    $ag[$agency->id] = $agency->last_name . " " . $agency->first_name;
                                }
                                echo form_dropdown('agency', $ag, (isset($_GET['agency']) ? $_GET['agency'] : ""), 'class="form-control" id="agency" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("agency") . '"');
                                ?>
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
                                echo form_dropdown('user', $us, (isset($_GET['user']) ? $_GET['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_GET['biller']) ? $_GET['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_GET['warehouse']) ? $_GET['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_GET['customer']) ? $_GET['customer'] : ""), 'class="form-control" id="rtcustomer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label" for="pagination"><?= lang("pagination"); ?></label>
								<?php
								$pg = array(lang('yes'),lang('no'));
								echo form_dropdown('pagination', $pg, (isset($_GET['pagination']) ? $_GET['pagination'] : 1), 'class="form-control" id="pagination" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("pagination") . '"');
								?>
							</div>
						</div>

						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_GET['start_date']) ? $_GET['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_GET['end_date']) ? $_GET['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
						
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
				<h2 class="show-heading">
					<?=lang("agency_commission_report")?>
					<small>
						<strong>
							<?php
								if ($this->input->get('start_date')) {
									echo "From " . $this->input->get('start_date') . " to " . $this->input->get('end_date');
								}
							?>
						</strong>
					</small>
				</h2>
                <div class="table-responsive">
				<table class="table table-bordered table-condensed table-striped">
						<thead>
						<tr>
							<th style="width:100px;"><?= lang("date"); ?></th>
							<th style="width:150px;"><?= lang("product_name"); ?></th>
							<th style="width:150px;"><?= lang("customer"); ?></th>
							<th style="width:150px;"><?= lang("name"); ?></th>
							<th style="width:150px;"><?= lang("rate"); ?></th>
							<th style="width:150px;"><?= lang("unit_price"); ?></th>
							<th style="width:150px;" class="print-hidden"><?= lang("discount"); ?></th>
							<th style="width:150px;"><?= lang("grand_total"); ?></th>
							<th style="width:150px;"><?= lang("deposit"); ?></th>
							<th style="width:150px;"><?= lang("paid"); ?></th>
							<th style="width:150px;"><?= lang("invoice_paid"); ?></th>
                            <th style="width:150px;"><?= lang("commission_invoice"); ?></th>
							<th style="width:150px;"><?= lang("commission_amount"); ?></th>
							<th style="width:150px;"><?= lang("commission_paid"); ?></th>
							<th style="width:150px;"><?= lang("commission_balance"); ?></th>
							<th style="width:150px;"><?= lang("withdrawn"); ?></th>
							<th style="width:150px;"><?= lang("withdrawn_percent"); ?></th>
							<th style="width:150px;"><?= lang("withdraw_remain"); ?></th>
							<th style="width:150px;" class="print-hidden"><?= lang("status"); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php 

							$grand_total = 0;
							$total_deposit = 0;
							$total_discount = 0;
							$total_paid = 0;
							$total_invoice_paid = 0;
							$total_commission_invoice = 0;
							$total_commission_amount = 0;
							$total_commission_paid = 0;
							$total_commission_balance = 0;
							$total_commission_withdrawn = 0;
							$total_commission_withdraw_remain = 0;

							if(isset($sales) && $sales){
								foreach($sales as $row){
									$agencies = json_decode($row->agency_id);
									$agency_commission = json_decode($row->agency_commission);
									$agency_limit_percent = json_decode($row->agency_limit_percent);
									$agency_value_commission =  json_decode($row->agency_value_commission);
									if(isset($_GET['agency']) && !empty($_GET['agency'])){
										$agencies = array_filter($agencies, function($v){
											return $v == $_GET['agency'];
										});
									}
									if(isset($agencies) && $agencies){
										foreach($agencies as $i=> $agency){
											// Get agency
											$agency_details = $this->reports_model->getAgencyByID($agency);
											$agency_name = $agency_details->first_name.' '.$agency_details->last_name;
											$agency_rate = $agency_commission[$i]?$agency_commission[$i]:'';
											$amount = $agency_value_commission[$i] == 1? $row->grand_total : $row->real_unit_price;
											$commission_invoice = ($agency_rate * $amount) / 100;

											// Last commission
											$last_commission = 0;
											$lpaid_percentage = ($row->last_paid * 100) / $amount;
											if($lpaid_percentage > $agency_limit_percent[$i]){
												$last_commission = $commission_invoice;
											}else{
												$last_commission = ($row->last_paid / (($agency_limit_percent[$i] * $amount)/100)) * $commission_invoice;
											}

											// Commission paid
											$commission_amount = 0;
											$paid_percentage = ($row->paid * 100) / $amount;
											if($paid_percentage > $agency_limit_percent[$i]){
												$commission_amount = $commission_invoice;
											}else{
												$commission_amount = ($row->paid / (($agency_limit_percent[$i] * $amount)/100)) * $commission_invoice;
											}
											
											$total_commission = $commission_amount + $last_commission;
											if($total_commission > $commission_invoice){
												$commission_amount = $commission_invoice - $last_commission;
											}
											
											// Commission withdrawn
											$payments = $this->reports_model->getSaleAgencyPayments($row->id, $agency_details->id);
											$commission_withdrawn = 0;
											$commission_withdrawn_discount = 0;
											if($payments){
												foreach($payments as $payment){
													$commission_withdrawn += $payment->amount;
													$commission_withdrawn_discount += $payment->discount;
												}
											}

											// Commission paid
											$ppayments = $this->reports_model->getSaleAgencyPayments($row->id, $agency_details->id, true);
											$commission_paid = 0;
											$commission_paid_discount = 0;
											if($ppayments){
												foreach($ppayments as $ppayment){
													$commission_paid += $ppayment->amount;
													$commission_paid_discount += $ppayment->discount;
												}
											}

											$commission_balance = $this->cus->formatDecimal($commission_amount - $commission_paid - $commission_paid_discount);
											$commission_withdraw_remain = $this->cus->formatDecimal($commission_invoice - $commission_withdrawn - $commission_withdrawn_discount);
											$commission_withdrawn_percent = (($commission_withdrawn + $commission_withdrawn_discount) * 100) / $commission_invoice;
											if($commission_withdrawn<=0){
												$status = '<span class="label label-warning">'.lang('pending').'</span>';
											}else if($commission_withdraw_remain<=0){
												$status = '<span class="label label-success">'.lang('paid').'</span>';
											}else{
												$status = '<span class="label label-info">'.lang('partial').'</span>';
											}
											echo '<tr>
												<td>'.$this->cus->hrld($row->date).'</td>
												<td>'.$row->product_name.'</td>
												<td>'.$row->customer.'</td>
												<td>'.$agency_name.'</td>
												<td class="text-center">'.$agency_rate.'%</td>
												<td class="text-right">'.$this->cus->formatMoney($row->real_unit_price).'</td>
												<td class="text-right print-hidden">'.$this->cus->formatMoney($row->order_discount).'</td>
												<td class="text-right">'.$this->cus->formatMoney($row->grand_total).'</td>
												<td class="text-right" style="background:#EEC;">'.$this->cus->formatMoney($row->deposit).'</td>
												<td class="text-right" style="background:#EEE;">'.$this->cus->formatMoney($row->paid).'</td>
												<td class="text-right">'.$this->cus->formatMoney($row->invoice_paid).'</td>
												<td class="text-right">'.$this->cus->formatMoney($commission_invoice).'</td>
												<td class="text-right" style="background:#EEE;">'.$this->cus->formatDecimal($commission_amount).'</td>
												<td class="text-right">'.$this->cus->formatDecimal($commission_paid).'</td>
												<td class="text-right">'.$this->cus->formatDecimal($commission_balance).'</td>
												<td class="text-right">'.$this->cus->formatDecimal($commission_withdrawn).'</td>
												<td class="text-center">'.$this->cus->formatDecimal($commission_withdrawn_percent).' %</td>
												<td class="text-right">'.$this->cus->formatDecimal($commission_withdraw_remain).'</td>
												<td class="text-center print-hidden">'.$status.'</td>
											</tr>';
											$grand_total += $row->grand_total;
											$total_deposit += $row->deposit;
											$total_discount += $row->order_discount;
											$total_paid += $row->paid;
											$total_invoice_paid += $row->invoice_paid;
											$total_commission_withdrawn += $commission_withdrawn;
											$total_commission_amount += $commission_amount;
											$total_commission_paid += $commission_paid;
											$total_commission_balance += $commission_balance;
											$total_commission_invoice += $commission_invoice;
											$total_commission_withdraw_remain += $commission_withdraw_remain;
										}
									}
								}
							}
						?>
						</tbody>
						<tfoot class="dtFilter">
							<tr class="active">
								<th colspan="6" style="text-align:right;"><b><?=lang("total")?> : </b></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_discount)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($grand_total)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_deposit)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_paid)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_invoice_paid)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_commission_invoice)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_commission_amount)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_commission_paid)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_commission_balance)?></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_commission_withdrawn)?></th>
								<th></th>
								<th style="text-align:right;"><?=$this->cus->formatMoney($total_commission_withdraw_remain)?></th>
								<th></th>
						</tfoot>
					</table>
                </div>
                <div class="row">
					<div class="col-md-6 text-left"></div>
					<div class="col-md-6 text-right"><?=$links?></div>
            	</div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/agency_commission_export/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/agency_commission_export/0/xls/?v=1'.$v)?>";
            return false;
        });
		$("#biller").change(biller); biller();
		function biller(){
			var biller = $("#biller").val();
			var project = "<?= (isset($_GET['project']) ? trim($_GET['project']) : ''); ?>";
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
		var customer = "<?= isset($_GET['customer'])?$_GET['customer']:0; ?>";
		$('#rtcustomer').val(customer).select2({
		   minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customers/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });	
            },
			   ajax: {
				url: site.base_url+"customers/suggestions",
				dataType: 'json',
				quietMillis: 15,
				data: function (term, page) {
					return {
						term: term,
						limit: 10
					};
				},
				results: function (data, page) {
					if(data.results != null) {
						return { results: data.results };
					} else {
						return { results: [{id: '', text: 'No Match Found'}]};
					}
				}
			}
		});
    });
</script>