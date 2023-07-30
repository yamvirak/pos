<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    //var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    
	var allow_remove = true;
	<?php if((!$Owner && !$Admin) && !empty($rental_id) && $rental_id>0){ ?>
		var allow_remove = false;
	<?php } ?>
	
	$(document).ready(function () {
		
		 <?php 
         // $this->session->set_userdata('remove_slls', 1);
        if ($this->session->userdata('remove_slls')) {?>
        if (localStorage.getItem('slitems')) {
            localStorage.removeItem('slitems');
        }
        if (localStorage.getItem('sldiscount')) {
            localStorage.removeItem('sldiscount');
        }
        if (localStorage.getItem('sltax2')) {
            localStorage.removeItem('sltax2');
        }
        if (localStorage.getItem('slref')) {
            localStorage.removeItem('slref');
        }
        if (localStorage.getItem('slshipping')) {
            localStorage.removeItem('slshipping');
        }
        if (localStorage.getItem('slwarehouse')) {
            localStorage.removeItem('slwarehouse');
        }
        if (localStorage.getItem('slnote')) {
            localStorage.removeItem('slnote');
        }
        if (localStorage.getItem('slinnote')) {
            localStorage.removeItem('slinnote');
        }
        if (localStorage.getItem('slcustomer')) {
            localStorage.removeItem('slcustomer');
        }
        if (localStorage.getItem('slbiller')) {
            localStorage.removeItem('slbiller');
        }
        if (localStorage.getItem('slcurrency')) {
            localStorage.removeItem('slcurrency');
        }
        if (localStorage.getItem('sldate')) {
            localStorage.removeItem('sldate');
        }
        if (localStorage.getItem('slsale_status')) {
            localStorage.removeItem('slsale_status');
        }
        if (localStorage.getItem('slpayment_status')) {
            localStorage.removeItem('slpayment_status');
        }
        if (localStorage.getItem('paid_by')) {
            localStorage.removeItem('paid_by');
        }
        if (localStorage.getItem('amount_1')) {
            localStorage.removeItem('amount_1');
        }
        if (localStorage.getItem('paid_by_1')) {
            localStorage.removeItem('paid_by_1');
        }
        if (localStorage.getItem('pcc_holder_1')) {
            localStorage.removeItem('pcc_holder_1');
        }
        if (localStorage.getItem('pcc_type_1')) {
            localStorage.removeItem('pcc_type_1');
        }
        if (localStorage.getItem('pcc_month_1')) {
            localStorage.removeItem('pcc_month_1');
        }
        if (localStorage.getItem('pcc_year_1')) {
            localStorage.removeItem('pcc_year_1');
        }
        if (localStorage.getItem('pcc_no_1')) {
            localStorage.removeItem('pcc_no_1');
        }
        if (localStorage.getItem('cheque_no_1')) {
            localStorage.removeItem('cheque_no_1');
        }
        if (localStorage.getItem('slpayment_term')) {
            localStorage.removeItem('slpayment_term');
        }
		if (localStorage.getItem('slsaleman')) {
			localStorage.removeItem('slsaleman');
		}
		if (localStorage.getItem('slcommission')) {
			localStorage.removeItem('slcommission');
		}
		if (localStorage.getItem('stock_deduction')) {
			localStorage.removeItem('stock_deduction');
		}
        <?php $this->cus->unset_data('remove_slls');}?>
		
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('payment_note_1')) {
                localStorage.removeItem('payment_note_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
			if (localStorage.getItem('slsaleman')) {
                localStorage.removeItem('slsaleman');
            }
			if (localStorage.getItem('slcommission')) {
                localStorage.removeItem('slcommission');
            }
			if (localStorage.getItem('stock_deduction')) {
                localStorage.removeItem('stock_deduction');
            }
            if (localStorage.getItem('receivable_account')) {
                localStorage.removeItem('receivable_account');
            }
			if (localStorage.getItem('slagency')) {
				localStorage.removeItem('slagency');
			}
            localStorage.removeItem('remove_slls');
        }
        <?php if($quote_id) { ?>
        // localStorage.setItem('sldate', '<?= $this->cus->hrld($quote->date) ?>');
        localStorage.setItem('slcustomer', '<?= $quote->customer_id ?>');
        localStorage.setItem('slbiller', '<?= $quote->biller_id ?>');
        localStorage.setItem('slwarehouse', '<?= $quote->warehouse_id ?>');
		localStorage.setItem('slnote', '<?= str_replace(array("\r", "\n", "'"), "", $this->cus->decode_html($quote->note)); ?>');
        localStorage.setItem('sldiscount', '<?= (isset($quote->order_discount_id) ? $quote->order_discount_id : '') ?>');
        localStorage.setItem('sltax2', '<?= (isset($quote->order_tax_id) ? $quote->order_tax_id : '') ?>');
		localStorage.setItem('slsaleman', '<?= (isset($quote->saleman_id) ? $quote->saleman_id : '') ?>');
		localStorage.setItem('slcommission', '<?= (isset($saleman_info) ? $saleman_info->saleman_commission : '') ?>');
		localStorage.setItem('slshipping', '<?= (isset($quote->shipping) ? $quote->shipping : '') ?>');
		localStorage.setItem('slpayment_term', '<?= (isset($quote->payment_term) ? $quote->payment_term : '') ?>');
        localStorage.setItem('slitems', JSON.stringify(<?= $quote_items; ?>));
        <?php } ?>
		
        <?php if($this->input->get('customer')) { ?>
			if (!localStorage.getItem('slitems')) {
				localStorage.setItem('slcustomer', <?=$this->input->get('customer');?>);
			}
        <?php } ?>
		
        <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
        if (!localStorage.getItem('sldate')) {
            $("#sldate").datetimepicker({
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
		
		$(document).on('change', '#receivable_account', function (e) {
            localStorage.setItem('receivable_account', $(this).val());
        });
        if (receivable_account = localStorage.getItem('receivable_account')) {
            $('#receivable_account').val(receivable_account);
        }

        $(document).on('change', '#stock_deduction', function (e) {
            localStorage.setItem('stock_deduction', $(this).val());
        });
        if (stock_deduction = localStorage.getItem('stock_deduction')) {
            $('#stock_deduction').val(stock_deduction);
        }
		
        $(document).on('change', '#sldate', function (e) {
            localStorage.setItem('sldate', $(this).val());
        });
        if (sldate = localStorage.getItem('sldate')) {
            $('#sldate').val(sldate);
        }
        <?php } ?>
        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
		$(document).on('change', '#agency_id', function (e) {
            localStorage.setItem('slagency', $(this).val());
        });
        if (slagency = localStorage.getItem('slagency')) {
            $('#agency_id').val(slagency);
        }
		$(document).on('change', '#slpayment_term', function (e) {
            localStorage.setItem('slpayment_term', $(this).val());
        });
        if (slpayment_term = localStorage.getItem('slpayment_term')) {
            $('#slpayment_term').val(slpayment_term);
        }
        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?=$slnumber?>');
        }
        if (!localStorage.getItem('sltax2')) {
            localStorage.setItem('sltax2', <?=$Settings->default_tax_rate2;?>);
        }
        ItemnTotals();
        $('.bootbox').on('hidden.bs.modal', function (e) {
            $('#add_item').focus();
        });		
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= ($Settings->search_by_category==1 ? site_url('sales/category_suggestions') : site_url('sales/suggestions')) ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val(),
						<?php if($this->config->item('room_rent') && isset($rental_id) && $rental_id){ ?>
							rental_id : "<?=$rental_id?>",
						<?php } ?>
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
               if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
					<?php if($Settings->search_by_category==1){ ?>
						var products = ui.item.products;
                        $.each(products,function(){
                            if($(this)[0]){
                                var row = add_invoice_item($(this)[0]);
                            }
                        });
                        $(this).val('');
                    <?php } else { ?>
                        var row = add_invoice_item(ui.item);
						if (row)
							$(this).val('');
                    <?php } ?>
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $(document).on('change', '#gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#slcustomer').val()) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');
                        } else {
                            $('#gc_details').html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
        });
		
		var old_exchange_rate;
		$(document).on("focus", '.exchange_rate', function () {
			old_exchange_rate = $(this).val();
		}).on("change", '.exchange_rate', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_exchange_rate);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});    

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_sale'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                
				<?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("sales/add", $attrib);
				
                if ($quote_id) {
                    echo form_hidden('quote_id', $quote_id);
					echo form_hidden('sale_order_id', $sale_order_id);
					echo form_hidden('delivery_id', isset($delivery_id)? $delivery_id: 0);
					echo form_hidden('rental_id', isset($rental_id)? $rental_id: '');
					echo form_hidden('fuel_sale_id', isset($fuel_sale_id)? $fuel_sale_id: '');
					echo form_hidden('fuel_customers', isset($fuel_customers)? json_encode($fuel_customers): '');
					echo form_hidden('groups_delivery', isset($groups_delivery)? $groups_delivery: '');
					echo form_hidden('consignment_id', isset($consignment_id)? $consignment_id: 0);
					echo form_hidden('repair_id', isset($repair_id)? $repair_id: 0);
                }
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "sldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>														
						<?php } ?>
						<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
							<div class="form-group">
								<?= lang("reference_no", "slref"); ?>
								<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref"'); ?>
							</div>
						</div>
                        <?php  if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "slbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'slbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );

                            echo form_input($biller_input);
						}
						
						if($Settings->project == 1){ ?>
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("project", "project"); ?>
										<div class="no-project">
											<?php
											$pj[''] = '';
											if(isset($projects) && $projects){
												foreach ($projects as $project) {
													$pj[$project->id] = $project->name;
												}
											}
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
										</div>
									</div>
								</div>
							<?php } else { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("project", "project"); ?>
										<div class="no-project">
											<?php
											$pj[''] = ''; 
											if(isset($user) && isset($projects) && $projects){
												$right_project = json_decode($user->project_ids);
												if($right_project){
													foreach ($projects as $project) {
														if(in_array($project->id, $right_project)){
															$pj[$project->id] = $project->name;
														}
													}
												}
												
											}
											echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
											?>
										</div>
									</div>
								</div>
							<?php } ?>
						<?php } 
						if($this->config->item('quotation')) {  ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("qa_reference", "qa_reference"); ?>
									<?php
									$qa_opts[""] =  lang('select_quotation') ;
									if($quotations){
										foreach ($quotations as $quotation) {
											$qa_opts[$quotation->id] = $quotation->reference_no;
										}
									}
									
									echo form_dropdown('qa_reference', $qa_opts, (isset($qa_id) ? $qa_id: ''), 'id="qa_reference" class="form-control input-tip select" style="width:100%;" ');
									?>
								</div>
							</div>
						<?php } if($this->config->item('saleorder')) {  ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("so_reference", "so_reference"); ?>
									<?php
									$so_opts[""] =  lang('select_so') ;
									if($saleorders){
										foreach ($saleorders as $saleorder) {
											$so_opts[$saleorder->id] = $saleorder->reference_no;
										}
									}
									echo form_dropdown('so_reference', $so_opts, (isset($so_id) ? $so_id: ''), 'id="so_reference" class="form-control input-tip select" style="width:100%;" ');
									?>
								</div>
							</div>
						<?php } if($this->config->item('deliveries')) {  ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("dn_reference", "dn_reference"); ?>
									<?php
									$dn_opts[""] =  lang('select_dn') ;
									if($deliveries){
										foreach ($deliveries as $delivery) {
											$dn_opts[$delivery->id] = $delivery->do_reference_no;
										}
									}
									echo form_dropdown('dn_reference', $dn_opts, (isset($dn_id) ? $dn_id: ''), 'id="dn_reference" class="form-control input-tip select" style="width:100%;" ');
									?>
								</div>
							</div>
						<?php } ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("warehouse", "slwarehouse"); ?>
								<?php
								foreach ($warehouses as $warehouse) {
									$wh[$warehouse->id] = $warehouse->name;
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
						<?php foreach($currencies as $currency){ ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("exchange_rate"." (".$currency->code.")", "exchange_rate"); ?>
									<?php echo form_input('exchange_rate_'.$currency->code, $currency->rate, 'class="form-control input-tip exchange_rate"'); ?>
								</div>
							</div>	
						<?php } ?>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-4">
                                        <div class="form-group" style="margin-bottom: 13px;">
                                            <?= lang("customer", "slcustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="toogle-customer-read-attr" class="external">
                                                        <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="<?= site_url('customers/add'); ?>" id="add-customer"class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
									<?php if ($this->config->item('saleman')==true) { 
										if($Owner || $Admin || $GP['sales-assign_sales']){
											$hide_saleman = '';
										}else{
											$hide_saleman = ' style="display:none !important;"';
										}
									?>
										<div class="col-md-4" <?= $hide_saleman ?>>
											<div class="form-group">
											<?= lang("saleman", "saleman"); ?>
											<?php 
												$opsalemans[""] = lang('select').' '.lang('saleman');
												foreach($salemans as $saleman){
													$opsalemans[$saleman->id] = $saleman->first_name .' '.$saleman->last_name;
												}
											?>
											<?= form_dropdown('saleman_id', $opsalemans, (isset($quote->saleman_id)?$quote->saleman_id:$this->session->userdata('user_id')), ' id="saleman_id" class="form-control" required="required"'); ?>
											</div>
										</div>
										<?php if ($this->config->item('saleman_commission')==true){ ?>
											<div class="col-md-4" <?= $hide_saleman ?>>
												<div class="form-group">
												<?= lang("commission", "commission"); ?>
												<?php echo form_input('commission', (isset($_POST['commission']) ? $_POST['commission'] : ''), 'class="form-control input-tip" id="commission"'); ?>
												</div>
											</div>
										<?php } ?>
									<?php } ?>

									<?php if($this->config->item("agency")){ ?>
										<div class="col-md-4">
											<div class="form-group">
											<?= lang("agency", "agency"); ?>
											<?php 
												$opagencies[""] = array();
												if($agencies){
													foreach($agencies as $agency){
														$opagencies[$agency->id] = $agency->first_name .' '.$agency->last_name .' '.$agency->agency_commission."%";
													}
												}
											?>
											<?= form_dropdown('agency_id[]', $opagencies, '', ' id="agency_id" multiple class="form-control" required="required"'); ?>
											</div>
										</div>
									<?php } ?>
									<?php if($this->config->item("room_rent") && (isset($rental_id) && $rental_id > 0)){ 
                                            $from_date = $this->cus->hrsd($quote->from_date);
                                            $to_date = $this->cus->hrsd($quote->to_date);
                                            $form_label = '';
                                            if(isset($checked_out_date)){
                                                $to_date = $checked_out_date;
                                                $form_label = "<b style='color:red;'>(".lang('checked_out').")</b>";
                                                echo form_hidden("rental_status", "checked_out");
                                            }
                                        ?>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("from_date", "from_date"); ?>
												<div class="input-group">
													<?= form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] : $from_date), 'class="form-control" id="from_date" autocomplete="off" required="required" readonly="readonly"'); ?>
													<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
														<i class="fa fa-calendar" aria-hidden="true"></i>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("to_date", "to_date"); ?> <?= $form_label ?>
												<div class="input-group">
													<?= form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] : $to_date), 'class="form-control" id="to_date" autocomplete="off" readonly="readonly"'); ?>
													<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
														<i class="fa fa-calendar" aria-hidden="true"></i>
													</div>
												</div>
											</div>
										</div>
									<?php } ?>
									
                                    <?php if($receivable_account){ ?>
                                        <div class="col-md-4">    
                                            <div class="form-group">
                                                <?= lang("receivable_account", "receivable_account"); ?>
                                                <select name="receivable_account" id="receivable_account" class="form-control receivable_account" style="width:100%">
                                                    <?= $receivable_account ?>
                                                </select>  
                                            </div>
                                        </div> 
									<?php } if($Settings->car_operation == 1){ ?>
									
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("vehicle_model", "vehicle_model"); ?>
											<?php echo form_input('vehicle_model', (isset($_POST['vehicle_model']) ? $_POST['vehicle_model'] : ''), 'class="form-control input-tip" id="vehicle_model"'); ?>
										</div>
									</div>
									
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("vehicle_kilometers", "vehicle_kilometers"); ?>
											<?php echo form_input('vehicle_kilometers', (isset($_POST['vehicle_kilometers']) ? $_POST['vehicle_kilometers'] : ''), 'class="form-control input-tip" id="vehicle_kilometers"'); ?>
										</div>
									</div>
									
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("vehicle_vin_no", "vehicle_vin_no"); ?>
											<?php echo form_input('vehicle_vin_no', (isset($_POST['vehicle_vin_no']) ? $_POST['vehicle_vin_no'] : ''), 'class="form-control input-tip" id="vehicle_vin_no"'); ?>
										</div>
									</div>
									
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("vehicle_plate", "vehicle_plate"); ?>
											<?php echo form_input('vehicle_plate', (isset($_POST['vehicle_plate']) ? $_POST['vehicle_plate'] : ''), 'class="form-control input-tip" id="vehicle_plate"'); ?>
										</div>
									</div>
									
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("job_number", "job_number"); ?>
											<?php echo form_input('job_number', (isset($_POST['job_number']) ? $_POST['job_number'] : ''), 'class="form-control input-tip" id="job_number"'); ?>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("mechanic", "mechanic"); ?>
											<?php echo form_input('mechanic', (isset($_POST['mechanic']) ? $_POST['mechanic'] : ''), 'class="form-control input-tip" id="mechanic"'); ?>
										</div>
									</div>
									<?php } ?>
                                </div>
                            </div>

                        </div>


                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                        <?php if (($Owner || $Admin || $GP['products-add']) && !isset($rental_id)) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
                                                <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                            </a>
                                        </div>
                                        <?php } if (($Owner || $Admin || $GP['sales-add_gift_card']) && !isset($rental_id)) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="sellGiftCard" class="tip" title="<?= lang('sell_gift_card') ?>">
                                               <i class="fa fa-2x fa-credit-card addIcon" id="addIcon"></i>
                                            </a>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="slTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
										<?php if ($Settings->qty_operation) {
												$head_row = 'rowspan="2"';
											}else{
												$head_row = '';
											}
										?>
                                        <tr>
											<?php if($Settings->search_by_category==1){ ?>
												<th <?= $head_row ?> class="col-md-4"><?= lang('category') . ' (' . lang('product') .')'; ?></th>
											<?php }else{?>
												<th <?= $head_row ?> class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>	
											<?php } if ($Settings->show_qoh == 1) { ?>	
												<th <?= $head_row ?> class="col-md-1"><?= lang("qoh"); ?></th>
											<?php } if($this->config->item('saleman_commission') && $Settings->product_commission == 1){
												echo '<th '.$head_row.' class="col-md-1">' . lang("saleman") . '</th>';
											}											
											if ($Settings->product_expiry) {
                                                echo '<th '.$head_row.' class="col-md-1">' . lang("expiry_date") . '</th>';
                                            }
                                            if ($Settings->product_serial) {
                                                echo '<th '.$head_row.' class="col-md-1">' . lang("serial_no") . '</th>';
                                            }
                                            ?>
                                            <th <?= $head_row ?> class="col-md-1"><?= lang("unit_price"); ?></th>
											
											<?php if ($Settings->qty_operation) { ?>											
												<th class="col-md-4" colspan="4"><?= lang("quantity_operation"); ?></th>
												<th <?= $head_row ?> class="col-md-1"><?= lang("total_quantity"); ?></th>
											<?php }else{ ?>
												<th <?= $head_row ?> class="col-md-1"><?= lang("quantity"); ?></th>
                                            <?php } if ($Settings->show_unit == 1) { ?>	
												<th <?= $head_row ?> class="col-md-1"><?= lang("unit"); ?></th>
                                            <?php } if ($Settings->foc == 1) {
												echo '<th '.$head_row.' class="col-md-1">' . $this->lang->line("foc") . '</th>';
                                            }if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th '.$head_row.' class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                            }
                                            ?>
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th '.$head_row.' class="col-md-1">' . lang("product_tax") . '</th>';
                                            }
                                            ?>
                                            <th <?= $head_row ?>>
                                                <?= lang("subtotal"); ?>
                                                (<span class="currency "><?= $default_currency->code ?></span>)
                                            </th>
                                            <th <?= $head_row ?> style="width: 30px !important; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
										<?php if ($Settings->qty_operation) { ?>
											<tr>
												<th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('width') ?></th>
												<th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('height') ?></th>
												<th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('square') ?></th>
												<th style="color:white; text-align:center; background-color:#428BCA; border:1px solid #357EBD"><?= lang('quantity') ?></th>
											</tr>
										<?php } ?>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_tax", "sltax2"); ?>
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang("select") . ' ' . lang("order_tax") . '" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("order_discount", "sldiscount"); ?>
                                    <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        
                        <?php if(!isset($repair_id) && !isset($rental_id)){ ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("shipping", "slshipping"); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>
                                </div>
                            </div>

                        <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("document", "document") ?>
                                    <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                        data-show-preview="false" class="form-control file">
                                </div>
                            </div>

                        

                        <div style="display:none !important" class="col-sm-4">
                            <div class="form-group">
                                <?= lang("sale_status", "slsale_status"); ?>
                                <?php $sst = array('completed' => lang('completed'), 'pending' => lang('pending'));
                                echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"'); ?>

                            </div>
                        </div>

						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_term", "slpayment_term"); ?>
                                <?php 
								$pt[''] = '';
								foreach ($paymentterms as $paymentterm) {
									$pt[$paymentterm->id] = $paymentterm->description;
								}
								if (isset($quote) && $quote) {
                                    if(!isset($quote->payment_term) || $quote->payment_term == ''){
                                        $quote->payment_term = $Settings->default_payment_term;
                                    }
                                    echo form_dropdown('payment_term', $pt, (isset($_POST['payment_term']) ? $_POST['payment_term'] : $quote->payment_term), 'class="form-control input-tip" id="slpayment_term"'); 

                                }else{
                                    echo form_dropdown('payment_term', $pt, (isset($_POST['payment_term']) ? $_POST['payment_term'] : "N/A"), 'class="form-control input-tip" id="slpayment_term"');  
                                }
				
								?>
                            </div>
                        </div>

						<?php if((isset($delivery_id) && $delivery_id) || (isset($groups_delivery) && $groups_delivery) || isset($repair_id)){ 
							echo form_hidden('stock_deduction', 1);	
						} else { ?>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("stock_deduction", "stock_deduction"); ?>
									<?php $sd = array(1 => lang('completed'), 0 => lang('pending'));
									echo form_dropdown('stock_deduction', $sd, 1, 'class="form-control input-tip" id="stock_deduction"'); ?>
								</div>
							</div>
						<?php } ?>

						<div class="clearfix"></div>

						<?php if(isset($so_deposit) && $so_deposit > 0) { ?>
							<div class="col-sm-4">
								<?= lang("so_deposit", "v_so_deposit"); ?>
								<?php echo form_input('v_so_deposit', $this->cus->formatMoney($so_deposit), 'class="form-control input-tip text-right" readonly="true"'); ?>
								<input type="hidden"  value="<?= $so_deposit ?>" name="so_deposit"/>
							</div>
						<?php } ?>

                        <?php if(isset($checked_out_date) && $checked_out_date && isset($quote->deposit) && $quote->deposit>0){ ?>
                            <div class="col-sm-4">
								<?= lang("rental_deposit", "rental_deposit"); ?>
								<?php echo form_input('rental_deposit', $this->cus->formatMoney($quote->deposit), 'class="form-control input-tip text-right" readonly="true"'); ?>
								<input type="hidden"  value="<?= $quote->deposit ?>" name="so_deposit"/>
							</div>
                        <?php } ?>

                        <?php if ($Owner || $Admin || $GP['sales-payments']) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_status", "slpayment_status"); ?>
                                <?php $pst = array('pending' => lang('pending'),  'partial' => lang('partial'), 'paid' => lang('paid'));
                                echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"'); ?>
                            </div>
                        </div>
                        <?php 
                        } else {
                            echo form_hidden('payment_status', 'pending');
                        }
                        ?>
                        <div class="clearfix"></div>

                        <div id="payments" style="display: none;">
                            <div class="col-md-12">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <?= lang("payment_reference_no", "payment_reference_no"); ?>
                                                    <?= form_input('payment_reference_no', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input name="amount-paid" type="text" id="amount_1"
                                                               class="pa form-control kb-pad amount"/>
                                                    </div>
                                                    <div class="form-group gc" style="display: none;">
                                                        <?= lang("gift_card_no", "gift_card_no"); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no"
                                                               class="pa form-control kb-pad"/>

                                                        <div id="gc_details"></div>
                                                    </div>
                                                </div>
                                            </div>
											<div class="col-sm-3">
												<div class="payment">
													<div class="form-group">
														<?= lang("discount", "discount"); ?>
														<input name="payment_discount" value="0" type="text" class="form-control" id="payment_discount"/>
													</div>
												</div>
											</div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <?= lang("paying_by", "paid_by_1"); ?>&nbsp;&nbsp;<small style="color:red;" id="amount_deposit"></small>
                                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                                        <?= $this->cus->cash_opts(); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>

                                        <div class="form-group">
                                            <?= lang('payment_note', 'payment_note_1'); ?>
                                            <textarea name="payment_note" id="payment_note_1"
                                                      class="pa form-control kb-text payment_note"></textarea>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("sale_note", "slnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("staff_note", "slinnote"); ?>
                                        <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ""), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>																				
									</div>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_sale', lang("submit"), 'id="add_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
								<?php echo form_submit('add_sale_next', lang("submit_and_next"), 'id="add_sale_next" class="btn btn-info" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> : <span class="totals_val pull" id="titems">0</span></td>
                            <td><?= lang('total') ?> : <span class="totals_val pull" id="total">0.00</span></td>
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <td><?= lang('order_discount') ?> : <span class="totals_val pull" id="tds">0.00</span></td>
                            <?php } ?>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> : <span class="totals_val pull" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('shipping') ?> : <span class="totals_val pull" id="tship">0.00</span></td>
                            <td><input id="g_total" type="hidden" name="g_total"/><?= lang('grand_total') ?> : <span class="totals_val pull" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>
				
                <?php echo form_close(); ?>

            </div>
        </div>
    </div>
</div>



<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group hidden">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial">
                            </div>
                        </div>
						<div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <div id="pserials-div"></div>
								<input type="hidden" class="form-control" id="pserial">
								<input type="hidden" class="form-control" id="pscost">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
					<?php  if(in_array('bom',$this->config->item('product_types'))) { ?>
                        <div class="form-group">
                            <label for="pbom_type" class="col-sm-4 control-label"><?= lang('bom_type') ?></label>
                            <div class="col-sm-8">
                                <div id="pbom_type-div"></div>
                            </div>
                        </div>      
					<?php } if ($Settings->attributes == 1) { ?>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php } if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
					
					<?php if($this->config->item('product_currency')==true) { ?>
                        <div class="form-group">
                            <label for="pproduct_currency" class="col-sm-4 control-label"><?= lang('product_currency') ?></label>
                            <div class="col-sm-8">
                                <div id="pproduct_currency-div"></div>
                            </div>
                        </div>
					<?php } ?>
					
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
					
					<?php if ($Settings->product_additional){ ?>
						<div class="form-group">
							<label for="paditional" class="col-sm-4 control-label"><?= lang('product_additional') ?></label>
							<div class="col-sm-8">
								<div id="paditional-div"></div>
							</div>
						</div>
                    <?php } if ($Settings->product_formulation){ ?>
						<div class="form-group">
							<label for="pformulation" class="col-sm-4 control-label"><?= lang('product_formulation') ?></label>
							<div class="col-sm-8">
								<div id="pformulation-div"></div>
							</div>
						</div>
					<?php } ?>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:20%; display:none !important"><?=lang('product_tax');?></th>
                            <th style="width:20%; display:none !important"><span id="pro_tax"></span></th>
							<th style="width:25%;"><?=lang('total');?></th>
                            <th style="width:25%;"><span id="pro_total"></span></th>
                        </tr>
                    </table>
						
					<?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
						<div class="panel panel-default">
							<div class="panel-heading"><?= lang('calculate_product_discount'); ?></div>
							<div class="panel-body">

								<div class="form-group">
									<label for="tpdiscount" class="col-sm-4 control-label"><?= lang('discount') ?></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="tpdiscount">
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					
					<?php if($this->config->item("room_rent")){ ?>
						<div id="electricity">
							<div class="panel panel-default">
								<div class="panel-heading"><?= lang('electricity_or_water'); ?></div>
								<div class="panel-body">
									<div class="form-group">
										<label for="old_number" class="col-sm-4 control-label"><?= lang('old_number') ?></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" id="old_number" <?=(!$Admin && !$Owner?'readonly':'')?>>
										</div>
									</div>
									<div class="form-group">
										<label for="new_number" class="col-sm-4 control-label"><?= lang('new_number') ?></label>
										<div class="col-sm-8">
											<input type="text" class="form-control" id="new_number">
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					
					<div class="panel panel-default">
                        <div class="panel-heading"> <?= lang('calculate_unit_price'); ?></div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="pprice" class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="psubtotal">
                                        <div class="input-group-addon" style="padding: 2px 8px;">
                                            <a href="#" id="calculate_unit_price" class="tip" title="<?= lang('calculate_unit_price'); ?>">
                                                <i class="fa fa-calculator"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					
					<input type="hidden" id="hpro_total"/>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
					
                </form>
								
				<div class="clearfix"></div>
			
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>
                        <div style="width:64%; padding-left:2.55%" class="col-sm-8 input-group">
                            <input type="text" class="form-control" id="mcode">
							<span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;">
                                <i class="fa fa-random"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>

                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
					<div class="form-group">
                        <label for="mcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcost">
                        </div>
                    </div>
					<?php if($Settings->overselling && ($Owner || $Admin || $GP['products-add'])){ ?>
						<div class="form-group">
							<label for="add_product" class="col-sm-4 control-label"><?= lang('add_to_list_products') ?></label>
							<div class="col-sm-8">
								<select id="add_product" class="add_product" style="width:100%">
									<option value="0"><?= lang('no') ?></option>
									<option value="1"><?= lang('yes') ?></option>
								</select>
							</div>
						</div>
					<?php } ?>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="comboModal" tabindex="-1" role="dialog" aria-labelledby="comboModalLabel" aria-hidden="true" >
    <div class="modal-dialog" style="width:50%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="comboModalLabel"></h4>
            </div>
            <div class="modal-body" style="margin-top:-15px !important;">
				<label class="table-label"><?= lang("combo_products"); ?></label>
				<table id="comboProduct" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
					<thead>
						<tr>
							<th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
							<?php if ($Settings->qty_operation) { ?>
								<th><?= lang('width') ?></th>
								<th><?= lang('height') ?></th>
							<?php } ?>
							<th><?= lang('quantity') ?></th>
							<th><?= lang('price') ?></th>
							<th width="3%">
								<a id="add_comboProduct" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
							</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
            </div>
			
			
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editCombo"><?=lang('submit')?></button>
            </div>
			
        </div>
    </div>
</div>




<div class="modal" id="prProModal" tabindex="-1" role="dialog" aria-labelledby="prProModalLabel" aria-hidden="true" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="prProModalLabel"></h4>
            </div>
            <div class="modal-body" style="margin-top:-15px !important; margin-bottom: -50px !important">
				<label class="table-label"><?= lang("promotion_condition"); ?></label>
				<table id="proCondition" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
					<thead>
						<tr>
							<th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
							<th><?= lang('min_quantity') ?></th>
							<th><?= lang('max_quantity') ?></th>
							<th><?= lang('free_quantity') ?></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
            </div>
			
			
			<div class="modal-body">
				<label class="table-label"><?= lang("promotion_product"); ?></label>
				<table id="proProduct" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
					<thead>
						<tr>
							<th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
							<th><?= lang('quantity') ?></th>
							<th width="3%">
								<a id="add_proProduct" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
							</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
            </div>
			
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editPromotion"><?=lang('submit')?></button>
            </div>
			
        </div>
    </div>
</div>



<div class="modal" id="cmModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
			
				<?php if($suspend_option != 0){ ?>
					<div class="form-group">
						<?= lang('tags', 'tags'); ?>
						<?php
						$tags1 = array(0 => lang('no'));
						foreach($tags as $tag){
							$tags1[$tag->name] = $tag->name;
						}
						?>
						<?= form_dropdown('tags', $tags1, '', 'class="form-control" id="tags" style="width:100%;"'); ?>
					</div>
					<script type="text/javascript">
						$(function(){
							$("#tags").on("change",function(){
								var tags = $(this).val();
								$("#icomment").val(tags);
							});
						});
					</script>
				<?php } ?>
                <div class="form-group">
                    <?= lang('comment', 'icomment'); ?>
                    <?= form_textarea('comment', '', 'class="form-control skip" id="icomment" style="height:80px;"'); ?>
                </div>
                <div class="form-group hidden">
                    <?= lang('ordered', 'iordered'); ?>
                    <?php
                    $opts = array(0 => lang('no'), 1 => lang('yes'));
                    ?>
                    <?= form_dropdown('ordered', $opts, '', 'class="form-control" id="iordered" style="width:100%;"'); ?>
                </div>
				
                <input type="hidden" id="irow_id" value=""/>
				
            </div>
			
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
			
        </div>
    </div>
</div>

<div class="modal" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?= lang("card_no", "gccard_no"); ?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                                                                                           id="genNo"><i
                                    class="fa fa-cogs"></i></a></div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname"/>

                <div class="form-group">
                    <?= lang("value", "gcvalue"); ?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("price", "gcprice"); ?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("customer", "gccustomer"); ?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("expiry_date", "gcexpiry"); ?>
                    <?php echo form_input('gcexpiry', $this->cus->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
		$('#qa_reference').on('change',function(){
			var qa_reference = $(this).val();
			location.replace(site.base_url+"sales/add/"+qa_reference);
		});
		$('#so_reference').on('change',function(){
			var so_reference = $(this).val();
			location.replace(site.base_url+"sales/add/"+so_reference+"/1");
		});
		$('#dn_reference').on('change',function(){
			var dn_reference = $(this).val();
			location.replace(site.base_url+"sales/add/"+dn_reference+"/2");
		});
		$(".combo_product:not(.ui-autocomplete-input)").live("focus", function (event) {
			$(this).autocomplete({
				source: '<?= site_url('products/suggestions'); ?>',
				minLength: 1,
				autoFocus: false,
				delay: 250,
				response: function (event, ui) {
					if (ui.content.length == 1 && ui.content[0].id != 0) {
						ui.item = ui.content[0];
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
						$(this).autocomplete('close');
						$(this).removeClass('ui-autocomplete-loading');
					}
				},
				select: function (event, ui) {
					event.preventDefault();
					if (ui.item.id !== 0) {
						var parent = $(this).parent().parent();
						parent.find(".combo_product_id").val(ui.item.id);
						parent.find(".combo_name").val(ui.item.name);
						parent.find(".combo_code").val(ui.item.code);
						parent.find(".combo_price").val(formatDecimal(ui.item.price));
						parent.find(".combo_qty").val(formatDecimal(1));
						if (site.settings.qty_operation == 1) {
							parent.find(".combo_width").val(formatDecimal(1));
							parent.find(".combo_height").val(formatDecimal(1));
						}
						$(this).val(ui.item.label);
					} else {
						bootbox.alert('<?= lang('no_match_found') ?>');
					}
				}
			});
		});
		
		
		$(".promotion_product:not(.ui-autocomplete-input)").live("focus", function (event) {
			$(this).autocomplete({
				source: '<?= site_url('products/suggestions'); ?>',
				minLength: 1,
				autoFocus: false,
				delay: 250,
				response: function (event, ui) {
					if (ui.content.length == 1 && ui.content[0].id != 0) {
						ui.item = ui.content[0];
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
						$(this).autocomplete('close');
						$(this).removeClass('ui-autocomplete-loading');
					}
				},
				select: function (event, ui) {
					event.preventDefault();
					if (ui.item.id !== 0) {
						var parent = $(this).parent().parent();
						parent.find(".promotion_product_id").val(ui.item.id);
						$(this).val(ui.item.label);
					} else {
						bootbox.alert('<?= lang('no_match_found') ?>');
					}
				}
			});
		});
						
        $('#gccustomer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {						
                        return {results: data.results};
						
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }					
                }
            }
        });
		
		$('#commission').change(function (e) {
			localStorage.setItem('slcommission', $(this).val());
		});
		
		$('#saleman_id').change(function (e) {
			var saleman_id = $(this).val();
			$.ajax({
				url : site.base_url + "sales/getUser",
				dataType : "JSON",
				type : "GET",
				data : { saleman_id : saleman_id },
				success : function(data){
					localStorage.setItem('slcommission', data.saleman_commission);
					$('#commission').val(data.saleman_commission);
				}
			});
			localStorage.setItem('slsaleman', saleman_id);
		});
		
		if (slcustomer = localStorage.getItem('slcustomer')) {
            $customer = slcustomer;
        }else{
			$customer = 0;
		}
		$('#slcustomer').on("select2-selecting", function(e) {		   
		   var customer = e.val; $customer = e.val;
		   $.ajax({
				  url : site.base_url + "sales/get_company",
				  dataType : "JSON",
				  type : "GET",
				  data : { customer : customer },
				  success : function(data){
					if(data.saleman_id > 0){
						$("#saleman_id").select2("val",data.saleman_id);
						$("#commission").val(data.saleman_commission);
						localStorage.setItem('slsaleman',data.saleman_id);
						localStorage.setItem('slcommission',data.saleman_commission);						
					}
					
					<?php if($Settings->installment==1){ ?>
						var installment = JSON.parse(data.installment);
						if(installment.id > 0){
							var reference_no = installment.reference_no;
							bootbox.confirm({
								message: "<?= lang("customer_has_installment") ?>",
								buttons: {
									confirm: {
										label: 'Yes',
										className: 'btn-success'
									},
									cancel: {
										label: 'No',
										className: 'btn-danger'
									}
								},
								callback: function (result) {
									if(result){
										$(this).select2('val',customer);
									}else{
										$('#slcustomer').select2('val','');
										if (localStorage.getItem('slcustomer')) {
											localStorage.removeItem('slcustomer');
										}
									}
								}
							});
						}
					<?php } ?>
				}
		   });
		   
		   $.ajax({
				  url : site.base_url + "sales/get_credit",
				  dataType : "JSON",
				  type : "GET",
				  data : { customer : customer },
				  success : function(balance){
					 if(balance > 0){
						  bootbox.alert("Customer have over credit limit");
						  $("#slcustomer").select2("val","");	
						  localStorage.removeItem('slcustomer');
						  return false;
					 }			
				  }
		   });
		   
		   <?php if($Settings->customer_deposit_alerts == 1){ ?>
			   $.ajax({
					  url : site.base_url + "sales/get_deposit",
					  dataType : "JSON",
					  type : "GET",
					  data : { customer : customer },
					  success : function(re){
						  var deposit_amount = parseFloat(re.deposit_amount);						  
						  if(deposit_amount > 0){
							  bootbox.alert("<?= lang('customer_deposit_alerts') ?>"+ deposit_amount);
						  }
					  }
			   });
		   <?php } ?>
		   
		});
		
        $('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
		
		$("#paid_by_1").on("change",function(){		
			var paid_by = $(this).val();
			var customer =  $customer;
			var gtotal = parseFloat($("#gtotal").text());
			if(paid_by == 'deposit'){
				$.ajax({
					  url : site.base_url + "sales/get_deposit",
					  dataType : "JSON",
					  type : "GET",
					  data : { customer : customer },
					  success : function(re){
						  var deposit_amount = parseFloat(re.deposit_amount);
						  var deposit_value = " | <?= lang("deposit_amount") ?>=" + deposit_amount;
						  if(deposit_amount > gtotal){							  
							  $("#amount_1").val(gtotal);
						  }else{
							  $("#amount_1").val(deposit_amount);
						  }						  
						  $("#amount_deposit").html(deposit_value);
					  }
			   });
			}
		});
		
		$("#slbiller").change(biller); biller();
		function biller(){
			var biller = $("#slbiller").val();
			var project = 0;
			<?php if ($quote && isset($quote->project_id) && $quote->project_id) { ?>
				project = "<?= $quote->project_id ?>";
			<?php } ?>
			$.ajax({
				url : "<?= site_url("sales/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
		
		var old_tp_discount;
		$('#tpdiscount').focus(function () {
			old_tp_discount = $(this).val();
		}).change(function () {
			var new_tp_discount = $(this).val() ? $(this).val() : '0';
			if (is_valid_discount(new_tp_discount)) {
				var pro_total = $('#hpro_total').val()-0;
				if (new_tp_discount.indexOf("%") !== -1) {
					var pds = new_tp_discount.split("%");
					if (!isNaN(pds[0])) {
						var discount = parseFloat(((pro_total) * parseFloat(pds[0])) / 100);
					} else {
						var discount = parseFloat(new_tp_discount);
					}
				} else {
					var discount = parseFloat(new_tp_discount);
				}

				var pro_quantity = $('#pquantity').val()-0;
				var pro_price = $('#pprice').val()-0;
				var g_pro_total = pro_quantity * pro_price;
				var n_pro_total = pro_total - discount;
				var pro_discount = (g_pro_total - n_pro_total) / pro_quantity;

				$('#pdiscount').val(pro_discount);
				$('#pdiscount').change();
				$(this).val('');
				return;
			} else {
				$(this).val(old_tp_discount);
				bootbox.alert(lang.unexpected_value);
				return;
			}

		});
		<?php if((!$Owner && !$Admin) && !empty($rental_id) && $rental_id>0){ ?>
			$("#add_item, #to_date").prop("readonly","readonly");
			$("#to_date").prop("class","form-control");
			$("#addManually, #sellGiftCard").parent().hide();
		<?php } ?>
    });
</script>
