<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('set_serials'); ?></h2>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
						<li>
                            <a href="<?php echo site_url('products/set_serial_by_excel/'.$product->id.'/'.$warehouse_product->warehouse_id); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-upload"></i> <?= lang('set_serial_by_excel') ?>
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
                <div class="well well-sm no-print">
                    <div class="controls table-controls">
						<div class="col-md-4">
							<div class="form-group">
							<?= lang("status", "status"); ?>
							<?php 
								$status[''] = lang('select').' '.lang('status');
								$status['0'] = lang('active');
								$status['1'] = lang('inactive');
							?>
							<?= form_dropdown('status', $status, (isset($_POST['status']) ? $_POST['status'] : ''), ' id="status" class="form-control"'); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("serial_no", "serial_no"); ?>
								<?php echo form_input('sserial_no', (isset($_POST['sserial_no']) ? $_POST['sserial_no'] : ''), 'class="form-control input-tip" id="sserial_no"'); ?>
							</div>
						</div>
						<?php
							$tbody = '';
							$i = 1;
							$total_active = 0;
							if($product_serials){
								foreach($product_serials as $product_serial){
									if($product_serial->inactive==1){
										$tbody .= '<tr class="inactive index" row="'.$i.'" id="row_'.$i.'">	
													<td style="line-height:33.64px" id="index_'.$i.'" class="text-center new_index">'.$i.'</td>
													<td><input type="hidden" value="'.$product_serial->serial.'" class="serial_no"/>'.$product_serial->supplier.'</td>
													<td class="text-center">'.$this->cus->hrsd($product_serial->date).'</td>
													<td>'.$product_serial->serial.'</td>
													<td class="text-right">'.$this->cus->formatMoney($product_serial->cost).'</td>
													<td class="text-right">'.$this->cus->formatMoney($product_serial->price).'</td>
													<td>'.$product_serial->color.'</td>
													<td>'.$product_serial->description.'</td>
												</tr>';
									}else{
										$total_active++;
										$disable_serial = '';
										$disable_cost = '';
										if($product_serial->purchase_id > 0 || $product_serial->receive_id > 0 || $product_serial->transfer_id > 0 || $product_serial->pawn_id > 0 || $product_serial->adjustment_id > 0){
											$disable_serial = 'readonly';
											if($product_serial->adjustment_id > 0){
												$disable_cost = '';
											}else{
												$disable_cost = 'readonly';
											}
										}
										$tbody .= '<tr class="t_active index" row="'.$i.'" id="row_'.$i.'">	
													<td id="index_'.$i.'" class="text-center new_index">'.$i.'</td>
													<td>
														<input type="hidden" value="'.$product_serial->purchase_id.'" name="purchase_id[]"/>
														<input type="hidden" value="'.$product_serial->receive_id.'" name="receive_id[]"/>
														<input type="hidden" value="'.$product_serial->transfer_id.'" name="transfer_id[]"/>
														<input type="hidden" value="'.$product_serial->pawn_id.'" name="pawn_id[]"/>
														<input type="hidden" value="'.$product_serial->adjustment_id.'" name="adjustment_id[]"/>
														<input type="hidden" value="'.$product_serial->supplier_id.'" class="form-control supplier_id" name="supplier_id[]" id="supplier_id_'.$i.'"/>
														<input type="text" class="form-control supplier" value="'.$product_serial->supplier.'"  name="supplier[]" data-id="'.$i.'"  id="supplier_'.$i.'"/>
													</td>
													<td><input value="'.$this->cus->hrsd($product_serial->date).'" class="form-control text-center input-tip date serial_date" type="text" name="serial_date[]"/></td>
													<td><input '.$disable_serial.' type="text" value="'.$product_serial->serial.'" name="serial_no[]" class="form-control text-left serial_no"/></td>
													<td><input '.$disable_cost.' type="text" value="'.$this->cus->formatDecimal($product_serial->cost).'" name="cost[]" class="form-control text-right cost"/></td>
													<td><input type="text" value="'.$this->cus->formatDecimal($product_serial->price).'" name="price[]" class="form-control text-right price"/></td>
													<td><input type="text" value="'.$product_serial->color.'" name="color[]" class="form-control text-right color"/></td>
													<td><input type="text" value="'.$product_serial->description.'" name="description[]" class="form-control text-right description"/></td>
													<td class="text-center"><i class="fa fa-times tip del_serial" id="'.$i.'" title="'.lang('remove').'" style="cursor:pointer;"></i></td>
												</tr>';
									}
									$i++;			
								}
							}
						?>
						
                        <table id="srTable"
                               class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>
								<tr>
									<th><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
									<th><?= lang("quantity"); ?></th>
									<th><?= lang("serial_quantity"); ?></th>
								</tr>
                            </thead>
							<tbody>
								<tr>
									<td><?= $product->name.' ( '.$product->code.' )' ?></td>
									<td class="text-right"><?= $this->cus->convertQty($product->id,$warehouse_product->quantity) ?></td>
									<td><input id="total_qty" type="hidden" value="<?= $warehouse_product->quantity ?>" /><input type="hidden" id="active_qty" value="<?= $total_active ?>" /> <input  type="text" value="<?= $total_active ?>" class="form-control input-tip text-right serial_quantity"/></td>
								</tr>
							</tbody>
                        </table>
                    </div>
					<?php echo form_open_multipart("products/set_serials") ?>
						<input type="hidden" name= "product_id" id="product_id" value="<?= $product->id ?>" />
						<input type="hidden" name= "warehouse_id" id="warehouse_id" value="<?= $warehouse_product->warehouse_id ?>" />
						<div class="controls table-controls">
							<table id="serialTable" class="table items table-striped table-bordered table-condensed table-hover">
								<thead>
									<tr>
										<th><?= lang("#")?></th>
										<th class="col-xs-2"><?= lang("supplier")?></th>
										<th class="col-xs-1"><?= lang("date")?></th>
										<th class="col-xs-4"><?= lang("serial_number")  ?></th>
										<th class="col-xs-1"><?= lang("cost")  ?></th>
										<th class="col-xs-1"><?= lang("price")  ?></th>
										<th class="col-xs-1"><?= lang("color")  ?></th>
										<th class="col-xs-1"><?= lang("description")  ?></th>
										<th class="text-center" style="width:30px;">
											<i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
										</th>
									</tr>
								</thead>
								<tbody id="t_product_serial">
									<?= $tbody ?>
								</tbody>
							</table>
						</div>
						<div class="form-group">
							<?php echo form_submit('set_serials', lang("submit"), 'class="btn btn-primary"'); ?>
						</div>
                    <?= form_close(); ?>
                    <div class="clearfix"></div>
                </div>
			</div>	
		</div>	
	</div>
