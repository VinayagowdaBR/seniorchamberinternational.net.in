<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Crud_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function clear_cache()
    {
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    


public function get_report_by_range($start_date, $end_date)
{
    $admin_id = $this->session->userdata('admin_id'); // Get admin ID from session

    $this->db->where('date >=', $start_date);
    $this->db->where('date <=', $end_date);
    $this->db->where('posted_by', $admin_id); // Filter by posted_by matching admin_id
    $query = $this->db->get('happy_story');
    return $query->result();
}




public function update_story($id, $data)
{
    $this->db->where('happy_story_id', $id);
    $updated = $this->db->update('happy_story', $data);

    // Debug: check affected rows
    if ($this->db->affected_rows() > 0) {
        return true;
    } else {
        return false;
    }
}


    public function get_legion_and_area_by_admin($admin_id)
{
    log_message('debug', 'Method invoked: get_legion_and_area_by_admin | Admin ID: ' . $admin_id);
    $session_data = $this->session->userdata();
    log_message('debug', 'Full Session Data: ' . print_r($session_data, true));

    try {
        // Check if admin_id exists in admin_legion table
        $this->db->select('legion_id');
        $this->db->from('admin_legion');
        $this->db->where('admin_id', $admin_id);
        $query = $this->db->get();

        if ($query->num_rows() === 0) {
            $message = 'No legion assigned to this admin.';
            log_message('debug', 'Admin ID ' . $admin_id . ' => ' . $message);
            return ['status' => false, 'message' => $message];
        }

        $legion_id = $query->row()->legion_id;
        log_message('debug', 'Fetched legion_id: ' . $legion_id . ' for admin_id: ' . $admin_id);

        // Fetch legion and area details using joins
        $this->db->select('legions.id as legion_id, legions.name AS legion_name, areas.name AS area_name');
        $this->db->from('legions');
        $this->db->join('areas', 'legions.area_id = areas.id', 'left');
        $this->db->where('legions.id', $legion_id);
        $legion_query = $this->db->get();

        if ($legion_query->num_rows() === 0) {
            $message = 'Legion or area details not found.';
            log_message('debug', 'Legion ID ' . $legion_id . ' => ' . $message);
            return ['status' => false, 'message' => $message];
        }

        $result = $legion_query->row();
        log_message('debug', 'Legion ID: ' . $result->legion_id . ', Legion Name: ' . $result->legion_name . ', Area Name: ' . $result->area_name);

        return [
            'status' => true,
            'legion_id' => $result->legion_id,
            'legion_name' => $result->legion_name,
            'area_name' => $result->area_name
        ];
    } catch (Exception $e) {
        log_message('error', 'Exception in get_legion_and_area_by_admin: ' . $e->getMessage());
        return [
            'status' => false,
            'message' => 'An unexpected error occurred while retrieving legion and area details.'
        ];
    }
}


    public function get_legions_by_area($area_id)
    {
        return $this->db->select('id, name')
                        ->from('legions')
                        ->where('area_id', $area_id)
                        ->order_by('name', 'ASC')
                        ->get()
                        ->result_array();
    }
    

    

    ////////////  GET THE  AREA  AND THE LEGION //////
   public function get_areas_with_legions()
{
    // Add prefix to the SELECT statement
    $this->db->select('areas.id as area_id, areas.name as area_name, legions.id as legion_id, legions.name as legion_name, legions.prefix as legion_prefix');
    $this->db->from('areas');
    $this->db->join('legions', 'legions.area_id = areas.id', 'left');
    $query = $this->db->get();
    $result = $query->result();

    log_message('debug', 'DB query result count: ' . count($result));

    $areas = [];

    foreach ($result as $row) {
        log_message('debug', 'Processing row: area_id=' . $row->area_id . ', legion_id=' . $row->legion_id . ', prefix=' . (isset($row->legion_prefix) ? $row->legion_prefix : 'NULL'));

        $area_id = $row->area_id;
        if (!isset($areas[$area_id])) {
            $areas[$area_id] = [
                'id' => $area_id,
                'name' => $row->area_name,
                'legions' => []
            ];
            log_message('debug', "New area added: ID {$area_id}, Name {$row->area_name}");
        }

        if ($row->legion_id) {
            $areas[$area_id]['legions'][] = [
                'id' => $row->legion_id,
                'name' => $row->legion_name,
                'prefix' => $row->legion_prefix ?? '' // Add prefix field with null coalescing
            ];
            log_message('debug', "Added legion to area {$area_id}: Legion ID {$row->legion_id}, Name {$row->legion_name}, Prefix {$row->legion_prefix}");
        }
    }

    log_message('debug', 'Final areas array: ' . print_r($areas, true));

    return array_values($areas); // Reset keys to numeric
}
  

    // In Crud_model.php
        public function get_all_areas()
        {

            
            return $this->db->select('id, name')
                            ->from('areas')
                            ->order_by('name', 'ASC')
                            ->get()
                            ->result_array();
        }


    /////// INSETST LEGION //////
  public function insert_legion($data) 
{
    log_message('debug', 'insert_legion method invoked with data: ' . print_r($data, true));

    // Updated validation to include prefix
    if (!isset($data['name']) || !isset($data['area_id']) || !isset($data['prefix'])) {
        log_message('error', 'insert_legion validation failed: Missing required fields (name, area_id, or prefix)');
        return false; // validation failed
    }

    // Additional validation for prefix (optional but recommended)
    if (empty(trim($data['prefix']))) {
        log_message('error', 'insert_legion validation failed: Prefix cannot be empty');
        return false;
    }

    // Clean the prefix data
    $data['prefix'] = strtoupper(trim($data['prefix']));

    // Check if prefix already exists (optional uniqueness check)
    $this->db->where('prefix', $data['prefix']);
    $existing_prefix = $this->db->get('legions');
    if ($existing_prefix->num_rows() > 0) {
        log_message('error', 'insert_legion validation failed: Prefix already exists - ' . $data['prefix']);
        return false; // prefix already exists
    }

    // Insert the data
    $insert_result = $this->db->insert('legions', $data);
    
    if ($insert_result) {
        $insert_id = $this->db->insert_id();
        log_message('debug', 'insert_legion successful: Inserted legion with ID ' . $insert_id);
        return $insert_id; // Return the inserted ID
    } else {
        log_message('error', 'insert_legion failed: Database insert error');
        return false;
    }
}


    public function insert_area($data) {
        log_message('debug', 'insert_area method invoked with data: ' . print_r($data, true));
    
        if (!isset($data['name'])) {
            return false; // simple validation: area must have a name
        }
    
        // Insert area data into 'areas' table
        $insert = $this->db->insert('areas', $data);
    
        if ($insert) {
            // Return inserted ID if needed
            return $this->db->insert_id();
        } else {
            return false;
        }
    }
    

    /////////GET NAME BY TABLE NAME AND ID/////////////
    function get_type_name_by_id($type, $type_id = '', $field = 'name')
    {
        if ($type_id != '') {
            $l = $this->db->get_where($type, array(
                $type . '_id' => $type_id
            ));
            $n = $l->num_rows();
            if ($n > 0) {
                return $l->row()->$field;
            }
        }
    }
    function get_settings_value($type, $type_name = '', $field = 'value')
    {
        if ($type_name != '') {
            return $this->db->get_where($type, array('type' => $type_name))->row()->$field;
        }
    }

    /////////Filter One/////////////
    function filter_one($table, $type, $value)
    {
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where($type, $value);
        return $this->db->get()->result_array();
    }

    // FILE_UPLOAD
    function img_thumb($type, $id, $ext = '.jpg', $width = '400', $height = '400')
    {
        ini_set('display_errors',1);
	      error_reporting(E_ALL);
        $this->load->library('image_lib');
        ini_set("memory_limit", "-1");

        $config1['image_library']  = 'gd2';
        $config1['create_thumb']   = TRUE;
        $config1['maintain_ratio'] = TRUE;
        $config1['width']          = $width;
        $config1['height']         = $height;
        $config1['source_image']   = 'uploads/' . $type . '_image/' . $type . '_' . $id.'_thumb' . $ext;
    //echo '<pre>';print_r($config1);exit;
        $this->image_lib->initialize($config1);
        $this->image_lib->resize();
        $this->image_lib->clear();
    }

    // FILE_UPLOAD
    function file_up($name, $type, $id, $multi = '', $no_thumb = '', $ext = '.jpg')
    {
        if ($multi == '') {
            move_uploaded_file($_FILES[$name]['tmp_name'], 'uploads/' . $type . '_image/' . $type . '_' . $id . $ext);
            if ($no_thumb == '') {
                $this->Crud_model->img_thumb($type, $id, $ext);
            }
        } elseif ($multi == 'multi') {
            $ib = 1;
            foreach ($_FILES[$name]['name'] as $i => $row) {
                $ib = $this->file_exist_ret($type, $id, $ib);
                move_uploaded_file($_FILES[$name]['tmp_name'][$i], 'uploads/' . $type . '_image/' . $type . '_' . $id . '_' . $ib . $ext);
                if ($no_thumb == '') {
                    $this->Crud_model->img_thumb($type, $id . '_' . $ib, $ext);
                }
            }
        }
    }

    // FILE_UPLOAD : EXT :: FILE EXISTS
    function file_exist_ret($type, $id, $ib, $ext = '.jpg')
    {
        if (file_exists('uploads/' . $type . '_image/' . $type . '_' . $id . '_' . $ib . $ext)) {
            $ib = $ib + 1;
            $ib = $this->file_exist_ret($type, $id, $ib);
            return $ib;
        } else {
            return $ib;
        }
    }


    // FILE_VIEW
    function file_view($type, $id, $width = '100', $height = '100', $thumb = 'no', $src = 'no', $multi = '', $multi_num = '', $ext = '.jpg')
    {
        if ($multi == '') {
            if (file_exists('uploads/' . $type . '_image/' . $type . '_' . $id . $ext)) {
                if ($thumb == 'no') {
                    $srcl = base_url() . 'uploads/' . $type . '_image/' . $type . '_' . $id . $ext;
                } elseif ($thumb == 'thumb') {
                    $srcl = base_url() . 'uploads/' . $type . '_image/' . $type . '_' . $id . '_thumb' . $ext;
                }

                if ($src == 'no') {
                    return '<img src="' . $srcl . '" height="' . $height . '" width="' . $width . '" />';
                } elseif ($src == 'src') {
                    return $srcl;
                }
            }
            else{
                return base_url() . 'uploads/'. $type.'_image/male_default.jpg';
            }

        } else if ($multi == 'multi') {
            $num    = $this->Crud_model->get_type_name_by_id($type, $id, 'num_of_imgs');
            //$num = 2;
            $i      = 0;
            $p      = 0;
            $q      = 0;
            $return = array();
            while ($p < $num) {
                $i++;
                if (file_exists('uploads/' . $type . '_image/' . $type . '_' . $id . '_' . $i . $ext)) {
                    if ($thumb == 'no') {
                        $srcl = base_url() . 'uploads/' . $type . '_image/' . $type . '_' . $id . '_' . $i . $ext;
                    } elseif ($thumb == 'thumb') {
                        $srcl = base_url() . 'uploads/' . $type . '_image/' . $type . '_' . $id . '_' . $i . '_thumb' . $ext;
                    }

                    if ($src == 'no') {
                        $return[] = '<img src="' . $srcl . '" height="' . $height . '" width="' . $width . '" />';
                    } elseif ($src == 'src') {
                        $return[] = $srcl;
                    }
                    $p++;
                } else {
                    $q++;
                    if ($q == 10) {
                        break;
                    }
                }

            }
            if (!empty($return)) {
                if ($multi_num == 'one') {
                    return $return[0];
                } else if ($multi_num == 'all') {
                    return $return;
                } else {
                    $n = $multi_num - 1;
                    unset($return[$n]);
                    return $return;
                }
            } else {
                if ($multi_num == 'one') {
                    return base_url() . 'uploads/'. $type.'_image/male_default.jpg';
                } else if ($multi_num == 'all') {
                    return array(base_url() . 'uploads/'. $type.'_image/male_default.jpg');
                } else {
                    return array(base_url() . 'uploads/'. $type.'_image/male_default.jpg');
                }
            }
        }
    }


    // FILE_VIEW
    function file_dlt($type, $id, $ext = '.jpg', $multi = '', $m_sin = '')
    {
        if ($multi == '') {
            if (file_exists('uploads/' . $type . '_image/' . $type . '_' . $id . $ext)) {
                unlink("uploads/" . $type . "_image/" . $type . "_" . $id . $ext);
            }
            if (file_exists("uploads/" . $type . "_image/" . $type . "_" . $id . "_thumb" . $ext)) {
                unlink("uploads/" . $type . "_image/" . $type . "_" . $id . "_thumb" . $ext);
            }

        } else if ($multi == 'multi') {
            $num = $this->Crud_model->get_type_name_by_id($type, $id, 'num_of_imgs');
            if ($m_sin == '') {
                $i = 0;
                $p = 0;
                while ($p < $num) {
                    $i++;
                    if (file_exists('uploads/' . $type . '_image/' . $type . '_' . $id . '_' . $i . $ext)) {
                        unlink("uploads/" . $type . "_image/" . $type . "_" . $id . '_' . $i . $ext);
                        $p++;
                        $data['num_of_imgs'] = $num - 1;
                        $this->db->where($type . '_id', $id);
                        $this->db->update($type, $data);
                    }

                    if (file_exists("uploads/" . $type . "_image/" . $type . "_" . $id . '_' . $i . "_thumb" . $ext)) {
                        unlink("uploads/" . $type . "_image/" . $type . "_" . $id . '_' . $i . "_thumb" . $ext);
                    }
                    if ($i > 50) {
                        break;
                    }
                }
            } else {
                if (file_exists('uploads/' . $type . '_image/' . $type . '_' . $id . '_' . $m_sin . $ext)) {
                    unlink("uploads/" . $type . "_image/" . $type . "_" . $id . '_' . $m_sin . $ext);
                }
                if (file_exists("uploads/" . $type . "_image/" . $type . "_" . $id . '_' . $m_sin . "_thumb" . $ext)) {
                    unlink("uploads/" . $type . "_image/" . $type . "_" . $id . '_' . $m_sin . "_thumb" . $ext);
                }
                $data['num_of_imgs'] = $num - 1;
                $this->db->where($type . '_id', $id);
                $this->db->update($type, $data);
            }
        }
    }

    //DELETE MULTIPLE ITEMS
    function multi_delete($type, $ids_array)
    {
        foreach ($ids_array as $row) {
            $this->file_dlt($type, $row);
            $this->db->where($type . '_id', $row);
            $this->db->delete($type);
        }
    }

    //DELETE SINGLE ITEM
    function single_delete($type, $id)
    {
        $this->file_dlt($type, $id);
        $this->db->where($type . '_id', $id);
        $this->db->delete($type);
    }

    //GET PRODUCT LINK
    function product_link($product_id,$quick='')
    {
        if($quick=='quick'){
            return base_url() . 'index.php/home/quick_view/' . $product_id;
        } else {
            $name = url_title($this->Crud_model->get_type_name_by_id('product', $product_id, 'title'));
            return base_url() . 'index.php/home/product_view/' . $product_id . '/' . $name;
        }
    }

    //GET PRODUCT LINK
    function blog_link($blog_id)
    {
        $name = url_title($this->Crud_model->get_type_name_by_id('blog', $blog_id, 'title'));
        return base_url() . 'index.php/home/blog_view/' . $blog_id . '/' . $name;
    }

    //GET PRODUCT LINK
    function vendor_link($vendor_id)
    {
        $name = url_title($this->Crud_model->get_type_name_by_id('vendor', $vendor_id, 'display_name'));
        return base_url() . 'index.php/home/vendor_profile/' . $vendor_id . '/' . $name;
    }

    /////////GET CHOICE TITLE////////
    function choice_title_by_name($product,$name)
    {
        $return = '';
        $options = json_encode($this->get_type_name_by_id('product',$product_id,'options'),true);
        foreach ($options as $row) {
            if($row['name'] == $name){
                $return = $row['title'];
            }
        }
        return $return;
    }

    /////////SELECT HTML/////////////
    function select_html($from, $name, $field, $type, $class, $e_match = '', $condition = '', $c_match = '', $onchange = '',$condition_type='single')
    {
        $return = '';
        $other  = '';
        $multi  = 'no';
        $phrase = 'Choose a ' . $name;
        if ($class == 'demo-cs-multiselect') {
            $other = 'multiple';
            $name  = $name . '[]';
            if ($type == 'edit') {
                $e_match = json_decode($e_match);
                if ($e_match == NULL) {
                    $e_match = array();
                }
                $multi = 'yes';
            }
        }
        $return = '<select name="' . $name . '" onChange="' . $onchange . '(this.value,this)" class="' . $class . '" ' . $other . '  data-placeholder="' . $phrase . '" tabindex="2" data-hide-disabled="true" >';
        if (!is_array($from)) {
            if ($condition == '') {
                $all = $this->db->get($from)->result_array();
            } else if ($condition !== '') {
                if($condition_type=='single'){
                    $all = $this->db->get_where($from, array(
                        $condition => $c_match
                    ))->result_array();
                }else if($condition_type=='multi'){
                    $this->db->where_in($condition,$c_match);
                    $all = $this->db->get($from)->result_array();
                }
            }

            $return .= '<option value="">Choose one</option>';

            foreach ($all as $row):
                if ($type == 'add') {
                    $return .= '<option value="' . $row[$from . '_id'] . '">' . $row[$field] . '</option>';
                } else if ($type == 'edit') {
                  
                    $return .= '<option value="' . $row[$from . '_id'] . '" ';
                    
                    if ($multi == 'no') {
                        
                        if ($row[$from . '_id'] == $e_match) {
                            $return .= 'selected=."selected"';
                        }
                        
                        // elseif ($row[$from . '_name'] == $e_match) {
                        //     $return .= 'selected=."selected"';
                        // }
                    }

                    else if ($multi == 'yes') {
                        if (in_array($row[$from . '_id'], $e_match)) {
                            $return .= 'selected=."selected"';
                        }
                    }
                    $return .= '>' . $row[$field] . '</option>';
                }
            endforeach;
        } else {
            $all = $from;
            $return .= '<option value="">Choose one</option>';
            foreach ($all as $row):
                if ($type == 'add') {
                    $return .= '<option value="' . $row . '">';
                    if ($condition == '') {
                        $return .= ucfirst(str_replace('_', ' ', $row));
                    } else {
                        $return .= $this->Crud_model->get_type_name_by_id($condition, $row, $c_match);
                    }
                    $return .= '</option>';
                } else if ($type == 'edit') {
                    $return .= '<option value="' . $row . '" ';
                    if ($row == $e_match) {
                        $return .= 'selected=."selected"';
                    }
                    $return .= '>';

                    if ($condition == '') {
                        $return .= ucfirst(str_replace('_', ' ', $row));
                    } else {
                        $return .= $this->Crud_model->get_type_name_by_id($condition, $row, $c_match);
                    }

                    $return .= '</option>';
                }
            endforeach;
        }
        $return .= '</select>';
        return $return;
    }

    //CHECK IF PRODUCT EXISTS IN TABLE
    function exists_in_table($table, $field, $val)
    {
        $ret = '';
        $res = $this->db->get($table)->result_array();
        foreach ($res as $row) {
            if ($row[$field] == $val) {
                $ret = $row[$table . '_id'];
            }
        }
        if ($ret == '') {
            return false;
        } else {
            return $ret;
        }

    }

    //FORM FIELDS
    function form_fields($array)
    {
        $return = '';
        foreach ($array as $row) {
            $return .= '<div class="form-group">';
            $return .= '    <label class="col-sm-4 control-label" for="demo-hor-inputpass">' . $row . '</label>';
            $return .= '    <div class="col-sm-6">';
            $return .= '       <input type="text" name="ad_field_values[]" id="demo-hor-inputpass" class="form-control">';
            $return .= '       <input type="hidden" name="ad_field_names[]" value="' . $row . '" >';
            $return .= '    </div>';
            $return .= '</div>';
        }
        return $return;
    }

    // PAGINATION
    function pagination($type, $per, $link, $f_o, $f_c, $other, $current, $seg = '3', $ord = 'desc')
    {
        $t   = explode('#', $other);
        $t_o = $t[0];
        $t_c = $t[1];
        $c   = explode('#', $current);
        $c_o = $c[0];
        $c_c = $c[1];

        $this->load->library('pagination');
        $this->db->order_by($type . '_id', $ord);
        $config['total_rows']  = $this->db->count_all_results($type);
        $config['base_url']    = base_url() . $link;
        $config['per_page']    = $per;
        $config['uri_segment'] = $seg;

        $config['first_link']      = '&laquo;';
        $config['first_tag_open']  = $t_o;
        $config['first_tag_close'] = $t_c;

        $config['last_link']      = '&raquo;';
        $config['last_tag_open']  = $t_o;
        $config['last_tag_close'] = $t_c;

        $config['prev_link']      = '&lsaquo;';
        $config['prev_tag_open']  = $t_o;
        $config['prev_tag_close'] = $t_c;

        $config['next_link']      = '&rsaquo;';
        $config['next_tag_open']  = $t_o;
        $config['next_tag_close'] = $t_c;

        $config['full_tag_open']  = $f_o;
        $config['full_tag_close'] = $f_c;

        $config['cur_tag_open']  = $c_o;
        $config['cur_tag_close'] = $c_c;

        $config['num_tag_open']  = $t_o;
        $config['num_tag_close'] = $t_c;
        $this->pagination->initialize($config);

        $this->db->order_by($type . '_id', $ord);
        return $this->db->get($type, $config['per_page'], $this->uri->segment($seg))->result_array();
    }

    //TOTALING OF CART ITEMS BY TYPE
    function cart_total_it($type)
    {
        $carted = $this->cart->contents();
        $ret    = 0;
        if (count($carted) > 0) {
            foreach ($carted as $items) {
                $ret += $items[$type] * $items['qty'];
            }
            return $ret;
        } else {
            return false;
        }
    }

    //GETTING ADDITIONAL FIELDS FOR PRODUCT ADD
    function get_additional_fields($product_id)
    {
        $additional_fields = $this->Crud_model->get_type_name_by_id('product', $product_id, 'additional_fields');
        $ab                = json_decode($additional_fields,true);
        $name = json_decode($ab['name']);
        $value = json_decode($ab['value']);
        $final = array();
        if(!empty($name)){
            foreach ($name as $n => $row) {
                $final[] = array(
                    'name' => $row,
                    'value' => $value[$n]
                );
            }
        }
        return $final;
    }

    //GETTING IDS OF A TABLE FILTERING SPECIFIC TYPE OF VALUE RANGE
    function ids_between_values($table, $value_type, $up_val, $down_val)
    {
        $this->db->order_by($table . '_id', 'desc');
        return $this->db->get_where($table, array(
            $value_type . ' <=' => $up_val,
            $value_type . ' >=' => $down_val
        ))->result_array();
    }

    //DAYS START-END TIMESTAMP
    function date_timestamp($date, $type)
    {
        $date = explode('-', $date);
        $d    = $date[2];
        $m    = $date[1];
        $y    = $date[0];
        if ($type == 'start') {
            return mktime(0, 0, 0, $m, $d, $y);
        }
        if ($type == 'end') {
            return mktime(0, 0, 0, $m, $d + 1, $y);
        }
    }

    //GETTING BOOTSTRAP COLUMN VALUE
    function boot($num)
    {
        return (12 / $num);
    }

    //GETTING LIMITING CHARECTER
    function limit_chars($string, $char_limit)
    {
        $length = 0;
        $return = array();
        $words  = explode(" ", $string);
        foreach ($words as $row) {
            $length += strlen($row);
            $length += 1;
            if ($length < $char_limit) {
                array_push($return, $row);
            } else {
                array_push($return, '...');
                break;
            }
        }

        return implode(" ", $return);
    }

    //GETTING LOGO BY TYPE
    function logo($type)
    {
        $logo = $this->db->get_where('ui_settings', array(
            'type' => $type
        ))->row()->value;
        return base_url() . 'uploads/logo_image/logo_' . $logo . '.png';
    }


    //GETTING MONTH'S TOTAL BY TYPE
    function month_total($type, $filter1 = '', $filter_val1 = '', $filter2 = '', $filter_val2 = '', $notmatch = '', $notmatch_val = '')
    {
        $ago = time() - (86400 * 30);
        $a   = 0;
        if ($type == 'sale') {
            $result = $this->db->get_where('sale', array(
                'sale_datetime >= ' => $ago,
                'sale_datetime <= ' => time()
            ))->result_array();
            foreach ($result as $row) {
                if($this->session->userdata('title') == 'admin'){
                    if($this->sale_payment_status($row['sale_id'],'admin') == 'fully_paid'){
                        //make version for vendor
                        $res_cat = $this->db->get_where('product', array(
                            'category' => $filter_val1
                        ))->result_array();
                        foreach ($res_cat as $row1) {
                            if ($p = $this->product_in_sale($row['sale_id'], $row1['product_id'], 'subtotal')) {
                                $a += $p;
                            }
                        }
                    }
                }
                if($this->session->userdata('title') == 'vendor'){
                    if($this->sale_payment_status($row['sale_id'],'vendor',$this->session->userdata('vendor_id')) == 'fully_paid'){
                        //make version for vendor
                        $res_cat = $this->db->get_where('product', array(
                            'category' => $filter_val1
                        ))->result_array();
                        foreach ($res_cat as $row1) {
                            if ($p = $this->vendor_share_in_sale($row['sale_id'],$this->session->userdata('vendor_id'),'paid')) {
                                $p = $p['total'];
                                $a += $p;
                            }
                        }
                    }
                }
            }
        } else if ($type == 'stock') {
            if($this->session->userdata('title') == 'admin'){
                $this->db->get_where('added_by',json_encode(array('type'=>'vendor','id'=>$this->session->userdata('vendor_id'))));
                $this->db->get_where('datetime >= ',$ago);
                $this->db->get_where('datetime <= ',time());
                $result = $this->db->get('stock')->result_array();
                foreach ($result as $row) {
                    if ($row[$filter2] == $filter_val2) {
                        if ($row[$filter1] == $filter_val1) {
                            if ($notmatch == '') {
                                $a += $row['total'];
                            } else {
                                if ($row[$notmatch] !== $notmatch_val) {
                                    $a += $row['total'];
                                }
                            }
                        }
                    }
                }
            }
            if($this->session->userdata('title') == 'vendor'){
                $result = $this->db->get_where('stock', array(
                    'datetime >= ' => $ago,
                    'datetime <= ' => time()
                ))->result_array();
                foreach ($result as $row) {
                    if ($row[$filter2] == $filter_val2) {
                        if ($row[$filter1] == $filter_val1) {
                            if ($notmatch == '') {
                                $a += $row['total'];
                            } else {
                                if ($row[$notmatch] !== $notmatch_val) {
                                    $a += $row['total'];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $a;
    }

    function email_invoice($sale_id){
        $email = $this->get_type_name_by_id('user', $this->get_type_name_by_id('sale', $sale_id, 'buyer'), 'email');
        $sale_code = '#'.$this->get_type_name_by_id('sale', $sale_id, 'sale_code');
        $from = $this->db->get_where('general_settings', array(
            'type' => 'system_email'
        ))->row()->value;
        $from_name = $this->db->get_where('general_settings', array(
            'type' => 'system_name'
        ))->row()->value;
        $page_data['sale_id'] = $sale_id;
        $text = $this->load->view('front/shopping_cart/invoice_email', $page_data, TRUE);
        $this->email_model->do_email($from, $from_name, $email, $sale_code, $text);
        $admins = $this->db->get_where('admin',array('role'=>'1'))->result_array();
        foreach ($admins as $row) {
            $this->email_model->do_email($from, $from_name, $row['email'], $sale_code, $text);
        }
    }

    //GETTING VENDOR PERMISSION
    function vendor_permission($codename)
    {
        if ($this->session->userdata('vendor_login') !== 'yes') {
            return false;
        } else {
            return true;
        }
    }

    function is_added_by($type,$id,$user_id,$user_type = 'vendor')
    {
        $added_by = json_decode($this->db->get_where($type,array($type.'_id'=>$id))->row()->added_by,true);
        if($user_type == 'admin'){
            $user_id = $added_by['id'];
        }
        $this->benchmark->mark_time();
        if($added_by['type'] == $user_type && $added_by['id'] == $user_id){
            return true;
        } else {
            return false;
        }
    }

    //SALE WISE TOTAL BY TYPE
    function provider_detail($type,$id='',$with_link='')
    {
        if($type == 'admin'){
            $name = $this->db->get_where('general_settings',array('type'=>'system_name'))->row()->value;
            if($with_link == ''){
                return $name;
            } else if($with_link == 'with_link') {
                return '<a href="'.base_url().'">'.$name.'</a>';
            }
        } else if($type == 'vendor'){
            $name = $this->db->get_where('vendor',array('vendor_id'=>$id))->row()->display_name;
            if($with_link == ''){
                return $name;
            } else if($with_link == 'with_link') {
                return '<a href="'.$this->vendor_link($added_by['id']).'">'.$name.'</a>';
            }
        }
    }

    function sale_payment_status($sale_id,$type='',$id=''){
        $payment_status = json_decode($this->db->get_where('sale', array(
            'sale_id' => $sale_id
        ))->row()->payment_status,true);
        $paid = '';
        $unpaid = '';
        foreach ($payment_status as $row) {
            if($type == ''){
                if($row['status'] == 'paid'){
                    $paid = 'yes';
                }
                if($row['status'] == 'due'){
                    $unpaid = 'yes';
                }
            } else {
                if(isset($row[$type])){
                    if($type == 'vendor'){
                        if($row[$type] == $id){
                            if($row['status'] == 'paid'){
                                $paid = 'yes';
                            }
                            if($row['status'] == 'due'){
                                $unpaid = 'yes';
                            }
                        }
                    } else if($type == 'admin'){
                        if($row['status'] == 'paid'){
                            $paid = 'yes';
                        }
                        if($row['status'] == 'due'){
                            $unpaid = 'yes';
                        }
                    }
                }
            }
        }
        if($paid == 'yes' && $unpaid == ''){
            return 'fully_paid';
        }
        else if($paid == 'yes' && $unpaid == 'yes'){
            return 'partially_paid';
        }
        else if($paid == '' && $unpaid == 'yes'){
            return 'due';
        }
        if($paid == '' && $unpaid == ''){
            return 'due';
        }
    }


    //GETTING ADMIN PERMISSION
    function admin_permission($codename)
    {
       $admin_id   = $this->session->userdata('admin_id');
        $admin        = $this->db->get_where('admin', array(
            'admin_id' => $admin_id
        ))->row();
        $this->benchmark->mark_time();
        $permission = $this->db->get_where('permission', array(
            'codename' => $codename
        ))->row()->permission_id;
        if ($admin->role == 1) {
            return true;
        } else {
            $role  = $admin->role;
            $role_permissions = json_decode($this->Crud_model->get_type_name_by_id('role', $role, 'permission'));
            if (in_array($permission, $role_permissions)) {
                return true;
            } else {
                return false;
            }
        }/**/
    }


    //GETTING USER TOTAL
    function user_total($last_days = 0)
    {
        if ($last_days == 0) {
            $time = 0;
        } else {
            $time = time() - (24 * 60 * 60 * $last_days);
        }
        $sales  = $this->db->get_where('sale', array(
            'buyer' => $this->session->userdata('user_id'),
            'payment_status' => 'paid',
            'sale_datetime >=' => $time
        ))->result_array();
        $return = 0;
        foreach ($sales as $row) {
            $return += $row['grand_total'];
        }
        return number_format((float) $return, 2, '.', '');
    }


    //GETTING IP DATA OF PEOPLE BROWSING THE SYSTEM
    function ip_data()
    {
        if(!$this->input->is_ajax_request()){
            $this->session->set_userdata('timestamp', time());
            $user_data = $this->session->userdata('surfer_info');
            $ip        = $_SERVER['REMOTE_ADDR'];
            if (!$user_data) {
                if ($_SERVER['HTTP_HOST'] !== 'localhost') {
                    $ip_data = file_get_contents("http://ip-api.com/json/" . $ip);
                    $this->session->set_userdata('surfer_info', $ip_data);
                }
            }
        }
    }


    function seo_stat($type='') {
        try {
            $url = base_url();
            $seostats = new \SEOstats\SEOstats;
            if ($seostats->setUrl($url)) {

                if($type == 'facebook'){
                    return SEOstats\Services\Social::getFacebookShares();
                }
                elseif ($type == 'gplus') {
                    return SEOstats\Services\Social::getGooglePlusShares();
                }
                elseif ($type == 'twitter') {
                    return SEOstats\Services\Social::getTwitterShares();
                }
                elseif ($type == 'linkedin') {
                    return SEOstats\Services\Social::getLinkedInShares();
                }
                elseif ($type == 'pinterest') {
                    return SEOstats\Services\Social::getPinterestShares();
                }

                elseif ($type == 'alexa_global') {
                    return SEOstats\Services\Alexa::getGlobalRank();
                }
                elseif ($type == 'alexa_country') {
                    return SEOstats\Services\Alexa::getCountryRank();
                }

                elseif ($type == 'alexa_bounce') {
                    return SEOstats\Services\Alexa::getTrafficGraph(5);
                }
                elseif ($type == 'alexa_time') {
                    return SEOstats\Services\Alexa::getTrafficGraph(4);
                }
                elseif ($type == 'alexa_traffic') {
                    return SEOstats\Services\Alexa::getTrafficGraph(1);
                }
                elseif ($type == 'alexa_pageviews') {
                    return SEOstats\Services\Alexa::getTrafficGraph(2);
                }

                elseif ($type == 'google_siteindex') {
                    return SEOstats\Services\Google::getSiteindexTotal();
                }
                elseif ($type == 'google_back') {
                    return SEOstats\Services\Google::getBacklinksTotal();
                }
                elseif ($type == 'search_graph_1') {
                    return SEOstats\Services\SemRush::getDomainGraph(1);
                }
                elseif ($type == 'search_graph_2') {
                    return SEOstats\Services\SemRush::getDomainGraph(2);
                }

            }
        }
        catch(\Exception $e) {
            echo 'Caught SEOstatsException: ' . $e->getMessage();
        }
    }


    function ticket_unread_messages($ticket_id,$user_type){
        $count = 0;
        if($ticket_id !== 'all'){
            $msgs  = $this->db->get_where('ticket_message',array('ticket_id'=>$ticket_id))->result_array();
        } else if($ticket_id == 'all'){
            $msgs  = $this->db->get('ticket_message')->result_array();
        }
        foreach($msgs as $row){
            $status = json_decode($row['view_status'],true);
            foreach($status as $type => $row1){
                if($type == $user_type.'_show'){
                    if($row1 == 'no'){
                        $count++;
                    }
                }
            }
        }
        return $count;

    }

    function ticket_message_viewed($ticket_id,$user_type){

        $msgs  = $this->db->get_where('ticket_message',array('ticket_id'=>$ticket_id))->result_array();
        foreach($msgs as $row){
            $status = json_decode($row['view_status'],true);
            $new_status = array();
            foreach($status as $type=>$row1){
                if($type == $user_type.'_show'){
                    $new_status[$type] =  'ok';
                } else{
                    $new_status[$type] =  $row1;
                }
            }
            $view_status = json_encode($new_status);
            $this->db->where('ticket_message_id', $row['ticket_message_id']);
            $this->db->update('ticket_message', array(
                'view_status' => $view_status
            ));

        }

    }

    function check_login($table, $username, $password)
    {
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where('email', $username);
        $this->db->where('password', $password);
        $query_result = $this->db->get();
        $result = $query_result->row();
        // log_message('info', 'Login result: ' . json_encode($result));

        return $result;
    }

    function alldata_count($table)
    {
        if(!empty($this->session->userdata('earning_status'))){
            $state = $this->session->userdata('earning_status');
            if($state == 'paid' || $state == 'due'){
                $this->db->where($table.'.payment_status', $state);
            }
        }
        $query = $this->db->get($table);
        return $query->num_rows();
    }

    function alldatas($table,$limit,$start,$col,$dir)
    {
        $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get($table);
        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function data_search($table,$limit,$start,$search,$col,$dir)
    {
        $query = $this->db->like($table.'_id',$search)->or_like('name',$search)->limit($limit,$start)->order_by($col,$dir)->get($table);


        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function data_search_count($table,$search)
    {
        $query = $this->db->like($table.'_id',$search)->or_like('name',$search)->get($table);

        return $query->num_rows();
    }

    // function allmembers_count($membership)
    // {
    //     $query = $this->db->get_where("member", array("membership" => $membership))->result();
    //     return count($query);
    // }
    
    function allmembers_count($membership)
    {
        if(!empty($this->session->userdata('free_member_status_type')) && $membership == 1){
            if($this->session->userdata('free_member_status_type') == 'groom'){
                $this->db->where('gender',1);
            }else if($this->session->userdata('free_member_status_type') == 'bride'){
                $this->db->where('gender',2);
            }

            if(!empty($this->session->userdata('free_filter_status')) && $membership == 1){
                if($this->session->userdata('free_filter_status') == 'approved'){
                    $this->db->where('status', 'approved');
                }else if($this->session->userdata('free_filter_status') == 'pending'){
                    $this->db->where('status', 'pending');
                }            
            }

            if(!empty($this->session->userdata('free_member_profile_image')) && $membership == 1){
                if($this->session->userdata('free_member_profile_image') == 'default'){
                    $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
                    $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
                }           
            }
           
        }
        else if(!empty($this->session->userdata('premium_member_status_type')) && $membership == 2){
            if($this->session->userdata('premium_member_status_type') == 'groom'){
                $this->db->where('gender',1);
            }else if($this->session->userdata('premium_member_status_type') == 'bride'){
                $this->db->where('gender',2);
            }
        }

        if(!empty($this->session->userdata('premium_filter_status')) && $membership == 2){
            if($this->session->userdata('premium_filter_status') == 'approved'){
                $this->db->where('status', 'approved');
            }else if($this->session->userdata('premium_filter_status') == 'pending'){
                $this->db->where('status', 'pending');
            }            
        }

        if(!empty($this->session->userdata('premium_member_profile_image')) && $membership == 2){
                if($this->session->userdata('premium_member_profile_image') == 'default'){
                    $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
                    $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
                }           
            }
        
        $query = $this->db->get_where("member", array("membership" => $membership))->result();
        return count($query);
    }

    
    

    // function allmembers($membership,$limit,$start,$col,$dir)
    // {
    //     $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get_where("member", array("membership" => $membership));
    //     if($query->num_rows()>0)
    //     {
    //         return $query->result();
    //     }
    //     else
    //     {
    //         return null;
    //     }
    // }

    public function get_members_by_admin_scope($member_type, $limit, $start, $order, $dir, $admin_id)
    {


        if ($admin_id != 1) {
            $this->db->select('area_id');
            $area_query = $this->db->get_where('admin_area', ['admin_id' => $admin_id]);
        
            if ($area_query->num_rows() > 0) {
                // Admin has assigned areas
                $area_ids = array_column($area_query->result_array(), 'area_id');
                $this->db->where_in('area_id', $area_ids);
            } else {
                // Step 2: Check admin_legions
                $this->db->select('legion_id');
                $legion_query = $this->db->get_where('admin_legion', ['admin_id' => $admin_id]);
        
                if ($legion_query->num_rows() > 0) {
                    $legion_ids = array_column($legion_query->result_array(), 'legion_id');
                    $this->db->where_in('legion_id', $legion_ids);
                } else {
                    // No area or legion found for this admin, return empty result
                    log_message('info', "No areas or legions found for admin_id: {$admin_id}. Returning empty result.");
                    return [];
                }
            }
        
        }
        // // Step 1: Check admin_areas
        // $this->db->select('area_id');
        // $area_query = $this->db->get_where('admin_area', ['admin_id' => $admin_id]);
    
        // if ($area_query->num_rows() > 0) {
        //     // Admin has assigned areas
        //     $area_ids = array_column($area_query->result_array(), 'area_id');
        //     $this->db->where_in('area_id', $area_ids);
        // } else {
        //     // Step 2: Check admin_legions
        //     $this->db->select('legion_id');
        //     $legion_query = $this->db->get_where('admin_legions', ['admin_id' => $admin_id]);
    
        //     if ($legion_query->num_rows() > 0) {
        //         $legion_ids = array_column($legion_query->result_array(), 'legion_id');
        //         $this->db->where_in('legion_id', $legion_ids);
        //     } else {
        //         // No area or legion found for this admin, return empty result
        //         log_message('info', "No areas or legions found for admin_id: {$admin_id}. Returning empty result.");
        //         return [];
        //     }
        // }
    
        // Step 3: Membership condition
        $this->db->where('membership', $member_type);
    
        // ===== Apply session-based filters here =====
    
        if (!empty($this->session->userdata('free_member_status_type')) && $member_type == 1) {
            if ($this->session->userdata('free_member_status_type') == 'groom') {
                $this->db->where('gender', 1);
            } else if ($this->session->userdata('free_member_status_type') == 'bride') {
                $this->db->where('gender', 2);
            }
    
            if (!empty($this->session->userdata('free_filter_status'))) {
                if ($this->session->userdata('free_filter_status') == 'approved') {
                    $this->db->where('status', 'approved');
                } else if ($this->session->userdata('free_filter_status') == 'pending') {
                    $this->db->where('status', 'pending');
                }
            }
    
            if (!empty($this->session->userdata('free_member_profile_image'))) {
                if ($this->session->userdata('free_member_profile_image') == 'default') {
                    $this->db->not_like('profile_image', 'male_default.jpg');
                    $this->db->not_like('profile_image', 'female_default.png');
                }
            }
    
        } else if (!empty($this->session->userdata('premium_member_status_type')) && $member_type == 2) {
            if ($this->session->userdata('premium_member_status_type') == 'groom') {
                $this->db->where('gender', 1);
            } else if ($this->session->userdata('premium_member_status_type') == 'bride') {
                $this->db->where('gender', 2);
            }
    
            if (!empty($this->session->userdata('premium_filter_status'))) {
                if ($this->session->userdata('premium_filter_status') == 'approved') {
                    $this->db->where('status', 'approved');
                } else if ($this->session->userdata('premium_filter_status') == 'pending') {
                    $this->db->where('status', 'pending');
                }
            }
    
            if (!empty($this->session->userdata('premium_member_profile_image'))) {
                if ($this->session->userdata('premium_member_profile_image') == 'default') {
                    $this->db->not_like('profile_image', 'male_default.jpg');
                    $this->db->not_like('profile_image', 'female_default.png');
                }
            }
        }
    
        // Step 4: Apply limit, offset, ordering
        $this->db->limit($limit, $start);
        $this->db->order_by($order, $dir);
    
        // Step 5: Execute query
        $query = $this->db->get('member');
    
        $result = $query->result();
    
        // Step 6: Prepare log data (first 5 members, only selected fields)
        $log_data = array_map(function ($member) {
            return [
                'member_id'   => $member->member_id,
                'first_name'  => $member->first_name,
                'last_name'   => $member->last_name,
                'email'       => $member->email,
                'gender'      => $member->gender,
                'status'      => $member->status,
                'membership'  => $member->membership,
            ];
        }, array_slice($result, 0, 5));
        // log_message('info', 'Fetched members for admin_id ' . $admin_id . ': ' . json_encode($log_data));

        log_message('info', 'Fetched members for admin_id ' . $admin_id . ': ' . json_encode($log_data));
    
        return $query;
    }
    
    


    function allmembers($membership, $limit, $start, $col, $dir)
    {   
        $admin_id = $this->session->userdata('admin_id');
        log_message('info', 'Admin ID from session: ' . $admin_id);
    
        // $members =
    
        
        // if(!empty($this->session->userdata('free_member_status_type')) && $membership == 1){
        //     if($this->session->userdata('free_member_status_type') == 'groom'){
        //         $this->db->where('gender',1);
        //     }else if($this->session->userdata('free_member_status_type') == 'bride'){
        //         $this->db->where('gender',2);
        //     }

        //     if(!empty($this->session->userdata('free_filter_status')) && $membership == 1){
        //         if($this->session->userdata('free_filter_status') == 'approved'){
        //             $this->db->where('status', 'approved');
        //         }else if($this->session->userdata('free_filter_status') == 'pending'){
        //             $this->db->where('status', 'pending');
        //         }            
        //     }

        //     if(!empty($this->session->userdata('free_member_profile_image')) && $membership == 1){
        //         if($this->session->userdata('free_member_profile_image') == 'default'){
        //             $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
        //             $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
        //         }           
        //     }

        // }
        // else if(!empty($this->session->userdata('premium_member_status_type')) && $membership == 2){
        //     if($this->session->userdata('premium_member_status_type') == 'groom'){
        //         $this->db->where('gender',1);
        //     }else if($this->session->userdata('premium_member_status_type') == 'bride'){
        //         $this->db->where('gender',2);
        //     }

        //     if(!empty($this->session->userdata('premium_filter_status')) && $membership == 2){
        //         if($this->session->userdata('premium_filter_status') == 'approved'){
        //             $this->db->where('status', 'approved');
        //         }else if($this->session->userdata('premium_filter_status') == 'pending'){
        //             $this->db->where('status', 'pending');
        //         }            
        //     }

        //     if(!empty($this->session->userdata('premium_member_profile_image')) && $membership == 2){
        //         if($this->session->userdata('premium_member_profile_image') == 'default'){
        //             $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
        //             $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
        //         }           
        //     }
        // }
        
        // $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get_where("member", array("membership" => $membership));
        $query = $this->get_members_by_admin_scope($membership, $limit, $start, $col, $dir, $admin_id);
        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }
    
    // function allmembers($membership, $limit, $start, $col, $dir)
    // {   
    //     $admin_id = $this->session->userdata('admin_id');
    //     log_message('info', 'Admin ID from session: ' . $admin_id);
    
    //     $members = $this->get_members_by_admin_scope($membership, $limit, $start, $col, $dir, $admin_id);
    
        
    //     if(!empty($this->session->userdata('free_member_status_type')) && $membership == 1){
    //         if($this->session->userdata('free_member_status_type') == 'groom'){
    //             $this->db->where('gender',1);
    //         }else if($this->session->userdata('free_member_status_type') == 'bride'){
    //             $this->db->where('gender',2);
    //         }

    //         if(!empty($this->session->userdata('free_filter_status')) && $membership == 1){
    //             if($this->session->userdata('free_filter_status') == 'approved'){
    //                 $this->db->where('status', 'approved');
    //             }else if($this->session->userdata('free_filter_status') == 'pending'){
    //                 $this->db->where('status', 'pending');
    //             }            
    //         }

    //         if(!empty($this->session->userdata('free_member_profile_image')) && $membership == 1){
    //             if($this->session->userdata('free_member_profile_image') == 'default'){
    //                 $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
    //                 $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
    //             }           
    //         }

    //     }
    //     else if(!empty($this->session->userdata('premium_member_status_type')) && $membership == 2){
    //         if($this->session->userdata('premium_member_status_type') == 'groom'){
    //             $this->db->where('gender',1);
    //         }else if($this->session->userdata('premium_member_status_type') == 'bride'){
    //             $this->db->where('gender',2);
    //         }

    //         if(!empty($this->session->userdata('premium_filter_status')) && $membership == 2){
    //             if($this->session->userdata('premium_filter_status') == 'approved'){
    //                 $this->db->where('status', 'approved');
    //             }else if($this->session->userdata('premium_filter_status') == 'pending'){
    //                 $this->db->where('status', 'pending');
    //             }            
    //         }

    //         if(!empty($this->session->userdata('premium_member_profile_image')) && $membership == 2){
    //             if($this->session->userdata('premium_member_profile_image') == 'default'){
    //                 $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
    //                 $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
    //             }           
    //         }
    //     }
        
    //     $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get_where("member", array("membership" => $membership));
    //     if($query->num_rows()>0)
    //     {
    //         return $query->result();
    //     }
    //     else
    //     {
    //         return null;
    //     }
    // }
    
 function members_search($membership,$limit,$start,$search,$col,$dir)
    {
        $this->db->limit($limit,$start);
        $this->db->order_by($col,$dir);
        $this->db->where('membership',$membership);
        
        $this->db->group_start();
        $this->db->like('percentage', $search,'after');
        $this->db->or_like('member_id', $search);
        $this->db->or_like('first_name', $search);
        $this->db->or_like('last_name', $search);
        $this->db->or_like('member_profile_id', $search);
        $this->db->group_end();
        
        // $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        
        if(!empty($this->session->userdata('free_member_status_type')) && $membership == 1){
            if($this->session->userdata('free_member_status_type') == 'groom'){
                $this->db->where('gender',1);
            }else if($this->session->userdata('free_member_status_type') == 'bride'){
                $this->db->where('gender',2);
            }

            if(!empty($this->session->userdata('free_filter_status')) && $membership == 1){
                if($this->session->userdata('free_filter_status') == 'approved'){
                    $this->db->where('status', 'approved');
                }else if($this->session->userdata('free_filter_status') == 'pending'){
                    $this->db->where('status', 'pending');
                }            
            }

            if(!empty($this->session->userdata('free_member_profile_image')) && $membership == 1){
                if($this->session->userdata('free_member_profile_image') == 'default'){
                    $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
                    $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
                }           
            }
        }
        else if(!empty($this->session->userdata('premium_member_status_type')) && $membership == 2){
            if($this->session->userdata('premium_member_status_type') == 'groom'){
                $this->db->where('gender',1);
            }else if($this->session->userdata('premium_member_status_type') == 'bride'){
                $this->db->where('gender',2);
            }

            if(!empty($this->session->userdata('premium_filter_status')) && $membership == 2){
                if($this->session->userdata('premium_filter_status') == 'approved'){
                    $this->db->where('status', 'approved');
                }else if($this->session->userdata('premium_filter_status') == 'pending'){
                    $this->db->where('status', 'pending');
                }            
            }

            if(!empty($this->session->userdata('premium_member_profile_image')) && $membership == 2){
                if($this->session->userdata('premium_member_profile_image') == 'default'){
                    $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
                    $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
                }           
            }
        }
        
        
        $query = $this->db->get('member');

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }


  function members_search_count($membership,$search)
    {
        if(!empty($this->session->userdata('free_member_status_type')) && $membership == 1){
            if($this->session->userdata('free_member_status_type') == 'groom'){
                $this->db->where('gender',1);
            }else if($this->session->userdata('free_member_status_type') == 'bride'){
                $this->db->where('gender',2);
            }

            if(!empty($this->session->userdata('free_filter_status')) && $membership == 1){
                if($this->session->userdata('free_filter_status') == 'approved'){
                    $this->db->where('status', 'approved');
                }else if($this->session->userdata('free_filter_status') == 'pending'){
                    $this->db->where('status', 'pending');
                }            
            }

            if(!empty($this->session->userdata('free_member_profile_image')) && $membership == 1){
                if($this->session->userdata('free_member_profile_image') == 'default'){
                    $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
                    $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
                }           
            }
        }
        else if(!empty($this->session->userdata('premium_member_status_type')) && $membership == 2){
            if($this->session->userdata('premium_member_status_type') == 'groom'){
                $this->db->where('gender',1);
            }else if($this->session->userdata('premium_member_status_type') == 'bride'){
                $this->db->where('gender',2);
            }

            if(!empty($this->session->userdata('premium_filter_status')) && $membership == 2){
                if($this->session->userdata('premium_filter_status') == 'approved'){
                    $this->db->where('status', 'approved');
                }else if($this->session->userdata('premium_filter_status') == 'pending'){
                    $this->db->where('status', 'pending');
                }            
            }

            if(!empty($this->session->userdata('premium_member_profile_image')) && $membership == 2){
                if($this->session->userdata('premium_member_profile_image') == 'default'){
                    $this->db->not_like('profile_image', '"profile_image":"male_default.jpg"');
                    $this->db->not_like('profile_image', '"profile_image":"female_default.png"');
                }           
            }
        }
        
        $this->db->where('membership',$membership);
        
        $this->db->group_start();
        $this->db->like('percentage', $search,'after');
        $this->db->or_like('member_id', $search);
        $this->db->or_like('first_name', $search);
        $this->db->or_like('last_name', $search);
        $this->db->or_like('member_profile_id', $search);
        $this->db->group_end();

        // $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        $query = $this->db->get('member');

        return $query->num_rows();
    }   

    function all_deleted_members_count($membership)
    {
        $query = $this->db->get("deleted_member")->result();
        return count($query);
    }

    function all_deleted_members($membership,$limit,$start,$col,$dir)
    {
        $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get("deleted_member");
        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function deleted_members_search($membership,$limit,$start,$search,$col,$dir)
    {
        $this->db->limit($limit,$start);
        $this->db->order_by($col,$dir);
        $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        $query = $this->db->get('deleted_member');

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function deleted_members_search_count($membership,$search)
    {
        $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        $query = $this->db->get('deleted_member');

        return $query->num_rows();
    }

    function allstates($table,$limit,$start,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.name, country.name AS country_name', FALSE);
        $this->db->from($table);
        $this->db->join('country', 'country.country_id = '.$table.'.country_id', 'left');
        $this->db->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function state_search($table,$limit,$start,$search,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.name, country.name AS country_name', FALSE);
        $this->db->from($table);
        $this->db->join('country', 'country.country_id = '.$table.'.country_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.name',$search)->or_like('country.name',$search)->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function state_search_count($table,$search)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.name, country.name AS country_name', FALSE);
        $this->db->from($table);
        $this->db->join('country', 'country.country_id = '.$table.'.country_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.name',$search)->or_like('country.name',$search);
        $query = $this->db->get();

        return $query->num_rows();
    }



    function allcastes($table,$limit,$start,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.caste_name, religion.name AS religion_name', FALSE);
        $this->db->from($table);
        $this->db->join('religion', 'religion.religion_id = '.$table.'.religion_id', 'left');
        $this->db->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function caste_search($table,$limit,$start,$search,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.caste_name, religion.name AS religion_name', FALSE);
        $this->db->from($table);
        $this->db->join('religion', 'religion.religion_id = '.$table.'.religion_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.caste_name',$search)->or_like('religion.name',$search)->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function caste_search_count($table,$search)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.caste_name, religion.name AS religion_name', FALSE);
        $this->db->from($table);
        $this->db->join('religion', 'religion.religion_id = '.$table.'.religion_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.caste_name',$search)->or_like('religion.name',$search);
        $query = $this->db->get();

        return $query->num_rows();
    }

    function allsub_castes($table,$limit,$start,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.sub_caste_name, caste.caste_name, religion.name AS religion_name', FALSE);
        $this->db->from($table);
        $this->db->join('caste', 'caste.caste_id = '.$table.'.caste_id', 'left');
        $this->db->join('religion', 'religion.religion_id = caste.religion_id', 'left');
        $this->db->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function sub_caste_search($table,$limit,$start,$search,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.sub_caste_name, caste.caste_name AS caste_name, religion.name AS religion_name', FALSE);
        $this->db->from($table);
         $this->db->join('caste', 'caste.caste_id = '.$table.'.caste_id', 'left');
        $this->db->join('religion', 'religion.religion_id = caste.religion_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.sub_caste_name',$search)->or_like('caste.caste_name',$search)->or_like('religion.name',$search)->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function sub_caste_search_count($table,$search)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.sub_caste_name, caste.caste_name AS caste_name, religion.name AS religion_name', FALSE);
        $this->db->from($table);
        $this->db->join('caste', 'caste.caste_id = '.$table.'.caste_id', 'left');
        $this->db->join('religion', 'religion.religion_id = caste.religion_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.sub_caste_name',$search)->or_like('caste.caste_name',$search)->or_like('religion.name',$search);
        $query = $this->db->get();

        return $query->num_rows();
    }

    function allcities($table,$limit,$start,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.name, state.name AS state_name, country.name AS country_name', FALSE);
        $this->db->from($table);
        $this->db->join('state', 'state.state_id = '.$table.'.state_id', 'left');
        $this->db->join('country', 'country.country_id = state.country_id', 'left');
        $this->db->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function city_search($table,$limit,$start,$search,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.name, state.name AS state_name, country.name AS country_name', FALSE);
        $this->db->from($table);
        $this->db->join('state', 'state.state_id = '.$table.'.state_id', 'left');
        $this->db->join('country', 'country.country_id = state.country_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.name',$search)->or_like('state.name',$search)->or_like('country.name',$search)->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function city_search_count($table,$search)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.name, state.name AS state_name, country.name AS country_name', FALSE);
        $this->db->from($table);
        $this->db->join('state', 'state.state_id = '.$table.'.state_id', 'left');
        $this->db->join('country', 'country.country_id = state.country_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.name',$search)->or_like('state.name',$search)->or_like('country.name',$search);
        $query = $this->db->get();

        return $query->num_rows();
    }

function allearnings($table,$limit,$start,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.payment_type,'.$table.'.payment_status,'.$table.'.payment_details,'.$table.'.amount,'.$table.'.purchase_datetime,'.$table.'.custom_payment_method_name, plan.name AS package_name, member.first_name AS member_first_name, member.last_name AS member_last_name, IFNULL(plan.start_date, 0) AS start_date, IFNULL(plan.end_date, 0) AS end_date', FALSE);
        $this->db->from($table);
        $this->db->join('plan', 'plan.plan_id = '.$table.'.plan_id', 'left');
        $this->db->join('member', 'member.member_id = '.$table.'.member_id', 'left');

        $this->db->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            $results = $query->result();

            // Iterate over results to update payment_status based on purchase_datetime and start/end dates
            foreach ($results as &$row) {
                $purchase_date = $row->purchase_datetime;
                $start_date = $row->start_date;
                $end_date = $row->end_date;

                // Convert to timestamps for comparison if not zero
                $purchase_ts = is_numeric($purchase_date) ? $purchase_date : strtotime($purchase_date);
                $start_ts = ($start_date && $start_date != 0) ? strtotime($start_date) : 0;
                $end_ts = ($end_date && $end_date != 0) ? strtotime($end_date) : 0;

                if ($purchase_ts >= $start_ts && $purchase_ts <= $end_ts) {
                    $row->payment_status = 'paid';
                } elseif ($purchase_ts > $end_ts) {
                    $row->payment_status = 'due';
                }
                // else keep original payment_status
            }

            // Filter results based on session earning_status filter
            $state = $this->session->userdata('earning_status') ?? 'all';
            if ($state == 'paid') {
                $results = array_filter($results, function($row) {
                    return $row->payment_status === 'paid';
                });
            } elseif ($state == 'due') {
                $results = array_filter($results, function($row) {
                    return $row->payment_status === 'due';
                });
            }
            // If 'all', no filtering

            return array_values($results); // Reindex array after filtering
        }
        else
        {
            return null;
        }
    }

function earning_search($table,$limit,$start,$search,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.payment_type,'.$table.'.payment_status,'.$table.'.payment_details,'.$table.'.amount,'.$table.'.purchase_datetime,'.$table.'.custom_payment_method_name, plan.name AS package_name, member.first_name AS member_first_name, member.last_name AS member_last_name, IFNULL(plan.start_date, 0) AS start_date, IFNULL(plan.end_date, 0) AS end_date', FALSE);

        $this->db->from($table);
        $this->db->join('plan', 'plan.plan_id = '.$table.'.plan_id', 'left');
        $this->db->join('member', 'member.member_id = '.$table.'.member_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.payment_type',$search)->or_like($table.'.payment_status',$search)->or_like($table.'.amount',$search)->or_like('plan.name',$search)->or_like('member.first_name',$search)->or_like('member.last_name',$search)->limit($limit,$start)->order_by($col,$dir);
        if(!empty($this->session->userdata('earning_status'))){
            $state = $this->session->userdata('earning_status');
            if($state == 'paid' || $state == 'due'){
                $this->db->where($table.'.payment_status', $state);
            }
        }
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function earning_search_count($table,$search)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.payment_type,'.$table.'.payment_status,'.$table.'.payment_details,'.$table.'.amount,'.$table.'.purchase_datetime,'.$table.'.custom_payment_method_name, plan.name AS package_name, member.first_name AS member_first_name, member.last_name AS member_last_name', FALSE);
        $this->db->from($table);
        $this->db->join('plan', 'plan.plan_id = '.$table.'.plan_id', 'left');
        $this->db->join('member', 'member.member_id = '.$table.'.member_id', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.payment_type',$search)->or_like($table.'.payment_status',$search)->or_like($table.'.amount',$search)->or_like('plan.name',$search)->or_like('member.first_name',$search)->or_like('member.last_name',$search);
        if(!empty($this->session->userdata('earning_status'))){
            $state = $this->session->userdata('earning_status');
            if($state == 'paid' || $state == 'due'){
                $this->db->where($table.'.payment_status', $state);
            }
        }
        $query = $this->db->get();

        return $query->num_rows();
    }

    function allcontact_messages($table,$limit,$start,$col,$dir)
    {
        $this->db->select('*', FALSE);
        $this->db->from($table);
        $this->db->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function contact_message_search($table,$limit,$start,$search,$col,$dir)
    {
        $this->db->select('*', FALSE);
        $this->db->from($table);
        $this->db->like($table.'_id',$search)->or_like($table.'.name',$search)->or_like($table.'.subject',$search)->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function contact_message_search_count($table,$search)
    {
        $this->db->select('*', FALSE);
        $this->db->from($table);
        $this->db->like($table.'_id',$search)->or_like($table.'.name',$search)->or_like($table.'.subject',$search);
        $query = $this->db->get();

        return $query->num_rows();
    }

    function allstories($table, $limit, $start, $col, $dir)
    {
        $this->db->select(
            $table . '.' . $table . '_id, ' . 
            $table . '.title, ' . 
            $table . '.date, ' .  // ✅ Added date here
            $table . '.activity_photo, ' .   // changed here from image to activity_photo
            $table . '.approval_status, ' . 
            $table . '.post_time, ' . 
            $table . '.partner_name, ' . 
            $table . '.description, ' .  
            'member.first_name AS member_name', FALSE);
    
        $this->db->from($table);
        $this->db->join('member', 'member.member_id = '.$table.'.posted_by', 'left');
        $this->db->limit($limit, $start)->order_by($col, $dir);
        $query = $this->db->get();
    
        if($query->num_rows() > 0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }
    
    function story_search($table,$limit,$start,$search,$col,$dir)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.title, '.$table.'.image, '.$table.'.approval_status, '.$table.'.post_time,'. $table.'.partner_name, member.first_name AS member_name', FALSE);
        $this->db->from($table);
        $this->db->join('member', 'member.member_id = '.$table.'.posted_by', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.title',$search)->or_like($table.'.partner_name',$search)->or_like('member.first_name',$search)->limit($limit,$start)->order_by($col,$dir);
        $query = $this->db->get();

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function story_search_count($table,$search)
    {
        $this->db->select(''.$table.'.'.$table.'_id, '.$table.'.title, '.$table.'.image, '.$table.'.approval_status, '.$table.'.post_time,'. $table.'.partner_name, member.first_name AS member_name', FALSE);
        $this->db->from($table);
        $this->db->join('member', 'member.member_id = '.$table.'.posted_by', 'left');
        $this->db->like($table.'_id',$search)->or_like($table.'.title',$search)->or_like($table.'.partner_name',$search)->or_like('member.first_name',$search);
        $query = $this->db->get();

        return $query->num_rows();
    }

    function get_listed_messaging_members($member_id)
    {
        $message_array1 = array();
        $message_array2 = array();
        $message_list1 = $this->db->select('message_thread_to AS list')->select('message_thread_id')->select('message_thread_time')->get_where('message_thread', array('message_thread_from' => $member_id))->result();
        $message_list2 = $this->db->select('message_thread_from AS list')->select('message_thread_id')->select('message_thread_time')->get_where('message_thread', array('message_thread_to' => $member_id))->result();
        foreach ($message_list1 as $list1) {
            // $message_array1[] = $list1->list;
            $message_array1[] = array('message_thread_id' => $list1->message_thread_id, 'member_id' => $list1->list, 'message_thread_time' => $list1->message_thread_time);
        }
        foreach ($message_list2 as $list2) {
            // $message_array2[] = $list2->list;
            $message_array2[] = array('message_thread_id' => $list2->message_thread_id, 'member_id' => $list2->list, 'message_thread_time' => $list2->message_thread_time);
        }
        return $listed_messaging_members = array_unique (array_merge ($message_array1, $message_array2), SORT_REGULAR);
    }

    function allsite_language($table,$limit,$start,$col,$dir)
    {
        $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get($table);
        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function site_language_search($table,$limit,$start,$search,$col,$dir)
    {
        $query = $this->db->like('word',$search)->limit($limit,$start)->order_by($col,$dir)->get($table);


        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function site_language_search_count($table,$search)
    {
        $query = $this->db->like('word',$search)->get($table);

        return $query->num_rows();
    }

    function message_thread_member_position($thread_id,$member){
        $from = $this->db->get_where('message_thread',array('message_thread_id'=>$thread_id,'message_thread_from'=>$member))->num_rows();
        $to = $this->db->get_where('message_thread',array('message_thread_id'=>$thread_id,'message_thread_to'=>$member))->num_rows();
        if($from > 0){
            return 'from';
        } else if($to > 0){
            return 'to';
        }
    }

    function is_message_thread_seen($thread_id,$member){
        $position = $this->message_thread_member_position($thread_id,$member);
        $position_db_field = 'message_'.$position.'_seen';
        $seen = $this->db->get_where('message_thread', array('message_thread_id' => $thread_id))->row()->$position_db_field;
        if($seen == 'yes'){
            return true;
        }
        return false;
    }

    function timezone()
    {
        $timezone = $this->db->get_where('general_settings', array('type'=>'time_zone'))->row()->value;
        if($timezone != NULL){
            date_default_timezone_set ($timezone);
        }else{
            date_default_timezone_set('Asia/Dhaka');
        }
    }

    function demo(){
        if($this->config->item('demo') == 0 )
        {
            return 0;
        }else{
            return 1;
        }
    }


// ****************** Model Code Add 11-12-2020 **************************

    // ****************** Member Filter Code Start **************************

    function filter_members_count($member_gender)
        {
            $query = $this->db->get_where("member", array('gender'=>$member_gender,'membership'=>1))->result();
            return count($query);
        }


    function filtered_all_gender_members($member_gender,$limit,$start,$col,$dir){
        $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get_where("member", array("gender" => $member_gender,'membership'=>1));
        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function filtered_members_search($member_gender,$limit,$start,$search,$col,$dir){
        $this->db->limit($limit,$start);
        $this->db->order_by($col,$dir);
        $this->db->where('gender',$member_gender);
        $this->db->where('membership',1);
        // $this->db->where('status','approved');
        $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        $query = $this->db->get('member');

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function filtered_members_search_count($member_gender,$search)
    {
        $this->db->where('gender',$member_gender);
        $this->db->where('gender',$member_gender);
        $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        $query = $this->db->get('member');

        return $query->num_rows();
    }

    function filter_free_members_count($member_type)
    {
        $query = $this->db->get_where("member", array('membership'=>1))->result();
        return count($query);
    }

    function filtered_all_free_members($member_type,$limit,$start,$col,$dir){
        $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get_where("member", array('membership'=>1));
        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

function filtered_free_members_search($member_type,$limit,$start,$search,$col,$dir){
        $this->db->limit($limit,$start);
        $this->db->order_by($col,$dir);
        $this->db->where('membership',$member_type);
        $this->db->where('membership',1);
        $this->db->where('status','approved');
        $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        $query = $this->db->get('member');

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }


    // ************************** Export CSV Premium Members Details Starts *************************

    function filter_premium_members_count($member_gender)
        {
            $query = $this->db->get_where("member", array('gender'=>$member_gender,'membership'=>2, 'status' => 'approved'))->result();
            return count($query);
        }

    function filtered_all_premium_gender_members($member_gender,$limit,$start,$col,$dir)
        {
            $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get_where("member", array("gender" => $member_gender,'membership'=>2, 'status' => 'approved'));
            if($query->num_rows()>0)
            {
                return $query->result();
            }
            else
            {
                return null;
            }
        }

    function filtered_premium_members_search($member_gender,$limit,$start,$search,$col,$dir){
        $this->db->limit($limit,$start);
        $this->db->order_by($col,$dir);
        $this->db->where('gender',$member_gender);
        $this->db->where('membership',2);
        $this->db->where('status','approved');
        $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        $query = $this->db->get('member');

        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }

    function filtered_all_premium_members($member_type,$limit,$start,$col,$dir){
        $query = $this->db->limit($limit,$start)->order_by($col,$dir)->get_where("member", array('membership'=>2));
        if($query->num_rows()>0)
        {
            return $query->result();
        }
        else
        {
            return null;
        }
    }
// ****************** Model Code Add 11-12-2020 **************************

// Export CSV File code start(30-9-2021)
    function get_groom_free_members(){
 
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'1', 'status'=>'approved', 'gender' => '1'))->get()->result_array();
        return $response;
    }

    function get_active_groom_free_members(){
 
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'1', 'status'=>'approved', 'gender' => '1', 'is_closed' => 'no'))->get()->result_array();
        return $response;
    }
    
    function get_bride_free_members(){
 
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'1', 'status'=>'approved', 'gender' => '2'))->get()->result_array();
        return $response;
    }

    function get_active_bride_free_members(){
 
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'1', 'status'=>'approved', 'gender' => '2', 'is_closed' => 'no'))->get()->result_array();
        return $response;
    }
    
    function get_free_members_for_csv(){
 
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'1', 'status'=>'approved'))->get()->result_array();
        return $response;
    }

    function get_active_free_members_for_csv(){
 
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'1', 'status'=>'approved', 'is_closed' => 'no'))->get()->result_array();
        return $response;
    }
    
    function getPremiumGroomDetails(){
     
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'2','status'=>'approved','gender'=>'1'))->get()->result_array();
        return $response;
    }

    function getactivePremiumGroomDetails(){
     
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'2','status'=>'approved','gender'=>'1', 'is_closed' => 'no'))->get()->result_array();
        return $response;
    }
    
    function getPremiumBrideDetails(){
 
        $response = array();
        $response =  $this->db->select('*')->from('member')->where(array('membership'=>'2', 'status'=>'approved', 'gender' => '2'))->get()->result_array();
        return $response;
    }

    function getactivePremiumBrideDetails(){
 
        $response = array();
        $response =  $this->db->select('*')->from('member')->where(array('membership'=>'2', 'status'=>'approved', 'gender' => '2', 'is_closed' => 'no'))->get()->result_array();
        return $response;
    }
    
    function getPremiumUserDetails(){
 
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'2', 'status'=>'approved'))->get()->result_array();
        return $response;
    }

    function getactivePremiumUserDetails(){
 
        $response = array();
        $response = $this->db->select('*')->from('member')->where(array('membership'=>'2', 'status'=>'approved','is_closed' => 'no'))->get()->result_array();
        return $response;
    }



// code written by gowda
function allmembers_starfilter($membership,$limit,$start,$col,$dir,$nakshatra,$nakshatra_gender)
    { 
         if($nakshatra){
            if($nakshatra=='Ashwini Nakshatra')
            {
                $nakshtra_array=[1,2,27,6,13,7,15,9,10,11,12,21,4];
            
            }
            else if($nakshatra=='Bharani Nakshatra')
            {
                $nakshtra_array=[12,5,26,7,8,19,12,13,20,22];
            
            }
            else if($nakshatra=='Mrigasira Nakshatra')
            {
                $nakshtra_array=[27,3,5,7,9,19,12,14,21];
            
            }
            else if($nakshatra=='Ardra Nakshatra')
            {
                $nakshtra_array=[27,3,5,9,12,14,25,23];
            
            }
            else if($nakshatra=='Punarvasu Nakshatra')
            {
                $nakshtra_array=[1,26,27,3,4,5,6,8,10,12,13,15,17,19,22];
            
            }
            else if($nakshatra=='Pushya Nakshatra')
            {
                $nakshtra_array=[4,6,7,9,11,13,14,18,20,25];
            
            }
            else if($nakshatra=='Ashlesha Nakshatra')
            {
                $nakshtra_array=[1,8,10,14,17,19,21,24];
            
            }

            else if($nakshatra=='Magha Nakshatra')
            {
                $nakshtra_array=[2,26,27,4,5,7,8,9,11,13,15,16,20,22];
            
            }

            else if($nakshatra=='Purva Phalguni Nakshatra')
            {
                $nakshtra_array=[1,26,3,5,8,19,22,16,17,21];
            
            }

            else if($nakshatra=='Uttara Phalguni Nakshatra')
            {
                $nakshtra_array=[1,2,27,4,11,13,15,20,22,24];
            
            }

            else if($nakshatra=='Hasta Nakshatra')
            {
                $nakshtra_array=[3,5,11,12,14,16,18,19,21];
            
            }

            else if($nakshatra=='Chitra Nakshatra')
            {
                $nakshtra_array=[12,27,4,10,11,13,15,17,20,22,24];
            
            }

            else if($nakshatra=='Swati Nakshatra')
            {
                $nakshtra_array=[2,3,5,12,13,14,16,18,23,25];
            
            }

            else if($nakshatra=='Vishakha Nakshatra')
            {
                $nakshtra_array=[1,2,27,3,4,6,8,15,17,21,22,24];
            
            }

            else if($nakshatra=='Anuradha Nakshatra')
            {
                $nakshtra_array=[2,27,4,5,7,9,15,16,18,20,22,23,25];
            
            }

            else if($nakshatra=='Jyeshtha Nakshatra')
            {
                $nakshtra_array=[26,10,21,23,24,27,3,5];
            
            }

            else if($nakshatra=='Mula Nakshatra')
            {
                $nakshtra_array=[2,4,9,11,18,20,22];
            
            }

            else if($nakshatra=='Purva Ashadha Nakshatra')
            {
                $nakshtra_array=[19,12,14,10,21,23,25];
            
            }

            else if($nakshatra=='Uttara Ashadha Nakshatra')
            {
                $nakshtra_array=[1,27,4,6,8,9,11,13,15,17,20,22];
            
            }
            else if($nakshatra=='Shravana Nakshatra')
            {
                $nakshtra_array=[2,26,3,5,7,9,12,14,16,19,20,21,23,25];
            
            }

            else if($nakshatra=='Dhanishta Nakshatra')
            {
                $nakshtra_array=[1,26,27,4,6,8,19,11,13,15,17,10,20,21,23,25];
            
            }

            else if($nakshatra=='Shatabhisha Nakshatra')
            {
                $nakshtra_array=[2,3,5,8,10,12,9,14,16,18,21,23,25];
            
            }

            else if($nakshatra=='Purva Bhadra Nakshatra')
            {
                $nakshtra_array=[1,3,4,6,8,10,12,13,15,17];
            
            }
            else if($nakshatra=='Uttara Bhadra Nakshatra')
            {
                $nakshtra_array=[2,27,4,5,8,9,11,13,14,16,20,18,25];
            
            }

            else if($nakshatra=='Revati Nakshatra')
            {
                $nakshtra_array=[1,26,3,5,6,8,9,10,11,13,17,15];
            
            }
            else if($nakshatra=='Krittika Nakshatra')
            {
                $nakshtra_array=[1,2,27,20,6,8,9,12,13,15,10,21];
            
            }

            else if($nakshatra=='Rohini Nakshatra')
            {
                $nakshtra_array=[27,3,5,7,9,19,12,14,21];
            
            }

        $q = $this->db
        ->limit($limit,$start)
        ->order_by($col,$dir)
        ->select('*')
        ->from('member')
        ->join('nakshtramatch', 'member.nakshtra_id =nakshtra_match_id', 'inner')
        ->where_in('nakshtramatch.nakshtra_match_id',$nakshtra_array)
        ->where('membership', $membership)
        ->where('gender <>', $nakshatra_gender)
        ->get();
        if( $q->num_rows() >0) 
        {
           
        return $q->result();
        }
        else{
       return null;
         }
         }
        
    }

   function star_allmembers_count($member_type, $limit, $start, $order, $dir,$nakshatra,$nakshatra_gender)
    {
        if($nakshatra){
            if($nakshatra=='Ashwini Nakshatra')
            {
                $nakshtra_array=[1,2,27,6,13,7,15,9,10,11,12,21,4];
            
            }
            else if($nakshatra=='Bharani Nakshatra')
            {
                $nakshtra_array=[12,5,26,7,8,19,12,13,20,22];
            
            }
            else if($nakshatra=='Mrigasira Nakshatra')
            {
                $nakshtra_array=[27,3,5,7,9,19,12,14,21];
            
            }
            else if($nakshatra=='Ardra Nakshatra')
            {
                $nakshtra_array=[27,3,5,9,12,14,25,23];
            
            }
            else if($nakshatra=='Punarvasu Nakshatra')
            {
                $nakshtra_array=[1,26,27,3,4,5,6,8,10,12,13,15,17,19,22];
            
            }
            else if($nakshatra=='Pushya Nakshatra')
            {
                $nakshtra_array=[4,6,7,9,11,13,14,18,20,25];
            
            }
            else if($nakshatra=='Ashlesha Nakshatra')
            {
                $nakshtra_array=[1,8,10,14,17,19,21,24];
            
            }

            else if($nakshatra=='Magha Nakshatra')
            {
                $nakshtra_array=[2,26,27,4,5,7,8,9,11,13,15,16,20,22];
            
            }

            else if($nakshatra=='Purva Phalguni Nakshatra')
            {
                $nakshtra_array=[1,26,3,5,8,19,22,16,17,21];
            
            }

            else if($nakshatra=='Uttara Phalguni Nakshatra')
            {
                $nakshtra_array=[1,2,27,4,11,13,15,20,22,24];
            
            }

            else if($nakshatra=='Hasta Nakshatra')
            {
                $nakshtra_array=[3,5,11,12,14,16,18,19,21];
            
            }

            else if($nakshatra=='Chitra Nakshatra')
            {
                $nakshtra_array=[12,27,4,10,11,13,15,17,20,22,24];
            
            }

            else if($nakshatra=='Swati Nakshatra')
            {
                $nakshtra_array=[2,3,5,12,13,14,16,18,23,25];
            
            }

            else if($nakshatra=='Vishakha Nakshatra')
            {
                $nakshtra_array=[1,2,27,3,4,6,8,15,17,21,22,24];
            
            }

            else if($nakshatra=='Anuradha Nakshatra')
            {
                $nakshtra_array=[2,27,4,5,7,9,15,16,18,20,22,23,25];
            
            }

            else if($nakshatra=='Jyeshtha Nakshatra')
            {
                $nakshtra_array=[26,10,21,23,24,27,3,5];
            
            }

            else if($nakshatra=='Mula Nakshatra')
            {
                $nakshtra_array=[2,4,9,11,18,20,22];
            
            }

            else if($nakshatra=='Purva Ashadha Nakshatra')
            {
                $nakshtra_array=[19,12,14,10,21,23,25];
            
            }

            else if($nakshatra=='Uttara Ashadha Nakshatra')
            {
                $nakshtra_array=[1,27,4,6,8,9,11,13,15,17,20,22];
            
            }
            else if($nakshatra=='Shravana Nakshatra')
            {
                $nakshtra_array=[2,26,3,5,7,9,12,14,16,19,20,21,23,25];
            
            }

            else if($nakshatra=='Dhanishta Nakshatra')
            {
                $nakshtra_array=[1,26,27,4,6,8,19,11,13,15,17,10,20,21,23,25];
            
            }

            else if($nakshatra=='Shatabhisha Nakshatra')
            {
                $nakshtra_array=[2,3,5,8,10,12,9,14,16,18,21,23,25];
            
            }

            else if($nakshatra=='Purva Bhadra Nakshatra')
            {
                $nakshtra_array=[1,3,4,6,8,10,12,13,15,17];
            
            }
            else if($nakshatra=='Uttara Bhadra Nakshatra')
            {
                $nakshtra_array=[2,27,4,5,8,9,11,13,14,16,20,18,25];
            
            }

            else if($nakshatra=='Revati Nakshatra')
            {
                $nakshtra_array=[1,26,3,5,6,8,9,10,11,13,17,15];
            
            }
            else if($nakshatra=='Krittika Nakshatra')
            {
                $nakshtra_array=[1,2,27,20,6,8,9,12,13,15,10,21];
            
            }

            else if($nakshatra=='Rohini Nakshatra')
            {
                $nakshtra_array=[27,3,5,7,9,19,12,14,21];
            
            }
        }

        $query = $this->db
        ->select('*')
        ->from('member')
        ->join('nakshtramatch', 'member.nakshtra_id =nakshtra_match_id', 'inner')
        ->where_in('nakshtramatch.nakshtra_match_id',$nakshtra_array)
        ->where('membership', $member_type)
        ->where('gender <>', $nakshatra_gender)
        ->get()
        ->result();
        return count($query); 
    }

    function star_members_search($membership,$limit,$start,$search,$col,$dir,$nakshatra,$nakshatra_gender)
    {
        $this->db->limit($limit,$start);
        $this->db->order_by($col,$dir);
        $this->db->where('membership',$membership);
        
        $this->db->group_start();
        $this->db->like('percentage', $search,'after');
        $this->db->or_like('member_id', $search);
        $this->db->or_like('first_name', $search);
        $this->db->or_like('last_name', $search);
        $this->db->or_like('member_profile_id', $search);
        $this->db->group_end();
        $star_members_search = $this->allmembers_starfilter($membership, $limit, $start, '', $dir,$nakshatra,$nakshatra_gender);

        return $star_members_search;
    } 

    function star_members_search_count($membership,$limit,$start,$search,$col,$dir,$nakshatra,$nakshatra_gender)
    {
        $star_members_search = $this->star_members_search($membership, $limit, $start,$search,$col, $dir,$nakshatra,$nakshatra_gender);

        // $this->db->where("(member_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR member_profile_id LIKE '%$search%')");
        return count($star_members_search);

        // return  $star_members_search->num_rows();
    } 

    function star_select_html($from, $name, $field, $type, $class, $e_match = '', $condition = '', $c_match = '', $onchange = '',$condition_type='single')
    {
        $return = '';
        $other  = '';
        $multi  = 'no';
        $phrase = 'Choose a ' . $name;
        if ($class == 'demo-cs-multiselect') {
            $other = 'multiple';
            $name  = $name . '[]';
            if ($type == 'edit') {
                $e_match = json_decode($e_match);
                if ($e_match == NULL) {
                    $e_match = array();
                }
                $multi = 'yes';
            }
        }
        $return = '<select name="' . $name . '" onChange="' . $onchange . '(this.value,this)" class="' . $class . '" ' . $other . '  data-placeholder="' . $phrase . '" tabindex="2" data-hide-disabled="true" >';
        if (!is_array($from)) {
            if ($condition == '') {
                $all = $this->db->get($from)->result_array();
            } else if ($condition !== '') {
                if($condition_type=='single'){
                    $all = $this->db->get_where($from, array(
                        $condition => $c_match
                    ))->result_array();
                }else if($condition_type=='multi'){
                    $this->db->where_in($condition,$c_match);
                    $all = $this->db->get($from)->result_array();
                }
            }

            $return .= '<option value="">Choose one</option>';

            foreach ($all as $row):
                if ($type == 'add') {
                    $return .= '<option value="' . $row[$from . '_id'] . '">' . $row[$field] . '</option>';
                } else if ($type == 'edit') {
                  
                    $return .= '<option value="' . $row[$from . '_id'] . '" ';
                    
                    if ($multi == 'no') {
                        
                        if ($row[$from . '_id'] == $e_match) {
                            $return .= 'selected=."selected"';
                        }
                        
                        elseif ($row[$from . '_name'] == $e_match) {
                            $return .= 'selected=."selected"';
                        }
                    }

                    else if ($multi == 'yes') {
                        if (in_array($row[$from . '_id'], $e_match)) {
                            $return .= 'selected=."selected"';
                        }
                    }
                    $return .= '>' . $row[$field] . '</option>';
                }
            endforeach;
        } else {
            $all = $from;
            $return .= '<option value="">Choose one</option>';
            foreach ($all as $row):
                if ($type == 'add') {
                    $return .= '<option value="' . $row . '">';
                    if ($condition == '') {
                        $return .= ucfirst(str_replace('_', ' ', $row));
                    } else {
                        $return .= $this->Crud_model->get_type_name_by_id($condition, $row, $c_match);
                    }
                    $return .= '</option>';
                } else if ($type == 'edit') {
                    $return .= '<option value="' . $row . '" ';
                    if ($row == $e_match) {
                        $return .= 'selected=."selected"';
                    }
                    $return .= '>';

                    if ($condition == '') {
                        $return .= ucfirst(str_replace('_', ' ', $row));
                    } else {
                        $return .= $this->Crud_model->get_type_name_by_id($condition, $row, $c_match);
                    }

                    $return .= '</option>';
                }
            endforeach;
        }
        $return .= '</select>';
        return $return;
    }


function patnername_select_html($from,$gender,$selected)
    {   
        //echo $selected;
        $return = '';
        $return = '<select name="partner_name" onChange="" class="form-control form-control-sm selectpicker"      tabindex="2" data-hide-disabled="true" >';
        if($gender==2){
           $return = '<select name="member_name" onChange="" class="form-control form-control-sm selectpicker"      tabindex="2" data-hide-disabled="true" >';   
        }

        $this->db->where('gender',$gender);
        $this->db->where('status','approved');
        $query = $this->db->get($from);

        if($query->num_rows()>0)
        {
            $results    =    $query->result();
        }
        else
        {
            $results    =  null;
        }
        if($results){
             $return .= '<option value="">Choose one</option>';
            foreach($results as $key=>$row){
                        
                   
                    if ($row->member_id == $selected) { 
                         //$return .= 'selected="selected"'; 
                         $return .= '<option value="' . $row->member_id .'" selected="selected">' . $row->first_name.' '.$row->last_name . '</option>';
                     }
                   

                 $return .= '<option value="' . $row->member_id .'">' . $row->first_name.' '.$row->last_name . '</option>';
                    $return .= '</option>';

                 
            }
        }
        $return .= '</select>';
        return $return;
    }

function ocupation_select_html()
    {   
        //echo $selected;
       
        $return = '<select name="profession" id="filter_profession" onChange="filter_members(\'0\',\'search\')" class="form-control form-control-sm selectpicker" tabindex="2" data-hide-disabled="true" >';
       

         $query = $this->db->get('occupation');

        if($query->num_rows()>0)
        {
            $results    =    $query->result();
        }
        else
        {
            $results    =  null;
        }
        if($results){
             $return .= '<option value="">Choose one</option>';
            foreach($results as $key=>$row){
          
                   

                 $return .= '<option value="' . $row->occupation_id  .'">' . $row->name.'</option>';
                    $return .= '</option>';

                 
            }
        }
        $return .= '</select>';
        return $return;
    }

     function get_membername_by_id($table,$field1val)
    {
        if(!empty($field1val)){
            $this->db->where('member_id',$field1val);
            $query = $this->db->get($table);
              if($query->num_rows()>0)
            {
                $results    =    $query->row_array();

               return $results['first_name'].' '.$results['last_name'];
              
                 
            }
            else
            {
              return  null;
            }
        }else
            {
              return  null;
            }
    }

}
