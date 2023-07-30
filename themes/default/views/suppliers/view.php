<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
    function calculateAge($dob = false){
        $date = new DateTime($dob);
        $now = new DateTime();
        $interval = $now->diff($date);
        return $interval->y;
    }
    $age = calculateAge($supplier->dob);
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-4">
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <div style="max-width:200px; margin: 0 auto;">
                                <?php if (!empty($supplier->logo)) {
                                        echo '<img src="' . base_url() . 'assets/uploads/' . $supplier->logo . '" alt="' . $supplier->logo . '" class="avatar"/>';
                                    }else{
                                        echo '<img src="' . base_url() . 'assets/uploads/avatars/blank.png" alt="' . $supplier->logo . '" class="avatar"/>';
                                    }
                                    ?>
                            </div>
                            <h4 style="font-size:16px;"><?= $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name; ?></h4>
                        </div>
                    </div>
                </div>

                <div class="col-xs-8">
                    <div class="table-responsive">
                        <table width="100%" class="table table-borderless table-striped table-condensed">
                           
                                <tr>
                                    <td><strong><?= lang("code") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $supplier->code; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("company") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $supplier->company; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("name") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $supplier->name; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("email"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= lang($supplier->email) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("phone") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $supplier->phone; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("address"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= lang($supplier->address) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("vat_no"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= lang($supplier->vat_no) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("country"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= lang($supplier->country) ?></strong></td>
                                </tr>
                                
                            
                        </table>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="buttons">
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
                            <i class="fa fa-close"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
                            <i class="fa fa-print"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
                        </a>
                    </div>
                    <?php if ($Owner || $Admin || $GP['reports-suppliers']) { ?>
                        <div class="btn-group">
                            <a href="<?=site_url('reports/supplier_report/'.$supplier->id);?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2" class="btn btn-info" title="<?= lang('suppliers_report') ?>">
                                <i class="fa fa-print"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('suppliers_report') ?></span>
                            </a>
                        </div>
                    <?php } ?>
                   

                    <?php if ($Owner || $Admin || $GP['suppliers-edit']) { ?>
                        <div class="btn-group">
                            <a href="<?=site_url('suppliers/edit/'.$supplier->id);?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2" class="btn btn-primary" title="<?= lang('edit_supplier') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
</div>