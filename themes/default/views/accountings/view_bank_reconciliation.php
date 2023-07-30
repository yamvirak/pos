<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') -100;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
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
				<thead>
					<tr>
						<th>

							<table style="margin-top: 5px; width:100%;">
		                    	<th>
			                        <tr>  
			                            <?php 
			                                $biller_id = (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller);
			                                $biller_id_all = lang('all_selected');
			                                $biller_id_detail = $this->site->getCompanyByID($biller_id);
			                                if($biller_id_detail){
			                                ?>
			                                <td class="text_left" style="width: 10%">
			                                    <div>
			                                        <?= !empty($biller_id_detail->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller_id_detail->logo).'" alt="">' : ''; ?>
			                                    </div>
			                                </td>
			                                <td></td>
			                                <td class="text_center" style="width:100%">
			                                    <div>
			                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller_id_detail->company;?></strong><br>
			                                        <strong style="font-size:20px";><?= $biller_id_detail->name;?></strong>
			                                    </div>
			                                <br>

			                                <?php 
			                                }else{
			                                ?>

			                                <td class="text_left" style="width: 10%">
			                                    <div>
			                                        <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
			                                    </div>
			                                </td>
			                                <td></td>
			                                <td class="text_center" style="width:100%">
			                                    <div>
			                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
			                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
			                                    </div>
			                                <?php } ?>
			                                <div><?= $biller->address;?></div>
											<div><?= lang('tel').' : '. $biller->phone ?></div>	
											<div><?= lang('email').' : '. $biller->email ?></div>
			                            </td> 
			                            <td class="text_center" style="width:20%">
												<?= $this->cus->qrcode('link', urlencode(site_url('accountings/view_bank_reconciliation/' . $inv->id)), 2); ?>
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
									<td class="text_center" style="width:30%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('bank_reconciliation') ?></i></b></span></td>
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
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('ref') ?></td>
													<td style="text-align:left"> : <b><?= $inv->reference ?></b></td>
												</tr>
												<tr>
													<td><?= lang('date') ?></td>
													<td style="text-align:left"> : <?= $this->cus->hrsd($inv->date) ?></td>
												</tr>
												<tr>
													<td><?= lang('statement_date') ?></td>
													<td> : <?= $this->cus->hrsd($inv->statement_date) ?></td>
												</tr>
												<tr>
													<td><?= lang('account') ?></td>
													<td> : <?= $account->code.' - '.$account->name ?></td>
												</tr>
												
											</table>
										</fieldset>
									</td>
									<td style="width:40%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('information') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('beginning_balance') ?></td>
													<td> : <?= $this->cus->formatMoney($inv->beginning_balance); ?></td>
												</tr>

												<tr>
													<td><?= lang('service_charge') ?></td>
													<td> : <?= $this->cus->formatMoney($inv->service_charge); ?></td>
												</tr>
												<tr>
													<td><?= lang('interest_earned') ?></td>
													<td> : <?= $this->cus->formatMoney($inv->interest_earned); ?></td>
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
						$unclear_amount = 0;
						
						$tbody .='<tr>
									<td style="border-right:none !important" class="text_left" colspan="6"><b>Balance As Per Bank Statement</b></td>
									<td style="border-left:none !important" class="text_right"><b>'.$this->cus->formatMoney($inv->ending_balance).'</b></td>
								</tr>';
						$tbody .='<tr>
									<td class="text_left" colspan="7"><b>Unpreseted Cheque(s)</b></td>
									
								</tr>';	
						
						foreach ($rows as $row){
							$unclear_amount += $row->amount;
							if($row->amount < 0){
								$amount = '('.$this->cus->formatMoney(abs($row->amount)).')';
							}else{
								$amount = $this->cus->formatMoney($row->amount);
							}
							$tbody .='<tr>
											<td class="text_center">'.$i.'</td>
											<td class="text_left">'.$row->transaction.'</td>
											<td class="text_center">'.$this->cus->hrsd($row->transaction_date).'</td>
											<td class="text_left">'.$row->reference.'</td>
											<td class="text_left">'.$row->narrative.'</td>
											<td class="text_left">'.($row->description==null ? $row->description : '').'</td>
											<td class="text_right">'.$amount.'</td>
					
										</tr>';		
							$i++;
						}
						if($unclear_amount < 0){
							$unclear_amount_td = '('.$this->cus->formatMoney(abs($unclear_amount)).')';
						}else{
							$unclear_amount_td = $unclear_amount;
						}
						
						$balance_acc = $inv->ending_balance + $unclear_amount;
						if($balance_acc < 0){
							$balance_acc = '('.$this->cus->formatMoney(abs($balance_acc)).')';
						}else{
							$balance_acc = $balance_acc;
						}
						
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("transaction"); ?></th>
										<th><?= lang("transaction_date"); ?></th>
										<th><?= lang("reference"); ?></th>
										<th><?= lang("narrative"); ?></th>
										<th><?= lang("description"); ?></th>
										<th><?= lang("amount"); ?></th>
			
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tfoot>
									
									<tr>
										<td colspan="6" class="text_left" style="border-right:none !important"><b><?= lang('Balance As Per Account') ?> </b></td>
										<td class="text_right" style="border-left:none !important"><b><?= $balance_acc ?></b></td>
									</tr>
									<?php if($inv->note){ ?>
										<tr>
											<td style="border:0px !important" colspan="4"><b><?= lang('note') ?> : </b> <?= $this->cus->decode_html($inv->note)  ?></td>
										</tr>
									<?php } ?>
								</tfoot>
							</table>
						</td>
					</tr>
				</tbody>
				<tfoot class="hidden">
					<tr class="tr_print">
						<td>
							<table style="margin-top:<?= $margin_signature ?>px;">
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
				<tfoot>
			<tr class="tr_print1">
				<td>
					<table style="margin-top:30px; margin-bottom:<?= $margin_signature - 20 ?>px;">
						<thead class="footer_item">
							<th class="text_center"><?= lang("prepared_by");?></th>
							<th class="text_center"><?= lang("checked_by");?></th>
							<th class="text_center"><?= lang("approved_by");?></th>
							<th class="text_center"><?= lang("received_by") ?></th>
						</thead>
						<tbody class="footer_item_body">
							<td class="footer_item_body"></td>
							<td class="footer_item_body"></td>
							<td class="footer_item_body"></td>
							<td class="footer_item_body"></td>
						</tbody>
						<thead class="footer_item_footer">
							<th class="footer_item_footer text_left">Name:<span class="border_bottom"> <?= $created_by->first_name." ".$created_by->last_name;?></span></th>
							<th class="footer_item_footer text_left">Name:<span class="border_bottom"></span></th>
							<th class="footer_item_footer text_left">Name:<span class="border_bottom"></span></th>
							<th class="footer_item_footer text_left">Name:</th>
						</thead>
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
					<?php if ($inv->attachment) { ?>
						<div class="btn-group">
							<a href="<?= site_url('assets/uploads/' . $inv->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
								<i class="fa fa-download"></i>
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
		.bg-text{
			display:block !important;
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
		min-height:<?= $min_height+20 ?>px !important;
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
						transaction : "Bank Reconciliation",
						reference_no : "<?= $inv->reference ?>"
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
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
    });
	
</script>

