
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_service'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_configuration/edit_service/".$id, $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("code", "code") ?>
                        <div class="input-group">
                            <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : $service->code), 'class="form-control" id="code"  required="required"') ?>
                            <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;">
                                <i class="fa fa-random"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("service_type", "service_type"); ?>
                        <?php
                            $st[''] = '';
                            foreach ($service_types as $service_type) {
                                $st[$service_type->id] = $service_type->name;
                            }
                            echo form_dropdown('service_type', $st, (isset($_POST['service_type']) ? $_POST['service_type'] : $service->electricity), 'id="service_type" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("service_type") . '" required="required" style="width:100%;" ');
                        ?>
                    </div>
                </div>
            
            <div class="col-md-6">
                <div class="form-group">
    				<?= lang("name", "name"); ?>
    				<?php echo form_input('name', $service->name, 'class="form-control" id="name" required="required"'); ?>
    			</div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
    				<?= lang("price", "price"); ?>
    				<?php echo form_input('price', $this->cus->formatDecimal($service->price), 'class="form-control" id="price" required="required"'); ?>
    			</div>
            </div>
            <div class="col-md-6">
                <div class="form-group standard">
                        <?= lang('product_unit', 'unit'); ?>
                        <div class="input-group">
                            <?php 
                            $pu[''] = lang('select').' '.lang('unit');
                            if($base_units){
                                foreach ($base_units as $bu) {
                                    $pu[$bu->id] = $bu->name .' ('.$bu->code.')';
                                }
                            }
                            ?>
                            <?= form_dropdown('unit', $pu, set_value('unit', $service->unit), 'class="form-control tip" required="required" id="unit" style="width:100%;"'); ?>
                            <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                <a href="<?= site_url('system_settings/add_unit'); ?>" class="external" data-toggle="modal" data-target="#myModal">
                                    <i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
                                </a>
                            </div>
                        </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <?= lang('inactive', 'inactive'); ?>
                    <?php
                        $in_opts[0] = lang('no');
                        $in_opts[1] = lang('yes');
                    ?>
                    <?= form_dropdown('inactive', $in_opts, $service->inactive, 'class="form-control" id="inactive" style="width:100%;"'); ?>
                </div>
            </div>
            <div class="col-md-6">
            <?php if($this->Settings->accounting==1){ ?>
                <h2 style="font-size:17px; color:#428BCA;"><?=lang("account_settings")?></h2>
                <hr style="margin-top:0px;margin-bottom:10px;"/>
            
                <div class="form-group">
                    <?= lang("discount_account", "discount_account") ?>
                    <select name="discount_account" class="form-control select" id="discount_account" style="width:100%">
                        <option value=""><?= lang('select_discount_account') ?></option>
                        <?= $discount_accounts ?>
                    </select>
                </div>
                <div class="form-group">
                    <?= lang("sale_account", "sale_account") ?>
                    <select name="sale_account" class="form-control select" id="sale_account" style="width:100%">
                        <option value=""><?= lang('select_sale_account') ?></option>
                        <?= $sale_accounts ?>
                    </select>
                </div>
                <div class="form-group">
                    <?= lang("expense_account", "expense_account") ?>
                    <select name="expense_account" class="form-control select" id="expense_account" style="width:100%">
                        <option value=""><?= lang('select_expense_account') ?></option>
                        <?= $expense_accounts ?>
                    </select>
                </div>
                <?php } ?>
            </div>
		</div>
    </div>
		<div class="modal-footer">
			<?php echo form_submit('edit_service', lang('edit_service'), 'class="btn btn-primary"'); ?>
		</div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#random_num').click(function(){
            $(this).parent('.input-group').children('input').val(generateCardNo(8));
        });
        function generateCardNo(x) {
            if(!x) { x = 16; }
            chars = "1234567890";
            no = "";
            for (var i=0; i<x; i++) {
                var rnum = Math.floor(Math.random() * chars.length);
                no += chars.substring(rnum,rnum+1);
            }
            return no;
        }
    })
</script>
