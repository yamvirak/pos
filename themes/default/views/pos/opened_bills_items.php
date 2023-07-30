<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header modal-primary">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="ajaxModalLabel">
                <?= lang('suspended_sales') ?> (<?= lang("print") ?>)
            </h4>
        </div>
        <div class="modal-body" style="padding-bottom:0;">        
			<table class="table items table-striped table-bordered table-condensed table-hover sortable_table">
				<thead>
					<tr>
						<th><?= lang("&nbsp;") ?></th>
						<th><?= lang("product_name") ?></th>
						<th><?= lang("quantity") ?></th>
						<th><?= lang("unit_price") ?></th>
						<th><?= lang("status") ?></th>
					</tr>
				</thead>
				<tbody>
					<?= isset($result)?$result:''; ?>
				</tbody>
			</table>
            <div class="clearfix"></div>
        </div>
        
    </div>
</div>

<script type="text/javascript">
	$(function(){
		$(".cancel_print").live("click",function(){
			var parent = $(this).parent().parent();
			var sid = parent.find(".sid").val();
			$.ajax({
				type : "GET",
				url : "<?= site_url("pos/cancel_print") ?>",
				dataType : "JSON",				
				data : { sid : sid},
				success : function(){					
					location.href = "<?= $_SERVER['HTTP_REFERER'] ?>";
				}
			});
			
			return false;
		})
	});
</script>

