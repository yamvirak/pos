<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class=class="box-content">
		<div class="box-header">
			<h2 class="blue"><i class="fa-fw fa fa-bars"></i><?= lang('view_address'). ($company ? ' ('.$company->name.')' : '') ?></h2>
		</div>
		<?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("customers/view_address" , $attrib); ?>
        <div class="modal-body">
            <p><?= lang('customize_report'); ?></p>
			<div class="col-md-6">
				<div class="form-group">
					<?= lang('customer', 'customer'); ?>
					<?php 
						$customer_opt = "<option value='false'>".lang("select")." ".lang("customer")."</option>";
						if ($customers){
							foreach($customers as $customer){
								$customer_opt .= "<option ".($customer_id == $customer->id ? "selected" : "")." value='".$customer->id."'>".$customer->name."</option>";
							}
						} 
					?>
					<select class="form-control" name="customer">
						<?= $customer_opt ?>
					</select>
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="form-group">
					<?= lang('color', 'color_marker'); ?>
					<select class="form-control" name="color_marker">
						<option value="false"><?= lang("select")." ".lang("color") ?></option>
						<option <?= ($_POST['color_marker'] == "red-dot" ? "selected" : "") ?> value="red-dot"><?= lang("red") ?></option>
						<option <?= ($_POST['color_marker'] == "green-dot" ? "selected" : "") ?> value="green-dot"><?= lang("green") ?></option>
						<option <?= ($_POST['color_marker'] == "blue-dot" ? "selected" : "") ?> value="blue-dot"><?= lang("blue") ?></option>
						<option <?= ($_POST['color_marker'] == "pink-dot" ? "selected" : "") ?> value="pink-dot"><?= lang("pink") ?></option>
						<option <?= ($_POST['color_marker'] == "yellow-dot" ? "selected" : "") ?> value="yellow-dot"><?= lang("yellow") ?></option>
						<option <?= ($_POST['color_marker'] == "purple-dot" ? "selected" : "") ?> value="purple-dot"><?= lang("purple") ?></option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<?php  
					$markers = array();
					if($addresses){
						foreach($addresses as $address){
							$image = "";
							if($address->image){
								$image = lang('image')." : <img src='".base_url('assets/uploads/'.$address->image.'')."' alt='' width='150px' height='90px'> </br>";
							}
							$markers[] = array(
								'name' => $image. lang('name').' : '.$address->name."<br/>".lang('phone').' : '.$address->phone."</br>".lang('address').' : '.$address->address."</br>".lang('kilometer').' : '.$address->kilometer,
								'longitude' => $address->longitude,
								'latitude' => $address->latitude,
								'color_marker' => 'http://maps.google.com/mapfiles/ms/icons/'.$address->color_marker.'.png',
								'location_name' => $address->name,
								'phone' => $address->phone,
								'image' => $address->image,
							);
						}
					}
					
				?>
				<div class="box">
					<div class="box-content">
						<div class="row">
							<div class="clearfix"></div>
							<div class="col-lg-12" style="margin-top:5px;">
								<div id="map" style="height: 900px;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo form_submit('search', lang("Search"), 'class="btn btn-primary"'); ?>
			</div>
        </div>
		
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBCcaduP4beYg3sMlRvDa4nLm1AY3hYrE0"></script>
<script type="text/javascript">
	var markers = <?=json_encode($markers)?>;
	var locations = [];
	$.each(markers,function(i,e){
		locations[i] = [e.name,e.latitude,e.longitude,e.color_marker,e.location_name,e.phone,e.image,i];
	});
		
	function InitMap() {
		var map = new google.maps.Map(document.getElementById('map'), {
			zoom: 13,
			center: new google.maps.LatLng(11.5673684, 104.9121794),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		var infowindow = new google.maps.InfoWindow();
		var marker, i;
		for (i = 0; i < locations.length; i++) {
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(locations[i][1], locations[i][2]),
				map: map,
				icon: {
				  url: (locations[i][3])
				},
				label: {
					text:locations[i][4],
					color: "green"
				}
				
				
			});
			google.maps.event.addListener(marker, 'click', (function (marker, i) {
				return function () {
					infowindow.setContent(locations[i][0]);
					infowindow.open(map, marker);
				}
			})(marker, i));
		}
	}
	google.maps.event.addDomListener(window, "load", InitMap);
	
</script>



