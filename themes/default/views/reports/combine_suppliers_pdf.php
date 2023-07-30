<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("supplier_report"); ?></title>
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
                                    <th width=150><?= lang("N#"); ?></th>
                                    <th	width=150><?= lang("company"); ?></th>
									<th width=150><?= lang("name");?></th>
                                    <th	width=150><?= lang("phone"); ?></th>
									<th	width=150><?= lang("email"); ?></th>
									<th width=150><?= lang("total_purchases");?></th>
                                    <th	width=150><?= lang("total_amount"); ?></th>
                                    <th	width=150><?= lang("paid"); ?></th>
									<th width=150><?= lang("balance");?></th>
                                </tr>
                            </thead>
						</tr>
                    </thead>
					
                    <tbody>
						<?php 
						
						$total = 0;
						$total_amount = 0;
						$paid = 0;
						$balance = 0;
						
						foreach($rows as $i => $data_row){
							
							$total 		  += $data_row->total;
							$total_amount += $data_row->total_amount;
							$paid 		  += $data_row->paid;
							$balance 	  += $data_row->balance;
							
							?>
							<tr>
								<td class=center><?= ($i+1) ?></td>
								<td class=center><?= $data_row->company; ?></td>
								<td class=center><?= $data_row->name; ?></td>
								<td class=center><?= $data_row->phone; ?></td>
								<td class=center><?= $data_row->email; ?></td>
								<td class=right><?= $this->cus->formatMoney($data_row->total); ?></td>
								<td class=right><?= $this->cus->formatMoney($data_row->total_amount); ?></td>
								<td class=right><?= $this->cus->formatMoney($data_row->paid); ?></td>
								<td class=right><?= $this->cus->formatMoney($data_row->balance); ?></td>
								
							</tr>  
						<?php } ?>
                    </tbody>
					
                    <tfoot>
						<tr>
							<th colspan=5></th>
							<th	class=right><?= $this->cus->formatMoney($total); ?></td>
							<th	class=right><?= $this->cus->formatMoney($total_amount); ?></td>
							<td class=right><?= $this->cus->formatMoney($paid); ?></td>
							<th	class=right><?= $this->cus->formatMoney($balance); ?></td>
						</tr>
                    </tfoot>
					
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>