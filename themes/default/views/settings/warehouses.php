<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        function tax_type(x) {
            return (x == 1) ? "<?=lang('percentage')?>" : "<?=lang('fixed')?>";
        }

        $('#CURData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getWarehouses') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, { "bSortable": false, "mRender": img_hl }, null, null, null, null, null, null, {"bSortable": false}]
        });
    });
</script>
<?= form_open('system_settings/warehouse_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <?php if(!$this->config->item('one_warehouse')){ ?>
                <a href="<?php echo site_url('system_settings/add_warehouse'); ?>" data-toggle="modal" 
                    class="btn btn-success btn-block box_sub_menu" tabindex="-1"
                    data-backdrop="static" data-keyboard="false" data-target="#myModal">
                    <i class="fa fa-plus"></i> <?= lang('add_warehouse') ?>
                </a>
            <?php } ?>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <?php if(!$this->config->item('one_warehouse')){ ?>
                <a href="#" id="delete" data-action="delete" class="btn btn-danger btn-block box_sub_menu" tabindex="-1">
                    <i class="fa fa-trash-o"></i> <?= lang('delete_warehouses') ?>
                </a>
            <?php } ?>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-building-o"></i><?= lang('warehouse'); ?></h2>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="CURData" class="table table-bordered table-hover table-striped table-condensed">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?= lang("map"); ?></th>
                            <th class="col-xs-1"><?= lang("code"); ?></th>
                            <th class="col-xs-2"><?= lang("name"); ?></th>
                            <th class="col-xs-2"><?= lang("price_group"); ?></th>
                            <th class="col-xs-2"><?= lang("phone"); ?></th>
                            <th class="col-xs-2"><?= lang("email"); ?></th>
                            <th class="col-xs-3"><?= lang("address"); ?></th>
                            <th style="width:65px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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

