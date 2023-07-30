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
        <style type="text/css" media="all">
            body { color: #000;font-size: 11px !important; }
            #wrapper { max-width: 480px; margin: 0 auto; padding-top: 0px; }
            .btn { border-radius: 0; margin-bottom: 5px; }
            .bootbox .modal-footer { border-top: 0; text-align: center; }
            h3 { margin: 5px 0; }
            th{font-size:10px !important;}
            .order_barcodes img { float: none !important; margin-top: 5px; }
            @media print {
                .no-print { display: none; }
                #wrapper { max-width: 480px; width: 100%; min-width: 250px; margin: 0 auto; }
                .no-border { border: none !important; }
                .border-bottom { border-bottom: 1px solid #ddd !important; }
                body { color: #000;font-size: 11px !important; }
                th{font-size:10px !important;}
                
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
            <style type="text/css">
                .invoice_inv{
                    font-size: 16px;
                    font-weight: bold;
                    font-family: Khmer OS Muol Light;
                }
                .full_stop{
                    text-align: center;
                }
                .invoice_header{
                    font-weight: bold;
                    font-size: 25px;
                }
            </style>
            <div id="receipt-data">

                <div class="text-center">
                    <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="" style="text-align:center;">' : ''; ?>
                </div>
                               
                <div class="text-center">
                    
                    <?php
                    echo "<p>" . lang("address") . ": " . $biller->address."</br>";
                    echo "" . lang("tel") . ": " . $biller->phone. "</p>";
                    echo "<div class='invoice_inv'> វិក្កយបត្រ/ INVOICE</div>";
                    // comment or remove these extra info if you don't need
                    // if (!empty($biller->cf11) && $biller->cf11 != "-") {
                    //     echo "<br>" . lang("bcf1") . ": " . $biller->cf1;
                    // }
                    // if (!empty($biller->cf2) && $biller->cf2 != "-") {
                    //     echo "<br>" . lang("bcf2") . ": " . $biller->cf2;
                    // }
                    // if (!empty($biller->cf3) && $biller->cf3 != "-") {
                    //     echo "<br>" . lang("bcf3") . ": " . $biller->cf3;
                    // }
                    // if (!empty($biller->cf4) && $biller->cf4 != "-") {
                    //     echo "<br>" . lang("bcf4") . ": " . $biller->cf4;
                    // }
                    // if (!empty($biller->cf5) && $biller->cf5 != "-") {
                    //     echo "<br>" . lang("bcf5") . ": " . $biller->cf5;
                    // }
                    // if (!empty($biller->cf6) && $biller->cf6 != "-") {
                    //     echo "<br>" . lang("bcf6") . ": " . $biller->cf6;
                    // }
                    // end of the customer fields

                    //echo "<br>";
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
                    echo lang("queue") . ": " .$pos->queue_number . " <br/>";
                }
              //echo lang("inv_branch") . ":"."<span style='float:right;'>" . $inv->biller.;"</span><br>";
                //echo lang("inv_branch") . ":"."<span style='float:right;'>" .$warehouses->name. "</span><br>";

               

                echo lang("inv_date") . "<span class='full_stop'>:</span>"."<span style='float:right;'>". $this->cus->hrld($inv->date,1) . "</span><br>";
                echo lang("inv_sale") . "<span class='full_stop'>:</span><span style='float:right;'>" . $inv->reference_no . "</span><br>";
                if (!empty($inv->return_sale_ref)) {
                    echo '<p>'.lang("return_ref")."<span class='full_stop'>:</span><span style='float:right;'>".$inv->return_sale_ref. "</span>";
                    if ($inv->return_id) {
                        echo ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                    } else {
                        echo '</p>';
                    }
                }
                echo lang("inv_user") . ":"."<span style='float:right;'>". $created_by->first_name." ".$created_by->last_name . "</span><br/>";                
                echo lang("inv_customer") . ":"."<span style='float:right;'>" . ($customer->company && $customer->company != '-' ? $customer->name : $customer->name) . "</span><br>";
                //echo lang("inv_phone") . ":"."<span style='float:right;'>" . ($customer->phone && $customer->phone != '-' ? $customer->phone : $customer->phone) . "</span><br>";
                if(isset($table) && $table != false && $table->name !=''){
                    echo lang("inv_table") . ":"."<span style='float:right;font-weight:bold;font-size:14px;'>". $table->name."</span><br>";     
                }
                if(isset($inv) && $inv != false && $inv->rental_name !=''){
                    echo lang("inv_room") . ":"."<span style='float:right;font-weight:bold;font-size:14px;'>". $inv->rental_name."</span><br>";     
                }
                if ($pos_settings->customer_details) {
                    if ($customer->vat_no != "-" && $customer->vat_no != "") {
                        echo "<br>" . lang("vat_no") . ": " . $customer->vat_no;
                    }
                    echo lang("tel") . ": " . $customer->phone . "<br>";
                    echo lang("address") . ": " . $customer->address . "<br>";
                    echo $customer->city ." ".$customer->state." ".$customer->country ."<br>";
                    // if (!empty($customer->cf1) && $customer->cf1 != "-") {
                    //     echo "<br>" . lang("ccf1") . ": " . $customer->cf1;
                    // }
                    // if (!empty($customer->cf2) && $customer->cf2 != "-") {
                    //     echo "<br>" . lang("ccf2") . ": " . $customer->cf2;
                    // }
                    // if (!empty($customer->cf3) && $customer->cf3 != "-") {
                    //     echo "<br>" . lang("ccf3") . ": " . $customer->cf3;
                    // }
                    // if (!empty($customer->cf4) && $customer->cf4 != "-") {
                    //     echo "<br>" . lang("ccf4") . ": " . $customer->cf4;
                    // }
                    // if (!empty($customer->cf5) && $customer->cf5 != "-") {
                    //     echo "<br>" . lang("ccf5") . ": " . $customer->cf5;
                    // }
                    // if (!empty($customer->cf6) && $customer->cf6 != "-") {
                    //     echo "<br>" . lang("ccf6") . ": " . $customer->cf6;
                    // }
                }
                echo "</p>";

                ?>

                <div style="clear:both;"></div>
                <style type="text/css">
                    .th_col{
                        color: white;
                    }
                    .border_round{
                        vertical-align:middle;
                        border:1px solid #000 !important;
                        border-top: 1px solid #000 !important;
                        border-bottom: 1.5px solid #000 !important;
                    }
                    .header_top{
                        border-top:2px solid #000 !important;
                        border-bottom: 2px solid #000 !important;
                    }
                    .header_buttom{
                        border-bottom: 2px solid #000 !important;
                    }
                    

                </style>

                <table class="table table-striped table-condensed" style="margin-bottom:3px !important;">
                    <tbody>
                        <tr class="header_top">
                            <th class="text-center"><?=lang("ល.រ<br>No.");?></th>
                            <th class="text-center"><?=lang("បរិយាយ <br> Description");?></th>
                            <th class="text-center"><?=lang("ចំនួន<br>Qty");?></th>
                            <th class="text-center"><?=lang("តំលៃ<br>Price");?><?= ($inv->total_discount > 0 ? ' ('.lang('dis').') ' : '') ?></th>
                            <th class="text-center"><?=lang("សរុប<br>Amount");?></th>
                        </tr>
                    </tbody>
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
                            echo '<tr>
                                    <td class="text-center">' . $r . '</td>
                                    <td>' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') .($row->comment!=''?'<br>'.$row->comment:'').($row->bom_type!=''?'<br>'.$row->bom_type:''). '</td>
                                    
                                     <td class="text_center">'.$this->cus->convertQty($row->product_id,$row->quantity).'</td>
                                    <td style="text-align:center">' .$this->cus->formatMoney($row->unit_price) . ' '.($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '').'</td>


                                    <td style="text-align:right">' . $this->cus->formatMoney($row->subtotal) . '</td>
                                </tr>';

                            // echo '<tr><td class="no-border border-bottom">' . $this->cus->formatQuantity($row->quantity) . ' x ';
                            // if ($row->item_discount != 0) {
                            //     echo '<del>' . $this->cus->formatMoney($row->net_unit_price + ($row->item_discount / $row->quantity) + ($row->item_tax / $row->quantity)) . '</del> ';
                            // }
                            // echo $this->cus->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)).' ('.$this->cus->formatMoney($row->net_unit_price).' + '.$this->cus->formatMoney($row->item_tax / $row->quantity) . ')</td><td class="no-border border-bottom text-right">' . $this->cus->formatMoney($row->subtotal) . '</td></tr>';
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
                                    <td>' . $r . '</td>
                                    <td>' .product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') . '</td>
                                    <td style="text-align:center">' .number_format($row->unit_quantity,0).'</td>
                                    <td style="text-align:center">' . $this->cus->formatMoney($row->net_unit_price + ($row->item_tax / $row->unit_quantity)) . '</td>
                                    <td style="text-align:right">' . $this->cus->formatMoney($row->subtotal) . '</td>
                                </tr>';
                                
                                //echo '<tr><td colspan="2" class="no-border">#' . $r . ': &nbsp;&nbsp;' . product_name($row->product_name, $printer->char_per_line) . ($row->variant ? ' (' . $row->variant . ')' : '') . '<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td></tr>';
                                //echo '<tr><td class="no-border border-bottom">' . $this->cus->formatQuantity($row->quantity) . ' x '.$this->cus->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)).'</td><td class="no-border border-bottom text-right">' . $this->cus->formatMoney($row->subtotal) . '</td></tr>';

                                // echo '<tr><td class="no-border border-bottom">' . $this->cus->formatQuantity($row->quantity) . ' x ';
                                // if ($row->item_discount != 0) {
                                //     echo '<del>' . $this->cus->formatMoney($row->net_unit_price + ($row->item_discount / $row->quantity) + ($row->item_tax / $row->quantity)) . '</del> ';
                                // }
                                // echo $this->cus->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)) . '</td><td class="no-border border-bottom text-right">' . $this->cus->formatMoney($row->subtotal) . '</td></tr>';
                                $r++;
                            }
                        }

                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-left" colspan="3"><?=lang("totals");?></th>
                            <th class="text-left"><?=lang(":");?></th>
                            <th class="text-right"><?=$this->cus->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax));?></th>
                        </tr>
                        <?php
                        if ($inv->order_tax != 0) {
                            echo '<tr><th class="text-left" colspan="3">' . lang("tax") . '</th>';
                            echo '<th class="text-left">:</th>';
                            echo '<th class="text-right">' . $this->cus->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
                        }
                        if ($inv->order_discount != 0) {
                            echo '<tr><th class="text-left" colspan="3">' . lang("order_discount_pos") . '</th>';
                            echo '<th class="text-left">:</th>';
                            echo '<th class="text-right">' . ($inv->order_discount_id != 0 ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->cus->formatMoney($inv->order_discount) . '</th></tr>';
                        }

                        if ($inv->shipping != 0) {
                            echo '<tr><th class="text-left" colspan="3">' . lang("shipping") . '</th>';
                            echo '<th class="text-left">:</th>';
                            echo '<th class="text-right">' . $this->cus->formatMoney($inv->shipping) . '</th></tr>';
                        }

                        if ($return_sale) {
                            if ($return_sale->surcharge != 0) {
                                echo '<tr><th class="text-left" colspan="3">' . lang("order_discount") . '</th>';
                                echo '<th class="text-left">:</th>';
                                echo '<th class="text-right">' . $this->cus->formatMoney($return_sale->surcharge) . '</th></tr>';
                            }
                        }
                        
                    
                            
                        if(json_decode($inv->currencies)){
                            foreach(json_decode($inv->currencies) as $currency){
                                if($currency->currency=='KHR'){
                                    $kh_rate = $currency->rate;
                                }
                                
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
                                    foreach(json_decode($inv->currencies) as $currency){ 
                                        $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                        $grand_total = (($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) / $base_currency->rate) * $currency->rate;
                                    ?>
                                        <tr>
                                            <th class="text-left" colspan="3"><?=lang("grand_totals");?> <small>[<?=$currency->currency?>]</small></th>
                                            <th class="text-left"><?=lang(":");?></th>
                                            <th class="text-right"><?=$this->cus->formatOtherMoney($grand_total, $currency->currency);?></th>
                                        </tr>
                                <?php
                                    }
                                }
                        } else {
                            if(json_decode($inv->currencies)){
                                foreach(json_decode($inv->currencies) as $currency){ 
                                    $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                    $grand_total = (($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) / $base_currency->rate) * $currency->rate;
                                ?>
                                    <tr>
                                        <th class="text-left" colspan="3"><?=lang("grand_totals");?> <small>(<?=$currency->currency?>)</small></th>
                                        <th class="text-left"><?=lang(":");?></th>
                                        <th class="text-right"><?=$this->cus->formatOtherMoney($grand_total, $currency->currency);?></th>
                                    </tr>
                            <?php
                                }
                            }
                        }

                        if ($payments){
                            foreach ($payments as $payment) {

                            ?>
                                 <tr>
                                    <th class="text-left" colspan="3"><?=lang("inv_paid_amount");?></th>
                                    <th class="text-left"><?=lang(":");?></th>
                                    <th class="text-right bold"><?=$this->cus->formatMoney($payment->pos_paid);?></th>
                                </tr>

                             <?php }  } if ($this->cus->formatDecimal($inv->paid) < $this->cus->formatDecimal($inv->grand_total)) { ?>
                                        
                                <tr>
                                    <th class="text-left" colspan="3"><?=lang("inv_due_amount");?></th>
                                    <th class="text-left"><?=lang(":");?></th>
                                    <th class="text-right bold"><?= $this->cus->formatMoney($inv->grand_total - $inv->paid) ?></th>
                                </tr>
                            <?php } ?>

                            <?php
                                if ($payments) {
                                    echo '<table class="table table-striped table-condensed" style="text-align:center;"><tbody>';
                                    foreach ($payments as $payment) {
                                        echo '<tr class="header_top">';
                                            echo '<th>' . lang("paid_by") . ': ' . $payment->paid_by . '</th>';
                                            echo '<th>' . lang("inv_change")."(USD)". ': ' . $this->cus->formatOtherMoney($payment->pos_balance > 0 ? ($payment->pos_balance) : 0) . '</th>';
                                            echo '<th>' . lang("inv_change")."(KHR)" . ': ' . $this->cus->formatKhMoney($payment->pos_balance > 0 ? ($payment->pos_balance) : 0,$currency->rate) . '</th>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                }
                            ?>
                <?php
                if ($payments) {
                    //echo '<table class="table table-striped table-condensed"><tfoot>';
                    foreach ($payments as $payment) {
                        
                        if (($payment->paid_by == 'cash') && $payment->pos_paid) {
                            echo '<tr>';
                            echo '<th class="text-left" colspan="3">' . lang("paid_amounts") . '</th>';
                            echo '<th class="text-left">:</th>';
                            echo '<th class="text-right bold" style="font-size:12px !important;"> ' . $this->cus->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</th>';

                            echo '</tr>';
                            echo '<tr style="border:2px dotted #000;padding-top: 5px;padding-bottom: 5px;font-weight: bold;borer:1px solid #000;">';
                            echo '<th class="text-left" colspan="2">' . lang("change") .'</th>';
                            echo' <th class="text-left" colspan="2" style="border:2px dotted #000; font-size:12px !important;">' . ($payment->pos_balance > 0 ?number_format($payment->pos_balance*$kh_rate,0) : 0) . ' (៛)</th>';
                            //echo' <th class="text-left" colspan="2" style="font-size:12px !important;">' . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance*4000) : 0) . ' ($)</th>';
                            echo' <th class="text-right" style="font-size:12px !important;">' . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance) : 0) . ' ($)</th>';
                            echo '</tr>';

                            //echo '<th>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</th></tr>';
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
                            echo '<th colspan="2" class="text-left">' . lang("paid_by"). '</th>';
                            echo '<th class="text-left">{'. lang($payment->paid_by) . '}</th>';
                            echo '<th class="text-left">:</th>';
                            echo '<th class="text-right">'. $this->cus->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</th>';
                        }elseif ($payment->paid_by == 'deposit' && $payment->amount) {
                            echo '<th colspan="2" class="text-left">' . lang("paid_by"). '</th>';
                            echo '<th class="text-left">{'. lang($payment->paid_by) . '}</th>';
                            echo '<th class="text-left">:</th>';
                            echo '<th class="text-right">'. $this->cus->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</th>';
                        }


                        echo '</tr>';

                    }
                    
                }

                echo '</tfoot></table>';
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

               <!--  <?= $customer->award_points != 0 && $Settings->each_spent > 0 ? '<p class="text-center">'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                .'<br>'.
                lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>' : ''; ?>
                <?= $inv->note ? '<p class="text-center">' . $this->cus->decode_html($inv->note) . '</p>' : ''; ?>
                <?= $inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->cus->decode_html($inv->staff_note) . '</p>' : ''; ?> -->

                <?= $biller->cf111 ? '<p class="text-center" style="font-size:9px;font-weight:bold;">'.$this->cus->decode_html($biller->cf1111).'</p>' : ''; ?>
      
                

                <div class="text-left hidden" style="margin-top: -22px;padding-bottom: 14px;font-size: 10px; padding-top: 5px;font-weight: bold;margin-left: 5px;border-top:2px dotted #000">
                    <?php 
                     echo '<span>'.lang("exchange_rate").' : 1 USD = '.number_format($kh_rate,0).' (រៀល)</span><br>';
                    ?>
                </div>
                <div class="text-center hidden" style="margin-top: -12px;padding-bottom: 7px;font-size: 9px; padding-top: 5px;font-weight: bold;">
                    <?= $biller->cf1 ? '<p class="text-center" style="font-size:9px;font-weight:bold;">'.$this->cus->decode_html($biller->cf1).'</p>' : ''; ?>
                 <?= $biller->invoice_footer ? '<span class="text-center">'.$this->cus->decode_html($biller->invoice_footer).'</span>' : ''; ?>
                </div>
                <div class="text-center hidden" style="border-top:2px dotted #000;border-bottom:2px dotted #000;font-size: 9px; padding-top: 5px;padding-bottom: 5px;font-weight: bold;">*ទំនាក់ទំនង ទិញម៉ាស៊ិនគិតលុយ: 093 471 106 / 089 217 000* <br>
                *Powered by: CLOUD ASEAN SOLUTION CO.,LTD. *

                </div>


            </div>

            <div style="clear:both;"></div>
        </div>

        <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">
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
    if( ! $modal) {
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