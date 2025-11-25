<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phonepe_contribution extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Session library is already auto-loaded in autoload.php
        // No need to load it again here to avoid conflicts
        $this->load->library('phonepe_contribution');
        $this->load->database();
        $this->load->model('Crud_model');
        $this->load->library('email');
        $this->load->helper('url');
    }
    
    public function initiate_payment() {
        try {
            log_message('debug', '=== PhonePe Contribution Payment Initiation Started ===');
            
            // Get POST data
            $total_amount = $this->input->post('amount');
            $payment_type = $this->input->post('payment_type');
            $member_ids = $this->input->post('payment_ids');
            $plan_id = $this->input->post('plan_id');
            $return_url = $this->input->post('return_url');
            $cancel_url = $this->input->post('cancel_url');
            
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
                redirect($cancel_url);
                return;
            }
            
            // Get admin details
            $admin_id = $this->session->userdata('admin_id');
            $admin = $this->db->get_where('admin', ['admin_id' => $admin_id])->row();
            
            if (!$admin) {
                $this->session->set_flashdata('danger_alert', 'Admin session expired');
                redirect(base_url('admin/login'));
                return;
            }
            
            // Generate unique transaction ID
            $contribution_transaction_id = 'CONTRIB_' . time() . '_' . uniqid();
            $merchant_transaction_id = 'CONTRIB_MT_' . time() . '_' . rand(1000, 9999);
            
            // Convert amount to paise
            $amount_in_paise = $total_amount * 100;
            
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
            
            // Initiate PhonePe payment
            $response = $this->phonepe_contribution->create_payment($phonepe_data);
            
            // Update master record with response
            $this->db->where('contribution_bulk_payment_id', $contribution_bulk_payment_id);
            $this->db->update('contribution_bulk_payment_master', [
                'phonepe_response' => json_encode($response),
                'payment_status' => 'processing'
            ]);
            
            if ($response['success'] && isset($response['data']['instrumentResponse']['redirectInfo']['url'])) {
                $redirect_url = $response['data']['instrumentResponse']['redirectInfo']['url'];
                redirect($redirect_url);
            } else {
                $error_message = $response['message'] ?? 'Payment gateway error';
                log_message('error', 'PhonePe Contribution Error: ' . $error_message);
                
                $this->db->where('contribution_bulk_payment_id', $contribution_bulk_payment_id);
                $this->db->update('contribution_bulk_payment_master', [
                    'payment_status' => 'failed'
                ]);
                
                $this->session->set_flashdata('danger_alert', $error_message);
                redirect($cancel_url);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exception in contribution payment: ' . $e->getMessage());
            $this->session->set_flashdata('danger_alert', 'Payment processing error');
            redirect(base_url('admin/contributionpayment'));
        }
    }
    
    public function payment_return() {
        log_message('debug', '=== PhonePe Contribution Payment Return ===');
        
        $merchant_transaction_id = $this->input->post('transactionId') ?: $this->input->get('transactionId');
        
        if ($merchant_transaction_id) {
            // Verify payment
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
        log_message('debug', '=== PhonePe Contribution Callback Received ===');
        
        $callback_data = json_decode(file_get_contents('php://input'), true);
        log_message('debug', 'Contribution Callback Data: ' . json_encode($callback_data));
        
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
        $merchant_transaction_id = $response['data']['merchantTransactionId'];
        $phonepe_transaction_id = $response['data']['transactionId'];
        
        // Get pending payment data
        $payment_data = $this->session->userdata('pending_contribution_payment');
        
        if (!$payment_data) {
            // Try to find from database
            $master = $this->db->get_where('contribution_bulk_payment_master', [
                'phonepe_merchant_transaction_id' => $merchant_transaction_id
            ])->row();
            
            if ($master) {
                $payment_data = [
                    'contribution_bulk_payment_id' => $master->contribution_bulk_payment_id,
                    'member_ids' => json_decode($master->member_ids, true),
                    'plan_id' => $master->plan_id
                ];
            }
        }
        
        if (!$payment_data) {
            log_message('error', 'Contribution payment data not found');
            return;
        }
        
        // Update master record
        $this->db->where('contribution_bulk_payment_id', $payment_data['contribution_bulk_payment_id']);
        $this->db->update('contribution_bulk_payment_master', [
            'phonepe_transaction_id' => $phonepe_transaction_id,
            'payment_status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
        
        // Get updated master record
        $master = $this->db->get_where('contribution_bulk_payment_master', [
            'contribution_bulk_payment_id' => $payment_data['contribution_bulk_payment_id']
        ])->row();
        
        // Generate invoice
        $invoice_number = 'CONTRIB-INV-' . date('Ymd') . '-' . str_pad($payment_data['contribution_bulk_payment_id'], 6, '0', STR_PAD_LEFT);
        
        $plan = $this->db->get_where('plan', ['plan_id' => $payment_data['plan_id']])->row();
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
        
        // Clear session
        $this->session->unset_userdata('pending_contribution_payment');
        $this->session->unset_userdata('contribution_cart');
        
        $this->session->set_flashdata('success_alert', 'Contribution payment successful! Invoice: ' . $invoice_number);
    }
}
