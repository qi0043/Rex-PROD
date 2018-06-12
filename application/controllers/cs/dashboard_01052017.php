<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller 
{
	 
	private $logger_cs;  //log
	 
	public function __construct()
     {
		parent::__construct();
		
		$ci =& get_instance();
		$ci->load->config('rex');
		$this->load->helper('url');
		
		//load database
		$this->load->model('cs/cs_model');
		
		//define logger folder application/logs/cs
		$loggings = $ci->config->item('log');
		$this->load->library('logging/logging',$loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder
		
		//load sessions
		$this->load->library('session');
	 }
	 
	
	public function index()
	{
		//get user fan from the server
		if(!isset($_SERVER['PHP_AUTH_USER']))
		{
			$this->logger_cs->error("Error: REMOTE_USER not set.");
			$errdata['message'] = 'Unable to get username';
			$errdata['heading'] = "Internal error";
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
		
		//logged user fan
		$fan = strtolower($_SERVER['PHP_AUTH_USER']);
		$data = array();
	
		/**
		 Login user role might be:
		 - a student
		 - a supervisor
		 - a student but also be a supervisor of other students
		**/
		
		//load model database
		//check if the login user is a student
		//return 0 if the login user fan is not found in the students list
		$students = $this->cs_model->db_match_stu_fan($fan);
		
		//check if the login user is a supervisor
		//return 0 if the login user fan is not found in the supervisors list
		$sups = $this->cs_model->db_match_sup_fan($fan);
		
		if($students && $sups) //login user is a student and a supervisor
		{
			$data['student']['id'] = $students[0]['id'];
			$data['supervisor']['id'] = $sups[0]['id'];
			
			//store user role and fan in the session
			$str = '';
			$str .= $fan; 
			
			$user_data = array('user_role' => 'rhd_stu_and_sup', 'fan' => $fan, 'stu_fan' => $str, 'sup_id' =>$sups[0]['id'], 'stu_id' =>$students[0]['id']);
			//$user_data = array('user_role' => 'rhd_sup', 'fan' => $fan);
			
			$this->session->set_userdata($user_data);
			$data['heading'] = 'My Dashboard';
			//$this->load->view('cs/stu_sup_view', $data);
			$this->load->view('cs/stu_sup_view', $data);
		}
		else if($sups) //login user is a supervisor
		{
			//store user role and fan in the session
			$user_data = array('user_role' => 'rhd_sup', 'fan' => $fan);
			$this->session->set_userdata($user_data);
			
			$this->supervisor_view($sups[0]['id']);
		}
		else if($students) //login user is a student
		{
			//store user role and fan in the session
			$str = '';
			$str.= $fan;
			$user_data = array('user_role' => 'rhd_stu', 'fan' =>$fan, 'stu_fan' => $str);
			$this->session->set_userdata($user_data);
			
			//$this->candidate_view($students[0]['id'], $fan);
			$this->candidate_view($students[0]['id']);
		}
		else // login user is neither a student nor a supervisor
		{
			$user_data = array('user_role' => 'unauthorized', 'fan' =>$fan);
			$this->session->set_userdata($user_data);
		    $this->logger_cs->error("Error: Unauthorised User Logged In: fan - " .$fan);
			$errdata['message'] = "<p>Access to this application is restricted to RHD students, supervisors, and other staff who are actively involved in supporting the RHD program.</p>
<p>If you believe that you should have access please contact your <a target='_blank' href='http://www.flinders.edu.au/graduate-research/about/academic-support.cfm'> Research Higher Degree Administrative Officer</a>  listed on the Office for Graduate Research site. </p>
";
			$errdata['heading'] = "RHD Research Excellence application";
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
	}
	
    /**** load view to supervisor view with supervisor data********/
	/**** @PARAM $id, the unique superviros id number from the database - supervisor table****************/
    public function supervisor($id)
	{
		$sup_info = $this->cs_model->db_get_all_sup_info($id);
		if($sup_info == 0)
		{
			$this->logger_cs->error("Error: invalid supervisor id input by fan: " . $_SERVER['PHP_AUTH_USER']);
			$errdata['heading'] = "Warning";
			$errdata['message'] = 'Invalid URL';
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
		
		$data['supervisor'] = $sup_info[0];
		unset($sup_info);
		
		$data['supervisor']['students'] = $this->getSupStuInfo($id);
		$str = '';
		$str .= strtolower($_SERVER['PHP_AUTH_USER']);
		
		if($data['supervisor']['students'] != 0)
		{
			foreach($data['supervisor']['students'] as $stu_info)
			{
				$str .= $stu_info['details']['stu_fan'];
			}
		}
		
		$user_data = array('stu_fan' => $str);
		$this->session->set_userdata($user_data);
		
		$data['heading'] = 'Supervisor Dashboard';
		
		$this->load->view('cs/supervisor_view', $data);
		
		/*echo '<pre>';
		print_r($data);
		echo '</pre>';*/
	}
	
	public function supervisor_view($id)
	{
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		
		$ci =& get_instance();
		$ci->load->config('rex');
		
		$collection_id = $ci->config->item('milestone_collection');
		$examination_id = $ci->config->item('examination_collection');
		
		$this->load->helper('url');
		
		$success = $this->rexrest->processClientCredentialToken();
		
		
		$sup_info = $this->cs_model->db_get_all_sup_info($id);
		if($sup_info == 0)
		{
			$this->logger_cs->error("Error: invalid supervisor id input by fan: " . $_SERVER['PHP_AUTH_USER']);
			$errdata['heading'] = "Warning";
			$errdata['message'] = 'Invalid URL';
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
		
		$data['supervisor'] = $sup_info[0];
		unset($sup_info);
		
		$data['supervisor']['students'] = $this->getSupStuDetails($id);
		$str = '';
		$str .= strtolower($_SERVER['PHP_AUTH_USER']);
		
		if($data['supervisor']['students'] != 0)
		{
			foreach($data['supervisor']['students'] as $stu_info)
			{
				$str .= $stu_info['stu_fan'];
			}
		}
		
		$user_data = array('stu_fan' => $str);
		$this->session->set_userdata($user_data);
		
		$data['heading'] = 'Supervisor Dashboard';
		
		/************************************/
		/**** Milestones****/
		/************************************/
		
		if(isset($data['supervisor']['students'][0]))
		{
			for($i = 0; $i < count($data['supervisor']['students']); $i++)
			{
				$stu_fan = isset($data['supervisor']['students'][$i]['stu_fan']) ? $data['supervisor']['students'][$i]['stu_fan'] : '';
				$stu_id = isset($data['supervisor']['students'][$i]['rhd_stu_id']) ? $data['supervisor']['students'][$i]['rhd_stu_id'] : '';
				if($stu_fan != '' && $id != '')
				{
					$stu_milestones = $this->cs_model->db_get_stu_milestone($stu_id);
					$milestones = array();
					$current_milestone = '';
					$current_milestone_code = '';
					$current_milestone_due_date = '';		
					if($stu_milestones != 0)
					{
						$current_milestone = $stu_milestones[0]['milestone_name'];
						$current_milestone_code = $stu_milestones[0]['milestone_code'];
						$current_milestone_due_date = $stu_milestones[0]['due_date'];
						if(count($stu_milestones) > 1)
						{
							for($i=0; $i<count($stu_milestones); $i++)
							{
								$due_date = $stu_milestones[$i]['due_date'];
								
								if(strtotime($due_date) < strtotime($current_milestone_due_date))
								{
									$current_milestone = $stu_milestones[$i]['milestone_name'];
									$current_milestone_code = $stu_milestones[$i]['milestone_code'];
									$current_milestone_due_date = $due_date;
									
								}
								
							}
						}
					}
					$milestones['current_milestone'] = $current_milestone;
					$milestones['current_milestone_code'] = $current_milestone_code;
					$milestones['current_milestone_due_date'] = $current_milestone_due_date;
				
					//search in Equella to get student existed milestones
					$where = "/xml/item/people/candidate/fan='".$stu_fan."' OR ";
					$where = $where . "/xml/item/people/candidate/fan='" . $stu_fan ."'";
					
					$where = urlencode($where);
			
					$searchsuccess = $this->rexrest->search($response, '', $collection_id, $where, 0, 50, 'modified', false, 'all', true);
					
					if(isset($response['available']) && $response['available'] > 0)
					{
						$j = 0;
						foreach($response['results'] as $result)
						{
							$j++;
							$xmlwrapper_name = 'xmlwrapper_stu_milestone'.$i. '_'.$j;
							$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$result['metadata']), $xmlwrapper_name);
							$milestone_name = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name');
							$milestone_id = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name_id');
							$stu_sign_off = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/evidence_finalised');
		
							$milestones['milestone'][$j]['modifiedDate'] = $result['modifiedDate'];
							$milestones['milestone'][$j]['name'] = $milestone_name;
							$milestones['milestone'][$j]['name_id'] = $milestone_id ;
							$milestones['milestone'][$j]['uuid'] = $result['uuid'];
							$milestones['milestone'][$j]['version'] = $result['version'];
							$milestones['milestone'][$j]['status'] = $result['status'];
							$milestones['milestone'][$j]['modifiedDate'] = $result['modifiedDate'];
							$milestones['milestone'][$j]['stu_sign_off'] = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/evidence_finalised');
						}
					}
					
					$data['supervisor']['students'][$i]['milestones'] = $milestones;
					
					/************************************/
					/**** Examination panel****/
					/************************************/
					
					//search in Equella to get student existed examination thesis
					$s_success = $this->rexrest->search($r, '', $examination_id, $where, 0, 1, 'modified', false, 'all', true);
					if(isset($r['available']) && $r['available'] > 0)
					{
						$xmlwrapper_name = 'xmlwrapper_exam';
						$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$r['results'][0]['metadata']), $xmlwrapper_name);
						$thesis_name = $this->$xmlwrapper_name->nodeValue('/xml/item/thesis/title');
						
						$data['supervisor']['students'][$i]['examination']['status'] = $r['results'][0]['status'];
						$data['supervisor']['students'][$i]['examination']['uuid'] = $r['results'][0]['uuid'];
						$data['supervisor']['students'][$i]['examination']['version'] = $r['results'][0]['version'];
						$data['supervisor']['students'][$i]['examination']['thesis_title'] = $thesis_name;
						$data['supervisor']['students'][$i]['examination']['modifiedDate'] = $r['results'][0]['modifiedDate'];
					}
					else
					{
						$data['supervisor']['students'][$i]['examination'] = '';
					}
					
				}
				
			}
		}
		
		
		$this->load->view('cs/supervisor_view', $data);
	}
	
	
	public function candidate($id, $stu_fan)
	{
		$data = $this->getStuInfo($id, $stu_fan);
		
		/*echo '<pre>';
		print_r($data);
		echo '</pre>';*/
		$data['heading'] = 'Student Milestones';
		
		$this->load->view('cs/student_view', $data);
	}
	
	/**** NOT IN USE Ajax Call from view/milestone_modal_view  .to create a milestome in REX ********/
    public function create_milestones()
    {
		$this->load->helper('url');
		$ci = & get_instance();
		$collection_id = $ci->config->item('milestone_collection');
		
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		$success = $this->rexrest->processClientCredentialToken();
		if(!$success)
		{
			$result['status'] = "error";
			$result['error_info'] = $this->rexrest->error;
			$this->logger_cs->error("Error: error in rest - processClientCredentialToken for fan - " .strtolower($_SERVER['REMOTE_USER']));
			echo json_encode($result);
			return;
		}
	
		//get POST variables from the view
		$milestone = $_POST["milestone"];
		$name_id = $_POST["milestone_id"];
		$fan = $_POST["candidate_fan"];
		$candidate_given_name = $_POST["candidate_given_name"];
		$candidate_pref_name = $_POST["candidate_pref_name"];
		$candidate_family_name = $_POST["candidate_family_name"];
		$topic_code = $_POST["candidate_topic_code"];
		$course_code = $_POST["candidate_course_code"];
		$course_title = $_POST["candidate_course_title"];
		#$attendance = $_POST["candidate_course_attendance"];
		$topic_status = $_POST["candidate_topic_status"];
		$candidate_id = $_POST["candidate_id"];
		#$candidate_email = $_POST["candidate_email"];
		$candidate_thesis_title = $_POST["candidate_thesis_title"];
		
		$candidate_school = $_POST["candidate_school"];
		$candidate_school_name = $_POST["candidate_school_name"];
		
		$candidate_load= $_POST["candidate_load"];
		$tcs = $_POST["tcs"];
		$candidate_commence_date = $_POST["candidate_commence_date"];
		$candidate_sub_date = $_POST["candidate_sub_date"];
		$candidate_residency = $_POST["candidate_residency"];
		$candidate_rts_end_date = $_POST["candidate_rts_end_date"];
		#$candidate_eftsl= $_POST["candidate_eftsl"];
		#$candidate_unreported_eftsl = $_POST["candidate_unreported_eftsl"];
		#$candidate_total_eftsl = $_POST["candidate_total_eftsl"];
		$tcs = $_POST["tcs"];
		
		//create a new item
		$item_bean["collection"]["uuid"] = $collection_id;
		//load root node
		$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => '<xml><item></item></xml>'));
		
		$thesis_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/thesis/title");
		$this->xmlwrapper->createTextNode($thesis_name, $candidate_thesis_title);
	
		$t_code = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/code");
		$this->xmlwrapper->createTextNode($t_code, $topic_code);
		
		$c_code = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/courses/course/code");
		$this->xmlwrapper->createTextNode($c_code, $course_code);
		
		$c_title = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/courses/course/name");
		$this->xmlwrapper->createTextNode($c_title, $course_title);
	
		$c_school= $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/org_unit");
		$this->xmlwrapper->createTextNode($c_school, $candidate_school);
		
		$c_school_name= $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/org_unit_name");
		$this->xmlwrapper->createTextNode($c_school_name, $candidate_school_name);
		
		$sub_date = $this->xmlwrapper->createNodeFromXPath("/xml/item/thesis/expected_submission_date");
		$this->xmlwrapper->createTextNode($sub_date, $candidate_sub_date);
		
		$progression_node = $this->xmlwrapper->createNodeFromXPath("/xml/item/progression");
		$milestone_node = $this->xmlwrapper->createNode($progression_node, "milestone");
		$milestone_type = $this->xmlwrapper->createAttribute($milestone_node, "name");
		$milestone_type->nodeValue = $milestone;
		
		$milestone_id = $this->xmlwrapper->createAttribute($milestone_node, "name_id");
		$milestone_id->nodeValue = $name_id;
	
		$stu_fan = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/fan");
		$this->xmlwrapper->createTextNode($stu_fan, strtolower($fan));
	
		$stu_id = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/id");
		$this->xmlwrapper->createTextNode($stu_id, $candidate_id);
		
		$stu_f_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/first_names");
		$this->xmlwrapper->createTextNode($stu_f_name, $candidate_given_name);
		
		$stu_l_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/last_names");
		$this->xmlwrapper->createTextNode($stu_l_name, $candidate_family_name);
		
		$candidature = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature");
		$start_date = $this->xmlwrapper->createAttribute($candidature, "start_date");
		$start_date->nodeValue = $candidate_commence_date;

		$load = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/load/FTE_type");
		$this->xmlwrapper->createTextNode($load, $candidate_load);
		
		/*$degree = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/load/FTE");
		$this->xmlwrapper->createTextNode($degree, $candidate_degree);*/
		
		$rtscheme = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/RTScheme");
		$rts_status = $this->xmlwrapper->createAttribute($rtscheme, "status");
		$rts_status->nodeValue = $topic_status;
		
		
		$end_date = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/RTScheme/end_date");
		$this->xmlwrapper->createTextNode($end_date, $candidate_rts_end_date);
		
		if($tcs != '')
		{
			$tc_root = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/supervisors");
			foreach($tcs as $tc)
			{
				$tc_node = $this->xmlwrapper->createNode($tc_root, "supervisor");
				$node_role= $this->xmlwrapper->createNode($tc_node, "role");
				$node_fan = $this->xmlwrapper->createNode($tc_node, "fan");
				$node_title = $this->xmlwrapper->createNode($tc_node, "title");
				$node_first_name = $this->xmlwrapper->createNode($tc_node, "first_names");
				$node_last_name = $this->xmlwrapper->createNode($tc_node, "last_names");
				
				$node_role->nodeValue = $tc['role']; 
				$node_fan->nodeValue = $tc['fan'];
				$node_title->nodeValue = $tc['title'];
				$node_first_name->nodeValue = $tc['given_name'];
				$node_last_name->nodeValue = $tc['family_name'];
				
			}
		}
		
		$dis = '';
		if(strlen($topic_code))
		{
			$dis = strtoupper(substr($topic_code, 0, 4));
		}
		
		if($dis!= '')
		{
			$postgra = $this->cs_model->db_get_postgrd_coord($dis);
			$sys_workflow = $this->xmlwrapper->createNodeFromXPath("/xml/item/sys_workflow");
			$postgrd_coord = $this->xmlwrapper->createNode($sys_workflow, "postgraduate_coordinator");
			$postgrd_coord->nodeValue = $postgra[0]['fan'];
			
			$coords = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/coordinators");
			$coord = $this->xmlwrapper->createNode($coords, "coordinator");
			
			$coord_fan = $this->xmlwrapper->createNode($coord, "fan");
			$coord_fan->nodeValue = $postgra[0]['fan'];
			
			$coord_role = $this->xmlwrapper->createNode($coord, "role");
			$coord_role->nodeValue = $postgra[0]['role_name'];
			
			$coord_first_name = $this->xmlwrapper->createNode($coord, "first_names");
			$coord_first_name ->nodeValue = $postgra[0]['first_name'];
			
			$coord_last_name = $this->xmlwrapper->createNode($coord, "last_names");
			$coord_last_name ->nodeValue = $postgra[0]['last_name'];
		}
		
		
		$item_bean['metadata'] = $this->xmlwrapper->__toString();
		$success = $this->rexrest->createDraftItem($item_bean, $response1);

		if(!$success)
		{
			$this->logger_cs->error("Error: error to create draft item for fan - " .strtolower($_SERVER['PHP_AUTH_USER']) . '-' . $this->rexrest->error);
			$result['error_info'] = 'error';
			$result['error_info'] = $this->flexrest->error;
			echo json_encode($result);
			return;
		}
		
		if(!isset($response1['headers']['location']))
		{
			$this->logger_cs->error('createItem failed' . ', error: ' . 'No Location header in createItem response.');
			#$this->load->view('reading_listmgr/showerror_view', $errdata);
			$result['status'] = 'error';
			$result['error_info'] = $errdata['message'];
			echo json_encode($result);
			return;
		}
		
		$location = $response1['headers']['location'];
		$uuid = substr($location, strpos($location, 'item')+5, 36);
		$version = substr($location, strpos($location, 'item')+42, (strlen($location)-1-(strpos($location, 'item')+42)));

		$result['status'] = "success";
		$result['uuid'] = $uuid;
		$result['version'] = $version;
		$result['token'] = $this->generateToken($fan);
		#$result['token'] = $this->generateToken('qi0043');
		#$result['token'] = $this->generateToken('team0003');
		echo json_encode($result);
		
    }

	//AJAX call from view/student_view to create a milestone
	public function stundentData()
	{
		$this->load->helper('url');
		$ci = & get_instance();
		$ci->load->config('rex');
		$collection_id = $ci->config->item('milestone_collection');
		
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		$success = $this->rexrest->processClientCredentialToken();
		if(!$success)
		{
			$result['status'] = "error";
			$result['error_info'] = $this->rexrest->error;
			$this->logger_cs->error("Error: error in rest - processClientCredentialToken for fan - " .strtolower($_SERVER['PHP_AUTH_USER']));
			echo json_encode($result);
			return;
		}
		
	
		//get POST variables from the view
		$mile_stone = $_POST["milestone"];
		$milestone_id = $_POST["milestone_id"];
		$id = $_POST["id"];
		
		$data = array();
		$data['milestone_id'] = $_POST["milestone_id"];
		$students = $this->cs_model->db_get_rhd_student_info($id);
		$data['student'] = $students[0];
		$supervisors = $this->cs_model->db_get_stu_sup_info($id);
		
		for($index = 0; $index < count($supervisors); $index++)
		{
			$sup_info = $this->cs_model->db_get_all_sup_info($supervisors[$index]['rhd_supv_id']);
			
			$supervisors[$index]['supv_staff_id'] = $sup_info[0]['supv_staff_id'];
			$supervisors[$index]['supv_fan'] = $sup_info[0]['supv_fan'];
			$supervisors[$index]['title'] = $sup_info[0]['title'];
			$supervisors[$index]['given_name'] = $sup_info[0]['given_name'];
			$supervisors[$index]['family_name'] = $sup_info[0]['family_name'];
			$supervisors[$index]['email'] = $sup_info[0]['email'];
		}	
		
		
		$data['student']['supervisors'] = $supervisors;
		$data['milestone'] = '';
		switch ($mile_stone)
		{
			case 'coc':
				$data['milestone'] = 'Confirmation of Candidature';
				break;
			case 'mcr':
				$data['milestone'] = 'Mid-Candidature Review';
				break;
			case 'ftr':
				$data['milestone'] = 'Final Thesis Review';
				break;
			case 'ir':
				$data['milestone'] = 'Interim Candidature Review';
				break;
		}
		
		
		/***********************************
		 get school name from the taxonomy by org_num (org_unit)
		************************************/
		$rhd_schools_taxonomy_uuid = $ci->config->item('rhd_schools_taxonomy_uuid');
		$this->rexrest->getTerms($rhd_schools_taxonomy_uuid, '', $r);
		$school_name = '';
		
		for($i=0; $i < count($r); $i++)
		{
			if(isset($r[$i]['term']) && $r[$i]['term']!= '')
			{	
				$this->rexrest->getTermData($rhd_schools_taxonomy_uuid, $r[$i]['uuid'], 'org_unit', $r_data);
				if($data['student']['school_org_num'] == $r_data['org_unit'])
				{
					$school_name = $r[$i]['term'];
				}
			}
		}
		$data['student']['school_name'] = $school_name;
		
		//create a new item
		$item_bean["collection"]["uuid"] = $collection_id;
		//load root node
		$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => '<xml><item></item></xml>'));
		
		$thesis_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/thesis/title");
		$this->xmlwrapper->createTextNode($thesis_name, $data['student']['thesis_title']);
	
		$t_code = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/code");
		$this->xmlwrapper->createTextNode($t_code, $data['student']['topic_code']);
		
		$c_code = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/courses/course/code");
		$this->xmlwrapper->createTextNode($c_code, $data['student']['course_code']);
		
		$c_title = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/courses/course/name");
		$this->xmlwrapper->createTextNode($c_title, $data['student']['course_title']);
	
		$c_school= $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/org_unit");
		$this->xmlwrapper->createTextNode($c_school, $data['student']['school_org_num']);
		
		$c_school_name= $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/org_unit_name");
		$this->xmlwrapper->createTextNode($c_school_name, $data['student']['school_name']);
		
		$sub_date = $this->xmlwrapper->createNodeFromXPath("/xml/item/thesis/expected_submission_date");
		$this->xmlwrapper->createTextNode($sub_date, $data['student']['est_submit_date']);
		
		$progression_node = $this->xmlwrapper->createNodeFromXPath("/xml/item/progression");
		$milestone_node = $this->xmlwrapper->createNode($progression_node, "milestone");
		$milestone_type = $this->xmlwrapper->createAttribute($milestone_node, "name");
		$milestone_type->nodeValue = $data['milestone'];
		
		$name_id = $this->xmlwrapper->createAttribute($milestone_node, "name_id");
		$name_id->nodeValue = $milestone_id;
	
		$stu_fan = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/fan");
		$this->xmlwrapper->createTextNode($stu_fan, strtolower($data['student']['stu_fan']));
	
		$stu_id = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/id");
		$this->xmlwrapper->createTextNode($stu_id, $data['student']['stu_id']);
		
		$stu_f_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/first_names");
		$this->xmlwrapper->createTextNode($stu_f_name, $data['student']['given_name']);
		
		$stu_l_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/last_names");
		$this->xmlwrapper->createTextNode($stu_l_name, $data['student']['family_name']);
		
		$candidature = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature");
		$start_date = $this->xmlwrapper->createAttribute($candidature, "start_date");
		$start_date->nodeValue = $data['student']['start_date'];

		$res = $this->xmlwrapper->createAttribute($candidature, "residency_type");
		$res->nodeValue = $data['student']['residency'];
		
		$topic_status = $this->xmlwrapper->createAttribute($candidature, "status");
		$topic_status->nodeValue = $data['student']['topic_status'];
		
		$eftsl_total = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/eftsl/total");
		$this->xmlwrapper->createTextNode($eftsl_total, $data['student']['total_eftsl']);
       
	   	$eftsl_unreported = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/eftsl/unreported");
		$this->xmlwrapper->createTextNode($eftsl_unreported, $data['student']['unreported_eftsl']);
		
		$eftsl_reported = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/eftsl/reported");
		$this->xmlwrapper->createTextNode($eftsl_reported, $data['student']['reported_eftsl']);
		
		$load = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/load/FTE_type");
		$this->xmlwrapper->createTextNode($load, $data['student']['load']);
		
		$rtscheme = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/RTScheme");
		$catrgory_node = $this->xmlwrapper->createNode($rtscheme, "category");
		$catrgory_node->nodeValue = $data['student']['liability_category'];
		
		$end_date = $this->xmlwrapper->createNode($rtscheme, "end_date");
		$end_date->nodeValue = $data['student']['fec_date'];
		
		$schol_details = $this->xmlwrapper->createNode($rtscheme, "schol_details");
		$schol_details->nodeValue = $data['student']['schol_details'];
		
		
		if(count($data['student']['supervisors']) > 0)
		{
			$tc_root = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/supervisors");
			foreach($data['student']['supervisors'] as $tc)
			{
				$tc_node = $this->xmlwrapper->createNode($tc_root, "supervisor");
				$node_role= $this->xmlwrapper->createNode($tc_node, "role");
				$node_fan = $this->xmlwrapper->createNode($tc_node, "fan");
				$node_title = $this->xmlwrapper->createNode($tc_node, "title");
				$node_first_name = $this->xmlwrapper->createNode($tc_node, "first_names");
				$node_last_name = $this->xmlwrapper->createNode($tc_node, "last_names");
				
				$node_role->nodeValue = $tc['supv_role']; 
				$node_fan->nodeValue = $tc['supv_fan'];
				$node_title->nodeValue = $tc['title'];
				$node_first_name->nodeValue = $tc['given_name'];
				$node_last_name->nodeValue = $tc['family_name'];
				
			}
			
		}
		
		$dis = '';
		if(strlen($data['student']['topic_code']))
		{
			$dis = strtoupper(substr($data['student']['topic_code'], 0, 4));
		}
		
		if($dis!= '')
		{
			$postgra = $this->cs_model->db_get_postgrd_coord($dis);
			$sys_workflow = $this->xmlwrapper->createNodeFromXPath("/xml/item/sys_workflow");
			$postgrd_coord = $this->xmlwrapper->createNode($sys_workflow, "postgraduate_coordinator");
			$postgrd_coord->nodeValue = $postgra[0]['fan'];
			
			$coords = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/coordinators");
			$coord = $this->xmlwrapper->createNode($coords, "coordinator");
			
			$coord_fan = $this->xmlwrapper->createNode($coord, "fan");
			$coord_fan->nodeValue = $postgra[0]['fan'];
			
			$coord_role = $this->xmlwrapper->createNode($coord, "role");
			$coord_role->nodeValue = $postgra[0]['role_name'];
			
			$coord_first_name = $this->xmlwrapper->createNode($coord, "first_names");
			$coord_first_name ->nodeValue = $postgra[0]['first_name'];
			
			$coord_last_name = $this->xmlwrapper->createNode($coord, "last_names");
			$coord_last_name ->nodeValue = $postgra[0]['last_name'];
		}
		
		
		$item_bean['metadata'] = $this->xmlwrapper->__toString();
		$success = $this->rexrest->createDraftItem($item_bean, $response1);

		if(!$success)
		{
			$this->logger_cs->error("Error: error to create draft item for fan - " .strtolower($_SERVER['PHP_AUTH_USER']) . '-' . $this->rexrest->error);
			$result['status'] = 'error';
			$result['error_info'] = $this->flexrest->error;
			echo json_encode($result);
			return;
		}
		
		if(!isset($response1['headers']['location']))
		{
			$this->logger_cs->error('createItem failed' . ', error: ' . 'No Location header in createItem response.');
			#$this->load->view('reading_listmgr/showerror_view', $errdata);
			$result['status'] = 'error';
			$result['error_info'] = $errdata['message'];
			echo json_encode($result);
			return;
		}
		
		$location = $response1['headers']['location'];
		$uuid = substr($location, strpos($location, 'item')+5, 36);
		$version = substr($location, strpos($location, 'item')+42, (strlen($location)-1-(strpos($location, 'item')+42)));

		$result['status'] = "success";
		$result['uuid'] = $uuid;
		$result['version'] = $version;
		$result['token'] = $this->generateToken(strtolower($_SERVER['PHP_AUTH_USER']));
		
		echo json_encode($result);
		
		#$this->load->view('cs/milestone_modal_view', $data);
		
	}
	
	//AJAX call from view/student_view to create a examination
	public function create_examination()
	{
		$this->load->helper('url');
		$ci = & get_instance();
		$ci->load->config('rex');
		$collection_id = $ci->config->item('examination_collection');
		
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		$success = $this->rexrest->processClientCredentialToken();
		if(!$success)
		{
			$result['status'] = "error";
			$result['error_info'] = $this->rexrest->error;
			$this->logger_cs->error("Error: error in rest - processClientCredentialToken for fan - " .strtolower($_SERVER['PHP_AUTH_USER']));
			echo json_encode($result);
			return;
		}
		
	
		//get POST variables from the view
		$id = $_POST["id"];
		
		$data = array();
		$students = $this->cs_model->db_get_rhd_student_info($id);
		$data['student'] = $students[0];
		$supervisors = $this->cs_model->db_get_stu_sup_info($id);
		
		if(count($supervisors) > 0)
		{
			for($index = 0; $index < count($supervisors); $index++)
			{
				$sup_info = $this->cs_model->db_get_all_sup_info($supervisors[$index]['rhd_supv_id']);
				
				$supervisors[$index]['supv_staff_id'] = $sup_info[0]['supv_staff_id'];
				$supervisors[$index]['supv_fan'] = $sup_info[0]['supv_fan'];
				$supervisors[$index]['title'] = $sup_info[0]['title'];
				$supervisors[$index]['given_name'] = $sup_info[0]['given_name'];
				$supervisors[$index]['family_name'] = $sup_info[0]['family_name'];
				$supervisors[$index]['email'] = $sup_info[0]['email'];
			}	
		}
		
		$data['student']['supervisors'] = $supervisors;
		
		
		/***********************************
		 get school name from the taxonomy by org_num (org_unit)
		************************************/
		$rhd_schools_taxonomy_uuid = $ci->config->item('rhd_schools_taxonomy_uuid');
		$this->rexrest->getTerms($rhd_schools_taxonomy_uuid, '', $r);
		$school_name = '';
		
		for($i=0; $i < count($r); $i++)
		{
			if(isset($r[$i]['term']) && $r[$i]['term']!= '')
			{	
				$this->rexrest->getTermData($rhd_schools_taxonomy_uuid, $r[$i]['uuid'], 'org_unit', $r_data);
				if($data['student']['school_org_num'] == $r_data['org_unit'])
				{
					$school_name = $r[$i]['term'];
				}
			}
		}
		$data['student']['school_name'] = $school_name;
		
		//create a new item
		$item_bean["collection"]["uuid"] = $collection_id;
		//load root node
		$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => '<xml><item></item></xml>'));
		
		$thesis_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/thesis/title");
		$this->xmlwrapper->createTextNode($thesis_name, $data['student']['thesis_title']);
	
		$t_code = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/code");
		$this->xmlwrapper->createTextNode($t_code, $data['student']['topic_code']);
		
		$c_code = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/courses/course/code");
		$this->xmlwrapper->createTextNode($c_code, $data['student']['course_code']);
		
		$c_title = $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/courses/course/name");
		$this->xmlwrapper->createTextNode($c_title, $data['student']['course_title']);
	
		$c_school= $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/org_unit");
		$this->xmlwrapper->createTextNode($c_school, $data['student']['school_org_num']);
		
		$c_school_name= $this->xmlwrapper->createNodeFromXPath("/xml/item/curriculum/topics/topic/org_unit_name");
		$this->xmlwrapper->createTextNode($c_school_name, $data['student']['school_name']);
		
		$sub_date = $this->xmlwrapper->createNodeFromXPath("/xml/item/thesis/expected_submission_date");
		$this->xmlwrapper->createTextNode($sub_date, $data['student']['est_submit_date']);
	
		$stu_fan = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/fan");
		$this->xmlwrapper->createTextNode($stu_fan, strtolower($data['student']['stu_fan']));
	
		$stu_id = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/id");
		$this->xmlwrapper->createTextNode($stu_id, $data['student']['stu_id']);
		
		$stu_f_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/first_names");
		$this->xmlwrapper->createTextNode($stu_f_name, $data['student']['given_name']);
		
		$stu_l_name = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/candidate/last_names");
		$this->xmlwrapper->createTextNode($stu_l_name, $data['student']['family_name']);
		
		$candidature = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature");
		$start_date = $this->xmlwrapper->createAttribute($candidature, "start_date");
		$start_date->nodeValue = $data['student']['start_date'];

		$res = $this->xmlwrapper->createAttribute($candidature, "residency_type");
		$res->nodeValue = $data['student']['residency'];
		
		$topic_status = $this->xmlwrapper->createAttribute($candidature, "status");
		$topic_status->nodeValue = $data['student']['topic_status'];
		
		$eftsl_total = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/eftsl/total");
		$this->xmlwrapper->createTextNode($eftsl_total, $data['student']['total_eftsl']);
       
	   	$eftsl_unreported = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/eftsl/unreported");
		$this->xmlwrapper->createTextNode($eftsl_unreported, $data['student']['unreported_eftsl']);
		
		$eftsl_reported = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/eftsl/reported");
		$this->xmlwrapper->createTextNode($eftsl_reported, $data['student']['reported_eftsl']);
		
		$load = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/load/FTE_type");
		$this->xmlwrapper->createTextNode($load, $data['student']['load']);
		
		$rtscheme = $this->xmlwrapper->createNodeFromXPath("/xml/item/candidature/RTScheme");
		$catrgory_node = $this->xmlwrapper->createNode($rtscheme, "category");
		$catrgory_node->nodeValue = $data['student']['liability_category'];
		
		$end_date = $this->xmlwrapper->createNode($rtscheme, "end_date");
		$end_date->nodeValue = $data['student']['fec_date'];
		
		$schol_details = $this->xmlwrapper->createNode($rtscheme, "schol_details");
		$schol_details->nodeValue = $data['student']['schol_details'];
		
		
		if(count($data['student']['supervisors']) > 0)
		{
			$tc_root = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/supervisors");
			foreach($data['student']['supervisors'] as $tc)
			{
				$tc_node = $this->xmlwrapper->createNode($tc_root, "supervisor");
				$node_role= $this->xmlwrapper->createNode($tc_node, "role");
				$node_fan = $this->xmlwrapper->createNode($tc_node, "fan");
				$node_title = $this->xmlwrapper->createNode($tc_node, "title");
				$node_first_name = $this->xmlwrapper->createNode($tc_node, "first_names");
				$node_last_name = $this->xmlwrapper->createNode($tc_node, "last_names");
				
				$node_role->nodeValue = $tc['supv_role']; 
				$node_fan->nodeValue = $tc['supv_fan'];
				$node_title->nodeValue = $tc['title'];
				$node_first_name->nodeValue = $tc['given_name'];
				$node_last_name->nodeValue = $tc['family_name'];
				
			}
		}
		
		/*$dis = '';
		if(strlen($data['student']['topic_code']))
		{
			$dis = strtoupper(substr($data['student']['topic_code'], 0, 4));
		}
		
		if($dis!= '')
		{
			$postgra = $this->cs_model->db_get_postgrd_coord($dis);
			$sys_workflow = $this->xmlwrapper->createNodeFromXPath("/xml/item/sys_workflow");
			$postgrd_coord = $this->xmlwrapper->createNode($sys_workflow, "postgraduate_coordinator");
			$postgrd_coord->nodeValue = $postgra[0]['fan'];
			
			$coords = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/coordinators");
			$coord = $this->xmlwrapper->createNode($coords, "coordinator");
			
			$coord_fan = $this->xmlwrapper->createNode($coord, "fan");
			$coord_fan->nodeValue = $postgra[0]['fan'];
			
			$coord_role = $this->xmlwrapper->createNode($coord, "role");
			$coord_role->nodeValue = $postgra[0]['role_name'];
			
			$coord_first_name = $this->xmlwrapper->createNode($coord, "first_names");
			$coord_first_name ->nodeValue = $postgra[0]['first_name'];
			
			$coord_last_name = $this->xmlwrapper->createNode($coord, "last_names");
			$coord_last_name ->nodeValue = $postgra[0]['last_name'];
		}*/
		
		
		$item_bean['metadata'] = $this->xmlwrapper->__toString();
		$success = $this->rexrest->createDraftItem($item_bean, $response1);

		if(!$success)
		{
			$this->logger_cs->error("Error: error to create draft item for fan - " .strtolower($_SERVER['PHP_AUTH_USER']) . '-' . $this->rexrest->error);
			$result['status'] = 'error';
			$result['error_info'] = $this->flexrest->error;
			echo json_encode($result);
			return;
		}
		
		if(!isset($response1['headers']['location']))
		{
			$this->logger_cs->error('createItem failed' . ', error: ' . 'No Location header in createItem response.');
			#$this->load->view('reading_listmgr/showerror_view', $errdata);
			$result['status'] = 'error';
			$result['error_info'] = $errdata['message'];
			echo json_encode($result);
			return;
		}
		
		$location = $response1['headers']['location'];
		$uuid = substr($location, strpos($location, 'item')+5, 36);
		$version = substr($location, strpos($location, 'item')+42, (strlen($location)-1-(strpos($location, 'item')+42)));

		$result['status'] = "success";
		$result['uuid'] = $uuid;
		$result['version'] = $version;
		$result['token'] = $this->generateToken(strtolower($_SERVER['PHP_AUTH_USER']));
		
		echo json_encode($result);
		
		#$this->load->view('cs/milestone_modal_view', $data);
		
	}


	public function candidate_view($id)
	{
		$data = array();
		$data['heading'] = 'Student Dashboard';
			
		/************************************/
		/**** My Details panel ****/
		/************************************/
		//get student info from the database
		$students = $this->cs_model->db_get_rhd_student_info($id);
		if($students == 0 || !$students)
		{
			$this->logger_cs->error("Error: fan-".$_SERVER['PHP_AUTH_USER'] . " invalid student ID input: " .$id);
			$errdata['message'] = 'Invalid ID input';
			$errdata['heading'] = "Error";
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
		
		
		$data['student'] = $students[0];
		$data['student']['postgra_coord'] = '';
		
		$dis = '';
		if(strlen($data['student']['topic_code']))
		{
			$dis = strtoupper(substr($data['student']['topic_code'], 0, 4));
		}
		
		if($dis!= '')
		{
			$data['student']['postgra_coord'] = $this->cs_model->db_get_postgrd_coord($dis);
		}
		
		//get outh id and passwords from config/rex/$config['oauth_clients'] => 'rhd'
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		
		$ci =& get_instance();
		$ci->load->config('rex');
		
		$rhd_schools_taxonomy_uuid = $ci->config->item('rhd_schools_taxonomy_uuid');
		$collection_id = $ci->config->item('milestone_collection');
		$examination_id = $ci->config->item('examination_collection');
		
		$this->load->helper('url');
		
		$success = $this->rexrest->processClientCredentialToken();
		
		//get school name from the taxonomy tool [Equella]
		$this->rexrest->getTerms($rhd_schools_taxonomy_uuid, '', $r);
		$school_name = '';
		
		for($i=0; $i < count($r); $i++)
		{
			if(isset($r[$i]['term']) && $r[$i]['term']!= '')
			{	
				$this->rexrest->getTermData($rhd_schools_taxonomy_uuid, $r[$i]['uuid'], 'org_unit', $r_data);
				if($data['student']['school_org_num'] == $r_data['org_unit'])
				{
					$school_name = $r[$i]['term'];
				}
			}
		}
		$data['student']['school_name'] = $school_name;
		
		//get student's supervisor info
		$supervisors = $this->cs_model->db_get_stu_sup_info($id);
		
		if(count($supervisors) > 0)
		{
			for($index = 0; $index < count($supervisors); $index++)
			{
				$sup_info = $this->cs_model->db_get_all_sup_info($supervisors[$index]['rhd_supv_id']);
				if(isset($sup_info[0]))
				{
					$supervisors[$index]['supv_staff_id'] = $sup_info[0]['supv_staff_id'];
					$supervisors[$index]['supv_fan'] = $sup_info[0]['supv_fan'];
					$supervisors[$index]['title'] = $sup_info[0]['title'];
					$supervisors[$index]['given_name'] = $sup_info[0]['given_name'];
					$supervisors[$index]['family_name'] = $sup_info[0]['family_name'];
					$supervisors[$index]['email'] = $sup_info[0]['email'];
				}
			}	
		}
		
		$data['student']['supervisors'] = $supervisors;
		
		
		/************************************/
		/**** Myilestone panel****/
		/************************************/
		//TODO::: search in the database what current milestone the student is at
		
		$stu_milestones = $this->cs_model->db_get_stu_milestone($id);
		$milestones = array();
		$current_milestone = '';
		$current_milestone_code = '';
		$current_milestone_due_date = '';		
	    if($stu_milestones != 0)
		{
			$current_milestone = $stu_milestones[0]['milestone_name'];
			$current_milestone_code = $stu_milestones[0]['milestone_code'];
			$current_milestone_due_date = $stu_milestones[0]['due_date'];
			if(count($stu_milestones) > 1)
			{
				for($i=0; $i<count($stu_milestones); $i++)
				{
					$due_date = $stu_milestones[$i]['due_date'];
					
					if(strtotime($due_date) < strtotime($current_milestone_due_date))
					{
						$current_milestone = $stu_milestones[$i]['milestone_name'];
						$current_milestone_code = $stu_milestones[$i]['milestone_code'];
						$current_milestone_due_date = $due_date;
						
					}
					
				}
			}
		}
		$milestones['current_milestone'] = $current_milestone;
		$milestones['current_milestone_code'] = $current_milestone_code;
		$milestones['current_milestone_due_date'] = $current_milestone_due_date;
		
		//$milestones['current_milestone_code'] = '1000';
		//$milestones['current_milestone_due_date'] = $current_milestone_due_date;
		/*echo '<pre>';
		print_r($milestones);
		echo '</pre>';*/
		

		//search in Equella to get student existed milestones
		$where = "/xml/item/people/candidate/fan='".$data['student']['stu_fan']."' OR ";
		$where = $where . "/xml/item/people/candidate/fan='" . $data['student']['stu_fan'] ."'";
		
		/*if($current_milestone_code != '')
		{
			$where = $where . "/xml/item/progression/milestone/@name_id='" . $current_milestone_code."'";
			
		}
		else if($current_milestone != '')
		{
			$where = $where . "/xml/item/progression/milestone/@name='" . $current_milestone."'";
		}*/
		
		$where = urlencode($where);

		$searchsuccess = $this->rexrest->search($response, '', $collection_id, $where, 0, 50, 'modified', false, 'all', true);
		
		/*echo '<pre>';
		print_r($response);
		echo '</pre>';*/
		
		if(isset($response['available']) && $response['available'] > 0)
		{
			$i = 0;
			foreach($response['results'] as $result)
			{
				$i++;
				$xmlwrapper_name = 'xmlwrapper_stu'.$i;
				$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$result['metadata']), $xmlwrapper_name);
				$milestone_name = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name');
				$milestone_id = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name_id');
				$stu_sign_off = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/evidence_finalised');
				
				$milestones['milestone'][$i]['name'] = $milestone_name;
				$milestones['milestone'][$i]['name_id'] = $milestone_id ;
				$milestones['milestone'][$i]['uuid'] = $result['uuid'];
				$milestones['milestone'][$i]['version'] = $result['version'];
				$milestones['milestone'][$i]['status'] = $result['status'];
				$milestones['milestone'][$i]['stu_sign_off'] = $stu_sign_off;
				$milestones['milestone'][$i]['modifiedDate'] = $result['modifiedDate'];
				
				
				/*if($milestone_name == 'Interim Candidature Review')
				{
					$milestone_id = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name_id');
					#$milestones[$milestone_id][$milestone_name]= $milestone_id;
					$milestones[$milestone_id][$milestone_name]['uuid'] = $result['uuid'];
					$milestones[$milestone_id][$milestone_name]['version'] = $result['version'];
					$milestones[$milestone_id][$milestone_name]['name'] = $result['name'];
					$milestones[$milestone_id][$milestone_name]['status'] = $result['status'];
					$milestones[$milestone_id][$milestone_name]['modifiedDate'] = $result['modifiedDate'];
					
				}
				else
				{
					$milestones[$milestone_name]['uuid'] = $result['uuid'];
					$milestones[$milestone_name]['version'] = $result['version'];
					$milestones[$milestone_name]['name'] = $result['name'];
					$milestones[$milestone_name]['status'] = $result['status'];
					$milestones[$milestone_name]['modifiedDate'] = $result['modifiedDate'];
				}*/
			}
		}
		
		
		$data['milestones'] = $milestones;
		
		
		/************************************/
		/**** Examination panel****/
		/************************************/
		/*$where = "/xml/item/people/candidate/fan='".$data['student']['stu_fan']."' OR ";
		$where = $where . "/xml/item/people/candidate/fan='" . $data['student']['stu_fan'] ."'";
		$where = urlencode($where);*/
		//search in Equella to get student existed examination thesis
		$s_success = $this->rexrest->search($r, '', $examination_id, $where, 0, 1, 'modified', false, 'all', true);
		if(isset($r['available']) && $r['available'] > 0)
		{
			$xmlwrapper_name = 'xmlwrapper_exam';
			$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$r['results'][0]['metadata']), $xmlwrapper_name);
			$thesis_name = $this->$xmlwrapper_name->nodeValue('/xml/item/thesis/title');
			
			$data['examination']['status'] = $r['results'][0]['status'];
			$data['examination']['uuid'] = $r['results'][0]['uuid'];
			$data['examination']['version'] = $r['results'][0]['version'];
			$data['examination']['thesis_title'] = $thesis_name;
			$data['examination']['modifiedDate'] = $r['results'][0]['modifiedDate'];
		}
		else
		{
			$data['examination'] = '';
		}
		
		
		/*if($_SERVER['PHP_AUTH_USER'] == 'qi0043')
		{
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		}*/
		
		$this->load->view('cs/candidate_view', $data);
		
	}
	/** Not in use***/
	/** Called from view/student_view modal***/
	/***** return student and student's supervisors info back to to the view modal *****/
	public function returnStuInfo($mile_stone, $id, $milestone_id = '1')
	{
		$data = array();
		$data['milestone_id'] = $milestone_id;
		$students = $this->cs_model->db_get_rhd_student_info($id);
		$data['student'] = $students[0];
		$supervisors = $this->cs_model->db_get_stu_sup_info($id);
		
		if(count($supervisors) > 0)
		{
			for($index = 0; $index < count($supervisors); $index++)
			{
				$sup_info = $this->cs_model->db_get_all_sup_info($supervisors[$index]['rhd_supv_id']);
				
				$supervisors[$index]['supv_staff_id'] = $sup_info[0]['supv_staff_id'];
				$supervisors[$index]['supv_fan'] = $sup_info[0]['supv_fan'];
				$supervisors[$index]['title'] = $sup_info[0]['title'];
				$supervisors[$index]['given_name'] = $sup_info[0]['given_name'];
				$supervisors[$index]['family_name'] = $sup_info[0]['family_name'];
				$supervisors[$index]['email'] = $sup_info[0]['email'];
			}	
		}
		
		$data['student']['supervisors'] = $supervisors;
		$data['milestone'] = '';
		switch ($mile_stone)
		{
			case 'coc':
				$data['milestone'] = 'Confirmation of Candidature';
				break;
			case 'mcr':
				$data['milestone'] = 'Mid-Candidature Review';
				break;
			case 'ftr':
				$data['milestone'] = 'Final Thesis Review';
				break;
			case 'ir':
				$data['milestone'] = 'Interim Candidature Review';
				break;
		}
		
		
		/***********************************
		 get school name from the taxonomy by org_num (org_unit)
		************************************/
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		$ci =& get_instance();
		$ci->load->config('rex');
		$rhd_schools_taxonomy_uuid = $ci->config->item('rhd_schools_taxonomy_uuid');
		$success = $this->rexrest->processClientCredentialToken();
		$this->rexrest->getTerms($rhd_schools_taxonomy_uuid, '', $r);
		$school_name = '';
		
		for($i=0; $i < count($r); $i++)
		{
			if(isset($r[$i]['term']) && $r[$i]['term']!= '')
			{	
				$this->rexrest->getTermData($rhd_schools_taxonomy_uuid, $r[$i]['uuid'], 'org_unit', $r_data);
				if($data['student']['school_org_num'] == $r_data['org_unit'])
				{
					$school_name = $r[$i]['term'];
				}
			}
		}
		$data['student']['school_name'] = $school_name;
		
		/*echo '<pre>';
		print_r($data);
		echo '</pre>';*/
		
		$this->load->view('cs/milestone_modal_view', $data);
		
	}
	
	
	/**** Call from view/student_view  .milestone-link onClick event ********/
	/**** redirect page to REX with single sign on (token)     *******/
    public function rexLink($item_uuid='missed', $version='missed')
    {
        $this->load->helper('url');
        $ci =& get_instance();
        $ci->load->config('rex');
        $institute_url = $ci->config->item('institute_url');
        $rex_link = $institute_url . "items/" . $item_uuid. "/" . $version . "/";
        $token = $this->generateToken($_SERVER['PHP_AUTH_USER']);
		#$token = $this->generateToken('team0003');
        $rex_link .= '?token=' . $token;
        redirect($rex_link);
    }
	

	
	/*************** Private Functions *************************/
	/**
	get student and student's supervisors info from the database
	@param $id  id no from the tbl_rhd_students table
	@return array with student and supervisors info
	**/
	private function getStuInfo($id, $stu_fan)
	{
		$data = array();
		
		$students = $this->cs_model->db_get_rhd_student_info($id);
		if($students == 0)
		{
			$this->logger_cs->error("Error: invalid student id input by fan: " . $_SERVER['PHP_AUTH_USER']);
			$errdata['heading'] = "Warning";
			$errdata['message'] = 'Invalid URL';
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
		
		$data['student'] = $students[0];
		
		
		$ci =& get_instance();	
		$ci->load->config('rex');
		$collection_id = $ci->config->item('milestone_collection');
		$examination_id = $ci->config->item('examination_collection');
		$this->load->helper('url');
		//get outh id and passwords from config/rex/$config['oauth_clients'] => 'rhd'
		$oauth = array('oauth_client_config_name' => 'rhd'); 
		
		//load rexrest from the library with the $oauth
		$this->load->library('rexrest/rexrest', $oauth); 

		$success = $this->rexrest->processClientCredentialToken();
		
		if(!$success)
		{
			$errdata['heading'] = "Error";
			$errdata['message'] = $this->rexrest->error;
			$this->load->view('test/showerror_view', $errdata);
			return;
		}
		
		$where = "/xml/item/people/candidate/fan='".$stu_fan."' OR ";
		$where = $where . "/xml/item/people/candidate/fan='" . $stu_fan ."'";
		$where = urlencode($where);

		$searchsuccess = $this->rexrest->search($response, '', $collection_id, $where, 0, 50, 'modified', false, 'all', true);
		/*echo '<pre>';
		print_r($response);
		echo '</pre>';*/
		
		if($response['available'] > 0)
		{
			$milestones = array();
			$i = 0;
			foreach($response['results'] as $result)
			{
				$i++;
				$xmlwrapper_name = 'xmlwrapper_stu'.$i;
				$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$result['metadata']), $xmlwrapper_name);
				$milestone_name = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name');
				if($milestone_name == 'Interim Milestone')
				{
					$milestone_id = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name_id');
					#$milestones[$milestone_id][$milestone_name]= $milestone_id;
					$milestones[$milestone_id][$milestone_name]['uuid'] = $result['uuid'];
					$milestones[$milestone_id][$milestone_name]['version'] = $result['version'];
					$milestones[$milestone_id][$milestone_name]['name'] = $result['name'];
					$milestones[$milestone_id][$milestone_name]['status'] = $result['status'];
					$milestones[$milestone_id][$milestone_name]['modifiedDate'] = $result['modifiedDate'];
					
				}
				else
				{
					$milestones[$milestone_name]['uuid'] = $result['uuid'];
					$milestones[$milestone_name]['version'] = $result['version'];
					$milestones[$milestone_name]['name'] = $result['name'];
					$milestones[$milestone_name]['status'] = $result['status'];
					$milestones[$milestone_name]['modifiedDate'] = $result['modifiedDate'];
				}
			}
			
			$data['milestones'] = $milestones;
		}
		
		/*echo '<pre>';
		print_r($data);
		echo '</pre>';*/
		
		$s_success = $this->rexrest->search($r, '', $examination_id, $where, 0, 1, 'modified', false, 'all', true);
		if($r['available'] > 0)
		{
			$xmlwrapper_name = 'xmlwrapper_exam';
			$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$r['results'][0]['metadata']), $xmlwrapper_name);
			$thesis_name = $this->$xmlwrapper_name->nodeValue('/xml/item/thesis/title');
			
			$data['examination']['status'] = $r['results'][0]['status'];
			$data['examination']['uuid'] = $r['results'][0]['uuid'];
			$data['examination']['version'] = $r['results'][0]['version'];
			$data['examination']['thesis_title'] = $thesis_name;
			$data['examination']['modifiedDate'] = $r['results'][0]['modifiedDate'];
		}
		else
		{
			$data['examination'] = '';
		}
		return $data;
		
	}

	private function getSupStuInfo($id)
	{
		$students = $this->cs_model->db_get_sup_info($id);
		if($students > 0)
		{
			$index = 0;
			$stu_info = '';
			foreach($students as $stu)
			{
				$index++;
				$stu_id = $stu['rhd_stu_id'];
				$stu_info = $this->cs_model->db_get_rhd_student_info($stu_id);
				$students[$index-1]['details'] = $stu_info[0];
			}
			unset($stu_info);
		}
	
		return $students;
	}
	
	private function getSupStuDetails($id)
	{
		$students = $this->cs_model->db_get_sup_details($id);
		return $students;
	}
	
	private function getItem($uuid, $version)
	{
		$ci = & get_instance();
		$ci->load->config('rex');
		
		$this->load->helper('url');
		//get outh id and passwords from config/rex/$config['oauth_clients'] => 'rhd'
		$oauth = array('oauth_client_config_name' => 'rhd'); 
		
		//load rexrest from the library with the $oauth
		$this->load->library('rexrest/rexrest', $oauth); 

		$success = $this->rexrest->processClientCredentialToken();
		
		if(!$success)
		{
			$errdata['heading'] = "Error";
			$errdata['message'] = $this->rexrest->error;
			$this->load->view('rhd/showerror_view', $errdata);
			return;
		}
		
		//call function getItemAll from the rexrest/rexrest and return the array
		$success = $this->rexrest->getItemAll($uuid, $version, $response);
		if(!$success)
		{
			$errdata['heading'] = "Error";
			$errdata['message'] = $this->rexrest->error;
			$this->load->view('rhd/showerror_view', $errdata);
			return;
		}
		
		return $response;
	}
	
	private function validate_params($uuid, $version)
	{
		if(strcmp($uuid, 'missed')==0 || strlen($uuid) != 36)
		{
			$this->logger_test->error("Error: Invalid UUID input.");
			$errdata['message'] = 'Invalid UUID input';
			$errdata['heading'] = "Input error";
			$this->load->view('test/showerror_view', $errdata);
			$this->output->_display();
			return false;
		}
		
		if(strcmp($version, 'missed')==0 || !is_numeric($version))
		{
			$this->logger_test->error("Error: Invalid Version No input.");
			$errdata['message'] = 'Invalid Version No. input';
			$errdata['heading'] = "Input error";
			$this->load->view('test/showerror_view', $errdata);
			$this->output->_display();
			return false;
		}
		
		return true;
	}

    private function generateToken_by_shared_secret()
    {
        $ci =& get_instance();
        $ci->load->config('rex');
        $username = $ci->config->item('rhd_shared_secret_username');
        $sharedSecretId = $ci->config->item('rhd_shared_secret_id');
        $sharedSecretValue = $ci->config->item('rhd_shared_secret_value');

        $time = mktime() . '000';
        return urlencode ($username) . ':' . urlencode($sharedSecretId) . ':' .  $time . ':' .
            urlencode(base64_encode (pack ('H*', md5 ($username . $sharedSecretId . $time . $sharedSecretValue))));

    }

    private function generateToken($username)
    {
        $ci =& get_instance();
        $ci->load->config('rex');

        $sharedSecretId = $ci->config->item('rhd_shared_secret_id');
        $sharedSecretValue = $ci->config->item('rhd_shared_secret_value');

        $time = mktime() . '000';

        return urlencode ($username) . ':' . urlencode($sharedSecretId) . ':' .  $time . ':' .
            urlencode(base64_encode (pack ('H*', md5 ($username . $sharedSecretId . $time . $sharedSecretValue))));

    }

}
?>