<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var cTable = $('#SLData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('customers/getCustomers') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "customer_details_link";
                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, null, null, null, null, null, null, null, {"bVisible" : <?= ($this->config->item("saleman")?"true":"false"); ?> }, {"mRender": currencyFormat}, {"mRender": row_status},{"bSortable": false}]
        }).dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('price_group');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('customer_group');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('saleman');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('deposit');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        $('#myModal').on('hidden.bs.modal', function () {
            cTable.fnDraw( false );
        });
    });
</script>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo form_open('customers/customer_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customers'); ?></h2> -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?= site_url('customers/add'); ?>" data-backdrop='static' data-keyboard='false' 
                data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" 
                id="add" class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang("add_customer"); ?>
            </a>
        </div>
       <div class="sub_menu">
            <a href="<?= site_url('customers/import_csv'); ?>" data-toggle="modal" data-backdrop="static" 
                data-keyboard="false" data-target="#myModal" class="btn btn-info btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang("import_by_csv"); ?>
            </a>
        </div>
        
        <?php if ($this->Owner ||$this->Admin || $this->GP['bulk_actions']) { ?>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>
        
        <div class="sub_menu">
            <a href="#" class="bpo btn btn-danger btn-block box_sub_menu" title="<?= $this->lang->line("delete_customers") ?>" tabindex="-1"
                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
            </a>
        </div>
        <?php } ?>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-users tip"></i><?= lang('customers'); ?></h2>
                </li>

            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->

                <div class="table-responsive">
                    <table id="SLData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="max-width:30px; width: 30px !important; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="width:150px;"><?= lang("code"); ?></th>
                            <th style="width:150px;"><?= lang("company"); ?></th>
                            <th style="width:120px;"><?= lang("name"); ?></th>
                            <th style="width:120px;"><?= lang("phone"); ?></th>
                            <th style="width:200px;"><?= lang("address"); ?></th>
                            <th style="width:120px;"><?= lang("price_group"); ?></th>
                            <th style="width:120px;"><?= lang("customer_group"); ?></th>
                            <th style="width:120px;"><?= lang("saleman"); ?></th>
                            <th style="width:120px;"><?= lang("deposit"); ?></th>
                            <th style="width:120px;"><?= lang("status"); ?></th>
                            <th style="max-width:30px; width:30px !important;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="max-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="max-width:30px; width:30px !important;" class="text-center"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin  || $GP['bulk_actions']) { ?>
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
<script type="text/javascript">
    $(function(){
        $(document).on('ifChecked ifUnchecked', '.multi-select', function(event) {
            var checked = [];
            $('.multi-select:checked').each(function() {
                checked.push($(this).val());
            });
            var url = "<?= site_url("customers/send_sms") ?>";
            if(checked.length > 0){
                $("#send_sms").attr("href", url + "?ids="+checked);
            }else{
                $("#send_sms").attr("href", url + "?ids=0");
            }
        });
    });
</script>
<script type="text/javascript">
    $(function(){
        $("#SLData").css("width", "auto");
    });
</script>
    

