<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_room_rate'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_configuration/add_room_rates", $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            	<div class="form-group">
                    <?= lang("code", "code"); ?>
                    <div class="input-group">
                        <?php echo form_input('code', '', 'class="form-control bold" id="code" required="required"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                            <a href="#" id="genNo">
                                <i class="fa fa-cogs"></i>
                            </a>
                        </div>
                    </div>
                </div>
				<div class="form-group">
                    <?= lang("source_type", "source_type"); ?>
                    <?php
                        $st[''] = '';
                        foreach ($source_types as $source_type) {
                            $st[$source_type->id] = $source_type->name;
                        }
                        echo form_dropdown('source_type', $st, (isset($_POST['source_type']) ? $_POST['source_type'] : ''), 'id="source_type" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("source_type") . '" required="required" style="width:100%;" ');
                    ?>
                </div>
                <div class="form-group">
                    <?= lang("room_type", "room_type"); ?>
                    <?php
                        $rt[''] = '';
                        foreach ($room_types as $room_type) {
                            $rt[$room_type->id] = $room_type->name;
                        }
                        echo form_dropdown('room_type', $rt, (isset($_POST['room_type']) ? $_POST['room_type'] : ''), 'id="room_type" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("room_type") . '" required="required" style="width:100%;" ');
                    ?>
                </div>
                <div class="form-group">
                    <?= lang("price", "price"); ?>
                    <?php echo form_input('price', $row->price, 'class="form-control" id="price" required="required"'); ?>
                </div>
				<div class="form-group">
                    <?= lang('status', 'status'); ?>
                    <?php
                    	$in_opts['active'] = lang('active');
                        $in_opts['inactive'] = lang('inactive');
                        
                    ?>
                    <?= form_dropdown('status', $in_opts, $service->status, 'class="form-control" id="status" style="width:100%;"'); ?>
                </div>
		</div>
	
		<div class="modal-footer">
			<?php echo form_submit('add_room_rate', lang('add_room_rate'), 'class="btn btn-primary"'); ?>
		</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#genNo').click(function () {
            var no = generateRoomRatesNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
    });
</script>   


