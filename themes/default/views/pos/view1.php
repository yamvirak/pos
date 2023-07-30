<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($modal) { ?>
	<div class="modal-dialog no-modal-header" role="document"><div class="modal-content">
		<div class="modal-body">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
<?php } ?>
		<html>
			<?php if (!$modal) { ?>
				<head>
					<meta charset="utf-8">
					<title><?=$page_title . " " . lang("no") . " " . $inv->id;?></title>
					<base href="<?=base_url()?>"/>
					<meta http-equiv="cache-control" content="max-age=0"/>
					<meta http-equiv="cache-control" content="no-cache"/>
					<meta http-equiv="expires" content="0"/>
					<meta http-equiv="pragma" content="no-cache"/>
					<link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
					<link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
					<script type="text/javascript" src="<?=$assets?>js/jquery-2.0.3.min.js"></script>
					<style type="text/css" media="all">
						body { color: #000; }
						#wrapper { max-width: 480px; margin: 0 auto; padding-top: 20px; }
						.btn { border-radius: 0; margin-bottom: 5px; }
						.bootbox .modal-footer { border-top: 0; text-align: center; }
						h3 { margin: 5px 0; }
						.order_barcodes img { float: none !important; margin-top: 5px; }
						@media print {
							.no-print { display: none; }
							#wrapper { max-width: 480px; width: 100%; min-width: 250px; margin: 0 auto; }
							.no-border { border: none !important; }
							.border-bottom { border-bottom: 1px solid #ddd !important; }
						}
					</style>
					<script type="text/javascript">
						<?php if(isset($_GET['q']) && $_GET['q']==1){ ?>
							window.print();
							setTimeout(function(){ 
								location.href= "<?= site_url("pos") ?>";
							}, 600);
						<?php } ?>
					</script>
				</head>
			<?php } ?>
			<body>
				<div id="wrapper">
					<div id="receiptData">
						<div class="no-print">
							<?php 
								$inv_currencies = json_decode($inv->currencies);
								if($inv_currencies){
									$currencies = false;
									foreach($inv_currencies as $currency){
										$currencies[$currency->currency] = $currency;
									}
									$currency = $currencies['KHR'];
								}
							
							if ($message) { 
								?>
								<div class="alert alert-success">
									<button data-dismiss="alert" class="close" type="button">×</button>
									<?=is_array($message) ? print_r($message, true) : $message;?>
								</div>
								<?php 
							} ?>
						</div>
						<div id="receipt-data">
							<table style="width:100%">
								<tr>
									<td class="text-center"><?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?></td>
								</tr>
								<tr>
									
									<td class="text-center">
										<b style="font-size:20px;"><?= $biller->name ?></b>
										<p class="font_14"> <?= $biller->address . " " . $biller->city ?></p>
										<p class="font_14"> <?= $biller->phone ?></p>
									</td>
									
								</tr>
							</table>
							<table  class="font_14" style="width:100%; margin-bottom:10px">
								<?php if($pos->queue_enable == 1 || (isset($table) && $table != false && $table->name !='')) { ?>
									<tr>
										<?php if ($pos->queue_enable == 1) { ?>
											<td><?= lang("Q") ?> : <?= $pos->queue_number ?></td>
										<?php } if((isset($table) && $table != false && $table->name !='')){ ?>
											<td><?= lang("T") ?> : <?= $table->name ?></td>
										<?php } ?>
									</tr>
								<?php } ?>
								<tr>
									<td><?= lang("N") ?> : <?= $inv->reference_no . ($inv->vehicle_plate ? "<span style='float:right'>".lang('vehicle_plate')." : ".$inv->vehicle_plate."</span>" : "") ?></td>
									<td class="text-right"><?= lang("D") ?> : <?= $this->cus->hrld($inv->date,1) . ($inv->vehicle_model ? "<span style='float:right'>".lang('vehicle_model')." : ".$inv->vehicle_model."</span>" : "") ?></td>
								</tr>
								<tr>
									<td><?= lang("U") ?> : <?= $created_by->last_name." ".$created_by->first_name . ($inv->vehicle_vin_no ? "<span style='float:right'>".lang('vehicle_vin')." : ".$inv->vehicle_vin_no."</span>" : "") ?></td>
									<td class="text-right"><?= lang("C") ?> : <?= ($customer->company && $customer->company != '-' ? $customer->company : $customer->name) .($inv->mechanic ? "<span style='float:right'>".lang('mechanic')." : ".$inv->mechanic."</span>" : "") ?></td>
								</tr>
							</table>
							<table class="table table-striped table-condensed font_14">
								<thead>
									<tr>
										<th class="text-center"><?=lang("#");?></th>
										<th class="text-center"><?=lang("description");?></th>
										<th class="text-center"><?=lang("qty");?></th>
										<th class="text-center"><?=lang("price");?>
										<?= ($inv->product_discount > 0 ? lang('dis') : '') ?>
										<th class="text-center"><?=lang("total_product_price");?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$r = 1; 
									
									foreach ($rows as $row) {
										$gross_unit_price = 0;
										if($row->item_discount > 0){
											$gross_unit_price = $row->unit_price + ($row->item_discount / $row->unit_quantity) ;
										}
										if($this->config->item('product_currency')==true && $row->currency_rate > 1){
											echo '<tr>
												<td class="no-border">' . $r . '</td>
												<td class="no-border">' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') .($row->comment!=''?'<br>'.$row->comment:'').($row->bom_type!=''?'<br>'.$row->bom_type:''). '</td>
												<td style="text-align:right" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity).'</td>
												<td style="text-align:right" class="no-border">' . $this->cus->formatOtherMoney(($gross_unit_price ? $gross_unit_price : $row->unit_price) * $row->currency_rate, $row->currency_code, -1) . ' '.($row->discount != 0 ? '<small>(' . $row->discount * $row->currency_rate . ')</small> ' : '') .'</td>
												<td style="text-align:right" class="no-border">' . $this->cus->formatOtherMoney($row->subtotal * $row->currency_rate, $row->currency_code, -1) . '</td>
											</tr>';
										}else{
											echo '<tr>
													<td class="no-border">' . $r . '</td>
													<td class="no-border">' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') .($row->comment!=''?'<br>'.$row->comment:'').($row->bom_type!=''?'<br>'.$row->bom_type:''). '</td>
													<td style="text-align:right" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity) .'</td>
													<td style="text-align:right" class="no-border">' . $this->cus->formatMoney($gross_unit_price ? $gross_unit_price : $row->unit_price) . ' '.($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '').'</td>
													<td style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->subtotal) . '</td>
												</tr>';
										}
										$r++;
									}

									?>
								</tbody>
								<tfoot>
									<?php if($inv->grand_total != $inv->total){ ?>
										<tr>
											<th class="text-right" colspan="4"><?=lang("total");?></th>
											<th class="text-right"><?=$this->cus->formatMoney($inv->total);?></th>
										</tr>
									
									<?php } ?>
									
									<?php
										if ($inv->order_tax != 0) {
											echo '<tr><th class="text-right" colspan="4">' . lang("tax") . '</th><th class="text-right">' . $this->cus->formatMoney($inv->order_tax) . '</th></tr>';
										}
										if ($inv->order_discount != 0) {
											echo '<tr><th class="text-right" colspan="4">' . lang("order_discount") . '</th><th class="text-right">' . ($inv->order_discount_id != 0 ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->cus->formatMoney($inv->order_discount) . '</th></tr>';
										}
										if ($inv->shipping != 0) {
											echo '<tr><th class="text-right" colspan="4">' . lang("shipping") . '</th><th class="text-right">' . $this->cus->formatMoney($inv->shipping) . '</th></tr>';
										}
									?>
										<tr>
											<th style="font-size:10px" class="text-right" colspan="4"><?= lang("grand_total")." (USD)" ?></th>
											<th class="text-right"><?= $this->cus->formatOtherMoney($inv->grand_total) ;?></th>
										</tr>
										<tr>
											<th style="font-size:10px" class="text-right" colspan="4>"><?= lang("grand_total")." (KHR)" ?></th>
											<th class="text-right"><?= $this->cus->formatKhMoney($inv->grand_total,$currency->rate) ;?></th>
										</tr>
								
									
									<?php if ($this->cus->formatDecimal($inv->paid) < $this->cus->formatDecimal($inv->grand_total)) { ?>
										<tr>
											<th class="text-right" colspan="4"><?=lang("paid_amount");?></th>
											<th class="text-right"><?=$this->cus->formatMoney($inv->paid);?></th>
										</tr>
										<tr>
											<th class="text-right" colspan="4"><?=lang("due_amount");?></th>
											<th class="text-right"><?= $this->cus->formatMoney($inv->grand_total - $inv->paid) ?></th>
										</tr>
									<?php } ?>
								</tfoot>
							</table>
							<?php
								if ($payments) {
									echo '<table class="font_14 table table-striped table-condensed" style="text-align:center;"><tbody>';
									foreach ($payments as $payment) {
										echo '<tr>';
											echo '<td>' . lang("paid_by") . ': ' . $payment->paid_by . '</td>';
											echo '<td>' . lang("paying") . ': ' . $this->cus->formatMoney($payment->pos_paid) . '</td>';
											echo '<td>' . lang("change")."(USD)". ': ' . $this->cus->formatOtherMoney($payment->pos_balance > 0 ? ($payment->pos_balance) : 0) . '</td>';
											echo '<td>' . lang("change")."(KHR)" . ': ' . $this->cus->formatKhMoney($payment->pos_balance > 0 ? ($payment->pos_balance) : 0,$currency->rate) . '</td>';
										echo '</tr>';
									}
									echo '</tbody></table>';
								}
							?>
							
							<?= $inv->note ? '<p class="text-center font_14">' . $this->cus->decode_html($inv->note) . '</p>' : ''; ?>
							<?= $inv->staff_note ? '<p class="no-print font_14"><strong>' . lang('staff_note') . ':</strong> ' . $this->cus->decode_html($inv->staff_note) . '</p>' : ''; ?>
							<?= $biller->invoice_footer ? '<p class="text-center font_14">'.$this->cus->decode_html($biller->invoice_footer).'</p>' : ''; ?>
						</div>
						<div style="clear:both;"></div>
						<?php if ($pos->queue_enable == 1) {?>
							<script type="text/javascript">
								$(function(){
									$("#print-sticker").on("click", sticker_print);
									<?php if(isset($_GET['q']) && $_GET['q']==1){ ?>
										sticker_print();
									<?php } ?>
									function sticker_print(){
											var socket_data = {
												'sale_id' : "<?= $inv->id ?>",
												'number' : "<?= $pos->queue_number ?>",
												'printer': <?= json_encode($printer); ?>,
											};
											$.ajax({
												url : "<?= site_url("pos/p_sticker") ?>",
												type : "GET",
												data : {data: JSON.stringify(socket_data)},
												success : function(data){
													sticker(data);
												}
											});
											return false;
									}
									function sticker(elem)
									{
										var mywindow = window.open('', 'PRINT', 'height=400,width=600');
										mywindow.document.write('<html><head><title>' + document.title  + '</title>');
										mywindow.document.write('</head><body >');
										mywindow.document.write('<div id="socket_data">'+elem+'</div>');
										mywindow.document.write('</body></html>');
										mywindow.document.close();
										mywindow.focus();
										mywindow.print();
										mywindow.close();
										return true;
									}
									
								});
							</script>
						<?php } ?>
					</div>

					<div id="buttons" style="padding-top:10px;" class="no-print">
						<hr>
						<span class="pull-right col-xs-12">
							<?php 
							if ($pos->remote_printing == 1) {
								echo '<button onclick="window.print();" class="btn btn-block btn-primary print">'.lang("print").'</button>';
							} else {
								echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
								echo '<button onclick="return openCashDrawer()" class="btn btn-block btn-default">'.lang("open_cash_drawer").'</button>';
							}
							?>
						</span>
						<?php if ($pos->queue_enable == 1) { ?>
							<span class="col-xs-12">
								<a class="btn btn-block btn-danger" href="#" id="print-sticker"><?= lang("print_sticker"); ?></a>
							</span>
						<?php } ?>
						<span class="col-xs-12">
							<a class="btn btn-block btn-warning" href="<?= site_url('pos'); ?>"><?= lang("back_to_pos"); ?></a>
						</span>
						<?php if ($pos->remote_printing == 1) { ?>
							<div style="clear:both;"></div>
							<div class="col-xs-12 hidden" style="background:#F5F5F5; padding:10px;">
								<p style="font-weight:bold;">
									Please don't forget to disble the header and footer in browser print settings.
								</p>
								<p style="text-transform: capitalize;">
									<strong>FF:</strong> File &gt; Print Setup &gt; Margin &amp; Header/Footer Make all --blank--
								</p>
								<p style="text-transform: capitalize;">
									<strong>chrome:</strong> Menu &gt; Print &gt; Disable Header/Footer in Option &amp; Set Margins to None
								</p>
							</div>
						<?php } ?>
						<div style="clear:both;"></div>
					</div>
				</div>

			</body>
		</html>
<?php if(!$modal) { ?>	
	<script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?php } else { ?>
		</div>
	</div>	
<?php } ?>	
	
<?php /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */ ?>
<?php include 'remote_printing.php'; ?>	


<style>
	.font_14{
		font-size:14px !important
	}
	@media print {
		.font_14 {
           font-size:11px !important
		}
	}
</style>













