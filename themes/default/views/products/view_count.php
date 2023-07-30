<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') -110;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg main_content">
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
									</td>
									<td class="text_center" style="width:20%">
										<?= $this->cus->qrcode('link', urlencode(site_url('products/view_using_stock/' . $inv->id)), 2); ?>
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
									<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('stock_count') ?></i></b></span></td>
									<td valign="bottom" style="width:20%"><hr class="hr_title"></td>
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
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('warehouse') ?></td>
													<td> : <strong><?= $warehouse->name.' ( '.$warehouse->code.' )'; ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('start_date') ?></td>
													<td> : <?= $this->cus->hrld($stock_count->date); ?></td>
												</tr>
												<tr>
													<td><?= lang('end_date') ?></td>
													<td> : <?= $this->cus->hrld($stock_count->updated_at); ?></td>
												</tr>
												<tr class="hidden">
													<td><?= lang('initial_file') ?></td>
													<td> : 	<?= anchor('welcome/download/'.$stock_count->initial_file, '<i class="fa fa-download"></i> '.lang('initial_file'), 'class="btn btn-primary btn-xs"'); ?></td>
												</tr>
												<?php if ($stock_count->type == 'partial') { ?>
													<tr>
														<td><?= lang('categories'); ?></td>
														<td><?= $stock_count->category_names; ?></td>
													</tr>
												<?php } ?>
											</table>
										</fieldset>
									</td>
									<td style="width:40%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('ref') ?></td>
													<td style="text-align:left"> : <b><?= $stock_count->reference_no; ?></b></td>
												</tr>
												<tr>
													<td><?= lang('type') ?></td>
													<td style="text-align:left"> : <b><?= lang($stock_count->type); ?></b></td>
												</tr>
												<tr>
													<td><?= lang('date') ?></td>
													<td style="text-align:left"> : <?= $this->cus->hrsd($stock_count->date) ?></td>
												</tr>
												<tr class="hidden">
													<td><?= lang('final_file') ?></td>
													<td> : <?= anchor('welcome/download/'.$stock_count->final_file, '<i class="fa fa-download"></i> '.lang('final_file'), 'class="btn btn-primary btn-xs"'); ?></td>
												</tr>
												<?php if ($stock_count->type == 'partial') { ?>
													<tr>
														<td><?= lang('brands'); ?></td>
														<td><?= $stock_count->brand_names; ?></td>
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
						foreach ($stock_count_items as $row){
							$tbody .='<tr>
											<td class="text_center">'.$i.'</td>
											<td class="text_left">'.$row->product_code.' - '.$row->name . ($row->product_variant ? ' (' . $row->product_variant . ')' : '').'</td>
											<td class="text_center">'.(($row->product_expiry != '0000-00-00' && $row->product_expiry != '') ? $this->cus->hrsd($row->product_expiry) : '' ).'</td>
											<td class="text_right">'.$this->cus->formatQuantity($row->expected).'</td>
											<td class="text_right">'.$this->cus->formatQuantity($row->counted).'</td>
											<td class="text_right">'.$this->cus->formatQuantity($row->counted-$row->expected).'</td>
										</tr>';		
							$i++;
						}
					?>
					<tr>
						<td>
							<table class="table_item_main">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("description"); ?></th>
										<th><?= lang("expiry"); ?></th>
										<th><?= lang("expected"); ?></th>
										<th><?= lang("counted"); ?></th>
										<th><?= lang("difference"); ?></th>
									</tr>
								</thead>
								<tbody id="tbody_main">
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
                            		<td class="text_center" style="width:30%"><?= lang("inv_stock_control_by") ?></td>
                                    <td class="text_center" style="width:30%"><?= lang("inv_counted_by") ?></td>
                                    <td class="text_center" style="width:30%"><?= lang("inv_prepared") ?></td>
                                </tr>
                                <tr>
                                    <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                    <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                    <td class="text_center" style="width:30%; padding-top:100px">______________________</td>
                                </tr>
                                <tr>
                                    <td class="text_center" style="width:30%">ឈ្មោះ <?= $using_by->last_name." ".$using_by->first_name;?></td>
                                    <td class="text_center" style="width:30%">ឈ្មោះ </td>
                                    <td class="text_center" style="width:30%">ឈ្មោះ <?= $created_by->last_name." ".$created_by->first_name;?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
			</table>
	
			<div id="buttons" style="padding:10px 0px 10px 0px" class="no-print">
				<hr>
				<div class="btn-group btn-group-justified">
					<div class="btn-group">
						<a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
							<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
							<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<?php
						if ($adjustment) {
							echo '<a href="'.site_url('products/view_adjustment/'.$adjustment->id).'" class="btn btn-primary btn-block no-print" data-toggle="modal" data-target="#myModal2">'.lang('view_adjustment').'</a>';
						} else {
							echo '<a href="'.site_url('products/add_adjustment/'.$stock_count->id).'" class="btn btn-primary btn-block no-print">'.lang('add_adjustment').'</a>';
						}
						?>
					</div>
				</div>
			</div>
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

