<script>
    $(document).ready(function () {
        var oTable = $('#RoomData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('rentals_configuration/getRooms/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : 0)); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            },
            null,
			null,
			null,
			{"mRender": currencyFormat},
			{"mRender": row_status}, {"bSortable": false}],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {              
				nRow.id = aData[0];
                nRow.className = "";
                return nRow;
            }
        }).dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('room_type');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('floor');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('price');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
	    echo form_open('rentals_configuration/room_actions', 'id="action-form"');
	}
?>

<div class="box">
    <div class="box-header box-header">
       <div class="action-header"> 
            <a href="<?php echo site_url('rentals_configuration/add_room'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                <i class="fa fa-plus-circle"></i> <?= lang('add') ?>
            </a>
        </div>
        <div class="action-header"> 
            <a href="#" class="bpo" title="<b><?=lang("delete_")?></b>"
                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                    data-html="true" data-placement="left">
                <i class="fa fa-trash-o"></i> <?=lang('delete')?>
            </a>
        </div>
        <div class="action-header"> 
            <a href="#" id="excel" data-action="export_excel">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>               
        <div class="box-icon">
            <ul class="btn-tasks">
                <h2 class="blue"><i class="fa fa-inbox"></i><?= lang('rooms'); ?></h2>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon bgreen">
                            <h4><span class="fa fa-inbox" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_number"><?=$this->cus->formatQuantityQty($totalrooms->totalRoom)?></div>
                                <div class="box_name"><?= lang('total_rooms'); ?></div>
                            </div>
                        </div>
                        <div class="box-footer" style="background:#088c5a">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('rentals_configuration/rooms') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon bBigBlue">
                            <h4><span class="fa fa-bed" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_number"><?=$this->cus->formatQuantityQty($TotalRoomTypes->totalRoomType)?></div>
                                <div class="box_name"><?= lang('total_room_type'); ?></div>
                            </div>
                        </div>
                        <div class="box-footer" style="background:#008cb6">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('rentals_configuration/room_types') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon blightOrange">
                            <h4><span class="fa fa-building" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_number"><?=$this->cus->formatQuantityQty($TotalFloors->TotalFloor)?></div>
                                <div class="box_name"><?= lang('total_floors'); ?></div>
                            </div>
                        </div>
                        <div class="box-footer" style="background:#e49800">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('rentals_configuration/floors') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box-dashboard">
                        <div class="box-content_icon bred">
                            <h4><span class="fa fa-list-alt" id ="icon"></span></h4>
                            <div class="box_right">
                                <div class="box_number"><?=$this->cus->formatQuantityQty($TotalServices->TotalService)?></div>
                                <div class="box_name"><?= lang('total_services'); ?></div>
                            </div>
                        </div>
                        <div class="box-footer" style="background:#ad3d3d">
                            <a class="btn tip" title="" data-original-title="<?=lang("view_detail")?>" data-placement="top" href="<?= site_url('rentals_configuration/services') ?>"><?=lang("view_detail")?> <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>  
                        </div>
                    </div>
                </div>

                <div class="table-responsive table-container">
                    <table id="RoomData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:3%; width: 3%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th width="200"><?= lang("room_type"); ?></th>
							<th width="200"><?= lang("name"); ?></th>
							<th width="200"><?= lang("floor"); ?></th>
                            <th width="200"><?= lang("price"); ?></th>
							<th width="80"><?= lang("status"); ?></th>
                            <th style="width:30px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
							<th></th>
                            <th>[<?= lang("actions"); ?>]</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>

    <style type="text/css">
        .box-dashboard{
            min-height:110px;
            margin-bottom:20px;
            /*border-radius:4px;*/
            color:white; 
            clear:both;
        }
        .box-content_icon{
            padding:10px 10px 80px 10px !important;
            border-radius: 5px 5px 0px 0px;
        }
        .box_right{
            float: right;
        }
        .box_number{
            font-size: 35px;
            margin-top: -20px;
            font-weight: bold;
            text-align: center;
        }
        .box_name{
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }
        .box-footer{
            padding:0px 0;
            margin:0;
            text-align:center;
            border-radius:0 0 4px 4px;
            font-size:13px;
        }
        .box-footer a{
            color:#193653;
            text-decoration:none;
            cursor:pointer;
        }
        .box-dashboard h4{
            font-size:16px;
        }
        .box-dashboard ul li{
            list-style:none;
            line-height:30px;
            font-size:12px;
        }
        .box-dashboard span{
            float:right;
            font-size: 20px;
            font-weight: bold;
        }
        .highcharts-title, .box-dashboard h4 {
            font-family: 'Ubuntu','Moul', sans-serif;
        } 
        .highcharts-subtitle{
            font-family: 'Ubuntu','Nokora', sans-serif; 
        } 
        #icon{
            font-size: 73px;
            margin-top: -7px;
            opacity: 54%;
            position: absolute;
        }
    </style>

