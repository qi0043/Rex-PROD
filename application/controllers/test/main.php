<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller 
{
	 
	 private $logger_test;
	 private $soapusername;
     private $soappassword;
     private $soapparams;

	 public function __construct()
     {
		parent::__construct();
		
		#session_start();

		$ci =& get_instance();
		$ci->load->config('rex');
		$this->load->helper('url');
		
		//load database
		$this->load->model('test/connect_model');
		
		//equella soap login
		$this->soapusername = $ci->config->item('soap_activation_username');
        $this->soappassword = $ci->config->item('soap_activation_password');
        $this->soapparams = array('username'=>$this->soapusername, 'password'=>$this->soappassword);  
		
		//define logger folder application/logs/testLog
		$loggings = $ci->config->item('testlog');
		$this->load->library('logging/logging',$loggings); //call loggings from the lib
        $this->logger_test = $this->logging->get_logger('test'); //get test folder
		
	 }
	 
	 //view load test
	 public function index()
	 {
		 $this->load->view('/test/welcome');
	 }
	 
	 //database connection test
	 public function db_connect()
	 {
		$students = $this->connect_model->db_get_rhd_student_info();
		echo '<pre>';
		print_r($students);
		echo '</pre>';
		exit;
	 }
	 
	//equella soap call and rest call test
	public function rest($uuid, $version)
	{
		if(!$this->validate_params($uuid,$version))
			exit();
		if(!isset($_SERVER['REMOTE_USER']))
		{
			$this->logger_test->error("Error: REMOTE_USER not set.");
			$errdata['message'] = 'Unable to get username';
			$errdata['heading'] = "Internal error";
			$this->load->view('test/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
		else
		{
			//Test SOAP calls
			$fan = strtolower($_SERVER['REMOTE_USER']);
			$groups = $this->getUserGroups($fan); //return all group uuids and group names in a string
			echo 'Logged In User Groups: ';
			echo $groups;
			
			
			//TEST REST calls
			$item_detail = $this->getItem($uuid, $version);
			
			echo '<pre>';
			echo 'Item Details: ';
			print_r($item_detail);
			echo '</pre>';
			exit();
			
		}
	 }
	 
	/*************** Private Functions *************************/
	private function getUserGroups($userUuid)
	{ 
		$this->load->library('rexsoap/rexsoap',$this->soapparams);
		if(!$this->rexsoap->success)
		{
			$errdata['message'] = $this->rexsoap->error_info;
			$errdata['heading'] = "Internal error";
			$this->load->view('test/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
	
	
		//get user group
		$groups = $this->rexsoap->getGroupsByUser($userUuid);
		if(!$this->rexsoap->success)
		{
			$this->logger_test->error($this->rexsoap->error_info);
			$errdata['message'] = $this->rexsoap->error_info;
			$errdata['heading'] = "Internal error";
			$this->load->view('test/showerror_view', $errdata);
			$this->output->_display();
			exit();
		}
		
		return $groups;
		
		/*$ci = & get_instance();
		$ci->load->config('rex');
		
		$usergrp_rhd_moderator = $ci -> config ->item('rhd_moderator_group'); //get rhd moderator group uuid
		$usergrp_rhd_contributor = $ci -> config->item('rhd_thesis_contributor_group'); //get rhd contributor group uuid
		
		$user_role = '';
		
		if(strpos($groups, $usergrp_rhd_moderator) !== false)
		{
		if(strpos($groups, $usergrp_rhd_contributor) !== false)
		{
		$_SESSION['rhd_privilege'] = 'mod&con';
		$user_role = 'mod&con';
		}
		else
		{
		$_SESSION['rhd_privilege'] = 'moderator';
		$user_role = 'moderator';
		}
		}	
		else
		{
		$_SESSION['rhd_privilege'] = 'contributor';
		$user_role = 'contributor';
		}
		
		return $user_role;*/
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
			$this->load->view('test/showerror_view', $errdata);
			return;
		}
		
		//call function getItemAll from the rexrest/rexrest and return the array
		$success = $this->rexrest->getItemAll($uuid, $version, $response);
		if(!$success)
		{
			$errdata['heading'] = "Error";
			$errdata['message'] = $this->rexrest->error;
			$this->load->view('test/showerror_view', $errdata);
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

}
?>