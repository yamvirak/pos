<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1;
    $(document).ready(function () {
        if (localStorage.getItem('remove_jnls')) {
            if (localStorage.getItem('jnitems')) {
                localStorage.removeItem('jnitems');
            }
            if (localStorage.getItem('jnref')) {
                localStorage.removeItem('jnref');
            }
			if (localStorage.getItem('jn_type')) {
                localStorage.removeItem('jn_type');
            }
			if (localStorage.getItem('type')) {
                localStorage.removeItem('type');
            }
			
            if (localStorage.getItem('jnnote')) {
                localStorage.removeItem('jnnote');
            }
            if (localStorage.getItem('jndate')) {
                localStorage.removeItem('jndate');
            }
			if (localStorage.getItem('project')) {
                localStorage.removeItem('project');
            }
			if (localStorage.getItem('jncustomer')) {
                localStorage.removeItem('jncustomer');
            }
			if (localStorage.getItem('jnsupplier')) {
                localStorage.removeItem('jnsupplier');
            }
            localStorage.removeItem('remove_jnls');
        }
        <?php if ($enter_journal) { ?>
        localStorage.setItem("jndate", "<?= $this->cus->hrld($enter_journal->date); ?>");
        localStorage.setItem("jnref", "<?= $enter_journal->reference_no; ?>");
		localStorage.setItem("jn_type", "<?= $enter_journal->type; ?>");
		localStorage.setItem("type", "<?= $enter_journal->type; ?>");
		localStorage.setItem("jncustomer", "<?= $enter_journal->customer_id; ?>");
		localStorage.setItem("jnsupplier", "<?= $enter_journal->supplier_id; ?>");
        localStorage.setItem("jnnote", "<?= str_replace(array('\r', '\n'), "", $this->cus->decode_html($enter_journal->note)); ?>");
		localStorage.setItem("jnitems", JSON.stringify(<?= $journal_items; ?>));
        localStorage.setItem("remove_jnls", "1");
        <?php } ?>
        
        $("#add_item").autocomplete({
            source: '<?= site_url('accountings/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_journal_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
		
		
		function changeType(){
			var type = $('#type').val();
			if(type=='customer'){
				$('.supplier-box').slideUp();
				$('.customer-box').slideDown();
			}else if(type=='supplier'){
				$('.customer-box').slideUp();
				$('.supplier-box').slideDown();
			}else{
				$('.customer-box').slideUp();
				$('.supplier-box').slideUp();
			}
		}
		
		$(document).on('change', '#type', function (e) {
			changeType();
        });

		var $customer = $('#slcustomer');	
		function nsCustomer() {
			$('#slcustomer').select2({
				minimumInputLength: 1,
				ajax: {
					url: site.base_url + "customers/suggestions",
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
		}
		function nsSupplier() {
			$('#slsupplier').select2({
				minimumInputLength: 1,
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
		}
		
		if (jncustomer = localStorage.getItem('jncustomer')) {
			$customer.val(jncustomer).select2({
				minimumInputLength: 1,
				data: [],
				initSelection: function (element, callback) {
					$.ajax({
						type: "get", async: false,
						url: site.base_url+"customers/getCustomer/" + $(element).val(),
						dataType: "json",
						success: function (data) {
							callback(data[0]);
						}
					});
				},
				ajax: {
					url: site.base_url + "customers/suggestions",
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
		}else{
			nsCustomer();
		}
		
		var $supplier = $('#slsupplier');

		if (jnsupplier = localStorage.getItem('jnsupplier')) {
			$supplier.val(jnsupplier).select2({
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
		}else{
			nsSupplier();
		}
		
		changeType();
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_enter_journal'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("accountings/edit_enter_journal/".$enter_journal->id, $attrib);
                ?>
				<input type="hidden" name="bank_reco_id" value="<?= $enter_journal->bank_reco_id ?>" />
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['accountings-enter_journals-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "jndate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->cus->hrld($enter_journal->date)), 'class="form-control input-tip datetime" id="jndate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "jnref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $enter_journal->reference_no), 'class="form-control input-tip" id="jnref"'); ?>
                            </div>
                        </div>
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "slbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $enter_journal->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
											
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("project", "project"); ?>
										<div class="no-project">
											<?php
											$pj[''] = '';
											if($projects){
												foreach ($projects as $project) {
													$pj[$project->id] = $project->name;
												}
											}
											
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $enter_journal->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
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
												if($right_project){
													foreach ($projects as $project) {
														if(in_array($project->id, $right_project)){
															$pj[$project->id] = $project->name;
														}
													}
												}
												
											}
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $enter_journal->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
										</div>
									</div>
								</div>
								
							<?php } ?>
						
						<?php } ?>
                        <div class="col-md-4">
							<div class="form-group">
								<?= lang("type", "jn_type"); ?>
								<?php
								$jn["journal"] = "Journal";
								$jn["payment"] = "Payment Voucher";
								$jn["receipt"] = "Receipt Voucher";
								
								echo form_dropdown('jn_type', $jn, (isset($_POST['jn_type']) ? $_POST['jn_type'] : $enter_journal->jn_type), 'id="jn_type" data-placeholder="' . lang("select") . ' ' . lang("type") . '"  class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("type", "type"); ?>
								<?php
								$typ[""] = lang('select').' '.lang('type');
								$typ["customer"] = lang('customer');
								$typ["supplier"] = lang('supplier');
								echo form_dropdown('type', $typ, (isset($_POST['type']) ? $_POST['type'] : $enter_journal->type), 'id="type" data-placeholder="' . lang("select") . ' ' . lang("type") . '"  class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						<div class="col-md-4  customer-box">
							<div class="form-group">
								<?= lang("customer", "slcustomer"); ?>
								<div class="input-group">
									<?php
									echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $enter_journal->customer_id), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '"  class="form-control input-tip" style="width:100%;"');
									?>

									<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
										<a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
											<i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
										</a>
									</div>
									
									<?php if ($Owner || $Admin || $GP['customers-add']) { ?>
										<div class="input-group-addon no-print" style="padding: 2px 5px;">
											<a href="<?= site_url('customers/add'); ?>" id="add-customer" class="external" data-toggle="modal" data-target="#myModal">
												<i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
											</a>
										</div>
									<?php } ?>

								</div>
							</div>
						</div>
						
						<div class="col-md-4  supplier-box">
							<div class="form-group">
								<?= lang("supplier", "slsupplier"); ?>
								<div class="input-group">
									<?php
									echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : $enter_journal->supplier_id), 'id="slsupplier" data-placeholder="' . lang("select") . ' ' . lang("supplier") . '"  class="form-control input-tip" style="width:100%;"');
									?>

									<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
										<a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal">
											<i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
										</a>
									</div>
									
									<?php if ($Owner || $Admin || $GP['suppliers-add']) { ?>
										<div class="input-group-addon no-print" style="padding: 2px 5px;">
											<a href="<?= site_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal">
												<i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
											</a>
										</div>
									<?php } ?>

								</div>
							</div>
						</div>
						
                        <div class="clearfix"></div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_account_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("products"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="jnTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <th><?= lang("account")?></th>
                                            <th class="col-md-4"><?= lang("description"); ?></th>                         
											<th class="col-md-2"><?= lang("debit"); ?></th>
											<th class="col-md-2"><?= lang("credit"); ?></th>
											
                                            <th style="max-width: 30px !important; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= lang("note", "jnnote"); ?>
                                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="jnnote" style="margin-top: 10px; height: 100px;"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('edit_cost_enter_journal', lang("submit"), 'id="submit_journal" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
	$(function(){
		$("#slbiller").change(biller); biller();
		function biller(){
			var biller = $("#slbiller").val();
			var project = "<?= $enter_journal->project_id ?>";
			$.ajax({
				url : "<?= site_url("accountings/get_project") ?>",
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

