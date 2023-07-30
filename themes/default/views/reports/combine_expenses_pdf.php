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
		
            <h2><?= lang("expenses_report") ?></h2>
            
            <div class="clearfix"></div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
						<tr class="active">
							 <thead>
                                <tr>
                                    <th width=130><?= lang("date"); ?></th>
                                    <th	width=100><?= lang("reference_no"); ?></th>
									<th	width=100><?= lang("biller"); ?></th>
									<th	width=100><?= lang("supplier"); ?></th>
                                    <th	width=100><?= lang("category"); ?></th>
                                    <th	width=100><?= lang("amount"); ?></th>
                                    <th	width=300><?= lang("paid"); ?></th>
									<th	width=300><?= lang("balance"); ?></th>
                                    <th	width=100><?= lang("created_by"); ?></th>
									<th	width=100><?= lang("note"); ?></th>
									<th	width=100><?= lang("payment_status"); ?></th>
                                </tr>
                            </thead>
						</tr>
                    </thead>
					
                    <tbody>
						<?php 
						$total = 0;
						foreach($rows as $data_row){ 
							$total += $data_row->amount;
							$paid += $data_row->paid;
						?>
							<tr>
								<td class=center><?= $this->cus->hrld($data_row->date); ?> </td>
								<td class=center><?= $data_row->reference; ?> </td>
								<td class=center><?= $data_row->biller; ?> </td>
								<td class=center><?= $data_row->supplier; ?> </td>
								<td class=center><?= $data_row->category; ?> </td>
								<td class=center><?= $this->cus->formatMoney($data_row->amount); ?> </td>
								<td class=center><?= $this->cus->formatMoney($data_row->paid); ?> </td>
								<td class=center><?= $this->cus->formatMoney($data_row->amount - $data_row->paid); ?> </td>
								<td class=center><?= $data_row->user; ?></td>
								<td class=center><?= $data_row->note; ?> </td>
								<td class=center><?= $data_row->payment_status; ?> </td>
								
							</tr>   
						<?php } ?>
                    </tbody>
					
                    <tfoot>
						<tr>
							<th colspan=5></th>
							<th	class=center><?= $this->cus->formatMoney($total); ?></th>
							<td class=center><?= $this->cus->formatMoney($paid); ?> </td>
							<td class=center><?= $this->cus->formatMoney($data_row->amount - $data_row->paid); ?> </td>
							<th	class=center></th>
							<th	class=center></th>
						</tr>
                    </tfoot>
					
                </table>
            </div>
			
			 <div class="row">
                <div class="col-xs-7 pull-left"></div>
            </div>

        </div>
    </div>
</div>
</body>
</html>