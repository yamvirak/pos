<?php defined('BASEPATH') OR exit('No direct script access allowed');
$v = "";

if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
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
		
		$(document).on('click', '.transaction_link', function () {
			var transaction = $(this).attr('transaction');
			var opt = $(this).attr('opt');
			var start_date = $('#start_date').val();
			var end_date = $('#end_date').val();
			var warehouse_id = $('#warehouse').val();
			var product_id = $(this).parent().parent().attr('id');
			$('#myModal').modal({remote: site.base_url + 'reports/view_stock_modal/?o='+opt+'&t='+transaction+'&w='+warehouse_id+'&p='+product_id+'&s='+start_date+'&e='+end_date});
			$('#myModal').modal('show');
		});
		
		oTable = $('#SLData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('reports/getInventoryInOut?v=1&'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
				nRow.id = aData[0];
				nRow.className = "product_link3";
                return nRow;
            },
            "aoColumns": [
			{"bVisible":false},
			null,
			null,
			null,
			{"sClass" : "text-right", "bSearchable":false},
			{"sClass" : "text-right", "bSearchable":false},
			<?php if($Settings->accounting == 1){ ?>
				{"sClass" : "text-right", "bSearchable":false},
			<?php } if(!$this->config->item('one_warehouse')){ ?>	
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},
			<?php } if($this->config->item('deliveries')){ ?>	
				{"sClass" : "text-right", "bSearchable":false},		
			<?php } if($this->config->item('using_stocks')){ ?>	
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},
			<?php } if($this->config->item('convert')){ ?>	
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},
			<?php } if($this->config->item('consignments')){ ?>	
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},
			<?php } if($this->config->item('concretes')){ ?>	
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},	
			<?php } if($this->config->item('pawn')){ ?>	
				{"sClass" : "text-right", "bSearchable":false},	
			<?php } ?>
			{"sClass" : "text-right", "bSearchable":false},
			{"sClass" : "text-right", "bSearchable":false},
			{"sClass" : "text-right", "bSearchable":false},
			{"sClass" : "text-right", "bSearchable":false},
			{"sClass" : "text-right", "bSearchable":false},
			{"sClass" : "text-right", "bSearchable":false}],
          
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('id');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
			
        ], "footer");
    });
</script>

