<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="col-md-2 col-sm-6 col-xs-12">
                <div class="layout_green">
                    <div><span class="fa fa-line-chart" id ="icon"></span></div>
                    <div style="margin-top: 12px"><?= lang('sales_amount') ?></div>
                    <div style="font-size: 25px"><b><?= isset($sales->total_amount) ? $this->cus->formatMoney($sales->total_amount) : '0.00' ?></b></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 col-xs-12">
                <div class="layout_blue">
                    <div><span class="fa fa-money" id ="icon"></span></div>
                    <div style="margin-top: 12px"><?= lang('total_paid') ?></div>
                    <div style="font-size: 25px"><b><?= isset($sales->paid) ? $this->cus->formatMoney($sales->paid) : '0.00' ?></b></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 col-xs-12">
                <div class="layout_orange">
                    <div><span class="fa fa-usd" id ="icon"></span></div>
                    <div style="margin-top: 12px"><?= lang('due_amount') ?></div>
                    <div style="font-size: 25px"><b><?= (isset($sales->total_amount) || isset($sales->paid)) ? $this->cus->formatMoney($sales->total_amount - $sales->paid) : '0.00' ?></b></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 col-xs-12">
                <div class="layout_purple">
                    <div><span class="fa fa-signal" id ="icon"></span></div>
                    <div style="margin-top: 12px"><?= lang('total_sales') ?></div>
                    <div style="font-size: 25px"><b><?= $total_sales ?></b></div>
                </div>
            </div>
			    <?php if($this->config->item("quotation")==true){ ?>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                        <div class="layout_yellow">
                            <div><span class="fa fa-file-text-o" id ="icon"></span></div>
                            <div style="margin-top: 12px"><?= lang('total_quotes') ?></div>
                            <div style="font-size: 25px"><b><?= $total_quotes ?></b></div>
                        </div>
                    </div>
				<?php } ?>
            <div class="col-md-2 col-sm-6 col-xs-12">
                <div class="layout_pink">
                    <div><span class="fa fa-recycle" id ="icon"></span></div>
                    <div style="margin-top: 12px"><?= lang('total_returns') ?></div>
                    <div style="font-size: 25px"><b><?= $total_returns ?></b></div>
                </div>
            </div>  
        </div>
    </div>
</div>

<ul id="myTab" class="nav nav-tabs no-print">
    <li class=""><a href="#sales-con" class="tab-grey"><?= lang('sales') ?></a></li>
    <li class=""><a href="#payments-con" class="tab-grey"><?= lang('payments') ?></a></li>
	<?php if($this->config->item("quotation")==true){ ?>
		<li class=""><a href="#quotes-con" class="tab-grey"><?= lang('quotes') ?></a></li>
	<?php } ?>
    <li class=""><a href="#deposits-con" class="tab-grey"><?= lang('deposits') ?></a></li>
</ul>

