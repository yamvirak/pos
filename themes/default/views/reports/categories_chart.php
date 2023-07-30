<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
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
<?php
    $categories_list = ''; $products_list = '';
    foreach ($categories_sales as $categories_sale) {
        $products = $this->reports_model->getChartProductsDataByCategory($categories_sale->id);
        $total_qty = '';
        foreach($products as $product){
            $total_qty .= '["'.$product->code.'", '.$product->TotalSales.'],';
        }
        $categories_list .= '{name :"'.$categories_sale->name.'",y : '.$categories_sale->TotalSales.',drilldown : "'.$categories_sale->name.'"},';
        $products_list .= '{name: "'.$categories_sale->name.'",id: "'.$categories_sale->name.'",data: ['.$total_qty.']}, ';
    }
    ?>
    <style type="text/css" media="screen">
        .tooltip-inner {
            max-width: 500px;
        }
    </style>
    <script src="<?= $assets ?>js/highcharts/highcharts.js"></script>
    <script src="<?= $assets ?>js/highcharts/data.js"></script>
    <script src="<?= $assets ?>js/highcharts/drilldown.js"></script>
    <script src="<?= $assets ?>js/highcharts/exporting.js"></script>
    <script src="<?= $assets ?>js/highcharts/export-data.js"></script>
    <script src="<?= $assets ?>js/highcharts/accessibility.js"></script>
    <script type="text/javascript">
        $(function(){
                // Create the chart
                Highcharts.chart('sales_cat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    accessibility: {
                        announceNewData: {
                            enabled: true
                        }
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y:.1f}$'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:11px">{series.name}</span>',
                        pointFormat: '<span style="color:{point.color}"> {point.name}</span>: <b>{point.y:.2f}$</b><br/>'
                    },
                    series: [
                        {
                            name: "Total: ",
                            colorByPoint: true,
                            data: [<?php echo $categories_list; ?>]
                        }
                    ],
                    drilldown: {
                        series: [<?php echo $products_list; ?>]
                    }
                });
        })
    </script>
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
                });
	    </script>
    <div class="box" style="margin-top: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('categories_chart'); ?></h2>
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
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?php echo lang('categories_chart_heading'); ?></p>
					<div id="form">
                    <?php echo form_open("reports/categories_chart"); ?>
						<div class="row">
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
									<?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("end_date", "end_date"); ?>
									<?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
						</div>
						<?php echo form_close(); ?>
					</div>
                    <div id="sales_cat" style="width:100%; height:450px;"></div>
                </div>
            </div>
        </div>
    <div class="clearfix"></div>

     <div class="box" style="margin-top: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('categories_chart'); ?></h2>
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
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <figure class="highcharts-figure1">
        <div id="container1"></div>
        <p class="highcharts-description1">
            
        </p>
    </figure>

<style>
    .highcharts-figure1,
    .highcharts-data-table1 table {
        width: 100%;
        height: 500px;
        margin: 1em;
        padding: 0px 0px 0px 0px;
    }
    .highcharts-data-table1 table {
        font-family: Verdana, sans-serif;
        border-collapse: collapse;
        border: 1px solid #ebebeb;
        margin: 10px auto;
        text-align: center;
        width: 100%;
        max-width: 600px;
    }
    .highcharts-data-table1 caption {
        padding: 1em 0;
        font-size: 1.2em;
        color: #555;
    }
    .highcharts-data-table1 th {
        font-weight: 600;
        padding: 0.5em;
    }

    .highcharts-data-table1 td,
    .highcharts-data-table1 th,
    .highcharts-data-table1 caption {
        padding: 0.5em;
    }

    .highcharts-data-table1 thead tr,
    .highcharts-data-table1 tr:nth-child(even) {
        background: #f8f8f8;
    }

    .highcharts-data-table1 tr:hover {
        background: #f1f7ff;
    }
