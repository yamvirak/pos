<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
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
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {
		oTable = $('#InstallmentCustomerTable').dataTable({
            "aaSorting": [[1, "desc"], [3, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('reports/getInstallmentPaymentCustomers?v=1&'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                return nRow;
            },
            "aoColumns": [
			{"sClass" : "left"}, 
			{"sClass" : "center"}, 
			null,
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat}, 
			{"mRender" : currencyFormat}, 
			{"mRender" : currencyFormat},
			{"mRender" : formatDecimal, "sClass" : "center"},
			{"mRender" : row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var deposit = 0, principal =0, interest = 0, amount = 0, paid = 0, total_paid=0, total_balance = 0;
				for (var i = 0; i < aaData.length; i++) {
					deposit += parseFloat(aaData[aiDisplay[i]][6]);
					principal += parseFloat(aaData[aiDisplay[i]][7]);
					interest += parseFloat(aaData[aiDisplay[i]][8]);
					amount += parseFloat(aaData[aiDisplay[i]][9]);
					paid += parseFloat(aaData[aiDisplay[i]][10]);
					total_paid += parseFloat(aaData[aiDisplay[i]][11]);
					total_balance += parseFloat(aaData[aiDisplay[i]][12]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[6].innerHTML = currencyFormat(parseFloat(deposit));
				nCells[7].innerHTML = currencyFormat(parseFloat(principal));
				nCells[8].innerHTML = currencyFormat(parseFloat(interest));
				nCells[9].innerHTML = currencyFormat(parseFloat(amount));
				nCells[10].innerHTML = currencyFormat(parseFloat(paid));
				nCells[11].innerHTML = currencyFormat(parseFloat(total_paid));
				nCells[12].innerHTML = currencyFormat(parseFloat(total_balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('price');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('discount');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('amount');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('installment_amount');?>]", filter_type: "text", data: []},
			{column_number: 13, filter_default_label: "[<?=lang('count');?>]", filter_type: "text", data: []},
			{column_number: 14, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php echo form_open("reports/installment_payment_customers", ' id="form-submit" '); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('installment_payment_customers_report'); ?></h2>
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
                
				<li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
				<div id="form">
					<div class="row">
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("reference_no", "reference_no"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
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
										if (isset($projects) && $projects != false) {
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						<?php } ?>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
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
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
					</div>
					<div class="form-group">
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("Search"), 'class="btn btn-primary"'); ?> 
						</div>
                    </div>
					<?php echo form_close(); ?>
				</div>
                <div class="table-responsive">
                    <table id="InstallmentCustomerTable" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
							<th rowspan="2"><?= lang("name"); ?></th>
							<th rowspan="2"><?= lang("reference_no"); ?></th>
							<th rowspan="2"><?= lang("product"); ?></th>
							<th rowspan="2"><?= lang("price"); ?></th>
							<th rowspan="2"><?= lang("discount"); ?></th>
							<th rowspan="2"><?= lang("amount"); ?></th>
							<th rowspan="2"><?= lang("deposit"); ?></th>
							<th colspan="3"><?= lang("installments"); ?></th>
							<th rowspan="2"><?= lang("paid"); ?></th>
							<th colspan="3"><?= lang("total"); ?></th>
							<th rowspan="2"><?= lang("status"); ?></th>
						</tr>
						<tr>
							<th style="background:#428BCA; border: 1px solid #357EBD; text-align:center; color:#FFF;"><?= lang("principal"); ?></th>
							<th style="background:#428BCA; border: 1px solid #357EBD; text-align:center; color:#FFF;"><?= lang("interest"); ?></th>
							<th style="background:#428BCA; border: 1px solid #357EBD; text-align:center; color:#FFF;"><?= lang("payment"); ?></th>
							<th style="background:#428BCA; border: 1px solid #357EBD; text-align:center; color:#FFF;"><?= lang("paid"); ?></th>
							<th style="background:#428BCA; border: 1px solid #357EBD; text-align:center; color:#FFF;"><?= lang("balance"); ?></th>
							<th style="background:#428BCA; border: 1px solid #357EBD; text-align:center; color:#FFF;"><?= lang("percent_paid"); ?></th>
						</tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="15" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
		
		$('#form').hide();
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
		$('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getInstallmentPaymentCustomers/pdf/?v=1'.$v)?>";
            return false;
        });
		$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getInstallmentPaymentCustomers/0/xls/?v=1'.$v)?>";
            return false;
        });
		$("#biller").change(biller);biller();
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
					}else{
						
					}
				}
			})
		}
    });
</script>
