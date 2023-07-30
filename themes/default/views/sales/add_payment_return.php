<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_payment_return'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("sales/add_payment_return/" . $inv->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
               <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-sm-6 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                    </div>
                </div>
                <input type="hidden" value="<?php echo $inv->id; ?>" name="sale_id"/>
            </div>
            
			<?php
				$now = time();
				$invoice_date = strtotime($inv->date);
				$datediff = $now - $invoice_date;
				$payment_date = round($datediff / (60 * 60 * 24));
				$discount = 0;
                if (isset($payment_term) && $payment_term != false) {
                   if($payment_date < $payment_term->due_day_discount){
                        if($payment_term->discount_type == "Percentage"){
                            $discount = ($payment_term->discount * $inv->grand_total) / 100;
                        }else{
                            $discount = $payment_term->discount;
                        }
                    }       
                }
				$credit_interest = 0;
				if($Settings->installment==1){
					$installment = $this->sales_model->getInstallmentBySaleID($inv->sale_id);
					echo form_hidden("installment_id", ($installment ? $installment->id : ''));
					$return_interest = 0;
					if($return_payments){
						foreach($return_payments as $return_payment){
							$return_interest -= (double)$return_payment->interest_paid;
						}
					}
					if($sale_payments){
						foreach($sale_payments as $sale_payment){
							$credit_interest += (double)$sale_payment->interest_paid;
						}
					}
					$credit_interest = $this->cus->formatDecimal($credit_interest - $return_interest - $inv->surcharge_interest);
				}
				$credit_amount = $this->cus->formatDecimal(abs($inv->grand_total) - abs($inv->paid));
			?>
			<div class="clearfix"></div>
			
            <div id="payments">

                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
							
                            <div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("amount", "amount_1"); ?>
                                        <input name="amount-paid" readonly="readonly" type="text" id="amount_1"
                                               value="0"
                                               class="pa form-control kb-pad amount" required="required"/>
                                    </div>
                                </div>
                            </div>
							
							<div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("discount", "discount"); ?>
                                        <input name="discount" value="<?= $discount; ?>" type="text" class="form-control" id="discount"/>
                                    </div>
                                </div>
                            </div>
		
							<?php if($Settings->installment==1){ ?>
								<div class="col-sm-6">
									<div class="form-group">
										<?= lang("interest_paid", "interest_paid"); ?>
										<input type="text" name="interest-paid" class="form-control interest_paid" value="<?= $credit_interest ?>" />
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<?= lang("principal_paid", "principal_paid"); ?>
										<input type="text" name="principal-paid" class="form-control principal_paid" value="<?= $credit_amount ?>" />
									</div>
								</div>
							<?php } ?>
							
							<?php 							
							foreach($currencies as $currency){ 
								$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
								$base_amount = $this->cus->formatDecimal(($credit_amount + $credit_interest)/$base_currency->rate);
								$amount = $base_amount * $currency->rate;
							?>
								<div class="col-sm-8">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> : <span id="am_<?= $currency->code ?>"><?= $this->cus->formatDecimal($amount) ?> (<?= $currency->code ?>)</span>
										<input c_code="<?= $currency->code ?>" name="c_amount[]" value="<?= ($currency->code==$base_currency->code?$base_amount:0) ?>" rate="<?= $base_currency->rate ?>" type="text" <?= ($base_currency->code==$currency->code?"default=true":"") ?> class="form-control c_amount"/>
										<input name="currency[]" value="<?= $currency->code ?>" type="hidden" />								
									</div>                                
								</div>
								<div class="col-sm-4">								
									<div class="form-group">
										<?= lang("rate", "rate"); ?>
										<input <?= ($currency->code == 'USD' ? 'readonly' : '') ?> id="<?= $currency->code ?>" name="rate[]" value="<?= $currency->rate ?>" type="text" class="form-control rate" />										
									</div>                                
								</div>
							<?php } ?>
							<div class="col-sm-12">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by" required="required">
                                       <?= $this->cus->cash_opts(false,true,false,true); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>

            </div>

            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_payment_return', lang('add_payment_return'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
		
		var old_rate;
		$(document).on("focus", '.rate', function () {
			old_rate = $(this).val();
		}).on("change", '.rate', function () {
			if($(this).val() == ''){
				$(this).val(0);
			}else if (!is_numeric($(this).val())) {
				$(this).val(old_rate);
				return;
			}
			$('.c_amount').change();
		}); 
		
		var old_discount;
		$(document).on("focus", '#discount', function () {
			old_discount = $(this).val();
		}).on("change", '#discount', function () {
			if($(this).val() == ''){
				$(this).val(0);
			}else if (!is_numeric($(this).val())) {
				$(this).val(old_discount);
				return;
			}
			$('.c_amount').change();
		}); 
		
		var old_amount;
		$(document).on("focus", '.c_amount', function () {
			old_amount = $(this).val();
		}).on("change", '.c_amount', function () {
			var row = $(this).closest('tr');
			if($(this).val() == ''){
				$(this).val(0);
			}else if (!is_numeric($(this).val())) {
				$(this).val(old_amount);
				return;
			}
			var c_total = 0;
			$(".c_amount").each(function(){
				var base_rate = formatDecimal($(this).attr("rate"),11);
				var code = $(this).attr("c_code");
				var rate =  $("#"+code).val() - 0;
				var amount = formatDecimal($(this).val(),11);
				var base_amount = amount / rate;
				var camount = base_amount * base_rate;
					c_total += camount;
			});
			var discount = $('#discount').val() - 0;
			var total_amount = formatDecimal('<?= $base_amount ?>');
			var balance_amount = total_amount - c_total - discount;
			$(".c_amount").each(function(){
				var code = $(this).attr("c_code");
				var rate =  $("#"+code).val() - 0;
				var amount_html = formatMoney(rate * balance_amount) + ' ('+ code +')';
				$("#am_"+code).html(amount_html);
			});
			$("#amount_1").val(c_total);	
		}); 
		
        $("#date").datetimepicker({
            <?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
            fontAwesome: true,
            language: 'cus',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
		
		$(".interest_paid,.principal_paid").on("change",function(){
			var interest_paid = $(".interest_paid").val() - 0;
			var principal_paid = $(".principal_paid").val() - 0;
			var amount_paid = interest_paid + principal_paid; 
			$(".c_amount").each(function(){
				$("[default=true]").val(amount_paid);
			});
		});
		
		$(".c_amount").on("change",function(){
			var amount_paid = $(this).val();
			var interest = (("<?= $credit_interest; ?>") * 100) / ("<?= ($credit_amount+ $credit_interest); ?>");
			var principal = (("<?= $credit_amount; ?>") * 100) / ("<?= ($credit_amount+ $credit_interest); ?>");
			var interest_paid  = (amount_paid * interest / 100);
			var principal_paid = (amount_paid * principal / 100);
			$(".interest_paid").val(interest_paid);
			$(".principal_paid").val(principal_paid - interest_paid);
		});
		$(".c_amount").change();
    });
</script>
