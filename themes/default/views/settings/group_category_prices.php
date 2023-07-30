<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var ti = 0;
        $(document).on('change', '.price', function () {
            var row = $(this).closest('tr');
            row.first('td').find('input[type="checkbox"]').iCheck('check');
        });
        $(document).on('click', '.form-submit', function () {
            var btn = $(this);
            btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var row = btn.closest('tr');
            var product_id = row.attr('id');
            var price = row.find('.price').val();
            $.ajax({
                type: 'post',
                url: '<?= site_url('system_settings/update_product_group_price/'.$price_group->id); ?>',
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                    product_id: product_id, price: price
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
            // btn.html('<i class="fa fa-check"></i>');
        });
        function price_input(x) {
			
			ti = ti+1;
            var v = x.split('__');
			<?php 
				$price_group = $this->settings_model->getPriceGroupByID($id); 
			?>
			
			var price_group = "<?= $price_group->formula; ?>";
			var ds = price_group;
			var vl = 0;
			if (ds.indexOf("%") !== -1) {
				var pds = ds.split("%");
				if (!isNaN(pds[0])) {
					vl = parseFloat((pds[0] * v[2]) / 100) + parseFloat(v[2]);
				} else {
					vl = parseFloat(v[2]);
				}
			} else {

				if(!is_numeric(ds)){
					<?= $this->session->set_flashdata('error', lang("unexpected_value")); ?>
					return false;
				}
				
				if(ds > 0){
					vl = parseFloat(ds) + parseFloat(v[2]);
				}else{
					vl = parseFloat(v[2]);
				}
			}
			
            return "<div class=\"text-center\"><input type=\"text\" name=\"price"+v[0]+"\" value=\""+(vl != '' ? formatDecimals(vl) : '')+"\" class=\"form-control text-center price\" tabindex=\""+(ti)+"\" style=\"padding:2px;height:auto;\"></div>"; // onclick=\"this.select();\"
        }

        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getCategoryPrices/'.$price_group->id."/?cat=".($price_group->category_id?$price_group->category_id:0)) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "product_group_price_id";
                return nRow;
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, null, null, {"bSortable": false, "mRender": price_input}, {"mRender":currencyFormat}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 1, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('price');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?= form_open('system_settings/product_group_price_actions/'.$price_group->id, 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?> (<?= $price_group->name; ?>)</h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="update_price" data-action="update_price">
                                <i class="fa fa-dollar"></i> <?= lang('update_price') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('system_settings/update_prices_csv/'.$price_group->id); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                                <i class="fa fa-upload"></i> <?= lang('update_prices_csv') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_product_group_prices') ?>
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
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
							<th class="col-xs-3"><?= lang("category"); ?></th>
                            <th class="col-xs-3"><?= lang("product_code"); ?></th>
                            <th class="col-xs-4"><?= lang("product_name"); ?></th>
                            <th><?= lang("price"); ?></th>
							<th class="col-xs-4"><?= lang("price"); ?></th>
                            <th style="width:85px;"><?= lang("update"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>

                        </tbody>
						<tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
							<th></th> 
                        </tr>
                        </tfoot>
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

        $('#update_price').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

