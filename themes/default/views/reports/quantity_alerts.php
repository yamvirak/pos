<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#PQData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getQuantityAlerts' . ($warehouse_id ? '/' . $warehouse_id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": img_hl
            }, null, null, {"mRender": text_right}, {"mRender": text_right}, {"mRender": text_right}],
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('quantity');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('alert_quantity');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('quantity_to_add');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="javascript:;" onclick="window.print();" id ="print" 
                class="tip btn btn-success btn-block box_sub_menu" title="<?= lang('print') ?>">
                <i class="icon fa fa-file-fa fa-print">&nbsp;</i><?=lang('print')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" id="xls" class="tip btn btn-warning btn-block box_sub_menu" title="<?= lang('download_xls') ?>">
                <i class="icon fa fa-file-excel-o"></i>&nbsp;</i><?=lang('download_xls')?>
            </a>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue">
                        <i class="fa-fw fa fa-calendar-o tip icon"></i><?= lang('product_quantity_alerts') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
                    </h2>   
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i>
                        </a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li>
                                <a href="<?= site_url('reports/quantity_alerts') ?>">
                                    <i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/quantity_alerts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <!-- <div class="box-icon">
            <ul class="btn-tasks">
                
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                
            </ul>
        </div> -->
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->

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
							<td></td>
							<td class="text_center" style="width:100%">
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
										echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('product_quantity_alerts_kh').'</div>';
										echo '<div class="bold">'.lang('product_quantity_alerts_en').'</div><br>';
								}
								?>
							
							</td> 
                        </tr>
					</th>
                </table>

                <div class="table-responsive">
                    <table id="PQData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped dfTable reports-table">
                        <thead>
                        <tr class="active">
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th><?php echo $this->lang->line("product_code"); ?></th>
                            <th><?php echo $this->lang->line("product_name"); ?></th>
                            <th><?php echo $this->lang->line("quantity"); ?></th>
                            <th><?php echo $this->lang->line("alert_quantity"); ?></th>
							<th><?php echo $this->lang->line("quantity_to_add"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
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
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/pdf')?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getQuantityAlerts/'.($warehouse_id ? $warehouse_id : '0').'/0/xls')?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>
