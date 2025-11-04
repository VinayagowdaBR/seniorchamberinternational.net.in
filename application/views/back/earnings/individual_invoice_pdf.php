<!-- The individul downloade pdf  -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo $payment->invoice_number; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.6;
        }
        
        .invoice-container {
            padding: 20px;
        }
        
        /* Header Section */
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        .invoice-header h1 {
            font-size: 28pt;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .invoice-header .subtitle {
            font-size: 11pt;
            opacity: 0.9;
        }
        
        /* Organization Info */
        .org-info {
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
        }
        
        .org-info h3 {
            font-size: 14pt;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .org-info p {
            margin: 3px 0;
            font-size: 9pt;
            color: #555;
        }
        
        /* Invoice Meta */
        .invoice-meta {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        
        .meta-row {
            display: table-row;
        }
        
        .meta-cell {
            display: table-cell;
            padding: 10px;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        
        .meta-label {
            font-weight: bold;
            color: #667eea;
            font-size: 9pt;
            text-transform: uppercase;
        }
        
        .meta-value {
            font-size: 10pt;
            color: #333;
        }
        
        /* Section Title */
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            margin-bottom: 15px;
        }
        
        /* Info Card */
        .info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .info-row {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            color: #555;
            width: 40%;
        }
        
        .info-value {
            display: table-cell;
            color: #333;
        }
        
        /* Amount Breakdown */
        .amount-breakdown {
            background: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .breakdown-row td {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .breakdown-label {
            font-size: 10pt;
            color: #555;
        }
        
        .breakdown-value {
            text-align: right;
            font-weight: bold;
            font-size: 10pt;
        }
        
        .breakdown-total {
            border-top: 2px solid #4caf50 !important;
            padding-top: 15px !important;
        }
        
        .breakdown-total td {
            font-size: 16pt;
            font-weight: bold;
            color: #2e7d32;
            padding-top: 15px;
        }
        
        /* Footer */
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #777;
        }
        
        .footer-note {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        
        .footer-note p {
            margin: 5px 0;
            font-size: 9pt;
            color: #856404;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 15px;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-muted {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <h1>PAYMENT INVOICE</h1>
            <div class="subtitle">Member Payment Receipt</div>
        </div>
        
        <!-- Organization Info -->
        <div class="org-info">
            <h3><?php echo $org_name; ?></h3>
            <p><strong>Email:</strong> <?php echo $org_email; ?></p>
            <p><strong>Phone:</strong> <?php echo $org_phone; ?></p>
        </div>
        
        <!-- Invoice Meta Information -->
        <table class="invoice-meta">
            <tr class="meta-row">
                <td class="meta-cell">
                    <div class="meta-label">Invoice Number</div>
                    <div class="meta-value"><?php echo $payment->invoice_number; ?></div>
                </td>
                <td class="meta-cell">
                    <div class="meta-label">Invoice Date</div>
                    <div class="meta-value"><?php echo date('d M Y', $payment->purchase_datetime); ?></div>
                </td>
            </tr>
            <tr class="meta-row">
                <td class="meta-cell">
                    <div class="meta-label">Transaction ID</div>
                    <div class="meta-value"><?php echo $payment->payment_code ?? 'N/A'; ?></div>
                </td>
                <td class="meta-cell">
                    <div class="meta-label">Status</div>
                    <div class="meta-value">
                        <span class="status-badge">✓ PAID</span>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Member Information -->
        <h2 class="section-title">Member Information</h2>
        <div class="info-card">
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value"><?php echo $payment->first_name . ' ' . $payment->last_name; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Member Code:</div>
                <div class="info-value"><?php echo $payment->member_profile_id ?? 'N/A'; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo $payment->email ?? 'N/A'; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value"><?php echo $payment->mobile ?? 'N/A'; ?></div>
            </div>
        </div>
        
        <!-- Payment Information -->
        <h2 class="section-title">Payment Information</h2>
        <div class="info-card">
            <div class="info-row">
                <div class="info-label">Package:</div>
                <div class="info-value"><?php echo $payment->plan_name; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Payment Method:</div>
                <div class="info-value"><?php echo ucfirst($payment->payment_type ?? 'N/A'); ?></div>
            </div>
        </div>
        
        <!-- Amount Breakdown -->
        <div class="amount-breakdown">
            <table class="breakdown-table">
                <tr class="breakdown-row">
                    <td class="breakdown-label">Base Amount</td>
                    <td class="breakdown-value">₹<?php echo number_format($base_amount, 2); ?></td>
                </tr>
                <tr class="breakdown-row">
                    <td class="breakdown-label">GST (<?php echo number_format($gst_percentage, 2); ?>%)</td>
                    <td class="breakdown-value">₹<?php echo number_format($gst_amount, 2); ?></td>
                </tr>
                <tr class="breakdown-row breakdown-total">
                    <td class="breakdown-label">TOTAL PAID</td>
                    <td class="breakdown-value">₹<?php echo number_format($total_amount, 2); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Footer Notes -->
        <div class="footer-note">
            <p><strong>Note:</strong> This is a computer-generated invoice and does not require a signature.</p>
            <p>All payments are non-refundable. For queries, please contact our support team.</p>
        </div>
        
        <!-- Invoice Footer -->
        <div class="invoice-footer">
            <p><strong>Thank you for your payment!</strong></p>
            <p>Generated on: <?php echo $generated_date; ?></p>
            <p style="margin-top: 10px;">This invoice was generated electronically and is valid without signature.</p>
        </div>
    </div>
</body>
</html>
