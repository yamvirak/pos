<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">
    <?php if(!$this->config->item('purchase_order')) { ?>
        #POData th:nth-child(4) {
            display: none !important;
        }
        #POData td:nth-child(4) {
            display: none !important;
        } 
    <?php } if(!$this->config->item('receive_item')) { ?>
        #POData th:nth-child(12) {
            display: none !important;
        }
        #POData td:nth-child(12) {
            display: none !important;
        }   
    <?php } ?>
</style>
<script>
    $(document).ready(function () {
    <?php if ($this->session->userdata('remove_pols')) { ?>
        if (localStorage.getItem('poitems')) {
            localStorage.removeItem('poitems');
        }
        if (localStorage.getItem('podiscount')) {
            localStorage.removeItem('podiscount');
        }
        if (localStorage.getItem('potax2')) {
            localStorage.removeItem('potax2');
        }
        if (localStorage.getItem('poshipping')) {
            localStorage.removeItem('poshipping');
        }
        if (localStorage.getItem('poref')) {
            localStorage.removeItem('poref');
        }
        if (localStorage.getItem('powarehouse')) {
            localStorage.removeItem('powarehouse');
        }
        if (localStorage.getItem('ponote')) {
            localStorage.removeItem('ponote');
        }
        if (localStorage.getItem('posupplier')) {
            localStorage.removeItem('posupplier');
        }
        if (localStorage.getItem('posiref')) {
            localStorage.removeItem('posiref');
        }
        if (localStorage.getItem('pocurrency')) {
            localStorage.removeItem('pocurrency');
        }
        if (localStorage.getItem('podate')) {
            localStorage.removeItem('podate');
        }
        if (localStorage.getItem('postatus')) {
            localStorage.removeItem('postatus');
        }
        if (localStorage.getItem('popayment_term')) {
            localStorage.removeItem('popayment_term');
        }
        if (localStorage.getItem('payable_account')) {
            localStorage.removeItem('payable_account');
        }
    <?php $this->cus->unset_data('remove_pols');} ?>
        
        oTable = $('#POData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('purchases/getPurchases/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : 0).($payment_status ? '/' . $payment_status : ''));?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, null, null,null, {"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat},{"mRender": row_status}, {"bSortable": false,"mRender": pay_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                if(aData[8] > 0){
                    nRow.className = "purchase_link warning";
                }else{
                    nRow.className = "purchase_link";
                }
                
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, return_pur=0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][7]);
                    return_pur += parseFloat(aaData[aiDisplay[i]][8]);
                    paid += parseFloat(aaData[aiDisplay[i]][9]);
                    balance += parseFloat(aaData[aiDisplay[i]][10]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(total);
                nCells[8].innerHTML = currencyFormat(return_pur);
                nCells[9].innerHTML = currencyFormat(paid);
                nCells[10].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('po_reference');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('si_reference');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('purchase_status');?>]", filter_type: "text", data: []},
            {column_number: 12, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");

        <?php if ($this->session->userdata('remove_pols')) {?>
        if (localStorage.getItem('poitems')) {
            localStorage.removeItem('poitems');
        }
        if (localStorage.getItem('podiscount')) {
            localStorage.removeItem('podiscount');
        }
        if (localStorage.getItem('potax2')) {
            localStorage.removeItem('potax2');
        }
        if (localStorage.getItem('poshipping')) {
            localStorage.removeItem('poshipping');
        }
        if (localStorage.getItem('poref')) {
            localStorage.removeItem('poref');
        }
        if (localStorage.getItem('powarehouse')) {
            localStorage.removeItem('powarehouse');
        }
        if (localStorage.getItem('ponote')) {
            localStorage.removeItem('ponote');
        }
        if (localStorage.getItem('posupplier')) {
            localStorage.removeItem('posupplier');
        }
        if (localStorage.getItem('pocurrency')) {
            localStorage.removeItem('pocurrency');
        }
        if (localStorage.getItem('podate')) {
            localStorage.removeItem('podate');
        }
        if (localStorage.getItem('postatus')) {
            localStorage.removeItem('postatus');
        }
        if (localStorage.getItem('popayment_term')) {
            localStorage.removeItem('popayment_term');
        }
        <?php $this->cus->unset_data('remove_pols');}
        ?>
    });
    
    $('#multi_payment').live('click',function(){
        var purchase_id = '';
        var intRegex = /^\d+$/;
        var i = 0;
        $('.input-xs').each(function(){
            if ($(this).is(':checked') && intRegex.test($(this).val())) {
                if(i==0){
                    purchase_id += $(this).val();
                    i=1;
                }else{
                    purchase_id += "PurchaseID"+$(this).val();
                }
                
            }
        });
        if(purchase_id==''){
            alert("<?= lang('no_sale_selected') ?>")
            return false;
        }else{
            var link = '<?= anchor('purchases/add_multi_payment/#######', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="multi_payment"')?>';
            var add_payment_link = link.replace("#######", purchase_id);
            $("#payment_box").html(add_payment_link);
            $('.multi_payment').click();    
            $("#payment_box").html('<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment"><i class="fa fa-money"></i> <?=lang('add_payment')?></a>');     
            return false;
        }
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo form_open('purchases/purchase_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
    <!-- <h2 class="blue"><i class="fa-fw fa fa-star"></i><?= lang('purchases').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2>     -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?=site_url('purchases/add')?>" class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?=lang('add_purchase')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
            </a>
        </div>
        <div class="sub_menu" id="payment_box">
            <a href="javascript:void(0)" id="multi_payment" data-action="multi_payment"
                class="btn btn-info btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-money"></i> <?=lang('add_payment')?>
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
                    <h2 class="blue"><i class="icon fa fa-shopping-cart"></i><?= lang('purchases'); ?></h2>
                </li>
                <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchases/') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('purchases/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchases/') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('purchases/index/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("ref_no"); ?></th>
                            <th><?= lang("po_reference"); ?></th>
                            <th><?= lang("si_reference"); ?></th>
                            <th><?= lang("project"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("returned"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("purchase_status"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:90px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="15" class="dataTables_empty"><?=lang('loading_data_from_server');?></td>
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
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("returned"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                            <th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:90px; text-align: center;"><?= lang("actions"); ?></th>
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
    #POData th:nth-child(6), #POData td:nth-child(6){
        display:none !important;
    }
</style>
<?php } ?>