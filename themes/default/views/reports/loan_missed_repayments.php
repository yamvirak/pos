<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
    $v = "";
    if ($this->input->post('reference_no')) {
        $v .= "&reference_no=" . $this->input->post('reference_no');
    }
    if ($this->input->post('customer')) {
        $v .= "&customer=" . $this->input->post('customer');
    }
    if ($this->input->post('biller')) {
        $v .= "&biller=" . $this->input->post('biller');
    }
    if ($this->input->post('project')) {
        $v .= "&project=" . $this->input->post('project');
    }
    if ($this->input->post('warehouse')) {
        $v .= "&warehouse=" . $this->input->post('warehouse');
    }
    if ($this->input->post('user')) {
        $v .= "&user=" . $this->input->post('user');
    }
    if ($this->input->post('start_date')) {
        $v .= "&start_date=" . $this->input->post('start_date');
    }
    if ($this->input->post('end_date')) {
        $v .= "&end_date=" . $this->input->post('end_date');
    }
?>
<script>
    $(document).ready(function () {
        oTable = $('#LTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getLoanMissedRepayments?v=1&'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"sClass":"center"}, 
                {"mRender":fsd , "sClass":"center"}, 
                {"sClass":"center"}, 
                {"sClass":"center"}, 
                {"sClass":"center"}, 
                {"sClass":"left"}, 
                {"mRender":currencyFormat},
                {"mRender":currencyFormat},
                {"mRender":currencyFormat},
                {"mRender":currencyFormat},
                {"mRender":currencyFormat},
                {"mRender":currencyFormat},
                {"mRender":currencyFormat},
                {"mRender":currencyFormat},
                {"mRender":fld, "sClass":"center"},
                {"mRender":row_status}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                 var payment = 0, interest = 0,  principal= 0, payment_paid = 0,interest_paid =0, principal_paid = 0, penalty_paid = 0;
                for (var i = 0; i < aaData.length; i++) {
                    payment += parseFloat(aaData[aiDisplay[i]][6]);
                    interest += parseFloat(aaData[aiDisplay[i]][7]);
                    principal += parseFloat(aaData[aiDisplay[i]][8]);
                    
                    payment_paid += parseFloat(aaData[aiDisplay[i]][10]);
                    interest_paid += parseFloat(aaData[aiDisplay[i]][11]);
                    principal_paid += parseFloat(aaData[aiDisplay[i]][12]);
                    penalty_paid += parseFloat(aaData[aiDisplay[i]][13]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(payment));
                nCells[7].innerHTML = currencyFormat(parseFloat(interest));
                nCells[8].innerHTML = currencyFormat(parseFloat(principal));
                nCells[10].innerHTML = currencyFormat(parseFloat(payment_paid));
                nCells[11].innerHTML = currencyFormat(parseFloat(interest_paid));
                nCells[12].innerHTML = currencyFormat(parseFloat(principal_paid));
                nCells[13].innerHTML = currencyFormat(parseFloat(penalty_paid));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[#]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('deadline');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 14, filter_default_label: "[<?=lang('payment_date');?>]", filter_type: "text", data: []},
            {column_number: 15, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        
    });
</script>

<?=form_open('reports/loan_missed_repayments_report', 'id="action-form"');?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('loan_missed_repayments_report'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                
                <li class="dropdown">
                    <a href="#" onClick="window.print(); return false;" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("reference_no", "reference_no"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
            
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <?php if($Settings->project == 1){ ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("project", "project"); ?>
                                    <div class="no-project">
                                        <?php
                                        $pj[''] = '';
                                        if (isset($projects) && $projects != false) {
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
                                        echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <div class="input-group input-append">
                                    <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <div class="input-group input-append">
                                        <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> 
                            <?php echo form_submit('submit_report', $this->lang->line("Search"), 'class="btn btn-primary"'); ?> 
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>

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
                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('loan_payment_late_report_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('loan_missed_repayments_report')?>
                                </div><br>
                               
                            </td> 
                        </tr>
                </table>
                
                <table class="header_report_side" border="0" style="margin-bottom:20px;">
                    <tr style="width: 30%!important;">
                        <?php
                            if ($this->input->post('start_date')) {
                                echo '<td style="width:25%;">'.lang('from_date').'</td><td style=width:10px;>:</td><td class="header_report">'.$this->input->post('start_date') . " to " . $this->input->post('end_date').'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('from_date').'</td><td style=width:10px;>:</td><td class="header_report">'. $this->cus->hrsd(date("Y-m-d")) . " to " . $this->cus->hrsd(date("Y-m-d")).'</td>';
                            }
                        ?>           
                    </tr>
                    <tr style="width: 30%!important;">
                        <?php 
                            $biller_id = (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller);
                            $biller_id_all = lang('all_selected');
                            $biller_id_detail = $this->site->getCompanyByID($biller_id);
                            if($biller_id_detail){
                                echo '<td style="width:25%;">'.lang('inv_branch').'</td><td style=width:10px;>:</td><td class="header_report">'.$biller_id_detail->name.'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('inv_branch').'</td style=width:10px;><td>:</td><td class="header_report">'.$biller_id_all.'</td>';
                                        }
                           
                        ?>
                    </tr>

                     <tr style="width: 30%!important;">
                        <?php 
                            $project = (isset($_POST['project']) ? $_POST['project'] : false);
                            $project_all = lang('all_selected');
                            $project_detail = $this->site->getProjectByID($project);
                            if($project_detail){
                                echo '<td style="width:25%;">'.lang('project_name').'</td><td style=width:10px;>:</td><td class="header_report">'.$project_detail->name.'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('project_name').'</td style=width:10px;><td>:</td><td class="header_report">'.$project_all.'</td>';
                                        }
                           
                        ?>
                    </tr>
                    <tr style="width: 30%!important;">
                        <?php 
                            $customer = (isset($_POST['customer']) ? $_POST['customer'] : false);
                            $customer_all = lang('all_selected');
                            $customer_detail = $this->site->getCompanyByID($customer);
                            if($customer_detail){
                                echo '<td style="width:25%;">'.lang('customer_name').'</td><td style=width:10px;>:</td><td class="header_report">'.$customer_detail->name.'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('customer_name').'</td style=width:10px;><td>:</td><td class="header_report">'.$customer_all.'</td>';
                                        }
                           
                        ?>
                    </tr>
                    <tr style="width: 30%!important;">
                        <?php 
                            $warehouse = (isset($_POST['warehouse']) ? $_POST['warehouse'] : false);
                            $warehouse_all = lang('all_selected');
                            $warehouse_detail = $this->site->getWarehouseByID($warehouse);
                            if($warehouse_detail){
                                echo '<td style="width:25%;">'.lang('warehouse_name').'</td><td style=width:10px;>:</td><td class="header_report">'.$warehouse_detail->name.'</td>';
                            }else{
                                echo '<td style="width:25%;">'.lang('warehouse_name').'</td style=width:10px;><td>:</td><td class="header_report">'.$warehouse_all.'</td>';
                                        }
                           
                        ?>
                    </tr>
                   
                    
                </table>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="LTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th width='80'>#</th>
                            <th width='180'><?= lang("deadline") ?></th>
                            <th width='180'><?= lang("reference_no") ?></th>
                            <th width='180'><?= lang("biller") ?></th>
                            <th width='180'><?= lang("customer") ?></th>
                            <th width='180'><?= lang("phone") ?></th>
                            <th width='180'><?= lang("payment") ?></th>
                            <th width='180'><?= lang("interest") ?></th>
                            <th width='180'><?= lang("principal") ?></th>
                            <th width='180'><?= lang("balance") ?></th>
                            <th width='150'><?= lang("payment") ?><br/><small>( <?= lang('paid') ?> )</small></th>
                            <th width='150'><?= lang("interest") ?><br/><small>( <?= lang('paid') ?> )</small></th>
                            <th width='150'><?= lang("principal") ?><br/><small>( <?= lang('paid') ?> )</small></th>
                            <th width='150'><?= lang("penalty") ?><br/><small>( <?= lang('paid') ?> )</small></th>
                            <th width='140'><?= lang("payment_date") ?></th>
                            <th width='180'><?= lang("status") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
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
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>

                    <div style="margin-top: 50px !important;"></div>
                        <table width="100%" style="text-align:center;"> 
                          <tbody>
                            <tr class="tr_print">
                                <td>
                                    <table style="margin-top:<?= $margin_signature ?>px; margin-bottom:<?= $margin_signature -20 ?>px;">
                                        <thead class="footer_item">
                                            <th class="text_center"><?= lang("prepared_by");?></th>
                                            <th class="text_center"><?= lang("checked_by");?></th>
                                            <th class="text_center"><?= lang("approved_by");?></th>
                                            <th class="text_center"><?= lang("acknowledgement_by") ?></th>
                                        </thead>
                                        <tbody class="footer_item_body">
                                            <td class="footer_item_body"></td>
                                            <td class="footer_item_body"></td>
                                            <td class="footer_item_body"></td>
                                            <td class="footer_item_body"></td>
                                        </tbody>

                                        <thead class="footer_item_footer">
                                            <th class="footer_item_footer text_left">
                                                <div class="footer_name"><?= lang('name_date')?></div>
                                            </th>
                                            <th class="footer_item_footer text_left">
                                            <div class="footer_name"><?= lang('name_date')?></div>
                                            </th>
                                            <th class="footer_item_footer text_left">
                                                <div class="footer_name"><?= lang('name_date')?></div>
                                            </th>
                                            <th class="footer_item_footer text_left">
                                                <div class="footer_name"><?= lang('name_date')?></div>
                                                        
                                            </th>
                                        </thead>
                                    </table>
                                </td>
                                </tr>
                            </tbody>
                       </table>

                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        
        var customer_id = "<?= isset($_POST['customer'])?$_POST['customer']:0 ?>";
        if (customer_id > 0) {
          $('#customer_id').val(customer_id).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
              $.ajax({
                type: "get", async: false,
                url: site.base_url+"customers/getCustomer/" + $(element).val(),
                dataType: "json",
                success: function (data) {
                  callback(data[0]);
                }
              });
            },
            ajax: {
              url: site.base_url + "customers/suggestions",
              dataType: 'json',
              deietMillis: 15,
              data: function (term, page) {
                return {
                  term: term,
                  limit: 10
                };
              },
              results: function (data, page) {
                if (data.results != null) {
                  return {results: data.results};
                } else {
                  return {results: [{id: '', text: 'No Match Found'}]};
                }
              }
            }
          });
        }else{
          $('#customer_id').select2({
            minimumInputLength: 1,
            ajax: {
              url: site.base_url + "customers/suggestions",
              dataType: 'json',
              quietMillis: 15,
              data: function (term, page) {
                return {
                  term: term,
                  limit: 10
                };
              },
              results: function (data, page) {
                if (data.results != null) {
                  return {results: data.results};
                } else {
                  return {results: [{id: '', text: 'No Match Found'}]};
                }
              }
            }
          });
        }
        
        
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getLoanMissedRepayments/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getLoanMissedRepayments/0/xls/?v=1'.$v)?>";
            return false;
        });
        
        $("#biller").change(biller); biller();
        
        function biller(){
            var biller = $("#biller").val();
            var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
            $.ajax({
                url : "<?= site_url("reports/get_project") ?>",
                type : "GET",
                dataType : "JSON",
                data : { biller : biller, project : project },
                success : function(data){
                    if(data){
                        $(".no-project").html(data.result);
                        $("#project").select2();
                    }else{
                        
                    }
                }
            })
        }
        
    });
</script>