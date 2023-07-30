<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_cash'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("sales/add_cash/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="row">
				
				<?php if ($Owner || $Admin || $GP['sales-date']) { ?>
				<div class="col-sm-6">
					<div class="form-group">
						<?= lang("date", "date"); ?>
						<?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y h:i')), 'class="form-control datetime" id="date" required="required"'); ?>
					</div>
				</div>
				<?php } ?>
				<div class="col-sm-6">
					<div class="form-group">
						<?= lang("reference_no", "reference_no"); ?>
						<?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment_ref), 'class="form-control tip" id="reference_no"'); ?>
					</div>
				</div>
								
				<div class="col-sm-12">
					<?= lang("sales", "sales"); ?>
					
					<table class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th><?= lang("reference_no") ?></th>						
								<th><?= lang("amount") ?></th>
								<th></th>			
								<th><?= lang("status") ?></th>
							</tr>
						</thead>
						
						<tbody>
					<?php				
						foreach($allAssigns as $assign){ ?>				
						<tr>
							<td class="center"><?= $assign->reference_no ?></td>										
							<td class="right"><?= $this->cus->formatMoney($assign->total) ?></td>
							<td class="center">
								<input type="text" value="<?= $this->cus->formatDecimal($assign->grand_total - $assign->paid); ?>" class="form-control text-right" name="amount[]" />
								<input type="hidden" class="form-control" value="<?= $assign->id ?>" name="sale_id[]" />
								<input type="hidden" class="form-control" value="cash" name="paid_by[]" />
							</td>
							<td class="center">
								<span class="label label-warning">
									<?= lang($assign->payment_status) ?>
								</span>
							</td>							
						</tr>				
					<?php 
						}
					?>
						<tbody>
					</table>					
				</div>
				
				<div class="col-sm-12">
					<div class="form-group">
						<?= lang("note", "note"); ?>
						<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
					</div>
				</div>
			</div>			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_cash', lang('add_cash'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>
