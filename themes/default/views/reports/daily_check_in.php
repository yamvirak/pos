<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
		
		$('#ExpDaily').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            "oTableTools": {
                "sSwfPath": "assets/media/swf/copy_csv_xls_pdf.swf",
                "aButtons": ["csv", {"sExtends": "pdf", "sPdfOrientation": "landscape", "sPdfMessage": ""}, "print"]
            }
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('daily_expenses_report'); ?> <?php
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
				
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo form_open("reports/daily_expenses"); ?>
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
                                <?= lang("supplier", "supplier"); ?>
                                <?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier_id"'); ?> </div>
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
                <div class="table-responsive">
                    <table id="ExpDaily" style="margin-bottom:3px;" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" style="white-space:nowrap;">
						<thead>
							<tr class="active">
								<th><?= lang("category"); ?></th>
								<?php 
									$post = $this->input->post()?$this->input->post():$this->input->get();
									$year = isset($post['year'])?$post['year']:date("Y");
									$month = isset($post['month'])?$post['month']:date("m");
									$number_days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
								if(isset($number_days) && $number_days){
									for($day = 1; $day <= $number_days; $day++){
										echo '<th>'.date('d', strtotime($year."-".$month."-".$day)).'</th>';
									}
								}
								?>
								<th><?= lang("total"); ?></th>
							</tr>
                        </thead>
						<tbody>
							<?php
								$tbody = "";
								if(isset($categories) && $categories){
									foreach($categories as $category){
										$total_category  = 0;
										$tbody .="<tr><td>".$category->name."</td>";
										if(isset($number_days) && $number_days){
											for($day = 1; $day <= $number_days; $day++){
												$amount = isset($expenses[$category->id][$day]) ? $expenses[$category->id][$day] : 0;
												$total_category += $amount;
												$tbody .="<td class='text-right'>".($amount > 0 ? $this->cus->formatMoney($amount) : '')."</td>";
											}
										}
										$tbody .="<td class='text-right'><b>".$this->cus->formatMoney($total_category)."</b></td></tr>";
									}
								}
								echo $tbody;
							?>
                        </tbody>
                        <tfoot>
                        	<tr>
	                        	<td>Total Room</td>
	                        	<td>2</td>
                        	</tr>
                        	<tr>
	                        	<td>Total Room</td>
	                        	<td>2</td>
                        	</tr>
                        	<tr>
	                        	<td>Total Room</td>
	                        	<td>2</td>
                        	</tr>
                        	<tr>
	                        	<td>Total Room</td>
	                        	<td>2</td>
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
		var supplier_id = "<?= isset($_POST['supplier'])?$_POST['supplier']:0 ?>";
		if (supplier_id > 0) {
		  $('#supplier_id').val(supplier_id).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
			  $.ajax({
				type: "get", async: false,
				url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
				dataType: "json",
				success: function (data) {
				  callback(data[0]);
				}
			  });
			},
			ajax: {
			  url: site.base_url + "suppliers/suggestions",
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
		  $('#supplier_id').select2({
			minimumInputLength: 1,
			ajax: {
			  url: site.base_url + "suppliers/suggestions",
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
            window.location.href = "<?=site_url('reports/daily_expenses_export/pdf/?v=1'.$v)?>";
            return false;
        });
		$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/daily_expenses_export/0/xls/?v=1'.$v)?>";
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



