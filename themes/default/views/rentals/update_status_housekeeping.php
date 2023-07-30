<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_status_room'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_housekeeping/update_status/" . $room->id, $attrib); ?>
        <div class="modal-body">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= lang('room_details'); ?>
                </div>
                <div class="panel-body">
                    <table class="bold table table-bordered table-hover table-striped table-condensed reports-table dataTable" border="1" style="margin-bottom:0;">
                        <tbody>
                            <tr>
                                <td><?= lang('room_type'); ?></td>
                                <td><?= $room->room_type_name; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('floor'); ?></td>
                                <td><?= $room->floor; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('room_name'); ?></td>
                                <td><?= $room->name; ?></td>
                            </tr>
							<tr>
                                <td><?= lang('phone'); ?></td>
                                <td><?= $customer->phone; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('availability'); ?></td>
                                <td class="text_left"><strong><?= $this->cus->row_status($room->availability); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?= lang('housekeeping_status'); ?></td>
                                <td class="text_left"><strong><?= $this->cus->row_status($room->housekeeping_status); ?></strong></td>
                            </tr>
                            <tr class="hidden">
                                <td><?= lang('status'); ?></td>
                                <td style="text-align:left !important;"><strong><?= $this->cus->row_status($room->status); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
			<div class="form-group">
                <?= lang('housekeeping_status', 'housekeeping_status'); ?>
                <?php
					$opts = array('cleaned' => lang('cleaned'), 'dirty' => lang('dirty'));
                ?>
                <?= form_dropdown('status', $opts, $room->housekeeping_status, 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
            </div>

            <div class="no-cancelled_reservation">
				<div class="form-group">
					<?= lang("date", "date"); ?>
					<?php 
						$cancelled_reservation = $this->cus->hrsd(date("Y-m-d"));
						if(!empty($room->cancelled_reservation) && $room->cancelled_reservation != '0000-00-00'){
							$cancelled_reservation = $this->cus->hrsd($room->cancelled_reservation);
						} 
					?>
					<?php echo form_input('cckodate', (isset($_POST['cckodate']) ? $_POST['cckodate'] : $cancelled_reservation), 'class="form-control date bold"'); ?>
				</div>
			</div>

			<div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $this->cus->decode_html($room->note)), 'class="form-control" id="note"'); ?>
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
		<?php if($room->status != 'checked_in'){ ?>
			$(".no-checked_in").hide();
			$(".no-cancelled_reservation").hide();
		<?php } ?>
		<?php if($room->status != 'checked_out'){ ?>
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
