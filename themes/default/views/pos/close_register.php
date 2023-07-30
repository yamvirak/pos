<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title"
                id="myModalLabel"><?= lang('close_register') . ' (' . $this->cus->hrld($register_open_time ? $register_open_time : $this->session->userdata('register_open_time')) . ' - ' . $this->cus->hrld(date('Y-m-d H:i:s')) . ')'; ?>
				 ( <?= $users->first_name.' '.$users->last_name ?> )
			</h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("pos/close_register/" . $user_id, $attrib);
        ?>
        <div class="modal-body">
            <div id="alerts"></div>
            <table width="100%" class="stable">
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_in_hand'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                        <span><?= $this->cus->formatMoney($this->session->userdata('cash_in_hand')); ?></span></h4>
                    </td>
                </tr>

				<tr>
                    <td width="300px;" style="font-weight:bold"><h4><?= lang('discount'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right">
						<h4>
                            <span><?= $this->cus->formatMoney($totaldiscount->total_discount ? $totaldiscount->total_discount : '0.00'); ?></span>
						</h4>
					</td>
                </tr>
				
				<tr>
                    <td width="300px;" style="font-weight:bold;"><h4><?= lang('sale'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;">
						<h4>
						<?php
							$totalsales->paid = $totalsales->paid + abs($refund_item->total);
							$totalsales->total = $totalsales->total + abs($refund_item->total);
						?>
                            <span><?= $this->cus->formatMoney($totalsales->total ? $totalsales->total : 0) ?></span>
						</h4>
					</td>
                </tr>
                
				<tr>
                    <td ><h4><?= lang('return'); ?>:</h4></td>
                    <td style="text-align:right;"><h4>
						<?php
							$refunds->returned = $refunds->returned + ($refund_item->total);
							$refunds->total = $refunds->total + ($refund_item->total);
						?>
                            <span><?= $this->cus->formatMoney($refunds->total ? abs($refunds->total) : 0)  ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('expense'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?php $expense = $expenses ? $expenses->total : 0; echo $this->cus->formatMoney($expense); ?></span>
                        </h4></td>
                </tr>
				
				<?php
					$payment_td = "";
					$total_cash_amount = ($total_cash->total_cash ? $total_cash->total_cash : 0);
					if($payments){
						foreach($payments as $payment){
							$payment_td .= '<tr><td><h4>'.lang("paid_by").' '.$payment->paid_by.':</h4></td>';
							$payment_td .= '<td style="text-align:right;"><h4>'.$this->cus->formatMoney($payment->paid).'</h4></td></tr>';
						}
					}
					echo $payment_td;
					$total_cash_amount = $total_cash_amount + $this->session->userdata('cash_in_hand') + ($refunds->returned ? $refunds->returned : 0) - ($expenses ? $expenses->total : 0);
				?>
				<tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><strong><?= lang('total_cash'); ?>:</strong></h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4><strong><span><?= $this->cus->formatMoney($total_cash_amount) ?></span></strong></h4></td>
                </tr>
				
				<?php if($count_money){ ?>
					<tr>
						<td style="font-weight:bold; border-top: 1px solid #DDD;"><h4><strong><?= lang('total_count_money_kh'); ?></strong>:</h4>
						</td>
						<td style="text-align:right; border-top: 1px solid #DDD;"><h4>
							<span><strong><?= $this->cus->formatMoney(($count_money ? $count_money->total_amount_kh : 0)); ?></strong></span>
						</h4></td>
					</tr>
					<tr>
						<td style="font-weight:bold;"><h4><strong><?= lang('total_count_money_us'); ?></strong>:</h4>
						</td>
						<td style="text-align:right;"><h4>
							<span><strong><?= $this->cus->formatMoney(($count_money ? $count_money->total_amount_us : 0)); ?></strong></span>
						</h4></td>
					</tr>
					<tr>
						<td style="border-bottom: 1px solid #DDD; font-weight:bold;"><h4><strong><?= lang('total_count_money'); ?></strong>:</h4>
						</td>
						<td style="border-bottom: 1px solid #DDD; text-align:right;"><h4>
							<span><strong><?= $this->cus->formatMoney(($count_money ? $count_money->total_amount : 0)); ?></strong></span>
						</h4></td>
					</tr>
				<?php } ?>	
            </table>

            <?php
			
			if($pos_settings->table_enable){
				if ($suspended_bills) {
					$total_sp = 0;
					echo '<h3>' . lang('opened_bills') . '</h3><table class="table table-hovered table-bordered"><thead><tr><th>' . lang('customer') . '</th><th>' . lang('date') . '</th><th>' . lang('total_items') . '</th><th>' . lang('table') . '</th><th>' . lang('note') . '</th><th>' . lang('amount') . '</th><th><i class="fa fa-trash-o"></i></th></tr></thead><tbody>';
					foreach ($suspended_bills as $bill) {
						$total_sp += $bill->total;
						echo '<tr><td>' . $bill->customer . '</td><td>' . $this->cus->hrld($bill->date) . '</td><td class="text-center">' . $bill->count . '</td><td class="text-center">'.$bill->table_name.'</td><td>' . $bill->suspend_note . '</td><td class="text-right">' . $bill->total . '</td><td class="text-center"><a href="#" class="tip po" title="<b>' . $this->lang->line("delete_bill") . '</b>" data-content="<p>' . lang('r_u_sure') . '</p><a class=\'btn btn-danger po-delete\' href=\'' . site_url('pos/delete/' . $bill->id) . '\'>' . lang('i_m_sure') . '</a> <button class=\'btn po-close\'>' . lang('no') . '</button>"  rel="popover"><i class="fa fa-trash-o"></i></a></td></tr>';
					}
					echo '<tr><td colspan="5"></td><td class="text-right bold">'.$this->cus->formatDecimal($total_sp).'</td><td></td></tr></tbody></table>';
				}
			}else{
				if ($suspended_bills) {
					$total_sp = 0;
					echo '<h3>' . lang('opened_bills') . '</h3><table class="table table-hovered table-bordered"><thead><tr><th>' . lang('customer') . '</th><th>' . lang('date') . '</th><th>' . lang('total_items') . '</th><th>' . lang('note') . '</th><th>' . lang('amount') . '</th><th><i class="fa fa-trash-o"></i></th></tr></thead><tbody>';
					foreach ($suspended_bills as $bill) {
						$total_sp += $bill->total;
						echo '<tr><td>' . $bill->customer . '</td><td>' . $this->cus->hrld($bill->date) . '</td><td class="text-center">' . $bill->count . '</td><td>' . $bill->suspend_note . '</td><td class="text-right">' . $bill->total . '</td><td class="text-center"><a href="#" class="tip po" title="<b>' . $this->lang->line("delete_bill") . '</b>" data-content="<p>' . lang('r_u_sure') . '</p><a class=\'btn btn-danger po-delete\' href=\'' . site_url('pos/delete/' . $bill->id) . '\'>' . lang('i_m_sure') . '</a> <button class=\'btn po-close\'>' . lang('no') . '</button>"  rel="popover"><i class="fa fa-trash-o"></i></a></td></tr>';
					}
					echo '<tr><td colspan="4"></td><td class="text-right bold">'.$this->cus->formatDecimal($total_sp).'</td><td></td></tr></tbody></table>';
				}
			}

            ?>
            <div class="clearfix" style="height:10px"></div>
            <div class="row no-print">
				<div class="col-sm-6">
					<div class="form-group">
						<?= lang("total_cash_submit", "total_cash_submitted"); ?>
						<?= form_hidden('total_cash', $total_cash_amount); ?>
						<?= form_input('total_cash_submitted', (isset($_POST['total_cash_submitted']) ? $_POST['total_cash_submitted'] : $total_cash_amount), 'class="form-control input-tip" id="total_cash_submitted" required="required"'); ?>
					</div>
				</div>
				<div class="col-sm-6">
					<?php if ($suspended_bills) { ?>
                        <div class="form-group">
                            <?= lang("transfer_opened_bills", "transfer_opened_bills"); ?>
                            <?php $u = $user_id ? $user_id : $this->session->userdata('user_id');
                            if ($Owner || $Admin) { 
                                $usrs[-1] = lang('delete_all');
                            }
                            $usrs[0] = lang('leave_opened');
							if($open_users){
								foreach ($open_users as $open_user) {
									if ($open_user->user_id != $u) {
										$usrs[$open_user->user_id] = $open_user->user;
									}
								}
							}
                            
                            ?>
                            <?= form_dropdown('transfer_opened_bills', $usrs, (isset($_POST['transfer_opened_bills']) ? $_POST['transfer_opened_bills'] : 0), 'class="form-control input-tip" id="transfer_opened_bills" required="required"'); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="form-group no-print">
                <label for="note"><?= lang("note"); ?></label>

                <div
                    class="controls"> <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?> </div>
            </div>

        </div>
        <div class="modal-footer no-print">
            <?= form_submit('close_register', lang('close_register'), 'class="btn cl-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>

</div>
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.po', function (e) {
            e.preventDefault();
            $('.po').popover({
                html: true,
                placement: 'left',
                trigger: 'manual'
            }).popover('show').not(this).popover('hide');
            return false;
        });
        $(document).on('click', '.po-close', function () {
            $('.po').popover('hide');
            return false;
        });
        $(document).on('click', '.po-delete', function (e) {
            var row = $(this).closest('tr');
            e.preventDefault();
            $('.po').popover('hide');
            var link = $(this).attr('href');
            $.ajax({
                type: "get", url: link,
                success: function (data) {
                    row.remove();
                    addAlert(data, 'success');
                },
                error: function (data) {
                    addAlert('Failed', 'danger');
                }
            });
            return false;
        });
    });
    function addAlert(message, type) {
        $('#alerts').empty().append(
            '<div class="alert alert-' + type + '">' +
            '<button type="button" class="close" data-dismiss="alert">' +
            '&times;</button>' + message + '</div>');
    }
</script>


