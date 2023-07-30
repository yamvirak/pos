<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";

if ($this->input->post('supplier')) {
    $v .= "&supplier=" . $this->input->post('supplier');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<?php echo form_open('reports/ap_aging/', 'id="action-form"');	?>
<div class="box">
	<div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('ap_aging'); ?>
        </h2>
		<div class="box-icon">
			<ul class="btn-tasks">
				<li class="dropdown">
					<a href="#" class="toggle_up tip" title="<?= lang('hide_form'); ?>">
						<i class="icon fa fa-toggle-up"></i>
					</a>
				</li>
				<li class="dropdown">
					<a href="#" class="toggle_down tip" title="<?= lang('show_form'); ?>">
						<i class="icon fa fa-toggle-down"></i>
					</a>
				</li>
			</ul>
		</div>
		<div class="box-icon">
			<ul class="btn-tasks">
				
				<li class="dropdown"><a href="#" id="xls" data-action="export_excel" class="tip" title="<?= lang('download_xls'); ?>"><i class="icon fa fa-file-excel-o"></i></a></li>						
				
			</ul>
		</div>
	</div>				
	<?php echo form_close();?>
	<div class="box-content">
		<div class="row">
			<div class="col-lg-12">
				<p class="introtext"><?= lang('customize_report'); ?></p>
				<div id="form">
					<?php echo form_open("reports/ap_aging/"); ?>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="biller"><?= lang("biller"); ?></label>
									<?php
									$bl[""] = "";
									foreach ($billers as $biller) {
										$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
									}
									echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
									?>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
									<?php
									$wh[""] = "";
									foreach ($warehouses as $warehouse) {
										$wh[$warehouse->id] = $warehouse->name;
									}
									echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
									?>
								</div>
							</div>
							
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="supplier"><?= lang("supplier"); ?></label>
									<?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("supplier") . '"'); ?>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("end_date", "end_date"); ?>
									<?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : $this->cus->hrsd(date("Y-m-d"))), 'class="form-control date" id="end_date"'); ?>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="controls"> 
								<?php echo form_submit('submit_sale_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
							</div>
						</div>
					<?php echo form_close(); ?>
				</div>
				<div class="clearfix"></div>
				<!-- AR Aging Column -->
				<script>
					$(document).ready(function () {
						var oTable = $('#First').dataTable({
							"aaSorting": [[0, "asc"], [1, "desc"]],
							"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
							"iDisplayLength": <?=$Settings->rows_per_page?>,
							'bProcessing': true,'bServerSide': true,
							'sAjaxSource': "<?= site_url('reports/getAPAging/?v=1'.$v)?>",
							'fnServerData': function (sSource, aoData, fnCallback) {
								aoData.push({
									"name": "<?=$this->security->get_csrf_token_name()?>",
									"value": "<?=$this->security->get_csrf_hash()?>"
								});
								$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
							},
							'fnRowCallback': function (nRow, aData, iDisplayIndex) {
								var oSettings = oTable.fnSettings();										
								var id = aData[0];
								if($('#end_date').val()){
									var end_date = formatDateYMD($('#end_date').val());
									id = id+'/'+end_date;
								}else{
									id = id+'/0'
								}
								var biller_id = $('#biller').val();
								id = id+'/'+biller_id;
								nRow.id = id;
								nRow.className = "ap_aging";										
								return nRow;
							},
							"aoColumns": [{
								"bSortable": false,
								"mRender": checkbox
							},
							null,
							{"mRender": currencyFormat,"bSortable" : true,"bSearchable" : true},
							{"mRender": currencyFormat,"bSortable" : true,"bSearchable" : true},
							{"mRender": currencyFormat,"bSortable" : true,"bSearchable" : true},
							{"mRender": currencyFormat,"bSortable" : true,"bSearchable" : true},
							{"mRender": currencyFormat,"bSortable" : true,"bSearchable" : true},
							{"mRender": currencyFormat,"bSortable" : true,"bSearchable" : true}],
							"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
								var balance1 = 0, balance2 = 0, balance3 = 0, balance4 = 0,  balance0 = 0, total = 0;
								for (var i = 0; i < aaData.length; i++) {
									balance0 += parseFloat(aaData[aiDisplay[i]][2]);
									balance1 += parseFloat(aaData[aiDisplay[i]][3]);
									balance2 += parseFloat(aaData[aiDisplay[i]][4]);
									balance3 += parseFloat(aaData[aiDisplay[i]][5]);
									balance4 += parseFloat(aaData[aiDisplay[i]][6]);
									total += parseFloat(aaData[aiDisplay[i]][7]);
								}
								var nCells = nRow.getElementsByTagName('th');
								nCells[2].innerHTML = currencyFormat(parseFloat(balance0));
								nCells[3].innerHTML = currencyFormat(parseFloat(balance1));
								nCells[4].innerHTML = currencyFormat(parseFloat(balance2));
								nCells[5].innerHTML = currencyFormat(parseFloat(balance3));
								nCells[6].innerHTML = currencyFormat(parseFloat(balance4));
								nCells[7].innerHTML = currencyFormat(parseFloat(total));
							}
						}).fnSetFilteringDelay().dtFilter([
								
						{column_number: 1, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},], "footer");
					});
				</script>
				<div class="table-responsive">
					<table id="First" class="table table-striped table-bordered table-condensed table-hover dtable">
						<thead>
							<tr>
								<th style="min-width:3%; width: 3%; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th width="200"><?php echo lang("supplier"); ?></th>
								<th	width="120"><?php echo lang("current"); ?></th>
								<th	width="120"><?php echo lang("1 - 30 Days"); ?></th>
								<th	width="120"><?php echo lang("31 - 60 Days"); ?></th>
								<th	width="120"><?php echo lang("61 - 90 Days"); ?></th>
								<th	width="120"><?php echo lang("> 90 Days"); ?></th>
								<th	width="120"><?php echo lang("total"); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="13" class="dataTables_empty">
									<?php echo $this->lang->line("loading_data"); ?>
								</td>
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
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>			
</div>
	
