<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    @media print {
        #myModal .modal-content {
            display: none !important;
        }
    }
</style>
<div class="modal-dialog no-modal-header">
    <div class="modal-content">
        <div class="modal-body print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
                <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
                </div>
            <div class="clearfix"></div>
            <div class="row padding10">
                <div class="col-xs-6">
                    <h2 class=""><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                    <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>
                    <?php
                    echo $supplier->address . "<br />" . $supplier->city . " " . $supplier->postal_code . " " . $supplier->state . "<br />" . $supplier->country;
                    echo "<p>";
                    if ($supplier->cf1 != "-" && $supplier->cf1 != "") {
                        echo "<br>" . lang("ccf1") . ": " . $supplier->cf1;
                    }
                    if ($supplier->cf2 != "-" && $supplier->cf2 != "") {
                        echo "<br>" . lang("ccf2") . ": " . $supplier->cf2;
                    }
                    if ($supplier->cf3 != "-" && $supplier->cf3 != "") {
                        echo "<br>" . lang("ccf3") . ": " . $supplier->cf3;
                    }
                    if ($supplier->cf4 != "-" && $supplier->cf4 != "") {
                        echo "<br>" . lang("ccf4") . ": " . $supplier->cf4;
                    }
                    if ($supplier->cf5 != "-" && $supplier->cf5 != "") {
                        echo "<br>" . lang("ccf5") . ": " . $supplier->cf5;
                    }
                    if ($supplier->cf6 != "-" && $supplier->cf6 != "") {
                        echo "<br>" . lang("ccf6") . ": " . $supplier->cf6;
                    }
                    echo "</p>";
                    echo lang("tel") . ": " . $supplier->phone . "<br />" . lang("email") . ": " . $supplier->email;
                    ?>

                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <p style="font-weight:bold;"><?= lang("date"); ?>: <?= $this->cus->hrsd($deposit->date); ?></p>
                </div>
            </div>
            <div class="well">
                <table class="table table-borderless" style="margin-bottom:0;">
                    <tbody>
                    <tr>
                        <td><strong><?= lang("amount"); ?></strong></td>
                        <td>
                            <strong><?php echo $this->cus->formatMoney($deposit->amount); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("paid_by"); ?></strong></td>
                        <td><strong><?= $deposit->paid_by; ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $deposit->note; ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div style="clear: both;"></div>
            <div class="row">
                <div class="col-sm-4 pull-left">
                    <p>&nbsp;</p>

                    <p>&nbsp;</p>

                    <p>&nbsp;</p>

                    <p style="border-bottom: 1px solid #666;">&nbsp;</p>

                    <p><?= lang("stamp_sign"); ?></p>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>