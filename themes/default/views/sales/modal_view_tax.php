<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 150;
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

	$inv_currencies = json_decode($inv->currencies);
		if($inv_currencies){
			$currencies = false;
			foreach($inv_currencies as $currency){
				$currencies[$currency->currency] = $currency;
			}
			$currency = $currencies['KHR'];
		}
	?>	
	<table>
		<thead style="display:table-header-group !important;">
			<tr>
					<th>
						<table style="margin-top: 5px;">
                            <tr>
                                <td class="text_left" style="width:15%">
                                     <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                </td>
                                <td></td>
                                 <td class="text_center" style="width:70%">
                                    <div>
                                        <strong style="font-size:18px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:18px";><?= $biller->name;?></strong>
                                    </div>
                                    <div class="font_address"><?= $biller->address?></div>
                                    <div class="font_address"><?= lang('tel').' : '. $biller->phone ?></div> 
                                    <div class="font_address"><?= lang('email').' : '. $biller->email ?></div>  
                                    <div style="padding-bottom:5px;padding-top:5px;">
                                    
                                        <?php if($biller->vat_no!="" || $biller->vat_no != NULL){
                                            echo "លេខអត្តសញ្ញាណកម្ម អតប (VATTIN):";
                                        $vat_no = str_split($biller->vat_no);
                                        ?>
                                            <span style="margin-bottom:4px;">
                                                
                                                <?php 
                                                    foreach($vat_no as $v){
                                                        if($v == "-"){
                                                            echo "<span style='padding:0px 3px; margin:3 5px;'>".$v."</span>";
                                                        }else{
                                                            echo "<span style='border:1px solid #999; padding:3px 5px; margin:0 1px;'>".$v."</span>";
                                                        }
                                                    }
                                                ?>
                                            </span>
                                        <?php } ?>
                                    </div> 
                                </td> 
                                <td class="text_center" style="width:15%">
                                    <?= $this->cus->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>
                                </td>
                            </tr>
                        </table>
					</th>
				</tr>
			<tr>
				<th>
					<table style="margin-top:10px">
						<tr>
							<td class="text_center" style="width:100%"><span style="font-size:<?= $font_size+6 ?>px"><b><?= lang('វិក័យបត្រអាករ/TAX INVOICE') ?></b></span></td>
						</tr>
						<tr>
							<td valign="bottom" style="width:100%"><hr class="hr_title"></td>
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
												<?= lang('Company Name / Customer ') ?> : <?= $customer->name ?>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="vertical-align:top !important;">
												<?= lang('អាសយដ្ឋាន /  Address') ?> : <?= $customer->address .' '. $customer->city?>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="vertical-align:top !important;">
												<?= lang('ទូរស័ព្ទលេខ  /  Tel') ?> : <?= $customer->phone ?>
											</td>
										</tr>
								
										<tr>
											<td colspan="2" style="vertical-align:top !important;">
												<div style="padding-bottom:5px;padding-top:5px;">
			                                        <?php if($customer->vat_no!="" || $customer->vat_no != NULL){
			                                            echo "លេខអត្តសញ្ញាណកម្ម អតប (VATTIN):";
			                                        $vat_no = str_split($customer->vat_no);
			                                        ?>
			                                            <br><span style="margin-bottom:4px;">
			                                                
			                                                <?php 
			                                                    foreach($vat_no as $v){
			                                                        if($v == "-"){
			                                                            echo "<span style='padding:0px 3px; margin:3 5px;'>".$v."</span>";
			                                                        }else{
			                                                            echo "<span style='border:1px solid #999; padding:3px 5px; margin:0 1px;'>".$v."</span>";
			                                                        }
			                                                    }
			                                                ?>
			                                            </span>
			                                        <?php } ?>
			                                    </div>
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
                                           	<td><?= lang('inv_po') ?>: <?= $inv->si_reference_no ?></td>
                                        </tr>
										<tr>
											<td style="vertical-align:top !important;">
												<?= lang('កាលបរិច្ឆេទ  / Date') ?> : <?= $this->cus->hrsd($inv->date) ?>
											</td>
										</tr>
										<tr>
											<td style="vertical-align:top !important;">
												<?= lang('អត្រាប្តូរប្រាក់/Exchange Rate') ?> : 1 ដុល្លារ = <?= $currency->rate;?>
											</td>

											<!-- <span style="font-style: italic;" class="bold">'.lang("អត្រាប្តូរប្រាក់").' : 1 ដុល្លារ = '.($currency->rate).'</span> -->
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
				
			
				$tbody = '';
				$i=1;
				foreach ($rows as $row){
					if ($inv->product_discount != 0) {
						$td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
					}else{
						$td_discount = '';
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
									<td class="text_right">'.$this->cus->formatMoney($row->subtotal).'</td>
								</tr>';		
					$i++;
				}
				
				$footer_colspan = 3;
				$footer_rowspan = 2;
				if($inv->product_discount != 0){
					$footer_colspan++;
				}
				if($inv->grand_total != $inv->total){
					$footer_rowspan++;
				}
				if($inv->order_discount != 0){
					$footer_rowspan++;
				}
				if($inv->total_tax != 0){
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

				
				$tfooter = '';
				$footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'"><span style="font-style: italic;" class="bold">'.lang("អត្រាប្តូរប្រាក់").' : 1 ដុល្លារ = '.($currency->rate).'</span>'.$this->cus->decode_html($biller->cf6).'</td>';
				if ($inv->grand_total != $inv->total) {
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
									<td class="text_right"><b>'. $this->cus->formatMoney($inv->order_discount).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				if ($inv->total_tax  != 0) {
					$tax = $this->site->getTaxRateByID($inv->order_tax_id);
					$tfooter .= '<tr class="hidden">
									'.$footer_note.'
									<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("អាករលើតម្លៃបន្ថែម") ." ". $this->cus->numberToKhmer($tax->rate).' %<br/>'.$tax->name.'</p></td>
									<td class="text_right"><b>'.$this->cus->formatMoney($inv->total_tax).'</b></td>
								</tr>';
					$footer_note = '';		
				}

				if ($inv->total_tax  != 0) {
					$tax = $this->site->getTaxRateByID10($inv->order_tax_id);
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right bold"><p style="margin:4px 0 4px 0px !important; line-height:18px;">'.lang("អាករលើតម្លៃបន្ថែម") ." ". $this->cus->numberToKhmer($tax->rate).' %<br/>'.$tax->name.'</p></td>
									<td class="text_right"><b>'.$this->cus->formatMoney($inv->total_tax).'</b></td>
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
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th>
									<p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("ល.រ"); ?><br/><?= lang("Nº"); ?></p>
								</th>
								<th style="width:370px;">
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
									<p style="margin:4px 0 4px 0px !important; line-height:18px;"><?= lang("ថ្លៃទំនិញ"); ?><br/><?= lang("Amount"); ?></p>
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
		border:3px double #428BCD !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item th{
		border:1px solid #000000 !important;
        background-color : #ddd !important;
		text-align:center !important;
		line-height:26px !important;
	}
	.table_item thead th, .table_item tfoot th {
	    color: #000000 !important;
	    border: 1px solid #000000 !important;
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

	p{
        margin: 0px !important;
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