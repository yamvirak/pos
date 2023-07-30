<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="clearfix"></div>
<?= '</div></div></div></td></tr></table></div></div>'; ?>
<div class="clearfix"></div>
<footer class="hidden">
<a href="#" id="toTop" class="blue" style="position: fixed; bottom: 30px; right: 30px; font-size: 30px; display: none;">
    <i class="fa fa-chevron-circle-up"></i>
</a>

    <p style="text-align:center;">&copy; <?= date('Y') . " " . $Settings->site_name; ?> <?php if ($_SERVER["REMOTE_ADDR"] == '127.0.0.1') {
            //echo ' - Page rendered in <strong>{elapsed_time}</strong> seconds';
        } ?></p>
</footer>
<?= '</div>'; ?>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
<div id="modal-loading" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>
<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code); ?>
<?php
	$Settings->product_promotions = $this->config->item('product_promotions');
?>
<script type="text/javascript">
var user_id = <?= $this->session->userdata('user_id') ?>,dt_lang = <?=$dt_lang?>, dp_lang = <?=$dp_lang?>, site = <?=json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats))?>;
var lang = {spoiled: '<?=lang('spoiled');?>',paid: '<?=lang('paid');?>', expired: '<?=lang('expired');?>', assigned: '<?=lang('assigned');?>',cleared: '<?=lang('cleared');?>', approved: '<?=lang('approved');?>', rejected: '<?=lang('rejected');?>', pending: '<?=lang('pending');?>', completed: '<?=lang('completed');?>', ordered: '<?=lang('ordered');?>', received: '<?=lang('received');?>', partial: '<?=lang('partial');?>', sent: '<?=lang('sent');?>', r_u_sure: '<?=lang('r_u_sure');?>', due: '<?=lang('due');?>', returned: '<?=lang('returned');?>', transferring: '<?=lang('transferring');?>', active: '<?=lang('active');?>', inactive: '<?=lang('inactive');?>', unexpected_value: '<?=lang('unexpected_value');?>', select_above: '<?=lang('select_above');?>', download: '<?=lang('download');?>', active: '<?=lang('active');?>', inactive: '<?=lang('inactive');?>', payoff: '<?=lang('payoff');?>', pawn_rate: '<?=lang('pawn_rate');?>', pawn_received: '<?=lang('pawn_received');?>', pawn_sent: '<?=lang('pawn_sent');?>', closed: '<?=lang('closed');?>', yes: '<?=lang('yes');?>', no: '<?=lang('no');?>', morning: '<?=lang('morning');?>', afternoon: '<?=lang('afternoon');?>', full: '<?=lang('full');?>', freight: '<?=lang('freight');?>', packaging: '<?=lang('packaging');?>', take_away: '<?=lang('take_away');?>', fixed: '<?=lang('fixed');?>', difference: '<?=lang('difference');?>', checked_in: '<?=lang('checked_in');?>', checked_out: '<?=lang('checked_out');?>', expense : '<?= lang('expense') ?>', draft : '<?= lang('draft') ?>', enrolled : '<?= lang('enrolled') ?>', 
				returned_to_borrower : '<?= lang('returned_to_borrower') ?>',
				collateral_with_borrower : '<?= lang('collateral_with_borrower') ?>',
				deposited_into_branch : '<?= lang('deposited_into_branch') ?>',
				repossessed : '<?= lang('repossessed') ?>',
				sold : '<?= lang('sold') ?>',
				lost : '<?= lang('lost') ?>',
				disbursed : '<?= lang('disbursed') ?>',
				requested : '<?= lang('requested') ?>',
				declined : '<?= lang('declined') ?>',
				applied : '<?= lang('applied') ?>',
				cancelled :'<?= lang('cancelled') ?>',
				suspended :'<?= lang('suspended') ?>',
				repairing : '<?= lang('repairing') ?>',
				done : '<?= lang('done') ?>',
				reservation : '<?= lang('reservation')?>',
				room_blocking : '<?= lang('room_blocking')?>',
				booked : '<?= lang('booked')?>',
				not_done : '<?=lang('not_done')?>',
				dirty : '<?=lang('dirty')?>',
				repair : '<?=lang('repair')?>',
				cleaned : '<?=lang('cleaned')?>',
				free : '<?=lang('free')?>',
				maintenance : '<?=lang('maintenance')?>',
				room_charge : '<?=lang('room_charge')?>',
				room_late_checkout : '<?=lang('room_late_checkout')?>',
				house_use : '<?=lang('house_use')?>',
				complimentary : '<?=lang('complimentary')?>'
				
				
				
			};
