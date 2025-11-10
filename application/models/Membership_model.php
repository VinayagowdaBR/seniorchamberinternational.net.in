<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Membership_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    // Get all memberships with optional status filter
    public function get_all($status = null) {
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('display_order', 'ASC');
        return $this->db->get('membership_types')->result();
    }
    
    // Get single membership by ID
    public function get_by_id($id) {
        return $this->db->get_where('membership_types', array('id' => $id))->row();
    }
    
    // Get single membership by slug
    public function get_by_slug($slug) {
        return $this->db->get_where('membership_types', array('slug' => $slug))->row();
    }
    
    // Get single membership by value
    public function get_by_value($value) {
        return $this->db->get_where('membership_types', array('membership_value' => $value))->row();
    }
    
    // Add new membership
    public function insert($data) {
        return $this->db->insert('membership_types', $data);
    }
    
    // Update membership
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('membership_types', $data);
    }
    
    // Delete membership
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('membership_types');
    }
    
    // Check if slug exists (for validation)
    public function slug_exists($slug, $exclude_id = null) {
        $this->db->where('slug', $slug);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results('membership_types') > 0;
    }
    
    // Check if membership value exists (for validation)
    public function value_exists($value, $exclude_id = null) {
        $this->db->where('membership_value', $value);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results('membership_types') > 0;
    }
    
    // Count members using this membership type
    public function count_members_by_type($membership_value) {
        return $this->db->where('membership', $membership_value)
                        ->count_all_results('member');
    }
    
    // DataTable server-side processing
    public function get_datatables($search = '', $start = 0, $length = 10, $order_column = 'id', $order_dir = 'ASC') {
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('slug', $search);
            $this->db->or_like('membership_value', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by($order_column, $order_dir);
        $this->db->limit($length, $start);
        
        return $this->db->get('membership_types')->result();
    }
    
    // Count all records
    public function count_all() {
        return $this->db->count_all('membership_types');
    }
    
    // Count filtered records
    public function count_filtered($search = '') {
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('slug', $search);
            $this->db->or_like('membership_value', $search);
            $this->db->group_end();
        }
        
        return $this->db->count_all_results('membership_types');
    }
}
?>
