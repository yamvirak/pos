<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
    $max_row_limit = $this->config->item('form_max_row') -130;
    $font_size = $this->config->item('font_size');
    $font_size_company = $this->config->item('font_size_company');
    $font_size_name = $this->config->item('font_size_name');
    $td_line_height = $font_size + 15;
    $min_height = $font_size * 6; 
    $margin = $font_size - 5;
    $margin_signature = $font_size * 5;

    $currency = $this->db->where("code !=","USD")->get('currencies')->row();
    $kh_rate = $currency->rate;

    if($inv->sale_type_name=='Cash'){
        $inv->sale_type_name = 'សាច់ប្រាក់';
    }
    elseif($inv->sale_type_name=='Deposit'){
        $inv->sale_type_name = 'បង់ដំណាក់កាល';
    }else{
        $inv->sale_type_name = 'បង់រំលស់';
    }
?>


<div class="modal-dialog modal-lg main_content">
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

        $deposit_amount = 0;
        if($deposits){
            foreach($deposits as $deposit){
                $deposit_amount += $deposit->amount;
            }
        }
        ?>  


        <table>

            <thead>
                <tr>
                    <th>
                        <table style="margin-top: 5px;">
                            <tr>
                                
                                 <td class="text_left" style="width:60%">
                                    <div>
                                            <strong style="font-size:<?= $font_size_company ?>px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                            <strong style="font-size:<?= $font_size_name ?>px;"><?= $biller->name;?></strong>
                                    </div>
                                    <div class="font_address"><span><?= lang("biller_address") ?> : </span><?= $biller->address?></div>
                                    <div class="font_address"><?= lang('biller_phone').' : '. $biller->phone ?></div> 
                                    <div class="font_address"><?= lang('biller_email').' : '. $biller->email ?></div> 
                                    <div class="font_address"><?= lang('biller_facebook').' : '. $biller->cf3 ?></div> 

                                </td> 
                                <td class="text_center" style="width:20%">
                                    <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
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
                                    <td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px" class="inv_invoice_kh"> <?= lang('inv_invoice_kh') ?> </span><span class="inv_invoice">/ <?= lang('inv_invoice') ?></span></td>
                                <td valign="bottom" style="width:14%"><hr class="hr_title"></td>
                            </tr>
                        </table>
                    </th>
                </tr>
                <tr>
                   <td><div style="padding:5px !important;"></div></td>
                </tr>

                <tr>
                    <th>
                        <table>
                            <tr>
                                <td style="width:100%">
                                    <fieldset>
                                        <table border="1" class="table_item_head">
                                            
                                            <tr>
                                                <td style="width: 150px;"><?= lang('inv_bill') ?></td>
                                                <td style="width: 155px;"> <strong><?= $customer->name;?></strong>
                                                </td>
                                                <td style="width: 150px;"><?= lang('inv_reference')?></td>
                                                <td> <strong><?= $inv->reference_no ?></strong>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="width: 150px;"><?= lang('inv_checked_in') ?></td>
                                                <td style="text-transform: uppercase;"> <strong><?= $this->cus->hrld($inv->from_date)?></strong>
                                                </td>
                                                <td style="width:180px;"><?= lang('inv_deposit_date') ?> </td>
                                                <td style="text-transform: uppercase;"> <strong><?= $this->cus->hrld($deposit->date) ?></strong>
                                                </td>
                                                
                                            </tr>
                                            <tr>
                                                <td style="width: 180px;"><?= lang('inv_checked_out') ?></td>
                                                <td style="text-transform: uppercase;"> <strong><?= $this->cus->hrld($inv->to_date) ?></strong>
                                                </td>
                                                
                                                <td style="width: 180px;"><?= lang('inv_deposit_amount') ?></td>
                                                <td style="text-transform: uppercase;"> <strong>
                                                    <?= $this->cus->formatMoney($deposit->amount)?>
                                                </strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 180px;"><?= lang('inv_period') ?></td>
                                                <td style="text-transform: uppercase;"> <strong><?= $inv->frequency ?> <?= lang('(យប់)') ?></strong>
                                                </td>
                                                
                                                <td style="width: 180px;"><?= lang('inv_phone') ?></td>
                                                <td style="text-transform: uppercase;"> <strong>
                                                    <?= $customer->phone;?>
                                                </strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                    </th>
                </tr>
                <tr>
                   <td><div style="padding:5px !important;"></div></td>
                </tr>
            </thead>
            <tbody>
                <?php
                    $tbody = '';
                    $i=1;
                    foreach ($rows as $row){
                        if ($inv->product_discount != 0) {
                            $td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<strong>(' . $row->discount . ')</strong> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
                        }else{
                            $td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<strong>(' . $row->discount . ')</strong> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
                        }
                        if($row->room_rent==1){
                            $lroom_rent = '&nbsp; ['.$row->start_number.' - '.$row->end_number.']';
                        }else{
                            $lroom_rent = '';
                        }

                        
                        $tbody .='<tr style="vertical-align: top;">
                                        <td class="text_center">'.$i.'</td>
                                        <td class="text_left">
                                            '.$this->cus->remove_tag($row->product_name).'
                                            '.'['.$this->cus->remove_tag($row->details).']'.'
                                            '.($row->serial_no ? '<br>' . $row->serial_no : '').'
                                            '.($row->bom_type ? '<br>' . $row->bom_type : '').'
                                            '.$lroom_rent.'
                                        </td>
                                        <td class="text_center">'.$this->cus->formatQuantity($row->quantity).'</td>
                                        <td class="text_right">'.$this->cus->formatMoney($row->real_unit_price).'</td>
                                        '.$td_discount.'
                                        <td class="text_right">'.$this->cus->formatMoney($row->subtotal).'</td>
                                    </tr>';     
                        $i++;
                    }
                    
                    $footer_colspan = 3;
                    $footer_rowspan = 1;
                    // if($inv->product_discount != 0){
                    //     $footer_colspan++;
                    // }
                    if($inv->grand_total != $inv->total){
                        $footer_rowspan++;
                    }
                    if($inv->order_discount != 0){
                        $footer_rowspan++;
                    }
                    if($inv->order_tax != 0){
                        $footer_rowspan++;
                    }
                    if($inv->shipping != 0){
                        $footer_rowspan++;
                    }
                    
                    if ($inv->paid <= $inv->grand_total) {
                        $footer_rowspan++;
                    }
                    if ($payment && $payment->discount != 0) {
                        $footer_rowspan++;
                    }   
                    
                    $amount_balance = (($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($deposit->amount+$return_sale->paid) : $deposit->amount));

                    if($amount_balance <= $inv->grand_total){
                        $footer_rowspan++;
                    }
                    
                    $tfooter = '';

                    $footer_note = '<td style="font-size:11px;" class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->cus->decode_html($biller->invoice_footer).'</td>';
                    if ($inv->grand_total != $inv->total) {
                        $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right" colspan="2"><b>'.lang("inv_total").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($inv->total).'</b></td>
                                    </tr>';
                        $footer_note = '';      
                    }
                    if ($inv->order_discount != 0) {
                        $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right" colspan="2"><b>'.lang("inv_order_discount").'</b></td>
                                        <td class="text_right"><b>'.($inv->order_discount_id ? '<casll>('.$inv->order_discount_id.')</casll> ' : '') . $this->cus->formatMoney($inv->order_discount).'</b></td>
                                    </tr>';
                        $footer_note = '';      
                    }
                    if ($inv->order_tax  != 0) {
                        $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right" colspan="2"><b>'.lang("order_tax").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($inv->order_tax).'</b></td>
                                    </tr>';
                        $footer_note = '';      
                    }
                    if ($inv->shipping  != 0) {
                        $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right" colspan="2"><b>'.lang("shipping").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($inv->shipping).'</b></td>
                                    </tr>';
                        $footer_note = '';      
                    }

                    $tfooter .= '<tr>
                                    '.$footer_note.'
                                    <td class="text_right" colspan="2"><b>'.lang("inv_grand_total").'</b></td>
                                    <td class="text_right"><b>'.$this->cus->formatMoney($inv->grand_total).'</b></td>
                                </tr>';
                        $footer_note = '';
                   
                        if ($deposit->amount <= $inv->grand_total) {
                      
                            $tfooter .= '<tr>
                                            <td class="text_right" colspan="2"><b>'.lang("inv_paid").'</b></td>
                                            <td class="text_right"><b>'.$this->cus->formatMoney($return_sale ? ($deposit->amount+$return_sale->paid) : $deposit->amount).'</b></td>



                                        </tr>';
                            $footer_note = '';      
                        }   
                        if ($payment->discount != 0) {
                            $tfooter .= '<tr>
                                            <td class="text_right" colspan="2"><b>'.lang("discount").'</b></td>
                                            <td class="text_right"><b>'.$this->cus->formatMoney($payment->discount).'</b></td>
                                        </tr>';
                            $footer_note = '';      
                        }
                      
                  
                    if($amount_balance <= $inv->grand_total){
                        $tfooter .= '<tr>
                                        <td class="text_right" colspan="2"><b>'.lang("inv_balance").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($amount_balance).'</b></td>
                                    </tr>';
                        $footer_note = '';      
                    }
                ?>
                <tr>
                    <td>
                        <table class="table_item_main">

                            <thead>
                                <tr>
                                    <th class="text-center" width="40"><?=lang("ល.រ<br>No.");?></th>
                                    <th class="text-center"><?=lang("ប្រភេទផ្ទះ <br> Description");?></th>
                                    <th class="text-center" width="80"><?=lang("ចំនួនយប់<br>Quantity");?></th>
                                    <th class="text-center" width="100"><?=lang("ថ្លៃឯកតា<br>Unit Price");?></th>
                                    <th class="text-center" width="80"><?=lang("បញ្ចុះតំលៃ<br>Discount");?></th>
                                    <th class="text-center" width="100"><?=lang("សរុប<br>Amount");?></th>
                                </tr>
                            </thead>
                            <tbody id="tbody_main">
                                <?= $tbody ?>
                            </tbody>
                            <tbody id="tfooter">
                                <?= $tfooter ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                
                <?php if($this->config->item("room_rent") && $inv->sale_type=='room_rent'){ ?>
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td style="width:40%">
                                    <fieldset>
                                        <legend style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('room_rent') ?></i></b></legend>
                                            <table width="50%" style="font-weight:bold;">                   
                                                <tr>
                                                    <td><?= lang('start_date') ?></td>
                                                    <td style="text-align:left"> : <?= $this->cus->hrld($inv->start_date) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= lang('end_date') ?></td>
                                                    <td style="text-align:left"> : <?= $this->cus->hrld($inv->end_date) ?></td>
                                                </tr>
                                            </table>
                                    </fieldset>
                                </td>
                                <td></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php } ?>
            
            </tbody>
                 <tfoot>
                    <tr class="tr_print">
                        <td>
                            <table style="margin-top:40px;">
                                <tr>
                                    <td class="text_center" style="width:50%"><?= lang("ហត្ថលេខា និងឈ្មោះអតិថិជន") .'<br>'. lang("Customer's Signature & Name") ?></td>
                                    <td class="text_center" style="width:50%"><?= lang("ហត្ថលេខា និងឈ្មោះអ្នកលក់").'<br>'. lang("Seller's Signature & Name") ?></td>
                                </tr>
                                <tr>
                                    <td class="text_center" style="width:50%; padding-top:80px">______________________</td>
                                    <td class="text_center" style="width:50%; padding-top:80px">______________________</td>
                                </tr>
                                <tr>
                                    <td class="text_center" style="width:50%">ឈ្មោះ <?= $customer->name;?></td>
                                    <td class="text_center" style="width:50%">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
        </table>
    <div class="clearfix"></div>
    
    <div class="buttons" style="margin-top:20px; margin-bottom:20px">
        <div class="btn-group btn-group-justified">
        
            <div class="btn-group">
                <a href="<?= site_url("rentals") ?>" data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
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
            <?php if($Settings->installment==1){ ?>
            <div class="btn-group hidden">
                <a href="<?= site_url('sales/modal_view_agreement/' . $inv->id) ?>" class="tip btn btn-success" title="<?= lang('agreement') ?>" data-toggle="modal" data-target="#myModal2" data-backdrop="static" data-keyboard="false">
                    <i class="fa fa-file-text-o"></i>
                        <span class="hidden-sm hidden-xs"><?= lang('agreement') ?></span>
                    </a>
            </div>
            <?php } ?>
            
           
           
            <div class="btn-group hidden">
                <a href="<?= site_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                    <i class="fa fa-download"></i>
                    <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                </a>
            </div>
            <?php if ($inv->attachment) { ?>
                <div class="btn-group">
                    <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                        <i class="fa fa-chain"></i>
                        <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                    </a>
                </div>
            <?php } ?>
            <?php if (!$inv->sale_id && $inv->sale_status!='draft') { ?>
                <div class="btn-group">
                    <a href="<?= site_url('rentals/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                        <i class="fa fa-edit"></i>
                        <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                    </a>
                </div>
                <div class="btn-group">
                    <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_rentals") ?></b>"
                        data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('rentals/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                        data-html="true" data-placement="top">
                        <i class="fa fa-trash-o"></i>
                        <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                    </a>
                </div>
            <?php } ?>
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
    }
    .tr_print{
        display:none;
    }
    #tbody_main .td_print{
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
    .table_item_main th{
        border:1px solid black !important;
        background-color : #ddd !important;
        text-align:center !important;
        line-height: 18px !important;
        padding: 5px;
    }
    .table_item_head td {
        border: 1px solid black !important;
        background-color: #fff040 !important;
        line-height: 15px !important;
        padding: 5px;
    }
    .table_item_main td{
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
    .font_address{
        font-size: 11px;
    }

    legend{
        width: initial !important;
        margin-bottom: initial !important;
        border: initial !important;
    }
    p{
        margin: 0 0 0px;
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
    .footer_name{
        margin-bottom: -55px;
        font-weight: bold;
        font-family: Khmer OS Muol Light;
        font-size: 12px;

    }

    .footer_item th{
        border:1px solid #000000 !important;
        background-color : #9996 !important;
        text-align:center !important;
        line-height:30px !important;
        width: 25%;
    }

    .footer_item_body{
        border:1px solid #000000 !important;
        line-height: 150px;
        padding-top: 130px !important;
    }
    .footer_item_footer{
        border:1px solid #000000 !important;
        text-align:left !important;
   }         
    .inv_invoice{
        font-size: 18px;
        font-weight: bold;
    }
    .inv_invoice_kh{
        font-size: 18px;
        font-weight: bold;
        font-family: Khmer OS Muol Light!important;
    }

   .full_stop{
        text-align: center;
    }
    .cus_info{
        padding: 7px;
        font-size: 12px;
        font-weight: bold;
        text-align: left;
        border-radius: 10px;
        background-color: #dfdfdf !important;
        color: #262626;
        margin-bottom: 10px;
    }
    .cus_info_left{
        padding: 9px;
        font-size: 12px;
        font-weight: bold;
        text-align: left;
        border-radius: 10px 0px 0px 10px;
        background-color: #dfdfdf !important;
        color: #262626;
        margin-bottom: 10px;
    }
    .cus_info_right{
        padding: 9px;
        font-size: 12px;
        font-weight: bold;
        text-align: left;
        border-radius: 0px 10px 10px 00px;
        background-color: #dfdfdf !important;
        color: #262626;
        margin-bottom: 10px;
    }
    .name_receive {
        border: 1px solid #ffe130;
        padding: 7px;
        font-size: 14px;
        font-weight: bold;
        text-align: left;
        border-radius: 20px 0px 0px 20px;
        background-color: #ffe130 !important;
        color: #262626;
        margin-bottom: 10px;
}
           
</style>

<script type="text/javascript">

    $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
    });

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
            var form_height = $('.table_item_main').height()-0;
            
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
                    td_html +='<td class="td_print">&nbsp;</td>';
                    td_html +='<td class="td_print">&nbsp;</td>';
                    td_html +='<td class="td_print">&nbsp;</td></tr>';
                $('#tbody_main').append(td_html);
            }
        }
        
    });
    
</script>

