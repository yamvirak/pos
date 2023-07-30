<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_category'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_category/".$category->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>

            <div class="form-group">
                <?= lang('category_code', 'code'); ?>
                <?= form_input('code', set_value('code', $category->code), 'class="form-control" id="code" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('category_name', 'name'); ?>
                <?= form_input('name', set_value('name', $category->name), 'class="form-control" id="name" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("category_image", "image") ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>
			
			<?php if(!$this->config->item('one_biller')){ ?>
				<div class="form-group">
					<label class="control-label" for="biller"><?= lang("biller"); ?></label>
					<?php
					foreach ($billers as $biller) {
						$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
					}
					echo form_dropdown('biller[]', $bl, (isset($_POST['biller']) ? $_POST['biller'] : json_decode($category->biller)), 'class="form-control biller" id="biller" multiple data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
					?>
				</div>
			
			<?php } if($Settings->project == 1){ ?>	
				<div class="form-group project">
					<?= lang("project", "project"); ?>
					<div class="no-project-multi">
						<?php
						$mpj[''] = array(); 
						if(isset($multi_projects) && $multi_projects){
							foreach ($multi_projects as $multi_project) {
								$mpj[$multi_project->id] = $multi_project->name;
							}
						}
						
						echo form_dropdown('project_multi[]', $mpj, (isset($_POST['project_multi']) ? $_POST['project_multi'] : $category->project), 'id="project_multi" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '"  style="width:100%;" multiple');
						?>
					</div>	
				</div>
			<?php } ?>
			
			<?php if(POS || $Settings->installment){ ?>
				<div class="form-group">
					<?= lang("warehouse", "warehouse") ?>
					<?php                
					$wh = array(lang('select')); 
					foreach($warehouses as $warehouse){
						$wh[$warehouse->id] = $warehouse->name;
					}
					echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $category->warehouse_id), 'class="form-control select" id="warehouse" style="width:100%"')
					?>
				</div>
			<?php } ?>
			
			
			<?php if($Settings->installment){ ?>
				
				<div class="form-group">
					<?= lang("installment", "installment") ?>
					<?php                
					$ln = array(0 =>lang('no'), 1=>lang('yes'));
					echo form_dropdown('installment', $ln, (isset($_POST['installment']) ? $_POST['installment'] : $category->installment), 'class="form-control select" id="installment" style="width:100%"')
					?>
				</div>
				
			<?php } ?>
			
			<?php if($pos_settings->table_enable){ ?>
			
				<div class="form-group">
					<?= lang("category_type", "category_type") ?>
					<?php                
					$types1 = array(lang('select')); 
					foreach($types as $type){
						$types1[$type->id] = $type->name;
					}              
					echo form_dropdown('type', $types1, (isset($_POST['type']) ? $_POST['type'] : $category->type_id), 'class="form-control select" id="type" style="width:100%"')
					?>
				</div>
			
			<?php } ?>
			
            <div class="form-group">
                <?= lang("parent_category", "parent") ?>
                <?php
                $cat[''] = lang('select').' '.lang('parent_category');
                foreach ($categories as $pcat) {
                    $cat[$pcat->id] = $pcat->name;
                }
                echo form_dropdown('parent', $cat, (isset($_POST['parent']) ? $_POST['parent'] : $category->parent_id), 'class="form-control select" id="parent" style="width:100%"')
                ?>
            </div>
			
		<?php if($Settings->accounting == 1){ ?>	
		
			<div class="form-group">
                <?= lang("stock_account", "stock_account") ?>
				<select name="stock_account" class="form-control select" id="stock_account" style="width:100%">
					<option value=""><?= lang('select_stock_account') ?></option>
					<?= $stock_accounts ?>
				</select>
            </div>
			
			<div class="form-group">
                <?= lang("adjustment_account", "adjustment_account") ?>
				<select name="adjustment_account" class="form-control select" id="adjustment_account" style="width:100%">
					<option value=""><?= lang('select_adjustment_account') ?></option>
					<?= $adjustment_accounts ?>
				</select>
            </div>
			
			<div class="form-group">
                <?= lang("usage_account", "usage_account") ?>
				<select name="usage_account" class="form-control select" id="usage_account" style="width:100%">
					<option value=""><?= lang('select_usage_account') ?></option>
					<?= $usage_accounts ?>
				</select>
            </div>
			
			<?php  if($this->config->item('convert')){?>
				<div class="form-group">
					<?= lang("convert_account", "convert_account") ?>
					<select name="convert_account" class="form-control select" id="convert_account" style="width:100%">
						<option value=""><?= lang('select_convert_account') ?></option>
						<?= $convert_accounts ?>
					</select>
				</div>
			<?php } ?>
			
			<div class="form-group">
                <?= lang("cost_of_sale_account", "cost_of_sale_account") ?>
				<select name="cost_of_sale_account" class="form-control select" id="cost_of_sale_account" style="width:100%">
					<option value=""><?= lang('select_cost_of_sale_account') ?></option>
					<?= $cost_accounts ?>
				</select>
            </div>
			<div class="form-group">
                <?= lang("sale_account", "sale_account") ?>
				<select name="sale_account" class="form-control select" id="sale_account" style="width:100%">
					<option value=""><?= lang('select_sale_account') ?></option>
					<?= $sale_accounts ?>
				</select>
            </div>
			<?php if($this->config->item("pawn")){ ?>
				<div class="form-group">
					<?= lang("pawn_account", "pawn_account") ?>
					<select name="pawn_account" class="form-control select" id="pawn_account" style="width:100%">
						<option value=""><?= lang('select_pawn_account') ?></option>
						<?= $pawn_accounts ?>
					</select>
				</div>
			<?php } ?>
		<?php } ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_category', lang('edit_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>

<script type="text/javascript">
    $(document).ready(function () {
		$('#project').live('change', function() {
			var project_id = $(this).val();
			if(project_id != '0'){
				$(".seperate_project").slideUp();
			}else{
				$(".seperate_project").slideDown();
				
			}
		});
		biller();
		$("#biller").change(biller);
		function biller(){
			var biller = $("#biller").val();
			<?php
				$multi_project = '';
				if($category && $category->project && $category->project != "null"){
					$projects = json_decode($category->project);
					foreach($projects as $project){
						$multi_project .=$project.'#';;
					}
				}
			?>
			var project_multi = '<?= $multi_project ?>';
			$.ajax({
				url : "<?= site_url("system_settings/get_multi_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project_multi : project_multi },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$(".no-project-multi").html(data.multi_resultl);
						$("#project_multi").select2();
					}
				}
			})
		}
    });
</script>