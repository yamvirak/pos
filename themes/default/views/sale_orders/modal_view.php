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
                                    <div class="hidden" style="padding-bottom:5px;padding-top:5px;"><?= lang("លេខអត្តសញ្ញាណកម្ម អតប (VATTIN)") ?> :
                                    
                                        <?php if($biller->vat_no!="" || $biller->vat_no != NULL){ 
                                        $vat_no = str_split($biller->vat_no);
                                        ?>
                                            <span style="margin-bottom:4px;">
                                                
                                                <?php 
                                                    foreach($vat_no as $v){
                                                        if($v == "-"){
                                                            echo "<span style='padding:0px 3px; margin:3 5px;'>".$v."</span>";
                                                        }else{
                                                            echo "<span style='border:1px solid #999; padding:3px 5px; margin:0 1px;'>".$v."</span>";
                                                        }
                                                    }
                                                ?>
                                            </span>
                                        <?php } ?>
                                    </div>  
                                </td> 
									<td class="text_center" style="width:20%">
										<?= $this->cus->qrcode('link', urlencode(site_url('sale_orders/view/' . $inv->id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
               		<tr>
	                    <th>
	                        <table>
	                            <tr>
	                                <td valign="bottom" style="width:30%"><hr class="hr_title"></td>
	                                    <td class="text_center" style="width:35%"><span style="font-size:<?= $font_size+5 ?>px" class="inv_invoice_kh"> <?= lang('inv_sale_order_kh') ?> </span><span class="inv_invoice">/ <?= lang('inv_sale_order') ?></span></td>
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
                                                <td><?= lang('inv_sale') ?></td>
                                                <td style="text-align:left"> : <b><?= $inv->reference_no ?></b></td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('inv_date') ?></td>
                                                <td style="text-align:left"> : <?= $this->cus->hrsd($inv->date) ?></td>
                                            </tr>
                                            <tr class="hidden">
                                                <td><?= lang('inv_po') ?></td>
                                                <td style="text-align:left"> : <?= $inv->si_reference_no ?></td>
                                            </tr>

                                            <tr>
                                                <td><?= lang('inv_user') ?></td>
                                                <td style="text-align:left"> : 
                                                        <?= $created_by->last_name."&nbsp;".$created_by->first_name; ?>
                                                    </td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('inv_phone') ?></td>
                                                <td style="text-align:left"> : 
                                                        <?= $created_by->phone ?>
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
								$td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->cus->formatMoney($row->item_discount) . '</td>';
							}else{
								$td_discount = '';
							}
							if($Settings->foc == 1){
								$foc = '<td class="text_center">'.$this->cus->formatQuantity($row->foc).'</td>';
							}else{
								$foc = '';
							}
							$tbody .='<tr>
											<td class="text_center">'.$i.'</td>
											<td class="text_center">'.$row->product_code.'</td>
											<td class="text_left">
												'.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '').'
												'.($row->details ? '<br>' . $row->details : '').'
												'.($row->comment ? '<br>' . nl2br($row->comment) : '').'
												'.($row->serial_no ? '<br>' . $row->serial_no : '').'
											</td>
											<td class="text_center">'.$this->cus->formatQuantity($row->unit_quantity).'</td>
											'.$foc.'
											<td class="text_right">'.$this->cus->formatMoney($row->unit_price).'</td>
											'.$td_discount.'
											<td class="text_right">'.$this->cus->formatMoney($row->subtotal).'</td>
										</tr>';		
							$i++;
						}
						
						$footer_colspan = 4;
						$footer_rowspan = 1;
						if($inv->product_discount != 0){
							$footer_colspan++;
						}
						if($Settings->foc == 1){
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
						
						if($deposit_payment && $deposit_payment->amount != 0){
							$footer_rowspan += 2;
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
									
						if($deposit_payment && $deposit_payment->amount != 0){
							$tfooter .= '<tr>
											<td class="text_right"><b>'.lang("deposit").'</b></td>
											<td class="text_right"><b>'.$this->cus->formatMoney($deposit_payment->amount).'</b></td>
										</tr>';
							$tfooter .= '<tr>
											<td class="text_right"><b>'.lang("balance").'</b></td>
											<td class="text_right"><b>'.$this->cus->formatMoney($inv->grand_total - $deposit_payment->amount).'</b></td>
										</tr>';
						}	
					?>
               <tr>
						<td>
							<table class="table_item_main">
								<thead>
									<tr>
										<th class="text-center" width="30"><?=lang("ល.រ<br>No.");?></th>
										<th><?= lang("code"); ?></th>
                                    	<th class="text-center"><?=lang("បរិយាយ <br> Description");?></th>
										<th class="text-center" width="50"><?=lang("ចំនួន<br>Qty");?></th>
										<?php if($Settings->foc == 1){ ?>
											<th><?= lang("foc"); ?></th>
										<?php } ?>
										<th class="text-center" width="80"><?=lang("តំលៃ<br>Price");?></th>
										<?php 
											if($inv->product_discount != 0){
												echo '<th>'.lang("បញ្ចុះតំលៃ<br>Dis.").'</th>';
											}
										?>
										<th class="text-center" width="120"><?=lang("សរុប<br>Amount");?></th>
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
                                <td class="text_center" style="width:30%">ឈ្មោះ <strong><?= $customer->company.'&nbsp;('.$customer->name;?>)</strong></td>
                                <td class="text_center" style="width:30%">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></td>
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
					<?php if ($inv->attachment) { ?>
						<div class="btn-group">
							<a href="<?= site_url('assets/uploads/' . $inv->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
								<i class="fa fa-download"></i>
								<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
							</a>
						</div>
					<?php } ?>
					<div class="btn-group">
						<a href="<?= site_url('sale_orders/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
							<i class="fa fa-envelope-o"></i>
							<span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
						</a>
					</div>

					<?php if ($inv->status!='completed' && $inv->status!='approved') { ?>
						<div class="btn-group">
							<a href="<?= site_url('sale_orders/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
								<i class="fa fa-edit"></i>
								<span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
							</a>
						</div>
						<div class="btn-group">
							<a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
								data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('sale_orders/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
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
						transaction : "Sale Order",
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
					<?php if ($inv->product_discount != 0) { ?>
						td_html +='<td class="td_print">&nbsp;</td>';
					<?php } if($Settings->foc == 1){ ?>
						td_html +='<td class="td_print">&nbsp;</td>';
					<?php }	?>
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody_main').append(td_html);
			}
		}
		
    });
	
</script>
