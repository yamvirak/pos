<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_locations'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_locations/".$id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>
					             <div class="form-group">
									<?= lang("country", "brcountry"); ?>
										<div class="no-country">
											<?php
												$opt_countries[''] = lang("select") . ' ' . lang("country");
													if(isset($countries) && $countries){
														foreach ($countries as $country) {
														$opt_countries[$country->id] = $country->name;
													}
												}
												echo form_dropdown('country', $opt_countries, (isset($_POST['country']) ? $_POST['country'] : ''), 'id="brcountry" class="form-control input-tip select" style="width:100%;"');
															?>
										</div>
								</div>
					            <div class="form-group">
					                <?= lang('locations_type', 'locations_type'); ?>
					                <?php 
					                    $location_type = array(lang("select")." ".lang("locations_type"));
					                    foreach($locations_type as $type){
					                        $location_type[$type->id] = $type->description;
					                    } 
					                ?>
					                
					                <?= form_dropdown('type', $location_type , $locations->type_id, 'class="form-control" id="type" required="required"'); ?>
					            </div>

					            <div class="form-group code">
			                        <?= lang("code", "code"); ?>
			                        <?php echo form_input('code', $locations->code, 'class="form-control tip" id="code" data-bv-notempty="false"'); ?>
			                    </div>

					            <div class="form-group">
					                <?= lang('name', 'name'); ?>
					                <?= form_input('name', $locations->name, 'class="form-control" id="name" required="required"'); ?>
					            </div>
         
								<div class="form-group">
									<?= lang("province", "brprovince"); ?>
										<div class="no-province">
											<?php
												$opt_provinces[''] = lang("select") . ' ' . lang("province");
													if(isset($provinces) && $provinces){
														foreach ($provinces as $province) {
															$opt_provinces[$province->id] = $province->name;
														}
													}
												echo form_dropdown('province', $opt_provinces, (isset($_POST['province']) ? $_POST['province'] : $locations->province_id), 'id="brprovince" class="form-control input-tip select" style="width:100%;"');
														?>
										</div>
								</div>								<div class="form-group">
									<?= lang("district", "brdistrict"); ?>
									<div class="no-district">
										<?php
											$opt_districts[''] = lang("select") . ' ' . lang("district");
											if(isset($districts) && $districts){
												foreach ($districts as $district) {
													$opt_districts[$district->id] = $district->name;
												}
											}
											echo form_dropdown('district', $opt_districts, (isset($_POST['district']) ? $_POST['district'] : ''), 'id="brdistrict" class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
						
							
								<div class="form-group">
									<?= lang("commune", "brcommune"); ?>
									<div class="no-commune">
									<?php
										$opt_communes[''] = lang("select") . ' ' . lang("commune");
										if(isset($communes) &&$communes){
											foreach ($communes as $commune) {
												$opt_communes[$commune->id] = $commune->name;
											}
										}
										echo form_dropdown('commune', $opt_communes, (isset($_POST['commune']) ? $_POST['commune'] : ''), 'id="brcommune" class="form-control input-tip select" style="width:100%;"');
									?>
									</div>
								</div>
			
			
            
            
            
        </div>
        <div class="modal-footer">
            <?= form_submit('edit_locations', lang('edit_locations'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>

<script type="text/javascript">
    $(document).ready(function () {
		$('#project').live('change', function() {
			var project_id = $(this).val();
			if(project_id != '0'){
				$(".seperate_project").slideUp();
			}else{
				$(".seperate_project").slideDown();
				
			}
		});
		 
		biller();
		$("#biller").change(biller);
		function biller(){
			var biller = $("#biller").val();
			<?php
				$multi_project = '';
				if($category && $category->project && $category->project != "null"){
					$projects = json_decode($category->project);
					foreach($projects as $project){
						$multi_project .=$project.'#';;
					}
				}
			?>
			var project_multi = '<?= $multi_project ?>';
			$.ajax({
				url : "<?= site_url("system_settings/get_multi_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project_multi : project_multi },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$(".no-project-multi").html(data.multi_resultl);
						$("#project_multi").select2();
					}
				}
			})
		}
    });
