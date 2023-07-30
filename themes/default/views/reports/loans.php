<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('application_no')) {
    $v .= "&application_no=" . $this->input->post('application_no');
}
if ($this->input->post('borrower')) {
    $v .= "&borrower=" . $this->input->post('borrower');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
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
		oTable = $('#LoanTable').dataTable({
            "aaSorting": [[1, "desc"], [3, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('reports/getLoansReport?v=1&'. $v)?>',
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
			null,
			null,
			null,
			null,
			null,
			{"sClass" : "center"},
			null,
			{"mRender" : fsd, "sClass" : "center"},
			{"mRender" : fsd, "sClass" : "center"},
			null,
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var principal_amount =0, outstanding_amount = 0;
				for (var i = 0; i < aaData.length; i++) {
					principal_amount += parseFloat(aaData[aiDisplay[i]][10]);
					outstanding_amount += parseFloat(aaData[aiDisplay[i]][11]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[10].innerHTML = currencyFormat(parseFloat(principal_amount));
				nCells[11].innerHTML = currencyFormat(parseFloat(outstanding_amount));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('loan_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('application_no');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('borrower_code');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('borrower');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('gender');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('loan_product');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('disbursed_date');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('maturity_date');?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('loan_officer');?>]", filter_type: "text", data: []},
			{column_number: 12, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php echo form_open("reports/loans", ' id="form-submit" '); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('loans_report'); ?></h2>
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
                <li class="dropdown">
                    <a href="javascript:;" onclick="window.print();" id ="print" class="tip" title="<?= lang('print') ?>"><i class="icon fa fa-file-fa fa-print"></i></a>
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
                                <?= lang("loan_reference_no", "loan_reference_no"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("application_no", "application_no"); ?>
                                <?php echo form_input('application_no', (isset($_POST['application_no']) ? $_POST['application_no'] : ""), 'class="form-control tip" id="application_no"'); ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="borrower"><?= lang("borrower"); ?></label>
                                <?php echo form_input('borrower', (isset($_POST['borrower']) ? $_POST['borrower'] : ""), 'class="form-control" id="borrower" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("borrower") . '"'); ?>
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
							<?php echo form_submit('submit_report', $this->lang->line("search"), 'class="btn btn-primary"'); ?> 
						</div>
                    </div>
				</div>
				
				<?php echo form_close(); ?>

				<div class="clearfix"></div>

                <table style="width:100%; margin-bottom: 10px">
                    <?php 
                        $biller_id = (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller);
                                $biller_id_all = lang('all_selected');
                                $biller_id_detail = $this->site->getCompanyByID($biller_id);
                                if($biller_id_detail){
                    ?>
                    
                    <tr>
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

                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('loans_report_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('loans_report')?>
                                </div><br>
                            
                        </td>
                    </tr>
                    
                    <?= $print_filter ?>
                <?php } ?>
                </table>
				
                <div class="table-responsive">
                    <table id="LoanTable" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th width="160px"><?= lang("loan_reference_no"); ?></th>
                            <th width="160px"><?= lang("application_no"); ?></th>
                            <th width="200px"><?= lang("biller"); ?></th>
							<th width="120px"><?= lang("borrower_code"); ?></th>
							<th width="120px"><?= lang("borrower"); ?></th>
							<th width="120px"><?= lang("gender"); ?></th>
							<th width="120px"><?= lang("loan_product") ?></th>
							<th width="120px"><?= lang("disbursed_date") ?></th>
							<th width="120px"><?= lang("maturity_date") ?></th>
							<th width="120px"><?= lang("loan_officer") ?></th>
							<th width="120px"><?= lang("principal_amount") ?></th>
							<th width="120px"><?= lang("outstanding_amount"); ?></th>
                            <th style="width:20px;"><?= lang("status"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="13" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
            window.location.href = "<?=site_url('reports/getLoansReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getLoansReport/0/xls/?v=1'.$v)?>";
            return false;
        });
		$('form[data-toggle="validator"]').bootstrapValidator({ feedbackIcons:{valid: 'fa fa-check',invalid: 'fa fa-times',validating: 'fa fa-refresh'}, excluded: [':disabled'] });
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