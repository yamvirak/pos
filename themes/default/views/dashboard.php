<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
function row_status($x)
{
    if ($x == null) {
        return '';
    } elseif ($x == 'pending') {
        return '<div class="text-center"><span class="label label-warning">' . lang($x) . '</span></div>';
    } elseif ($x == 'completed' || $x == 'paid' || $x == 'sent' || $x == 'received') {
        return '<div class="text-center"><span class="label label-success">' . lang($x) . '</span></div>';
    } elseif ($x == 'partial' || $x == 'transferring') {
        return '<div class="text-center"><span class="label label-info">' . lang($x) . '</span></div>';
    } elseif ($x == 'due') {
        return '<div class="text-center"><span class="label label-danger">' . lang($x) . '</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-default">' . lang($x) . '</span></div>';
    }
}
?>
<?php if (($Owner || $Admin)) { ?>
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="row">
            <div class="col-sm-12 col-md-12">
                    <ul class="breadcrumb">
                        <?php
                        foreach ($bc as $b) {
                            if ($b['link'] === '#') {
                                echo '<li class="active">' . $b['page'] . '</li>';
                            } else {
                                echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
                            }
                        }
                        ?>
                    </ul>
            </div>
            <?php if($this->config->item("sale")){ ?>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard table-responsive">
                        <div class="box-content_icon dblue">
                            <h4><span class="fa fa-line-chart" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_sales'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatMoney($todayData->amount - $todayData->total_return)?></div>
                                </div>
                           
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatMoney($thisMonthData->amount - $thisMonthData->total_return)?></div>                        
                                </div>
                            
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatMoney($lastMonthData->amount - $lastMonthData->total_return)?></div>                            
                                </div>
                          
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #098ad8">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/sales') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon dpurple">
                            <h4><span class="fa fa-shopping-cart" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_ar'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatMoney(($todayData->amount-$todayData->paid) - ($todayData->total_return - $todayData->total_return_paid))?></div>
                                </div>
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatMoney(($thisMonthData->amount-$thisMonthData->paid) - ($thisMonthData->total_return - $thisMonthData->total_return_paid))?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatMoney(($lastMonthData->amount-$lastMonthData->paid) - ($lastMonthData->total_return - $lastMonthData->total_return_paid))?></div>                           
                                </div>
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #3e39a5">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/ar_aging') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon dorange">
                            <h4><span class="fa fa-shopping-cart" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_purchases'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatMoney($todayPurchase->amount - $todayPurchase->total_return)?></div>
                                </div>
                                 <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatMoney($thisMonthPurchase->amount - $thisMonthPurchase->total_return)?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatMoney($lastMonthPurchase->amount - $lastMonthPurchase->total_return)?></div>                              
                                </div>
                               
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #D84A0D">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/purchases') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon dgreen">
                            <h4><span class="fa fa-money" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_expenses'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatMoney($todayExpenses->amount)?></div>
                                </div>
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatMoney($thisMonthExpenses->amount)?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatMoney($lastMonthExpenses->amount)?></div>                              
                                </div>
                                
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #0e7e38">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/expenses') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <?php } if($this->config->item("room_rent")){ ?>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon dlightblue">
                            <h4><span class="fa fa-sign-in" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_check_in'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatQuantityQty($todayCheckIn->TotalCheckIn)?></div>
                                </div>
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatQuantityQty($thisMonthCheckIn->TotalCheckIn)?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatQuantityQty($lastMonthCheckIn->TotalCheckIn)?></div>                              
                                </div>
                                
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #14a18f">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('rentals_configuration/rooms') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon bBigBlue">
                            <h4><span class="fa fa-sign-out" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_check_out'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatQuantityQty($todayCheckOut->TotalCheckOut)?></div>
                                </div>
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatQuantityQty($thisMonthCheckOut->TotalCheckOut)?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatQuantityQty($lastMonthCheckOut->TotalCheckOut)?></div>                              
                                </div>
                                
                            </div> 
                        </div>
                        <div class="box-footer" style="background:#008cb6">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('rentals_configuration/room_types') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon blightOrange">
                            <h4><span class="fa fa-list-alt" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_reservation'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatQuantityQty($todayReservation->TotalReservation)?></div>
                                </div>
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatQuantityQty($thisMonthReservation->TotalReservation)?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatQuantityQty($lastMonthReservation->TotalReservation)?></div>                          
                                </div>
                            </div> 
                        </div>
                        <div class="box-footer" style="background:#e49800">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('rentals_configuration/floors') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon bCuscenGreen">
                            <h4><span class="fa fa-bed" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('room_occupied'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatQuantityQty($TotalServices->TotalService)?></div>
                                </div>
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatQuantityQty($thisMonthCheckIn->TotalCheckIn)?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatQuantityQty($lastMonthCheckIn->TotalCheckIn)?></div>                          
                                </div>
                            </div> 
                        </div>
                        <div class="box-footer" style="background:#0e7e38">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('rentals_configuration/services') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

            <?php } if($this->config->item("installment")){ ?>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard bpurple">
                        <div class="box-content">
                            <h4 style="text-transform:uppercase;"><span class="fa fa-calculator" id="icon"></span><?=lang("installment")?></h4>
                            <ul>
                                <li><?=lang("today")?> <span><?=$this->cus->formatMoney($todayInstallment->amount)?></span></li>
                                <li class="hidden"><?=lang("yesterday")?> <span><?=$this->cus->formatMoney($yesterdayInstallment->amount)?></span></li>
                                <li><?=lang("this_month")?> <span><?=$this->cus->formatMoney($thisMonthInstallment->amount)?></span></li>
                            </ul>
                        </div>
                       
                        <div class="box-footer" style="background:#672d80">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/installments') ?>"><?=lang("view_detail")?> <i class="fa fa-chevron-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard bpink">
                        <div class="box-content">
                            <h4 style="text-transform:uppercase;"><span class="fa fa-usd" id="icon"></span><?=lang("installment_payment")?></h4>
                            <ul>
                                <li><?=lang("today")?> <span><?=$this->cus->formatMoney($todayInstallmentPayment->amount)?></span></li>
                                <li class="hidden"><?=lang("yesterday")?> <span><?=$this->cus->formatMoney($yesterdayInstallmentPayment->amount)?></span></li>
                                <li><?=lang("this_month")?> <span><?=$this->cus->formatMoney($thisMonthInstallmentPayment->amount)?></span></li>
                            </ul>
                        </div>
                        <div class="box-footer" style="background:#9c355e">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/installment_payments') ?>"><?=lang("view_detail")?> <i class="fa fa-chevron-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard borange">
                        <div class="box-content">
                            <h4 style="text-transform:uppercase;"><span class="fa fa-exclamation-triangle" id="icon"></span><?=lang("late_replayments")?></h4>
                            <ul>
                                <li><?=lang("today")?> <span><?=$this->cus->formatMoney($todayLateRePayment->amount)?></span></li>
                                <li class="hidden"><?=lang("yesterday")?> <span><?=$this->cus->formatMoney($yesterdayLateRePayment->amount)?></span></li>
                                <li><?=lang("this_month")?> <span><?=$this->cus->formatMoney($thisMonthLateRePayment->amount)?></span></li>
                            </ul>
                        </div>
                        <div class="box-footer" style="background:#cc3b1a">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/installment_missed_repayments') ?>"><?=lang("view_detail")?> <i class="fa fa-chevron-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard bdarkblue">
                        <div class="box-content">
                            <h4 style="text-transform:uppercase;"><span class="fa fa-usd" id="icon"></span><?=lang("expenses")?></h4>
                            <ul>
                                <li><?=lang("today")?> <span><?=$this->cus->formatMoney($todayExpenses->amount)?></span></li>
                                <li class="hidden"><?=lang("yesterday")?> <span><?=$this->cus->formatMoney($yesterdayExpenses->amount)?></span></li>
                                <li><?=lang("this_month")?> <span><?=$this->cus->formatMoney($thisMonthExpenses->amount)?></span></li>
                            </ul>
                        </div>
                        <div class="box-footer" style="background:#07899c">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/installment_payments') ?>"><?=lang("view_detail")?> <i class="fa fa-chevron-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <div class="box" style="margin-bottom: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('chart_of_montly_revenue'); ?></h2>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-md-12">

                    <div id="ov-chart" style="width:100%; height:320px;"></div>
                    <p class="text-center hidden"><?= lang("chart_lable_toggle"); ?></p>
                </div>
            </div>
        </div>
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('chart_of_montly_expense'); ?></h2>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-md-12">

                    <div id="ox-chart" style="width:100%; height:320px;"></div>
                    <p class="text-center hidden"><?= lang("chart_lable_toggle"); ?></p>
                </div>
            </div>
        </div>
    </div>
<div class="row hidden" style="margin-bottom: 15px;">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-tasks"></i> <?= lang('latest_five') ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">

                        <ul id="dbTab" class="nav nav-tabs">
                            <?php if ($Owner || $Admin || $GP['sales-index']) { ?>
                            <li class=""><a href="#sales"><?= lang('sales') ?></a></li>
                            <?php } if (($Owner || $Admin || $GP['installments-index']) && $Settings->installment==1) { ?>
                            <li class=""><a href="#installments"><?= lang('installments') ?></a></li>
                            <?php } if (($Owner || $Admin || $GP['quotes-index']) && $this->config->item('quotation')) { ?>
                            <li class=""><a href="#quotes"><?= lang('quotes') ?></a></li>
                            <?php } if ($Owner || $Admin || $GP['purchases-index']) { ?>
                            <li class=""><a href="#purchases"><?= lang('purchases') ?></a></li>
                            <?php } if (($Owner || $Admin || $GP['transfers-index']) && !$this->config->item('one_warehouse')) { ?>
                            <li class=""><a href="#transfers"><?= lang('transfers') ?></a></li>
                            <?php } if ($Owner || $Admin || $GP['customers-index']) { ?>
                            <li class=""><a href="#customers"><?= lang('customers') ?></a></li>
                            <?php } if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                            <li class=""><a href="#suppliers"><?= lang('suppliers') ?></a></li>
                            <?php } ?>
                        </ul>

                        <div class="tab-content">
                        <?php if ($Owner || $Admin || $GP['sales-index']) { ?>
                            <div id="sales" class="tab-pane fade in">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="sales-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= lang("date"); ?></th>
                                                    <th><?= lang("reference_no"); ?></th>
                                                    <th><?= lang("customer"); ?></th>
                                                    <th><?= lang("grand_total"); ?></th>
                                                    <th><?= lang("returned"); ?></th>
                                                    <th><?= lang("paid"); ?></th>
                                                    <th><?= lang("discount"); ?></th>
                                                    <th><?= lang("balance"); ?></th>
                                                    <th><?= lang("payment_status"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($sales)) {
                                                    $r = 1;
                                                    foreach ($sales as $order) {
                                                       
                                                        echo '<tr id="' . $order->id . '" class="' . (isset($order->pos) ? "receipt_link" : "invoice_link") . '"><td>' . $r . '</td>
                                                            <td>' . $this->cus->hrld($order->date) . '</td>
                                                            <td>' . $order->reference_no . '</td>
                                                            <td>' . $order->customer . '</td>
                                                            <td class="text-right">' . $this->cus->formatMoney($order->grand_total) . '</td>
                                                            <td class="text-right">' . $this->cus->formatMoney($order->total_return) . '</td>
                                                            <td class="text-right">' . $this->cus->formatMoney($order->paid) . '</td>
                                                            <td class="text-right">' . $this->cus->formatMoney($order->discount) . '</td>
                                                            <td class="text-right">' . $this->cus->formatMoney($order->balance) . '</td>
                                                            <td>' . row_status($order->payment_status) . '</td>
                                                        </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="10"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php } if (($Owner || $Admin || $GP['installments-index']) && $Settings->installment==1) { ?>
                            
                            <div id="installments" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="installment-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th width="150px"><?= $this->lang->line("date"); ?></th>
                                                    <th width="150px"><?= $this->lang->line("reference_no"); ?></th>
                                                    <th width="150px"><?= $this->lang->line("customer"); ?></th>
                                                    <th><?= $this->lang->line("description"); ?></th>
                                                    <th width="120px"><?= $this->lang->line("term"); ?></th>
                                                    <th width="100px"><?= $this->lang->line("price"); ?></th>
                                                    <th width="100px"><?= $this->lang->line("rate"); ?></th>
                                                    <th width="100px"><?= $this->lang->line("status"); ?></th>
                                                    <th width="100px"><?= $this->lang->line("amount"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($installments)) {
                                                    $r = 1;
                                                    foreach ($installments as $installment) {
                                                        echo '<tr id="' . $installment->id . '" class="installment_link"><td>' . $r . '</td>
                                                        <td>' . $this->cus->hrld($installment->created_date) . '</td>
                                                        <td>' . $installment->reference_no . '</td>
                                                        <td>' . $installment->customer . '</td>
                                                        <td>' . $installment->description . '</td>
                                                        <td class="center">' . ($installment->term / $installment->frequency) . ' -Month</td>
                                                        <td class="right">' . $this->cus->formatMoney($installment->price) . '</td>
                                                        <td class="center">' . $installment->interest_rate . ' % </td>
                                                        <td>' . row_status($installment->status) . '</td>
                                                        <td class="text-right">' . $this->cus->formatMoney($installment->installment_amount) . '</td>
                                                    </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="10"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php } if ($Owner || $Admin || $GP['quotes-index']) { ?>

                            <div id="quotes" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="quotes-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("date"); ?></th>
                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                    <th><?= $this->lang->line("customer"); ?></th>
                                                    <th><?= $this->lang->line("status"); ?></th>
                                                    <th><?= $this->lang->line("amount"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($quotes)) {
                                                    $r = 1;
                                                    foreach ($quotes as $quote) {
                                                        echo '<tr id="' . $quote->id . '" class="quote_link"><td>' . $r . '</td>
                                                        <td>' . $this->cus->hrld($quote->date) . '</td>
                                                        <td>' . $quote->reference_no . '</td>
                                                        <td>' . $quote->customer . '</td>
                                                        <td>' . row_status($quote->status) . '</td>
                                                        <td class="text-right">' . $this->cus->formatMoney($quote->grand_total) . '</td>
                                                    </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="6"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } if ($Owner || $Admin || $GP['purchases-index']) { ?>

                            <div id="purchases" class="tab-pane fade in">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="purchases-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= lang("date"); ?></th>
                                                    <th><?= lang("ref_no"); ?></th>
                                                    <th><?= lang("supplier"); ?></th>
                                                    <th><?= lang("grand_total"); ?></th>
                                                    <th><?= lang("returned"); ?></th>
                                                    <th><?= lang("paid"); ?></th>
                                                    <th><?= lang("balance"); ?></th>
                                                    <th><?= lang("purchase_status"); ?></th>
                                                    <th><?= lang("payment_status"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($purchases)) {
                                                    $r = 1;
                                                    foreach ($purchases as $purchase) {
                                                        echo '<tr id="' . $purchase->id . '" class="purchase_link"><td>' . $r . '</td>
                                                    <td>' . $this->cus->hrld($purchase->date) . '</td>
                                                    <td>' . $purchase->reference_no . '</td>
                                                    <td>' . $purchase->supplier . '</td>
                                                    <td class="text-right">' . $this->cus->formatMoney($purchase->grand_total) . '</td>
                                                    <td class="text-right">' . $this->cus->formatMoney($purchase->return_purchase_total) . '</td>
                                                    <td class="text-right">' . $this->cus->formatMoney($purchase->paid) . '</td>
                                                    <td class="text-right">' . $this->cus->formatMoney($purchase->balance) . '</td>
                                                    <td>' . row_status($purchase->status) . '</td>
                                                    <td>' . row_status($purchase->payment_status) . '</td>
                                                </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="10"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } if ($Owner || $Admin || $GP['transfers-index']) { ?>

                            <div id="transfers" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="transfers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("date"); ?></th>
                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                    <th><?= $this->lang->line("from"); ?></th>
                                                    <th><?= $this->lang->line("to"); ?></th>
                                                    <th><?= $this->lang->line("status"); ?></th>
                                                    <th><?= $this->lang->line("amount"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($transfers)) {
                                                    $r = 1;
                                                    foreach ($transfers as $transfer) {
                                                        echo '<tr id="' . $transfer->id . '" class="transfer_link"><td>' . $r . '</td>
                                                <td>' . $this->cus->hrld($transfer->date) . '</td>
                                                <td>' . $transfer->transfer_no . '</td>
                                                <td>' . $transfer->from_warehouse_name . '</td>
                                                <td>' . $transfer->to_warehouse_name . '</td>
                                                <td>' . row_status($transfer->status) . '</td>
                                                <td class="text-right">' . $this->cus->formatMoney($transfer->grand_total) . '</td>
                                            </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="7"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } if ($Owner || $Admin || $GP['customers-index']) { ?>

                            <div id="customers" class="tab-pane fade in">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="customers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("company"); ?></th>
                                                    <th><?= $this->lang->line("name"); ?></th>
                                                    <th><?= $this->lang->line("email"); ?></th>
                                                    <th><?= $this->lang->line("phone"); ?></th>
                                                    <th><?= $this->lang->line("address"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($customers)) {
                                                    $r = 1;
                                                    foreach ($customers as $customer) {
                                                        echo '<tr id="' . $customer->id . '" class="customer_link pointer"><td>' . $r . '</td>
                                            <td>' . $customer->company . '</td>
                                            <td>' . $customer->name . '</td>
                                            <td>' . $customer->email . '</td>
                                            <td>' . $customer->phone . '</td>
                                            <td>' . $customer->address . '</td>
                                        </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="6"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } if ($Owner || $Admin || $GP['suppliers-index']) { ?>

                            <div id="suppliers" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="suppliers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("company"); ?></th>
                                                    <th><?= $this->lang->line("name"); ?></th>
                                                    <th><?= $this->lang->line("email"); ?></th>
                                                    <th><?= $this->lang->line("phone"); ?></th>
                                                    <th><?= $this->lang->line("address"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($suppliers)) {
                                                    $r = 1;
                                                    foreach ($suppliers as $supplier) {
                                                        echo '<tr id="' . $supplier->id . '" class="supplier_link pointer"><td>' . $r . '</td>
                                        <td>' . $supplier->company . '</td>
                                        <td>' . $supplier->name . '</td>
                                        <td>' . $supplier->email . '</td>
                                        <td>' . $supplier->phone . '</td>
                                        <td>' . $supplier->address . '</td>
                                    </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="6"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } ?>

                        </div>


                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

<?php } else { ?>


<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="row">
            <?php if($this->config->item("sale")){ ?>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard table-responsive">
                        <div class="box-content_icon dblue">
                            <h4><span class="fa fa-line-chart" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_sales'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatMoney($todayData->amount - $todayData->total_return)?></div>
                                </div>
                           
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatMoney($thisMonthData->amount - $thisMonthData->total_return)?></div>                        
                                </div>
                            
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatMoney($lastMonthData->amount - $lastMonthData->total_return)?></div>                            
                                </div>
                          
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #098ad8">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/sales') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon dpurple">
                            <h4><span class="fa fa-shopping-cart" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_ar'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatMoney(($todayData->amount-$todayData->paid) - ($todayData->total_return - $todayData->total_return_paid))?></div>
                                </div>
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatMoney(($thisMonthData->amount-$thisMonthData->paid) - ($thisMonthData->total_return - $thisMonthData->total_return_paid))?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatMoney(($lastMonthData->amount-$lastMonthData->paid) - ($lastMonthData->total_return - $lastMonthData->total_return_paid))?></div>                           
                                </div>
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #3e39a5">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/ar_aging') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon dorange">
                            <h4><span class="fa fa-shopping-cart" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_purchases'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatMoney($todayPurchase->amount - $todayPurchase->total_return)?></div>
                                </div>
                                 <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatMoney($thisMonthPurchase->amount - $thisMonthPurchase->total_return)?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatMoney($lastMonthPurchase->amount - $lastMonthPurchase->total_return)?></div>                              
                                </div>
                               
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #D84A0D">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/purchases') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon dgreen">
                            <h4><span class="fa fa-money" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_total">
                                    <div class="box_name"><?= lang('today_expenses'); ?></div>
                                    <div class="box_number"><?=$this->cus->formatMoney($todayExpenses->amount)?></div>
                                </div>
                                <div class="box_lw">
                                    <div class="box_name_lw"><?= lang('this_month'); ?></div>
                                    <div class="box_number_lw"><?=$this->cus->formatMoney($thisMonthExpenses->amount)?></div>                           
                                </div>
                                <div class="box_lm">
                                    <div class="box_name_lm"><?= lang('last_month'); ?></div>
                                    <div class="box_number_lm"><?=$this->cus->formatMoney($lastMonthExpenses->amount)?></div>                              
                                </div>
                                
                            </div> 
                        </div>
                        <div class="box-footer" style="background: #0e7e38">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('reports/expenses') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

            <?php } ?>
    </div>

<!-- Overview Chart Heading -->

    <div class="box" style="margin-bottom: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('overview_chart'); ?></h2>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-md-12">
                    <p class="introtext"><?php echo lang('overview_chart_heading'); ?></p>

                    <div id="ov-chart" style="width:100%; height:320px;"></div>
                    <p class="text-center"><?= lang("chart_lable_toggle"); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php } ?>



<script type="text/javascript">
    $(document).ready(function () {
        $('.order').click(function () {
            window.location.href = '<?=site_url()?>orders/view/' + $(this).attr('id') + '#comments';
        });
        $('.invoice').click(function () {
            window.location.href = '<?=site_url()?>orders/view/' + $(this).attr('id');
        });
        $('.quote').click(function () {
            window.location.href = '<?=site_url()?>quotes/view/' + $(this).attr('id');
        });
    });
</script>

    <?php if (($Owner || $Admin || $GP) && $chatData) { 
    if($chatData){
        foreach ($chatData as $month_sale) {
            $months[] = date('M-Y', strtotime($month_sale->month));
            $msales[] = $month_sale->sales;
            $mtax1[] = $month_sale->tax1;
            $mtax2[] = $month_sale->tax2;
            $mpurchases[] = $month_sale->purchases;
            $mtax3[] = $month_sale->ptax;
        }
    }
    ?>

    <style type="text/css" media="screen">
        .tooltip-inner {
            max-width: 500px;
        }
    </style>
     <script src="<?= $assets; ?>js/hc/highcharts.js"></script>

     <script type="text/javascript">
        $(function () {
            $('#ov-chart').highcharts({
                chart: {},
                credits: {enabled: false},
                title: {
                    text: '<?=lang("sales")?>'
                },
                xAxis: {categories: <?= json_encode($months); ?>},
                yAxis: {min: 0, title: ""},
                tooltip: {
                    shared: true,
                    followPointer: true,
                    formatter: function () {
                        if (this.key) {
                            return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                        } else {
                            var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                            $.each(this.points, function () {
                                s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                currencyFormat(this.y) + '</b></td></tr>';
                            });
                            s += '</table></div>';
                            return s;
                        }
                    },
                    useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                    style: {fontSize: '14px', padding: '0', color: '#000000'}
                },
                labels: {
                    items: [{
                        html: '<?= lang("stock_value"); ?>',
                        style: {
                            left: '85px',
                            top: '18px',
                            color: (
                                Highcharts.defaultOptions.title.style &&
                                Highcharts.defaultOptions.title.style.color
                            ) || 'black'
                        }
                    }]
                },
                series: [{
                    type: 'column',
                    name: '<?= lang("sp_tax"); ?>',
                    visible: false,
                    data: [<?php echo implode(', ', $mtax1);?>]
                },
                    {
                        type: 'column',
                        name: '<?= lang("order_tax"); ?>',
                        visible: false,
                        data: [<?php echo implode(', ', $mtax2);?>]
                    },
                    {
                        type: 'column',
                        colorByPoint: true,
                        name: '<?= lang("sales"); ?>',
                        data: [<?php echo implode(', ', $msales); ?>]
                    }, {
                        type: 'spline',
                        colorByPoint: true,
                        name: '<?= lang("purchases"); ?>',
                        data: [<?php echo implode(', ', $mpurchases); ?>],
                        marker: {
                            lineWidth: 2,
                            states: {
                                hover: {
                                    lineWidth: 4
                                }
                            },
                            lineColor: Highcharts.getOptions().colors[3],
                            fillColor: 'white'
                        }
                    }, {
                        type: 'spline',
                        name: '<?= lang("pp_tax"); ?>',
                        visible: false,
                        data: [<?php echo implode(', ', $mtax3);?>],
                        marker: {
                            lineWidth: 2,
                            states: {
                                hover: {
                                    lineWidth: 4
                                }
                            },
                            lineColor: Highcharts.getOptions().colors[3],
                            fillColor: 'white'
                        }
                    }, {
                        type: 'pie',
                        name: '<?= lang("stock_value"); ?>',
                        data: [
                            {name:'<?= lang("stock_value_by_price"); ?>', y: <?php echo $stock->stock_by_price; ?>},
                            {name:'<?= lang("stock_value_by_cost"); ?>', y: <?php echo $stock->stock_by_cost; ?>},
                        ],
                        center: [100, 80],
                        size: 100,
                        showInLegend: false,
                        dataLabels: {
                            enabled: false
                        }
                    }]
            });
        });
    </script>

   <?php  
    if($chatDataExpense){
        foreach ($chatDataExpense as $month_expense) {
            $monthsex[] = date('M-Y', strtotime($month_expense->month));
            $mexpense[] = $month_expense->expenses;
        }
    }
    ?>
    <script type="text/javascript">
        $(function () {
            $('#ox-chart').highcharts({
                chart: {},
                credits: {enabled: false},
                title: {
                    text: '<?=lang("expense")?>'
                },
                xAxis: {categories: <?= json_encode($monthsex); ?>},
                yAxis: {min: 0, title: ""},
                tooltip: {
                    shared: true,
                    followPointer: true,
                    formatter: function () {
                        if (this.key) {
                            return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                        } else {
                            var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                            $.each(this.points, function () {
                                s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                currencyFormat(this.y) + '</b></td></tr>';
                            });
                            s += '</table></div>';
                            return s;
                        }
                    },
                    useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                    style: {fontSize: '14px', padding: '0', color: '#000000'}
                },
                series: [{
                        type: 'column',
                        colorByPoint: true,
                        name: '<?= lang("expense"); ?>',
                        data: [<?php echo implode(', ', $mexpense); ?>]
                }, {
                        center: [100, 80],
                        size: 100,
                        showInLegend: false,
                        dataLabels: {
                            enabled: false
                        }
                    }]
            });
        });
    </script>
<?php } ?>


    <style type="text/css">
        .box-dashboard{
            min-height:110px;
            margin-bottom:20px;
            /*border-radius:4px;*/
            color:white; 
            clear:both;
        }

        .box-content_icon{
            padding:10px 10px 110px 10px !important;
            border-radius: 5px 5px 0px 0px;
        }
        .box_right{
            float: left;
        }
        .box_number{
            font-size: 25px;
            font-weight: bold;
            text-align: left;
            margin-top: -3px;
        }
        .box_number_footer{
            font-size: 22px;
            margin-top: 5px;
            font-weight: bold;
            text-align: right;
        }
        .box_name{
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-top: -15px;
            padding-right: 95px;
        }

        .box-content{
            padding:10px 10px 5px 10px;
        }
        .box-footer{
            padding:0px 0;
            margin:0;
            text-align:center;
            border-radius:0 0 4px 4px;
            font-size:13px;
        }
        .box-footer a{
            color:white;
            text-decoration:none;
            cursor:pointer;
        }
        .box-dashboard h4{
            font-size:16px;
        }
        .box-dashboard ul li{
            list-style:none;
            line-height:30px;
            font-size:12px;
        }
        .box-dashboard span{
            float:right;
            font-size: 20px;
            font-weight: bold;
        }
        .highcharts-title, .box-dashboard h4 {
            font-family: 'Ubuntu','Moul', sans-serif;
        } 
        .highcharts-subtitle{
            font-family: 'Ubuntu','Nokora', sans-serif; 
        } 
        #icon{
            font-size: 65px;
            margin-top: -15px;
            opacity: 24%;
/*            position: absolute;*/
        }
        .dblue{
            background-color: #2caffe;#D143F8
            /* background: linear-gradient(to bottom right, #2caffe, #D143F8); */
        }
        .dpurple{
            background-color: #544fc5;
            /* background: linear-gradient(to bottom right, #544fc5, #4DBFF5); */
        }
        .dorange{
            background-color: #FC5913;
            /* background: linear-gradient(to bottom right, red, yellow); */
        }
        .dgreen{
            background: #149544;
            /* background: linear-gradient(to bottom right, #0e7e38, yellow); */
        }
        .box_name_lm{
            font-size: 15px;
            float: right;
            margin: 0px 0px 0px 20px;
        }
        .box_number_lm{
            font-size: 19px;
            font-weight: bold;
            text-align: right;
            margin: 0px 0px 0px 0px;
        }
        .box_name_lw{
            font-size: 15px;
            float: left;
            margin: 0px 0px 0px 0px;
        }
        .box_number_lw{
            font-size: 19px;
            font-weight: bold;
            text-align: left;
        }
        .box_lm{
            display: inline-block;
            float: right;
            margin: 10px 0px 0px 25px;
        }
        .box_lw{
            display: inline-block;
            float: left;
            margin: 10px 0px 0px 0px;
        }
        .box_total{
            float: left;
            margin: -50px 0px 0px 0px;
        }

    </style>
