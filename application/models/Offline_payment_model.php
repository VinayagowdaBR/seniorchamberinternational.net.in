<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Offline_payment_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    // Get invoice by ID
    function get_invoice_by_id($invoice_id) {
        return $this->db->get_where('offline_payment_invoices', ['id' => $invoice_id])->row();
    }

    // Get all invoices for admin
    function get_invoices_by_admin($admin_id) {
        $this->db->where('admin_id', $admin_id);
        $this->db->order_by('id', 'DESC');
        return $this->db->get('offline_payment_invoices')->result();
    }

    // Get all invoices (for super admin)
    function get_all_invoices() {
        $this->db->select('offline_payment_invoices.*, admin.name as admin_name');
        $this->db->from('offline_payment_invoices');
        $this->db->join('admin', 'admin.admin_id = offline_payment_invoices.admin_id', 'left');
        $this->db->order_by('offline_payment_invoices.id', 'DESC');
        return $this->db->get()->result();
    }

    // Get members for an invoice
    function get_invoice_members($invoice_id) {
        return $this->db->get_where('offline_payment_members', ['invoice_id' => $invoice_id])->result_array();
    }

    // Get total amount collected by admin
    function get_total_collected_by_admin($admin_id) {
        $this->db->select_sum('total_amount');
        $this->db->where('admin_id', $admin_id);
        $result = $this->db->get('offline_payment_invoices')->row();
        return $result->total_amount ?? 0;
    }

    // Get invoice count by admin
    function get_invoice_count_by_admin($admin_id) {
        return $this->db->where('admin_id', $admin_id)->count_all_results('offline_payment_invoices');
    }
}
