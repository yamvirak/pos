<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";

if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
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
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}



?>
<script>
    $(document).ready(function () {
        oTable = $('#consignmentData').dataTable({
            "aaSorting": [[11, "desc"],[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getConsignmentReport/?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				nRow.id = aData[11]; 
				nRow.className = "consignment_link";
				return nRow;
			},
            "aoColumns": [{"mRender": fld}, 
			null, 
			null, 
			null,
			null,
			null, 
			{"mRender": currencyFormat},
			{"mRender": currencyFormat},
			{"mRender": currencyFormat},
			{"mRender": currencyFormat},
			{"mRender": row_status}],
			
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var gtotal = 0, toal_return = 0, total_sale = 0, balance=0;
				for (var i = 0; i < aaData.length; i++) {
					gtotal += parseFloat(aaData[aiDisplay[i]][6]);
					toal_return += parseFloat(aaData[aiDisplay[i]][7]);
					total_sale += parseFloat(aaData[aiDisplay[i]][8]);
					balance += parseFloat(aaData[aiDisplay[i]][9]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
				nCells[7].innerHTML = currencyFormat(parseFloat(toal_return));
				nCells[8].innerHTML = currencyFormat(parseFloat(total_sale));
				nCells[9].innerHTML = currencyFormat(parseFloat(balance));
			}

        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no')?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer')?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('created_by')?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('status')?>]", filter_type: "text", data: []},
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
    });
</script>

<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('consignments_report'); ?><?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
        </h2> -->
        <div class="sub_menu">&nbsp&nbsp&nbsp&nbsp&nbsp</div>
        <div class="sub_menu">
            <a href="javascript:;" onclick="window.print();" id ="print" 
                class="tip btn btn-success btn-block box_sub_menu" title="<?= lang('print') ?>">
                <i class="icon fa fa-file-fa fa-print">&nbsp;</i><?=lang('print')?>
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
                    <h2 class="blue"><i class="icon fa fa-filter tip"></i><?= lang('consignments_report'); ?></h2>
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
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li> -->
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->

                <div id="form">

                    <?php echo form_open("reports/consignments"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
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
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <table style="margin-top: 5px; width:100%;">
                    <th>
                        <tr>  
							<?php 
								$biller_id = (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller);
								$biller_id_all = lang('all_selected');
								$biller_id_detail = $this->site->getCompanyByID($biller_id);
								if($biller_id_detail){
								?>
								<td class="text_left" style="width: 10%">
									<div>
										<?= !empty($biller_id_detail->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller_id_detail->logo).'" alt="">' : ''; ?>
									</div>
								</td>
								<td></td>
								<td class="text_center" style="width:100%">
									<div>
										<strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller_id_detail->company;?></strong><br>
										<strong style="font-size:20px";><?= $biller_id_detail->name;?></strong>
									</div>
								<br>

								<?php 
								}else{
							?>
							<td></td>
							<td class="text_center" style="width:100%">
								<br>
								<?php } ?>
				
								<?php 
									$sale_type_id = (isset($_POST['sale_type_id']) ? $_POST['sale_type_id'] : false);
									$sale_type_id_all = lang('all_selected');
									//$sale_type_id_detail = $this->site->getSaleTypesByID($sale_type_id);
									if($sale_type_id == 1){
										echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('cash_monthly_report_kh').'</div>';
										echo '<div class="bold">'.lang('cash_monthly_report_en').'</div><br>';
									}elseif($sale_type_id == 2){
										echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('deposit_monthly_report_kh').'</div>';
										echo '<div class="bold">'.lang('deposit_monthly_report_en').'</div><br>';

									}elseif($sale_type_id == 3){
										echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('loan_monthly_report_kh').'</div>';
										echo '<div class="bold">'.lang('loan_monthly_report_en').'</div><br>';

									}else{
										echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('consignments_report_kh').'</div>';
										echo '<div class="bold">'.lang('consignments_report_en').'</div><br>';
								}
								?>
							
							</td> 
                        </tr>
					</th>
                </table>

                <div class="table-responsive">
                    <table id="consignmentData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th class="col-xs-1"><?= lang("date"); ?></th>
                            <th class="col-xs-1"><?= lang("reference_no"); ?></th>
                            <th class="col-xs-1"><?= lang("biller"); ?></th>
							<th class="col-xs-1"><?= lang("warehouse")  ?></th>
							<th class="col-xs-1"><?= lang("customer")  ?></th>
                            <th class="col-xs-1"><?= lang("created_by"); ?></th>
							<th class="col-xs-1"><?= lang("grand_total"); ?></th>
							<th class="col-xs-1"><?= lang("total_return"); ?></th>
							<th class="col-xs-1"><?= lang("total_sale"); ?></th>
							<th class="col-xs-1"><?= lang("balance"); ?></th>
							<th class="col-xs-1"><?= lang("status"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		
		var customer_id = "<?= isset($_POST['customer'])?$_POST['customer']:0 ?>";
		if (customer_id > 0) {
		  $('#customer_id').val(customer_id).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
			  $.ajax({
				type: "get", async: false,
				url: site.base_url+"customers/getCustomer/" + $(element).val(),
				dataType: "json",
				success: function (data) {
				  callback(data[0]);
				}
			  });
			},
			ajax: {
			  url: site.base_url + "customers/suggestions",
			  dataType: 'json',
			  deietMillis: 15,
			  data: function (term, page) {
				return {
				  term: term,
				  limit: 10
				};
			  },
			  results: function (data, page) {
				if (data.results != null) {
				  return {results: data.results};
				} else {
				  return {results: [{id: '', text: 'No Match Found'}]};
				}
			  }
			}
		  });
		}else{
		  $('#customer_id').select2({
			minimumInputLength: 1,
			ajax: {
			  url: site.base_url + "customers/suggestions",
			  dataType: 'json',
			  quietMillis: 15,
			  data: function (term, page) {
				return {
				  term: term,
				  limit: 10
				};
			  },
			  results: function (data, page) {
				if (data.results != null) {
				  return {results: data.results};
				} else {
				  return {results: [{id: '', text: 'No Match Found'}]};
				}
			  }
			}
		  });
		}
		
		
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getConsignmentReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getConsignmentReport/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>
