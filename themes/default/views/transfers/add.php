<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    <?php if ($this->session->userdata('remove_tols')) { ?>
    if (localStorage.getItem('toitems')) {
        localStorage.removeItem('toitems');
    }
    if (localStorage.getItem('toshipping')) {
        localStorage.removeItem('toshipping');
    }
    if (localStorage.getItem('toref')) {
        localStorage.removeItem('toref');
    }
    if (localStorage.getItem('to_warehouse')) {
        localStorage.removeItem('to_warehouse');
    }
    if (localStorage.getItem('tonote')) {
        localStorage.removeItem('tonote');
    }
    if (localStorage.getItem('from_warehouse')) {
        localStorage.removeItem('from_warehouse');
    }
	if (localStorage.getItem('to_biller')) {
        localStorage.removeItem('to_biller');
    }
	if (localStorage.getItem('fr_biller')) {
        localStorage.removeItem('fr_biller');
    }
    if (localStorage.getItem('todate')) {
        localStorage.removeItem('todate');
    }
    if (localStorage.getItem('tostatus')) {
        localStorage.removeItem('tostatus');
    }
    <?php $this->cus->unset_data('remove_tols');
} ?>
    var count = 1, an = 1, product_variant = 0, shipping = 0,
        product_tax = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>, toitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function () {
        <?php if ($Owner || $Admin || $GP['transfers-date']) { ?>
        if (!localStorage.getItem('todate')) {
            $("#todate").datetimepicker({
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
        }
        $(document).on('change', '#todate', function (e) {
            localStorage.setItem('todate', $(this).val());
        });
        if (todate = localStorage.getItem('todate')) {
            $('#todate').val(todate);
        }
        <?php } ?>
        ItemnTotals();
        $("#add_item").autocomplete({
            //source: '<?= site_url('transfers/suggestions'); ?>',
            source: function (request, response) {
                if (!$('#from_warehouse').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('transfers/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#from_warehouse").val()
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    if ($('#from_warehouse').val()) {
                        bootbox.alert('<?= lang('no_match_found') ?>', function () {
                            $('#add_item').focus();
                        });
                    } else {
                        bootbox.alert('<?= lang('please_select_warehouse') ?>', function () {
                            $('#add_item').focus();
                        });
                    }
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_transfer_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $('#add_item').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });
		
		
		function checkWarehouse(){
			var from_warehouse =  $('#from_warehouse').val();
			var to_warehouse =  $('#to_warehouse').val();
			if(from_warehouse == to_warehouse){
				$('#add_transfer, #edit_transfer').attr('disabled', true); 
			}else{
				$('#add_transfer, #edit_transfer').attr('disabled', false); 
			}
		}
        $('#from_warehouse, #to_warehouse').on("select2-close", function (e) {
            checkWarehouse();
        });
		checkWarehouse();
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_transfer'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("transfers/add", $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['transfers-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "todate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="todate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "ref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $rnumber), 'class="form-control input-tip" id="ref"'); ?>
                            </div>
                        </div>
						<div class="col-md-4">
                            <div class="form-group">
                                <?= lang("status", "tostatus"); ?>
                                <?php
                                $post = array('completed' => lang('completed'), 'pending' => lang('pending'), 'sent' => lang('sent'));
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="tostatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "fr_biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="fr_biller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'fr_biller',
                                'value' => $this->session->userdata('biller_id'),
                            );

                            echo form_input($biller_input);
                        } ?>
						<?php if($Settings->project == 1){ ?>
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("from_project", "project"); ?>
										<div class="no-project">
											<?php
											$pj[''] = '';
											if(isset($projects) && $projects){
												foreach ($projects as $project) {
													$pj[$project->id] = $project->name;
												}
											}
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
										</div>
									</div>
								</div>
							<?php } else { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("from_project", "project"); ?>
										<div class="no-project">
											<?php
											$pj[''] = ''; 
											if(isset($user) && isset($projects) && $projects){
												$right_project = json_decode($user->project_ids);
												if($right_project){
													foreach ($projects as $project) {
														if(in_array($project->id, $right_project)){
															$pj[$project->id] = $project->name;
														}
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
								<?= lang("from_warehouse", "from_warehouse"); ?>
								<?php
									$wh[''] = '';
									foreach ($warehouses as $warehouse) {
										$wh[$warehouse->id] = $warehouse->name;
									}
								?>
								<?php echo form_dropdown('from_warehouse', $wh, (isset($_POST['from_warehouse']) ? $_POST['from_warehouse'] : ''), 'id="from_warehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("from_warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("to_biller", "to_biller"); ?>
											<?php
											$tbl[""] = lang("select")." ".lang("to_biller");
											foreach ($to_billers as $to_biller) {
												$tbl[$to_biller->id] = $to_biller->name != '-' ? $to_biller->name : $to_biller->company;
											}
											echo form_dropdown('to_biller', $tbl, (isset($_POST['to_biller']) ? $_POST['to_biller'] : ""), 'id="to_biller" data-placeholder="' . lang("select") . ' ' . lang("to_biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
											?>
										</div>
									</div>
									
									<?php if($Settings->project == 1){ ?>
										<?php if ($Owner || $Admin) { ?>
											<div class="col-md-4">
												<div class="form-group">
													<?= lang("to_project", "to_project"); ?>
													<div class="to_no-project">
														<?php
														echo form_dropdown('to_project', $pj, (isset($_POST['to_project']) ? $_POST['to_project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="to_project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
														?>
													</div>
												</div>
											</div>
										<?php } else { ?>
											<div class="col-md-4">
												<div class="form-group">
													<?= lang("to_project", "to_project"); ?>
													<div class="to_no-project">
														<?php
														$pj[''] = ''; 
														if(isset($user) && isset($projects) && $projects){
															$right_project = json_decode($user->project_ids);
															if($right_project){
																foreach ($projects as $project) {
																	if(in_array($project->id, $right_project)){
																		$pj[$project->id] = $project->name;
																	}
																}
															}
														}
														echo form_dropdown('to_project', $pj, (isset($_POST['to_project']) ? $_POST['to_project'] : $Settings->project_id), 'id="to_project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
														?>
													</div>
												</div>
											</div>
										<?php } ?>
									<?php } ?>
									
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("to_warehouse", "to_warehouse"); ?>
											<?php
											$whf[''] = '';
											foreach ($to_warehouses as $to_warehouse) {
												$whf[$to_warehouse->id] = $to_warehouse->name;
											}
											echo form_dropdown('to_warehouse', $whf, (isset($_POST['to_warehouse']) ? $_POST['to_warehouse'] : ''), 'id="to_warehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("to_warehouse") . '" required="required" style="width:100%;" ');
											?>
										</div>
									</div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?></label>
                                <div class="controls table-controls">
                                    <table id="toTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
											<tr>
												<th class="col-md-5"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
												<?php if($Settings->show_qoh == 1) { ?>	
													<th class="col-md-1"><?= lang("qoh"); ?></th>	
												<?php } if ($Settings->product_expiry) {
													echo '<th class="col-md-2">' . $this->lang->line("expiry_date") . '</th>';
												} if ($Settings->product_serial) {
													echo '<th class="col-md-2">' . lang("serial_no") . '</th>';
												} ?>
												<th style="width:30%" class="col-md-1"><?= lang("quantity"); ?></th>
												<?php if($Settings->show_unit == 1) { ?>	
													<th class="col-md-2"><?= lang("unit"); ?></th>	
												<?php } ?>
												<th style="width: 10% !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("document", "document") ?>
									<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
										   data-show-preview="false" class="form-control file">
								</div>
							</div>
							<div class="clearfix"></div>
                            <div class="from-group">
                                <?= lang("note", "tonote"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'id="tonote" class="form-control" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>
                            <div
                                class="from-group"><?php echo form_submit('add_transfer', $this->lang->line("submit"), 'id="add_transfer" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                           
                            </td>
                        </tr>
                    </table>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
					<?php if ($Settings->product_serial) { ?>
                        <div class="form-group hidden">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial">
                            </div>
                        </div>
						<div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <div id="pserials-div"></div>
								<input type="hidden" class="form-control" id="pserial">
								<input type="hidden" class="form-control" id="pscost">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>

                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
		$("#fr_biller").change(biller); biller();
		function biller(){
			var biller = $("#fr_biller").val();
			if(!biller){
				biller = localStorage.getItem('fr_biller');
			}
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
		$("#to_biller").change(to_biller); to_biller();
		function to_biller(){
			var to_biller = $("#to_biller").val();
			if(!to_biller){
				to_biller = localStorage.getItem('to_biller');
			}
			var to_project = 0;
			$.ajax({
				url : "<?= site_url("transfers/get_to_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : to_biller, project : to_project },
				success : function(data){
					if(data){
						$(".to_no-project").html(data.result);
						$("#to_project").select2();
					}
				}
			})
		}
	});
</script>



