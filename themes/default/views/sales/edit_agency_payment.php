<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_agency_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("sales/edit_agency_payment/" . $payment->id, $attrib); ?>
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
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment->reference_no), 'class="form-control tip" id="reference_no" required="required"'); ?>
                    </div>
                </div>
                <input type="hidden" value="<?php echo $payment->transaction_id; ?>" name="sale_id"/>
            </div>
			
            <div class="clearfix"></div>
			
            <div id="payments">
                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?php 
											echo lang("amount", "amount_1"); 
										?>
                                        <input name="amount-paid" readonly
                                               value="<?= $this->cus->formatDecimal($payment->amount); ?>" type="text"
                                               id="amount_1" class="pa form-control kb-pad amount"/>
                                    </div>
                                </div>
                            </div>
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
										<?= $this->cus->cash_opts($payment->paid_by,true,false,true); ?>
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
        $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
    });
</script>
