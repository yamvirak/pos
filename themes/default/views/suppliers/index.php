<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#SupData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('suppliers/getSuppliers') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "supplier_details_link";
                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, null, null, null, null, null, null, null, {"mRender": currencyFormat}, {"bSortable": false}]
        }).dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('email_address');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('city');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('country');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('deposit');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo form_open('suppliers/supplier_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?= site_url('suppliers/add'); ?>" data-backdrop='static' data-keyboard='false' 
                data-toggle="modal" data-target="#myModal" id="add"
                class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang("add_supplier"); ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="<?= site_url('suppliers/import_csv'); ?>" data-toggle="modal" data-target="#myModal"
                class="btn btn-info btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang("import_by_csv"); ?>

            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="bpo btn btn-danger btn-block box_sub_menu" title="<b><?= $this->lang->line("delete_suppliers") ?></b>" tabindex="-1"
                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                <i class="fa fa-trash-o"></i> <?= lang('delete_suppliers') ?>
            </a>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-users tip"></i><?= lang('suppliers'); ?></h2>
                </li>

            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="SupData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("code"); ?></th>
                            <th><?= lang("company"); ?></th>
                            <th><?= lang("name"); ?></th>
                            <th><?= lang("email_address"); ?></th>
                            <th><?= lang("phone"); ?></th>
                            <th><?= lang("city"); ?></th>
                            <th><?= lang("country"); ?></th>
                            <th><?= lang("deposit"); ?></th>
                            <th style="width:85px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<?php if ($action && $action == 'add') {
    echo '<script>$(document).ready(function(){$("#add").trigger("click");});</script>';
}
?>
    

