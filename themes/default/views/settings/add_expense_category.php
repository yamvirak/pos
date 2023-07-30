<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_expense_category'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_expense_category", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('category_code', 'code'); ?>
                <?= form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('category_name', 'name'); ?>
                <?= form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
			
			<div class="form-group">
                <?= lang('parent', 'parent'); ?>
				<?php 
					$exp = array(lang("select")." ".lang("expense_category"));
					foreach($expenses_categories as $expense){
						$exp[$expense->id] = $expense->name;
					} 
				?>
                <?= form_dropdown('parent', $exp ,'', 'class="form-control" id="parent_id" required="required"'); ?>
            </div>
			
			<?php  if($Settings->accounting==1){ ?>

				<div class="form-group">
					<?= lang("expense_account", "expense_account"); ?>
					<select name="expense_account" class="form-control select" id="expense_account" style="width:100%">
						<?= $expense_accounts ?>
					</select>
				</div>
				
			<?php } ?>
			
			<div class="form-group">
                <?= lang('note', 'note'); ?>
                <?= form_textarea('note', '', 'class="form-control" id="note" required="required"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?= form_submit('add_expense_category', lang('add_expense_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>