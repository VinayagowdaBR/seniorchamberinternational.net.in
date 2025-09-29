<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phonepe {
    
    private $merchant_id;
    private $salt_key; 
    private $salt_index;
    private $base_url;
    
    public function __construct() {
        $ci =& get_instance();
        
        // Load configuration from database
        $this->merchant_id = $ci->db->get_where('business_settings', array('type' => 'phonepe_merchant_id'))->row()->value;
        $this->salt_key = $ci->db->get_where('business_settings', array('type' => 'phonepe_salt_key'))->row()->value;
        $this->salt_index = $ci->db->get_where('business_settings', array('type' => 'phonepe_salt_index'))->row()->value;
        $mode = $ci->db->get_where('business_settings', array('type' => 'phonepe_mode'))->row()->value;
        
        // Set API URL based on mode
        if($mode == 'sandbox') {
            $this->base_url = 'https://api-preprod.phonepe.com/apis/pg-sandbox';
        } else {
            $this->base_url = 'https://api.phonepe.com/apis/hermes';
        }
        
        // Debug log to check if values are loaded correctly
        log_message('debug', 'PhonePe Library Initialized - Merchant ID: ' . $this->merchant_id . ', Mode: ' . $mode);
    }
    
    public function create_payment($data) {
        $merchant_transaction_id = $data['merchant_transaction_id'];
        $amount = $data['amount']; // Amount should already be in paise
        $redirect_url = $data['redirect_url'];
        $callback_url = $data['callback_url'];
        $mobile_number = $data['mobile_number'];
        $user_id = $data['user_id'];
        
        $payload = [
            'merchantId' => $this->merchant_id,
            'merchantTransactionId' => $merchant_transaction_id,
            'merchantUserId' => $user_id,
            'amount' => $amount,
            'redirectUrl' => $redirect_url,
            'redirectMode' => 'POST',
            'callbackUrl' => $callback_url,
            'mobileNumber' => $mobile_number,
            'paymentInstrument' => [
                'type' => 'PAY_PAGE'
            ]
        ];
        
        $encoded_payload = base64_encode(json_encode($payload));
        $string_to_hash = $encoded_payload . '/pg/v1/pay' . $this->salt_key;
        $sha256_hash = hash('sha256', $string_to_hash);
        $checksum = $sha256_hash . '###' . $this->salt_index;
        
        $curl_data = [
            'request' => $encoded_payload
        ];
        
        $url = $this->base_url . '/pg/v1/pay';
        
        // Log request details for debugging
        log_message('debug', 'PhonePe Request URL: ' . $url);
        log_message('debug', 'PhonePe Request Data: ' . json_encode($curl_data));
        log_message('debug', 'PhonePe Checksum: ' . $checksum);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($curl_data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-VERIFY: ' . $checksum
            ]
        ]);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        // Log response for debugging
        log_message('debug', 'PhonePe Response Code: ' . $http_code);
        log_message('debug', 'PhonePe Response: ' . $response);
        
        if ($error) {
            log_message('error', 'PhonePe CURL Error: ' . $error);
            return ['success' => false, 'message' => 'Connection error: ' . $error];
        }
        
        $response_array = json_decode($response, true);
        
        // Check if response is valid
        if (!$response_array) {
            log_message('error', 'PhonePe Invalid JSON Response: ' . $response);
            return ['success' => false, 'message' => 'Invalid response from payment gateway'];
        }
        
        return $response_array;
    }
    
    public function verify_payment($merchant_transaction_id) {
        $string_to_hash = '/pg/v1/status/' . $this->merchant_id . '/' . $merchant_transaction_id . $this->salt_key;
        $sha256_hash = hash('sha256', $string_to_hash);
        $checksum = $sha256_hash . '###' . $this->salt_index;
        
        $url = $this->base_url . '/pg/v1/status/' . $this->merchant_id . '/' . $merchant_transaction_id;
        
        // Log request details for debugging
        log_message('debug', 'PhonePe Verification URL: ' . $url);
        log_message('debug', 'PhonePe Verification Checksum: ' . $checksum);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-VERIFY: ' . $checksum,
                'X-MERCHANT-ID: ' . $this->merchant_id
            ]
        ]);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        // Log response for debugging
        log_message('debug', 'PhonePe Verification Response Code: ' . $http_code);
        log_message('debug', 'PhonePe Verification Response: ' . $response);
        
        if ($error) {
            log_message('error', 'PhonePe Verification CURL Error: ' . $error);
            return ['success' => false, 'message' => 'Connection error: ' . $error];
        }
        
        $response_array = json_decode($response, true);
        
        // Check if response is valid
        if (!$response_array) {
            log_message('error', 'PhonePe Verification Invalid JSON Response: ' . $response);
            return ['success' => false, 'message' => 'Invalid response from payment gateway'];
        }
        
        return $response_array;
    }

    // Backward-compatible alias used by some controllers
    public function check_payment_status($merchant_transaction_id) {
        return $this->verify_payment($merchant_transaction_id);
    }
}
