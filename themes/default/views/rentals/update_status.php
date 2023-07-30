<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_status'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals/update_status/" . $rental->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= lang('rental_details'); ?>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-hover table-striped table-condensed reports-table dataTable" style="margin-bottom:0;">
                        <tbody>
                            <tr>
                                <td><?= lang('reference_no'); ?></td>
                                <td><?= $rental->reference_no; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('biller'); ?></td>
                                <td><?= $rental->biller; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('customer'); ?></td>
                                <td><?= $rental->customer; ?></td>
                            </tr>
							<tr>
                                <td><?= lang('phone'); ?></td>
                                <td><?= $customer->phone; ?></td>
                            </tr>
							<tr>
                                <td><?= lang('checked_in_date'); ?></td>
                                <td><?= $this->cus->hrsd($rental->checked_in); ?></td>
                            </tr>
							<tr>
                                <td><?= lang('from_date'); ?></td>
                                <td><?= $this->cus->hrsd($rental->from_date); ?></td>
                            </tr>
							<tr>
                                <td><?= lang('to_date'); ?></td>
                                <td><?= $this->cus->hrsd($rental->to_date); ?></td>
                            </tr>
                            <tr style="font-weight:bold;">
                                <td><?= lang('deposit'); ?></td>
                                <td><?= $this->cus->formatMoney($rental_deposit->amount); ?></td>
                            </tr>
                            <tr style="font-weight:bold;">
                                <td><?= lang('status'); ?></td>
                                <td><strong><?= lang($rental->status); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
			<div class="form-group">
                <?= lang('status', 'status'); ?>
                <?php
					$opts = array('reservation' => lang('reservation'), 'cancelled' => lang('cancelled'),'checked_in' => lang('checked_in'), 'checked_out' => lang('checked_out'));
                ?>
                <?= form_dropdown('status', $opts, $rental->status, 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
            </div>

            <div class="no-cancelled_reservation">
				<div class="form-group">
					<?= lang("date", "date"); ?>
					<?php 
						$cancelled_reservation = $this->cus->hrsd(date("Y-m-d"));
						if(!empty($rental->cancelled_reservation) && $rental->cancelled_reservation != '0000-00-00'){
							$cancelled_reservation = $this->cus->hrsd($rental->cancelled_reservation);
						} 
					?>
					<?php echo form_input('cckodate', (isset($_POST['cckodate']) ? $_POST['cckodate'] : $cancelled_reservation), 'class="form-control date bold"'); ?>
				</div>
			</div>
			
			<div class="no-checked_in">
				<div class="form-group">
					<?= lang("date", "date"); ?>
					<?php 
						$checked_in = $this->cus->hrsd(date("Y-m-d"));
						if(!empty($rental->checked_in) && $rental->checked_in != '0000-00-00'){
							$checked_in = $this->cus->hrsd($rental->checked_in);
						}
						if(!$Admin && !$Owner){ 	
							echo form_input('ckidate', (isset($_POST['ckidate']) ? $_POST['ckidate'] : $checked_in), 'class="form-control date bold" readonly');
						}else{
							echo form_input('ckidate', (isset($_POST['ckidate']) ? $_POST['ckidate'] : $checked_in), 'class="form-control date bold"');
						}
					?>
				</div>
			</div>
			
			<div class="no-checked_out">
				<div class="form-group">
					<?= lang("date", "date"); ?>
					<?php 
						$checked_out = $this->cus->hrsd(date("Y-m-d"));
						if(!empty($rental->checked_out) && $rental->checked_out != '0000-00-00'){
							$checked_out = $this->cus->hrsd($rental->checked_out);
						} 
					?>
					<?php echo form_input('ckodate', (isset($_POST['ckodate']) ? $_POST['ckodate'] : $checked_out), 'class="form-control date bold"'); ?>
				</div>
			</div>

			<div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $this->cus->decode_html($rental->note)), 'class="form-control" id="note"'); ?>
            </div>
			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>

<script type="text/javascript">
	$(function(){
		<?php if($rental->status != 'checked_in'){ ?>
			$(".no-checked_in").hide();
			$(".no-cancelled_reservation").hide();
		<?php } ?>
		<?php if($rental->status != 'checked_out'){ ?>
			$(".no-checked_out").hide();
			$(".no-cancelled_reservation").hide();
		<?php } ?>
		$("#status").on("change",function(){
			var status = $("#status").val();
			if(status == 'checked_in'){
				$(".no-checked_in").slideDown();
				$(".no-checked_out").slideUp();
				$(".no-cancelled_reservation").slideUp();
			}else if(status == 'checked_out'){
				$(".no-checked_out").slideDown();
				$(".no-checked_in").slideUp();
				$(".no-cancelled_reservation").slideUp();
			}else if(status == 'cancelled_reservation'){
				$(".no-cancelled_reservation").slideDown();
				$(".no-checked_out").slideUp();
				$(".no-checked_in").slideUp();
			}
			else{
				$(".no-checked_in").slideUp();
				$(".no-checked_out").slideUp();
				$(".no-cancelled_reservation").slideUp();
			}
		});
	});
</script>
