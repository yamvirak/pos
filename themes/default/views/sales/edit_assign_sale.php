<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_assign_sale'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("sales/edit_assign_sale/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>			
			<div class="row">				
				
				<div class="col-sm-12">
					<?php if ($Owner || $Admin || $GP['sales-date']) { ?>
						<div class="form-group">
							<?= lang("date", "date"); ?>
							<?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y h:i')), 'class="form-control datetime" id="date" required="required"'); ?>
						</div>
						
						<div class="form-group hidden">
						<?= lang("created_by", "created_by"); ?>
						<?php 
							$users = array();
							foreach($allUsers as $user){
								$users[$user->id] = ucfirst($user->username);
							}
						?>
						<?= form_dropdown('created_by', $users, $assign_sale->created_by, 'class="form-control" required="required"'); ?>
						</div>
						
					<?php } ?>
					
					<div class="form-group">
						<?= lang("reference", "reference"); ?>
						<?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $assign_sale->reference_no), 'class="form-control tip" id="reference"'); ?>
					</div>
					<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
	                        <div class="form-group">
	                            <?= lang("biller", "slbiller"); ?>
	                            <?php
	                            $bl[""] = "";
	                            foreach ($billers as $biller) {
	                                $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
	                            }
	                            echo form_dropdown('biller', $bl, ($assign_sale->biller_id ? $assign_sale->biller_id : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
	                            ?>
	                        </div>
	                <?php } else {
	                    $biller_input = array(
	                        'type' => 'hidden',
	                        'name' => 'biller',
	                        'id' => 'slbiller',
	                        'value' => $this->session->userdata('biller_id'),
	                    );

	                    echo form_input($biller_input);
	                } ?>
					<div class="form-group">
						<?= lang("assign_to", "assign_to"); ?>
						<?php 
							$users = array("&nbsp;");
							foreach($allUsers as $user){
								$users[$user->id] = ucfirst($user->username);
							}
						?>
						<?= form_dropdown('assign_to', $users, $assign_sale->assign_to, 'class="form-control" required="required"'); ?>
					</div>
					
					<div class="form-group">
						<?= lang("note", "note"); ?>
						<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $assign_sale->note), 'class="form-control" id="note"'); ?>
					</div>
					
				</div>
				
			</div>			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_assign_sale', lang('update'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>
