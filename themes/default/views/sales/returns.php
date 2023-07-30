<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<script>
    $(document).ready(function () {
        oTable = $('#SLData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('sales/getReturns/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : '').'/'.($payment_status ? $payment_status : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[8]);
                nRow.className = "return_link re"+aData[8];
                var action = $('td:eq(12)', nRow);
                if(aData[10] == 'paid'){
                    action.find('.add_payment').remove();
                }
                return nRow;
            },
            "aoColumns": [
            {"mRender": checkbox}, 
            {"mRender": fld}, 
            null, 
            null, 
            null,
            {"mRender": currencyFormat}, 
            <?php if($Settings->installment==1){ ?>
                {"mRender": currencyFormat},
            <?php }else{ ?>
                {"bVisible": false},
            <?php } ?>
            {"bSortable": false, "bSearchable": false, "mRender": currencyFormat}, 
            {"mRender": currencyFormat}, {"mRender": currencyFormat},
            {"bSortable": false, "mRender": currencyFormat}, 
            {"bSortable": false, "mRender": pay_status}, 
            {"bSortable": false,"mRender": attachment}, 
            {"bVisible": false}, {"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, interest=0, paid = 0, balance = 0, discount = 0, credit = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][5]);
                    interest += parseFloat(aaData[aiDisplay[i]][6]);
                    credit += parseFloat(aaData[aiDisplay[i]][7]);
                    paid += parseFloat(aaData[aiDisplay[i]][8]);
                    discount += parseFloat(aaData[aiDisplay[i]][9]);
                    balance += parseFloat(aaData[aiDisplay[i]][10]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(parseFloat(gtotal));
                <?php if($Settings->installment==1){ ?>
                    nCells[6].innerHTML = currencyFormat(parseFloat(interest));
                    nCells[7].innerHTML = currencyFormat(parseFloat(credit));
                    nCells[8].innerHTML = currencyFormat(parseFloat(paid));
                    nCells[9].innerHTML = currencyFormat(parseFloat(discount));
                    nCells[10].innerHTML = currencyFormat(parseFloat(balance));
                <?php }else{ ?>
                    nCells[6].innerHTML = currencyFormat(parseFloat(credit));
                    nCells[7].innerHTML = currencyFormat(parseFloat(paid));
                    nCells[8].innerHTML = currencyFormat(parseFloat(discount));
                    nCells[9].innerHTML = currencyFormat(parseFloat(balance));
                <?php } ?>
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            <?php if($Settings->installment==1){ ?>
                {column_number: 11, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
            <?php }else{ ?>
                {column_number: 10, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
            <?php } ?>
        ], "footer");
    });

</script>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {
        echo form_open('sales/sale_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('sale_returns').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?=site_url('sales/return_sale')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_sale_return')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        
                        <li>
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo"
                            title="<b><?=lang("delete_sale_returns")?></b>"
                            data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                            data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?=lang('delete_sale_returns')?>
                        </a>
                    </li>
                    </ul>
                </li>
                <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('sales/returns') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('sales/returns/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('sales/returns') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('sales/returns/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?=lang('list_results');?></p>

                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("reference_no_to"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("total_return"); ?></th>
                            <th><?= lang("credit_interest"); ?></th>
                            <th><?= lang("credit_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("discount"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="16" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th>
                            <th><?= lang("total_return"); ?></th>
                            <th><?= lang("credit_interest"); ?></th>
                            <th><?= lang("credit_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("discount"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
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
<script type="text/javascript">
    $(function(){
        $("#SLData").css("width", "auto");
    });
</script>

