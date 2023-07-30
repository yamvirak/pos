<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function () {
        oTable = $('#dmpData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getBomItems/'.($biller ? $biller->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
			{"mRender": checkbox}, 
			{"mRender": fld}, 
			null,
			null,
			null,
			{"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "";
                return nRow;
            },
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
        ], "footer");

        if (localStorage.getItem('remove_bols')) {
            if (localStorage.getItem('boitemsFr')) {
				localStorage.removeItem('boitemsFr');
			}
			 if (localStorage.getItem('boitemsTo')) {
				localStorage.removeItem('boitemsTo');
			}
			if (localStorage.getItem('boshipping')) {
				localStorage.removeItem('boshipping');
			}
			if (localStorage.getItem('boref')) {
				localStorage.removeItem('boref');
			}
			if (localStorage.getItem('bonote')) {
				localStorage.removeItem('bonote');
			}
			if (localStorage.getItem('bowarehouse')) {
				localStorage.removeItem('bowarehouse');
			}
			if (localStorage.getItem('bodate')) {
				localStorage.removeItem('bodate');
			}
			if (localStorage.getItem('bostatus')) {
				localStorage.removeItem('bostatus');
			}
			
			if (localStorage.getItem('bo_to_warehouse')) {
				localStorage.removeItem('bo_to_warehouse');
			}
	
			if (localStorage.getItem('bo_from_warehouse')) {
				localStorage.removeItem('bo_from_warehouse');
			}
			if (localStorage.getItem('todate')) {
				localStorage.removeItem('todate');
			}

			
            localStorage.removeItem('remove_bols');
        }

        <?php if ($this->session->userdata('remove_bols')) { ?>
            if (localStorage.getItem('boitemsFr')) {
				localStorage.removeItem('boitemsFr');
			}
			 if (localStorage.getItem('boitemsTo')) {
				localStorage.removeItem('boitemsTo');
			}
			if (localStorage.getItem('boshipping')) {
				localStorage.removeItem('boshipping');
			}
			if (localStorage.getItem('boref')) {
				localStorage.removeItem('boref');
			}
			if (localStorage.getItem('bonote')) {
				localStorage.removeItem('bonote');
			}
			if (localStorage.getItem('bowarehouse')) {
				localStorage.removeItem('bowarehouse');
			}
			if (localStorage.getItem('bodate')) {
				localStorage.removeItem('bodate');
			}
			if (localStorage.getItem('bostatus')) {
				localStorage.removeItem('bostatus');
			}
			
			if (localStorage.getItem('bo_to_warehouse')) {
				localStorage.removeItem('bo_to_warehouse');
			}
	
			if (localStorage.getItem('bo_from_warehouse')) {
				localStorage.removeItem('bo_from_warehouse');
			}
			if (localStorage.getItem('todate')) {
				localStorage.removeItem('todate');
			}
        <?php $this->cus->unset_data('remove_bols');}
        ?>
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo form_open('products/bom_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('boms').' ('.($biller ? $biller->name : lang('all_billers')).')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('products/add_bom') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_bom') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        
						<li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_boms") ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_boms') ?>
                             </a>
                         </li>
                    </ul>
                </li>
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= site_url('products/boms') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . site_url('products/boms/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-2"><?= lang("date"); ?></th>
                            <th class="col-xs-2"><?= lang("name"); ?></th>
                            <th class="col-xs-2"><?= lang("created_by"); ?></th>
                            <th><?= lang("note"); ?></th>
                            <th style="min-width:75px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th>
                            <th></th>
                            <th style="width:75px; text-align:center;"><?= lang("actions"); ?></th>
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
