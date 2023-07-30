<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="modal-dialog">
    
	<div class="modal-content">
        
		<div class="modal-header">
            
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
			
            <h4 class="modal-title" id="myModalLabel">
				<?= lang("split_suspend") ?>
			</h4>
			
        </div>
		
        <?php
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo form_open_multipart("pos/add_table", $attrib);
        ?>
		<input type="hidden" name="delete_id" value="<?php echo $suspend->id; ?>" />
		<input type="hidden" name="bill_id" value="<?php echo $bill_id ?>" />
		<input type="hidden" name="table_id" value="<?php echo $split_to->id ?>" />
		<input type="hidden" name="table_name" value="<?php echo $split_to->name ?>" />
		<input type="hidden" name="warehouse_id" value="<?php echo $suspend->warehouse_id ?>" />
		<input type="hidden" name="customer_id" value="<?php echo $suspend->customer_id ?>" />
		<div class="modal-body">
            <div id="alerts"></div>
			<div class="row">
				<div class="col-sm-12">
					<div class="table-responsive">
						<table width="100%" class="table table-bordered table-condensed table-hover table-striped dataTable">
							<thead>
								<tr>
									<th style="width:3% !important;">
										<input type="checkbox" checked class="checkbox checkth input-xs" />
									</th>
									<th style="width:200px;"><?= lang("name") ?></th>
									<th style="width:80px;"><?= lang("quantity") ?></th>
									<th style="width:80px;"><?= lang("unit_price") ?></th>
								</tr>
							</thead>
							<tbody>
							<?php 
								if($suspend_items){
									foreach($suspend_items as $suspend_item){
										echo '<tr>
												<td class="text-center"><input type="checkbox" name="val[]" value="'.$suspend_item->id.'" checked class="checkbox multi-select input-xs" /></td>
												<td>'.$suspend_item->product_code.' - '.$suspend_item->product_name.'</td>
												<td class="text-center">'.$this->cus->formatQuantity($suspend_item->quantity).'</td>
												<td class="text-right">'.$this->cus->formatMoney($suspend_item->unit_price).'</td>
											</tr>';
									}
								}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
        </div>
        <div class="modal-footer no-print">
            <?= form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
	<?= form_close(); ?>
</div>

<script type="text/javascript">
	$(function(){
		$('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%'
		});
		$(document).on('ifChecked', '.checkth, .checkft', function(event) {
			$('.checkth, .checkft').iCheck('check');
			$('.multi-select').each(function() {
				$(this).iCheck('check');
			});
		});
		$(document).on('ifUnchecked', '.checkth, .checkft', function(event) {
			$('.checkth, .checkft').iCheck('uncheck');
			$('.multi-select').each(function() {
				$(this).iCheck('uncheck');
			});
		});
		$(document).on('ifUnchecked', '.multi-select', function(event) {
			$('.checkth, .checkft').attr('checked', false);
			$('.checkth, .checkft').iCheck('update');
		});
	});
</script>


