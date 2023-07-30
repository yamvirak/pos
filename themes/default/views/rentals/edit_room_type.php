<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_room_type'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_configuration/edit_room_type/".$id, $attrib); ?>
        
		<div class="modal-body">
            <p><?= lang('update_info'); ?></p>
			<div class="form-group">
				<?= lang("name", "name"); ?>
				<?php echo form_input('name', $row->name, 'class="form-control" id="name" required="required"'); ?>
			</div>
		</div>
	
		<div class="modal-footer">
			<?php echo form_submit('edit_room_type', lang('edit_room_type'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
