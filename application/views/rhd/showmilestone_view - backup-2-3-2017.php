
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      
    
     <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/tmp_flin_base_v2.css" media="all">
     
    <link href="<?php echo base_url() . 'resource/rhd/';?>css/tmp_flin_print.css" rel="stylesheet" media="print"/>
    <link rel="stylesheet" href="<?php echo base_url() . 'resource/rhd/';?>css/tmp_flin_user_styles.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/flex-reports/jquery-ui-1.10.2.custom.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/flindersbase_local.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/flindersbase_local_print.css" media="print">

    
    <style type="text/css">

        
    .table_candidatedetails tr, .table_candidatedetails td, .table_candidatedetails, .table{
        margin-bottom: 8px;
        margin-top: 0px;
        padding: 10px;
        border: 0;
        outline: 0;
        border-spacing: 0;
        text-align: left;
    }
        table{padding:10px;}
    </style>



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
<h1 style="text-align:center"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $milestone_array['metadata']['name']?>

<span class="importantText">
   <?php  $status = $milestone_array['metadata']['status'];
		  if(isset($status)){?>
			<?php echo ($status=='live')? '' : '&nbsp;&nbsp;&nbsp;&nbsp;'.$status;
		 }?>
         
</span>
</h1>
</div>
  
<!-- Content of Milestone -->
 <?php if($milestone_array['format']=='pdf'){?>
	<h2 style="text-align:center">Flinders University</h2>
    <h2 style="text-align:center"><?php echo $milestone_array['metadata']['name']?>
 
           <?php
		    $status = $milestone_array['metadata']['status'];
		  	$approval = $milestone_array['metadata']['approved'];
		   if(isset($status) && isset($approval)){
	 ?>
    <span class="importantText" style="color:#FF0000"> <?php echo 'Draft' ?>
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
    <div id="parta" name="candidatedetails" title="A.CANDIDATE DETAILS" style="border-style: solid; border-width: 1px">
    <h2 style="color:white; background-color:black;">&nbsp;A. &nbsp;&nbsp;&nbsp;&nbsp;CANDIDATE'S DETAILS</h2>

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
                RTS End Date:
            </td>
            <td>
                <?php echo $milestone_array['metadata']['rtsenddate']?>
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
                echo "<tr><td><li><a href='".$milestone_array['metadata']['overallreport']['link'][$j]."'>" . $milestone_array['metadata']['overallreport']['name'][$j] . "</a></li></tr></td>";
            }
        }
        ?>

    </table>
        <br/><br/>
    </div>

    <div id="partb" name="assessmentcriteria" title="B.ASSESSMENT CRITERIA" style="border-style: solid;border-width: 1px">
        <h2 style="color:white; background-color:black;">&nbsp;B. &nbsp;&nbsp;&nbsp;&nbsp;ASSESSMENT CRITERIA<br/></h2>
        <div style="padding:10px">
        <h3>1. Training<br></h3>
        <p>Student question:</p>
        <table class="table_assesscriteria">
            <tr>
                <td>
                    Does student require additional education and/or training to ensure timely completion?
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['additionaleducation']['requested'] ?></b>
                </td>
            </tr>

            <?php
                if (strnatcasecmp($milestone_array['metadata']['additionaleducation']['requested'], 'Yes') == 0){
                    echo "<tr><td><br>Detail what you need and why:</td></tr>
                          <tr><td><ul>".$milestone_array['metadata']['additionalresource']['inputs']['text']."</ul></tr></td>";
                }

                if(array_key_exists("uuid",$milestone_array['metadata']['additionaleducation']['inputs'])) {
                    echo "<tr><td>Supporting documentation:</td></tr>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['additionaleducation']['inputs']['uuid']); $j++) {
                        echo "<tr><td><li><a href='".$milestone_array['metadata']['additionaleducation']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['additionaleducation']['inputs']['name'][$j] . "</a></li></tr></td>";
                    }
                }
            ?>

            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['additionaleducation']['comment'] ?></ul>
                </td>
            </tr>
        </table>

        <br>
        <h3>2. Resource<br></h3>
        <p>Student question:</p>
        <table>
            <tr>
                <td>
                    Does student require additional resources to complete the project, other than those noted at admission?
                    <b> <?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['additionalresource']['requested'] ?></b><br>
                </td>
            </tr>

            <?php
            if (strnatcasecmp($milestone_array['metadata']['additionalresource']['requested'], 'Yes') == 0){
                echo "<tr><td><br>Detail what you need and why:</td></tr>
                          <tr><td><ul>".$milestone_array['metadata']['additionalresource']['inputs']['text']."</ul></tr></td>";
            }
            if(array_key_exists("uuid",$milestone_array['metadata']['additionalresource']['inputs'])) {
                echo "<tr><td>Supporting documentation:</td>";
                for ($j = 1; $j <= count($milestone_array['metadata']['additionalresource']['inputs']['uuid']); $j++) {
                    echo "<tr><td><li><a href='".$milestone_array['metadata']['additionalresource']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['additionalresource']['inputs']['name'][$j] . "</a></li></tr></td>";
                }
                echo "</tr>";
            }
            ?>

            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['additionalresource']['comment'] ?></ul>
                </td>
            </tr>
        </table>

        <br/>
        <h3>3. Research Question<br></h3>
        <p>Supervisor question:</p>
        <table>
            <tr>
                <td>
                    Has the candidate articulated a suitable research problem or question(s)?
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['researchproblem']['accepted'] ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <br><I>Student input can be recorded as text and/or as a document file </I><br>
                    Student input as text:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['researchproblem']['inputs']['text'] ?></ul>
                </td>
            </tr>


            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['researchproblem']['inputs'])) {
                echo "<tr><td><br>Student input as a file. Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['researchproblem']['inputs']['uuid']); $j++) {
                    echo "<tr><td><li><a href='".$milestone_array['metadata']['researchproblem']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['researchproblem']['inputs']['name'][$j] . "</a></li></tr></td>";
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
                    <ul><?php echo $milestone_array['metadata']['researchproblem']['comment'] ?></ul>
                </td>
            </tr>
        </table>
        <br>

        <h3>4. Plan</h3>
        <p>Supervisor question:<br></p>
        <p>
            1. Has the candidate provided a detailed and achievable plan? <br>
            That satisfies the requirements for their proposed research project, that includes a Research Proposal and Literature Review as outlined in the RHD Policies and Procedures Section 17.5?<br>
            2. Is the timeframe realistic/feasible?<br>

            The input can be recorded as text and/or as a document file.<br>
        </p>
        <table>
            <tr>
                <td>
                    Proposal: student input as text
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['planproposal']['inputs']['text'] ?></ul>
                </td>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['planproposal']['inputs'])) {
                echo "<tr><td>Proposal: the input as a file<br> <I>Add text-matching report if appropriate.</I></td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['planproposal']['inputs']['uuid']); $j++) {
                    echo "<tr><td><li><a href='".$milestone_array['metadata']['planproposal']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['planproposal']['inputs']['name'][$j] . "</a></li></tr></td>";
                }
            }
            ?>

            <tr>
                <td>
                    <br>Lit review: student input as text
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['litreview']['inputs']['text'] ?></ul>
                </td>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['litreview']['inputs'])) {
                echo "<tr><td>Lit review: student input as a file<br><I>Add text-matching report if appropriate.</I></td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['litreview']['inputs']['uuid']); $j++) {
                    echo "<tr><td><li><a href='".$milestone_array['metadata']['litreview']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['litreview']['inputs']['name'][$j] . "</a></li></tr></td>";
                }
            }
            ?>

            <tr>
                <td>
                    <br>Timeframe: student input as text
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['plantimeframe']['inputs'] ?></ul>
                </td>
            </tr>

            <tr>
                <td>
                    Has the candidate provided a detailed and achievable plan?
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['planproposal']['achievable'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['planproposal']['comment'] ?></ul>
                </td>
            </tr>

            <tr>
                <td>
                    Is the timeframe realistic/feasible?
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['planproposal']['realistic'] ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['plantimeframe']['comment'] ?></ul>
                </td>
            </tr>
        </table>
        <br>

        <h3>5. Methodology<br></h3>
        <p>Supervisor question:<br></p>

        <table>
            <tr>
                <td>
                    Appropriate to the discipline, has the candidate identified a research methodology/method and type of data collection, or equivalent, for the project?
                 <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['methodology']['accepted'] ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <br><p><I>Student input can be recorded as text and/or as a document file.</I><br></p>
                    <p>Student input as text:</p>
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['methodology']['inputs']['text'] ?></ul>
                </td>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['methodology']['inputs'])) {
                echo "<tr><td>The input as a file<br><I>Add text-matching report if appropriate.</I></td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['methodology']['inputs']['uuid']); $j++) {
                    echo "<tr><td><li><a href='".$milestone_array['metadata']['methodology']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['methodology']['inputs']['name'][$j] . "</a></li></td></tr>";
                }
            }
            ?>

            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['methodology']['comment'] ?></ul>
                </td>
            </tr>
        </table>

        <h3>6. Ethics<br></h3>
        <p>Supervisor question: Has the appropriate ethics approval been sought?<br></p>

        <p><I>The input can be recorded as text and/or as a document file.</I></p>
        <table>
            <tr>
                <td>
                    Student input as text:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['ethics']['inputs']['text'] ?></ul>
                </td>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['ethics']['inputs'])) {
                echo "<tr><td>Student input as a file: <br><I>Add text-matching report if appropriate.</I></td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['ethics']['inputs']['uuid']); $j++) {
                    echo "<tr><td><li><a href='".$milestone_array['metadata']['ethics']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['ethics']['inputs']['name'][$j] . "</a></li></tr></td>";
                }
            }
            ?>

            <tr>
                <td>
                    <p><br>Supervisor assessment-&nbsp;<I>Refer to Ethics, Biosafety and Integrity for more information.</I>
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['ethics']['accepted'] ?></b></p>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['ethics']['comment'] ?></ul>
                </td>
            </tr>
        </table>
        <br>
        <h3>7. Oral Communication<br></h3>
        <p>Supervisor question:<br></p>
        <p>a) Has the candidate displayed good oral communication skills? <br></p>
        <p>b) What material have they presented material that demonstrated depth of knowledge about their project, used audio visual materials effectively and answered questions appropriately?<br></p>
        <p><I>The input can be recorded as text and/or as a document file.</I></p>
        <table>
            <tr>
                <td>
                    Student input as text:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['oralskill']['inputs']['text'] ?></ul>
                </td>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['oralskill']['inputs'])) {
                echo "<tr><td>Student input as a file: <br><I>Add text-matching report if appropriate.</I></td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['oralskill']['inputs']['uuid']); $j++) {
                    echo "<tr><td><li><a href='".$milestone_array['metadata']['oralskill']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['oralskill']['inputs']['name'][$j] . "</a></li></tr></td>";
                }
            }
            ?>

            <tr>
                <td>
                    <br>Supervisor assessment:
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['oralskill']['accepted'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['oralskill']['comment'] ?></ul>
                </td>
            </tr>

        </table>
        <br>

        <h3>8. Written Communication<br></h3>
        <p>Supervisor question:<br></p>
        <p>a) Can the candidate write in an appropriate format and express themselves clearly?<br> b) What written submissions demonstrate this?<br></p>
        <p><I>The input can be recorded as text and/or as a document file.</I></p>
        <table>
            <tr>
                <td>
                    Student input on text matching of the written work:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['writtenskill']['inputs']['text'] ?></ul>
                </td>
            </tr>

            <?php
            if(array_key_exists("uuid",$milestone_array['metadata']['writtenskill']['inputs'])) {
                echo "<tr><td>Student input as a file: <br>Add text-matching report if appropriate.</td></tr>";
                for ($j = 1; $j <= count($milestone_array['metadata']['writtenskill']['inputs']['uuid']); $j++) {
                    echo "<tr><td><li><a href='".$milestone_array['metadata']['writtenskill']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['writtenskill']['inputs']['name'][$j] . "</a></li></tr></td>";
                }
            }
            ?>

            <tr>
                <td>
                    <br>Supervisor assessment:
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['writtenskill']['accepted'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['writtenskill']['comment'] ?></ul>
                </td>
            </tr>

        </table>
        <br>
        <h3>9. Text-matching<br></h3>
        <p>Supervisor question:<br>Has the candidate’s written submission for this Milestone been subjected to text-matching?<br> </p>
        <p><I>If you would like to record your input to this section, please enter it as a text-based comment. Any text-matching reports should be added on the first page and on other pages where appropriate.</I></p>
        <table>
            <tr>
                <td>
                    Student input:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['textmatching']['inputs']['text'] ?></ul>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Has the candidate’s written submission for this Milestone been subjected to text-matching?
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['textmatching']['subjected'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Does the candidate’s written submission comply with the University’s Academic Integrity Policy?
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['textmatching']['integrity'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Supervisor comment:
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['textmatching']['comment'] ?></ul>
                </td>
            </tr>
        </table>
        <br>
        <h3>10. Goals for next Milestone<br></h3>
        <p>Supervisor question<br></p>
        <p>Recommended goals for next milestone. <br></p>
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
                echo "<td><br></td></tr>";
                $numOfGoal++;
            }
            ?>
        </table>
        <br>
        <h3>11. Supervisor</h3>
        <table>
            <tr>
                <td>
                    Recommendation:
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['supervisor']['recommendation'] ?></b>
                </td>
            </tr>

            <?php
            if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Satisfactory') == 0){
                echo "<tr><td><br>Supervisor comment: </td></tr>
                          <tr><td>".$milestone_array['metadata']['supervisor']['recommendationcomment']."<td></tr>";
            }
            if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Resubmit') == 0){
                echo "<tr><td><br>Supervisor judgement: Candidate to resubmit the milestone following completion of further work. </td></tr>
                          <tr><td>Specify additional work required, including a timeframe for the work to be resubmitted.</td></tr>
                          <tr><td>".$milestone_array['metadata']['supervisor']['resubmit']."<td></tr>";
            }
            if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Show cause') == 0){
                echo "<tr><td><br>Supervisor judgement: Candidate required to show cause </td></tr>
                          <tr><td>Indicate why the candidate should not be permitted to proceed with the candidature</td></tr>
                          <tr><td>".$milestone_array['metadata']['supervisor']['showcause']."<td></tr>";
            }

            ?>

        </table>
        <br><br/>
        </div>
    </div>

    <div id="partc" name="moderation" title="C. MODERATION" style="border-style: solid;border-width: 1px">
        <h2 style="color:white; background-color:black;">&nbsp;C. &nbsp;&nbsp;&nbsp;&nbsp;MODERATION</h2>
        <div style="padding:10px">
        <h3>1. MOD: Postgrad Coordinator</h3>
        <table>
            <tr>
                <td>
                    Supported by Postgraduate Coordinator:
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['coordinatorsupport'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    <p><br>Postgrad Coordinator comment: This makes more sense as a workflow step than in section D.</p>
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['mod']['coordinatorcomment'] ?></ul>
                </td>
            </tr>
        </table>
        <br>
        <h3>2. MOD: Candidate</h3>
        <table>
            <tr>
                <td>
                    Candidate signoff: I have noted and support/do not support this assessment
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['studentaccepted'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Candidate comment
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['mod']['studentcomment'] ?></ul>
                </td>
            </tr>
        </table>
        <br>
        <h3>3. MOD: Faculty</h3>
        <table>
            <tr>
                <td>
                    Faculty determination
                    <b><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['facultydetermination'] ?></b>
                </td>
            </tr>

            <tr>
                <td>
                    <br>Faculty comment
                </td>
            </tr>
            <tr>
                <td>
                    <ul><?php echo $milestone_array['metadata']['mod']['facultycomment'] ?></ul>
                </td>
            </tr>
        </table>
    </div>
    </div>
</body>
</html>