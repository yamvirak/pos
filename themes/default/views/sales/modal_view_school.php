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
									<div style="font-family: Khmer Muol Light !important; font-size:<?= $font_size+6 ?>px">វិក្កយប័ត្រ</div>
									<div style="font-size:<?= $font_size+2 ?>px">INVOICE No: <?= $inv->reference_no ?></div>
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
								<td style="border-top:none !important"><?= $this->cus->hrsd($inv->date) ?></td>
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
						$amount_balance = $inv->grand_total - ($payment ? ($payment->paid ? $payment->paid : 0) + ($payment->discount ? $payment->discount : 0) : 0);
						$tfooter = '<tr>
										<td style="border:none !important" colspan="2"></td>
										<td colspan="2" class="text_right"><b>'.lang("សុរប/Total").'</b></td>
										<td class="text_right"><b>'.$this->cus->formatMoney($inv->grand_total).'</b></td>
									</tr>';

						if($payment){
							if ($payment->paid != 0) {
								$tfooter .= '<tr>
												<td style="border:none !important" colspan="2"></td>
												<td colspan="2" class="text_right"><b>'.lang("បានទូទាត់/Paid").'</b></td>
												<td class="text_right"><b>'.$this->cus->formatMoney($payment->paid).'</b></td>
											</tr>';

							}	
							if ($payment->discount != 0) {
								$tfooter .= '<tr>
												<td style="border:none !important" colspan="2"></td>	
												<td colspan="2" class="text_right"><b>'.lang("បញ្ចុះតម្លៃ/Discount").'</b></td>
												<td class="text_right"><b>'.$this->cus->formatMoney($payment->discount).'</b></td>
											</tr>';
							}
						}		
						
						if($amount_balance <> $inv->grand_total){
							$tfooter .= '<tr>
											<td style="border:none !important" colspan="2"></td>
											<td colspan="2" class="text_right"><b>'.lang("នៅសល់/Balance").'</b></td>
											<td class="text_right"><b>'.$this->cus->formatMoney($amount_balance).'</b></td>
										</tr>';	
						}
					?>
					<tr>
						<td>
							<table class="table_item_main" style="margin-top:10px">
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
								<tbody id="tbody_main">
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
												១-សូមលោក លោកស្រីអញ្ចើញមកបង់ប្រាក់តាមកាលបរិច្ឆេទដែលសាលាបានកំណត់ដូចមានបែងចែងក្នុង
												<span style="font-family: Khmer Muol Light !important">
													គោលការណ៏នៃការបង់ប្រាក់ថ្លៃសិក្សា
												</span>។<br>
												២-ករណីដែលការបង់ប្រាក់ធ្វើឡើងក្រោយកាលបរិច្ឆេទកំណត់ សាលានឹងពិន័យ១០%នៃប្រាក់ដែលត្រូវបង់។ ក្នុងករណីដែលមាតាបិតា ឬអាណាព្យាបាលមិនបានបង់ប្រាក់ហួសកាលបរិច្ឆេទកំណត់ ចំនួន១០ថ្ងៃនៃថ្ងៃសិក្សា
												សាលានឹងផ្អាកការសិក្សារបស់សិស្ស ជាបណ្ដោះអាសន្ន រហូតដល់ថ្ងៃដែលមាតាបិតា ឬអាណាព្យាបាលមកធ្វើការទូទាត់ប្រាក់ថ្លៃសិក្សារួចរាល់។
											</div>
											<?= $this->cus->decode_html($inv->note) ?>
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
									<td class="text_center" style="width:50%; font-family: Khmer !important;">អ្នករៀបចំ/Prepared By</td>
									<td class="text_center" style="width:50%; font-family: Khmer !important;">អ្នកត្រួតពិនិត្យ/Checked By</td>
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
					<div class="btn-group"> 
						<a href="<?= site_url('sales/add_payment/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('add_payment') ?>">
							<i class="fa fa-dollar"></i>
							<span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a href="<?= site_url('sales/payments/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('view') ?>" data-toggle="modal" data-target="#myModal2">
							<i class="fa fa fa-money"></i>
							<span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
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
	#tbody_main .td_print{
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
	.table_item_main th{
		border:1px solid black !important;
		background-color : #428BCD !important;
		text-align:center !important;
		line-height:30px !important;
		font-family: Khmer !important;
		
	}
	.table_item_main td{
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


