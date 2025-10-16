<?php
// Get data from controller
$invoice_number = $invoice->invoice_number;
$payment_date = date('d M Y, h:i A', strtotime($invoice->payment_date));
$transaction_id = $invoice->transaction_id;
$total_amount = $invoice->total_amount;
$base_amount = $invoice->base_amount;
$gst_amount = $invoice->gst_amount;
$gst_percentage = $invoice->gst_percentage;
$total_members = $invoice->total_members;
$plan_name = $invoice->plan_name;
$payment_method = ucfirst($invoice->payment_method);
$paid_by_name = $paid_by ? $paid_by->name : 'Unknown';
$paid_by_email = $paid_by ? $paid_by->email : '';
?>

<style>
/* Modern Invoice Styling */
.invoice-container {
    max-width: 1200px;
    margin: 20px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.invoice-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    position: relative;
    overflow: hidden;
}

.invoice-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.invoice-header h1 {
    font-size: 32px;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.invoice-header .subtitle {
    font-size: 16px;
    opacity: 0.9;
}

.invoice-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 30px 40px;
    background: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
}

.meta-item {
    display: flex;
    flex-direction: column;
}

.meta-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.meta-value {
    font-size: 16px;
    font-weight: 600;
    color: #212529;
}

.meta-value.highlight {
    color: #667eea;
    font-size: 20px;
}

