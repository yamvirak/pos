<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_receive_vat'); ?></h4>
        </div>
        <?php echo form_open_multipart("purchases/add_receive_vat/" . $id, isset($attrib)? $attrib: ''); ?>
        <div class="modal-body">
			<p><?= lang('enter_info'); ?></p>
			
			<div class="row">
				<div class="col-sm-12">
					<div class="clearfix"></div>
					<table class="table table-bordered table-striped table-condensed" style="white-space:nowrap;">
						<thead>
							<tr>
								<th width="3%"><?= lang("no"); ?></th>
								<th width="100px"><?= lang("date") ?></th>
								<th width="100px"><?= lang("ref") ?></th>
								<th width="100px"><?= lang("amount") ?></th>
								<th width="3%"><a id="add_receive_vat" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a></th>
							</tr>
						</thead>
						<tbody id="element_data">						
							<?php
								$total_vat = 0;
								if($receive_vats){
									$tbody = "";
									$i = 1;
									foreach($receive_vats as $receive_vat){
										$total_vat += $receive_vat->amount;
										$tbody .= "<tr>
														<td class='text-center'>".$i++."</td>
														<td><input type='text' value='".$this->cus->hrld($receive_vat->date)."' name='vat_date[]' class='form-control datetime vat_date'/></td>
														<td><input type='text' value='".$receive_vat->reference."' name='reference[]' class='form-control reference' /></td>
														<td><input type='text' value='".$receive_vat->amount."' name='amount[]' class='form-control text-right amount'/></td>
														<td class='text-center'><a href='#' class='btn btn-sm delete_vat'><i class='fa fa-trash'></i></a></td>
													</tr>";
									}
									echo $tbody;
								}
							?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="3" class="text-right"><b><?= lang('total') ?></b></th>
								<th id="total_vat" class="text-right"><?= $this->cus->formatDecimal($total_vat) ?></th>
								<th></th>
							</tr>
						</tfoot>
						
					</table>

				</div>
			</div>
			<div class="clearfix"></div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript">
	$(document).ready(function(){
		$(".delete_vat").bind("click",delete_vat);
		$("#add_receive_vat").on("click",function(){
			var i = $("#element_data tr").length + 1;
			var html = "";
				html += "<tr>";
					html += "<td class=\"text-center\">"+i+"</td>";
					html += "<td><input type='text' value='<?= $Settings->date_with_time == 0 ? date('d/m/Y') : date('d/m/Y H:i') ?>' name='vat_date[]' class='form-control datetime vat_date' /></td>";
					html += "<td><input type='text' name='reference[]' class='form-control reference' /></td>";
					html += "<td><input type=\"text\" name=\"amount[]\" class=\"form-control text-right amount\" /></td>";
					html += "<td class=\"text-center\">";
						html += "<a href=\"#\" class=\"btn btn-sm delete_vat\">";
							html += "<i class=\"fa fa-trash\"></i>";
						html += "</a>";
					html += "</td>";
			html += "</tr>";
			$("#element_data").append(html);
			$(".delete_vat").bind("click",delete_vat); 
			$("select").select2();
			return false;
		});
		function delete_vat(){
			var parent = $(this).parent().parent();
			parent.remove();
			calVAT();
			return false;
		}	
		function disable_str(e){
			if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
				(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
				(e.keyCode >= 35 && e.keyCode <= 40)) {
					 return;
			}
			if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
				e.preventDefault();
			}								
		}
		
		var old_amount;
		$(document).on("focus", '.amount', function () {
			old_amount = $(this).val();
		}).on("change", '.amount', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_amount);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			calVAT();
		}); 

		function calVAT(){
			var total_vat = 0;
			$(".amount").each(function(){
				total_vat += $(this).val()-0;
			});
			$('#total_vat').html(formatDecimal(total_vat));
		}
	});
	
</script>
