<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-12">
                <div class="small-box padding1010 col-sm-3 bblue">
					<?php 
						$purchases->total_amount = $purchases->total_amount + $expenses->total_amount;
						$grandtotal_purchases->total_amount = $grandtotal_purchases->total_amount + $expenses->total_amount;
						$purchases->paid = $purchases->paid + $expenses->paid;
					?>
                    <h3><?= isset($grandtotal_purchases->total_amount) ? $this->cus->formatMoney($grandtotal_purchases->total_amount) : '0.00' ?></h3>

                    <p><?= lang('grand_total') ?></p>
                    <p><small><?= lang('purchases') ?> + <?= lang('expenses') ?> + <?= lang('freight') ?></small></p>
                </div>
                <div class="small-box padding1010 col-sm-3 blightOrange">
                    <h3><?= isset($purchases_return->total_amount) ? $this->cus->formatMoney(abs($purchases_return->total_amount)) : '$0.00' ?></h3>
                    <p><?= lang('total_return') ?></p>
					<p><small><?= lang('purchases') ?></small></p>
                </div>
				<div class="small-box padding1010 col-sm-3 bblue">
                    <h3><?= isset($purchases->total_amount) ? $this->cus->formatMoney($purchases->total_amount) : '0.00' ?></h3>

                    <p><?= lang('total_amount') ?></p>
                    <p><small><?= '( '.lang('purchases').' - '.lang('return').' )' ?> + <?= lang('expenses') ?> + <?= lang('freight') ?></small></p>
                </div>
				<div class="small-box padding1010 col-sm-3 bdarkGreen">
                    <h3><?= isset($purchases->paid) ? $this->cus->formatMoney($purchases->paid) : '0.00' ?></h3>

                    <p><?= lang('total_paid') ?></p>
					<p><small><?= lang('purchases') ?> + <?= lang('expenses') ?> + <?= lang('freight') ?></small></p>
                </div>
            </div>
            <div class="col-sm-12">
				 <div class="small-box padding1010 col-sm-3 borange">
                    <h3><?= (isset($purchases->total_amount) || isset($purchases->paid)) ? $this->cus->formatMoney($purchases->total_amount - $purchases->paid) : '0.00' ?></h3>

                    <p><?= lang('balance').' '.lang('amount') ?></p>
					<p><small><?= lang('purchases') ?> + <?= lang('expenses') ?> + <?= lang('freight') ?></small></p>
                </div>
				<div class="small-box col-sm-3 padding1010 bblue">
					<div class="inner clearfix">
						<a>
							<h3><?= $total_purchases + $total_expense ?></h3>
							<p><?= lang('total').' '.lang('transaction') ?></p>
							<p><small><?= lang('purchases') ?> + <?= lang('return') ?> + <?= lang('expenses') ?> + <?= lang('freight') ?></small></p>
						</a>
					</div>
				</div>
            </div>
        </div>
    </div>
</div>
<div style="clear:both;"></div>
<ul id="myTab" class="nav nav-tabs no-print">
    <li class=""><a href="#purcahses-con" class="tab-grey"><?= lang('purchases') ?></a></li>
	<li class=""><a href="#expenses-con" class="tab-grey"><?= lang('expenses') ?></a></li>
	<li class=""><a href="#freights-con" class="tab-grey"><?= lang('freights') ?></a></li>
    <li class=""><a href="#payments-con" class="tab-grey"><?= lang('payments') ?></a></li>
</ul>

