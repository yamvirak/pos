<html>
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
        <style type="text/css" media="all">
            #wrapper { max-width: 570px; }
			table th, table th{
				font-size:22px;
				font-family: "Titillium Web","Suwannaphum", sans-serif;
			}
        </style>
    </head>

    <body>
		<div id="wrapper">
		
			<div id="receiptData">
				
				<div id="receipt-data">
				
					<table>
						<tr>
							<th style="text-align:center;">
								<?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
							</th>
						</tr>
						<tr>
							<th style="text-align:center; font-size:30px;">
								<?=$biller->name != '-' ? $biller->name : $biller->company;?>
							</th>
						</tr>
						<tr>
							<th style="text-align:center;">
								<?= $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country; ?>
							</th>
						</tr>
						<tr>
							<th style="text-align:center;">
								<?= $biller->phone; ?>
							</th>
						</tr>
						<tr>
							<th style="text-align:center;">
								<?= lang("invoice"); ?>
							</th>
						</tr>
						<tr>
							<th>&nbsp;</th>
						</tr>
					</table>
					
					<table>
						<tr>
							<th>
								<?= lang("customer") ?> : <?= ($customer->company && $customer->company != '-' ? $customer->company : $customer->name) ?>
							</th>
						</tr>
						<tr>
							<th>
								<?= lang("reference_no") ?> : <?= $inv->reference_no ?>
							</th>
						</tr>
						<tr>
							<th>
								<?= lang("user") ?> : <?= $created_by->first_name." ".$created_by->last_name ?>
							</th>
						</tr>
						<tr>
							<th>
								<?= lang("date") ?> : <?= $this->cus->hrld($inv->date) ?>
							</th>
						</tr>
						<?php if($inv->table_name){ ?>
							<tr>
								<th><?= lang("table") ?> : <?= $inv->table_name ?></th>
							</tr>
						<?php } ?>
						<tr>
							<th>&nbsp;</th>
						</tr>
					</table>
					
					<table class="table table-striped table-condensed">
						<thead>
							<tr style="background:#000; color:#FFF;">
								<th class="text-center"><?=lang("no");?></th>
								<th class="text-center"><?=lang("description");?></th>
								<th class="text-center"><?=lang("qty");?></th>
								<th class="text-center"><?=lang("unit_price");?></th>
								<th class="text-center"><?=lang("total_product_price");?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$r = 1; $category = 0;
							$tax_summary = array();
							foreach ($rows as $row) {
								if ($pos_settings->item_order == 1 && $category != $row->category_id) {
									$category = $row->category_id;
									echo '<tr><th colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></th></tr>';
								}
								if ($Settings->invoice_view == 1) {
									if (isset($tax_summary[$row->tax_code])) {
										$tax_summary[$row->tax_code]['items'] += $row->unit_quantity;
										$tax_summary[$row->tax_code]['tax'] += $row->item_tax;
										$tax_summary[$row->tax_code]['amt'] += ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
									} else {
										$tax_summary[$row->tax_code]['items'] = $row->unit_quantity;
										$tax_summary[$row->tax_code]['tax'] = $row->item_tax;
										$tax_summary[$row->tax_code]['amt'] = ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
										$tax_summary[$row->tax_code]['name'] = $row->tax_name;
										$tax_summary[$row->tax_code]['code'] = $row->tax_code;
										$tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
									}
								}
								echo '<tr>
										<th class="no-border">' . $r . '</th>
										<th class="no-border">' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') . '</th>
										<th style="text-align:right" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity) . '</th>
										<th style="text-align:right" class="no-border">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->cus->formatMoney($row->net_unit_price + ($row->item_tax / $row->unit_quantity)) . '</th>
										<th style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->subtotal) . '</th>
									</tr>';
								$r++;
							}
							if ($return_rows) {
								echo '<tr class="warning"><th colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></th></tr>';
								foreach ($return_rows as $row) {
									if ($pos_settings->item_order == 1 && $category != $row->category_id) {
										$category = $row->category_id;
										echo '<tr><th colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></th></tr>';
									}
									if ($Settings->invoice_view == 1) {
										if (isset($tax_summary[$row->tax_code])) {
											$tax_summary[$row->tax_code]['items'] += $row->unit_quantity;
											$tax_summary[$row->tax_code]['tax'] += $row->item_tax;
											$tax_summary[$row->tax_code]['amt'] += ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
										} else {
											$tax_summary[$row->tax_code]['items'] = $row->unit_quantity;
											$tax_summary[$row->tax_code]['tax'] = $row->item_tax;
											$tax_summary[$row->tax_code]['amt'] = ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
											$tax_summary[$row->tax_code]['name'] = $row->tax_name;
											$tax_summary[$row->tax_code]['code'] = $row->tax_code;
											$tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
										}
									}
									echo '<tr>
										<th class="no-border">' . $r . '</th>
										<th class="no-border">' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') . '</th>
										<th style="text-align:right" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity) . '</th>
										<th style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->net_unit_price + ($row->item_tax / $row->unit_quantity)) . '</th>
										<th style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->subtotal) . '</th>
									</tr>';
									$r++;
								}
							}

							?>
						</tbody>
						<tfoot>
							<?php 
								if ($payments) {                   
									foreach ($payments as $payment) {                        
										$currencies = json_decode($payment->currencies);
										$khr_rate = $currencies[1]->rate;
										$khr_name = $currencies[1]->currency;
									}
								}
							?>
							<tr>
								<th class="text-right" colspan="4"><?=lang("total");?></th>
								<th class="text-right"><?=$this->cus->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax));?></th>
							</tr>
							<?php
							if ($inv->order_tax != 0) {
								echo '<tr><th class="text-right" colspan="4">' . lang("order_tax") . '</th><th class="text-right">' . $this->cus->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
							}
							if ($inv->order_discount != 0) {
								echo '<tr><th class="text-right" colspan="4">' . lang("discount") . '</th><th class="text-right">' . ($inv->order_discount_id != 0 ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->cus->formatMoney($inv->order_discount) . '</th></tr>';
								echo '<tr><th class="text-right" colspan="4">' . lang("discount") .' ('. $khr_name .')'. '</th><th class="text-right">' . ($inv->order_discount_id != 0 ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->cus->formatMoney($inv->order_discount * $khr_rate) . '</th></tr>';
							}

							if ($inv->shipping != 0) {
								echo '<tr><th class="text-right" colspan="4">' . lang("shipping") . '</th><th class="text-right">' . $this->cus->formatMoney($inv->shipping) . '</th></tr>';
							}

							if ($return_sale) {
								if ($return_sale->surcharge != 0) {
									echo '<tr><th class="text-right" colspan="4">' . lang("discount") . '</th><th class="text-right">' . $this->cus->formatMoney($return_sale->surcharge) . '</th></tr>';
									echo '<tr><th class="text-right" colspan="4">' . lang("discount") .' ('. $khr_name .')'. '</th><th class="text-right">' . $this->cus->formatMoney($return_sale->surcharge * $khr_rate) . '</th></tr>';
								}
							}
							if ($pos_settings->rounding || $inv->rounding > 0) {
								?>
								<tr>
									<th class="text-right" colspan="4"><?=lang("rounding");?> </th>
									<th class="text-right"><?= $this->cus->formatMoney($inv->rounding);?></th>
								</tr>
								<tr>
									<th class="text-right" colspan="4"><?=lang("rounding");?> (<?=$khr_name;?>)</th>
									<th class="text-right"><?= $this->cus->formatMoneyKH($inv->rounding * $khr_rate);?></th>
								</tr>
								<tr>
									<th class="text-right" colspan="4"><?=lang("grand_total");?></th>
									<th class="text-right"><?=$this->cus->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding));?></th>
								</tr>
								<tr>
									<th class="text-right" colspan="4"><?=lang("grand_total");?> (<?=$khr_name;?>)</th>
									<th class="text-right"><?=$this->cus->formatMoneyKH(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) * $khr_rate);?></th>
								</tr>
								<?php 
							} else {
								?>
								<tr>
									<th class="text-right" colspan="4"><?=lang("grand_total");?></th>
									<th class="text-right"><?=$this->cus->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);?></th>
								</tr>
								<tr>
									<th class="text-right" colspan="4"><?=lang("grand_total");?> (<?=$khr_name;?>)</th>
									<th class="text-right"><?=$this->cus->formatMoneyKH(($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) * $khr_rate);?></th>
								</tr>
								<?php 
							} 
							if ($inv->paid < $inv->grand_total) {
								?>
								<tr>
									<th class="text-right" colspan="4"><?=lang("paid_amount");?></th>
									<th class="text-right"><?=$this->cus->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);?></th>
								</tr>
								<tr>
									<th class="text-right" colspan="4"><?=lang("paid_amount");?> (<?=$khr_name;?>)</th>
									<th class="text-right"><?=$this->cus->formatMoneyKH(($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid) * $khr_rate);?></th>
								</tr>
								<tr>
									<th class="text-right" colspan="4"><?=lang("due_amount");?></th>
									<th class="text-right"><?=$this->cus->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));?></th>
								</tr>
								 <tr>
									<th class="text-right" colspan="4"><?=lang("due_amount");?> (<?=$khr_name;?>)</th>
									<th class="text-right"><?=$this->cus->formatMoneyKH((($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid)) * $khr_rate);?></th>
								</tr>
								<?php 
							} 
							if ($payments) {                   
								foreach ($payments as $payment) {                        
									$currencies = json_decode($payment->currencies);
									$khr_rate = $currencies[1]->rate;
									if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
										echo '<tr>';								
											echo '<th class="text-right" colspan="4">' . lang("received_amount") . '</th>';
											echo '<th class="text-right">' . $this->cus->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</th>';									
										echo '</tr>';
										echo '<tr>';								
											echo '<th class="text-right" colspan="4">' . lang("received_amount") .' ('. $khr_name .')'. '</th>';
											echo '<th class="text-right">' . $this->cus->formatMoneyKH(($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) * $khr_rate) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</th>';									
										echo '</tr>';
										echo '<tr>';								
											echo '<th class="text-right" colspan="4">' . lang("change") . '</th>';									
											echo '<th class="text-right">' . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance) : 0) . '</th>';
										echo '</tr>';
										echo '<tr>';								
											echo '<th class="text-right" colspan="4">' . lang("change") .' ('. $khr_name .')'. '</th>';									
											echo '<th class="text-right">' . ($payment->pos_balance > 0 ? $this->cus->formatMoneyKH($payment->pos_balance * $khr_rate) : 0) . '</th>';
										echo '</tr>';
									}
								}
							}
							echo "<tr><th colspan=5 style='text-align:center;font-size:16px;'><br/><br/><br/> ~ www.sunfixconsulting.com ~ <br/> 017 907 700 / 010 929 575</th></tr>";
							?>
							
						</tfoot>
					</table>
					
				</div>
				
				<div style="clear:both;"></div>
				
			</div>
		</div>
	</body>
</html>