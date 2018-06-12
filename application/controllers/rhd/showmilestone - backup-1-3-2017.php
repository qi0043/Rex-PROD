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
    public function index($format='pdf', $uuid='2266afd4-62eb-4d46-8ac1-a748c4564088', $version='1')
    {
        #$this->output->enable_profiler(TRUE);
        $errdata['heading'] = "Error";
        
        if($this->validate_params($format, $uuid, $version) == false)
        {
            $errdata['message'] = "Invalid Request";
            $this->load->view('rhd/showerror_view', $errdata);
            return;
        }
        $oauth = array('oauth_client_config_name' => 'rhd');

        $this->load->helper('url');
        $this->load->library('rexrest/rexrest', $oauth);

        $ci =& get_instance();
        $ci->load->config('rex');

        $loggings = $ci->config->item('testlog');
        $this->load->library('logging/logging',$loggings); //call loggings from the lib
        $this->logger_test = $this->logging->get_logger('test'); //get test folder

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

        log_message('error', htmlentities($response['metadata']));

        $attachmentInfo = $response['attachments'];

        /*echo "<pre>";
        print_r($attachmentInfo);
        echo "Byebye!";
        echo "</pre>";
        exit;*/
        
        $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));

        $milestone_array = $this->milestoneXml2Array($this->xmlwrapper, $attachmentInfo);