</script>

<script type="text/javascript">
	$(function(){
		$(document).on('click', '#profile-upload', function() {
			$('#profile-image').click();
		});
		$('#profile-image').change(function() {
			var imgPath = this.value;
			var ext = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
			if(ext == "gif" || ext == "png" || ext == "jpg" || ext == "jpeg")
				readURLProfile(this);
			else
				alert("Please select image file (jpg, jpeg, png).")
		});
		function readURLProfile(input) {
			if(input.files && input.files[0]) {
				var reader = new FileReader();
				reader.readAsDataURL(input.files[0]);
				reader.onload = function(e) {
					$('#profile-image-preview').attr('src', e.target.result);
				};
			}
		}
		$(document).on('click', '#profile-remove', function() {
			$('#profile-image-preview').attr('src', "<?= site_url('/assets/uploads/avatars/blank.png') ?>");
			$("#profile-image").val("");
		});
		
		
		country();
		$(document).on('change', '#brprovince', district);
		$(document).on('change', '#brdistrict', commune);
		$(document).on('change', '#brcommune', village);
		
		function country() {
			$.ajax({
				url : "<?= site_url('loans/get_countries') ?>",
				type : "GET",
				dataType : "JSON",
				data : { id : "<?= ($borrower?$locations->country_id:0) ?>"},
				success:function(data){
					var id = $("#brcountry option:selected").val();
					if(data){
						$(".no-country").html(data.result);
						$("#brcountry").select2();
						province(id);
					}
				}
			});
		}
		
		function province(country_id) {
			var country_id = $("#brcountry option:selected").val()?$("#brcountry option:selected").val():country_id;
			$.ajax({
				url : "<?= site_url('loans/get_provinces') ?>",
				type : "GET",
				data : { country_id : country_id, id : "<?= ($locations?$locations->province_id:0) ?>"},
				dataType : "JSON",
				success:function(data){
					var province_id = $("#brprovince option:selected").val();
					if(data){
						$(".no-province").html(data.result);
						$("#brprovince").select2();
						district(province_id);
					}
				}
			});
		}
		
		function district(province_id) {
			var province_id = $("#brprovince option:selected").val()?$("#brprovince option:selected").val():province_id;
			$.ajax({
				url : "<?= site_url('loans/get_districts') ?>",
				type : "GET",
				data : { province_id : province_id, id : "<?= ($locations?$locations->district_id:0) ?>"},
				dataType : "JSON",
				success:function(data){
					var district_id = $("#brdistrict option:selected").val();
					if(data){
						$(".no-district").html(data.result);
						$("#brdistrict").select2();
						commune(district_id);
					}
				}
			});
		}
		
		function commune(district_id) {
			var district_id = $("#brdistrict option:selected").val()?$("#brdistrict option:selected").val():district_id;
			$.ajax({
				url : "<?= site_url('loans/get_communces') ?>",
				type : "GET",
				data : { district_id : district_id, id : "<?= ($locations?$locations->commune_id:0) ?>"},
				dataType : "JSON",
				success:function(data){
					var commune_id = $("#brcommune option:selected").val();
					if(data){
						$(".no-commune").html(data.result);
						$("#brcommune").select2();
						village(commune_id);
					}
				}
			});
		}
		
		function village(commune_id) {
			var commune_id = $("#brcommune option:selected").val()?$("#brcommune option:selected").val():commune_id;
			$.ajax({
				url : "<?= site_url('loans/get_villages') ?>",
				type : "GET",
				data : { commune_id : commune_id, id : "<?= ($locations?$locations->village_id:0) ?>"},
				dataType : "JSON",
				success:function(data){
					if(data){
						$(".no-village").html(data.result);
						$("#brvillage").select2();
					}
				}
			});
		}
		
	});
</script>
