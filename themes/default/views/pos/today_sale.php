<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('today_sale'); ?></h4>
        </div>
        <div class="modal-body">
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_in_hand'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->cus->formatMoney($this->session->userdata('cash_in_hand')); ?></span></h4>
                    </td>
                </tr>
				<tr>
                    <td width="300px;"><h4 style="font-weight:bold;"><?= lang('product'); ?>:</h4></td>
                    <td width="200px;" style="text-align:right;"><h4 style="font-weight:bold;">
                            <span><?= $this->cus->formatQuantity($totalsales->total_quantity ? $totalsales->total_quantity : '0.00') ?></span>
                        </h4></td>
                </tr>
				<tr>
                    <td width="300px;"><h4 style="font-weight:bold;"><?= lang('discount'); ?>:</h4></td>
                    <td width="200px;" style="text-align:right;"><h4 style="font-weight:bold;">
                            <span><?= $this->cus->formatMoney($totalsales->total_discount ? $totalsales->total_discount : '0.00') ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td width="300px;"><h4 style="font-weight:bold;"><?= lang('sale'); ?>:</h4></td>
					<?php
						$totalsales->paid = $totalsales->paid + abs($refund_item->total);
						$totalsales->total = $totalsales->total + abs($refund_item->total);
					?>
                    <td width="200px;" style="text-align:right;"><h4 style="font-weight:bold;">
                            <span><?= $this->cus->formatMoney($totalsales->total ? $totalsales->total : 0) ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid #DDD;"><h4><?= lang('return'); ?>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4>
					<?php
						$refunds->returned = $refunds->returned + ($refund_item->total);
						$refunds->total = $refunds->total + ($refund_item->total);
					?>
                            <span><?= $this->cus->formatMoney($refunds->total ? abs($refunds->total) : 0) ?></span>
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
					$total_cash_amount = ($total_cash->total_cash ? $total_cash->total_cash : 0);
					if($payments){
						foreach($payments as $payment){
							$payment_td .= '<tr><td><h4>'.lang("paid_by").' '.$payment->paid_by.':</h4></td>';
							$payment_td .= '<td style="text-align:right;"><h4>'.$this->cus->formatMoney($payment->paid).'</h4></td></tr>';
						}
					}
					echo $payment_td;
					$total_cash_amount = $total_cash_amount + $this->session->userdata('cash_in_hand') + ($refunds->returned ? $refunds->returned : 0) - ($expenses ? $expenses->total : 0);
				?>
                <tr>
                    <td width="300px;" style="font-weight:bold;border-top: 1px solid #EEE;"><h4><strong><?= lang('total_cash'); ?></strong>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #EEE;"><h4><span><strong><?= $this->cus->formatMoney($total_cash_amount) ?></strong></span></h4></td>
                </tr>
            </table>
        </div>
    </div>

</div>
