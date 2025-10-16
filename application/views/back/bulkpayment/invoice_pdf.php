<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo $invoice->invoice_number; ?></title>
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
        
        /* Payment Details */
        .payment-details {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            margin-bottom: 15px;
        }
        
        .detail-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .detail-row {
            display: table-row;
        }
        
        .detail-cell {
            display: table-cell;
            width: 50%;
            padding: 8px;
            vertical-align: top;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
            font-size: 9pt;
        }
        
        .detail-value {
            color: #333;
            font-size: 10pt;
            margin-top: 3px;
        }
        
        /* Member Table */
        .members-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .members-table thead {
            background: #667eea;
            color: white;
        }
        
        .members-table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .members-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }
        
        .members-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .members-table tbody tr:last-child td {
            border-bottom: 2px solid #667eea;
        }
        
        /* Amount Breakdown */
        .amount-breakdown {
            background: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .breakdown-row {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .breakdown-row td {
            padding: 8px 0;
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
            margin-top: 10px;
        }
        
        .breakdown-total td {
            font-size: 16pt;
            font-weight: bold;
            color: #2e7d32;
            padding-top: 15px;
        }
        
        /* Footer */
        .invoice-footer {
            margin-top: 50px;
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
        
        /* Text Utilities */
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
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
            <h1>BULK PAYMENT INVOICE</h1>
            <div class="subtitle">Official Payment Receipt</div>
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
                    <div class="meta-value"><?php echo $invoice->invoice_number; ?></div>
                </td>
                <td class="meta-cell">
                    <div class="meta-label">Invoice Date</div>
                    <div class="meta-value"><?php echo date('d M Y', strtotime($invoice->payment_date)); ?></div>
                </td>
            </tr>
            <tr class="meta-row">
                <td class="meta-cell">
                    <div class="meta-label">Transaction ID</div>
                    <div class="meta-value"><?php echo $invoice->transaction_id; ?></div>
                </td>
                <td class="meta-cell">
                    <div class="meta-label">Status</div>
                    <div class="meta-value">
                        <span class="status-badge">✓ COMPLETED</span>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Payment Details -->
        <div class="payment-details">
            <h2 class="section-title">Payment Information</h2>
            <table class="detail-grid">
                <tr class="detail-row">
                    <td class="detail-cell">
                        <div class="detail-label">Paid By</div>
                        <div class="detail-value"><?php echo $admin->name; ?></div>
                        <?php if ($admin->email): ?>
                            <div class="text-muted" style="font-size: 8pt;"><?php echo $admin->email; ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="detail-cell">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value"><?php echo ucfirst($invoice->payment_method); ?></div>
                    </td>
                </tr>
                <tr class="detail-row">
                    <td class="detail-cell">
                        <div class="detail-label">Package</div>
                        <div class="detail-value"><?php echo $invoice->plan_name; ?></div>
                    </td>
                    <td class="detail-cell">
                        <div class="detail-label">Total Members</div>
                        <div class="detail-value"><?php echo $invoice->total_members; ?> Members</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Member Details Table -->
        <h2 class="section-title">Member Payment Details</h2>
        <table class="members-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="30%">Member Name</th>
                    <th width="20%">Invoice No.</th>
                    <th width="15%" class="text-right">Base Amount</th>
                    <th width="15%" class="text-right">GST</th>
                    <th width="15%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (!empty($members)):
                    $sl = 1;
                    foreach ($members as $member): 
                ?>
                <tr>
                    <td><?php echo $sl++; ?></td>
                    <td>
                        <strong><?php echo $member['member_name']; ?></strong>
                        <!-- ✅ FIX: Use member_profile_id instead of member_code -->
                        <?php if (isset($member['member_profile_id']) && $member['member_profile_id']): ?>
                            <br><span class="text-muted"><?php echo $member['member_profile_id']; ?></span>
                        <?php endif; ?>
                    </td>

                    <td><?php echo $member['invoice_number'] ?? 'N/A'; ?></td>
                    <td class="text-right">₹<?php echo number_format($member['base_amount'], 2); ?></td>
                    <td class="text-right">
                        ₹<?php echo number_format($member['gst_amount'], 2); ?>
                        <br><span class="text-muted">(<?php echo $member['gst_percentage']; ?>%)</span>
                    </td>
                    <td class="text-right"><strong>₹<?php echo number_format($member['total_amount'], 2); ?></strong></td>
                </tr>
                <?php 
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="6" class="text-center">No member details found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Amount Breakdown -->
        <div class="amount-breakdown">
            <table class="breakdown-table">
                <tr class="breakdown-row">
                    <td class="breakdown-label">Subtotal (Base Amount)</td>
                    <td class="breakdown-value">₹<?php echo number_format($invoice->base_amount, 2); ?></td>
                </tr>
                <tr class="breakdown-row">
                    <td class="breakdown-label">GST (<?php echo number_format($invoice->gst_percentage, 2); ?>%)</td>
                    <td class="breakdown-value">₹<?php echo number_format($invoice->gst_amount, 2); ?></td>
                </tr>
                <tr class="breakdown-row breakdown-total">
                    <td class="breakdown-label">GRAND TOTAL</td>
                    <td class="breakdown-value">₹<?php echo number_format($invoice->total_amount, 2); ?></td>
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
