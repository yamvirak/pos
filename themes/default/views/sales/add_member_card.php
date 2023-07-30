<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_member_card'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("sales/add_member_card", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang("card_no", "card_no"); ?>
                <div class="input-group">
                    <?php echo form_input('card_no', '', 'class="form-control" id="card_no" required="required"'); ?>
                    <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                        <a href="#" id="genNo">
                            <i class="fa fa-cogs"></i>
                        </a>
                    </div>
                </div>
            </div>
			<?php if($this->Admin || $this->Owner){ ?>
				<div class="form-group">
					<?= lang("award_points", "award_points"); ?>
					<?php echo form_input('award_points', 0, 'class="form-control" id="award_points" min="0" required="required"'); ?>
				</div>
			<?php } else {
				$award_points_input = array(
					'type' => 'hidden',
					'name' => 'award_points',
					'id' => 'award_points',
					'value' => 0,
				);
				echo form_input($award_points_input);
			}?>
            <div class="form-group">
				<?= lang("customer", "customer"); ?>
				<?php echo form_input('customer', '', 'class="form-control" id="customer"'); ?>
			</div>
			
			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<?= lang("each_spent", "each_spent"); ?>
						<?php echo form_input('each_spent', $this->Settings->each_spent, 'class="form-control" min="0" id="each_spent" required'); ?>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<?= lang("ca_point", "ca_point"); ?>
						<?php echo form_input('ca_point',$this->Settings->ca_point, 'class="form-control" id="ca_point" required'); ?>
					</div>
				</div>
			</div>
			
            <div class="form-group">
                <?= lang("expiry_date", "expiry"); ?>
                <?php echo form_input('expiry', $this->cus->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="expiry"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_member_card', lang('add_member_card'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['cus'] = <?=$dp_lang?>;
        $('#customer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
		var customer_points = 0;
        $('#customer').on('select2-close', function () {
            var selected_customer = $(this).val();
            $.ajax({
                type: "get", async: false,
                url: site.base_url + "customers/get_award_points/" + selected_customer,
                dataType: 'json',
                success: function (data) {
                    if (data != null) {
						 customer_points = parseInt(data.ca_points);
                        if (customer_points > 0) {
                             $('#award_points').val(customer_points);
                        } else {
							 $('#award_points').val(0);
                        }
                    } else {
                        $('#award_points').val(0);
                    }
                }
            });
        });
		$('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
    });
</script>    