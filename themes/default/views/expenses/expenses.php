<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
		<?php if($this->session->userdata('remove_expls')) { ?>
			if (localStorage.getItem('expitems')) {
				localStorage.removeItem('expitems');
			}
			if (localStorage.getItem('exptax2')) {
				localStorage.removeItem('exptax2');
			}
			if (localStorage.getItem('exdiscount')) {
				localStorage.removeItem('exdiscount');
			}
			if (localStorage.getItem('expref')) {
				localStorage.removeItem('expref');
			}
			if (localStorage.getItem('expwarehouse')) {
				localStorage.removeItem('expwarehouse');
			}
			if (localStorage.getItem('expsupplier')) {
				localStorage.removeItem('expsupplier');
			}
			if (localStorage.getItem('expnote')) {
				localStorage.removeItem('expnote');
			}
			if (localStorage.getItem('expbiller')) {
				localStorage.removeItem('expbiller');
			}
			if (localStorage.getItem('expdate')) {
				localStorage.removeItem('expdate');
			}
			if (localStorage.getItem('expproject')) {
				localStorage.removeItem('expproject');
			}
			if (localStorage.getItem('exproom')) {
				localStorage.removeItem('exproom');
			}
			if (localStorage.getItem('expvehicle')) {
				localStorage.removeItem('expvehicle');
			}
			if (localStorage.getItem('exppayable_account')) {
				localStorage.removeItem('exppayable_account');
			}
			if (localStorage.getItem('exppaying_from')) {
				localStorage.removeItem('exppaying_from');
			}

        <?php $this->cus->unset_data('remove_expls'); } ?>

        oTable = $('#EXPData1').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 24, 40, 100, -1], [10, 24, 40, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?=site_url('purchases/getExpenses/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : 0))?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "mRender": checkbox
            }, {"mRender": fld}, null, null, null,  {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat},{"mRender": row_status}, {"mRender": row_status}, {
                "bSortable": false,
                "mRender": attachment
            }, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "expense_link";
				
				var action = $('td:eq(11)', nRow);
				<?php if($Settings->approval_expense==1){ ?>
					if(aData[8] == "approved"){
						action.find('.approve_expense').remove();
						action.find('.edit_expense').remove();
						action.find('.delete_expense').remove();
					}else if(aData[8] == "pending"){
						action.find('.unapprove_expense').remove();
						action.find('.expense_payment').remove();
					}
				<?php } ?>
				
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0; paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][5]);
					paid += parseFloat(aaData[aiDisplay[i]][6]);
					balance += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(total);
				nCells[6].innerHTML = currencyFormat(paid);
				nCells[7].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []}
        ], "footer");
		
		$('#multi_payment').live('click',function(){
			var purchase_id = '';
			var intRegex = /^\d+$/;
			var i = 0;
			$('.input-xs').each(function(){
				if ($(this).is(':checked') && intRegex.test($(this).val())) {
					if(i==0){
						purchase_id += $(this).val();
						i = 1;
					}else{
						purchase_id += "ExpenseID"+$(this).val();
					}
				}
			});
			if(purchase_id==''){
				alert("<?= lang('no_sale_selected') ?>")
				return false;
			}else{
				var link = '<?= anchor('purchases/add_multi_expayment/#######', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="multi_payment"')?>';
				var add_payment_link = link.replace("#######", purchase_id);
				$("#payment_box").html(add_payment_link); $('.multi_payment').click();
				$("#payment_box").html('<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment"><i class="fa fa-money"></i> <?=lang('add_payment')?></a>');		
				return false;
			}
		});
    });
</script>

<?php if ($Owner) {
    echo form_open('purchases/expense_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
	<!-- <h2 class="blue"><i class="fa-fw fa fa-star"></i><?= lang('expenses').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2>	 -->
		<div class="sub_menu"></div>
		<div class="sub_menu">
			<a href="<?=site_url('expenses/add')?>" class="btn btn-success btn-block box_sub_menu" tabindex="-1">
				<i class="fa fa-plus-circle"></i> <?=lang('add_expense')?>
			</a>
		</div>
		<div class="sub_menu">
			<a href="<?=site_url('purchases/import_expense')?>" class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
				<i class="fa fa-plus-circle"></i> <?=lang('import_expense')?>
			</a>
		</div>
		<div class="sub_menu">
			<a href="#" id="excel" data-action="export_excel" class="btn btn-primary btn-block box_sub_menu" tabindex="-1">
				<i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
			</a>
		</div>
		<div class="sub_menu" id="payment_box">
			<?php if($Settings->payment_expense){ ?>
					<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment" 
						class="btn btn-info btn-block box_sub_menu" tabindex="-1">
						<i class="fa fa-money"></i> <?=lang('add_payment')?>
					</a>
			<?php } ?>
		</div>
		<div class="sub_menu">
			<a href="#" class="bpo btn btn-danger btn-block box_sub_menu" tabindex="-1"
				title="<b><?= $this->lang->line("delete_expenses") ?></b>" 
				data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
				data-html="true" data-placement="left">
				<i class="fa fa-trash-o"></i> <?= lang('delete_expenses') ?>
			</a>
		</div>
	
		<div class="box-icon">
            <ul class="btn-tasks">
				<li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-dollar"></i><?= lang('expenses'); ?></h2>
                </li>
				
				<?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchases/expenses/') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('purchases/expenses/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
				<?php } if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= site_url('purchases/expenses') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . site_url('purchases/expenses/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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

                <div class="table-responsive">
                    <table id="EXPData1" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference"); ?></th>
							<th><?= lang("project"); ?></th>
							<th><?= lang("supplier"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
							<th><?= lang("balance"); ?></th>
							<th><?= lang("status"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                            </th>
                            <th style="width:30px !important;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="14" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
                            <th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>



<style type="text/css">
	<?php if(!$Settings->project){ ?>
		#EXPData1 th:nth-child(4), #EXPData1 td:nth-child(4){
			display:none !important;
		}
	<?php } if($Settings->payment_expense==0){ ?>
		#EXPData1 th:nth-child(5), #EXPData1 td:nth-child(5){
			display:none !important;
		}
		#EXPData1 th:nth-child(7), #EXPData1 td:nth-child(7){
			display:none !important;
		}
		#EXPData1 th:nth-child(8), #EXPData1 td:nth-child(8){
			display:none !important;
		}	
		#EXPData1 th:nth-child(10), #EXPData1 td:nth-child(10), .expense_payment{
			display:none !important;
		}
		
	<?php } if($Settings->approval_expense==0){ ?>
			#EXPData1 th:nth-child(9), #EXPData1 td:nth-child(9){
				display:none !important;
			}
	<?php } ?>
</style>
