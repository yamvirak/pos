<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog no-modal-header" role="document">
	<div class="modal-content">
		<div class="modal-body">
			<button type="button" class="close hidden" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
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
			<div id="wrapper">
				<div id="receiptData">
					<div id="receipt-data">
						<div class="text-center">
							<?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
							<h3 style="text-transform:uppercase;">
								<?=$biller->name != '-' ? $biller->name : $biller->company;?>
							</h3>
							<?php
							echo "<p>" . $biller->address . " 
								" . $biller->city . " 
								" . $biller->postal_code . " 
								" . $biller->state . " 
								" . $biller->country .
								"<br>" . lang("tel") . ": " . $biller->phone;
							echo '</p>';
							?>
							<h2 style="text-decoration:underline;font-weight:bold;"><?=lang("repair_receipt")?></h2>
						</div>
						<table style="width:100%;font-weight:bold;">
							<tr>
								<td style="vertical-align:top;">
									<table style="width:100%; font-weight:bold;">
										<tr>
											<td><?= lang('name') ?> : <strong><?= $customer->company ?></strong></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?> : <?= ($repair->phone?$repair->phone:$customer->phone) ?></td>
										</tr>
										<tr>
											<td><?= lang('brand') ?> : <?= ($brand?$brand->name:'n/a'); ?></td>
										</tr>
										<tr>
											<td><?= lang('model') ?> : <?= ($model?$model->name:'n/a'); ?></td>
										</tr>
										<tr>
											<td><?= lang('imei_number') ?> : <?= $repair->imei_number; ?></td>
										</tr>
										<tr>
											<td><?= lang('machine_type') ?> : <?= $machine_type?$machine_type->name:''; ?></td>
										</tr>
									</table>
								</td>
								<td style="vertical-align:top; text-align:right;">
									<table style="width:100%; font-weight:bold;">
										<tr>
											<td><?= lang('reference_no') ?> : <?= $inv->reference_no ?></td>
										</tr>
										<tr>
											<td><?= lang('date') ?> : <?= $this->cus->hrld($inv->date) ?></td>
										</tr>
										<tr>
											<td><?= lang('receive_date') ?>: <?= $this->cus->hrld($repair->receive_date) ?></td>
										</tr>
										<tr>
											<td><?= lang('user') ?> : <?= $created_by->first_name . ' ' . $created_by->last_name; ?></td>
										</tr>												
									</table>
								</td>
							</tr>
						</table>
						<br/>
						<div style="clear:both;"></div>
						<table class="table table-striped table-condensed">
							<thead>
								<tr>
									<th class="text-center"><?=lang("#");?></th>
									<th class="text-center"><?=lang("description");?></th>
									<th class="text-center"><?=lang("quantity");?></th>
									<th class="text-center"><?=lang("unit_price");?><?= ($inv->total_discount > 0 ? ' ('.lang('dis').') ' : '') ?></th>
									<th class="text-center"><?=lang("subtotal");?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$r = 1; $category = 0;
								foreach ($rows as $row) {
									if($this->config->item('product_currency')==true && $row->currency_rate > 1){
										echo '<tr>
											<td style="text-align:center" class="no-border">' . $r . '</td>
											<td class="no-border">'. $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '').'
											<small>'.($row->item_note ? '<br>' . $row->item_note : '').'
											'.($row->comment ? '<br>' . nl2br($row->comment) : '').'</small></td>
											<td style="text-align:center" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity) .' '.$row->name. '</td>
											<td style="text-align:right" class="no-border">' . $this->cus->formatOtherMoney($row->unit_price * $row->currency_rate, $row->currency_code, -1) . ' '.($row->discount != 0 ? '<small>(' . $row->discount * $row->currency_rate . ')</small> ' : '') .'</td>
											<td style="text-align:right" class="no-border">' . $this->cus->formatOtherMoney($row->subtotal * $row->currency_rate, $row->currency_code, -1) . '</td>
										</tr>';
									}else{
										echo '<tr>
												<td style="text-align:center" class="no-border">' . $r . '</td>
												<td class="no-border">'. $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '').'
												<small>'.($row->item_note ? '<br>' . $row->item_note : '').'
												'.($row->comment ? '<br>' . nl2br($row->comment) : '').'</small></td>
												<td style="text-align:center" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity) .' '.$row->unit_name. '</td>
												<td style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->unit_price) . ' '.($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '').'</td>
												<td style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->subtotal) . '</td>
											</tr>';
									}
									$r++;
								}
								?>
							</tbody>
							<tfoot>
								<tr>
									<th class="text-right" colspan="4"><?=lang("total");?></th>
									<th class="text-right"><?=$this->cus->formatMoney($inv->total + $inv->product_tax);?></th>
								</tr>
								<?php
									if ($inv->order_tax != 0) {
										echo '<tr><th class="text-right" colspan="4">' . lang("tax") . '</th><th class="text-right">' . $this->cus->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
									}
									if ($inv->order_discount != 0) {
										echo '<tr><th class="text-right" colspan="4">' . lang("order_discount") . '</th><th class="text-right">' . ($inv->order_discount_id != 0 ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->cus->formatMoney($inv->order_discount) . '</th></tr>';
									}
									if ($inv->shipping != 0) {
										echo '<tr><th class="text-right" colspan="4">' . lang("shipping") . '</th><th class="text-right">' . $this->cus->formatMoney($inv->shipping) . '</th></tr>';
									}
								?>
								
								<tr>
									<th class="text-right" colspan="4"><?=lang("grand_total");?></th>
									<th class="text-right"><?=$this->cus->formatMoney($inv->grand_total);?></th>
								</tr>

								<?php if ($inv->paid < $inv->grand_total) {
									?>
									<tr>
										<th class="text-right" colspan="4"><?=lang("paid_amount");?></th>
										<th class="text-right"><?=$this->cus->formatMoney($inv->paid);?></th>
									</tr>
									<tr>
										<th class="text-right" colspan="4"><?=lang("balance");?></th>
										<th class="text-right"><?=$this->cus->formatMoney(($inv->grand_total + $inv->rounding) - $inv->paid);?></th>
									</tr>
									<?php 
								} ?>
							</tfoot>
						</table>
					</div>
					<small>
						<?= $biller->invoice_footer ? '<p class="text-center">'.$this->cus->decode_html($biller->invoice_footer).'</p>' : ''; ?>
					</small>
					<?= $this->cus->qrcode('link', urlencode(site_url('sales?id=' . $inv->id)), 2); ?>
					<div style="clear:both;"></div>
				</div>
				<div id="buttons" style="padding-top:10px;" class="no-print">
					<hr>
					<div class="btn-group btn-group-justified" role="group">
						<div class="btn-group" role="group">
							<button onclick="window.print()" class="btn btn-block btn-primary"><?= lang('print'); ?></button>
						</div>
						<div class="btn-group" role="group">
							<a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>"><?= lang('close'); ?></a>
						</div>
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>
		</div>
	</div>
</div>