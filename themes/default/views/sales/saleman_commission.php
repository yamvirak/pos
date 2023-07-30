<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
	function commission (x, c = ''){
		var res = x.split('_');
		var grand_total = formatDecimalRaw(res[0]);
		var commision_rate = res[1];
		var commission_amount = 0;
		if (commision_rate.indexOf("%") >= 0){
			var d =  commision_rate.split('%');
			var a = grand_total * formatDecimalRaw(d[0]);
			if(a > 0){
				commission_amount =a / 100;
			}
		}else{
			commission_amount = formatDecimalRaw(commision_rate);
		}
		if(c=='disableFormatMoney'){
			return (commission_amount);
		}else{
			return currencyFormat(commission_amount);
		}

		
	}

    $(document).ready(function () {
        oTable = $('#SMData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('sales/getSalemanCommissions') ?>',
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
                nRow.className = "invoice_link";
				
				var action = $('td:eq(12)', nRow);
				var status = $('td:eq(11)', nRow);
				var balance = $('td:eq(10)', nRow);
	
				if(formatDecimal(aData[9]) == 0){
					status.html(row_status("pending"));
					action.find('.view_payment').remove();
				}else if(formatDecimal(aData[9]) == formatDecimal(commission(aData[8],'disableFormatMoney'))){
					status.html(row_status("completed"));
					action.find('.add_payment').remove();
				}else{
					status.html(row_status("partial"));
				}
				balance.html(currencyFormat(formatDecimal((commission(aData[8],'disableFormatMoney'))-formatDecimal(aData[9]))));
                return nRow;
            },
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0,  total_comission = 0, paid = 0;
                for (var i = 0; i < aaData.length; i++) {
					gtotal += parseFloat(aaData[aiDisplay[i]][6]);
					total_comission += parseFloat(commission(aaData[aiDisplay[i]][8],'disableFormatMoney'));
					paid += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
				nCells[8].innerHTML = currencyFormat(parseFloat(total_comission));
				nCells[9].innerHTML = currencyFormat(parseFloat(paid));
				nCells[10].innerHTML = currencyFormat(parseFloat(total_comission-paid));
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null,null, null, null, {"bSearchable" : false,"mRender": currencyFormat}, {"sClass": "center"}, {"bSearchable" : false, "mRender": commission},{"bSearchable" : false,"mRender": currencyFormat},null, null, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('rate');?>]", filter_type: "text", data: []},


        ], "footer");
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {?><?= form_open('sales/saleman_commission_action', 'id="action-form"') ?><?php } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('saleman_commissions'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <li><a href="#" id="pdf" data-action="export_pdf"><i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?></a></li>
						<li id="payment_box">
							<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment">
								<i class="fa fa-money"></i> <?=lang('add_payment')?>
							</a>
                        </li>
					</ul>
					
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
				<div class="table-responsive">
					<table id="SMData" class="table table-bordered table-hover table-striped table-condensed">
						<thead>
						<tr>
							<th style="min-width:30px; width: 30px; text-align: center;">
								<input class="checkbox checkft" type="checkbox" name="check"/>
							</th>
							<th><?= lang("date"); ?></th>
							<th><?= lang("reference_no"); ?></th>
							<th><?= lang("customer"); ?></th>
							<th><?= lang("group"); ?></th>
                            <th><?= lang("saleman"); ?></th>
							<th><?= lang("grand_total"); ?></th>
							<th><?= lang("rate"); ?></th>
                            <th><?= lang("amount"); ?></th>
							<th><?= lang("paid"); ?></th>
							<th><?= lang("balance"); ?></th>
							<th><?= lang("status"); ?></th>
							<th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td colspan="13" class="dataTables_empty"><?= lang("loading_data"); ?></td>
						</tr>
						</tbody>
						<tfoot class="dtFilter">
						<tr class="active">
							<th style="min-width:30px; width: 30px; text-align: center;">
								<input class="checkbox checkft" type="checkbox" name="check"/>
							</th>
							<th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>

							<th style="text-align:center"><?= lang("status"); ?></th>
							<th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
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
        <?= form_submit('perform_action', 'perform_action', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            $(document).on('click', '#delete', function(e) {
                e.preventDefault();
                $('#form_action').val($(this).attr('data-action'));
                //$('#action-form').submit();
                $('#action-form-submit').click();
            });
			$(document).on('click', '#create_sale', function(e) {
                e.preventDefault();
                $('#form_action').val($(this).attr('data-action'));
                $('#action-form-submit').click();
            });
			
			$('#multi_payment').live('click',function(){
				var sale_id = '';
				var intRegex = /^\d+$/;
				var i = 0;
				$('.input-xs').each(function(){
					if ($(this).is(':checked') && intRegex.test($(this).val())) {
						if(i==0){
							sale_id += $(this).val();
							i=1;
						}else{
							sale_id += "SaleID"+$(this).val();
						}
						
					}
				});
				if(sale_id==''){
					alert("<?= lang('no_sale_selected') ?>")
					return false;
				}else{
					var link = '<?= anchor('sales/add_commission_payment/#######', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="multi_payment"')?>';
					var add_payment_link = link.replace("#######", sale_id);
					$("#payment_box").html(add_payment_link);
					$('.multi_payment').click();
					$("#payment_box").html('<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment"><i class="fa fa-money"></i> <?=lang('add_payment')?></a>');		
					return false;
				}
			});
        });
		
    </script>
<?php } ?>