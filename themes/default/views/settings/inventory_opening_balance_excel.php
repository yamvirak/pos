<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_inventory_opening_balance_excel'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/add_inventory_opening_balance_excel/", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-12">
                    <div class="well well-small">
                        <a href="<?php echo base_url(); ?>assets/csv/sample_inventory_opening_balance.xlsx" class="btn btn-primary pull-right">
                            <i class="fa fa-download"></i> <?= lang("download_sample_file") ?>
                        </a>
                        <span class="text-warning"><?= lang("csv1"); ?></span><br/><?= lang("csv2"); ?> 
                        <span class="text-info">(<?= lang("product_code") . ', ' . lang("product_cost"); ?>)</span>
                        <?= lang("csv3"); ?>
                    </div>
					<?php if ($Owner || $Admin || $GP['system_settings-date']) { ?>
						<div class="form-group">
							<?= lang("date", "date"); ?>
							<?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
						</div>
					<?php } ?>
						
					<div class="form-group <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
						<?= lang("reference", "reference"); ?>
						<?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : ''), 'class="form-control tip" id="reference"'); ?>
					</div>
					
					<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
							<div class="form-group">
								<?= lang("biller", "slbiller"); ?>
								<?php
								$bl[""] = "";
								foreach ($billers as $biller) {
									$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
								}
								echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
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
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '"  style="width:100%;" ');
										?>
									</div>
								</div>
							<?php } ?>
						
						<?php } ?>
						
		
					<div class="form-group">
						<?= lang("warehouse", "ivowarehouse"); ?>
						<?php
						$wh[''] = '';
						foreach ($warehouses as $warehouse) {
							$wh[$warehouse->id] = $warehouse->name;
						}
						echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="ivowarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required"  style="width:100%;"');
						?>
					</div>
                            

					
					<div class="form-group">
						<div class="form-group">
							<?= lang("document", "document") ?>
							<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
								   data-show-preview="false" class="form-control file">
						</div>
					</div>
					

                    <div class="form-group">
						<label for="xlsx_file"><?= lang("upload_file"); ?></label>
						<input type="file" data-browse-label="<?= lang('browse'); ?>" accept=".xls, .xlsx" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="xlsx_file" required="required"/>
					</div>

                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_inventory_opening_balance', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script type="text/javascript">
	$(function(){
		$(document).ready(function () {
			$.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
			$("#date").datetimepicker({
				<?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
				fontAwesome: true,
				language: 'cus',
				weekStart: 1,
				todayBtn: 1,
				autoclose: 1,
				todayHighlight: 1,
				startView: 2,
				forceParse: 0
			}).datetimepicker('update', new Date());
		});
		$("#slbiller").change(biller); biller();
		function biller(){
			var biller = $("#slbiller").val();
			var project = 0;
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
	});
</script>