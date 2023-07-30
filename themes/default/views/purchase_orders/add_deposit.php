<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= ($write_off && $write_off == "write_off" ? lang('write_off_deposit') : lang('add_deposit')) ; ?></h4>
        </div>
        <?php 
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo form_open_multipart("purchase_orders/add_deposit/" . $inv->id, $attrib);
		
		if($write_off && $write_off == "write_off"){
			$inv->grand_total = $po_deposit;
			$inv->paid = 0;
			$discount = 0;
		}else{
			$inv_return_paid = isset($inv->return_paid)? $inv->return_paid: 0;
			$inv_return_total = isset($inv->return_total)? $inv->return_total: 0;
			$inv->grand_total = ($inv->grand_total+$inv_return_paid) - $inv_return_total;
		}
        ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <?php if ($Owner || $Admin || $GP['purchase_orders-date']) { ?>
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
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment_ref), 'class="form-control tip" id="reference_no"'); ?>
                    </div>
                </div>
                <input type="hidden" value="<?= $inv->id ?>" name="purchase_order_id"/>
				<?php if($write_off && $write_off == "write_off"){ ?>
					<input type="hidden" value="write_off" name="write_off"/>
				<?php } ?>
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
                                        <input name="amount-paid" type="text" id="amount_1" readonly
                                               value="0"
                                               class="pa form-control kb-pad amount" required="required"/>
                                    </div>
                                </div>
                            </div>
							<?php 							
							foreach($currencies as $currency){ 
								$inv_paid = isset($inv->paid)? $inv->paid : 0;
								$inv_discount = isset($discount)? $discount : 0;
                                ?>
								<div class="col-sm-12">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> : <?= $this->cus->formatMoney(($currency->rate)*($inv->grand_total - $inv_paid - $inv_discount)) ?>
										(<?= strtoupper($currency->code); ?>)
										<a href="<?= site_url("system_settings/edit_currency/".$currency->id) ?>" data-toggle="modal" data-target="#myModal2">
											<small><?= $this->cus->formatDecimal($currency->rate) ?></small>
										</a>
										<input name="c_amount[]" value="0.00" rate="<?= $currency->rate ?>" type="text" class="form-control c_amount"/>
										<input name="currency[]" value="<?= $currency->code ?>" type="hidden" />
										<input name="rate[]" value="<?= $currency->rate ?>" type="hidden" />										
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
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_deposit', ($write_off && $write_off == "write_off" ? lang('write_off_deposit') : lang('add_deposit')) , 'class="btn btn-primary"'); ?>
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
		$(".c_amount").on("keyup",function(){
			var c_total = 0;
			$(".c_amount").each(function(){
				var amount = ($(this).val()-0);
				var rate = ($(this).attr("rate")-0);
				var camount = amount / rate;
					c_total += camount;
			});
			$("#amount_1").val(c_total);	
		});	
        $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
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
