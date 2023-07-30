<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        
		<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('down_payment').' ('.lang('sale').' '.lang('reference').': '.$inv->reference_no.')'; ?></h4>
		</div>
		<?php 
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo form_open_multipart("sales/down_payment/" . $inv->id, $attrib);
			$payment_amount = $inv->grand_total - $inv->paid;
			$payment_period = 7;
			$payment_term = 1;
			$payment_date = date("Y-m-d");
		?>
        <div class="modal-body">
			<div class="row">
				<div class="col-sm-3">
					<div class="form-group">
						<label for="payment_amount"><?= lang("amount") ?></label>
						<input type="text" class="form-control" value="<?= $this->cus->formatDecimal($payment_amount) ?>" name="payment_amount" id="payment_amount" />
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<label for="payment_period"><?= lang("period") ?></label>
						<input type="number" class="form-control" min=1 value="<?= $payment_period ?>" name="payment_period" id="payment_period" />
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<label for="payment_term"><?= lang("term") ?></label>
						<input type="number" class="form-control" min=1 value="<?= $payment_term ?>" name="payment_term" id="payment_term" />
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<label for="payment_date"><?= lang("date") ?></label>
						<input type="text" class="form-control date" value="<?= $this->cus->hrsd($payment_date) ?>" name="payment_date" id="payment_date" />
					</div>
				</div>
			</div>
			
            <div class="table-responsive">
                <table id="down_payment" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
						<tr>
							<th width="10px;"><?= lang("#"); ?></th>
							<th width="100px;"><?= lang("deadline"); ?></th>
							<th width="100px;"><?= lang("payment"); ?></th>
							<th width="80px;"><?= lang("%"); ?></th>
						</tr>
                    </thead>
					<tbody class="tbody"><?= $html; ?></tbody>
					<tfoot class="tfoot"></tfoot>
                </table>
            </div>
        </div>
		<div class="modal-footer">
            <?= form_submit('down_payment', lang('down_payment'), ' class="btn btn-primary"');?>
        </div>
		<?php echo form_close(); ?>
    </div>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
		
		$("#payment_term").on("change",down_payment);
		
		down_payment();
		
		function down_payment(e){
			var payment_term = $("#payment_term").val() - 0;
			var payment_amount = $("#payment_amount").val() - 0;
			var payment_period = $("#payment_period").val() - 0;
			var payment_date = $("#payment_date").val();
			var payment = payment_amount / payment_term;
			var split_date = payment_date.split("/");
			var date = new Date(split_date[2], split_date[1] - 1, split_date[0]);
			var html = '', fhtml = '', total = 0;
			for(var i = 1; i <= payment_term; i++){
				total += payment;
				var percent = (payment * 100) / payment_amount ;
				var increment = i == 1 ? 1 : (payment_period * 1); 
					date.setDate(date.getDate() + increment);
				var get_date = fsd(date.toJSON().slice(0,10));
				html += '<tr>';
						html += '<td style="text-align:center">'+ i +'<input type="hidden" class="no" name="no[]" value="'+i+'" /></td>';
						html += '<td><input type="text" style="text-align:center" value="'+get_date+'" class="form-control date deadline" name="deadline[]" /></td>';
						html += '<td><input type="text" style="text-align:right" value="'+payment+'" class="form-control payment" autocomplete="off" name="payment[]" /></td>';
						html += '<td><input type="text" style="text-align:center" value="'+percent+'" class="form-control percent" autocomplete="off" name="percent[]" /></td>';
				html += '</tr>';
			}
			
				fhtml += '<tr>';
						fhtml += '<th></th>';
						fhtml += '<th></th>';
						fhtml += '<th style="text-align:right" id="total"><input type="text" style="text-align:right" readonly class="form-control" value="'+formatDecimal(total)+'" /></th>';
						fhtml += '<th></th>';
				fhtml += '</tr>';
			
			$("#down_payment .tbody").html(html);
			$("#down_payment .tfoot").html(fhtml);
		}
		
		// Payment Keyin
		$(document).on('change', '.payment', function (e) {
			var payment_amount = $("#payment_amount").val() - 0;
			var self = $(this).parent().parent().find(".no").val() - 0;
			var above = 0, i = 0;
			$(".payment").each(function(){
				var loop_above = $(this).parent().parent().find(".no").val() - 0;
				if(loop_above > self){
					i++;
				}else{
					var payment = $(this).val() - 0
					above += payment;
				}
			});
			var total = 0, below = (payment_amount - above) / i;
			$('.payment').each(function(){
				var parent = $(this).parent().parent();
				var loop_below = $(this).parent().parent().find(".no").val() - 0;
				if(loop_below > self){
					$(this).val(below);
				}
				var payment = $(this).val() - 0;
				var percent = (payment * 100) / payment_amount;
				parent.find(".percent").val(percent);
				total += payment;
			});
			$("#total input").val(total);
		});
		
		// Percent Keyin
		$(document).on('change', '.percent', function (e) {
			var payment_amount = $("#payment_amount").val() - 0;
			var self = $(this).parent().parent().find(".no").val() - 0;
			var above = 0, i = 0;
			$(".percent").each(function(){
				var percent_above = $(this).parent().parent().find(".no").val() - 0;
				if(percent_above > self){
					i++;
				}else{
					var percent = $(this).val() - 0
					above += percent;
				}
			});
			var below = (100 - above) / i;
			$('.percent').each(function(){
				var parent = $(this).parent().parent();
				var percent_below = $(this).parent().parent().find(".no").val() - 0;
				if(percent_below > self){
					$(this).val(below);
				}
				var percent = $(this).val() - 0;
				var payment = (percent * payment_amount) / 100;
				parent.find(".payment").val(payment);
			});
		});
    });
</script>
