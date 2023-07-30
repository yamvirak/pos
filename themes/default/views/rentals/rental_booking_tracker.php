<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var pb = <?= json_encode($pb); ?>;
        var lang = { 'deduct_deposit' : "<?=lang('deduct_deposit')?>" };
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : lang[x]) : lang[x];
        }
        function ref(x) {
            return (x != null) ? x : ' ';
        }
        oTable = $('#table_rental').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('rentals/getRentalDeposits/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, {"mRender": ref}, {"sClass": "center"}, null, null,{"mRender":fsd},null, null, {"mRender": currencyFormat}, {"mRender": row_status}, {"bVisible": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[11];
                if(aData[13] == "RentalDeposit" || aData[13] == "ReturnRentalDeposit"){
                    nRow.className = "rental_deposit_link";
                }else if (aData[12] > 0) {
                    if(aData[10]=='returned'){
                        nRow.className = "payment_link warning";
                    }else{
                        nRow.className = "payment_link";
                    }
                }
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    if (aaData[aiDisplay[i]][11] == 'sent' || aaData[aiDisplay[i]][11] == 'expense' || aaData[aiDisplay[i]][11] == 'pawn_sent'){
                        total -= Math.abs(parseFloat(aaData[aiDisplay[i]][9]));
                    }else if (aaData[aiDisplay[i]][11] == 'returned' && aaData[aiDisplay[i]][12] > 0){
                        total -= Math.abs(parseFloat(aaData[aiDisplay[i]][9]));
                    }else{
                        total += parseFloat(aaData[aiDisplay[i]][9]);
                    }    
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[9].innerHTML = currencyFormat(parseFloat(total));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('payment_ref');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('rental_ref');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('room');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('checked_in_date');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<?php if ($Owner || $Admin) {
    echo form_open('rentals/rental_actions', 'id="action-form"');
} ?>
<div class="box">

    <div class="box-header box-header">            
        <div class="box-icon">
            <ul class="btn-tasks">
                <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('rentals/') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('rentals/index/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('rentals/index') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('rentals/index/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                <h2 class="blue"><i class="fa fa-bed"></i><?= lang('checked_in_list'); ?></h2>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive" style="width:100% !important;">
                    <table id="table_rental" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered">
                        <thead>
                            <tr class="primary">
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("payment_ref"); ?></th>
                                <th><?= lang("rental_ref"); ?></th>
                                <th><?= lang("room"); ?></th>
                                <th><?= lang("customer"); ?></th>
                                <th><?= lang("phone"); ?></th>
                                <th><?= lang("checked_in_date"); ?></th>
                                <th><?= lang("created_by"); ?></th>
                                <th><?= lang("paid_by"); ?></th>
                                <th><?= lang("amount"); ?></th>
                                <th><?= lang("type"); ?></th>
                                <th style="width:85px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
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
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
  
</div>
<?php if ($Owner || $Admin) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>