<?php echo form_open("reports/inventory_in_out", ' id="form-submit" '); ?>

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
					<h2 class="blue"><i class="fa-fw fa fa-calendar icon"></i><?= lang('inventory_in_out'); ?></h2>
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
				
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
				<li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li> -->
				
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->
				<div id="form">
					<div class="row">
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>			
                        
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="category"><?= lang("category"); ?></label>
                                <?php
                                $ct[""] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $ct[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $ct, (isset($_POST['category']) ? $_POST['category'] : ""), 'class="form-control" id="category" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("category") . '"');
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
                                		<?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')), 'class="form-control date" id="start_date"'); ?>
								</div>
							</div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
								<div class="input-group input-append">
                                	<span class="input-group-addon add-on"><span class="fa fa-calendar"></span></span>
                                	<?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control date" id="end_date"'); ?>
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
				
				<?php
					$fcolspan = 12;
					$colspan=3;
					if($Settings->accounting == 1){
						$colspan++;
						$fcolspan = $fcolspan + 1;
					}
					if(!$this->config->item('one_warehouse')){
						$colspan++;
						$fcolspan = $fcolspan + 2;
					}
					if($this->config->item('deliveries')){
						$colspan++;
						$fcolspan = $fcolspan + 2;
					}
					if($this->config->item('using_stocks')){
						$colspan++;
						$fcolspan = $fcolspan + 2;
					}
					if($this->config->item('convert')){
						$colspan++;
						$fcolspan = $fcolspan + 2;
					}
					if($this->config->item('consignments')){
						$colspan++;
						$fcolspan = $fcolspan + 2;
					}
					if($this->config->item('concretes')){
						$colspan++;
						$fcolspan = $fcolspan + 2;
					}
					if($this->config->item('pawn')){
						$colspan++;
						$fcolspan = $fcolspan + 1;
					}
				?>
				
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
                                        echo '<div class="bold" style="font-size:15px;font-family: Khmer OS Muol Light;">'.lang('inventory_in_out_kh').'</div>';
                                        echo '<div class="bold">'.lang('inventory_in_out_en').'</div><br>';
                                }
                                   
                                ?>
                               
                            </td> 
                        </tr>
                </table>

                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped dataTable">
                        <thead>
							<tr>
								<th rowspan="2"><?= lang("id"); ?></th>
								<th rowspan="2"><?= lang("category"); ?></th>
								<th rowspan="2"><?= lang("code"); ?></th>
								<th rowspan="2"><?= lang("name"); ?></th>
								<th rowspan="2"><?= lang("begin"); ?></th>
								<th colspan="<?= $colspan  - ($this->config->item('deliveries') ? 1 : 0) ?>"><?= lang("in"); ?> <i class="fa fa-plus-circle"></i></th>
								<th colspan="<?= ($colspan - ($this->config->item('pawn') ? 1 : 0) - ($Settings->accounting==1 ? 1 : 0)) ?>"><?= lang("out"); ?> <i class="fa fa-minus-circle"></i></th>
								<th rowspan="2"><?= lang("ending"); ?></th>
							</tr>
							<tr>
								<?php if($Settings->accounting == 1){ ?>
									<th id="stock_in"><?= lang("opening"); ?></th>
								<?php } ?>
								<th id="stock_in"><?= lang("purchase"); ?></th>
								<th id="stock_in"><?= lang("sale"); ?></th>
								<th id="stock_in"><?= lang("adjustment"); ?></th>
								<?php if(!$this->config->item('one_warehouse')){ ?>	
									<th id="stock_in"><?= lang("transfer"); ?></th>
								<?php } if($this->config->item('using_stocks')){ ?>
									<th id="stock_in"><?= lang("using"); ?></th>
								<?php } if($this->config->item('convert')){ ?>
									<th id="stock_in"><?= lang("convert"); ?></th>
								<?php } if($this->config->item('consignments')){ ?>
									<th id="stock_in"><?= lang("consignment"); ?></th>
								<?php } if($this->config->item('concretes')){ ?>
									<th id="stock_in"><?= lang("concrete"); ?></th>	
								<?php } if($this->config->item('pawn')){ ?>
									<th id="stock_in"><?= lang("pawn"); ?></th>	
								<?php } ?>
								<th id="stock_out"><?= lang("purchase"); ?></th>
								<th id="stock_out"><?= lang("sale"); ?></th>
								<th id="stock_out"><?= lang("adjustment"); ?></th>
								<?php if(!$this->config->item('one_warehouse')){ ?>	
									<th id="stock_out"><?= lang("transfer"); ?></th>
								<?php } if($this->config->item('deliveries')){ ?>
									<th id="stock_out"><?= lang("delivery"); ?></th>
								<?php } if($this->config->item('using_stocks')){ ?>
									<th id="stock_out"><?= lang("using"); ?></th>
								<?php } if($this->config->item('convert')){ ?>
									<th id="stock_out"><?= lang("convert"); ?></th>
								<?php } if($this->config->item('consignments')){ ?>
									<th id="stock_out"><?= lang("consignment"); ?></th>	
								<?php } if($this->config->item('concretes')){ ?>
									<th id="stock_out"><?= lang("concrete"); ?></th>	
								<?php } ?>						
							</tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="<?= $fcolspan ?>" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th colspan="<?= ($fcolspan - 3) ?>"></td>
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
    	$(function(){
        	$("#SLData").css("width", "auto");
    	});
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
            window.location.href = "<?=site_url('reports/getInventoryInOut/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getInventoryInOut/0/xls/?v=1'.$v)?>";
            return false;
        });
    });
</script>