//        echo "<pre>";
//        print_r($milestone_array);
//        echo "Byebye!";
//        echo "</pre>";
//        return;
        

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
        exit;*/

        if($format=='html')
        {
            $data['milestone_array']['format'] = 'html';
			
            $this->load->view('rhd/showmilestone_view', $data);
        }
        else if($format=='pdf')
        {
            $data['milestone_array']['format'] = 'pdf';
            $data['milestone_array']['uuid'] = $uuid; #item uuid
            $data['milestone_array']['version'] = $version; #item version number
			
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
            $this->load->view('rhd/showmilestone_view', $data);
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
    

    /**
     * Extract XML data and store it in array
     *
     * @param xmlwrapper $itemXml
     */
    protected function milestoneXml2Array($itemXml, $attachmentInfo)
    {
        //values of part A --- Candidate's details
	    $candidate_name = '/xml/item/people/candidate/display_name';
        $milestoneArray['metadata']['candidatename'] = $itemXml->nodeValue($candidate_name);

        $student_id = '/xml/item/people/candidate/id';
        $milestoneArray['metadata']['studentid'] = $itemXml->nodeValue($student_id);

        $student_fan = '/xml/item/people/candidate/fan';
        $milestoneArray['metadata']['studenfan'] = $itemXml->nodeValue($student_fan);

        $degree = '/xml/item/curriculum/courses/course/@degree_type';
        $milestoneArray['metadata']['degree'] = $itemXml->nodeValue($degree);

        $topic_code = '/xml/item/curriculum/topics/topic/code';
        $milestoneArray['metadata']['topiccode'] = $itemXml->nodeValue($topic_code);

        $thesis_title = '/xml/item/thesis/title';
        $milestoneArray['metadata']['thesistitle'] = $itemXml->nodeValue($thesis_title);

        $school_name = '/xml/item/curriculum/topics/topic/org_unit_name';
        $milestoneArray['metadata']['schoolname'] = $itemXml->nodeValue($school_name);

        $ft_or_pt = '/xml/item/candidature/load/FTE_type';
        $milestoneArray['metadata']['fullorparttime'] = $itemXml->nodeValue($ft_or_pt);

        $request_PhD = '/xml/item/candiature/change_request/course/type';
        $milestoneArray['metadata']['requestPhD'] = $itemXml->nodeValue($request_PhD);



        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/progression/milestone/evidence/attachments/uuid'); $j++)
        {
            $overallreport_uuid = '/xml/item/progression/milestone/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['overallreport']['uuid'][$j] = $itemXml->nodeValue($overallreport_uuid);
            $milestoneArray['metadata']['overallreport']['name'][$j] = $this->getFileName($itemXml->nodeValue($overallreport_uuid),$attachmentInfo);
            $milestoneArray['metadata']['overallreport']['link'][$j] = $this->getFileLink($itemXml->nodeValue($overallreport_uuid),$attachmentInfo);
        }

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/people/supervisors/supervisor'); $j++)
        {
            $supervisorRole = '/xml/item/people/supervisors/supervisor['.$j.']/role';
            $supervisorTitle = '/xml/item/people/supervisors/supervisor['.$j.']/title';
            $supervisorName = '/xml/item/people/supervisors/supervisor['.$j.']/display_name';

            $milestoneArray['metadata']['supervisorsdetail'][$j]['role'] = $itemXml->nodeValue($supervisorRole);
            $milestoneArray['metadata']['supervisorsdetail'][$j]['title'] = $itemXml->nodeValue($supervisorTitle);
            $milestoneArray['metadata']['supervisorsdetail'][$j]['name'] = $itemXml->nodeValue($supervisorName);
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
        }
        else{
            $RTS_end_date = '';
            $milestoneArray['metadata']['rtsenddate'] = $itemXml->nodeValue($RTS_end_date);
        }
        //the value of section 1 of part B --- Met all goals agreed at confirmation of candidature

        //the value of section 2 of part B --- timeframe feasible


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


        //the information of training --- related to the value of section 5 of part B
        $additional_education_requested = '/xml/item/progression/student_request/training';
        $milestoneArray['metadata']['additionaleducation']['requested'] = $itemXml->nodeValue($additional_education_requested);

        $additional_education_comment = '/xml/item/support/training/component/reviewer/comment';
        $milestoneArray['metadata']['additionaleducation']['comment'] = $itemXml->nodeValue($additional_education_comment);

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
            $additional_education_description_text = '';
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
            $additional_resource_description_text = '';
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
        $plan_proposal_input_text = '/xml/item/research/project/plan/proposal/evidence/text';
        $milestoneArray['metadata']['planproposal']['inputs']['text'] = $itemXml->nodeValue($plan_proposal_input_text);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/research/project/plan/proposal/evidence/attachments/uuid'); $j++){
            $plan_proposal_input_uuid = '/xml/item/research/project/plan/proposal/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['planproposal']['inputs']['uuid'][$j] = $itemXml->nodeValue($plan_proposal_input_uuid);
            $milestoneArray['metadata']['planproposal']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($plan_proposal_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['planproposal']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($plan_proposal_input_uuid),$attachmentInfo);
        }

        $plan_litreview_input_text = '/xml/item/thesis/progress/lit_review/evidence/text';
        $milestoneArray['metadata']['litreview']['inputs']['text'] = $itemXml->nodeValue($plan_litreview_input_text);

        for ($j = 1; $j <= $itemXml->numNodes('/xml/item/thesis/progress/lit_review/evidence/attachments/uuid'); $j++){
            $plan_litreview_input_uuid = '/xml/item/thesis/progress/lit_review/evidence/attachments/uuid['.$j.']';
            $milestoneArray['metadata']['litreview']['inputs']['uuid'][$j] = $itemXml->nodeValue($plan_litreview_input_uuid);
            $milestoneArray['metadata']['litreview']['inputs']['name'][$j] = $this->getFileName($itemXml->nodeValue($plan_litreview_input_uuid),$attachmentInfo);
            $milestoneArray['metadata']['litreview']['inputs']['link'][$j] = $this->getFileLink($itemXml->nodeValue($plan_litreview_input_uuid),$attachmentInfo);

        }

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

        // the information of ethics
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

        // information of text matching
        $text_matching_subjected = '/xml/item/progression/milestone/compliance/academic_integrity/text_matching/supervisor/completed';
        $milestoneArray['metadata']['textmatching']['subjected'] = $itemXml->nodeValue($text_matching_subjected);

        $text_matching_integrity = '/xml/item/progression/milestone/compliance/academic_integrity/policy/complies';
        $milestoneArray['metadata']['textmatching']['integrity'] = $itemXml->nodeValue($text_matching_integrity);

        $text_matching_input_text = '/xml/item/progression/milestone/compliance/academic_integrity/text_matching/evidence/text';
        $milestoneArray['metadata']['textmatching']['inputs']['text'] = $itemXml->nodeValue($text_matching_input_text);

        $text_matching_comment = '/xml/item/progression/milestone/compliance/academic_integrity/policy/comment';
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

        //information of supervisor's section
        $supervisor_recommendation = '/xml/item/progression/outcome/recommendation/@type';
        $milestoneArray['metadata']['supervisor']['recommendation'] = $itemXml->nodeValue($supervisor_recommendation);

        if(strnatcasecmp($milestoneArray['metadata']['supervisor']['recommendation'], 'Satisfactory') == 0) {
            $supervisor_recommendation_comment = '/xml/item/progression/outcome/recommendation/supervisor/comment';
            $milestoneArray['metadata']['supervisor']['recommendationcomment'] = $itemXml->nodeValue($supervisor_recommendation_comment);
        }
        else {
            $supervisor_recommendation_comment = '';
            $milestoneArray['metadata']['supervisor']['recommendationcomment'] = '';
        }


        $supervisor_judgement = '/xml/item/candidature/change_request/course/recommendation/supervisor/accepted';
        $milestoneArray['metadata']['supervisor']['judgement'] = $itemXml->nodeValue($supervisor_judgement);

        $supervisor_comment = '/xml/item/candidature/change_request/course/recommendation/supervisor/comment';
        $milestoneArray['metadata']['supervisor']['comment'] = $itemXml->nodeValue($supervisor_comment);

        if(strnatcasecmp($milestoneArray['metadata']['supervisor']['recommendation'], 'Resubmit') == 0) {
            $supervisor_resubmit_requiredwork = '/xml/item/progression/outcome/resubmit/required_work';
            $milestoneArray['metadata']['supervisor']['resubmit'] = $itemXml->nodeValue($supervisor_resubmit_requiredwork);
        }
        else {
            $supervisor_resubmit_requiredwork = '';
            $milestoneArray['metadata']['supervisor']['resubmit'] = '';
        }

        if(strnatcasecmp($milestoneArray['metadata']['supervisor']['recommendation'], 'Show cause') == 0) {
            $supervisor_resubmit_showcause = '/xml/item/progression/outcome/show_cause/comment';
            $milestoneArray['metadata']['supervisor']['showcause'] = $itemXml->nodeValue($supervisor_resubmit_showcause);
        }
        else {
            $supervisor_resubmit_showcause = '';
            $milestoneArray['metadata']['supervisor']['showcause'] = '';
        }


        //information of MOD by postgrad coordinator
        $mod_coordinator_support = '/xml/item/progression/outcome/recommendation/@supported';
        $milestoneArray['metadata']['mod']['coordinatorsupport'] = $itemXml->nodeValue($mod_coordinator_support);

        $mod_coordinator_comment = '/xml/item/progression/outcome/recommendation/coordinator/comment';
        $milestoneArray['metadata']['mod']['coordinatorcomment'] = $itemXml->nodeValue($mod_coordinator_comment);

        //information of MOD by candidate
        $mod_student_accepted = '/xml/item/progression/outcome/student/accepted';
        $milestoneArray['metadata']['mod']['studentaccepted'] = $itemXml->nodeValue($mod_student_accepted);

        $mod_student_comment = '/xml/item/progression/outcome/student/comment';
        $milestoneArray['metadata']['mod']['studentcomment'] = $itemXml->nodeValue($mod_student_comment);


        //information of MOD by faculty
        $mod_faculty_determination = '/xml/item/progression/outcome/@status';
        $milestoneArray['metadata']['mod']['facultydetermination'] = $itemXml->nodeValue($mod_faculty_determination);

        $mod_faculty_comment = '/xml/item/progression/outcome/determination/comment';
        $milestoneArray['metadata']['mod']['facultycomment'] = $itemXml->nodeValue($mod_faculty_comment);

        $milestone_status = '/xml/item/@itemstatus';
        $milestoneArray['metadata']['status'] = $itemXml->nodeValue($milestone_status);

        $milestone_moderated = '/xml/item/@moderating';
        $milestoneArray['metadata']['approved'] = $itemXml->nodeValue($milestone_moderated);

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
        $ci->load->config('flex');
        $username = $ci->config->item('sam_shared_secret_username');
        $sharedSecretId = $ci->config->item('sam_shared_secret_id');
        $sharedSecretValue = $ci->config->item('sam_shared_secret_value');
        
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
        if($uuid == 'missed')
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
        if($uuid == 'missed')
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
        
        if($this->validate_params('pdf', $uuid, $version) == false)
        {
            $errdata['message'] = "Invalid Request";
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . 'Invalid input params.');
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }

        $this->load->helper('url');
        $this->load->library('flexrest/flexrest');
        
        $success = $this->flexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }  
        
	#ob_end_flush();
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
        #ob_start();
        #echo 'good';

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
        header("Content-Length: $len");      // Close connection after $size characters
	echo $content;                       // Output content
	ob_flush();
        flush();                             // Force php-output-cache to flush to flex.
                                             
	ob_end_flush();
	
        // Optional: kill all other output buffering
        #while (ob_get_level() > 0) {
        #    ob_end_clean();
	#}
        
        #make sure sam work flow completely finishes
        sleep (5);

	$success = $this->flexrest->getItem($uuid, $version, $response);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        
        unset($response['headers']);
        $item_bean = $response;
        /*
        echo "<br>=====================================";
        echo "<pre>";
        print_r($response);
        echo "</pre>";
	 */
        $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));
        
        if(!$this->itemIsSam($this->xmlwrapper))
        {
            $errdata['message'] = "Item is not SAM";
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        
        $milestone_array = $this->samXml2Array($this->xmlwrapper);
        $milestone_array['status'] = $response['status'];
        $data = array('milestone_array' => $milestone_array);
        #$data['milestone_array']['avail_ref'] = $avail_ref;
        #$data['milestone_array']['avail_ver'] = $avail_ver;
        $data['milestone_array']['format'] = 'pdf';
        $data['milestone_array']['uuid'] = $uuid; #item uuid
        $data['milestone_array']['version'] = $version; #item version number

        
        $approve_date =  $data['milestone_array']['metadata']['approved'];
        $tmp_date =  $data['milestone_array']['metadata']['approvalDate'];
        if($approve_date == null)
        {
            #$errdata['message'] = '/xml/item/curriculum/assessment/approval/approved is null';
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . 'approved date is null');
            #$this->load->view('sam/showerror_view', $errdata);
            return;
        }
        $tmp_time = substr($approve_date, strpos($approve_date, 'T')+1, 8);
        $tmp_time = str_replace(':', '-', $tmp_time);
        $tmp_date_time = $tmp_date . 'T' . $tmp_time;
        
        
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
	ob_start();
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
        
        $success = $this->flexrest->filesCopy($uuid, $version, $response1);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed (filesCopy), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        if(!isset($response1['headers']['location']))
        {
            $errdata['message'] = 'No Location header in response to copy files REST call.';
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        $location = $response1['headers']['location'];
        $filearea_uuid = substr($location, strpos($location, 'file')+5, 36);
        
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
        
        if(!isset($item_bean['attachments']))
            $item_bean['attachments'] = array();
        #$attachment_count = count($item_bean['attachments']);
        $existing_attachments = $item_bean['attachments'];
        $new_attachments = array();
        
        $xpath_files = '/xml/item/curriculum/assessment/SAMs/files';
         
        $node_previous_files = null;
        $xpath_previous_files = '/xml/item/curriculum/assessment/SAMs/previous_files';
        $xpath_files_first_file = $xpath_files . '/file[1]/uuid';
        
        $xpath_previous_files_first_file = $xpath_previous_files . '/file[1]/uuid';
        $first_files_uuid = $this->xmlwrapper->nodeValue($xpath_files_first_file);
        $first_previous_files_uuid = $this->xmlwrapper->nodeValue($xpath_previous_files_first_file);
        
        $old_files_exist = $first_files_uuid == null ? false : true;
        $previous_files_exist = $first_previous_files_uuid == null ? false : true;
        
        $count_old_files = $this->xmlwrapper->numNodes($xpath_files . '/file');
        $count_previous_files = $this->xmlwrapper->numNodes($xpath_previous_files . '/file');
        #echo $old_files_exist . ' dd '; echo $count_old_files; exit();
        
        $old_file_array = array();
        $previous_file_uuid_suffix = 200;
        $next_previous_file_uuid = '';
        $old_one_file_multi_avails = false;
        $old_avails = array();
        
        /****************************************************************
         * copy all the <files> to <previous_files>
         ****************************************************************/
        ####
        
        /*log_message('error', 'Attaching SAM pdf, count_old_files: '.$count_old_files);
        if($old_files_exist == true)
            log_message('error', 'Attaching SAM pdf, old_files_exist: true ');
        else
            log_message('error', 'Attaching SAM pdf, old_files_exist: false ');*/
        
        if($count_old_files > 0 && $old_files_exist == false)
        {
            log_message('error', 'Attaching SAM pdf, $count_old_files > 0 && $old_files_exist == false, item uuid: ' . $uuid);
            exit();
        }
        
        #if($old_files_exist)
        if($count_old_files > 0)
        {
            $node_previous_files = $this->xmlwrapper->createNodeFromXPath($xpath_previous_files);
            #if($previous_files_exist)
            if($count_previous_files > 0)
                $previous_file_uuid_suffix += $count_previous_files;
            
            $next_previous_file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $previous_file_uuid_suffix);

            #echo $next_previous_file_uuid; exit();
            
            for ($j = 1; $j <= $count_old_files; $j++) 
            {
                $tmp_xpath_file_uuid = $xpath_files . '/file['.$j.']/uuid';
                $tmp_xpath_file_ref = $xpath_files . '/file['.$j.']/@ref';
                $tmp_xpath_file_distributed = $xpath_files . '/file['.$j.']/@distributed';
                 
                $tmp_file_uuid = $this->xmlwrapper->nodeValue($tmp_xpath_file_uuid);
                $tmp_file_ref = $this->xmlwrapper->nodeValue($tmp_xpath_file_ref);
                $tmp_file_distributed = $this->xmlwrapper->nodeValue($tmp_xpath_file_distributed);
                
                $old_file_array[$j-1]['uuid'] = $tmp_file_uuid;
                $old_file_array[$j-1]['ref'] = $tmp_file_ref;
                $old_file_array[$j-1]['dist'] = $tmp_file_distributed;
                
                $old_avails[$j-1] = $tmp_file_ref;
            }
            
            if($count_old_files > 1 && 
                    $old_file_array[0]['uuid'] == $old_file_array[1]['uuid'] &&
                    $old_file_array[0]['uuid'] == $old_file_array[$count_old_files-1]['uuid'])
                $old_one_file_multi_avails = true;
            else
                $old_one_file_multi_avails = false;
            
            #Append the files to previous_files subtree, change uuid
            for ($j = 0; $j < $count_old_files; $j++) 
            {
                $tmp_node_previous_file = $this->xmlwrapper->createNode($node_previous_files, "file");
                $tmp_node_previous_file_uuid = $this->xmlwrapper->createNode($tmp_node_previous_file, "uuid");
                $tmp_node_previous_file_ref = $this->xmlwrapper->createAttribute($tmp_node_previous_file, "ref");
                $tmp_node_previous_file_distri = $this->xmlwrapper->createAttribute($tmp_node_previous_file, "distributed");
                
                $tmp_node_previous_file_ref->nodeValue = $old_file_array[$j]['ref'];
                $tmp_node_previous_file_distri->nodeValue = $old_file_array[$j]['dist'];
                
                $tmp_node_previous_file_uuid->nodeValue = $next_previous_file_uuid; 
                for($i=0; $i<count($existing_attachments); $i++)
                {
                    if($existing_attachments[$i]['uuid'] == $old_file_array[$j]['uuid'])
                        $existing_attachments[$i]['uuid'] = $next_previous_file_uuid;
                }
                if($count_old_files>1 && $old_one_file_multi_avails == false)
                {
                    $previous_file_uuid_suffix ++;
                    $next_previous_file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $previous_file_uuid_suffix);
                }
            }
        }
        
        #print_r(simplexml_import_dom($node_previous_files)->asXML());exit();
        
        
        $this->xmlwrapper->deleteNodeFromXPath($xpath_files);        
        $node_files = $this->xmlwrapper->createNodeFromXPath($xpath_files);
        #$distributed = $approve_date; #new DateTime("now");
        #$approve_time = $distributed->format('c');
        #$item_bean['attachments'] = null;
        $file_uuid = $uuid;
        $largest_suffix_number = 0;
        
        #find out the largest suffix number in uuid (last three chars)
        for ($j = 0; $j < $count_old_files; $j++) 
        {
            $file_suffix_number = intval(substr($old_file_array[$j]['uuid'], 33, 3));
            if($file_suffix_number > $largest_suffix_number)
                $largest_suffix_number = $file_suffix_number;
        }
        
        $new_one_file_multi_avails = false;
        $new_avails = array();
        $i = 0;
        foreach ($data['milestone_array']['metadata']['availability'] as $availdata  )
        {
            $new_avails[$i] = $availdata['avRef'];
            $i ++;
        }
        if($data['milestone_array']['metadata']['multiple'] != 'yes' && count($new_avails) > 1)
            $new_one_file_multi_avails = true;
        
        $attachment_count = 0;
        foreach ($data['milestone_array']['metadata']['availability'] as $availdata  )
        {
            $data['milestone_array']['avail_ref'] = $availdata['avRef'];
            $data['milestone_array']['avail_ver'] = $availdata['avVersion'];

            if($data['milestone_array']['metadata']['multiple'] == 'yes' || $attachment_count == 0) 
            {

                ob_start();
                $this->load->view('sam/showmilestone_view', $data);
                $html = ob_get_contents();
                ob_end_clean();

                $pdfclsname = 'pdf_class'.$attachment_count;
                $this->load->library('pdf/pdf_class', null, $pdfclsname); 
                #$this->pdf_class->SetDisplayMode('fullpage');
                $this->$pdfclsname->setFooter('{PAGENO} / {nb}');
                $this->$pdfclsname->WriteHTML($html);
                if($data['milestone_array']['metadata']['multiple'] == 'yes' || count($data['milestone_array']['metadata']['availability']) === 1) 
                {
                    $filename = $data['milestone_array']['avail_ref'] . '_' . $tmp_date_time . '.pdf'; 
                }
                else
                {
                    $filename = $data['milestone_array']['metadata']['availability_name'];
                    if(substr($filename, -2) == '. ')
                        $filename = substr($filename, 0, strlen($filename)-2);
                    if(substr($filename, -1) == '.')
                        $filename = substr($filename, 0, strlen($filename)-1);
                    $filename = str_replace('.', ' ', $filename);
                    $filename = str_replace('(', ' ', $filename);
                    $filename = str_replace(')', ' ', $filename);
                    $filename = str_replace('/', ' ', $filename);
                    $filename = str_replace(',', ' ', $filename);
                    $filename = str_replace(':', ' ', $filename);
                    $filename = str_replace('[', ' ', $filename);
                    $filename = str_replace(']', ' ', $filename);
                    $filename = preg_replace('/\s+/', ' ', $filename);
                    if(strlen($filename) > 70)
                        $filename = substr($filename, 0, 70);
                    if(substr($filename, -1) == ' ')
                        $filename = substr($filename, 0, strlen($filename)-1);
                    $filename = str_replace(' ', '_', $filename);
                    $filename = $filename . '_' . $tmp_date_time . '.pdf';
                }

                $pdf_content = $this->$pdfclsname->Output($filename, 'S');

                $success = $this->flexrest->fileUpload($filearea_uuid, $filename, $pdf_content, $response2);
                if(!$success)
                {
                    $errdata['message'] = $this->flexrest->error;
                    log_message('error', 'Attaching SAM pdf failed (fileUpload), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
                    $this->load->view('sam/showerror_view', $errdata);
                    return;
                }

                /***************************************************************
                 * Determine the file uuid, 
                 *    - reuse exiting uuid if topic availability is the same
                 *    - otherwise generate new file uuid
                 * 
                 ***************************************************************/
                
                if($count_old_files > 0)
                {
                    $file_uuid = null;
                    #$largest_suffix_number = 0;
                }
                else
                {
                    #No old attachments, generagte new file uuid
                    $file_uuid = sprintf("%03d", $attachment_count);
                    $file_uuid = substr($uuid, 0, 33) . $file_uuid;
                }
                
                $reuse_old_file_uuid = true;

                /***************************************************************
                 * Old avails:
                 *   A_B_C   1 file uuid-1
                 * New avails:
                 *   A_B_C_D 1 file uuid-1 (reuse exiting uuid)
                 * 
                 * Old avails:
                 *   A_B_C   1 file uuid-1
                 * New avails:
                 *   B_C     1 file uuid-2 (generate new uuid)  
                 ***************************************************************/
                if($old_one_file_multi_avails == true && $new_one_file_multi_avails == true)
                {
                    if(count(array_diff($old_avails, $new_avails)) > 0)
                    {
                        #There is new availability added in, generate new file uuid
                        $reuse_old_file_uuid = false;
                    }
                    else
                    {
                        $reuse_old_file_uuid = true;
                        $file_uuid = $old_file_array[0]['uuid'];
                    }
                }
                
                /*
                echo '<pre>';
                echo '$old_one_file_multi_avails ' . $old_one_file_multi_avails;echo '<br>';
                echo '$new_one_file_multi_avails ' . $new_one_file_multi_avails;echo '<br>';
                echo '$file_uuid ' . $file_uuid;echo '<br>';
                print_r($old_avails);echo '<br>';
                print_r($new_avails);echo '<br>';
                echo '</pre>';exit();
                */
                
                /***************************************************************
                 * Old avails:
                 *   A_B 1 file uuid-1
                 * New avails:
                 *   A   1 file uuid-2
                 *   B   1 file uuid-3
                 ***************************************************************/
                if($old_one_file_multi_avails == true && $new_one_file_multi_avails == false)
                {
                    #generate new file uuid
                    $reuse_old_file_uuid = false;
                }
                
                /***************************************************************
                 * Old avails:
                 *   A   1 file uuid-1
                 *   B   1 file uuid-2
                 * New avails:
                 *   B_C 1 file uuid-2 (reuse uuid of common avail, generate new one if no common avail )
                 ***************************************************************/
                if($old_one_file_multi_avails == false && $new_one_file_multi_avails == true)
                {
                    #
                    $reuse_old_file_uuid = true;
                    for ($j = 0; $j < $count_old_files; $j++) 
                    {
                        for($i = 0; $i < count($new_avails); $i++)
                        {
                            if($new_avails[$i] == $old_file_array[$j]['ref'])
                            $file_uuid = $old_file_array[$j]['uuid'];
                            break;
                        }
                        if($file_uuid != null)
                            break;
                    }
                }
                
                /***************************************************************
                 * Old avails:
                 *   A   1 file uuid-1
                 *   B   1 file uuid-2
                 * New avails:
                 *   A   1 file uuid-1
                 *   C   1 file uuid-3
                 * 
                 * Example:
                 * <previous_files>
                 *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4700A_2014_S2">
                 *       <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc200</uuid>
                 *    </file>
                 *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4700B_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc201</uuid>
                 *    </file>
                 *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4700C_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc202</uuid>
                 *    </file>
                 *  </previous_files>
                 *  <files>
                 *    <file distributed="2014-07-23T13:59:44+0930" ref="COMP4700A_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc000</uuid>
                 *    </file>
                 *    <file distributed="2014-07-23T13:59:44+0930" ref="COMP4700D_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc003</uuid>
                 *    </file>
                 *    <file distributed="2014-07-23T13:59:44+0930" ref="COMP4700C_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc002</uuid>
                 *    </file>
                 *  </files>
                 ***************************************************************/

                #Reuse exiting file uuid
                if($file_uuid === null && $reuse_old_file_uuid == true)
                {
                    for ($j = 0; $j < $count_old_files; $j++) 
                    {
                        #For the same avilability, the uuid must remain the same
                        if($data['milestone_array']['avail_ref'] == $old_file_array[$j]['ref'])
                        {
                            $file_uuid = $old_file_array[$j]['uuid'];
                            #echo '111 ' . $file_uuid; exit();
                            #if(intval(substr($file_uuid, 32, 3)) > $largest_suffix_number)
                            #    $largest_suffix_number = intval(substr($file_uuid, 32, 3));
                            break;
                        }
                    }
                }

                #the availability is new so it is not found in the exiting files, so assign new uuid to it
                if($file_uuid === null)
                {
                    $largest_suffix_number ++;
                    $file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $largest_suffix_number);
                }

                $new_attachments[$attachment_count] = array('type'=>'file', 
                                              'filename'=>$filename, 
                                              'description'=>$filename,
                                              'uuid'=>$file_uuid);
                #$item_bean['attachments'][$attachment_count] = array('type'=>'file', 
                #                              'filename'=>$filename, 
                #                              'description'=>$filename,
                #                              'uuid'=>$file_uuid);
            }
            $node_file = $this->xmlwrapper->createNode($node_files, "file");
            $node_uuid = $this->xmlwrapper->createNode($node_file, "uuid");
            $node_ref = $this->xmlwrapper->createAttribute($node_file, "ref");
            $node_distri = $this->xmlwrapper->createAttribute($node_file, "distributed");
            $node_uuid->nodeValue = $file_uuid; 
            $node_ref->nodeValue = $data['milestone_array']['avail_ref'];
            #$node_distri->nodeValue = $distributed->format('c');
            $node_distri->nodeValue = $approve_date;

            $attachment_count ++;
        }
       
        
        $item_bean['attachments'] = array_merge($new_attachments, $existing_attachments);
        $item_bean['metadata'] = $this->xmlwrapper->__toString();
        
        /*
        echo '<pre>';
        #print_r($new_attachments);echo '<br>';
        #print_r($existing_attachments);echo '<br>';
        echo $item_bean['metadata']; echo '<br>';
        #print_r(json_decode(json_encode($item_bean['metadata']), true));echo '<br>';
        print_r($item_bean['attachments']);
        log_message('error', $item_bean['metadata']);
        echo '</pre>';
        exit();
        */
        
        $success = $this->flexrest->editItem($uuid, $version, $item_bean, $response3, $filearea_uuid);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed (editItem), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        #log_message('error', htmlentities($response['metadata']));
        /*
        echo "<br>=====================================";
        echo "<pre>";
        print_r($response3);
	echo "</pre>";
	 */
    }   
    
    
    
    
    /**
     * Attach SAM PDF to ITEM
     *
     * @param string $uuid, item UUID
     * @param string $version, item Version
     */
    public function attach_pdf_old_lx9n3h2_old($uuid='missed', $version='missed')
    {
        #$this->output->enable_profiler(TRUE);
        $errdata['heading'] = "Error";
        
        if($this->validate_params('pdf', $uuid, $version) == false)
        {
            $errdata['message'] = "Invalid Request";
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . 'Invalid input params.');
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }

        $this->load->helper('url');
        $this->load->library('flexrest/flexrest');
        
        $success = $this->flexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }  
        
	#ob_end_flush();
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
        #ob_start();
        #echo 'good';

        // Ignore connection-closing by the client/user
        ignore_user_abort(true);

        // Set your timelimit to a length long enough for your script to run, 
        // but not so long it will bog down your server in case multiple versions run 
        // or this script get's in an endless loop.
        /*if ( 
             !ini_get('safe_mode') 
             && strpos(ini_get('disable_functions'), 'set_time_limit') === FALSE 
        ){
            set_time_limit(60);
        }*/

        // Get your output and send it to the client
        $content = 'good';         // Get the content of the output buffer
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
	
        // Optional: kill all other output buffering
        #while (ob_get_level() > 0) {
        #    ob_end_clean();
	#}
        
        #make sure sam work flow completely finishes
        sleep (5);

	$success = $this->flexrest->getItem($uuid, $version, $response);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        
        unset($response['headers']);
        $item_bean = $response;
        /*
        echo "<br>=====================================";
        echo "<pre>";
        print_r($response);
        echo "</pre>";
	 */
        $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));
        
        if(!$this->itemIsSam($this->xmlwrapper))
        {
            $errdata['message'] = "Item is not SAM";
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        
        $milestone_array = $this->samXml2Array($this->xmlwrapper);
        $milestone_array['status'] = $response['status'];
        $data = array('milestone_array' => $milestone_array);
        #$data['milestone_array']['avail_ref'] = $avail_ref;
        #$data['milestone_array']['avail_ver'] = $avail_ver;
        $data['milestone_array']['format'] = 'pdf';
        $data['milestone_array']['uuid'] = $uuid; #item uuid
        $data['milestone_array']['version'] = $version; #item version number

	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
	ob_start();
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
        
        $success = $this->flexrest->filesCopy($uuid, $version, $response1);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        if(!isset($response1['headers']['location']))
        {
            $errdata['message'] = 'No Location header in response to copy files REST call.';
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        $location = $response1['headers']['location'];
        $filearea_uuid = substr($location, strpos($location, 'file')+5, 36);
        
        $xpath_files = '/xml/item/curriculum/assessment/SAMs/files';
        #$xpath_uuid = '/xml/item/curriculum/files/file/uuid';
        #$this->xmlwrapper->deleteNodeFromXPath('/xml/item/curriculum/SAMs');
        $this->xmlwrapper->deleteNodeFromXPath($xpath_files);        
        $node_files = $this->xmlwrapper->createNodeFromXPath($xpath_files);
        $distributed = new DateTime("now");
        $item_bean['attachments'] = null;
        $file_uuid = $uuid;
        
        #if($data['milestone_array']['metadata']['multiple'] == 'yes') 
        {
            $attachment_count = 0;
            foreach ($data['milestone_array']['metadata']['availability'] as $availdata  )
            {
                $data['milestone_array']['avail_ref'] = $availdata['avRef'];
                $data['milestone_array']['avail_ver'] = $availdata['avVersion'];
                
                if($data['milestone_array']['metadata']['multiple'] == 'yes' || $attachment_count == 0) 
                {

                    ob_start();
                    $this->load->view('sam/showmilestone_view', $data);
                    $html = ob_get_contents();
                    ob_end_clean();

                    $pdfclsname = 'pdf_class'.$attachment_count;
                    $this->load->library('pdf/pdf_class', null, $pdfclsname); 
                    #$this->pdf_class->SetDisplayMode('fullpage');
                    $this->$pdfclsname->setFooter('{PAGENO} / {nb}');
                    $this->$pdfclsname->WriteHTML($html);
                    if($data['milestone_array']['metadata']['multiple'] == 'yes') 
                        $filename = $data['milestone_array']['avail_ref'] . '.pdf'; 
                    else
                    {
                        $filename = $data['milestone_array']['metadata']['availability_name'];
                        if(substr($filename, -2) == '. ')
                                $filename = substr($filename, 0, strlen($filename)-2);
                        $filename = str_replace('. ', '_', $filename);
                        $filename .= '.pdf';
                    }
                    
                    $pdf_content = $this->$pdfclsname->Output($filename, 'S');

                    $success = $this->flexrest->fileUpload($filearea_uuid, $filename, $pdf_content, $response2);
                    if(!$success)
                    {
                        $errdata['message'] = $this->flexrest->error;
                        log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
                        $this->load->view('sam/showerror_view', $errdata);
                        return;
                    }

                    $file_uuid = sprintf("%03d", $attachment_count);
                    $file_uuid = substr($uuid, 0, 33) . $file_uuid;
                    $item_bean['attachments'][$attachment_count] = array('type'=>'file', 
                                                  'filename'=>$filename, 
                                                  'description'=>$filename,
                                                  'uuid'=>$file_uuid);
                }
                $node_file = $this->xmlwrapper->createNode($node_files, "file");
                $node_uuid = $this->xmlwrapper->createNode($node_file, "uuid");
                $node_ref = $this->xmlwrapper->createAttribute($node_file, "ref");
                $node_distri = $this->xmlwrapper->createAttribute($node_file, "distributed");
                $node_uuid->nodeValue = $file_uuid; 
                $node_ref->nodeValue = $data['milestone_array']['avail_ref'];
                $node_distri->nodeValue = $distributed->format('c');
                
                $attachment_count ++;
            }
        }
        
        
        $item_bean['metadata'] = $this->xmlwrapper->__toString();
        $success = $this->flexrest->editItem($uuid, $version, $item_bean, $response3, $filearea_uuid);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        #log_message('error', htmlentities($response['metadata']));
        /*
        echo "<br>=====================================";
        echo "<pre>";
        print_r($response3);
	echo "</pre>";
	 */
    }   
    
    /**
     * Attach SAM PDF to ITEM
     *
     * @param string $uuid, item UUID
     * @param string $version, item Version
     */
    public function attach_pdf_temp_klk398wd($uuid='missed', $version='missed')
    {
        #$filename_encode = 'kk   kk kk';
        #$filename_encode = rawurlencode($filename_encode);
        #echo $filename_encode;
        #exit();
        
        #$this->output->enable_profiler(TRUE);
        $errdata['heading'] = "Error";
        
        if($this->validate_params('pdf', $uuid, $version) == false)
        {
            $errdata['message'] = "Invalid Request";
            log_message('error', 'Attaching SAM pdf failed (validate_params), item uuid: ' . $uuid . ', error: ' . 'Invalid input params.');
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }

        $this->load->helper('url');
        $this->load->library('flexrest/flexrest');
        
        $success = $this->flexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }  
        
	#ob_end_flush();
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
        #ob_start();
        #echo 'good';

        // Ignore connection-closing by the client/user
        ignore_user_abort(true);

        // Set your timelimit to a length long enough for your script to run, 
        // but not so long it will bog down your server in case multiple versions run 
        // or this script get's in an endless loop.
        /*if ( 
             !ini_get('safe_mode') 
             && strpos(ini_get('disable_functions'), 'set_time_limit') === FALSE 
        ){
            set_time_limit(60);
        }*/
        /*
        // Get your output and send it to the client
        $content = 'good';         // Get the content of the output buffer

	$len = strlen($content);             
	header('Content-Type: text/html; charset=UTF-8');
	header('Content-Encoding: none;');
        header('Connection: close');         // Tell the client to close connection
        header("Content-Length: $len");      // Close connection after $size characters
	echo $content;                       // Output content
	ob_flush();
        flush();                             // Force php-output-cache to flush to flex.
                                             
	ob_end_flush();
	*/
        // Optional: kill all other output buffering
        #while (ob_get_level() > 0) {
        #    ob_end_clean();
	#}
        
        #make sure sam work flow completely finishes
        #sleep (5);

	$success = $this->flexrest->getItem($uuid, $version, $response);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed (getItem), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        
        unset($response['headers']);
        $item_bean = $response;
        /*
        echo "<br>=====================================";
        echo "<pre>";
        print_r($response);
        echo "</pre>";
	exit();
        */
        $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));
        
        if(!$this->itemIsSam($this->xmlwrapper))
        {
            $errdata['message'] = "Item is not SAM";
            log_message('error', 'Attaching SAM pdf failed (itemIsSam), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        
        $xpath_files = '/xml/item/curriculum/assessment/SAMs/files';
        #$xpath_uuid = '/xml/item/curriculum/files/file/uuid';
        #$this->xmlwrapper->deleteNodeFromXPath('/xml/item/curriculum/SAMs');
        $xpath_files_first_file = $xpath_files . '/file[1]/@distributed';
        $approve_date_old = $this->xmlwrapper->nodeValue($xpath_files_first_file);
        
        $xpath_approved = '/xml/item/curriculum/assessment/approval/approved';
        $xpath_approval_date = '/xml/item/curriculum/assessment/approval/date';
        #$xpath_distributed_date = '/xml/item/curriculum/assessment/SAMs/distributed';
        
        $approve_date = $this->xmlwrapper->nodeValue($xpath_approved);
        $tmp_date = $this->xmlwrapper->nodeValue($xpath_approval_date);
        
        if($approve_date == null)
        {
            $errdata['message'] = '/xml/item/curriculum/assessment/approval/approved is null';
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        #$this->xmlwrapper->setNodeValue($xpath_distributed_date, $tmp_date);
        #$this->xmlwrapper->setNodeValue($xpath_approved, $approve_date);
        #$this->xmlwrapper->setNodeValue($xpath_approval_date, substr($approve_date, 0, 10));
        $tmp_time = substr($approve_date, strpos($approve_date, 'T')+1, 8);
        $tmp_time = str_replace(':', '-', $tmp_time);
        $tmp_date_time = $tmp_date . 'T' . $tmp_time;
        #echo $tmp_time; exit();
        
        $milestone_array = $this->samXml2Array($this->xmlwrapper);
        $milestone_array['status'] = $response['status'];
        $data = array('milestone_array' => $milestone_array);
        #$data['milestone_array']['avail_ref'] = $avail_ref;
        #$data['milestone_array']['avail_ver'] = $avail_ver;
        $data['milestone_array']['format'] = 'pdf';
        $data['milestone_array']['uuid'] = $uuid; #item uuid
        $data['milestone_array']['version'] = $version; #item version number

	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
	ob_start();
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
        
        $success = $this->flexrest->filesCopy($uuid, $version, $response1);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid (filesCopy): ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        if(!isset($response1['headers']['location']))
        {
            $errdata['message'] = 'No Location header in response to copy files REST call.';
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        $location = $response1['headers']['location'];
        $filearea_uuid = substr($location, strpos($location, 'file')+5, 36);
        
       
        
        $this->xmlwrapper->deleteNodeFromXPath($xpath_files);        
        $node_files = $this->xmlwrapper->createNodeFromXPath($xpath_files);
        
        
        #echo $approve_date; echo 'kkk'; exit();
        
        ##$xpath_approvedate = '/xml/item/moderation/liveapprovaldate';
        ##$approve_date = $this->xmlwrapper->nodeValue($xpath_approvedate) . '111';
        #echo $approve_date; exit();
        #$distributed = new DateTime("now");
        $distributed = $approve_date;
        $item_bean['attachments'] = null;
        $file_uuid = $uuid;
        
        #if($data['milestone_array']['metadata']['multiple'] == 'yes') 
        {
            $attachment_count = 0;
            foreach ($data['milestone_array']['metadata']['availability'] as $availdata  )
            {
                $data['milestone_array']['avail_ref'] = $availdata['avRef'];
                $data['milestone_array']['avail_ver'] = $availdata['avVersion'];
                
                if($data['milestone_array']['metadata']['multiple'] == 'yes' || $attachment_count == 0) 
                {

                    ob_start();
                    $this->load->view('sam/showmilestone_view', $data);
                    $html = ob_get_contents();
                    ob_end_clean();

                    $pdfclsname = 'pdf_class'.$attachment_count;
                    $this->load->library('pdf/pdf_class', null, $pdfclsname); 
                    #$this->pdf_class->SetDisplayMode('fullpage');
                    $this->$pdfclsname->setFooter('{PAGENO} / {nb}');
                    $this->$pdfclsname->WriteHTML($html);
                    if($data['milestone_array']['metadata']['multiple'] == 'yes') 
                    {
                        #$filename = $data['milestone_array']['avail_ref'] . '.pdf'; 
                        #$filename_date = $data['milestone_array']['avail_ref'] . '_' . $approve_date . '.pdf';
                        
                        $filename = $data['milestone_array']['avail_ref'] . '_' . $approve_date_old . '.pdf';
                        $filename_new = $data['milestone_array']['avail_ref'] . '_' . $tmp_date_time . '.pdf';
                    }
                    else
                    {
                        $filename = $data['milestone_array']['metadata']['availability_name'];
                        if(substr($filename, -2) == '. ')
                                $filename = substr($filename, 0, strlen($filename)-2);
                        $filename = str_replace('. ', '_', $filename);
                        $filename = $filename . '_' . $approve_date_old . '.pdf';
                        
                        if(count($data['milestone_array']['metadata']['availability']) === 1)
                        {
                            $filename_new = $data['milestone_array']['avail_ref'] . '_' . $tmp_date_time . '.pdf';
                        }
                        else
                        {
                            $filename_new = $data['milestone_array']['metadata']['availability_name'];
                            if(substr($filename_new, -2) == '. ')
                                $filename_new = substr($filename_new, 0, strlen($filename_new)-2);
                            if(substr($filename_new, -1) == '.')
                                $filename_new = substr($filename_new, 0, strlen($filename_new)-1);
                            $filename_new = str_replace('.', ' ', $filename_new);
                            $filename_new = str_replace('(', ' ', $filename_new);
                            $filename_new = str_replace(')', ' ', $filename_new);
                            $filename_new = str_replace('/', ' ', $filename_new);
                            $filename_new = str_replace(',', ' ', $filename_new);
                            $filename_new = str_replace(':', ' ', $filename_new);
                            $filename_new = str_replace('[', ' ', $filename_new);
                            $filename_new = str_replace(']', ' ', $filename_new);
                            $filename_new = preg_replace('/\s+/', ' ', $filename_new);
                            $filename_new = str_replace(' ', '_', $filename_new);
                            $filename_new = $filename_new . '_' . $tmp_date_time . '.pdf';
                        }
                    }
                    
                    $filename_encode = rawurlencode($filename);
                    $success = $this->flexrest->fileDelete($filearea_uuid, $filename_encode, $response2);
                    if(!$success)
                    {
                        $errdata['message'] = $this->flexrest->error;
                        #log_message('error', 'Attaching SAM pdf failed (fileDelete), item uuid: ' . $uuid . ', error: ' . $errdata['message'] . ' file name: ' . $filename_encode);
                        #$this->load->view('sam/showerror_view', $errdata);
                        #return;
                        $filename_encode = str_replace('14%20S2', '14%20%20%20S2', $filename_encode);
                        $success = $this->flexrest->fileDelete($filearea_uuid, $filename_encode, $response2);
                        if(!$success)
                        {
                            #$errdata['message'] = $this->flexrest->error;
                            log_message('error', 'Attaching SAM pdf failed (fileDelete), item uuid: ' . $uuid . ', error: ' . $errdata['message'] . ' file name: ' . $filename_encode);
                            #$this->load->view('sam/showerror_view', $errdata);
                            #exit();
                            echo 'failed to delete old file<br>';
                        }
                        #echo "Changed to 3 spaces and file deleted!";
                    }
                    #echo 'Old file deleted<br>';
                    echo 'new file:' . $filename_new . '<br>';
                    
                    $pdf_content = $this->$pdfclsname->Output($filename_new, 'S');

                    $success = $this->flexrest->fileUpload($filearea_uuid, $filename_new, $pdf_content, $response3);
                    #echo 'after fileUpload<br>';
                    if(!$success)
                    {
                        $errdata['message'] = $this->flexrest->error;
                        log_message('error', 'Attaching SAM pdf failed (fileUpload), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
                        $this->load->view('sam/showerror_view', $errdata);
                        return;
                    }
                    echo 'fileUpload success<br>';

                    $file_uuid = sprintf("%03d", $attachment_count);
                    $file_uuid = substr($uuid, 0, 33) . $file_uuid;
                    $item_bean['attachments'][$attachment_count] = array('type'=>'file', 
                                                  'filename'=>$filename_new, 
                                                  'description'=>$filename_new,
                                                  'uuid'=>$file_uuid);
                    #echo 'after setting $item_bean[attachments][$attachment_count]<br>';
                }
                $node_file = $this->xmlwrapper->createNode($node_files, "file");
                $node_uuid = $this->xmlwrapper->createNode($node_file, "uuid");
                $node_ref = $this->xmlwrapper->createAttribute($node_file, "ref");
                $node_distri = $this->xmlwrapper->createAttribute($node_file, "distributed");
                $node_uuid->nodeValue = $file_uuid; 
                $node_ref->nodeValue = $data['milestone_array']['avail_ref'];
                $node_distri->nodeValue = $distributed;
                
                $attachment_count ++;
            }
        }
        
        
        $item_bean['metadata'] = $this->xmlwrapper->__toString();
        $success = $this->flexrest->editItem($uuid, $version, $item_bean, $response4, $filearea_uuid);
        #echo 'after editItem<br>';
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed (editItem), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        echo 'editItem success<br>';
        #log_message('error', htmlentities($response['metadata']));
        /*
        echo "<br>=====================================";
        echo "<pre>";
        print_r($response3);
	echo "</pre>";
	 */
    }   
    
    /**
     * Attach SAM PDF to ITEM
     *   More detailed comments in line
     *
     * @param string $uuid, item UUID
     * @param string $version, item Version
     */
    public function attach_pdf_temp_manual_f9dj4k56($uuid='missed', $version='missed')
    {
        #$this->output->enable_profiler(TRUE);
        $errdata['heading'] = "Error";
        
        if($this->validate_params('pdf', $uuid, $version) == false)
        {
            $errdata['message'] = "Invalid Request";
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . 'Invalid input params.');
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }

        $this->load->helper('url');
        $this->load->library('flexrest/flexrest');
        
        $success = $this->flexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }  
        
	#ob_end_flush();
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
        #ob_start();
        #echo 'good';

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

        /*
        $content = 'good';         // Get the content of the output buffer
                            
	$len = strlen($content);             
	header('Content-Type: text/html; charset=UTF-8');
	header('Content-Encoding: none;');
        header('Connection: close');         // Tell the client to close connection
        header("Content-Length: $len");      // Close connection after $size characters
	echo $content;                       // Output content
	ob_flush();
        flush();                             // Force php-output-cache to flush to flex.
                                             
	ob_end_flush();
	
        sleep (5);
        */

	$success = $this->flexrest->getItem($uuid, $version, $response);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        echo '<br><br>Finish getItem.';
        
        unset($response['headers']);
        $item_bean = $response;
        /*
        echo "<br>=====================================";
        echo "<pre>";
        print_r($response);
        echo "</pre>";
	 */
        $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));
        
        if(!$this->itemIsSam($this->xmlwrapper))
        {
            $errdata['message'] = "Item is not SAM";
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        
        $milestone_array = $this->samXml2Array($this->xmlwrapper);
        $milestone_array['status'] = $response['status'];
        $data = array('milestone_array' => $milestone_array);
        #$data['milestone_array']['avail_ref'] = $avail_ref;
        #$data['milestone_array']['avail_ver'] = $avail_ver;
        $data['milestone_array']['format'] = 'pdf';
        $data['milestone_array']['uuid'] = $uuid; #item uuid
        $data['milestone_array']['version'] = $version; #item version number

        
        $approve_date =  $data['milestone_array']['metadata']['approved'];
        $tmp_date =  $data['milestone_array']['metadata']['approvalDate'];
        if($approve_date == null)
        {
            #$errdata['message'] = '/xml/item/curriculum/assessment/approval/approved is null';
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . 'approved date is null');
            #$this->load->view('sam/showerror_view', $errdata);
            return;
        }
        #$tmp_time = substr($approve_date, strpos($approve_date, 'T')+1, 8);
        #$tmp_time = str_replace(':', '-', $tmp_time);
        #$tmp_date_time = $tmp_date . 'T' . $tmp_time;
        $tmp_date_time = $tmp_date;####
        $tmp_distributed = $data['milestone_array']['metadata']['approval'];####
        
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
	ob_start();
	#log_message('error', 'ob_get_level ():');
	#log_message('error', ob_get_level ());
        
        $success = $this->flexrest->filesCopy($uuid, $version, $response1);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed (filesCopy), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        echo '<br><br>Finish filesCopy.';
        
        if(!isset($response1['headers']['location']))
        {
            $errdata['message'] = 'No Location header in response to copy files REST call.';
            log_message('error', 'Attaching SAM pdf failed, item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        $location = $response1['headers']['location'];
        $filearea_uuid = substr($location, strpos($location, 'file')+5, 36);
        
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
        
        if(!isset($item_bean['attachments']))
            $item_bean['attachments'] = array();
        #$attachment_count = count($item_bean['attachments']);
        $existing_attachments = $item_bean['attachments'];
        $new_attachments = array();
        
        $xpath_files = '/xml/item/curriculum/assessment/SAMs/files';
         
        $node_previous_files = null;
        $xpath_previous_files = '/xml/item/curriculum/assessment/SAMs/previous_files';
        $xpath_files_first_file = $xpath_files . '/file[1]/uuid';
        
        $xpath_previous_files_first_file = $xpath_previous_files . '/file[1]/uuid';
        $first_files_uuid = $this->xmlwrapper->nodeValue($xpath_files_first_file);
        $first_previous_files_uuid = $this->xmlwrapper->nodeValue($xpath_previous_files_first_file);
        
        $old_files_exist = $first_files_uuid == null ? false : true;
        $previous_files_exist = $first_previous_files_uuid == null ? false : true;
        
        $count_old_files = $this->xmlwrapper->numNodes($xpath_files . '/file');
        $count_previous_files = $this->xmlwrapper->numNodes($xpath_previous_files . '/file');
        #echo $old_files_exist . ' dd '; echo $count_old_files; exit();
        
        $old_file_array = array();
        $previous_file_uuid_suffix = 200;
        $next_previous_file_uuid = '';
        $old_one_file_multi_avails = false;
        $old_avails = array();
        
        /****************************************************************
         * copy all the <files> to <previous_files>
         ****************************************************************/
        if($old_files_exist)
        {
            $node_previous_files = $this->xmlwrapper->createNodeFromXPath($xpath_previous_files);
            if($previous_files_exist)
                $previous_file_uuid_suffix += $count_previous_files;
            
            $next_previous_file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $previous_file_uuid_suffix);

            #echo $next_previous_file_uuid; exit();
            
            for ($j = 1; $j <= $count_old_files; $j++) 
            {
                $tmp_xpath_file_uuid = $xpath_files . '/file['.$j.']/uuid';
                $tmp_xpath_file_ref = $xpath_files . '/file['.$j.']/@ref';
                $tmp_xpath_file_distributed = $xpath_files . '/file['.$j.']/@distributed';
                 
                $tmp_file_uuid = $this->xmlwrapper->nodeValue($tmp_xpath_file_uuid);
                $tmp_file_ref = $this->xmlwrapper->nodeValue($tmp_xpath_file_ref);
                $tmp_file_distributed = $this->xmlwrapper->nodeValue($tmp_xpath_file_distributed);
                
                $old_file_array[$j-1]['uuid'] = $tmp_file_uuid;
                $old_file_array[$j-1]['ref'] = $tmp_file_ref;
                $old_file_array[$j-1]['dist'] = $tmp_file_distributed;
                
                $old_avails[$j-1] = $tmp_file_ref;
            }
            
            if($count_old_files > 1 && 
                    $old_file_array[0]['uuid'] == $old_file_array[1]['uuid'] &&
                    $old_file_array[0]['uuid'] == $old_file_array[$count_old_files-1]['uuid'])
                $old_one_file_multi_avails = true;
            else
                $old_one_file_multi_avails = false;
            
            #Append the files to previous_files subtree, change uuid
            for ($j = 0; $j < $count_old_files; $j++) 
            {
                $tmp_node_previous_file = $this->xmlwrapper->createNode($node_previous_files, "file");
                $tmp_node_previous_file_uuid = $this->xmlwrapper->createNode($tmp_node_previous_file, "uuid");
                $tmp_node_previous_file_ref = $this->xmlwrapper->createAttribute($tmp_node_previous_file, "ref");
                $tmp_node_previous_file_distri = $this->xmlwrapper->createAttribute($tmp_node_previous_file, "distributed");
                
                $tmp_node_previous_file_ref->nodeValue = $old_file_array[$j]['ref'];
                $tmp_node_previous_file_distri->nodeValue = $old_file_array[$j]['dist'];
                
                $tmp_node_previous_file_uuid->nodeValue = $next_previous_file_uuid; 
                for($i=0; $i<count($existing_attachments); $i++)
                {
                    if($existing_attachments[$i]['uuid'] == $old_file_array[$j]['uuid'])
                        $existing_attachments[$i]['uuid'] = $next_previous_file_uuid;
                }
                if($count_old_files>1 && $old_one_file_multi_avails == false)
                {
                    $previous_file_uuid_suffix ++;
                    $next_previous_file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $previous_file_uuid_suffix);
                }
            }
        }
        
        #print_r(simplexml_import_dom($node_previous_files)->asXML());exit();
        
        
        $this->xmlwrapper->deleteNodeFromXPath($xpath_files);        
        $node_files = $this->xmlwrapper->createNodeFromXPath($xpath_files);
        #$distributed = $approve_date; #new DateTime("now");
        #$approve_time = $distributed->format('c');
        #$item_bean['attachments'] = null;
        $file_uuid = $uuid;
        $largest_suffix_number = 0;
        
        #find out the largest suffix number in uuid (last three chars)
        for ($j = 0; $j < $count_old_files; $j++) 
        {
            $file_suffix_number = intval(substr($old_file_array[$j]['uuid'], 33, 3));
            if($file_suffix_number > $largest_suffix_number)
                $largest_suffix_number = $file_suffix_number;
        }
        
        $new_one_file_multi_avails = false;
        $new_avails = array();
        $i = 0;
        foreach ($data['milestone_array']['metadata']['availability'] as $availdata  )
        {
            $new_avails[$i] = $availdata['avRef'];
            $i ++;
        }
        if($data['milestone_array']['metadata']['multiple'] != 'yes' && count($new_avails) > 1)
            $new_one_file_multi_avails = true;
        
        $attachment_count = 0;
        foreach ($data['milestone_array']['metadata']['availability'] as $availdata  )
        {
            $data['milestone_array']['avail_ref'] = $availdata['avRef'];
            $data['milestone_array']['avail_ver'] = $availdata['avVersion'];

            if($data['milestone_array']['metadata']['multiple'] == 'yes' || $attachment_count == 0) 
            {

                ob_start();
                $this->load->view('sam/showmilestone_view', $data);
                $html = ob_get_contents();
                ob_end_clean();

                $pdfclsname = 'pdf_class'.$attachment_count;
                $this->load->library('pdf/pdf_class', null, $pdfclsname); 
                #$this->pdf_class->SetDisplayMode('fullpage');
                $this->$pdfclsname->setFooter('{PAGENO} / {nb}');
                $this->$pdfclsname->WriteHTML($html);
                if($data['milestone_array']['metadata']['multiple'] == 'yes' || count($data['milestone_array']['metadata']['availability']) === 1) 
                {
                    $filename = $data['milestone_array']['avail_ref'] . '_' . $tmp_date_time . '.pdf'; 
                }
                else
                {
                    $filename = $data['milestone_array']['metadata']['availability_name'];
                    if(substr($filename, -2) == '. ')
                        $filename = substr($filename, 0, strlen($filename)-2);
                    if(substr($filename, -1) == '.')
                        $filename = substr($filename, 0, strlen($filename)-1);
                    $filename = str_replace('.', ' ', $filename);
                    $filename = str_replace('(', ' ', $filename);
                    $filename = str_replace(')', ' ', $filename);
                    $filename = str_replace('/', ' ', $filename);
                    $filename = str_replace(',', ' ', $filename);
                    $filename = str_replace(':', ' ', $filename);
                    $filename = str_replace('[', ' ', $filename);
                    $filename = str_replace(']', ' ', $filename);
                    $filename = preg_replace('/\s+/', ' ', $filename);
                    if(strlen($filename) > 70)
                        $filename = substr($filename, 0, 70);
                    if(substr($filename, -1) == ' ')
                        $filename = substr($filename, 0, strlen($filename)-1);
                    $filename = str_replace(' ', '_', $filename);
                    $filename = $filename . '_' . $tmp_date_time . '.pdf';
                }

                $pdf_content = $this->$pdfclsname->Output($filename, 'S');

                $success = $this->flexrest->fileUpload($filearea_uuid, $filename, $pdf_content, $response2);
                if(!$success)
                {
                    $errdata['message'] = $this->flexrest->error;
                    log_message('error', 'Attaching SAM pdf failed (fileUpload), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
                    $this->load->view('sam/showerror_view', $errdata);
                    return;
                }
                echo '<br><br>Finish fileUpload!';

                /***************************************************************
                 * Determine the file uuid, 
                 *    - reuse exiting uuid if topic availability is the same
                 *    - otherwise generate new file uuid
                 * 
                 ***************************************************************/
                
                if($count_old_files > 0)
                {
                    $file_uuid = null;
                    #$largest_suffix_number = 0;
                }
                else
                {
                    #No old attachments, generagte new file uuid
                    $file_uuid = sprintf("%03d", $attachment_count);
                    $file_uuid = substr($uuid, 0, 33) . $file_uuid;
                }
                
                $reuse_old_file_uuid = true;

                /***************************************************************
                 * Old avails:
                 *   A_B_C   1 file uuid-1
                 * New avails:
                 *   A_B_C_D 1 file uuid-1 (reuse exiting uuid)
                 * 
                 * Old avails:
                 *   A_B_C   1 file uuid-1
                 * New avails:
                 *   B_C     1 file uuid-2 (generate new uuid)  
                 ***************************************************************/
                if($old_one_file_multi_avails == true && $new_one_file_multi_avails == true)
                {
                    if(count(array_diff($old_avails, $new_avails)) > 0)
                    {
                        #There is new availability added in, generate new file uuid
                        $reuse_old_file_uuid = false;
                    }
                    else
                    {
                        $reuse_old_file_uuid = true;
                        $file_uuid = $old_file_array[0]['uuid'];
                    }
                }
                
                /*
                echo '<pre>';
                echo '$old_one_file_multi_avails ' . $old_one_file_multi_avails;echo '<br>';
                echo '$new_one_file_multi_avails ' . $new_one_file_multi_avails;echo '<br>';
                echo '$file_uuid ' . $file_uuid;echo '<br>';
                print_r($old_avails);echo '<br>';
                print_r($new_avails);echo '<br>';
                echo '</pre>';exit();
                */
                
                /***************************************************************
                 * Old avails:
                 *   A_B 1 file uuid-1
                 * New avails:
                 *   A   1 file uuid-2
                 *   B   1 file uuid-3
                 ***************************************************************/
                if($old_one_file_multi_avails == true && $new_one_file_multi_avails == false)
                {
                    #generate new file uuid
                    $reuse_old_file_uuid = false;
                }
                
                /***************************************************************
                 * Old avails:
                 *   A   1 file uuid-1
                 *   B   1 file uuid-2
                 * New avails:
                 *   B_C 1 file uuid-2 (reuse uuid of common avail, generate new one if no common avail )
                 ***************************************************************/
                if($old_one_file_multi_avails == false && $new_one_file_multi_avails == true)
                {
                    #
                    $reuse_old_file_uuid = true;
                    for ($j = 0; $j < $count_old_files; $j++) 
                    {
                        for($i = 0; $i < count($new_avails); $i++)
                        {
                            if($new_avails[$i] == $old_file_array[$j]['ref'])
                            $file_uuid = $old_file_array[$j]['uuid'];
                            break;
                        }
                        if($file_uuid != null)
                            break;
                    }
                }
                
                /***************************************************************
                 * Old avails:
                 *   A   1 file uuid-1
                 *   B   1 file uuid-2
                 * New avails:
                 *   A   1 file uuid-1
                 *   C   1 file uuid-3
                 * 
                 * Example:
                 * <previous_files>
                 *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4700A_2014_S2">
                 *       <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc200</uuid>
                 *    </file>
                 *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4700B_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc201</uuid>
                 *    </file>
                 *    <file distributed="2014-07-22T13:59:44+0930" ref="COMP4700C_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc202</uuid>
                 *    </file>
                 *  </previous_files>
                 *  <files>
                 *    <file distributed="2014-07-23T13:59:44+0930" ref="COMP4700A_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc000</uuid>
                 *    </file>
                 *    <file distributed="2014-07-23T13:59:44+0930" ref="COMP4700D_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc003</uuid>
                 *    </file>
                 *    <file distributed="2014-07-23T13:59:44+0930" ref="COMP4700C_2014_S2">
                 *      <uuid>dd96fbba-b720-4b00-9180-bfeeab3dc002</uuid>
                 *    </file>
                 *  </files>
                 ***************************************************************/

                #Reuse exiting file uuid
                if($file_uuid === null && $reuse_old_file_uuid == true)
                {
                    for ($j = 0; $j < $count_old_files; $j++) 
                    {
                        #For the same avilability, the uuid must remain the same
                        if($data['milestone_array']['avail_ref'] == $old_file_array[$j]['ref'])
                        {
                            $file_uuid = $old_file_array[$j]['uuid'];
                            #echo '111 ' . $file_uuid; exit();
                            #if(intval(substr($file_uuid, 32, 3)) > $largest_suffix_number)
                            #    $largest_suffix_number = intval(substr($file_uuid, 32, 3));
                            break;
                        }
                    }
                }

                #the availability is new so it is not found in the exiting files, so assign new uuid to it
                if($file_uuid === null)
                {
                    $largest_suffix_number ++;
                    $file_uuid = substr($uuid, 0, 33) . sprintf("%03d", $largest_suffix_number);
                }

                $new_attachments[$attachment_count] = array('type'=>'file', 
                                              'filename'=>$filename, 
                                              'description'=>$filename,
                                              'uuid'=>$file_uuid);
                #$item_bean['attachments'][$attachment_count] = array('type'=>'file', 
                #                              'filename'=>$filename, 
                #                              'description'=>$filename,
                #                              'uuid'=>$file_uuid);
            }
            $node_file = $this->xmlwrapper->createNode($node_files, "file");
            $node_uuid = $this->xmlwrapper->createNode($node_file, "uuid");
            $node_ref = $this->xmlwrapper->createAttribute($node_file, "ref");
            $node_distri = $this->xmlwrapper->createAttribute($node_file, "distributed");
            $node_uuid->nodeValue = $file_uuid; 
            $node_ref->nodeValue = $data['milestone_array']['avail_ref'];
            #$node_distri->nodeValue = $distributed->format('c');
            $node_distri->nodeValue = $tmp_distributed; ####$approve_date;

            $attachment_count ++;
        }
       
        
        $item_bean['attachments'] = array_merge($new_attachments, $existing_attachments);
        $item_bean['metadata'] = $this->xmlwrapper->__toString();
        
        /*
        echo '<pre>';
        #print_r($new_attachments);echo '<br>';
        #print_r($existing_attachments);echo '<br>';
        echo $item_bean['metadata']; echo '<br>';
        #print_r(json_decode(json_encode($item_bean['metadata']), true));echo '<br>';
        print_r($item_bean['attachments']);
        log_message('error', $item_bean['metadata']);
        echo '</pre>';
        exit();
        */
        
        $success = $this->flexrest->editItem($uuid, $version, $item_bean, $response3, $filearea_uuid);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            log_message('error', 'Attaching SAM pdf failed (editItem), item uuid: ' . $uuid . ', error: ' . $errdata['message']);
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        echo '<br><br>Finish Edit Item!';
        echo '<br><br>Completed!';
        #log_message('error', htmlentities($response['metadata']));
        /*
        echo "<br>=====================================";
        echo "<pre>";
        print_r($response3);
	echo "</pre>";
	 */
    }   
    
    #testing
    public function test16yt7i()
    {
         $this->load->helper('url');
        $this->load->library('flexrest/flexrest');
        
        $success = $this->flexrest->processClientCredentialToken();
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }    

	$uuid = 'c58d96a2-2541-4679-8dca-a264d2b4fbd6';
        $success = $this->flexrest->getItem($uuid, $version=1, $response);
        if(!$success)
        {
            $errdata['message'] = $this->flexrest->error;
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
        
        echo "<pre>";
        print_r($response);
        echo "</pre>";
        #log_message('error', htmlentities($response['metadata']));
        
        $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));
       
        $milestone_array = $this->samXml2Array($this->xmlwrapper);
        echo "<pre>";
        print_r($milestone_array);
        echo "</pre>";
        exit();

	unset($response['headers']);
        $item_bean = $response;
        #unset($item_bean["uuid"] );
	#unset($item_bean["version"] );
	#unset($item_bean["drm"] );
        $item_bean["attachments"] = null;

        $success = $this->flexrest->editItem($uuid, 1, $item_bean, $response1);	
        #$success = $this->flexrest->createItem($item_bean, $response1, null, 'true');
        if(!$success)
        {
            #$errdata['message'] = $this->flexrest->error;
            #log_message('error', ' createItem failed' . ', error: ' . $errdata['message']);
            #log_message('error', 'Metadata: ' . $item_bean['metadata']);
            #$this->load->view('reading_listmgr/showerror_view', $errdata);
            $result['error_info'] = $this->flexrest->error;
            echo "<pre>";
	    print_r($result);
	    print_r($response1);
            echo "</pre>";
            return;
        }
	exit();


        echo "<pre>";
        print_r($response);
        echo "</pre>";
        log_message('error', htmlentities($response['metadata']));
        
        $this->load->library('xmlwrapper/xmlwrapper', array('xmlString' => (string)$response['metadata']));
        
        if(!$this->itemIsSam($this->xmlwrapper))
        {
            $errdata['message'] = "Item is not SAM";
            $this->load->view('sam/showerror_view', $errdata);
            return;
        }
       
        $milestone_array = $this->samXml2Array($this->xmlwrapper);
        echo "<pre>";
        print_r($milestone_array);
        echo "</pre>";
    }
}

/* End of file */
