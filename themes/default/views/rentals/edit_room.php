<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_room'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_configuration/edit_room/".$id, $attrib); ?>
        
		<div class="modal-body">
            <p><?= lang('update_info'); ?></p>

            <div class="row">
				<div class="col-md-6">
					 <div class="form-group">
                        <?= lang("biller", "biller"); ?>
						<?php
							$bl[''] = '';
							foreach ($billers as $biller) {
								$bl[$biller->id] = $biller->name;
							}
							echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $row->biller_id), 'id="biller" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" style="width:100%;" ');
						?>
                    </div>
                </div>
                <div class="col-md-6">
					<div class="form-group">
                        <?= lang("warehouse", "warehouse"); ?>
						<?php
							$wh[''] = '';
							foreach ($warehouses as $warehouse) {
								$wh[$warehouse->id] = $warehouse->name;
							}
							echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $row->warehouse_id), 'id="warehouse" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
						?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', set_value('name', $row->name), 'class="form-control" id="name" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("room_type", "room_type"); ?>
						<?php
							$rt[''] = '';
							foreach ($room_types as $room_type) {
								$rt[$room_type->id] = $room_type->name;
							}
							echo form_dropdown('room_type', $rt, (isset($_POST['room_type']) ? $_POST['room_type'] : $row->room_type_id), 'id="room_type" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("room_type") . '" required="required" style="width:100%;" ');
						?>
                    </div>
                </div>
                <div class="col-md-6">
					<div class="form-group">
                        <?= lang("product", "product"); ?>
						<?php
							$pd[''] = '';
							foreach ($products as $product) {
								$pd[$product->id] = $product->name;
							}
							echo form_dropdown('product_id', $pd, (isset($_POST['product_id']) ? $_POST['product_id'] : $row->product_id), 'id="product_id" required="required" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("product") . '" style="width:100%;" ');
						?>
                    </div>
                </div>
                <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("price", "price"); ?>
                            <?php echo form_input('price', (isset($_POST['price']) ? $_POST['price'] :$row->price), 'class="form-control input-tip bold" id="price" name="price"'); ?>
                        </div>
                </div>
                <div class="col-md-6">
					<div class="form-group">
                        <?= lang("floor", "floor"); ?>
						<?php
							$fl[''] = lang("select")." ".lang("floor");
							foreach ($floors as $floor) {
								$fl[$floor->id] = $floor->floor;
							}
							echo form_dropdown('floor', $fl, (isset($_POST['floor']) ? $_POST['floor'] : $row->floor), 'id="floor" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("floor") . '" style="width:100%;" required="required"');
						?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("bed_number", "bed_number"); ?>
						<?php
							$bn[''] = '';
							foreach ($bed_numbers as $bed_number) {
								$bn[$bed_number->id] = $bed_number->name;
							}
							echo form_dropdown('bed_number', $bn, (isset($_POST['bed_number']) ? $_POST['bed_number'] : $row->bed_number_id), 'id="bed_number" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("bed_number") . '" required="required" style="width:100%;" ');
						?>
                    </div>
                </div>
                <div class="col-md-6">
					<div class="form-group">
                        <?= lang("status", "status"); ?>
						<?php
							$st = array('active'=>lang('active'),'inactive'=>lang('inactive'));
							echo form_dropdown('status', $st, (isset($_POST['status']) ? $_POST['status'] : $row->status), 'id="status" class="form-control select"  style="width:100%;"');
						?>
                    </div>
				</div>
			</div>
		</div>

		<div class="modal-footer">
			<?php echo form_submit('edit_room', lang('edit_room'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
</div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script type="text/javascript">
        $('#product_id').change(function (e) {
            var product_id = $(this).val();
            $.ajax({
                url : site.base_url + "rentals_configuration/getProductPrice",
                dataType : "JSON",
                type : "GET",
                data : { product_id : product_id },
                success : function(data){
                    localStorage.setItem('sldpcommission', data.price);
                    $('#price').val(data.price);
                }
            });

        });
</script>
