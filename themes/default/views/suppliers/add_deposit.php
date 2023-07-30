<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_deposit') . " (" . $company->name . ")"; ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("suppliers/add_deposit/" . $company->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <?php echo lang('date', 'date'); ?>
                        <div class="controls">
                            <?php echo form_input('date', set_value('date', date($dateFormats[($Settings->date_with_time == 0 ? 'php_sdate' : 'php_ldate')])), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
					
					<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
						<div class="form-group">
							<div class="controls">
								<?= lang("biller", "slbiller"); ?>
								<?php
								$bl[""] = "";
								foreach ($billers as $biller) {
									$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
								}
								echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
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
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>

							
						<?php } ?>
					
					<?php } ?>

					<div class="form-group">
                        <?php echo lang('amount', 'amount'); ?>
                        <div class="controls">
                            <?php echo form_input('amount', set_value('amount'), 'class="form-control" id="amount" required="required"'); ?>
                        </div>
                    </div>
                    <div class="form-group">
						<?= lang("paying_by", "paid_by_1"); ?>
						<select name="paid_by" id="paid_by_1" class="form-control paid_by" required="required">
							<?= $this->cus->cash_opts(false,true,false,true); ?>
						</select>
					</div>
					<div class="form-group">
						<?= lang("attachment", "attachment") ?>
						<input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
					</div>
                    <div class="form-group">
                        <?php echo lang('note', 'note'); ?>
                        <div class="controls">
                            <?php echo form_textarea('note', set_value('note'), 'class="form-control" id="note"'); ?>
                        </div>
                    </div>
					
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_deposit', lang('add_deposit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?php echo $modal_js ?>
<script type="text/javascript">
	$("#slbiller").change(biller); biller();
	function biller(){
		var biller = $("#slbiller").val();
		var project = 0;
		$.ajax({
			url : "<?= site_url("customers/get_project") ?>",
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

