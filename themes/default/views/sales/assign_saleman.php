<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
	<div class="modal-dialog"​>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
				</button>
				<h4 class="modal-title" id="myModalLabel"><?php echo lang('assign_saleman'); ?></h4>
			</div>
			<?php $attrib = array('data-toggle' => '', 'role' => 'form');
			echo form_open_multipart("sales/assign_saleman/".$id, $attrib); ?>			
			<div class="modal-body">
				<p><?= lang('enter_info'); ?></p>				
				<div class="row">				   
				   <div class="col-sm-12">						
						<div class="form-group">
							<?php
								foreach($sales as $sale){
									echo "<input type='hidden' value='".$sale."' name='sale_id[]' />";
								}
							?>
							
							<?= lang("saleman", "saleman"); ?>
							<?php 
								$users = array(lang('select').' '.lang('saleman'));
								foreach($allUsers as $user){
									$users[$user->id] = $user->first_name .' '.$user->last_name;
								}
							?>
							<?= form_dropdown('saleman_id', $users, 0, 'class="form-control" required="required"'); ?>
						</div>						
				   </div>				   
				</div>
			</div>			
			<div class="modal-footer">
				<?php echo form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
			</div>			
		</div>		
		<?php echo form_close(); ?>		
	</div>	
</div>
<?= $modal_js ?>
<script type="text/javascript">
	$(function(){
		$('#assignModal').appendTo("body").modal('show');
	})
</script>