<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel">
				<?= lang('sales') . ' (' . $this->cus->hrld($register_open_time) . ' - ' . ($register_close_time?$this->cus->hrld($register_close_time):$this->cus->hrld(date('Y-m-d H:i:s'))) . ')'; ?>
			</h4>
        </div>
        <div class="modal-body">
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_in_hand'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;">
						<h4><span><?= $this->cus->formatMoney($cash_in_hand); ?></span></h4>
                    </td>
                </tr>

				<tr>
                    <td width="300px;" style="font-weight:bold; "><h4><?= lang('discount'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;">
						<h4>
                            <span><?= $this->cus->formatMoney(isset($totaldiscount) ? $totaldiscount->total_discount : '0.00'); ?></span>
						</h4>
					</td>
                </tr>
				
				<tr>
                    <td width="300px;" style="font-weight:bold;"><h4><?= lang('sale'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;">
						<h4>
						<?php
							$totalsales->paid = $totalsales->paid + (isset($refund_item) ? abs($refund_item->total) : 0);
							$totalsales->total = $totalsales->total + (isset($refund_item) ? abs($refund_item->total) : 0);
						?>
                            <span><?= $this->cus->formatMoney($totalsales->total ? $totalsales->total : 0) ?></span>
						</h4>
					</td>
                </tr>
                
				<tr>
                    <td><h4><?= lang('return'); ?>:</h4></td>
                    <td style="text-align:right"><h4>
						<?php
							$refunds->returned = $refunds->returned + (isset($refund_item) ? $refund_item->total : 0);
							$refunds->total = $refunds->total + (isset($refund_item) ? $refund_item->total : 0);
						?>
                            <span><?= $this->cus->formatMoney($refunds->total ? abs($refunds->total) : 0)  ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expense'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                        <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->cus->formatMoney($expense); ?></span>
                    </h4></td>
                </tr>
                <?php
					$payment_td = "";
					if($payments){
						foreach($payments as $payment){
							$payment_td .= '<tr><td><h4>'.lang("paid_by").' '.$payment->paid_by.':</h4></td>';
							$payment_td .= '<td style="text-align:right;"><h4>'.$this->cus->formatMoney($payment->paid).'</h4></td></tr>';
						}
					}
					echo $payment_td;
				?>
				<tr>
                    <td style="font-weight:bold; border-top: 1px solid #DDD;"><h4><strong><?= lang('total_cash'); ?></strong>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4><?= $this->cus->formatMoney($total_cash) ?></h4></td>
                </tr>
				<tr>
                    <td style="font-weight:bold;"><h4><strong><?= lang('total_cash_submitted'); ?></strong>:</h4></td>
                    <td style="text-align:right;"><h4><?= $this->cus->formatMoney($total_cash_submitted) ?></h4></td>
                </tr>
				<tr>
                    <td style="font-weight:bold; border-bottom: 1px solid #DDD;"><h4><strong><?= lang('total_cash_balance'); ?></strong>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4><?= $this->cus->formatMoney($total_cash - $total_cash_submitted) ?></h4></td>
                </tr>
				
				<?php if($count_money){ ?>
					<tr>
						<td style="font-weight:bold;border-bottom: 1px solid #DDD;"><h4><strong><?= lang('total_count_money_kh'); ?></strong>:</h4>
						</td>
						<td style="text-align:right; border-bottom: 1px solid #DDD;"><h4>
							<span><strong><?= $this->cus->formatMoney(($count_money ? $count_money->total_amount_kh : 0)); ?></strong></span>
						</h4></td>
					</tr>
					<tr>
						<td style="font-weight:bold; border-bottom: 1px solid #DDD;"><h4><strong><?= lang('total_count_money_us'); ?></strong>:</h4>
						</td>
						<td style="text-align:right; border-bottom: 1px solid #DDD;"><h4>
							<span><strong><?= $this->cus->formatMoney(($count_money ? $count_money->total_amount_us : 0)); ?></strong></span>
						</h4></td>
					</tr>
					<tr>
						<td style="font-weight:bold; border-bottom: 1px solid #DDD;"><h4><strong><?= lang('total_count_money'); ?></strong>:</h4>
						</td>
						<td style="text-align:right; border-bottom: 1px solid #DDD;"><h4>
							<span><strong><?= $this->cus->formatMoney(($count_money ? $count_money->total_amount : 0)); ?></strong></span>
						</h4></td>
					</tr>
				<?php } ?>

            </table>
        </div>
    </div>

</div>



