<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_agency_commission_payment'); ?></h4>
        </div>
        <?php 
        $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        $tbody = '';
        $total_commission_discount = 0 ;
        $total_commission_amount = 0;
        $sid = '';
        $k = 0;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : '';
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : '';
        foreach($sales as $row){
            $agencies = json_decode($row->agency_id);
            $agency_commission = json_decode($row->agency_commission);
            $agency_limit_percent = json_decode($row->agency_limit_percent);
            $agency_value_commission =  json_decode($row->agency_value_commission);
            $c = 0;
            if($agencies){
                foreach($agencies as $i => $agency){
                    $agency_details = $this->sales_model->getAgencyByID($agency);
                    if(in_array($agency_details->id,$agency_ids)){
                        $agency_name = $agency_details->first_name.' '.$agency_details->last_name;
                        $agency_rate = $agency_commission[$i]?$agency_commission[$i]:'';
                        $amount = $agency_value_commission[$i] == 1? $row->grand_total : $row->real_unit_price;
                        $commission_invoice = ($agency_rate * $amount) / 100;

                        // Last commission
                        $last_commission = 0;
                        $lpaid_percentage = ($row->last_paid * 100) / $amount;
                        if($lpaid_percentage > $agency_limit_percent[$i]){
                            $last_commission = $commission_invoice;
                        }else{
                            $last_commission = ($row->last_paid / (($agency_limit_percent[$i] * $amount)/100)) * $commission_invoice;
                        }
                                  
                        // Commission amount
                        $commission_amount = 0;
                        $paid_percentage = ($row->paid * 100) / $amount;
                        if($paid_percentage > $agency_limit_percent[$i]){
                            $commission_amount = $commission_invoice;
                        }else{
                            $commission_amount = ($row->paid / (($agency_limit_percent[$i] * $amount)/100)) * $commission_invoice;
                        }
                        
                        $total_commission = $commission_amount + $last_commission;
                        if($total_commission > $commission_invoice){
                            $commission_amount = $commission_invoice - $last_commission;
                        }

                        // Commission paid
                        $ppayments = $this->sales_model->getSaleAgencyPayments($row->id, $agency_details->id, true);
                        $commission_paid = 0;
                        if($ppayments){
                            foreach($ppayments as $ppayment){
                                $commission_paid += ($ppayment->amount + $ppayment->discount);
                            }
                        }
                        
                        $commission_balance = $this->cus->formatDecimal($commission_amount - $commission_paid);
                        if($commission_balance != 0){
                            if($i==1){
                                $sid .=$row->id;
                            }else{
                                $sid .=$row->id.'SaleID';
                            }
                            $tbody .= '<tr>
                                    <td style="text-align:center;">'.$this->cus->hrsd($row->date).'</td>
                                    <td>'.$row->product_name.'<input type="hidden" name="sale_id[]" value="'.$row->id.'" /></td>
                                    <td>'.$row->customer.'<input type="hidden" name="agency_id[]" value="'.$agency_details->id.'" /></td>
                                    <td>'.$agency_name.'<input type="hidden" name="index[]" value="'.$i.'" /></td>
                                    <td style="text-align:right;">'.$this->cus->formatMoney($commission_balance).'</td>
                                </tr>';
                            $total_commission_amount += $commission_balance;
                        }
                        $c++;
                    }
                }
                $tfoot ='<tr class="active">
                            <th colspan="4" style="text-align:right;">'.lang('total').' : </th>
                            <th style="text-align:right;">'.$this->cus->formatMoney($total_commission_amount).'</th>
                        </tr>';
            }
            $k+=$c;
        }
            echo form_open_multipart("sales/add_agency_commission_payment/". $sid, $attrib);
            echo form_hidden("start_date", $start_date);
            echo form_hidden("end_date",$end_date);
		?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
               <?php if ($Owner || $Admin || $GP['sales-agency_commission-date']) { ?>
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
									<th><?= lang('date') ?></th>
									<th><?= lang('product_name') ?></th>
                                    <th><?= lang('customer') ?></th>
									<th><?= lang('agency') ?></th>
									<th><?= lang('commission') ?></th>
								</tr>
							</thead>
							<tbody>
								<?= $tbody ?>
							</tbody>
                            <tfoot class="dtFilter">
                                <?= $tfoot ?>
                            </tfoot>
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
                                        <input name="amount-paid" readonly="readonly" type="text" id="amount_1" value="0" class="pa form-control kb-pad amount" required="required"/>
                                    </div>
                                </div>
                            </div>
							
                            <?php if($k==1){ ?>
                                <div class="col-sm-12">
                                    <div class="payment">
                                        <div class="form-group">
                                            <?= lang("discount", "discount"); ?>
                                            <input name="discount" value="<?= $total_commission_discount; ?>" type="text" class="form-control" id="discount"/>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
							<?php 							
							foreach($currencies as $currency){ 
								$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
								$base_amount = $this->cus->formatDecimal($total_commission_amount / $base_currency->rate);
								$amount = $base_amount * $currency->rate;
							?>
								<div class="col-sm-8">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> : <span id="am_<?= $currency->code ?>"><?= $this->cus->formatDecimal($amount) ?> (<?= $currency->code ?>)</span>
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
							
							<div class="col-sm-12">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
										<?= $this->cus->cash_opts(false,true,false,true); ?>
                                    </select>
                                </div>
                            </div>
							
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