<div class="tab-content">
    <div id="sales-con" class="tab-pane fade in">

        <?php
        $v = "&customer=" . $user_id;
        if ($this->input->post('submit_sale_report')) {
            if ($this->input->post('biller')) {
                $v .= "&biller=" . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= "&warehouse=" . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= "&user=" . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= "&serial=" . $this->input->post('serial');
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
            oTable = $('#SlRData').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= site_url('reports/getSalesReport/?v=1' . $v) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                    nRow.id = aData[0];
                    nRow.className = (aData[7] > 0) ? "invoice_link2 warning" : "invoice_link2";
                    return nRow;
                },
                "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, null, {
                    "bSearchable": false,
                    "mRender": pqFormat
                }, {"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                    var gtotal = 0, paid = 0, discount=0, balance = 0, greturn = 0;
                    for (var i = 0; i < aaData.length; i++) {
                        gtotal += parseFloat(aaData[aiDisplay[i]][6]);
						greturn += parseFloat(aaData[aiDisplay[i]][7]);
                        paid += parseFloat(aaData[aiDisplay[i]][8]);
						discount += parseFloat(aaData[aiDisplay[i]][9]);
                        balance += parseFloat(aaData[aiDisplay[i]][10]);
                    }
                    var nCells = nRow.getElementsByTagName('th');
                    nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
					nCells[7].innerHTML = currencyFormat(parseFloat(greturn));
                    nCells[8].innerHTML = currencyFormat(parseFloat(paid));
					nCells[9].innerHTML = currencyFormat(parseFloat(discount));
                    nCells[10].innerHTML = currencyFormat(parseFloat(balance));
                }
            }).fnSetFilteringDelay().dtFilter([
                {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
                {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
                {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
                {column_number: 11, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
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
				window.location.href = "<?=site_url('reports/print_customer_sales/?v=2' . $v)?>";
				return false;
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
					var link = '<?= anchor('sales/add_multi_payment/#######', '<i class="icon fa fa-usd"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="multi_payment"')?>';
					var add_payment_link = link.replace("#######", sale_id);
					$("#payment_box").html(add_payment_link);
					$('.multi_payment').click();
					$("#payment_box").html('<a href="#" id="multi_payment" class="tip" data-action="multi_payment" title="<?= lang('add_payment') ?>"><i class="icon fa fa-usd"></i></a>');		
					return false;
				}
			});
		
        });
        </script>

        <div class="box sales-table">
            <div class="box-header">
                <!-- <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('customer_sales_report'); ?> <?php
                    if ($this->input->post('start_date')) {
                        echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
                    }
                    ?>
                </h2> -->
                <div class="sub_menu"></div>
                <div class="sub_menu">
                    <a href="#" id="print" class="tip btn btn-success btn-block box_sub_menu" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>&nbsp;</i><?=lang('print')?>
                    </a>
                </div>
                <div class="sub_menu">
                    <a href="#" id="xls" class="tip btn btn-warning btn-block box_sub_menu" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>&nbsp;</i><?=lang('download_xls')?>
                    </a>
                </div>
                <div class="sub_menu">
                    <a href="#" class="toggle_down tip btn btn-info btn-block box_sub_menu" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-eye"></i>&nbsp;</i><?=lang('show_form')?>
                    </a>
                </div>
                <div class="sub_menu">
                    <a href="#" class="toggle_up tip btn btn-danger btn-block box_sub_menu" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-eye-slash"></i>&nbsp;</i><?=lang('hide_form')?>
                    </a>
                </div>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <h2 class="blue">
                                <i class="icon fa fa-heart tip"></i><?= lang('customer_sales_report'); ?>
                            </h2>
                        </li>

                        <!-- <li class="dropdown">
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
                                <i
                                class="icon fa fa-file-pdf-o"></i>
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
							<a href="#" id="multi_payment"  class="tip" data-action="multi_payment" title="<?= lang('add_payment') ?>">
								<i class="icon fa fa-usd"></i>
							</a>
                        </li> -->
						
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- <p class="introtext"><?= lang('customize_report'); ?></p> -->

                        <div id="form">

                            <?php echo form_open("reports/customer_report/" . $user_id); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                        <?php
                                        $us[""] = lang('select').' '.lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . " " . $user->last_name;
                                        }
                                        echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                        <?php
                                        $bl[""] = lang('select').' '.lang('biller');
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
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
                                <?php if($Settings->product_serial) { ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('serial_no', 'serial'); ?>
                                            <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("start_date", "start_date"); ?>
                                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("end_date", "end_date"); ?>
                                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div
                                    class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>

                            </div>
                            <div class="clearfix"></div>

                            <div class="table-responsive">
                                <table id="SlRData"
                                class="table table-bordered table-hover table-striped table-condensed reports-table reports-table">
                                <thead>
                                    <tr>
										<th style="min-width:30px; width: 30px; text-align: center;">
											<input class="checkbox checkft" type="checkbox" name="check"/>
										</th>
                                        <th><?= lang("date"); ?></th>
                                        <th><?= lang("reference_no"); ?></th>
                                        <th><?= lang("biller"); ?></th>
                                        <th><?= lang("customer"); ?></th>
                                        <th><?= lang("product_qty"); ?></th>
                                        <th><?= lang("grand_total"); ?></th>
										<th><?= lang("return"); ?></th>
                                        <th><?= lang("paid"); ?></th>
										<th><?= lang("discount"); ?></th>
                                        <th><?= lang("balance"); ?></th>
                                        <th><?= lang("payment_status"); ?></th>
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
                                        <th><?= lang("product_qty"); ?></th>
                                        <th><?= lang("grand_total"); ?></th>
										<th><?= lang("return"); ?></th>
                                        <th><?= lang("paid"); ?></th>
										<th><?= lang("discount"); ?></th>
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
        $p = "&customer=" . $user_id;
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
                "aoColumns": [{"mRender": fld}, null, null, {"bVisible": false},{"mRender": fld}, {"mRender": paid_by}, {"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": row_status}],
                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                    nRow.id = aData[9];
                    nRow.className = "payment_link";
                    if (aData[8] == 'returned' || aData[6] < 0) {
                        nRow.className = "payment_link danger";
                    }
                    return nRow;
                },
                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                    var total = 0, discount=0;
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
                {column_number: 2, filter_default_label: "[<?=lang('sale_ref');?>]", filter_type: "text", data: []},
                {column_number: 3, filter_default_label: "[<?=lang('date_ref');?>]", filter_type: "text", data: []},
                {column_number: 4, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
				{column_number: 7, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
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
                <div class="sub_menu"></div>
                <div class="sub_menu">
                    <a href="#" id="xls1" class="tip btn btn-warning btn-block box_sub_menu" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>&nbsp;</i><?=lang('download_xls')?>
                    </a>
                </div>
                <div class="sub_menu">
                    <a href="#" class="paytoggle_down tip btn btn-info btn-block box_sub_menu" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-eye"></i>&nbsp;</i><?=lang('show_form')?>
                    </a>
                </div>
                <div class="sub_menu">
                    <a href="#" class="paytoggle_up tip btn btn-danger btn-block box_sub_menu" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-eye-slash"></i>&nbsp;</i><?=lang('hide_form')?>
                    </a>
                </div>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <h2 class="blue"><i class="icon fa fa-money tip"></i><?= lang('customer_payments_report'); ?>
                                <?php
                                    if ($this->input->post('start_date')) {
                                    echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
                                    }
                                ?>
                            </h2>
                        </li>

                        <!-- <li class="dropdown">
                            <a href="#" class="paytoggle_up tip" title="<?= lang('hide_form') ?>">
                                <i
                                class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="paytoggle_down tip" title="<?= lang('show_form') ?>">
                                <i
                                class="icon fa fa-toggle-down"></i>
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
                        </li> -->
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- <p class="introtext"><?= lang('customize_report'); ?></p> -->

                        <div id="payform">

                            <?php echo form_open("reports/customer_report/" . $user_id."/#payments-con"); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                        <?php
                                        $us[""] = lang('select').' '.lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . " " . $user->last_name;
                                        }
                                        echo form_dropdown('pay_user', $us, (isset($_POST['pay_user']) ? $_POST['pay_user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("start_date", "start_date"); ?>
                                        <?php echo form_input('pay_start_date', (isset($_POST['pay_start_date']) ? $_POST['pay_start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("end_date", "end_date"); ?>
                                        <?php echo form_input('pay_end_date', (isset($_POST['pay_end_date']) ? $_POST['pay_end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
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
                            class="table table-bordered table-hover table-striped table-condensed reports-table reports-table">

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
                                    <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
<div id="quotes-con" class="tab-pane fade in">
    <script type="text/javascript">
    $(document).ready(function () {
        oTable = $('#QuRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getQuotesReport/?v=1&customer='.$user_id) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{"mRender": fld}, null, null, null, {
                "bSearchable": false,
                "mRender": pqFormat
            }, {"mRender": currencyFormat}, {"mRender": row_status}],
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('grand_total');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
    </script>
    <div class="box">
        <div class="box-header">
            <div class="sub_menu"></div>
            <div class="sub_menu">
                <a href="#" id="xls2" class="tip btn btn-success btn-block box_sub_menu" title="<?= lang('download_xls') ?>">
                    <i class="icon fa fa-file-excel-o"></i>&nbsp;</i><?=lang('download_xls')?>
                </a>
            </div>

            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <h2 class="blue"><i class="icon fa fa-heart-o tip"></i><?= lang('quotes'); ?></h2>
                    </li>   

                    <!-- <li class="dropdown">
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
                        <a href="#" id="image1" class="tip image" title="<?= lang('save_image') ?>">
                            <i class="icon fa fa-file-picture-o"></i>
                        </a>
                    </li> -->
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <!-- <p class="introtext"><?php echo lang('list_results'); ?></p> -->

                    <div class="table-responsive">
                        <table id="QuRData" class="table table-bordered table-hover table-striped table-condensed reports-table">
                            <thead>
                                <tr>
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("reference_no"); ?></th>
                                    <th><?= lang("biller"); ?></th>
                                    <th><?= lang("customer"); ?></th>
                                    <th><?= lang("product_qty"); ?></th>
                                    <th><?= lang("grand_total"); ?></th>
                                    <th><?= lang("status"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7"
                                    class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?= lang("product_qty"); ?></th>
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
<div id="deposits-con" class="tab-pane fade in">
    <script type="text/javascript">
    $(document).ready(function () {
        oTable = $('#DepData').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= site_url('reports/get_deposits/'.$user_id) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                "aoColumns": [{"mRender": fld}, {"mRender": currencyFormat}, null, null, {"mRender": decode_html}]
            }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('amount');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
        ], "footer");
    });
    </script>
    <div class="box">
        <div class="box-header">
            <div class="sub_menu"></div>
            <div class="sub_menu">
                <a href="#" id="xls3" class="tip btn btn-success btn-block box_sub_menu" title="<?= lang('download_xls') ?>">
                    <i class="icon fa fa-file-excel-o"></i>&nbsp;</i><?=lang('download_xls')?>
                </a>
            </div>

            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <h2 class="blue"><i class="icon fa fa-money tip"></i><?= lang('deposits'); ?></h2>
                    </li>

                    <!-- <li class="dropdown">
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
                        <a href="#" id="image1" class="tip image" title="<?= lang('save_image') ?>">
                            <i class="icon fa fa-file-picture-o"></i>
                        </a>
                    </li> -->
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <!-- <p class="introtext"><?php echo lang('list_results'); ?></p> -->

                    <div class="table-responsive">
                        <table id="DepData" class="table table-bordered table-condensed table-hover table-striped reports-table">
                            <thead>
                            <tr class="primary">
                                <th class="col-xs-2"><?= lang("date"); ?></th>
                                <th class="col-xs-1"><?= lang("amount"); ?></th>
                                <th class="col-xs-1"><?= lang("paid_by"); ?></th>
                                <th class="col-xs-2"><?= lang("created_by"); ?></th>
                                <th class="col-xs-6"><?= lang("note"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                            <tr class="primary">
                                <th class="col-xs-2"></th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-2"></th>
                                <th class="col-xs-6"></th>
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
	#SlRData th:nth-child(6), 
	#SlRData td:nth-child(6),
	#QuRData th:nth-child(6),
	#QuRData td:nth-child(6)
	{
		display:none;
	}
</style>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#pdf').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getSalesReport/pdf/?v=1'.$v)?>";
        return false;
    });
    $('#xls').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getSalesReport/0/xls/?v=1'.$v)?>";
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
    $('#xls2').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getQuotesReport/0/xls/?v=1'.$p)?>";
        return false;
    });
    $('#xls3').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPaymentsReport/0/xls/?v=1'.$p)?>";
        return false;
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

<style>
    .layout_orange{
        width: 100%;
        height: 100px;
        background: #F85118;
        color: #fff;
        font-size: 14px;
        text-align: left;
        padding: 10px 0px 0px 7px;
        margin: 10px 110px 20px 0px;
        border-radius: 5px;
        display: inline-block;
    }
    .layout_green{
        width: 100%;
        height: 100px;
        background: #056D15;
        color: #fff;
        font-size: 14px;
        text-align: left;
        padding: 10px 0px 0px 7px;
        margin: 10px 110px 20px 0px;
        border-radius: 5px;
    }
    .layout_blue{
        width: 100%;
        height: 100px;
        background: #059DE5;
        color: #fff;
        font-size: 14px;
        text-align: left;
        padding: 10px 0px 0px 7px;
        margin: 10px 110px 20px 0px;
        border-radius: 5px;
    }
    .layout_purple{
        width: 100%;
        height: 100px;
        background: #7D1DF1;
        color: #fff;
        font-size: 14px;
        text-align: left;
        padding: 10px 0px 0px 7px;
        margin: 10px 110px 20px 0px;
        border-radius: 5px;
    }
    .layout_yellow{
        width: 100%;
        height: 100px;
        background: #fabb3d;
        color: #fff;
        font-size: 14px;
        text-align: left;
        padding: 10px 0px 0px 7px;
        margin: 10px 110px 20px 0px;
        border-radius: 5px;
    }
    .layout_pink{
        width: 100%;
        height: 100px;
        background: #CD10C2;
        color: #fff;
        font-size: 14px;
        text-align: left;
        padding: 10px 0px 0px 7px;
        margin: 10px 110px 20px 0px;
        border-radius: 5px;
        
    }
    #icon{
        font-size: 55px;
        margin: 11px 7px 0px 0px;
        opacity: 30%;
        float: right;
    }
    
        
</style>