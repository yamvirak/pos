<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_area'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/add_area", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line("name"); ?></label>
                <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
				<?php echo lang('city_province', 'city_id'); ?>
				<div class="controls">
					<?php
					$ct[""] = lang("select")." ".lang("city_province");
					if($cities){
						foreach ($cities as $city) {
						   $ct[$city->id] = $city->name; 
						}
					}
					echo form_dropdown('city_id', $ct, 0, 'id="city_id" class="form-control"');
					?>
				</div>
			</div>
			<div class="form-group">
				<?php echo lang('district', 'district_id'); ?>
				<div class="district_box">
					<?php
						$ds[""] = lang("select")." ".lang("district");
						echo form_dropdown('district_id', $ds, 0, 'id="district_id" class="form-control"');
					?>
				</div>
			</div>
			<div class="form-group">
				<?php echo lang('commune', 'commune_id'); ?>
				<div class="commune_box">
					<?php
						$cm[""] = lang("select")." ".lang("commune");
						echo form_dropdown('commune_id', $cm, 0, 'id="commune_id" class="form-control"');
					?>
				</div>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_area', lang('add_area'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script type="text/javascript">
	$(document).ready(function () {
		$("#city_id").live("change",function(){
			var city_id = $(this).val();
			$.ajax({
				url : site.base_url + "system_settings/get_districts",
				dataType : "JSON",
				type : "GET",
				data : { city_id : city_id},
				success : function(data){
					var district_sel = "<select class='form-control' id='district_id' name='district_id'><option value=''><?= lang('select').' '.lang('district') ?></option>";
					if (data != false) {
						$.each(data, function () {
							district_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
					}
					district_sel += "</select>"
					$(".district_box").html(district_sel);
					$('select').select2();	
				}
			});
		});
		$("#district_id").live("change",function(){
			var district_id = $(this).val();
			$.ajax({
				url : site.base_url + "system_settings/get_commune",
				dataType : "JSON",
				type : "GET",
				data : { district_id : district_id},
				success : function(data){
					var commune_sel = "<select class='form-control' id='commune_id' name='commune_id'><option value=''><?= lang('select').' '.lang('commune') ?></option>";
					if (data != false) {
						$.each(data, function () {
							commune_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
					}
					commune_sel += "</select>"
					$(".commune_box").html(commune_sel);
					$('select').select2();	
				}
			});
		});
	});
</script>
