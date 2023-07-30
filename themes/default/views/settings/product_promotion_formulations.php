<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
	$tbody = '';
	if($product_promotions){
		foreach($product_promotions as $product_promotion){
			$tbody .= '<tr>';
			$tbody .= '<td><input value="'.($product_promotion->for_product_id ? $product_promotion->for_product_id : 0).'" type="hidden" class="for_product_id" name="for_product_id[]" /><input value="'.($product_promotion->product_name ? $product_promotion->product_name : 0).'" class="form-control tip for_product" id="for_product" type="text" name="for_product[]"/></td>';
			$tbody .= '<td><input value="'.($product_promotion->for_min_quantity ? $product_promotion->for_min_quantity : 0).'" class="form-control text-right tip for_min_quantity" id="for_min_quantity" type="text" name="for_min_quantity[]"/></td>';
			$tbody .= '<td><input value="'.($product_promotion->for_max_quantity ? $product_promotion->for_max_quantity : 0).'" class="form-control text-right tip for_max_quantity" id="for_max_quantity" type="text" name="for_max_quantity[]"/></td>';
			$tbody .= '<td><input value="'.($product_promotion->for_free_quantity ? $product_promotion->for_free_quantity : 0).'" class="form-control text-right tip for_free_quantity" id="for_free_quantity" type="text" name="for_free_quantity[]"/></td>';
			$tbody .= '<td class="text-center"><i class="fa fa-times tip for_del" title="Remove" style="cursor:pointer;"></i></td>';
			$tbody .= '</tr>';
		}
	}
?>

<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('product_promotion_formulations').' ('.$promotion->name.') ('.$product->code.' - '.$product->name.')' ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="well well-sm no-print">
					<?php echo form_open_multipart("system_settings/product_promotion_formulations") ?>
						<input type="hidden" name= "promotion_id" id="promotion_id" value="<?= $promotion->id ?>" />
						<input type="hidden" name= "product_id" id="product_id" value="<?= $product->id ?>" />
						<div class="controls table-controls">
							<table id="formulationTable" class="table items table-striped table-bordered table-condensed table-hover">
								<thead>
									<tr>
										<th class="col-xs-4"><?= lang("product")?></th>
										<th class="col-xs-2"><?= lang("min_quantity")?></th>
										<th class="col-xs-2"><?= lang("max_quantity")  ?></th>
										<th class="col-xs-2"><?= lang("free_quantity")  ?></th>
										<th style="width:5%">
											<i id="add_formulation" class="fa fa-plus" style="opacity:0.5; filter:alpha(opacity=50);"></i>
										</th>
									</tr>
								</thead>
								<tbody>
									<?= $tbody ?>
								</tbody>
							</table>
						</div>
						<div class="form-group">
							<?php echo form_submit('product_promotion_formulations', lang("submit"), 'class="btn btn-primary"'); ?>
						</div>
                    <?= form_close(); ?>
                    <div class="clearfix"></div>
                </div>
			</div>	
		</div>	
	</div>
</div>	
<script type="text/javascript">
    $(document).ready(function() {
		$('#add_formulation').click(function(){
			var tr_html = '<tr>';
			tr_html +=  '<td><input type="hidden" class="for_product_id" name="for_product_id[]" /><input class="form-control tip for_product" id="for_product" type="text" name="for_product[]"/></td>';
			tr_html +=  '<td><input value="0" class="form-control text-right tip for_min_quantity" id="for_min_quantity" type="text" name="for_min_quantity[]"/></td>';
			tr_html +=  '<td><input value="0" class="form-control text-right tip for_max_quantity" id="for_max_quantity" type="text" name="for_max_quantity[]"/></td>';
			tr_html +=  '<td><input value="0" class="form-control text-right tip for_free_quantity" id="for_free_quantity" type="text" name="for_free_quantity[]"/></td>';
			tr_html += '<td class="text-center"><i class="fa fa-times tip for_del"  title="Remove" style="cursor:pointer;"></i></td></tr>';
			$("#formulationTable").append(tr_html);	
        });
		
		$(document).on('click', '.for_del', function () {
            var id = $(this).attr('id');
            $(this).parent().parent().remove();
        });

		var old_row_qty;
		$(document).on("focus", '.for_min_quantity, .for_max_quantity, .for_free_quantity', function () {
			old_row_qty = $(this).val();
		}).on("change", '.for_min_quantity, .for_max_quantity, .for_free_quantity', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_row_qty);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});
		
		$(".for_product:not(.ui-autocomplete-input)").live("focus", function (event) {
			$(this).autocomplete({
				source: '<?= site_url('products/suggestions'); ?>',
				minLength: 1,
				autoFocus: false,
				delay: 250,
				response: function (event, ui) {
					if (ui.content.length == 1 && ui.content[0].id != 0) {
						ui.item = ui.content[0];
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
						$(this).autocomplete('close');
						$(this).removeClass('ui-autocomplete-loading');
					}
				},
				select: function (event, ui) {
					event.preventDefault();
					if (ui.item.id !== 0) {
						var parent = $(this).parent().parent();
						parent.find(".for_product_id").val(ui.item.id);
						$(this).val(ui.item.label);
					} else {
						bootbox.alert('<?= lang('no_match_found') ?>');
					}
				}
			});
		});
		
    });
</script>