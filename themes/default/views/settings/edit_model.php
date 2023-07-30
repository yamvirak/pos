<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_model'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_model/".$id, $attrib); ?>
        
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
				<?= lang("brand", "brand"); ?>
				<?php
					$opt_brands[''] = lang("select")." ".lang("brand");
					foreach ($brands as $brand) {
						$opt_brands[$brand->id] = $brand->name;
					}
					echo form_dropdown('brand', $opt_brands, $row->brand_id, 'id="brand" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("brand") . '"  style="width:100%;" required="required"');
				?>
			</div>
		</div>
	
		<div class="modal-footer">
			<?php echo form_submit('edit_model', lang('edit_model'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
