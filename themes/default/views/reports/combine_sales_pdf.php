<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("customers"); ?></title>
    <link href="<?php echo $assets ?>styles/style.css" rel="stylesheet">
    <style type="text/css">
        html, body {
            height: 100%;
            background: #FFF;
        }
        body:before, body:after {
            display: none !important;
        }
        .table th {
            text-align: center;
            padding: 5px;
        }
        .table td {
            padding: 4px;
        }
    </style>
</head>

<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
			<h2><?= lang("sales_report") ?></h2>
                <table class="table table-bordered table-hover table-striped">
                    <thead>
						<tr class="active">
							 <thead>
                                <tr>
                                    <th width=150><?= lang("date"); ?></th>
                                    <th	width=150><?= lang("reference_no"); ?></th>
									<th width=150><?= lang("biller");?></th>
                                    <th	width=150><?= lang("customer"); ?></th>
									<th	width=150><?= lang("product"); ?></th>
									<th width=150><?= lang("gross_margin");?></th>
                                    <th	width=150><?= lang("grand_total"); ?></th>
                                    <th	width=150><?= lang("paid"); ?></th>
									<th width=150><?= lang("discount");?></th>
                                    <th	width=150><?= lang("balance"); ?></th>
									<th width=150><?= lang("payment_status");?></th>
                                </tr>
                            </thead>
						</tr>
                    </thead>
					
                    <tbody>
						<?php 
							$t_grand_total = 0;
							$t_paid = 0;
							$t_balance = 0;
							$t_gross_margin = 0;
							foreach($rows as $data_row){
								$t_grand_total += $data_row->grand_total;
								$t_paid += $data_row->paid;
								$t_balance += $data_row->grand_total - $data_row->paid;
								$gross_margin += ($data_row->grand_total - $data_row->total_cost);
							?>
							<tr>
								<td class=center><?= $this->cus->hrld($data_row->date); ?> </td>
								<td class=center><?= $data_row->reference_no; ?> </td>
								<td class=center><?= $data_row->biller; ?> </td>
								<td class=center><?= $data_row->customer; ?> </td>
								<td class=center><?= $data_row->iname; ?> </td>
								<td class=right><?= $this->cus->formatMoney($gross_margin); ?> </td>
								<td class=right><?= $this->cus->formatMoney($data_row->grand_total); ?> </td>
								<td class=right><?= $this->cus->formatMoney($data_row->paid); ?> </td>
								<td class=center><?= $data_row->discount; ?> </td>
								<td class=right><?= ($data_row->grand_total - $data_row->paid); ?> </td>
								<td class=center><?= $data_row->payment_status; ?> </td>
							</tr>  
						<?php } ?>
                    </tbody>
					
                    <tfoot>
						<tr>
							<th colspan=5></th>
							<th	class=right><?= $this->cus->formatMoney($gross_margin); ?></th>
							<th	class=right><?= $this->cus->formatMoney($t_grand_total); ?></th>
							<th	class=right><?= $this->cus->formatMoney($t_paid); ?></th>
							<td class=center><?= $data_row->discount; ?> </td>
							<th	class=right><?= $this->cus->formatMoney($t_balance); ?></th>
						</tr>
                    </tfoot>
					
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>