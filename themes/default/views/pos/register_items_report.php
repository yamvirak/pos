<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = 0;
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
									<?php
										$user = $this->site->getUser($register->user_id);
										$closed_by = false;
										if($register->closed_by){
											$closed_by = $this->site->getUser($register->closed_by);
										}
										
									?>
									<td style="width:100%">
										<fieldset>
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('close_register') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('user') ?></td>
													<td> : <strong><?= $user->last_name." ".$user->first_name ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('opened_at') ?></td>
													<td> : <?= $this->cus->hrld($register->date,true) ?></td>
												</tr>
												<?php if($closed_by){ ?>
													<tr>
														<td><?= lang('closed_by') ?></td>
														<td> : <strong><?= $closed_by->last_name." ".$closed_by->first_name ?></strong></td>
													</tr>
													<tr>
														<td><?= lang('closed_at') ?></td>
														<td> : <?= $this->cus->hrld($register->closed_at,true) ?></td>
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
						if($register_items){
							foreach ($register_items as $register_item){
								$tbody .='<tr>
												<td class="text_center">'.$i.'</td>
												<td class="text_left">'.$register_item->product_code.'</td>
												<td class="text_left">'.$register_item->product_name.'</td>
												<td class="text_right">'.$this->cus->formatQuantity($register_item->quantity).'</td>
											</tr>';		
								$i++;
							}
						}else{
							$tbody = '<tr>
										<td class="text_left" colspan="4">'.lang("sEmptyTable").'</td>
									</tr>';
						}
						
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("code"); ?></th>
										<th><?= lang("description"); ?></th>
										<th><?= lang("quantity"); ?></th>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
									<tr>
										<td class="text_right" colspan="3" style="font-weight:bold;"><?= lang('total_amount'); ?></td>
										<td style="text-align:right;" class="text_right">
											<?= $this->cus->formatMoney($register->total_cash + $register->total_cheques + $register->total_cc_slips) ?>
										</td>
									</tr>
									<tr>
										<td class="text_right" colspan="3" style="font-weight:bold;"><?= lang('total_submit_amount'); ?></td>
										<td style="text-align:right;" class="text_right">
											<?= $this->cus->formatMoney($register->total_cash_submitted + $register->total_cheques_submitted + $register->total_cc_slips_submitted) ?>
										</td>
									</tr>
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
									<td class="text_center" style="width:50%"><?= lang("user") ?></td>
									<td class="text_center" style="width:50%"><?= lang("closed_by") ?></td>
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
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>