<style type="text/css">
	.dtable { white-space:nowrap; }
</style>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
	$(document).ready(function () {
		
		var supplier_id = "<?= isset($_POST['supplier'])?$_POST['supplier']:0 ?>";
		if (supplier_id > 0) {
		  $('#supplier_id').val(supplier_id).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
			  $.ajax({
				type: "get", async: false,
				url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
				dataType: "json",
				success: function (data) {
				  callback(data[0]);
				}
			  });
			},
			ajax: {
			  url: site.base_url + "suppliers/suggestions",
			  dataType: 'json',
			  deietMillis: 15,
			  data: function (term, page) {
				return {
				  term: term,
				  limit: 10
				};
			  },
			  results: function (data, page) {
				if (data.results != null) {
				  return {results: data.results};
				} else {
				  return {results: [{id: '', text: 'No Match Found'}]};
				}
			  }
			}
		  });
		}else{
		  $('#supplier_id').select2({
			minimumInputLength: 1,
			ajax: {
			  url: site.base_url + "suppliers/suggestions",
			  dataType: 'json',
			  quietMillis: 15,
			  data: function (term, page) {
				return {
				  term: term,
				  limit: 10
				};
			  },
			  results: function (data, page) {
				if (data.results != null) {
				  return {results: data.results};
				} else {
				  return {results: [{id: '', text: 'No Match Found'}]};
				}
			  }
			}
		  });
		}
		
		
		
		$('#xls').click(function (event) {
			var supplier_id = ''; var i = 0;
			$('.multi-select').each(function(){
				if ($(this).is(':checked')) {
					if(i==0){
						supplier_id += "'"+$(this).val()+"'";
						i=1;
					}else{
						supplier_id += ",'"+$(this).val()+"'";
					}
				}
			});
			if(supplier_id==''){
				alert("<?= lang('no_sale_selected') ?>")
				return false;
			}else{
				var link = "<?=site_url('reports/getAPAging/0/xls/?cid=')?>"+supplier_id;
				event.preventDefault();
				window.location.href = link;
				return false;
			}
        });
		
		$('#pdf').click(function (event) {
			var supplier_id = ''; var i = 0;
			$('.multi-select').each(function(){
				if ($(this).is(':checked')) {
					if(i==0){
						supplier_id += "'"+$(this).val()+"'";
						i=1;
					}else{
						supplier_id += ",'"+$(this).val()+"'";
					}
				}
			});
			if(supplier_id==''){
				alert("<?= lang('no_sale_selected') ?>")
				return false;
			}else{
				var link = "<?=site_url('reports/getAPAging/pdf/0/?cid=')?>"+supplier_id;
				event.preventDefault();
				window.location.href = link;
				return false;
			}
        });
		
		$('#form').hide();
		$('.toggle_down').click(function () {
		   $("#form").slideDown();
		   return false;
		});
		$('.toggle_up').click(function () {
		   $("#form").slideUp();
		   return false;
		});
	});
</script>

