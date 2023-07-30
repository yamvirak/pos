<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('payment_ref')) {
    $v .= "&payment_ref=" . $this->input->post('payment_ref');
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
            'sAjaxSource': '<?= site_url('reports/getRentalDeposits/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, {"mRender": ref}, {"sClass": "center"}, null, null,{"mRender":fsd},null, {"mRender": paid_by}, {"mRender": currencyFormat}, {"mRender": row_status}, {"bVisible": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[11];
				if(aData[13] == "RentalDeposit" || aData[13] == "ReturnRentalDeposit"){
                    nRow.className = "rental_deposit_link";
                }else if (aData[12] > 0) {
					if(aData[10]=='returned'){
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
						total -= Math.abs(parseFloat(aaData[aiDisplay[i]][9]));
					}else if (aaData[aiDisplay[i]][11] == 'returned' && aaData[aiDisplay[i]][12] > 0){
						total -= Math.abs(parseFloat(aaData[aiDisplay[i]][9]));
					}else{
						total += parseFloat(aaData[aiDisplay[i]][9]);
					}    
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[9].innerHTML = currencyFormat(parseFloat(total));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('payment_ref');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('rental_ref');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('room');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('checked_in_date');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
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
        <!-- <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('rental_deposits_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            } ?>
        </h2> -->
        <div class="sub_menu"></div>
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
                    <h2 class="blue"><i class="icon fa fa-money tip"></i><?= lang('rental_deposits_report'); ?></h2>
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
                </li> -->
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <!-- <p class="introtext"><?= lang('customize_report'); ?></p> -->

                <div id="form">

                    <?php echo form_open("reports/rental_deposits"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_ref", "payment_ref"); ?>
                                <?php echo form_input('payment_ref', (isset($_POST['payment_ref']) ? $_POST['payment_ref'] : ""), 'class="form-control tip" id="payment_ref"'); ?>

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
						<?php if($Settings->project == 1){ ?>
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
                                        <?= lang('rental_deposits_report_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('rental_deposits_report')?>
                                </div><br>
                            </td> 
                        </tr>
                </table>
                <div class="table-responsive">
                    <table id="RentalPayData"
                           class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("payment_ref"); ?></th>
                            <th><?= lang("rental_ref"); ?></th>
							<th><?= lang("room"); ?></th>
							<th><?= lang("customer"); ?></th>
                            <th><?= lang("phone"); ?></th>
                            <th><?= lang("checked_in_date"); ?></th>
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
                </div>
            </div>
        </div>
    </div>
</div>

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
            window.location.href = "<?=site_url('reports/getRentalDeposits/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getRentalDeposits/0/xls/?v=1'.$v)?>";
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
    });
</script>
