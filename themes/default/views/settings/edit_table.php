<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_table'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_table/".$id, $attrib); ?>
		<div class="modal-body">
            <p><?= lang('update_info'); ?></p>
            <div class="row">
				<div class="col-md-12">
                    <div class="form-group">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', set_value('name', $row->name), 'class="form-control" id="name" required="required"'); ?>
                    </div>
					<div class="form-group">
                        <?= lang("floor", "floor"); ?>
						<?php
							$fl[''] = lang("select")." ".lang("floor");
							foreach ($floors as $floor) {
								$fl[$floor->id] = $floor->floor;
							}
							echo form_dropdown('floor', $fl, (isset($_POST['floor']) ? $_POST['floor'] : $row->floor), 'id="floor" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("floor") . '" style="width:100%;" required="required"');
						?>
                    </div>
					<div class="form-group">
                        <?= lang("warehouse", "warehouse"); ?>
						<?php
							$wh[''] = '';
							foreach ($warehouses as $warehouse) {
								$wh[$warehouse->id] = $warehouse->name;
							}
							echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $row->warehouse_id), 'id="warehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
						?>
                    </div>
					<div class="form-group">
                        <?= lang("product", "product"); ?>
						<?php
							$pr[''] = lang("select")." ".lang("product");
							foreach ($products as $product) {
								$pr[$product->id] = $product->name;
							}
							echo form_dropdown('product_id', $pr, (isset($_POST['product_id']) ? $_POST['product_id'] : $row->product_id), 'id="product" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("product_id") . '" style="width:100%;" ');
						?>
                    </div>
                    <div class="form-group">
                        <?= lang("status", "status"); ?>
                        <?php 
							$status = array('active' => lang('active'), 'inactive' => lang('inactive'));
                            echo form_dropdown('status', $status, $row->status, 'class="form-control select" id="status" placeholder="' . lang('select_active') . '" ');
						?>
                    </div>
					<div class="form-group">
						<?= lang("description", "description"); ?>
						<?php echo form_textarea('description', set_value('description', $row->description), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<?php echo form_submit('edit_table', lang('edit_table'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
