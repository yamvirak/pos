<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_purchase_request'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("purchase_requests/add_purchase_request_by_excel", $attrib)
                ?>


                <div class="row">
                    <div class="col-lg-12">
						<div class="well well-small">
                            <a href="<?php echo base_url(); ?>assets/csv/sample_purchase_request.xlsx"
                               class="btn btn-primary pull-right"><i
                                    class="fa fa-download"></i> <?= lang("download_sample_file") ?></a>
                            <span class="text-warning"><?= lang("csv1"); ?></span>
                                </span> <?= lang("csv3"); ?>
                                <p><?= lang('images_location_tip'); ?></p>

                        </div>	
                        <?php if ($Owner || $Admin || $GP['purchase_requests-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "prdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="prdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "prref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="prref"'); ?>
                            </div>
                        </div>
						
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "prbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="prbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'prbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
						<?php if($Settings->project == 1){ ?>
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("project", "project"); ?>
										<div class="input-group">
											<div class="no-project">
												<?php
												$pj[''] = '';
												if($projects){
													foreach ($projects as $project) {
														$pj[$project->id] = $project->name;
													}
												}
												
												echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
												?>
											</div>
											<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
												<a href="<?= site_url('system_settings/projects'); ?>" class="external" target="_blank">
													<i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
												</a>
											</div>
											<div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
												<a href="<?= site_url('system_settings/add_project'); ?>" class="external" data-toggle="modal" data-target="#myModal">
													<i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
												</a>
											</div>
										</div>
									</div>
								</div>
							<?php } else { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("project", "project"); ?>
										<div class="no-project">
											<?php
											$pj[''] = ''; 
											if(isset($user) && isset($projects) && $projects){
												$right_project = json_decode($user->project_ids);
												foreach ($projects as $project) {
													if(in_array($project->id, $right_project)){
														$pj[$project->id] = $project->name;
													}
												}
											}
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
										</div>
									</div>
								</div>
							<?php } ?>
						<?php } ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("warehouse", "prwarehouse"); ?>
								<?php
								if($warehouses){
									foreach ($warehouses as $warehouse) {
										$wh[$warehouse->id] = $warehouse->name;
									}
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="prwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("supplier", "prsupplier"); ?>
											<input type="hidden" name="supplier" value="" id="prsupplier" class="form-control" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
											<input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
										</div>
									</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="sticker"></div>
                        <div class="clearfix"></div>
						<?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_tax", "prtax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['tax2']) ? $_POST['tax2'] : $Settings->default_tax_rate2), 'id="prtax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
						
						<?php echo form_hidden("status","pending"); ?>
						
                        <div class="col-md-4">
							<div class="form-group">
								<label for="xlsx_file"><?= lang("upload_file"); ?></label>
								<input type="file" data-browse-label="<?= lang('browse'); ?>" accept=".xls, .xlsx" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="xlsx_file" required="required"/>
							</div>
						</div>
						
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "prnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="prnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_purchase_request', $this->lang->line("submit"), 'id="add_purchase_request" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <?php echo form_submit('add_purchase_request_next', $this->lang->line("submit_and_next"), 'id="add_purchase_request_next" class="btn btn-info" style="padding: 6px 15px; margin:15px 0;"'); ?>
								<button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?php if ($Owner || $Admin || $GP['purchase_requests-date']) { ?>
            $("#prdate").datetimepicker({
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
        <?php } ?>
		$("#prbiller").change(biller); biller();
		function biller(){
			var biller = $("#prbiller").val();
			var project = 0;
			$.ajax({
				url : "<?= site_url("purchases/get_project") ?>",
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
		
		$('#prsupplier').val(prsupplier).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    });
</script>
