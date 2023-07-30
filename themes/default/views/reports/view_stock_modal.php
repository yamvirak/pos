<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 5;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
	
	$d_type = '<tr>
					<td>'.lang('type').'</td>
					<td> : <strong>'.($type=='minus' ? lang('subtraction') : lang('addition')).'</strong></td>
				</tr>';
	
	$d_start = '<tr>
					<td>'.lang('start_date').'</td>
					<td> : <strong>'.$start_date.'</strong></td>
				</tr>';
	$d_end = '<tr>
					<td>'.lang('end_date').'</td>
					<td> : <strong>'.$end_date.'</strong></td>
				</tr>';			
	
	
	if($transaction=='OpeningBalance'){
		$transaction = 'inventory_opening_balances';
	}else if($transaction=='Purchases' || $transaction=='Receives'){
		$transaction = 'purchases';
	}else if($transaction=='Sale' || $transaction=='Delivery'){
		$transaction = 'sales';
	}else if($transaction=='QuantityAdjustment'){
		$transaction = 'quantity_adjustments';
	}else if($transaction=='Transfer'){
		$transaction = 'transfers';
	}else if($transaction=='UsingStock'){
		$transaction = 'using_stocks';
	}else if($transaction=='Convert'){
		$transaction = 'converts';
	}else if($transaction=='Pawns'){
		$transaction = 'pawns';
	}else if($transaction=='balance'){
		$transaction = 'ending_balance';
		$d_type = '';
		$d_end = '';
		$d_start = '<tr>
						<td>'.lang('end_date').'</td>
						<td> : <strong>'.$end_date.'</strong></td>
					</tr>';
	}else if($transaction=='begin'){
		$transaction = 'begin_balance';
		$d_type = '';
		$d_end = '';
		$d_start = '<tr>
						<td>'.lang('end_date').'</td>
						<td> : <strong>'.$start_date.'</strong></td>
					</tr>';
	}
	
	
