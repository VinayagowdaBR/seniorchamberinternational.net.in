<?php
/**
 * Manual Payment Completion Script for Testing
 * This simulates a successful PhonePe payment callback
 * 
 * Usage: http://localhost/senior-new/test_complete_payment.php?payment_id=1
 */

// Load CodeIgniter
define('BASEPATH', TRUE);
$system_path = 'system';
$application_folder = 'application';

// Bootstrap CodeIgniter
require_once $system_path.'/core/CodeIgniter.php';

// Get payment ID from URL
$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;

if (!$payment_id) {
    die("Error: Please provide payment_id parameter. Example: test_complete_payment.php?payment_id=1");
}

// Load database
$CI =& get_instance();
$CI->load->database();

// Get payment record
$payment = $CI->db->get_where('contribution_bulk_payment_master', [
    'contribution_bulk_payment_id' => $payment_id
])->row();

if (!$payment) {
    die("Error: Payment ID $payment_id not found");
}

echo "<h2>Payment Completion Test</h2>";
echo "<p><strong>Payment ID:</strong> {$payment->contribution_bulk_payment_id}</p>";
echo "<p><strong>Transaction ID:</strong> {$payment->contribution_transaction_id}</p>";
echo "<p><strong>Current Status:</strong> {$payment->payment_status}</p>";
echo "<p><strong>Total Amount:</strong> ₹{$payment->total_amount}</p>";
echo "<p><strong>Total Members:</strong> {$payment->total_members}</p>";
echo "<hr>";

// Simulate PhonePe transaction ID
$phonepe_transaction_id = 'TEST_TXN_' . time() . '_' . rand(1000, 9999);

// Update master record to paid
$CI->db->where('contribution_bulk_payment_id', $payment_id);
$CI->db->update('contribution_bulk_payment_master', [
    'phonepe_transaction_id' => $phonepe_transaction_id,
    'payment_status' => 'paid',
    'paid_at' => date('Y-m-d H:i:s')
]);

echo "<p style='color: green;'>✓ Updated master record to PAID status</p>";

// Generate invoice number
$invoice_number = 'CONTRIB-INV-' . date('Ymd') . '-' . str_pad($payment_id, 6, '0', STR_PAD_LEFT);

echo "<p><strong>Generated Invoice Number:</strong> {$invoice_number}</p>";

// Get plan details
$plan = $CI->db->get_where('plan', ['plan_id' => $payment->plan_id])->row();

if (!$plan) {
    die("Error: Plan not found");
}

// Calculate amounts
$member_ids = json_decode($payment->member_ids, true);
$base_amount = $plan->amount * count($member_ids);
$gst_amount = ($base_amount * $plan->gst) / 100;

echo "<p><strong>Base Amount:</strong> ₹{$base_amount}</p>";
echo "<p><strong>GST ({$plan->gst}%):</strong> ₹{$gst_amount}</p>";

// Insert invoice
$invoice_data = [
    'invoice_number' => $invoice_number,
    'contribution_bulk_payment_id' => $payment_id,
    'transaction_id' => $payment->contribution_transaction_id,
    'payment_date' => date('Y-m-d H:i:s'),
    'paid_by_admin_id' => $payment->processed_by_admin_id,
    'processed_by_admin_name' => $payment->processed_by_admin_name,
    'total_amount' => $payment->total_amount,
    'base_amount' => $base_amount,
    'gst_amount' => $gst_amount,
    'gst_percentage' => $plan->gst,
    'total_members' => count($member_ids),
    'plan_id' => $payment->plan_id,
    'plan_name' => $plan->name,
    'member_ids' => json_encode($member_ids),
    'payment_status' => 'completed',
    'payment_method' => 'phonepe_contribution',
    'phonepe_transaction_id' => $phonepe_transaction_id
];

$CI->db->insert('contribution_bulk_payment_invoices', $invoice_data);
$invoice_id = $CI->db->insert_id();

echo "<p style='color: green;'>✓ Created invoice record (ID: {$invoice_id})</p>";

// Update member payments
$count = 0;
foreach ($member_ids as $member_id) {
    $package_payment_data = [
        'member_id' => $member_id,
        'plan_id' => $payment->plan_id,
        'contribution_bulk_payment_id' => $payment_id,
        'contribution_invoice_number' => $invoice_number,
        'amount' => $plan->amount,
        'timestamp' => time(),
        'payment_status' => 'paid',
        'payment_method' => 'phonepe_contribution',
        'transaction_id' => $phonepe_transaction_id
    ];
    
    $CI->db->insert('package_payment', $package_payment_data);
    $count++;
}

echo "<p style='color: green;'>✓ Created {$count} member payment records</p>";

echo "<hr>";
echo "<h3 style='color: green;'>✓ Payment Completed Successfully!</h3>";
echo "<p><a href='http://localhost/senior-new/admin/contributionpayment/invoices' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>View Invoices</a></p>";
echo "<p><a href='http://localhost/senior-new/admin/contributionpayment/invoice_detail/{$invoice_id}' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;'>View This Invoice</a></p>";
?>