</style>


    <script>
        Highcharts.chart('container1', {
        chart: {
            type: 'pie'
        },
            title: {
            text: 'Room Rental',
            align: 'center'
        },

        accessibility: {
            announceNewData: {
            enabled: true
            },
            point: {
            valueSuffix: '%'
            }
        },

        plotOptions: {
            series: {
            borderRadius: 5,
            dataLabels: {
            enabled: true,
            format: '{point.name}: {point.y:.1f}%'
                }
            }
        },

        tooltip: {
            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
        },

        series: [
            {
            name: 'Browsers',
            colorByPoint: true,
            data: [
                {
                name: 'Standard Room',
                y: 45.04,
                drilldown: 'Standard Room'
                },
                {
                name: 'Superior King',
                y: 9.47,
                drilldown: 'Superior King'
                },
                {
                name: 'Superior Twin',
                y: 5.32,
                drilldown: 'Superior Twin'
                },
                {
                name: 'Deluxe Room',
                y: 18.15,
                drilldown: 'Deluxe Room'
                },
                {
                name: 'Total',
                y: 39.02,
                drilldown: 'Total'
                },
                {
                name: 'Unsold Room',
                y: 11.02,
                drilldown: 'Unsold Room'
                },
                {
                name: 'Occupancy',
                y: 25.02,
                drilldown: 'Occupancy'
                }
            ]
            }
        ],
        drilldown: {
            series: [
            {
                name: 'Standard Room',
                id: 'Standard Room',
                data: [
                [
                    'v97.0',
                    36.89
                ],
                [
                    'v96.0',
                    18.16
                ],
                [
                    'v95.0',
                    0.54
                ],
                [
                    'v94.0',
                    0.7
                ],
                [
                    'v93.0',
                    0.8
                ],
                [
                    'v92.0',
                    0.41
                ],
                [
                    'v91.0',
                    0.31
                ],
                [
                    'v90.0',
                    0.13
                ],
                [
                    'v89.0',
                    0.14
                ],
                [
                    'v88.0',
                    0.1
                ],
                [
                    'v87.0',
                    0.35
                ],
                [
                    'v86.0',
                    0.17
                ],
                [
                    'v85.0',
                    0.18
                ],
                [
                    'v84.0',
                    0.17
                ],
                [
                    'v83.0',
                    0.21
                ],
                [
                    'v81.0',
                    0.1
                ],
                [
                    'v80.0',
                    0.16
                ],
                [
                    'v79.0',
                    0.43
                ],
                [
                    'v78.0',
                    0.11
                ],
                [
                    'v76.0',
                    0.16
                ],
                [
                    'v75.0',
                    0.15
                ],
                [
                    'v72.0',
                    0.14
                ],
                [
                    'v70.0',
                    0.11
                ],
                [
                    'v69.0',
                    0.13
                ],
                [
                    'v56.0',
                    0.12
                ],
                [
                    'v49.0',
                    0.17
                ]
                ]
            },
            {
                name: 'Superior King',
                id: 'Superior King',
                data: [
                [
                    'v15.3',
                    0.1
                ],
                [
                    'v15.2',
                    2.01
                ],
                [
                    'v15.1',
                    2.29
                ],
                [
                    'v15.0',
                    0.49
                ],
                [
                    'v14.1',
                    2.48
                ],
                [
                    'v14.0',
                    0.64
                ],
                [
                    'v13.1',
                    1.17
                ],
                [
                    'v13.0',
                    0.13
                ],
                [
                    'v12.1',
                    0.16
                ]
                ]
            },
            {
                name: 'Superior Twin',
                id: 'Superior Twin',
                data: [
                [
                    'v97',
                    6.62
                ],
                [
                    'v96',
                    2.55
                ],
                [
                    'v95',
                    0.15
                ]
                ]
            },
            {
                name: 'Deluxe Room',
                id: 'Deluxe Room',
                data: [
                [
                    'v96.0',
                    4.17
                ],
                [
                    'v95.0',
                    3.33
                ],
                [
                    'v94.0',
                    0.11
                ],
                [
                    'v91.0',
                    0.23
                ],
                [
                    'v78.0',
                    0.16
                ],
                [
                    'v52.0',
                    0.15
                ]
                ]
            },
            {
                name: 'Total',
                id: 'Total',
                data: [
                [
                    'v96.0',
                    4.17
                ],
                [
                    'v95.0',
                    3.33
                ],
                [
                    'v94.0',
                    0.11
                ],
                [
                    'v91.0',
                    0.23
                ],
                [
                    'v78.0',
                    0.16
                ],
                [
                    'v52.0',
                    0.15
                ]
                ]
            },
            {
                name: 'Unsold Room',
                id: 'Unsold Room',
                data: [
                [
                    'v96.0',
                    4.17
                ],
                [
                    'v95.0',
                    3.33
                ],
                [
                    'v94.0',
                    0.11
                ],
                [
                    'v91.0',
                    0.23
                ],
                [
                    'v78.0',
                    0.16
                ],
                [
                    'v52.0',
                    0.15
                ]
                ]
            },
            {
                name: 'Occupancy',
                id: 'Occupancy',
                data: [
                [
                    'v96.0',
                    4.17
                ],
                [
                    'v95.0',
                    3.33
                ],
                [
                    'v94.0',
                    0.11
                ],
                [
                    'v91.0',
                    0.23
                ],
                [
                    'v78.0',
                    0.16
                ],
                [
                    'v52.0',
                    0.15
                ]
                ]
            }
            ]
        }
        });
    </script>
    </div>
    </div>
            </div>
        </div>
        </div>
        </div>