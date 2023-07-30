<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
    $max_row_limit = $this->config->item('form_max_row') -180;
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
		?>	
		<table>
			<thead>
				<tr>
					<th>
						<table style="margin-top: 5px;">
                            <tr>
                                <td class="text_left">
                                     <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                </td>
                                <td></td>
                                 <td class="text_center" style="width:60%">
                                    	<div>
                                            <strong style="font-size:<?= $font_size_company ?>px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                            <strong style="font-size:<?= $font_size_name ?>px;"><?= $biller->name;?></strong>
                                        </div>
                                    <br>
                                    <?php if($biller->vat_no!="" || $biller->vat_no != NULL){ 
                                    $vat_no = str_split($biller->vat_no);
                                    ?>
                                        <div style="margin-bottom:4px;" class="hidden">
                                            <?= lang("លេខអត្តសញ្ញាណកម្ម អតប (VATTIN)") ?> :
                                            <?php 
                                                foreach($vat_no as $v){
                                                    if($v == "-"){
                                                        echo "<span style='padding:3px 5px; margin:0 1px;'>".$v."</span>";
                                                    }else{
                                                        echo "<span style='border:1px solid #999; padding:3px 5px; margin:0 1px;'>".$v."</span>";
                                                    }
                                                }
                                            ?>
                                        </div>
                                    <?php } ?>
                                    <div class="font_address"><span><?= lang("inv_address") ?> : </span><?= $biller->address?></div>
                                    <div class="font_address"><?= lang('inv_phone').' : '. $biller->phone ?></div> 
                                    <div class="font_address"><?= lang('inv_email').' : '. $biller->email ?></div>   
                                </td> 
                                <td class="text_center" style="width:20%">
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
                                    <td valign="bottom" style="width:60%"><hr class="hr_title"></td>
                                    <?php if($inv->purchase_id){ ?>
                                            <td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('purchase_return') ?></i></b></span></td>
                                    <?php }else if($inv->status=="draft"){ ?>
                                            <td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('openning_balance') ?></i></b></span></td>
                                    <?php }else {?>
                                            <td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('purchase_invoice') ?></i></b></span></td>
                                    <?php } ?>
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
                                            <legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('supplier') ?></i></b></legend>
                                            <table>
                                                <tr>
                                                    <td><?= lang('name') ?></td>
                                                    <td> : <strong><?= $supplier->company ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td><?= lang('address') ?></td>
                                                    <td> : <?= $supplier->address ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= lang('tel') ?></td>
                                                    <td> : <?= $supplier->phone ?></td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </td>
                                    <td style="width:40%">
                                        <fieldset style="margin-left:5px !important">
                                            <legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
                                            <table>
                                                <?php 
                                                    $purchase_link = ''; 
                                                    if($inv->purchase_id){ 
                                                        $purchase_link= ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('purchases/modal_view/'.$inv->purchase_id).'"><i class="fa fa-external-link no-print"></i></a><br>';?>
                                                        <tr>
                                                            <td><?= lang('return_ref') ?></td>
                                                            <td style="text-align:left"> : <b><?= $inv->return_purchase_ref ?></b></td>
                                                        </tr>
                                                <?php } ?>
                                                
                                                <tr>
                                                    <td><?= lang('ref') ?></td>
                                                    <td style="text-align:left"> : <b><?= $inv->reference_no.$purchase_link ?></b></td>
                                                </tr>
                                                <tr>
                                                    <td><?= lang('date') ?></td>
                                                    <td style="text-align:left"> : <?= $this->cus->hrsd($inv->date) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= lang('created_by') ?></td>
                                                    <td style="text-align:left"> : <?= $created_by->first_name.'&nbsp;'.$created_by->last_name; ?></td>
                                                </tr>
                                                
                                                <?php if ($inv->si_reference_no){ ?>
                                                    <tr>
                                                        <td><?= lang('si_reference_no') ?></td>
                                                        <td style="text-align:left"> : <b><?= $inv->si_reference_no ?></b></td>
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
                        $tbody = '';
                        $i=1;
                        foreach ($rows as $row){
                            

                            if ($inv->product_discount != 0) {
	                            $td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
	                        }else{
	                            $td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
	                        }

                            
                            if($Settings->cbm==1){
                                $td_cbm = '<td class="text_right">'.$this->cus->formatDecimal($row->total_cbm).'</td>';
                            }else{
                                $td_cbm = '';
                            }

                            $tbody .='<tr>
                                            <td class="text_center">'.$i.'</td>
                                            <td class="text_center">'.$row->product_code.'</td>
                                            <td class="text_left">
                                                '.$row->product_name . ($row->variant ? '' . $row->variant . ')' : '').'
                                                '.($row->details ? '(' . $row->details : '').'
                                                '.($row->serial_no ? '<br>' . $row->serial_no : '').'
                                               
                                            </td>
                                           
                                            <td class="text_right">'.$this->cus->convertQty($row->product_id,$row->quantity).'</td>
                                            '.$td_cbm.'
                                            <td class="text_right">'.$this->cus->formatMoney($row->unit_cost).'</td>
                                            '.$td_discount.'
                                            <td class="text_right">'.$this->cus->formatMoney($row->subtotal).'</td>
                                        </tr>';     
                            $i++;
                        }
                        
                        $footer_colspan = 5;
                        $footer_rowspan = 1;
                        if($inv->product_discount != 0){
                            $footer_colspan++;
                        }
                        if($Settings->cbm == 1){
                            $footer_colspan++;
                        }
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
                        if($payment){
                            if ($payment->paid != 0) {
                                $footer_rowspan++;
                            }
                            if ($payment->discount != 0) {
                                $footer_rowspan++;
                            }
                        }
                            
                        
                        
                        $amount_balance = $inv->grand_total - ($payment?($payment->paid + $payment->discount):0);
                        if($amount_balance <> $inv->grand_total){
                            $footer_rowspan++;
                        }
                        
                        $tfooter = '';
                        $footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->cus->decode_html($inv->note).'</td>';
                        if ($inv->grand_total != $inv->total) {
                            $tfooter .= '<tr>
                                            '.$footer_note.'
                                            <td class="text_right"><b>'.lang("total").'</b></td>
                                            <td class="text_right"><b>'.$this->cus->formatMoney($inv->total).'</b></td>
                                        </tr>';
                            $footer_note = '';      
                        }
                        if ($inv->order_discount != 0) {
                            $tfooter .= '<tr>
                                            '.$footer_note.'
                                            <td class="text_right"><b>'.lang("order_discount").'</b></td>
                                            <td class="text_right"><b>'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->cus->formatMoney($inv->order_discount).'</b></td>
                                        </tr>';
                            $footer_note = '';      
                        }
                        if ($inv->order_tax  != 0) {
                            $tfooter .= '<tr>
                                            '.$footer_note.'
                                            <td class="text_right"><b>'.lang("order_tax").'</b></td>
                                            <td class="text_right"><b>'.$this->cus->formatMoney($inv->order_tax).'</b></td>
                                        </tr>';
                            $footer_note = '';      
                        }
                        if ($inv->shipping  != 0) {
                            $tfooter .= '<tr>
                                            '.$footer_note.'
                                            <td class="text_right"><b>'.lang("shipping").'</b></td>
                                            <td class="text_right"><b>'.$this->cus->formatMoney($inv->shipping).'</b></td>
                                        </tr>';
                            $footer_note = '';      
                        }
                        $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right"><b>'.lang("grand_total").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($inv->grand_total).'</b></td>
                                    </tr>';
                            $footer_note = '';      
                        if($payment){
                            if ($payment->paid != 0) {
                                $tfooter .= '<tr>
                                                <td class="text_right"><b>'.lang("paid").'</b></td>
                                                <td class="text_right"><b>'.$this->cus->formatMoney($payment->paid).'</b></td>
                                            </tr>';
                                $footer_note = '';      
                            }   
                            if ($payment->discount != 0) {
                                $tfooter .= '<tr>
                                                <td class="text_right"><b>'.lang("discount").'</b></td>
                                                <td class="text_right"><b>'.$this->cus->formatMoney($payment->discount).'</b></td>
                                            </tr>';
                                $footer_note = '';      
                            }
                        }   
                        
                        if($amount_balance <> $inv->grand_total){
                            $tfooter .= '<tr>
                                            <td class="text_right"><b>'.lang("balance").'</b></td>
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
	                                    <th class="text-center"><?=lang("កូដទំនិញ <br> Product Code");?></th>
	                                    <th class="text-center"><?=lang("បរិយាយទំនិញ <br> Description");?></th>
	                                    <th class="text-center" width="80"><?=lang("ចំនួន<br>Quantity");?></th>
	                                    <th class="text-center" width="100"><?=lang("ថ្លៃឯកតា<br>Unit Price");?></th>
	                                    <th class="text-center" width="80"><?=lang("បញ្ចុះតំលៃ<br>Discount");?></th>
	                                    <th class="text-center" width="120"><?=lang("សរុប<br>Amount");?></th>
                                	</tr>

                                    
                                </thead>
                                <tbody id="tbody">
                                    <?= $tbody ?>
                                </tbody>
                                <tbody id="tfooter">
                                    <?= $tfooter ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
			<tfoot>
                   <tr class="tr_print">
	                <td>
	                    <table style="margin-top:<?= $margin_signature ?>px; margin-bottom:<?= $margin_signature - 20 ?>px;">
	                        <thead class="footer_item">
	                            <th class="text_center"><?= lang("prepared_by");?></th>
	                            <th class="text_center"><?= lang("checked_by");?></th>
	                            <th class="text_center"><?= lang("approved_by");?></th>
	                            <th class="text_center"><?= lang("received_by") ?></th>
	                        </thead>
	                        <tbody class="footer_item_body">
	                            <td class="footer_item_body"></td>
	                            <td class="footer_item_body"></td>
	                            <td class="footer_item_body"></td>
	                            <td class="footer_item_body"></td>
	                        </tbody>
	                        <thead class="footer_item_footer">
	                            <th class="footer_item_footer text_left">Name:</th>
	                            <th class="footer_item_footer text_left">Name:</th>
	                            <th class="footer_item_footer text_left">Name:</th>
	                            <th class="footer_item_footer text_left">Name:</th>
	                        </thead>
	                    </table>
	                </td>
	            </tr>

            <tfoot>
		</table>
	<div class="clearfix"></div>

	<div class="buttons" style="margin-top:20px; margin-bottom:20px">
                <hr>
                <div class="btn-group btn-group-justified">
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
                        <?php if ($inv->attachment) { ?>
                            <div class="btn-group">
                                <a href="<?= site_url('assets/uploads/' . $inv->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
                                    <i class="fa fa-download"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                                </a>
                            </div>
                        <?php }if($inv->status=='draft' || $inv->status=='freight'){ ?>
                            <div class="btn-group"> 
                                <a href="<?= site_url('purchases/add_payment/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('add_payment') ?>">
                                    <i class="fa fa-dollar"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                                </a>
                            </div>
                            
                        <?php } else if($inv->purchase_id){ ?>
                            <div class="btn-group"> 
                                <a href="<?= site_url('purchases/add_payment_return/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('add_payment') ?>">
                                    <i class="fa fa-dollar"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                                </a>
                            </div>
                        <?php } else { ?>
                            <div class="btn-group"> 
                                <a href="<?= site_url('purchases/add_payment/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('add_payment') ?>">
                                    <i class="fa fa-dollar"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                                </a>
                            </div>
                        <?php } ?>
                        <div class="btn-group">
                            <a href="<?= site_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('purchases/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <?php  if(!$inv->purchase_id && $inv->status!="draft"){ ?>
                            <div class="btn-group">
                                <a href="<?= site_url('purchases/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                    <i class="fa fa-edit"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                                    data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('purchases/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                    data-html="true" data-placement="top">
                                    <i class="fa fa-trash-o"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                                </a>
                            </div>
                        <?php } ?>
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
    .hr_title{
        border:3px double #428BCD !important;
        margin-bottom:<?= $margin ?>px !important;
        margin-top:<?= $margin ?>px !important;
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
        window.onafterprint = function(){       
            $.ajax({
                url : site.base_url + "sales/add_print",
                dataType : "JSON",
                type : "GET",
                data : { 
                        transaction_id : <?= $inv->id ?>,
                        transaction : "Purchase",
                        reference_no : "<?= ($inv->purchase_id ? $inv->return_purchase_ref : $inv->reference_no )?>"
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
                    td_html +='<td class="td_print">&nbsp;</td>';
                    <?php if ($inv->product_discount != 0) { ?>
                        td_html +='<td class="td_print">&nbsp;</td>';
                    <?php } if($Settings->cbm == 1) { ?>
                        td_html +='<td class="td_print">&nbsp;</td>';
                    <?php } ?>
                    td_html +='<td class="td_print">&nbsp;</td></tr>';
                $('#tbody').append(td_html);
            }

        }
        
    });
    
</script>

