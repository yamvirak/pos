<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($modal) { ?>
<div class="modal-dialog no-modal-header" role="document"><div class="modal-content"><div class="modal-body">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
    <?php 
} else {
    ?><!doctype html>
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
    <body>
        <?php 
    } ?>
	
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
		<style>
			@media print{
				#wrapper{
					<?= $hide_print ?>
				}
				.bg-text{
					display:block !important;
				}
			}
			.bg-text{
				opacity: 0.1;
				color:lightblack;
				font-size:50px;
				position:absolute;
				transform:rotate(300deg);
				-webkit-transform:rotate(300deg);
				display:none;
			}
		</style>
        <div id="receiptData">
            <div class="no-print">
                <?php 
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
                <div class="text-center">
                    <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                    <h3 style="text-transform:uppercase;"><?= $biller->name ?></h3>
                    <?php
                    echo "<p>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country .
                    "<br>" . lang("tel") . ": " . $biller->phone;


                    // end of the customer fields

                    echo "<br>";
                    if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                        echo $pos_settings->cf_title1 . ": " . $pos_settings->cf_value1 . "<br>";
                    }
                    if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                        echo $pos_settings->cf_title2 . ": " . $pos_settings->cf_value2 . "<br>";
                    }
                    echo '</p>';
                    ?>
                </div>
                <?php
                if ($Settings->invoice_view == 1) {
                    ?>
                    <div class="col-sm-12 text-center">
                        <h4 style="font-weight:bold;"><?=lang('tax_invoice');?></h4>
                    </div>
                    <?php 
                }
				echo "<p>";
				if ($pos->queue_enable == 1) {
					echo '<b>'.lang("queue") . ": #" .$pos->queue_number . "</b> <br/>";
                }
                echo lang("date") . ": " . $this->cus->hrld($inv->date,1) . ($inv->vehicle_model ? "<span style='float:right'>".lang('vehicle_model')." : ".$inv->vehicle_model."</span>" : ""). "<br>";
                echo lang("sale_no_ref") . ": " . $inv->reference_no . ($inv->vehicle_plate ? "<span style='float:right'>".lang('vehicle_plate')." : ".$inv->vehicle_plate."</span>" : ""). "<br>";
                if (!empty($inv->return_sale_ref)) {
                    echo '<p>'.lang("return_ref").': '.$inv->return_sale_ref;
                    if ($inv->return_id) {
                        echo ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                    } else {
                        echo '</p>';
                    }
                }
                echo lang("user") . ": " . $created_by->first_name." ".$created_by->last_name . ($inv->vehicle_vin_no ? "<span style='float:right'>".lang('vehicle_vin')." : ".$inv->vehicle_vin_no."</span>" : "")."<br/>";                
                echo lang("customer") . ": " . ($customer->company && $customer->company != '-' ? $customer->company : $customer->name) .($inv->mechanic ? "<span style='float:right'>".lang('mechanic')." : ".$inv->mechanic."</span>" : ""). "<br>";
                if(isset($table) && $table != false && $table->name !=''){

					echo lang("table") . ": " . $table->name;     
				}
				if ($pos_settings->customer_details) {
                    if ($customer->vat_no != "-" && $customer->vat_no != "") {
                        echo "<br>" . lang("vat_no") . ": " . $customer->vat_no;
                    }
                    echo lang("tel") . ": " . $customer->phone . "<br>";
                    echo lang("address") . ": " . $customer->address . "<br>";
                    echo $customer->city ." ".$customer->state." ".$customer->country ."<br>";
                    
					if (!empty($customer->cf1) && $customer->cf1 != "-") {
                        echo "<br>" . lang("ccf1") . ": " . $customer->cf1;
                    }
                    if (!empty($customer->cf2) && $customer->cf2 != "-") {
                        echo "<br>" . lang("ccf2") . ": " . $customer->cf2;
                    }
                    if (!empty($customer->cf3) && $customer->cf3 != "-") {
                        echo "<br>" . lang("ccf3") . ": " . $customer->cf3;
                    }
                    if (!empty($customer->cf4) && $customer->cf4 != "-") {
                        echo "<br>" . lang("ccf4") . ": " . $customer->cf4;
                    }
                    if (!empty($customer->cf5) && $customer->cf5 != "-") {
                        echo "<br>" . lang("ccf5") . ": " . $customer->cf5;
                    }
                    if (!empty($customer->cf6) && $customer->cf6 != "-") {
                        echo "<br>" . lang("ccf6") . ": " . $customer->cf6;
                    }
                }
                echo "</p>";
                ?>
                <div style="clear:both;"></div>
                <table class="table table-striped table-condensed">
                    <thead>
						<tr>
							<th class="text-center"><?=lang("#");?></th>
							<th class="text-center"><?=lang("description");?></th>
							<th class="text-center"><?=lang("qty");?></th>
							<th class="text-center"><?=lang("unit_price");?><?= ($inv->total_discount > 0 ? ' ('.lang('dis').') ' : '') ?></th>
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
                                echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
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
							if($this->config->item('product_currency')==true && $row->currency_rate > 1){
								echo '<tr>
									<td class="no-border">' . $r . '</td>
									<td class="no-border">' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') .($row->comment!=''?'<br>'.$row->comment:'').($row->bom_type!=''?'<br>'.$row->bom_type:''). '</td>
									<td style="text-align:right" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity) .' '.$row->name. '</td>
									<td style="text-align:right" class="no-border">' . $this->cus->formatOtherMoney($row->unit_price * $row->currency_rate, $row->currency_code, -1) . ' '.($row->discount != 0 ? '<small>(' . $row->discount * $row->currency_rate . ')</small> ' : '') .'</td>
									<td style="text-align:right" class="no-border">' . $this->cus->formatOtherMoney($row->subtotal * $row->currency_rate, $row->currency_code, -1) . '</td>
								</tr>';
							}else{
								echo '<tr>
										<td class="no-border">' . $r . '</td>
										<td class="no-border">' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') .($row->comment!=''?'<br>'.$row->comment:'').($row->bom_type!=''?'<br>'.$row->bom_type:''). '</td>
										<td style="text-align:right" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity) .' '.$row->name. '</td>
										<td style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->unit_price) . ' '.($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '').'</td>
										<td style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->subtotal) . '</td>
									</tr>';
							}
							$r++;
                        }
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($return_rows as $row) {
                                if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                                    $category = $row->category_id;
                                    echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
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
										<td class="no-border">' . $r . '</td>
										<td class="no-border">' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') . '</td>
										<td style="text-align:right" class="no-border">' . $this->cus->formatQuantity($row->unit_quantity) . '</td>
										<td style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->net_unit_price + ($row->item_tax / $row->unit_quantity)) . '</td>
										<td style="text-align:right" class="no-border">' . $this->cus->formatMoney($row->subtotal) . '</td>
									</tr>';
                                $r++;
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right" colspan="4"><?=lang("total");?></th>
                            <th class="text-right"><?=$this->cus->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax));?></th>
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
                        if ($return_sale) {
                            if ($return_sale->surcharge != 0) {
                                echo '<tr><th class="text-right" colspan="4">' . lang("order_discount") . '</th><th class="text-right">' . $this->cus->formatMoney($return_sale->surcharge) . '</th></tr>';
                            }
                        }
                        if ($pos_settings->rounding || $inv->rounding > 0) {
                            ?>
                            <tr>
                                <th class="text-right" colspan="4"><?=lang("rounding");?></th>
                                <th class="text-right"><?= $this->cus->formatMoney($inv->rounding);?></th>
                            </tr>
							<?php 
								if(json_decode($inv->currencies)){
									foreach(json_decode($inv->currencies) as $key=>$currency){ 
										$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
										$grand_total = (($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) / $base_currency->rate) * $currency->rate;
									?>
										<tr>
											<th class="text-right" colspan="4">
												<?php 
													if($key==0){
														echo lang("grand_total");
													}
												?>
											</th>
											<th class="text-right"><?=$this->cus->formatOtherMoney($grand_total, $currency->currency);?></th>
										</tr>
								<?php
									}
								}
                        } else {
							if(json_decode($inv->currencies)){
								foreach(json_decode($inv->currencies) as $key2=> $currency){ 
									$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
									$grand_total = (($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) / $base_currency->rate) * $currency->rate;
								?>
									<tr>
										<th class="text-right" colspan="4">
											<?php 
													if($key2==0){
														echo lang("grand_total");
													}
												?>
										</th>
										<th class="text-right"><?=$this->cus->formatOtherMoney($grand_total, $currency->currency);?></th>
									</tr>
							<?php
								}
							}
                        } 
                        if ($inv->paid < $inv->grand_total) {
                            ?>
                            <tr>
                                <th class="text-right" colspan="4"><?=lang("paid_amount");?></th>
                                <th class="text-right"><?=$this->cus->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);?></th>
                            </tr>
                            <tr>
                                <th class="text-right" colspan="4"><?=lang("due_amount");?></th>
                                <th class="text-right"><?=$this->cus->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));?></th>
                            </tr>
                            <?php 
                        } ?>
                    </tfoot>
                </table>
                <?php
                if ($payments) {
                    echo '<table class="table table-striped table-condensed" style="text-align:center;"><tbody>';
                    foreach ($payments as $payment) {
                        echo '<tr>';
                        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }

                if ($return_payments) {
                    echo '<strong>'.lang('return_payments').'</strong><table class="table table-striped table-condensed"><tbody>';
                    foreach ($return_payments as $payment) {
                        $payment->amount = (0-$payment->amount);
                        echo '<tr>';
                        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->cus->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }

                if ($Settings->invoice_view == 1) {
                    if (!empty($tax_summary)) {
                        echo '<h4 style="font-weight:bold;">' . lang('tax_summary') . '</h4>';
                        echo '<table class="table table-condensed"><thead><tr><th>' . lang('name') . '</th><th>' . lang('code') . '</th><th>' . lang('qty') . '</th><th>' . lang('tax_excl') . '</th><th>' . lang('tax_amt') . '</th></tr></td><tbody>';
                        foreach ($tax_summary as $summary) {
                            echo '<tr><td>' . $summary['name'] . '</td><td class="text-center">' . $summary['code'] . '</td><td class="text-center">' . $this->cus->formatQuantity($summary['items']) . '</td><td class="text-right">' . $this->cus->formatMoney($summary['amt']) . '</td><td class="text-right">' . $this->cus->formatMoney($summary['tax']) . '</td></tr>';
                        }
                        echo '</tbody></tfoot>';
                        echo '<tr><th colspan="4" class="text-right">' . lang('total_tax_amount') . '</th><th class="text-right">' . $this->cus->formatMoney($return_sale ? $inv->product_tax+$return_sale->product_tax : $inv->product_tax) . '</th></tr>';
                        echo '</tfoot></table>';
                    }
                }
                ?>
                <?= $customer->award_points != 0 && $Settings->each_spent > 0 ? '<p class="text-center">'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                .'<br>'.
                lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>' : ''; ?>
				<?= $inv->note ? '<p class="text-center">' . $this->cus->decode_html($inv->note) . '</p>' : ''; ?>
				<?= $inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->cus->decode_html($inv->staff_note) . '</p>' : ''; ?>
				<?= $biller->invoice_footer ? '<p class="text-center">'.$this->cus->decode_html($biller->invoice_footer).'</p>' : ''; ?>
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
            <?php 
            if ($message) { 
                ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?=is_array($message) ? print_r($message, true) : $message;?>
                </div>
                <?php 
            } ?>
            <?php 
            if ($modal) {
                ?>
                <div class="btn-group btn-group-justified" role="group" aria-label="...">
                    <div class="btn-group" role="group">
                        <?php
                        if ($pos->remote_printing == 1) {
                            echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        } else {
                            echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        }

                        ?>
                    </div>
                    <div class="btn-group" role="group">
                        <a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                    </div>
                </div>
                <?php 
            } else { 
                ?>
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
                <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a></span>
                <?php if ($pos->queue_enable == 1) { ?>
					<span class="col-xs-12">
						<a class="btn btn-block btn-danger" href="#" id="print-sticker"><?= lang("print_sticker"); ?></a>
					</span>
				<?php } ?>
				<span class="col-xs-12">
                    <a class="btn btn-block btn-warning" href="<?= site_url('pos'); ?>"><?= lang("back_to_pos"); ?></a>
                </span>
                <?php 
            }
            if ($pos->remote_printing == 1) {
                ?>
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
                <?php 
            } ?>
            <div style="clear:both;"></div>
        </div>
    </div>
    <?php
    if(!$modal) {
        ?>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
        <?php
    }
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
			window.onafterprint = function(){		
				$.ajax({
					url : "<?= site_url('sales/add_print') ?>",
					dataType : "JSON",
					type : "GET",
					data : { 
							transaction_id : <?= $inv->id ?>,
							transaction : "POS",
							reference_no : "<?= $inv->reference_no ?>"
						}
				});
			}
            $('#email').click(function () {
                bootbox.prompt({
                    title: "<?= lang("email_address"); ?>",
                    inputType: 'email',
                    value: "<?= $customer->email; ?>",
                    callback: function (email) {
                        if (email != null) {
                            $.ajax({
                                type: "post",
                                url: "<?= site_url('pos/email_receipt') ?>",
                                data: {<?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: <?= $inv->id; ?>},
                                dataType: "json",
                                success: function (data) {
                                    bootbox.alert({message: data.msg, size: 'small'});
                                },
                                error: function () {
                                    bootbox.alert({message: '<?= lang('ajax_request_failed'); ?>', size: 'small'});
                                    return false;
                                }
                            });
                        }
                    }
                });
                return false;
            });
        });

    </script>
    <?php /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */ ?>
    <?php include 'remote_printing.php'; ?>
    <?php
    if($modal) {
        ?>
    </div>
</div>
</div>
<?php 
} else {
    ?>
</body>
</html>
<?php
}
?>