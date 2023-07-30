<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('general_ledger'); ?>
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

                    <?php echo form_open("accountings/general_ledger"); ?>
					
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
								<?= lang("account", "account"); ?>
								<select name="account" class="form-control select" id="account" style="width:100%">
									<option value=""><?= lang('select').' '.lang('account') ?></option>
									<?= $accounts ?>
								</select>
							</div>
						</div>
						
						<div class="col-sm-3">
							<div class="form-group">
								<label class="control-label" for="user"><?= lang("created_by"); ?></label>
								<?php
								$crb[""] = lang('select').' '.lang('user');
								foreach ($users as $user) {
									$crb[$user->id] = $user->first_name.' - '.$user->last_name;
								}
								echo form_dropdown('user', $crb, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control"  data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
								?>
							</div>
						</div>
						
						<div class="col-sm-3">
							<div class="form-group">
								<label class="control-label" for="customer"><?= lang("customer"); ?></label>
								<?php
								$ctm[""] = lang('select').' '.lang('customer');
								foreach ($customer as $cus) {
									$ctm[$cus->id] = $cus->company.' - '.$cus->name;
								}
								echo form_dropdown('customer', $ctm, (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"');
								?>
							</div>
						</div>
						
						<div class="col-sm-3">
							<div class="form-group">
								<label class="control-label" for="supplier"><?= lang("supplier"); ?></label>
								<?php
								$supp[""] = lang('select').' '.lang('supplier');
								foreach ($supplier as $sup) {
									$supp[$sup->id] = $sup->company.' - '.$sup->name;
								}
								echo form_dropdown('supplier', $supp, (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("supplier") . '"');
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

                <div class="table-responsive">
                    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;" border="1" class="table table-bordered table-hover table-striped table-condensed accountings-table dataTable">
						<thead>
							<tr>
								<th><?= lang('account'); ?></th>
								<th><?= lang('date'); ?></th>
								<th><?= lang('created_by'); ?></th>
								<th><?= lang('customer'); ?></th>
								<th><?= lang('supplier'); ?></th>
								<th><?= lang('reference'); ?></th>
								<th><?= lang('transaction'); ?></th>
								<th><?= lang('narrative'); ?></th>
								<th><?= lang('description'); ?></th>
								<th><?= lang('debit'); ?></th>
								<th><?= lang('credit'); ?></th>
								<th><?= lang('balance'); ?></th>
							</tr>
                        </thead>
						<tbody>
						<?php
							$getAccWithBegins = $this->accountings_model->getAccWithBegin();
							$html = '';
							if($getAccWithBegins){
								$getAccTrans = $this->accountings_model->getAccTrans();
								if($getAccTrans){
									$accTrans = array();
									foreach($getAccTrans as $getAccTran){
										$accTrans[$getAccTran['account']][] = $getAccTran;
									}
									
								}
															
								foreach($getAccWithBegins as $getAccWithBegin){
									if(($getAccWithBegin->amountBegin * $getAccWithBegin->nature) >= 0){
										$begin_balance = $this->cus->formatMoney(abs($getAccWithBegin->amountBegin));
									}else{
										$begin_balance = '('.$this->cus->formatMoney(abs($getAccWithBegin->amountBegin)).')';
									}
									$html .='<tr style="font-weight:bold; color:#39c65c">
												<td colspan="11">'.$getAccWithBegin->account.' - '.$getAccWithBegin->name.'</td>
												<td style="text-align:right">'.$begin_balance.'</td>
											</tr>';
											
									$accDetails = (isset($accTrans[$getAccWithBegin->account])?$accTrans[$getAccWithBegin->account]:false);
									if($accDetails){
										$balance = $getAccWithBegin->amountBegin;
										foreach($accDetails as $accDetail){
											$balance += $accDetail['amount'];
											if($accDetail['amount'] > 0){
												$debit = $this->cus->formatMoney($accDetail['amount']);
												$credit = '';
											}else{
												$debit = '';
												$credit = $this->cus->formatMoney(abs($accDetail['amount']));
											}
											if(($balance * $getAccWithBegin->nature) >= 0){
												$balance_show = $this->cus->formatMoney(abs($balance));
											}else{
												$balance_show = '('.$this->cus->formatMoney(abs($balance)).')';
											}
											
											$html .='<tr>
														<td></td>
														<td>'.$this->cus->hrld($accDetail["transaction_date"]).'</td>
														<td>'.$accDetail["username"].'</td>
														<td>'.$accDetail["customer"].'</td>
														<td>'.$accDetail["supplier"].'</td>
														<td>'.$accDetail["reference"].'</td>
														<td>'.$accDetail["transaction"].'</td>
														<td>'.$accDetail["narrative"].'</td>
														<td>'.$this->cus->remove_tag($accDetail["description"]).'</td>
														<td style="text-align:right">'.$debit.'</td>
														<td style="text-align:right">'.$credit.'</td>
														<td style="text-align:right">'.$balance_show.'</td>
													</tr>';
										}
									}
								}
							}
							
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
			this.download = "general_ledger.xls";
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