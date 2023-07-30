<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

	<div class="box">
		<div class="box-header">
			<h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('category_discount'); ?> (<?= $category->name ?>)</h2>
		</div>		
		<?php			
			echo form_open_multipart("system_settings/category_discount/".$id."/".$parent_id);
		?>				
		<div class="box-content">
			<div class="row">				
				<div class="col-sm-3">
					<div class="form-group">
						<?= lang("start_date","start_date") ?>
						<div class="controls">
							<input type="text" name="start_date" value="<?= date("d/m/Y") ?>" required class="form-control date " />
						</div>
					</div>
				</div>
				
				<div class="col-sm-3">
					<div class="form-group">
						<?= lang("end_date","end_date") ?>
						<div class="controls">
							<input type="text" name="end_date" value="<?= date("d/m/Y") ?>" required class="form-control date " />
						</div>
					</div>
				</div>
				
				<div class="col-sm-3">
					<div class="form-group">
						<?= lang("discount","discount") ?> <small class="red">(%/Value)</small>
						<div class="controls">
							<input type="text" value="0" class="setPrice form-control " />
						</div>
					</div>
				</div>
				
				<div class="col-sm-3">
					<div class="form-group">
						<?= lang("markup_price","markup_price") ?> <small class="red">(Value)</small>
						<div class="controls">
							<input type="text" value="0" class="markupPrice form-control " />
						</div>
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">						
						<div class="controls">
							<input type="submit" name="submit" value="<?= lang("submit") ?>" class="btn btn-danger" />
						</div>
					</div>
				</div>
				
				<div class="col-sm-12">
					<table class="table table-bordered table-condensed table-hover table-striped dataTable">
						<thead>
							<tr>								
								<th><?= lang("code");?></th>
								<th><?= lang("category");?></th>
								<th><?= lang("name");?></th>
								<th style="width:200px;"><?= lang("price");?></th>
								<th style="width:200px;"><?= lang("discount");?></th>
								<th style="width:200px;"><?= lang("promotion_price");?></th>
								<th style="width:200px;"><?= lang("date");?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
							if($products){
								foreach($products as $i => $product){
									$category = $this->settings_model->getCategoryByID($product->category_id);
							?>
							<tr>								
								<td class="center"><?= $product->code?></td>
								<td class="center"><?= $category->name ?></td>
								<td><?= $product->name?></td>
								<td class="right">
									<?= $this->cus->formatMoney($product->price); ?>
									<input type="hidden" value="<?= $product->price; ?>" class="base_price input-sm" />
								</td>
								<td>
									<input type="text" value="<?= (100-(($product->promo_price / $product->price) * 100)).'%';?>" name="discount[]"  class="discount form-control input-sm text-right" />
								</td>
								<td class="right">
									<input type="hidden" name="product_id[]" value="<?= $product->id ?>" />
									<input type="text" name="price[]" value="<?= $this->cus->formatDecimal($product->promo_price); ?>" class="price form-control input-sm text-right" />
								</td>								
								<td class="center">	
									<?php if($product->start_date){ ?>
										<?= $this->cus->hrsd($product->start_date) . " - " .$this->cus->hrsd($product->end_date); ?>									
									<?php } ?>
								</td>								
							</tr>
							<?php } 
							}else{
								 echo "<tr><td colspan='6'>".lang('no_product')."</td></tr>";
							} ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>   
		
		<?php echo form_close(); ?>
		
	</div>
</div>

<script type="text/javascript">


	$(function(){
		
		$(".setPrice").on("keyup",markup_price);
		$(".markupPrice").on("keyup",markup_price);
		
		function markup_price(){
			var setPrice = $(".setPrice").val();
			var markupPrice = $(".markupPrice").val();
			
			var total = 0;
			$(".price").each(function(){
				var price = $(this).parent().parent().find(".base_price").val();				
				if (markupPrice.indexOf("%") !== -1) {
					var mds = markupPrice.split("%");	
					markupPrice = price + (mds[0] * price / 100);
				}				
				if (setPrice.indexOf("%") !== -1) {
					var pds = setPrice.split("%");					
					total = price - (pds[0] * price / 100) + formatDecimal(markupPrice);	
				} else {
					total = (price - setPrice) + formatDecimal(markupPrice);	
				}				
				$(this).val(total);				
				$(this).parent().parent().find(".discount").val(setPrice);
			});
		}
		$('.discount').live('keyup',function(){
			var setPrice =$(this).val();
			var markupPrice = $(".markupPrice").val();
			var price = $(this).parent().parent().find(".base_price").val();	
				
			if (markupPrice.indexOf("%") !== -1) {
				var mds = markupPrice.split("%");	
				markupPrice = price + (mds[0] * price / 100);
			}				
			if (setPrice.indexOf("%") !== -1) {
				var pds = setPrice.split("%");					
				total = price - (pds[0] * price / 100) + formatDecimal(markupPrice);	
			} else {
				total = (price - setPrice) + formatDecimal(markupPrice);	
			}				
			$(this).parent().parent().find(".price").val(total);				
		});
	});
</script>
