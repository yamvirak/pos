<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
    $max_row_limit = $this->config->item('form_max_row') -250;
    $font_size = $this->config->item('font_size');
    $td_line_height = $font_size + 15;
    $min_height = $font_size * 6; 
    $margin = $font_size - 5;
    $margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg">
    <div class="no-print" style="height:15px;"></div>
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
    <table>
        <thead style="display:table-header-group !important;">
            
            <tr>
                    <th>
                        <table style="margin-top: 5px;">
                            <tr>
                                <td class="text_left" style="width:15%">
                                     <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                </td>
                                <td></td>
                                 <td class="text_center" style="width:70%">
                                    <div>
                                        <strong style="font-size:18px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:18px";><?= $biller->name;?></strong>
                                    </div>
                                    <div class="font_address"><?= $biller->address?></div>
                                    <div class="font_address"><?= lang('tel').' : '. $biller->phone ?></div> 
                                    <div class="font_address"><?= lang('email').' : '. $biller->email ?></div>   
                                </td> 
                                <td class="text_center" style="width:15%">
                                    <?= $this->cus->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>
                                </td>
                            </tr>
                        </table>
                    </th>
                </tr>
                <tr>
                    <th>
                        <table>
                            <tr>
                                <td valign="bottom" style="width:50%"><hr class="hr_title"></td>
                                <?php if ($inv->sale_status=='draft') { ?>
                                    <td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('openning_balance') ?></i></b></span></td>
                                <?php }else if ($payment && $payment->paid != 0) { ?>
                                    <td class="text_center"><b style="font-family: Khmer OS Muol Light;"><?= lang('delivery_note_kh') ?></b>/<b><?= lang('delivery_note') ?></b></td>
                                <?php }else{ ?>
                                    <td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b style="font-family: Khmer OS Muol Light;"><?= lang('delivery_note_kh') ?></b>/<b><?= lang('delivery_note') ?></b></span></td>
                                <?php } ?>
                                
                                <td valign="bottom" style="width:14%"><hr class="hr_title"></td>
                            </tr>
                        </table>
                    </th>
                </tr>
            <tr>
                    <th>
                        <table>
                            <tr>
                                <td style="width:60%">
                                    <fieldset>
                                        <legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('inv_customer_info') ?></i></b></legend>
                                        <table>
                                            <tr>
                                                <td style="width: 150px;"><?= lang('inv_customer') ?></td>
                                                <td style="text-transform: uppercase;"> : <strong><?= $customer->company.'&nbsp;('.$customer->name;?>)</strong>

                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('inv_phone') ?></td>
                                                <td> : <?= $customer->phone ?></td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: top;"><?= lang('inv_address') ?></td>
                                                <td style="font-size: 12px;"> : <?= $this->cus->remove_tag($customer->address) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="height:22px;"></td>
                                            </tr>
                                            <?php if($inv->vehicle_model){ ?>
                                                <tr>
                                                    <td><?= lang('vehicle_model') ?></td>
                                                    <td style="text-align:left"> : 
                                                        <?= $inv->vehicle_model ?>
                                                    </td>
                                                </tr>
                                            <?php } if($inv->vehicle_plate){ ?>
                                                <tr>
                                                    <td><?= lang('vehicle_plate') ?></td>
                                                    <td style="text-align:left"> : 
                                                        <?= $inv->vehicle_plate ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </table>
                                    </fieldset>
                                </td>
                                <td style="width:40%">
                                    <fieldset style="margin-left:5px !important">
                                        <legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('inv_reference') ?></i></b></legend>
                                        <table>
                                            <tr>
                                                <td><?= lang('inv_sale') ?></td>
                                                <td style="text-align:left"> : <b><?= $inv->reference_no ?></b></td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('inv_date') ?></td>
                                                <td style="text-align:left"> : <?= $this->cus->hrsd($inv->date) ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('inv_po') ?></td>
                                                <td style="text-align:left"> : <?= $inv->si_reference_no ?></td>
                                            </tr>

                                            <tr>
                                                <td><?= lang('inv_user') ?></td>
                                                <td style="text-align:left"> : 
                                                        <?= $created_by->last_name."&nbsp;".$created_by->first_name; ?>
                                                    </td>
                                            </tr>
                                            <?php if($inv->saleman_ids){ ?>
                                            <tr>
                                                <td><?= lang('inv_saleman') ?></td>
                                                <td style="text-align:left"> : 
                                                    <?php 
                                                        if($inv->saleman_name == ""){
                                                            echo "N/A";
                                                        }else{
                                                             echo $inv->saleman_name;
                                                        }
                                                        ?>
                                                   
                                                </td>
                                            </tr>
                                          
                                            <?php } if($inv->vehicle_wing_no){ ?>
                                                <tr>
                                                    <td><?= lang('vehicle_vin') ?></td>
                                                    <td style="text-align:left"> : 
                                                        <?= $inv->vehicle_wing_no ?>
                                                    </td>
                                                </tr>
                                            <?php } if($inv->mechanic){ ?>
                                                <tr>
                                                    <td><?= lang('mechanic') ?></td>
                                                    <td style="text-align:left"> : 
                                                        <?= $inv->mechanic ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            
                                        </table>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                        
                        
                    </th>
                </tr>
        </thead>
        <tbody>
            <?php
                $inv_currencies = json_decode($inv->currencies);
                if($inv_currencies){
                    $currencies = false;
                    foreach($inv_currencies as $currency){
                        $currencies[$currency->currency] = $currency;
                    }
                    $currency = $currencies['KHR'];
                }
            
                $tbody = '';
                    $i=1;
                    foreach ($rows as $row){
                        if ($inv->product_discount != 0) {
                            $td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<casll>(' . $row->discount . ')</casll> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
                        }else{
                            $td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<casll>(' . $row->discount . ')</casll> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
                        }
                        if($row->room_rent==1){
                            $lroom_rent = '&nbsp; ['.$row->start_number.' - '.$row->end_number.']';
                        }else{
                            $lroom_rent = '';
                        }
                        $tbody .='<tr style="vertical-align: top;">
                                        <td class="text_center">'.$i.'</td>
                                        <td class="text_left">
                                            '.$row->product_code.'
                                        </td>
                                        <td class="text_left">
                                            '.$row->product_name.'
                                            '.$this->cus->decode_html($row->details).'
                                            '.($row->comment ? '<br>' . nl2br($row->comment) : '').'
                                            '.($row->serial_no ? '<br>' . $row->serial_no : '').'
                                            '.($row->bom_type ? '<br>' . $row->bom_type : '').'
                                            '.$lroom_rent.'
                                        </td>
                                        <td class="text_right">'.$this->cus->convertQty($row->product_id,$row->quantity).'</td>
                                       
                                    </tr>';     
                        $i++;
                    }
                
                
                ?>
            <tr>
                <td>
                    <table class="table_item_main">
                        <thead>
                            <tr>
                                <th class="text-center" width="40"><?=lang("ល.រ<br>No.");?></th>
                                <th class="text-center" width="200"><?=lang("លេខកូដ<br>Code");?></th>
                                    <th class="text-center"><?=lang("បរិយាយ <br> Description");?></th>
                                    <th class="text-center" width="80"><?=lang("ចំនួន<br>Qty");?></th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                            <?= $tbody ?>
                        </tbody>
                    </table>
                    
                </td>
            </tr>
        </tbody>
        <tfoot>
                    <tr class="tr_print bold">
                        <td>
                            <table style="margin-top:<?= $margin_signature ?>px;">
                                <tr>
                                    <td class="text_center" style="width:30%"><?= lang("buyer_by") ?></td>
                                    <td class="text_center" style="width:30%"><?= lang("inv_delivery")?></td>
                                    <td class="text_center" style="width:30%"><?= lang("inv_prepared") ?></td>
                                </tr>
                                <tr>
                                    <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                    <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                    <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                </tr>
                                <tr>
                                    <td class="text_center" style="width:30%">ឈ្មោះ <?= $customer->company;?></td>
                                    <td class="text_center" style="width:30%">ឈ្មោះ ...............................</td>
                                    <td class="text_center" style="width:30%">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>

            <tfoot>
    </table>
    
    <div class="clearfix"></div>
    
    <div class="buttons" style="margin-top:15px; margin-bottom:15px">
        <div class="btn-group btn-group-justified">
            <div class="btn-group">
                <a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
                    <i class="fa fa-close"></i>
                    <span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
                </a>
            </div>
            <div class="btn-group">
                <a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
                    <i class="fa fa-print"></i>
                    <span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
                </a>
            </div>
            
        </div>
    </div>
    
    <div class="clearfix"></div>
    
</div>
<style>
    @media print{
        .no-print{
            display:none !important;
        }
        .tr_print{
            display:table-row !important;
        }
        .modal-dialog{
            <?= $hide_print ?>
        }
        .bg-text{
            display:block !important;
        }
        #myModal .main_content {
            display: none !important;
        }
        @page{
            margin: 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
        }
    }
    
    .tr_print{
        display:none;
    }
    #tbody .td_print{
        border:none !important;
        border-left:1px solid black !important;
        border-right:1px solid black !important;
        border-bottom:1px solid black !important;
    }
    .modal-dialog{
        background-color:white !important;
        padding-left:12px; !important;
        padding-right:12px; !important;
    }
    .hr_title{
        border:3px double #428BCD !important;
        margin-bottom:<?= $margin ?>px !important;
        margin-top:<?= $margin ?>px !important;
    }
    .table_item th{
        border:1px solid black !important;
        background-color : #428BCD !important;
        text-align:center !important;
        line-height:26px !important;
    }
    .table_item td{
        border:1px solid black;
        line-height:<?=$td_line_height?>px !important;
    }
    .footer_des[rowspan] {
      vertical-align: top !important;
      text-align: left !important;
      border:0px !important;
    }
    
    .text_center{
        text-align:center !important;
    }
    .text_left{
        text-align:left !important;
        padding-left:3px !important;
    }
    .text_right{
        text-align:right !important;
        padding-right:3px !important;
    }
    p{
        margin: 0px !important;
    }
    
    fieldset{
        -moz-border-radius: 9px !important;
        -webkit-border-radius: 15px !important;
        border-radius:9px !important;
        border:2px solid #428BCD !important;
        min-height:<?= $min_height ?>px !important;
        margin-bottom : <?= $margin ?>px !important;
        padding-left : <?= $margin ?>px !important;
        line-height: 22px;
    }

    legend{
        width: initial !important;
        margin-bottom: initial !important;
        border: initial !important;
    }
    
    table{
        width:100% !important;
        font-size:<?= $font_size ?>px !important;
        border-collapse: collapse !important;
    }
    
    .bg-text{
        opacity: 0.1;
        color:lightblack;
        font-size:100px;
        position:absolute;
        transform:rotate(300deg);
        -webkit-transform:rotate(300deg);
        display:none;
    }
