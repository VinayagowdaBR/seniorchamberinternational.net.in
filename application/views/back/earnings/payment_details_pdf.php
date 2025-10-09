<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; padding: 8px; }
        th { background: #f5f5f5; text-align: left; }
    </style>
</head>
<body>
    <h2>Payment Receipt</h2>

    <table>
        <tr>
            <th>Receipt No</th>
            <td><?= $payment->package_payment_id ?></td>
        </tr>
        <tr>
            <th>Member</th>
            <td><?= $member->first_name . ' ' . $member->last_name ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= $member->email ?></td>
        </tr>
        <tr>
            <th>Plan</th>
            <td><?= $plan->name ?></td>
        </tr>
        <tr>
            <th>Amount</th>
            <td><?= currency('', 'def') . $payment->amount ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= ucfirst($payment->payment_status) ?></td>
        </tr>
        <tr>
            <th>Payment Type</th>
            <td><?= $payment->payment_type ?></td>
        </tr>
        <tr>
            <th>Date</th>
            <td><?= date('d/m/Y h:i A', $payment->purchase_datetime) ?></td>
        </tr>
    </table>

    <div class="row">
    <!-- Member Information -->
    <div class="col-md-6">
        <h4 class="text-primary"><i class="fa fa-user"></i> Member Information</h4>
        <table class="table table-bordered">
            <tr>
                <th width="40%">Name:</th>
                <td><?php echo $member->first_name . ' ' . $member->last_name; ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo $member->email; ?></td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td><?php echo $member->phone; ?></td>
            </tr>
            <tr>
                <th>Member ID:</th>
                <td><?php echo $member->member_id; ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Payment Information -->
    <div class="col-md-6">
        <h4 class="text-success"><i class="fa fa-credit-card"></i> Current Payment Details</h4>
        <table class="table table-bordered">
            <tr>
                <th width="40%">Invoice Number:</th>
                <td><strong><?php echo $payment->invoice_number; ?></strong></td>
            </tr>
            <tr>
                <th>Package:</th>
                <td><?php echo $plan->name; ?></td>
            </tr>
            <tr>
                <th>Amount:</th>
                <td class="text-success"><strong><?php echo currency('', 'def') . number_format($payment->amount, 2); ?></strong></td>
            </tr>
            <tr>
                <th>Payment Type:</th>
                <td><?php echo $payment->payment_type; ?></td>
            </tr>
            <tr>
                <th>Transaction ID:</th>
                <td><small><?php echo $payment->payment_code; ?></small></td>
            </tr>
            <tr>
                <th>Payment Date:</th>
                <td><?php echo date('d M Y, h:i A', $payment->purchase_datetime); ?></td>
            </tr>
        </table>
    </div>
</div>

<!-- Validity Period -->
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h4><i class="fa fa-calendar"></i> Validity Period</h4>
            <p style="font-size: 16px; margin: 0;">
                <strong>From:</strong> <?php echo date('d M Y', strtotime($payment->validity_start_date)); ?>
                &nbsp;&nbsp;<i class="fa fa-arrow-right"></i>&nbsp;&nbsp;
                <strong>To:</strong> <?php echo date('d M Y', strtotime($payment->validity_end_date)); ?>
                &nbsp;&nbsp;
                <span class="badge badge-success"><?php echo $payment->validity_period; ?></span>
            </p>
        </div>
    </div>
</div>

<!-- Payment History -->
<div class="row">
    <div class="col-md-12">
        <h4 class="text-warning"><i class="fa fa-history"></i> Payment History (All Years)</h4>
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">Invoice No</th>
                        <th width="15%">Date</th>
                        <th width="15%">Amount</th>
                        <th width="20%">Validity</th>
                        <th width="10%">Year</th>
                        <th width="10%">Status</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($payment_history)) {
                        $sl = 1;
                        foreach ($payment_history as $history) {
                    ?>
                    <tr>
                        <td><?php echo $sl++; ?></td>
                        <td><strong><?php echo $history->invoice_number; ?></strong></td>
                        <td><?php echo date('d M Y', $history->purchase_datetime); ?></td>
                        <td><?php echo currency('', 'def') . number_format($history->amount, 2); ?></td>
                        <td>
                            <?php 
                            if ($history->validity_start_date) {
                                echo date('d M Y', strtotime($history->validity_start_date)) . ' to ' . 
                                     date('d M Y', strtotime($history->validity_end_date));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td><?php echo $history->invoice_year ?: '-'; ?></td>
                        <td>
                            <?php if ($history->payment_status == 'paid'): ?>
                                <span class="badge badge-success">Paid</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Due</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($history->invoice_generated == 1): ?>
                                <a href="<?php echo base_url('admin/earnings/download_invoice/' . $history->package_payment_id); ?>" 
                                   class="btn btn-primary btn-xs" target="_blank">
                                    <i class="fa fa-download"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="8" class="text-center">No payment history found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <a href="<?php echo base_url('admin/earnings/download_invoice/' . $payment_id); ?>" 
       class="btn btn-success" target="_blank">
        <i class="fa fa-download"></i> Download Current Invoice
    </a>
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>


    <p style="margin-top: 40px; text-align: center;">
        Thank you for your payment!
    </p>
</body>
</html>
