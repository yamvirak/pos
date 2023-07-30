<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 20;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
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
											echo '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
										?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
										<div><?= $biller->address.' '.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										<?= $this->cus->qrcode('link', urlencode(site_url('sales/modal_view_sale_concrete/' . $sale->id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:55%"><hr class="hr_title"></td>
									<td class="text_center" style="width:30%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('invoice') ?></i></b></span></td>
									<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td style="width:60%">
										<fieldset>
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('customer') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('code') ?></td>
													<td> : <strong><?= $customer->code ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('name') ?></td>
													<td> : <?= $customer->company ?></td>
												</tr>
												<tr>
													<td><?= lang('location') ?></td>
													<td> : <?= $sale->location_name ?></td>
												</tr>
												<tr>
													<td><?= lang('address') ?></td>
													<td> : <?= $customer->address ?></td>
												</tr>
												<tr>
													<td><?= lang('tel') ?></td>
													<td> : <?= $customer->phone ?></td>
												</tr>
											</table>
										</fieldset>
									</td>
									<td style="width:40%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('ref') ?></td>
													<td style="text-align:left"> : <b><?= $sale->reference_no ?></b></td>
												</tr>
												<tr>
													<td><?= lang('date') ?></td>
													<td style="text-align:left"> : <?= $this->cus->hrsd($sale->date) ?></td>
												</tr>
												<tr>
													<td><?= lang('from_date') ?></td>
													<td style="text-align:left"> : <?= $this->cus->hrld($sale->from_date) ?></td>
												</tr>
												<tr>
													<td><?= lang('to_date') ?></td>
													<td style="text-align:left"> : <?= $this->cus->hrld($sale->to_date) ?></td>
												</tr>
												<?php if($sale->due_date){ ?>
													<tr>
														<td><?= lang('due_date') ?></td>
														<td style="text-align:left"> : <?= $this->cus->hrld($sale->due_date) ?></td>
													</tr>
												<?php } ?>
												
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
						$i=1;
						$subtotal = 0;
						if($sale_items){
							foreach ($sale_items as $sale_item){
								$subtotal_item = $sale_item->unit_price * $sale_item->quantity;
								$tbody .='<tr>
												<td class="text_center">'.$i.'</td>
												<td class="text_left">'.$sale_item->product_name .'</td>
												<td class="text_center">'.$this->cus->formatQuantity($sale_item->quantity).'</td>
												<td class="text_right">'.$this->cus->formatMoney($sale_item->unit_price).'</td>
												<td class="text_right">'.$this->cus->formatMoney($subtotal_item).'</td>
											</tr>';
								$subtotal += $subtotal_item;			
								$i++;
							}
						}

						$footer_colspan = 3;
						$footer_rowspan = 1;
						if($sale->grand_total != $subtotal){
							$footer_rowspan++;
						}
						if($truck_charge->truck_charge > 0){
							$footer_rowspan++;
						}
						if($truck_charge->pump_charge > 0){
							$footer_rowspan++;
						}
						if($sale->order_discount != 0){
							$footer_rowspan++;
						}
						if($sale->order_tax != 0){
							$footer_rowspan++;
						}
						$tfooter = '';
						$footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->cus->decode_html($sale->note).'</td>';
						if ($sale->grand_total != $subtotal) {
							$tfooter .= '<tr>
											'.$footer_note.'
											<td class="text_right"><b>'.lang("total").'</b></td>
											<td class="text_right"><b>'.$this->cus->formatMoney($subtotal).'</b></td>
										</tr>';
							$footer_note = '';		
						}
						if ($truck_charge->truck_charge > 0) {
							$tfooter .= '<tr>
											'.$footer_note.'
											<td class="text_right"><b>'.lang("truck_charge").'</b></td>
											<td class="text_right"><b>'. $this->cus->formatMoney($truck_charge->truck_charge).'</b></td>
										</tr>';
							$footer_note = '';		
						}
						if ($truck_charge->pump_charge > 0) {
							$tfooter .= '<tr>
											'.$footer_note.'
											<td class="text_right"><b>'.lang("pump_charge").'</b></td>
											<td class="text_right"><b>'. $this->cus->formatMoney($truck_charge->pump_charge).'</b></td>
										</tr>';
							$footer_note = '';		
						}
						if ($sale->order_discount != 0) {
							$tfooter .= '<tr>
											'.$footer_note.'
											<td class="text_right"><b>'.lang("order_discount").'</b></td>
											<td class="text_right"><b>'.$this->cus->formatMoney($sale->order_discount).'</b></td>
										</tr>';
							$footer_note = '';		
						}
						if ($sale->order_tax  != 0) {
							$tfooter .= '<tr>
											'.$footer_note.'
											<td class="text_right"><b>'.lang("order_tax").'</b></td>
											<td class="text_right"><b>'.$this->cus->formatMoney($sale->order_tax).'</b></td>
										</tr>';
							$footer_note = '';		
						}
						$tfooter .= '<tr>
										'.$footer_note.'
										<td class="text_right"><b>'.lang("grand_total").'</b></td>
										<td class="text_right"><b>'.$this->cus->formatMoney($sale->grand_total).'</b></td>
									</tr>';
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("stregth"); ?></th>
										<th><?= lang("quantity"); ?></th>
										<th><?= lang("unit_price"); ?></th>
										<th><?= lang("subtotal"); ?></th>
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
							<table style="margin-top:15px;">
								<tr>
									<td class="text_center" style="width:50%"><?= lang("preparer") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:50%"><?= lang("customer").' '. lang("signature") ?></td>
								</tr>
								<tr>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
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
							<i class="fa fa-close"></i>
							<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
							<i class="fa fa-print"></i>
							<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
						</a>
					</div>
					<?php if ($sale->attachment) { ?>
						<div class="btn-group">
							<a href="<?= site_url('welcome/download/' . $sale->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
								<i class="fa fa-chain"></i>
								<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
							</a>
						</div>
					<?php } ?>
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
		.modal-dialog{
			<?= $hide_print ?>
		}
		#myModal .main_content {
            display: none !important;
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
		border:3px double #428BCD !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item th{
		border:1px solid black !important;
		background-color : #428BCD !important;
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
		border:2px solid #428BCD !important;
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
				var tfooter = $('#tfooter').height() - 30;
				page_height = page_height + tfooter
			}
			var blank_height = page_height - form_height;
			if(blank_height > 0){
				var td_html = '<tr class="tr_print blank_tr">';
					td_html +='<td class="td_print"><div style="height:'+blank_height+'px !important">&nbsp;</div></td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>

