<?php defined('BASEPATH') OR exit('No direct script access allowed');?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <base href="<?= site_url() ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $Settings->site_name ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet"/>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
	<link rel="stylesheet" href="<?= $assets ?>multiselect/css/bootstrap-multiselect.css" type="text/css">
	<script type="text/javascript" src="<?= $assets ?>multiselect/js/bootstrap-multiselect.js"></script>

    <!--[if lt IE 9]>
    <script src="<?= $assets ?>js/jquery.js"></script>
    <![endif]-->
    <noscript><style type="text/css">#loading { display: none; }</style></noscript>
    <?php if ($Settings->user_rtl) { ?>
        <link href="<?= $assets ?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet"/>
        <link href="<?= $assets ?>styles/style-rtl.css" rel="stylesheet"/>
        
        <script type="text/javascript">
            $(document).ready(function () { $('.pull-right, .pull-left').addClass('flip'); });
        </script>
    <?php } ?>
    <script type="text/javascript">
        $(window).load(function () {
            $("#loading").fadeOut("slow");
        });
    </script>
</head>
<body>
    <?php
		$payment_alert_num = $this->site->getAlertCustomerpayments();
		$downpayment_alert_num = $this->site->getAlertDownPayments();
		$missed_payment_alert_num = $this->site->getAlertInstallmentMissedRepayments();
		$rental_payment_alert_num = $this->site->getAlertPaymentRentals();
		$repair_receive_alert_num = $this->site->getAlertRepairReceives();
		if(!$this->config->item("loan")){
			$missed_payment_alert_num = 0;
		}
		if(!$this->config->item("room_rent")){
			$rental_payment_alert_num = 0;
		}
		if(!$this->config->item("repair")){
			$repair_receive_alert_num = 0;
		}
		if(!$this->config->item("down_payment")){
			$downpayment_alert_num = 0;
		}

		if($Settings->product_license == 1){
			$license_alert_num = $this->site->getAlertProductLicense();
		}else{
			$license_alert_num = 0;
		}
    ?>
<noscript>
    <div class="global-site-notice noscript">
        <div class="notice-inner">
            <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                your browser to utilize the functionality of this website.</p>
        </div>
    </div>
</noscript>
<div id="loading"></div>

<?php
	if($this->session->userdata('cus_style')) {
		setcookie("cuscen_style", $this->session->userdata('cus_style'));	
	}
	if($this->session->userdata('style_view')) {
		$style_view = $this->session->userdata('style_view');
	}else{
		$style_view = 'standard_view';
	}
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){ 
		$style_view = 'standard_view';
	}
?>

