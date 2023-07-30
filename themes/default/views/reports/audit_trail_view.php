<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
			<table width="100%" style="white-space:wrap !important;">
				<tr>
					<td style="width:200px;"><?= lang("user") ?></td>
					<td>: <?= $user->first_name . ' '. $user->last_name ?></td>
				</tr>
				<tr>
					<td><?= lang("event") ?></td>
					<td>: <?= $row->event ?></td>
				</tr>
				<tr>
					<td><?= lang("table") ?></td>
					<td>: <?= $row->table_name ?></td>
				</tr>
				<tr >
					<td><?= lang("old_values") ?></td>
					<td class="well well-sm">: <small style="word-break: break-all;"><?= $row->old_values ?></small></td>
				</tr>
				<tr>
					<td><?= lang("new_values") ?></td>
					<td class="well well-sm">: <small style="word-break: break-all;"><?= $row->new_values ?></small></td>
				</tr>
				<tr>
					<td><?= lang("url") ?></td>
					<td>: <?= $row->url ?></td>
				</tr>
				<tr>
					<td><?= lang("date") ?></td>
					<td>: <?= $this->cus->hrsd($row->created_at) ?></td>
				</tr>
			</table>
		</div>
	</div>
</div>
