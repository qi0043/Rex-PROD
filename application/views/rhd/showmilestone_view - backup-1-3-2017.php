
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]--> 
      
    
     <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/tmp_flin_base_v2.css" media="all">
     
    <link href="<?php echo base_url() . 'resource/rhd/';?>css/tmp_flin_print.css" rel="stylesheet" media="print"/>
    <link rel="stylesheet" href="<?php echo base_url() . 'resource/rhd/';?>css/tmp_flin_user_styles.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/flex-reports/jquery-ui-1.10.2.custom.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/flindersbase_local.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/flindersbase_local_print.css" media="print">
    
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>js/jslib.js"></script>
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>js/jquery-1.9.1.js"></script>
    
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>js/jquery-ui-1.10.2.custom.js"></script>
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>js/jquery-ui-1.10.2.custom.min.js"></script>
    
    <style type="text/css">
    .hiddenForm {
        display: none;
        margin: 0px;
        padding: 0px;
    }
    .signature_img{
        max-height:200px;
        max-width:250px;
        }
        
    .table_coordinators tr, .table_coordinators td, .table_coordinators{
        margin-bottom: 8px;
        margin-top: 0px;
        padding: 0;
        border: 0;
        outline: 0;
        border-spacing: 0;
        text-align: left;
    }
    </style>
    
    <script>
    $(function() {
        $( "input[type=submit], input[type=button], a.button" ).button();
    });
		
    </script>
    <script>
    
    function submitMe(theID) {
        
        //alert(theID);
        theForm = 'tsearch'+theID
        
        //alert(theForm);
        
        document.getElementById(theForm).submit();
        
    }
    
    </script>
    
    <title></title>
</head>
<body id="bodytag">

    <!--Wrapper around entire page-->
    <div id="page_wrapper">

	<!--Banner-->
    <?php if($milestone_array['format']=='html'){?>
      <header class="main_header" role="banner">

        <h1>Flinders University MILESTONE</h1>
        <div id="main_banner">
        	<img class="tagline" src="http://www.flinders.edu.au/flinders/app_templates/flinderstemplates/images/banners/inspiring_achievement.png" alt="tagline image" />
        	<a href="http://www.flinders.edu.au"><img src="http://www.flinders.edu.au/flinders/app_templates/flinderstemplates/images/flinders_logo.png" alt="Flinders logo"></a>
			
	  		
        </div>
</header><!--End Banner-->
	<?php } ?>
    
<!--Content wrapper (between header and footer) -->
<div id="page_content" class="row" style="background-color:#FFF; ">  
 <!--  page_content row wrapper -->		      
 <div class="column grid_15" style="margin-bottom: 60px;">

<!-- main content area -->
        
<div class="row">
<div class="column grid_15">  
<article role="main">
<div id="container_num_1" class="container container_no_box">
<div class="noprint">
<h1 style="text-align:center"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RHD Milestone

<span class="importantText">
   <?php  $status = $milestone_array['metadata']['status'];
		  if(isset($status)){?>
			<?php echo ($status=='live')? '' : '&nbsp;&nbsp;&nbsp;&nbsp;DRAFT'; 
		 }?>
         
</span>
</h1>
</div>
  
