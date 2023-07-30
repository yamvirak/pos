<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("suppliers"); ?></title>
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
			<h2><?= lang("purchases_report") ?></h2>
                <table class="table table-bordered table-hover table-striped">
                    <thead>
						<tr class="active">
							 <thead>
                                <tr>
                                    <th width=150><?= lang("date"); ?></th>
                                    <th	width=150><?= lang("reference_no"); ?></th>
									<th	width=150><?= lang("warehouse"); ?></th>
                                    <th	width=150><?= lang("supplier"); ?></th>
									<th width=150><?= lang("product(Qty)"); ?></th>
                                    <th	width=150><?= lang("grand_total"); ?></th>
                                    <th	width=150><?= lang("paid"); ?></th>
                                    <th	width=150><?= lang("balance"); ?></th>
									<th	width=150><?= lang("status"); ?></th>
                                </tr>
                            </thead>
						</tr>
                    </thead>
					
                    <tbody>
						<?php 
							$t_grand_total = 0;
							$t_paid = 0;
							$t_balance = 0;
							foreach($rows as $data_row){
								$t_grand_total += $data_row->grand_total;
								$t_paid += $data_row->paid;
								$t_balance += $data_row->grand_total - $data_row->paid;
							?>
							<tr>
								<td class=center><?= $this->cus->hrld($data_row->date); ?> </td>
								<td class=center><?= $data_row->reference_no; ?> </td>
								<td class=center><?= $data_row->wname; ?> </td>
								<td class=center><?= $data_row->supplier; ?> </td>
								<td class=center><?= $data_row->iname; ?> </td>
								<td class=right><?= $this->cus->formatMoney($data_row->grand_total); ?> </td>
								<td class=right><?= $this->cus->formatMoney($data_row->paid); ?> </td>
								<td class=right><?= $this->cus->formatMoney($t_balance); ?> </td>
								<td class=center><?= $data_row->status; ?> </td>
							</tr>  
						<?php } ?>
                    </tbody>
					
                    <tfoot>
						<tr>
							<th colspan=5></th>
							<th	class=right><?= $this->cus->formatMoney($t_grand_total); ?></th>
							<th	class=right><?= $this->cus->formatMoney($t_paid); ?></th>
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