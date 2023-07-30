<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("suppliers"); ?></title>
    <link href="<?php echo $assets ?>styles/style.css" rel="stylesheet">
    <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
    <style type="text/css">
		@page {
		  size: A4;
		  margin: 0;
		}
        html, body {
            height: 100%;
            background: #999;
        }
        body:before, 
		body:after {
            display: none !important;
        }
        #wrap { 
            max-width: 780px; 
            margin: 0 auto; 
            padding-top: 20px; 
            page-break-after: always;
        }
		#paper{
			background: #FFF;
			padding:15px;
			box-shadow:0.8px 1px 1.5px #000;
		}
		#print-box{
			max-width: 780px;
			height:2px;
			margin: 0 auto; 
		}
        .table-item th {
            text-align: center;
            padding: 5px;
            font-size:13px !important;
        }
        .table-item td {
            padding: 4px;
            font-size:13px !important;
            white-space:nowrap;
        }
    </style>
</head>
<body>
<div id="print-box">
	<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
		<i class="fa fa-print"></i> <?= lang('print') ?>
	</button>
	<?php if(isset($_GET['v']) && trim($_GET['v'])==2){ ?>
		<a href="<?= site_url("reports/supplier_report/".trim($_GET['supplier'])); ?>" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;">
			<i class="fa fa-chevron-left"></i> <?= lang('back') ?>
		</a>
	<?php } ?>
</div>
<?php
	$font_size = $this->config->item('font_size');
	$created_by = $this->site->getUser($this->input->get("user"));
    $suppliers = explode(",",$_GET['supplier']);
    foreach($suppliers as $cust){
        $rows = $this->reports_model->getPurchaseReportBySuppliers($cust);
		$supplier = $this->site->getCompanyByID($cust);
?>
<div id="wrap">
	<div id="paper">
		<div class="row">
			<div class="col-lg-12">
				<table width="100%">
					<tr>
						<td colspan="7" style="border:none;">
							<div class="cover-head">
								<div class="text-center">
									<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
									<div><?= $biller->address.' '.$biller->city ?></div>
									<div><?= lang('tel').' : '. $biller->phone ?></div>	
									<div><?= lang('email').' : '. $biller->email ?></div>
								</div>
								<div class="col-sm-12 text-center" style="margin-bottom:8px;">
									<h4 style="font-weight:bold; text-decoration:underline;"><?=lang('supplier_purchases_report');?></h4>
								</div>
								<table width="100%">
									<tr>
										<th style="border:none; text-align:left;">
											<span style='color:#357EDD !important;'><?= lang("supplier") ?> : <?= ($supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name) ?></span>
										</th>
									</tr>
									<tr>
										<th style="border:none; text-align:left;">
											<span style='color:#357EDD !important;'><?= lang("phone") ?> : <?= $supplier->phone; ?></span>
										</th>
									</tr>
									<tr>
										<th style="border:none; text-align:left;">
											<span style='color:#357EDD !important;'>
											<?= lang("address") ?> : <?= $supplier->address; ?>
											<?= $supplier->city ." ".$supplier->state." ".$supplier->country; ?>
											</span>
										</th>
									</tr>
								</table>
								<div style="clear:both;"></div>
								<hr/>
							</div>
						</td>
					</tr>
				</table>
				<div class="clearfix"></div>
				<h5><b><?= lang("view_report") ?></b></h5>
				<div class="table-responsive">
					<table class="table-item" border="1">
						<thead>
							<tr class="active">
								 <thead>
									<tr>
										<th style="width:170px"><?= lang("date"); ?></th>
										<th	style="width:180px"><?= lang("reference_no"); ?></th>
										<th	style="width:200px"><?= lang("supplier"); ?></th>
										<th	style="width:90px"><?= lang("grand_total"); ?></th>
										<th	style="width:100px"><?= lang("returned"); ?></th>
										<th	style="width:100px"><?= lang("paid"); ?></th>
										<th	style="width:100px"><?= lang("balance"); ?></th>
									</tr>
								</thead>
							</tr>
						</thead>
						<tbody>
							<?php 
								$t_grand_total = 0;
								$t_paid = 0;
								$t_balance = 0;
								$t_total_return = 0;
								foreach($rows as $data_row){
									$t_grand_total += $data_row->grand_total;
									$t_paid += $data_row->paid;
									$t_balance += $data_row->balance;
									$t_total_return += $data_row->total_return;
								?>
								<tr>
									<td style="text-align:center"><?= $this->cus->hrld($data_row->date); ?> </td>
									<td style="text-align:center"><?= $data_row->reference_no; ?> </td>
									<td style="text-align:left"><?= $data_row->supplier; ?></td>
									<td style="text-align:right"><?= $this->cus->formatMoney($data_row->grand_total); ?> </td>
									<td style="text-align:right"><?= $this->cus->formatMoney($data_row->total_return); ?> </td>
									<td style="text-align:right"><?= $this->cus->formatMoney($data_row->paid); ?> </td>
									<td style="text-align:right"><?= $this->cus->formatMoney($data_row->balance); ?> </td>
								</tr>  
							<?php } ?>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td style="text-align:right; font-weight:bold;"><?= $this->cus->formatMoney($t_grand_total); ?></td>
								<td style="text-align:right; font-weight:bold;"><?= $this->cus->formatMoney($t_total_return); ?></td>
								<td style="text-align:right; font-weight:bold;"><?= $this->cus->formatMoney($t_paid); ?></td>
								<td style="text-align:right; font-weight:bold;"><?= $this->cus->formatMoney($t_balance); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
				
				<br/><br/><br/>
				<div class="row">
					<div class="col-xs-7"></div>
					<div class="col-xs-4">
						<p><?=lang("created_by");?>: <?=$created_by->first_name . ' ' . $created_by->last_name;?> </p>
						<p>&nbsp;</p>
						<p>&nbsp;</p>
						<hr>
					</div>
				</div>
				<br/>
			</div>
		</div>
	</div>
</div>
<?php } ?>
</body>
</html>