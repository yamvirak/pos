<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- <div class="modal-dialog no-modal-header" role="document">
	<div class="modal-content">
		<div class="modal-body"> -->
<?php 
	if($customer->gender=='male'){
    $customer->gender = 'ប្រុស';
    }else{
    $customer->gender = 'ស្រី';
}
?>

<div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
		<div class="modal-body">
			<button type="button" class="close hidden" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
		<?php if($Settings->watermark){
			echo '<p class="bg-text">';
				for($b=0; $b < 7; $b++){
					echo $biller->name.'<br>';
				}
			echo '</p>';
		}
		$hide_print = '';
		if($print==1){
			$hide_print = 'display:none !important;';
		}else if($print==2){
			if($Settings->watermark){
				echo '<p class="bg-text" style="transform:rotate(600deg) !important">';
					for($b=0; $b < 7; $b++){
						echo lang('re-print').'<br>';
					}
				echo '</p>';	
			}else{
				echo '<p class="bg-text">';
					for($b=0; $b < 7; $b++){
						echo lang('re-print').'<br>';
					}
				echo '</p>';
			}
		}
		?>
			<div id="wrapper">
				<div id="receiptData">
					<div id="receipt-data">
						<div class="text-center">
									<table style="margin-top: 5px;">
			                            <tr>
			                                <td class="text_left">
			                                     <?= !empty($biller->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
			                                </td>
			                                <td></td>
			                                 <td class="text_center" style="width:60%">
			                                    <div>
			                                        <strong style="font-size:20px;font-family: Khmer OS Muol Light;"><?= $biller->company;?></strong><br>
			                                        <strong style="font-size:20px";><?= $biller->name;?></strong>
			                                    </div>
			                                    <div class="font_address"><?= $biller->address?></div>
			                                    <div class="font_address"><?= lang('tel').' : '. $biller->phone ?></div> 
			                                    <div class="font_address"><?= lang('email').' : '. $biller->email ?></div>   
			                                </td> 
			                                <td class="text_center" style="width:20%">
			                                    <?= $this->cus->qrcode('link', urlencode(site_url('rentals/view/' . $inv->id)), 2); ?>
			                                </td>
			                            </tr>
			                        </table>
								
							<h2 style="font-family:Moul; font-size:18px;"><?= lang("ប័ណ្ណចូលស្នាក់នៅ") ?></h2>
							<h2 style="font-weight:bold; margin-top:0px;"><?= lang("CHECK-IN FORM") ?></h2>
						</div>
						<div style="clear:both;"></div>
						<table class="table table-striped table-condensed">
							<tr>
								<td colspan="4" style="background:#CCC; font-weight:bold;">
									<b>
										<?=lang("personal_infomation")?>
									</b>
								</td>
							</tr>
							<tr>
								<td><?= lang("name") ?></td>
								<td>: <?= ($customer->company && $customer->company != '-' ? $customer->company : $customer->name) ?></td>
								<td><?= lang("gender") ?></td>
								<td>: <?= $customer->gender; ?></td>
							</tr>
							<tr>
								<td><?= lang("address") ?></td>
								<td colspan="3">: <?= $customer->address; ?></td>
							</tr>
							<tr>
								<td><?= lang("nationality") ?></td>
								<td>: <?= $customer->nationality; ?></td>
								<td><?= lang("nric_number") ?></td>
								<td>: <?= $customer->nric; ?></td>
							</tr>
							<tr>
								<td><?= lang("dob") ?></td>
								<td>: <?= $this->cus->hrsd($customer->dob); ?></td>
								<td><?= lang("telephone") ?></td>
								<td>: <?= $customer->phone; ?></td>
							</tr>
							<tr>
								<td colspan="4" style="background:#CCC; font-weight:bold;">
									<b>
										<?=lang("room_details")?>
									</b>
								</td>
							</tr>
							<tr>
								<td><?= lang("room_number") ?></td>
								<td>: <?=$room->name?></td>
								<td></td>
								<td></td>
							</tr>
							<?php 
								$deposit_amount = 0;
								if($deposits){
									foreach($deposits as $deposit){
										$deposit_amount += $deposit->amount;
									}
								}
								
								$water = 0; 
								$water_no = 0;
								$electricity = 0; 
								$electricity_no = 0;
								$room_charge=0;
								$car_parking =0;
								$motor_parking =0;
								$toktok_parking =0;
								$other = 0;
								if($rows){
									foreach ($rows as $row) {
										if($row->electricity == 1){
											$water += $row->unit_price;
											$water_no = $row->old_number;
										}
										if($row->electricity == 2){
											$electricity += $row->unit_price;
											$electricity_no = $row->old_number;
										}
										if($row->electricity == 3){
											$room_charge += $row->unit_price;
										}
										if($row->electricity == 4){
											$car_parking += $row->unit_price;
										}
										if($row->electricity == 5){
											$motor_parking += $row->unit_price;
										}
										if($row->electricity == 6){
											$toktok_parking += $row->unit_price;
										}
										if($row->electricity != 1 &&
										   $row->electricity != 2 &&
										   $row->electricity != 3 &&
										   $row->electricity != 4 &&
										   $row->electricity != 5 &&
										   $row->electricity != 6
										   ){
											$other += $row->unit_price;
										}
									}
								}
							?>
							<tr>
								<td><?= lang("room_charge") ?></td>
								<td>: <?=$this->cus->formatMoney($room_charge)?></td>
								<td><?= lang("deposit") ?></td>
								<td>: <?= $this->cus->formatMoney($deposit_amount); ?></td>
							</tr>
							<tr>
								<td><?= lang("checked_in_date") ?></td>
								<td>: <?=$this->cus->hrsd($inv->checked_in)?></td>
								<td><?= lang("contract_period") ?></td>
								<td>: <?= $inv->contract_period ?> <?=($frequency?$frequency->description:'')?></td>
							</tr>
							<tr>
								<td colspan="4" style="background:#CCC; font-weight:bold;">
									<b>
										<?=lang("parking_charges")?>
									</b>
								</td>
							</tr>
							<tr>
								<td><?= lang("car_parking") ?></td>
								<td>: <?= $this->cus->formatMoney($car_parking) ?></td>
								<td><?= lang("motor_parking") ?></td>
								<td>: <?= $this->cus->formatMoney($motor_parking) ?></td>
							</tr>
							<tr>
								<td><?= lang("toktok_parking") ?></td>
								<td>: <?= $this->cus->formatMoney($toktok_parking) ?></td>
								<td><?= lang("other") ?></td>
								<td>: <?= $this->cus->formatMoney($other) ?></td>
							</tr>
							<tr>
								<td colspan="4" style="background:#CCC; font-weight:bold;">
									<b>
										<?=lang("utilities")?>
									</b>
								</td>
							</tr>
							<tr>
								<td><?= lang("water") ?></td>
								<td>: <?=($water_no)?></td>
								<td><?= lang("electricity") ?></td>
								<td>: <?=($electricity_no)?></td>
							</tr>
							<tr>
								<td><?= lang("water_fees") ?></td>
								<td>: <?=$this->cus->formatMoney($water)?></td>
								<td><?= lang("electricity_fees") ?></td>
								<td>: <?=$this->cus->formatMoney($electricity)?></td>
							</tr>
						</table>
						
					</div>
					<p><b><?= lang("important_notice");?> : </b></p>
					<p>Money, jewels and other valuables are brought to the Property (The <?= $biller->name ?>'s premises) at the guest's sole risk.										
<?= $biller->name ?> and/or the management accept no liability and shall not be responsible for any loss or damage 										
thereto and guests remains solely responsible for the safekeeping of any such items.</p>
					<div style="clear:both;"></div>
					<br/>
					<table style="width:100%; text-align:center; font-weight:bold;">
						<tr>
							<td><?= lang("guest_signature") ?> :</td>
							<td><?= lang("prepared_by") ?> :</td>
							<td><?= lang("approved_by") ?> :</td>
						</tr>
						<tr>
							<td>
								<br/><br/><br/>
								<?= ($customer->company) ?>
							</td>
							<td>
								<br/><br/><br/>
								<?= ($created_by->first_name . " ".$created_by->last_name) ?>
							</td>
							<td>
								<br/><br/><br/>
								
							</td>
						</tr>
					</table>
				</div>
				<div id="buttons" style="padding-top:10px;" class="no-print">
					<hr>
					<div class="btn-group btn-group-justified" role="group">
						<div class="btn-group" role="group">
							<button onclick="window.print()" class="btn btn-block btn-primary"><?= lang('print'); ?></button>
						</div>
						<div class="btn-group" role="group">
							<a href="<?= site_url("rentals") ?>" class="btn btn-default"><?= lang('close'); ?></a>
						</div>
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>
		</div>
	</div>
</div>