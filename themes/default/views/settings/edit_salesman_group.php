<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_salesman_group'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_salesman_group/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

			<div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line("name"); ?></label>
                <?php echo form_input('name', $salesman_group->name, 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="description"><?php echo $this->lang->line("description"); ?></label>
                <?php echo form_input('description', $salesman_group->description, 'class="form-control" id="description"'); ?>
            </div>

           
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_salesman_group', lang('edit_salesman_group'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>