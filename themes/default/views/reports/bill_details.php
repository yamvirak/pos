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
        <h2 class="#428BCA"><i class="fa-fw fa fa-calendar"></i><?= lang('bill_details_report').' ('.(isset($sel_warehouse) ? $sel_warehouse->name : lang('all_warehouses')).')'; ?></h2>
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
                <p class="introtext"><?= lang("bill_details_report") ?></p>
				
				<div id="form">

                    <?php echo form_open("reports/bill_details"); ?>
                    
					<div class="row">
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("sale_reference_no"); ?></label>
                                <?php echo form_input('sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : ""), 'class="form-control tip" id="sale_reference_no"'); ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name .' '.$user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
						<?php if($this->config->item("saleman")){ ?>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="user"><?= lang("saleman"); ?></label>
									<?php
									$us[""] = lang('select').' '.lang('saleman');
									foreach ($users as $user) {
										$us[$user->id] = $user->first_name .' '.$user->last_name;
									}
									echo form_dropdown('saleman', $us, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line("saleman") . " " . $this->lang->line("saleman") . '"');
									?>
								</div>
							</div>
						<?php } ?>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
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
                                <label class="control-label" for="show_item"><?= lang("show_details"); ?></label>
                                <?php
								$st[0] 	= lang('no');
								$st[1] 	= lang('yes');
                                echo form_dropdown('show_details', $st, (isset($_POST['show_details']) ? $_POST['show_details'] : 0), 'class="form-control" id="show_details" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("show_details") . '"');
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
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
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
                    <table id="blTable" border='1' class="table table-bordered table-condensed dfTable reports-table">
                        <thead>
							<th style="width:200px !important;"><?= lang("date") ?></th>
							<th style="width:200px !important;"><?= lang("reference_no") ?></th>
							<th style="width:200px !important;"><?= lang("biller") ?></th>
							<th style="width:200px !important;"><?= lang("customer") ?></th>
							<th style="width:200px !important;"><?= lang("sale_reference_no") ?></th>
							<th style="width:120px !important;"><?= lang("table") ?></th>
							<th style="width:120px !important;"><?= lang("created_by") ?></th>
							<th style="width:120px !important;"><?= lang("total") ?></th>
							<th style="width:120px !important;"><?= lang("discount") ?></th>
							<th style="width:120px !important;"><?= lang("print") ?></th>
							<th style="width:120px !important;"><?= lang("printed_by") ?></th>
							<?php if(!$this->input->post('show_details')){ ?>
								<th style="width:100px !important;"><?= lang("count") ?></th>
							<?php } ?>
							<th style="width:100px !important;"><?= lang("status") ?></th>
                        </thead>
						<tbody>
							<?php 
								if($bills){
									foreach($bills as $bill){
										$bill_details = $this->reports_model->getBillItemsDetails($bill->id);
										$show_details = '';
										if(!$this->input->post('show_details')){
											$show_details = '<td>'.$this->cus->row_status($bill->count).'</td>';
										}
										echo '<tr>
												<td>'.$this->cus->hrld($bill->date).'</td>
												<td>'.$bill->reference_no.'</td>
												<td>'.$bill->biller.'</td>
												<td>'.$bill->customer.'</td>
												<td>'.$bill->sale_ref.'</td>
												<td>'.$bill->table.'</td>
												<td>'.$bill->user.'</td>
												<td style="text-align:right;">'.$this->cus->formatMoney($bill->total).'</td>
												<td style="text-align:right;">'.$this->cus->formatMoney($bill->discount).'</td>
												<td style="text-align:center;">'.$bill->print.'</td>
												<td>'.$bill->puser.'</td>
												'.$show_details.'
												<td style="text-align:center;">'.$this->cus->row_status($bill->suspend_status).'</td>
											</tr>';
											
										if($bill_details && $this->input->post('show_item')){
												echo '<tr style="font-weight:bold;">
														  <td colspan="2"></td>
														  <td>'.lang("product_code").'</td>
														  <td colspan="2">'.lang("product_name").'</td>
														  <td>'.lang("quantity").'</td>
														  <td>'.lang("unit_price").'</td>
														  <td>'.lang("item_discount").'</td>
														  <td>'.lang("subtotal").'</td>
														  <td colspan="4"></td>
														</tr>';
											foreach($bill_details as $bill_detail){
												echo '<tr>
														  <td colspan="2"></td>
														  <td>'.$bill_detail->product_code.'</td>
														  <td style="text-align:left;" colspan="2">'.$bill_detail->product_name.'</td>
														  <td style="text-align:center;">'.$this->cus->formatQuantity($bill_detail->quantity).'</td>
														  <td style="text-align:right;">'.$this->cus->formatMoney($bill_detail->unit_price).'</td>
														  <td style="text-align:right;">'.$this->cus->formatMoney($bill_detail->item_discount).'</td>
														  <td style="text-align:right;">'.$this->cus->formatMoney($bill_detail->subtotal).'</td>
														  <td colspan="4"></td>
														</tr>';
											}
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
		
		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:no-wrap; } table th, table td{ font-size:9px !important; }</style>' + $('.table-responsive').html());
			this.href = result;
			this.download = "bill_details_report.xls";
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
