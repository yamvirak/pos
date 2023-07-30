<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('trial_balance'); ?>
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

                    <?php echo form_open("accountings/trial_balance"); ?>
					
                    <div class="row">
						
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller[]', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control biller" id="biller" multiple data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
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
						
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="sub_account"><?= lang("sub_account"); ?></label>
                                <?php
                                $sub_acc["no"] = lang('no');
								$sub_acc["yes"] = lang('yes');
                                echo form_dropdown('sub_account', $sub_acc, (isset($_POST['sub_account']) ? $_POST['sub_account'] : ""), 'class="form-control" id="sub_account" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sub_account") . '"');
                                ?>
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
				<?php
					if(isset($_POST['start_date'])){
						$start_date = $this->cus->fsd($_POST['start_date']);
					}else{
						$start_date = date("Y-m-d");
					}
					if(isset($_POST['end_date'])){
						$end_date = $this->cus->fsd($_POST['end_date']);
					}else{
						$end_date = date("Y-m-d");
					}
					
				
					$biller_multi = (isset($_POST['biller']) ? $_POST['biller'] : false);
					$project_multi = (isset($_POST['project_multi']) ? $_POST['project_multi'] : false);
					$project = (isset($_POST['project']) ? $_POST['project'] : false);
					$thead = '';
					$head_total = '';
					if($biller_multi && !$project_multi){
						$rowspan = 3;
						$colspan_main = 7;
						$sub_thead = '<tr>';
						$sub_thead1 = '<tr>';
						for($i=0; $i<count($biller_multi); $i++){
							$colspan_main += 6;
							$biller_detail = $this->site->getCompanyByID($biller_multi[$i]);
							if($biller_detail){
								$thead .= '<th colspan="6">'.$biller_detail->name.'</td>';
							}else{
								$thead .= '<th colspan="6">'.lang('no_biller').'</td>';
							}
							$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('begin').'</th>';
							$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('current').'</th>';
							$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('balance').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						}
						
						$thead .= '<th colspan="6">'.lang('total').'</td>';
						$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('begin').'</th>';
						$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('current').'</th>';
						$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('balance').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						$sub_thead .= '</tr>';
						$sub_thead1 .= '</tr>';
					}else if($project_multi){
						$rowspan = 3;
						$colspan_main = 7;
						$sub_thead = '<tr>';
						$sub_thead1 = '<tr>';
						for($i=0; $i<count($project_multi); $i++){
							$colspan_main += 6;
							$project_detail = $this->site->getProjectByID($project_multi[$i]);
							if($project_detail){
								$thead .= '<th colspan="6">'.$project_detail->name.'</td>';
							}else{
								$thead .= '<th colspan="6">'.lang('no_project').'</td>';
							}
							$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('begin').'</th>';
							$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('current').'</th>';
							$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('balance').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
							$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						}
						$thead .= '<th colspan="6">'.lang('total').'</td>';
						$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('begin').'</th>';
						$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('current').'</th>';
						$sub_thead .= '<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('balance').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead1 .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						$sub_thead .= '</tr>';
						$sub_thead1 .= '</tr>';
					}else{
						$head_total = '';
						$sub_thead = '';
						$sub_thead1 = '';
						$rowspan = 2;
						$colspan_main = 7;
						$thead.='<th colspan="2">'.lang('begin').'</th>';
						$thead.='<th colspan="2">'.lang('current').'</th>';
						$thead.='<th colspan="2">'.lang('balance').'</th>';
						$sub_thead .= '<tr><th style="border: 1px solid #428bca; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead .= '<th style="border: 1px solid #428bca; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						$sub_thead .= '<th style="border: 1px solid #428bca; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead .= '<th style="border: 1px solid #428bca; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th>';
						$sub_thead .= '<th style="border: 1px solid #428bca; color: white; background-color:#428bca; text-align:center">'.lang('debit').'</th>';
						$sub_thead .= '<th style="border: 1px solid #428bca; color: white; background-color:#428bca; text-align:center">'.lang('credit').'</th></tr>';
					}
					
				?>
				
				
				
				<?php
					function getAccountByParent($parent_code){
						$CI =& get_instance();
						$data = $CI->accountings_model->getAccountByParent($parent_code);
						return $data;
				
					}
					$retainearning_acc = $Settings->retainearning_acc;
					$accTrans = array();
					$accBeginTrans = array();
					$accTranBillers = array();
					$accBeginTranBillers = array();
					$accTranProjects = array();
					$accBeginTranProjects = array();
					
					$getAccTranAmounts = $this->accountings_model->getAccTranAmounts();
					if($getAccTranAmounts){
						foreach($getAccTranAmounts as $getAccTranAmount){
							$accTrans[$getAccTranAmount->account] = $getAccTranAmount->amount;
						}
						
					}
					
					
					
					$getBeginAccTranAmounts = $this->accountings_model->getBeginAccTranAmounts();
					if($getBeginAccTranAmounts){
						foreach($getBeginAccTranAmounts as $getBeginAccTranAmount){
							$accBeginTrans[$getBeginAccTranAmount->account] = $getBeginAccTranAmount->amount;
						}
						
					}
					
					$retainearning = $this->accountings_model->getAmountRetainEarning()->amount;
					$accBeginTrans[$retainearning_acc] = $retainearning + (isset($accBeginTrans[$retainearning_acc]) ? $accBeginTrans[$retainearning_acc] : 0);
					
					if($biller_multi && !$project_multi){
						$getAccTranAmounts = $this->accountings_model->getAccTranAmountBillers();
						if($getAccTranAmounts){
							foreach($getAccTranAmounts as $getAccTranAmount){
								$accTranBillers[$getAccTranAmount->account][$getAccTranAmount->biller_id] = $getAccTranAmount->amount;
							}
						}
						$getBeginAccTranAmounts = $this->accountings_model->getBeginAccTranAmountBillers();
						if($getBeginAccTranAmounts){
							foreach($getBeginAccTranAmounts as $getBeginAccTranAmount){
								$accBeginTranBillers[$getBeginAccTranAmount->account][$getBeginAccTranAmount->biller_id] = $getBeginAccTranAmount->amount;
							}
							
						}
						
						
						$getRetainEarningBillers = $this->accountings_model->getAmountRetainEarningBillers();
						if($getRetainEarningBillers){
							foreach($getRetainEarningBillers as $getRetainEarningBiller){
								$accBeginTranBillers[$retainearning_acc][$getRetainEarningBiller->biller_id] = (isset($accBeginTranBillers[$retainearning_acc][$getRetainEarningBiller->biller_id])?$accBeginTranBillers[$retainearning_acc][$getRetainEarningBiller->biller_id]:0) + $getRetainEarningBiller->amount;
							}
						}
						
					}else if($project_multi){
						$getAccTranAmounts = $this->accountings_model->getAccTranAmountProjects();
						if($getAccTranAmounts){
							foreach($getAccTranAmounts as $getAccTranAmount){
								$accTranProjects[$getAccTranAmount->account][$getAccTranAmount->project_id] = $getAccTranAmount->amount;
							}
						}
						$getBeginAccTranAmounts = $this->accountings_model->getBeginAccTranAmountProjects();
						if($getBeginAccTranAmounts){
							foreach($getBeginAccTranAmounts as $getBeginAccTranAmount){
								$accBeginTranProjects[$getBeginAccTranAmount->account][$getBeginAccTranAmount->project_id] = $getBeginAccTranAmount->amount;
							}
							
						}
						
						$getRetainEarningProjects = $this->accountings_model->getAmountRetainEarningProjects();
						if($getRetainEarningProjects){
							foreach($getRetainEarningProjects as $getRetainEarningProject){
								$accBeginTranProjects[$retainearning_acc][$getRetainEarningProject->project_id] = (isset($accBeginTranProjects[$retainearning_acc][$getRetainEarningProject->project_id])?$accBeginTranProjects[$retainearning_acc][$getRetainEarningProject->project_id]:0) + $getRetainEarningProject->amount;
							}
						}
					}
					
					
					function formatMoney($number)
					{
						$CI =& get_instance();
						$data = $CI->cus->formatMoney($number);
						return $data;
					}
					
					

					function getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $accBeginTrans, $accBeginTranBillers, $accBeginTranProjects, $start_date, $end_date){
						$sub_td = '';
						$total_amount = 0;
						$total_amount_begin = 0;
						$amount = 0;
						$amount_begin = 0;
						$total_amount_billers = array();
						$total_amount_billers_begin = array();
						$total_amount_projects = array();
						$total_amount_projects_begin = array();
						foreach($subAccounts as $subAccount){
							$tmp_td = '';
							$space ='&nbsp&nbsp';
							$split = explode('/',$subAccount->line_age);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							$amount = (isset($accTrans[$subAccount->code]) ? $accTrans[$subAccount->code] : 0);
							$amount_begin = (isset($accBeginTrans[$subAccount->code]) ? $accBeginTrans[$subAccount->code] : 0);
							$SubSubAccounts = getAccountByParent($subAccount->code);
							if($SubSubAccounts){
								$SubSubAccount = getSubSubAccount($SubSubAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $accBeginTrans, $accBeginTranBillers, $accBeginTranProjects, $start_date, $end_date);
								$tmp_td = $SubSubAccount['sub_td'];
								$amount += $SubSubAccount['total_amount'];
								$amount_begin += $SubSubAccount['total_amount_begin'];
							}else{
								$SubSubAccount = array();
							}
							
							if($biller_multi && !$project_multi){
								foreach($biller_multi as $biller_id){
									$amount_biller = (isset($accTranBillers[$subAccount->code][$biller_id])?$accTranBillers[$subAccount->code][$biller_id]:0);
									$amount_biller_begin = (isset($accBeginTranBillers[$subAccount->code][$biller_id])?$accBeginTranBillers[$subAccount->code][$biller_id]:0);
									$total_amount_billers[$biller_id] = $amount_biller + (isset($total_amount_billers[$biller_id])?$total_amount_billers[$biller_id]:0) + (isset($SubSubAccount['total_amount_billers'][$biller_id])?$SubSubAccount['total_amount_billers'][$biller_id]:0);
									$total_amount_billers_begin[$biller_id] = $amount_biller_begin + (isset($total_amount_billers_begin[$biller_id])?$total_amount_billers_begin[$biller_id]:0) + (isset($SubSubAccount['total_amount_billers_begin'][$biller_id])?$SubSubAccount['total_amount_billers_begin'][$biller_id]:0);
								}
							}else if($project_multi){
								foreach($project_multi as $project_id){
									$amount_project = (isset($accTranProjects[$subAccount->code][$project_id])?$accTranProjects[$subAccount->code][$project_id]:0);
									$amount_project_begin = (isset($accBeginTranProjects[$subAccount->code][$project_id])?$accBeginTranProjects[$subAccount->code][$project_id]:0);
									$total_amount_projects[$project_id] = $amount_project + (isset($total_amount_projects[$project_id])?$total_amount_projects[$project_id]:0) + (isset($SubSubAccount['total_amount_projects'][$project_id])?$SubSubAccount['total_amount_projects'][$project_id]:0);
									$total_amount_projects_begin[$project_id] = $amount_project_begin + (isset($total_amount_projects_begin[$project_id])?$total_amount_projects_begin[$project_id]:0) + (isset($SubSubAccount['total_amount_projects_begin'][$project_id])?$SubSubAccount['total_amount_projects_begin'][$project_id]:0);
								}
							}
							
							$total_amount += $amount;
							$total_amount_begin += $amount_begin;
							
							$amount_balance = $amount_begin + $amount;
							
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if($amount_begin || $amount){
									
									if($amount_begin < 0){
										$v_amount_debit_begin = '';
										$v_amount_credit_begin = formatMoney(abs($amount_begin));
									}else if($amount_begin > 0){
										$v_amount_debit_begin = formatMoney($amount_begin);
										$v_amount_credit_begin = '';
									}else{
										$v_amount_debit_begin = '';
										$v_amount_credit_begin = '';
									}
									
									if($amount < 0){
										$v_amount_debit = '';
										$v_amount_credit = formatMoney(abs($amount));
									}else if($amount > 0){
										$v_amount_debit = formatMoney($amount);
										$v_amount_credit = '';
									}else{
										$v_amount_debit = '';
										$v_amount_credit = '';
									}
									
									if($amount_balance < 0){
										$v_amount_debit_balance = '';
										$v_amount_credit_balance = formatMoney(abs($amount_balance));
									}else if($amount_balance > 0){
										$v_amount_debit_balance = formatMoney($amount_balance);
										$v_amount_credit_balance = '';
									}else{
										$v_amount_debit_balance = '';
										$v_amount_credit_balance = '';
									}
									
									$sub_td_biller = '';
									$sub_td_project = '';
									if($biller_multi && !$project_multi){									
										foreach($biller_multi as $biller_id){
											$amount_biller_begin = (isset($accBeginTranBillers[$subAccount->code][$biller_id])?$accBeginTranBillers[$subAccount->code][$biller_id]:0) + (isset($SubSubAccount['total_amount_billers_begin'][$biller_id])?$SubSubAccount['total_amount_billers_begin'][$biller_id]:0);
											if($amount_biller_begin < 0){
												$v_amount_biller_debit_begin = '';
												$v_amount_biller_credit_begin = formatMoney(abs($amount_biller_begin));
											}else if($amount_biller_begin > 0){
												$v_amount_biller_debit_begin = formatMoney($amount_biller_begin);
												$v_amount_biller_credit_begin = '';
											}else{
												$v_amount_biller_debit_begin = '';
												$v_amount_biller_credit_begin = '';
											}
											
											$amount_biller = (isset($accTranBillers[$subAccount->code][$biller_id])?$accTranBillers[$subAccount->code][$biller_id]:0) + (isset($SubSubAccount['total_amount_billers'][$biller_id])?$SubSubAccount['total_amount_billers'][$biller_id]:0);
											if($amount_biller < 0){
												$v_amount_biller_debit = '';
												$v_amount_biller_credit = formatMoney(abs($amount_biller));
											}else if($amount_biller > 0){
												$v_amount_biller_debit = formatMoney($amount_biller);
												$v_amount_biller_credit = '';
											}else{
												$v_amount_biller_debit = '';
												$v_amount_biller_credit = '';
											}
											
											$amount_biller_balance = $amount_biller_begin + $amount_biller;
											
											if($amount_biller_balance < 0){
												$v_amount_biller_debit_balance = '';
												$v_amount_biller_credit_balance = formatMoney(abs($amount_biller_balance));
											}else if($amount_biller_balance > 0){
												$v_amount_biller_debit_balance = formatMoney($amount_biller_balance);
												$v_amount_biller_credit_balance = '';
											}else{
												$v_amount_biller_debit_balance = '';
												$v_amount_biller_credit_balance = '';
											}
											
										
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/x/begin/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_debit_begin.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/x/begin/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_credit_begin.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_debit.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_credit.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->code.'/x/x/x/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_debit_balance.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->code.'/x/x/x/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_credit_balance.'</td>';
											
										}
									}else if($project_multi){
																				
										foreach($project_multi as $project_id){
											
											$amount_project_begin = (isset($accBeginTranProjects[$subAccount->code][$project_id])?$accBeginTranProjects[$subAccount->code][$project_id]:0) + (isset($SubSubAccount['total_amount_projects_begin'][$project_id])?$SubSubAccount['total_amount_projects_begin'][$project_id]:0);
											if($amount_project_begin < 0){
												$v_amount_project_debit_begin = '';
												$v_amount_project_credit_begin = formatMoney(abs($amount_project_begin));
											}else if($amount_project_begin > 0){
												$v_amount_project_debit_begin = formatMoney($amount_project_begin);
												$v_amount_project_credit_begin = '';
											}else{
												$v_amount_project_debit_begin = '';
												$v_amount_project_credit_begin = '';
											}
											
											$amount_project = (isset($accTranProjects[$subAccount->code][$project_id])?$accTranProjects[$subAccount->code][$project_id]:0) + (isset($SubSubAccount['total_amount_projects'][$project_id])?$SubSubAccount['total_amount_projects'][$project_id]:0);
											if($amount_project < 0){
												$v_amount_project_debit = '';
												$v_amount_project_credit = formatMoney(abs($amount_project));
											}else if($amount_project > 0){
												$v_amount_project_debit = formatMoney($amount_project);
												$v_amount_project_credit = '';
											}else{
												$v_amount_project_debit = '';
												$v_amount_project_credit = '';
											}
											
											$amount_project_balance = $amount_project_begin + $amount_project;
											
											if($amount_project_balance < 0){
												$v_amount_project_debit_balance = '';
												$v_amount_project_credit_balance = formatMoney(abs($amount_project_balance));
											}else if($amount_project_balance > 0){
												$v_amount_project_debit_balance = formatMoney($amount_project_balance);
												$v_amount_project_credit_balance = '';
											}else{
												$v_amount_project_debit_balance = '';
												$v_amount_project_credit_balance = '';
											}
											
		
											
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/x/begin/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_debit_begin.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/x/begin/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_credit_begin.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_debit.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_credit.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->code.'/x/x/x/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_debit_balance.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->code.'/x/x/x/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_credit_balance.'</td>';
											
										}
									}
											
									$sub_td .= '<tr>
													<td>'.$space.$subAccount->code.' - '.$subAccount->name.'</td>
													'.$sub_td_biller.'
													'.$sub_td_project.'
													<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/x/begin/x/x" style="text-align:right">'.$v_amount_debit_begin.'</td>
													<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/x/begin/x/x" style="text-align:right">'.$v_amount_credit_begin.'</td>
													<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount_debit.'</td>
													<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount_credit.'</td>
													<td class="accounting_link" id="'.$subAccount->code.'/x/x/x/x/x" style="text-align:right">'.$v_amount_debit_balance.'</td>
													<td class="accounting_link" id="'.$subAccount->code.'/x/x/x/x/x" style="text-align:right">'.$v_amount_credit_balance.'</td>
												</tr>';		
								}
							}
							
							$sub_td .=	$tmp_td;		
						}
						$data = array(
								'sub_td' => $sub_td,
								'total_amount' => $total_amount,
								'total_amount_begin' => $total_amount_begin,
								'total_amount_billers' => $total_amount_billers,
								'total_amount_projects' => $total_amount_projects,
								'total_amount_billers_begin' => $total_amount_billers_begin,
								'total_amount_projects_begin' => $total_amount_projects_begin);
						return $data;
					}	
					
					function getSubSubAccount($SubSubAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $accBeginTrans, $accBeginTranBillers, $accBeginTranProjects, $start_date, $end_date){
						$sub_td = '';
						$total_amount = 0;
						$total_amount_begin = 0;
						$amount = 0;
						$amount_begin = 0;
						$total_amount_billers = array();
						$total_amount_billers_begin = array();
						$total_amount_projects = array();
						$total_amount_projects_begin = array();
						foreach($SubSubAccounts as $SubSubAccount){
							$tmp_td = '';
							$space ='&nbsp&nbsp';
							$split = explode('/',$SubSubAccount->line_age);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							$amount_begin = (isset($accBeginTrans[$SubSubAccount->code]) ? $accBeginTrans[$SubSubAccount->code] : 0);
							$amount = (isset($accTrans[$SubSubAccount->code]) ? $accTrans[$SubSubAccount->code] : 0);
							$subAccounts = getAccountByParent($SubSubAccount->code);
							if($subAccounts){
								$subAccount = getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $accBeginTrans, $accBeginTranBillers, $accBeginTranProjects, $start_date, $end_date);
								$tmp_td = $subAccount['sub_td'];
								$amount += $subAccount['total_amount'];
								$amount_begin += $subAccount['total_amount_begin'];
							}else{
								$subAccount = array();
							}
							
							
							if($biller_multi && !$project_multi){
								foreach($biller_multi as $biller_id){
									$amount_biller = (isset($accTranBillers[$SubSubAccount->code][$biller_id])?$accTranBillers[$SubSubAccount->code][$biller_id]:0);
									$amount_biller_begin = (isset($accBeginTranBillers[$SubSubAccount->code][$biller_id])?$accBeginTranBillers[$SubSubAccount->code][$biller_id]:0);
									$total_amount_billers[$biller_id] = $amount_biller + (isset($total_amount_billers[$biller_id])?$total_amount_billers[$biller_id]:0) + (isset($subAccount['total_amount_billers'][$biller_id])?$subAccount['total_amount_billers'][$biller_id]:0);
									$total_amount_billers_begin[$biller_id] = $amount_biller_begin + (isset($total_amount_billers_begin[$biller_id])?$total_amount_billers_begin[$biller_id]:0) + (isset($subAccount['total_amount_billers_begin'][$biller_id])?$subAccount['total_amount_billers_begin'][$biller_id]:0);
								}
							}else if($project_multi){
								foreach($project_multi as $project_id){
									$amount_project = (isset($accTranProjects[$SubSubAccount->code][$project_id])?$accTranProjects[$SubSubAccount->code][$project_id]:0);
									$amount_project_begin = (isset($accBeginTranProjects[$SubSubAccount->code][$project_id])?$accBeginTranProjects[$SubSubAccount->code][$project_id]:0);
									$total_amount_projects[$project_id] = $amount_project + (isset($total_amount_projects[$project_id])?$total_amount_projects[$project_id]:0) + (isset($subAccount['total_amount_projects'][$project_id])?$subAccount['total_amount_projects'][$project_id]:0);
									$total_amount_projects_begin[$project_id] = $amount_project_begin + (isset($total_amount_projects_begin[$project_id])?$total_amount_projects_begin[$project_id]:0) + (isset($subAccount['total_amount_projects_begin'][$project_id])?$subAccount['total_amount_projects_begin'][$project_id]:0);
								}
							}

							$total_amount_begin += $amount_begin;
							$total_amount += $amount;
							
							$amount_balance = $amount_begin + $amount;
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if($amount_begin || $amount){
									
									if($amount_begin < 0){
										$v_amount_debit_begin = '';
										$v_amount_credit_begin = formatMoney(abs($amount_begin));
									}else if($amount_begin > 0){
										$v_amount_debit_begin = formatMoney($amount_begin);
										$v_amount_credit_begin = '';
									}else{
										$v_amount_debit_begin = '';
										$v_amount_credit_begin = '';
									}
									
									if($amount < 0){
										$v_amount_debit = '';
										$v_amount_credit = formatMoney(abs($amount));
									}else if($amount > 0){
										$v_amount_debit = formatMoney($amount);
										$v_amount_credit = '';
									}else{
										$v_amount_debit = '';
										$v_amount_credit = '';
									}
									
									if($amount_balance < 0){
										$v_amount_debit_balance = '';
										$v_amount_credit_balance = formatMoney(abs($amount_balance));
									}else if($amount_balance > 0){
										$v_amount_debit_balance = formatMoney($amount_balance);
										$v_amount_credit_balance = '';
									}else{
										$v_amount_debit_balance = '';
										$v_amount_credit_balance = '';
									}
									
									$sub_td_biller = '';
									$sub_td_project = '';
									if($biller_multi && !$project_multi){
										
										foreach($biller_multi as $biller_id){
											$amount_biller_begin = (isset($accBeginTranBillers[$SubSubAccount->code][$biller_id])?$accBeginTranBillers[$SubSubAccount->code][$biller_id]:0) + (isset($subAccount['total_amount_billers_begin'][$biller_id])?$subAccount['total_amount_billers_begin'][$biller_id]:0);
											if($amount_biller_begin < 0){
												$v_amount_biller_debit_begin = '';
												$v_amount_biller_credit_begin = formatMoney(abs($amount_biller_begin));
											}else if($amount_biller_begin > 0){
												$v_amount_biller_debit_begin = formatMoney($amount_biller_begin);
												$v_amount_biller_credit_begin = '';
											}else{
												$v_amount_biller_debit_begin = '';
												$v_amount_biller_credit_begin = '';
											}
											
											$amount_biller = (isset($accTranBillers[$SubSubAccount->code][$biller_id])?$accTranBillers[$SubSubAccount->code][$biller_id]:0) + (isset($subAccount['total_amount_billers'][$biller_id])?$subAccount['total_amount_billers'][$biller_id]:0);
											if($amount_biller < 0){
												$v_amount_biller_debit = '';
												$v_amount_biller_credit = formatMoney(abs($amount_biller));
											}else if($amount_biller > 0){
												$v_amount_biller_debit = formatMoney($amount_biller);
												$v_amount_biller_credit = '';
											}else{
												$v_amount_biller_debit = '';
												$v_amount_biller_credit = '';
											}
											
											$amount_biller_balance = $amount_biller_begin + $amount_biller;
											
											if($amount_biller_balance < 0){
												$v_amount_biller_debit_balance = '';
												$v_amount_biller_credit_balance = formatMoney(abs($amount_biller_balance));
											}else if($amount_biller_balance > 0){
												$v_amount_biller_debit_balance = formatMoney($amount_biller_balance);
												$v_amount_biller_credit_balance = '';
											}else{
												$v_amount_biller_debit_balance = '';
												$v_amount_biller_credit_balance = '';
											}
											
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/x/begin/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_debit_begin.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/x/begin/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_credit_begin.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_debit.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_credit.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/x/x/x/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_debit_balance.'</td>';
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/x/x/x/'.$biller_id.'/x" style="text-align:right;">'.$v_amount_biller_credit_balance.'</td>';
										}
									}else if($project_multi){
																				
										foreach($project_multi as $project_id){
											$amount_project_begin = (isset($accBeginTranProjects[$SubSubAccount->code][$project_id])?$accBeginTranProjects[$SubSubAccount->code][$project_id]:0) + (isset($subAccount['total_amount_projects_begin'][$project_id])?$subAccount['total_amount_projects_begin'][$project_id]:0);
											if($amount_project_begin < 0){
												$v_amount_project_debit_begin = '';
												$v_amount_project_credit_begin = formatMoney(abs($amount_project_begin));
											}else if($amount_project_begin > 0){
												$v_amount_project_debit_begin = formatMoney($amount_project_begin);
												$v_amount_project_credit_begin = '';
											}else{
												$v_amount_project_debit_begin = '';
												$v_amount_project_credit_begin = '';
											}
											
											$amount_project = (isset($accTranProjects[$SubSubAccount->code][$project_id])?$accTranProjects[$SubSubAccount->code][$project_id]:0) + (isset($subAccount['total_amount_projects'][$project_id])?$subAccount['total_amount_projects'][$project_id]:0);
											if($amount_project < 0){
												$v_amount_project_debit = '';
												$v_amount_project_credit = formatMoney(abs($amount_project));
											}else if($amount_project > 0){
												$v_amount_project_debit = formatMoney($amount_project);
												$v_amount_project_credit = '';
											}else{
												$v_amount_project_debit = '';
												$v_amount_project_credit = '';
											}
											
											$amount_project_balance = $amount_project_begin + $amount_project;
											
											if($amount_project_balance < 0){
												$v_amount_project_debit_balance = '';
												$v_amount_project_credit_balance = formatMoney(abs($amount_project_balance));
											}else if($amount_project_balance > 0){
												$v_amount_project_debit_balance = formatMoney($amount_project_balance);
												$v_amount_project_credit_balance = '';
											}else{
												$v_amount_project_debit_balance = '';
												$v_amount_project_credit_balance = '';
											}
											
											
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/x/begin/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_debit_begin.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/x/begin/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_credit_begin.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_debit.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_credit.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/x/x/x/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_debit_balance.'</td>';
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/x/x/x/x/'.$project_id.'" style="text-align:right;">'.$v_amount_project_credit_balance.'</td>';
											

										}
									}
									$sub_td .= '<tr>
													<td>'.$space.$SubSubAccount->code.' - '.$SubSubAccount->name.'</td>
													'.$sub_td_biller.'
													'.$sub_td_project.'
													<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/x/begin/x/x" style="text-align:right">'.$v_amount_debit_begin.'</td>
													<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/x/begin/x/x" style="text-align:right">'.$v_amount_credit_begin.'</td>
													<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount_debit.'</td>
													<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount_credit.'</td>
													<td class="accounting_link" id="'.$SubSubAccount->code.'/x/x/x/x/x" style="text-align:right">'.$v_amount_debit_balance.'</td>
													<td class="accounting_link" id="'.$SubSubAccount->code.'/x/x/x/x/x" style="text-align:right">'.$v_amount_credit_balance.'</td>
												</tr>';
								}
							}
							$sub_td .= $tmp_td;				
						}
						$data = array(
								'sub_td' => $sub_td,
								'total_amount' => $total_amount,
								'total_amount_begin' => $total_amount_begin,
								'total_amount_billers' => $total_amount_billers,
								'total_amount_projects' => $total_amount_projects,
								'total_amount_billers_begin' => $total_amount_billers_begin,
								'total_amount_projects_begin' => $total_amount_projects_begin);
						return $data;
					}

					
				
					$tbody = '';
					$total_debit = 0;
					$total_credit = 0;
					$total_debit_begin= 0;
					$total_credit_begin = 0;
					$total_debit_balance= 0;
					$total_credit_balance = 0;

					
					$total_debit_billers = array();
					$total_credit_billers = array();
					$total_debit_billers_begin = array();
					$total_credit_billers_begin = array();

					
					$total_debit_projects = array();
					$total_credit_projects = array();
					foreach($trial_balances as $trial_balance){
						$sections = $this->accountings_model->getAccountSectionsByCode(array($trial_balance));	
						if($sections){
							foreach($sections as $section){
								$tbody .="<tr  style='color:#39c65c; font-weight:bold'><td style='text-align:left' colspan='".$colspan_main."'><span>".$section->name."</span></td></tr>";
								$mainAccounts = $this->accountings_model->getMainAccountBySection($section->id);
								if($mainAccounts){
									foreach($mainAccounts as $mainAccount){
										$tmp_td = '';
										$subAccounts = getAccountByParent($mainAccount->code);			
										$amount_begin = (isset($accBeginTrans[$mainAccount->code]) ? $accBeginTrans[$mainAccount->code] : 0);
										$amount = (isset($accTrans[$mainAccount->code]) ? $accTrans[$mainAccount->code] : 0);
										if($subAccounts){
											$sub_acc = getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $accBeginTrans, $accBeginTranBillers, $accBeginTranProjects, $start_date, $end_date);
											$tmp_td = $sub_acc['sub_td'];
											$amount += $sub_acc['total_amount'];
											$amount_begin += $sub_acc['total_amount_begin'];
										}else{
											$sub_acc = array();
										}
										$amount_balance = $amount_begin + $amount;
										if($amount_begin || $amount){
											if($amount_begin < 0){
												$total_credit_begin += $amount_begin;
												$v_amount_debit_begin = '';
												$v_amount_credit_begin = formatMoney(abs($amount_begin));
											}else if($amount_begin > 0){
												$total_debit_begin += $amount_begin;
												$v_amount_debit_begin = formatMoney($amount_begin);
												$v_amount_credit_begin = '';
											}else{
												$v_amount_debit_begin = '';
												$v_amount_credit_begin = '';
											}
											if($amount < 0){
												$total_credit += $amount;
												$v_amount_debit = '';
												$v_amount_credit = formatMoney(abs($amount));
											}else if($amount > 0){
												$total_debit += $amount;
												$v_amount_debit = formatMoney($amount);
												$v_amount_credit = '';
											}else{
												$v_amount_debit = '';
												$v_amount_credit = '';
											}
											
											if($amount_balance < 0){
												$total_credit_balance += $amount_balance;
												$v_amount_debit_balance = '';
												$v_amount_credit_balance = formatMoney(abs($amount_balance));
											}else if($amount_balance > 0){
												$total_debit_balance += $amount_balance;
												$v_amount_debit_balance = formatMoney($amount_balance);
												$v_amount_credit_balance = '';
											}else{
												$v_amount_debit_balance = '';
												$v_amount_credit_balance = '';
											}
											$sub_td_biller = '';
											$sub_td_project = '';
											if($biller_multi && !$project_multi){
																								
												foreach($biller_multi as $biller_id){
													
													$amount_biller_begin = (isset($accBeginTranBillers[$mainAccount->code][$biller_id])?$accBeginTranBillers[$mainAccount->code][$biller_id]:0);
													$amount_biller_begin = $amount_biller_begin + (isset($sub_acc['total_amount_billers_begin'][$biller_id])?$sub_acc['total_amount_billers_begin'][$biller_id]:0);
													if($amount_biller_begin < 0){
														$total_credit_billers_begin[$biller_id] = (isset($total_credit_billers_begin[$biller_id])?$total_credit_billers_begin[$biller_id]:0) + $amount_biller_begin;
														$v_amount_biller_debit_begin = '';
														$v_amount_biller_credit_begin = formatMoney(abs($amount_biller_begin));
													}else if($amount_biller_begin > 0){
														$total_debit_billers_begin[$biller_id] = (isset($total_debit_billers_begin[$biller_id])?$total_debit_billers_begin[$biller_id]:0) + $amount_biller_begin;
														$v_amount_biller_debit_begin = formatMoney($amount_biller_begin);
														$v_amount_biller_credit_begin = '';
													}else{
														$v_amount_biller_credit_begin = '';
														$v_amount_biller_debit_begin = '';
													}
													
													
													$amount_biller = (isset($accTranBillers[$mainAccount->code][$biller_id])?$accTranBillers[$mainAccount->code][$biller_id]:0);
													$amount_biller = $amount_biller + (isset($sub_acc['total_amount_billers'][$biller_id])?$sub_acc['total_amount_billers'][$biller_id]:0);
													if($amount_biller < 0){
														$total_credit_billers[$biller_id] = (isset($total_credit_billers[$biller_id])?$total_credit_billers[$biller_id]:0) + $amount_biller;
														$v_amount_biller_debit = '';
														$v_amount_biller_credit = formatMoney(abs($amount_biller));
													}else if($amount_biller > 0){
														$total_debit_billers[$biller_id] = (isset($total_debit_billers[$biller_id])?$total_debit_billers[$biller_id]:0) + $amount_biller;
														$v_amount_biller_debit = formatMoney($amount_biller);
														$v_amount_biller_credit = '';
													}else{
														$v_amount_biller_credit = '';
														$v_amount_biller_debit = '';
													}
													
													$amount_biller_balance = $amount_biller_begin + $amount_biller;
													if($amount_biller_balance < 0){
														$v_amount_biller_debit_balance = '';
														$v_amount_biller_credit_balance = formatMoney(abs($amount_biller_balance));
													}else if($amount_biller_balance > 0){
														$v_amount_biller_debit_balance = formatMoney($amount_biller_balance);
														$v_amount_biller_credit_balance = '';
													}else{
														$v_amount_biller_credit_balance = '';
														$v_amount_biller_debit_balance = '';
													}
													
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/x/begin/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller_debit_begin.'</td>';
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/x/begin/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller_credit_begin.'</td>';
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller_debit.'</td>';
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller_credit.'</td>';
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->code.'/x/x/x/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller_debit_balance.'</td>';
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->code.'/x/x/x/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller_credit_balance.'</td>';
												}
											} else if($project_multi){
																						
												foreach($project_multi as $project_id){
													
													$amount_project_begin = (isset($accBeginTranProjects[$mainAccount->code][$project_id])?$accBeginTranProjects[$mainAccount->code][$project_id]:0);
													$amount_project_begin = $amount_project_begin + (isset($sub_acc['total_amount_projects_begin'][$project_id])?$sub_acc['total_amount_projects_begin'][$project_id]:0);
													if($amount_project_begin < 0){
														$total_credit_projects_begin[$project_id] = (isset($total_credit_projects_begin[$project_id])?$total_credit_projects_begin[$project_id]:0) + $amount_project_begin;
														$v_amount_project_debit_begin = '';
														$v_amount_project_credit_begin = formatMoney(abs($amount_project_begin));
													}else if($amount_project_begin > 0){
														$total_debit_projects_begin[$project_id] = (isset($total_debit_projects_begin[$project_id])?$total_debit_projects_begin[$project_id]:0) + $amount_project_begin;
														$v_amount_project_debit_begin = formatMoney($amount_project_begin);
														$v_amount_project_credit_begin = '';
													}else{
														$v_amount_project_debit_begin = '';
														$v_amount_project_credit_begin = '';
													}
													
													
													$amount_project = (isset($accTranProjects[$mainAccount->code][$project_id])?$accTranProjects[$mainAccount->code][$project_id]:0);
													$amount_project = $amount_project + (isset($sub_acc['total_amount_projects'][$project_id])?$sub_acc['total_amount_projects'][$project_id]:0);
													if($amount_project < 0){
														$total_credit_projects[$project_id] = (isset($total_credit_projects[$project_id])?$total_credit_projects[$project_id]:0) + $amount_project;
														$v_amount_project_debit = '';
														$v_amount_project_credit = formatMoney(abs($amount_project));
													}else if($amount_project > 0){
														$total_debit_projects[$project_id] = (isset($total_debit_projects[$project_id])?$total_debit_projects[$project_id]:0) + $amount_project;
														$v_amount_project_debit = formatMoney($amount_project);
														$v_amount_project_credit = '';
													}else{
														$v_amount_project_debit = '';
														$v_amount_project_credit = '';
													}
													
													$amount_project_balance = $amount_project_begin + $amount_project;
													if($amount_project_balance < 0){
														$v_amount_project_debit_balance = '';
														$v_amount_project_credit_balance = formatMoney(abs($amount_project_balance));
													}else if($amount_project_balance > 0){
														$v_amount_project_debit_balance = formatMoney($amount_project_balance);
														$v_amount_project_credit_balance = '';
													}else{
														$v_amount_project_debit_balance = '';
														$v_amount_project_credit_balance = '';
													}
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/x/begin/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project_debit_begin.'</td>';
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/x/begin/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project_credit_begin.'</td>';
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project_debit.'</td>';
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project_credit.'</td>';
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->code.'/x/x/x/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project_debit_balance.'</td>';
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->code.'/x/x/x/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project_credit_balance.'</td>';
												}
											}
											$tbody .='<tr>
														<td style="font-weight:bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$mainAccount->code.' - '.$mainAccount->name.'</td>
														'.$sub_td_biller.'
														'.$sub_td_project.'
														<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/x/begin/x/x" style="text-align:right; font-weight:bold">'.$v_amount_debit_begin.'</td>
														<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/x/begin/x/x" style="text-align:right; font-weight:bold">'.$v_amount_credit_begin.'</td>
														<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right; font-weight:bold">'.$v_amount_debit.'</td>
														<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right; font-weight:bold">'.$v_amount_credit.'</td>
														<td class="accounting_link" id="'.$mainAccount->code.'/x/x/x/x/x" style="text-align:right; font-weight:bold">'.$v_amount_debit_balance.'</td>
														<td class="accounting_link" id="'.$mainAccount->code.'/x/x/x/x/x" style="text-align:right; font-weight:bold">'.$v_amount_credit_balance.'</td>
													</tr>';
										}
										$tbody .= $tmp_td;		
									}
								}
							}
						}
					}
					
					$td_total_biller = '';
					$td_total_project = '';
					if($biller_multi && !$project_multi){									
						foreach($biller_multi as $biller_id){
							$total_debit_biller_begin = formatMoney((isset($total_debit_billers_begin[$biller_id])?$total_debit_billers_begin[$biller_id]:0));
							$total_credit_biller_begin = formatMoney(abs((isset($total_credit_billers_begin[$biller_id])?$total_credit_billers_begin[$biller_id]:0)));
							
							$total_debit_biller = formatMoney((isset($total_debit_billers[$biller_id])?$total_debit_billers[$biller_id]:0));
							$total_credit_biller = formatMoney(abs((isset($total_credit_billers[$biller_id])?$total_credit_billers[$biller_id]:0)));
							
							$total_debit_billers_balance = formatMoney((isset($total_debit_billers_begin[$biller_id])?$total_debit_billers_begin[$biller_id]:0) + (isset($total_debit_billers[$biller_id])?$total_debit_billers[$biller_id]:0));
							$total_credit_billers_balance = formatMoney(abs(((isset($total_credit_billers_begin[$biller_id])?$total_credit_billers_begin[$biller_id]:0) + (isset($total_credit_billers[$biller_id])?$total_credit_billers[$biller_id]:0))));
							
							$td_total_biller .= "
												<td style='text-align:right; font-weight:bold'>".$total_debit_biller_begin."</td>
												<td style='text-align:right; font-weight:bold'>".$total_credit_biller_begin."</td>
												<td style='text-align:right; font-weight:bold'>".$total_debit_biller."</td>
												<td style='text-align:right; font-weight:bold'>".$total_credit_biller."</td>
												<td style='text-align:right; font-weight:bold'>".$total_debit_billers_balance."</td>
												<td style='text-align:right; font-weight:bold'>".$total_credit_billers_balance."</td>";
						}
					} else if($project_multi){
						foreach($project_multi as $project_id){
							
							$total_debit_project_begin = formatMoney((isset($total_debit_projects_begin[$project_id])?$total_debit_projects_begin[$project_id]:0));
							$total_credit_project_begin = formatMoney(abs((isset($total_credit_projects_begin[$project_id])?$total_credit_projects_begin[$project_id]:0)));
							
							$total_debit_project = formatMoney((isset($total_debit_projects[$project_id])?$total_debit_projects[$project_id]:0));
							$total_credit_project = formatMoney(abs((isset($total_credit_projects[$project_id])?$total_credit_projects[$project_id]:0)));
							
							$total_debit_project_balance = formatMoney((isset($total_debit_projects_begin[$project_id])?$total_debit_projects_begin[$project_id]:0) + (isset($total_debit_projects[$project_id])?$total_debit_projects[$project_id]:0));
							$total_credit_project_balance = formatMoney(abs(((isset($total_credit_projects_begin[$project_id])?$total_credit_projects_begin[$project_id]:0) + (isset($total_credit_projects[$project_id])?$total_credit_projects[$project_id]:0))));

							$td_total_project .= "<td style='text-align:right; font-weight:bold'>".$total_debit_project_begin."</td>
												<td style='text-align:right; font-weight:bold'>".$total_credit_project_begin."</td>
												<td style='text-align:right; font-weight:bold'>".$total_debit_project."</td>
												<td style='text-align:right; font-weight:bold'>".$total_credit_project."</td>
												<td style='text-align:right; font-weight:bold'>".$total_debit_project_balance."</td>
												<td style='text-align:right; font-weight:bold'>".$total_credit_project_balance."</td>";
						}
					}
					
					$v_total_debit_begin = formatMoney($total_debit_begin);
					$v_total_credit_begin = formatMoney(abs($total_credit_begin));
					
					$v_total_debit = formatMoney($total_debit);
					$v_total_credit = formatMoney(abs($total_credit));
					
					
					$v_total_debit_balance = formatMoney($total_debit_balance);
					$v_total_credit_balance= formatMoney(abs($total_credit_balance));
					
					$tbody .="<tr style='font-weight:bold; color:#4286f4'><td style='text-align:left'></td>
								".$td_total_biller."
								".$td_total_project."
								<td style='text-align:right; font-weight:bold'>".$v_total_debit_begin."</td>
								<td style='text-align:right; font-weight:bold'>".$v_total_credit_begin."</td>
								<td style='text-align:right; font-weight:bold'>".$v_total_debit."</td>
								<td style='text-align:right; font-weight:bold'>".$v_total_credit."</td>
								<td style='text-align:right; font-weight:bold'>".$v_total_debit_balance."</td>
								<td style='text-align:right; font-weight:bold'>".$v_total_credit_balance."</td>
							</tr>";

				?>
				
				
                <div class="table-responsive">
                    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;" border="1" class="table table-bordered table-hover table-striped table-condensed accountings-table dataTable">
						<thead>
							<tr>
								<th rowspan="<?= $rowspan ?>"><?= lang('account'); ?></th>
								<?= $thead ?>
								<?= $head_total ?>
							</tr>
							<?= $sub_thead ?>
							<?= $sub_thead1 ?>
                        </thead>
						<tbody>
							<?= $tbody ?>
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
			this.download = "trial_balance.xls";
			return true;			
		});
		
		$('#project').live('change', function() {
			var project_id = $(this).val();
			if(project_id != '0'){
				$(".seperate_project").slideUp();
			}else{
				$(".seperate_project").slideDown();
				
			}
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