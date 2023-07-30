<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header no-print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_member_card'); ?></h4>
        </div>
        <div class="modal-body">
            <?php if ($member_card->expiry && $member_card->expiry < date('Y-m-d')) { ?>
                <div class="alert alert-danger">
                    <?= lang('card_expired') ?>
                </div>
            <?php } ?>
            <div class="card">
                <div class="front">
                    <img src="<?=$assets;?>images/card.png" alt="" class="card_img">
                    <div class="card-content white-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="353px" height="206px" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <text x="5"  y="20" style="font-size:16;fill:#FFF;">
                                <?= lang('member_card'); ?>
                            </text>
                            <text x="175"  y="20" style="font-size:16;fill:#FFF;">
                                <?= wordwrap($member_card->card_no, 4, ' ', true); ?>
                            </text>
                            <text x="5"  y="75" style="font-size:36;fill:#FFF;">
                                <?= $customer ? ($customer->company != '-' ? $customer->company : $customer->name) : ''; ?>
                            </text>
                            <text x="5"  y="115" style="font-size:14;fill:#FFF;">
                                <?= $member_card->expiry ? lang('expiry').': '.$this->cus->hrsd($member_card->expiry) : ''; ?>
                            </text>
                            <image xlink:href="<?= $this->cus->save_barcode($member_card->card_no, 'code128', 50, true, true); ?>" x="-10" y="135" height="50" width="353" />
                        </svg>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
                <div class="back">
                    <img src="<?=$assets;?>images/card2.png" alt="" class="card_img">
                    <div class="card-content">
                        <div class="middle">
                            <?= '<img src="' . base_url('assets/uploads/logos/' . $Settings->logo2) . '" alt="' . $Settings->site_name . '" />'; ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if (! $member_card->expiry || $member_card->expiry > date('Y-m-d')) { ?>
			
			<div id="buttons" style="padding-top:10px;" class="no-print">
				<hr>
				<div class="btn-group btn-group-justified">
					<div class="btn-group"> 
						<button type="button" class="btn btn-primary btn-block no-print" onClick="window.print();"><?= lang('print'); ?></button>
					</div>
					<div class="btn-group">
						<a href="<?= site_url("sales/redeem_points/".$member_card->id); ?>" class="btn btn-danger btn-block no-print"><?= lang('redeem_points'); ?></a>
					</div>
				</div>
			</div>
					
				
				
			<?php } ?>
        </div>
    </div>
</div>
