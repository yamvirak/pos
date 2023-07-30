<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 60;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size;
?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
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
                                <td class="text_center" style="width:15%">
                                    <?= $this->cus->qrcode('link', urlencode(site_url('quotations/view/' . $inv->id)), 2); ?>
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
									<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('transfer') ?></i></b></span></td>
									<td valign="bottom" style="width:20%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<tr>
										<td colspan="2">
											<fieldset>
												<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
												<table>
											
													<tr>
														<td><?= lang('ref') ?></td>
														<td style="text-align:left"> : <b><?= $transfer->transfer_no ?></b></td>
													</tr>
													<tr>
														<td><?= lang('date') ?></td>
														<td style="text-align:left"> : <?= $this->cus->hrld($transfer->date) ?></td>
													</tr>
													<tr>
														<td><?= lang('created_by') ?></td>
														<td style="text-align:left"> : <?= $created_by->first_name.' '.$created_by->last_name ?></td>
													</tr>
													
												</table>
											</fieldset>
										</td>
									</tr>
									<td style="width:50%">
										<fieldset>
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('from') ?></i></b></legend>
											<table>
												<?php if($fr_project){ ?> 
													<tr>
														<td><?= lang('project') ?></td>
														<td style="text-align:left"> : <b><?= $fr_project->name ?></b></td>
													</tr>
												<?php } ?>
												<tr>
													<td><?= lang('warehouse') ?></td>
													<td style="text-align:left"> : <b><?= $from_warehouse->name ?></b></td>
												</tr>
												<tr>
													<td><?= lang('address') ?></td>
													<td style="text-align:left"> : <?= strip_tags($from_warehouse->address) ?></td>
												</tr>
												<tr>
													<td><?= lang('phone') ?></td>
													<td style="text-align:left"> : <?= strip_tags($from_warehouse->phone) ?></td>
												</tr>
												
											</table>
										</fieldset>
									</td>
									<td style="width:50%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('to') ?></i></b></legend>
											<table>
												<?php if($to_project){ ?> 
													<tr>
														<td><?= lang('project') ?></td>
														<td style="text-align:left"> : <b><?= $to_project->name ?></b></td>
													</tr>
												<?php } ?>
												<tr>
													<td><?= lang('warehouse') ?></td>
													<td style="text-align:left"> : <b><?= $to_warehouse->name ?></b></td>
												</tr>
												<tr>
													<td><?= lang('address') ?></td>
													<td style="text-align:left"> : <?= strip_tags($to_warehouse->address) ?></td>
												</tr>
												<tr>
													<td><?= lang('phone') ?></td>
													<td style="text-align:left"> : <?= strip_tags($to_warehouse->phone) ?></td>
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
							if($Settings->product_expiry=='1'){
								if($row->expiry  && $row->expiry != '0000-00-00'){
									$expired_td = '<td class="text_center">'.$this->cus->hrsd($row->expiry).'</td>';
								}else{
									$expired_td = '<td></td>';
								}
								
							}else{
								$expired_td = '';
							}
							$tbody .='<tr>
											<td class="text_center">'.$i.'</td>
											<td class="text_center">'.$row->product_code.'</td>
											<td class="text_left">
												'.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '').'
												'.($row->serial_no ? '<br>' . $row->serial_no : '').'
											</td>
											<td class="text_center">'.$this->cus->formatQuantity($row->unit_quantity).' '.$row->unit_name.'</td>
											'.$expired_td.'
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
										<th><?= lang("code"); ?></th>
										<th><?= lang("description"); ?></th>
										<th><?= lang("quantity"); ?></th>
										<?php if($Settings->product_expiry=='1'){ ?>
											<th><?= lang("expiry_date"); ?></th>
										<?php } ?>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tfoot>
									<?php if($transfer->note){?>
										<tr>
											<td style="border:0px !important" colspan="4"><b><?= lang('note') ?> : </b> <?= $this->cus->decode_html($transfer->note)  ?></td>
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
									<td class="text_center" style="width:25%"><?= lang("sender").' '. lang("signature") ?></td>
									<td class="text_center" style="width:25%"><?= lang("preparer") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:25%"><?= lang("approver") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:25%"><?= lang("receiver").' '. lang("signature") ?></td>
								</tr>
								<tr>
									<td class="text_center" style="width:25%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:25%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:25%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:25%; padding-top:60px">______________________</td>
								</tr>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
	
			<div id="buttons" style="padding-top:10px;" class="no-print">
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
					<?php if ($transfer->attachment) { ?>
						<div class="btn-group">
							<a href="<?= site_url('assets/uploads/' . $transfer->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
								<i class="fa fa-download"></i>
								<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
							</a>
						</div>
					<?php } ?>
					<div class="btn-group">
						<a href="<?= site_url('transfers/email/' . $transfer->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
							<i class="fa fa-envelope-o"></i>
							<span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a href="<?= site_url('transfers/edit/' . $transfer->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
							<i class="fa fa-edit"></i>
							<span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
							data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('transfers/delete/' . $transfer->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
							data-html="true" data-placement="top">
							<i class="fa fa-trash-o"></i>
							<span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
						</a>
					</div>
				</div>
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
	#tbody .td_print{
		border:none !important;
		border-left:1px solid black !important;
		border-right:1px solid black !important;
		border-bottom:1px solid black !important;
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
		line-height:30px !important;
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
	
	fieldset{
		-moz-border-radius: 9px !important;
		-webkit-border-radius: 15px !important;
		border-radius:9px !important;
		border:2px solid #428BCD !important;
		min-height:<?= $min_height ?>px !important;
		margin-bottom : <?= $margin ?>px !important;
		padding-left : <?= $margin ?>px !important;
	}

	legend{
		width: initial !important;
		margin-bottom: initial !important;
		border: initial !important;
	}
	
	.modal table{
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
						transaction_id : <?= $transfer->id ?>,
						transaction : "Transfer",
						reference_no : "<?= $transfer->transfer_no ?>"
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
					if(site.settings.product_expiry == 1){
						td_html +='<td class="td_print">&nbsp;</td>';
					}
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
    });
	
</script>

