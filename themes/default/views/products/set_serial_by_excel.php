<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('set_serial_by_excel'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("products/set_serial_by_excel/", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-12">
                    <div class="well well-small">
                        <a href="<?php echo base_url(); ?>assets/csv/sample_set_product_serial.xlsx" class="btn btn-primary pull-right">
                            <i class="fa fa-download"></i> <?= lang("download_sample_file") ?>
                        </a>
                        <span class="text-warning"><?= lang("csv1"); ?></span><br/><?= lang("csv2"); ?> 
                        <span class="text-info">(<?= lang("supplier") . ', ' . lang("date"). ', ' . lang("serial"). ', ' . lang("cost"). ', ' . lang("price"). ', ' . lang("color"). ', ' . lang("description"); ?>)</span>
                        <?= lang("csv3"); ?>
                    </div>
					<input type="hidden" value="<?= $product_id ?>" name="product" />
					<input type="hidden" value="<?= $warehouse_id ?>" name="warehouse" />
					
                    <div class="form-group">
						<label for="xlsx_file"><?= lang("upload_file"); ?></label>
						<input type="file" data-browse-label="<?= lang('browse'); ?>" accept=".xls, .xlsx" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="xlsx_file" required="required"/>
					</div>

                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('set_serial', lang('set_serial'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script type="text/javascript">
	$(function(){
		$("#slbiller").change(biller); biller();
		function biller(){
			var biller = $("#slbiller").val();
			var project = 0;
			$.ajax({
				url : "<?= site_url("products/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
	});
</script>