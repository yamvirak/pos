<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
		oTable = $('#table_rentals').dataTable({
			"aaSorting": [[10, "asc"],[0, "desc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= site_url('rentals_housekeeping/getRentalsHousekeepings/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : 0).'/'.($payment_status ? $payment_status : '')); ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			"aoColumns": [
			{"mRender" : checkbox},
			{"mRender" : fld, "sClass":"center"}, 
			null,
			null, 
			null,
			null,
			{"sClass" : "text-center"},
			{"mRender" : fsd, "sClass":"center"}, 
			{"mRender" : fsd, "sClass":"center"},
			null,
			{"mRender": row_status}, 
			{"mRender" : attachment}, 
			{"bSortable": false}],
            
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {              
				nRow.id = aData[0];
                //nRow.className = "rental_link";
				var action = $('td:eq(13)', nRow);
				if(aData[11] == 'checked_out'){
					action.find('.rentals-create_sale').remove();
					action.find('.rentals-check_out').remove();
					action.find('.rentals-delete').remove();
					action.find('.rentals-edit').remove();
					action.find('.rentals-deposit').remove();
					action.find('.rentals-view_deposit').remove();
					action.find('.rentals-checked_in').add();
				}else if(aData[11] == 'reservation'){
					action.find('.rentals-create_sale').remove();
					action.find('.rentals-check_out').remove();
				}
				var now = new Date();
				var to_date = new Date(aData[7]);
				if(to_date <= now && aData[11] == 'checked_in'){
					nRow.className = "rental_link danger";
					action.find('.rentals-checked_in').remove();
				}
				if(to_date >= now && aData[11] == 'checked_in'){
					action.find('.rentals-create_sale').remove();
				}
				if(aData[9] <= 0 && aData[11] == 'checked_in'){
					nRow.className = "rental_link danger";
				}
				if(aData[9] <= 0){
					action.find('.rentals-return_deposit').remove();
				}
                return nRow;
            }
		}).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('assigned_to');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('room_type');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('room');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('order_date');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('finish_date');?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
		
        <?php if($this->session->userdata('remove_rtls')) { ?>
        if (localStorage.getItem('rtitems')) {
            localStorage.removeItem('rtitems');
        }
        if (localStorage.getItem('rtdiscount')) {
            localStorage.removeItem('rtdiscount');
        }
        if (localStorage.getItem('rttax2')) {
            localStorage.removeItem('rttax2');
        }
        if (localStorage.getItem('rtref')) {
            localStorage.removeItem('rtref');
        }
        if (localStorage.getItem('rtwarehouse')) {
            localStorage.removeItem('rtwarehouse');
        }
        if (localStorage.getItem('rtnote')) {
            localStorage.removeItem('rtnote');
        }
        if (localStorage.getItem('rtcustomer')) {
            localStorage.removeItem('rtcustomer');
        }
        if (localStorage.getItem('rtbiller')) {
            localStorage.removeItem('rtbiller');
        }
        if (localStorage.getItem('rtcurrency')) {
            localStorage.removeItem('rtcurrency');
        }
        if (localStorage.getItem('rtdate')) {
            localStorage.removeItem('rtdate');
        }
		if (localStorage.getItem('rtfloor')) {
            localStorage.removeItem('rtfloor');
        }
		if (localStorage.getItem('rtroom')) {
            localStorage.removeItem('rtroom');
        }
		if (localStorage.getItem('rtfrom_date')) {
            localStorage.removeItem('rtfrom_date');
        }
		if (localStorage.getItem('rtto_date')) {
            localStorage.removeItem('rtto_date');
        }
		if (localStorage.getItem('rtfrequency')) {
            localStorage.removeItem('rtfrequency');
        }
		if (localStorage.getItem('rtcontract_period')) {
            localStorage.removeItem('rtcontract_period');
        }
        if (localStorage.getItem('rtstatus')) {
            localStorage.removeItem('rtstatus');
        }
		if (localStorage.getItem('rtstaff_note')) {
			localStorage.removeItem('rtstaff_note');
		}
		
        <?php $this->cus->unset_data('remove_rtls'); } ?>
		
		<?php if(isset($_GET['sale_id'])){ ?>
			var sale_id = "<?= $_GET['sale_id'] ?>";
			$('#myModal2').modal({remote: site.base_url + 'sales/modal_view/' + sale_id, backdrop: 'static', keyboard: false});
			$('#myModal2').modal('show');
		<?php } ?>
		
    });
</script>
<?php if ($Owner || $Admin) {
    echo form_open('rentals_housekeeping/rental_actions', 'id="action-form"');
} ?>
<div class="box">
	<div class="box-header box-header">
       <div class="action-header"> 
            <a href="<?php echo site_url('rentals_housekeeping/add'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
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
                <h2 class="blue"><i class="fa fa-inbox"></i><?= lang('housekeeping_list'); ?></h2>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="table_rentals" cellpadding="0" cellspacing="0" border="0"
						   class="table table-bordered">
						<thead>
							<tr class="primary">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th style="width:150px;"><?= lang("date"); ?></th>
								<th style="width:150px;"><?= lang("reference_no"); ?></th>
								<th style="width:150px;"><?= lang("assigned_to"); ?></th>
								<th style="width:150px;"><?= lang("phone"); ?></th>
								<th style="width:150px;"><?= lang("room_type"); ?></th>
								<th style="width:150px;"><?= lang("room"); ?></th>
								<th style="width:150px;"><?= lang("order_date"); ?></th>
								<th style="width:150px;"><?= lang("finish_date"); ?></th>
								<th style="width:150px;"><?= lang("description"); ?></th>
								<th style="width:50px;"><?= lang("status"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th style="width:85px;"><?= lang("actions"); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
							</tr>
						</tbody>
						<tfoot>
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
								<th></th>
								<th></th>
								<th></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th></th>
							</tr>
                        </tfoot>
					</table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>