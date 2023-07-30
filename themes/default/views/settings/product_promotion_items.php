<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#PRPData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= site_url('system_settings/getProductPromotionItems/'.$product_promotion->id); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
			"aoColumns": [{"bVisible": false}, null, null, null, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<div class="box">
	<div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?> (<?= $product_promotion->name; ?>)</h2>
	</div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>

                <div class="table-responsive">
                    <table id="PRPData" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr>
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkth" type="checkbox" name="check"/>
								</th>
								<th class="col-xs-3"><?= lang("category"); ?></th>
								<th class="col-xs-3"><?= lang("product_code"); ?></th>
								<th class="col-xs-4"><?= lang("product_name"); ?></th>
								<th><?= lang("actions"); ?></th>
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
								<th></th><th></th><th></th>
								<th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>


