<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_saleman'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("auth/add_saleman", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>


            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("first_name", "first_name"); ?>
                        <?php echo form_input('first_name', '', 'class="form-control tip" id="first_name" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                     <div class="form-group">
                        <?= lang("last_name", "last_name"); ?>
                        <?php echo form_input('last_name', '', 'class="form-control tip" id="last_name" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">  
					<div class="form-group">
						<?= lang('gender', 'gender'); ?>
						<?php
						$ge[''] = array('Male' => lang('male'), 'Female' => lang('female'));
						echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : ''), 'class="form-control select" id="gender" data-placeholder="' . lang("select") . ' ' . lang("gender") . '" required="required"');
						?>
					</div>
                </div>
                <div class="col-md-6">
                     <div class="form-group">
                        <?= lang("phone", "phone"); ?> 
                        <input type="text" name="phone" class="form-control"  id="phone"/>
                    </div>
                </div>
                <div class="col-md-6">	
                    <div class="form-group">
                        <?= lang("position", "position"); ?> 
						<?php echo form_input('phone', '', 'class="form-control tip" id="position"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("saleman_group", "saleman_group"); ?>
                            <?php 
                                $pr = array(lang("select")." ".lang("saleman_group"));
                                foreach($salemangroups as $salemangroup){
                                    $pr[$salemangroup->id] = $salemangroup->name;
                                } 
                            ?>
                            <?= form_dropdown('saleman_group', $pr ,'', 'class="form-control select" id="saleman_group" required="required"'); ?>
                        </div>
                    </div>
                <div class="col-md-6">
                    <?php if($this->config->item('saleman_commission')){ ?>
                        <div class="form-group">
                            <?= lang("commission", "commission"); ?>
                            <input type="text" name="commission" class="form-control" id="commission"/>
                        </div>
                    <?php } ?>
                    
                </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('status', 'status'); ?>
                        <?php
                        $opt = array(1 => lang('active'), 0 => lang('inactive'));
                        echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" required="required" class="form-control select" style="width:100%;"');
                        ?>
                    </div>

                </div>


				
            </div>

        </div>
		
        <div class="modal-footer">
            <?php echo form_submit('add_saleman', lang('add_saleman'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function (e) {
        $('#add-customer-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }, excluded: [':disabled']
        }); 
        
        $('select.select').select2({minimumResultsForSearch: 7});
        fields = $('.modal-content').find('.form-control');
        $.each(fields, function () {
            var id = $(this).attr('id');
            var iname = $(this).attr('name');
            var iid = '#' + id;
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                $("label[for='" + id + "']").append(' *');
                $(document).on('change', iid, function () {
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
                });
            }
        });



        
    });
</script>

