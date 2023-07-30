<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=lang('rooms');?> | <?= $Settings->site_name ?></title>
    <base href="<?=base_url()?>" />
    <meta http-equiv="cache-control" content="max-age=0"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
    <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
    <link rel="stylesheet" href="<?=$assets?>styles/style.css" type="text/css"/>    
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
</head>
<body>
<noscript>
    <div class="global-site-notice noscript">
        <div class="notice-inner">
            <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                your browser to utilize the functionality of this website.</p>
        </div>
    </div>
</noscript>
	<div id="wrapper">
		<header id="header" class="navbar">
			<div class="container">
				<div class="navbar-brand">
	                 <span style="padding: 13px;display: block;float: left;"><a href="#" id="main-menu-act"><i class="icon fa fa-tasks tip white"></i></a></span>
	                <?php if ($Settings->logo) {
	                    echo '<a href="'.site_url().'"><img src="' . base_url('assets/uploads/logos/' . $Settings->logo) . '" alt="' . $Settings->site_name . '" style="width: 150px;" /></a>';
	                } ?>
            	</div>
				<div class="header-nav">
					<ul class="nav navbar-nav pull-right">
						<li class="dropdown">
							<a class="account dropdown-toggle" data-toggle="dropdown" href="#">
								<img class="account" alt="" src="<?=$this->session->userdata('avatar') ? site_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/' . $this->session->userdata('gender') . '.png';?>" class="mini_avatar img-rounded" style="border-radius: 50px;width: 43px;margin:-15px 0px -15px 0px;">
								<div class="btn white">
									<span><?=lang('welcome')?>! <?=$this->session->userdata('username');?></span>
								</div>
							</a>
							<ul class="dropdown-menu pull-right">
								<li>
									<a href="<?=site_url('auth/profile/' . $this->session->userdata('user_id'));?>">
										<i class="fa fa-user"></i> <?=lang('profile');?>
									</a>
								</li>
								<li>
									<a href="<?=site_url('auth/profile/' . $this->session->userdata('user_id') . '/#cpassword');?>">
										<i class="fa fa-key"></i> <?=lang('change_password');?>
									</a>
								</li>
								<li class="divider"></li>
								<li>
									<a href="<?=site_url('auth/logout');?>">
										<i class="fa fa-sign-out"></i> <?=lang('logout');?>
									</a>
								</li>
							</ul>
						</li>
					</ul>
					<ul class="nav navbar-nav pull-right">
						<li class="dropdown">
							<a class="btn pos-tip" title="<?=lang('dashboard')?>" data-placement="bottom" href="<?=site_url('welcome')?>">
								<i class="fa fa-dashboard"></i>
							</a>
						</li>
						<li class="dropdown">
							<a href="<?= site_url("rentals") ?>" class="btn pos-tip" title="<?=lang('rentals')?>" data-placement="bottom" href="<?=site_url('rentals')?>">
								<i class="fa-fw fa fa-bed"></i>
							</a>
						</li>
						<li class="dropdown">
							<a href="<?= site_url("customers") ?>" class="btn pos-tip" title="<?=lang('customers')?>" data-placement="bottom" href="<?=site_url('customers')?>">
								<i class="fa fa-users"></i>
							</a>
						</li>
						<?php if ($Owner || $Admin) {?>                      
							<li class="dropdown hidden-xs">
								<a class="btn pos-tip" title="<?=lang('clear_ls')?>" data-placement="bottom" id="clearLS" href="#">
									<i class="fa fa-eraser"></i>
								</a>
							</li>
						<?php } ?>
					</ul>
					<ul class="nav navbar-nav pull-right">					
						<li class="dropdown">
							<a class="btn bred" style="cursor: default;">
								<span class="pos-logo-lg" style="font-size:23px;" id="time-part"></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</header>

		<div class="container" style="margin-top:50px;">
			<div class="row">
				<div>
					<div class="col-sm-2">
						<div class="form-group">
							<?= lang("warehouse", "rtwarehouse"); ?>
							<?php
								$wh[''] = '';
								foreach ($warehouses as $warehouse) {
										$wh[$warehouse->id] = $warehouse->name;
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="rtwarehouse" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
							?>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							<?= lang("floor", "floor"); ?>
							<?php
								$fl = array(lang('select').' '.lang('floor'));
								foreach ($floors as $floor) {
									$fl[$floor->id] = $floor->floor;
								}
								echo form_dropdown('floor', $fl, (isset($_GET['floor']) ? $_GET['floor'] : $Settings->default_floor), 'id="rtfloor" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("floor") . '" required="required" style="width:100%;" ');
							?>
						</div>
					</div>
					<div class="col-sm-8">
						<div class="form-group"><br>
							<span style="font-size: 40px;vertical-align: middle;"><i class="fa-fw fa fa-bed"></i></span>
							<button class="btn tip btn-primary" data-original-title="<?=lang('room_status');?>"><label style="color:#fff;"><i class="icon fa fa-television" style="padding-right:5px;"></i><?=lang("room_status") ?> </label> </button>

								<span style="font-size: 40px;vertical-align: middle;"><i class="fa-fw fa fa-bed"></i></span>

							<button class="btn tip btn-primary" data-original-title="<?=lang('room_free');?>"><label style="color:#fff;"><i class="fa fa-sign-in" style="padding-right:5px;"></i><?=lang("room_free") ?> </label> </button>
					
							<button class="btn tip btn-success" data-original-title="<?=lang('room_occupied');?>"><label style="color:#fff;"><i class="fa fa-sign-in" style="padding-right:5px;"></i><?=lang("room_occupied") ?> </label></button> 
                        	<button class="btn tip btn-warning" data-original-title="<?=lang('room_reservation');?>"><label style="color:#fff;"><i class="fa fa-sign-in" style="padding-right:5px;"></i><?=lang("room_reservation") ?> </label> </button>
                        	<button class="btn tip btn-danger" data-original-title="<?=lang('room_blocking');?>"><label style="color:#fff;"><i class="fa fa-sign-in" style="padding-right:5px;"></i><?=lang("room_blocking") ?> </label></button> 
                        	
                        </div>
                    </div>
                    <div class="col-sm-1 hidden">
						<div class="form-group"> 

							<table style="margin-top:35px;">
								<tr>
									<td>
										<div style="background:#428BCA;width:100px;height:25px;padding-right:10px;"></div>
									</td>
									<td style="width:200px;">&nbsp;<?= lang('room_free'); ?></td>
								
									<td>
										<div style="background:#FF5454;width:100px;height:25px;padding-right:10px;"></div>
									</td>
									<td>&nbsp;<?= lang('room_occupied'); ?></td>
								
									<td>
										<div style="background:#BDEA74;width:100px;height:25px;padding-right:10px;"></div>
									</td>
									<td>&nbsp;<?= lang('room_reservation'); ?></td>

									<td>
										<div style="background:#BDEA74;width:100px;height:25px;padding-right:10px;"></div>
									</td>
									<td>&nbsp;<?= lang('room_reservation'); ?></td>
								</tr>
							</table>

						</div>
					</div>
					
				</div>
				<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="table-board">
						<div id="ajax_rooms"></div>
					</div>
				</div>

				<div class="col-sm-10 hidden" style="padding-right:0px;">
					<div style="width:100%; height:92vh; margin-top:30px; overflow-y:scroll;">
						<div id="ajax_rooms1"></div>
					</div>
				</div>

				<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="table-board">
						<div id="ajax_rooms1">Hello</div>
					</div>
				</div>

			</div>
		</div>
	</div>



	<style type="text/css">
	.table-board{
		margin-top:0px;
/*		background-color:#0a7b81 !important;*/
	}
	.table-pos{
		width:14.2%;
		height:142px;
		margin:0px;	
		padding:0px;
	}
	.table-pos ul{
		margin:0px;
		padding:0px;
	}
	.table-pos li{
		color:#FFF;
		font-size:12px;
		list-style:none;
		line-height:25px;
	}
	.table-pos .table_name{
		font-size:16px;
		text-shadow:0 0 10px #000000;
		font-weight:bold;
		color: #d6d7d8;
	}
</style>

	<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
	<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
	<div id="modal-loading" style="display: none;">
		<div class="blackbg"></div>
		<div class="loader"></div>
	</div>
	<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
	<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code); ?>
	<script type="text/javascript">
	var user_id = <?= $this->session->userdata('user_id') ?>,dt_lang = <?=$dt_lang?>, dp_lang = <?=$dp_lang?>, site = <?=json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats))?>;
	var lang = {paid: '<?=lang('paid');?>', expired: '<?=lang('expired');?>', assigned: '<?=lang('assigned');?>',cleared: '<?=lang('cleared');?>', approved: '<?=lang('approved');?>', rejected: '<?=lang('rejected');?>', pending: '<?=lang('pending');?>', completed: '<?=lang('completed');?>', ordered: '<?=lang('ordered');?>', received: '<?=lang('received');?>', partial: '<?=lang('partial');?>', sent: '<?=lang('sent');?>', r_u_sure: '<?=lang('r_u_sure');?>', due: '<?=lang('due');?>', returned: '<?=lang('returned');?>', transferring: '<?=lang('transferring');?>', active: '<?=lang('active');?>', inactive: '<?=lang('inactive');?>', unexpected_value: '<?=lang('unexpected_value');?>', select_above: '<?=lang('select_above');?>', download: '<?=lang('download');?>', active: '<?=lang('active');?>', inactive: '<?=lang('inactive');?>', payoff: '<?=lang('payoff');?>', pawn_rate: '<?=lang('pawn_rate');?>', pawn_received: '<?=lang('pawn_received');?>', pawn_sent: '<?=lang('pawn_sent');?>', closed: '<?=lang('closed');?>', yes: '<?=lang('yes');?>', no: '<?=lang('no');?>', morning: '<?=lang('morning');?>', afternoon: '<?=lang('afternoon');?>', full: '<?=lang('full');?>', freight: '<?=lang('freight');?>', packaging: '<?=lang('packaging');?>', take_away: '<?=lang('take_away');?>', fixed: '<?=lang('fixed');?>', difference: '<?=lang('difference');?>', checked_in: '<?=lang('checked_in');?>', checked_out: '<?=lang('checked_out');?>'};
	</script>
	<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/core.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
	<script type="text/javascript">
		$(function(){
			$("#rtwarehouse, #rtfloor").on("change",ajax_rooms); ajax_rooms();
			function ajax_rooms(){
				var warehouse = $("#rtwarehouse option:selected").val();
				var floor = $("#rtfloor option:selected").val();
				$.ajax({
					url : "<?= site_url("rentals/ajax_rooms") ?>",
					dataType : "JSON",
					data : {
						warehouse : warehouse,
						floor : floor,
					},
					success:function(result){
						$("#ajax_rooms").html(result);
					}
				})
			}
			var interval = setInterval(function() {
			var momentNow = moment();
				$('#date-part').html(momentNow.format('YYYY MMMM DD') + ' '
									+ momentNow.format('dddd')
									 .substring(0,3).toUpperCase());
				$('#time-part').html(momentNow.format('hh:mm:ss A'));
			}, 100);
		});
	</script>
	<style type="text/css">
		.box-room{
			width:235px;
			outline:1px solid #FFF;
			color:#FFF;
			height: 200px;
			margin: 10px !important;
			border-radius: 10px !important;
		}
		.box-room small{
			font-size:11px;
		}
		.btn {
    		display: inline-block;
		    padding: 4px 12px;
		    text-align: center;
		    white-space: nowrap;
		    vertical-align: middle;
		    cursor: pointer;
		    -webkit-user-select: none;
		    -moz-user-select: none;
		    -ms-user-select: none;
		    user-select: none;
		    background-image: none;
		    border: 1px solid transparent;
		    border-radius: 4px;
		 }
	</style>
</body>
</html>
