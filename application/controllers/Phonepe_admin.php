<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phonepe_admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Crud_model');
        $this->load->library('phonepe');
    }

    /**
     * COMPLETE FIXED VERSION - Auto-creates package_payment records
     */
 /**
 * COMPLETE FIXED VERSION - Calculates amount from plan table with GST
 */
public function initiate_payment()
{
    try {
        log_message('debug', '=== PhonePe Bulk Payment Initiation Started ===');
        
        // Get POST data
        $total_amount = $this->input->post('amount');
        $payment_type = $this->input->post('payment_type');
        $member_ids = $this->input->post('payment_ids');
        $plan_id = $this->input->post('plan_id');
        $return_url = $this->input->post('return_url');
        $cancel_url = $this->input->post('cancel_url');
        
        log_message('debug', 'Total Amount: ' . $total_amount);
        log_message('debug', 'Payment Type: ' . $payment_type);
        log_message('debug', 'Plan ID: ' . $plan_id);
        log_message('debug', 'Member IDs (raw): ' . print_r($member_ids, true));
        
        // Decode member IDs if JSON string
        if (is_string($member_ids)) {
            $member_ids = json_decode($member_ids, true);
        }
        
        if (!is_array($member_ids) || empty($member_ids)) {
            log_message('error', 'Invalid member IDs');
            $this->session->set_flashdata('danger_alert', 'Invalid payment data');
            redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
            return;
        }
        
        log_message('debug', 'Processing ' . count($member_ids) . ' members');
        
        // STEP 1: Get plan details from database
        $plan = $this->db->get_where('plan', ['plan_id' => $plan_id])->row();
        
        if (!$plan) {
            log_message('error', 'Plan not found: ' . $plan_id);
            $this->session->set_flashdata('danger_alert', 'Invalid plan selected');
            redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
            return;
        }
        
        // STEP 2: Calculate per-member amount with GST
        $base_amount = $plan->amount; // e.g., 700
        $gst_percentage = $plan->gst; // e.g., 18.00
        $gst_amount = ($base_amount * $gst_percentage) / 100; // 700 * 0.18 = 126
        $amount_per_member = $base_amount + $gst_amount; // 700 + 126 = 826
        
        log_message('debug', 'Plan: ' . $plan->name);
        log_message('debug', 'Base Amount: ' . $base_amount);
        log_message('debug', 'GST %: ' . $gst_percentage);
        log_message('debug', 'GST Amount: ' . $gst_amount);
        log_message('debug', 'Amount per Member: ' . $amount_per_member);
        
        // STEP 3: Validate total amount
// STEP 3: Calculate total amount (use backend calculation, ignore frontend)
$calculated_total = $amount_per_member * count($member_ids);

// Log the difference if frontend sent wrong amount
if ($total_amount != $calculated_total) {
    log_message('warning', 'Frontend amount mismatch. Frontend: ' . $total_amount . ', Backend calculated: ' . $calculated_total . '. Using backend value.');
}

log_message('debug', 'Using backend calculated amount: ' . $calculated_total);

        
        // Generate bulk transaction ID
        $bulk_transaction_id = 'BULK_' . time() . '_' . rand(1000, 9999);
        $admin_id = $this->session->userdata('admin_id') ?: '1';
        $admin_name = $this->session->userdata('admin_name') ?: 'Admin';
        
        // STEP 4: Get or create package_payment records
        $payment_records = [];
        
        foreach ($member_ids as $member_id) {
            // Check if record already exists
            $existing = $this->db->get_where('package_payment', [
                'member_id' => $member_id,
                'plan_id' => $plan_id,
                'payment_status' => 'due'
            ])->row();
            
            if ($existing) {
                log_message('debug', 'Found existing payment for member: ' . $member_id);
                
                // Update the amount if it's different
                if ($existing->amount != $amount_per_member) {
                    $this->db->where('package_payment_id', $existing->package_payment_id);
                    $this->db->update('package_payment', ['amount' => $amount_per_member]);
                    log_message('debug', 'Updated amount for payment ID: ' . $existing->package_payment_id);
                }
                
                $payment_records[] = $existing;
            } else {
                // CREATE NEW RECORD
                log_message('debug', 'Creating new payment for member: ' . $member_id);
                
                $member = $this->db->get_where('member', ['member_id' => $member_id])->row();
                
                if (!$member) {
                    log_message('error', 'Member not found: ' . $member_id);
                    continue;
                }
                
                $payment_data = [
                    'plan_id' => $plan_id,
                    'member_id' => $member_id,
                    'payment_type' => 'bulk_payment',
                    'payment_status' => 'due',
                    'payment_details' => json_encode([
                        'base_amount' => $base_amount,
                        'gst_percentage' => $gst_percentage,
                        'gst_amount' => $gst_amount,
                        'plan_name' => $plan->name
                    ]),
                    'amount' => $amount_per_member,
                    'purchase_datetime' => time(),
                    'payment_code' => '',
                    'subscription_period' => 'yearly'
                ];
                
                $this->db->insert('package_payment', $payment_data);
                $new_id = $this->db->insert_id();
                
                log_message('debug', 'Created package_payment ID: ' . $new_id . ' with amount: ' . $amount_per_member);
                
                $payment_record = $this->db->get_where('package_payment', [
                    'package_payment_id' => $new_id
                ])->row();
                
                if ($payment_record) {
                    $payment_records[] = $payment_record;
                }
            }
        }
        
        if (empty($payment_records)) {
            log_message('error', 'No payment records created');
            $this->session->set_flashdata('danger_alert', 'Could not create payment records');
            redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
            return;
        }
        
        log_message('debug', 'Total payment records: ' . count($payment_records));
        
        // STEP 5: Start database transaction
        $this->db->trans_begin();
        
        // Create bulk_payment_master record
        $bulk_master_data = [
            'bulk_transaction_id' => $bulk_transaction_id,
            'phonepe_merchant_transaction_id' => $bulk_transaction_id,
            'total_amount' => $calculated_total,
            'total_members' => count($payment_records),
            'currency' => 'INR',
            'payment_status' => 'pending',
            'payment_method' => 'PhonePe',
            'processed_by_admin_id' => $admin_id,
            'processed_by_admin_name' => $admin_name,
            'payment_details' => json_encode([
                'payment_type' => $payment_type,
                'plan_id' => $plan_id,
                'plan_name' => $plan->name,
                'base_amount' => $base_amount,
                'gst_percentage' => $gst_percentage,
                'amount_per_member' => $amount_per_member,
                'member_ids' => $member_ids,
                'package_payment_ids' => array_column($payment_records, 'package_payment_id')
            ]),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('bulk_payment_master', $bulk_master_data);
        $bulk_payment_id = $this->db->insert_id();
        log_message('debug', 'Created bulk_payment_master ID: ' . $bulk_payment_id);
        
        // Link package_payment records to bulk_payment_master
        foreach ($payment_records as $payment) {
            $this->db->where('package_payment_id', $payment->package_payment_id);
            $this->db->update('package_payment', [
                'bulk_payment_id' => $bulk_payment_id,
                'payment_code' => $bulk_transaction_id,
                'payment_status' => 'processing'
            ]);
            log_message('debug', 'Linked payment ID: ' . $payment->package_payment_id);
        }
        
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            log_message('error', 'Transaction rollback');
            $this->session->set_flashdata('danger_alert', 'Database error occurred');
            redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
            return;
        }
        
        $this->db->trans_commit();
        log_message('debug', 'Database transaction committed successfully');
        
        // STEP 6: Initiate PhonePe payment
        $phonepe_data = [
            'merchant_transaction_id' => $bulk_transaction_id,
            'amount' => $calculated_total * 100, // Convert to paise
            'redirect_url' => base_url('phonepe_admin/bulk_payment_return'),
            'callback_url' => base_url('phonepe_admin/bulk_payment_callback'),
            'mobile_number' => '9999999999',
            'user_id' => 'ADMIN_' . $admin_id
        ];
        
        log_message('debug', 'Initiating PhonePe payment for: ₹' . $calculated_total);
        $response = $this->phonepe->create_payment($phonepe_data);
        log_message('debug', 'PhonePe Response: ' . json_encode($response));
        
        if (isset($response['success']) && $response['success'] === true) {
            $payment_url = $response['data']['instrumentResponse']['redirectInfo']['url'] ?? null;
            
            if ($payment_url) {
                // Update PhonePe response in bulk_payment_master
                $this->db->where('bulk_payment_id', $bulk_payment_id);
                $this->db->update('bulk_payment_master', [
                    'phonepe_response' => json_encode($response)
                ]);
                
                log_message('debug', 'Redirecting to PhonePe payment page');
                redirect($payment_url);
            }
        }
        
        // If we reach here, payment initiation failed
        log_message('error', 'PhonePe payment initiation failed');
        $this->db->where('bulk_payment_id', $bulk_payment_id);
        $this->db->update('bulk_payment_master', [
            'payment_status' => 'failed',
            'phonepe_response' => json_encode($response)
        ]);
        
        $this->session->set_flashdata('danger_alert', 'Payment gateway error. Please try again.');
        redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
        
    } catch (Exception $e) {
        log_message('error', 'Exception in initiate_payment: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        $this->session->set_flashdata('danger_alert', 'System error occurred');
        redirect(base_url('admin/earnings/payment_cart'));
    }
}







public function bulk_payment_return()
{
    log_message('debug', '=== Return URL ===');
    
    $bulk_transaction_id = $this->input->get('id') ?? $this->input->post('transactionId') ?? $this->input->post('merchantTransactionId');
    
    if (!$bulk_transaction_id) {
        $this->session->set_flashdata('danger_alert', 'Invalid response');
        redirect(base_url('admin/earnings'));
        return;
    }
    
    // RETRY LOGIC: Try up to 5 times with 2 second delay
    $max_retries = 5;
    $retry_count = 0;
    $status = 'FAILED';
    
    while ($retry_count < $max_retries) {
        log_message('debug', 'Status check attempt: ' . ($retry_count + 1));
        
        $status = $this->verify_and_process_bulk_payment($bulk_transaction_id);
        
        if ($status === 'SUCCESS') {
            break; // Payment successful
        }
        
        if ($status === 'PENDING') {
            // Wait 2 seconds before next attempt
            sleep(2);
            $retry_count++;
        } else {
            // Payment failed, stop retrying
            break;
        }
    }
    
    if ($status === 'SUCCESS') {
        redirect(base_url('admin/earnings/bulk_payment_success_page'));
    } else if ($status === 'PENDING') {
        $this->session->set_flashdata('warning_alert', 'Payment is being processed. Please check status later.');
        redirect(base_url('admin/earnings'));
    } else {
        $this->session->set_flashdata('danger_alert', 'Payment failed');
        redirect(base_url('admin/earnings/payment_cart'));
    }
}

    public function bulk_payment_callback()
    {
        $input = file_get_contents('php://input');
        log_message('debug', '=== Callback: ' . $input);
        
        $data = json_decode($input, true);
        $bulk_transaction_id = $data['merchantTransactionId'] ?? null;
        
        if ($bulk_transaction_id) {
            $this->verify_and_process_bulk_payment($bulk_transaction_id, true);
        }
    }

private function verify_and_process_bulk_payment($bulk_transaction_id, $is_callback = false)
{
    try {
        log_message('debug', '=== Verify: ' . $bulk_transaction_id);
        
        $response = $this->phonepe->verify_payment($bulk_transaction_id);
        
        log_message('debug', 'Payment State: ' . ($response['data']['state'] ?? 'UNKNOWN'));
        
        // Check if payment is successful
        $is_success = isset($response['success'], $response['data']['state']) &&
                     $response['success'] === true &&
                     $response['data']['state'] === 'COMPLETED';
        
        // Check if payment is pending
        $is_pending = isset($response['data']['state']) && 
                     $response['data']['state'] === 'PENDING';
        
        if ($is_pending) {
            log_message('debug', 'Payment is still PENDING');
            return 'PENDING';
        }
        
        if ($is_success) {
            // ... existing success processing code ...
            
            $bulk_payment = $this->db->get_where('bulk_payment_master', [
                'bulk_transaction_id' => $bulk_transaction_id
            ])->row();
            
            if (!$bulk_payment) {
                log_message('error', 'Bulk payment not found');
                return 'FAILED';
            }
            
            $phonepe_transaction_id = $response['data']['transactionId'] ?? null;
            
            $this->db->trans_begin();
            
            // Update bulk_payment_master
            $this->db->where('bulk_payment_id', $bulk_payment->bulk_payment_id);
            $this->db->update('bulk_payment_master', [
                'payment_status' => 'paid',
                'phonepe_transaction_id' => $phonepe_transaction_id,
                'phonepe_response' => json_encode($response),
                'paid_at' => date('Y-m-d H:i:s')
            ]);
            
            // Get child payments
            $child_payments = $this->db->get_where('package_payment', [
                'bulk_payment_id' => $bulk_payment->bulk_payment_id
            ])->result();
            
            $generated_invoices = [];
            
            foreach ($child_payments as $payment) {
                $member = $this->db->get_where('member', ['member_id' => $payment->member_id])->row();
                $plan = $this->db->get_where('plan', ['plan_id' => $payment->plan_id])->row();
                
                if (!$member || !$plan) continue;
                
                $validity_start = date('Y-m-d');
                $validity_end = date('Y-m-d', strtotime('+1 year'));
                $invoice_number = $this->generate_invoice_number(date('Y'));
                
                // Update member
                $this->db->where('member_id', $payment->member_id);
                $this->db->update('member', [
                    'membership' => ($plan->plan_id == '1') ? 1 : 2,
                    'express_interest' => $member->express_interest + $plan->express_interest,
                    'direct_messages' => $member->direct_messages + $plan->direct_messages,
                    'photo_gallery' => $member->photo_gallery + $plan->photo_gallery
                ]);
                
                // Update payment
                $this->db->where('package_payment_id', $payment->package_payment_id);
                $this->db->update('package_payment', [
                    'payment_status' => 'paid',
                    'payment_type' => 'PhonePe',
                    'payment_code' => $bulk_transaction_id,
                    'invoice_number' => $invoice_number,
                    'payment_details' => json_encode($response),
                    'purchase_datetime' => time()
                ]);
                
                $generated_invoices[] = [
                    'invoice_number' => $invoice_number,
                    'member_name' => $member->first_name . ' ' . $member->last_name,
                    'amount' => $payment->amount
                ];
                
                log_message('debug', 'Generated invoice: ' . $invoice_number . ' for member: ' . $payment->member_id);
            }
            
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                log_message('error', 'Transaction rollback');
                return 'FAILED';
            }
            
            $this->db->trans_commit();
            log_message('debug', 'Payment processing completed successfully');
            
            $this->session->set_userdata('bulk_payment_invoices', $generated_invoices);
            $this->session->unset_userdata(['cart_items', 'bulk_payment_data']);
            
            if (!$is_callback) {
                return 'SUCCESS';
            } else {
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
        
        log_message('error', 'Payment verification failed');
        return 'FAILED';
        
    } catch (Exception $e) {
        log_message('error', 'Verify exception: ' . $e->getMessage());
        return 'FAILED';
    }
}




private function generate_invoice_number($year)
{
    // Get last invoice number for this year from invoice_number format
    $last = $this->db->select('invoice_number')
        ->where('invoice_number IS NOT NULL')
        ->where('invoice_number LIKE', 'INV-' . $year . '-%')
        ->order_by('package_payment_id', 'DESC')
        ->limit(1)
        ->get('package_payment')
        ->row();
    
    $number = 1;
    
    if ($last && $last->invoice_number) {
        // Extract number from format: INV-2025-00001
        $parts = explode('-', $last->invoice_number);
        if (isset($parts[2])) {
            $number = intval($parts[2]) + 1;
        }
    }
    
    // Format: INV-2025-00001
    return 'INV-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
}




}
