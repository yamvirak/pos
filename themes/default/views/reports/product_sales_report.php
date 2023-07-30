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

 <?php echo form_open("reports/product_sales_report", ' id="form-submit" '); ?>
 
<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('product_sales_report').' ('.(isset($sel_warehouse) ? $sel_warehouse->name : lang('all_warehouses')).')'; ?></h2> -->
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
                    <h2 class="blue"><i class="icon fa fa-calendar tip"></i><?= lang('product_sales_report'); ?></h2>
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
				
                <li class="dropdown hidden">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
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
                <!-- <p class="introtext"><?= lang("product_sales_report") ?></p> -->
				
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
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
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
                                    $sm[$saleman->id] = $saleman->first_name . " " . $saleman->last_name;
                                }
                                echo form_dropdown('saleman', $sm, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("saleman") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
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
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("Search"), 'class="btn btn-primary"'); ?> 
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
                                </div><br>

                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('monthly_product_sales_report_kh')?>
                                </div> 

                                <div class="bold">
                                        <?= lang('monthly_product_sales_report_en')?>
                                </div><br>
                               
                            </td> 
                        </tr>
                </table>
                <table class="header_report_side" border="0" style="margin-bottom:20px;">
                    <tr style="width: 30%!important;">
                        <?php
                            if ($this->input->post('start_date')) {
                                echo '<td style="width:25%;">'.lang('from_date').'</td><td style=width:10px;>:</td><td class="header_report">'.$this->input->post('start_date') . " to " . $this->input->post('end_date').'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('from_date').'</td><td style=width:10px;>:</td><td class="header_report">'. $this->cus->hrsd(date("Y-m-d")) . " to " . $this->cus->hrsd(date("Y-m-d")).'</td>';
                            }
                        ?>           
                    </tr>
                    <tr style="width: 30%!important;">
                        <?php 
                            $category = (isset($_POST['category']) ? $_POST['category'] : false);
                            $category_all = lang('all_selected');
                            $category_detail = $this->site->getCategoryByID($category);
                            if($category_detail){
                                echo '<td style="width:25%;">'.lang('category_name').'</td><td style=width:10px;>:</td><td class="header_report">'.$category_detail->name.'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('category_name').'</td style=width:10px;><td>:</td><td class="header_report">'.$category_all.'</td>';
                                        }
                           
                        ?>
                    </tr>
                    <tr style="width: 30%!important;">
                        <?php 
                            $warehouse = (isset($_POST['warehouse']) ? $_POST['warehouse'] : false);
                            $warehouse_all = lang('all_selected');
                            $warehouse_detail = $this->site->getWarehouseByID($warehouse);
                            if($warehouse_detail){
                                echo '<td style="width:25%;">'.lang('warehouse_name').'</td><td style=width:10px;>:</td><td class="header_report">'.$warehouse_detail->name.'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('warehouse_name').'</td style=width:10px;><td>:</td><td class="header_report">'.$warehouse_all.'</td>';
                                        }
                           
                        ?>
                    </tr>
                    <tr style="width: 30%!important;">
                        <?php 
                            $project = (isset($_POST['project']) ? $_POST['project'] : false);
                            $project_all = lang('all_selected');
                            $project_detail = $this->site->getProjectByID($project);
                            if($project_detail){
                                echo '<td style="width:25%;">'.lang('project_name').'</td><td style=width:10px;>:</td><td class="header_report">'.$project_detail->name.'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('project_name').'</td style=width:10px;><td>:</td><td class="header_report">'.$project_all.'</td>';
                                        }
                           
                        ?>
                    </tr>
                    
                </table>
                <div class="clearfix"></div>
				
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-condensed dfTable reports-table">
                        <thead>
							<tr>
								<th width="3%" rowspan="2">
									<i class="fa fa-chevron-circle-down" aria-hidden="true"></i>
								</th>
								<th>
									<?= lang("warehouse") ?>
									<i class="fa fa-angle-double-right" aria-hidden="true"></i>
									<?= lang("category") ?>
									<i class="fa fa-angle-double-right" aria-hidden="true"></i>
									<?= lang("product") ?>
								</th>
								<th width=120><?= lang("product_type") ?></th>
								<th width=120><?= lang("sold") ?></th>	
								<th width=120><?= lang("unit_price") ?></th>
								<th width=120><?= lang("discount") ?></th>
								<?php if($Admin || $Owner || $GP['products-cost']){ ?>
									<th width=120><?= lang("total_cost") ?></th>
								<?php } ?>
								<th width=120><?= lang("total_price") ?></th>
								<th width=120><?= lang("net_profit") ?></th>
							</tr>
                        </thead>
                        <tbody>
							
							<?php
							
							$date = date("Y-m-d");								
							$product = $this->input->post("product");
							$category = $this->input->post("category");
							$warehouse_id = $this->input->post("warehouse");
							$start_date = $this->input->post("start_date");
							$end_date = $this->input->post("end_date");
							$saleman = $this->input->post("saleman");
							$biller = $this->input->post("biller");
							$customer = $this->input->post("customer");
							$project = $this->input->post("project");
							
							$total_quantity 	= 0;
							$grand_total 		= 0; 
							$total_net_profit 	= 0; 
							$total_cost 		= 0; 
							$total_discount 	= 0; 
							$total_sold 		= 0;
							$total_unit_price 	= 0;
							
							foreach($result_categories as $result_category){
								$product_sales = $this->reports_model->getProductBySales($result_category->id, $start_date, $end_date, $product, $warehouse_id, $saleman, $biller, $project, $customer);
								$subtotal = 0; 
								$net_profit = 0; 
								$cost = 0;
								$discount = 0; 
								$unit_price = 0;
								if($product_sales){
								?>
									<tr>
										<td colspan="9" class="bold left">
											<i class="	fa fa-chevron-circle-right"></i>
											<?= $result_category->name ?>
										</td>
									</tr>
									<?php 
									foreach($product_sales as $i => $product_sale){
										$unit = $this->site->getProductUnit($product_sale->product_id,$product_sale->product_unit_id);
										$total_quantity += $product_sale->quantity;
										$subtotal 	+= $product_sale->subtotal;
										$net_profit += ($product_sale->subtotal - $product_sale->cost);
										$cost 		+= $product_sale->cost;
										$discount 	+= $product_sale->item_discount;
										$unit_price += isset($product_sale->unit_price)? $product_sale->unit_price: 0;
										$row_body = '';
										if($product_sale->product_type =='bom'){
											$raw_datas = array();
											$raw_split = explode('#',$product_sale->raw_materials);
											if($raw_split){
												foreach($raw_split as $raw_row){
													$raw_meterial = json_decode($raw_row);
													if($raw_meterial){
														foreach($raw_meterial as $row){
															$raw_datas[$row->product_id] = $raw_datas[$row->product_id] + $row->quantity;
														}
													}
												}
											}

											if($raw_datas){
												foreach($raw_datas as $key => $raw_data){
													$raw_info = $this->reports_model->getProductById($key);
													$row_body .= '<tr>
																	<td></td>
																	<td class="right">'.$raw_info->name.'</td>
																	<td>'.lang($raw_info->type).'</td>
																	<td>[ '.$raw_data.' ]</td>
																	<td colspan="4"></td>
																</tr>';
												}
											}
										}
										
										$cstyle = ""; 
										if($product_sale->unit_price==0){
											$cstyle = " style='color:red; font-weight:bold; text-decoration:underline; ' ";
										}
									?>
										<tr <?=$cstyle?> >
											<td><?= ($i+1) ?></td>
											<td class="left"><?= ucfirst($product_sale->product_name); ?></td>
											<td><?= $product_sale->product_type; ?></td>
											<td class="right"><?= $this->cus->convertQty($product_sale->product_id, $product_sale->quantity); ?></td>	
											<td class="right"><?= $this->cus->formatMoney($product_sale->unit_price); ?></td>											
											<td class="right"><?= $this->cus->formatMoney($product_sale->item_discount) ?></td>
											<?php if($Admin || $Owner || $GP['products-cost']){ ?>
												<td class="right"><?= $this->cus->formatMoney($product_sale->cost); ?></td>
											<?php } ?>
											<td class="right"><?= $this->cus->formatMoney($product_sale->subtotal); ?></td>
											<td class="right"><?= $this->cus->formatMoney($product_sale->subtotal - $product_sale->cost); ?></td>
										</tr>	
										<?= $row_body ?>
									<?php 
									}
									$grand_total += $subtotal;
									$total_net_profit += $net_profit;
									$total_cost += $cost;
									$total_discount += $discount;
									$total_unit_price += $unit_price;
								?>
									<tr class="bold" style="color:#357EBD">
										<td colspan="4"></td>
										<td class="right"><?= $this->cus->formatMoney($unit_price); ?></td>
										<td class="right"><?= $this->cus->formatMoney($discount); ?></td>
										<?php if($Admin || $Owner || $GP['products-cost']){ ?>
											<td class="right"><?= $this->cus->formatMoney($cost); ?></td>
										<?php } ?>
										<td class="right"><?= $this->cus->formatMoney($subtotal); ?></td>
										<td class="right"><?= $this->cus->formatMoney($net_profit); ?></td>
									</tr>
							<?php 	
								} 						
							}
						?>
							<tr class="bold">
								<td colspan="3" class="right" style="vertical-align: top !important;"><?= lang("total") ?></td>
								<td class="right"><?= $this->cus->formatMoney($total_quantity); ?></td>
								<td class="right"><?= $this->cus->formatMoney($total_unit_price); ?></td>
								<td class="right"><?= $this->cus->formatMoney($total_discount); ?></td>
								<?php if($Admin || $Owner || $GP['products-cost']){ ?>
									<td class="right"><?= $this->cus->formatMoney($total_cost); ?></td>
								<?php } ?>
								<td class="right"><?= $this->cus->formatMoney($grand_total); ?></td>
								<td class="right"><?= $this->cus->formatMoney($total_net_profit); ?></td>
							</tr>
                        </tbody>
                    </table>
                    <div class="clearfix"></div>
   	<div style="margin-top: 50px !important;"></div>
        <table width="100%" style="text-align:center;"> 
          <tbody>
            <tr class="tr_print">
                <td>
                    <table style="margin-top:<?= $margin_signature ?>px; margin-bottom:<?= $margin_signature -20 ?>px;">
                        <thead class="footer_item">
                            <th class="text_center"><?= lang("prepared_by");?></th>
                            <th class="text_center"><?= lang("checked_by");?></th>
                            <th class="text_center"><?= lang("approved_by");?></th>
                            <th class="text_center"><?= lang("acknowledgement_by") ?></th>
                        </thead>
                        <tbody class="footer_item_body">
                            <td class="footer_item_body"></td>
                            <td class="footer_item_body"></td>
                            <td class="footer_item_body"></td>
                            <td class="footer_item_body"></td>
                        </tbody>

                        <thead class="footer_item_footer">
                            <th class="footer_item_footer text_left">
                                <div class="footer_name"><?= lang('name_date')?></div>
                            </th>
                            <th class="footer_item_footer text_left">
                            <div class="footer_name"><?= lang('name_date')?></div>
                            </th>
                            <th class="footer_item_footer text_left">
                                <div class="footer_name"><?= lang('name_date')?></div>
                            </th>
                            <th class="footer_item_footer text_left">
                                <div class="footer_name"><?= lang('name_date')?></div>
                                        
                            </th>
                        </thead>
                    </table>
                </td>
                </tr>
            </tbody>
       </table>
       	<!-- <div class="no-print">

                <div class="col-md-3 col-xs-3 padding1010">
                    <a class="bmGreen white quick-button tip" href="<?= site_url('reports/profit_loss') ?>" data-original-title="<?= lang('QUANTITY SOLD OUT') ?>">
                        <p class="report_title"><?= lang('QUANTITY SOLD OUT') ?></p>
                        <i class="fa fa-line-chart"></i>
                        <p class="report_amount"><?php echo $total_quantity;?></p>
                    </a>
                </div>
                <div class="col-md-3 col-xs-3 padding1010">
                    <a class="bdarkGreen white quick-button tip" href="<?= site_url('reports/profit_loss') ?>" data-original-title="<?= lang('TOTAL COST') ?>">
                        <p class="report_title"><?= lang('TOTAL COST') ?></p>
                         <i class="fa fa-money"></i>
                        <p class="report_amount"><?= $this->cus->formatMoney($total_cost); ?></p>
                    </a>
                </div>
                <div class="col-md-3 col-xs-3 padding1010">
                    <a class="borange white quick-button tip" href="<?= site_url('reports/profit_loss') ?>" data-original-title="<?= lang('TOTAL PRICE') ?>">
                        <p class="report_title"><?= lang('TOTAL PRICE') ?></p>
                        <i class="fa fa-money"></i>
                        <p class="report_amount"><?= $this->cus->formatMoney($grand_total); ?></p>
                    </a>
                </div>
                <div class="col-md-3 col-xs-3 padding1010">
                    <a class="bblue white quick-button tip" href="<?= site_url('reports/profit_loss') ?>" data-original-title="<?= lang('NET PROFIT') ?>">
                        <p class="report_title"><?= lang('NET PROFIT') ?></p>
                       
                        <i class="fa fa-line-chart"></i>
                        <p class="report_amount"><?= $this->cus->formatMoney($total_net_profit); ?></p>
                    </a>
                </div>
            </div>
            </div>
        </div> -->
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
    
         $('#pdf').click(function (event) {
            event.preventDefault();
			$("#form-submit").append("<input type='hidden' name='pdf' value=1 />")
			$("#form-submit").submit();
            return false;
        });

		$("#xls").click(function(e) {
			event.preventDefault();
			$("#form-submit").append("<input type='hidden' name='xls' value=1 />")
			$("#form-submit").submit();
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
			var project = "<?= trim($_POST['project']); ?>";
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

