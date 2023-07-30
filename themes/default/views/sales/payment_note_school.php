<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 15;
	$font_size = 14;
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 7.5; 
	$margin = $font_size - 5;
	$margin_signature = $font_size;
?>
<div class="modal-dialog modal-lg main_content">
	<div class="modal-content">
		<div class="modal-body">
			<table>
				<tr>
					<th>
						<table>
							<tr>
								<td class="text_center" style="width:50%">
									<div style="font-family: Khmer Muol Light !important; font-size:<?= $font_size+6 ?>px">សាលាអន្តរជាតិអាប៊ែនឌែនឡៃ</div>
									<div style="font-size:<?= $font_size+2 ?>px">Abundant Life International School</div>
								</td>
								<td class="text_center" style="width:50%">
									<div style="font-family: Khmer Muol Light !important; font-size:<?= $font_size+6 ?>px">ប័ណ្ឌទទួលប្រាក់</div>
									<div style="font-size:<?= $font_size+2 ?>px">RECEIPT No: <?= $payment->reference_no ?></div>
								</td>
							</tr>
						</table>
					</th>
				</tr>
				<tr>
					<th>
						<table class="table_school" style="margin-top:20px">
							<tr>
								<td class="text_left" style="border-bottom:none !important">ឈ្មោះ/ Name: <?= $student->lastname.' '.$student->firstname ?></td>
								<td style="border-bottom:none !important">ភេទ</td>
								<td style="border-bottom:none !important">ថ្នាក់ទី</td>
								<td style="border-bottom:none !important">កាលបរិច្ឆេទ</td>
							</tr>
							<tr>
								<td class="text_left" style="border-bottom:none !important;border-top:none !important">អត្តលេខ/ ID No: <?= $student->number ?></td>
								<td style="border-bottom:none !important;border-top:none !important">Sex</td>
								<td style="border-bottom:none !important;border-top:none !important">Grand</td>
								<td style="border-bottom:none !important;border-top:none !important">Invoice Date</td>
							</tr>
							<tr>
								<td class="text_left" style="border-top:none !important">ថ្ងៃខែឆ្នាំកំណើត/ Date of birth: <?= $this->cus->hrsd($student->dob) ?></td>
								<td style="border-top:none !important"><?= ($student->gender == "male" ? "ប្រុស" : "ស្រី") ?></td>
								<td style="border-top:none !important"><?= $study->level ?></td>
								<td style="border-top:none !important"><?= $this->cus->hrsd($payment->date) ?></td>
							</tr>
						</table>
					</th>
				</tr>

				<tbody>
					<?php
						$tbody = '';
						$i=1;
						
						if($rows){
							$gross_unit_price = 0;
							foreach ($rows as $row){
								if($row->item_discount > 0){
									$gross_unit_price = $row->unit_price + ($row->item_discount / $row->unit_quantity) ;
								}
								$tbody .='<tr>
												<td class="text_center">'.$i.'</td>
												<td class="text_left">
													'.$row->product_name.'
													'.($row->details ? '<br>' . $row->details : '').'
												</td>
												<td class="text_center">'.$this->cus->formatQuantity($row->unit_quantity).' '.$row->unit_name.'</td>
												<td class="text_right">'.$this->cus->formatMoney($gross_unit_price ? $gross_unit_price : $row->unit_price).'</td>
												<td class="text_right">'.$this->cus->formatMoney($row->subtotal).'</td>
											</tr>';		
								$i++;
							}
						}
						
						$cash_info = $this->site->getCashAccountByID($payment->paid_by);
						
						$tfooter = '<tr>
										<td style="border:none !important" colspan="2">
											<div style="font-size:12px; font-family: Khmer Muol Light !important;">ប្រភេទនៃការទូទាត់: 
												<input '.($cash_info->type == "cash" ? "checked" : "").' type="checkbox"/> សាច់ប្រាក់​
												<input '.($cash_info->type == "cheque" ? "checked" : "").' style="margin-left:20px" type="checkbox"/> សែក
												<input '.($cash_info->type == "bank" ? "checked" : "").' style="margin-left:20px" type="checkbox"/> ធនាគារ
											</div>
											
										</td>
										<td colspan="2" class="text_right"><b>'.lang("សុរប/Total").'</b></td>
										<td class="text_right"><b>'.$this->cus->formatMoney($inv->grand_total).'</b></td>
									</tr>';
						$tfooter .= '<tr>
										<td style="border:none !important" colspan="2">
											<div style="font-size:12px; font-family: Khmer Muol Light !important;">យោងវិក្កយបត្រលេខ: '.$inv->reference_no.'</div>
										</td>
										<td colspan="2" class="text_right"><b>'.lang("ទឹកប្រាក់បង់/Pay").'</b></td>
										<td class="text_right"><b>'.$this->cus->formatMoney($payment->amount).'</b></td>
									</tr>';
						$tfooter .= '<tr>
										<td style="border:none !important" colspan="2"></td>
										<td colspan="2" class="text_right"><b>'.lang("បញ្ចុះតម្លៃ/Discount").'</b></td>
										<td class="text_right"><b>'.$this->cus->formatMoney($payment->discount).'</b></td>
									</tr>';
						
					?>
					<tr>
						<td>
							<table class="table_item" style="margin-top:10px">
								<thead>
									<tr>
										<th rowspan="2">ល.រ <br>No</th>
										<th rowspan="2">បរិយាយ<br>Description</th>
										<th colspan="3">ចំនួនទឹកប្រាក់ដែលត្រូវបង</th>
									</tr>
									<tr>
										<th>QTY</th>
										<th>Price</th>
										<th>Amount</th>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tbody id="tfooter">
									<?= $tfooter ?>
									<?php if($siblings){?>
										<tr>
											<td colspan="5" style="border:none !important">
												<?php 
													echo "<div style='font-family: Khmer Muol Light !important;'>ព័ត៌មានបងប្អូនបង្កើតដែលកំពុងសិក្សានៅសាលា:</div>";
													$u = 0;
													foreach($siblings as $sibling) {
														$u++;
														$sibling_study_info = $this->sales_model->getLastStudyInfo($sibling->id,$study->study_year);
														echo "កូនទី".$this->cus->numberToKhmer($u).". ឈ្មោះ ".$sibling->lastname." ".$sibling->firstname." 
															ថ្នាក់ទី ".$sibling_study_info->level."
															សិក្សានៅអគារ ".$sibling->biller."<br>";
													}
												?>
											</td>
										</tr>
									<?php } ?>
									<tr>
										<td colspan="5" style="font-size:12px !important; border: none !important" class="footer_des">
											<div style="font-family: Khmer Muol Light !important; font-size:14px !important">សម្គាល់:</div>
											<div style="font-family: Khmer !important;">
												១-ប្រាក់ដែលបានបង់រួច មិនអាចដកវិញបានឡើយ / Payment made is nonrefunable<br>
												២-សូមអានលក្ខខណ្ឌផ្សេងៗនៅខាងក្រោមនៃប័ណ្ឌនេះ / Please read the term and condition on the other side of this receipt
											</div>
											<?= $this->cus->decode_html($payment->note) ?>
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
									<td class="text_center" style="width:50%; font-family: Khmer !important;">អ្នកបង់ប្រាក់/Paid By</td>
									<td class="text_center" style="width:50%; font-family: Khmer !important;">អ្នកទទួលប្រាក់/Received By</td>
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
					<?php if ($payment->attachment) { ?>
						<div class="btn-group">
							<a href="<?= site_url('welcome/download/' . $payment->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
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
		#myModal .modal-content {
            display: none !important;
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
		font-family: Khmer !important;
		
	}
	.table_item td{
		border:1px solid black;
		line-height:<?=$td_line_height?>px !important;
		font-family: Khmer!important;
	}
	
	.table_school{
		border:1px solid black !important;
		text-align:center !important;
		line-height:30px !important;
	}
	.table_school td{
		border:1px solid black;
		font-size:14px;
		font-family: Khmer;
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


