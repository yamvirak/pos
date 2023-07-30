<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_address') . " (" . $company->name . ")"; ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
				echo form_open_multipart("customers/edit_address/" . $address->id, $attrib); 
                ?>
                <div class="row">
                    <div class="col-lg-12">
						<div class="col-md-2">
							<div class="form-group">
								<?= lang('name', 'name'); ?>
								<input name="name" type="text" value="<?= $address->name ?>" class="form-control input-sm" id="name" required="required" />
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<?= lang('contact_person', 'contact_person'); ?>
								<?= form_input('contact_person', $address->contact_person, 'class="form-control" id="contact_person"'); ?>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<?= lang('phone', 'phone'); ?>
								<?= form_input('phone', $address->phone, 'class="form-control" id="phone"'); ?>
							</div>
						</div>
						
                        <div class="col-md-2">
							<div class="form-group">
								<?= lang('address', 'address'); ?>
								<input name="address" type="text" value="<?= $address->address ?>"  class="form-control input-sm" id="address" required="required" />
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<?= lang('kilometer', 'kilometer'); ?>
								<input name="kilometer" type="text" value="<?= $address->kilometer ?>" class="form-control input-sm" id="kilometer" />
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<?= lang('color', 'color_marker'); ?>
								<select class="form-control" name="color_marker">
									<option <?= ($address->color_marker == "red-dot" ? "selected" : "") ?> value="red-dot"><?= lang("red") ?></option>
									<option <?= ($address->color_marker == "green-dot" ? "selected" : "") ?> value="green-dot"><?= lang("green") ?></option>
									<option <?= ($address->color_marker == "blue-dot" ? "selected" : "") ?> value="blue-dot"><?= lang("blue") ?></option>
									<option <?= ($address->color_marker == "pink-dot" ? "selected" : "") ?> value="pink-dot"><?= lang("pink") ?></option>
									<option <?= ($address->color_marker == "yellow-dot" ? "selected" : "") ?> value="yellow-dot"><?= lang("yellow") ?></option>
									<option <?= ($address->color_marker == "purple-dot" ? "selected" : "") ?> value="purple-dot"><?= lang("purple") ?></option>
								</select>
							</div>
						</div>
						<div class="col-md-12 hidden">
							<div class="form-group">
								<?= lang('latitude', 'latitude'); ?>
								<input type="text" name="latitude" readonly="readonly" class="form-control input-sm" id="latitude" value="<?= $address->latitude ?>" />
							</div>
							<div class="form-group">
								<?= lang('longitude', 'longitude'); ?>
								<input type="text" name="longitude" readonly="readonly" class="form-control input-sm" id="longitude" value="<?= $address->longitude ?>" />
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<div id="map" style="height: 900px;"></div>
							</div>
						</div>	
						<div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('edit_address', $this->lang->line("submit"), 'id="edit_address" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
							</div>
                        </div>
					</div>
				</div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false&amp;key=AIzaSyBCcaduP4beYg3sMlRvDa4nLm1AY3hYrE0&amp;libraries=places"></script>
<script>
	var map;
	var marker;
	var latitude = "<?=(isset($address) && $address->latitude ? $address->latitude:'11.5563738')?>";
	var longitude = "<?=(isset($address) && $address->longitude ? $address->longitude:'104.9282099')?>";
	var myLatlng = new google.maps.LatLng(latitude, longitude);
	var geocoder = new google.maps.Geocoder();
	var infowindow = new google.maps.InfoWindow();
	function initialize() {
		var mapOptions = {
			zoom: 12,
			center: myLatlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map"), mapOptions);
		marker = new google.maps.Marker({
			map: map,
			position: myLatlng,
			draggable: true
		});
		geocoder.geocode({
			'latLng': myLatlng
		}, function (results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					$('#latitude,#longitude').show();
					//$('#address').val(results[0].formatted_address);
					$('#latitude').val(marker.getPosition().lat());
					$('#longitude').val(marker.getPosition().lng());
					var content = "<?= lang('name').' : '.$company->name.' <br/>'. lang('phone') . ' : ' . $address->phone.' <br/>'. lang('address') . ' : ' . $address->address ?>";
					
					infowindow.setContent(content);
					infowindow.open(map, marker);
				}
			}
		});
		var ac = new google.maps.places.Autocomplete((document.getElementById('address')), {
		  types: ['geocode']
		});
		ac.addListener('place_changed', function() {
			var place = ac.getPlace();
			if (place.geometry.viewport) {
				map.fitBounds(place.geometry.viewport);
			} else {
				map.setCenter(place.geometry.location);
				map.setZoom(15);
			}
			marker.setPosition(place.geometry.location);
			marker.setVisible(true);
			$('#latitude').val(marker.getPosition().lat());
			$('#longitude').val(marker.getPosition().lng());
		});
		google.maps.event.addListener(marker, 'dragend', function () {
			geocoder.geocode({
				'latLng': marker.getPosition()
			}, function (results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (results[0]) {
						$('#address').val(results[0].formatted_address);
						$('#latitude').val(marker.getPosition().lat());
						$('#longitude').val(marker.getPosition().lng());
						var content = "<?= lang('name').' : '.$company->name.' <br/>'. lang('phone') . ' : ' . $address->phone.' <br/>'. lang('address') . ' : ' . $address->address ?>";
						
					infowindow.setContent(content);
						infowindow.open(map, marker);
					}
				}
			});
		});
	}
	google.maps.event.addDomListener(window, 'load', initialize);
</script>

