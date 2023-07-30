<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_driver'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_driver", $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
				<?= lang("code", "code"); ?>
				<?php echo form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
			</div>
			<div class="form-group">
				<?= lang("name", "name"); ?>
				<?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
			</div>
			<div class="form-group">
				<?= lang("phone", "phone"); ?>
				<?php echo form_input('phone', '', 'class="form-control" id="phone"'); ?>
			</div>
			<div class="form-group">
				<?= lang("address", "address"); ?>
				<?php echo form_textarea('address', '', 'class="form-control" id="address"'); ?>
			</div>
		</div>
		<div class="modal-footer">
			<?php echo form_submit('add_driver', lang('add_driver'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
