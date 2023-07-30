<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_project'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/add_project", $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
				<?= lang("biller", "biller"); ?>
				<?php
				$bl[""] = lang('select').' '.lang('biller');
				foreach ($billers as $biller) {
					$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
				}
				echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'id="biller" class="form-control select" style="width:100%;"');
				?>
			</div>
			<div class="form-group">
				<?= lang("name", "name"); ?>
				<?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
			</div>
			<div class="form-group">
				<?= lang("start_date", "start_date"); ?>
				<?php echo form_input('start_date', '', 'class="form-control date" id="start_date"'); ?>
			</div>
			<div class="form-group">
				<?= lang("end_date", "end_date"); ?>
				<?php echo form_input('end_date', '', 'class="form-control date" id="end_date"'); ?>
			</div>
			<div class="form-group">
				<label class="control-label" for="address"><?php echo $this->lang->line("address"); ?></label>
				<?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
			</div>	
			<div class="form-group">
				<?= lang("description", "description"); ?>
				<?php echo form_textarea('description', '', 'class="form-control" id="description"'); ?>
			</div>
		</div>
		<div class="modal-footer">
			<?php echo form_submit('add_project', lang('add_project'), 'class="btn btn-primary"'); ?>
		</div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
