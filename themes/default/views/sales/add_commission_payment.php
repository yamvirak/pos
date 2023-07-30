<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_commission_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
	
		$b = 1;
		$tbody = '';
		$total_amount = 0;
		$sid = '';
		foreach($inv as $row){
			if($b==1){
				$sid .=$row->id;
			}else{
				$sid .='SaleID'.$row->id;
			}

			$commission_amount = 0;
			$balance = 0;
			$dpos = strpos($row->saleman_commission, '%');
			if ($dpos !== false) {
				$pds = explode("%", $row->saleman_commission);
				$commission_amount = $row->grand_total * $pds[0];
				if($commission_amount > 0){
					$commission_amount = $commission_amount / 100;
				}
			} else {
				$commission_amount = $row->saleman_commission;
			}
			$balance = $commission_amount - $row->paid_commission;
			if($balance > 0){
				$total_amount += $balance;
				$tbody .='<tr>	
							<td class="text-center">'.$b++.'</td>
							<td class="text-left">'.$row->reference_no.'</td>
							<td class="text-left">'.$row->group_name.'</td>
							<td class="text-left">'.$row->saleman.'</td>
							<td class="text-right">'.$this->cus->formatMoney($row->grand_total).'</td>
							<td class="text-center">'.$row->saleman_commission.'</td>
							<td class="text-right">'.$this->cus->formatMoney($commission_amount).'</td>
							<td class="text-right">'.$this->cus->formatMoney($row->paid_commission).'</td>
							<td class="text-right">'.$this->cus->formatMoney($balance).'</td>
						</tr>';
			}
			
		
		}

        echo form_open_multipart("sales/add_commission_payment/" . $sid, $attrib);
		?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
               <?php if ($Owner || $Admin || $GP['sales-saleman_commission-date']) { ?>
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
				<div class="col-sm-12 hidden">
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-striped print-table order-table">
							<thead>
								<tr>
									<th><?= lang('#') ?></th>
									<th><?= lang('reference_no') ?></th>
									<th><?= lang('group') ?></th>
									<th><?= lang('saleman') ?></th>
									<th><?= lang('grand_total') ?></th>
									<th><?= lang('commission_rate') ?></th>
									<th><?= lang('commission_amount') ?></th>
									<th><?= lang('paid_amount') ?></th>
									<th><?= lang('balance') ?></th>
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
							<?php 							
							foreach($currencies as $currency){ 
								$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
								$base_amount = $total_amount / $base_currency->rate;
								$amount = $base_amount * $currency->rate;
							?>
								<div class="col-sm-12">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> : <?= $this->cus->formatMoney($amount) ?>
										(<?= strtoupper($currency->code); ?>)
										<a href="<?= site_url("system_settings/edit_currency/".$currency->id) ?>" data-toggle="modal" data-target="#myModal2">
											<small><?= $this->cus->formatDecimal($currency->rate) ?></small>
										</a>
										<input name="c_amount[]" value="0.00" rate="<?= $base_currency->rate ?>" type="text" class="form-control c_amount"/>
										<input name="currency[]" value="<?= $currency->code ?>" type="hidden" />
										<input name="rate[]" value="<?= $currency->rate ?>" type="hidden" class="rate" />										
									</div>                                
								</div>
							<?php } ?>
							
							<div class="col-sm-12">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by" required="required">
                                        <?= $this->cus->cash_opts(); ?>
                                    </select>
                                </div>
                            </div>
							
                        </div>
						
                        <div class="clearfix"></div>
                        <div class="row cbank" style="display: none;">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("account_number", "account_number"); ?>
                                    <input name="account_number" value="<?= ($bank_info ? $bank_info->account_number : '') ?>" type="text" id="account_number" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("account_name", "account_name"); ?>
                                    <input name="account_name" value="<?= ($bank_info ? $bank_info->account_name : '') ?>" type="text" id="account_name" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="row ccheque" style="display: none;">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <?= lang("bank_name", "bank_name"); ?>
                                    <input name="bank_name" type="text" id="bank_name" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("cheque_number", "cheque_number"); ?>
                                    <input name="cheque_number" type="text" id="cheque_number" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("cheque_date", "cheque_date"); ?>
                                    <input name="cheque_date" value="<?= $this->cus->hrsd(date("Y-m-d")) ?>" type="text" id="cheque_date" class="form-control date"/>
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
            <?php echo form_submit('add_payment', lang('add_payment'), 'class="btn btn-primary add_payment"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
		
		var grand_total = formatDecimal(<?= $total_amount ?>);
		if(grand_total== 0){
			$('.add_payment').attr('disabled', true);
		}
		
		
		$(".c_amount").on("change",function(){
			var c_total = 0;
			$(".c_amount").each(function(){
				var base_rate = formatDecimal($(this).attr("rate"),11);
				var rate = formatDecimal($(this).parent().find(".rate").val(),11);
				var amount = formatDecimal($(this).val(),11);
				var base_amount = amount / rate;
				var camount = base_amount * base_rate;
					c_total += camount;
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
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>
