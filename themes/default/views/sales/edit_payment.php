<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("sales/edit_payment/" . $payment->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->cus->hrld($payment->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-sm-6 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment->reference_no), 'class="form-control tip" id="reference_no" required="required"'); ?>
                    </div>
                </div>
                <input type="hidden" value="<?php echo $payment->sale_id; ?>" name="sale_id"/>
            </div>
			
            <div class="clearfix"></div>
			
			<?php 
				$now = time();
				$invoice_date = strtotime($inv->date);
				$datediff = $now - $invoice_date;
				$payment_date = round($datediff / (60 * 60 * 24));
				$discount = 0;
				if($payment_term){
					if($payment_date < $payment_term->due_day_discount){
						if($payment_term->discount_type == "Percentage"){
							$discount = ($payment_term->discount * $inv->grand_total) / 100;
						}else{
							$discount = $payment_term->discount;
						}
					}
				}
				
			?>
			
            <div id="payments">
                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?php 
											echo lang("amount", "amount_1"); 
											if($discount > 0){
												echo "&nbsp;<small style='color:red'>( ".lang("payment_term")." ".$payment_term->discount." ".$payment_term->discount_type." )</small>";
											}
										?>
										
                                        <input name="amount-paid" readonly
                                               value="<?= $this->cus->formatDecimal($payment->amount); ?>" type="text"
                                               id="amount_1" class="pa form-control kb-pad amount"/>
                                    </div>
                                </div>
                            </div>
							
							<div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("discount", "discount"); ?>
                                        <input name="discount" value="<?= $payment->discount; ?>" type="text" class="form-control" id="discount"/>
                                    </div>
                                </div>
                            </div>

							<?php if($Settings->installment==1 && $inv->installment==1){ ?>
							
								<div class="col-sm-4">
									<div class="form-group">
										<?= lang("interest_paid", "interest_paid"); ?>
										<input type="text" name="interest-paid" class="form-control interest_paid" value="<?= $this->cus->formatDecimal($payment->interest_paid) ?>" />
									</div>
								</div>
								
								<div class="col-sm-4">
									<div class="form-group">
										<?= lang("principal_paid", "principal_paid"); ?>
										<input type="text" name="principal-paid" class="form-control principal_paid" value="<?= $this->cus->formatDecimal($payment->amount) ?>" />
									</div>
								</div>
								
								<div class="col-sm-4">		
									<?= lang("penalty_paid", "penalty_paid"); ?>
									<div class="form-group">
										<input name="penalty-paid" value="<?= $this->cus->formatDecimal($payment->penalty_paid); ?>" type="text" id="penalty" class="form-control"/>
									</div>                           
								</div>
							
							<?php } ?>
							
							<?php
							$p_currencies = array();
							$g_currencies = json_decode($payment->currencies);							
							foreach($g_currencies as $currency){
								$p_currencies[$currency->currency] = array('amount'=>$currency->amount, 'rate'=>$currency->rate);
							}
							foreach($currencies as $currency){ 
								$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
								$amount = $p_currencies[$currency->code] ? $p_currencies[$currency->code]['amount'] : 0;
								$rate = $p_currencies[$currency->code] ? $p_currencies[$currency->code]['rate'] : $currency->rate;
							?>
							
								
								<div class="col-sm-8">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?>
										<input c_code="<?= $currency->code ?>" name="c_amount[]" rate="<?= $base_currency->rate ?>" type="text"  value="<?= $amount ?>" class="form-control c_amount"/>
										<input name="currency[]" value="<?= $currency->code ?>" type="hidden" />								
									</div>                                
								</div>
								<div class="col-sm-4">								
									<div class="form-group">
										<?= lang("rate", "rate"); ?>
										<input <?= ($currency->code == 'USD' ? 'readonly' : '') ?> id="<?= $currency->code ?>" name="rate[]" value="<?= $rate ?>" type="text" class="form-control rate" />										
									</div>                                
								</div>
								
							<?php } ?>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                        <?= $this->cus->cash_opts($payment->paid_by); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
						<div class="row cbank" style="display: none;">
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("account_number", "account_number"); ?>
									<input name="account_number" value="<?= $payment->account_number ?>" type="text" id="account_number" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("account_name", "account_name"); ?>
									<input name="account_name" value="<?= $payment->account_name ?>" type="text" id="account_name" class="form-control"/>
								</div>
							</div>
						</div>
						<div class="row ccheque" style="display: none;">
							<div class="col-sm-12">
								<div class="form-group">
									<?= lang("bank_name", "bank_name"); ?>
									<input name="bank_name" value="<?= $payment->bank_name ?>" type="text" id="bank_name" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("cheque_number", "cheque_number"); ?>
									<input name="cheque_number" value="<?= $payment->cheque_number ?>" type="text" id="cheque_number" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("cheque_date", "cheque_date"); ?>
									<input name="cheque_date" value="<?= $this->cus->hrsd($payment->cheque_date && $payment->cheque_date != "0000-00-00" ? $payment->cheque_date : date("Y-m-d")) ?>" type="text" id="cheque_date" class="form-control date"/>
								</div>
							</div>
						</div>
						<div class="form-group gc" style="display: none;">
                            <?= lang("gift_card_no", "gift_card_no"); ?>
                            <input name="gift_card_no" type="text" id="gift_card_no" class="pa form-control kb-pad"/>
                            <div id="gc_details"></div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
			
			<?php if($Settings->installment==1){ ?>
				<div class="form-group">
					<?= lang("is_deposit", "is_deposit"); ?>
					<input name="is_deposit" type="checkbox" <?= ($payment->is_deposit==1?"checked":"") ?> id="is_deposit" class="form-control"/>
				</div>
			<?php } ?>
			
            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $payment->note), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_payment', lang('edit_payment'), 'class="btn btn-primary"'); ?>
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
		
		$(".c_amount").on("change",function(){
			var amount = $(this).val();
			var interest = (("<?= $payment->interest_paid; ?>") * 100) / ("<?= ($payment->amount + $payment->interest_paid); ?>");
			var principal = (("<?= $payment->amount; ?>") * 100) / ("<?= ($payment->amount + $payment->interest_paid); ?>");
			var interest_paid  = (amount * interest / 100);
			var principal_paid = (amount * principal / 100);
			$(".interest_paid").val(interest_paid);
			$(".principal_paid").val(principal_paid);
		});
		
		$(".interest_paid, .principal_paid").on("change",function(){
			var interest_paid  = $(".interest_paid").val() - 0;
			var principal_paid = $(".principal_paid").val() - 0;
			var amount_paid    = interest_paid + principal_paid; 
			$(".c_amount").each(function(){
				$("[rate=1]").val(amount_paid);
			});
		});

		var old_rate;
		$(document).on("focus", '.rate', function () {
			old_rate = $(this).val();
		}).on("change", '.rate', function () {
			var row = $(this).closest('tr');
			if($(this).val() == ''){
				$(this).val(0);
			}else if (!is_numeric($(this).val())) {
				$(this).val(old_rate);
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
			$(".principal_paid").val(c_total);	
			$("#amount_1").val(c_total);	
		}); 	

		$(document).on('change', '#gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id != <?=$inv->customer_id?>) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');

                        } else {
                            var due = <?=$inv->grand_total-$inv->paid?>;
                            if (due > data.balance) {
                                $('#amount_1').val(formatDecimal(data.balance));
                            }
                            $('#gc_details').html('<small>Card No: <span style="max-width:60%;float:right;">' + data.card_no + '</span><br>Value: <span style="max-width:60%;float:right;">' + currencyFormat(data.value) + '</span><br>Balance: <span style="max-width:60%;float:right;">' + currencyFormat(data.balance) + '</span></small>');
                            $('#gift_card_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
        });
		
		$(document).on('change', '.paid_by', function () {
			var cash_type = $('option:selected', this).attr('cash_type');
            if(cash_type == 'bank'){
				$('.cbank').slideDown();
				$('.gc').slideUp();
				$('.ccheque').slideUp();
			}else if(cash_type == 'cheque'){
				$('.ccheque').slideDown();
				$('.gc').slideUp();
				$('.cbank').slideUp();
			}else if (cash_type == 'gift_card') {
                $('.gc').slideDown();
				$('.cbank').slideUp();
				$('.ccheque').slideUp();
                $('#gift_card_no').focus();
            } else {
                $('.gc').slideUp();
				$('.cbank').slideUp();
				$('.ccheque').slideUp();
            }
        });
		$(".paid_by").change();
        $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
    });
</script>
