
<script>
	$(document).ready(function () {
		var oTable = $('#dataTable').dataTable({
			"aaSorting": [[1, "asc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': "<?= site_url('reports/getLoginHistories/?v=1'.$v) ?>",
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
				},
				"aoColumns": [		
				{"sClass" : "center"},
				{"sClass" : "center"},
				{"mRender" : fld, "sClass" : "center"},]
				,'fnRowCallback': function (nRow, aData, iDisplayIndex) {
						var oSettings = oTable.fnSettings();
						var action = $('td:eq(15)', nRow);	
						return nRow;
					},
					"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {		
					}
				}).fnSetFilteringDelay().dtFilter([            				
					{column_number: 0, filter_default_label: "[<?= lang("ip_address") ?>]", filter_type: "text", data: []},
					{column_number: 1, filter_default_label: "[<?= lang("user") ?>]", filter_type: "text", data: []},
					{column_number: 2, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},					
				], "footer");
	});
</script>
	<div class="box">
		<div class="box-header">
			<h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('login_histories'); ?></h2>
		</div>
		<div class="box-content">
			<div class="row">
				<?= form_open_multipart("reports/login_histories"); ?>
				<div class="col-sm-12">
					<p class="introtext"><?= lang('list_results'); ?></p>			
					<div class="table-responsive">
						<table id="dataTable" class="table table-condensed table-bordered table-hover table-striped">
							<thead>
								<tr>
									<th width="150"><?= lang("ip_address") ?></th>
									<th width="200"><?= lang("user") ?></th>
									<th width="100"><?= lang("date") ?></th>
								</tr>
							</thead>
							<tbody>
							</tbody>					
							<tfoot>
								<tr>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<?= form_close(); ?>
			</div>
		</div>
	</div>
<script language="javascript">
    $(document).ready(function () {        
		$(".form").slideUp();
        $('.toggle_down').click(function () {
            $(".form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $(".form").slideUp();
            return false;
        });
    });
</script>
<style type="text/css">
	.table {
		white-space:nowrap;
	}
</style>
	
