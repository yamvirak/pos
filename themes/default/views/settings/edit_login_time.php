<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_login_time'); ?></h4>
        </div>
		
        <?php 
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo form_open_multipart("system_settings/edit_login_time/".$id, $attrib); ?>
		
		<div class="modal-body">
            <p><?= lang('update_info'); ?></p>
			
			<div class="form-group">
				<?= lang("group", "group"); ?>
				<?php
				$bl[""] = lang('select').' '.lang('group');
				foreach ($groups as $group) {
					$bl[$group->id] = ucfirst($group->name);
				}
				echo form_dropdown('group', $bl, (isset($_POST['group']) ? $_POST['group'] : $login->group_id), 'id="group" class="form-control select" style="width:100%;"');
				?>
			</div>
			
			<div class="form-group">
				<?= lang("day", "day"); ?>
				<?php
				$days = array(
							"Mon" => lang("monday"),
							"Tue" => lang("tuesday"),
							"Wed" => lang("wednesday"),
							"Thu" => lang("thursday"),
							"Fri" => lang("friday"),
							"Sat" => lang("saturday"),
							"Sun" => lang("sunday"),
					);
				echo form_dropdown('day', $days, (isset($_POST['day']) ? $_POST['day'] : $login->day), ' id="day" class="form-control select" style="width:100%;"');
				?>
			</div>
			
			<div class="form-group">
				<?= lang("time_in", "time_in"); ?>
				<?php echo form_input('time_in', date("H:i", strtotime($login->time_in)), 'class="form-control timepicker"'); ?>
			</div>
			
			<div class="form-group">
				<?= lang("time_out", "time_out"); ?>
				<?php echo form_input('time_out', date("H:i", strtotime($login->time_out)), 'class="form-control timepicker"'); ?>
			</div>
			
			<div class="form-group">
				<?= lang("description", "description"); ?>
				<?php echo form_textarea('description', $login->description, 'class="form-control" id="description"'); ?>
			</div>
		</div>	
		
		<div class="modal-footer">
			<?php echo form_submit('edit_login_time', lang('edit_login_time'), 'class="btn btn-primary" '); ?>
		</div>
		
	</div>	
		<?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
	$(function(){
		$('.timepicker').datetimepicker({
			format: 'hh:ii',
			startView: 0
		});	
	});
</script>