<!-- Content of Milestone -->
 <?php if($milestone_array['format']=='pdf'){?>
	<h2 style="text-align:center">Flinders University</h2>
    <h2 style="text-align:center">MILESTONE OF RHD
 
           <?php
		    $status = $milestone_array['metadata']['status'];
		  	$approval = $milestone_array['metadata']['approved'];
		   if(isset($status) && isset($approval)){
	 ?>
    <span class="importantText" style="color:#FF0000"> <?php echo ($status=='live') ? '' : 	'DRAFT	'; ?>
    </span>
    <?php } 
	else {?>
   	    <span class="importantText" style="color:#FF0000">
    	<?php echo 'DRAFT';}?>
    	</span>
    
<?php } else{ ?>
<h2 style="text-align:center">MILESTONE OF RHD </h2>
<?php } ?>
</h2>
    <div id="parta" name="candidatedetails" title="A.CANDIDATE DETAILS">
    <h2>A. CANDIDATE'S DETAILS</h2>
    <table class="table_candidatedetails">
        <tr>
            <td>
                Candiate Name:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['candidatename']?>
            </td>
        </tr>
        <tr>
            <td>
                Student ID:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['studentid']?>
            </td>
        </tr>
        <tr>
            <td>
                Student FAN:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['studenfan']?>
            </td>
        </tr>
        <tr>
            <td>
                Degree:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['degree']?>
            </td>
        </tr>
        <tr>
            <td>
                Topic Code:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['topiccode']?>
            </td>
        </tr>
        <tr>
            <td>
                Thesis Title:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['thesistitle']?>
            </td>
        </tr>
        <tr>
            <td>
                School (Dept or Unit):
            </td>
            <td>
                <?php echo $milestone_array['metadata']['schoolname']?>
            </td>
        </tr>
        <tr>
            <td>
                FT/PT Candidature:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['fullorparttime']?>
            </td>
        </tr>
        <tr>
            <td>
                Candidature Commencement Date:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['commercedate']?>
            </td>
        </tr>
        <tr>
            <td>
                Expected Submission Date:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['submissiondate']?>
            </td>
        </tr>
        <tr>
            <td>
                Supervisor(s):
            </td>
            <td>
                <?php
                $supervisorsList = $milestone_array['metadata']['supervisorsdetail'];
                foreach ($supervisorsList as $supervisor){
                    echo "<li>". $supervisor['role'] . ": " . $supervisor['title'] . ". " . $supervisor['name'] . "</li>";
                }
                ?>
            </td>
        </tr>

        <?php
        if(array_key_exists("uuid",$milestone_array['metadata']['overallreport'])) {
            echo "<tr><td>Overall report - to support milestone determination.</td></tr>";
            for ($j = 1; $j <= count($milestone_array['metadata']['overallreport']['uuid']); $j++) {
                echo "<tr><td><a href='".$milestone_array['metadata']['overallreport']['link'][$j]."'>" . $milestone_array['metadata']['overallreport']['name'][$j] . "</a></td></tr>";
            }
        }
        ?>

    </table>
    </div>

    <div id="partb" name="assessmentcriteria" title="B. ASSESSMENT CRITERIA">
        <h2>B. ASSESSMENT CRITERIA<br></h2>
        <h3>Training<br></h3>
        <h4>Student question:</h4>
        <table class="table_assesscriteria">
            <tr>
                <td>
                    Do you require additional education and/or training to ensure timely completion?
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['additionaleducation']['requested'] ?></b>
                </td>
            </tr>

            <?php
                if (strnatcasecmp($milestone_array['metadata']['additionaleducation']['requested'], 'Yes') == 0){
                    echo "<tr><td>Detail what you need and why:</td>></tr>
                          <tr><td>".$milestone_array['metadata']['additionalresource']['inputs']['text']."</td></tr>";
                }

                if(array_key_exists("uuid",$milestone_array['metadata']['additionaleducation']['inputs'])) {
                    echo "<tr><td>Supporting documentation:</td>></tr>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['additionaleducation']['inputs']['uuid']); $j++) {
                        echo "<tr><td><a href='".$milestone_array['metadata']['additionaleducation']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['additionaleducation']['inputs']['name'][$j] . "</a></td></tr>";
                    }
                }
            ?>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['additionaleducation']['comment'] ?>
                </td>
            </tr>
        </table>

        <h3>Resource<br></h3>
        <h4>Student question:</h4>
        <table>
            <tr>
                <td>
                    Do you require additional resources to complete the project, other than those noted at admission?
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['additionalresource']['requested'] ?></b>
                </td>
            </tr>

            <?php
            if (strnatcasecmp($milestone_array['metadata']['additionalresource']['requested'], 'Yes') == 0){
                echo "<tr><td>Detail what you need and why:</td>></tr>
                          <tr><td>".$milestone_array['metadata']['additionalresource']['inputs']['text']."<td></tr>";
            }
            if(array_key_exists("uuid",$milestone_array['metadata']['additionalresource']['inputs'])) {
                echo "<tr><td>Supporting documentation:</td>></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['additionalresource']['inputs']['uuid']); $j++) {
                    echo "<tr><td><a href='".$milestone_array['metadata']['additionalresource']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['additionalresource']['inputs']['name'][$j] . "</a></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['additionalresource']['comment'] ?>
                </td>
            </tr>
        </table>

        <h3>Research Question<br></h3>
        <h4>Supervisor question:</h4>
        <table>
            <tr>
                <td>
                    Has the candidate articulated a suitable research problem or question(s)?
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['researchproblem']['accepted'] ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    Student input can be recorded as text and/or as a document file.
                </td>
            </tr>
            <tr>
                <td>
                    Student input as text:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['researchproblem']['inputs']['text'] ?>
                </td>
            </tr>


            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['researchproblem']['inputs'])) {
                echo "<tr><td>The input as a file. Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['researchproblem']['inputs']['uuid']); $j++) {
                    echo "<tr><td><a href='".$milestone_array['metadata']['researchproblem']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['researchproblem']['inputs']['name'][$j] . "</a></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['researchproblem']['comment'] ?>
                </td>>
            </tr>
        </table>

        <h3>Plan<br></h3>
        <h4>Supervisor question:<br></h4>
        <p>
            1. Has the candidate provided a detailed and achievable plan? <br>
            That satisfies the requirements for their proposed research project, that includes a Research Proposal and Literature Review as outlined in the RHD Policies and Procedures Section 17.5?<br>
            2. Is the timeframe realistic/feasible?<br>

            The input can be recorded as text and/or as a document file.
        </p>
        <table>
            <tr>
                <td>
                    Proposal: student input as text
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['planproposal']['inputs']['text'] ?>
                </td>>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['planproposal']['inputs'])) {
                echo "<tr><td>Proposal: the input as a file<br> Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['planproposal']['inputs']['uuid']); $j++) {
                    echo "<tr><td><a href='".$milestone_array['metadata']['planproposal']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['planproposal']['inputs']['name'][$j] . "</a></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    Lit review: student input as text
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['litreview']['inputs']['text'] ?>
                </td>>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['litreview']['inputs'])) {
                echo "<tr><td>Lit review: student input as a file<br>Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['litreview']['inputs']['uuid']); $j++) {
                    echo "<tr><td><a href='".$milestone_array['metadata']['litreview']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['litreview']['inputs']['name'][$j] . "</a></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    Timeframe: student input as text
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['plantimeframe']['inputs'] ?>
                </td>>
            </tr>

            <tr>
                <td>
                    Has the candidate provided a detailed and achievable plan?
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['planproposal']['achievable'] ?></b>
                </td>>
            </tr>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['planproposal']['comment'] ?>
                </td>>
            </tr>

            <tr>
                <td>
                    Is the timeframe realistic/feasible?
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['planproposal']['realistic'] ?></b>
                </td>>
            </tr>
            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['plantimeframe']['comment'] ?>
                </td>>
            </tr>
        </table>

        <h3>Methodology<br></h3>
        <h4>Supervisor question:<br></h4>

        <table>
            <tr>
                <td>
                    Appropriate to the discipline, has the candidate identified a research methodology/method and type of data collection, or equivalent, for the project?
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['methodology']['accepted'] ?></b>
                </td>>
            </tr>
            <tr>
                <td>
                    <p>Student input can be recorded as text and/or as a document file.<br></p>
                    <p>Student input as text:</p>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['methodology']['inputs']['text'] ?>
                </td>>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['methodology']['inputs'])) {
                echo "<tr><td>The input as a file<br>Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['methodology']['inputs']['uuid']); $j++) {
                    echo "<tr><td><a href='".$milestone_array['metadata']['methodology']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['methodology']['inputs']['name'][$j] . "</a></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['methodology']['comment'] ?>
                </td>>
            </tr>
        </table>

        <h3>Ethics<br></h3>
        <h4>Supervisor question:<br></h4>
        <h4>Has the appropriate ethics approval been sought?<br></h4>
        <p>The input can be recorded as text and/or as a document file.</p>
        <table>
            <tr>
                <td>
                    Student input as text:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['ethics']['inputs']['text'] ?>
                </td>>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['ethics']['inputs'])) {
                echo "<tr><td>Student input as a file: <br>Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['ethics']['inputs']['uuid']); $j++) {
                    echo "<tr><td><a href='".$milestone_array['metadata']['ethics']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['ethics']['inputs']['name'][$j] . "</a></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    <p>Supervisor assessment<br></p>
                    <p>Refer to Ethics, Biosafety and Integrity for more information.</p>
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['ethics']['accepted'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['ethics']['comment'] ?>
                </td>>
            </tr>
        </table>

        <h3>Oral Communication<br></h3>
        <h4>Supervisor question:<br></h4>
        <h4>a) Has the candidate displayed good oral communication skills? <br></h4>
        <h4>b) What material have they presented material that demonstrated depth of knowledge about their project, used audio visual materials effectively and answered questions appropriately?<br></h4>
        <p>The input can be recorded as text and/or as a document file.</p>
        <table>
            <tr>
                <td>
                    Student input as text:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['oralskill']['inputs']['text'] ?>
                </td>>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['oralskill']['inputs'])) {
                echo "<tr><td>Student input as a file: <br>Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['oralskill']['inputs']['uuid']); $j++) {
                    echo "<tr><td><a href='".$milestone_array['metadata']['oralskill']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['oralskill']['inputs']['name'][$j] . "</a></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    Supervisor assessment:
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['oralskill']['accepted'] ?></b>
                </td>>
            </tr>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['oralskill']['comment'] ?>
                </td>>
            </tr>

        </table>

        <h3>Written Communication<br></h3>
        <h4>Supervisor question:<br></h4>
        <h4>a) Can the candidate write in an appropriate format and express themselves clearly?<br> </h4>
        <h4>b) What written submissions demonstrate this?<br></h4>
        <p>The input can be recorded as text and/or as a document file.</p>
        <table>
            <tr>
                <td>
                    Student input on text matching of the written work:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['writtenskill']['inputs']['text'] ?>
                </td>>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['writtenskill']['inputs'])) {
                echo "<tr><td>Student input as a file: <br>Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['writtenskill']['inputs']['uuid']); $j++) {
                    echo "<tr><td><a href='".$milestone_array['metadata']['writtenskill']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['writtenskill']['inputs']['name'][$j] . "</a></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    Supervisor assessment:
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['writtenskill']['accepted'] ?></b>
                </td>>
            </tr>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['writtenskill']['comment'] ?>
                </td>>
            </tr>

        </table>

        <h3>Text-matching<br></h3>
        <h4>Supervisor question:<br></h4>
        <h4>Has the candidate’s written submission for this Milestone been subjected to text-matching?<br> </h4>
        <p>If you would like to record your input to this section, please enter it as a text-based comment. Any text-matching reports should be added on the first page and on other pages where appropriate.</p>
        <table>
            <tr>
                <td>
                    Student input:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['textmatching']['inputs']['text'] ?>
                </td>>
            </tr>

            <tr>
                <td>
                    Has the candidate’s written submission for this Milestone been subjected to text-matching?
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['textmatching']['subjected'] ?></b>
                </td>>
            </tr>

            <tr>
                <td>
                    Does the candidate’s written submission comply with the University’s Academic Integrity Policy?
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['textmatching']['integrity'] ?></b>
                </td>>
            </tr>

            <tr>
                <td>
                    Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['textmatching']['comment'] ?>
                </td>>
            </tr>
        </table>

        <h3>Goals for next Milestone<br></h3>
        <h4>Supervisor question:<br></h4>
        <h4>Recommended goals for next milestone. <br></h4>
        <p>These can include conference abstracts/posters, articles published or submitted, papers in preparation, thesis chapters completed, patents, etc. <br></p>
        <h4>Clearly outline the specified goals with realistic timeframes.</h4>
        <table>
            <?php
            $numOfGoal = 1;
            foreach($milestone_array['metadata']['studentgoal'] as $goal){
                echo "<tr><td>The information of goal ".$numOfGoal."</td></tr>";
                echo "<tr><td>Time Frame:</td>";
                echo "<td>".$goal['timeframe']."</td></tr>";
                echo "<tr><td>Goal:</td>";
                echo "<td>".$goal['goalcontent']."</td></tr>";
                echo "<tr><td>Comment:</td>";
                echo "<td>".$goal['comment']."</td></tr>";
                $numOfGoal++;
            }
            ?>
        </table>

        <h3>Supervisor</h3>
        <table>
            <tr>
                <td>
                    Recommendation:
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['supervisor']['recommendation'] ?></b>
                </td>>
            </tr>

            <?php
            if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Satisfactory') == 0){
                echo "<tr><td>Supervisor comment: </td>></tr>
                          <tr><td>".$milestone_array['metadata']['supervisor']['recommendationcomment']."<td></tr>";
            }
            if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Resubmit') == 0){
                echo "<tr><td>Supervisor judgement: Candidate to resubmit the milestone following completion of further work. </td>></tr>
                          <tr><td>Specify additional work required, including a timeframe for the work to be resubmitted.</td></tr>
                          <tr><td>".$milestone_array['metadata']['supervisor']['resubmit']."<td></tr>";
            }
            if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Show cause') == 0){
                echo "<tr><td>Supervisor judgement: Candidate required to show cause </td>></tr>
                          <tr><td>Indicate why the candidate should not be permitted to proceed with the candidature</td></tr>
                          <tr><td>".$milestone_array['metadata']['supervisor']['showcause']."<td></tr>";
            }

            ?>

        </table>

    </div>
    <div id="partc" name="moderation" title="C. MODERATION">
        <h2>C. MODERATION</h2>
        <h3>MOD: Postgrad Coordinator</h3>
        <table>
            <tr>
                <td>
                    Supported by Postgraduate Coordinator:
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['mod']['coordinatorsupport'] ?></b>
                </td>>
            </tr>

            <tr>
                <td>
                    <p>Postgrad Coordinator comment:</p>
                    <p>This makes more sense as a workflow step than in section D.</p>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['mod']['coordinatorcomment'] ?>
                </td>>
            </tr>
        </table>

        <h3>MOD: Candidate</h3>
        <table>
            <tr>
                <td>
                    Candidate signoff: I have noted and support/do not support this assessment
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['mod']['studentaccepted'] ?></b>
                </td>>
            </tr>

            <tr>
                <td>
                    Candidate comment
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['mod']['studentcomment'] ?>
                </td>>
            </tr>
        </table>

        <h3>MOD: Faculty</h3>
        <table>
            <tr>
                <td>
                    Faculty determination
                </td>
                <td>
                    <b><?php echo $milestone_array['metadata']['mod']['facultydetermination'] ?></b>
                </td>>
            </tr>

            <tr>
                <td>
                    Faculty comment
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $milestone_array['metadata']['mod']['facultycomment'] ?>
                </td>>
            </tr>
        </table>
    </div>
</body>
</html>