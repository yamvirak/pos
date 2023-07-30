<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('send_sms'); ?></h4>
        </div>
        <?php 
		$attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
        echo form_open_multipart("customers/send_sms", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="row">
                <div class="well">
					<div class="col-md-12">
						<?php 
							if($ids[0] != NULL){
								foreach($ids as $id){ 
									$customer = $this->site->getCompanyByID($id);
									echo " [".$customer->name . "] ";
									echo "<input type='hidden' name='customer_id[]' value='".$id."' />";
								}
							}else{
								echo lang("no_customer_selected");
							}
						?>
					</div>
					<div class="clearfix"></div>
				</div>
            </div>
			
            <div class="row">
                <div class="col-md-12">	
                    <div class="form-group">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', '', 'class="form-control" autocomplete="off" id="name"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("message", "message"); ?>
                        <?php echo form_textarea('message', '', 'class="form-control" id="message"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
			<?php 
				if($ids[0] != NULL){ 
					echo form_submit('send', lang('send'), 'class="btn btn-primary"');
				}else{
					echo '<a class="btn btn-danger" disabled>'.lang("unable_to_send").'</a>';
				} 
			?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js; ?>

