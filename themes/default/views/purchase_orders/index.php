<?php defined('BASEPATH') OR exit('No direct script access allowed');
$v = "";
if($this->input->get("status")){
    $v .= "&status=". $this->input->get("status");
}
?>
<script>
    $(document).ready(function () {
        oTable = $('#PODAta1').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('purchase_orders/getPurchaseOrders/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : '').'/'.("?v=1".$v));?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "purchase_order_link";
                var action = $('td:eq(11)', nRow);
                var checkbox = $('td:eq(0)', nRow); 
                
                if(aData[7] == 0){
                    action.find('.write_off_deposit').remove();
                    action.find('.view_deposit').remove();
                }
                
                if(aData[8] == 'approved'){
                    action.find('.edit_purchase_order').remove();
                    action.find('.approve_purchase_order').remove();
                    action.find('.delete_purchase_order').remove();
                    if(aData[7] > 0){
                        action.find('.unapprove_purchase_order').remove();
                        action.find('.reject_purchase_order').remove();
                    }
                }
                if(aData[8] == 'pending'){
                    action.find('.add_deposit').remove();
                    action.find('.unapprove_purchase_order').remove();
                }
                
                if(aData[8] == 'partial'){
                    action.find('.edit_purchase_order').remove();
                    action.find('.approve_purchase_order').remove();
                    action.find('.unapprove_purchase_order').remove();
                    action.find('.delete_purchase_order').remove();
                    action.find('.reject_purchase_order').remove();
                    action.find('.add_deposit').remove();
                }
                if(aData[8] != 'partial' && aData[8] != 'approved'){
                    action.find('.create_purchase').remove();
                    action.find('.add_receive_item').remove();
                }

                
                if(aData[8] == 'completed'){
                    action.find('.create_purchase').remove();
                    action.find('.add_receive_item').remove();
                    action.find('.edit_purchase_order').remove();
                    action.find('.approve_purchase_order').remove();
                    action.find('.unapprove_purchase_order').remove();
                    action.find('.delete_purchase_order').remove();
                    action.find('.reject_purchase_order').remove();
                    action.find('.add_deposit').remove();
                }

                if(aData[8] == 'rejected'){
                    action.find('.create_purchase').remove();
                    action.find('.add_receive_item').remove();
                    action.find('.edit_purchase_order').remove();
                    action.find('.unapprove_purchase_order').remove();
                    action.find('.delete_purchase_order').remove();
                    action.find('.reject_purchase_order').remove();
                    action.find('.add_deposit').remove();
                }
                if(aData[10] == 1){
                    action.find('.create_purchase').remove();
                }else if(aData[10] == 2){
                    action.find('.add_receive_item').remove();
                }
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, deposit = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
                    deposit += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(total);
                nCells[7].innerHTML = currencyFormat(deposit);
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, null, null, {"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": row_status}, {"bSortable": false,"mRender": attachment},null, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('reference_no_to');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('received');?>]", filter_type: "text", data: []},
        ], "footer");
        
        <?php if($this->session->userdata('remove_porls')) { ?>
        if (localStorage.getItem('poritems')) {
            localStorage.removeItem('poritems');
        }
        if (localStorage.getItem('pordiscount')) {
            localStorage.removeItem('pordiscount');
        }
        if (localStorage.getItem('portax2')) {
            localStorage.removeItem('portax2');
        }
        if (localStorage.getItem('porshipping')) {
            localStorage.removeItem('porshipping');
        }
        if (localStorage.getItem('porref')) {
            localStorage.removeItem('porref');
        }
        if (localStorage.getItem('porwarehouse')) {
            localStorage.removeItem('porwarehouse');
        }
        if (localStorage.getItem('porsupplier')) {
            localStorage.removeItem('porsupplier');
        }
        if (localStorage.getItem('pornote')) {
            localStorage.removeItem('pornote');
        }
        if (localStorage.getItem('porbiller')) {
            localStorage.removeItem('porbiller');
        }
        if (localStorage.getItem('qucurrency')) {
            localStorage.removeItem('qucurrency');
        }
        if (localStorage.getItem('pordate')) {
            localStorage.removeItem('pordate');
        }
        if (localStorage.getItem('porstatus')) {
            localStorage.removeItem('porstatus');
        }
        <?php $this->cus->unset_data('remove_porls'); } ?>
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('purchase_orders/purchase_order_action', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('purchase_orders').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2> -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?= site_url('purchase_orders/add') ?>" class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang('add_purchase_order') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="bpo btn btn-danger btn-block box_sub_menu" tabindex="-1"
                title="<b><?= $this->lang->line("delete_purchase_orders") ?></b>" 
                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_purchase_orders') ?>
            </a>
        </div>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-shopping-cart"></i><?= lang('purchase_orders'); ?></h2>
                </li>
              
                <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchase_orders/') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('purchase_orders/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchase_orders/index') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('purchase_orders/index/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                <div class="table-responsive">
                    <table id="PODAta1" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("reference_no_to"); ?></th>
                            <th><?= lang("project"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("total"); ?></th>
                            <th><?= lang("deposit"); ?></th>
                            <th><?= lang("status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th><?= lang("received"); ?></th>
                            <th style="width:114px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11"
                                class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th style="width:114px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<?php if(!$Settings->project){ ?>
<style type="text/css">
    #PODAta1 th:nth-child(5), #PODAta1 td:nth-child(5){
        display:none !important;
    }
</style>
<?php } ?>

<style type="text/css">
    #PODAta1 th:nth-child(11), #PODAta1 td:nth-child(11){
        display:none !important;
    }
</style>