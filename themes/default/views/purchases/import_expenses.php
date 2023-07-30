<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('import_expenses'); ?></h4>
        </div>
        <?php
        $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("purchases/import_expenses", $attrib)
        ?>
        <div class="modal-body">
			Reference		Supplier Code	Category Code	Amount	Paid	Note

            <a href="<?php echo base_url(); ?>assets/csv/expenses_sample.xls" class="btn btn-primary pull-right">
                <i class="fa fa-download"></i> <?= lang("download_sample_file") ?>
            </a>
            <p><?= lang('enter_info'); ?></p>

            <div class="well well-small">
                <span class="text-warning"><?= lang("csv1"); ?></span><br/>
                <?= lang("csv2"); ?> 
                <span class="text-info">
                    (<?= lang("date") . ', ' . lang("reference_no"). ', ' . lang("biller") . ', ' . lang("project") . ', ' . lang("supplier"). ', ' . lang("category_code"). ', ' . lang("amount"). ', ' . lang("note"). ', ' . lang("warehouse"); ?>)
                </span> <?= lang("csv3"); ?><br>
            </div>
             <div class="col-md-12">
                <div class="form-group">
                    <label for="csv_file"><?= lang("upload_file"); ?></label>
                    <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false"
                    data-show-preview="false" required="required"/>
                </div>
            </div>
    
            <div class="clearfix"></div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('import', lang('import'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>

