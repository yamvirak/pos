<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = 0;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg">
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
								<?= $this->cus->qrcode('link', urlencode(site_url('sales/payment_note_commission/' . $payment->id)), 2); ?>
							</td>
						</tr>
					</table>
				</th>
			</tr>
			<tr>
				<th>
					<table>
						<tr>
							<td valign="bottom" style="width:35%"><hr class="hr_title"></td>
							<?php if($payment->type == 'returned' || $payment->transaction == 'Saleman Commission' || $payment->transaction == 'Agency Commission'){ ?>
								<td class="text_center" style="width:33%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('inv_payment_voucher') ?></i></b></span></td>
							<?php }else{ ?>
								<td class="text_center" style="width:37%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('receipt_voucher') ?></i></b></span></td>
							<?php } ?>
							<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
						</tr>
					</table>
				</th>
			</tr>
			<?php
				if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe' || $payment->paid_by == 'authorize') {
					$payment_info = ' (' . substr($payment->cc_no, -4) . ')';
				} elseif ($payment->paid_by == 'Cheque') {
					$payment_info = ' (' . $payment->cheque_no . ')';
				}else{
					$payment_info = '';
				}
			
			?>
			<tr>
				<th>
					<table>
						<tr>
							<td style="width:50%">
								<fieldset>
									
									
								<?php if($payment->transaction == 'Saleman Commission'){ ?>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('saleman') ?></i></b></legend>
									<table>
											<tr>
												<td><?= lang('name') ?></td>
												<td> : <strong><?= $inv->saleman_name?></strong></td>
											</tr>
											<tr>
												<td><?= lang('customer') ?></td>
												<td> : <strong><?= $customer->company.'&nbsp;('.$customer->name;?>)</strong>
											
											</tr>
											<tr>
												<td><?= lang('product') ?></td>
												<td> : <?= $inv->description ?></td>
											</tr>
										
									<?php }else{ ?>

									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('customer') ?></i></b></legend>
									<table>
											<tr>
												<td><?= lang('name') ?></td>
												<td style="text-transform: uppercase;"> : <strong>
                                                    <?= $customer->company?>
                                                    <?php 
                                                        if($customers->company == 'N/A'){
                                                        echo '';

                                                    }else{
                                                        echo 'និង' .$customers->company;
                                                    }
                                                    ?>
                                                    </strong>
                                               </td>
											</tr>
											<tr>
												<td><?= lang('tel') ?></td>
												<td> : <?= $customer->phone ?></td>
											</tr>
											<tr>
												<td><?= lang('address') ?></td>
												<td style="font-size: 10px;"> : <?= $customer->address; ?></td>
											</tr>
										<?php } ?>
										
									</table>
								</fieldset>
							</td>
							<td style="width:37%">
								<fieldset style="margin-left:5px !important">
									<?php if($payment->transaction == 'Saleman Commission'){ ?>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->cus->hrsd($payment->date) ?></td>
										</tr>
										<tr>
											<td><?= lang('payment_reference') ?></td>
											<td style="text-align:left"> : <b><?= $payment->reference_no ?></b></td>
										</tr>
										
										 <tr>
											<td><?= lang('paid_by') ?></td>
											<td style="text-align:left"> : <b><?= $payment->cash_account.$payment_info ?></b></td>
										</tr>
										
									<?php }else{ ?>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->cus->hrsd($payment->date) ?></td>
										</tr>
										<tr class="hidden">
											<td><?= lang('ref') ?></td>
											<td style="text-align:left"> : <b><?= $inv->reference_no ?></b></td>
										</tr>
										<tr>
											<td><?= lang('payment_reference') ?></td>
											<td style="text-align:left"> : <b><?= $payment->reference_no ?></b></td>
										</tr>
										
										 <tr>
											<td><?= lang('paid_by') ?></td>
											<td style="text-align:left"> : <b><?= $payment->cash_account.$payment_info ?></b></td>
										</tr>

									<?php } ?>
										
									</table>
								</fieldset>
							</td>
						</tr>
						<?php if($payment->note){ ?>
							<tr>
								<td><b><?= lang('note') ?> : </b><?= html_entity_decode($payment->note); ?></td>
							</tr>
						<?php } ?>
					</table>
				</th>
			</tr>
		</thead>
		
		<tbody>
			<?php
				$tbody = '';
				$footer_rowspan = 2;
				$i=1;
				$total_amount = 0;
				$total_paid = 0;
				$total_discount = 0;
				$sale_ids = "";
				$tax_amount = 0;
				foreach ($inv_payments as $inv_payment){
					$sale_ids .= $inv_payment->sale_id."SaleID";
					$inv_payment->payment_amount = abs($inv_payment->payment_amount);
					$payment_discount->payment_amount = abs($inv_payment->payment_discount);
					$total_amount += $inv_payment->payment_amount;
					$tax_rate = '15%';
					$tax_amount = ($tax_rate * $total_amount) / 100;
					$total_discount += $inv_payment->payment_discount;
					$total_paid = $total_amount - $tax_amount;
					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_left">'.$inv_payment->sale_ref.'</td>
									<td class="text_center">'.$this->cus->hrsd($inv_payment->sale_date).'</td>
									<td class="text_right">'.$this->cus->formatMoney($inv_payment->payment_discount).'</td>
									<td class="text_right">'.$this->cus->formatMoney($inv_payment->payment_amount).'</td>
								</tr>';		
					$i++;

					$footer_colspan = 3;
                    $footer_rowspan = 1;
                    if($inv->grand_total != $inv->total){
                        $footer_rowspan++;
                    }
                    if($inv->order_discount != 0){
                        $footer_rowspan++;
                    }

                    if($inv->other_discount != 0){
                        $footer_rowspan++;
                    }

                    if($inv->order_tax != 0){
                        $footer_rowspan++;
                    }
                    if($inv->shipping != 0){
                        $footer_rowspan++;
                    }
                    
                    if ($inv->paid <= $inv->grand_total) {
                        $footer_rowspan++;
                    }
                    if ($payment && $payment->discount != 0) {
                        $footer_rowspan++;
                    }   
                    
                    $amount_balance = (($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));

                    if($amount_balance <= $inv->grand_total){
                        $footer_rowspan++;
                    }
                    
                    $tfooter = '';

                    $footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->cus->decode_html($inv->note1).'</td>';

                  
                    $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right"><b>'.lang("total_amount").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($total_amount).'</b></td>
                                    </tr>';
                    $footer_note = '';
                   
                    
                   
				}
				
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th><?= lang("#"); ?></th>
								<th><?= lang("reference"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("discount"); ?></th>
								<th><?= lang("withdraw"); ?></th>
							</tr>
						</thead>
						<tbody id="tbody_main">
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
			<tr class="tr_print1">
				<td>
					
					 <table style="margin-top:5px; margin-bottom:<?= $margin_signature -60;?>px;">
                                <thead class="footer_item">
                                    <th class="text_cen con_content" style="width: 35%;"><?= lang("បេឡាករ/Cashier");?></th>
                                    <th class="text_left con_content" style="width: 35%;"><?= lang("អនុម័ត្តដោយ/Approved by");?></th>
                                    <th class="text_left con_content" style="width: 35%;">

                                    	<?php if($payment->transaction == 'Saleman Commission' || $payment->transaction == 'Agency Commission'){
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
		                                	<?php if($payment->transaction == 'Saleman Commission' || $payment->transaction == 'Agency Commission'){
		                                		echo $inv->saleman_name;
		                                	}else{
		                                		echo "";
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
								<?= $this->cus->qrcode('link', urlencode(site_url('sales/payment_note_commission/' . $payment->id)), 2); ?>
							</td>
						</tr>
					</table>
				</th>
			</tr>
			<tr>
				<th>
					<table>
						<tr>
							<td valign="bottom" style="width:35%"><hr class="hr_title"></td>
							<?php if($payment->type == 'returned' || $payment->transaction == 'Saleman Commission' || $payment->transaction == 'Agency Commission'){ ?>
								<td class="text_center" style="width:33%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('inv_payment_voucher') ?></i></b></span></td>
							<?php }else{ ?>
								<td class="text_center" style="width:37%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('receipt_voucher') ?></i></b></span></td>
							<?php } ?>
							<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
						</tr>
					</table>
				</th>
			</tr>
			<?php
				if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe' || $payment->paid_by == 'authorize') {
					$payment_info = ' (' . substr($payment->cc_no, -4) . ')';
				} elseif ($payment->paid_by == 'Cheque') {
					$payment_info = ' (' . $payment->cheque_no . ')';
				}else{
					$payment_info = '';
				}
			
			?>
			<tr>
				<th>
					<table>
						<tr>
							<td style="width:50%">
								<fieldset>
									
									
								<?php if($payment->transaction == 'Saleman Commission'){ ?>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('saleman') ?></i></b></legend>
									<table>
											<tr>
												<td><?= lang('name') ?></td>
												<td> : <strong><?= $inv->saleman_name?></strong></td>
											</tr>
											<tr>
												<td><?= lang('customer') ?></td>
												<td> : <strong><?= $customer->company.'&nbsp;('.$customer->name;?>)</strong>
											
											</tr>
											<tr>
												<td><?= lang('product') ?></td>
												<td> : <?= $inv->description ?></td>
											</tr>
										
									<?php }else{ ?>

									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('customer') ?></i></b></legend>
									<table>
											<tr>
												<td><?= lang('name') ?></td>
												<td style="text-transform: uppercase;"> : <strong>
                                                    <?= $customer->company?>
                                                    <?php 
                                                        if($customers->company == 'N/A'){
                                                        echo '';

                                                    }else{
                                                        echo 'និង' .$customers->company;
                                                    }
                                                    ?>
                                                    </strong>
                                               </td>
											</tr>
											<tr>
												<td><?= lang('tel') ?></td>
												<td> : <?= $customer->phone ?></td>
											</tr>
											<tr>
												<td><?= lang('address') ?></td>
												<td style="font-size: 10px;"> : <?= $customer->address; ?></td>
											</tr>
										<?php } ?>
										
									</table>
								</fieldset>
							</td>
							<td style="width:37%">
								<fieldset style="margin-left:5px !important">
									<?php if($payment->transaction == 'Saleman Commission'){ ?>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->cus->hrsd($payment->date) ?></td>
										</tr>
										<tr>
											<td><?= lang('payment_reference') ?></td>
											<td style="text-align:left"> : <b><?= $payment->reference_no ?></b></td>
										</tr>
										
										 <tr>
											<td><?= lang('paid_by') ?></td>
											<td style="text-align:left"> : <b><?= $payment->cash_account.$payment_info ?></b></td>
										</tr>
										
									<?php }else{ ?>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->cus->hrsd($payment->date) ?></td>
										</tr>
										<tr class="hidden">
											<td><?= lang('ref') ?></td>
											<td style="text-align:left"> : <b><?= $inv->reference_no ?></b></td>
										</tr>
										<tr>
											<td><?= lang('payment_reference') ?></td>
											<td style="text-align:left"> : <b><?= $payment->reference_no ?></b></td>
										</tr>
										
										 <tr>
											<td><?= lang('paid_by') ?></td>
											<td style="text-align:left"> : <b><?= $payment->cash_account.$payment_info ?></b></td>
										</tr>

									<?php } ?>
										
									</table>
								</fieldset>
							</td>
						</tr>
						<?php if($payment->note){ ?>
							<tr>
								<td><b><?= lang('note') ?> : </b><?= html_entity_decode($payment->note); ?></td>
							</tr>
						<?php } ?>
					</table>
				</th>
			</tr>
		</thead>
		
		<tbody>
			<?php
				$tbody = '';
				$footer_rowspan = 2;
				$i=1;
				$total_amount = 0;
				$total_paid = 0;
				$total_discount = 0;
				$sale_ids = "";
				$tax_amount = 0;
				foreach ($inv_payments as $inv_payment){
					$sale_ids .= $inv_payment->sale_id."SaleID";
					$inv_payment->payment_amount = abs($inv_payment->payment_amount);
					$payment_discount->payment_amount = abs($inv_payment->payment_discount);
					$total_amount += $inv_payment->payment_amount;
					$tax_rate = '15%';
					$tax_amount = ($tax_rate * $total_amount) / 100;
					$total_discount += $inv_payment->payment_discount;
					$total_paid = $total_amount - $tax_amount;
					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_left">'.$inv_payment->sale_ref.'</td>
									<td class="text_center">'.$this->cus->hrsd($inv_payment->sale_date).'</td>
									<td class="text_right">'.$this->cus->formatMoney($inv_payment->payment_discount).'</td>
									<td class="text_right">'.$this->cus->formatMoney($inv_payment->payment_amount).'</td>
								</tr>';		
					$i++;

					$footer_colspan = 3;
                    $footer_rowspan = 1;
                    if($inv->grand_total != $inv->total){
                        $footer_rowspan++;
                    }
                    if($inv->order_discount != 0){
                        $footer_rowspan++;
                    }

                    if($inv->other_discount != 0){
                        $footer_rowspan++;
                    }

                    if($inv->order_tax != 0){
                        $footer_rowspan++;
                    }
                    if($inv->shipping != 0){
                        $footer_rowspan++;
                    }
                    
                    if ($inv->paid <= $inv->grand_total) {
                        $footer_rowspan++;
                    }
                    if ($payment && $payment->discount != 0) {
                        $footer_rowspan++;
                    }   
                    
                    $amount_balance = (($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));

                    if($amount_balance <= $inv->grand_total){
                        $footer_rowspan++;
                    }
                    
                    $tfooter = '';

                    $footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->cus->decode_html($inv->note1).'</td>';

                  
                    $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right"><b>'.lang("total_amount").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($total_amount).'</b></td>
                                    </tr>';
                    $footer_note = '';
				}
				
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th><?= lang("#"); ?></th>
								<th><?= lang("reference"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("discount"); ?></th>
								<th><?= lang("withdraw"); ?></th>
							</tr>
						</thead>
						<tbody id="tbody_main">
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
		                                		echo $inv->saleman_name;
		                                	}else{
		                                		echo "";
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



	<div class="clearfix"></div>
	
	<div class="buttons" style="margin-top:20px; margin-bottom:20px">
		<div class="btn-group btn-group-justified">
			<div class="btn-group">
				<a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
					<i class="fa fa-close"></i>
					<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
				</a>
			</div>
			<?php if($inv_payments && $inv_payment->installment_item_id <= 0){ ?>
						<div class="btn-group">
							<a data-toggle="modal" data-target="#myModal2" class="tip btn btn-warning" href="<?= site_url("sales/edit_payment/".$payment->id) ?>" title="<?= lang('edit') ?>">
								<i class="fa fa-edit"></i>
								<span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
							</a>
						</div>
					<?php } ?>


			<div class="btn-group">
				<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
					<i class="fa fa-print"></i>
					<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
				</a>
			</div>
			<?php if ($payment->attachment) { ?>
				<div class="btn-group">
					<a href="<?= site_url('assets/uploads/' . $payment->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
						<i class="fa fa-download"></i>
						<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
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
		#myModal .modal-content {
            display: none !important;
        }
        .bg-text{
			display:block !important;
		}
        @page{
            margin: 5mm 5mm 5mm 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
        }
	}
	.tr_print{
		display:none;
	}
	.footer_name{
        margin-bottom: -26px;
        font-weight: bold;
        font-family: Khmer OS Muol Light;
        font-size: 13px;

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
	 .footer_item_body{
        border:1px solid #000000 !important;
        line-height: 100px;
        padding-top: 105px !important;
    }
    .footer_item_footer{
        border:1px solid #000000 !important;
        text-align:left !important;
        line-height:30px !important;
    }
    .footer_line{
    	/*margin-top: 4px;*/
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
	
	table{
		width:100% !important;
		font-size:<?= $font_size ?>px !important;
		border-collapse: collapse !important;
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
						transaction_id : <?= $payment->id ?>,
						transaction : "Sale Payment",
						reference_no : "<?= $payment->reference_no ?>"
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
					td_html +='<td class="td_print">&nbsp;</td><tr>';
				$('#tbody').append(td_html);
			}
		}
    });
	
</script>
