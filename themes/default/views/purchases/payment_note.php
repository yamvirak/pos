<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = 0;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>

<?php 
function convert_number_to_words($num)
{
        $ones = array(
        0                   => 'សូន្យ',
        1                   => 'មួយ',
        2                   => 'ពីរ',
        3                   => 'បី',
        4                   => 'បួន',
        5                   => 'ប្រាំ',
        6                   => 'ប្រាំមួយ',
        7                   => 'ប្រាំពីរ',
        8                   => 'ប្រាំបី',
        9                   => 'ប្រាំបួន',
        10                  => 'ដប់',
        11                  => 'ដប់មួយ',
        12                  => 'ដប់ពីរ',
        13                  => 'ដប់បី',
        14                  => 'ដប់បួន',
        15                  => 'ដប់ប្រាំ',
        16                  => 'ដប់ប្រាំមួយ',
        17                  => 'ដប់ប្រាំពីរ',
        18                  => 'ដប់ប្រាំបី',
        19                  => 'ដប់ប្រាំបួន',
        "014"               => "ដប់បួន"
        );
        $tens = array( 
            0 => "សូន្យ",
            1 => "ដប់",
            2 => "ម្ភៃ",
            3 => "សាបសិប", 
            4 => "សែសិប", 
            5 => "ហាសិប", 
            6 => "ហុកសិប", 
            7 => "ចិតសិប", 
            8 => "ប៉ែតសិប", 
            9 => "កៅសិប" 
        ); 
        $hundreds = array( 
            "រយ", 
            "ពាន់", 
            "លាន", 
            "បីលាន", 
            "ទ្រីលាន", 
            "កោត" 
        ); /*limit t quadrillion */
        $num = number_format($num,2,".",","); 
        $num_arr = explode(".",$num); 
        $wholenum = $num_arr[0]; 
        $decnum = $num_arr[1]; 
        $whole_arr = array_reverse(explode(",",$wholenum)); 
        krsort($whole_arr,1); 
        $rettxt = ""; 
    foreach($whole_arr as $key => $i){
            while(substr($i,0,1)=="0")
                $i=substr($i,1,5);
            if($i < 20){ 
            /* echo "getting:".$i; */
            $rettxt .= $ones[$i]; 
            }elseif($i < 100){ 
                if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)]; 
                if(substr($i,1,1)!="0") $rettxt .= "".$ones[substr($i,1,1)]; 
            }else{ 
                if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]."".$hundreds[0]; 
                if(substr($i,1,1)!="0")$rettxt .= "".$tens[substr($i,1,1)]; 
                if(substr($i,2,1)!="0")$rettxt .= "".$ones[substr($i,2,1)]; 
            } 
            if($key > 0){ 
                 $rettxt .= "".$hundreds[$key].""; 
               }
    }   
    if($decnum > 0){
        $rettxt .= "ចុច";
        if($decnum < 20){
           $rettxt .= $ones[$decnum];
        }elseif($decnum < 100){
            $rettxt .= $tens[substr($decnum,0,1)];  
            $rettxt .= "".$ones[substr($decnum,1,1)];
        }
    }
        return $rettxt;
}
extract($_POST);
if(isset($convert))
{
    //echo "<p align='center' class='bg-info text-white col-md-4 offset-md-4 mt-4 p-1'>".numberTowords("$num")."</p>";
}





if($customer->gender=='male'){
    $customer->gender = 'ប្រុស';
    }else{
    $customer->gender = 'ស្រី';
}

if($customers->gender == 'male'){
      $customers->gender = 'ប្រុស';
    }elseif($customers->gender == "female"){
        $customers->gender = 'ស្រី';
    }else{
        $customers->gender = 'N/A';
    }

