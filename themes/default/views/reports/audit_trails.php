<?php
	$v = "";
	if($this->input->post('type')){
		$v .= "&type=" . $this->input->post('type');
	}
?>
<script>
	$(document).ready(function () {
		var oTable = $('#dataTable').dataTable({
			"aaSorting": [[6, "desc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': "<?= site_url('reports/getAuditTrails/?v=1'.$v) ?>",
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
				},
				"aoColumns": [		
				null,
				null,
				null,
				{"mRender" : function(e) {
								var old_values = " "+ e;
								if(old_values.length > 80){
									return "<span class='text'>" + old_values.substr(1,80) + "....</span><a text='"+old_values+"' class='clview'>View</a>";
								}else{
									return old_values;
								}
							 } 
				},
				{"mRender" : function(e) { 
								var new_values = " "+ e;
								if(new_values.length > 80){
									return "<span class='text'>" + new_values.substr(1,80) + "....</span><a text='"+new_values+"' class='clview'>View</a>";
								}else{
									return new_values;
								}
							 } 
				},
				null,
				{"mRender" : fld}]
				,'fnRowCallback': function (nRow, aData, iDisplayIndex) {
						var oSettings = oTable.fnSettings();
						nRow.id = aData[7];
						nRow.className = "audit_trail_link";
						return nRow;
					},
					"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {		
					}
				}).fnSetFilteringDelay().dtFilter([            				
					{column_number: 0, filter_default_label: "[<?= lang("user") ?>]", filter_type: "text", data: []},
					{column_number: 1, filter_default_label: "[<?= lang("event") ?>]", filter_type: "text", data: []},
					{column_number: 2, filter_default_label: "[<?= lang("table") ?>]", filter_type: "text", data: []},
					{column_number: 3, filter_default_label: "[<?= lang("old_values") ?>]", filter_type: "text", data: []},
					{column_number: 4, filter_default_label: "[<?= lang("new_values") ?>]", filter_type: "text", data: []},
					{column_number: 5, filter_default_label: "[<?= lang("url") ?>]", filter_type: "text", data: []},
					{column_number: 6, filter_default_label: "[<?= lang("date") ?>]", filter_type: "text", data: []},
				], "footer");
				
				$('body').on('click', '.clview', function() {
					var text = $(this).attr("text");
					if($(this).text() == "View"){
						$(this).parent().find(".text").text(text);
						$(this).text("Hide");
					}else{
						$(this).parent().find(".text").text(text.substr(1,80)+"....");
						$(this).text("View");
					}
				});
								
	});
	
</script>
	<div class="box">
		<div class="box-header">
			<div class="box-icon">
            	<ul class="btn-tasks">
					<li class="dropdown">
						<h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('audit_trails'); ?></h2>
					</li>
				</ul>
			</div>
		</div>
		<div class="box-content">
			<div class="row">
				<?= form_open_multipart("reports/audit_trails"); ?>
				<div class="col-sm-12">
					<!-- <p class="introtext"><?= lang('list_results'); ?></p>			 -->
					<div class="table-responsive">
						<table id="dataTable" class="table table-condensed table-bordered table-hover table-striped">
							<thead>
								<tr>
									<th width="150"><?= lang("user") ?></th>
									<th width="200"><?= lang("event") ?></th>
									<th width="200"><?= lang("table") ?></th>
									<th width="200"><?= lang("old_values") ?></th>
									<th width="200"><?= lang("new_values") ?></th>
									<th width="200"><?= lang("url") ?></th>
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
									<th></th>
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
	
