
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <!--<link rel="stylesheet" type="text/css" href="<?php /*echo base_url() . 'resource/rhd/';*/?>css/tmp_flin_base_v2.css" media="all">

    <link href="<?php /*echo base_url() . 'resource/rhd/';*/?>css/tmp_flin_print.css" rel="stylesheet" media="print"/>
    <link rel="stylesheet" href="<?php /*echo base_url() . 'resource/rhd/';*/?>css/tmp_flin_user_styles.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="<?php /*echo base_url() . 'resource/rhd/';*/?>css/flex-reports/jquery-ui-1.10.2.custom.css">
    <link rel="stylesheet" type="text/css" href="<?php /*echo base_url() . 'resource/rhd/';*/?>css/flindersbase_local.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?php /*echo base_url() . 'resource/rhd/';*/?>css/flindersbase_local_print.css" media="print">
-->



<!--     <style type="text/css">


     .table_content tr, .table_content td, .table_content{
         margin-bottom: 8px;
         margin-top: 0px;
         padding: 10px;
         border: 0;
         outline: 0;
         border-spacing: 0;
         text-align: left;
     }

     .sectionhead{
         color:white;
         background-color:black;
         font-size: 15px;
     }

     .sectiondiv{
         border-style: solid;
         border-width: 1px;
     }

     .contentdiv{
        padding: 10px;
     }

     </style>-->


    <title><?php echo $milestone_array['metadata']['name'] ?></title>


    <!--<script language="javascript">
        var now=new Date();
        document.write(now.getDay()+"-"+now.getMonth()+"-"+now.getYear());
    </script>-->

</head>

<body id="bodytag">
    <?php
    $refValue = new DateTime("now");
    $displayDate = $refValue->format('c');
    ?>
    <p><?php echo $displayDate ?></p>
	<!--Banner-->
    <?php if($milestone_array['format']=='html'){?>
      <header class="main_header" role="banner">

        <h1>Flinders University</h1>
        <!--<div id="main_banner">
        	<img class="tagline" src="http://www.flinders.edu.au/flinders/app_templates/flinderstemplates/images/banners/inspiring_achievement.png" alt="tagline image" />
        	<a href="http://www.flinders.edu.au"><img src="http://www.flinders.edu.au/flinders/app_templates/flinderstemplates/images/flinders_logo.png" alt="Flinders logo"></a>
			
	  		
        </div>-->
        </header><!--End Banner-->
	<?php } ?>
    
<!--Content wrapper (between header and footer) -->
<!--<div id="page_content" class="row" style="background-color:#FFF; ">  -->
 <!--  page_content row wrapper -->		      
<!-- <div class="column grid_15" style="margin-bottom: 60px;">-->

<!-- main content area -->
        
<!--<div class="row">
<div class="column grid_15">  
<article role="main">
<div id="container_num_1" class="container container_no_box">-->
    <div class="noprint">
        <h1 style="text-align:center; font-size: 20px"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $milestone_array['metadata']['name']?>
            <span class="importantText">
               <?php  $status = $milestone_array['status'];
                      if(isset($status)){?>
                        <?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$status;
                     }?>
            </span>
        </h1>
    </div>
  