if($business_owner->gender=='male'){
    $business_owner->gender = 'ប្រុស';
    }else{
    $business_owner->gender = 'ស្រី';
}
if($business_owner_02->gender=='male'){
    $business_owner_02->gender = 'ប្រុស';
    }else{
    $business_owner_02->gender = 'ស្រី';
}
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
					<table style="margin-top: 8px;">
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
                                    <div style="font-size: 11px;"><?= $biller->cf2?></div>
                                    <div><?= lang('tel').' : '. $biller->phone ?></div> 
                                    <div><?= lang('email').' : '. $biller->email ?></div>   
                                </td> 
							<td class="text_center" style="width:20%">
								<?= $this->cus->qrcode('link', urlencode(site_url('purchases/payment_note/' . $payment->id)), 2); ?>
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
							
						</tr>
					</table>
				</th>
			</tr>

			<tr>
				<td class="text_center"><span style="font-size:<?= $font_size+5 ?>px"><b><?= lang('inv_payment_voucher') ?></b></span></td>
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

			<style type="text/css">
				.name_receive{
					border:1px solid black;
					padding: 7px;
				    font-size: 14px;
				    font-weight: bold;
				    text-align: center;
				}
			</style>

		</thead>
		<tbody>
			<?php
				$tbody = '';
				$footer_rowspan = 2;
				$i=1;
				$total_paid = 0;
				$total_discount = 0;
				$sale_ids = "";
				foreach ($inv_payments as $inv_payment){
					$sale_ids .= $inv_payment->sale_id."SaleID";
					$inv_payment->payment_amount = abs($inv_payment->payment_amount);
					$payment_discount->payment_amount = abs($inv_payment->payment_discount);
					$total_paid += $inv_payment->payment_amount;
					$total_discount += $inv_payment->payment_discount;
					$tbody .='<tr>
									
									<td class="text_left">បានទទួលប្រាក់ពីឈ្មោះ <br> Received From</td>
									<td class="text_center">'.$this->cus->hrsd($inv_payment->sale_date).'</td>
									<td class="text_right">'.$this->cus->formatMoney($inv_payment->payment_discount).'</td>
									<td class="text_right">'.$this->cus->formatMoney($inv_payment->payment_amount).'</td>
								</tr>';		
					$i++;
					
				}
				if($payment->interest_paid > 0){
					$total_paid += $payment->interest_paid;
					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_left">'.lang('installment_interest').'</td>
									<td class="text_left">'.$inv->description.'</td>
									<td class="text_right"></td>
									<td class="text_right">'.$this->cus->formatMoney($payment->interest_paid).'</td>
								</tr>';	
								$i++;
				}
				
				if($payment->penalty_paid > 0){
					$total_paid += $payment->penalty_paid;
					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_left">'.lang('loan_penalty').'</td>
									<td class="text_left">'.$inv->description.'</td>
									<td class="text_right"></td>
									<td class="text_right">'.$this->cus->formatMoney($payment->penalty_paid).'</td>
								</tr>';	
								$i++;
				}
				
			?>

			<table border="0" style="padding:5px;">
				<tr>
					<td colspan="3"></td>
					<td>លេខ <br> NO.</td>
					<td><div class="bold"><?= $payment->reference_no ?></div></td>
				</tr>


				<tr>
					<td width="130">បានទទួលប្រាក់ពីឈ្មោះ <br> Received From</td>
					<td style="width:30%;">
						<?= $supplier->company;?>
						<br>
						<div style="border-bottom:1px solid black;width: 100%;"></div>
					</td>
					<td width="50px;">&nbsp; </td>
					<td>ចំនួនទឹកប្រាក់ <br> Amount</td>
					<td><div class="name_receive"><?= $this->cus->formatMoney($total_paid) ?></div></td>
				</tr>
				<tr>
					<td><div style="margin: -6px;">&nbsp;</div></td>
				</tr>
				<tr>
					<td width="130">ទឹកប្រាក់សរសេរជាអក្សរ <br>Amount In Word</td>
					<td style="width:30%;"><?=convert_number_to_words($total_paid).'ដុល្លារសហរដ្ឋអាមេរិកគត់';?> <br>
						<div style="border-bottom:1px solid black;width: 100%;"></div>
					</td>
					<td width="50px;">&nbsp; </td>
					<td>ទូទាត់ដោយ<br> Paying By</td>
					<td><div class="name_receive"><?= $payment->cash_account.$payment_info ?></div></td>
					
				</tr>
				<tr>
					<td><div style="margin: -6px;">&nbsp;</div></td>
				</tr>
				<tr>
					<td width="130">អត្ថន័យ <br> <?=lang('Payment Being For')?></td>
					<td colspan="4"> <?= html_entity_decode($payment->note); ?><br>
						<div style="border-bottom:1px solid black;width: 100%;"></div>
					</td>
				</tr>
				<tr>
					<td><div style="margin: -6px;">&nbsp;</div></td>
				</tr>
				<tr>
					<td colspan="2"><?=lang('cash_paid_cannot_returned')?></td>
					
					<td width="50px;">&nbsp; </td>
					<td><?=lang('ថ្ងៃខែឆ្នាំ')?><br><?= lang('date') ?></td>
					<td><div class="name_receive"><?= $this->cus->hrsd($payment->date) ?></div></td>
				</tr>
				<tr>
					<td><div style="margin: -6px;">&nbsp;</div></td>
				</tr>
			</table>

			<tr>
				<td>
					<table class="table_item">
						<thead class="hidden">
							<tr>
								<th><?= lang("#"); ?></th>
								<th><?= lang("reference"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("discount"); ?></th>
								<th><?= lang("paid"); ?></th>
							</tr>
						</thead>
						<tbody id="tbody" class="hidden">
							<?= $tbody ?>
						</tbody>
						<tbody id="tfooter" class="hidden">
							<tr>
								<td class="text_right" colspan="3"><b><?= lang('total') ?> : </b></td>
								<td class="text_right"><b><?= $this->cus->formatMoney($total_discount) ?></b></td>
								<td class="text_right"><b><?= $this->cus->formatMoney($total_paid) ?></b></td>
							</tr>
							
							<?php if($payment->note){ ?>
								<tr>
									<td style="border:none !important" colspan="5"><b><?= lang('note') ?> : </b><?= html_entity_decode($payment->note); ?></td>
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
                                <thead class="footer_items">
                                    <th class="text_cen con_content" style="width: 50%;"><?= lang("អ្នកផ្គត់ផ្គង់/Suppliers");?></th>
                                    <th class="text_left con_content hidden" style="width: 35%;"><?= lang("អ្នកលក់/Seller");?></th>
                                    <th class="text_left con_content" style="width: 50%;"><?= lang("បេឡាករ/Cashier");?></th>
                                    
                                </thead>
                                <tbody class="footer_item_bodys">
                                    <td class="footer_item_bodys" style="height:60px;"></td>
                                    <td class="footer_item_bodys"></td>
                                    <td class="footer_item_bodys"></td>
                                </tbody>
                                <thead class="footer_item_footers">
                                    <th class="footer_item_footers">
		                                <div class="footer_name">ឈ្មោះ <?= $supplier->company?></div>
		                                <div class="footer_line">................................................</div>
                            		</th>
                                    <th class="footer_item_footers text_left hidden">
		                                <div class="footer_name">ឈ្មោះ <?= $inv->saleman_name?></div>
		                                <div class="footer_line">................................................</div>
                            		</th>
                                    <th class="footer_item_footers text_left">
			                            <div class="footer_name">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></div>
		                                <div class="footer_line">................................................</div>
                            		</th>
                                </thead>
                            </table>
				</td>
			</tr>
		</tfoot>
	</table>

	<hr style="border-bottom: 3px dotted black; margin-top: 5px!important; margin-bottom: 5px!important;">



	<table>
		<thead>
			<tr>
				<th>
					<table style="margin-top: 8px;">
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
                                    <div style="font-size: 11px;"><?= $biller->cf2?></div>
                                    <div><?= lang('tel').' : '. $biller->phone ?></div> 
                                    <div><?= lang('email').' : '. $biller->email ?></div>   
                                </td> 
							<td class="text_center" style="width:20%">
								<?= $this->cus->qrcode('link', urlencode(site_url('purchases/payment_note/' . $payment->id)), 2); ?>
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
							
						</tr>
					</table>
				</th>
			</tr>

			<tr>
				<td class="text_center"><span style="font-size:<?= $font_size+5 ?>px"><b><?= lang('inv_payment_voucher') ?></b></span></td>
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

			<style type="text/css">
				.name_receive{
					border:1px solid black;
					padding: 7px;
				    font-size: 14px;
				    font-weight: bold;
				    text-align: center;
				}
			</style>

		</thead>
		<tbody>
			<?php
				$tbody = '';
				$footer_rowspan = 2;
				$i=1;
				$total_paid = 0;
				$total_discount = 0;
				$sale_ids = "";
				foreach ($inv_payments as $inv_payment){
					$sale_ids .= $inv_payment->sale_id."SaleID";
					$inv_payment->payment_amount = abs($inv_payment->payment_amount);
					$payment_discount->payment_amount = abs($inv_payment->payment_discount);
					$total_paid += $inv_payment->payment_amount;
					$total_discount += $inv_payment->payment_discount;
					$tbody .='<tr>
									
									<td class="text_left">បានទទួលប្រាក់ពីឈ្មោះ <br> Received From</td>
									<td class="text_center">'.$this->cus->hrsd($inv_payment->sale_date).'</td>
									<td class="text_right">'.$this->cus->formatMoney($inv_payment->payment_discount).'</td>
									<td class="text_right">'.$this->cus->formatMoney($inv_payment->payment_amount).'</td>
								</tr>';		
					$i++;
					
				}
				if($payment->interest_paid > 0){
					$total_paid += $payment->interest_paid;
					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_left">'.lang('installment_interest').'</td>
									<td class="text_left">'.$inv->description.'</td>
									<td class="text_right"></td>
									<td class="text_right">'.$this->cus->formatMoney($payment->interest_paid).'</td>
								</tr>';	
								$i++;
				}
				
				if($payment->penalty_paid > 0){
					$total_paid += $payment->penalty_paid;
					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_left">'.lang('loan_penalty').'</td>
									<td class="text_left">'.$inv->description.'</td>
									<td class="text_right"></td>
									<td class="text_right">'.$this->cus->formatMoney($payment->penalty_paid).'</td>
								</tr>';	
								$i++;
				}
				
			?>

			<table border="0" style="padding:5px;">
				<tr>
					<td colspan="3"></td>
					<td>លេខ <br> NO.</td>
					<td><div class="bold"><?= $payment->reference_no ?></div></td>
				</tr>


				<tr>
					<td width="130">បានទទួលប្រាក់ពីឈ្មោះ <br> Received From</td>
					<td style="width:30%;">
						<?= $supplier->company;?>
						<br>
						<div style="border-bottom:1px solid black;width: 100%;"></div>
					</td>
					<td width="50px;">&nbsp; </td>
					<td>ចំនួនទឹកប្រាក់ <br> Amount</td>
					<td><div class="name_receive"><?= $this->cus->formatMoney($total_paid) ?></div></td>
				</tr>
				<tr>
					<td><div style="margin: -6px;">&nbsp;</div></td>
				</tr>
				<tr>
					<td width="130">ទឹកប្រាក់សរសេរជាអក្សរ <br>Amount In Word</td>
					<td style="width:30%;"><?=convert_number_to_words($total_paid).'ដុល្លារសហរដ្ឋអាមេរិកគត់';?> <br>
						<div style="border-bottom:1px solid black;width: 100%;"></div>
					</td>
					<td width="50px;">&nbsp; </td>
					<td>ទូទាត់ដោយ<br> Paying By</td>
					<td><div class="name_receive"><?= $payment->cash_account.$payment_info ?></div></td>
					
				</tr>
				<tr>
					<td><div style="margin: -6px;">&nbsp;</div></td>
				</tr>
				<tr>
					<td width="130">អត្ថន័យ <br> <?=lang('Payment Being For')?></td>
					<td colspan="4"> <?= html_entity_decode($payment->note); ?><br>
						<div style="border-bottom:1px solid black;width: 100%;"></div>
					</td>
				</tr>
				<tr>
					<td><div style="margin: -6px;">&nbsp;</div></td>
				</tr>
				<tr>
					<td colspan="2"><?=lang('cash_paid_cannot_returned')?></td>
					
					<td width="50px;">&nbsp; </td>
					<td><?=lang('ថ្ងៃខែឆ្នាំ')?><br><?= lang('date') ?></td>
					<td><div class="name_receive"><?= $this->cus->hrsd($payment->date) ?></div></td>
				</tr>
				<tr>
					<td><div style="margin: -6px;">&nbsp;</div></td>
				</tr>
			</table>

			<tr>
				<td>
					<table class="table_item">
						<thead class="hidden">
							<tr>
								<th><?= lang("#"); ?></th>
								<th><?= lang("reference"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("discount"); ?></th>
								<th><?= lang("paid"); ?></th>
							</tr>
						</thead>
						<tbody id="tbody" class="hidden">
							<?= $tbody ?>
						</tbody>
						<tbody id="tfooter" class="hidden">
							<tr>
								<td class="text_right" colspan="3"><b><?= lang('total') ?> : </b></td>
								<td class="text_right"><b><?= $this->cus->formatMoney($total_discount) ?></b></td>
								<td class="text_right"><b><?= $this->cus->formatMoney($total_paid) ?></b></td>
							</tr>
							
							<?php if($payment->note){ ?>
								<tr>
									<td style="border:none !important" colspan="5"><b><?= lang('note') ?> : </b><?= html_entity_decode($payment->note); ?></td>
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
                                <thead class="footer_items">
                                    <th class="text_cen con_content" style="width: 50%;"><?= lang("អ្នកផ្គត់ផ្គង់/Suppliers");?></th>
                                    <th class="text_left con_content hidden" style="width: 35%;"><?= lang("អ្នកលក់/Seller");?></th>
                                    <th class="text_left con_content" style="width: 50%;"><?= lang("បេឡាករ/Cashier");?></th>
                                    
                                </thead>
                                <tbody class="footer_item_bodys">
                                    <td class="footer_item_bodys" style="height:60px;"></td>
                                    <td class="footer_item_bodys"></td>
                                    <td class="footer_item_bodys"></td>
                                </tbody>
                                <thead class="footer_item_footers">
                                    <th class="footer_item_footers">
		                                <div class="footer_name">ឈ្មោះ <?= $supplier->company?></div>
		                                <div class="footer_line">................................................</div>
                            		</th>
                                    <th class="footer_item_footers text_left hidden">
		                                <div class="footer_name">ឈ្មោះ <?= $inv->saleman_name?></div>
		                                <div class="footer_line">................................................</div>
                            		</th>
                                    <th class="footer_item_footers text_left">
			                            <div class="footer_name">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></div>
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
	.footer_name{
        margin-bottom: -26px;
        font-weight: bold;
        font-size: 13px;
        text-align: center;

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
	 .footer_item_bodys{
        line-height: 70px;
        padding-top: 70px !important;
    }
    .footer_item_footers{
        text-align:left !important;
        line-height:30px !important;
    }
    .footer_line{
    	margin-top: 4px;
    	text-align: center;
    }
     .footer_items th{ 
        text-align:center !important;
        line-height:30px !important;  
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
	p{
		display: inline;
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
ƒ