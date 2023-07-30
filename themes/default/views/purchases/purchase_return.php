<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#POData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('purchases/getPurchaseReturn/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : '').($payment_status ? '/' . $payment_status : ''));?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, null, null, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": pay_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "purchase_link";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, credit=0,  paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
                    credit += parseFloat(aaData[aiDisplay[i]][7]);
                    paid += parseFloat(aaData[aiDisplay[i]][6]);
                    balance += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(total);
                nCells[7].innerHTML = currencyFormat(credit);
                nCells[8].innerHTML = currencyFormat(paid);
                nCells[9].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('ref_no_to');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");

    });
    


</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo form_open('purchases/purchase_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-star"></i><?= lang('purchase_returns').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2>  -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="bpo btn btn-danger btn-block box_sub_menu" tabindex="-1"
                title="<b><?=lang("delete_purchases")?></b>"
                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                data-html="true" data-placement="left">
                <i class="fa fa-trash-o"></i> <?=lang('delete_purchases')?>
            </a>
        </div>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-shopping-cart"></i><?= lang('purchase_returns'); ?></h2>
                </li>
           
                <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchases/purchase_return') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('purchases/purchase_return/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchases/purchase_return') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('purchases/purchase_return/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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

                <!-- <p class="introtext"><?=lang('list_results');?></p> -->

                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("ref_no"); ?></th>
                            <th><?= lang("ref_no_to"); ?></th>
                            <th><?= lang("project"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("total_return"); ?></th>
                            <th><?= lang("debit_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?=lang('loading_data_from_server');?></td>
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
                            <th><?= lang("total_return"); ?></th>
                            <th><?= lang("debit_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
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

<?php if(!$Settings->project){ ?>
<style type="text/css">
    #POData th:nth-child(5), #POData td:nth-child(5){
        display:none !important;
    }
</style>
<?php } ?>