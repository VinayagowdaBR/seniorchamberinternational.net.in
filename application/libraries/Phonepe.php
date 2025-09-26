<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phonepe {
    
    private $merchant_id;
    private $salt_key; 
    private $salt_index;
    private $base_url;
    
    public function __construct() {
        $ci =& get_instance();
        
        $this->merchant_id = $ci->db->get_where('business_settings', array('type' => 'phonepe_merchant_id'))->row()->value;
        $this->salt_key = $ci->db->get_where('business_settings', array('type' => 'phonepe_salt_key'))->row()->value;
        $this->salt_index = $ci->db->get_where('business_settings', array('type' => 'phonepe_salt_index'))->row()->value;
        $mode = $ci->db->get_where('business_settings', array('type' => 'phonepe_mode'))->row()->value;
        
        if($mode == 'sandbox') {
            $this->base_url = 'https://api-preprod.phonepe.com/apis/pg-sandbox';
        } else {
            $this->base_url = 'https://api.phonepe.com/apis/hermes';
        }
    }
    
    public function create_payment($data) {
        $merchant_transaction_id = $data['merchant_transaction_id'];
        $amount = $data['amount'] * 100; // Convert to paise
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
        curl_close($curl);
        
        return json_decode($response, true);
    }
}
