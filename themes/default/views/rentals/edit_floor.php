<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_floor'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_configuration/edit_floor/".$id, $attrib); ?>
        
		<div class="modal-body">
            <p><?= lang('update_info'); ?></p>
			<div class="form-group">
				<?= lang("name", "name"); ?>
				<?php echo form_input('name', $row->floor, 'class="form-control" id="name" required="required"'); ?>
			</div>
		</div>
	
		<div class="modal-footer">
			<?php echo form_submit('edit_floor', lang('edit_floor'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
