<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#PODAta2').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('purchase_requests/getPurchaseRequests/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : '')); ?>',
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
                nRow.className = "purchase_request_link";
                var action = $('td:eq(8)', nRow);
                var checkbox = $('td:eq(0)', nRow); 
                
                if(aData[6] == 'approved'){
                    action.find('.edit_purchase_request').remove();
                    action.find('.approve_purchase_request').remove();
                    action.find('.delete_purchase_request').remove();
                }
                if(aData[6] == 'partial'){
                    action.find('.edit_purchase_request').remove();
                    action.find('.approve_purchase_request').remove();
                    action.find('.delete_purchase_request').remove();
                    action.find('.reject_purchase_request').remove();
                    action.find('.unapprove_purchase_request').remove();
                }
                if(aData[6] != 'partial' && aData[6] != 'approved'){
                    action.find('.create_purchase').remove();
                }
                if(aData[6] == 'pending'){
                    action.find('.unapprove_purchase_request').remove();
                }
                
                if(aData[6] == 'completed'){
                    action.find('.create_purchase').remove();
                    action.find('.edit_purchase_request').remove();
                    action.find('.approve_purchase_request').remove();
                    action.find('.unapprove_purchase_request').remove();
                    action.find('.delete_purchase_request').remove();
                    action.find('.reject_purchase_request').remove();
                }
                
                if(aData[6] == 'rejected'){
                    action.find('.create_purchase').remove();
                    action.find('.edit_purchase_request').remove();
                    action.find('.delete_purchase_request').remove();
                    action.find('.reject_purchase_request').remove();
                    action.find('.unapprove_purchase_request').remove();
                }
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][5]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(total);
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, {"mRender": decode_html},{"mRender": currencyFormat}, {"mRender": row_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        <?php if($this->session->userdata('remove_prls')) { ?>
        if (localStorage.getItem('pritems')) {
            localStorage.removeItem('pritems');
        }
        if (localStorage.getItem('prdiscount')) {
            localStorage.removeItem('prdiscount');
        }
        if (localStorage.getItem('prtax2')) {
            localStorage.removeItem('prtax2');
        }
        if (localStorage.getItem('prshipping')) {
            localStorage.removeItem('prshipping');
        }
        if (localStorage.getItem('prref')) {
            localStorage.removeItem('prref');
        }
        if (localStorage.getItem('prwarehouse')) {
            localStorage.removeItem('prwarehouse');
        }
        if (localStorage.getItem('prsupplier')) {
            localStorage.removeItem('prsupplier');
        }
        if (localStorage.getItem('prnote')) {
            localStorage.removeItem('prnote');
        }
        if (localStorage.getItem('prcustomer')) {
            localStorage.removeItem('prcustomer');
        }
        if (localStorage.getItem('prbiller')) {
            localStorage.removeItem('prbiller');
        }
        if (localStorage.getItem('qucurrency')) {
            localStorage.removeItem('qucurrency');
        }
        if (localStorage.getItem('prdate')) {
            localStorage.removeItem('prdate');
        }
        if (localStorage.getItem('prstatus')) {
            localStorage.removeItem('prstatus');
        }
        <?php $this->cus->unset_data('remove_prls'); } ?>
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('purchase_requests/purchase_request_action', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('purchase_requests').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2> -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?= site_url('purchase_requests/add') ?>" class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang('add_purchase_request') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="<?= site_url('purchase_requests/add_purchase_request_by_excel') ?>"
                class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang('add_purchase_request_by_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" class="btn btn-primary btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="bpo btn btn-danger btn-block box_sub_menu" tabindex="-1"
                title="<b><?= $this->lang->line("delete_purchase_requests") ?></b>" 
                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_purchase_requests') ?>
            </a>
        </div>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-shopping-cart"></i><?= lang('purchase_requests'); ?></h2>
                </li>
                
                 <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchase_requests/') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('purchase_requests/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchase_requests/index') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('purchase_requests/index/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                    <table id="PODAta2" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("project"); ?></th>   
                            <th><?= lang("note"); ?></th>
                            <th><?= lang("total"); ?></th>
                            <th><?= lang("status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="10"
                                class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
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
    #PODAta2 th:nth-child(4), #PODAta2 td:nth-child(4){
        display:none !important;
    }
</style>
<?php } ?>