<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
        </div>
		
        <?php 
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("pos/add_customer", $attrib); ?>
        
		<div class="modal-body">
            
			<p><?= lang('enter_info'); ?></p>
            
            <div class="row">
			
				<div class="col-sm-12">
				 
					<div class="form-group">
						<label class="control-label" for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
						<?php
						foreach ($customer_groups as $customer_group) {
							$cgs[$customer_group->id] = $customer_group->name;
						}
						echo form_dropdown('customer_group', $cgs, $Settings->customer_group, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
						?>
					</div>
					
					<div class="form-group person">
						<?= lang("name", "name"); ?>
						<?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true" autocomplete="off" '); ?>
					</div>
					
					<div class="form-group">
						<?= lang("phone", "phone"); ?>
						<input type="tel" name="phone" class="form-control" required="required" id="phone" autocomplete="off" />
					</div>
				
				</div>
				
            </div>

        </div>
		
        <div class="modal-footer">
            <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
	$(function(){
		$("select").select2();
	});
</script>

