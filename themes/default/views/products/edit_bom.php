<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    <?php 
		if ($this->session->userdata('remove_bols')) { ?>
    if (localStorage.getItem('boitemsFr')) {
        localStorage.removeItem('boitemsFr');
    }
	 if (localStorage.getItem('boitemsTo')) {
        localStorage.removeItem('boitemsTo');
    }
    if (localStorage.getItem('boref')) {
        localStorage.removeItem('boref');
    }
    if (localStorage.getItem('bonote')) {
        localStorage.removeItem('bonote');
    }
    if (localStorage.getItem('bodate')) {
        localStorage.removeItem('bodate');
    }
    <?php $this->cus->unset_data('remove_bols'); } ?>
	
    var count = 1, an = 1, product_variant = 0, total = 0,
        boitemsFr = {}, boitemsTo = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function () {
		<?php if ($bom) {?>
        localStorage.setItem('todate', '<?= date($dateFormats[($Settings->date_with_time == 0 ? 'php_sdate' : 'php_ldate')], strtotime($bom->date)) ?>');
        localStorage.setItem('boref', '<?= $bom->reference_no ?>');
        localStorage.setItem('bonote', '<?= $this->cus->decode_html($bom->note); ?>');
        localStorage.setItem('boitemsFr', JSON.stringify(<?= $bom_items; ?>));
		localStorage.setItem('boitemsTo', JSON.stringify(<?= $bom_itemsTo; ?>));
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['products-date']) { ?>
        if (!localStorage.getItem('bodate')) {
            $("#bodate").datetimepicker({
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
        $(document).on('change', '#bodate', function (e) {
            localStorage.setItem('bodate', $(this).val());
        });
        if (bodate = localStorage.getItem('bodate')) {
            $('#bodate').val(bodate);
        }
        <?php } ?>
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('transfers/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#bowarehouse").val()
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
                    var row = add_bom_itemFr(ui.item);
                    if (row)
                        $(this).val('');
                } else {
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
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#add_itemTo").autocomplete({            
            source: function (request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('transfers/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#bowarehouse").val()
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
                    var row = add_bom_itemTo(ui.item);
                    if (row)
                        $(this).val('');
                } else {                    
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $('#add_itemTo').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_bom'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo form_open_multipart("products/edit_bom/".$bom->id, $attrib);
                ?>
					<?php if ($Owner || $Admin || $GP['products-date']) { ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("date", "bodate"); ?>
								<?php echo form_input('bodate', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="bodate" required="required"'); ?>
							</div>
						</div>
					<?php } ?>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang("name", "name"); ?>						   							
							<?php echo form_input('name', (isset($_POST['name']) ? $_POST['name'] : $conumber),'class="form-control input-tip" id="name"'); ?>
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
						} 
					?>
					<div class="col-md-12">
						<div class="form-group">
							<?= lang("note", "ponote"); ?>
							<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
						</div>
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
					<div class="col-md-12">
						<div class="control-group table-group">
							<label class="table-label"><?= lang("bom_items_from"); ?> *</label>
							<div class="controls table-controls">
								<table id="cfTable" class="table items table-striped table-bordered table-condensed table-hover">
									<thead>
										<tr>
											<th class="col-md-9"><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
											<th class="col-md-2"><?= lang("quantity"); ?></th>											
											<th style="width: 30px !important; text-align: center;">
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
					<div class="col-md-12" id="sticker">
						<div class="well well-sm">
							<div class="form-group" style="margin-bottom:0;">
								<div class="input-group wide-tip">
									<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
										<i class="fa fa-2x fa-barcode addIcon"></i></a></div>
									<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_itemTo" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="control-group table-group">
							<label class="table-label"><?= lang("bom_items_to"); ?> *</label>
							<div class="controls table-controls">
								<table id="ctTable" class="table items table-striped table-bordered table-condensed table-hover">
									<thead>
										<tr>
											<th class="col-md-9"><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
											<th class="col-md-2"><?= lang("quantity"); ?></th>											
											<th style="width: 30px !important; text-align: center;">
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
					<!-- Button Submit -->
					<div class="col-md-12">
						<div class="fprom-group"><?php echo form_submit('edit_bom', lang("submit"), 'id="edit_bom" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
						<button type="button" name="reset" class="btn btn-danger" id="reset"><?= lang('reset') ?></button></div>
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
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="prModalTo" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="pquantityTo" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantityTo">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-divTo"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-divTo"></div>
                        </div>
                    </div>
                    <input type="hidden" id="old_qtyTo" value=""/>
                    <input type="hidden" id="row_idTo" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItemTo"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
