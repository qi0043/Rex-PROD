<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller to show SAM in HTML or PDF format
 */
class Showmilestone extends CI_Controller
{

    /**
     * Display a Milestone in HTML or PDF format
     *
     * @param string $format, html/pdf
     * @param array $uuid, item UUID
     * @param array $version, item Version
     */
    public function index($format='html', $uuid='missed', $version='missed')
    {
        #$this->output->enable_profiler(TRUE);
        $errdata['heading'] = "Error";
		//define logger folder application/logs/cs

        if($this->validate_params($format, $uuid, $version) == false)
        {
            $errdata['message'] = "Invalid Request";
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

		      $this->getComment($uuid, $version);


        $oauth = array('oauth_client_config_name' => 'rhd');

        $this->load->helper('url');
        $this->load->library('rexrest/rexrest', $oauth);

        $ci =& get_instance();
        $ci->load->config('rex');


        $success = $this->rexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }
        $success = $this->rexrest->getItemAll($uuid, $version, $response);
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

       /* echo "<pre>";
        print_r($response);
        echo "Byebye!";
        echo "</pre>";
        exit;*/



        $attachmentInfo = $response['attachments'];

        //echo "<pre>";
//        print_r($attachmentInfo);
//        echo "Byebye!";
//        echo "</pre>";
//        exit;

		    $xmlwrapper_name = 'xmlwrapper_' . $uuid . '_' .$version;
        //pull out metadata XML

		    $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']), $xmlwrapper_name);
        $milestone_array = $this->milestoneXml2Array($this->$xmlwrapper_name, $attachmentInfo);
        $milestone_array['status'] = $response['status'];





	#log_message('error', $response['status']);

       #echo'<pre>';
       #echo 'response================<br>';
       #print_r($response);
	   #echo 'sam-array===============<br>';
       #print_r($milestone_array);
	   #echo 'responsemetadata========<br>';
       #echo (string)$response['metadata'];
       #echo'</pre><br><hr/>';

        /*
        if($this->xmlwrapper->num_node_notfound > 7)
        {
            $errdata['message'] = "Metadata missing";
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        */
        $data = array('milestone_array' => $milestone_array);

        /*echo "<pre>";
        print_r($data);
        echo "Byebye!";
        echo "</pre>";
        exit();*/

        if($format=='html')
        {
            $data['milestone_array']['format'] = 'html';

            $this->load->view('cs/milestone_view', $data);
        }
        else if($format=='pdf')
        {
            $data['milestone_array']['format'] = 'pdf';
            $data['milestone_array']['uuid'] = $uuid; #item uuid
            $data['milestone_array']['version'] = $version; #item version number
            $success = $this->rexrest->getItemHistory($uuid, $version, $re);
            if (!$success) {
                $this->logger_cs->error("getItemHistory error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
                return false;
            }
            $sup_sub_mod = array();

            for($i=0; $i<count($re); $i++)
            {
                if(isset($re[$i]['step']) && $re[$i]['step'] == '7139af5c-68e2-4003-ae15-bae141b06b05')
                {
                    $sup_sub_mod['date'] = isset($re[$i]['date']) ? $re[$i]['date'] : '';

                    $sup_sub_mod['approver_fan'] = isset($re[$i]['user']['id']) ? $re[$i]['user']['id'] : '';

                    $approver_name = '';
                    if($sup_sub_mod['approver_fan'] != '')
                    {
                        if(strlen($sup_sub_mod['approver_fan']) == 36)
                        {
                            $approver_name = 'System Administrator';
                        }
                        else
                        {
                            $this->load->library('ldap/ldap', $oauth);
                            $ldap_user = $this->ldap->get_attributes($sup_sub_mod['approver_fan']);
                            $approver_name = $ldap_user['name'];
                        }
                    }
                    $sup_sub_mod['approver_name'] = $approver_name;
                }
            }
            $data['milestone_array']['metadata']['supervisor_approval']['date'] = $sup_sub_mod['date'];
            $data['milestone_array']['metadata']['supervisor_approval']['approver_fan'] = $sup_sub_mod['approver_fan'];
            $data['milestone_array']['metadata']['supervisor_approval']['approver_name'] = $sup_sub_mod['approver_name'];

           // $data['milestone_array']['metadata']['comments'] = $comments;

      /*echo "<pre>";
        print_r($data);
        echo "Byebye!";
        echo "</pre>";
        exit;*/

            /*
            if($data['milestone_array']['metadata']['approved'] == 'Yes' && isset($response['attachments']) && isset($response['attachments'][0]))
            {
		    //if a = b, means this attachment is the signature image
		    if($response['attachments'][0]['uuid'] == $data['milestone_array']['metadata']['signature_Uuid'])
            { //attachment uuid
                  $data['milestone_array']['attachments_name'] = $response['attachments'][0]['filename']; //attachment file name

                  #generate a token for OAuth user to be able to see digital signature for 30 mintues.
                  $data['milestone_array']['url'] = $this->generateToken();
		    }

                    $ci =& get_instance();
                    $ci->load->config('flex');
                    $institute_url = $ci->config->item('institute_url');
                    $data['milestone_array']['institute_url'] = $institute_url;
            }
            */

            if(isset($data['milestone_array']['metadata']['availability_name']))
                $pdffilename = 'RHD ' . $data['milestone_array']['metadata']['availability_name'] . " ";
            else
                $pdffilename = 'RHD ';

            $pdffilename .= date("Y-m-d") . ".pdf";



            ob_start();
            $this->load->view('cs/milestone_view', $data);
            $html = ob_get_contents();
            ob_end_clean();

            //mPDF generates lots of PHP ERR logs, disable the logs here.
            #$errorlevel=error_reporting();
            #error_reporting(0);
            //See class MY_Exceptions
            #$_SESSION['GEN_PHPERR_LOGS'] = false;

            $this->load->library('pdf/pdf_class');
            #$this->pdf_class->SetDisplayMode('fullpage');
            $this->pdf_class->setFooter('{PAGENO} / {nb}');
	    #$this->pdf_class->setHeader('|STATEMENT OF ASSESSMENT METHODS – 2016|');
            #$this->pdf_class->SetAutoFont();
            $this->pdf_class->WriteHTML($html);
            $this->pdf_class->Output($pdffilename, 'I');

            #error_reporting($errorlevel);
            #$_SESSION['GEN_PHPERR_LOGS'] = true;
        }
    }

    protected function itemIsMilestone($itemXml)
    {
        $type = '/xml/item/curriculum/@item_type';

        $itemismilestone = $itemXml->nodeValue($type);
        $itemismilestone = 'MILESTONE';
        if(isset($itemismilestone) && $itemismilestone == 'MILESTONE')
            return true;
        return false;
    }



    /**
     * Extract XML data and store it in array
     *
     * @param xmlwrapper $itemXml
     */
    protected function milestoneXml2Array($itemXml, $attachmentInfo)
    {
        //information of report overview
	      $candidate_name = '/xml/item/people/candidate/display_name';
        $milestoneArray['metadata']['candidatename'] = $itemXml->nodeValue($candidate_name);

        $student_id = '/xml/item/people/candidate/id';
        $milestoneArray['metadata']['studentid'] = $itemXml->nodeValue($student_id);

        $student_fan = '/xml/item/people/candidate/fan';
        $milestoneArray['metadata']['studenfan'] = $itemXml->nodeValue($student_fan);

        $degree_code = '/xml/item/curriculum/courses/course/code';
        $milestoneArray['metadata']['degreecode'] = $itemXml->nodeValue($degree_code);
        $degree_name = '/xml/item/curriculum/courses/course/name';
        $milestoneArray['metadata']['degreename'] = $itemXml->nodeValue($degree_name);
        $milestoneArray['metadata']['degree'] = $milestoneArray['metadata']['degreecode'] . ' ' . $milestoneArray['metadata']['degreename'];

		    $award_type = '/xml/item/curriculum/courses/course/@award_type';
        $milestoneArray['metadata']['award_type'] = $itemXml->nodeValue($award_type);

        $topic_code = '/xml/item/curriculum/topics/topic/code';
        $milestoneArray['metadata']['topiccode'] = $itemXml->nodeValue($topic_code);

        $thesis_title = '/xml/item/thesis/title';
        $milestoneArray['metadata']['thesistitle'] = $itemXml->nodeValue($thesis_title);

        $school_name = '/xml/item/curriculum/topics/topic/org_unit_name';
        $milestoneArray['metadata']['schoolname'] = $itemXml->nodeValue($school_name);

        $ft_or_pt = '/xml/item/candidature/load/FTE_type';
        $milestoneArray['metadata']['fullorparttime'] = $itemXml->nodeValue($ft_or_pt);

        $request_PhD = '/xml/item/candidature/change_request/course/type';
        $milestoneArray['metadata']['requestPhD'] = $itemXml->nodeValue($request_PhD);

        $requestPhD_supervisoraccepted = '/xml/item/candidature/change_request/course/recommendation/supervisor/accepted';
        $milestoneArray['metadata']['requestPhDsupervisoraccept'] = $itemXml->nodeValue($requestPhD_supervisoraccepted);

        $eftsl = '/xml/item/candidature/eftsl/total';
        $eftslScore = $itemXml->nodeValue($eftsl);
        if(floatval($eftslScore)!=0) {
            $milestoneArray['metadata']['eftsl'] = round(floatval($eftslScore), 2);
        }

        //the information of overall report attachments
        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/progression/milestone/evidence/attachments/uuid'); $j++)
        {
            $overallreport_uuid = '/xml/item/progression/milestone/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['overallreport']['uuid'][$j] = $itemXml->nodeValue($overallreport_uuid);
            $milestoneArray['metadata']['overallreport']['name'][$j] = $this->getFileName($itemXml->nodeValue($overallreport_uuid),$attachmentInfo);
            $milestoneArray['metadata']['overallreport']['link'][$j] = $this->getFileLink($itemXml->nodeValue($overallreport_uuid),$attachmentInfo);
        }

        //the information of supervisors
        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/people/supervisors/supervisor'); $j++)
        {
            $supervisorRole = '/xml/item/people/supervisors/supervisor['.$j.']/role';
            $supervisorTitle = '/xml/item/people/supervisors/supervisor['.$j.']/title';
            $supervisorName = '/xml/item/people/supervisors/supervisor['.$j.']/display_name';
            $supervisorFan = '/xml/item/people/supervisors/supervisor['.$j.']/fan';

            $milestoneArray['metadata']['supervisorsdetail'][$j]['role'] = $itemXml->nodeValue($supervisorRole);
            $milestoneArray['metadata']['supervisorsdetail'][$j]['title'] = $itemXml->nodeValue($supervisorTitle);
            $milestoneArray['metadata']['supervisorsdetail'][$j]['name'] = $itemXml->nodeValue($supervisorName);
            $milestoneArray['metadata']['supervisorsdetail'][$j]['fan'] = $itemXml->nodeValue($supervisorFan);
        }

        $prinsupervisor_name = '/xml/item/sys_workflow/principal_supervisor_name';
        $milestoneArray['metadata']['prinsupervisorsdetail']['name'] = $itemXml->nodeValue($prinsupervisor_name);

        //the information of intermissions
        $intermission_status = '/xml/item/candidature/absences/@status';
        $milestoneArray['metadata']['intermission']['status'] = $itemXml->nodeValue($intermission_status);

        if(strnatcasecmp($milestoneArray['metadata']['intermission']['status'], 'Yes') == 0){
            for ($j = 1; $j <= $itemXml->numNodes('/xml/item/candidature/absences/episode'); $j++)
            {
                $intermissionStart = '/xml/item/candidature/absences/episode['.$j.']/start_date';
                $intermissionEnd = '/xml/item/candidature/absences/episode['.$j.']/end_date';
                $intermissionReason = '/xml/item/candidature/absences/episode['.$j.']/reason';

                $milestoneArray['metadata']['intermission'][$j]['start'] = $itemXml->nodeValue($intermissionStart);
                $milestoneArray['metadata']['intermission'][$j]['end'] = $itemXml->nodeValue($intermissionEnd);
                $milestoneArray['metadata']['intermission'][$j]['reason'] = $itemXml->nodeValue($intermissionReason);
            }
        }


        // the information of ethics
        $ethics_status = '/xml/item/research/project/research_conduct/ethics/@ref_status';
        $milestoneArray['metadata']['ethics']['status'] = $itemXml->nodeValue($ethics_status);

        if(strnatcasecmp($milestoneArray['metadata']['ethics']['status'], 'Approved') == 0) {
            $ethics_committee_name = '/xml/item/research/project/research_conduct/ethics/@ref_org';
            $milestoneArray['metadata']['ethics']['committee'] = $itemXml->nodeValue($ethics_committee_name);

            $ethics_approval_number = '/xml/item/research/project/research_conduct/ethics/@ref';
            $milestoneArray['metadata']['ethics']['number'] = $itemXml->nodeValue($ethics_approval_number);
        }

        if(strnatcasecmp($milestoneArray['metadata']['ethics']['status'], 'Applied') == 0) {
            $ethics_committee_name = '/xml/item/research/project/research_conduct/ethics/@ref_org';
            $milestoneArray['metadata']['ethics']['committee'] = $itemXml->nodeValue($ethics_committee_name);
        }

        $ethics_accepted = '/xml/item/research/project/research_conduct/ethics/reviewer/accepted';
        $milestoneArray['metadata']['ethics']['accepted'] = $itemXml->nodeValue($ethics_accepted);

        $ethics_input_text = '/xml/item/research/project/research_conduct/ethics/evidence/text';
        $milestoneArray['metadata']['ethics']['inputs']['text'] = $itemXml->nodeValue($ethics_input_text);

        $ethics_comment = '/xml/item/research/project/research_conduct/ethics/reviewer/comment';
        $milestoneArray['metadata']['ethics']['comment'] = $itemXml->nodeValue($ethics_comment);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/research/project/research_conduct/ethics/evidence/attachments/uuid'); $j++){
            $ethics_input_uuid = '/xml/item/research/project/research_conduct/ethics/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['ethics']['inputs']['uuid'][$j] = $itemXml->nodeValue($ethics_input_uuid);
            $milestoneArray['metadata']['ethics']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($ethics_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['ethics']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($ethics_input_uuid),$attachmentInfo);
        }

        //the information of coordinators
        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/people/coordinators/coordinator'); $j++)
        {
            $coordinatorRole = '/xml/item/people/coordinators/coordinator['.$j.']/role';
            $coordinatorrFirstName = '/xml/item/people/coordinators/coordinator['.$j.']/first_names';
            $coordinatorLastName = '/xml/item/people/coordinators/coordinator['.$j.']/last_names';
            $coordinatorFan = '/xml/item/people/coordinators/coordinator['.$j.']/fan';

            $milestoneArray['metadata']['coordinatorsdetail'][$j]['role'] = $itemXml->nodeValue($coordinatorRole);
            $milestoneArray['metadata']['coordinatorsdetail'][$j]['name'] = $itemXml->nodeValue($coordinatorrFirstName)." ".$itemXml->nodeValue($coordinatorLastName);
            $milestoneArray['metadata']['coordinatorsdetail'][$j]['fan'] = $itemXml->nodeValue($coordinatorFan);
        }

        $commerce_date = '/xml/item/candidature/@start_date';
        $milestoneArray['metadata']['commercedate'] = $itemXml->nodeValue($commerce_date);

        $submission_date = '/xml/item/thesis/expected_submission_date';
        $milestoneArray['metadata']['submissiondate'] = $itemXml->nodeValue($submission_date);

        $residency_type = '/xml/item/candidature/@residency_type';
        $milestoneArray['metadata']['residencytype'] = $itemXml->nodeValue($residency_type);

        if(strnatcasecmp($milestoneArray['metadata']['residencytype'], 'International') != 0) {
            $RTS_end_date = '/xml/item/candidature/RTScheme/end_date';
            $milestoneArray['metadata']['rtsenddate'] = $itemXml->nodeValue($RTS_end_date);
            $RTS_category = '/xml/item/candidature/RTScheme/category';
            $milestoneArray['metadata']['rtscategory'] = $itemXml->nodeValue($RTS_category);
        }
        else{
            $RTS_end_date = '/xml/item/candidature/RTScheme/end_date';
            $milestoneArray['metadata']['rtsenddate'] = $itemXml->nodeValue($RTS_end_date);
            $RTS_category = '/xml/item/candidature/RTScheme/category';
            $milestoneArray['metadata']['rtscategory'] = $itemXml->nodeValue($RTS_category);
        }

        $export_control = '/xml/item/research/project/restrictions/export_control';
        $milestoneArray['metadata']['exportcontrol'] = $itemXml->nodeValue($export_control);

        $third_party_agree = '/xml/item/research/project/restrictions/third_party_agreement';
        $milestoneArray['metadata']['tpagreement'] = $itemXml->nodeValue($third_party_agree);

        $studentSignOff = '/xml/item/progression/milestone/evidence_finalised';
        $milestoneArray['metadata']['studentsignoff'] = $itemXml->nodeValue($studentSignOff);

        //the information of oral communication
        $oral_skill_input_text = '/xml/item/communication/oral/evidence/text';
        $milestoneArray['metadata']['oralskill']['inputs']['text'] = $itemXml->nodeValue($oral_skill_input_text);

        $oral_skill_accepted = '/xml/item/communication/oral/reviewer/accepted';
        $milestoneArray['metadata']['oralskill']['accepted'] = $itemXml->nodeValue($oral_skill_accepted);

        $oral_skill_comment = '/xml/item/communication/oral/reviewer/comment';
        $milestoneArray['metadata']['oralskill']['comment'] = $itemXml->nodeValue($oral_skill_comment);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/communication/oral/evidence/attachments/uuid'); $j++){
            $oral_skill_input_uuid = '/xml/item/communication/oral/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['oralskill']['inputs']['uuid'][$j] = $itemXml->nodeValue($oral_skill_input_uuid);
            $milestoneArray['metadata']['oralskill']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($oral_skill_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['oralskill']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($oral_skill_input_uuid),$attachmentInfo);
        }


