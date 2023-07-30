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
            var product_code = row.find('.code').val();
			var product_name = row.find('.name').val();
			var product_price = row.find('.price').val();
            $.ajax({
                type: 'post',
                url: '<?= site_url('system_settings/update_category_product/'.$id); ?>',
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                    product_id : product_id, 
					product_code : product_code,
					product_name : product_name,
					product_price : product_price
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
		
        function code_input(x) {
            return "<div class=\"text-center\"><input type=\"text\" value=\""+x+"\" disabled name=\"code[]\" style=\"width:100%;\" class=\"form-control input-sm code\"></div>";
        }
		
		function name_input(x) {
            return "<div class=\"text-center\"><input type=\"text\" value=\""+x+"\" disabled name=\"name[]\" style=\"width:100%;\" class=\"form-control input-sm name\"></div>";
        }
		
		function price_input(x) {
            return "<div class=\"text-center\"><input type=\"text\" value=\""+formatDecimal(x, 4)+"\" disabled name=\"price[]\" style=\"width:100%;\" class=\"form-control input-sm price\"></div>";
        }

        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getCategoryProducts/'.$id); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "";
                return nRow;
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, {"mRender": code_input}, {"mRender": name_input}, {"mRender": price_input}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 1, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
        ], "footer");
		
		$(".checkbox").live("ifChecked",function(){
			var tr = $(this).closest("tr");
			tr.find(".code").removeAttr("disabled");
			tr.find(".name").removeAttr("disabled");
			tr.find(".price").removeAttr("disabled");
		});
		
		$(".checkbox").live("ifUnchecked",function(){
			var tr = $(this).closest("tr");
			tr.find(".code").prop("disabled","disabled");
			tr.find(".name").prop("disabled","disabled");
			tr.find(".price").prop("disabled","disabled");
		});
		
		$("#CGData tbody tr").live("click",function(){
			var tr = $(this).closest("tr");
			tr.find(".checkbox").iCheck('check');
		});
		
    });
</script>
<?= form_open('system_settings/category_product_actions/'.$id, 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?> (<?= $category->name ?>)</h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="update_product" data-action="update_product">
                                <i class="fa fa-check-circle"></i> <?= lang('update_product') ?>
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
                            <th style="min-width:3%; width: 3%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
							<th class="col-xs-3"><?= lang("category"); ?></th>
                            <th class="col-xs-3"><?= lang("product_code"); ?></th>
                            <th class="col-xs-3"><?= lang("product_name"); ?></th>
							<th class="col-xs-3"><?= lang("price"); ?></th>
                            <th style="width:3%;"><?= lang("update"); ?></th>
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
        
        $('#update_product').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });
    });
</script>
