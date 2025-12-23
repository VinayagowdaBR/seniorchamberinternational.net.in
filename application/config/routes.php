<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// PhonePe Contribution Payment Routes
$route['phonepe_contribution/initiate_payment'] = 'phonepe_contribution/initiate_payment';
$route['phonepe_contribution/payment_return'] = 'phonepe_contribution/payment_return';
$route['phonepe_contribution/payment_callback'] = 'phonepe_contribution/payment_callback';

// Custom route for Admin Offline Payment
$route['admin/payment/make'] = 'offline_payment/make';
// Offline Payment Routes
$route['offline-payment'] = 'offline_payment/make';
$route['offline-payment/cart'] = 'offline_payment/offline_payment_cart';
$route['offline-payment/invoices'] = 'offline_payment/offline_invoice_list';
$route['offline-payment/invoice/(:num)'] = 'offline_payment/offline_invoice_detail/$1';
$route['offline-payment/invoice-pdf/(:num)'] = 'offline_payment/offline_invoice_pdf/$1';

// Custom route for Admin Offline Contribution
$route['admin/contribution/make'] = 'offline_contribution/make';
$route['admin/offline_contribution/make'] = 'offline_contribution/make';
// Offline Contribution Routes
$route['offline-contribution'] = 'offline_contribution/make';
$route['offline-contribution/cart'] = 'offline_contribution/offline_contribution_cart';
$route['offline-contribution/invoices'] = 'offline_contribution/offline_invoice_list';
$route['offline-contribution/invoice/(:num)'] = 'offline_contribution/offline_invoice_detail/$1';
$route['offline-contribution/invoice-pdf/(:num)'] = 'offline_contribution/offline_invoice_pdf/$1';

// Custom route for generating PDF

