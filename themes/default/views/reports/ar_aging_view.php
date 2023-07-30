<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 10;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<button type="button" class="btn btn-xs btn-default no-print print_logo" style="margin-left:10px; margin-top:10px">
			<i class="fa fa-print"></i> <?= lang('print'); ?> Logo
		</button>
		<div class="modal-body">
			<table>
				<thead>
					<tr class="company_info" id="company_info">
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
									<td valign="bottom" style="width:50%"><hr class="hr_title"></td>
									<td class="text_center" style="width:40%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('CUSTOMER_BALANCE_STATEMENT') ?></i></b></span></td>
									<td valign="bottom" style="width:10%"><hr class="hr_title"></td>
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
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('customer') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('name') ?></td>
													<td> : <strong><?= $customer->company ?></strong></td>
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

								</tr>
							</table>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$tbody = '';
						$tfooter = '';
						$grand_total = 0; $paid = 0; $discount = 0; $total_balance = 0; $total_return = 0;	$i=1;
						if($rows){
							foreach ($rows as $row){
								$balance = $row->grand_total - ($row->paid) - ($row->discount)- ($row->grand_total_return);
								if($this->cus->formatDecimal($balance) !=0){
									$grand_total += $row->grand_total;
									$paid += $row->paid;
									$discount += $row->discount;
									$total_balance += $balance;
									$total_return += $row->grand_total_return;
									$tbody .='<tr>
												<td class="text_center">'.$i.'</td>
												<td class="text_center">'.$this->cus->hrld($row->date).'</td>
												<td class="text_center">'.$row->reference_no.'</td>
												<td class="text_right">'.$this->cus->formatMoney($row->grand_total).'</td>
												<td class="text_right">'.$this->cus->formatMoney($row->grand_total_return).'</td>
												<td class="text_right">'.$this->cus->formatMoney($row->paid).'</td>
												<td class="text_right">'.$this->cus->formatMoney($row->discount).'</td>
												<td class="text_right">'.$this->cus->formatMoney($balance).'</td>
											</tr>';
									$i++;		
								}	
							}
						}
						
						$tfooter .='<tr style="font-weight:bold !important">
									<td class="text_right" colspan="3">'.lang('total').'</td>
									<td class="text_right">'.$this->cus->formatMoney($grand_total).'</td>
									<td class="text_right">'.$this->cus->formatMoney($total_return).'</td>
									<td class="text_right">'.$this->cus->formatMoney($paid).'</td>
									<td class="text_right">'.$this->cus->formatMoney($discount).'</td>
									<td class="text_right">'.$this->cus->formatMoney($total_balance).'</td>
								</tr>';
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#") ?></th>
										<th><?= lang("date") ?></th>
										<th><?= lang("reference_no") ?></th>					
										<th><?= lang("grand_total") ?></th>
										<th><?= lang("return") ?></th>
										<th><?= lang("paid") ?></th>
										<th><?= lang("discount") ?></th>
										<th><?= lang("balance") ?></th>		
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
									<td class="text_center" style="width:50%"><?= lang("contractor") .' '. lang("signature") ?></td>
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
		.company_info{
			display:none !important;
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
		$(".print_logo").live("click",function(){
			$("#company_info").removeClass("company_info");
			window.print();
		});
		window.addEventListener("afterprint", function(event) { 
			$("#company_info").addClass("company_info");
		});
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
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>

