<?php defined('BASEPATH') OR exit('No direct script access allowed');
	$rate = $currency->rate;
?>
<style>
	.btn-success{
		width:200px !important;
	}
	.rate{
		text-align:center;
		background:#428BCA; 
		color:white;
	}
	.rate1{
		text-align:center;
		background:white; 
		color:black;
	}
	.qty_kh{
		text-align:right;
	}
	.qty_use{
		text-align:right;
	}
	
</style> 

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title">
				 (<?= strtoupper($this->session->userdata("username")) ?>)
				 <?= lang('khmer_rate') ?> : <?= $this->cus->formatMoney($rate) ?>
			</h4>
        </div>
        <?php 
		$attrib = array('role' => 'form');
        echo form_open_multipart("pos/count_money/", $attrib);
        ?>
        <div class="modal-body">
			<div class="row">
				<div class="col-sm-12">
					<h2>ក្រដាសប្រាក់សរុប</h2>
						<table class="stable table table-bordered"​ >
							<tr>
								<th class="rate" style="width:30%;">ក្រដាសប្រាក់ ៛</th>
								<th class="rate" style="width:20%;">ចំនូន</th>
								<th class="rate" style="width:30%;">ក្រដាសប្រាក់ $</th>
								<th class="rate" style="width:20%;">ចំនូន</th>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="100" style=""><b >១០០ ៛</b></button></td>
								<td class="rate1" ><input  class="form-control padding0 input-tip qty_kh" value_kh="100" type="text" id="kh_100" name="100_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="1" style=""><b>1 $</b></button></td>
								<td class="rate1" ><input  class="form-control padding0 input-tip qty_use" value_use="1" type="text" id="use_1" name="1_use" value="0"></input></td>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="500" style=""><b>៥០០ ៛</b></button></td>
								<td class="rate1" ><input  class="form-control padding0 input-tip qty_kh" value_kh="500" type="text" id="kh_500" name="500_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="2" style=""><b>2 $</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_use" value_use="2" type="text" id="use_2"  name="2_use" value="0"></input></td>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="1000" style=""><b>១០០០ ៛</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_kh" value_kh="1000" type="text" id="kh_1000" name="1k_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="5" style=""><b>5 $</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_use" value_use="5" type="text" id="use_5" name="5_use" value="0"></input></td>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="2000" style=""><b>២០០០ ៛</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_kh" value_kh="2000" type="text" id="kh_2000" name="2k_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="10" style=""><b>10 $</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_use" value_use="10" type="text" id="use_10" name="10_use" value="0"></input></td>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="5000" style=""><b>៥០០០ ៛</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_kh" value_kh="5000" type="text" id="kh_5000" name="5k_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="20" style=""><b>20 $</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_use" value_use="20" type="text" id="use_20" name="20_use" value="0"></input></td>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="10000" style=""><b style="margin-left:-px;">១០០០០ ៛</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_kh" value_kh="10000" type="text" id="kh_10000" name="10k_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="50" style=""><b>50 $</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_use" value_use="50" type="text" id="use_50" name="50_use" value="0"></input></td>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="20000" style=""><b style="margin-left:-px;">២០០០០ ៛</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_kh" value_kh="20000" type="text" id="kh_20000" name="20k_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="100" style=""><b>100 $</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_use" value_use="100" type="text" id="use_100" name="100_use" value="0"></input></td>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="50000" style=""><b style="margin-left:-px;">៥០០០០ ៛</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_kh" value_kh="50000" type="text" id="kh_50000" name="50k_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="500" style=""><b>500 $</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_use" value_use="500" type="text" id="use_500" name="500_use" value="0"></input></td>
							</tr>
							<tr>
								<td class="rate1" ><button class="btn btn-success label-info currency" value="100000" style=""><b style="margin-left:-px;">១០០០០០ ៛</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_kh" value_kh="100000" type="text" id="kh_100000" name="100k_riel" value="0"></input></td>
								<td class="rate1" ><button class="btn btn-success label-info currency_" value="1000" style=""><b>1000 $</b></button></td>
								<td class="rate1" ><input class="form-control padding0 input-tip qty_use" value_use="1000" type="text" id="use_1000" name="1k_use" value="0"></input></td>
							</tr>
						</table>
				</div>
			</div>
			<table width="100%" class="stable table table-bordered"​​ border="1">
				<tr>
					<td class="rate1" style="width:30%"><span>ប្រាក់<b> ៛ </b> សរុប</span></td>
					<td class="rate1" style="width:20%"><input class="rate1 form-control padding0 input-tip" id="total_kh_" type="text" value="0" readonly="true" name="rate_kh" style="background:#428BCA"></input></td>
					<td class="rate1" style="width:30%"><span>ប្រាក់<b> $ </b>សរុប</span></td>
					<td class="rate1" style="width:20%"><input class="rate1 form-control padding0 input-tip" id="total_use_" type="text" value="0" readonly="true" name="rate_use" style="background:#428BCA"></input></td>
				</tr>
				<tr>
					<td>ប្រាក់រៀលសរុប</td>
					<td colspan="3" class="total_riel" style="text-align:right;" ></td>
				</tr>
				<tr>
					<td>ប្រាក់ដុល្លាសរុប  <input type="hidden" name="total_money" class="total_money"/></td>
					<td colspan="3" class="total_use" style="text-align:right;" ></td>
				</tr>
	
            </table>

            
            
        </div>
        <div class="modal-footer no-print">
            <?= form_submit('count_money', lang('count_money'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>

</div>
<?= $modal_js ?>
<script type="text/javascript">

	
	$('.currency').click(function(){
		var value = $(this).val();
		var old_value = $('#kh_' + value).val()-0;
		$('#kh_' + value).val(old_value+1);
		return false;
	});
	
	$('.currency_').click(function(){
		var value = $(this).val();
		var old_value = $('#use_' + value).val()-0;
		$('#use_' + value).val(old_value+1);
		return false;
		
	});
	
	$('.currency').click(function(){
		var value = $(this).val()-0;
		var rate = '<?= $rate?>'
		var use = $('#total_use_').val()-0;
		var total_rate = use * rate;
		var plus_kh_value = $('#total_kh_').val()-0;
		var total = plus_kh_value +  value;
		$('#total_kh_').val(total);
		$('.total_use').html(formatMoney((total/rate) + use ));
		$('.total_riel').html(formatMoney(total + total_rate));
		$('.total_money').val((total/rate) + use);
		
	});
	$('.currency_').click(function(){
		var value = $(this).val()-0;
		var rate = '<?= $rate ?>';
		var khmer = $('#total_kh_').val()-0;
		var total_rate = khmer/rate;
		var plus_use_value = $('#total_use_').val()-0;
		var total = plus_use_value +  value;
		$('#total_use_').val(total);
		$('.total_riel').html(formatMoney((total * rate) + khmer));
		$('.total_use').html(formatMoney(total + total_rate));
		$('.total_money').val(total+total_rate);
		
	});
	$(document).on("change", ".qty_kh", function(){
		 var sum = 0;
		 var rate = '<?= $rate?>';
		 var use = $('#total_use_').val()-0;
		 var total_rate = use * rate;
		 $(".qty_kh").each(function(){
			var value_kh = $(this).attr('value_kh')-0;
			if(!isNaN(value_kh)){
				sum += (value_kh * $(this).val()-0);
			}
	        
	    });
		$('#total_kh_').val(sum );
		$('.total_use').html(formatMoney((sum/ rate ) + use));
		$('.total_riel').html(formatMoney(sum + total_rate));
		$('.total_money').val((sum/ rate ) + use);
	});

	$(document).on("change", ".qty_use", function(){
		 var sum_ = 0;
		 var rate = '<?= $rate?>';
		 var khmer = $('#total_kh_').val()-0;
		 var total_rate = khmer/rate;
		 $(".qty_use").each(function(){
			var value_use = $(this).attr('value_use')-0;
			if(!isNaN(value_use)){
				sum_ += (value_use * $(this).val()-0);
			}
	        
	    });
		$('#total_use_').val(sum_);
		$('.total_use').html(formatMoney(total_rate+sum_));
		$('.total_riel').html(formatMoney((sum_* rate) + khmer));
		$('.total_money').val(total_rate+sum_);
	});
	
	
</script>