<!-- Content of Milestone -->

	<h2 style="text-align:center">Flinders University</h2>

    <div class = "sectiondiv">
        <h2 class = "sectionhead">&nbsp;A. &nbsp;&nbsp;&nbsp;&nbsp;CANDIDATE'S DETAILS</h2>

        <table class="table_content">
            <tr>
                <td>
                    Candiate Name:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['candidatename'])?$milestone_array['metadata']['candidatename']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    Student ID:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['studentid'])?$milestone_array['metadata']['studentid']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    Student FAN:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['studenfan'])? $milestone_array['metadata']['studenfan']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    Degree:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['degree'])? $milestone_array['metadata']['degree']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    Topic Code:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['topiccode'])? $milestone_array['metadata']['topiccode']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    Thesis Title:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['thesistitle'])? $milestone_array['metadata']['thesistitle']: ""?>
                </td>
            </tr>
            <tr>
                <td>
                    School (Dept or Unit):
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['schoolname'])? $milestone_array['metadata']['schoolname']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    FT/PT Candidature:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['fullorparttime'])? $milestone_array['metadata']['fullorparttime']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    Candidature Commencement Date:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['commercedate'])? $milestone_array['metadata']['commercedate']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    Expected Submission Date:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['submissiondate'])? $milestone_array['metadata']['submissiondate']:""?>
                </td>
            </tr>
            <tr>
                <td>
                    RTS End Date:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['rtsenddate'])?$milestone_array['metadata']['rtsenddate']:""?>
                </td>
            </tr>

            <?php if($milestone_array['metadata']['exportcontrol']=='Yes'){?>
            <tr>
                <td>
                    Thesis is subject to export control:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['exportcontrol'])?$milestone_array['metadata']['exportcontrol']:""?>
                </td>
            </tr>
            <?php } ?>

            <?php if($milestone_array['metadata']['tpagreement']=='Yes'){?>
            <tr>
                <td>
                    This research/thesis is subject to a formal third party agreement:
                </td>
                <td>
                    <?php echo isset($milestone_array['metadata']['tpagreement'])?$milestone_array['metadata']['tpagreement']:""?>
                </td>
            </tr>
            <?php } ?>

            <tr>
                <td>
                    Supervisor(s):
                </td>
                <td><ul>
                        <?php
                        if(isset($milestone_array['metadata']['supervisorsdetail'])){
                            $supervisorsList = $milestone_array['metadata']['supervisorsdetail'];
                            foreach ($supervisorsList as $supervisor){
                                echo "<li>". $supervisor['role'] . ": " . $supervisor['title'] . ". " . $supervisor['name'] . "</li>";
                            }
                        }
                        ?>
                    </ul></td>
            </tr>

            <?php
            if(isset($milestone_array['metadata']['overallreport'])) {
                echo "<tr><td>Overall report - to support milestone determination.<ul>";
                for ($j = 1; $j <= count($milestone_array['metadata']['overallreport']['uuid']); $j++) {
                    echo "<li><a href='".$milestone_array['metadata']['overallreport']['link'][$j]."'>" . $milestone_array['metadata']['overallreport']['name'][$j] . "</a></li>";
                }
                echo "</ul></td></tr>";
            }
            ?>

        </table>

    </div>

    <div class = "sectiondiv">
        <h2 class = "sectionhead">&nbsp;B. &nbsp;&nbsp;&nbsp;&nbsp;ASSESSMENT CRITERIA</h2>
        <div class = "contentdiv">
            <h4>Professional Development </h4>
            <ul class="ul_topic">

                <li>
                    Does student completed any professional development activities since your last milestone?
                    <b><?php echo isset($milestone_array['metadata']['professionaldevelopment']['completed'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['professionaldevelopment']['completed'] : ""?></b>
                </li>

                <?php
                if (strnatcasecmp($milestone_array['metadata']['professionaldevelopment']['completed'], 'Yes') == 0){
                    echo "<li>Outline the completed activities:<ul><li>";
                    echo isset($milestone_array['metadata']['professionaldevelopment']['comment'])? $milestone_array['metadata']['professionaldevelopment']['comment']: "";
                    echo "</li></ul></li>";
                }
                ?>

                <li>
                    Does student require additional education and/or training to ensure timely completion?
                    <b><?php echo isset($milestone_array['metadata']['additionaleducation']['requested'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['additionaleducation']['requested'] : ""?></b>
                </li>

                <?php
                    if (strnatcasecmp($milestone_array['metadata']['additionaleducation']['requested'], 'Yes') == 0){
                        echo "<li>Detail what you need and why:<ul><li>";
                        echo isset($milestone_array['metadata']['additionaleducation']['inputs']['text'])? $milestone_array['metadata']['additionaleducation']['inputs']['text']: "";
                        echo "</ul></li>";
                    }

                    if(isset($milestone_array['metadata']['additionaleducation']['inputs']['uuid'])) {
                        echo "<li>Supporting documentation:<ul>";
                        for ($j = 1; $j <= count($milestone_array['metadata']['additionaleducation']['inputs']['uuid']); $j++) {
                            echo "<li><a href='".$milestone_array['metadata']['additionaleducation']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['additionaleducation']['inputs']['name'][$j] . "</a></li>";
                        }
                        echo "</ul></li>";
                    }
                ?>

                <li>
                    Supervisor comment:
                    <ul>
                        <li><?php echo isset($milestone_array['metadata']['additionaleducation']['comment'])? $milestone_array['metadata']['additionaleducation']['comment'] :"" ?></li>
                    </ul>
                </li>
            </ul>
        </div>


        <div class = "contentdiv">
            <h4>Resource </h4>
            <ul class="ul_topic">
                <li>
                    Does student require additional resources to complete the project, other than those noted at admission?<b> <?php echo isset($milestone_array['metadata']['additionalresource']['requested']) ? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['additionalresource']['requested'] : "" ?></b>
                </li>

                <?php
                if (strnatcasecmp($milestone_array['metadata']['additionalresource']['requested'], 'Yes') == 0){
                    echo "<li>Detail student needs and the reasons:<ul><li>";
                    echo isset($milestone_array['metadata']['additionalresource']['inputs']['text'])? $milestone_array['metadata']['additionalresource']['inputs']['text']: "";
                    echo "</li></ul></li>";
                }
                if(isset($milestone_array['metadata']['additionalresource']['inputs']['uuid'])) {
                    echo "<li>Supporting documentation:<ul>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['additionalresource']['inputs']['uuid']); $j++) {
                        echo "<li><a href='".$milestone_array['metadata']['additionalresource']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['additionalresource']['inputs']['name'][$j] . "</a></li>";
                    }
                    echo "</ul></li>";
                }
                ?>

                <li>
                    Supervisor comment:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['additionalresource']['comment']) ? $milestone_array['metadata']['additionalresource']['comment']: "" ?>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>


        <?php if($milestone_array['metadata']['nameid']=='1000'){?>
        <div class = "contentdiv">
            <h4>Research Question (Supervisor question):</h4>
            <ul class="ul_topic">
                <li>
                    Has the student articulated a suitable research problem or question(s)?
                    <b><?php echo isset($milestone_array['metadata']['researchproblem']['accepted']) ? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['researchproblem']['accepted'] : ""?></b>
                </li>

                <li>
                    Student input as text:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['researchproblem']['inputs']['text'] ) ? $milestone_array['metadata']['researchproblem']['inputs']['text'] : ""?>
                        </li>
                    </ul>
                </li>


                <?php
                if(isset($milestone_array['metadata']['researchproblem']['inputs']['uuid'])) {
                    echo "<li>Student input as a file. Add text-matching report if appropriate.<ul>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['researchproblem']['inputs']['uuid']); $j++) {
                        echo "<li><a href='".$milestone_array['metadata']['researchproblem']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['researchproblem']['inputs']['name'][$j] . "</a></li>";
                    }
                    echo "</ul></li>";
                }
                ?>

                <li>
                    Supervisor comment:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['researchproblem']['comment']) ? $milestone_array['metadata']['researchproblem']['comment']:"" ?>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <?php } ?>


        <div class = "contentdiv">
            <h4>Plan </h4>

            <ul class="ul_topic">
                <?php if($milestone_array['metadata']['nameid']=='1000' || $milestone_array['metadata']['nameid']=='10' ){?>
                <li>
                    Proposal: student input as text
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['planproposal']['inputs']['text'])?$milestone_array['metadata']['planproposal']['inputs']['text'] :""?>
                        </li>
                    </ul>
                </li>

                <?php
                if(isset($milestone_array['metadata']['planproposal']['inputs']['uuid'])) {
                    echo "<li>Proposal: the input as a file. <I>Add text-matching report if appropriate.</I><ul>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['planproposal']['inputs']['uuid']); $j++) {
                        echo "<li><a href='".$milestone_array['metadata']['planproposal']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['planproposal']['inputs']['name'][$j] . "</a></li>";
                    }
                    echo "</ul></li>";
                }
                ?>

                <li>
                    Lit review: student input as text
                    <ul>
                        <li><?php echo isset($milestone_array['metadata']['litreview']['inputs']['text'])? $milestone_array['metadata']['litreview']['inputs']['text'] :""?></li>
                    </ul>
                </li>

                    <?php
                    if(isset($milestone_array['metadata']['litreview']['inputs']['uuid'])) {
                        echo "<li>Lit review: student input as a file<I>Add text-matching report if appropriate.</I><ul>";
                        for ($j = 1; $j <= count($milestone_array['metadata']['litreview']['inputs']['uuid']); $j++) {
                            echo "<li><a href='".$milestone_array['metadata']['litreview']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['litreview']['inputs']['name'][$j] . "</a></li>";
                            echo "</ul></li>";
                        }
                    }
                    ?>
                <?php } ?>

                <?php
                if($milestone_array['metadata']['nameid']!='1000' and $milestone_array['metadata']['nameid']!='10'){
                    echo "<li>Completion plan: student input as text<ul><li>";

                    echo isset($milestone_array['metadata']['completionplan']['inputs']['text'])? $milestone_array['metadata']['completionplan']['inputs']['text'] :"";
                    echo "</li></ul></li>";


                    if(isset($milestone_array['metadata']['completionplan']['inputs']['uuid'])) {
                        echo "<li>Lit review: student input as a file.<I>Add text-matching report if appropriate.</I><ul>";
                        for ($j = 1; $j <= count($milestone_array['metadata']['completionplan']['inputs']['uuid']); $j++) {
                            echo "<li><a href='".$milestone_array['metadata']['completionplan']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['completionplan']['inputs']['name'][$j] . "</a></li>";
                            echo "</ul></li>";
                        }
                    }
                }
                ?>

                <li>
                    Timeframe: student input as text
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['plantimeframe']['inputs'])? $milestone_array['metadata']['plantimeframe']['inputs'] :""?>
                        </li>
                    </ul>
                </li>

                <li>
                    Has the student provided a detailed and achievable plan?<b><?php echo isset($milestone_array['metadata']['planproposal']['achievable'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['planproposal']['achievable'] :""?></b>
                </li>

                <li>
                    Supervisor comment:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['planproposal']['comment'])? $milestone_array['metadata']['planproposal']['comment']:"" ?>
                        </li>
                    </ul>
                </li>


                <li>
                    Is the timeframe realistic/feasible?<b><?php echo isset($milestone_array['metadata']['planproposal']['realistic'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['planproposal']['realistic']:"" ?></b>
                </li>

                <li>
                    Supervisor comment:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['plantimeframe']['comment'])?$milestone_array['metadata']['plantimeframe']['comment']:"" ?>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>


        <div class = "contentdiv">
            <h4>Methodology </h4>

            <ul class="ul_topic">
                <li>
                     Appropriate to the discipline, has the student identified a research methodology/method and type of data collection, or equivalent, for the project?
                     <b><?php echo isset($milestone_array['metadata']['methodology']['accepted'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['methodology']['accepted'] :""?></b>
                </li>

                <li>
                    Student input as text:
                    <ul>
                        <li><?php echo isset($milestone_array['metadata']['methodology']['inputs']['text'])? $milestone_array['metadata']['methodology']['inputs']['text']:"" ?></li>
                    </ul>
                </li>

                <?php
                if(isset($milestone_array['metadata']['methodology']['inputs']['uuid'])) {
                    echo "<li>The input as a file:<I>Add text-matching report if appropriate.</I><ul>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['methodology']['inputs']['uuid']); $j++) {
                        echo "<li><a href='".$milestone_array['metadata']['methodology']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['methodology']['inputs']['name'][$j] . "</a></li>";
                    }
                    echo "</ul></li>";
                }
                ?>

                <li>
                    Supervisor comment:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['methodology']['comment'])? $milestone_array['metadata']['methodology']['comment']:"" ?>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <?php if($milestone_array['metadata']['nameid']=='1000' or $milestone_array['metadata']['nameid']=='10' ){?>

        <div class = "contentdiv">
            <h4>Ethics (Supervisor question): Has the appropriate ethics approval been sought?</h4>

            <ul class="ul_content">
                <li>
                    Student input as text:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['ethics']['inputs']['text'])? $milestone_array['metadata']['ethics']['inputs']['text'] :""?>
                        </li>
                    </ul>
                </li>


                <?php
                if(isset($milestone_array['metadata']['ethics']['inputs']['uuid'])) {
                    echo "<li>Student input as a file: Add text-matching report if appropriate.<ul>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['ethics']['inputs']['uuid']); $j++) {
                        echo "<li><a href='".$milestone_array['metadata']['ethics']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['ethics']['inputs']['name'][$j] . "</a></li>";
                    }
                    echo "</ul></li>";
                }
                ?>

                <li>
                    Supervisor assessment-&nbsp;<I>Refer to Ethics, Biosafety and Integrity for more information.</I><b><?php echo isset($milestone_array['metadata']['ethics']['accepted'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['ethics']['accepted']:"" ?></b>
                </li>

                <li>
                    Supervisor comment:
                    <ul>
                        <li><?php echo isset($milestone_array['metadata']['ethics']['comment'])? $milestone_array['metadata']['ethics']['comment']:"" ?></li>
                    </ul>
                </li>

            </ul>
        </div>
        <?php } ?>


        <div class = "contentdiv">
            <h4>Oral Communication </h4>

            <ul class="ul_topic">
                <li>
                        Student input as text:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['oralskill']['inputs']['text'])? $milestone_array['metadata']['oralskill']['inputs']['text']:"" ?>
                        </li>
                    </ul>
                </li>


                <?php
                if(isset($milestone_array['metadata']['oralskill']['inputs']['uuid'])) {
                    echo "<li>Student input as a file: <I>Add text-matching report if appropriate.</I><ul>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['oralskill']['inputs']['uuid']); $j++) {
                        echo "<li><a href='".$milestone_array['metadata']['oralskill']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['oralskill']['inputs']['name'][$j] . "</a></li>";
                    }
                    echo "</ul></li>";
                }
                ?>

                <li>
                    Supervisor assessment:<b><?php echo isset($milestone_array['metadata']['oralskill']['accepted'])?  '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['oralskill']['accepted'] :""?></b>
                </li>

                <li>
                    Supervisor comment:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['oralskill']['comment'])? $milestone_array['metadata']['oralskill']['comment']:"" ?>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>


        <div class = "contentdiv">
            <h4>Written Communication </h4>

            <ul class="ul_topic">
                <li>
                    Student input on text matching of the written work:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['writtenskill']['inputs']['text'])?$milestone_array['metadata']['writtenskill']['inputs']['text']:"" ?>
                        </li>
                    </ul>
                </li>


                <?php
                if(isset($milestone_array['metadata']['writtenskill']['inputs']['uuid'])) {
                    echo "<li>Student input as a file: <I>Add text-matching report if appropriate.</I><ul>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['writtenskill']['inputs']['uuid']); $j++) {
                        echo "<li><a href='".$milestone_array['metadata']['writtenskill']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['writtenskill']['inputs']['name'][$j] . "</a></li>";
                    }
                    echo "</ul></li>";
                }
                ?>

                <li>
                    Supervisor assessment:<b><?php echo isset($milestone_array['metadata']['writtenskill']['accepted'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['writtenskill']['accepted']:"" ?></b>
                </li>

                <li>
                    Supervisor comment:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['writtenskill']['comment'] )? $milestone_array['metadata']['writtenskill']['comment'] :""?>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>

        <div class = "contentdiv">
            <h4>Text-matching </h4>
            <ul class="ul_topic">
                <li>
                    Student input:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['textmatching']['inputs']['text'])? $milestone_array['metadata']['textmatching']['inputs']['text'] : ""?>
                        </li>
                    </ul>
                </li>

                <li>
                    Has the student’s written submission for this Milestone been subjected to text-matching?
                    <b><?php echo isset($milestone_array['metadata']['textmatching']['subjected'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['textmatching']['subjected']:"" ?></b>
                </li>

                <li>
                    Does the student’s written submission comply with the University’s Academic Integrity Policy?
                    <b><?php echo isset($milestone_array['metadata']['textmatching']['integrity'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['textmatching']['integrity']:"" ?></b>
                </li>

                <li>
                    Supervisor comment:
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['textmatching']['comment'])? $milestone_array['metadata']['textmatching']['comment']:""?>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>


        <div class = "contentdiv">
            <h4>Goals for next Milestone </h4>
            <ul class="ul_topic">
                <?php
                if(isset($milestone_array['metadata']['studentgoal'])){
                    $numOfGoal = 1;
                    foreach($milestone_array['metadata']['studentgoal'] as $goal){
                        echo "<li>The information of goal ".$numOfGoal."<ul>";
                        echo "<li>Time Frame:";
                        if(isset($goal['timeframe']))
                            echo $goal['timeframe']."</li>";
                        else "</li>";

                        echo "<li>Goal:";
                        if(isset($goal['goalcontent']))
                            echo $goal['goalcontent']."</li>";
                        else "</li>";

                        echo "<li>Comment:";
                        if(isset($goal['comment']))
                            echo $goal['comment']."</li>";
                        else "</li>";
                        echo "</ul></li>";
                        $numOfGoal++;
                    }
                }
                else
                    echo "<li></li>";
                ?>
            </ul>
        </div>

        <?php if($milestone_array['metadata']['nameid']=='3000'){?>
            <div class = "contentdiv">
                <h4>Publishing</h4>
                <p>Recommend publishing opportunities either pre or post-graduation (if appropriate).</p>
                <p><I>If student would like to record input to this section, please enter it as text and/or as a document file.</I></p>
                <ul class="ul_content">
                    <li>
                        Student input as text:
                        <ul>
                            <li>
                                <?php echo isset($milestone_array['metadata']['publish']['inputs']['text'])? $milestone_array['metadata']['publish']['inputs']['text'] :""?>
                            </li>
                        </ul>
                    </li>

                    <?php
/*                    if(isset($milestone_array['metadata']['publish']['inputs']['uuid'])) {
                        echo "<li><br>Student input as a file: <br><I>Add text-matching report if appropriate.</I><br><ul>";
                        for ($j = 1; $j <= count($milestone_array['metadata']['publish']['inputs']['uuid']); $j++) {
                            echo "<li><a href='".$milestone_array['metadata']['publish']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['ethics']['inputs']['name'][$j] . "</a></li>";
                        }
                        echo "</ul></li>";
                    }
                    */?><!--

                    <li>
                        <p><br>Supervisor assessment-&nbsp;<I>Refer to Ethics, Biosafety and Integrity for more information.</I><b><?php /*echo isset($milestone_array['metadata']['publish']['accepted'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['publish']['accepted']:"" */?></b></p>
                    </li>-->

                    <li>
                        Supervisor comment:
                        <ul>
                            <li>
                                <?php echo isset($milestone_array['metadata']['publish']['comment'])? $milestone_array['metadata']['publish']['comment']:"" ?>
                            </li>
                        </ul>
                    </li>

                </ul>
            </div>
        <?php } ?>


        <div class = "contentdiv">
            <h4>Supervisor</h4>
            <ul class="ul_topic">
                <li>
                    Recommendation:
                    <b><?php echo isset($milestone_array['metadata']['supervisor']['recommendation'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['supervisor']['recommendation'] :""?></b>
                </li>

                <?php
                if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Satisfactory') == 0){
                    echo "<li>Supervisor comment: <ul><li>";
                    echo isset($milestone_array['metadata']['supervisor']['recommendationcomment'])? $milestone_array['metadata']['supervisor']['recommendationcomment']: "";
                    echo "</li></ul></li>";
                }
                if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Resubmit') == 0){
                    echo "<li>Supervisor judgement: Candidate to resubmit the milestone following completion of further work. 
                              Specify additional work required, including a timeframe for the work to be resubmitted.<ul><li>";
                    echo isset($milestone_array['metadata']['supervisor']['resubmit'])? $milestone_array['metadata']['supervisor']['resubmit']: "";
                    echo "</li></ul></li>";
                }
                if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Show cause') == 0){
                    echo "<li>Supervisor judgement: student required to show cause --- indicate why the student should not be permitted to proceed with the candidature.<ul><li>";
                    echo isset($milestone_array['metadata']['supervisor']['showcause'])? $milestone_array['metadata']['supervisor']['showcause']: "";
                    echo "</li></ul></li>";
                }
                ?>

            </ul>
        </div>


        </div>
    </div>

    <div class = "sectiondiv">
        <h2 class = "sectionhead">&nbsp;C. &nbsp;&nbsp;&nbsp;&nbsp;MODERATION</h2>
        <div class = "contentdiv">
            <h4>MOD: Postgrad Coordinator</h4>
            <ul class="ul_topic">
                <li>
                    Supported by Postgraduate Coordinator:
                    <b><?php echo isset($milestone_array['metadata']['mod']['coordinatorsupport'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['coordinatorsupport'] :""?></b>
                </li>

                <li>
                    Postgrad Coordinator comment: This makes more sense as a workflow step than in section D.
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['mod']['coordinatorcomment']) ? $milestone_array['metadata']['mod']['coordinatorcomment'] : "" ?>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class = "contentdiv">
            <h4>MOD: Candidate</h4>
            <ul class="ul_topic">
                <li>
                    Candidate signoff: I have noted and support/do not support this assessment
                    <b><?php echo isset($milestone_array['metadata']['mod']['studentaccepted'] ) ? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['studentaccepted'] :""?></b>
                </li>

                <li>
                    Candidate comment
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['mod']['studentcomment'])? $milestone_array['metadata']['mod']['studentcomment']:"" ?>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class = "contentdiv">
            <h4>MOD: Faculty</h4>
            <ul class="ul_topic">
                <li>
                    Faculty determination
                    <b><?php echo isset($milestone_array['metadata']['mod']['facultydetermination']) ?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['facultydetermination'] :""?></b>
                </li>

                <li>
                    Faculty comment
                    <ul>
                        <li>
                            <?php echo isset($milestone_array['metadata']['mod']['facultycomment'])? $milestone_array['metadata']['mod']['facultycomment']:"" ?>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>

</body>
</html>