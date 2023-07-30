<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('create_user'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext hidden"><?php echo lang('create_user'); ?></p>

                <?php $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                echo form_open("auth/create_user", $attrib);
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('create_user') ?></legend>
                            <div class="col-md-12">
                                <div class="col-md-5">
                                    
                                    <div class="form-group">
                                        <?php echo lang('last_name', 'last_name'); ?>
                                        <div class="controls">
                                            <?php echo form_input('last_name', '', 'class="form-control" id="last_name" required="required"'); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <?= lang('gender', 'gender'); ?>
                                        <?php
                                        $ge[''] = array('male' => lang('male'), 'female' => lang('female'));
                                        echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : ''), 'class="tip form-control" id="gender" data-placeholder="' . lang("select") . ' ' . lang("gender") . '" required="required"');
                                        ?>
                                    </div>

                                    <div class="form-group">
                                        <?php echo lang('phone', 'phone'); ?>
                                        <div class="controls">
                                            <?php echo form_input('phone', '', 'class="form-control" id="phone" '); ?>
                                        </div>
                                    </div>
        
                                    <div class="form-group">
                                        <?php echo lang('username', 'username'); ?>
                                        <div class="controls">
                                            <input type="text" id="username" name="username" class="form-control"
                                                required="required" pattern=".{4,20}"/>
                                        </div>
                                    </div>

                                </div>
                                
                                <div class="col-md-6 col-md-offset-1">
                                    <div class="form-group">
                                        <?php echo lang('first_name', 'first_name'); ?>
                                        <div class="controls">
                                            <?php echo form_input('first_name', '', 'class="form-control" id="first_name" required="required" pattern=".{3,10}"'); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <?php echo lang('company', 'company'); ?>
                                        <div class="controls">
                                            <?php echo form_input('company', '', 'class="form-control" id="company"'); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <?php echo lang('email', 'email'); ?>
                                        <div class="controls">
                                            <input type="email" id="email" name="email" class="form-control"
                                                />
                                            <?php /* echo form_input('email', '', 'class="form-control" id="email" required="required"'); */ ?>
                                        </div>
                                    </div>
                                    
                                    <!--
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label class="checkbox" for="notify">
                                                <input type="checkbox" name="notify" value="1" id="notify" checked="checked"/>
                                                <?= lang('notify_user_by_email') ?>
                                            </label>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    -->

                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="col-md-12">
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('create_password') ?></legend>
                            <div class="col-md-12">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <?php echo lang('password', 'password'); ?>
                                        <div class="controls">
                                            <?php echo form_password('password', '', 'class="form-control tip" id="password" required="required" '); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-md-offset-1">
                                    <div class="form-group">
                                        <?php echo lang('confirm_password', 'confirm_password'); ?>
                                        <div class="controls">
                                            <?php echo form_password('confirm_password', '', 'class="form-control" id="confirm_password" required="required" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="col-md-12">
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('user_options') ?></legend>
                            <div class="col-md-12">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <?= lang('status', 'status'); ?>
                                        <?php
                                        $opt = array(1 => lang('active'), 0 => lang('inactive'));
                                        echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" required="required" class="form-control select" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-6 col-md-offset-1">
                                    <?php if($this->config->item("concretes")){ ?>
                                        <div class="form-group">
                                            <?php echo lang('mixzer_commission_percentage', 'mixzer_commission'); ?>
                                            <div class="controls">
                                                <?php echo form_input('mixzer_commission', '', 'class="form-control" id="mixzer_commission" '); ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="form-group">
                                        <?= lang("group", "group"); ?>
                                        <?php
                                        foreach ($groups as $group) {
                                            if ($group['name'] != 'customer' && $group['name'] != 'supplier') {
                                                $gp[$group['id']] = ucfirst($group['name']);
                                            }
                                        }
                                        echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : ''), 'id="group" required="required" class="form-control select" style="width:100%;"');
                                        ?>
                                    </div>

                                    <div class="clearfix"></div>
                                </div>
                                
                                <div class="col-md-5">
                                    <div class="no">
                                        <div class="form-group">
                                            <?= lang("biller", "biller"); ?>
                                            <?php
                                            $bl[""] = lang('all').' '.lang('biller');
                                            foreach ($billers as $biller) {
                                                $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                            }
                                            echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'id="biller" class="form-control select" style="width:100%;"');
                                            ?>
                                        </div>
                                        
                                        <?php if($Settings->project == 1){ ?>
                                        
                                        <div class="form-group">
                                            <?= lang("project", "project"); ?>
                                            <div class="no_project">
                                                <?php
                                                $pl = array();
                                                foreach ($projects as $project) {
                                                    $pl[$project->id] = $project->name;
                                                }
                                                echo form_dropdown('project[]', $pl, (isset($_POST['project']) ? $_POST['project'] : ''), 'id="project" class="form-control select" multiple style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <?php } if($this->pos_settings->table_enable == 1){ ?>
                                            <div class="form-group">
                                                <?= lang("floor", "floor"); ?>
                                                
                                                    <?php
                                                    $pfl = array();
                                                    foreach ($floors as $floor) {
                                                        $pfl[$floor->id] = $floor->floor;
                                                    }
                                                    echo form_dropdown('floor[]', $pfl, (isset($_POST['floor']) ? $_POST['floor'] : ''), 'id="floor" class="form-control select" multiple style="width:100%;"');
                                                    ?>
                                            
                                            </div>
                                        <?php } ?>
                                        
                                        <?php if($Settings->login_time){ ?>
                                                <div class="form-group">
                                                    <?= lang("days_off", "days_off"); ?>
                                                    <?php
                                                    $days = array(
                                                                "Mon" => lang("monday"),
                                                                "Tue" => lang("tuesday"),
                                                                "Wed" => lang("wednesday"),
                                                                "Thu" => lang("thursday"),
                                                                "Fri" => lang("friday"),
                                                                "Sat" => lang("saturday"),
                                                                "Sun" => lang("sunday"),
                                                        );
                                                    echo form_dropdown('days_off[]', $days, (isset($_POST['days_off']) ? $_POST['days_off'] : ''), ' id="day" class="form-control select" style="width:100%;" multiple');
                                                    ?>
                                                </div>
                                        <?php } ?>
                                        
                                        <div class="form-group">
                                            <?= lang("view_right", "view_right"); ?>
                                            <?php
                                            $vropts = array(1 => lang('all_records'), 0 => lang('own_records'));
                                            echo form_dropdown('view_right', $vropts, (isset($_POST['view_right']) ? $_POST['view_right'] : 1), 'id="view_right" class="form-control select" style="width:100%;"');
                                            ?>
                                        </div>

                                        <div class="form-group">
                                            <?= lang("allow_discount", "allow_discount"); ?>
                                            <?php
                                                $opts = array(1 => lang('yes'), 0 => lang('no'));
                                                echo form_dropdown('allow_discount', $opts, (isset($_POST['allow_discount']) ? $_POST['allow_discount'] : 0), 'id="allow_discount" class="form-control select" style="width:100%;"');
                                            ?>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6 col-md-offset-1">
                                    <div class="no">
                                        <div class="form-group">
                                            <?= lang("warehouse", "warehouse"); ?>
                                            <?php
                                            foreach ($warehouses as $warehouse) {
                                                $wh[$warehouse->id] = $warehouse->name;
                                            }
                                            echo form_dropdown('warehouse[]', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'id="warehouse" multiple class="form-control select" style="width:100%;" ');
                                            ?>
                                        </div>

                                        <div class="form-group">
                                            <?= lang("edit_right", "edit_right"); ?>
                                            <?php
                                            $opts = array(1 => lang('yes'), 0 => lang('no'));
                                            echo form_dropdown('edit_right', $opts, (isset($_POST['edit_right']) ? $_POST['edit_right'] : 0), 'id="edit_right" class="form-control select" style="width:100%;"');
                                            ?>
                                        </div>

                                        <?php if($this->config->item("repair")==true){ ?>
                                            <div class="form-group">
                                                <?= lang("technician", "technician"); ?>
                                                <?= form_dropdown('technician', $opts, (isset($_POST['technician']) ? $_POST['technician'] : 0), 'id="technician" class="form-control select" style="width:100%;"'); ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>

                            </div>
                        </fieldset>
                    </div>
                </div>

                <p><?php echo form_submit('add_user', lang('add_user'), 'class="btn btn-primary"'); ?></p>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        $('.no').slideUp();
        $('#group').change(function (event) {
            var group = $(this).val();
            if (group == 1 || group == 2) {
                $('.no').slideUp();
            } else {
                $('.no').slideDown();
            }
        });
    });
</script>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function () {
		$("#biller").change(function(){
			var biller = $(this).val();
			$.ajax({
				url : "<?= site_url("system_settings/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller },
				success : function(data){
					$(".no_project").html(data.result);
					$("#project").select2();
				}
			})
		})
	});
</script>