</script>
<?php
$s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
foreach (lang('select2_lang') as $s2_key => $s2_line) {
    $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
}
$s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>
<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTable11.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/core.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>



		
<?= ($m == 'purchases' && ($v == 'add' || $v == 'edit' || $v == 'purchase_by_csv')) ? '<script type="text/javascript" src="' . $assets . 'js/purchases.js"></script>' : ''; ?>
<?= ($m == 'transfers' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/transfers.js"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add' || $v == 'edit' || $v == 'edit_pos')) ? '<script type="text/javascript" src="' . $assets . 'js/sales.js"></script>' : ''; ?>
<?= ($m == 'quotes' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/quotes.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_adjustment' || $v == 'edit_adjustment')) ? '<script type="text/javascript" src="' . $assets . 'js/adjustments.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_using_stock' || $v == 'edit_using_stock')) ? '<script type="text/javascript" src="' . $assets . 'js/using_stocks.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_scan_stock' || $v == 'edit_scan_stock')) ? '<script type="text/javascript" src="' . $assets . 'js/scan_stocks.js"></script>' : ''; ?>

<?= ($m == 'sale_orders' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/sale_orders.js"></script>' : ''; ?>
<?= ($m == 'deliveries' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/deliveries.js"></script>' : ''; ?>
<?= ($m == 'purchase_orders' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/purchase_orders.js"></script>' : ''; ?>
<?= ($m == 'purchase_requests' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/purchase_requests.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_convert' || $v == 'edit_convert')) ? '<script type="text/javascript" src="' . $assets . 'js/converts.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_convert' || $v == 'edit_convert')) ? '<script type="text/javascript" src="' . $assets . 'js/converts_to.js"></script>' : ''; ?>

<?= ($m == 'products' && ($v == 'add_bom' || $v == 'edit_bom')) ? '<script type="text/javascript" src="' . $assets . 'js/boms.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_bom' || $v == 'edit_bom')) ? '<script type="text/javascript" src="' . $assets . 'js/boms_to.js"></script>' : ''; ?>

<?= ($m == 'system_settings' && ($v == 'add_bom' || $v == 'edit_bom')) ? '<script type="text/javascript" src="' . $assets . 'js/boms.js"></script>' : ''; ?>

<?= ($m == 'converts' && ($v == 'add_bom' || $v == 'edit_bom')) ? '<script type="text/javascript" src="' . $assets . 'js/boms.js"></script>' : ''; ?>

<?= ($m == 'products' && ($v == 'add_cost_adjustment' || $v == 'edit_cost_adjustment')) ? '<script type="text/javascript" src="' . $assets . 'js/cost_adjustments.js"></script>' : ''; ?>
<?= ($m == 'accountings' && ($v == 'add_enter_journal' || $v == 'edit_enter_journal')) ? '<script type="text/javascript" src="' . $assets . 'js/enter_journal.js"></script>' : ''; ?>
<?= ($m == 'purchases' && ($v == 'add_receive' || $v == 'edit_receive')) ? '<script type="text/javascript" src="' . $assets . 'js/receives.js"></script>' : ''; ?>
<?= ($m == 'pawns' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/pawns.js"></script>' : ''; ?>
<?= ($m == 'attendances' && ($v == 'add_take_leave' || $v == 'edit_take_leave')) ? '<script type="text/javascript" src="' . $assets . 'js/take_leave.js"></script>' : ''; ?>
<?= ($m == 'attendances' && ($v == 'add_check_in_out' || $v == 'edit_check_in_out')) ? '<script type="text/javascript" src="' . $assets . 'js/check_in_out.js"></script>' : ''; ?>
<?= ($m == 'pos' && ($v == 'add_customer_stock' || $v == 'edit_customer_stock')) ? '<script type="text/javascript" src="' . $assets . 'js/customer_stocks.js"></script>' : ''; ?>
<?= ($m == 'expenses' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/expenses.js"></script>' : ''; ?>
<?= ($m == 'system_settings' && ($v == 'add_inventory_opening_balance' || $v == 'edit_inventory_opening_balance')) ? '<script type="text/javascript" src="' . $assets . 'js/inventory_opening_balances.js"></script>' : ''; ?>
<?= ($m == 'schools' && ($v == 'add_examination' || $v == 'edit_examination')) ? '<script type="text/javascript" src="' . $assets . 'js/examinations.js"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add_fuel_sale' || $v == 'edit_fuel_sale')) ? '<script type="text/javascript" src="' . $assets . 'js/fuel_sale.js"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add_fuel_customer' || $v == 'edit_fuel_customer')) ? '<script type="text/javascript" src="' . $assets . 'js/fuel_customer.js"></script>' : ''; ?>
<?= ($m == 'hr' && ($v == 'add_kpi' || $v == 'edit_kpi')) ? '<script type="text/javascript" src="' . $assets . 'js/kpi.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_consignment' || $v == 'edit_consignment')) ? '<script type="text/javascript" src="' . $assets . 'js/consignment.js"></script>' : ''; ?>
<?= ($m == 'repairs' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/repairs.js"></script>' : ''; ?>
<?= ($m == 'repairs' && ($v == 'add_check' || $v == 'edit_check')) ? '<script type="text/javascript" src="' . $assets . 'js/repair_checks.js"></script>' : ''; ?>
<?= ($m == 'rentals' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/rentals.js"></script>' : ''; ?>
<?= ($m == 'rentals_check_in' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/rentals_check_in.js"></script>' : ''; ?>

<?= ($m == 'concretes' && ($v == 'add_delivery' || $v == 'edit_delivery')) ? '<script type="text/javascript" src="' . $assets . 'js/con_delivery.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_fuel' || $v == 'edit_fuel')) ? '<script type="text/javascript" src="' . $assets . 'js/con_fuel.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_error' || $v == 'edit_error')) ? '<script type="text/javascript" src="' . $assets . 'js/con_error.js"></script>' : ''; ?>

<script type="text/javascript" charset="UTF-8">var oTable = '', r_u_sure = "<?=lang('r_u_sure')?>";
    <?=$s2_file_date?>
    $.extend(true, $.fn.dataTable.defaults, {"oLanguage":<?=$dt_lang?>});
    $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
    $(window).load(function () {
        $('.mm_<?=$m?>').addClass('active');
        $('.mm_<?=$m?>').find("ul").first().slideToggle();
        $('#<?=$m?>_<?=$v?>').addClass('active');
        $('.mm_<?=$m?> a .chevron').removeClass("closed").addClass("opened");
    });
	
	$(function(){
		 $('input[type=text]').attr('autocomplete','off');
	});
</script>
</body>
</html>
