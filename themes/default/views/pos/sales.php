<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style type="text/css" media="screen">
    <?php if($Settings->car_operation != 1) { ?>
		#SLData td:nth-child(5),
		#SLData td:nth-child(6),
		#SLData td:nth-child(7),
		#SLData td:nth-child(8) {
			display: none !important;
		}
		#SLData th:nth-child(5),
		#SLData th:nth-child(6),
		#SLData th:nth-child(7),
		#SLData th:nth-child(8) {
			display: none !important;
		}
    <?php } ?>
</style>

<script>
    $(document).ready(function () {
		oTable = $('#SLData').dataTable({
			"aaSorting": [[0, "desc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= site_url('pos/getSales/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : '').'/'.(isset($payment_status) && $payment_status ? $payment_status : '')); ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				var oSettings = oTable.fnSettings();
				nRow.id = aData[0];
				nRow.className = "receipt_link";
				<?php if($pos_settings->table_enable!=1){?>
					var action = $('td:eq(12)', nRow);
					if(aData[12] == 'packaging'){
						action.find('.packaging').remove();
					}
					else if(aData[12] == 'take_away'){
						action.find('.packaging').remove();
						action.find('.undo_packaging').remove();
						action.find('.add_delivery').remove();
					}else{
						action.find('.undo_packaging').remove();
						action.find('.add_delivery').remove();
					}
				<?php } ?>
				<?php if($pos_settings->table_enable==1){?>
					var action = $('td:eq(13)', nRow);
					if(aData[12] == 'packaging'){
						action.find('.packaging').remove();
					}
					else if(aData[12] == 'take_away'){
						action.find('.packaging').remove();
						action.find('.undo_packaging').remove();
						action.find('.add_delivery').remove();
					}else{
						action.find('.undo_packaging').remove();
						action.find('.add_delivery').remove();
					}
				<?php } ?>
				return nRow;
			},
			/* 'bStateSave': true,
			'fnStateSave': function (oSettings, oData) {
				localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
			},
			'fnStateLoad': function (oSettings) {
				var data = localStorage.getItem('DataTables_' + window.location.pathname);
				return JSON.parse(data);
			}, */
			"aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, null,null,null,null, { "sClass":"center" <?php if($pos_settings->table_enable!=1){ echo ",'bVisible':false"; }?>},  {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": pay_status},{"mRender":row_status <?php if($pos_settings->pos_delivery!=1){ echo ",'bVisible':false"; }?> },{"bSortable": false}],
			 "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var gtotal = 0, paid = 0, sale_return = 0, discount = 0,  balance = 0;
				for (var i = 0; i < aaData.length; i++) {
					gtotal += parseFloat(aaData[aiDisplay[i]][9]);
					sale_return += parseFloat(aaData[aiDisplay[i]][10]);
					paid += parseFloat(aaData[aiDisplay[i]][11]);
					discount += parseFloat(aaData[aiDisplay[i]][12]);
					balance += parseFloat(aaData[aiDisplay[i]][13]);
				}
				var nCells = nRow.getElementsByTagName('th');
				<?php if($pos_settings->table_enable!=1) { ?>
					nCells[8].innerHTML = currencyFormat(parseFloat(gtotal));
					nCells[9].innerHTML = currencyFormat(parseFloat(sale_return));
					nCells[10].innerHTML = currencyFormat(parseFloat(paid));
					nCells[11].innerHTML = currencyFormat(parseFloat(discount));
					nCells[12].innerHTML = currencyFormat(parseFloat(balance));
				<?php }else{ ?>
					nCells[9].innerHTML = currencyFormat(parseFloat(gtotal));
					nCells[10].innerHTML = currencyFormat(parseFloat(sale_return));
					nCells[11].innerHTML = currencyFormat(parseFloat(paid));
					nCells[12].innerHTML = currencyFormat(parseFloat(discount));
					nCells[13].innerHTML = currencyFormat(parseFloat(balance));
				<?php } ?>
			}
		}).fnSetFilteringDelay().dtFilter([
			{column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('vehicle_model');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('vehicle_plate');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('vehicle_vin');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('mechanic');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('table');?>]", filter_type: "text", data: []},
			{column_number: 14, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
			{column_number: 15, filter_default_label: "[<?=lang('delivery_status');?>]", filter_type: "text", data: []},
		], "footer");

        $(document).on('click', '.email_receipt', function () {
            var sid = $(this).attr('data-id');
            var ea = $(this).attr('data-email-address');
            var email = prompt("<?= lang("email_address"); ?>", ea);
            if (email != null) {
                $.ajax({
                    type: "post",
                    url: "<?= site_url('pos/email_receipt') ?>/" + sid,
                    data: { <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: sid },
                    dataType: "json",
                        success: function (data) {
                        bootbox.alert(data.msg);
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_request_failed'); ?>');
                        return false;
                    }
                });
            }
        });
		
		$('#multi_payment').live('click',function(){
			var sale_id = '';
			var intRegex = /^\d+$/;
			var i = 0;
			$('.input-xs').each(function(){
				if ($(this).is(':checked') && intRegex.test($(this).val())) {
					if(i==0){
						sale_id += $(this).val();
						i=1;
					}else{
						sale_id += "SaleID"+$(this).val();
					}
				}
			});
			if(sale_id==''){
				alert("<?= lang('no_sale_selected') ?>")
				return false;
			}else{
				var link = '<?= anchor('sales/add_multi_payment/#######', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal" class="multi_payment"')?>';
				var add_payment_link = link.replace("#######", sale_id);
				$("#payment_box").html(add_payment_link);
				$('.multi_payment').click();
				$("#payment_box").html('<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment"><i class="fa fa-money"></i> <?=lang('add_payment')?></a>');		
				return false;
			}
		});
		
    });
