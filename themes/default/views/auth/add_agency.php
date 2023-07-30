<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_agency'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("auth/add_agency", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang("first_name", "first_name"); ?>
                        <?php echo form_input('first_name', '', 'class="form-control tip" id="first_name" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("last_name", "last_name"); ?>
                        <?php echo form_input('last_name', '', 'class="form-control tip" id="last_name" required="required"'); ?>
                    </div>
					<div class="form-group">
						<?= lang('gender', 'gender'); ?>
						<?php
						$ge = array('Male' => lang('male'), 'Female' => lang('female'));
						echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : ''), 'class="tip form-control" id="gender" data-placeholder="' . lang("select") . ' ' . lang("gender") . '" required="required"');
						?>
					</div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?> 
                        <input type="text" name="phone" class="form-control" id="phone" />
                    </div>
					<div class="form-group">
						<?= lang("commission", "commission"); ?> (%)
						<input type="text" name="agency_commission" class="form-control" min="0" id="agency_commission"/>
					</div>
					<div class="form-group">
                        <?= lang("limit_percent", "limit_percent"); ?> (%)
                        <input type="text" name="agency_limit_percent" class="form-control" min="0" id="agency_limit_percent"/>
                    </div>
                    <div class="form-group">
						<?= lang('value', 'value'); ?>
						<?php
						$vl = array(0 => lang('unit_price'), 1 => lang('grand_total'));
						echo form_dropdown('agency_value_commission', $vl, (isset($_POST['agency_value_commission']) ? $_POST['agency_value_commission'] : ''), 'class="tip form-control" id="agency_value_commission" data-placeholder="' . lang("select") . ' ' . lang("value") . '"');
						?>
					</div>
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
            <?php echo form_submit('add_agency', lang('add_agency'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?=$modal_js ?>