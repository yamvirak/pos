<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_truck'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("customers/edit_truck/".$truck->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', $truck->name, 'class="form-control tip" id="name" required="required"'); ?>
            </div>
			<div class="form-group">
                <?= lang('plate_number', 'plate_number'); ?>
                <?= form_input('plate_number', $truck->plate_number, 'class="form-control tip" id="plate_number" '); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_truck', lang('edit_truck'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
