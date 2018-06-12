<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('blti.php');

class Lti {
	var $secret;

	function __construct() {
                $ci =& get_instance();
                $ci->load->config('flo');
                $flo_lti_secret = $ci->config->item('flo_lti_secret');
		$this->secret = $flo_lti_secret;
		#session_start();
	}

	//
	// return information for given student
	//
    public function authenticate() {
    	global $_REQUEST;
		//All of the LTI Launch data gets passed through in $_REQUEST
		if (isset($_REQUEST['lti_message_type']) && $_REQUEST['lti_message_type'] == 'basic-lti-launch-request') {    //Is this an LTI Request?
			//Get BLTI class to do all the hard work (more explanation of these below)
			// - first parameter is the secret, which is required
			// - second parameter (defaults to true) tells BLTI whether to store the launch data is stored in the session ($_SESSION['_basic_lti_context'])
			// - third parameter (defaults to true) tells BLTI whether to redirect the user after successful validation
			$context = new BLTI($this->secret, true, false);

			//Deal with results from BLTI class
			if ($context->complete) {
				#echo 'Invalid access';
				#print_r($_SESSION);
				die();
				return false;
			} else if($context->valid) {
				// true if LTI request was verified
				//echo 'ok';
				//print_r($_SESSION['_basic_lti_context']);
				//die();
				return true;
			}
			#print_r($context);
			die('Invalid access');
		}
                #die("didn't recognise as lti");
		die("Invalid access");
		return true;
    }

}


