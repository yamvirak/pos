<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 10;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg">
	<div class="no-print" style="height:15px;"></div>
	<?php if($Settings->watermark){
		echo '<p class="bg-text">';
			for($b=0; $b < 7; $b++){
				echo $biller->name.'<br>';
			}
		echo '</p>';
	}
	$hide_print = '';
	if($print==1){
		$hide_print = 'display:none !important;';
	}else if($print==2){
		if($Settings->watermark){
			echo '<p class="bg-text" style="transform:rotate(600deg) !important">';
				for($b=0; $b < 7; $b++){
					echo lang('re-print').'<br>';
				}
			echo '</p>';	
		}else{
			echo '<p class="bg-text">';
				for($b=0; $b < 7; $b++){
					echo lang('re-print').'<br>';
				}
			echo '</p>';
			
		}
		
	}
	?>	
	<table>
		<thead style="display:table-header-group !important;">
			<tr>
				<th>
					<table>
						<tr>
							<td class="text_center" style="width:17%">
								<?php
									if($logo){
										echo '<img width="130px" src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
									}
								?>
							</td>
							<td class="text_left" style="width:40%">
								<div style="font-size:<?= $font_size+7 ?>px"><b><?= $biller->company ?></b></div>
								<div><?= $biller->cf2 ?></div>
								<div><?= $biller->cf3 ?></div>
								<div><?= lang('ទូរស័ព្ទលេខ​').' : '. $biller->cf1 ?></div>	

							</td>
							<td style="width:3%"></td>
							<td class="text_right" style="width:40%">
								<div style="font-size:<?= $font_size+6 ?>px"><b><?= $biller->name ?></b></div>
								<div><?= $biller->address ?></div>
								<div><?= $biller->city ?></div>
								<div><?= lang('Phone').' : '. $biller->phone ?></div>	

							</td>
						</tr>
					</table>
				</th>
			</tr>
			<tr>
				<th>
					<table style="margin-top:10px; margin-bottom:10px">
						<tr>
							<td valign="bottom" style="width:10%"></td>
							<?php if ($inv->sale_status=='draft') { ?>
								<td class="text_center" style="width:80%"><span style="font-size:<?= $font_size+5 ?>px"><b><?= lang('openning_balance') ?></b></span></td>
							<?php }else{ ?>
								<td class="text_center" style="width:80%"><span style="font-size:<?= $font_size+5 ?>px"><b><?= lang('វិក្កយបត្រ/Commercial Invoice') ?></span></b></td>
							<?php } ?>
							
							<td valign="bottom" style="width:10%"></td>
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
									<table>
										<tr>
											<td colspan="2">
												<?= lang('ឈ្មោះក្រុមហ៊ុន ឬ អតិថិជន') ?> : <?= $customer->company ?>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="vertical-align:top !important;">
												<?= lang('Company Name/ Customer ') ?> : <?= $customer->name ?>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="vertical-align:top !important;">
												<?= lang('អាសយដ្ឋាន /  Address') ?> : <?= $customer->address .' '. $customer->city?>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="vertical-align:top !important;">
												<?= lang('ទូរស័ព្ទ​លេខ  /  Tel') ?> : <?= $customer->phone ?>
											</td>
										</tr>										
									</table>
								</fieldset>
							</td>
							<td style="width:40%; vertical-align:top !important;">
								<fieldset style="margin-left:5px !important">
									<table>
										<tr>
											<td>
												<?= lang('លេខវិក្កយបត្រ / Invoice Nº  ') ?> : <?= $inv->reference_no ?>
											</td>
										</tr>
										<tr>
											<td style="vertical-align:top !important;">
												<?= lang('កាលបរិច្ឆេទ  / Date') ?> : <?= $this->cus->hrsd($inv->date) ?>
											</td>
										</tr>
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
				$inv_currencies = json_decode($inv->currencies);
				if($inv_currencies){
					$currencies = false;
					foreach($inv_currencies as $currency){
						$currencies[$currency->currency] = $currency;
					}
					$currency = $currencies['KHR'];
				}
			
				$tbody = '';
				$i=1;
				foreach ($rows as $row){
					if ($inv->product_discount != 0) {
						$td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
					}else{
						$td_discount = '';
					}
					$subtotal = $row->subtotal;
					if ($inv->order_tax  != 0) {
						$tax = $this->site->getTaxRateByID($inv->order_tax_id);
						if($tax){
							$subtotal = $subtotal + ($subtotal * $tax->rate) / 100;
						}
					}

					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_left">
										'.($row->product_name ? '<span>'.$row->product_name.'</span>' : '').'
										<small>'. ($row->comment ? '<br/>'.$row->comment : '').' </small>
										<small>'. ($row->variant ? '<br/>(' . $row->variant . ')' : '').' </small>
									</td>
									<td class="text_center">'.$this->cus->formatQuantity($row->unit_quantity).' '.$row->unit_name.'</td>
									<td class="text_right">'.$this->cus->formatMoney($row->unit_price).'</td>
									'.$td_discount.'
									<td class="text_right">'.$this->cus->formatMoney($subtotal).'</td>
								</tr>';		
					$i++;
				}
				if ($inv->order_tax  != 0) {
					$inv->total += $inv->order_tax;
				}
				$footer_colspan = 3;
				$footer_rowspan = 2;
				if($inv->product_discount != 0){
					$footer_colspan++;
				}
				if($inv->order_discount != 0){
					$footer_rowspan++;
				}
				if($inv->order_tax != 0){
					$footer_rowspan++;
				}
				if($inv->shipping != 0){
					$footer_rowspan++;
				}
				
				if (isset($payment->paid) && $payment->paid != 0) {
					$footer_rowspan++;
				}
				if (isset($payment->discount) && $payment->discount != 0) {
					$footer_rowspan++;
				}	
				
				$payment_paid = isset($payment->paid)? $payment->paid : 0;
				$payment_discount = isset($payment->discount)? $payment->discount: 0;
				$amount_balance = $inv->grand_total - ($payment_paid + $payment_discount);
				if($amount_balance <> $inv->grand_total){
					$footer_rowspan++;
				}
				
				$tfooter = '';
				$footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'"><span style="font-style: italic;" class="bold">'.lang("exchange_rate").' : 1 USD = '.($currency->rate).'</span>'.$this->cus->decode_html($inv->note).'</td>';
				if ($this->cus->formatDecimal($inv->grand_total) != $this->cus->formatDecimal($inv->total)) {
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("សរុប").'<br/>'.lang("total").'</p></td>
									<td class="text_right"><b>'.$this->cus->formatMoney($inv->total).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				if ($inv->order_discount != 0) {
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("បញ្ចុះតម្លៃ").'<br/>'.lang("order_discount").'</p></td>
									<td class="text_right"><b>'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->cus->formatMoney($inv->order_discount).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				if ($inv->shipping  != 0) {
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("ថ្លៃដឹក").'<br/>'.lang("Shipping").'</p></td>
									<td class="text_right"><b>'.$this->cus->formatMoney($inv->shipping).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				
				$tfooter .= '<tr>
								'.$footer_note.'
								<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("សរុបរួម (ជាដុល្លារ)").'<br/>'.lang("Grand Total (in USD)").'</p></td>
								<td class="text_right"><b>'.$this->cus->formatMoney($inv->grand_total).'</b></td>
							</tr>
							<tr>
								<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("សរុបរួម (ជារៀល)").'<br/>'.lang("Grand Total (in Riel)").'</p></td>
								<td class="text_right"><b>'.$this->cus->formatKhMoney($inv->grand_total,$currency->rate).'</b></td>
							</tr>';
							
					$footer_note = '';		
				if (isset($payment->paid) && $payment->paid != 0) {
					$tfooter .= '<tr>
									<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("បានបង់").'<br/>'.lang("Paid").'</p></td>
									<td class="text_right"><b>'.$this->cus->formatMoney($payment->paid).'</b></td>
								</tr>';
					$footer_note = '';		
				}	
				if (isset($payment->discount) && $payment->discount != 0) {
					$tfooter .= '<tr>
									<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("បញ្ចុះតម្លៃ").'<br/>'.lang("Discount").'</p></td>
									<td class="text_right"><b>'.$this->cus->formatMoney($payment->discount).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				if($amount_balance <> $inv->grand_total){
					$tfooter .= '<tr>
									<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("នៅសល់").'<br/>'.lang("Balance").'</p></td>
									<td class="text_right"><b>'.$this->cus->formatMoney($amount_balance).'</b></td>
								</tr>';
					$footer_note = '';		
				}
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th>
									<p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("ល.រ"); ?><br/><?= lang("Nº"); ?></p>
								</th>
								<th>
									<p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("បរិយាយទំនិញ"); ?><br/><?= lang("Description"); ?></p>
								</th>
								<th>
									<p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("បរិមាណ"); ?><br/><?= lang("Quantity"); ?></p>
								</th>
								<th>
									<p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("ថ្លៃឯកតា"); ?><br/><?= lang("Unit Price"); ?></p>
								</th>
								<?php 
									if($inv->product_discount != 0){
										echo '<th><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("បញ្ចុះតម្លៃ").'<br/>'.lang("Discount").'</p></th>';
									}
								?>
								<th>
									<p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("ថ្លៃទំនិញរួមទាំងអាករ"); ?><br/><?= lang("Amount (VAT Included)"); ?></p>
								</th>
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
							<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
							<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
						</tr>
						<tr>
							<td class="text_center" style="width:50%"><p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("ហត្ថលេខានិងឈ្មោះអតិថិជន") ?><br/><?= lang("Customer's Signature & Name") ?></p></td>
							<td class="text_center" style="width:50%"><p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("ហត្ថលេខានិងឈ្មោះអ្នកលក់") ?><br/><?= lang("Seller's Signature & Name") ?></p></td>
						</tr>
					</table>
				</td>
			</tr>
		</tfoot>
	</table>
	
	<div class="clearfix"></div>
	
	<div class="buttons" style="margin-top:15px; margin-bottom:15px">
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
			<?php if ($inv->attachment) { ?>
				<div class="btn-group">
					<a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
						<i class="fa fa-chain"></i>
						<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
					</a>
				</div>
			<?php } ?>
			<?php if (!$inv->sale_id && $inv->sale_status!='draft' && $inv->type!='concrete') { ?>
				<div class="btn-group">
					<a href="<?= site_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
						<i class="fa fa-edit"></i>
						<span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
					</a>
				</div>
				<div class="btn-group">
					<a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_sale") ?></b>"
						data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
						data-html="true" data-placement="top">
						<i class="fa fa-trash-o"></i>
						<span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
					</a>
				</div>
			<?php } ?>
		</div>
	</div>
	
	<div class="clearfix"></div>
	
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
		.bg-text{
			display:block !important;
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
	.modal-dialog{
		background-color:white !important;
		padding-left:12px; !important;
		padding-right:12px; !important;
	}
	.hr_title{
		border:3px double #000000 !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item th{
		border:1px solid black !important;
		background-color : #428BCD !important;
		text-align:center !important;
		line-height:26px !important;
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
		border:none !important;
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
	
	.bg-text{
		opacity: 0.1;
		color:lightblack;
		font-size:100px;
		position:absolute;
		transform:rotate(300deg);
		-webkit-transform:rotate(300deg);
		display:none;
	}
</style>

<script type="text/javascript">
	
    $(document).ready( function() {
		window.onafterprint = function(){		
			$.ajax({
				url : site.base_url + "sales/add_print",
				dataType : "JSON",
				type : "GET",
				data : { 
						transaction_id : <?= $inv->id ?>,
						transaction : "Sale",
						reference_no : "<?= $inv->reference_no ?>"
					}
			});
		}
		
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
					<?php if ($inv->product_discount != 0) { ?>
						td_html +='<td class="td_print">&nbsp;</td>';
					<?php } ?>
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
    });

</script>