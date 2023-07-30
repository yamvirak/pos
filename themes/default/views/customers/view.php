<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
    function calculateAge($dob = false){
        $date = new DateTime($dob);
        $now = new DateTime();
        $interval = $now->diff($date);
        return $interval->y;
    }
    $age = calculateAge($customer->dob);
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-4">
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <div style="max-width:200px; margin: 0 auto;">
                                <?php if (!empty($customer->logo)) {
                                        echo '<img src="' . base_url() . 'assets/uploads/' . $customer->logo . '" alt="' . $customer->logo . '" class="avatar"/>';
                                    }else{
                                        echo '<img src="' . base_url() . 'assets/uploads/avatars/blank.png" alt="' . $customer->logo . '" class="avatar"/>';
                                    }
                                    ?>
                            </div>
                            <h4 style="font-size:16px;"><?= $customer->name?></h4>
                        </div>
                    </div>
                </div>

                <div class="col-xs-8">
                    <div class="table-responsive">
                        <table width="100%" class="table table-borderless table-striped table-condensed">
                           
                                <tr>
                                    <td><strong><?= lang("code") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $customer->code; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("name") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $customer->name; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("gender"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= lang($customer->gender) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("card_type"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= lang($customer->card_types) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("card_id"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= lang($customer->nric) ?></strong></td>
                                </tr>
                                <tr class="hidden">
                                    <td><strong><?= lang("age"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= $age ?></strong></td>
                                </tr>
                                <tr class="hidden">
                                    <td><strong><?= lang("dob"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= $this->cus->hrsd($customer->dob) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("nationality") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $customer->nationality; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("occupation") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $customer->occupation; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("phone") ?></strong></td>
                                    <td>:</td>
                                    <td><?= $customer->phone; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong><?= lang("address"); ?></strong></td>
                                    <td>:</td>
                                    <td><?= lang($customer->address) ?></strong></td>
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
                    <?php if ($Owner || $Admin || $GP['hospitals-edit_customer']) { ?>
                        <div class="btn-group">
                            <a href="<?=site_url('customers/edit/'.$customer->id);?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2" class="btn btn-primary" title="<?= lang('edit_customer') ?>">
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