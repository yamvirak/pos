
<script>
    $(document).ready(function () {
        var oTable = $('#TaData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('rentals_configuration/getRoomRates') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            },
            null,
            null,
            null,
            null,
           {"mRender": currencyFormat},
            null,
            {"mRender":row_status}, {"bSortable": false}],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {              
				nRow.id = aData[0];
                nRow.className = "";
                return nRow;
            }
        }).dtFilter([		
            {column_number: 1, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('source_type');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('room_type');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('price');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php if ($Owner) {
    echo form_open('rentals_configuration/room_rates_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header box-header">
       <div class="action-header"> 
            <a href="<?php echo site_url('rentals_configuration/add_room_rates'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                <i class="fa fa-plus-circle"></i> <?= lang('add') ?>
            </a>
        </div>
        <div class="action-header"> 
            <a href="#" class="bpo" title="<b><?=lang("delete_")?></b>"
                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                    data-html="true" data-placement="left">
                <i class="fa fa-trash-o"></i> <?=lang('delete')?>
            </a>
        </div>
        <div class="action-header"> 
            <a href="#" id="excel" data-action="export_excel">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </a>
        </div>               
        <div class="box-icon">
            <ul class="btn-tasks">
                <h2 class="blue"><i class="fa fa-inbox"></i><?= lang('room_rate'); ?></h2>
				
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="TaData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:5%; width: 5%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th width="150px"><?= lang("code"); ?></th> 
                            <th width="150px"><?= lang("source_type"); ?></th>
                            <th width="150px"><?= lang("room_type"); ?></th>  							
                            <th width="150"><?= lang("name"); ?></th> 
                            <th><?= lang("price"); ?></th>
                            <th><?= lang("description"); ?></th> 
                            <th width="150px"><?= lang("status"); ?></th>                            
                            <th style="width:10%; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="datafloors_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <th></th>
                            <th>[<?= lang("actions"); ?>]</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>

