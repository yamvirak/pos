<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
  } */
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('project')) {
    $v .= "&project=" . $this->input->post('project');
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

if ($this->input->post('vehicle_model')) {
    $v .= "&vehicle_model=" . $this->input->post('vehicle_model');
}
if ($this->input->post('vehicle_plate')) {
    $v .= "&vehicle_plate=" . $this->input->post('vehicle_plate');
}
if ($this->input->post('vehicle_vin')) {
    $v .= "&vehicle_vin=" . $this->input->post('vehicle_vin');
}
if ($this->input->post('mechanic')) {
    $v .= "&mechanic=" . $this->input->post('mechanic');
}
if ($this->input->post('sale_type')) {
    $v .= "&sale_type=" . $this->input->post('sale_type');
}
if ($this->input->post('sale_tax')) {
    $v .= "&sale_tax=" . $this->input->post('sale_tax');
}


?>

<script>
    $(document).ready(function () {
		oTable = $('#SlRData').dataTable({
			"aaSorting": [[0, "desc"],[1, "desc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= site_url('reports/getSalesReportMargin/?v=1' . $v) ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				nRow.id = aData[13]; 
				nRow.className = (aData[8] > 0) ? "invoice_link2 warning" : "invoice_link2";
				var action = $('td:eq(5)', nRow);
				return nRow;
			},
			"aoColumns": [{"mRender": fld}, null, null, null,null, {
				"bSearchable": false,
				"mRender": pqFormat
			}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var gtotal = 0, paid = 0, balance = 0, discount=0, gross_margin = 0, return_total = 0;
				for (var i = 0; i < aaData.length; i++) {
					gross_margin += parseFloat(aaData[aiDisplay[i]][6]);
					gtotal += parseFloat(aaData[aiDisplay[i]][7]);
					return_total += parseFloat(aaData[aiDisplay[i]][8]);
					paid += parseFloat(aaData[aiDisplay[i]][9]);
					discount += parseFloat(aaData[aiDisplay[i]][10]);
					balance += parseFloat(aaData[aiDisplay[i]][11]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[6].innerHTML = currencyFormat(parseFloat(gross_margin));
				nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
				nCells[8].innerHTML = currencyFormat(parseFloat(return_total));
				nCells[9].innerHTML = currencyFormat(parseFloat(paid));
				nCells[10].innerHTML = currencyFormat(parseFloat(discount));
				nCells[11].innerHTML = currencyFormat(parseFloat(balance));
			}
		}).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
			{column_number: 12, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
		], "footer");
    });
</script>
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
        <!-- <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('sales_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
        </h2> -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="javascript:;" onclick="window.print();" id ="print" 
                class="tip btn btn-success btn-block box_sub_menu" title="<?= lang('print') ?>">
                <i class="icon fa fa-file-fa fa-print">&nbsp;</i><?=lang('print')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="xls" class="tip btn btn-warning btn-block box_sub_menu" title="<?= lang('download_xls') ?>">
                <i class="icon fa fa-file-excel-o">&nbsp;</i><?=lang('download_xls')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="toggle_down tip btn btn-info btn-block box_sub_menu" title="<?= lang('show_form') ?>">
                <i class="icon fa fa-eye">&nbsp;</i><?=lang('show_form')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="toggle_up tip btn btn-danger btn-block box_sub_menu" title="<?= lang('hide_form') ?>">
                <i class="icon fa fa-eye-slash">&nbsp;</i><?=lang('hide_form')?>
            </a>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-line-chart tip"></i><?= lang('sales_report'); ?></h2>
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
                
                <li class="dropdown">
                    <a href="javascript:;" onclick="window.print();" id ="print" class="tip" title="<?= lang('print') ?>"><i class="icon fa fa-file-fa fa-print"></i></a>
                </li> -->
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <!-- <p class="introtext"><?= lang('customize_report'); ?></p> -->

                <div id="form">

                    <?php echo form_open("reports/sales"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>
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
						
						<?php if($Settings->project == 1){ ?>
									
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project">
										<?php
										$pj[''] = '';
										if(isset($projects) && $projects){
											foreach ($projects as $project) {
												$pj[$project->id] = $project->name;
											}
										}
										
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						
						<?php } ?>
						
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
						
						<?php if($Settings->car_operation == 1){ ?>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="vehi"><?= lang("vehicle_model"); ?></label>
									<?php echo form_input('vehicle_model', (isset($_POST['vehicle_model']) ? $_POST['vehicle_model'] : ""), 'class="form-control tip" id="vehicle_model"'); ?>

								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="vehicle_plate"><?= lang("vehicle_plate"); ?></label>
									<?php echo form_input('vehicle_plate', (isset($_POST['vehicle_plate']) ? $_POST['vehicle_plate'] : ""), 'class="form-control tip" id="vehicle_plate"'); ?>

								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="vehicle_vin"><?= lang("vehicle_vin"); ?></label>
									<?php echo form_input('vehicle_vin', (isset($_POST['vehicle_vin']) ? $_POST['vehicle_vin'] : ""), 'class="form-control tip" id="vehicle_vin"'); ?>

								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="mechanic"><?= lang("mechanic"); ?></label>
									<?php echo form_input('mechanic', (isset($_POST['mechanic']) ? $_POST['mechanic'] : ""), 'class="form-control tip" id="mechanic"'); ?>

								</div>
							</div>
						
						<?php } ?>
						<div class="col-sm-4 hidden">
                            <div class="form-group">
                                <label class="control-label" for="sale_tax"><?= lang("sale_tax"); ?></label>
                                <?php
                                $stax[""] = lang('select').' '.lang('sale_tax');
                                $stax["yes"] = lang('yes');
								$stax["no"] = lang('no');
                                echo form_dropdown('sale_tax', $stax, (isset($_POST['sale_tax']) ? $_POST['sale_tax'] : ""), 'class="form-control" id="sale_tax" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sale_tax") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="sale_types"><?= lang("sale_types"); ?></label>
                                <?php
                                $st[""] = lang('select').' '.lang('sale_types');
                                $st["sale"] = lang('sale');
								$st["pos"] = lang('pos');
                                echo form_dropdown('sale_type', $st, (isset($_POST['sale_type']) ? $_POST['sale_type'] : ""), 'class="form-control" id="sale_type" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sale_types") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <div class="input-group input-append">
                                    <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <div class="input-group input-append">
                                    <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                                </div>
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

                                <td class="text_left" style="width: 10%">
                                    <div>
                                        <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                    </div>
                                </td>
                                <td></td>
                                <td class="text_center" style="width:100%">
                                    <div>
                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
                                    </div>
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
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('monthly_sales_report_kh').'</div>';
                                        echo '<div class="bold">'.lang('monthly_sales_report_en').'</div><br>';
                                }
                                   
                                ?>
                               
                            </td> 
                        </tr>
                </table>

                <div class="table-responsive">
                    <table id="SlRData" class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
							<tr>

								<th><?= lang("date"); ?></th>
								<th><?= lang("reference_no"); ?></th>
								<th><?= lang("biller"); ?></th>
								<th><?= lang("customer"); ?></th>
								<th><?= lang("created_by"); ?></th>
								<th><?= lang("products"); ?></th>
								<th><?= lang("gross_margin"); ?></th>
								<th><?= lang("grand_total"); ?></th>
								<th><?= lang("returned"); ?></th>
								<th><?= lang("paid"); ?></th>
								<th><?= lang("discount"); ?></th>
								<th><?= lang("balance"); ?></th>
								<th><?= lang("payment_status"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th><?= lang("products"); ?></th>
								<th><?= lang("gross_margin"); ?></th>
								<th><?= lang("grand_total"); ?></th>
								<th><?= lang("returned"); ?></th>
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
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=($pos_settings->table_enable == 1?site_url('reports/getSalesReportMarginGroup/pdf/0/?v=1'.$v):site_url('reports/getSalesReportMargin/pdf/0/?v=1'.$v))?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=($pos_settings->table_enable == 1?site_url('reports/getSalesReportMarginGroup/0/xls/?v=1'.$v):site_url('reports/getSalesReportMargin/0/xls/?v=1'.$v))?>";
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

		$("#biller").change(biller); biller();
		function biller(){
			var biller = $("#biller").val();
			var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
			$.ajax({
				url : "<?= site_url("reports/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
    });
</script>
<style type="text/css">
	#SlRData td:nth-child(6),#SlRData th:nth-child(6) {
	   display: none !important;
	}
	<?php if(!$Owner && !$Admin && !$this->session->userdata('show_cost')){ ?>
		#SlRData td:nth-child(7),#SlRData th:nth-child(7) {
		   display: none !important;
		}
	<?php } ?>
</style>