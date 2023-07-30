<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_product_promotion'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_product_promotion", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label for="name"><?php echo $this->lang->line("product_promotion_name"); ?></label>
                <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("from_date", "from_date"); ?>
                <?php echo form_input('from_date', '', 'class="form-control date" id="from_date" required="required"'); ?>
            </div>
       
            <div class="form-group">
                <?= lang("to_date", "to_date"); ?>
                <?php echo form_input('to_date', '', 'class="form-control date" id="to_date" required="required"'); ?>
            </div>
     
            <div class="form-group">
                <?= lang("description", "description"); ?>
                <?php echo form_textarea('description', '', 'class="form-control" id="description"'); ?>
            </div>

            <div class="form-group">
                <?php
                $sta = array();
                $sta[1] = "Active";
                $sta[2] = "Inactive";
                echo form_dropdown('status', $sta, (isset($_POST['status']) ? $_POST['status'] : ""), 'class="form-control" id="status"');
                ?>
            </div>
          
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_product_promotion', lang('add_product_promotion'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>

<script type="text/javascript">
	 $(document).ready(function() {
        $('#multiselect').multiselect({
            buttonContainer: '<div id="multiselect-container"></div>',
			buttonWidth: '100%',
            onChange: function(options, selected) {
                // Get checkbox corresponding to option:
                var value = $(options).val();
                var $input = $('#multiselect-container input[value="' + value + '"]');
                // Adapt label class:
                if (selected) {
                    $input.closest('label').addClass('active');
                }
                else {
                    $input.closest('label').removeClass('active');
                }
            }
        });
    });
</script>