</style>

<script type="text/javascript">
    
    $(document).ready( function() {
        window.onafterprint = function(){       
            $.ajax({
                url : site.base_url + "sales/add_print",
                dataType : "JSON",
                type : "GET",
                data : { 
                        transaction_id : <?= $inv->id ?>,
                        transaction : "Sale",
                        reference_no : "<?= $inv->reference_no ?>"
                    }
            });
        }
        
        window.addEventListener("beforeprint", function(event) { addTr();});
        function addTr(){
            $('.blank_tr').remove();
            var page_height = <?= $max_row_limit ?>;
            var form_height = $('.table_item').height()-0;
            if(form_height > page_height && (form_height - page_height) > 15){
                var pages = Math.ceil(form_height / page_height);
                page_height = (page_height - (15 * (pages + 1))) * pages;
            }
            var blank_height = page_height - form_height;
            if(blank_height > 0){
                var td_html = '<tr class="tr_print blank_tr">';
                    td_html +='<td class="td_print"><div style="height:'+blank_height+'px !important">&nbsp;</div></td>';
                    td_html +='<td class="td_print">&nbsp;</td>';
                    td_html +='<td class="td_print">&nbsp;</td>';
                    td_html +='<td class="td_print">&nbsp;</td></tr>';
                $('#tbody').append(td_html);
            }
        }
    });

</script>