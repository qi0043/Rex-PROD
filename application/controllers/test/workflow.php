<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow extends CI_Controller 
{
	public function __construct()
    {
		parent::__construct();
	
	}
	 
	 /***********************************************************************
	  Redraft a rejected item and then submit for moderation
	  @uuid      item uuid
	  @version   verison no
	 ************************************************************************/
	 public function redraftAndModMilestone($uuid='missed', $version='missed')
	 {
		$this->output->enable_profiler(TRUE);
		session_start();
	    ignore_user_abort(true);
		// Set your timelimit to a length long enough for your script to run, 
		// but not so long it will bog down your server in case multiple versions run 
		// or this script get's in an endless loop.
		$content = 'good';
		$len = strlen($content);             
		header('Content-Type: text/html; charset=UTF-8');
		header('Content-Encoding: none;');
		header('Connection: close');         // Tell the client to close connection
		header("Content-Length: $len");      // Close connection after $size characters
		echo $content;                       // Output content
		ob_flush();
        flush();                             // Force php-output-cache to flush to rex.
                                             
		ob_end_flush();                       
										 
        #make sure sam work flow completely finishes
		sleep(5);

		$ci =& get_instance();
		$ci->load->config('rex');
		 //define logger folder application/logs/cs
		//$loggings = $ci->config->item('log');
		//$this->load->library('logging/logging',$loggings); //call loggings from the lib
        //$this->logger_cs = $this->logging->get_logger('cs'); //get cs folder
		
		 if(!$this->validate_params($uuid,$version))
		 {
			 //$this->logger_cs->error("Workflow redraft milestone error: invalid uuid or version no - " . $uuid."/".$version);
			 return;
		 }
		
		//$this->load->helper('url');
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		
		$success = $this->rexrest->processClientCredentialToken();
		
		if(!$success)
		{
			$this->logger_cs->error("Workflow redraft milestone processClientCredentailToekn error:".$this->rexrest->error.'-' . $uuid."/".$version);
			return;
		}
		
		$success = $this->rexrest->getItem($uuid, $version, $response);
		if(!$success)
		{
			//$this->logger_cs->error("Workflow redraft milestone getItem error:".$this->rexrest->error.'-' . $uuid."/".$version);
			return;
		}
		
		
		if($response['status'] == 'rejected')
		{
			$success = $this->rexrest->item_redraft($uuid, $version, $response);
			if(!$success)
			{
				//$this->logger_cs->error("Workflow redraft milestone item_redraft error:".$this->rexrest->error.'-' . $uuid."/".$version);
				return;
			}
			
			$success = $this->rexrest->getItem($uuid, $version, $r);
			if(!$success)
			{
				//$this->logger_cs->error("Workflow redraft milestone getItem error:" . $this->rexrest->error. '-' . $uuid."/".$version);
				return;
			}
			
			if($r['status'] == 'draft')
			{
				$success = $this->rexrest->submitForModeration($uuid, $version, $r);
				if(!$success)
				{
					//$this->logger_cs->error("Workflow redraft milestone submitForModeration error:".$this->rexrest->error. '-' . $uuid."/".$version);
					return;
				}
				
				return;
			}
			else
			{
				//$this->logger_cs->error("Workflow redraft milestone - unable to submit for moderation - item status is:".$r['status'].'-' . $uuid."/".$version);
				return;	
			}
		}
		else
		{
			//$this->logger_cs->error("Workflow redraft milestone - unable to redrafte - item status is:".$response['status'].'-' . $uuid."/".$version);
			return;	
		}
	 }
	
	
	/***********************************************************************
	  Automatically Redraft an item that the form hasn't completed
	  @uuid      item uuid
	  @version   verison no
	 ************************************************************************/
	public function redraftItem($uuid='missed', $version='missed')
	{
		$this->output->enable_profiler(TRUE);
		session_start();
	    ignore_user_abort(true);
		// Set your timelimit to a length long enough for your script to run, 
		// but not so long it will bog down your server in case multiple versions run 
		// or this script get's in an endless loop.
		$content = 'good';
			#ob_end_clean();                     // Close current output buffer
		$len = strlen($content);             
		header('Content-Type: text/html; charset=UTF-8');
		header('Content-Encoding: none;');
		header('Connection: close');         // Tell the client to close connection
		header("Content-Length: $len");      // Close connection after $size characters
		echo $content;                       // Output content
		ob_flush();
        flush();                             // Force php-output-cache to flush to rex.
                                             
		ob_end_flush();                       
										 
        #make sure sam work flow completely finishes
		sleep(5);

		$ci =& get_instance();
		$ci->load->config('rex');
		//$loggings = $ci->config->item('log');
		//$this->load->library('logging/logging',$loggings); //call loggings from the lib
       // $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder
		
		 if(!$this->validate_params($uuid,$version))
		 {
			// $this->logger_cs->error("Workflow redraft milestone error: invalid uuid or version no - " . $uuid."/".$version);
			 return;
		 }
		 
		//$this->load->helper('url');
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		
		$success = $this->rexrest->processClientCredentialToken();
		
		if(!$success)
		{
			//$this->logger_cs->error("Workflow redraft milestone processClientCredentailToekn error:".$this->rexrest->error.'-' . $uuid."/".$version);
			return;
		}
		
		$success = $this->rexrest->getItem($uuid, $version, $response);
		if(!$success)
		{
			//$this->logger_cs->error("Workflow redraft milestone getItem error:".$this->rexrest->error.'-' . $uuid."/".$version);
			return;
		}

		if($response['status'] == 'moderating'|| $response['status'] == 'live' || $response['status'] == 'rejected')
		{
			$success = $this->rexrest->item_redraft($uuid, $version, $response);
			if(!$success)
			{
				//$this->logger_cs->error("Workflow redraft milestone item_redraft error:".$this->rexrest->error.'-' . $uuid."/".$version);
				return;
			}
			else
			{
				return;
			}
			
		}
		else
		{
			//$this->logger_cs->error("Workflow redraft milestone - unable to redraft item" . $uuid."/".$version);
			return;	
		}

		
	}
    
	public function addAttachment($uuid='missed', $version='missed')
	{
		$errdata['heading'] = "Error";

        if($this->validate_params($uuid, $version) == false)
        {
            $errdata['message'] = "Invalid Request";
            log_message('error', 'Attaching test file failed type 1, item uuid: ' . $uuid . ', error: ' . 'Invalid input params.');
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        $this->load->helper('url');
        $this->load->library('rexrest/rexrest');


        $success = $this->rexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching test file failed type 2, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        ignore_user_abort(true);
        $content = 'good';         // Get the content of the output buffer
        #ob_end_clean();                     // Close current output buffer
        $len = strlen($content);
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Encoding: none;');
        header('Connection: close');         // Tell the client to close connection
        header("Content-Length: $len");      // Close connection after $size characters
        echo $content;  // Output content

        ob_flush();
        flush();                             // Force php-output-cache to flush to rex.

        ob_end_flush();

        #make sure milestone work flow completely finishes
        sleep (5);

        $success = $this->rexrest->getItem($uuid, $version, $response);
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching test file failed type 3, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            #$this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        unset($response['headers']);
        $item_bean = $response;

        $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));

        ob_start();

        $success = $this->rexrest->filesCopy($uuid, $version, $response1);

        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching test file failed (fileCopy), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
           # $this->load->view('rhd/showerror_view', $errdata);
            return;
        }
        if(!isset($response1['headers']['location']))
        {
            $errdata['message'] = 'No Location header in response to copy files REST call.';
            log_message('error', 'Attaching test file failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            #$this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        $location = $response1['headers']['location'];
        $filearea_uuid = substr($location, strpos($location, 'file')+5, 36);

        if(!isset($item_bean['attachments'])) {
            $item_bean['attachments'] = array();
        }

        $existing_attachments = $item_bean['attachments'];
        $new_attachments = array();

        $largest_suffix_number = 0;
        for ($j = 0; $j < count($existing_attachments); $j++)
        {
            $file_suffix_number = intval(substr($existing_attachments[$j]['uuid'], 33, 3));
            if($file_suffix_number > $largest_suffix_number)
                $largest_suffix_number = $file_suffix_number;
        }

        $largest_suffix_number ++;
        $file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $largest_suffix_number);

        ob_start();
        require_once('workflow_report.txt');
        $testFileContent = ob_get_contents();
        ob_end_clean();

        $refValue = new DateTime("now");
        $filename = 'workflow_report_' . $refValue->format('c') . '.txt';

        $success = $this->rexrest->fileUpload($filearea_uuid, $filename, $testFileContent, $response2);
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching test file failed (fileUpload), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }
		

		$num_current_attachment = $this->xmlwrapper->numNodes('/xml/item/attachments/uuid');
	
		$attachment_root = $this->xmlwrapper->createNodeFromXPath('/xml/item/attachments');
		$uuid_node = $this->xmlwrapper->createNode($attachment_root, "uuid");
		$uuid_node->nodeValue = $file_uuid;

        $new_attachments[0] = array('type'=>'file',
            'filename'=>$filename,
            'description'=>$filename,
            'uuid'=>$file_uuid);

        $item_bean['attachments'] = array_merge($new_attachments,$existing_attachments);
        $item_bean['metadata'] = $this->xmlwrapper->__toString();

        $success = $this->rexrest->editItem($uuid, $version, $item_bean, $response3, $filearea_uuid);
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching Milestone pdf failed (editItem), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            #$this->load->view('rhd/showerror_view', $errdata);
            return;
        }
	}
	
    public function getComment($uuid='missed', $version='missed')
    {
        $ci =& get_instance();
        $ci->load->config('rex');
        $loggings = $ci->config->item('log');
        $this->load->library('logging/logging', $loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder

        if (!$this->validate_params($uuid, $version)) {
            $this->logger_cs->error("Workflow redraft milestone error: invalid uuid or version no - " . $uuid . "/" . $version);
            return false;
        }

        //$this->load->helper('url');
        $oauth = array('oauth_client_config_name' => 'rhd');
        $this->load->library('rexrest/rexrest', $oauth);

        $success = $this->rexrest->processClientCredentialToken();

        if (!$success) {
            $this->logger_cs->error("Workflow redraft milestone processClientCredentailToekn error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
            return false;
        }

        $success = $this->rexrest->getItemHistory($uuid, $version, $response);
        if (!$success) {
            $this->logger_cs->error("Workflow redraft milestone getItem error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
            return false;
        }
        echo 'response: <pre>';
        print_r($response);
        echo '</pre>';
    }
	 
	private function validate_params($uuid, $version)
	{
		if(strcmp($uuid, 'missed')==0 || strlen($uuid) != 36)
		{
			return false;
		}
		
		if(strcmp($version, 'missed')==0 || !is_numeric($version))
		{
			return false;
		}
		
		return true;
	}
	
}
?>