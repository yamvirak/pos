<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('print_barcode_label'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= site_url('products/add_barcode_style') ?>" data-toggle="modal" data-target="#myModal" class="tip" data-placement="top" title="<?= lang("add_barcode_style") ?>">
                        <i class="icon fa fa-plus tip"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo sprintf(lang('print_barcode_heading'), 
                anchor('system_settings/categories', lang('categories')),
                anchor('system_settings/subcategories', lang('subcategories')),
                anchor('purchases', lang('purchases')),
                anchor('transfers', lang('transfers'))
                ); ?></p>

                <div class="well well-sm no-print">
					
					
                    <div class="form-group">
                        <?= lang("add_product", "add_item"); ?>
                        <?php echo form_input('add_item', '', 'class="form-control" id="add_item" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                    </div>
					
                    <?= form_open("products/print_barcodes", 'id="barcode-print-form" data-toggle="validator"'); ?>
                    <div class="controls table-controls">
                        <table id="bcTable"
                               class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>
                            <tr>
                                <th class="col-xs-4"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                <th class="col-xs-1"><?= lang("quantity"); ?></th>
                                <th class="col-xs-7"><?= lang("variants"); ?></th>
                                <th class="text-center" style="width:30px;">
                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                </th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
						<div class="form-group">
							<?php
								foreach($barcode_styles as  $barcode_style){
									$opts[$barcode_style->id] = $barcode_style->barcode_code.' - '.$barcode_style->barcode_name;
								}
							?>
							<div style="margin-bottom:10px"><?= lang('dimension').' '.lang('style'); ?> ( <?= lang('pixel'); ?> )</div>
							<?= form_dropdown('style', $opts, isset($style)? $style : '', 'class="form-control tip" id="style" required="required"'); ?>
                            
							<table style="margin-top:20px" class="table items table-striped table-bordered table-condensed table-hover">
								<thead>
									<tr>
										<th colspan="6"><?= lang('paper_demension') ?></th>
										<th colspan="8"><?= lang('barcode_demension') ?></th>
									</tr>
									<tr>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('width') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('height') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('padding_top') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('padding_bottom') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('padding_left') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('padding_right') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('width') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('height') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('padding_top') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('padding_bottom') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('padding_left') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('padding_right') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('quantity') ?></th>
										<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang('size') ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><input name="p_width" value="<?= $barcode_setting->p_width ?>" id="p_width" type="text" class="form-control text-center" /></td>
										<td><input name="p_height" value="<?= $barcode_setting->p_height ?>" id="p_height" type="text" class="form-control text-center" /></td>
										<td><input name="p_padding_top" value="<?= $barcode_setting->p_padding_top ?>" id="p_padding_top" type="text" class="form-control text-center" /></td>
										<td><input name="p_padding_bottom" value="<?= $barcode_setting->p_padding_bottom ?>" id="p_padding_bottom" type="text" class="form-control text-center" /></td>
										<td><input name="p_padding_left" value="<?= $barcode_setting->p_padding_left ?>" id="p_padding_left" type="text" class="form-control text-center" /></td>
										<td><input name="p_padding_right" value="<?= $barcode_setting->p_padding_right ?>" id="p_padding_right" type="text" class="form-control text-center" /></td>
										<td><input name="b_width" value="<?= $barcode_setting->b_width ?>" id="b_width" type="text" class="form-control text-center" /></td>
										<td><input name="b_height" value="<?= $barcode_setting->b_height ?>" id="b_height" type="text" class="form-control text-center" /></td>
										<td><input name="b_padding_top" value="<?= $barcode_setting->b_padding_top ?>" id="b_padding_top" type="text" class="form-control text-center" /></td>
										<td><input name="b_padding_bottom" value="<?= $barcode_setting->b_padding_bottom ?>" id="b_padding_bottom" type="text" class="form-control text-center" /></td>
										<td><input name="b_padding_left" value="<?= $barcode_setting->b_padding_left ?>" id="b_padding_left" type="text" class="form-control text-center" /></td>
										<td><input name="b_padding_right" value="<?= $barcode_setting->b_padding_right ?>" id="b_padding_right" type="text" class="form-control text-center" /></td>
										<td><input name="b_quantity" value="<?= $barcode_setting->b_quantity ?>" id="b_quantity" type="text" class="form-control text-center" /></td>
										<td><input name="b_size" value="<?= $barcode_setting->b_size ?>" id="b_size" type="text" class="form-control text-center" /></td>
									</tr>
								</tbody>
							</table>
                            <div class="clearfix"></div>
                        </div>
                        <div class="form-group">
                            <span style="font-weight: bold; margin-right: 15px;"><?= lang('print'); ?>:</span>
                            
                            <input name="product_name" type="checkbox" id="product_name" value="1" checked="checked" style="display:inline-block;" />
                            <label for="product_name" class="padding05"><?= lang('product_name'); ?></label>
                            <input name="price" type="checkbox" id="price" value="1" checked="checked" style="display:inline-block;" />
                            <label for="price" class="padding05"><?= lang('price'); ?></label>
                            <input name="currencies" type="checkbox" id="currencies" value="1" style="display:inline-block;" />
                            <label for="currencies" class="padding05"><?= lang('currencies'); ?></label>
                            <input name="unit" type="checkbox" id="unit" value="1" style="display:inline-block;" />
                            <label for="unit" class="padding05"><?= lang('unit'); ?></label>
                            <input name="category" type="checkbox" id="category" value="1" style="display:inline-block;" />
                            <label for="category" class="padding05"><?= lang('category'); ?></label>
                            <input name="variants" type="checkbox" id="variants" value="1" style="display:inline-block;" />
                            <label for="variants" class="padding05"><?= lang('variants'); ?></label>
                            <input name="product_image" type="checkbox" id="product_image" value="1" style="display:inline-block;" />
                            <label for="product_image" class="padding05"><?= lang('product_image'); ?></label>
							<?php if($this->Settings->product_serial){ ?>
								<input name="serial" type="checkbox" id="serial" value="1" checked="checked" style="display:inline-block;" />
								<label for="serial" class="padding05"><?= lang('serial'); ?></label>
								<input style="height:30px;" type="text" class="s_serial" name="s_serial"/>
							<?php } ?>
                        </div>
                    <div class="form-group">
                        <?php echo form_submit('print', lang("update"), 'class="btn btn-primary"'); ?>
                        <button type="button" id="reset" class="btn btn-danger"><?= lang('reset'); ?></button>
                    </div>
                    <?= form_close(); ?>
                    <div class="clearfix"></div>
                </div>
				<div id="barcode-con" class="no-print" style="margin-bottom:10px">
                    <?php
                        if ($this->input->post('print')) {
                            if (!empty($barcodes)) {
                               echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="'.lang('print').'"><i class="icon fa fa-print"></i> '.lang('print').'</button>';
                            }
                        }
                    ?>
                </div>
				<center>
				<style>
					@media print{
						.remove_border{border:0 !important}
					}
				</style>
				<?php
					$font_size = '16px';
					echo '<div class="remove_border" style="display: block; border: 1px dotted #CCC; page-break-after:always; width:'.$barcode_setting->p_width.'px; height:'.$barcode_setting->p_height.'px; padding-top:'.$barcode_setting->p_padding_top.'px; padding-right:'.$barcode_setting->p_padding_right.'px; padding-bottom:'.$barcode_setting->p_padding_bottom.'px; padding-left:'.$barcode_setting->p_padding_left.'px">';

					if (isset($barcodes)) {
						foreach ($barcodes as $item) {
							for ($r = 1; $r <= $item['quantity']; $r++) {
								if (($r % ($barcode_setting->b_quantity+1) == 0) && $barcode_setting->b_quantity > 1) {
									echo '</div><div class="clearfix"></div><div class="remove_border" style="display: block; border: 1px dotted #CCC; page-break-after:always; width:'.$barcode_setting->p_width.'px; height:'.$barcode_setting->p_height.'px; padding-top:'.$barcode_setting->p_padding_top.'px; padding-right:'.$barcode_setting->p_padding_right.'px; padding-bottom:'.$barcode_setting->p_padding_bottom.'px; padding-left:'.$barcode_setting->p_padding_left.'px">';
								}
								echo '<div class="remove_border" style="display: block; border: 1px dotted #CCC; text-align: center; float: left; width:'.$barcode_setting->b_width.'px; height:'.$barcode_setting->b_height.'px; padding-top:'.$barcode_setting->b_padding_top.'px; padding-right:'.$barcode_setting->b_padding_right.'px; padding-bottom:'.$barcode_setting->b_padding_bottom.'px; padding-left:'.$barcode_setting->b_padding_left.'px">';							
									if($item['image']) {
										echo '<span><img src="'.base_url('assets/uploads/thumbs/'.$item['image']).'" alt="" /></span><br>';
									}
									if($item['site']) {
										echo '<span style="font-size:'.$font_size.'">'.$item['site'].'</span><br>';
									}
									if($item['name']) {
										echo '<span style="font-size:'.$font_size.'" font-weight:bold;>'.$item['name'].'</span><br>';
									}
									if($item['price']) {
										echo '<span style="font-weight:bold;font-size:'.$font_size.'">'.lang('price').' ';
										if($item['currencies']) {
											foreach ($currencies as $currency) {
												echo $currency->code . ': ' . $this->cas->formatMoney($item['price'] * $currency->rate).', ';
											}
										} else {
											echo $item['price'];
										}
										echo '</span><br>';
									}
									if($item['unit']) {
										echo '<span style="font-size:'.$font_size.'">'.lang('unit').': '.$item['unit'].'</span><br>';
									}
									if($item['category']) {
										echo '<span style="font-size:'.$font_size.'">'.lang('category').': '.$item['category'].'</span><br>';
									}
									if($item['variants']) {
										echo '<span style="font-size:'.$font_size.'">'.lang('variants').': ';
										foreach ($item['variants'] as $variant) {
											echo $variant->name.', ';
										}
										echo '</span><br>';
									}
									echo '<span>'.$item['barcode'].'</span>
								</div>';
								
								if($barcode_setting->b_quantity==1 ){
									echo '</div><div class="clearfix"></div><div style="display: block; border: 1px solid #CCC; page-break-after:always; width:'.$barcode_setting->p_width.'px; height:'.$barcode_setting->p_height.'px; padding-top:'.$barcode_setting->p_padding_top.'px; padding-right:'.$barcode_setting->p_padding_right.'px; padding-bottom:'.$barcode_setting->p_padding_bottom.'px; padding-left:'.$barcode_setting->p_padding_left.'px">';
								}
							}
						}
					}
					
					echo '</div>'
				?>
				</center>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ac = false; bcitems = {};
    if (localStorage.getItem('bcitems')) {
        bcitems = JSON.parse(localStorage.getItem('bcitems'));
    }
    <?php if($items) { ?>
    localStorage.setItem('bcitems', JSON.stringify(<?= $items; ?>));
    <?php } ?>
    $(document).ready(function() {
		
		
		$(".category").on("change",function(){
			var term = $(this).val();			
			$.ajax({
				url : "<?= site_url("products/get_suggestion_categories") ?>",
				dataType : "JSON",
				type : "GET",
				data : { term : term, quantity : $("#quantity").val() },
				success:function(re){
					localStorage.setItem('bcitems', JSON.stringify(re));
					loadItems();
				}
			});
		});
		
		
        <?php if ($this->input->post('print')) { ?>
            $( window ).load(function() {
                $('html, body').animate({
                    scrollTop: ($("#barcode-con").offset().top)-15
                }, 1000);
            });
        <?php } ?>
        if (localStorage.getItem('bcitems')) {
            loadItems();
        }
        $("#add_item").autocomplete({
            source: '<?= site_url('products/get_suggestions'); ?>',
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
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        check_add_item_val();

        $('#style').change(function (e) {
			var style = $(this).val();
            localStorage.setItem('bcstyle', style);
			$.ajax({
				url : "<?= site_url("products/get_barcode_style") ?>",
				type : "GET",
				dataType : "JSON",
				data : { style : style},
				success : function(data){
					if(data){
						var p_width = (data.result.p_width > 0 ? data.result.p_width : 0);
						var p_height = (data.result.p_height > 0 ? data.result.p_height : 0);
						var p_padding_top = (data.result.p_padding_top > 0 ? data.result.p_padding_top : 0);
						var p_padding_bottom = (data.result.p_padding_bottom > 0 ? data.result.p_padding_bottom : 0);
						var p_padding_left = (data.result.p_padding_left > 0 ? data.result.p_padding_left : 0);
						var p_padding_right = (data.result.p_padding_right > 0 ? data.result.p_padding_right : 0);
						var b_width = (data.result.b_width > 0 ? data.result.b_width : 0);
						var b_height = (data.result.b_height > 0 ? data.result.b_height : 0);
						var b_padding_top = (data.result.b_padding_top > 0 ? data.result.b_padding_top : 0);
						var b_padding_bottom = (data.result.b_padding_bottom > 0 ? data.result.b_padding_bottom : 0);
						var b_padding_left = (data.result.b_padding_left > 0 ? data.result.b_padding_left : 0);
						var b_padding_right = (data.result.b_padding_right > 0 ? data.result.b_padding_right : 0);
						var b_quantity = (data.result.b_quantity > 0 ? data.result.b_quantity : 0);
						var b_size = (data.result.b_size > 0 ? data.result.b_size : 0);
						
						$('#p_width').val(p_width);
						$('#p_height').val(p_height);
						$('#p_padding_top').val(p_padding_top);
						$('#p_padding_bottom').val(p_padding_bottom);
						$('#p_padding_left').val(p_padding_left);
						$('#p_padding_right').val(p_padding_right);
						$('#b_width').val(b_width);
						$('#b_height').val(b_height);
						$('#b_padding_top').val(b_padding_top);
						$('#b_padding_bottom').val(b_padding_bottom);
						$('#b_padding_left').val(b_padding_left);
						$('#b_padding_right').val(b_padding_right);
						$('#b_quantity').val(b_quantity);
						$('#b_size').val(b_size);
					}
				}
			})
        });
        if (style = localStorage.getItem('bcstyle')) {
            $('#style').val(style);
            $('#style').select2("val", style);
			$('#style').change();
        }

        $('#cf_width').change(function (e) {
            localStorage.setItem('cf_width', $(this).val());
        });
        if (cf_width = localStorage.getItem('cf_width')) {
            $('#cf_width').val(cf_width);
        }

        $('#cf_height').change(function (e) {
            localStorage.setItem('cf_height', $(this).val());
        });
        if (cf_height = localStorage.getItem('cf_height')) {
            $('#cf_height').val(cf_height);
        }

        $('#cf_orientation').change(function (e) {
            localStorage.setItem('cf_orientation', $(this).val());
        });
        if (cf_orientation = localStorage.getItem('cf_orientation')) {
            $('#cf_orientation').val(cf_orientation);
        }

        $(document).on('ifChecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 1);
        });
        $(document).on('ifUnchecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 0);
        });
        if (site_name = localStorage.getItem('bcsite_name')) {
            if (site_name == 1)
                $('#site_name').iCheck('check');
            else
                $('#site_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 1);
        });
        $(document).on('ifUnchecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 0);
        });
        if (product_name = localStorage.getItem('bcproduct_name')) {
            if (product_name == 1)
                $('#product_name').iCheck('check');
            else
                $('#product_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#price', function(event) {
            localStorage.setItem('bcprice', 1);
        });
        $(document).on('ifUnchecked', '#price', function(event) {
            localStorage.setItem('bcprice', 0);
            $('#currencies').iCheck('uncheck');
        });
        if (price = localStorage.getItem('bcprice')) {
            if (price == 1)
                $('#price').iCheck('check');
            else
                $('#price').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 1);
        });
        $(document).on('ifUnchecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 0);
        });
        if (currencies = localStorage.getItem('bccurrencies')) {
            if (currencies == 1)
                $('#currencies').iCheck('check');
            else
                $('#currencies').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 1);
        });
        $(document).on('ifUnchecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 0);
        });
        if (unit = localStorage.getItem('bcunit')) {
            if (unit == 1)
                $('#unit').iCheck('check');
            else
                $('#unit').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#category', function(event) {
            localStorage.setItem('bccategory', 1);
        });
        $(document).on('ifUnchecked', '#category', function(event) {
            localStorage.setItem('bccategory', 0);
        });
        if (category = localStorage.getItem('bccategory')) {
            if (category == 1)
                $('#category').iCheck('check');
            else
                $('#category').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 1);
        });
        $(document).on('ifUnchecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 0);
        });
        if (check_promo = localStorage.getItem('bccheck_promo')) {
            if (check_promo == 1)
                $('#check_promo').iCheck('check');
            else
                $('#check_promo').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 1);
        });
        $(document).on('ifUnchecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 0);
        });
        if (product_image = localStorage.getItem('bcproduct_image')) {
            if (product_image == 1)
                $('#product_image').iCheck('check');
            else
                $('#product_image').iCheck('uncheck');
        }
		$('.s_serial').change(function(){
			var s_serial = $(this).val();
			localStorage.setItem('s_serial', s_serial);
		});
		if (s_serial = localStorage.getItem('s_serial')) {
			$('.s_serial').val(s_serial);
		}
		$(document).on('ifChecked', '#serial', function(event) {
			$('.s_serial').css('display','inline');
            localStorage.setItem('bcproduct_serial', 1);
        });
        $(document).on('ifUnchecked', '#serial', function(event) {
			$('.s_serial').css('display','none');
            localStorage.setItem('bcproduct_serial', 0);
			localStorage.setItem('s_serial','');
        });
        if (serial = localStorage.getItem('bcproduct_serial')) {
            if (serial == 1)
                $('#serial').iCheck('check');
            else
                $('#serial').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 1);
        });
        $(document).on('ifUnchecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 0);
        });
        if (variants = localStorage.getItem('bcvariants')) {
            if (variants == 1)
                $('#variants').iCheck('check');
            else
                $('#variants').iCheck('uncheck');
        }

        $(document).on('ifChecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 1;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });
        $(document).on('ifUnchecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 0;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete bcitems[id];
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
            $(this).closest('#row_' + id).remove();
        });

        $('#reset').click(function (e) {

            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('bcitems')) {
                        localStorage.removeItem('bcitems');
                    }
                    if (localStorage.getItem('bcstyle')) {
                        localStorage.removeItem('bcstyle');
                    }
                    if (localStorage.getItem('bcsite_name')) {
                        localStorage.removeItem('bcsite_name');
                    }
                    if (localStorage.getItem('bcproduct_name')) {
                        localStorage.removeItem('bcproduct_name');
                    }
                    if (localStorage.getItem('bcprice')) {
                        localStorage.removeItem('bcprice');
                    }
                    if (localStorage.getItem('bccurrencies')) {
                        localStorage.removeItem('bccurrencies');
                    }
                    if (localStorage.getItem('bcunit')) {
                        localStorage.removeItem('bcunit');
                    }
                    if (localStorage.getItem('bccategory')) {
                        localStorage.removeItem('bccategory');
                    }
                    // if (localStorage.getItem('cf_width')) {
                    //     localStorage.removeItem('cf_width');
                    // }
                    // if (localStorage.getItem('cf_height')) {
                    //     localStorage.removeItem('cf_height');
                    // }
                    // if (localStorage.getItem('cf_orientation')) {
                    //     localStorage.removeItem('cf_orientation');
                    // }

                    $('#modal-loading').show();
                    window.location.replace("<?= site_url('products/print_barcodes'); ?>");
                }
            });
        });

        var old_row_qty;
        $(document).on("focus", '.quantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.quantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
            bcitems[item_id].qty = new_qty;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });

    });
	$(document).on("focus", '.varaint_qty', function () {
            old_varaint_qty = $(this).val();
        }).on("change", '.varaint_qty', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val())) {
                $(this).val(old_varaint_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
	});
	
	

    function add_product_item(item) {
        ac = true;
        if (item == null) {
            return false;
        }
		item_id = item.id;
        if (bcitems[item_id]) {
            bcitems[item_id].qty = parseFloat(bcitems[item_id].qty) + 1;
        } else {
            bcitems[item_id] = item;
            bcitems[item_id]['selected_variants'] = {};
            $.each(item.variants, function () {
                bcitems[item_id]['selected_variants'][this.id] = 1;
            });
        }
        localStorage.setItem('bcitems', JSON.stringify(bcitems));
        loadItems();
        return true;
    }

    function loadItems () {

        if (localStorage.getItem('bcitems')) {
            $("#bcTable tbody").empty();
            bcitems = JSON.parse(localStorage.getItem('bcitems'));
			sortedItems = _.sortBy(bcitems, function(o,i){return [o.c];}).reverse();
            $.each(sortedItems, function () {
                var item = this;
                var row_no = item.id;
                var vd = '';
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item.id + '" data-item-id="' + item.id + '"></tr>');
                tr_html = '<td><input name="product[]" type="hidden" value="' + item.id + '"><span id="name_' + row_no + '">' + item.name + ' (' + item.code + ')</span></td>';
                tr_html += '<td><input class="form-control quantity text-center" name="quantity[]" type="text" value="' + formatDecimal(item.qty) + '" data-id="' + row_no + '" data-item="' + item.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                if(item.variants) {
                    $.each(item.variants, function () {
                        vd += '<input name="vt_'+ item.id +'_'+ this.id +'" type="checkbox" class="checkbox" id="'+this.id+'" data-item-id="'+item.id+'" value="'+this.id+'" '+( item.selected_variants[this.id] == 1 ? 'checked="checked"' : '')+' style="display:inline-block;" /><label  class="padding05">'+this.name+' <input value="'+formatDecimal(this.quantity)+'" style="width:50px; height:30px; text-align:center" type="text" class="varaint_qty" name="varaint_qty_'+ item.id +'_'+ this.id +'"/></label>';
                    });
                }
                tr_html += '<td>'+vd+'</td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.appendTo("#bcTable");
            });
            $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            return true;
        }
    }

</script>