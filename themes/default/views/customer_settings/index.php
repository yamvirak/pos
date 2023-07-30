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
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('customer_settings'); ?></h2>

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
                echo form_open_multipart("customer_settings", $attrib);
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
                                                            ?> </div>
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
                                    <?= lang("default_project", "project"); ?>
                                    <?php
                                    $opt_projects[""] = "";
                                    if($projects){
                                        foreach ($projects as $project) {
                                            $opt_projects[$project->id] = $project->name;
                                        }
                                    }
                                    echo form_dropdown('project', $opt_projects, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" data-placeholder="' . lang("select") . ' ' . lang("project") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
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
                                    <label class="control-label" for="date_with_time"><?= lang("date_with_time"); ?></label>
                                    <div class="controls">
                                        <?php
                                            echo form_dropdown('date_with_time', $wm, $Settings->date_with_time, 'class="form-control tip"  id="date_with_time" style="width:100%;"');
                                        ?>
                                    </div>
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