<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$wm = array('0' => lang('no'), '1' => lang('yes'));
$ps = array('0' => lang("disable"), '1' => lang("enable"));
?>

<script>
    $(document).ready(function() {
        <?php if (isset($message)) {
            echo 'localStorage.clear();';
        } ?>
        var timezones = <?= json_encode(DateTimeZone::listIdentifiers(DateTimeZone::ALL)); ?>;
        $('#timezone').autocomplete({
            source: timezones
        });
        if ($('#protocol').val() == 'smtp') {
            $('#smtp_config').slideDown();
        } else if ($('#protocol').val() == 'sendmail') {
            $('#sendmail_config').slideDown();
        }
        $('#protocol').change(function() {
            if ($(this).val() == 'smtp') {
                $('#sendmail_config').slideUp();
                $('#smtp_config').slideDown();
            } else if ($(this).val() == 'sendmail') {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideDown();
            } else {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideUp();
            }
        });
        $('#overselling').change(function() {
            if ($(this).val() == 1) {
                if ($('#accounting_method').select2("val") != 2) {
                    bootbox.alert('<?= lang('overselling_will_only_work_with_AVCO_accounting_method_only') ?>');
                    $('#accounting_method').select2("val", '2');
                }
            }
        });
        $('#accounting_method').change(function() {
            var oam = <?= $Settings->accounting_method ?>,
                nam = $(this).val();
            if (oam != nam) {
                bootbox.alert('<?= lang('accounting_method_change_alert') ?>');
            }
        });
        $('#accounting_method').change(function() {
            if ($(this).val() != 2) {
                if ($('#overselling').select2("val") == 1) {
                    bootbox.alert('<?= lang('overselling_will_only_work_with_AVCO_accounting_method_only') ?>');
                    $('#overselling').select2("val", 0);
                }
            }
        });
        $('#item_addition').change(function() {
            if ($(this).val() == 1) {
                bootbox.alert('<?= lang('product_variants_feature_x') ?>');
            }
        });
        var sac = $('#sac').val()
        if (sac == 1) {
            $('.nsac').slideUp();
        } else {
            $('.nsac').slideDown();
        }
        $('#sac').change(function() {
            if ($(this).val() == 1) {
                $('.nsac').slideUp();
            } else {
                $('.nsac').slideDown();
            }
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('system_settings'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?= site_url('system_settings/paypal') ?>" class="toggle_up"><i class="icon fa fa-paypal"></i><span class="padding-right-10"><?= lang('paypal'); ?></span></a></li>
                <li class="dropdown"><a href="<?= site_url('system_settings/skrill') ?>" class="toggle_down"><i class="icon fa fa-bank"></i><span class="padding-right-10"><?= lang('skrill'); ?></span></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("system_settings", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="alert alert-info" role="alert">
                            <p>
                                <strong>Cron Job:</strong> <code>0 1 * * * wget -qO- <?= site_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</code> to run at 1:00 AM daily. For local installation, you can run cron job manually at any time.
                                <?php if (!DEMO) { ?>
                                    <a class="btn btn-primary btn-xs pull-right" target="_blank" href="<?= site_url('cron/run'); ?>">Run cron job now</a>
                                <?php } ?>
                            </p>
                        </div>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('site_config') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("site_name", "site_name"); ?>
                                    <?= form_input('site_name', $Settings->site_name, 'class="form-control tip" id="site_name1"  required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("language", "language"); ?>
                                    <?php
                                    $lang = array(
                                        'english'                   => 'English',
                                        'khmer'                        => 'Khmer',
                                        'simplified-chinese'        => 'Simplified Chinese',
                                        'thai'                      => 'Thai',

                                    );
                                    echo form_dropdown('language', $lang, $Settings->language, 'class="form-control tip" id="language" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="currency"><?= lang("default_currency"); ?></label>

                                    <div class="controls"> <?php
                                                            foreach ($currencies as $currency) {
                                                                $cu[$currency->code] = $currency->name;
                                                            }
                                                            echo form_dropdown('currency', $cu, $Settings->default_currency, 'class="form-control tip" id="currency" required="required" style="width:100%;"');
                                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("accounting_method", "accounting_method"); ?>
                                    <?php
                                    $am = array(0 => 'FIFO (First In First Out)', 1 => 'LIFO (Last In First Out)', 2 => 'AVCO (Average Cost Method)', 3 => 'By Product');
                                    echo form_dropdown('accounting_method', $am, $Settings->accounting_method, 'class="form-control tip" id="accounting_method" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="email"><?= lang("default_email"); ?></label>

                                    <?= form_input('email', $Settings->default_email, 'class="form-control tip" required="required" id="email"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="customer_group"><?= lang("default_customer_group"); ?></label>
                                    <?php
                                    foreach ($customer_groups as $customer_group) {
                                        $pgs[$customer_group->id] = $customer_group->name;
                                    }
                                    echo form_dropdown('customer_group', $pgs, $Settings->customer_group, 'class="form-control tip" id="customer_group" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="price_group"><?= lang("default_price_group"); ?></label>
                                    <?php
                                    foreach ($price_groups as $price_group) {
                                        $cgs[$price_group->id] = $price_group->name;
                                    }
                                    echo form_dropdown('price_group', $cgs, $Settings->price_group, 'class="form-control tip" id="price_group" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4" style="display:none">
                                <div class="form-group">
                                    <?= lang('maintenance_mode', 'mmode'); ?>
                                    <div class="controls"> <?php
                                                            echo form_dropdown('mmode', $wm, (isset($_POST['mmode']) ? $_POST['mmode'] : $Settings->mmode), 'class="tip form-control" required="required" id="mmode" style="width:100%;"');
                                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="theme"><?= lang("theme"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $themes = array(
                                            'default' => 'Default'
                                        );
                                        echo form_dropdown('theme', $themes, $Settings->theme, 'id="theme" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="style_view"><?= lang("Style View"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $style_views = array(
                                            'standard_view' => 'Standard',
                                            'classic_view' => 'Classic'
                                        );
                                        echo form_dropdown('style_view', $style_views, $this->session->userdata('style_view'), 'id="style_view" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="rtl"><?= lang("rtl_support"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('rtl', $ps, $Settings->rtl, 'id="rtl" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="captcha"><?= lang("login_captcha"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('captcha', $ps, $Settings->captcha, 'id="captcha" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="disable_editing"><?= lang("disable_editing"); ?></label>
                                    <?= form_input('disable_editing', $Settings->disable_editing, 'class="form-control tip" id="disable_editing" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="rows_per_page"><?= lang("rows_per_page"); ?></label>
                                    <?= form_input('rows_per_page', $Settings->rows_per_page, 'class="form-control tip" id="rows_per_page" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="dateformat"><?= lang("dateformat"); ?></label>

                                    <div class="controls">
                                        <?php
                                        foreach ($date_formats as $date_format) {
                                            $dt[$date_format->id] = $date_format->js;
                                        }
                                        echo form_dropdown('dateformat', $dt, $Settings->dateformat, 'id="dateformat" class="form-control tip" style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="timezone"><?= lang("timezone"); ?></label>
                                    <?php
                                    $timezone_identifiers = DateTimeZone::listIdentifiers();
                                    foreach ($timezone_identifiers as $tzi) {
                                        $tz[$tzi] = $tzi;
                                    }
                                    ?>
                                    <?= form_dropdown('timezone', $tz, TIMEZONE, 'class="form-control tip" id="timezone" required="required"'); ?>
                                </div>
                            </div>
                            <!--<div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reg_ver', 'reg_ver'); ?>
                            <div class="controls">  <?php
                                                    echo form_dropdown('reg_ver', $wm, (isset($_POST['reg_ver']) ? $_POST['reg_ver'] : $Settings->reg_ver), 'class="tip form-control" required="required" id="reg_ver" style="width:100%;"');
                                                    ?> </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('allow_reg', 'allow_reg'); ?>
                            <div class="controls">  <?php
                                                    echo form_dropdown('allow_reg', $wm, (isset($_POST['allow_reg']) ? $_POST['allow_reg'] : $Settings->allow_reg), 'class="tip form-control" required="required" id="allow_reg" style="width:100%;"');
                                                    ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reg_notification', 'reg_notification'); ?>
                            <div class="controls">  <?php
                                                    echo form_dropdown('reg_notification', $wm, (isset($_POST['reg_notification']) ? $_POST['reg_notification'] : $Settings->reg_notification), 'class="tip form-control" required="required" id="reg_notification" style="width:100%;"');
                                                    ?>
                            </div>
                        </div>
                    </div>-->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="restrict_calendar"><?= lang("calendar"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt_cal = array(1 => lang('private'), 0 => lang('shared'));
                                        echo form_dropdown('restrict_calendar', $opt_cal, $Settings->restrict_calendar, 'class="form-control tip" required="required" id="restrict_calendar" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="warehouse"><?= lang("default_warehouse"); ?></label>

                                    <div class="controls"> <?php
                                                            foreach ($warehouses as $warehouse) {
                                                                $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                                            }
                                                            echo form_dropdown('warehouse', $wh, $Settings->default_warehouse, 'class="form-control tip" id="warehouse" required="required" style="width:100%;"');
                                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("default_biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="biller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>

                            <?php if ($Settings->project) { ?>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("default_project", "project"); ?>
                                        <?php
                                        $opt_projects[""] = "";
                                        if ($projects) {
                                            foreach ($projects as $project) {
                                                $opt_projects[$project->id] = $project->name;
                                            }
                                        }
                                        echo form_dropdown('project', $opt_projects, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" data-placeholder="' . lang("select") . ' ' . lang("project") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>

                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("default_payment_term", "payment_term"); ?>
                                    <?php
                                    $pm[""] = "";
                                    foreach ($payment_terms as $payment_term) {
                                        $pm[$payment_term->id] = $payment_term->description;
                                    }
                                    echo form_dropdown('payment_term', $pm, (isset($_POST['payment_term']) ? $_POST['payment_term'] : $Settings->default_payment_term), 'id="payment_term" data-placeholder="' . lang("select") . ' ' . lang("payment_term") . '"  class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="single_login"><?= lang("single_login"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $single_logins = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('single_login', $single_logins, $Settings->single_login, 'class="form-control tip" required="required" id="single_login" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="login_time"><?= lang("login_time"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $login_times = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('login_time', $login_times, $Settings->login_time, 'class="form-control tip" required="required" id="login_time" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('payment_expense', 'payment_expense'); ?>
                                    <?= form_dropdown('payment_expense', $wm, $Settings->payment_expense, 'class="form-control" id="payment_expense" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('approval_expense', 'approval_expense'); ?>
                                    <?= form_dropdown('approval_expense', $wm, $Settings->approval_expense, 'class="form-control" id="approval_expense" required="required"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php
                                    $lm_print[0] = lang('unlimited');
                                    $lm_print[2] = lang('re-print');
                                    $lm_print[1] = lang('limited');
                                    ?>
                                    <?= lang('limit_print', 'limit_print'); ?>
                                    <?= form_dropdown('limit_print', $lm_print, $Settings->limit_print, 'class="form-control" id="limit_print" required="required"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="date_with_time"><?= lang("date_with_time"); ?></label>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('date_with_time', $wm, $Settings->date_with_time, 'class="form-control tip"  id="date_with_time" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('show_unit', 'show_unit'); ?>
                                    <?= form_dropdown('show_unit', $wm, $Settings->show_unit, 'class="form-control" id="show_unit"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('show_qoh', 'show_qoh'); ?>
                                    <?= form_dropdown('show_qoh', $wm, $Settings->show_qoh, 'class="form-control" id="show_qoh"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('receive_item_vat', 'receive_item_vat'); ?>
                                    <?= form_dropdown('receive_item_vat', $wm, $Settings->receive_item_vat, 'class="form-control" id="receive_item_vat"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('default_cash_account', 'default_cash_account'); ?>
                                    <select name="default_cash_account" id="default_cash_account" class="form-control default_cash_account">
                                        <?= $this->cus->cash_opts($Settings->default_cash_account, true, false, true); ?>
                                    </select>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('products') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("product_tax", "tax_rate"); ?>
                                    <?php
                                    echo form_dropdown('tax_rate', $ps, $Settings->default_tax_rate, 'class="form-control tip" id="tax_rate" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="racks"><?= lang("racks"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('racks', $ps, $Settings->racks, 'id="racks" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="attributes"><?= lang("attributes"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('attributes', $ps, $Settings->attributes, 'id="attributes" class="form-control tip"  required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_expiry"><?= lang("product_expiry"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('product_expiry', $ps, $Settings->product_expiry, 'id="product_expiry" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="remove_expired"><?= lang("remove_expired"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $re_opts = array(0 => lang('no') . ', ' . lang('i_ll_remove'), 1 => lang('yes') . ', ' . lang('remove_automatically'));
                                        echo form_dropdown('remove_expired', $re_opts, $Settings->remove_expired, 'id="remove_expired" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="image_size"><?= lang("image_size"); ?> (Width :
                                        Height) *</label>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <?= form_input('iwidth', $Settings->iwidth, 'class="form-control tip" id="iwidth" placeholder="image width" required="required"'); ?>
                                        </div>
                                        <div class="col-xs-6">
                                            <?= form_input('iheight', $Settings->iheight, 'class="form-control tip" id="iheight" placeholder="image height" required="required"'); ?></div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="thumbnail_size"><?= lang("thumbnail_size"); ?>
                                        (Width : Height) *</label>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <?= form_input('twidth', $Settings->twidth, 'class="form-control tip" id="twidth" placeholder="thumbnail width" required="required"'); ?>
                                        </div>
                                        <div class="col-xs-6">
                                            <?= form_input('theight', $Settings->theight, 'class="form-control tip" id="theight" placeholder="thumbnail height" required="required"'); ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('watermark', 'watermark'); ?>
                                    <?php
                                    echo form_dropdown('watermark', $wm, (isset($_POST['watermark']) ? $_POST['watermark'] : $Settings->watermark), 'class="tip form-control" required="required" id="watermark" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('display_all_products', 'display_all_products'); ?>
                                    <?php
                                    $dopts = array(0 => lang('hide_with_0_qty'), 1 => lang('show_with_0_qty'));
                                    echo form_dropdown('display_all_products', $dopts, (isset($_POST['display_all_products']) ? $_POST['display_all_products'] : $Settings->display_all_products), 'class="tip form-control" required="required" id="display_all_products" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('barcode_separator', 'barcode_separator'); ?>
                                    <?php
                                    $bcopts = array('-' => lang('-'), '.' => lang('.'), '~' => lang('~'), '_' => lang('_'));
                                    echo form_dropdown('barcode_separator', $bcopts, (isset($_POST['barcode_separator']) ? $_POST['barcode_separator'] : $Settings->barcode_separator), 'class="tip form-control" required="required" id="barcode_separator" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('barcode_renderer', 'barcode_renderer'); ?>
                                    <?php
                                    $bcropts = array(1 => lang('image'), 0 => lang('svg'));
                                    echo form_dropdown('barcode_renderer', $bcropts, (isset($_POST['barcode_renderer']) ? $_POST['barcode_renderer'] : $Settings->barcode_img), 'class="tip form-control" required="required" id="barcode_renderer" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('update_cost_with_purchase', 'update_cost'); ?>
                                    <?= form_dropdown('update_cost', $wm, $Settings->update_cost, 'class="form-control" id="update_cost" required="required"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('show_warehouse_qty', 'show_warehouse_qty'); ?>
                                    <?= form_dropdown('show_warehouse_qty', $wm, $Settings->show_warehouse_qty, 'class="form-control" id="show_warehouse_qty" required="required"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('alert_qty_by_warehouse', 'alert_qty_by_warehouse'); ?>
                                    <?= form_dropdown('alert_qty_by_warehouse', $wm, $Settings->alert_qty_by_warehouse, 'class="form-control" id="alert_qty_by_warehouse" required="required"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('cbm', 'cbm'); ?>
                                    <?= form_dropdown('cbm', $wm, $Settings->cbm, 'class="form-control" id="cbm" required="required"'); ?>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('sales') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="overselling"><?= lang("over_selling"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt = array(1 => lang('yes'), 0 => lang('no'));
                                        echo form_dropdown('restrict_sale', $opt, $Settings->overselling, 'class="form-control tip" id="overselling" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="reference_format"><?= lang("reference_format"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $ref = array(1 => lang('prefix_year_no'), 2 => lang('prefix_month_year_no'), 3 => lang('sequence_number'), 4 => lang('random_number'));
                                        echo form_dropdown('reference_format', $ref, $Settings->reference_format, 'class="form-control tip" required="required" id="reference_format" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="reference_reset"><?= lang("reference_reset"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $ref_reset = array(0 => lang('no_reset'), 1 => lang('year_reset'), 2 => lang('month_reset'));
                                        echo form_dropdown('reference_reset', $ref_reset, $Settings->reference_reset, 'class="form-control tip" required="required" id="reference_reset" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("invoice_tax", "tax_rate2"); ?>
                                    <?php $tr['0'] = lang("disable");
                                    foreach ($tax_rates as $rate) {
                                        $tr[$rate->id] = $rate->name;
                                    }
                                    echo form_dropdown('tax_rate2', $tr, $Settings->default_tax_rate2, 'id="tax_rate2" class="form-control tip" required="required" style="width:100%;"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_discount"><?= lang("product_level_discount"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('product_discount', $ps, $Settings->product_discount, 'id="product_discount" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang("product_serial"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('product_serial', $ps, $Settings->product_serial, 'id="product_serial" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="detect_barcode"><?= lang("auto_detect_barcode"); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('detect_barcode', $ps, $Settings->auto_detect_barcode, 'id="detect_barcode" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="bc_fix"><?= lang("bc_fix"); ?></label>


                                    <?= form_input('bc_fix', $Settings->bc_fix, 'class="form-control tip" required="required" id="bc_fix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="item_addition"><?= lang("item_addition"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $ia = array(0 => lang('add_new_item'), 1 => lang('increase_quantity_if_item_exist'));
                                        echo form_dropdown('item_addition', $ia, $Settings->item_addition, 'id="item_addition" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("set_focus", "set_focus"); ?>
                                    <?php
                                    $sfopts = array(0 => lang('add_item_input'), 1 => lang('last_order_item'));
                                    echo form_dropdown('set_focus', $sfopts, (isset($_POST['set_focus']) ? $_POST['set_focus'] : $Settings->set_focus), 'id="set_focus" data-placeholder="' . lang("select") . ' ' . lang("set_focus") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_view"><?= lang("invoice_view"); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt_inv = array(1 => lang('tax_invoice'), 0 => lang('standard'));
                                        echo form_dropdown('invoice_view', $opt_inv, $Settings->invoice_view, 'class="form-control tip" required="required" id="invoice_view" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="car_operation"><?= lang("car_operation"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt_car = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('car_operation', $opt_car, $Settings->car_operation, 'class="form-control tip" required="required" id="car_operation" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="qty_operation"><?= lang("qty_operation"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt_qty = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('qty_operation', $opt_qty, $Settings->qty_operation, 'class="form-control tip" required="required" id="qty_operation" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_formulation"><?= lang("product_formulation"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt_qty = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('product_formulation', $opt_qty, $Settings->product_formulation, 'class="form-control tip" required="required" id="product_formulation" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="deposit_amount_alerts"><?= lang("deposit_amount_alerts"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $deposit_amount_alerts = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('deposit_amount_alerts', $deposit_amount_alerts, $Settings->customer_deposit_alerts, 'class="form-control tip" required="required" id="deposit_amount_alerts" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($Settings->installment) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="bc_fix"><?= lang("installment_alert_days"); ?></label>
                                        <?= form_input('installment_alert_days', $Settings->installment_alert_days, 'class="form-control tip" required="required" id="installment_alert_days"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="bc_fix"><?= lang("installment_holidays"); ?></label>
                                        <?php
                                        $opt_lho = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('installment_holiday', $opt_lho, $Settings->installment_holiday, 'class="form-control tip" required="required" id="installment_holiday" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("loan")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="bc_fix"><?= lang("loan_alert_days"); ?></label>
                                        <?= form_input('loan_alert_days', $Settings->loan_alert_days, 'class="form-control tip" required="required" id="loan_alert_days"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="customer_price"><?= lang("customer_price"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $customer_prices = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('customer_price', $customer_prices, $Settings->customer_price, 'class="form-control tip" required="required" id="customer_price" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_additional"><?= lang("product_additional"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $pa = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('product_additional', $pa, $Settings->product_additional, 'class="form-control tip"  id="product_additional" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="set_custom_field"><?= lang("set_custom_field_search"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $cfs = array(1 => lang('enable'), 0 => lang('disable'));
                                        echo form_dropdown('set_custom_field', $cfs, $Settings->set_custom_field, 'class="form-control tip" required="required" id="set_custom_field" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('search_by_category', 'search_by_category'); ?>
                                    <?= form_dropdown('search_by_category', $wm, $Settings->search_by_category, 'class="form-control" id="search_by_category" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('foc', 'foc'); ?>
                                    <?= form_dropdown('foc', $wm, $Settings->foc, 'class="form-control" id="foc" required="required"'); ?>
                                </div>
                            </div>

                            <?php if ($this->config->item("room_rent")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("default_floor", "default_floor"); ?>
                                        <?php
                                        $opt_floors[""] = "";
                                        if ($floors) {
                                            foreach ($floors as $floor) {
                                                $opt_floors[$floor->id] = $floor->floor;
                                            }
                                        }
                                        echo form_dropdown('default_floor', $opt_floors, (isset($_POST['default_floor']) ? $_POST['default_floor'] : $Settings->default_floor), 'id="floor" data-placeholder="' . lang("select") . ' ' . lang("floor") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item('saleman_commission')) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('product_commission', 'product_commission'); ?>
                                        <?= form_dropdown('product_commission', $wm, $Settings->product_commission, 'class="form-control" id="product_commission" required="required"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('product_license', 'product_license'); ?>
                                    <?= form_dropdown('product_license', $wm, $Settings->product_license, 'class="form-control" id="product_license" required="required"'); ?>
                                </div>
                            </div>
                        </fieldset>


                        <?php if ($this->config->item("concretes")) { ?>
                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border"><?= lang('concretes') ?></legend>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('moving_waitings', 'moving_waitings'); ?>
                                        <?= form_dropdown('moving_waitings', $wm, $Settings->moving_waitings, 'class="form-control" id="moving_waitings"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('missions', 'missions'); ?>
                                        <?= form_dropdown('missions', $wm, $Settings->missions, 'class="form-control" id="missions"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('fuel_expenses', 'fuel_expenses'); ?>
                                        <?= form_dropdown('fuel_expenses', $wm, $Settings->fuel_expenses, 'class="form-control" id="fuel_expenses"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('errors', 'errors'); ?>
                                        <?= form_dropdown('errors', $wm, $Settings->errors, 'class="form-control" id="errors"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('absents', 'absents'); ?>
                                        <?= form_dropdown('absents', $wm, $Settings->absents, 'class="form-control" id="absents"'); ?>
                                    </div>
                                </div>
                            </fieldset>
                        <?php } ?>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('prefix') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="sales_prefix"><?= lang("sales_prefix"); ?></label>

                                    <?= form_input('sales_prefix', $Settings->sales_prefix, 'class="form-control tip" id="sales_prefix"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="sale_tax_prefix"><?= lang("sale_tax_prefix"); ?></label>
                                    <?= form_input('sale_tax_prefix', isset($Settings->sale_tax_prefix) ?  $Settings->sale_tax_prefix : '', 'class="form-control tip" id="sale_tax_prefix"'); ?>
                                </div>
                            </div>
                            <?php if ($this->config->item("receive_payment")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="rp_prefix"><?= lang("rp_prefix"); ?></label>

                                        <?= form_input('rp_prefix', $Settings->rp_prefix, 'class="form-control tip" id="rp_prefix"'); ?>
                                    </div>
                                </div>
                            <?php }
                            if ($this->config->item("saleorder")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="sale_order_prefix"><?= lang("sale_order_prefix"); ?></label>

                                        <?= form_input('sale_order_prefix', $Settings->sale_order_prefix, 'class="form-control tip" id="sale_order_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="return_prefix"><?= lang("return_prefix"); ?></label>

                                    <?= form_input('return_prefix', $Settings->return_prefix, 'class="form-control tip" id="return_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="payment_prefix"><?= lang("payment_prefix"); ?></label>
                                    <?= form_input('payment_prefix', $Settings->payment_prefix, 'class="form-control tip" id="payment_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="ppayment_prefix"><?= lang("ppayment_prefix"); ?></label>
                                    <?= form_input('ppayment_prefix', $Settings->ppayment_prefix, 'class="form-control tip" id="ppayment_prefix"'); ?>
                                </div>
                            </div>

                            <?php if ($this->config->item("deliveries")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="delivery_prefix"><?= lang("delivery_prefix"); ?></label>

                                        <?= form_input('delivery_prefix', $Settings->delivery_prefix, 'class="form-control tip" id="delivery_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("quotation")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="quote_prefix"><?= lang("quote_prefix"); ?></label>

                                        <?= form_input('quote_prefix', $Settings->quote_prefix, 'class="form-control tip" id="quote_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="purchase_prefix"><?= lang("purchase_prefix"); ?></label>

                                    <?= form_input('purchase_prefix', $Settings->purchase_prefix, 'class="form-control tip" id="purchase_prefix"'); ?>
                                </div>
                            </div>

                            <?php if ($this->config->item("purchase_order")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="purchase_order_prefix"><?= lang("purchase_order_prefix"); ?></label>

                                        <?= form_input('purchase_order_prefix', $Settings->purchase_order_prefix, 'class="form-control tip" id="purchase_order_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("purchase_request")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="purchase_request_prefix"><?= lang("purchase_request_prefix"); ?></label>

                                        <?= form_input('purchase_request_prefix', $Settings->purchase_request_prefix, 'class="form-control tip" id="purchase_request_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="returnp_prefix"><?= lang("returnp_prefix"); ?></label>

                                    <?= form_input('returnp_prefix', $Settings->returnp_prefix, 'class="form-control tip" id="returnp_prefix"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="transfer_prefix"><?= lang("transfer_prefix"); ?></label>
                                    <?= form_input('transfer_prefix', $Settings->transfer_prefix, 'class="form-control tip" id="transfer_prefix"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('expense_prefix', 'expense_prefix'); ?>
                                    <?= form_input('expense_prefix', $Settings->expense_prefix, 'class="form-control tip" id="expense_prefix"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('qa_prefix', 'qa_prefix'); ?>
                                    <?= form_input('qa_prefix', $Settings->qa_prefix, 'class="form-control tip" id="qa_prefix"'); ?>
                                </div>
                            </div>

                            <?php if ($this->config->item("stock_counts")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('count_stock_prefix', 'count_stock_prefix'); ?>
                                        <?= form_input('count_stock_prefix', $Settings->count_stock_prefix, 'class="form-control tip" id="count_stock_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($Settings->accounting) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('ca_prefix', 'ca_prefix'); ?>
                                        <?= form_input('ca_prefix', $Settings->ca_prefix, 'class="form-control tip" id="ca_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("using_stocks")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('us_prefix', 'us_prefix'); ?>
                                        <?= form_input('us_prefix', $Settings->us_prefix, 'class="form-control tip" id="us_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('rus_prefix', 'rus_prefix'); ?>
                                        <?= form_input('rus_prefix', $Settings->rus_prefix, 'class="form-control tip" id="rus_prefix"'); ?>
                                    </div>
                                </div>
                            <?php }
                            if ($this->config->item("consignments")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('csm_prefix', 'csm_prefix'); ?>
                                        <?= form_input('csm_prefix', $Settings->csm_prefix, 'class="form-control tip" id="csm_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('rcsm_prefix', 'rcsm_prefix'); ?>
                                        <?= form_input('rcsm_prefix', $Settings->rcsm_prefix, 'class="form-control tip" id="rcsm_prefix"'); ?>
                                    </div>
                                </div>
                            <?php }
                            if ($this->config->item("receive_item")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('receive_prefix', 'receive_prefix'); ?>
                                        <?= form_input('receive_prefix', $Settings->receive_prefix, 'class="form-control tip" id="receive_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('bill_prefix', 'bill_prefix'); ?>
                                    <?= form_input('bill_prefix', $Settings->bill_prefix, 'class="form-control tip" id="bill_prefix"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('customer_prefix', 'customer_prefix'); ?>
                                    <?= form_input('customer_prefix', $Settings->customer_prefix, 'class="form-control tip" id="customer_prefix"'); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('supplier_prefix', 'supplier_prefix'); ?>
                                    <?= form_input('supplier_prefix', $Settings->supplier_prefix, 'class="form-control tip" id="supplier_prefix"'); ?>
                                </div>
                            </div>

                            <?php if ($Settings->installment) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('installment_prefix', 'installment_prefix'); ?>
                                        <?= form_input('installment_prefix', $Settings->installment_prefix, 'class="form-control tip" id="installment_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("loan")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('app_prefix', 'app_prefix'); ?>
                                        <?= form_input('app_prefix', $Settings->app_prefix, 'class="form-control tip" id="app_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('loan_prefix', 'loan_prefix'); ?>
                                        <?= form_input('loan_prefix', $Settings->loan_prefix, 'class="form-control tip" id="loan_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('sav_prefix', 'sav_prefix'); ?>
                                        <?= form_input('sav_prefix', $Settings->sav_prefix, 'class="form-control tip" id="sav_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('sav_tr_prefix', 'sav_tr_prefix'); ?>
                                        <?= form_input('sav_tr_prefix', $Settings->sav_tr_prefix, 'class="form-control tip" id="sav_tr_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("pawn")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('pawn_prefix', 'pawn_prefix'); ?>
                                        <?= form_input('pawn_prefix', $Settings->pawn_prefix, 'class="form-control tip" id="pawn_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("ktv")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('customer_stock_prefix', 'customer_stock_prefix'); ?>
                                        <?= form_input('customer_stock_prefix', $Settings->customer_stock_prefix, 'class="form-control tip" id="customer_stock_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("fuel")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('fuel_sale_prefix', 'fuel_sale_prefix'); ?>
                                        <?= form_input('fuel_prefix', $Settings->fuel_prefix, 'class="form-control tip" id="fuel_sale_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("repair")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('repair_prefix', 'repair_prefix'); ?>
                                        <?= form_input('repair_prefix', $Settings->repair_prefix, 'class="form-control tip" id="repair_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('check_prefix', 'check_prefix'); ?>
                                        <?= form_input('check_prefix', $Settings->check_prefix, 'class="form-control tip" id="check_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("room_rent")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('rental_prefix', 'rental_prefix'); ?>
                                        <?= form_input('rental_prefix', $Settings->rental_prefix, 'class="form-control tip" id="rental_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("payroll")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('cv_prefix', 'cv_prefix'); ?>
                                        <?= form_input('cv_prefix', $Settings->cv_prefix, 'class="form-control tip" id="cv_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($this->config->item("concretes")) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('cdn_prefix', 'cdn_prefix'); ?>
                                        <?= form_input('cdn_prefix', $Settings->cdn_prefix, 'class="form-control tip" id="cdn_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('csale_prefix', 'csale_prefix'); ?>
                                        <?= form_input('csale_prefix', $Settings->csale_prefix, 'class="form-control tip" id="csale_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('cfuel_prefix', 'cfuel_prefix'); ?>
                                        <?= form_input('cfuel_prefix', $Settings->cfuel_prefix, 'class="form-control tip" id="cfuel_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('cmw_prefix', 'cmw_prefix'); ?>
                                        <?= form_input('cmw_prefix', $Settings->cmw_prefix, 'class="form-control tip" id="cmw_prefix"'); ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('cms_prefix', 'cms_prefix'); ?>
                                        <?= form_input('cms_prefix', $Settings->cms_prefix, 'class="form-control tip" id="cms_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('cfe_prefix', 'cfe_prefix'); ?>
                                        <?= form_input('cfe_prefix', $Settings->cfe_prefix, 'class="form-control tip" id="cfe_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('ccms_prefix', 'ccms_prefix'); ?>
                                        <?= form_input('ccms_prefix', $Settings->ccms_prefix, 'class="form-control tip" id="ccms_prefix"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('cabsent_prefix', 'cabsent_prefix'); ?>
                                        <?= form_input('cabsent_prefix', $Settings->cabsent_prefix, 'class="form-control tip" id="cabsent_prefix"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('money_number_format') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="decimals"><?= lang("decimals"); ?></label>

                                    <div class="controls"> <?php
                                                            $decimals = array(0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4');
                                                            echo form_dropdown('decimals', $decimals, $Settings->decimals, 'class="form-control tip" id="decimals"  style="width:100%;" required="required"');
                                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="qty_decimals"><?= lang("qty_decimals"); ?></label>
                                    <div class="controls"> <?php
                                                            $qty_decimals = array(0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4');
                                                            echo form_dropdown('qty_decimals', $qty_decimals, $Settings->qty_decimals, 'class="form-control tip" id="qty_decimals"  style="width:100%;" required="required"');
                                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('sac', 'sac'); ?>
                                    <?= form_dropdown('sac', $ps, set_value('sac', $Settings->sac), 'class="form-control tip" id="sac"  required="required"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="nsac">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="decimals_sep"><?= lang("decimals_sep"); ?></label>

                                        <div class="controls"> <?php
                                                                $dec_point = array('.' => lang('dot'), ',' => lang('comma'));
                                                                echo form_dropdown('decimals_sep', $dec_point, $Settings->decimals_sep, 'class="form-control tip" id="decimals_sep"  style="width:100%;" required="required"');
                                                                ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="thousands_sep"><?= lang("thousands_sep"); ?></label>
                                        <div class="controls"> <?php
                                                                $thousands_sep = array('.' => lang('dot'), ',' => lang('comma'), '0' => lang('space'));
                                                                echo form_dropdown('thousands_sep', $thousands_sep, $Settings->thousands_sep, 'class="form-control tip" id="thousands_sep"  style="width:100%;" required="required"');
                                                                ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('display_currency_symbol', 'display_symbol'); ?>
                                    <?php $opts = array(0 => lang('disable'), 1 => lang('before'), 2 => lang('after')); ?>
                                    <?= form_dropdown('display_symbol', $opts, $Settings->display_symbol, 'class="form-control" id="display_symbol" style="width:100%;" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('currency_symbol', 'symbol'); ?>
                                    <?= form_input('symbol', $Settings->symbol, 'class="form-control" id="symbol" style="width:100%;"'); ?>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('manual_product') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="manual_category"><?= lang("manual_category"); ?></label>
                                    <div class="controls"> <?php
                                                            foreach ($manual_categories as $manual_category) {
                                                                $mc[$manual_category->id] = $manual_category->name;
                                                            }
                                                            echo form_dropdown('manual_category', $mc, $Settings->manual_category, 'class="form-control tip" id="manual_category" required="required" style="width:100%;"');
                                                            ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="manual_unit"><?= lang("manual_unit"); ?></label>
                                    <div class="controls"> <?php
                                                            foreach ($manual_units as $manual_unit) {
                                                                $mu[$manual_unit->id] = $manual_unit->name;
                                                            }
                                                            echo form_dropdown('manual_unit', $mu, $Settings->manual_unit, 'class="form-control tip" id="manual_unit" required="required" style="width:100%;"');
                                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </fieldset>

                        <?php if ($Settings->accounting == '1') { ?>

                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border"><?= lang('accounting') ?></legend>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="default_cash"><?= lang("default_cash"); ?></label>
                                        <div class="controls">
                                            <select name="default_cash" class="form-control default_cash" style="width:100%">
                                                <?= $cash_account ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="retainearning_acc"><?= lang("retained_earning_account"); ?></label>
                                        <div class="controls">
                                            <select name="retainearning_acc" class="form-control retainearning_acc" style="width:100%">
                                                <?= $retainearning_account ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="default_receivable_account"><?= lang("default_receivable_account"); ?></label>
                                        <div class="controls">
                                            <select name="default_receivable_account" class="form-control default_receivable_account" style="width:100%">
                                                <option value="0"><?= lang('base_biller_setting') ?></option>
                                                <?= $receivable_account ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="default_payable_account"><?= lang("default_payable_account"); ?></label>
                                        <div class="controls">
                                            <select name="default_payable_account" class="form-control default_payable_account" style="width:100%">
                                                <option value="0"><?= lang('base_biller_setting') ?></option>
                                                <?= $payable_account ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </fieldset>
                        <?php } ?>


                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('email') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="protocol"><?= lang("email_protocol"); ?></label>

                                    <div class="controls"> <?php
                                                            $popt = array('mail' => 'PHP Mail Function', 'sendmail' => 'Send Mail', 'smtp' => 'SMTP');
                                                            echo form_dropdown('protocol', $popt, $Settings->protocol, 'class="form-control tip" id="protocol"  style="width:100%;" required="required"');
                                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="row" id="sendmail_config" style="display: none;">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="mailpath"><?= lang("mailpath"); ?></label>

                                            <?= form_input('mailpath', $Settings->mailpath, 'class="form-control tip" id="mailpath"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="row" id="smtp_config" style="display: none;">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_host"><?= lang("smtp_host"); ?></label>

                                            <?= form_input('smtp_host', $Settings->smtp_host, 'class="form-control tip" id="smtp_host"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_user"><?= lang("smtp_user"); ?></label>

                                            <?= form_input('smtp_user', $Settings->smtp_user, 'class="form-control tip" id="smtp_user"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_pass"><?= lang("smtp_pass"); ?></label>

                                            <?= form_password('smtp_pass', $smtp_pass, 'class="form-control tip" id="smtp_pass"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_port"><?= lang("smtp_port"); ?></label>

                                            <?= form_input('smtp_port', $Settings->smtp_port, 'class="form-control tip" id="smtp_port"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_crypto"><?= lang("smtp_crypto"); ?></label>

                                            <div class="controls"> <?php
                                                                    $crypto_opt = array('' => lang('none'), 'tls' => 'TLS', 'ssl' => 'SSL');
                                                                    echo form_dropdown('smtp_crypto', $crypto_opt, $Settings->smtp_crypto, 'class="form-control tip" id="smtp_crypto"');
                                                                    ?> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('award_points') ?></legend>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label"><?= lang("customer_award_points"); ?></label>

                                    <div class="row">
                                        <div class="col-sm-4 col-xs-6">
                                            <?= lang('each_spent'); ?><br>
                                            <?= form_input('each_spent', $this->cus->formatDecimal($Settings->each_spent), 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                        </div>
                                        <div class="col-sm-4 col-xs-5">
                                            <?= lang('award_points'); ?><br>
                                            <?= form_input('ca_point', $Settings->ca_point, 'class="form-control"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label"><?= lang("staff_award_points"); ?></label>

                                    <div class="row">
                                        <div class="col-sm-4 col-xs-6">
                                            <?= lang('each_in_sale'); ?><br>
                                            <?= form_input('each_sale', $this->cus->formatDecimal($Settings->each_sale), 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                        </div>
                                        <div class="col-sm-4 col-xs-5">
                                            <?= lang('award_points'); ?><br>
                                            <?= form_input('sa_point', $Settings->sa_point, 'class="form-control"'); ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('Additional Settings') ?></legend>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('installment', 'installment'); ?>
                                                <?= form_dropdown('installment', $wm, $Settings->installment, 'class="form-control" id="installment" required="required"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('accounting', 'accounting'); ?>
                                                <?= form_dropdown('accounting', $wm, $Settings->accounting, 'class="form-control" id="accounting" required="required"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('project', 'project'); ?>
                                                <?= form_dropdown('project', $wm, $Settings->project, 'class="form-control" id="project" required="required"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('pos', 'pos'); ?>
                                                <?= form_dropdown('pos', $wm, $Settings->pos, 'class="form-control" id="pos" required="required"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('wholesale', 'wholesale'); ?>
                                                <?= form_dropdown('wholesale', $wm, $Settings->wholesale, 'class="form-control" id="wholesale" required="required"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </fieldset>

                    </div>
                </div>
                <div style="clear: both; height: 10px;"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="controls">
                            <?= form_submit('update_settings', lang("update_settings"), 'class="btn btn-primary"'); ?>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
        <div class="alert alert-info" role="alert">
            <p>
                <strong>Cron Job:</strong> <code>0 1 * * * wget -qO- <?= site_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</code> to run at 1:00 AM daily. For local installation, you can run cron job manually at any time.
                <?php if (!DEMO) { ?>
                    <a class="btn btn-primary btn-xs pull-right" target="_blank" href="<?= site_url('cron/run'); ?>">Run cron job now</a>
                <?php } ?>
            </p>
        </div>
    </div>
</div>