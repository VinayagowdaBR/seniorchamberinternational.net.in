<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Membership_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    // Get all active memberships
    public function get_all_memberships() {
        $this->db->where('status', 'active');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('memberships')->result();
    }
    
    // Get single membership by slug
    public function get_membership_by_slug($slug) {
        return $this->db->get_where('memberships', array('membership_slug' => $slug))->row();
    }
    
    // Add new membership
    public function add_membership($data) {
        return $this->db->insert('memberships', $data);
    }
    
    // Update membership
    public function update_membership($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('memberships', $data);
    }
    
    // Delete membership
    public function delete_membership($id) {
        $this->db->where('id', $id);
        return $this->db->delete('memberships');
    }
}
?>
