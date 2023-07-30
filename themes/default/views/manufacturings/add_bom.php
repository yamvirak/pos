<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1,count_finish = 1, an_finish = 1;
    $(document).ready(function () {
        if (localStorage.getItem('remove_bomls')) {
            if (localStorage.getItem('bomitems')) {
                localStorage.removeItem('bomitems');
            }
            if (localStorage.getItem('bomfinitems')) {
                localStorage.removeItem('bomfinitems');
            }
            if (localStorage.getItem('bomname')) {
                localStorage.removeItem('bomname');
            }
            if (localStorage.getItem('sodate')) {
			    localStorage.removeItem('sodate');
		    }
            
            localStorage.removeItem('remove_bomls');
        }
        <?php if ($Owner || $Admin || $GP['products-converts-date']) { ?>
			if (!localStorage.getItem('sodate')) {
				$("#sodate").datetimepicker({
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
			$(document).on('change', '#sodate', function (e) {
				localStorage.setItem('sodate', $(this).val());
			});
			if (sodate = localStorage.getItem('sodate')) {
				$('#sodate').val(sodate);
			}
        <?php } ?>
        
        
        $("#add_item").autocomplete({
            source: '<?= site_url('system_settings/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
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
                    var row = add_raw_material_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        
        $("#add_finish_item").autocomplete({
            source: '<?= site_url('system_settings/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_finish_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
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
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_finish_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_finised_good_item(ui.item);
                    if (row)
                        $(this).val('');
                        $('#add_finish_item').focus();
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_bom'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("converts/add_bom", $attrib);
                ?>
                <div class="row">
                <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "sodate"); ?>
                                    <?php echo form_input('updated_at', (isset($_POST['updated_at']) ? $_POST['updated_at'] : ""), 'class="form-control input-tip datetime" id="sodate" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("name", "bomname"); ?>
                                <?php echo form_input('name', (isset($_POST['name']) ? $_POST['name'] : ''), 'class="form-control input-tip" required id="bomname"'); ?>
                            </div>
                        </div>
                         <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "slbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->name : $biller->company;
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
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_raw_material_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("raw_material"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="bomRaw" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                            <tr>
                                                <th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>                                            
                                                <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                <th class="col-md-1"><?= lang("unit"); ?></th>
                                                <th style="max-width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
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
                                        <?php echo form_input('add_finish_item', '', 'class="form-control input-lg" id="add_finish_item" placeholder="' . lang("add_finished_good_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("finished_good"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="bomFinished" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                            <tr>
                                                <th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>                                            
                                                <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                <th class="col-md-1"><?= lang("unit"); ?></th>
                                                <th style="max-width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <?= lang("note", "bomnote"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="bomnote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="fprom-group"><?php echo form_submit('add_bom', lang("submit"), 'id="add_bom" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
    
    $("#slbiller").change(biller); biller();
        function biller(){
            var biller = $("#slbiller").val();
            var project = "<?= $bom->project_id ?>";
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
