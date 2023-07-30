<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
    $max_row_limit = $this->config->item('form_max_row') -110;
    $font_size = $this->config->item('font_size');
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
        ?>  
        <table>
            <thead>
                <tr>
                    <th>
                        <table style="margin-top: 10px;">
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
                                <td class="text_center" style="width:20%">
                                        <?= $this->cus->qrcode('link', urlencode(site_url('products/modal_view_consignment/' . $consignment->id)), 2); ?>
                                </td>
                            </tr>
                        </table>
                    </th>
                </tr>
                <tr>
                    <th>
                        <table>
                            <tr>
                                <td valign="bottom" style="width:35%"><hr class="hr_title"></td>
                                    <td class="text_center" style="width:20%"><span class="inv_invoice" style="font-size:<?= $font_size+5 ?>px"><?= ($consignment->status == 'returned' ? lang('consignment_return') : lang('consignment'))  ?></span></td>
                                <td valign="bottom" style="width:15%"><hr class="hr_title"></td>
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
                                                <td style="vertical-align:top; "><?= lang('inv_address') ?></td>
                                                <td style="font-size:12px;height:43px;vertical-align:top;"> : <?= $this->cus->remove_tag($customer->address) ?></td>
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
                                                    <td><?= lang('inv_reference') ?></td>
                                                    <td style="text-align:left"> : <b><?= $consignment->reference_no ?></b></td>
                                                </tr>
                                                <tr>
                                                    <td><?= lang('inv_date') ?></td>
                                                    <td style="text-align:left"> : <?= $this->cus->hrsd($consignment->date) ?></td>
                                                </tr>
                                                <tr>
                                                <td><?= lang('inv_user') ?></td>
                                                    <td style="text-align:left"> : 
                                                        <?= $created_by->last_name."&nbsp;".$created_by->first_name; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?= lang('inv_valid_day') ?></td>
                                                    <td style="text-align:left"> : <?= $consignment->valid_day ?> <?= lang('days') ?></td>
                                                </tr>
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
                        $tbody = '';
                        $i=1;
                        foreach ($rows as $row){
                            $tbody .='<tr>
                                            <td class="text_center">'.$i.'</td>
                                            <td class="text_center">'.$row->product_code.'</td>
                                            <td class="text_left">'.$row->product_name.'</td>
                                            <td class="text_center">'.$this->cus->formatQuantity($row->unit_quantity).'</td>
                                            <td class="text_right">'.$this->cus->formatMoney($row->unit_price).'</td>
                                            <td class="text_right">'.$this->cus->formatMoney($row->subtotal).'</td>
                                        </tr>';     
                            $i++;
                        }
                        
                        $footer_colspan = 3;
                        $footer_rowspan = 1;


                        $tfooter = '';
                        $footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->cus->decode_html($consignment->note).'</td>';
                        $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right" colspan="2"><b>'.lang("inv_grand_total_usd").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($consignment->grand_total).'</b></td>
                                    </tr>';
                ?>
                    <tr>
                        <td>
                            <table class="table_item_main">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="30"><?=lang("ល.រ<br>No.");?></th>
                                        <th class="text-center"><?=lang("កូដ <br> Code");?></th>
                                        <th class="text-center"><?=lang("បរិយាយ <br> Description");?></th>
                                        <th class="text-center" width="80"><?=lang("ចំនួន <br> Quantity");?></th>
                                        <th class="text-center" width="80"><?=lang("តម្លៃ​ឯកតា <br> Unit Price");?></th>
                                        <th class="text-center" width="80"><?=lang("ចំនួនសរុប <br> Subtotal");?></th>
                                        <?php if($Settings->product_expiry=='1'){ ?>
                                            <th><?= lang("expiry_date"); ?></th>
                                        <?php } ?>
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
                <tr class="tr_print bold">
                    <td>
                        <table style="margin-top:<?= $margin_signature ?>px;">
                            <tr>
                                <td class="text_center" style="width:30%"><?= lang("inv_customer") ?></td>
                                <td class="text_center" style="width:30%"><?= lang("inv_approved_by") ?></td>
                                <td class="text_center" style="width:30%"><?= lang("inv_prepared") ?></td>
                            </tr>
                            <tr>
                                <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                            </tr>
                            <tr>
                                <td class="text_center" style="width:30%">ឈ្មោះ <strong><?= $customer->company?></strong></td>
                                <td class="text_center" style="width:30%">ឈ្មោះ </td>
                                <td class="text_center" style="width:30%">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tfoot>
        </table>
    <div class="clearfix"></div>

    <div id="buttons" style="padding:10px 0px 10px 0px;" class="no-print">
                <hr>
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
                    <?php if ($consignment->attachment) { ?>
                        <div class="btn-group">
                            <a href="<?= site_url('assets/uploads/' . $consignment->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php } ?>
                    <?php if ($consignment->status!='completed') { ?>
                        <?php if ($consignment->status!='returned') { ?>
                            <div class="btn-group">
                                <a href="<?= site_url('products/edit_consignment/' . $consignment->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                    <i class="fa fa-edit"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                </a>
                            </div>
                        <?php } ?>  
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('products/delete_consignment/' . $consignment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
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
        .ti_print{
           display:block !important;
        }
        .modal-dialog{
            <?= $hide_print ?>
        }
        .bg-text{
            display:block !important;
        }
        @page{
            margin: 5mm 5mm 5mm 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
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

    .table_item_main th{
        border:1px solid black !important;
        background-color : #ddd !important;
        text-align:center !important;
        line-height: 14px !important;
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
    p{
        margin: 0px !important;
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
    .invoice_inv{
        font-size: 18px;
        font-weight: bold;
        font-family: Khmer OS Muol Light!important;
    }
   .full_stop{
        text-align: center;
    }
           
</style>

<script type="text/javascript">
    $(document).ready( function() {
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
                    if(site.settings.product_expiry == 1){
                        td_html +='<td class="td_print">&nbsp;</td>';
                    }
                    td_html +='<td class="td_print">&nbsp;</td></tr>';
                $('#tbody_main').append(td_html);
            }
        }
        
    });
    
</script>

