<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=lang('pos_module') . " | " . $Settings->site_name;?></title>
    <script type="text/javascript">if(parent.frames.length !== 0){top.location = '<?=site_url('pos')?>';}</script>
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
    <?php if($pos_settings->quick_pos==1){ ?>
        <link rel="stylesheet" href="<?=$assets?>pos/css/quick_pos.css" type="text/css"/>
    <?php } ?>
    <link rel="stylesheet" href="<?=$assets?>pos/css/print.css" type="text/css" media="print"/>
    <script type="text/javascript" src="<?=$assets?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?=$assets?>js/jquery-migrate-1.2.1.min.js"></script>
    <!--[if lt IE 9]>
    <script src="<?=$assets?>js/jquery.js"></script>
    <![endif]-->
    <?php if ($Settings->user_rtl) {?>
        <link href="<?=$assets?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet"/>
        <link href="<?=$assets?>styles/style-rtl.css" rel="stylesheet"/>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.pull-right, .pull-left').addClass('flip');
            });
        </script>
    <?php }
    ?>
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
                        <img class="account" alt="" src="<?=$this->session->userdata('avatar') ? site_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/' . $this->session->userdata('gender') . '.png';?>" class="mini_avatar img-rounded" style="border-radius: 50px;width: 43px;margin:-15px 0px -15px 0px;">

                         <a class="btn pos-tip" data-toggle="dropdown" href="#" style="font-size: 16px;">
                            <span><?=lang('welcome')?>! <?=$this->session->userdata('username');?></span>
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
                        <a class="btn pos-tip" title="<?=lang('view_bill_screen')?>" data-placement="bottom" href="<?=site_url('pos/view_bill')?>" target="_blank">
                            <i class="fa fa-laptop"></i>
                        </a>
                    </li>

                    <?php if($pos_settings->pos_favorite_items==1){ ?>
                        <li class="dropdown">
                            <a class="btn pos-tip" title="<?=lang('favorite')?>" id="favorite">
                                <i class="fa fa-star"></i>
                            </a>
                        </li>   
                    <?php } ?>
                    <input type="hidden" class="sp_favorite" />
                    <?php if ($Owner) {?>
                        <li class="dropdown hidden">
                            <a class="btn pos-tip" title="<?=lang('settings')?>" data-placement="bottom" href="<?=site_url('pos/settings')?>">
                                <i class="fa fa-cogs"></i>
                            </a>
                        </li>
                    <?php }
                    ?>
                    <li class="dropdown hidden">
                        <a class="btn pos-tip" title="<?=lang('calculator')?>" data-placement="bottom" href="#" data-toggle="dropdown">
                            <i class="fa fa-calculator"></i>
                        </a>
                        <ul class="dropdown-menu pull-right calc">
                            <li class="dropdown-content">
                                <span id="inlineCalc"></span>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown hidden-xs">
                        <a class="btn pos-tip" title="<?=lang('shortcuts')?>" data-placement="bottom" href="#" data-toggle="modal" data-target="#sckModal">
                            <i class="fa fa-key"></i>
                        </a>
                    </li>
                    
                    <?php  
                        if($this->config->item('ktv1')){
                            
                            if( $Admin || $Owner || $GP['pos-customer_stock'] ){ ?>
                    
                                <li class="dropdown">
                                    <a class="btn pos-tip" title="<?=lang('customer_stocks')?>" data-placement="bottom" href="<?=site_url('pos/customer_stocks')?>">
                                        <i class="fa fa-bars"></i>
                                        <?php 
                                            $cuspendings = $this->pos_model->getAllCustomerStockPendings();
                                            if($cuspendings){
                                                echo '<span class="number bred white">'.($cuspendings).'</span>';
                                            } 
                                        ?>
                                    </a>
                                </li>
                                
                                <li class="dropdown">
                                    <a class="btn pos-tip" title="<?=lang('add_customer_stock')?>" data-placement="bottom" href="<?=site_url('pos/add_customer_stock')?>">
                                        <i class="fa fa-plus-circle"></i>
                                    </a>
                                </li>
                    
                    <?php 
                            } 
                    
                        } 
                    ?>
                    
                    <?php if($pos_settings->pos_order_display==1){ ?>
                        <li class="dropdown">
                            <a class="btn pos-tip" id="spinner-toggle" title="<?=lang('spinner_toggle')?>" data-placement="bottom" data-html="true">
                                <i class="fa fa-folder-open-o"></i>
                            </a>
                        </li>
                    <?php } ?>

                    
                        <?php if($pos_settings->table_enable == 1){
                            if($sid){ 
                                $sbill_items = $this->pos_model->getOpenBillByID($sid);
                        ?>
                        <input type="hidden" id="saleman" value="<?= $sbill_items->saleman ?>" />
                        <li class="dropdown">
                            <a class="btn <?=$sbill_class?> pos-tip" id="opened_salemans" title="<?=lang('salemans')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/opened_salemans/'.$sid)?>" data-toggle="ajax">
                                <i class="fa fa-user"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bred pos-tip" id="spinner-toggle" title="<?=lang('spinner_toggle')?>" href="<?= site_url("pos/add_table"); ?>" data-placement="bottom" data-html="true" style="color:#ffffff;font-weight:bold;font-size: 25px;">
                                 <i class="fa fa-cutlery" aria-hidden="true">&nbsp;</i>
                                    <?=$billsTable->name?>
                                 <i class="fa fa-cutlery" aria-hidden="true">&nbsp;</i>
                            </a>
                        </li>
                        <?php } else { ?>
                        <li class="dropdown">
                            <a class="btn borange pos-tip" id="spinner-toggle" title="<?=lang('spinner_toggle')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/add_table')?>">
                                <i class="fonts fa fa-cutlery" aria-hidden="true">&nbsp;</i><?=lang('table')?>
                            </a>
                        </li>

                        <?php } ?>

                        <?php } ?>
                    <li class="dropdown">
                        <a class="btn pos-tip" id="opened_bills" title="<?=lang('suspended_sales')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/opened_bills')?>" data-toggle="ajax">
                            <i class="fa fa-th"></i>
                            <?php 
                                $count_suspend = $this->site->getCountSuspends();
                                if($count_suspend){
                                    echo '<span class="number bred white">'.($count_suspend).'</span>';
                                } 
                            ?>
                        </a>
                    </li>
                        
                    <?php if($Admin || $Owner && ($pos_settings->table_enable == 1 && $sid) ){ ?>
                        <li class="dropdown">
                            <a class="btn bBlue pos-tip" id="opened_bill_items" title="<?=lang('opened_bill_items')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/opened_bills_items/'.$sid)?>" data-toggle="ajax">
                                <i class="fa fa-print"></i>
                            </a>
                        </li>
                    <?php } ?>
                                        
                    <li class="dropdown hidden">
                        <a class="btn pos-tip" id="count_money" title="<span><?=lang('count_money')?></span>" data-placement="bottom" data-html="true" data-backdrop="static" href="<?=site_url('pos/count_money')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-money"></i>
                        </a>
                    </li>

                    <li class="dropdown">
                        <a class="btn pos-tip" id="register_items" title="<?=lang('register_items')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/register_items')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-folder-open-o"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a class="btn pos-tip" id="register_details" title="<?=lang('register_details')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/register_details')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-line-chart"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a class="btn pos-tip" id="close_register" title="<?=lang('close_register')?>" data-placement="bottom" data-html="true" data-backdrop="static" href="<?=site_url('pos/close_register')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-times-circle"></i>
                        </a>
                    </li>

                    <li class="dropdown hidden">
                        <a class="btn bdarkGreen pos-tip" id="today_sale" title="<?=lang('today_sale')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/today_sale')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-heart"></i>
                        </a>
                    </li>
                    <?php if ($Owner || $Admin) {?>
                        <li class="dropdown hidden">
                            <a class="btn bdarkGreen pos-tip" id="today_profit" title="<?=lang('today_profit')?>" data-placement="bottom" data-html="true" href="<?=site_url('reports/profit')?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-hourglass-half"></i>
                            </a>
                        </li>
                        <li class="dropdown hidden-xs">
                            <a class="btn pos-tip" title="<?=lang('list_open_registers')?>" data-placement="bottom" href="<?=site_url('pos/registers')?>">
                                <i class="fa fa-list"></i>
                                <?php 
                                    $count_open = $this->site->getCountRegisterLists();
                                    if($count_open){
                                        echo '<span class="number bred white">'.($count_open).'</span>';
                                    } 
                                ?>
                            </a>
                        </li>
                        <li class="dropdown hidden-xs">
                            <a class="btn pos-tip" title="<?=lang('clear_ls')?>" data-placement="bottom" id="clearLS" href="#">
                                <i class="fa fa-eraser"></i>
                            </a>
                        </li>
                    <?php }
                    ?>
                </ul>

                <ul class="nav navbar-nav pull-right">
                    <li class="dropdown hidden">
                        <a class="btn bred bold" style="cursor: default;"><span id="display_time"></span></a>
                    </li>
                    <li class="dropdown">
                            <a class="btn bred" style="cursor: default;">
                                <span class="pos-logo-lg" style="font-size:23px;" id="time-part"></span>
                            </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div id="content">
        <div class="c1">
            <div class="pos">
                <?php
                    if ($error) {
                        echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close fa-2x\" data-dismiss=\"alert\">&times;</button>" . $error . "</div>";
                    }
                ?>              
                <?php
                    if ($message) {
                        echo "<div class=\"alert alert-success hidden\"><button type=\"button\" class=\"close fa-2x\" data-dismiss=\"alert\">&times;</button>" . $message . "</div>";
                    }
                ?>
                <div id="pos">
                    <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos-sale-form');
                    echo form_open("pos", $attrib);?>
                    
                    <input type="hidden" name="suspend_bill_id" value="<?= $sid; ?>">
                    <div class="col-xs-5 pos_product_left">
                        <?php if($pos_settings->pos_order_display==1){ ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="scroll-spinner" style="display:none;">
                                        <div id="quick-spinner"></div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div id="printhead">
                            <h4 style="text-transform:uppercase;"><?php echo $Settings->site_name; ?></h4>
                            <?php
                                echo "<h5 style=\"text-transform:uppercase;\">" . $this->lang->line('order_list') . "</h5>";
                                echo $this->lang->line("date") . " " . $this->cus->hrld(date('Y-m-d H:i:s'));
                            ?>
                        </div>
                        <div id="left-top">
                            <div  style="position: absolute; <?=$Settings->user_rtl ? 'right:-9999px;' : 'left:-9999px;';?>"><?php echo form_input('test', '', 'id="test" class="kb-pad"'); ?></div>
                            
                            <input type="hidden" name="biller" id="biller" value="<?= ($Owner || $Admin || !$this->session->userdata('biller_id')) ? $pos_settings->default_biller : $this->session->userdata('biller_id')?>"/>
                            <input type="hidden" name="project" id="project_id" />
                            <input type="hidden" name="delivery_status" id="delivery_status"/>
                            <input type="hidden" name="reference_no" id="pos_reference_no" />
                            <input type="hidden" name="date" id="podate" value="<?= date("d/m/Y H:i"); ?>" />
                            
                            <?php if($this->config->item("member_card")==true){?>
                                <div class="form-group">
                                    <?php echo form_input('membership_code', (isset($_POST['membership_code']) ? $_POST['membership_code'] : ''), 'class="form-control pos-input-tip" placeholder="' . $this->lang->line("scan_membership_code").'" id="posmembership_code" autocomplete="off" '); ?>
                                </div>
                            <?php } ?>
                                    
                            <div class="form-group">
                                <div class="input-group">
                                    
                                    <?php
                                        echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="poscustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control pos-input-tip" style="width:100%;"');                                 
                                    ?>
                                    <input type="hidden" name="saleman_id" id="saleman_id" />
                                    
                                    <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                        <a href="#" id="toogle-customer-read-attr" class="external">
                                            <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                        </a>
                                    </div>
                                    <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                        <a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                        </a>
                                    </div>
                                <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                        <a href="<?=site_url('customers/add');?>" id="add-customer" class="external" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.5em;"></i>
                                        </a>
                                    </div>
                                <?php } ?>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                            <div class="no-print">
                                <div class="form-group">
                                    <?php
                                    
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="poswarehouse" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                    ?>
                                </div>
                                
                            </div>
                        </div>
                        <div id="print">
                            <div id="left-middle">
                                <div id="product-list">
                                    <table class="table items table-striped table-bordered table-condensed table-hover sortable_table"
                                           id="posTable" style="margin-bottom: 0;">
                                        <thead>
                                        <?php if ($Settings->qty_operation == 1) {
                                                $head_row = 'rowspan="2"';
                                            }else{
                                                $head_row = '';
                                            }
                                        ?>
                                        <tr>
                                            <th <?= $head_row ?> width="40%"><?=lang("product");?></th>
                                            <th <?= $head_row ?> width="15%"><?=lang("price");?></th>
                                            <?php                       
                                            if ($Settings->qty_operation == 1) { ?>                                         
                                                <th width="25%" colspan="4"><?= lang("quantity_operation"); ?></th>
                                            <?php } ?>
                                            <th <?= $head_row ?> width="15%"><?=lang("qty");?></th>
                                            <?php if ($Settings->show_unit == 1) { ?>                                           
                                                <th <?= $head_row ?> width="15%"><?=lang("unit");?></th>
                                            <?php } ?>
                                            <th <?= $head_row ?> width="15%"><?=lang("subtotal");?></th>
                                            <th <?= $head_row ?> style="width: 5%; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        <?php if ($Settings->qty_operation == 1) { ?>
                                            <tr>
                                                <th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('width') ?></th>
                                                <th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('height') ?></th>
                                                <th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('square') ?></th>
                                                <th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('qty') ?></th>
                                            </tr>
                                        <?php } ?>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                            <div id="left-bottom">
                                <table id="totalTable"
                                       style="width:100%; float:right; padding:5px; color:#000; background: #FFF;">
                                    <tr>
                                        <td style="padding: 5px 10px;border-top: 1px solid #DDD;"><?=lang('items');?></td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                                            <span id="titems">0</span>
                                        </td>
                                        <td style="padding: 5px 10px;border-top: 1px solid #DDD;"><?=lang('total');?></td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                                            <span id="total">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 10px;"><?=lang('order_tax');?>
                                            <a href="#" id="pptax2">
                                                <i class="fonts fa fa-edit"></i>
                                            </a>
                                        </td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;">
                                            <span id="ttax2">0.00</span>
                                        </td>
                                        <td style="padding: 5px 10px;"><?=lang('discount');?>
                                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                                            <a href="#" id="ppdiscount">
                                                <i class="fonts fa fa-edit"></i>
                                            </a>
                                            <?php } ?>
                                        </td>
                                        <td class="text-right" style="padding: 5px 10px;font-weight:bold;">
                                            <span id="tds">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 10px; border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;" colspan="2">
                                            <a href="#" id="pshipping">
                                                <i class="fa fa-plus-square"></i>
                                            </a>
                                            <span id="tship"></span>
                                            <?php if($this->Settings->car_operation == 1){ ?>
                                                <?=lang('car_operation');?>
                                                <a href="#" id="pvehicle">
                                                    <i class="fa fa-plus-square"></i>
                                                </a>
                                            <?php } ?>
                                        </td>
                                        <td class="text-right" style="padding:5px 10px 5px 10px; font-size: 14px;border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;" colspan="2">
                                            <div style="font-size:20px;font-weight: bold;">
                                                <span>KHR = </span><span id="gtotal_khr">0.00</span>
                                                <span>&nbsp;&nbsp; | &nbsp;&nbsp;</span>
                                                <span>$ = </span><span id="gtotal">0.00</span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <?php $suspend_option = ($pos_settings->table_enable); if($pos_settings->quick_pos!=1){ ?>
                                    <div class="clearfix"></div>
                                    <div id="botbuttons" class="col-xs-12 text-center">
                                        <div class="row">
                                            <?php 
                                                if($suspend_option == 1){ 
                                                    $count = $this->pos_model->getSuspendItemBySuspendID($sid);
                                                ?>
                                                <div class="col-xs-4">
                                                    <div class="row">
                                                        <div class="btn-group-vertical btn-block">
                                                            <input type="hidden" class="item_order_count" value="<?= $count; ?>" />
                                                            <input type="hidden" class="item_ordered" />
                                                            <?php if ($billsTable != false){?>
                                                                <input type="hidden"  value="<?= $billsTable->table_name  ?>" time="<?= date("d/m/Y H:i",strtotime($billsTable->date));  ?>" class="add_suspend_item" id="<?= $billsTable->table_id  ?>"/>
                                                                <a href="<?= site_url("pos/add_table?v=1&bill_id=".$billsTable->id) ?>" id="move_suspend" type="button" class="btn btn-warning btn-block pos_button">
                                                                    <i class="fonts fa fa-random" aria-hidden="true"></i> <?=lang('move');?>
                                                                </a>
                                                            <?php }else{ ?>
                                                                <a disabled class="btn btn-warning btn-block pos_button"><i class="fonts fa fa-retweet" aria-hidden="true"></i> <?=lang('move');?></a>
                                                            <?php } ?>
                                                            <?php if ($billsTable != false){?>
                                                                <a hreff="<?= site_url("pos/delete_suspend/".$billsTable->table_id); ?>" class="btn btn-danger btn-block btn-flat delete_suspend pos_button" >
                                                                    <i class="fonts fa fa-trash-o fa-2x tip pointer" aria-hidden="true"></i> <?=lang('delete');?>
                                                                </a>
                                                            <?php }else{ ?>
                                                                <a disabled class="btn btn-danger btn-block btn-flat delete_suspend pos_button"><i class="fonts fa fa-trash-o fa-2x tip pointer" aria-hidden="true"></i> <?=lang('delete');?></a>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                    $permission = $this->site->checkPermissions();                                              
                                                    if ($Owner || $Admin || $GP['sales-payments'] == 1) {                                                   
                                                        $payment_block = "enabled";
                                                    }else{
                                                        $payment_block = "disabled";
                                                    }
                                                ?>
                                                <div class="col-xs-4">
                                                    <div class="row">
                                                        <div class="btn-group-vertical btn-block">
                                                            <button type="button" class="btn btn-info btn-block pos_button" id="print_order">
                                                                <i class="fonts fa fa-floppy-o" aria-hidden="true"></i> <?=lang('order');?>
                                                            </button>
                                                            <button <?= $payment_block ?> type="button" class="btn btn-primary btn-block pos_button" id="print_bill">
                                                                <i class="fonts fa fa-print" aria-hidden="true"></i> <?=lang('bill');?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-4">
                                                    <div class="row">
                                                        <button <?= $payment_block ?> type="button" class="btn btn-success btn-block payment" id="payment" style="height:79px;">
                                                            <i class="fonts fa fa-money" style="margin-right: 5px;"></i><?=lang('payment');?>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php }else { ?>
                                                <div class="col-xs-4" style="padding: 0;">
                                                    <div class="btn-group-vertical btn-block">                                                                                          
                                                        <button type="button" class="btn btn-warning btn-block btn-flat pos_button" id="suspend"><i class="fonts fa fa-th"></i>
                                                            <?=lang('suspend'); ?>
                                                        </button>
                                                        <?php if($sid){ ?>
                                                            <a hreff="<?= site_url("pos/delete_suspend/".$sid); ?>" class="btn cl-primary btn-block btn-flat delete_suspend" id="reset">
                                                                <?= lang('cancel'); ?>
                                                            </a>
                                                        <?php } else { ?>
                                                            <button type="button" class="btn btn-danger btn-block btn-flat pos_button" id="reset"><i class="fonts fa fa-trash-o fa-2x tip pointer posdel" aria-hidden="true"></i>
                                                                <?= lang('cancel'); ?>
                                                            </button>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php if($pos_settings->table_enable == 1){ ?>
                                                <div class="col-xs-4" style="padding: 0;">
                                                    <div class="btn-group-vertical btn-block">
                                                        <button type="button" class="btn btn-warning btn-block" id="print_order">
                                                            <?=lang('order');?>
                                                        </button>
                                                        <button type="button" class="btn cl-primary btn-block" id="print_bill">
                                                            <?=lang('bill');?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-xs-4" style="padding: 0;">
                                                    <button type="button" class="btn cl-success btn-block payment" id="payment" style="height:67px;">
                                                        <i class="fonts fa fa-money" style="margin-right: 5px;"></i><?=lang('payment');?>
                                                    </button>
                                                </div>
                                            <?php  }else{ ?>
                                                 <div class="col-xs-4">
                                                    <div class="row">
                                                        <div class="btn-group-vertical btn-block">
                                                            
                                                            <button type="button" class="btn btn-primary btn-block pos_button" id="print_bill" style="height:79px;"> <i class="fonts fa fa-print" aria-hidden="true"></i>
                                                                <?=lang('bill');?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if($pos_settings->pos_payment==0){ ?>
                                                    <div class="col-xs-4" style="padding: 0;">
                                                        <input type="submit" id="submit_pos_payment" value="Submit Sale" style="display: none;"/>
                                                        <button id="pos_payment" type="button" class="btn cl-danger btn-block"  style="height:67px;">
                                                            <i class="fa fa-money" style="margin-right: 5px;"></i><?=lang('save');?>
                                                        </button>
                                                    </div>
                                                <?php }else{ ?>
                                                    <div class="col-xs-4" style="padding: 0;">
                                                        <button type="button" class="btn btn-success btn-block payment" id="payment" style="height:79px;">
                                                            <i class="fonts fa fa-money" style="margin-right: 5px;"></i><?=lang('payment');?>
                                                        </button>
                                                    </div>
                                                <?php } ?>
                                            <?php } 
                                        } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div style="clear:both; height:5px;"></div>

                                <div id="num">
                                    <div id="icon"></div>
                                </div>
                                <span id="hidesuspend"></span>
                                <input type="hidden" name="pos_note" value="" id="pos_note">
                                <input type="hidden" name="staff_note" value="" id="staff_note">
                                <?php if ($sid) {?>
                                <input type="hidden" name="delete_id" value="<?php echo $sid; ?>" />
                                <?php } ?>
                                <?php
                                    $allCurrencies = $this->site->getAllCurrencies();
                                    $column = (2 + count($allCurrencies));
                                    foreach ($allCurrencies as $i => $currency){?>                                  
                                        <input name="camount[]" id="camount_<?=$i?>" type="hidden" value=""/>
                                        <input name="currency[]" type="hidden" value="<?= $currency->code ?>"/>
                                        <input name="rate[]" type="hidden" value="<?= $currency->rate ?>"/>
                                <?php
                                    }
                                ?>
                                <div id="payment-con">
                                    <?php for ($i = 1; $i <= 3; $i++) {?>
                                        <input type="hidden" name="amount[]" id="amount_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="balance_amount[]" id="balance_amount_<?=$i?>" value=""/>
                                        <input type="hidden" name="paid_by[]" id="paid_by_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_no[]" id="cc_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="paying_gift_card_no[]" id="paying_gift_card_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_holder[]" id="cc_holder_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cheque_no[]" id="cheque_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_month[]" id="cc_month_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_year[]" id="cc_year_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_type[]" id="cc_type_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_cvv2[]" id="cc_cvv2_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="payment_note[]" id="payment_note_val_<?=$i?>" value=""/>
                                    <?php }
                                    ?>
                                </div>
                                <input name="order_tax" type="hidden" value="<?=$suspend_sale ? $suspend_sale->order_tax_id : $Settings->default_tax_rate2;?>" id="postax2">
                                <input name="discount" type="hidden" value="<?=$suspend_sale ? $suspend_sale->order_discount_id : '';?>" id="posdiscount">
                                <input name="shipping" type="hidden" value="<?=$suspend_sale ? $suspend_sale->shipping : '0';?>" id="posshipping">
                                <input type="hidden" name="rpaidby" id="rpaidby" value="cash" style="display: none;"/>
                                <input type="hidden" name="total_items" id="total_items" value="0" style="display: none;"/>
                                <input type="submit" id="submit_sale" value="Submit Sale" style="display: none;"/>
                                <?php if($this->Settings->car_operation == 1){ ?>
                                    <input type="hidden" name="povehicle_model" id="povehicle_model"  value="<?=$suspend_sale ? $suspend_sale->vehicle_model : '';?>" />
                                    <input type="hidden" name="povehicle_kilometers" id="povehicle_kilometers" value="<?=$suspend_sale ? $suspend_sale->vehicle_kilometers : '';?>"/>
                                    <input type="hidden" name="povehicle_vin_no"  id="povehicle_vin_no" value="<?=$suspend_sale ? $suspend_sale->vehicle_vin_no : '';?>" />
                                    <input type="hidden" name="povehicle_plate"  id="povehicle_plate" value="<?=$suspend_sale ? $suspend_sale->vehicle_plate : '';?>" />
                                    <input type="hidden" name="pojob_number"  id="pojob_number" value="<?=$suspend_sale ? $suspend_sale->job_number : '';?>" />
                                    <input type="hidden" name="pomechanic"  id="pomechanic" value="<?=$suspend_sale ? $suspend_sale->mechanic : '';?>" />
                                <?php } ?>
                            </div>
                        </div>
                        
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-xs-7 pos_search_product_right">
                        <div class="form-group" id="ui">
                                    <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                    <div class="input-group">
                                    <?php } ?>
                                    <span class="input-group-addon-search" style="padding: 2px 8px;">
                                       <i class="fa fa-search" style="font-size: 1.5em;"></i>
                                    </span>
                                    <?php echo form_input('add_item', '', 'class="form-control pos-tip" id="add_item" data-placement="top"  style="border: none;border-top:none;height: 40px;" data-trigger="focus" placeholder="' . $this->lang->line("search_product_by_name_code") . '" title="' . $this->lang->line("au_pr_name_tip") . '"'); ?>
                                    <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                        
                                    </div>
                                    <?php } ?>
                                    <div style="clear:both;"></div>
                                </div>
                    </div>
                   

                    <div class="col-xs-7 pos_product_right">
                        <?php if($pos_settings->quick_pos==1){ ?>
                            <div class="row">
                                <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <?php
                                                foreach ($billers as $biller) {
                                                    $bl[$biller->id] = ($biller->company && $biller->name != '-' ? $biller->name : $biller->company);
                                                }
                                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                    <?php } else {
                                        $biller_input = array(
                                            'type' => 'hidden',
                                            'name' => 'biller',
                                            'id' => 'posbiller',
                                            'value' => $this->session->userdata('biller_id'),
                                        );
                                        echo form_input($biller_input);
                                        foreach ($billers as $biller) {
                                            $btest = ($biller->company && $biller->name != '-' ? $biller->name : $biller->company);
                                            $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                            if ($biller->id == $this->session->userdata('biller_id')) {
                                                $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                            }
                                        }
                                    }
                                    if($Settings->project == 1){    
                                        if ($Owner || $Admin) { ?>
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <div class="no-project">
                                                            <?php
                                                            $pj[''] = '';
                                                            if(isset($projects) && $projects){
                                                                foreach ($projects as $project) {
                                                                    $pj[$project->id] = $project->name;
                                                                }
                                                            }
                                                            echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php } else { ?>
                                                <div class="col-xs-6">
                                                    <div class="form-group">
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
                                                            echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php 
                                        }
                                    }
                                
                                $usd_notes = array("1","5","10","20","50","100");
                                $khr_notes = array("500","1,000","5,000","10,000","20,000","50,000");
                            ?>
                            </div>
                            <div class="clearfix"></div>
                            <div id="b-top">
                                <div class="col-sm-6 col-md-6 col-lg-6 col-xs-6 cl-primary">
                                    <div class="row">
                                        <table style="width:100%;" id="table-money">
                                            <tr>
                                                <td style='width:50%;'>
                                                    <button style='width:100%; height:40px;' type='button' class='btn btn-money cl-primary' id='quick_payble_usd'>
                                                        <?=lang("USD")?>
                                                    </button>
                                                </td>
                                                <td style="width:50%">
                                                    <button style='width:100%; height:40px;' type='button' class='btn btn-money cl-primary' id='quick_payble_khr'>
                                                        <?=lang("KHR")?>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php foreach($usd_notes as $i => $usd_note){
                                                    $khr_note = $khr_notes[$i];
                                                ?>
                                                <tr>
                                                    <td><button style='width:100%; height:40px;' type='button' class='btn btn-money cl-primary quick_cash_usd'><?= $usd_note ?></button></td>
                                                    <td><button style='width:100%; height:40px;' type='button' class='btn btn-money cl-primary quick_cash_khr'><?=$khr_note ?></button></td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td>
                                                    <button style='width:100%; height:40px;' type='button' class='btn btn-money cl-primary' id='quick_clear_usd'>
                                                        <i class="fa fa-eraser" aria-hidden="true"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <button style='width:100%; height:40px;' type='button' class='btn btn-money cl-primary' id='quick_clear_khr'>
                                                    <i class="fa fa-eraser" aria-hidden="true"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6 col-lg-6 col-xs-6 btn-success">
                                    <div class="row">
                                        <table style="width:100%;" id="table-cal">
                                            <tr>
                                                <td style="width:25%;"><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>7</button></td>
                                                <td style="width:25%;"><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>8</button></td>
                                                <td style="width:25%;"><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>9</button></td>
                                                <td style="width:25%;" rowspan="2"><button style='width:100%; height:160px;' type='button' class='btn btn-key btn-success' id="backspace"><img src="<?=base_url('assets/images/delete.png')?>" width="25" /></button></td>
                                            </tr>
                                            <tr>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>4</button></td>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>5</button></td>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>6</button></td>
                                                
                                            </tr>
                                            <tr>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>1</button></td>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>2</button></td>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>3</button></td>
                                                <td rowspan="2"><button style='width:100%; height:160px;' type='button' class='btn btn-key btn-success' id="erase_all">C</button></td>
                                            </tr>
                                            <tr>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>0</button></td>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-success expression'>.</button></td>
                                                <td><button style='width:100%; height:80px;' type='button' class='btn btn-key btn-danger' value="USD" id="lan_toggle"><?=lang('USD')?></button></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="clearfix" style="height:5px;"></div>
                            </div>
                            <div id="b-middle">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
                                    <div class="row">
                                        <table class="table table-borderless" style="white-space:nowrap; margin-bottom:5px;" id="table-money-info">
                                            <tr>
                                                <th></th>
                                                <th class="text-right"><?= lang("USD") ?></th>
                                                <th class="text-right"><?= lang("KHR") ?></th>
                                            </tr>
                                            <tr>
                                                <th><?= lang("grand_total") ?><input type="hidden" value="0" id="qgtotal"/></th>
                                                <th>: <span id="qgtotal_usd" style="float:right;"><?= $this->cus->formatOtherMoney(0) ?></span></th>
                                                <th>  <span id="qgtotal_khr" style="float:right;"><?= $this->cus->formatKhMoney(0) ?></span></th>
                                            </tr>
                                            <tr>
                                                <th><?= lang("paying") ?><input name="gpaying" type="hidden" value="0" id="gpaying"/></th>
                                                <th style="padding-top:0;padding-bottom:0;"><input type="text" name="qpaying_usd" class="form-control text-right qpaying camount" id="qpaying_usd"/></th>
                                                <th style="padding-top:0;padding-bottom:0;"><input type="text" name="qpaying_khr" class="form-control text-right qpaying camount" id="qpaying_khr"/></th>
                                            </tr>
                                            <?php if($pos_settings->pos_multi_payment==1) { ?>
                                                <tr id="rowMultiPayment">
                                                    <th colspan="3">
                                                        <button type="button" class="btn cl-primary btn-other col-md-12 addMorePayment">
                                                            <i class="fa fa-plus"></i> <?=lang('add_more_payments')?>
                                                        </button>
                                                    </th>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <th><?= lang("balance") ?><input name="qbalance" type="hidden" value="0" id="qbalance"/></th>
                                                <th> : <span id="qbalance_usd" style="float:right;color:red;"><?= $this->cus->formatOtherMoney(0) ?></span></th>
                                                <th> <span id="qbalance_khr" style="float:right;color:red;"><?= $this->cus->formatKhMoney(0) ?></span></th>
                                            </tr>
                                            <tr>
                                                <th><?= lang("change") ?></th>
                                                <th>: <span id="qchange_usd" style="float:right;"><?= $this->cus->formatOtherMoney(0) ?></span></th>
                                                <th>  <span id="qchange_khr" style="float:right;"><?= $this->cus->formatKhMoney(0) ?></span></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div id="b-bottom">
                                <div class="col-sm-3">
                                    <div class="row">
                                        <?php if($sid){ ?>
                                            <a hreff="<?= site_url("pos/delete_suspend/".$sid); ?>" style="line-height: 29px;height:40px;text-transform:uppercase;" class="btn btn-other btn-danger btn-block btn-flat delete_suspend" id="reset">
                                                <i class="fa fa-times-circle-o" aria-hidden="true"></i> <?= lang('clear'); ?>
                                            </a>
                                        <?php } else { ?>
                                            <button type="button" style="height:40px;text-transform:uppercase;" class="btn btn-other btn-danger btn-block btn-flat" id="reset">
                                                <i class="fa fa-times-circle-o" aria-hidden="true"></i> <?= lang('cancel'); ?>
                                            </button>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="row">
                                        <button type="button" style="height:40px;text-transform:uppercase;" class="btn btn-other cl-primary btn-block btn-flat" id="suspend">
                                        <i class="fa fa-paper-plane-o" aria-hidden="true"></i> <?=lang('suspend'); ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="row">
                                        <input type="submit" id="submit_quick_pos" value="Submit Sale" style="display: none;"/>
                                        <button id="quick_payment" type="button" style="height:40px;text-transform:uppercase;" class="btn btn-other btn-success btn-block">
                                            <i class="fa fa-money" style="margin-right: 5px;"></i><?=lang('payment');?>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        <?php } 
                        echo form_close(); 
                        if($pos_settings->quick_pos!=1){ ?>
                            <div id="cpinner">
                                <div id="panel-top">
                                    <?php if($pos_settings->pos_category_fix==1){ ?>
                                        <div id="quick-categories">
                                            <div class=" col-sm-1 col-md-1 col-lg-1 col-xs-1 previous_d">
                                                <div class="row">
                                                    <button class="btn cl-primary" title="<?=lang('previous')?>" type="button" id="previous_c">
                                                        <i class="fa fa-chevron-left"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-sm-11 col-md-11 col-lg-11 col-xs-11">
                                                <div class="row">
                                                    <div class="cpcategory">
                                                        <?php 
                                                        foreach($categories as $category){
                                                                if($this->pos_settings->default_category==$category->id){
                                                                    echo "<button type='button' disabled-open-category='true' value='{$category->id}' class='animated ccategory btn cl-primary cl-danger category'>{$category->name}</button>"; 
                                                                }else{
                                                                    echo "<button type='button' disabled-open-category='true' value='{$category->id}' class='animated ccategory btn cl-primary category'>{$category->name}</button>";
                                                                }
                                                            }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class=" col-sm-1 col-md-1 col-lg-1 col-xs-1 next_d">
                                                <div class="row">
                                                    <button class="btn cl-primary" title="<?=lang('next')?>" type="button" id="next_c">
                                                        <i class="fa fa-chevron-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                           
                                            <div class="clearfix"></div>
                                            <div id="pos-subcategories" style="margin-top:5px;"></div>
                                        </div>
                                    <?php } ?>
                                    <div class="clearfix"></div>
                                    <div id="product-search" style="padding-top:5px;" class="hidden">
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="form-group">
                                                    <input type="text" class="form-control sp_code" placeholder="<?=lang("code")?>"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="form-group">
                                                    <input type="text" class="form-control sp_name" placeholder="<?=lang("name")?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                                <style type="text/css">
                                    .previous_d{
                                        width: 33px !important;
                                        margin-right: 5px;
                                    }
                                    .next_d{
                                        width: 33px !important;
                                        margin-right: 5px;
                                    }
                                </style>
                                <div id="proContainer">
                                    <div id="ajaxproducts">
                                        <div id="item-list">
                                            <?php echo $products; ?>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div id="panel-bottom" class="btn-group btn-group-justified pos-grid-nav">
                                            <div class="btn-group">
                                                <button style="z-index:10002;" class="btn cl-primary pos-tip" title="<?=lang('previous')?>" type="button" id="previous">
                                                    <i class="fa fa-chevron-left"></i>
                                                </button>
                                            </div>
                                            <?php 
                                                if ($Owner || $Admin || $GP['sales-add_gift_card']) {
                                                    
                                                ?>
                                            <div class="btn-group hidden">
                                                <button style="z-index:10003;" class="btn cl-primary pos-tip" type="button" id="sellGiftCard" title="<?=lang('sell_gift_card')?>">
                                                    <i class="fa fa-credit-card" id="addIcon"></i> <?=lang('sell_gift_card')?>
                                                </button>
                                            </div>
                                            <?php 
                                                    } 
                                                
                                            ?>
                                            <div class="btn-group">
                                                <button style="z-index:10004;" class="btn cl-primary pos-tip" title="<?=lang('next')?>" type="button" id="next">
                                                    <i class="fa fa-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        <?php } ?>
                        <div style="clear:both;"></div>
                    
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
    </div>
</div>
<?php if($pos_settings->pos_category_fix!=1 && $pos_settings->quick_pos != 1){ ?>
<div class="rotate btn-cat-con">    
    <button type="button" id="open-brands" class="btn btn-info open-brands"><?= lang('brands'); ?></button> 
    <button type="button" id="open-subcategory" class="btn btn-warning open-subcategory"><?= lang('subcategories'); ?></button>
    <button type="button" id="open-category" class="btn btn-primary open-category"><?= lang('categories'); ?></button>
</div>
<?php } ?>
<div id="brands-slider">
    <div id="brands-list">      
        <?php
            foreach ($brands as $brand) {
                echo "<button id=\"brand-" . $brand->id . "\" type=\"button\" value='" . $brand->id . "' class=\"btn-prni brand\" ><img src=\"assets/uploads/thumbs/" . ($brand->image ? $brand->image : 'no_image.png') . "\" style='width:" . $Settings->twidth . "px;height:" . $Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $brand->name . "</span></button>";
            }
        ?>
    </div>
</div>
<div id="category-slider">
    <!--<button type="button" class="close open-category"><i class="fa fa-2x">&times;</i></button>-->
    <div id="category-list">
        <?php
            //for ($i = 1; $i <= 40; $i++) {
            foreach ($categories as $category) {
                echo "<button id=\"category-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni category\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $Settings->twidth . "px;height:" . $Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
            }
            //}
        ?>
    </div>
</div>
<div id="subcategory-slider">
    <!--<button type="button" class="close open-category"><i class="fa fa-2x">&times;</i></button>-->
    <div id="subcategory-list">
        <?php
            if (!empty($subcategories)) {
                foreach ($subcategories as $category) {
                    echo "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $Settings->twidth . "px;height:" . $Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
                }
            }
        ?>
    </div>
</div>
<div class="modal fade in" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="payModalLabel"><?=lang('finalize_sale');?></h4>
            </div>
            <div class="modal-body" id="payment_content">
                <div class="row">
                    <div class="col-md-10 col-sm-9">
                            
                            <div class="row">
                            
                                <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                                
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang("date", "sldate"); ?>
                                            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date("d/m/Y H:i")), 'class="form-control input-tip sldate" id="sldate" required="required"'); ?>
                                        </div>
                                    </div>
                                    
                                <?php } ?>
                                    
                                <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?=lang("biller", "biller");?>
                                            <?php
                                                foreach ($billers as $biller) {
                                                    $btest = ($biller->company && $biller->name != '-' ? $biller->name : $biller->company);
                                                    $bl[$biller->id] = $btest;
                                                    $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                                    if ($biller->id == $pos_settings->default_biller) {
                                                        $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                                    }
                                                }
                                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                <?php } else {
                                        $biller_input = array(
                                            'type' => 'hidden',
                                            'name' => 'biller',
                                            'id' => 'posbiller',
                                            'value' => $this->session->userdata('biller_id'),
                                        );

                                        echo form_input($biller_input);

                                        foreach ($billers as $biller) {
                                            $btest = ($biller->company && $biller->name != '-' ? $biller->name : $biller->company);
                                            $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                            if ($biller->id == $this->session->userdata('biller_id')) {
                                                $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                            }
                                        }
                                    }
                                    
                                $biller = $default_biller;  
                                ?>
                                
                                <?php if($pos_settings->pos_delivery == 1){ ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('delivery_status', 'delivery_status'); ?>
                                            <?php
                                                $opts = array('pending' => lang('pending'), 'packaging'=>lang('packaging'), 'take_away' => lang('take_away'));
                                            ?>
                                            <?= form_dropdown('delivery_status', $opts, '', 'class="form-control delivery_status" id="podelivery_status" required="required" style="width:100%;"'); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                
                                <?php if($pos_settings->pos_ref == 1){ ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang("reference_no", "slref"); ?>
                                            <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip pos_ref" id="pos_ref"'); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            
                            <?php if($Settings->project == 1){ ?>
                                    
                                <?php if ($Owner || $Admin) { ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?= lang("project", "project"); ?>
                                                <div class="no-project">
                                                    <?php
                                                    $pj[''] = '';
                                                    if(isset($projects) && $projects){
                                                        foreach ($projects as $project) {
                                                            $pj[$project->id] = $project->name;
                                                        }
                                                    }
                                                    echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                <?php } else { ?>
                                        <div class="col-md-6">
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
                                                    echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                                    ?>
                                                </div>
                                            </div>
                                        </div>




                                <?php } ?>
                            
                            <?php } ?>
                            
                            </div>
                        
                        <?php if($pos_settings->pos_payment_sale_note==1){ ?>                       
                            
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <?=form_textarea('sale_note', '', 'id="sale_note" class="form-control kb-text skip" style="height: 100px;" placeholder="' . lang('sale_note') . '" maxlength="250"');?>
                                    </div>
                                    <div class="col-sm-6">
                                        <?=form_textarea('staffnote', '', 'id="staffnote" class="form-control kb-text skip" style="height: 100px;" placeholder="' . lang('staff_note') . '" maxlength="250"');?>
                                    </div>
                                </div>
                            </div>
                            
                        <?php } ?>
                       
                        <div class="clearfir"></div>
                        
                        <div class="payment-cash">
                            
                            <table class="table table-bordered table-condensed table-striped">
                                <tbody>                                 
                                    <tr>
                                        <th width="50%" height="25" class="text-left bold"><?= lang("currency"); ?></th>
                                        <?php
                                            $allCurrencies = $this->site->getAllCurrencies();
                                            $column = (2 + count($allCurrencies));
                                            foreach ($allCurrencies as $currency){ ?>
                                                <th  class="text-center"><?=$currency->code?></th>
                                        <?php } ?>
                                    </tr>
                                    <tr>
                                        <td width="50%" height="25" class="text-left bold"><?= lang("total_items"); ?></td>
                                        <?php foreach ($allCurrencies as $currency){?>
                                                <td class="text-right"><span class="item_count">0</span></td>
                                        <?php } ?>
                                    </tr>
                                    <tr>
                                        <td width="50%" height="25" class="text-left bold"><?= lang("total_payable"); ?></td>                                   
                                        <?php 
                                            foreach ($allCurrencies as $currency){
                                                $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                            ?>
                                                <td class="text-right"><span class="total_payable" base_rate="<?= $base_currency->rate ?>" rate="<?=$currency->rate?>" id="total_payable">0</span></td>
                                        <?php } ?>
                                    </tr>
                                    <tr>
                                        <td width="50%" height="25" class="text-left bold"><?= lang("paid_amount"); ?></td>                                 
                                        <?php 
                                            foreach ($allCurrencies as $currency){ 
                                                $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                        ?>
                                                <td class="text-right">
                                                    <input name="camount[]" base_rate="<?= $base_currency->rate ?>" rate="<?=$currency->rate?>" type="text" class="form-control camount <?=($currency->code==$base_currency->code?"base_amount":"");?>" class="text-right"/>                                                
                                                </td>
                                        <?php } ?>
                                    </tr>
                                    
                                    <tr>
                                        <td width="50%" height="25" class="text-left bold"><?= lang("balance"); ?></td>                                 
                                        <?php
                                            $count_currency = 0;
                                            foreach ($allCurrencies as $currency){
                                                $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                                $count_currency++;
                                        ?>
                                                <td class="text-right"><span class="balance_1" base_rate="<?= $base_currency->rate ?>" rate="<?=$currency->rate?>" id="balance_1">0</span></td>
                                        <?php } ?>
                                    </tr>
                                </tbody>
                            </table>                        
                        </div>
                                                
                        <div id="payments" class="hidden">
                            <div class="well well-sm well_1">
                                <div class="payment">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <?=lang("paying_by", "paid_by_1");?>
                                                <select name="paid_by[]" id="paid_by_1" class="form-control paid_by">
                                                    <?= $this->cus->cash_opts(); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <?=lang("amount", "amount_1");?>
                                                <input name="amount[]" type="text" id="amount_1"
                                                       class="pa form-control kb-pad1 amount"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group gc_1" style="display: none;">
                                                <?=lang("gift_card_no", "gift_card_no_1");?>
                                                <input name="paying_gift_card_no[]" type="text" id="gift_card_no_1"
                                                       class="pa form-control kb-pad gift_card_no"/>

                                                <div id="gc_details_1"></div>
                                            </div>
                                            <div class="form-group">
                                                <?=lang('payment_note', 'payment_note');?>
                                                <textarea name="payment_note[]" id="payment_note_1"
                                                          class="pa form-control kb-text payment_note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($pos_settings->pos_multi_payment==1) { ?>
                            <div id="multi-payment"></div>
                            <button type="button" class="btn cl-primary col-md-12 addButton">
                                <i class="fa fa-plus"></i> <?=lang('add_more_payments')?>
                            </button>
                        <?php } ?>
                        
                        <div style="clear:both; height:15px;"></div>
                        
                        <div class="font16">
                            <table class="table table-bordered table-condensed table-striped" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <td width="25%"><?=lang("total_items");?></td>
                                    <td width="25%" class="text-right"><span id="item_count">0.00</span></td>
                                    <td width="25%"><?=lang("total_payable");?></td>
                                    <td width="25%" class="text-right"><span id="twt">0.00</span></td>
                                </tr>
                                <tr>
                                    <td><?=lang("total_paying");?></td>
                                    <td class="text-right"><span id="total_paying">0.00</span></td>
                                    <td><?=lang("balance");?></td>
                                    <td class="text-right"><span id="balance">0.00</span></td>
                                </tr>
                                <tr>
                                    <td><?= lang("change_usd") ?></td>
                                    <td class="text-right"><span id="change_usd">0.00</span></td>
                                    <td><?=lang("change_riel");?></td>
                                    <td class="text-right"><span id="change_riel">0.00</span></td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-3 text-center">
                        <span style="font-size: 1.2em; font-weight: bold;"><?=lang('quick_cash');?></span>

                        <div class="btn-group btn-group-vertical">
                            <button type="button" class="btn btn-lg btn-danger quick-cash" id="quick-payable">0.00
                            </button>
                            <?php
                                foreach (lang('quick_cash_notes') as $cash_note_amount) {
                                    echo '<button type="button" class="btn btn-lg btn-primary quick-cash">' . $cash_note_amount . '</button>';
                                }
                            ?>
                            <button type="button" class="btn btn-lg btn-danger"
                                    id="clear-cash-notes"><?=lang('clear');?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-block btn-lg btn-primary" id="submit-sale"><?=lang('submit');?></button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="comboModal" tabindex="-1" role="dialog" aria-labelledby="comboModalLabel" aria-hidden="true" >
    <div class="modal-dialog" style="width:50%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="comboModalLabel"></h4>
            </div>
            <div class="modal-body" style="margin-top:-15px !important;">
                <label class="table-label"><?= lang("combo_products"); ?></label>
                <table id="comboProduct" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                    <thead>
                        <tr>
                            <th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                            <?php if ($Settings->qty_operation) { ?>
                                <th><?= lang('width') ?></th>
                                <th><?= lang('height') ?></th>
                            <?php } ?>
                            <th><?= lang('quantity') ?></th>
                            <th><?= lang('price') ?></th>
                            <th width="3%">
                                <a id="add_comboProduct" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            
            
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editCombo"><?=lang('submit')?></button>
            </div>
            
        </div>
    </div>
</div>

<div class="modal" id="cmModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <?php if($suspend_option != 0){ ?>
                    <div class="form-group">
                        <?= lang('tags', 'tags'); ?>
                        <?php
                        $tags1 = array(0 => lang('no'));
                        foreach($tags as $tag){
                            $tags1[$tag->name] = $tag->name;
                        }
                        ?>
                        <?= form_dropdown('tags', $tags1, '', 'class="form-control" id="tags" style="width:100%;"'); ?>
                    </div>
                    <script type="text/javascript">
                        $(function(){
                            $("#tags").on("change",function(){
                                var tags = $(this).val();
                                $("#icomment").val(tags);
                            });
                        });
                    </script>
                <?php } ?>
                <div class="form-group">
                    <?= lang('comment', 'icomment'); ?>
                    <?= form_textarea('comment', '', 'class="form-control" id="icomment" style="height:80px;"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('ordered', 'iordered'); ?>
                    <?php
                    $opts = array(0 => lang('no'), 1 => lang('yes'));
                    ?>
                    <?= form_dropdown('ordered', $opts, '', 'class="form-control" id="iordered" style="width:100%;"'); ?>
                </div>
                <input type="hidden" id="irow_id" value=""/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) {
                        ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?=lang('product_tax')?></label>
                            <div class="col-sm-8">
                                <?php
                                    $tr[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                    ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_serial) { ?>
                        
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <div id="pserials-div"></div>
                                <input type="hidden" class="form-control" id="pserial">
                                <input type="hidden" class="form-control" id="pscost">
                            </div>
                        </div>
                    <?php } ?>
                    <?php if (($Admin || $Owner || $GP['pos-return_order'] == 1) && $pos_settings->table_enable == 1) { ?>
                        <div class="form-group hidden">
                            <label for="return_quantity" class="col-sm-4 control-label"><?=lang('return_quantity')?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="return_quantity">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?=lang('quantity')?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    
                    <?php if(in_array('bom',$this->config->item('product_types'))) { ?>
                        <div class="form-group">
                            <label for="pbom_type" class="col-sm-4 control-label"><?= lang('bom_type') ?></label>
                            <div class="col-sm-8">
                                <div id="pbom_type-div"></div>
                            </div>
                        </div>    
                    <?php } ?>
                    
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?=lang('product_option')?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?=lang('product_discount')?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    
                    <?php if($this->config->item('product_currency')==true) { ?>
                        <div class="form-group">
                            <label for="pproduct_currency" class="col-sm-4 control-label"><?= lang('product_currency') ?></label>
                            <div class="col-sm-8">
                                <div id="pproduct_currency-div"></div>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?=lang('unit_price')?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <?php if ($Settings->product_additional){ ?>
                        <div class="form-group">
                            <label for="paditional" class="col-sm-4 control-label"><?= lang('product_additional') ?></label>
                            <div class="col-sm-8">
                                <div id="paditional-div"></div>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:20%; display:none !important"><?=lang('product_tax');?></th>
                            <th style="width:20%; display:none !important"><span id="pro_tax"></span></th>
                            <th style="width:25%;"><?=lang('total');?></th>
                            <th style="width:25%;"><span id="pro_total"></span></th>
                        </tr>
                    </table>
                    
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="panel panel-default">
                            <div class="panel-heading"><?= lang('calculate_product_discount'); ?></div>
                            <div class="panel-body">

                                <div class="form-group">
                                    <label for="tpdiscount" class="col-sm-4 control-label"><?= lang('discount') ?></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="tpdiscount">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <input type="hidden" id="hpro_total"/>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?=lang('sell_gift_card');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('enter_info');?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?=lang("card_no", "gccard_no");?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                            <a href="#" id="genNo"><i class="fa fa-cogs"></i></a>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?=lang('gift_card')?>" id="gcname"/>

                <div class="form-group">
                    <?=lang("value", "gcvalue");?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("price", "gcprice");?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("customer", "gccustomer");?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("expiry_date", "gcexpiry");?>
                    <?php echo form_input('gcexpiry', $this->cus->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?=lang('sell_gift_card')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?=lang('add_product_manually')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>
                        <div style="width:64%; padding-left:2.55%" class="col-sm-8 input-group">
                            <input type="text" class="form-control" id="mcode">
                            <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;">
                                <i class="fa fa-random"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?=lang('product_name')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-text" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) {
                        ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?=lang('product_tax')?> *</label>

                            <div class="col-sm-8">
                                <?php
                                    $tr[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control pos-input-tip" style="width:100%;"');
                                    ?>
                            </div>
                        </div>
                    <?php }
                    ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?=lang('quantity')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?=lang('product_discount')?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="mdiscount">
                            </div>
                        </div>
                    <?php }
                    ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?=lang('unit_price')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mprice">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mcost" class="col-sm-4 control-label"><?=lang('unit_cost')?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mcost">
                        </div>
                    </div>
                    
                    <?php if($Settings->overselling && ($Owner || $Admin || $GP['products-add'])){ ?>
                        <div class="form-group">
                            <label for="add_product" class="col-sm-4 control-label"><?= lang('add_to_list_products') ?></label>
                            <div class="col-sm-8">
                                <select id="add_product" class="add_product" style="width:100%">
                                    <option value="0"><?= lang('no') ?></option>
                                    <option value="1"><?= lang('yes') ?></option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?=lang('product_tax');?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="sckModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                <i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span>
                </button>
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
                <h4 class="modal-title" id="mModalLabel"><?=lang('shortcut_keys')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <table class="table table-bordered table-striped table-condensed table-hover"
                       style="margin-bottom: 0px;">
                    <thead>
                    <tr>
                        <th><?=lang('shortcut_keys')?></th>
                        <th><?=lang('actions')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?=$pos_settings->focus_add_item?></td>
                        <td><?=lang('focus_add_item')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->add_manual_product?></td>
                        <td><?=lang('add_manual_product')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->customer_selection?></td>
                        <td><?=lang('customer_selection')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->add_customer?></td>
                        <td><?=lang('add_customer')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->toggle_category_slider?></td>
                        <td><?=lang('toggle_category_slider')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->toggle_subcategory_slider?></td>
                        <td><?=lang('toggle_subcategory_slider')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->cancel_sale?></td>
                        <td><?=lang('cancel_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->suspend_sale?></td>
                        <td><?=lang('suspend_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->print_items_list?></td>
                        <td><?=lang('print_items_list')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->finalize_sale?></td>
                        <td><?=lang('finalize_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->today_sale?></td>
                        <td><?=lang('today_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->open_hold_bills?></td>
                        <td><?=lang('open_hold_bills')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->close_register?></td>
                        <td><?=lang('close_register')?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="vModal" tabindex="-1" role="dialog" aria-labelledby="sModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="sModalLabel"><?=lang('car_operation');?></h4>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang("vehicle_model", "vehicle_model"); ?>
                        <?php echo form_input('vehicle_model', (isset($_POST['vehicle_model']) ? $_POST['vehicle_model'] : ''), 'class="form-control input-tip" id="vehicle_model"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vehicle_kilometers", "vehicle_kilometers"); ?>
                        <?php echo form_input('vehicle_kilometers', (isset($_POST['vehicle_kilometers']) ? $_POST['vehicle_kilometers'] : ''), 'class="form-control input-tip" id="vehicle_kilometers"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vehicle_vin_no", "vehicle_vin_no"); ?>
                        <?php echo form_input('vehicle_vin_no', (isset($_POST['vehicle_vin_no']) ? $_POST['vehicle_vin_no'] : ''), 'class="form-control input-tip" id="vehicle_vin_no"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vehicle_plate", "vehicle_plate"); ?>
                        <?php echo form_input('vehicle_plate', (isset($_POST['vehicle_plate']) ? $_POST['vehicle_plate'] : ''), 'class="form-control input-tip" id="vehicle_plate"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("job_number", "job_number"); ?>
                        <?php echo form_input('job_number', (isset($_POST['job_number']) ? $_POST['job_number'] : ''), 'class="form-control input-tip" id="job_number"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("mechanic", "mechanic"); ?>
                        <?php echo form_input('mechanic', (isset($_POST['mechanic']) ? $_POST['mechanic'] : ''), 'class="form-control input-tip" id="mechanic"'); ?>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="updateVehicle" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="dsModal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="dsModalLabel"><?=lang('edit_order_discount');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("order_discount", "order_discount_input");?>
                    <?php echo form_input('order_discount_input', '', 'class="form-control kb-pad" id="order_discount_input"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateOrderDiscount" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="sModal" tabindex="-1" role="dialog" aria-labelledby="sModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="sModalLabel"><?=lang('shipping');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("shipping", "shipping_input");?>
                    <?php echo form_input('shipping_input', '', 'class="form-control kb-pad" id="shipping_input"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateShipping" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="txModal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="txModalLabel"><?=lang('edit_order_tax');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("order_tax", "order_tax_input");?>
                        <?php
                            $tr[""] = "";
                            foreach ($tax_rates as $tax) {
                                $tr[$tax->id] = $tax->name;
                            }
                            echo form_dropdown('order_tax_input', $tr, "", 'id="order_tax_input" class="form-control pos-input-tip" style="width:100%;"');
                        ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateOrderTax" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="susModal" tabindex="-1" role="dialog" aria-labelledby="susModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="susModalLabel"><?=lang('suspend_sale');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('type_reference_note');?></p>

                <div class="form-group">
                    <?=lang("reference_note", "reference_note");?>
<?php echo form_input('reference_note', $reference_note, 'class="form-control kb-text" id="reference_note"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="suspend_sale" class="btn btn-primary"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<?php 
    if($suspend_option != 0){
        foreach($types as $type){ ?>        
            <div id="order_tbl_<?= strtolower($type->name) ?>" data-item="<?= strtolower($type->name) ?>" class="hidden print_order">           
                <span id="order_span_<?= strtolower($type->name) ?>"></span>
                <table id="order-table-<?= strtolower($type->name) ?>" class="prT table table-striped" style="margin-bottom:0;" width="100%"></table>
            </div>
<?php   } 
    }
?>

<div id="order_tbl">
    <span id="order_span"></span>
    <table id="order-table" class="prT table table-striped" style="margin-bottom:0;" width="100%"></table>
</div>
<span class="hidden" id="bill_company"><?php echo $biller->company;  ?></span>
<span class="hidden" id="bill_address"><?php echo $biller->address . "<br>" . $biller->city . " " . $biller->postal_code . " " . $biller->state . "<br>" . $biller->country; ?></span>
<span class="hidden" id="bill_phone"><?php echo lang("tel") . ": " . $biller->phone;  ?></span>
<div id="bill_tbl">
    <span id="bill_span"></span>
    <table id="bill-table" width="100%" class="prT table table-striped table-condensed" style="margin-bottom:0;"></table>
    <table id="bill-total-table" class="prT table" style="margin-bottom:0;" width="100%"></table>
    <center><span id="bill_number" style="font-size:38px; color:#EEE; opacity:0.5; font-weight:bold; top:15%; position:absolute;"></span></center>
    <span id="bill_footer"></span>
</div>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2"
     aria-hidden="true"></div>
<div id="modal-loading" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>
<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code);?>
<script type="text/javascript">
var site = <?=json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats))?>, pos_settings = <?=json_encode($pos_settings);?>;
var lang = {
    unexpected_value: '<?=lang('unexpected_value');?>', 
    select_above: '<?=lang('select_above');?>', 
    r_u_sure: '<?=lang('r_u_sure');?>', 
    bill: '<?=lang('bill');?>', 
    order: '<?=lang('order');?>', 
    total: '<?=lang('total');?>',
    items: '<?=lang('items');?>',
    discount: '<?=lang('discount');?>',
    order_tax: '<?=lang('order_tax');?>',
    grand_total: '<?=lang('grand_total');?>',
    total_payable: '<?=lang('total_payable');?>',
    rounding: '<?=lang('rounding');?>',
    merchant_copy: '<?=lang('merchant_copy');?>',
    description : '<?= lang("description") ?>', 
    qty : '<?= lang("qty") ?>', 
    unit_price : '<?= lang("unit_price") ?>',
    no : '<?= lang("no") ?>',
    paid_l_t_payable : '<?= lang("paid_l_t_payable") ?>',
    x_total : '<?= lang("x_total") ?>',
    
};
</script>

<script type="text/javascript">
    var product_variant = 0, shipping = 0, p_page = 0, per_page = 0, tcp = "<?=$tcp?>", pro_limit = <?= $pos_settings->pro_limit; ?>,
        brand_id = 0, obrand_id = 0, cat_id = "<?=$pos_settings->default_category?>", ocat_id = "<?=$pos_settings->default_category?>", sub_cat_id = 0, osub_cat_id,
        count = 1, an = 1, DT = <?=$Settings->default_tax_rate?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, total_paid = 0, grand_total = 0,
        KB = <?=$pos_settings->keyboard?>, tax_rates =<?php echo json_encode($tax_rates); ?>;
    var protect_delete = <?php if (!$Owner && !$Admin) {echo $pos_settings->pin_code ? '1' : '0';} else {echo '0';} ?>, billers = <?= json_encode($posbillers); ?>, biller = <?= json_encode($posbiller); ?>, pos_delorder = <?php if (!$Owner && !$Admin && $GP['pos-delete_order']!=1) { echo '1'; } else { echo '0'; } ?>;
    var username = '<?=$this->session->userdata('username');?>', order_data = '', bill_data = '';
    var bill_id = '<?= $sid ?>', user_id = '<?= $this->session->userdata('user_id');?>';
    var kh_rate = '<?= $this->site->getCurrencyByCode("KHR")->rate  ?>';
    var allow_min_price = "<?= $pos_settings->allow_min_price ?>";
    function widthFunctions(e) {
        var wh = $(window).height(),
            lth = $('#left-top').height(),
            lbh = $('#left-bottom').height()
            pbt = $("#panel-top").height(),
            pnb = $("#panel-bottom").height();
        <?php if($pos_settings->pos_category_fix==1 && $pos_settings->quick_pos != 1){ ?>
            $('#item-list').css("height", wh - pbt - pnb - 155); // Height POS Dashboard
            $('#item-list').css("min-height", 407);
        <?php }else{ ?>
        $('#item-list').css("height", wh - 180);
        $('#item-list').css("min-height", 407);
        <?php } ?>
        $('#left-middle').css("height", wh - lth - lbh - 102);
        $('#left-middle').css("min-height", 278);
        $('#product-list').css("height", wh - lth - lbh - 107);
        $('#product-list').css("min-height", 278);
        // For quick pos setting
        var b_top = $('#b-top').height(), b_bottom = $("#b-bottom").height();
        $('#b-middle').css("height", wh - b_top - b_bottom - 180);
    }
    $(window).bind("resize", widthFunctions);
    $(document).ready(function () {
        $('#poscustomer').on("select2-selecting", select_customer);
        select_customer();
        function select_customer(e){            
            var customer = (localStorage.getItem('poscustomer')?localStorage.getItem('poscustomer'):"<?=$customer->id;?>");         
            if(e){
                customer = e.val;
            }
            $.ajax({
              url : site.base_url + "sales/get_company",
              dataType : "JSON",
              type : "GET",
              data : { customer : customer },
              success : function(saleman_id){
                 <?php if($sid && isset($sbill_items) && $sbill_items->saleman_id > 0){ ?>
                        $("#saleman_id").val("<?= $sbill_items->saleman_id ?>");
                 <?php } else { ?>
                        if(saleman_id=='0'){
                            saleman_id = <?= $this->session->userdata('user_id') ?>;
                        }
                        $("#saleman_id").val(saleman_id);   
                 <?php } ?>
              }
           });
           

        }
        
        $('#view-customer').click(function(){
            $('#myModal').modal({remote: site.base_url + 'customers/view/' + $("input[name=customer]").val()});
            $('#myModal').modal('show');
        });
        $('textarea').keydown(function (e) {
            if (e.which == 13) {
               var s = $(this).val();
               $(this).val(s+'\n').focus();
               e.preventDefault();
               return false;
            }
        });
        
        <?php if ($sid) {?>
            localStorage.setItem('poswarehouse', "<?= isset($warehouse_id)? $warehouse_id: '' ?>");
            localStorage.setItem('positems', JSON.stringify(<?=$items;?>));
        <?php } else if ($this->session->userdata('remove_posls')) {?>
        if (localStorage.getItem('positems')) {
            localStorage.removeItem('positems');
        }
        if (localStorage.getItem('posdiscount')) {
            localStorage.removeItem('posdiscount');
        }
        if (localStorage.getItem('postax2')) {
            localStorage.removeItem('postax2');
        }
        if (localStorage.getItem('posshipping')) {
            localStorage.removeItem('posshipping');
        }
        if (localStorage.getItem('vehicle_model')) {
            localStorage.removeItem('vehicle_model');
        }
        if (localStorage.getItem('vehicle_kilometers')) {
            localStorage.removeItem('vehicle_kilometers');
        }
        if (localStorage.getItem('vehicle_vin_no')) {
            localStorage.removeItem('vehicle_vin_no');
        }
        if (localStorage.getItem('vehicle_plate')) {
            localStorage.removeItem('vehicle_plate');
        }
        if (localStorage.getItem('job_number')) {
            localStorage.removeItem('job_number');
        }
        if (localStorage.getItem('mechanic')) {
            localStorage.removeItem('mechanic');
        }
        if (localStorage.getItem('poswarehouse')) {
            localStorage.removeItem('poswarehouse');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('poscustomer')) {
            localStorage.removeItem('poscustomer');
        }
        if (localStorage.getItem('posbiller')) {
            localStorage.removeItem('posbiller');
        }
        if (localStorage.getItem('poscurrency')) {
            localStorage.removeItem('poscurrency');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('staffnote')) {
            localStorage.removeItem('staffnote');
        }
        <?php $this->cus->unset_data('remove_posls');}
        
        ?>
        widthFunctions();
        <?php if ($suspend_sale) {?>
        localStorage.setItem('postax2', '<?=$suspend_sale->order_tax_id;?>');
        localStorage.setItem('posdiscount', '<?=$suspend_sale->order_discount_id;?>');
        localStorage.setItem('poswarehouse', '<?=$suspend_sale->warehouse_id;?>');
        localStorage.setItem('poscustomer', '<?=$suspend_sale->customer_id;?>');
        localStorage.setItem('posbiller', '<?=$suspend_sale->biller_id;?>');
        localStorage.setItem('posshipping', '<?=$suspend_sale->shipping;?>');
        <?php }
        ?>
        <?php if ($this->input->get('customer')) {?>
        if (!localStorage.getItem('positems')) {
            localStorage.setItem('poscustomer', <?=$this->input->get('customer');?>);
        } else if (!localStorage.getItem('poscustomer')) {
            localStorage.setItem('poscustomer', <?=$customer->id;?>);
        }
        <?php } else {?>
        if (!localStorage.getItem('poscustomer')) {
            localStorage.setItem('poscustomer', <?=$customer->id;?>);
        }
        <?php }
        ?>
        if (!localStorage.getItem('postax2')) {
            localStorage.setItem('postax2', <?=$Settings->default_tax_rate2;?>);
        }
        $('.select').select2({minimumResultsForSearch: 7});
        // var customers = [{
        //     id: <?=$customer->id;?>,
        //     text: '<?=$customer->company == '-' ? $customer->name : $customer->company;?>'
        // }];
        $('#poscustomer').val(localStorage.getItem('poscustomer')).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?=site_url('customers/getCustomer')?>/" + $(element).val(),
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        callback(data[0]);                      
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
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
        if (KB) {
            display_keyboards();

            var result = false, sct = '';
            $('#poscustomer').on('select2-opening', function () {
                sct = '';
                $('.select2-input').addClass('kb-text');
                display_keyboards();
                $('.select2-input').bind('change.keyboard', function (e, keyboard, el) {
                    if (el && el.value != '' && el.value.length > 0 && sct != el.value) {
                        sct = el.value;
                    }
                    if(!el && sct.length > 0) {
                        $('.select2-input').addClass('select2-active');
                        $.ajax({
                            type: "get",
                            async: false,
                            url: "<?=site_url('customers/suggestions')?>/" + sct,
                            dataType: "json",
                            success: function (res) {
                                if (res.results != null) {
                                    $('#poscustomer').select2({data: res}).select2('open');
                                    $('.select2-input').removeClass('select2-active');
                                } else {
                                    bootbox.alert('no_match_found');
                                    $('#poscustomer').select2('close');
                                    $('#test').click();
                                }
                            }
                        });
                    }
                });
            });

            $('#poscustomer').on('select2-close', function () {
                $('.select2-input').removeClass('kb-text');
                $('#test').click();
                $('select, .select').select2('destroy');
                $('select, .select').select2({minimumResultsForSearch: 7});
            });
            $(document).bind('click', '#test', function () {
                var kb = $('#test').keyboard().getkeyboard();
                kb.close();
                //kb.destroy();
                $('#add-item').focus();
            });

        }
        $(document).on('change', '#project', function () {
            var sb = $(this).val();
            $('#project_id').val(sb);
        });
        $(document).on('change', '#posbiller', function () {
            var sb = $(this).val();
            $.each(billers, function () {
                if(this.id == sb) {
                    biller = this;
                }
            });
            $('#biller').val(sb);
        });

        <?php for ($i = 1; $i <= 5; $i++) {?>
        $('#paymentModal').on('change', '#amount_<?=$i?>', function (e) {
            $('#amount_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('blur', '#amount_<?=$i?>', function (e) {
            $('#amount_val_<?=$i?>').val($(this).val());
        });
        
        $('#paymentModal').on('select2-close', '#paid_by_<?=$i?>', function (e) {
            $('#paid_by_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_no_<?=$i?>', function (e) {
            $('#cc_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_holder_<?=$i?>', function (e) {
            $('#cc_holder_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#gift_card_no_<?=$i?>', function (e) {
            $('#paying_gift_card_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_month_<?=$i?>', function (e) {
            $('#cc_month_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_year_<?=$i?>', function (e) {
            $('#cc_year_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_type_<?=$i?>', function (e) {
            $('#cc_type_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_cvv2_<?=$i?>', function (e) {
            $('#cc_cvv2_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#cheque_no_<?=$i?>', function (e) {
            $('#cheque_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#payment_note_<?=$i?>', function (e) {
            $('#payment_note_val_<?=$i?>').val($(this).val());
        });
        <?php }
        ?>

        $('#payment').click(function () {
            
            <?php if ($sid) {?>
            suspend = $('<span></span>');                       
            suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" />');
            suspend.appendTo("#hidesuspend");
            <?php }
            ?>
            var twt = formatDecimal((total + invoice_tax) - order_discount + shipping);
            if (an == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            gtotal = formatDecimal(twt,<?= $Settings->decimals ?>);
            <?php if ($pos_settings->rounding) {?>
            round_total = roundNumber(gtotal, <?=$pos_settings->rounding?>);
            var rounding = formatDecimal(0 - (gtotal - round_total));
            $('#twt').text(formatMoney(round_total) + ' (' + formatMoney(rounding) + ')');
            $('#quick-payable').text(round_total);
            <?php } else {?>
            $('#twt').text(formatMoney(gtotal));
            $('#quick-payable').text(gtotal);
            <?php }
            ?>
            $('#item_count, .item_count').text(count - 1);          
            $('#paymentModal').appendTo("body").modal('show');
            $('#amount_1').focus();
        });
        $('#paymentModal').on('show.bs.modal', function(e) {
            $('#submit-sale').text('<?=lang('submit');?>').attr('disabled', false);
        });
        $('#paymentModal').on('shown.bs.modal', function(e) {
            $(".base_amount").val(total_paid).trigger("keyup").focus();
            <?php if($pos_settings->quick_payable==1){ ?>
                if($(".base_amount").val() == 0){
                   $("#quick-payable").trigger("click");
                }
            <?php } ?>
        });
        
        var pi = 'amount_1', pa = 2;
        $(document).on('click', '.quick-cash', function () {
            var $quick_cash = $(this);
            var amt = $quick_cash.contents().filter(function () {
                return this.nodeType == 3;
            }).text();
            var th = ',';
            var $pi = $('#' + pi);
            amt = formatDecimal(amt.split(th).join(""),<?= $Settings->decimals ?>) * 1 + $pi.val() * 1;
            $pi.val(formatDecimal(amt,<?= $Settings->decimals ?>)).focus();
            $(".base_amount").val(formatDecimal(amt,<?= $Settings->decimals ?>)).trigger("keyup");
            var note_count = $quick_cash.find('span');
            if (note_count.length == 0) {
                $quick_cash.append('<span class="badge">1</span>');
            } else {
                note_count.text(parseInt(note_count.text()) + 1);
            }
        });

        $(document).on('click', '#clear-cash-notes', function () {
            $('.quick-cash').find('.badge').remove();
            $('#' + pi).val('0').focus();
            $(".base_amount").val('0').trigger("keyup");
        });

        $(document).on('change', '.gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            var payid = $(this).attr('id'),
                id = payid.substr(payid.length - 1);
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#poscustomer').val()) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');
                        } else {
                            $('#gc_details_' + id).html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no_' + id).parent('.form-group').removeClass('has-error');
                            //calculateTotals();
                            $('#amount_' + id).val(gtotal >= data.balance ? data.balance : gtotal).focus();
                        }
                    }
                });
            }
        });

        $(document).on('click', '.addButton', function () {
            if (pa <= 5) {
                $('#paid_by_1, #pcc_type_1').select2('destroy');
                var phtml = $('#payments').html(),
                    update_html = phtml.replace(/_1/g, '_' + pa);
                pi = 'amount_' + pa;
                $('#multi-payment').append('<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-1x" style="font-weight:bold;">&times;</i></button>' + update_html);
                $('#paid_by_1, #pcc_type_1, #paid_by_' + pa + ', #pcc_type_' + pa).select2({minimumResultsForSearch: 7});
                read_card();
                pa++;
            } else {
                bootbox.alert('<?=lang('max_reached')?>');
                return false;
            }
            display_keyboards();
            $('#paymentModal').css('overflow-y', 'scroll');
        });
        
        $(document).on('click', '.addMorePayment', function () {
            var multi_payment = '<tr><th><select name="m_paid_by[]" class="form-control m_paid_by">';
                multi_payment += '<?= $this->cus->cash_opts(false,true,false,true); ?>';
                multi_payment += '</select></th>';
                multi_payment += '<th><input type="text" name="m_qpaying_usd[]" class="form-control text-right m_qpaying_usd"/></th>';
                multi_payment += '<th><input type="text" name="m_qpaying_khr[]" class="form-control text-right m_qpaying_khr"/></th></tr>';
            $('#rowMultiPayment').after(multi_payment);
        });
        

        $(document).on('click', '.close-payment', function () {
            $(this).next().remove();
            $(this).remove();
            pa--;
        });
        
        $(document).on('click', '#pos_payment', function () {
            event.preventDefault();
            $('#submit_pos_payment').trigger("click");
            $(this).prop('disabled', true);

        });
        
        /************Updated Currencies***************/
                                
            /*$(".paid_by").on("change",function(){
                var paid_by = $(this).val();
                if(paid_by != "cash"){
                    $(".payment-cash").hide();                  
                }else{
                    $(".payment-cash").show();                  
                }
            });*/
            
            $(".payment").on("click",function(){
                var decimal = site.settings.decimals;
                var amount_1 = $("#amount_1").val();
                $(".total_payable").each(function(){
                    var base_rate = $(this).attr("base_rate") - 0;
                    var rate = $(this).attr("rate") - 0;
                    var payable = (formatDecimal((total + invoice_tax) - order_discount + shipping) / base_rate) * rate;
                    if(rate > 1000){
                        payable = formatMoneyKH(Math.round(payable/100) * 100);
                    }else{
                        payable = formatMoney(payable);
                    }
                    $(this).text(payable);
                }); 
                
                $(".camount").on("keyup",function(){
                    var tamount = 0, i = 0;
                    $(".camount").each(function(){
                        var amount = $(this).val()-0;
                        var base_rate = $(this).attr("base_rate")-0;
                        var rate = $(this).attr("rate")-0;
                        var camount = (amount / rate) * base_rate;
                            tamount += camount;
                        $("#camount_"+i).val(amount);                                           
                        i++;
                    });
                    $(".balance_1").each(function(){
                        var base_rate = $(this).attr("base_rate")-0;
                        var rate = $(this).attr("rate")-0;
                        var balance_1 = ((tamount - (total-order_discount+shipping)) / base_rate) * rate;
                        if(rate > 1000){
                            balance_1 = formatMoneyKH(balance_1);
                        }else{
                            balance_1 = formatMoney(balance_1);
                        }
                        $(this).text(balance_1);
                        i++;
                    });
                    $("#amount_1").val(formatDecimal(tamount,decimal)).trigger("focus keyup keypress");
                    $('#amount_val_1').val(formatDecimal(tamount,decimal));
                    calculateTotals();
                });
                
            });
            
            $(".camount").keydown(function (e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                         return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
            $('.camount').keydown( function(e){
                if ($(this).val().length >= 16) { 
                    $(this).val($(this).val().substr(0, 16));
                }
            });
            $('.camount').keyup( function(e){
                if ($(this).val().length >= 16) { 
                    $(this).val($(this).val().substr(0, 16));
                }
            });
    
        /************End Updated Currencies**************/
        
        $(document).on('focus keyup keypress', '.amount', function () {
            pi = $(this).attr('id');
            calculateTotals();
        }).on('blur keyup keypress', '.amount', function () {
            calculateTotals();
        });

        function calculateTotals() {
            var total_paying = 0;
            var ia = $(".amount");
            $.each(ia, function (i) {
                var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
                total_paying += parseFloat(this_amount);
            });
            $('#total_paying').text(formatMoney(total_paying));
            <?php if ($pos_settings->rounding) {?>
                $('#balance').text(formatMoney(total_paying - round_total));
                $('#balance_' + pi).val(formatDecimal(total_paying - round_total));
                total_paid = total_paying;
                grand_total = round_total;
            <?php } else {?>
                $('#balance').text(formatMoney(total_paying - gtotal));
                $('#balance_' + pi).val(formatDecimal(total_paying - gtotal));
                total_paid = total_paying;
                grand_total = gtotal;
            <?php } ?>
            
            var change = total_paid - grand_total;
            if(change > 0){
                change = change.toString();
                if (change.indexOf(".") >= 0){
                    var res = change.split(".");
                    var change_usd = formatDecimal(res[0],4);
                    var change_riel = formatDecimal(("0."+res[1]),4) * kh_rate;
                    if(change_riel > 0){
                        change_riel = Math.round(change_riel / 100);
                        change_riel = change_riel * 100;
                    }
                    $("#change_usd").html(formatMoney(change_usd));
                    $("#change_riel").html(change_riel+" ៛");
                }else{
                    $("#change_usd").html(formatMoney(change));
                    $("#change_riel").html(0);
                }
            }else{
                $("#change_usd").html(formatMoney(0));
                $("#change_riel").html(0);
            }
            
            
            
        }

        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#poscustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?=site_url('sales/suggestions');?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#poswarehouse").val(),
                        customer_id: $("#poscustomer").val()
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                }
            },
            select: function (event, ui) {              
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var product_type = ui.item.row.type;
                    if (product_type == 'digital') {
                        $.ajax({
                            type: 'get',
                            url: '<?= site_url('sales/suggestionsDigital'); ?>',
                            dataType: "json",
                            data: {
                                term : ui.item.item_id,
                                warehouse_id: $("#slwarehouse").val(),
                                customer_id: $("#slcustomer").val(),
                            },
                            success: function (result) {
                                $.each( result, function(key, value) {
                                    var row = add_invoice_item(value);
                                    if (row)
                                        $(this).val('');
                                });
                            }
                        });
                        $(this).val('');
                    }else {
                        var row = add_invoice_item(ui.item);
                        if (row)
                            $(this).val('');
                    }
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        <?php if ($pos_settings->tooltips) {echo '$(".pos-tip").tooltip();';} ?>
        // $('#posTable').stickyTableHeaders({fixedOffset: $('#product-list')});
        $('#posTable').stickyTableHeaders({scrollableArea: $('#product-list')});
        $('#product-list,#brands-list, #category-list, #subcategory-list').perfectScrollbar({suppressScrollX: true});
        $('select, .select').select2({minimumResultsForSearch: 7});

        $(document).on('click', '.product', function (e) {
            $('#modal-loading').show();
            code = $(this).val(),
            wh = $('#poswarehouse').val(),
            cu = $('#poscustomer').val();
            $.ajax({
                type: "get",
                url: "<?=site_url('pos/getProductDataByCode')?>",
                data: {code: code, warehouse_id: wh, customer_id: cu},
                dataType: "json",
                success: function (data) {
                    e.preventDefault();
                    if (data !== null) {
                        if (data.id !== 0) {
                            var product_type = data.row.type;
                            if (product_type == 'digital') {
                                $.ajax({
                                    type: 'get',
                                    url: '<?= site_url('sales/suggestionsDigital'); ?>',
                                    dataType: "json",
                                    data: {
                                        term : data.item_id,
                                        warehouse_id: $("#slwarehouse").val(),
                                        customer_id: $("#slcustomer").val(),
                                    },
                                    success: function (result) {
                                        $.each( result, function(key, value) {
                                            var row = add_invoice_item(value);
                                            if (row)
                                                $(this).val('');
                                        });
                                    }
                                });
                                $(this).val('');
                            }else {
                                var row = add_invoice_item(data);
                                if (row)
                                    $(this).val('');
                                
                            }
                        } else {
                            bootbox.alert('<?= lang('no_match_found') ?>');
                        }
                        $('#modal-loading').hide();
                    } else {
                        bootbox.alert('<?=lang('no_match_found')?>');
                        $('#modal-loading').hide();
                    }
                }
            });
        });
        
        $(document).on('click', '#favorite', function () {
            if (cat_id != $(this).val()) {
                $(".sp_code").val("");
                $(".sp_name").val("");
                var sp_favorite = $(".sp_favorite").val()==1?0:1;
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxcategorydata');?>",
                    data: {
                        category_id : cat_id, 
                        sp_favorite : sp_favorite,
                    },
                    dataType: "json",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        $('#subcategory-list').empty();
                        var newScs = $('<div></div>');
                        newScs.html(data.subcategories);
                        newScs.appendTo("#subcategory-list");
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function () {
                        p_page = 'n';
                        $('#category-' + cat_id).addClass('active');
                        $('#category-' + ocat_id).removeClass('active');
                        ocat_id = cat_id;
                        $('#modal-loading').hide();
                        nav_pointer();
                        $(".sp_favorite").val(sp_favorite);
                    });
            }
        });
        $('#random_num').click(function(){
            $(this).parent('.input-group').children('input').val(generateCardNo(8));
        });
        $(document).on('change keypress keyup', '.sp_code,.sp_name', function () {
            if (cat_id != $(this).val()) {
                var sp_code = $(".sp_code").val().trim();
                var sp_name = $(".sp_name").val().trim();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxcategorydata');?>",
                    data: {
                        category_id : cat_id, 
                        sp_code : sp_code,
                        sp_name : sp_name,
                    },
                    dataType: "json",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        $('#subcategory-list').empty();
                        var newScs = $('<div></div>');
                        newScs.html(data.subcategories);
                        newScs.appendTo("#subcategory-list");
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function () {
                        p_page = 'n';
                        $('#category-' + cat_id).addClass('active');
                        $('#category-' + ocat_id).removeClass('active');
                        ocat_id = cat_id;
                        $('#modal-loading').hide();
                        nav_pointer();
                    });
            }
        });
        
        $(document).on('click', '.category', function () {
            if (cat_id != $(this).val()) {
                if(!$(this).attr("disabled-open-category")){
                    $('#open-category').click();
                }
                <?php if($pos_settings->pos_category_fix==1 && $pos_settings->quick_pos != 1){ ?>
                    $(".category").removeClass("cl-danger");
                    $(this).addClass("cl-danger");
                    sub_cat_id = 0;
                <?php } ?>
                
                $('#modal-loading').show();
                cat_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxcategorydata');?>",
                    data: {
                        category_id : cat_id, 
                    },
                    dataType: "json",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        $('#subcategory-list').empty();
                        var newScs = $('<div></div>');
                        newScs.html(data.subcategories);
                        newScs.appendTo("#subcategory-list");
                        
                        <?php if($pos_settings->pos_category_fix==1 && $pos_settings->quick_pos != 1){ ?>
                            $('#pos-subcategories').empty();
                            var newScs1 = $('<span></span>');
                            newScs1.html(data.subcategories);
                            newScs1.appendTo("#pos-subcategories");
                        <?php } ?>
                        
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#category-' + cat_id).addClass('active');
                    $('#category-' + ocat_id).removeClass('active');
                    ocat_id = cat_id;
                    $('#modal-loading').hide();
                    nav_pointer();
                });
            }
        });
        
        $('#category-' + cat_id).addClass('active');

        $(document).on('click', '.brand', function () {
            if (brand_id != $(this).val()) {
                $('#open-brands').click();
                $('#modal-loading').show();
                brand_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxbranddata');?>",
                    data: {brand_id: brand_id},
                    dataType: "json",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#brand-' + brand_id).addClass('active');
                    $('#brand-' + obrand_id).removeClass('active');
                    obrand_id = brand_id;
                    $('#category-' + cat_id).removeClass('active');
                    $('#subcategory-' + sub_cat_id).removeClass('active');
                    cat_id = 0; sub_cat_id = 0;
                    $('#modal-loading').hide();
                    nav_pointer();
                });
            }
        });

        $(document).on('click', '.subcategory', function () {
            if (sub_cat_id != $(this).val()) {
                
                <?php if($pos_settings->pos_category_fix==1 && $pos_settings->quick_pos != 1){ ?>
                    $(".subcategory").removeClass("cl-danger");
                    $(this).addClass("cl-danger");
                <?php } ?>
                
                $('#open-subcategory').click();
                $('#modal-loading').show();
                sub_cat_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#subcategory-' + sub_cat_id).addClass('active');
                    $('#subcategory-' + osub_cat_id).removeClass('active');
                    $('#modal-loading').hide();
                });
            }
        });
        
        var per_page_c = 0;
        var total_row_c = "<?= $this->pos_model->categories_count(); ?>" - 0;
        var category_row = "<?= $this->config->item("category_rows") ?>" - 0;
        
        $('#next_c').click(function () {
            per_page_c += category_row;
            if(per_page_c < 0){
                per_page_c = 0;
            }
            $.ajax({
                type: "get",
                url: "<?=site_url('pos/ajax_categories');?>",
                data: { per_page : per_page_c , active : cat_id },
                dataType: "json",
                success: function (data) {
                    $('.cpcategory').empty();
                    var newPrs = $('<div></div>');
                    newPrs.html(data.html);
                    newPrs.appendTo(".cpcategory");
                    cpointer();
                }
            }).done(function () {
                $('#modal-loading').hide();
            });
            
        });
        
        $('#previous_c').click(function () {
            per_page_c -= category_row;
            if(per_page_c < 0){
                per_page_c = 0;
            }
            $.ajax({
                type: "get",
                url: "<?=site_url('pos/ajax_categories');?>",
                data: { per_page : per_page_c , active : cat_id  },
                dataType: "json",
                success: function (data) {
                    $('.cpcategory').empty();
                    var newPrs = $('<div></div>');
                    newPrs.html(data.html);
                    newPrs.appendTo(".cpcategory");
                    cpointer();
                }
            }).done(function () {
                $('#modal-loading').hide();
            });
            
        });
        
        cpointer();
        
        function cpointer()
        {
            if( per_page_c >= total_row_c || total_row_c <= per_page_c + category_row){
                $('#next_c').attr("disabled", "disabled");
            }else{
                $('#next_c').removeAttr("disabled");
            } 
            if(per_page_c > 0){
                $('#previous_c').removeAttr("disabled");
            }else{
                $('#previous_c').attr("disabled", "disabled");
            }
        }
        
        $('#next').click(function () {
            var sp_code = $(".sp_code").val().trim();
            var sp_name = $(".sp_name").val().trim();
            var sp_favorite = $(".sp_favorite").val().trim();
            if (p_page == 'n') {
                p_page = 0
            }
            p_page = p_page + pro_limit;
            if (tcp >= pro_limit && p_page < tcp) {
                $('#modal-loading').show();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page, sp_code : sp_code, sp_name : sp_name, sp_favorite : sp_favorite},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }
                }).done(function () {
                    $('#modal-loading').hide();
                });
            } else {
                p_page = p_page - pro_limit;
            }
        });

        $('#previous').click(function () {
            var sp_code = $(".sp_code").val().trim();
            var sp_name = $(".sp_name").val().trim();
            var sp_favorite = $(".sp_favorite").val().trim();
            if (p_page == 'n') {
                p_page = 0;
            }
            if (p_page != 0) {
                $('#modal-loading').show();
                p_page = p_page - pro_limit;
                if (p_page == 0) {
                    p_page = 'n'
                }
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page, sp_code : sp_code, sp_name : sp_name, sp_favorite : sp_favorite},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }

                }).done(function () {
                    $('#modal-loading').hide();
                });
            }
        });

        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val(),
                id = $(this).attr('id'),
                pa_no = id.substr(id.length - 1);
            $('#rpaidby').val(p_val);
            if (p_val == 'gift_card') {
                $('.gc_' + pa_no).show();
                $('.ngc_' + pa_no).hide();
                $('#gift_card_no_' + pa_no).focus();
            } else {
                $('.ngc_' + pa_no).show();
                $('.gc_' + pa_no).hide();
                $('#gc_details_' + pa_no).html('');
            }
        });
        
        

        
        $(document).on('click', '#submit-sale', function () {
            
            var delivery_status = $(".delivery_status option:selected").val();
            var pos_ref = $(".pos_ref").val();
            
            <?php if($sid){ ?>
                suspend = $('<span></span>');
                suspend.html('<input type="hidden" name="submit-sale" value="1" />');
                suspend.appendTo("#hidesuspend");
            <?php } ?>
            
                if (total_paid < gtotal) {
                    
                    if(allow_min_price == 1){
                    
                        bootbox.confirm("<?=lang('paid_l_t_payable');?>", function (res) {
                            if (res == true) {
                                $("#pos_reference_no").val(pos_ref);
                                $("#delivery_status").val(delivery_status);
                                $('#pos_note').val(localStorage.getItem('posnote'));
                                $('#staff_note').val(localStorage.getItem('staffnote'));
                                $('#submit-sale').text('<?=lang('loading');?>').attr('disabled', true);
                                $('#pos-sale-form').submit();
                            }
                        });
                        return false;
                        
                    }else {
                        bootbox.alert("<?=lang('paid_l_t_payable');?>");
                        return false;
                    }
                    
                } else {
                    $("#pos_reference_no").val(pos_ref);
                    $("#delivery_status").val(delivery_status);
                    $('#pos_note').val(localStorage.getItem('posnote'));
                    $('#staff_note').val(localStorage.getItem('staffnote'));
                    $(this).text('<?=lang('loading');?>').attr('disabled', true);
                    $('#pos-sale-form').submit();
                }
            
            
            
        });
        
        $('#suspend').click(function () {
            if (count <= 1) {
                bootbox.alert('<?=lang('x_suspend');?>');
                return false;
            } else {
                $('#susModal').modal();
            }
        });
        
        $(".delete_suspend").on("click",function(){
            <?php if($Owner || $Admin || $GP["pos-delete_table"] == 1){  ?>
                var hreff = $(this).attr("hreff"); 
                bootbox.confirm("<?= lang('sure_to_cancel_sale') ?>", function (result) {
                    if (result) {
                        location.href = hreff;
                    }
                });
            <?php }else { ?>
                bootbox.alert("<?= lang("no_permission") ?>");
            <?php } ?>
            return false;
        });
                
        $('#suspend_sale').click(function () {
            ref = $('#reference_note').val();
            if (!ref || ref == '') {
                bootbox.alert('<?=lang('type_reference_note');?>');
                return false;
            } else {
                suspend = $('<span></span>');
                <?php if ($sid) {?>
                suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                <?php } else {?>
                suspend.html('<input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                <?php }
                ?>
                suspend.appendTo("#hidesuspend");
                $('#total_items').val(count - 1);
                $('#pos-sale-form').submit();
            }
        });
        
    });
 
    $(document).ready(function () {
        
        $(document).off('click', '#print_order').on('click', '#print_order',function(e) {
            $(this).attr("disabled", "disabled");
            if (an == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            <?php if($this->pos_settings->table_enable == 1){ ?>
                var an1 = "<?= $sid ?>";
                if(!an1){
                    bootbox.alert('<?=lang('please_select_table');?>');
                    return false;
                }
            <?php } ?>
            
            printOrder();
            update_bill();
            return false;
            
            <?php if ($pos_settings->remote_printing != 1) { ?>
                printOrder();
            <?php } else { ?>
                Popup($('#order_tbl').html());
            <?php } ?>
        });
        
        $(document).off('click', '#print_bill').on('click', '#print_bill',function(e) {
            if (an == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            // edition new version
            <?php if ($pos_settings->after_sale_page != 1) { ?>
                <?php if($pos_settings->table_enable==1){ ?>
                    var allow = true;
                    $(".suspend_item_id").each(function(){
                        var suspend_item_id = $(this).val();
                        if(suspend_item_id <= 0){
                            allow = false;
                        }
                    });
                    if(!allow){
                        bootbox.alert('<?=lang('x_suspend');?>');
                        return false;
                    }
                <?php } ?>
                
                Popup($('#bill_tbl').html());
                
                <?php if($pos_settings->table_enable==1){ ?>
                    add_bill();
                <?php } ?>
                
            <?php } else { ?>
                $(this).attr("disabled","disabled");
                printBill();
            <?php } ?>
            
            return false;
            
            // old version
            <?php if ($pos_settings->remote_printing != 1) { ?>
                $(this).attr("disabled","disabled");
                printBill();
            <?php } else { ?>
                Popup($('#bill_tbl').html());
            <?php } ?>
        });

        function add_bill(){
            var bill_id = "<?= $sid ?>";
            $.ajax({
                url : "<?= site_url("pos/add_bill"); ?>",
                data : { bill_id : bill_id},
                success : function(data){
                    var number_dynamic = parseFloat(data) + 1;
                    $("#bill_number").html(number_dynamic);
                }
            });
        }
        
        number_bill();
        function number_bill(number){
            var number_static = parseFloat("<?= isset($suspend_sale->print)? $suspend_sale->print: 0 ?>") + 1; 
            $("#bill_number").html(number_static);
        }
        
        $(".print_order").each(function(){
            var item = $(this).attr("data-item");
            $('#print_order_'+item).click(function () {             
                if (an == 1) {
                    bootbox.alert('<?=lang('x_total');?>');
                    return false;
                }
                <?php if ($pos_settings->remote_printing != 1) { ?>
                    printOrder();
                <?php } else { ?>
                    Popup($("#order_tbl_"+item).html());
                <?php } ?>
            });         
        });
    });
    
    $(function () {
        $(".alert").effect("shake");
        setTimeout(function () {
            $(".alert").hide('blind', {}, 500)
        }, 15000);
        <?php if ($pos_settings->display_time) {?>
        var now = new moment();
        $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        setInterval(function () {
            var now = new moment();
            $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        }, 1000);
        <?php }
        ?>
    });
    
    <?php if ($pos_settings->remote_printing == 1) { ?>
    
    function Popup(data) {
        var mywindow = window.open('', 'cus_pos_print', 'height=500,width=300');
        mywindow.document.write('<html><head><title>Print</title>');
        mywindow.document.write('<link rel="stylesheet" href="<?=$assets?>styles/helpers/bootstrap.min.css" type="text/css" />');
        mywindow.document.write('</head><body>');
        mywindow.document.write(data);
        mywindow.document.write('</body></html>');
        mywindow.print();           
        mywindow.close();
        return true;
    }
    
    function update_bill(){
        if( $(".add_suspend_item").attr("id") > 0 ){
            var table_id = $(".add_suspend_item").attr("id");
            var table_name = $(".add_suspend_item").val();
            <?php if ($sid) {?>
                suspend = $('<span></span>');                       
                suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="table_id" value="' + table_id + '" /><input type="hidden" name="table_name" value="' + table_name + '" />');                         
                suspend.appendTo("#hidesuspend");
            <?php } ?>
            $.ajax({
                  type: "POST",
                  dataType: "JSON",
                  url: site.base_url + "pos",
                  data: $("#pos-sale-form").serialize(),
                  success : function(data){
                        $.ajax({
                            url : "<?= site_url("pos/update_bill/") ?>",
                            type : "GET",
                            dataType : "JSON",
                            data : { suspend_id : "<?= $sid; ?>" },
                            success : function(data){               
                                $(".item_ordered").val(data.count);
                                localStorage.setItem('positems', JSON.stringify(data.pr));
                                loadItems();
                                $("#print_order").removeAttr("disabled");
                                <?php if($pos_settings->pos_redirect_order==1 && $pos_settings->table_enable == 1){ ?>
                                    setInterval(function(){
                                        location.href = "<?= site_url("pos/add_table"); ?>";
                                    }, 1000);                                   
                                <?php } ?>
                            }
                        }); 
                  }
            });
        }
        if(localStorage.getItem('positems')){
            sortedItems = JSON.parse(localStorage.getItem('positems'));
            $.each(sortedItems, function () {           
                var item = this;
                var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
                positems[item_id].row.ordered = 1;              
                localStorage.setItem('positems', JSON.stringify(positems));                 
                loadItems();
            }); 
        }
    }
    
    <?php } ?>
</script>
<?php
    $s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
    foreach (lang('select2_lang') as $s2_key => $s2_line) {
        $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
    }
    $s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>
<script type="text/javascript" src="<?=$assets?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/select2.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/custom.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/plugins.min.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/parse-track-data.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/pos.ajax.js"></script>

<script type="text/javascript">

<?php if (isset($_GET['sale_id']) && $_GET['sale_id'] > 0) { ?>
        printInvoice();
        function printInvoice() {
            var socket_data = {
                'printer': <?= json_encode($printer); ?>,
                'logo': (biller && biller.logo ? biller.logo : ''),
                'text': bill_data
            };
            $.get('<?= site_url('pos/p_invoice?sale_id='.trim($_GET['sale_id'])); ?>', {data: JSON.stringify(socket_data)});
            
            <?php if ($pos_settings->table_enable == 1) { ?>
                window.location.href = "<?= site_url("pos/add_table") ?>";
            <?php }else { ?>
                //window.location.href = "<?= site_url("pos") ?>";
            <?php } ?>
            return false;
        }
<?php } ?>
<?php if ($pos_settings->table_enable == 1) { ?>
        var order_printers = <?= json_encode($order_printers); ?>;      
        function printOrder() {
            $.each(order_printers, function() {
                var socket_data = { 'printer': this, 
                'logo': (biller && biller.logo ? biller.logo : ''), 
                'text': order_data };
                $.get('<?= site_url('pos/p/order'); ?>', {data: JSON.stringify(socket_data)});
            });
            return false;
        }
        function printBill() {
            var socket_data = {
                'printer': <?= json_encode($printer); ?>,
                'logo': (biller && biller.logo ? biller.logo : ''),
                'text': bill_data
            };
            $.get('<?= site_url('pos/p_bill'); ?>', {data: JSON.stringify(socket_data), sid : "<?= ($sid?$sid:0) ?>", }).always(function() {
                $("#print_bill").removeAttr("disabled");
            });
            return false;
        }
<?php }else { ?>
        
        function printOrder() {
            bootbox.alert("<?= lang("loading") ?>");
        }
        
        function printBill() {
            bootbox.alert("<?= lang("loading") ?>");
        }
        
<?php } ?>
</script>

<?php
if ($pos_settings->remote_printing != 1) {
    ?>
    <script type="text/javascript">
        var order_printers = <?= json_encode($order_printers); ?>;
        
        function printOrder() {
            $.each(order_printers, function() {
                var socket_data = { 'printer': this, 
                'logo': (biller && biller.logo ? biller.logo : ''), 
                'text': order_data };
                $.get('<?= site_url('pos/p/order'); ?>', {data: JSON.stringify(socket_data)});
            });
            return false;
        }

        function printBill() {
            var socket_data = {
                'printer': <?= json_encode($printer); ?>,
                'logo': (biller && biller.logo ? biller.logo : ''),
                'text': bill_data
            };
            $.get('<?= site_url('pos/p'); ?>', {data: JSON.stringify(socket_data)});
            return false;
        }
    </script>
    <?php
} elseif ($pos_settings->remote_printing == 2) {
    ?>
    <script src="<?= $assets ?>js/socket.io.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        socket = io.connect('http://localhost:6440', {'reconnection': false});

        function printBill() {
            if (socket.connected) {
                var socket_data = {'printer': <?= json_encode($printer); ?>, 'text': bill_data};
                socket.emit('print-now', socket_data);
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }

        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            if (socket.connected) {
                $.each(order_printers, function() {
                    var socket_data = {'printer': this, 'text': order_data};
                    socket.emit('print-now', socket_data);
                });
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
    </script>
    <?php

} elseif ($pos_settings->remote_printing == 3) {

    ?>
    <script type="text/javascript">
        try {
            socket = new WebSocket('ws://127.0.0.1:6441');
            socket.onopen = function () {
                console.log('Connected');
                return;
            };
            socket.onclose = function () {
                console.log('Not Connected');
                return;
            };
        } catch (e) {
            console.log(e);
        }

        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            if (socket.readyState == 1) {
                $.each(order_printers, function() {
                    var socket_data = { 'printer': this, 
                    'logo': (biller && biller.logo ? site.base_url+'assets/uploads/logos/'+biller.logo : ''), 
                    'text': order_data };
                    socket.send(JSON.stringify({
                        type: 'print-receipt',
                        data: socket_data
                    }));
                });
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }

        function printBill() {
            if (socket.readyState == 1) {
                var socket_data = {
                    'printer': <?= json_encode($printer); ?>,
                    'logo': (biller && biller.logo ? site.base_url+'assets/uploads/logos/'+biller.logo : ''),
                    'text': bill_data
                };
                socket.send(JSON.stringify({
                    type: 'print-receipt',
                    data: socket_data
                }));
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
    </script>
    <?php
}
?>
<script type="text/javascript">
$('.sortable_table tbody').sortable({
    containerSelector: 'tr'
});
</script>
<script type="text/javascript" charset="UTF-8"><?=$s2_file_date?></script>
<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
<?php 
if (isset($print) && !empty($print)) {
    /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */
    include 'remote_printing.php';
}
?>
    <style type="text/css">
        <?php if( !$Owner && !$Admin && $GP['pos-show_items'] != 1){ ?>
                #ui, .product{
                    display:none !important;
                }
        <?php } ?>
    </style>
    
    <script>
        $(document).ready(function(){
            
            
            $(".combo_product:not(.ui-autocomplete-input)").live("focus", function (event) {
                $(this).autocomplete({
                    source: '<?= site_url('products/suggestions'); ?>',
                    minLength: 1,
                    autoFocus: false,
                    delay: 250,
                    response: function (event, ui) {
                        if (ui.content.length == 1 && ui.content[0].id != 0) {
                            ui.item = ui.content[0];
                            $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                            $(this).removeClass('ui-autocomplete-loading');
                        }
                    },
                    select: function (event, ui) {
                        event.preventDefault();
                        if (ui.item.id !== 0) {
                            var parent = $(this).parent().parent();
                            parent.find(".combo_product_id").val(ui.item.id);
                            parent.find(".combo_name").val(ui.item.name);
                            parent.find(".combo_code").val(ui.item.code);
                            parent.find(".combo_price").val(formatDecimal(ui.item.price));
                            parent.find(".combo_qty").val(formatDecimal(1));
                            if (site.settings.qty_operation == 1) {
                                parent.find(".combo_width").val(formatDecimal(1));
                                parent.find(".combo_height").val(formatDecimal(1));
                            }
                            $(this).val(ui.item.label);
                        } else {
                            bootbox.alert('<?= lang('no_match_found') ?>');
                        }
                    }
                });
            });
            
            <?php if( !$Owner && !$Admin && $GP['pos-show_items'] != 1){ ?> 
                $("#print_order").attr("disabled",true);
            <?php } ?>
            
            <?php if( !$Owner && !$Admin && $GP['pos-move_table'] != 1){ ?> 
                $("#move_suspend").attr("disabled",true);
            <?php } ?>
            
            <?php if( !$Owner && !$Admin && $GP['pos-delete_table'] != 1){ ?>   
                $(".delete_suspend").attr("disabled",true);
            <?php } ?>
            
            <?php if( !$Owner && !$Admin && $GP['pos-print_bill'] != 1){ ?> 
                $("#print_bill").attr("disabled",true);
            <?php } ?>
            
            $("#posbiller").change(biller);
            
            <?php if($Settings->project==1){ ?>
                biller();
            <?php } ?>
            
            function biller(){
                var biller = $("#posbiller").val();
                var project = "<?= $Settings->project_id ?>";
                $.ajax({
                    url : "<?= site_url("pos/get_project") ?>",
                    type : "GET",
                    dataType : "JSON",
                    data : { biller : biller, project : project },
                    success : function(data){
                        if(data){
                            $(".no-project").html(data.result);
                            var project_id = $("#project").val();
                            $("#project").change(function(){
                                project_id = $(this).val();
                            });
                            $("#project_id").val(project_id);
                            $('select').select2();
                        }
                    }
                })
            }
            
            $("#sldate").on("change",function(){
                var podate = $(this).val();
                $("#podate").val(podate);
            });
            
            $("#sldate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'cus',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());

            var old_tp_discount;
            $('#tpdiscount').focus(function () {
                old_tp_discount = $(this).val();
            }).change(function () {
                var new_tp_discount = $(this).val() ? $(this).val() : '0';
                if (is_valid_discount(new_tp_discount)) {
                    var pro_total = $('#hpro_total').val()-0;
                    if (new_tp_discount.indexOf("%") !== -1) {
                        var pds = new_tp_discount.split("%");
                        if (!isNaN(pds[0])) {
                            var discount = parseFloat(((pro_total) * parseFloat(pds[0])) / 100);
                        } else {
                            var discount = parseFloat(new_tp_discount);
                        }
                    } else {
                        var discount = parseFloat(new_tp_discount);
                    }

                    var pro_quantity = $('#pquantity').val()-0;
                    var pro_price = $('#pprice').val()-0;
                    var g_pro_total = pro_quantity * pro_price;
                    var n_pro_total = pro_total - discount;
                    var pro_discount = (g_pro_total - n_pro_total) / pro_quantity;

                    $('#pdiscount').val(pro_discount);
                    $('#pdiscount').change();
                    $(this).val('');
                    return;
                } else {
                    $(this).val(old_tp_discount);
                    bootbox.alert(lang.unexpected_value);
                    return;
                }
            });
            
            $('#posmembership_code').on("change blur keyup", function(e) {
               var membership_code = $(this).val();
               $.ajax({
                  url : site.base_url + "pos/get_membership_code",
                  dataType : "JSON",
                  type : "GET",
                  data : { membership_code : membership_code },
                  success : function(mm){
                    var customer_id = parseInt(mm.customer_id)?parseInt(mm.customer_id):0;
                    $("#poscustomer").val(customer_id).select2({
                        minimumInputLength: 1,
                        data: [],
                        initSelection: function (element, callback) {
                            $.ajax({
                                type: "get", async: false,
                                url: site.base_url+"customers/getCustomer/" + $(element).val(),
                                dataType: "json",
                                success: function (data) {
                                    callback(data[0]);
                                }
                            });
                        },
                        ajax: {
                            url: site.base_url + "customers/suggestions",
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
                    if(mm.status=="success"){
                        $("#posmembership_code").css({"color":"#428BCA", "font-weight":"bold"});
                        $("#poscustomer").prop("readonly","readonly");
                    }else if(mm.status=="expired"){
                        $("#posmembership_code").css({"color":"#FABB3D", "font-weight":"bold"});
                        $("#poscustomer").removeAttr("readonly");
                    }else{
                        $("#posmembership_code").css({"color":"#C9302C", "font-weight":"bold"});
                        $("#poscustomer").removeAttr("readonly");
                    }
                  }
               });
            });

            var interval = setInterval(function() {
            var momentNow = moment();
                $('#date-part').html(momentNow.format('YYYY MMMM DD') + ' '
                                    + momentNow.format('dddd')
                                     .substring(0,3).toUpperCase());
                $('#time-part').html(momentNow.format('hh:mm:ss A'));
            }, 100);
        
            $("#spinner-toggle").click(function() {
                 $( ".scroll-spinner" ).slideToggle( "slow", function() {});
            });
            
            $(function(){
                 $('input[type=text]').attr('autocomplete','off');
            });
            
        });             
    </script>
</body>
</html>
