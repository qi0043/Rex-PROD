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

		#check down time notice
		$down_notice = false;
		$down_notice = $this->cs_model->db_chk_notice();
		if($down_notice != false)
		{
			#$this->error_info($down_notice['message']);
			if ($down_notice['message'] == '')
				$down_notice['message'] = 'RHD Research Excellence is temporarily unavailable.';
			#echo $down_notice['message'];
			$errdata['message'] = $down_notice['message'];
			$errdata['heading'] = "Notice";
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit;
		}


		//define logger folder application/logs/cs
		$loggings = $ci->config->item('log');
		$this->load->library('logging/logging',$loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder

		//load sessions
		$this->load->library('session');
        $this->load->library('permission/permission');
     }

	public function index()
	{
		//get user fan from the server
		if(!isset($_SERVER['PHP_AUTH_USER']))
		{
			$this->logger_cs->error("Error: REMOTE_USER not set.");
			$errdata['message'] = 'Unable to get username';
			$errdata['heading'] = "Internal error";
			$this->load->view('test/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}

		//logged user fan
		$fan = strtolower($_SERVER['PHP_AUTH_USER']);
		$data = array();

		if ($fan == 'couc0005') {

			/*
			echo "<pre>";
			print_r($_SERVER);
			echo "</pre>";
			exit;
			*/
		}

		

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
		/*echo '<pre>';
		print_r($students);
		echo '</pre>';
		exit;*/
		//check if the login user is a supervisor
		//return 0 if the login user fan is not found in the supervisors list
		$sups = $this->cs_model->db_match_sup_fan($fan);
		$coordinators = $this->cs_model->db_get_match_coordinator_fan($fan);
        $is_administrator = $this->permission->get_rex_permission($_SERVER['PHP_AUTH_USER']);


		if ($students && $sups && $coordinators) {
            $str = '';
            $str .= $fan;
            $user_data = array('user_role' => 'student_supervisor_coordinator', 'fan' => $fan, 'stu_fan' => $str, 'sup_id' =>$sups[0]['id'], 'stu_id' =>$students[0]['id'], 'coordinator_fan' => $coordinators[0]['fan'], 'admin'=>$is_administrator);
            $this->session->set_userdata($user_data);
            $data['heading'] = 'My Dashboard';
            $this->load->view('cs/stu_sup_coord_view', $data);
        }
        else if($students && $sups) //login user is a student and a supervisor

		{
			//store user role and fan in the session
			$str = '';
			$str .= $fan;

			$user_data = array('user_role' => 'rhd_stu_and_sup', 'fan' => $fan, 'stu_fan' => $str, 'supv_fan' => $sups[0]['supv_fan'], 'sup_id' =>$sups[0]['id'], 'stu_id' =>$students[0]['id'], 'admin'=>$is_administrator);
			//$user_data = array('user_role' => 'rhd_sup', 'fan' => $fan);

			$this->session->set_userdata($user_data);
			$data['heading'] = 'My Dashboard';
			$this->load->view('cs/stu_sup_view', $data);
		}
		else if ($sups && $coordinators) {
            $user_data = array('user_role' => 'supervisor_coordinator', 'fan' => $fan, 'supv_fan' => $sups[0]['supv_fan'], 'sup_id' =>$sups[0]['id'], 'coordinator_fan' => $coordinators[0]['fan'], 'admin'=>$is_administrator);
            $this->session->set_userdata($user_data);
            $this->load->view('cs/sup_coordinator_view', $data);
        }
        else if ($students && $coordinators) {
		    log_message('error', $coordinators[0]['id']);
            $user_data = array('user_role' => 'student_coordinator', 'fan' => $fan, 'stu_id' =>$students[0]['id'], 'coordinator_fan' => $coordinators[0]['fan'], 'admin'=>$is_administrator);
            $this->session->set_userdata($user_data);
            $this->load->view('cs/stu_coord_view', $data);
        }
        else if ($coordinators) {
            $user_data = array('user_role' => 'coordinator', 'fan' => $fan, 'admin'=>$is_administrator);
            $this->session->set_userdata($user_data);
            $this->coordinator($fan);
        }
		else if($sups) //login user is a supervisor
		{
			//store user role and fan in the session
			$user_data = array('user_role' => 'rhd_sup', 'fan' => $fan, 'admin'=>$is_administrator);
			$this->session->set_userdata($user_data);

			$this->supervisor_view($sups[0]['supv_fan']);
		}
		else if($students) //login user is a student
		{
			//store user role and fan in the session
			$str = '';
			$str.= $fan;
			$user_data = array('user_role' => 'rhd_stu', 'fan' =>$fan, 'stu_fan' => $str, 'admin'=>$is_administrator);
			$this->session->set_userdata($user_data);

			//$this->candidate_view($students[0]['id'], $fan);
			$this->candidate_view($students[0]['stu_fan']);
		}
		else // login user is neither a student nor a supervisor
		{
			$user_data = array('user_role' => 'unauthorized', 'fan' =>$fan, 'admin'=>$is_administrator);
			$this->session->set_userdata($user_data);
		    $this->logger_cs->error("Error: Unauthorised User Logged In: fan - " .$fan);
			$errdata['message'] = 'Sorry, you do not have permission to view this page';
			$errdata['heading'] = "ERROR";
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

	public function supervisor_view($supv_fan)
	{
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);

		$ci =& get_instance();
		$ci->load->config('rex');

		$collection_id = $ci->config->item('milestone_collection');
		$examination_id = $ci->config->item('examination_collection');

		$this->load->helper('url');

		$success = $this->rexrest->processClientCredentialToken();


		$sup_info = $this->cs_model->db_get_all_sup_info_by_fan($supv_fan);
		if($sup_info == 0)
		{
			$this->logger_cs->error("Error: invalid supervisor id input by fan: " . $_SERVER['PHP_AUTH_USER']);
			$errdata['heading'] = "Warning";
			$errdata['message'] = 'Invalid URL';
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}

        $fan = $sup_info[0]['supv_fan'];
        $this->check_permission($fan);
        $id = $sup_info[0]['id'];

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
        $data['current_view'] = 'Supervisor View';

		/************************************/
		/**** Milestones****/
		/************************************/

		if(isset($data['supervisor']['students'][0]))
		{
			for($i = 0; $i < count($data['supervisor']['students']); $i++)
			{
				$stu_fan = isset($data['supervisor']['students'][$i]['stu_fan']) ? $data['supervisor']['students'][$i]['stu_fan'] : '';
				$stu_id = isset($data['supervisor']['students'][$i]['rhd_stu_id']) ? $data['supervisor']['students'][$i]['rhd_stu_id'] : '';
				if($stu_fan != '' && $stu_id != '')
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
							if($result['status']!= 'deleted')
							{
								$j++;
								$xmlwrapper_name = 'xmlwrapper_stu_milestone'.$i. '_'.$j;
								$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$result['metadata']), $xmlwrapper_name);
								$milestone_name = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name');
								$milestone_id = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name_id');
								$stu_sign_off = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/evidence_finalised');
                                $due_date = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@due_date');

								$milestones['milestone'][$j]['modifiedDate'] = $result['modifiedDate'];
								$milestones['milestone'][$j]['name'] = $milestone_name;
								$milestones['milestone'][$j]['name_id'] = $milestone_id ;
								$milestones['milestone'][$j]['uuid'] = $result['uuid'];
								$milestones['milestone'][$j]['version'] = $result['version'];
								$milestones['milestone'][$j]['status'] = $result['status'];
								$milestones['milestone'][$j]['modifiedDate'] = $result['modifiedDate'];
								$milestones['milestone'][$j]['due_date'] = $due_date;
								$milestones['milestone'][$j]['stu_sign_off'] = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/evidence_finalised');
							}
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
						if($r['results'][0]['status']!= 'deleted')
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
		$milestone_due_date = $_POST["milestone_due_date"];

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
				$data['milestone'] = 'Interim Review';
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

		$due_date= $this->xmlwrapper->createAttribute($milestone_node, "due_date");
		$due_date->nodeValue = $milestone_due_date;

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


	public function candidate_view($stu_fan)
	{
		$data = array();
		$data['heading'] = 'Student Dashboard';

		/************************************/
		/**** My Details panel ****/
		/************************************/
		//get student info from the database

		$students = $this->cs_model->db_get_rhd_student_info_by_fan($stu_fan);

		if($students == 0 || !$students)
		{
			$this->logger_cs->error("Error: fan-".$_SERVER['PHP_AUTH_USER'] . " invalid student ID input: " .$stu_fan);
			$errdata['message'] = 'Invalid ID input';
			$errdata['heading'] = "Error";
			$this->load->view('cs/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}

        $fan = $students[0]['stu_fan'];
        $this->check_permission($fan);
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
		$supervisors = $this->cs_model->db_get_stu_sup_info_by_fan($stu_fan);

		/*if(count($supervisors) > 0)
		{
			for($index = 0; $index < count($supervisors); $index++) {
                if (isset($supervisors[$index]['supv_fan'])) {
                    $sup_info = $this->cs_model->db_get_all_sup_info_by_fan($supervisors[$index]['supv_fan']);
                    if (isset($sup_info[0]) && !empty($sup_info[0])) {
                        $supervisors[$index]['supv_staff_id'] = $sup_info[0]['supv_staff_id'];
                        $supervisors[$index]['supv_fan'] = $sup_info[0]['supv_fan'];
                        $supervisors[$index]['title'] = $sup_info[0]['title'];
                        $supervisors[$index]['given_name'] = $sup_info[0]['given_name'];
                        $supervisors[$index]['family_name'] = $sup_info[0]['family_name'];
                        $supervisors[$index]['email'] = $sup_info[0]['email'];
                    }
                }
            }
		}*/

		$data['student']['supervisors'] = $supervisors;


		/************************************/
		/**** Myilestone panel****/
		/************************************/
		//TODO::: search in the database what current milestone the student is at

        $id = $data['student']['id'];
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
				if($result['status']!= 'deleted')
				{
					$i++;
					$xmlwrapper_name = 'xmlwrapper_stu'.$i;
					$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$result['metadata']), $xmlwrapper_name);
					$milestone_name = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name');
					$milestone_id = $this->$xmlwrapper_name->nodeValue('/xml/item/progression/milestone/@name_id');
					$stu_sign_off = $this->$xmlwrapper_name->nodeValue('/item/progression/milestone/evidence_finalised');

					$milestones['milestone'][$i]['name'] = $milestone_name;
					$milestones['milestone'][$i]['name_id'] = $milestone_id ;
					$milestones['milestone'][$i]['uuid'] = $result['uuid'];
					$milestones['milestone'][$i]['version'] = $result['version'];
					$milestones['milestone'][$i]['status'] = $result['status'];
					$milestones['milestone'][$i]['stu_sign_off'] = $stu_sign_off;
					$milestones['milestone'][$i]['modifiedDate'] = $result['modifiedDate'];
				}

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
			if($r['results'][0]['status']!= 'deleted')
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
		}
		else
		{
			$data['examination'] = '';
		}

		/*echo '<pre>';
		print_r($data);
		echo '</pre>';
		*/
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
        $rex_link .= '?token=' . $token;
        redirect($rex_link);
    }

    public function coordinator($fan) {
        $this->check_permission($fan);
        $students = $this->cs_model->db_get_postgrad_coordinator_students($fan);
        $coordinator_info = $this->cs_model->db_get_match_coordinator_fan($fan);
        $new_students =  array();
        foreach ($students as $student) {
            $stu_fan = $student['stu_fan'];
            $new_students[$stu_fan][] = $student;
        }

        foreach ($new_students as $current_fan=>$student){
            $course_code = array();
            $load = array();
            $start_date = array();
            $principal_supervisor_name = array();
            for ($j = 0; $j < count($new_students[$current_fan]); $j++){
                $course_code[] = $new_students[$current_fan][$j]['course_code'];
                $load[] = $new_students[$current_fan][$j]['load'];
                $start_date[] = $new_students[$current_fan][$j]['start_date'];
                $principal_supervisor_name[] = $new_students[$current_fan][$j]['principal_supervisor_name'];
            }

            if (count(array_count_values($course_code))>1){
                $new_students[$current_fan]['course_code_list'] = implode('<br/>', $course_code);
            }else{
                $new_students[$current_fan]['course_code_list'] = $course_code[0];
            }

            if (count(array_count_values($load))>1){
                $new_students[$current_fan]['load_list'] = implode('<br/>', $load);
            }else{
                $new_students[$current_fan]['load_list'] = $load[0];
            }

            if (count(array_count_values($start_date))>1){
                $new_students[$current_fan]['start_date_list'] = implode('<br/>', $start_date);
            }else{
                $new_students[$current_fan]['start_date_list'] = $start_date[0];
            }

            if (count(array_count_values($principal_supervisor_name))>1){
                $new_students[$current_fan]['principal_supervisor_name_list'] = implode('<br/>', $principal_supervisor_name);
            }else{
                $new_students[$current_fan]['principal_supervisor_name_list'] = $principal_supervisor_name[0];
            }

        }
        $data['students'] = $new_students;
        $data['coordinator'] = $coordinator_info[0];
        $data['current_view'] = 'Coordinator View';
        $this->load->view('cs/coordinator_view', $data);
    }

    public function logout() {
        $user_data = array('user_role', 'fan', 'stu_fan', 'sup_id');
        $this->session->unset_userdata($user_data);
        $this->session->sess_destroy();
        $this->load->view('cs/logout');
    }

    public function updateStudentDetail($fan){
        //get student info from the database
        $students = $this->cs_model->db_get_rhd_student_info_by_fan($fan);
        if($students == 0 || !$students)
        {
            $this->logger_cs->error("Error: fan-".$_SERVER['PHP_AUTH_USER'] . " invalid student ID input: " .$id);
            $errdata['message'] = 'Invalid ID input';
            $errdata['heading'] = "Error";
            $this->load->view('cs/showerror_view', $errdata);
            $this->output->_display();
            exit();
        }

        $studentInfo = $students[0];

        /* echo '<pre>';
		print_r($studentInfo);
		echo '</pre>';
        exit();  */

        //get outh id and passwords from config/rex/$config['oauth_clients'] => 'rhd'
        $oauth = array('oauth_client_config_name' => 'rhd');
        $this->load->library('rexrest/rexrest', $oauth);

        $ci =& get_instance();
        $ci->load->config('rex');
        $collection_id = $ci->config->item('milestone_collection');
        $this->load->helper('url');

        $success = $this->rexrest->processClientCredentialToken();
        if(!$success)
        {
            $result['status'] = "error";
            $result['error_info'] = $this->rexrest->error;
            $this->logger_cs->error("Error: error in rest - processClientCredentialToken for fan - " .strtolower($_SERVER['PHP_AUTH_USER']));
            echo json_encode($result);
            return;
        }

        //search in Equella to get student existed milestones
        $where = "/xml/item/people/candidate/fan='".$fan."' AND ";
        $where = $where . "/xml/item/@itemstatus =" . "'draft'";
        $where = urlencode($where);
        $searchsuccess = $this->rexrest->search($response, '', $collection_id, $where, 0, 50, 'modified', false, 'all', true);

        /* echo '<pre>';
		print_r($response);
		echo '</pre>';
		exit(); */

        $milestones = $response['results'];
        $countOfMilestones = $response['available'];

        /*echo '<pre>';
		print_r($milestones);
		echo '</pre>';*/

        /*echo '<pre>';
        print_r($countOfMilestones);
        echo '</pre>';*/

        /***********************************
        get school name from the taxonomy by org_num (org_unit)
         ************************************/
        $rhd_schools_taxonomy_uuid = $ci->config->item('rhd_schools_taxonomy_uuid');
        $this->rexrest->getTerms($rhd_schools_taxonomy_uuid, '', $r);
        $schoolName = '';
        for($i=0; $i < count($r); $i++)
        {
            if(isset($r[$i]['term']) && $r[$i]['term']!= '')
            {
                $this->rexrest->getTermData($rhd_schools_taxonomy_uuid, $r[$i]['uuid'], 'org_unit', $r_data);
                if($studentInfo['school_org_num'] == $r_data['org_unit'])
                {
                    $schoolName = $r[$i]['term'];
                }
            }
        }

        /*echo '<pre>';
		print_r('The name of organization is '.$schoolName);
		echo '</pre>';*/

        //$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => '<xml><item></item></xml>'));

        for($j = 0; $j < $countOfMilestones; $j++){

            $ms = $milestones[$j];

            /*echo "<pre>";
            print_r($ms);
            echo "</pre>";
            continue;*/

            $currentUUID = $ms['uuid'];
            $currentVersion = $ms['version'];
            $success = $this->rexrest->getItem($currentUUID, $currentVersion, $response);
            if(!$success)
            {
                $result['status'] = "error";
                $result['error_info'] = $this->rexrest->error;
                $this->logger_cs->error("Error: error in rest - fail to get the milestone item." );
                echo json_encode($result);
                return;
            }

            /*echo "<pre>";
            print_r($response);
            echo "</pre>";*/

            unset($response['headers']);
            $cms = $response;

            /*echo "<pre>";
            print_r($cms);
            echo "</pre>";
            continue;*/


            //$xmlwrapper_name = 'xmlwrapper_stu_milestone' . '_' . $j;

            //$xmlwrapper = 'xmlwrapper' . ' ' . $j;
            $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$cms['metadata']));

            $candidate_firstname = '/xml/item/people/candidate/first_names';
            $studentInfo['given_name'] = $studentInfo['given_name'];
            $this->xmlwrapper->setNodeValue($candidate_firstname, $studentInfo['given_name']);

            $candidate_lastname = '/xml/item/people/candidate/last_names';
            $this->xmlwrapper->setNodeValue($candidate_lastname, $studentInfo['family_name']);


            $student_id = '/xml/item/people/candidate/id';
            $this->xmlwrapper->setNodeValue($student_id, $studentInfo['stu_id']);

            $student_fan = '/xml/item/people/candidate/fan';
            $this->xmlwrapper->setNodeValue($student_fan, $studentInfo['stu_fan']);

            $degree_code = '/xml/item/curriculum/courses/course/code';
            $this->xmlwrapper->setNodeValue($degree_code, $studentInfo['course_code']);

            $degree_name = '/xml/item/curriculum/courses/course/name';
            $this->xmlwrapper->setNodeValue($degree_name, $studentInfo['course_title']);


            $topic_code = '/xml/item/curriculum/topics/topic/code';
            $this->xmlwrapper->setNodeValue($topic_code, $studentInfo['topic_code']);

            $thesis_title = '/xml/item/thesis/title';
            $this->xmlwrapper->setNodeValue($thesis_title, $studentInfo['thesis_title']);

            $school_code = '/xml/item/curriculum/topics/topic/org_unit';
            $this->xmlwrapper->setNodeValue($school_code, $studentInfo['school_org_num']);

            $school_name = '/xml/item/curriculum/topics/topic/org_unit_name';
            $this->xmlwrapper->setNodeValue($school_name, $schoolName);

            $scholarship_details = '/xml/item/candidature/RTScheme/schol_details';
            $this->xmlwrapper->setNodeValue($scholarship_details, $studentInfo['schol_details']);

            $ft_or_pt = '/xml/item/candidature/load/FTE_type';
            $this->xmlwrapper->setNodeValue($ft_or_pt, $studentInfo['load']);

            $eftsl_unreported = '/xml/item/candidature/eftsl/unreported';
            $this->xmlwrapper->setNodeValue($eftsl_unreported, $studentInfo['unreported_eftsl']);

            $eftsl_reported = '/xml/item/candidature/eftsl/reported';
            $this->xmlwrapper->setNodeValue($eftsl_reported, $studentInfo['reported_eftsl']);

            $eftsl = '/xml/item/candidature/eftsl/total';
            $this->xmlwrapper->setNodeValue($eftsl, $studentInfo['total_eftsl']);


            $RTScheme = '/xml/item/candidature/RTScheme/category';
            $this->xmlwrapper->setNodeValue($RTScheme, $studentInfo['liability_category']);


            $RTEndDate = '/xml/item/candidature/RTScheme/end_date';
            $this->xmlwrapper->setNodeValue($RTEndDate, $studentInfo['fec_date']);


            $start_date = '/xml/item/candidature/@start_date';
            $this->xmlwrapper->setNodeValue($start_date, $studentInfo['start_date']);

            $expect_submit_date = '/xml/item/thesis/expected_submission_date';
            $this->xmlwrapper->setNodeValue($expect_submit_date, $studentInfo['est_submit_date']);

            $residency_type = '/xml/item/candidature/@residency_type';
            $this->xmlwrapper->setNodeValue($residency_type, $studentInfo['residency']);

            $topic_status = '/xml/item/candidature/@status';
            $this->xmlwrapper->setNodeValue($topic_status, $studentInfo['topic_status']);


            //Call the restful function to update the milestone item
            $cms['metadata'] = $this->xmlwrapper->__toString();

            /*echo "<pre>";
            print_r($cms['metadata']);
            echo "</pre>";
            continue;*/

            $success = $this->rexrest->editItem($currentUUID, $currentVersion, $cms, $response3);
            if(!$success)
            {
                $result['status'] = "error";
                $result['error_info'] = $this->rexrest->error;
                $this->logger_cs->error("Error: error in rest - fail to edit the milestone item." );
                echo json_encode($result);
                return;
            }


            /*echo "<pre>";
            print_r($response3);
            echo "</pre>";
            continue;*/

        }

    }

    public function updateSupervisorDetail($fan){
        //get student id from the database
        $studentIdResult = $this->cs_model->db_match_stu_fan($fan);
        if($studentIdResult == 0 || !$studentIdResult)
        {
            $this->logger_cs->error("Error: fan-".$_SERVER['PHP_AUTH_USER'] . " invalid student ID input: " .$id);
            $errdata['message'] = 'Invalid ID input';
            $errdata['heading'] = "Error";
            $this->load->view('cs/showerror_view', $errdata);
            $this->output->_display();
            exit();
        }
        $studentId = $studentIdResult[0];

        //get supervisor information from the database
        $supervisors = $this->cs_model->db_get_stu_sup_info($studentId);
        if($supervisors == 0 || !$supervisors)
        {
            $this->logger_cs->error("Error: fan-".$_SERVER['PHP_AUTH_USER'] . " invalid student ID input: " .$id);
            $errdata['message'] = 'Invalid ID input';
            $errdata['heading'] = "Error";
            $this->load->view('cs/showerror_view', $errdata);
            $this->output->_display();
            exit();
        }

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

        /*echo '<pre>';
		print_r($supervisors);
		echo '</pre>';
        exit();*/

        //get outh id and passwords from config/rex/$config['oauth_clients'] => 'rhd'
        $oauth = array('oauth_client_config_name' => 'rhd');
        $this->load->library('rexrest/rexrest', $oauth);

        $ci =& get_instance();
        $ci->load->config('rex');
        $collection_id = $ci->config->item('milestone_collection');
        $this->load->helper('url');

        $success = $this->rexrest->processClientCredentialToken();
        if(!$success)
        {
            $result['status'] = "error";
            $result['error_info'] = $this->rexrest->error;
            $this->logger_cs->error("Error: error in rest - processClientCredentialToken for fan - " .strtolower($_SERVER['PHP_AUTH_USER']));
            echo json_encode($result);
            return;
        }

        //search in Equella to get student existed milestones
        $where = "/xml/item/people/candidate/fan='".$fan."' AND ";
        $where = $where . "/xml/item/@itemstatus =" . "'draft'";
        $where = urlencode($where);
        $searchsuccess = $this->rexrest->search($response, '', $collection_id, $where, 0, 50, 'modified', false, 'all', true);

        /*echo '<pre>';
		print_r($response);
		echo '</pre>';
		exit();*/

        $milestones = $response['results'];
        $countOfMilestones = $response['available'];

        /*echo '<pre>';
		print_r($milestones);
		echo '</pre>';
		exit();*/

        /*echo '<pre>';
        print_r($countOfMilestones);
        echo '</pre>';*/


        for($j = 0; $j < $countOfMilestones; $j++){

            $ms = $milestones[$j];

            /*echo "<pre>";
            print_r($ms);
            echo "</pre>";
            continue;*/

            $currentUUID = $ms['uuid'];
            $currentVersion = $ms['version'];
            $success = $this->rexrest->getItem($currentUUID, $currentVersion, $response);
            if(!$success)
            {
                $result['status'] = "error";
                $result['error_info'] = $this->rexrest->error;
                $this->logger_cs->error("Error: error in rest - fail to get the milestone item." );
                echo json_encode($result);
                return;
            }

            /*echo "<pre>";
            print_r($response);
            echo "</pre>";*/

            unset($response['headers']);
            $cms = $response;

            /*echo "<pre>";
            print_r($cms);
            echo "</pre>";
            continue;*/

            //$xmlwrapper = 'xmlwrapper' . ' ' . $j;
            $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$cms['metadata']));

            //delete the old metadata about the supervisors
            $supervisors_route = '/xml/item/people/supervisors';
            $this->xmlwrapper->deleteNodeFromXPath($supervisors_route);
            $tc_root = $this->xmlwrapper->createNodeFromXPath("/xml/item/people/supervisors");

            for($i = 0; $i<count($supervisors); $i++){

                $tc_node = $this->xmlwrapper->createNode($tc_root, "supervisor");
                $node_role= $this->xmlwrapper->createNode($tc_node, "role");
                $node_fan = $this->xmlwrapper->createNode($tc_node, "fan");
                $node_title = $this->xmlwrapper->createNode($tc_node, "title");
                $node_first_name = $this->xmlwrapper->createNode($tc_node, "first_names");
                $node_last_name = $this->xmlwrapper->createNode($tc_node, "last_names");
                //$node_display_name = $this->xmlwrapper->createNode($tc_node, "display_name");

                //$displayName = $supervisors[$i]['given_name'] . ' ' . $supervisors[$i]['family_name'];

                $node_role->nodeValue = $supervisors[$i]['supv_role'];
                $node_fan->nodeValue = $supervisors[$i]['supv_fan'];
                $node_title->nodeValue = $supervisors[$i]['title'];
                $node_first_name->nodeValue = $supervisors[$i]['given_name'];
                $node_last_name->nodeValue = $supervisors[$i]['family_name'];
                //$node_display_name->nodeValue = $displayName;

            }

            //Call the restful function to update the milestone item
            $cms['metadata'] = $this->xmlwrapper->__toString();

            /*echo "<pre>";
            print_r($cms['metadata']);
            echo "</pre>";
            continue;*/

            $success = $this->rexrest->editItem($currentUUID, $currentVersion, $cms, $response3);
            if(!$success)
            {
                $result['status'] = "error";
                $result['error_info'] = $this->rexrest->error;
                $this->logger_cs->error("Error: error in rest - fail to edit the milestone item." );
                echo json_encode($result);
                return;
            }


            /*echo "<pre>";
            print_r($response3);
            echo "</pre>";
            continue;*/

        }

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

	private function array_sort($array, $on, $order=SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}

			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
				break;
				case SORT_DESC:
					arsort($sortable_array);
				break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

		return $new_array;
	}

	private function getSupStuInfo($id)
	{
		$students = $this->cs_model->db_get_sup_info($id);
		/*if($_SERVER['PHP_AUTH_USER'] == 'qi0043')
		{
			$students = $this->cs_model->db_get_sup_details($id);

		    echo '<pre>';
			print_r($students);
			echo '</pre>';

			exit;
		}*/
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
				$students[$index-1]['family_name'] = $stu_info[0]['family_name'];
			}
			unset($stu_info);
		}
		//$new_stu = $this->array_sort($students,'family_name', SORT_ASC);

		if($_SERVER['PHP_AUTH_USER'] == 'qi0043')
		{

		    echo '<pre>';
			print_r($students);
			echo '</pre>';

			exit;
		}

		return $new_stu;
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

    private function check_permission($fan)
    {
        if (strcasecmp($fan, $_SERVER['PHP_AUTH_USER']) !== 0) {
            if(!$this->permission->success) {
                $errdata['message'] = 'Authentication error';
                $errdata['heading'] = "Internal error";
                $this->load->view('cs/showerror_view', $errdata);
                $this->output->_display();
                exit();
            }

            $is_authorised = $this->permission->get_rex_permission($_SERVER['PHP_AUTH_USER']);
            if (!$is_authorised) {
                log_message('error', 'Error: Access restricted for ' . $_SERVER['PHP_AUTH_USER']);
                $errdata['message'] = 'You are not authorised to view this page, if you think you should have this permission, please contact flex.help@flinders.edu.au';
                $errdata['heading'] = "Error Code 100";
                $this->load->view('cs/showerror_view', $errdata);
                $this->output->_display();
                exit();
            }
        }
    }
}
