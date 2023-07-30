<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

?>
<style type="text/css">
    .topborder div { border-top: 1px solid #CCC; }
</style>
<script>
    $(document).ready(function () {        
        oTable = $('#countMoneyTable').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getCountMoney/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, {"mRender": fld},  null, {"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"},
			{"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}, {"sClass": "center"}],
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var gtotal = 0, a = 0, b=0, c = 0, d = 0, e=0, f = 0, g=0, h = 0, z = 0, j=0, k = 0, l = 0, m = 0, n=0, o = 0, p = 0, q = 0, v=0, x = 0, y=0;
				for (var i = 0; i < aaData.length; i++) {
					gtotal += parseFloat(aaData[aiDisplay[i]][3]);
					a += parseFloat(aaData[aiDisplay[i]][4]);
					b += parseFloat(aaData[aiDisplay[i]][5]);
					c += parseFloat(aaData[aiDisplay[i]][6]);
					d += parseFloat(aaData[aiDisplay[i]][7]);
					e += parseFloat(aaData[aiDisplay[i]][8]);
					f += parseFloat(aaData[aiDisplay[i]][9]);
					g += parseFloat(aaData[aiDisplay[i]][10]);
					h += parseFloat(aaData[aiDisplay[i]][11]);
					z += parseFloat(aaData[aiDisplay[i]][12]);
					j += parseFloat(aaData[aiDisplay[i]][13]);
					k += parseFloat(aaData[aiDisplay[i]][14]);
					l += parseFloat(aaData[aiDisplay[i]][15]);
					m += parseFloat(aaData[aiDisplay[i]][16]);
					n += parseFloat(aaData[aiDisplay[i]][17]);
					o += parseFloat(aaData[aiDisplay[i]][18]);
					p += parseFloat(aaData[aiDisplay[i]][19]);
					q += parseFloat(aaData[aiDisplay[i]][20]);
					v += parseFloat(aaData[aiDisplay[i]][21]);
					x += parseFloat(aaData[aiDisplay[i]][22]);
					y += parseFloat(aaData[aiDisplay[i]][23]);
					
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[3].innerHTML = currencyFormat(parseFloat(gtotal));
				nCells[4].innerHTML = currencyFormat(parseFloat(a));
				nCells[5].innerHTML = currencyFormat(parseFloat(b));
				nCells[6].innerHTML = c;
				nCells[7].innerHTML = d;
				nCells[8].innerHTML = e;
				nCells[9].innerHTML = f;
				nCells[10].innerHTML = g;
				nCells[11].innerHTML = h;
				nCells[12].innerHTML = z;
				nCells[13].innerHTML = j;
				nCells[14].innerHTML = k;
				nCells[15].innerHTML = l;
				nCells[16].innerHTML = m;
				nCells[17].innerHTML = n;
				nCells[18].innerHTML = o;
				nCells[19].innerHTML = p;
				nCells[20].innerHTML = q;
				nCells[21].innerHTML = v;
				nCells[22].innerHTML = x;
				nCells[23].innerHTML = y;
	
			}
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[ yyyy-mm-dd HH:mm:ss ]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('user');?>]", filter_type: "text", data: []},
        ], "footer");

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
<style>.table td:nth-child(6) {
        text-align: center;
    }</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('count_money_report'); ?><?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
                            class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
                            class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("reports/count_money_report"); ?>
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("user"); ?></label>
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
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="countMoneyTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                        <tr>
							<th style="min-width:3%; width: 3%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang('counted_at'); ?></th>
                            <th><?= lang('counted_by'); ?></th>
                            <th><?= lang('total_amount'); ?></th>	
							<th><?= lang('total_amount_us'); ?></th>
							<th><?= lang('total_amount_kh'); ?></th> 
                            <th><?= lang('100_riel'); ?></th>                            
                            <th><?= lang('500_riel'); ?></th>
                            <th><?= lang('1k_riel'); ?></th>
							<th><?= lang('2k_riel'); ?></th>
							<th><?= lang('5k_riel'); ?></th>							
                            <th><?= lang('10k_riel'); ?></th>                            
                            <th><?= lang('20k_riel'); ?></th>
                            <th><?= lang('50k_riel'); ?></th>
							<th><?= lang('100k_riel'); ?></th>
							<th><?= lang('1_use'); ?></th>                            
                            <th><?= lang('2_use'); ?></th>
                            <th><?= lang('5_use'); ?></th>
							<th><?= lang('10_use'); ?></th>
							<th><?= lang('20_use'); ?></th>							
                            <th><?= lang('50_use'); ?></th>                            
                            <th><?= lang('100_use'); ?></th>
                            <th><?= lang('500_use'); ?></th>
							<th><?= lang('1k_use'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="24" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
							<th></th>
                            <th></th>
							<th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
                            <th></th>
                            <th></th>
							<th></th>
                            <th></th>
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
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCountMoney/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCountMoney/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>