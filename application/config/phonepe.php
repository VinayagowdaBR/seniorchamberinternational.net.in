<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// PhonePe UAT (Testing) Configuration
$config['phonepe_merchant_id'] = 'PGTESTPAYUAT86'; // Your UAT Merchant ID
$config['phonepe_salt_key'] = '96434309-7796-489d-8924-ab56988a6076'; // Your UAT Salt Key
$config['phonepe_salt_index'] = 1; // Salt Index
$config['phonepe_base_url'] = 'https://api-preprod.phonepe.com/apis/pg-sandbox';

// PhonePe Production Configuration (use when going live)
/*
$config['phonepe_merchant_id'] = 'YOUR_PRODUCTION_MERCHANT_ID';
$config['phonepe_salt_key'] = 'YOUR_PRODUCTION_SALT_KEY';
$config['phonepe_salt_index'] = 1;
$config['phonepe_base_url'] = 'https://api.phonepe.com/apis/hermes';
*/
