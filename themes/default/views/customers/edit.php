<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_customer'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("customers/edit/" . $customer->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                    <label class="control-label" for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
                        <?php
                        foreach ($customer_groups as $customer_group) {
                            $cgs[$customer_group->id] = $customer_group->name;
                        }
                        echo form_dropdown('customer_group', $cgs, $customer->customer_group_id, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                        <?php
                        $pgs[''] = lang('select').' '.lang('price_group');
                        if($price_groups){
							foreach ($price_groups as $price_group) {
								$pgs[$price_group->id] = $price_group->name;
							}
						}
                        echo form_dropdown('price_group', $pgs, $customer->price_group_id, 'class="form-control select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                	<div class="form-group code">
                        <?= lang("code", "code"); ?>
                        <?php echo form_input('code', $customer->code, 'class="form-control tip" id="code" data-bv-notempty="true"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                	<div class="form-group">
							<?= lang("gender", "gender"); ?>
							<?php
								$opt_gender = array(
										'' => lang('select').' '.lang('gender'),
										'male' => lang('male'),
										'female' => lang('female')
									);
								echo form_dropdown('gender', $opt_gender, $customer->gender, 'class="form-control select" id="gender" style="width:100%;"');
							?>
					</div>
                </div>
                <div class="col-md-6">
                	 <div class="form-group company">
                        <?= lang("company", "company"); ?>
                        <?php echo form_input('company', $customer->company, 'class="form-control tip" id="company"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                	<div class="form-group person">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', $customer->name, 'class="form-control tip" id="name" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                	<div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" id="phone"
                               value="<?= $customer->phone ?>"/>
                    </div>
                </div>
                <div class="col-md-6">
                	<div class="form-group">
							<?= lang("customer_status", "customer_status"); ?>
							<?php
								$opt_status = array(
										'active' => lang('active'),
										'spoiled' => lang('spoiled')
									);
								echo form_dropdown('customer_status', $opt_status, $customer->customer_status, 'class="form-control select" id="customer_status" style="width:100%;"');
							?>
					</div>
                </div>
                <?php if($Settings->accounting==1){  ?>
                <div class="col-md-6">
                	<div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" id="email_address"
                               value="<?= $customer->email ?>"/>
                    </div>
				</div>
                <div class="col-md-6">
                	<div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', $customer->vat_no, 'class="form-control" id="vat_no"'); ?>
                    </div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
                        <?= lang("credit_day", "credit_day"); ?>
                        <?php echo form_input('credit_day', $customer->credit_day, 'class="form-control" id="credit_day"'); ?>
                    </div>
				</div>
				<div class="col-md-6">
                	<div class="form-group">
                        <?= lang("credit_amount", "credit_amount"); ?>
                        <?php echo form_input('credit_amount', $customer->credit_amount, 'class="form-control" id="credit_amount"'); ?>
                    </div>
				</div>
				<?php } ?>
				 <?php if($this->config->item("room_rent")){  ?>
				<div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="card_type"><?php echo $this->lang->line("card_type"); ?></label>
                        <?php
                        $cct[''] = lang('select').' '.lang('card_type');
                        if($card_types){
							foreach ($card_types as $card_type) {
								$cct[$card_type->id] = $card_type->name;
							}
						}
                        echo form_dropdown('card_type_id', $cct, $customer->card_type_id, 'class="form-control select" id="card_type_id" style="width:100%;"');
                        ?>
                    </div>
                </div>
				<div class="col-md-6">
                	<div class="form-group">
							<?= lang("identity_no", "identity_no"); ?>
							<?php echo form_input('nric', $customer->nric, 'class="form-control" id="nric" autocomplete="off"'); ?>
					</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="nationality"><?php echo $this->lang->line("nationality"); ?></label>
                        <?php
                        $nation[''] = lang('select').' '.lang('nationality');
                        if($nationality_types){
							foreach ($nationality_types as $nationality_type) {
								$nation[$nationality_type->id] = $nationality_type->name;
							}
						}
                        echo form_dropdown('nationality_id', $nation, $customer->nationality_id, 'class="form-control select" id="nationality_id" style="width:100%;"');
                        ?>
                    </div>
                </div>

                <div class="col-md-6">
                	<div class="form-group">
							<?= lang("occupation", "occupation"); ?>
							<?php echo form_input('occupation',  $customer->occupation, 'class="form-control" id="occupation"'); ?>
						</div>
                </div>
              
                <div class="col-md-6">
                	<div class="form-group">
							<?= lang("dob", "dob"); ?>
							<?php echo form_input('dob', $this->cus->hrsd($customer->dob), 'class="form-control date" id="dob" autocomplete="off"'); ?>
					</div>
                </div>
				<?php } if($this->config->item("product_promotions")){  ?>
                <div class="col-md-6">
					<div class="form-group">
						<label class="control-label"><?php echo $this->lang->line("product_promotion"); ?></label>
						<?php
						$prp[''] = lang('select').' '.lang('product_promotion');
						if($product_promotions){
							foreach ($product_promotions as $product_promotion) {
								$prp[$product_promotion->id] = $product_promotion->name;
							}
						}
						echo form_dropdown('product_promotion_id', $prp, 0, 'class="form-control select" id="product_promotion_id" style="width:100%;"');
						?>
					</div>
				</div>
				<?php } if($this->config->item("saleman")){  ?>
				<div class="col-md-6">
					<div class="form-group">
						<label class="control-label"><?php echo $this->lang->line("saleman"); ?></label>
						<?php
							$sms[''] = lang('select').' '.lang('saleman');
							foreach($salemans as $saleman){
								$sms[$saleman->id] = $saleman->first_name .' '.$saleman->last_name;
							}
							echo form_dropdown('saleman_id', $sms, 0, 'class="form-control select" id="saleman_id" style="width:100%;"');
						?>
					</div>
				</div>
				<?php } ?>

                <div class="col-md-12">
                	 <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_textarea('address',(isset($_POST['address']) ? $_POST['address'] : $customer->address), 'class="form-control" id="address" required="required" style="height: 50px;"'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group hidden">
                        <?= lang("city", "city"); ?>
                        <?php echo form_input('city', $customer->city, 'class="form-control" id="city"'); ?>
                    </div>
                </div>
				
                <div class="col-md-6">
					<?php if($this->config->item("concretes")){ ?>
						 <div class="form-group">
							<?= lang("credit_quantity", "credit_quantity"); ?>
							<?php echo form_input('credit_quantity', $customer->credit_quantity, 'class="form-control" id="credit_quantity"'); ?>
						</div>
					<?php } if($this->config->item('customer_orders')){ ?>
						<div class="form-group hidden">
							<?= lang("username", "username"); ?>
							<?php echo form_input('username', $customer->username, 'class="form-control" id="username"'); ?>
						</div>

						<div class="form-group hidden">
							<?= lang("password", "password"); ?>
							<?php echo form_password('password', '', 'class="form-control" id="password"'); ?>
						</div>
					<?php } if(!$this->config->item("room_rent") && !$Settings->installment==1){  ?>
						<div class="form-group hidden">
							<?= lang("ccf1", "cf1"); ?>
							<?php echo form_input('cf1', $customer->cf1, 'class="form-control" id="cf1"'); ?>
						</div>
						<div class="form-group hidden">
							<?= lang("ccf2", "cf2"); ?>
							<?php echo form_input('cf2', $customer->cf2, 'class="form-control" id="cf2"'); ?>
						</div>
						<div class="form-group hidden">
							<?= lang("ccf3", "cf3"); ?>
							<?php echo form_input('cf3', $customer->cf3, 'class="form-control" id="cf3"'); ?>
						</div>
						<div class="form-group hidden">
							<?= lang("ccf4", "cf4"); ?>
							<?php echo form_input('cf4', $customer->cf4, 'class="form-control" id="cf4"'); ?>
						</div>
						<div class="form-group hidden">
							<?= lang("ccf5", "cf5"); ?>
							<?php echo form_input('cf5', $customer->cf5, 'class="form-control" id="cf5"'); ?>
						</div>
						<div class="form-group hidden">
							<?= lang("ccf6", "cf6"); ?>
							<?php echo form_input('cf6', $customer->cf6, 'class="form-control" id="cf6"'); ?>
						</div>
					<?php } ?>
                </div>
            </div>
			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_customer', lang('edit_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function (e) {

		$('#credit_day').focus(function () {
			old_credit_day = $(this).val();
		}).change(function () {
			if (!is_numeric($(this).val())) {
				$(this).val(old_credit_day);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});

        $('#credit_amount').focus(function () {
			old_credit_amount = $(this).val();
		}).change(function () {
			if (!is_numeric($(this).val())) {
				$(this).val(old_credit_amount);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});
	});
</script>
