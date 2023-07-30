<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title"
                id="myModalLabel"><?= lang('sales') . ' (' . $this->cus->hrld($this->session->userdata('register_open_time')) . ' - ' . $this->cus->hrld(date('Y-m-d H:i:s')) . ')'; ?></h4>
        </div>
        <div class="modal-body">
			<?php 
				$sale_item = "";
				if($totalsale_items){
					$i = 1;
					foreach($totalsale_items as $totalsale_item){
						$sale_item .= "<tr>
											<td class='text-center'>".$i++."</td>
											<td class='text-left'>".$totalsale_item->product_name."</td>
											<td class='text-right'>".$this->cus->formatQuantity($totalsale_item->quantity)."</td>
										</tr>";
					}
				}else{
					$sale_item = lang("sEmptyTable");
				}
			?>
		
			<table class="table items table-striped table-bordered table-condensed table-hover sortable_table">
				<thead>
					<tr>
						<th><?= lang("#") ?></th>
						<th><?= lang("product_name") ?></th>
						<th><?= lang("quantity") ?></th>
					</tr>
				</thead>
				<tbody>
					<?= $sale_item ?>
				</tbody>
			</table>
            <table width="100%" class="stable">
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><?= lang('total_sales'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;"><h4>
					<?php
						$totalsales->total = $totalsales->total + abs($refund_item->total);
					?>
                        <span><?= $this->cus->formatMoney($totalsales->total ? $totalsales->total : '0.00') ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid #DDD;"><h4><?= lang('refunds'); ?>:</h4></td>
                    <td style="text-align:right;border-top: 1px solid #DDD;"><h4>
					<?php
						$refunds->total = $refunds->total + ($refund_item->total);
					?>
                        <span><?= $this->cus->formatMoney($refunds->total ? abs($refunds->total) : '0.00') ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expenses'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->cus->formatMoney($expense); ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('total_cash'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                            <span><strong><?= $totalsales->paid ? $this->cus->formatMoney(($totalsales->paid + ($this->session->userdata('cash_in_hand'))) + ($refunds->returned ? $refunds->returned : 0) - $expense) : $this->cus->formatMoney($this->session->userdata('cash_in_hand')-$expense); ?></strong></span>
                        </h4></td>
                </tr>
            </table>
        </div>
    </div>

</div>



