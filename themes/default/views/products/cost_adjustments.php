<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function () {
        oTable = $('#dmpData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getcostadjustments/'.($biller ? $biller->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, {"mRender": decode_html}, {"bSortable": false,"mRender": attachment}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "cost_adjustment_link";
                return nRow;
            },
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang(' note');?>]", filter_type: "text", data: []},
        ], "footer");

        if (localStorage.getItem('remove_cals')) {
            if (localStorage.getItem('caitems')) {
                localStorage.removeItem('caitems');
            }
            if (localStorage.getItem('caref')) {
                localStorage.removeItem('caref');
            }
            if (localStorage.getItem('canote')) {
                localStorage.removeItem('canote');
            }
            if (localStorage.getItem('canote')) {
                localStorage.removeItem('canote');
            }
            localStorage.removeItem('remove_cals');
        }

        <?php if ($this->session->userdata('remove_cals')) { ?>
            if (localStorage.getItem('caitems')) {
                localStorage.removeItem('caitems');
            }
            if (localStorage.getItem('caref')) {
                localStorage.removeItem('caref');
            }
            if (localStorage.getItem('qawarehouse')) {
                localStorage.removeItem('qawarehouse');
            }
            if (localStorage.getItem('canote')) {
                localStorage.removeItem('canote');
            }
        <?php $this->cus->unset_data('remove_cals');}
        ?>
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo form_open('products/cost_adjustment_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
		<!-- <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('cost_adjustments').' ('.($biller ? $biller->name : lang('all_billers')).')'; ?></h2> -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?= site_url('products/add_cost_adjustment') ?>" 
                class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang('add_cost_adjustment') ?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" 
                class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                 <i class="fa fa-upload"></i> <?= lang('add_cost_adjustment_excel') ?>
            </a>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                   <h2 class="blue"><i class="icon fa fa-barcode tip"></i><?= lang('cost_adjustments'); ?></h2>
                </li>	
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?= site_url('products/cost_adjustments') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
								<li class="divider"></li>
								<?php
									foreach ($billers as $biller) {
										echo '<li><a href="' . site_url('products/cost_adjustments/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
									}
								?>
							</ul>
						</li>
				<?php } ?>	
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-2"><?= lang("date"); ?></th>
                            <th class="col-xs-2"><?= lang("reference_no"); ?></th>
                            <th class="col-xs-2"><?= lang("created_by"); ?></th>
                            <th><?= lang("note"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?>

<script language="javascript">
    $(document).ready(function () {
        $('#add_cost_adjustment').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>
