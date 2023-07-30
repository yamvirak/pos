<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_saleman_target'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_saleman_target/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
				<?= lang('salesman_group', 'group'); ?>
				<?php
				$opt_group[''] = lang("select") . ' ' . lang("group");
				if($groups){
					foreach($groups as $group){
						$opt_group[$group->id] = $group->name;
					}
				}
				echo form_dropdown('group', $opt_group, $saleman_target->group_id, 'class="tip form-control" id="group"'); ?>
			</div>
            <div class="form-group">
                <label class="control-label" for="description"><?php echo $this->lang->line("description"); ?></label>

                <div
                    class="controls"> <?php echo form_input('description', $saleman_target->description, 'class="form-control" id="description" required="required"'); ?> </div>
            </div>
			
			<div class="form-group">
                <label class="control-label" for="min_amount"><?php echo $this->lang->line("min_amount"); ?></label>

                <div
                    class="controls"> <?php echo form_input('min_amount', $saleman_target->min_amount, 'class="form-control" id="min_amount" required="required"'); ?> </div>
            </div>
			
			<div class="form-group">
                <label class="control-label" for="max_amount"><?php echo $this->lang->line("max_amount"); ?></label>

                <div
                    class="controls"> <?php echo form_input('max_amount', $saleman_target->max_amount, 'class="form-control" id="max_amount" required="required"'); ?> </div>
            </div>
			
			<div class="form-group">
                <label class="control-label" for="commission"><?php echo $this->lang->line("commission"); ?></label>

                <div
                    class="controls"> <?php echo form_input('commission', $saleman_target->commission, 'class="form-control" id="commission" required="required"'); ?> </div>
            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_saleman_target', lang('edit_saleman_target'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>