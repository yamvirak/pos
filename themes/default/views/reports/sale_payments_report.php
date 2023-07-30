<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
$v = "";
if ($this->input->post('paid_by')) {
    $v .= "&paid_by=" . $this->input->post('paid_by');
}
if ($this->input->post('sale_ref')) {
    $v .= "&sale_ref=" . $this->input->post('sale_ref');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('project')) {
    $v .= "&project=" . $this->input->post('project');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
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
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}

if ($this->input->post('subproject')) {
    $v .= "&subproject=" . $this->input->post('subproject');
}

?>
<script>
    $(document).ready(function () {
        oTable = $('#SalePaymentsData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getSalePaymentsReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, null, {"mRender": fld},null,null, null,null, {"mRender": currencyFormat}, {"mRender": currencyFormat},{"bSortable": false,"mRender": attachment}, {"mRender": row_status}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                if(aData[11]=='deposited'){
                    nRow.className = "so_deposit_link";
                }else if(aData[11]=='returned'){
                    nRow.className = "payment_link warning";
                }else{
                    nRow.className = "payment_link";
                }
                nRow.id = aData[12];
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var amount = 0, discount = 0;
                for (var i = 0; i < aaData.length; i++) {
                    amount += parseFloat(aaData[aiDisplay[i]][8]);
                    discount += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[8].innerHTML = currencyFormat(parseFloat(amount));
                nCells[9].innerHTML = currencyFormat(parseFloat(discount));
            }
        })
        .fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('sale_ref');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('sale_date');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>

<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('sale_payments_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            } ?>
        </h2> -->
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
        <div class="sub_menu">
            <a href="#" class="toggle_down tip btn btn-info btn-block box_sub_menu" title="<?= lang('show_form') ?>">
                <i class="icon fa fa-eye"></i>&nbsp;</i><?=lang('show_form')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="toggle_up tip btn btn-danger btn-block box_sub_menu" title="<?= lang('hide_form') ?>">
                <i class="icon fa fa-eye-slash"></i>&nbsp;</i><?=lang('hide_form')?>
            </a>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-money tip"></i><?= lang('sale_payments_report'); ?></h2>
                </li>

                <!-- <li class="dropdown">
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
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" onclick="window.print(); return false;" id="print" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li> -->
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('customize_report'); ?></p> -->
                <div id="form">
                    <?php echo form_open("reports/sale_payments_report"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="rbiller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[''] = lang("select")." ".lang("biller");
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="rbiller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
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

                        <div class="col-sm-4 hidden">
                            <div class="form-group all">
                                <?= lang("block", "block") ?>
                                <div class="controls" id="subproject_data"> <?php
                                    echo form_input('subproject',(isset($_POST['subproject']) ? $_POST['subproject'] : ""), 'class="form-control" id="subproject"  placeholder="' . lang("select_block_to_load") . '"');
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                            <?=lang("paid_by", "paid_by");?>
                                <select name="paid_by" id="paid_by" class="form-control paid_by">
                                    <?= $this->cus->cash_opts_report($this->input->post('paid_by') ? $this->input->post('paid_by') : 0, false, true); ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("sale_ref", "sale_ref"); ?>
                                <?php echo form_input('sale_ref', (isset($_POST['sale_ref']) ? $_POST['sale_ref'] : ""), 'class="form-control tip" id="sale_ref"'); ?>

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
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <div class="input-group input-append">
                                    <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <div class="input-group input-append">
                                        <span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
                <table style="margin-top: 5px; width:100%;">
                    <th>
                        <tr> 
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

                                <div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">
                                        <?= lang('sale_payments_report_kh')?>
                                </div> 
                                <div class="bold">
                                        <?= lang('sale_payments_report_en')?>
                                </div><br>
                            </td> 
                        </tr>
                </table>
                <div class="table-responsive">
                    <table id="SalePaymentsData"class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                            <tr>
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("reference_no"); ?></th>
                                <th><?= lang("sale_ref"); ?></th>
                                <th><?= lang("sale_date"); ?></th>
                                <th><?= lang("customer"); ?></th>
                                <th><?= lang("product_name"); ?></th>
                                <th><?= lang("created_by"); ?></th>
                                <th><?= lang("paid_by"); ?></th>
                                <th><?= lang("amount"); ?></th>
                                <th><?= lang("discount"); ?></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                <th><?= lang("type"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                    <table class="print_only" id="table_sinature">
                        <tr>
                            <td class="text-center bg_header" style="width:25%"><?= lang("prepared_by") ?></td>
                            <td class="text-center bg_header" style="width:25%"><?= lang("checked_by") ?></td>
                            <td class="text-center bg_header" style="width:25%"><?= lang("verified_by") ?></td>
                            <td class="text-center bg_header" style="width:25%"><?= lang("approved_by") ?></td>
                        </tr>
                        <tr>
                            <?php
                                $user = $this->site->getUserByID($this->session->userdata("user_id"));
                            ?>
                            <td style="height:120px; padding-left:5px; vertical-align: bottom !important">
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center" style="width:25%"><?= lang('name_date')?> / <?= $this->cus->hrsd(date("Y-m-d")) ?></td>
                            <td class="text-left" style="width:25%"><?= lang('name_date')?></td>
                            <td class="text-left" style="width:25%"><?= lang('name_date')?></td>
                            <td class="text-left" style="width:25%"><?= lang('name_date')?></td>
                        </tr>
                    </table>

            </div>
        </div>
    </div>
</div>

<style>
    @media print{    
        .dtFilter{
            display: table-footer-group !important;
        }
        #form{
            display:none !important;
        }
        .print_only{
            display:table !important;
        }
        table .td_biller{ 
            display:none !important;
        } 
        .exportExcel tr th{
            background-color : #428BCA !important;
            color : white !important;
        }
        @page{
            margin: 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
        }
        
    }
    .print_only{
        display:none;
    }
    #table_sinature{
        width:100%;
        margin-top:15px
    }
    #table_sinature td{
        border:1px solid black;
        padding: 7px;
    }
    .bg_header{
        background-color: rgb(0 0 0 / 8%);
    }

