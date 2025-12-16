<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Offline_payment extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Crud_model');
    
        /* cache control */
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    // Check if admin is logged in
    private function check_admin_login() {
        if ($this->session->userdata('admin_id') == NULL) {
            redirect(base_url() . 'admin/login', 'refresh');
        }
    }

    // Main page - Select members for offline payment
    function make() {
        $this->check_admin_login();

        $page_data['title'] = "Make Offline Payment";
        $page_data['page_name'] = "offline_payment_make"; 
        $page_data['folder'] = "offline_payment";
        $page_data['file'] = "make.php";
        $page_data['top'] = "dashboard.php"; 
        $page_data['bottom'] = "dashboard.php";
        
        $admin_id = $this->session->userdata('admin_id');
        
        // Get members based on admin's area/legion access
        $page_data['members'] = $this->Crud_model->get_members_by_admin_access($admin_id);
        
        // Get all plans for dropdown
        $page_data['plans'] = $this->db->get('plan')->result_array();

        // Flash messages
        if ($this->session->flashdata('success')) {
            $page_data['success_alert'] = $this->session->flashdata('success');
        }
        if ($this->session->flashdata('error')) {
            $page_data['danger_alert'] = $this->session->flashdata('error');
        }

        $this->load->view('back/index', $page_data);
    }

    // Add members to offline payment cart (AJAX)
    function offline_add_to_cart() {
        header('Content-Type: application/json');
        
        $member_ids = $this->input->post('member_ids');
        
        log_message('debug', 'Offline Add to Cart - Received: ' . json_encode($member_ids));
        
        if (!empty($member_ids) && is_array($member_ids)) {
            $cart = $this->session->userdata('offline_payment_cart');
            
            if (!is_array($cart)) {
                $cart = [];
            }
            
            $added_count = 0;
            foreach ($member_ids as $member_id) {
                if (!in_array($member_id, $cart)) {
                    $cart[] = $member_id;
                    $added_count++;
                }
            }
            
            $this->session->set_userdata('offline_payment_cart', $cart);
            
            log_message('debug', 'Cart after update: ' . json_encode($cart));
            
            echo json_encode([
                'success' => true, 
                'count' => count($cart), 
                'message' => $added_count . ' member(s) added to cart'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'No members selected'
            ]);
        }
    }

    // Get cart count (AJAX)
    function offline_get_cart_count() {
        header('Content-Type: application/json');
        
        $cart = $this->session->userdata('offline_payment_cart');
        
        if (!is_array($cart)) {
            $cart = [];
        }
        
        echo json_encode([
            'success' => true, 
            'count' => count($cart)
        ]);
    }

    // View offline payment cart
    function offline_payment_cart() {
        $this->check_admin_login();

        $page_data['title'] = "Offline Payment Cart";
        $page_data['page_name'] = "offline_payment_cart"; 
        $page_data['folder'] = "offline_payment";
        $page_data['file'] = "offline_payment_cart.php";
        $page_data['top'] = "dashboard.php"; 
        $page_data['bottom'] = "dashboard.php";

        $cart = $this->session->userdata('offline_payment_cart');
        
        if (!is_array($cart)) {
            $cart = [];
        }
        
        if (!empty($cart)) {
            $this->db->select('member.*, areas.name as area_name, legions.name as legion_name');
            $this->db->from('member');
            $this->db->join('areas', 'member.area_id = areas.id', 'left');
            $this->db->join('legions', 'member.legion_id = legions.id', 'left');
            $this->db->where_in('member.member_id', $cart);
            $page_data['cart_members'] = $this->db->get()->result_array();
        } else {
            $page_data['cart_members'] = [];
        }

        // Get all plans
        $page_data['plans'] = $this->db->get('plan')->result_array();

        // Flash messages
        if ($this->session->flashdata('success')) {
            $page_data['success_alert'] = $this->session->flashdata('success');
        }
        if ($this->session->flashdata('error')) {
            $page_data['danger_alert'] = $this->session->flashdata('error');
        }

        $this->load->view('back/index', $page_data);
    }

    // Remove from cart (AJAX)
    function offline_remove_from_cart() {
        header('Content-Type: application/json');
        
        $member_id = $this->input->post('member_id');
        $cart = $this->session->userdata('offline_payment_cart');
        
        if (!is_array($cart)) {
            $cart = [];
        }
        
        if (($key = array_search($member_id, $cart)) !== false) {
            unset($cart[$key]);
            $cart = array_values($cart);
            $this->session->set_userdata('offline_payment_cart', $cart);
            echo json_encode([
                'success' => true, 
                'count' => count($cart), 
                'message' => 'Member removed from cart'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Member not found in cart'
            ]);
        }
    }

    // Clear entire cart (AJAX)
    function offline_clear_cart() {
        header('Content-Type: application/json');
        
        $this->session->unset_userdata('offline_payment_cart');
        echo json_encode([
            'success' => true, 
            'message' => 'Cart cleared successfully'
        ]);
    }

    // Process offline bulk payment
    function offline_process_payment() {
        $this->check_admin_login();

        $cart = $this->session->userdata('offline_payment_cart');
        
        if (!is_array($cart) || empty($cart)) {
            $this->session->set_flashdata('error', 'Cart is empty. Please add members first.');
            redirect(base_url() . 'offline_payment/offline_payment_cart');
            return;
        }

        // Get form data
        $plan_id = $this->input->post('plan_id');
        $payment_method = $this->input->post('payment_method');
        $transaction_id = $this->input->post('transaction_id');
        $payment_date = $this->input->post('payment_date');
        $notes = $this->input->post('notes');

        // Validate
        if (empty($plan_id) || empty($payment_method)) {
            $this->session->set_flashdata('error', 'Please fill all required fields');
            redirect(base_url() . 'offline_payment/offline_payment_cart');
            return;
        }

        // Get plan details
        $plan = $this->db->get_where('plan', ['plan_id' => $plan_id])->row();
        
        if (!$plan) {
            $this->session->set_flashdata('error', 'Invalid plan selected');
            redirect(base_url() . 'offline_payment/offline_payment_cart');
            return;
        }

        $admin_id = $this->session->userdata('admin_id');
        $total_members = count($cart);
        
        // Calculate amounts
        $amount_per_member = $plan->amount;
        $base_amount = $amount_per_member * $total_members;
        $gst_percentage = isset($plan->gst) ? $plan->gst : 0;
        $gst_amount = ($base_amount * $gst_percentage) / 100;
        $total_amount = $base_amount + $gst_amount;

        // Generate unique invoice number
        $invoice_number = 'OFF-INV-' . date('Ymd') . '-' . strtoupper(substr(md5(time() . $admin_id), 0, 6));

        // Use provided payment date or current datetime
        $payment_datetime = !empty($payment_date) ? date('Y-m-d H:i:s', strtotime($payment_date)) : date('Y-m-d H:i:s');

        // Prepare invoice data
        $invoice_data = [
            'invoice_number' => $invoice_number,
            'admin_id' => $admin_id,
            'plan_id' => $plan_id,
            'plan_name' => $plan->name,
            'total_members' => $total_members,
            'base_amount' => $base_amount,
            'gst_percentage' => $gst_percentage,
            'gst_amount' => $gst_amount,
            'total_amount' => $total_amount,
            'payment_method' => $payment_method,
            'transaction_id' => $transaction_id,
            'payment_status' => 'completed',
            'payment_date' => $payment_datetime,
            'notes' => $notes
        ];

        // Insert invoice
        $this->db->insert('offline_payment_invoices', $invoice_data);
        $invoice_id = $this->db->insert_id();

        if (!$invoice_id) {
            $this->session->set_flashdata('error', 'Failed to create invoice. Please try again.');
            redirect(base_url() . 'offline_payment/offline_payment_cart');
            return;
        }

        // Get member details
        $this->db->select('member.*, areas.name as area_name, legions.name as legion_name');
        $this->db->from('member');
        $this->db->join('areas', 'member.area_id = areas.id', 'left');
        $this->db->join('legions', 'member.legion_id = legions.id', 'left');
        $this->db->where_in('member.member_id', $cart);
        $members = $this->db->get()->result_array();

        // Process each member
        foreach ($members as $member) {
            // Insert into offline_payment_members
            $member_payment_data = [
                'invoice_id' => $invoice_id,
                'member_id' => $member['member_id'],
                'member_profile_id' => $member['member_profile_id'],
                'member_name' => $member['first_name'] . ' ' . $member['last_name'],
                'member_email' => $member['email'],
                'area_name' => $member['area_name'],
                'legion_name' => $member['legion_name'],
                'amount' => $amount_per_member
            ];
            $this->db->insert('offline_payment_members', $member_payment_data);

            // Insert into package_payment table
            $payment_data = [
                'member_id' => $member['member_id'],
                'plan_id' => $plan_id,
                'amount' => $amount_per_member,
                'payment_type' => 'offline_bulk',
                'payment_status' => 'paid',
                'purchase_datetime' => strtotime($payment_datetime),
                'offline_invoice_id' => $invoice_id
            ];
            $this->db->insert('package_payment', $payment_data);

            // Update member's membership and benefits
            $membership = ($plan_id == 1) ? 1 : 2;
            $update_member_data = ['membership' => $membership];
            
            if (isset($plan->express_interest)) {
                $update_member_data['express_interest'] = $plan->express_interest;
            }
            if (isset($plan->direct_messages)) {
                $update_member_data['direct_messages'] = $plan->direct_messages;
            }
            if (isset($plan->photo_gallery)) {
                $update_member_data['photo_gallery'] = $plan->photo_gallery;
            }
            
            $this->db->where('member_id', $member['member_id']);
            $this->db->update('member', $update_member_data);

            // --- Send Individual Member Email ---
            
            $to = $member['email'];
            $sub = "Payment Confirmation - " . $plan->name;
            $msg = "Dear " . $member['first_name'] . " " . $member['last_name'] . ",<br><br>";
            $msg .= "Your payment for <b>" . $plan->name . "</b> has been successfully processed by the admin.<br>";
            $msg .= "Amount: " . $amount_per_member . "<br>";
            $msg .= "Transaction ID: " . $transaction_id . "<br><br>";
            $msg .= "Thank you,<br>Senior Chamber International";

            log_message('debug', 'Attempting to send confirmation email to Member: ' . $to);
            $this->_send_email($to, $sub, $msg);
            // ------------------------------------
        }

        // --- Send Summary Email to National Members (Membership ID = 3) ---

        // Admin who processed the payment
        $admin_details = $this->db->get_where('admin', array('admin_id' => $admin_id))->row();
        $admin_name = isset($admin_details->name) ? $admin_details->name : 'Admin';

        $summary_sub = "Offline Payment Summary - " . count($members) . " Members Paid";
        $summary_msg = "<h3>Offline Payment Processed</h3>";
        $summary_msg .= "<p><b>Processed By:</b> " . $admin_name . "</p>";
        $summary_msg .= "<p><b>Plan:</b> " . $plan->name . "</p>";
        $summary_msg .= "<p><b>Total Amount:</b> " . $total_amount . "</p>";
        $summary_msg .= "<p><b>Transaction ID:</b> " . $transaction_id . "</p>";
        $summary_msg .= "<br><h4>Paid Members List:</h4>";
        $summary_msg .= "<table border='1' cellpadding='5' cellspacing='0'>";
        $summary_msg .= "<tr><th>ID</th><th>Name</th><th>Email</th><th>Amount</th></tr>";
        
        foreach ($members as $mem) {
             $summary_msg .= "<tr>";
             $summary_msg .= "<td>" . $mem['member_profile_id'] . "</td>";
             $summary_msg .= "<td>" . $mem['first_name'] . " " . $mem['last_name'] . "</td>";
             $summary_msg .= "<td>" . $mem['email'] . "</td>";
             $summary_msg .= "<td>" . $amount_per_member . "</td>";
             $summary_msg .= "</tr>";
        }
        $summary_msg .= "</table>";
        
        // Fetch National Members (membership = 3)
        $national_members = $this->db->get_where('member', ['membership' => 3])->result_array();
        
        if (!empty($national_members)) {
            foreach ($national_members as $nm) {
                if (!empty($nm['email'])) {
                    log_message('debug', 'Attempting to send summary email to National Member: ' . $nm['email']);
                    $this->_send_email($nm['email'], $summary_sub, $summary_msg);
                }
            }
        }
        // ------------------------------------------------------------

        // Clear cart
        $this->session->unset_userdata('offline_payment_cart');

        // Set success message and redirect
        $this->session->set_flashdata('success', 'Offline payment processed successfully!');
        redirect(base_url() . 'offline_payment/offline_payment_success/' . $invoice_id);
    }

    // Offline payment success page
    function offline_payment_success($invoice_id) {
        $this->check_admin_login();

        $page_data['title'] = "Offline Payment Success";
        $page_data['page_name'] = "offline_payment_success"; 
        $page_data['folder'] = "offline_payment";
        $page_data['file'] = "offline_payment_success.php";
        $page_data['top'] = "dashboard.php"; 
        $page_data['bottom'] = "dashboard.php";

        $page_data['invoice'] = $this->db->get_where('offline_payment_invoices', ['id' => $invoice_id])->row();
        
        if (!$page_data['invoice']) {
            $this->session->set_flashdata('error', 'Invoice not found');
            redirect(base_url() . 'offline_payment/offline_invoice_list');
            return;
        }

        $page_data['members'] = $this->db->get_where('offline_payment_members', ['invoice_id' => $invoice_id])->result_array();
        $page_data['paid_by'] = $this->db->get_where('admin', ['admin_id' => $page_data['invoice']->admin_id])->row();

        $this->load->view('back/index', $page_data);
    }

    // Offline invoice list
    function offline_invoice_list() {
        $this->check_admin_login();

        $page_data['title'] = "Offline Payment Invoices";
        $page_data['page_name'] = "offline_invoice_list"; 
        $page_data['folder'] = "offline_payment";
        $page_data['file'] = "offline_invoice_list.php";
        $page_data['top'] = "dashboard.php"; 
        $page_data['bottom'] = "dashboard.php";

        $admin_id = $this->session->userdata('admin_id');
        
        if ($admin_id == 1) {
            $this->db->select('offline_payment_invoices.*, admin.name as admin_name, admin.email as admin_email');
            $this->db->from('offline_payment_invoices');
            $this->db->join('admin', 'admin.admin_id = offline_payment_invoices.admin_id', 'left');
            $this->db->order_by('offline_payment_invoices.id', 'DESC');
            $page_data['invoices'] = $this->db->get()->result();
        } else {
            $this->db->where('admin_id', $admin_id);
            $this->db->order_by('id', 'DESC');
            $page_data['invoices'] = $this->db->get('offline_payment_invoices')->result();
        }

        if ($this->session->flashdata('success')) {
            $page_data['success_alert'] = $this->session->flashdata('success');
        }
        if ($this->session->flashdata('error')) {
            $page_data['danger_alert'] = $this->session->flashdata('error');
        }

        $this->load->view('back/index', $page_data);
    }

    // Offline invoice detail
    function offline_invoice_detail($invoice_id) {
        $this->check_admin_login();

        $page_data['title'] = "Offline Invoice Detail";
        $page_data['page_name'] = "offline_invoice_detail"; 
        $page_data['folder'] = "offline_payment";
        $page_data['file'] = "offline_invoice_detail.php";
        $page_data['top'] = "dashboard.php"; 
        $page_data['bottom'] = "dashboard.php";

        $page_data['invoice'] = $this->db->get_where('offline_payment_invoices', ['id' => $invoice_id])->row();
        
        if (!$page_data['invoice']) {
            $this->session->set_flashdata('error', 'Invoice not found');
            redirect(base_url() . 'offline_payment/offline_invoice_list');
            return;
        }

        $page_data['members'] = $this->db->get_where('offline_payment_members', ['invoice_id' => $invoice_id])->result_array();
        $page_data['paid_by'] = $this->db->get_where('admin', ['admin_id' => $page_data['invoice']->admin_id])->row();

        $this->load->view('back/index', $page_data);
    }

    // Generate PDF invoice
    function offline_invoice_pdf($invoice_id) {
        $this->check_admin_login();
        
        $invoice = $this->db->get_where('offline_payment_invoices', ['id' => $invoice_id])->row();
        
        if (!$invoice) {
            $this->session->set_flashdata('error', 'Invoice not found');
            redirect(base_url() . 'offline_payment/offline_invoice_list');
            return;
        }

        $members = $this->db->get_where('offline_payment_members', ['invoice_id' => $invoice_id])->result_array();
        $paid_by = $this->db->get_where('admin', ['admin_id' => $invoice->admin_id])->row();

        // Data for view
        $data['invoice'] = $invoice;
        $data['members'] = $members;
        $data['paid_by'] = $paid_by;
        
        // Helper data from system settings
        $data['system_title'] = $this->Crud_model->get_type_name_by_id('general_settings', 1, 'value');
        $data['system_email'] = $this->Crud_model->get_type_name_by_id('general_settings', 2, 'value');
        $data['system_phone'] = $this->Crud_model->get_type_name_by_id('general_settings', 4, 'value');

        // Load PDF library
        $this->load->library('pdf');
        
        // Load HTML from new PDF view
        $html = $this->load->view('back/offline_payment/offline_invoice_pdf', $data, true);
        
        // Generate PDF
        $filename = 'Invoice_' . $invoice->invoice_number;
        $this->pdf->create($html, $filename);
    }
    // Custom email sending function using provided SMTP settings
    private function _send_email($to, $subject, $message) {
        $this->load->library('email');

        // SMTP Configuration
        $config = array(
            'protocol'    => 'smtp',
            'smtp_host'   => 'mail.seniorchamberinternational.net.in',
            'smtp_port'   => 587,
            'smtp_crypto' => 'tls',
            'smtp_user'   => 'info@seniorchamberinternational.net.in',
            'smtp_pass'   => 'Senioradmin@1234',
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'crlf'        => "\r\n",
            'smtp_timeout'=> 10,
        );

        $this->email->initialize($config);

        // Sender
        $this->email->from('info@seniorchamberinternational.net.in', 'Senior Chamber International');
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($message);

        if ($this->email->send()) {
            log_message('debug', 'Email sent successfully to ' . $to);
            return true;
        } else {
            log_message('error', 'Email failed to ' . $to . '. Error: ' . $this->email->print_debugger());
            return false;
        }
    }
}
