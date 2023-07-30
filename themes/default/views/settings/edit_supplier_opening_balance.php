<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_supplier_opening_balance'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_supplier_opening_balance/" . $supplier_opening_balance->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			 
			 <?php if ($Owner || $Admin || $GP['purchases-date']) { ?>

                <div class="form-group">
                    <?= lang("date", "date"); ?>
                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->cus->hrld($supplier_opening_balance->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                </div>
            <?php } ?>

            <div class="form-group <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                <?= lang("reference", "reference"); ?>
                <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $supplier_opening_balance->reference_no), 'class="form-control tip" id="reference" required="required"'); ?>
            </div>
			
			<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
					<div class="form-group">
						<?= lang("biller", "biller"); ?>
						<?php
						$bl[""] = "";
						foreach ($billers as $biller) {
							$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
						}
						echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $supplier_opening_balance->biller_id), 'id="biller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
						?>
					</div>

			<?php } else {
				$biller_input = array(
					'type' => 'hidden',
					'name' => 'biller',
					'id' => 'biller',
					'value' => $this->session->userdata('biller_id'),
				);
				echo form_input($biller_input);
			} ?>
			<?php if($Settings->project == 1){ ?>
						
				<?php if ($Owner || $Admin) { ?>
								
			
						<div class="form-group">
							<?= lang("project", "project"); ?>
							<div class="no-project">
								<?php
								$pj[''] = '';
								foreach ($projects as $project) {
									$pj[$project->id] = $project->name;
								}
								echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $supplier_opening_balance->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
								?>
							</div>
						</div>
		
				
				<?php } else { ?>
				

						<div class="form-group">
							<?= lang("project", "project"); ?>
							<div class="no-project">
								<?php
								$pj[''] = ''; $right_project = json_decode($user->project_ids);
								foreach ($projects as $project) {
									if(in_array($project->id, $right_project)){
										$pj[$project->id] = $project->name;
									}
								}
								echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $supplier_opening_balance->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '"  style="width:100%;" ');
								?>
							</div>
						</div>
		
					
				<?php } ?>
			<?php } ?>
			<div class="form-group">
				<?= lang("warehouse", "slwarehouse"); ?>
				<?php
				$wh[''] = '';
				foreach ($warehouses as $warehouse) {
					$wh[$warehouse->id] = $warehouse->name;
				}
				echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $supplier_opening_balance->warehouse_id), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
				?>
			</div>



			
			<div class="form-group">
                <?= lang('supplier', 'supplier'); ?>
                <?php
                $ct[''] = lang('select').' '.lang('supplier');
                foreach ($suppliers as $supplier) {
					$ct[$supplier->id] = $supplier->name.' ('.$supplier->code.')';
                }
                ?>
                <?= form_dropdown('supplier', $ct, set_value('supplier', $supplier_opening_balance->supplier_id), 'class="form-control tip" required id="supplier"'); ?>
            </div>
			

			
            <div class="form-group">
                <?= lang("amount", "amount"); ?>
                <input name="amount" type="text" id="amount" value="<?= $this->cus->formatDecimal($supplier_opening_balance->grand_total); ?>"
                       class="pa form-control kb-pad amount" required="required"/>
            </div>
			
			<?php if($Settings->accounting == 1){ ?>
				<div class="form-group">
					<?= lang("opening_account", "opening_account"); ?>
					<select name="opening_account" class="form-control select" id="opening_account" style="width:100%">
						<?= $cash_account ?>
					</select>
				</div>
			<?php } ?>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $supplier_opening_balance->note), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_supplier_opening_balance', lang('edit_supplier_opening_balance'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
    });
	
	$("select").select2();
	$("#biller").change(biller); biller();
	function biller(){
		var biller = $("#biller").val();
		var project = "<?= $supplier_opening_balance->project_id ?>";
		$.ajax({
			url : "<?= site_url("sales/get_project") ?>",
			type : "GET",
			dataType : "JSON",
			data : { biller : biller, project : project },
			success : function(data){
				if(data){
					$(".no-project").html(data.result);
					$("#project").select2();
				}
			}
		})
	}
</script>