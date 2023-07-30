<script>
    $(document).ready(function () {
        var oTable = $('#TaData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getDrivers') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            },
			null,
			null,
			null,
			null,
			{"bSortable": false}],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {              
				nRow.id = aData[0];
                nRow.className = "";
                return nRow;
            }
        }).dtFilter([		
            {column_number: 1, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},			
        ], "footer");
    });
</script>
<?php if ($Owner) {
    echo form_open('system_settings/driver_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('drivers'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
				<li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo site_url('system_settings/add_driver'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_driver') ?>
                            </a>
                        </li>
						<li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
					    
						<li class="divider"></li>
						<li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_drivers') ?>
                            </a>
                        </li>
					<ul>
				</li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="TaData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:5%; width: 5%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>							
                            <th><?= lang("code"); ?></th>
							<th><?= lang("name"); ?></th>
							<th><?= lang("phone"); ?></th>
							<th><?= lang("address"); ?></th>
                            <th style="width:10%; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="datadrivers_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <th>[<?= lang("actions"); ?>]</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>