?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
			<table>
				<thead>
					<tr>
						<th>
							<table>
								<tr>
									<td class="text_center" style="width:20%">
										<?php
											if($logo){
												echo '<img height="80px" src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
											}
										?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
										<div><?= $biller->address.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:60%"><hr class="hr_title"></td>
									<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('inventory_in_out') ?></i></b></span></td>
									<td valign="bottom" style="width:20%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td style="width:100%">
										<fieldset>
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang($transaction) ?></i></b></legend>
											<table>
												<?php if($warehouse){ ?>
													<tr>
														<td><?= lang('warehouse') ?></td>
														<td> : <strong><?= $warehouse->name ?></strong></td>
													</tr>
												<?php } ?>
												<tr>
													<td><?= lang('product') ?></td>
													<td> : <strong><?= $product->code.' - '.$product->name ?></strong></td>
												</tr>
												
												<?php if($expiry){ ?>
													<tr>
														<td><?= lang('expiry') ?></td>
														<td> : <strong><?= $expiry ?></strong></td>
													</tr>
												<?php } ?>
												
												<?= $d_type.$d_start.$d_end ?>
											</table>
										</fieldset>
									</td>

								</tr>
							</table>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$tbody = '';
						$tfooter = '';
						$i = 1;
						$total_qty = 0;
						$total_amount = 0;
						if($rows){
							foreach($rows as $row){
								if($row->quantity !='' && $row->quantity != 0){
									$total_qty += $row->quantity;
									$amount = $row->real_unit_cost * $row->quantity;
									$total_amount += $amount;
									$td_stock_value = '';
									if($Owner || $Admin || $this->session->userdata('show_cost')){ 
										$td_stock_value = '<td class="text_right">'.$this->cus->formatMoney($row->real_unit_cost).'</td>
															<td class="text_right">'.$this->cus->formatMoney($amount).'</td>';
									}
									
									$tbody .= '<tr>
													<td class="text_center">'.$i++.'</td>
													<td class="text_center">'.$this->cus->hrsd($row->date).'</td>
													<td class="text_center">'.$row->reference_no.'</td>
													<td class="text_left">'.$row->last_name.' '.$row->first_name.'</td>
													<td	class="text_right">'.$this->cus->formatQuantity($row->quantity).'</td>
													'.$td_stock_value.'
												</tr>';
								}
								
							}
							$tfooter = '<tr>
											<td class="text_right" colspan="4"><b>'.lang('total').'</b></td>
											<td	class="text_right"><b>'.$this->cus->convertQty($product->id,$total_qty).'</b></td>
											'.($Owner || $Admin || $this->session->userdata('show_cost') ? '<td	class="text_right" colspan="2"><b>'.$this->cus->formatMoney($total_amount).'</b></td>' : '').'
										</tr>';
						}else{
							$tbody = '<tr><td colspan="7">'.lang('sEmptyTable').'</td></tr>';
						}
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#") ?></th>
										<th><?= lang("date") ?></th>
										<th><?= lang("reference_no") ?></th>
										<th><?= lang("created_by") ?></th>
										<th><?= lang("quantity") ?></th>
										<?php if($Owner || $Admin || $this->session->userdata('show_cost')){  ?>
											<th><?= lang("cost") ?></th>
											<th><?= lang("amount") ?></th>
										<?php } ?>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tbody id="tfooter">
									<?= $tfooter ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr class="tr_print">
						<td>
							<table style="margin-top:<?= $margin_signature ?>px;">
								<tr>
									<td class="text_center" style="width:50%"><?= lang("prepared_by") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:50%"></td>
								</tr>
								<tr>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:50%; padding-top:60px"></td>
								</tr>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
	
			<div id="buttons" style="padding-top:10px;" class="no-print">
				<hr>
				<div class="btn-group btn-group-justified">
					<div class="btn-group">
						<a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
							<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
							<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	@media print{
		.no-print{
			display:none !important;
		}
		.tr_print{
			display:table-row !important;
		}
		@page{
			margin: 5mm; 
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;         
		}
	}
	.tr_print{
		display:none;
	}
	#tbody .td_print{
		border:none !important;
		border-left:1px solid black !important;
		border-right:1px solid black !important;
		border-bottom:1px solid black !important;
	}
	.hr_title{
		border:3px double #7daaf2 !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item th{
		border:1px solid black !important;
		background-color : #7daaf2 !important;
		text-align:center !important;
		line-height:30px !important;
	}
	.table_item td{
		border:1px solid black;
		line-height:<?=$td_line_height?>px !important;
	}
	.footer_des[rowspan] {
	  vertical-align: top !important;
	  text-align: left !important;
	  border:0px !important;
	}
	
	.text_center{
		text-align:center !important;
	}
	.text_left{
		text-align:left !important;
		padding-left:3px !important;
	}
	.text_right{
		text-align:right !important;
		padding-right:3px !important;
	}
	
	fieldset{
		-moz-border-radius: 9px !important;
		-webkit-border-radius: 15px !important;
		border-radius:9px !important;
		border:2px solid #7daaf2 !important;
		min-height:<?= $min_height ?>px !important;
		margin-bottom : <?= $margin ?>px !important;
		padding-left : <?= $margin ?>px !important;
	}

	legend{
		width: initial !important;
		margin-bottom: initial !important;
		border: initial !important;
	}
	
	.modal table{
		width:100% !important;
		font-size:<?= $font_size ?>px !important;
		border-collapse: collapse !important;
	}
</style>

<script type="text/javascript">
    $(document).ready( function() {
		window.addEventListener("beforeprint", function(event) { addTr();});
		function addTr(){
			$('.blank_tr').remove();
			var page_height = <?= $max_row_limit ?>;
			var form_height = $('.table_item').height()-0;
			if(form_height > page_height && (form_height - page_height) > 15){
				var pages = Math.ceil(form_height / page_height);
				page_height = (page_height - (15 * (pages + 1))) * pages;
			}
			var blank_height = page_height - form_height;
			if(blank_height > 0){
				var td_html = '<tr class="tr_print blank_tr">';
					td_html +='<td class="td_print"><div style="height:'+blank_height+'px !important">&nbsp;</div></td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					<?php if($Owner || $Admin || $this->session->userdata('show_cost')){  ?>
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
					<?php } ?>
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>

