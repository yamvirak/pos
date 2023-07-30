<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    .dfTable th, .dfTable td {
        text-align: center;
        vertical-align: middle;
    }
    .dfTable td {
        padding: 2px;
    }

    .data tr:nth-child(odd) td {
        color: #2FA4E7;
    }

    .data tr:nth-child(even) td {
        text-align: right;
    }
</style>

 <?php echo form_open("reports/saleman_products_report", ' id="form-submit" '); ?>
 
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('saleman_products_report').' ('.(isset($sel_warehouse) ? $sel_warehouse->name : lang('all_warehouses')).')'; ?></h2>
		
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
                <p class="introtext"><?= lang("saleman_products_report") ?></p>
				
				<div id="form">
				
					<div class="row">
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
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
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="saleman"><?= lang("saleman"); ?></label>
                                <?php
                                $sm[""] = lang('select').' '.lang('saleman');
                                foreach ($salemans as $saleman) {
                                    $sm[$saleman->id] = $saleman->last_name . " " . $saleman->first_name;
                                }
                                echo form_dropdown('saleman', $sm, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("saleman") . '"');
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
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
						
					</div>
					
					
					<div class="form-group">
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("Search"), 'class="btn btn-primary"'); ?> 
						</div>
                    </div>
					
					<?php echo form_close(); ?>
					
				</div>
				
                <div class="table-responsive">
                    <table border=1 class="table table-bordered table-striped table-condensed dfTable reports-table">
                        <thead>
							<tr>
								<th width="3%" rowspan="2">
									<i class="fa fa-chevron-circle-down" aria-hidden="true"></i>
								</th>
								<th>
									<?= lang("saleman") ?>
									<i class="fa fa-angle-double-right" aria-hidden="true"></i>
									<?= lang("product") ?>
								</th>
								<th width=120><?= lang("sold") ?></th>	
								<?php if($Settings->foc == 1){ $colspan_foc = 1; ?>
									<th width=120><?= lang("foc") ?></th>	
								<?php } else { $colspan_foc = 0; } ?>
								<th width=120><?= lang("unit_price") ?></th>
								<th width=120><?= lang("discount") ?></th>
								<?php if($Admin || $Owner || $GP['products-cost']){ ?>
									<th width=120><?= lang("cost") ?></th>
								<?php } ?>
								<th width=120><?= lang("subtotal") ?></th>
								<th width=120><?= lang("net_profit") ?></th>
							</tr>
                        </thead>
                        <tbody>
							<?php 
								if (isset($sales) && $sales != false) {
									foreach($sales as $saleman){
										$saleman_details = $this->reports_model->getSalemanProductDetails($saleman->saleman_id);
										if($saleman_details){
											echo '<tr>
														<td colspan="'.(8 + $colspan_foc).'" class="bold left"><i class="fa fa-chevron-circle-right">&nbsp;</i>'.ucfirst($saleman->saleman).'</td>
												  </tr>';
											$subtotal = 0; $total_net_profit= 0;
											foreach($saleman_details as $k=> $saleman_detail){
												if($Admin || $Owner || $GP['products-cost']){
													$html_cost = '<td class="right">'.$this->cus->formatMoney($saleman_detail->cost).'</td>';
												}else{
													$html_cost = '';
												}
												if($Settings->foc == 1){
													$html_foc = '<td style="text-align:center">'.$this->cus->convertQty($saleman_detail->product_id, $saleman_detail->foc).'</td>';
												}else{
													$html_foc = '';
												}
												$net_profit = $saleman_detail->subtotal - ($saleman_detail->cost * $saleman_detail->quantity);
												$subtotal += $saleman_detail->subtotal;
												$total_net_profit += $net_profit;
												echo '<tr>
															<td>'.($k+1).'</td>
															<td style="text-align:left">'.$saleman_detail->product_name.'</td>
															<td style="text-align:center">'.$this->cus->convertQty($saleman_detail->product_id, $saleman_detail->quantity).'</td>
															'.$html_foc.'
															<td style="text-align:right">'.$this->cus->formatMoney($saleman_detail->unit_price).'</td>
															<td style="text-align:right">'.$this->cus->formatMoney($saleman_detail->discount).'</td>
															'.$html_cost.'
															<td style="text-align:right">'.$this->cus->formatMoney($saleman_detail->subtotal).'</td>
															<td style="text-align:right">'.$this->cus->formatMoney($net_profit).'</td>
														</tr>';
											}
											$col = 5;
											if($Admin || $Owner || $GP['products-cost']){
												$col += 1;
											}
						
											echo '<tr style="font-weight:bold;">
													   <td style="text-align:right !important" colspan="'.($col + $colspan_foc).'">'.lang("total").' : </td>
													   <td style="text-align:right !important">'.$this->cus->formatMoney($subtotal).'</td>
													   <td style="text-align:right !important">'.$this->cus->formatMoney($total_net_profit).'</td>
												  </tr>';	
										}
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
		
		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table{ white-space:wrap !important; } .table th, .table td{ font-size:10px !important; border:1px solid #000 !important; }</style>' + $('.table-responsive').html());
			this.href = result;
			this.download = "saleman_products_report.xls";
			return true;			
		});

		$('#form').hide();
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
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
					}else{
						
					}
				}
			})
		}
		
    });
</script>
