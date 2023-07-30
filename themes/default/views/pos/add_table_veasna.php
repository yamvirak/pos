<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=lang('pos_module') . " | " . $Settings->site_name;?></title>
    <script type="text/javascript">if(parent.frames.length !== 0){top.location = '<?=site_url('pos')?>';}</script>
    <base href="<?=base_url()?>"/>
	<?php if($pos_settings->pos_layout_fix==1){ ?>
	<meta name="viewport" content="user-scalable=no" />
	<?php } ?>
    <meta http-equiv="cache-control" content="max-age=0"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
    <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
	<link rel="stylesheet" href="<?=$assets?>styles/style.css" type="text/css"/>  
	<link rel="stylesheet" href="<?=$assets?>pos/css/posajax.css" type="text/css"/>  
    <script type="text/javascript" src="<?=$assets?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?=$assets?>js/jquery-migrate-1.2.1.min.js"></script>
</head>
<body  class="<?= (isset($_GET['bill_id'])?"bgray":"bwhite") ?>">
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
            <a class="navbar-brand" href="<?=site_url()?>">
				<span class="logo">
					<span class="pos-logo-lg"><?=$Settings->site_name?></span>					
				</span>
			</a>
            <div class="header-nav">
                <ul class="nav navbar-nav pull-right">
                    <li class="dropdown">
                        <a class="account dropdown-toggle" data-toggle="dropdown" href="#">
                            <div class="user">
                                <span><?=ucfirst($this->session->userdata('username'));?></span>
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
                        <a class="pos-tip" title="<?=lang('dashboard')?>" data-placement="bottom" href="<?=site_url('welcome')?>">
                            <i class="fa fa-dashboard"></i>
                        </a>
                    </li>
                </ul>
                <ul class="nav navbar-nav pull-right">					
                    <li class="dropdown">
                        <a class="btn bblack" style="cursor: default;">
							<span class="pos-logo-lg" style="font-size:23px;" id="time-part"></span>
						</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2">
				<div class="form-group">					
					<?php
						echo lang("customer", "customer");
						echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="pmcustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control pos-input-tip" style="width:100%;"');									
					?>
				</div>
				<div class="form-group">
					<?= lang("warehouse", "pmwarehouse"); ?>
					<?php
						$wh[''] = '';
						foreach ($warehouses as $warehouse) {
								$wh[$warehouse->id] = $warehouse->name;
						}
						echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="pmwarehouse" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
					?>
				</div>
				<div class="form-group">
					<?= lang("floor", "floor"); ?>
					<?php
						$fl[''] = lang('select').' '.lang('floor');
						foreach ($floors as $floor) {
							$fl[$floor->id] = $floor->floor;
						}
						echo form_dropdown('floor', $fl, (isset($_GET['floor']) ? $_GET['floor'] : $pos_settings->default_floor), 'id="pmfloor" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("floor") . '" required="required" style="width:100%;" ');
					?>
				</div>
			</div>
			<div class="col-sm-12 col-md-2 col-lg-10 col-xl-10">
				<div class="table-board">
					<div id="ajax_tables"></div>
				</div>
			</div>
			<?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos-sale-form'); echo form_open("pos/add_table", $attrib);?>
						<div id="hidesuspend"></div>				
			<?php echo form_close(); ?>
		</div>
    </div>
</div>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
<div id="modal-loading" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
<style type="text/css">
	.table-board{
		margin-top:30px;
		background:#0a7b81;
	}
	.table-pos{
		width:10%;
		height:100px;
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
		text-shadow:0 0 4px #999999;
		font-weight:bold;
	}
</style>
<script type="text/javascript">
	$(function(){
		localStorage.setItem('pmcustomer', "<?= $pos_settings->default_customer ?>");
		var $customer = $('#pmcustomer');						
		if (pmcustomer = localStorage.getItem('pmcustomer')) {
			$customer.val(pmcustomer).select2({
				minimumInputLength: 1,
				data: [],
				initSelection: function (element, callback) {
					$.ajax({
						type: "get", 
						async: false,
						url: "<?php echo site_url("customers/getCustomer/") ?>/" + $(element).val(),
						dataType: "json",
						success: function (data) {
							callback(data[0]);
						}
					});
				},
				ajax: {
					url: "<?php echo site_url("customers/suggestions") ?>",
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
		$("#pmwarehouse, #pmfloor").on("change",ajax_tables);
		ajax_tables();
		function ajax_tables(){			
			var warehouse_id = $("#pmwarehouse").val();
			var floor_id = $("#pmfloor").val();
			var bill_id = "<?= isset($_GET["bill_id"])? $_GET["bill_id"]:'' ?>";
			$('#modal-loading').show();
			$.ajax({
                    type: "GET",
                    url: "<?=site_url('pos/ajax_tables');?>",
					dataType: "json",
                    data: { 
						warehouse_id : warehouse_id,
						floor_id : floor_id,
						bill_id : bill_id,
					},                    
                    success: function (data) {
						$("#ajax_tables").html(data.tables);
                    }
			}).done(function () {						
				$('#modal-loading').hide();						
			});
		}
		$(document).on('click', '.add_suspend', function (event) {
			event.preventDefault();
			var table_id = $(this).attr("table_id");
			var table_name = $(this).attr("table_name");
			var bill_id = $(this).attr("bill_id");
			var warehouse_id = $("#pmwarehouse").val();
			var customer_id = $("#pmcustomer").val();
			if(!bill_id || bill_id == ''){					
				if (!customer_id || customer_id == '') {
					bootbox.alert('<?=lang('customer_selection');?>');
					return false;
				} else {
					suspend = $('<span></span>');
					suspend.html('<input type="hidden" name="table_id" value="'+table_id+'" /><input type="hidden" name="warehouse_id" value="'+warehouse_id+'" /><input type="hidden" name="customer_id" value="'+customer_id+'" />');
					suspend.appendTo("#hidesuspend");					
					$('#pos-sale-form').submit();
				}
			}else{
				window.location.href = "<?= site_url('pos/index') ?>/" + bill_id;
			}
			$(this).prop('disabled', true);
		});
		$('body').on('click', '.move_suspend', function() {
			event.preventDefault();
			var table_id = $(this).attr("table_id");
			var table_name = $(this).attr("table_name");
			var bill_id = $(this).attr("bill_id");
			var warehouse_id = $("#pmwarehouse").val();
			var customer_id = $("#pmcustomer").val();
			$.ajax({
				url  : "<?= site_url('pos/split_suspend') ?>",
				type : "GET",
				data : {
					delete_id : "<?php echo isset($_GET['bill_id'])? $_GET['bill_id']: 0; ?>",
					bill_id : bill_id,
					table_id : table_id,
				},
				success : function(data){
					$('#myModal').html(data);
					$('#myModal').modal('show');
				}
			});
			$(this).prop('disabled', true);
		});
		$("select").select2();
		setInterval(ajax_tables, 50000);
	});
</script>
</body>
</html>
