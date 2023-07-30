<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function () {
        oTable = $('#dmpData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('accountings/getBankReconsiliations/'.($biller ? $biller->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld},{"mRender": fsd},  null, null, null,{"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat},null, {"mRender": decode_html}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "bank_reconciliation_link";
                return nRow;
            },
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('statement_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('account');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('beginning_balance');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('ending_balance');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('service_charge');?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('interest_earned');?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo form_open('accountings/bank_reconciliation_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('bank_reconciliations').' ('.($biller ? $biller->name : lang('all_billers')).') '; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('accountings/add_bank_reconciliation') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_bank_reconciliation') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
						
						<li>
                            <a href="#" class="bpo"
                            title="<b><?=lang("delete_bank_reconciliations")?></b>"
                            data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                            data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?=lang('delete_bank_reconciliations')?>
							</a>
						</li>

                    </ul>
                </li>
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= site_url('accountings/bank_reconciliations') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . site_url('accountings/bank_reconciliations/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
							<th><?= lang("statement_date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
							<th><?= lang("project"); ?></th>
							<th><?= lang("account"); ?></th>
							<th><?= lang("beginning_balance"); ?></th>
							<th><?= lang("ending_balance"); ?></th>
							<th><?= lang("service_charge"); ?></th>
							<th><?= lang("interest_earned"); ?></th>
                            <th><?= lang("created_by"); ?></th>
                            <th><?= lang("note"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="min-width:75px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="16" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                           <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:75px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>

<style type="text/css">
	<?php if(!$Settings->project){ ?>
		#dmpData th:nth-child(5), #dmpData td:nth-child(5){
			display:none !important;
		}
	<?php } ?>
</style>