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
									<td class="text_center" style="width:20%">
										<?php
											echo '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
										?>
									</td>
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
										<?= $this->cus->qrcode('link', urlencode(site_url('products/view_cost_adjustment/' . $inv->id)), 2); ?>
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
                                        <td class="text_center" style="width:20%"><span class="inv_invoice" style="font-size:<?= $font_size+5 ?>px"><?= lang('cost_adjustment') ?></span></td>
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
													<td><?= lang('inv_created_by') ?></td>
													<td> : <strong><?= $created_by->first_name.' '.$created_by->last_name ?></strong></td>
												</tr>
												<?php if($project){ ?>
													<tr>
														<td><?= lang('project') ?></td>
														<td style="text-align:left"> : <b><?= $project->name ?></b></td>
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
													<td style="text-align:left"> : <b><?= $inv->reference_no ?></b></td>
												</tr>
												<tr>
													<td><?= lang('inv_date') ?></td>
													<td style="text-align:left"> : <?= $this->cus->hrsd($inv->date) ?></td>
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
											<td class="text_left">
												'.$row->product_name .'
												'.($row->details ? '<br>' . $row->details : '').'
											</td>
											<td class="text_right">'.$this->cus->formatMoney($row->old_cost).'</td>
											<td class="text_right">'.$this->cus->formatMoney($row->new_cost).'</td>
										</tr>';		
							$i++;
						}
					?>
					<tr>
						<td>
							<table class="table_item_main">
								<thead>
									<tr>
										<!-- <th><?= lang("#"); ?></th>
										<th><?= lang("code"); ?></th>
										<th><?= lang("description"); ?></th>
										<th><?= lang("old_cost"); ?></th>
										<th><?= lang("new_cost"); ?></th> -->

										<th class="text-center" width="30"><?=lang("ល.រ<br>No.");?></th>
										<th class="text-center"><?=lang("កូដ <br> Code");?></th>
										<th class="text-center"><?=lang("បរិយាយ <br> Description");?></th>
										<th class="text-center" width="80"><?=lang("តម្លៃចាស់<br> Old_Cost");?></th>
										<th class="text-center" width="80"><?=lang("តម្លៃថ្មី<br>New_Cost");?></th>
										<?php if($Settings->product_expiry=='1'){ ?>
											<th><?= lang("expiry_date"); ?></th>
										<?php } ?>
									</tr>
								</thead>
								<tbody id="tbody_main">
									<?= $tbody ?>
								</tbody>
								<tfoot>
									<?php if($inv->note){?>
										<tr>
											<td style="border:0px !important" colspan="4"><b><?= lang('note') ?> : </b> <?= $this->cus->decode_html($inv->note)  ?></td>
										</tr>
									<?php } ?>
								</tfoot>
							</table>
						</td>
					</tr>
				</tbody>
				<tfoot>
                    <tr class="tr_print bold">
                        <td>
                            <table style="margin-top:<?= $margin_signature ?>px;">
                                    <td class="text_center" style="width:30%"><?= lang("inv_approved_by") ?></td>
                                    <td class="text_center" style="width:30%"><?= lang("inv_prepared") ?></td>
                                </tr>
                                <tr>
                                    <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                    <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                </tr>
                                <tr>
                                    
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
                        <a id="print_form" onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
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
        border:3px double #333333 !important;
        margin-bottom:<?= $margin ?>px !important;
        margin-top:<?= $margin ?>px !important;
    }
    .table_item_main th{
        border:1px solid black !important;
        background-color: #ddd !important;
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
        border:2px solid #333333 !important;
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
					if(site.settings.product_expiry == 1){
						td_html +='<td class="td_print">&nbsp;</td>';
					}
                    td_html +='<td class="td_print">&nbsp;</td></tr>';
                $('#tbody_main').append(td_html);
            }
        }
        
    });
    
</script>