</div>	
<script type="text/javascript">
    $(document).ready(function() {
	
		$(document).on("focus", '.serial_no', function () {
		}).bind("keypress", '.serial_no', function (e) {
			if(e.keyCode == 13){
				e.preventDefault();
				return false;
			}
		});
		
		$(document).on('click', '.del_serial', function () {
            var id = $(this).attr('id');
			$(this).closest('#row_' + id).remove();
        });
		$(document).on('change', '#status', function () {
            var status = $(this).val();
			if(status=='0'){
				$('.inactive').css('display','none');
				$('.t_active').show();
			}else if(status=='1'){
				$('.inactive').show();
				$('.t_active').css('display','none');
			}else{
				$('.inactive').show();
				$('.t_active').show();
			}
			reorderIndex();
        });
		
		$(document).on('change', '#sserial_no', function () {
            var sserial_no = $(this).val();
			$('.serial_no').each(function(){
				var serial = $(this).val();
				if(serial == sserial_no || sserial_no == ''){
					$(this).closest('tr').show();
				}else{
					$(this).closest('tr').css('display','none');
				}
			})
			reorderIndex();
        });

		$(document).on("focus", '.serial_quantity', function () {
			old_value = $(this).val();
		}).on("change", '.serial_quantity', function () {
			var total_qty = $('#total_qty').val();
			var active_qty = $('#active_qty').val();
			var new_value = $(this).val() - 0;
			if (!is_numeric($(this).val()) || new_value < 0 || total_qty < new_value || active_qty > new_value) {
				$(this).val(old_value);
				return;
			}
			var n_number = Math.floor(Math.random() * (999999999 - 100000000 + 1) + 100000000); 
			var total_active = 0;
			var html = '';
			
			$('.t_active').each(function(){
				total_active ++;
			});
			
			if(total_active > new_value){
				for(var i = 0; i < (total_active - new_value); i++){
					$(".add_row:last").remove();
				}
			}else{
				var date = '<?= date("d/m/Y") ?>';
				for(var i = 0; i < (new_value - total_active); i++){
					var row_num = (n_number+i);
					html +='<tr class="t_active index add_row" row="'+row_num+'" id="row_'+row_num+'">';
						html +='<td id="index_'+row_num+'" class="text-center new_index">'+row_num+'</td>';
						html +='<td><input type="hidden" class="form-control supplier_id" name="supplier_id[]" id="supplier_id_'+row_num+'"/><input type="text" class="form-control supplier" name="supplier[]" data-id="'+row_num+'"  id="supplier_'+row_num+'"/></td>';
						html +='<td><input class="form-control text-center input-tip date serial_date" value="'+date+'" type="text" name="serial_date[]"/></td>';
						html +='<td><input type="text" name="serial_no[]" class="form-control text-left serial_no"/></td>';
						html +='<td><input type="text" name="cost[]" value="<?= $this->cus->formatDecimal($product->cost) ?>"class="form-control text-right cost"/></td>';
						html +='<td><input type="text" name="price[]" value="<?= $this->cus->formatDecimal($product->price) ?>" class="form-control text-right price"/></td>';
						html +='<td><input type="text" name="color[]" class="form-control text-right color"/></td>';
						html +='<td><input type="text" name="description[]" class="form-control text-right description"/></td>';
						html +='<td class="text-center"><i class="fa fa-times tip del_serial" id="'+row_num+'" title="<?=lang('remove')?>" style="cursor:pointer;"></i></td>';
					html +='</tr>';
				}
				$('#t_product_serial').append(html);
			}
			
			reorderIndex();
		});
		
		function reorderIndex(){
			var new_index = 0;
			$('.index').each(function(){
				var style = $(this).css('display');
				if(style=='table-row'){
					new_index++;
					var id = $(this).attr('row');
					$('#index_' + id).html(new_index);
				}
			});
	
		}
		
		$(".supplier:not(.ui-autocomplete-input)").live("focus", function (event) {
			$(this).autocomplete({
				source: '<?= site_url('products/supplier_suggestions'); ?>',
				minLength: 1,
				autoFocus: false,
				delay: 250,
				response: function (event, ui) {
					if (ui.content.length == 1 && ui.content[0].id != 0) {
						ui.item = ui.content[0];
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
						$(this).autocomplete('close');
						$(this).removeClass('ui-autocomplete-loading');
					}
				},
				select: function (event, ui) {
					event.preventDefault();
					if (ui.item.item_id !== 0) {
						var parent = $(this).parent().parent();
						parent.find(".supplier_id").val(ui.item.item_id);
						$(this).val(ui.item.label);
					} else {
						bootbox.alert('<?= lang('no_match_found') ?>');
					}
				}
			});
		});
    });
</script>