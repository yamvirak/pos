<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
	$v = "";
	if ($this->input->post('reference_no')) {
		$v .= "&reference_no=" . $this->input->post('reference_no');
	}
	if ($this->input->post('borrower')) {
	    $v .= "&borrower=" . $this->input->post('borrower');
	}
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
	if ($this->input->post('project')) {
		$v .= "&project=" . $this->input->post('project');
	}
	if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
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
        oTable = $('#LTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getLoanRepaymentsReport?v=1&'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
				{"sClass":"center"},
				{"mRender":fsd , "sClass":"center"}, 
				{"sClass":"center"},
				{"sClass":"center"},
				{"sClass":"center"},
				{"sClass":"center"},
				{"mRender":currencyFormat},
				{"mRender":currencyFormat},
				{"mRender":currencyFormat},
				{"mRender":currencyFormat},
				{"mRender":currencyFormat},
				{"mRender":currencyFormat}, 
				{"mRender":row_status}],
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var interest = 0,  principal= 0, total = 0, paid = 0, fee_charge = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
					interest += parseFloat(aaData[aiDisplay[i]][6]);
					fee_charge += parseFloat(aaData[aiDisplay[i]][7]);
					principal += parseFloat(aaData[aiDisplay[i]][8]);
					total += parseFloat(aaData[aiDisplay[i]][9]);
					paid += parseFloat(aaData[aiDisplay[i]][10]);
					balance += parseFloat(aaData[aiDisplay[i]][11]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(interest));
                nCells[7].innerHTML = currencyFormat(parseFloat(fee_charge));
				nCells[8].innerHTML = currencyFormat(parseFloat(principal));
				nCells[9].innerHTML = currencyFormat(parseFloat(total));
				nCells[10].innerHTML = currencyFormat(parseFloat(paid));
				nCells[11].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
        	{column_number: 0, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
			{column_number: 1, filter_default_label: "[<?=lang('deadline');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('loan_product');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
			{column_number: 12, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
		
    });
</script>


<?=form_open('reports/loan_repayments_report', 'id="action-form"');?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('loan_repayments_report'); ?></h2>
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
					<a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
						<i class="icon fa fa-file-excel-o"></i>
					</a>
				</li>
				<li class="dropdown">
					<a href="#" onclick="window.print(); return false;" id="print" class="tip" title="<?= lang('print') ?>">
						<i class="icon fa fa-print"></i>
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
                                <label class="control-label" for="borrower"><?= lang("borrower"); ?></label>
                                <?php echo form_input('borrower', (isset($_POST['borrower']) ? $_POST['borrower'] : ""), 'class="form-control" id="borrower" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("borrower") . '"'); ?>
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
								<label for="product"><?= lang('product') ?></label>
								<?php
									$tp[''] = lang('select').' '.lang('product');
									if($products){
										foreach ($products as $product) {
											$tp[$product->id] = $product->name;
										}
									}
									echo form_dropdown('product', $tp, (isset($_POST['product']) ? $_POST['product'] : 0), ' class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("product") . '" style="width:100%;" ');
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
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("Search"), 'class="btn btn-primary"'); ?> 
						</div>
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
                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('loan_repayments_report_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('loan_repayments_report')?>
                                </div><br>
                               
                            </td> 
                        </tr>
                </table>
				<table class="print_only" style="width:100%; margin-bottom: 10px">
					<?php
						$print_filter = "";
						$p = 1;
						
						if ($this->input->post('biller')) {
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("biller").": ".$bl[$this->input->post('biller')]."</td>";
						}
						if ($this->input->post('project')) {
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("project").": ".$pj[$this->input->post('project')]."</td>";
						}
						if ($this->input->post('warehouse')) {
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("warehouse").": ".$wh[$this->input->post('warehouse')]."</td>";
						}
						if ($this->input->post('start_date')) {
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("start_date").": ".$this->input->post('start_date')."</td>";
						}
						if ($this->input->post('end_date')) {
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("end_date").": ".$this->input->post('end_date')."</td>";
						}
						$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
						$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("printing_date").": ".$this->cus->hrsd(date("Y-m-d"))."</td></tr>";
					?>
					
					<tr class="hidden">
						<th colspan="2" class="text-center" style="font-size:15px;font-family: Khmer OS Muol Light;"><u><?= lang('loan_repayments_report_kh'); ?></u></th>
					</tr>
					<tr class="hidden">
						<th colspan="2" class="text-center"><u><?= lang('loan_repayments_report'); ?></u></th>
					</tr>
					<?= $print_filter ?>
				</table>

				
                <div class="table-responsive">
                    <table id="LTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr class="active">
								<th><?= lang("period") ?></th>
								<th><?= lang("deadline") ?></th>
								<th><?= lang("reference_no") ?></th>
								<th><?= lang("loan_product") ?></th>
								<th><?= lang("customer") ?></th>
								<th><?= lang("phone") ?></th>
								<th><?= lang("interest") ?><br/><small>( <?= lang('paid') ?> )</small></th>
								<th><?= lang("fee_charge") ?><br/><small>( <?= lang('paid') ?> )</small></th>
								<th><?= lang("principal") ?><br/><small>( <?= lang('paid') ?> )</small></th>
								<th><?= lang("total") ?></th>
								<th><?= lang("paid") ?></th>
								<th><?= lang("balance") ?></th>
								<th><?= lang("status") ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
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
                        </tfoot>
                    </table>
                </div>
				<table class="print_only" id="table_sinature">
					<tr>
						<td class="text-center footer_head" style="width:25%"><?= lang("prepared_by") ?></td>
						<td class="text-center footer_head" style="width:25%"><?= lang("checked_by") ?></td>
						<td class="text-center footer_head" style="width:25%"><?= lang("verified_by") ?></td>
						<td class="text-center footer_head" style="width:25%"><?= lang("approved_by") ?></td>
					</tr>
					<tr>
						<?php
							$user = $this->site->getUserByID($this->session->userdata("user_id"));
						?>
						<td style="height:110px; padding-left:5px; vertical-align: bottom !important">
							<?= lang("date") ?>: <?= $this->cus->hrsd(date("Y-m-d")) ?><br>
							<?= lang("name") ?>: <?= $user->last_name." ".$user->first_name ?>
						</td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</table>
            </div>
        </div>
    </div>
</div>
<style>
	@media print{    
		.dtFilter{
			display: table-footer-group !important;
		}
		#form{
			display:none !important;
		}
		.print_only{
			display:table !important;
		}
		table .td_biller{ 
			display:none; !important
		} 
		.exportExcel tr th{
			background-color : #428BCA !important;
			color : white !important;
		}
		@page{
			margin: 5mm; 
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;         
		}
		
	}
	.print_only{
		display:none;
	}
	.footer_head{
		padding: 5px;
	}
	#table_sinature{
		width:100%;
		margin-top:15px;
	}

	#table_sinature td{
		border:1px solid black;
	}
</style>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>
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
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getLoanRepaymentsReport/xls/?v=1'.$v)?>";
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
		var borrower = "<?= isset($_POST['borrower'])?$_POST['borrower']:0; ?>";
		$('#borrower').val(borrower).select2({
		   minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"loans/getBorrower/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });	
            },
			   ajax: {
				url: site.base_url+"loans/borrower_suggestions",
				dataType: 'json',
				quietMillis: 15,
				data: function (term, page) {
					return {
						term: term,
						limit: 10
					};
				},
				results: function (data, page) {
					if(data.results != null) {
						return { results: data.results };
					} else {
						return { results: [{id: '', text: 'No Match Found'}]};
					}
				}
			}
		});
		
    });
</script>