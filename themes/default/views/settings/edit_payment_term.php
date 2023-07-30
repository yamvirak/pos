<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_payment_term'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_payment_term/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="form-group">
				<?php 
					$term_type = array(
								"day" => lang("day"), 
								"end_month" => lang("end_month")
							);
				?>
                <label for="term_type"><?php echo $this->lang->line("term_type"); ?></label>
                <div class="controls"><?php echo form_dropdown('term_type',$term_type, $payment_term->term_type, 'class="form-control" id="term_type" required="required"'); ?></div>
            </div>
			
            <div class="form-group">
                <label class="control-label" for="description"><?php echo $this->lang->line("description"); ?></label>
                <div class="controls"> <?php echo form_input('description', $payment_term->description, 'class="form-control" id="description" required="required"'); ?> </div>
            </div>
            <div class="form-group day">
                <label class="control-label" for="due_day"><?php echo $this->lang->line("due_day"); ?></label>
                <div class="controls"> <?php echo form_input('due_day', $payment_term->due_day, 'class="form-control" id="due_day" required="required"'); ?> </div>
            </div>
			
			<div class="form-group day">
                <label for="due_day_discount"><?php echo $this->lang->line("due_day_discount"); ?></label>
                <div class="controls"><?php echo form_input('due_day_discount', $payment_term->due_day_discount, 'class="form-control" id="due_day_discount" required="required"'); ?></div>
            </div>
			<?php 
				$discount_type = array(
									"Percentage" => lang("Percentage"), 
									"Amount" => lang("amount")
								);
			?>
			<div class="form-group day">
                <label for="discount"><?php echo $this->lang->line("discount_type"); ?></label>
                <div class="controls"><?php echo form_dropdown('discount_type',$discount_type, $payment_term->discount_type, 'class="form-control" id="discount_type" required="required"'); ?></div>
            </div>
			
			<div class="form-group day">
                <label for="discount"><?php echo $this->lang->line("discount"); ?></label>
                <div class="controls"><?php echo form_input('discount', $payment_term->discount, 'class="form-control" id="discount" required="required"'); ?></div>
            </div>
			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_payment_term', lang('edit_payment_term'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php if($payment_term->term_type=="end_month"){ ?>
	<style>	
		.day{display:none}
	</style>
<?php } ?>

<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function () {
		$('#term_type').live('change', function() {
			var term_type = $(this).val();
			if(term_type=="end_month"){
				$('.day').slideUp();
			}else{
				$('.day').slideDown();
			}
		});
    });
</script>