<?php defined('BASEPATH') OR exit('No direct script access allowed');

$v = "";
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('saleman')) {
    $v .= "&saleman=" . $this->input->post('saleman');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<?php echo form_open('reports/ar_aging_details/', 'id="action-form"');	?>
<div class="box">
	<div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('ar_aging_details'); ?>
        </h2>
		<div class="box-icon">
			<ul class="btn-tasks">
				<li class="dropdown">
					<a href="#" class="toggle_up tip" title="<?= lang('hide_form'); ?>">
						<i class="icon fa fa-toggle-up"></i>
					</a>
				</li>
				<li class="dropdown">
					<a href="#" class="toggle_down tip" title="<?= lang('show_form'); ?>">
						<i class="icon fa fa-toggle-down"></i>
					</a>
				</li>
			</ul>
		</div>
		<div class="box-icon">
			<ul class="btn-tasks">
				
				<li class="dropdown"><a href="#" id="xls" data-action="export_excel" class="tip" title="<?= lang('download_xls'); ?>"><i class="icon fa fa-file-excel-o"></i></a></li>						
				
			</ul>
		</div>
	</div>				
	<?php echo form_close();?>
	<div class="box-content">
		<div class="row">
			<div class="col-lg-12">
				<p class="introtext"><?= lang('customize_report'); ?></p>
				<div id="form">
					<?php echo form_open("reports/ar_aging_details/"); ?>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="biller"><?= lang("biller"); ?></label>
									<?php
									$bl[""] = "";
									foreach ($billers as $biller) {
										$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
									}
									echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
									?>
								</div>
							</div>
							
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
									<?php
									$wh[""] = "";
									foreach ($warehouses as $warehouse) {
										$wh[$warehouse->id] = $warehouse->name;
									}
									echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
									?>
								</div>
							</div>
							
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="customer"><?= lang("customer"); ?></label>
									<?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="user"><?= lang("saleman"); ?></label>
									<?php
									$us[""] = "";
									foreach ($users as $user) {
										$us[$user->id] = $user->first_name . " " . $user->last_name;
									}
									echo form_dropdown('saleman', $us, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("saleman") . '"');
									?>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("start_date", "start_date"); ?>
									<?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("end_date", "end_date"); ?>
									<?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="controls"> 
								<?php echo form_submit('submit_sale_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
							</div>
						</div>
					<?php echo form_close(); ?>
				</div>
				<div class="clearfix"></div>
				
				<div class="table-responsive">
					<table id="First" class="table table-striped table-bordered table-condensed table-hover dtable">
						<thead>
							<tr>
								<th style="min-width:3%; width: 3%; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th width="200"><?php echo lang("customer"); ?></th>
								<th	width="120"><?php echo lang("current"); ?></th>
								<th	width="120"><?php echo lang("1 - 30 Days"); ?></th>
								<th	width="120"><?php echo lang("31 - 60 Days"); ?></th>
								<th	width="120"><?php echo lang("61 - 90 Days"); ?></th>
								<th	width="120"><?php echo lang("> 90 Days"); ?></th>
								<th	width="120"><?php echo lang("total"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
								if($rows){
									
									foreach($rows as $row){
										
										echo '<tr class="ar_aging" id="'.$row->id.'">
												<td><input type="checkbox" name="val[]" value="'.$row->id.'" class="checkbox checkbox multi-select input-xs" /></td>
												<td>'.$row->customer.'</td>
												<td class="right">'.$this->cus->formatMoney($row->balance0).'</td>
												<td class="right">'.$this->cus->formatMoney($row->balance1).'</td>
												<td class="right">'.$this->cus->formatMoney($row->balance2).'</td>
												<td class="right">'.$this->cus->formatMoney($row->balance3).'</td>
												<td class="right">'.$this->cus->formatMoney($row->balance4).'</td>
												<td class="right">'.$this->cus->formatMoney($row->total).'</td>
											</tr>';
											
										$parents = $this->reports_model->getARAgingByCustomerID($row->id,"total");
										if($parents){
											
											echo '<tr>
													<th class="text-center">'.lang("#").'</th>
													<th class="text-center">'.lang("reference_no").'</th>
													<th class="text-center">'.lang("saleman").'</th>							
													<th class="text-center">'.lang("grand_total").'</th>
													<th class="text-center">'.lang("return").'</th>
													<th class="text-center">'.lang("paid").'</th>
													<th class="text-center">'.lang("discount").'</th>
													<th class="text-center">'.lang("balance").'</th>
												 </tr>';
											
											$grand_total = 0; 
											$paid = 0; 
											$discount = 0; 
											$total_balance = 0; 
											$total_return = 0;
											$i	=	0;
											foreach($parents as $parent){
												$balance = $parent->grand_total - ($parent->amount) - ($parent->discount)- ($parent->grand_total_return);
												if($balance !=0){
													$grand_total 	+= $parent->grand_total;
													$paid 			+= $parent->paid;
													$discount 		+= $parent->discount;
													$total_balance 	+= $balance;
													$total_return 	+= $parent->grand_total_return;												
													echo '<tr class="warning">
															<td class="text-center">'.($i+1).'</td>
															<td class="text-left">'.$parent->reference_no.'</td>
															<td class="text-left">'.ucfirst($parent->saleman).'</td>							
															<td class="text-right">'.$this->cus->formatMoney($parent->grand_total).'</td>
															<td class="text-right">'.$this->cus->formatMoney($parent->grand_total_return).'</td>
															<td class="text-right">'.$this->cus->formatMoney($parent->amount).'</td>
															<td class="text-right">'.$this->cus->formatMoney($parent->discount).'</td>
															<td class="text-right">'.$this->cus->formatMoney($parent->grand_total-($parent->paid+$parent->discount+$parent->grand_total_return)).'</td>								
														</tr>';
													$i++;
												}
											}
											echo '<tr>
													<th></th>
													<th></th>
													<th></th>
													<th class="text-right">'.$this->cus->formatMoney($grand_total).'</th>
													<th class="text-right">'.$this->cus->formatMoney($total_return).'</th>
													<th class="text-right">'.$this->cus->formatMoney($paid).'</th>
													<th class="text-right">'.$this->cus->formatMoney($discount).'</th>
													<th class="text-right">'.$this->cus->formatMoney($total_balance).'</th>							
												</tr>';
										}
									}
								}else{ ?>
									<tr>
										<td colspan="13" class="dataTables_empty">
											<?php echo $this->lang->line("datatables_lang")['sZeroRecords']; ?>
										</td>
									</tr>
								<?php } ?>
						</tbody>
						<tfoot class="dtFilter">
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>			
</div>
	
<style type="text/css">
	.dtable { white-space:nowrap; }
</style>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#xls').click(function (event) {
			var customer_id = ''; var i = 0;
			$('.multi-select').each(function(){
				if ($(this).is(':checked')) {
					if(i==0){
						customer_id += "'"+$(this).val()+"'";
						i=1;
					}else{
						customer_id += ",'"+$(this).val()+"'";
					}
				}
			});
			if(customer_id==''){
				alert("<?= lang('no_sale_selected') ?>")
				return false;
			}else{
				var link = "<?=site_url('reports/getARAging/0/xls/0/1/?cid=')?>"+customer_id;
				event.preventDefault();
				window.location.href = link;
				return false;
			}
        });
		
		$('#pdf').click(function (event) {
			var customer_id = ''; var i = 0;
			$('.multi-select').each(function(){
				if ($(this).is(':checked')) {
					if(i==0){
						customer_id += "'"+$(this).val()+"'";
						i=1;
					}else{
						customer_id += ",'"+$(this).val()+"'";
					}
				}
			});
			if(customer_id==''){
				alert("<?= lang('no_sale_selected') ?>")
				return false;
			}else{
				var link = "<?=site_url('reports/getARAging/pdf/0/?cid=')?>"+customer_id;
				event.preventDefault();
				window.location.href = link;
				return false;
			}
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
	});
</script>

