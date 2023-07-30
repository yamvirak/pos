<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    .dfTable th, .dfTable td {
        text-align: center;
        vertical-align: middle;
    }
    .dfTable td {
        padding: 2px;
    }

    .data tr:nth-child(odd) td {
        color: #2FA4E7;
    }

    .data tr:nth-child(even) td {
        text-align: right;
    }
</style>

<?php echo form_open("reports/sales_detail"); ?>
 
<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('sales_detail_report').' ('.(isset($sel_warehouse) ? $sel_warehouse->name : lang('all_warehouses')).')'; ?></h2> -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="javascript:;" onclick="window.print();" id ="print" class="tip btn btn-success btn-block box_sub_menu" 
                title="<?= lang('print') ?>">
                <i class="icon fa fa-file-fa fa-print"></i>&nbsp;</i><?=lang('print')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" class="tip btn btn-warning btn-block box_sub_menu" title="<?= lang('download_xls') ?>" data-action="export_excel">
                <i class="icon fa fa-file-excel-o"></i>&nbsp;</i><?=lang('download_xls')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="toggle_down tip btn btn-info btn-block box_sub_menu" title="<?= lang('show_form') ?>">
                <i class="icon fa fa-eye"></i>&nbsp;</i><?=lang('show_form')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="toggle_up tip btn btn-danger btn-block box_sub_menu" title="<?= lang('hide_form') ?>">
                <i class="icon fa fa-eye-slash"></i>&nbsp;</i><?=lang('hide_form')?>
            </a>
        </div>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-dollar tip"></i><?= lang('sales_detail_report'); ?></h2>
                </li>
                <!-- <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>

                <li class="dropdown hidden">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
                
                <li class="dropdown">
                    <a href="#" id="excel" class="tip" title="<?= lang('download_xls') ?>" data-action="export_excel">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:;" onclick="window.print();" id ="print" class="tip" title="<?= lang('print') ?>"><i class="icon fa fa-file-fa fa-print"></i></a>
                </li> -->
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang("customize_report") ?></p> -->
                <div id="form">
                    <div class="row no-print">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>
                        <div class="col-sm-4 hidden">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <?php if($this->Settings->product_serial){ ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="serial_no"><?= lang("serial_no"); ?></label>
                                    <?php echo form_input('serial_no', (isset($_POST['serial_no']) ? $_POST['serial_no'] : ""), 'class="form-control tip" id="serial_no"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-sm-4 hidden">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("product_zero"); ?></label>
                                <?php
                                $pz = array(lang("all"),lang("yes"));
                                    echo form_dropdown('product_zero', $pz, (isset($_POST['product_zero']) ? $_POST['product_zero'] : 0), 'class="form-control" id="product_zero" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product_zero") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hidden">
                            <div class="form-group">
                                <label class="control-label" for="sale_tax"><?= lang("sale_tax"); ?></label>
                                <?php
                                $stax[""] = lang('select').' '.lang('sale_tax');
                                $stax["yes"] = lang('yes');
                                $stax["no"] = lang('no');
                                echo form_dropdown('sale_tax', $stax, (isset($_POST['sale_tax']) ? $_POST['sale_tax'] : ""), 'class="form-control" id="sale_tax" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sale_tax") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("saleman"); ?></label>
                                <?php
                                $opsaleman[""] = lang('select').' '.lang('saleman');
                                foreach ($salemans as $saleman) {
                                    $opsaleman[$saleman->id] = $saleman->last_name . " " . $saleman->first_name;
                                }
                                echo form_dropdown('saleman', $opsaleman, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line("saleman") . " " . $this->lang->line("saleman") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <?php if($Settings->project == 1){ ?>
                                    
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("project", "project"); ?>
                                    <div class="no-project">
                                        <?php
                                        $pj[''] = '';
                                        if (isset($projects) && $projects != false) {
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
                                        echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        
                        <?php } ?>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="sale_types"><?= lang("sale_types"); ?></label>
                                <?php
                                $st[""] = lang('select').' '.lang('sale_types');
                                $st["sale"] = lang('sale');
                                $st["pos"] = lang('pos');
                                $st["return"] = lang('return');
                                echo form_dropdown('sale_type', $st, (isset($_POST['sale_type']) ? $_POST['sale_type'] : ""), 'class="form-control" id="sale_type" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sale_types") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <?php if($Settings->car_operation == 1){ ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="vehi"><?= lang("vehicle_model"); ?></label>
                                    <?php echo form_input('vehicle_model', (isset($_POST['vehicle_model']) ? $_POST['vehicle_model'] : ""), 'class="form-control tip" id="vehicle_model"'); ?>

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="vehicle_plate"><?= lang("vehicle_plate"); ?></label>
                                    <?php echo form_input('vehicle_plate', (isset($_POST['vehicle_plate']) ? $_POST['vehicle_plate'] : ""), 'class="form-control tip" id="vehicle_plate"'); ?>

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="vehicle_vin"><?= lang("vehicle_vin"); ?></label>
                                    <?php echo form_input('vehicle_vin', (isset($_POST['vehicle_vin']) ? $_POST['vehicle_vin'] : ""), 'class="form-control tip" id="vehicle_vin"'); ?>

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="mechanic"><?= lang("mechanic"); ?></label>
                                    <?php echo form_input('mechanic', (isset($_POST['mechanic']) ? $_POST['mechanic'] : ""), 'class="form-control tip" id="mechanic"'); ?>

                                </div>
                            </div>
                        
                        <?php } ?>
                        
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <div class="input-group input-append">
                                    <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <div class="input-group input-append">
                                        <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                                </div>
                            </div>
                        </div>
                        
                        
                    </div>
                    <div class="form-group">
                        <div class="controls"> 
                            <?php echo form_submit('submit_report', $this->lang->line("Search"), 'class="btn btn-primary"'); ?> 
                        </div>
                    </div>
                    
                    
                    
                </div>

                <table style="margin-top: 5px; width:100%;">
                    <th>
                        <tr>  
                            <?php 
                                $biller_id = (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller);
                                $biller_id_all = lang('all_selected');
                                $biller_id_detail = $this->site->getCompanyByID($biller_id);
                                if($biller_id_detail){
                                ?>
                                <td class="text_left" style="width: 10%">
                                    <div>
                                        <?= !empty($biller_id_detail->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller_id_detail->logo).'" alt="">' : ''; ?>
                                    </div>
                                </td>
                                <td></td>
                                <td class="text_center" style="width:100%">
                                    <div>
                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller_id_detail->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller_id_detail->name;?></strong>
                                    </div>
                                <br>

                                <?php 
                                }else{
                                ?>

                                <td class="text_left" style="width: 10%">
                                    <div>
                                        <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                    </div>
                                </td>
                                <td></td>
                                <td class="text_center" style="width:100%">
                                    <div>
                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
                                    </div>
                                <br>

                                <?php } ?>
                    
                                <?php 
                                    $sale_type_id = (isset($_POST['sale_type_id']) ? $_POST['sale_type_id'] : false);
                                    $sale_type_id_all = lang('all_selected');
                                    //$sale_type_id_detail = $this->site->getSaleTypesByID($sale_type_id);
                                    if($sale_type_id == 1){
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('cash_monthly_report_kh').'</div>';
                                        echo '<div class="bold">'.lang('cash_monthly_report_en').'</div><br>';
                                    }elseif($sale_type_id == 2){
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('deposit_monthly_report_kh').'</div>';
                                        echo '<div class="bold">'.lang('deposit_monthly_report_en').'</div><br>';

                                    }elseif($sale_type_id == 3){
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('loan_monthly_report_kh').'</div>';
                                        echo '<div class="bold">'.lang('loan_monthly_report_en').'</div><br>';

                                    }else{
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('monthly_sale_detail_report_kh').'</div>';
                                        echo '<div class="bold">'.lang('monthly_sale_detail_report_en').'</div><br>';
                                }
                                   
                                ?>
                               
                            </td> 
                        </tr>
                </table>
                
                <div class="table-responsive">
                    <table id="saleTable" class="table table-bordered table-striped dfTable reports-table">
                        <thead>
                            <th><?= lang("date") ?></th>
                            <th><?= lang("reference_no") ?></th>
                            <th><?= lang("customer") ?></th>
                            <?php if($pos_settings->table_enable == 1){ ?>
                                <th><?= lang("tables") ?></th>
                            <?php } ?>
                            <th><?= lang("product_code") ?></th>
                            <th><?= lang("product_name") ?></th>                            
                            <th><?= lang("quantity") ?></th>
                            <?php if($Settings->foc==1){ $colspan_foc = 1;?>
                                <th><?= lang("foc") ?></th>
                            <?php } else {$colspan_foc = 0;} ?>
                            <th><?= lang("unit_price") ?></th>
                            <th><?= lang("discount") ?></th>
                            <th><?= lang("subtotal") ?></th>
                            <th><?= lang("total") ?></th>
                            <th><?= lang("order_discount") ?></th>
                            <th><?= lang("order_tax") ?></th>
                            <th><?= lang("shipping") ?></th>
                            <th><?= lang("grand_total") ?></th>
                            <th><?= lang("paid") ?></th>
                            <th><?= lang("discount") ?></th>
                            <th><?= lang("balance") ?></th>
                        </thead>
                        <tbody>
                        <?php   
                            $grand_subtotal = 0;
                            $grand_order_discount = 0;
                            $grand_order_tax = 0;
                            $grand_shipping = 0;
                            $grand_grand_total = 0;
                            $grand_paid = 0;
                            $grand_disoucnt = 0;
                            $grand_balance = 0; 
                            if (isset($sales) && $sales != false) {

                                foreach($sales as $sale)
                                {
                                    $body = '';
                                    $bgcolor = 'f4f6f9';
                                    if($sale->sale_status=='returned'){
                                        $bgcolor = 'fcf8e3';
                                    }
                                    $sale_items = $this->reports_model->getAllSaleItemsId($sale->id, $this->input->post());
                                    if($sale_items){
                                        $payment = $this->reports_model->getPaymentBySaleID($sale->id);
                                        if(!$payment){
                                            $payment = (object)array();
                                            $payment->paid = 0;
                                            $payment->discount = 0;
                                        }
                                        $rowspan=1;
                                        $subtotal = 0;
            
                                        foreach($sale_items as $sale_item){
                                            $subtotal += $sale_item->subtotal;
                                            $number = '';
                                            $unit_price = 0;
                                            if($sale_item->item_discount > 0){
                                                $unit_price = $sale_item->unit_price + ($sale_item->item_discount / $sale_item->unit_quantity) ;
                                            }else{
                                                $unit_price = $sale_item->unit_price;
                                            }
                                            if($sale_item->electricity > 0){
                                                $number = ' ['.$sale_item->old_number.'] ['.$sale_item->new_number.']';
                                            }
                                            
                                            if($Settings->foc==1){
                                                $td_foc = '<td style="background-color:white">'.$this->cus->formatQuantity($sale_item->foc) .'</td>';
                                            }else{
                                                $td_foc = '';
                                            }
                                            if($sale_item->unit_price==0){
                                                $body .='
                                                        <tr style="color:red; font-weight:bold; text-decoration:underline;">
                                                            <td style="background-color:white" class="left">'.$sale_item->product_code.'</td>                                       
                                                            <td style="background-color:white" class="left">'.$sale_item->product_name." ".($sale_item->serial_no?"( ".$sale_item->serial_no." )":" ").$number.'</td>   
                                                            <td style="background-color:white">'.$this->cus->convertQty($sale_item->product_id,$sale_item->quantity) .'</td>    
                                                            '.$td_foc.'
                                                            <td style="background-color:white" class="right">'.$this->cus->formatMoney($unit_price).'</td>  
                                                            <td style="background-color:white" class="right">'.$this->cus->formatMoney($sale_item->discount).'</td> 
                                                            <td style="background-color:white" class="right">'.$this->cus->formatMoney($sale_item->subtotal).'</td> 
                                                        </tr>';
                                            }else{
                                                $body .='
                                                        <tr>
                                                            <td style="background-color:white" class="left">'.$sale_item->product_code.'</td>                                       
                                                            <td style="background-color:white" class="left">'.$sale_item->product_name." ".($sale_item->serial_no?"( ".$sale_item->serial_no." )":" ").$number.'</td>   
                                                            <td style="background-color:white">'.$this->cus->convertQty($sale_item->product_id,$sale_item->quantity) .'</td>    
                                                            '.$td_foc.'
                                                            <td style="background-color:white" class="right">'.$this->cus->formatMoney($unit_price).'</td>  
                                                            <td style="background-color:white" class="right">'.((strpos($sale_item->discount, '%') !== false) ? $sale_item->discount : $this->cus->formatMoney($sale_item->discount)).'</td>
                                                            <td style="background-color:white" class="right">'.$this->cus->formatMoney($sale_item->subtotal).'</td> 
                                                        </tr>';
                                            }
                                                        
                                            $rowspan++;
                                        
                                        } 
                                        $header = '
                                                <tr class="invoice_link2" id="'.$sale->id.'">
                                                    <td style="background-color:#'.$bgcolor.'; text-align:left" rowspan="'.$rowspan.'">'.$this->cus->hrsd($sale->date).'</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:left" rowspan="'.$rowspan.'">'.$sale->reference_no.'</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:left" rowspan="'.$rowspan.'">'.$sale->customer.'</td>
                                                    '.($pos_settings->table_enable == 1 ? '<td style="background-color:#'.$bgcolor.'; text-align:center" rowspan="'.$rowspan.'">'.$sale->table_name.'</td>':'').'
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right; padding:0px; border:0px !important;" colspan="'.(6 + $colspan_foc).'"></td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right" rowspan="'.$rowspan.'">'.$this->cus->formatMoney($subtotal).'</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right" rowspan="'.$rowspan.'">'.$this->cus->formatMoney($sale->order_discount) . '</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right" rowspan="'.$rowspan.'">' .$this->cus->formatMoney($sale->order_tax) . '</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right" rowspan="'.$rowspan.'">' . $this->cus->formatMoney($sale->shipping) . '</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right" rowspan="'.$rowspan.'">'.$this->cus->formatMoney($sale->grand_total).'</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right" rowspan="'.$rowspan.'">'.$this->cus->formatMoney($payment->paid).'</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right" rowspan="'.$rowspan.'">'.$this->cus->formatMoney($payment->discount).'</td>
                                                    <td style="background-color:#'.$bgcolor.'; text-align:right" rowspan="'.$rowspan.'">'.$this->cus->formatMoney($sale->grand_total -($payment->paid+$payment->discount)).'</td>
                                                </tr>';     
                                                
                                            echo $header.$body;
                                
                                }
                                $grand_subtotal += $subtotal;
                                $grand_order_discount += $sale->order_discount;
                                $grand_order_tax += $sale->order_tax;
                                $grand_shipping += $sale->shipping;
                                $grand_grand_total += $sale->grand_total;
                                $grand_paid += $payment->paid;
                                $grand_disoucnt += $payment->discount;
                                $grand_balance += ($sale->grand_total -($payment->paid+$payment->discount));                        
                            }                        
                            
                        } ?>
                        <tr>
                            <td class="right bold" colspan="<?=(($pos_settings->table_enable == 1?10:9) + $colspan_foc) ?>"><?= lang("grand_total") ?> : </td>
                            <td class="right bold"><?= $this->cus->formatMoney($grand_subtotal); ?></td>
                            <td class="right bold"><?= $this->cus->formatMoney($grand_order_discount)?></td>
                            <td class="right bold"><?= $this->cus->formatMoney($grand_order_tax)?></td>
                            <td class="right bold"><?= $this->cus->formatMoney($grand_shipping)?></td>
                            <td class="right bold"><?= $this->cus->formatMoney($grand_grand_total)?></td>
                            <td class="right bold"><?= $this->cus->formatMoney($grand_paid)?></td>
                            <td class="right bold"><?= $this->cus->formatMoney($grand_disoucnt)?></td>
                            <td class="right bold"><?= $this->cus->formatMoney($grand_balance)?></td>
                            
                        </tr>
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var customer_id = "<?= isset($_POST['customer'])?$_POST['customer']:0 ?>";
        if (customer_id > 0) {
          $('#customer_id').val(customer_id).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
              $.ajax({
                type: "get", async: false,
                url: site.base_url+"customers/getCustomer/" + $(element).val(),
                dataType: "json",
                success: function (data) {
                  callback(data[0]);
                }
              });
            },
            ajax: {
              url: site.base_url + "customers/suggestions",
              dataType: 'json',
              deietMillis: 15,
              data: function (term, page) {
                return {
                  term: term,
                  limit: 10
                };
              },
              results: function (data, page) {
                if (data.results != null) {
                  return {results: data.results};
                } else {
                  return {results: [{id: '', text: 'No Match Found'}]};
                }
              }
            }
          });
        }else{
          $('#customer_id').select2({
            minimumInputLength: 1,
            ajax: {
              url: site.base_url + "customers/suggestions",
              dataType: 'json',
              quietMillis: 15,
              data: function (term, page) {
                return {
                  term: term,
                  limit: 10
                };
              },
              results: function (data, page) {
                if (data.results != null) {
                  return {results: data.results};
                } else {
                  return {results: [{id: '', text: 'No Match Found'}]};
                }
              }
            }
          });
        }
        $('#form').hide();
        
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        
        $("#biller").change(biller); biller();
        function biller(){
            var biller = $("#biller").val();
            var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
            $.ajax({
                url : "<?= site_url("reports/get_project") ?>",
                type : "GET",
                dataType : "JSON",
                data : { biller : biller, project : project },
                success : function(data){
                    if(data){
                        $(".no-project").html(data.result);
                        $("#project").select2();
                    }else{
                        
                    }
                }
            })
        }
        
    });
</script>
