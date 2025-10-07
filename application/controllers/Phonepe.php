<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PhonePe Controller for Admin Bulk Payments
 */
class Phonepe extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Load required libraries and models
        $this->load->database();
        $this->load->model('Crud_model');
        $this->load->library('phonepe'); // Your existing PhonePe library
        // $this->load->library('session'); // Add this

        
        // Session is autoloaded in CodeIgniter config, no need to load it manually
    }

    /**
     * Initiate PhonePe Payment for Admin Bulk Payments
     */
    public function initiate_payment()
    {
        try {
            log_message('debug', '=== PhonePe Bulk Payment Initiation Started ===');
            
            // Get POST data from the form
            $amount = $this->input->post('amount');
            $payment_type = $this->input->post('payment_type');
            $payment_ids = $this->input->post('payment_ids');
            $return_url = $this->input->post('return_url');
            $cancel_url = $this->input->post('cancel_url');

            log_message('debug', 'Amount: ' . $amount);
            log_message('debug', 'Payment Type: ' . $payment_type);
            log_message('debug', 'Payment IDs: ' . $payment_ids);

            // Validate inputs
            if (empty($amount) || empty($payment_type) || empty($payment_ids)) {
                log_message('error', 'Invalid payment data received');
                $this->session->set_flashdata('danger_alert', 'Invalid payment data');
                redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
                return;
            }

            // Generate unique merchant transaction ID for bulk payment
            $merchant_transaction_id = 'BULK_' . time() . '_' . rand(1000, 9999);
            log_message('debug', 'Generated Transaction ID: ' . $merchant_transaction_id);

            // Get admin details
            $admin_id = $this->session->userdata('admin_id') ?: '1';
            $mobile_number = '9999999999'; // Default or fetch from admin table

            // Store bulk payment data in session for callback
            $this->session->set_userdata('bulk_payment_pending', array(
                'merchant_transaction_id' => $merchant_transaction_id,
                'payment_ids' => $payment_ids,
                'amount' => $amount,
                'payment_type' => $payment_type,
                'initiated_at' => date('Y-m-d H:i:s')
            ));

            // Prepare data for your existing PhonePe library's create_payment method
            $phonepe_data = array(
                'merchant_transaction_id' => $merchant_transaction_id,
                'amount' => $amount * 100, // Convert rupees to paise
                'redirect_url' => base_url('phonepe/bulk_payment_return'),
                'callback_url' => base_url('phonepe/bulk_payment_callback'),
                'mobile_number' => $mobile_number,
                'user_id' => 'ADMIN_' . $admin_id
            );

            log_message('debug', 'PhonePe Data: ' . json_encode($phonepe_data));

            // Call your existing PhonePe library's create_payment method
            $response = $this->phonepe->create_payment($phonepe_data);

            log_message('debug', 'PhonePe Response: ' . json_encode($response));

            // Check if payment initiation was successful
            if (isset($response['success']) && $response['success'] === true) {
                // Extract payment URL from response
                $payment_url = $response['data']['instrumentResponse']['redirectInfo']['url'] ?? null;
                
                if ($payment_url) {
                    log_message('debug', 'Redirecting to PhonePe: ' . $payment_url);
                    // Redirect user to PhonePe payment page
                    redirect($payment_url);
                } else {
                    log_message('error', 'Payment URL not found in PhonePe response');
                    $this->session->set_flashdata('danger_alert', 'Failed to get payment URL from PhonePe');
                    redirect($cancel_url);
                }
            } else {
                // Payment initiation failed
                $error_message = $response['message'] ?? 'Payment initiation failed';
                log_message('error', 'PhonePe Error: ' . $error_message);
                $this->session->set_flashdata('danger_alert', 'PhonePe Error: ' . $error_message);
                redirect($cancel_url);
            }

        } catch (Exception $e) {
            log_message('error', 'PhonePe Bulk Payment Exception: ' . $e->getMessage());
            $this->session->set_flashdata('danger_alert', 'Payment gateway error. Please try again.');
            redirect(base_url('admin/earnings/payment_cart'));
        }
    }

    /**
     * Return URL - User is redirected here after completing payment on PhonePe
     */
    public function bulk_payment_return()
    {
        log_message('debug', '=== PhonePe Return URL Called ===');
        
        // PhonePe sends transaction ID in different ways
        $merchant_transaction_id = $this->input->get('id') ?: 
                                   $this->input->post('transactionId') ?: 
                                   $this->input->get('transactionId');
        
        log_message('debug', 'Transaction ID from return: ' . $merchant_transaction_id);

        if (!$merchant_transaction_id) {
            // Try to get from session
            $bulk_data = $this->session->userdata('bulk_payment_pending');
            $merchant_transaction_id = isset($bulk_data['merchant_transaction_id']) ? $bulk_data['merchant_transaction_id'] : null;
        }

        if (!$merchant_transaction_id) {
            log_message('error', 'No transaction ID found in return URL');
            $this->session->set_flashdata('danger_alert', 'Invalid payment response');
            redirect(base_url('admin/earnings'));
            return;
        }

        // Verify and process the payment
        $this->verify_and_process_bulk_payment($merchant_transaction_id);
    }

    /**
     * Callback URL - PhonePe server calls this (server-to-server, optional)
     */
    public function bulk_payment_callback()
    {
        $input = file_get_contents('php://input');
        log_message('debug', '=== PhonePe Callback Received ===');
        log_message('debug', 'Callback Data: ' . $input);

        $data = json_decode($input, true);
        $merchant_transaction_id = isset($data['transactionId']) ? $data['transactionId'] : (isset($data['merchantTransactionId']) ? $data['merchantTransactionId'] : null);

        if ($merchant_transaction_id) {
            log_message('debug', 'Processing callback for: ' . $merchant_transaction_id);
            $this->verify_and_process_bulk_payment($merchant_transaction_id, true);
        } else {
            log_message('error', 'No transaction ID in callback data');
        }
    }

    /**
     * Verify Payment Status and Process Bulk Payments
     */
    private function verify_and_process_bulk_payment($merchant_transaction_id, $is_callback = false)
    {
        try {
            log_message('debug', '=== Verifying Bulk Payment: ' . $merchant_transaction_id . ' ===');

            // Verify payment using your existing library's verify_payment method
            $response = $this->phonepe->verify_payment($merchant_transaction_id);

            log_message('debug', 'Verification Response: ' . json_encode($response));

            // Check if payment is successful
            $is_success = isset($response['success']) && $response['success'] === true && 
                         isset($response['data']['state']) && $response['data']['state'] == 'COMPLETED';

            if ($is_success) {
                log_message('debug', 'Payment verified as COMPLETED');

                // Get payment IDs from session
                $bulk_payment_data = $this->session->userdata('bulk_payment_pending');
                
                if (!$bulk_payment_data) {
                    log_message('error', 'Bulk payment data not found in session');
                    if (!$is_callback) {
                        $this->session->set_flashdata('danger_alert', 'Payment session expired');
                        redirect(base_url('admin/earnings'));
                    }
                    return;
                }

                $payment_ids = json_decode($bulk_payment_data['payment_ids'], true);
                log_message('debug', 'Processing ' . count($payment_ids) . ' payments');

                // Update each payment record
                $success_count = 0;
                foreach ($payment_ids as $payment_id) {
                    log_message('debug', 'Processing payment ID: ' . $payment_id);

                    $payment_details = $this->db->get_where('package_payment', 
                        array('package_payment_id' => $payment_id))->row();
                    
                    if (!$payment_details) {
                        log_message('error', 'Payment record not found: ' . $payment_id);
                        continue;
                    }

                    $member_details = $this->db->get_where('member', 
                        array('member_id' => $payment_details->member_id))->row();
                    $plan_details = $this->db->get_where('plan', 
                        array('plan_id' => $payment_details->plan_id))->row();

                    if (!$member_details || !$plan_details) {
                        log_message('error', 'Member or Plan not found for payment: ' . $payment_id);
                        continue;
                    }

                    // Update member benefits
                    $member_data = array();
                    $member_data['membership'] = ($plan_details->plan_id == '1') ? 1 : 2;
                    $member_data['express_interest'] = $member_details->express_interest + $plan_details->express_interest;
                    $member_data['direct_messages'] = $member_details->direct_messages + $plan_details->direct_messages;
                    $member_data['photo_gallery'] = $member_details->photo_gallery + $plan_details->photo_gallery;

                    $package_info = array(
                        'current_package' => $plan_details->name,
                        'package_price' => $payment_details->amount,
                        'payment_type' => 'PhonePe Bulk'
                    );
                    $member_data['package_info'] = json_encode(array($package_info));

                    $this->db->where('member_id', $payment_details->member_id);
                    $this->db->update('member', $member_data);

                    // Update payment status
                    $payment_update = array(
                        'payment_status' => 'paid',
                        'payment_type' => 'PhonePe',
                        'payment_code' => $merchant_transaction_id,
                        'payment_details' => json_encode($response),
                        'purchase_datetime' => time(),
                        'payment_timestamp' => time()
                    );
                    
                    $this->db->where('package_payment_id', $payment_id);
                    $this->db->update('package_payment', $payment_update);

                    $success_count++;
                    log_message('debug', 'Successfully updated payment: ' . $payment_id);
                }

                log_message('debug', 'Updated ' . $success_count . ' out of ' . count($payment_ids) . ' payments');

                // Clear cache and session
                if (function_exists('recache')) {
                    recache();
                }
                $this->session->unset_userdata('cart_items');
                $this->session->unset_userdata('bulk_payment_data');
                $this->session->unset_userdata('bulk_payment_pending');

                // Only redirect if not a callback
                if (!$is_callback) {
                    $this->session->set_flashdata('alert', 'bulk_payment_success');
                    redirect(base_url('admin/earnings'));
                } else {
                    echo json_encode(array('status' => 'success', 'message' => 'Payment processed'));
                }
            } else {
                // Payment failed or pending
                $error_message = isset($response['message']) ? $response['message'] : (isset($response['data']['state']) ? $response['data']['state'] : 'Payment verification failed');
                log_message('error', 'Payment not completed. Status: ' . $error_message);
                
                if (!$is_callback) {
                    $this->session->set_flashdata('danger_alert', 'Payment failed: ' . $error_message);
                    redirect(base_url('admin/earnings/payment_cart'));
                } else {
                    echo json_encode(array('status' => 'failed', 'message' => $error_message));
                }
            }

        } catch (Exception $e) {
            log_message('error', 'PhonePe Verification Exception: ' . $e->getMessage());
            
            if (!$is_callback) {
                $this->session->set_flashdata('danger_alert', 'Payment verification error');
                redirect(base_url('admin/earnings'));
            } else {
                echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
            }
        }
    }
}
