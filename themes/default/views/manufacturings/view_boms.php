<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') -10;
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
 <!-- <div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body"> -->
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
										
										</div>
									</td> 
									<td class="text_center" style="width:20%">
										<?= $this->cus->qrcode('link', urlencode(site_url('manufacturings/view_boms/' . $convert->id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:70%"><hr class="hr_title"></td>
									<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('bills_of_materials') ?></i></b></span></td>
									<td valign="bottom" style="width:10"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
						      <td style="width:50%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('ព័ត៌មានអ្នកប្រើប្រាស់/User Info') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('user') ?></td>
													<td> : <strong><?= $created_by->first_name.' '.$created_by->last_name ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('phone') ?></td>
													<td> : <strong><?= $created_by->phone ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('email') ?></td>
													<td> : <strong><?= $biller->email ?></strong></td>
												</tr>
											</table>
										</fieldset>
									</td>
									</td>
									<td style="width:50%;">
										<fieldset style="margin-left:5px;padding: 11px; !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('inv_reference') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('inv_date') ?></td>
													<td style="text-align:left"> : <?= $this->cus->hrsd($boms->updated_at) ?></td>
												</tr>
												<tr>
													<td><?= lang('bom') ?></td>
													<td style="text-align:left"> : <?= $boms->name ?></td>
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
						$tbody_finish = '';
						$i=1;
						$t=1;
						$sub_total_bom_cost = 0;
						$total_bom_cost = 0;
						foreach ($bom_items as $row){
							
							
							if($row->type=="raw_material"){
								$total_bom_cost += $row->total_cost;
								$tbody .='<tr>
												<td class="text_center">'.$i.'</td>
												<td class="text_center">'.$row->product_code.'</td>
												<td class="text_left">'.$row->product_name.'</td>
												<td class="text_right">'.$this->cus->convertQty($row->product_id,$row->quantity).'</td>
												<td class="text_right">'.$this->cus->formatMoney($row->unit_cost).'</td>
												<td class="text_right">'.$this->cus->formatMoney($row->total_cost).'</td>
											</tr>';		
								$i++;
							}else{
								$tbody_finish .='<tr>
												<td class="text_center">'.$t.'</td>
												<td class="text_center">'.$row->product_code.'</td>
												<td class="text_left">'.$row->product_name.'</td>
												<td class="text_right">'.$this->cus->convertQty($row->product_id,$row->quantity).'</td>
												<td class="text_right">'.$this->cus->formatMoney($row->unit_cost).'</td>
												<td class="text_right bold">'.$this->cus->formatMoney($total_bom_cost).'</td>
											</tr>';		
								$t++;
							}
							
						}

						$footer_colspan = 4;
                    $footer_rowspan = 1;
                    if($inv->grand_total != $inv->total){
                        $footer_rowspan++;
                    }
                    
                    $tfooter = '';

                    $footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->cus->decode_html($inv->note1).'</td>';
                   
                        $tfooter .= '<tr>
                                        '.$footer_note.'
                                        <td class="text_right"><b>'.lang("total_bom_cost").'</b></td>
                                        <td class="text_right"><b>'.$this->cus->formatMoney($total_bom_cost).'</b></td>
                                    </tr>';
                        $footer_note = '';      
                    
                    

					?>
					<tr>
						<td>
							<table class="table_item_main">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("code"); ?></th>
										<th><?= lang("description"); ?></th>
										<th><?= lang("quantity"); ?></th>
										<th><?= lang("product_cost"); ?></th>
										<th><?= lang("boms_cost"); ?></th>
									</tr>
								</thead>
								<tbody id="tbody_main">
									<tr>
										<td colspan="4" class="text_left"><b><?= lang('raw_material') ?> : </b></td>
									</tr>
									<?= $tbody ?>

									<tbody id="tfooter">
                                	<?= $tfooter ?>
                            	</tbody>

									<tr>
										<td colspan="4" class="text_left"><b><?= lang('finished_good') ?> : </b></td>
									</tr>
									<?= $tbody_finish ?>
								</tbody>
								
								<tfoot>
									<?php if($boms->note){?>
										<tr>
											<td style="border:0px !important" colspan="4"><b><?= lang('note') ?> : </b> <?= $this->cas->decode_html($boms->note)  ?></td>
										</tr>
									<?php } ?>
								</tfoot>
							</table>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr class="tr_print">
						<td>
							<table style="margin-top:<?= $margin_signature ?>px;">
								<tr>
									<td class="text_center" style="width:50%"><?= lang("preparer") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:50%"><?= lang("approver").' '. lang("signature") ?></td>
								</tr>
								<tr>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
								</tr>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
	
			<div id="buttons" style="padding: 0px 0px 10px 0px" class="no-print">
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
			
					<div class="btn-group">
						<a href="<?= site_url('converts/edit_bom/' . $boms->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
							<i class="fa fa-edit"></i>
							<span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
							data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('converts/delete_bom/' . $boms->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
							data-html="true" data-placement="top">
							<i class="fa fa-trash-o"></i>
							<span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
						</a>
					</div>
				</div>
			</div>
		<!-- </div>
	</div>
</div> -->
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
        border:3px double #000 !important;
        margin-bottom:<?= $margin ?>px !important;
        margin-top:<?= $margin ?>px !important;
    }
    .table_item_main th{
        border:1px solid black !important;
		background: #ddd !important;
        text-align:center !important;
        line-height: 14px !important;
        padding: 8px;
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
        border:2px solid #000 !important;
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
        background-color : #ddd !important;
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
						transaction_id : <?= $boms->id ?>,
						transaction : "bom",
						reference_no : "<?= $boms->reference_no ?>"
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
					// td_html +='<td class="td_print"><div style="height:'+blank_height+'px !important">&nbsp;</div></td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
    });
	
</script>

