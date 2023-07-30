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
		
		$('#IVLData').dataTable({
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            "oTableTools": {
                "sSwfPath": "assets/media/swf/copy_csv_xls_pdf.swf",
                "aButtons": ["csv", {"sExtends": "pdf", "sPdfOrientation": "landscape", "sPdfMessage": ""}, "print"]
            }
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('sort')?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('code')?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('name')?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('transaction');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('reference')?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('quantity')?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('unit')?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('unit_cost')?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('qoh')?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('average_cost')?>]", filter_type: "text", data: []},
        ], "footer");
    });
	
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('inventory_valuation_report'); ?><?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
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
                    <?php echo form_open("reports/inventory_valuation_report"); ?>
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
                                <label class="control-label" for="warehouse"><?= lang("warehouse") ?></label>
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
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : $this->cus->hrld(date('Y-m-d H:i'))), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="IVLData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
							<tr>
								<th class='hidden'><?= lang("sort"); ?></th>
								<th><?= lang("code"); ?></th>
								<th><?= lang("name"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("transaction"); ?></th>
								<th><?= lang("reference"); ?></th>
								<th><?= lang("quantity"); ?></th>
								<th><?= lang("unit"); ?></th>
								<th><?= lang("unit_cost"); ?></th>
								<th><?= lang("qoh"); ?></th>
								<th><?= lang("average_cost"); ?></th>
								<th><?= lang("stock_value"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<?php
								$tbody = "";
								if($products){
									$product_value = 0;
									foreach($products as $product){
										$old_qty = 0;
										$old_cost = 0;
										$stock_value = 0;
										$total_qoh = 0;
										$total_cost = 0;
										$tbody.="<tr class='product_link3' id='".$product->id."'>
													<td class='hidden'><b>".$product->code."</b></td>
													<td><b>".$product->code."</b></td>
													<td><b>".$product->name."</b></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
												</tr>";
										
										if(isset($stockmoves[$product->id])){
											foreach($stockmoves[$product->id] as $stockmove){
												if($stockmove->transaction=='CostAdjument' || $stockmove->transaction=='OpeningBalance' || $stockmove->transaction=='Pawns' || $stockmove->transaction=='Purchases' || $stockmove->transaction=='Receives' || ($stockmove->transaction=='QuantityAdjustment' && $stockmove->quantity > 0) || ($stockmove->transaction=='Convert' && $stockmove->quantity > 0)){	
													$new_cost = $stockmove->real_unit_cost;
													$new_qty = $stockmove->quantity;
													$total_qty = $new_qty + $old_qty;
													if($old_qty >= 0){
														$total_old_cost = $old_qty * $old_cost;
														$total_new_cost = $new_qty * $new_cost; 
														$old_cost = ($total_old_cost + $total_new_cost) / $total_qty;
													}else{
														if($total_qty > 0){
															$old_cost = $new_cost;
														}else{
															$old_cost = $product->cost;
														}
													}
												}else{
													$old_cost = $stockmove->real_unit_cost;
												}
												$old_qty += $stockmove->quantity;
												$qoh = $old_qty;
												$average_cost = $old_cost;
												$stock_value = $old_cost * $old_qty;
												$total_qoh = $qoh;
												$total_cost = $average_cost;
												$tbody.="<tr>
															<td class='hidden'>".$product->code."</td>
															<td></td>
															<td></td>
															<td>".$this->cus->hrld($stockmove->date)."</td>
															<td>".$stockmove->transaction."</td>
															<td>".$stockmove->reference_no."</td>
															<td class='text-right'>".$this->cus->formatQuantity($stockmove->quantity)."</td>
															<td>".$stockmove->unit_name."</td>
															<td class='text-right'>".$this->cus->formatMoney($stockmove->real_unit_cost * ($stockmove->unit_quantity > 0 ? $stockmove->unit_quantity : 1))."</td>
															<td class='text-right'>".$this->cus->formatQuantity($qoh)."</td>
															<td class='text-right'>".$this->cus->formatMoney($average_cost)."</td>
															<td class='text-right'>".$this->cus->formatMoney($stock_value)."</td>
														</tr>";
											}
										}
										$tbody.="<tr class='product_link' id='".$product->id."'>
													<td class='hidden'>".$product->code."</td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td class='text-right'><b>".$product->name."</b></td>
													<td class='text-right'>".$this->cus->formatQuantity($total_qoh)."</td>
													<td class='text-right'>".$this->cus->formatMoney($total_cost)."</td>
													<td class='text-right'>".$this->cus->formatMoney($stock_value)."</td>
												</tr>";
										$product_value += $stock_value;
									}
									echo $tbody;
								}
							?>
                        </tbody>
						<tfoot class="dtFilter">
							<tr class="active">
								<th class='hidden'></th>
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
								<th class="text-right"><b><?= $this->cus->formatMoney($product_value) ?></b></th>
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
            window.location.href = "<?=site_url('reports/inventory_valuation_report_action/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/inventory_valuation_report_action/0/xls/?v=1'.$v)?>";
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
    });
</script>
