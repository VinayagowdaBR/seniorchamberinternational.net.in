<?php
// Use payment details from controller
$details = $payment;

// Debug (optional, comment later)
// echo "<pre>"; print_r($details); echo "</pre>";
?>

<?php if (in_array($details->payment_type, [
        'custom_payment_method_1',
        'custom_payment_method_2',
        'custom_payment_method_3',
        'custom_payment_method_4'
    ])) : ?>

    <!-- Custom Payment Method View -->
    <div class="modal-body" style="word-wrap: break-word">
        <table class="table table-condensed table-bordered">
            <tbody>
                <tr>
                    <th><?= translate('payment_method') ?></th>
                    <td><?= $details->custom_payment_method_name ?></td>
                </tr>
                <tr>
                    <th><?= translate('transaction_id') ?></th>
                    <td><?= $details->custom_payment_method_transaction_id ?></td>
                </tr>
                <tr>
                    <th><?= translate('comment') ?></th>
                    <td><?= $details->custom_payment_method_comment ?></td>
                </tr>
                <tr>
                    <th><?= translate('bill_copy') ?></th>
                    <td>
                        <?php if ($details->custom_payment_method_bill_copy
                            && file_exists('uploads/custom_payment_method_bill_image/' . $details->custom_payment_method_bill_copy)) : ?>
                            <a href="<?= base_url() ?>admin/earnings/download_cpm_bill_copy/<?= $details->package_payment_id ?>"
                               class="btn btn-link"><?= translate('download') ?></a>
                        <?php else: ?>
                            <span class="text-muted"><?= translate('no_file_uploaded') ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="modal-footer">
        <?php if ($details->payment_status == 'due') : ?>
            <button data-target='#accept_payment_modal' data-toggle='modal'
                    onclick="accept_payment(<?= $details->package_payment_id ?>)"
                    class="btn btn-info"><?= translate('accept_payment') ?></button>
        <?php else : ?>
            <button class="btn btn-success"><?= translate('payment_already_accepted') ?></button>
        <?php endif; ?>
        <a href="<?= base_url() ?>admin/earnings/download_payment_pdf/<?= $details->package_payment_id ?>"
           class="btn btn-primary"><?= translate('download_pdf') ?></a>
        <button data-dismiss="modal" class="btn btn-danger" type="button">
            <?= translate('close') ?>
        </button>
    </div>

    <script>
        function accept_payment(payment_id) {
            $('#earnings_modal').modal('hide');
            $("#package_payment_id").val(payment_id);
        }
    </script>

<?php else : ?>

    <!-- Standard Payment Method View (Stripe/PayPal/Instamojo) -->
    <div class="modal-body" style="word-wrap: break-word">
        <?php
        $payment_data = json_decode($details->payment_details, true);
        $payment_info = null;

        if (isset($payment_data['payment'])) {
            $payment_info = $payment_data['payment'];
        } elseif (isset($payment_data['payments'][0])) {
            $payment_info = $payment_data['payments'][0];
        } elseif (is_array($payment_data)) {
            // Handle flat JSON (Stripe in your case)
            $payment_info = $payment_data;
        }
        ?>

        <?php if ($payment_info) : ?>
            <table class="table table-condensed table-bordered">
                <tbody>
                    <tr>
                        <th><?= translate('payment_id') ?></th>
                        <td><?= $payment_info['payment_id'] ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th><?= translate('buyer_name') ?></th>
                        <td><?= $payment_info['buyer_name'] ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th><?= translate('email') ?></th>
                        <td><?= $payment_info['buyer_email'] ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th><?= translate('phone') ?></th>
                        <td><?= $payment_info['buyer_phone'] ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th><?= translate('amount') ?></th>
                        <td><?= $payment_info['amount'] ?? 'N/A' ?> <?= $payment_info['currency'] ?? '' ?></td>
                    </tr>
                    <tr>
                        <th><?= translate('status') ?></th>
                        <td><?= $payment_info['status'] ?? 'N/A' ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else : ?>
            <p class="text-danger"><?= translate('no_payment_details_available') ?></p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="modal-footer">
        <a href="<?= base_url() ?>admin/earnings/download_payment_pdf/<?= $details->package_payment_id ?>"
           class="btn btn-primary"><?= translate('download_pdf') ?></a>
        <button data-dismiss="modal" class="btn btn-danger" type="button">
            <?= translate('close') ?>
        </button>
    </div>

<?php endif; ?>
