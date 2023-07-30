<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$v = "";
	
	if($this->input->post("reference_no")){
		$v .= "&reference_no=" . $this->input->post("reference_no");
	}
	if($this->input->post("user")){
		$v .= "&user=" . $this->input->post("user");
	}
	if($this->input->post("saleman")){
		$v .= "&saleman=" . $this->input->post("saleman");
	}
	if($this->input->post("customer")){
		$v .= "&customer=" . $this->input->post("customer");
	}
	if($this->input->post("biller")){
		$v .= "&biller=" . $this->input->post("biller");
	}
	if($this->input->post("start_date")){
		$v .= "&start_date=" . $this->input->post("start_date");
	}
	if($this->input->post("end_date")){
		$v .= "&end_date=" . $this->input->post("end_date");
	}
	if($this->input->post("payment_status")){
		$v .= "&payment_status=" . $this->input->post("payment_status");
	}
	if($this->input->post("show_item")){
		$v .= "&show_item=" . $this->input->post("show_item");
	}
?>

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
</style>
<div class="box">
    <div class="box-header">
        <h2 class="#428BCA">
			<i class="fa-fw fa fa-calendar"></i><?= lang('saleman_detail_report').' ('.(isset($sel_warehouse) ? $sel_warehouse->name : lang('all_warehouses')).')'; ?></h2>

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
                <p class="introtext"><?= lang("saleman_detail_report") ?></p>
				
				<div id="form">

                    <?php echo form_open("reports/saleman_detail_report"); ?>
                    
					<div class="row">
						
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
                                    $us[$user->id] = $user->last_name .' '.$user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("saleman"); ?></label>
                                <?php
                                $opsalemans[""] = lang('select').' '.lang('saleman');
                                foreach ($salemans as $saleman) {
                                    $opsalemans[$saleman->id] = $saleman->last_name .' '.$saleman->first_name;
                                }
                                echo form_dropdown('saleman', $opsalemans, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line("saleman") . " " . $this->lang->line("saleman") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
						
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
                                <label class="control-label" for="payment_status"><?= lang("payment_status"); ?></label>
                                <?php
                                $ps[""] = lang('select').' '.lang('payment_status');
								$ps["pending"] 	= lang('pending');
								$ps["partial"] 	= lang('partial');
								$ps["paid"] 	= lang('paid');
                                echo form_dropdown('payment_status', $ps, (isset($_POST['payment_status']) ? $_POST['payment_status'] : ""), 'class="form-control" id="payment_status" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("payment_status") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="show_item"><?= lang("show_item"); ?></label>
                                <?php
								$st[0] 	= lang('no');
								$st[1] 	= lang('yes');
                                echo form_dropdown('show_item', $st, (isset($_POST['show_item']) ? $_POST['show_item'] : 0), 'class="form-control" id="show_item" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("show_item") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control datetime" id="end_date"'); ?>
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
                    <table id="IVTable" border='1' class="table table-bordered table-condensed dfTable reports-table">
                        <thead>
							<th><?= lang("date") ?></th>
							<th style="width:100px !important;"><?= lang("reference_no") ?></th>
							<th style="width:100px !important;"><?= lang("biller") ?></th>
							<th style="width:100px !important;"><?= lang("customer") ?></th>
							<th style="width:100px !important;"><?= lang("status") ?></th>
							<th style="width:100px !important;"><?= lang("created_by") ?></th>
							<th style="width:100px !important;"><?= lang("grand_total") ?></th>
							<th style="width:100px !important;"><?= lang("paid") ?></th>
							<th style="width:100px !important;"><?= lang("discount") ?></th>
							<th style="width:100px !important;"><?= lang("balance") ?></th>
							<th style="width:100px !important;"><?= lang("payment_status") ?></th>
                        </thead>
						<tbody>
						<?php
							if (isset($salemans) && $salemans != false) {
								foreach($salemans as $saleman){								
									$sales = $this->reports_model->getAllSaleBySalemanId($saleman->id, $this->input->post());
									if($sales){
										
									echo '<tr>
												<td colspan="11" class="bold left">
													<i class="	fa fa-chevron-circle-right"></i>
													'.ucfirst($saleman->first_name.' '.$saleman->last_name).'
												</td>
											</tr>';
								
									$grand_total 	= 0; 
									$paid 			= 0;
									$balance 		= 0; 
									$discount		= 0;
									$show_item 		= $this->input->post('show_item');
									
									foreach($sales as $sale){
										$user 			 = $this->site->getUser($sale->created_by);
										$grand_total 	+= $sale->grand_total;
										$balance 		+= $sale->grand_total - $sale->paid;
										$payment 		 = $this->reports_model->getPaymentBySaleID($sale->id);
										if($payment){
											$paid 			+= $payment->paid;
											$discount 		+= $payment->discount;
										}
										echo '<tr>
													<td>'.$this->cus->hrsd($sale->date).'</td>
													<td>'.$sale->reference_no.'</td>
													<td>'.$sale->biller.'</td>
													<td>'.$sale->customer.'</td>
													<td>'.$this->cus->row_status($sale->sale_status).'</td>
													<td>'.($user->last_name.' '.$user->first_name).'</td>
													<td class="right">'. $this->cus->formatMoney($sale->grand_total).'</td>
													<td class="right">'. $this->cus->formatMoney(($payment ? $payment->paid : 0)).'</td>
													<td class="right">'. $this->cus->formatMoney(($payment ? $payment->discount : 0)).'</td>
													<td class="right">'. $this->cus->formatMoney($sale->grand_total - ($payment ? ($payment->paid+$payment->discount) : 0)).'</td>
													<td>'. $this->cus->row_status($sale->payment_status).'</td>
												</tr>';
												
										if($show_item){
											
											$sale_items = $this->reports_model->getSaleItemsBySaleID($sale->id);
											
											if(count($sale_items)  > 0){
													
													echo '<tr>
																<td style="background:#EEE; font-weight:bold;">'.lang("Nº").'</td>
																<td style="background:#EEE; font-weight:bold;">'.lang("item").'</td>
																<td style="background:#EEE; font-weight:bold;">'.lang("quantity").'</td>
																<td style="background:#EEE; font-weight:bold;">'.lang("unit_price").'</td>
																<td style="background:#EEE; font-weight:bold;">'.lang("item_discount").'</td>
																<td style="background:#EEE; font-weight:bold;">'.lang("item_tax").'</td>
																<td style="background:#EEE; font-weight:bold;">'.lang("subtotal").'</td>
																<td style="background:#EEE;" colspan="4"></td>
															</tr>';
															
												foreach($sale_items as $key => $sale_item){
													
													echo '<tr>
																<td style="background:#EEE;">'.($key+1).'</td>
																<td style="background:#EEE; text-align:left !important;">'.$sale_item->product_name.'</td>
																<td style="background:#EEE; text-align:center !important;">'.$this->cus->formatDecimal($sale_item->quantity).'</td>
																<td style="background:#EEE; text-align:right !important;">'.$this->cus->formatMoney($sale_item->unit_price).'</td>
																<td style="background:#EEE; text-align:right !important;">'.$this->cus->formatMoney($sale_item->item_discount).'</td>
																<td style="background:#EEE; text-align:right !important;">'.$this->cus->formatMoney($sale_item->item_tax).'</td>
																<td style="background:#EEE; text-align:right !important;">'.$this->cus->formatMoney($sale_item->subtotal).'</td>
																<td style="background:#EEE;" colspan="4"></td>
															</tr>';
												}
											}
										}
									} 
									echo '<tr>
												<td colspan="6"></td>
												<td class="right bold">'.$this->cus->formatMoney($grand_total).'</td>
												<td class="right bold">'.$this->cus->formatMoney($paid).'</td>
												<td class="right bold">'.$this->cus->formatMoney($discount).'</td>
												<td class="right bold">'.$this->cus->formatMoney($balance).'</td>
												<td></td>
											</tr>';
								}
								
							}
							}
							 
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
		var customer_id = "<?= isset($_POST['customer'])?$_POST['customer']:0 ?>";
		if (customer_id > 0) {
		  $('#customer_id').val(customer_id).select2({
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
			  url: site.base_url + "customers/suggestions",
			  dataType: 'json',
			  deietMillis: 15,
			  data: function (term, page) {
				return {
				  term: term,
				  limit: 10
				};
			  },
			  results: function (data, page) {
				if (data.results != null) {
				  return {results: data.results};
				} else {
				  return {results: [{id: '', text: 'No Match Found'}]};
				}
			  }
			}
		  });
		}else{
		  $('#customer_id').select2({
			minimumInputLength: 1,
			ajax: {
			  url: site.base_url + "customers/suggestions",
			  dataType: 'json',
			  quietMillis: 15,
			  data: function (term, page) {
				return {
				  term: term,
				  limit: 10
				};
			  },
			  results: function (data, page) {
				if (data.results != null) {
				  return {results: data.results};
				} else {
				  return {results: [{id: '', text: 'No Match Found'}]};
				}
			  }
			}
		  });
		}
		$("#xls").click(function(e) {
			event.preventDefault();
			window.location.href = "<?=site_url('reports/saleman_detail_report_action/0/xls/?v=1'.$v)?>";
			return false;		
		});
		
		$("#pdf").click(function(e) {
			event.preventDefault();
			window.location.href = "<?=site_url('reports/saleman_detail_report_action/1/xls/?v=1'.$v)?>";
			return false;		
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
		
		$("#biller").change(biller); 
		biller();
		
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
