<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style type="text/css" media="screen">
    <?php if($Settings->car_operation != 1) { ?>
        #SLData td:nth-child(6),
        #SLData td:nth-child(7),
        #SLData td:nth-child(8),
        #SLData td:nth-child(9) {
            display: none !important;
        }
        #SLData th:nth-child(6),
        #SLData th:nth-child(7),
        #SLData th:nth-child(8),
        #SLData th:nth-child(9) {
            display: none !important;
        }
    <?php } ?>
    
    <?php if($this->config->item("deliveries") != true) { ?>
        #SLData td:nth-child(15) {
            display: none !important;
        }
        #SLData th:nth-child(15) {
            display: none !important;
        }
    <?php } ?>
</style>

<script>
    $(document).ready(function () {
        oTable = $('#SLData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('sales/getSales/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : 0).'/'.($payment_status ? $payment_status : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[15]);
                var action = $('td:eq(16)', nRow);
                if(aData[10] > 0){
                    nRow.className = "invoice_link re warning"+aData[15]+" warning";
                }else{
                    nRow.className = "invoice_link re"+aData[11];
                }
                if(aData[18] > 0){
                    action.find('.add_payment').remove();
                    action.find('.down_payment').remove();
                    action.find('.view_down_payment').remove();
                    action.find('.add_installment').remove();
                    nRow.className = "invoice_link danger";
                }
                
                if(aData[15] == 'completed'){
                    action.find('.add_delivery').remove();
                }else if(aData[20] == 0){
                    action.find('.add_return').remove();
                }
                if(aData[16] == 'paid'){
                    action.find('.add_payment').remove();
                }
                return nRow;
            },
            'bStateSave': true,
            'fnStateSave': function (oSettings, oData) {
                localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
            },
            'fnStateLoad': function (oSettings) {
                var data = localStorage.getItem('DataTables_' + window.location.pathname);
                return JSON.parse(data);
            },
            "search": {
                "caseInsensitive": false
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null, null,null,null,null,null, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat},{"mRender": row_status}, {"mRender": pay_status, "bSortable" : false}, {"bSortable": false,"mRender": attachment}, {"bVisible": false}, {"bVisible": false},  {"bVisible": false},{"bVisible": false}, {"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, rtotal = 0, paid = 0, discount=0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][9]);
                    rtotal += parseFloat(aaData[aiDisplay[i]][10]);
                    paid += parseFloat(aaData[aiDisplay[i]][11]);
                    discount += parseFloat(aaData[aiDisplay[i]][12]);
                    balance += parseFloat(aaData[aiDisplay[i]][13]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[9].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[10].innerHTML = currencyFormat(parseFloat(rtotal));
                nCells[11].innerHTML = currencyFormat(parseFloat(paid));
                nCells[12].innerHTML = currencyFormat(parseFloat(discount));
                nCells[13].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('vehicle_model');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('vehicle_plate');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('vehicle_vin');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('mechanic');?>]", filter_type: "text", data: []},
            {column_number: 14, filter_default_label: "[<?=lang('delivery_status');?>]", filter_type: "text", data: []},
            {column_number: 15, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");

        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            if (localStorage.getItem('slsaleman')) {
                localStorage.removeItem('slsaleman');
            }
            if (localStorage.getItem('stock_deduction')) {
                localStorage.removeItem('stock_deduction');
            }
            if (localStorage.getItem('slroom')) {
                localStorage.removeItem('slroom');
            }
            localStorage.removeItem('remove_slls');
        }

        <?php if ($this->session->userdata('remove_slls')) {?>
        if (localStorage.getItem('slitems')) {
            localStorage.removeItem('slitems');
        }
        if (localStorage.getItem('sldiscount')) {
            localStorage.removeItem('sldiscount');
        }
        if (localStorage.getItem('sltax2')) {
            localStorage.removeItem('sltax2');
        }
        if (localStorage.getItem('slref')) {
            localStorage.removeItem('slref');
        }
        if (localStorage.getItem('slshipping')) {
            localStorage.removeItem('slshipping');
        }
        if (localStorage.getItem('slwarehouse')) {
            localStorage.removeItem('slwarehouse');
        }
        if (localStorage.getItem('slnote')) {
            localStorage.removeItem('slnote');
        }
        if (localStorage.getItem('slinnote')) {
            localStorage.removeItem('slinnote');
        }
        if (localStorage.getItem('slcustomer')) {
            localStorage.removeItem('slcustomer');
        }
        if (localStorage.getItem('slbiller')) {
            localStorage.removeItem('slbiller');
        }
        if (localStorage.getItem('slcurrency')) {
            localStorage.removeItem('slcurrency');
        }
        if (localStorage.getItem('sldate')) {
            localStorage.removeItem('sldate');
        }
        if (localStorage.getItem('slsale_status')) {
            localStorage.removeItem('slsale_status');
        }
        if (localStorage.getItem('slpayment_status')) {
            localStorage.removeItem('slpayment_status');
        }
        if (localStorage.getItem('paid_by')) {
            localStorage.removeItem('paid_by');
        }
        if (localStorage.getItem('amount_1')) {
            localStorage.removeItem('amount_1');
        }
        if (localStorage.getItem('paid_by_1')) {
            localStorage.removeItem('paid_by_1');
        }
        if (localStorage.getItem('pcc_holder_1')) {
            localStorage.removeItem('pcc_holder_1');
        }
        if (localStorage.getItem('pcc_type_1')) {
            localStorage.removeItem('pcc_type_1');
        }
        if (localStorage.getItem('pcc_month_1')) {
            localStorage.removeItem('pcc_month_1');
        }
        if (localStorage.getItem('pcc_year_1')) {
            localStorage.removeItem('pcc_year_1');
        }
        if (localStorage.getItem('pcc_no_1')) {
            localStorage.removeItem('pcc_no_1');
        }
        if (localStorage.getItem('cheque_no_1')) {
            localStorage.removeItem('cheque_no_1');
        }
        if (localStorage.getItem('slpayment_term')) {
            localStorage.removeItem('slpayment_term');
        }
        if (localStorage.getItem('slsaleman')) {
            localStorage.removeItem('slsaleman');
        }
        if (localStorage.getItem('stock_deduction')) {
            localStorage.removeItem('stock_deduction');
        }
        if (localStorage.getItem('slroom')) {
            localStorage.removeItem('slroom');
        }
        <?php $this->cus->unset_data('remove_slls');}?>

        $(document).on('click', '.sledit', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
        
    });

</script>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {
        echo form_open('sales/sale_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('sales').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2> -->
        <div class="sub_menu"></div>
            <div class="sub_menu">
            <a href="<?=site_url('sales/add')?>" 
                class="btn btn-success btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?=lang('add_sale')?>
            </a>
        </div>
        <?php if ($Owner || $Admin || $GP['sales-import_sale']) { ?>
            <div class="sub_menu">
                <a href="<?=site_url('sales/import_sale')?>" 
                    class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                    <i class="fa fa-plus-circle"></i> <?=lang('import_sale')?>
                </a>
            </div>
        <?php } ?>
        <div class="sub_menu">
            <a href="#" id="excel" data-action="export_excel" 
                class="btn btn-primary btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
            </a>
        </div>
        <div id="payment_box" class="sub_menu">
            <a href="javascript:void(0)" id="multi_payment" data-action="multi_payment" 
                class="btn btn-info btn-block box_sub_menu" tabindex="-1">
                <i class="fa fa-money"></i> <?=lang('add_payment')?>
            </a>
        </div>
        <div class="sub_menu">
            <a href="#" class="bpo btn btn-danger btn-block box_sub_menu" tabindex="-1"
            title="<?=lang("delete_sales")?>"
            data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
            data-html="true" data-placement="left">
            <i class="fa fa-trash-o"></i> <?=lang('delete_sales')?>
            </a>
        </div>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-line-chart"></i><?= lang('sales'); ?></h2>
                </li>
                 <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('sales/') ?>"><i class="icon fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('sales/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                
                <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('sales/index') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . site_url('sales/index/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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

                <!-- <p class="introtext"><?=lang('list_results');?></p> -->

                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("description"); ?></th>
                            <th><?= lang("vehicle_model"); ?></th>
                            <th><?= lang("vehicle_plate"); ?></th>
                            <th><?= lang("vehicle_vin"); ?></th>
                            <th><?= lang("mechanic"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("returned"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("discount"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("delivery_status"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="18" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th><th></th><th></th><th></th>
                            <th></th><th></th><th></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("returned"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("discount"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                            <th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
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


<div id="assign"></div>

<script type="text/javascript">
    $(function(){
        $("#SLData").css("width", "auto");  
        $("#assign_to").on("click",function(){
            
            var assign_to = [];
            if($(".multi-select:checked").length > 0){
                
                $(".multi-select:checked").each(function(){
                    var multi = $(this).val();
                    assign_to.push(multi);
                });
                
            }else{
                alert("<?= lang("select_above") ?>");
                return false;
            }           
            $.ajax({
                url : "<?= site_url("sales/assign_to") ?>",
                data : { assign_to : assign_to },
                type : "GET",
                dataType : "JSON",
                success : function(data){                   
                    $('#assign').html(data);                    
                },
                error:function(e){                  
                    addAlert(e.responseText, 'danger');
                }
            });
        }); 

        $('#multi_payment').live('click',function(){
            var sale_id = '';
            var intRegex = /^\d+$/;
            var i = 0;
            $('.input-xs').each(function(){
                if ($(this).is(':checked') && intRegex.test($(this).val())) {
                    if(i==0){
                        sale_id += $(this).val();
                        i=1;
                    }else{
                        sale_id += "SaleID"+$(this).val();
                    }
                    
                }
            });
            if(sale_id==''){
                alert("<?= lang('no_sale_selected') ?>")
                return false;
            }else{
                var link = '<?= anchor('sales/add_multi_payment/#######', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="multi_payment"')?>';
                var add_payment_link = link.replace("#######", sale_id);
                $("#payment_box").html(add_payment_link);
                $('.multi_payment').click();
                $("#payment_box").html('<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment"><i class="fa fa-money"></i> <?=lang('add_payment')?></a>');     
                return false;
            }
        });

        /*==========ASSIGN SALEMAN==========*/
        
        $("#assign_saleman").on("click",function(){
            
            var assign_saleman = [];
            if($(".multi-select:checked").length > 0){
                
                $(".multi-select:checked").each(function(){
                    var multi = $(this).val();
                    assign_saleman.push(multi);
                });
                
            }else{
                alert("<?= lang("select_above") ?>");
                return false;
            }           
            $.ajax({
                url : "<?= site_url("sales/assign_saleman") ?>",
                data : { assign_saleman : assign_saleman },
                type : "GET",
                dataType : "JSON",
                success : function(data){                   
                    $('#assign').html(data);                    
                },
                error:function(e){                  
                    addAlert(e.responseText, 'danger');
                }
            });
        });

        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
        

            
    });
</script>

