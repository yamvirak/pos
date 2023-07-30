<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_multi_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
	
		$b = 1;
		$tbody = '';
		$now = time();
		$total_discount = 0 ;
		$total_amount = 0;
		$sid = '';
		foreach($inv as $row){
			$invoice_date = strtotime($row->date);
			$datediff = $now - $invoice_date;
			$payment_date = round($datediff / (60 * 60 * 24));
			$discount = 0;
			$total = ($row->grand_total-$row->total_return) - ($row->paid+$row->discount-$row->paid_return);
			if($total != 0){
				if($b==1){
					$sid .=$row->id;
				}else{
					$sid .='SaleID'.$row->id;
				}
				if($payment_date < $row->due_day_discount){							
					if($row->discount_type == "Percentage"){
						$discount = ($row->payment_discount * $total) / 100;
					}else{
						$discount = $row->payment_discount;
					}
				}
				$grand_total = $total - $discount;
				$total_discount += $discount;
				$total_amount += $total;
				$tbody .='<tr>	
							<td class="text-center">'.$b++.'</td>
							<td class="text-left">'.$row->reference_no.'</td>
							<td class="text-right">'.$this->cus->formatMoney($total).'</td>
							<td class="text-right">'.$this->cus->formatMoney($discount).'</td>
							<td class="text-right">'.$this->cus->formatMoney($grand_total).'</td>
						</tr>';
			}
			
		}

        echo form_open_multipart("sales/add_multi_payment/" . $sid, $attrib);
		?>
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
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment_ref), 'class="form-control tip" id="reference_no"'); ?>
                    </div>
                </div>
				<div class="col-sm-12">
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-striped print-table order-table">
							<thead>
								<tr>
									<th><?= lang('#') ?></th>
									<th><?= lang('reference_no') ?></th>
									<th><?= lang('total') ?></th>
									<th><?= lang('discount') ?></th>
									<th><?= lang('grand_total') ?></th>
								</tr>
							</thead>
							<tbody>
								<?= $tbody ?>
							</tbody>
						</table>
					</div>
				</div>
            </div>

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
                                        <input name="discount" value="<?= $total_discount; ?>" type="text" class="form-control" id="discount" readonly="true"/>
                                    </div>
                                </div>
                            </div>
							
							<?php 							
							foreach($currencies as $currency){ 
								$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
								$base_amount = $this->cus->formatDecimal($total_amount / $base_currency->rate);
								$amount = $base_amount * $currency->rate;
							?>
								<div class="col-sm-8">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> : <span id="am_<?= $currency->code ?>"><?= $this->cus->formatMoney($amount) ?> (<?= $currency->code ?>)</span>
										<input c_code="<?= $currency->code ?>" name="c_amount[]" value="0.00" rate="<?= $base_currency->rate ?>" type="text" class="form-control c_amount"/>
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
							
							<?php if($Settings->accounting == 1){ ?>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("paying_by", "paid_by_1"); ?>
                                        <select name="paid_by" id="paid_by_1" class="form-control paid_by" required="required">
                                            <?= $this->cus->cash_opts(); ?>
                                        </select>
                                    </div>
                                </div>

							<?php } ?>
							
                        </div>
						
                        <div class="clearfix"></div>
                        <div class="form-group gc" style="display: none;">
                            <?= lang("gift_card_no", "gift_card_no"); ?>
                            <input name="gift_card_no" type="text" id="gift_card_no" class="pa form-control kb-pad"/>
                            <div id="gc_details"></div>
                        </div>
                        <div class="pcc_1" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_no" type="text" id="pcc_no_1" class="form-control"
                                               placeholder="<?= lang('cc_no') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">

                                        <input name="pcc_holder" type="text" id="pcc_holder_1" class="form-control"
                                               placeholder="<?= lang('cc_holder') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type"
                                                placeholder="<?= lang('card_type') ?>">
                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                            <option value="MasterCard"><?= lang("MasterCard"); ?></option>
                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                            <option value="Discover"><?= lang("Discover"); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input name="pcc_month" type="text" id="pcc_month_1" class="form-control"
                                               placeholder="<?= lang('month') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input name="pcc_year" type="text" id="pcc_year_1" class="form-control"
                                               placeholder="<?= lang('year') ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pcheque_1" style="display:none;">
                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                <input name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no"/>
                            </div>
                        </div>
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
            <?php echo form_submit('add_multi_payment', lang('add_multi_payment'), 'class="btn btn-primary"'); ?>
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
			var total_amount = formatDecimal('<?= $base_amount ?>');
			var balance_amount = total_amount - c_total;
			$(".c_amount").each(function(){
				var code = $(this).attr("c_code");
				var rate =  $("#"+code).val() - 0;
				var amount_html = formatMoney(rate * balance_amount) + ' ('+ code +')';
				$("#am_"+code).html(amount_html);
			});
			$("#amount_1").val(c_total);	
		}); 
		
        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            $('#rpaidby').val(p_val);
			if(p_val == 'deposit'){
				$('.paying_to_box').slideUp();
			}else if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
                $('#amount_1').focus();
				$('.paying_to_box').slideDown();
            } else if (p_val == 'CC') {
                $('.pcheque_1').hide();
                $('.pcash_1').hide();
                $('.pcc_1').show();
                $('#pcc_no_1').focus();
				$('.paying_to_box').slideDown();
            } else if (p_val == 'Cheque') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').show();
                $('#cheque_no_1').focus();
				$('.paying_to_box').slideDown();
            } else {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').hide();
				$('.paying_to_box').slideDown();
            }
            if (p_val == 'gift_card') {
                $('.gc').show();
                $('#gift_card_no').focus();
            } else {
                $('.gc').hide();
            }
        });
        $('#pcc_no_1').change(function (e) {
            var pcc_no = $(this).val();
            localStorage.setItem('pcc_no_1', pcc_no);
            var CardType = null;
            var ccn1 = pcc_no.charAt(0);
            if (ccn1 == 4)
                CardType = 'Visa';
            else if (ccn1 == 5)
                CardType = 'MasterCard';
            else if (ccn1 == 3)
                CardType = 'Amex';
            else if (ccn1 == 6)
                CardType = 'Discover';
            else
                CardType = 'Visa';

            $('#pcc_type_1').select2("val", CardType);
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
    });
</script>
