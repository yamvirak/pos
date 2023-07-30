<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_branch_prefix'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_branch_prefix/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>
             <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                    <?= lang("biller", "biller"); ?>
                        <?php
                            $bl[""] = lang('select').' '.lang('biller');
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                            }
                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $row->bill_id), 'id="biller" class="form-control select" style="width:100%;"');
                        ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date"><?= lang('date') ?></label>
                        <?php echo form_input('date', $this->cus->hrld($row->date), 'class="form-control datetime" id="date" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('bill_prefix', 'bill_prefix'); ?>
                        <?= form_input('bill_prefix', $row->bill_prefix, 'class="form-control" id="bill_prefix"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('quotation', 'quotation'); ?>
                        <?= form_input('qu', $row->qu, 'class="form-control" id="qu"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('sale_order', 'sale_order'); ?>
                        <?= form_input('so', $row->so, 'class="form-control" id="so"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('purchase_order', 'purchase_order'); ?>
                        <?= form_input('po', $row->po, 'class="form-control" id="po"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('pos', 'pos'); ?>
                        <?= form_input('pos', $row->pos, 'class="form-control" id="pos"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('payment_rv', 'payment_rv'); ?>
                        <?= form_input('pay', $row->pay, 'class="form-control" id="pay"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('payment_pv', 'payment_pv'); ?>
                        <?= form_input('ppay', $row->ppay, 'class="form-control" id="ppay"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('expense', 'expense'); ?>
                        <?= form_input('ex', $row->ex, 'class="form-control" id="ex"'); ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('journals', 'journals'); ?>
                        <?= form_input('jn', $row->jn, 'class="form-control" id="jn"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('installment', 'installment'); ?>
                        <?= form_input('inst', $row->inst, 'class="form-control" id="inst"'); ?>
                    </div>
                </div>
                <?php echo form_hidden('id', $branch_prefix->id); ?>
            
            </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_branch_prefix', lang('edit_branch_prefix'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>