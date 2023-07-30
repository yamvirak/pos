<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">
				<?php echo lang('submit_cash'); ?>
				(<?= $fuel_sale->reference_no; ?>)
			</h4>
        </div>
		<?php 
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo form_open_multipart("sales/submit_cash/" . $fuel_sale->id, $attrib);
		?>
		<div class="modal-body">
			<p><?= lang('enter_info'); ?></p>
			<div class="control-group table-group">
				<label class="table-label"><?= lang("count_money"); ?> *</label>
				<div class="controls table-controls" style="margin-top:20px;">
					<table class="table table-borderless table-condensed table-striped">
						<?php 
							$moneys_USD = array("100","50","20","10","5","1","-1","-2","-3","-4");
							$moneys_KHR = array("100000","50000","20000","10000","15000","5000","2000","1000","500","100");
							if($moneys_KHR){
								$total_cash_kh = 0;
								$total_cash_usd = 0;
								$json_total_cash_count = json_decode($fuel_sale->json_total_cash_count);
								$json_total_cash_open = json_decode($fuel_sale->json_total_cash_open);
								if($json_total_cash_count){
									$query_usd = json_decode($json_total_cash_count->USD);
									$query_kh = json_decode($json_total_cash_count->KHR);
								}
								echo '<tr>';
									if($moneys_USD){
										$change_usd = ($json_total_cash_open?$json_total_cash_open->USD->amount:0);
										echo '<td colspan="3">
												<input name="change_usd" class="form-control change-money-usd" autocomplete="off" value="'.$this->cus->formatDecimal($change_usd).'" placeholder="'.lang("USD").'" />
											</td>';
									}
									if($moneys_KHR){
										$change_kh = ($json_total_cash_open?$json_total_cash_open->KHR->amount:0);
										echo '<td colspan="3">
												<input name="change_kh" class="form-control change-money-kh" autocomplete="off" value="'.$this->cus->formatDecimal($change_kh).'" placeholder="'.lang("KHR").'" />
											</td>';
									}
								echo '</tr>';
								foreach($moneys_KHR as $k=> $money){
									echo '<tr>';
										if($moneys_USD){
											$value_usd = 0;
											$subvalue_usd = 0;
											if(isset($query_usd) && $query_usd->{$moneys_USD[$k]} >= 1){
												$value_usd = $query_usd->{$moneys_USD[$k]};
												$subvalue_usd = $moneys_USD[$k] * $query_usd->{$moneys_USD[$k]};
											}
											$total_cash_usd += $subvalue_usd;
											if($moneys_USD[$k] <= 0){
												echo '<td style="width:120px;"><button style="width:100%;" class="text-right btn btn-primary" value="'.$moneys_USD[$k].'" onClick="return false;">'.$this->cus->formatDecimal(0).'</button></td>';
											}else{
												echo '<td style="width:120px;"><button style="width:100%;" class="btn-money-usd text-right btn btn-primary" value="'.$moneys_USD[$k].'" onClick="return false;">'.$this->cus->formatDecimal($moneys_USD[$k]).'</button></td>';
											}
											echo '<td style="width:120px;"><input type="number" min=0 name="count-money-usd['.$moneys_USD[$k].']" value="'.$value_usd.'" class="count-money-usd form-control" /></td>';
											echo '<td style="width:120px;"><button style="width:100%;" class="val-money-usd text-right btn btn-danger" onClick="return false;">'.$subvalue_usd.'</button></td>';
										}
										if($moneys_KHR){
											$value_kh = 0;
											$subvalue_kh = 0;
											if(isset($query_kh) && $query_kh->{$money} >= 1){
												$value_kh = $query_kh->{$money};
												$subvalue_kh = $money * $query_kh->{$money};
											}
											$total_cash_kh += $subvalue_kh;
											echo '<td style="width:120px;"><button style="width:100%;" class="btn-money-kh text-right btn btn-primary" value="'.$money.'" onClick="return false;">'.number_format($money,-1).'</button></td>';
											echo '<td style="width:120px;"><input type="number" min=0 name="count-money-kh['.$money.']" value="'.$value_kh.'" class="count-money-kh form-control" /></td>';
											echo '<td style="width:120px;"><button style="width:100%;" class="val-money-kh text-right btn btn-danger" onClick="return false;">'.$subvalue_kh.'</button></td>';
										}
									echo '</tr>';
								}
								echo '<tr>';
									if($moneys_USD){
										echo '<td></td>';
										echo '<td class="text-center"></td>';
										echo '<td class="text-right bold" id="total_USD" style="font-size:18px;">'.number_format($total_cash_usd).'</td>';
									}
									if($moneys_KHR){
										echo '<td></td>';
										echo '<td class="text-center"></td>';
										echo '<td class="text-right bold" id="total_KHR" style="font-size:18px;">'.number_format($total_cash_kh).'</td>';
									}
								echo '</tr>';
							}
						?>
					</table>
				</div>
			</div>
		</div>
		<div class="modal-footer">
            <?php echo form_submit('submit_cash', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		
		//========USD=========//
		$(".btn-money-usd").on("click",function(){
			var row = $(this).closest("tr");
			var count = row.find(".count-money-usd").val()-0;
			if(count >= 0){
				row.find(".count-money-usd").val(count+1);
				$(".count-money-usd").change();
			}
		});
		
		$(".val-money-usd").on("click",function(){
			var row = $(this).closest("tr");
			var count = row.find(".count-money-usd").val()-0;
			if(count > 0){
				row.find(".count-money-usd").val(count-1);
				$(".count-money-usd").change();
			}
		});
		
		$(".count-money-usd").on("change",function(){
			var row = $(this).closest("tr");
			var money = row.find(".btn-money-usd").attr("value")-0;
			var count = row.find(".count-money-usd").val()-0;
				row.find(".val-money-usd").text(formatDecimal(money * count));
				cal_total();
		});
		
		//========KH=========//
		$(".btn-money-kh").on("click",function(){
			var row = $(this).closest("tr");
			var count = row.find(".count-money-kh").val()-0;
			if(count >= 0){
				row.find(".count-money-kh").val(count+1);
				$(".count-money-kh").change();
			}
		});
		$(".val-money-kh").on("click",function(){
			var row = $(this).closest("tr");
			var count = row.find(".count-money-kh").val()-0;
			if(count > 0){
				row.find(".count-money-kh").val(count-1);
				$(".count-money-kh").change();
			}
		});
		$(".count-money-kh").on("change",function(){
			var row = $(this).closest("tr");
			var money = row.find(".btn-money-kh").attr("value")-0;
			var count = row.find(".count-money-kh").val()-0;
				row.find(".val-money-kh").text(formatDecimal(money * count));
				cal_total();
		});
		
		//========Calc=========//
		function cal_total(){
			var total = 0;
			$(".count-money-usd").each(function(){
				var row = $(this).closest("tr");
				var money_usd = row.find(".val-money-usd").text()-0;
				total += money_usd;
			});
			$("#total_USD").text(total);
			
			var total_kh = 0;
			$(".count-money-kh").each(function(){
				var row = $(this).closest("tr");
				var money_kh = row.find(".val-money-kh").text()-0;
				total_kh += money_kh;
			});
			$("#total_KHR").text(total_kh);
		}
		
	});
</script>