.invoice-body {
    padding: 40px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #667eea;
    display: inline-block;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.info-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.info-card h4 {
    font-size: 14px;
    color: #6c757d;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card p {
    font-size: 16px;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.members-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.members-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.members-table thead th {
    color: white;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px;
    text-align: left;
}

.members-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background 0.2s;
}

.members-table tbody tr:hover {
    background: #f8f9fa;
}

.members-table tbody td {
    padding: 16px;
    font-size: 14px;
    color: #495057;
}

.members-table tbody tr:last-child {
    border-bottom: none;
}

.amount-breakdown {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    padding: 30px;
    border-radius: 8px;
    margin-top: 40px;
}

.breakdown-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    font-size: 16px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.breakdown-row:last-child {
    border-bottom: none;
    margin-top: 10px;
    padding-top: 20px;
    border-top: 2px solid #4caf50;
}

.breakdown-row.total {
    font-size: 24px;
    font-weight: 700;
    color: #2e7d32;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #e9ecef;
}

.btn-custom {
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.btn-secondary-custom {
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
}

.btn-secondary-custom:hover {
    background: #667eea;
    color: white;
    text-decoration: none;
}

.btn-success-custom {
    background: #28a745;
    color: white;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-success-custom:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
    text-decoration: none;
    color: white;
}

.status-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.member-invoice-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.member-invoice-link:hover {
    text-decoration: underline;
}

@media print {
    .action-buttons {
        display: none;
    }
    .invoice-container {
        box-shadow: none;
    }
}

@media (max-width: 768px) {
    .invoice-meta {
        grid-template-columns: 1fr;
    }
    .info-grid {
        grid-template-columns: 1fr;
    }
    .action-buttons {
        flex-direction: column;
    }
    .btn-custom {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div id="content-container">
    <!-- Breadcrumb -->
    <div id="page-head">
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?=base_url()?>admin/bulkpayment">Bulk Payment</a></li>
            <li><a href="<?=base_url()?>admin/bulkpayment/invoices">Invoices</a></li>
            <li class="active">Invoice Details</li>
        </ol>
    </div>

    <div id="page-content">
        <div class="invoice-container">
            <!-- Invoice Header -->
            <div class="invoice-header">
                <h1><i class="fa fa-file-text"></i> BULK PAYMENT INVOICE</h1>
                <div class="subtitle">Complete payment details and member breakdown</div>
            </div>

            <!-- Invoice Meta Information -->
            <div class="invoice-meta">
                <div class="meta-item">
                    <div class="meta-label">Invoice Number</div>
                    <div class="meta-value highlight"><?php echo $invoice_number; ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Payment Date</div>
                    <div class="meta-value"><?php echo $payment_date; ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Transaction ID</div>
                    <div class="meta-value"><?php echo $transaction_id; ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Status</div>
                    <div class="meta-value">
                        <span class="status-badge status-completed">
                            <i class="fa fa-check-circle"></i> Completed
                        </span>
                    </div>
                </div>
            </div>

            <!-- Invoice Body -->
            <div class="invoice-body">
                <!-- Payment Information -->
                <h3 class="section-title">Payment Information</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <h4><i class="fa fa-user"></i> Paid By</h4>
                        <p><?php echo $paid_by_name; ?></p>
                        <?php if ($paid_by_email): ?>
                            <small style="color: #6c757d;"><?php echo $paid_by_email; ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="info-card">
                        <h4><i class="fa fa-credit-card"></i> Payment Method</h4>
                        <p><?php echo $payment_method; ?></p>
                    </div>
                    <div class="info-card">
                        <h4><i class="fa fa-gift"></i> Package</h4>
                        <p><?php echo $plan_name; ?></p>
                    </div>
                    <div class="info-card">
                        <h4><i class="fa fa-users"></i> Total Members</h4>
                        <p><?php echo $total_members; ?> Members</p>
                    </div>
                </div>

                <!-- Member Details -->
                <h3 class="section-title" style="margin-top: 40px;">Member Details</h3>
                <table class="members-table">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Member Name</th>
                            <th width="150">Invoice Number</th>
                            <th width="120">Base Amount</th>
                            <th width="100">GST</th>
                            <th width="120">Total</th>
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
                                <?php if (isset($member['member_code']) && $member['member_code']): ?>
                                    <br><small style="color: #6c757d;">Code: <?php echo $member['member_code']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($member['invoice_number'])): ?>
                                    <a href="<?php echo base_url('admin/earnings/view_invoice/' . $member['invoice_number']); ?>" 
                                       class="member-invoice-link" target="_blank">
                                        <?php echo $member['invoice_number']; ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #6c757d;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>₹<?php echo number_format($member['base_amount'], 2); ?></td>
                            <td>
                                ₹<?php echo number_format($member['gst_amount'], 2); ?>
                                <br><small style="color: #6c757d;">(<?php echo $member['gst_percentage']; ?>%)</small>
                            </td>
                            <td><strong>₹<?php echo number_format($member['total_amount'], 2); ?></strong></td>
                        </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 40px;">
                                <i class="fa fa-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 10px;"></i>
                                No member details found
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Amount Breakdown -->
                <div class="amount-breakdown">
                    <div class="breakdown-row">
                        <span>Subtotal (Base Amount)</span>
                        <strong>₹<?php echo number_format($base_amount, 2); ?></strong>
                    </div>
                    <div class="breakdown-row">
                        <span>GST (<?php echo number_format($gst_percentage, 2); ?>%)</span>
                        <strong>₹<?php echo number_format($gst_amount, 2); ?></strong>
                    </div>
                    <div class="breakdown-row total">
                        <span>Grand Total</span>
                        <strong>₹<?php echo number_format($total_amount, 2); ?></strong>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="<?php echo base_url('admin/bulkpayment/download_invoice/' . $invoice->invoice_id); ?>" 
                       class="btn-custom btn-primary-custom" target="_blank">
                        <i class="fa fa-download"></i> Download PDF
                    </a>
                    
                    <button onclick="window.print()" class="btn-custom btn-secondary-custom">
                        <i class="fa fa-print"></i> Print Invoice
                    </button>
                    
                    <a href="<?php echo base_url('admin/bulkpayment/invoices'); ?>" 
                       class="btn-custom btn-success-custom">
                        <i class="fa fa-arrow-left"></i> Back to Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add animation on page load
    $('.invoice-container').hide().fadeIn(500);
    
    // Print functionality
    window.addEventListener('beforeprint', function() {
        document.title = 'Invoice - <?php echo $invoice_number; ?>';
    });
});
</script>
