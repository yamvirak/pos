<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .table td:first-child {
        font-weight: bold;
    }

    label {
        margin-right: 10px;
    }
	table{
		white-space: normal !important;
	}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('group_permissions'); ?> (<?= $group->name ?>)</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang("set_permissions"); ?></p>

                <?php if (!empty($p)) {
                    if ($p->group_id != 1) {

                        echo form_open("system_settings/permissions/" . $id); ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
									<tr>
										<th colspan="6"
											style="font-size: 14px; text-align:left;"><?= lang("inventory_permissions");?></th>
									</tr>
									<tr>
										<th rowspan="2" class="text-center"><?= lang("module_name"); ?>
										</th>
										<th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
									</tr>
									<tr>
										<th class="text-center"><?= lang("view"); ?></th>
										<th class="text-center"><?= lang("add"); ?></th>
										<th class="text-center"><?= lang("edit"); ?></th>
										<th class="text-center"><?= lang("delete"); ?></th>
										<th class="text-center"><?= lang("misc"); ?></th>
									</tr>
                                </thead>

								<tbody>
									<?php if($this->config->item('inventory')) { ?>
										<tr>
											<td><?= lang("products"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="products-index" <?php echo $p->{'products-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="products-add" <?php echo $p->{'products-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="products-edit" <?php echo $p->{'products-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="products-delete" <?php echo $p->{'products-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
										<div class="container-fluid">
											<div class="col-md-6">  
												<input type="checkbox" value="1" id="products-import" class="checkbox" name="products-import" <?php echo $p->{'products-import'} ? "checked" : ''; ?>>
												<label for="products-import" class="padding05"><?= lang('import_product') ?></label>
											</div>
											<div class="col-md-6">  
												<input type="checkbox" value="1" id="products-cost" class="checkbox" name="products-cost" <?php echo $p->{'products-cost'} ? "checked" : ''; ?>>
												<label for="products-cost" class="padding05"><?= lang('product_cost') ?></label>
											</div>
											<div class="col-md-6">  
												<input type="checkbox" value="1" id="products-price" class="checkbox" name="products-price" <?php echo $p->{'products-price'} ? "checked" : ''; ?>>
												<label for="products-price" class="padding05"><?= lang('product_price') ?> </label>
											</div>
											<div class="col-md-6">
												<input type="checkbox" value="1" id="products-adjustments" class="checkbox" name="products-adjustments" <?php echo $p->{'products-adjustments'} ? "checked" : ''; ?>>
												<label for="products-adjustments" class="padding05"><?= lang('adjustments') ?></label>
											</div>
											<div class="col-md-6">
												<?php if($Settings->accounting==1){ ?>
													<input type="checkbox" value="1" id="products-cost_adjustments" class="checkbox" name="products-cost_adjustments" <?php echo $p->{'products-cost_adjustments'} ? "checked" : ''; ?>>
													<label for="products-cost_adjustments" class="padding05"><?= lang('cost_adjustments') ?></label>
												<?php } ?>
											</div>
											<div class="col-md-6">
												<input type="checkbox" value="1" id="products-barcode" class="checkbox" name="products-barcode" <?php echo $p->{'products-barcode'} ? "checked" : ''; ?>>
												<label for="products-barcode" class="padding05"><?= lang('print_barcodes') ?></label>
											</div>
											<div class="col-md-6">
												<input type="checkbox" value="1" id="products-serial" class="checkbox" name="products-serial" <?php echo $p->{'products-serial'} ? "checked" : ''; ?>>
												<label for="products-serial" class="padding05"><?= lang('serial') ?></label>
												<?php if($this->config->item('using_stocks')) {?>
											</div>
											<div class="col-md-6">
												<input type="checkbox" value="1" id="products-using_stocks" class="checkbox" name="products-using_stocks" <?php echo $p->{'products-using_stocks'} ? "checked" : ''; ?>>
												<label for="products-using_stocks" class="padding05"><?= lang('using_stocks') ?></label>
											<?php } if($this->config->item('stock_counts')) {?>
											</div>
											<div class="col-md-6">
												<input type="checkbox" value="1" id="products-stock_count" class="checkbox" name="products-stock_count" <?php echo $p->{'products-stock_count'} ? "checked" : ''; ?>>
												<label for="products-stock_count" class="padding05"><?= lang('stock_counts') ?></label>
											<?php } if($this->config->item('convert')){ ?>
											</div>
											<div class="col-md-6">
													<input type="checkbox" value="1" id="products-convert" class="checkbox" name="products-convert" <?php echo $p->{'products-convert'} ? "checked" : ''; ?>>
													<label for="products-convert" class="padding05"><?= lang('converts') ?></label>
												<?php } ?>
											</div>
											<div class="col-md-6">
												<input type="checkbox" value="1" id="products-date" class="checkbox" name="products-date" <?php echo $p->{'products-date'} ? "checked" : ''; ?>>
												<label for="products-date" class="padding05"><?= lang('date') ?></label>
											</div>
										</td>
										</tr>
										<?php if($this->config->item('using_stocks')) {?>	
											<tr>
												<td><?= lang("using_stocks"); ?></td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-using_stocks" <?php echo $p->{'products-using_stocks'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-using_stocks-add" <?php echo $p->{'products-using_stocks-add'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-using_stocks-edit" <?php echo $p->{'products-using_stocks-edit'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-using_stocks-delete" <?php echo $p->{'products-using_stocks-delete'} ? "checked" : ''; ?>>
												</td>
												<td>
													<div class="container-fluid">
														<div class="col-md-6">
															<input type="checkbox" value="1" id="products-using_stocks-date" class="checkbox" name="products-using_stocks-date" <?php echo $p->{'products-using_stocks-date'} ? "checked" : ''; ?>>
															<label for="products-using_stocks-date" class="padding05"><?= lang('date') ?></label>
														</div>
													</div>
												</td>
											</tr>
										<?php } ?>	
											<tr>
												<td><?= lang("adjustments"); ?></td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-adjustments" <?php echo $p->{'products-adjustments'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-adjustments-add" <?php echo $p->{'products-adjustments-add'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-adjustments-edit" <?php echo $p->{'products-adjustments-edit'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-adjustments-delete" <?php echo $p->{'products-adjustments-delete'} ? "checked" : ''; ?>>
												</td>
												<td>
												<div class="container-fluid">
													<div class="col-md-6">
														<input type="checkbox" value="1" id="products-approve_adjustment" class="checkbox" name="products-approve_adjustment" <?php echo $p->{'products-approve_adjustment'} ? "checked" : ''; ?>>
														<label for="products-approve_adjustment" class="padding05"><?= lang('approve_adjustment') ?></label>
													</div>
													<div class="col-md-6">
														<input type="checkbox" value="1" id="products-adjustments-date" class="checkbox" name="products-adjustments-date" <?php echo $p->{'products-adjustments-date'} ? "checked" : ''; ?>>
														<label for="products-adjustments-date" class="padding05"><?= lang('date') ?></label>
													</div>
												</div>
												</td>
											</tr>
										<?php  if($this->config->item('convert')) {?>
											<tr>
												<td><?= lang("converts"); ?></td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-converts" <?php echo $p->{'products-converts'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-converts-add" <?php echo $p->{'products-converts-add'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-converts-edit" <?php echo $p->{'products-converts-edit'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-converts-delete" <?php echo $p->{'products-converts-delete'} ? "checked" : ''; ?>>
												</td>
												<td>
												<div class="container-fluid">
													<div class="col-md-6">
														<input type="checkbox" value="1" id="products-converts-date" class="checkbox" name="products-converts-date" <?php echo $p->{'products-converts-date'} ? "checked" : ''; ?>>
														<label for="products-converts-date" class="padding05"><?= lang('date') ?></label>
													</div>
												</div>
												</td>
											</tr>	
										<?php } if($Settings->accounting==1){ ?>
											<tr>
												<td><?= lang("cost_adjustments"); ?></td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-cost_adjustments" <?php echo $p->{'products-cost_adjustments'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-cost_adjustments-add" <?php echo $p->{'products-cost_adjustments-add'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-cost_adjustments-edit" <?php echo $p->{'products-cost_adjustments-edit'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-cost_adjustments-delete" <?php echo $p->{'products-cost_adjustments-delete'} ? "checked" : ''; ?>>
												</td>
												<td>
												<div class="container-fluid">
													<div class="col-md-6">
														<input type="checkbox" value="1" id="products-cost_adjustments-date" class="checkbox" name="products-cost_adjustments-date" <?php echo $p->{'products-cost_adjustments-date'} ? "checked" : ''; ?>>
														<label for="products-cost_adjustments-date" class="padding05"><?= lang('date') ?></label>
													</div>
												</div>
												</td>
											</tr>
										<?php } if(!$this->config->item('one_warehouse')){ ?>
											<tr>
												<td><?= lang("transfers"); ?></td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="transfers-index" <?php echo $p->{'transfers-index'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="transfers-add" <?php echo $p->{'transfers-add'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="transfers-edit" <?php echo $p->{'transfers-edit'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="transfers-delete" <?php echo $p->{'transfers-delete'} ? "checked" : ''; ?>>
												</td>
												<td>
												<div class="container-fluid">
													<div class="col-md-6">
														<input type="checkbox" value="1" id="transfers-email" class="checkbox" name="transfers-email" <?php echo $p->{'transfers-email'} ? "checked" : ''; ?>>
														<label for="transfers-email" class="padding05"><?= lang('email') ?></label>
													</div>
													<div class="col-md-6">
														<input type="checkbox" value="1" id="transfers-pdf" class="checkbox" name="transfers-pdf" <?php echo $p->{'transfers-pdf'} ? "checked" : ''; ?>>
														<label for="transfers-pdf" class="padding05"><?= lang('pdf') ?></label>
													</div>
													<div class="col-md-6">
														<input type="checkbox" value="1" id="transfers-date" class="checkbox" name="transfers-date" <?php echo $p->{'transfers-date'} ? "checked" : ''; ?>>
														<label for="transfers-date" class="padding05"><?= lang('date') ?></label>
													</div>
												</div>
												</td>
											</tr>
										<?php } if($this->config->item('consignments')){ ?>	
											<tr>
												<td><?= lang("consignments"); ?></td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-consignments" <?php echo $p->{'products-consignments'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-add_consignment" <?php echo $p->{'products-add_consignment'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-edit_consignment" <?php echo $p->{'products-edit_consignment'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="products-delete_consignment" <?php echo $p->{'products-delete_consignment'} ? "checked" : ''; ?>>
												</td>
												<td>
												<div class="container-fluid">
													<div class="col-md-6">
														<input type="checkbox" value="1" id="consignments-date" class="checkbox" name="consignments-date" <?php echo $p->{'consignments-date'} ? "checked" : ''; ?>>
														<label for="consignments-date" class="padding05"><?= lang('date') ?></label>
													</div>
													<div class="col-md-6">
														<input type="checkbox" value="1" id="reports-consignments" class="checkbox" name="reports-consignments" <?php echo $p->{'reports-consignments'} ? "checked" : ''; ?>>
														<label for="reports-consignments" class="padding05"><?= lang('consignments_report') ?></label>
													</div>
													<div class="col-md-6">
														<input type="checkbox" value="1" id="reports-consignment_details" class="checkbox" name="reports-consignment_details" <?php echo $p->{'reports-consignment_details'} ? "checked" : ''; ?>>
														<label for="reports-consignment_details" class="padding05"><?= lang('consignment_details_report') ?></label>
													</div>
												</div>
												</td>

											</tr>
										<?php } ?>
									
									<?php } ?>
                                </tbody>
                            </table>

							<table class="table table-bordered table-hover table-striped">
								<thead>
									<tr>
										<th colspan="6"
											style="font-size: 14px; text-align:left;"><?= lang("sale_permissions");?></th>
									</tr>
									<tr>
										<th rowspan="2" class="text-center"><?= lang("module_name"); ?>
										</th>
										<th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
									</tr>
									<tr>
										<th class="text-center"><?= lang("view"); ?></th>
										<th class="text-center"><?= lang("add"); ?></th>
										<th class="text-center"><?= lang("edit"); ?></th>
										<th class="text-center"><?= lang("delete"); ?></th>
										<th class="text-center"><?= lang("misc"); ?></th>
									</tr>
								</thead>

								<tbody>

									<?php if($this->config->item('quotation')){ ?>
										<tr>
											<td><?= lang("quotes"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="quotes-index" <?php echo $p->{'quotes-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="quotes-add" <?php echo $p->{'quotes-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="quotes-edit" <?php echo $p->{'quotes-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="quotes-delete" <?php echo $p->{'quotes-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="quotes-email" class="checkbox" name="quotes-email" <?php echo $p->{'quotes-email'} ? "checked" : ''; ?>>
													<label for="quotes-email" class="padding05"><?= lang('email') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="quotes-pdf" class="checkbox" name="quotes-pdf" <?php echo $p->{'quotes-pdf'} ? "checked" : ''; ?>>
													<label for="quotes-pdf" class="padding05"><?= lang('pdf') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="quotes-date" class="checkbox" name="quotes-date" <?php echo $p->{'quotes-date'} ? "checked" : ''; ?>>
													<label for="quotes-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</td>
										</tr>
									<?php } if($this->config->item('saleorder')){ ?>
										<tr>
											<td><?= lang("sale_orders"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sale_orders-index" <?php echo $p->{'sale_orders-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sale_orders-add" <?php echo $p->{'sale_orders-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sale_orders-edit" <?php echo $p->{'sale_orders-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sale_orders-delete" <?php echo $p->{'sale_orders-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="approve_sale_orders" class="checkbox" name="approve_sale_orders" <?php echo $p->{'approve_sale_orders'} ? "checked" : ''; ?>>
													<label for="approve_sale_orders" class="padding05"><?= lang('approve_sale_orders') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sale_orders-pdf" class="checkbox" name="sale_orders-pdf" <?php echo $p->{'sale_orders-pdf'} ? "checked" : ''; ?>>
													<label for="sale_orders-pdf" class="padding05"><?= lang('pdf') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sale_orders-date" class="checkbox" name="sale_orders-date" <?php echo $p->{'sale_orders-date'} ? "checked" : ''; ?>>
													<label for="sale_orders-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									<?php } if($this->config->item('sale')) { ?>
										<tr>
											<td><?= lang("sales"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-index" <?php echo $p->{'sales-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-add" <?php echo $p->{'sales-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-edit" <?php echo $p->{'sales-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-delete" <?php echo $p->{'sales-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sales-import_sale" class="checkbox" name="sales-import_sale" <?php echo $p->{'sales-import_sale'} ? "checked" : ''; ?>>
													<label for="sales-import_sale" class="padding05"><?= lang('import_sale') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sales-email" class="checkbox" name="sales-email" <?php echo $p->{'sales-email'} ? "checked" : ''; ?>>
													<label for="sales-email" class="padding05"><?= lang('email') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf" <?php echo $p->{'sales-pdf'} ? "checked" : ''; ?>>
													<label for="sales-pdf" class="padding05"><?= lang('pdf') ?></label>
												</div>
												<div class="col-md-6"> 
													<?php if (POS) { ?>
														<input type="checkbox" value="1" id="pos-index" class="checkbox" name="pos-index" <?php echo $p->{'pos-index'} ? "checked" : ''; ?>>
														<label for="pos-index" class="padding05"><?= lang('pos') ?></label>
													<?php } ?>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sales-payments" class="checkbox" name="sales-payments" <?php echo $p->{'sales-payments'} ? "checked" : ''; ?>>
													<label for="sales-payments" class="padding05"><?= lang('payments') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="sales-return_sales" class="checkbox" name="sales-return_sales" <?php echo $p->{'sales-return_sales'} ? "checked" : ''; ?>>
													<label for="sales-return_sales" class="padding05"><?= lang('return_sales') ?></label>
												</div>
												<div class="col-md-6">
													<?php if($this->config->item('saleman')){ ?>
														<input type="checkbox" value="1" id="sales-assign_sales" class="checkbox" name="sales-assign_sales" <?php echo $p->{'sales-assign_sales'} ? "checked" : ''; ?>>
														<label for="sales-assign_sales" class="padding05"><?= lang('saleman') ?></label>
													<?php } ?>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sales-date" class="checkbox" name="sales-date" <?php echo $p->{'sales-date'} ? "checked" : ''; ?>>
													<label for="sales-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									
									<?php } ?>
									
									<?php if($this->config->item('fuel')){ ?>
										<tr>
											<td><?= lang("fuel_sale"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-fuel_sale-index" <?php echo $p->{'sales-fuel_sale-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-fuel_sale-add" <?php echo $p->{'sales-fuel_sale-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-fuel_sale-edit" <?php echo $p->{'sales-fuel_sale-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-fuel_sale-delete" <?php echo $p->{'sales-fuel_sale-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sales-fuel_sale-date" class="checkbox" name="sales-fuel_sale-date" <?php echo $p->{'sales-fuel_sale-date'} ? "checked" : ''; ?>>
													<label for="sales-fuel_sale-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									<?php } ?>
									
									<?php if($this->config->item('saleman_commission')){ ?>
										<tr>
											<td><?= lang("saleman_commissions"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-saleman_commission-index" <?php echo $p->{'sales-saleman_commission-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-saleman_commission-add" <?php echo $p->{'sales-saleman_commission-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-saleman_commission-edit" <?php echo $p->{'sales-saleman_commission-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-saleman_commission-delete" <?php echo $p->{'sales-saleman_commission-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sales-saleman_commission-date" class="checkbox" name="sales-saleman_commission-date" <?php echo $p->{'sales-saleman_commission-date'} ? "checked" : ''; ?>>
													<label for="sales-saleman_commission-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									
									<?php } if($this->config->item('agency')){ ?>
										<tr>
											<td><?= lang("agency_commissions"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-agency_commission-index" <?php echo $p->{'sales-agency_commission-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-agency_commission-add" <?php echo $p->{'sales-agency_commission-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-agency_commission-edit" <?php echo $p->{'sales-agency_commission-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-agency_commission-delete" <?php echo $p->{'sales-agency_commission-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="sales-agency_commission-date" class="checkbox" name="sales-agency_commission-date" <?php echo $p->{'sales-agency_commission-date'} ? "checked" : ''; ?>>
													<label for="sales-agency_commission-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>

									<?php } if($Settings->installment){ ?>
										<tr>
											<td><?= lang("installments"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="installments-index" <?php echo $p->{'installments-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="installments-add" <?php echo $p->{'installments-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="installments-edit" <?php echo $p->{'installments-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="installments-delete" <?php echo $p->{'installments-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="installments-payments" class="checkbox" name="installments-payments" <?php echo $p->{'installments-payments'} ? "checked" : ''; ?>>
													<label for="installments-payments" class="padding05"><?= lang('payments') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="installments-payoff" class="checkbox" name="installments-payoff" <?php echo $p->{'installments-payoff'} ? "checked" : ''; ?>>
													<label for="installments-payoff" class="padding05"><?= lang('payoff') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="installments-inactive" class="checkbox" name="installments-inactive" <?php echo $p->{'installments-inactive'} ? "checked" : ''; ?>>
													<label for="installments-inactive" class="padding05"><?= lang('inactive') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="installments-date" class="checkbox" name="installments-date" <?php echo $p->{'installments-date'} ? "checked" : ''; ?>>
													<label for="installments-date" class="padding05"><?= lang('date') ?></label>
												</div>
												
											</td>
										</tr>
									<?php } if($this->config->item("loan")){ ?>
									
										<tr>
											<td><?= lang("loans"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="loans-index" <?php echo $p->{'loans-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												n/a
											</td>
											<td class="text-center">
												n/a
											</td>
											<td class="text-center">
												n/a
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-schedule-add" class="checkbox" name="loans-schedule-add" <?php echo $p->{'loans-schedule-add'} ? "checked" : ''; ?>>
													<label for="loans-schedule-add" class="padding05"><?= lang('add_schedule') ?></label>
												</div>
												<div class="col-md-6"> 
													
													<input type="checkbox" value="1" id="loans-schedule-edit" class="checkbox" name="loans-schedule-edit" <?php echo $p->{'loans-schedule-edit'} ? "checked" : ''; ?>>
													<label for="loans-schedule-edit" class="padding05"><?= lang('edit_schedule') ?></label>
												</div>
												<div class="col-md-6"> 
													
													<input type="checkbox" value="1" id="loans-date" class="checkbox" name="loans-date" <?php echo $p->{'loans-date'} ? "checked" : ''; ?>>
													<label for="loans-date" class="padding05"><?= lang('date') ?></label>
												</div>
												<div class="col-md-6"> 
													
													<input type="checkbox" value="1" id="loans-payment-schedule" class="checkbox" name="loans-payment-schedule" <?php echo $p->{'loans-payment-schedule'} ? "checked" : ''; ?>>
													<label for="loans-payment-schedule" class="padding05"><?= lang('payment_schedule') ?></label>
												</div>
												<div class="col-md-6"> 
													
													<input type="checkbox" value="1" id="loans-payments" class="checkbox" name="loans-payments" <?php echo $p->{'loans-payments'} ? "checked" : ''; ?>>
													<label for="loans-payments" class="padding05"><?= lang('payments') ?></label>
												</div>
												<div class="col-md-6"> 
													
													<input type="checkbox" value="1" id="loans-payoff" class="checkbox" name="loans-payoff" <?php echo $p->{'loans-payoff'} ? "checked" : ''; ?>>
													<label for="loans-payoff" class="padding05"><?= lang('payoff') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-borrowers" class="checkbox" name="loans-borrowers" <?php echo $p->{'loans-borrowers'} ? "checked" : ''; ?>>
													<label for="loans-borrowers" class="padding05"><?= lang('borrowers') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-borrower_types" class="checkbox" name="loans-borrower_types" <?php echo $p->{'loans-borrower_types'} ? "checked" : ''; ?>>
													<label for="loans-borrower_types" class="padding05"><?= lang('borrower_types') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-loan_products" class="checkbox" name="loans-loan_products" <?php echo $p->{'loans-loan_products'} ? "checked" : ''; ?>>
													<label for="loans-loan_products" class="padding05"><?= lang('loan_products') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-collaterals" class="checkbox" name="loans-collaterals" <?php echo $p->{'loans-loan_products'} ? "checked" : ''; ?>>
													<label for="loans-collaterals" class="padding05"><?= lang('collaterals') ?></label>
												</div>
												<div class="col-md-6"> 	
													<input type="checkbox" value="1" id="loans-guarantors" class="checkbox" name="loans-guarantors" <?php echo $p->{'loans-guarantors'} ? "checked" : ''; ?>>
													<label for="loans-guarantors" class="padding05"><?= lang('guarantors') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-charges" class="checkbox" name="loans-charges" <?php echo $p->{'loans-charges'} ? "checked" : ''; ?>>
													<label for="loans-charges" class="padding05"><?= lang('charges') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-working_status" class="checkbox" name="loans-working_status" <?php echo $p->{'loans-working_status'} ? "checked" : ''; ?>>
													<label for="loans-working_status" class="padding05"><?= lang('working_status') ?></label>
												</div>
											</div>
												
											</td>
										</tr>
										
										<tr>
											<td><?= lang("loan_applications"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="loans-applications-index" <?php echo $p->{'loans-applications-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="loans-applications-add" <?php echo $p->{'loans-applications-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="loans-applications-edit" <?php echo $p->{'loans-applications-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="loans-applications-delete" <?php echo $p->{'loans-applications-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-applications-approve" class="checkbox" name="loans-applications-approve" <?php echo $p->{'loans-applications-approve'} ? "checked" : ''; ?>>
													<label for="loans-applications-approve" class="padding05"><?= lang('approve_application') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-applications-decline" class="checkbox" name="loans-applications-decline" <?php echo $p->{'loans-applications-decline'} ? "checked" : ''; ?>>
													<label for="loans-applications-decline" class="padding05"><?= lang('decline_application') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="loans-applications-disburse" class="checkbox" name="loans-applications-disburse" <?php echo $p->{'loans-applications-disburse'} ? "checked" : ''; ?>>
													<label for="loans-applications-disburse" class="padding05"><?= lang('add_disbursement') ?></label>
												</div>
												<div class="col-md-6"> 	
													<input type="checkbox" value="1" id="loans-applications-date" class="checkbox" name="loans-applications-date" <?php echo $p->{'loans-applications-date'} ? "checked" : ''; ?>>
													<label for="loans-applications-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
												
											</td>
										</tr>
										
										<tr>
											<td><?= lang("savings"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="savings-index" <?php echo $p->{'savings-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="savings-add" <?php echo $p->{'savings-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="savings-edit" <?php echo $p->{'savings-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="savings-delete" <?php echo $p->{'savings-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="savings-add_deposit" class="checkbox" name="savings-add_deposit" <?php echo $p->{'savings-add_deposit'} ? "checked" : ''; ?>>
													<label for="savings-add_deposit" class="padding05"><?= lang('add_deposit') ?></label>
												</div>
												<div class="col-md-6"> 	
													<input type="checkbox" value="1" id="savings-add_withdraw" class="checkbox" name="savings-add_withdraw" <?php echo $p->{'savings-add_withdraw'} ? "checked" : ''; ?>>
													<label for="savings-add_withdraw" class="padding05"><?= lang('add_withdraw') ?></label>
												</div>
												<div class="col-md-6"> 	
													<input type="checkbox" value="1" id="savings-add_transfer" class="checkbox" name="savings-add_transfer" <?php echo $p->{'savings-add_transfer'} ? "checked" : ''; ?>>
													<label for="savings-add_transfer" class="padding05"><?= lang('add_transfer') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="savings-saving_products" class="checkbox" name="savings-saving_products" <?php echo $p->{'savings-saving_products'} ? "checked" : ''; ?>>
													<label for="savings-saving_products" class="padding05"><?= lang('saving_products') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="savings-date" class="checkbox" name="savings-date" <?php echo $p->{'savings-date'} ? "checked" : ''; ?>>
													<label for="savings-date" class="padding05"><?= lang('date') ?></label>
												</div>
												
											</td>
										</tr>
										
									<?php } if($this->config->item('deliveries')) {?>
									<tr>
										<td><?= lang("deliveries"); ?></td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="sales-deliveries" <?php echo $p->{'sales-deliveries'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="sales-add_delivery" <?php echo $p->{'sales-add_delivery'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="sales-edit_delivery" <?php echo $p->{'sales-edit_delivery'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="sales-delete_delivery" <?php echo $p->{'sales-delete_delivery'} ? "checked" : ''; ?>>
										</td>
										<td>
										<div class="container-fluid">
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="sales-email" class="checkbox" name="sales-email_delivery" <?php echo $p->{'sales-email_delivery'} ? "checked" : ''; ?>><label for="sales-email_delivery" class="padding05"><?= lang('email') ?></label>
											</div>
											<div class="col-md-6">
												<input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf_delivery" <?php echo $p->{'sales-pdf_delivery'} ? "checked" : ''; ?>>
												<label for="sales-pdf_delivery" class="padding05"><?= lang('pdf') ?></label>
											</div>
											<div class="col-md-6">
												<input type="checkbox" value="1" id="sales-date_delivery" class="checkbox" name="sales-date_delivery" <?php echo $p->{'sales-date_delivery'} ? "checked" : ''; ?>>
												<label for="sales-date_delivery" class="padding05"><?= lang('date') ?></label>
											</div>
										</div>
										</td>
									</tr>
									<?php } if($this->config->item('repair')) {?>
									<tr>
										<td><?= lang("repairs"); ?></td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="repairs-index" <?php echo $p->{'repairs-index'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="repairs-add" <?php echo $p->{'repairs-add'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="repairs-edit" <?php echo $p->{'repairs-edit'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="repairs-delete" <?php echo $p->{'repairs-delete'} ? "checked" : ''; ?>>
										</td>
										<td>
											<input type="checkbox" value="1" id="repairs-update_status" class="checkbox" name="repairs-update_status" <?php echo $p->{'repairs-update_status'} ? "checked" : ''; ?>>
											<label for="repairs-update_status" class="padding05"><?= lang('update_status') ?></label>
											<input type="checkbox" value="1" id="repairs-pdf" class="checkbox" name="repairs-pdf" <?php echo $p->{'repairs-pdf'} ? "checked" : ''; ?>>
											<label for="repairs-pdf" class="padding05"><?= lang('pdf') ?></label>
											<input type="checkbox" value="1" id="repairs-date" class="checkbox" name="repairs-date" <?php echo $p->{'repairs-date'} ? "checked" : ''; ?>>
											<label for="repairs-date" class="padding05"><?= lang('date') ?></label>
											<input type="checkbox" value="1" id="repairs-problems" class="checkbox" name="repairs-problems" <?php echo $p->{'repairs-problems'} ? "checked" : ''; ?>>
											<label for="repairs-problems" class="padding05"><?= lang('problems') ?>
											</label>
											<input type="checkbox" value="1" id="repairs-items" class="checkbox" name="repairs-items" <?php echo $p->{'repairs-items'} ? "checked" : ''; ?>>
											<label for="repairs-items" class="padding05"><?= lang('items') ?></label>
											<input type="checkbox" value="1" id="repairs-view_status" class="checkbox" name="repairs-view_status" <?php echo $p->{'repairs-view_status'} ? "checked" : ''; ?>>
											<label for="repairs-view_status" class="padding05"><?= lang('view_status') ?></label>
											
											<input type="checkbox" value="1" id="repairs-machine_types" class="checkbox" name="repairs-machine_types" <?php echo $p->{'repairs-machine_types'} ? "checked" : ''; ?>>
											<label for="repairs-machine_types" class="padding05"><?= lang('machine_types') ?></label>

											<input type="checkbox" value="1" id="reports-repairs" class="checkbox" name="reports-repairs" <?php echo $p->{'reports-repairs'} ? "checked" : ''; ?>>
											<label for="reports-repairs" class="padding05"><?= lang('repairs_report') ?></label>
										</td>
									</tr>
									<tr>
										<td><?= lang("checks"); ?></td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="repairs-checks" <?php echo $p->{'repairs-checks'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="repairs-add_check" <?php echo $p->{'repairs-add_check'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="repairs-edit_check" <?php echo $p->{'repairs-edit_check'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="repairs-delete_check" <?php echo $p->{'repairs-delete_check'} ? "checked" : ''; ?>>
										</td>
										<td>
											<input type="checkbox" value="1" id="repairs-date_check" class="checkbox" name="repairs-date" <?php echo $p->{'repairs-date_check'} ? "checked" : ''; ?>>
											<label for="repairs-date_check" class="padding05"><?= lang('date') ?></label>

											<input type="checkbox" value="1" id="repairs-diagnostics" class="checkbox" name="repairs-diagnostics" <?php echo $p->{'repairs-diagnostics'} ? "checked" : ''; ?>>
											<label for="repairs-diagnostics" class="padding05"><?= lang('diagnostics') ?></label>

										</td>
									</tr>
									<?php } if($this->config->item('gift_card')){ ?>
										<tr>
											<td><?= lang("gift_cards"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-gift_cards" <?php echo $p->{'sales-gift_cards'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-add_gift_card" <?php echo $p->{'sales-add_gift_card'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-edit_gift_card" <?php echo $p->{'sales-edit_gift_card'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-delete_gift_card" <?php echo $p->{'sales-delete_gift_card'} ? "checked" : ''; ?>>
											</td>
											<td>

											</td>
										</tr>
									<?php } if($this->config->item('member_card')){ ?>
										<tr>
											<td><?= lang("member_cards"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-member_cards" <?php echo $p->{'sales-member_cards'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-add_member_card" <?php echo $p->{'sales-add_member_card'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-edit_member_card" <?php echo $p->{'sales-edit_member_card'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="sales-delete_member_card" <?php echo $p->{'sales-delete_member_card'} ? "checked" : ''; ?>>
											</td>
											<td>

											</td>
										</tr>
									<?php } ?>
								
								</tbody>
							</table>

                            <table class="table table-bordered table-hover table-striped">

                                <thead>
                                <tr>
                                    <th colspan="6"
                                        style="font-size: 14px; text-align:left;"><?= lang("purchase_permissions");?></th>
                                </tr>
                                <tr>
                                    <th rowspan="2" class="text-center"><?= lang("module_name"); ?>
                                    </th>
                                    <th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
                                </tr>
                                <tr>
                                    <th class="text-center"><?= lang("view"); ?></th>
                                    <th class="text-center"><?= lang("add"); ?></th>
                                    <th class="text-center"><?= lang("edit"); ?></th>
                                    <th class="text-center"><?= lang("delete"); ?></th>
                                    <th class="text-center"><?= lang("misc"); ?></th>
                                </tr>
                                </thead>
                                
								<tbody>

									<?php if($this->config->item('purchase_request')){ ?>
										<tr>
											<td><?= lang("purchase_requests"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchase_requests-index" <?php echo $p->{'purchase_requests-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchase_requests-add" <?php echo $p->{'purchase_requests-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchase_requests-edit" <?php echo $p->{'purchase_requests-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchase_requests-delete" <?php echo $p->{'purchase_requests-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="approve_purchase_requests" class="checkbox" name="approve_purchase_requests" <?php echo $p->{'approve_purchase_requests'} ? "checked" : ''; ?>>
													<label for="approve_purchase_requests" class="padding05"><?= lang('approve_purchase_requests') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchase_requests-pdf" class="checkbox" name="purchase_requests-pdf" <?php echo $p->{'purchase_requests-pdf'} ? "checked" : ''; ?>>
													<label for="purchase_requests-pdf" class="padding05"><?= lang('pdf') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchase_requests-date" class="checkbox" name="purchase_requests-date" <?php echo $p->{'purchase_requests-date'} ? "checked" : ''; ?>>
													<label for="purchase_requests-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>

										</tr>
									<?php } if($this->config->item('purchase_order')){ ?>
										<tr>
											<td><?= lang("purchase_orders"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchase_orders-index" <?php echo $p->{'purchase_orders-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchase_orders-add" <?php echo $p->{'purchase_orders-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchase_orders-edit" <?php echo $p->{'purchase_orders-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchase_orders-delete" <?php echo $p->{'purchase_orders-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="approve_purchase_orders" class="checkbox" name="approve_purchase_orders" <?php echo $p->{'approve_purchase_orders'} ? "checked" : ''; ?>>
													<label for="approve_purchase_orders" class="padding05"><?= lang('approve_purchase_orders') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchase_orders-pdf" class="checkbox" name="purchase_orders-pdf" <?php echo $p->{'purchase_orders-pdf'} ? "checked" : ''; ?>>
													<label for="purchase_orders-pdf" class="padding05"><?= lang('pdf') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchase_orders-date" class="checkbox" name="purchase_orders-date" <?php echo $p->{'purchase_orders-date'} ? "checked" : ''; ?>>
													<label for="purchase_orders-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</td>
										</tr>
										<?php } ?>
									<?php if($this->config->item('purchase')) { ?>

										<tr>
											<td><?= lang("purchases"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-index" <?php echo $p->{'purchases-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-add" <?php echo $p->{'purchases-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-edit" <?php echo $p->{'purchases-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-delete" <?php echo $p->{'purchases-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchases-email" class="checkbox" name="purchases-email" <?php echo $p->{'purchases-email'} ? "checked" : ''; ?>>
													<label for="purchases-email" class="padding05"><?= lang('email') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchases-pdf" class="checkbox" name="purchases-pdf" <?php echo $p->{'purchases-pdf'} ? "checked" : ''; ?>>
													<label for="purchases-pdf" class="padding05"><?= lang('pdf') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchases-payments" class="checkbox" name="purchases-payments" <?php echo $p->{'purchases-payments'} ? "checked" : ''; ?>>
													<label for="purchases-payments" class="padding05"><?= lang('payments') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchases-return_purchases" class="checkbox" name="purchases-return_purchases" <?php echo $p->{'purchases-return_purchases'} ? "checked" : ''; ?>>
													<label for="purchases-return_purchases" class="padding05"><?= lang('return_purchases') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="purchases-date" class="checkbox" name="purchases-date" <?php echo $p->{'purchases-date'} ? "checked" : ''; ?>>
													<label for="purchases-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
										
										<?php if($this->config->item('receive_item')) { ?>
											<tr>
												<td><?= lang("receive_items"); ?></td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="purchases-receives" <?php echo $p->{'purchases-receives'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="purchases-add_receive" <?php echo $p->{'purchases-add_receive'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="purchases-edit_receive" <?php echo $p->{'purchases-edit_receive'} ? "checked" : ''; ?>>
												</td>
												<td class="text-center">
													<input type="checkbox" value="1" class="checkbox" name="purchases-delete_receive" <?php echo $p->{'purchases-delete_receive'} ? "checked" : ''; ?>>
												</td>
												<td>
												<div class="container-fluid">
													<div class="col-md-6"> 
														<input type="checkbox" value="1" id="purchases-receive_date" class="checkbox" name="purchases-receive_date" <?php echo $p->{'purchases-receive_date'} ? "checked" : ''; ?>>
														<label for="purchases-receive_date" class="padding05"><?= lang('date') ?></label>
													</div>
												</div>
												</td>
											</tr>
										<?php } ?>
										<tr class="hidden">
											<td><?= lang("expenses"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-expenses" <?php echo $p->{'purchases-expenses'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-expenses-add" <?php echo $p->{'purchases-expenses-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-expenses-edit" <?php echo $p->{'purchases-expenses-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-expenses-delete" <?php echo $p->{'purchases-expenses-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<?php if($Settings->approval_expense==1){ ?>
														<input type="checkbox" value="1" id="purchases-approve_expense" class="checkbox" name="purchases-approve_expense" <?php echo $p->{'purchases-approve_expense'} ? "checked" : ''; ?>>
														<label for="purchases-approve_expense" class="padding05"><?= lang('approve_expense') ?></label>
													<?php } ?>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchases-expenses-date" class="checkbox" name="purchases-expenses-date" <?php echo $p->{'purchases-expenses-date'} ? "checked" : ''; ?>>
													<label for="purchases-expenses-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									
										<?php } if($this->config->item('hr')){ ?>
										<tr>
											<td><?= lang("employee"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-index" <?php echo $p->{'hr-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-add" <?php echo $p->{'hr-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-edit" <?php echo $p->{'hr-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-delete" <?php echo $p->{'hr-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-positions" class="checkbox" name="hr-positions" <?php echo $p->{'hr-positions'} ? "checked" : ''; ?>>
													<label for="hr-positions" class="padding05"><?= lang('positions') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-departments" class="checkbox" name="hr-departments" <?php echo $p->{'hr-departments'} ? "checked" : ''; ?>>
													<label for="hr-departments" class="padding05"><?= lang('departments') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-groups" class="checkbox" name="hr-groups" <?php echo $p->{'hr-groups'} ? "checked" : ''; ?>>
													<label for="hr-groups" class="padding05"><?= lang('hr_groups') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-employee_types" class="checkbox" name="hr-employee_types" <?php echo $p->{'hr-employee_types'} ? "checked" : ''; ?>>
													<label for="hr-employee_types" class="padding05"><?= lang('employee_types') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-employees_relationships" class="checkbox" name="hr-employees_relationships" <?php echo $p->{'hr-employees_relationships'} ? "checked" : ''; ?>>
													<label for="hr-employees_relationships" class="padding05"><?= lang('employees_relationships') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-tax_conditions" class="checkbox" name="hr-tax_conditions" <?php echo $p->{'hr-tax_conditions'} ? "checked" : ''; ?>>
													<label for="hr-tax_conditions" class="padding05"><?= lang('tax_conditions') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-leave_types" class="checkbox" name="hr-leave_types" <?php echo $p->{'hr-leave_types'} ? "checked" : ''; ?>>
													<label for="hr-leave_types" class="padding05"><?= lang('leave_types') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-employees_report" class="checkbox" name="hr-employees_report" <?php echo $p->{'hr-employees_report'} ? "checked" : ''; ?>>
													<label for="hr-employees_report" class="padding05"><?= lang('employees_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="hr-banks_report" class="checkbox" name="hr-banks_report" <?php echo $p->{'hr-banks_report'} ? "checked" : ''; ?>>
													<label for="hr-banks_report" class="padding05"><?= lang('banks_report') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("kpi"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-kpi_index" <?php echo $p->{'hr-kpi_index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-kpi_add" <?php echo $p->{'hr-kpi_add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-kpi_edit" <?php echo $p->{'hr-kpi_edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-kpi_delete" <?php echo $p->{'hr-kpi_delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-kpi_types" class="checkbox" name="hr-kpi_types" <?php echo $p->{'hr-kpi_types'} ? "checked" : ''; ?>>
													<label for="hr-kpi_types" class="padding05"><?= lang('kpi_types') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-kpi_report" class="checkbox" name="hr-kpi_report" <?php echo $p->{'hr-kpi_report'} ? "checked" : ''; ?>>
													<label for="hr-kpi_report" class="padding05"><?= lang('kpi_report') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("id_card"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-id_cards" <?php echo $p->{'hr-id_cards'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-add_id_card" <?php echo $p->{'hr-add_id_card'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-edit_id_card" <?php echo $p->{'hr-edit_id_card'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-delete_id_card" <?php echo $p->{'hr-delete_id_card'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-sample_id_cards" class="checkbox" name="hr-sample_id_cards" <?php echo $p->{'hr-sample_id_cards'} ? "checked" : ''; ?>>
													<label for="hr-sample_id_cards" class="padding05"><?= lang('sample_id_cards') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-approve_id_card" class="checkbox" name="hr-approve_id_card" <?php echo $p->{'hr-approve_id_card'} ? "checked" : ''; ?>>
													<label for="hr-approve_id_card" class="padding05"><?= lang('approve_id_card') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-id_cards_report" class="checkbox" name="hr-id_cards_report" <?php echo $p->{'hr-id_cards_report'} ? "checked" : ''; ?>>
													<label for="hr-id_cards_report" class="padding05"><?= lang('id_cards_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-id_cards_date" class="checkbox" name="hr-id_cards_date" <?php echo $p->{'hr-id_cards_date'} ? "checked" : ''; ?>>
													<label for="hr-id_cards_date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("salary_review"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-salary_reviews" <?php echo $p->{'hr-salary_reviews'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-add_salary_review" <?php echo $p->{'hr-add_salary_review'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-edit_salary_review" <?php echo $p->{'hr-edit_salary_review'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="hr-delete_salary_review" <?php echo $p->{'hr-delete_salary_review'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-approve_salary_review" class="checkbox" name="hr-approve_salary_review" <?php echo $p->{'hr-approve_salary_review'} ? "checked" : ''; ?>>
													<label for="hr-approve_salary_review" class="padding05"><?= lang('approve_salary_review') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-salary_reviews_report" class="checkbox" name="hr-salary_reviews_report" <?php echo $p->{'hr-salary_reviews_report'} ? "checked" : ''; ?>>
													<label for="hr-salary_reviews_report" class="padding05"><?= lang('salary_reviews_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="hr-salary_reviews_date" class="checkbox" name="hr-salary_reviews_date" <?php echo $p->{'hr-salary_reviews_date'} ? "checked" : ''; ?>>
													<label for="hr-salary_reviews_date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									
									<?php } if($this->config->item('attendance')){ ?>       
										<tr>
											<td><?= lang("attendances"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="attendances-check_in_outs" <?php echo $p->{'attendances-check_in_outs'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="attendances-add_check_in_out" <?php echo $p->{'attendances-add_check_in_out'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="attendances-edit_check_in_out" <?php echo $p->{'attendances-edit_check_in_out'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="attendances-delete_check_in_out" <?php echo $p->{'attendances-delete_check_in_out'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-generate_attendances" class="checkbox" name="attendances-generate_attendances" <?php echo $p->{'attendances-generate_attendances'} ? "checked" : ''; ?>>
													<label for="attendances-generate_attendances" class="padding05"><?= lang('generate_attendances') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-take_leaves" class="checkbox" name="attendances-take_leaves" <?php echo $p->{'attendances-take_leaves'} ? "checked" : ''; ?>>
													<label for="attendances-take_leaves" class="padding05"><?= lang('take_leaves') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-approve_take_leave" class="checkbox" name="attendances-approve_take_leave" <?php echo $p->{'attendances-approve_take_leave'} ? "checked" : ''; ?>>
													<label for="attendances-approve_take_leave" class="padding05"><?= lang('approve_take_leave') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-approve_attendances" class="checkbox" name="attendances-approve_attendances" <?php echo $p->{'attendances-approve_attendances'} ? "checked" : ''; ?>>
													<label for="attendances-approve_attendances" class="padding05"><?= lang('approve_attendances') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-cancel_attendances" class="checkbox" name="attendances-cancel_attendances" <?php echo $p->{'attendances-cancel_attendances'} ? "checked" : ''; ?>>
													<label for="attendances-cancel_attendances" class="padding05"><?= lang('cancel_attendances') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-approve_ot" class="checkbox" name="attendances-approve_ot" <?php echo $p->{'attendances-approve_ot'} ? "checked" : ''; ?>>
													<label for="attendances-approve_ot" class="padding05"><?= lang('approve_ot') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-policies" class="checkbox" name="attendances-policies" <?php echo $p->{'attendances-policies'} ? "checked" : ''; ?>>
													<label for="attendances-policies" class="padding05"><?= lang('policies') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-ot_policies" class="checkbox" name="attendances-ot_policies" <?php echo $p->{'attendances-ot_policies'} ? "checked" : ''; ?>>
													<label for="attendances-ot_policies" class="padding05"><?= lang('ot_policies') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-list_devices" class="checkbox" name="attendances-list_devices" <?php echo $p->{'attendances-list_devices'} ? "checked" : ''; ?>>
													<label for="attendances-list_devices" class="padding05"><?= lang('devices') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-check_in_out_report" class="checkbox" name="attendances-check_in_out_report" <?php echo $p->{'attendances-check_in_out_report'} ? "checked" : ''; ?>>
													<label for="attendances-check_in_out_report" class="padding05"><?= lang('check_in_out_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-daily_attendance_report" class="checkbox" name="attendances-daily_attendance_report" <?php echo $p->{'attendances-daily_attendance_report'} ? "checked" : ''; ?>>
													<label for="attendances-daily_attendance_report" class="padding05"><?= lang('daily_attendance_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-montly_attendance_report" class="checkbox" name="attendances-montly_attendance_report" <?php echo $p->{'attendances-montly_attendance_report'} ? "checked" : ''; ?>>
													<label for="attendances-montly_attendance_report" class="padding05"><?= lang('montly_attendance_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-attendance_department_report" class="checkbox" name="attendances-attendance_department_report" <?php echo $p->{'attendances-attendance_department_report'} ? "checked" : ''; ?>>
													<label for="attendances-attendance_department_report" class="padding05"><?= lang('attendance_department_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-employee_leave_report" class="checkbox" name="attendances-employee_leave_report" <?php echo $p->{'attendances-employee_leave_report'} ? "checked" : ''; ?>>
													<label for="attendances-employee_leave_report" class="padding05"><?= lang('employee_leave_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="attendances-date" class="checkbox" name="attendances-date" <?php echo $p->{'attendances-date'} ? "checked" : ''; ?>>
													<label for="attendances-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									<?php } if($this->config->item('payroll')){ ?>    
										<tr>
											<td><?= lang("cash_advances"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-cash_advances" <?php echo $p->{'payrolls-cash_advances'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-add_cash_advance" <?php echo $p->{'payrolls-add_cash_advance'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-edit_cash_advance" <?php echo $p->{'payrolls-edit_cash_advance'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-delete_cash_advance" <?php echo $p->{'payrolls-delete_cash_advance'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-approve_cash_advance" class="checkbox" name="payrolls-approve_cash_advance" <?php echo $p->{'payrolls-approve_cash_advance'} ? "checked" : ''; ?>>
													<label for="payrolls-approve_cash_advance" class="padding05"><?= lang('approve_cash_advance') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-payback" class="checkbox" name="payrolls-payback" <?php echo $p->{'payrolls-payback'} ? "checked" : ''; ?>>
													<label for="payrolls-payback" class="padding05"><?= lang('payback') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-cash_advances_report" class="checkbox" name="payrolls-cash_advances_report" <?php echo $p->{'payrolls-cash_advances_report'} ? "checked" : ''; ?>>
													<label for="payrolls-cash_advances_report" class="padding05"><?= lang('cash_advances_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-cash_advances_date" class="checkbox" name="payrolls-cash_advances_date" <?php echo $p->{'payrolls-cash_advances_date'} ? "checked" : ''; ?>>
													<label for="payrolls-cash_advances_date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("benefits"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-benefits" <?php echo $p->{'payrolls-benefits'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-add_benefit" <?php echo $p->{'payrolls-add_benefit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-edit_benefit" <?php echo $p->{'payrolls-edit_benefit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-delete_benefit" <?php echo $p->{'payrolls-delete_benefit'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-additions" class="checkbox" name="payrolls-additions" <?php echo $p->{'payrolls-additions'} ? "checked" : ''; ?>>
													<label for="payrolls-additions" class="padding05"><?= lang('additions') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-deductions" class="checkbox" name="payrolls-deductions" <?php echo $p->{'payrolls-deductions'} ? "checked" : ''; ?>>
													<label for="payrolls-deductions" class="padding05"><?= lang('deductions') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-approve_benefit" class="checkbox" name="payrolls-approve_benefit" <?php echo $p->{'payrolls-approve_benefit'} ? "checked" : ''; ?>>
													<label for="payrolls-approve_benefit" class="padding05"><?= lang('approve_benefit') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-benefits_report" class="checkbox" name="payrolls-benefits_report" <?php echo $p->{'payrolls-benefits_report'} ? "checked" : ''; ?>>
													<label for="payrolls-benefits_report" class="padding05"><?= lang('benefits_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-benefit_details_report" class="checkbox" name="payrolls-benefit_details_report" <?php echo $p->{'payrolls-benefit_details_report'} ? "checked" : ''; ?>>
													<label for="payrolls-benefit_details_report" class="padding05"><?= lang('benefit_details_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-benefits_date" class="checkbox" name="payrolls-benefits_date" <?php echo $p->{'payrolls-benefits_date'} ? "checked" : ''; ?>>
													<label for="payrolls-benefits_date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("salaries"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-salaries" <?php echo $p->{'payrolls-salaries'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-add_salary" <?php echo $p->{'payrolls-add_salary'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-edit_salary" <?php echo $p->{'payrolls-edit_salary'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-delete_salary" <?php echo $p->{'payrolls-delete_salary'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-approve_salary" class="checkbox" name="payrolls-approve_salary" <?php echo $p->{'payrolls-approve_salary'} ? "checked" : ''; ?>>
													<label for="payrolls-approve_salary" class="padding05"><?= lang('approve_salary') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-salaries_report" class="checkbox" name="payrolls-salaries_report" <?php echo $p->{'payrolls-salaries_report'} ? "checked" : ''; ?>>
													<label for="payrolls-salaries_report" class="padding05"><?= lang('salaries_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-salary_details_report" class="checkbox" name="payrolls-salary_details_report" <?php echo $p->{'payrolls-salary_details_report'} ? "checked" : ''; ?>>
													<label for="payrolls-salary_details_report" class="padding05"><?= lang('salary_details_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-salary_banks_report" class="checkbox" name="payrolls-salary_banks_report" <?php echo $p->{'payrolls-salary_banks_report'} ? "checked" : ''; ?>>
													<label for="payrolls-salary_banks_report" class="padding05"><?= lang('salary_banks_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-payslips_report" class="checkbox" name="payrolls-payslips_report" <?php echo $p->{'payrolls-payslips_report'} ? "checked" : ''; ?>>
													<label for="payrolls-payslips_report" class="padding05"><?= lang('payslips_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="payrolls-salaries_date" class="checkbox" name="payrolls-salaries_date" <?php echo $p->{'payrolls-salaries_date'} ? "checked" : ''; ?>>
													<label for="payrolls-salaries_date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("payments"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-payments" <?php echo $p->{'payrolls-payments'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-add_payment" <?php echo $p->{'payrolls-add_payment'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-edit_payment" <?php echo $p->{'payrolls-edit_payment'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="payrolls-delete_payment" <?php echo $p->{'payrolls-delete_payment'} ? "checked" : ''; ?>>
											</td>
											<td>
												<input type="checkbox" value="1" id="payrolls-payments_report" class="checkbox" name="payrolls-payments_report" <?php echo $p->{'payrolls-payments_report'} ? "checked" : ''; ?>>
												<label for="payrolls-payments_report" class="padding05"><?= lang('payments_report') ?></label>
												<input type="checkbox" value="1" id="payrolls-payment_details_report" class="checkbox" name="payrolls-payment_details_report" <?php echo $p->{'payrolls-payment_details_report'} ? "checked" : ''; ?>>
												<label for="payrolls-payment_details_report" class="padding05"><?= lang('payment_details_report') ?></label>
												<input type="checkbox" value="1" id="payrolls-payments_date" class="checkbox" name="payrolls-payments_date" <?php echo $p->{'payrolls-payments_date'} ? "checked" : ''; ?>>
												<label for="payrolls-payments_date" class="padding05"><?= lang('date') ?></label>
											</td>
										</tr>
									<?php } if($this->config->item('constructions')){ ?>
										<tr>
											<td><?= lang("constructors"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="constructions-index-constructor" <?php echo $p->{'constructions-index-constructor'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="constructions-add-constructor" <?php echo $p->{'constructions-add-constructor'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="constructions-edit-constructor" <?php echo $p->{'constructions-edit-constructor'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="constructions-delete-constructor" <?php echo $p->{'constructions-delete-constructor'} ? "checked" : ''; ?>>
											</td>
											
										</tr>
									
									<?php }  if($this->config->item('pawn')){ ?>
										<tr>
											<td><?= lang("pawns"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="pawns-index" <?php echo $p->{'pawns-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="pawns-add" <?php echo $p->{'pawns-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="pawns-edit" <?php echo $p->{'pawns-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="pawns-delete" <?php echo $p->{'pawns-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6">
													<input type="checkbox" value="1" id="pawns-returns" class="checkbox" name="pawns-returns" <?php echo $p->{'pawns-returns'} ? "checked" : ''; ?>>
													<label for="pawns-returns" class="padding05"><?= lang('returns') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="pawns-purchases" class="checkbox" name="pawns-purchases" <?php echo $p->{'pawns-purchases'} ? "checked" : ''; ?>>
													<label for="pawns-purchases" class="padding05"><?= lang('purchases') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="pawns-closes" class="checkbox" name="pawns-closes" <?php echo $p->{'pawns-closes'} ? "checked" : ''; ?>>
													<label for="pawns-closes" class="padding05"><?= lang('closes') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="pawns-products" class="checkbox" name="pawns-products" <?php echo $p->{'pawns-products'} ? "checked" : ''; ?>>
													<label for="pawns-products" class="padding05"><?= lang('products') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="pawns-payments" class="checkbox" name="pawns-payments" <?php echo $p->{'pawns-payments'} ? "checked" : ''; ?>>
													<label for="pawns-payments" class="padding05"><?= lang('payments') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="pawns-date" class="checkbox" name="pawns-date" <?php echo $p->{'pawns-date'} ? "checked" : ''; ?>>
													<label for="pawns-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									<?php } if($this->config->item('room_rent')){ ?>
										<tr>
											<td><?= lang("rentals"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="rentals-index" <?php echo $p->{'rentals-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="rentals-add" <?php echo $p->{'rentals-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="rentals-edit" <?php echo $p->{'rentals-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="rentals-delete" <?php echo $p->{'rentals-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="rentals-date" class="checkbox" name="rentals-date" <?php echo $p->{'rentals-date'} ? "checked" : ''; ?>>
													<label for="rentals-date" class="padding05"><?= lang('date') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="rentals-edit_price" class="checkbox" name="rentals-edit_price" <?php echo $p->{'rentals-edit_price'} ? "checked" : ''; ?>>
													<label for="rentals-edit_price" class="padding05"><?= lang('edit_price') ?></label>
												</div>
												<div class="col-md-6"> 

													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="rentals-rooms" name="rentals-rooms" <?php echo $p->{'rentals-rooms'} ? "checked" : ''; ?>>
														<label for="rentals-rooms" class="padding05"><?= lang('rooms') ?></label>
													</span>
												</div>
												<div class="col-md-6"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="rentals-floors" name="rentals-floors" <?php echo $p->{'rentals-floors'} ? "checked" : ''; ?>>
														<label for="rentals-floors" class="padding05"><?= lang('floors') ?></label>
													</span>
												</div>
												<div class="col-md-6"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="rentals-services" name="rentals-services" <?php echo $p->{'rentals-services'} ? "checked" : ''; ?>>
														<label for="rentals-services" class="padding05"><?= lang('services') ?></label>
													</span>
												</div>
												<div class="col-md-6"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="reports-daily_rentals" name="reports-daily_rentals" <?php echo $p->{'reports-daily_rentals'} ? "checked" : ''; ?>>
														<label for="reports-daily_rentals" class="padding05"><?= lang('daily_rentals_report') ?></label>
													</span>
												</div>
												<div class="col-md-6"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="reports-rentals" name="reports-rentals" <?php echo $p->{'reports-rentals'} ? "checked" : ''; ?>>
														<label for="reports-rentals" class="padding05"><?= lang('rentals_report') ?></label>
													</span>
												</div>
												<div class="col-md-6"> 

													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="reports-rental_details" name="reports-rental_details" <?php echo $p->{'reports-rental_details'} ? "checked" : ''; ?>>
														<label for="reports-rental_details" class="padding05"><?= lang('rental_details_report') ?></label>
													</span>
												</div>
											</td>
										</tr>
									<?php } ?>
                                </tbody>
                            </table>

                            <table class="table table-bordered table-hover table-striped">

                                <thead>
                                <tr>
                                    <th colspan="6"
                                        style="font-size: 14px; text-align:left;"><?= lang("expenses_permissions");?></th>
                                </tr>
                                <tr>
                                    <th rowspan="2" class="text-center"><?= lang("module_name"); ?>
                                    </th>
                                    <th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
                                </tr>
                                <tr>
                                    <th class="text-center"><?= lang("view"); ?></th>
                                    <th class="text-center"><?= lang("add"); ?></th>
                                    <th class="text-center"><?= lang("edit"); ?></th>
                                    <th class="text-center"><?= lang("delete"); ?></th>
                                    <th class="text-center"><?= lang("misc"); ?></th>
                                </tr>
                                </thead>
                                
								<tbody>
									<?php if($this->config->item('purchase')) { ?>
										<tr>
											<td><?= lang("expenses"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-expenses" <?php echo $p->{'purchases-expenses'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-expenses-add" <?php echo $p->{'purchases-expenses-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-expenses-edit" <?php echo $p->{'purchases-expenses-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="purchases-expenses-delete" <?php echo $p->{'purchases-expenses-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<?php if($Settings->approval_expense==1){ ?>
														<input type="checkbox" value="1" id="purchases-approve_expense" class="checkbox" name="purchases-approve_expense" <?php echo $p->{'purchases-approve_expense'} ? "checked" : ''; ?>>
														<label for="purchases-approve_expense" class="padding05"><?= lang('approve_expense') ?></label>
													<?php } ?>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="purchases-expenses-date" class="checkbox" name="purchases-expenses-date" <?php echo $p->{'purchases-expenses-date'} ? "checked" : ''; ?>>
													<label for="purchases-expenses-date" class="padding05"><?= lang('date') ?></label>
												</div>
											</div>
											</td>
										</tr>
									<?php } ?>
                                </tbody>
                            </table>

                            <table class="table table-bordered table-hover table-striped">

                                <thead>
									<tr>
										<th colspan="6"
											style="font-size: 14px; text-align:left;"><?= lang("people_permissions");?></th>
									</tr>
									<tr>
										<th rowspan="2" class="text-center"><?= lang("module_name"); ?>
										</th>
										<th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
									</tr>
									<tr>
										<th class="text-center"><?= lang("view"); ?></th>
										<th class="text-center"><?= lang("add"); ?></th>
										<th class="text-center"><?= lang("edit"); ?></th>
										<th class="text-center"><?= lang("delete"); ?></th>
										<th class="text-center"><?= lang("misc"); ?></th>
									</tr>
                                </thead>
                                
								<tbody>
									<?php if($this->config->item('sale')) { ?>
										<tr>
											<td><?= lang("customers"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="customers-index" <?php echo $p->{'customers-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="customers-add" <?php echo $p->{'customers-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="customers-edit" <?php echo $p->{'customers-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="customers-delete" <?php echo $p->{'customers-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 

													<input type="checkbox" value="1" id="customers-deposits" class="checkbox" name="customers-deposits" <?php echo $p->{'customers-deposits'} ? "checked" : ''; ?>>
													<label for="customers-deposits" class="padding05"><?= lang('deposits') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="customers-delete_deposit" class="checkbox" name="customers-delete_deposit" <?php echo $p->{'customers-delete_deposit'} ? "checked" : ''; ?>>
													<label for="customers-delete_deposit" class="padding05"><?= lang('delete_deposit') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="customers-print_card" class="checkbox" name="customers-print_card" <?php echo $p->{'customers-print_card'} ? "checked" : ''; ?>>
													<label for="customers-print_card" class="padding05"><?= lang('print_card') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="customers-send_sms" class="checkbox" name="customers-send_sms" <?php echo $p->{'customers-send_sms'} ? "checked" : ''; ?>>
													<label for="customers-send_sms" class="padding05"><?= lang('send_sms') ?></label>
												</div>
											</td>
										</tr>
									<?php } if($this->config->item('purchase')) {?>
										<tr>
											<td><?= lang("suppliers"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="suppliers-index" <?php echo $p->{'suppliers-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="suppliers-add" <?php echo $p->{'suppliers-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="suppliers-edit" <?php echo $p->{'suppliers-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="suppliers-delete" <?php echo $p->{'suppliers-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="suppliers-deposits" class="checkbox" name="suppliers-deposits" <?php echo $p->{'suppliers-deposits'} ? "checked" : ''; ?>>
													<label for="suppliers-deposits" class="padding05"><?= lang('deposits') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="suppliers-delete_deposit" class="checkbox" name="suppliers-delete_deposit" <?php echo $p->{'suppliers-delete_deposit'} ? "checked" : ''; ?>>
													<label for="suppliers-delete_deposit" class="padding05"><?= lang('delete_deposit') ?></label>
												</div>
											</div>
											</td>
										</tr>
									<?php } ?>	
									
										<tr>
											<td><?= lang("users"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-index" <?php echo $p->{'auth-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-add" <?php echo $p->{'auth-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-edit" <?php echo $p->{'auth-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-delete" <?php echo $p->{'auth-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											</td>
										</tr>
									
									<?php if($this->config->item("saleman")){ ?>
										<tr>
											<td><?= lang("salemans"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-saleman" <?php echo $p->{'auth-saleman'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-saleman-add" <?php echo $p->{'auth-saleman-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-saleman-edit" <?php echo $p->{'auth-saleman-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-saleman-delete" <?php echo $p->{'auth-saleman-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											</td>
										</tr>
									<?php } ?>
									
									<?php if($this->config->item("agency")){ ?>
										<tr>
											<td><?= lang("agencies"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-agency" <?php echo $p->{'auth-agency'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-agency-add" <?php echo $p->{'auth-agency-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-agency-edit" <?php echo $p->{'auth-agency-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="auth-agency-delete" <?php echo $p->{'auth-agency-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											</td>
										</tr>
									<?php } ?>

                                </tbody>
                            </table>

							<table class="table table-bordered table-hover table-striped">

								<thead>
									<tr>
										<th colspan="6"
											style="font-size: 14px; text-align:left;"><?= lang("accounting_permissions");?></th>
									</tr>
									<tr>
										<th rowspan="2" class="text-center"><?= lang("module_name"); ?>
										</th>
										<th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
									</tr>
									<tr>
										<th class="text-center"><?= lang("view"); ?></th>
										<th class="text-center"><?= lang("add"); ?></th>
										<th class="text-center"><?= lang("edit"); ?></th>
										<th class="text-center"><?= lang("delete"); ?></th>
										<th class="text-center"><?= lang("misc"); ?></th>
									</tr>
								</thead>

								<tbody>
									<?php  if($Settings->accounting==1){ ?>
									<tr>
										<td><?= lang("accountings"); ?></td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-index" <?php echo $p->{'accountings-index'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-add" <?php echo $p->{'accountings-add'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-edit" <?php echo $p->{'accountings-edit'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-delete" <?php echo $p->{'accountings-delete'} ? "checked" : ''; ?>>
										</td>
										<td>
										<div class="container-fluid">
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="accountings-journals" class="checkbox" name="accountings-journals" <?php echo $p->{'accountings-journals'} ? "checked" : ''; ?>>
												<label for="accountings-journals" class="padding05"><?= lang('journals') ?></label>
											</div>
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="accountings-general_ledger" class="checkbox" name="accountings-general_ledger" <?php echo $p->{'accountings-general_ledger'} ? "checked" : ''; ?>>
												<label for="accountings-general_ledger" class="padding05"><?= lang('general_ledger') ?></label>
											</div>
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="accountings-cash_books" class="checkbox" name="accountings-cash_books" <?php echo $p->{'accountings-cash_books'} ? "checked" : ''; ?>>
												<label for="accountings-cash_books" class="padding05"><?= lang('cash_books') ?></label>
											</div>
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="accountings-trial_balance" class="checkbox" name="accountings-trial_balance" <?php echo $p->{'accountings-trial_balance'} ? "checked" : ''; ?>>
												<label for="accountings-trial_balance" class="padding05"><?= lang('trial_balance') ?></label>
											</div>
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="accountings-balance_sheet" class="checkbox" name="accountings-balance_sheet" <?php echo $p->{'accountings-balance_sheet'} ? "checked" : ''; ?>>
												<label for="accountings-balance_sheet" class="padding05"><?= lang('balance_sheet') ?></label>	
											</div>
											<div class="col-md-6"> 	
												<input type="checkbox" value="1" id="accountings-income_statement" class="checkbox" name="accountings-income_statement" <?php echo $p->{'accountings-income_statement'} ? "checked" : ''; ?>>
												<label for="accountings-income_statement" class="padding05"><?= lang('income_statement') ?></label>
											</div>
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="accountings-cash_flow" class="checkbox" name="accountings-cash_flow" <?php echo $p->{'accountings-cash_flow'} ? "checked" : ''; ?>>
												<label for="accountings-cash_flow" class="padding05"><?= lang('cash_flow') ?></label>
											</div>
										</div>
										</td>
									</tr>
									
									<tr>
										<td><?= lang("enter_journals"); ?></td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-enter_journals" <?php echo $p->{'accountings-enter_journals'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-enter_journals-add" <?php echo $p->{'accountings-enter_journals-add'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-enter_journals-edit" <?php echo $p->{'accountings-enter_journals-edit'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-enter_journals-delete" <?php echo $p->{'accountings-enter_journals-delete'} ? "checked" : ''; ?>>
										</td>
										<td>
										<div class="container-fluid">
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="accountings-enter_journals-date" class="checkbox" name="accountings-enter_journals-date" <?php echo $p->{'accountings-enter_journals-date'} ? "checked" : ''; ?>>
												<label for="accountings-enter_journals-date" class="padding05"><?= lang('date') ?></label>
											</div>
										</div>
										</td>
									</tr>
									
									<tr>
										<td><?= lang("bank_reconciliations"); ?></td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-bank_reconciliations" <?php echo $p->{'accountings-bank_reconciliations'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-bank_reconciliation-add" <?php echo $p->{'accountings-bank_reconciliation-add'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-bank_reconciliation-edit" <?php echo $p->{'accountings-bank_reconciliation-edit'} ? "checked" : ''; ?>>
										</td>
										<td class="text-center">
											<input type="checkbox" value="1" class="checkbox" name="accountings-bank_reconciliation-delete" <?php echo $p->{'accountings-bank_reconciliation-delete'} ? "checked" : ''; ?>>
										</td>
										<td>
										<div class="container-fluid">
											<div class="col-md-6"> 
												<input type="checkbox" value="1" id="accountings-bank_reconciliation-date" class="checkbox" name="accountings-bank_reconciliation-date" <?php echo $p->{'accountings-bank_reconciliation-date'} ? "checked" : ''; ?>>
												<label for="accountings-bank_reconciliation-date" class="padding05"><?= lang('date') ?></label>
											</div>
										</div>
										</td>
									</tr>
									
									<?php }  if($this->config->item('schools')){ ?>
										<tr>
											<td><?= lang("students"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-index" <?php echo $p->{'schools-index'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-add" <?php echo $p->{'schools-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-edit" <?php echo $p->{'schools-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-delete" <?php echo $p->{'schools-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-student_report" class="checkbox" name="schools-student_report" <?php echo $p->{'schools-student_report'} ? "checked" : ''; ?>>
													<label for="schools-student_report" class="padding05"><?= lang('student_report') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("teachers"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-teachers" <?php echo $p->{'schools-teachers'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-teachers-add" <?php echo $p->{'schools-teachers-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-teachers-edit" <?php echo $p->{'schools-teachers-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-teachers-delete" <?php echo $p->{'schools-teachers-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-teacher_report" class="checkbox" name="schools-teacher_report" <?php echo $p->{'schools-teacher_report'} ? "checked" : ''; ?>>
													<label for="schools-teacher_report" class="padding05"><?= lang('teacher_report') ?></label>
												</div>
											</div>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("skills"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-skills" <?php echo $p->{'schools-skills'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-skills-add" <?php echo $p->{'schools-skills-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-skills-edit" <?php echo $p->{'schools-skills-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-skills-delete" <?php echo $p->{'schools-skills-delete'} ? "checked" : ''; ?>>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("sections"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-sections" <?php echo $p->{'schools-sections'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-sections-add" <?php echo $p->{'schools-sections-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-sections-edit" <?php echo $p->{'schools-sections-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-sections-delete" <?php echo $p->{'schools-sections-delete'} ? "checked" : ''; ?>>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("levels"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-levels" <?php echo $p->{'schools-levels'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-levels-add" <?php echo $p->{'schools-levels-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-levels-edit" <?php echo $p->{'schools-levels-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-levels-delete" <?php echo $p->{'schools-levels-delete'} ? "checked" : ''; ?>>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("rooms"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-rooms" <?php echo $p->{'schools-rooms'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-rooms-add" <?php echo $p->{'schools-rooms-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-rooms-edit" <?php echo $p->{'schools-rooms-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-rooms-delete" <?php echo $p->{'schools-rooms-delete'} ? "checked" : ''; ?>>
											</td>
										</tr>
										
										
										
										<tr>
											<td><?= lang("classes"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-classes" <?php echo $p->{'schools-classes'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-classes-add" <?php echo $p->{'schools-classes-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-classes-edit" <?php echo $p->{'schools-classes-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-classes-delete" <?php echo $p->{'schools-classes-delete'} ? "checked" : ''; ?>>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("time_tables"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-time_tables" <?php echo $p->{'schools-time_tables'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-time_tables-add" <?php echo $p->{'schools-time_tables-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-time_tables-edit" <?php echo $p->{'schools-time_tables-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-time_tables-delete" <?php echo $p->{'schools-time_tables-delete'} ? "checked" : ''; ?>>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("class_years"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-class_years" <?php echo $p->{'schools-class_years'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-class_years-add" <?php echo $p->{'schools-class_years-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-class_years-edit" <?php echo $p->{'schools-class_years-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-class_years-delete" <?php echo $p->{'schools-class_years-delete'} ? "checked" : ''; ?>>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("credit_scores"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-credit_scores" <?php echo $p->{'schools-credit_scores'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-credit_scores-add" <?php echo $p->{'schools-credit_scores-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-credit_scores-edit" <?php echo $p->{'schools-credit_scores-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-credit_scores-delete" <?php echo $p->{'schools-credit_scores-delete'} ? "checked" : ''; ?>>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("attendances"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-attendances" <?php echo $p->{'schools-attendances'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-attendances-add" <?php echo $p->{'schools-attendances-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-attendances-edit" <?php echo $p->{'schools-attendances-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-attendances-delete" <?php echo $p->{'schools-attendances-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-attendance_report" class="checkbox" name="schools-attendance_report" <?php echo $p->{'schools-attendance_report'} ? "checked" : ''; ?>>
													<label for="schools-attendance_report" class="padding05"><?= lang('attendance_report') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("teacher_attendances"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-teacher_attendances" <?php echo $p->{'schools-teacher_attendances'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-teacher_attendances-add" <?php echo $p->{'schools-teacher_attendances-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-teacher_attendances-edit" <?php echo $p->{'schools-teacher_attendances-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-teacher_attendances-delete" <?php echo $p->{'schools-teacher_attendances-delete'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-teacher_attendance_report" class="checkbox" name="schools-teacher_attendance_report" <?php echo $p->{'schools-teacher_attendance_report'} ? "checked" : ''; ?>>
													<label for="schools-teacher_attendance_report" class="padding05"><?= lang('teacher_attendance_report') ?></label>
												</div>
											</div>
											</td>
										</tr>
										
										<tr>
											<td><?= lang("examinations"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-examinations" <?php echo $p->{'schools-examinations'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-examinations-add" <?php echo $p->{'schools-examinations-add'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-examinations-edit" <?php echo $p->{'schools-examinations-edit'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="schools-examinations-delete" <?php echo $p->{'schools-examinations-delete'} ? "checked" : ''; ?>>
											</td>
											
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-study_info_report" class="checkbox" name="schools-study_info_report" <?php echo $p->{'schools-study_info_report'} ? "checked" : ''; ?>>
													<label for="schools-study_info_report" class="padding05"><?= lang('study_info_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-examanition_report" class="checkbox" name="schools-examanition_report" <?php echo $p->{'schools-examanition_report'} ? "checked" : ''; ?>>
													<label for="schools-examanition_report" class="padding05"><?= lang('examanition_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-monthly_class_result_report" class="checkbox" name="schools-monthly_class_result_report" <?php echo $p->{'schools-monthly_class_result_report'} ? "checked" : ''; ?>>
													<label for="schools-monthly_class_result_report" class="padding05"><?= lang('monthly_class_result_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-monthly_top_five_report" class="checkbox" name="schools-monthly_top_five_report" <?php echo $p->{'schools-monthly_top_five_report'} ? "checked" : ''; ?>>
													<label for="schools-monthly_top_five_report" class="padding05"><?= lang('monthly_top_five_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-section_by_month_report" class="checkbox" name="schools-section_by_month_report" <?php echo $p->{'schools-section_by_month_report'} ? "checked" : ''; ?>>
													<label for="schools-section_by_month_report" class="padding05"><?= lang('section_by_month_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-sectionly_class_result_report" class="checkbox" name="schools-sectionly_class_result_report" <?php echo $p->{'schools-sectionly_class_result_report'} ? "checked" : ''; ?>>
													<label for="schools-sectionly_class_result_report" class="padding05"><?= lang('sectionly_class_result_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-class_result_report" class="checkbox" name="schools-class_result_report" <?php echo $p->{'schools-class_result_report'} ? "checked" : ''; ?>>
													<label for="schools-class_result_report" class="padding05"><?= lang('class_result_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-yearly_class_result_report" class="checkbox" name="schools-yearly_class_result_report" <?php echo $p->{'schools-yearly_class_result_report'} ? "checked" : ''; ?>>
													<label for="schools-yearly_class_result_report" class="padding05"><?= lang('yearly_class_result_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-yearly_top_five_report" class="checkbox" name="schools-yearly_top_five_report" <?php echo $p->{'schools-yearly_top_five_report'} ? "checked" : ''; ?>>
													<label for="schools-yearly_top_five_report" class="padding05"><?= lang('yearly_top_five_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-yearly_subject_result_report" class="checkbox" name="schools-yearly_subject_result_report" <?php echo $p->{'schools-yearly_subject_result_report'} ? "checked" : ''; ?>>
													<label for="schools-yearly_subject_result_report" class="padding05"><?= lang('yearly_subject_result_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-sectionly_subject_result_report" class="checkbox" name="schools-sectionly_subject_result_report" <?php echo $p->{'schools-sectionly_subject_result_report'} ? "checked" : ''; ?>>
													<label for="schools-sectionly_subject_result_report" class="padding05"><?= lang('sectionly_subject_result_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-result_by_student_form" class="checkbox" name="schools-result_by_student_form" <?php echo $p->{'schools-result_by_student_form'} ? "checked" : ''; ?>>
													<label for="schools-result_by_student_form" class="padding05"><?= lang('result_by_student_form') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-monthly_top_five_form" class="checkbox" name="schools-monthly_top_five_form" <?php echo $p->{'schools-monthly_top_five_form'} ? "checked" : ''; ?>>
													<label for="schools-monthly_top_five_form" class="padding05"><?= lang('monthly_top_five_form') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-yearly_top_five_form" class="checkbox" name="schools-yearly_top_five_form" <?php echo $p->{'schools-yearly_top_five_form'} ? "checked" : ''; ?>>
													<label for="schools-yearly_top_five_form" class="padding05"><?= lang('yearly_top_five_form') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-best_student_by_level_report" class="checkbox" name="schools-best_student_by_level_report" <?php echo $p->{'schools-best_student_by_level_report'} ? "checked" : ''; ?>>
													<label for="schools-best_student_by_level_report" class="padding05"><?= lang('best_student_by_level_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-failure_student_by_year_report" class="checkbox" name="schools-failure_student_by_year_report" <?php echo $p->{'schools-failure_student_by_year_report'} ? "checked" : ''; ?>>
													<label for="schools-failure_student_by_year_report" class="padding05"><?= lang('failure_student_by_year_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="schools-overview_chart" class="checkbox" name="schools-overview_chart" <?php echo $p->{'schools-overview_chart'} ? "checked" : ''; ?>>
													<label for="schools-overview_chart" class="padding05"><?= lang('overview_chart') ?></label>
												</div>
											</div>
											</td>
											
										</tr>
									<?php }  if($this->config->item('concretes')){ ?>
										<tr>
											<td><?= lang("delivery"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-deliveries" <?php echo $p->{'concretes-deliveries'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-add_delivery" <?php echo $p->{'concretes-add_delivery'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-edit_delivery" <?php echo $p->{'concretes-edit_delivery'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-delete_delivery" <?php echo $p->{'concretes-delete_delivery'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-deliveries-date" class="checkbox" name="concretes-deliveries-date" <?php echo $p->{'concretes-deliveries-date'} ? "checked" : ''; ?>>
													<label for="concretes-deliveries-date" class="padding05"><?= lang('date') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-drivers" class="checkbox" name="concretes-drivers" <?php echo $p->{'concretes-drivers'} ? "checked" : ''; ?>>
													<label for="concretes-drivers" class="padding05"><?= lang('driver') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-trucks" class="checkbox" name="concretes-trucks" <?php echo $p->{'concretes-trucks'} ? "checked" : ''; ?>>
													<label for="concretes-trucks" class="padding05"><?= lang('truck') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-slumps" class="checkbox" name="concretes-slumps" <?php echo $p->{'concretes-slumps'} ? "checked" : ''; ?>>
													<label for="concretes-slumps" class="padding05"><?= lang('slump') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-casting_types" class="checkbox" name="concretes-casting_types" <?php echo $p->{'concretes-casting_types'} ? "checked" : ''; ?>>
													<label for="concretes-casting_types" class="padding05"><?= lang('casting_type') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-quality_controllers" class="checkbox" name="concretes-quality_controllers" <?php echo $p->{'concretes-quality_controllers'} ? "checked" : ''; ?>>
													<label for="concretes-quality_controllers" class="padding05"><?= lang('quality_controller') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-deliveries_report" class="checkbox" name="concretes-deliveries_report" <?php echo $p->{'concretes-deliveries_report'} ? "checked" : ''; ?>>
													<label for="concretes-deliveries_report" class="padding05"><?= lang('deliveries_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-daily_deliveries" class="checkbox" name="concretes-daily_deliveries" <?php echo $p->{'concretes-daily_deliveries'} ? "checked" : ''; ?>>
													<label for="concretes-daily_deliveries" class="padding05"><?= lang('daily_deliveries') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-daily_stock_outs" class="checkbox" name="concretes-daily_stock_outs" <?php echo $p->{'concretes-daily_stock_outs'} ? "checked" : ''; ?>>
													<label for="concretes-daily_stock_outs" class="padding05"><?= lang('daily_stock_outs') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-daily_stock_ins" class="checkbox" name="concretes-daily_stock_ins" <?php echo $p->{'concretes-daily_stock_ins'} ? "checked" : ''; ?>>
													<label for="concretes-daily_stock_ins" class="padding05"><?= lang('daily_stock_ins') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-inventory_in_outs" class="checkbox" name="concretes-inventory_in_outs" <?php echo $p->{'concretes-inventory_in_outs'} ? "checked" : ''; ?>>
													<label for="concretes-inventory_in_outs" class="padding05"><?= lang('inventory_in_outs') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-truck_commissions" class="checkbox" name="concretes-truck_commissions" <?php echo $p->{'concretes-truck_commissions'} ? "checked" : ''; ?>>
													<label for="concretes-truck_commissions" class="padding05"><?= lang('truck_commissions') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-truck_summary_commissions" class="checkbox" name="concretes-truck_summary_commissions" <?php echo $p->{'concretes-truck_summary_commissions'} ? "checked" : ''; ?>>
													<label for="concretes-truck_summary_commissions" class="padding05"><?= lang('truck_summary_commissions') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-pump_commissions" class="checkbox" name="concretes-pump_commissions" <?php echo $p->{'concretes-pump_commissions'} ? "checked" : ''; ?>>
													<label for="concretes-pump_commissions" class="padding05"><?= lang('pump_commissions') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-pump_summary_commissions" class="checkbox" name="concretes-pump_summary_commissions" <?php echo $p->{'concretes-pump_summary_commissions'} ? "checked" : ''; ?>>
													<label for="concretes-pump_summary_commissions" class="padding05"><?= lang('pump_summary_commissions') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-mixzer_commissions" class="checkbox" name="concretes-mixzer_commissions" <?php echo $p->{'concretes-mixzer_commissions'} ? "checked" : ''; ?>>
													<label for="concretes-mixzer_commissions" class="padding05"><?= lang('mixzer_commissions') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-qc_commissions" class="checkbox" name="concretes-qc_commissions" <?php echo $p->{'concretes-qc_commissions'} ? "checked" : ''; ?>>
													<label for="concretes-qc_commissions" class="padding05"><?= lang('qc_commissions') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-zaer_ot" class="checkbox" name="concretes-zaer_ot" <?php echo $p->{'concretes-zaer_ot'} ? "checked" : ''; ?>>
													<label for="concretes-zaer_ot" class="padding05"><?= lang('zaer_ot') ?></label>
												</div>
											</div>
											</td>
										</tr>	
										<tr>
											<td><?= lang("fuel"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-fuels" <?php echo $p->{'concretes-fuels'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-add_fuel" <?php echo $p->{'concretes-add_fuel'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-edit_fuel" <?php echo $p->{'concretes-edit_fuel'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-delete_fuel" <?php echo $p->{'concretes-delete_fuel'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-fuels-date" class="checkbox" name="concretes-fuels-date" <?php echo $p->{'concretes-fuels-date'} ? "checked" : ''; ?>>
													<label for="concretes-fuels-date" class="padding05"><?= lang('date') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-fuels_report" class="checkbox" name="concretes-fuels_report" <?php echo $p->{'concretes-fuels_report'} ? "checked" : ''; ?>>
													<label for="concretes-fuels_report" class="padding05"><?= lang('fuels_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-fuel_summaries_report" class="checkbox" name="concretes-fuel_summaries_report" <?php echo $p->{'concretes-fuel_summaries_report'} ? "checked" : ''; ?>>
													<label for="concretes-fuel_summaries_report" class="padding05"><?= lang('fuel_summaries_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-fuel_details_report" class="checkbox" name="concretes-fuel_details_report" <?php echo $p->{'concretes-fuel_details_report'} ? "checked" : ''; ?>>
													<label for="concretes-fuel_details_report" class="padding05"><?= lang('fuel_details_report') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-fuel_by_customer_report" class="checkbox" name="concretes-fuel_by_customer_report" <?php echo $p->{'concretes-fuel_by_customer_report'} ? "checked" : ''; ?>>
													<label for="concretes-fuel_by_customer_report" class="padding05"><?= lang('fuel_by_customer_report') ?></label>
												</div>
											</div>
											</td>
										</tr>	
										<tr>
											<td><?= lang("sale"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-sales" <?php echo $p->{'concretes-sales'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-add_sale" <?php echo $p->{'concretes-add_sale'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-edit_sale" <?php echo $p->{'concretes-edit_sale'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-delete_sale" <?php echo $p->{'concretes-delete_sale'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-sales-date" class="checkbox" name="concretes-sales-date" <?php echo $p->{'concretes-sales-date'} ? "checked" : ''; ?>>
													<label for="concretes-sales-date" class="padding05"><?= lang('date') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-sales_report" class="checkbox" name="concretes-sales_report" <?php echo $p->{'concretes-sales_report'} ? "checked" : ''; ?>>
													<label for="concretes-sales_report" class="padding05"><?= lang('sales_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-sale_details_report" class="checkbox" name="concretes-sale_details_report" <?php echo $p->{'concretes-sale_details_report'} ? "checked" : ''; ?>>
													<label for="concretes-sale_details_report" class="padding05"><?= lang('sale_details_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-product_sales_report" class="checkbox" name="concretes-product_sales_report" <?php echo $p->{'concretes-product_sales_report'} ? "checked" : ''; ?>>
													<label for="concretes-product_sales_report" class="padding05"><?= lang('product_sales_report') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-product_customers_report" class="checkbox" name="concretes-product_customers_report" <?php echo $p->{'concretes-product_customers_report'} ? "checked" : ''; ?>>
													<label for="concretes-product_customers_report" class="padding05"><?= lang('product_customers_report') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("adjustment"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-adjustments" <?php echo $p->{'concretes-adjustments'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-add_adjustment" <?php echo $p->{'concretes-add_adjustment'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center"></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-delete_adjustment" <?php echo $p->{'concretes-delete_adjustment'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-approve_adjustment" class="checkbox" name="concretes-approve_adjustment" <?php echo $p->{'concretes-approve_adjustment'} ? "checked" : ''; ?>>
													<label for="concretes-approve_adjustment" class="padding05"><?= lang('approve_adjustment') ?></label>
												</div>
												<div class="col-md-6">
													<input type="checkbox" value="1" id="concretes-adjustments_report" class="checkbox" name="concretes-adjustments_report" <?php echo $p->{'concretes-adjustments_report'} ? "checked" : ''; ?>>
													<label for="concretes-adjustments_report" class="padding05"><?= lang('adjustments_report') ?></label>
												</div>
											</div>
											</td>
										</tr>
										<tr>
											<td><?= lang("error"); ?></td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-errors" <?php echo $p->{'concretes-errors'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-add_error" <?php echo $p->{'concretes-add_error'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-edit_error" <?php echo $p->{'concretes-edit_error'} ? "checked" : ''; ?>>
											</td>
											<td class="text-center">
												<input type="checkbox" value="1" class="checkbox" name="concretes-delete_error" <?php echo $p->{'concretes-delete_error'} ? "checked" : ''; ?>>
											</td>
											<td>
											<div class="container-fluid">
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-errors-date" class="checkbox" name="concretes-errors-date" <?php echo $p->{'concretes-errors-date'} ? "checked" : ''; ?>>
													<label for="concretes-errors-date" class="padding05"><?= lang('date') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-daily_errors" class="checkbox" name="concretes-daily_errors" <?php echo $p->{'concretes-daily_errors'} ? "checked" : ''; ?>>
													<label for="concretes-daily_errors" class="padding05"><?= lang('daily_errors') ?></label>
												</div>
												<div class="col-md-6"> 
													<input type="checkbox" value="1" id="concretes-daily_error_materials" class="checkbox" name="concretes-daily_error_materials" <?php echo $p->{'concretes-daily_error_materials'} ? "checked" : ''; ?>>
													<label for="concretes-daily_error_materials" class="padding05"><?= lang('daily_error_materials') ?></label>
												</div>
											</div>
											</td>
										</tr>
										
									<?php }  ?>
								

								</tbody>
							</table>

                            <table class="table table-bordered table-hover table-striped">

                                <thead>
									<tr>
										<th colspan="6"
											style="font-size: 14px; text-align:left;"><?= lang("setting_permissions");?></th>
									</tr>
									<tr>
										<th rowspan="2" class="text-center hidden"><?= lang("module_name"); ?>
										</th>
										<th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
									</tr>
									<tr class="hidden">
										<th class="text-center"><?= lang("view"); ?></th>
										<th class="text-center"><?= lang("add"); ?></th>
										<th class="text-center"><?= lang("edit"); ?></th>
										<th class="text-center"><?= lang("delete"); ?></th>
										<th class="text-center"><?= lang("misc"); ?></th>
									</tr>
                                </thead>
                                
								<tbody>
									<tr>
										<td class="hidden"><?= lang("settings"); ?></td>
										<td colspan="5">
										<div class="container-fluid">
										<div class="col-md-3"> 
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="settings" name="settings" <?php echo $p->{'settings'} ? "checked" : ''; ?>>
												<label for="settings" class="padding05"><?= lang('settings') ?></label>
											</span>
										</div>
										<div class="col-md-3"> 
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="system_settings" name="system_settings" <?php echo $p->{'system_settings'} ? "checked" : ''; ?>>
												<label for="system_settings" class="padding05"><?= lang('system_settings') ?></label>
											</span>
										</div>
										<div class="col-md-3">
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="pos_settings" name="pos_settings" <?php echo $p->{'pos_settings'} ? "checked" : ''; ?>>
												<label for="pos_settings" class="padding05"><?= lang('pos_settings') ?></label>
											</span>
										</div>
										<div class="col-md-3">
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="change_logo"
												name="change_logo" <?php echo $p->{'change_logo'} ? "checked" : ''; ?>><label for="change_logo" class="padding05"><?= lang('change_logo') ?></label>
											</span>
										</div>
										<div class="col-md-3">
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="billers-index" name="billers-index" <?php echo $p->{'billers-index'} ? "checked" : ''; ?>>
												<label for="billers-index" class="padding05"><?= lang('billers') ?></label>
											</span>
										</div>
										<div class="col-md-3">
											<?php  if($Settings->project==1){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="projects-index" name="projects-index" <?php echo $p->{'projects-index'} ? "checked" : ''; ?>>
													<label for="projects-index" class="padding05"><?= lang('projects') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item('inventory')) { ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="warehouses-index" name="warehouses-index" <?php echo $p->{'warehouses-index'} ? "checked" : ''; ?>>
													<label for="warehouses-index" class="padding05"><?= lang('warehouses') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="categories-index" name="categories-index" <?php echo $p->{'categories-index'} ? "checked" : ''; ?>>
													<label for="categories-index" class="padding05"><?= lang('categories') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="units-index" name="units-index" <?php echo $p->{'units-index'} ? "checked" : ''; ?>>
													<label for="units-index" class="padding05"><?= lang('units') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="brands-index" name="brands-index" <?php echo $p->{'brands-index'} ? "checked" : ''; ?>>
													<label for="brands-index" class="padding05"><?= lang('brands') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<?php if($this->config->item('repair')==true){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="system_settings-models" name="system_settings-models" <?php echo $p->{'system_settings-models'} ? "checked" : ''; ?>>
													<label for="system_settings-models" class="padding05"><?= lang('models') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<?php } ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="variants-index" name="variants-index" <?php echo $p->{'variants-index'} ? "checked" : ''; ?>>
													<label for="variants-index" class="padding05"><?= lang('variants') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<?php if($this->config->item('convert')){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="boms-index" name="boms-index" <?php echo $p->{'boms-index'} ? "checked" : ''; ?>>
														<label for="boms-index" class="padding05"><?= lang('bom') ?></label>
													</span>
										</div>
										<div class="col-md-3">
												<?php } ?>
											<?php } ?>
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="areas-index" name="areas-index" <?php echo $p->{'areas-index'} ? "checked" : ''; ?>>
												<label for="areas-index" class="padding05"><?= lang('areas') ?></label>
											</span>
										</div>
										<div class="col-md-3">
											
											<?php if($this->config->item("vehicles")){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="system_settings-vehicles" name="system_settings-vehicles" <?php echo $p->{'system_settings-vehicles'} ? "checked" : ''; ?>>
													<label for="system_settings-vehicles" class="padding05"><?= lang('vehicles') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item('fuel')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="system_settings-tanks" name="system_settings-tanks" <?php echo $p->{'system_settings-tanks'} ? "checked" : ''; ?>>
													<label for="system_settings-tanks" class="padding05"><?= lang('tanks') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item("loan")){ ?>	
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="system_settings-frequencies" name="system_settings-frequencies" <?php echo $p->{'system_settings-frequencies'} ? "checked" : ''; ?>>
													<label for="system_settings-frequencies" class="padding05"><?= lang('frequencies') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item('purchase')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="expense_categories-index" name="expense_categories-index" <?php echo $p->{'expense_categories-index'} ? "checked" : ''; ?>>
													<label for="expense_categories-index" class="padding05"><?= lang('expense_categories') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($pos_settings->table_enable==1){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="tables-index" name="tables-index" <?php echo $p->{'tables-index'} ? "checked" : ''; ?>>
													<label for="tables-index" class="padding05"><?= lang('tables') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item('sale')){ ?>
		
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="customer_groups-index" name="customer_groups-index" <?php echo $p->{'customer_groups-index'} ? "checked" : ''; ?>>
													<label for="customer_groups-index" class="padding05"><?= lang('customer_groups') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<?php if($Settings->customer_price==1){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="customer_price-index" name="customer_price-index" <?php echo $p->{'customer_price-index'} ? "checked" : ''; ?>>
														<label for="customer_price-index" class="padding05"><?= lang('customer_price') ?></label>
													</span>
										</div>
										<div class="col-md-3">
												<?php } ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="price_groups-index" name="price_groups-index" <?php echo $p->{'price_groups-index'} ? "checked" : ''; ?>>
													<label for="price_groups-index" class="padding05"><?= lang('price_groups') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<?php if($this->config->item('product_promotions')){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="system_settings-product_promotions" name="system_settings-product_promotions" <?php echo $p->{'system_settings-product_promotions'} ? "checked" : ''; ?>>
														<label for="system_settings-product_promotions" class="padding05"><?= lang('product_promotions') ?></label>
													</span>
												<?php } ?>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item('sale') || $this->config->item('purchase') || $this->config->item('list_sales')){ ?>
												<span style="inline-block">
												
													<input type="checkbox" value="1" class="checkbox" id="payment_terms-index" name="payment_terms-index" <?php echo $p->{'payment_terms-index'} ? "checked" : ''; ?>>
													<label for="payment_terms-index" class="padding05"><?= lang('payment_terms') ?></label>
												</span>
										</div>
										<div class="col-md-3">
		
											<?php } if($this->config->item('saleman_commission')){ ?>
												<span style="inline-block">
												
													<input type="checkbox" value="1" class="checkbox" id="saleman_targets-index" name="saleman_targets-index" <?php echo $p->{'saleman_targets-index'} ? "checked" : ''; ?>>
													<label for="saleman_targets-index" class="padding05"><?= lang('saleman_targets') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item('sale') || $this->config->item('purchase')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="currencies-index" name="currencies-index" <?php echo $p->{'currencies-index'} ? "checked" : ''; ?>>
													<label for="currencies-index" class="padding05"><?= lang('currencies') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($Settings->accounting==1){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="system_settings-inventory_opening_balances" name="system_settings-inventory_opening_balances" <?php echo $p->{'system_settings-inventory_opening_balances'} ? "checked" : ''; ?>>
													<label for="system_settings-inventory_opening_balances" class="padding05"><?= lang('inventory_opening_balances') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="customer_opening_balances-index" name="customer_opening_balances-index" <?php echo $p->{'customer_opening_balances-index'} ? "checked" : ''; ?>>
													<label for="customer_opening_balances-index" class="padding05"><?= lang('customer_opening_balances') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="supplier_opening_balances-index" name="supplier_opening_balances-index" <?php echo $p->{'supplier_opening_balances-index'} ? "checked" : ''; ?>>
													<label for="supplier_opening_balances-index" class="padding05"><?= lang('supplier_opening_balances') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item('sale') || $this->config->item('purchase')){ ?>
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="tax_rates-index`" name="tax_rates-index" <?php echo $p->{'tax_rates-index'} ? "checked" : ''; ?>>
												<label for="tax_rates-index" class="padding05"><?= lang('tax_rates') ?></label>
											</span>
										</div>
										<div class="col-md-3">
											
											<?php } if($this->config->item('sale')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="email_templates-index" name="email_templates-index" <?php echo $p->{'email_templates-index'} ? "checked" : ''; ?>>
													<label for="email_templates-index" class="padding05"><?= lang('email_templates') ?></label>
												</span>
										</div>
										<div class="col-md-3">
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="list_printers-index" name="list_printers-index" <?php echo $p->{'list_printers-index'} ? "checked" : ''; ?>>
													<label for="list_printers-index" class="padding05"><?= lang('printers') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } if($this->config->item('saleman')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="system_settings-salesman_groups" name="system_settings-salesman_groups" <?php echo $p->{'system_settings-salesman_groups'} ? "checked" : ''; ?>>
													<label for="system_settings-salesman_groups" class="padding05"><?= lang('salesman_groups') ?></label>
												</span>
										</div>
										<div class="col-md-3">
											<?php } ?>
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="group_permissions-index" name="group_permissions-index" <?php echo $p->{'group_permissions-index'} ? "checked" : ''; ?>>
												<label for="group_permissions-index" class="padding05"><?= lang('group_permissions') ?></label>
											</span>
										</div>
										<div class="col-md-3">
											<?php  if($this->config->item('backup')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="backups-index" name="backups-index" <?php echo $p->{'backups-index'} ? "checked" : ''; ?>>
													<label for="backups-index" class="padding05"><?= lang('backups') ?></label>
											<?php } ?>
										</div>
											
										</td>
									</tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered table-hover table-striped">

                                <thead>
									<tr>
										<th colspan="6"
											style="font-size: 14px; text-align:left;"><?= lang("report_permissions");?></th>
									</tr>
									<tr>
										<th rowspan="2" class="text-center hidden"><?= lang("module_name"); ?>
										</th>
										<th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
									</tr>
									<tr class="hidden">
										<th class="text-center"><?= lang("view"); ?></th>
										<th class="text-center"><?= lang("add"); ?></th>
										<th class="text-center"><?= lang("edit"); ?></th>
										<th class="text-center"><?= lang("delete"); ?></th>
										<th class="text-center"><?= lang("misc"); ?></th>
									</tr>
                                </thead>
                                
								<tbody>
									<tr>
										<td class="hidden"><?= lang("reports"); ?></td>
										<td colspan="5">
										<div class="container-fluid">
											<?php if($this->config->item('inventory')) { ?>
												<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="warehouse_stock" name="reports-warehouse_stock" <?php echo $p->{'reports-warehouse_stock'} ? "checked" : ''; ?>>
														<label for="warehouse_stock" class="padding05"><?= lang('warehouse_stock') ?></label>
													
													</span>
												</div>
												<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="best_sellers" name="reports-best_sellers" <?php echo $p->{'reports-best_sellers'} ? "checked" : ''; ?>>
														<label for="best_sellers" class="padding05"><?= lang('best_sellers') ?></label>
													</span>
												</div>
												<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="quantity_alerts" name="reports-quantity_alerts" <?php echo $p->{'reports-quantity_alerts'} ? "checked" : ''; ?>>
														<label for="quantity_alerts" class="padding05"><?= lang('quantity_alerts') ?></label>
													</span>
												</div>
												<div class="col-md-3"> 
												<?php if($Settings->product_expiry){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="expiry_alerts" name="reports-expiry_alerts" <?php echo $p->{'reports-expiry_alerts'} ? "checked" : ''; ?>>
														<label for="expiry_alerts" class="padding05"><?= lang('expiry_alerts') ?></label>
													</span>
												</div>
												<div class="col-md-3"> 
												<?php } if($Settings->product_license){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="product_license_alerts" name="reports-product_license_alerts" <?php echo $p->{'reports-product_license_alerts'} ? "checked" : ''; ?>>
														<label for="product_license_alerts" class="padding05"><?= lang('product_license_alerts') ?></label>
													</span>
												<?php } ?>
												</div>

												<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="brands" name="reports-brands" <?php echo $p->{'reports-brands'} ? "checked" : ''; ?>>
														<label for="brands" class="padding05"><?= lang('brands') ?></label>
													</span>
												</div>
											<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="categories" name="reports-categories" <?php echo $p->{'reports-categories'} ? "checked" : ''; ?>>
														<label for="categories" class="padding05"><?= lang('categories') ?></label>
													</span>
											</div>

											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="inventory_in_out" name="reports-inventory_in_out" <?php echo $p->{'reports-inventory_in_out'} ? "checked" : ''; ?>>
													<label for="inventory_in_out" class="padding05"><?= lang('inventory_in_out') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<?php if($this->config->item('using_stocks')){?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="using_stocks" name="reports-using_stocks" <?php echo $p->{'reports-using_stocks'} ? "checked" : ''; ?>>
														<label for="using_stocks" class="padding05"><?= lang('using_stocks') ?></label>
													</span>
												<?php } ?>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="adjustments" name="reports-adjustments" <?php echo $p->{'reports-adjustments'} ? "checked" : ''; ?>>
													<label for="adjustments" class="padding05"><?= lang('adjustments') ?></label>
												</span>
											</div>

											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="categories_chart" name="reports-categories_chart" <?php echo $p->{'reports-categories_chart'} ? "checked" : ''; ?>>
													<label for="categories_chart" class="padding05"><?= lang('categories_chart') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="products" name="reports-products" <?php echo $p->{'reports-products'} ? "checked" : ''; ?>>
													<label for="products" class="padding05"><?= lang('products') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="variants" name="reports-variants" <?php echo $p->{'reports-product_variants'} ? "checked" : ''; ?>>
													<label for="variants" class="padding05"><?= lang('products_variants') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<?php if($Settings->product_serial){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="product_serial_report" name="reports-product_serial_report" <?php echo $p->{'reports-product_serial_report'} ? "checked" : ''; ?>>
														<label for="product_serial_report" class="padding05"><?= lang('product_serial_report') ?></label>
													</span>
												<?php } ?>
											</div>
											
											<div class="col-md-3"> 
												<?php if($Settings->accounting==1){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="cost_adjustments" name="reports-cost_adjustments" <?php echo $p->{'reports-cost_adjustments'} ? "checked" : ''; ?>>
														<label for="cost_adjustments" class="padding05"><?= lang('cost_adjustments') ?></label>
													</span>
											</div>
											<div class="col-md-3"> 
												<?php } if(!$this->config->item('one_warehouse')){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="transfers" name="reports-transfers" <?php echo $p->{'reports-transfers'} ? "checked" : ''; ?>>
														<label for="transfers" class="padding05"><?= lang('transfers') ?></label>
													</span>
												
												<?php } ?>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item('saleorder')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="po-num_alerts" name="po-num_alerts" <?php echo $p->{'po-num_alerts'} ? "checked" : ''; ?>>
													<label for="po-num_alerts" class="padding05"><?= lang('so_alerts') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item('purchase_order')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="po-num_alerts" name="so-num_alerts" <?php echo $p->{'so-num_alerts'} ? "checked" : ''; ?>>
													<label for="so-num_alerts" class="padding05"><?= lang('po_alerts') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item('inventory')) { ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="product_sales_report" name="reports-product_sales_report" <?php echo $p->{'reports-product_sales_report'} ? "checked" : ''; ?>>
													<label for="product_sales_report" class="padding05"><?= lang('product_sales_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="product_purchases_report" name="reports-product_purchases_report" <?php echo $p->{'reports-product_purchases_report'} ? "checked" : ''; ?>>
													<label for="product_purchases_report" class="padding05"><?= lang('product_purchases_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<?php if($this->config->item('product_promotions')) { ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="products_promotion_report" name="reports-products_promotion_report" <?php echo $p->{'reports-products_promotion_report'} ? "checked" : ''; ?>>
														<label for="products_promotion_report" class="padding05"><?= lang('products_promotion_report') ?></label>
													</span>
												<?php } ?>
											</div>
											<div class="col-md-3"> 	
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="products_free_report" name="reports-products_free_report" <?php echo $p->{'reports-products_free_report'} ? "checked" : ''; ?>>
													<label for="products_free_report" class="padding05"><?= lang('products_free_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<?php if($this->config->item('fuel')) { ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="reports-fuel_sales" name="reports-fuel_sales" <?php echo $p->{'reports-fuel_sales'} ? "checked" : ''; ?>>
														<label for="reports-fuel_sales" class="padding05"><?= lang('fuel_sales_report') ?></label>
													</span>
											</div>
											<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="reports-tanks" name="reports-tanks" <?php echo $p->{'reports-tanks'} ? "checked" : ''; ?>>
														<label for="reports-tanks" class="padding05"><?= lang('tanks_report') ?></label>
													</span>
											</div>
											<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="reports-fuel_customers_report" name="reports-fuel_customers_report" <?php echo $p->{'reports-fuel_customers_report'} ? "checked" : ''; ?>>
														<label for="reports-fuel_customers_report" class="padding05"><?= lang('fuel_customers_report') ?></label>
													</span>
												<?php } ?>
											</div>
											<div class="col-md-3"> 	
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="product_monthly_sale" name="reports-product_monthly_sale" <?php echo $p->{'reports-product_monthly_sale'} ? "checked" : ''; ?>>
													<label for="product_monthly_sale" class="padding05"><?= lang('product_monthly_sale') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 	
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="product_yearly_sale" name="reports-product_yearly_sale" <?php echo $p->{'reports-product_yearly_sale'} ? "checked" : ''; ?>>
													<label for="product_yearly_sale" class="padding05"><?= lang('product_yearly_sale') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="register" name="reports-register" <?php echo $p->{'reports-register'} ? "checked" : ''; ?>>
													<label for="register" class="padding05"><?= lang('register_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item('pawn')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="pawn" name="reports-pawn" <?php echo $p->{'reports-pawn'} ? "checked" : ''; ?>>
													<label for="pawn" class="padding05"><?= lang('pawn_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 

											<?php } if($this->config->item('agency')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="agency_commission_report" name="reports-agency_commission" <?php echo $p->{'reports-agency_commission'} ? "checked" : ''; ?>>
													<label for="agency_commission_report" class="padding05"><?= lang('agency_commission_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item('saleman')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="saleman_report" name="reports-saleman_report" <?php echo $p->{'reports-saleman_report'} ? "checked" : ''; ?>>
													<label for="saleman_report" class="padding05"><?= lang('saleman_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="saleman_detail_report" name="reports-saleman_detail_report" <?php echo $p->{'reports-saleman_detail_report'} ? "checked" : ''; ?>>
													<label for="saleman_detail_report" class="padding05"><?= lang('saleman_detail_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<?php if($this->config->item('saleman_commission')){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="saleman_commission_report" name="reports-saleman_commission_report" <?php echo $p->{'reports-saleman_commission_report'} ? "checked" : ''; ?>>
														<label for="saleman_commission_report" class="padding05"><?= lang('saleman_commission_report') ?></label>
													</span>
											</div>
											<div class="col-md-3"> 
												<?php } ?>
											<?php } if($this->config->item('sale')) { ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="daily_sales" name="reports-daily_sales" <?php echo $p->{'reports-daily_sales'} ? "checked" : ''; ?>>
													<label for="daily_sales" class="padding05"><?= lang('daily_sales') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="monthly_sales" name="reports-monthly_sales" <?php echo $p->{'reports-monthly_sales'} ? "checked" : ''; ?>>
													<label for="monthly_sales" class="padding05"><?= lang('monthly_sales') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="sales" name="reports-sales" <?php echo $p->{'reports-sales'} ? "checked" : ''; ?>>
													<label for="sales" class="padding05"><?= lang('sales') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="sales_detail" name="reports-sales_detail" <?php echo $p->{'reports-sales_detail'} ? "checked" : ''; ?>>
													<label for="sales_detail" class="padding05"><?= lang('sales_detail_report') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 

												<?php if($this->config->item('deliveries')) {?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="deliveries" name="reports-deliveries" <?php echo $p->{'reports-deliveries'} ? "checked" : ''; ?>>
														<label for="deliveries" class="padding05"><?= lang('deliveries_report') ?></label>
													</span>
											</div>
											<div class="col-md-3"> 
												<?php } ?>
											<?php } if($this->config->item("loan")){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="reports-loans" name="reports-loans" <?php echo $p->{'reports-loans'} ? "checked" : ''; ?>>
													<label for="reports-loans" class="padding05"><?= lang('reports-loans') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="reports-loan_collection" name="reports-loan_collection" <?php echo $p->{'reports-loan_collection'} ? "checked" : ''; ?>>
													<label for="reports-loans" class="padding05"><?= lang('reports-loan_collection') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="reports-loan_disbursement" name="reports-loan_disbursement" <?php echo $p->{'reports-loan_disbursement'} ? "checked" : ''; ?>>
													<label for="reports-loans" class="padding05"><?= lang('reports-loan_disbursement') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } ?>
											<?php if($Settings->installment==1){ ?>
											
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="reports-installments" name="reports-installments" <?php echo $p->{'reports-installments'} ? "checked" : ''; ?>>
													<label for="reports-installments" class="padding05"><?= lang('reports-installments') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="reports-installment_products" name="reports-installment_products" <?php echo $p->{'reports-installment_products'} ? "checked" : ''; ?>>
													<label for="reports-installment_products" class="padding05"><?= lang('reports-installment_products') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="reports-installment_payments" name="reports-installment_payments" <?php echo $p->{'reports-installment_payments'} ? "checked" : ''; ?>>
													<label for="reports-installment_payments" class="padding05"><?= lang('reports-installment_payments') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } ?>
											
											<?php if($this->config->item('ar_ap_aging')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="ar_customer" name="reports-ar_customer" <?php echo $p->{'reports-ar_customer'} ? "checked" : ''; ?>>
													<label for="ar_customer" class="padding05"><?= lang('ar_customer') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="ap_supplier" name="reports-ap_supplier" <?php echo $p->{'reports-ap_supplier'} ? "checked" : ''; ?>>
													<label for="ap_supplier" class="padding05"><?= lang('ap_supplier') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="ar_aging" name="reports-ar_aging" <?php echo $p->{'reports-ar_aging'} ? "checked" : ''; ?>>
													<label for="ar_aging" class="padding05"><?= lang('ar_aging') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="ap_aging" name="reports-ap_aging" <?php echo $p->{'reports-ap_aging'} ? "checked" : ''; ?>>
													<label for="ap_aging" class="padding05"><?= lang('ap_aging') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item("purchase")){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="daily_purchases" name="reports-daily_purchases" <?php echo $p->{'reports-daily_purchases'} ? "checked" : ''; ?>>
													<label for="daily_purchases" class="padding05"><?= lang('daily_purchases') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="monthly_purchases" name="reports-monthly_purchases" <?php echo $p->{'reports-monthly_purchases'} ? "checked" : ''; ?>>
													<label for="monthly_purchases" class="padding05"><?= lang('monthly_purchases') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="purchases" name="reports-purchases" <?php echo $p->{'reports-purchases'} ? "checked" : ''; ?>>
													<label for="purchases" class="padding05"><?= lang('purchases') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="purchases_detail" name="reports-purchases_detail" <?php echo $p->{'reports-purchases_detail'} ? "checked" : ''; ?>>
													<label for="purchases_detail" class="padding05"><?= lang('purchases_detail') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="expenses" name="reports-expenses" <?php echo $p->{'reports-expenses'} ? "checked" : ''; ?>>
													<label for="expenses" class="padding05"><?= lang('expenses') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item("purchase") || $this->config->item("sale") ){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="payments" name="reports-payments" <?php echo $p->{'reports-payments'} ? "checked" : ''; ?>>
													<label for="payments" class="padding05"><?= lang('payments') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="profit_loss" name="reports-profit_loss" <?php echo $p->{'reports-profit_loss'} ? "checked" : ''; ?>>
													<label for="profit_loss" class="padding05"><?= lang('profit_loss') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item("sale")){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="customers" name="reports-customers" <?php echo $p->{'reports-customers'} ? "checked" : ''; ?>>
													<label for="customers" class="padding05"><?= lang('customers') ?></label>
												</span>
											</div>
											<div class="col-md-3"> 
											<?php } if($this->config->item("sale")){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="suppliers" name="reports-suppliers" <?php echo $p->{'reports-suppliers'} ? "checked" : ''; ?>>
													<label for="suppliers" class="padding05"><?= lang('suppliers') ?></label>
												</span>
											<?php } ?>
											</div>
										<div class="col-md-3"> 
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="users" name="reports-users" <?php echo $p->{'reports-users'} ? "checked" : ''; ?>>
												<label for="users" class="padding05"><?= lang('staff_report') ?></label>
											</span>
										</div>
										<div class="col-md-3"> 
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="print_history" name="reports-print_history" <?php echo $p->{'reports-print_history'} ? "checked" : ''; ?>>
												<label for="print_history" class="padding05"><?= lang('print_history') ?></label>
											</span>
										</div>
										<div class="col-md-3"> 
											<?php if($this->config->item('ktv')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="customer_stocks" name="reports-customer_stocks" <?php echo $p->{'reports-customer_stocks'} ? "checked" : ''; ?>>
													<label for="customer_stocks" class="padding05"><?= lang('customer_stocks') ?></label>
												</span>
										</div>
										<div class="col-md-3"> 
											<?php } if($this->config->item('saleman')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="saleman_products" name="reports-saleman_products" <?php echo $p->{'reports-saleman_products'} ? "checked" : ''; ?>>
													<label for="saleman_products" class="padding05"><?= lang('saleman_products') ?></label>
												</span>
										</div>
										<div class="col-md-3"> 
											<?php } ?>
											
											<?php if($pos_settings->table_enable==1){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="bill_details" name="reports-bill_details" <?php echo $p->{'reports-bill_details'} ? "checked" : ''; ?>>
													<label for="reports-bill_details" class="padding05"><?= lang('reports-bill_details') ?></label>
												</span>
											<?php } ?>
										</div>
										<div class="col-md-3"> 
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="audit_trails" name="reports-audit_trails" <?php echo $p->{'reports-audit_trails'} ? "checked" : ''; ?>>
												<label for="audit_trails" class="padding05"><?= lang('audit_trails') ?></label>
											</span>
										</div>
										</div>
											
										</td>
									</tr>
                                </tbody>
                            </table>
						
                            <table class="table table-bordered table-hover table-striped">

                                <thead>
									<tr>
										<th colspan="6"
											style="font-size: 14px; text-align:left;"><?= lang("miscellaneous_permissions");?></th>
									</tr>
									<tr>
										<th rowspan="2" class="text-center hidden"><?= lang("module_name"); ?>
										</th>
										<th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
									</tr>
									<tr class="hidden">
										<th class="text-center"><?= lang("view"); ?></th>
										<th class="text-center"><?= lang("add"); ?></th>
										<th class="text-center"><?= lang("edit"); ?></th>
										<th class="text-center"><?= lang("delete"); ?></th>
										<th class="text-center"><?= lang("misc"); ?></th>
									</tr>
                                </thead>
                                
								<tbody>
									<tr>
										<td class="hidden"><?= lang("misc"); ?></td>
										<td colspan="5">
										<div class="container-fluid">
										<div class="col-md-3"> 
											<span style="inline-block">
												<input type="checkbox" value="1" class="checkbox" id="bulk_actions"
												name="bulk_actions" <?php echo $p->bulk_actions ? "checked" : ''; ?>>
												<label for="bulk_actions" class="padding05"><?= lang('bulk_actions') ?></label>
											</span>
										</div>
										<div class="col-md-3"> 
											<?php if($this->config->item('sale')){ ?>
												<span style="inline-block">
													<input type="checkbox" value="1" class="checkbox" id="edit_price"
													name="edit_price" <?php echo $p->edit_price ? "checked" : ''; ?>>
													<label for="edit_price" class="padding05"><?= lang('edit_price_on_sale') ?></label>
												</span>
										</div>
											<div class="col-md-3"> 
												<?php if(POS){ ?>
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="pos-delete_order"
														name="pos-delete_order"<?php echo $p->{'pos-delete_order'} ? "checked" : ''; ?>>
														<label for="pos-delete_order" class="padding05"><?= lang('pos-delete_order') ?></label>
													</span>
											</div>
												<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="pos-return_order"
														name="pos-return_order"<?php echo $p->{'pos-return_order'} ? "checked" : ''; ?>>
														<label for="pos-return_order" class="padding05"><?= lang('pos-return_order') ?></label>
													</span>
												</div>
												<div class="col-md-3"> 
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="pos-show_items"
														name="pos-show_items"<?php echo $p->{'pos-show_items'} ? "checked" : ''; ?>>
														<label for="pos-show_items" class="padding05"><?= lang('pos-show_items') ?></label>
													</span>
												</div>
												<div class="col-md-3">
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="pos-delete_table"
														name="pos-delete_table"<?php echo $p->{'pos-delete_table'} ? "checked" : ''; ?>>
														<label for="pos-delete_table" class="padding05"><?= lang('pos-delete_table') ?></label>
													</span>
												</div>
												<div class="col-md-3">
													
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="pos-print_bill"
														name="pos-print_bill"<?php echo $p->{'pos-print_bill'} ? "checked" : ''; ?>>
														<label for="pos-print_bill" class="padding05"><?= lang('pos-print_bill') ?></label>
													</span>
												</div>
												<div class="col-md-3">
													
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="pos-move_table"
														name="pos-move_table"<?php echo $p->{'pos-move_table'} ? "checked" : ''; ?>>
														<label for="pos-move_table" class="padding05"><?= lang('pos-move_table') ?></label>
													</span>
												</div>
												<div class="col-md-3">
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="pos-customer_stock"
														name="pos-customer_stock"<?php echo $p->{'pos-customer_stock'} ? "checked" : ''; ?>>
														<label for="pos-customer_stock" class="padding05"><?= lang('pos-customer_stock') ?></label>
													</span>
												</div>
												<div class="col-md-3">
												<?php } ?>	
													<?php } ?>	
													<span style="inline-block">
														<input type="checkbox" value="1" class="checkbox" id="unlimited-print"
														name="unlimited-print"<?php echo $p->{'unlimited-print'} ? "checked" : ''; ?>>
														<label for="unlimited-print" class="padding05"><?= lang('unlimited-print') ?></label>
													</span>
												</div>
										</div>
										</td>
									</tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered table-hover table-striped">

                                <thead>
									<tr>
										<th colspan="6"
											style="font-size: 14px; text-align:left;"><?= lang("category_permissions");?></th>
									</tr>
									<tr>
										<th rowspan="2" class="text-center hidden"><?= lang("module_name"); ?>
										</th>
										<th colspan="5" class="text-center hidden"><?= lang("permissions"); ?></th>
									</tr>
									<tr class="hidden">
										<th class="text-center"><?= lang("view"); ?></th>
										<th class="text-center"><?= lang("add"); ?></th>
										<th class="text-center"><?= lang("edit"); ?></th>
										<th class="text-center"><?= lang("delete"); ?></th>
										<th class="text-center"><?= lang("misc"); ?></th>
									</tr>
                                </thead>
                                
								<tbody>
									<?php if($this->config->item("user_by_category")){ ?>
										<tr>
											<td class="hidden"><?= lang("category"); ?></td>
											<td colspan="5">
												<div class="form-group">
													<?php
													foreach ($categories as $category) {
														$ct[$category->id] = $category->name;
													}
													echo form_dropdown('category[]', $ct, (isset($_POST['category']) ? $_POST['category'] : ($p->categories ? json_decode($p->categories) : '')), 'class="form-control category" id="biller" multiple data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("category") . '"');
													?>
												</div>
											</td>
										</tr>
									<?php } ?>
                                </tbody>
                            </table>

                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><?=lang('update')?></button>
                        </div>
                        <?php echo form_close();
                    } else {
                        echo $this->lang->line("group_x_allowed");
                    }
                } else {
                    echo $this->lang->line("group_x_allowed");
                } ?>


            </div>
        </div>
    </div>
</div>
