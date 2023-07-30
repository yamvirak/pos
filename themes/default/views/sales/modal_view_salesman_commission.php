<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 40;
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
											if($logo){
												echo '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
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
										<?= $this->cus->qrcode('link', urlencode(site_url('sales/modal_view_salesman_commission/' . $commission->id)), 2); ?>
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
									<td class="text_center" style="width:30%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('salesman_commission') ?></i></b></span></td>
									<td valign="bottom" style="width:10%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>				
									<td style="width:49%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('info') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('salesman_group') ?></td>
													<td>: <?= $commission->salesman_group ?></td>
												</tr>	
												<?php if($commission->from_date && $commission->from_date !="0000-00-00"){ ?>
													<tr>
														<td><?= lang('from_date') ?></td>
														<td>: <?= $this->cus->hrsd($commission->from_date) ?></td>
													</tr>	
												<?php } if($commission->to_date && $commission->to_date !="0000-00-00"){ ?>
													<tr>
														<td><?= lang('to_date') ?></td>
														<td>: <?= $this->cus->hrsd($commission->to_date) ?></td>
													</tr>	
												<?php } ?>
											</table>
										</fieldset>
									</td>
									<td style="width:49%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('commission_type') ?></td>
													<td>: <?= $commission->commission_type ?></td>
												</tr>	
												<tr>
													<td><?= lang('date') ?></td>
													<td>: <?= $this->cus->hrsd($commission->date) ?></td>
												</tr>	
												<tr>
													<td><?= lang('created_by') ?></td>
													<td>: <?= $created_by->last_name." ".$created_by->first_name ?></td>
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
						$tbody = "";
						$i = 1;
						if($commission_items){
							foreach($commission_items as $commission_item){
								if (strpos($commission_item->rate, '%') == true) {
									$d_rate = $commission_item->rate;
								}else{
									$d_rate = $this->cus->formatMoney($commission_item->rate);
								}
								$reference = "";
								if($commission->commission_type=="Normal"){
									$reference = "<td class='text_center'>".$commission_item->reference_no."</td>";
								}
								$tbody .="<tr>
											<td class='text_center'>".$i++."</td>
											<td class='text_left'>".$commission_item->last_name." ".$commission_item->first_name."</td>
											".$reference."
											<td class='text_right'>".$this->cus->formatMoney($commission_item->grand_total)."</td>
											<td class='text_right'>".$this->cus->formatMoney($commission_item->amount)."</td>
											<td class='text_right'>".$d_rate."</td>
											<td class='text_right'>".$this->cus->formatMoney($commission_item->commission)."</td>
										</tr>";
							}
						}
						$footer_colspan = 4;
						$footer_rowspan = 1;
						if($commission->commission_type=="Normal"){
							$footer_colspan++;
						}
						if($commission->paid != 0){
							$footer_rowspan += 2;
						}
						$tfooter = '';
						$footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->cus->decode_html($commission->note).'</td>';
						$tfooter .= '<tr>
										'.$footer_note.'
										<td class="text_right"><b>'.lang("total").'</b></td>
										<td class="text_right"><b>'.$this->cus->formatMoney($commission->total_commission).'</b></td>
									</tr>';
						$footer_note = '';
						if($commission->paid != 0){		
							$tfooter .= '<tr>
											'.$footer_note.'
											<td class="text_right"><b>'.lang("paid").'</b></td>
											<td class="text_right"><b>'.$this->cus->formatMoney($commission->paid).'</b></td>
										</tr>';
							$tfooter .= '<tr>
											'.$footer_note.'
											<td class="text_right"><b>'.lang("balance").'</b></td>
											<td class="text_right"><b>'.$this->cus->formatMoney($commission->total_commission - $commission->paid).'</b></td>
										</tr>';
						}						
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("salesman"); ?></th>
										<?php if($commission->commission_type=="Normal"){ ?>
											<th><?= lang("reference"); ?></th>
										<?php } ?>
										<th><?= lang("grand_total"); ?></th>
										<th><?= lang("amount"); ?></th>
										<th><?= lang("rate"); ?></th>
										<th><?= lang("commission"); ?></th>
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
							<table style="margin-top:20px;">
								<tr>
									<td class="text_center" style="width:50%"><?= lang("preparer") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:50%"><?= lang("approver").' '. lang("signature") ?></td>
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
		.bg-text{
			display:block !important;
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
					<?php if($commission->commission_type=="Normal"){ ?>
						td_html +='<td class="td_print">&nbsp;</td>';
					<?php } ?>
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>

