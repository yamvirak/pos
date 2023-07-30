<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 15 ;
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
					<table style="margin-top: 5px;">
						<tr>
							<td class="text_left">
                                     <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                </td>
                                <td></td>
                                 <td class="text_center" style="width:60%">
                                    <div>
                                        <strong style="font-size:20px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
                                    </div>
                                    <div style="font-size: 11px;"><?= $biller->address?></div>
                                    <div><?= lang('tel').' : '. $biller->phone ?></div> 
                                    <div><?= lang('email').' : '. $biller->email ?></div>   
                                </td> 
							<td class="text_center" style="width:20%">
								<?= $this->cus->qrcode('link', urlencode(site_url('rentals/deposit_note/' . $deposit->id)), 2); ?>
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
							<td class="text_center" style="width:25%">
								<?php if($deposit->type=='received'){ ?>
									<span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('receipt_voucher') ?></i></b></span>
								<?php }else{ ?>
									<span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('payment_voucher') ?></i></b></span>
								<?php } ?>
							</td>
							<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
						</tr>
					</table>
				</th>
			</tr>
			<?php
				if ($deposit->paid_by == 'gift_card' || $deposit->paid_by == 'CC' || $deposit->paid_by == 'ppp' || $deposit->paid_by == 'stripe' || $deposit->paid_by == 'authorize') {
					$deposit_info = ' (' . substr($deposit->cc_no, -4) . ')';
				} elseif ($deposit->paid_by == 'Cheque') {
					$deposit_info = ' (' . $deposit->cheque_no . ')';
				}else{
					$deposit_info = '';
				}
			
			?>

			

			<tr>
				<th>
					<table>
						<tr>
							<td style="width:50%">
								<fieldset>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('customer') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('name') ?></td>
											<td> : <strong><?= $customer->company ?></strong></td>
										</tr>
										<tr>
											<td><?= lang('address') ?></td>
											<td> : <?= $customer->address.$customer->city ?></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?></td>
											<td> : <?= $customer->phone ?></td>
										</tr>
										<?php if(isset($room) && $room){ ?>
											<tr>
												<td style="font-weight:bold;"><?= lang('room') ?></td>
												<td style="font-weight:bold;"> : <?= $room->name ?></td>
											</tr>
										<?php } ?>
									</table>
								</fieldset>
							</td>
							<td style="width:50%">
								<fieldset style="margin-left:5px !important">
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('payment_reference') ?></td>
											<td style="text-align:left"> : <b><?= $deposit->reference_no ?></b></td>
										</tr>
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->cus->hrsd($deposit->date) ?></td>
										</tr>
										<tr>
											<td><?= lang('paid_by') ?></td>
											<td style="text-align:left"> : <b><?= lang($deposit->cash_account).' '.$deposit_info ?></b></td>
										</tr>
										<tr>
											<td><?= lang('inv_cashier') ?></td>
											<td style="text-align:left"> : <b><?= $created_by->last_name." ".$created_by->first_name;?></b></td>
										</tr>
										<?php if ($deposit->paid_by == 'CC' || $deposit->paid_by == 'ppp' || $deposit->paid_by == 'stripe' || $deposit->paid_by == 'authorize') { ?>
											<tr>
												<td><?= lang('name') ?></td>
												<td style="text-align:left"> : <b><?= $deposit->cc_holder; ?></b></td>
											</tr>		
										<?php } ?>
										<?php if ($deposit->paid_by == 'ppp' || $deposit->paid_by == 'stripe' || $deposit->paid_by == 'authorize') { ?>
											<tr>
												<td><?= lang('transaction_id') ?></td>
												<td style="text-align:left"> : <b><?= $deposit->transaction_id ?></b></td>
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
				$footer_rowspan = 2;
				$i=1;
				$tbody .='<tr>
							<td class="text_center">'.$i.'</td>
							<td class="text_left">'.$rental->reference_no.'</td>
							<td class="text_center">'.$this->cus->hrsd($rental->date).'</td>
							<td class="text_right">'.$this->cus->formatMoney($deposit->amount).'</td>
						</tr>';		

				
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th><?= lang("#"); ?></th>
								<th><?= lang("reference"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("deposit"); ?></th>
							</tr>
						</thead>
						<tbody id="tbody">
							<?= $tbody ?>
						</tbody>
						<tbody id="tfooter">
							<tr>
								<td class="text_right" colspan="3"><b><?= lang('total') ?> : </b></td>
								<td class="text_right"><b><?= $this->cus->formatMoney($deposit->amount) ?></b></td>
							</tr>
							<?php if($deposit->note){ ?>
								<tr>
									<td style="border:none !important" colspan="4"><b><?= lang('note') ?> : </b><?= $this->cus->decode_html($deposit->note); ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>

		<tfoot>
			<tr class="tr_print1">
				<td>
					
					 <table style="margin-top:5px; margin-bottom:<?= $margin_signature -60;?>px;">
                                <thead class="footer_item">
                                    <th class="text_cen con_content" style="width: 35%;"><?= lang("បេឡាករ/Cashier");?></th>
                                    <th class="text_left con_content" style="width: 35%;"><?= lang("អនុម័ត្តដោយ/Approved by");?></th>
                                    <th class="text_left con_content" style="width: 35%;">

                                    	<?php if($payment->transaction == 'Saleman Commission'){
		                                		echo 'អ្នកលក់/Saleman';
		                                	}else{
		                                		echo 'អតិថិជន/Customer';
		                                	}
		                               	?>	
                                    	</th>
                                    
                                </thead>
                                <tbody class="footer_item_body">
                                    <td class="footer_item_body" style="height:60px;"></td>
                                    <td class="footer_item_body"></td>
                                    <td class="footer_item_body"></td>
                                </tbody>
                                <thead class="footer_item_footer">
                                    <th class="footer_item_footer text_left">
		                                <div class="footer_name">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></div>
		                                <div class="footer_line">................................................</div>
                                        
                            		</th>
                                    <th class="footer_item_footer text_left">
		                                <div class="footer_name">ឈ្មោះ</div>
		                                <div class="footer_line">................................................</div>
                                        
                            		</th>
                                    <th class="footer_item_footer text_left">
		                                <div class="footer_name">ឈ្មោះ 
		                                	<?php if($payment->transaction == 'Saleman Commission'){
		                                		echo $saleman->last_name.' '.$saleman->first_name;
		                                	}else{
		                                		echo $customer->company;
		                                	}

		                                		?>
		                                		
		                                	</div>
		                                <div class="footer_line">................................................</div>
                                        
                            		</th>
                                </thead>
                            </table>
				</td>
			</tr>
		</tfoot>
	</table>

	<hr style="border-bottom: 3px dotted black; margin-top: 25px!important; margin-bottom: 25px!important;">

		<table>
		<thead>
			

			<tr>
				<th>
					<table style="margin-top: 5px;">
						<tr>
							<td class="text_left">
                                     <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                </td>
                                <td></td>
                                 <td class="text_center" style="width:60%">
                                    <div>
                                        <strong style="font-size:20px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
                                    </div>
                                    <div style="font-size: 11px;"><?= $biller->address?></div>
                                    <div><?= lang('tel').' : '. $biller->phone ?></div> 
                                    <div><?= lang('email').' : '. $biller->email ?></div>   
                                </td> 
							<td class="text_center" style="width:20%">
								<?= $this->cus->qrcode('link', urlencode(site_url('rentals/deposit_note/' . $deposit->id)), 2); ?>
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
							<td class="text_center" style="width:25%">
								<?php if($deposit->type=='received'){ ?>
									<span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('receipt_voucher') ?></i></b></span>
								<?php }else{ ?>
									<span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('payment_voucher') ?></i></b></span>
								<?php } ?>
							</td>
							<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
						</tr>
					</table>
				</th>
			</tr>
			<?php
				if ($deposit->paid_by == 'gift_card' || $deposit->paid_by == 'CC' || $deposit->paid_by == 'ppp' || $deposit->paid_by == 'stripe' || $deposit->paid_by == 'authorize') {
					$deposit_info = ' (' . substr($deposit->cc_no, -4) . ')';
				} elseif ($deposit->paid_by == 'Cheque') {
					$deposit_info = ' (' . $deposit->cheque_no . ')';
				}else{
					$deposit_info = '';
				}
			
			?>

			

			<tr>
				<th>
					<table>
						<tr>
							<td style="width:50%">
								<fieldset>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('customer') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('name') ?></td>
											<td> : <strong><?= $customer->company ?></strong></td>
										</tr>
										<tr>
											<td><?= lang('address') ?></td>
											<td> : <?= $customer->address.$customer->city ?></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?></td>
											<td> : <?= $customer->phone ?></td>
										</tr>
										<?php if(isset($room) && $room){ ?>
											<tr>
												<td style="font-weight:bold;"><?= lang('room') ?></td>
												<td style="font-weight:bold;"> : <?= $room->name ?></td>
											</tr>
										<?php } ?>
									</table>
								</fieldset>
							</td>
							<td style="width:50%">
								<fieldset style="margin-left:5px !important">
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('payment_reference') ?></td>
											<td style="text-align:left"> : <b><?= $deposit->reference_no ?></b></td>
										</tr>
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->cus->hrsd($deposit->date) ?></td>
										</tr>
										<tr>
											<td><?= lang('paid_by') ?></td>
											<td style="text-align:left"> : <b><?= lang($deposit->cash_account).' '.$deposit_info ?></b></td>
										</tr>
										<tr>
											<td><?= lang('inv_cashier') ?></td>
											<td style="text-align:left"> : <b><?= $created_by->last_name." ".$created_by->first_name;?></b></td>
										</tr>


										<?php if ($deposit->paid_by == 'CC' || $deposit->paid_by == 'ppp' || $deposit->paid_by == 'stripe' || $deposit->paid_by == 'authorize') { ?>
											<tr>
												<td><?= lang('name') ?></td>
												<td style="text-align:left"> : <b><?= $deposit->cc_holder; ?></b></td>
											</tr>		
										<?php } ?>
										<?php if ($deposit->paid_by == 'ppp' || $deposit->paid_by == 'stripe' || $deposit->paid_by == 'authorize') { ?>
											<tr>
												<td><?= lang('transaction_id') ?></td>
												<td style="text-align:left"> : <b><?= $deposit->transaction_id ?></b></td>
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
				$footer_rowspan = 2;
				$i=1;
				$tbody .='<tr>
							<td class="text_center">'.$i.'</td>
							<td class="text_left">'.$rental->reference_no.'</td>
							<td class="text_center">'.$this->cus->hrsd($rental->date).'</td>
							<td class="text_right">'.$this->cus->formatMoney($deposit->amount).'</td>
						</tr>';		

				
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th><?= lang("#"); ?></th>
								<th><?= lang("reference"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("deposit"); ?></th>
							</tr>
						</thead>
						<tbody id="tbody">
							<?= $tbody ?>
						</tbody>
						<tbody id="tfooter">
							<tr>
								<td class="text_right" colspan="3"><b><?= lang('total') ?> : </b></td>
								<td class="text_right"><b><?= $this->cus->formatMoney($deposit->amount) ?></b></td>
							</tr>
							<?php if($deposit->note){ ?>
								<tr>
									<td style="border:none !important" colspan="4"><b><?= lang('note') ?> : </b><?= $this->cus->decode_html($deposit->note); ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>

		<tfoot>
			<tr class="tr_print1">
				<td>
					
					 <table style="margin-top:5px; margin-bottom:<?= $margin_signature -60;?>px;">
                                <thead class="footer_item">
                                    <th class="text_cen con_content" style="width: 35%;"><?= lang("បេឡាករ/Cashier");?></th>
                                    <th class="text_left con_content" style="width: 35%;"><?= lang("អនុម័ត្តដោយ/Approved by");?></th>
                                    <th class="text_left con_content" style="width: 35%;">

                                    	<?php if($payment->transaction == 'Saleman Commission'){
		                                		echo 'អ្នកលក់/Saleman';
		                                	}else{
		                                		echo 'អតិថិជន/Customer';
		                                	}
		                               	?>	
                                    	</th>
                                    
                                </thead>
                                <tbody class="footer_item_body">
                                    <td class="footer_item_body" style="height:60px;"></td>
                                    <td class="footer_item_body"></td>
                                    <td class="footer_item_body"></td>
                                </tbody>
                                <thead class="footer_item_footer">
                                    <th class="footer_item_footer text_left">
		                                <div class="footer_name">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></div>
		                                <div class="footer_line">................................................</div>
                                        
                            		</th>
                                    <th class="footer_item_footer text_left">
		                                <div class="footer_name">ឈ្មោះ</div>
		                                <div class="footer_line">................................................</div>
                                        
                            		</th>
                                    <th class="footer_item_footer text_left">
		                                <div class="footer_name">ឈ្មោះ 
		                                	<?php if($payment->transaction == 'Saleman Commission'){
		                                		echo $saleman->last_name.' '.$saleman->first_name;
		                                	}else{
		                                		echo $customer->company;
		                                	}

		                                		?>
		                                		
		                                	</div>
		                                <div class="footer_line">................................................</div>
                                        
                            		</th>
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
					<?php if ($deposit->attachment) { ?>
						<div class="btn-group">
							<a href="<?= site_url('assets/uploads/' . $deposit->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
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
		#myModal .modal-content {
            display: none !important;
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
		border:3px solid #428BCD !important;
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
	.footer_item_footer{
        border:1px solid #000000 !important;
        text-align:left !important;
        line-height:30px !important;
    }
    .footer_line{
    	/*margin-top: 4px;*/
    }
    .footer_name{
        margin-bottom: -26px;
        font-weight: bold;
        font-family: Khmer OS Muol Light;
        font-size: 13px;
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
						transaction_id : <?= $deposit->id ?>,
						transaction : "Deposit Note",
						reference_no : "<?= $deposit->reference_no ?>"
					}
			});
		}
		//window.addEventListener("beforeprint", function(event) { addTr();});
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
					td_html +='<td class="td_print">&nbsp;</td><tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>
