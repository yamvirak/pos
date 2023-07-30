<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_deposits').' ( '.lang('reference').': '.$rental->reference_no.')'; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th><?= $this->lang->line("date"); ?></th>
                        <th><?= $this->lang->line("reference_no"); ?></th>
                        <th><?= $this->lang->line("amount"); ?></th>
						<th><?= $this->lang->line("paid_by"); ?></th>
						<th><?= $this->lang->line("status"); ?></th>
                        <th><?= $this->lang->line("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($deposits)) {
                        foreach ($deposits as $deposit) { ?>
                            <tr class="row<?= $deposit->id ?>">
                                <td><?= $this->cus->hrld($deposit->date); ?></td>
                                <td><?= $deposit->reference_no; ?></td>
                                <td style="text-align:right !important"><?= $this->cus->formatMoney($deposit->amount) . ' ' . (($deposit->attachment) ? '<a href="' . site_url('welcome/download/' . $deposit->attachment) . '"><i class="fonts fa fa-chain"></i></a>' : ''); ?></td>
								<td><?= lang($deposit->paid_by); ?></td>
								<td><?=$this->cus->row_status($deposit->type)?></td>
                                <td>
                                    <div class="text-center">
                                        <a href="<?= site_url('rentals/deposit_note/' . $deposit->id) ?>"
                                           data-toggle="modal" data-target="#myModal2"><i class="fonts fa fa-print"></i></a>
                                        <?php if ($deposit->paid_by != 'gift_card') { ?>
											<a href="<?= site_url('rentals/edit_deposit/' . $deposit->id) ?>" data-toggle="modal" data-target="#myModal2"><i class="fonts fa fa-edit"></i></a>
                                            <a href="#" class="po"
                                               title="<b><?= $this->lang->line("delete_deposit") ?></b>"
                                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $deposit->id ?>' href='<?= site_url('rentals/delete_deposit/' . $deposit->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                               rel="popover"><i class="fonts fa fa-trash-o"></i></a>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='6'>" . lang('no_data_available') . "</td></tr>";
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $(document).on('click', '.po-delete', function () {
            var id = $(this).attr('id');
            $(this).closest('tr').remove();
        });
    });
</script>
