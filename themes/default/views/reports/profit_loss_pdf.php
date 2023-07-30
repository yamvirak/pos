<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .bold {
        font-weight: bold;
    }
</style>
<div class="col-xs-12">
    <h2 class="blue"><i class="fa-fw fa fa-bars"></i><?= lang('profit_loss'); ?> (
        <small><?= ($start ? $this->cus->hrld($start) : '') . ' - ' . ($end ? $this->cus->hrld($end) : ''); ?></small>
        )
    </h2>

    <div class="row">

        <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #fa603d;">
                <h4 class="bold text-muted"><?= lang('purchases') ?></h4>
                <i class="fa fa-star"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_purchases->total_amount) ?></h3>

                <p class="text-center"><?= $this->cus->formatMoney($total_purchases->total) . ' ' . lang('purchases') ?>
                    & <?= $this->cus->formatMoney($total_purchases->paid) . ' ' . lang('paid') ?>
                    & <?= $this->cus->formatMoney($total_purchases->tax) . ' ' . lang('tax') ?></p>
				<p class="text-center">&nbsp;</p>	
            </div>
        </div>
        <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #78cd51;">
                <h4 class="bold text-muted"><?= lang('sales') ?></h4>
                <i class="fa fa-heart"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_sales->total_amount) ?></h3>

                <p class="text-center"><?= $this->cus->formatMoney($total_sales->total) . ' ' . lang('sales') ?>
                    & <?= $this->cus->formatMoney($total_sales->paid) . ' ' . lang('paid') ?>
                    & <?= $this->cus->formatMoney($total_sales->tax) . ' ' . lang('tax') ?> </p>
				<p class="text-center">&nbsp;</p>	
            </div>
        </div>
		<div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: purple;">
                <h4 class="bold text-muted"><?= lang('gross_margin') ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_gross_margin->grand_total - $total_gross_margin->total_cost) ?></h3>
				<p class="text-center"><?= $total_sales->total . ' ' . lang('sales') ?> </p>
				<p class="text-center"><?= $this->cus->formatMoney($total_gross_margin->grand_total) . ' ' . lang('price') ?>
				& <?= $this->cus->formatMoney($total_gross_margin->total_cost) . ' ' . lang('cost') ?> </p>
            </div>
        </div>

    </div>
    <div class="row">
		<div class="col-xs-2" style="padding-left:0; padding-right:28px; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #fa603d;">
                <h4 class="bold text-muted"><?= lang('payments_sent') ?><br><br></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_paid->total_amount) ?></h3>

                <p class="text-center"><?= $total_paid->total . ' ' . lang('sent') ?></p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>
		
		<div class="col-xs-2" style="padding-left:0; padding-right:28px; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: black;">
                <h4 class="bold text-muted"><?= lang('payments_returned') ?></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_purchase_returned->total_amount) ?></h3>

                <p class="text-center"><?= $total_purchase_returned->total . ' ' . lang('returned') ?></p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>
		
        <div class="col-xs-2" style="padding-left:0; padding-right:28px; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #78cd51;">
                <h4 class="bold text-muted"><?= lang('payments_received') ?></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_received->total_amount) ?></h3>

                <p class="bold text-center"><?= $total_received->total . ' ' . lang('received') ?> </p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>
        <div class="col-xs-2" style="padding-left:0; padding-right:28px; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #b2b8bd;">
                <h4 class="bold text-muted"><?= lang('payments_returned') ?></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_returned->total_amount) ?></h3>

                <p class="text-center"><?= $total_returned->total . ' ' . lang('returned') ?></p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>
       
        <div class="col-xs-2" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: brown;">
                <h4 class="bold text-muted"><?= lang('expenses') ?><br><br></h4>
                <i class="fa fa-bank"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_expenses->total_amount) ?></h3>

                <p class="bold text-center"><?= $total_expenses->total . ' ' . lang('expenses') ?></p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>
		<div class="col-xs-2" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: brown;">
                <h4 class="bold text-muted"><?= lang('payment_expenses') ?><br><br></h4>
                <i class="fa fa-usd"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_expenses_amount->total_amount) ?></h3>

                <p class="bold text-center"><?= $total_expenses_amount->total . ' ' . lang('payment_expenses') ?></p>

                <p class="text-center">&nbsp;</p>
            </div>
        </div>

    </div>
    <div class="row">

        <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #ff5454;">
                <h4 class="bold text-muted"><?= lang('profit_loss') ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_sales->total_amount - $total_purchases->total_amount) ?></h3>

                <p class="text-center"><?= $this->cus->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                    - <?= $this->cus->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?><br>&nbsp;
                </p>
            </div>
        </div>
        <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #e84c8a;">
                <h4 class="bold text-muted"><?= lang('profit_loss') ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney($total_sales->total_amount - $total_purchases->total_amount - $total_sales->tax) ?></h3>

                <p class="text-center"><?= $this->cus->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                    - <?= $this->cus->formatMoney($total_sales->tax) . ' ' . lang('tax') ?>
                    - <?= $this->cus->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?><br>&nbsp;
                </p>
            </div>
        </div>
        <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #428bca;">
                <h4 class="bold text-muted"><?= lang('profit_loss') ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney(($total_sales->total_amount - $total_sales->tax) - ($total_purchases->total_amount - $total_purchases->tax)) ?></h3>

                <p class="text-center">
                    ( <?= $this->cus->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                    - <?= $this->cus->formatMoney($total_sales->tax) . ' ' . lang('tax') ?> ) -
                    ( <?= $this->cus->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?>
                    - <?= $this->cus->formatMoney($total_purchases->tax) . ' ' . lang('tax') ?> )</p>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-xs-12" style="padding-left:0; padding-right:0; padding-bottom:15px;">
            <div style="padding: 5px 10px; color: #FFF; background: #16a085;">
                <h4 class="bold text-muted"><?= lang('payments') ?></h4>
                <i class="fa fa-pie-chart"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney(($total_received->total_amount+$total_purchase_returned->total_amount) - $total_returned->total_amount - $total_paid->total_amount - $total_expenses_amount->total_amount) ?></h3>

                <p class="bold text-center"><?= $this->cus->formatMoney($total_received->total_amount) . ' ' . lang('received') ?>
                    + <?= $this->cus->formatMoney($total_purchase_returned->total_amount) . ' ' . lang('returned') ?>
					- <?= $this->cus->formatMoney($total_returned->total_amount) . ' ' . lang('returned') ?>
                    - <?= $this->cus->formatMoney($total_paid->total_amount) . ' ' . lang('sent') ?>
                    - <?= $this->cus->formatMoney($total_expenses_amount->total_amount) . ' ' . lang('expenses') ?></p>
            </div>
        </div>

    </div>

    <?php foreach ($warehouses_report as $warehouse_report) { ?>
    <div class="col-xs-4" style="padding-left:0; padding-right:0; padding-bottom:15px; margin-bottom:150px;">
        <div style="padding: 5px 10px; color: #FFF; background: #428bca;">
            <div class="small-box padding1010 bblue">
            <h4 class="bold" style="color:#FFF;"><?= $warehouse_report['warehouse']->name.' ('.$warehouse_report['warehouse']->code.')'; ?></h4>
                <i class="fa fa-money"></i>

                <h3 class="bold text-center"><?= $this->cus->formatMoney(($warehouse_report['total_sales']->total_amount) - ($warehouse_report['total_purchases']->total_amount)) ?></h3>

                <p class="bold text-center">
                    <?= lang('sales').' - '.lang('purchases'); ?>
                </p>
                <hr style="border-color: rgba(255, 255, 255, 0.4);">
                <p class="bold text-center">
                    <?= $this->cus->formatMoney($warehouse_report['total_sales']->total_amount) . ' ' . lang('sales'); ?>
                    - <?= $this->cus->formatMoney($warehouse_report['total_sales']->tax) . ' ' . lang('tax') ?>
                    = <?= $this->cus->formatMoney($warehouse_report['total_sales']->total_amount-$warehouse_report['total_sales']->tax).' '.lang('net_sales'); ?>
                </p>
                <p class="bold text-center">
                    <?= $this->cus->formatMoney($warehouse_report['total_purchases']->total_amount) . ' ' . lang('purchases') ?>
                    - <?= $this->cus->formatMoney($warehouse_report['total_purchases']->tax) . ' ' . lang('tax') ?>
                    = <?= $this->cus->formatMoney($warehouse_report['total_purchases']->total_amount-$warehouse_report['total_purchases']->tax).' '.lang('net_purchases'); ?>
                </p>
                <hr style="border-color: rgba(255, 255, 255, 0.4);">

                <h3 class="bold text-center">
                    <?= $this->cus->formatMoney((($warehouse_report['total_sales']->total_amount-$warehouse_report['total_sales']->tax))-($warehouse_report['total_purchases']->total_amount-$warehouse_report['total_purchases']->tax)); ?>
                </h3>
                <p class="bold text-center">
                    <?= lang('net_sales').' - '.lang('net_purchases'); ?>
                </p>
                <hr style="border-color: rgba(255, 255, 255, 0.4);">

                <h3 class="bold text-center"><?= $this->cus->formatMoney($warehouse_report['total_expenses']->total_amount); ?></h3>
                <p class="bold text-center">
                    <?= $warehouse_report['total_expenses']->total.' '.lang('expenses'); ?>
                </p>
            </div>
        </div>
    </div>
    <?php } ?>

</div>