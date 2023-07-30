<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#QUData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('quotes/getQuotes1/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : '')); ?>',
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
                nRow.className = "quote_link";
				
				var action = $('td:eq(8)', nRow);
				if(aData[6] == 'completed' || aData[6] == 'deleted'){
					action.find('.create_sale').remove();
					action.find('.create_sale_order').remove();
					action.find('.edit_quote').remove();	
					action.find('.delete_quote').remove();
				}
				
                return nRow;
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, {"mRender": decode_html},{"mRender": currencyFormat}, {"mRender": row_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []}, 
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('total');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        <?php if($this->session->userdata('remove_quls')) { ?>
        if (localStorage.getItem('quitems')) {
            localStorage.removeItem('quitems');
        }
        if (localStorage.getItem('qudiscount')) {
            localStorage.removeItem('qudiscount');
        }
        if (localStorage.getItem('qutax2')) {
            localStorage.removeItem('qutax2');
        }
        if (localStorage.getItem('qushipping')) {
            localStorage.removeItem('qushipping');
        }
        if (localStorage.getItem('quref')) {
            localStorage.removeItem('quref');
        }
        if (localStorage.getItem('quwarehouse')) {
            localStorage.removeItem('quwarehouse');
        }
        if (localStorage.getItem('qusupplier')) {
            localStorage.removeItem('qusupplier');
        }
        if (localStorage.getItem('qunote')) {
            localStorage.removeItem('qunote');
        }
        if (localStorage.getItem('qucustomer')) {
            localStorage.removeItem('qucustomer');
        }
        if (localStorage.getItem('qubiller')) {
            localStorage.removeItem('qubiller');
        }
        if (localStorage.getItem('qucurrency')) {
            localStorage.removeItem('qucurrency');
        }
        if (localStorage.getItem('qudate')) {
            localStorage.removeItem('qudate');
        }
        if (localStorage.getItem('qustatus')) {
            localStorage.removeItem('qustatus');
        }
		if (localStorage.getItem('qusaleman')) {
			localStorage.removeItem('qusaleman');
		}
		if (localStorage.getItem('quvalid_day')) {
			localStorage.removeItem('quvalid_day');
		}
        <?php $this->cus->unset_data('remove_quls'); } ?>
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('quotes/quote_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
		<!-- <h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('quotes').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2> -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a class="btn btn-success btn-block box_sub_menu" href="<?= site_url('quotes/add') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_quote') ?>
              </a>
        </div>
        <div class="sub_menu">
            <a class="btn btn-warning btn-block box_sub_menu" href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="bpo btn btn-danger btn-block box_sub_menu" title="<b><?= $this->lang->line("delete_quotes") ?></b>" 
                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_quotes') ?>
            </a>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-line-chart"></i><?= lang('quotes'); ?></h2>
                </li>
               <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('pos/sales') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('pos/sales/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('pos/sales') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('pos/sales/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                    <table id="QUData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("customer"); ?></th>
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