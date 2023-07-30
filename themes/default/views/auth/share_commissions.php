<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var ti = 0;
        $(document).on('change', '.commission', function () {
            var row = $(this).closest('tr');
            row.first('td').find('input[type="checkbox"]').iCheck('check');
        });
        $(document).on('click', '.form-submit', function () {
            var btn = $(this);
            btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var row = btn.closest('tr');
            var product_id = row.attr('id');
            var commission = row.find('.commission').val();
            $.ajax({
                type: 'post',
                url: '<?= site_url('auth/update_salesman_product_commissions/'.$salesman->id); ?>',
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                    product_id: product_id, commission: commission
                },
                success: function (data) {
                    if (data.status != 1)
                        btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                    else
                        btn.removeClass('btn-primary').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-check"></i>');
                },
                error: function (data) {
                    btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                }
            });
        });
        function commision_input(x) {
			
			ti = ti+1;
            var v = x.split('__');
            return "<div class=\"text-center\"><input type=\"text\" name=\"commission"+v[0]+"\" value=\""+(v[1] != '' ? (v[1]) : '')+"\" class=\"form-control text-center commission\" tabindex=\""+(ti)+"\" style=\"padding:2px;height:auto;\"></div>"; // onclick=\"this.select();\"
        }
    });
</script>
<?= form_open('auth/share_commission_actions/'.$salesman->id, 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?> (<?= $salesman->first_name.' '.$salesman->last_name; ?>)</h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="update_commission" data-action="update_commission">
                                <i class="fa fa-dollar"></i> <?= lang('update_commission') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("list_results"); ?></p>
                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr>
								<th class="col-xs-3"><?= lang("commission_type"); ?></th>
								<th class="col-xs-3"><?= lang("description"); ?></th>
								<th class="col-xs-2"><?= lang("min_amount"); ?></th>
								<th class="col-xs-2"><?= lang("max_amount"); ?></th>
								<th class="col-xs-2"><?= lang("commission"); ?></th>
								<th class="text-center"><i class="fa fa-plus tip pointer add" title="Add" style="cursor:pointer"></i></th>
							</tr>
                        </thead>
                        <tbody id="tbody">
							<?php
								$tbody = "";
								if($commissions){
									foreach($commissions as $commission){
										if($commission->commission_type=="Normal"){
											$tbody .= '<tr>
														<td><input value="Normal" type="hidden" class="commission_type" name="commission_type[]"/>'.lang($commission->commission_type).'</td>
														<td><input type="text" value="'.$commission->description.'" class="form-control description" name="description[]"/></td>
														<td colspan="2"><input type="hidden" name="min_amount[]"/><input type="hidden" name="max_amount[]"/></td>
														<td><input type="text" value="'.$commission->commission.'" class="form-control commission text-right" name="commission[]"/></td>
														<td class="text-center"></td>
													</tr>';
										}else{
											$tbody .= '<tr>
															<td><input value="Target" type="hidden" class="commission_type" name="commission_type[]"/>'.lang($commission->commission_type).'</td>
															<td><input type="text" value="'.$commission->description.'" class="form-control description" name="description[]"/></td>
															<td><input type="text" value="'.$this->cus->formatDecimal($commission->min_amount).'" class="form-control min_amount text-right" name="min_amount[]"/></td>
															<td><input type="text" value="'.$this->cus->formatDecimal($commission->max_amount).'" class="form-control max_amount text-right" name="max_amount[]"/></td>
															<td><input type="text" value="'.$commission->commission.'"class="form-control commission text-right" name="commission[]"/></td>
															<td class="text-center"><i class="fa fa-times tip pointer del" title="Remove" style="cursor:pointer"></i></td>
														</tr>';
										}
										
									}
								}
								if($tbody != ""){
									echo $tbody;
								}else { ?>
									<tr>
										<td><input value="Normal" type="hidden" class="commission_type" name="commission_type[]"/><?= lang("normal") ?></td>
										<td><input type="text" class="form-control description" name="description[]"/></td>
										<td colspan="2"><input type="hidden" name="min_amount[]"/><input type="hidden" name="max_amount[]"/></td>
										<td><input type="text" class="form-control commission text-right" name="commission[]"/></td>
										<td class="text-center"></td>
									</tr>
									<tr>
										<td><input value="Target" type="hidden" class="commission_type" name="commission_type[]"/><?= lang("target") ?></td>
										<td><input type="text" class="form-control description" name="description[]"/></td>
										<td><input type="text" class="form-control min_amount text-right" name="min_amount[]"/></td>
										<td><input type="text" class="form-control max_amount text-right" name="max_amount[]"/></td>
										<td><input type="text" class="form-control commission text-right" name="commission[]"/></td>
										<td class="text-center"><i class="fa fa-times tip pointer del" title="Remove" style="cursor:pointer"></i></td>
									</tr>
								<?php } ?>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {
		
		$(".add").click(function (){
			var tbody = '<tr>';
					tbody += '<td><input value="Target" type="hidden" class="commission_type" name="commission_type[]"/><?= lang("target") ?></td>';
					tbody += '<td><input type="text" class="form-control description" name="description[]"/></td>';
					tbody += '<td><input type="text" class="form-control min_amount text-right" name="min_amount[]"/></td>';
					tbody += '<td><input type="text" class="form-control max_amount text-right" name="max_amount[]"/></td>';
					tbody += '<td><input type="text" class="form-control commission text-right" name="commission[]"/></td>';
					tbody += '<td class="text-center"><i class="fa fa-times tip pointer del" title="Remove" style="cursor:pointer"></i></td>';
				tbody += "</tr>";
			$('#tbody').append(tbody);
		});
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
		});
		
        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#update_commission').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

