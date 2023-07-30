<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getProductPromotions') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "product_promotion_link";
                return nRow;
            },
			"aoColumns": [{"bVisible": false}, null, {"mRender" : fsd}, {"mRender" : fsd}, {"mRender" : row_status}, {"bSortable": false}]
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="<?= lang('add_product_promotion') ?>" class="tip" href="<?php echo site_url('system_settings/add_product_promotion'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                    <i class="icon fa fa-plus"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>

                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr>
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkth" type="checkbox" name="check"/>
								</th>
								<th><?php echo $this->lang->line("name"); ?></th>
                                <th><?php echo $this->lang->line("from_date"); ?></th>
                                <th><?php echo $this->lang->line("to_date"); ?></th>
                                <th><?php echo $this->lang->line("status"); ?></th>
								<th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>						
							 </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>


