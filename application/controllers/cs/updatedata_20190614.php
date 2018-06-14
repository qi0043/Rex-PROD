<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UpdateData extends CI_Controller
{
	protected $logger_cs;
	protected $oauth;

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
		
		//connecting to rest with rhd webservice account
		$oauth = array('oauth_client_config_name' => 'rhd');
		$this->load->library('rexrest/rexrest', $oauth);
		$success = $this->rexrest->processClientCredentialToken();
		if(!$success)
		{}

		// Load email library
        $this->load->library('email');

		// Load permission library
        $this->load->library('permission/permission');
        $this->check_permission($_SERVER['PHP_AUTH_USER']);
	 }

	public function milestones()
	{
		try {
            $milestones = $this->cs_model->vw_rhd_mod_milestones();
            $result = $this->update_milestone($milestones);
            $status = count($result['errors']) > 0 ? 'PS' : 'S';
            $message = 'Completed: ' . $result['counter'] . ' milestones processed, ' . count($result['errors']) . ' errors';
            $this->cs_model->populate_daily_import('milestones', $status, $message);
            $this->send_email($result['errors'], $result['counter'], 0, $status, 'Milestone Data Update');
		} catch (Exception $e) {
		    $errors = array();
            $error['header'] = 'unexpected exception occurred';
            $error['msg'] = $e->getMessage();
            array_push($errors, $error);
            $this->cs_model->populate_daily_import('milestones', 'E', $e->getMessage());
            $this->send_email($errors, $result['counter'], 0, 'E', 'Milestone Data Update');
        }

		return $errors;
	}

	public function single_milestone($fan)
    {
        try {
            $milestones = $this->cs_model->get_vw_rhd_mod_milestone($fan);
            $result = $this->update_milestone($milestones);
            $status = count($result['errors']) > 0 ? 'PS' : 'S';
            $message = 'Completed: ' . $result['counter'] . ' milestones processed, ' . count($result['errors']) . ' errors';
            $this->cs_model->populate_daily_import('milestones', $status, $message);
            $this->send_email($result['errors'], $result['counter'], 0, $status, 'Milestone Data Update');
        } catch (Exception $e) {
            $errors = array();
            $error['header'] = 'unexpected exception occurred';
            $error['msg'] = $e->getMessage();
            array_push($errors, $error);
            $this->cs_model->populate_daily_import('milestones', 'E', $e->getMessage());
            $this->send_email($errors, $result['counter'], 0, 'E', 'Milestone Data Update');
        }

        return $errors;
    }

	private function update_milestone($milestones)
    {
        $counter = 0;
        $errors = array();

        for ($i = 0; $i < count($milestones); $i++)
        {
            $fan = $milestones[$i]['stu_fan'];
            $milestone_code = $milestones[$i]['milestone_code'];
            $milestone_name = $milestones[$i]['milestone_name'];
            $milestone_due_date = $milestones[$i]['milestone_due_date'];
            $milestone_collection_uuid = $this->config->item('milestone_collection');
            $rex_milestones = $this->cs_model->get_stu_milestone($milestone_collection_uuid, $fan);
            if($rex_milestones != 0)
            {
                //one student should only has 1 draft milestone.
                $item_uuid = $rex_milestones[0]['item_uuid'];
                $item_version = $rex_milestones[0]['item_version'];
                $item_status = $rex_milestones[0]['item_status'];
                $item_name = $rex_milestones[0]['item_name'];

                //validate item uuid and item version
                $result = $this->validate_params($item_uuid,$item_version);
                if(!$result)
                {
                    $error['header'] = 'invalid item uuid or version no.';
                    $error['msg'] = 'item uuid:' . $item_uuid .' item version no: '. $item_version ;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $milestone_collection_uuid, 'N', 'I', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $isLocked = $this->rexrest->getLock($item_uuid, $item_version, $res);
                if ($isLocked) {
                    $error['header'] = 'item locked for editing';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version . $this->rexrest->error;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $milestone_collection_uuid, 'N', 'L', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                //REST CALL: get item XML by item uuid and version no.
                $success = $this->rexrest->getItem($item_uuid, $item_version, $response);
                if(!$success)
                {
                    $error['header'] = 'error in getting item';
                    $error['msg'] = 'item uuid:' . $item_uuid .' item version no: '. $item_version  . $this->rexrest->error;;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $milestone_collection_uuid, 'N', 'G', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                unset($response['headers']);
                $cms = $response;

                //IMPORTANT: each xml item needs to be assigned a unique xml wrapper name
                $xmlwrapper_name = 'rhd_milestone' . $item_uuid . '_' . $item_version;
                //load xml wrapper from the library
                $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$cms['metadata']), $xmlwrapper_name);

                $milestone_code_xpath = '/xml/item/progression/milestone/@name_id';
                $milestone_name_xpath = '/xml/item/progression/milestone/@name';
                $milestone_due_date_xpath = '/xml/item/progression/milestone/@due_date';

                //if node alreay existed, set the node to the new value
                //else create a new node and then set node value
                $milestone_node = $this->$xmlwrapper_name->node('/xml/item/progression/milestone');

                if($this->$xmlwrapper_name->nodeExists($milestone_code_xpath))
                {
                    $this->$xmlwrapper_name->setNodeValue($milestone_code_xpath, $milestone_code);
                }
                else
                {
                    $milestone_name_id = $this->$xmlwrapper_name->createAttribute($milestone_node, "name_id");
                    $milestone_name_id->nodeValue = $milestone_code;
                }

                if($this->$xmlwrapper_name->nodeExists($milestone_name_xpath))
                {
                    $this->$xmlwrapper_name->setNodeValue($milestone_name_xpath, $milestone_name);
                }
                else
                {
                    $milestone_name_node = $this->$xmlwrapper_name->createAttribute($milestone_node, "name");
                    $milestone_name_node->nodeValue = $milestone_name;
                }

                if($this->$xmlwrapper_name->nodeExists($milestone_due_date_xpath))
                {
                    $this->$xmlwrapper_name->setNodeValue($milestone_due_date_xpath, $milestone_due_date);
                }
                else
                {
                    $milestone_due_date_node = $this->$xmlwrapper_name->createAttribute($milestone_node, "due_date");
                    $milestone_due_date_node->nodeValue = $milestone_due_date;
                }

                //get file area uuid
                $filearea_uuid = '';
                if(count($cms['attachments']) >0)
                {
                    //copy all files to a new filearea and get the file area uuid
                    $success = $this->rexrest->filesCopy($item_uuid, $item_version, $r);
                    if(!$success)
                    {
                        $error['header'] = 'failed to copy files';
                        $error['msg'] = 'item uuid:' . $item_uuid .' item version no: '. $item_version  . $this->rexrest->error;;
                        array_push($errors, $error);
                        $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                            $milestone_collection_uuid, 'N', 'C', $error['header'] . ' ' . $error['msg']);
                        continue;
                    }

                    $location = $r['headers']['location'];
                    $filearea_uuid = substr($location, strpos($location, 'file')+5, 36);
                }

                $cms['metadata'] = $this->$xmlwrapper_name->__toString();

                $success = $this->rexrest->editItem($item_uuid, $item_version, $cms, $r_new, $filearea_uuid);
                if(!$success)
                {
                    $error['header'] = 'failed to edit item';
                    $error['msg'] = 'item uuid:' . $item_uuid .' item version no: '. $item_version  . $this->rexrest->error;;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $milestone_collection_uuid, 'N', 'E', $error['header'] . ' ' . $error['msg']);
                    continue;
                }
            }

            $counter++;
        }

        $result = array();
        $result['errors'] = $errors;
        $result['counter'] = $counter;
        return $result;
    }

	public function single_student($fan)
    {
        try {
            $students = $this->cs_model->get_vw_rhd_mod_student($fan);

            /* ------------------------- 
            echo "<pre>";
            echo "<h3>Student</h3>";
            print_r($students);
            echo "</pre>";
           // exit;
           */  
            
            
            $result = $this->update_student($students);
            
            
            
            $status = count($result['errors']) > 0 ? 'PS' : 'S';
            $message = 'Completed: ' . $result['milestone_counter'] . ' students updated in RHD Milestone, '
                . $result['submit_thesis_counter'] . ' students updated in RHD Submit Thesis, '
                . count($result['errors']) . ' errors';
            $this->cs_model->populate_daily_import('students', $status, $message);
            $this->send_email($result['errors'], $result['milestone_counter'], $result['submit_thesis_counter'], $status, 'Student Data Update');
        } catch (Exception $e) {
            $errors = array();
            $error['header'] = 'unexpected exception occurred';
            $error['msg'] = $e->getMessage();
            array_push($errors, $error);
            $this->cs_model->populate_daily_import('students', 'E', $e->getMessage());
            $this->send_email($errors, $result['milestone_counter'], $result['submit_thesis_counter'], 'E', 'Student Data Update');
        }

        return $errors;
    }

    private function update_student($student_array)
    {
        $milestone_counter = 0;
        $submit_thesis_counter = 0;
        $errors = array();

        foreach ($student_array as $student) {
            $fan = $student['stu_fan'];
            $title = trim($student['thesis_title'], ';');
            $expected_submission_date = $student['est_submit_date'];
            $family_name = $student['family_name'];
            $given_name = $student['given_name'];
            $course_code = $student['course_code'];
            $course_title = $student['course_title'];
            $topic_code = $student['topic_code'];
            $topic_status = $student['topic_status'];
            $org_unit = $student['school_org_num'];
            $org_unit_name = $student['school_name'];
            $reported_eftsl = (string)$student['reported_eftsl'];
            $unreported_eftsl = (string)$student['unreported_eftsl'];
            $total_eftsl = (string)$student['total_eftsl'];
            $load = $student['load'];
            $liability_category = $student['liability_category'];
            $residency_type = $student['residency'];
            $student_id = (string)$student['stu_id'];
            $start_date = $student['start_date'];
            $end_date = $student['fec_date'];
            $last_mod = strtotime($student['last_mod_ts']);
            

              /* -------------------------  
              echo "<pre>";
              echo "<h3>Last modified (time / time stamp)</h3>";
              echo $last_mod . " / " . $student['last_mod_ts'];
              echo "</pre>";
              #exit;
              */

             

            // Update RHD Milestone collection
            $milestone_uuid = $this->config->item('milestone_collection');
            $milestone = $this->cs_model->get_stu_milestone($milestone_uuid, $fan);
  
  
            /* ------------------------- 
                   
            echo "<pre>";
            echo "<h3>Last update in db</h3>";
            echo $student['last_mod_ts'];
            echo "</pre>";

            echo "<pre>";
            echo "<h3>Milestones</h3>";
            print_r($milestone);
            echo "</pre>";


            echo "<pre>";
            echo "<h3>Last update in equella</h3>";
            echo $milestone[0]['student_update'];
            echo "</pre>";

            #exit;
            */

             

            if ($milestone != 0) {
                $item_uuid = $milestone[0]['item_uuid'];
                $item_version = $milestone[0]['item_version'];
                $item_status = $milestone[0]['item_status'];
                $item_name = $milestone[0]['item_name'];

                $result = $this->validate_params($item_uuid,$item_version);
                if (!$result) {
                    $error['header'] = 'invalid item uuid or version no.';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $milestone_uuid, 'N', 'I', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $isLocked = $this->rexrest->getLock($item_uuid, $item_version, $res);
                if ($isLocked) {
                    $error['header'] = 'item locked for editing';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $milestone_uuid, 'N', 'L', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $success = $this->rexrest->getItem($item_uuid, $item_version, $response);
                if (!$success) {
                    $error['header'] = 'error in getting item';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version . $this->rexrest->error;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $milestone_uuid, 'N', 'G', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                unset($response['headers']);
                $cms = $response;

                /* -------------------------  
                echo "<pre>";
                echo "<h3>Metadata string from getItem</h3>";
                
                print_r($cms);
                echo "</pre>";
                */




                $xmlwrapper_milestone = 'rhd_milestone' . $item_uuid . '_' . $item_version;
                $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$cms['metadata']), $xmlwrapper_milestone);

                
                
                // 05 JAN 18 New code from AC 
                // Check for existence of data_update node
                // If node doesn't exist, create it
                
                $data_update = '/xml/item/data_update';
                if (!($this->$xmlwrapper_milestone->nodeExists($data_update))) {
                    $this->$xmlwrapper_milestone->createNodeFromXPath("/xml/item/data_update");
                }
                

                // 05 JAN 18 New code from AC 
				// set a timestamp value
                $current_timestamp = date('Y-m-d H:i:s');

                $now = strtotime($current_timestamp);

                // 05 JAN 18 New code from AC 
                // Check for existence of student update node
                // If node doesn't exist, create it

                                
                $student_update_xpath = '/xml/item/data_update/student';
                if (!($this->$xmlwrapper_milestone->nodeExists($student_update_xpath))) {
                    $this->$xmlwrapper_milestone->createNodeFromXPath("/xml/item/data_update/student");

                } else { // node exists, should have a value

                    $student_updated = $this->$xmlwrapper_milestone->nodeValue("/xml/item/data_update/student");
                    
                 }



                if (!preg_match('/[1-9]/', $milestone[0]['student_update'])) {
                    $flex_student_update = 0;
                 }
                else {
                    $flex_student_update = strtotime($milestone[0]['student_update']);
                }

                 

                // New code AC 11 JAN 18
                // Check if Last update in db (lastmod) > Last update in equella (strtotime($milestone[0]['student_update'])
                // If TRUE, set the values and update Equella
                // 

                /* -------------------------  
                echo "<pre>";
                echo "<h3>Flex update time (time / time stamp)</h3>";
                echo $flex_student_update . " / " .$milestone[0]['student_update'];

                echo "<h3>Last mod > Flex update (boolean)</h3>";
                echo $last_mod > $flex_student_update;
                echo "</pre>";
                #exit;
                */

               
                
                
                $thesis_title_xpath = '/xml/item/thesis/title';
                $this->$xmlwrapper_milestone->setNodeValue($thesis_title_xpath, $title);

                $expected_submission_date_xpath = '/xml/item/thesis/expected_submission_date';
                $this->$xmlwrapper_milestone->setNodeValue($expected_submission_date_xpath, $expected_submission_date);

                $student_id_xpath = '/xml/item/people/candidate/id';
                $this->$xmlwrapper_milestone->setNodeValue($student_id_xpath, $student_id);

                $family_name_xpath = '/xml/item/people/candidate/last_names';
                $this->$xmlwrapper_milestone->setNodeValue($family_name_xpath, $family_name);

                $given_name_xpath = '/xml/item/people/candidate/first_names';
                $this->$xmlwrapper_milestone->setNodeValue($given_name_xpath, $given_name);

                $end_date_xpath = '/xml/item/candidature/RTScheme/end_date';
                $this->$xmlwrapper_milestone->setNodeValue($end_date_xpath, $end_date);

                $course_code_xpath = '/xml/item/curriculum/courses/course/code';
                $this->$xmlwrapper_milestone->setNodeValue($course_code_xpath, $course_code);

                $course_title_xpath = '/xml/item/curriculum/courses/course/name';
                $this->$xmlwrapper_milestone->setNodeValue($course_title_xpath, $course_title);

                $topic_code_xpath = '/xml/item/curriculum/topics/topic/code';
                $this->$xmlwrapper_milestone->setNodeValue($topic_code_xpath, $topic_code);

                $org_unit_xpath = '/xml/item/curriculum/topics/topic/org_unit';
                $this->$xmlwrapper_milestone->setNodeValue($org_unit_xpath, $org_unit);

                $org_unit_name_xpath = '/xml/item/curriculum/topics/topic/org_unit_name';
                $this->$xmlwrapper_milestone->setNodeValue($org_unit_name_xpath, $org_unit_name);

                $reported_eftsl_xpath = '/xml/item/candidature/eftsl/reported';
                $this->$xmlwrapper_milestone->setNodeValue($reported_eftsl_xpath, $reported_eftsl);

                $unreported_eftsl_xpath = '/xml/item/candidature/eftsl/unreported';
                $this->$xmlwrapper_milestone->setNodeValue($unreported_eftsl_xpath, $unreported_eftsl);

                $total_eftsl_xpath = '/xml/item/candidature/eftsl/total';
                $this->$xmlwrapper_milestone->setNodeValue($total_eftsl_xpath, $total_eftsl);

                $load_xpath = '/xml/item/candidature/load/FTE_type';
                $this->$xmlwrapper_milestone->setNodeValue($load_xpath, $load);


                $liability_category_xpath = '/xml/item/candidature/RTScheme/category';
                $this->$xmlwrapper_milestone->setNodeValue($liability_category_xpath, $liability_category);

                $residency_type_xpath = '/xml/item/candidature/@residency_type';
                if ($this->$xmlwrapper_milestone->nodeExists($residency_type_xpath)) {
                    $this->$xmlwrapper_milestone->setNodeValue($residency_type_xpath, $residency_type);
                } else {
                    $residency_type_parent_xpath = $this->$xmlwrapper_milestone->node("/xml/item/candidature");
                    $topic_status_node = $this->$xmlwrapper_milestone->createAttribute($residency_type_parent_xpath, 'residency_type');
                    $this->$xmlwrapper_milestone->createTextNode($topic_status_node, $residency_type);
                }

                $topic_status_xpath = '/xml/item/candidature/@status';
                if ($this->$xmlwrapper_milestone->nodeExists($topic_status_xpath)) {
                    $this->$xmlwrapper_milestone->setNodeValue($topic_status_xpath, $topic_status);
                } else {
                    $topic_code_parent_xpath = $this->$xmlwrapper_milestone->node("/xml/item/candidature");
                    $topic_status_node = $this->$xmlwrapper_milestone->createAttribute($topic_code_parent_xpath, 'status');
                    $this->$xmlwrapper_milestone->createTextNode($topic_status_node, $topic_status);
                }

                $start_date_xpath = '/xml/item/candidature/@start_date';
                if ($this->$xmlwrapper_milestone->nodeExists($start_date_xpath)) {
                    $this->$xmlwrapper_milestone->setNodeValue($start_date_xpath, $start_date);
                } else {
                    $start_date_parent_xpath = $this->$xmlwrapper_milestone->node('/xml/item/candidature');
                    $start_date_node = $this->$xmlwrapper_milestone->createAttribute($start_date_parent_xpath, 'start_date');
                    $this->$xmlwrapper_milestone->createTextNode($start_date_node, $start_date);
                }

                if ($last_mod > $flex_student_update) {         
                    $this->$xmlwrapper_milestone->setNodeValue($student_update_xpath, $current_timestamp);
                }
                
                $cms['metadata'] = $this->$xmlwrapper_milestone->__toString();

                /* -------------------------  
                echo "<pre>";
                echo "<h3>Metadata string for update</h3>";
                
                print_r($cms);
                echo "</pre>";



                #exit;
                */

                   

                if ($last_mod > $flex_student_update) {

                    #$this->$xmlwrapper_milestone->setNodeValue($student_update, $current_timestamp);

                    // perform the update

                                
                $success = $this->rexrest->editItem($item_uuid, $item_version, $cms, $new_response);
                if (!$success) {
                    $error['header'] = 'failed to edit item';
                    $error['msg'] = 'item uuid:' . $item_uuid .' item version no: '. $item_version  . $this->rexrest->error;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $milestone_uuid, 'N', 'E', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $milestone_counter++;

                }


            }

            // Update RHD Submit Thesis collection
            $submit_thesis_uuid = $this->config->item('examination_collection');
            $submit_thesis = $this->cs_model->get_stu_milestone($submit_thesis_uuid, $fan);
            if ($submit_thesis !== 0) {
                $item_uuid = $submit_thesis[0]['item_uuid'];
                $item_version = $submit_thesis[0]['item_version'];
                $current_title = $submit_thesis[0]['title'];

                $result = $this->validate_params($item_uuid,$item_version);
                if (!$result) {
                    $error['header'] = 'invalid item uuid or version no.';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $submit_thesis_uuid, 'Y', 'I', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $isLocked = $this->rexrest->getLock($item_uuid, $item_version, $res);
                if ($isLocked) {
                    $error['header'] = 'item locked for editing';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $submit_thesis_uuid, 'N', 'E', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $success = $this->rexrest->getItem($item_uuid, $item_version, $response);
                if (!$success) {
                    $error['header'] = 'error in getting item';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version . $this->rexrest->error;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $submit_thesis_uuid, 'N', 'G', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                unset($response['headers']);
                $cms = $response;

                $xmlwrapper_submit_thesis = 'rhd_submit_thesis' . $item_uuid . '_' . $item_version;
                $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$cms['metadata']), $xmlwrapper_submit_thesis);
                
                
                // 05 JAN 18 New code from AC 
                // Check for existence of data_update node
                // If node doesn't exist, create it
                
                $data_update = '/xml/item/data_update';
                if (!($this->$xmlwrapper_submit_thesis->nodeExists($data_update))) {
                    //echo $data_update . " :: node not exists";
                    $this->$xmlwrapper_submit_thesis->createNodeFromXPath("/xml/item/data_update");
                }
                
                // 05 JAN 18 New code from AC 
                // Check for existence of student update node
                // If node doesn't exist, create it
                
                $student_update = '/xml/item/data_update/student';
                if (!($this->$xmlwrapper_submit_thesis->nodeExists($student_update))) {
                    $this->$xmlwrapper_submit_thesis->createNodeFromXPath("/xml/item/data_update/student");
                }


				// 05 JAN 18 New code from AC 
				// set a timestamp value
                $current_timestamp = date('Y-m-d H:i:s');
                

                $student_update_xpath = '/xml/item/data_update/student';
                if (!($this->$xmlwrapper_submit_thesis->nodeExists($student_update_xpath))) {
                    $this->$xmlwrapper_submit_thesis->createNodeFromXPath("/xml/item/data_update/student");

                } else { // node exists, should have a value

                    $student_updated = $this->$xmlwrapper_submit_thesis->nodeValue("/xml/item/data_update/student");
           

                }
				
                $flex_student_update = strtotime($milestone[0]['student_update']);

                // New code AC 11 JAN 18
                // Check if Last update in db (lastmod) > Last update in equella (strtotime($milestone[0]['student_update'])
                // If TRUE, set the values and update Equella
                // 

                
				if ($last_mod > $flex_student_update) {
				// set the value
                    $this->$xmlwrapper_submit_thesis->setNodeValue($student_update_xpath, $current_timestamp);
                }
                

                $thesis_title_xpath = '/xml/item/thesis/title';
                $this->$xmlwrapper_submit_thesis->setNodeValue($thesis_title_xpath, $title);

                $previous_title_xpath = '/xml/item/thesis/previous_title';
                $this->$xmlwrapper_submit_thesis->setNodeValue($previous_title_xpath, $current_title);

                $expected_submission_date_xpath = '/xml/item/thesis/expected_submission_date';
                $this->$xmlwrapper_submit_thesis->setNodeValue($expected_submission_date_xpath, $expected_submission_date);

                $student_id_xpath = '/xml/item/people/candidate/id';
                $this->$xmlwrapper_submit_thesis->setNodeValue($student_id_xpath, $student_id);

                $family_name_xpath = '/xml/item/people/candidate/last_names';
                $this->$xmlwrapper_submit_thesis->setNodeValue($family_name_xpath, $family_name);

                $given_name_xpath = '/xml/item/people/candidate/first_names';
                $this->$xmlwrapper_submit_thesis->setNodeValue($given_name_xpath, $given_name);

                $end_date_xpath = '/xml/item/candidature/RTScheme/end_date';
                $this->$xmlwrapper_submit_thesis->setNodeValue($end_date_xpath, $end_date);

                $course_code_xpath = '/xml/item/curriculum/courses/course/code';
                $this->$xmlwrapper_submit_thesis->setNodeValue($course_code_xpath, $course_code);

                $course_title_xpath = '/xml/item/curriculum/courses/course/name';
                $this->$xmlwrapper_submit_thesis->setNodeValue($course_title_xpath, $course_title);

                $topic_code_xpath = '/xml/item/curriculum/topics/topic/code';
                $this->$xmlwrapper_submit_thesis->setNodeValue($topic_code_xpath, $topic_code);

                $org_unit_xpath = '/xml/item/curriculum/topics/topic/org_unit';
                $this->$xmlwrapper_submit_thesis->setNodeValue($org_unit_xpath, $org_unit);

                $org_unit_name_xpath = '/xml/item/curriculum/topics/topic/org_unit_name';
                $this->$xmlwrapper_submit_thesis->setNodeValue($org_unit_name_xpath, $org_unit_name);

                $reported_eftsl_xpath = '/xml/item/candidature/eftsl/reported';
                $this->$xmlwrapper_submit_thesis->setNodeValue($reported_eftsl_xpath, $reported_eftsl);

                $unreported_eftsl_xpath = '/xml/item/candidature/eftsl/unreported';
                $this->$xmlwrapper_submit_thesis->setNodeValue($unreported_eftsl_xpath, $unreported_eftsl);

                $total_eftsl_xpath = '/xml/item/candidature/eftsl/total';
                $this->$xmlwrapper_submit_thesis->setNodeValue($total_eftsl_xpath, $total_eftsl);

                $load_xpath = '/xml/item/candidature/load/FTE_type';
                $this->$xmlwrapper_submit_thesis->setNodeValue($load_xpath, $load);


                $liability_category_xpath = '/xml/item/candidature/RTScheme/category';
                $this->$xmlwrapper_submit_thesis->setNodeValue($liability_category_xpath, $liability_category);

                $residency_type_xpath = '/xml/item/candidature/@residency_type';
                if ($this->$xmlwrapper_submit_thesis->nodeExists($residency_type_xpath)) {
                    $this->$xmlwrapper_submit_thesis->setNodeValue($residency_type_xpath, $residency_type);
                } else {
                    $residency_type_parent_xpath = $this->$xmlwrapper_submit_thesis->node("/xml/item/candidature");
                    $topic_status_node = $this->$xmlwrapper_submit_thesis->createAttribute($residency_type_parent_xpath, 'residency_type');
                    $this->$xmlwrapper_submit_thesis->createTextNode($topic_status_node, $residency_type);
                }

                $topic_status_xpath = '/xml/item/candidature/@status';
                if ($this->$xmlwrapper_submit_thesis->nodeExists($topic_status_xpath)) {
                    $this->$xmlwrapper_submit_thesis->setNodeValue($topic_status_xpath, $topic_status);
                } else {
                    $topic_code_parent_xpath = $this->$xmlwrapper_submit_thesis->node("/xml/item/candidature");
                    $topic_status_node = $this->$xmlwrapper_submit_thesis->createAttribute($topic_code_parent_xpath, 'status');
                    $this->$xmlwrapper_submit_thesis->createTextNode($topic_status_node, $topic_status);
                }

                $start_date_xpath = '/xml/item/candidature/@start_date';
                if ($this->$xmlwrapper_submit_thesis->nodeExists($start_date_xpath)) {
                    $this->$xmlwrapper_submit_thesis->setNodeValue($start_date_xpath, $start_date);
                } else {
                    $start_date_parent_xpath = $this->$xmlwrapper_submit_thesis->node('/xml/item/candidature');
                    $start_date_node = $this->$xmlwrapper_submit_thesis->createAttribute($start_date_parent_xpath, 'start_date');
                    $this->$xmlwrapper_submit_thesis->createTextNode($start_date_node, $start_date);
                }

                $cms['metadata'] = $this->$xmlwrapper_submit_thesis->__toString();

                 /* -------------------------  
                echo "<pre>";
                echo "<h3>Metadata string for update in RHD Examination</h3>";
                
                print_r($cms);
                echo "</pre>";
                #exit;
                */
                


                if ($last_mod > $flex_student_update) {
                $success = $this->rexrest->editItem($item_uuid, $item_version, $cms, $new_response);
                if (!$success) {
                    $error['header'] = 'failed to edit item';
                    $error['msg'] = 'item uuid:' . $item_uuid .' item version no: '. $item_version  . $this->rexrest->error;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $fan, $item_uuid, $item_version,
                        $submit_thesis_uuid, 'N', 'E', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $submit_thesis_counter++;

            }
            }
        }

        $result = array();
        $result['errors'] = $errors;
        $result['milestone_counter'] = $milestone_counter;
        $result['submit_thesis_counter'] = $submit_thesis_counter;
        return $result;
    }

	public function students()
    {
        try {
            $students = $this->cs_model->vw_rhd_mod_students();

            
            
            $result = $this->update_student($students);
            $status = count($result['errors']) > 0 ? 'PS' : 'S';
            $message = 'Completed: ' . $result['milestone_counter'] . ' students updated in RHD Milestone, '
                . $result['submit_thesis_counter'] . ' students updated in RHD Submit Thesis, '
                . count($result['errors']) . ' errors';
            $this->cs_model->populate_daily_import('students', $status, $message);
            $this->send_email($result['errors'], $result['milestone_counter'], $result['submit_thesis_counter'], $status, 'Student Data Update');
        } catch (Exception $e) {
            $errors = array();
            $error['header'] = 'unexpected exception occurred';
            $error['msg'] = $e->getMessage();
            array_push($errors, $error);
            $this->cs_model->populate_daily_import('students', 'E', $e->getMessage());
            $this->send_email($errors, $result['milestone_counter'], $result['submit_thesis_counter'], 'E', 'Student Data Update');
        }

        return $errors;
    }


    public function single_coordinator($fan)
    {
        try {
           
            
            $coordinators = $this->cs_model->get_vw_rhd_postgradcoord_students($fan);
            
            /* ---------------  */
            echo "<pre>";
            
            echo "<h3>Coordinator</h3>";
            print_r($coordinators);
            echo "</pre>";
            exit; 

            
            $result = $this->update_coordinator($coordinators);
            $status = count($result['errors']) > 0 ? 'PS' : 'S';
            $message = 'Completed: ' . $result['milestone_counter'] . ' coordinators updated in RHD Milestone, '
                . $result['submit_thesis_counter'] . ' coordinators updated in RHD Submit Thesis, '
                . count($result['errors']) . ' errors';
            $this->cs_model->populate_daily_import('stu_supv', $status, $message);
            $this->send_email($result['errors'], $result['milestone_counter'], $result['submit_thesis_counter'], $status, 'Supervisor Data Update');
        } catch (Exception $e) {
            $errors = array();
            $error['header'] = 'unexpected exception occurred';
            $error['msg'] = $e->getMessage();
            array_push($errors, $error);
            $this->cs_model->populate_daily_import('stu_supv', 'E', $e->getMessage());
            $this->send_email($errors, $result['milestone_counter'], $result['submit_thesis_counter'], 'E', 'Supervisor Data Update');
        }

        return $errors;
    }



    public function coordinators()
    {
        try {
           
            
            $coordinators = $this->cs_model->get_vw_rhd_postgradcoord_students_all();
            
            /* ---------------  */
            echo "<pre>";
            
            echo "<h3>All Coordinators</h3>";
            print_r($coordinators);
            echo "</pre>";
            exit; 

            
            $result = $this->update_coordinator($coordinators);
            $status = count($result['errors']) > 0 ? 'PS' : 'S';
            $message = 'Completed: ' . $result['milestone_counter'] . ' coordinators updated in RHD Milestone, '
                . $result['submit_thesis_counter'] . ' coordinators updated in RHD Submit Thesis, '
                . count($result['errors']) . ' errors';
            $this->cs_model->populate_daily_import('stu_supv', $status, $message);
            $this->send_email($result['errors'], $result['milestone_counter'], $result['submit_thesis_counter'], $status, 'Supervisor Data Update');
        } catch (Exception $e) {
            $errors = array();
            $error['header'] = 'unexpected exception occurred';
            $error['msg'] = $e->getMessage();
            array_push($errors, $error);
            $this->cs_model->populate_daily_import('stu_supv', 'E', $e->getMessage());
            $this->send_email($errors, $result['milestone_counter'], $result['submit_thesis_counter'], 'E', 'Supervisor Data Update');
        }

        return $errors;
    }
    
   
    public function single_supervisor($fan)
    {
        try {
           

            $duplicates = $this->cs_model->get_duplicated_principal_by_fan($fan);
            $supervisors = $this->cs_model->get_vw_rhd_mod_stusupv($fan);


            /* --------------------------------------  
            echo "<pre>";
            echo "<h3>Supervisor(s)</h3>";
            print_r($supervisors);
            echo "</pre>";
            #exit;
            */

              
            
            $result = $this->update_supervisor($duplicates, $supervisors);
            $status = count($result['errors']) > 0 ? 'PS' : 'S';
            $message = 'Completed: ' . $result['milestone_counter'] . ' supervisors updated in RHD Milestone, '
                . $result['submit_thesis_counter'] . ' supervisors updated in RHD Submit Thesis, '
                . count($result['errors']) . ' errors';
            $this->cs_model->populate_daily_import('stu_supv', $status, $message);
            $this->send_email($result['errors'], $result['milestone_counter'], $result['submit_thesis_counter'], $status, 'Supervisor Data Update');
        } catch (Exception $e) {
            $errors = array();
            $error['header'] = 'unexpected exception occurred';
            $error['msg'] = $e->getMessage();
            array_push($errors, $error);
            $this->cs_model->populate_daily_import('stu_supv', 'E', $e->getMessage());
            $this->send_email($errors, $result['milestone_counter'], $result['submit_thesis_counter'], 'E', 'Supervisor Data Update');
        }

        return $errors;
    }

    private function update_supervisor($duplicates, $supervisors)
    {
        $milestone_counter = 0;
        $submit_thesis_counter = 0;
        $errors = array();
        $student_supervisors = array();
        foreach ($supervisors as $supervisor) {
            $stu_fan = $supervisor['stu_fan'];
            $student_supervisors[$stu_fan][] = $supervisor;
        }

        foreach ($student_supervisors as $stu_fan => $supervisor_array) {
            
            $error['msg'] = 'Find principal for Student FAN: ' . $stu_fan;
            $principal = $this->cs_model->get_principal($stu_fan);


            $last_mod = strtotime($principal[0]['last_mod_ts']);


        /* --------------------------   
            echo "<pre>";
            echo "<h3>Principal Supervisor</h3>";
            print_r($principal);
            echo "</pre>";
*/
            echo "<pre>";
            echo "<h3>Last update in db</h3>";
            echo $principal[0]['last_mod_ts'];
            echo "</pre>";
            #exit;

           
        
        

            if (count($principal) === 0) {
                $error['header'] = 'No Principal Supervisor found.';
                $error['msg'] = 'Student FAN: ' . $stu_fan;
                array_push($errors, $error);
                continue;
            }

            foreach ($duplicates as $duplicate) {
                if (strcasecmp($stu_fan, $duplicate['stu_fan']) == 0) {
                    $error['header'] = 'Duplicated Principal Supervisor found.';
                    $error['msg'] = 'Student FAN: ' . $stu_fan;
                    echo "<pre>";
            echo "<h3Duplicate</h3>";
            print_r($error);
            echo "</pre>";


                    array_push($errors, $error);
                    continue 2;
                }
            }

            // Update supervisors in RHD Milestone collection
            $milestone_uuid = $this->config->item('milestone_collection');
            $milestone = $this->cs_model->get_stu_milestone($milestone_uuid, $stu_fan);

            /* -------------------------  
            echo "<pre>";
            echo "<h3>Milestones</h3>";
            print_r($milestone);
            echo "</pre>";
            #exit; 

           
 */
            

            

            if ($milestone != 0) {
                $item_uuid = $milestone[0]['item_uuid'];
                $item_version = $milestone[0]['item_version'];
                $item_status = $milestone[0]['item_status'];
                $item_name = $milestone[0]['item_name'];

                $result = $this->validate_params($item_uuid, $item_version);
                if (!$result) {
                    $error['header'] = 'invalid item uuid or version no.';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $stu_fan, $item_uuid, $item_version,
                        $milestone_uuid, 'N', 'I', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $isLocked = $this->rexrest->getLock($item_uuid, $item_version, $res);
                if ($isLocked) {
                    $error['header'] = 'item locked for editing';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $stu_fan, $item_uuid, $item_version,
                        $milestone_uuid, 'N', 'L', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $success = $this->rexrest->getItem($item_uuid, $item_version, $response);
                if (!$success) {
                    $error['header'] = 'error in getting item';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $stu_fan, $item_uuid, $item_version,
                        $milestone_uuid, 'N', 'G', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                unset($response['headers']);
                $cms = $response;
                $xmlwrapper_milestone = 'rhd_milestone' . $item_uuid . '_' . $item_version;
                $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$cms['metadata']), $xmlwrapper_milestone);

                $supervisors_root_xpath = '/xml/item/people/supervisors';
                if ($this->$xmlwrapper_milestone->nodeExists($supervisors_root_xpath)) {
                    $this->$xmlwrapper_milestone->deleteNodeFromXPath($supervisors_root_xpath);
                }

                $supervisor_node = $this->$xmlwrapper_milestone->createNodeFromXPath("/xml/item/people/supervisors");
                
                foreach ($supervisor_array as $key => $supv) {
                    $node_root = $this->$xmlwrapper_milestone->createNode($supervisor_node, 'supervisor');
                    $node_role = $this->$xmlwrapper_milestone->createNode($node_root, 'role');
                    $node_fan = $this->$xmlwrapper_milestone->createNode($node_root, "fan");
                    $node_title = $this->$xmlwrapper_milestone->createNode($node_root, "title");
                    $node_first_name = $this->$xmlwrapper_milestone->createNode($node_root, "first_names");
                    $node_last_name = $this->$xmlwrapper_milestone->createNode($node_root, "last_names");

                    $node_role->nodeValue = $supv['supv_role'];
                    $node_fan->nodeValue = $supv['supv_fan'];
                    $node_title->nodeValue = $supv['title'];
                    $node_first_name->nodeValue = $supv['given_name'];
                    $node_last_name->nodeValue = $supv['family_name'];
                }


                // set a timestamp value
                $current_timestamp = date('Y-m-d H:i:s');

                $now = strtotime($current_timestamp);
                
                // 08 JAN 18 New code from AC 
                // Check for existence of data_update node
                // If node doesn't exist, create it
                
                $data_update = '/xml/item/data_update';
                if (!($this->$xmlwrapper_milestone->nodeExists($data_update))) {
                    $this->$xmlwrapper_milestone->createNodeFromXPath("/xml/item/data_update");
                }
                
                // 08 JAN 18 New code from AC 
                // Check for existence of student update node
                // If node doesn't exist, create it
                
                $supervisor_update = '/xml/item/data_update/supervisors';
                if (!($this->$xmlwrapper_milestone->nodeExists($supervisor_update))) {
                    $this->$xmlwrapper_milestone->createNodeFromXPath("/xml/item/data_update/supervisors");
                }

                if (!preg_match('/[1-9]/', $milestone[0]['supervisor_update'])) {
                    $flex_supervisor_update = 0;
                 }
                else {
                    $flex_supervisor_update = strtotime($milestone[0]['supervisor_update']);
                }


                /* ------------------------- 
                echo "<pre>";
                echo "<h3>Flex update time (time / time stamp)</h3>";
                echo $flex_supervisor_update . " / " .$milestone[0]['supervisor_update'];

                echo "<h3>Last mod > Flex update (boolean)</h3>";
                echo $last_mod > $flex_supervisor_update;
                echo "</pre>";
                #exit;

                 */

                
                
           

                // update metadata if lsat mod in db is newer

                if ($last_mod > $flex_supervisor_update)
                {
                    // set the value
                    $this->$xmlwrapper_milestone->setNodeValue($supervisor_update, $current_timestamp);

                }
                
                
                $cms['metadata'] = $this->$xmlwrapper_milestone->__toString();


                /* -------------------------  
                echo "<pre>";
                echo "<h3>Metadata string for update</h3>";
                
                print_r($cms);
                echo "</pre>";
                #exit;

                */
                
                
                
                if ($last_mod > $flex_supervisor_update)
                {
                    // edit the item
                    $success = $this->rexrest->editItem($item_uuid, $item_version, $cms, $new_response);
                    if (!$success) {
                        $error['header'] = 'failed to edit item';
                        $error['msg'] = 'item uuid:' . $item_uuid .' item version no: '. $item_version  . $this->rexrest->error;
                        array_push($errors, $error);
                        $this->cs_model->insert_error_item($item_name, $item_status, $stu_fan, $item_uuid, $item_version,
                        $milestone_uuid, 'N', 'E', $error['header'] . ' ' . $error['msg']);
                        continue;
                }

                    $milestone_counter++;

                }

                
            }

            // Update supervisors in RHD Submit Thesis collection
            $submit_thesis_uuid = $this->config->item('examination_collection');
            $thesis = $this->cs_model->get_stu_milestone($submit_thesis_uuid, $stu_fan);

            /* ------------------------- 
            echo "<pre>";
            echo "<h3>Thesis Milestones</h3>";
            print_r($thesis);
            echo "</pre>";
            #exit; 

             */
            



            if ($thesis != 0) {
                $item_uuid = $thesis[0]['item_uuid'];
                $item_version = $thesis[0]['item_version'];
                $result = $this->validate_params($item_uuid, $item_version);
                if (!$result) {
                    $error['header'] = 'invalid item uuid or version no.';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $stu_fan, $item_uuid, $item_version,
                        $submit_thesis_uuid, 'N', 'I', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $isLocked = $this->rexrest->getLock($item_uuid, $item_version, $res);
                if ($isLocked) {
                    $error['header'] = 'item locked for editing';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version . $this->rexrest->error;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $stu_fan, $item_uuid, $item_version,
                        $submit_thesis_uuid, 'N', 'L', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                $success = $this->rexrest->getItem($item_uuid, $item_version, $response);
                if (!$success) {
                    $error['header'] = 'error in getting item';
                    $error['msg'] = 'item uuid:' . $item_uuid . ' item version no: ' . $item_version . $this->rexrest->error;
                    array_push($errors, $error);
                    $this->cs_model->insert_error_item($item_name, $item_status, $stu_fan, $item_uuid, $item_version,
                        $submit_thesis_uuid, 'N', 'G', $error['header'] . ' ' . $error['msg']);
                    continue;
                }

                unset($response['headers']);
                $cms = $response;
                $xmlwrapper_thesis = 'rhd_submit_thesis' . $item_uuid . '_' . $item_version;
                $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$cms['metadata']), $xmlwrapper_thesis);

                $supervisors_root_xpath = '/xml/item/people/supervisors';
                if ($this->$xmlwrapper_thesis->nodeExists($supervisors_root_xpath)) {
                    $this->$xmlwrapper_thesis->deleteNodeFromXPath($supervisors_root_xpath);
                }

                $supervisor_node = $this->$xmlwrapper_thesis->createNodeFromXPath("/xml/item/people/supervisors");
                foreach ($supervisor_array as $key => $supv) {
                    $node_root = $this->$xmlwrapper_thesis->createNode($supervisor_node, 'supervisor');
                    $node_role = $this->$xmlwrapper_thesis->createNode($node_root, 'role');
                    $node_fan = $this->$xmlwrapper_thesis->createNode($node_root, "fan");
                    $node_title = $this->$xmlwrapper_thesis->createNode($node_root, "title");
                    $node_first_name = $this->$xmlwrapper_thesis->createNode($node_root, "first_names");
                    $node_last_name = $this->$xmlwrapper_thesis->createNode($node_root, "last_names");

                    $node_role->nodeValue = $supv['supv_role'];
                    $node_fan->nodeValue = $supv['supv_fan'];
                    $node_title->nodeValue = $supv['title'];
                    $node_first_name->nodeValue = $supv['given_name'];
                    $node_last_name->nodeValue = $supv['family_name'];
                }

                // 08 JAN 18 New code from AC 
                // Check for existence of data_update node
                // If node doesn't exist, create it
                
                $data_update = '/xml/item/data_update';
                if (!($this->$xmlwrapper_thesis->nodeExists($data_update))) {
                    $this->$xmlwrapper_thesis->createNodeFromXPath("/xml/item/data_update");
                }
                
                // 08 JAN 18 New code from AC 
                // Check for existence of student update node
                // If node doesn't exist, create it
                
                $supervisor_update = '/xml/item/data_update/supervisors';
                if (!($this->$xmlwrapper_thesis->nodeExists($supervisor_update))) {
                    $this->$xmlwrapper_thesis->createNodeFromXPath("/xml/item/data_update/supervisors");
                }

                if (!preg_match('/[1-9]/', $thesis[0]['supervisor_update'])) {
                    $flex_supervisor_update = 0;
                 }
                else {
                    $flex_supervisor_update = strtotime($thesis[0]['supervisor_update']);
                }


				// 08 JAN 18 New code from AC 
				// set a timestamp value
                $current_timestamp = date('Y-m-d H:i:s');
                

                // update metadata if lsat mod in db is newer

                if ($last_mod > $flex_supervisor_update)
                {
                    // set the value
                    $this->$xmlwrapper_thesis->setNodeValue($supervisor_update, $current_timestamp);

                }
                $cms['metadata'] = $this->$xmlwrapper_thesis->__toString();
                
                /* -------------------------  
                echo "<pre>";
                echo "<h3>Thesis Metadata string for update</h3>";
                
                print_r($cms);
                echo "</pre>";

                #exit;

                */

                


                    if ($last_mod > $flex_supervisor_update) {

                    $success = $this->rexrest->editItem($item_uuid, $item_version, $cms, $new_response);
                    if (!$success) {
                        $error['header'] = 'failed to edit item';
                        $error['msg'] = 'item uuid:' . $item_uuid .' item version no: '. $item_version  . $this->rexrest->error;
                        array_push($errors, $error);
                        $this->cs_model->insert_error_item($item_name, $item_status, $stu_fan, $item_uuid, $item_version,
                        $submit_thesis_uuid, 'N', 'E', $error['header'] . ' ' . $error['msg']);
                        continue;
                    }

                $submit_thesis_counter++;


                }
				
            }
        }

        $result = array();
        $result['errors'] = $errors;
        $result['milestone_counter'] = $milestone_counter;
        $result['submit_thesis_counter'] = $submit_thesis_counter;
        return $result;
    }

    public function supervisors()
    {
        try {
            
            /* -------------------------  
                echo "<pre>";
                echo "<h3>Authorised User</h3>";
                
                echo $_SERVER['PHP_AUTH_USER'];
                echo "</pre>";

                exit;

               */ 

            $duplicates = $this->cs_model->get_duplicated_principal();

            $supervisors = $this->cs_model->get_vw_rhd_mod_stusupv_all();

            /* -------------------------  
                echo "<pre>";
                echo "<h3>Supervisors</h3>";
                
                print_r($supervisors);
                echo "</pre>";

                exit;

                */

            $result = $this->update_supervisor($duplicates, $supervisors);
            $status = count($result['errors']) > 0 ? 'PS' : 'S';



            $message = 'Completed: ' . $result['milestone_counter'] . ' supervisors updated in RHD Milestone, '
                . $result['submit_thesis_counter'] . ' supervisors updated in RHD Submit Thesis, '
                . count($result['errors']) . ' errors';
            $this->cs_model->populate_daily_import('stu_supv', $status, $message);
            $this->send_email($result['errors'], $result['milestone_counter'], $result['submit_thesis_counter'], $status, 'Supervisor Data Update');
        } catch (Exception $e) {
            $errors = array();
            $error['header'] = 'unexpected exception occurred';
            $error['msg'] = $e->getMessage();
            array_push($errors, $error);
            $this->cs_model->populate_daily_import('stu_supv', 'E', $e->getMessage());
            $this->send_email($errors, $result['milestone_counter'], $result['submit_thesis_counter'], 'E', 'Supervisor Data Update');
        }


        return $errors;

        
            #exit;

            
    }



    public function all()
    {
        $this->milestones();
        $this->students();
        $this->supervisors();
    }

    public function get_error_items()
    {
        $errors = $this->cs_model->get_error_items();
        $data['items'] = $errors;
        $this->load->view('cs/student_update_view', $data);
        return $errors;
    }

	/**
     * Validate incoming parameters
     *
     * @param array $uuid, item UUID
     * @param array $version, item Version
     * @return validation outcome
     */
    private function validate_params($uuid, $version)
    {
        if (strcmp($uuid, 'missed') == 0 || strlen($uuid) != 36)
            return false;

        if (strcmp($version, 'missed') == 0 || !is_numeric($version))
            return false;

        return true;
    }

    private function send_email($error_array, $updated_milestone, $updated_thesis, $update_status, $name)
    {
        $error_message = '';
        foreach ($error_array as $error) {
            $error_message = $error_message . ' ' . $error['header'] . ' ' . $error['msg'] . "\r\n";
        }

        $this->email->from('DoNotReply@flinders.edu.au', 'DoNotReply@flinders.edu.au');
        $this->email->to('flex.help@flinders.edu.au');
        $this->email->subject('RHD REX Data Update Notification');
        $message = 'The ' . $name . ' was completed on ' . date("Y-m-d H:i:s") . ' with the status '
            . $update_status . '. There were ' . $updated_milestone . ' records updated in RHD Milestone, '
            . $updated_thesis . ' records updated in RHD Submit Thesis, '
            . 'Count of Error: ' . count($error_array) . ' Error List: ' . "\r\n" . $error_message;
        $this->email->message($message);
        $this->email->send();
    }

    private function check_permission($fan)
    {
        if(!$this->permission->success) {
            $errdata['message'] = 'Authentication error';
            $errdata['heading'] = "Internal error";
            $this->load->view('cs/showerror_view', $errdata);
            $this->output->_display();
            exit();
        }

        $is_authorised = $this->permission->get_rex_permission($fan);
        if (!$is_authorised) {
            $this->logger_cs->error("Error: Access restricted for " . $fan);
            $errdata['message'] = 'You are not authorised to view this page, if you think you should have the permission, please contact flex.help@flinders.edu.au';
            $errdata['heading'] = "Error";
            $this->load->view('cs/showerror_view', $errdata);
            $this->output->_display();
            exit();
        }
    }

    public function all_tables()
    {
        $tables = $this->cs_model->get_all_tables();
            
            /* ---------------  */
            echo "<pre>";
            echo "<h3>Tables</h3>";
            print_r($tables);
            echo "</pre>";
            exit; 

        return $errors;
    }


    public function not_live()
    {
        $notLive = $this->cs_model->get_stu_milestone_not_live();
            
            /* ---------------  */
            echo "<pre>";
            echo "<h3>Not Live</h3>";
            print_r($notLive);
            echo "</pre>";
            exit; 

        return $errors;
    }

    public function all_coords()
    {
        $coordinators = $this->cs_model->vw_rhd_postgrad_coord();
            
            /* ---------------  */
            echo "<pre>";
            echo "<h3>Coordinators</h3>";
            print_r($coordinators);
            echo "</pre>";
            exit; 

        return $errors;
    }

    public function all_views()
    {
        $views = $this->cs_model->get_all_views();
            
            /* ---------------  */
            echo "<pre>";
            echo "<h3>Views</h3>";
            print_r($views);
            echo "</pre>";
            exit; 

            

        return $errors;
    }
    
    public function all_supervisors()
    {
        $supervisors = $this->cs_model->get_vw_rhd_mod_stusupv_all();
            
            /* ---------------  */
            echo "<pre>";
            echo "<h3>All Supervisors</h3>";
            print_r($supervisors);
            echo "</pre>";
            exit; 

            

        return $errors;
    }

}

