<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
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
if ($this->input->post('status')) {
    $v .= "&status=" . $this->input->post('status');
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
        oTable = $('#RentalData').dataTable({
			"aaSorting": [[10, "asc"],[0, "desc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= site_url('reports/getRentalsReport/?v=1'.$v); ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			"aoColumns": [
			{"mRender" : fld, "sClass":"center"}, 
			null,
			null, 
			null,
			{"sClass" : "text-center"},
			{"mRender" : fsd, "sClass":"center"}, 
			{"mRender" : fsd, "sClass":"center"},
			{"mRender": currencyFormat},
			{"mRender": currencyFormat},
			{"sClass":"center"},
			{"mRender" : fsd, "sClass":"center"},
			{"mRender": row_status}, 
			{"mRender" : attachment}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var total =0, deposit = 0;
				for (var i = 0; i < aaData.length; i++) {
					total += parseFloat(aaData[aiDisplay[i]][7]);
					deposit += parseFloat(aaData[aiDisplay[i]][8]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[7].innerHTML = currencyFormat(parseFloat(total));
				nCells[8].innerHTML = currencyFormat(parseFloat(deposit));
            }
		}).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('voucher_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('guest name');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('room_number');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('arrival');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('departure');?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('period');?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('checked_in_date');?>]", filter_type: "text", data: []},
			{column_number: 11, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
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
        <!-- <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('rentals_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
        </h2> -->
        <div class="sub_menu">&nbsp&nbsp&nbsp&nbsp&nbsp</div>
        <div class="sub_menu">
            <a href="javascript:;" onclick="window.print();" id ="print" 
                class="tip btn btn-success btn-block box_sub_menu" title="<?= lang('print') ?>">
                <i class="icon fa fa-file-fa fa-print">&nbsp;</i><?=lang('print')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="xls" class="tip btn btn-warning btn-block box_sub_menu" title="<?= lang('download_xls') ?>">
                <i class="icon fa fa-file-excel-o"></i>&nbsp;</i><?=lang('download_xls')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="toggle_down tip btn btn-info btn-block box_sub_menu" title="<?= lang('show_form') ?>">
                <i class="icon fa fa-eye"></i>&nbsp;</i><?=lang('show_form')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="toggle_up tip btn btn-danger btn-block box_sub_menu" title="<?= lang('hide_form') ?>">
                <i class="icon fa fa-eye-slash"></i>&nbsp;</i><?=lang('hide_form')?>
            </a>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-dollar tip"></i><?= lang('rentals_report'); ?></h2>
                </li>
                <!-- <li class="dropdown">
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
                </li> -->
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->
                <div id="form">
                    <?php echo form_open("reports/rentals"); ?>
                    <div class="row">
					
                        <div class="col-sm-4 hidden">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
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
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="rtcustomer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
						
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("floor", "floor"); ?>
								<?php
								$fl[''] = '';
								if($floors){
									foreach ($floors as $floor) {
										$fl[$floor->id] = $floor->floor;
									}
								}
								echo form_dropdown('floor', $fl, (isset($_POST['floor']) ? $_POST['floor'] : ''), 'id="floor" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("floor") . '" ');
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
                                <label class="control-label" for="user"><?= lang("status"); ?></label>
                                <?php
                                $st = array(''=>lang('select').' '.lang('status'), 'reservation'=>lang('reservation') , 'checked_in'=>lang('checked_in'), 'checked_out'=>lang('checked_out'));
                                echo form_dropdown('status', $st, (isset($_POST['status']) ? $_POST['status'] : ""), 'class="form-control" id="status" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("status") . '"');
                                ?>
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

                <table style="width:100%; margin-bottom: 10px">
                    <?php 
                        $biller_id = (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller);
                                $biller_id_all = lang('all_selected');
                                $biller_id_detail = $this->site->getCompanyByID($biller_id);
                                if($biller_id_detail){
                    ?>
                    
                    <tr>
                        <td class="text_left" style="width: 10%">
                            <div>
                                <?= !empty($biller_id_detail->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller_id_detail->logo).'" alt="">' : ''; ?>
                             </div>
                        </td>
                        <td></td>
                        <td class="text_center" style="width:100%">
                            <div>
                                <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller_id_detail->company;?></strong><br>
                                    <strong style="font-size:20px";><?= $biller_id_detail->name;?></strong>
                            </div>
                            <br>

                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('rentals_report_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('rentals_report_en')?>
                                </div><br>
                            
                        </td>
                    </tr>
                    
                    <?= $print_filter ?>
                    <?php } ?>
                </table>

                <div class="table-responsive">
                    <table id="RentalData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                         <tr class="primary">
								<th style="width:150px;"><?= lang("date"); ?></th>
								<th style="width:150px;"><?= lang("voucher_no"); ?></th>
								<th style="width:150px;"><?= lang("guest name"); ?></th>
								<th style="width:150px;"><?= lang("phone"); ?></th>
								<th style="width:150px;"><?= lang("room_number"); ?></th>
								<th style="width:150px;"><?= lang("arrival"); ?></th>
								<th style="width:150px;"><?= lang("departure"); ?></th>
								<th style="width:150px;"><?= lang("total"); ?></th>
								<th style="width:150px;"><?= lang("deposit"); ?></th>
								<th style="width:150px;"><?= lang("period"); ?></th>
								<th style="width:150px;"><?= lang("checked_in_date"); ?></th>
								<th style="width:50px;"><?= lang("status"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
							</tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
							</tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getRentalsReport/pdf/?v=1'.$v)?>";
            return false;
        });
		
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getRentalsReport/0/xls/?v=1'.$v)?>";
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
		
		$("#biller").change(biller); biller();
		function biller(){
			var biller = $("#biller").val();
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
			var room_id = "<?= (isset($_POST['room'])?$_POST['room']:0) ?>";
			$.ajax({
                type: "get",
                url: "<?=site_url('reports/get_room_floor')?>",
                data: { floor_id: floor_id, room_id : room_id },
                dataType: "json",
                success: function (data) {
                    if(data){
						$(".no-room").html(data.result);
						$("#room").select2();
					}
                }
            });
		}
		
		var customer = "<?= isset($_POST['customer'])?$_POST['customer']:0; ?>";
		$('#rtcustomer').val(customer).select2({
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
				url: site.base_url+"customers/suggestions",
				dataType: 'json',
				quietMillis: 15,
				data: function (term, page) {
					return {
						term: term,
						limit: 10
					};
				},
				results: function (data, page) {
					if(data.results != null) {
						return { results: data.results };
					} else {
						return { results: [{id: '', text: 'No Match Found'}]};
					}
				}
			}
		});
		
    });
</script>