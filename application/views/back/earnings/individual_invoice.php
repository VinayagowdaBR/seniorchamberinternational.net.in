<?php
$invoice_number = $payment->invoice_number;
$payment_date = date('d M Y, h:i A', $payment->purchase_datetime);
$member_name = $payment->first_name . ' ' . $payment->last_name;
$member_code = $payment->member_profile_id ?? 'N/A'; 
$plan_name = $payment->plan_name;
$payment_method = ucfirst($payment->payment_type ?? 'N/A');
$transaction_id = $payment->payment_code ?? 'N/A';
?>

<style>
/* Reuse the same styles from bulk invoice_detail.php */
.invoice-container {
    max-width: 900px;
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
}

.invoice-header h1 {
    font-size: 32px;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.invoice-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 30px 40px;
    background: #f8f9fa;
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
}

.info-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #667eea;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
}

.amount-breakdown {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    padding: 30px;
    border-radius: 8px;
    margin-top: 30px;
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
    margin-top: 30px;
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

.status-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #d4edda;
    color: #155724;
}

@media print {
    .action-buttons {
        display: none;
    }
    .invoice-container {
        box-shadow: none;
    }
}
</style>

<div id="content-container">
    <!-- Breadcrumb -->
    <div id="page-head">
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?=base_url()?>admin/earnings">Earnings</a></li>
            <li class="active">Invoice Details</li>
        </ol>
    </div>

    <div id="page-content">
        <div class="invoice-container">
            <!-- Invoice Header -->
            <div class="invoice-header">
                <h1><i class="fa fa-file-text"></i> PAYMENT INVOICE</h1>
                <div class="subtitle">Member Payment Receipt</div>
            </div>

            <!-- Invoice Meta -->
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
                        <span class="status-badge">
                            <i class="fa fa-check-circle"></i> Paid
                        </span>
                    </div>
                </div>
            </div>

            <!-- Invoice Body -->
            <div class="invoice-body">
                <!-- Member Information -->
                <h3 class="section-title">Member Information</h3>
                <div class="info-card">
                    <div class="info-row">
                        <span><strong>Name:</strong></span>
                        <span><?php echo $member_name; ?></span>
                    </div>
                    <div class="info-row">
                        <span><strong>Member Code:</strong></span>
                        <span><?php echo $member_code; ?></span>
                    </div>
                    <div class="info-row">
                        <span><strong>Email:</strong></span>
                        <span><?php echo $payment->email ?? 'N/A'; ?></span>
                    </div>
                    <div class="info-row">
                        <span><strong>Phone:</strong></span>
                         <span><?php echo $payment->mobile ?? 'N/A'; ?></span>
                    </div>
                </div>

                <!-- Payment Information -->
                <h3 class="section-title">Payment Information</h3>
                <div class="info-card">
                    <div class="info-row">
                        <span><strong>Package:</strong></span>
                        <span><?php echo $plan_name; ?></span>
                    </div>
                    <div class="info-row">
                        <span><strong>Payment Method:</strong></span>
                        <span><?php echo $payment_method; ?></span>
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <div class="amount-breakdown">
                    <div class="breakdown-row">
                        <span>Base Amount</span>
                        <strong>₹<?php echo number_format($base_amount, 2); ?></strong>
                    </div>
                    <div class="breakdown-row">
                        <span>GST (<?php echo number_format($gst_percentage, 2); ?>%)</span>
                        <strong>₹<?php echo number_format($gst_amount, 2); ?></strong>
                    </div>
                    <div class="breakdown-row total">
                        <span>Total Paid</span>
                        <strong>₹<?php echo number_format($total_amount, 2); ?></strong>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="<?php echo base_url('admin/earnings/download_invoice/' . $invoice_number); ?>" 
                       class="btn-custom btn-primary-custom" target="_blank">
                        <i class="fa fa-download"></i> Download PDF
                    </a>
                    
                    <button onclick="window.print()" class="btn-custom btn-secondary-custom">
                        <i class="fa fa-print"></i> Print Invoice
                    </button>
                    
                    <a href="javascript:history.back()" class="btn-custom btn-secondary-custom">
                        <i class="fa fa-arrow-left"></i> Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
