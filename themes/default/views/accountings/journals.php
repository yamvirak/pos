<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";

if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('project')) {
    $v .= "&project=" . $this->input->post('project');
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
			oTable = $('#JonalData').dataTable({
				"aaSorting": [[0, "desc"]],
				"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
				"iDisplayLength": <?= $Settings->rows_per_page ?>,
				'bProcessing': true, 'bServerSide': true,
				'sAjaxSource': '<?= site_url('accountings/getJornals/?v=1' . $v) ?>',
				'fnServerData': function (sSource, aoData, fnCallback) {
					aoData.push({
						"name": "<?= $this->security->get_csrf_token_name() ?>",
						"value": "<?= $this->security->get_csrf_hash() ?>"
					});
					$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
				},
				'fnRowCallback': function (nRow, aData, iDisplayIndex) {
					nRow.id = aData[0]; 
					return nRow;
				},
				'bStateSave': true,
				'fnStateSave': function (oSettings, oData) {
					localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
				},
				'fnStateLoad': function (oSettings) {
					var data = localStorage.getItem('DataTables_' + window.location.pathname);
					return JSON.parse(data);
				},
				"aoColumns": [{"sClass": "center"},{"sClass": "center"}, {"sClass": "center"}, {"mRender": fd,"sClass": "center"}, null,null,{"mRender": currencyFormatAcc, "bSearchable" : false},{"mRender": currencyFormatAcc, "bSearchable" : false},null,{"mRender": decode_html},null,null,null],
				"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
					var credit = 0, debit = 0;
					for (var i = 0; i < aaData.length; i++) {
						credit += parseFloat(aaData[aiDisplay[i]][6]);
						debit += parseFloat(aaData[aiDisplay[i]][7]);
					}
					var nCells = nRow.getElementsByTagName('th');
					nCells[6].innerHTML = currencyFormat(parseFloat(credit));
					nCells[7].innerHTML = currencyFormat(parseFloat(debit));
				}
			}).fnSetFilteringDelay().dtFilter([
				{column_number: 0, filter_default_label: "[<?=lang('id');?>]", filter_type: "text", data: []},
				{column_number: 1, filter_default_label: "[<?=lang('transaction');?>]", filter_type: "text", data: []},
				{column_number: 2, filter_default_label: "[<?=lang('transaction_id');?>]", filter_type: "text", data: []},
				{column_number: 3, filter_default_label: "[<?=lang('transaction_date');?>]", filter_type: "text", data: []},
				{column_number: 4, filter_default_label: "[<?=lang('account');?>]", filter_type: "text", data: []},
				{column_number: 5, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
				{column_number: 8, filter_default_label: "[<?=lang('narrative');?>]", filter_type: "text", data: []},
				{column_number: 9, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
				{column_number: 10, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
				{column_number: 11, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
				{column_number: 12, filter_default_label: "[<?=lang('user');?>]", filter_type: "text", data: []}
			], "footer");
		
			if (localStorage.getItem('remove_qals')) {
				if (localStorage.getItem('jnitems')) {
					localStorage.removeItem('jnitems');
				}
				if (localStorage.getItem('jnref')) {
					localStorage.removeItem('jnref');
				}
				if (localStorage.getItem('jn_type')) {
					localStorage.removeItem('jn_type');
				}
				if (localStorage.getItem('jnnote')) {
					localStorage.removeItem('jnnote');
				}
				if (localStorage.getItem('jndate')) {
					localStorage.removeItem('jndate');
				}
				if (localStorage.getItem('project')) {
					localStorage.removeItem('project');
				}
				localStorage.removeItem('remove_jnls');
			}
			
			<?php if ($this->session->userdata('remove_qals')) { ?>
				if (localStorage.getItem('jnitems')) {
					localStorage.removeItem('jnitems');
				}
				if (localStorage.getItem('jnref')) {
					localStorage.removeItem('jnref');
				}
				if (localStorage.getItem('jn_type')) {
					localStorage.removeItem('jn_type');
				}
				if (localStorage.getItem('jnnote')) {
					localStorage.removeItem('jnnote');
				}
				if (localStorage.getItem('jndate')) {
					localStorage.removeItem('jndate');
				}
				if (localStorage.getItem('project')) {
					localStorage.removeItem('project');
				}
			<?php 
				$this->cus->unset_data('remove_qals');
			}
			?>
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
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('journals'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
        </h2>

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
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("accountings/journals"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

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
                                <label class="control-label" for="project"><?= lang("project"); ?></label>
                                <?php
                                $pr[""] = lang('select').' '.lang('project');
                                foreach ($projects as $project) {
                                    $pr[$project->id] = $project->name;
                                }
                                echo form_dropdown('project', $pr, (isset($_POST['project']) ? $_POST['project'] : ""), 'class="form-control" id="project" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("project") . '"');
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

                <div class="table-responsive">
                    <table id="JonalData"
                           class="table table-bordered table-condensed accountings-table">
                        <thead>
                        <tr>
							<th><?= lang("id"); ?></th>
                            <th><?= lang("transaction"); ?></th>
                            <th><?= lang("transaction_id"); ?></th>
                            <th><?= lang("transaction_date"); ?></th>
                            <th><?= lang("account"); ?></th>
							<th><?= lang("reference"); ?></th>
							<th><?= lang("debit"); ?></th>
							<th><?= lang("credit"); ?></th>
                            <th><?= lang("narrative"); ?></th>
                            <th><?= lang("description"); ?></th>
							<th><?= lang("biller"); ?></th>
                            <th><?= lang("project"); ?></th>
							<th><?= lang("user"); ?></th>
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
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('accountings/getJornals/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('accountings/getJornals/0/xls/?v=1'.$v)?>";
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


<style type="text/css">
	#JonalData th:nth-child(1), 
	#JonalData td:nth-child(1)
	{
		display:none !important;
	}
	<?php if($Settings->project <> 1){ ?>
		#JonalData th:nth-child(12), 
		#JonalData td:nth-child(12)
		{
			display:none !important;
		}
	<?php } ?>
</style>


