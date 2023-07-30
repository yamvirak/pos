<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php 
	if($customer->gender=='male'){
    $customer->gender = 'Male';
    	}else{
    $customer->gender = 'Female';
	}
	$deposit_amount = 0;
        if($deposits){
            foreach($deposits as $deposit){
                $deposit_amount += $deposit->amount;
        }
    }
?>
<ul id="myTab" class="nav nav-tabs no-print">
	<li>
		<a href="#rental_details" class="tab-grey">
		<?= lang('rental_details') ?> 
		</a>
	</li>
	<li>
		<a href="#sales" class="tab-grey">
		<?= lang('sales') ?> 
		</a>
	</li>
</ul>

<div class="tab-content">
	<div id="rental_details" class="tab-pane">
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa fa-users"></i><?= lang('customer_information'); ?>  (<?= $room->name ?>)</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
		                    <a href="javascript:;" onclick="window.print();" id ="print" class="tip" title="<?= lang('print') ?>"><i class="fonts icon fa fa-file-fa fa-print"></i></a>
		                </li>
					</ul>
				</div>
			</div>


			<div class="box-content">
				<div class="row">
					<div class="col-sm-12">
						<table class="table table-bordered table-hover table-striped table-condensed reports-table dataTable table-service" border="1">
                                <tr>
                                    <td class="col-lg-3"><?= lang('customer_id') ?></td>
                                    <td class="col-lg-3"><?= $customer->code ?></td>
                                    <td class="col-lg-3"><?= lang('customer_name') ?></td>
                                    <td class="col-lg-3"><?= $customer->company ?></td>
                                </tr>   
                                <tr>
                                	<td class="col-lg-3"><?= lang('gender') ?></td>
                                    <td class="col-lg-3"><?= $customer->gender ?></td>
                                    <td class="col-lg-3"><?= lang('nationality') ?></td>
                                    <td class="col-lg-3"><?= $customer->nationality ?></td>
                                </tr>   
                                <tr>
                                    <td class="col-lg-3"><?= lang('phone') ?></td>
                                    <td class="col-lg-3"><?= $customer->phone ?></td>
                                    <td class="col-lg-3"><?= lang('email') ?></td>
                                    <td class="col-lg-3"><?= $customer->email ?></td>
                                </tr>
                                <tr>
                                    <td class="col-lg-3"><?= lang('id_card_type') ?></td>
                                    <td class="col-lg-3"><?= $customer->card_types ?></td>
                                    <td class="col-lg-3"><?= lang('identity_no') ?></td>
                                    <td class="col-lg-3"><?= $customer->nric ?></td>
                                </tr>
                                <tr>              
                                    <td><?= lang('address') ?></td>
                                    <td colspan="4"> <?= $customer->address ?></td>           
                                </tr>
                        </table>
						
					</div>
					<div class="col-sm-2 hidden">
						<?php 
						$file_exists = file_exists('assets/uploads/avatars/blank.png');
						if ($file_exists) {
							echo '<img src="' . base_url() . 'assets/uploads/avatars/blank.png" alt="' . $customer->logo . '" class="avatar"/>';
						}
						?>
					</div>


					<div class="clearfix"></div>
				</div>
			</div>
		</div>

		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa fa-sign-out"></i><?= lang('check_in_out_info'); ?></h2>
				<div class="box-icon">
					<ul class="btn-tasks">
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-sm-12">
						<table class="table table-bordered table-hover table-striped table-condensed reports-table dataTable table-service" border="1">
                                <tr>

                                	<td class="col-lg-3"><?= lang('reservation_date') ?></td>
                                    <td class="col-lg-3"><?=$this->cus->convertStandardEnDate($rental->from_date);?></td>
                                    
                                    
									<td class="col-lg-3"><?= lang('room_no') ?></td>
                                    <td class="col-lg-3 bold"><?=$room->name;?></td>
									
                                </tr>   
                                <tr>
                                	<td class="col-lg-3"><?= lang('checked_in_date') ?></td>
                                	<td class="col-lg-3">
                                    	<?php
											if(!empty($rental->checked_in) && $rental->checked_in != '0000-00-00'){
												echo $this->cus->convertStandardEnDate($rental->checked_in);
											}
										?>
									</td>
									<td class="col-lg-3"><?= lang('room_type') ?></td>
                                    <td class="col-lg-3 bold"><?= $room->room_type_name?></td>
                                    
                                   
                                </tr>
                                <tr>
                                	<td class="col-lg-3"><?= lang('from_date') ?></td>
                                    <td class="col-lg-3"><?=$this->cus->convertStandardEnDate($rental->from_date);?></td>
                                    
                                    <td class="col-lg-3"><?= lang('duration_of_stay') ?></td>
                                    <td class="col-lg-3"><?= $frequency->description ?></td>
                                </tr> 
                                <tr>
                                	<td class="col-lg-3"><?= lang('to_date') ?></td>
                                    <td class="col-lg-3"><?=$this->cus->convertStandardEnDate($rental->to_date);?></td>
                                    <td class="col-lg-3"><?= lang('person_in_stay') ?></td>
                                    <td class="col-lg-3">
                                    	<?= lang('adult') ?> ( <?= ($rental?$rental->adult:'N/A') ?> ), 
                                    	<?= lang('kid') ?> ( <?= ($rental?$rental->kid:'N/A') ?> )
                                    </td>
                                  
                                </tr>
                                <tr>
                                    <td class="col-lg-3"><?= lang('referred_by') ?></td>
                                    <td class="col-lg-3"><?= $customer->nric?></td>
                                    <td class="col-lg-3"><?= lang('status') ?></td>
                                    <td class="col-lg-3"><?= lang($rental->status) ?></td>
                                </tr>
                                <tr>
                                    <td class="col-lg-3"><?= lang('sources') ?></td>
                                    <td class="col-lg-3"><?= $rental->sources?></td>

                                    <td class="col-lg-3"><?= lang('remark') ?></td>
                                    <td class="col-lg-3"><?= lang($rental->note) ?></td>
                                </tr>

                                
                        </table>
                       </div>
                   </div>
               </div>
           </div>

           <div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa fa-money"></i><?= lang('payment_information'); ?></h2>
				<div class="box-icon">
					<ul class="btn-tasks">
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-sm-12">
						<div style="margin-left: 15px;font-style: italic;margin-top: -15px !important;">
							<h2 class="blue"><i class="fa fa-bed"></i> &nbsp; <?= lang('rental_service_information'); ?></h2>
						</div>
						<?php 
						if($rental_items){
								echo '<table class="table table-bordered table-hover table-striped table-condensed reports-table dataTable">';
									echo '<tr>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:50px;">'.lang("#").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:500px;">'.lang("description").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:200px;">'.lang("date").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:200px;">'.lang("quantity").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:200px;">'.lang("price").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center;width:200px;">'.lang("total_amount").'</td>
										  <tr>';
									foreach($rental_items as $key => $rental_item){
										$old_number = '';
										if($rental_item->old_number){
											$old_number .= '<small>[~'.(double)$rental_item->old_number.']</small>';
										}
										$comment = '';
										if($rental_item->comment){
											$comment .= '<small>(~'.$rental_item->comment.')</small>';
										}
										$total_amount = $rental_item->quantity * $rental_item->unit_price;
										$sub_total_amount += $total_amount;
										$total_balance = $sub_total_amount - $deposit_amount;

										echo '<tr>
												<td style="text-align:center;">'.($key+1).'</td>
												<td>'.$rental_item->product_name.' '.$old_number.' '.$comment.'</small></td>
												<td style="text-align:center;">'.$this->cus->convertStandardEnDate($rental->from_date).'</td>
												<td style="text-align:right;">'.$this->cus->formatQuantity($rental_item->quantity).'</td>
												<td style="text-align:right;">'.$this->cus->formatMoney($rental_item->unit_price).'</td>
												<td style="text-align:right;">'.$this->cus->formatMoney($total_amount).'</td>
											</tr>';
										}
										echo  '<tfoot class="bold">';
										echo '<tr>	
												<td colspan="5" style="text-align:right;">'.lang('grand_total').'</td>
												<td style="text-align:right;" class="success">'.$this->cus->formatMoney($sub_total_amount).'</td>
											</tr>';
										echo '<tr>	
												<td colspan="5" style="text-align:right;">'.lang('deposit').'</td>
												<td style="text-align:right;" class="info">'.$this->cus->formatMoney($deposit_amount).'</td>
											</tr>';
										echo '<tr>	
												<td colspan="5" style="text-align:right;">'.lang('balance').'</td>
												<td style="text-align:right;" class="warning">'.$this->cus->formatMoney($total_balance).'</td>
											</tr>';
										echo  '</tfoot>';
								echo '</table>';
							}
						?>
                       </div>
                   </div>
               </div>

				
               <div class="box-content">
				<div class="row">
					<div class="col-sm-12">
						<div style="margin-left: 15px;font-style: italic;margin-top: -25px !important;">
							<h2 class="blue"><i class="fa fa-cutlery"></i> &nbsp; <?= lang('food_order_information'); ?></h2>
						</div>
						<?php 
						if($food_order_items){
								echo '<table class="table table-bordered table-hover table-striped table-condensed reports-table dataTable">';
									echo '<tr>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:50px;">'.lang("#").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:500px;">'.lang("description").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:200px;">'.lang("date").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:200px;">'.lang("quantity").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center; width:200px;">'.lang("price").'</td>
											<td style="font-weight:bold; background:#428BCA; color:#FFF; text-align:center;width:200px;">'.lang("total_amount").'</td>
										  <tr>';
									foreach($food_order_items as $key => $food_order_item){
										$old_number = '';
										if($food_order_item->old_number){
											$old_number .= '<small>[~'.(double)$food_order_item->old_number.']</small>';
										}
										$comment = '';
										if($rental_item->comment){
											$comment .= '<small>(~'.$food_order_item->comment.')</small>';
										}
										$total_amount_item = $food_order_item->quantity * $food_order_item->unit_price;
										$sub_total_amount_item += $total_amount_item;
										$total_grandTotal_all = $sub_total_amount_item + $total_balance;

										echo '<tr>
												<td style="text-align:center;">'.($key+1).'</td>
												<td>'.$food_order_item->product_name.' '.$old_number.' '.$comment.'</small></td>
												<td style="text-align:center;">'.$this->cus->convertStandardEnDate($rental->from_date).'</td>
												<td style="text-align:right;">'.$this->cus->formatQuantity($food_order_item->quantity).'</td>
												<td style="text-align:right;">'.$this->cus->formatMoney($food_order_item->unit_price).'</td>
												<td style="text-align:right;">'.$this->cus->formatMoney($total_amount_item).'</td>
											</tr>';
										}
										echo  '<tfoot class="bold">';
										echo '<tr>	
												<td colspan="5" style="text-align:right;">'.lang('sub_total').'</td>
												<td style="text-align:right;" class="success">'.$this->cus->formatMoney($sub_total_amount_item).'</td>
											</tr>';
										echo '<tr>	
												<td colspan="5" style="text-align:right;">'.lang('grand_total_all').'</td>
												<td style="text-align:right;" class="danger">'.$this->cus->formatMoney($total_grandTotal_all).'</td>
											</tr>';
										echo  '</tfoot>';
								echo '</table>';
							}
						?>
                       </div>
                   </div>
               </div>
           </div>

           

	</div>
	<div id="sales" class="tab-pane fade in">
		<script>
			$(document).ready(function () {
				$('.tip').tooltip();
				oTable = $('#PRental').dataTable({
					"aaSorting": [[1, "desc"]],
					"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
					"iDisplayLength": <?= $Settings->rows_per_page ?>,
					'bProcessing': true, 'bServerSide': true,
					'sAjaxSource': '<?= site_url('rentals/getSales/'.$id) ?>',
					'fnServerData': function (sSource, aoData, fnCallback) {
						aoData.push({
							"name": "<?= $this->security->get_csrf_token_name() ?>",
							"value": "<?= $this->security->get_csrf_hash() ?>"
						});
						$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
					},
					'fnRowCallback': function (nRow, aData, iDisplayIndex) {
						var oSettings = oTable.fnSettings();
						nRow.id = aData[10];
						nRow.className = "invoice_link";
						return nRow;
					},
					"aoColumns": [
						{"mRender": fld}, 
						null,
						null,
						{"mRender": fld},
						{"mRender": fld},
						{"mRender" : currencyFormat },
						{"mRender" : currencyFormat },
						{"mRender" : currencyFormat },
						{"mRender" : row_status},
						null,
					],
					"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
						var grand_total =0, paid = 0, balance =0;
						for (var i = 0; i < aaData.length; i++) {
							grand_total += parseFloat(aaData[aiDisplay[i]][5]);
							paid += parseFloat(aaData[aiDisplay[i]][6]);
							balance += parseFloat(aaData[aiDisplay[i]][7]);
						}
						var nCells = nRow.getElementsByTagName('th');
						nCells[5].innerHTML = currencyFormat(parseFloat(grand_total));
						nCells[6].innerHTML = currencyFormat(parseFloat(paid));
						nCells[7].innerHTML = currencyFormat(parseFloat(balance));
					},
				}).fnSetFilteringDelay().dtFilter([
					{column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
					{column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
					{column_number: 2, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
					{column_number: 3, filter_default_label: "[<?=lang('from_date');?>]", filter_type: "text", data: []},
					{column_number: 4, filter_default_label: "[<?=lang('to_date');?>]", filter_type: "text", data: []},
					{column_number: 8, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
				], "footer");
				
				$('#pdf').click(function (event) {
					event.preventDefault();
					window.location.href = "<?=site_url('rentals/sales_actions/'.$id.'/1/0')?>";
					return false;
				});
				$('#xls').click(function (event) {
					event.preventDefault();
					window.location.href = "<?=site_url('rentals/sales_actions/'.$id.'/0/1')?>";
					return false;
				});
			});
		</script>
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa fa-heart-o"></i><?= lang('sales'); ?> (<?= $room->name ?>)</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
							</a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							   <li>
									<li>
										<a href="#" id="xls" data-action="export_excel"><i class="fa fa-file-excel-o"></i> 
											<?= lang('export_to_excel') ?>
										</a>
									</li>
									<li>
										<a href="#" id="pdf" data-action="export_pdf"><i class="fa fa-file-pdf-o"></i> 
											<?= lang('export_to_pdf') ?>
										</a>
									</li>
							   </li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('list_results'); ?></p>
						<div class="table-responsive">
							<table id="PRental" class="table table-bordered dataTable" style="white-space:nowrap;">
								<thead>
									<tr>
										<th style="width:200px;"><?= lang("date"); ?></th>
										<th style="width:200px;"><?= lang("reference_no"); ?></th>
										<th style="width:200px;"><?= lang("customer"); ?></th>
										<th style="width:200px;"><?= lang("from_date"); ?></th>
										<th style="width:200px;"><?= lang("to_date"); ?></th>
										<th style="width:200px;"><?= lang("grand_total"); ?></th>
										<th style="width:200px;"><?= lang("paid"); ?></th>
										<th style="width:150px;"><?= lang("balance"); ?></th>
										<th style="width:90px;"><?= lang("payment_status"); ?></th>
										<th style="width:90px;"><?= lang("actions"); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
									</tr>
								</tbody>
								<tfoot>
									<tr>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>
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
		.box-header{
			display:table-row !important;
		}
        @page{
            margin:2mm; 
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
	.table-service{
		width:40%;
	}
	.table-service td{
		padding:2px;
		border:1px solid #357EBD;
	}
    .table_item th{
        border:1px solid black !important;
        background-color : rgb(0 0 0 / 10%) !important;
        text-align:center !important;
        line-height:22px !important;
    }
    .table_item td{
        border:1px solid black;
        line-height:20px !important;
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

    .inv_invoice_kh{
        font-size: 18px;
        font-weight: bold;
        font-family: Khmer OS Muol Light!important;
    }
    .inv_invoice{
        font-size: 18px;
        font-weight: bold;
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
    .footer_signature{
        font-family: 'Ubuntu','Khmer OS Muol Light';
        text-align: center;
        width: 25%;
        font-weight: bold;
    }
    .footer_signature_name{
        font-family: 'Ubuntu','Khmer OS Muol Light';
    }
    .footer_signature_body{
        text-align: center;
        width: 25%;
        padding-top: 70px;
    }
    .footer_signature_footer{
        text-align: center;
        width: 25%;
    }
    .table_header td{
        border:1px solid #000000 !important;
        line-height:22px !important;
        padding: 0px 2px 0px 2px !important;
        font-weight: bold;
    }
    .table_item tbody td{
         border:1px solid #000000 !important;
    }
           
</style>