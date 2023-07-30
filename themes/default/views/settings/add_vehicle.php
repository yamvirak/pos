<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_vehicle'); ?></h4>
        </div>
        <?php 
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/add_vehicle", $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
				<?= lang("plate_no", "plate_no"); ?>
				<?php echo form_input('plate_no', '', 'class="form-control" id="plate_no" required="required"'); ?>
			</div>
			<div class="form-group">
				<?= lang("type", "type"); ?>
				<?php echo form_input('type', '', 'class="form-control" id="type"'); ?>
			</div>
			<div class="form-group">
				<?= lang("description", "description"); ?>
				<?php echo form_textarea('description', '', 'class="form-control" id="description"'); ?>
			</div>
		</div>
		<div class="modal-footer">
			<?php echo form_submit('add_vehicle', lang('add_vehicle'), 'class="btn btn-primary"'); ?>
		</div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
