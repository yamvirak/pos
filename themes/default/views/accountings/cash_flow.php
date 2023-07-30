s<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('cash_flow'); ?>
			<?php
				if ($this->input->post('start_date')) {
					echo lang('from') .' '.$this->input->post('start_date') ." ". lang('to'). " " . $this->input->post('end_date');
				}else{
					echo lang('from') .' '.date("d/m/Y") ." ".lang('to'). " " . date("d/m/Y");
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

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("accountings/cash_flow"); ?>
					
                    <div class="row">
						
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller[]', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" multiple id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						
						<?php if($Settings->project == 1){ ?>
							<div class="col-md-3 project">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project-multi">
										<?php
										$mpj[''] = array(); 
										if(isset($multi_projects) && $multi_projects){
											foreach ($multi_projects as $multi_project) {
												$mpj[$multi_project->id] = $multi_project->name;
											}
										}
										
										echo form_dropdown('project_multi[]', $mpj, (isset($_POST['project_multi']) ? $_POST['project_multi'] : $Settings->project_id), 'id="project_multi" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '"  style="width:100%;" multiple');
										?>
									</div>	
								</div>
							 </div>
						<?php } ?>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date("d/m/Y")), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
						
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date("d/m/Y")), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>

                       
                    </div>
					
                    <div class="form-group">
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
						</div>
                    </div>
					
                    <?php echo form_close(); ?>

                </div>
				
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;" border="1" class="table table-bordered table-hover table-striped table-condensed accountings-table dataTable">
						<thead>
							<tr>
								<th><?= lang('account'); ?></th>
								<th><?= lang('amount'); ?></th>
							</tr>
                        </thead>
						<tbody>
							<?php 
								$html = '';
								$cash_period = 0;
								foreach($cash_flows as $cash_flow){
									$total_cash = 0;
									$html .='<tr style="color:#4286f4; font-weight:bold"><td colspan="2">'.$cash_flow->name.'</td></tr>';
									if($cash_flow->id=='1'){
										$net_income = $this->accountings_model->getNETIncome();
										$net_income_amount= $net_income->amount * (-1);						
										if($net_income_amount<0){
											$net_income_show="( ".$this->cus->formatMoney(abs($net_income_amount))." )";	
										}else{
											$net_income_show= $this->cus->formatMoney($net_income_amount);	
										}
										$html .='<tr style="color:#39c65c; font-weight:bold">
													<td><span style="margin-left:3%"> NET INCOME </span></td>
													<td style="text-align:right">'.$net_income_show.'</td>
												</tr>';
										$total_cash += 	$net_income_amount;	
									}
									$amountCashFlows = $this->accountings_model->getAmountByCashFlow($cash_flow->id);
									if($amountCashFlows){
										foreach($amountCashFlows as $amountCashFlow){		
											$nature = $amountCashFlow->nature;										
											if($amountCashFlow->amount < 0){
												$amount=abs($amountCashFlow->amount);
											}else{
												$amount=$nature * $amountCashFlow->amount;
											}
											
											if($amount < 0){
												$amount_show="( ".$this->cus->formatMoney(abs($amount))." )";	
											}else{
												$amount_show= $this->cus->formatMoney($amount);	
											}
											$html .='<tr>
														<td><span style="margin-left:3%">'.$amountCashFlow->code.' - '.$amountCashFlow->name.'</span></td>
														<td style="text-align:right">'.$amount_show.'</td>
													</tr>';
											$total_cash += $amount;
										}
									}
									
									if($total_cash < 0){
										$total_cash_show="( ".$this->cus->formatMoney(abs($total_cash))." )";	
									}else{
										$total_cash_show= $this->cus->formatMoney($total_cash);	
									}
									$cash_period += $total_cash;
									$html .='<tr style="color:#4286f4; font-weight:bold">
												<td><span>'.lang("total").' '.$cash_flow->name.'</span></td>
												<td style="text-align:right">'.$total_cash_show.'</td>
											</tr>';
								}
								if($cash_period < 0){
									$cash_period_show="( ".$this->cus->formatMoney(abs($cash_period))." )";	
								}else{
									$cash_period_show= $this->cus->formatMoney($cash_period);	
								}
								$html .='<tr style="color:#4286f4; font-weight:bold">
												<td><span>'.lang("total_cash").' '.lang("increse_for_period").'</span></td>
												<td style="text-align:right">'.$cash_period_show.'</td>
											</tr>';
											
								$last_net_income = $this->accountings_model->getNETIncome(1);
								$last_income_amount=$last_net_income->amount * (-1);
								$last_cash_flow = $this->accountings_model->getLastAmountByCashFlow();
								$last_cash_period = $last_income_amount + $last_cash_flow->amount;
								
								if($last_cash_period < 0){
									$last_cash_period_show="( ".$this->cus->formatMoney(abs($last_cash_period))." )";	
								}else{
									$last_cash_period_show= $this->cus->formatMoney($last_cash_period);	
								}
								
								$html .='<tr style="color:#4286f4; font-weight:bold">
											<td><span>'.lang("cash_flow").' '.lang("begining").'</span></td>
											<td style="text-align:right">'.$last_cash_period_show.'</td>
										</tr>';
								
								$end_cash_period = $last_cash_period + $cash_period;
								
								if($end_cash_period < 0){
									$end_cash_period_show="( ".$this->cus->formatMoney(abs($end_cash_period))." )";	
								}else{
									$end_cash_period_show= $this->cus->formatMoney($end_cash_period);	
								}
								
								$html .='<tr style="color:#4286f4; font-weight:bold">
											<td><span>'.lang("cash_flow").' '.lang("ending").'</span></td>
											<td style="text-align:right">'.$end_cash_period_show.'</td>
										</tr>';
								
								echo $html;
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
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:10px !important; }</style>' + $('.table-responsive').html());
			this.href = result;
			this.download = "cash_flow.xls";
			return true;			
		});
		
		biller();
		$("#biller").change(biller);
		function biller(){
			var biller = $("#biller").val();
			<?php
				$multi_project = '';
				if(isset($_POST['project_multi'])){
					for($i=0; $i<count($_POST['project_multi']); $i++){
						$multi_project .=$_POST['project_multi'][$i].'#';
					}
				}
				
			?>
			var project_multi = '<?= $multi_project ?>';
			$.ajax({
				url : "<?= site_url("accountings/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project_multi : project_multi },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$(".no-project-multi").html(data.multi_resultl);
						$("#project_multi").select2();
					}
				}
			})
		}
    });
</script>