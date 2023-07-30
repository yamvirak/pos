<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_nozzle_start_no') . " (" . $room->name . ")"; ?></h4>
        </div>

        <div class="modal-body">
            <div class="alerts-con"></div>
            <div class="table-responsive">
                <table id="FSData" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                    <tr class="primary">
                        <th class="col-xs-8"><?= lang("product"); ?></th>
                        <th class="col-xs-8"><?= lang("price"); ?></th>
                        <th style="width:25px;"><?= lang("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $modal_js ?>
<script type="text/javascript">
	$(document).ready(function () {
		$('.tip').tooltip();
		oTable = $('#FSData').dataTable({
			"aaSorting": [[1, "asc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= site_url('system_settings/getRoomProducts/'.$room->id) ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			"aoColumns": [null, {"mRender" : currencyFormat},{"bSortable": false}]
		});
		$('div.dataTables_length select').addClass('form-control');
		$('div.dataTables_length select').addClass('select2');
		$('div.dataTables_filter input').attr('placeholder', '');
		$('select.select2').select2({minimumResultsForSearch: 7});
	});
</script>