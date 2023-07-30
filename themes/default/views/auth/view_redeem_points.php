<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<ul id="myTab" class="nav nav-tabs">
	<li><a href="#view_redeem_points" class="tab-grey"><?= lang('view_redeem_points') ?></a></li>
</ul>
<div class="tab-content">
	<div id="view_redeem_points" class="tab-pane fade in">
		<script>
			$(document).ready(function () {
				oTable = $('#table_view_redeem_points').dataTable({
					"aaSorting": [[1, "asc"]],
					"aLengthMenu": [[-1], ["<?= lang('all') ?>"]],
					"iDisplayLength": -1,
					'bProcessing': true, 'bServerSide': true,
					'sAjaxSource': '<?= site_url('auth/getRedeemPoints/'.$id) ?>',
					'fnServerData': function (sSource, aoData, fnCallback) {
						aoData.push({
							"name": "<?= $this->security->get_csrf_token_name() ?>",
							"value": "<?= $this->security->get_csrf_hash() ?>"
						});
						$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
					},
					"aoColumns": [
						{"mRender" : fld}, 
						{"mRender" : currencyFormat}, 
						null,
						null,
						{"bSortable": false}],
					'fnRowCallback': function (nRow, aData, iDisplayIndex) {
						var oSettings = oTable.fnSettings();
						nRow.id = aData[0];
						return nRow;
					},
					"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
						var amount = 0;
						for (var i = 0; i < aaData.length; i++) {
							amount += parseFloat(aaData[aiDisplay[i]][1]);
						}
						var nCells = nRow.getElementsByTagName('th');
						nCells[1].innerHTML = currencyFormat(parseFloat(amount));
					}
				}).fnSetFilteringDelay().dtFilter([
					{column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
					{column_number: 2, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
					{column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
				], "footer");
				
				$('#pdf').click(function (event) {
					event.preventDefault();
					window.location.href = "<?=site_url('auth/redeem_points_actions/'.$id.'/1/0')?>";
					return false;
				});
				$('#xls').click(function (event) {
					event.preventDefault();
					window.location.href = "<?=site_url('auth/redeem_points_actions/'.$id.'/0/1')?>";
					return false;
				});
			});
		</script>
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa fa-heart-o"></i><?= lang('view_redeem_points'); ?></h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
							</a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							   <li>
									<li>
										<a href="#" id="xls" data-action="export_excel"><i class="fa fa-file-excel-o"></i> 
											<?= lang('export_to_excel') ?>
										</a>
									</li>
									<li>
										<a href="#" id="pdf" data-action="export_pdf"><i class="fa fa-file-pdf-o"></i> 
											<?= lang('export_to_pdf') ?>
										</a>
									</li>
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
							<table id="table_view_redeem_points" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
								<thead>
								<tr class="active">
									<th width='180'><?= lang("date") ?></th>
									<th width='180'><?= lang("amount") ?></th>
									<th width='180'><?= lang("note") ?></th>
									<th width='180'><?= lang("created_by") ?></th>
									<th width='5%'><?= lang("actions") ?></th>
								</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
									</tr>
								</tbody>
								<tfoot class="dtFilter">
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									 <th style="width:25px;"><?php echo $this->lang->line("actions"); ?></th>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>