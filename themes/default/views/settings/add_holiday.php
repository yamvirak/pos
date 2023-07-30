<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_vehicle'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/add_holiday", $attrib); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-12">
					<div class="form-group">
                        <?= lang("from_date", "from_date"); ?>
                        <?php echo form_input('from_date', '', 'class="form-control date" id="from_date"'); ?>
                    </div>
				</div>	

				<div class="col-md-12">
					<div class="form-group">
                        <?= lang("to_date", "to_date"); ?>
                        <?php echo form_input('to_date', '', 'class="form-control date" id="to_date"'); ?>
                    </div>
				</div>
				
				<div class="col-md-12">
					<div class="form-group">
                        <?= lang("description", "description"); ?>
                        <?php echo form_textarea('description', '', 'class="form-control" id="description"'); ?>
                    </div>
				</div>
				
			</div>
		</div>
	<div class="modal-footer">
		<?php echo form_submit('add_holiday', lang('add_holiday'), 'class="btn btn-primary"'); ?>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
