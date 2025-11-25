<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// PhonePe Contribution Account Configuration

// UAT (Testing) Configuration
$config['phonepe_contribution_merchant_id'] = 'PGTESTPAYUAT86'; // Replace with Contribution Account Merchant ID
$config['phonepe_contribution_salt_key'] = '96434309-7796-489d-8924-ab56988a6076'; // Replace with Contribution Account Salt Key
$config['phonepe_contribution_salt_index'] = 1; 
$config['phonepe_contribution_base_url'] = 'https://api-preprod.phonepe.com/apis/pg-sandbox'; // Change to production URL for live

// Production URL: https://api.phonepe.com/apis/hermes
