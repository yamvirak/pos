<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
		<?php if($this->session->userdata('remove_rtls')) { ?>
        if (localStorage.getItem('rtitems')) {
            localStorage.removeItem('rtitems');
        }
        if (localStorage.getItem('rtdiscount')) {
            localStorage.removeItem('rtdiscount');
        }
        if (localStorage.getItem('rttax2')) {
            localStorage.removeItem('rttax2');
        }
        if (localStorage.getItem('rtref')) {
            localStorage.removeItem('rtref');
        }
        if (localStorage.getItem('rtwarehouse')) {
            localStorage.removeItem('rtwarehouse');
        }
        if (localStorage.getItem('rtnote')) {
            localStorage.removeItem('rtnote');
        }
        if (localStorage.getItem('rtcustomer')) {
            localStorage.removeItem('rtcustomer');
        }
        if (localStorage.getItem('rtbiller')) {
            localStorage.removeItem('rtbiller');
        }
        if (localStorage.getItem('rtcurrency')) {
            localStorage.removeItem('rtcurrency');
        }
        if (localStorage.getItem('rtdate')) {
            localStorage.removeItem('rtdate');
        }
		if (localStorage.getItem('rtfloor')) {
            localStorage.removeItem('rtfloor');
        }
		if (localStorage.getItem('rtroom')) {
            localStorage.removeItem('rtroom');
        }
		if (localStorage.getItem('rtfrom_date')) {
            localStorage.removeItem('rtfrom_date');
        }
		if (localStorage.getItem('rtto_date')) {
            localStorage.removeItem('rtto_date');
        }
		if (localStorage.getItem('rtfrequency')) {
            localStorage.removeItem('rtfrequency');
        }
		if (localStorage.getItem('rtcontract_period')) {
            localStorage.removeItem('rtcontract_period');
        }
		if (localStorage.getItem('rtstaff_note')) {
			localStorage.removeItem('rtstaff_note');
		}
        <?php $this->cus->unset_data('remove_rtls'); } ?>
		
		<?php if($this->input->get('customer')) { ?>
			if (!localStorage.getItem('rtitems')) {
				localStorage.setItem('rtcustomer', <?=$this->input->get('customer');?>);
			}
        <?php } ?>
		
        <?php if ($Owner || $Admin || $GP['rentals-date']) { ?>
        if (!localStorage.getItem('rtdate')) {
            $("#rtdate").datetimepicker({
                <?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
                fontAwesome: true,
                language: 'cus',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#rtdate', function (e) {
            localStorage.setItem('rtdate', $(this).val());
        });
        if (rtdate = localStorage.getItem('rtdate')) {
            $('#rtdate').val(rtdate);
        }
        <?php } ?>
        $(document).on('change', '#rtbiller', function (e) {
            localStorage.setItem('rtbiller', $(this).val());
        });
        if (rtbiller = localStorage.getItem('rtbiller')) {
            $('#rtbiller').val(rtbiller);
        }
        if (!localStorage.getItem('rttax2')) {
            localStorage.setItem('rttax2', <?=$Settings->default_tax_rate2;?>);
        }
		$(document).on('change', '#rtfloor', function (e) {
			localStorage.setItem('rtfloor', $(this).val());
		});
		if (rtfloor = localStorage.getItem('rtfloor')) {
			$('#rtfloor').val(rtfloor);
		}
		$(document).on('change', '#rtroom', function (e) {
			localStorage.setItem('rtroom', $(this).val());
		});
		if (rtroom = localStorage.getItem('rtroom')) {
			$('#rtroom').val(rtroom);
		}
		$(document).on('change', '#rtfrequency', function (e) {
			localStorage.setItem('rtfrequency', $(this).val());
		});
		if (rtfrequency = localStorage.getItem('rtfrequency')) {
			$('#rtfrequency').val(rtfrequency);
		}
		$(document).on('change', '#rtcontract_period', function (e) {
			localStorage.setItem('rtcontract_period', $(this).val());
		});
		if (rtcontract_period = localStorage.getItem('rtcontract_period')) {
			$('#rtcontract_period').val(rtcontract_period);
		}
		$(document).on('change', '#rtfrom_date', function (e) {
			localStorage.setItem('rtfrom_date', $(this).val());
		});
		if (rtfrom_date = localStorage.getItem('rtfrom_date')) {
			$('#rtfrom_date').val(rtfrom_date);
		}
		$(document).on('change', '#rtto_date', function (e) {
			localStorage.setItem('rtto_date', $(this).val());
		});
		if (rtto_date = localStorage.getItem('rtto_date')) {
			$('#rtto_date').val(rtto_date);
		}

		$(window).bind('beforeunload', function (e) {
            $.get('<?= site_url('welcome/set_data/remove_rtls/1'); ?>');
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
		
    });
</script>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_housekeepings'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("rentals_housekeeping/add", $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
            	<?php if ($Owner || $Admin || $GP['rentals-date']) { ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("date", "rtdate"); ?>
                           <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="rtdate" required="required"'); ?>
                    </div>
                </div>
               <?php } ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("reference_no", "rtref"); ?>
                        <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $rtnumber), 'class="form-control input-tip" id="rtref"'); ?>
                    </div>
                </div>
				<div class="col-md-6">
					<div class="form-group">
                        <?= lang("biller", "biller"); ?>
						<?php
							$bl[''] = '';
							foreach ($billers as $biller) {
								$bl[$biller->id] = $biller->name;
							}
							echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="biller" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" style="width:100%;" ');
						?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("warehouse", "warehouse"); ?>
						<?php
							$wh[''] = '';
							foreach ($warehouses as $warehouse) {
								$wh[$warehouse->id] = $warehouse->name;
							}
							echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="warehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
						?>
                    </div>
                </div>
                 <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("floor", "rtfloor"); ?>
                                <?php
                                    $fl[''] = '';
                                    if($floors){
                                    foreach ($floors as $floor) {
                                        $fl[$floor->id] = $floor->floor;
                                }
                            }
                             echo form_dropdown('floor', $fl, (isset($_POST['floor']) ? $_POST['floor'] : ''), 'id="rtfloor" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("floor") . '" required="required" style="width:100%;" ');
                            ?>
                        </div>
                </div>
                <div class="col-md-6 ">
                   <div class="form-group">
                        <?= lang("room", "rtroom"); ?>
                            <div class="no-room">
                                <select name="room" class="form-control"></select>
                            </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("order_date", "order_date"); ?>
                       	<div class="input-group">
                            <?php echo form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] : $this->cus->hrsd(date("Y-m-d"))), 'class="form-control input-tip date bold" id="rtfrom_date" required="required"'); ?>
                            <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                 <i class="fa fa-calendar" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
               	<div class="col-md-6">
               		<div class="form-group">
                        <?= lang("finish_date", "finish_date"); ?>
                        <div class="input-group">
                            <?php echo form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] : $this->cus->hrsd(date("Y-m-d"))), 'class="form-control input-tip date bold" id="rtto_date" required="required"'); ?>
                             <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                                        
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("status", "status"); ?>
						<?php
							$hpts[''] = '';
							foreach ($housekeeping_types as $housekeeping_type) {
								$hpts[$housekeeping_type->id] = $housekeeping_type->name;
							}
							echo form_dropdown('status', $hpts, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("status") . '" style="width:100%;" ');
						?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("assigned_to", "assigned_to"); ?>
                        <?php
                            $stOpt[''] = '';
                            foreach ($staffs as $staff) {
                                $stOpt[$staff->id] = $staff->name;
                            }
                            echo form_dropdown('staff', $stOpt, (isset($_POST['staff']) ? $_POST['staff'] : ''), 'id="staff" class="form-control select" data-placeholder="' . lang("select") . ' ' . lang("status") . '" style="width:100%;" ');
                        ?>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang("note", "rtnote"); ?>
                           <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="rtnote" style="margin-top: 10px; height: 100px;"'); ?>
                    </div>
                </div>
			</div>
		</div>
		
		<div class="modal-footer">
			<?php echo form_submit('add_housekeepings', lang('add_housekeepings'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
	</div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#rtfloor").on("change", floor);
        function floor(e){
            $('.rtdel').each(function(i,e){
                var row = $(this).closest('tr');
                var item_id = row.attr('data-item-id');
                delete rtitems[item_id];
                row.remove();
            });
            var floor_id = $("#rtfloor").val();
            var room_id = $("#rtroom").val()?$("#rtroom").val():0;
            $.ajax({
                type: "get",
                url: "<?=site_url('rentals/get_room_floor')?>",
                data: { floor_id: floor_id, room_id : room_id },
                dataType: "json",
                success: function (data) {
                    if(data){
                        $(".no-room").html(data.result);
                        $("#rtroom").select2();
                    }
                }
            });
        }
    });
</script> 
