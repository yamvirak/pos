<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
	$v = "";
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
	if ($this->input->post('customer')) {
		$v .= "&customer=" . $this->input->post('customer');
	}
	if ($this->input->post('warehouse')) {
		$v .= "&warehouse=" . $this->input->post('warehouse');
	}
	if ($this->input->post('floor')) {
		$v .= "&floor=" . $this->input->post('floor');
	}
	if ($this->input->post('month')) {
		$v .= "&month=" . $this->input->post('month');
	}
	if ($this->input->post('year')) {
		$v .= "&year=" . $this->input->post('year');
	}
?>
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
        <h2 class="blue"><i class="fa-fw fa fa-list"></i><?= lang('daily_rentals_report'); ?> <?php
            if ($this->input->post('month')) {
                echo "Date " . $this->input->post('month') . ", " . $this->input->post('year');
            }
            ?>
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
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo form_open("reports/daily_rentals"); ?>
                    <div class="row">
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
                                <label class="control-label" for="user"><?= lang("warehouse"); ?></label>
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
                                <label class="control-label" for="user"><?= lang("floor"); ?></label>
                                <?php
                                $fl[""] = lang('select').' '.lang('floor');
                                foreach ($floors as $floor) {
                                    $fl[$floor->id] = $floor->floor;
                                }
                                echo form_dropdown('floor', $fl, (isset($_POST['floor']) ? $_POST['floor'] : $Settings->default_floor), 'class="form-control" id="floor" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("floor") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("month", "month"); ?>
								<select name="month" class="form-control">
									<?php 
										for ($m=1; $m<=12; $m++) {
											if(isset($_POST['month']) && $_POST['month'] == $m){
												echo '<option value='.$m.' selected>'.$month = date('F', mktime(0,0,0,$m, 1, date('Y'))).'</option>';
											}else if(!isset($_POST['month']) && $m == date("m")){
												echo '<option value='.$m.' selected>'.$month = date('F', mktime(0,0,0,$m, 1, date('Y'))).'</option>';
											}else{
												echo '<option value='.$m.'>'.$month = date('F', mktime(0,0,0,$m, 1, date('Y'))).'</option>';
											}
										}
									?>
								</select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("year", "year"); ?>
                                <?php echo form_input('year', (isset($_POST['year']) ? $_POST['year'] : date("Y")), 'class="form-control year" id="year"'); ?>
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
                                        <?= lang('daily_rentals_report_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('daily_rentals_report')?>
                                </div><br>
                            </td> 
                        </tr>
                </table>

                <div class="table-responsive">
                    <table style="margin-bottom:3px;" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" style="white-space:nowrap;">
                        <thead>
							<tr class="active">
								<th style="width:80px;"><?= lang("room"); ?></th>
								<?php 
								$post = $this->input->post()?$this->input->post():$this->input->get();
								$year = isset($post['year'])?$post['year']:date("Y");
								$month = isset($post['month'])?$post['month']:date("m");
								$number_days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
								$total_expense = 0;
								$total_expense_day = array();
								if(isset($number_days) && $number_days){
									for($day = 1; $day <= $number_days; $day++){
										echo '<th>'.date('d', strtotime($year."-".$month."-".$day)).'</th>';
									}
								}
								?>
							</tr>
                        </thead>
                        <tbody>
							<?php 
								if($daily_rentals){
									foreach($daily_rentals as $daily_rental){
										$string = '';
										if(isset($number_days) && $number_days){
											for($day = 1; $day <= $number_days; $day++){
												$rental = $this->reports_model->getRentalByDate($daily_rental->id,$day,$month,$year);
                                                $reservation = $this->reports_model->getReservationByDate($daily_rental->id,$day,$month,$year);
                                                $booked = $this->reports_model->getBookedByDate($daily_rental->id,$day,$month,$year);
												if($rental){
													$string .='<td class="bold" style="text-align:center;background:#FF5454;color:#FFF;"> 1 </td>';
												}elseif($reservation){
                                                    $string .='<td class="bold blightOrange" style="text-align:center;color:#FFF;"> 1 </td>';
                                                }elseif($booked){
                                                    $string .='<td class="bold bdarkGreen" style="text-align:center;color:#FFF;"> 1 </td>';
                                                }else{
													$string .='<td class="bold" style="text-align:center;"> 0 </td>';
												}
											}
										}
										echo '<tr>
												<td style="text-align:center;">'.$daily_rental->name.'</td>
												'.$string.'
											 </tr>';
									}
								}
							?>
                        </tbody>
                        
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
            window.location.href = "<?=site_url('reports/daily_rentals_export/pdf/?v=1'.$v)?>";
            return false;
        });
		$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/daily_rentals_export/0/xls/?v=1'.$v)?>";
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
    });
</script>