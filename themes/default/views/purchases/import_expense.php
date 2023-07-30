<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('import_expense'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                //$attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
				$attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("purchases/import_expense", $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
						<div class="well well-small">
                            <a href="<?php echo base_url(); ?>assets/csv/sample_expense.xlsx"
                               class="btn btn-primary pull-right"><i
                                    class="fa fa-download"></i> <?= lang("download_sample_file") ?></a>
                            <span class="text-warning"><?= lang("csv1"); ?></span>
                                </span> <?= lang("csv3"); ?>
                                <p><?= lang('images_location_tip'); ?></p>

                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("biller", "biller"); ?>
									<?php
									$bl[""] = "";
									foreach ($billers as $biller) {
										$bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
									}
									echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
									?>
								</div>
							</div>
						<?php } else {
							$biller_input = array(
								'type' => 'hidden',
								'name' => 'biller',
								'id' => 'biller',
								'value' => $this->session->userdata('biller_id'),
							);
							echo form_input($biller_input);
						} ?>
						
						
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("warehouse", "warehouse"); ?>
								<?php

								foreach ($warehouses as $warehouse) {
									$wh[$warehouse->id] = $warehouse->name;
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="warehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="xlsx_file"><?= lang("upload_file"); ?></label>
								<input type="file" data-browse-label="<?= lang('browse'); ?>" accept=".xls, .xlsx" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="xlsx_file" required="required"/>
							</div>
						</div>
						
                        <div class="col-sm-12">
                            <div class="form-group">
                                <?php echo form_submit('import', $this->lang->line("import"), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
						
                    </div>
                </div>
               
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>


