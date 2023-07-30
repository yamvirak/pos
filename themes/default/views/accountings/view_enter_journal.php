
<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row')-100;
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

	<?php 
function convert_number_to_words($number) {

    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ' ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'Zero',
        1                   => 'One',
        2                   => 'Two',
        3                   => 'Three',
        4                   => 'Four',
        5                   => 'Five',
        6                   => 'Six',
        7                   => 'Seven',
        8                   => 'Eight',
        9                   => 'Nine',
        10                  => 'Ten',
        11                  => 'Eleven',
        12                  => 'Twelve',
        13                  => 'Thirteen',
        14                  => 'Fourteen',
        15                  => 'Fifteen',
        16                  => 'Sixteen',
        17                  => 'Seventeen',
        18                  => 'Eighteen',
        19                  => 'Nineteen',
        20                  => 'Twenty',
        30                  => 'Thirty',
        40                  => 'Fourty',
        50                  => 'Fifty',
        60                  => 'Sixty',
        70                  => 'Seventy',
        80                  => 'Eighty',
        90                  => 'Ninety',
        100                 => 'Hundred',
        1000                => 'Thousand',
        1000000             => 'Million',
        1000000000          => 'Billion',
        1000000000000       => 'Trillion',
        1000000000000000    => 'Quadrillion',
        1000000000000000000 => 'Quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}


?>

<style type="text/css">
	.border_bottom{
		border-bottom: 1px dotted black;
    	padding: 0px 4px 0px 4px;
	}
</style>

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
                                    <div class="font_address"><?= $biller->address?></div>
                                    <div class="font_address"><?= lang('tel').' : '. $biller->phone ?></div> 
                                    <div class="font_address"><?= lang('email').' : '. $biller->email ?></div>   
                                </td> 
                                <td class="text_center" style="width:20%">
                                    <?= $this->cus->qrcode('link', urlencode(site_url('accountings/view_enter_journal/' . $inv->id)), 2); ?>
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
							<!-- <td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('enter_journals') ?></i></b></span></td> -->
							<?php if($inv->jn_type =='payment'){ ?>
									<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('payment_voucher') ?></i></b></span></td>
							<?php }else if($inv->jn_type =="receipt"){ ?>
									<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('receipt_voucher') ?></i></b></span></td>
							<?php }else {?>
									<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('journals_voucher') ?></i></b></span></td>
							<?php } ?>
							<td valign="bottom" style="width:20%"><hr class="hr_title"></td>
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
									<?php if($inv->jn_type =='payment' && $inv->type == "supplier"){ ?>

									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('supplier') ?></i></b>
									</legend>

									<table>
										<tr>
											<td><?= lang('company') ?></td>
											<td> : <strong><?= $supplier->company;?></strong></td>
										</tr>
										<tr>
											<td><?= lang('name') ?></td>
											<td> : <strong><?= $supplier->name;?></strong></td>
										</tr>
										<tr>
											<td><?= lang('address') ?></td>
											<td> : <?= $supplier->address;?></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?></td>
											<td> : <?= $supplier->phone ?></td>
										</tr>
									</table>

									<?php }else if($inv->jn_type =="payment" && $inv->type ="customer"){ ?>

									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('received_by') ?></i></b>
									</legend>

									<table>
										<tr>
											<td><?= lang('company') ?></td>
											<td> : <strong><?= $customer->company;?></strong></td>
										</tr>
										<tr>
											<td><?= lang('name') ?></td>
											<td> : <strong><?= $customer->name;?></strong></td>
										</tr>
										<tr>
											<td><?= lang('address') ?></td>
											<td> : <?= $customer->address;?></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?></td>
											<td> : <?= $customer->phone ?></td>
										</tr>
									</table>

									<?php }else if($inv->jn_type =="receipt"){ ?>

									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('received_from') ?></i></b>
									</legend>

									<table>
										<tr>
											<td><?= lang('company') ?></td>
											<td> : <strong><?= $customer->company;?></strong></td>
										</tr>
										<tr>
											<td><?= lang('name') ?></td>
											<td> : <strong><?= $customer->name;?></strong></td>
										</tr>
										<tr>
											<td><?= lang('address') ?></td>
											<td> : <?= $customer->address;?></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?></td>
											<td> : <?= $customer->phone ?></td>
										</tr>
									</table>

									<?php }else {?>
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('journals_voucher') ?></i></b>
									</legend>

									<table>
										<tr>
											<td><?= lang('company') ?></td>
											<td> : <strong><?= $biller->company;?></strong></td>
										</tr>
										<tr>
											<td><?= lang('name') ?></td>
											<td> : <strong><?= $biller->name;?></strong></td>
										</tr>
										<tr>
											<td><?= lang('address') ?></td>
											<td style="font-size: 11px;"> : <?= $biller->address;?></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?></td>
											<td> : <?= $biller->phone ?></td>
										</tr>
									</table>

									<?php } ?>

									
								</fieldset>
							</td>
							<td style="width:40%">
								<fieldset style="margin-left:5px !important">
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<?php 
											$purchase_link = ''; 
											if($inv->purchase_id){ 
												$purchase_link= ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('purchases/modal_view/'.$inv->purchase_id).'"><i class="fa fa-external-link no-print"></i></a><br>';?>
												<tr>
													<td><?= lang('return_ref') ?></td>
													<td style="text-align:left"> : <b><?= $inv->return_purchase_ref ?></b></td>
												</tr>
										<?php } ?>

									

										<?php if($inv->jn_type =='payment'){ ?>
										
										<tr>
											<td><?= lang('pv_reference') ?></td>
											<td style="text-align:left"> : <b><?= $inv->reference_no.$purchase_link ?></b></td>
										</tr>
										<?php }else if($inv->jn_type =="receipt"){ ?>
										<tr>
											<td><?= lang('rv_reference') ?></td>
											<td style="text-align:left"> : <b><?= $inv->reference_no.$purchase_link ?></b></td>
										</tr>
										<?php }else {?>
										<tr>
											<td><?= lang('jv_reference') ?></td>
											<td style="text-align:left"> : <b><?= $inv->reference_no.$purchase_link ?></b></td>
										</tr>
										<?php } ?>


										
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->cus->hrsd($inv->date) ?></td>
										</tr>
										<tr>
											<td><?= lang('created_by') ?></td>
											<td style="text-align:left"> : <?= $created_by->first_name." ".$created_by->last_name;?></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?></td>
											<td style="text-align:left"> : <?= $created_by->phone;?></td>
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
				$total = 0;
				foreach ($rows as $row){
					if($row->amount < 0){
						$credit = $this->cus->formatMoney(abs($row->amount));
						$debit = '';
					}else{
						$total += $row->amount;
						$credit = '';
						$debit = $this->cus->formatMoney($row->amount);
					}
					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_center">'.$row->account_code.'</td>
									<td class="text_left">'.$row->account_name.'</td>
									<td class="text_left">'.$row->description.'</td>
									<td class="text_right">'.$debit.'</td>
									<td class="text_right">'.$credit.'</td>
								</tr>';		
					$i++;
				}
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th style="width:30px;"><?= lang("#"); ?></th>
								<th style="width:80px;"><?= lang("AC.Code"); ?></th>
								<th style="width:160px;"><?= lang("AC.Name"); ?></th>
								<th style="width:260px;"><?= lang("description"); ?></th>
								<th><?= lang("debit"); ?></th>
								<th><?= lang("credit"); ?></th>
							</tr>
						</thead>
						<tbody id="tbody">
							<?= $tbody ?>
						</tbody>
						<tfoot>
							<tr>
								
							</tr>
							<tr>
								<td class="text_left" colspan="4"><b><?=lang('Amount in Word:')?></b>&nbsp;
									<?php
								  echo convert_number_to_words($total);?>
								  <strong style="float: right;"><?= lang('total') ?> : </strong>
								</td>
								
								<td colspan="2" class="text_right"><b><?= $this->cus->formatMoney($total) ?></b></td>
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
							<th class="footer_item_footer text_left">Name:</span></th>
							<th class="footer_item_footer text_left">Name:</span></th>
							<th class="footer_item_footer text_left">Name:</th>
						</thead>
					</table>
				</td>
			</tr>
		</tfoot>
	</table>
	
		<div class="clearfix"></div>
	
		<div class="buttons no-print" style="margin-top:20px; margin-bottom:20px;">
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
	.footer_item th{
		border:1px solid #000000 !important;
		background-color : #9996 !important;
		text-align:center !important;
		line-height:30px !important;
		width: 25%;
	}
	.footer_item_body{
		border:1px solid #000000 !important;
		line-height: 150px;
		padding-top: 130px !important;
	}
	.footer_item_footer{
		border:1px solid #000000 !important;
		text-align:left !important;
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
						transaction : "Enter Journal",
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
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
    });
	
