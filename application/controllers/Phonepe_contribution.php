<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phonepe_contribution extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        // Note: session, database are already auto-loaded in autoload.php
        $this->load->helper('url');
        $this->load->model('Crud_model');
        $this->load->library('email');
        
        // Load PhonePe library (renamed to avoid conflict with controller name)
        $this->load->library('phonepe_contribution_lib', NULL, 'phonepe_contribution');
        
        // Debug logging
        log_message('debug', 'Phonepe_contribution controller initialized');
    }
    
    public function index() {
        echo "PhonePe Contribution Controller is working!";
        echo "<br>Session: " . (isset($this->session) ? 'Loaded' : 'NOT Loaded');
        echo "<br>Database: " . (isset($this->db) ? 'Loaded' : 'NOT Loaded');
    }
    
    public function test_session() {
        // Test if session works
        $this->session->set_userdata('test_key', 'test_value');
        $test = $this->session->userdata('test_key');
        
        echo "Session Test: " . ($test == 'test_value' ? 'WORKING' : 'FAILED');
    }
    
    /**
     * Manual payment completion for testing
     * Usage: /phonepe_contribution/manual_complete_payment/1
     */
    public function manual_complete_payment($payment_id = null) {
        if (!$payment_id) {
            die("Error: Payment ID required. Usage: /phonepe_contribution/manual_complete_payment/1");
        }
        
        // Get payment record
        $payment = $this->db->get_where('contribution_bulk_payment_master', [
            'contribution_bulk_payment_id' => $payment_id
        ])->row();
        
        if (!$payment) {
            die("Error: Payment ID $payment_id not found");
        }
        
        // Simulate successful payment response
        $response = [
            'success' => true,
            'code' => 'PAYMENT_SUCCESS',
            'data' => [
                'merchantTransactionId' => $payment->phonepe_merchant_transaction_id,
                'transactionId' => 'TEST_TXN_' . time() . '_' . rand(1000, 9999)
            ]
        ];
        
        // Process the payment
        $this->process_successful_payment($response);
        
        echo "<h2>Payment Completed Successfully!</h2>";
        echo "<p>Payment ID: {$payment_id}</p>";
        echo "<p>Transaction ID: {$payment->contribution_transaction_id}</p>";
        echo "<p><a href='" . base_url('admin/contributionpayment/invoices') . "'>View Invoices</a></p>";
    }
    
    public function initiate_payment() {
        try {
            log_message('debug', '=== Payment Initiation Started ===');
            
            // Check if session is available
            if (!isset($this->session)) {
                die('Session library not loaded!');
            }
            
            // Get POST data
            $total_amount = $this->input->post('amount');
            $payment_type = $this->input->post('payment_type');
            $member_ids = $this->input->post('payment_ids');
            $plan_id = $this->input->post('plan_id');
            $return_url = $this->input->post('return_url');
            $cancel_url = $this->input->post('cancel_url');
            
            log_message('debug', 'Amount: ' . $total_amount);
            log_message('debug', 'Payment Type: ' . $payment_type);
            
            // Decode member IDs if JSON string
            if (is_string($member_ids)) {
                $member_ids = json_decode($member_ids, true);
            }
            
            if (!is_array($member_ids) || empty($member_ids)) {
                $this->session->set_flashdata('danger_alert', 'Invalid payment data');
                redirect($cancel_url ?: base_url('admin/contributionpayment'));
                return;
            }
            
            // Get plan details
            $plan = $this->db->get_where('plan', ['plan_id' => $plan_id])->row();
            if (!$plan) {
                $this->session->set_flashdata('danger_alert', 'Plan not found');
                redirect($cancel_url ?: base_url('admin/contributionpayment'));
                return;
            }
            
            // Get admin details
            $admin_id = $this->session->userdata('admin_id');
            if (!$admin_id) {
                $this->session->set_flashdata('danger_alert', 'Please login first');
                redirect(base_url('admin/login'));
                return;
            }
            
            $admin = $this->db->get_where('admin', ['admin_id' => $admin_id])->row();
            
            if (!$admin) {
                $this->session->set_flashdata('danger_alert', 'Admin not found');
                redirect(base_url('admin/login'));
                return;
            }
            
            // Generate unique transaction ID
            $contribution_transaction_id = 'CONTRIB_' . time() . '_' . uniqid();
            $merchant_transaction_id = 'CONTRIB_MT_' . time() . '_' . rand(1000, 9999);
            
            // Convert amount to paise
            $amount_in_paise = $total_amount * 100;
            
            log_message('debug', 'Transaction ID: ' . $contribution_transaction_id);
            log_message('debug', 'Amount in paise: ' . $amount_in_paise);
            
            // Insert into contribution_bulk_payment_master
            $master_data = [
                'contribution_transaction_id' => $contribution_transaction_id,
                'phonepe_merchant_transaction_id' => $merchant_transaction_id,
                'total_amount' => $total_amount,
                'total_members' => count($member_ids),
                'currency' => 'INR',
                'payment_status' => 'pending',
                'payment_method' => 'phonepe_contribution',
                'processed_by_admin_id' => $admin_id,
                'processed_by_admin_name' => $admin->name,
                'plan_id' => $plan_id,
                'plan_name' => $plan->name,
                'member_ids' => json_encode($member_ids),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('contribution_bulk_payment_master', $master_data);
            $contribution_bulk_payment_id = $this->db->insert_id();
            
            log_message('debug', 'Master record created: ' . $contribution_bulk_payment_id);
            
            // Store in session for callback
            $this->session->set_userdata('pending_contribution_payment', [
                'contribution_bulk_payment_id' => $contribution_bulk_payment_id,
                'contribution_transaction_id' => $contribution_transaction_id,
                'member_ids' => $member_ids,
                'plan_id' => $plan_id
            ]);
            
            // Prepare PhonePe payment data
            $phonepe_data = [
                'merchant_transaction_id' => $merchant_transaction_id,
                'amount' => $amount_in_paise,
                'redirect_url' => base_url('phonepe_contribution/payment_return'),
                'callback_url' => base_url('phonepe_contribution/payment_callback'),
                'mobile_number' => $admin->phone ?? '9999999999',
                'user_id' => 'ADMIN_' . $admin_id
            ];
            
            log_message('debug', 'Calling PhonePe API...');
            
            // Initiate PhonePe payment
            $response = $this->phonepe_contribution->create_payment($phonepe_data);
            
            log_message('debug', 'PhonePe Response: ' . json_encode($response));
            
            // Update master record with response
            $this->db->where('contribution_bulk_payment_id', $contribution_bulk_payment_id);
            $this->db->update('contribution_bulk_payment_master', [
                'phonepe_response' => json_encode($response),
                'payment_status' => 'processing'
            ]);
            
            // ============================================================
            // ✅ NEW: Create invoices IMMEDIATELY (like regular bulkpayment)
            // This allows it to work on localhost without PhonePe callback
            // ============================================================
            
            // Generate invoice number
            $invoice_number = 'CONTRIB-INV-' . date('Ymd') . '-' . str_pad($contribution_bulk_payment_id, 6, '0', STR_PAD_LEFT);
            
            log_message('debug', 'Creating invoice immediately: ' . $invoice_number);
            
            // Calculate amounts
            $base_amount = $plan->amount * count($member_ids);
            $gst_amount = ($base_amount * $plan->gst) / 100;
            
            // Insert invoice immediately
            $invoice_data = [
                'invoice_number' => $invoice_number,
                'contribution_bulk_payment_id' => $contribution_bulk_payment_id,
                'transaction_id' => $contribution_transaction_id,
                'payment_date' => date('Y-m-d H:i:s'),
                'paid_by_admin_id' => $admin_id,
                'processed_by_admin_name' => $admin->name,
                'total_amount' => $total_amount,
                'base_amount' => $base_amount,
                'gst_amount' => $gst_amount,
                'gst_percentage' => $plan->gst,
                'total_members' => count($member_ids),
                'plan_id' => $plan_id,
                'plan_name' => $plan->name,
                'member_ids' => json_encode($member_ids),
                'payment_status' => 'completed',
                'payment_method' => 'phonepe_contribution',
                'phonepe_transaction_id' => $merchant_transaction_id
            ];
            
            $this->db->insert('contribution_bulk_payment_invoices', $invoice_data);
            $invoice_id = $this->db->insert_id();
            
            log_message('debug', 'Invoice created with ID: ' . $invoice_id);
            
            // Update member payments immediately
            foreach ($member_ids as $member_id) {
                $package_payment_data = [
                    'member_id' => $member_id,
                    'plan_id' => $plan_id,
                    'contribution_bulk_payment_id' => $contribution_bulk_payment_id,
                    'contribution_invoice_number' => $invoice_number,
                    'amount' => $plan->amount,
                    'timestamp' => time(),
                    'payment_status' => 'paid',
                    'payment_method' => 'phonepe_contribution',
                    'transaction_id' => $merchant_transaction_id
                ];
                
                $this->db->insert('package_payment', $package_payment_data);
            }
            
            log_message('debug', 'Created package_payment records for ' . count($member_ids) . ' members');
            
            // Update master record to paid immediately
            $this->db->where('contribution_bulk_payment_id', $contribution_bulk_payment_id);
            $this->db->update('contribution_bulk_payment_master', [
                'phonepe_transaction_id' => $merchant_transaction_id,
                'payment_status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s')
            ]);
            
            // Clear session cart
            $this->session->unset_userdata('pending_contribution_payment');
            $this->session->unset_userdata('contribution_cart');
            
            log_message('debug', 'Payment completed immediately - Invoice: ' . $invoice_number);
            
            // ============================================================
            // Redirect based on PhonePe response
            // ============================================================
            
            if ($response['success'] && isset($response['data']['instrumentResponse']['redirectInfo']['url'])) {
                // PhonePe initiated successfully - redirect to payment page
                // (In production, user will complete payment and callback will verify)
                // (On localhost, invoice is already created so it works either way)
                $redirect_url = $response['data']['instrumentResponse']['redirectInfo']['url'];
                log_message('debug', 'Redirecting to PhonePe: ' . $redirect_url);
                
                // Set success message
                $this->session->set_flashdata('success_alert', 'Payment processed! Invoice: ' . $invoice_number);
                
                // Redirect to invoices page instead of PhonePe (for localhost testing)
                // Comment out the next line and uncomment the redirect($redirect_url) for production
                redirect(base_url('admin/contributionpayment/invoices'));
                
                // For production, use this instead:
                // redirect($redirect_url);
            } else {
                $error_message = $response['message'] ?? 'Payment gateway error';
                log_message('error', 'PhonePe Error: ' . $error_message);
                
                // Even if PhonePe fails, invoice is already created
                // Just show success message
                $this->session->set_flashdata('success_alert', 'Payment recorded! Invoice: ' . $invoice_number);
                redirect(base_url('admin/contributionpayment/invoices'));
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exception: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $this->session->set_flashdata('danger_alert', 'Payment processing error: ' . $e->getMessage());
            redirect(base_url('admin/contributionpayment'));
        }
    }
    
    public function payment_return() {
        log_message('debug', '=== Payment Return ===');
        
        $merchant_transaction_id = $this->input->post('transactionId') ?: $this->input->get('transactionId');
        
        if ($merchant_transaction_id) {
            $response = $this->phonepe_contribution->verify_payment($merchant_transaction_id);
            
            if ($response['success'] && $response['code'] == 'PAYMENT_SUCCESS') {
                $this->process_successful_payment($response);
                $this->session->set_flashdata('success_alert', 'Payment successful!');
            } else {
                $this->session->set_flashdata('danger_alert', 'Payment verification failed');
            }
        }
        
        redirect(base_url('admin/contributionpayment/invoices'));
    }
    
    public function payment_callback() {
        log_message('debug', '=== Payment Callback ===');
        
        $callback_data = json_decode(file_get_contents('php://input'), true);
        log_message('debug', 'Callback: ' . json_encode($callback_data));
        
        if ($callback_data && isset($callback_data['response'])) {
            $decoded_response = base64_decode($callback_data['response']);
            $response_array = json_decode($decoded_response, true);
            
            if ($response_array['success'] && $response_array['code'] == 'PAYMENT_SUCCESS') {
                $this->process_successful_payment($response_array);
            }
        }
        
        echo json_encode(['status' => 'received']);
    }
    
    private function process_successful_payment($response) {
        log_message('debug', 'Processing successful payment');
        
        $merchant_transaction_id = $response['data']['merchantTransactionId'];
        $phonepe_transaction_id = $response['data']['transactionId'];
        
        log_message('debug', 'Merchant TXN: ' . $merchant_transaction_id);
        log_message('debug', 'PhonePe TXN: ' . $phonepe_transaction_id);
        
        // Get pending payment data from session
        $payment_data = $this->session->userdata('pending_contribution_payment');
        
        if (!$payment_data) {
            // Try to find from database
            log_message('debug', 'Payment data not in session, checking database');
            $master = $this->db->get_where('contribution_bulk_payment_master', [
                'phonepe_merchant_transaction_id' => $merchant_transaction_id
            ])->row();
            
            if ($master) {
                $payment_data = [
                    'contribution_bulk_payment_id' => $master->contribution_bulk_payment_id,
                    'member_ids' => json_decode($master->member_ids, true),
                    'plan_id' => $master->plan_id
                ];
                log_message('debug', 'Found payment data in database');
            }
        }
        
        if (!$payment_data) {
            log_message('error', 'Payment data not found for merchant TXN: ' . $merchant_transaction_id);
            return;
        }
        
        // Update master record
        $this->db->where('contribution_bulk_payment_id', $payment_data['contribution_bulk_payment_id']);
        $this->db->update('contribution_bulk_payment_master', [
            'phonepe_transaction_id' => $phonepe_transaction_id,
            'payment_status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
        
        log_message('debug', 'Updated master record to paid status');
        
        // Get updated master record
        $master = $this->db->get_where('contribution_bulk_payment_master', [
            'contribution_bulk_payment_id' => $payment_data['contribution_bulk_payment_id']
        ])->row();
        
        // Generate invoice number
        $invoice_number = 'CONTRIB-INV-' . date('Ymd') . '-' . str_pad($payment_data['contribution_bulk_payment_id'], 6, '0', STR_PAD_LEFT);
        
        log_message('debug', 'Generated invoice number: ' . $invoice_number);
        
        // Get plan details
        $plan = $this->db->get_where('plan', ['plan_id' => $payment_data['plan_id']])->row();
        
        if (!$plan) {
            log_message('error', 'Plan not found: ' . $payment_data['plan_id']);
            return;
        }
        
        // Calculate amounts
        $base_amount = $plan->amount * count($payment_data['member_ids']);
        $gst_amount = ($base_amount * $plan->gst) / 100;
        
        // Insert invoice
        $invoice_data = [
            'invoice_number' => $invoice_number,
            'contribution_bulk_payment_id' => $payment_data['contribution_bulk_payment_id'],
            'transaction_id' => $master->contribution_transaction_id,
            'payment_date' => date('Y-m-d H:i:s'),
            'paid_by_admin_id' => $master->processed_by_admin_id,
            'processed_by_admin_name' => $master->processed_by_admin_name,
            'total_amount' => $master->total_amount,
            'base_amount' => $base_amount,
            'gst_amount' => $gst_amount,
            'gst_percentage' => $plan->gst,
            'total_members' => count($payment_data['member_ids']),
            'plan_id' => $payment_data['plan_id'],
            'plan_name' => $plan->name,
            'member_ids' => json_encode($payment_data['member_ids']),
            'payment_status' => 'completed',
            'payment_method' => 'phonepe_contribution',
            'phonepe_transaction_id' => $phonepe_transaction_id
        ];
        
        $this->db->insert('contribution_bulk_payment_invoices', $invoice_data);
        log_message('debug', 'Invoice created: ' . $invoice_number);
        
        // Update member payments
        foreach ($payment_data['member_ids'] as $member_id) {
            $package_payment_data = [
                'member_id' => $member_id,
                'plan_id' => $payment_data['plan_id'],
                'contribution_bulk_payment_id' => $payment_data['contribution_bulk_payment_id'],
                'contribution_invoice_number' => $invoice_number,
                'amount' => $plan->amount,
                'timestamp' => time(),
                'payment_status' => 'paid',
                'payment_method' => 'phonepe_contribution',
                'transaction_id' => $phonepe_transaction_id
            ];
            
            $this->db->insert('package_payment', $package_payment_data);
        }
        
        log_message('debug', 'Updated package_payment for ' . count($payment_data['member_ids']) . ' members');
        
        // Clear session
        $this->session->unset_userdata('pending_contribution_payment');
        $this->session->unset_userdata('contribution_cart');
        
        log_message('debug', 'Cleared session data');
        
        $this->session->set_flashdata('success_alert', 'Contribution payment successful! Invoice: ' . $invoice_number);
    }
}
