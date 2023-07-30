<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_biller'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("billers/add", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("logo", "biller_logo"); ?>
                        <?php
                        $biller_logos[''] = '';
                        foreach ($logos as $key => $value) {
                            $biller_logos[$value] = $value;
                        }
                        echo form_dropdown('logo', $biller_logos, '', 'class="form-control select" id="biller_logo" required="required" '); ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div id="logo-con" class="text-center"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group person">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone"/>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', '', 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("city", "city"); ?>
                        <?php echo form_input('city', '', 'class="form-control" id="city" required="required"'); ?>
                    </div>
					<div class="form-group">
                        <?= lang("country", "country"); ?>
                        <?php echo form_input('country', '', 'class="form-control" id="country"'); ?>
                    </div>
					<div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                    </div>
					<div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" required="required" id="email_address"/>
                    </div>

                </div>
                <div class="col-md-6">
					<div class="form-group company">
                        <?= lang("name_kh", "company"); ?>
                        <?php echo form_input('company', '', 'class="form-control tip" id="company" data-bv-notempty="true"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("phone_kh", "cf1"); ?>
                        <?php echo form_input('cf1', '', 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("address_kh", "cf2"); ?>
                        <?php echo form_input('cf2', '', 'class="form-control" id="cf2"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("city_kh", "cf3"); ?>
                        <?php echo form_input('cf3', '', 'class="form-control" id="cf3"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("country_kh", "cf4"); ?>
                        <?php echo form_input('cf4', '', 'class="form-control" id="cf4"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("vat_no_kh", "cf5"); ?>
                        <?php echo form_input('cf5', '', 'class="form-control" id="cf5"'); ?>

                    </div>
					<div class="form-group">
                        <?= lang("prefix", "prefix"); ?>
                        <?php echo form_input('prefix', '', 'class="form-control" id="prefix"'); ?>
                    </div>
                </div>
				<div class="col-md-6">
					<div class="form-group">
						<?= lang('default_cash', 'default_cash'); ?>
						<select name="default_cash" id="default_cash" class="form-control default_cash" required="required">
							<option value=""><?= lang("select")." ".lang("default_cash") ?></option>
							<?= $this->cus->cash_opts(false,true,false,true); ?>
						</select>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
                        <?= lang("postal_code", "postal_code"); ?>
                        <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code"'); ?>
                    </div>
				</div>
				<?php if($this->config->item('concretes')){ ?>
					<div class="col-md-6">
						<div class="form-group">
							<?= lang("start_hour", "start_hour"); ?>
							<?php echo form_input('start_hour', '', 'class="form-control timepicker" id="start_hour"'); ?>
						</div>
						<div class="form-group">
							<?= lang("office_commission_rate", "office_commission_rate"); ?>
							<?php echo form_input('office_commission_rate', 0, 'class="form-control" id="office_commission_rate"'); ?>
						</div>
						<div class="form-group">
							<?= lang("pump_commission_rate", "pump_commission_rate"); ?>
							<?php echo form_input('pump_commission_rate', 0, 'class="form-control" id="pump_commission_rate"'); ?>
						</div>
						<div class="form-group">
							<?= lang("truck_commission_rate", "truck_commission_rate"); ?>
							<?php echo form_input('truck_commission_rate', 0, 'class="form-control" id="truck_commission_rate"'); ?>
						</div>
						<div class="form-group">
							<?= lang("big_truck_commission_rate", "big_truck_commission_rate"); ?>
							<?php echo form_input('big_truck_commission_rate', 0, 'class="form-control" id="big_truck_commission_rate"'); ?>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<?= lang("end_hour", "end_hour"); ?>
							<?php echo form_input('end_hour', '', 'class="form-control timepicker" id="end_hour"'); ?>
						</div>
						<div class="form-group">
							<?= lang("fuel_price", "fuel_price"); ?>
							<?php echo form_input('fuel_price', 0, 'class="form-control" id="fuel_price"'); ?>
						</div>
						<div class="form-group">
							<?= lang("pump_commission_rate_assistant", "pump_commission_rate_assistant"); ?>
							<?php echo form_input('pump_commission_rate_assistant', 0, 'class="form-control" id="pump_commission_rate_assistant"'); ?>
						</div>
						<div class="form-group">
							<?= lang("truck_commission_rate_ot", "truck_commission_rate_ot"); ?>
							<?php echo form_input('truck_commission_rate_ot', 0, 'class="form-control" id="truck_commission_rate_ot"'); ?>
						</div>
						<div class="form-group">
							<?= lang("big_truck_commission_rate_ot", "big_truck_commission_rate_ot"); ?>
							<?php echo form_input('big_truck_commission_rate_ot', 0, 'class="form-control" id="big_truck_commission_rate_ot"'); ?>
						</div>
					</div>
				<?php } if($Settings->accounting == 1){ ?>
				<div class="col-md-6">
                    <div class="form-group">
                        <?= lang("receivable_account", "receivable_account"); ?>
                        <select name="receivable_account" class="form-control select" id="receivable_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('receivable_account') ?></option>
							<?= $receivable_account ?>
						</select>
                    </div>
                    <div class="form-group">
                        <?= lang("payable_account", "payable_account"); ?>
                        <select name="payable_account" class="form-control select" id="payable_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('payable_account') ?></option>
							<?= $payable_account ?>
						</select>
                    </div>
                    <div class="form-group">
                        <?= lang("purchase_discount_account", "purchase_discount_account"); ?>
                        <select name="purchase_discount_account" class="form-control select" id="purchase_discount_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('purchase_discount_account') ?></option>
							<?= $purchase_discount_account ?>
						</select>
                    </div>
                    <div class="form-group">
                        <?= lang("sale_discount_account", "sale_discount_account"); ?>
                        <select name="sale_discount_account" class="form-control select" id="sale_discount_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('sale_discount_account') ?></option>
							<?= $sale_discount_account ?>
						</select>

                    </div>
                    <div class="form-group">
                        <?= lang("purchase_return_surcharge_account", "purchase_return_surcharge_account"); ?>
                        <select name="purchase_return_account" class="form-control select" id="purchase_return_surcharge_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('purchase_return_surcharge_account') ?></option>
							<?= $purchase_return_account ?>
						</select>
                    </div>
					<div class="form-group">
                        <?= lang("sale_return_surcharge_account", "sale_return_surcharge_account"); ?>
                        <select name="sale_return_account" class="form-control select" id="sale_return_surcharge_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('sale_return_surcharge_account') ?></option>
							<?= $sale_return_account ?>
						</select>
                    </div>
					<div class="form-group">
                        <?= lang("shipping_account", "shipping_account"); ?>
                        <select name="shipping_account" class="form-control select" id="shipping_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('shipping_account') ?></option>
							<?= $shipping_account ?>
						</select>
                    </div>
					<?php if($this->config->item('saleman_commission') || $this->config->item('concretes')){ ?>
						<div class="form-group">
							<?= lang("saleman_commission_account", "saleman_commission_account"); ?>
							<select name="saleman_commission_account" class="form-control select" id="saleman_commission_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('saleman_commission_account') ?></option>
								<?= $saleman_commission_account ?>
							</select>
						</div>
					
					<?php } if($this->config->item('agency')){ ?>
						<div class="form-group">
							<?= lang("agency_commission_account", "agency_commission_account"); ?>
							<select name="agency_commission_account" class="form-control select" id="agency_commission_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('agency_commission_account') ?></option>
								<?= $agency_commission_account ?>
							</select>
						</div>
					<?php } if($this->config->item('pawn')){ ?>
						<div class="form-group">
							<?= lang("pawn_stock_account", "pawn_stock_account"); ?>
							<select name="pawn_stock_account" class="form-control select" id="pawn_stock_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('pawn_stock_account') ?></option>
								<?= $pawn_stock_account ?>
							</select>
						</div>
					<?php } if($this->config->item('ktv')){ ?>
						<div class="form-group">
							<?= lang("customer_stock_account", "customer_stock_account"); ?>
							<select name="customer_stock_account" class="form-control select" id="customer_stock_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('customer_stock_account') ?></option>
								<?= $customer_stock_account ?>
							</select>
						</div>
					<?php } if($this->config->item('consignments')){ ?>	
						<div class="form-group">
							<?= lang("consignment_account", "consignment_account"); ?>
							<select name="consignment_account" class="form-control select" id="consignment_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('consignment_account') ?></option>
								<?= $consignment_account ?>
							</select>
						</div>
					<?php } ?>
					<div class="form-group">
                        <?= lang("other_income_account", "other_income_account"); ?>
                        <select name="other_income_account" class="form-control select" id="other_income_account" style="width:100%">
							<option value=""><?= lang('other_income_account') ?></option>
							<?= $other_income_account ?>
						</select>
                    </div>
					<div class="form-group">
                        <?= lang("opening_balance_account", "opening_balance_account"); ?>
                        <select name="opening_balance_account" class="form-control select" id="opening_balance_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('opening_balance_account') ?></option>
							<?= $opening_balance_account ?>
						</select>
                    </div>
                </div>
				
				<div class="col-md-6">
					<div class="form-group">
                        <?= lang("prepaid_account", "prepaid_account"); ?>
                        <select name="prepaid_account" class="form-control select" id="prepaid_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('prepaid_account') ?></option>
							<?= $prepaid_account ?>
						</select>
                    </div>
					<div class="form-group">
                        <?= lang("supplier_deposit_account", "supplier_deposit_account"); ?>
                        <select name="supplier_deposit_account" class="form-control select" id="supplier_deposit_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('supplier_deposit_account') ?></option>
							<?= $supplier_deposit_account ?>
						</select>

                    </div>
					<div class="form-group">
                        <?= lang("customer_deposit_account", "customer_deposit_account"); ?>
                        <select name="customer_deposit_account" class="form-control select" id="customer_deposit_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('customer_deposit_account') ?></option>
							<?= $customer_deposit_account ?>
						</select>

                    </div>
					<?php if($this->config->item('payroll')){ ?>
						<div class="form-group">
							<?= lang("cash_advance_account", "cash_advance_account"); ?>
							<select name="cash_advance_account" class="form-control select" id="cash_advance_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('cash_advance_account') ?></option>
								<?= $cash_advance_account ?>
							</select>
						</div>
						<div class="form-group">
							<?= lang("overtime_account", "overtime_account"); ?>
							<select name="overtime_account" class="form-control select" id="overtime_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('overtime_account') ?></option>
								<?= $overtime_account ?>
							</select>
						</div>
						<div class="form-group">
							<?= lang("salary_expense_account", "salary_expense_account"); ?>
							<select name="salary_expense_account" class="form-control select" id="salary_expense_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('salary_expense_account') ?></option>
								<?= $salary_expense_account ?>
							</select>
						</div>
						
						<div class="form-group">
							<?= lang("salary_13_account", "salary_13_account"); ?>
							<select name="salary_13_account" class="form-control select" id="salary_13_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('salary_13_account') ?></option>
								<?= $salary_13_account ?>
							</select>
						</div>
						
						<div class="form-group">
							<?= lang("compensate_account", "compensate_account"); ?>
							<select name="compensate_account" class="form-control select" id="compensate_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('compensate_account') ?></option>
								<?= $compensate_account ?>
							</select>
						</div>
						
						<div class="form-group">
							<?= lang("salary_payable_account", "salary_payable_account"); ?>
							<select name="salary_payable_account" class="form-control select" id="salary_payable_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('salary_payable_account') ?></option>
								<?= $salary_payable_account ?>
							</select>
						</div>
					<?php } ?>
					<div class="form-group">
                        <?= lang("vat_input_account", "vat_input_account"); ?>
                        <select name="vat_input_account" class="form-control select" id="vat_input_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('vat_input_account') ?></option>
							<?= $vat_input_account ?>
						</select>

                    </div>
					<div class="form-group">
                        <?= lang("vat_output_account", "vat_output_account"); ?>
                        <select name="vat_output_account" class="form-control select" id="vat_output_account" style="width:100%">
							<option value=""><?= lang('select').' '.lang('vat_output_account') ?></option>
							<?= $vat_output_account ?>
						</select>

                    </div>


					<?php if($Settings->installment==1){ ?>
						<div class="form-group">
							<?= lang("installment_outstanding_account", "installment_outstanding_account"); ?>
							<select name="installment_outstanding_account" class="form-control select" id="installment_outstanding_account" style="width:100%">
								<option value=""><?= lang('installment_outstanding_account') ?></option>
								<?= $installment_outstanding_account ?>
							</select>
						</div>
						<div class="form-group">
							<?= lang("installment_interest_account", "installment_interest_account"); ?>
							<select name="installment_interest_account" class="form-control select" id="installment_interest_account" style="width:100%">
								<option value=""><?= lang('installment_interest_account') ?></option>
								<?= $installment_interest_account ?>
							</select>
						</div>

					<?php } ?>
					<?php if($this->config->item('concretes')){ ?>
						<div class="form-group">
							<?= lang("fuel_expense_account", "fuel_expense_account"); ?>
							<select name="fuel_expense_account" class="form-control select" id="fuel_expense_account" style="width:100%">
								<option value=""><?= lang('select').' '.lang('fuel_expense_account') ?></option>
								<?= $fuel_expense_account ?>
							</select>
						</div>
					<?php } ?>
                </div>
				<?php } ?>
				
				
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang("invoice_footer", "invoice_footer"); ?>
                        <?php echo form_textarea('invoice_footer', '', 'class="form-control skip" id="invoice_footer" style="height:100px;"'); ?>
                    </div>
                </div>

            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_biller', lang('add_biller'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        $('#biller_logo').change(function (event) {
            var biller_logo = $(this).val();
            $('#logo-con').html('<img width="300px" height="80px" src="<?=base_url('assets/uploads/logos')?>/' + biller_logo + '" alt="">');
        });
    });
</script>
<?= $modal_js ?>
