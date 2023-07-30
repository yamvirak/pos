<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .table td:first-child {
        font-weight: bold;
    }

    label {
        margin-right: 10px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('clear'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<?php echo form_open("system_settings/clear/"); ?>
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th rowspan="2" class="text-center"><input class="checkbox checkft" type="checkbox" name="check"/></th>
								<th colspan="20" class="text-center"><?= lang("function_name"); ?></th>
							</tr>
						</thead>
						<tbody>
						
							<tr>
								<td><?= lang("settings"); ?></td>
								<td>
									<input type="checkbox" value="1" id="clear-list_billers" class="checkbox multi-select" name="clear-list_billers">
									<label for="clear-list_billers" class="padding05"><?= lang('list_billers') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_projects" class="checkbox multi-select" name="clear-list_projects">
									<label for="clear-list_projects" class="padding05"><?= lang('list_projects') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-warehouses" class="checkbox multi-select" name="clear-warehouses">
									<label for="clear-warehouses" class="padding05"><?= lang('warehouses') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-expense_categories" class="checkbox multi-select" name="clear-expense_categories">
									<label for="clear-expense_categories" class="padding05"><?= lang('expense_categories') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-categories" class="checkbox multi-select" name="clear-categories">
									<label for="clear-categories" class="padding05"><?= lang('categories') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-frequencies" class="checkbox multi-select" name="clear-frequencies">
									<label for="clear-frequencies" class="padding05"><?= lang('frequencies') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-units" class="checkbox multi-select" name="clear-units">
									<label for="clear-units" class="padding05"><?= lang('units') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-brands" class="checkbox multi-select" name="clear-brands">
									<label for="clear-brands" class="padding05"><?= lang('brands') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-boms" class="checkbox multi-select" name="clear-boms">
									<label for="clear-boms" class="padding05"><?= lang('boms') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-customer_group" class="checkbox multi-select" name="clear-customer_group">
									<label for="clear-customer_group" class="padding05"><?= lang('customer_group') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-price_group" class="checkbox multi-select" name="clear-price_group">
									<label for="clear-price_group" class="padding05"><?= lang('price_group') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-payment_terms" class="checkbox multi-select" name="clear-payment_terms">
									<label for="clear-payment_terms" class="padding05"><?= lang('payment_terms') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-currencies" class="checkbox multi-select" name="clear-currencies">
									<label for="clear-currencies" class="padding05"><?= lang('currencies') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-customer_opening_balances" class="checkbox multi-select" name="clear-customer_opening_balances">
									<label for="clear-customer_opening_balances" class="padding05"><?= lang('customer_opening_balances') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-supplier_opening_balances" class="checkbox multi-select" name="clear-supplier_opening_balances">
									<label for="clear-supplier_opening_balances" class="padding05"><?= lang('supplier_opening_balances') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-tax_rates" class="checkbox multi-select" name="clear-tax_rates">
									<label for="clear-tax_rates" class="padding05"><?= lang('tax_rates') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-printers" class="checkbox multi-select" name="clear-printers">
									<label for="clear-printers" class="padding05"><?= lang('printers') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-user_groups" class="checkbox multi-select" name="clear-user_groups">
									<label for="clear-user_groups" class="padding05"><?= lang('user_groups') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-calendar_lists" class="checkbox multi-select" name="clear-calendar_lists">
									<label for="clear-calendar_lists" class="padding05"><?= lang('calendar_lists') ?></label>
								</td>
							</tr>
						
							<tr>
								<td><?= lang("products"); ?></td>
								<td>
									<input type="checkbox" value="1" id="clear-list_products" class="checkbox multi-select" name="clear-list_products">
									<label for="clear-list_products" class="padding05"><?= lang('list_products') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-using_stocks" class="checkbox multi-select" name="clear-using_stocks">
									<label for="clear-using_stocks" class="padding05"><?= lang('using_stocks') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-stock_counts" class="checkbox multi-select" name="clear-stock_counts">
									<label for="clear-stock_counts" class="padding05"><?= lang('stock_counts') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-quantity_adjustments" class="checkbox multi-select" name="clear-quantity_adjustments">
									<label for="clear-quantity_adjustments" class="padding05"><?= lang('quantity_adjustments') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-cost_adjustments" class="checkbox multi-select" name="clear-cost_adjustments">
									<label for="clear-cost_adjustments" class="padding05"><?= lang('cost_adjustments') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-converts" class="checkbox multi-select" name="clear-converts">
									<label for="clear-converts" class="padding05"><?= lang('converts') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_transfers" class="checkbox multi-select" name="clear-list_transfers">
									<label for="clear-list_transfers" class="padding05"><?= lang('list_transfers') ?></label>
								</td>
							</tr>
							
							<tr>
								<td><?= lang("sales"); ?></td>
								<td>
									<input type="checkbox" value="1" id="clear-list_quotations" class="checkbox multi-select" name="clear-list_quotations">
									<label for="clear-list_quotations" class="padding05"><?= lang('list_quotations') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_sale_orders" class="checkbox multi-select" name="clear-list_sale_orders">
									<label for="clear-list_sale_orders" class="padding05"><?= lang('list_sale_orders') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_sales" class="checkbox multi-select" name="clear-list_sales">
									<label for="clear-list_sales" class="padding05"><?= lang('list_sales') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-pos" class="checkbox multi-select" name="clear-pos">
									<label for="clear-pos" class="padding05"><?= lang('pos') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-deliveries" class="checkbox multi-select" name="clear-deliveries">
									<label for="clear-deliveries" class="padding05"><?= lang('deliveries') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_returns" class="checkbox multi-select" name="clear-list_returns">
									<label for="clear-list_returns" class="padding05"><?= lang('list_returns') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_gift_cards" class="checkbox multi-select" name="clear-list_gift_cards">
									<label for="clear-list_gift_cards" class="padding05"><?= lang('list_gift_cards') ?></label>
								</td>
							</tr>
							
							<tr>
								<td><?= lang("purchases"); ?></td>
								<td>
									<input type="checkbox" value="1" id="clear-list_purchase_requests" class="checkbox multi-select" name="clear-list_purchase_requests">
									<label for="clear-list_purchase_requests" class="padding05"><?= lang('list_purchase_requests') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_purchase_orders" class="checkbox multi-select" name="clear-list_purchase_orders">
									<label for="clear-list_purchase_orders" class="padding05"><?= lang('list_purchase_orders') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_purchases" class="checkbox multi-select" name="clear-list_purchases">
									<label for="clear-list_purchases" class="padding05"><?= lang('list_purchases') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_receives" class="checkbox multi-select" name="clear-list_receives">
									<label for="clear-list_receives" class="padding05"><?= lang('list_receives') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-purchase_returns" class="checkbox multi-select" name="clear-purchase_returns">
									<label for="clear-purchase_returns" class="padding05"><?= lang('purchase_returns') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_expenses" class="checkbox multi-select" name="clear-list_expenses">
									<label for="clear-list_expenses" class="padding05"><?= lang('list_expenses') ?></label>
								</td>
							</tr>
							
							<tr>
								<td><?= lang("pawns"); ?></td>
								<td>
									<input type="checkbox" value="1" id="clear-list_pawns" class="checkbox multi-select" name="clear-list_pawns">
									<label for="clear-list_pawns" class="padding05"><?= lang('list_pawns') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_pawn_returns" class="checkbox multi-select" name="clear-list_pawn_returns">
									<label for="clear-list_pawn_returns" class="padding05"><?= lang('list_pawn_returns') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_pawn_purchases" class="checkbox multi-select" name="clear-list_pawn_purchases">
									<label for="clear-list_pawn_purchases" class="padding05"><?= lang('list_pawn_purchases') ?></label>
								</td>
							</tr>

							<tr>
								<td><?= lang("people"); ?></td>
								<td>
									<input type="checkbox" value="1" id="clear-list_customers" class="checkbox multi-select" name="clear-list_customers">
									<label for="clear-list_customers" class="padding05"><?= lang('list_customers') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_suppliers" class="checkbox multi-select" name="clear-list_suppliers">
									<label for="clear-list_suppliers" class="padding05"><?= lang('list_suppliers') ?></label>
								</td>
							</tr>
							
							<tr>
								<td><?= lang("accountings"); ?></td>
								<td>
									<input type="checkbox" value="1" id="clear-list_chart_accounts" class="checkbox multi-select" name="clear-list_chart_accounts">
									<label for="clear-list_chart_accounts" class="padding05"><?= lang('list_chart_accounts') ?></label>
								</td>
								<td>
									<input type="checkbox" value="1" id="clear-list_enter_journals" class="checkbox multi-select" name="clear-list_enter_journals">
									<label for="clear-list_enter_journals" class="padding05"><?= lang('list_enter_journals') ?></label>
								</td>
							</tr>
							
							
						</tbody>
					</table>
				</div>

				<div class="form-actions">
					<input type="hidden" value="1" name="clear"/>
					<button type="submit" class="btn btn-primary"><?=lang('clear')?></button>
				</div>
				<?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>