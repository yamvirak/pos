<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
/* if($this->input->post('name')){
  $v .= "&name=".$this->input->post('name');
} */
if ($this->input->post('payment_ref')) {
    $v .= "&payment_ref=" . $this->input->post('payment_ref');
}
if ($this->input->post('paid_by')) {
    $v .= "&paid_by=" . $this->input->post('paid_by');
}
if ($this->input->post('sale_ref')) {
    $v .= "&sale_ref=" . $this->input->post('sale_ref');
}
if ($this->input->post('purchase_ref')) {
    $v .= "&purchase_ref=" . $this->input->post('purchase_ref');
}
if ($this->input->post('expense_ref')) {
    $v .= "&expense_ref=" . $this->input->post('expense_ref');
}
if ($this->input->post('supplier')) {
    $v .= "&supplier=" . $this->input->post('supplier');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('project')) {
    $v .= "&project=" . $this->input->post('project');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('cheque')) {
    $v .= "&cheque=" . $this->input->post('cheque');
}
if ($this->input->post('tid')) {
    $v .= "&tid=" . $this->input->post('tid');
}
if ($this->input->post('card')) {
    $v .= "&card=" . $this->input->post('card');
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
        var pb = <?= json_encode($pb); ?>;
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : x) : x;
        }

        function ref(x) {
            return (x != null) ? x : ' ';
        }

        oTable = $('#PayRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getPaymentsReportCus/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, {"mRender": ref}, {"mRender": ref}, {"mRender": ref},{"mRender": fld},null,null, {"mRender": paid_by}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}, {"bVisible": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[12];
				if(aData[14]=='Con Mission'){
					nRow.className = "no_link";
				}else if(aData[14]=='CCommission'){
					nRow.className = "commission_payment_link";
				}else if(aData[14]=='Fuel Expense'){
					nRow.className = "fuel_expense_payment_link";
				}else if(aData[14]=='SO Deposit'){
					nRow.className = "so_deposit_link";
				}else if(aData[14]=='PO Deposit'){
					nRow.className = "po_deposit_link";
				}else if(aData[14]=='Salesman Commission'){
					nRow.className = "salesman_payment_link";
				}else if(aData[14] == "RentalDeposit"){
                    nRow.className = "rental_deposit_link";
                }else if(aData[11]=='pawn_received' || aData[11]=='pawn_sent' || aData[11]=='pawn_rate'){
					nRow.className = "pawn_payment";
				}else if (aData[13] > 0) {
					if(aData[11]=='returned'){
						nRow.className = "payment_link warning";
					}else{
						nRow.className = "payment_link";
					}
                } else  {
					if(aData[11]=='returned'){
						nRow.className = "payment_link2 warning";
					}else{
						nRow.className = "payment_link2";
					}
                }
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, discount=0;
                for (var i = 0; i < aaData.length; i++) {
					if (aaData[aiDisplay[i]][11] == 'sent' || aaData[aiDisplay[i]][11] == 'expense' || aaData[aiDisplay[i]][11] == 'pawn_sent'){
						total -= Math.abs(parseFloat(aaData[aiDisplay[i]][9]));
						discount -= Math.abs(parseFloat(aaData[aiDisplay[i]][10]));
					}else if (aaData[aiDisplay[i]][11] == 'returned' && aaData[aiDisplay[i]][13] > 0){
						total -= Math.abs(parseFloat(aaData[aiDisplay[i]][9]));
						discount -= Math.abs(parseFloat(aaData[aiDisplay[i]][10]));
					}else{
						total += parseFloat(aaData[aiDisplay[i]][9]);
						discount += parseFloat(aaData[aiDisplay[i]][10]);
					}    
                }

                var nCells = nRow.getElementsByTagName('th');
                nCells[9].innerHTML = currencyFormat(parseFloat(total));
				nCells[10].innerHTML = currencyFormat(parseFloat(discount));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('payment_ref');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('sale_ref');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('purchase_ref');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('expense_ref');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('date_ref');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('biller')) { ?>
        $('#rbiller').select2({ allowClear: true });
        <?php } ?>
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
        <!-- <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('payments_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            } ?>
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
                    <h2 class="blue"><i class="icon fa fa-money tip"></i><?= lang('payments_report'); ?></h2>
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

                <!-- <p class="introtext"><?= lang('customize_report'); ?></p> -->

                <div id="form">

                    <?php echo form_open("reports/payments"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_ref", "payment_ref"); ?>
                                <?php echo form_input('payment_ref', (isset($_POST['payment_ref']) ? $_POST['payment_ref'] : ""), 'class="form-control tip" id="payment_ref"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4 hidden">
                            <div class="form-group">
                            <?=lang("paid_by", "paid_by");?>
                                <select name="paid_by" id="paid_by" class="form-control paid_by">
                                    <?= $this->cus->paid_opts($this->input->post('paid_by'), false, true); ?>
                                    <?=$pos_settings && $pos_settings->paypal_pro ? '<option value="ppp">' . lang("paypal_pro") . '</option>' : '';?>
                                    <?=$pos_settings && $pos_settings->stripe ? '<option value="stripe">' . lang("stripe") . '</option>' : '';?>
                                    <?=$pos_settings && $pos_settings->authorize ? '<option value="authorize">' . lang("authorize") . '</option>' : '';?>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("sale_ref", "sale_ref"); ?>
                                <?php echo form_input('sale_ref', (isset($_POST['sale_ref']) ? $_POST['sale_ref'] : ""), 'class="form-control tip" id="sale_ref"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("purchase_ref", "purchase_ref"); ?>
                                <?php echo form_input('purchase_ref', (isset($_POST['purchase_ref']) ? $_POST['purchase_ref'] : ""), 'class="form-control tip" id="purchase_ref"'); ?>

                            </div>
                        </div>
						
						<?php if($this->config->item('expense_payable')){ ?>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("expense_ref", "expense_ref"); ?>
									<?php echo form_input('expense_ref', (isset($_POST['expense_ref']) ? $_POST['expense_ref'] : ""), 'class="form-control tip" id="expense_ref"'); ?>
								</div>
							</div>
						<?php } ?>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="rbiller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[''] = '';
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="rbiller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
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
									<label class="control-label" for="supplier"><?= lang("supplier"); ?></label>
									<?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("supplier") . '"'); ?>
								</div>
							</div>

                        <div class="col-sm-4 hidden">
                            <div class="form-group">
                                <?= lang("transaction_id", "tid"); ?>
                                <?php echo form_input('tid', (isset($_POST['tid']) ? $_POST['tid'] : ""), 'class="form-control" id="tid"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hidden">
                            <div class="form-group">
                                <?= lang("card_no", "card"); ?>
                                <?php echo form_input('card', (isset($_POST['card']) ? $_POST['card'] : ""), 'class="form-control" id="card"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hidden">
                            <div class="form-group">
                                <?= lang("cheque_no", "cheque"); ?>
                                <?php echo form_input('cheque', (isset($_POST['cheque']) ? $_POST['cheque'] : ""), 'class="form-control" id="cheque"'); ?>
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
										echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('payment_report_kh').'</div>';
										echo '<div class="bold">'.lang('payment_report_en').'</div><br>';
								}
								?>
							
							</td> 
                        </tr>
					</th>
                </table>

                <div class="table-responsive">
                    <table id="PayRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">

                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("payment_ref"); ?></th>
                            <th><?= lang("sale_ref"); ?></th>
                            <th><?= lang("purchase_ref"); ?></th>
							<th><?= lang("expense_ref"); ?></th>
							<th><?= lang("date_ref"); ?></th>
                            <th><?= lang("name"); ?></th>
							<th><?= lang("created_by"); ?></th>
							<th><?= lang("paid_by"); ?></th>
                            <th><?= lang("amount"); ?></th>
							<th><?= lang("discount"); ?></th>
                            <th><?= lang("type"); ?></th>
                            <th><?= lang("id"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
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
		
		var supplier_id = "<?= isset($_POST['supplier'])?$_POST['supplier']:0 ?>";
		if (supplier_id > 0) {
		  $('#supplier_id').val(supplier_id).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
			  $.ajax({
				type: "get", async: false,
				url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
				dataType: "json",
				success: function (data) {
				  callback(data[0]);
				}
			  });
			},
			ajax: {
			  url: site.base_url + "suppliers/suggestions",
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
		  $('#supplier_id').select2({
			minimumInputLength: 1,
			ajax: {
			  url: site.base_url + "suppliers/suggestions",
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
            window.location.href = "<?=site_url('reports/getPaymentsReportCus/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getPaymentsReportCus/0/xls/?v=1'.$v)?>";
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
		$("#rbiller").change(biller); biller();
		function biller(){
			var biller = $("#rbiller").val();
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

<?php if(!$this->config->item('expense_payable')){ ?>
	<style type="text/css">
		#PayRData td:nth-child(5), #PayRData th:nth-child(5){
		   display: none !important;
		}
	</style>
<?php } ?>
