<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_stories extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // Load database in case it's not autoloaded (though it likely is)
        $this->load->database();
    }

    public function index()
    {
        if (!is_cli()) {
            echo "This script can only be run from the command line.";
            return;
        }

        echo "Starting DB Test via Controller...\n";

        // 1. Check Table
        if (!$this->db->table_exists('happy_story')) {
            echo "ERROR: Table 'happy_story' not found.\n";
            return;
        }
        echo "Table 'happy_story' exists.\n";

        // 2. Insert Dummy Data
        echo "Table Fields:\n";
        $fields = $this->db->list_fields('happy_story');
        print_r($fields);

        // Check specifically for legion_id
        if (!in_array('legion_id', $fields)) {
            echo "CRITICAL ERROR: 'legion_id' column MISSING in 'happy_story' table!\n";
        }

        $data = array(
            'title' => 'CLI Test Story ' . date('H:i:s'),
            'description' => 'Test story inserted via Test_stories controller.',
            'date' => date('Y-m-d'),
            'program_date' => date('Y-m-d'),
            'legion_id' => 0, // Testing the default used for Super Admin
            'partner_name' => 'CLI Partner',
            'program_area' => 'CLI Area',
            'legion_name' => 'CLI Legion',
            'area_name' => 'CLI Location',
            'approval_status' => 1,
            'image' => '',
            'posted_by' => 1 // Assuming 1 exists
        );

        echo "Inserting data: " . json_encode($data) . "\n";

        try {
            // Check if DB debug is on, might help show errors
            $db_debug = $this->db->db_debug;
            $this->db->db_debug = TRUE; // Enable to trigger exceptions on DB errors? CI3 usually returns false.

            if ($this->db->insert('happy_story', $data)) {
                $insert_id = $this->db->insert_id();
                echo "SUCCESS: Inserted with ID: " . $insert_id . "\n";
                
                // 3. Verify
                $query = $this->db->get_where('happy_story', array('happy_story_id' => $insert_id));
                if ($query->num_rows() > 0) {
                    echo "VERIFICATION: Record found.\n";
                    print_r($query->row_array());
                } else {
                    echo "ERROR: Inserted but not found? Transaction issue?\n";
                }
            } else {
                echo "ERROR: Insert failed.\n";
                $error = $this->db->error();
                echo "DB Error: " . $error['message'] . "\n";
            }
        } catch (Throwable $e) {
            echo "FATAL ERROR: " . $e->getMessage() . "\n";
            echo "Trace: " . $e->getTraceAsString() . "\n";
        }
        $this->db->db_debug = $db_debug;
    }
}
