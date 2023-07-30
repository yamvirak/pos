<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
if (!empty($variants)) {
    foreach ($variants as $variant) {
        $vars[] = addslashes($variant->name);
    }
} else {
    $vars = array();
}
?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
        $('#category').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        } else {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                                placeholder: "<?= lang('no_subcategory') ?>",
                                data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
				
				<?php if($Settings->accounting == 1){ ?>
					$.ajax({
						type: "get",
						async: false,
						url: "<?= site_url('products/getCategoryAccounts') ?>/" + v,
						dataType: "json",
						success: function (acdata) {
							$("#stock_account").val(acdata.stock_acc);
							$("#adjustment_account").val(acdata.adjustment_acc);
							$("#usage_account").val(acdata.usage_acc);
							$("#convert_account").val(acdata.convert_acc);
							$("#cost_of_sale_account").val(acdata.cost_acc);
							$("#sale_account").val(acdata.sale_acc);
							$("#sale_account_sv").val(acdata.sale_acc);
							
							<?php if($this->config->item("pawn")){ ?>
								$("#pawn_account").val(acdata.pawn_acc);
								$("#pawn_account").change();
							<?php } ?>	
							
							$("#stock_account").change();
							$("#adjustment_account").change();
							$("#usage_account").change();
							$("#convert_account").change();
							$("#cost_of_sale_account").change();
							$("#sale_account").change();
							$("#sale_account_sv").change();
						},
						error: function () {
							bootbox.alert('<?= lang('ajax_error') ?>');
							$('#modal-loading').hide();
						}
					});
				<?php } ?>
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
        });
        $('#code, #serial_number').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_product'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('update_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/edit/" . $product->id, $attrib)
                ?>
                <div class="col-md-5">
                    <div class="form-group">
                        <?= lang("product_type", "type") ?>
                        <?php
                        $opts = $this->config->item("product_types");
						foreach($opts as $opt){
							$pro_types[$opt] = lang($opt);
						}
                        
						echo form_dropdown('type', $pro_types, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                        ?>
                    </div>
					<?php if($this->config->item("concretes")){ ?>
						<div class="form-group" <?= ($product->type != "bom" ? "style='display:none'" : "") ?>>
							<?= lang("stregth", "stregth") ?>
							<?php
								$str_opt[0] = lang("no");
								$str_opt[1] = lang("yes");
								echo form_dropdown('stregth', $str_opt, (isset($_POST['stregth']) ? $_POST['stregth'] : ($product ? $product->stregth : '')), 'class="form-control" id="stregth"');
							?>
						</div>
					<?php } ?>
					<div class="form-group all">
                        <span id="l_product_code"><?= lang("product_code", "code") ?></span>
                        <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code"  required="required"') ?>
                        <span class="help-block"><?= lang('you_scan_your_barcode_too') ?></span>
                    </div>
					
                    <div class="form-group all">
                        <span id="l_product_name"><?= lang("product_name", "name") ?></span>
                        <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required"'); ?>
                    </div>
                    
                    <div class="form-group all" style="display:none !important">
                        <?= lang("barcode_symbology", "barcode_symbology") ?>
                        <?php
                        $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca' => 'UPC-A', 'upce' => 'UPC-E');
                        echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select" id="barcode_symbology" required="required" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group all hidden">
                        <?= lang("brand", "brand") ?>
						<div class="input-group">
							<?php
							$br[''] = "";
							if($brands){
								foreach ($brands as $brand) {
									$br[$brand->id] = $brand->name;
								}
							}
							
							echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand : '')), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
							?>
							<div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
								<a href="<?= site_url('system_settings/add_brand'); ?>" class="external" data-toggle="modal" data-target="#myModal">
									<i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
								</a>
							</div>
						</div>
                    </div>
                    <?php  if($this->config->item('room_rent')) {?>
                    <div class="form-group all">
                        <?= lang("service_types", "service_types") ?>
						<div class="input-group">
							<?php
							$stopt[''] = "";
							if($service_types){
								foreach ($service_types as $service_type) {
									$stopt[$service_type->id] = $service_type->name;
								}
							}
							echo form_dropdown('electricity', $stopt, (isset($_POST['electricity']) ? $_POST['electricity'] : ($product ? $product->electricity : '')), 'class="form-control select" id="service_types" placeholder="' . lang("select") . " " . lang("service_types") . '" style="width:100%"')
							?>
							<div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
								<a href="<?= site_url('rentals_configuration/add_service'); ?>" class="external" data-toggle="modal" data-target="#myModal">
									<i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
								</a>
							</div>
						</div>
                    </div>
                    <?php }?>
                    <div class="form-group all">
                        <?= lang("category", "category") ?>
						<div class="input-group">
							<?php
							$cat[''] = "";
							foreach ($categories as $category) {
								$cat[$category->id] = $category->name;
							}
							echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
							?>
							<div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
								<a href="<?= site_url('system_settings/add_category'); ?>" class="external" data-toggle="modal" data-target="#myModal">
									<i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
								</a>
							</div>
						</div>
                    </div>
                    <div class="form-group all">
                        <?= lang("subcategory", "subcategory") ?>
                        <div class="controls" id="subcat_data"> <?php
                            echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= lang('product_unit', 'unit'); ?>
						<div class="input-group">
							<?php 
							$pu[''] = lang('select').' '.lang('unit');
							if($base_units){
								foreach ($base_units as $bu) {
									$pu[$bu->id] = $bu->name .' ('.$bu->code.')';
								}
							}
							
							?>
							<?= form_dropdown('unit', $pu, set_value('unit', $product->unit), 'class="form-control tip" required="required" id="unit" style="width:100%;"'); ?>
							<div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
								<a href="<?= site_url('system_settings/add_unit'); ?>" class="external" data-toggle="modal" data-target="#myModal">
									<i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
								</a>
							</div>
						</div>
					</div>
                    <div class="form-group standard hide_raw">
                        <?= lang('default_sale_unit', 'default_sale_unit'); ?>
                        <?php
                        $uopts[''] = lang('select').' '.lang('unit');
						if($subunits){
							foreach ($subunits as $sunit) {
								$uopts[$sunit->id] = $sunit->name .' ('.$sunit->code.')';
							}
						}
                        ?>
                        <?= form_dropdown('default_sale_unit', $uopts, $product->sale_unit, 'class="form-control" id="default_sale_unit" style="width:100%;"'); ?>
                    </div>
                    <div class="form-group standard">
                        <?= lang('default_purchase_unit', 'default_purchase_unit'); ?>
                        <?= form_dropdown('default_purchase_unit', $uopts, $product->purchase_unit, 'class="form-control" id="default_purchase_unit" style="width:100%;"'); ?>
                    </div>
					
					<?php 
					$currency_rate = 1;
					if($this->config->item('product_currency')==true){
						$currency_rate = $product->currency_rate;
					?>
						<div class="form-group">
							<?= lang("product_currency", "currency_code") ?>
							<?php
								$copts = array();
								foreach ($currencies as $currency) {
									$copts[$currency->code] = $currency->name;
								}
							?>
							<?= form_dropdown('currency_code', $copts, ($product->currency_code?$product->currency_code:$Settings->default_currency), 'class="form-control" id="currency_code" style="width:100%;" required="required" '); ?>
						</div>
					<?php } ?>
					
					
					<?php
					$class_cost = 'standard';
					$display_cost = '';
					if($Settings->accounting == 1 && $product->quantity <> 0){
						$display_cost = 'display:none !important';
						$class_cost = '';
					}
						if($Owner || $Admin) {  ?>
						
							<div class="form-group <?= $class_cost ?>" style="<?= $display_cost ?>">
								<?= lang("product_cost", "cost") ?>
								<?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->cus->formatDecimal($product->cost,16)-0 : 0)), 'class="form-control tip" id="cost" required="required"') ?>
							</div>
							<div class="form-group all standard hide_raw services div_price">
								<?= lang("product_price", "price") ?>
								<?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->cus->formatDecimal($product->price * $currency_rate,16)-0 : 0)), 'class="form-control tip" id="price" required="required"') ?>
							</div>
						<?php } else { 
							$display_cost = 'display:none !important';
							$display_price = 'display:none !important';
							$class_price = '';
							$class_cost = '';
							
							if($this->session->userdata('show_cost') && $Settings->accounting != 1) { 
								$display_cost = '';
								$class_cost = 'standard';
							}
							if($this->session->userdata('show_price')) {
								$display_price = '';
								$class_price = 'standard div_price';
							}
						
						
						?>
								
								<div class="form-group <?= $class_cost ?>" style="<?= $display_cost ?>">
									<?= lang("product_cost", "cost") ?>
									<?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->cus->formatDecimal($product->cost,16) : 0)), 'class="form-control tip" id="cost" required="required"') ?>
								</div>
								<div class="form-group all <?= $class_price ?> hide_raw services" style="<?= $display_price ?>">
									<?= lang("product_price", "price") ?>
									<?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->cus->formatDecimal($product->price * $currency_rate,16) : 0)), 'class="form-control tip" id="price" required="required"') ?>
								</div>
							
						<?php }
					
					if($this->Settings->product_additional==1){ ?>
						<div class="form-group standard">
                            <?= lang("product_additional", "product_additional") ?>
							<?= form_input('product_additional', (isset($_POST['product_additional']) ? $_POST['product_additional'] : ($product ? $this->cus->formatQuantity($product->product_additional) : 0)), 'class="form-control tip" id="product_additional"') ?>
                        </div>
					<?php } if($this->Settings->accounting_method=='3'){ 
						$am = array(0 => 'FIFO (First In First Out)', 1 => 'LIFO (Last In First Out)', 2 => 'AVCO (Average Cost Method)');
					?>
						<div class="form-group standard">
							<?= lang("accounting_method", "accounting_method") ?>
							<?= form_dropdown('accounting_method', $am, (isset($_POST['accounting_method']) ? $_POST['accounting_method'] : ($product ? $product->accounting_method : 2)), 'class="form-control tip" id="accounting_method" ') ?>
						</div>
							
                    <?php } if($this->Settings->product_serial==1){ ?>
                        <div class="form-group standard">
                            <input type="checkbox" <?= ($product->seperate_qty==1 ? 'checked' : '') ?> class="checkbox" value="1" name="seperate_qty" id="seperate_qty" <?= $this->input->post('seperate_qty') ? 'checked="checked"' : ''; ?>>
                            <label for="seperate_qty" class="padding05">
                                <?= lang('seperate_qty'); ?>
                            </label>
                        </div>
                    <?php } ?>
					
                    <div class="form-group standard hide_raw">
                        <input type="checkbox" class="checkbox" value="1" name="promotion" id="promotion" <?= $this->input->post('promotion') ? 'checked="checked"' : ''; ?>>
                        <label for="promotion" class="padding05">
                            <?= lang('promotion'); ?>
                        </label>
                    </div>

                    <div id="promo"<?= $product->promotion ? '' : ' style="display:none;"'; ?>>
                        <div class="well well-sm">
                            <div class="form-group">
                                <?= lang('promo_price', 'promo_price'); ?>
                                <?= form_input('promo_price', set_value('promo_price', $product->promo_price ? $this->cus->formatDecimal($product->promo_price) : ''), 'class="form-control tip" id="promo_price"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?= form_input('start_date', set_value('start_date', $product->start_date ? $this->cus->hrsd($product->start_date) : ''), 'class="form-control tip date" id="start_date"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?= form_input('end_date', set_value('end_date', $product->end_date ? $this->cus->hrsd($product->end_date) : ''), 'class="form-control tip date" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group all">
                            <?= lang("product_tax", "tax_rate") ?>
                            <?php
                            $tr[""] = "";
                            foreach ($tax_rates as $tax) {
                                $tr[$tax->id] = $tax->name;
                            }
                            echo form_dropdown('tax_rate', $tr, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group all">
                            <?= lang("tax_method", "tax_method") ?>
                            <?php
                            $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                            echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                            ?>
                        </div>
                    <?php } if($pos_settings->table_enable == 1){ ?>
					 <div class="form-group">
                        <input name="adjustment_qty" type="checkbox" class="checkbox" id="adjustment_qty" value="1" <?= $product->adjustment_qty == 1 ? 'checked="checked"' : '' ?>/>
                        <label for="extras" class="padding05"><?= lang('adjustment_qty') ?></label>
                    </div>
					<?php } ?>
                    <div class="form-group standard">
                        <?= lang("alert_quantity", "alert_quantity") ?>
                        <div
                            class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->cus->formatDecimal($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                            <span class="input-group-addon">
                            <input type="checkbox" name="track_quantity" id="inlineCheckbox1"
                                   value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                        </span>
                        </div>
                    </div>

                    <div class="form-group all">
                        <?= lang("product_image", "product_image") ?>
                        <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>" name="product_image" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                    </div>

                    <div class="form-group all">
                        <?= lang("product_gallery_images", "images") ?>
                        <input id="images" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile[]" multiple="true" data-show-upload="false"
                               data-show-preview="false" class="form-control file" accept="image/*">
                    </div>
                    <div id="img-details"></div>
                </div>
                <div class="col-md-6 col-md-offset-1">
				
					<div class="form-group">
						<?= lang('inactive', 'inactive'); ?>
						<?php
							$in_opts[0] = lang('no');
							$in_opts[1] = lang('yes');
						?>
						<?= form_dropdown('inactive', $in_opts, $product->inactive, 'class="form-control" id="inactive" style="width:100%;"'); ?>
					</div>
				
					<?php if($this->config->item('convert')){?>
						<div class="standard">
							<div class="form-group">
								<?= lang('add_convert_item', 'add_convert_item'); ?>
								<?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item3" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
							</div>
							<div class="control-group table-group">
								<label class="table-label" for="convert_items"><?= lang("convert_items"); ?></label>
								<div class="controls table-controls">
									<table id="prTable3" class="table items table-striped table-bordered table-condensed table-hover">
										<thead>
										<tr>
											<th class="col-md-5 col-sm-5 col-xs-5"><?= lang('product') ?></th>                                        
											<th class="col-md-1 col-sm-1 col-xs-1 text-center"><?= lang("quantity") ?></th>
											<th class="col-md-1 col-sm-1 col-xs-1 text-center"><?= lang("cost") ?></th>
											<th class="col-md-1 col-sm-1 col-xs-1 text-center">
												<i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
											</th>
										</tr>
										</thead>
										<tbody>
											<?php
												$convert_td = '';
												if(isset($convert_items) && $convert_items){
													foreach($convert_items as $convert_item){
														$row_no = $convert_item->product_id;
														$convert_td .='<tr id="row_'.$row_no.'" class="item_'.$row_no.'" data-item-id="'.$row_no.'">
																			<td><input name="convert_item_id[]" type="hidden" value="'.$row_no.'"><input name="convert_item_code[]" type="hidden" value="'.$convert_item->code.'"><input name="convert_item_name[]" type="hidden" value="'.$convert_item->name.'"><span id="name_'.$row_no.'">'.$convert_item->code.' - '.$convert_item->name.'</span></td>
																			<td><input name="convert_item_unit[]" type="hidden" value="'.$convert_item->unit.'"><input class="text-center form-control convert_item_qty" name="convert_item_qty[]" value="'.$convert_item->quantity.'"/></td>
																			<td><input readonly="true" class="text-center form-control convert_cost"  value="'.$convert_item->cost.'"/></td>
																			<td class="text-center"><i class="fa fa-times tip del_convert" id="'.$row_no.'" title="Remove" style="cursor:pointer;"></i></td>
																		</tr>';			
														$old_convert_items[$row_no] = array(
																					"id" => $row_no,
																					"code" => $convert_item->code,
																					"name" => $convert_item->name,
																					"qty" => $convert_item->quantity,
																					"cost" => $convert_item->cost,
																					"unit" => $convert_item->unit,
																				);			
													}
													?>
											<?php	
												}
												echo $convert_td; 
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					
					<?php } ?>
				
					<?php if($Settings->cbm == 1){ ?>
						<div class="standard">
							<strong><?= lang("cbm") ?></strong><br>
							<table class="table table-bordered table-condensed table-striped" style=" margin-bottom: 0; margin-top: 10px;">
								<thead>
									<tr>
										<th><?= lang('length') ?> (cm)</th>
										<th><?= lang('width') ?> (cm)</th>
										<th><?= lang('height') ?> (cm)</th>
										<th><?= lang('weight') ?> (kg)</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><input type="text" value="<?= ($product->p_length > 0 ? $product->p_length:0) ?>" name="p_length" class="form-control text-right" id="p_length"/></td>
										<td><input type="text" value="<?= ($product->p_width > 0 ? $product->p_width:0) ?>"name="p_width" class="form-control text-right" id="p_width"/></td>
										<td><input type="text" value="<?= ($product->p_height > 0 ? $product->p_height:0) ?>"name="p_height" class="form-control text-right" id="p_height"/></td>
										<td><input type="text" value="<?= ($product->p_weight > 0 ? $product->p_weight:0) ?>"name="p_weight" class="form-control text-right" id="p_weight"/></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="clearfix"></div>
					<?php } ?>
                    <div class="standard1">
                        <div id="attrs"></div>
                        <div>
                            <div id="box_product_unit" class="table-responsive">
							<strong><?= lang("product_unit") ?></strong><br>
                                <table class="table table-bordered table-condensed table-striped" style=" margin-bottom: 0; margin-top: 10px;">
                                    <thead>
										<tr class="active">
											<th><?= lang('name') ?></th>
											<th><?= lang('quantity') ?></th>
											<th><?= lang('price') ?></th>
										</tr>
                                    </thead>
                                    <tbody id="unit_body">
									<?php
										if($product_unit){
											foreach($product_unit as $productunit_row){
												echo'<tr>
														<td>'.$productunit_row->name.'</td>
														<td><input type="hidden" name="product_unit_id[]" value="'.$productunit_row->id.'"/>
															<input '.($productunit_row->id==$product->unit?'readonly':'').' class="form-control" name="product_unit_qty[]" placeholder="'.lang('quantity').'" type="text" value="'.$productunit_row->unit_qty.'"/>
														</td>
														<td>
															<input '.($productunit_row->id==$product->unit?'readonly':'').' type="text" name="product_unit_price[]" class="form-control" placeholder="'.lang('price').'" value="'.$productunit_row->unit_price.'" />
														</td>
													</tr>';
											}
										}
										
									
									?>
									</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div id="attrs"></div>
                        <div class="well well-sm">
                            <?php
                            if ($product_options) { ?>
                            <table class="table table-bordered table-condensed table-striped"
                                   style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?> margin-top: 10px;">
                                <thead>
                                <tr class="active">
                                    <th><?= lang('name') ?></th>
                                    <th><?= lang('warehouse') ?></th>
                                    <th><?= lang('quantity') ?></th>
                                    <th><?= lang('price_addition') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($product_options as $option) {
                                    echo '<tr><td class="col-xs-3"><input type="hidden" name="attr_id[]" value="' . $option->id . '"><span>' . $option->name . '</span></td><td class="code text-center col-xs-3"><span>' . $option->wh_name . '</span></td><td class="quantity text-center col-xs-2"><span>' . $this->cus->formatQuantity($option->wh_qty) . '</span></td><td class="price text-right col-xs-2">' . $this->cus->formatMoney($option->price) . '</td></tr>';
                                }
                            ?>
                            </tbody>
                            </table>
                            <?php
                            }
                            if ($product_variants) { ?>
                                <h3 class="bold"><?=lang('update_variants');?></h3>
                                <table class="table table-bordered table-condensed table-striped" style="margin-top: 10px;">
                                <thead>
                                <tr class="active">
                                    <th class="col-xs-8"><?= lang('name') ?></th>
                                    <th class="col-xs-4"><?= lang('price_addition') ?></th>
									<th class="col-xs-4"><i class="fa fa-trash-o"></i></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($product_variants as $pv) {
                                     echo '<tr><td class="col-xs-3"><input type="hidden" name="variant_id_' . $pv->id . '" value="' . $pv->id . '"><input type="text" name="variant_name_' . $pv->id . '" value="' . $pv->name . '" class="form-control"></td><td class="price text-right col-xs-2"><input type="text" name="variant_price_' . $pv->id . '" value="' . $pv->price . '" class="form-control"></td><td><a href="#" id="'.$pv->id.'" class="remove-variant"><i class="fa fa-remove"></i></a></td></tr>';
									
								}
                                ?>
                                </tbody>
                                </table>
                                <?php
                            }
                            ?>
							
							<script type="text/javascript">
								$(function(){
									$(".remove-variant").click(function(){																				
										var id = $(this).attr("id");
										var parent = $(this).parent().parent();
										var booleans = false;
										$.ajax({
											url : "<?= site_url("products/delete_variant") ?>",
											type : "GET",
											//dataType : "JSON",
											data : { id : id },
											success : function(){													
												parent.remove();
											},
											error:function(e){
												window.alert(e.responseText);
												return false;
											}
										});
										return false;
									});
								})
							</script>
							<?php if($Settings->attributes==1){ ?>
								<div class="form-group">
									<input type="checkbox" class="checkbox" name="attributes" id="attributes" <?= $this->input->post('attributes') ? 'checked="checked"' : ''; ?>>
									<label for="attributes" class="padding05"><?= lang('add_more_variants'); ?></label>
									<?= lang('eg_sizes_colors'); ?>
								</div>
							<?php } ?>
                            <div id="attr-con" <?= $this->input->post('attributes') ? '' : 'style="display:none;"'; ?>>
                                <div class="form-group" id="ui" style="margin-bottom: 0;">
                                    <div class="input-group">
                                        <?php
                                        echo form_input('attributesInput', '', 'class="form-control select-tags" id="attributesInput" placeholder="' . $this->lang->line("enter_attributes") . '"'); ?>
                                        <div class="input-group-addon" style="padding: 2px 5px;">
                                            <a href="#" id="addAttributes">
                                                <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                                <div class="table-responsive">
                                    <table id="attrTable" class="table table-bordered table-condensed table-striped" style="margin-bottom: 0; margin-top: 10px;">
                                        <thead>
                                            <tr class="active">
                                                <th><?= lang('name') ?></th>
                                                <th><?= lang('warehouse') ?></th>
                                               
                                                <th><?= lang('price_addition') ?></th>
                                                <th><i class="fa fa-times attr-remove-all"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody><?php
                                            if ($this->input->post('attributes')) {
                                                $a = sizeof($_POST['attr_name']);
                                                for ($r = 0; $r <= $a; $r++) {
                                                    if (isset($_POST['attr_name'][$r]) && (isset($_POST['attr_warehouse'][$r]) || isset($_POST['attr_quantity'][$r]))) {
                                                        echo '<tr class="attr">
                                                        <td><input type="hidden" name="attr_name[]" value="' . $_POST['attr_name'][$r] . '"><span>' . $_POST['attr_name'][$r] . '</span></td>
                                                        <td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . (isset($_POST['attr_warehouse'][$r]) ? $_POST['attr_warehouse'][$r] : '') . '"><input type="hidden" name="attr_wh_name[]" value="' . (isset($_POST['attr_wh_name'][$r]) ? $_POST['attr_wh_name'][$r] : '') . '"><span>' . (isset($_POST['attr_wh_name'][$r]) ? $_POST['attr_wh_name'][$r] : '') . '</span></td>
                                                        <td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $_POST['attr_quantity'][$r] . '"><span>' . $_POST['attr_quantity'][$r] . '</span></td>
                                                        <td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $_POST['attr_price'][$r] . '"><span>' . $_POST['attr_price'][$r] . '</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td>
                                                    </tr>';
                                                }
                                            }
                                        }
                                        ?></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                    </div>
					
					<div class="bom" style="display:none;">

                        <div class="form-group">
                            <?= lang("add_product", "add_item") . ' (' . lang('not_with_variants') . ')'; ?>
                            <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item_bom" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                        </div>
                        <div class="control-group table-group">
                            <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>

                            <div class="controls table-controls">
                                <table id="bomTable"
                                       class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-5 col-sm-5 col-xs-5"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                        <th class="col-md-2 col-sm-3 col-xs-3"><?= lang("type"); ?></th>
                                        <th class="col-md-2 col-sm-2 col-xs-2"><?= lang("quantity"); ?></th>
										<th class="col-md-3 col-sm-3 col-xs-3"><?= lang("unit"); ?></th>
                                        <th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                            <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
					
					<div class="combo2" style="display:none;">
                        <div class="form-group">
                            <?= lang("add_product", "add_item") . ' (' . lang('not_with_variants') . ')'; ?>
                            <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item2" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                        </div>
                        <div class="control-group table-group">
                            <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>
                            <div class="controls table-controls">
                                <table id="prTable2"
                                       class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-5 col-sm-5 col-xs-5"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>                                        
                                        <th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                            <?= lang("variant") ?>
                                        </th>
										<th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                            <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
					
                    <div class="combo" style="display:none;">

                        <div class="form-group">
                            <?= lang("add_product", "add_item") . ' (' . lang('not_with_variants') . ')'; ?>
                            <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                        </div>
                        <div class="control-group table-group">
                            <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>
                            <!--<div class="row"><div class="ccol-md-10 col-sm-10 col-xs-10"><label class="table-label" for="combo"><?= lang("combo_products"); ?></label></div>
                            <div class="ccol-md-2 col-sm-2 col-xs-2"><div class="form-group no-help-block" style="margin-bottom: 0;"><input type="text" name="combo" id="combo" value="" data-bv-notEmpty-message="" class="form-control" /></div></div></div>-->
                            <div class="controls table-controls">
                                <table id="prTable"
                                       class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-5 col-sm-5 col-xs-5"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                        <th class="col-md-2 col-sm-2 col-xs-2"><?= lang("quantity"); ?></th>
                                        <th class="col-md-3 col-sm-3 col-xs-3"><?= lang("unit_price"); ?></th>
										<th class="col-md-3 col-sm-3 col-xs-3"><?= lang("variant"); ?></th>
                                        <th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                            <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="digital" style="display:none;">
                        <?php
                        if (filter_var($product->file, FILTER_VALIDATE_URL) === FALSE) {
                            $file = $product->file;
                            $file_link = '';
                        } else {
                            $file_link = $product->file;
                            $file = '';
                        }
                        ?>
                        <div class="form-group digital">
                            <?= lang("digital_file", "digital_file") ?>
                            <input id="digital_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="digital_file" data-show-upload="false"
                                   data-show-preview="false" class="form-control file">
                        </div>
                        <div class="form-group digital">
                            <?= lang('file_link', 'file_link'); ?>
                            <?= form_input('file_link', $file_link, 'class="form-control" id="file_link"'); ?>
                        </div>
                    </div>

                    <div class="form-group standard">
                        <div class="form-group">
                            <?= lang("supplier", "supplier") ?>
                            <button type="button" class="btn btn-primary btn-xs" id="addSupplier"><i class="fa fa-plus"></i></button>
                        </div>
                        <div class="row" id="supplier-con">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <?php
                                    echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && ! empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <?= form_input('supplier_part_no', (isset($_POST['supplier_part_no']) ? $_POST['supplier_part_no'] : ""), 'class="form-control tip" id="supplier_part_no" placeholder="' . lang('supplier_part_no') . '"'); ?>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <?= form_input('supplier_price', (isset($_POST['supplier_price']) ? $_POST['supplier_price'] : ""), 'class="form-control tip" id="supplier_price" placeholder="' . lang('supplier_price') . '"'); ?>
                                </div>
                            </div>
                        </div>
                        <div id="ex-suppliers"></div>
                    </div>
					
					<?php if($Settings->accounting == 1){ ?>
						<div class="form-group standard">
							<div class="form-group">
								<?= lang("account", "account") ?>
							</div>
							<div class="form-group">
								<?= lang("stock_account", "stock_account") ?>
								<select name="stock_account" class="form-control select" id="stock_account" style="width:100%">
									<option value=""><?= lang('select_stock_account') ?></option>
									<?= $stock_accounts ?>
								</select>
							</div>
							<div class="form-group">
								<?= lang("adjustment_account", "adjustment_account") ?>
								<select name="adjustment_account" class="form-control select" id="adjustment_account" style="width:100%">
									<option value=""><?= lang('select_adjustment_account') ?></option>
									<?= $adjustment_accounts ?>
								</select>
							</div>
							<div class="form-group">
								<?= lang("usage_account", "usage_account") ?>
								<select name="usage_account" class="form-control select" id="usage_account" style="width:100%">
									<option value=""><?= lang('select_usage_account') ?></option>
									<?= $usage_accounts ?>
								</select>
							</div>
							<?php if($this->config->item('convert')){?>
								<div class="form-group">
									<?= lang("convert_account", "convert_account") ?>
									<select name="convert_account" class="form-control select" id="convert_account" style="width:100%">
										<option value=""><?= lang('select_convert_account') ?></option>
										<?= $convert_accounts ?>
									</select>
								</div>
							<?php } ?>
							<div class="form-group standard hide_raw">
								<?= lang("cost_of_sale_account", "cost_of_sale_account") ?>
								<select name="cost_of_sale_account" class="form-control select" id="cost_of_sale_account" style="width:100%">
									<option value=""><?= lang('select_cost_of_sale_account') ?></option>
									<?= $cost_accounts ?>
								</select>
							</div>

							
							<div class="form-group standard hide_raw">
								<?= lang("sale_account", "sale_account") ?>
								<select name="sale_account" class="form-control select" id="sale_account" style="width:100%">
									<option value=""><?= lang('select_sale_account') ?></option>
									<?= $sale_accounts ?>
								</select>
							</div>
							<?php if($this->config->item("pawn")){ ?>
								<div class="form-group standard hide_raw">
									<?= lang("pawn_account", "pawn_account") ?>
									<select name="pawn_account" class="form-control select" id="pawn_account" style="width:100%">
										<option value=""><?= lang('select_pawn_account') ?></option>
										<?= $pawn_accounts ?>
									</select>
								</div>
							<?php } ?>
						</div>
						
						
						<div <?= ($product->type=='service' || $product->type=='bom'?'':'style="display:none"')?> class="form-group services">
							
							<div class="form-group">
								<?= lang("account", "account") ?>
							</div>
							<div class="form-group">
								<?= lang("sale_account", "sale_account") ?>
								<select name="sale_account_sv" class="form-control select" id="sale_account_sv" style="width:100%">
									<option value=""><?= lang('select_sale_account') ?></option>
									<?= $sale_accounts ?>
								</select>
							</div>
							
							<?php if($this->config->item("pawn")){ ?>
								<div class="form-group">
									<?= lang("pawn_account", "pawn_account") ?>
									<select name="pawn_account_sv" class="form-control select" id="pawn_account_sv" style="width:100%">
										<option value=""><?= lang('select_pawn_account') ?></option>
										<?= $pawn_accounts ?>
									</select>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
					
                </div>
				
				<?php if ($Settings->product_formulation) { ?>	
					<div class="col-md-12">
						<div class="form-group">
							<input type="checkbox" class="checkbox" name="formulation" id="formulation" <?= $this->input->post('formulation') ? 'checked="checked"' : ''; ?>>
							<label for="formulation" class="padding05"><?= lang('formulation'); ?></label>
						</div>
						<div class="control-group table-group product_formulation" style="display:none">
							<div class="controls table-controls">
								<table id="formulationTable"
									   class="table items table-striped table-bordered table-condensed table-hover">
									<thead>
									<tr>
                                        <th><?= lang("width"); ?> (x)</th>
										<th><?= lang("height"); ?> (x)</th>
										<th><?= lang("square"); ?> (x)</th>
										<th><?= lang("quantity"); ?> (x)</th>
										<th><?= lang("field"); ?> </th>
										<th><?= lang("operation"); ?></th>
										<th><?= lang("calculation"); ?></th>
										<th>
											<i id="add_formulation" class="fa fa-plus" style="opacity:0.5; filter:alpha(opacity=50);"></i>
										</th>
									</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						</div>
					</div>
				<?php } ?>

                <div class="col-md-12">

                    <div class="form-group">
                        <input name="cf" type="checkbox" class="checkbox" id="extras" value="" checked="checked"/><label
                            for="extras" class="padding05"><?= lang('custom_fields') ?></label>
                    </div>
                    <div class="row" id="extras-con">

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf1', 'cf1') ?>
                                <?= form_input('cf1', (isset($_POST['cf1']) ? $_POST['cf1'] : ($product ? $product->cf1 : '')), 'class="form-control tip" id="cf1"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf2', 'cf2') ?>
                                <?= form_input('cf2', (isset($_POST['cf2']) ? $_POST['cf2'] : ($product ? $product->cf2 : '')), 'class="form-control tip" id="cf2"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf3', 'cf3') ?>
                                <?= form_input('cf3', (isset($_POST['cf3']) ? $_POST['cf3'] : ($product ? $product->cf3 : '')), 'class="form-control tip" id="cf3"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf4', 'cf4') ?>
                                <?= form_input('cf4', (isset($_POST['cf4']) ? $_POST['cf4'] : ($product ? $product->cf4 : '')), 'class="form-control tip" id="cf4"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf5', 'cf5') ?>
                                <?= form_input('cf5', (isset($_POST['cf5']) ? $_POST['cf5'] : ($product ? $product->cf5 : '')), 'class="form-control tip" id="cf5"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf6', 'cf6') ?>
                                <?= form_input('cf6', (isset($_POST['cf6']) ? $_POST['cf6'] : ($product ? $product->cf6 : '')), 'class="form-control tip" id="cf6"') ?>
                            </div>
                        </div>


                    </div>


                    <div class="form-group all">
                        <?= lang("product_details", "product_details") ?>
                        <?= form_textarea('product_details', (isset($_POST['product_details']) ? $_POST['product_details'] : ($product ? $product->product_details : '')), 'class="form-control" id="product_details"'); ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_details_for_invoice", "details") ?>
                        <?= form_textarea('details', (isset($_POST['details']) ? $_POST['details'] : ($product ? $product->details : '')), 'class="form-control" id="details"'); ?>
                    </div>

                    <div class="form-group">
                        <?php echo form_submit('edit_product', $this->lang->line("edit_product"), 'class="btn btn-primary"'); ?>
                    </div>

                </div>
                <?= form_close(); ?>

            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
        var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
        var items = {};
		var for_items = {};
		var items3 = {};
		
		<?php if(isset($old_convert_items) && $old_convert_items){ ?>

			var ci = <?= json_encode($old_convert_items) ?>;
			$.each(ci, function() { 
				item_id = this.id;
				if (items3[item_id]) {
					items3[item_id].qty = (parseFloat(items3[item_id].qty) + 1).toFixed(2);
				} else {
					items3[item_id] = this;
				}
			});
			
		<?php }
		
		
		if(isset($formulation_items) && $formulation_items) {
			$formulation_items_detail = array();
			$c = rand(100000, 9999999);
			foreach($formulation_items as $item){
				$options = $this->products_model->getUnitbyProduct($item->for_product_id);
				$formulation_items_detail[$c] = array(
													"id" => $c,
													"product_id" => $item->for_product_id,
													"for_condition" => $item->for_condition,
													"for_condition_h" => $item->for_condition_h,
													"for_condition_s" => $item->for_condition_s,
													"for_condition_q" => $item->for_condition_q,
													"for_width" => $item->for_width,
													"for_type" => $item->for_type,
													"for_height" => $item->for_height,
													"for_square" => $item->for_square,
													"for_qty" => $item->for_qty,
													"for_field" => $item->for_field,
													"for_operation" => $item->for_operation,
													"for_caculation" => $item->for_caculation,
												);
				$c++;							
			}
            echo '
                var ci = '.json_encode($formulation_items_detail).';
                $.each(ci, function() { add_formulation_product_item(this); });
                ';
        }
		
		if($bom_items) {
			$bom_items_detail = array();
			foreach($bom_items as $item){
                $c = rand(100000, 9999999);
				$options = $this->products_model->getUnitbyProduct($item->product_id);
				$bom_items_detail[$c] = array(
                                                    "row_id" => $c,
													"id" => $item->product_id,
                                                    "bom_type" => $item->bom_type,
													"code" => $item->code,
													"name" => $item->name,
													"qty" => $item->quantity,
													"options" => $options,
													"unit_id" => $item->unit_id,
												);
			}
            echo '
                var ci = '.json_encode($bom_items_detail).';
                $.each(ci, function() { add_bom_product_item(this); });
                ';
        }
		
        if($combo_items) {
			$combo_items_detail = array();
			foreach($combo_items as $item){
				$variant_details = $this->products_model->getVariantByProductID($item->id);
				$combo_items_detail[$item->id] = array(
													"id" => $item->id,
													"code" => $item->code,
													"name" => $item->name,
													"qty" => $item->qty,
													"price" => $item->price,
													"option_id" => $item->option_id,
													"variants" => $variant_details
												);
			}
            echo '
                var ci = '.json_encode($combo_items_detail).';
                $.each(ci, function() { add_product_item(this); });
                ';
        }
        ?>
		
		<?php
		if($digital_items) {
			$digital_items_detail = array();
			foreach($digital_items as $item){
				$variant_details = $this->products_model->getVariantByProductID($item->id);
				$digital_items_detail[$item->id] = array(
													"id" => $item->id,
													"code" => $item->code,
													"name" => $item->name,
													"qty" => $item->qty,
													"price" => $item->price,
													"option_id" => $item->option_id,
													"variants" => $variant_details
												);
			}
			echo '
					var ci = '.json_encode($digital_items_detail).';
					$.each(ci, function() {
						add_product_item2(this); 
					});
				';
			} 
		?>
		
        <?=isset($_POST['cf']) ? '$("#extras").iCheck("check");': '' ?>
        $('#extras').on('ifChecked', function () {
            $('#extras-con').slideDown();
        });
        $('#extras').on('ifUnchecked', function () {
            $('#extras-con').slideUp();
        });
		<?=((isset($formulation_items) && $formulation_items)) ? '$("#formulation").iCheck("check");$(".product_formulation").slideDown();': '' ?>
		$('#formulation').on('ifChecked', function () {
            $('.product_formulation').slideDown();
        });
        $('#formulation').on('ifUnchecked', function () {
            $('.product_formulation').slideUp();
        });

        <?= isset($_POST['promotion']) || $product->promotion ? '$("#promotion").iCheck("check");': '' ?>
        $('#promotion').on('ifChecked', function (e) {
            $('#promo').slideDown();
        });
        $('#promotion').on('ifUnchecked', function (e) {
            $('#promo').slideUp();
        });

        $('.attributes').on('ifChecked', function (event) {
            $('#options_' + $(this).attr('id')).slideDown();
        });
        $('.attributes').on('ifUnchecked', function (event) {
            $('#options_' + $(this).attr('id')).slideUp();
        });

        //$('#cost').removeAttr('required');
        $('#type').change(function () {
            var t = $(this).val();
            if (t == 'standard' || t == 'raw_material' || t == 'asset' || t == 'service_rental' ) {
                $('.standard').slideDown();
                $('#cost').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
            } else {
				$('.standard').slideUp();
                $('#cost').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
            }
			if (t == 'raw_material' || t == 'asset') {
				$('.hide_raw').slideUp();
			}

            
			
			<?php if($Settings->accounting == 1){ ?>
				if(t == 'service' || t == 'bom'){
					$('.services').slideDown();
				}else{
					$('.services').slideUp();
				}
			<?php } ?>
            if (t !== 'digital') {
                //$('.digital').slideUp();
				$('.combo2').slideUp();
            } else {
                //$('.digital').slideDown();
				 $('.combo2').slideDown();
            }
            if (t !== 'combo') {
                $('.combo').slideUp();
                //$('#add_item').removeAttr('required');
                //$('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
            } else {
                $('.combo').slideDown();
                //$('#add_item').attr('required', 'required');
                //$('form[data-toggle="validator"]').bootstrapValidator('addField', 'add_item');
            }
			
			if (t !== 'bom') {
                $('.bom').slideUp();
            } else {
                $('.bom').slideDown();
            }

            if(t=='standard' || t=='bom' || t=='service' || t=='combo' || t=='service_rental'){
                $('.div_price').slideDown();
            }
			
        });
		
		$("#stregth").live("change",function(){
			var stregth = $(this).val();
			if(stregth==1){
				$("#l_product_code").html('<label for="code"><?= lang("stregth_code") ?></label>');
				$("#l_product_name").html('<label for="name"><?= lang("stregth_name") ?></label>');
			}else{
				$("#l_product_code").html('<label for="code"><?= lang("product_code") ?></label>');
				$("#l_product_name").html('<label for="name"><?= lang("product_name") ?></label>');
			}
		});
		
		$('.for_caculation').live('change',function(){
			var row = $(this).closest('tr');
			item_id = row.attr('data-item-id');
			var for_caculation = $(this).val();
			for_items[item_id].for_caculation = for_caculation;
		});
        
        
        $(document).on("focus", '.for_width, .for_height, .for_square, .for_qty', function () {
			old_value = $(this).val();
		}).on("change", '.for_width, .for_height, .for_square, .for_qty', function () {
            var thisx = $(this);
            var allow_text = ['0','1','2','3','4','5','6','7','8','9','x','&','|',' ','>','<','=','.'];
            var row = $(this).closest('tr');
            var value = $(this).val();
            var cur_class = $(this).attr('id');

            for (var i = 0; i < value.length; i++) {
                if(!(jQuery.inArray(value.charAt(i), allow_text) !== -1)){
                    thisx.val(old_value);
                    bootbox.alert(lang.unexpected_value);
                    return false;
                }
            }
            item_id = row.attr('data-item-id');
            if(cur_class=='for_width'){
                for_items[item_id].for_width = value;
            }else if(cur_class=='for_height'){
                for_items[item_id].for_height = value;
            }else if(cur_class=='for_square'){
                for_items[item_id].for_square = value;
            }else if(cur_class=='for_qty'){
                for_items[item_id].for_qty = value;
            }
			
		});
		
		$('.for_field').live('change',function(){
			var row = $(this).closest('tr');
			item_id = row.attr('data-item-id');
			var for_field = $(this).val();
			for_items[item_id].for_field = for_field;
		});
		
		$('.for_operation').live('change',function(){
			var row = $(this).closest('tr');
			item_id = row.attr('data-item-id');
			var for_operation = $(this).val();
			for_items[item_id].for_operation = for_operation;
		});
		$(document).on('click', '.for_del', function () {
            var id = $(this).attr('id');
            delete for_items[id];
            $(this).parent().parent().remove();
        });

        $('#add_formulation').click(function(){
            var item = {};
			item.id = Math.random();
            var row = add_formulation_product_item(item);
        });
		
		function add_formulation_product_item(item) {
            if (item == null) {
                return false;
            }
            item_id = item.id;
            for_items[item_id] = item;

			//var Arr_cons = [{id:"granter_than", name:"<?= lang('granter_than') ?>"},{id:"less_than", name:"<?= lang('less_than') ?>"},{id:"equal_to", name:"<?= lang('equal_to') ?>"}];  
			var Arr_cacs = [{id:"empty", name:"<?= lang('empty') ?>"},{id:"width", name:"<?= lang('width') ?>"},{id:"height", name:"<?= lang('height') ?>"},{id:"square", name:"<?= lang('square') ?>"},{id:"qty", name:"<?= lang('quantity') ?>"},{id:"total_qty", name:"<?= lang('total_qty') ?>"}];  
			var Arr_oper = [{id:"equal", name:"<?= lang('equal') ?>"},{id:"multiple", name:"<?= lang('multiple') ?>"},{id:"divide", name:"<?= lang('divide') ?>"},{id:"add", name:"<?= lang('add') ?>"},{id:"subtraction", name:"<?= lang('subtraction') ?>"}];  
			
			$("#formulationTable tbody").empty();

			var i = 1;
            $.each(for_items, function () {
                var operation_id = this.for_operation;
                var field_id = this.for_field;
				var field = "<select id=\"for_field\" name=\"for_field[]\" class=\"form-control select\ for_field\">";
				$.each(Arr_cacs, function () {
					if(this.id == field_id){
						field += '<option selected value='+this.id+'>'+this.name+'</option>';
					}else{
						field += '<option value='+this.id+'>'+this.name+'</option>';
					}
				});
				field += "</select>";
				
				var operation = "<select id=\"for_operation\" name=\"for_operation[]\" class=\"form-control select\ for_operation\">";
				$.each(Arr_oper, function () {
					if(this.id == operation_id){
						operation += '<option selected value='+this.id+'>'+this.name+'</option>';
					}else{
						operation += '<option value='+this.id+'>'+this.name+'</option>';
					}
				});
				operation += "</select>";
				
				
				if (this.for_width == null){
					this.for_width = '';
				}
				if (this.for_height == null){
					this.for_height = '';
				}
				if (this.for_square == null){
					this.for_square = '';
				}
				if (this.for_qty == null){
					this.for_qty = '';
				}
				if (this.for_caculation == null){
					this.for_caculation = '';
				}
				
                var row_no = this.id ;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
				tr_html =  '<td><input value="'+this.for_width+'" class="form-control tip for_width" id="for_width" type="text" name="for_width[]"/></td>';
				tr_html +=  '<td><input value="'+this.for_height+'" class="form-control tip for_height" id="for_height" type="text" name="for_height[]"/></td>';
				tr_html +=  '<td><input value="'+this.for_square+'" class="form-control tip for_square" id="for_square" type="text" name="for_square[]"/></td>';
				tr_html +=  '<td><input value="'+this.for_qty+'" class="form-control tip for_qty" id="for_qty" type="text" name="for_qty[]"/></td>';
                tr_html += '<td>'+field+'</td>';
				tr_html += '<td>'+operation+'</td>';
				tr_html +=  '<td><input value="'+this.for_caculation+'" class="form-control tip for_caculation" type="text" name="for_caculation[]"/></td>';
				tr_html += '<td class="text-center"><i class="fa fa-times tip for_del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#formulationTable");				
				
				i++;
            });

            return true;
        }
		
		
		
		
		
		$("#add_item_bom").autocomplete({
            source: '<?= site_url('products/raw_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_bom_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        $("#add_item").autocomplete({
            source: '<?= site_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 5,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                        $('#add_item').removeAttr('required');
                        $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
		
		$("#add_item2").autocomplete({
            source: '<?= site_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item2(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
		
        $('#add_item').removeAttr('required');
        $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
		
		
		function add_bom_product_item(item) {
            if (item == null) {
                return false;
            }
            item_id = item.row_id;
            if (items[item_id]) {
                items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
            } else {
                items[item_id] = item;
            }
            var pp = 0;
            $("#bomTable tbody").empty();			
            $.each(items, function () {
				var unit_id = this.unit_id;
				var opt = '<p>n/a</p>';
				if(this.options !== false) {
					opt = "<select name=\"bom_unit_id[]\" class=\"form-control select\">";
					$.each(this.options, function () {
						if(this.id == unit_id){
							opt += "<option selected value="+this.id+">"+this.name+"</option>";
						}else{
							opt += "<option value="+this.id+">"+this.name+"</option>";
						}
						
					});
					opt += "</select>";
				}
				
                var row_no = this.row_id;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
                tr_html = '<td><input name="bom_item_id[]" type="hidden" value="' + this.id + '"><input name="bom_item_name[]" type="hidden" value="' + this.name + '"><input name="bom_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.code + ' - ' + this.name + '</span></td>';
                tr_html += '<td><input class="form-control text-center" type="text" value="' + this.bom_type + '" name="bom_type[]"/></td>';
                tr_html += '<td><input class="form-control text-center rquantity" name="bom_item_quantity[]" type="text" value="' + formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td class=center>'+opt+'</td>'; 
				tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#bomTable");				
                pp += formatDecimal(parseFloat(this.price)*parseFloat(this.qty));
            });
            $('.item_' + item_id).addClass('warning');

            return true;
        }
		
		
        function add_product_item(item) {
            if (item == null) {
                return false;
            }
            item_id = item.id;
            if (items[item_id]) {
                items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
            } else {
                items[item_id] = item;
            }

            $("#prTable tbody").empty();
            $.each(items, function () {
				
				var option_id = this.option_id;
				var opt = '<p>n/a</p>';
				if(this.variants) {
					opt = "<select id=\"poption\" name=\"coption_id[]\" class=\"form-control select\">";
					$.each(this.variants, function () {
						if(option_id == this.id){
							opt += "<option value="+this.id+" selected>"+this.name+"</option>";
						}else{
							opt += "<option value="+this.id+">"+this.name+"</option>";
						}
					});
					opt += "</select>";
				}
				
                var row_no = this.id;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '"></tr>');
                tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.code + ' - ' + this.name + '</span></td>';
                tr_html += '<td><input class="form-control text-center rquantity" name="combo_item_quantity[]" type="text" value="' + formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td><input class="form-control text-center rprice" name="combo_item_price[]" type="text" value="' + formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td class=center>'+opt+'</td>';
				tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#prTable");
            });
            $('.item_' + item_id).addClass('warning');
            //audio_success.play();
            return true;

        }
		
		
		function add_product_item2(item) {
            if (item == null) {
                return false;
            }
			
            item_id = item.id;
            if (items[item_id]) {
                items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
            } else {
                items[item_id] = item;
            }
            var pp = 0;
            $("#prTable2 tbody").empty();
            $.each(items, function () {
				
				var option_id = this.option_id;
				var opt = '<p>n/a</p>';
				if(this.variants !== false) {
					opt = "<select id=\"poption\" name=\"poption_id[]\" class=\"form-control select\">";
					$.each(this.variants, function () {
						if(option_id == this.id){
							opt += "<option value="+this.id+" selected>"+this.name+"</option>";
						}else{
							opt += "<option value="+this.id+">"+this.name+"</option>";
						}
					});
					opt += "</select>";
				}

                var row_no = this.id;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
                tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.code + ' - ' + this.name + '</span></td>';      
				tr_html += '<td class=center>'+opt+'</td>'; 
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#prTable2");
            });
            $('.item_' + item_id).addClass('warning');
            return true;
        }
		
		
		$("#add_item3").autocomplete({
			source: '<?= site_url('products/suggestions'); ?>',
			minLength: 1,
			autoFocus: false,
			delay: 250,
			response: function (event, ui) {
				if ($(this).val().length >= 16 && ui.content[0].id == 0) {
					bootbox.alert('<?= lang('no_product_found') ?>', function () {
						$('#add_item').focus();
					});
					$(this).val('');
				}
				else if (ui.content.length == 1 && ui.content[0].id != 0) {
					ui.item = ui.content[0];
					$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
					$(this).autocomplete('close');
					$(this).removeClass('ui-autocomplete-loading');
				}
				else if (ui.content.length == 1 && ui.content[0].id == 0) {
					bootbox.alert('<?= lang('no_product_found') ?>', function () {
						$('#add_item').focus();
					});
					$(this).val('');

				}
			},
			select: function (event, ui) {
				event.preventDefault();
				if (ui.item.id !== 0) {
					var row = add_product_item3(ui.item);
					if (row) {
						$(this).val('');
					}
				} else {
					bootbox.alert('<?= lang('no_product_found') ?>');
				}
			}
		});
		
		$(document).on('click', '.del_convert', function () {
            var id = $(this).attr('id');
            delete items3[id];
            $(this).closest('#row_' + id).remove();
            costConvertCalulation();
        });

		function add_product_item3(item) {
			if (item == null) {
				return false;
			}
			item_id = item.id;
			if (items3[item_id]) {
				items3[item_id].qty = (parseFloat(items3[item_id].qty) + 1).toFixed(2);
			} else {
				items3[item_id] = item;
			}
			var pp = 0;
			$("#prTable3 tbody").empty();			
			$.each(items3, function () {
				var row_no = this.id;
				var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
				tr_html = '<td><input name="convert_item_id[]" type="hidden" value="' + this.id + '"><input name="convert_item_code[]" type="hidden" value="' + this.code + '"><input name="convert_item_name[]" type="hidden" value="' + this.name + '"><span id="name_' + row_no + '">' + this.code + ' - ' + this.name + '</span></td>';              
				tr_html += '<td><input name="convert_item_unit[]" type="hidden" value="' + this.unit + '"><input class="text-center form-control convert_item_qty" name="convert_item_qty[]" value="' + this.qty + '"/></td>';
				tr_html += '<td><input readonly="true" class="text-center form-control convert_cost"  value="' + this.cost + '"/></td>';
				tr_html += '<td class="text-center"><i class="fa fa-times tip del_convert" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
				newTr.html(tr_html);
				newTr.prependTo("#prTable3");
			});
			costConvertCalulation();
			return true;
		}

		var old_convert_qty;
		$(document).on("focus", '.convert_item_qty', function () {
			old_convert_qty = $(this).val();
		}).on("change", '.convert_item_qty', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_convert_qty);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			costConvertCalulation();
		});

		function costConvertCalulation(){
			var total_cost = 0;
			$('.convert_cost').each(function(){
				var parent = $(this).closest('tr'); 
				var cost = $(this).val()-0;
				var quantity = parent.find('.convert_item_qty').val()-0;
				total_cost += (quantity * cost);
			});
			$('#cost').val(total_cost);
		}
		

        function calculate_price() {
            var rows = $('#prTable').children('tbody').children('tr');
			var type = $('#type').val();
            var pp = 0;
			if(type!='bom'){
				$.each(rows, function () {
					pp += formatDecimal(parseFloat($(this).find('.rprice').val())*parseFloat($(this).find('.rquantity').val()));
				});
				$('#price').val(pp);
			}
            
            return true;
        }

        $(document).on('change', '.rquantity, .rprice', function () {
            calculate_price();
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete items[id];
            $(this).closest('#row_' + id).remove();
            calculate_price();
        });
		
		

        var su = 2;
        $('#addSupplier').click(function () {
            if (su <= 5) {
                $('#supplier_1').select2('destroy');
                $('#supplier_1').select2('destroy');
                var html = '<div style="clear:both;height:5px;"></div><div class="row"><div class="col-xs-12"><div class="form-group"><input type="hidden" name="supplier_' + su + '", class="form-control" id="supplier_' + su + '" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>" style="width:100%;display: block !important;" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_part_no" class="form-control tip" id="supplier_' + su + '_part_no" placeholder="<?= lang('supplier_part_no') ?>" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_price" class="form-control tip" id="supplier_' + su + '_price" placeholder="<?= lang('supplier_price') ?>" /></div></div></div>';
                $('#ex-suppliers').append(html);
                var sup = $('#supplier_' + su);
                suppliers(sup);
                su++;
            } else {
                bootbox.alert('<?= lang('max_reached') ?>');
                return false;
            }
        });

        var _URL = window.URL || window.webkitURL;
        $("input#images").on('change.bs.fileinput', function () {
            var ele = document.getElementById($(this).attr('id'));
            var result = ele.files;
            $('#img-details').empty();
            for (var x = 0; x < result.length; x++) {
                var fle = result[x];
                for (var i = 0; i <= result.length; i++) {
                    var img = new Image();
                    img.onload = (function (value) {
                        return function () {
                            ctx[value].drawImage(result[value], 0, 0);
                        }
                    })(i);

                    img.src = 'images/' + result[i];
                }
            }
        });
        var variants = <?=json_encode($vars);?>;
        $(".select-tags").select2({
            tags: variants,
            tokenSeparators: [","],
            multiple: true
        });
		
        $(document).on('ifChecked', '#attributes', function (e) {
            $('#attr-con').slideDown();
        });
		
        $(document).on('ifUnchecked', '#attributes', function (e) {
            $(".select-tags").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#attr-con').slideUp();
        });
        $('#addAttributes').click(function (e) {
            e.preventDefault();
            var attrs_val = $('#attributesInput').val(), attrs;
            attrs = attrs_val.split(',');
            for (var i in attrs) {
                if (attrs[i] !== '') {
                    $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value=""><span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                }
            }
        });
        $(document).on('click', '.delAttr', function () {
            $(this).closest("tr").remove();
        });
        $(document).on('click', '.attr-remove-all', function () {
            $('#attrTable tbody').empty();
            $('#attrTable').hide();
        });
        var row, warehouses = <?= json_encode($warehouses); ?>;
        $(document).on('click', '.attr td:not(:last-child)', function () {
            row = $(this).closest("tr");
            $('#aModalLabel').text(row.children().eq(0).find('span').text());
            $('#awarehouse').select2("val", (row.children().eq(1).find('input').val()));
            $('#aquantity').val(row.children().eq(2).find('span').text());
            $('#aprice').val(row.children().eq(3).find('span').text());
            $('#aModal').appendTo('body').modal('show');
        });

        $(document).on('click', '#updateAttr', function () {
            var wh = $('#awarehouse').val(), wh_name;
            $.each(warehouses, function () {
                if (this.id == wh) {
                    wh_name = this.name;
                }
            });
            row.children().eq(1).html('<input type="hidden" name="attr_warehouse[]" value="' + wh + '"><input type="hidden" name="attr_wh_name[]" value="' + wh_name + '"><span>' + wh_name + '</span>');
            
            row.children().eq(3).html('<input type="hidden" name="attr_price[]" value="' + $('#aprice').val() + '"><span>' + currencyFormat($('#aprice').val()) + '</span>');

            $('#aModal').modal('hide');
        });
    });

    <?php if ($product) { ?>
    $(document).ready(function () {
        $('#enable_wh').click(function () {
            var whs = $('.wh');
            $.each(whs, function () {
                $(this).val($('#v' + $(this).attr('id')).val());
            });
            $('#warehouse_quantity').val(1);
            $('.wh').attr('disabled', false);
            $('#show_wh_edit').slideDown();
        });
        $('#disable_wh').click(function () {
            $('#warehouse_quantity').val(0);
            $('#show_wh_edit').slideUp();
        });
        $('#show_wh_edit').hide();
        $('.wh').attr('disabled', true);
        var t = "<?=$product->type?>";
        if (t == 'standard' || t == 'raw_material' || t == 'asset') {
            $('.standard').slideDown();
            $('#cost').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
        } else {
			$('.standard').slideUp();
            $('#cost').attr('required', 'required');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
        }
		if (t == 'raw_material' || t == 'asset') {
			$('.hide_raw').slideUp();
		}
        if (t !== 'digital') {
            //$('.digital').slideUp();
			$('.combo2').slideUp();
        } else {
            //$('.digital').slideDown();
			$('.combo2').slideDown();
        }
        if (t !== 'combo') {
            $('.combo').slideUp();
        } else {
            $('.combo').slideDown();
        }
		if (t !== 'bom') {
			$('.bom').slideUp();
		} else {
			$('.bom').slideDown();
		}
        if(t=='standard' || t=='bom' || t=='service' || t=='combo'){
            $('.div_price').slideDown();
        }  

        $('#add_item').removeAttr('required');
        $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
        //$("#code").parent('.form-group').addClass("has-error");
        //$("#code").focus();
        $("#product_image").parent('.form-group').addClass("text-warning");
        $("#images").parent('.form-group').addClass("text-warning");
        $.ajax({
            type: "get", 
            url: "<?= site_url('products/getSubCategories') ?>/" + <?= $product->category_id ?>,
            dataType: "json",
			async: true,
            success: function (scdata) {
                if (scdata != null) {
                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                        placeholder: "<?= lang('select_category_to_load') ?>",
                        data: scdata
                    });
                }
            }
        });
        <?php if ($product->supplier1) { ?>
        select_supplier('supplier1', "<?= $product->supplier1; ?>");
        $('#supplier_price').val("<?= $this->cus->formatDecimal($product->supplier1price); ?>");
        $('#supplier_part_no').val("<?= $product->supplier1_part_no; ?>");
        <?php } else { ?>
            $('#supplier1').addClass('rsupplier');
        <?php } ?>
        <?php if ($product->supplier2) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_2', "<?= $product->supplier2; ?>");
        $('#supplier_2_price').val("<?= $this->cus->formatDecimal($product->supplier2price); ?>");
        $('#supplier_2_part_no').val("<?= $product->supplier2_part_no; ?>");
        <?php } ?>
        <?php if ($product->supplier3) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_3', "<?= $product->supplier3; ?>");
        $('#supplier_3_price').val("<?= $this->cus->formatDecimal($product->supplier3price); ?>");
        $('#supplier_3_part_no').val("<?= $product->supplier3_part_no; ?>");
        <?php } ?>
        <?php if ($product->supplier4) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_4', "<?= $product->supplier4; ?>");
        $('#supplier_4_price').val("<?= $this->cus->formatDecimal($product->supplier4price); ?>");
        $('#supplier_4_part_no').val("<?= $product->supplier4_part_no; ?>");
        <?php } ?>
        <?php if ($product->supplier5) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_5', "<?= $product->supplier5; ?>");
        $('#supplier_5_price').val("<?= $this->cus->formatDecimal($product->supplier5price); ?>");
        $('#supplier_5_part_no').val("<?= $product->supplier5_part_no; ?>");
        <?php } ?>
        function select_supplier(id, v) {
            $('#' + id).val(v).select2({
                minimumInputLength: 1,
                data: [],
                initSelection: function (element, callback) {
                    $.ajax({
                        type: "get", async: false,
                        url: "<?= site_url('suppliers/getSupplier') ?>/" + $(element).val(),
                        dataType: "json",
                        success: function (data) {
                            callback(data[0]);
                        }
                    });
                },
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
    });
    <?php } ?>
    $(document).ready(function () {
        $('#enable_wh').trigger('click');
        $('#unit').change(function(e) {
			var product_unit = '';
            var v = $(this).val();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubUnits') ?>/" + v,
                    dataType: "json",
                    success: function (data) {
                        $('#default_sale_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $('#default_purchase_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $.each(data, function () {
							if(this.id == v){
								var qtyreadonly='readonly';
							}else{
								var qtyreadonly='';
							}
                            if(this.operation_value == 0 && this.operation_value != "" && this.operation_value != null){
								var base_unit = 0;
							}else{
								var base_unit = getNumber(this.operation_value) <= 0 ? 1 : this.operation_value;
							}
							product_unit += '<tr><td>'+this.name+'</td>';
							product_unit += '<td><input type="hidden" name="product_unit_id[]" value="'+this.id+'"/><input class="form-control" '+qtyreadonly+' name="product_unit_qty[]" placeholder="<?= lang('quantity') ?>" type="text" value="'+getNumber(base_unit)+'"/></td><td><input '+qtyreadonly+' type="text" name="product_unit_price[]" class="form-control" placeholder="<?= lang('price') ?>" /></td></tr>';							
							$("<option />", {value: this.id, text: this.name+' ('+this.code+')'}).appendTo($('#default_sale_unit'));
                            $("<option />", {value: this.id, text: this.name+' ('+this.code+')'}).appendTo($('#default_purchase_unit'));
                        });
						$('#box_product_unit').show();
						$('#unit_body').html(product_unit);
                        $('#default_sale_unit').select2('val', v);
                        $('#default_purchase_unit').select2('val', v);
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                    }
                });
            } else {
				$('#box_product_unit').hide();
				$('#unit_body').empty();
                $('#default_sale_unit').select2("destroy").empty();
                $('#default_purchase_unit').select2("destroy").empty();
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_sale_unit'));
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_purchase_unit'));
                $('#default_sale_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
                $('#default_purchase_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
            }
        });
        $('#digital_file').removeAttr('required');
        $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
    });
</script>

<div class="modal" id="aModal" tabindex="-1" role="dialog" aria-labelledby="aModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="aModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="awarehouse" class="col-sm-4 control-label"><?= lang('warehouse') ?></label>
                        <div class="col-sm-8">
                            <?php
                            $wh[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, '', 'id="awarehouse" class="form-control"');
                            ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="aprice" class="col-sm-4 control-label"><?= lang('price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aprice">
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="updateAttr"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
