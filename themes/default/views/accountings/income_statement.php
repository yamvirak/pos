<?php if(!$pdf){ ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('income_statement'); ?>
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
				<li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
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

                    <?php echo form_open("accountings/income_statement"); ?>
					
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
							<?php echo form_submit('pdf_report', $this->lang->line("pdf"), 'class="hidden" id="pdf_report" '); ?> 
						</div>
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

                                <?php } ?>

                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('income_statement_report_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('income_statement_report')?>
                                </div><br>
                            </td> 
                        </tr>
                </table>

				
                <div class="clearfix"></div>
		
		
			
			<?php 
				if($pdf){
					echo "<h2>".lang("income_statement")."</h2>";
					echo "<p>".date("d/m/Y H:i")."</p>";
				}
			?>
			
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
					$project = (isset($_POST['project'])?$_POST['project']: false);
					$thead = '';
					if($biller_multi && !$project_multi){
						$rowspan = 1;
						$colspan_main = 2;
						for($i=0; $i<count($biller_multi); $i++){
							$colspan_main += 1;
							$biller_detail = $this->site->getCompanyByID($biller_multi[$i]);
							if($biller_detail){
								$thead .= '<th>'.$biller_detail->name.'</td>';
							}else{
								$thead .= '<th>'.lang('no_biller').'</td>';
							}
						}
						$thead.='<th>'.lang('total').'</th>';
					}else if($project_multi){
						$rowspan = 1;
						$colspan_main = 2;
						for($i=0; $i<count($project_multi); $i++){
							$colspan_main += 1;
							$project_detail = $this->site->getProjectByID($project_multi[$i]);
							if($project_detail){
								$thead .= '<th>'.$project_detail->name.'</td>';
							}else{
								$thead .= '<th>'.lang('no_project').'</td>';
							}
						}
						$thead.='<th>'.lang('total').'</th>';
					}else{
						$rowspan = 1;
						$colspan_main = 2;
						$thead .= '<th></th>';
					}
					
				?>
				
				
				
				<?php
					function getAccountByParent($parent_code){
						$CI =& get_instance();
						$data = $CI->accountings_model->getAccountByParent($parent_code);
						return $data;
				
					}
					$accTrans = array();
					$accTranBillers = array();
					$accTranProjects = array();
					
					$getAccTranAmounts = $this->accountings_model->getAccTranAmounts();
					if($getAccTranAmounts){
						foreach($getAccTranAmounts as $getAccTranAmount){
							$accTrans[$getAccTranAmount->account] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
						}
						
					}
					
					if($biller_multi && !$project_multi){
						$getAccTranAmounts = $this->accountings_model->getAccTranAmountBillers();
						if($getAccTranAmounts){
							foreach($getAccTranAmounts as $getAccTranAmount){
								$accTranBillers[$getAccTranAmount->account][$getAccTranAmount->biller_id] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
							}
							
						}
					}else if($project_multi){
						$getAccTranAmounts = $this->accountings_model->getAccTranAmountProjects();
						if($getAccTranAmounts){
							foreach($getAccTranAmounts as $getAccTranAmount){
								$accTranProjects[$getAccTranAmount->account][$getAccTranAmount->project_id] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
							}
							
						}
					}
					
					
					function formatMoney($number)
					{
						$CI =& get_instance();
						$data = $CI->cus->formatMoney($number);
						return $data;
					}
					
					

					function getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_billers = array();
						$total_amount_projects = array();
						foreach($subAccounts as $subAccount){
							$tmp_td = '';
							$space ='&nbsp;';
							$split = explode('/',$subAccount->line_age);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							$amount = (isset($accTrans[$subAccount->code])?$accTrans[$subAccount->code]:0);
							$SubSubAccounts = getAccountByParent($subAccount->code);
							if($SubSubAccounts){
								$SubSubAccount = getSubSubAccount($SubSubAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date);
								$tmp_td = $SubSubAccount['sub_td'];
								$amount += $SubSubAccount['total_amount'];
							}else{
								$SubSubAccount = array();
							}
							if($biller_multi && !$project_multi){
								foreach($biller_multi as $biller_id){
									$amount_biller = (isset($accTranBillers[$subAccount->code][$biller_id])?$accTranBillers[$subAccount->code][$biller_id]:0);
									$total_amount_billers[$biller_id] = $amount_biller + (isset($total_amount_billers[$biller_id])?$total_amount_billers[$biller_id]:0) + (isset($SubSubAccount['total_amount_billers'][$biller_id])?$SubSubAccount['total_amount_billers'][$biller_id]:0);
								}
							}else if($project_multi){
								foreach($project_multi as $project_id){
									$amount_project = (isset($accTranProjects[$subAccount->code][$project_id])?$accTranProjects[$subAccount->code][$project_id]:0);
									$total_amount_projects[$project_id] = $amount_project + (isset($total_amount_projects[$project_id])?$total_amount_projects[$project_id]:0) + (isset($SubSubAccount['total_amount_projects'][$project_id])?$SubSubAccount['total_amount_projects'][$project_id]:0);
								}
							}
							$total_amount += $amount;
							
								if($amount != 0){
									if($amount < 0){
										$v_amount = '( '.formatMoney(abs($amount)).' )';
									}else{
										$v_amount = formatMoney($amount);
									}
									$sub_td_biller = '';
									$sub_td_project = '';
									if($biller_multi && !$project_multi){									
										foreach($biller_multi as $biller_id){
											$amount_biller = (isset($accTranBillers[$subAccount->code][$biller_id])?$accTranBillers[$subAccount->code][$biller_id]:0) + (isset($SubSubAccount['total_amount_billers'][$biller_id])?$SubSubAccount['total_amount_billers'][$biller_id]:0);
											if($amount_biller < 0){
												$v_amount_biller = '( '.formatMoney(abs($amount_biller)).' )';
											}else{
												$v_amount_biller = formatMoney($amount_biller);
											}
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right">'.$v_amount_biller.'</td>';
										}
									}else if($project_multi){								
										foreach($project_multi as $project_id){
											$amount_project = (isset($accTranProjects[$subAccount->code][$project_id])?$accTranProjects[$subAccount->code][$project_id]:0) + (isset($SubSubAccount['total_amount_projects'][$project_id])?$SubSubAccount['total_amount_projects'][$project_id]:0);
											if($amount_project < 0){
												$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
											}else{
												$v_amount_project = formatMoney($amount_project);
											}
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right">'.$v_amount_project.'</td>';
										}
									}
									$sub_td .= '<tr style="color: #de0d0d;font-weight:500;">
												<td>'.$space.$subAccount->code.' - '.$subAccount->name.'</td>
												'.$sub_td_biller.'
												'.$sub_td_project.'
												<td class="accounting_link" id="'.$subAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount.'</td>
											</tr>';
								}
							
							
							$sub_td .=	$tmp_td;		
						}
						$data = array(
								'sub_td' => $sub_td,
								'total_amount' => $total_amount,
								'total_amount_billers' => $total_amount_billers,
								'total_amount_projects' => $total_amount_projects);
						return $data;
					}	
					
					function getSubSubAccount($SubSubAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_billers = array();
						$total_amount_projects = array();
						foreach($SubSubAccounts as $SubSubAccount){
							$tmp_td = '';
							$space ='&nbsp;';
							$split = explode('/',$SubSubAccount->line_age);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							
							$amount = (isset($accTrans[$SubSubAccount->code])?$accTrans[$SubSubAccount->code]:0);
							$subAccounts = getAccountByParent($SubSubAccount->code);
							if($subAccounts){
								$subAccount = getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date);
								$tmp_td = $subAccount['sub_td'];
								$amount += $subAccount['total_amount'];
							}else{
								$subAccount = array();
							}
							if($biller_multi && !$project_multi){
								foreach($biller_multi as $biller_id){
									$amount_biller = (isset($accTranBillers[$SubSubAccount->code][$biller_id])?$accTranBillers[$SubSubAccount->code][$biller_id]:0);
									$total_amount_billers[$biller_id] = $amount_biller + (isset($total_amount_billers[$biller_id])?$total_amount_billers[$biller_id]:0) + (isset($subAccount['total_amount_billers'][$biller_id])?$subAccount['total_amount_billers'][$biller_id]:0);
								}
							}else if($project_multi){
								foreach($project_multi as $project_id){
									$amount_project = (isset($accTranProjects[$SubSubAccount->code][$project_id])?$accTranProjects[$SubSubAccount->code][$project_id]:0);
									$total_amount_projects[$project_id] = $amount_project + (isset($total_amount_projects[$project_id])?$total_amount_projects[$project_id]:0) + (isset($subAccount['total_amount_projects'][$project_id])?$subAccount['total_amount_projects'][$project_id]:0);
								}
							}
							$total_amount += $amount;
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if($amount != 0){
									if($amount < 0){
										$v_amount = '( '.formatMoney(abs($amount)).' )';
									}else{
										$v_amount = formatMoney($amount);
									}
									$sub_td_biller = '';
									$sub_td_project = '';
									if($biller_multi && !$project_multi){								
										foreach($biller_multi as $biller_id){
											$amount_biller = (isset($accTranBillers[$SubSubAccount->code][$biller_id])?$accTranBillers[$SubSubAccount->code][$biller_id]:0)  + (isset($subAccount['total_amount_billers'][$biller_id])?$subAccount['total_amount_billers'][$biller_id]:0);
											if($amount_biller < 0){
												$v_amount_biller = '( '.formatMoney(abs($amount_biller)).' )';
											}else{
												$v_amount_biller = formatMoney($amount_biller);
											}
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right">'.$v_amount_biller.'</td>';
										}
									}else if($project_multi){
										foreach($project_multi as $project_id){
											$amount_project = (isset($accTranProjects[$SubSubAccount->code][$project_id])?$accTranProjects[$SubSubAccount->code][$project_id]:0)  + (isset($subAccount['total_amount_projects'][$project_id])?$subAccount['total_amount_projects'][$project_id]:0);
											if($amount_project < 0){
												$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
											}else{
												$v_amount_project = formatMoney($amount_project);
											}
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right">'.$v_amount_project.'</td>';
										}
									}
									$sub_td .= '<tr>
													<td>'.$space.$SubSubAccount->code.' - '.$SubSubAccount->name.'</td>
													'.$sub_td_biller.'
													'.$sub_td_project.'
													<td class="accounting_link" id="'.$SubSubAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount.'</td>
												</tr>';
								}
							}
							$sub_td .= $tmp_td;				
						}
						$data = array(
								'sub_td' => $sub_td,
								'total_amount' => $total_amount,
								'total_amount_billers' => $total_amount_billers,
								'total_amount_projects' => $total_amount_projects);
						return $data;
					}

					
				
					$tbody = '';
					$gross_profit = 0;
					$net_profit = 0;
					$gross_profit_billers = array();
					$net_profit_billers = array();
					$gross_profit_projects = array();
					$net_profit_projects = array();
					foreach($income_statements as $income_statement){
						$sections = $this->accountings_model->getAccountSectionsByCode(array($income_statement));	
						if($sections){
							foreach($sections as $section){
								$tbody .="<tr style='color:#067740; font-weight:bold'><td style='text-align:left' colspan='".$colspan_main."'><span>".$section->name."</span></td></tr>";
								$mainAccounts = $this->accountings_model->getMainAccountBySection($section->id);
								if($mainAccounts){
									$space ='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									foreach($mainAccounts as $mainAccount){
										$subAccounts = getAccountByParent($mainAccount->code);			
										$amount = (isset($accTrans[$mainAccount->code])?$accTrans[$mainAccount->code]:0);
										$tmp_td = '';
										if($subAccounts){
											$sub_acc = getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date);
											$tmp_td = $sub_acc['sub_td'];
											$amount += $sub_acc['total_amount'];
										}else{
											$sub_acc = array();
										}
										if($amount != 0){
											if($amount < 0){
												$v_amount = '( '.formatMoney(abs($amount)).' )';
											}else{
												$v_amount = formatMoney($amount);
											}
											$sub_td_biller = '';		
											$sub_td_project = '';
											if($biller_multi && !$project_multi){
													
												foreach($biller_multi as $biller_id){
													$amount_biller = (isset($accTranBillers[$mainAccount->code][$biller_id])?$accTranBillers[$mainAccount->code][$biller_id]:0);
													$amount_biller = $amount_biller + (isset($sub_acc['total_amount_billers'][$biller_id])?$sub_acc['total_amount_billers'][$biller_id]:0);
													if($amount_biller < 0){
														$v_amount_biller = '( '.formatMoney(abs($amount_biller)).' )';
													}else{
														$v_amount_biller = formatMoney($amount_biller);
													}
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller.'</td>';
													
													if($income_statement == 'RE'){
														$gross_profit_billers[$biller_id] = (isset($gross_profit_billers[$biller_id])?$gross_profit_billers[$biller_id]:0) + $amount_biller;
														$net_profit_billers[$biller_id] = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0) + $amount_biller;
													}else if($income_statement == 'OI'){
														$net_profit_billers[$biller_id] = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0) + $amount_biller;
													}else if($income_statement == 'CO'){
														$gross_profit_billers[$biller_id] = (isset($gross_profit_billers[$biller_id])?$gross_profit_billers[$biller_id]:0) - $amount_biller;
														$net_profit_billers[$biller_id] = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0) - $amount_biller;
													}else{
														$net_profit_billers[$biller_id] = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0) - $amount_biller;
													}

												}
											} else if($project_multi){
																						
												foreach($project_multi as $project_id){
													$amount_project = (isset($accTranProjects[$mainAccount->code][$project_id])?$accTranProjects[$mainAccount->code][$project_id]:0);
													$amount_project = $amount_project + (isset($sub_acc['total_amount_projects'][$project_id])?$sub_acc['total_amount_projects'][$project_id]:0);
													if($amount_project < 0){
														$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
													}else{
														$v_amount_project = formatMoney($amount_project);
													}
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project.'</td>';
													
													if($income_statement == 'RE'){
														$gross_profit_projects[$project_id] = (isset($gross_profit_projects[$project_id])?$gross_profit_projects[$project_id]:0) + $amount_project;
														$net_profit_projects[$project_id] = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0) + $amount_project;
													}else if($income_statement == 'OI'){
														$net_profit_projects[$project_id] = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0) + $amount_project;
													}else if($income_statement == 'CO'){
														$gross_profit_projects[$project_id] = (isset($gross_profit_projects[$project_id])?$gross_profit_projects[$project_id]:0) - $amount_project;
														$net_profit_projects[$project_id] = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0) - $amount_project;
													}else{
														$net_profit_projects[$project_id] = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0) - $amount_project;
													}
												}
											}
											$tbody .='<tr>
														<td style="font-weight:bold">'.$space.$mainAccount->code.' - '.$mainAccount->name.'</td>
														'.$sub_td_biller.'
														'.$sub_td_project.'
														<td class="accounting_link" id="'.$mainAccount->code.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right; font-weight:bold">'.$v_amount.'</td>
													</tr>';
										}
										if($income_statement == 'RE'){
											$gross_profit += $amount;	
											$net_profit += $amount;
										}else if($income_statement == 'OI'){
											$net_profit += $amount;
										}else if($income_statement == 'CO'){
											$gross_profit -= $amount;	
											$net_profit -= $amount;
										}else{
											$net_profit -= $amount;	
										}
										$tbody .= $tmp_td;		
									}
								}
							}
						}

						
						if($income_statement=='CO'){
							$td_gross_profit_biller = '';
							$td_gross_profit_project = '';
							if($biller_multi && !$project_multi){
								foreach($biller_multi as $biller_id){
									$gross_profit_biller = (isset($gross_profit_billers[$biller_id])?$gross_profit_billers[$biller_id]:0);
									if($gross_profit_biller < 0){
										$v_gross_profit_biller = '( '.formatMoney(abs($gross_profit_biller)).' )';
									}else{
										$v_gross_profit_biller = formatMoney($gross_profit_biller);
									}
									$td_gross_profit_biller .='<td style="text-align:right; font-weight:bold; color:#327bf7">'.$v_gross_profit_biller.'</td>';
								}
							}else if($project_multi){
								foreach($project_multi as $project_id){
									$gross_profit_project = (isset($gross_profit_projects[$project_id])?$gross_profit_projects[$project_id]:0);
									if($gross_profit_project < 0){
										$v_gross_profit_project = '( '.formatMoney(abs($gross_profit_project)).' )';
									}else{
										$v_gross_profit_project = formatMoney($gross_profit_project);
									}
									$td_gross_profit_project .='<td style="text-align:right; font-weight:bold; color:#327bf7">'.$v_gross_profit_project.'</td>';
								}
							}
							
							if($gross_profit < 0){
								$v_gross_profit = '( '.formatMoney(abs($gross_profit)).' )';
							}else{
								$v_gross_profit = formatMoney($gross_profit);
							}
							$tbody .='<tr>
										<td style="font-weight:bold; color:#327bf7">'.lang('gross_profit_loss').'</td>
										'.$td_gross_profit_biller.'
										'.$td_gross_profit_project.'
										<td style="text-align:right; font-weight:bold; color:#327bf7">'.$v_gross_profit.'</td>
									</tr>';		
						}
					}
					$td_net_profit_biller = '';
					$td_net_profit_project = '';
					if($biller_multi && !$project_multi){
						foreach($biller_multi as $biller_id){
							$net_profit_biller = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0);
							if($net_profit_biller < 0){
								$v_net_profit_biller = '( '.formatMoney(abs($net_profit_biller)).' )';
							}else{
								$v_net_profit_biller = formatMoney($net_profit_biller);
							}
							$td_net_profit_biller .='<td style="text-align:right; font-weight:bold; color:#327bf7">'.$v_net_profit_biller.'</td>';
						}
					}else if($project_multi){
						foreach($project_multi as $project_id){
							$net_profit_project = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0);
							if($net_profit_project < 0){
								$v_net_profit_project = '( '.formatMoney(abs($net_profit_project)).' )';
							}else{
								$v_net_profit_project = formatMoney($net_profit_project);
							}
							$td_net_profit_project .='<td style="text-align:right; font-weight:bold; color:#327bf7">'.$v_net_profit_project.'</td>';
						}
					}
					if($net_profit < 0){
						$v_net_profit= '( '.formatMoney(abs($net_profit)).' )';
					}else{
						$v_net_profit = formatMoney($net_profit);
					}
					$tbody .='<tr>
								<td style="font-weight:bold; color:#327bf7">'.lang('net_profit_loss').'</td>
								'.$td_net_profit_biller.'
								'.$td_net_profit_project.'
								<td style="text-align:right; font-weight:bold; color:#327bf7">'.$v_net_profit.'</td>
							</tr>';
				?>
				
                <div class="table-responsive">
                    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;" border="1" class="table table-bordered table-hover table-striped table-condensed accountings-table dataTable">
						<thead>
							<tr>
								<th rowspan="<?= $rowspan ?>"><?= lang('account'); ?></th>
								<?= $thead ?>
							</tr>
                        </thead>
						<tbody>
							<?= $tbody ?>
						</tbody>
                    </table>
                </div>
				
	<?php if(!$pdf){ ?>

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
			this.download = "income_statement.xls";
			return true;			
		});
		
		$("#pdf").click(function(e) {
			$("#pdf_report").trigger("click");
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

<?php } ?>