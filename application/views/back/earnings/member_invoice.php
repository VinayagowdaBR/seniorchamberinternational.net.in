<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice <?php echo $invoice_number; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            margin: 20px 0;
        }
        .invoice-details {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-details-left, .invoice-details-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-details-right {
            text-align: right;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            margin-bottom: 8px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table th {
            background-color: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #007bff;
        }
        .items-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #007bff;
        }
        .total-label {
            font-size: 18px;
            font-weight: bold;
        }
        .total-amount {
            font-size: 24px;
            color: #28a745;
            font-weight: bold;
        }
        .payment-info {
            background-color: #f0f8ff;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            border-radius: 4px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <?php if($logoSrc): ?>
                <img src="<?php echo $logoSrc; ?>" class="logo" alt="Logo">
            <?php endif; ?>
            <div class="company-name"><?php echo $system_title; ?></div>
            <div class="invoice-title">INVOICE</div>
        </div>

        <!-- Validity Period Banner -->
<div style="background-color: #28a745; color: white; padding: 15px; margin: 20px 0; text-align: center; border-radius: 5px;">
    <h3 style="margin: 0; font-size: 18px;">VALIDITY PERIOD</h3>
    <p style="margin: 10px 0 0 0; font-size: 16px;">
        <strong><?php echo date('d M Y', strtotime($payment->validity_start_date)); ?></strong>
        &nbsp;to&nbsp;
        <strong><?php echo date('d M Y', strtotime($payment->validity_end_date)); ?></strong>
    </p>
    <p style="margin: 5px 0 0 0; font-size: 14px;">(<?php echo $payment->validity_period; ?>)</p>
</div>

        
        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-details-left">
                <div class="detail-label">Bill To:</div>
                <div class="detail-value">
                    <strong><?php echo $member->first_name . ' ' . $member->last_name; ?></strong><br>
                    Email: <?php echo $member->email; ?><br>
                    Phone: <?php echo $member->phone; ?><br>
                    Member ID: <?php echo $member->member_id; ?>
                </div>
            </div>
            <div class="invoice-details-right">
                <div class="detail-value">
                    <span class="detail-label">Invoice Number:</span><br>
                    <strong><?php echo $invoice_number; ?></strong>
                </div>
                <div class="detail-value">
                    <span class="detail-label">Invoice Date:</span><br>
                    <?php echo $invoice_date; ?>
                </div>
                <div class="detail-value">
                    <span class="status-badge">PAID</span>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="10%">#</th>
                    <th width="50%">Description</th>
                    <th width="20%">Package</th>
                    <th width="20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <strong><?php echo $plan->name; ?></strong><br>
                        <small>
                            • Express Interests: <?php echo $plan->express_interest; ?><br>
                            • Direct Messages: <?php echo $plan->direct_messages; ?><br>
                            • Photo Gallery: <?php echo $plan->photo_gallery; ?> photos
                        </small>
                    </td>
                    <td><?php echo $plan->name; ?></td>
                    <td><?php echo currency('', 'def') . number_format($payment->amount, 2); ?></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Payment Information -->
        <div class="payment-info">
            <strong>Payment Information:</strong><br>
            Payment Method: PhonePe (Bulk Payment)<br>
            Transaction ID: <?php echo $payment->payment_code; ?><br>
            Payment Date: <?php echo date('d M Y, h:i A', $payment->payment_timestamp); ?>
        </div>
        
        <!-- Total Section -->
        <div class="total-section">
            <div class="total-label">Total Amount:</div>
            <div class="total-amount"><?php echo currency('', 'def') . number_format($payment->amount, 2); ?></div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p>For any queries, please contact us at <?php echo $this->Crud_model->get_type_name_by_id('general_settings', '2', 'value'); ?></p>
        </div>
    </div>
</body>
</html>