<div id="app_wrapper">
	<?php if($style_view=='standard_view' || $this->session->userdata('customer_login') == true){ ?>
	<header id="header" class="navbar">
        <div class="container header_fonts">

        	<a class="navbar-brand" href="<?= site_url() ?>"><span class="logo"><?= $Settings->site_name ?></span></a>
				<div class="btn-group visible-xs pull-right btn-visible-sm">
					<button class="navbar-toggle btn" type="button" data-toggle="collapse" data-target="#sidebar_menu">
						<span class="fa fa-bars"></span>
					</button>
					<a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>" class="btn">
						<span class="fa fa-user"></span>
					</a>
					<a href="<?= site_url('logout'); ?>" class="btn">
						<span class="fa fa-sign-out"></span>
					</a>
				</div>
				
            <div class="navbar navbar-fixed-top collapse navbar-collapse js-navbar-collapse">
			<div class="navbar-brand">
				 <span style="padding: 13px;display: block;float: left;"><a href="#" id="main-menu-act"><i class="icon fa fa-tasks tip white"></i></a></span>
                <?php if ($Settings->logo2) {
                    echo '<a href="'.site_url().'"><img src="' . base_url('assets/uploads/logos/' . $Settings->logo) . '" alt="' . $Settings->site_name . '" style="width: 150px;" /></a>';
                } ?>
            </div>

				<ul class="nav navbar-nav pull-right">

					<li class="dropdown hidden-sm top_header_menu">
						<a class="tip white" title="<?= lang('pos') ?>" data-placement="bottom" href="<?= site_url('pos') ?>">
							<i class="fa fa-th-large"></i> <?= lang('pos') ?>
						</a>
					</li>

					<?php if($this->config->item('room_rent')){  ?>
					<li class="dropdown hidden-sm top_header_menu">
						<a href="<?= site_url('rentals/view_rooms') ?>" class="tip white" title="<?= lang('view_room') ?>" data-placement="bottom">
							<i class="icon fa fa-television"></i> <?= lang('view_room') ?>
						</a>
					</li>
					<?php } ?>
					<li class="dropdown hidden-sm top_header_menu">
						<a href="#" onclick="showReport()" class="tip white" title="<?= lang('reports') ?>" data-placement="bottom">
							<i class="fa fa-line-chart"></i> <?= lang('reports') ?>
						</a>
					</li>
					
					<li class="dropdown hidden-sm top_header_menu">
						<a class="tip white" title="<?= lang('configurations') ?>" data-placement="bottom" href="<?= site_url('pos/settings') ?>">
							<i class="fa fa-cogs"></i> <?= lang('configurations') ?>
						</a>
					</li>
					<li class="dropdown hidden-sm top_header_menu">
						<a class="tip white" title="<?= lang('systems') ?>" data-placement="bottom" href="<?= site_url('system_settings') ?>">
							<i class="fa fa-sitemap"></i> <?= lang('systems') ?>
						</a>
					</li>
					<li class="dropdown hidden-xs">
						<a class="btn tip white" title="<?=lang('clear_ls')?>" data-placement="bottom" id="clearLS" href="#">
							<i class="fa fa-eraser"></i>
						</a>
					</li>
					<li class="dropdown hidden-xs">
						<a class="tip" title="<?= lang('language') ?>" data-placement="bottom" data-toggle="dropdown"
						   href="#">
							<img src="<?= base_url('assets/images/' . $Settings->user_language . '.png'); ?>" alt="">
						</a>
						<ul class="dropdown-menu pull-right">
							<?php $scanned_lang_dir = array_map(function ($path) {
								return basename($path);
							}, glob(APPPATH . 'language/*', GLOB_ONLYDIR));
							foreach ($scanned_lang_dir as $entry) { 
								if($entry == 'simplified-chinese'  || $entry == 'thai'){
								}else{
								?>
								<li>
									<a href="<?= site_url('welcome/language/' . $entry); ?>">
										<img src="<?= base_url(); ?>assets/images/<?= $entry; ?>.png" class="language-img"> 
										&nbsp;&nbsp;<?= ucwords($entry); ?>
									</a>
								</li>
							<?php } }  ?> 
							<li class="divider"></li>

							<li>
								<a href="<?= site_url('welcome/toggle_rtl') ?>">
									<i class="fa fa-align-<?=$Settings->user_rtl ? 'right' : 'left';?>"></i>
									<?= lang('toggle_alignment') ?>
								</a>
							</li>
						</ul>
					</li>
					<li class="dropdown hidden-sm">
                        <a class="btn tip white" title="<?= lang('styles') ?>" data-placement="bottom" data-toggle="dropdown"
                           href="#">
                            <i class="fa fa-css3"></i>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <li class="bwhite noPadding">
                                <a href="#" id="fixed">
		                            <i class="fa fa-angle-double-left"></i>
		                            <span id="fixedText">Fixed</span>
		                        </a>
                                <a href="#" id="cssBlack" class="black">
                                   <i class="fa fa-stop"></i> Black
                               </a>
                           </li>
                        </ul>
                    </li>
                    <?php if (($Owner || $Admin || $GP['purchases-payments'] || $GP['reports-quantity_alerts'] || $GP['installments-payments'] || $GP['sales-payments'] ||  $GP['so-num_alerts'] || $GP['po-num_alerts'] || $GP['reports-expiry_alerts'] || $GP['reports-product_license_alerts']) && ($license_alert_num > 0 || $qty_alert_num > 0 || $exp_alert_num > 0 || $payment_purchase_alert_num > 0 || $payment_alert_num > 0 || $sale_order_num > 0 || $purchase_order_num > 0 || $loan_alert_term_num > 0 || $downpayment_alert_num > 0 || $missed_payment_alert_num || $rental_payment_alert_num || $repair_receive_alert_num)) { ?>
							<li class="dropdown hidden-xs">
								<a class="btn tip white" title="<?= lang('alerts') ?>" 
									data-placement="left" data-toggle="dropdown" href="#">
									<i class="fa fa-exclamation-triangle"></i>
									<span class="number bred_alert white" id="alert-show">0</span>
								</a>
								<ul class="dropdown-menu pull-right">
									
									<?php if($this->config->item('pawn') && $pawn_alert_num > 0){  ?>
										<li>
											<a href="<?= site_url('pawns/index/0/0/due') ?>" class="">
												<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $pawn_alert_num; ?></span>
												<span style="padding-right: 35px;"><?= lang('pawn_payment_alert') ?></span>
											</a>
										</li>
									<?php } ?>
								
									<?php if (($Owner || $Admin || $GP['purchases-payments']) && $payment_purchase_alert_num > 0){ ?>
									<li>
										<a href="<?= site_url('purchases/index/0/0/due') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $payment_purchase_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('purchase_payment_alert') ?></span>
										</a>
									</li>
									<?php } if (($Owner || $Admin || $GP['sales-payments']) && $payment_alert_num > 0){ ?>
									<li>
										<a href="<?= site_url('sales/index/0/0/due') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $payment_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('sale_payment_alert') ?></span>
										</a>
									</li>

									<li>
										<a href="<?= site_url('installments/index/0/0/active') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $loan_alert_term_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('loan_alert_term') ?></span>
										</a>
									</li>

									<?php } if ($this->config->item('down_payment') && ($Owner || $Admin || $GP['sales-payments']) && $downpayment_alert_num > 0){ ?>
									<li>
										<a href="<?= site_url('sales/missed_repayments') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $downpayment_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('sale_down_payment_alert') ?></span>
										</a>
									</li>
									<?php } if ( ($Owner || $Admin || $GP['installments-payments']) && $missed_payment_alert_num > 0){ ?>
									<li>
										<a href="<?= site_url('installments/missed_repayments') ?>">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $missed_payment_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('missed_repayment_alerts') ?></span>
										</a>
									</li>
									<?php } if ( ($Owner || $Admin || $GP['rentals-index']) && $rental_payment_alert_num > 0){ ?>
									<li>
										<a href="<?= site_url('rentals/index/0/0/due') ?>">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $rental_payment_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('rental_payment_alerts') ?></span>
										</a>
									</li>
									<?php } if ( ($Owner || $Admin || $GP['repairs-index']) && $repair_receive_alert_num > 0){ ?>
									<li>
										<a href="<?= site_url('repairs/index/0/0/receive') ?>">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $repair_receive_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('repair_receive_alerts') ?></span>
										</a>
									</li>
									<?php } if (($Owner || $Admin || $GP['reports-quantity_alerts']) && $qty_alert_num > 0){ ?>								
									<li>
										<a href="<?= site_url('reports/quantity_alerts') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $qty_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('quantity_alerts') ?></span>
										</a>
									</li>
									<?php 
									} if(($Owner || $Admin || $GP['reports-quantity_alerts']) && $Settings->alert_qty_by_warehouse == 1){
											$qty_alert_warehouses = '';
											if ($Settings->alert_qty_by_warehouse == 1){
												$warehouse_alerts = $this->site->get_total_qty_alert_warehouses();
												if($warehouse_alerts){
													foreach($warehouse_alerts as $warehouse_alert){
														$qty_alert_warehouses .= '
																					<li>
																						<a href="'.site_url('reports/quantity_alerts/'.$warehouse_alert->id).'" class="">
																							<span class="label label-danger pull-right alert-no" style="margin-top:3px;">'.$warehouse_alert->alert_qty.'</span>
																							<span style="padding-right: 35px;">'.lang('quantity_alerts').' ( '.$warehouse_alert->warehouse.' )</span>
																						</a>
																					</li>
																				';
													}
												}
											}
											echo $qty_alert_warehouses;
									} if ($Settings->product_expiry && ($Owner || $Admin || $GP['reports-quantity_alerts'])) { ?>
									<li>
										<a href="<?= site_url('reports/expiry_alerts') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $exp_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('expiry_alerts') ?></span>
										</a>
									</li>
									<?php } if (($Owner || $Admin || $GP['reports-product_license_alerts']) && $license_alert_num > 0) {?>
									<li>
										<a href="<?= site_url('reports/product_license_alerts') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $license_alert_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('license_alerts') ?></span>
										</a>
									</li>
									<?php } if (($Owner || $Admin || $GP['so-num_alerts']) && $sale_order_num) {?>
									<li>
										<a href="<?= site_url('sale_orders?status=pending') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $sale_order_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('so_alerts') ?></span>
										</a>
									</li>
									<?php } if (($Owner || $Admin || $GP['po-num_alerts']) && $purchase_order_num) { ?>
									<li>
										<a href="<?= site_url('purchase_orders/?status=pending') ?>" class="">
											<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $purchase_order_num; ?></span>
											<span style="padding-right: 35px;"><?= lang('po_alerts') ?></span>
										</a>
									</li>
									<?php } 
									$alert_delivery = $this->site->getPendingDelivery();
									if (($Owner || $Admin || $GP['sales-add']) && $alert_delivery) { ?>
										<li>
											<a href="<?= site_url('deliveries/?status=pending') ?>" class="">
												<span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $alert_delivery; ?></span>
												<span style="padding-right: 35px;"><?= lang('delivery_alerts') ?></span>
											</a>
										</li>
									<?php } ?>
								</ul>
							</li>
						<?php } ?>

						<script type="text/javascript">
							$(function(){
								var show = 0;
								$(".alert-no").each(function(){
									show += $(this).text() - 0;
								});
								$("#alert-show").text(show);
							});
						</script>

                    <div class="dropdown hidden-sm">
	                    <li class="dropdown">
	                        <img class="account hidden" alt="" src="<?=$this->session->userdata('avatar') ? site_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/' . $this->session->userdata('gender') . '.png';?>" class="mini_avatar img-rounded" style="border-radius: 50px;width: 48px;">

	                         <a class="white btn tip" data-toggle="dropdown" href="#" style="font-size: 16px;text-transform: none;">
	                            <span><?=lang('welcome')?>! <?=$this->session->userdata('username');?></span>
	                        </a>


	                        <ul class="dropdown-menu pull-right" style="font-size: 16px;text-transform: none;">
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
	                </div>
				</ul>
			</div>
            </div>
        </div>
    </header>

			<img id="img1"/>
			<img id="img2"/>
			<div id="div1"/>
			<div id="div2"/>
			<div id="div3"/>
			<div id="div4"/>

			<script type="text/javascript">
				$("#img1").on('click', function() {
				   $(this).hide();
				   $("#div2, #div3, #div4").hide();
				   $("#div1").show();
				});
			</script>



			<script type="text/javascript">
				
				function showDistribution() {
				   document.getElementById('welcomeDistribution').style.display = "block";
				   document.getElementById('welcomeDistributionPurchase').style.display = "block";
				   document.getElementById('welcomeDistributionPeople').style.display = "block";
				   document.getElementById('welcomeInventory').style.display = "block";
				   document.getElementById('welcomeHospitality').style.display = "none";
				   document.getElementById('welcomeFinance').style.display = "none";
				   document.getElementById('welcomeReport').style.display = "none";
				   document.getElementById('welcomeSystem').style.display = "none";
				   
				}
				function showFinance() {
				   document.getElementById('welcomeFinance').style.display = "block";
				   document.getElementById('welcomeDistribution').style.display = "none";
				   document.getElementById('welcomeDistributionPurchase').style.display = "none";
				   document.getElementById('welcomeDistributionPeople').style.display = "none";
				   document.getElementById('welcomeHospitality').style.display = "none";
				   document.getElementById('welcomeInventory').style.display = "none";
				}
				function showHospitality() {
				   document.getElementById('welcomeHospitality').style.display = "block";
				   document.getElementById('welcomeInventory').style.display = "none";
				   document.getElementById('welcomeDistribution').style.display = "none";
				   document.getElementById('welcomeDistributionPurchase').style.display = "none";
				   document.getElementById('welcomeDistributionPeople').style.display = "none";
				   document.getElementById('welcomeReport').style.display = "none";
				   document.getElementById('welcomeFinance').style.display = "none";
				   document.getElementById('welcomeSystem').style.display = "none";
				}
				
				function showReport() {
				   document.getElementById('welcomeReport').style.display = "block";
				   document.getElementById('welcomeDistribution').style.display = "none";
				   document.getElementById('welcomeDistributionPurchase').style.display = "none";
				   document.getElementById('welcomeDistributionPeople').style.display = "none";
				   document.getElementById('welcomeHospitality').style.display = "none";
				   document.getElementById('welcomeInventory').style.display = "none";
				   document.getElementById('welcomeSystem').style.display = "none";
				   document.getElementById('welcomeFinance').style.display = "none";
				}
				function showConfiguration() {
				   	document.getElementById('welcomeConfiguration').style.display = "block";
				   	document.getElementById('welcomeInventory').style.display = "none";
				   	document.getElementById('welcomeHospitality').style.display = "none";
				   	document.getElementById('welcomeDistribution').style.display = "none";
				  	document.getElementById('welcomeDistributionPurchase').style.display = "none";
				   	document.getElementById('welcomeDistributionPeople').style.display = "none";
				}
				function showSystem() {
				   document.getElementById('welcomeSystem').style.display = "block";
				   document.getElementById('welcomeDistribution').style.display = "none";
				   document.getElementById('welcomeDistributionPurchase').style.display = "none";
				   document.getElementById('welcomeDistributionPeople').style.display = "none";
				   document.getElementById('welcomeHospitality').style.display = "none";
				   document.getElementById('welcomeInventory').style.display = "none";
				   document.getElementById('welcomeFinance').style.display = "none";
				   document.getElementById('welcomeReport').style.display = "none";
				}
				
			</script>
	<div class="container tab-content" id="container">
        <div class="row" id="main-con">
        <table class="lt"><tr><td class="sidebar-con">
			<div id="sidebar-left">
				<div class="sidebar-nav nav-collapse collapse navbar-collapse navbar-fixed" id="sidebar_menu">
					<div class="special_space"></div>
					<ul class="nav main-menu">
					<?php if ($this->config->item('inventory') && ($Owner || $Admin || $GP['products-consignments'] || $GP['products-index'] || $GP['products-add'] || $GP['products-barcode'] || $GP['products-adjustments'] || $GP['products-stock_count'] || $GP['products-converts'])) { ?>
								
						<li class="mm_products" id="welcomeInventory">
							<a class="dropmenu" href="#">
								<span class="chevron"></span>
								<i class="fa fa-barcode"></i>
								<span class="text"> <?= lang('inventory'); ?> 
								</span>
							</a>
							<ul>
							<?php if ($Owner || $Admin || $GP['products-index']) { ?>
								<li id="products_index">
									<a class="submenu" href="<?= site_url('products'); ?>">
										<i class="fa fa-arrow-circle-o-right"></i><span class="text"> <?= lang('products'); ?></span>
									</a>
								</li>
								<li id="products_non_stock">
									<a class="submenu" href="<?= site_url('products/non_stock'); ?>">
										<i class="fa fa-arrow-circle-o-right"></i><span class="text"> <?= lang('non_stock'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['products-import']) { ?>
								<li id="products_import_csv">
									<a class="submenu" href="<?= site_url('products/import_csv'); ?>">
										<i class="fa fa-arrow-circle-o-right"></i>
										<span class="text"> <?= lang('import_products'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['products-stock_count']) && $this->config->item('stock_counts')) { ?>
								<li id="products_stock_counts">
									<a class="submenu" href="<?= site_url('products/stock_counts'); ?>">
										<i class="fa fa-th-list"></i>
										<span class="text"> <?= lang('stock_counts'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['products-barcode']) && $this->config->item('print_barcodes')) { ?>
								<li id="products_print_barcodes">
									<a class="submenu" href="<?= site_url('products/print_barcodes'); ?>">
										<i class="fa fa-arrow-circle-o-right"></i><span class="text"> <?= lang('print_barcode_label'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['products-using_stocks']) && $this->config->item('using_stocks') ) {?>
									<li id="products_using_stocks">
										<a class="submenu" href="<?= site_url('products/using_stocks'); ?>">
											<i class="fa fa-filter"></i>
											<span class="text"> <?= lang('using_stocks'); ?></span>
										</a>
									</li>
							<?php } if ($Owner || $Admin || $GP['products-adjustments']) { ?>
								<li id="products_quantity_adjustments">
									<a class="submenu" href="<?= site_url('products/quantity_adjustments'); ?>">
										<i class="fa fa-filter"></i><span class="text"> <?= lang('quantity_adjustments'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || ($GP['products-cost'] && $GP['products-cost_adjustments'])) && $Settings->accounting=='1') { ?>
								<li id="products_cost_adjustments">
									<a class="submenu" href="<?= site_url('products/cost_adjustments'); ?>">
										<i class="fa fa-money"></i>
										<span class="text"> <?= lang('cost_adjustments'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['products-consignments']) && $this->config->item('consignments')) { ?>		
								<li id="products_consignments">
									<a class="submenu" href="<?= site_url('products/consignments'); ?>">
										<i class="fa fa-list-ol"></i>
										<span class="text"> <?= lang('consignments'); ?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>

						<li class="mm_converts mm_converts mm_convert_report mm_convert_detail">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-industry"></i>
                                    <span class="text"> <?= lang('manufacturing'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
								<?php } if (($Owner || $Admin || $GP['products-converts']) && $this->config->item('convert')) { ?>
								<li id="converts_index">
									<a class="submenu" href="<?= site_url('converts/'); ?>">
										<i class="fa fa-list-ol"></i>
										<span class=""> <?= lang('converts'); ?></span>
									</a>
								</li>
                          
								<?php } if ($this->config->item('inventory') && ($Owner || $Admin || $GP['boms-boms']) && $this->config->item('convert')){ ?>		
								<li id="converts_convert_report">
									<a class="submenu" href="<?= site_url('converts/convert_report'); ?>">
										<i class="fa fa-list-ol"></i>
										<span class="text"> <?= lang('convert_report'); ?></span>
									</a>
								</li>
								<?php } if ($this->config->item('inventory') && ($Owner || $Admin || $GP['boms-boms']) && $this->config->item('convert')){ ?>		
								<li id="converts_convert_detail">
									<a class="submenu" href="<?= site_url('converts/convert_detail'); ?>">
										<i class="fa fa-list-ol"></i>
										<span class="text"> <?= lang('convert_detail'); ?></span>
									</a>
								</li>
								          
								<?php } if ($this->config->item('inventory') && ($Owner || $Admin || $GP['boms-boms']) && $this->config->item('convert')){ ?>		
								<li id="converts_boms">
									<a class="submenu" href="<?= site_url('converts/boms'); ?>">
										<i class="fa fa-list-ol"></i>
										<span class="text"> <?= lang('boms'); ?></span>
									</a>
								</li>
                                </ul>
                            </li>

						
						<?php  } if($this->config->item('transfer')) {?>
                            <li class="mm_transfers">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-star-o"></i>
                                    <span class="text"> <?= lang('transfers'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                	 <li id="transfers_add">
                                        <a class="submenu" href="<?= site_url('transfers/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer'); ?></span>
                                        </a>
                                    </li>
                                    
                                   	<li id="transfers_index">
                                        <a class="submenu" href="<?= site_url('transfers'); ?>">
                                            <i class="fa fa-star-o"></i><span class="text"> <?= lang('list_transfers'); ?></span>
                                        </a>
                                    </li>
                                
                                </ul>
                            </li>
                        <?php } ?>

						<?php if ($this->config->item('repair') && ($Owner || $Admin || $GP['repairs-index'] || $GP['repairs-checks'] || $GP['repairs-problems'] || $GP['repairs-items'])) { ?>
							<li class="mm_repairs">
								<a class="dropmenu" href="#">
									<i class="fa fa-heart"></i>
									<span class="text"> <?= lang('repairs'); ?> 
									</span> <span class="chevron closed"></span>
								</a>
								<ul>
									<?php if (($Owner || $Admin || $GP['repairs-index']) && $this->config->item('repair')) { ?>
										<li id="repairs_index">
											<a class="submenu" href="<?= site_url('repairs'); ?>">
												<i class="fa fa-magnet"></i><span class="text"> <?= lang('repairs'); ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if (($Owner || $Admin || $GP['repairs-items']) && $this->config->item('repair')) { ?>
										<li id="repairs_items">
											<a class="submenu" href="<?= site_url('repairs/items'); ?>">
												<i class="fa fa-retweet"></i><span class="text"> <?= lang('repair_items'); ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if (($Owner || $Admin || $GP['repairs-checks']) && $this->config->item('repair')) { ?>
										<li id="repairs_checks">
											<a class="submenu" href="<?= site_url('repairs/checks'); ?>">
												<i class="fa fa-bolt"></i>
												<span class="text"> <?= lang('checks'); ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if (($Owner || $Admin || $GP['repairs-problems']) && $this->config->item('repair')) { ?>
										<li id="repairs_problems">
											<a class="submenu" href="<?= site_url('repairs/problems'); ?>">
												<i class="fa fa-warning"></i>
												<span class="text"> <?= lang('problems'); ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if (($Owner || $Admin || $GP['repairs-diagnostics']) && $this->config->item('repair')) { ?>
										<li id="repairs_diagnostics">
											<a class="submenu" href="<?= site_url('repairs/diagnostics'); ?>">
												<i class="fa fa-h-square"></i>
												<span class="text"> <?= lang('diagnostics'); ?></span>
											</a>
										</li>
									<?php }if (($Owner || $Admin || $GP['repairs-machine_types']) && $this->config->item('repair')) { ?>
										<li id="repairs_machine_types">
											<a class="submenu" href="<?= site_url('repairs/machine_types'); ?>">
												<i class="fa fa-th-list"></i>
												<span class="text"> <?= lang('machine_types'); ?></span>
											</a>
										</li>
									<?php } ?>
								</ul>
							</li>

					<?php }if ($this->config->item('room_rent') && ($Owner || $Admin || $GP['rentals-index'])) { ?>
						<li class="mm_rentals mm_rentals_check_in tab-pane fade in" id="welcomeHospitality">
							<a class="dropmenu" href="#">
								<i class="icon fa fa-television"></i>
								<span class="text"> <?= lang('front_desk'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
								<?php if (($Owner || $Admin || $GP['rentals-index']) && $this->config->item('room_rent')) { ?>

									<li id="rentals_add">
										<a class="submenu" href="<?= site_url('rentals/add'); ?>">
											<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_rental'); ?></span>
										</a>
									</li>

									
									<li id="rentals_check_in_add">
										<a class="submenu" href="<?= site_url('rentals_check_in/add'); ?>">
											<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_daily_check_in'); ?></span>
										</a>
									</li>
									<li id="rentals_check_in_index">
										<a class="submenu" href="<?= site_url('rentals_check_in'); ?>">
											<i class="fa fa-sign-in"></i><span class="text"> <?= lang('checked_in_list'); ?></span>
										</a>
									</li>
									<li id="rentals_reservations">
										<a class="submenu" href="<?= site_url('rentals/reservations'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('reservation_list'); ?></span>
										</a>
									</li>

									<li id="rentals_rental_check_out">
										<a class="submenu" href="<?= site_url('rentals/rental_check_out'); ?>">
											<i class="fa fa-sign-out"></i><span class="text"> <?= lang('checked_out_list'); ?></span>
										</a>
									</li>
									<li id="rentals_index">
										<a class="submenu" href="<?= site_url('rentals'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('rentals'); ?></span>
										</a>
									</li>
									<li id="rentals_cancelled_reservation_list">
										<a class="submenu" href="<?= site_url('rentals/cancelled_reservation_list'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('cancelled_reservation_list'); ?></span>
										</a>
									</li>
									<li id="rentals_rental_booking_tracker">
										<a class="submenu" href="<?= site_url('rentals/rental_booking_tracker'); ?>">
											<i class="fa fa-money"></i><span class="text"> <?= lang('booking_deposit_tracker'); ?></span>
										</a>
									</li>
									<?php } ?>
								
							</ul>
						</li>

						<li class="mm_rentals_housekeeping1">
							<a class="dropmenu" href="#">
								<i class="fa fa-bed"></i>
								<span class="text"> <?= lang('channel_manager'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
								<?php if (($Owner || $Admin || $GP['rentals-rooms']) && $this->config->item('room_rent')) { ?>
									<li id="rentals_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('update_rate'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping_today_room_status">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('update_room'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping_today_room_status">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('live_booking'); ?></span>
										</a>
									</li>
								<?php } ?>
							</ul>
						</li>
						<li class="hidden">
							<a class="dropmenu" href="#">
								<i class="fa fa-bed"></i>
								<span class="text"> <?= lang('otas'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
								<?php if (($Owner || $Admin || $GP['rentals-rooms']) && $this->config->item('room_rent')) { ?>
									<li id="rentals_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('OTA Booking'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('OTA Invoice'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('OTA Content'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('OTA Commission'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('OTA Disparity'); ?></span>
										</a>
									</li>
								<?php } ?>
							</ul>
						</li>
						<li class="mm_rentals_housekeeping">
							<a class="dropmenu" href="#">
								<i class="fa fa-bed"></i>
								<span class="text"> <?= lang('housekeeping'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
								<?php if (($Owner || $Admin || $GP['rentals-rooms']) && $this->config->item('room_rent')) { ?>

									<li id="rentals_housekeeping_index">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('housekeeping_list'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping_index_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index_housekeeping'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('today_room_status'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping_view_rooms">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/view_rooms'); ?>">
											<i class="icon fa fa-television"></i><span class="text"> <?= lang('view_rooms'); ?></span>
										</a>
									</li>
									
								<?php } ?>
							</ul>
						</li>
						<li class="">
							<a class="dropmenu" href="#">
								<i class="fa fa-cog"></i>
								<span class="text"> <?= lang('booking_engine'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
								<?php if (($Owner || $Admin || $GP['rentals-rooms']) && $this->config->item('room_rent')) { ?>
									<li id="rentals_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('Live Link'); ?></span>
										</a>
									</li>
									<li id="rentals_housekeeping">
										<a class="submenu" href="<?= site_url('rentals_housekeeping/index'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('Website Config'); ?></span>
										</a>
									</li>
								<?php } ?>
							</ul>
						</li>

						<li class="mm_rentals_configuration">
							<a class="dropmenu" href="#">
								<i class="fa fa-cogs"></i>
								<span class="text"> <?= lang('hotel_configuration'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
								<?php if (($Owner || $Admin || $GP['rentals-rooms']) && $this->config->item('room_rent')) { ?>
									<li id="rentals_configuration_rooms">
										<a class="submenu" href="<?= site_url('rentals_configuration/rooms'); ?>">
											<i class="fa fa-inbox"></i><span class="text"> <?= lang('rooms'); ?></span>
										</a>
									</li>
									<li id="rentals_configuration_housekeeping_status">
										<a class="submenu" href="<?= site_url('rentals_configuration/housekeeping_status'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('housekeeping_status'); ?></span>
										</a>
									</li>
									<li id="rentals_configuration_room_types">
										<a class="submenu" href="<?= site_url('rentals_configuration/room_types'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('room_types'); ?></span>
										</a>
									</li>
								<?php }if (($Owner || $Admin || $GP['rentals-floors']) && $this->config->item('room_rent')) { ?>
									<li id="rentals_configuration_floors">
										<a class="submenu" href="<?= site_url('rentals_configuration/floors'); ?>">
											<i class="fa fa-building"></i><span class="text"> <?= lang('floors'); ?></span>
										</a>
									</li>
									<li id="rentals_configuration_service_types">
										<a class="submenu" href="<?= site_url('rentals_configuration/service_types'); ?>">
											<i class="fa fa-list-alt"></i><span class="text"> <?= lang('service_type'); ?></span>
										</a>
									</li>
									<li id="rentals_configuration_services">
										<a class="submenu" href="<?= site_url('rentals_configuration/services'); ?>">
											<i class="fa fa-list-alt"></i><span class="text"> <?= lang('services'); ?></span>
										</a>
									</li>
									<li id="rentals_configuration_room_rates">
										<a class="submenu" href="<?= site_url('rentals_configuration/room_rates'); ?>">
											<i class="fa fa-bed"></i><span class="text"> <?= lang('room_rates'); ?></span>
										</a>
									</li>
								<?php } ?>
							</ul>
						</li>
					<?php } if ($this->config->item('sale') && ($Owner || $Admin || $GP['sales-receive_payments'] || $GP['sales-salesman_commissions'] || $GP['pos-index'] || $GP['sales-index'] || $GP['sales-fuel_sale-index'] || $GP['sales-deliveries'] || $GP['sales-gift_cards'] || $GP['sales-member_cards'] || $GP['quotes-index']  || $GP['sale_orders-index'])) { ?>
						<li id="welcomeDistribution" class="mm_sales  mm_deliveries mm_quotes mm_sale_orders <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
							<a class="dropmenu" href="#">
								<i class="fa fa-line-chart"></i>
								<span class="text"> <?= lang('sale'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
							<?php if (($Owner || $Admin || $GP['quotes-index']) && $this->config->item('quotation')) { ?>
								<li id="quotes_index">
									<a class="submenu" href="<?= site_url('quotes'); ?>">
										<i class="fa fa-heart-o"></i><span class="text"> <?= lang('quotes'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['sale_orders-index']) &&  $this->config->item('saleorder')) { ?>
								<li id="sale_orders_index">
									<a class="submenu" href="<?= site_url('sale_orders'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('sale_orders'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['sales-index']) && $this->config->item('list_sales')) { ?>
								<li id="sales_index">
									<a class="submenu" href="<?= site_url('sales'); ?>">
										<i class="fa fa-heart"></i><span class="text"> <?= lang('sales'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['pos-index']) && $this->config->item('sale_pos') && POS) { ?>
								<li id="pos_sales">
									<a class="submenu" href="<?= site_url('pos/sales'); ?>">
										<i class="fa fa-heart"></i><span class="text"> <?= lang('pos_sales'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['sales-deliveries']) && $this->config->item('deliveries')) { ?>
								<li id="deliveries_index">
									<a class="submenu" href="<?= site_url('deliveries'); ?>">
										<i class="fa fa-truck"></i><span class="text"> <?= lang('deliveries'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['sales-return_sales']) { ?>
								<li id="sales_returns">
									<a class="submenu" href="<?= site_url('sales/returns'); ?>">
										<i class="fa fa-heart"></i>
										<span class="text"> <?= lang('sale_returns'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['sales-fuel_sale-index']) && $this->config->item('fuel')) { ?>
								<li id="sales_fuel_customers">
									<a class="submenu" href="<?= site_url('sales/fuel_customers'); ?>">
										<i class="fa fa-heart"></i>
										<span class="text"> <?= lang('fuel_customers'); ?></span>
									</a>
								</li>
								<li id="sales_fuel_sales">
									<a class="submenu" href="<?= site_url('sales/fuel_sales'); ?>">
										<i class="fa fa-heart"></i>
										<span class="text"> <?= lang('fuel_sales'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['sales-index']) && $this->config->item('concretes')) { ?>
								<li id="sales_sale_concretes">
									<a class="submenu" href="<?= site_url('sales/sale_concretes'); ?>">
										<i class="fa fa-heart"></i><span class="text"> <?= lang('sale_concretes'); ?></span>
									</a>
								</li>	
							<?php } if(($Owner || $Admin || $GP['sales-agency_commission-index']) && $this->config->item('agency')){?>
								<li id="sales_agency_commission">
									<a class="submenu" href="<?= site_url('sales/agency_commission'); ?>">
										<i class="fa fa-heart"></i>
										<span class="text"> <?= lang('agency_commissions'); ?></span>
									</a>
								</li>
							<?php }  if (($Owner || $Admin || $GP['sales-gift_cards']) && $this->config->item('gift_card')) { ?>
								<li id="sales_gift_cards">
									<a class="submenu" href="<?= site_url('sales/gift_cards'); ?>">
										<i class="fa fa-gift"></i><span class="text"> <?= lang('gift_cards'); ?></span>
									</a>
								</li>
							<?php }  if (($Owner || $Admin || $GP['sales-member_cards']) && $this->config->item('member_card')) { ?>
								<li id="sales_member_cards">
									<a class="submenu" href="<?= site_url('sales/member_cards'); ?>">
										<i class="fa fa-gift"></i><span class="text"> <?= lang('member_cards'); ?></span>
									</a>
								</li>
							<?php }  if (($Owner || $Admin || $GP['sales-salesman_commissions']) && $this->config->item('saleman_commission')) { ?>
								<li id="sales_salesman_commissions">
									<a class="submenu" href="<?= site_url('sales/salesman_commissions'); ?>">
										<i class="fa fa-gift"></i><span class="text"> <?= lang('salesman_commissions'); ?></span>
									</a>
								</li>
							<?php }  if (($Owner || $Admin || $GP['sales-receive_payments']) && $this->config->item('receive_payment')) { ?>
								<li id="sales_receive_payments">
									<a class="submenu" href="<?= site_url('sales/receive_payments'); ?>">
										<i class="fa fa-gift"></i><span class="text"> <?= lang('receive_payments'); ?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>
					<?php } if(($Owner || $Admin || $GP['installments-index'] || $GP['installments-payments']) && $Settings->installment){ ?>
						<li class="mm_installments <?= strtolower($this->router->fetch_method()) == 'installments' ? 'mm_installments' : '' ?>">
							<a class="dropmenu" href="#">
								<i class="fa fa-heart"></i>
								<span class="text"> <?= lang('installment'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
							<?php if ($Owner || $Admin || $GP['installments-index']) { ?>
								<li id="installments_index">
									<a class="submenu" href="<?= site_url('installments'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('installments');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['installments-payments']) { ?>
								<li id="installments_missed_repayments">
									<a class="submenu" href="<?= site_url('installments/missed_repayments'); ?>">
										<i class="fa fa-usd"></i>
										<span class="text"> <?= lang('missed_repayments');?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>
					<?php } if(($Owner || $Admin || $GP['loans-index'] || $GP['loans-loan_products'] || $GP['loans-index'] || $GP['loans-payments']) && $this->config->item("loan")){ ?>
						<li class="mm_loans <?= strtolower($this->router->fetch_method()) == 'loans' ? 'mm_loans' : '' ?>">
							<a class="dropmenu" href="#">
								<i class="fa fa-balance-scale"></i>
								<span class="text"> <?= lang('loan'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
							<?php if ($Owner || $Admin || $GP['loans-applications']) { ?>
								<li id="loans_applications">
									<a class="submenu" href="<?= site_url('loans/applications'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('applications');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['loans-index']) { ?>
								<li id="loans_index">
									<a class="submenu" href="<?= site_url('loans'); ?>">
										<i class="fa fa-heart"></i>
										<span class="text"> <?= lang('loans');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['loans-payments']) { ?>
								<li id="loans_missed_repayments">
									<a class="submenu" href="<?= site_url('loans/missed_repayments'); ?>">
										<i class="fa fa-usd"></i>
										<span class="text"> <?= lang('missed_repayments');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['loans-borrowers']) { ?>
								<li id="loans_borrowers">
									<a class="submenu" href="<?= site_url('loans/borrowers'); ?>">
										<i class="fa fa-star"></i>
										<span class="text"> <?= lang('borrowers');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['loans-loan_products']) { ?>
								<li id="loans_loan_products">
									<a class="submenu" href="<?= site_url('loans/loan_products'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('loan_products');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['loans-charges']) { ?>
								<li id="loans_charges">
									<a class="submenu" href="<?= site_url('loans/charges'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('charges');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['loans-index']) { ?>
								<li id="loans_calculator">
									<a class="submenu" href="<?= site_url('loans/calculator'); ?>">
										<i class="fa fa-calculator"></i>
										<span class="text"> <?= lang('calculator');?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>
						<li class="mm_savings <?= strtolower($this->router->fetch_method()) == 'savings' ? 'mm_savings' : '' ?>">
							<a class="dropmenu" href="#">
								<i class="fa fa-balance-scale"></i>
								<span class="text"> <?= lang('savings'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
							<?php if ($Owner || $Admin || $GP['savings-index']) { ?>
								<li id="savings_index">
									<a class="submenu" href="<?= site_url('savings'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('savings');?></span>
									</a>
								</li>
								<li id="savings_saving_products">
									<a class="submenu" href="<?= site_url('savings/saving_products'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('saving_products');?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>	
					<?php } if ($this->config->item('purchase') && ($Owner || $Admin || $GP['purchases-receives'] || $GP['purchases-index'] || $GP['purchases-expenses'] || $GP['purchase_requests-index'] || $GP['purchase_orders-index'])) { ?>
						<li id="welcomeDistributionPurchase" class="mm_purchases mm_purchase_orders mm_purchase_requests">
							<a class="dropmenu" href="#">
								<i class="fa fa-shopping-cart"></i>
								<span class="text"> <?= lang('purchase'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
							<?php if (($Owner || $Admin || $GP['purchase_requests-index']) && $this->config->item('purchase_request')) { ?>
								<li id="purchase_requests_index">
									<a class="submenu" href="<?= site_url('purchase_requests'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('purchase_requests'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['purchase_orders-index']) && $this->config->item('purchase_order')) { ?>
								<li id="purchase_orders_index">
									<a class="submenu" href="<?= site_url('purchase_orders'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('purchase_orders'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['purchases-index']) { ?>
								<li id="purchases_index">
									<a class="submenu" href="<?= site_url('purchases'); ?>">
										<i class="fa fa-star"></i><span class="text"> <?= lang('purchases'); ?></span>
									</a>
								</li>
								<?php if($this->config->item('receive_item')==true){?>
									<li id="purchases_freights">
										<a class="submenu" href="<?= site_url('purchases/freights'); ?>">
											<i class="fa fa-star"></i>
											<span class="text"> <?= lang('freights'); ?></span>
										</a>
									</li>
								<?php } ?>
							<?php } if(($Owner || $Admin || $GP['purchases-receives']) && $this->config->item('receive_item')){ ?>	
								<li id="purchases_receives">
									<a class="submenu" href="<?= site_url('purchases/receives'); ?>">
										<i class="fa fa-star"></i>
										<span class="text"> <?= lang('receives'); ?></span>
									</a>
								</li>
							<?php }if($Owner || $Admin || $GP['purchases-return_purchases']){ ?>
								<li id="purchases_purchase_return">
									<a class="submenu" href="<?= site_url('purchases/purchase_return'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('purchase_returns'); ?></span>
									</a>
								</li>
								</li>
							<?php } ?>
							</ul>
						</li>
						<?php } if ($Owner || $Admin || $GP['purchases-expenses']) { ?>
						<li class="mm_expenses">
							<a class="dropmenu" href="#">
								<i class="fa fa-money"></i>
								<span class="text"> <?= lang('expenses'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
								<li id="expenses_add">
                                        <a class="submenu" href="<?= site_url('expenses/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_expenses'); ?></span>
                                        </a>
                                </li>
								<li id="expenses_index">
									<a class="submenu" href="<?= site_url('expenses'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('list_expenses'); ?></span>
									</a>
								</li>
							</ul>
						</li>

					<?php } if ($Owner || $Admin || $GP['customers-index'] || $GP['auth-saleman'] || $GP['auth-agency'] || $GP['suppliers-index'] || $GP['auth-index']) { ?>
						<li id="welcomeDistributionPeople" class="mm_auth mm_customers mm_suppliers">
							<a class="dropmenu" href="#">
								<i class="fa fa-users"></i>
								<span class="text"> <?= lang('people'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if ($Owner || $Admin || $GP['auth-index']) { ?>
								<li id="auth_users">
									<a class="submenu" href="<?= site_url('users'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('users'); ?></span>
									</a>
								</li>
							<?php } if(($Owner || $Admin || $GP['auth-agency']) && $this->config->item('agency')){ ?>
								<li id="auth_agencies">
									<a class="submenu" href="<?= site_url('auth/agencies'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('agencies'); ?></span>
									</a>
								</li>
							<?php } if(($Owner || $Admin || $GP['auth-saleman']) && $this->config->item('saleman')){ ?>
								<li id="auth_salemans">
									<a class="submenu" href="<?= site_url('auth/salemans'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salemans'); ?></span>
									</a>
								</li>	
							<?php } if ($this->config->item('sale') && ($Owner || $Admin || $GP['customers-index'])) { ?>
								<li id="customers_index">
									<a class="submenu" href="<?= site_url('customers'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('customers'); ?></span>
									</a>
								</li>
							<?php }

							if ($this->config->item('purchase') && ($Owner || $Admin || $GP['suppliers-index'])) { ?>
								<li id="suppliers_index">
									<a class="submenu" href="<?= site_url('suppliers'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('suppliers'); ?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>

					<?php } if ($this->config->item('hr') && ($Owner || $Admin || $GP['hr-salary_reviews_report'] || $GP['hr-salary_reviews'] || $GP['hr-kpi_add'] || $GP['hr-id_cards'] || $GP['hr-id_cards_report'] || $GP['hr-sample_id_cards'] || $GP['hr-id_cards'] || $GP['hr-kpi_report'] || $GP['hr-employees_report'] || $GP['hr-banks_report'] || $GP['hr-kpi_index'] || $GP['hr-kpi_types'] || $GP['hr-index'] || $GP['hr-departments'] || $GP['hr-positions'] || $GP['hr-groups'] || $GP['hr-employee_types'] || $GP['hr-employees_relationships'] || $GP['hr-tax_conditions'] || $GP['hr-leave_types'])) { ?>
						<li class="mm_hr">
							<a class="dropmenu" href="#">
								<i class="fa fa-users"></i>
								<span class="text"> <?= lang('hr'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if($Owner || $Admin || $GP['hr-index']){ ?>
								<li id="hr_index">
									<a class="submenu" href="<?= site_url('hr'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('employees'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-kpi_add']){ ?>
								<li id="hr_print_kpi">
									<a class="submenu" href="<?= site_url('hr/print_kpi'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('print_kpi'); ?></span>
									</a>
								</li>		
							<?php } if($Owner || $Admin || $GP['hr-kpi_index']){ ?>
								<li id="hr_kpi">
									<a class="submenu" href="<?= site_url('hr/kpi'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('kpi'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-id_cards']){ ?>
								<li id="hr_id_cards">
									<a class="submenu" href="<?= site_url('hr/id_cards'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('id_cards'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-salary_reviews']){ ?>
								<li id="hr_salary_reviews">
									<a class="submenu" href="<?= site_url('hr/salary_reviews'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salary_reviews'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['hr-positions']) { ?>
								<li id="hr_positions">
									<a class="submenu" href="<?= site_url('hr/positions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('positions'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-departments']) { ?>
								<li id="hr_departments">
									<a class="submenu" href="<?= site_url('hr/departments'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('departments'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-groups']) { ?>
								<li id="hr_groups">
									<a class="submenu" href="<?= site_url('hr/groups'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('groups'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-employee_types']) { ?>
								<li id="hr_employee_types">
									<a class="submenu" href="<?= site_url('hr/employee_types'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('employee_types'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-employees_relationships']) { ?>
								<li id="hr_employees_relationships">
									<a class="submenu" href="<?= site_url('hr/employees_relationships'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('employees_relationships'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-tax_conditions']) { ?>
								<li id="hr_tax_conditions">
									<a class="submenu" href="<?= site_url('hr/tax_conditions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('tax_conditions'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-leave_types']) { ?>
								<li id="hr_leave_types">
									<a class="submenu" href="<?= site_url('hr/leave_types'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('leave_types'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-kpi_types']) { ?>
								<li id="hr_kpi_types">
									<a class="submenu" href="<?= site_url('hr/kpi_types'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('kpi_types'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-sample_id_cards']) { ?>
								<li id="hr_sample_id_cards">
									<a class="submenu" href="<?= site_url('hr/sample_id_cards'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('sample_id_cards'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['hr-employees_report']) { ?>
								<li id="hr_employees_report">
									<a class="submenu" href="<?= site_url('hr/employees_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('employees_report'); ?></span>
									</a>
								</li>		
							<?php } if($Owner || $Admin || $GP['hr-banks_report']) { ?>
								<li id="hr_banks_report">
									<a class="submenu" href="<?= site_url('hr/banks_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('banks_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['hr-kpi_report']) { ?>
								<li id="hr_kpi_report">
									<a class="submenu" href="<?= site_url('hr/kpi_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('kpi_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-id_cards_report']) { ?>
								<li id="hr_id_cards_report">
									<a class="submenu" href="<?= site_url('hr/id_cards_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('id_cards_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hr-salary_reviews_report']) { ?>
								<li id="hr_salary_reviews_report">
									<a class="submenu" href="<?= site_url('hr/salary_reviews_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salary_reviews_report'); ?></span>
									</a>
								</li>	
							<?php } ?>
							</ul>
						</li>
					<?php } if($this->config->item('attendance') && ($Owner || $Admin || $GP['attendances-check_in_outs'] || $GP['attendances-add_check_in_out'] || $GP['attendances-edit_check_in_out'] || $GP['attendances-delete_check_in_out'] || $GP['attendances-generate_attendances'] || $GP['attendances-take_leaves'] || $GP['attendances-approve_attendances'] || $GP['attendances-cancel_attendances'] || $GP['attendances-approve_ot']|| $GP['attendances-policies'] || $GP['attendances-ot_policies'] || $GP['attendances-list_devices'] || $GP['attendances-check_in_out_report'] || $GP['attendances-daily_attendance_report'] || $GP['attendances-montly_attendance_report'] || $GP['attendances-attendance_department_report'] || $GP['attendances-employee_leave_report'])){ ?>
						<li class="mm_attendances">
							<a class="dropmenu" href="#">
								<i class="fa fa-users"></i>
								<span class="text"> <?= lang('attendance'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if($Owner || $Admin || $GP['attendances-generate_attendances']){ ?>
								<li id="attendances_index">
									<a class="submenu" href="<?= site_url('attendances'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('generate_attendances'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-check_in_outs']){ ?>
								<li id="attendances_check_in_outs">
									<a class="submenu" href="<?= site_url('attendances/check_in_outs'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('check_in_outs'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-take_leaves']){ ?>
							
								<li id="attendances_take_leaves">
									<a class="submenu" href="<?= site_url('attendances/take_leaves'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('take_leaves'); ?></span>
									</a>
								</li>

							<?php } if($Owner || $Admin || $GP['attendances-approve_attendances']){ ?>
							
								<li id="attendances_approve_attendances">
									<a class="submenu" href="<?= site_url('attendances/approve_attendances'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('approve_attendances'); ?></span>
									</a>
								</li>

							<?php } if($Owner || $Admin || $GP['attendances-cancel_attendances']){ ?>
								<li id="attendances_cancel_attendances">
									<a class="submenu" href="<?= site_url('attendances/cancel_attendances'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('cancel_attendances'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-approve_ot']){ ?>
								<li id="attendances_approve_ot">
									<a class="submenu" href="<?= site_url('attendances/approve_ot'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('approve_ot'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-policies']){ ?>
								<li id="attendances_policies">
									<a class="submenu" href="<?= site_url('attendances/policies'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('policies'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-ot_policies']){ ?>
								<li id="attendances_ot_policies">
									<a class="submenu" href="<?= site_url('attendances/ot_policies'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('ot_policies'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-list_devices']){ ?>
								<li id="attendances_list_devices">
									<a class="submenu" href="<?= site_url('attendances/list_devices'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('devices'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-check_in_out_report']){ ?>
							
								<li id="attendances_check_in_out_report">
									<a class="submenu" href="<?= site_url('attendances/check_in_out_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('check_in_out_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-daily_attendance_report']){ ?>
								<li id="attendances_daily_attendance_report">
									<a class="submenu" href="<?= site_url('attendances/daily_attendance_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('daily_attendance_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-montly_attendance_report']){ ?>
								<li id="attendances_montly_attendance_report">
									<a class="submenu" href="<?= site_url('attendances/montly_attendance_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('montly_attendance_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-attendance_department_report']){ ?>
								<li id="attendances_attendance_department_report">
									<a class="submenu" href="<?= site_url('attendances/attendance_department_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('attendance_department_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['attendances-employee_leave_report']){ ?>
								<li id="attendances_employee_leave_report">
									<a class="submenu" href="<?= site_url('attendances/employee_leave_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('employee_leave_report'); ?></span>
									</a>
								</li>
								<li id="attendances_employee_leave_by_year_report">
									<a class="submenu" href="<?= site_url('attendances/employee_leave_by_year_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('employee_leave_by_year_report'); ?></span>
									</a>
								</li>	
							<?php } ?>
							</ul>
						</li>
					<?php } if($this->config->item('payroll') && ($Owner || $Admin || $GP['payrolls-salary_banks_report'] || $GP['payrolls-payslips_report'] || $GP['payrolls-cash_advances_report'] || $GP['payrolls-cash_advances'] || $GP['payrolls-payment_details_report'] || $GP['payrolls-payments_report'] || $GP['payrolls-payments'] || $GP['payrolls-deductions'] || $GP['payrolls-additions'] || $GP['payrolls-salaries_report'] || $GP['payrolls-salary_details_report'] || $GP['payrolls-salaries'] ||$GP['payrolls-benefits'] || $GP['payrolls-benefits_report'] || $GP['payrolls-benefit_details_report'])){ ?>
						<li class="mm_payrolls">
							<a class="dropmenu" href="#">
								<i class="fa fa-users"></i>
								<span class="text"> <?= lang('payroll'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if($Owner || $Admin || $GP['payrolls-cash_advances']) { ?>
								<li id="payrolls_cash_advances">
									<a class="submenu" href="<?= site_url('payrolls/cash_advances'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('cash_advances'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-benefits']) { ?>
								<li id="payrolls_benefits">
									<a class="submenu" href="<?= site_url('payrolls/benefits'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('benefits'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-salaries']) { ?>
								<li id="payrolls_index">
									<a class="submenu" href="<?= site_url('payrolls'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salaries'); ?></span>
									</a>
								</li>
								<li id="payrolls_salaries_13">
									<a class="submenu" href="<?= site_url('payrolls/salaries_13'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salaries_13'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-payments']) { ?>
								<li id="payrolls_payments">
									<a class="submenu" href="<?= site_url('payrolls/payments'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('payments'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['payrolls-cash_advances_report']) { ?>
								<li id="payrolls_cash_advances_report">
									<a class="submenu" href="<?= site_url('payrolls/cash_advances_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('cash_advances_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['payrolls-benefits_report']) { ?>
								<li id="payrolls_benefits_report">
									<a class="submenu" href="<?= site_url('payrolls/benefits_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('benefits_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-benefit_details_report']) { ?>
								<li id="payrolls_benefit_details_report">
									<a class="submenu" href="<?= site_url('payrolls/benefit_details_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('benefit_details_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['payrolls-salaries_report']) { ?>
								<li id="payrolls_salaries_report">
									<a class="submenu" href="<?= site_url('payrolls/salaries_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salaries_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-salary_details_report']) { ?>
								<li id="payrolls_salary_details_report">
									<a class="submenu" href="<?= site_url('payrolls/salary_details_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salary_details_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-salary_banks_report']) { ?>
								<li id="payrolls_salary_banks_report">
									<a class="submenu" href="<?= site_url('payrolls/salary_banks_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salary_banks_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-salaries_report']) { ?>
								<li id="payrolls_salaries_13_report">
									<a class="submenu" href="<?= site_url('payrolls/salaries_13_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salaries_13_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-salary_details_report']) { ?>
								<li id="payrolls_salary_13_details_report">
									<a class="submenu" href="<?= site_url('payrolls/salary_13_details_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('salary_13_details_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-payslips_report']) { ?>
								<li id="payrolls_payslips_report">
									<a class="submenu" href="<?= site_url('payrolls/payslips_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('payslips_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['payrolls-payments_report']) { ?>
								<li id="payrolls_payments_report">
									<a class="submenu" href="<?= site_url('payrolls/payments_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('payments_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-payment_details_report']) { ?>
								<li id="payrolls_payment_details_report">
									<a class="submenu" href="<?= site_url('payrolls/payment_details_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('payment_details_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['payrolls-additions']) { ?>
								<li id="payrolls_additions">
									<a class="submenu" href="<?= site_url('payrolls/additions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('additions'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['payrolls-deductions']) { ?>
								<li id="payrolls_deductions">
									<a class="submenu" href="<?= site_url('payrolls/deductions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('deductions'); ?></span>
									</a>
								</li>	
							<?php } ?>
							</ul>
						</li>
					<?php } if($this->config->item('concretes') && ($Owner || $Admin || $GP['concretes-absents_report'] || $GP['concretes-absents'] || $GP['concretes-officer_commissions'] || $GP['concretes-commissions_report'] || $GP['concretes-commissions'] || $GP['concretes-fuel_expenses_report'] || $GP['concretes-fuel_expense_details_report'] || $GP['concretes-missions_report'] || $GP['concretes-moving_waitings_report'] || $GP['concretes-fuel_expenses'] || $GP['concretes-missions'] || $GP['concretes-moving_waitings'] || $GP['concretes-mission_types'] || $GP['concretes-daily_errors'] || $GP['concretes-daily_error_materials'] || $GP['concretes-errors'] || $GP['concretes-adjustments_report'] || $GP['concretes-adjustments'] || $GP['concretes-daily_stock_ins'] || $GP['concretes-inventory_in_outs'] || $GP['concretes-daily_stock_outs'] || $GP['concretes-product_sales_report'] || $GP['concretes-officers']  || $GP['concretes-product_customers_report'] || $GP['concretes-fuel_by_customer_report'] || $GP['concretes-pump_commissions'] || $GP['concretes-truck_commissions'] || $GP['concretes-sale_details_report'] || $GP['concretes-sales_report'] || $GP['concretes-fuel_summaries_report'] || $GP['concretes-fuel_details_report'] || $GP['concretes-fuels_report'] || $GP['concretes-fuels'] || $GP['concretes-sales'] || $GP['concretes-daily_deliveries'] || $GP['concretes-deliveries_report'] || $GP['concretes-casting_types'] || $GP['concretes-slumps'] || $GP['concretes-deliveries'] || $GP['concretes-drivers'] || $GP['concretes-trucks'])){ ?>
						<li class="mm_concretes">
							<a class="dropmenu" href="#">
								<i class="fa fa-users"></i>
								<span class="text"> <?= lang('concrete'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if($Owner || $Admin || $GP['concretes-deliveries']) { ?>
								<li id="concretes_index">
									<a class="submenu" href="<?= site_url('concretes'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('deliveries'); ?></span>
									</a>
								</li>
							<?php } if($Settings->moving_waitings && ($Owner || $Admin || $GP['concretes-moving_waitings'])) { ?>
								<li id="concretes_moving_waitings">
									<a class="submenu" href="<?= site_url('concretes/moving_waitings'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('moving_waitings'); ?></span>
									</a>
								</li>
							<?php } if($Settings->missions && ($Owner || $Admin || $GP['concretes-missions'])) { ?>
								<li id="concretes_missions">
									<a class="submenu" href="<?= site_url('concretes/missions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('missions'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-fuels']) { ?>
								<li id="concretes_fuels">
									<a class="submenu" href="<?= site_url('concretes/fuels'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('fuels'); ?></span>
									</a>
								</li>
							<?php } if($Settings->fuel_expenses && ($Owner || $Admin || $GP['concretes-fuel_expenses'])) { ?>
								<li id="concretes_fuel_expenses">
									<a class="submenu" href="<?= site_url('concretes/fuel_expenses'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('fuel_expenses'); ?></span>
									</a>
								</li>		
							<?php } if($Owner || $Admin || $GP['concretes-sales']) { ?>
								<li id="concretes_sales">
									<a class="submenu" href="<?= site_url('concretes/sales'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('sales'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-adjustments']) { ?>
								<li id="concretes_adjustments">
									<a class="submenu" href="<?= site_url('concretes/adjustments'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('adjustments'); ?></span>
									</a>
								</li>
							<?php } if($Settings->errors && ($Owner || $Admin || $GP['concretes-errors'])) { ?>
								<li id="concretes_errors">
									<a class="submenu" href="<?= site_url('concretes/errors'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('errors'); ?></span>
									</a>
								</li>
							<?php } if($Settings->absents && ($Owner || $Admin || $GP['concretes-absents'])) { ?>
								<li id="concretes_absents">
									<a class="submenu" href="<?= site_url('concretes/absents'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('absents'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-commissions']) { ?>
								<li id="concretes_commissions">
									<a class="submenu" href="<?= site_url('concretes/commissions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('commissions'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-drivers']) { ?>
								<li id="concretes_drivers">
									<a class="submenu" href="<?= site_url('concretes/drivers'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('drivers'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-trucks']) { ?>
								<li id="concretes_trucks">
									<a class="submenu" href="<?= site_url('concretes/trucks'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('trucks'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-slumps']) { ?>
								<li id="concretes_slumps">
									<a class="submenu" href="<?= site_url('concretes/slumps'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('slumps'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-casting_types']) { ?>
								<li id="concretes_casting_types">
									<a class="submenu" href="<?= site_url('concretes/casting_types'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('casting_types'); ?></span>
									</a>
								</li>
							<?php } if($Settings->missions && ($Owner || $Admin || $GP['concretes-mission_types'])) { ?>
								<li id="concretes_mission_types">
									<a class="submenu" href="<?= site_url('concretes/mission_types'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('mission_types'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-officers']) { ?>
								<li id="concretes_officers">
									<a class="submenu" href="<?= site_url('concretes/officers'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('officers'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['concretes-deliveries_report']) { ?>
								<li id="concretes_deliveries_report">
									<a class="submenu" href="<?= site_url('concretes/deliveries_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('deliveries_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['concretes-daily_deliveries']) { ?>
								<li id="concretes_daily_deliveries">
									<a class="submenu" href="<?= site_url('concretes/daily_deliveries'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('daily_deliveries'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-daily_stock_outs']) { ?>
								<li id="concretes_daily_stock_outs">
									<a class="submenu" href="<?= site_url('concretes/daily_stock_outs'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('daily_stock_outs'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['concretes-daily_stock_ins']) { ?>
								<li id="concretes_daily_stock_ins">
									<a class="submenu" href="<?= site_url('concretes/daily_stock_ins'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('daily_stock_ins'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-inventory_in_outs']) { ?>
								<li id="concretes_inventory_in_outs">
									<a class="submenu" href="<?= site_url('concretes/inventory_in_outs'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('inventory_in_outs'); ?></span>
									</a>
								</li>
							<?php } if($Settings->moving_waitings && ($Owner || $Admin || $GP['concretes-moving_waitings_report'])) { ?>
								<li id="concretes_moving_waitings_report">
									<a class="submenu" href="<?= site_url('concretes/moving_waitings_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('moving_waitings_report'); ?></span>
									</a>
								</li>
							<?php } if($Settings->missions && ($Owner || $Admin || $GP['concretes-missions_report'])) { ?>
								<li id="concretes_missions_report">
									<a class="submenu" href="<?= site_url('concretes/missions_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('missions_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-fuels_report']) { ?>
								<li id="concretes_fuels_report">
									<a class="submenu" href="<?= site_url('concretes/fuels_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('fuels_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-fuel_summaries_report']) { ?>
								<li id="concretes_fuel_summaries_report">
									<a class="submenu" href="<?= site_url('concretes/fuel_summaries_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('fuel_summaries_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['concretes-fuel_details_report']) { ?>
								<li id="concretes_fuel_details_report">
									<a class="submenu" href="<?= site_url('concretes/fuel_details_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('fuel_details_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-fuel_by_customer_report']) { ?>
								<li id="concretes_fuel_by_customer_report">
									<a class="submenu" href="<?= site_url('concretes/fuel_by_customer_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('fuel_by_customer_report'); ?></span>
									</a>
								</li>
							<?php } if($Settings->fuel_expenses && ($Owner || $Admin || $GP['concretes-fuel_expenses_report'])) { ?>
								<li id="concretes_fuel_expenses_report">
									<a class="submenu" href="<?= site_url('concretes/fuel_expenses_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('fuel_expenses_report'); ?></span>
									</a>
								</li>
							<?php } if($Settings->fuel_expenses && ($Owner || $Admin || $GP['concretes-fuel_expense_details_report'])) { ?>
								<li id="concretes_fuel_expense_details_report">
									<a class="submenu" href="<?= site_url('concretes/fuel_expense_details_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('fuel_expense_details_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-sales_report']) { ?>
								<li id="concretes_sales_report">
									<a class="submenu" href="<?= site_url('concretes/sales_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('sales_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-sale_details_report']) { ?>
								<li id="concretes_sale_details_report">
									<a class="submenu" href="<?= site_url('concretes/sale_details_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('sale_details_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-product_sales_report']) { ?>
								<li id="concretes_product_sales_report">
									<a class="submenu" href="<?= site_url('concretes/product_sales_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('product_sales_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['concretes-product_customers_report']) { ?>
								<li id="concretes_product_customers_report">
									<a class="submenu" href="<?= site_url('concretes/product_customers_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('product_customers_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-adjustments_report']) { ?>
								<li id="concretes_adjustments_report">
									<a class="submenu" href="<?= site_url('concretes/adjustments_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('adjustments_report'); ?></span>
									</a>
								</li>
							<?php } if($Settings->errors && ($Owner || $Admin || $GP['concretes-daily_errors'])) { ?>
								<li id="concretes_daily_errors">
									<a class="submenu" href="<?= site_url('concretes/daily_errors'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('daily_errors'); ?></span>
									</a>
								</li>		
							<?php } if($Settings->errors && ($Owner || $Admin || $GP['concretes-daily_error_materials'])) { ?>
								<li id="concretes_daily_error_materials">
									<a class="submenu" href="<?= site_url('concretes/daily_error_materials'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('daily_error_materials'); ?></span>
									</a>
								</li>
							<?php } if($Settings->absents && ($Owner || $Admin || $GP['concretes-absents_report'])) { ?>
								<li id="concretes_absents_report">
									<a class="submenu" href="<?= site_url('concretes/absents_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('absents_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-commissions_report']) { ?>
								<li id="concretes_commissions_report">
									<a class="submenu" href="<?= site_url('concretes/commissions_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('commissions_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-truck_commissions']) { ?>
								<li id="concretes_truck_commissions">
									<a class="submenu" href="<?= site_url('concretes/truck_commissions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('truck_commissions'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-pump_commissions']) { ?>
								<li id="concretes_pump_commissions">
									<a class="submenu" href="<?= site_url('concretes/pump_commissions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('pump_commissions'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['concretes-officer_commissions']) { ?>
								<li id="concretes_officer_commissions">
									<a class="submenu" href="<?= site_url('concretes/officer_commissions'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('officer_commissions'); ?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>		
					<?php } if($this->config->item('hospitals') && ($Owner || $Admin || $GP['hospitals-treaments'] || $GP['hospitals-patients'] || $GP['hospitals-illnesses'] || $GP['hospitals-doctors'] || $GP['hospitals-bed_rooms'] || $GP['hospitals-services'] || $GP['hospitals-service_categories'])){ ?>
						<li class="mm_hospitals">
							<a class="dropmenu" href="#">
								<i class="fa fa-h-square"></i>
								<span class="text"> <?= lang('hospitals'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if($Owner || $Admin || $GP['hospitals-patients']) { ?>
								<li id="hospitals_patients">
									<a class="submenu" href="<?= site_url('hospitals/patients'); ?>">
										<i class="fa fa fa-h-square"></i><span class="text"> <?= lang('patients'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hospitals-treaments']) { ?>
								<li id="hospitals_treaments">
									<a class="submenu" href="<?= site_url('hospitals/treaments'); ?>">
										<i class="fa fa fa-h-square"></i><span class="text"> <?= lang('treaments'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hospitals-service_categories']) { ?>
								<li id="hospitals_service_categories">
									<a class="submenu" href="<?= site_url('hospitals/service_categories'); ?>">
										<i class="fa fa fa-h-square"></i><span class="text"> <?= lang('service_categories'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hospitals-services']) { ?>
								<li id="hospitals_services">
									<a class="submenu" href="<?= site_url('hospitals/services'); ?>">
										<i class="fa fa fa-h-square"></i><span class="text"> <?= lang('services'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hospitals-bed_rooms']) { ?>
								<li id="hospitals_bed_rooms">
									<a class="submenu" href="<?= site_url('hospitals/bed_rooms'); ?>">
										<i class="fa fa fa-h-square"></i><span class="text"> <?= lang('bed_rooms'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hospitals-illnesses']) { ?>
								<li id="hospitals_illnesses">
									<a class="submenu" href="<?= site_url('hospitals/illnesses'); ?>">
										<i class="fa fa fa-h-square"></i><span class="text"> <?= lang('illnesses'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['hospitals-doctors']) { ?>
								<li id="hospitals_doctors">
									<a class="submenu" href="<?= site_url('hospitals/doctors'); ?>">
										<i class="fa fa fa-h-square"></i><span class="text"> <?= lang('doctors'); ?></span>
									</a>
								</li>	
							<?php } ?>
							</ul>
						</li>
					
					<?php } if($this->config->item('schools') && ($Owner || $Admin  || $GP['schools-overview_chart'] || $GP['schools-failure_student_by_year_report'] || $GP['schools-best_student_by_level_report'] || $GP['schools-teacher_attendance_report'] || $GP['schools-attendance_report'] || $GP['schools-teacher_attendances'] || $GP['schools-teacher_report'] || $GP['schools-student_report'] || $GP['schools-attendances'] || $GP['schools-class_result_report'] || $GP['schools-add'] || $GP['schools-teachers-add'] || $GP['schools-examinations-add'] || $GP['schools-add'] || $GP['schools-teachers-add']|| $GP['schools-yearly_top_five_form'] || $GP['schools-monthly_top_five_form'] || $GP['schools-result_by_student_form'] || $GP['schools-study_info_report'] || $GP['schools-sectionly_subject_result_report'] || $GP['schools-yearly_top_five_report'] || $GP['schools-yearly_class_result_report'] || $GP['schools-yearly_subject_result_report'] || $GP['schools-monthly_top_five_report'] || $GP['schools-sectionly_class_result_report'] || $GP['schools-section_by_month_report'] || $GP['schools-study_info_report'] || $GP['schools-examanition_report'] || $GP['schools-monthly_class_result_report'] || $GP['schools-skills'] || $GP['schools-levels'] || $GP['schools-subjects'] || $GP['schools-sections'] || $GP['schools-rooms'] || $GP['schools-classes'] || $GP['schools-credit_scores'])) { ?>	
						<li class="mm_schools">
							<a class="dropmenu" href="#">
								<i class="fa fa-users"></i>
								<span class="text"> <?= lang('school'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if($Owner || $Admin || $GP['schools-index']) { ?>
								<li id="schools_index">
									<a class="submenu" href="<?= site_url('schools'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('students'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-teachers']) { ?>	
								<li id="schools_teachers">
									<a class="submenu" href="<?= site_url('schools/teachers'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('teachers'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-skills']) { ?>	
								<li id="schools_skills">
									<a class="submenu" href="<?= site_url('schools/skills'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('skills'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-levels']) { ?>	
								<li id="schools_levels">
									<a class="submenu" href="<?= site_url('schools/levels'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('levels'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-subjects']) { ?>	
								<li id="schools_subjects">
									<a class="submenu" href="<?= site_url('schools/subjects'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('subjects'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-sections']) { ?>	
								<li id="schools_sections">
									<a class="submenu" href="<?= site_url('schools/sections'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('sections'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-rooms']) { ?>	
								<li id="schools_rooms">
									<a class="submenu" href="<?= site_url('schools/rooms'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('rooms'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-classes']) { ?>	
								<li id="schools_classes">
									<a class="submenu" href="<?= site_url('schools/classes'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('classes'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-credit_scores']) { ?>	
								<li id="schools_credit_scores">
									<a class="submenu" href="<?= site_url('schools/credit_scores'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('credit_scores'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-examinations']) { ?>	
								<li id="schools_examinations">
									<a class="submenu" href="<?= site_url('schools/examinations'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('examinations'); ?></span>
									</a>
								</li>
								<li id="schools_examination_details">
									<a class="submenu" href="<?= site_url('schools/examination_details'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('examination_details'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-attendances']) { ?>
								<li id="schools_attendances">
									<a class="submenu" href="<?= site_url('schools/attendances'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('attendances'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-teacher_attendances']) { ?>
								<li id="schools_teacher_attendances">
									<a class="submenu" href="<?= site_url('schools/teacher_attendances'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('teacher_attendances'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-study_info_report']) { ?>	
								<li id="schools_study_info_report">
									<a class="submenu" href="<?= site_url('schools/study_info_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('study_info_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-student_report']) { ?>	
								<li id="schools_student_report">
									<a class="submenu" href="<?= site_url('schools/student_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('student_report'); ?></span>
									</a>
								</li>	
								<li id="schools_student_by_class_report">
									<a class="submenu" href="<?= site_url('schools/student_by_class_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('student_by_class_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-teacher_report']) { ?>	
								<li id="schools_teacher_report">
									<a class="submenu" href="<?= site_url('schools/teacher_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('teacher_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-examanition_report']) { ?>	
								<li id="schools_examanition_report">
									<a class="submenu" href="<?= site_url('schools/examanition_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('examanition_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-attendance_report']) { ?>	
								<li id="schools_attendance_report">
									<a class="submenu" href="<?= site_url('schools/attendance_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('attendance_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-teacher_attendance_report']) { ?>	
								<li id="schools_teacher_attendance_report">
									<a class="submenu" href="<?= site_url('schools/teacher_attendance_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('teacher_attendance_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-monthly_class_result_report']) { ?>	
								<li id="schools_monthly_class_result_report">
									<a class="submenu" href="<?= site_url('schools/monthly_class_result_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('monthly_class_result_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-monthly_top_five_report']) { ?>	
								<li id="schools_monthly_top_five_report">
									<a class="submenu" href="<?= site_url('schools/monthly_top_five_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('monthly_top_five_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-section_by_month_report']) { ?>	
								<li id="schools_section_by_month_report">
									<a class="submenu" href="<?= site_url('schools/section_by_month_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('section_by_month_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-sectionly_class_result_report']) { ?>	
								<li id="schools_sectionly_class_result_report">
									<a class="submenu" href="<?= site_url('schools/sectionly_class_result_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('sectionly_class_result_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-class_result_report']) { ?>	
								<li id="schools_class_result_report">
									<a class="submenu" href="<?= site_url('schools/class_result_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('class_result_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-yearly_class_result_report']) { ?>	
								<li id="schools_yearly_class_result_report">
									<a class="submenu" href="<?= site_url('schools/yearly_class_result_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('yearly_class_result_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-yearly_top_five_report']) { ?>	
								<li id="schools_yearly_top_five_report">
									<a class="submenu" href="<?= site_url('schools/yearly_top_five_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('yearly_top_five_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-sectionly_subject_result_report']) { ?>	
								<li id="schools_sectionly_subject_result_report">
									<a class="submenu" href="<?= site_url('schools/sectionly_subject_result_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('sectionly_subject_result_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-yearly_subject_result_report']) { ?>	
								<li id="schools_yearly_subject_result_report">
									<a class="submenu" href="<?= site_url('schools/yearly_subject_result_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('yearly_subject_result_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-result_by_student_form']) { ?>	
								<li id="schools_result_by_student_form">
									<a class="submenu" href="<?= site_url('schools/result_by_student_form'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('result_by_student_form'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-monthly_top_five_form']) { ?>	
								<li id="schools_monthly_top_five_form">
									<a class="submenu" href="<?= site_url('schools/monthly_top_five_form'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('monthly_top_five_form'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-yearly_top_five_form']) { ?>	
								<li id="schools_yearly_top_five_form">
									<a class="submenu" href="<?= site_url('schools/yearly_top_five_form'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('yearly_top_five_form'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-best_student_by_level_report']) { ?>	
								<li id="schools_best_student_by_level_report">
									<a class="submenu" href="<?= site_url('schools/best_student_by_level_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('best_student_by_level_report'); ?></span>
									</a>
								</li>	
							<?php } if($Owner || $Admin || $GP['schools-failure_student_by_year_report']) { ?>	
								<li id="schools_failure_student_by_year_report">
									<a class="submenu" href="<?= site_url('schools/failure_student_by_year_report'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('failure_student_by_year_report'); ?></span>
									</a>
								</li>
							<?php } if($Owner || $Admin || $GP['schools-overview_chart']) { ?>	
								<li id="schools_overview_chart">
									<a class="submenu" href="<?= site_url('schools/overview_chart'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('overview_chart'); ?></span>
									</a>
								</li>	
							<?php } ?>	
							</ul>
						</li>
					<?php } if($this->config->item('pawn') && ($Owner || $Admin || $GP['pawns-add'] || $GP['pawns-edit'] || $GP['pawns-delete'] || $GP['pawns-index'] || $GP['pawns-payments'] || $GP['pawns-date'] || $GP['pawns-closes'] || $GP['pawns-returns'] || $GP['pawns-purchases'] || $GP['pawns-products'])){ ?>
						<li class="mm_pawns">
							<a class="dropmenu" href="#">
								<i class="fa fa-star"></i>
								<span class="text"> <?= lang('pawn'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
							<?php if ($Owner || $Admin || $GP['pawns-index']) { ?>
								<li id="pawns_index">
									<a class="submenu" href="<?= site_url('pawns'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('pawns'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['pawns-returns']) { ?>
							<li id="pawns_returns">
								<a class="submenu" href="<?= site_url('pawns/returns'); ?>">
									<i class="fa fa-heart-o"></i>
									<span class="text"> <?= lang('pawn_returns'); ?></span>
								</a>
							</li>
							<?php } if ($Owner || $Admin || $GP['pawns-purchases']) { ?>
								<li id="pawns_purchase">
									<a class="submenu" href="<?= site_url('pawns/purchase'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('pawn_purchases'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['pawns-products']) { ?>
								<li id="pawns_products">
									<a class="submenu" href="<?= site_url('pawns/products'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('pawn_products'); ?></span>
									</a>
								</li>
							<?php } ?>	
							</ul>
						</li>
					<?php } if($Settings->accounting && ($Owner || $Admin || $GP['accountings-bank_reconciliations'] || $GP['accountings-cash_flow'] || $GP['accountings-income_statement'] || $GP['accountings-balance_sheet'] || $GP['accountings-trial_balance'] || $GP['accountings-cash_books'] || $GP['accountings-general_ledger'] || $GP['accountings-enter_journals'] || $GP['accountings-index'] || $GP['accountings-journals'])){ ?>
						<li id="welcomeFinance" class="mm_accountings <?= strtolower($this->router->fetch_method()) == 'accountings' ? 'mm_accounting' : '' ?>">
							<a class="dropmenu" href="#">
								<i class="fa fa-money"></i>
								<span class="text"> <?= lang('accounting'); ?> 
								</span> <span class="chevron closed"></span>
							</a>
							<ul>
							<?php if ($Owner || $Admin || $GP['accountings-index']) { ?>
								<li id="accountings_index">
									<a class="submenu" href="<?= site_url('accountings'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('chart_accounts');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['accountings-enter_journals']) { ?>
								<li id="accountings_add_enter_journal">
									<a class="submenu" href="<?= site_url('accountings/add_enter_journal'); ?>">
										<i class="fa fa-plus-circle"></i>
										<span class="text"> <?= lang('add_enter_journal');?></span>
									</a>
								</li>
								<li id="accountings_enter_journals">
									<a class="submenu" href="<?= site_url('accountings/enter_journals'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('enter_journals');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['accountings-bank_reconciliations']) { ?>	
								<li id="accountings_bank_reconciliations" class="hidden">
									<a class="submenu" href="<?= site_url('accountings/bank_reconciliations'); ?>">
										<i class="fa fa-heart-o"></i>
										<span class="text"> <?= lang('bank_reconciliations');?></span>
									</a>
								</li>	
							<?php } if ($Owner || $Admin || $GP['accountings-journals']) { ?>
								<li id="accountings_journals">
									<a class="submenu" href="<?= site_url('accountings/journals'); ?>">
										<i class="fa fa-bars"></i>
										<span class="text"> <?= lang('journals');?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['accountings-general_ledger']) { ?>
								<li id="accountings_general_ledger">
									<a href="<?= site_url('accountings/general_ledger') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('general_ledger'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['accountings-trial_balance']) { ?>	
								<li id="accountings_trial_balance">
									<a href="<?= site_url('accountings/trial_balance') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('trial_balance'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['accountings-income_statement']) { ?>	
								<li id="accountings_income_statement">
									<a href="<?= site_url('accountings/income_statement') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('income_statement'); ?></span>
									</a>
								</li>
								<li id="accountings_income_statement_by_month">
									<a href="<?= site_url('accountings/income_statement_by_month') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('income_statement_by_month'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['accountings-balance_sheet']) { ?>	
								<li id="accountings_balance_sheet">
									<a href="<?= site_url('accountings/balance_sheet') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('balance_sheet'); ?></span>
									</a>
								</li>
								<li id="accountings_balance_sheet_by_month">
									<a href="<?= site_url('accountings/balance_sheet_by_month') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('balance_sheet_by_month'); ?></span>
									</a>
								</li>
								<li id="accountings_balance_sheet_with_last_month" class="hidden">
									<a href="<?= site_url('accountings/balance_sheet_with_last_month') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('balance_sheet_with_last_month'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['accountings-cash_flow']) { ?>	
								<li id="accountings_cash_flow" class="hidden">
									<a href="<?= site_url('accountings/cash_flow') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('cash_flow'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['accountings-cash_books']) { ?>
								<li id="accountings_cash_books">
									<a href="<?= site_url('accountings/cash_books') ?>">
										<i class="fa fa-bars"></i><span class="text"> <?= lang('cash_book'); ?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>
					<?php } if ($Owner || $Admin || $GP['system_settings-cash_account'] || $GP['projects-index'] || $GP['system_settings'] || $GP['pos_settings'] || $GP['change_logo'] || $GP['billers-index'] || $GP['warehouses-index'] || $GP['areas-index'] || $GP['expense_categories-index'] || $GP['categories-index'] || $GP['tables-index'] || $GP['units-index'] || $GP['brands-index'] || $GP['variants-index'] || $GP['system_settings-boms'] || $GP['customer_groups-index'] || $GP['price_groups-index']|| $GP['payment_terms-index']|| $GP['currencies-index']|| $GP['customer_opening_balances-index']|| $GP['supplier_opening_balances-index']|| $GP['tax_rates-index']|| $GP['list_printers-index'] || $GP['email_templates-index'] || $GP['group_permissions-index'] || $GP['backups-index'] || $GP['system_settings-rooms']){ ?>
						<li id="welcomeSystem" class="mm_system_settings mm_billers mm_calendar mm_billers <?= strtolower($this->router->fetch_method()) == 'sales' ? '' : 'mm_pos' ?>">
							<a class="dropmenu" href="#">
								<i class="fa fa-cog"></i><span class="text"> <?= lang('setting'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if ($Owner || $Admin || $GP['system_settings']){ ?>	
								<li id="system_settings_index">
									<a href="<?= site_url('system_settings') ?>">
										<i class="fa fa-cog"></i><span class="text"> <?= lang('system_settings'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['pos_settings']) && POS){ ?>
								<li id="pos_settings">
									<a href="<?= site_url('pos/settings') ?>">
										<i class="fa fa-th-large"></i><span class="text"> <?= lang('pos_settings'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['change_logo']){ ?>
								<li id="system_settings_change_logo">
									<a href="<?= site_url('system_settings/change_logo') ?>" data-toggle="modal" data-target="#myModal">
										<i class="fa fa-upload"></i><span class="text"> <?= lang('change_logo'); ?></span>
									</a>
								</li>
							
							<?php } if ($Owner || $Admin || $GP['billers-index']){ ?>
								<li id="billers_index">
									<a class="submenu" href="<?= site_url('billers'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('billers'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['projects-index']) && $Settings->project){ ?>
								<li id="system_settings_projects">
									<a class="submenu" href="<?= site_url('system_settings/projects'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('projects'); ?></span>
									</a>
								</li>	
							<?php } if (($this->config->item('sale') || $this->config->item('purchase')) && ($Owner || $Admin || $GP['warehouses-index'])){ ?>	
								<li id="system_settings_warehouses">
									<a href="<?= site_url('system_settings/warehouses') ?>">
										<i class="fa fa-building-o"></i><span class="text"> <?= lang('warehouses'); ?></span>
									</a>
								</li>
							<?php } if (($this->config->item('purchase')) && ($Owner || $Admin || $GP['expense_categories-index'])){ ?>	
								<li id="system_settings_expense_categories">
									<a href="<?= site_url('system_settings/expense_categories') ?>">
										<i class="fa fa-folder-open"></i><span class="text"> <?= lang('expense_categories'); ?></span>
									</a>
								</li>
							<?php } if (($this->config->item('inventory')) && ($Owner || $Admin || $GP['categories-index'])){ ?>	
								<li id="system_settings_categories">
									<a href="<?= site_url('system_settings/categories') ?>">
										<i class="fa fa-folder-open"></i><span class="text"> <?= lang('categories'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['system_settings-frequencies']) && $Settings->installment || $this->config->item('room_rent')){ ?>	
								<li id="system_settings_frequencies">
									<a class="submenu" href="<?= site_url('system_settings/frequencies'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('frequencies'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['tables-index']) && $pos_settings->table_enable==true){ ?>	
								<li id="system_settings_tables">
									<a class="submenu" href="<?= site_url('system_settings/tables'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('tables'); ?></span>
									</a>
								</li>
								<li id="system_settings_floors">
									<a class="submenu" href="<?= site_url('system_settings/floors'); ?>">
										<i class="fa fa-users"></i><span class="text"> <?= lang('floors'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['system_settings-vehicles']) && $this->config->item("vehicle")){ ?>		
								<li id="system_settings_vehicles">
									<a href="<?= site_url('system_settings/vehicles') ?>">
										<i class="fa fa-car"></i><span class="text"> <?= lang('vehicles'); ?></span>
									</a>
								</li>
							<?php } if ($this->config->item('inventory') && ($Owner || $Admin || $GP['units-index'])){ ?>	
								<li id="system_settings_units">
									<a href="<?= site_url('system_settings/units') ?>">
										<i class="fa fa-wrench"></i><span class="text"> <?= lang('units'); ?></span>
									</a>
								</li>
							<?php } if ($this->config->item('inventory') && ($Owner || $Admin || $GP['brands-index'])){ ?>		
								<li id="system_settings_brands">
									<a href="<?= site_url('system_settings/brands') ?>">
										<i class="fa fa-th-list"></i><span class="text"> <?= lang('brands'); ?></span>
									</a>
								</li>
								<li id="system_settings_branch_prefix">
									<a href="<?= site_url('system_settings/branch_prefix') ?>">
										<i class="fa fa-wrench"></i><span class="text"> <?= lang('branch_prefix'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['system_settings-models']) && $this->config->item("repair")){ ?>
								<li id="system_settings_models">
									<a href="<?= site_url('system_settings/models') ?>">
										<i class="fa fa-th-list"></i><span class="text"> <?= lang('models'); ?></span>
									</a>
								</li>
							
							<?php } if ($this->config->item('sale') && ($Owner || $Admin || $GP['customer_groups-index'])){ ?>		
								<li id="system_settings_customer_groups">
									<a href="<?= site_url('system_settings/customer_groups') ?>">
										<i class="fa fa-chain"></i><span class="text"> <?= lang('customer_groups'); ?></span>
									</a>
								</li>
							<?php } if($this->config->item('sale') && ($Owner || $Admin || $GP['customer_price-index']) && $Settings->customer_price){ ?>
									<li id="system_settings_customer_prices">
										<a href="<?= site_url('system_settings/customer_prices') ?>">
											<i class="fa fa-dollar"></i><span class="text"> <?= lang('customer_prices'); ?></span>
										</a>
									</li>
							<?php } if ($this->config->item('sale') && ($Owner || $Admin || $GP['price_groups-index'])){ ?>		
								<li id="system_settings_price_groups">
									<a href="<?= site_url('system_settings/price_groups') ?>">
										<i class="fa fa-dollar"></i><span class="text"> <?= lang('price_groups'); ?></span>
									</a>
								</li>
							<?php } if($this->config->item('product_promotions') && ($Owner || $Admin || $GP['system_settings-product_promotions'])){ ?>
									<li id="system_settings_product_promotions">
										<a href="<?= site_url('system_settings/product_promotions') ?>">
											<i class="fa fa-dollar"></i><span class="text"> <?= lang('product_promotions'); ?></span>
										</a>
									</li>
							<?php } if (($this->config->item('sale') || $this->config->item('purchase')) && ($Owner || $Admin || $GP['payment_terms-index']) && $this->config->item('list_sales')){ ?>		
								<li id="system_settings_payment_terms">
									<a href="<?= site_url('system_settings/payment_terms') ?>">
										<i class="fa fa-chain"></i><span class="text"> <?= lang('payment_terms'); ?></span>
									</a>
								</li>
							<?php } if(($Owner || $Admin || $GP['saleman_targets-index']) && $this->config->item('saleman_commission')){ ?>
								<li id="system_settings_saleman_targets">
									<a href="<?= site_url('system_settings/saleman_targets') ?>">
										<i class="fa fa-money"></i><span class="text"> <?= lang('saleman_targets'); ?></span>
									</a>
								</li>
							<?php } if (($this->config->item('sale') || $this->config->item('purchase')) && ($Owner || $Admin || $GP['currencies-index'])){ ?>	
								<li id="system_settings_currencies">
									<a href="<?= site_url('system_settings/currencies') ?>">
										<i class="fa fa-money"></i><span class="text"> <?= lang('currencies'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['system_settings-inventory_opening_balances']) && $Settings->accounting){ ?>	
								<li id="system_settings_inventory_opening_balances">
									<a class="submenu" href="<?= site_url('system_settings/inventory_opening_balances'); ?>">
										<i class="fa fa-key"></i><span class="text"> <?= lang('inventory_opening_balances'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['customer_opening_balances-index']) && $Settings->accounting){ ?>	
								<li id="system_settings_customer_opening_balances">
									<a class="submenu" href="<?= site_url('system_settings/customer_opening_balances'); ?>">
										<i class="fa fa-key"></i><span class="text"> <?= lang('customer_opening_balances'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['supplier_opening_balances-index']) && $Settings->accounting ){ ?>		
								<li id="system_settings_supplier_opening_balances">
									<a class="submenu" href="<?= site_url('system_settings/supplier_opening_balances'); ?>">
										<i class="fa fa-key"></i><span class="text"> <?= lang('supplier_opening_balances'); ?></span>
									</a>
								</li>
							<?php } if (($this->config->item('sale') || $this->config->item('purchase')) && ($Owner || $Admin || $GP['tax_rates-index'])){ ?>		
								<li id="system_settings_tax_rates">
									<a href="<?= site_url('system_settings/tax_rates') ?>">
										<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('tax_rates'); ?></span>
									</a>
								</li>
								<li id="system_settings_tax_rates">
									<a href="<?= site_url('system_settings/tax_validation') ?>">
										<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('tax_validations'); ?></span>
									</a>
								</li>
							<?php } if (($this->config->item('sale')) && POS && ($Owner || $Admin || $GP['list_printers-index'])){ ?>		
								<li id="pos_printers">
									<a href="<?= site_url('pos/printers') ?>">
										<i class="fa fa-print"></i><span class="text"> <?= lang('printers'); ?></span>
									</a>
								</li>
							<?php } if (($this->config->item('sale') || $this->config->item('purchase')) && ($Owner || $Admin || $GP['email_templates-index'])){ ?>		
								<li id="system_settings_email_templates">
									<a href="<?= site_url('system_settings/email_templates') ?>">
										<i class="fa fa-envelope"></i><span class="text"> <?= lang('email_templates'); ?></span>
									</a>
								</li>
							<?php } if(($Owner || $Admin || $GP['system_settings-salesman_groups']) && $this->config->item('saleman')) { ?>
								<li id="system_settings_salesman_groups">
									<a href="<?= site_url('system_settings/salesman_groups') ?>">
										<i class="fa fa-key"></i><span class="text"> <?= lang('salesman_groups'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['system_settings-cash_account']){ ?>		
								<li id="system_settings_cash_accounts">
									<a href="<?= site_url('system_settings/cash_accounts') ?>">
										<i class="fa fa-money"></i><span class="text"> <?= lang('cash_accounts'); ?></span>
									</a>
								</li>
							<?php } if ($Owner || $Admin || $GP['group_permissions-index']){ ?>		
								<li id="system_settings_user_groups">
									<a href="<?= site_url('system_settings/user_groups') ?>">
										<i class="fa fa-key"></i><span class="text"> <?= lang('group_permissions'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin) && ($Settings->installment || $Settings->login_time || $this->config->item('attendance'))){ ?>	
								<li id="calendar_calendar_lists">
									<a href="<?= site_url('calendar/calendar_lists') ?>">
										<i class="fa fa-key"></i><span class="text"> <?= lang('holidays'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin) && $Settings->login_time){ ?>	
								<li id="system_settings_login_time">
									<a href="<?= site_url('system_settings/login_time') ?>">
										<i class="fa fa-clock-o"></i><span class="text"> <?= lang('login_time'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['system_settings-tanks']) && $this->config->item("fuel")){ ?>		
								<li id="system_settings_tanks">
									<a href="<?= site_url('system_settings/tanks') ?>">
										<i class="fa fa-cube"></i><span class="text"> <?= lang('tanks'); ?></span>
									</a>
								</li>
								<li id="system_settings_fuel_times">
									<a href="<?= site_url('system_settings/fuel_times') ?>">
										<i class="fa fa-clock-o"></i><span class="text"> <?= lang('fuel_times'); ?></span>
									</a>
								</li>
							<?php } if (($Owner || $Admin || $GP['backups-index']) && $this->config->item('backup')){ ?>		
								<li id="system_settings_backups">
									<a href="<?= site_url('system_settings/backups') ?>">
										<i class="fa fa-database"></i><span class="text"> <?= lang('backups'); ?></span>
									</a>
								</li>
							<?php } ?>
							</ul>
						</li>

						<?php } if ($Owner || $Admin || $GP['reports-inventory_valuation_report'] || $GP['reports-product_license_alerts'] || $GP['reports-product_purchases_report'] || $GP['reports-deliveries'] || $GP['reports-warehouse_stock'] || $GP['reports-quantity_alerts'] || $GP['reports-best_sellers'] || $GP['reports-expiry_alerts'] || $GP['reports-categories'] || $GP['reports-brands'] || $GP['reports-products'] || $GP['reports-inventory_in_out'] || $GP['reports-adjustments'] || $GP['reports-product_sales_report'] || $GP['reports-register'] || $GP['reports-saleman_report'] || $GP['reports-saleman_detail_report'] || $GP['reports-expenses'] || $GP['reports-daily_purchases'] || $GP['reports-monthly_purchases'] || $GP['reports-daily_sales'] || $GP['reports-monthly_sales'] || $GP['reports-sales_detail'] || $GP['reports-purchases'] || $GP['reports-profit_loss'] || $GP['reports-customers'] || $GP['reports-suppliers'] || $GP['reports-users'] || $GP['reports-audit_trails'] || $GP['reports-sales'] || $GP['reports-cost_adjustments'] || $GP['reports-pawn'] || $GP['reports-daily_rentals'] || $GP['reports-rentals'] || $GP['reports-rental_details']) { ?>

						<li id="welcomeReport" class="mm_reports">
							<a class="dropmenu" href="#">
								<i class="fa fa-bar-chart-o"></i>
								<span class="text"> <?= lang('report'); ?> </span> 
								<span class="chevron closed"></span>
							</a>
							<ul>
							<?php if ($this->config->item('inventory') && ($Owner || $Admin || $GP['products-consignments'] || $GP['products-index'] || $GP['products-add'] || $GP['products-barcode'] || $GP['products-adjustments'] || $GP['products-stock_count'] || $GP['products-converts'])) { ?>
								<li class="mm_inventory" id="welcomeInventory">
									<a class="dropmenu" href="#">
										<i class="fa fa-bar-chart-o"></i>
										<span class="text big_fonts"> <?= lang('inventory'); ?> </span>
										<span class="chevron closed"></span>
									</a>
									<ul>
										<!-- inventory submenu-->
										<?php } if ($Owner || $Admin || $GP['reports-inventory_in_out']) { ?>
										<li id="reports_inventory_in_out">
											<a href="<?= site_url('reports/inventory_in_out') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('inventory_in_out'); ?></span>
											</a>
										</li>
										<?php if($Settings->product_expiry){ ?>
										<li id="reports_inventory_in_out_expiry">
											<a href="<?= site_url('reports/inventory_in_out_expiry') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('inventory_in_out_expiry'); ?></span>
											</a>
										</li>
										<?php } ?>
										<?php } if($this->config->item('using_stocks') && ($Owner || $Admin || $GP['reports-using_stocks'])){?>
										<li id="reports_using_stocks">
											<a href="<?= site_url('reports/using_stocks') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('using_stocks_report'); ?></span>
											</a>
										</li>
										<li id="reports_using_stock_details">
											<a href="<?= site_url('reports/using_stock_details') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('using_stock_details_report'); ?></span>
											</a>
										</li>
										<?php } if ($Owner || $Admin || $GP['reports-quantity_alerts']) { ?>
										<li id="reports_quantity_alerts">
											<a href="<?= site_url('reports/quantity_alerts') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('product_quantity_alerts'); ?></span>
											</a>
										</li>
										<?php } if ($Settings->product_expiry && ($Owner || $Admin || $GP['reports-expiry_alerts'])) { ?>	
										<li id="reports_expiry_alerts">
											<a href="<?= site_url('reports/expiry_alerts') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('product_expiry_alerts'); ?></span>
											</a>
										</li>
										<?php } if (($Owner || $Admin || $GP['reports-product_variants']) && $Settings->attributes == 1) { ?>	
										<li id="reports_product_variant">
											<a href="<?= site_url('reports/product_variant') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('product_variant'); ?></span>
											</a>
										</li>
										<?php } if ($this->config->item('product_promotions') && ($Owner || $Admin || $GP['reports-products_promotion_report'])) { ?>	
										<li id="reports_products_promotion_report">
											<a href="<?= site_url('reports/products_promotion_report') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('products_promotion_report'); ?></span>
											</a>
										</li>
										<?php } if ($Owner || $Admin || $GP['reports-adjustments']) { ?>	
										<li id="reports_adjustments">
											<a href="<?= site_url('reports/adjustments') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('adjustments_report'); ?></span>
											</a>
										</li>
										<li id="reports_adjustment_details">
											<a href="<?= site_url('reports/adjustment_details') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('adjustment_details_report'); ?></span>
											</a>
										</li>
										<?php } if (($Owner || $Admin || $GP['reports-cost_adjustments']) && $Settings->accounting=='1') { ?>	
										<li id="reports_cost_adjustments">
											<a href="<?= site_url('reports/cost_adjustments') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('cost_adjustments_report'); ?></span>
											</a>
										</li>
										
									<?php } ?>
									</ul>
								</li>
							<!-- end inventory submenu -->
							<!-- start transfer -->
							<li id="reports_transfer" class="transfer <?= strtolower($this->router->fetch_method()) == 'transfer' ? 'mm_pos' : '' ?>">
									<a class="dropmenu" href="#">
										<i class="fa fa-exchange"></i>
										<span class="text"> <?= lang('transfer'); ?> 
										</span> <span class="chevron closed"></span>
									</a>
									<ul>
										<?php } if(($Owner || $Admin || $GP['reports-transfers']) && !$this->config->item('one_warehouse')){?>
										<li id="reports_transfers">
											<a href="<?= site_url('reports/transfers') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('transfers_report'); ?></span>
											</a>
										</li>
										<li id="reports_transfer_details">
											<a href="<?= site_url('reports/transfer_details') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('transfer_details_report'); ?></span>
											</a>
										</li>
									</ul>
								</li>
							<!-- End of transfer -->
							<!-- sale submenu-->
							<?php } if ($this->config->item('sale') && ($Owner || $Admin || $GP['sales-receive_payments'] || $GP['sales-salesman_commissions'] || $GP['pos-index'] || $GP['sales-index'] || $GP['sales-fuel_sale-index'] || $GP['sales-deliveries'] || $GP['sales-gift_cards'] || $GP['sales-member_cards'] || $GP['quotes-index']  || $GP['sale_orders-index'])) { ?>
								<li id="reports_sales" class="mm_sales  mm_deliveries mm_quotes mm_sale_orders <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
									<a class="dropmenu" href="#">
										<i class="fa fa-line-chart"></i>
										<span class="text"> <?= lang('sale'); ?> 
										</span> <span class="chevron closed"></span>
									</a>
									<ul>
									<?php  if ($Owner || $Admin || $GP['reports-daily_sales']) { ?>
										<li id="reports_daily_sales">
											<a href="<?= site_url('reports/daily_sales') ?>">
												<i class="fa fa-calendar"></i><span class="text"> <?= lang('daily_sales'); ?></span>
											</a>
										</li>
										<li id="reports_daily_sale_lists">
											<a href="<?= site_url('reports/daily_sale_lists') ?>">
												<i class="fa fa-calendar"></i><span class="text"> <?= lang('daily_sale_lists'); ?></span>
											</a>
										</li>
									<?php } if ($Owner || $Admin || $GP['reports-monthly_sales']) { ?>	
										<li id="reports_monthly_sales">
											<a href="<?= site_url('reports/monthly_sales') ?>">
												<i class="fa fa-calendar"></i><span class="text"> <?= lang('monthly_sales'); ?></span>
											</a>
										</li>
									
									<?php } if ($Owner || $Admin || $GP['reports-sales']) { ?>
										<li id="reports_sales">
											<a href="<?= site_url('reports/sales') ?>">
												<i class="fa fa-heart"></i><span class="text"> <?= lang('sales_report'); ?></span>
											</a>
										</li>
									<?php } if ($Owner || $Admin || $GP['reports-sale_details_report']) { ?>	
										<li id="reports_sale_details_report">
											<a href="<?= site_url('reports/sales_detail') ?>">
												<i class="fa fa-heart"></i><span class="text"> <?= lang('sale_details_report'); ?></span>
											</a>
										</li>
									<?php } if ($Owner || $Admin || $GP['reports-product_sales_report']) { ?>	
											<li id="reports_product_sales_report">
												<a href="<?= site_url('reports/product_sales_report') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('product_sales_report'); ?></span>
												</a>
											</li>
									<?php } if ($Owner || $Admin || $GP['reports-product_sale_by_customer_report']) { ?>	
										<li id="reports_product_sale_by_customer_report">
											<a href="<?= site_url('reports/product_sale_by_customer_report') ?>">
												<i class="fa fa-heart"></i><span class="text"> <?= lang('product_sale_by_customer_report'); ?></span>
											</a>
										</li>
										<?php } ?>
									</ul>
								</li>
						

									<!-- End of sale submenu -->
									<!-- start saleman -->
							<?php } if (($Owner || $Admin || $GP['reports-saleman_report'])  && $this->config->item('saleman')==true) { ?>

								<li id="reports_salesman" class="salesman">
									<a class="dropmenu" href="#">
										<i class="fa fa-briefcase"></i>
										<span class="text"> <?= lang('saleman'); ?></span> 
										<span class="chevron closed"></span>
									</a>
									<ul>
										<li id="reports_saleman_report">
											<a href="<?= site_url('reports/saleman_report') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('saleman_report'); ?></span>
											</a>
										</li>
										<li id="reports_saleman_group_report">
											<a href="<?= site_url('reports/saleman_group_report') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('saleman_group_report'); ?></span>
											</a>
										</li>
										<?php  if (($Owner || $Admin || $GP['reports-saleman_detail_report']) && $this->config->item('saleman')==true) { ?>	
										<li id="reports_saleman_detail_report">
											<a href="<?= site_url('reports/saleman_detail_report') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('saleman_detail_report'); ?></span>
											</a>
										</li>
										<li id="reports_saleman_products_report">
											<a href="<?= site_url('reports/saleman_products_report') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('saleman_products_report'); ?></span>
											</a>
										</li>
										<?php } if(($Owner || $Admin || $GP['reports-salesman_commission_report']) && $this->config->item('saleman_commission')){ ?>
										<li id="reports_salesman_commission_report">
											<a href="<?= site_url('reports/salesman_commission_report') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('salesman_commission_report'); ?></span>
											</a>
										</li>
										<li id="reports_salesman_commission_detail">
											<a href="<?= site_url('reports/salesman_commission_detail') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('salesman_commission_detail'); ?></span>
											</a>
										</li>
										<?php } if($Settings->product_commission == 1){ ?>
											<li id="reports_salesman_product_commissions">
												<a href="<?= site_url('reports/salesman_product_commissions') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('salesman_product_commissions'); ?></span>
												</a>
											</li>
										<?php } ?>
									</ul>
								</li>

							<?php } ?>
									<!-- End of saleman -->
									<!-- satrt purchase submenu -->
							<?php  if ($this->config->item('purchase') && ($Owner || $Admin || $GP['purchases-receives'] || $GP['purchases-index'] || $GP['purchases-expenses'] || $GP['purchase_requests-index'] || $GP['purchase_orders-index'])) { ?>
								<li id="welcomeDistributionPurchase" class="mm_purchases mm_purchase_orders mm_purchase_requests">
									<a class="dropmenu" href="#">
										<i class="fa fa-shopping-cart"></i>
										<span class="text"> <?= lang('purchase'); ?> 
										</span> <span class="chevron closed"></span>
									</a>
									<ul>
										<?php } if($this->config->item('purchase')){
											if ($Owner || $Admin || $GP['reports-purchases']) { ?>	
											<li id="reports_purchases">
												<a href="<?= site_url('reports/purchases') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('purchases_report'); ?></span>
												</a>
											</li>	
											<?php } if ($Owner || $Admin || $GP['reports-purchases_detail']) { ?>	
											<li id="reports_purchases_detail">
												<a href="<?= site_url('reports/purchases_detail') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('purchases_detail_report'); ?></span>
												</a>
											</li>
											<?php } if ($Owner || $Admin || $GP['reports-receive_items_report']) { ?>	
											<li id="reports_receive_items_report">
												<a href="<?= site_url('reports/receive_items_report') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('receive_items_report'); ?></span>
												</a>
											</li>
											<?php } if ($Owner || $Admin || $GP['reports-product_purchases_report']) { ?>	
											<li id="reports_product_purchases_report" class="hidden">
												<a href="<?= site_url('reports/product_purchases_report') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('product_purchases_report'); ?></span>
												</a>
											</li>

										<?php } ?>
									</ul>
								</li>	
								<!-- end of purcase submenu -->
								<!-- start expense submenu -->
								<li>
									<a class="dropmenu" href="#">
										<i class="fa fa-money"></i>
										<span class="text big_fonts"> <?= lang('expense'); ?> </span>
										<span class="chevron closed"></span>
									</a>
									<ul>
										<?php } if ($Owner || $Admin || $GP['reports-expenses']) { ?>	
										<li id="reports_daily_expenses">
											<a href="<?= site_url('reports/daily_expenses') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('daily_expenses_report'); ?></span>
											</a>
										</li>
										<li id="reports_expenses">
											<a href="<?= site_url('reports/expenses') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('expenses_report'); ?></span>
											</a>
										</li>
										<li id="reports_expense_details">
											<a href="<?= site_url('reports/expense_details') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('expense_details_report'); ?></span>
											</a>
										</li>
									</ul>
								</li>
								<!-- end of expense submenu -->
								<!-- start payment submenu -->
								<li>
									<a class="dropmenu" href="#">
										<i class="fa fa-usd"></i>
										<span class="text big_fonts"> <?= lang('payment'); ?> </span>
										<span class="chevron closed"></span>
									</a>
									<ul>
										<li id="reports_sale_payments_report">
											<a href="<?= site_url('reports/sale_payments_report') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('sale_payments_report'); ?></span>
											</a>
										</li>
										<li id="reports_purchase_payments_report">
											<a href="<?= site_url('reports/purchase_payments_report') ?>">
												<i class="fa fa-money"></i><span class="text"> <?= lang('purchase_payments_report'); ?></span>
											</a>
										</li>
										<li id="reports_payments">
											<a href="<?= site_url('reports/payments') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('payments_report'); ?></span>
											</a>
										</li>
										<?php } if ($Owner || $Admin || $GP['reports-profit_loss']) { ?>	
										<li id="reports_profit_loss">
											<a href="<?= site_url('reports/profit_loss') ?>">
												<i class="fa fa-sign-out"></i><span class="text"> <?= lang('profit_and_loss'); ?></span>
											</a>
										</li>
									</ul>
								</li>
								<!-- end of payment subenu -->
								<!-- start account submneu -->
								<?php } if($Settings->accounting && ($Owner || $Admin || $GP['accountings-bank_reconciliations'] || $GP['accountings-cash_flow'] || $GP['accountings-income_statement'] || $GP['accountings-balance_sheet'] || $GP['accountings-trial_balance'] || $GP['accountings-cash_books'] || $GP['accountings-general_ledger'] || $GP['accountings-enter_journals'] || $GP['accountings-index'] || $GP['accountings-journals'])){ ?>
								<li id="welcomeFinance" class="mm_accountings <?= strtolower($this->router->fetch_method()) == 'accountings' ? 'mm_accounting' : '' ?>">
									<a class="dropmenu" href="#">
										<i class="fa fa-calculator"></i>
										<span class="text"> <?= lang('accounting'); ?> 
										</span> <span class="chevron closed"></span>
									</a>
									<ul>
										<?php } if($this->config->item('ar_ap_aging')){
											if ($Owner || $Admin || $GP['reports-ar_customer']) { ?>	
											<li id="reports_ar_customer">
												<a href="<?= site_url('reports/ar_customer') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('ar_customer'); ?></span>
												</a>
											</li>
											<?php } if ($Owner || $Admin || $GP['reports-ap_supplier']) { ?>	
											<li id="reports_ap_supplier">
												<a href="<?= site_url('reports/ap_supplier') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('ap_supplier'); ?></span>
												</a>
											</li>
											<?php } if ($Owner || $Admin || $GP['reports-ar_aging']) { ?>	
											<li id="reports_ar_aging">
												<a href="<?= site_url('reports/ar_aging') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('ar_aging'); ?></span>
												</a>
											</li>
											<?php } if ($Owner || $Admin || $GP['reports-ap_aging']) { ?>	
											<li id="reports_ap_aging">
												<a href="<?= site_url('reports/ap_aging') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('ap_aging'); ?></span>
												</a>
											</li>	
										<?php } ?>
									</ul>
								</li>
								<!-- end of account submenu -->
								<!-- other report -->
								<li>
									<a class="dropmenu" href="#">
										<i class="fa fa-folder-open"></i>
										<span class="text big_fonts"> <?= lang('other_report'); ?> </span>
										<span class="chevron closed"></span>
									</a>
									<ul>
										<?php } if($this->config->item('inventory')){
											if ($Owner || $Admin || $GP['reports-warehouse_stock']) { ?>
											<li id="reports_warehouse_stock">
												<a href="<?= site_url('reports/warehouse_stock') ?>">
													<i class="fa fa-sign-out"></i>
													<span class="text"> <?= lang('warehouse_stock'); ?></span>
												</a>
											</li>
											<?php } if ($Owner || $Admin || $GP['reports-categories_chart']) { ?>
											<li id="reports_categories_chart">
												<a href="<?= site_url('reports/categories_chart') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('categories_chart'); ?></span>
												</a>
											</li>
										    <?php } if ($this->config->item('sale') && ($Owner || $Admin || $GP['reports-customers'])) { ?>	
											<li id="reports_customers">
												<a href="<?= site_url('reports/customers') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('customers_report'); ?></span>
												</a>
											</li>
											<?php } if ($this->config->item('purchase') && ($Owner || $Admin || $GP['reports-suppliers'])) { ?>	
											<li id="reports_suppliers">
												<a href="<?= site_url('reports/suppliers') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('suppliers_report'); ?></span>
												</a>
											</li>
											<?php } if ($Owner || $Admin || $GP['reports-users']) { ?>	
											<li id="reports_users">
												<a href="<?= site_url('reports/users') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('staff_report'); ?></span>
												</a>
											</li>
											<?php } if ($Owner || $Admin || $GP['reports-audit_trails']) { ?>	
											<li id="reports_audit_trails">
												<a href="<?= site_url('reports/audit_trails') ?>">
													<i class="fa fa-sign-out"></i><span class="text"> <?= lang('audit_trails'); ?></span>
												</a>
											</li>
										<?php } ?>
									</ul>
								</li>
								<!-- end ohter report -->
							</ul>
						</li>
					<?php } ?>
					</ul>
				</div>
				
			</div></td>

			<td class="content-con">
			<div id="content">
			<div class="row">
				<div class="col-sm-12 col-md-12 hidden">
					<ul class="breadcrumb">
						<?php
						foreach ($bc as $b) {
							if ($b['link'] === '#') {
								echo '<li class="active">' . $b['page'] . '</li>';
							} else {
								echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
							}
						}
						?>
						<li class="right_log hidden-xs">
							<?= lang('your_ip') . ' ' . $ip_address . " <span class='hidden-sm'>( " . lang('last_login_at') . ": " . date($dateFormats['php_ldate'], $this->session->userdata('old_last_login')) . " " . ($this->session->userdata('last_ip') != $ip_address ? lang('ip:') . ' ' . $this->session->userdata('last_ip') : '') . " )</span>" ?>
						</li>
					</ul>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<?php if ($message) { ?>
						<div class="alert alert-success">
							<button data-dismiss="alert" class="close" type="button">×</button>
							<?= $message; ?>
						</div>
					<?php } ?>
					<?php if ($error) { ?>
						<div class="alert alert-danger">
							<button data-dismiss="alert" class="close" type="button">×</button>
							<?= $error; ?>
						</div>
					<?php } ?>
					<?php if ($warning) { ?>
						<div class="alert alert-warning">
							<button data-dismiss="alert" class="close" type="button">×</button>
							<?= $warning; ?>
						</div>
					<?php } ?>
					<?php
					if ($info) {
						foreach ($info as $n) {
							if (!$this->session->userdata('hidden' . $n->id)) {
								?>
								<div class="alert alert-info">
									<a href="#" id="<?= $n->id ?>" class="close hideComment external"
									   data-dismiss="alert">&times;</a>
									<?= $n->comment; ?>
								</div>
							<?php }
						}
					} ?>
				</div>	
			</div>
			<div class="alerts-con"></div>
	<?php } else if ($style_view=='classic_view') {
		if(!isset($_COOKIE['cus_style'])){
			$_COOKIE['cus_style'] = $this->session->userdata('cus_style');
		}
		$class_backgroud_color = "#000000"; 
		$over_color = "#202020"; 
		if(isset($_COOKIE['cus_style'])){
			if($_COOKIE['cus_style'] == "light"){
				$over_color = "#2e0663"; 
				$class_backgroud_color = "#561da1";
			}else if($_COOKIE['cus_style'] == "blue"){
				$over_color = "#144882"; 
				$class_backgroud_color = "#206da8";
			}else if($_COOKIE['cus_style'] == "pink"){
				$over_color = "#9e3795"; 
				$class_backgroud_color = "#840d48";
			}else if($_COOKIE['cus_style'] == "green"){
				$over_color = "#058205"; 
				$class_backgroud_color = "#1aa570";
			}
		}
		
	
	?>	f
	<div id="content">
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<ul class="breadcrumb">
					<?php
					foreach ($bc as $b) {
						if ($b['link'] === '#') {
							echo '<li class="active">' . $b['page'] . '</li>';
						} else {
							echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
						}
					}
					?>
					<!--<li class="right_log hidden-xs">
						<?= lang('your_ip') . ' ' . $ip_address . " <span class='hidden-sm'>( " . lang('last_login_at') . ": " . date($dateFormats['php_ldate'], $this->session->userdata('old_last_login')) . " " . ($this->session->userdata('last_ip') != $ip_address ? lang('ip:') . ' ' . $this->session->userdata('last_ip') : '') . " )</span>" ?>
					</li>-->
				</ul>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<?php if ($message) { ?>
					<div class="alert alert-success">
						<button data-dismiss="alert" class="close" type="button">×</button>
						<?= $message; ?>
					</div>
				<?php } ?>
				<?php if ($error) { ?>
					<div class="alert alert-danger">
						<button data-dismiss="alert" class="close" type="button">×</button>
						<?= $error; ?>
					</div>
				<?php } ?>
				<?php if ($warning) { ?>
					<div class="alert alert-warning">
						<button data-dismiss="alert" class="close" type="button">×</button>
						<?= $warning; ?>
					</div>
				<?php } ?>
				<?php
				if ($info) {
					foreach ($info as $n) {
						if (!$this->session->userdata('hidden' . $n->id)) {
							?>
							<div class="alert alert-info">
								<a href="#" id="<?= $n->id ?>" class="close hideComment external"
								   data-dismiss="alert">&times;</a>
								<?= $n->comment; ?>
							</div>
						<?php }
					}
				} ?>
			</div>
		</div>
	<div class="alerts-con"></div>
<?php } ?>	