<div class="tab-content">
    <div id="purcahses-con" class="tab-pane fade in">
        <?php
        $v = "&supplier=" . $user_id;
        if ($this->input->post('submit_purchase_report')) {
            if ($this->input->post('biller')) {
                $v .= "&biller=" . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= "&warehouse=" . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= "&user=" . $this->input->post('user');
            }
            if ($this->input->post('start_date')) {
                $v .= "&start_date=" . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= "&end_date=" . $this->input->post('end_date');
            }
        }
        ?>
        <script>
        $(document).ready(function () {
            oTable = $('#PoRData').dataTable({
				"aaSorting": [[0, "desc"]],
				"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
				"iDisplayLength": <?= $Settings->rows_per_page ?>,
				'bProcessing': true, 'bServerSide': true,
				'sAjaxSource': '<?= site_url('reports/getPurchasesReport/?v=1' . $v) ?>',
				'fnServerData': function (sSource, aoData, fnCallback) {
					aoData.push({
						"name": "<?= $this->security->get_csrf_token_name() ?>",
						"value": "<?= $this->security->get_csrf_hash() ?>"
					});
					$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
				},
				'fnRowCallback': function (nRow, aData, iDisplayIndex) {
					nRow.id = aData[0];
					nRow.className = (aData[9] > 0) ? "purchase_link2 warning" : "purchase_link2";
					return nRow;
				},
				"aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, null,null,null, {
					"bSearchable": false,
					"mRender": pqFormat
				}, {"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status},{"mRender": pay_status},{"bSortable": false,"mRender": attachment}],
				"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
					var gtotal = 0, returned= 0, paid = 0, balance = 0;
					for (var i = 0; i < aaData.length; i++) {
						gtotal += parseFloat(aaData[aiDisplay[i]][8]);
						returned += parseFloat(aaData[aiDisplay[i]][9]);
						paid += parseFloat(aaData[aiDisplay[i]][10]);
						balance += parseFloat(aaData[aiDisplay[i]][11]);
					}
					var nCells = nRow.getElementsByTagName('th');
					nCells[8].innerHTML = currencyFormat(parseFloat(gtotal));
					nCells[9].innerHTML = currencyFormat(parseFloat(returned));
					nCells[10].innerHTML = currencyFormat(parseFloat(paid));
					nCells[11].innerHTML = currencyFormat(parseFloat(balance));
				}
			}).fnSetFilteringDelay().dtFilter([
				{column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
				{column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
				{column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
				{column_number: 4, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
				{column_number: 5, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
				{column_number: 6, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
				{column_number: 12, filter_default_label: "[<?=lang('purchase_status');?>]", filter_type: "text", data: []},
				{column_number: 13, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
			], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#form').hide();
            $('.toggle_down').click(function () {
                $("#form").slideDown();
                return false;
            });
            $('.toggle_up').click(function () {
                $("#form").slideUp();
                return false;
            });
			
			$(document).on('ifChecked ifUnchecked', '.multi-select', function(event) {
				var multi = [];
				$('.multi-select:checked').each(function() {
					multi.push($(this).val());
				});
			   $("#print").attr("window", multi);
			});
			
			$('#print').click(function (event) {
				event.preventDefault();
				window.location.href = "<?=site_url('reports/print_supplier_purchases/?v=2' . $v)?>";
				return false;
			});
			
			$('#multi_payment').live('click',function(){
				var purchase_id = '';
				var intRegex = /^\d+$/;
				var i = 0;
				$('.input-xs').each(function(){
					if ($(this).is(':checked') && intRegex.test($(this).val())) {
						if(i==0){
							purchase_id += $(this).val();
							i=1;
						}else{
							purchase_id += "PurchaseID"+$(this).val();
						}
						
					}
				});
				if(purchase_id==''){
					alert("<?= lang('no_sale_selected') ?>")
					return false;
				}else{
					var link = '<?= anchor('purchases/add_multi_payment/#######', '<i class="icon fa fa-usd"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="multi_payment"')?>';
					var add_payment_link = link.replace("#######", purchase_id);
					$("#payment_box").html(add_payment_link);
					$('.multi_payment').click();
					$("#payment_box").html('<a href="#" id="multi_payment" class="tip" ​data-action="multi_payment" title="<?= lang('add_payment') ?>"><i class="icon fa fa-usd"></i></a>');		
					return false;
				}
			});
			
        });
        </script>

        <div class="box purchases-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-star nb"></i> <?= lang('purchases_report'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
                }
                ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                                <i class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                                <i class="icon fa fa-toggle-down"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="box-icon">
                    <ul class="btn-tasks">
						<li class="dropdown">
							<a href="#" id="print" class="tip" title="<?= lang('print') ?>">
								<i class="icon fa fa-print"></i>
							</a>
						</li>
                        <li class="dropdown">
                            <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                                <i class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                                <i class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
						<li class="dropdown" id="payment_box">
							<a href="#" id="multi_payment"  class="tip" ​data-action="multi_payment" title="<?= lang('add_payment') ?>">
								<i class="icon fa fa-usd"></i>
							</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="form">

                            <?php echo form_open("reports/supplier_report/" . $user_id); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                        <?php
                                        $us[""] = lang('select').' '.lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . " " . $user->last_name;
                                        }
                                        echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                        ?>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                        <?php
                                        $wh[""] = lang('select').' '.lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                        ?>
                                    </div>
                                </div>
								
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label" for="biller"><?= lang("biller"); ?></label>
										<?php
										$bl[""] = lang('select').' '.lang('biller');
										if (isset($billers) && $billers != false) {
											foreach ($billers as $biller) {
												$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
											}
										}
										
										echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
										?>
									</div>
								</div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("start_date", "start_date"); ?>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("end_date", "end_date"); ?>
                                        <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                class="controls"> <?php echo form_submit('submit_purchase_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>

                        </div>
                        <div class="clearfix"></div>


                        <div class="table-responsive">
                            <table id="PoRData"
								   class="table table-bordered table-hover table-striped table-condensed reports-table">
								<thead>
								<tr>
									<th style="min-width:30px; width: 30px; text-align: center;">
										<input class="checkbox checkft" type="checkbox" name="check"/>
									</th>
									<th><?= lang("date"); ?></th>
									<th><?= lang("reference_no"); ?></th>
									<th><?= lang("biller"); ?></th>
									<th><?= lang("project"); ?></th>
									<th><?= lang("warehouse"); ?></th>
									<th><?= lang("supplier"); ?></th>
									<th><?= lang("product_qty"); ?></th>
									<th><?= lang("grand_total"); ?></th>
									<th><?= lang("returned"); ?></th>
									<th><?= lang("paid"); ?></th>
									<th><?= lang("balance"); ?></th>
									<th><?= lang("purchase_status"); ?></th>
									<th><?= lang("payment_status"); ?></th>
									<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
									<th><?= lang("product_qty"); ?></th>
									<th><?= lang("grand_total"); ?></th>
									<th><?= lang("returned"); ?></th>
									<th><?= lang("paid"); ?></th>
									<th><?= lang("balance"); ?></th>
									<th></th>
									<th></th>
									<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								</tr>
								</tfoot>
							</table>
						</div>

                </div>
            </div>
        </div>
    </div>
</div>



<div id="expenses-con" class="tab-pane fade in">
    <?php
    $e = "&supplier=" . $user_id;
    if ($this->input->post('submit_expense_report')) {
        if ($this->input->post('expense_user')) {
            $e .= "&user=" . $this->input->post('expense_user');
        }
        if ($this->input->post('expense_start_date')) {
            $e .= "&start_date=" . $this->input->post('expense_start_date');
        }
        if ($this->input->post('expense_end_date')) {
            $e .= "&end_date=" . $this->input->post('expense_end_date');
        }
    }
    ?>
    <script>
    $(document).ready(function () {


       oTable = $('#EXPData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getExpensesReport/?v=1' . $e); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[11];
                nRow.className = "expense_link2";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null,null, null, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, null, null, {"bSortable": false, "mRender": attachment}, {"mRender" : row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][4]);
					paid += parseFloat(aaData[aiDisplay[i]][5]);
					balance += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[4].innerHTML = currencyFormat(total);
				nCells[5].innerHTML = currencyFormat(paid);
				nCells[6].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
    </script>
    <script type="text/javascript">
    $(document).ready(function () {
        $('#expeseForm').hide();
        $('.expensetoggle_down').click(function () {
            $("#expeseForm").slideDown();
            return false;
        });
        $('.expesetoggle_up').click(function () {
            $("#expeseForm").slideUp();
            return false;
        });
    });
    </script>

    <div class="box expenses-table">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('expenses_report'); ?> <?php
				if ($this->input->post('start_date')) {
					echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
				}
				?>
			</h2>

            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a href="#" class="expesetoggle_up tip" title="<?= lang('hide_form') ?>">
                            <i class="icon fa fa-toggle-up"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="expensetoggle_down tip" title="<?= lang('show_form') ?>">
                            <i class="icon fa fa-toggle-down"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                            <i class="icon fa fa-file-pdf-o"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                            <i class="icon fa fa-file-excel-o"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="image1" class="tip" title="<?= lang('save_image') ?>">
                            <i class="icon fa fa-file-picture-o"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?= lang('customize_report'); ?></p>
                    <div id="expeseForm">
                        <?php echo form_open("reports/supplier_report/" . $user_id."/#expenses-con"); ?>
                        <div class="row">

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                    <?php
                                    $us[""] = lang('select').' '.lang('user');
                                    foreach ($users as $user) {
                                        $us[$user->id] = $user->first_name . " " . $user->last_name;
                                    }
                                    echo form_dropdown('expense_user', $us, (isset($_POST['expense_user']) ? $_POST['expense_user'] : ""), 'class="form-control" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("start_date", "start_date"); ?>
                                    <?php echo form_input('expense_start_date', (isset($_POST['expense_start_date']) ? $_POST['expense_start_date'] : ""), 'class="form-control date"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("end_date", "end_date"); ?>
                                    <?php echo form_input('expense_end_date', (isset($_POST['expense_end_date']) ? $_POST['expense_end_date'] : ""), 'class="form-control date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div
                            class="controls"> <?php echo form_submit('submit_expense_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                        </div>
                        <?php echo form_close(); ?>

                    </div>
                    <div class="clearfix"></div>

                    <div class="table-responsive">
                        <table id="EXPData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
							<thead>
							<tr class="active">
								<th class="col-xs-2"><?= lang("date"); ?></th>
								<th class="col-xs-2"><?= lang("reference"); ?></th>
								<th class="col-xs-2"><?= lang("biller"); ?></th>
								<th class="col-xs-2"><?= lang("supplier"); ?></th>
								<th class="col-xs-1"><?= lang("amount"); ?></th>
								<th class="col-xs-3"><?= lang("paid"); ?></th>
								<th class="col-xs-3"><?= lang("balance"); ?></th>
								<th class="col-xs-1"><?= lang("note"); ?></th>
								<th class="col-xs-2"><?= lang("created_by"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
								</th>
								<th class="col-xs-2"><?= lang("status"); ?></th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
							</tr>
							</tbody>
							<tfoot class="dtFilter">
							<tr class="active">
								<th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
								
							</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style type="text/css">
	#EXPData th:nth-child(8), #EXPData td:nth-child(8){
		display:none !important;
	}
	#EXPData th:nth-child(9), #EXPData td:nth-child(9){
		display:none !important;
	}
	#EXPData th:nth-child(10), #EXPData td:nth-child(10){
		display:none !important;
	}
</style>


<div id="freights-con" class="tab-pane fade in">
    <?php
    $f = "&supplier=" . $user_id;
    if ($this->input->post('submit_freight_report')) {
        if ($this->input->post('freight_user')) {
            $f .= "&user=" . $this->input->post('freight_user');
        }
        if ($this->input->post('freight_start_date')) {
            $f .= "&start_date=" . $this->input->post('freight_start_date');
        }
        if ($this->input->post('freight_end_date')) {
            $f .= "&end_date=" . $this->input->post('freight_end_date');
        }
    }
    ?>
    <script>
    $(document).ready(function () {
		oTable = $('#FreightRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getFreightReports/?v=1' . $f) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[10];
                nRow.className = "purchase_link2";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null, null, null,null,null, {"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": pay_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][6]);
                    paid += parseFloat(aaData[aiDisplay[i]][7]);
                    balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[7].innerHTML = currencyFormat(parseFloat(paid));
                nCells[8].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
    </script>
    <script type="text/javascript">
    $(document).ready(function () {
        $('#freightForm').hide();
        $('.freighttoggle_down').click(function () {
            $("#freightForm").slideDown();
            return false;
        });
        $('.freighttoggle_up').click(function () {
            $("#freightForm").slideUp();
            return false;
        });
    });
    </script>

    <div class="box freights-table">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('expenses_report'); ?> <?php
				if ($this->input->post('start_date')) {
					echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
				}
				?>
			</h2>

            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a href="#" class="freighttoggle_up tip" title="<?= lang('hide_form') ?>">
                            <i class="icon fa fa-toggle-up"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="freighttoggle_down tip" title="<?= lang('show_form') ?>">
                            <i class="icon fa fa-toggle-down"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                            <i class="icon fa fa-file-pdf-o"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                            <i class="icon fa fa-file-excel-o"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="image1" class="tip" title="<?= lang('save_image') ?>">
                            <i class="icon fa fa-file-picture-o"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?= lang('customize_report'); ?></p>
                    <div id="freightForm">
                        <?php echo form_open("reports/supplier_report/" . $user_id."/#freights-con"); ?>
                        <div class="row">

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                    <?php
                                    $us[""] = lang('select').' '.lang('user');
                                    foreach ($users as $user) {
                                        $us[$user->id] = $user->first_name . " " . $user->last_name;
                                    }
                                    echo form_dropdown('freight_user', $us, (isset($_POST['freight_user']) ? $_POST['freight_user'] : ""), 'class="form-control" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("start_date", "start_date"); ?>
                                    <?php echo form_input('freight_start_date', (isset($_POST['freight_start_date']) ? $_POST['freight_start_date'] : ""), 'class="form-control date"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("end_date", "end_date"); ?>
                                    <?php echo form_input('freight_end_date', (isset($_POST['freight_end_date']) ? $_POST['freight_end_date'] : ""), 'class="form-control date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div
                            class="controls"> <?php echo form_submit('submit_freight_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                        </div>
                        <?php echo form_close(); ?>

                    </div>
                    <div class="clearfix"></div>

                    <div class="table-responsive">
                        <table id="FreightRData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									<th><?= lang("date"); ?></th>
									<th><?= lang("reference_no"); ?></th>
									<th><?= lang("biller"); ?></th>
									<th><?= lang("project"); ?></th>
									<th><?= lang("warehouse"); ?></th>
									<th><?= lang("supplier"); ?></th>
									<th><?= lang("grand_total"); ?></th>
									<th><?= lang("paid"); ?></th>
									<th><?= lang("balance"); ?></th>
									<th style="width:100px"><?= lang("payment_status"); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
								</tr>
							</tbody>
							<tfoot class="dtFilter">
								<tr class="active">
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th><?= lang("grand_total"); ?></th>
									<th><?= lang("paid"); ?></th>
									<th><?= lang("balance"); ?></th>
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


<div id="payments-con" class="tab-pane fade in">
    <?php
    $p = "&supplier=" . $user_id;
    if ($this->input->post('submit_payment_report')) {
        if ($this->input->post('pay_user')) {
            $p .= "&user=" . $this->input->post('pay_user');
        }
        if ($this->input->post('pay_start_date')) {
            $p .= "&start_date=" . $this->input->post('pay_start_date');
        }
        if ($this->input->post('pay_end_date')) {
            $p .= "&end_date=" . $this->input->post('pay_end_date');
        }
    }
    ?>
    <script>
    $(document).ready(function () {
        var pb = <?= json_encode($pb); ?>;
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : x) : x;
        }

        oTable = $('#PayRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getPaymentsReport/?v=1' . $p) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, {"bVisible": false}, null,{"mRender": fld}, {"mRender": paid_by}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[9];
				if(aData[8]=='returned'){
					nRow.className = "payment_link2 warning";
				}else{
					nRow.className = "payment_link2";
				}
                
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, discount= 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
					discount += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(parseFloat(total));
				nCells[6].innerHTML = currencyFormat(parseFloat(discount));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('payment_ref');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('purchase_ref');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('date_ref');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
        ], "footer");
    });
    </script>
    <script type="text/javascript">
    $(document).ready(function () {
        $('#payform').hide();
        $('.paytoggle_down').click(function () {
            $("#payform").slideDown();
            return false;
        });
        $('.paytoggle_up').click(function () {
            $("#payform").slideUp();
            return false;
        });
    });
    </script>

    <div class="box payments-table">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-money nb"></i><?= lang('payments_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a href="#" class="paytoggle_up tip" title="<?= lang('hide_form') ?>">
                            <i class="icon fa fa-toggle-up"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="paytoggle_down tip" title="<?= lang('show_form') ?>">
                            <i class="icon fa fa-toggle-down"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                            <i class="icon fa fa-file-pdf-o"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                            <i class="icon fa fa-file-excel-o"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="image1" class="tip" title="<?= lang('save_image') ?>">
                            <i class="icon fa fa-file-picture-o"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">

                    <p class="introtext"><?= lang('customize_report'); ?></p>

                    <div id="payform">

                        <?php echo form_open("reports/supplier_report/" . $user_id."/#payments-con"); ?>
                        <div class="row">

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                    <?php
                                    $us[""] = lang('select').' '.lang('user');
                                    foreach ($users as $user) {
                                        $us[$user->id] = $user->first_name . " " . $user->last_name;
                                    }
                                    echo form_dropdown('pay_user', $us, (isset($_POST['pay_user']) ? $_POST['pay_user'] : ""), 'class="form-control" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("start_date", "start_date"); ?>
                                    <?php echo form_input('pay_start_date', (isset($_POST['pay_start_date']) ? $_POST['pay_start_date'] : ""), 'class="form-control date"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("end_date", "end_date"); ?>
                                    <?php echo form_input('pay_end_date', (isset($_POST['pay_end_date']) ? $_POST['pay_end_date'] : ""), 'class="form-control date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div
                            class="controls"> <?php echo form_submit('submit_payment_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                        </div>
                        <?php echo form_close(); ?>

                    </div>
                    <div class="clearfix"></div>

                    <div class="table-responsive">
                        <table id="PayRData"
                        class="table table-bordered table-hover table-striped table-condensed reports-table">

                        <thead>
                            <tr>
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("payment_ref"); ?></th>
                                <th><?= lang("sale_ref"); ?></th>
                                <th><?= lang("purchase_ref"); ?></th>
								<th><?= lang("date_ref"); ?></th>
                                <th><?= lang("paid_by"); ?></th>
                                <th><?= lang("amount"); ?></th>
								<th><?= lang("discount"); ?></th>
                                <th><?= lang("type"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
								<th></th>
                                <th></th>
                                <th><?= lang("amount"); ?></th>
								<th><?= lang("discount"); ?></th>
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


<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#pdf').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPurchasesReport/pdf/?v=1'.$v)?>";
        return false;
    });
    $('#xls').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPurchasesReport/0/xls/?v=1'.$v)?>";
        return false;
    });
    $('#image').click(function (event) {
        event.preventDefault();
        html2canvas($('.purchases-table'), {
            onrendered: function (canvas) {
                var img = canvas.toDataURL()
                window.open(img);
            }
        });
        return false;
    });
    $('#pdf1').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPaymentsReport/pdf/?v=1'.$p)?>";
        return false;
    });
    $('#xls1').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPaymentsReport/0/xls/?v=1'.$p)?>";
        return false;
    });
    $('#image1').click(function (event) {
        event.preventDefault();
        html2canvas($('.payments-table'), {
            onrendered: function (canvas) {
                var img = canvas.toDataURL()
                window.open(img);
            }
        });
        return false;
    });
});
</script>
<style type="text/css">
	<?php if(!$Settings->project){ ?>
		#PoRData th:nth-child(5), #PoRData td:nth-child(5){
			display:none !important;
		}
	<?php } ?>
		#PoRData th:nth-child(8), #PoRData td:nth-child(8){
			display:none !important;
		}
		#PoRData th:nth-child(15), #PoRData td:nth-child(15){
			display:none !important;
		}

</style>
