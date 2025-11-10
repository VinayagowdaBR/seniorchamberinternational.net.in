<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{

	/*
	 *	Developed by: Active IT zone
	 *	Date	: 18 September, 2017
	 *	Active Matrimony CMS
	 *	http://codecanyon.net/user/activeitezone
	 */

function __construct()
{
	parent::__construct();
	$this->load->database(); // Ensure database is loaded
	$this->load->library('session'); // Add this
	$this->system_name = $this->Crud_model->get_type_name_by_id('general_settings', '1', 'value');
	$this->system_email = $this->Crud_model->get_type_name_by_id('general_settings', '2', 'value');
	$this->system_title = $this->Crud_model->get_type_name_by_id('general_settings', '3', 'value');
	$this->Crud_model->timezone();
	$this->load->library('spreadsheet');
	$this->lang->load("member", "kannada");
}


/////////////////////////////////////////// Contribution Package Section ///////////////////////////////////////////
	public function contribution($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "packages/index.php";
				$page_data['folder'] = "contribution";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "packages/index.php";
				$plans = $this->db->where('contribution', 1)->get("plan")->result();
				// Calculate total_amount = amount + gst for each plan
				foreach ($plans as $plan) {
					$plan->total_amount = $plan->amount + ($plan->amount * $plan->gst / 100);
				}
				$page_data['all_plans'] = $plans;
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_package!");
				} elseif ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_package!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['danger_alert'] = translate("you_have_successfully_deleted_the_package!");
				} elseif ($this->session->flashdata('alert') == "failed_image") {
					$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "contribution";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_package") {
				$page_data['top'] = "packages/packages.php";
				$page_data['folder'] = "contribution";
				$page_data['file'] = "add_package.php";
				$page_data['bottom'] = "packages/packages.php";
				$page_data['page_name'] = "contribution";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "do_add") {
				$this->contribution_do_add();
			} elseif ($para1 == "edit_package") {
				$page_data['top'] = "packages/packages.php";
				$page_data['folder'] = "contribution";
				$page_data['file'] = "edit_package.php";
				$page_data['bottom'] = "packages/packages.php";
				$page_data['get_plan'] = $this->db->get_where("plan", array("plan_id" => $para2))->result();
				$page_data['page_name'] = "contribution";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "update") {
				$this->contribution_update();
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$plan_id = $para2;
				$this->db->where('plan_id', $para2);
				$result = $this->db->delete('plan');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
					redirect(base_url() . 'admin/contribution', 'refresh');
				} else {
					echo "Data Failed to Delete!";
				}
				exit;
			}
		}
	}




	// public function contribution_do_add()
	// {
	// 	$data['name'] = $this->input->post('name');
	// 	$data['amount'] = $this->input->post('amount');
	// 	$data['gst'] = $this->input->post('gst');
	// 	$data['start_date'] = $this->input->post('start_date');
	// 	$data['end_date'] = $this->input->post('end_date');
	// 	$data['contribution'] = 1;

	// 	if (!empty($_POST['exp_int_status'])) {
	// 		$data['exp_int_status'] = 1;
	// 	} else {
	// 		$data['exp_int_status'] = 0;
	// 	}

	// 	if (!empty($_POST['dir_msg_status'])) {
	// 		$data['dir_msg_status'] = 1;
	// 	} else {
	// 		$data['dir_msg_status'] = 0;
	// 	}

	// 	$this->db->insert('plan', $data);
	// 	$plan_id = $this->db->insert_id();

	// 	if (!demo()) {
	// 		if ($_FILES['image']['name'] !== '') {
	// 			$id = $plan_id;
	// 			$path = $_FILES['image']['name'];
	// 			$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
	// 			if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
	// 				$this->Crud_model->file_up("image", "plan", $id, '', '', $ext);
	// 				$images[] = array('image' => 'plan_' . $id . $ext, 'thumb' => 'plan_' . $id . '_thumb' . $ext);
	// 				$data['image'] = json_encode($images);
	// 			} else {
	// 				$this->session->set_flashdata('alert', 'failed_image');
	// 				redirect(base_url() . 'admin/contribution', 'refresh');
	// 			}
	// 		}
	// 	}

	// 	$this->db->where('plan_id', $plan_id);
	// 	$result = $this->db->update('plan', $data);
	// 	recache();
	// 	if ($result) {
	// 		$this->session->set_flashdata('alert', 'add');
	// 		redirect(base_url() . 'admin/contribution', 'refresh');
	// 	} else {
	// 		echo "Data Failed to Add!";
	// 	}
	// 	exit;
	// }

	// public function contribution_update()
	// {
	// 	$plan_id = $this->input->post('plan_id');
	// 	$data['name'] = $this->input->post('name');
	// 	$data['amount'] = $this->input->post('amount');
	// 	$data['gst'] = $this->input->post('gst');
	// 	$data['start_date'] = $this->input->post('start_date');
	// 	$data['end_date'] = $this->input->post('end_date');
	// 	$data['contribution'] = 1;

	// 	if (!empty($_POST['exp_int_status'])) {
	// 		$data['exp_int_status'] = 1;
	// 	} else {
	// 		$data['exp_int_status'] = 0;
	// 	}

	// 	if (!empty($_POST['dir_msg_status'])) {
	// 		$data['dir_msg_status'] = 1;
	// 	} else {
	// 		$data['dir_msg_status'] = 0;
	// 	}

	// 	if (!demo()) {
	// 		if ($_FILES['image']['name'] !== '') {
	// 			$id = $plan_id;
	// 			$path = $_FILES['image']['name'];
	// 			$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
	// 			if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
	// 				$this->Crud_model->file_up("image", "plan", $id, '', '', $ext);
	// 				$images[] = array('image' => 'plan_' . $id . $ext, 'thumb' => 'plan_' . $id . '_thumb' . $ext);
	// 				$data['image'] = json_encode($images);
	// 			} else {
	// 				$this->session->set_flashdata('alert', 'failed_image');
	// 				redirect(base_url() . 'admin/contribution', 'refresh');
	// 			}
	// 		}
	// 	}

	// 	$this->db->where('plan_id', $plan_id);
	// 	$result = $this->db->update('plan', $data);
	// 	recache();
	// 	if ($result) {
	// 		$this->session->set_flashdata('alert', 'edit');
	// 		redirect(base_url() . 'admin/contribution', 'refresh');
	// 	} else {
	// 		echo "Data Failed to Edit!";
	// 	}
	// 	exit;
	// }


/////////////////////////////////////////// Contribution Package Section End ///////////////////////////////////////////



	public function index()
	{
		//error_reporting(1);
		//ini_set('display_errors',1);
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "dashboard.php";
			$page_data['folder'] = "dashboard";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "dashboard.php";
			$page_data['page_name'] = "dashboard";
			$page_data['total_members'] = $this->db->get('member')->num_rows();
			$page_data['total_pending_members'] = $this->db->get_where('member', array('status' => 'pending'))->num_rows();
			$page_data['total_approved_members'] = $this->db->get_where('member', array('status' => 'approved'))->num_rows();
			$page_data['pending'] = $this->db->get_where('member', array('membership' => 1, 'status' => 'pending'))->num_rows();
			$page_data['approved'] = $this->db->get_where('member', array('membership' => 1, 'status' => 'approved'))->num_rows();
			$page_data['prepending'] = $this->db->get_where('member', array('membership' => 2, 'status' => 'pending'))->num_rows();
			$page_data['preapproved'] = $this->db->get_where('member', array('membership' => 2, 'status' => 'approved'))->num_rows();
			$page_data['total_premium_members'] = $this->db->get_where('member', array('membership' => 2))->num_rows();
			$page_data['total_free_members'] = $this->db->get_where('member', array('membership' => 1))->num_rows();
			$page_data['total_blocked_members'] = $this->db->get_where('member', array('is_blocked' => 'yes'))->num_rows();
			$page_data['total_earnings'] = $this->db->select_sum('amount')->get('package_payment')->row()->amount ?? 0;
			$page_data['total_paid'] = $this->db->select_sum('amount')->from('package_payment')->where('payment_status', 'paid')->get()->row()->amount ?? 0;
			$page_data['total_due'] = $this->db->select_sum('amount')->from('package_payment')->where('payment_status', 'due')->get()->row()->amount ?? 0;

			$last_month_timestamp = strtotime(date('d-m-Y h:i:s', strtotime("-1 months")));
			$page_data['paid_last_month_earnings'] = $this->db->select_sum('amount')->get_where('package_payment', array('purchase_datetime >=' => $last_month_timestamp, 'payment_status' => 'paid'))->row()->amount ?? 0;

			$last_3_months_timestamp = strtotime(date('d-m-Y h:i:s', strtotime("-3 months")));
			$page_data['paid_last_3_months_earnings'] = $this->db->select_sum('amount')->get_where('package_payment', array('purchase_datetime >=' => $last_3_months_timestamp, 'payment_status' => 'paid'))->row()->amount ?? 0;

			$half_yearly_timestamp = strtotime(date('d-m-Y h:i:s', strtotime("-6 months")));
			$page_data['paid_half_yearly_earnings'] = $this->db->select_sum('amount')->get_where('package_payment', array('purchase_datetime >=' => $half_yearly_timestamp, 'payment_status' => 'paid'))->row()->amount ?? 0;

			$last_year_timestamp = strtotime(date('d-m-Y h:i:s', strtotime("-12 months")));
			$page_data['paid_yearly_earnings'] = $this->db->select_sum('amount')->get_where('package_payment', array('purchase_datetime >=' => $last_year_timestamp, 'payment_status' => 'paid'))->row()->amount ?? 0;

			$page_data['due_last_month_earnings'] = $this->db->select_sum('amount')->get_where('package_payment', array('purchase_datetime >=' => $last_month_timestamp, 'payment_status' => 'due'))->row()->amount ?? 0;

			$page_data['due_last_3_months_earnings'] = $this->db->select_sum('amount')->get_where('package_payment', array('purchase_datetime >=' => $last_3_months_timestamp, 'payment_status' => 'due'))->row()->amount ?? 0;

			$page_data['due_half_yearly_earnings'] = $this->db->select_sum('amount')->get_where('package_payment', array('purchase_datetime >=' => $half_yearly_timestamp, 'payment_status' => 'due'))->row()->amount ?? 0;

			$page_data['due_yearly_earnings'] = $this->db->select_sum('amount')->get_where('package_payment', array('purchase_datetime >=' => $last_year_timestamp, 'payment_status' => 'due'))->row()->amount ?? 0;
			$page_data['free_member_bride'] = $this->db->get_where('member', array('membership' => 1, 'gender' => '2'))->num_rows();
			$page_data['approved_free_member_bride'] = $this->db->get_where('member', array('membership' => 1, 'gender' => '2', 'status' => 'approved',))->num_rows();
			$page_data['pending_free_member_bride'] = $this->db->get_where('member', array('membership' => 1, 'gender' => '2', 'status' => 'pending'))->num_rows();

			$page_data['free_member_groom'] = $this->db->get_where('member', array('membership' => 1, 'gender' => '1'))->num_rows();
			$page_data['approved_free_member_groom'] = $this->db->get_where('member', array('membership' => 1, 'gender' => '1', 'status' => 'approved',))->num_rows();
			$page_data['pending_free_member_groom'] = $this->db->get_where('member', array('membership' => 1, 'gender' => '1', 'status' => 'pending'))->num_rows();

			// --------------- Premium member bride and groom details Start --------------
			$page_data['premium_member_bride'] = $this->db->get_where('member', array('membership' => 2, 'gender' => '2'))->num_rows();
			$page_data['approved_premium_member_bride'] = $this->db->get_where('member', array('membership' => 2, 'gender' => '2', 'status' => 'approved',))->num_rows();
			$page_data['pending_premium_member_bride'] = $this->db->get_where('member', array('membership' => 2, 'gender' => '2', 'status' => 'pending'))->num_rows();

			$page_data['premium_member_groom'] = $this->db->get_where('member', array('membership' => 2, 'gender' => '1'))->num_rows();
			$page_data['approved_premium_member_groom'] = $this->db->get_where('member', array('membership' => 2, 'gender' => '1', 'status' => 'approved',))->num_rows();
			$page_data['pending_premium_member_groom'] = $this->db->get_where('member', array('membership' => 2, 'gender' => '1', 'status' => 'pending'))->num_rows();

			// --------------- Premium member bride and groom details End --------------
			$page_data['remaining_members'] = $this->db->get_where('member', array('gender' => '0'))->num_rows();
			$page_data['pending_remaining_members'] = $this->db->get_where('member', array('gender' => '0', 'status' => 'pending'))->num_rows();
			$page_data['approve_remaining_members'] = $this->db->get_where('member', array('gender' => '0', 'status' => 'approved'))->num_rows();


			$page_data['total_stories'] = $this->db->get('happy_story')->num_rows();
			$page_data['approved_stories'] = $this->db->get_where('happy_story', array('approval_status' => 1))->num_rows();
			$page_data['pending_stories'] = $this->db->get_where('happy_story', array('approval_status' => 0))->num_rows();

			//--added by kavya (19-3-21)
			$page_data['total_blocked_male'] = $this->db->get_where('member', array('is_blocked' => 'yes', 'gender' => '1'))->num_rows();
			$page_data['total_blocked_female'] = $this->db->get_where('member', array('is_blocked' => 'yes', 'gender' => '2'))->num_rows();
			$page_data['started'] = $this->db->select('*')->from('member')->order_by('member_since', 'asc')->get()->row();


			$this->load->view('back/index', $page_data);
		}
	}

	public function get_legions_of_area($area_id = null) {
		$this->load->model('Crud_model'); // Load model if not autoloaded
	
		if ($area_id === null) {
			// Get area_id from URI segment or GET parameter if not passed
			$area_id = $this->input->get('area_id') ?? $this->uri->segment(3);
		}
	
		$legions = $this->Crud_model->get_legions_by_area($area_id);
	
		// Log legions as JSON string to avoid "array to string" warning
		log_message('info', 'Fetched legions: ' . json_encode($legions));
	
		// Return JSON response
		echo json_encode($legions);
	}
	
	public function add_area() {
		$this->load->model('Crud_model');
	
		$area_name = $this->input->post('area_name');  // missing semicolon fixed
	
		if (empty($area_name)) {
			$response = [
				'success' => false,
				'message' => 'Area name is required.'  // message fixed to reflect area_name
			];
			log_message('error', 'Add Area validation failed: ' . json_encode($response));
			echo json_encode($response);
			return;
		}
	
		$data = ['name' => $area_name];
	
		$insert_id = $this->Crud_model->insert_area($data);
	
		if ($insert_id) {
			$response = [
				'success' => true,
				'message' => 'Area added successfully.',
				'area_name' => $area_name,
				'area_id' => $insert_id
			];
			log_message('info', 'Add Area success response: ' . json_encode($response));
			echo json_encode($response);
		} else {
			$response = [
				'success' => false,
				'message' => 'Failed to add area.'
			];
			log_message('error', 'Add Area failed to insert: ' . json_encode($response));
			echo json_encode($response);
		}
	}
	

public function add_legion() {
    // Load model
    $this->load->model('Crud_model');

    // Get POST data - ADD PREFIX HERE
    $legion_name = $this->input->post('legion_name');
    $prefix = $this->input->post('prefix'); // NEW LINE
    $area_id = $this->input->post('area_id');

    // Log the received data
    log_message('info', 'Add Legion request received with data: legion_name=' . $legion_name . ', prefix=' . $prefix . ', area_id=' . $area_id);

    // Simple validation - UPDATE VALIDATION
    if (empty($legion_name) || empty($prefix) || empty($area_id)) {
        $response = [
            'success' => false,
            'message' => 'Legion name, prefix, and area ID are required.'
        ];
        log_message('error', 'Add Legion validation failed: ' . json_encode($response));
        echo json_encode($response);
        return;
    }

    // Prepare data - ADD PREFIX TO DATA ARRAY
    $data = [
        'name' => $legion_name,
        'prefix' => strtoupper(trim($prefix)), // Store in uppercase
        'area_id' => $area_id
    ];

    // Insert via model and check result
    $insert_id = $this->Crud_model->insert_legion($data);

    if ($insert_id) {
        // Get the generated ID from the database
        $this->db->where('id', $insert_id);
        $legion = $this->db->get('legions')->row();
        
        $response = [
            'success' => true,
            'message' => 'Legion added successfully.',
            'legion_name' => $legion_name,
            'prefix' => strtoupper(trim($prefix)), // RETURN PREFIX
            'legion_id' => $insert_id,
            'legion_id_generated' => $legion->legion_id_generated ?? '', // RETURN GENERATED ID
            'area_id' => $area_id
        ];
        log_message('info', 'Add Legion success response: ' . json_encode($response));
        echo json_encode($response);
    } else {
        $response = [
            'success' => false,
            'message' => 'Failed to add legion.'
        ];
        log_message('error', 'Add Legion failed to insert: ' . json_encode($response));
        echo json_encode($response);
    }
}


public function delete_legion()
{
    log_message('info', 'Delete legion method invoked');

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $legion_id = $data['legion_id'] ?? null;

    if (!$legion_id) {
        echo json_encode(['success' => false, 'message' => 'Missing legion ID']);
        return;
    }

    // ✅ Check if legion is being used in `member` table
    $inUse = $this->db->get_where('member', ['legion_id' => $legion_id])->num_rows();

    if ($inUse > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete: This legion is assigned to one or more members.'
        ]);
        return;
    }

    // ✅ Proceed to delete from `legions` table
    $this->db->where('id', $legion_id);
    $deleted = $this->db->delete('legions');

    if ($deleted) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Delete failed. Please try again.'
        ]);
    }
}


	
public function delete_area()
{
    log_message('info', 'Delete area method invoked');

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $area_id = $data['area_id'] ?? null;

    if (!$area_id) {
        echo json_encode(['success' => false, 'message' => 'Missing area ID']);
        return;
    }

    // ✅ Check if the area is being used in `legions` table
    $inUse = $this->db->get_where('legions', ['area_id' => $area_id])->num_rows();

    if ($inUse > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete: This area has assigned legions.'
        ]);
        return;
    }

    // ✅ Proceed with deletion
    $this->db->where('id', $area_id); // assuming `id` is primary key in `area` table
    $deleted = $this->db->delete('areas'); // use actual table name

    if ($deleted) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Delete failed. Please try again.'
        ]);
    }
}

public function area_legion() {
    // Check permission
    if ($this->admin_permission() == FALSE) {
        redirect(base_url() . 'admin/login', 'refresh');
    }

    // Load the model
    $this->load->model('Crud_model');

    // Fetch the data here - MAKE SURE THIS INCLUDES PREFIX
    $page_data['areas'] = $this->Crud_model->get_areas_with_legions();

    // Prepare other page data
    $page_data['title'] = "Area & Legions || " . $this->system_title;
    $page_data['page_name'] = "area_legion";
    $page_data['top'] = "dashboard.php";
    $page_data['folder'] = "area_legion"; 
    $page_data['file'] = "index.php";    
    $page_data['bottom'] = "dashboard.php";

    // Now pass the $page_data array with areas data to your view
    $this->load->view('back/index', $page_data);
}

public function add_member() {
    $this->load->model('Crud_model');

    // Get POST data
    $first_name = $this->input->post('first_name');
    $last_name = $this->input->post('last_name');
    $email = $this->input->post('email');
    $mobile = $this->input->post('mobile');
    $gender = $this->input->post('gender');
    $legion_id = $this->input->post('legion_id');
    $area_id = $this->input->post('area_id');

    // Validation
    if (empty($first_name) || empty($email) || empty($legion_id) || empty($area_id)) {
        $response = [
            'success' => false,
            'message' => 'Required fields are missing.'
        ];
        echo json_encode($response);
        return;
    }

    // Prepare member data
    $member_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'mobile' => $mobile,
        'gender' => $gender,
        'legion_id' => $legion_id,
        'area_id' => $area_id,
        'password' => password_hash('default123', PASSWORD_DEFAULT), // Default password
        'email_verification_status' => 1 // Verified by default for admin creation
    ];

    $member_id = $this->Crud_model->insert_member($member_data);

    if ($member_id) {
        // Get the generated profile ID
        $this->db->where('member_id', $member_id);
        $member = $this->db->get('member')->row();
        
        $response = [
            'success' => true,
            'message' => 'Member added successfully.',
            'member_id' => $member_id,
            'member_profile_id' => $member->member_profile_id,
            'full_name' => $first_name . ' ' . $last_name
        ];
        echo json_encode($response);
    } else {
        $response = [
            'success' => false,
            'message' => 'Failed to add member.'
        ];
        echo json_encode($response);
    }
}


	

	function admin_permission()
	{
		$admin_id = $this->session->userdata('admin_id');
		if ($admin_id == NULL) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function admins($para1 = '', $para2 = '')
	{
		$user_data = $this->session->userdata('user_data');
		log_message('info', 'User session data: ' . json_encode($user_data));
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] 	= "Admin || " . $this->system_title;

			if ($para1 == '') {
				$page_data['top'] 		= "member_configure/index.php";
				$page_data['folder'] 	= "admin";
				$page_data['file'] 		= "index.php";
				$page_data['bottom'] 	= "member_configure/index.php";
				$this->db->where_not_in('admin_id', 1);
				$page_data['all_admins'] = $this->db->get("admin")->result();
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "admin";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_admin") {
				// $areas = $this->Crud_model->get_all_areas(); // Replace with your actual model name
				// log_message('debug', 'Fetched Areas: ' . print_r($areas, true));

				$page_data['areas'] = $this->Crud_model->get_all_areas();
				
				$page_data['top'] 		= "members/index.php";
				$page_data['folder'] 	= "admin";
				$page_data['file']	 	= "add_admin.php";
				$page_data['bottom'] 	= "members/index.php";
				$page_data['page_name'] = "admin";
				if ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				}

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "edit_admin") {
				$page_data['top'] 		= "members/index.php";
				$page_data['folder'] 	= "admin";
				$page_data['file']	 	= "edit_admin.php";
				$page_data['bottom'] 	= "members/index.php";
				$page_data['page_name'] = "admin";

				$page_data['admin_data'] = $this->db->get_where('admin', array(
					'admin_id' => $para2
				))->result_array();

				if ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				}

				$this->load->view('back/index', $page_data);
			} 
			
			// elseif ($para1 == "do_add") {
			// 	$this->form_validation->set_rules('name', 'Name', 'required');
			// 	$this->form_validation->set_rules('email', 'Email', 'required');
			// 	$this->form_validation->set_rules('phone', 'Phone No.', 'required');
			// 	$this->form_validation->set_rules('role', 'role', 'required');


			// 	if ($this->form_validation->run() == FALSE) {
			// 		$page_data['top'] 		= "members/index.php";
			// 		$page_data['folder'] 	= "admin";
			// 		$page_data['file']	 	= "add_admin.php";
			// 		$page_data['bottom'] 	= "members/index.php";
			// 		$page_data['page_name'] = "admin";

			// 		$page_data['form_contents'] = $this->input->post();

			// 		$page_data['danger_alert'] = translate("failed_to_add_the_data!");

			// 		$this->load->view('back/index', $page_data);
			// 	} else {
			// 		$data['name'] = $this->input->post('name');
			// 		$data['email'] = $this->input->post('email');
			// 		$data['phone'] = $this->input->post('phone');
			// 		$data['address'] = $this->input->post('address');
			// 		$password = substr(hash('sha512', rand()), 0, 12);
			// 		$data['password'] = sha1($password);
			// 		$data['role'] = $this->input->post('role');
			// 		$data['timestamp'] = time();
			// 		$result = $this->db->insert('admin', $data);

			// 		$this->Email_model->member_staff_account_opening_by_admin('admin', $data['email'], $password);

			// 		recache();
			// 		if ($result) {
			// 			$this->session->set_flashdata('alert', 'add');
			// 			redirect(base_url() . 'admin/admins', 'refresh');
			// 		} else {
			// 			$this->session->set_flashdata('alert', 'failed_add');
			// 			redirect(base_url() . 'admin/admins', 'refresh');
			// 		}
			// 	}
			elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
				$this->form_validation->set_rules('phone', 'Phone No.', 'required');
				$this->form_validation->set_rules('role', 'Role', 'required');
				$this->form_validation->set_rules('area', 'Area', 'required');
				$this->form_validation->set_rules('legion_id', 'Legion', 'required');
				$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
                $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

			
				if ($this->form_validation->run() == FALSE) {
					// handle validation failure - load form with errors
					$page_data['top']       = "members/index.php";
					$page_data['folder']    = "admin";
					$page_data['file']      = "add_admin.php";
					$page_data['bottom']    = "members/index.php";
					$page_data['page_name'] = "admin";
			        $page_data['form_contents'] = $this->input->post();
                    $page_data['areas'] = $this->Crud_model->get_all_areas(); // populate dropdown
                	$page_data['form_contents'] = $this->input->post();
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
			
					$this->load->view('back/index', $page_data);
				} else {
					// Prepare admin data
					$data['name']      = $this->input->post('name');
					$data['email']     = $this->input->post('email');
					$data['phone']     = $this->input->post('phone');
					$data['address']   = $this->input->post('address');
					$data['role']      = $this->input->post('role');
					$password = $this->input->post('password');
					$data['password']  = sha1($password);
					$data['timestamp'] = time();
			
					// Insert admin record
					$this->db->insert('admin', $data);
					$admin_id = $this->db->insert_id();
			
					if ($admin_id) {
						// Define static arrays of role IDs
						$areaRoleArray   = [3, 4, 5,6]; // Replace with real area role IDs
						$legionRoleArray = [2, 7,8,9];    // Replace with real legion role IDs
					
						$role_id = (int)$this->input->post('role');
					
						// Insert into admin_area if role is allowed
						if (in_array($role_id, $areaRoleArray)) {
							$area_data = [
								'admin_id' => $admin_id,
								'area_id'  => $this->input->post('area')
							];
							$this->db->insert('admin_area', $area_data);
						}
					
						// Insert into admin_legion if role is allowed
						if (in_array($role_id, $legionRoleArray)) {
							$legion_data = [
								'admin_id'  => $admin_id,
								'legion_id' => $this->input->post('legion_id')
							];
							$this->db->insert('admin_legion', $legion_data);
						}
					
						// Send email with password
						$this->Email_model->member_staff_account_opening_by_admin('admin', $data['email'], $password);
					
						recache();
					
						$this->session->set_flashdata('alert', 'add');
						redirect(base_url() . 'admin/admins', 'refresh');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
						redirect(base_url() . 'admin/admins', 'refresh');
					}
					
				}
			
			
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('email', 'Email', 'required');
				$this->form_validation->set_rules('phone', 'Phone No.', 'required');
				$this->form_validation->set_rules('role', 'role', 'required');


				if ($this->form_validation->run() == FALSE) {
					$page_data['top'] 		= "members/index.php";
					$page_data['folder'] 	= "admin";
					$page_data['file']	 	= "edit_admin.php";
					$page_data['bottom'] 	= "members/index.php";
					$page_data['page_name'] = "admin";

					$page_data['form_contents'] = $this->input->post();

					$page_data['admin_data'] = $this->db->get_where('admin', array(
						'admin_id' => $para2
					))->result_array();

					$page_data['danger_alert'] = translate("failed_to_update_the_data!");

					$this->load->view('back/index', $page_data);
				} else {
					$data['name'] = $this->input->post('name');
					$data['email'] = $this->input->post('email');
					$data['phone'] = $this->input->post('phone');
					$data['address'] = $this->input->post('address');

					$data['role'] = $this->input->post('role');
					$data['timestamp'] = time();
					$this->db->where('admin_id', $para2);
					$result = $this->db->update('admin', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
						redirect(base_url() . 'admin/admins', 'refresh');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
						redirect(base_url() . 'admin/admins', 'refresh');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('admin_id', $para2);
				$result = $this->db->delete('admin');

				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}

	function role($para1 = '', $para2 = '')
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] 	= "Admin || " . $this->system_title;

			if ($para1 == '') {
				$page_data['top'] 		= "member_configure/index.php";
				$page_data['folder'] 	= "role";
				$page_data['file'] 		= "index.php";
				$page_data['bottom'] 	= "member_configure/index.php";
				$page_data['all_roles'] = $this->db->get("role")->result();
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "role";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_role") {
				$page_data['top'] 		= "members/index.php";
				$page_data['folder'] 	= "role";
				$page_data['file']	 	= "add_role.php";
				$page_data['bottom'] 	= "members/index.php";
				$page_data['page_name'] = "role";
				$page_data['all_permissions'] = $this->db->get('permission')->result_array();

				if ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				}

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "edit_role") {
				$page_data['top'] 		= "members/index.php";
				$page_data['folder'] 	= "role";
				$page_data['file']	 	= "edit_role.php";
				$page_data['bottom'] 	= "members/index.php";
				$page_data['page_name'] = "role";
				$page_data['all_permissions'] = $this->db->get('permission')->result_array();
				$page_data['role_data'] = $this->db->get_where('role', array('role_id' => $para2))->result_array();

				if ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				}

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'Role Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$page_data['top'] 		= "members/index.php";
					$page_data['folder'] 	= "role";
					$page_data['file']	 	= "add_role.php";
					$page_data['bottom'] 	= "members/index.php";
					$page_data['page_name'] = "role";
					$page_data['all_permissions'] = $this->db->get('permission')->result_array();

					$page_data['form_contents'] = $this->input->post();
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");

					$this->load->view('back/index', $page_data);
				} else {
					$data['name'] = $this->input->post('name');
					$data['permission'] = json_encode($this->input->post('permission'));
					$data['description'] = $this->input->post('description');
					$result = $this->db->insert('role', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
						redirect(base_url() . 'admin/role', 'refresh');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
						redirect(base_url() . 'admin/role', 'refresh');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'Role Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$page_data['top'] 		= "members/index.php";
					$page_data['folder'] 	= "role";
					$page_data['file']	 	= "edit_role.php";
					$page_data['bottom'] 	= "members/index.php";
					$page_data['page_name'] = "role";
					$page_data['all_permissions'] = $this->db->get('permission')->result_array();
					$page_data['role_data'] = $this->db->get_where('role', array('role_id' => $para2))->result_array();

					$page_data['form_contents'] = $this->input->post();
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");

					$this->load->view('back/index', $page_data);
				} else {
					$data['name'] = $this->input->post('name');
					$data['permission'] = json_encode($this->input->post('permission'));
					$data['description'] = $this->input->post('description');

					$this->db->where('role_id', $para2);
					$this->db->update('role', $data);
					recache();

					$this->session->set_flashdata('alert', 'edit');
					redirect(base_url() . 'admin/role', 'refresh');
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('role_id', $para2);
				$result = $this->db->delete('role');

				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}



	function members($para1 = "", $para2 = "", $para3 = "", $para4 = "")
	{




		 $log_message = "members - para1: $para1, para2: $para2, para3: $para3, para4: $para4";
    	log_message("info", $log_message);
	     //error_reporting(E_ALL);
	     // ini_set('display_errors',1);
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			// if (!empty($_POST['free_member_gender'])) {
			// 	$this->session->set_userdata('free_member_status_type', $_POST['free_member_gender']);
			// 	$this->session->set_userdata('free_filter_status', $_POST['free_filter_status']);
			// 	$this->session->set_userdata('free_member_profile_image', $_POST['free_member_profile_image']);
			// }

			// if (!empty($_POST['premium_member_gender'])) {
			// 	$this->session->set_userdata('premium_member_status_type', $_POST['premium_member_gender']);
			// 	$this->session->set_userdata('premium_filter_status', $_POST['premium_filter_status']);
			// 	$this->session->set_userdata('premium_member_profile_image', $_POST['premium_member_profile_image']);
			// }
			$member_types = ['guest', 'free', 'premium', 'ngb', 'national'];

			foreach ($member_types as $type) {
				if (!empty($_POST["{$type}_member_gender"])) {
					$this->session->set_userdata("{$type}_member_status_type", $_POST["{$type}_member_gender"]);
					$this->session->set_userdata("{$type}_filter_status", $_POST["{$type}_filter_status"]);
					$this->session->set_userdata("{$type}_member_profile_image", $_POST["{$type}_member_profile_image"]);
				}
			}


			$member_approval = $this->db->get_where('general_settings', array('type' => 'member_approval_by_admin'))->row()->value;
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($this->session->flashdata('alert') == "block") {
				$page_data['danger_alert'] = translate("you_have_successfully_blocked_this_member!");
			} elseif ($this->session->flashdata('alert') == "unblock") {
				$page_data['success_alert'] = translate("you_have_successfully_unlocked_this_member!");
			} elseif ($this->session->flashdata('alert') == "delete") {
				$page_data['success_alert'] = translate("this_member_is_moved_to_deleted_member_list!");
			} elseif ($this->session->flashdata('alert') == "failed_delete") {
				$page_data['danger_alert'] = translate("failed_to_delete_this_member!");
			} elseif ($this->session->flashdata('alert') == "member_approval") {
				$page_data['success_alert'] = translate("you_have_successfully_approved_this_member!");
			} elseif ($this->session->flashdata('alert') == "demo_msg") {
				$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
			}







			if ($para2 == "list_data") {
				if ($para1 == "free_members") {
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
                            3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					}
				} 
				elseif ($para1 == "premium_members") {
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
                            3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					}
				}
				
				elseif ($para1 == "national_members") {
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
                            3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					}
				}
				elseif ($para1 == "guest_members") {
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
                            3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					}
				}
				elseif ($para1 == "ngb_members") {
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
                            3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
							7 => 'spiritual_and_social_background',
							8 => 'sub_caste_name',
						);
					}
				}

				$limit = $this->input->post('length');
				$start = $this->input->post('start');

				if ($this->input->post('order')[0]['column'] == 0) {
					$order = 'member_id';
					$dir = 'desc';
				} else {
					$order = $columns[$this->input->post('order')[0]['column']];
					$dir = $this->input->post('order')[0]['dir'];
				}

				if ($para1 == "free_members") {
					$member_type = 1;
				}
				 elseif ($para1 == "premium_members") {
					$member_type = 2;
				}
				 elseif ($para1 == "national_members") {
					$member_type = 3;
				}
				 elseif ($para1 == "ngb_members") {
					$member_type = 4;
				}
				 elseif ($para1 == "guest_members") {
					$member_type = 0;
				}
				

				$totalData = $this->Crud_model->allmembers_count($member_type);

				$totalFiltered = $totalData;

				if (empty($this->input->post('search')['value'])) {
					 
					$members = $this->Crud_model->allmembers($member_type, $limit, $start, $order, $dir);
				} else {  
					$search = $this->input->post('search')['value'];

					$members =  $this->Crud_model->members_search($member_type, $limit, $start, $search, $order, $dir);

					$totalFiltered = $this->Crud_model->members_search_count($member_type, $search);
				}

				$data = array();
				if (!empty($members)) {
					// if ($dir == 'asc') { $i = $start + 1; } elseif ($dir == 'desc') { $i = $totalFiltered - $start; }
					foreach ($members as $member) {

						$image = json_decode($member->profile_image, true);
						$basic_info = json_decode($member->spiritual_and_social_background, true);  
						$nestedData['caste'] =$basic_info[0]['caste'];
						$nestedData['sub_caste']=$basic_info[0]['sub_caste'];
						if(!empty($basic_info[0]['caste'])){
							$page_data['caste'] = $this->db->get_where("caste", array("caste_id" => $basic_info[0]['caste']))->row_array();
						$nestedData['caste'] =$page_data['caste']['caste_name'];	 	
						//print_r($page_data);exit;	
						}
						if(!empty($basic_info[0]['sub_caste'])){
							$page_data['get_sub_caste'] = $this->db->get_where("sub_caste", array("sub_caste_id" => $basic_info[0]['sub_caste']))->row_array(); 
							$nestedData['sub_caste']=	$page_data['get_sub_caste']['sub_caste_name'];
						//print_r($page_data);exit;	
						} 
						//         		if (file_exists('uploads/profile_image/'.$image[0]['thumb'])) {
						// 			$member_image="<img src='".base_url()."uploads/profile_image/".$image[0]['thumb']."' class='img-sm'>";
						// 		}
						// 		else {
						// 			$member_image="<img src='".base_url()."uploads/profile_image/male_default.jpg' class='img-sm'>";
						// 		}
						if (file_exists('uploads/profile_image/' . $image[0]['profile_image'])) {

							if ($image[0]['profile_image'] == 'male_default_thumb.jpg') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/male_default.jpg' class='img-sm'>";
							} else if ($image[0]['profile_image'] == 'female_default_thumb.png') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/female_default.png' class='img-sm'>";
							} else {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/" . $image[0]['profile_image'] . "' class='img-sm'>";
							}
						} else {
							if ($member->gender == '1') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/male_default.jpg' class='img-sm'>";
							} else if ($member->gender == '2') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/female_default.png' class='img-sm'>";
							}
						}
						$present_address = json_decode($member->present_address, true);
						$permanent_address = json_decode($member->permanent_address, true);
						$nakshatra = json_decode($member->astronomic_information, true);

						if (!empty($present_address[0]['city'])) {
							$address = $this->Crud_model->get_type_name_by_id('city', $present_address[0]['city']);
						} elseif (!empty($permanent_address[0]['permanent_city'])) {
							$address = $this->Crud_model->get_type_name_by_id('city', $permanent_address[0]['permanent_city']);
						} elseif (empty($permanent_address[0]['permanent_city']) && empty($present_address[0]['city'])) {
							$address = "no address";
						}
						if ($member->is_blocked == "yes") {
							$block_button = "<button data-target='#block_modal' data-toggle='modal' class='btn btn-success btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title= '" . translate('unblock') . "' onclick='block(\"" . $member->is_blocked . "\", " . $member->member_id . ")'><i class='fa fa-check'></i></button>";
						} elseif ($member->is_blocked == "no") {
							$block_button = "<button data-target='#block_modal' data-toggle='modal' class='btn btn-dark btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('block') . "' onclick='block(\"" . $member->is_blocked . "\", " . $member->member_id . ")'><i class='fa fa-ban'></i></button>";
						}
						if ($member->is_closed == "yes") {
							$acnt_status_button = "<center><span class='badge badge-danger' style='width:60px'>" . translate('closed') . "</span></center>";
						} elseif ($member->is_closed == "no") {
							$acnt_status_button = "<center><span class='badge badge-success' style='width:60px'>" . translate('Active') . "</span></center>";
						}
						if ($member_approval == 'yes') {
							if ($member->status == "pending") {
								$status_button = "<button data-target='#status_modal' data-toggle='modal' class='btn btn-info btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title= '" . translate('approve') . "' onclick='status(\"" . $member->status . "\", " . $member->member_id . ")'><i class='fa fa-hand-pointer-o'></i></button>";
							} elseif ($member->status == "approved") {
								$status_button = "<button  data-toggle='modal' class='btn btn-success btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title= '" . translate('approved') . "' ><i class='fa fa-thumbs-up'></i></button>";
							}
						} else {
							$status_button = '';
						}
						$nestedData['image'] = $member_image;
						$nestedData['address'] = $address;
						$nestedData['nakshatra'] = $nakshatra[0]['moon_sign'];
						$nestedData['percentage'] = $member->percentage;
						$nestedData['name'] = $member->first_name . ' ' . $member->last_name;
						if ($member->status == "pending") {
							$nestedData['status'] = "<button  data-toggle='modal' class='badge badge-info' >" . translate('pending') . "</button>";
						} elseif ($member->status == "approved") {
							$nestedData['status'] = "<button   class='badge badge-success' >" . translate('approved') . "</i></button>";
						}
						$nestedData['member_id'] = $member->member_profile_id;
						$nestedData['follower'] =  $member->follower;
						$nestedData['profile_reported'] = $member->reported_by;
						// $nestedData['sub_caste_name'] = $member->sub_caste_name;

						if ($para1 == "premium_members") {
							$package_info = $this->db->get_where('member', array('member_id' => $member->member_id))->row()->package_info;
							$package_info = json_decode($package_info, true);
							$nestedData['package'] = $package_info[0]['current_package'];
						}
						$nestedData['member_since'] = date('d/m/Y h:i:s A', strtotime($member->member_since));
						$nestedData['member_status'] = $acnt_status_button;
						$nestedData['options'] = "<a href='" . base_url() . "admin/members/" . $para1 . "/view_member/" . $member->member_id . "' id='demo-dt-view-btn' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('view_profile' ) . "' ><i class='fa fa-eye'></i></a>
						 <a href='" . base_url() . "admin/members/" . $para1 . "/edit_member/" . $member->member_id . "' id='demo-dt-edit-btn' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('edit_profile') . "' ><i class='fa fa-edit'></i></a>
			                <a href='" . base_url() . "admin/members/" . $para1 . "/print_member/" . $member->member_id . "' id='demo-dt-pdf-btn' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('Generate_Pdf') . "' ><i class='fa fa-file-pdf-o'></i></a>			
									
									
									<a href='#' id='demo-dt-delete-btn' data-target='#package_modal' data-toggle='modal' class='btn btn-info btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('packages') . "' onclick='view_package(" . $member->member_id . ")'><i class='fa fa-object-ungroup'></i></a> " . $block_button . "<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete_member') . "' onclick='delete_member(" . $member->member_id . ")'><i class='fa fa-trash'></i></button>" . $status_button . "";
						$data[] = $nestedData;
						// if ($dir == 'asc') { $i++; } elseif ($dir == 'desc') { $i--; }
					}
				}
				 //echo '<pre>';print_r($data);exit;
				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			}
			// code writtten by gowda






			else if ($para2 == "star_list_data") {
				if ($para1 == "free_members") {
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							// 4 => 'follower',
							// 5 => 'reported_by',
							4 => 'member_since',
							5 => 'spiritual_and_social_background',
							6 => 'sub_caste_name',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							// 3 => 'follower',
							// 4 => 'reported_by',
							3 => 'member_since',
							4 => 'spiritual_and_social_background',
							5 => 'sub_caste_name',
						);
					}
				} elseif ($para1 == "premium_members") {
					log_message("info","list of premium members");
					if ($member_approval == 'yes') {
						log_message("info","list of premium members");
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'follower',
							4 => 'reported_by',
							5 => 'member_since',
						);
					}
				
				} elseif ($para1 == "guest_members") {
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'follower',
							4 => 'reported_by',
							5 => 'member_since',
						);
					}
				}
				elseif ($para1 == "ngb_members") {
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'follower',
							4 => 'reported_by',
							5 => 'member_since',
						);
					}
				}
				elseif ($para1 == "national_members") {
					log_message("info","list of national members");
					if ($member_approval == 'yes') {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'status',
							4 => 'follower',
							5 => 'reported_by',
							6 => 'member_since',
						);
					} else {
						$columns = array(
							0 => '',
							1 => 'member_profile_id',
							2 => 'first_name',
							3 => 'follower',
							4 => 'reported_by',
							5 => 'member_since',
						);
					}
				}
				$nakshatra = $this->input->post('nakshatra');
				$nakshatra_gender = $this->input->post('gender');
			
				
				$limit = $this->input->post('length');
				$start = $this->input->post('start');

				if ($this->input->post('order')[0]['column'] == 0) {
					$order = 'member_id';
					$dir = 'desc';
				} else {
					$order = $columns[$this->input->post('order')[0]['column']];
					$dir = $this->input->post('order')[0]['dir'];
				}

				if ($para1 == "free_members") {
					$member_type = 1;
				} 
				elseif ($para1 == "premium_members") {
					$member_type = 2;
				}
				elseif ($para1 == "guest_members") {
					$member_type = 0;
				}
				elseif ($para1 == "ngb_members") {
					$member_type = 4;
				}
				elseif ($para1 == "national_members") {
					$member_type = 3;
				}

				$totalData = $this->Crud_model->star_allmembers_count($member_type, $limit, $start, $order, $dir,$nakshatra,$nakshatra_gender);

				$totalFiltered = $totalData;

				if (empty($this->input->post('search')['value'])) {

					$members = $this->Crud_model->allmembers_starfilter($member_type, $limit, $start, $order, $dir,$nakshatra,$nakshatra_gender);
				} else {
					$search = $this->input->post('search')['value'];

					$members =  $this->Crud_model->star_members_search($member_type, $limit, $start, $search, $order, $dir,$nakshatra,$nakshatra_gender);

					$totalFiltered = $this->Crud_model->star_members_search_count($member_type, $limit, $start, $search, $order, $dir,$nakshatra,$nakshatra_gender);
				}

				$data = array();
				if (!empty($members)) {
					foreach ($members as $member) {
						$image = json_decode($member->profile_image, true);

						// added by divya

						$basic_info = json_decode($member->spiritual_and_social_background, true);  
						$nestedData['caste'] =$basic_info[0]['caste'];
						$nestedData['sub_caste']=$basic_info[0]['sub_caste'];

						if(!empty($basic_info[0]['caste'])){
							$page_data['caste'] = $this->db->get_where("caste", array("caste_id" => $basic_info[0]['caste']))->row_array();
						$nestedData['caste'] =$page_data['caste']['caste_name'];	 	
						//print_r($page_data);exit;	
						}
						if(!empty($basic_info[0]['sub_caste'])){
							$page_data['get_sub_caste'] = $this->db->get_where("sub_caste", array("sub_caste_id" => $basic_info[0]['sub_caste']))->row_array(); 
							$nestedData['sub_caste']=	$page_data['get_sub_caste']['sub_caste_name'];
						//print_r($page_data);exit;	
						} 

						if (file_exists('uploads/profile_image/' . $image[0]['thumb'])) {

							if ($image[0]['thumb'] == 'male_default_thumb.jpg') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/male_default.jpg' class='img-sm'>";
							} else if ($image[0]['thumb'] == 'female_default_thumb.png') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/female_default.png' class='img-sm'>";
							} else {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/" . $image[0]['thumb'] . "' class='img-sm'>";
							}
						} else {
							if ($member->gender == '1') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/male_default.jpg' class='img-sm'>";
							} else if ($member->gender == '2') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/female_default.png' class='img-sm'>";
							}
						}
						$present_address = json_decode($member->present_address, true);
						$permanent_address = json_decode($member->permanent_address, true);
						$nakshatra = json_decode($member->astronomic_information, true);

						if (!empty($present_address[0]['city'])) {
							$address = $this->Crud_model->get_type_name_by_id('city', $present_address[0]['city']);
						} elseif (!empty($permanent_address[0]['permanent_city'])) {
							$address = $this->Crud_model->get_type_name_by_id('city', $permanent_address[0]['permanent_city']);
						} elseif (empty($permanent_address[0]['permanent_city']) && empty($present_address[0]['city'])) {
							$address = "no address";
						}
						if ($member->is_blocked == "yes") {
							$block_button = "<button data-target='#block_modal' data-toggle='modal' class='btn btn-success btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title= '" . translate('unblock') . "' onclick='block(\"" . $member->is_blocked . "\", " . $member->member_id . ")'><i class='fa fa-check'></i></button>";
						} elseif ($member->is_blocked == "no") {
							$block_button = "<button data-target='#block_modal' data-toggle='modal' class='btn btn-dark btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('block') . "' onclick='block(\"" . $member->is_blocked . "\", " . $member->member_id . ")'><i class='fa fa-ban'></i></button>";
						}
						if ($member->is_closed == "yes") {
							$acnt_status_button = "<center><span class='badge badge-danger' style='width:60px'>" . translate('closed') . "</span></center>";
						} elseif ($member->is_closed == "no") {
							$acnt_status_button = "<center><span class='badge badge-success' style='width:60px'>" . translate('Active') . "</span></center>";
						}
						if ($member_approval == 'yes') {
							if ($member->status == "pending") {
								$status_button = "<button data-target='#status_modal' data-toggle='modal' class='btn btn-info btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title= '" . translate('approve') . "' onclick='status(\"" . $member->status . "\", " . $member->member_id . ")'><i class='fa fa-hand-pointer-o'></i></button>";
							} elseif ($member->status == "approved") {
								$status_button = "<button  data-toggle='modal' class='btn btn-success btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title= '" . translate('approved') . "' ><i class='fa fa-thumbs-up'></i></button>";
							}
						} else {
							$status_button = '';
						}
						$nestedData['image'] = $member_image;
						$nestedData['address'] = $address;
						$nestedData['nakshatra'] = $nakshatra[0]['moon_sign'];
						$nestedData['percentage'] = $member->percentage;
						$nestedData['name'] = $member->first_name . ' ' . $member->last_name;
						if ($member->status == "pending") {
							$nestedData['status'] = "<button  data-toggle='modal' class='badge badge-info' >" . translate('pending') . "</button>";
						} elseif ($member->status == "approved") {
							$nestedData['status'] = "<button   class='badge badge-success' >" . translate('approved') . "</i></button>";
						}
						$nestedData['member_id'] = $member->member_profile_id;
						$nestedData['follower'] =  $member->follower;
						$nestedData['profile_reported'] = $member->reported_by;

						if ($para1 == "premium_members") {
							$package_info = $this->db->get_where('member', array('member_id' => $member->member_id))->row()->package_info;
							$package_info = json_decode($package_info, true);
							$nestedData['package'] = $package_info[0]['current_package'];
						}
						$nestedData['member_since'] = date('d/m/Y h:i:s A', strtotime($member->member_since));
						$nestedData['member_status'] = $acnt_status_button;
						$nestedData['options'] = "<a href='" . base_url() . "admin/members/" . $para1 . "/view_member/" . $member->member_id . "' id='demo-dt-view-btn' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('view_profile') . "' ><i class='fa fa-eye'></i></a>";
						$data[] = $nestedData;
						// if ($dir == 'asc') { $i++; } elseif ($dir == 'desc') { $i--; }
					}
				}
			
				
				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			}
			// code ended for star matching







			elseif ($para1 == "free_members") {
				if ($para2 == "") {
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "index.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_free_members'] = $this->db->get_where("member", array("membership" => 1))->result();
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				} elseif ($para2 == "view_member") {
					$page_data['top'] 		= "members/members.php";
					$page_data['folder'] 	= "members";
					$page_data['file'] 		= "view_member.php";
					$page_data['bottom'] 	= "members/members.php";
					$page_data['get_free_member_by_id'] = $this->db->get_where("member", array("membership" => 1, "member_id" => $para3))->result();
				} elseif ($para2 == "edit_member") {
					$page_data['areas'] = $this->Crud_model->get_all_areas();
					$page_data['top'] 		= "members/members.php";
					$page_data['folder'] 	= "members";
					$page_data['file']	 	= "edit_member.php";
					$page_data['bottom'] 	= "members/members.php";
					$page_data['get_free_member_by_id'] = $this->db->get_where("member", array("membership" => 1, "member_id" => $para3))->result();
				} elseif ($para2 == "print_member") {
					$this->load->library('pdf');
					$page_data['get_free_member_by_id'] = $this->db->get_where("member", array("membership" => 1, "member_id" => $para3))->result();
					$page_data['member_type'] = "Visitors";
					$page_data['parameter'] 	= "free_members";
					$page_data['page_name'] 	= "free_members";
					$this->load->view('back/members/print_member', $page_data);
					$html = $this->output->get_output();
					$dompdf = new pdf();
					$dompdf->loadHtml($html);
					$dompdf->set_option('isRemoteEnabled', TRUE);
					$dompdf->render();
					$fileName = $page_data['get_free_member_by_id'][0]->first_name . " " . $page_data['get_free_member_by_id'][0]->last_name . " " . "Member Details";
					$dompdf->stream($fileName . ".pdf", array("Attachment" => 0));
				} 
				elseif ($para2 == "starmatching") {
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "star_matching.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_free_members'] = $this->db->get_where("member", array("membership" => 1, "member_id" => $para3))->result();
					foreach ($page_data['get_free_members'] as $get_free_members) {
						$page_data['nakshatra'] = json_decode($get_free_members->astronomic_information, true);
		
					}
					// $page_data['nakshatra'] = $page_data['nakshatra'][0]['moon_sign'];
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				}




				$page_data['member_type'] = "Visitors";
				$page_data['parameter'] 	= "free_members";
				$page_data['page_name'] 	= "free_members";
				$this->load->view('back/index', $page_data);
			} 
			elseif ($para1 == "premium_members") {
				
				if ($para2 == "") {
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "index.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_premium_members'] = $this->db->get_where("member", array("membership" => 2))->result();
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				} elseif ($para2 == "view_member") {
					$page_data['top'] = "members/members.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "view_member.php";
					$page_data['bottom'] = "members/members.php";
					$page_data['get_premium_member_by_id'] = $this->db->get_where("member", array("membership" => 2, "member_id" => $para3))->result();
				} elseif ($para2 == "edit_member") {
					$page_data['areas'] = $this->Crud_model->get_all_areas();
					$page_data['top'] 		= "members/members.php";
					$page_data['folder'] 	= "members";
					$page_data['file']	 	= "edit_member.php";
					$page_data['bottom'] 	= "members/members.php";
					$page_data['get_premium_member_by_id'] = $this->db->get_where("member", array("membership" => 2, "member_id" => $para3))->result();
				} elseif ($para2 == "print_member") {
					$this->load->library('pdf');
					$page_data['get_premium_member_by_id'] = $this->db->get_where("member", array("membership" => 2, "member_id" => $para3))->result();
					$page_data['member_type'] = "Legions";
					$page_data['parameter'] 	= "premium_members";
					$page_data['page_name'] 	= "premium_members";
					$this->load->view('back/members/print_member', $page_data);
					$html = $this->output->get_output();
					$dompdf = new pdf();
					$dompdf->loadHtml($html);
					$dompdf->set_option('isRemoteEnabled', TRUE);
					$dompdf->render();
					$fileName = $page_data['get_premium_member_by_id'][0]->first_name . " " . $page_data['get_premium_member_by_id'][0]->last_name . " " . "Member Details";
					$dompdf->stream($fileName . ".pdf", array("Attachment" => 0));
				}
				elseif ($para2 == "starmatching") {
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "premium_star_matching.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_premium_members'] = $this->db->get_where("member", array("membership" => 2, "member_id" => $para3))->result();
					foreach ($page_data['get_premium_members'] as $get_premium_members) {
						$page_data['nakshatra'] = json_decode($get_premium_members->astronomic_information, true);
		
					}
					
					// $page_data['nakshatra'] = $page_data['nakshatra'][0]['moon_sign'];
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				}




				$page_data['member_type'] = "Legions";
				$page_data['parameter'] = "premium_members";
				$page_data['page_name'] = "premium_members";
				$this->load->view('back/index', $page_data);
			} 
			
			elseif ($para1 == "national_members") {
				if ($para2 == "") {

					log_message("info","National Members entry point of funcitons");
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "index.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_premium_members'] = $this->db->get_where("member", array("membership" => 3))->result();
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				} elseif ($para2 == "view_member") {
					$page_data['top'] = "members/members.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "view_member.php";
					$page_data['bottom'] = "members/members.php";
					$page_data['get_national_member_by_id'] = $this->db->get_where("member", array("membership" => 3, "member_id" => $para3))->result();
				} elseif ($para2 == "edit_member") {
					$page_data['areas'] = $this->Crud_model->get_all_areas();
					$page_data['top'] 		= "members/members.php";
					$page_data['folder'] 	= "members";
					$page_data['file']	 	= "edit_member.php";
					$page_data['bottom'] 	= "members/members.php";
					$page_data['get_national_member_by_id'] = $this->db->get_where("member", array("membership" => 3, "member_id" => $para3))->result();
				} elseif ($para2 == "print_member") {
					$this->load->library('pdf');
					$page_data['get_premium_member_by_id'] = $this->db->get_where("member", array("membership" => 3, "member_id" => $para3))->result();
					$page_data['member_type'] = "National";
					$page_data['parameter'] 	= "national_members";
					$page_data['page_name'] 	= "national_members";
					$this->load->view('back/members/print_member', $page_data);
					$html = $this->output->get_output();
					$dompdf = new pdf();
					$dompdf->loadHtml($html);
					$dompdf->set_option('isRemoteEnabled', TRUE);
					$dompdf->render();
					$fileName = $page_data['get_premium_member_by_id'][0]->first_name . " " . $page_data['get_premium_member_by_id'][0]->last_name . " " . "Member Details";
					$dompdf->stream($fileName . ".pdf", array("Attachment" => 0));
				}
				elseif ($para2 == "starmatching") {
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "premium_star_matching.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_premium_members'] = $this->db->get_where("member", array("membership" => 3, "member_id" => $para3))->result();
					foreach ($page_data['get_premium_members'] as $get_premium_members) {
						$page_data['nakshatra'] = json_decode($get_premium_members->astronomic_information, true);
		
					}
					
					// $page_data['nakshatra'] = $page_data['nakshatra'][0]['moon_sign'];
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				}




				$page_data['member_type'] = "National";
				$page_data['parameter'] = "national_members";
				$page_data['page_name'] = "national_members";
				$this->load->view('back/index', $page_data);
			}
			elseif ($para1 == "guest_members") {
				if ($para2 == "") {
					log_message("info","Guest Members entry point of funcitons");
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "index.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_premium_members'] = $this->db->get_where("member", array("membership" => 0))->result();
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				} elseif ($para2 == "view_member") {
					$page_data['top'] = "members/members.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "view_member.php";
					$page_data['bottom'] = "members/members.php";
					$page_data['get_guest_member_by_id'] = $this->db->get_where("member", array("membership" => 0, "member_id" => $para3))->result();
				} elseif ($para2 == "edit_member") {
					$page_data['areas'] = $this->Crud_model->get_all_areas();
					$page_data['top'] 		= "members/members.php";
					$page_data['folder'] 	= "members";
					$page_data['file']	 	= "edit_member.php";
					$page_data['bottom'] 	= "members/members.php";
					$page_data['get_guest_member_by_id'] = $this->db->get_where("member", array("membership" => 0, "member_id" => $para3))->result();
				} elseif ($para2 == "print_member") {
					$this->load->library('pdf');
					$page_data['get_premium_member_by_id'] = $this->db->get_where("member", array("membership" => 0, "member_id" => $para3))->result();
					$page_data['member_type'] = "guest";
					$page_data['parameter'] = "guest_members";
					$page_data['page_name'] = "guest_members";
					$this->load->view('back/members/print_member', $page_data);
					$html = $this->output->get_output();
					$dompdf = new pdf();
					$dompdf->loadHtml($html);
					$dompdf->set_option('isRemoteEnabled', TRUE);
					$dompdf->render();
					$fileName = $page_data['get_premium_member_by_id'][0]->first_name . " " . $page_data['get_premium_member_by_id'][0]->last_name . " " . "Member Details";
					$dompdf->stream($fileName . ".pdf", array("Attachment" => 0));
				}
				elseif ($para2 == "starmatching") {
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "premium_star_matching.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_premium_members'] = $this->db->get_where("member", array("membership" => 0, "member_id" => $para3))->result();
					foreach ($page_data['get_premium_members'] as $get_premium_members) {
						$page_data['nakshatra'] = json_decode($get_premium_members->astronomic_information, true);
		
					}
					
					// $page_data['nakshatra'] = $page_data['nakshatra'][0]['moon_sign'];
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				}




				$page_data['member_type'] = "Guest";
				$page_data['parameter'] = "guest_members";
				$page_data['page_name'] = "guest_members";
				$this->load->view('back/index', $page_data);
			}
			elseif ($para1 == "ngb_members") {
				if ($para2 == "") {
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "index.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_premium_members'] = $this->db->get_where("member", array("membership" => 4))->result();
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				} elseif ($para2 == "view_member") {
					$page_data['top'] = "members/members.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "view_member.php";
					$page_data['bottom'] = "members/members.php";
					$page_data['get_ngb_member_by_id'] = $this->db->get_where("member", array("membership" => 4, "member_id" => $para3))->result();
				} elseif ($para2 == "edit_member") {
					$page_data['areas'] = $this->Crud_model->get_all_areas();
					$page_data['top'] 		= "members/members.php";
					$page_data['folder'] 	= "members";
					$page_data['file']	 	= "edit_member.php";
					$page_data['bottom'] 	= "members/members.php";
					$page_data['get_ngb_member_by_id'] = $this->db->get_where("member", array("membership" => 4, "member_id" => $para3))->result();
				} elseif ($para2 == "print_member") {
					$this->load->library('pdf');
					$page_data['get_premium_member_by_id'] = $this->db->get_where("member", array("membership" => 4, "member_id" => $para3))->result();
					$page_data['member_type'] = "Ngb";
					$page_data['parameter'] 	= "ngb_member";
					$page_data['page_name'] 	= "ngb_member";
					$this->load->view('back/members/print_member', $page_data);
					$html = $this->output->get_output();
					$dompdf = new pdf();
					$dompdf->loadHtml($html);
					$dompdf->set_option('isRemoteEnabled', TRUE);
					$dompdf->render();
					$fileName = $page_data['get_premium_member_by_id'][0]->first_name . " " . $page_data['get_premium_member_by_id'][0]->last_name . " " . "Member Details";
					$dompdf->stream($fileName . ".pdf", array("Attachment" => 0));
				}
				elseif ($para2 == "starmatching") {
					$page_data['top'] = "members/index.php";
					$page_data['folder'] = "members";
					$page_data['file'] = "premium_star_matching.php";
					$page_data['bottom'] = "members/index.php";
					$page_data['get_premium_members'] = $this->db->get_where("member", array("membership" => 4, "member_id" => $para3))->result();
					foreach ($page_data['get_premium_members'] as $get_premium_members) {
						$page_data['nakshatra'] = json_decode($get_premium_members->astronomic_information, true);
		
					}
					
					// $page_data['nakshatra'] = $page_data['nakshatra'][0]['moon_sign'];
					if ($this->session->flashdata('alert') == "edit") {
						$page_data['success_alert'] = translate("you_have_successfully_edited_the_profile!");
					} elseif ($this->session->flashdata('alert') == "upgrade") {
						$page_data['success_alert'] = translate("you_have_successfully_upgraded_the_member_package!");
					}
				}




				$page_data['member_type'] = "Ngb";
				$page_data['parameter'] = "ngb_members";
				$page_data['page_name'] = "ngb_members";
				$this->load->view('back/index', $page_data);
			}
			 elseif ($para1 == "add_member") {
				if ($para2 == "") {
					$page_data['top'] 		= "members/index.php";
					$page_data['folder'] 	= "members";
					$page_data['file']	 	= "add_member.php";
					$page_data['bottom'] 	= "members/index.php";
					$page_data['page_name'] = "add_member";
					$page_data['areas'] = $this->Crud_model->get_all_areas();
					if ($this->session->flashdata('alert') == "add") {
						$page_data['success_alert'] = translate("you_have_successfully_added_a_member!!");
					} elseif ($this->session->flashdata('alert') == "add_fail") {
						$page_data['danger_alert'] = translate("member_registration_failed!");
					}
					$this->load->view('back/index', $page_data);
				} elseif ($para2 == "do_add") {
					$this->form_validation->set_rules('fname', 'First Name', 'required');
					$this->form_validation->set_rules('lname', 'Last Name', 'required');
					$this->form_validation->set_rules('gender', 'Gender', 'required');
					//$this->form_validation->set_rules('on_behalf', 'On Behalf', 'required');
					$this->form_validation->set_rules('plan', 'Plan', 'required');
					$this->form_validation->set_rules('email', 'Email', 'required|is_unique[member.email]', array('required' => 'The %s is required.', 'is_unique' => 'This %s already exists.'));
					$this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'required');
					$this->form_validation->set_rules('mobile', 'Mobile Number', 'required');
					$this->form_validation->set_rules('password', 'Password', 'required');
					$this->form_validation->set_rules('cpassword', 'Confirm Password', 'required|matches[password]');
					if ($this->form_validation->run() == FALSE) {
						$page_data['top'] 		= "members/index.php";
						$page_data['folder'] 	= "members";
						$page_data['file']	 	= "add_member.php";
						$page_data['bottom'] 	= "members/index.php";
						$page_data['page_name'] = "add_member";
						$page_data['form_error'] = "yes";
						$page_data['form_contents'] = $this->input->post();
						$this->session->set_flashdata('alert', 'add_fail');
						$this->load->view('back/index', $page_data);
					} else {

						// ------------------------------------Profile Image------------------------------------ //
						// $profile_image[] = array('profile_image'    =>  'male_default.jpg',
						//                             'thumb'         =>  'male_default_thumb.jpg'
						//                     );

						if ($_POST['gender'] == '1') {
							$profile_image[] = array(
								'profile_image'    =>  'male_default.jpg',
								'thumb'         =>  'male_default_thumb.jpg'
							);
						} else if ($_POST['gender'] == '2') {
							$profile_image[] = array(
								'profile_image'    =>  'female_default.png',
								'thumb'         =>  'female_default_thumb.png'
							);
						}
						$profile_image = json_encode($profile_image);
						// ------------------------------------Profile Image------------------------------------ //

						// ------------------------------------Basic Info------------------------------------ //
						$basic_info[] = array(
							'age'                 => '',
							'marital_status'        => '',
							'number_of_children'    => '',
							'area'                  => '',
							'on_behalf'             => $this->input->post('on_behalf')
						);
						$basic_info = json_encode($basic_info);
						// ------------------------------------Basic Info------------------------------------ //

						// ------------------------------------Present Address------------------------------------ //
						$present_address[] = array(
							'country'        => '',
							'city'                  => '',
							'state'                 => '',
							'postal_code'           => ''
						);
						$present_address = json_encode($present_address);
						// ------------------------------------Present Address------------------------------------ //

						// ------------------------------------Education & Career------------------------------------ //
						$education_and_career[] = array(
							'highest_education' => '',
							'occupation'                    => '',
							'annual_income'                 => ''
						);
						$education_and_career = json_encode($education_and_career);
						// ------------------------------------Education & Career------------------------------------ //

						// ------------------------------------ Physical Attributes------------------------------------ //
						$physical_attributes[] = array(
							'weight'     => '',
							'eye_color'             => '',
							'hair_color'            => '',
							'complexion'            => '',
							'blood_group'           => '',
							'body_type'             => '',
							'body_art'              => '',
							'any_disability'        => ''
						);
						$physical_attributes = json_encode($physical_attributes);
						// ------------------------------------ Physical Attributes------------------------------------ //

						// ------------------------------------ Language------------------------------------ //
						$language[] = array(
							'mother_tongue'         => '',
							'language'              => '',
							'speak'                 => '',
							'read'                  => ''
						);
						$language = json_encode($language);
						// ------------------------------------ Language------------------------------------ //

						// ------------------------------------Hobbies & Interest------------------------------------ //
						$hobbies_and_interest[] = array(
							'hobby'     => '',
							'interest'              => '',
							'music'                 => '',
							'books'                 => '',
							'movie'                 => '',
							'tv_show'               => '',
							'sports_show'           => '',
							'fitness_activity'      => '',
							'cuisine'               => '',
							'dress_style'           => ''
						);
						$hobbies_and_interest = json_encode($hobbies_and_interest);
						// ------------------------------------Hobbies & Interest------------------------------------ //

						// ------------------------------------ Personal Attitude & Behavior------------------------------------ //
						$personal_attitude_and_behavior[] = array(
							'affection'   => '',
							'humor'                 => '',
							'political_view'        => '',
							'religious_service'     => ''
						);
						$personal_attitude_and_behavior = json_encode($personal_attitude_and_behavior);
						// ------------------------------------ Personal Attitude & Behavior------------------------------------ //

						// ------------------------------------Residency Information------------------------------------ //
						$residency_information[] = array(
							'birth_country'    => '',
							'residency_country'     => '',
							'citizenship_country'   => '',
							'grow_up_country'       => '',
							'immigration_status'    => ''
						);
						$residency_information = json_encode($residency_information);
						// ------------------------------------Residency Information------------------------------------ //

						// ------------------------------------Spiritual and Social Background------------------------------------ //
						$spiritual_and_social_background[] = array(
							'religion'   => '',
							'caste'                 => '',
							'sub_caste'             => '',
							'ethnicity'             => '',
							'u_manglik'             => '',
							'personal_value'        => '',
							'family_value'          => '',
							'community_value'       => '',
							'family_status'          =>  ''
						);
						$spiritual_and_social_background = json_encode($spiritual_and_social_background);
						// ------------------------------------Spiritual and Social Background------------------------------------ //

						// ------------------------------------ Life Style------------------------------------ //
						$life_style[] = array(
							'diet'                => '',
							'drink'                 => '',
							'smoke'                 => '',
							'living_with'           => ''
						);
						$life_style = json_encode($life_style);
						// ------------------------------------ Life Style------------------------------------ //

						// ------------------------------------ Astronomic Information------------------------------------ //
						$astronomic_information[] = array(
							'sun_sign'    => '',
							'moon_sign'                 => $this->Crud_model->get_type_name_by_id('nakshtra', $this->input->post('nakshtra'), 'nakshtra_name'),
							'time_of_birth'             => '',
							'city_of_birth'             => ''
						);
						$astronomic_information = json_encode($astronomic_information);
						$data['nakshtra_id']=$this->input->post('nakshtra');
						// ------------------------------------ Astronomic Information------------------------------------ //

						// ------------------------------------Permanent Address------------------------------------ //
						$permanent_address[] = array(
							'permanent_country'    => '',
							'permanent_city'                => '',
							'permanent_state'               => '',
							'permanent_postal_code'         => ''
						);
						$permanent_address = json_encode($permanent_address);
						// ------------------------------------Permanent Address------------------------------------ //

						// ------------------------------------Family Information------------------------------------ //
						$family_info[] = array(
							'father'             => '',
							'mother'                => '',
							'brother_sister'        => ''
						);
						$family_info = json_encode($family_info);
						// ------------------------------------Family Information------------------------------------ //

						// --------------------------------- Additional Personal Details--------------------------------- //
						$additional_personal_details[] = array(
							'home_district'  => '',
							'family_residence'              => '',
							'fathers_occupation'            => '',
							'special_circumstances'         => ''
						);
						$additional_personal_details = json_encode($additional_personal_details);
						// --------------------------------- Additional Personal Details--------------------------------- //

						// ------------------------------------ Partner Expectation------------------------------------ //
						$partner_expectation[] = array(
							'general_requirement'    => '',
							'partner_age'                       => '',
							'partner_height'                    => '',
							'partner_weight'                    => '',
							'partner_marital_status'            => '',
							'with_children_acceptables'         => '',
							'partner_country_of_residence'      => '',
							'partner_religion'                  => '',
							'partner_caste'                     => '',
							'partner_sub_caste'                  => '',
							'partner_complexion'                => '',
							'partner_education'                 => '',
							'partner_profession'                => '',
							'partner_drinking_habits'           => '',
							'partner_smoking_habits'            => '',
							'partner_diet'                      => '',
							'partner_body_type'                 => '',
							'partner_personal_value'            => '',
							'manglik'                           => '',
							'partner_any_disability'            => '',
							'partner_mother_tongue'             => '',
							'partner_family_value'              => '',
							'prefered_country'                  => '',
							'prefered_state'                    => '',
							'prefered_status'                   => ''
						);
						$partner_expectation = json_encode($partner_expectation);
						// ------------------------------------ Partner Expectation------------------------------------ //

						// ------------------------------------Privacy Status------------------------------------ //
						$privacy_status[] = array(
							'present_address'                 => 'no',
							'education_and_career'            => 'no',
							'physical_attributes'             => 'no',
							'language'                        => 'no',
							'hobbies_and_interest'            => 'no',
							'personal_attitude_and_behavior'  => 'no',
							'residency_information'           => 'no',
							'spiritual_and_social_background' => 'no',
							'life_style'                      => 'no',
							'astronomic_information'          => 'no',
							'permanent_address'               => 'no',
							'family_info'                     => 'no',
							'additional_personal_details'     => 'no',
							'partner_expectation'             => 'yes'
						);
						$privacy_status = json_encode($privacy_status);
						// ------------------------------------Privacy Status------------------------------------ //

						// ------------------------------------Pic Privacy Status------------------------------------ //
						$pic_privacy[] = array(
							'profile_pic_show'        => 'all',
							'gallery_show'            => 'premium'

						);
						$data_pic_privacy = json_encode($pic_privacy);
						// ------------------------------------Pic Privacy Status------------------------------------ //

						// --------------------------------- Additional Personal Details--------------------------------- //
						$package_info[] = array(
							'current_package'   => $this->Crud_model->get_type_name_by_id('plan', $this->input->post('plan')),
							'package_price'     => $this->Crud_model->get_type_name_by_id('plan', $this->input->post('plan'), 'amount'),
							'payment_type'      => 'None',
						);
						$package_info = json_encode($package_info);
						// --------------------------------- Additional Personal Details--------------------------------- //


						$data['status'] = 'approved';
						$data['first_name'] = $this->input->post('fname');
						$data['last_name'] = $this->input->post('lname');
						$data['gender'] = $this->input->post('gender');
						$data['email'] = $this->input->post('email');
						$data['email_verification_status'] = '1';
						$data['date_of_birth'] = strtotime($this->input->post('date_of_birth'));
						$data['height'] = 0.00;
						$data['mobile'] = $this->input->post('mobile');
						$data['password'] = sha1($this->input->post('password'));
						$data['profile_image'] = $profile_image;
						$data['introduction'] = '';
						$data['basic_info'] = $basic_info;
						$data['present_address'] = $present_address;
						$data['family_info'] = $family_info;
						$data['education_and_career'] = $education_and_career;
						$data['physical_attributes'] = $physical_attributes;
						$data['language'] = $language;
						$data['hobbies_and_interest'] = $hobbies_and_interest;
						$data['personal_attitude_and_behavior'] = $personal_attitude_and_behavior;
						$data['residency_information'] = $residency_information;
						$data['spiritual_and_social_background'] = $spiritual_and_social_background;
						$data['life_style'] = $life_style;
						$data['astronomic_information'] = $astronomic_information;
						$data['permanent_address'] = $permanent_address;
						$data['additional_personal_details'] = $additional_personal_details;
						$data['partner_expectation'] = $partner_expectation;
						$data['interest'] = '[]';
						$data['short_list'] = '[]';
						$data['followed'] = '[]';
						$data['ignored'] = '[]';
						$data['ignored_by'] = '[]';
						$data['gallery'] = '[]';
						$data['happy_story'] = '[]';
						$data['package_info'] = $package_info;
						$data['payments_info'] = '[]';
						$data['interested_by'] = '[]';
						$data['follower'] = 0;
						$data['notifications'] = '[]';
						$plan = $this->input->post('plan');
						// if ($plan == 1) {
						// 	$data['membership'] = 1;
						// } else {
						// 	$data['membership'] = 2;
						// }
						$data['membership'] = 2;
						$data['profile_status'] = 1;
						$data['is_closed'] = 'no';
						$data['member_since'] = date("Y-m-d H:i:s");
						$data['express_interest'] = $this->db->get_where('plan', array('plan_id' => $plan))->row()->express_interest;
						$data['direct_messages'] = $this->db->get_where('plan', array('plan_id' => $plan))->row()->direct_messages;
						$data['photo_gallery'] = $this->db->get_where('plan', array('plan_id' => $plan))->row()->photo_gallery;
						$data['profile_completion'] = 0;
						$data['is_blocked'] = 'no';
						$data['privacy_status'] = $privacy_status;
						$data['pic_privacy'] = $data_pic_privacy;
						$data['report_profile'] = '[]';
						$data['area'] = $this->input->post('area');             // Area name (from hidden input)
						$data['area_id'] = $this->input->post('area_id');       // Area ID (from <select> value)

						$data['legion'] = $this->input->post('legion');         // Legion name (from hidden input)
						$data['legion_id'] = $this->input->post('legion_id');   // Legion ID (from <select> value)


						$this->db->insert('member', $data);
						$insert_id = $this->db->insert_id();
						// Generate prefix-based member profile ID



						if (!empty($data['legion_id'])) {
    $member_profile_id = $this->Crud_model->generate_member_profile_id($data['legion_id']);
    if (!$member_profile_id) {
        // Fallback to old method if generation fails
        $member_profile_id = strtoupper(substr(hash('sha512', rand()), 0, 8)) . $insert_id;
    }
} else {
    // Fallback for members without legion assignment
    $member_profile_id = 'GEN' . str_pad($insert_id, 3, '0', STR_PAD_LEFT);
}


						$this->db->where('member_id', $insert_id);
						$this->db->update('member', array('member_profile_id' => $member_profile_id));
						recache();

						// $msg = 'done';
						if ($this->Email_model->member_staff_account_opening_by_admin('member', $data['email'], $this->input->post('password')) == false) {
							//$msg = 'done_but_not_sent';
						} else {
							//$msg = 'done_and_sent';
						}
						// $msg = 'done';
						if ($this->Email_model->member_registration_email_to_admin($insert_id) == false) {
							//$msg = 'done_but_not_sent';
						} else {
							//$msg = 'done_and_sent';
						}

						$this->session->set_flashdata('alert', 'add');
						redirect(base_url() . 'admin/members/add_member', 'refresh');
					}
				}
			} 
			elseif ($para1 == "update_member") {
				$this->form_validation->set_rules('introduction', 'Introduction', 'required');

				$this->form_validation->set_rules('first_name', 'First Name', 'required');
				$this->form_validation->set_rules('last_name', 'Last Name', 'required');
				$this->form_validation->set_rules('gender', 'Gender', 'required');
				//$this->form_validation->set_rules('on_behalf', 'On Behalf', 'required');
				if ($this->input->post('old_email') != $this->input->post('email')) {
					$this->form_validation->set_rules('email', 'Email', 'required|is_unique[member.email]', array('required' => 'The %s is required.', 'is_unique' => 'This %s already exists.'));
				}
				if ($this->input->post('old_mobile') != $this->input->post('mobile')) {
					$this->form_validation->set_rules('mobile', 'Mobile', 'required|is_unique[member.mobile]', array('required' => 'The %s is required.', 'is_unique' => 'This %s already exists.'));
				}
				$this->form_validation->set_rules('marital_status', 'Marital Status', 'required');
				$this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'required');

				if ($this->db->get_where('frontend_settings', array('type' => 'present_address'))->row()->value == "yes") {
					$this->form_validation->set_rules('country', 'Country', 'required');
					$this->form_validation->set_rules('state', 'State', 'required');
				}

				if ($this->db->get_where('frontend_settings', array('type' => 'education_and_career'))->row()->value == "yes") {
					$this->form_validation->set_rules('highest_education', 'Highest Education', 'required');
					$this->form_validation->set_rules('occupation', 'Occupation', 'required');
				}

				if ($this->db->get_where('frontend_settings', array('type' => 'language'))->row()->value == "yes") {
					$this->form_validation->set_rules('mother_tongue', 'Mother Tongue', 'required');
				}

				if ($this->db->get_where('frontend_settings', array('type' => 'residency_information'))->row()->value == "yes") {
					$this->form_validation->set_rules('birth_country', 'Birth Country', 'required');
					$this->form_validation->set_rules('citizenship_country', 'Citizenship Country', 'required');
				}

				if ($this->db->get_where('frontend_settings', array('type' => 'spiritual_and_social_background'))->row()->value == "yes") {
					// $this->form_validation->set_rules('religion', 'Religion', 'required');
				}

				if ($this->db->get_where('frontend_settings', array('type' => 'permanent_address'))->row()->value == "yes") {
					$this->form_validation->set_rules('permanent_country', 'Permanent Country', 'required');
					$this->form_validation->set_rules('permanent_state', 'Permanent State', 'required');
				}

				if ($this->form_validation->run() == FALSE) {
					$page_data['top'] 		= "members/index.php";
					$page_data['folder'] 	= "members";
					$page_data['areas'] = $this->Crud_model->get_all_areas();
					$page_data['file']	 	= "edit_member.php";
					$page_data['bottom'] 	= "members/index.php";
					$page_data['page_name'] = "edit_member";
					$page_data['form_error'] = "yes";
					$page_data['form_contents'] = $this->input->post();
					
					$this->session->set_flashdata('alert', 'edit_fail');
					if ($para3 == 'premium_members') {
						$page_data['get_premium_member_by_id'] = $this->db->get_where("member", array("membership" => 2, "member_id" => $para2))->result();
						$page_data['member_type'] = "Premium";
						$page_data['parameter'] = "premium_members";
						$page_data['page_name'] = "premium_members";
					} elseif ($para3 == 'free_members') {
						$page_data['get_free_member_by_id'] = $this->db->get_where("member", array("membership" => 1, "member_id" => $para2))->result();
						$page_data['member_type'] = "Free";
						$page_data['parameter'] = "free_members";
						$page_data['page_name'] = "free_members";
					} elseif ($para3 == 'guest_members') {
						$page_data['get_guest_member_by_id'] = $this->db->get_where("member", array("membership" => 0, "member_id" => $para2))->result();
						$page_data['member_type'] = "Free";
						$page_data['parameter'] = "guest_members";
						$page_data['page_name'] = "guest_members";
					} elseif ($para3 == 'national_members') {
						$page_data['get_free_member_by_id'] = $this->db->get_where("member", array("membership" => 3, "member_id" => $para2))->result();
						$page_data['member_type'] = "National";
						$page_data['parameter'] = "national_members";
						$page_data['page_name'] = "national_members";
					} elseif ($para3 == 'ngb_members') {
						$page_data['get_ngb_member_by_id'] = $this->db->get_where("member", array("membership" => 4, "member_id" => $para2))->result();
						$page_data['member_type'] = "Ngb";
						$page_data['parameter'] = "ngb_members";
						$page_data['page_name'] = "ngb_members";
					}

					$this->load->view('back/index', $page_data);
				} else {
					$data['first_name'] = $this->input->post('first_name');
					$data['last_name'] = $this->input->post('last_name');
					$data['gender'] = $this->input->post('gender');
					if (!demo()) {
						$data['email'] = $this->input->post('email');
					}
					$data['mobile'] = $this->input->post('mobile');
					$data['date_of_birth'] = strtotime($this->input->post('date_of_birth'));
					$data['height'] = $this->input->post('height');
					$data['introduction'] = $this->input->post('introduction');
					$data['percentage'] = $this->input->post('percentage') ;
					// ------------------------------------Basic Info------------------------------------ //
					$basic_info[] = array(
						'marital_status'		=>	$this->input->post('marital_status'),
						'number_of_children'	=>	$this->input->post('number_of_children'),
						'area'					=>	$this->input->post('area'),
						'on_behalf'             =>  $this->input->post('on_behalf')
					);
					$data['basic_info'] = json_encode($basic_info);
					// ------------------------------------Basic Info------------------------------------ //

					// ------------------------------------Present Address------------------------------------ //
					$present_address[] = array(
						'country'		=>  $this->input->post('country'),
						'city'					=>	$this->input->post('city'),
						'state'					=>	$this->input->post('state'),
						'postal_code'			=>	$this->input->post('postal_code')
					);
					$data['present_address'] = json_encode($present_address);
					// ------------------------------------Present Address------------------------------------ //

					// ------------------------------------Education & Career------------------------------------ //
					$education_and_career[] = array(
						'highest_education'	=>  $this->input->post('highest_education'),
						'occupation'					=>	$this->input->post('occupation'),
						'annual_income'					=>	$this->input->post('annual_income')
					);
					$data['education_and_career'] = json_encode($education_and_career);
					// ------------------------------------Education & Career------------------------------------ //

					// ------------------------------------ Physical Attributes------------------------------------ //
					$physical_attributes[] = array(
						'weight'     =>	$this->input->post('weight'),
						'eye_color'				=>	$this->input->post('eye_color'),
						'hair_color'			=>	$this->input->post('hair_color'),
						'complexion'			=>	$this->input->post('complexion'),
						'blood_group'			=>	$this->input->post('blood_group'),
						'body_type'				=>	$this->input->post('body_type'),
						'body_art'				=>	$this->input->post('body_art'),
						'any_disability'		=>	$this->input->post('any_disability')
					);
					$data['physical_attributes'] = json_encode($physical_attributes);
					// ------------------------------------ Physical Attributes------------------------------------ //

					// ------------------------------------ Language------------------------------------ //
					$language[] = array(
						'mother_tongue'			=>  $this->input->post('mother_tongue'),
						'language'				=>	$this->input->post('language'),
						'speak'					=>	$this->input->post('speak'),
						'read'					=>	$this->input->post('read')
					);
					$data['language'] = json_encode($language);
					// ------------------------------------ Language------------------------------------ //

					// ------------------------------------Hobbies & Interest------------------------------------ //
					$hobbies_and_interest[] = array(
						'hobby'	    =>  $this->input->post('hobby'),
						'interest'				=>  $this->input->post('interest'),
						'music'					=>	$this->input->post('music'),
						'books'					=>	$this->input->post('books'),
						'movie'					=>	$this->input->post('movie'),
						'tv_show'				=>	$this->input->post('tv_show'),
						'sports_show'			=>	$this->input->post('sports_show'),
						'fitness_activity'		=>	$this->input->post('fitness_activity'),
						'cuisine'				=>	$this->input->post('cuisine'),
						'dress_style'			=>	$this->input->post('dress_style')
					);
					$data['hobbies_and_interest'] = json_encode($hobbies_and_interest);
					// ------------------------------------Hobbies & Interest------------------------------------ //

					// ------------------------------------ Personal Attitude & Behavior------------------------------------ //
					$personal_attitude_and_behavior[] = array(
						'affection'	=>  $this->input->post('affection'),
						'humor'             =>	$this->input->post('humor'),
						'political_view'    =>	$this->input->post('political_view'),
						'religious_service' =>	$this->input->post('religious_service')
					);
					$data['personal_attitude_and_behavior'] = json_encode($personal_attitude_and_behavior);
					// ------------------------------------ Personal Attitude & Behavior------------------------------------ //

					// ------------------------------------Residency Information------------------------------------ //
					$residency_information[] = array(
						'birth_country'	=>  $this->input->post('birth_country'),
						'residency_country'		=>	$this->input->post('residency_country'),
						'citizenship_country'	=>	$this->input->post('citizenship_country'),
						'grow_up_country'		=>	$this->input->post('grow_up_country'),
						'immigration_status'	=>	$this->input->post('immigration_status')
					);
					$data['residency_information'] = json_encode($residency_information);
					// ------------------------------------Residency Information------------------------------------ //

					// ------------------------------------Spiritual and Social Background------------------------------------ //
					$spiritual_and_social_background[] = array(
						'religion'	=>  $this->input->post('religion'),
						'caste'					=>	$this->input->post('caste'),
						'sub_caste'				=>	$this->input->post('sub_caste'),
						'ethnicity'				=>	$this->input->post('ethnicity'),
						'personal_value'		=>	$this->input->post('personal_value'),
						'family_value'			=>	$this->input->post('family_value'),
						'u_manglik'             =>  $this->input->post('u_manglik'),
						'community_value'		=>	$this->input->post('community_value'),
						'family_status'         =>  $this->input->post('family_status')
					);
					$data['spiritual_and_social_background'] = json_encode($spiritual_and_social_background);
					// ------------------------------------Spiritual and Social Background------------------------------------ //

					// ------------------------------------ Life Style------------------------------------ //
					$life_style[] = array(
						'diet'				=>  $this->input->post('diet'),
						'drink'					=>	$this->input->post('drink'),
						'smoke'					=>	$this->input->post('smoke'),
						'living_with'			=>	$this->input->post('living_with')
					);
					$data['life_style'] = json_encode($life_style);
					// ------------------------------------ Life Style------------------------------------ //

					// ------------------------------------ Astronomic Information------------------------------------ //
					$astronomic_information[] = array(
						'sun_sign'	=>  $this->input->post('sun_sign'),
						'moon_sign'					=>	$this->Crud_model->get_type_name_by_id('nakshtra', $this->input->post('nakshtra'), 'nakshtra_name'),
						'time_of_birth'				=>	$this->input->post('time_of_birth'),
						'city_of_birth'				=>	$this->input->post('city_of_birth')
					);
					$data['astronomic_information'] = json_encode($astronomic_information);
					$data['nakshtra_id']=$this->input->post('nakshtra');
					// ------------------------------------ Astronomic Information------------------------------------ //

					// ------------------------------------Permanent Address------------------------------------ //
					$permanent_address[] = array(
						'permanent_country'	=>  $this->input->post('permanent_country'),
						'permanent_city'				=>	$this->input->post('permanent_city'),
						'permanent_state'				=>	$this->input->post('permanent_state'),
						'permanent_postal_code'			=>	$this->input->post('permanent_postal_code')
					);
					$data['permanent_address'] = json_encode($permanent_address);
					// ------------------------------------Permanent Address------------------------------------ //

					// ------------------------------------Family Information------------------------------------ //
					$family_info[] = array(
						'father'            => $this->input->post('father'),
						'mother'            => $this->input->post('mother'),
						'brother_sister'    => $this->input->post('brother_sister'),
						'wife'              => $this->input->post('wife'),
						'children'          => $this->input->post('children')
					);

					$data['family_info'] = json_encode($family_info);
					// ------------------------------------Family Information------------------------------------ //

					// ------------------------------------ Additional Personal Details------------------------------------ //
		       $additional_personal_details[] = array(
					// 'home_district'         => $this->input->post('home_district'),
					// 'family_residence'      => $this->input->post('family_residence'),
					// 'fathers_occupation'    => $this->input->post('fathers_occupation'),
					// 'special_circumstances' => $this->input->post('special_circumstances'),
					'anniversary'           => $this->input->post('anniversary')
				);

					$data['additional_personal_details'] = json_encode($additional_personal_details);
					// ------------------------------------ Additional Personal Details------------------------------------ //

					// ------------------------------------ Partner Expectation------------------------------------ //
					$partner_expectation[] = array(
						'general_requirement'	=>  $this->input->post('general_requirement'),
						'partner_age'						=>	$this->input->post('partner_age'),
						'partner_height'					=>	$this->input->post('partner_height'),
						'partner_weight'					=>	$this->input->post('partner_weight'),
						'partner_marital_status'			=>	$this->input->post('partner_marital_status'),
						'with_children_acceptables'			=>	$this->input->post('with_children_acceptables'),
						'partner_country_of_residence'		=>	$this->input->post('partner_country_of_residence'),
						'partner_religion'					=>	$this->input->post('partner_religion'),
						'partner_caste'						=>	$this->input->post('partner_caste'),
						'partner_sub_caste'					=>	$this->input->post('partner_sub_caste'),
						'partner_complexion'				=>	$this->input->post('partner_complexion'),
						'partner_education'                 =>	$this->input->post('partner_education'),
						'partner_profession'				=>	$this->input->post('partner_profession'),
						'partner_drinking_habits'			=>	$this->input->post('partner_drinking_habits'),
						'partner_smoking_habits'			=>	$this->input->post('partner_smoking_habits'),
						'partner_diet'						=>	$this->input->post('partner_diet'),
						'partner_body_type'					=>	$this->input->post('partner_body_type'),
						'partner_personal_value'			=>	$this->input->post('partner_personal_value'),
						'manglik'							=>	$this->input->post('manglik'),
						'partner_any_disability'			=>	$this->input->post('partner_any_disability'),
						'partner_mother_tongue'				=>	$this->input->post('partner_mother_tongue'),
						'partner_family_value'				=>	$this->input->post('partner_family_value'),
						'prefered_country'					=>	$this->input->post('prefered_country'),
						'prefered_state'					=>	$this->input->post('prefered_state'),
						'prefered_status'					=>	$this->input->post('prefered_status')
					);
					$data['partner_expectation'] = json_encode($partner_expectation);
					// ------------------------------------ Partner Expectation------------------------------------ //
					// Profile Image
					if (!demo()) {
						if ($_FILES['profile_image']['name'] !== '') {
							$path = $_FILES['profile_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								$this->Crud_model->file_up("profile_image", "profile", $para2, '', '', $ext);
								$images[] = array('profile_image' => 'profile_' . $para2 . $ext, 'thumb' => 'profile_' . $para2 . '_thumb' . $ext);
								$data['profile_image'] = json_encode($images);
								$data['profile_image_status'] = 1;
								$data['profile_image_update_time'] = date("Y-m-d H:i:s");
							}
						}
					}




// ✅ Add Legion & Area fields before update
$data['legion_id'] = $this->input->post('legion_id');
$data['legion']    = $this->input->post('legion');
$data['area_id']   = $this->input->post('area_id');
$data['area']      = $this->input->post('area');

$this->db->where('member_id', $para2);
$result = $this->db->update('member', $data);
recache();
if ($result) {
    $this->session->set_flashdata('alert', 'edit');
    redirect(base_url() . 'admin/members/' . $para3, 'refresh');
}

				}
			
			} 



			elseif ($para1 == "upgrade_member_package") {
				$up_member_id = $this->input->post('up_member_id');
				$plan_id = $this->input->post('plan');
				$member_type = $this->input->post('member_type');

				$prev_express_interest =  $this->db->get_where('member', array('member_id' => $up_member_id))->row()->express_interest;
				$prev_direct_messages = $this->db->get_where('member', array('member_id' => $up_member_id))->row()->direct_messages;
				$prev_photo_gallery = $this->db->get_where('member', array('member_id' => $up_member_id))->row()->photo_gallery;

				if ($plan_id == '1') {
					$data['membership'] = 1;
				} else {
					$data['membership'] = 2;
				}

				$data['express_interest'] = $prev_express_interest + $this->db->get_where('plan', array('plan_id' => $plan_id))->row()->express_interest;
				$data['direct_messages'] = $prev_direct_messages + $this->db->get_where('plan', array('plan_id' => $plan_id))->row()->direct_messages;
				$data['photo_gallery'] = $prev_photo_gallery + $this->db->get_where('plan', array('plan_id' => $plan_id))->row()->photo_gallery;

				$package_info[] = array(
					'current_package'   => $this->Crud_model->get_type_name_by_id('plan', $plan_id),
					'package_price'     => $this->Crud_model->get_type_name_by_id('plan', $plan_id, 'amount'),
					'payment_type'      => 'By Admin',
				);
				$data['package_info'] = json_encode($package_info);

				$this->db->where('member_id', $up_member_id);
				$result = $this->db->update('member', $data);
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'upgrade');
					redirect(base_url() . 'admin/members/' . $member_type, 'refresh');
				}
			}
		}
	}







	// Bulk member add
	function bulk_member_add($para1 = "", $para2 = "", $para3 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			if ($para1 == 'do_add') {
				if (!file_exists($_FILES['bulk_member_file']['tmp_name']) || !is_uploaded_file($_FILES['bulk_member_file']['tmp_name'])) {
					$this->session->set_flashdata('error', translate('File is not selected'));
					redirect('admin/bulk_member_add');
				}

				$inputFileName = $_FILES['bulk_member_file']['tmp_name'];

				$inputFileType = $this->spreadsheet->identify($inputFileName);
				$reader = $this->spreadsheet->createReader($inputFileType);
				$spreadsheet = $reader->load($inputFileName);
				$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

				$members = array();
				if (!empty($sheetData)) {

					if (!isset($sheetData[1])) {
						$this->session->set_flashdata('error', translate('Column names are missing'));
						redirect('admin/bulk_member_add');
					}

					foreach ($sheetData[1] as $colk => $colv) {
						$col_map[$colk] = $colv;
					}

					if (!isset($sheetData[2])) {
						$this->session->set_flashdata('error', translate('Data missing'));
						redirect('admin/bulk_member_add');
					}

					for ($i = 2; $i <= count($sheetData); $i++) {
						$member = array();
						foreach ($sheetData[$i] as $colk => $colv) {
							$member[$col_map[$colk]] = $colv;
						}
						$members[] = $member;
					}
				}
				$true_counter = "";
				$false_counter = "";


				if (!empty($members)) {
					foreach ($members as $member) {
						$add_done =   $this->member_bulk_upload_save_single($member);
						if ($add_done == TRUE) {
							$true_counter++;
						} elseif ($add_done != TRUE) {
							$false_counter++;
						}
					}
				}

				$page_data['title'] 	= "Admin || " . $this->system_title;
				$page_data['top'] 		= "members/index.php";
				$page_data['folder'] 	= "members";
				$page_data['file']	 	= "bulk_member_add.php";
				$page_data['bottom'] 	= "members/index.php";
				$page_data['page_name'] = "bulk_member_add";
				if ($true_counter > 0) {
					$page_data['true_counter']	 	= $true_counter;
				}
				if ($false_counter > 0) {
					$page_data['false_counter'] 	= $false_counter;
				}
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "") {
				$page_data['title'] = "Admin || " . $this->system_title;
				$page_data['top'] 		= "members/index.php";
				$page_data['folder'] 	= "members";
				$page_data['file']	 	= "bulk_member_add.php";
				$page_data['bottom'] 	= "members/index.php";
				$page_data['page_name'] = "bulk_member_add";
				$this->load->view('back/index', $page_data);
			}
		}
	}

	//add kundali and video start by Pooja

	public function add_kundali()
	{
		$id = $this->input->post('member_id');
		$config = $this->set_upload_options();
		$this->load->library('upload');
		$this->upload->initialize($config);



		if (!empty($_FILES['product_image']['name'])) {

			if (!$this->upload->do_upload('product_image')) {
				$error = $this->upload->display_errors();

				redirect('admin/add_kundali');
			} else {
				$data['image'] = $this->upload->data('file_name');


				print_r($this->upload->data('file_name'));
			}
		}

		$this->db->set($data)->where('member_id', $id)->update('member');

		$result = $this->db->affected_rows();


		if ($result == true) {
			$this->session->set_flashdata('success_alert', translate('Image successfully uploaded'));
			redirect('admin/members/premium_members');
		} else {
			$this->session->set_flashdata('danger_alert', translate('Please select only GIF, JPG, JPEG and PNG image types'));
			redirect('admin/add_kundali');
		}
	}


	public function add_video()
	{
		// echo "<pre>";
		// print_r($_POST);
		// print_r($_FILES);
		// exit();

		$id = $this->input->post('member_id');
		$config = $this->set_videoupload_options();
		$this->load->library('upload');
		$this->upload->initialize($config);



		if (!empty($_FILES['product_video']['name'])) {

			if (!$this->upload->do_upload('product_video')) {
				$error = $this->upload->display_errors();

				redirect('admin/add_video');
			} else {
				$data['video'] = $this->upload->data('file_name');


				print_r($this->upload->data('file_name'));
			}
		}

		$this->db->set($data)->where('member_id', $id)->update('member');

		$result = $this->db->affected_rows();


		if ($result == true) {
			$this->session->set_flashdata('success_alert', translate('video successfully uploaded'));
			redirect('admin/members/premium_members');
		} else {
			$this->session->set_flashdata('danger_alert', translate('Please select only mp4 format'));
			redirect('admin/add_video');
		}
	}

	private function set_videoupload_options()
	{
		$config = array();
		$config['upload_path'] = 'uploads/video';
		$config['allowed_types'] = 'mp4';
		/*$config['max_size']      = '50000';*/
		$config['overwrite']     = FALSE;
		return $config;
	}



	private function set_upload_options()
	{
		$config = array();
		$config['upload_path'] = 'uploads/kundali_image';
		$config['allowed_types'] = 'jpg|png|jpeg|pdf';
		// $config['max_size']      = '50000';
		$config['overwrite']     = FALSE;
		return $config;
	}

	//add kundali and video stop by Pooja

	/* member_kundali_modal and member_video_modal start*/

	function member_kundali_modal($member_id)
	{
		$page_data['member_id'] = $member_id;
		$this->load->view('back/members/kundali_modal', $page_data);
	}

	function member_video_modal($member_id)
	{
		$page_data['member_id'] = $member_id;
		$this->load->view('back/members/video_modal', $page_data);
	}
	/* member_kundali_modal and member_video_modal stop*/


	public function member_bulk_upload_save_single($member)
	{
		$member_email_check = $this->db->get_where("member", array("email" => $member['email']))->num_rows();
		if ($member_email_check > 0) {
			return 0;
		}
		// $profile_image[] = array('profile_image'    =>  'male_default.jpg',
		//                             'thumb'         =>  'male_default_thumb.jpg'
		//                     );

		if ($_POST['gender'] == '1') {
			$profile_image[] = array(
				'profile_image'    =>  'male_default.jpg',
				'thumb'         =>  'male_default_thumb.jpg'
			);
		} else if ($_POST['gender'] == '2') {
			$profile_image[] = array(
				'profile_image'    =>  'female_default.png',
				'thumb'         =>  'female_default_thumb.png'
			);
		}
		$profile_image = json_encode($profile_image);

		$basic_info[] = array(
			'age'                 => '',
			'marital_status'        => '',
			'number_of_children'    => '',
			'area'                  => '',
			'on_behalf'             => $member['on_behalf']
		);
		$basic_info = json_encode($basic_info);

		$present_address[] = array(
			'country'        => '',
			'city'                  => '',
			'state'                 => '',
			'postal_code'           => ''
		);
		$present_address = json_encode($present_address);

		$education_and_career[] = array(
			'highest_education' => '',
			'occupation'                    => '',
			'annual_income'                 => ''
		);
		$education_and_career = json_encode($education_and_career);

		$physical_attributes[] = array(
			'weight'     => '',
			'eye_color'             => '',
			'hair_color'            => '',
			'complexion'            => '',
			'blood_group'           => '',
			'body_type'             => '',
			'body_art'              => '',
			'any_disability'        => ''
		);
		$physical_attributes = json_encode($physical_attributes);

		$language[] = array(
			'mother_tongue'         => '',
			'language'              => '',
			'speak'                 => '',
			'read'                  => ''
		);
		$language = json_encode($language);

		$hobbies_and_interest[] = array(
			'hobby'     => '',
			'interest'              => '',
			'music'                 => '',
			'books'                 => '',
			'movie'                 => '',
			'tv_show'               => '',
			'sports_show'           => '',
			'fitness_activity'      => '',
			'cuisine'               => '',
			'dress_style'           => ''
		);
		$hobbies_and_interest = json_encode($hobbies_and_interest);

		$personal_attitude_and_behavior[] = array(
			'affection'   => '',
			'humor'                 => '',
			'political_view'        => '',
			'religious_service'     => ''
		);
		$personal_attitude_and_behavior = json_encode($personal_attitude_and_behavior);

		$residency_information[] = array(
			'birth_country'    => '',
			'residency_country'     => '',
			'citizenship_country'   => '',
			'grow_up_country'       => '',
			'immigration_status'    => ''
		);
		$residency_information = json_encode($residency_information);

		$spiritual_and_social_background[] = array(
			'religion'   => '',
			'caste'                 => '',
			'sub_caste'             => '',
			'ethnicity'             => '',
			'u_manglik'             => '',
			'personal_value'        => '',
			'family_value'          => '',
			'community_value'       => '',
			'family_status'          =>  ''
		);
		$spiritual_and_social_background = json_encode($spiritual_and_social_background);

		$life_style[] = array(
			'diet'                => '',
			'drink'                 => '',
			'smoke'                 => '',
			'living_with'           => ''
		);
		$life_style = json_encode($life_style);

		$astronomic_information[] = array(
			'sun_sign'    => '',
			'moon_sign'                 => '',
			'time_of_birth'             => '',
			'city_of_birth'             => ''
		);
		$astronomic_information = json_encode($astronomic_information);

		$permanent_address[] = array(
			'permanent_country'    => '',
			'permanent_city'                => '',
			'permanent_state'               => '',
			'permanent_postal_code'         => ''
		);
		$permanent_address = json_encode($permanent_address);

		$family_info[] = array(
			'father'             => '',
			'mother'                => '',
			'brother_sister'        => ''
		);
		$family_info = json_encode($family_info);

		$additional_personal_details[] = array(
			'home_district'  => '',
			'family_residence'              => '',
			'fathers_occupation'            => '',
			'special_circumstances'         => ''
		);
		$additional_personal_details = json_encode($additional_personal_details);

		$partner_expectation[] = array(
			'general_requirement'    => '',
			'partner_age'                       => '',
			'partner_height'                    => '',
			'partner_weight'                    => '',
			'partner_marital_status'            => '',
			'with_children_acceptables'         => '',
			'partner_country_of_residence'      => '',
			'partner_religion'                  => '',
			'partner_caste'                     => '',
			'partner_sub_caste'                  => '',
			'partner_complexion'                => '',
			'partner_education'                 => '',
			'partner_profession'                => '',
			'partner_drinking_habits'           => '',
			'partner_smoking_habits'            => '',
			'partner_diet'                      => '',
			'partner_body_type'                 => '',
			'partner_personal_value'            => '',
			'manglik'                           => '',
			'partner_any_disability'            => '',
			'partner_mother_tongue'             => '',
			'partner_family_value'              => '',
			'prefered_country'                  => '',
			'prefered_state'                    => '',
			'prefered_status'                   => ''
		);
		$partner_expectation = json_encode($partner_expectation);

		$privacy_status[] = array(
			'present_address'                 => 'no',
			'education_and_career'            => 'no',
			'physical_attributes'             => 'no',
			'language'                        => 'no',
			'hobbies_and_interest'            => 'no',
			'personal_attitude_and_behavior'  => 'no',
			'residency_information'           => 'no',
			'spiritual_and_social_background' => 'no',
			'life_style'                      => 'no',
			'astronomic_information'          => 'no',
			'permanent_address'               => 'no',
			'family_info'                     => 'no',
			'additional_personal_details'     => 'no',
			'partner_expectation'             => 'yes'
		);
		$privacy_status = json_encode($privacy_status);

		$pic_privacy[] = array(
			'profile_pic_show'        => 'all',
			'gallery_show'            => 'premium'

		);
		$data_pic_privacy = json_encode($pic_privacy);

		$package_info[] = array(
			'current_package'   => $this->Crud_model->get_type_name_by_id('plan', $member['package']),
			'package_price'     => $this->Crud_model->get_type_name_by_id('plan', $member['package'], 'amount'),
			'payment_type'      => 'None',
		);
		$package_info = json_encode($package_info);


		$data['status'] = 'approved';
		$data['first_name'] = $member['first_name'];
		$data['last_name'] = $member['last_name'];
		$data['gender'] = $member['gender'];
		$data['email'] = $member['email'];
		$data['email_verification_status'] = '1';
		$data['date_of_birth'] = strtotime($member['date_of_birth']);
		$data['height'] = 0.00;
		$data['mobile'] = $member['mobile'];
		$data['password'] = sha1($member['password']);
		$data['profile_image'] = $profile_image;
		$data['introduction'] = '';
		$data['basic_info'] = $basic_info;
		$data['present_address'] = $present_address;
		$data['family_info'] = $family_info;
		$data['education_and_career'] = $education_and_career;
		$data['physical_attributes'] = $physical_attributes;
		$data['language'] = $language;
		$data['hobbies_and_interest'] = $hobbies_and_interest;
		$data['personal_attitude_and_behavior'] = $personal_attitude_and_behavior;
		$data['residency_information'] = $residency_information;
		$data['spiritual_and_social_background'] = $spiritual_and_social_background;
		$data['life_style'] = $life_style;
		$data['astronomic_information'] = $astronomic_information;
		$data['permanent_address'] = $permanent_address;
		$data['additional_personal_details'] = $additional_personal_details;
		$data['partner_expectation'] = $partner_expectation;
		$data['interest'] = '[]';
		$data['short_list'] = '[]';
		$data['followed'] = '[]';
		$data['ignored'] = '[]';
		$data['ignored_by'] = '[]';
		$data['gallery'] = '[]';
		$data['happy_story'] = '[]';
		$data['package_info'] = $package_info;
		$data['payments_info'] = '[]';
		$data['interested_by'] = '[]';
		$data['follower'] = 0;
		$data['notifications'] = '[]';
		$plan = $member['package'];
		if ($plan == 1) {
			$data['membership'] = 1;
		} else {
			$data['membership'] = 2;
		}
		$data['profile_status'] = 1;
		$data['is_closed'] = 'no';
		$data['member_since'] = date("Y-m-d H:i:s");
		$data['express_interest'] = $this->db->get_where('plan', array('plan_id' => $plan))->row()->express_interest;
		$data['direct_messages'] = $this->db->get_where('plan', array('plan_id' => $plan))->row()->direct_messages;
		$data['photo_gallery'] = $this->db->get_where('plan', array('plan_id' => $plan))->row()->photo_gallery;
		$data['profile_completion'] = 0;
		$data['is_blocked'] = 'no';
		$data['privacy_status'] = $privacy_status;
		$data['pic_privacy'] = $data_pic_privacy;
		$data['report_profile'] = '[]';

		$this->db->insert('member', $data);
		$insert_id = $this->db->insert_id();
		$member_profile_id = strtoupper(substr(hash('sha512', rand()), 0, 8)) . $insert_id;

		$this->db->where('member_id', $insert_id);
		$this->db->update('member', array('member_profile_id' => $member_profile_id));
		recache();
		$this->Email_model->member_staff_account_opening_by_admin('member', $data['email'], $member['password']);
		$this->Email_model->member_registration_email_to_admin($insert_id);
		return 1;
	}

	function deleted_members($para1 = "", $para2 = "", $para3 = "", $para4 = "")
	{
	    
	    
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$member_approval = $this->db->get_where('general_settings', array('type' => 'member_approval_by_admin'))->row()->value;
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($this->session->flashdata('alert') == "restore") {
				$page_data['success_alert'] = translate("you_have_successfully_restored_this_member!");
			}
			if ($this->session->flashdata('alert') == "delete") {
				$page_data['success_alert'] = translate("you_have_successfully_deleted_this_member_permanently!");
			} elseif ($this->session->flashdata('alert') == "demo_msg") {
				$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
			}
			if ($para2 == "list_data") {
				// Get membership configuration
				$membership_config = $this->get_membership_config($para1);
				if (!$membership_config) {
					echo json_encode(array('error' => 'Invalid membership type'));
					return;
				}
				$membership_value = $membership_config->membership_value;
			
				// Get member approval setting
				$member_approval_setting = $this->db->get_where('general_settings', array('type' => 'member_approval'))->row();
				$member_approval = $member_approval_setting ? $member_approval_setting->value : 'no';
			
				// Define columns (same for all types)
				if ($member_approval == 'yes') {
					$columns = array(
						0 => '',
						1 => 'member_profile_id',
						2 => 'first_name',
						3 => 'status',
						4 => 'follower',
						5 => 'reported_by',
						6 => 'member_since',
						7 => 'spiritual_and_social_background',
						8 => 'sub_caste_name',
					);
				} else {
					$columns = array(
						0 => '',
						1 => 'member_profile_id',
						2 => 'first_name',
						3 => 'status',
						4 => 'follower',
						5 => 'reported_by',
						6 => 'member_since',
						7 => 'spiritual_and_social_background',
						8 => 'sub_caste_name',
					);
				}
			
				// Get DataTable parameters
				$limit = $this->input->post('length');
				$start = $this->input->post('start');
				$order_col = $columns[$this->input->post('order')[0]['column']];
				$dir = $this->input->post('order')[0]['dir'];
			
				// Get admin ID for scoped data (if needed; adjust as per your auth)
				$admin_id = $this->session->userdata('admin_id');
			
				// Get total and filtered data (UPDATED: Use $membership_value instead of $member_type)
				$totalData = $this->Crud_model->allmemberscount($membership_value);  // Note: Your Crud_model method should accept value, not type
				if (empty($this->input->post('search')['value'])) {
					$members = $this->Crud_model->allmembers($membership_value, $limit, $start, $order_col, $dir);
					$totalFiltered = $totalData;
				} else {
					$search = $this->input->post('search')['value'];
					$members = $this->Crud_model->memberssearch($membership_value, $limit, $start, $search, $order_col, $dir);
					$totalFiltered = $this->Crud_model->memberssearchcount($membership_value, $search);
				}

				
				$data = array();
				if (!empty($members)) {
					// if ($dir == 'asc') { $i = $start + 1; } elseif ($dir == 'desc') { $i = $totalFiltered - $start; }
					foreach ($members as $member) {
						//          	$image = json_decode($member->profile_image, true);
						//          	if (file_exists('uploads/profile_image/'.$image[0]['thumb'])) {
						// 			$member_image="<img src='".base_url()."uploads/profile_image/".$image[0]['thumb']."' class='img-sm'>";
						// 		}
						// 		else {
						// 			$member_image="<img src='".base_url()."uploads/profile_image/male_default.jpg' class='img-sm'>";
						// 		}


						$image = json_decode($member->profile_image, true);
						if (file_exists('uploads/profile_image/' . $image[0]['thumb'])) {

							if ($image[0]['thumb'] == 'male_default_thumb.jpg') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/male_default.jpg' class='img-sm'>";
							} else if ($image[0]['thumb'] == 'female_default_thumb.png') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/female_default.png' class='img-sm'>";
							} else {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/" . $image[0]['thumb'] . "' class='img-sm'>";
							}
						} else {
							if ($member->gender == '1') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/male_default.jpg' class='img-sm'>";
							} else if ($member->gender == '2') {
								$member_image = "<img src='" . base_url() . "uploads/profile_image/female_default.png' class='img-sm'>";
							}
						}




						if ($member->is_closed == "yes") {
							$acnt_status_button = "<center><span class='badge badge-danger' style='width:60px'>" . translate('closed') . "</span></center>";
						} elseif ($member->is_closed == "no") {
							$acnt_status_button = "<center><span class='badge badge-success' style='width:60px'>" . translate('Active') . "</span></center>";
						}

						$nestedData['image'] = $member_image;
						$nestedData['address'] = $address;
						 $nestedData['percentage'] = $member->percentage.'%';
						$nestedData['name'] = $member->first_name . ' ' . $member->last_name;
						if ($member_approval == 'yes') {
							if ($member->status == "pending") {
								$nestedData['status'] = "<button  data-toggle='modal' class='badge badge-info' >" . translate('pending') . "</button>
								";
							} elseif ($member->status == "approved") {
								$nestedData['status'] = "<button   class='badge badge-success' >" . translate('approved') . "</i></button>
								";
							}
						}


						$nestedData['member_id'] = $member->member_profile_id;
						$nestedData['follower'] = $member->follower;
						$nestedData['profile_reported'] = $member->reported_by;

						if ($para1 == "premium_members") {
							$package_info = $this->db->get_where('member', array('member_id' => $member->member_id))->row()->package_info;
							$package_info = json_decode($package_info, true);
							$nestedData['package'] = $package_info[0]['current_package'];
						}
						$nestedData['member_since'] = date('d/m/Y h:i:s A', strtotime($member->member_since));
						$nestedData['member_status'] = $acnt_status_button;
						$nestedData['options'] = "<button data-target='#restore_modal' data-toggle='modal' class='btn btn-success btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title= '" . translate('restore') . "' onclick='restore($member->member_id)'><i class='fa fa-check'></i></button>" . ' ' . "<button data-target='#permanently_delete_member_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('permanently_delete_member') . "' onclick='permanently_delete_member($member->member_id)'><i class='fa fa-trash'></i></button>";

						$data[] = $nestedData;
						// if ($dir == 'asc') { $i++; } elseif ($dir == 'desc') { $i--; }
					}
				}

				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			} elseif ($para1 == "") {
				$page_data['top'] = "members/index.php";
				$page_data['folder'] = "deleted_members";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "members/index.php";
				$page_data['page_name'] = "deleted_member";

				$this->load->view('back/index', $page_data);
			}
		}
	}

	function member_restore($para1)
	{
		$this->session->set_flashdata('alert', 'restore');
		//echo $para1;
		$data['member_id'] = $para1;
		$data['member_profile_id'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->member_profile_id;
		$data['status'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->status;
		$data['first_name'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->first_name;
		$data['last_name'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->last_name;
		$data['gender'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->gender;
		$data['email'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->email;
		$data['email_verification_code'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->email_verification_code;
		$data['email_verification_status'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->email_verification_status;
		$data['mobile'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->mobile;
		$data['is_closed'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->is_closed;
		$data['date_of_birth'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->date_of_birth;
		$data['height'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->height;
		$data['password'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->password;
		$data['profile_image'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->profile_image;
		$data['introduction'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->introduction;
		$data['basic_info'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->basic_info;
		$data['present_address'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->present_address;
		$data['education_and_career'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->education_and_career;
		$data['physical_attributes'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->physical_attributes;
		$data['language'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->language;
		$data['hobbies_and_interest'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->hobbies_and_interest;
		$data['personal_attitude_and_behavior'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->personal_attitude_and_behavior;
		$data['residency_information'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->residency_information;
		$data['spiritual_and_social_background'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->spiritual_and_social_background;
		$data['life_style'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->life_style;
		$data['astronomic_information'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->astronomic_information;
		$data['permanent_address'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->permanent_address;
		$data['family_info'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->family_info;
		$data['additional_personal_details'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->additional_personal_details;
		$data['partner_expectation'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->partner_expectation;
		$data['interest'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->interest;
		$data['short_list'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->short_list;
		$data['followed'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->followed;
		$data['ignored'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->ignored;
		$data['ignored_by'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->ignored_by;
		$data['gallery'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->gallery;
		$data['happy_story'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->happy_story;
		$data['package_info'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->package_info;
		$data['payments_info'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->payments_info;
		$data['interested_by'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->interested_by;
		$data['follower'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->follower;
		$data['membership'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->membership;
		$data['notifications'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->notifications;
		$data['profile_status'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->profile_status;
		$data['member_since'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->member_since;
		$data['express_interest'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->express_interest;
		$data['direct_messages'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->direct_messages;
		$data['photo_gallery'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->photo_gallery;
		$data['profile_completion'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->profile_completion;
		$data['is_blocked'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->is_blocked;
		$data['privacy_status'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->privacy_status;
		$data['pic_privacy'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->pic_privacy;
		$data['report_profile'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->report_profile;
		$data['reported_by'] = $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->reported_by;

		$this->db->insert('member', $data);

		$this->db->where('member_id', $para1);
		$result = $this->db->delete('deleted_member');
		recache();
	}

	function permanently_member_delete($para1)
	{
		if (demo()) {
			$this->session->set_flashdata('alert', 'demo_msg');
			return false;
		}

		$this->session->set_flashdata('alert', 'delete');
		$img =  $this->db->get_where("deleted_member", array("member_id" => $para1))->row()->profile_image;
		$img = json_decode($img, true);

		$this->db->where('member_id', $para1);
		$result = $this->db->delete('deleted_member');

		unlink('uploads/profile_image/' . $img[0]['profile_image']);
		unlink('uploads/profile_image/' . $img[0]['thumb']);

		$this->session->set_flashdata('alert', 'delete');
		recache();
	}

	function member_block($para1, $para2)
	{
		if (demo()) {
			$this->session->set_flashdata('alert', 'demo_msg');
			return false;
		}
		if ($para1 == 'no') {
			$data['is_blocked'] = 'yes';
			$this->session->set_flashdata('alert', 'block');
		} elseif ($para1 == 'yes') {
			$data['is_blocked'] = 'no';
			$this->session->set_flashdata('alert', 'unblock');
		}

		$this->db->where('member_id', $para2);
		$this->db->update('member', $data);
		recache();
	}

	function member_approval_status($para1, $para2)
	{
		$data['status'] = 'approved';
		$this->session->set_flashdata('alert', 'approved');

		$this->db->where('member_id', $para2);
		$this->db->update('member', $data);
		$this->session->set_flashdata('alert', 'member_approval');

		$email = $this->db->get_where('member', array('member_id' => $para2))->row()->email;

		if ($this->Email_model->member_approval('member', $email) == false) {
			//$msg = 'done_but_not_sent';
		} else {
			//$msg = 'done_and_sent';
		}
		recache();
	}

	function member_delete($para1)
	{
	    error_reporting(E_ALL);
        ini_set('display_errors',1);
		if (demo()) {
			$this->session->set_flashdata('alert', 'demo_msg');
			return false;
		}

		$data['member_id'] = $para1;
		$data['member_profile_id'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'member_profile_id');
		$data['status'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'status');
		$data['first_name'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'first_name');
		$data['last_name'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'last_name');
		$data['gender'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'gender');
		$data['email'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'email');
		$data['email_verification_code'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'email_verification_code');
		$data['email_verification_status'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'email_verification_status');
		$data['mobile'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'mobile');
		$data['is_closed'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'is_closed');
		$data['date_of_birth'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'date_of_birth');
		$data['height'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'height');
		$data['password'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'password');
		$data['profile_image'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'profile_image');
		$data['introduction'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'introduction');
		$data['basic_info'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'basic_info');
		$data['present_address'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'present_address');
		$data['education_and_career'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'education_and_career');
		$data['physical_attributes'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'physical_attributes');
		$data['language'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'language');
		$data['hobbies_and_interest'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'hobbies_and_interest');
		$data['personal_attitude_and_behavior'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'personal_attitude_and_behavior');
		$data['residency_information'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'residency_information');
		$data['spiritual_and_social_background'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'spiritual_and_social_background');
		$data['life_style'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'life_style');
		$data['astronomic_information'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'astronomic_information');
		$data['permanent_address'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'permanent_address');
		$data['family_info'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'family_info');
		$data['additional_personal_details'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'additional_personal_details');
		$data['partner_expectation'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'partner_expectation');
		$data['interest'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'interest');
		$data['short_list'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'short_list');
		$data['followed'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'followed');
		$data['ignored'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'ignored');
		$data['ignored_by'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'ignored_by');
		$data['gallery'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'gallery');
		$data['happy_story'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'happy_story');
		$data['package_info'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'package_info');
		$data['payments_info'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'payments_info');
		$data['interested_by'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'interested_by');
		$data['follower'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'follower');
		$data['membership'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'membership');
		$data['notifications'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'notifications');
		$data['profile_status'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'profile_status');
		$data['member_since'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'member_since');
		$data['express_interest'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'express_interest');
		$data['direct_messages'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'direct_messages');
		$data['photo_gallery'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'photo_gallery');
		$data['profile_completion'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'profile_completion');
		$data['is_blocked'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'is_blocked');
		$data['privacy_status'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'privacy_status');
		$data['pic_privacy'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'pic_privacy');
		$data['report_profile'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'report_profile');
		$data['reported_by'] = $this->Crud_model->get_type_name_by_id('member', $para1, 'reported_by');

		$this->db->insert('deleted_member', $data);

		$this->db->where('member_id', $para1);
		$result = $this->db->delete('member');
		recache();
		if ($result) {
			$this->session->set_flashdata('alert', 'delete');
		} else {
			$this->session->set_flashdata('alert', 'failed_delete');
		}
	}

	function member_package_modal($member_id)
	{
		$page_data['member_id'] = $member_id;

		$this->load->view('back/members/package_modal', $page_data);
	}

	function member_profile_image_approval($para1 = "", $para2 = "", $para3 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "members/index.php";
				$page_data['folder'] = "members";
				$page_data['file'] = "profile_pic_approval.php";
				$page_data['bottom'] = "members/index.php";
				$page_data['page_name'] = "member_profile_pic_approval";
				if ($this->session->flashdata('alert') == "picture_approved") {
					$page_data['success_alert'] = translate("you_have_successfully_approved_the_image!");
				} elseif ($this->session->flashdata('alert') == "picture_rejected") {
					$page_data['danger_alert'] = translate("you_have_successfully_rejected_the_image!");
				}

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == 'change_status') {
				$data['profile_image_status'] = $para2;
				$this->db->where('member_id', $para3);
				$this->db->update('member', $data);
				if ($para2 == 1) {
					$this->session->set_flashdata('alert', 'picture_approved');
				} elseif ($para2 == 2) {
					$this->session->set_flashdata('alert', 'picture_rejected');
				}
				recache();
			}
		}
	}

	
				// elseif ($para1 == "update_story") {  
				// 	log_message('debug', 'Story ID to update: ');
				// 	$this->form_validation->set_rules('story_name', 'Story Name', 'required');
				// 	$this->form_validation->set_rules('dated', 'Dated', 'required');
				// 	// $this->form_validation->set_rules('member_name', 'Member Name', 'required');
				// 	// $this->form_validation->set_rules('partner_name', 'Partner Name', 'required');
				// 	$this->form_validation->set_rules('description', 'Description', 'required');

				//  if ($this->form_validation->run() == FALSE) {
				// 		$page_data['top'] 		= "stories/index.php";
				// 		$page_data['folder'] 	= "stories";
				// 		$page_data['file']	 	= "edit_story.php";
				// 		$page_data['bottom'] 	= "stories/index.php";
				// 		$page_data['page_name'] = "stories";
				// 		$page_data['form_contents'] = $this->input->post();
				// 	}else{ 
				// 		$data=array();
				// 		$data['title'] = $this->input->post('story_name');
				// 		$data['happy_story_id'] = $para2;
				//         $data['date'] = date('Y-m-d',strtotime($this->input->post('dated')));
				//         $data['member_name'] = $this->input->post('member_name');
				//         $data['partner_name'] = $this->input->post('partner_name');
				// 		$data['description'] = $this->input->post('description'); 	
				// 		$data['program_area'] = $this->input->post('program_area');
				// 		$data['legion_name'] = $this->input->post('legion_name');
				// 		$data['area_name'] = $this->input->post('area_name');			         
				//         //print_r($_FILES);exit;
				// 		$config = $this->set_upload_happy_story_image();
				// 		$this->load->library('upload');
				// 		$this->upload->initialize($config);

				// 		if ($_FILES['story_photo']['name'] !== '') {
				// 			$id = uniqid();
				// 			$path = $_FILES['story_photo']['name'];
				// 			$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
				// 			if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
				// 				$this->Crud_model->file_up("story_photo", "happy_story", $id, '', '', $ext);
				// 				$images[] = array('image' => 'happy_story_' . $id . $ext, 'thumb' => 'happy_story_' . $id . '_thumb' . $ext);
				// 				$data['image'] = json_encode($images);
				// 			} else {
				// 				$this->session->set_flashdata('alert', 'failed_image');
				// 				redirect(base_url() . 'admin/stories', 'refresh');
				// 			}
				// 		}
				// 		//print_r($data);exit;
				// 		$this->db->where('happy_story_id', $para2 );
				// 		log_message('debug', 'Story ID to update: ', $data);
				// 		$this->db->update('happy_story', $data);
				// 		$result = $this->db->affected_rows();
				// 		if ($result == true) {
				// 			$this->session->set_flashdata('success', 'Updated successfully');
				// 			redirect('admin/stories');
				// 		} else {
				// 			$this->session->set_flashdata('failed', 'Failed');
				// 			redirect('admin/stories');
				// 		}

 				// 	$page_data['top'] 		= "stories/index.php";
				// 	$page_data['folder'] 	= "stories";
				// 	$page_data['file']	 	= "edit_story.php";
				// 	$page_data['bottom'] 	= "stories/index.php";
				// 	$page_data['page_name'] = "stories";
				// 	$page_data['get_story'] = $this->db->get_where("happy_story", array("happy_story_id" => $para2))->row_array();
				// 	//print_r($page_data);exit;
					
				// 	 }
				// 	$this->load->view('back/index', $page_data);

					 
				// }


// function stories($para1 = "", $para2 = "", $para3 = "")
// 	{ 
 
// 		$this->db->where("(membership=2)");
// 		$this->db->where("(status='approved')");
//         $query = $this->db->get('member');

//         if($query->num_rows()>0)
//         {
//            $res=   $query->result();
//         }
 

//        // echo '<pre>';print_r($res);exit;
// 		if ($this->admin_permission() == FALSE) {
// 			redirect(base_url() . 'admin/login', 'refresh');
// 		} else {
// 			$page_data['title'] = "Admin || " . $this->system_title;
// 			if ($para1 == "") {
// 				$page_data['top'] = "stories/index.php";
// 				$page_data['folder'] = "stories";
// 				$page_data['file'] = "index.php";
// 				$page_data['bottom'] = "stories/index.php";
// 				$page_data['page_name'] = "stories";
// 				if ($this->session->flashdata('alert') == "approve") {
// 					$page_data['success_alert'] = translate("you_have_successfully_approved_the_story!");
// 				} elseif ($this->session->flashdata('alert') == "unpublish") {
// 					$page_data['danger_alert'] = translate("you_have_successfully_unpublished_the_story!");
// 				} elseif ($this->session->flashdata('alert') == "delete") {
// 					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
// 				} elseif ($this->session->flashdata('alert') == "failed_delete") {
// 					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
// 				} elseif ($this->session->flashdata('alert') == "demo_msg") {
// 					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
// 				}

// 				$this->load->view('back/index', $page_data);
// 			} 
// 			elseif ($para1 == "edit_story") {  
// 					$page_data['top'] 		= "stories/index.php";
// 					$page_data['folder'] 	= "stories";
// 					$page_data['file']	 	= "edit_story.php";
// 					$page_data['bottom'] 	= "stories/index.php";
// 					$page_data['get_story'] = $this->db->get_where("happy_story", array("happy_story_id" => $para2))->row_array();
					
// 					//print_r($page_data);exit;
// 					$page_data['page_name'] = "stories";
// 					$this->load->view('back/index', $page_data);

					 
// 				}
// 				elseif ($para1 == "update_story") {
//     $this->form_validation->set_rules('story_name', 'Story Name', 'required');
//     // $this->form_validation->set_rules('dated', 'Dated', 'required');
//     $this->form_validation->set_rules('description', 'Description', 'required');

//     if ($this->form_validation->run() == FALSE) {
//         $page_data['top']          = "stories/index.php";
//         $page_data['folder']       = "stories";
//         $page_data['file']         = "edit_story.php";
//         $page_data['bottom']       = "stories/index.php";
//         $page_data['page_name']    = "stories";
//         $page_data['form_contents'] = $this->input->post();
//     } else {
//         $data = array(
//             'title'         => $this->input->post('story_name'),
//             'happy_story_id'=> $para2,

//             'description'   => $this->input->post('description'),
//             'program_area'  => $this->input->post('program_area'),
//             'legion_name'   => $this->input->post('legion_name'),
//             'area_name'     => $this->input->post('area_name')
//         );

//         // Upload story_photo
//         if (!empty($_FILES['story_photo']['name'])) {
//             $id = uniqid();
//             $path = $_FILES['story_photo']['name'];
//             $ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
//             $allowed_exts = [".jpg", ".JPG", ".jpeg", ".JPEG", ".png", ".PNG"];

//             if (in_array($ext, $allowed_exts)) {
//                 $this->Crud_model->file_up("story_photo", "happy_story", $id, '', '', $ext);
//                 $images[] = array(
//                     'image' => 'happy_story_' . $id . $ext,
//                     'thumb' => 'happy_story_' . $id . '_thumb' . $ext
//                 );
//                 $data['image'] = json_encode($images);
//             } else {
//                 $this->session->set_flashdata('alert', 'failed_image');
//                 redirect(base_url() . 'admin/stories', 'refresh');
//             }
//         }

//         // Upload activity_photo
//         if (!empty($_FILES['activity_photo']['name'])) {
//             $upload = $this->do_upload('activity_photo');
//             if ($upload['status'] === 'success') {
//                 $data['activity_photo'] = $upload['file_name'];
//             } else {
//                 $this->session->set_flashdata('error', $upload['error']);
//                 redirect('admin/stories/edit_story/' . $para2);
//                 return;
//             }
//         }

//         // Upload press_coverage
//         if (!empty($_FILES['press_coverage']['name'])) {
//             $upload = $this->do_upload('press_coverage');
//             if ($upload['status'] === 'success') {
//                 $data['press_coverage'] = $upload['file_name'];
//             } else {
//                 $this->session->set_flashdata('error', $upload['error']);
//                 redirect('admin/stories/edit_story/' . $para2);
//                 return;
//             }
//         }

//         // Update DB
//         $this->db->where('happy_story_id', $para2);
//         $this->db->update('happy_story', $data);
//         $result = $this->db->affected_rows();

//         if ($result > 0) {
//             $this->session->set_flashdata('success', 'Updated successfully');
//             redirect('admin/stories');
//         } else {
//             $this->session->set_flashdata('failed', 'Failed');
//             redirect('admin/stories');
//         }
//     }

//     // In case of form validation failure or update failure
//     $page_data['top']       = "stories/index.php";
//     $page_data['folder']    = "stories";
//     $page_data['file']      = "edit_story.php";
//     $page_data['bottom']    = "stories/index.php";
//     $page_data['page_name'] = "stories";
//     $page_data['get_story'] = $this->db->get_where("happy_story", array("happy_story_id" => $para2))->row_array();

//     $this->load->view('back/index', $page_data);
// }
// 				elseif ($para1 == "list_data") {
// 					$columns = array(
// 						0 => '',
// 						1 => 'title',
// 						2 => 'date',
// 						3 => 'description',
// 						4 => 'options'
// 					);
// 					$limit = $this->input->post('length');
// 					$start = $this->input->post('start');
				
// 					// Handle sorting
// 					if ($this->input->post('order')[0]['column'] == 0) {
// 						$order = "happy_story_id";
// 						$dir = "desc";
// 					} else {
// 						$order = $columns[$this->input->post('order')[0]['column']];
// 						$dir = $this->input->post('order')[0]['dir'];
// 					}
// 					$table = 'happy_story';

					
				
// 					// Get total records
// 					$totalData = $this->Crud_model->alldata_count($table);
				
// 					$totalFiltered = $totalData;
				
// 					// Fetch data
// 					if (empty($this->input->post('search')['value'])) {
// 						$rows = $this->Crud_model->allstories($table, $limit, $start, $order, $dir);
// 					} else {
// 						$search = $this->input->post('search')['value'];
// 						$rows = $this->Crud_model->story_search($table, $limit, $start, $search, $order, $dir);
// 						$totalFiltered = $this->Crud_model->story_search_count($table, $search);
// 					}
				
// 					$data = array();
// 					if (!empty($rows)) {
// 						foreach ($rows as $row) {
// 							// Format activity_photo as an img tag
// 							$story_image = $row->activity_photo && file_exists('Uploads/happy_story_image/' . $row->activity_photo) ?
// 								"<img src='" . base_url('Uploads/happy_story_image/' . $row->activity_photo) . "' class='img-sm' height='30' width='30' alt='story image'>" :
// 								"<img src='" . base_url('Uploads/happy_story_image/default_image.jpg') . "' class='img-sm' height='30' width='30' alt='default image'>";
				
// 							// Approval button based on role
// 							$role_id = $this->session->userdata('role_id');
// 							$national_role_ids = [1, 3, 4, 5, 6];
// 							$is_national_role = in_array((int)$role_id, $national_role_ids);
// 							$approve_button = '';
// 							if ($is_national_role) {
// 								if ($row->approval_status == 1) {
// 									$approve_button = "
// 										<button data-target='#approval_modal' data-toggle='modal' class='btn btn-dark btn-xs add-tooltip'
// 											title='" . translate('unpublish') . "'
// 											onclick='approval(0, {$row->happy_story_id})'>
// 											<i class='fa fa-close'></i>
// 										</button>";
// 								} elseif ($row->approval_status == 0) {
// 									$approve_button = "
// 										<button data-target='#approval_modal' data-toggle='modal' class='btn btn-success btn-xs add-tooltip'
// 											title='" . translate('approve') . "'
// 											onclick='approval(1, {$row->happy_story_id})'>
// 											<i class='fa fa-check'></i>
// 										</button>";
// 								}
// 							}
				
// 							// Prepare DataTable row
// 							$nestedData = [];
// 							$nestedData['partner_name'] =  $row->title;
// 							$nestedData['image'] = $story_image;
// 							$nestedData['title'] = $row->title;
// 							$nestedData['date'] = $row->date;


// 							$nestedData['description'] = $row->description;
// 							$nestedData['options'] = $approve_button . "
// 								<a href='" . base_url('admin/stories/view_story/' . $row->happy_story_id) . "' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('view') . "'><i class='fa fa-eye'></i></a>
// 								<a href='" . base_url('admin/stories/edit_story/' . $row->happy_story_id) . "' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('edit') . "'><i class='fa fa-edit'></i></a>
// 								<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_story(" . $row->happy_story_id . ")'><i class='fa fa-trash'></i></button>";
				
// 							$data[] = $nestedData;
// 						}
// 					}
				
// 					$json_data = array(
// 						"draw" => intval($this->input->post('draw')),
// 						"recordsTotal" => intval($totalData),
// 						"recordsFiltered" => intval($totalFiltered),
// 						"data" => $data
// 					);
// 					echo json_encode($json_data);
// 				}
// 			elseif ($para1 == "approval") {
// 				if ($para2 == 0) {
// 					$data['approval_status'] = 1;
// 					$this->session->set_flashdata('alert', 'approve');
// 				} elseif ($para2 == 1) {
// 					$data['approval_status'] = 0;
// 					$this->session->set_flashdata('alert', 'unpublish');
// 				}
// 				$this->db->where('happy_story_id', $para3);
// 				$this->db->update('happy_story', $data);
// 				recache();
// 			} elseif ($para1 == "view_story") {
// 				$page_data['top'] = "stories/stories.php";
// 				$page_data['folder'] = "stories";
// 				$page_data['file'] = "view_story.php";
// 				$page_data['bottom'] = "stories/stories.php";
// 				$page_data['get_story'] = $this->db->get_where("happy_story", array("happy_story_id" => $para2))->result();
// 				$page_data['page_name'] = "stories";
// 				$this->load->view('back/index', $page_data);
// 			} elseif ($para1 == "delete") {
// 				if (demo()) {
// 					$this->session->set_flashdata('alert', 'demo_msg');
// 					return false;
// 				}
// 				$img =  $this->db->get_where("happy_story", array("happy_story_id" => $para2))->row()->image;
// 				$img = json_decode($img, true);
// 				unlink('uploads/happy_story_image/' . $img[0]['img']);
// 				unlink('uploads/happy_story_image/' . $img[0]['thumb']);
// 				$video_exist = $this->db->get_where("story_video", array("story_id" => $para2))->result();
// 				if ($video_exist) {
// 					$vid_type = $this->db->get_where("story_video", array("story_id" => $para2))->row()->type;
// 					if ($vid_type == 'upload') {
// 						$video_src = $this->db->get_where("story_video", array("story_id" => $para2))->row()->video_src;
// 						unlink($video_src);
// 					}
// 					$this->db->where('story_id', $para2);
// 					$this->db->delete('story_video');
// 				}
// 				$this->db->where('happy_story_id', $para2);
// 				$result = $this->db->delete('happy_story');
// 				recache();
// 				if ($result) {
// 					$this->session->set_flashdata('alert', 'delete');
// 				} else {
// 					$this->session->set_flashdata('alert', 'failed_delete');
// 				}
// 			}
// 			else if ($para1 == "add_story") {
// 				$page_data['top'] = "stories/index.php";
// 				$page_data['folder'] = "stories";
// 				$page_data['file'] = "add_story.php";
// 				$page_data['bottom'] = "stories/index.php";
// 				$page_data['page_name'] = "stories";
			
// 				// Get admin_id from session
// 				$admin_id = $this->session->userdata('admin_id');
// 				$admin_name = $this->session->userdata('name');
// 				// $admin_id = 26;
// 				// Fetch legion data for autofill based on session admin_id
// 				$legion_info = $this->Crud_model->get_legion_and_area_by_admin($admin_id);

// 				if (!$legion_info['status']) {
// 					$this->session->set_flashdata('failed', $legion_info['message']);
// 					redirect('admin/stories');
// 				}
				
// 				$page_data['legion'] = $legion_info;
// 				$page_data['legion']['admin_name'] = $admin_name;
			
// 				$this->load->view('back/index', $page_data);
// 			}
			
// 			else if ($para1 == "edit_story") {
// 				$page_data['top'] = "stories/index.php";
// 				$page_data['folder'] = "stories";
// 				$page_data['file'] = "edit_story.php";
// 				$page_data['bottom'] = "stories/index.php";
// 				$page_data['guruji_photo_id'] = $para2;
// 				$page_data['page_name'] = "stories";
	
// 				$this->load->view('back/index', $page_data);
// 			}
// 		}
// 	}


function stories($para1 = "", $para2 = "", $para3 = "")
{ 
    // Start output buffering
    ob_start();
    
    // Fetch admin ID from session
    $admin_id = $this->session->userdata('admin_id');
    if (!$admin_id) {
        log_message('error', 'No admin_id in session, redirecting to login');
        ob_end_clean();
        redirect(base_url() . 'admin/login', 'refresh');
        return;
    }
    log_message('debug', 'Stories function invoked | Admin ID: ' . $admin_id);
    
    // Get legion details
    $legion_info = $this->Crud_model->get_legion_and_area_by_admin($admin_id);
    
    // Initialize member query result
    $res = [];
    
    // Filter members by legion_id (check if column exists)
    if ($legion_info['status'] && !empty($legion_info['legion_id'])) {
        $this->db->where('membership', 2);
        $this->db->where('status', 'approved');
        $columns = $this->db->list_fields('member');
        if (in_array('legion_id', $columns)) {
            $this->db->where('member.legion_id', $legion_info['legion_id']);
        } else {
            log_message('debug', 'legion_id column not found in member table, skipping filter');
        }
        $query = $this->db->get('member');
        log_message('debug', 'Member query: ' . $this->db->last_query());

        if ($query->num_rows() > 0) {
            $res = $query->result();
            log_message('debug', 'Member query returned ' . $query->num_rows() . ' rows for legion_id: ' . $legion_info['legion_id']);
        } else {
            log_message('debug', 'No members found for legion_id: ' . $legion_info['legion_id']);
        }
    } else {
        log_message('debug', 'No legion assigned for admin_id: ' . $admin_id . ' | Message: ' . ($legion_info['message'] ?? 'Unknown error'));
    }

    // Check admin permissions
    if ($this->admin_permission() === FALSE) {
        log_message('debug', 'Admin permission check failed, redirecting to login');
        ob_end_clean();
        redirect(base_url() . 'admin/login', 'refresh');
        return;
    }

    $page_data['title'] = "Admin || " . $this->system_title;
    if ($para1 == "") {
        $page_data['top'] = "stories/index.php";
        $page_data['folder'] = "stories";
        $page_data['file'] = "index.php";
        $page_data['bottom'] = "stories/index.php";
        $page_data['page_name'] = "stories";
        if ($this->session->flashdata('alert') == "approve") {
            $page_data['success_alert'] = translate("you_have_successfully_approved_the_story!");
        } elseif ($this->session->flashdata('alert') == "unpublish") {
            $page_data['danger_alert'] = translate("you_have_successfully_unpublished_the_story!");
        } elseif ($this->session->flashdata('alert') == "delete") {
            $page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
        } elseif ($this->session->flashdata('alert') == "failed_delete") {
            $page_data['danger_alert'] = translate("failed_to_delete_the_data!");
        } elseif ($this->session->flashdata('alert') == "demo_msg") {
            $page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
        }

        ob_end_clean();
        $this->load->view('back/index', $page_data);
    } 
    elseif ($para1 == "edit_story") {  
        $page_data['top'] = "stories/index.php";
        $page_data['folder'] = "stories";
        $page_data['file'] = "edit_story.php";
        $page_data['bottom'] = "stories/index.php";
        if ($legion_info['status']) {
            $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
            $page_data['get_story'] = $this->db->get_where("happy_story", array("happy_story_id" => $para2))->row_array();
            log_message('debug', 'Edit story query: ' . $this->db->last_query());
            if (!$page_data['get_story']) {
                log_message('debug', 'Story not found or unauthorized for happy_story_id: ' . $para2 . ', legion_id: ' . $legion_info['legion_id']);
                $this->session->set_flashdata('failed', 'Story not found or not authorized.');
                ob_end_clean();
                redirect(base_url() . 'admin/stories');
            }
        } else {
            log_message('debug', 'No legion assigned for edit_story | Message: ' . $legion_info['message']);
            $this->session->set_flashdata('failed', $legion_info['message']);
            ob_end_clean();
            redirect(base_url() . 'admin/stories');
        }
        $page_data['page_name'] = "stories";
        ob_end_clean();
        $this->load->view('back/index', $page_data);
    }
    elseif ($para1 == "update_story") {
        $this->form_validation->set_rules('story_name', 'Story Name', 'required');
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == FALSE) {
            $page_data['top'] = "stories/index.php";
            $page_data['folder'] = "stories";
            $page_data['file'] = "edit_story.php";
            $page_data['bottom'] = "stories/index.php";
            $page_data['page_name'] = "stories";
            $page_data['form_contents'] = $this->input->post();
        } else {
            if ($legion_info['status']) {
                $data = array(
                    'title' => $this->input->post('story_name'),
                    'description' => $this->input->post('description'),
                    'program_area' => $this->input->post('program_area'),
                    'legion_name' => $this->input->post('legion_name'),
                    'area_name' => $this->input->post('area_name')
                );

                if (!empty($_FILES['story_photo']['name'])) {
                    $id = uniqid();
                    $path = $_FILES['story_photo']['name'];
                    $ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
                    $allowed_exts = [".jpg", ".JPG", ".jpeg", ".JPEG", ".png", ".PNG"];
                    if (in_array($ext, $allowed_exts)) {
                        $this->Crud_model->file_up("story_photo", "happy_story", $id, '', '', $ext);
                        $data['image'] = 'happy_story_' . $id . $ext;
                    } else {
                        $this->session->set_flashdata('alert', 'failed_image');
                        ob_end_clean();
                        redirect(base_url() . 'admin/stories', 'refresh');
                    }
                }

                if (!empty($_FILES['activity_photo']['name'])) {
                    $upload = $this->do_upload('activity_photo');
                    if ($upload['status'] === 'success') {
                        $data['activity_photo'] = $upload['file_name'];
                    } else {
                        $this->session->set_flashdata('error', $upload['error']);
                        ob_end_clean();
                        redirect('admin/stories/edit_story/' . $para2);
                        return;
                    }
                }

                if (!empty($_FILES['press_coverage']['name'])) {
                    $upload = $this->do_upload('press_coverage');
                    if ($upload['status'] === 'success') {
                        $data['press_coverage'] = $upload['file_name'];
                    } else {
                        $this->session->set_flashdata('error', $upload['error']);
                        ob_end_clean();
                        redirect('admin/stories/edit_story/' . $para2);
                        return;
                    }
                }

                $this->db->where('happy_story_id', $para2);
                $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                $this->db->update('happy_story', $data);
                log_message('debug', 'Update story query: ' . $this->db->last_query());
                $result = $this->db->affected_rows();

                if ($result > 0) {
                    log_message('debug', 'Story updated successfully for happy_story_id: ' . $para2);
                    $this->session->set_flashdata('success', 'Updated successfully');
                    ob_end_clean();
                    redirect('admin/stories');
                } else {
                    log_message('debug', 'Failed to update story for happy_story_id: ' . $para2 . ' or unauthorized');
                    $this->session->set_flashdata('failed', 'Failed to update or unauthorized.');
                    ob_end_clean();
                    redirect('admin/stories');
                }
            } else {
                log_message('debug', 'No legion assigned for update_story | Message: ' . $legion_info['message']);
                $this->session->set_flashdata('failed', $legion_info['message']);
                ob_end_clean();
                redirect('admin/stories');
            }
        }

        $page_data['top'] = "stories/index.php";
        $page_data['folder'] = "stories";
        $page_data['file'] = "edit_story.php";
        $page_data['bottom'] = "stories/index.php";
        $page_data['page_name'] = "stories";
        if ($legion_info['status']) {
            $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
            $page_data['get_story'] = $this->db->get_where("happy_story", array("happy_story_id" => $para2))->row_array();
        }
        ob_end_clean();
        $this->load->view('back/index', $page_data);
    }
    elseif ($para1 == "list_data") {
        try {
            $columns = array(
                0 => '',
                1 => 'title',
                2 => 'date',
                3 => 'description',
                4 => 'options'
            );
            $limit = $this->input->post('length');
            $start = $this->input->post('start');

            if ($this->input->post('order')[0]['column'] == 0) {
                $order = "happy_story_id";
                $dir = "desc";
            } else {
                $order = $columns[$this->input->post('order')[0]['column']];
                $dir = $this->input->post('order')[0]['dir'];
            }
            $table = 'happy_story';

            $json_data = array(
                'draw' => intval($this->input->post('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            );

            if ($legion_info['status'] && !empty($legion_info['legion_id'])) {
                // Reset query builder state
                $this->db->reset_query();
                // Fallback queries with explicit table qualification
                if (!method_exists($this->Crud_model, 'alldata_count')) {
                    $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                    $this->db->from($table);
                    $totalData = $this->db->count_all_results();
                } else {
                    // Assume Crud_model method handles legion_id internally or via query builder
                    $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                    $totalData = $this->Crud_model->alldata_count($table);
                }
                $totalFiltered = $totalData;
                log_message('debug', 'Total stories query: ' . $this->db->last_query());
                log_message('debug', 'Total stories for legion_id ' . $legion_info['legion_id'] . ': ' . $totalData);

                $this->db->reset_query();
                if (empty($this->input->post('search')['value'])) {
                    if (!method_exists($this->Crud_model, 'allstories')) {
                        $this->db->select('happy_story.*');
                        $this->db->from($table);
                        $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                        $this->db->limit($limit, $start);
                        $this->db->order_by($order, $dir);
                        $rows = $this->db->get()->result();
                    } else {
                        $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                        $rows = $this->Crud_model->allstories($table, $limit, $start, $order, $dir);
                    }
                } else {
                    $search = $this->input->post('search')['value'];
                    if (!method_exists($this->Crud_model, 'story_search')) {
                        $this->db->select('happy_story.*');
                        $this->db->from($table);
                        $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                        $this->db->group_start();
                        $this->db->like('happy_story.title', $search);
                        $this->db->or_like('happy_story.description', $search);
                        $this->db->group_end();
                        $this->db->limit($limit, $start);
                        $this->db->order_by($order, $dir);
                        $rows = $this->db->get()->result();
                    } else {
                        $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                        $rows = $this->Crud_model->story_search($table, $limit, $start, $search, $order, $dir);
                    }
                    $this->db->reset_query();
                    if (!method_exists($this->Crud_model, 'story_search_count')) {
                        $this->db->from($table);
                        $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                        $this->db->group_start();
                        $this->db->like('happy_story.title', $search);
                        $this->db->or_like('happy_story.description', $search);
                        $this->db->group_end();
                        $totalFiltered = $this->db->count_all_results();
                    } else {
                        $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                        $totalFiltered = $this->Crud_model->story_search_count($table, $search);
                    }
                }
                log_message('debug', 'Data query: ' . $this->db->last_query());
                log_message('debug', 'Fetched ' . (is_array($rows) ? count($rows) : 0) . ' stories for legion_id: ' . $legion_info['legion_id']);
            } else {
                log_message('debug', 'No legion assigned for list_data | Message: ' . ($legion_info['message'] ?? 'Unknown error'));
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode($json_data);
                return;
            }

            $data = array();
            if (!empty($rows) && is_array($rows)) {
                foreach ($rows as $row) {
                    $story_image = $row->activity_photo && file_exists('uploads/happy_story_image/' . $row->activity_photo) ?
                        "<img src='" . base_url('uploads/happy_story_image/' . $row->activity_photo) . "' class='img-sm' height='30' width='30' alt='story image'>" :
                        "<img src='" . base_url('uploads/happy_story_image/default_image.jpg') . "' class='img-sm' height='30' width='30' alt='default image'>";

                    $role_id = $this->session->userdata('role_id') ?? 0;
                    $national_role_ids = [1, 3, 4, 5, 6];
                    $is_national_role = in_array((int)$role_id, $national_role_ids);
                    $approve_button = '';
                    if ($is_national_role) {
                        if ($row->approval_status == 1) {
                            $approve_button = "
                                <button data-target='#approval_modal' data-toggle='modal' class='btn btn-dark btn-xs add-tooltip'
                                    title='" . translate('unpublish') . "'
                                    onclick='approval(0, {$row->happy_story_id})'>
                                    <i class='fa fa-close'></i>
                                </button>";
                        } elseif ($row->approval_status == 0) {
                            $approve_button = "
                                <button data-target='#approval_modal' data-toggle='modal' class='btn btn-success btn-xs add-tooltip'
                                    title='" . translate('approve') . "'
                                    onclick='approval(1, {$row->happy_story_id})'>
                                    <i class='fa fa-check'></i>
                                </button>";
                        }
                    }

                    $nestedData = [];
                    $nestedData['partner_name'] = $row->partner_name ?? '';
                    $nestedData['image'] = $story_image;
                    $nestedData['title'] = $row->title ?? '';
                    $nestedData['date'] = $row->date ?? '';
                    $nestedData['description'] = $row->description ?? '';
                    $nestedData['options'] = $approve_button . "
                        <a href='" . base_url('admin/stories/view_story/' . $row->happy_story_id) . "' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('view') . "'><i class='fa fa-eye'></i></a>
                        <a href='" . base_url('admin/stories/edit_story/' . $row->happy_story_id) . "' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('edit') . "'><i class='fa fa-edit'></i></a>
                        <button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_story(" . $row->happy_story_id . ")'><i class='fa fa-trash'></i></button>";

                    $data[] = $nestedData;
                }
            }

            $json_data = array(
                'draw' => intval($this->input->post('draw')),
                'recordsTotal' => intval($totalData),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data
            );

            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode($json_data);
        } catch (Exception $e) {
            log_message('error', 'Exception in list_data: ' . $e->getMessage());
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }
    elseif ($para1 == "approval") {
        if ($legion_info['status']) {
            if ($para2 == 0) {
                $data['approval_status'] = 1;
                $this->session->set_flashdata('alert', 'approve');
            } elseif ($para2 == 1) {
                $data['approval_status'] = 0;
                $this->session->set_flashdata('alert', 'unpublish');
            }
            $this->db->where('happy_story_id', $para3);
            $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
            $this->db->update('happy_story', $data);
            log_message('debug', 'Approval query: ' . $this->db->last_query());
            $result = $this->db->affected_rows();
            if ($result > 0) {
                log_message('debug', 'Approval status updated for happy_story_id: ' . $para3);
            } else {
                log_message('debug', 'Failed to update approval status for happy_story_id: ' . $para3);
            }
            ob_end_clean();
            recache();
        } else {
            log_message('debug', 'No legion assigned for approval | Message: ' . $legion_info['message']);
            $this->session->set_flashdata('failed', $legion_info['message']);
            ob_end_clean();
            redirect(base_url() . 'admin/stories');
        }
    }
    elseif ($para1 == "view_story") {
        $page_data['top'] = "stories/stories.php";
        $page_data['folder'] = "stories";
        $page_data['file'] = "view_story.php";
        $page_data['bottom'] = "stories/stories.php";
        if ($legion_info['status']) {
            $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
            $page_data['get_story'] = $this->db->get_where("happy_story", array("happy_story_id" => $para2))->result();
            log_message('debug', 'View story query: ' . $this->db->last_query());
            if (empty($page_data['get_story'])) {
                log_message('debug', 'Story not found or unauthorized for happy_story_id: ' . $para2 . ', legion_id: ' . $legion_info['legion_id']);
                $this->session->set_flashdata('failed', 'Story not found or not authorized.');
                ob_end_clean();
                redirect(base_url() . 'admin/stories');
            }
        } else {
            log_message('debug', 'No legion assigned for view_story | Message: ' . $legion_info['message']);
            $this->session->set_flashdata('failed', $legion_info['message']);
            ob_end_clean();
            redirect(base_url() . 'admin/stories');
        }
        $page_data['page_name'] = "stories";
        ob_end_clean();
        $this->load->view('back/index', $page_data);
    }
    elseif ($para1 == "delete") {
        if (demo()) {
            $this->session->set_flashdata('alert', 'demo_msg');
            ob_end_clean();
            return false;
        }
        if ($legion_info['status']) {
            $this->db->where('happy_story_id', $para2);
            $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
            $story = $this->db->get('happy_story')->row();
            log_message('debug', 'Delete story query: ' . $this->db->last_query());
            if ($story) {
                if ($story->image && file_exists('uploads/happy_story_image/' . $story->image)) {
                    unlink('uploads/happy_story_image/' . $story->image);
                }
                if ($story->activity_photo && file_exists('uploads/happy_story_image/' . $story->activity_photo)) {
                    unlink('uploads/happy_story_image/' . $story->activity_photo);
                }
                if ($story->press_coverage && file_exists('uploads/happy_story_image/' . $story->press_coverage)) {
                    unlink('uploads/happy_story_image/' . $story->press_coverage);
                }
                $video_exist = $this->db->get_where("story_video", array("story_id" => $para2))->result();
                if ($video_exist) {
                    $vid_type = $this->db->get_where("story_video", array("story_id" => $para2))->row()->type;
                    if ($vid_type == 'upload') {
                        $video_src = $this->db->get_where("story_video", array("story_id" => $para2))->row()->video_src;
                        if (file_exists($video_src)) {
                            unlink($video_src);
                        }
                    }
                    $this->db->where('story_id', $para2);
                    $this->db->delete('story_video');
                }
                $this->db->where('happy_story_id', $para2);
                $this->db->where('happy_story.legion_id', $legion_info['legion_id']);
                $result = $this->db->delete('happy_story');
                log_message('debug', 'Delete query: ' . $this->db->last_query());
                recache();
                if ($result) {
                    log_message('debug', 'Story deleted successfully for happy_story_id: ' . $para2);
                    $this->session->set_flashdata('alert', 'delete');
                } else {
                    log_message('debug', 'Failed to delete story for happy_story_id: ' . $para2);
                    $this->session->set_flashdata('alert', 'failed_delete');
                }
            } else {
                log_message('debug', 'Story not found or unauthorized for deletion, happy_story_id: ' . $para2);
                $this->session->set_flashdata('alert', 'failed_delete');
            }
        } else {
            log_message('debug', 'No legion assigned for delete | Message: ' . $legion_info['message']);
            $this->session->set_flashdata('failed', $legion_info['message']);
        }
        ob_end_clean();
        redirect(base_url() . 'admin/stories');
    }
    elseif ($para1 == "add_story") {
        $page_data['top'] = "stories/index.php";
        $page_data['folder'] = "stories";
        $page_data['file'] = "add_story.php";
        $page_data['bottom'] = "stories/index.php";
        $page_data['page_name'] = "stories";
        $admin_name = $this->session->userdata('name');
        if (!$legion_info['status']) {
            log_message('debug', 'No legion assigned for add_story | Message: ' . $legion_info['message']);
            $this->session->set_flashdata('failed', $legion_info['message']);
            ob_end_clean();
            redirect('admin/stories');
        }
        $page_data['legion'] = $legion_info;
        $page_data['legion']['admin_name'] = $admin_name;
        ob_end_clean();
        $this->load->view('back/index', $page_data);
    }
}



	private function set_upload_happy_story_image()
	{

		$config = array();
		$config['upload_path'] = 'uploads/happy_story_image';
		$config['allowed_types'] = 'jpg|png|jpeg|JPG';

		$config['overwrite']     = FALSE;
		return $config;
	}


	public function add_story_details() 
{
	try {
		$this->load->library('form_validation');
		$this->load->library('upload');

		$admin_id = $this->session->userdata('admin_id');
		$role_id = $this->session->userdata('role_id');

		if (!$role_id || !in_array($role_id, [2, 8])) {
			$this->session->set_flashdata('failed', ['general' => 'Only Presidents and Secretaries can add stories.']);
			redirect(base_url('admin/stories'), 'refresh');
			return;
		}
		if ($role_id == 2) {
    $partner_name = 'President';
} elseif ($role_id == 8) {
    $partner_name = 'Secretary';
} else {
    $partner_name = NULL;
}

		$this->db->select('legion_id');
		$this->db->from('admin_legion');
		$this->db->where('admin_id', $admin_id);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$result = $query->row();
			$legion_id = $result->legion_id;
		} else {
			$this->session->set_flashdata('failed', ['general' => 'Legion ID not found for this admin.']);
			redirect('admin/stories/add_story');
			return;
		}

		// ✅ Form validation including program_date
		$this->form_validation->set_rules('program_name', 'Program Name', 'required', ['required' => 'Program Name is required.']);
		$this->form_validation->set_rules('program_area', 'Program Area', 'required', ['required' => 'Program Area is required.']);
		$this->form_validation->set_rules('date', 'Date', 'required', ['required' => 'Date is required.']);
		$this->form_validation->set_rules('program_date', 'Program Date', 'required', ['required' => 'Program Date is required.']);
		$this->form_validation->set_rules('program_details', 'Program Details', 'required|min_length[10]', 
			[
				'required' => 'Program Details is required.',
				'min_length' => 'Program Details must be at least 10 characters long.'
			]);

		$errors = [];

		if ($this->form_validation->run() == FALSE) {
			$errors['program_name'] = form_error('program_name');
			$errors['program_area'] = form_error('program_area');
			$errors['date'] = form_error('date');
			$errors['program_date'] = form_error('program_date');
			$errors['program_details'] = form_error('program_details');
			$this->session->set_flashdata('failed', $errors);
			redirect('admin/stories/add_story');
			return;
		}

		$upload_path = FCPATH . 'uploads/happy_story_image/';
		if (!is_dir($upload_path)) {
			mkdir($upload_path, 0755, true);
		}

		$config = [
			'upload_path' => $upload_path,
			'allowed_types' => 'jpg|jpeg|png|pdf',
			'max_size' => 10240,
			'file_ext_tolower' => TRUE
		];

		$this->upload->initialize($config);
		if (!$this->upload->do_upload('activity_photo')) {
			$errors['activity_photo'] = $this->upload->display_errors('', '');
			$this->session->set_flashdata('failed', $errors);
			redirect('admin/stories/add_story');
			return;
		}
		$activity_photo = $this->upload->data('file_name');
		$activity_photo_size = $this->upload->data('file_size');
		$activity_photo_ext = strtolower($this->upload->data('file_ext'));

		if ($activity_photo_size < 10 || ($activity_photo_size > 5120 && $activity_photo_ext != '.pdf')) {
			$errors['activity_photo'] = 'Activity Photo size must be between 10KB and 5MB.';
			unlink($upload_path . $activity_photo);
			$this->session->set_flashdata('failed', $errors);
			redirect('admin/stories/add_story');
			return;
		}

		$press_coverage = NULL;
		if (!empty($_FILES['press_coverage']['name'])) {
			$this->upload->initialize($config);
			if (!$this->upload->do_upload('press_coverage')) {
				$errors['press_coverage'] = $this->upload->display_errors('', '');
				unlink($upload_path . $activity_photo);
				$this->session->set_flashdata('failed', $errors);
				redirect('admin/stories/add_story');
				return;
			}
			$press_coverage = $this->upload->data('file_name');
			$press_coverage_size = $this->upload->data('file_size');
			$press_coverage_ext = strtolower($this->upload->data('file_ext'));

			if ($press_coverage_size < 10 
				|| ($press_coverage_size > 10240 && $press_coverage_ext == '.pdf') 
				|| ($press_coverage_size > 5120 && $press_coverage_ext != '.pdf')) {
				$errors['press_coverage'] = 'Press Coverage size must be between 10KB and 5MB for images or 50KB and 10MB for PDFs.';
				unlink($upload_path . $activity_photo);
				unlink($upload_path . $press_coverage);
				$this->session->set_flashdata('failed', $errors);
				redirect('admin/stories/add_story');
				return;
			}

			if ($press_coverage_ext == '.pdf' && $press_coverage_size < 50) {
				$errors['press_coverage'] = 'Press Coverage PDF size must be at least 50KB.';
				unlink($upload_path . $activity_photo);
				unlink($upload_path . $press_coverage);
				$this->session->set_flashdata('failed', $errors);
				redirect('admin/stories/add_story');
				return;
			}
		}

		$admin_name = $this->session->userdata('name');
		log_message('info', 'Admin Name: ' . $admin_name);

		// ✅ Add program_date to the data array
		$story_data = [
			'legion_id' => $legion_id,
			'date' => $this->input->post('date'),
			'program_date' => $this->input->post('program_date'),
			'title' => $this->input->post('program_name'),
			'description' => $this->input->post('program_details'),
			'image' => '',
			'activity_photo' => $activity_photo,
			'press_coverage' => $press_coverage,
			'partner_name' => NULL,
			'posted_by' => $admin_id,
			'member_name' => $admin_name,
			'legion_name' => $this->input->post('legion_name'),
			'area_name' => $this->input->post('area'),
			'program_area' => $this->input->post('program_area'),
			 'partner_name'   => $partner_name,
			'approval_status' => 0
		];

		log_message('info', '$story_data ' . $admin_name);

		if ($this->db->insert('happy_story', $story_data)) {
			$this->session->set_flashdata('success', 'Story added successfully!');
			redirect('admin/stories');
			return;
		} else {
			$errors['general'] = 'Failed to add story to database.';
			unlink($upload_path . $activity_photo);
			if ($press_coverage) {
				unlink($upload_path . $press_coverage);
			}
			$this->session->set_flashdata('failed', $errors);
			redirect('admin/stories');
			return;
		}
	} catch (Exception $e) {
		$errors['general'] = 'An unexpected error occurred. Please try again.';
		$this->session->set_flashdata('failed', $errors);
		redirect('admin/stories/add_story');
	}
}

	
	function send_sms($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] 		= "send_sms/index.php";
				$page_data['folder'] 	= "send_sms";
				$page_data['file'] 		= "index.php";
				$page_data['bottom']	= "send_sms/index.php";
				$page_data['page_name'] = "send_sms";

				if ($this->session->flashdata('alert') == "sms_success") {
					$page_data['success_alert'] = translate("sms_sent_successfully!");
				} elseif ($this->session->flashdata('alert') == "sms_failed") {
					$page_data['danger_alert'] = translate("no_mobile_number_found_to_send_sms!");
				}

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "do_send") {
				$send_by 		= $this->input->post('send_by');
				$members_type 	= $this->input->post('member');
				$message 		= $this->input->post('msg');

				if ($members_type == 'free') {
					$this->db->select('mobile');
					$this->db->where('mobile IS NOT NULL', null, false);
					$this->db->where('membership', 1);
				} elseif ($members_type == 'premium') {
					$this->db->select('mobile');
					$this->db->where('mobile IS NOT NULL', null, false);
					$this->db->where('membership', 2);
				} elseif ($members_type == 'all') {
					$this->db->select('mobile');
					$this->db->where('mobile IS NOT NULL', null, false);
				}


				$recievers_phone = $this->db->get('member')->result_array();
				if (!empty($recievers_phone)) {
					$send_by = $this->input->post('send_by');
					if ($send_by == 'twilio') {
						$this->load->model('sms_model');

						foreach ($recievers_phone as $reciever_phone) {

							$this->sms_model->send_sms_via_twilio($message, $reciever_phone['mobile']);
						}
					} elseif ($send_by == 'msg91') {
						$this->load->model('sms_model');

						foreach ($recievers_phone as $reciever_phone) {

							$this->sms_model->send_sms_via_msg91($message, $reciever_phone['mobile']);
						}
					}

					$this->session->set_flashdata('alert', 'sms_success');
					redirect('admin/send_sms', 'refresh');
				} else {
					$this->session->set_flashdata('alert', 'sms_failed');
				}
			}
		}
	}


	// ============================== Earnings Section ==============================//////////////////////////////

	function earnings($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
	
			if (!empty($_POST['earningStatus'])) {
				$state = $_POST['earningStatus'];
				$this->session->set_userdata('earning_status', $state);
			}
			$page_data['title'] = "Admin || " . $this->system_title;
			
			if ($para1 == "") {
				$page_data['top'] = "earnings/index.php";
				$page_data['folder'] = "earnings";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "earnings/index.php";
				$page_data['page_name'] = "earnings";
				
				// Flash messages
				if ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				} elseif ($this->session->flashdata('alert') == "payment_accepted") {
					$page_data['success_alert'] = translate("payment_accepted_successfully!");
				} elseif ($this->session->flashdata('alert') == "payment_accepted_error") {
					$page_data['danger_alert'] = translate("something_went_wrong!");
				} elseif ($this->session->flashdata('alert') == "bulk_payment_success") {
					$page_data['success_alert'] = translate("bulk_payment_completed_successfully!");
				} elseif ($this->session->flashdata('alert') == "bulk_payment_error") {
					$page_data['danger_alert'] = translate("bulk_payment_failed!");
				}
				
				$this->load->view('back/index', $page_data);
			} 
			elseif ($para1 == "list_data") {
				log_message('debug', "Earnings list_data called");
	
				$columns = array(
					0 => 'package_payment_id',
					1 => 'member_first_name',
					2 => 'member_last_name',
					3 => 'payment_type',
					4 => 'amount',
					5 => 'package_name',
					6 => 'payment_status',
					7 => 'purchase_datetime',
					8 => 'start_date',
					9 => 'end_date',
				);
				
				$limit = $this->input->post('length');
				$start = $this->input->post('start');
	
				if ($this->input->post('order')[0]['column'] == 0 || $this->input->post('order')[0]['column'] == 1) {
					$order = "package_payment_id";
					$dir = "desc";
				} else {
					$order = $columns[$this->input->post('order')[0]['column']];
					$dir = $this->input->post('order')[0]['dir'];
				}
				
				$table = 'package_payment';
				$totalData = $this->Crud_model->alldata_count($table);
				$totalFiltered = $totalData;
	
				if (empty($this->input->post('search')['value'])) {
					$rows = $this->Crud_model->allearnings($table, $limit, $start, $order, $dir);
				} else {
					$search = $this->input->post('search')['value'];
					$rows = $this->Crud_model->earning_search($table, $limit, $start, $search, $order, $dir);
					$totalFiltered = $this->Crud_model->earning_search_count($table, $search);
				}
	
				$data = array();
				if (!empty($rows)) {
					$i = ($dir == 'asc') ? ($start + 1) : ($totalFiltered - $start);
					
					foreach ($rows as $row) {
						// ✅ Add checkbox column for bulk selection
						$nestedData['checkbox'] = "<input type='checkbox' class='earning-checkbox' 
							data-id='" . $row->package_payment_id . "' 
							data-amount='" . $row->amount . "' 
							data-member='" . htmlspecialchars($row->member_first_name . " " . $row->member_last_name) . "' 
							data-package='" . htmlspecialchars($row->package_name) . "' 
							data-status='" . $row->payment_status . "'>";
						
						$nestedData['#'] = $i;
						$nestedData['member_name'] = $row->member_first_name . ' ' . $row->member_last_name;
						$nestedData['date'] = date('d/m/Y h:i A', $row->purchase_datetime);
						
						// Payment type badges
						if ($row->payment_type == 'payUMoney') {
							$nestedData['payment_type'] = "<center><span class='badge badge-success'>PayUMoney</span></center>";
						} elseif ($row->payment_type == 'Stripe') {
							$nestedData['payment_type'] = "<center><span class='badge badge-info'>" . $row->payment_type . "</span></center>";
						} elseif ($row->payment_type == 'Paypal') {
							$nestedData['payment_type'] = "<center><span class='badge badge-primary'>" . $row->payment_type . "</span></center>";
						} elseif (strtolower($row->payment_type) == 'instamojo') {
							$nestedData['payment_type'] = "<center><span class='badge badge-warning'>" . $row->payment_type . "</span></center>";
						} elseif (in_array($row->payment_type, ['custom_payment_method_1', 'custom_payment_method_2', 'custom_payment_method_3', 'custom_payment_method_4'])) {
							$nestedData['payment_type'] = "<center><span class='badge badge-warning'>" . $row->custom_payment_method_name . "</span></center>";
						} else {
							$nestedData['payment_type'] = "<center><span class='badge badge-default'>" . $row->payment_type . "</span></center>";
						}
						
						$nestedData['amount'] = currency('', 'def') . $row->amount;
						$nestedData['package'] = $row->package_name;
						
						// Payment status
						if ($row->payment_status == 'paid') {
							$nestedData['status'] = "<center><span class='badge badge-success' style='width:60px'>" . translate($row->payment_status) . "</span></center>";
						} elseif ($row->payment_status == 'due') {
							$nestedData['status'] = "<center><span class='badge badge-danger' style='width:60px'>" . translate($row->payment_status) . "</span></center>";
						}
						
						$nestedData['start_date'] = ($row->start_date == 0) ? 0 : date('d/m/y', strtotime($row->start_date));
						$nestedData['end_date'] = ($row->end_date == 0) ? 0 : date('d/m/y', strtotime($row->end_date));
				
						// ✅ Build options column with conditional invoice download button
						$nestedData['options'] = "<button data-target='#earnings_modal' data-toggle='modal' class='btn btn-primary btn-xs add-tooltip' data-placement='top' title='" . translate('view') . " Details' onclick='get_detail($row->package_payment_id)'><i class='fa fa-eye'></i></button> ";
						
						// ✅ Add invoice download button if invoice is generated
						if (!empty($row->invoice_number) && isset($row->invoice_generated) && $row->invoice_generated == 1) {
							$nestedData['options'] .= "<a href='" . base_url('admin/earnings/download_invoice/' . $row->package_payment_id) . "' class='btn btn-success btn-xs add-tooltip' data-placement='top' title='Download Invoice' target='_blank'><i class='fa fa-download'></i></a> ";
						}
						
						$nestedData['options'] .= "<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_earning(" . $row->package_payment_id . ")'><i class='fa fa-trash'></i></button>";
				
						$data[] = $nestedData;
						$i = ($dir == 'asc') ? $i + 1 : $i - 1;
					}
				}
				
	
				$json_data = array(
					"draw" => intval($this->input->post('draw')),
					"recordsTotal" => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data" => $data
				);
				
				echo json_encode($json_data);
			}


			elseif ($para1 == "invoice_history") {
    $page_data['title'] = "Admin || " . $this->system_title;
    $page_data['top'] = "earnings/index.php";
    $page_data['folder'] = "earnings";
    $page_data['file'] = "invoice_history.php";
    $page_data['bottom'] = "earnings/index.php";
    $page_data['page_name'] = "earnings";
    
    $this->load->view('back/index', $page_data);
}




elseif ($para1 == "invoice_history_data") {
    // Get year filter
    $year = $this->input->post('year') ?: date('Y');
    
    // Get invoice data for the year
    $this->db->select('pp.*, m.first_name, m.last_name, m.email, p.name as package_name');
    $this->db->from('package_payment pp');
    $this->db->join('member m', 'm.member_id = pp.member_id');
    $this->db->join('plan p', 'p.plan_id = pp.plan_id');
    $this->db->where('pp.invoice_generated', 1);
    if ($year !== 'all') {
        $this->db->where('pp.invoice_year', $year);
    }
    $this->db->order_by('pp.purchase_datetime', 'DESC');
    
    $invoices = $this->db->get()->result();
    
    $data = array();
    $sl = 1;
    foreach ($invoices as $invoice) {
        $nestedData = array();
        $nestedData['#'] = $sl++;
        $nestedData['invoice_number'] = '<strong>' . $invoice->invoice_number . '</strong>';
        $nestedData['member_name'] = $invoice->first_name . ' ' . $invoice->last_name;
        $nestedData['package'] = $invoice->package_name;
        $nestedData['amount'] = currency('', 'def') . number_format($invoice->amount, 2);
        $nestedData['validity'] = date('d M Y', strtotime($invoice->validity_start_date)) . ' - ' . 
                                  date('d M Y', strtotime($invoice->validity_end_date));
        $nestedData['payment_date'] = date('d M Y', $invoice->purchase_datetime);
        $nestedData['year'] = $invoice->invoice_year;
        $nestedData['status'] = '<span class="badge badge-success">Paid</span>';
        $nestedData['action'] = '
            <a href="' . base_url('admin/earnings/download_invoice/' . $invoice->package_payment_id) . '" 
               class="btn btn-primary btn-xs" target="_blank" title="Download Invoice">
                <i class="fa fa-download"></i>
            </a>
            <button class="btn btn-info btn-xs" onclick="viewInvoiceDetail(' . $invoice->package_payment_id . ')" title="View Details">
                <i class="fa fa-eye"></i>
            </button>';
        
        $data[] = $nestedData;
    }
    
    echo json_encode(array('data' => $data));
}
elseif ($para1 == "view_detail") {
    $payment_id = (int) $para2;
    $page = max(1, (int) $this->input->get('page', 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;

    try {
        $payment_details = $this->db->get_where('package_payment', ['package_payment_id' => $payment_id])->row();
        if (!$payment_details) {
            echo json_encode(['status' => 'error', 'message' => 'Payment not found']);
            return;
        }

        $member_details = $this->db->get_where('member', ['member_id' => $payment_details->member_id])->row();
        if (!$member_details) {
            echo json_encode(['status' => 'error', 'message' => 'Member not found']);
            return;
        }

        $plan_details = $this->db->get_where('plan', ['plan_id' => $payment_details->plan_id])->row();
        if (!$plan_details) {
            echo json_encode(['status' => 'error', 'message' => 'Plan not found']);
            return;
        }

        $this->db->select('*');
        $this->db->from('package_payment');
        $this->db->where('member_id', $payment_details->member_id);
        $this->db->where('payment_status', 'paid');
        $this->db->order_by('purchase_datetime', 'DESC');
        $this->db->limit($limit, max(0, $offset));
        $payment_history = $this->db->get()->result();

        $total_history = $this->db->from('package_payment')
                                  ->where('member_id', $payment_details->member_id)
                                  ->where('payment_status', 'paid')
                                  ->count_all_results();

        $data = [
            'payment' => $payment_details,
            'member' => $member_details,
            'plan' => $plan_details,
            'payment_history' => $payment_history,
            'total_history' => $total_history,
            'current_page' => $page,
            'limit' => $limit,
            'payment_id' => $payment_id
        ];

        $view_file = 'back/earnings/payment_detail_modal';
        if (!file_exists(APPPATH . 'views/' . $view_file . '.php')) {
            $view_file = 'back/earnings/custom_payment_method_details';
        }

        // ✅ Don't return here, just load
        $this->load->view($view_file, $data);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }
}


// Also fix load_more_history to prevent negative offsets
elseif ($para1 == "load_more_history") {
    $member_id = (int) $this->input->post('member_id');
    $page = max(1, (int) $this->input->post('page', 1)); // FIXED: Ensure page >=1
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    log_message('debug', 'Load More History | member_id: ' . $member_id . ' | page: ' . $page . ' | offset: ' . $offset);
    
    if ($offset < 0) {
        $offset = 0; // Safety net
    }
    
    $this->db->select('*');
    $this->db->from('package_payment');
    $this->db->where('member_id', $member_id);
    $this->db->where('payment_status', 'paid');
    $this->db->order_by('purchase_datetime', 'DESC');
    $this->db->limit($limit, $offset);
    
    $more_history = $this->db->get()->result();
    
    log_message('debug', 'Load More Success | fetched: ' . count($more_history));
    
    echo json_encode(array(
        'history' => $more_history,
        'has_more' => count($more_history) == $limit // For hiding "Load More" button
    ));
}


// Update cart with selected package
elseif ($para1 == 'bulkpayment' && $para2 == 'update_cart_package') {
    header('Content-Type: application/json');  // ✅ Set JSON header
    
    log_message('debug', '=== UPDATE CART PACKAGE CALLED ===');
    
    $package_id = $this->input->post('package_id');
    $cart_items = $this->session->userdata('cart_items');
    
    log_message('debug', 'Package ID received: ' . $package_id);
    log_message('debug', 'Cart items: ' . print_r($cart_items, true));
    
    // Validate inputs
    if (empty($package_id)) {
        log_message('error', 'Package ID is empty');
        echo json_encode(['status' => 'error', 'message' => 'No package selected']);
        exit;
    }
    
    if (empty($cart_items) || !is_array($cart_items)) {
        log_message('error', 'Cart items empty or invalid');
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }
    
    // Get package details from database
    $package = $this->db->get_where('plan', ['plan_id' => $package_id])->row();
    
    if (!$package) {
        log_message('error', 'Package not found in database: ' . $package_id);
        echo json_encode(['status' => 'error', 'message' => 'Invalid package']);
        exit;
    }
    
    log_message('debug', 'Package found: ' . $package->name);
    
    // ✅ CORRECT GST CALCULATION
    $base_amount = floatval($package->amount);                           // 700
    $gst_percentage = floatval($package->gst);                           // 18.00
    $gst_amount = round(($base_amount * $gst_percentage) / 100, 2);     // 126
    $total_price = $base_amount + $gst_amount;                           // 826
    
    log_message('debug', 'Calculation: Base=' . $base_amount . ', GST%=' . $gst_percentage . ', GST Amount=' . $gst_amount . ', Total=' . $total_price);
    
    // ✅ CRITICAL: Store package ID in session
    $this->session->set_userdata('selected_package_id', $package_id);
    
    $verify_session = $this->session->userdata('selected_package_id');
    log_message('debug', 'Session package_id set to: ' . $verify_session);
    
    // Update all cart items
    foreach ($cart_items as &$item) {
        $item['packageName'] = $package->name;
        $item['packageId'] = $package->plan_id;
        $item['amount'] = $total_price;
        $item['base_amount'] = $base_amount;
        $item['gst_percentage'] = $gst_percentage;
        $item['gst_amount'] = $gst_amount;
    }
    unset($item);
    
    $this->session->set_userdata('cart_items', $cart_items);
    
    log_message('debug', 'Cart items updated successfully');
    
    // ✅ RETURN JSON RESPONSE
    $response = [
        'status' => 'success',
        'message' => 'Package updated successfully',
        'package_name' => $package->name,
        'package_price' => $total_price,
        'base_amount' => $base_amount,
        'gst_percentage' => $gst_percentage,
        'gst_amount' => $gst_amount,
        'total_amount' => $total_price * count($cart_items)
    ];
    
    log_message('debug', 'Sending response: ' . json_encode($response));
    
    echo json_encode($response);
    exit;  // ✅ CRITICAL: Stop execution
}



elseif ($para1 == "bulk_payment_success_page") {
    // ✅ Ensure admin is logged in
    // if ($this->admin_permission() == FALSE) {
    //     redirect(base_url() . 'admin/login', 'refresh');
    //     return;
    // }

    $invoices = $this->session->userdata('bulk_payment_invoices');
    
    if (empty($invoices)) {
        log_message('error', 'No invoice data in session');
        $this->session->set_flashdata('danger_alert', 'No invoice data found');
        redirect(base_url() . 'admin/earnings', 'refresh');
        return;
    }
    
    $page_data['title'] = "Admin || " . $this->system_title;
    $page_data['top'] = "earnings/index.php";
    $page_data['folder'] = "earnings";
    $page_data['file'] = "bulk_payment_success.php";
    $page_data['bottom'] = "earnings/index.php";
    $page_data['page_name'] = "earnings";
    $page_data['invoices'] = $invoices;
    
    // ✅ FIX: Use back/index wrapper, not direct view load
    $this->load->view('back/index', $page_data);
}




  // ============================================================
        // ✅ NEW: View Individual Member Invoice
        // ============================================================
        if ($para1 == "view_invoice" && $para2) {
            $invoice_number = $para2;
            
            $page_data['title'] = 'Admin | ' . $this->system_title;
            $page_data['top'] = "dashboard.php";
            $page_data['folder'] = "earnings";
            $page_data['file'] = "individual_invoice.php";
            $page_data['bottom'] = "dashboard.php";
            $page_data['page_name'] = "earnings";
            
            // Get payment details by invoice number
            $payment = $this->db
                ->select('pp.*, m.*, p.name as plan_name, p.amount as plan_amount, p.gst as plan_gst')
                ->from('package_payment pp')
                ->join('member m', 'm.member_id = pp.member_id', 'left')
                ->join('plan p', 'p.plan_id = pp.plan_id', 'left')
                ->where('pp.invoice_number', $invoice_number)
                ->get()
                ->row();
            
            if (!$payment) {
                $this->session->set_flashdata('danger_alert', 'Invoice not found');
                redirect(base_url('admin/earnings'));
                return;
            }
            
            // Calculate amounts
            $base_amount = floatval($payment->plan_amount);
            $gst_percentage = floatval($payment->plan_gst);
            $gst_amount = round(($base_amount * $gst_percentage) / 100, 2);
            $total_amount = $base_amount + $gst_amount;
            
            $page_data['payment'] = $payment;
            $page_data['base_amount'] = $base_amount;
            $page_data['gst_amount'] = $gst_amount;
            $page_data['gst_percentage'] = $gst_percentage;
            $page_data['total_amount'] = $total_amount;
            
            $this->load->view('back/index', $page_data);
            return;
        }
        
        // ============================================================
        // ✅ NEW: Download Individual Invoice PDF
        // ============================================================
        if ($para1 == "download_invoice" && $para2) {
            $invoice_number = $para2;
            
            // Load PDF helper
            $this->load->helper('pdf');
            
            // Get payment details
            $payment = $this->db
                ->select('pp.*, m.*, p.name as plan_name, p.amount as plan_amount, p.gst as plan_gst')
                ->from('package_payment pp')
                ->join('member m', 'm.member_id = pp.member_id', 'left')
                ->join('plan p', 'p.plan_id = pp.plan_id', 'left')
                ->where('pp.invoice_number', $invoice_number)
                ->get()
                ->row();
            
            if (!$payment) {
                $this->session->set_flashdata('danger_alert', 'Invoice not found');
                redirect(base_url('admin/earnings'));
                return;
            }
            
            // Calculate amounts
            $base_amount = floatval($payment->plan_amount);
            $gst_percentage = floatval($payment->plan_gst);
            $gst_amount = round(($base_amount * $gst_percentage) / 100, 2);
            $total_amount = $base_amount + $gst_amount;
            
            // Get organization details
            $org_name = $this->db->get_where('general_settings', ['type' => 'system_name'])->row();
            $org_email = $this->db->get_where('general_settings', ['type' => 'system_email'])->row();
            $org_phone = $this->db->get_where('general_settings', ['type' => 'contact_phone'])->row();
            
            $data = [
                'payment' => $payment,
                'base_amount' => $base_amount,
                'gst_amount' => $gst_amount,
                'gst_percentage' => $gst_percentage,
                'total_amount' => $total_amount,
                'org_name' => $org_name ? $org_name->description : 'Your Organization',
                'org_email' => $org_email ? $org_email->description : 'info@yourorg.com',
                'org_phone' => $org_phone ? $org_phone->description : '+91 1234567890',
                'generated_date' => date('d M Y, h:i A')
            ];
            
            // Load PDF view
            $html = $this->load->view('back/earnings/individual_invoice_pdf', $data, true);
            
            // Generate and download PDF
            $filename = 'Invoice_' . $invoice_number . '.pdf';
            generate_pdf($html, $filename, 'D');
        }



			// IT is old code for downloading invoice


			// elseif ($para1 == "download_invoice") {
			// 	$payment_id = (int) $para2;
			// 	$this->generate_member_invoice($payment_id);
			// }
			



			// ✅ Process bulk payment (AJAX)
			elseif ($para1 == "process_bulk_payment") {
				$cart_items = $this->session->userdata('cart_items');
				if (empty($cart_items)) {
					echo json_encode(['status' => 'error', 'message' => 'No items in cart']);
					return;
				}
				
				$total_amount = 0;
				$payment_ids = array();
				
				foreach ($cart_items as $item) {
					$total_amount += $item['amount'];
					$payment_ids[] = $item['id'];
				}
				
				$bulk_data = array(
					'payment_ids' => json_encode($payment_ids),
					'total_amount' => $total_amount,
					'payment_status' => 'pending',
					'created_at' => date('Y-m-d H:i:s')
				);
				
				$this->session->set_userdata('bulk_payment_data', $bulk_data);
				
				echo json_encode([
					'status' => 'success',
					'total_amount' => $total_amount,
					'payment_ids' => $payment_ids,
					'redirect_url' => base_url('admin/earnings/phonepe_bulk_payment')
				]);
			}
			// ✅ PhonePe bulk payment page
			elseif ($para1 == "phonepe_bulk_payment") {
				$bulk_payment_data = $this->session->userdata('bulk_payment_data');
				if (empty($bulk_payment_data)) {
					redirect(base_url() . 'admin/earnings', 'refresh');
				}
				
				$page_data['title'] = "Admin || " . $this->system_title;
				$page_data['top'] = "earnings/index.php";
				$page_data['folder'] = "earnings";
				$page_data['file'] = "process_bulk_payment.php";
				$page_data['bottom'] = "earnings/index.php";
				$page_data['page_name'] = "earnings";
				$page_data['bulk_payment_data'] = $bulk_payment_data;
				
				$this->load->view('back/index', $page_data);
			}
			// ✅ Bulk payment success callback
			elseif ($para1 == "bulk_payment_success") {
				$bulk_payment_data = $this->session->userdata('bulk_payment_data');
				if (empty($bulk_payment_data)) {
					$this->session->set_flashdata('danger_alert', 'Payment session expired');
					redirect(base_url() . 'admin/earnings', 'refresh');
					return;
				}
				
				$payment_ids = json_decode($bulk_payment_data['payment_ids'], true);
				
				foreach ($payment_ids as $payment_id) {
					$payment_details = $this->db->get_where('package_payment', array('package_payment_id' => $payment_id))->row();
					
					if (!$payment_details) continue;
					
					$member_details = $this->db->get_where('member', array('member_id' => $payment_details->member_id))->row();
					$plan_details = $this->db->get_where('plan', array('plan_id' => $payment_details->plan_id))->row();
	
					if (!$member_details || !$plan_details) continue;
	
					$data = array();
					$data['membership'] = ($plan_details->plan_id == '1') ? 1 : 2;
					$data['express_interest'] = $member_details->express_interest + $plan_details->express_interest;
					$data['direct_messages'] = $member_details->direct_messages + $plan_details->direct_messages;
					$data['photo_gallery'] = $member_details->photo_gallery + $plan_details->photo_gallery;
	
					$package_info = array(array(
						'current_package' => $plan_details->name,
						'package_price' => $payment_details->amount,
						'payment_type' => 'PhonePe Bulk'
					));
					$data['package_info'] = json_encode($package_info);
	
					$this->db->where('member_id', $payment_details->member_id);
					$this->db->update('member', $data);
	
					$data2 = array(
						'payment_status' => 'paid',
						'payment_type' => 'PhonePe',
						'payment_timestamp' => time(),
						'purchase_datetime' => time()
					);
					
					$this->db->where('package_payment_id', $payment_id);
					$this->db->update('package_payment', $data2);
				}
				
				recache();
				$this->session->unset_userdata('cart_items');
				$this->session->unset_userdata('bulk_payment_data');
				$this->session->set_flashdata('alert', 'bulk_payment_success');
				redirect(base_url() . 'admin/earnings/', 'refresh');
			}

			elseif ($para1 == "download_payment_pdf") {
				$payment_id = (int) $para2;
				$payment = $this->db->get_where('package_payment', ['package_payment_id' => $payment_id])->row();
				if (!$payment) { show_error('Payment not found'); }
	
				$member = $this->db->get_where('member', ['member_id' => $payment->member_id])->row();
				$plan = $this->db->get_where('plan', ['plan_id' => $payment->plan_id])->row();
	
				$logo_path = FCPATH . 'uploads/logo1.jpg';
				$logo_src = file_exists($logo_path)
					? 'data:image/'.pathinfo($logo_path, PATHINFO_EXTENSION).';base64,'.base64_encode(file_get_contents($logo_path))
					: '';
	
				$data = [
					'payment' => $payment,
					'member' => $member,
					'plan' => $plan,
					'logoSrc' => $logo_src,
					'system_title' => $this->system_title ?? 'YOUR COMPANY NAME',
				];
	
				$html = $this->load->view('back/earnings/payment_pdf', $data, true);
				$this->load->library('pdf');
				$this->pdf->loadHtml($html);
				$this->pdf->setPaper('A4', 'portrait');
				$this->pdf->render();
				$this->pdf->stream("payment_receipt_{$payment_id}.pdf", ["Attachment" => 1]);
			}
			elseif ($para1 == "download_cpm_bill_copy") {
				$cpm_bill_copy = $this->db->get_where('package_payment', array('package_payment_id' => $para2))->row()->custom_payment_method_bill_copy;
				$this->load->helper('download');
				$link = 'uploads/custom_payment_method_bill_image/' . $cpm_bill_copy;
				force_download($link, NULL);
			} 
			elseif ($para1 == "accept_payment") {
				$payment_details = $this->db->get_where('package_payment', array('package_payment_id' => $para2))->row();
				$member_details = $this->db->get_where('member', array('member_id' => $payment_details->member_id))->row();
				$plan_details = $this->db->get_where('plan', array('plan_id' => $payment_details->plan_id))->row();
	
				$data = array();
				$data['membership'] = ($plan_details->plan_id == '1') ? 1 : 2;
				$data['express_interest'] = $member_details->express_interest + $plan_details->express_interest;
				$data['direct_messages'] = $member_details->direct_messages + $plan_details->direct_messages;
				$data['photo_gallery'] = $member_details->photo_gallery + $plan_details->photo_gallery;
	
				$package_info = array(array(
					'current_package' => $plan_details->name,
					'package_price' => $payment_details->amount,
					'payment_type' => $payment_details->custom_payment_method_name
				));
				$data['package_info'] = json_encode($package_info);
	
				$this->db->where('member_id', $payment_details->member_id);
				$result = $this->db->update('member', $data);
				recache();
				
				if ($result) {
					$data2['payment_status'] = "paid";
					$this->db->where('package_payment_id', $para2);
					$result1 = $this->db->update('package_payment', $data2);
					
					if ($result1) {
						$this->session->set_flashdata('alert', 'payment_accepted');
						redirect(base_url() . 'admin/earnings/', 'refresh');
					}
				} else {
					$this->session->set_flashdata('alert', 'payment_accepted_error');
					redirect(base_url() . 'admin/earnings/', 'refresh');
				}
			} 
			elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				
				$cpm_bill_copy = $this->db->get_where('package_payment', array('package_payment_id' => $para2))->row()->custom_payment_method_bill_copy;
	
				$this->db->where('package_payment_id', $para2);
				$result = $this->db->delete('package_payment');
				recache();
				
				if ($result) {
					if ($cpm_bill_copy != null && file_exists('uploads/custom_payment_method_bill_image/' . $cpm_bill_copy)) {
						unlink('uploads/custom_payment_method_bill_image/' . $cpm_bill_copy);
					}
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}
	


	function contact_messages($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		}
		 else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "contact_messages/index.php";
				$page_data['folder'] = "contact_messages";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "contact_messages/index.php";
				$page_data['page_name'] = "contact_messages";

				if ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_this_message!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_this_message!");
				} elseif ($this->session->flashdata('alert') == "sent") {
					$page_data['success_alert'] = translate("you_have_successfully_replied_this_message!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
  				

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "list_data") {

				log_message('debug'," the list parta called");
				$columns = array(
					0 => 'contact_message_id',
					1 => 'name',
					2 => 'subject',
				);
				$limit = $this->input->post('length');
				$start = $this->input->post('start');

				if ($this->input->post('order')[0]['column'] == 0) {
					$order = "contact_message_id";
					$dir = "desc";
				} else {
					$order = $columns[$this->input->post('order')[0]['column']];
					$dir = $this->input->post('order')[0]['dir'];
				}

				$table = 'contact_message';

				$totalData = $this->Crud_model->alldata_count($table);

				$totalFiltered = $totalData;

				if (empty($this->input->post('search')['value'])) {
					$rows = $this->Crud_model->allcontact_messages($table, $limit, $start, $order, $dir);
				} else {
					$search = $this->input->post('search')['value'];

					$rows =  $this->Crud_model->contact_message_search($table, $limit, $start, $search, $order, $dir);

					$totalFiltered = $this->Crud_model->contact_message_search_count($table, $search);
				}

				$data = array();
				if (!empty($rows)) {
					if ($dir == 'asc') {
						$i = $start + 1;
					} elseif ($dir == 'desc') {
						$i = $totalFiltered - $start;
					}
					foreach ($rows as $row) {
						if ($row->reply != '') {
							$stat_txt = "<center><span class='badge badge-success'>" . translate('replied') . "</span></center>";
						} else {
							$stat_txt = "<center><span class='badge badge-danger'>" . translate('not_replied') . "</span></center>";
						}

						$nestedData['#'] = $i;
						$nestedData['name'] = $row->name;
						$nestedData['subject'] = $row->subject;
						$nestedData['date'] = date('d/m/Y h:i A', $row->timestamp);
						$nestedData['status'] = $stat_txt;
						$nestedData['options'] = "<a href='" . base_url() . "admin/contact_messages/view_message/" . $row->contact_message_id . "' id='demo-dt-view-btn' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='View Message' ><i class='fa fa-eye'></i></a>
		                	<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_message(" . $row->contact_message_id . ")'><i class='fa fa-trash'></i></button>";

						$data[] = $nestedData;
						if ($dir == 'asc') {
							$i++;
						} elseif ($dir == 'desc') {
							$i--;
						}
					}
				}

				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			} elseif ($para1 == "view_message") {
				$page_data['top'] = "contact_messages/contact_messages.php";
				$page_data['folder'] = "contact_messages";
				$page_data['file'] = "view_message.php";
				$page_data['bottom'] = "contact_messages/contact_messages.php";
				$page_data['get_message'] = $this->db->get_where("contact_message", array("contact_message_id" => $para2))->result();
				$page_data['page_name'] = "contact_messages";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "reply_message") {
				$page_data['top'] = "contact_messages/contact_messages.php";
				$page_data['folder'] = "contact_messages";
				$page_data['file'] = "reply_message.php";
				$page_data['bottom'] = "contact_messages/contact_messages.php";
				$page_data['get_message'] = $this->db->get_where("contact_message", array("contact_message_id" => $para2))->result();
				$page_data['page_name'] = "contact_messages";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == 'update_reply_message') {
				$data['reply'] = $this->input->post('reply_message');
				$this->db->where('contact_message_id', $para2);
				$this->db->update('contact_message', $data);
				$this->db->order_by('contact_message_id', 'desc');
				recache();
				$query = $this->db->get_where('contact_message', array(
					'contact_message_id' => $para2
				))->row();

				$protocol = $this->db->get_where('general_settings', array('type' => 'mail_status'))->row()->value;
				if ($protocol == 'smtp') {
					$from = $this->db->get_where('general_settings', array('type' => 'smtp_user'))->row()->value;
				} else if ($protocol == 'mail') {
					$from = $this->db->get_where('general_settings', array('type' => 'system_email'))->row()->value;
				}

				$system_name = $this->db->get_where('general_settings', array('type' => 'system_name'))->row()->value;

				$this->Email_model->do_email($from, $system_name, $query->email, 'RE: ' . $query->subject, $data['reply']);

				$this->session->set_flashdata('alert', 'sent');
				redirect(base_url() . 'admin/contact_messages', 'refresh');
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}

				$this->db->where('contact_message_id', $para2);
				$result = $this->db->delete('contact_message');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}

	//Newsletter
	function newsletter($para1 = "", $para2 = "")

	{
		//   echo "<pre>";
		//      print_r($_POST);
		//       exit();
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;

			if ($para1 == 'send') {
				$users       = explode(',', $this->input->post('user_email'));
				$to       = $this->input->post('to_email');
				$msg        = $this->input->post('newsletter_desc');
				$subject       = $this->input->post('subject');
				//   $text        = $this->input->post('newsletter_desc');
				//   $title       = $this->input->post('subject');
				$from        = $this->db->get_where('general_settings', array('type' => 'system_email'))->row()->value;


				$this->Email_model->newsletter($subject, $msg, $from, $to, $users);

				//   $this->Email_model->newsletter($title, $text, $users, $from, $to );
				recache();
				$this->session->set_flashdata('mail_send_alert', translate('newsletter_sent_successfully'));

				// foreach ($users as $key => $user)
				// {
				//     if ($user !== '')
				//     {
				//         $this->Email_model->newsletter($title, $text, $user, $from);
				//     }
				// }

			}

			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "newsletter/index.php";
			$page_data['folder'] = "newsletter";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "newsletter/index.php";
			$page_data['page_name'] = "newsletter";

			$this->load->view('back/index', $page_data);
		}
	}


	function knowledge_base($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "knowledge_base/index.php";
			$page_data['folder'] = "knowledge_base";
			$page_data['bottom'] = "knowledge_base/index.php";
			if ($para1 == "documentations") {
				$page_data['page_name'] = "documentations";
				$page_data['file'] = "documentations/index.php";

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "structural_info") {
				$page_data['page_name'] = "structural_info";
				$page_data['file'] = "structural_info/index.php";

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "how_to") {
				$page_data['page_name'] = "how_to";
				$page_data['file'] = "how_to/index.php";

				$this->load->view('back/index', $page_data);
			}
		}
	}

	function religion($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/religion";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				$page_data['all_religions'] = $this->db->get("religion")->result();
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "religion";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_religion") {
				$this->load->view('back/member_configure/religion/add_religion');
			} elseif ($para1 == "edit_religion") {
				$page_data['get_religion'] = $this->db->get_where("religion", array("religion_id" => $para2))->result();
				$this->load->view('back/member_configure/religion/edit_religion', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'Religion Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$result = $this->db->insert('religion', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'Religion Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$religion_id = $this->input->post('religion_id');
					$data['name'] = $this->input->post('name');
					$this->db->where('religion_id', $religion_id);
					$result = $this->db->update('religion', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}

				$this->db->where('religion_id', $para2);
				$result = $this->db->delete('religion');

				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}
	function on_behalf($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/on_behalf";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				$page_data['all_on_behalfs'] = $this->db->get("on_behalf")->result();
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "on_behalf";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_on_behalf") {
				$this->load->view('back/member_configure/on_behalf/add_on_behalf');
			} elseif ($para1 == "edit_on_behalf") {
				$page_data['get_on_behalf'] = $this->db->get_where("on_behalf", array("on_behalf_id" => $para2))->result();
				$this->load->view('back/member_configure/on_behalf/edit_on_behalf', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'on_behalf Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$result = $this->db->insert('on_behalf', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'on_behalf Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$on_behalf_id = $this->input->post('on_behalf_id');
					$data['name'] = $this->input->post('name');
					$this->db->where('on_behalf_id', $on_behalf_id);
					$result = $this->db->update('on_behalf', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('on_behalf_id', $para2);
				$result = $this->db->delete('on_behalf');

				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}
	function family_value($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/family_value";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				$page_data['all_family_values'] = $this->db->get("family_value")->result();
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "family_value";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_family_value") {
				$this->load->view('back/member_configure/family_value/add_family_value');
			} elseif ($para1 == "edit_family_value") {
				$page_data['get_family_value'] = $this->db->get_where("family_value", array("family_value_id" => $para2))->result();
				$this->load->view('back/member_configure/family_value/edit_family_value', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'family_value Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$result = $this->db->insert('family_value', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'family_value Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$family_value_id = $this->input->post('family_value_id');
					$data['name'] = $this->input->post('name');
					$this->db->where('family_value_id', $family_value_id);
					$result = $this->db->update('family_value', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('family_value_id', $para2);
				$result = $this->db->delete('family_value');

				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}
	function family_status($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/family_status";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				$page_data['all_family_statuss'] = $this->db->get("family_status")->result();
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "family_status";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_family_status") {
				$this->load->view('back/member_configure/family_status/add_family_status');
			} elseif ($para1 == "edit_family_status") {
				$page_data['get_family_status'] = $this->db->get_where("family_status", array("family_status_id" => $para2))->result();
				$this->load->view('back/member_configure/family_status/edit_family_status', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'family_status Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$result = $this->db->insert('family_status', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'family_status Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$family_status_id = $this->input->post('family_status_id');
					$data['name'] = $this->input->post('name');
					$this->db->where('family_status_id', $family_status_id);
					$result = $this->db->update('family_status', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('family_status_id', $para2);
				$result = $this->db->delete('family_status');

				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}

	function language($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/language";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				$page_data['all_languages'] = $this->db->get("language")->result();
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "language";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_language") {
				$this->load->view('back/member_configure/language/add_language');
			} elseif ($para1 == "edit_language") {
				$page_data['get_language'] = $this->db->get_where("language", array("language_id" => $para2))->result();
				$this->load->view('back/member_configure/language/edit_language', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'Language Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$result = $this->db->insert('language', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'Language Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$language_id = $this->input->post('language_id');
					$data['name'] = $this->input->post('name');
					$this->db->where('language_id', $language_id);
					$result = $this->db->update('language', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('language_id', $para2);
				$result = $this->db->delete('language');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}


	function country($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/country";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "country";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "list_data") {
				$columns = array(
					0 => 'name',
					1 => 'name',
				);
				$limit = $this->input->post('length');
				$start = $this->input->post('start');
				$order = $columns[$this->input->post('order')[0]['column']];
				$dir = $this->input->post('order')[0]['dir'];
				$table = 'country';

				$totalData = $this->Crud_model->alldata_count($table);

				$totalFiltered = $totalData;

				if (empty($this->input->post('search')['value'])) {
					$rows = $this->Crud_model->alldatas($table, $limit, $start, $order, $dir);
				} else {
					$search = $this->input->post('search')['value'];

					$rows =  $this->Crud_model->data_search($table, $limit, $start, $search, $order, $dir);

					$totalFiltered = $this->Crud_model->data_search_count($table, $search);
				}

				$data = array();
				if (!empty($rows)) {
					if ($dir == 'asc') {
						$i = $start + 1;
					} elseif ($dir == 'desc') {
						$i = $totalFiltered - $start;
					}
					foreach ($rows as $row) {
						$nestedData['#'] = $i;
						$nestedData['name'] = $row->name;
						$nestedData['options'] = "<button data-target='#country_modal' data-toggle='modal' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('edit') . "' onclick='edit_country(" . $row->country_id . ")'><i class='fa fa-edit'></i></button>
		                	<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_country(" . $row->country_id . ")'><i class='fa fa-trash'></i></button>";

						$data[] = $nestedData;
						if ($dir == 'asc') {
							$i++;
						} elseif ($dir == 'desc') {
							$i--;
						}
					}
				}

				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			} elseif ($para1 == "add_country") {
				$this->load->view('back/member_configure/country/add_country');
			} elseif ($para1 == "edit_country") {
				$page_data['get_country'] = $this->db->get_where("country", array("country_id" => $para2))->result();
				$this->load->view('back/member_configure/country/edit_country', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'country Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$result = $this->db->insert('country', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'country Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$country_id = $this->input->post('country_id');
					$data['name'] = $this->input->post('name');
					$this->db->where('country_id', $country_id);
					$result = $this->db->update('country', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('country_id', $para2);
				$result = $this->db->delete('country');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}

	function state($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/state";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "state";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "list_data") {
				$columns = array(
					0 => 'name',
					1 => 'name',
					2 => 'country_name'
				);
				$limit = $this->input->post('length');
				$start = $this->input->post('start');
				$order = $columns[$this->input->post('order')[0]['column']];
				$dir = $this->input->post('order')[0]['dir'];
				$table = 'state';

				$totalData = $this->Crud_model->alldata_count($table);

				$totalFiltered = $totalData;

				if (empty($this->input->post('search')['value'])) {
					$rows = $this->Crud_model->allstates($table, $limit, $start, $order, $dir);
				} else {
					$search = $this->input->post('search')['value'];

					$rows =  $this->Crud_model->state_search($table, $limit, $start, $search, $order, $dir);

					$totalFiltered = $this->Crud_model->state_search_count($table, $search);
				}

				$data = array();
				if (!empty($rows)) {
					if ($dir == 'asc') {
						$i = $start + 1;
					} elseif ($dir == 'desc') {
						$i = $totalFiltered - $start;
					}
					foreach ($rows as $row) {
						$nestedData['#'] = $i;
						$nestedData['name'] = $row->name;
						$nestedData['country_name'] = $row->country_name;
						$nestedData['options'] = "<button data-target='#state_modal' data-toggle='modal' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('edit') . "' onclick='edit_state(" . $row->state_id . ")'><i class='fa fa-edit'></i></button>
		                	<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_state(" . $row->state_id . ")'><i class='fa fa-trash'></i></button>";

						$data[] = $nestedData;
						if ($dir == 'asc') {
							$i++;
						} elseif ($dir == 'desc') {
							$i--;
						}
					}
				}

				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			} elseif ($para1 == "add_state") {
				$this->load->view('back/member_configure/state/add_state');
			} elseif ($para1 == "edit_state") {
				$page_data['get_state'] = $this->db->get_where("state", array("state_id" => $para2))->result();
				$this->load->view('back/member_configure/state/edit_state', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('country_id', 'Country', 'required');
				$this->form_validation->set_rules('name', 'State', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$data['country_id'] = $this->input->post('country_id');
					$result = $this->db->insert('state', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('country_id', 'Country', 'required');
				$this->form_validation->set_rules('name', 'state Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$state_id = $this->input->post('state_id');
					$data['name'] = $this->input->post('name');
					$data['country_id'] = $this->input->post('country_id');
					$this->db->where('state_id', $state_id);
					$result = $this->db->update('state', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('state_id', $para2);
				$result = $this->db->delete('state');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}

	function city($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/city";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_ddd_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "city";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "list_data") {
				$columns = array(
					0 => 'name',
					1 => 'name',
					2 => 'state_name',
					3 => 'country_name',
				);
				$limit = $this->input->post('length');
				$start = $this->input->post('start');
				$order = $columns[$this->input->post('order')[0]['column']];
				$dir = $this->input->post('order')[0]['dir'];
				$table = 'city';

				$totalData = $this->Crud_model->alldata_count($table);

				$totalFiltered = $totalData;

				if (empty($this->input->post('search')['value'])) {
					$rows = $this->Crud_model->allcities($table, $limit, $start, $order, $dir);
				} else {
					$search = $this->input->post('search')['value'];

					$rows =  $this->Crud_model->city_search($table, $limit, $start, $search, $order, $dir);

					$totalFiltered = $this->Crud_model->city_search_count($table, $search);
				}

				$data = array();
				if (!empty($rows)) {
					if ($dir == 'asc') {
						$i = $start + 1;
					} elseif ($dir == 'desc') {
						$i = $totalFiltered - $start;
					}
					foreach ($rows as $row) {
						$nestedData['#'] = $i;
						$nestedData['name'] = $row->name;
						$nestedData['state_name'] = $row->state_name;
						$nestedData['country_name'] = $row->country_name;
						$nestedData['options'] = "<button data-target='#city_modal' data-toggle='modal' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('edit') . "' onclick='edit_city(" . $row->city_id . ")'><i class='fa fa-edit'></i></button>
		                	<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_city(" . $row->city_id . ")'><i class='fa fa-trash'></i></button>";

						$data[] = $nestedData;
						if ($dir == 'asc') {
							$i++;
						} elseif ($dir == 'desc') {
							$i--;
						}
					}
				}

				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			} elseif ($para1 == "add_city") {
				$this->load->view('back/member_configure/city/add_city');
			} elseif ($para1 == "edit_city") {
				$page_data['get_city'] = $this->db->get_where("city", array("city_id" => $para2))->result();
				$page_data['country_id'] = $this->Crud_model->get_type_name_by_id('state', $page_data['get_city'][0]->state_id, 'country_id');
				$this->load->view('back/member_configure/city/edit_city', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('country_id', 'Country', 'required');
				$this->form_validation->set_rules('state_id', 'State', 'required');
				$this->form_validation->set_rules('name', 'City', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$data['state_id'] = $this->input->post('state_id');
					$result = $this->db->insert('city', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('country_id', 'Country', 'required');
				$this->form_validation->set_rules('state_id', 'State', 'required');
				$this->form_validation->set_rules('name', 'City', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$city_id = $this->input->post('city_id');
					$data['name'] = $this->input->post('name');
					$data['state_id'] = $this->input->post('state_id');
					$this->db->where('city_id', $city_id);
					$result = $this->db->update('city', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('city_id', $para2);
				$result = $this->db->delete('city');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}

	function caste($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/caste";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "caste";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "list_data") {
				$columns = array(
					0 => 'caste_name',
					1 => 'caste_name',
					2 => 'religion_name'
				);
				$limit = $this->input->post('length');
				$start = $this->input->post('start');
				$order = $columns[$this->input->post('order')[0]['column']];
				$dir = $this->input->post('order')[0]['dir'];
				$table = 'caste';
				$totalData = $this->Crud_model->alldata_count($table);
				$totalFiltered = $totalData;
				if (empty($this->input->post('search')['value'])) {
					$rows = $this->Crud_model->allcastes($table, $limit, $start, $order, $dir);
				} else {
					$search = $this->input->post('search')['value'];
					$rows =  $this->Crud_model->caste_search($table, $limit, $start, $search, $order, $dir);
					$totalFiltered = $this->Crud_model->caste_search_count($table, $search);
				}

				$data = array();
				if (!empty($rows)) {
					if ($dir == 'asc') {
						$i = $start + 1;
					} elseif ($dir == 'desc') {
						$i = $totalFiltered - $start;
					}
					foreach ($rows as $row) {
						$nestedData['#'] = $i;
						$nestedData['name'] = $row->caste_name;
						$nestedData['religion_name'] = $row->religion_name;
						$nestedData['options'] = "<button data-target='#caste_modal' data-toggle='modal' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('edit') . "' onclick='edit_caste(" . $row->caste_id . ")'><i class='fa fa-edit'></i></button>
		                	<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_caste(" . $row->caste_id . ")'><i class='fa fa-trash'></i></button>";

						$data[] = $nestedData;
						if ($dir == 'asc') {
							$i++;
						} elseif ($dir == 'desc') {
							$i--;
						}
					}
				}
				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			} elseif ($para1 == "add_caste") {
				$this->load->view('back/member_configure/caste/add_caste');
			} elseif ($para1 == "edit_caste") {
				$page_data['get_caste'] = $this->db->get_where("caste", array("caste_id" => $para2))->result();
				$this->load->view('back/member_configure/caste/edit_caste', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('religion_id', 'religion', 'required');
				$this->form_validation->set_rules('caste_name', 'caste name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['caste_name'] = $this->input->post('caste_name');
					$data['religion_id'] = $this->input->post('religion_id');
					$result = $this->db->insert('caste', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('religion_id', 'religion', 'required');
				$this->form_validation->set_rules('name', 'Caste Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$caste_id = $this->input->post('caste_id');
					$data['caste_name'] = $this->input->post('name');
					$data['religion_id'] = $this->input->post('religion_id');
					$this->db->where('caste_id', $caste_id);
					$result = $this->db->update('caste', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('caste_id', $para2);
				$result = $this->db->delete('caste');

				$this->db->where('caste_id', $para2);
				$this->db->delete('sub_caste');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}
	function sub_caste($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/sub_caste";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "sub_caste";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "list_data") {
				$columns = array(
					0 => 'sub_caste_name',
					1 => 'sub_caste_name',
					2 => 'caste_name',
					3 => 'religion_name'
				);
				$limit = $this->input->post('length');
				$start = $this->input->post('start');
				$order = $columns[$this->input->post('order')[0]['column']];
				$dir = $this->input->post('order')[0]['dir'];
				$table = 'sub_caste';

				$totalData = $this->Crud_model->alldata_count($table);

				$totalFiltered = $totalData;

				if (empty($this->input->post('search')['value'])) {
					$rows = $this->Crud_model->allsub_castes($table, $limit, $start, $order, $dir);
				} else {
					$search = $this->input->post('search')['value'];

					$rows =  $this->Crud_model->sub_caste_search($table, $limit, $start, $search, $order, $dir);

					$totalFiltered = $this->Crud_model->sub_caste_search_count($table, $search);
				}

				$data = array();
				if (!empty($rows)) {
					if ($dir == 'asc') {
						$i = $start + 1;
					} elseif ($dir == 'desc') {
						$i = $totalFiltered - $start;
					}
					foreach ($rows as $row) {
						$nestedData['#'] = $i;
						$nestedData['name'] = $row->sub_caste_name;
						$nestedData['caste_name'] = $row->caste_name;
						$nestedData['religion_name'] = $row->religion_name;
						$nestedData['options'] = "<button data-target='#sub_caste_modal' data-toggle='modal' class='btn btn-primary btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('edit') . "' onclick='edit_sub_caste(" . $row->sub_caste_id . ")'><i class='fa fa-edit'></i></button>
		                	<button data-target='#delete_modal' data-toggle='modal' class='btn btn-danger btn-xs add-tooltip' data-toggle='tooltip' data-placement='top' title='" . translate('delete') . "' onclick='delete_sub_caste(" . $row->sub_caste_id . ")'><i class='fa fa-trash'></i></button>";

						$data[] = $nestedData;
						if ($dir == 'asc') {
							$i++;
						} elseif ($dir == 'desc') {
							$i--;
						}
					}
				}

				$json_data = array(
					"draw"            => intval($this->input->post('draw')),
					"recordsTotal"    => intval($totalData),
					"recordsFiltered" => intval($totalFiltered),
					"data"            => $data
				);
				echo json_encode($json_data);
			} elseif ($para1 == "add_sub_caste") {
				$this->load->view('back/member_configure/sub_caste/add_sub_caste');
			} elseif ($para1 == "edit_sub_caste") {
				$page_data['get_sub_caste'] = $this->db->get_where("sub_caste", array("sub_caste_id" => $para2))->result();
				$page_data['religion_id'] = $this->Crud_model->get_type_name_by_id('caste', $page_data['get_sub_caste'][0]->caste_id, 'religion_id');
				$this->load->view('back/member_configure/sub_caste/edit_sub_caste', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('caste_id', 'religion', 'required');
				$this->form_validation->set_rules('sub_caste_name', 'sub_caste name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['sub_caste_name'] = $this->input->post('sub_caste_name');
					$data['caste_id'] = $this->input->post('caste_id');
					$result = $this->db->insert('sub_caste', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('caste_id', 'caste', 'required');
				$this->form_validation->set_rules('sub_caste_name', 'sub_Caste Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$sub_caste_id = $this->input->post('sub_caste_id');
					$data['sub_caste_name'] = $this->input->post('sub_caste_name');
					$data['caste_id'] = $this->input->post('caste_id');
					$this->db->where('sub_caste_id', $sub_caste_id);
					$result = $this->db->update('sub_caste', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('sub_caste_id', $para2);
				$result = $this->db->delete('sub_caste');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}
	function load_caste_with_religion_id($religion_id = "")
	{
		if ($religion_id == "") {
			echo "<select class='form-control chosen' name='caste_id'>
					<option value=''>Choose a First</option>
				</select>";
		} else {
			echo $this->Crud_model->select_html('caste', 'caste_id', 'caste_name', 'add', 'form-control chosen', '', 'religion_id', $religion_id, '');
		}
	}

	function load_state_with_country_id($country_id = "")
	{
		if ($country_id == "") {
			echo "<select class='form-control chosen' name='state_id'>
					<option value=''>Choose a Country First</option>
				</select>";
		} else {
			echo $this->Crud_model->select_html('state', 'state_id', 'name', 'add', 'form-control chosen', '', 'country_id', $country_id, '');
		}
	}

	function occupation($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "member_configure/index.php";
				$page_data['folder'] = "member_configure/occupation";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "member_configure/index.php";
				$page_data['all_occupations'] = $this->db->get("occupation")->result();
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_data!");
				} elseif ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_data!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['success_alert'] = translate("you_have_successfully_deleted_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_add") {
					$page_data['danger_alert'] = translate("failed_to_add_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_edit_the_data!");
				} elseif ($this->session->flashdata('alert') == "failed_delete") {
					$page_data['danger_alert'] = translate("failed_to_delete_the_data!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$page_data['page_name'] = "occupation";
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_occupation") {
				$this->load->view('back/member_configure/occupation/add_occupation');
			} elseif ($para1 == "edit_occupation") {
				$page_data['get_occupation'] = $this->db->get_where("occupation", array("occupation_id" => $para2))->result();
				$this->load->view('back/member_configure/occupation/edit_occupation', $page_data);
			} elseif ($para1 == "do_add") {
				$this->form_validation->set_rules('name', 'Occupation Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$data['name'] = $this->input->post('name');
					$result = $this->db->insert('occupation', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'add');
					} else {
						$this->session->set_flashdata('alert', 'failed_add');
					}
				}
			} elseif ($para1 == "update") {
				$this->form_validation->set_rules('name', 'Occupation Name', 'required');
				if ($this->form_validation->run() == FALSE) {
					$ajax_error[] = array('ajax_error' => validation_errors());
					echo json_encode($ajax_error);
				} else {
					$occupation_id = $this->input->post('occupation_id');
					$data['name'] = $this->input->post('name');
					$this->db->where('occupation_id', $occupation_id);
					$result = $this->db->update('occupation', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_edit');
					}
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('occupation_id', $para2);
				$result = $this->db->delete('occupation');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
				} else {
					$this->session->set_flashdata('alert', 'failed_delete');
				}
			}
		}
	}

	function packages($para1 = "", $para2 = "")
	{
	     //ini_set('display_errors',1);
	    // error_reporting(E_ALL);
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
if ($para1 == "") {
	$page_data['top'] = "packages/index.php";
	$page_data['folder'] = "packages";
	$page_data['file'] = "index.php";
	$page_data['bottom'] = "packages/index.php";
	$plans = $this->db->where('contribution', 0)->get("plan")->result();
	// Calculate total_amount = amount + gst for each plan
	foreach ($plans as $plan) {
		$gst_amount = ($plan->amount * $plan->gst) / 100;
		$plan->total_amount = $plan->amount + $gst_amount;
	}
	$page_data['all_plans'] = $plans;
	//echo '<pre>';print_r($page_data);exit;
	if ($this->session->flashdata('alert') == "edit") {
		$page_data['success_alert'] = translate("you_have_successfully_edited_the_package!");
	} elseif ($this->session->flashdata('alert') == "add") {
		$page_data['success_alert'] = translate("you_have_successfully_added_the_package!");
	} elseif ($this->session->flashdata('alert') == "delete") {
		$page_data['danger_alert'] = translate("you_have_successfully_deleted_the_package!");
	} elseif ($this->session->flashdata('alert') == "failed_image") {
		$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
	} elseif ($this->session->flashdata('alert') == "demo_msg") {
		$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
	}
} elseif ($para1 == "add_package") {
	$page_data['top'] = "packages/packages.php";
	$page_data['folder'] = "packages";
	$page_data['file'] = "add_package.php";
	$page_data['bottom'] = "packages/packages.php";
} elseif ($para1 == "do_add") {
	$data['name'] = $this->input->post('name');
	$data['amount'] = $this->input->post('amount');
	$data['gst'] = $this->input->post('gst');
	$data['start_date'] = $this->input->post('start_date');
	$data['end_date'] = $this->input->post('end_date');
	$data['contribution'] = 0;

	if (!empty($_POST['exp_int_status'])) {
		$data['exp_int_status'] = 1;
	} else {
		$data['exp_int_status'] = 0;
	}

	if (!empty($_POST['dir_msg_status'])) {
		$data['dir_msg_status'] = 1;
	} else {
		$data['dir_msg_status'] = 0;
	}

	$this->db->insert('plan', $data);
	$plan_id = $this->db->insert_id();

	log_message('info', 'Package added: plan_id=' . $plan_id . ', data=' . json_encode($data));

	if (!demo()) {
		if ($_FILES['image']['name'] !== '') {
			$id = $plan_id;
			$path = $_FILES['image']['name'];
			$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
			if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
				$this->Crud_model->file_up("image", "plan", $id, '', '', $ext);
				$images[] = array('image' => 'plan_' . $id . $ext, 'thumb' => 'plan_' . $id . '_thumb' . $ext);
				$data['image'] = json_encode($images);
			} else {
				$this->session->set_flashdata('alert', 'failed_image');
				redirect(base_url() . 'admin/packages', 'refresh');
			}
		}
	}

	$this->db->where('plan_id', $plan_id);
	$result = $this->db->update('plan', $data);
	recache();
	if ($result) {
		$this->session->set_flashdata('alert', 'add');
		redirect(base_url() . 'admin/packages', 'refresh');
	} else {
		echo "Data Failed to Add!";
	}
	exit;
} elseif ($para1 == "edit_package") {
	$page_data['top'] = "packages/packages.php";
	$page_data['folder'] = "packages";
	$page_data['file'] = "edit_package.php";
	$page_data['bottom'] = "packages/packages.php";
	$page_data['get_plan'] = $this->db->get_where("plan", array("plan_id" => $para2))->result();
} elseif ($para1 == "update") {
	/* echo '<pre>';print_r($_POST);
	echo '<br />'; 
	echo '<pre>';print_r($_FILES);exit;*/
	$plan_id = $this->input->post('plan_id');
	$data['name'] = $this->input->post('name');
	$data['amount'] = $this->input->post('amount');
	$data['gst'] = $this->input->post('gst');
	$data['start_date'] = $this->input->post('start_date');
	$data['end_date'] = $this->input->post('end_date');
	//	$data['express_interest'] = (!empty($this->input->post('express_interest'))?$this->input->post('express_interest'):'');
	//	$data['direct_messages'] = $this->input->post('direct_messages');
	//	$data['photo_gallery'] = $this->input->post('photo_gallery');

	if (!empty($_POST['exp_int_status'])) {
		$data['exp_int_status'] = 1;
	} else {
		$data['exp_int_status'] = 0;
	}

	if (!empty($_POST['dir_msg_status'])) {
		$data['dir_msg_status'] = 1;
	} else {
		$data['dir_msg_status'] = 0;
	}

	if (!demo()) {
		if ($_FILES['image']['name'] !== '') {
			$id = $plan_id;
			$path = $_FILES['image']['name'];
			$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
			if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
				$this->Crud_model->file_up("image", "plan", $id, '', '', $ext);
				$images[] = array('image' => 'plan_' . $id . $ext, 'thumb' => 'plan_' . $id . '_thumb' . $ext);
				$data['image'] = json_encode($images);
			} else {
				$this->session->set_flashdata('alert', 'failed_image');
				redirect(base_url() . 'admin/packages', 'refresh');
			}
		}
	}
	$this->db->where('plan_id', $plan_id);
	$result = $this->db->update('plan', $data);

	log_message('info', 'Package updated: plan_id=' . $plan_id . ', data=' . json_encode($data));

	recache();
	if ($result) {
		$this->session->set_flashdata('alert', 'edit');
		redirect(base_url() . 'admin/packages', 'refresh');
	} else {
		echo "Data Failed to Edit!";
	}
	exit;
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$plan_id = $para2;
				$this->db->where('plan_id', $para2);
				$result = $this->db->delete('plan');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
					redirect(base_url() . 'admin/packages', 'refresh');
				} else {
					echo "Data Failed to Delete!";
				}
				exit;
			}
			$page_data['page_name'] = "packages";
			$this->load->view('back/index', $page_data);
		}
	}

	function general_settings($para1 = "")
	{
	   // ini_set('display_errors',1);
	   // error_reporting(E_ALL);
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			if ($para1 == "") {
				$page_data['title'] = "Admin || " . $this->system_title;
				$page_data['top'] = "general_settings/index.php";
				$page_data['folder'] = "general_settings";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "general_settings/index.php";
				$page_data['page_name'] = "general_settings";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_settings!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "update_general_settings") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					redirect(base_url() . 'admin/general_settings', 'refresh');
				}
				$right_option = $this->input->post('right_option');
				if (isset($right_option)) {
					$data8['value'] = 'on';
				} else {
					$data8['value'] = 'off';
				}
				$this->db->where('type', 'right_click_option');
				$this->db->update('general_settings', $data8);

				$data1['value'] = $this->input->post('system_name');
				$this->db->where('type', 'system_name');
				$this->db->update('general_settings', $data1);

				$data2['value'] = $this->input->post('system_email');
				$this->db->where('type', 'system_email');
				$this->db->update('general_settings', $data2);

				$data3['value'] = $this->input->post('system_title');
				$this->db->where('type', 'system_title');
				$this->db->update('general_settings', $data3);

				$data4['value'] = $this->input->post('address');
				$this->db->where('type', 'address');
				$this->db->update('general_settings', $data4);

				$data5['value'] = $this->input->post('cache_time');
				$this->db->where('type', 'cache_time');
				$this->db->update('general_settings', $data5);

				$data6['value'] = $this->input->post('language');
				$this->db->where('type', 'language');
				$this->db->update('general_settings', $data6);

				$data7['value'] = $this->input->post('phone');
				$this->db->where('type', 'phone');
				$this->db->update('general_settings', $data7);

				$data9['value'] = $this->input->post('time_zone');
				$this->db->where('type', 'time_zone');
				$this->db->update('general_settings', $data9);

				$data10['value'] = $this->input->post('member_approval_by_admin');
				$this->db->where('type', 'member_approval_by_admin');
				$this->db->update('general_settings', $data10);

				$data11['value'] = $this->input->post('member_email_verification');
				$this->db->where('type', 'member_email_verification');
				$this->db->update('general_settings', $data11);

				$data12['value'] = $this->input->post('profile_pic_approval');
				$this->db->where('type', 'member_profile_pic_approval_by_admin');
				$this->db->update('general_settings', $data12);

				$data13['value'] = $this->input->post('email_notification_on_express_interest');
				$this->db->where('type', 'email_notification_on_express_interest');
				$this->db->update('general_settings', $data13);

				$data14['value'] = $this->input->post('email_notification_on_sending_message');
				$this->db->where('type', 'email_notification_on_sending_message');
				$this->db->update('general_settings', $data14);

				recache();

				$this->session->set_flashdata('alert', 'edit');

				redirect(base_url() . 'admin/general_settings', 'refresh');
			} elseif ($para1 == "update_smtp") {
				$mail_status = $this->input->post('mail_status');
				if (isset($mail_status)) {
					$data1['value'] = 'smtp';
				} else {
					$data1['value'] = 'mail';
				}
				$this->db->where('type', 'mail_status');
				$this->db->update('general_settings', $data1);

				$data2['value'] = $this->input->post('smtp_host');
				$this->db->where('type', 'smtp_host');
				$this->db->update('general_settings', $data2);

				$data3['value'] = $this->input->post('smtp_port');
				$this->db->where('type', 'smtp_port');
				$this->db->update('general_settings', $data3);

				$data4['value'] = $this->input->post('smtp_user');
				$this->db->where('type', 'smtp_user');
				$this->db->update('general_settings', $data4);

				$data5['value'] = $this->input->post('smtp_pass');
				$this->db->where('type', 'smtp_pass');
				$this->db->update('general_settings', $data5);
				recache();

				$this->session->set_flashdata('alert', 'edit');

				redirect(base_url() . 'admin/general_settings', 'refresh');
			} elseif ($para1 == "update_social_links") {
				$data1['value'] = $this->input->post('facebook');
				$this->db->where('type', 'facebook');
				$this->db->update('social_links', $data1);

				$data2['value'] = $this->input->post('google-plus');
				$this->db->where('type', 'google-plus');
				$this->db->update('social_links', $data2);

				$data3['value'] = $this->input->post('twitter');
				$this->db->where('type', 'twitter');
				$this->db->update('social_links', $data3);

				$data4['value'] = $this->input->post('pinterest');
				$this->db->where('type', 'pinterest');
				$this->db->update('social_links', $data4);

				$data5['value'] = $this->input->post('skype');
				$this->db->where('type', 'skype');
				$this->db->update('social_links', $data5);

				$data5['value'] = $this->input->post('youtube');
				$this->db->where('type', 'youtube');
				$this->db->update('social_links', $data5);
				recache();

				$this->session->set_flashdata('alert', 'edit');

				redirect(base_url() . 'admin/general_settings', 'refresh');
			} elseif ($para1 == "update_terms_and_conditions") {
				$data['value'] = $this->input->post('terms_and_conditions');
				$this->db->where('type', 'terms_conditions');
				$this->db->update('general_settings', $data);
				recache();

				$this->session->set_flashdata('alert', 'edit');

				redirect(base_url() . 'admin/general_settings', 'refresh');
			} elseif ($para1 == "update_privacy_policy") {
				$data['value'] = $this->input->post('privacy_policy');
				$this->db->where('type', 'privacy_policy');
				$this->db->update('general_settings', $data);
				recache();

				$this->session->set_flashdata('alert', 'edit');

				redirect(base_url() . 'admin/general_settings', 'refresh');
			}
		}
	}

	function frontend_appearances($para1 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "pages") {
				$page_data['top'] = "frontend_appearances/index.php";
				$page_data['folder'] = "frontend_appearances/pages";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "frontend_appearances/index.php";
				$page_data['page_name'] = "pages";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_settings!");
				}
				if ($this->session->flashdata('alert') == "failed_image") {
					$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
				}
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "header") {
				$page_data['top'] = "frontend_appearances/index.php";
				$page_data['folder'] = "frontend_appearances/header";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "frontend_appearances/index.php";
				$page_data['page_name'] = "header";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_settings!");
				}
				if ($this->session->flashdata('alert') == "failed_image") {
					$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
				}
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "footer") {
				$page_data['top'] = "frontend_appearances/index.php";
				$page_data['folder'] = "frontend_appearances/footer";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "frontend_appearances/index.php";
				$page_data['page_name'] = "footer";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_settings!");
				}
				if ($this->session->flashdata('alert') == "failed_image") {
					$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
				}
				$this->load->view('back/index', $page_data);
			}
		}
	}

	function save_frontend_settings($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			if ($para1 == "home_slider") {
				if ($this->input->post('slider_status')) {
					$data1['value'] = 'yes';
					$this->db->where('type', 'slider_status');
					$this->db->update('frontend_settings', $data1);
				} else {
					$data1['value'] = 'no';
					$this->db->where('type', 'slider_status');
					$this->db->update('frontend_settings', $data1);
				}

				$data2['value'] = $this->input->post('slider_position');
				$this->db->where('type', 'slider_position');
				$this->db->update('frontend_settings', $data2);

				$data4['value'] = $this->input->post('home_search_style');
				$this->db->where('type', 'home_search_style');
				$this->db->update('frontend_settings', $data4);

				$data5['value'] = $this->input->post('searching_heading');
				$this->db->where('type', 'home_searching_heading');
				$this->db->update('frontend_settings', $data5);

				if (!demo()) {
					$home_slider_image = $this->db->get_where('frontend_settings', array('type' => 'home_slider_image'))->row()->value;
					$img_features = json_decode($home_slider_image, true);
					$last_index = 0;

					$totally_new = array();
					$replaced_new = array();
					$untouched = array();

					foreach ($_FILES['nimg']['name'] as $i => $row) {
						if ($_FILES['nimg']['name'][$i] !== '') {
							$ib = $i + 1;
							$path = $_FILES['nimg']['name'][$i];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
							$img = 'slider_image_' . $ib . '.' . $ext;
							// $img_thumb = 'news_' . $para2 . '_' . $ib . '_thumb.' . $ext;
							$in_db = 'no';
							foreach ($img_features as $roww) {
								if ($roww['index'] == $i) {
									$replaced_new[] = array('index' => $i, 'img' => $img);
									$in_db = 'yes';
								}
							}
							if ($in_db == 'no') {
								$totally_new[] = array('index' => $i, 'img' => $img);
							}
							move_uploaded_file($_FILES['nimg']['tmp_name'][$i], 'uploads/home_page/slider_image/' . $img);
						}
					}

					$touched = $replaced_new + $totally_new;
					foreach ($img_features as $yy) {
						$is_touched = 'no';
						foreach ($touched as $rr) {
							if ($yy['index'] == $rr['index']) {
								$is_touched = 'yes';
							}
						}
						if ($is_touched == 'no') {
							$untouched[] = $yy;
						}
					}
					$new_img_features = array();
					foreach ($replaced_new as $k) {
						$new_img_features[] = $k;
					}
					foreach ($totally_new as $k) {
						$new_img_features[] = $k;
					}
					foreach ($untouched as $k) {
						$new_img_features[] = $k;
					}
					sort_array_of_array($new_img_features, 'index', SORT_ASC); // Sort the data with Index

					$data['value'] = json_encode($new_img_features);
					$this->db->where('type', 'home_slider_image');
					$this->db->update('frontend_settings', $data);
				}
				recache();

				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "home_premium_members") {
				if ($this->input->post('home_members_status')) {
					$data1['value'] = 'yes';
					$this->db->where('type', 'home_members_status');
					$this->db->update('frontend_settings', $data1);
				} else {
					$data1['value'] = 'no';
					$this->db->where('type', 'home_members_status');
					$this->db->update('frontend_settings', $data1);
				}
				$data2['value'] = $this->input->post('max_premium_member_num');

				$this->db->where('type', 'max_premium_member_num');
				$this->db->update('frontend_settings', $data2);
				recache();

				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "home_parallax") {
				$prev_image_info = $this->db->get_where('frontend_settings', array('type' => 'home_parallax_image'))->row()->value;
				if ($prev_image_info != '[]') {
					$prev_image_info = json_decode($prev_image_info, true);
					$prev_image = $prev_image_info[0]['image'];
				}
				if ($this->input->post('home_parallax_status')) {
					$data0['value'] = 'yes';
					$this->db->where('type', 'home_parallax_status');
					$this->db->update('frontend_settings', $data0);
				} else {
					$data0['value'] = 'no';
					$this->db->where('type', 'home_parallax_status');
					$this->db->update('frontend_settings', $data0);
				}
				$data1['value'] = $this->input->post('parallax_text');
				$is_edit = $this->input->post('is_edit');
				if (demo()) {
					$this->db->where('type', 'home_parallax_text');
					$this->db->update('frontend_settings', $data1);
					recache();

					$this->session->set_flashdata('alert', 'edit');
				}
				if (!demo()) {
					if ($is_edit == '0') {
						$this->db->where('type', 'home_parallax_text');
						$this->db->update('frontend_settings', $data1);
						recache();

						$this->session->set_flashdata('alert', 'edit');
					} elseif ($is_edit == '1') {
						if ($_FILES['parallax_image']['name'] !== '') {
							$path = $_FILES['parallax_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "parallax_image_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['parallax_image']['tmp_name'], 'uploads/home_page/parallax_image/' . $img_file_name);
								$home_parallax_image[] = array('image' => $img_file_name);

								$data2['value'] = json_encode($home_parallax_image);

								$this->db->where('type', 'home_parallax_text');
								$this->db->update('frontend_settings', $data1);

								$this->db->where('type', 'home_parallax_image');
								$this->db->update('frontend_settings', $data2);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/home_page/parallax_image/' . $prev_image)) {
									unlink('uploads/home_page/parallax_image/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}
				}

				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "home_happy_stories") {
				if ($this->input->post('home_stories_status')) {
					$data0['value'] = 'yes';
					$this->db->where('type', 'home_stories_status');
					$this->db->update('frontend_settings', $data0);
				} else {
					$data0['value'] = 'no';
					$this->db->where('type', 'home_stories_status');
					$this->db->update('frontend_settings', $data0);
				}

				$data['value'] = $this->input->post('max_story_num');

				$this->db->where('type', 'max_story_num');
				$this->db->update('frontend_settings', $data);
				recache();
				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "home_premium_plans") {
				if ($this->input->post('home_plans_status')) {
					$data0['value'] = 'yes';
					$this->db->where('type', 'home_plans_status');
					$this->db->update('frontend_settings', $data0);
				} else {
					$data0['value'] = 'no';
					$this->db->where('type', 'home_plans_status');
					$this->db->update('frontend_settings', $data0);
				}

				if (!demo()) {
					$prev_image_info = $this->db->get_where('frontend_settings', array('type' => 'home_premium_plans_image'))->row()->value;
					if ($prev_image_info != '[]') {
						$prev_image_info = json_decode($prev_image_info, true);
						$prev_image = $prev_image_info[0]['image'];
					}
					$is_edit = $this->input->post('is_edit');
					if ($is_edit == '0') {
						$this->session->set_flashdata('alert', 'edit');
					} elseif ($is_edit == '1') {
						if ($_FILES['premium_plans_image']['name'] !== '') {
							$path = $_FILES['premium_plans_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "premium_plans_image_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['premium_plans_image']['tmp_name'], 'uploads/home_page/premium_plans_image/' . $img_file_name);
								$home_premium_plans_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($home_premium_plans_image);

								$this->db->where('type', 'home_premium_plans_image');
								$this->db->update('frontend_settings', $data);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/home_page/premium_plans_image/' . $prev_image)) {
									unlink('uploads/home_page/premium_plans_image/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}
				}

				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "home_contact_info") {
				if ($this->input->post('home_contact_status')) {
					$data0['value'] = 'yes';
					$this->db->where('type', 'home_contact_status');
					$this->db->update('frontend_settings', $data0);
				} else {
					$data0['value'] = 'no';
					$this->db->where('type', 'home_contact_status');
					$this->db->update('frontend_settings', $data0);
				}

				$data['value'] = $this->input->post('contact_info_text');

				$this->db->where('type', 'home_contact_info_text');
				$this->db->update('frontend_settings', $data);
				recache();

				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "premium_plans") {
				if (!demo()) {
					$prev_image_info = $this->db->get_where('frontend_settings', array('type' => 'premium_plans_image'))->row()->value;
					if ($prev_image_info != '[]') {
						$prev_image_info = json_decode($prev_image_info, true);
						$prev_image = $prev_image_info[0]['image'];
					}
					$is_edit = $this->input->post('is_edit');
					if ($is_edit == '0') {
						if ($_FILES['premium_plans_image']['name'] !== '') {
							$path = $_FILES['premium_plans_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "premium_plans_image_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['premium_plans_image']['tmp_name'], 'uploads/premium_plans_image/' . $img_file_name);
								$premium_plans_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($premium_plans_image);

								$this->db->where('type', 'premium_plans_image');
								$this->db->update('frontend_settings', $data);
								recache();

								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
						$this->session->set_flashdata('alert', 'edit');
					} elseif ($is_edit == '1') {
						if ($_FILES['premium_plans_image']['name'] !== '') {
							$path = $_FILES['premium_plans_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "premium_plans_image_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['premium_plans_image']['tmp_name'], 'uploads/premium_plans_image/' . $img_file_name);
								$premium_plans_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($premium_plans_image);

								$this->db->where('type', 'premium_plans_image');
								$this->db->update('frontend_settings', $data);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/premium_plans_image/' . $prev_image)) {
									unlink('uploads/premium_plans_image/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}
				}

				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "happy_stories") {
				$data['value'] = $this->input->post('happy_stories_text');

				$this->db->where('type', 'happy_stories_text');
				$this->db->update('frontend_settings', $data);
				recache();

				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "contact_us") {
				$data['value'] = $this->input->post('contact_us_text');

				$this->db->where('type', 'contact_us_text');
				$this->db->update('frontend_settings', $data);
				recache();

				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "listing_page") {

				$data1['value'] = $this->input->post('advance_search_position');
				$this->db->where('type', 'advance_search_position');
				$this->db->update('frontend_settings', $data1);

				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "log_in") {
				if (!demo()) {
					$prev_image_info = $this->db->get_where('frontend_settings', array('type' => 'login_image'))->row()->value;
					if ($prev_image_info != '[]') {
						$prev_image_info = json_decode($prev_image_info, true);
						$prev_image = $prev_image_info[0]['image'];
					}
					$is_edit = $this->input->post('is_edit');
					if ($is_edit == '0') {
						if ($_FILES['login_image']['name'] !== '') {
							$path = $_FILES['login_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "login_image_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['login_image']['tmp_name'], 'uploads/login_image/' . $img_file_name);
								$login_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($login_image);

								$this->db->where('type', 'login_image');
								$this->db->update('frontend_settings', $data);
								recache();

								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
						$this->session->set_flashdata('alert', 'edit');
					} elseif ($is_edit == '1') {
						if ($_FILES['login_image']['name'] !== '') {
							$path = $_FILES['login_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "login_image_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['login_image']['tmp_name'], 'uploads/login_image/' . $img_file_name);
								$login_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($login_image);

								$this->db->where('type', 'login_image');
								$this->db->update('frontend_settings', $data);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/login_image/' . $prev_image)) {
									unlink('uploads/login_image/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}
				}
				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "registration") {
				if (!demo()) {
					$prev_image_info = $this->db->get_where('frontend_settings', array('type' => 'registration_image'))->row()->value;
					if ($prev_image_info != '[]') {
						$prev_image_info = json_decode($prev_image_info, true);
						$prev_image = $prev_image_info[0]['image'];
					}
					$is_edit = $this->input->post('is_edit');
					if ($is_edit == '0') {
						if ($_FILES['registration_image']['name'] !== '') {
							$path = $_FILES['registration_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "registration_image_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['registration_image']['tmp_name'], 'uploads/registration_image/' . $img_file_name);
								$registration_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($registration_image);

								$this->db->where('type', 'registration_image');
								$this->db->update('frontend_settings', $data);
								recache();

								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
						$this->session->set_flashdata('alert', 'edit');
					} elseif ($is_edit == '1') {
						if ($_FILES['registration_image']['name'] !== '') {
							$path = $_FILES['registration_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "registration_image_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['registration_image']['tmp_name'], 'uploads/registration_image/' . $img_file_name);
								$registration_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($registration_image);

								$this->db->where('type', 'registration_image');
								$this->db->update('frontend_settings', $data);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/registration_image/' . $prev_image)) {
									unlink('uploads/registration_image/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}
				}

				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "set_email_verification_message") {

				$data['value'] = $this->input->post('email_verification_message');
				$this->db->where('type', 'email_verification_message');
				$this->db->update('frontend_settings', $data);
				recache();

				$this->session->set_flashdata('alert', 'edit');

				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "update_header") {
				$prev_image_info = $this->db->get_where('frontend_settings', array('type' => 'header_logo'))->row()->value;
				if ($prev_image_info != '[]') {
					$prev_image_info = json_decode($prev_image_info, true);
					$prev_image = $prev_image_info[0]['image'];
				}
				$is_edit = $this->input->post('is_edit');
				if ($is_edit == '0') {
					if (!demo()) {
						if ($_FILES['header_logo']['name'] !== '') {
							$path = $_FILES['header_logo']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "header_logo_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['header_logo']['tmp_name'], 'uploads/header_logo/' . $img_file_name);
								$header_logo[] = array('image' => $img_file_name);

								$data['value'] = json_encode($header_logo);

								$this->db->where('type', 'header_logo');
								$this->db->update('frontend_settings', $data);
								recache();

								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						} elseif ($_FILES['favicon']['name'] !== '') {
							$path = $_FILES['favicon']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "favicon_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['favicon']['tmp_name'], 'uploads/favicon/' . $img_file_name);
								$favicon[] = array('image' => $img_file_name);

								$data['value'] = json_encode($favicon);

								$this->db->where('type', 'favicon');
								$this->db->update('frontend_settings', $data);
								recache();

								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}

					if ($this->input->post('sticky_header')) {
						$data1['value'] = 'yes';
						$this->db->where('type', 'sticky_header');
						$this->db->update('frontend_settings', $data1);
					} else {
						$data1['value'] = 'no';
						$this->db->where('type', 'sticky_header');
						$this->db->update('frontend_settings', $data1);
					}
					$this->session->set_flashdata('alert', 'edit');
				} elseif ($is_edit == '1') {
					if (!demo()) {
						if ($_FILES['header_logo']['name'] !== '') {
							$path = $_FILES['header_logo']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "header_logo_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['header_logo']['tmp_name'], 'uploads/header_logo/' . $img_file_name);
								$header_logo[] = array('image' => $img_file_name);

								$data['value'] = json_encode($header_logo);

								$this->db->where('type', 'header_logo');
								$this->db->update('frontend_settings', $data);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/header_logo/' . $prev_image)) {
									unlink('uploads/header_logo/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						} elseif ($_FILES['favicon']['name'] !== '') {
							$path = $_FILES['favicon']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "favicon_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['favicon']['tmp_name'], 'uploads/favicon/' . $img_file_name);
								$favicon[] = array('image' => $img_file_name);

								$data['value'] = json_encode($favicon);

								$this->db->where('type', 'favicon');
								$this->db->update('frontend_settings', $data);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/favicon/' . $prev_image)) {
									unlink('uploads/favicon/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}
				}
				redirect(base_url() . 'admin/frontend_appearances/header', 'refresh');
			} elseif ($para1 == "update_footer") {
				$data1['value'] = $this->input->post('footer_logo_position');
				$data2['value'] = $this->input->post('footer_text');
				$data3['value'] = $this->input->post('guruji_quotes');

				$this->db->where('type', 'footer_logo_position');
				$this->db->update('frontend_settings', $data1);

				$this->db->where('type', 'footer_text');
				$this->db->update('frontend_settings', $data2);

				$this->db->where('type', 'guruji_quotes');
				$this->db->update('frontend_settings', $data3);
				recache();

				if (!demo()) {
					$prev_image_info = $this->db->get_where('frontend_settings', array('type' => 'footer_logo'))->row()->value;
					if ($prev_image_info != '[]') {
						$prev_image_info = json_decode($prev_image_info, true);
						$prev_image = $prev_image_info[0]['image'];
					}
					$is_edit = $this->input->post('is_edit');
					if ($is_edit == '0') {
						if ($_FILES['footer_logo']['name'] !== '') {
							$path = $_FILES['footer_logo']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "footer_logo_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['footer_logo']['tmp_name'], 'uploads/footer_logo/' . $img_file_name);
								$footer_logo[] = array('image' => $img_file_name);

								$data['value'] = json_encode($footer_logo);

								$this->db->where('type', 'footer_logo');
								$this->db->update('frontend_settings', $data);
								recache();

								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
						$this->session->set_flashdata('alert', 'edit');
					} elseif ($is_edit == '1') {
						if ($_FILES['footer_logo']['name'] !== '') {
							$path = $_FILES['footer_logo']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "footer_logo_" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['footer_logo']['tmp_name'], 'uploads/footer_logo/' . $img_file_name);
								$footer_logo[] = array('image' => $img_file_name);

								$data['value'] = json_encode($footer_logo);

								$this->db->where('type', 'footer_logo');
								$this->db->update('frontend_settings', $data);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/footer_logo/' . $prev_image)) {
									unlink('uploads/footer_logo/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}
				}

				redirect(base_url() . 'admin/frontend_appearances/footer', 'refresh');
			} elseif ($para1 == "registration_msg") {

				$data['value'] = $this->input->post('registration_message');
				$this->db->where('type', 'registration_message');
				$this->db->update('frontend_settings', $data);
				recache();

				$this->session->set_flashdata('alert', 'edit');

				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			} elseif ($para1 == "registration_message") {
				if (!demo()) {
					$prev_image_info = $this->db->get_where('frontend_settings', array('type' => 'registration_message_image'))->row()->value;
					if ($prev_image_info != '[]') {
						$prev_image_info = json_decode($prev_image_info, true);
						$prev_image = $prev_image_info[0]['image'];
					}
					$is_edit = $this->input->post('is_edit');
					if ($is_edit == '0') {
						if ($_FILES['registration_message_image']['name'] !== '') {
							$path = $_FILES['registration_message_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "registration_message_image" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['registration_message_image']['tmp_name'], 'uploads/registration_message_image/' . $img_file_name);
								$registration_message_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($registration_message_image);

								$this->db->where('type', 'registration_message_image');
								$this->db->update('frontend_settings', $data);
								recache();

								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
						$this->session->set_flashdata('alert', 'edit');
					} elseif ($is_edit == '1') {
						if ($_FILES['registration_message_image']['name'] !== '') {
							$path = $_FILES['registration_message_image']['name'];
							$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
							$img_file_name = "registration_message_image" . time() . $ext;
							if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
								move_uploaded_file($_FILES['registration_message_image']['tmp_name'], 'uploads/registration_message_image/' . $img_file_name);
								$registration_message_image[] = array('image' => $img_file_name);

								$data['value'] = json_encode($registration_message_image);

								$this->db->where('type', 'registration_message_image');
								$this->db->update('frontend_settings', $data);
								recache();

								if ($prev_image_info != '[]' && file_exists('uploads/registration_message_image/' . $prev_image)) {
									unlink('uploads/registration_message_image/' . $prev_image);
								}
								$this->session->set_flashdata('alert', 'edit');
							} else {
								$this->session->set_flashdata('alert', 'failed_image');
							}
						}
					}
				}

				redirect(base_url() . 'admin/frontend_appearances/pages', 'refresh');
			}
		}
	}

	function currency_settings($para1 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "currency_configure") {
				$page_data['top'] = "currency_settings/index.php";
				$page_data['folder'] = "currency_settings/currency_configure";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "currency_settings/index.php";
				$page_data['page_name'] = "currency_configure";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_settings!");
				}
				$this->load->view('back/index', $page_data);
			}
			if ($para1 == "currency_set") {
				$page_data['top'] = "currency_settings/index.php";
				$page_data['folder'] = "currency_settings/currency_set";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "currency_settings/index.php";
				$page_data['page_name'] = "currency_set";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_settings!");
				}
				$this->load->view('back/index', $page_data);
			}
		}
	}

	function update_currency_settings($para1 = "")
	{
		if ($para1 == "home_currency") {
			$home_currency = $this->input->post('home_def_currency');

			$data['value'] = $home_currency;

			$this->db->where('type', 'home_def_currency');
			$result = $this->db->update('business_settings', $data);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/currency_settings/currency_configure', 'refresh');
		} elseif ($para1 == "system_currency") {
			$system_currency = $this->input->post('system_def_currency');

			$data['value'] = $system_currency;

			$this->db->where('type', 'currency');
			$result = $this->db->update('business_settings', $data);

			$this->db->where('currency_settings_id', $system_currency);
			$this->db->update('currency_settings', array(
				'exchange_rate_def' => '1'
			));
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/currency_settings/currency_configure', 'refresh');
		} elseif ($para1 == "currency_format") {
			$this->db->where('type', 'currency_format');
			$this->db->update('business_settings', array(
				'value' => $this->input->post('currency_format')
			));

			$this->db->where('type', 'symbol_format');
			$this->db->update('business_settings', array(
				'value' => $this->input->post('symbol_format')
			));

			$this->db->where('type', 'no_of_decimals');
			$result = $this->db->update('business_settings', array(
				'value' => $this->input->post('no_of_decimals')
			));
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/currency_settings/currency_configure', 'refresh');
		}
	}

	function set_currency_rate($para1 = "")
	{
		if ($this->input->post('exchange')) {
			$data['exchange_rate']    		= $this->input->post('exchange');
		}
		if ($this->input->post('exchange_def')) {
			$data['exchange_rate_def']    	= $this->input->post('exchange_def');
		}
		if ($this->input->post('name')) {
			$data['name']    	= $this->input->post('name');
		}
		if ($this->input->post('symbol')) {
			$data['symbol']    	= $this->input->post('symbol');
		}
		$cur_stats = $this->input->post('cur_stats');
		if (isset($cur_stats)) {
			$data['status'] = "ok";
		} else {
			$data['status'] = "no";
		}
		$this->db->where('currency_settings_id', $para1);
		$this->db->update('currency_settings', $data);
		recache();
	}

	function sms_settings($para1 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "twilio") {
				$page_data['top'] = "sms_settings/index.php";
				$page_data['folder'] = "sms_settings/twilio";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "sms_settings/index.php";
				$page_data['page_name'] = "twilio";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_settings!");
				}
				$this->load->view('back/index', $page_data);
			}
			if ($para1 == "msg91") {
				$page_data['top'] = "sms_settings/index.php";
				$page_data['folder'] = "sms_settings/msg91";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "sms_settings/index.php";
				$page_data['page_name'] = "msg91";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_settings!");
				}
				$this->load->view('back/index', $page_data);
			}
		}
	}

	function update_sms_settings($para1 = "")
	{
		if ($para1 == "twilio") {
			$twilio_activation = $this->input->post('twilio_activation');
			if (isset($twilio_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('twilio_account_sid');
			$data3['value'] = $this->input->post('twilio_auth_token');
			$data4['value'] = $this->input->post('twilio_sender_phone_number');

			$this->db->where('type', 'twilio_status');
			$result = $this->db->update('third_party_settings', $data1);

			$this->db->where('type', 'twilio_account_sid');
			$result = $this->db->update('third_party_settings', $data2);

			$this->db->where('type', 'twilio_auth_token');
			$result = $this->db->update('third_party_settings', $data3);

			$this->db->where('type', 'twilio_sender_phone_number');
			$result = $this->db->update('third_party_settings', $data4);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/sms_settings/twilio', 'refresh');
		}

		if ($para1 == "msg91") {
			$msg91_activation = $this->input->post('msg91_activation');
			if (isset($msg91_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('authentication_key');
			$data3['value'] = $this->input->post('sender_id');
			$data6['value'] = $this->input->post('type');

			$data4['value'] = $this->input->post('country_code');
			$data5['value'] = $this->input->post('route');


			$this->db->where('type', 'msg91_status');
			$result = $this->db->update('third_party_settings', $data1);

			$this->db->where('type', 'msg91_authentication_key');
			$result = $this->db->update('third_party_settings', $data2);

			$this->db->where('type', 'msg91_sender_id');
			$result = $this->db->update('third_party_settings', $data3);

			$this->db->where('type', 'msg91_country_code');
			$result = $this->db->update('third_party_settings', $data4);

			$this->db->where('type', 'msg91_route');
			$result = $this->db->update('third_party_settings', $data5);
			$this->db->where('type', 'msg91_type');
			$result = $this->db->update('third_party_settings', $data6);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/sms_settings/msg91', 'refresh');
		}
	}

	function theme_color_settings($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			if ($para1 == "") {
				$page_data['title'] = "Admin || " . $this->system_title;
				$page_data['top'] = "theme_color_settings/index.php";
				$page_data['folder'] = "theme_color_settings";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "theme_color_settings/index.php";
				$page_data['page_name'] = "theme_color_settings";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_updated_your_profile!");
				} elseif ($this->session->flashdata('alert') == "failed_edit") {
					$page_data['danger_alert'] = translate("failed_to_updated_your_profile!");
				}

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "update") {
				$data['value'] = $this->input->post('theme_color');
				$this->db->where('type', 'theme_color');
				$result = $this->db->update('frontend_settings', $data);
				recache();
			}
		}
	}

	function payments()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "payments/index.php";
			$page_data['folder'] = "payments";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "payments/index.php";
			$page_data['page_name'] = "payments";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_your_payments_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_your_payments_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_image") {
				$page_data['danger_alert'] = translate("image_upload_failed!_please_make_sure_your_image_format_is_JPG,_JPEG_or_PNG.");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_payments($para1 = "")
	{
		if ($para1 == "update_paypal") {

			$paypal_activation = $this->input->post('paypal_activation');
			if (isset($paypal_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('email');
			$data3['value'] = $this->input->post('paypal_account_type');

			$this->db->where('type', 'paypal_set');
			$result = $this->db->update('business_settings', $data1);

			$this->db->where('type', 'paypal_email');
			$result = $this->db->update('business_settings', $data2);

			$this->db->where('type', 'paypal_account_type');
			$result = $this->db->update('business_settings', $data3);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
		} elseif ($para1 == "update_stripe") {

			$stripe_activation = $this->input->post('stripe_activation');
			if (isset($stripe_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('secret_key');
			$data3['value'] = $this->input->post('publishable_key');

			$this->db->where('type', 'stripe_set');
			$result = $this->db->update('business_settings', $data1);

			$this->db->where('type', 'stripe_secret_key');
			$result = $this->db->update('business_settings', $data2);

			$this->db->where('type', 'stripe_publishable_key');
			$result = $this->db->update('business_settings', $data3);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
		} elseif ($para1 == "update_pum") {

			$pum_activation = $this->input->post('pum_activation');
			if (isset($pum_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data21['value'] = $this->input->post('pum_merchant_key');
			$data22['value'] = $this->input->post('pum_merchant_salt');
			$data3['value'] = $this->input->post('pum_account_type');

			$this->db->where('type', 'pum_set');
			$result = $this->db->update('business_settings', $data1);

			$this->db->where('type', 'pum_merchant_key');
			$result = $this->db->update('business_settings', $data21);

			$this->db->where('type', 'pum_merchant_salt');
			$result = $this->db->update('business_settings', $data22);

			$this->db->where('type', 'pum_account_type');
			$result = $this->db->update('business_settings', $data3);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
		} elseif ($para1 == 'update_instamojo') {
			$instamojo_activation = $this->input->post('instamojo_activation');
			if (isset($instamojo_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('instamojo_api_key');
			$data3['value'] = $this->input->post('instamojo_auth_token');
			$data4['value'] = $this->input->post('instamojo_account_type');

			$this->db->where('type', 'instamojo_set');
			$result = $this->db->update('business_settings', $data1);

			$this->db->where('type', 'instamojo_api_key');
			$result = $this->db->update('business_settings', $data2);

			$this->db->where('type', 'instamojo_auth_token');
			$result = $this->db->update('business_settings', $data3);

			$this->db->where('type', 'instamojo_account_type');
			$result = $this->db->update('business_settings', $data4);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
		} elseif ($para1 == 'update_custom_payment_1') {
			$cp_method_1_set = $this->input->post('custom_payment_method_1_set');
			if (isset($cp_method_1_set)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('custom_payment_method_1_name');
			$data3['value'] = $this->input->post('custom_payment_method_1_number');
			$data4['value'] = $this->input->post('custom_payment_method_1_instruction');

			$this->db->where('type', 'custom_payment_method_1_set');
			$result = $this->db->update('business_settings', $data1);

			$this->db->where('type', 'custom_payment_method_1_name');
			$result = $this->db->update('business_settings', $data2);

			$this->db->where('type', 'custom_payment_method_1_number');
			$result = $this->db->update('business_settings', $data3);

			$this->db->where('type', 'custom_payment_method_1_instruction');
			$result = $this->db->update('business_settings', $data4);

			if (!demo()) {
			}

			if (!demo() && $_FILES['cp_image1']['name'] !== '' && $_FILES['cp_image1']['error'] == 0) {
				$path = $_FILES['cp_image1']['name'];
				$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
				$img_file_name = 'cp_method_1_image.jpg';
				if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
					move_uploaded_file($_FILES['cp_image1']['tmp_name'], 'uploads/custom_payment_methods_image/' . $img_file_name);

					$this->session->set_flashdata('alert', 'edit');
				} else {
					$this->session->set_flashdata('alert', 'failed_image');
				}
			}
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
		} elseif ($para1 == 'update_custom_payment_2') {
			$cp_method_2_set = $this->input->post('custom_payment_method_2_set');
			if (isset($cp_method_2_set)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('custom_payment_method_2_name');
			$data3['value'] = $this->input->post('custom_payment_method_2_number');
			$data4['value'] = $this->input->post('custom_payment_method_2_instruction');

			$this->db->where('type', 'custom_payment_method_2_set');
			$result = $this->db->update('business_settings', $data1);

			$this->db->where('type', 'custom_payment_method_2_name');
			$result = $this->db->update('business_settings', $data2);

			$this->db->where('type', 'custom_payment_method_2_number');
			$result = $this->db->update('business_settings', $data3);

			$this->db->where('type', 'custom_payment_method_2_instruction');
			$result = $this->db->update('business_settings', $data4);

			if (!demo()) {
			}

			if (!demo() && $_FILES['cp_image2']['name'] !== '' && $_FILES['cp_image2']['error'] == 0) {
				$path = $_FILES['cp_image2']['name'];
				$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
				$img_file_name = 'cp_method_2_image.jpg';
				if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
					move_uploaded_file($_FILES['cp_image2']['tmp_name'], 'uploads/custom_payment_methods_image/' . $img_file_name);

					$this->session->set_flashdata('alert', 'edit');
				} else {
					$this->session->set_flashdata('alert', 'failed_image');
				}
			}
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
		} elseif ($para1 == 'update_custom_payment_3') {
			$cp_method_3_set = $this->input->post('custom_payment_method_3_set');
			if (isset($cp_method_3_set)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('custom_payment_method_3_name');
			$data3['value'] = $this->input->post('custom_payment_method_3_number');
			$data4['value'] = $this->input->post('custom_payment_method_3_instruction');

			$this->db->where('type', 'custom_payment_method_3_set');
			$result = $this->db->update('business_settings', $data1);

			$this->db->where('type', 'custom_payment_method_3_name');
			$result = $this->db->update('business_settings', $data2);

			$this->db->where('type', 'custom_payment_method_3_number');
			$result = $this->db->update('business_settings', $data3);

			$this->db->where('type', 'custom_payment_method_3_instruction');
			$result = $this->db->update('business_settings', $data4);

			if (!demo()) {
			}

			if (!demo() && $_FILES['cp_image3']['name'] !== '' && $_FILES['cp_image3']['error'] == 0) {
				$path = $_FILES['cp_image3']['name'];
				$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
				$img_file_name = 'cp_method_3_image.jpg';
				if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
					move_uploaded_file($_FILES['cp_image3']['tmp_name'], 'uploads/custom_payment_methods_image/' . $img_file_name);

					$this->session->set_flashdata('alert', 'edit');
				} else {
					$this->session->set_flashdata('alert', 'failed_image');
				}
			}
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
		} elseif ($para1 == 'update_custom_payment_4') {
			$cp_method_4_set = $this->input->post('custom_payment_method_4_set');
			if (isset($cp_method_4_set)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('custom_payment_method_4_name');
			$data3['value'] = $this->input->post('custom_payment_method_4_number');
			$data4['value'] = $this->input->post('custom_payment_method_4_instruction');

			$this->db->where('type', 'custom_payment_method_4_set');
			$result = $this->db->update('business_settings', $data1);

			$this->db->where('type', 'custom_payment_method_4_name');
			$result = $this->db->update('business_settings', $data2);

			$this->db->where('type', 'custom_payment_method_4_number');
			$result = $this->db->update('business_settings', $data3);

			$this->db->where('type', 'custom_payment_method_4_instruction');
			$result = $this->db->update('business_settings', $data4);

			if (!demo()) {
			}

			if (!demo() && $_FILES['cp_image4']['name'] !== '' && $_FILES['cp_image4']['error'] == 0) {
				$path = $_FILES['cp_image4']['name'];
				$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
				$img_file_name = 'cp_method_4_image.jpg';
				if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
					move_uploaded_file($_FILES['cp_image4']['tmp_name'], 'uploads/custom_payment_methods_image/' . $img_file_name);

					$this->session->set_flashdata('alert', 'edit');
				} else {
					$this->session->set_flashdata('alert', 'failed_image');
				}
			}
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
		}

		redirect(base_url() . 'admin/payments', 'refresh');
	}

	function faq($para1 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			if ($para1 == "") {
				$page_data['title'] = "Admin || " . $this->system_title;
				$page_data['top'] = "faq/index.php";
				$page_data['folder'] = "faq";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "faq/index.php";
				$page_data['page_name'] = "faq";
				if ($this->session->flashdata('alert') == "update") {
					$page_data['success_alert'] = translate("you_have_successfully_updated_the_FAQ!");
				} elseif ($this->session->flashdata('alert') == "failed_update") {
					$page_data['danger_alert'] = translate("failed_to_update_the_FAQ!");
				}
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "update") {
				$faqs = array();
				$f_q = $this->input->post('question');
				$f_a = $this->input->post('answer');
				foreach ($f_q as $i => $r) {
					$faqs[] = array(
						'question' => $f_q[$i],
						'answer' => $f_a[$i]
					);
				}
				$this->db->where('type', "faqs");
				$result = $this->db->update('general_settings', array('value' => json_encode($faqs)));
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'update');
				} else {
					$this->session->set_flashdata('alert', 'failed_update');
				}
				redirect(base_url() . 'admin/faq', 'refresh');
			}
		}
	}

	function delete_slider($image_name)
	{
		if (!demo()) {
			$new_img_features = array();
			$old_img_features = json_decode($this->db->get_where('frontend_settings', array('type' => 'home_slider_image'))->row()->value, true);
			foreach ($old_img_features as $row2) {
				if ($row2['img'] == $image_name) {
					if (file_exists('uploads/home_page/slider_image/' . $row2['img'])) {
						unlink('uploads/home_page/slider_image/' . $row2['img']);
					}
				} else {
					$new_img_features[] = $row2;
				}
			}
			$data['value'] = json_encode($new_img_features);
			$this->db->where('type', 'home_slider_image');
			$this->db->update('frontend_settings', $data);
			recache();
		}
	}

	function login()
	{
		if ($this->admin_permission() == TRUE) {
			redirect(base_url() . 'admin/', 'refresh');
		} else {
			$page_data['login_error'] = "";
			if ($this->session->flashdata('alert') == "login_error") {
				$page_data['login_error'] = translate("Your_email_or_password_is_Invalid!");
			}
			if ($this->session->flashdata('alert') == "not_sent") {
				$page_data['login_error'] = translate("failed_to_send_your_email!");
			}
			if ($this->session->flashdata('alert') == "no_email") {
				$page_data['login_error'] = translate("Your_email__is_Invalid!");
			}
			if ($this->session->flashdata('alert') == "email_sent") {
				$page_data['success_alert'] = translate("email_sent_successfully!");
			}
			$this->load->view('back/login', $page_data);
		}
	}

	function forget_pass()
	{
		if ($this->admin_permission() == TRUE) {
			redirect(base_url() . 'admin/', 'refresh');
		} else {
			$page_data['forget_pass_error'] = "";
			if ($this->session->flashdata('alert') == "forget_pass_error") {
				$page_data['forget_pass_error'] = "Your <b>Email</b> or <b>Password</b> is Invalid!";
			}
			$this->load->view('back/forget_pass', $page_data);
		}
	}

	function submit_forget_pass()
	{
		$this->form_validation->set_rules('email', 'Email', 'required');

		if ($this->form_validation->run() == FALSE) {
			$ajax_error[] = array('ajax_error'  =>  validation_errors());
			echo json_encode($ajax_error);
		} else {
			$query = $this->db->get_where('admin', array(
				'email' => $this->input->post('email')
			));
			if ($query->num_rows() > 0) {
				$admin_id = $query->row()->admin_id;
				$password = substr(hash('sha512', rand()), 0, 12);
				$data['password'] = sha1($password);
				if ($this->Email_model->password_reset_email('admin', $admin_id, $password)) {
					$this->db->where('admin_id', $admin_id);
					$this->db->update('admin', $data);
					recache();
					$this->session->set_flashdata('alert', 'email_sent');
				} else {
					$this->session->set_flashdata('alert', 'not_sent');
				}
			} else {
				$this->session->set_flashdata('alert', 'no_email');
			}
			redirect(base_url() . 'admin/login', 'refresh');
		}
	}

	function manage_language($para1 = "", $para2 = "", $para3 = "", $para4 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "manage_language/index.php";
				$page_data['folder'] = "manage_language";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "manage_language/index.php";
				$page_data['page_name'] = "manage_language";
				$page_data['all_language_list'] = $this->db->get("site_language_list")->result();
				if ($this->session->flashdata('alert') == "publish") {
					$page_data['success_alert'] = translate("you_have_successfully_published_the_language!");
				}
				if ($this->session->flashdata('alert') == "unpublish") {
					$page_data['success_alert'] = translate("you_have_successfully_unpublished_the_language!");
				}
				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_language!");
				}
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_language!");
				}
				if ($this->session->flashdata('alert') == "delete") {
					$page_data['danger_alert'] = translate("you_have_successfully_deleted_the_language!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "approval") {
				if ($para2 == "no") {
					$data['status'] = "ok";
					$this->session->set_flashdata('alert', 'publish');
				} elseif ($para2 == "ok") {
					$data['status'] = "no";
					$this->session->set_flashdata('alert', 'unpublish');
				}
				$this->db->where('site_language_list_id', $para3);
				$this->db->update('site_language_list', $data);
				recache();
			} elseif ($para1 == "add_site_language") {
				$this->load->view('back/manage_language/add_site_language');
			} elseif ($para1 == "edit_site_language") {
				$page_data['get_site_language'] = $this->db->get_where("site_language_list", array("site_language_list_id" => $para2))->result();
				$this->load->view('back/manage_language/edit_site_language', $page_data);
			} elseif ($para1 == "do_add") {
				$data['name'] = $this->input->post('language_name');
				$this->db->insert('site_language_list', $data);

				$id = $this->db->insert_id();
				if (!demo()) {
					move_uploaded_file($_FILES['language_icon']['tmp_name'], 'uploads/language_list_image/language_' . $id . '.jpg');
				}
				$language = 'lang_' . $id;

				$this->db->where('site_language_list_id', $id);
				$this->db->update('site_language_list', array(
					'db_field' => $language,
					'status' => 'ok'
				));
				recache();

				$this->session->set_flashdata('alert', 'add');
				add_language($language);
				redirect(base_url() . 'admin/manage_language', 'refresh');
			} elseif ($para1 == "update") {
				$this->db->where('site_language_list_id', $para2);
				$this->db->update('site_language_list', array(
					'name' => $this->input->post('language_name')
				));
				recache();
				if (!demo()) {
					if ($this->input->post('is_edit') == '1') {
						move_uploaded_file($_FILES['language_icon']['tmp_name'], 'uploads/language_list_image/language_' . $para2 . '.jpg');
					}
				}
				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/manage_language', 'refresh');
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$this->db->where('db_field', $para2);
				$this->db->delete('site_language_list');
				recache();
				$this->load->dbforge();
				$this->dbforge->drop_column('site_language', $para2);

				$lid = $this->db->get_where('site_language_list', array('db_field' => $para2))->row()->site_language_list_id;
				unlink('uploads/language_list_image/language_' . $lid . '.jpg');

				$this->session->set_flashdata('alert', 'delete');
			} elseif ($para1 == "set_translation") {
				if ($para3 == "") {
					$page_data['top'] = "manage_language/index.php";
					$page_data['folder'] = "manage_language/set_translation";
					$page_data['file'] = "index.php";
					$page_data['bottom'] = "manage_language/index.php";
					$page_data['page_name'] = "manage_language";
					$page_data['selected_language'] = $para2;

					$this->load->view('back/index', $page_data);
				} elseif ($para3 == "list_data") {
					$columns = array(
						0 => 'word',
						1 => 'word',
					);
					$limit = $this->input->post('length');
					$start = $this->input->post('start');
					$order = $columns[$this->input->post('order')[0]['column']];
					$dir = $this->input->post('order')[0]['dir'];
					$table = 'site_language';

					$totalData = $this->Crud_model->alldata_count($table);

					$totalFiltered = $totalData;

					if (empty($this->input->post('search')['value'])) {
						$rows = $this->Crud_model->allsite_language($table, $limit, $start, $order, $dir);
					} else {
						$search = $this->input->post('search')['value'];
						$rows = $this->Crud_model->site_language_search($table, $limit, $start, $search, $order, $dir);
						$totalFiltered = $this->Crud_model->site_language_search_count($table, $search);
					}

					$data = array();
					if (!empty($rows)) {
						if ($dir == 'asc') {
							$i = $start + 1;
						} elseif ($dir == 'desc') {
							$i = $totalFiltered - $start;
						}
						foreach ($rows as $row) {
							$nestedData['#'] = $i;
							$nestedData['word'] = "<span class='abv'>" . ucwords(str_replace('_', ' ', $row->word)) . "</span>";
							$nestedData['translation'] = "<form class='form-horizontal trs' id='" . $para2 . "_" . $row->word_id . "' method='post' action='" . base_url() . "admin/manage_language/upd_trn/" . $row->word_id . "'><div class='input-group' style='width:100%'><input type='text' name='translation' class='form-control input-sm ann' value='" . $row->$para2 . "' style='border: 1px solid rgb(48, 68, 87); height:24px'><span class='input-group-btn'><button type='button' class='btn btn-dark btn-xs btn-labeled fa fa-save submittera' data-wid='" . $para2 . "_" . $row->word_id . "' style='padding: 3px 5px'>Save</button></span></div><input type='hidden' name='lang' value='" . $para2 . "'></form>";

							$data[] = $nestedData;
							if ($dir == 'asc') {
								$i++;
							} elseif ($dir == 'desc') {
								$i--;
							}
						}
					}

					$json_data = array(
						"draw"            => intval($this->input->post('draw')),
						"recordsTotal"    => intval($totalData),
						"recordsFiltered" => intval($totalFiltered),
						"data"            => $data
					);
					echo json_encode($json_data);
				}
			} elseif ($para1 == "upd_trn") {
				$word_id = $para2;
				$translation = $this->input->post('translation');
				$language = $this->input->post('lang');
				$word = $this->db->get_where('site_language', array(
					'word_id' => $word_id
				))->row()->word;
				add_translation($word, $language, $translation);
			}
		}
	}

	function manage_admin()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "manage_admin/index.php";
			$page_data['folder'] = "manage_admin";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "manage_admin/index.php";
			$page_data['page_name'] = "manage_admin";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_your_profile!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_your_profile!");
			} elseif ($this->session->flashdata('alert') == "pass_edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_your_password!");
			} elseif ($this->session->flashdata('alert') == "pass_failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_your_password!");
			} elseif ($this->session->flashdata('alert') == "pass_matched") {
				$page_data['danger_alert'] = translate("your_current_&_new_password_can_not_be_the_same!");
			} elseif ($this->session->flashdata('alert') == "pass_not_matched") {
				$page_data['danger_alert'] = translate("invalid_current_password!");
			} elseif ($this->session->flashdata('alert') == "confirm_pass_fail") {
				$page_data['danger_alert'] = translate("your_new_and_confirm_password_is_not_matched!");
			} elseif ($this->session->flashdata('alert') == "image_success") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_backgroundimage!");
			} elseif ($this->session->flashdata('alert') == "failed_image") {
				$page_data['danger_alert'] = translate("image_upload_failed!_please_make_sure_your_image_format_is_JPG,_JPEG_or_PNG.");
			} elseif ($this->session->flashdata('alert') == "demo_msg") {
				$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
			}
			$this->load->view('back/index', $page_data);
		}
	}

	function update_admin_profile($para1 = "")
	{
		if ($para1 == "update_details") {
			if (demo()) {
				$this->session->set_flashdata('alert', 'demo_msg');
				redirect(base_url() . 'admin/manage_admin', 'refresh');
			}
			$this->form_validation->set_rules('name', 'Name', 'required');
			$this->form_validation->set_rules('email', 'Email', 'required');
			if ($this->form_validation->run() == FALSE) {
				$ajax_error[] = array('ajax_error' => validation_errors());
				echo json_encode($ajax_error);
			} else {
				$admin_id = $this->session->userdata('admin_id');
				$data['name'] = $this->input->post('name');
				$data['email'] = $this->input->post('email');
				$data['phone'] = $this->input->post('phone');
				$data['address'] = $this->input->post('address');
				$this->db->where('admin_id', $admin_id);
				$result = $this->db->update('admin', $data);
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'edit');
				} else {
					$this->session->set_flashdata('alert', 'failed_edit');
				}
				redirect(base_url() . 'admin/manage_admin', 'refresh');
			}
		} elseif ($para1 == "update_pass_details") {
			if (demo()) {
				$this->session->set_flashdata('alert', 'demo_msg');
				redirect(base_url() . 'admin/manage_admin', 'refresh');
			}
			$this->form_validation->set_rules('current_password', 'Current Password', 'required');
			$this->form_validation->set_rules('new_password', 'New Password', 'required');
			$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required');
			if ($this->form_validation->run() == FALSE) {
				redirect(base_url() . 'admin/manage_admin', 'refresh');
			} else {
				$admin_id = $this->session->userdata('admin_id');
				$current_password = sha1($this->input->post('current_password'));
				$data['password'] = sha1($this->input->post('new_password'));
				$confirm_password = sha1($this->input->post('confirm_password'));
				$prev_password = $this->db->get_where("admin", array("admin_id" => $admin_id))->row()->password;
				if ($current_password == $prev_password && $data['password'] != $current_password && $data['password'] == $confirm_password) {
					$this->db->where('admin_id', $admin_id);
					$result = $this->db->update('admin', $data);
					recache();
					if ($result) {
						$this->session->set_flashdata('alert', 'pass_edit');
					} else {
						$this->session->set_flashdata('alert', 'pass_failed_edit');
					}
					redirect(base_url() . 'admin/manage_admin', 'refresh');
				} elseif ($current_password != $prev_password) {
					$this->session->set_flashdata('alert', 'pass_not_matched');
					redirect(base_url() . 'admin/manage_admin', 'refresh');
				} elseif ($data['password'] == $current_password) {
					$this->session->set_flashdata('alert', 'pass_matched');
					redirect(base_url() . 'admin/manage_admin', 'refresh');
				} elseif ($data['password'] != $confirm_password) {
					$this->session->set_flashdata('alert', 'confirm_pass_fail');
					redirect(base_url() . 'admin/manage_admin', 'refresh');
				}
			}
		} elseif ($para1 == "update_login_page") {
			if (!demo()) {
				$prev_image_info = $this->db->get_where('general_settings', array('type' => 'admin_login_image'))->row()->value;
				if ($prev_image_info != '[]') {
					$prev_image_info = json_decode($prev_image_info, true);
					$prev_image = $prev_image_info[0]['image'];
				}
				$is_edit = $this->input->post('is_edit');
				if ($is_edit == '0') {
					if ($_FILES['admin_login_image']['name'] !== '') {
						$path = $_FILES['admin_login_image']['name'];
						$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
						$img_file_name = "admin_login_image_" . time() . $ext;
						if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
							move_uploaded_file($_FILES['admin_login_image']['tmp_name'], 'uploads/admin_login_image/' . $img_file_name);
							$admin_login_image[] = array('image' => $img_file_name);

							$data['value'] = json_encode($admin_login_image);

							$this->db->where('type', 'admin_login_image');
							$this->db->update('general_settings', $data);
							recache();

							$this->session->set_flashdata('alert', 'image_success');
						} else {
							$this->session->set_flashdata('alert', 'failed_image');
						}
					}
					$this->session->set_flashdata('alert', 'image_success');
				} elseif ($is_edit == '1') {
					if ($_FILES['admin_login_image']['name'] !== '') {
						$path = $_FILES['admin_login_image']['name'];
						$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
						$img_file_name = "admin_login_image_" . time() . $ext;
						if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
							move_uploaded_file($_FILES['admin_login_image']['tmp_name'], 'uploads/admin_login_image/' . $img_file_name);
							$admin_login_image[] = array('image' => $img_file_name);

							$data['value'] = json_encode($admin_login_image);

							$this->db->where('type', 'admin_login_image');
							$this->db->update('general_settings', $data);
							recache();

							if ($prev_image_info != '[]' && file_exists('uploads/admin_login_image/' . $prev_image)) {
								unlink('uploads/admin_login_image/' . $prev_image);
							}
							$this->session->set_flashdata('alert', 'image_success');
						} else {
							$this->session->set_flashdata('alert', 'failed_image');
						}
					}
				}
			}

			redirect(base_url() . 'admin/manage_admin', 'refresh');
		} elseif ($para1 == "update_forget_pass_page") {
			if (!demo()) {
				$prev_image_info = $this->db->get_where('general_settings', array('type' => 'forget_pass_image'))->row()->value;
				if ($prev_image_info != '[]') {
					$prev_image_info = json_decode($prev_image_info, true);
					$prev_image = $prev_image_info[0]['image'];
				}
				$is_edit = $this->input->post('is_edit');
				if ($is_edit == '0') {
					if ($_FILES['forget_pass_image']['name'] !== '') {
						$path = $_FILES['forget_pass_image']['name'];
						$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
						$img_file_name = "forget_pass_image_" . time() . $ext;
						if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
							move_uploaded_file($_FILES['forget_pass_image']['tmp_name'], 'uploads/forget_pass_image/' . $img_file_name);
							$forget_pass_image[] = array('image' => $img_file_name);

							$data['value'] = json_encode($forget_pass_image);

							$this->db->where('type', 'forget_pass_image');
							$this->db->update('general_settings', $data);
							recache();

							$this->session->set_flashdata('alert', 'image_success');
						} else {
							$this->session->set_flashdata('alert', 'failed_image');
						}
					}
					$this->session->set_flashdata('alert', 'image_success');
				} elseif ($is_edit == '1') {
					if ($_FILES['forget_pass_image']['name'] !== '') {
						$path = $_FILES['forget_pass_image']['name'];
						$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
						$img_file_name = "forget_pass_image_" . time() . $ext;
						if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
							move_uploaded_file($_FILES['forget_pass_image']['tmp_name'], 'uploads/forget_pass_image/' . $img_file_name);
							$forget_pass_image[] = array('image' => $img_file_name);

							$data['value'] = json_encode($forget_pass_image);

							$this->db->where('type', 'forget_pass_image');
							$this->db->update('general_settings', $data);
							recache();

							if ($prev_image_info != '[]' && file_exists('uploads/forget_pass_image/' . $prev_image)) {
								unlink('uploads/forget_pass_image/' . $prev_image);
							}
							$this->session->set_flashdata('alert', 'image_success');
						} else {
							$this->session->set_flashdata('alert', 'failed_image');
						}
					}
				}
			}
			redirect(base_url() . 'admin/manage_admin', 'refresh');
		}
	}

	function profile_sections()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "profile_sections/index.php";
			$page_data['folder'] = "profile_sections";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "profile_sections/index.php";
			$page_data['page_name'] = "profile_sections";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_the_settings!");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_profile_sections_settings()
	{
		$present_address_status = $this->input->post('present_address_status');
		$education_and_career_status = $this->input->post('education_and_career_status');
		$physical_attributes_status = $this->input->post('physical_attributes_status');
		$language_status = $this->input->post('language_status');
		$hobbies_and_interests_status = $this->input->post('hobbies_and_interests_status');
		$personal_attitude_and_behavior_status = $this->input->post('personal_attitude_and_behavior_status');
		$residency_information_status = $this->input->post('residency_information_status');
		$spiritual_and_social_background_status = $this->input->post('spiritual_and_social_background_status');
		$life_style_status = $this->input->post('life_style_status');
		$astronomic_information_status = $this->input->post('astronomic_information_status');
		$permanent_address_status = $this->input->post('permanent_address_status');
		$family_information_status = $this->input->post('family_information_status');
		$additional_personal_details_status = $this->input->post('additional_personal_details_status');
		$partner_expectation_status = $this->input->post('partner_expectation_status');
		if (isset($present_address_status)) {
			$data1['value'] = "yes";
		} else {
			$data1['value'] = "no";
		}
		if (isset($education_and_career_status)) {
			$data2['value'] = "yes";
		} else {
			$data2['value'] = "no";
		}
		if (isset($physical_attributes_status)) {
			$data3['value'] = "yes";
		} else {
			$data3['value'] = "no";
		}
		if (isset($language_status)) {
			$data4['value'] = "yes";
		} else {
			$data4['value'] = "no";
		}
		if (isset($hobbies_and_interests_status)) {
			$data5['value'] = "yes";
		} else {
			$data5['value'] = "no";
		}
		if (isset($personal_attitude_and_behavior_status)) {
			$data6['value'] = "yes";
		} else {
			$data6['value'] = "no";
		}
		if (isset($residency_information_status)) {
			$data7['value'] = "yes";
		} else {
			$data7['value'] = "no";
		}
		if (isset($spiritual_and_social_background_status)) {
			$data8['value'] = "yes";
		} else {
			$data8['value'] = "no";
		}
		if (isset($life_style_status)) {
			$data9['value'] = "yes";
		} else {
			$data9['value'] = "no";
		}
		if (isset($astronomic_information_status)) {
			$data10['value'] = "yes";
		} else {
			$data10['value'] = "no";
		}
		if (isset($permanent_address_status)) {
			$data11['value'] = "yes";
		} else {
			$data11['value'] = "no";
		}
		if (isset($family_information_status)) {
			$data12['value'] = "yes";
		} else {
			$data12['value'] = "no";
		}
		if (isset($additional_personal_details_status)) {
			$data13['value'] = "yes";
		} else {
			$data13['value'] = "no";
		}
		if (isset($partner_expectation_status)) {
			$data14['value'] = "yes";
		} else {
			$data14['value'] = "no";
		}

		$this->db->where('type', 'present_address');
		$this->db->update('frontend_settings', $data1);

		$this->db->where('type', 'education_and_career');
		$this->db->update('frontend_settings', $data2);

		$this->db->where('type', 'physical_attributes');
		$this->db->update('frontend_settings', $data3);

		$this->db->where('type', 'language');
		$this->db->update('frontend_settings', $data4);

		$this->db->where('type', 'hobbies_and_interests');
		$this->db->update('frontend_settings', $data5);

		$this->db->where('type', 'personal_attitude_and_behavior');
		$this->db->update('frontend_settings', $data6);

		$this->db->where('type', 'residency_information');
		$this->db->update('frontend_settings', $data7);

		$this->db->where('type', 'spiritual_and_social_background');
		$this->db->update('frontend_settings', $data8);

		$this->db->where('type', 'life_style');
		$this->db->update('frontend_settings', $data9);

		$this->db->where('type', 'astronomic_information');
		$this->db->update('frontend_settings', $data10);

		$this->db->where('type', 'permanent_address');
		$this->db->update('frontend_settings', $data11);

		$this->db->where('type', 'family_information');
		$this->db->update('frontend_settings', $data12);

		$this->db->where('type', 'additional_personal_details');
		$this->db->update('frontend_settings', $data13);

		$this->db->where('type', 'partner_expectation');
		$result = $this->db->update('frontend_settings', $data14);

		recache();

		if ($result) {
			$this->session->set_flashdata('alert', 'edit');
		} else {
			$this->session->set_flashdata('alert', 'failed_edit');
		}
		redirect(base_url() . 'admin/profile_sections', 'refresh');
	}


	function social_media_comments()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "social_media_comments/index.php";
			$page_data['folder'] = "social_media_comments";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "social_media_comments/index.php";
			$page_data['page_name'] = "social_media_comments";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_the_settings!");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_social_media_comments_settings()
	{
		$data1['value'] = $this->input->post('type');
		$data2['value'] = $this->input->post('discus_id');
		$data3['value'] = $this->input->post('facebook_comment_api');

		$this->db->where('type', 'comment_type');
		$result = $this->db->update('third_party_settings', $data1);

		$this->db->where('type', 'discus_id');
		$result = $this->db->update('third_party_settings', $data2);

		$this->db->where('type', 'fb_comment_api');
		$result = $this->db->update('third_party_settings', $data3);
		recache();

		if ($result) {
			$this->session->set_flashdata('alert', 'edit');
		} else {
			$this->session->set_flashdata('alert', 'failed_edit');
		}
		redirect(base_url() . 'admin/social_media_comments', 'refresh');
	}

	function captcha_settings()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "captcha_settings/index.php";
			$page_data['folder'] = "captcha_settings";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "captcha_settings/index.php";
			$page_data['page_name'] = "captcha_settings";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_the_settings!");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_captcha_settings()
	{
		$captcha_activation = $this->input->post('captcha_activation');
		if (isset($captcha_activation)) {
			$data1['value'] = "ok";
		} else {
			$data1['value'] = "no";
		}
		$data2['value'] = $this->input->post('captcha_public');
		$data3['value'] = $this->input->post('captcha_private');

		$this->db->where('type', 'captcha_status');
		$result = $this->db->update('third_party_settings', $data1);

		$this->db->where('type', 'captcha_public');
		$result = $this->db->update('third_party_settings', $data2);

		$this->db->where('type', 'captcha_private');
		$result = $this->db->update('third_party_settings', $data3);
		recache();

		if ($result) {
			$this->session->set_flashdata('alert', 'edit');
		} else {
			$this->session->set_flashdata('alert', 'failed_edit');
		}
		redirect(base_url() . 'admin/captcha_settings', 'refresh');
	}

	function social_media_login()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "captcha_settings/index.php";
			$page_data['folder'] = "social_media_login";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "captcha_settings/index.php";
			$page_data['page_name'] = "social_media_login";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_the_settings!");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_social_media_configurations($para1 = "")
	{

		if ($para1 == "google") {
			$g_login_activation = $this->input->post('g_login_activation');
			if (isset($g_login_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('g_client_id');
			$data3['value'] = $this->input->post('g_client_secret');

			$this->db->where('type', 'g_login_set');
			$result = $this->db->update('third_party_settings', $data1);

			$this->db->where('type', 'g_client_id');
			$result = $this->db->update('third_party_settings', $data2);

			$this->db->where('type', 'g_client_secret');
			$result = $this->db->update('third_party_settings', $data3);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/social_media_login', 'refresh');
		} elseif ($para1 == "facebook") {
			$fb_login_activation = $this->input->post('fb_login_activation');
			if (isset($fb_login_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('fb_appid');
			$data3['value'] = $this->input->post('fb_secret');

			$this->db->where('type', 'fb_login_set');
			$result = $this->db->update('third_party_settings', $data1);

			$this->db->where('type', 'fb_appid');
			$result = $this->db->update('third_party_settings', $data2);

			$this->db->where('type', 'fb_secret');
			$result = $this->db->update('third_party_settings', $data3);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/social_media_login', 'refresh');
		} elseif ($para1 == "twitter") {

			$twitter_login_activation = $this->input->post('twitter_login_activation');
			if (isset($twitter_login_activation)) {
				$data1['value'] = "ok";
			} else {
				$data1['value'] = "no";
			}
			$data2['value'] = $this->input->post('twitter_app_key');
			$data3['value'] = $this->input->post('twitter_app_secret');

			$this->db->where('type', 'twitter_login_set');
			$result = $this->db->update('third_party_settings', $data1);

			$this->db->where('type', 'twitter_app_key');
			$result = $this->db->update('third_party_settings', $data2);

			$this->db->where('type', 'twitter_app_secret');
			$result = $this->db->update('third_party_settings', $data3);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/social_media_login', 'refresh');
		}
	}

	function google_analytics_settings()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "google_analytics_settings/index.php";
			$page_data['folder'] = "google_analytics_settings";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "google_analytics_settings/index.php";
			$page_data['page_name'] = "google_analytics_settings";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_the_settings!");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_google_analytics_settings()
	{
		$google_analytics_activation = $this->input->post('google_analytics_activation');
		if (isset($google_analytics_activation)) {
			$data1['value'] = "yes";
		} else {
			$data1['value'] = "no";
		}
		$data2['value'] = $this->input->post('google_analytics_key');

		$this->db->where('type', 'google_analytics_set');
		$result = $this->db->update('third_party_settings', $data1);

		$this->db->where('type', 'google_analytics_key');
		$result = $this->db->update('third_party_settings', $data2);
		recache();

		if ($result) {
			$this->session->set_flashdata('alert', 'edit');
		} else {
			$this->session->set_flashdata('alert', 'failed_edit');
		}
		redirect(base_url() . 'admin/google_analytics_settings', 'refresh');
	}

	// FACEBOOK CHAT SETTINGS
	function facebook_chat_settings()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "facebook_chat_settings/index.php";
			$page_data['folder'] = "facebook_chat_settings";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "facebook_chat_settings/index.php";
			$page_data['page_name'] = "facebook_chat_settings";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_the_settings!");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_facebook_chat_settings()
	{
		$facebook_chat_activation = $this->input->post('facebook_chat_activation');
		if (isset($facebook_chat_activation)) {
			$data1['value'] = "yes";
		} else {
			$data1['value'] = "no";
		}
		$data2['value'] = $this->input->post('facebook_chat_page_id');
		$data3['value'] = $this->input->post('facebook_chat_logged_in_greeting');
		$data4['value'] = $this->input->post('facebook_chat_logged_out_greeting');
		$data5['value'] = $this->input->post('facebook_chat_theme_color');

		$this->db->where('type', 'facebook_chat_set');
		$result = $this->db->update('third_party_settings', $data1);

		$this->db->where('type', 'facebook_chat_page_id');
		$result = $this->db->update('third_party_settings', $data2);

		$this->db->where('type', 'facebook_chat_logged_in_greeting');
		$result = $this->db->update('third_party_settings', $data3);

		$this->db->where('type', 'facebook_chat_logged_out_greeting');
		$result = $this->db->update('third_party_settings', $data4);

		$this->db->where('type', 'facebook_chat_theme_color');
		$result = $this->db->update('third_party_settings', $data5);

		recache();

		if ($result) {
			$this->session->set_flashdata('alert', 'edit');
		} else {
			$this->session->set_flashdata('alert', 'failed_edit');
		}
		redirect(base_url() . 'admin/facebook_chat_settings', 'refresh');
	}



	function seo_settings()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "seo_settings/index.php";
			$page_data['folder'] = "seo_settings";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "seo_settings/index.php";
			$page_data['page_name'] = "seo_settings";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_the_settings!");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_seo_settings()
	{
		$data1['value'] = $this->input->post('seo_keywords');
		$data2['value'] = $this->input->post('seo_author');
		$data3['value'] = $this->input->post('seo_revisit');
		$data4['value'] = $this->input->post('seo_description');
		$data5['value'] = $this->input->post('title');

		$this->db->where('general_settings_id', 25);
		$result = $this->db->update('general_settings', $data1);

		$this->db->where('general_settings_id', 26);
		$result = $this->db->update('general_settings', $data2);

		$this->db->where('general_settings_id', 54);
		$result = $this->db->update('general_settings', $data3);

		$this->db->where('general_settings_id', 24);
		$result = $this->db->update('general_settings', $data4);

		$this->db->where('general_settings_id', 89);
		$result = $this->db->update('general_settings', $data5);

		if (!demo()) {
			$is_edit = $this->input->post('is_edit');

			if ($is_edit == '0') {
				if ($_FILES['seo_image_facebook']['name'] !== '') {
					$path = $_FILES['seo_image_facebook']['name'];
					$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
					$img_file_name = "seo_image_facebook_" . time() . $ext;
					if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
						move_uploaded_file($_FILES['seo_image_facebook']['tmp_name'], 'uploads/seo_image/' . $img_file_name);

						$data['value'] = $img_file_name;

						$this->db->where('type', 'seo_image_facebook');
						$this->db->update('general_settings', $data);
						recache();

						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_image');
					}
				} elseif ($_FILES['seo_image_twitter']['name'] !== '') {
					$path = $_FILES['seo_image_twitter']['name'];
					$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
					$img_file_name = "seo_image_twitter_" . time() . $ext;
					if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
						move_uploaded_file($_FILES['seo_image_twitter']['tmp_name'], 'uploads/seo_image/' . $img_file_name);

						$data['value'] = $img_file_name;

						$this->db->where('type', 'seo_image_twitter');
						$this->db->update('general_settings', $data);
						recache();

						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_image');
					}
				}
			} elseif ($is_edit == '1') {

				$prev_seo_fb_image = $this->db->get_where('general_settings', array('type' => 'seo_image_facebook'))->row()->value;

				if ($_FILES['seo_image_facebook']['name'] !== '') {
					$path = $_FILES['seo_image_facebook']['name'];
					$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
					$img_file_name = "seo_image_facebook_" . time() . $ext;
					if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
						move_uploaded_file($_FILES['seo_image_facebook']['tmp_name'], 'uploads/seo_image/' . $img_file_name);

						$data['value'] = $img_file_name;

						$this->db->where('type', 'seo_image_facebook');
						$this->db->update('general_settings', $data);
						recache();

						if ($prev_seo_fb_image != '' && file_exists('uploads/seo_image/' . $prev_seo_fb_image)) {
							unlink('uploads/seo_image/' . $prev_seo_fb_image);
						}
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_image');
					}
				} elseif ($_FILES['seo_image_twitter']['name'] !== '') {
					$prev_seo_twitter_image = $this->db->get_where('general_settings', array('type' => 'seo_image_twitter'))->row()->value;
					$path = $_FILES['seo_image_twitter']['name'];
					$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
					$img_file_name = "seo_image_twitter_" . time() . $ext;
					if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
						move_uploaded_file($_FILES['seo_image_twitter']['tmp_name'], 'uploads/seo_image/' . $img_file_name);

						$data['value'] = $img_file_name;

						$this->db->where('type', 'seo_image_twitter');
						$this->db->update('general_settings', $data);
						recache();

						if ($prev_seo_twitter_image != '' && file_exists('uploads/seo_image/' . $prev_seo_twitter_image)) {
							unlink('uploads/seo_image/' . $prev_seo_twitter_image);
						}
						$this->session->set_flashdata('alert', 'edit');
					} else {
						$this->session->set_flashdata('alert', 'failed_image');
					}
				}
			}
		}
		recache();

		if ($result) {
			$this->session->set_flashdata('alert', 'edit');
		} else {
			$this->session->set_flashdata('alert', 'failed_edit');
		}
		redirect(base_url() . 'admin/seo_settings', 'refresh');
	}

	function email_setup()
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			$page_data['top'] = "email_setup/index.php";
			$page_data['folder'] = "email_setup";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "email_setup/index.php";
			$page_data['page_name'] = "email_setup";
			if ($this->session->flashdata('alert') == "edit") {
				$page_data['success_alert'] = translate("you_have_successfully_updated_the_settings!");
			} elseif ($this->session->flashdata('alert') == "failed_edit") {
				$page_data['danger_alert'] = translate("failed_to_updated_the_settings!");
			}

			$this->load->view('back/index', $page_data);
		}
	}

	function update_email_setup($para1 = "")
	{
		if ($para1 == "password_reset_email") {
			$data1['subject'] = $this->input->post('password_reset_email_sub');
			$data2['body'] = $this->input->post('password_reset_email_body');

			$this->db->where('email_template_id', 1);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 1);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		} elseif ($para1 == "package_purchase_email") {
			$data1['subject'] = $this->input->post('account_approval_email_sub');
			$data2['body'] = $this->input->post('account_approval_email_body');

			$this->db->where('email_template_id', 2);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 2);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		} elseif ($para1 == "account_opening_email") {
			$data1['subject'] = $this->input->post('account_opening_email_sub');
			$data2['body'] = $this->input->post('account_opening_email_body');

			$this->db->where('email_template_id', 4);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 4);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		} elseif ($para1 == "account_opening_from_admin_email") {
			$data1['subject'] = $this->input->post('account_opening_from_admin_email_sub');
			$data2['body'] = $this->input->post('account_opening_from_user_email_body');

			$this->db->where('email_template_id', 7);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 7);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		} elseif ($para1 == "staff_add_email") {
			$data1['subject'] = $this->input->post('staff_add_email_sub');
			$data2['body'] = $this->input->post('staff_add_email_body');

			$this->db->where('email_template_id', 5);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 5);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		} elseif ($para1 == "member_approval_email") {
			$data1['subject'] = $this->input->post('member_approval_email_sub');
			$data2['body'] = $this->input->post('member_approval_email_body');

			$this->db->where('email_template_id', 6);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 6);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		} elseif ($para1 == "member_registration_email_to_admin") {
			$data1['subject'] = $this->input->post('member_registration_email_to_admin_sub');
			$data2['body'] = $this->input->post('member_registration_email_to_admin_body');

			$this->db->where('email_template_id', 8);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 8);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		} elseif ($para1 == "account_opening_by_user_member_approval_deactivated") {
			$data1['subject'] = $this->input->post('account_opening_by_user_member_approval_deactivated_sub');
			$data2['body'] = $this->input->post('account_opening_by_user_member_approval_deactivated_body');

			$this->db->where('email_template_id', 9);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 9);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		} elseif ($para1 == "resend_email_verification_email") {
			$data1['subject'] = $this->input->post('resend_email_verification_email_sub');
			$data2['body'] = $this->input->post('resend_email_verification_email_body');

			$this->db->where('email_template_id', 10);
			$result = $this->db->update('email_template', $data1);

			$this->db->where('email_template_id', 10);
			$result = $this->db->update('email_template', $data2);
			recache();

			if ($result) {
				$this->session->set_flashdata('alert', 'edit');
			} else {
				$this->session->set_flashdata('alert', 'failed_edit');
			}
			redirect(base_url() . 'admin/email_setup', 'refresh');
		}
	}

	function check_login()
	{
		if ($this->admin_permission() == TRUE) {
			redirect(base_url() . 'admin/', 'refresh');
		} else {
			$username = $this->input->post('email');
			$password = sha1($this->input->post('password'));

			$result = $this->Crud_model->check_login('admin', $username, $password);
			// echo $this->db->last_query();
			//print_r($result);exit;
			$data = array();
			if ($result) {
				$data['admin_name'] = $result->email;
				$data['admin_id'] = $result->admin_id;
				$data['role_id'] = $result->role;
				$data['name'] = $result->name;
				$data['user_type'] = 1;

				$this->session->set_userdata($data);

				redirect(base_url() . 'admin/index', 'refresh');
			} else {
				$this->session->set_flashdata('alert', 'login_error');

				redirect(base_url() . 'admin/login', 'refresh');
			}
		}
	}

	function logout()
	{
		$this->session->unset_userdata('admin_name');
		$this->session->unset_userdata('admin_id');
		session_destroy();
		redirect(base_url() . 'admin/login', 'refresh');
	}

	// Sql and script backup
	function backup($para1 = "", $para2 = "")
	{

		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {


				if (file_exists('downloaded-sql-backup.zip')) {
					unlink('downloaded-sql-backup.zip');
				} else if (file_exists('downloaded-project-backup.zip')) {
					unlink('downloaded-project-backup.zip');
				} else if (file_exists('downloaded-script-backup.zip')) {
					unlink('downloaded-script-backup.zip');
				}
				$page_data['top'] 		= "backup/index.php";
				$page_data['folder'] 	= "backup";
				$page_data['file'] 		= "index.php";
				$page_data['bottom']	= "backup/index.php";
				$page_data['page_name'] = "backup";

				if ($this->session->flashdata('alert') == "backup_success") {
					$page_data['success_alert'] = translate("backup_completed!");
				} elseif ($this->session->flashdata('alert') == "download_success") {
					$page_data['success_alert'] = translate("downloaded!");
				} elseif ($this->session->flashdata('alert') == "backup_error") {
					$page_data['danger_alert'] = translate("something_went_wrong!");
				} elseif ($this->session->flashdata('alert') == "demo_error") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo");
				}

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "get_backup") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_error');
					redirect(base_url() . 'admin/backup', 'refresh');
				}
				ini_set('memory_limit', '2048M');
				ini_set('max_execution_time', 600);

				$this->load->library('zip');
				$this->load->helper('download');
				$this->load->helper('file');
				$this->zip->clear_data();

				$root = FCPATH;

				$backup_mode = $this->input->post('backup_mode');
				$download_mode = $this->input->post('download_mode');

				$error = '';

				//sql backup, download or save in root
				if ($backup_mode == 'only_sql' || $backup_mode == 'both') {

					$this->load->dbutil();
					$prefs = array(
						'tables' => array(),                     // Array of tables to backup.
						'ignore' => array(),                     // List of tables to omit from the backup
						'format' => "zip",                       // gzip, zip, txt
						'filename' => "matrimonial_db_backup.sql",          // File name - NEEDED ONLY WITH ZIP FILES
						'add_drop' => TRUE,                        // Whether to add DROP TABLE statements to backup file
						'add_insert' => TRUE,                        // Whether to add INSERT data to backup file
						'newline' => "\n"                         // Newline character used in backup file
					);

					$sql_backup = &$this->dbutil->backup($prefs);
					$sql_file_name = $download_mode == 'download' ? 'downloaded-sql-backup.zip' : 'sql-backup-on-' . date("Y-m-d-H") . '.zip';
					$sql_save = $root . '/' . $sql_file_name;

					$this->load->helper('file');
					try {
						write_file($sql_save, $sql_backup);
						$this->zip->clear_data();
					} catch (Exception $e) {
						echo 'not done';
						$error .= "Sql could not be saved.";
					}

					if ($backup_mode == 'only_sql' && $download_mode == 'download') {
						force_download($sql_file_name, $sql_backup);
						unlink($sql_file_name);
						$this->zip->clear_data();

						if ($error == "") {
							$this->session->set_flashdata('alert', 'download_success');
						} else {
							$this->session->set_flashdata('alert', 'backup_error');
						}
						redirect(base_url() . 'admin/backup', 'refresh');
					}
				}

				//full project backup with or without sql
				if ($backup_mode == 'only_script' || $backup_mode == 'both') {

					$file_name = '';

					if ($backup_mode == 'both' && $download_mode == 'root') {
						$file_name = 'project-backup-on-' . date("Y-m-d-H") . '.zip';
					} else if ($backup_mode == 'only_script' && $download_mode == 'root') {
						$file_name = 'script-backup-on-' . date("Y-m-d-H") . '.zip';
					} else if ($backup_mode == 'both' && $download_mode == 'download') {
						$file_name = 'downloaded-project-backup' . '.zip';
					} else if ($backup_mode == 'only_script' && $download_mode == 'download') {
						$file_name = 'downloaded-script-backup' . '.zip';
					}

					$this->zip->clear_data();
					$this->zip->read_dir('./application');
					$this->zip->read_dir('./session');
					$this->zip->read_dir('./system');
					$this->zip->read_dir('./template');
					$this->zip->read_dir('./updates');
					$this->zip->read_dir('./uploads');

					$this->zip->read_file('.htaccess');
					$this->zip->read_file('index.php');

					if ($backup_mode == 'both') {
						$this->zip->read_file($sql_file_name);
					}

					if ($download_mode == 'download') {

						try {
							$this->zip->download($file_name);
							$this->zip->clear_data();
						} catch (Exception $e) {
							$error .= "Script could not be zipped.";
						}

						if ($error == "") {
							$this->session->set_flashdata('alert', 'download_success');
						} else {
							$this->session->set_flashdata('alert', 'backup_error');
						}
						redirect(base_url() . 'admin/backup', 'refresh');
					} else if ($download_mode == 'archive') {

						try {
							$this->zip->archive($root . '/' . $file_name);
							$this->zip->clear_data();
						} catch (Exception $e) {
							$error .= "Script could not be archived.";
						}
					}

					if ($backup_mode == 'both' && isset($sql_save)) {
						unlink($sql_file_name);
					}
				}

				if ($download_mode == 'archive') {

					$backup_success_text = translate("Backup completed");

					if ($error == "") {
						$this->session->set_flashdata('alert', 'backup_success');
					} else {
						$this->session->set_flashdata('alert', 'backup_error');
					}
					redirect(base_url() . 'admin/backup', 'refresh');
				}
				redirect(base_url() . 'admin/backup', 'refresh');
			}
		}
	}




	function update($para1 = "", $para2 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] 		= "update/index.php";
				$page_data['folder'] 	= "update";
				$page_data['file'] 		= "index.php";
				$page_data['bottom']	= "update/index.php";
				$page_data['page_name'] = "update";

				if ($this->session->flashdata('alert') == "update_success") {
					$page_data['success_alert'] = translate("script_updated_successfully!");
				} elseif ($this->session->flashdata('alert') == "update_fail") {
					$page_data['danger_alert'] = translate("something_went_wrong!");
				} elseif ($this->session->flashdata('alert') == "demo_error") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo");
				}
				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "do_update") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_error');
					redirect(base_url() . 'admin/update', 'refresh');
				}

				$error = '';
				if (!empty($_FILES)) {
					try {
						move_uploaded_file($_FILES['update']['tmp_name'], FCPATH . '/update.zip');
					} catch (Exception $e) {
						$error .= $e->getMessage();

						die($e->getMessage());
					}


					if (empty($error) && file_exists('update.zip')) {
						//extract
						$zip = new ZipArchive();
						$file = $zip->open('update.zip');
						if ($file === TRUE) {
							$zip->extractTo(FCPATH);
							$zip->close();
							unlink('update.zip');
						}

						//import sql
						if (file_exists('update.sql')) {

							// Set line to collect lines that wrap
							$templine = '';
							// Read in entire file
							$lines = file('update.sql');

							// Loop through each line
							foreach ($lines as $line) {
								// Skip it if it's a comment
								if (substr($line, 0, 2) == '--' || $line == '')
									continue;

								// Add this line to the current templine we are creating
								$templine .= $line;

								// If it has a semicolon at the end, it's the end of the query so can process this templine
								if (substr(trim($line), -1, 1) == ';') {
									// Perform the query
									$this->db->query($templine);

									// Reset temp variable to empty
									$templine = '';
								}
							}

							unlink('update.sql');
						}
						$this->session->set_flashdata('alert', 'update_success');
					}

					if (!empty($error)) {
						$this->session->set_flashdata('alert', 'update_fail');
					}
				}
				redirect("admin/update");
			}
		}
	}

	// **************************** Member profile details formate Module Start ****************************
	public function download_member_profile_format()
	{
	     error_reporting(E_ALL);
	     ini_set('display_errors',1);
		// Load library
		$this->load->library('pdf');
		// Load all views as normal
		$this->load->view('back/dashboard/member_profile_format');
		// Get output html
		$html = $this->output->get_output();
		$dompdf = new pdf();
		$dompdf->loadHtml($html);
		// To Load Image
		$dompdf->set_option('isRemoteEnabled', TRUE);
		// Render the HTML as PDF
		$dompdf->render();
		// Output the generated PDF to Browser      
		$fileName = 'Member_Profile_Format';
		$dompdf->stream($fileName . ".pdf", array("Attachment" => 0));
	}
	public function download_member_profile_format2()
	{
	    // Get output html
        $html = $this->output->get_output();
        
        // Load pdf library
        $this->load->library('pdf');
        
        // Load HTML content
        $this->pdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation
        $this->pdf->setPaper('A4', 'landscape');
        
        // Render the HTML as PDF
        $this->pdf->render();
        $fileName = 'Member_Profile_Format';
		$dompdf->stream($fileName . ".pdf", array("Attachment" => 0));
	}
	
	

	// **************************** Member profile details formate Module Start ****************************

	// update percentage for profile 04-01-2021
	function update_percentage()
	{
		$per = $_POST['per'];
		$member_id = $_POST['member_id'];
		//print_r($member_id);
		$data = array('percentage' => $per);
		$this->db->where(array('member_id' => $member_id))->update('member', $data);
		$row = $this->db->affected_rows();
		if ($row > 0) {
			$temp['result'] = true;
		} else {
			$temp['result'] = false;
		}
	}
	// ***** end of update percentage******* 



	// ******************** Application code added on (11-12-2020) **************************
	function channel_partner($para1 = "", $para2 = "", $para3 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "channel_partner/channel_partner.php";
				$page_data['folder'] = "channel_partner";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "channel_partner/channel_partner.php";
				$page_data['channel_list'] = $this->db->get("channel_partner")->result();
				$page_data['page_name'] = "channel_partner";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['danger_alert'] = translate("you_have_successfully_deleted_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "failed_image") {
					$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
			} elseif ($para1 == "add_channel") {
				$page_data['top'] = "channel_partner/channel_partner.php";
				$page_data['folder'] = "channel_partner";
				$page_data['file'] = "add_channel.php";
				$page_data['bottom'] = "channel_partner/channel_partner.php";
			} elseif ($para1 == "do_add") {
				// echo "<pre>";
				// print_r($_POST);
				// exit();
				$data['name'] = $this->input->post('name');
				$data['approval_status'] = 1;

				$channel_data['project_name'] = $this->input->post('project_name');
				$channel_data['mobile'] = $this->input->post('mobile');
				$channel_data['address'] = $this->input->post('address');
				$channel_data['testimonial'] = $this->input->post('testimonial');
				$this->db->insert('channel_partner', $data);

				$channel_partner_id = $this->db->insert_id();
				$config = $this->set_ch_partner_image_upload_options();
				$this->load->library('upload');
				$this->upload->initialize($config);

				if (!empty($_FILES['image']['name'])) {
					$id = $channel_partner_id;

					$ins_array = array();
					$counter = count($_FILES['image']['name']);

					for ($i = 0; $i < $counter; $i++) {
						$_FILES['new_image_doc']['name'] = $_FILES['image']['name'][$i];
						$_FILES['new_image_doc']['type'] = $_FILES['image']['type'][$i];
						$_FILES['new_image_doc']['tmp_name'] = $_FILES['image']['tmp_name'][$i];
						$_FILES['new_image_doc']['error'] = $_FILES['image']['error'][$i];
						$_FILES['new_image_doc']['size'] = $_FILES['image']['size'][$i];

						$this->upload->initialize($config);
						$this->upload->do_upload('new_image_doc');
						$img_data = $this->upload->data();
						$ins_array[$i] = $img_data['file_name'];
					}

					if (!empty($ins_array)) {
						$channel_data['image'] = $ins_array;
					}
				}

				for ($i = 0; $i < count($channel_data["project_name"]); $i++) {
					$ch_partner_batch[] = array('project_name' => $channel_data['project_name'][$i], 'mobile' => $channel_data['mobile'][$i], 'address' => $channel_data['address'][$i], 'testimonial' => $channel_data['testimonial'][$i], 'image' => $channel_data['image'][$i], 'channel_partner_id' => $channel_partner_id);
				}

				$this->db->insert_batch('channel_partner_details', $ch_partner_batch);
				$result = $this->db->affected_rows();

				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'add');
					redirect(base_url() . 'admin/channel_partner', 'refresh');
				} else {
					echo "Data Failed to Add!";
				}
				exit;
			} elseif ($para1 == "edit_channel_partner") {
				$page_data['top'] = "channel_partner/channel_partner.php";
				$page_data['folder'] = "channel_partner";
				$page_data['file'] = "edit_channel.php";
				$page_data['bottom'] = "channel_partner/channel_partner.php";
				$page_data['get_channel_partner'] = $this->db->get_where("channel_partner", array("channel_partner_id" => $para2))->row();
			} elseif ($para1 == "update") {
				// echo "<pre>";
				// print_r($_POST);
				// print_r($_FILES);
				// exit();
				$channel_partner_id = $this->input->post('channel_partner_id');
				$data['name'] = $this->input->post('name');

				$channel_data['channel_detail_id'] = $_POST['channel_detail_id'];
				$channel_data['project_name'] = $this->input->post('project_name');
				$channel_data['mobile'] = $this->input->post('mobile');
				$channel_data['address'] = $this->input->post('address');
				$channel_data['testimonial'] = $this->input->post('testimonial');

				$config = $this->set_ch_partner_image_upload_options();
				$this->load->library('upload');
				$this->upload->initialize($config);

				if (!empty($_FILES['image']['name'])) {

					$ins_array = array();
					$counter = count($_FILES['image']['name']);

					for ($i = 0; $i < $counter; $i++) {
						if ($_FILES['image']['size'][$i] > 0) {

							$_FILES['new_image_doc']['name'] = $_FILES['image']['name'][$i];
							$_FILES['new_image_doc']['type'] = $_FILES['image']['type'][$i];
							$_FILES['new_image_doc']['tmp_name'] = $_FILES['image']['tmp_name'][$i];
							$_FILES['new_image_doc']['error'] = $_FILES['image']['error'][$i];
							$_FILES['new_image_doc']['size'] = $_FILES['image']['size'][$i];

							$this->upload->initialize($config);
							$this->upload->do_upload('new_image_doc');
							$img_data = $this->upload->data();
							$channel_data['image'][$i] = $img_data['file_name'];
						} else {
							$channel_data['image'][$i] = null;
						}
					}
				}

				$channel_id_exists['id'] = $this->db->select('channel_detail_id')->from('channel_partner_details')->where('channel_partner_id', $channel_partner_id)->get()->result_array();

				foreach ($channel_id_exists['id'] as $value) {
					$channel_id_present[] = $value['channel_detail_id'];
				}

				$drop_items_code = array_diff($channel_id_present, $channel_data['channel_detail_id']);

				$this->db->where('channel_partner_id', $channel_partner_id);
				$result = $this->db->update('channel_partner', $data);
				$data_row = $this->db->affected_rows();

				for ($i = 0; $i < count($channel_data["project_name"]); $i++) {
					$item_exists = $this->db->select('channel_detail_id')->from('channel_partner_details')->where('channel_detail_id', $channel_data['channel_detail_id'][$i])->get()->row();
					if (!empty($item_exists)) {
						$update_batch[$i] = array('channel_detail_id' => $item_exists->channel_detail_id, 'channel_partner_id' => $channel_partner_id, 'project_name' => $channel_data['project_name'][$i], 'mobile' => $channel_data['mobile'][$i], 'address' => $channel_data['address'][$i], 'testimonial' => $channel_data['testimonial'][$i]);
						if (!empty($channel_data['image'][$i])) {
							$update_batch[$i]['image'] = $channel_data['image'][$i];
						}
					} else {
						$insert_batch[] = array('project_name' => $channel_data['project_name'][$i], 'mobile' => $channel_data['mobile'][$i], 'address' => $channel_data['address'][$i], 'testimonial' => $channel_data['testimonial'][$i], 'channel_partner_id' => $channel_partner_id);
					}
				}

				$insert_row = 0;
				if (!empty($insert_batch)) {
					$this->db->insert_batch('channel_partner_details', $insert_batch);
					$insert_row = $this->db->affected_rows();
				}

				$update_row = 0;
				if (!empty($update_batch)) {
					$this->db->update_batch('channel_partner_details', $update_batch, 'channel_detail_id');
					$update_row = $this->db->affected_rows();
				}

				$delete_row = 0;
				if (!empty($drop_items_code)) {
					$this->db->where_in('channel_detail_id', $drop_items_code)->where('channel_partner_id', $channel_partner_id)->delete('channel_partner_details');
					$delete_row = $this->db->affected_rows();
				}

				if ($data_row > 0 || $insert_row > 0 || $update_row > 0 || $delete_row > 0) {
					// return true;
					$this->session->set_flashdata('alert', 'edit');
					redirect(base_url() . 'admin/channel_partner', 'refresh');
				} else {
					// return false;
					echo "Data Failed to Edit!";
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$channel_partner_id = $para2;
				$this->db->where('channel_partner_id', $para2);
				$result1 = $this->db->delete('channel_partner');

				$this->db->where('channel_partner_id', $para2);
				$result2 = $this->db->delete('channel_partner_details');
				recache();
				if ($result1 == true && $result2 == true) {
					$this->session->set_flashdata('alert', 'delete');
					redirect(base_url() . 'admin/channel_partner', 'refresh');
				} else {
					echo "Data Failed to Delete!";
				}
				exit;
			}
			$page_data['page_name'] = "channel_partner";
			$this->load->view('back/index', $page_data);
		}
	}


	private function set_ch_partner_image_upload_options()
	{
		//upload an image options
		$config = array();
		$config['upload_path'] = 'uploads/channel_partner_image';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		// $config['max_size']      = '50000';
		$config['overwrite']     = FALSE;
		return $config;
	}
	function blog($para1 = "", $para2 = "", $para3 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "blog/blog.php";
				$page_data['folder'] = "blog";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "blog/blog.php";
				$page_data['blog_list'] = $this->db->get("blogs")->result();
				$page_data['page_name'] = "blog_page";
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['danger_alert'] = translate("you_have_successfully_deleted_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "failed_image") {
					$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
			} elseif ($para1 == "add_blog") {
				$page_data['top'] = "blog/blog.php";
				$page_data['folder'] = "blog";
				$page_data['file'] = "add_blog.php";
				$page_data['bottom'] = "blog/blog.php";
			} elseif ($para1 == "do_add") {
				// echo "<pre>";
				// print_r($_POST);
				// print_r($para1);
				// print_r($_FILES);
				// exit();
				// $date = $this->input->post('blog_post_time');
				// $time = date("H:i:s");

				$data['title'] = $this->input->post('title');
				$data['description'] = $this->input->post('description');
				// $data['blog_post_time'] = date('Y-m-d H:i:s', strtotime($date . $time));
				// $data['blog_post_time'] = strtotime($this->input->post('blog_post_time'));
				$data['post_time'] = date("Y-m-d H:i:s");
				$data['approval_status'] = 1;

				// echo "<pre>";
				// print_r($data);
				// exit();

				$this->db->insert('blogs', $data);
				$blog_id = $this->db->insert_id();

				if (!demo()) {
					if ($_FILES['image']['name'] !== '') {
						$id = $blog_id;
						$path = $_FILES['image']['name'];
						$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);


						if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
							$this->Crud_model->file_up("image", "blog", $id, '', '', $ext);
							$images[] = array('image' => 'blog_' . $id . $ext, 'thumb' => 'blog_' . $id . '_thumb' . $ext);
							$data['image'] = json_encode($images);
						} else {
							$this->session->set_flashdata('alert', 'failed_image');
							redirect(base_url() . 'admin/blog', 'refresh');
						}
					}
				}

				$this->db->where('blog_id', $blog_id);
				$result = $this->db->update('blogs', $data);
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'add');
					redirect(base_url() . 'admin/blog', 'refresh');
				} else {
					echo "Data Failed to Add!";
				}
				exit;
			} elseif ($para1 == "edit_blog") {
				$page_data['top'] = "blog/blog.php";
				$page_data['folder'] = "blog";
				$page_data['file'] = "edit_blog.php";
				$page_data['bottom'] = "blog/blog.php";
				$page_data['get_blog'] = $this->db->get_where("blogs", array("blog_id" => $para2))->result();
			} elseif ($para1 == "update") {
				// echo "<pre>";
				// print_r($_POST);
				// exit();
				$blog_id = $this->input->post('blog_id');
				$data['title'] = $this->input->post('title');
				$data['description'] = $this->input->post('description');

				// $date = $this->input->post('blog_post_time');
				// $time = date("H:i:s");
				// $data['blog_post_time'] = date('Y-m-d H:i:s', strtotime($date . $time));
				// $data['blog_post_time'] = strtotime($this->input->post('blog_post_time'));
				$data['post_time'] = date("Y-m-d H:i:s");

				if (!demo()) {
					if ($_FILES['image']['name'] !== '') {
						$id = $blog_id;
						$path = $_FILES['image']['name'];
						$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);


						if ($ext == ".jpg" || $ext == ".JPG" || $ext == ".jpeg" || $ext == ".JPEG" || $ext == ".png" || $ext == ".PNG") {
							$this->Crud_model->file_up("image", "blog", $id, '', '', $ext);
							$images[] = array('image' => 'blog_' . $id . $ext, 'thumb' => 'blog_' . $id . '_thumb' . $ext);
							$data['image'] = json_encode($images);
						} else {
							$this->session->set_flashdata('alert', 'failed_image');
							redirect(base_url() . 'admin/blog', 'refresh');
						}
					}
				}

				$this->db->where('blog_id', $blog_id);
				$result = $this->db->update('blogs', $data);
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'edit');
					redirect(base_url() . 'admin/blog', 'refresh');
				} else {
					echo "Data Failed to Edit!";
				}
				// exit;
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$blog_id = $para2;
				$this->db->where('blog_id', $para2);
				$result = $this->db->delete('blogs');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
					redirect(base_url() . 'admin/blog', 'refresh');
				} else {
					echo "Data Failed to Delete!";
				}
				exit;
			}
			$page_data['page_name'] = "blog_page";
			$this->load->view('back/index', $page_data);
		}

	
	}



	// function astro_tips($para1="",$para2="",$para3="")
	// 	{
	// 		if ($this->admin_permission() == FALSE) {
	//         	redirect(base_url().'admin/login', 'refresh');
	// 		}
	// 		else{
	// 			$page_data['title'] = "Astro Tips || ".$this->system_title;
	// 			if ($para1=="") {				
	// 				$page_data['top'] = "astro_tips/index.php";
	// 				$page_data['folder'] = "astro_tips";
	// 				$page_data['file'] = "index.php";
	// 				$page_data['bottom'] = "astro_tips/index.php";
	// 				$page_data['astro_tips_list'] = $this->db->select('*')->from("astro_tips")->order_by('post_time','desc')->get()->result();
	// 				$page_data['page_name'] = "astro_tips";


	// 				if ($this->session->flashdata('alert') == "edit") {
	// 					$page_data['success_alert'] = translate("you_have_successfully_edited_the_channel_partner!");
	// 				}
	// 				elseif ($this->session->flashdata('alert') == "add") {
	// 					$page_data['success_alert'] = translate("you_have_successfully_added_the_channel_partner!");
	// 				}
	// 				elseif ($this->session->flashdata('alert') == "delete") {
	// 					$page_data['danger_alert'] = translate("you_have_successfully_deleted_the_channel_partner!");
	// 				}
	// 				elseif ($this->session->flashdata('alert') == "failed_image") {
	// 					$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
	// 				}
	// 				elseif ($this->session->flashdata('alert') == "demo_msg") {
	// 					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
	// 				}
	// 			}
	// 			elseif ($para1=="add_astro_tips") {
	// 				$page_data['top'] = "astro_tips/astro_tips.php";
	// 				$page_data['folder'] = "astro_tips";
	// 				$page_data['file'] = "add_astro_tips.php";
	// 				$page_data['bottom'] = "astro_tips/astro_tips.php";
	// 			}
	// 			elseif ($para1=="do_add") {

	// 				// echo "<pre>";
	// 				// print_r($_POST);
	// 				// exit();
	// 				$data['astro_tips'] = $this->input->post('astro_tips');
	// 				$data['approval_status'] = 1;
	// 				$data['post_time'] = date("Y-m-d H:i:s");

	// 				$result = $this->db->insert('astro_tips', $data); 

	// 				recache();
	// 				if ($result) {
	// 					$this->session->set_flashdata('alert', 'add');
	// 					redirect(base_url().'admin/astro_tips', 'refresh');
	// 				}
	// 				else {
	// 					echo "Data Failed to Add!";
	// 				}
	//       			exit;		
	// 			}
	// 			elseif ($para1=="edit_astro_tips") {
	// 				// echo "<pre>";
	// 				// print_r($para2);
	// 				// exit();

	// 				$page_data['top'] = "astro_tips/astro_tips.php";
	// 				$page_data['folder'] = "astro_tips";
	// 				$page_data['file'] = "edit_astro_tips.php";
	// 				$page_data['bottom'] = "astro_tips/astro_tips.php";
	// 				$page_data['get_astro_tips'] = $this->db->get_where("astro_tips", array("astro_tips_id" => $para2))->row();

	// 				// echo "<pre>";
	// 				// print_r($page_data['get_astro_tips']);
	// 				// exit();

	// 			}
	// 			elseif ($para1=="update") {
	// 				// echo "<pre>";
	// 				// print_r($_POST);
	// 				// exit();
	// 				$astro_tips_id = $this->input->post('astro_tips_id');
	// 				$data['astro_tips'] = $this->input->post('astro_tips');
	// 				$data['post_time'] = date("Y-m-d H:i:s");


	// 				$this->db->where('astro_tips_id', $astro_tips_id);
	// 				$result = $this->db->update('astro_tips', $data);
	// 				recache();
	// 				if ($result) {
	// 					$this->session->set_flashdata('alert', 'edit');
	// 					redirect(base_url().'admin/astro_tips', 'refresh');
	// 				}
	// 				else {
	// 					echo "Data Failed to Edit!";
	// 				}
	//         		// exit;
	// 			}
	// 			elseif ($para1=="delete") {
	// 				if(demo()){
	// 					$this->session->set_flashdata('alert', 'demo_msg');
	// 					return false;
	// 				}
	// 				$astro_tips_id = $para2;
	// 				$this->db->where('astro_tips_id', $para2);
	// 	            $result = $this->db->delete('astro_tips');
	// 				recache();
	// 				if ($result) {
	// 					$this->session->set_flashdata('alert', 'delete');
	// 					redirect(base_url().'admin/astro_tips', 'refresh');
	// 				}
	// 				else {
	// 					echo "Data Failed to Delete!";
	// 				}
	//         		exit;
	// 			}
	// 			$page_data['page_name'] = "astro_tips";
	// 			$this->load->view('back/index', $page_data);
	// 		}
	// 	}

	function astro_tips($para1 = "", $para2 = "", $para3 = "")
	{
		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Astro Tips || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "astro_tips/index.php";
				$page_data['folder'] = "astro_tips";
				$page_data['file'] = "index.php";
				$page_data['bottom'] = "astro_tips/index.php";
				$page_data['astro_tips_list'] = $this->db->select('*')->from("astro_tips")->order_by('post_time', 'desc')->get()->result();
				$page_data['page_name'] = "astro_tips";

				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['danger_alert'] = translate("you_have_successfully_deleted_the_channel_partner!");
				} elseif ($this->session->flashdata('alert') == "failed_image") {
					$page_data['danger_alert'] = translate("failed_to_upload_your_image._make_sure_the_image_is_JPG,_JPEG_or_PNG!");
				} elseif ($this->session->flashdata('alert') == "demo_msg") {
					$page_data['danger_alert'] = translate("this_operation_is_disabled_in_demo!");
				}
			} elseif ($para1 == "add_astro_tips") {
				$page_data['top'] = "astro_tips/astro_tips.php";
				$page_data['folder'] = "astro_tips";
				$page_data['file'] = "add_astro_tips.php";
				$page_data['bottom'] = "astro_tips/astro_tips.php";
			} elseif ($para1 == "do_add") {

				// echo "<pre>";
				// print_r($_POST);
				// exit();
				$data['main_title'] = $this->input->post('main_title');
				$data['sub_title'] = $this->input->post('sub_title');
				$data['astro_tips'] = $this->input->post('astro_tips');
				$data['key_points'] = $this->input->post('key_points');
				$data['approval_status'] = 1;
				$data['post_time'] = date("Y-m-d H:i:s");
				// echo "<pre>";
				// print_r($data);
				// exit();

				$result = $this->db->insert('astro_tips', $data);

				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'add');
					redirect(base_url() . 'admin/astro_tips', 'refresh');
				} else {
					echo "Data Failed to Add!";
				}
				exit;
			} elseif ($para1 == "edit_astro_tips") {
				// echo "<pre>";
				// print_r($para2);
				// exit();

				$page_data['top'] = "astro_tips/astro_tips.php";
				$page_data['folder'] = "astro_tips";
				$page_data['file'] = "edit_astro_tips.php";
				$page_data['bottom'] = "astro_tips/astro_tips.php";
				$page_data['get_astro_tips'] = $this->db->get_where("astro_tips", array("astro_tips_id" => $para2))->row();
			} elseif ($para1 == "update") {
				// echo "<pre>";
				// print_r($_POST);
				// exit();
				$astro_tips_id = $this->input->post('astro_tips_id');
				$data['main_title'] = $this->input->post('main_title');
				$data['sub_title'] = $this->input->post('sub_title');
				$data['astro_tips'] = $this->input->post('astro_tips');
				$data['key_points'] = $this->input->post('key_points');
				$data['post_time'] = date("Y-m-d H:i:s");

				$this->db->where('astro_tips_id', $astro_tips_id);
				$result = $this->db->update('astro_tips', $data);
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'edit');
					redirect(base_url() . 'admin/astro_tips', 'refresh');
				} else {
					echo "Data Failed to Edit!";
				}
			} elseif ($para1 == "delete") {
				if (demo()) {
					$this->session->set_flashdata('alert', 'demo_msg');
					return false;
				}
				$astro_tips_id = $para2;
				$this->db->where('astro_tips_id', $para2);
				$result = $this->db->delete('astro_tips');
				recache();
				if ($result) {
					$this->session->set_flashdata('alert', 'delete');
					redirect(base_url() . 'admin/astro_tips', 'refresh');
				} else {
					echo "Data Failed to Delete!";
				}
				exit;
			}
			$page_data['page_name'] = "astro_tips";
			$this->load->view('back/index', $page_data);
		}
	}

	public function updateStatus($id, $status)
	{

		// echo "<pre>";
		// print_r($_POST);
		// exit();
		$this->db->where('plan_id', $id)->update('plan', array('status' => $status));
		if ($this->db->affected_rows()) $this->session->set_flashdata('message', array('message' => 'Package Updated successfully', 'class' => 'success'));
		else $this->session->set_flashdata('message', array('message' => 'Failed Try Again', 'class' => 'danger'));
		redirect(base_url() . 'admin/packages');
	}


	// ---------------- marriage_application_details start ---------------

	public function marriage_application_details($para1 = "", $para2 = "", $para3 = "")
	{
		if ($para1 == "") {
			$page_data['top'] = "marriage_application_details/index.php";
			$page_data['folder'] = "marriage_application_details";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			$page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
		} elseif ($para1 == "view_marriage_details") {
			$page_data['top'] = "marriage_application_details/index.php";
			$page_data['folder'] = "marriage_application_details";
			$page_data['file'] = "view_marriage_details.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			$page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$page_data['application_id'] = $para2;
			$this->load->view('back/index', $page_data);
		} else if ($para1 == "delete_marriage_details") {

			$data['delete_status'] = 1;
			$this->db->where('application_id', $para2);
			$this->db->update('marriage_application_form', $data);
			$result = $this->db->affected_rows();
			if ($result == true) {
				$this->session->set_flashdata('success', 'application deleted successfully');
				redirect('admin/marriage_application_details');
			} else {
				$this->session->set_flashdata('failed', 'Failed');
				redirect('admin/marriage_application_details');
			}
		}
	}
	// ------------------ marriage_application_details end --------------

	// ------------------ Gurugi Photos Module Start(18-9-21) start --------------
	private function set_upload_guruji_photos()
	{

		$config = array();
		$config['upload_path'] = 'uploads/guruji_photos';
		$config['allowed_types'] = 'jpg|png|jpeg|pdf';

		$config['overwrite']     = FALSE;
		return $config;
	}

	public function guruji_photos($para1 = "", $para2 = "", $para3 = "")
	{
		if ($para1 == "") {
			$page_data['top'] = "guruji_photos/index.php";
			$page_data['folder'] = "guruji_photos";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "guruji_photos/index.php";
			$page_data['page_name'] = "guruji_photos";

			$this->load->view('back/index', $page_data);
		} else if ($para1 == "add_guruji_photos") {
			$page_data['top'] = "guruji_photos/index.php";
			$page_data['folder'] = "guruji_photos";
			$page_data['file'] = "add_guruji_photos.php";
			$page_data['bottom'] = "guruji_photos/index.php";
			$page_data['page_name'] = "guruji_photos";

			$this->load->view('back/index', $page_data);
		} else if ($para1 == "edit_guruji_data") {
			$page_data['top'] = "guruji_photos/index.php";
			$page_data['folder'] = "guruji_photos";
			$page_data['file'] = "edit_guruji_data.php";
			$page_data['bottom'] = "guruji_photos/index.php";
			$page_data['guruji_photo_id'] = $para2;
			$page_data['page_name'] = "guruji_photos";

			$this->load->view('back/index', $page_data);
		} else if ($para1 == "delete_guruji_data") {
			$data['delete_status'] = 1;
			$this->db->where('guruji_photo_id', $para2);
			$this->db->update('guruji_photos', $data);
			$result = $this->db->affected_rows();

			if ($result == true) {
				$this->session->set_flashdata('success', 'deleted successfully');
				redirect('admin/guruji_photos');
			} else {
				$this->session->set_flashdata('failed', 'Failed');
				redirect('admin/guruji_photos');
			}
		}
	}

	public function add_guruji_photos_details()
	{
		$data['guruji_name'] = $_POST['guruji_name'];
		$config = $this->set_upload_guruji_photos();
		$this->load->library('upload');
		$this->upload->initialize($config);

		if (!empty($_FILES['guruji_photo']['name'])) {
			if (!$this->upload->do_upload('guruji_photo')) {
				$error = $this->upload->display_errors();
				redirect('admin/guruji_photos');
			} else {
				$data['guruji_photo'] = $this->upload->data('file_name');
			}
		}

		$this->db->insert('guruji_photos', $data);
		$result = $this->db->affected_rows();
		if ($result == true) {
			$this->session->set_flashdata('success', 'added successfully');
			redirect('admin/guruji_photos');
		} else {
			$this->session->set_flashdata('failed', 'Failed');
			redirect('admin/guruji_photos');
		}
	}

	public function update_guruji_details()
	{

		$guruji_photo_id = $_POST['guruji_photo_id'];
		$data['guruji_name'] = $_POST['guruji_name'];
		$config = $this->set_upload_guruji_photos();
		$this->load->library('upload');
		$this->upload->initialize($config);

		if (!empty($_FILES['guruji_photo']['name'])) {
			if (!$this->upload->do_upload('guruji_photo')) {
				$error = $this->upload->display_errors();
				redirect('admin/guruji_photos');
			} else {
				$data['guruji_photo'] = $this->upload->data('file_name');
			}
		}
		$this->db->where('guruji_photo_id', $guruji_photo_id);
		$this->db->update('guruji_photos', $data);
		$result = $this->db->affected_rows();
		if ($result == true) {
			$this->session->set_flashdata('success', 'updated successfully');
			redirect('admin/guruji_photos');
		} else {
			$this->session->set_flashdata('failed', 'Failed');
			redirect('admin/guruji_photos');
		}
	}

	// ------------------ Gurugi Photos Module end(18-9-21) --------------


	// ------------------ Offers Module Start(18-9-21) --------------
	function offers($para1 = "", $para2 = "", $para3 = "", $para4 = "")
	{

		if ($this->admin_permission() == FALSE) {
			redirect(base_url() . 'admin/login', 'refresh');
		} else {
			$page_data['title'] = "Admin || " . $this->system_title;
			if ($para1 == "") {
				$page_data['top'] = "offers/index.php";
				$page_data['folder'] = "offers";
				$page_data['file'] = "offers.php";
				$page_data['bottom'] = "offers/index.php";
				$page_data['page_name'] = "offers";
				$page_data['all_offers_list'] = $this->db->get("offers")->result();


				if ($this->session->flashdata('alert') == "add") {
					$page_data['success_alert'] = translate("you_have_successfully_added_the_offers!");
				}
				if ($this->session->flashdata('alert') == "edit") {
					$page_data['success_alert'] = translate("you_have_successfully_edited_the_offers!");
				} elseif ($this->session->flashdata('alert') == "delete") {
					$page_data['danger_alert'] = translate("you_have_successfully_deleted_the_offers!");
				}

				$this->load->view('back/index', $page_data);
			} elseif ($para1 == "add_offers") {
				$this->load->view('back/offers/add_offers');
			} elseif ($para1 == "edit_offers") {
				$page_data['get_offers'] = $this->db->get_where("offers", array("offer_id" => $para2))->result();
				$this->load->view('back/offers/edit_offers', $page_data);
			} elseif ($para1 == "do_add") {

				$data['futured_offers'] = $this->input->post('futured_offers');
				$data['current_offers'] = $this->input->post('current_offers');

				if (!empty($_POST['future_offer_status'])) {
					$data['future_offer_status'] = 1;
				} else {
					$data['future_offer_status'] = 0;
				}

				if (!empty($_POST['current_offer_status'])) {
					$data['current_offer_status'] = 1;
				} else {
					$data['current_offer_status'] = 0;
				}

				$this->db->insert('offers', $data);
				$this->session->set_flashdata('alert', 'add');

				redirect(base_url() . 'admin/offers', 'refresh');
			} elseif ($para1 == "update") {
				$data['futured_offers'] = $this->input->post('futured_offers');
				$data['current_offers'] = $this->input->post('current_offers');

				if (!empty($_POST['future_offer_status'])) {
					$data['future_offer_status'] = 1;
				} else {
					$data['future_offer_status'] = 0;
				}

				if (!empty($_POST['current_offer_status'])) {
					$data['current_offer_status'] = 1;
				} else {
					$data['current_offer_status'] = 0;
				}

				$this->db->where('offer_id', $para2);
				$this->db->update('offers', $data);

				$this->session->set_flashdata('alert', 'edit');
				redirect(base_url() . 'admin/offers', 'refresh');
			} elseif ($para1 == "delete") {
				$this->db->where('offer_id', $para2);
				$this->db->delete('offers');
				$this->session->set_flashdata('alert', 'delete');
			}
		}
	}

	public function updateofferStatus($id, $status)
	{
		$this->db->where('offer_id', $id)->update('offers', array('status' => $status));
		if ($this->db->affected_rows()) $this->session->set_flashdata('message', array('message' => 'Offer Updated successfully', 'class' => 'success'));
		else $this->session->set_flashdata('message', array('message' => 'Failed Try Again', 'class' => 'danger'));
		redirect(base_url() . 'admin/offers');
	}
	// ------------------ Offers Module End(18-9-21) --------------


	// export CSV file start(30-9-21)
	public function groomFreeMember()
	{
		// file name 
		$filename = 'groom_free_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$members_data    = $this->Crud_model->get_groom_free_members();
		// $active_members_data    = $this->Crud_model->get_active_groom_free_members();
		//echo "<pre>";
		//print_r($members_data);
		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "mother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					echo $n['payment_type'];
					print_r("\n");
				}
			}

			//$c = array_merge($a,$b);
			//fputcsv($file, $c);

		}
	}


	public function activegroomFreeMember()
	{
		// file name 
		$filename = 'active_groom_free_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		// $members_data    = $this->Crud_model->get_groom_free_members();
		$active_members_data    = $this->Crud_model->get_active_groom_free_members();
		//echo "<pre>";
		//print_r($members_data);
		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "mother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($active_members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					echo $n['payment_type'];
					print_r("\n");
				}
			}

			//$c = array_merge($a,$b);
			//fputcsv($file, $c);

		}
	}


	public function bridFreeMember()
	{
		// file name 
		$filename = 'bride_free_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$members_data    = $this->Crud_model->get_bride_free_members();
		//echo "<pre>";
		//print_r($members_data);
		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "mother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				if (!empty($value['introduction'])) {
					$string = $value['introduction'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				} else if (empty($value['introduction'])) {
					echo "--";
					print_r("\t");
				}
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $f['immigration_status'];
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {

					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					echo $n['payment_type'];
					print_r("\n");
				}
			}

			//$c = array_merge($a,$b);
			//fputcsv($file, $c);

		}
	}


	public function activebridFreeMember()
	{
		// file name 
		$filename = 'active_bride_free_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		// $members_data    = $this->Crud_model->get_bride_free_members();
		$active_members_data    = $this->Crud_model->get_active_bride_free_members();
		//echo "<pre>";
		//print_r($members_data);
		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "mother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($active_members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				if (!empty($value['introduction'])) {
					$string = $value['introduction'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				} else if (empty($value['introduction'])) {
					echo "--";
					print_r("\t");
				}
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $f['immigration_status'];
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {

					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					echo $n['payment_type'];
					print_r("\n");
				}
			}

			//$c = array_merge($a,$b);
			//fputcsv($file, $c);

		}
	}


	public function exportFreeMemberCSV()
	{
		// file name 
		$filename = 'free_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$members_data    = $this->Crud_model->get_free_members_for_csv();
		// echo "<pre>";
		// print_r($members_data);




		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "brother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		// print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					echo $n['payment_type'];
					print_r("\n");
				}
			}

			//$c = array_merge($a,$b);
			//fputcsv($file, $c);

		}
		fclose($file);
		exit;
	}


	public function exportactiveFreeMemberCSV()
	{
		// file name 
		$filename = 'active_free_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$active_members_data    = $this->Crud_model->get_active_free_members_for_csv();
		// echo "<pre>";
		// print_r($members_data);




		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "brother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		// print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($active_members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					echo $n['payment_type'];
					print_r("\n");
				}
			}

			//$c = array_merge($a,$b);
			//fputcsv($file, $c);

		}
		fclose($file);
		exit;
	}


	public function groomPremiumMember()
	{
		// file name 
		$filename = 'groom_premium_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$members_data    = $this->Crud_model->getPremiumGroomDetails();
		//echo "<pre>";
		// print_r($members_data);
		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "brother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					$string = $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					$string = $k['brother_sister'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = str_replace(' ', '', $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = str_replace(' ', '', $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					$string =  $n['current_package'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					$string = $n['payment_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\n");
				}
			}
		}
		fclose($file);
		exit;
	}

	public function activegroomPremiumMember()
	{
		// file name 
		$filename = 'active_groom_premium_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$active_members_data    = $this->Crud_model->getactivePremiumGroomDetails();
		//echo "<pre>";
		// print_r($members_data);
		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "brother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($active_members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					$string = $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					$string = $k['brother_sister'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = str_replace(' ', '', $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = str_replace(' ', '', $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					//$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					$string =  $n['current_package'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					$string = $n['payment_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\n");
				}
			}
		}
		fclose($file);
		exit;
	}


	public function bridPremiumMember()
	{
		// file name 
		$filename = 'bride_premium_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$members_data    = $this->Crud_model->getPremiumBrideDetails();
		//echo "<pre>";
		//print_r($members_data);




		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "brother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					$string = $n['payment_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\n");
				}
			}

			//$c = array_merge($a,$b);
			//fputcsv($file, $c);

		}
		fclose($file);
		exit;
	}

	public function activebridPremiumMember()
	{
		// file name 
		$filename = 'active_bride_premium_members_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$active_members_data    = $this->Crud_model->getactivePremiumBrideDetails();
		//echo "<pre>";
		//print_r($members_data);




		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "brother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($active_members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					$string = $n['payment_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\n");
				}
			}

			//$c = array_merge($a,$b);
			//fputcsv($file, $c);

		}
		fclose($file);
		exit;
	}



	public function exportPremiumMemberCSV()
	{
		// file name 
		$filename = 'premiumMembers_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$members_data    = $this->Crud_model->getPremiumUserDetails();
		//echo "<pre>";
		//print_r($members_data);




		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "brother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					echo $n['payment_type'];
					print_r("\n");
				}
			}
			//$c = array_merge($a,$b);
			//fputcsv($file, $c);
		}


		fclose($file);
		exit;
	}

	public function exportactivePremiumMemberCSV()
	{
		// file name 
		$filename = 'active_premiumMembers_' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// get data 
		$active_members_data    = $this->Crud_model->getactivePremiumUserDetails();
		//echo "<pre>";
		//print_r($members_data);


		// file creation 
		$file = fopen('php://output', 'w');

		$header_member = array("MemberId", "Member ProfileId", "Status", "First Name", "Last Name", "gender", "Email", "Mobile", "DOB", "Height", "Introduction", "marital status", "on behalf", "area", "no of children", "education", "occupation", "annual income", "mother tongue", "language", "speak", "read", "hobby", "interest", "music", "books", "movie", "tv show", "sports show", "fitness activity", "food", "dress style", "affection", "humor", "political view", "religious service", "birth country", "residency country", "citizenship country", "grow up country", "immigration status", "religion", "caste", "sub caste", "ethnicity", "personal value", "family value", "dosha", "community value", "family status", "diet", "drink", "smoke", "living with", "raashi", "nakshathra", "birth time", "birth city", "country", "state", "city", "postal code", "father", "brother", "brother/sister", "home district", "family residence", "fathers occupation", "special circumstances", "general_requirement", "partner_age", "partner_height", "partner_weight", "partner_marital_status", "with_children_acceptables", "partner_country_of_residence", "partner_religion", "partner_caste", "partner_complexion", "partner_education", "partner_profession", "partner_drinking_habits", "partner_smoking_habits", "partner_diet", "partner_body_type", "partner_personal_value", "manglik", "partner_any_disability", "partner_mother_tongue,", "partner_family_value", "prefered_country", "prefered_state", "prefered_status", "current package", "package price", "package type");
		//print_r($header_member);
		fputcsv($file, $header_member);

		foreach ($active_members_data as $value) {
			//print_r($value['member_id']);
			$basic_info = json_decode($value['basic_info'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$present_address = json_decode($value['present_address'], true);
			$education_and_career = json_decode($value['education_and_career'], true);
			$physical_attributes = json_decode($value['physical_attributes'], true);
			$language = json_decode($value['language'], true);
			$hobbies_and_interest = json_decode($value['hobbies_and_interest'], true);
			$personal_attitude_and_behavior = json_decode($value['personal_attitude_and_behavior'], true);
			$residency_information = json_decode($value['residency_information'], true);
			$spiritual_and_social_background = json_decode($value['spiritual_and_social_background'], true);
			$life_style = json_decode($value['life_style'], true);
			$astronomic_information = json_decode($value['astronomic_information'], true);
			$permanent_address = json_decode($value['permanent_address'], true);
			$family_info = json_decode($value['family_info'], true);
			$additional_personal_details = json_decode($value['additional_personal_details'], true);
			$partner_expectation = json_decode($value['partner_expectation'], true);
			$package_info = json_decode($value['package_info'], true);
			//print_r($partner_expectation);

			if ($header_member[0]) {
				echo $value['member_id'];
				print_r("\t");
			}
			if ($header_member[1]) {
				echo $value['member_profile_id'];
				print_r("\t");
			}
			if ($header_member[2]) {
				echo $value['status'];
				print_r("\t");
			}
			if ($header_member[3]) {
				$string = $value['first_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[4]) {
				$string = $value['last_name'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}
			if ($header_member[5]) {
				echo $this->Crud_model->get_type_name_by_id('gender', $value['gender']);
				print_r("\t");
			}
			if ($header_member[6]) {
				echo $value['email'];
				print_r("\t");
			}
			if ($header_member[7]) {
				echo $value['mobile'];
				print_r("\t");
			}
			if ($header_member[8]) {
				echo date('d/m/Y', $value['date_of_birth']);
				print_r("\t");
			}
			if ($header_member[9]) {
				echo $value['height'];
				print_r("\t");
			}
			if ($header_member[10]) {
				$string = $value['introduction'];
				$str = preg_replace("![^a-z0-9]+!i", "-", $string);
				echo $str;
				print_r("\t");
			}

			foreach ($basic_info as $basic) {
				$a = $basic;
				if ($header_member[11]) {
					if ($a['marital_status'] == 1) {
						print_r("unmarried");
						print_r("\t");
					} else if ($a['marital_status'] == 3) {
						print_r("divorced");
						print_r("\t");
					} else if ($a['marital_status'] == 5) {
						print_r("widowed");
						print_r("\t");
					}
				}
				if ($header_member[12]) {
					if ($a['on_behalf'] == 1) {
						print_r("self");
						print_r("\t");
					} else if ($a['on_behalf'] == 2) {
						print_r("daughter/son");
						print_r("\t");
					} else if ($a['on_behalf'] == 3) {
						print_r("sister");
						print_r("\t");
					} else if ($a['on_behalf'] == 4) {
						print_r("brother");
						print_r("\t");
					} else if ($a['on_behalf'] == 5) {
						print_r("friend");
						print_r("\t");
					} else if ($a['on_behalf'] == 6) {
						print_r("cousin");
						print_r("\t");
					}
				}

				if ($header_member[13]) {
					$string = $a['area'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
				}

				if ($header_member[14]) {
					echo $a['number_of_children'];
					print_r("\t");
				}
			}

			foreach ($education_and_career as $education) {
				$b = $education;
				if ($header_member[15]) {
					$string = $b['highest_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['highest_education'];print_r("\t");
				}

				if ($header_member[16]) {
					$string = $b['occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['occupation'];print_r("\t"); 
				}

				if ($header_member[17]) {
					$string = $b['annual_income'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $b['annual_income'];print_r("\t");
				}
			}

			foreach ($language as $lan) {
				$c = $lan;
				if ($header_member[18]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['mother_tongue']);
					print_r("\t");
				}
				if ($header_member[19]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['language']);
					print_r("\t");
				}
				if ($header_member[20]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['speak']);
					print_r("\t");
				}
				if ($header_member[21]) {
					echo $this->Crud_model->get_type_name_by_id('language', $c['read']);
					print_r("\t");
				}
			}

			foreach ($hobbies_and_interest as $hobbies) {
				$d = $hobbies;
				if ($header_member[22]) {
					$string = $d['hobby'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['hobby'];print_r("\t");
				}
				if ($header_member[23]) {
					$string = $d['interest'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['interest'];print_r("\t");
				}
				if ($header_member[24]) {
					$string = $d['music'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['music'];print_r("\t");
				}
				if ($header_member[25]) {
					$string = $d['books'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['books'];print_r("\t");
				}
				if ($header_member[26]) {
					$string = $d['movie'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['movie'];print_r("\t");
				}
				if ($header_member[27]) {
					$string = $d['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['tv_show'];print_r("\t");
				}
				if ($header_member[28]) {
					$string = $d['sports_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['sports_show'];print_r("\t");
				}
				if ($header_member[29]) {
					$string = $d['fitness_activity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['fitness_activity'];print_r("\t");
				}
				if ($header_member[30]) {
					$string = $d['cuisine'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['cuisine'];print_r("\t");
				}
				if ($header_member[31]) {
					$string = $d['dress_style'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $d['dress_style'];print_r("\t");
				}
			}

			foreach ($personal_attitude_and_behavior as $personal) {
				$e = $personal;
				if ($header_member[32]) {
					$string = $e['affection'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['affection'];print_r("\t");
				}
				if ($header_member[33]) {
					$string = $e['tv_show'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['humor'];print_r("\t");
				}
				if ($header_member[34]) {
					$string = $e['political_view'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['political_view'];print_r("\t");
				}
				if ($header_member[35]) {
					$string = $e['religious_service'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $e['religious_service'];print_r("\t");
				}
			}

			foreach ($residency_information as $residency) {
				$f = $residency;
				if ($header_member[36]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['birth_country']);
					print_r("\t");
				}
				if ($header_member[37]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['residency_country']);
					print_r("\t");
				}
				if ($header_member[38]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['citizenship_country']);
					print_r("\t");
				}
				if ($header_member[39]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['grow_up_country']);
					print_r("\t");
				}
				if ($header_member[40]) {
					echo $this->Crud_model->get_type_name_by_id('country', $f['immigration_status']);
					print_r("\t");
				}
			}

			foreach ($spiritual_and_social_background as $spiritual) {
				$g = $spiritual;
				if ($header_member[41]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $g['religion']);
					print_r("\t");
				}
				if ($header_member[42]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $g['caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[43]) {
					echo $this->Crud_model->get_type_name_by_id('sub_caste', $g['sub_caste'], 'sub_caste_name');
					print_r("\t");
				}
				if ($header_member[44]) {
					$string = $g['ethnicity'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['ethnicity'];print_r("\t");
				}
				if ($header_member[45]) {
					$string = $g['personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['personal_value'];print_r("\t");
				}
				if ($header_member[46]) {
					echo $this->Crud_model->get_type_name_by_id('family_value', $g['family_value']);
					print_r("\t");
				}
				if ($header_member[47]) {
					if ($g['u_manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($g['u_manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($g['u_manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($g['u_manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[48]) {
					$string = $g['community_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $g['community_value'];print_r("\t");
				}
				if ($header_member[49]) {
					echo $this->Crud_model->get_type_name_by_id('family_status', $g['family_status']);
					print_r("\t");
				}
			}

			foreach ($life_style as $life) {
				$h = $life;
				if ($header_member[50]) {
					$string = $h['diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['diet'];print_r("\t");
				}
				if ($header_member[51]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['drink']);
					print_r("\t");
				}
				if ($header_member[52]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $h['smoke']);
					print_r("\t");
				}
				if ($header_member[53]) {
					$string = $h['living_with'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $h['living_with'];print_r("\t");
				}
			}

			foreach ($astronomic_information as $astronomic) {
				$i = $astronomic;
				if ($header_member[54]) {
					echo $i['sun_sign'];
					print_r("\t");
				}
				if ($header_member[55]) {
					echo $i['moon_sign'];
					print_r("\t");
				}
				if ($header_member[56]) {
					echo $i['time_of_birth'];
					print_r("\t");
				}
				if ($header_member[57]) {
					echo $i['city_of_birth'];
					print_r("\t");
				}
			}

			foreach ($permanent_address as $permanent) {
				$j = $permanent;
				if ($header_member[58]) {
					echo $this->Crud_model->get_type_name_by_id('country', $j['permanent_country']);
					print_r("\t");
				}
				if ($header_member[59]) {
					echo $this->Crud_model->get_type_name_by_id('state', $j['permanent_state']);
					print_r("\t");
				}
				if ($header_member[60]) {
					echo $this->Crud_model->get_type_name_by_id('city', $j['permanent_city']);
					print_r("\t");
				}
				if ($header_member[61]) {
					echo $j['permanent_postal_code'];
					print_r("\t");
				}
			}

			foreach ($family_info as $family) {
				$k = $family;
				if ($header_member[62]) {
					echo $k['father'];
					print_r("\t");
				}
				if ($header_member[63]) {
					echo $k['mother'];
					print_r("\t");
				}
				if ($header_member[64]) {
					echo $k['brother_sister'];
					print_r("\t");
				}
			}

			foreach ($additional_personal_details as $additional) {
				$l = $additional;
				if ($header_member[65]) {
					$string = $l['home_district'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['home_district'];print_r("\t");
				}
				if ($header_member[66]) {
					$string = $l['family_residence'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['family_residence'];print_r("\t");
				}
				if ($header_member[67]) {
					$string = $l['fathers_occupation'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['fathers_occupation'];print_r("\t");
				}
				if ($header_member[68]) {
					$string = $l['special_circumstances'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $l['special_circumstances'];print_r("\t");
				}
			}

			foreach ($partner_expectation as $partner) {
				$m = $partner;
				if ($header_member[69]) {
					$string = $m['general_requirement'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['general_requirement'];print_r("\t");
				}
				if ($header_member[70]) {
					$string = $m['partner_age'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_age'];print_r("\t");
				}
				if ($header_member[71]) {
					$string = $m['partner_height'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_height'];print_r("\t");
				}
				if ($header_member[72]) {
					$string = $m['partner_weight'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_weight'];print_r("\t");
				}
				if ($header_member[73]) {
					echo $this->Crud_model->get_type_name_by_id('marital_status', $m['partner_marital_status']);
					print_r("\t");
				}
				if ($header_member[74]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['with_children_acceptables']);
					print_r("\t");
				}
				if ($header_member[75]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['partner_country_of_residence']);
					print_r("\t");
				}
				if ($header_member[76]) {
					echo $this->Crud_model->get_type_name_by_id('religion', $m['partner_religion']);
					print_r("\t");
				}
				if ($header_member[77]) {
					echo $this->Crud_model->get_type_name_by_id('caste', $m['partner_caste'], 'caste_name');
					print_r("\t");
				}
				if ($header_member[78]) {
					$string = $m['complexion'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['complexion'];print_r("\t");
				}
				if ($header_member[79]) {
					$string = $m['partner_education'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_education'];print_r("\t");
				}
				if ($header_member[80]) {
					$string = $m['partner_profession'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_profession'];print_r("\t");
				}
				if ($header_member[81]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_drinking_habits']);
					print_r("\t");
				}
				if ($header_member[82]) {
					echo $this->Crud_model->get_type_name_by_id('decision', $m['partner_smoking_habits']);
					print_r("\t");
				}
				if ($header_member[83]) {
					$string = $m['partner_diet'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_diet'];print_r("\t");
				}
				if ($header_member[84]) {
					$string = $m['partner_body_type'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_body_type'];print_r("\t");
				}
				if ($header_member[85]) {
					$string = $m['partner_personal_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_personal_value'];print_r("\t");
				}
				if ($header_member[86]) {
					if ($m['manglik'] == 1) {
						echo "yes";
						print_r("\t");
					} else if ($m['manglik'] == 2) {
						echo "no";
						print_r("\t");
					} else if ($m['manglik'] == 3) {
						echo "I don't know";
						print_r("\t");
					} else if (empty($m['manglik'])) {
						echo "--";
						print_r("\t");
					}
				}
				if ($header_member[87]) {
					$string = $m['partner_any_disability'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_any_disability'];print_r("\t");
				}
				if ($header_member[88]) {
					echo $this->Crud_model->get_type_name_by_id('language', $m['partner_mother_tongue']);
					print_r("\t");
				}
				if ($header_member[89]) {
					$string = $m['partner_family_value'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['partner_family_value'];print_r("\t");
				}
				if ($header_member[90]) {
					echo $this->Crud_model->get_type_name_by_id('country', $m['prefered_country']);
					print_r("\t");
				}
				if ($header_member[91]) {
					echo $this->Crud_model->get_type_name_by_id('state', $m['prefered_state']);
					print_r("\t");
				}
				if ($header_member[92]) {
					$string = $m['prefered_status'];
					$str = preg_replace("![^a-z0-9]+!i", "-", $string);
					echo $str;
					print_r("\t");
					//echo $m['prefered_status'];print_r("\t");
				}
			}

			foreach ($package_info as $package) {
				$n = $package;
				if ($header_member[93]) {
					echo $n['current_package'];
					print_r("\t");
				}
				if ($header_member[94]) {
					echo $n['package_price'];
					print_r("\t");
				}
				if ($header_member[95]) {
					echo $n['payment_type'];
					print_r("\n");
				}
			}
			//$c = array_merge($a,$b);
			//fputcsv($file, $c);
		}


		fclose($file);
		exit;
	}



	/*-------Refer and Earn Start-----------*/

	public function others($para1 = "", $para2 = "", $para3 = "")
	{
		if ($para1 == "") {
			$page_data['top'] = "marriage_application_details/index.php";
			$page_data['folder'] = "marriage_application_details";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			$page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
		} elseif ($para1 == "view_marriage_details") {
			$page_data['top'] = "marriage_application_details/index.php";
			$page_data['folder'] = "marriage_application_details";
			$page_data['file'] = "view_marriage_details.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			$page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$page_data['application_id'] = $para2;
			$this->load->view('back/index', $page_data);
		} else if ($para1 == "delete_marriage_details") {

			$data['delete_status'] = 1;
			$this->db->where('application_id', $para2);
			$this->db->update('marriage_application_form', $data);
			$result = $this->db->affected_rows();
			if ($result == true) {
				$this->session->set_flashdata('success', 'application deleted successfully');
				redirect('admin/marriage_application_details');
			} else {
				$this->session->set_flashdata('failed', 'Failed');
				redirect('admin/marriage_application_details');
			}
		}
	}

          /*-------Refer and Earn End-----------*/
          
    /*-------new code-----------*/
	/* network start*/
	public function network()
	{
		
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "profile";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "network";
			$this->load->view('back/index', $page_data);
		
			
	}
	
	public function bio()
	{
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "profile";
			$page_data['file'] = "bio.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "network";
			$this->load->view('back/index', $page_data);
	}
	public function connection()
	{
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "profile";
			$page_data['file'] = "connection.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "network";
			$this->load->view('back/index', $page_data);
	}
	public function group(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "profile";
			$page_data['file'] = "group.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "network";
			$this->load->view('back/index', $page_data);
	}
	public function photos(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "profile";
			$page_data['file'] = "photos.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "network";
			$this->load->view('back/index', $page_data);
	}
	public function training(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "profile";
			$page_data['file'] = "training.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "network";
			$this->load->view('back/index', $page_data);
	}
	public function testimonials(){
		$page_data['top'] = "new-Code/index.php";
		$page_data['folder'] = "profile";
		$page_data['file'] = "testimonials.php";
		$page_data['bottom'] = "marriage_application_details/index.php";
		// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
		$page_data['page_name'] = "network";
		$this->load->view('back/index', $page_data);		
	}
	public function network_picture(){
		$page_data['top'] = "new-Code/index.php";
		$page_data['folder'] = "picture";
		$page_data['file'] = "index.php";
		$page_data['bottom'] = "marriage_application_details/index.php";
		// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
		$page_data['page_name'] = "network";
		$this->load->view('back/index', $page_data);
	}
	public function network_group(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "group";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "network";
			$this->load->view('back/index', $page_data);
	}
	public function my_group(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "group";
			$page_data['file'] = "myGroup.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function group_content(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "group";
			$page_data['file'] = "groupContent.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
		}
		public function view_topic(){
		$page_data['top'] = "new-Code/index.php";
		$page_data['folder'] = "group";
		$page_data['file'] = "viewContent.php";
		$page_data['bottom'] = "marriage_application_details/index.php";
		// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
		$page_data['page_name'] = "marriage_application_details";
		$this->load->view('back/index', $page_data);

	}
	public function inbox(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "inbox";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function composeMessage(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "inbox";
			$page_data['file'] = "compose_msg.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function sendMessage(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "inbox";
			$page_data['file'] = "send_msg.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
		}
	public function readMessage(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "inbox";
			$page_data['file'] = "read_msg.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);

	}
	public function replayMessage(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "inbox";
			$page_data['file'] = "reply_msg.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function sentMessage(){
		$page_data['top'] = "new-Code/index.php";
		$page_data['folder'] = "inbox";
		$page_data['file'] = "sent_msg.php";
		$page_data['bottom'] = "marriage_application_details/index.php";
		// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
		$page_data['page_name'] = "marriage_application_details";
		$this->load->view('back/index', $page_data);
}


	public function add_group(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "group";
			$page_data['file'] = "addGroup.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function network_connection(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "connection";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "network";
			$this->load->view('back/index', $page_data);
	}
	public function network_msg(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "connection";
			$page_data['file'] = "msg.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function add_connection(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "connection";
			$page_data['file'] = "add_connection.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function network_testimonials(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "testimonials";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	
	public function operations(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "operation";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "operation";
			$this->load->view('back/index', $page_data);
	}
	public function event(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "event";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function registerEvent(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "event";
			$page_data['file'] = "register_event.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function document(){
		$page_data['top'] = "new-Code/index.php";
		$page_data['folder'] = "document";
		$page_data['file'] = "index.php";
		$page_data['bottom'] = "marriage_application_details/index.php";
		// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
		$page_data['page_name'] = "marriage_application_details";
		$this->load->view('back/index', $page_data);
	}
	public function email_invition(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "email";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function  palms_summary(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "palms";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function email_visitors(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "email";
			$page_data['file'] = "visitor.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function operation_mentor(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "email";
			$page_data['file'] = "mentor.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function registration_portal(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "operation";
			$page_data['file'] = "registration_portal.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function registration_add(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "operation";
			$page_data['file'] = "registration_add.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "marriage_application_details";
			$this->load->view('back/index', $page_data);
	}
	public function my_business(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "myBussiness";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "my_bussiness";
			$this->load->view('back/index', $page_data);
	}
	public function tracked(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "myBussiness";
			$page_data['file'] = "track.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "my_bussiness";
			$this->load->view('back/index', $page_data);
		}
	public function edit_delete_slip_one(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "myBussiness";
			$page_data['file'] = "edit-one.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "my_bussiness";
			$this->load->view('back/index', $page_data);
		}
	public function report(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "report";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "report";
			$this->load->view('back/index', $page_data);
		}
	 public function my_network(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "my_network";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "my_network";
			$this->load->view('back/index', $page_data);
	 }
	 public function shortcuts(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "shortcuts";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$page_data['page_name'] = "shortcuts";
			$this->load->view('back/index', $page_data);
	 }
	 public function otherUser(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "other";
			$page_data['file'] = "index.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$this->load->view('back/index', $page_data);
	 }
	 public function otherGroup(){
			$page_data['top'] = "new-Code/index.php";
			$page_data['folder'] = "other";
			$page_data['file'] = "group.php";
			$page_data['bottom'] = "marriage_application_details/index.php";
			// $page_data['application_data'] = $this->db->get_where("marriage_application_form", array("application_id" => $para2))->result();
			$this->load->view('back/index', $page_data);
	 }
          /*-------new End-----------*/

public function get_projects_by_date()
{
    $range = $this->input->post('date_range');

    if ($range) {
        list($start_date, $end_date) = explode('|', $range);

        // Fetch records where date is between selected range
        $this->db->where('date >=', $start_date);
        $this->db->where('date <=', $end_date);
        $projects = $this->db->get('happy_story')->result();

        // Load view and pass projects and date range
        $this->load->library('pdf'); // assuming you use a PDF lib
        $data['projects'] = $projects;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        // Load view as HTML and convert to PDF
        $html = $this->load->view('pdf/project_report', $data, true);
        $this->pdf->create($html, 'project_report_' . date('YmdHis')); // see step 3 below
    } else {
        redirect('your_controller/form_page'); // fallback
    }
}


public function generate_pdf() {
    // Get admin ID from session
    $admin_id = $this->session->userdata('user_id');
    log_message('debug', 'Admin ID from session: ' . ($admin_id ? $admin_id : 'Not set'));

    if (!$admin_id) {
        show_error('No admin is logged in.', 403);
        return;
    }

    // Get posted date range
    $date_range = $this->input->post('date_range');
    log_message('debug', 'Date range received: ' . ($date_range ? $date_range : 'Not set'));
    if (!$date_range) {
        show_error('Date range is required', 400);
        return;
    }

    list($start_date, $end_date) = explode('|', $date_range);
    log_message('debug', 'Date range parsed - Start: ' . $start_date . ', End: ' . $end_date);

    // Load model and fetch project data from happy_story table
    $this->load->model('HappyStory_model');
    $data['projects'] = $this->HappyStory_model->get_projects_by_date($start_date, $end_date);
    log_message('debug', 'Fetched ' . count($data['projects']) . ' project records');

    // Fetch admin details
    $this->db->select('member_name, legion_name, area_name');
    $this->db->from('happy_story');
    $this->db->where('posted_by', $admin_id);
    $this->db->order_by('post_time', 'DESC');
    $this->db->limit(1);
    $query = $this->db->get();
    log_message('debug', 'Admin query executed: ' . $this->db->last_query());

    if ($query->num_rows() > 0) {
        $row = $query->row_array();
        $data['member_name'] = htmlspecialchars($row['member_name'] ?? 'N/A');
        $data['legion_name'] = htmlspecialchars($row['legion_name'] ?? 'N/A');
        $data['area_name'] = htmlspecialchars($row['area_name'] ?? 'N/A');
        log_message('debug', 'Admin data: ' . json_encode($row));
    } else {
        $data['member_name'] = 'N/A';
        $data['legion_name'] = 'N/A';
        $data['area_name'] = 'N/A';
        log_message('debug', 'No admin data found for posted_by: ' . $admin_id);
    }

    // Set title and company
    $formatted_start = date('d-m-Y', strtotime($start_date));
    $formatted_end = date('d-m-Y', strtotime($end_date));
    $data['title'] = "Report - $formatted_start to $formatted_end";
    $data['company'] = 'Senior Chamber International';

    // Load PDF library
    $this->load->library('pdf');

    // Load HTML content from view
    $html = $this->load->view('pdf/project_report', $data, true);

    // Create and render PDF
    $this->pdf->create($html, 'project_report_' . date('dmY_His'));
}



public function generate() {

   $admin_id = $this->session->userdata('admin_id'); // Get admin ID from session
    log_message('debug', 'Admin ID from session: ' . ($admin_id ? $admin_id : 'Not set'));
    log_message('debug', 'Session data: ' . json_encode($this->session->userdata()));


    // Rest of the method remains the same
    $range = $this->input->get('range');
    log_message('debug', 'generate() called with date_range: ' . ($range ? $range : 'Not set'));
    if (!$range) {
        show_error('Date range is required', 400);
        return;
    }

    list($start_date, $end_date) = explode('|', $range);
    log_message('debug', 'Date range parsed - Start: ' . $start_date . ', End: ' . $end_date);

    $this->load->model('Crud_model');
    $data['report_data'] = $this->Crud_model->get_report_by_range($start_date, $end_date);
    log_message('debug', 'Fetched ' . count($data['report_data']) . ' records for date range');

    foreach ($data['report_data'] as $row) {
        log_message('debug', 'Row: ' . json_encode($row));
    }

    if ($admin_id) {
        $this->db->select('member_name, legion_name, area_name');
        $this->db->from('happy_story');
        $this->db->where('posted_by', $admin_id);
        $this->db->order_by('post_time', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        log_message('debug', 'Admin query executed: ' . $this->db->last_query());

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['member_name'] = htmlspecialchars($row['member_name'] ?? 'N/A');
            $data['legion_name'] = htmlspecialchars($row['legion_name'] ?? 'N/A');
            $data['area_name'] = htmlspecialchars($row['area_name'] ?? 'N/A');
            log_message('debug', 'Admin data: ' . json_encode($row));
        } else {
            $data['member_name'] = 'N/A';
            $data['legion_name'] = 'N/A';
            $data['area_name'] = 'N/A';
            log_message('debug', 'No admin data found for posted_by: ' . $admin_id);
        }
    } else {
        $data['member_name'] = 'N/A';
        $data['legion_name'] = 'N/A';
        $data['area_name'] = 'N/A';
        log_message('debug', 'No admin ID set, defaulting to N/A for admin data');
    }

    $formatted_start = date('d-m-Y', strtotime($start_date));
    $formatted_end = date('d-m-Y', strtotime($end_date));
    $data['title'] = "Report - $formatted_start to $formatted_end";
    $data['company'] = "Senior Chamber International";

    $this->load->library('pdf');
    $html = $this->load->view('pdf/report_template', $data, true);
    $this->pdf->create($html, 'Report_' . date('dmY_His'));
}

public function update_story($id)
{
    $this->load->model('Crud_model');

    // Collect the POST data safely
    $data = array(
        'title'         => $this->input->post('story_name', true),
        'date'          => $this->input->post('dated', true),
        'program_date'  => $this->input->post('program_date', true), // ✅ New line added
        'description'   => $this->input->post('description', true),
        'program_area'  => $this->input->post('program_area', true),
    );

    // Handle file uploads for photos (if any)
    if (!empty($_FILES['activity_photo']['name'])) {
        $upload = $this->do_upload('activity_photo');
        if ($upload['status'] === 'success') {
            $data['activity_photo'] = $upload['file_name'];
        } else {
            $this->session->set_flashdata('error', $upload['error']);
            redirect('admin/stories/edit_story/' . $id);
            return;
        }
    }

    if (!empty($_FILES['press_coverage']['name'])) {
        $upload = $this->do_upload('press_coverage');
        if ($upload['status'] === 'success') {
            $data['press_coverage'] = $upload['file_name'];
        } else {
            $this->session->set_flashdata('error', $upload['error']);
            redirect('admin/stories/edit_story/' . $id);
            return;
        }
    }

    log_message('debug', 'Update data: ' . print_r($data, true));

    $update = $this->Crud_model->update_story($id, $data);

    if ($update) {
        $this->session->set_flashdata('success', 'Project updated successfully!');
    } else {
        $this->session->set_flashdata('error', 'Failed to update the project.');
    }

    redirect('admin/stories');
}

///////////////////////////////////////////////////////////////////////////////////////////////
private function getLegionPrefix($legionId) {
    // Define legion prefixes based on your legion system
    $legionPrefixes = array(
        1 => "LEG1",    // Legion 1
        2 => "LEG2",    // Legion 2  
        3 => "LEG3",    // Legion 3
        4 => "LEG4",    // Legion 4
        5 => "NATL",    // National Legion
        6 => "PREM",    // Premium Legion
        // Add more legion mappings as needed
    );
    
    return isset($legionPrefixes[$legionId]) ? $legionPrefixes[$legionId] : "MEM";
}


public function get_areas_ajax() {
    // Set JSON header
    header('Content-Type: application/json');
    
    // Load model
    $this->load->model('Crud_model');
    
    try {
        // Get areas with legions including prefix
        $areas = $this->Crud_model->get_areas_with_legions();
        
        log_message('debug', 'AJAX areas data: ' . json_encode($areas));
        
        echo json_encode([
            'success' => true,
            'data' => $areas
        ]);
    } catch (Exception $e) {
        log_message('error', 'AJAX get_areas error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load areas data'
        ]);
    }
}


// File upload helper function
private function do_upload($field_name)
{
    $config['upload_path'] = './uploads/happy_story_image/';
    $config['allowed_types'] = 'gif|jpg|png|jpeg';
    $config['max_size'] = 2048; // 2MB max
    $config['encrypt_name'] = TRUE;

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload($field_name)) {
        return ['status' => 'error', 'error' => $this->upload->display_errors()];
    } else {
        return ['status' => 'success', 'file_name' => $this->upload->data('file_name')];
    }
}

/////////////////////////////////////////////////////////////////////////////////

private function generate_member_invoice($payment_id)
{
    // Get payment details
    $payment = $this->db->get_where('package_payment', array('package_payment_id' => $payment_id))->row();
    
    if (!$payment) {
        show_error('Payment not found');
        return;
    }
    
    $member = $this->db->get_where('member', array('member_id' => $payment->member_id))->row();
    $plan = $this->db->get_where('plan', array('plan_id' => $payment->plan_id))->row();
    
    // Get company logo
    $logo_path = FCPATH . 'uploads/logo1.jpg';
    $logo_src = file_exists($logo_path)
        ? 'data:image/'.pathinfo($logo_path, PATHINFO_EXTENSION).';base64,'.base64_encode(file_get_contents($logo_path))
        : '';
    
    $data = array(
        'payment' => $payment,
        'member' => $member,
        'plan' => $plan,
        'logoSrc' => $logo_src,
        'system_title' => $this->system_title,
        'invoice_number' => $payment->invoice_number,
        'invoice_date' => date('d M Y', $payment->purchase_datetime)
    );
    
    // Load PDF library
    $html = $this->load->view('back/earnings/member_invoice', $data, true);
    $this->load->library('pdf');
    $this->pdf->loadHtml($html);
    $this->pdf->setPaper('A4', 'portrait');
    $this->pdf->render();
    $this->pdf->stream("Invoice_{$payment->invoice_number}.pdf", array("Attachment" => 1));
}



// PhonePe admin endpoints removed as requested


///////////////////////////////  Bulk payment methods //////////////////////////////////////

// ============================== Bulk Payment Section ==============================
function bulkpayment($para1 = "", $para2 = "", $para3 = "")
{
    if ($this->admin_permission() == FALSE) {
        redirect(base_url() . 'admin/login', 'refresh');
    } else {
        $page_data['title'] = "Admin || " . $this->system_title;

        // ============================================================
        // Main bulk payment member selection page (default)
        // ============================================================
        if ($para1 == "" || $para1 == "list") {
            $page_data['top'] = "dashboard.php";
            $page_data['folder'] = "bulkpayment";
            $page_data['file'] = "index.php";
            $page_data['bottom'] = "dashboard.php";
            $page_data['page_name'] = "bulkpayment";
            
            // Get ALL members from database
            $this->db->select('m.member_id, m.member_profile_id, m.first_name, m.last_name, 
                              m.gender, m.date_of_birth, m.membership, m.status');
            $this->db->from('member m');
            $this->db->where('m.status !=', 'deleted');
            $this->db->order_by('m.first_name', 'ASC');
            $members = $this->db->get()->result();

            // Calculate age from Unix timestamp
            foreach ($members as $member) {
                if (!empty($member->date_of_birth) && is_numeric($member->date_of_birth)) {
                    $birth_year = date('Y', $member->date_of_birth);
                    $current_year = date('Y');
                    $age = $current_year - $birth_year;
                    $member->age = $age > 0 ? $age : 'N/A';
                } else {
                    $member->age = 'N/A';
                }
            }

            $page_data['all_members'] = $members;

            // Flash messages
            if ($this->session->flashdata('alert') == "cart_updated") {
                $page_data['success_alert'] = translate('cart_updated_successfully');
            }

            $this->load->view('back/index', $page_data);
        }


// ============================================================
// Delete Bulk Payment Invoice (COMPLETE FIX)
// ============================================================
elseif ($para1 == 'delete_invoice' && $para2) {
    $invoice_id = intval($para2);
    
    log_message('debug', '=== DELETE INVOICE REQUEST ===');
    log_message('debug', 'Invoice ID: ' . $invoice_id);
    
    // Get invoice BEFORE any checks
    $invoice = $this->db->get_where('bulk_payment_invoices', ['invoice_id' => $invoice_id])->row();
    
    if (!$invoice) {
        log_message('error', 'Invoice not found: ' . $invoice_id);
        $this->session->set_flashdata('danger_alert', 'Invoice not found');
        redirect(base_url('admin/bulkpayment/invoices'));
        exit;
    }
    
    $admin_id = $invoice->paid_by_admin_id;
    $invoice_number = $invoice->invoice_number;
    
    log_message('debug', 'Found invoice: ' . $invoice_number);
    log_message('debug', 'Admin ID: ' . $admin_id);
    
    try {
        $this->db->trans_start();
        
        // Delete member invoices
        $member_data = json_decode($invoice->member_ids, true);
        if (!empty($member_data)) {
            foreach ($member_data as $member) {
                if (isset($member['invoice_number'])) {
                    $this->db->where('invoice_number', $member['invoice_number'])
                             ->delete('package_payment');
                    log_message('debug', 'Deleted member invoice: ' . $member['invoice_number']);
                }
            }
        }
        
        // Delete bulk invoice
        $this->db->where('invoice_id', $invoice_id)->delete('bulk_payment_invoices');
        log_message('debug', 'Deleted bulk invoice');
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction failed');
            $this->session->set_flashdata('danger_alert', 'Failed to delete invoice');
        } else {
            log_message('info', 'SUCCESS: Invoice ' . $invoice_number . ' deleted');
            $this->session->set_flashdata('success_alert', 'Invoice deleted successfully');
        }
        
    } catch (Exception $e) {
        log_message('error', 'Exception: ' . $e->getMessage());
        $this->session->set_flashdata('danger_alert', 'Error: ' . $e->getMessage());
    }
    
    // Redirect
    log_message('debug', 'Redirecting to admin: ' . $admin_id);
    redirect(base_url('admin/bulkpayment/invoices_by_admin/' . $admin_id));
    exit;
}

// ============================================================
// View invoices by admin (CHECK THIS PART)
// ============================================================
elseif ($para1 == 'invoices_by_admin' && $para2) {
    $admin_id = $para2;
    
    log_message('debug', '=== LOADING ADMIN INVOICES ===');
    log_message('debug', 'Admin ID: ' . $admin_id);
    
    $page_data['page_name'] = 'bulkpayment/admin_invoices';
    $page_data['page_title'] = 'Admin Payment History';
    $page_data['top'] = "dashboard.php";
    $page_data['folder'] = "bulkpayment";
    $page_data['file'] = "admin_invoices.php";
    $page_data['bottom'] = "dashboard.php";
    
    // Get admin details
    $admin = $this->db->get_where('admin', ['admin_id' => $admin_id])->row();
    
    if (!$admin) {
        log_message('error', 'Admin not found: ' . $admin_id);
        $this->session->set_flashdata('danger_alert', 'Admin not found');
        redirect(base_url('admin/bulkpayment/invoices'));
        exit;
    }
    
    $page_data['admin'] = $admin;
    
    // Get all invoices for this admin
    $invoices = $this->db
        ->where('paid_by_admin_id', $admin_id)
        ->order_by('payment_date', 'DESC')
        ->get('bulk_payment_invoices')
        ->result();
    
    log_message('debug', 'Found ' . count($invoices) . ' invoices');
    
    $page_data['invoices'] = $invoices;
    
    // ✅ IMPORTANT: Load view and STOP
    $this->load->view('back/index', $page_data);
    return; // ✅ Stop execution here
}













        
        // ============================================================
        // ✅ NEW: Invoice Listing Page
        // ============================================================
        elseif ($para1 == 'invoices') {
            $page_data['page_name'] = 'bulkpayment/invoice_list';
            $page_data['page_title'] = 'Bulk Payment Invoices';
            $page_data['top'] = "dashboard.php";
            $page_data['folder'] = "bulkpayment";
            $page_data['file'] = "invoice_list.php";
            $page_data['bottom'] = "dashboard.php";
            
            // Get all invoices grouped by paid_by_admin_id
            $this->db->select('paid_by_admin_id, COUNT(*) as total_invoices, 
                              SUM(total_amount) as total_paid, MAX(payment_date) as last_payment');
            $this->db->from('bulk_payment_invoices');
            $this->db->group_by('paid_by_admin_id');
            $this->db->order_by('last_payment', 'DESC');
            $invoice_summary = $this->db->get()->result();
            
            // Get admin details for each
            foreach ($invoice_summary as &$summary) {
                $admin = $this->db->get_where('admin', ['admin_id' => $summary->paid_by_admin_id])->row();
                $summary->admin_name = $admin ? $admin->name : 'Unknown';
                $summary->admin_email = $admin ? $admin->email : '';
            }
            
            $page_data['invoice_summary'] = $invoice_summary;
            
            $this->load->view('back/index', $page_data);
            return;
        }
        
        // ============================================================
        // ✅ NEW: View invoices by specific admin
        // ============================================================
        elseif ($para1 == 'invoices_by_admin' && $para2) {
            $admin_id = $para2;
            
            $page_data['page_name'] = 'bulkpayment/admin_invoices';
            $page_data['page_title'] = 'Admin Payment History';
            $page_data['top'] = "dashboard.php";
            $page_data['folder'] = "bulkpayment";
            $page_data['file'] = "admin_invoices.php";
            $page_data['bottom'] = "dashboard.php";
            
            // Get admin details
            $page_data['admin'] = $this->db->get_where('admin', ['admin_id' => $admin_id])->row();
            
            // Get all invoices for this admin
            $page_data['invoices'] = $this->db
                ->where('paid_by_admin_id', $admin_id)
                ->order_by('payment_date', 'DESC')
                ->get('bulk_payment_invoices')
                ->result();
            
            $this->load->view('back/index', $page_data);
            return;
        }
        
        // ============================================================
        // ✅ NEW: View single invoice details
        // ============================================================
        elseif ($para1 == 'invoice_detail' && $para2) {
            $invoice_id = $para2;
            
            $page_data['page_name'] = 'bulkpayment/invoice_detail';
            $page_data['page_title'] = 'Invoice Details';
            $page_data['top'] = "dashboard.php";
            $page_data['folder'] = "bulkpayment";
            $page_data['file'] = "invoice_detail.php";
            $page_data['bottom'] = "dashboard.php";
            
            // Get invoice
            $invoice = $this->db->get_where('bulk_payment_invoices', ['invoice_id' => $invoice_id])->row();
            
            if (!$invoice) {
                $this->session->set_flashdata('danger_alert', 'Invoice not found');
                redirect(base_url('admin/bulkpayment/invoices'));
                return;
            }
            
            $page_data['invoice'] = $invoice;
            
            // Get admin who paid
            $page_data['paid_by'] = $this->db->get_where('admin', ['admin_id' => $invoice->paid_by_admin_id])->row();
            
            // Get member details
            $member_data = json_decode($invoice->member_ids, true);
            $page_data['members'] = $member_data;
            
            // Get plan details
            $page_data['plan'] = $this->db->get_where('plan', ['plan_id' => $invoice->plan_id])->row();
            
            $this->load->view('back/index', $page_data);
            return;
        }
        
        // ============================================================
        // ✅ NEW: Payment success redirect page
        // ============================================================
        elseif ($para1 == 'payment_success' && $para2) {
            $transaction_id = $para2;
            
            // Get the bulk invoice by transaction ID
            $bulk_invoice = $this->db
                ->where('transaction_id', $transaction_id)
                ->order_by('invoice_id', 'DESC')
                ->limit(1)
                ->get('bulk_payment_invoices')
                ->row();
            
            if ($bulk_invoice) {
                // Redirect to invoice detail page
                redirect(base_url('admin/bulkpayment/invoice_detail/' . $bulk_invoice->invoice_id));
            } else {
                // Fallback to old success page if bulk invoice not found
                $this->bulkpayment('bulk_payment_success_page');
            }
            return;
        }

		// ============================================================
		// Download Invoice PDF
		// ============================================================
		elseif ($para1 == 'download_invoice' && $para2) {
			$invoice_id = $para2;
			
			// Load PDF helper
			$this->load->helper('pdf');
			
			// Get invoice data
			$invoice = $this->db->get_where('bulk_payment_invoices', ['invoice_id' => $invoice_id])->row();
			
			if (!$invoice) {
				$this->session->set_flashdata('danger_alert', 'Invoice not found');
				redirect(base_url('admin/bulkpayment/invoices'));
				return;
			}
			
			// Get admin details
			$admin = $this->db->get_where('admin', ['admin_id' => $invoice->paid_by_admin_id])->row();
			
			// Get member details
			$members = json_decode($invoice->member_ids, true);
			
			// Get plan details
			$plan = $this->db->get_where('plan', ['plan_id' => $invoice->plan_id])->row();
			
			// Get organization details (from settings)
			$org_name = $this->db->get_where('general_settings', ['type' => 'system_name'])->row();
			$org_email = $this->db->get_where('general_settings', ['type' => 'system_email'])->row();
			$org_phone = $this->db->get_where('general_settings', ['type' => 'contact_phone'])->row();
			
			// Prepare data for PDF
			$data = [
				'invoice' => $invoice,
				'admin' => $admin,
				'members' => $members,
				'plan' => $plan,
				'org_name' => $org_name ? $org_name->description : 'Your Organization',
				'org_email' => $org_email ? $org_email->description : 'info@yourorg.com',
				'org_phone' => $org_phone ? $org_phone->description : '+91 1234567890',
				'generated_date' => date('d M Y, h:i A')
			];
			
			// Load PDF view
			$html = $this->load->view('back/bulkpayment/invoice_pdf', $data, true);
			
			// Generate and download PDF
			$filename = 'Invoice_' . $invoice->invoice_number . '.pdf';
			generate_pdf($html, $filename, 'D');
		}


        
        // ============================================================
        // Update cart with selected package (AJAX)
        // ============================================================
        elseif ($para1 == 'update_cart_package') {
            header('Content-Type: application/json');
            
            log_message('debug', '=== UPDATE CART PACKAGE CALLED ===');
            
            $package_id = $this->input->post('package_id');
            $cart_items = $this->session->userdata('cart_items');
            
            log_message('debug', 'Package ID received: ' . $package_id);
            log_message('debug', 'Cart items count: ' . (is_array($cart_items) ? count($cart_items) : 0));
            
            // Validate inputs
            if (empty($package_id)) {
                log_message('error', 'Package ID is empty');
                echo json_encode(['status' => 'error', 'message' => 'No package selected']);
                exit;
            }
            
            if (empty($cart_items) || !is_array($cart_items)) {
                log_message('error', 'Cart items empty or invalid');
                echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
                exit;
            }
            
            // Get package details from database
            $package = $this->db->get_where('plan', ['plan_id' => $package_id])->row();
            
            if (!$package) {
                log_message('error', 'Package not found in database: ' . $package_id);
                echo json_encode(['status' => 'error', 'message' => 'Invalid package']);
                exit;
            }
            
            log_message('debug', 'Package found: ' . $package->name);
            
            // CORRECT GST CALCULATION
            $base_amount = floatval($package->amount);
            $gst_percentage = floatval($package->gst);
            $gst_amount = round(($base_amount * $gst_percentage) / 100, 2);
            $total_price = $base_amount + $gst_amount;
            
            log_message('debug', 'Calculation: Base=' . $base_amount . ', GST%=' . $gst_percentage . 
                       ', GST Amount=' . $gst_amount . ', Total=' . $total_price);
            
            // CRITICAL: Store package ID in session
            $this->session->set_userdata('selected_package_id', $package_id);
            
            $verify_session = $this->session->userdata('selected_package_id');
            log_message('debug', 'Session package_id set to: ' . $verify_session);
            
            // Update all cart items
            foreach ($cart_items as &$item) {
                $item['packageName'] = $package->name;
                $item['packageId'] = $package->plan_id;
                $item['amount'] = $total_price;
                $item['base_amount'] = $base_amount;
                $item['gst_percentage'] = $gst_percentage;
                $item['gst_amount'] = $gst_amount;
            }
            unset($item);
            
            $this->session->set_userdata('cart_items', $cart_items);
            
            log_message('debug', 'Cart items updated successfully');
            
            // RETURN JSON RESPONSE
            $response = [
                'status' => 'success',
                'message' => 'Package updated successfully',
                'package_name' => $package->name,
                'package_price' => $total_price,
                'base_amount' => $base_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'total_amount' => $total_price * count($cart_items)
            ];
            
            log_message('debug', 'Sending response: ' . json_encode($response));
            
            echo json_encode($response);
            exit;
        }

        // ============================================================
        // Payment Cart Page
        // ============================================================
        elseif ($para1 == "payment_cart") {
            $cart_items = $this->session->userdata('cart_items');
            
            if (empty($cart_items)) {
                $this->session->set_flashdata('warning_alert', 'No items in cart');
                redirect(base_url() . 'admin/bulkpayment', 'refresh');
            }
            
            // Get ALL packages from plan table
            $packages = $this->db->get('plan')->result();
            
            log_message('debug', 'Payment Cart - Packages count: ' . count($packages));
            
            $page_data['title'] = 'Admin | ' . $this->system_title;
            $page_data['top'] = "dashboard.php";
            $page_data['folder'] = "earnings";
            $page_data['file'] = "payment_cart.php";
            $page_data['bottom'] = "earnings/index.php";
            $page_data['page_name'] = "bulkpayment";
            $page_data['cart_items'] = $cart_items;
            $page_data['packages'] = $packages;
            
            $this->load->view('back/index', $page_data);
        }
        
        // ============================================================
        // Bulk Payment Success Page (OLD - for backward compatibility)
        // ============================================================
        elseif ($para1 == "bulk_payment_success_page") {
            $invoices = $this->session->userdata('bulk_payment_invoices');
            
            if (empty($invoices)) {
                log_message('error', 'No invoice data in session');
                $this->session->set_flashdata('danger_alert', 'No invoice data found');
                redirect(base_url() . 'admin/bulkpayment', 'refresh');
                return;
            }
            
            $page_data['top'] = "earnings/index.php";
            $page_data['folder'] = "earnings";
            $page_data['file'] = "bulk_payment_success.php";
            $page_data['bottom'] = "earnings/index.php";
            $page_data['page_name'] = "bulkpayment";
            $page_data['invoices'] = $invoices;

            $this->load->view('back/index', $page_data);
        }
        
        // ============================================================
        // AJAX: Store cart items in session
        // ============================================================
        elseif ($para1 == "store_cart_items") {
            $items = json_decode($this->input->post('items'), true);
            
            if (empty($items)) {
                echo json_encode(['status' => 'error', 'message' => 'No items selected']);
                return;
            }
            
            $this->session->set_userdata('cart_items', $items);
            echo json_encode([
                'status' => 'success',
                'message' => count($items) . ' items added to cart',
                'redirect' => base_url('admin/bulkpayment/payment_cart')
            ]);
        }
        
        // ============================================================
        // AJAX: Remove cart item
        // ============================================================
        elseif ($para1 == "remove_cart_item") {
            $item_id = $this->input->post('item_id');
            $cart_items = $this->session->userdata('cart_items');
            
            if ($cart_items) {
                foreach ($cart_items as $key => $item) {
                    if ($item['id'] == $item_id) {
                        unset($cart_items[$key]);
                        break;
                    }
                }
                
                $cart_items = array_values($cart_items);
                $this->session->set_userdata('cart_items', $cart_items);
                
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
            }
        }
        
        // ============================================================
        // ✅ NEW: Download Invoice PDF
        // ============================================================
        elseif ($para1 == 'download_invoice' && $para2) {
            $invoice_id = $para2;
            
            // Get invoice data
            $invoice = $this->db->get_where('bulk_payment_invoices', ['invoice_id' => $invoice_id])->row();
            
            if (!$invoice) {
                show_404();
                return;
            }
            
            // Get admin details
            $admin = $this->db->get_where('admin', ['admin_id' => $invoice->paid_by_admin_id])->row();
            
            // Get member details
            $members = json_decode($invoice->member_ids, true);
            
            // Get plan details
            $plan = $this->db->get_where('plan', ['plan_id' => $invoice->plan_id])->row();
            
            // Load PDF library (you'll need to install mPDF or TCPDF)
            // For now, let's create a simple HTML invoice
            $data = [
                'invoice' => $invoice,
                'admin' => $admin,
                'members' => $members,
                'plan' => $plan
            ];
            
            $this->load->view('back/bulkpayment/invoice_pdf', $data);
        }
        
        // ============================================================
        // Default: Redirect to list
        // ============================================================
        else {
            redirect(base_url('admin/bulkpayment'));
        }
    }
}

////////////////////////////////////////////////////////////		Bulk payment methods  END 			///////////////////////////////////////////////////////////////////////




public function add_to_bulk_payment_cart()
{
    $member_id = $this->input->post('member_id');
    $plan_id = $this->input->post('plan_id');
    $amount = $this->input->post('amount');
    
    // Get member and plan details
    $member = $this->db->get_where('member', ['member_id' => $member_id])->row();
    $plan = $this->db->get_where('plan', ['plan_id' => $plan_id])->row();
    
    if (!$member || !$plan) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid member or plan']);
        return;
    }
    
    // Check if package_payment record already exists
    $existing = $this->db->get_where('package_payment', [
        'member_id' => $member_id,
        'plan_id' => $plan_id,
        'payment_status' => 'due'
    ])->row();
    
    if (!$existing) {
        // Create new package_payment record
        $payment_data = [
            'plan_id' => $plan_id,
            'member_id' => $member_id,
            'payment_type' => 'bulk_payment',
            'payment_status' => 'due',
            'payment_details' => '',
            'amount' => $amount,
            'purchase_datetime' => time(),
            'payment_code' => '',
            'subscription_period' => 'yearly'
        ];
        
        $this->db->insert('package_payment', $payment_data);
        $package_payment_id = $this->db->insert_id();
        
        log_message('debug', 'Created package_payment record ID: ' . $package_payment_id . ' for member: ' . $member_id);
    } else {
        $package_payment_id = $existing->package_payment_id;
    }
    
    // Add to cart session
    $cart_items = $this->session->userdata('cart_items') ?: [];
    
    $cart_items[] = [
        'member_id' => $member_id,
        'package_payment_id' => $package_payment_id,
        'member_name' => $member->first_name . ' ' . $member->last_name,
        'plan_name' => $plan->name,
        'amount' => $amount
    ];
    
    $this->session->set_userdata('cart_items', $cart_items);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Added to cart',
        'package_payment_id' => $package_payment_id
    ]);
}

















public function ajax_update_cart_package() {
    header('Content-Type: application/json');
    
    $package_id = $this->input->post('package_id');
    $cart_items = $this->session->userdata('cart_items');
    
    if (empty($package_id) || empty($cart_items)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit;
    }
    
    $package = $this->db->get_where('plan', ['plan_id' => $package_id])->row();
    
    if (!$package) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid package']);
        exit;
    }
    
    $base_amount = floatval($package->amount);
    $gst_percentage = floatval($package->gst);
    $gst_amount = round(($base_amount * $gst_percentage) / 100, 2);
    $total_price = $base_amount + $gst_amount;
    
    $this->session->set_userdata('selected_package_id', $package_id);
    
    foreach ($cart_items as &$item) {
        $item['packageName'] = $package->name;
        $item['packageId'] = $package->plan_id;
        $item['amount'] = $total_price;
        $item['base_amount'] = $base_amount;
        $item['gst_percentage'] = $gst_percentage;
        $item['gst_amount'] = $gst_amount;
    }
    unset($item);
    
    $this->session->set_userdata('cart_items', $cart_items);
    
    echo json_encode([
        'status' => 'success',
        'package_name' => $package->name,
        'package_price' => $total_price,
        'base_amount' => $base_amount,
        'gst_percentage' => $gst_percentage,
        'gst_amount' => $gst_amount,
        'total_amount' => $total_price * count($cart_items)
    ]);
    exit;
}

// ========== MEMBERSHIP MANAGEMENT SECTION ==========
// public function membership_management($para1 = '', $para2 = '') {
//     if ($this->admin_permission() == FALSE) {
//         redirect(base_url() . 'admin/login', 'refresh');
//     }
    
//     $page_data['title'] = 'Admin | ' . $this->system_title;
    
//     if ($para1 == 'list_data') {
//         // AJAX DataTable Request
//         $columns = array(
//             0 => 'id',
//             1 => 'name',
//             2 => 'membership_value',
//             3 => 'slug',
//             4 => 'status'
//         );
        
//         $limit = $this->input->post('length');
//         $start = $this->input->post('start');
//         $order_index = $this->input->post('order')[0]['column'];
//         $order = isset($columns[$order_index]) ? $columns[$order_index] : 'id';
//         $dir = $this->input->post('order')[0]['dir'];
        
//         // Total records
//         $totalData = $this->db->count_all('membership_types');
        
//         // Search
//         $search = $this->input->post('search')['value'];
//         if (!empty($search)) {
//             $this->db->group_start();
//             $this->db->like('name', $search);
//             $this->db->or_like('slug', $search);
//             $this->db->or_like('membership_value', $search);
//             $this->db->group_end();
//         }
        
//         // Get filtered count
//         $totalFiltered = $this->db->count_all_results('membership_types', FALSE);
        
//         // Get data
//         $this->db->order_by($order, $dir);
//         $this->db->limit($limit, $start);
//         $query = $this->db->get();
        
//         $data = array();
//         if ($query->num_rows() > 0) {
//             foreach ($query->result() as $row) {
//                 $nestedData = array();
//                 $nestedData[] = $row->id;
//                 $nestedData[] = $row->name;
//                 $nestedData[] = $row->membership_value;
//                 $nestedData[] = $row->slug;
                
//                 // Status badge
//                 if ($row->status == 'active') {
//                     $nestedData[] = '<span class="label label-success">Active</span>';
//                 } else {
//                     $nestedData[] = '<span class="label label-danger">Inactive</span>';
//                 }
                
//                 // Actions
//                 $nestedData[] = '
//                     <a href="' . base_url() . 'admin/membership_management/edit/' . $row->id . '" 
//                        class="btn btn-info btn-xs btn-labeled fa fa-wrench" 
//                        data-toggle="tooltip" 
//                        title="Edit">
//                         Edit
//                     </a>';
                
//                 $data[] = $nestedData;
//             }
//         }
        
//         $json_data = array(
//             "draw" => intval($this->input->post('draw')),
//             "recordsTotal" => intval($totalData),
//             "recordsFiltered" => intval($totalFiltered),
//             "data" => $data
//         );
        
//         echo json_encode($json_data);
//         exit;
        
//     } elseif ($para1 == 'add') {
//         // Show add form
//         $page_data['page_name'] = 'add_membership';
//         $page_data['top'] = 'membership/index.php';
//         $page_data['folder'] = 'membership';
//         $page_data['file'] = 'add.php';
//         $page_data['bottom'] = 'membership/index.php';
        
//         $this->load->view('back/index', $page_data);
        
//     } elseif ($para1 == 'do_add') {
//         // Process add
//         $data = array(
//             'name' => $this->input->post('name'),
//             'slug' => $this->input->post('slug'),
//             'membership_value' => $this->input->post('membership_value'),
//             'display_order' => $this->input->post('display_order'),
//             'status' => 'active'
//         );
        
//         $this->db->insert('membership_types', $data);
//         recache();
//         $this->session->set_flashdata('alert', 'add');
//         redirect(base_url() . 'admin/membership_management', 'refresh');
        
//     } elseif ($para1 == 'edit') {
//         // Show edit form
//         $membership = $this->db->get_where('membership_types', array('id' => $para2))->row();
        
//         if (!$membership) {
//             redirect(base_url() . 'admin/membership_management', 'refresh');
//         }
        
//         $page_data['page_name'] = 'edit_membership';
//         $page_data['membership'] = $membership;
//         $page_data['top'] = 'membership/index.php';
//         $page_data['folder'] = 'membership';
//         $page_data['file'] = 'edit.php';
//         $page_data['bottom'] = 'membership/index.php';
        
//         $this->load->view('back/index', $page_data);
        
//     } elseif ($para1 == 'update') {
//         // Process update
//         $data = array(
//             'name' => $this->input->post('name'),
//             'slug' => $this->input->post('slug'),
//             'membership_value' => $this->input->post('membership_value'),
//             'display_order' => $this->input->post('display_order'),
//             'status' => $this->input->post('status')
//         );
        
//         $this->db->where('id', $para2);
//         $this->db->update('membership_types', $data);
//         recache();
        
//         $this->session->set_flashdata('alert', 'edit');
//         redirect(base_url() . 'admin/membership_management', 'refresh');
        
//     } else {
//         // List view (default)
//         $page_data['page_name'] = 'membership_list';
//         $page_data['top'] = 'membership/index.php';
//         $page_data['folder'] = 'membership';
//         $page_data['file'] = 'list.php';
//         $page_data['bottom'] = 'membership/index.php';

		
        
//         if ($this->session->flashdata('alert') == 'add') {
//             $page_data['success_alert'] = 'Membership type added successfully!';
//         } elseif ($this->session->flashdata('alert') == 'edit') {
//             $page_data['success_alert'] = 'Membership type updated successfully!';
//         }
        
//         $this->load->view('back/index', $page_data);
//     }
// }
// ========== END MEMBERSHIP MANAGEMENT ==========



// ========== MEMBERSHIP MANAGEMENT SECTION ==========
// public function membership_management($para1 = '', $para2 = '') {
//     if ($this->admin_permission() == FALSE) {
//         redirect(base_url() . 'admin/login', 'refresh');
//     }

//     $page_data['title'] = 'Admin | ' . $this->system_title;

//     if ($para1 == 'list_data') {
//         // (unchanged DataTable code)
//     } elseif ($para1 == 'add') {
//         $page_data['page_name'] = 'add_membership';
//         $page_data['top'] = 'dashboard.php';
//         $page_data['folder'] = 'membership';
//         $page_data['file'] = 'add.php';
//         $page_data['bottom'] = 'membership/index.php';
//         $this->load->view('back/index', $page_data);

//     } elseif ($para1 == 'do_add') {
//         // (unchanged insert logic)
//     } elseif ($para1 == 'edit') {
//         $membership = $this->db->get_where('membership_types', array('id' => $para2))->row();

//         if (!$membership) {
//             redirect(base_url() . 'admin/membership_management', 'refresh');
//         }

//         $page_data['page_name'] = 'edit_membership';
//         $page_data['membership'] = $membership;
//         $page_data['top'] = 'dashboard.php';
//         $page_data['folder'] = 'membership';
//         $page_data['file'] = 'edit.php';
//         $page_data['bottom'] = 'membership/index.php';
//         $this->load->view('back/index', $page_data);

//     } elseif ($para1 == 'update') {
//         // (unchanged update logic)
//     } else {
//         $page_data['page_name'] = 'membership_list';
//         $page_data['top'] = 'dashboard.php';
//         $page_data['folder'] = 'membership';
//         $page_data['file'] = 'list.php';
//         $page_data['bottom'] = 'membership/index.php';

//         if ($this->session->flashdata('alert') == 'add') {
//             $page_data['success_alert'] = 'Membership type added successfully!';
//         } elseif ($this->session->flashdata('alert') == 'edit') {
//             $page_data['success_alert'] = 'Membership type updated successfully!';
//         }

//         $this->load->view('back/index', $page_data);
//     }
// }
// ========== END MEMBERSHIP MANAGEMENT ==========
// ========== MEMBERSHIP MANAGEMENT SECTION ==========
public function membership_management($para1 = '', $para2 = '') {
    if ($this->admin_permission() == FALSE) {
        redirect(base_url() . 'admin/login', 'refresh');
    }

    $this->load->database();
    $this->load->model('Membership_model');
    $page_data['title'] = 'Admin | ' . $this->system_title;

    // ======== AJAX LIST (DataTable) ========
    if ($para1 == 'list_data') {
        $draw   = intval($this->input->post('draw'));
        $start  = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        if ($length <= 0) $length = 10;

        $search = $this->input->post('search')['value'] ?? '';

        $order_index = $this->input->post('order')[0]['column'] ?? 0;
        $order_columns = ['id', 'name', 'membership_value', 'slug', 'status'];
        $order_column = $order_columns[$order_index] ?? 'id';
        $order_dir = $this->input->post('order')[0]['dir'] ?? 'ASC';

        // --- Fetch Data ---
        $totalRecords  = $this->Membership_model->count_all();
        $totalFiltered = $this->Membership_model->count_filtered($search);
        $memberships   = $this->Membership_model->get_datatables($search, $start, $length, $order_column, $order_dir);

        // --- Prepare Data for DataTable ---
        $data = [];
        foreach ($memberships as $row) {
            $nestedData = [];
            $nestedData[] = $row->id;
            $nestedData[] = htmlspecialchars($row->name);
            $nestedData[] = htmlspecialchars($row->membership_value);
            $nestedData[] = htmlspecialchars($row->slug);
            $nestedData[] = ($row->status == 'active')
                ? '<span class="label label-success">Active</span>'
                : '<span class="label label-danger">Inactive</span>';

            $nestedData[] = '
                <a href="' . base_url('admin/membership_management/edit/' . $row->id) . '" 
                   class="btn btn-info btn-xs btn-labeled fa fa-wrench" data-toggle="tooltip" title="Edit">
                    Edit
                </a>
                <a href="javascript:void(0);" 
                   onclick="confirm_modal(\'' . base_url('admin/membership_management/delete/' . $row->id) . '\');" 
                   class="btn btn-danger btn-xs btn-labeled fa fa-trash" data-toggle="tooltip" title="Delete">
                    Delete
                </a>';

            $data[] = $nestedData;
        }

        // --- JSON Response ---
        $json_data = [
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ];

        // Log for debugging
        log_message('debug', 'Membership list_data called. Records: ' . count($memberships));
        echo json_encode($json_data);
        exit;
    }

    // ======== ADD PAGE ========
    elseif ($para1 == 'add') {
        $page_data['page_name'] = 'add_membership';
        $page_data['top'] = 'dashboard.php';
        $page_data['folder'] = 'membership';
        $page_data['file'] = 'add.php';
        $page_data['bottom'] = 'membership/index.php';
        $this->load->view('back/index', $page_data);
    }

    // ======== DO ADD ========
    elseif ($para1 == 'do_add') {
        $slug  = $this->input->post('slug', TRUE);
        $value = $this->input->post('membership_value', TRUE);

        if ($this->Membership_model->slug_exists($slug)) {
            $this->session->set_flashdata('danger_alert', 'Slug already exists!');
            redirect(base_url('admin/membership_management/add'));
        }

        if ($this->Membership_model->value_exists($value)) {
            $this->session->set_flashdata('danger_alert', 'Membership value already exists!');
            redirect(base_url('admin/membership_management/add'));
        }

        $data = [
            'name' => $this->input->post('name', TRUE),
            'slug' => $slug,
            'membership_value' => $value,
            'display_order' => $this->input->post('display_order', TRUE),
            'status' => 'active'
        ];

        if ($this->Membership_model->insert($data)) {
            recache();
            $this->session->set_flashdata('success_alert', 'Membership added successfully!');
        } else {
            $this->session->set_flashdata('danger_alert', 'Failed to add membership.');
        }

        redirect(base_url('admin/membership_management'));
    }

    // ======== EDIT PAGE ========
    elseif ($para1 == 'edit') {
        $membership = $this->Membership_model->get_by_id($para2);
        if (!$membership) {
            redirect(base_url('admin/membership_management'));
        }

        $page_data['page_name'] = 'edit_membership';
        $page_data['membership'] = $membership;
        $page_data['top'] = 'dashboard.php';
        $page_data['folder'] = 'membership';
        $page_data['file'] = 'edit.php';
        $page_data['bottom'] = 'membership/index.php';
        $this->load->view('back/index', $page_data);
    }

    // ======== DO UPDATE ========
    elseif ($para1 == 'update') {
        $slug  = $this->input->post('slug', TRUE);
        $value = $this->input->post('membership_value', TRUE);

        if ($this->Membership_model->slug_exists($slug, $para2)) {
            $this->session->set_flashdata('danger_alert', 'Slug already exists!');
            redirect(base_url('admin/membership_management/edit/' . $para2));
        }

        if ($this->Membership_model->value_exists($value, $para2)) {
            $this->session->set_flashdata('danger_alert', 'Membership value already exists!');
            redirect(base_url('admin/membership_management/edit/' . $para2));
        }

        $data = [
            'name' => $this->input->post('name', TRUE),
            'slug' => $slug,
            'membership_value' => $value,
            'display_order' => $this->input->post('display_order', TRUE),
            'status' => $this->input->post('status', TRUE)
        ];

        if ($this->Membership_model->update($para2, $data)) {
            recache();
            $this->session->set_flashdata('success_alert', 'Membership updated successfully!');
        } else {
            $this->session->set_flashdata('danger_alert', 'Update failed.');
        }

        redirect(base_url('admin/membership_management'));
    }

    // ======== DELETE ========
    elseif ($para1 == 'delete') {
        $membership = $this->Membership_model->get_by_id($para2);
        if ($membership) {
            $count = $this->Membership_model->count_members_by_type($membership->membership_value);
            if ($count > 0) {
                $this->session->set_flashdata('danger_alert', 'Cannot delete. ' . $count . ' members are using this type.');
            } else {
                $this->Membership_model->delete($para2);
                recache();
                $this->session->set_flashdata('success_alert', 'Membership deleted successfully!');
            }
        }
        redirect(base_url('admin/membership_management'));
    }

    // ======== DEFAULT LIST VIEW ========
    else {
        $page_data['page_name'] = 'membership_list';
        $page_data['top'] = 'dashboard.php';
        $page_data['folder'] = 'membership';
        $page_data['file'] = 'list.php';
        // $page_data['bottom'] = 'membership/index.php';
		$page_data['bottom'] = 'earnings/index.php';

        $this->load->view('back/index', $page_data);
    }
}
// ========== END MEMBERSHIP MANAGEMENT ==========









}