</script>

<script type="text/javascript">
	// System for American Numbering 
var th_val = ['', 'thousand', 'million', 'billion', 'trillion'];
// System for uncomment this line for Number of English 
// var th_val = ['','thousand','million', 'milliard','billion'];
 
var dg_val = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
var tn_val = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
var tw_val = ['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
function toWordsconver(s) {
  s = s.toString();
    s = s.replace(/[\, ]/g, '');
    if (s != parseFloat(s))
        return 'not a number ';
    var x_val = s.indexOf('.');
    if (x_val == -1)
        x_val = s.length;
    if (x_val > 15)
        return 'too big';
    var n_val = s.split('');
    var str_val = '';
    var sk_val = 0;
    for (var i = 0; i < x_val; i++) {
        if ((x_val - i) % 3 == 2) {
            if (n_val[i] == '1') {
                str_val += tn_val[Number(n_val[i + 1])] + ' ';
                i++;
                sk_val = 1;
            } else if (n_val[i] != 0) {
                str_val += tw_val[n_val[i] - 2] + ' ';
                sk_val = 1;
            }
        } else if (n_val[i] != 0) {
            str_val += dg_val[n_val[i]] + ' ';
            if ((x_val - i) % 3 == 0)
                str_val += 'hundred ';
            sk_val = 1;
        }
        if ((x_val - i) % 3 == 1) {
            if (sk_val)
                str_val += th_val[(x_val - i - 1) / 3] + ' ';
            sk_val = 0;
        }
    }
    if (x_val != s.length) {
        var y_val = s.length;
        str_val += 'point ';
        for (var i = x_val + 1; i < y_val; i++)
            str_val += dg_val[n_val[i]] + ' ';
    }
    return str_val.replace(/\s+/g, ' ');
}

	var number = 1525;  
    var Inwords = toWordsconver(number);
</script>

