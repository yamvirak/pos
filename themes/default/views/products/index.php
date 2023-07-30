<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">
    <?php if($this->Settings->show_warehouse_qty) { ?>
        #PRData td:nth-child(12) {
            text-align: right;
        }
        #PRData td:nth-child(11) {
            text-align: right;
        }
        #PRData td:nth-child(10) {
            text-align: right;
        }   
    <?php } else { ?>
        #PRData td:nth-child(10) {
            text-align: right;
        }
    <?php } ?>
</style>
<?php

    $warehouse_header = '';
    $warehouse_footer = '';
    $warehouse_value = '';
    if ($this->Settings->show_warehouse_qty) {
        $warehouses = $this->site->getAllWarehouses();
        if($warehouses){
            foreach($warehouses as $warehoused){
                $warehouse_value  .='null,';
                $warehouse_header .= '<th>'.$warehoused->name.'</th>';
                $warehouse_footer .= '<th></th>';
            }
        }
    }
?>
<script>
    function JSConvertQty(product_qty){
        product_qty = product_qty.split("|");
        var product_id = product_qty[1];
        var quantity = formatDecimalRaw(product_qty[0]);
        var product_units = <?= $product_units ?>;
        if(product_units[product_id]){
            var unit_string = '';
            var i = 1;
            var operation = '';
            if(quantity < 0){
                quantity = quantity * (-1);
                operation = '-';
            }
            if(quantity < 1){
                return quantity;
            }
            $.each(product_units[product_id], function () {
                if(quantity >= this.unit_qty){
                    if(i > 1){
                        unit_string += ', ';
                    }
                    if(this.unit_qty == 1){
                        var quantity_unit = quantity / this.unit_qty;
                    }else{
                        var quantity_unit = parseInt(quantity / this.unit_qty);
                    }

                    unit_string += formatQuantity2(quantity_unit)+' <span style="color:#357EBD;">'+this.unit_name+'</span>';
                    quantity = quantity - (quantity_unit * this.unit_qty);
                    i++;
                }
            });
            return operation+''+unit_string;
        }else{
            return quantity;
        }
    }


    var oTable;
    $(document).ready(function () {
        oTable = $('#PRData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getProducts'.($warehouse_id ? '/'.$warehouse_id : '').($supplier ? '?supplier='.$supplier->id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "product_link";
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
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
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, {"bSortable": false,"mRender": img_hl}, null, null, null, null, null, <?php if($Owner || $Admin) { echo '{"mRender": currencyFormat}, {"mRender": currencyFormat},'; } else { if($this->session->userdata('show_cost')) { echo '{"mRender": currencyFormat},';  } if($this->session->userdata('show_price')) { echo '{"mRender": currencyFormat},';  } } ?> <?= $warehouse_value ?> {"mRender": JSConvertQty},  <?php if(!$warehouse_id || !$Settings->racks) { echo '{"bVisible": false},'; } else { echo '{"bSortable": true},'; } ?> {"mRender": formatQuantity}, {"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('unit');?>]", filter_type: "text", data: []},
            
            <?php $col = 6;
            if($Owner || $Admin) {
                echo '{column_number : 7, filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },';
                echo '{column_number : 8, filter_default_label: "['.lang('price').']", filter_type: "text", data: [] },';
                $col += 2;
            } else {
                if($this->session->userdata('show_cost')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },'; }
                if($this->session->userdata('show_price')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('price').']", filter_type: "text", data: [] },'; }
            }
            ?>
            <?php
            if ($this->Settings->show_warehouse_qty) {
                if($warehouses){
                    foreach($warehouses as $warehoused){
                        $col++;
                        echo '{column_number: '.$col.', filter_default_label: "['.$warehoused->name.']", filter_type: "text", data: []},';
                    }
                }
            }
            ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('quantity');?>]", filter_type: "text", data: []},
            <?php $col++; if($warehouse_id && $Settings->racks) { echo '{column_number : '. $col.', filter_default_label: "['.lang('rack').']", filter_type: "text", data: [] },'; } ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('alert_quantity');?>]", filter_type: "text", data: []},
        ], "footer");
    });
    
    
    $('#price_list').live('click',function(){
        var product_id = '';
        var intRegex = /^\d+$/;
        var i = 0;
        $('.input-xs').each(function(){
            if ($(this).is(':checked') && intRegex.test($(this).val())) {
                if(i==0){
                    product_id += $(this).val();
                    i=1;
                }else{
                    product_id += "ProductID"+$(this).val();
                }
                
            }
        });
        if(product_id==''){
            alert("<?= lang('no_sale_selected') ?>")
            return false;
        }else{
            var link = '<?= anchor('products/price_list/#######', '<i class="fa fa-money"></i> ' . lang('price_list'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="price_list"')?>';
            var price_list_link = link.replace("#######", product_id);
            $("#price_box").html(price_list_link);
            $('.price_list').click();
            $("#price_box").html('<a href="javascript:void(0)" id="price_list" data-action="price_list"><i class="fa fa-money"></i> <?=lang('price_list')?></a>');      
            return false;
        }
    });
    
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('products/product_actions'.($warehouse_id ? '/'.$warehouse_id : ''), 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <!-- <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i>
            <?= lang('products') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'.($supplier ? ' ('.lang('supplier').': '.($supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name).')' : ''); ?>
        </h2> -->
        <div class="sub_menu"></div>
        <div class="sub_menu">
            <a href="<?= site_url('products/add') ?>" class="btn btn-success btn-block box_sub_menu" 
                tabindex="-1">
                <i class="fa fa-plus-circle"></i> <?= lang('add_product') ?>
            </a>
        </div>
        <div class="sub_menu">
            <?php if(!$warehouse_id) { ?>
                <a href="<?= site_url('products/update_price') ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"  
                    class="btn btn-warning btn-block box_sub_menu" tabindex="-1">
                    <i class="fa fa-pencil-square-o"></i> <?= lang('update_price') ?>
                </a>
            <?php } ?>
        </div>
        <div class="sub_menu">
            <a href="#" id="labelProducts" data-action="labels"  class="btn btn-primary btn-block box_sub_menu" 
                tabindex="-1">
                <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
            </a>
        </div>
        <div id="price_box" class="sub_menu">
            <?php if ($Owner || $Admin || $GP['products-price_list']) { ?>
                <a href="javascript:void(0)" id="price_list" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-action="price_list" 
                    class="btn btn-info btn-block box_sub_menu" tabindex="-1">
                    <i class="fa fa-money"></i> <?=lang('price_list')?>
                </a>
            <?php } ?>
        </div>
        <div class="sub_menu">
        <a href="#" id="excel" data-action="export_excel" class="btn btn-success btn-block box_sub_menu" 
            tabindex="-1">
            <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
        </a>
        </div>
        <div class="sub_menu">
        <a href="#" class="bpo btn btn-danger btn-block box_sub_menu" title="<?= $this->lang->line("delete_products") ?>"
            tabindex="-1"
            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
            data-html="true" data-placement="left">
            <i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
        </a>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <h2 class="blue"><i class="icon fa fa-barcode tip"></i><?= lang('products'); ?></h2>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('products/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->

                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th><?= lang("code") ?></th>
                            <th><?= lang("name") ?></th>
                            <th><?= lang("type") ?></th>
                            <th><?= lang("category") ?></th>
                            <th><?= lang("unit") ?></th>
                            
                            <?php
                            if ($Owner || $Admin) {
                                echo '<th>' . lang("cost") . '</th>';
                                echo '<th>' . lang("price") . '</th>';
                            } else {
                                if ($this->session->userdata('show_cost')) {
                                    echo '<th>' . lang("cost") . '</th>';
                                }
                                if ($this->session->userdata('show_price')) {
                                    echo '<th>' . lang("price") . '</th>';
                                }
                            }
                            ?>
                            <?= $warehouse_header ?>
                            <th><?= lang("quantity") ?></th>
                            <th><?= lang("rack") ?></th>
                            <th><?= lang("alert_quantity") ?></th>
                            <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>

                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <?php
                            if ($Owner || $Admin) {
                                echo '<th></th>';
                                echo '<th></th>';
                            } else {
                                if ($this->session->userdata('show_cost')) {
                                    echo '<th></th>';
                                }
                                if ($this->session->userdata('show_price')) {
                                    echo '<th></th>';
                                }
                            }
                            ?>
                            <?= $warehouse_footer ?>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
