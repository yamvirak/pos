<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function () {
        oTable = $('#iovData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getInventoryOpeningBalance/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, {"mRender": decode_html}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "inventory_opening_balance_link";
                return nRow;
            },
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
        ], "footer");

        if (localStorage.getItem('remove_ivols')) {
            if (localStorage.getItem('ivoitems')) {
                localStorage.removeItem('ivoitems');
            }
            if (localStorage.getItem('ivoref')) {
                localStorage.removeItem('ivoref');
            }
            if (localStorage.getItem('ivonote')) {
                localStorage.removeItem('ivonote');
            }
            if (localStorage.getItem('ivowarehouse')) {
                localStorage.removeItem('ivowarehouse');
            }
            if (localStorage.getItem('ivonote')) {
                localStorage.removeItem('ivonote');
            }
            if (localStorage.getItem('ivodate')) {
                localStorage.removeItem('ivodate');
            }
            localStorage.removeItem('remove_ivols');
        }

        <?php if ($this->session->userdata('remove_ivols')) { ?>
            if (localStorage.getItem('ivoitems')) {
                localStorage.removeItem('ivoitems');
            }
            if (localStorage.getItem('ivoref')) {
                localStorage.removeItem('ivoref');
            }
            if (localStorage.getItem('ivonote')) {
                localStorage.removeItem('ivonote');
            }
            if (localStorage.getItem('ivowarehouse')) {
                localStorage.removeItem('ivowarehouse');
            }
            if (localStorage.getItem('ivonote')) {
                localStorage.removeItem('ivonote');
            }
            if (localStorage.getItem('ivodate')) {
                localStorage.removeItem('ivodate');
            }
        <?php $this->cus->unset_data('remove_ivols');}
        ?>
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo form_open('system_settings/inventory_opening_balance_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?= site_url('system_settings/add_inventory_opening_balance') ?>"
                class="tip btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang('add') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="<?php echo site_url('system_settings/add_inventory_opening_balance_excel/'); ?>" 
                data-toggle="modal" data-target="#myModal" class="tip btn btn-warning btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-arrow-circle-down"></i> <?= lang('add_by_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="tip btn btn-primary btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="bpo tip btn btn-danger btn-block box_sub_menu" tabindex="-1"
                title="<b><?= $this->lang->line("delete_inventory_opening_balances") ?></b>" 
                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                data-html="true" data-placement="right">
                <i class="fa fa-trash-o"></i> <?= lang('delete') ?>
            </a>
        </div>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-key"></i><?= lang('inventory_opening_balances'); ?></h2>
                </li>

                <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('system_settings/inventory_opening_balances') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('system_settings/inventory_opening_balances/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>  
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('system_settings/inventory_opening_balances') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('system_settings/inventory_opening_balances/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->

                <div class="table-responsive">
                    <table id="iovData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-2"><?= lang("date"); ?></th>
                            <th class="col-xs-2"><?= lang("reference_no"); ?></th>
                            <th class="col-xs-2"><?= lang("created_by"); ?></th>
                            <th><?= lang("note"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="min-width:75px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th>
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
<?php }
?>

<script language="javascript">
    $(document).ready(function () {
        $('#add_inventory_opening_balance').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>
