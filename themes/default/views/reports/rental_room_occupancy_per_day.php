<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('payment_ref')) {
    $v .= "&payment_ref=" . $this->input->post('payment_ref');
}
if ($this->input->post('paid_by')) {
    $v .= "&paid_by=" . $this->input->post('paid_by');
}
if ($this->input->post('sale_ref')) {
    $v .= "&sale_ref=" . $this->input->post('sale_ref');
}
if ($this->input->post('rental_ref')) {
    $v .= "&rental_ref=" . $this->input->post('rental_ref');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('project')) {
    $v .= "&project=" . $this->input->post('project');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('floor')) {
    $v .= "&floor=" . $this->input->post('floor');
}
if ($this->input->post('room')) {
    $v .= "&room=" . $this->input->post('room');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {
        var pb = <?= json_encode($pb); ?>;
        var lang = { 'deduct_deposit' : "<?=lang('deduct_deposit')?>" };
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : lang[x]) : lang[x];
        }
        function ref(x) {
            return (x != null) ? x : ' ';
        }
        oTable = $('#RentalPayData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getRentalPayments/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, {"mRender": ref}, {"mRender": ref}, {"sClass": "center"},{"mRender":fsd},{"mRender":fsd},null,null, null, {"mRender": currencyFormat}, {"mRender": row_status}, {"bVisible": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[12];
				if(aData[14] == "RentalDeposit"){
                    nRow.className = "rental_deposit_link";
                }else if (aData[13] > 0) {
					if(aData[11]=='returned'){
						nRow.className = "payment_link warning";
					}else{
						nRow.className = "payment_link";
					}
                }
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                for (var i = 0; i < aaData.length; i++) {
					if (aaData[aiDisplay[i]][11] == 'sent' || aaData[aiDisplay[i]][11] == 'expense' || aaData[aiDisplay[i]][11] == 'pawn_sent'){
						total -= Math.abs(parseFloat(aaData[aiDisplay[i]][10]));
					}else if (aaData[aiDisplay[i]][11] == 'returned' && aaData[aiDisplay[i]][13] > 0){
						total -= Math.abs(parseFloat(aaData[aiDisplay[i]][10]));
					}else{
						total += parseFloat(aaData[aiDisplay[i]][10]);
					}    
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[10].innerHTML = currencyFormat(parseFloat(total));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('payment_ref');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('sale_ref');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('rental_ref');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('room');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('from_date');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('to_date');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('biller')) { ?>
        $('#rbiller').select2({ allowClear: true });
        <?php } ?>
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('rental_room_occupancy_per_day'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            } ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:;" onclick="window.print();" id ="print" class="tip" title="<?= lang('print') ?>"><i class="icon fa fa-file-fa fa-print"></i></a>
                </li>
                
            </ul>
        </div>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("reports/rental_payments"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_ref", "payment_ref"); ?>
                                <?php echo form_input('payment_ref', (isset($_POST['payment_ref']) ? $_POST['payment_ref'] : ""), 'class="form-control tip" id="payment_ref"'); ?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("sale_ref", "sale_ref"); ?>
                                <?php echo form_input('sale_ref', (isset($_POST['sale_ref']) ? $_POST['sale_ref'] : ""), 'class="form-control tip" id="sale_ref"'); ?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("rental_ref", "rental_ref"); ?>
                                <?php echo form_input('rental_ref', (isset($_POST['rental_ref']) ? $_POST['rental_ref'] : ""), 'class="form-control tip" id="rental_ref"'); ?>

                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="rbiller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[''] = '';
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="rbiller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						<?php if($Settings->project == 0){ ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project">
										<?php
										$pj[''] = '';
										if (isset($projects) && $projects != false) {
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						<?php } ?>

                        <div class="col-sm-4">
                            <div class="form-group">
                            <?=lang("paid_by", "paid_by");?>
                                <select name="paid_by" id="paid_by" class="form-control paid_by">
                                    <?= $this->cus->cash_opts_report($this->input->post('paid_by') ? $this->input->post('paid_by') : 0, false, true); ?>
                                </select>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
							<div class="form-group">
								<?= lang("floor", "floor"); ?>
								<?php
                                $fl[""] = '';
								if($floors){
									foreach ($floors as $floor) {
										$fl[$floor->id] = $floor->floor;
									}
								}
								echo form_dropdown('floor', $fl, (isset($_POST['floor']) ? $_POST['floor'] : isset($Settings->floor_id)? $Settings->floor_id: ''), 'id="floor" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("floor") . '" ');
								?>
							</div>
						</div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("room", "room"); ?>
                                <div class="no-room">
                                    <select name="room" class="form-control"></select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <div class="input-group input-append">
                                    <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <div class="input-group input-append">
                                        <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
                <table style="margin-top: 5px; width:100%;">
                    <th>
                        <tr> 
                                <td class="text_left" style="width: 10%">
                                    <div>
                                        <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                    </div>
                                </td>
                                <td></td>
                                <td class="text_center" style="width:100%">
                                    <div>
                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
                                    </div>
                                <br>

                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('rental_room_occupancy_per_day_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('rental_room_occupancy_per_day')?>
                                </div><br>
                            </td> 
                        </tr>
                </table>
                <style>
                    .rental_date{
                        background-color:#000 !important;
                        color:#fff !important;
                    }
                    .rental_green{
                        background-color:#059941 !important;
                        color:#fff !important;
                    }
                    .rental_orange{
                        background-color:#CF5818 !important;
                        color:#fff !important;
                    }
                    .rental_blue{
                        background-color:#134B8A !important;
                        color:#fff !important;
                    }
                    .rental_yellow{
                        background-color:#C7AA06 !important;
                        color:#fff !important;
                    }
                </style>
                <div class="table-responsive">
                    <table  class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                            <tr>
                                <th class="rental_date"><?= lang('date')?></th>
                                <th class="rental_green"><?= lang('standard_room')?></th>
                                <th class="rental_orange"><?= lang('superoir_king')?></th>
                                <th class="rental_blue"><?= lang('superoir_twin')?></th>
                                <th class="rental_yellow"><?= lang('deluxe_room')?></th>
                                <th class="rental_date"><?= lang('Total')?></th>
                                <th class="rental_date"><?= lang('Unsold_room')?></th>
                                <th class="rental_date"><?= lang('Occupancy%')?></th>
                            </tr>
                            <tr style="text-align:center">
                                <td>13/05/2023</td>
                                <td>10</td>
                                <td>12</td>
                                <td>4</td>
                                <td>16</td>
                                <td>42</td>
                                <td>11</td>
                                <td style="background-color: #F10909">20%</td>
                            </tr>
                            <tr style="text-align:center">
                                <td>14/05/2023</td>
                                <td>10</td>
                                <td>7</td>
                                <td>5</td>
                                <td>9</td>
                                <td>31</td>
                                <td>6</td>
                                <td style="background-color: #F15A09">40%</td>
                            </tr>
                            <tr style="text-align:center">
                                <td>15/05/2023</td>
                                <td>3</td>
                                <td>9</td>
                                <td>5</td>
                                <td>16</td>
                                <td>33</td>
                                <td>8</td>
                                <td style="background-color: #F1B209">60%</td>
                            </tr>
                            <tr style="text-align:center">
                                <td>16/05/2023</td>
                                <td>6</td>
                                <td>9</td>
                                <td>7</td>
                                <td>8</td>
                                <td>30</td>
                                <td>10</td>
                                <td style="background-color: #A8F109">80%</td>
                            </tr>
                            <tr style="text-align:center">
                                <td>17/05/2023</td>
                                <td>10</td>
                                <td>12</td>
                                <td>5</td>
                                <td>17</td>
                                <td>27</td>
                                <td>6</td>
                                <td style="background-color: #15E615">100%</td>
                            </tr>
                        </thead>
                    </table>
                </div>

                <style>
                    .bg_red{
	                    border: 2px solid #CCD1D1 !important;
	                    margin: 3px !important;
                        padding: 20px !important;
	                    height: 220px !important;
	                    width: 160px !important;
	                    float: left !important;
	                    text-align: center !important;
                        border-radius: 9px !important;
                        background-color: #F7CFD3 !important;
                    }
                    .select { 
                        outline: none !important;
                        margin-top: -42px !important;
                        margin-left: 2px !important;
                        padding: 0px 4px 4px 0px !important;
                        border: none !important;
                        width: 102px !important;
                    }
                    .bg_green{
	                    border: 2px solid #CCD1D1 !important;
	                    margin: 3px !important;
	                    height: 220px !important;
	                    width: 160px !important;
	                    padding: 20px !important;
	                    float: left !important;
	                    text-align: center !important;
                        border-radius: 9px !important;
                        background-color: #CAEEC3 !important;
                    }
                    .bg_orange{
	                    border: 2px solid #CCD1D1 !important;
	                    margin: 3px !important;
	                    height: 220px !important;
	                    width: 160px !important;
	                    padding: 20px !important;
	                    float: left !important;
	                    text-align: center !important;
                        border-radius: 9px !important;
                        background-color: #EEE0C3 !important;
                    }
                    .bg_blue{
	                    border: 2px solid #CCD1D1 !important;
	                    margin: 3px !important;
	                    height: 220px !important;
	                    width: 160px !important;
	                    padding: 20px !important;
	                    float: left !important;
	                    text-align: center !important;
                        border-radius: 9px !important;
                        background-color: #C3E7EE !important;
                    }
                    .bg_white{
	                    border: 2px solid #CCD1D1 !important;
	                    margin: 3px !important;
	                    height: 220px !important;
	                    width: 160px !important;
	                    padding: 20px !important;
	                    float: left !important;
	                    text-align: center !important;
                        border-radius: 9px !important;
                        background-color: #fff !important;
                    }
                    .num_room_red{
                        font-size: 40px !important;
                        font-weight: bold !important;
                        color: #E74C3C !important;
                        padding: 13px 0px 40px 0px !important;
                    }
                    .num_room_blue{
                        font-size: 40px !important;
                        font-weight: bold !important;
                        color: #08689C !important;
                        padding: 13px 0px 40px 0px !important;
                    }
                    .num_room_green{
                        font-size: 40px !important;
                        font-weight: bold !important;
                        color: #04731A !important;
                        padding: 13px 0px 40px 0px !important;
                    }
                    .custom-checkbox {
                        margin-left: 115px !important;
                        margin-top: -25px !important;
                    }
                    .ellipsis{
                        margin-left: -127px !important;
                        margin-top: -17px !important;
                    }
                    .ellipsis-btn {
                        display: inline-block !important;
                        width: 30px !important;
                        height: 30px !important;
                        background-color: transparent !important;
                        border: none !important;
                        font-size: 20px !important;
                        color: #000 !important;
                        cursor: pointer !important;
                    }
                    .box-footer{
                        margin-left: -22px !important;
                        margin-top: 11px !important;
                        width: 160px !important;
                        height: 40px !important;
                        background-color: #CCD1D1 !important;
                        text-align:center !important;
                        border-radius:0 0 9px 9px !important;
                        font-size: 13px !important;
                        padding: 11px 0px 0px 0px !important;
                    }
                    .date{
                        margin-top: -23px !important;
                        color: #000 !important;
                        margin-left: -5px !important;
                        font-weight: bold !important;
                    }
                </style>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">

                    <div class="bg_red">
                        <div class="ellipsis">
                            <button class="ellipsis-btn">&#8942;</button>
                        </div>
                        <div class="custom-checkbox">
                            <input type="checkbox" id="option1" name="option1" value="Option 1">
                            <label for="option1"></label>
                        </div>
                        <div class="select"><b><?=lang("Clean")?> </div>
                        <div class="num_room_red">
                            <h4 style="font-size: 40px; padding-bottom: 10px">168</h4>
                            <h6 style="padding-left: 8px"><b>Standand Room</h6>
                        </div>
                        <div class="date"><i class="fa fa-calendar"></i>16-May-2023</div>
                        <div><b><i class="fa fa-sign-out"></i>17-May-2023</div>
                        <div class="box-footer room_rental">
                             <span class="fa fa-bed icon_bed"></span>
                             <span class="fa fa-bed icon_bed"></span>
                        </div>
                    </div>
                    <div class="bg_blue">
                        <div class="ellipsis">
                            <button class="ellipsis-btn">&#8942;</button>
                        </div>
                        <div class="custom-checkbox">
                            <input type="checkbox" id="option1" name="option1" value="Option 1">
                            <label for="option1"></label>
                        </div>
                        <div class="select"><?=lang("Clean")?> </div>
                        <div class="num_room_blue">
                            <h4 style="font-size: 40px; padding-bottom: 10px">168</h4>
                            <h6 style="padding-left: 8px"><b>Standand Room</h6>
                        </div>
                        <div class="date"><i class="fa fa-calendar"></i>16-May-2023</div>
                        <div><i class="fa fa-sign-out"></i>17-May-2023</div>
                        <div class="box-footer room_rental">
                             <span class="fa fa-bed icon_bed"></span>
                             <span class="fa fa-bed icon_bed"></span>
                        </div>
                    </div>
                    <div class="bg_green">
                        <div class="ellipsis">
                            <button class="ellipsis-btn">&#8942;</button>
                        </div>
                        <div class="custom-checkbox">
                            <input type="checkbox" id="option1" name="option1" value="Option 1">
                            <label for="option1"></label>
                        </div>
                        <div class="select"><?=lang("Clean")?> </div>
                        <div class="num_room_green">
                            <h4 style="font-size: 40px; padding-bottom: 10px">168</h4>
                            <h6 style="padding-left: 8px"><b>Standand Room</h6>
                        </div>
                        <div class="date"><i class="fa fa-calendar"></i>16-May-2023</div>
                        <div><i class="fa fa-sign-out"></i>17-May-2023</div>
                        <div class="box-footer room_rental">
                             <span class="fa fa-bed icon_bed"></span>
                             <span class="fa fa-bed icon_bed"></span>
                        </div>
                    </div>
                    <div class="bg_red">
                        <div class="ellipsis">
                            <button class="ellipsis-btn">&#8942;</button>
                        </div>
                        <div class="custom-checkbox">
                            <input type="checkbox" id="option1" name="option1" value="Option 1">
                            <label for="option1"></label>
                        </div>
                        <div class="select"><?=lang("Clean")?> </div>
                        <div class="num_room_red">
                            <h4 style="font-size: 40px; padding-bottom: 10px">168</h4>
                            <h6 style="padding-left: 8px"><b>Standand Room</h6>
                        </div>
                        <div class="date"><i class="fa fa-calendar"></i>16-May-2023</div>
                        <div><i class="fa fa-sign-out"></i>17-May-2023</div>
                        <div class="box-footer room_rental">
                             <span class="fa fa-bed icon_bed"></span>
                             <span class="fa fa-bed icon_bed"></span>
                        </div>
                    </div>
                    <div class="bg_blue">
                        <div class="ellipsis">
                            <button class="ellipsis-btn">&#8942;</button>
                        </div>
                        <div class="custom-checkbox">
                            <input type="checkbox" id="option1" name="option1" value="Option 1">
                            <label for="option1"></label>
                        </div>
                        <div class="select"><?=lang("Clean")?> </div>
                        <div class="num_room_blue">
                            <h4 style="font-size: 40px; padding-bottom: 10px">168</h4>
                            <h6 style="padding-left: 8px"><b>Standand Room</h6>
                        </div>
                        <div class="date"><i class="fa fa-calendar"></i>16-May-2023</div>
                        <div><i class="fa fa-sign-out"></i>17-May-2023</div>
                        <div class="box-footer room_rental">
                             <span class="fa fa-bed icon_bed"></span>
                             <span class="fa fa-bed icon_bed"></span>
                        </div>
                    </div>
                    <div class="bg_green">
                        <div class="ellipsis">
                            <button class="ellipsis-btn">&#8942;</button>
                        </div>
                        <div class="custom-checkbox">
                            <input type="checkbox" id="option1" name="option1" value="Option 1">
                            <label for="option1"></label>
                        </div>
                        <div class="select"><?=lang("Clean")?> </div>
                        <div class="num_room_green">
                            <h4 style="font-size: 40px; padding-bottom: 10px">168</h4>
                            <h6 style="padding-left: 8px"><b>Standand Room</h6>
                        </div>
                        <div class="date"><i class="fa fa-calendar"></i>16-May-2023</div>
                        <div><i class="fa fa-sign-out"></i>17-May-2023</div>
                        <div class="box-footer room_rental">
                             <span class="fa fa-bed icon_bed"></span>
                             <span class="fa fa-bed icon_bed"></span>
                        </div>
                    </div>
                    <div class="bg_red">
                        <div class="ellipsis">
                            <button class="ellipsis-btn">&#8942;</button>
                        </div>
                        <div class="custom-checkbox">
                            <input type="checkbox" id="option1" name="option1" value="Option 1">
                            <label for="option1"></label>
                        </div>
                        <div class="select"><?=lang("Clean")?> </div>
                        <div class="num_room_red">
                            <h4 style="font-size: 40px; padding-bottom: 10px">168</h4>
                            <h6 style="padding-left: 8px"><b>Standand Room</h6>
                        </div>
                        <div class="date"><i class="fa fa-calendar"></i>16-May-2023</div>
                        <div><i class="fa fa-sign-out"></i>17-May-2023</div>
                        <div class="box-footer room_rental">
                             <span class="fa fa-bed icon_bed"></span>
                             <span class="fa fa-bed icon_bed"></span>
                        </div>
                    </div>
                    <div class="bg_blue">
                        <div class="ellipsis">
                            <button class="ellipsis-btn">&#8942;</button>
                        </div>
                        <div class="custom-checkbox">
                            <input type="checkbox" id="option1" name="option1" value="Option 1">
                            <label for="option1"></label>
                        </div>
                        <div class="select"><?=lang("Clean")?> </div>
                        <div class="num_room_blue">
                            <h4 style="font-size: 40px; padding-bottom: 10px">168</h4>
                            <h6 style="padding-left: 8px"><b>Standand Room</h6>
                        </div>
                        <div class="date"><i class="fa fa-calendar"></i>16-May-2023</div>
                        <div><i class="fa fa-sign-out"></i>17-May-2023</div>
                        <div class="box-footer room_rental">
                             <span class="fa fa-bed icon_bed"></span>
                             <span class="fa fa-bed icon_bed"></span>
                        </div>
                    </div>
                </div>
            </div>        
        </div>
                <style>
                    .container-box{
                        padding: 0px 0px 10px 0px;
                    }
                    .item{
                        padding: 10px 0px 0px 10px;
                    }
                    .num {
                        padding: 15px 15px 0px 15px;
                    }
                    .ico{
                        font-size: 20px;
                        float: right;
                        padding: 10px 10px 0px 0px;
                        color: #0D6397;
                        cursor: pointer;
                    }
                    .bg-color{
                        margin: 15px 0px 0px 0px;
                        height: 100px;
                        width: 100%;
                        background-color: #E5E8E8;
                        border-radius: 9px;

                    }
                    .color_blue{
                        text-align: center;
                        background-color: #0D6397;
                        color: #fff;
                        border-radius: 5px;
                    }
                    .color_red{
                        text-align: center;
                        background-color: #B80808;
                        color: #fff;
                        border-radius: 5px;
                    }
                </style>
                <div class="box-content">
                    <div class="row">
                        <div class="container-box col-lg-12">
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="bg-color">
                                    <div class="ico"><i class="fa fa-user"></i></div>
                                    <p class="item">អតិថិជន</p>
                                    <div class="num">
                                        <div class="color_blue">123456789</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="bg-color">
                                    <div class="ico"><i class="fa fa-download"></i></div>
                                    <p class="item">ចំណូល</p>
                                    <div class="num">
                                        <div class="color_blue">123456789</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="bg-color">
                                    <div class="ico"><i class="fa fa-upload"></i></div>
                                    <p class="item">ចំណាយ</p>
                                    <div class="num">
                                        <div class="color_blue">123456789</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="bg-color">
                                    <div class="ico"><i class="fa fa-signal"></i></div>
                                    <p class="item">ចំណេញ​​ & ខាត</p>
                                    <div class="num">
                                        <div class="color_blue">123456789</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="bg-color">
                                    <div class="ico"><i class="fa fa-usd"></i></div>
                                    <p class="item">បញ្ចាំសរុប</p>
                                    <div class="num">
                                        <div class="color_red">123456789</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="bg-color">
                                    <div class="ico"><i class="fa fa-share-square-o"></i></div>
                                    <p class="item">ការទម្លាក់ប្រាក់</p>
                                    <div class="num">
                                        <div class="color_red">123456789</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="bg-color">
                                    <div class="ico"><i class="fa fa-check-circle"></i></div>
                                    <p class="item">ការប្រមូលប្រាក់</p>
                                    <div class="num">
                                        <div class="color_red">123456789</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="bg-color">
                                    <div class="ico"><i class="fa fa-money"></i></div>
                                    <p class="item">សមតុល្យភាពប្រាក់</p>
                                    <div class="num">
                                        <div class="color_red">123456789</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div style="margin: 20px 0px 0px -3px; width: 575px">
                                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                <canvas id="hightChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div style="margin: 20px 0px 0px -3px; width: 575px">
                                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                <canvas id="myChart1"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <script>
                    var ctx = document.getElementById('hightChart').getContext('2d');
                    var hightChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                        labels: ['មករា', 'កុម្ភះ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិការ', 'ធ្នូ'],
                        datasets: [{
                            label: '# Income2023',
                            data: [12, 19, 3, 20, 7, 10, 5, 9, 6, 15, 17, 11],
                            backgroundColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(10, 207, 31, 1)',
                            'rgba(237, 10, 244, 1)',
                            'rgba(10, 244, 194, 1)',
                            'rgba(24, 10, 248, 1)',
                            'rgba(248, 10, 10, 1)',
                            'rgba(248, 237, 10, 1)',
                            'rgba(156, 0, 250, 1)',
                            'rgba(250, 0, 118, 1)',
                            'rgba(76, 78, 77, 1)'
                            ],
                            borderWidth: 1
                        }]
                        },
                        options: {
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                </script> 
                <script>
                    var ctx = document.getElementById('myChart1').getContext('2d');
                    var myChart1 = new Chart(ctx, {
                        type: 'bar',
                        data: {
                        labels: ['មករា', 'កុម្ភះ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិការ', 'ធ្នូ'],
                        datasets: [{
                            label: '# Income2023',
                            data: [12, 19, 3, 20, 7, 10, 5, 9, 6, 15, 17, 11],
                            backgroundColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(10, 207, 31, 1)',
                            'rgba(237, 10, 244, 1)',
                            'rgba(10, 244, 194, 1)',
                            'rgba(24, 10, 248, 1)',
                            'rgba(248, 10, 10, 1)',
                            'rgba(248, 237, 10, 1)',
                            'rgba(156, 0, 250, 1)',
                            'rgba(250, 0, 118, 1)',
                            'rgba(76, 78, 77, 1)'
                            ],
                            borderWidth: 1
                        }]
                        },
                        options: {
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                </script>  -->

                <style>
                    .color-orange{
                        margin: 15px 0px 0px 0px;
                        height: 150px;
                        width: 100%;
                        background-color: #3D70F8;
                        border-radius: 4px;
                    }
                    .color-total-user{
                        text-align: center;
                        background-color: #3D70F8;
                        color: #fff;
                        border-radius: 0px 4px 0px 4px;
                        width: 80px;
                        height: 70px;
                        padding: 8px 0px 0px 0px;
                        font-size: 40px;
                        float: right;
                    }
                    .footer-series{
                        margin: -4px 0px 0px 0px;
                        width: 90%;
                        height: 40px;
                        background-color: #0046FF;
                        text-align:center;
                        border-radius:0 0 4px 4px;
                        font-size: 13px;
                        padding: 8px 0px 0px 0px;
                        cursor: pointer;
                    }
                    .number{
                        color: #fff;
                        float: left;
                        padding: 8px 0px 0px 5px;
                        font-size: 30px;
                    }
                    .subtitle{
                        color: #fff;
                        float: left;
                        padding: -10px 0px 30px 5px;
                        font-size: 19px;
                    }
                    .view{
                        border: 1px solid #065FA0;
                        border-radius: 4px;
                        font-size: 10px;
                        float: right;
                        padding: 2px 2px 2px 2px;
                        color: #000;
                        cursor: pointer;
                    }
                    .view-box{
                        padding: 7px 7px 0px 0px;
                    }
                    .view:hover{
                        background-color: #fff;
                        color: #000;
                    }
                    .icon {
                        padding: 18px 0px 20px 20px;
                    }
                    
                    
                </style>

                <div class="box-content">
                    <div class="row">
                        <div class="container-box col-lg-12">
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="color-orange">
                                    <!-- <div class="view-box">
                                        <div class="view">
                                            <a style="text-decoration: none">View More</a>
                                        </div>
                                    </div> -->
                                        <div class="color-total-user">
                                            <i class="fa fa-usd"></i>
                                        </div>
                                        <h1 class="number">$0.00</h1>
                                        <div class="subtitle">
                                            <h3>Total Outstanding Open Loans</h3>
                                        </div>
                                        <div class="footer-series room_rental">
                                            <a style="color: #fff">More Details</a>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="color-orange">
                                    <!-- <div class="view-box">
                                        <div class="view">
                                            <a style="text-decoration: none">View More</a>
                                        </div>
                                    </div> -->
                                        <div class="color-total-user">
                                            <i class="fa fa-usd"></i>
                                        </div>
                                        <h1 class="number">$0.00</h1>
                                    
                                        <h3 class="subtitle">Total Outstanding Open Loans</h3>
                                        <div class="footer-series room_rental">
                                            <a style="color: #fff">More Details</a>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="color-orange">
                                    <!-- <div class="view-box">
                                        <div class="view">
                                            <a style="text-decoration: none">View More</a>
                                        </div>
                                    </div> -->
                                        <div class="color-total-user">
                                            <i class="fa fa-usd"></i>
                                        </div>
                                        <h1 class="number">$0.00</h1>
                                    
                                        <h3 class="subtitle">Total Outstanding Open Loans</h3>
                                        <div class="footer-series room_rental">
                                            <a style="color: #fff">More Details</a>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="color-orange">
                                    <!-- <div class="view-box">
                                        <div class="view">
                                            <a style="text-decoration: none">View More</a>
                                        </div>
                                    </div> -->
                                        <div class="color-total-user">
                                            <i class="fa fa-usd"></i>
                                        </div>
                                        <h1 class="number">$0.00</h1>
                                    
                                        <h3 class="subtitle">Total Outstanding Open Loans</h3>
                                        <div class="footer-series room_rental">
                                            <a style="color: #fff">More Details</a>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="table-responsive">
                    <table id="RentalPayData"
                           class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("payment_ref"); ?></th>
                            <th><?= lang("sale_ref"); ?></th>
                            <th><?= lang("rental_ref"); ?></th>
							<th><?= lang("room"); ?></th>
							<th><?= lang("from_date"); ?></th>
                            <th><?= lang("to_date"); ?></th>
                            <th><?= lang("customer"); ?></th>
							<th><?= lang("created_by"); ?></th>
							<th><?= lang("paid_by"); ?></th>
                            <th><?= lang("amount"); ?></th>
                            <th><?= lang("type"); ?></th>
                            <th><?= lang("id"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                        </tr>
                        </tfoot>
                    </table>
                    <table class="print_only" id="table_sinature">
                        <tr>
                            <td class="text-center bg_header" style="width:25%"><?= lang("prepared_by") ?></td>
                            <td class="text-center bg_header" style="width:25%"><?= lang("checked_by") ?></td>
                            <td class="text-center bg_header" style="width:25%"><?= lang("verified_by") ?></td>
                            <td class="text-center bg_header" style="width:25%"><?= lang("approved_by") ?></td>
                        </tr>
                        <tr>
                            <?php
                                $user = $this->site->getUserByID($this->session->userdata("user_id"));
                            ?>
                            <td style="height:120px; padding-left:5px; vertical-align: bottom !important">
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center" style="width:25%"><?= lang('name_date')?> / <?= $this->cus->hrsd(date("Y-m-d")) ?></td>
                            <td class="text-left" style="width:25%"><?= lang('name_date')?></td>
                            <td class="text-left" style="width:25%"><?= lang('name_date')?></td>
                            <td class="text-left" style="width:25%"><?= lang('name_date')?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    @media print{    
        .dtFilter{
            display: table-footer-group !important;
        }
        #form{
            display:none !important;
        }
        .print_only{
            display:table !important;
        }
        table .td_biller{ 
            display:none; !important
        } 
        .exportExcel tr th{
            background-color : #428BCA !important;
            color : white !important;
        }
        @page{
            margin: 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
        }
        
    }
    .print_only{
        display:none;
    }
    #table_sinature{
        width:100%;
        margin-top:15px
    }
    #table_sinature td{
        border:1px solid black;
        padding: 7px;
    }
    .bg_header{
        background-color: rgb(0 0 0 / 8%);
    }

</style>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var customer_id = "<?= isset($_POST['customer'])?$_POST['customer']:0 ?>";
		if (customer_id > 0) {
		  $('#customer_id').val(customer_id).select2({
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
			  deietMillis: 15,
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
		}else{
		  $('#customer_id').select2({
			minimumInputLength: 1,
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
		}
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getRentalPayments/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getRentalPayments/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
		$("#rbiller").change(biller); biller();
		function biller(){
			var biller = $("#rbiller").val();
			var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
			$.ajax({
				url : "<?= site_url("reports/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
        $("#floor").on("change", floor); floor();
		function floor(){
			var floor_id = $("#floor").val();
			var room_id = "<?= (isset($_POST['room']) ? trim($_POST['room']): '') ?>";
			$.ajax({
                url: "<?=site_url('reports/get_room_floor')?>",
                type : "GET",
                dataType: "JSON",
                data: { floor_id: floor_id, room_id : room_id },
                success: function (data) {
                    if(data){
						$(".no-room").html(data.result);
						$("#room").select2();
					}
                }
            });
		}
    });
</script>

<style>
    @media print{
        .no-print{
            display:none !important;
        }
        .tr_print{
            display:table-row !important;
        }
        @page{
            margin: 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
        }
    }
    .tr_print{
        display:none;
    }
    #tbody .td_print{
        border:none !important;
        border-left:1px solid black !important;
        border-right:1px solid black !important;
        border-bottom:1px solid black !important;
    }
    .hr_title{
        border:3px double #428BCD !important;
        margin-bottom:<?= $margin ?>px !important;
        margin-top:<?= $margin ?>px !important;
    }
    .table_item th{
        border:1px solid black !important;
        background-color : #428BCD !important;
        text-align:center !important;
        line-height:30px !important;
    }
    .table_item td{
        border:1px solid black;
        line-height:<?=$td_line_height?>px !important;
    }
    .footer_des[rowspan] {
      vertical-align: top !important;
      text-align: left !important;
      border:0px !important;
    }
    .text_center{
        text-align:center !important;
    }
    .text_left{
        text-align:left !important;
        padding-left:3px !important;
    }
    .text_right{
        text-align:right !important;
        padding-right:3px !important;
    }
    fieldset{
        -moz-border-radius: 9px !important;
        -webkit-border-radius: 15px !important;
        border-radius:0px !important;
        border:2px solid #070707 !important;
        min-height:<?= ($min_height+50) ?>px !important;
        margin-top : <?= $margin+2 ?>px !important;
        margin-bottom : <?= $margin+5 ?>px !important;
        padding-left : <?= $margin+10 ?>px !important;
        padding-right : <?= $margin+10 ?>px !important;
        padding-bottom : <?= $margin+10 ?>px !important;
    }
    legend{
        width: initial !important;
        margin-bottom: initial !important;
        border: initial !important;
    }
    .modal table{
        width:100% !important;
        font-size:<?= $font_size ?>px !important;
        border-collapse: collapse !important;
    }
</style>
