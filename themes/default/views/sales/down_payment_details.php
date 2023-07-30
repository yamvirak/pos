<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
		oTable = $('#SptData').dataTable({
			"aaSorting": [[1, "asc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= site_url('sales/getSeparatePaymentDetails/'.$id) ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			"aoColumns": [
			{"mRender" : checkbox, "bSortable" : false},
			{"mRender" : fsd, "sClass":"center"}, 
			null,
			null, 
			{"mRender":currencyFormat},
			{"mRender":currencyFormat},
			{"mRender":currencyFormat},
			{"mRender":row_status},
			{"bSortable": false <?php if(isset($down_payment) && $down_payment->status  == 'inactive'){ echo ', "bVisible":false '; } ?>}],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {              
				nRow.id = aData[0];
                nRow.className = "";
				var action = $('td:eq(8)', nRow);
                return nRow;
            },"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var payment = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
					payment += parseFloat(aaData[aiDisplay[i]][4]);
                    paid += parseFloat(aaData[aiDisplay[i]][5]);
					balance += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[4].innerHTML = currencyFormat(parseFloat(payment));
                nCells[5].innerHTML = currencyFormat(parseFloat(paid));
				nCells[6].innerHTML = currencyFormat(parseFloat(balance));
            }
		}).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('deadline');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('sale_reference_no');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('payment');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('paid');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('balance');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php if ($Owner || $Admin) {
    echo form_open('sales/down_payment_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i>
			<?= lang('down_payment_details'); ?>
			<?php 
				if(isset($inv)){
					echo ' ('.lang('sale').' '.lang('reference').': '.$inv->reference_no.')';
					if($down_payment->status  == 'inactive'){
						echo ' <label class="label label-danger">'.lang($down_payment->status).'</label>';
					}
				}
			?>
		</h2>
	</div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="SptData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
						<thead>
						<tr class="primary">
							<th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
							<th class="col-xs-2"><?= lang("deadline"); ?></th>
							<th class="col-xs-2"><?= lang("sale_reference_no"); ?></th>
							<th class="col-xs-2"><?= lang("customer"); ?></th>
							<th class="col-xs-2"><?= lang("payment"); ?></th>
							<th class="col-xs-2"><?= lang("paid"); ?></th>
							<th class="col-xs-2"><?= lang("balance"); ?></th>
							<th class="col-xs-2"><?= lang("status"); ?></th>
							<th style="width:85px;"><?= lang("actions"); ?></th>
						</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
							</tr>
						</tbody>
						<tfoot>
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