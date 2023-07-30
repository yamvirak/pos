<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#ALData').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('sales/getAssigns' . ($warehouse_id ? '/' . $warehouse_id : '/0').($payment_status ? '/' . $payment_status : ''))?>',
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
                return nRow;
            },
            "aoColumns": [
			{"bSortable": false,"mRender": checkbox}, 
			{"mRender": fld}, 
			{"sClass" : "left"},
			{"sClass" : "left"},
			{"sClass" : "left"},
			{"sClass" : "left"},
			{"sClass" : "left"},
			{"mRender": row_status, "sClass" : "center"},
			{"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {                    
                }
                var nCells = nRow.getElementsByTagName('th');                
            }			
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('assign_to');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},         
            {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []}    
        ], "footer");


    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
	    echo form_open('sales/sale_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
			<i class="fa-fw fa fa-heart"></i>
			<?=lang('assign_sales');?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?=lang('list_results');?></p>
                <div class="table-responsive">
                    <table id="ALData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("created_by"); ?></th>
                            <th><?= lang("assign_to"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
							<th><?= lang("biller"); ?></th>
                            <th><?= lang("note"); ?></th>                            
                            <th><?= lang("status"); ?></th>
                            <th><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
<?php if ($Owner) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?>
