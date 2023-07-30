<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_license'); ?></h4>
        </div>
		<?php  
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo form_open_multipart("products/edit_license/".$id, $attrib); ?>
		<div class="modal-body">
			<p><?= lang('enter_info'); ?></p>
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group">
						<?= lang("issued_date", "l_issued_date"); ?>
						<?= form_input('l_issued_date', $this->cus->hrsd($row->issued_date), 'class="form-control date" id="l_issued_date" required="required"'); ?>
					</div>
					<div class="form-group">
						<?= lang("valid_date", "l_valid_date"); ?>
						<?= form_input('l_valid_date', $this->cus->hrsd($row->valid_date), 'class="form-control date" id="l_valid_date" required="required"'); ?>
					</div>
					<div class="form-group">
						<?php echo lang('document', 'l_document'); ?>
						<div class="controls">
							<input type="file" id="l_document" name="l_document" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('description', 'l_description'); ?>
						<div class="controls">
							<textarea id="l_description" name="l_description" class="form-control"><?= $row->description ?></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="modal-footer">
			<?php echo form_submit('edit_license', lang('edit_license'), 'class="btn btn-primary"'); ?>
		</div>

    	<?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>