<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_tax_validation'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/add_tax_validation", $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name"><?php echo $this->lang->line("name"); ?></label>

                        <div
                            class="controls"> <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?> </div>
                    </div>
                </div> 
                <div class="col-md-6"> 
                    <div class="form-group">
                        <label for="code"><?php echo $this->lang->line("code"); ?></label>

                        <div
                            class="controls"> <?php echo form_input('code', '', 'class="form-control" id="code"'); ?> </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("goverment", "goverment"); ?>
                        <?php
                            $tr[''] = '';
                            foreach ($tax_rates as $tax_rate) {
                                $tr[$tax_rate->id] = $tax_rate->name;
                            }
                            echo form_dropdown('tax_validation_vat', $tr, (isset($_POST['tax_validation_vat']) ? $_POST['tax_validation_vat'] : ''), 'id="tax_validation_vat" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("tax_validation_vat") . '" required="required" style="width:100%;" ');
                        ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("accommodation", "accommodation"); ?>
                        <?php
                            $rt[''] = '';
                            foreach ($tax_rates as $tax_rate) {
                                $rt[$tax_rate->id] = $tax_rate->name;
                            }
                            echo form_dropdown('tax_validation_acc', $rt, (isset($_POST['tax_validation_acc']) ? $_POST['tax_validation_acc'] : ''), 'id="tax_validation_acc" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("tax_validation_acc") . '" required="required" style="width:100%;" ');
                        ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?= lang("spc", "spc"); ?>
                        <?php
                            $rt[''] = '';
                            foreach ($tax_rates as $tax_rate) {
                                $rt[$tax_rate->id] = $tax_rate->name;
                            }
                            echo form_dropdown('tax_validation_spc', $rt, (isset($_POST['tax_validation_spc']) ? $_POST['tax_validation_spc'] : ''), 'id="tax_validation_spc" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("tax_validation_spc") . '" style="width:100%;" ');
                        ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?= lang("service charge", "service sharge"); ?>
                        <?php
                            $rt[''] = '';
                            foreach ($tax_rates as $tax_rate) {
                                $rt[$tax_rate->id] = $tax_rate->name;
                            }
                            echo form_dropdown('tax_validation_sc', $rt, (isset($_POST['tax_validation_sc']) ? $_POST['tax_validation_sc'] : ''), 'id="tax_validation_sc" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("tax_validation_sc") . ' " required="required"  style="width:100%;"');
                        ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?= lang("plt", "plt"); ?>
                        <?php
                            $rt[''] = '';
                            foreach ($tax_rates as $tax_rate) {
                                $rt[$tax_rate->id] = $tax_rate->name;
                            }
                            echo form_dropdown('tax_validation_plt', $rt, (isset($_POST['tax_validation_plt']) ? $_POST['tax_validation_plt'] : ''), 'id="tax_validation_plt" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("tax_validation_plt") . ' "style="width:100%;" ');
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type"><?php echo $this->lang->line("type"); ?></label>

                        <div class="controls"> <?php $type = array('1' => lang('percentage'), '2' => lang('fixed'));
                            echo form_dropdown('type', $type, '', 'class="form-control" id="type" required="required"'); ?> </div>
                    </div>
                </div>
			</div>
		</div>
        <div class="modal-footer">
            <?php echo form_submit('add_tax_validation', lang('add_tax_validation'), 'class="btn btn-primary"'); ?>
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