</script>

	<?php if ($Owner || $GP['bulk_actions']) {
	    echo form_open('sales/sale_actions', 'id="action-form"');
	} ?>
<div class="box">
	<div class="box-header">
		<div class="box-header">
		<!-- <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('pos_sales').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2> -->
		<div class="sub_menu"></div>
		<div class="sub_menu">
			<a href="<?= site_url('pos') ?>" 
				class="btn btn-success btn-block box_sub_menu" tabindex="-1">
				<i class="fa fa-plus-circle"></i><?= lang('add_sale') ?>
			</a>
		</div>
		<div class="sub_menu">
			<a href="#" id="excel" data-action="export_excel"
				class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
				<i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
			</a>
		</div>
		<div id="payment_box" class="sub_menu">
			<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment" 
				class="btn btn-primary btn-block box_sub_menu" tabindex="-1">
				<i class="fa fa-money"></i> <?=lang('add_payment')?>
			</a>
		</div>
		<div class="sub_menu">
			<a href="#" class="bpo btn btn-danger btn-block box_sub_menu" tabindex="-1" 
				title="<b><?= $this->lang->line("delete_sales") ?></b>" 
				data-content="<p><?= lang('r_u_sure') ?>
				</p><button type='button' class='btn btn-danger' id='delete' data-action='delete'>
				<?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?>
				</button>" data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> 
				<?= lang('delete_sales') ?>
			</a>
		</div>
		
		<div class="box-icon">
            <ul class="btn-tasks">
				<li class="dropdown">
                    <h2 class="blue"><i class="fa fa-line-chart"></i><?= lang('pos_sales'); ?></h2>
                </li>
         
			   <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('pos/sales') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('pos/sales/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
				
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= site_url('pos/sales') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . site_url('pos/sales/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
							}
							?>
						</ul>
					</liv>
				<?php } ?>
            </ul>
        </div>

</div>
   <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->

                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("customer"); ?></th>
							<th><?= lang("vehicle_model"); ?></th>
							<th><?= lang("vehicle_plate"); ?></th>
							<th><?= lang("vehicle_vin"); ?></th>
							<th><?= lang("mechanic"); ?></th>
							<th><?= lang("table"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
							<th><?= lang("returned"); ?></th>
                            <th><?= lang("paid"); ?></th>
							<th><?= lang("discount"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
							<th><?= lang("delivery_status"); ?></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="<?= ($Settings->car_operation == 1 ? 18 : 14) ?>" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
                            <th></th>
                            <th></th>
							<th></th>
                            <th><?= lang("grand_total"); ?></th>
							<th><?= lang("returned"); ?></th>
                            <th><?= lang("paid"); ?></th>
							<th><?= lang("discount"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
							<th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>


<div id="assign"></div>

<script type="text/javascript">
	$(function(){
		$("#SLData").css("width", "auto");	
		$("#assign_to").on("click",function(){
			
			var assign_to = [];
			if($(".multi-select:checked").length > 0){
				
				$(".multi-select:checked").each(function(){
					var multi = $(this).val();
					assign_to.push(multi);
				});
				
			}else{
				alert("<?= lang("select_above") ?>");
				return false;
			}			
			$.ajax({
				url : "<?= site_url("sales/assign_to") ?>",
				data : { assign_to : assign_to },
				type : "GET",
				dataType : "JSON",
				success : function(data){					
					$('#assign').html(data);					
				},
				error:function(e){					
					addAlert(e.responseText, 'danger');
				}
			});
		});	

		$('#multi_payment').live('click',function(){
			var sale_id = '';
			var intRegex = /^\d+$/;
			var i = 0;
			$('.input-xs').each(function(){
				if ($(this).is(':checked') && intRegex.test($(this).val())) {
					if(i==0){
						sale_id += $(this).val();
						i=1;
					}else{
						sale_id += "SaleID"+$(this).val();
					}
					
				}
			});
			if(sale_id==''){
				alert("<?= lang('no_sale_selected') ?>")
				return false;
			}else{
				var link = '<?= anchor('sales/add_multi_payment/#######', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="multi_payment"')?>';
				var add_payment_link = link.replace("#######", sale_id);
				$("#payment_box").html(add_payment_link);
				$('.multi_payment').click();
				$("#payment_box").html('<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment"><i class="fa fa-money"></i> <?=lang('add_payment')?></a>');		
				return false;
			}
		});

		/*==========ASSIGN SALEMAN==========*/
		
		$("#assign_saleman").on("click",function(){
			
			var assign_saleman = [];
			if($(".multi-select:checked").length > 0){
				
				$(".multi-select:checked").each(function(){
					var multi = $(this).val();
					assign_saleman.push(multi);
				});
				
			}else{
				alert("<?= lang("select_above") ?>");
				return false;
			}			
			$.ajax({
				url : "<?= site_url("sales/assign_saleman") ?>",
				data : { assign_saleman : assign_saleman },
				type : "GET",
				dataType : "JSON",
				success : function(data){					
					$('#assign').html(data);					
				},
				error:function(e){					
					addAlert(e.responseText, 'danger');
				}
			});
		});

        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
		

			
	});
</script>

