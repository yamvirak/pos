<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_freight'); ?></h4>
        </div>
        <?php //$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		if(isset($freight_type) && $freight_type=='freight_receive'){
			echo form_open_multipart("purchases/add_rec_freight/" . $id, isset($attrib)? $attrib: '');
		}else{
			echo form_open_multipart("purchases/add_freight/" . $id, isset($attrib)? $attrib: '');
		}
        ?>
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
								<th width="100px"><?= lang("supplier") ?></th>							
								<th width="100px"><?= lang("ref") ?></th>
								<th width="100px"><?= lang("amount") ?></th>
								<th width="100px"><?= lang("tax") ?></th>
								<th width="100px"><?= lang("total") ?></th>
								<th width="3%">
									<a id="add_supplier" class="btn btn-sm btn-primary">
										<i class="fa fa-plus"></i>
									</a>
								</th>
							</tr>
						</thead>
						<tbody id="element_data">						
							<?php 
							if($shippings){
								$totalshipping = 0;
								foreach($shippings as $s => $shipping){ 
									$totalshipping += $shipping->total;																	
								?>
									<tr>
										<td class="text-center"><?= ($s+1) ?></td>
										<?php 
											$purchase = $this->purchases_model->getPurchaseByFreightID($shipping->id);
										?>
										<td>
											<input type="hidden" value="<?= ($purchase ? $purchase->id : 0) ?>" name="old_id[]"/>
											<input type="text" value="<?=  ($Settings->date_with_time == 0 ? $this->cus->hrsd($shipping->date) : $this->cus->hrld($shipping->date)) ?>" name="freight_date[]" class="form-control datetime freight_date" />
										</td>
										<td>
											<select class="form-control" name="supplier[]"> 
												<?php foreach($suppliers as $supplier){ ?>
													<option value="<?= $supplier->id ?>" <?= $supplier->id== $shipping->supplier_id?"selected":""; ?> ><?= ucfirst($supplier->company) ?></option>
												<?php } ?>
											</select>
										</td>
										<td>
											<input type="text" value="<?= $shipping->reference_no; ?>" name="reference_no[]" class="form-control reference_no" />
										</td>
										<td>
											<input type="hidden" value="<?= $this->cus->formatDecimal($shipping->paid); ?>" class="form-control text-right paid" />
											<input type="text" value="<?= $this->cus->formatDecimal($shipping->f_cost); ?>" name="f_cost[]" class="form-control text-right f_cost" />
										</td>
										<td>
											<select class="form-control tax" name="tax[]"> 
												<?php foreach($tax_rates as $tax_rate){ ?>
													<option tax_rate="<?= $tax_rate->rate ?>" value="<?= $tax_rate->id ?>" <?= $tax_rate->id== $shipping->order_tax_id?"selected":""; ?> ><?= ($tax_rate->name) ?></option>
												<?php } ?>
											</select>
										</td>
										<td>
											<input readonly="true" type="text" value="<?= $this->cus->formatDecimal($shipping->total); ?>" name="amount[]" class="form-control text-right amount" />
										</td>
										<td class="text-center">
											<a href="#" class="btn btn-sm delete_supplier">
												<i class="fa fa-trash"></i>
											</a>
										</td>
									</tr>
								<?php }
							}
							?>
						</tbody>
						
						<tfoot>
							<tr>
								<th colspan="6"></th>
								<th id="total_amount" class="text-right"><?= isset($totalshipping)? $totalshipping: '' ?></th>
								<th></th>
							</tr>
						</tfoot>
						
					</table>
					
					<table class="table table-bordered table-striped table-condensed" style="white-space:nowrap;">
						<thead>
							<tr>
								<th width="3%"><?= lang("no") ?></th>
								<th width="120px"><?= lang("product_code") ?></th>
								<th width="120px"><?= lang("product_name") ?></th>
								<th width="120px"><?= lang("quantity") ?></th>
								<?php if($Settings->cbm==1){ ?>
									<th width="120px"><?= lang("cbm") ?></th>
								<?php } ?>
								<th width="120px"><?= lang("subtotal") ?></th>
								<th width="100px"><?= lang("unit_cost") ?></th>
								<th width="100px"><?= lang("%") ?></th>
							</tr>
						</thead>
						
						<tbody>
						<?php 	
						$total_cbm = 0;
						$grand_total = 0;
						foreach($rows as $i => $row){ 
							if($Settings->cbm==1 && $totalItem->total_cbm > 0){
								$percent = ($row->total_cbm * 100) / $totalItem->total_cbm;
							}else{
								$percent = ($row->subtotal * 100) / $totalItem->subtotal;
							}
							$shiping_item = $this->purchases_model->getPurchaseShippingItem($row->id,$row->product_id,$freight_type);
							if ($shiping_item) {
								$unit_cost = ($shiping_item->unit_cost) ? $shiping_item->unit_cost : 0 ;
								$percent =  ($shiping_item->unit_percent) ? $shiping_item->unit_percent: $percent ;
							}else{
								$unit_cost = 0 ;
							}
							$total_cbm += $row->total_cbm;
							$grand_total += $row->subtotal;
							
						?>
							<tr>
								<td class="text-center"><?= ($i+1) ?></td>
								<td class="text-center"><?= $row->product_code ?></td>
								<td class="text-left"><?= $row->product_name ?></td>
								<td class="text-center"><?= $this->cus->formatQuantity($row->quantity); ?></td>
								<?php if($Settings->cbm==1){ ?>
									<td class="text-center"><?= $this->cus->formatQuantity($row->total_cbm); ?></td>
								<?php } ?>
								<td class="text-right"><?= $this->cus->formatQuantity($row->subtotal); ?></td>
								<td>
									<input type="text" name="unit_cost[]" value="<?= $unit_cost ?>" class="form-control unit_cost text-right" />									
									<input type="hidden" name="quantity[]" value="<?= $row->quantity ?>" class="quantity" />
									<input type="hidden" name="subtotal[]" value="<?= $row->subtotal ?>" class="subtotal" />
									<input type="hidden" name="product_code[]" value="<?= $row->product_code ?>" class="product_code" />
									<input type="hidden" name="purchase_item[]" value="<?= $row->id ?>" class="purchase_item" />
								</td>
								<td>
									<input type="text" name="percent[]" value="<?= $percent  ?>" class="form-control percent text-right" />
								</td>
							</tr>
						<?php } ?>
						</tbody>
						
						<tfoot>
							<tr>
								<td colspan="4">&nbsp;</td>
								<?php if($Settings->cbm==1){ ?>
									<td class="text-center"><?= $this->cus->formatQuantity($total_cbm); ?></td>
								<?php } ?>
								<td class="text-right"><?= $this->cus->formatQuantity($grand_total); ?></td>
								<td colspan="2">&nbsp;</td>
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
		
		//$(".delete_supplier").bind("click",delete_supplier);
		$("#add_supplier").on("click",function(){
			var i = $("#element_data tr").length + 1;
			var html = "";
				html += "<tr>";
					html += "<td class=\"text-center\">"+i+"</td>";
					html += "<td><input type='text' value='<?= $Settings->date_with_time == 0 ? date('d/m/Y') : date('d/m/Y H:i') ?>' name='freight_date[]' class='form-control datetime freight_date' /></td>";
					html += "<td>";																																		
						html += "<select class=\"form-control\" name=\"supplier[]\">";
						<?php foreach($suppliers as $supplier){ ?>
							html += "<option value=\"<?= $supplier->id ?>\"><?= ucfirst($supplier->company) ?></option>";
						<?php } ?>
						html += "</select>";
						html += "</td>";
						html += "<td><input type='text' name='reference_no[]' class='form-control reference_no' /></td>";
						html += "<td><input type=\"text\" name=\"f_cost[]\" class=\"form-control text-right f_cost\" /></td>";
						html += "<td><select class=\"form-control tax\" name=\"tax[]\">";
						<?php foreach($tax_rates as $tax_rate){ ?>
							html += "<option tax_rate=\"<?= $tax_rate->rate ?>\" value=\"<?= $tax_rate->id ?>\"><?= ucfirst($tax_rate->name) ?></option>";
						<?php } ?>
						html += "</select></td>";
						html += "<td><input readonly=\"true\" type=\"text\" name=\"amount[]\" class=\"form-control text-right amount\" /></td>";
					html += "<td class=\"text-center\">";
						html += "<a href=\"#\" class=\"btn btn-sm delete_supplier\">";
							html += "<i class=\"fa fa-trash\"></i>";
						html += "</a>";
					html += "</td>";
			html += "</tr>";
			
			$("#element_data").append(html);
			$(".delete_supplier").bind("click",delete_supplier); 
			$("select").select2();
			$(".amount").bind("keydown", disable_str); 
			$(".amount").bind("keyup", calculate_shipping_amount); 
			return false;
		});
		
		$('.tax, .f_cost').live('change',function(){
			var parent = $(this).parent().parent();
			var tax_rate = $('option:selected',  parent.find('.tax')).attr('tax_rate');
			var f_cost = parent.find('.f_cost').val() - 0;
			var amount = f_cost;
			if(tax_rate > 0){
				amount = f_cost + (f_cost * tax_rate / 100);
			}
			parent.find('.amount').val(amount);
			parent.find('.amount').keyup();
		});

		
		$(".percent").keyup(function(){			
			var amount = 0;
			$(".f_cost").each(function(){
				amount += Number($(this).val());
			});
			var parent = $(this).parent().parent();
			var percent = parent.find(".percent").val() / 100;
			var quantity = parent.find(".quantity").val();
			var unit_cost = ((amount * percent) / quantity);			
			parent.find(".unit_cost").val(unit_cost);
		});
		
		
		$(".delete_supplier").bind("click",delete_supplier);
		$(".amount, .percent, .unit_cost, .f_cost").bind("keydown", disable_str); 
		
		$(".amount").bind("keyup", calculate_shipping_amount); 
			
		function calculate_shipping_amount(){								
			var amount = 0;
			$(".f_cost").each(function(){
				amount += Number($(this).val());
			});
			$("#total_amount").html(amount);
						
			$(".percent").each(function(){
				var parent = $(this).parent().parent();
				var percent = parent.find(".percent").val() / 100;
				var quantity = parent.find(".quantity").val();
				var subtotal = ((amount * percent) / quantity);
				parent.find(".unit_cost").val(subtotal);
			});
			
		}
		
		function delete_supplier(){
			var parent = $(this).parent().parent();
			var paid = parent.find('.paid').val() - 0;
			if(paid != 0){
				bootbox.alert('<?=lang('freight_already_has_payment');?>');
			}else{
				parent.remove();
				calculate_shipping_amount();
			}
			
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
	});
	
</script>
