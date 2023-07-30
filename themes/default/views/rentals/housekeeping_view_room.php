<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>

<div class="box">
    <div class="box-header box-header">
                  
        <div class="box-icon">
            <ul class="btn-tasks">
                <h2 class="blue"><i class="icon fa fa-television"></i><?= lang('view_room'); ?></h2>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
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
                            <button class="btn tip btn-primary btn_radius" data-original-title="<?=lang('room_status');?>"><label style="color:#fff;"><i class="icon fa fa-television" style="padding-right:5px;"></i><?=lang("room_status") ?> </label> </button>

                                <span style="font-size: 40px;vertical-align: middle;"><i class="fa-fw fa fa-bed"></i></span>

                            <button class="btn tip btn-primary btn_radius" data-original-title="<?=lang('room_free');?>"><label style="color:#fff;"><i class="fa fa-sign-in" style="padding-right:5px;"></i><?=lang("room_free") ?> </label> </button>
                    
                            <button class="btn tip btn-success btn_radius" data-original-title="<?=lang('room_occupied');?>"><label style="color:#fff;"><i class="fa fa-sign-in" style="padding-right:5px;"></i><?=lang("room_occupied") ?> </label></button> 
                            <button class="btn tip btn-warning btn_radius" data-original-title="<?=lang('room_reservation');?>"><label style="color:#fff;"><i class="fa fa-sign-in" style="padding-right:5px;"></i><?=lang("room_reservation") ?> </label> </button>
                            <button class="btn tip btn-danger btn_radius" data-original-title="<?=lang('maintenances');?>"><label style="color:#fff;"><i class="fa fa-sign-in" style="padding-right:5px;"></i><?=lang("maintenances") ?> </label></button> 
                            
                        </div>
                    </div>
                    
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="table-board">
                        <div id="ajax_rooms"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
    <div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
    <div id="modal-loading" style="display: none;">
        <div class="blackbg"></div>
        <div class="loader"></div>
    </div>
    <div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>

    <script type="text/javascript">
        $(function(){
            $("#rtwarehouse, #rtfloor").on("change",ajax_rooms); ajax_rooms();
            function ajax_rooms(){
                var warehouse = $("#rtwarehouse option:selected").val();
                var floor = $("#rtfloor option:selected").val();
                $.ajax({
                    url : "<?= site_url("rentals_housekeeping/ajax_rooms") ?>",
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
