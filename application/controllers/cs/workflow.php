<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow extends CI_Controller 
{
	protected $logger_cs;  //log
	public function __construct()
    {
		parent::__construct();
		//define logger folder application/logs/cs
		$ci =& get_instance();
		$ci->load->config('rex');
		$this->load->helper('url');
		$loggings = $ci->config->item('log');
		$this->load->library('logging/logging',$loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder
	
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
		$content = 'redraft milestone and submit for moderation';
		$len = strlen($content);             
		header('Content-Type: text/html; charset=UTF-8');
		header('Content-Encoding: none;');
		header('Connection: close');         // Tell the client to close connection
		header("Content-Length: $len");      // Close connection after $size characters
		echo $content;                       // Output content
		ob_flush();
        flush();                             // Force php-output-cache to flush to flex.
                                             
		ob_end_flush();                       
										 
        #make sure sam work flow completely finishes
		sleep(5);

		$ci =& get_instance();
		$ci->load->config('rex');
		 //define logger folder application/logs/cs
		$loggings = $ci->config->item('log');
		$this->load->library('logging/logging',$loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder
		
		 if(!$this->validate_params($uuid,$version))
		 {
			 $this->logger_cs->error("Workflow redraft milestone error: invalid uuid or version no - " . $uuid."/".$version);
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
			$this->logger_cs->error("Workflow redraft milestone getItem error:".$this->rexrest->error.'-' . $uuid."/".$version);
			return;
		}
		
		
		if($response['status'] == 'rejected')
		{
			$success = $this->rexrest->item_redraft($uuid, $version, $response);
			if(!$success)
			{
				$this->logger_cs->error("Workflow redraft milestone item_redraft error:".$this->rexrest->error.'-' . $uuid."/".$version);
				return;
			}
			
			$success = $this->rexrest->getItem($uuid, $version, $r);
			if(!$success)
			{
				$this->logger_cs->error("Workflow redraft milestone getItem error:" . $this->rexrest->error. '-' . $uuid."/".$version);
				return;
			}
			
			if($r['status'] == 'draft')
			{
				$success = $this->rexrest->submitForModeration($uuid, $version, $r);
				if(!$success)
				{
					$this->logger_cs->error("Workflow redraft milestone submitForModeration error:".$this->rexrest->error. '-' . $uuid."/".$version);
					return;
				}
				
				return;
			}
			else
			{
				$this->logger_cs->error("Workflow redraft milestone - unable to submit for moderation - item status is:".$r['status'].'-' . $uuid."/".$version);
				return;	
			}
		}
		else
		{
			$this->logger_cs->error("Workflow redraft milestone - unable to redrafte - item status is:".$response['status'].'-' . $uuid."/".$version);
			return;	
		}
	 }
		
	/***********************************************************************
	  Automatically Redraft an item that the form hasn't completed
	  @uuid      item uuid
	  @version   verison no
	 ************************************************************************/
	public function redraftMilestone($uuid='missed', $version='missed')
	{
		$this->output->enable_profiler(TRUE);
		session_start();
	    ignore_user_abort(true);
		// Set your timelimit to a length long enough for your script to run, 
		// but not so long it will bog down your server in case multiple versions run 
		// or this script get's in an endless loop.
		$content = 'redraft milestone';
			#ob_end_clean();                     // Close current output buffer
		$len = strlen($content);             
		header('Content-Type: text/html; charset=UTF-8');
		header('Content-Encoding: none;');
		header('Connection: close');         // Tell the client to close connection
		header("Content-Length: $len");      // Close connection after $size characters
		echo $content;                       // Output content
		ob_flush();
        flush();                             // Force php-output-cache to flush to flex.
                                             
		ob_end_flush();                       
										 
        #make sure sam work flow completely finishes
		sleep(5);

		$ci =& get_instance();
		$ci->load->config('rex');
		$loggings = $ci->config->item('log');
		$this->load->library('logging/logging',$loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder
		
		 if(!$this->validate_params($uuid,$version))
		 {
			 $this->logger_cs->error("Workflow redraft milestone error: invalid uuid or version no - " . $uuid."/".$version);
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
			$this->logger_cs->error("Workflow redraft milestone getItem error:".$this->rexrest->error.'-' . $uuid."/".$version);
			return;
		}
		
		$new_item_bean = $response;
		$xmlwrapper = 'xmlwrapper_redraft' . $uuid . '_' . $version;
		//pull out metadata XML 
		$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$new_item_bean['metadata']), $xmlwrapper);
		
		//delete approvals and comments
		if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome'))
		{
			$this->$xmlwrapper->deleteNodeFromXPath('/xml/item/candidature/change_request/course/outcome');  
		}
		
		if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/@supported'))
		{
			$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/@supported', ''); 
		}
		
		if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator'))
		{
			$this->$xmlwrapper->deleteNodeFromXPath('/xml/item/progression/outcome/recommendation/coordinator');  
		}
		
		if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student'))
		{
			$this->$xmlwrapper->deleteNodeFromXPath('/xml/item/progression/outcome/student');  
		}
		
		
		$new_item_bean['metadata'] = $this->$xmlwrapper->__toString();

		$success = $this->rexrest->editItem($uuid, $version, $new_item_bean, $re, '');
		if(!$success)
		{
			$this->logger_cs->error("Workflow redraft milestone remove comments error:".$this->rexrest->error.'-' . $uuid."/".$version);
			return;
		}

		if($response['status'] == 'rejected' || $response['status'] == 'moderating')
		{
			$success = $this->rexrest->item_redraft($uuid, $version, $r);
			if(!$success)
			{
				$this->logger_cs->error("Workflow redraft milestone item_redraft error:".$this->rexrest->error.'-' . $uuid."/".$version);
				return;
			}
			else
			{
				return;
			}
			
		}
		else
		{
			$this->logger_cs->error("Workflow redraft milestone - unable to redraft item" . $uuid."/".$version);
			return;	
		}
	}
	
    public function rejectMilestone($uuid='missed', $version='missed')
	{
		//https://rex-test.flinders.edu.au:443/api/item/e31a1578-db8f-45b3-abe9-036daa765e26/1/task/b3774398-66bb-49ea-a2df-4f2be3fd4066/reject?message=please%20finish%20all%20parts
		
		//https://rex-test.flinders.edu.au:443/api/task/?filter=all&start=0&length=10&collections=1ef133a1-7853-422d-8562-ccb47fec39c6&reverse=false
		$collection_uuid = '1ef133a1-7853-422d-8562-ccb47fec39c6';
		$workflow_uuid = '8c4ec54a-348c-483e-bcda-b36fb7185d5d';
		
		
		$ci =& get_instance();
		$ci->load->config('rex');
		$loggings = $ci->config->item('log');
		$this->load->library('logging/logging',$loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder
		
		 if(!$this->validate_params($uuid,$version))
		 {
			 $this->logger_cs->error("Workflow redraft milestone error: invalid uuid or version no - " . $uuid."/".$version);
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
		
		//	public function getCurrentTasks($filter='all', $start = 0, $length = 100, $collection_uuid, &$response)

		$success = $this->rexrest->getCurrentTasks('all', 0, 100, $collection_uuid, $response);
		if(!$success)
		{
			$this->logger_cs->error("Workflow get current tasks error:".$this->rexrest->error);
			return;
		}
		
		if(isset($response['results']) && isset($response['length']) && $response['length'] >= 1)
		{
			$task_uuid = '';
			for($i=0; $i<$response['length']; $i++)
			{
				//if($response['results'][$i]['item']['uuid'] == $uuid && $response['results'][$i]['task']['workflow']['uuid'])
				if($response['results'][$i]['item']['uuid'] == $uuid)
				{
					
					$task_uuid = $response['results'][$i]['task']['uuid'];
					break;
				}
			}
			
			if(strlen($task_uuid) == 36)
			{
				//echo 'task uuid......: '. $response['results'][$i]['task']['uuid'];
				
				$success = $this->rexrest->rejectItem($uuid, $version, $task_uuid, 'Student has not signed off or principle supervisor has not finished recommedation',$r);
				if(!$success)
				{
					$this->logger_cs->error("Workflow reject task error:".$this->rexrest->error);
			return;
				}
				else
				{
					return 'successfullly rejected this milestone back to the original contributor';
					
					
				}
			}
		}
		else
		{
			
			return 'no task found';
		}
		
	}
	
	public function uploadWorkflowComment($uuid='missed', $version='missed', $workflow_uuid= 'missed')
    {
		$data = array();
        $ci =& get_instance();
        $ci->load->config('rex');
        $loggings = $ci->config->item('log');
        $this->load->library('logging/logging', $loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder

        if (!$this->validate_workflow_params($uuid, $version, $workflow_uuid)) {
            $this->logger_cs->error("Workflow uploadWorkflowComment error: invalid item uuid, version no or workflow uuid - " . $uuid . "/" . $version. "/" . $workflow_uuid);
            return false;
        }

        //$this->load->helper('url');
        $oauth = array('oauth_client_config_name' => 'rhd');
        $this->load->library('rexrest/rexrest', $oauth);

        $success = $this->rexrest->processClientCredentialToken();

        if (!$success) {
            $this->logger_cs->error("Milestone  uploadWorkflowComment processClientCredentailToekn error:" . $this->rexrest->error . '-' . $uuid . "/" . $version. "/" . $workflow_uuid);
            return false;
        }

        $success = $this->rexrest->getItemHistory($uuid, $version, $response);
        if (!$success) {
            $this->logger_cs->error("getItemHistory error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
            return false;
        }
		
		
		$workflow_step_name = '';
		
		switch ($workflow_uuid)
		{
			case 'a8b0c2dd-a93d-472c-9fa9-ce4f62ec3fec': //student sign off
				$workflow_step_name = 'stu_sign_off';
			break;
			
			case 'a08b3eb2-457b-46ea-94b0-29a82de3fc06': //postGrad Coord sign off
				$workflow_step_name = 'postGrad_sign_off';
			break;
			
			/***********************************************/
			/**********Masters to PhD sign offs*************/
			/***********************************************/
			case '4a94577bd-4629-4254-8f59-4704a34bbee3': //FSE :: Masters to PhD sign off
				$workflow_step_name = 'master_to_phd_sign_off';
			break;
			
			case '07799b26-0c8d-4b58-bca8-13fca444a70c': //SaBS :: Masters to PhD sign off
				$workflow_step_name = 'master_to_phd_sign_off';
			break;
			
			
			case '512da26c-f82c-4ba9-8909-8fe051390c4c': //MNHS :: Masters to PhD sign off
				$workflow_step_name = 'master_to_phd_sign_off';
			break;
			
			case '860e0255-ab90-41da-a453-c2d16a824d6f': //EHL :: Masters to PhD sign off
				$workflow_step_name = 'master_to_phd_sign_off';
			break;
			
			/***********************************************/
			/*************RHD admin sign offs***************/
			/***********************************************/
			case '4ad18428-ded8-4e32-8c36-460856ad03a2': //EHL Admin: milestone sign off
				$workflow_step_name = 'rhd_admin_sign_off';
			break;
			
			case '5fb6f61f-9f2d-47b5-a69b-1a5d6d9ccc6e': //FSE Admin: milestone sign off
				$workflow_step_name = 'rhd_admin_sign_off';
			break;
			
			case 'd906c04f-8742-4733-8a0f-65fca8d8b24f': //MNHS Admin: milestone sign off
				$workflow_step_name = 'rhd_admin_sign_off';
			break;
			
			case '9f0e0561-84e2-421c-af55-8a6f857f7b57': //MNHS Admin: milestone sign off
				$workflow_step_name = 'rhd_admin_sign_off';
			break;
		}
		if(isset($response[0]) && $workflow_step_name != '')
		{
			//ignore the last element in the array -> [headers]
			for($i=0; $i<count($response)-1; $i++)
			{
				if(isset($response[$i]['step']))
				{
					$workflow_step_uuid = $response[$i]['step'];
					if($workflow_step_uuid == $workflow_uuid)
					{
						$data[$workflow_step_name]['step_uuid'] = $workflow_step_uuid;
						$data[$workflow_step_name]['stepName'] = $response[$i]['stepName']; // Student sign off
						$data[$workflow_step_name]['type'] = $response[$i]['type']; //apprved or rejected
						$data[$workflow_step_name]['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
						$data[$workflow_step_name]['comment'] = $response[$i]['comment']; 
						$data[$workflow_step_name]['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
						$data[$workflow_step_name]['state'] = $response[$i]['state']; //moderating
					}
				}
			}
		}
		
		$success = $this->rexrest->getItem($uuid, $version, $r);
        if (!$success) 
		{
            $this->logger_cs->error("getItem error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
            return false;
        }
		
		$new_item_bean = $r;
		$xmlwrapper = 'xmlwrapper_' . $uuid . '_' . $workflow_uuid;
		//pull out metadata XML 
		$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$new_item_bean['metadata']), $xmlwrapper);
		
		/**** student sign off ***/
		if(isset($data['stu_sign_off']) && isset($data['stu_sign_off']['type']) && $data['stu_sign_off']['type']!= '')
		{
			$type = '';
			switch ($data['stu_sign_off']['type'])
			{
				case 'approved':
					$type= 'Yes';
				break;
				case 'rejected':
					$type= 'No';
				break;
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/accepted'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/accepted', $type);
			}
			else
			{
				$stu_approval = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/student/accepted");
				$this->$xmlwrapper->createTextNode($stu_approval,$type, true);
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/comment', $data['stu_sign_off']['comment']);
			}
			else
			{
				$stu_comment = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/student/comment");
				$this->$xmlwrapper->createTextNode($stu_comment,$data['stu_sign_off']['comment'], true);
			}
			
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/date'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/date', $data['stu_sign_off']['date']);
			}
			else
			{
				$student_approval_date = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/student/date");
				$this->$xmlwrapper->createTextNode($student_approval_date,$data['stu_sign_off']['date'], true);
			}
			
			if(isset($data['stu_sign_off']['moderator']) && $data['stu_sign_off']['moderator']!= '')
			{
				$stu_name = '';
				if(strlen($data['stu_sign_off']['moderator']) == 36)
				{
					$stu_name = 'System Administrator';
				}
				else
				{
					$this->load->library('ldap/ldap', $oauth);
					$ldap_user = $ci->ldap->get_attributes($data['stu_sign_off']['moderator']);
					$stu_name = $ldap_user['name'];
				}
				
				/*echo 'USER NAME:<pre>';
				print_r($ldap_user);
				echo '</pre>';*/
				
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/approver'))
				{
					
					$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/approver', $stu_name);
				}
				else
				{
					$stu_approver = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/student/approver");
					$this->$xmlwrapper->createTextNode($stu_approver,$stu_name, true);
				}
			}
			
		}
		else
		{
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/accepted'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/accepted', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/comment', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/approver'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/approver', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/date'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/date', '');
			}
		}
		
		/********postGrad sign off******/
		if(isset($data['postGrad_sign_off']) && isset($data['postGrad_sign_off']['type']) && $data['postGrad_sign_off']['type']!= '')
		{
			$type = '';
			switch ($data['postGrad_sign_off']['type'])
			{
				case 'approved':
					$type = 'Yes';
				break;
				case 'rejected':
					$type = 'No';
				break;
			}
			
				
			if(!$this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation'))
			{
				$this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation");
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/@supported'))
			{
				$s_node = $this->$xmlwrapper->node("/xml/item/progression/outcome/recommendation");
				$pg_approval = $this->$xmlwrapper->createAttribute($s_node, "supported");
				$this->$xmlwrapper->createTextNode($pg_approval, $type);

			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/@supported', $type);
				
			}
			
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/comment', $data['postGrad_sign_off']['comment']);
			}
			else
			{
				$pg_comment = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation/coordinator/comment");
				$this->$xmlwrapper->createTextNode($pg_comment,$data['postGrad_sign_off']['comment'], true);
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator'))
			{
				$this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation/coordinator");
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/date'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/date', $data['postGrad_sign_off']['date']);
			}
			else
			{
				$pg_date = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation/coordinator/date");
				$this->$xmlwrapper->createTextNode($pg_date,$data['postGrad_sign_off']['date'], true);
			}
			
			if(isset($data['postGrad_sign_off']['moderator']) && $data['postGrad_sign_off']['moderator']!= '')
			{
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/@fan'))
				{
					
					$this->$xmlwrapper->setNodeValue("/xml/item/progression/outcome/recommendation/coordinator/@fan", $data['postGrad_sign_off']['moderator'], true);
				}
				else
				{
					$coord_node = $this->$xmlwrapper->node("/xml/item/progression/outcome/recommendation/coordinator");
					$pg_fan= $this->$xmlwrapper->createAttribute($coord_node, "fan");
					$this->$xmlwrapper->createTextNode($pg_fan, $data['postGrad_sign_off']['moderator']);
				}
				
				
				$pg_name  = '';
				if(strlen($data['postGrad_sign_off']['moderator']) == 36)
				{
					$pg_name = 'System Administrator';
				}
				else
				{
					$this->load->library('ldap/ldap', $oauth);
					$ldap_user = $ci->ldap->get_attributes($data['postGrad_sign_off']['moderator']);
					$pg_name = $ldap_user['name'];
				}
				
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/name'))
				{
					
					$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/name', $pg_name);
				}
				else
				{
					$pg_approver = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation/coordinator/name");
					$this->$xmlwrapper->createTextNode($pg_approver,$pg_name, true);
				}
			}
		}
		else
		{
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/@supported'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/@supported', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/comment', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/date'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/date', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/name'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/name', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/@fan'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/@fan', '');
			}
		}
		
		
		/********rhd course change sign off******/
		if(isset($data['master_to_phd_sign_off']) && isset($data['master_to_phd_sign_off']['type']) && $data['master_to_phd_sign_off']['type']!= '')
		{
			$type = '';
			switch ($data['master_to_phd_sign_off']['type'])
			{
				case 'approved':
					$type = 'Yes';
				break;
				case 'rejected':
					$type = 'No';
				break;
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request'))
			{
				$this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request");
				
			}
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course'))
			{
				$this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course");
				
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome'))
			{
				$outcome = $this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course/outcome");
				if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@approved'))
				{
					$fa_approval = $this->$xmlwrapper->createAttribute($outcome, "approved");
					$this->$xmlwrapper->createTextNode($fa_approval, $type);
				}
				else
				{
					$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@approved', $type);
				}
			}
			else
			{
				if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@approved'))
				{
					$outcome_node = $this->$xmlwrapper->node("/xml/item/candidature/change_request/course/outcome");
					$fa_approval = $this->$xmlwrapper->createAttribute($outcome_node, "approved");
					$this->$xmlwrapper->createTextNode($fa_approval, $type);
				}
				else
				{
					$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@approved', $type);
				}
			}
			
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@date_approved'))
			{
				$outcome_node = $this->$xmlwrapper->node("/xml/item/candidature/change_request/course/outcome");
				$approval_date = $this->$xmlwrapper->createAttribute($outcome_node, "date_approved");
				$this->$xmlwrapper->createTextNode($approval_date , $data['master_to_phd_sign_off']['date']);
			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@date_approved', $data['master_to_phd_sign_off']['date']);
			}
			
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/comment'))
			{
				$com = $this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course/outcome/comment");
				$this->$xmlwrapper->createTextNode($com,$data['master_to_phd_sign_off']['comment'], true);
			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/comment', $data['master_to_phd_sign_off']['comment']);
				
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/fan'))
			{
				$approver_fan = $this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course/outcome/fan");
				$this->$xmlwrapper->createTextNode($approver_fan,$data['master_to_phd_sign_off']['moderator'], true);
			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/fan', $data['master_to_phd_sign_off']['moderator']);
				
			}
			
			$approver_name = '';
			if(strlen($data['master_to_phd_sign_off']['moderator']) == 36)
			{
				$approver_name = 'System Administrator';
			}
			else if($data['master_to_phd_sign_off']['moderator']!= '')
			{
				$this->load->library('ldap/ldap', $oauth);
				$ldap_user = $ci->ldap->get_attributes($data['master_to_phd_sign_off']['moderator']);
				$approver_name = $ldap_user['name'];
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/approved_by'))
			{
				
				$approver_node = $this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course/outcome/approved_by");
				$this->$xmlwrapper->createTextNode($approver_node, $approver_name, true);
			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/approved_by', $approver_name);
			}
			
			
		}
		else
		{
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@approved'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@approved', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@date_approved'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@date_approved', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/comment'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/comment', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/fan'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/fan', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/approved_by'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/approved_by', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@course_change'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@course_change', '');
			}
		}
	
		
		/********rhd admin sign off******/
		if(isset($data['rhd_admin_sign_off']) && isset($data['rhd_admin_sign_off']['type']) && $data['rhd_admin_sign_off']['type']!= '')
		{
			$type = '';
			switch ($data['rhd_admin_sign_off']['type'])
			{
				case 'approved':
					$type = 'Yes';
				break;
				case 'rejected':
					$type = 'No';
				break;
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/@status'))
			{
				
				$this->$xmlwrapper->setNodeValue("/xml/item/progression/outcome/@status", $type);
			}
			else
			{
				$admin_node = $this->$xmlwrapper->node("/xml/item/progression/outcome");
				$admin_approval = $this->$xmlwrapper->createAttribute($admin_node, "status");
				$this->$xmlwrapper->createTextNode($admin_approval, $type);
			}
			

			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/comment', $data['rhd_admin_sign_off']['comment']);
			}
			else
			{
				$pg_comment = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/determination/comment");
				$this->$xmlwrapper->createTextNode($pg_comment,$data['rhd_admin_sign_off']['comment'], true);
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/date'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/date', $data['rhd_admin_sign_off']['date']);
			}
			else
			{
				$pg_date = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/determination/date");
				$this->$xmlwrapper->createTextNode($pg_date,$data['rhd_admin_sign_off']['date'], true);
			}
			
			if(isset($data['rhd_admin_sign_off']['moderator']) && $data['rhd_admin_sign_off']['moderator']!= '')
			{
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/@fan'))
				{
					
					$this->$xmlwrapper->setNodeValue("/xml/item/progression/outcome/determination/@fan", $data['rhd_admin_sign_off']['moderator'], true);
				}
				else
				{
					$coord_node = $this->$xmlwrapper->node("/xml/item/progression/outcome/determination");
					$pg_fan= $this->$xmlwrapper->createAttribute($coord_node, "fan");
					$this->$xmlwrapper->createTextNode($pg_fan, $data['rhd_admin_sign_off']['moderator']);
				}
				
				$pg_name  = '';
				if(strlen($data['rhd_admin_sign_off']['moderator']) == 36)
				{
					$pg_name = 'System Administrator';
				}
				else
				{
					$this->load->library('ldap/ldap', $oauth);
					$ldap_user = $ci->ldap->get_attributes($data['rhd_admin_sign_off']['moderator']);
					$pg_name = $ldap_user['name'];
				}
				
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/name'))
				{
					
					$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/name', $pg_name);
				}
				else
				{
					$pg_approver = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/determination/name");
					$this->$xmlwrapper->createTextNode($pg_approver,$pg_name, true);
				}
			}
			
		
		}
		else
		{
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/@status'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/@status', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/comment', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/date'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/date', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/name'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/name', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/@fan'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/@fan', '');
			}
		}
		
		
		$new_item_bean['metadata'] = $this->$xmlwrapper->__toString();

		$success = $this->rexrest->editItem($uuid, $version, $new_item_bean, $r, '');
				
		echo 'data: <pre>';
        print_r($data);
        echo '</pre>';
		
        
		
    }
	
    public function getComment($uuid='missed', $version='missed')
    {
		$data = array();
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
            $this->logger_cs->error("Milestone getComment processClientCredentailToekn error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
            return false;
        }

        $success = $this->rexrest->getItemHistory($uuid, $version, $response);
        if (!$success) {
            $this->logger_cs->error("getItemHistory error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
            return false;
        }
		

		
		if(isset($response[0]))
		{
			//ignore the last element in the array -> [headers]
			for($i=0; $i<count($response)-1; $i++)
			//for($i = count($response)-2; $i >= 0; $i--)
			{
				//echo $i . $response[$i]['state'] . '<br/>';
				
				if(isset($response[$i]['step']))
				{
					$workflow_step_uuid = $response[$i]['step'];
					switch($workflow_step_uuid)
					{
						case 'a8b0c2dd-a93d-472c-9fa9-ce4f62ec3fec': //student sign off
							$data['stu_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['stu_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['stu_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['stu_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['stu_sign_off']['comment'] = $response[$i]['comment']; 
							$data['stu_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['stu_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						case 'a08b3eb2-457b-46ea-94b0-29a82de3fc06': //postGrad Coord sign off
							$data['postGrad_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['postGrad_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['postGrad_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['postGrad_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['postGrad_sign_off']['comment'] = $response[$i]['comment']; 
							$data['postGrad_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['postGrad_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						/***********************************************/
						/**********Masters to PhD sign offs*************/
						/***********************************************/
						case '4a94577bd-4629-4254-8f59-4704a34bbee3': //FSE :: Masters to PhD sign off
							$data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['master_to_phd_sign_off']['comment'] = $response[$i]['comment']; 
							$data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						case '07799b26-0c8d-4b58-bca8-13fca444a70c': //SaBS :: Masters to PhD sign off
							$data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['master_to_phd_sign_off']['comment'] = $response[$i]['comment']; 
							$data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						
						case '512da26c-f82c-4ba9-8909-8fe051390c4c': //MNHS :: Masters to PhD sign off
							$data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['master_to_phd_sign_off']['comment'] = $response[$i]['comment']; 
							$data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						case '860e0255-ab90-41da-a453-c2d16a824d6f': //EHL :: Masters to PhD sign off
							$data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['master_to_phd_sign_off']['comment'] = $response[$i]['comment']; 
							$data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						/***********************************************/
						/*************RHD admin sign offs***************/
						/***********************************************/
						case '4ad18428-ded8-4e32-8c36-460856ad03a2': //EHL Admin: milestone sign off
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment']; 
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						case '5fb6f61f-9f2d-47b5-a69b-1a5d6d9ccc6e': //FSE Admin: milestone sign off
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment']; 
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						case 'd906c04f-8742-4733-8a0f-65fca8d8b24f': //MNHS Admin: milestone sign off
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment']; 
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
						case '9f0e0561-84e2-421c-af55-8a6f857f7b57': //MNHS Admin: milestone sign off
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment']; 
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;
						
					}
				}
				
			}
			
		}
		
		$success = $this->rexrest->getItem($uuid, $version, $r);
        if (!$success) 
		{
            $this->logger_cs->error("getItem error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
            return false;
        }
		
		$new_item_bean = $r;
		$xmlwrapper = 'xmlwrapper_' . $uuid;
		//pull out metadata XML 
		$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$new_item_bean['metadata']), $xmlwrapper);
		
		/**** student sign off ***/
		if(isset($data['stu_sign_off']) && isset($data['stu_sign_off']['type']) && $data['stu_sign_off']['type']!= '')
		{
			$type = '';
			switch ($data['stu_sign_off']['type'])
			{
				case 'approved':
					$type= 'Yes';
				break;
				case 'rejected':
					$type= 'No';
				break;
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/accepted'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/accepted', $type);
			}
			else
			{
				$stu_approval = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/student/accepted");
				$this->$xmlwrapper->createTextNode($stu_approval,$type, true);
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/comment', $data['stu_sign_off']['comment']);
			}
			else
			{
				$stu_comment = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/student/comment");
				$this->$xmlwrapper->createTextNode($stu_comment,$data['stu_sign_off']['comment'], true);
			}
			
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/date'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/date', $data['stu_sign_off']['date']);
			}
			else
			{
				$student_approval_date = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/student/date");
				$this->$xmlwrapper->createTextNode($student_approval_date,$data['stu_sign_off']['date'], true);
			}
			
			if(isset($data['stu_sign_off']['moderator']) && $data['stu_sign_off']['moderator']!= '')
			{
				$this->load->library('ldap/ldap', $oauth);
				$ldap_user = $ci->ldap->get_attributes($data['stu_sign_off']['moderator']);
				$stu_name = $ldap_user['name'];
				
				/*echo 'USER NAME:<pre>';
				print_r($ldap_user);
				echo '</pre>';*/
				
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/approver'))
				{
					
					$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/approver', $stu_name);
				}
				else
				{
					$stu_approver = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/student/approver");
					$this->$xmlwrapper->createTextNode($stu_approver,$stu_name, true);
				}
			}
			
		}
		else
		{
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/accepted'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/accepted', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/comment', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/approver'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/approver', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/student/date'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/student/date', '');
			}
		}
		
		/********postGrad sign off******/
		if(isset($data['postGrad_sign_off']) && isset($data['postGrad_sign_off']['type']) && $data['postGrad_sign_off']['type']!= '')
		{
			$type = '';
			switch ($data['postGrad_sign_off']['type'])
			{
				case 'approved':
					$type = 'Yes';
				break;
				case 'rejected':
					$type = 'No';
				break;
			}
			
				
			if(!$this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation'))
			{
				$this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation");
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/@supported'))
			{
				$s_node = $this->$xmlwrapper->node("/xml/item/progression/outcome/recommendation");
				$pg_approval = $this->$xmlwrapper->createAttribute($s_node, "supported");
				$this->$xmlwrapper->createTextNode($pg_approval, $type);

			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/@supported', $type);
				
			}
			
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/comment', $data['postGrad_sign_off']['comment']);
			}
			else
			{
				$pg_comment = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation/coordinator/comment");
				$this->$xmlwrapper->createTextNode($pg_comment,$data['postGrad_sign_off']['comment'], true);
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator'))
			{
				$this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation/coordinator");
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/date'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/date', $data['postGrad_sign_off']['date']);
			}
			else
			{
				$pg_date = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation/coordinator/date");
				$this->$xmlwrapper->createTextNode($pg_date,$data['postGrad_sign_off']['date'], true);
			}
			
			if(isset($data['postGrad_sign_off']['moderator']) && $data['postGrad_sign_off']['moderator']!= '')
			{
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/@fan'))
				{
					
					$this->$xmlwrapper->setNodeValue("/xml/item/progression/outcome/recommendation/coordinator/@fan", $data['postGrad_sign_off']['moderator'], true);
				}
				else
				{
					$coord_node = $this->$xmlwrapper->node("/xml/item/progression/outcome/recommendation/coordinator");
					$pg_fan= $this->$xmlwrapper->createAttribute($coord_node, "fan");
					$this->$xmlwrapper->createTextNode($pg_fan, $data['postGrad_sign_off']['moderator']);
				}
				
				/*$this->load->library('ldap/ldap', $oauth);
				$ldap_user = $ci->ldap->get_attributes($data['postGrad_sign_off']['moderator']);
				$pg_name = $ldap_user['name'];
				
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/name'))
				{
					
					$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/name', $pg_name);
				}
				else
				{
					$pg_approver = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/recommendation/coordinator/name");
					$this->$xmlwrapper->createTextNode($pg_approver,$pg_name, true);
				}*/
			}
			
		
		}
		else
		{
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/@supported'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/@supported', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/comment', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/date'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/date', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/name'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/name', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/recommendation/coordinator/@fan'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/recommendation/coordinator/@fan', '');
			}
		}
		
		
		/********rhd course change sign off******/
		if(isset($data['master_to_phd_sign_off']) && isset($data['master_to_phd_sign_off']['type']) && $data['master_to_phd_sign_off']['type']!= '')
		{
			$type = '';
			switch ($data['master_to_phd_sign_off']['type'])
			{
				case 'approved':
					$type = 'Yes';
				break;
				case 'rejected':
					$type = 'No';
				break;
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request'))
			{
				$this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request");
				
			}
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course'))
			{
				$this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course");
				
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome'))
			{
				$outcome = $this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course/outcome");
				if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@approved'))
				{
					$fa_approval = $this->$xmlwrapper->createAttribute($outcome, "approved");
					$this->$xmlwrapper->createTextNode($fa_approval, $type);
				}
				else
				{
					$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@approved', $type);
				}
			}
			else
			{
				if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@approved'))
				{
					$outcome_node = $this->$xmlwrapper->node("/xml/item/candidature/change_request/course/outcome");
					$fa_approval = $this->$xmlwrapper->createAttribute($outcome_node, "approved");
					$this->$xmlwrapper->createTextNode($fa_approval, $type);
				}
				else
				{
					$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@approved', $type);
				}
			}
			
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@date_approved'))
			{
				$outcome_node = $this->$xmlwrapper->node("/xml/item/candidature/change_request/course/outcome");
				$approval_date = $this->$xmlwrapper->createAttribute($outcome_node, "date_approved");
				$this->$xmlwrapper->createTextNode($approval_date , $data['master_to_phd_sign_off']['date']);
			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@date_approved', $data['master_to_phd_sign_off']['date']);
			}
			
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/comment'))
			{
				$com = $this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course/outcome/comment");
				$this->$xmlwrapper->createTextNode($com,$data['master_to_phd_sign_off']['comment'], true);
			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/comment', $data['master_to_phd_sign_off']['comment']);
				
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/fan'))
			{
				$approver_fan = $this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course/outcome/fan");
				$this->$xmlwrapper->createTextNode($approver_fan,$data['master_to_phd_sign_off']['moderator'], true);
			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/fan', $data['master_to_phd_sign_off']['moderator']);
				
			}
			$approver_name = '';
			if(strlen($data['master_to_phd_sign_off']['moderator']) == 36)
			{
				$approver_name = 'System Administrator';
			}
			else if($data['master_to_phd_sign_off']['moderator']!= '')
			{
				$this->load->library('ldap/ldap', $oauth);
				$ldap_user = $ci->ldap->get_attributes($data['master_to_phd_sign_off']['moderator']);
				$approver_name = $ldap_user['name'];
			}
			
			if(!$this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/approved_by'))
			{
				
				$approver_node = $this->$xmlwrapper->createNodeFromXPath("/xml/item/candidature/change_request/course/outcome/approved_by");
				$this->$xmlwrapper->createTextNode($approver_node, $approver_name, true);
			}
			else
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/approved_by', $approver_name);
			}
			
			
		}
		else
		{
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@approved'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@approved', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@date_approved'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@date_approved', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/comment'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/comment', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/fan'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/fan', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/approved_by'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/approved_by', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/candidature/change_request/course/outcome/@course_change'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/candidature/change_request/course/outcome/@course_change', '');
			}
		}
	
		
		/********rhd admin sign off******/
		if(isset($data['rhd_admin_sign_off']) && isset($data['rhd_admin_sign_off']['type']) && $data['rhd_admin_sign_off']['type']!= '')
		{
			$type = '';
			switch ($data['rhd_admin_sign_off']['type'])
			{
				case 'approved':
					$type = 'Yes';
				break;
				case 'rejected':
					$type = 'No';
				break;
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/@status'))
			{
				
				$this->$xmlwrapper->setNodeValue("/xml/item/progression/outcome/@status", $type);
			}
			else
			{
				$admin_node = $this->$xmlwrapper->node("/xml/item/progression/outcome");
				$admin_approval = $this->$xmlwrapper->createAttribute($admin_node, "status");
				$this->$xmlwrapper->createTextNode($admin_approval, $type);
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/comment', $data['rhd_admin_sign_off']['comment']);
			}
			else
			{
				$pg_comment = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/determination/comment");
				$this->$xmlwrapper->createTextNode($pg_comment,$data['rhd_admin_sign_off']['comment'], true);
			}
			
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/date'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/date', $data['rhd_admin_sign_off']['date']);
			}
			else
			{
				$pg_date = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/determination/date");
				$this->$xmlwrapper->createTextNode($pg_date,$data['rhd_admin_sign_off']['date'], true);
			}
			
			if(isset($data['rhd_admin_sign_off']['moderator']) && $data['rhd_admin_sign_off']['moderator']!= '')
			{
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/@fan'))
				{
					
					$this->$xmlwrapper->setNodeValue("/xml/item/progression/outcome/determination/@fan", $data['rhd_admin_sign_off']['moderator'], true);
				}
				else
				{
					$coord_node = $this->$xmlwrapper->node("/xml/item/progression/outcome/determination");
					$pg_fan= $this->$xmlwrapper->createAttribute($coord_node, "fan");
					$this->$xmlwrapper->createTextNode($pg_fan, $data['rhd_admin_sign_off']['moderator']);
				}
				
				$this->load->library('ldap/ldap', $oauth);
				$ldap_user = $ci->ldap->get_attributes($data['rhd_admin_sign_off']['moderator']);
				$pg_name = $ldap_user['name'];
				
				if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/name'))
				{
					
					$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/name', $pg_name);
				}
				else
				{
					$pg_approver = $this->$xmlwrapper->createNodeFromXPath("/xml/item/progression/outcome/determination/name");
					$this->$xmlwrapper->createTextNode($pg_approver,$pg_name, true);
				}
			}
			
		
		}
		else
		{
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/@status'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/@status', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/comment'))
			{
				
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/comment', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/date'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/date', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/name'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/name', '');
			}
			if($this->$xmlwrapper->nodeExists('/xml/item/progression/outcome/determination/@fan'))
			{
				$this->$xmlwrapper->setNodeValue('/xml/item/progression/outcome/determination/@fan', '');
			}
		}
		
		
		
		
		$new_item_bean['metadata'] = $this->$xmlwrapper->__toString();

		$success = $this->rexrest->editItem($uuid, $version, $new_item_bean, $r, '');
				
				
		echo 'data: <pre>';
        print_r($data);
        echo '</pre>';
		
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