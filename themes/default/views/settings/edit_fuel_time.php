<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_fuel_time'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_fuel_time/".$id, $attrib); ?>
		<div class="modal-body">
            <p><?= lang('update_info'); ?></p>
			<div class="form-group">
				<?= lang("open_time", "open_time"); ?>
				<?php echo form_input('open_time', $fuel->open_time, 'class="form-control timepicker" autocomplete="off" id="open_time" required="required"'); ?>
			</div>
			<div class="form-group">
				<?= lang("close_time", "close_time"); ?>
				<?php echo form_input('close_time', $fuel->close_time, 'class="form-control timepicker" autocomplete="off" id="close_time" required="required"'); ?>
			</div>
		</div>
		<div class="modal-footer">
			<?php echo form_submit('edit_fuel_time', lang('edit_fuel_time'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
	$(function(){
		$('.timepicker').datetimepicker({
			format: 'hh:ii:00',
			startView: 0,
			autoclose: 1,
		});	
	});
</script>
