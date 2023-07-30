<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#BrandTable').dataTable({
            "aaSorting": [[3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getBrands') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, {"bSortable": false, "mRender": img_hl}, null, null, {"bSortable": false}]
        });
    });
</script>
<?= form_open('system_settings/brand_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?php echo site_url('system_settings/add_brand'); ?>" data-toggle="modal" 
                data-backdrop="static" data-keyboard="false" data-target="#myModal"
                class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus"></i> <?= lang('add_brand') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="<?php echo site_url('system_settings/import_brands'); ?>" data-toggle="modal" 
                data-backdrop="static" data-keyboard="false" data-target="#myModal"
                class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus"></i> <?= lang('import_brands') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="btn btn-primary btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="delete" data-action="delete" class="btn btn-danger btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-trash-o"></i> <?= lang('delete_brands') ?>
            </a>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
            <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-th-list tip"></i><?= lang('brands'); ?></h2>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->
                <div class="table-responsive">
                    <table id="BrandTable" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:40px; width: 40px; text-align: center;">
                                    <?= lang("image"); ?>
                                </th>
                                <th><?= lang("code"); ?></th>
                                <th><?= lang("name"); ?></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty">
                                    <?= lang('loading_data_from_server') ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

