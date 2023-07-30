<?php

if ($pos_settings->remote_printing != 1) {
    if ($Settings->invoice_view == 1) {
        $tax_summary = array();
        foreach ($rows as $row) {
            if (isset($tax_summary[$row->tax_code])) {
                $tax_summary[$row->tax_code]['items'] += $row->quantity;
                $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
            } else {
                $tax_summary[$row->tax_code]['items'] = $row->quantity;
                $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
            }
        }
        if ($return_rows) {
            foreach ($return_rows as $row) {
                if (isset($tax_summary[$row->tax_code])) {
                    $tax_summary[$row->tax_code]['items'] += $row->quantity;
                    $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                    $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                } else {
                    $tax_summary[$row->tax_code]['items'] = $row->quantity;
                    $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                    $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                    $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                    $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                    $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                }
            }
        }
    }
    ?>
    <script type="text/javascript">

        function receiptData() {

            receipt = {};
            receipt.store_name = "<?= printText(($biller->company && $biller->name != '-' ? $biller->name : $biller->company), $printer->char_per_line);?>\n";

            receipt.header = "";
            receipt.header += "<?= printText(($biller->company && $biller->name != '-' ? $biller->name : $biller->company ), $printer->char_per_line);?>\n";
            <?php
            if ($biller->address) { ?>
                receipt.header += "<?= printText($biller->address, $printer->char_per_line);?>\n";
                <?php
            }
            if ($biller->city) { ?>
                receipt.header += "<?= printText($biller->city . " " . ($biller->country ? $biller->country : ''), $printer->char_per_line);?>\n";
                <?php
            } ?>
            receipt.header += "<?= printText(lang('tel').': '.$biller->phone, $printer->char_per_line);?>";
            <?php
            // comment or remove these extra info if you don't need
            if (!empty($biller->cf1) && $biller->cf1 != "-") {
                echo 'receipt.header += "\n" + "' . lang("bcf1") . ': ' . $biller->cf1 .'";';
            }
            if (!empty($biller->cf2) && $biller->cf2 != "-") {
                echo 'receipt.header += "\n" + "' . lang("bcf2") . ': ' . $biller->cf2 .'";';
            }
            if (!empty($biller->cf3) && $biller->cf3 != "-") {
                echo 'receipt.header += "\n" + "' . lang("bcf3") . ': ' . $biller->cf3 .'";';
            }
            if (!empty($biller->cf4) && $biller->cf4 != "-") {
                echo 'receipt.header += "\n" + "' . lang("bcf4") . ': ' . $biller->cf4 .'";';
            }
            if (!empty($biller->cf5) && $biller->cf5 != "-") {
                echo 'receipt.header += "\n" + "' . lang("bcf5") . ': ' . $biller->cf5 .'";';
            }
            if (!empty($biller->cf6) && $biller->cf6 != "-") {
                echo 'receipt.header += "\n" + "' . lang("bcf6") . ': ' . $biller->cf6 .'";';
            }
            // end of the customer fields

            echo 'receipt.header += "\n\n";';
            if ($pos_settings->cf_title1 && $pos_settings->cf_value1) { ?>
                receipt.header += "<?= printText(($pos_settings->cf_title1 . ": " . $pos_settings->cf_value1), $printer->char_per_line);?>\n";
                <?php
            }
            if ($pos_settings->cf_title2 && $pos_settings->cf_value2) { ?>
                receipt.header += "<?= printText(($pos_settings->cf_title2 . ": " . $pos_settings->cf_value2), $printer->char_per_line);?>\n";
                <?php
            } ?>
            receipt.header += "\n";

            receipt.info = "";
            receipt.info += "<?= lang("date") . ": " . $this->cus->hrld($inv->date); ?>" + "\n";
            receipt.info += "<?= lang("sale_no_ref") . ": " . $inv->id; ?>" + "\n";
            receipt.info += "<?= lang("sales_person") . ": " . $created_by->first_name." ".$created_by->last_name; ?>" + "\n\n";
            receipt.info += "<?= lang("customer") . ": " . ($customer->company && $customer->company != '-' ? $customer->company : $customer->name); ?>" + "\n";
            <?php 
            if ($pos_settings->customer_details) {
                if ($customer->vat_no != "-" && $customer->vat_no != "") {
                    echo 'receipt.info += "' . lang("vat_no") . ': ' . $customer->vat_no .'" + "\n";';
                }
                echo 'receipt.info += "' . lang("tel") . ': ' . $customer->phone .'" + "\n";';
                echo 'receipt.info += "' . lang("address") . ': ' . $customer->address .'" + "\n";';
                echo 'receipt.info += "' . $customer->city ." ".$customer->state." ".$customer->country .'" + "\n";';

                if (!empty($customer->cf1) && $customer->cf1 != "-") {
                    echo 'receipt.info += "\n" + "' . lang("ccf1") . ': ' . $customer->cf1 .'";';
                }
                if (!empty($customer->cf2) && $customer->cf2 != "-") {
                    echo 'receipt.info += "\n" + "' . lang("ccf2") . ': ' . $customer->cf2 .'";';
                }
                if (!empty($customer->cf3) && $customer->cf3 != "-") {
                    echo 'receipt.info += "\n" + "' . lang("ccf3") . ': ' . $customer->cf3 .'";';
                }
                if (!empty($customer->cf4) && $customer->cf4 != "-") {
                    echo 'receipt.info += "\n" + "' . lang("ccf4") . ': ' . $customer->cf4 .'";';
                }
                if (!empty($customer->cf5) && $customer->cf5 != "-") {
                    echo 'receipt.info += "\n" + "' . lang("ccf5") . ': ' . $customer->cf5 .'";';
                }
                if (!empty($customer->cf6) && $customer->cf6 != "-") {
                    echo 'receipt.info += "\n" + "' . lang("ccf6") . ': ' . $customer->cf6 .'";';
                }
                echo 'receipt.info += "\n";';
            }
            ?>

            receipt.items = "";
            <?php $r = 1; foreach ($rows as $row): ?>
            receipt.items += "<?= printLine(product_name(addslashes("#".$r." ".$row->product_code." - ".$row->product_name).' '.($row->variant ? ' (' . $row->variant . ')' : ''), $printer->char_per_line).": ".($row->tax_code ? '*'.$row->tax_code : ''), $printer->char_per_line, ' '); ?>" + "\n";
            receipt.items += "<?= printLine("   ".($this->cus->formatQuantity($row->unit_quantity).' '.$row->product_unit_code)." x ".$this->cus->formatMoney($row->unit_price) . ":  ". $this->cus->formatMoney($row->subtotal), $printer->char_per_line, ' '); ?>" + "\n";
            <?php $r++; endforeach; ?>
            <?php
            if ($return_rows) { ?>
                receipt.items += "\n" + "<?=lang('returned_items');?>" + "\n";
                <?php $r = 1; foreach ($return_rows as $row): ?>
                receipt.items += "<?= printLine(product_name(addslashes("#".$r." ".$row->product_code." - ".$row->product_name).' '.($row->variant ? ' (' . $row->variant . ')' : ''), $printer->char_per_line).": ".($row->tax_code ? '*'.$row->tax_code : ''), $printer->char_per_line, ' '); ?>" + "\n";
                receipt.items += "<?= printLine("   ".($this->cus->formatQuantity($row->unit_quantity).' '.$row->product_unit_code)." x ".$this->cus->formatMoney($row->unit_price) . ":  ". $this->cus->formatMoney($row->subtotal), $printer->char_per_line, ' ') . ""; ?>" + "\n";
                <?php $r++; endforeach; ?>
                <?php
            } ?>
            receipt.totals = "";
            receipt.totals += "<?= printLine(lang("total") . ": " . $this->cus->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)), $printer->char_per_line); ?>" + "\n";
            <?php 
            if ($inv->order_tax != 0) { ?>
                receipt.totals += "<?= printLine(lang("tax") . ": " . $this->cus->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax), $printer->char_per_line); ?>" + "\n";
                <?php 
            }
            if ($inv->total_discount != 0) { ?>
                receipt.totals += "<?= printLine(lang("discount") . ": (" . $this->cus->formatMoney($return_sale ? ($inv->product_discount+$return_sale->product_discount) : $inv->product_discount) . ") " . $this->cus->formatMoney($return_sale ? ($inv->order_discount+$return_sale->order_discount) : $inv->order_discount), $printer->char_per_line); ?>" + "\n";
                <?php 
            }
            if ($inv->shipping != 0) { ?>
                receipt.totals += "<?= printLine(lang("shipping") . ": ". $this->cus->formatMoney($inv->shipping), $printer->char_per_line); ?>" + "\n";
                <?php 
            }
            if ($pos_settings->rounding || $inv->rounding > 0) { ?>
                receipt.totals += "<?= printLine(lang("rounding") . ": " . $this->cus->formatMoney($inv->rounding), $printer->char_per_line); ?>" + "\n";
                receipt.totals += "<?= printLine(lang("grand_total") . ": " . $this->cus->formatMoney($return_sale ? ($this->cus->roundMoney($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : $this->cus->roundMoney($inv->grand_total + $inv->rounding)), $printer->char_per_line); ?>" + "\n";
                <?php 
            } else { ?>
                receipt.totals += "<?= printLine(lang("grand_total") . ": " . $this->cus->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total), $printer->char_per_line); ?>" + "\n";
                <?php 
            } ?>
            receipt.totals += "<?= printLine(lang("paid_amount") . ": " . $this->cus->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid), $printer->char_per_line); ?>" + "\n";
            receipt.totals += "<?= printLine(lang("due_amount") . ": " . $this->cus->formatMoney(($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid)), $printer->char_per_line); ?>" + "\n";

            receipt.payments = '';
            <?php
            if($payments) {

                foreach($payments as $payment) {
                    if ($payment->paid_by == 'cash'  || $payment->paid_by == 'deposit' && $payment->pos_paid) { ?>
                        receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->pos_paid), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("change") . ": " . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance) : '0.00'), $printer->char_per_line); ?>" + "\n";
                        <?php 
                    } if (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) { ?>
                        receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->pos_paid), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("card_no") . ": xxxx xxxx xxxx " . substr($payment->cc_no, -4), $printer->char_per_line); ?>" + "\n";
                        <?php  
                    } if ($payment->paid_by == 'gift_card') { ?>
                        receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->pos_paid), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("card_no") . ": " . $payment->gc_no, $printer->char_per_line); ?>" + "\n";
                        <?php 
                    } if ($payment->paid_by == 'Cheque' && $payment->cheque_no) { ?>
                        receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->pos_paid), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("cheque_no") . ": " . $payment->cheque_no, $printer->char_per_line); ?>" + "\n";
                        <?php if ($payment->paid_by == 'other' && $payment->amount) { ?>
                            receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                            receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->amount), $printer->char_per_line); ?>" + "\n";
                            receipt.payments += "<?= printText(lang("payment_note") . ": " . $payment->note, $printer->char_per_line); ?>" + "\n";
                            <?php 
                        }
                    }

                }
            }
            if($return_payments) {
                ?>
                receipt.payments += "\n" + "<?=printText(lang("return_payments"), $printer->char_per_line);?>" + "\n";
                <?php
                foreach($return_payments as $payment) {
                    if ($payment->paid_by == 'cash'  || $payment->paid_by == 'deposit' && $payment->pos_paid) { ?>
                        receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->pos_paid), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("change") . ": " . ($payment->pos_balance > 0 ? $this->cus->formatMoney($payment->pos_balance) : '0.00'), $printer->char_per_line); ?>" + "\n";
                        <?php 
                    } if (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) { ?>
                        receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->pos_paid), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("card_no") . ": xxxx xxxx xxxx " . substr($payment->cc_no, -4), $printer->char_per_line); ?>" + "\n";
                        <?php  
                    } if ($payment->paid_by == 'gift_card') { ?>
                        receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->pos_paid), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("card_no") . ": " . $payment->gc_no, $printer->char_per_line); ?>" + "\n";
                        <?php 
                    } if ($payment->paid_by == 'Cheque' && $payment->cheque_no) { ?>
                        receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->pos_paid), $printer->char_per_line); ?>" + "\n";
                        receipt.payments += "<?= printLine(lang("cheque_no") . ": " . $payment->cheque_no, $printer->char_per_line); ?>" + "\n";
                        <?php if ($payment->paid_by == 'other' && $payment->amount) { ?>
                            receipt.payments += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), $printer->char_per_line); ?>" + "\n";
                            receipt.payments += "<?= printLine(lang("amount") . ": " . $this->cus->formatMoney($payment->amount), $printer->char_per_line); ?>" + "\n";
                            receipt.payments += "<?= printText(lang("payment_note") . ": " . $payment->note, $printer->char_per_line); ?>" + "\n";
                            <?php 
                        }
                    }

                }
            }
            ?>
            receipt.footer = "";
            <?php 
            if ($Settings->invoice_view == 1) {
                if (!empty($tax_summary)) {
                    ?>
                    receipt.footer += "<?=lang('tax_summary');?>" + "\n\n";
                    receipt.footer += "<?=taxLine(lang('name'), lang('code'), lang('qty'), lang('tax_excl'), lang('tax_amt'), $printer->char_per_line);?>" + "\n";
                    receipt.footer += "<?=str_replace("\n", "", drawLine($printer->char_per_line));?>"; + "\n";
                    <?php foreach ($tax_summary as $summary): ?>
                    receipt.footer += "<?=taxLine($summary['name'], $summary['code'], $this->cus->formatQuantity($summary['items']), $this->cus->formatMoney($summary['amt']), $this->cus->formatMoney($summary['tax']), $printer->char_per_line);?>" + "\n";
                    <?php endforeach;?>
                    receipt.footer += "<?=str_replace("\n", "", drawLine($printer->char_per_line));?>"; + "\n";
                    receipt.footer += "\n<?=printLine(lang("total_tax_amount") . ":" . $this->cus->formatMoney($inv->product_tax), $printer->char_per_line);?>" + "\n";
                    receipt.footer += "<?=str_replace("\n", "", drawLine($printer->char_per_line));?>" + "\n\n";
                    <?php
                }
            }
            if ($inv->note) { ?>
                receipt.footer += "<?= printText(strip_tags(preg_replace('/\s+/',' ', $this->cus->decode_html($inv->note))), $printer->char_per_line); ?>" + "\n\n";
                <?php 
            } 
            if ($biller->invoice_footer) { ?>
                receipt.footer += "<?= printText(str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), $biller->invoice_footer), $printer->char_per_line);?>\n\n";
                <?php 
            } ?>
            return receipt;
        }

        var socket = null;

    </script>

    <?php
    if ( ! $pos_settings->remote_printing) {
        ?>
        <script type="text/javascript">
            function openCashDrawer() {
                var ocddata = {
                    'printer': <?= json_encode($printer); ?>
                };
                $.get('<?= site_url('pos/open_drawer'); ?>', {data: JSON.stringify(ocddata)});
                return false;
            }

            function printReceipt() {
                var receipt_data = receiptData();
                var socket_data = {
                    'printer': <?= json_encode($printer); ?>,
                    'logo': '<?= !empty($biller->logo) ? $biller->logo : ''; ?>',
                    'text': receipt_data,
                    'cash_drawer': <?= isset($modal) ? 0 : 1; ?>, 'drawer_code': '<?= $pos_settings->cash_drawer_codes; ?>'
                };
                $.get('<?= site_url('pos/p'); ?>', {data: JSON.stringify(socket_data)});
                return false;
            }
        </script>
        <?php
    } elseif ($pos_settings->remote_printing == 2) {
        ?>
        <script src="<?= $assets ?>plugins/socket.io.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            socket = io.connect('http://localhost:6440', {'reconnection': false});

            function printReceipt() {
                if (socket.connected) {
                    var receipt_data = receiptData();
                    var socket_data = {
                        'printer': <?= json_encode($printer); ?>,
                        'logo': '<?= !empty($biller->logo) ? base_url('assets/uploads/logos/'.$biller->logo) : ''; ?>',
                        'text': receipt_data,
                        'cash_drawer': <?= isset($modal) ? 0 : 1; ?>, 'drawer_code': '<?= $pos_settings->cash_drawer_codes; ?>'
                    };
                    socket.emit('print-now', socket_data);
                    return false;
                } else {
                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                    return false;
                }
            }

            function openCashDrawer() {
                if (socket.connected) {
                    var ocddata = {
                        'printer': <?= json_encode($printer); ?>,
                        'cash_drawer': 1, 'drawer_code': '<?= $pos_settings->cash_drawer_codes; ?>'
                    };
                    socket.emit('open-cashdrawer', ocddata);
                    return false;
                } else {
                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                    return false;
                }
            }
        </script>
        <?php

    } elseif ($pos_settings->remote_printing == 3) {

        ?>
        <script type="text/javascript">
            try {
                socket = new WebSocket('ws://localhost:9000/test/server.php');
                socket.onopen = function () {
                    console.log('Connected');
                    return;
                };
                socket.onclose = function () {
                    console.log('Not Connected');
                    return;
                };
            } catch (e) {
                console.log(e);
            }

            function openCashDrawer() {
                if (socket.readyState == 1) {
                    var ocddata = {
                        'printer': <?= json_encode($printer); ?>
                    };
                    socket.send(JSON.stringify({
                        type: 'open-cashdrawer',
                        data: ocddata
                    }));
                    return false;
                } else {
                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                    return false;
                }
            }

            function printReceipt() {
                if (socket.readyState == 1) {
					
                    var receipt_data = receiptData();
                    var socket_data = {
                        'printer': <?= json_encode($printer); ?>,
                        'logo': '<?= !empty($biller->logo) ? base_url('assets/uploads/logos/'.$biller->logo) : ''; ?>',
                        'text': receipt_data,
                        'cash_drawer': <?= isset($modal) ? 0 : 1; ?>, 'drawer_code': '<?= $pos_settings->cash_drawer_codes; ?>'
                    };
					
					socket.send(JSON.stringify({
                        type: 'print-receipt',
                        data: socket_data
                    }));
                    return false;
                } else {
                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                    return false;
                }
            }
            </script>
            <?php
        }
        ?>
        <script type="text/javascript">
            <?php 
            if ($pos_settings->auto_print && (!isset($modal) || empty($modal))) {
                ?>
                $(document).ready(function() {
                    setTimeout(printReceipt, 1000);
                });
                <?php 
            }
            ?>
        </script>
        <?php
    }
    ?>