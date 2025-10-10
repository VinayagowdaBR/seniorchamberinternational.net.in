<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PhonePe Admin Controller for Bulk Payments
 */
class Phonepe_admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load required resources
        $this->load->database();
        $this->load->model('Crud_model');
        $this->load->library('phonepe'); // loads application/libraries/Phonepe.php
    }

    /**
     * Initiate PhonePe Payment for Admin Bulk Payments
     */
    public function initiate_payment()
    {
        try {
            log_message('debug', '=== PhonePe Bulk Payment Initiation Started ===');

            $amount = $this->input->post('amount');
            $payment_type = $this->input->post('payment_type');
            $payment_ids = $this->input->post('payment_ids');
            $return_url = $this->input->post('return_url');
            $cancel_url = $this->input->post('cancel_url');

            log_message('debug', 'Amount: ' . $amount);
            log_message('debug', 'Payment Type: ' . $payment_type);
            log_message('debug', 'Payment IDs: ' . json_encode($payment_ids));

            if (empty($amount) || empty($payment_type) || empty($payment_ids)) {
                log_message('error', 'Invalid payment data received');
                $this->session->set_flashdata('danger_alert', 'Invalid payment data');
                redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
                return;
            }

            // Generate unique transaction ID
            $merchant_transaction_id = 'BULK_' . time() . '_' . rand(1000, 9999);
            log_message('debug', 'Generated Transaction ID: ' . $merchant_transaction_id);

            // Get admin info
            $admin_id = $this->session->userdata('admin_id') ?: '1';
            $mobile_number = '9999999999'; // placeholder admin number

            // Store payment session
            $this->session->set_userdata('bulk_payment_pending', [
                'merchant_transaction_id' => $merchant_transaction_id,
                'payment_ids' => json_encode($payment_ids),
                'amount' => $amount,
                'payment_type' => $payment_type,
                'initiated_at' => date('Y-m-d H:i:s')
            ]);

            // Prepare PhonePe payload
            $phonepe_data = [
                'merchant_transaction_id' => $merchant_transaction_id,
                'amount' => $amount * 100, // in paise
                'redirect_url' => base_url('phonepe_admin/bulk_payment_return'),
                'callback_url' => base_url('phonepe_admin/bulk_payment_callback'),
                'mobile_number' => $mobile_number,
                'user_id' => 'ADMIN_' . $admin_id
            ];

            log_message('debug', 'PhonePe Data: ' . json_encode($phonepe_data));

            // Initiate payment
            $response = $this->phonepe->create_payment($phonepe_data);
            log_message('debug', 'PhonePe Response: ' . json_encode($response));

            if (isset($response['success']) && $response['success'] === true) {
                $payment_url = $response['data']['instrumentResponse']['redirectInfo']['url'] ?? null;
                if ($payment_url) {
                    log_message('debug', 'Redirecting to PhonePe: ' . $payment_url);
                    redirect($payment_url);
                } else {
                    log_message('error', 'Payment URL missing in response');
                    $this->session->set_flashdata('danger_alert', 'Failed to get payment URL');
                    redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
                }
            } else {
                $error_message = $response['message'] ?? 'Payment initiation failed';
                log_message('error', 'PhonePe Error: ' . $error_message);
                $this->session->set_flashdata('danger_alert', 'PhonePe Error: ' . $error_message);
                redirect($cancel_url ?: base_url('admin/earnings/payment_cart'));
            }

        } catch (Exception $e) {
            log_message('error', 'PhonePe Bulk Payment Exception: ' . $e->getMessage());
            $this->session->set_flashdata('danger_alert', 'Payment gateway error. Please try again.');
            redirect(base_url('admin/earnings/payment_cart'));
        }
    }

    /**
     * Return URL - User is redirected here after payment
     */
    public function bulk_payment_return()
    {
        log_message('debug', '=== PhonePe Return URL Called ===');

        $merchant_transaction_id = $this->input->get('id') ??
                                   $this->input->post('transactionId') ??
                                   $this->input->get('transactionId');

        if (!$merchant_transaction_id) {
            $bulk_data = $this->session->userdata('bulk_payment_pending');
            $merchant_transaction_id = $bulk_data['merchant_transaction_id'] ?? null;
        }

        if (!$merchant_transaction_id) {
            log_message('error', 'No transaction ID found in return URL');
            $this->session->set_flashdata('danger_alert', 'Invalid payment response');
            redirect(base_url('admin/earnings'));
            return;
        }

        $this->verify_and_process_bulk_payment($merchant_transaction_id);
    }

    /**
     * Callback URL - called by PhonePe server
     */
    public function bulk_payment_callback()
    {
        $input = file_get_contents('php://input');
        log_message('debug', '=== PhonePe Callback Received ===');
        log_message('debug', 'Callback Data: ' . $input);

        $data = json_decode($input, true);
        $merchant_transaction_id = $data['transactionId'] ??
                                   $data['merchantTransactionId'] ?? null;

        if ($merchant_transaction_id) {
            log_message('debug', 'Processing callback for: ' . $merchant_transaction_id);
            $this->verify_and_process_bulk_payment($merchant_transaction_id, true);
        } else {
            log_message('error', 'No transaction ID in callback data');
        }
    }

    /**
     * Verify and process bulk payment result
     */
    private function verify_and_process_bulk_payment($merchant_transaction_id, $is_callback = false)
    {
        try {
            log_message('debug', '=== Verifying Bulk Payment: ' . $merchant_transaction_id . ' ===');

            $response = $this->phonepe->verify_payment($merchant_transaction_id);
            log_message('debug', 'Verification Response: ' . json_encode($response));

            $is_success = isset($response['success'], $response['data']['state']) &&
                          $response['success'] === true &&
                          $response['data']['state'] === 'COMPLETED';

            if ($is_success) {
                log_message('debug', 'Payment verified as COMPLETED');

                $bulk_payment_data = $this->session->userdata('bulk_payment_pending');
                if (!$bulk_payment_data) {
                    log_message('error', 'No session bulk payment data');
                    if (!$is_callback) {
                        $this->session->set_flashdata('danger_alert', 'Payment session expired');
                        redirect(base_url('admin/earnings'));
                    }
                    return;
                }

                $payment_ids = json_decode($bulk_payment_data['payment_ids'], true);
                log_message('debug', 'Processing ' . count($payment_ids) . ' payments');

                $this->db->trans_start();
                $success_count = 0;
                $generated_invoices = [];

                foreach ($payment_ids as $payment_id) {
                    $payment = $this->db->get_where('package_payment', ['package_payment_id' => $payment_id])->row();
                    if (!$payment) continue;

                    $member = $this->db->get_where('member', ['member_id' => $payment->member_id])->row();
                    $plan = $this->db->get_where('plan', ['plan_id' => $payment->plan_id])->row();
                    if (!$member || !$plan) continue;

                    $validity_start = date('Y-m-d');
                    $validity_end = date('Y-m-d', strtotime('+1 year'));
                    $invoice_year = date('Y');

                    $invoice_number = $this->generate_invoice_number($invoice_year);

                    // Update member details
                    $member_data = [
                        'membership' => ($plan->plan_id == '1') ? 1 : 2,
                        'express_interest' => $member->express_interest + $plan->express_interest,
                        'direct_messages' => $member->direct_messages + $plan->direct_messages,
                        'photo_gallery' => $member->photo_gallery + $plan->photo_gallery,
                        'package_info' => json_encode([[
                            'current_package' => $plan->name,
                            'package_price' => $payment->amount,
                            'payment_type' => 'PhonePe Bulk',
                            'invoice_number' => $invoice_number,
                            'validity_start' => $validity_start,
                            'validity_end' => $validity_end
                        ]])
                    ];
                    $this->db->where('member_id', $payment->member_id)->update('member', $member_data);

                    // Update payment record
                    $payment_update = [
                        'payment_status' => 'paid',
                        'payment_type' => 'PhonePe',
                        'payment_code' => $merchant_transaction_id,
                        'invoice_number' => $invoice_number,
                        'invoice_generated' => 1,
                        'invoice_year' => $invoice_year,
                        'validity_start_date' => $validity_start,
                        'validity_end_date' => $validity_end,
                        'validity_period' => '1 Year',
                        'bulk_payment_reference' => $merchant_transaction_id,
                        'payment_details' => json_encode($response),
                        'purchase_datetime' => time(),
                        'payment_timestamp' => time()
                    ];
                    $this->db->where('package_payment_id', $payment_id)->update('package_payment', $payment_update);

                    $generated_invoices[] = [
                        'payment_id' => $payment_id,
                        'invoice_number' => $invoice_number,
                        'member_name' => $member->first_name . ' ' . $member->last_name,
                        'amount' => $payment->amount,
                        'validity_start' => $validity_start,
                        'validity_end' => $validity_end,
                        'invoice_year' => $invoice_year
                    ];

                    $success_count++;
                    log_message('debug', 'Invoice ' . $invoice_number . ' generated for payment: ' . $payment_id);
                }

                $this->db->trans_complete();
                log_message('debug', "Generated $success_count invoices successfully");

                if (function_exists('recache')) recache();

                $this->session->set_userdata('bulk_payment_invoices', $generated_invoices);
                $this->session->unset_userdata(['cart_items', 'bulk_payment_data', 'bulk_payment_pending']);

                if (!$is_callback) {
                    $this->session->set_flashdata('alert', 'bulk_payment_success');
                    redirect(base_url('admin/earnings/bulk_payment_success_page'));
                } else {
                    echo json_encode(['status' => 'success', 'message' => 'Payment processed']);
                }
            } else {
                $error_message = $response['message'] ?? 'Payment verification failed';
                log_message('error', 'Payment not completed: ' . $error_message);

                if (!$is_callback) {
                    $this->session->set_flashdata('danger_alert', 'Payment failed: ' . $error_message);
                    redirect(base_url('admin/earnings/payment_cart'));
                } else {
                    echo json_encode(['status' => 'failed', 'message' => $error_message]);
                }
            }

        } catch (Exception $e) {
            log_message('error', 'Verification Exception: ' . $e->getMessage());
            if (!$is_callback) {
                $this->session->set_flashdata('danger_alert', 'Payment verification error');
                redirect(base_url('admin/earnings'));
            } else {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    /**
     * Generate unique invoice number (year-wise)
     * Format: INV-2025-00001
     */
    private function generate_invoice_number($year)
    {
        $last_invoice = $this->db->select('invoice_number')
            ->from('package_payment')
            ->where('invoice_number IS NOT NULL')
            ->where('invoice_year', $year)
            ->order_by('package_payment_id', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        $number = 1;
        if ($last_invoice && $last_invoice->invoice_number) {
            $parts = explode('-', $last_invoice->invoice_number);
            $number = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
        }

        return 'INV-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
