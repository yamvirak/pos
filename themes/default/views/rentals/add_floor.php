<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_floor'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_configuration/add_floor", $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
				<?= lang("name", "name"); ?>
				<?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
			</div>
		</div>
	
		<div class="modal-footer">
			<?php echo form_submit('add_floor', lang('add_floor'), 'class="btn btn-primary"'); ?>
		</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
