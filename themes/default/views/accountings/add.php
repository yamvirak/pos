<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_account'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("accountings/addAccount", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
                <label class="control-label" for="section"><?php echo $this->lang->line("section"); ?></label>
                <?php
				$pgs[''] = lang('select').' '.lang('section');
                foreach ($sections as $section) {
                    $pgs[$section->id] = $section->name;
                }
                echo form_dropdown('section', $pgs, isset($Settings->section)? $Settings->section: '', 'class="form-control tip select" id="section" required="required" style="width:100%;"');
                ?>
            </div>
			<div class="form-group">
                <label class="control-label" for="parent_code"><?php echo $this->lang->line("parent_account"); ?></label>
                <select name="parent_code" class="form-control tip select" id="parent_code" style="width:100%;">
					<option value=""><?= lang('select').' '.lang('parent_account') ?></option>
				</select>
            </div>
            <div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line("code"); ?></label>
                <?php echo form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line("name"); ?></label>
                <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
            
            
            <div class="form-group">
                <label class="control-label" for="is_cash"><?php echo $this->lang->line("is_cash"); ?></label>
                <?php
                $pg_iscash['0'] = lang('no');
				$pg_iscash['1'] = lang('yes');
                echo form_dropdown('is_cash', $pg_iscash, '', 'class="form-control tip select" id="is_cash" required="required" style="width:100%;"');
                ?>
            </div>
			<div class="form-group">
                <label class="control-label" for="inactive"><?php echo $this->lang->line("inactive"); ?></label>
                <?php
                $pg_inactive['0'] = lang('no');
				$pg_inactive['1'] = lang('yes');
                echo form_dropdown('inactive', $pg_inactive, '', 'class="form-control tip select" id="inactive" required="required" style="width:100%;"');
                ?>
            </div>
			
			<div class="form-group">
                <label class="control-label" for="cash_flow"><?php echo $this->lang->line("cash_flow"); ?></label>
                <?php
				$cfs['0'] = lang('select').' '.lang('cash_flow');
                foreach ($cash_flows as $cash_flow) {
                    $cfs[$cash_flow->id] = $cash_flow->name;
                }
                echo form_dropdown('cash_flow', $cfs, '', 'class="form-control tip select" id="cash_flow"  style="width:100%;"');
                ?>
            </div>
			<div class="form-group nature_box" style="display:none">
                <label class="control-label" for="nature"><?php echo $this->lang->line("nature"); ?></label>
                <?php
				$nts['debit'] = lang('debit');
				$nts['credit'] = lang('credit');
                echo form_dropdown('nature', $nts, '', 'class="form-control tip select" id="nature"  style="width:100%;"');
                ?>
            </div>
			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_account', lang('add_account'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script>
	$( document ).ready(function() {
		$("#section").live("change",function(){
			var section_id = $(this).val();
			$.ajax({
				type: 'get',
				dataType : "JSON",
				url: '<?= site_url('accountings/sectionChange'); ?>',
				data: {
					section_id: section_id,						
				},
				success: function (data) {
					$("#parent_code").html(data.acc_options);
					$("#code").val(data.last_code);
					$("#parent_code").change();
				}
			});
		});
		
		$("#cash_flow").live("change",function(){
			var cash_flow = $(this).val();
			if(cash_flow=='0'){
				$('.nature_box').slideUp();
			}else{
				$('.nature_box').slideDown();
			}
		});
		
		$("#parent_code").live("change",function(){
			var parent_id = $(this).val();
			if(parent_id!=''){
				$.ajax({
					type: 'get',
					dataType : "JSON",
					url: '<?= site_url('accountings/parentChange'); ?>',
					data: {
						parent_id: parent_id,						
					},
					success: function (data) {
						$("#code").val(data.last_code);
					}
				});
			}
			
		});
	});
</script>
