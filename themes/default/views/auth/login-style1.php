<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <script type="text/javascript">if (parent.frames.length !== 0) {
            top.location = '<?=site_url('pos')?>';
        }</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="<?= $assets ?>js/respond.min.js"></script>
    <![endif]-->
    <style>
        .btn {
            margin-top: 10px;
        }

        span#recaptcha_privacy {
            display: none;
        }

        .recaptchatable #recaptcha_response_field {
            height: 30px;
            border-right: 1px solid #CCA940 !important;
        }
    </style>

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
<div class="container">
    <div class="row">
        <div class="col-sm-8">            
			<?php foreach($users as $user){ ?>
				<button class="btn btn-default btn-lg account" id="<?= $user->username  ?>">					
                        <img alt="" src="<?= $user->avatar ? site_url() . 'assets/uploads/avatars/thumbs/' . $user->avatar : base_url('assets/images/' . $user->gender . '.png'); ?>" class="mini_avatar img-rounded">
                        <div class="user">
                            <span><?= $user->username ?></span>
                        </div>                    
				</button>
			<?php } ?>
        </div>
		<div class="col-sm-4">
			
			<div class="login-box login-shadow">
			
				<div class="text-center">
					<?php if ($Settings->logo2) {
							echo '<img src="' . site_url() . 'assets/uploads/logos/' . $Settings->logo2 . '" alt="' . $Settings->site_name . '" style="margin-bottom:10px;" />';
						  }
					?>
				</div>
				
				<div style="clear:both; height:20px;"></div>
				
				<div class="header bblue">
					<?= lang('please_login') ?>
				</div>
				<?php if ($Settings->mmode) { ?>
					<div class="alert alert-warning">
						<button data-dismiss="alert" class="close" type="button">×</button>
						<?= lang('site_is_offline') ?>
					</div>
				<?php }
				if ($error) { ?>
					<div class="alert alert-danger">
						<button data-dismiss="alert" class="close" type="button">×</button>
						<ul class="list-group"><?= $error; ?></ul>
					</div>
				<?php }
				if ($message) { ?>
					<div class="alert alert-success ">
						<button data-dismiss="alert" class="close" type="button">×</button>
						<ul class="list-group"><?= $message; ?></ul>
					</div>
				<?php } ?>
			
				<?php echo form_open("auth/login", 'class="login" data-toggle="validator1"'); ?>	
				
				<fieldset class="col-sm-12">
					 <div class="form-group">
						<div class="controls row">
							<div class="input-group col-sm-12">
								<input type="text" id="identity" name="identity" class="form-control" placeholder="<?= lang("username"); ?>" required="required"/>								
								<span class="input-group-addon"><i class="fa fa-user"></i></span>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="controls row">
							<div class="input-group col-sm-12">
								<?php echo form_input($password); ?>
								<span class="input-group-addon"><i class="fa fa-key"></i></span>
							</div>
						</div>
					</div>
					
					<div class="confirm">
						<?php echo form_checkbox('remember', '1', FALSE, 'id="remember"'); ?>
						<label for="remember"><?= lang('remember_me') ?></label>
					</div>
					
					<?php if ($Settings->captcha) { ?>
						<div class="form-group">
							<div class="controls row">
								<div class="row">
									<div class="col-sm-5">
										<span class="captcha-image"><?php echo $image; ?></span>
									</div>
									<div class="col-sm-7">
										<div class="input-group">
											<?php echo form_input($captcha); ?>
											<span class="input-group-addon">
												<a href="#" class="reload-captcha">
													<i class="fa fa-refresh"></i>
												</a>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>

					<?php } ?>

					<div class="row">
						<?php echo form_submit('submit', lang('login'), 'class="btn btn-primary col-xs-12"'); ?>
					</div>
						
				</fieldset>
			
				<?php echo form_close(); ?>
				
				<div class="clearfix"></div>
			
		</div>
    </div>
</div>


<script src="<?= $assets ?>js/jquery.js"></script>
<script src="<?= $assets ?>js/bootstrap.min.js"></script>
<script src="<?= $assets ?>js/validator.js"></script>
<script src="<?= $assets ?>js/jquery.cookie.js"></script>
<script src="<?= $assets ?>js/select2.min.js"></script>
<style type="text/css">
	.btn-lg{
		text-transform: capitalize;
		color:#428BCA !important;
		font-size:14px;
	}
	.btn-lg img{
		width:80px;
	}
</style>
<script>
    $(document).ready(function () {
		$("select").select2();
        if ($("#identity").val() == "") {
            $("#identity").focus()
        } else {
            $("#password").focus();
        }
        $('.reload-captcha').click(function (event) {
            event.preventDefault();
            $.ajax({
                url: '<?=base_url();?>auth/reload_captcha',
                success: function (data) {
                    $('.captcha-image').html(data);
                }
            });
        });
        if ($.cookie('the_style')) {
            if ($.cookie('the_style') == 'light') {
                if ($('body').hasClass('bblack')) {
                    $('body, #content').removeClass('bblack');
                }
                if ($('body').hasClass('bblue')) {
                    $('body, #content').removeClass('bblue');
                }
            }
            if ($.cookie('the_style') == 'blue') {
                $('body, #content').addClass('bblue');
                $('.header').addClass('bblack');
            }
            if ($.cookie('the_style') == 'black') {
                $('body, #content').addClass('bblack');
            }
        } else {
            $('body, #content').addClass('bblue');
        }
		
		$(".account").on("click",function(){
			$user = $(this).attr("id"), $identify = $("#identity"),$account = $(".account"), $password = $("#password");
			
			$account.removeClass("byellow");
			$(this).addClass("byellow");
			
			$identify.val($user);
			$password.focus();
			
		});
		
    });
</script>

</body>
</html>