</style>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        
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
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getSalePaymentsReport/0/xls/?v=1'.$v)?>";
            return false;
        });
        // $("#rbiller").change(biller); biller();
        // function biller(){
        //     var biller = $("#rbiller").val();
        //     var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
        //     $.ajax({
        //         url : "<?= site_url("reports/get_project") ?>",
        //         type : "GET",
        //         dataType : "JSON",
        //         data : { biller : biller, project : project },
        //         success : function(data){
        //             if(data){
        //                 $(".no-project").html(data.result);
        //                 $("#project").select2();
        //             }
        //         }
        //     })
        // }
    });
</script>
<script type="text/javascript">

        $(document).ready(function () {
        $("#subproject").select2("destroy").empty().attr("placeholder", "<?= lang('select_project_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_project_to_load') ?>'}
            ]
        });
        $('#project').change(function () {
            var v = $(this).val();
            url: "<?= site_url('products/getSubProject') ?>/" + v,
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubProject') ?>/" + v,
                    dataType: "json",
                    async: true,
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#subproject").select2("destroy").empty().attr("placeholder", "<?= lang('select_block') ?>").select2({
                                placeholder: "<?= lang('select_block_to_load') ?>",
                                data: scdata
                            });
                        } else {
                            $("#subproject").select2("destroy").empty().attr("placeholder", "<?= lang('no_subproject') ?>").select2({
                                placeholder: "<?= lang('no_subproject') ?>",
                                data: [{id: '', text: '<?= lang('no_subproject') ?>'}]
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });

                
            } else {
                $("#subproject").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }


            $('#modal-loading').hide();
        });
        $('#code').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
    });

</script>
