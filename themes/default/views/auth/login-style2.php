<?php defined('BASEPATH') or exit('No direct script access allowed');
$bgs = glob(VIEWPATH . 'default/assets/images/login-bgs/*.jpg');
foreach ($bgs as &$bg) {
    $af = explode('assets/', $bg);
    $bg = $assets . $af[1];
}
// $this->sma->print_arrays($bgs);
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/helpers/login.css" rel="stylesheet"/>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <!--[if lt IE 9]>
    <script src="<?= $assets ?>js/respond.min.js"></script>
    <![endif]-->
    <style>
        body {
            min-width: 350px;
        }
        .bblue {
            background: #fff !important;
        }
        .login-page .page-back {
           
            align-items: center;
            flex-direction: column;
            justify-content: center;
            background-size: cover !important;
            background-position: center !important;
            background-image: url("<?= $bgs[mt_rand(0, count($bgs) - 1)] ?>") !important;
            left: 0;
            overflow: hidden;
            position: fixed;
            top: 0;
            transition: opacity 1s ease 0s;
            width: 100%;
            z-index: 0;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }
        .contents {
            border-radius: 6px;
            background: #fff;
            margin-right: 50px;
        }
        .login-content, .login-page .login-form-links {
            margin-top: 20px;
            border-radius: 6px;
        }
        .login_right{
            float: right;
            background-color: #fff;
            height: 100%;

        }
    </style>

</head>

<body class="login-page">
    <noscript>
        <div class="global-site-notice noscript">
            <div class="notice-inner">
                <p>
                    <strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                    your browser to utilize the functionality of this website.
                </p>
            </div>
        </div>
    </noscript>
    <div class="page-back" style="margin-right:100px !important;">

        <div class="login_right">
           <div class="contents">
            <div  style="margin-top:50px;"></div>
            <div class="text-center">
                <?php if ($Settings->logo2) {
                    echo '<img src="' . base_url('assets/uploads/logos/' . $Settings->logo2) . '" alt="' . $Settings->site_name . '" style="margin-bottom:10px;" />';
                } ?>
            </div>
            <div  style="margin-top:100px;"></div>

            <div id="login">
                <div class="container">

                    <div class="login-form-div">
                        <div class="login-content">
                            <?php if ($Settings->mmode) {
                                ?>
                                <div class="alert alert-warning">
                                    <button data-dismiss="alert" class="close" type="button">×</button>
                                    <?= lang('site_offline') ?>
                                </div>
                                <?php
                                }
                            if ($error) {
                                ?>
                                <div class="alert alert-danger">
                                    <button data-dismiss="alert" class="close" type="button">×</button>
                                    <ul class="list-group"><?= $error; ?></ul>
                                </div>
                                <?php
                            }
                            if ($message) {
                                ?>
                                <div class="alert alert-success">
                                    <button data-dismiss="alert" class="close" type="button">×</button>
                                    <ul class="list-group"><?= $message; ?></ul>
                                </div>
                                <?php
                            }
                            ?>
                            <?php echo form_open('auth/login', 'class="login" data-toggle="validator"'); ?>
                            
                            <div class="col-sm-12">
                                <div class="textbox-wrap form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                        <input type="text" required="required" class="form-control" name="identity"
                                        placeholder="<?= lang('username') ?>"/>
                                    </div>
                                </div>
                                <div class="textbox-wrap form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                        <input type="password" required="required" class="form-control " name="password"
                                        placeholder="<?= lang('pw') ?>"/>
                                    </div>
                                </div>
                            </div>

                            <div class="form-action col-sm-12">
                                <div class="checkbox pull-left">
                                </div>
                                <button type="submit" class="btn btn-info pull-right login_submit"><?= lang('sign_in') ?> &nbsp; <i class="fa fa-sign-in"></i></button>
                            </div>
                            <div class="form-action col-sm-12 login_copyright">
                                <span><?= lang("Copyright © 2017-2023 CuscenHMS All Rights Reserved.") ?></span><br>
                                <span><?= lang("Version 2023.03.001") ?></span>
                            </div>
                            <?php echo form_close(); ?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="forgot_password" style="display: none;">
                <div class=" container">
                    <div class="login-form-div">
                        <div class="login-content">
                            <?php
                            if ($error) {
                                ?>
                                <div class="alert alert-danger">
                                    <button data-dismiss="alert" class="close" type="button">×</button>
                                    <ul class="list-group"><?= $error; ?></ul>
                                </div>
                                <?php
                            }
                            if ($message) {
                                ?>
                                <div class="alert alert-success">
                                    <button data-dismiss="alert" class="close" type="button">×</button>
                                    <ul class="list-group"><?= $message; ?></ul>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="div-title col-sm-12">
                                <h3 class="text-primary"><?= lang('forgot_password') ?></h3>
                            </div>
                            <?php echo form_open('auth/forgot_password', 'class="login" data-toggle="validator"'); ?>
                            <div class="col-sm-12">
                                <p>
                                    <?= lang('type_email_to_reset'); ?>
                                </p>
                                <div class="textbox-wrap form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon "><i class="fa fa-envelope"></i></span>
                                        <input type="email" name="forgot_email" class="form-control "
                                        placeholder="<?= lang('email_address') ?>" required="required"/>
                                    </div>
                                </div>
                                <div class="form-action">
                                    <a class="btn btn-success pull-left login_link" href="#login">
                                        <i class="fa fa-chevron-left"></i> <?= lang('back') ?>
                                    </a>
                                    <button type="submit" class="btn btn-primary pull-right">
                                        <?= lang('submit') ?> &nbsp;&nbsp; <i class="fa fa-envelope"></i>
                                    </button>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            
               
        </div>
        </div>
    </div>

    <script src="<?= $assets ?>js/jquery.js"></script>
    <script src="<?= $assets ?>js/bootstrap.min.js"></script>
    <script src="<?= $assets ?>js/jquery.cookie.js"></script>
    <script src="<?= $assets ?>js/login.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            localStorage.clear();
            var hash = window.location.hash;
            if (hash && hash != '') {
                $("#login").hide();
                $(hash).show();
            }
        });
    </script>
</body>
</html>