        //the information of written communication
        $written_skill_accepted = '/xml/item/communication/written/reviewer/accepted';
        $milestoneArray['metadata']['writtenskill']['accepted'] = $itemXml->nodeValue($written_skill_accepted);

        $written_skill_comment = '/xml/item/communication/written/reviewer/comment';
        $milestoneArray['metadata']['writtenskill']['comment'] = $itemXml->nodeValue($written_skill_comment);

        $written_skill_input_text = '/xml/item/communication/written/evidence/text';
        $milestoneArray['metadata']['writtenskill']['inputs']['text'] = $itemXml->nodeValue($written_skill_input_text);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/communication/written/evidence/attachments/uuid'); $j++){
            $written_skill_input_uuid = '/xml/item/communication/written/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['writtenskill']['inputs']['uuid'][$j] = $itemXml->nodeValue($written_skill_input_uuid);
            $milestoneArray['metadata']['writtenskill']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($written_skill_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['writtenskill']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($written_skill_input_uuid),$attachmentInfo);
        }


        //the information of professional development --- related to the value of section 5 of part B
		$professional_development_completed = '/xml/item/support/training/history/student/@identified';
		$milestoneArray['metadata']['professionaldevelopment']['completed'] = $itemXml->nodeValue($professional_development_completed);


        $additional_education_requested = '/xml/item/progression/student_request/training';
        $milestoneArray['metadata']['additionaleducation']['requested'] = $itemXml->nodeValue($additional_education_requested);

        $additional_education_comment = '/xml/item/support/training/component/reviewer/comment';
        $milestoneArray['metadata']['additionaleducation']['comment'] = $itemXml->nodeValue($additional_education_comment);


        if(strnatcasecmp($milestoneArray['metadata']['professionaldevelopment']['completed'], 'Yes') == 0) {
            $professional_development_description = '/xml/item/support/training/history/student/comment';
            $milestoneArray['metadata']['professionaldevelopment']['comment']= $itemXml->nodeValue($professional_development_description);

            for ($j = 1; $j <= $itemXml->numNodes('/xml/item/support/training/history/student/attachments/uuid'); $j++){
                $professional_development_input_uuid = '/xml/item/support/training/history/student/attachments/uuid['.$j.']';
                $milestoneArray['metadata']['professionaldevelopment']['inputs']['uuid'][$j] = $itemXml->nodeValue($professional_development_input_uuid);
                $milestoneArray['metadata']['professionaldevelopment']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($professional_development_input_uuid),$attachmentInfo);
                $milestoneArray['metadata']['professionaldevelopment']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($professional_development_input_uuid),$attachmentInfo);
            }
        }
        else {
            $milestoneArray['metadata']['professionaldevelopment']['comment'] = '';
        }

        if(strnatcasecmp($milestoneArray['metadata']['additionaleducation']['requested'], 'Yes') == 0) {
            $additional_education_description_text = '/xml/item/support/training/component/student/comment';
            $milestoneArray['metadata']['additionaleducation']['inputs']['text'] = $itemXml->nodeValue($additional_education_description_text);

            for ($j = 1; $j <= $itemXml->numNodes('/xml/item/support/training/component/description/attachments/uuid'); $j++){
                $support_education_input_uuid = '/xml/item/support/training/component/description/attachments/uuid['.$j.']';
                $milestoneArray['metadata']['additionaleducation']['inputs']['uuid'][$j] = $itemXml->nodeValue($support_education_input_uuid);
                $milestoneArray['metadata']['additionaleducation']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($support_education_input_uuid),$attachmentInfo);
                $milestoneArray['metadata']['additionaleducation']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($support_education_input_uuid),$attachmentInfo);
            }
        }
        else {
            $milestoneArray['metadata']['additionaleducation']['inputs']['text'] = '';
        }


        //the information of additional resource
        $additional_resource_requested = '/xml/item/progression/student_request/resources';
        $milestoneArray['metadata']['additionalresource']['requested'] = $itemXml->nodeValue($additional_resource_requested);

        if(strnatcasecmp($milestoneArray['metadata']['additionalresource']['requested'], 'Yes') == 0) {
            $additional_resource_description_text = '/xml/item/support/resources/resource/description/text';
            $milestoneArray['metadata']['additionalresource']['inputs']['text'] = $itemXml->nodeValue($additional_resource_description_text);

            for ($j = 1; $j <= $itemXml->numNodes('/xml/item/support/resources/resource/description/attachments/uuid'); $j++){
                $support_resource_input_uuid = '/xml/item/support/resources/resource/description/attachments/uuid['.$j.']';
                $milestoneArray['metadata']['additionalresource']['inputs']['uuid'][$j] = $itemXml->nodeValue($support_resource_input_uuid);
                $milestoneArray['metadata']['additionalresource']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($support_resource_input_uuid),$attachmentInfo);
                $milestoneArray['metadata']['additionalresource']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($support_resource_input_uuid),$attachmentInfo);
            }
        }
        else {
            $milestoneArray['metadata']['additionalresource']['inputs']['text'] = '';
        }

        $additional_resource_comment = '/xml/item/support/resources/resource/reviewer/comment';
        $milestoneArray['metadata']['additionalresource']['comment'] = $itemXml->nodeValue($additional_resource_comment);



		//the information of research questions
        $research_problem_accepted = '/xml/item/research/project/research_question/reviewer/accepted';
        $milestoneArray['metadata']['researchproblem']['accepted'] = $itemXml->nodeValue($research_problem_accepted);

        $research_problem_input_text = '/xml/item/research/project/research_question/evidence/text';
        $milestoneArray['metadata']['researchproblem']['inputs']['text'] = $itemXml->nodeValue($research_problem_input_text);

        $research_problem_comment = '/xml/item/research/project/research_question/reviewer/comment';
        $milestoneArray['metadata']['researchproblem']['comment'] = $itemXml->nodeValue($research_problem_comment);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/research/project/research_question/evidence/attachments/uuid'); $j++){
            $research_problem_input_uuid = '/xml/item/research/project/research_question/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['researchproblem']['inputs']['uuid'][$j] = $itemXml->nodeValue($research_problem_input_uuid);
            $milestoneArray['metadata']['researchproblem']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($research_problem_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['researchproblem']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($research_problem_input_uuid),$attachmentInfo);
        }


        //the information of plan
        $plan_input_text = '/xml/item/research/project/plan/evidence/text';
        $milestoneArray['metadata']['plan']['inputs']['text'] = $itemXml->nodeValue($plan_input_text);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/research/project/plan/evidence/attachments/uuid'); $j++){
            $plan_input_uuid = '/xml/item/research/project/plan/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['plan']['inputs']['uuid'][$j] = $itemXml->nodeValue($plan_input_uuid);
            $milestoneArray['metadata']['plan']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($plan_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['plan']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($plan_input_uuid),$attachmentInfo);
        }

		$plan_acceptance = '/xml/item/research/project/plan/reviewer/accepted';
		$milestoneArray['metadata']['plan']['supervisor']['review'] = $itemXml->nodeValue($plan_acceptance);
		$timeframe_acceptance = '/xml/item/research/project/plan/timeframe/reviewer/accepted';
		$milestoneArray['metadata']['plan']['supervisor']['timeframe'] = $itemXml->nodeValue($timeframe_acceptance);
		$plan_sup_comment = '/xml/item/research/project/plan/reviewer/comment';
		$milestoneArray['metadata']['plan']['supervisor']['comment'] = $itemXml->nodeValue($plan_sup_comment);


        //Do not need again
        /*$plan_litreview_input_text = '/xml/item/thesis/progress/lit_review/evidence/text';
        $milestoneArray['metadata']['litreview']['inputs']['text'] = $itemXml->nodeValue($plan_litreview_input_text);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/thesis/progress/lit_review/evidence/attachments/uuid'); $j++){
            $plan_litreview_input_uuid = '/xml/item/thesis/progress/lit_review/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['litreview']['inputs']['uuid'][$j] = $itemXml->nodeValue($plan_litreview_input_uuid);
            $milestoneArray['metadata']['litreview']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($plan_litreview_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['litreview']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($plan_litreview_input_uuid),$attachmentInfo);

        }

		$plan_completionplan_input_text = '/xml/item/thesis/progress/completion_plan/evidence/text';
        $milestoneArray['metadata']['completionplan']['inputs']['text'] = $itemXml->nodeValue($plan_completionplan_input_text);

		for ($j = 1; $j <= $itemXml->numNodes('/xml/item/thesis/progress/completion_plan/evidence/attachments/uuid'); $j++){
            $plan_litreview_input_uuid = '/xml/item/thesis/progress/completion_plan/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['completionplan']['inputs']['uuid'][$j] = $itemXml->nodeValue($plan_litreview_input_uuid);
            $milestoneArray['metadata']['completionplan']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($plan_litreview_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['completionplan']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($plan_litreview_input_uuid),$attachmentInfo);

        }*/

        $plan_timeframe = '/xml/item/research/project/plan/timeframe/evidence/text';
        $milestoneArray['metadata']['plantimeframe']['inputs'] = $itemXml->nodeValue($plan_timeframe);

        $achievable_proposal = '/xml/item/research/project/plan/proposal/reviewer/accepted';
        $milestoneArray['metadata']['planproposal']['achievable'] = $itemXml->nodeValue($achievable_proposal);

        $proposal_comment = '/xml/item/research/project/plan/proposal/reviewer/comment';
        $milestoneArray['metadata']['planproposal']['comment'] = $itemXml->nodeValue($proposal_comment);

        $realistic_tiemframe = '/xml/item/research/project/plan/timeframe/reviewer/accepted';
        $milestoneArray['metadata']['planproposal']['realistic'] = $itemXml->nodeValue($realistic_tiemframe);

        $timeframe_comment = '/xml/item/research/project/plan/timeframe/reviewer/comment';
        $milestoneArray['metadata']['plantimeframe']['comment'] = $itemXml->nodeValue($timeframe_comment);



        //the information of methodology
        $methodology_accepted = '/xml/item/research/project/research_conduct/methodology/reviewer/accepted';
        $milestoneArray['metadata']['methodology']['accepted'] = $itemXml->nodeValue($methodology_accepted);

        $methodology_input_text = '/xml/item/research/project/research_conduct/methodology/evidence/text';
        $milestoneArray['metadata']['methodology']['inputs']['text'] = $itemXml->nodeValue($methodology_input_text);

        $methodology_comment = '/xml/item/research/project/research_conduct/methodology/reviewer/comment';
        $milestoneArray['metadata']['methodology']['comment'] = $itemXml->nodeValue($methodology_comment);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/research/project/research_conduct/methodology/evidence/attachments/uuid'); $j++){
            $methodology_input_uuid = '/xml/item/research/project/research_conduct/methodology/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['methodology']['inputs']['uuid'][$j] = $itemXml->nodeValue($methodology_input_uuid);
            $milestoneArray['metadata']['methodology']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($methodology_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['methodology']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($methodology_input_uuid),$attachmentInfo);
        }

        // information of text matching
        $text_matching_subjected = '/xml/item/progression/milestone/compliance/academic_integrity/text_matching/completed';
        $milestoneArray['metadata']['textmatching']['subjected'] = $itemXml->nodeValue($text_matching_subjected);

        $text_matching_integrity = '/xml/item/progression/milestone/compliance/academic_integrity/policy/supervisor/compliant';
        $milestoneArray['metadata']['textmatching']['integrity'] = $itemXml->nodeValue($text_matching_integrity);

        $text_matching_input_text = '/xml/item/progression/milestone/compliance/academic_integrity/policy/evidence/text';
        $milestoneArray['metadata']['textmatching']['inputs']['text'] = $itemXml->nodeValue($text_matching_input_text);

        $text_matching_comment = '/xml/item/progression/milestone/compliance/academic_integrity/policy/supervisor/comment';
        $milestoneArray['metadata']['textmatching']['comment'] = $itemXml->nodeValue($text_matching_comment);

        //information of goals for next milestone
        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/progression/goals/agreed'); $j++)
        {
            $time_frame = '/xml/item/progression/goals/agreed['.$j.']/timeframe';
            $comment = '/xml/item/progression/goals/agreed['.$j.']/goal';

            $milestoneArray['metadata']['agreedgoal'][$j]['timeframe'] = $itemXml->nodeValue($time_frame);
            $milestoneArray['metadata']['agreedgoal'][$j]['comment'] = $itemXml->nodeValue($comment);
        }
        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/progression/goals/student'); $j++)
        {
            $time_frame = '/xml/item/progression/goals/student['.$j.']/timeframe';
            $goal_content = '/xml/item/progression/goals/student['.$j.']/goal';

            $milestoneArray['metadata']['studentgoal'][$j]['timeframe'] = $itemXml->nodeValue($time_frame);
            $milestoneArray['metadata']['studentgoal'][$j]['goalcontent'] = $itemXml->nodeValue($goal_content);

            for ($m = 1; $m <= $itemXml->numNodes('/xml/item/progression/goals/agreed'); $m++){
                            if ($milestoneArray['metadata']['studentgoal'][$j]['timeframe'] == $milestoneArray['metadata']['agreedgoal'][$m]['timeframe']){
                                $milestoneArray['metadata']['studentgoal'][$j]['comment'] = $milestoneArray['metadata']['agreedgoal'][$m]['comment'];
                                break;
                            }
                        }
        }

        //information of publishing
        $publish_input_text = '/xml/item/support/mentoring/publishing/student/comment';
        $milestoneArray['metadata']['publish']['inputs']['text'] = $itemXml->nodeValue($publish_input_text);

        $publish_supervisor_comment = '/xml/item/support/mentoring/publishing/reviewer/comment';
        $milestoneArray['metadata']['publish']['comment'] = $itemXml->nodeValue($publish_supervisor_comment);

        $publish_supervisor_accepted = '/xml/item/support/mentoring/publishing/reviewer/accepted';
        $milestoneArray['metadata']['publish']['accepted'] = $itemXml->nodeValue($publish_supervisor_accepted);

        //information of supervisor's section

        $supervisor_recommendation = '/xml/item/progression/outcome/recommendation/@type';
        $milestoneArray['metadata']['supervisor']['recommendation'] = $itemXml->nodeValue($supervisor_recommendation);

        if(strnatcasecmp($milestoneArray['metadata']['supervisor']['recommendation'], 'Satisfactory') == 0) {
            $supervisor_recommendation_comment = '/xml/item/progression/outcome/recommendation/supervisor/comment';
            $milestoneArray['metadata']['supervisor']['recommendationcomment'] = $itemXml->nodeValue($supervisor_recommendation_comment);
        }
        else {
            $milestoneArray['metadata']['supervisor']['recommendationcomment'] = '';
        }

       //supervisor recommendation

		if($milestoneArray['metadata']['supervisor']['recommendation'] == 'Satisfactory' && $milestoneArray['metadata']['award_type']=='Masters')
		{
			$supervisor_judgement = '/xml/item/candidature/change_request/course/recommendation/supervisor/accepted';
       		$milestoneArray['metadata']['supervisor']['judgement'] = $itemXml->nodeValue($supervisor_judgement);
			$supervisor_comment = '/xml/item/candidature/change_request/course/recommendation/supervisor/comment';
        	$milestoneArray['metadata']['supervisor']['comment'] = $itemXml->nodeValue($supervisor_comment);

		}

        if(strnatcasecmp($milestoneArray['metadata']['supervisor']['recommendation'], 'Resubmit') == 0) {
            $supervisor_resubmit_requiredwork = '/xml/item/progression/outcome/resubmit/required_work';
            $milestoneArray['metadata']['supervisor']['resubmit'] = $itemXml->nodeValue($supervisor_resubmit_requiredwork);
        }
        else {
            $milestoneArray['metadata']['supervisor']['resubmit'] = '';
        }

        if(strnatcasecmp($milestoneArray['metadata']['supervisor']['recommendation'], 'Show cause') == 0) {
            $supervisor_resubmit_showcause = '/xml/item/progression/outcome/show_cause/comment';
            $milestoneArray['metadata']['supervisor']['showcause'] = $itemXml->nodeValue($supervisor_resubmit_showcause);
        }
        else {
            $milestoneArray['metadata']['supervisor']['showcause'] = '';
        }


        //information of MOD by postgrad coordinator
        $mod_coordinator_support = '/xml/item/progression/outcome/recommendation/@supported';
        $milestoneArray['metadata']['mod']['coordinatorsupport'] = $itemXml->nodeValue($mod_coordinator_support);

        $mod_coordinator_comment = '/xml/item/progression/outcome/recommendation/coordinator/comment';
        $milestoneArray['metadata']['mod']['coordinatorcomment'] = $itemXml->nodeValue($mod_coordinator_comment);

        $mod_coordinator_name = '/xml/item/sys_workflow/postgraduate_coordinator_name';
        $milestoneArray['metadata']['mod']['coordinatorname'] = $itemXml->nodeValue($mod_coordinator_name);

        $mod_coordinator_date = '/xml/item/progression/outcome/recommendation/coordinator/date';
        $milestoneArray['metadata']['mod']['coordinatordate'] = $itemXml->nodeValue($mod_coordinator_date);

        //information of MOD by candidate
        $mod_student_accepted = '/xml/item/progression/outcome/student/accepted';
        $milestoneArray['metadata']['mod']['studentaccepted'] = $itemXml->nodeValue($mod_student_accepted);

        $mod_student_comment = '/xml/item/progression/outcome/student/comment';
        $milestoneArray['metadata']['mod']['studentcomment'] = $itemXml->nodeValue($mod_student_comment);

        $mod_student_name = '/xml/item/progression/outcome/student/approver';
        $milestoneArray['metadata']['mod']['studentname'] = $itemXml->nodeValue($mod_student_name);

        $mod_student_date = '/xml/item/progression/outcome/student/date';
        $milestoneArray['metadata']['mod']['studentdate'] = $itemXml->nodeValue($mod_student_date);

        //information of MOD by faculty
        $mod_faculty_determination = '/xml/item/progression/outcome/@status';
        $milestoneArray['metadata']['mod']['facultydetermination'] = $itemXml->nodeValue($mod_faculty_determination);

        $mod_faculty_comment = '/xml/item/progression/outcome/determination/comment';
        $milestoneArray['metadata']['mod']['facultycomment'] = $itemXml->nodeValue($mod_faculty_comment);

        $mod_faculty_phddetermination = '/xml/item/candidature/change_request/course/outcome/@approved';
        $milestoneArray['metadata']['mod']['facultyphddetermination'] = $itemXml->nodeValue($mod_faculty_phddetermination);

        $mod_faculty_phdcomment = '/xml/item/candidature/change_request/course/outcome/comment';
        $milestoneArray['metadata']['mod']['facultyphdcomment'] = $itemXml->nodeValue($mod_faculty_phdcomment);

        $mod_faculty_name = '/xml/item/progression/outcome/determination/name';
        $milestoneArray['metadata']['mod']['facultyname'] = $itemXml->nodeValue($mod_faculty_name);

        $mod_faculty_date = '/xml/item/progression/outcome/determination/date';
        $milestoneArray['metadata']['mod']['facultydate'] = $itemXml->nodeValue($mod_faculty_date);


        /*$milestone_status = '/xml/item/@itemstatus';
        $milestoneArray['metadata']['status'] = $itemXml->nodeValue($milestone_status);*/

        /*$milestone_moderated = '/xml/item/@moderating';
        $milestoneArray['metadata']['approved'] = $itemXml->nodeValue($milestone_moderated);*/

        $milestoneName = '/xml/item/progression/milestone/@name';
        $milestoneArray['metadata']['name'] = $itemXml->nodeValue($milestoneName);

        $milestoneNameid = '/xml/item/progression/milestone/@name_id';
        $milestoneArray['metadata']['nameid'] = $itemXml->nodeValue($milestoneNameid);

		$approval_date = '/xml/item/progression/milestone/date_completed';
		$milestoneArray['metadata']['approval_date'] = $itemXml->nodeValue($approval_date);



        //Other Information
        /*$thesis_abstract_text = '/xml/item/thesis/abstract/text';
        $milestoneArray['metadata']['thesis']['abstract']['text'] = $itemXml->nodeValue($thesis_abstract_text);

        $intermissions = '/xml/item/candidature/absences/episode/reason';
        $milestoneArray['metadata']['intermissions'] = $itemXml->nodeValue($intermissions);

        */


        return $milestoneArray;

    }

    /**
     * Validate incoming parameters
     *
     * @param string $format, html/pdf
     * @param array $uuid, item UUID
     * @param array $version, item Version
     */
    protected function validate_params($format, $uuid, $version)
    {
        if($format!='html' && $format!='pdf')
            return false;
        if(strcmp($uuid, 'missed')==0 || strlen($uuid) != 36)
            return false;

        if(strcmp($version, 'missed')==0 || !is_numeric($version))
            return false;

        return true;
    }

	/**
	Generates a token that is valid for 30 minutes.  This should be appended to URLs so that users are not forced to log in to view content.
	E.g.
	$itemURL = "http://MYSERVER/myinst/items/619722b1-22f8-391a-2bcf-46cfaab36265/1/?token=" . generateToken("fred.smith", "IntegSecret", "squirrel");

	In the example above, if fred.smith is a valid username on the EQUELLA server he will be automatically logged into the system so that he can view
	item 619722b1-22f8-391a-2bcf-46cfaab36265/1 (provided he has the permissions to do so).

	Note that to use this functionality, the Shared Secrets user management plugin must be enabled (see User Management in the EQUELLA Administration Console)
	and a shared secret must be configured.

	@param username :The username of the user to log in as
	@param sharedSecretId :The ID of the shared secret
	@param sharedSecretValue :The value of the shared secret
	@return : A token that can be directly appended to a URL (i.e. it is already URL encoded)   E.g.  $URL = $URL . "?token=" . generateToken(x,y,z);
	*/
	private function generateToken()
	{
        $ci =& get_instance();
        $ci->load->config('rex');
        $username = $ci->config->item('rhd_shared_secret_username');
        $sharedSecretId = $ci->config->item('rhd_shared_secret_id');
        $sharedSecretValue = $ci->config->item('rhd_shared_secret_value');

		$time = mktime() . '000';
		/*return urlencode ($username) . ':' . urlencode($sharedSecretId) . ':' .  $time . ':' .
                        urlencode(base64_encode (pack ('H*', md5 ($username . $sharedSecretId . $time . $sharedSecretValue))));*/
		return urlencode($username) . ':' . urlencode($sharedSecretId) . ':' .  $time . ':' .
                        urlencode(base64_encode (pack ('H*', md5 ($username . $sharedSecretId . $time . $sharedSecretValue))));

	}


    /**
     * Attach SAM PDF to ITEM
     *   More detailed comments in line
     *
     * @param string $uuid, item UUID
     * @param string $version, item Version
     */
    private function getFileName($uuid='missed', $attachmentInfo){
        if($uuid == 'missed'|| $attachmentInfo=='')
            return '';
        else {
            foreach($attachmentInfo as $attachment){
                $attachuuid = $attachment['uuid'];
                $attachDisplayName = $attachment['description'];
                if($attachuuid == $uuid){
                    return $attachDisplayName;
                }
            }
            return '';
        }
    }

    private function getFileLink($uuid='missed', $attachmentInfo){
        if($uuid == 'missed' || $attachmentInfo=='')
            return '';
        else {
            foreach($attachmentInfo as $attachment){
                $attachuuid = $attachment['uuid'];
                $attachLink = $attachment['links']['view'];
                if($attachuuid == $uuid){
                    return $attachLink;
                }
            }
            return '';
        }
    }

    public function attach_pdf_lx9n3h2($uuid='missed', $version='missed')
    {
        #$this->output->enable_profiler(TRUE);
        $errdata['heading'] = "Error";

        if($this->validate_params('pdf',$uuid, $version) == false)
        {
            $errdata['message'] = "Invalid Request";
            log_message('error', 'Attaching Milestone pdf failed type 1, item uuid: ' . $uuid . ', error: ' . 'Invalid input params.');
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        /****************************************************************
         * Important: Respond to Equella so that its workflow can finish
         * Otherwise the editItem will fail.
         *
         * Script goes on after flushing to equella.
         ****************************************************************/

        // Ignore connection-closing by the client/user
        ignore_user_abort(true);

        // Set your timelimit to a length long enough for your script to run,
        // but not so long it will bog down your server in case multiple versions run
        // or this script get's in an endless loop.

        $content = 'good';         // Get the content of the output buffer
        #ob_end_clean();                     // Close current output buffer
        $len = strlen($content);
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Encoding: none;');
        header('Connection: close');         // Tell the client to close connection
        header("Content-Length:").$len;      // Close connection after $size characters
        echo $content;  // Output content

        ob_flush();
        flush();                             // Force php-output-cache to flush to flex.

        ob_end_flush();

        #make sure milestone work flow completely finishes
        sleep (5);

		//$this->getCommentInside($uuid, $version);
		$this->getComment($uuid, $version);


		$oauth = array('oauth_client_config_name' => 'rhd');

        $this->load->helper('url');
        $this->load->library('rexrest/rexrest', $oauth);

        //$this->load->library('rexrest/rexrest');

        $ci =& get_instance();
        $ci->load->config('rex');

        $success = $this->rexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching Milestone pdf failed type 2, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        $success = $this->rexrest->getItem($uuid, $version, $response);
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching Milestone pdf failed type 3, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

      /*  echo "<br>=====================================";
        echo "<pre>";
        print_r($response);
        echo "</pre>";*/


        //log_message('error',json_encode($response));

        unset($response['headers']);
        $item_bean = $response;

        //log_message('error',json_encode($response['attachments']));

		$xmlwrapper = 'attachxmlwrapper_' . $uuid;
		//pull out metadata XML
		$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$item_bean['metadata']), $xmlwrapper);

        //$this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));

		//log_message('error', 'xmlwrapper: ' . $this->xmlwrapper);

        if(!$this->itemIsMilestone($this->$xmlwrapper))
        {
            $errdata['message'] = "Item is not Milestone";
            log_message('error', 'Attaching Milestone pdf failed type 4, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        $milestone_array = $this->milestoneXml2Array($this->$xmlwrapper, $response['attachments']);
		//log_message('error', 'milestone_array: ' . $milestone_array);
        $milestone_array['status'] = $item_bean['status'];

   /*     echo "<br>=====================================";
        echo "<pre>";
        print_r($milestone_array);
        echo "</pre>";*/
       // exit;

        $post_wording = '';
        if($milestone_array['metadata']['name'] == 'Interim Review')
        {
            if(isset($milestone_array['metadata']['nameid']) && $milestone_array['metadata']['nameid']!= '')
            {
                if(intval($milestone_array['metadata']['nameid']) >= 3010)
                {
                    $post_wording = ' - post-Final';
                }
                else if(intval($milestone_array['metadata']['nameid']) >= 2010 && intval($milestone_array['metadata']['nameid']) < 3000)
                {
                    $post_wording = ' - pre-Final';
                }
                else if(intval($milestone_array['metadata']['nameid']) >= 1010 && intval($milestone_array['metadata']['nameid']) < 2000)
                {
                    $post_wording = ' - pre-Mid';
                }
                else if(intval($milestone_array['metadata']['nameid']) >= 10 && intval($milestone_array['metadata']['nameid']) < 1000)
                {
                    $post_wording = ' - pre-Confirmation';
                }
                else
                {
                    $post_wording = $milestone_array['metadata']['nameid'];
                }
            }
        }

        if(strnatcasecmp($milestone_array['status'],'live')!= 0){
            log_message('error',"Only live collection can be generated as PDF version.");
            return;
        }

        $data = array('milestone_array' => $milestone_array);
        $data['milestone_array']['format'] = 'pdf';
        $data['milestone_array']['uuid'] = $uuid; #item uuid
        $data['milestone_array']['version'] = $version; #item version number

		$success = $this->rexrest->getItemHistory($uuid, $version, $re);
		if (!$success) {
			$this->logger_cs->error("getItemHistory error:" . $this->rexrest->error . '-' . $uuid . "/" . $version);
			return false;
		}
		$sup_sub_mod = array();

		for($i=0; $i<count($re); $i++)
		{
			if(isset($re[$i]['step']) && $re[$i]['step'] == '7139af5c-68e2-4003-ae15-bae141b06b05')
			{
				$sup_sub_mod['date'] = isset($re[$i]['date']) ? $re[$i]['date'] : '';

				$sup_sub_mod['approver_fan'] = isset($re[$i]['user']['id']) ? $re[$i]['user']['id'] : '';

				$approver_name = '';
				if($sup_sub_mod['approver_fan'] != '')
				{
					if(strlen($sup_sub_mod['approver_fan']) == 36)
					{
						$approver_name = 'System Administrator';
					}
					else
					{
						$this->load->library('ldap/ldap', $oauth);
						$ldap_user = $this->ldap->get_attributes($sup_sub_mod['approver_fan']);
						$approver_name = $ldap_user['name'];
					}
				}
				$sup_sub_mod['approver_name'] = $approver_name;
			}
		}
		$data['milestone_array']['metadata']['supervisor_approval']['date'] = $sup_sub_mod['date'];
		$data['milestone_array']['metadata']['supervisor_approval']['approver_fan'] = $sup_sub_mod['approver_fan'];
		$data['milestone_array']['metadata']['supervisor_approval']['approver_name'] = $sup_sub_mod['approver_name'];

        //$tmp_date_time = time();
        $refValue = new DateTime("now");

        ob_start();

        $success = $this->rexrest->filesCopy($uuid, $version, $response1);

        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching Milestone pdf failed (fileCopy), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }
        if(!isset($response1['headers']['location']))
        {
            $errdata['message'] = 'No Location header in response to copy files REST call.';
            log_message('error', 'Attaching Milestone pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        //log_message('error',json_encode($response1));

        $location = $response1['headers']['location'];
        $filearea_uuid = substr($location, strpos($location, 'file')+5, 36);

        //log_message('error', $filearea_uuid);

        /***************************************************************
         * Check whther there are exiting files, if yes, move them to
         * previous_files and change uuid.
         *
         * <files>
         *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4730C_2014_S2">
         *        <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc000</uuid>
         *    </file>
         * </files>
         *
         * <previous_files>
         *    <file distributed="2014-08-28T14:34:18+09:30" ref="COMP4730C_2014_S2">
         *        <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc100</uuid>
         *    </file>
         * </previous_files>
         ****************************************************************/

        /***************************************************************
         * Muiltiple files for each availability
         *
         * <files>
         *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4730C_2014_S2">
         *        <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc000</uuid>
         *    </file>
         *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4730D_2014_S2">
         *        <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc001</uuid>
         *    </file>
         * </files>
         *
         * Single file for all availabilities
         * <files>
         *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4730C_2014_S2">
         *        <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc000</uuid>
         *    </file>
         *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4730D_2014_S2">
         *        <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc000</uuid>
         *    </file>
         * </files>
         ****************************************************************/

        if(!isset($item_bean['attachments'])) {
            $item_bean['attachments'] = array();
        }

        $existing_attachments = $item_bean['attachments'];

        $new_attachments = array();

        $xpath_files = '/xml/item/progression/milestone/report/current';

        $node_previous_files = null;
        $xpath_previous_files = '/xml/item/progression/milestone/report/previous';

        $xpath_files_current_file = $xpath_files . '/version/uuid';

        $first_current_files_uuid = $this->$xmlwrapper->nodeValue($xpath_files_current_file);

        $current_files_exist = $first_current_files_uuid == null ? false : true;

        $count_current_files = $this->$xmlwrapper->numNodes($xpath_files . '/version');
        $count_previous_files = $this->$xmlwrapper->numNodes($xpath_previous_files . '/version');

        $old_file_array = array();
        $previous_file_uuid_suffix = 200;


        /****************************************************************
         * copy all the <files> to <previous_files>
         ****************************************************************/
        ####

        //log_message('error', 'Attaching Milestone pdf, count_current_files: '.$count_current_files);
        /* if($current_files_exist == true)
            log_message('error', 'Attaching Milestone pdf, current_files_exist: true ');
        else
            log_message('error', 'Attaching Milestone pdf, current_files_exist: false '); */

        if($count_current_files > 0 && $current_files_exist == false)
        {

            log_message('error', 'Attaching milestone pdf, $count_old_files > 0 && $old_files_exist == false, item uuid: ' . $uuid);
            exit();
        }

        #if($old_files_exist)
        if($count_current_files > 0)
        {
            //$node_previous_files = $this->xmlwrapper->createNodeFromXPath($xpath_previous_files);

            if($count_previous_files > 0) {
                $previous_file_uuid_suffix += $count_previous_files;
            }

            $next_previous_file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $previous_file_uuid_suffix);


            //Copy the current version information to array

            $tmp_xpath_file_uuid = $xpath_files . '/version/uuid';
            $tmp_xpath_file_ref = $xpath_files . '/version/@ref';
            $tmp_xpath_file_generated = $xpath_files . '/version/@generated';

            $tmp_file_uuid = $this->$xmlwrapper->nodeValue($tmp_xpath_file_uuid);
            $tmp_file_ref = $this->$xmlwrapper->nodeValue($tmp_xpath_file_ref);
            $tmp_file_generated = $this->$xmlwrapper->nodeValue($tmp_xpath_file_generated);

            $old_file_array['uuid'] = $tmp_file_uuid;
            $old_file_array['ref'] = $tmp_file_ref;
            $old_file_array['gen'] = $tmp_file_generated;

            /*log_message('error','the current file information is : '.json_encode($old_file_array));
            exit();*/



            //Append the current version to previous_versions subtree

            /*$tmp_node_previous_file = $this->xmlwrapper->createNode($node_previous_files, "version");
            $tmp_node_previous_file_uuid = $this->xmlwrapper->createNode($tmp_node_previous_file, "uuid");
            $tmp_node_previous_file_ref = $this->xmlwrapper->createAttribute($tmp_node_previous_file, "ref");
            $tmp_node_previous_file_generated = $this->xmlwrapper->createAttribute($tmp_node_previous_file, "generated");

            $tmp_node_previous_file_ref->nodeValue = $old_file_array['ref'];
            $tmp_node_previous_file_generated->nodeValue = $old_file_array['gen'];
            $tmp_node_previous_file_uuid->nodeValue = $old_file_array['uuid'];*/

               /*log_message('error', json_encode($existing_attachments));
               exit();*/
        }


        $this->$xmlwrapper->deleteNodeFromXPath($xpath_files);
        $node_files = $this->$xmlwrapper->createNodeFromXPath($xpath_files);

        //log_message('error',json_encode($node_files));

        $largest_suffix_number = 0;

        //log_message('error',$file_uuid);

        //find out the largest suffix number in uuid (last three chars)
        for ($j = 0; $j < count($existing_attachments); $j++)
        {
            $file_suffix_number = intval(substr($existing_attachments[$j]['uuid'], 33, 3));
            if($file_suffix_number > $largest_suffix_number)
                $largest_suffix_number = $file_suffix_number;
        }

        $file_suffix_number = intval(substr($old_file_array['uuid'], 33, 3));
        if($file_suffix_number > $largest_suffix_number)
            $largest_suffix_number = $file_suffix_number;

        $largest_suffix_number ++;

        $file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $largest_suffix_number);

        //log_message('error', 'the new file uuid is : ' . $file_uuid);

        ob_start();
        $this->load->view('cs/milestone_view', $data);
        $html = ob_get_contents();
        ob_end_clean();

        $pdfclsname = 'pdf_class' . $uuid;
        $this->load->library('pdf/pdf_class', null, $pdfclsname);
        $this->$pdfclsname->setFooter('{PAGENO} / {nb}');
        $this->$pdfclsname->WriteHTML($html);


        $filename = $data['milestone_array']['metadata']['name'] . $post_wording;
        $timestring = $refValue->format('Y-m-d H-i-s');
		$timestring = str_replace(' ', 'T', $timestring);
        $filename = $filename . '_' . $timestring . '.pdf';


        $pdf_content = $this->$pdfclsname->Output($filename, 'S');

        $success = $this->rexrest->fileUpload($filearea_uuid, $filename, $pdf_content, $response2);
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching Milestone pdf failed (fileUpload), item name: ' . $filename . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }


        $new_attachments[0] = array('type'=>'file',
            'filename'=>$filename,
            'description'=>$filename,
            'uuid'=>$file_uuid);


        $node_file = $this->$xmlwrapper->createNode($node_files, "version");
        $node_uuid = $this->$xmlwrapper->createNode($node_file, "uuid");
        $node_ref = $this->$xmlwrapper->createAttribute($node_file, "ref");
        $node_generated = $this->$xmlwrapper->createAttribute($node_file, "generated");
        $node_uuid->nodeValue = $file_uuid;
        $node_ref->nodeValue = $data['milestone_array']['metadata']['studenfan'].'_'. $data['milestone_array']['metadata']['nameid'];
        //log_message('error',$data['milestone_array']['metadata']['nameid']);
        $node_generated->nodeValue = $refValue->format('c');


        $item_bean['attachments'] = array_merge($new_attachments,$existing_attachments);
         $item_bean['metadata'] = $this->$xmlwrapper->__toString();

        //log_message('error', json_encode( $item_bean['attachments']));


        $success = $this->rexrest->editItem($uuid, $version, $item_bean, $response3, $filearea_uuid);
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching Milestone pdf failed (editItem), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            //log_message('error', json_encode($item_bean['attachments']));
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }
    }


    public function justTest($uuid='missed', $version='missed'){
        $this->load->helper('url');
        $this->load->library('rexrest/rexrest');


        $success = $this->rexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching Milestone pdf failed type 2, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }
        $success = $this->rexrest->filesCopy($uuid, $version, $response1);

        if(!$success)
        {
            $errdata['message'] = $this->rexrest->error;
            log_message('error', 'Attaching Milestone pdf failed (fileCopy), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }

        echo "<br>".json_encode($response1);
        return;
    }

    private function getComment_backup($uuid='missed', $version='missed')
    {
		    $data = array();
        $ci =& get_instance();
        $ci->load->config('rex');
        $loggings = $ci->config->item('log');
        $this->load->library('logging/logging', $loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder

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

						// 16 FEB College workflow steps added AC

						case '6bbf6f29-32c0-4cb9-b449-02405ae1f876': //BGL: milestone sign off
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;

						case '3379d95e-9d50-493f-ba3a-365d27aa99c1': //EPSW: milestone sign off
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;

						case '88879c34-b539-469a-ba9a-24584657559f': //HASS: milestone sign off
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;

						case '141e05d1-47ae-48ba-8655-b7376da05fc4': //MPH: milestone sign off
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;

						case 'e4f61113-0236-4672-a58c-68048369c668': //NHS: milestone sign of
							$data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
							$data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
							$data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
							$data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
							$data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
							$data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
							$data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
						break;

						case '945c5536-09bc-417e-9754-a57d4611bc89': //SE: milestone sign off
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

    }

    public function showComment($uuid='missed', $version='missed')
   {
       $data = array();
       $ci =& get_instance();
       $ci->load->config('rex');
       $loggings = $ci->config->item('log');
       $this->load->library('logging/logging', $loggings); //call loggings from the lib
       $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder

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
         /*echo 'data: <pre>';
             print_r($data);
             echo '</pre>';

             echo 'response: <pre>';
             print_r($response);
             echo '</pre>';
         exit;*/
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

           case 'a08b3eb2-457b-46ea-94b0-29a82de3fc06': //RHD Coord sign off
             $data['postGrad_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['postGrad_sign_off']['stepName'] = $response[$i]['stepName']; //RHD Coord sign off
             $data['postGrad_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['postGrad_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['postGrad_sign_off']['comment'] = $response[$i]['comment'];
             $data['postGrad_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['postGrad_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           /***********************************************/
           /**********Course change [Masters to PhD] sign offs*************/
           /***********************************************/

           case '860e0255-ab90-41da-a453-c2d16a824d6f': //EHL :: Masters to PhD sign off
             $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
             $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           //4a94577bd-4629-4254-8f59-4704a34bbee3
           case 'a94577bd-4629-4254-8f59-4704a34bbee3': //FSE :: Masters to PhD sign off
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

           case '07799b26-0c8d-4b58-bca8-13fca444a70c': //SaBS :: Masters to PhD sign off
             $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
             $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
           break;


           case '5a83d9f9-7691-475f-a0e3-5ea54d20156f': //BGL :: Masters to PhD sign off
             $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
             $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case 'c1d0ec7b-c4f6-4924-9fc4-a19ecae4d6eb': //EPSW :: Masters to PhD sign off
             $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
             $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case '45b41539-6f6e-4e00-9bc2-41164077e8e7': //HASS :: Masters to PhD sign off
             $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
             $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case '4390da07-413b-4b79-903c-a18336f443aa': //MPH :: Masters to PhD sign off
             $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
             $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case '8cfbc7c8-c671-4bd4-8282-036ddb5b4cf3': //NHS :: Masters to PhD sign off
             $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
             $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case 'b631a893-14a2-4e04-94e3-54d7d6ecd67f': //SE :: Masters to PhD sign off
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

           case '9f0e0561-84e2-421c-af55-8a6f857f7b57': //SaBS Admin: milestone sign off
             $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
             $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
           break;


           // 16 FEB College workflow steps added AC

           case '6bbf6f29-32c0-4cb9-b449-02405ae1f876': //BGL: milestone sign off
             $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
             $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case '3379d95e-9d50-493f-ba3a-365d27aa99c1': //EPSW: milestone sign off
             $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
             $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case '88879c34-b539-469a-ba9a-24584657559f': //HASS: milestone sign off
             $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
             $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case '141e05d1-47ae-48ba-8655-b7376da05fc4': //MPH: milestone sign off
             $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
             $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case 'e4f61113-0236-4672-a58c-68048369c668': //NHS: milestone sign of
             $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
             $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
             $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
             $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
             $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
             $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
             $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
           break;

           case '945c5536-09bc-417e-9754-a57d4611bc89': //SE: milestone sign off
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


   //calculate each workflow step approval date.
   if(!isset($data['stu_sign_off']) || !isset($data['stu_sign_off']['date']) || $data['stu_sign_off']['date'] == '')
   {
     $data['stu_sign_off']['active'] = 'F';
     $data['postGrad_sign_off']['active'] = 'F';
     $data['rhd_admin_sign_off']['active'] = 'F';
     $data['master_to_phd_sign_off']['active'] = 'F';
   }
   else
   {
     $data['stu_sign_off']['active'] = 'T';

     if(!isset($data['postGrad_sign_off']) || !isset($data['postGrad_sign_off']['date']) || $data['postGrad_sign_off']['date'] == '')
     {
       $data['postGrad_sign_off']['active'] = 'F';
       $data['rhd_admin_sign_off']['active'] = 'F';
       $data['master_to_phd_sign_off']['active'] = 'F';
     }
     else
     {
       $stu_approval_date = strtotime($data['stu_sign_off']['date']);
       $postGra_approval_date = strtotime($data['postGrad_sign_off']['date']);

       if($stu_approval_date < $postGra_approval_date)
       {
         $data['postGrad_sign_off']['active'] = 'T';

         if(!isset($data['master_to_phd_sign_off']) || !isset($data['master_to_phd_sign_off']['date']) || $data['master_to_phd_sign_off']['date'] == '')
         {
           $data['master_to_phd_sign_off']['active'] = 'F';
           //$data['rhd_admin_sign_off']['active'] = 'F';
         }
         else
         {
           $master_to_phd_approval_date = strtotime($data['master_to_phd_sign_off']['date']);
           if($postGra_approval_date < $master_to_phd_approval_date)
           {
             $data['master_to_phd_sign_off']['active'] = 'T';

             if(isset($data['rhd_admin_sign_off']) && $data['rhd_admin_sign_off']['date'] != '')
             {
               $rhd_admin_approval_date = strtotime($data['rhd_admin_sign_off']['date']);
               if($master_to_phd_approval_date < $rhd_admin_approval_date)
               {
                 $data['rhd_admin_sign_off']['active'] = 'T';
               }
               else
               {
                 $data['rhd_admin_sign_off']['active'] = 'F';
               }
             }
           }
           else
           {
             $data['master_to_phd_sign_off']['active'] = 'F';
             //$data['rhd_admin_sign_off']['active'] = 'F';
           }
         }

         if($data['master_to_phd_sign_off']['active'] == 'F')
         {
           if(!isset($data['rhd_admin_sign_off']) || !isset($data['rhd_admin_sign_off']['date']) || $data['rhd_admin_sign_off']['date'] == '')
           {
             $data['rhd_admin_sign_off']['active'] = 'F';
           }
           else
           {
             $rhd_admin_approval_date = strtotime($data['rhd_admin_sign_off']['date']);
             if($postGra_approval_date < $rhd_admin_approval_date)
             {
               $data['rhd_admin_sign_off']['active'] = 'T';
             }
             else
             {
               $data['rhd_admin_sign_off']['active'] = 'F';
             }
           }
         }

       }
       else
       {
         $data['postGrad_sign_off']['active'] = 'F';
         $data['rhd_admin_sign_off']['active'] = 'F';
         $data['master_to_phd_sign_off']['active'] = 'F';

       }

     }

   }


   echo 'data: <pre>';
       print_r($data);
       echo '</pre>';

       echo 'response: <pre>';
       print_r($response);
       echo '</pre>';
   exit;


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
   if(isset($data['stu_sign_off']) && isset($data['stu_sign_off']['active']) && $data['stu_sign_off']['active'] == 'T' && isset($data['stu_sign_off']['type']) && $data['stu_sign_off']['type']!= '')
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
   if(isset($data['postGrad_sign_off']) && isset($data['postGrad_sign_off']['active']) && $data['postGrad_sign_off']['active'] == 'T' && isset($data['postGrad_sign_off']) && isset($data['postGrad_sign_off']['type']) && $data['postGrad_sign_off']['type']!= '')
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
   if(isset($data['master_to_phd_sign_off']) && isset($data['master_to_phd_sign_off']['active']) && $data['master_to_phd_sign_off']['active'] == 'T' && isset($data['master_to_phd_sign_off']) && isset($data['master_to_phd_sign_off']['type']) && $data['master_to_phd_sign_off']['type']!= '')
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
   if(isset($data['rhd_admin_sign_off']) && isset($data['rhd_admin_sign_off']['active']) && $data['rhd_admin_sign_off']['active'] == 'T' && isset($data['rhd_admin_sign_off']) && isset($data['rhd_admin_sign_off']['type']) && $data['rhd_admin_sign_off']['type']!= '')
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


   //$success = $this->rexrest->editItem($uuid, $version, $new_item_bean, $r, '');

   }


	  private function getComment($uuid='missed', $version='missed')
    {
		    $data = array();
        $ci =& get_instance();
        $ci->load->config('rex');
        $loggings = $ci->config->item('log');
        $this->load->library('logging/logging', $loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder

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

            case 'a08b3eb2-457b-46ea-94b0-29a82de3fc06': //RHD Coord sign off
              $data['postGrad_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['postGrad_sign_off']['stepName'] = $response[$i]['stepName']; //RHD Coord sign off
              $data['postGrad_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['postGrad_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['postGrad_sign_off']['comment'] = $response[$i]['comment'];
              $data['postGrad_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['postGrad_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            /***********************************************/
            /**********Course change [Masters to PhD] sign offs*************/
            /***********************************************/

            case '860e0255-ab90-41da-a453-c2d16a824d6f': //EHL :: Masters to PhD sign off
              $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
              $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            //4a94577bd-4629-4254-8f59-4704a34bbee3
            case 'a94577bd-4629-4254-8f59-4704a34bbee3': //FSE :: Masters to PhD sign off
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

            case '07799b26-0c8d-4b58-bca8-13fca444a70c': //SaBS :: Masters to PhD sign off
              $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
              $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
            break;


            case '5a83d9f9-7691-475f-a0e3-5ea54d20156f': //BGL :: Masters to PhD sign off
              $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
              $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case 'c1d0ec7b-c4f6-4924-9fc4-a19ecae4d6eb': //EPSW :: Masters to PhD sign off
              $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
              $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case '45b41539-6f6e-4e00-9bc2-41164077e8e7': //HASS :: Masters to PhD sign off
              $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
              $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case '4390da07-413b-4b79-903c-a18336f443aa': //MPH :: Masters to PhD sign off
              $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
              $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case '8cfbc7c8-c671-4bd4-8282-036ddb5b4cf3': //NHS :: Masters to PhD sign off
              $data['master_to_phd_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['master_to_phd_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['master_to_phd_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['master_to_phd_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['master_to_phd_sign_off']['comment'] = $response[$i]['comment'];
              $data['master_to_phd_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['master_to_phd_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case 'b631a893-14a2-4e04-94e3-54d7d6ecd67f': //SE :: Masters to PhD sign off
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

            case '9f0e0561-84e2-421c-af55-8a6f857f7b57': //SaBS Admin: milestone sign off
              $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
              $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
            break;


            // 16 FEB College workflow steps added AC

            case '6bbf6f29-32c0-4cb9-b449-02405ae1f876': //BGL: milestone sign off
              $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
              $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case '3379d95e-9d50-493f-ba3a-365d27aa99c1': //EPSW: milestone sign off
              $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
              $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case '88879c34-b539-469a-ba9a-24584657559f': //HASS: milestone sign off
              $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
              $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case '141e05d1-47ae-48ba-8655-b7376da05fc4': //MPH: milestone sign off
              $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
              $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case 'e4f61113-0236-4672-a58c-68048369c668': //NHS: milestone sign of
              $data['rhd_admin_sign_off']['step_uuid'] = $workflow_step_uuid;
              $data['rhd_admin_sign_off']['stepName'] = $response[$i]['stepName']; // Student sign off
              $data['rhd_admin_sign_off']['type'] = $response[$i]['type']; //apprved or rejected
              $data['rhd_admin_sign_off']['date'] = $response[$i]['date']; // 2017-03-31T11:36:02.986+10:30
              $data['rhd_admin_sign_off']['comment'] = $response[$i]['comment'];
              $data['rhd_admin_sign_off']['moderator'] = $response[$i]['user']['id']; //fan or sys uuid
              $data['rhd_admin_sign_off']['state'] = $response[$i]['state']; //moderating
            break;

            case '945c5536-09bc-417e-9754-a57d4611bc89': //SE: milestone sign off
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


		//calculate each workflow step approval date.
		if(!isset($data['stu_sign_off']) || !isset($data['stu_sign_off']['date']) || $data['stu_sign_off']['date'] == '')
		{
			$data['stu_sign_off']['active'] = 'F';
			$data['postGrad_sign_off']['active'] = 'F';
			$data['rhd_admin_sign_off']['active'] = 'F';
			$data['master_to_phd_sign_off']['active'] = 'F';
		}
		else
		{
			$data['stu_sign_off']['active'] = 'T';

			if(!isset($data['postGrad_sign_off']) || !isset($data['postGrad_sign_off']['date']) || $data['postGrad_sign_off']['date'] == '')
			{
				$data['postGrad_sign_off']['active'] = 'F';
				$data['rhd_admin_sign_off']['active'] = 'F';
				$data['master_to_phd_sign_off']['active'] = 'F';
			}
			else
			{
				$stu_approval_date = strtotime($data['stu_sign_off']['date']);
				$postGra_approval_date = strtotime($data['postGrad_sign_off']['date']);

				if($stu_approval_date < $postGra_approval_date)
				{
					$data['postGrad_sign_off']['active'] = 'T';

					if(!isset($data['master_to_phd_sign_off']) || !isset($data['master_to_phd_sign_off']['date']) || $data['master_to_phd_sign_off']['date'] == '')
					{
						$data['master_to_phd_sign_off']['active'] = 'F';
						//$data['rhd_admin_sign_off']['active'] = 'F';
					}
					else
					{
						$master_to_phd_approval_date = strtotime($data['master_to_phd_sign_off']['date']);
						if($postGra_approval_date < $master_to_phd_approval_date)
						{
							$data['master_to_phd_sign_off']['active'] = 'T';

							if(isset($data['rhd_admin_sign_off']) && $data['rhd_admin_sign_off']['date'] != '')
							{
								$rhd_admin_approval_date = strtotime($data['rhd_admin_sign_off']['date']);
								if($master_to_phd_approval_date < $rhd_admin_approval_date)
								{
									$data['rhd_admin_sign_off']['active'] = 'T';
								}
								else
								{
									$data['rhd_admin_sign_off']['active'] = 'F';
								}
							}
						}
						else
						{
							$data['master_to_phd_sign_off']['active'] = 'F';
							//$data['rhd_admin_sign_off']['active'] = 'F';
						}
					}

					if($data['master_to_phd_sign_off']['active'] == 'F')
					{
						if(!isset($data['rhd_admin_sign_off']) || !isset($data['rhd_admin_sign_off']['date']) || $data['rhd_admin_sign_off']['date'] == '')
						{
							$data['rhd_admin_sign_off']['active'] = 'F';
						}
						else
						{
							$rhd_admin_approval_date = strtotime($data['rhd_admin_sign_off']['date']);
							if($postGra_approval_date < $rhd_admin_approval_date)
							{
								$data['rhd_admin_sign_off']['active'] = 'T';
							}
							else
							{
								$data['rhd_admin_sign_off']['active'] = 'F';
							}
						}
					}

				}
				else
				{
					$data['postGrad_sign_off']['active'] = 'F';
					$data['rhd_admin_sign_off']['active'] = 'F';
					$data['master_to_phd_sign_off']['active'] = 'F';

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
		if(isset($data['stu_sign_off']) && isset($data['stu_sign_off']['active']) && $data['stu_sign_off']['active'] == 'T' && isset($data['stu_sign_off']['type']) && $data['stu_sign_off']['type']!= '')
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
		if(isset($data['postGrad_sign_off']) && isset($data['postGrad_sign_off']['active']) && $data['postGrad_sign_off']['active'] == 'T' && isset($data['postGrad_sign_off']) && isset($data['postGrad_sign_off']['type']) && $data['postGrad_sign_off']['type']!= '')
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
		if(isset($data['master_to_phd_sign_off']) && isset($data['master_to_phd_sign_off']['active']) && $data['master_to_phd_sign_off']['active'] == 'T' && isset($data['master_to_phd_sign_off']) && isset($data['master_to_phd_sign_off']['type']) && $data['master_to_phd_sign_off']['type']!= '')
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
		if(isset($data['rhd_admin_sign_off']) && isset($data['rhd_admin_sign_off']['active']) && $data['rhd_admin_sign_off']['active'] == 'T' && isset($data['rhd_admin_sign_off']) && isset($data['rhd_admin_sign_off']['type']) && $data['rhd_admin_sign_off']['type']!= '')
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

    }

	private function getCommentInside($uuid='missed', $version='missed')
    {
		$data = array();
        $ci =& get_instance();
        $ci->load->config('rex');
        $loggings = $ci->config->item('log');
        $this->load->library('logging/logging', $loggings); //call loggings from the lib
        $this->logger_cs = $this->logging->get_logger('cs'); //get cs folder

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


		//calculate each workflow step approval date.
		if(!isset($data['stu_sign_off']) || !isset($data['stu_sign_off']['date']) || $data['stu_sign_off']['date'] == '')
		{
			$data['stu_sign_off']['active'] = 'F';
			$data['postGrad_sign_off']['active'] = 'F';
			$data['rhd_admin_sign_off']['active'] = 'F';
			$data['master_to_phd_sign_off']['active'] = 'F';
		}
		else
		{
			$data['stu_sign_off']['active'] = 'T';

			if(!isset($data['postGrad_sign_off']) || !isset($data['postGrad_sign_off']['date']) || $data['postGrad_sign_off']['date'] == '')
			{
				$data['postGrad_sign_off']['active'] = 'F';
				$data['rhd_admin_sign_off']['active'] = 'F';
				$data['master_to_phd_sign_off']['active'] = 'F';
			}
			else
			{
				$stu_approval_date = strtotime($data['stu_sign_off']['date']);
				$postGra_approval_date = strtotime($data['postGrad_sign_off']['date']);

				if($stu_approval_date < $postGra_approval_date)
				{
					$data['postGrad_sign_off']['active'] = 'T';

					if(!isset($data['master_to_phd_sign_off']) || !isset($data['master_to_phd_sign_off']['date']) || $data['master_to_phd_sign_off']['date'] == '')
					{
						$data['master_to_phd_sign_off']['active'] = 'F';
						//$data['rhd_admin_sign_off']['active'] = 'F';
					}
					else
					{
						$master_to_phd_approval_date = strtotime($data['master_to_phd_sign_off']['date']);
						if($postGra_approval_date < $master_to_phd_approval_date)
						{
							$data['master_to_phd_sign_off']['active'] = 'T';

							if(isset($data['rhd_admin_sign_off']) && $data['rhd_admin_sign_off']['date'] != '')
							{
								$rhd_admin_approval_date = strtotime($data['rhd_admin_sign_off']['date']);
								if($master_to_phd_approval_date < $rhd_admin_approval_date)
								{
									$data['rhd_admin_sign_off']['active'] = 'T';
								}
								else
								{
									$data['rhd_admin_sign_off']['active'] = 'F';
								}
							}
						}
						else
						{
							$data['master_to_phd_sign_off']['active'] = 'F';
							//$data['rhd_admin_sign_off']['active'] = 'F';
						}
					}

					if($data['master_to_phd_sign_off']['active'] == 'F')
					{
						if(!isset($data['rhd_admin_sign_off']) || !isset($data['rhd_admin_sign_off']['date']) || $data['rhd_admin_sign_off']['date'] == '')
						{
							$data['rhd_admin_sign_off']['active'] = 'F';
						}
						else
						{
							$rhd_admin_approval_date = strtotime($data['rhd_admin_sign_off']['date']);
							if($postGra_approval_date < $rhd_admin_approval_date)
							{
								$data['rhd_admin_sign_off']['active'] = 'T';
							}
							else
							{
								$data['rhd_admin_sign_off']['active'] = 'F';
							}
						}
					}

				}
				else
				{
					$data['postGrad_sign_off']['active'] = 'F';
					$data['rhd_admin_sign_off']['active'] = 'F';
					$data['master_to_phd_sign_off']['active'] = 'F';

				}

			}

		}


		/*echo 'data: <pre>';
        print_r($data);
        echo '</pre>';

        echo 'response: <pre>';
        print_r($response);
        echo '</pre>';
		exit;*/


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
		if(isset($data['stu_sign_off']) && isset($data['stu_sign_off']['active']) && $data['stu_sign_off']['active'] == 'T' && isset($data['stu_sign_off']['type']) && $data['stu_sign_off']['type']!= '')
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
		if(isset($data['postGrad_sign_off']) && isset($data['postGrad_sign_off']['active']) && $data['postGrad_sign_off']['active'] == 'T' && isset($data['postGrad_sign_off']) && isset($data['postGrad_sign_off']['type']) && $data['postGrad_sign_off']['type']!= '')
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
		if(isset($data['master_to_phd_sign_off']) && isset($data['master_to_phd_sign_off']['active']) && $data['master_to_phd_sign_off']['active'] == 'T' && isset($data['master_to_phd_sign_off']) && isset($data['master_to_phd_sign_off']['type']) && $data['master_to_phd_sign_off']['type']!= '')
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
		if(isset($data['rhd_admin_sign_off']) && isset($data['rhd_admin_sign_off']['active']) && $data['rhd_admin_sign_off']['active'] == 'T' && isset($data['rhd_admin_sign_off']) && isset($data['rhd_admin_sign_off']['type']) && $data['rhd_admin_sign_off']['type']!= '')
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

    }

}



/* End of file */
