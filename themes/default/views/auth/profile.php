<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-sm-2">
        <div class="row">
            <div class="col-sm-12 text-center">
                <div style="max-width:200px; margin: 0 auto;">
                    <?=
                    $user->avatar ? '<img alt="" src="' . base_url() . 'assets/uploads/avatars/thumbs/' . $user->avatar . '" class="avatar">' :
                        '<img alt="" src="' . base_url() . 'assets/images/' . $user->gender . '.png" class="avatar">';
                    ?>
                </div>
                <h4><?= lang('login_email'); ?></h4>

                <p><i class="fa fa-envelope"></i> <?= $user->email; ?></p>
            </div>
        </div>
    </div>

    <div class="col-sm-10">
        <ul id="myTab" class="nav nav-tabs">
            <li class=""><a href="#edit" class="tab-grey"><?= lang('edit') ?></a></li>
            <li class=""><a href="#cpassword" class="tab-grey"><?= lang('change_password') ?></a></li>
            <li class=""><a href="#avatar" class="tab-grey"><?= lang('avatar') ?></a></li>
			<?php if (($Owner || $Admin) && $id != $this->session->userdata('user_id') && $Settings->each_sale > 0 && $Settings->sa_point > 0) { ?>
				<li class=""><a href="#apoints" class="tab-grey"><?= lang('award_points') ?></a></li>
			<?php } ?>
        </ul>
        <div class="tab-content">
            <div id="edit" class="tab-pane fade in">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-edit nb"></i><?= lang('edit_profile'); ?></h2>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <?php $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                                echo form_open('auth/edit_user/' . $user->id, $attrib);
                                ?>
                                <div class="row">
                                    <div class="col-md-12">
                                            <fieldset class="scheduler-border">
                                                <legend class="scheduler-border"><?= lang('edit_profile') ?></legend>
                                                <div class="col-md-12">
                                                    <div class="col-md-5">
                                                        
                                                        <div class="form-group">
                                                            <?php echo lang('last_name', 'last_name'); ?>

                                                            <div class="controls">
                                                                <?php echo form_input('last_name', $user->last_name, 'class="form-control" id="last_name" required="required"'); ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <?= lang('gender', 'gender'); ?>
                                                            <div class="controls">  <?php
                                                                $ge[''] = array('male' => lang('male'), 'female' => lang('female'));
                                                                echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : $user->gender), 'class="tip form-control" id="gender" required="required"');
                                                                ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <?php echo lang('phone', 'phone'); ?>
                                                            <div class="controls">
                                                                <input type="tel" name="phone" class="form-control" id="phone"
                                                                        value="<?= $user->phone ?>"/>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if (($Owner || $Admin) && $id != $this->session->userdata('user_id')) { ?>
                                                        <div style="display:none" class="form-group">
                                                            <?= lang('award_points', 'award_points'); ?>
                                                            <?= form_input('award_points', set_value('award_points', $user->award_points), 'class="form-control tip" id="award_points"  required="required"'); ?>
                                                        </div>
                                                        <?php } ?>

                                                        <?php if ($Owner && $id != $this->session->userdata('user_id')) { ?>
                                                            <div class="form-group">
                                                                <?php echo lang('username', 'username'); ?>
                                                                <input type="text" name="username" class="form-control"
                                                                    id="username" value="<?= $user->username ?>"
                                                                    required="required"/>
                                                            </div>
                                                        <?php } ?>
                                                    </div>

                                                    <div class="col-md-6 col-md-offset-1">
                                                        <div class="form-group">
                                                            <?php echo lang('first_name', 'first_name'); ?>
                                                            <div class="controls">
                                                                <?php echo form_input('first_name', $user->first_name, 'class="form-control" id="first_name" required="required"'); ?>
                                                            </div>
                                                        </div>

                                                        <?php if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
                                                            <div class="form-group">
                                                                <?php echo lang('company', 'company'); ?>
                                                                <div class="controls">
                                                                    <?php echo form_input('company', $user->company, 'class="form-control" id="company"'); ?>
                                                                </div>
                                                            </div>
                                                        <?php } else {
                                                            echo form_hidden('company', $user->company);
                                                        } ?>

                                                        <?php if ($Owner && $id != $this->session->userdata('user_id')) { ?>
                                                            <div class="form-group">
                                                                <?php echo lang('email', 'email'); ?>

                                                                <input type="email" name="email" class="form-control" id="email"
                                                                    value="<?= $user->email ?>"/>
                                                            </div>
                                                            
                                                        <?php } ?>
                                                        
                                                        <?php echo form_hidden('id', $id); ?>
                                                        <?php echo form_hidden($csrf); ?>
                                                    </div>
                                                </div>
                                            </fieldset>
                                    </div>

                                    <?php if ($Owner && $id != $this->session->userdata('user_id')) { ?>
                                        <div class="col-md-12">
                                            <fieldset class="scheduler-border">
                                                <legend class="scheduler-border"><?= lang('if_you_need_to_rest_password_for_user') ?></legend>
                                                <div class="col-md-12">
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <?php echo lang('password', 'password'); ?>
                                                        <?php echo form_input($password); ?>
                                                    </div>
                                                </div>
                                                    
                                                <div class="col-md-6 col-md-offset-1">
                                                    <div class="form-group">
                                                        <?php echo lang('confirm_password', 'password_confirm'); ?>
                                                        <?php echo form_input($password_confirm); ?>
                                                    </div>
                                                </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </fieldset>
                                        </div>
                                    <?php } ?>

                                    <?php if ((isset($this->GP['auth-edit']) || $this->Owner) && $id != $this->session->userdata('user_id')) { ?>
                                        <div class="col-md-12">
                                            <fieldset class="scheduler-border">
                                                <legend class="scheduler-border"><?= lang('user_options') ?></legend>
                                                <div class="col-md-12">
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <?= lang('status', 'status'); ?>
                                                            <?php
                                                                $opt = array(1 => lang('active'), 0 => lang('inactive'));
                                                              echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : $user->active), 'id="status" required="required" class="form-control input-tip select" style="width:100%;"');
                                                            ?>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-md-offset-1">

                                                        <?php if($this->config->item("concretes")){ ?>
                                                            <div class="form-group">
                                                                <?php echo lang('mixzer_commission_percentage', 'mixzer_commission'); ?>
                                                                <div class="controls">
                                                                    <?php echo form_input('mixzer_commission', (isset($_POST['status']) ? $_POST['status'] : $user->mixzer_commission), 'class="form-control" id="mixzer_commission" '); ?>
                                                                </div>
                                                            </div>
                                                        <?php } if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
                                                        <div class="form-group">
                                                            <?= lang("group", "group"); ?>
                                                            <?php
                                                            $gp[""] = "";
                                                            foreach ($groups as $group) {
                                                                if ($group['name'] != 'customer' && $group['name'] != 'supplier') {
                                                                    $gp[$group['id']] = ucfirst($group['name']);
                                                                }
                                                            }
                                                            echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : $user->group_id), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                                            ?>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="no">                                                               
                                                        <?php } ?>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <?php if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
                                                        <div class="no">
                                                            <div class="form-group">
                                                                <?= lang("biller", "biller"); ?>
                                                                <?php
                                                                $bl[""] = lang('all').' '.lang('biller');
                                                                foreach ($billers as $biller) {
                                                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                                                }
                                                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $user->biller_id), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" class="form-control select" style="width:100%;"');
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
                                                                        $project = json_decode($user->project_ids);
                                                                        echo form_dropdown('project[]', $pl, (isset($_POST['project']) ? $_POST['project'] : $project), 'id="project" class="form-control select" multiple style="width:100%;"');
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
                                                                        echo form_dropdown('floor[]', $pfl, (isset($_POST['floor']) ? $_POST['floor'] : json_decode($user_info->floor_ids)), 'id="floor" class="form-control select" multiple style="width:100%;"');
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
                                                                    echo form_dropdown('days_off[]', $days, (isset($_POST['days_off']) ? $_POST['days_off'] : json_decode($user_info->days_off)), ' id="day" class="form-control select" style="width:100%;" multiple');
                                                                    ?>
                                                                </div>
                                                            <?php } ?>

                                                            <div class="form-group">
                                                                <?= lang("view_right", "view_right"); ?>
                                                                <?php
                                                                $vropts = array(1 => lang('all_records'), 0 => lang('own_records'));
                                                                echo form_dropdown('view_right', $vropts, (isset($_POST['view_right']) ? $_POST['view_right'] : $user->view_right), 'id="view_right" class="form-control select" style="width:100%;"');
                                                                ?>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <?= lang("allow_discount", "allow_discount"); ?>
                                                                <?php
                                                                    $opts = array(1 => lang('yes'), 0 => lang('no'));
                                                                    echo form_dropdown('allow_discount', $opts, (isset($_POST['allow_discount']) ? $_POST['allow_discount'] : $user->allow_discount), 'id="allow_discount" class="form-control select" style="width:100%;"');
                                                                ?>
                                                            </div>
                                                        <?php } ?>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-md-offset-1">
                                                        <?php if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
                                                        <div class="no">
                                                            
                                                            <div class="form-group">
                                                                <?= lang("warehouse", "warehouse"); ?>
                                                                <?php
                                                                
                                                                if($warehouses){
                                                                    foreach ($warehouses as $warehouse) {
                                                                        $wh[$warehouse->id] = $warehouse->name;
                                                                    }
                                                                }
                                                                
                                                                echo form_dropdown('warehouse[]', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : json_decode($user->warehouse_id)), 'id="warehouse" class="form-control select" multiple data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" style="width:100%;" ');
                                                                ?>
                                                            </div>

                                                            <div class="form-group">
                                                                <?= lang("edit_right", "edit_right"); ?>
                                                                <?php
                                                                $opts = array(1 => lang('yes'), 0 => lang('no'));
                                                                echo form_dropdown('edit_right', $opts, (isset($_POST['edit_right']) ? $_POST['edit_right'] : $user->edit_right), 'id="edit_right" class="form-control select" style="width:100%;"');
                                                                ?>
                                                            </div>
                                                            
                                                        <?php } ?>
                                                        <?php if($this->config->item("repair")){ ?>
                                                            <div class="form-group">
                                                                <?= lang("technician", "technician"); ?>
                                                                <?= form_dropdown('technician', $opts, (isset($_POST['technician']) ? $_POST['technician'] : (int)$user->technician), 'id="technician" class="form-control select" style="width:100%;"'); ?>
                                                            </div>
                                                        <?php } ?>
                                                        </div>
                                                        </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </fieldset>
                                        </div>
                                    <?php } ?>

                                </div>
                                <p><?php echo form_submit('update', lang('update'), 'class="btn btn-primary"'); ?></p>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="cpassword" class="tab-pane fade">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-key nb"></i><?= lang('change_password'); ?></h2>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <?php echo form_open("auth/change_password", 'id="change-password-form"'); ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <?php echo lang('old_password', 'curr_password'); ?> <br/>
                                                <?php echo form_password('old_password', '', 'class="form-control" id="curr_password" required="required"'); ?>
                                            </div>

                                            <div class="form-group">
                                                <label
                                                    for="new_password"><?php echo sprintf(lang('new_password'), $min_password_length); ?></label>
                                                <br/>
                                                <?php echo form_password('new_password', '', 'class="form-control" id="new_password" required="required"'); ?>
                                                
                                            </div>

                                            <div class="form-group">
                                                <?php echo lang('confirm_password', 'new_password_confirm'); ?> <br/>
                                                <?php echo form_password('new_password_confirm', '', 'class="form-control" id="new_password_confirm" required="required" data-bv-identical="true" data-bv-identical-field="new_password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>

                                            </div>
                                            <?php echo form_input($user_id); ?>
                                            <p><?php echo form_submit('change_password', lang('change_password'), 'class="btn btn-primary"'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="avatar" class="tab-pane fade">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-file-picture-o nb"></i><?= lang('change_avatar'); ?></h2>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-md-5">
                                    <div style="position: relative;">
                                        <?php if ($user->avatar) { ?>
                                            <img alt=""
                                                 src="<?= base_url() ?>assets/uploads/avatars/<?= $user->avatar ?>"
                                                 class="profile-image img-thumbnail">
                                            <a href="#" class="btn btn-danger btn-xs po"
                                               style="position: absolute; top: 0;" title="<?= lang('delete_avatar') ?>"
                                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-block btn-danger po-delete' href='<?= site_url('auth/delete_avatar/' . $id . '/' . $user->avatar) ?>'> <?= lang('i_m_sure') ?></a> <button class='btn btn-block po-close'> <?= lang('no') ?></button>"
                                               data-html="true" rel="popover"><i class="fa fa-trash-o"></i></a><br>
                                            <br><?php } ?>
                                    </div>
                                    <?php echo form_open_multipart("auth/update_avatar"); ?>
                                    <div class="form-group">
                                        <?= lang("change_avatar", "change_avatar"); ?>
                                        <input type="file" data-browse-label="<?= lang('browse'); ?>" name="avatar" id="product_image" required="required"
                                               data-show-upload="false" data-show-preview="false" accept="image/*"
                                               class="form-control file"/>
                                    </div>
                                    <div class="form-group">
                                        <?php echo form_hidden('id', $id); ?>
                                        <?php echo form_hidden($csrf); ?>
                                        <?php echo form_submit('update_avatar', lang('update_avatar'), 'class="btn btn-primary"'); ?>
                                        <?php echo form_close(); ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
			<div id="apoints" class="tab-pane fade">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-file-picture-o nb"></i><?= lang('award_points'); ?></h2>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
								<?php $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
									echo form_open('auth/update_award_point/' . $user->id, $attrib);
								?>
									<div class="col-md-12">
										<div class="col-md-5">
											
											<?php if($this->Admin || $this->Owner){ ?>
												<div class="form-group">
													<?= lang("award_points", "award_points"); ?>
													<?php echo form_input('award_points', $this->cus->formatDecimal($user->award_points), 'class="form-control" min="0" '); ?>
												</div>
											<?php } else {
												$award_points_input = array(
													'type' => 'hidden',
													'name' => 'award_points',
													'id' => 'award_points',
													'value' => $this->cus->formatDecimal($user->award_points),
												);
												echo form_input($award_points_input);
											}?>
											<div class="form-group">
												<?php echo lang('each_sale', 'each_sale'); ?> <br/>
												<?php echo form_input('each_sale', $user->each_sale, 'class="form-control" min="0"  id="each_sale" min="0" required="required" '); ?>
											</div>
											<div class="form-group">
												<?php echo lang('sa_point', 'sa_point'); ?> <br/>
												<?php echo form_input('sa_point', $user->sa_point, 'class="form-control" id="sa_point" required="required" '); ?>
											</div>
											<div class="form-group">
											<?php echo form_submit('update_award_point', lang('update_award_point'), 'class="btn btn-primary"'); ?>
											</div>
										</div>
									</div>
								<?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			
		</div>
    </div>
    <script>
        $(document).ready(function () {
            $('#change-password-form').bootstrapValidator({
                message: 'Please enter/select a value',
                submitButtons: 'input[type="submit"]'
            });
        });
    </script>
    <?php if ($Owner && $id != $this->session->userdata('user_id')) { ?>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function () {
            $('#group').change(function (event) {
                var group = $(this).val();
                if (group == 1 || group == 2) {
                    $('.no').slideUp();
                } else {
                    $('.no').slideDown();
                }
            });
            var group = <?=$user->group_id?>;
            if (group == 1 || group == 2) {
                $('.no').slideUp();
            } else {
                $('.no').slideDown();
            }
        });
    </script>
<?php } ?>

<script type="text/javascript" charset="utf-8">
	$(document).ready(function () {
		$("#biller").change(biller);
		
		biller();
		function biller(){
			var biller = $("#biller option:selected").val();
			var user = "<?= $user->id ?>";
			$.ajax({
				url : "<?= site_url("system_settings/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller , user : user },
				success : function(data){
					if(data){
						$(".no_project").html(data.result);
						$("#project").select2();
					}else{
						
					}
				}
			})
		}
	});
</script>
