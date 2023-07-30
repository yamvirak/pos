<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_housekeeping_status'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_configuration/edit_housekeeping_status/".$id, $attrib); ?>
        
		<div class="modal-body">
            <p><?= lang('update_info'); ?></p>
                <div class="form-group">
                    <?= lang("code", "code"); ?>
                    <?php echo form_input('code', $row->code, 'class="form-control" id="code" required="required"'); ?>
                </div>
    			<div class="form-group">
    				<?= lang("name", "name"); ?>
    				<?php echo form_input('name', $row->name, 'class="form-control" id="name" required="required"'); ?>
    			</div>
                <div class="form-group">
                    <?php echo lang('color', 'color'); ?>
                    <div class="controls">
                      <input type="color" value="<?= $row->color ?>" name="color">
                    </div>
                </div>
                <div class="form-group">
                    <?= lang('status', 'status'); ?>
                    <?php
                    	$in_opts['active'] = lang('active');
                        $in_opts['inactive'] = lang('inactive');
                        
                    ?>
                    <?= form_dropdown('status', $in_opts, $service->status, 'class="form-control" id="status" style="width:100%;"'); ?>
                </div>
                <div class="form-group">
                    <?php echo lang('description', 'description'); ?>
                    <div class="controls">
                        <textarea name="description" class="form-control"><?= $row->description ?></textarea>
                    </div>
                </div>
           
           </div>
	
		<div class="modal-footer">
			<?php echo form_submit('edit_housekeeping_status', lang('edit_housekeeping_status'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
