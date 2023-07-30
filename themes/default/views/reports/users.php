<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#staffTable').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getUsers') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, null, null, null, {"mRender": user_status}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('first_name');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('last_name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('email');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
            {
                column_number: 5, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?=lang('status');?>',
                    width: '100%',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: '1', label: '<?=lang('active');?>'}, {value: '0', label: '<?=lang('inactive');?>'}]
            }
        ], "footer");
    });
</script>
<style>.table td:nth-child(6) {
        text-align: center;
    }</style>
<?php if ($Owner) {
    echo form_open('auth/user_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-users tip"></i><?= lang('users'); ?></h2>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('view_report_staff'); ?></p> -->

                <table style="margin-top: 5px; width:100%;">
                    <th>
                        <tr>  


                            <?php 
                                $biller_id = (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller);
                                $biller_id_all = lang('all_selected');
                                $biller_id_detail = $this->site->getCompanyByID($biller_id);
                                if($biller_id_detail){
                                ?>
                                <td class="text_left" style="width: 10%">
                                    <div>
                                        <?= !empty($biller_id_detail->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller_id_detail->logo).'" alt="">' : ''; ?>
                                    </div>
                                </td>
                                <td></td>
                                <td class="text_center" style="width:100%">
                                    <div>
                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller_id_detail->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller_id_detail->name;?></strong>
                                    </div>
                                <br>

                                <?php 
                                }else{
                                ?>

                                <td class="text_left" style="width: 10%">
                                    <div>
                                        <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                                    </div>
                                </td>
                                <td></td>
                                <td class="text_center" style="width:100%">
                                    <div>
                                        <strong style="font-size:22px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
                                    </div>
                                <br>

                                <?php } ?>
                    
                                <?php 
                                    $sale_type_id = (isset($_POST['sale_type_id']) ? $_POST['sale_type_id'] : false);
                                    $sale_type_id_all = lang('all_selected');
                                    //$sale_type_id_detail = $this->site->getSaleTypesByID($sale_type_id);
                                    if($sale_type_id == 1){
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('cash_monthly_report_kh').'</div>';
                                        echo '<div class="bold">'.lang('cash_monthly_report_en').'</div><br>';
                                    }elseif($sale_type_id == 2){
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('deposit_monthly_report_kh').'</div>';
                                        echo '<div class="bold">'.lang('deposit_monthly_report_en').'</div><br>';

                                    }elseif($sale_type_id == 3){
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('loan_monthly_report_kh').'</div>';
                                        echo '<div class="bold">'.lang('loan_monthly_report_en').'</div><br>';

                                    }else{
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('user_kh').'</div>';
                                        echo '<div class="bold">'.lang('user_en').'</div><br>';
                                }
                                   
                                ?>
                               
                            </td> 
                        </tr>
                </table>
                <div class="table-responsive">
                    <table id="staffTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                        <tr>
                            <th><?php echo lang('first_name'); ?></th>
                            <th><?php echo lang('last_name'); ?></th>
                            <th><?php echo lang('email'); ?></th>
                            <th><?php echo lang('company'); ?></th>
                            <th><?php echo lang('group'); ?></th>
                            <th style="width:100px;"><?php echo lang('status'); ?></th>
                            <th style="width:80px;"><?php echo lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:100px;"></th>
                            <th style="width:85px; text-align:center;"><?= lang("actions"); ?></th>
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

    <script language="javascript">
        $(document).ready(function () {
            $('#set_admin').click(function () {
                $('#usr-form-btn').trigger('click');
            });

        });

    </script>

<?php } ?>