<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        
		<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_down_payment').' ('.lang('sale').' '.lang('reference').': '.$inv->reference_no.')'; ?></h4>
		</div>
		
        <div class="modal-body">
            <div class="table-responsive">
                <table id="tSep" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
						<tr>
							<th width="10px;"><?= lang("#"); ?></th>
							<th width="100px;"><?= lang("payment_date"); ?></th>
							<th width="100px;"><?= lang("amount"); ?></th>
							<th width="100px;"><?= lang("period"); ?></th>
							<th width="100px;"><?= lang("term"); ?></th>
							<th width="80px;"><?= lang("date"); ?></th>
							<th width="80px;"><?= lang("user"); ?></th>
							<th width="80px;"><?= lang("status"); ?></th>
							<th width="80px;"><?= lang("actions"); ?></th>
						</tr>
                    </thead>
					<tbody>
						<?php 
							if($down_payments){
								foreach($down_payments as $key => $down_payment){
									$user = $this->site->getUser($down_payment->created_by);
									
									$inactive_link = '';
									if($down_payment->status != 'inactive'){
										$inactive_link = "<a href='#' class='po label label-danger' title='<b>" . lang("inactive") . "</b>' data-content=\"<p>"
											. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('sales/inactive_down_payment/').$down_payment->id. "'>"
											. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'>"
											. lang('inactive') . "</a>";
									}
									
									echo '<tr>
											<td style="text-align:center">'.($key+1).'</td>
											<td style="text-align:center">'.$this->cus->hrsd($down_payment->payment_date).'</td>
											<td style="text-align:right">'.$this->cus->formatMoney($down_payment->amount).'</td>
											<td style="text-align:center">'.$down_payment->period.'</td>
											<td style="text-align:center">'.$down_payment->term.'</td>
											<td style="text-align:center">'.$this->cus->hrsd($down_payment->created_at).'</td>
											<td style="text-align:center">'.$user->first_name.' '.$user->last_name.'</td>
											<td style="text-align:center">'.$this->cus->row_status($down_payment->status).'</td>
											<td style="text-align:center">
												<a target="_blank" class="label label-warning" href="'.site_url('sales/down_payment_details/'.$down_payment->id).'">'.lang("view").'</a>
												'.$inactive_link.'
											</td>
										</tr>';
								}
							}
						?>
					</tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
		$("#tSep").dataTable({
			"bPaginate": false
		});
    });
</script>
