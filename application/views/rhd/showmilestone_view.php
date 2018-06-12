
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

<?php
    function isValued($value){
        if(!isset($value)) return false;
        else if(empty($value)) return false;
        else return true;
    }
?>

     <style type="text/css">
         .table_content tr, .table_content td, .table_content{
             margin-bottom: 8px;
             margin-top: 0px;
             padding: 10px;
             padding-left: 0px;
             outline: 0;
             border-spacing: 0;
             text-align: left;
         }

         .sectionhead{
             color:white;
             background-color:black;
             font-size: 15px;
         }

         .sectioncontentdiv{
             padding: 10px;
             padding-top: 0px;
         }

         .contentdiv{
             padding: 10px;
             padding-top: 0px;
             border-bottom-style: solid;
             border-bottom-width: 1px;
         }

         .title_topic{
             background-color:gainsboro;
         }

         .li_item, .ul_item{
             padding: 10px;
         }

         .p_item_content{
             padding-left:30px;
             font-size: smaller;
         }

         .p_item {
             font-size: medium;
             line-height: 150%;
             padding-top: 15px;
         }

         .div_html{
             padding-left: 20px;
             padding-right: 20px;
         }
         }

     </style>


    <title><?php echo $milestone_array['metadata']['name']; ?></title>


</head>

<body id="bodytag">
    <?php
    $refValue = new DateTime("now");
    $displayDate = $refValue->format('c');
    ?>
    <p><?php echo $displayDate ?></p>
    <p style="color: red"><?php if(isset($milestone_array['metadata']['studentsignoff']) and strnatcasecmp($milestone_array['metadata']['studentsignoff'],'Yes')) echo "Note: the student has not completed this report."?></p>
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
        <h1 style="text-align:center; font-size: 20px"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $milestone_array['metadata']['name'];?>
            <span class="importantText">
               <?php  $status = $milestone_array['status'];
                      if(isset($status)){
                          echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$status;
                     }?>
            </span>
        </h1>
    </div>
  
<!-- Content of Milestone -->

	<h2 style="text-align:center">Flinders University</h2>

    <div class = "sectiondiv">
        <div class="sectionheaddiv">
            <h2 class = "sectionhead">&nbsp;A. &nbsp;&nbsp;&nbsp;&nbsp;STUDENT'S DETAILS</h2>
        </div>
        <div class="sectioncontentdiv">
            <table class="table_content">
                <tr>
                    <td>
                        Student Name:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['candidatename'])?$milestone_array['metadata']['candidatename']:"";?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Student ID:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['studentid'])?$milestone_array['metadata']['studentid']:""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Student FAN:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['studenfan'])? $milestone_array['metadata']['studenfan']:""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Degree:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['degree'])? $milestone_array['metadata']['degree']:""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Topic Code:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['topiccode'])? $milestone_array['metadata']['topiccode']:""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Thesis Title:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['thesistitle'])? $milestone_array['metadata']['thesistitle']: ""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        School (Dept or Unit):
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['schoolname'])? $milestone_array['metadata']['schoolname']:""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Full/Part Time:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['fullorparttime'])? $milestone_array['metadata']['fullorparttime']:""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Student Start Date:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['commercedate'])? $milestone_array['metadata']['commercedate']:""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Expected Submission Date:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['submissiondate'])? $milestone_array['metadata']['submissiondate']:""; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        RTS End Date:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['rtsenddate'])?$milestone_array['metadata']['rtsenddate']:""; ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        Total EFTSL :
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['eftsl'])?$milestone_array['metadata']['eftsl']:""; ?>
                    </td>
                </tr>


                <tr>
                    <td>
                        Thesis is subject to export control:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['exportcontrol'])?$milestone_array['metadata']['exportcontrol']:""; ?>
                    </td>
                </tr>


                <tr>
                    <td>
                        This research/thesis is subject to a formal third party agreement:
                    </td>
                    <td>
                        <?php echo isset($milestone_array['metadata']['tpagreement'])?$milestone_array['metadata']['tpagreement']:""; ?>
                    </td>
                </tr>


                <tr>
                    <td>
                        Supervisor(s):
                    </td>
                    <td>
                            <?php
                            if(isset($milestone_array['metadata']['supervisorsdetail'])){
                                echo "<ul class='ul_item'>";
                                $supervisorsList = $milestone_array['metadata']['supervisorsdetail'];
                                foreach ($supervisorsList as $supervisor){
                                    echo "<li class='li_item'>". $supervisor['role'] . ": " . $supervisor['title'] . ". " . $supervisor['name'] . "</li>";
                                }
                                echo "</ul>";
                            }
                            ?>
                    </td>
                </tr>

                <!--<tr>
                    <td>
                        Coordinator(s):
                    </td>
                    <td>
                        <?php
/*                        if(isset($milestoneArray['metadata']['coordinatorsdetail'])){
                            echo "<p class='p_item'>";
                            $coordinatorsList = $milestone_array['metadata']['coordinatorsdetail'];
                            foreach ($coordinatorsList as $coordinator){
                                echo "<p class='p_item_content'>". $coordinator['role'] . ": "  . $coordinator['name'] . "</p>";
                            }
                            echo "</p>";
                        }
                        */?>
                    </td>
                </tr>-->

            </table>

                <?php
                if(isset($milestone_array['metadata']['overallreport'])) {
                    echo "<p>Overall report - to support milestone determination.</p><ul class='ul_item'>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['overallreport']['uuid']); $j++) {
                        echo "<li class='li_item'><a href='".$milestone_array['metadata']['overallreport']['link'][$j]."'>" . $milestone_array['metadata']['overallreport']['name'][$j] . "</a></li>";
                    }
                    echo "</ul>";
                }
                ?>
        </div>
    </div>

    <div class = "sectiondiv">
        <div class="sectionheaddiv">
            <h2 class = "sectionhead">&nbsp;B. &nbsp;&nbsp;&nbsp;&nbsp;ASSESSMENT CRITERIA</h2>
        </div>
        <div class="sectioncontentdiv">
            <div class = "contentdiv">
                <h4 class = "title_topic">Professional Development </h4>
                    <p class="p_item">
                        Does student completed any professional development activities since your last milestone?
                        <b><?php echo isset($milestone_array['metadata']['professionaldevelopment']['completed'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['professionaldevelopment']['completed'] : ""; ?></b>
                    </p>

                    <?php
                    if (strnatcasecmp($milestone_array['metadata']['professionaldevelopment']['completed'], 'Yes') == 0){
                        echo "<p class='p_item'>Outline the completed activities:</p><div class='div_html'>";
                        echo isset($milestone_array['metadata']['professionaldevelopment']['comment'])? $milestone_array['metadata']['professionaldevelopment']['comment']: "";
                        echo "</div>";
                    }
                    ?>

                    <p class="p_item">
                        Does student require additional education and/or training to ensure timely completion?
                        <b><?php echo isset($milestone_array['metadata']['additionaleducation']['requested'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['additionaleducation']['requested'] : ""; ?></b>
                    </p>

                    <?php
                    if (strnatcasecmp($milestone_array['metadata']['additionaleducation']['requested'], 'Yes') == 0){
                        echo "<p class='p_item'>Detail what you need and why:</p><div class='div_html'>";
                        echo isset($milestone_array['metadata']['additionaleducation']['inputs']['text'])? $milestone_array['metadata']['additionaleducation']['inputs']['text']: "";
                        echo "</div>";
                    }

                    if(isset($milestone_array['metadata']['additionaleducation']['inputs']['uuid'])) {
                        echo "<p class='p_item'>Supporting documentation:</p>";
                        for ($j = 1; $j <= count($milestone_array['metadata']['additionaleducation']['inputs']['uuid']); $j++) {
                            echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['additionaleducation']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['additionaleducation']['inputs']['name'][$j] . "</a></p>";
                        }
                    }
                    ?>

                    <p class="p_item">Supervisor comment:</p>
                        <?php echo isset($milestone_array['metadata']['additionaleducation']['comment'])? "<div class='div_html'>".$milestone_array['metadata']['additionaleducation']['comment']."</div>" :""; ?>
            </div>


            <div class = "contentdiv">
                <h4 class = "title_topic">Resource </h4>

                    <p class="p_item">
                        Does student require additional resources to complete the project, other than those noted at admission?<b> <?php echo isset($milestone_array['metadata']['additionalresource']['requested']) ? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['additionalresource']['requested'] : "" ?></b>
                    </p>

                    <?php
                    if (strnatcasecmp($milestone_array['metadata']['additionalresource']['requested'], 'Yes') == 0){
                        echo "<p class='p_item'>Detail student needs and the reasons:</p><div class='div_html'>";
                        echo isset($milestone_array['metadata']['additionalresource']['inputs']['text'])? $milestone_array['metadata']['additionalresource']['inputs']['text']: "";
                        echo "</div>";
                    }
                    if(isset($milestone_array['metadata']['additionalresource']['inputs']['uuid'])) {
                        echo "<p class='p_item'>Supporting documentation:</p>";
                        for ($j = 1; $j <= count($milestone_array['metadata']['additionalresource']['inputs']['uuid']); $j++) {
                            echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['additionalresource']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['additionalresource']['inputs']['name'][$j] . "</a></p>";
                        }
                    }
                    ?>

                <p class="p_item">Supervisor comment:</p>

                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['additionalresource']['comment']) ? $milestone_array['metadata']['additionalresource']['comment']: ""; ?>
                </div>

            </div>


            <?php if($milestone_array['metadata']['nameid']=='1000'){?>
            <div class = "contentdiv">
                <h4 class = "title_topic">Research Question (Supervisor question):</h4>

                <p class="p_item">
                    Has the student articulated a suitable research problem or question(s)?
                    <b><?php echo isset($milestone_array['metadata']['researchproblem']['accepted']) ? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['researchproblem']['accepted'] : ""; ?></b>
                </p>

                <p class="p_item">Student input as text:</p>
                <?php echo isset($milestone_array['metadata']['researchproblem']['inputs']['text'] ) ? "<div class='div_html'>".$milestone_array['metadata']['researchproblem']['inputs']['text']."</div>" : ""; ?>



                <?php
                if(isset($milestone_array['metadata']['researchproblem']['inputs']['uuid'])) {
                    echo "<p class='p_item'>Student input as a file. Add text-matching report if appropriate.</p>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['researchproblem']['inputs']['uuid']); $j++) {
                        echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['researchproblem']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['researchproblem']['inputs']['name'][$j] . "</a></p>";
                    }
                }
                ?>

                <p class="p_item">Supervisor comment:</p>
                <?php echo isset($milestone_array['metadata']['researchproblem']['comment']) ? "<div class='div_html'>".$milestone_array['metadata']['researchproblem']['comment']."</div>":""; ?>

            </div>
            <?php } ?>


            <div class = "contentdiv">
                <h4 class = "title_topic">Plan </h4>

                <p class="p_item">Plan: student input as text</p>
                <?php echo isset($milestone_array['metadata']['plan']['inputs']['text'])? "<div class='div_html'>".$milestone_array['metadata']['plan']['inputs']['text']."</div>" :""; ?>

                <?php
                if(isset($milestone_array['metadata']['plan']['inputs']['uuid'])) {
                    echo "<p class='p_item'>Plan: the input as a file. </p>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['plan']['inputs']['uuid']); $j++) {
                        echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['plan']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['plan']['inputs']['name'][$j] . "</a></p>";
                    }
                }
                ?>

                    <?php /*if($milestone_array['metadata']['nameid']=='1000' || $milestone_array['metadata']['nameid']=='10' ){*/?><!--
                    <p class="p_item_content">
                        Proposal: student input as text
                        <?php /*echo isset($milestone_array['metadata']['planproposal']['inputs']['text'])? "<p class='p_item'><p class='p_item_content'>".$milestone_array['metadata']['planproposal']['inputs']['text']."</p></p>" :""; */?>
                    </p>

                    <?php
/*                    if(isset($milestone_array['metadata']['planproposal']['inputs']['uuid'])) {
                        echo "<p class='p_item_content'>Proposal: the input as a file. <I>Add text-matching report if appropriate.</I><p class='p_item'>";
                        for ($j = 1; $j <= count($milestone_array['metadata']['planproposal']['inputs']['uuid']); $j++) {
                            echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['planproposal']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['planproposal']['inputs']['name'][$j] . "</a></p>";
                        }
                        echo "</p></p>";
                    }
                    */?>

                    <p class="p_item_content">
                        Lit review: student input as text
                        <?php /*echo isset($milestone_array['metadata']['litreview']['inputs']['text'])? "<p class='p_item'><p class='p_item_content'>".$milestone_array['metadata']['litreview']['inputs']['text']."</p></p>" :""*/?>
                    </p>

                        <?php
/*                        if(isset($milestone_array['metadata']['litreview']['inputs']['uuid'])) {
                            echo "<p class='p_item_content'>Lit review: student input as a file<I>Add text-matching report if appropriate.</I><p class='p_item'>";
                            for ($j = 1; $j <= count($milestone_array['metadata']['litreview']['inputs']['uuid']); $j++) {
                                echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['litreview']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['litreview']['inputs']['name'][$j] . "</a></p>";
                                echo "</p></p>";
                            }
                        }
                        */?>
                    <?php /*} */?>

                    --><?php
/*                    if($milestone_array['metadata']['nameid']!='1000' and $milestone_array['metadata']['nameid']!='10'){
                        echo "<p class='p_item_content'>Completion plan: student input as text";

                        echo isset($milestone_array['metadata']['completionplan']['inputs']['text'])? "<p class='p_item'><p class='p_item_content'>".$milestone_array['metadata']['completionplan']['inputs']['text']."</p></p>" :"";
                        echo "</p>";


                        if(isset($milestone_array['metadata']['completionplan']['inputs']['uuid'])) {
                            echo "<p class='p_item_content'>Lit review: student input as a file.- <I>Add text-matching report if appropriate.</I><p class='p_item'>";
                            for ($j = 1; $j <= count($milestone_array['metadata']['completionplan']['inputs']['uuid']); $j++) {
                                echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['completionplan']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['completionplan']['inputs']['name'][$j] . "</a></p>";
                                echo "</p></p>";
                            }
                        }
                    }
                    */?>

                <p class="p_item">Timeframe: student input as text</p>
                <?php echo isset($milestone_array['metadata']['plantimeframe']['inputs'])? "<div class='div_html'>".$milestone_array['metadata']['plantimeframe']['inputs']."</div>" :""; ?>


                <p class="p_item">
                    Has the student provided a detailed and achievable plan?<b><?php echo isset($milestone_array['metadata']['planproposal']['achievable'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['planproposal']['achievable'] :""?></b>
                </p>

                <p class="p_item">Supervisor comment: </p>
                <?php echo isset($milestone_array['metadata']['planproposal']['comment'])? "<div class='div_html'>".$milestone_array['metadata']['planproposal']['comment']."</div>":""; ?>


                <p class="p_item">
                    Is the timeframe realistic/feasible?<b><?php echo isset($milestone_array['metadata']['planproposal']['realistic'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['planproposal']['realistic']:"" ?></b>
                </p>

                <p class="p_item">Supervisor comment:</p>
                <?php echo isset($milestone_array['metadata']['plantimeframe']['comment'])? "<div class='div_html'>".$milestone_array['metadata']['plantimeframe']['comment']."</div>":""; ?>

            </div>


            <div class = "contentdiv">
                <h4 class = "title_topic">Methodology </h4>

                <p class="p_item">
                    Appropriate to the discipline, has the student identified a research methodology/method and type of data collection, or equivalent, for the project?
                    <b><?php echo isset($milestone_array['metadata']['methodology']['accepted'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['methodology']['accepted'] :""; ?></b>
                </p>

                <p class="p_item">Student input as text:</p>
                <?php echo isset($milestone_array['metadata']['methodology']['inputs']['text'])? "<div class='div_html'>".$milestone_array['metadata']['methodology']['inputs']['text']."</div>":""; ?>


                <?php
                if(isset($milestone_array['metadata']['methodology']['inputs']['uuid'])) {
                    echo "<p class='p_item'>The input as a file:</p>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['methodology']['inputs']['uuid']); $j++) {
                        echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['methodology']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['methodology']['inputs']['name'][$j] . "</a></p>";
                    }
                }
                ?>

                <p class="p_item">Supervisor comment:</p>
                <?php echo isset($milestone_array['metadata']['methodology']['comment'])? "<div class='div_html'>".$milestone_array['metadata']['methodology']['comment']."</div>":""; ?>

            </div>

            <?php if($milestone_array['metadata']['nameid']=='1000' or $milestone_array['metadata']['nameid']=='10' ){?>
            <div class = "contentdiv">
                <h4 class = "title_topic">Ethics (Supervisor question): Has the appropriate ethics approval been sought?</h4>

                <p class="p_item">Student input as text:</p>
                <?php echo isset($milestone_array['metadata']['ethics']['inputs']['text'])? "<div class='div_html'>".$milestone_array['metadata']['ethics']['inputs']['text']."</div>" :""; ?>

                <?php
                if(isset($milestone_array['metadata']['ethics']['inputs']['uuid'])) {
                    echo "<p class='p_item'>Student input as a file: Add text-matching report if appropriate.</p>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['ethics']['inputs']['uuid']); $j++) {
                        echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['ethics']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['ethics']['inputs']['name'][$j] . "</a></p>";
                    }
                }
                ?>

                <p class="p_item">
                    Supervisor assessment-&nbsp;<b><?php echo isset($milestone_array['metadata']['ethics']['accepted'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['ethics']['accepted']:""; ?></b>
                </p>

                <p class="p_item">Supervisor comment:</p>
                <?php echo isset($milestone_array['metadata']['ethics']['comment'])? "<div class='div_html'>".$milestone_array['metadata']['ethics']['comment']."</div>":""; ?>

            </div>
            <?php } ?>


            <div class = "contentdiv">
                <h4 class = "title_topic">Oral Communication </h4>

                <p class="p_item">Student input as text:</p>
                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['oralskill']['inputs']['text'])? $milestone_array['metadata']['oralskill']['inputs']['text']:""; ?>
                </div>

                <?php
                if(isset($milestone_array['metadata']['oralskill']['inputs']['uuid'])) {
                    echo "<p class='p_item'>Student input as a file: </p>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['oralskill']['inputs']['uuid']); $j++) {
                        echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['oralskill']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['oralskill']['inputs']['name'][$j] . "</a></p>";
                    }
                }
                ?>

                <p class="p_item">
                    Supervisor assessment:<b><?php echo isset($milestone_array['metadata']['oralskill']['accepted'])?  '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['oralskill']['accepted'] :""; ?></b>
                </p>

                <p class="p_item">Supervisor comment: </p>
                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['oralskill']['comment'])? $milestone_array['metadata']['oralskill']['comment']:""; ?>
                </div>

            </div>


            <div class = "contentdiv">
                <h4 class = "title_topic">Written Communication </h4>

                <p class="p_item">Student input on text matching of the written work:</p>
                <p class="p_item_content">
                    <?php echo isset($milestone_array['metadata']['writtenskill']['inputs']['text'])?$milestone_array['metadata']['writtenskill']['inputs']['text']:""; ?>
                </p>

                <?php
                if(isset($milestone_array['metadata']['writtenskill']['inputs']['uuid'])) {
                    echo "<p class='p_item'>Student input as a file:</p>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['writtenskill']['inputs']['uuid']); $j++) {
                        echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['writtenskill']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['writtenskill']['inputs']['name'][$j] . "</a></p>";
                    }
                }
                ?>

                <p class="p_item">
                    Supervisor assessment:<b><?php echo isset($milestone_array['metadata']['writtenskill']['accepted'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['writtenskill']['accepted']:""; ?></b>
                </p>

                <p class="p_item">Supervisor comment:</p>
                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['writtenskill']['comment'] )? $milestone_array['metadata']['writtenskill']['comment'] :""; ?>
                </div>

            </div>

            <div class = "contentdiv">
                <h4 class = "title_topic">Text-matching </h4>
                <p class="p_item">Student input:</p>
                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['textmatching']['inputs']['text'])? $milestone_array['metadata']['textmatching']['inputs']['text'] : ""; ?>
                </div>

                <p class="p_item">
                    Has the student’s written submission for this Milestone been subjected to text-matching?
                    <b><?php echo isset($milestone_array['metadata']['textmatching']['subjected'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['textmatching']['subjected']:""; ?></b>
                </p>

                <p class="p_item">
                    Does the student’s written submission comply with the University’s Academic Integrity Policy?
                    <b><?php echo isset($milestone_array['metadata']['textmatching']['integrity'])?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['textmatching']['integrity']:""; ?></b>
                </p>

                <p class="p_item">Supervisor comment:</p>
                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['textmatching']['comment'])? $milestone_array['metadata']['textmatching']['comment']:""; ?>
                </div>

            </div>


            <div class = "contentdiv">
                <h4 class = "title_topic">Goals for next Milestone </h4>
                <?php
                if(isset($milestone_array['metadata']['studentgoal'])){
                    echo "<ol>";
                    $numOfGoal = 1;
                    foreach($milestone_array['metadata']['studentgoal'] as $goal){
                        echo "<li class='li_item'>The information of goal ".$numOfGoal."<ul class='ul_item'>";
                        echo "<li class='li_item'>Time Frame:";
                        if(isset($goal['timeframe']))
                            echo $goal['timeframe']."</li>";
                        else echo "</li>";

                        echo "<li class='li_item'>Goal:";
                        if(isset($goal['goalcontent']))
                            echo $goal['goalcontent']."</li>";
                        else echo "</li>";
                        echo "<li class='li_item'>Comment:";
                        if(isset($goal['comment']))
                            echo $goal['comment']."</li>";
                        else echo "</li>";
                        echo "</ul></li>";
                        $numOfGoal++;
                    }
                    echo "</ol>";
                }
                else
                    echo "<p class='p_item_content'></p>";
                ?>
            </div>

            <?php if($milestone_array['metadata']['nameid']=='3000'){?>
                <div class = "contentdiv">
                    <h4 class = "title_topic">Publishing</h4>
                    <p class="p_item">Student input as text:</p>
                    <div class="div_html">
                        <?php echo isset($milestone_array['metadata']['publish']['inputs']['text'])? $milestone_array['metadata']['publish']['inputs']['text'] :""; ?>
                    </div>

                        <?php
    /*                    if(isset($milestone_array['metadata']['publish']['inputs']['uuid'])) {
                            echo "<p class='p_item_content'><br>Student input as a file: <br><I>Add text-matching report if appropriate.</I><br><ul>";
                            for ($j = 1; $j <= count($milestone_array['metadata']['publish']['inputs']['uuid']); $j++) {
                                echo "<p class='p_item_content'><a href='".$milestone_array['metadata']['publish']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['ethics']['inputs']['name'][$j] . "</a></p>";
                            }
                            echo "</p></p>";
                        }
                        */?><!--

                        <p class='p_item_content'>
                            <p><br>Supervisor assessment-&nbsp;<I>Refer to Ethics, Biosafety and Integrity for more information.</I><b><?php /*echo isset($milestone_array['metadata']['publish']['accepted'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['publish']['accepted']:"" */?></b></p>
                        </p>-->

                    <p class="p_item">Supervisor comment:</p>
                    <div class="div_html">
                        <?php echo isset($milestone_array['metadata']['publish']['comment'])? $milestone_array['metadata']['publish']['comment']:""; ?>
                    </div>

                </div>
            <?php } ?>


            <div class = "contentdiv">
                <h4 class = "title_topic">Supervisor</h4>
                <p class="p_item">
                    Recommendation:
                    <b><?php echo isset($milestone_array['metadata']['supervisor']['recommendation'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['supervisor']['recommendation'] :""; ?></b>
                </p>

                <?php
                if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Satisfactory') == 0){
                    echo "<p class='p_item'>Supervisor comment: </p><div class=\"div_html\">";
                    echo isset($milestone_array['metadata']['supervisor']['recommendationcomment'])? $milestone_array['metadata']['supervisor']['recommendationcomment']: "";
                    echo "</div>";
                }
                if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Resubmit') == 0){
                    echo "<p class='p_item'>Supervisor judgement: Student to resubmit the milestone following completion of further work. 
                                Specify additional work required, including a timeframe for the work to be resubmitted.</p><div class=\"div_html\">";
                    echo isset($milestone_array['metadata']['supervisor']['resubmit'])? $milestone_array['metadata']['supervisor']['resubmit']: "";
                    echo "</div>";
                }
                if (strnatcasecmp($milestone_array['metadata']['supervisor']['recommendation'], 'Show cause') == 0){
                    echo "<p class='p_item'>Supervisor judgement: student required to show cause.</p><div class=\"div_html\">";
                    echo isset($milestone_array['metadata']['supervisor']['showcause'])? $milestone_array['metadata']['supervisor']['showcause']: "";
                    echo "</div>";
                }
                ?>

            </div>
        </div>
    </div>

    <div class = "sectiondiv">
        <div class="sectionheaddiv">
            <h2 class = "sectionhead">&nbsp;C. &nbsp;&nbsp;&nbsp;&nbsp;MODERATION</h2>
        </div>
        <div class="sectioncontentdiv">
            <div class = "contentdiv">
                <h4 class = "title_topic">MOD: Postgrad Coordinator</h4>
                <p class="p_item">
                    Supported by Postgraduate Coordinator:
                    <b><?php echo isset($milestone_array['metadata']['mod']['coordinatorsupport'])? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['coordinatorsupport'] :""; ?></b>
                </p>

                <p class="p_item">Postgrad Coordinator comment: </p>
                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['mod']['coordinatorcomment']) ? $milestone_array['metadata']['mod']['coordinatorcomment'] : ""; ?>
                </div>

            </div>

            <div class = "contentdiv">
                <h4 class = "title_topic">MOD: Student</h4>
                <p class="p_item">
                    Student signoff: I have noted and support/do not support this assessment
                    <b><?php echo isset($milestone_array['metadata']['mod']['studentaccepted'] ) ? '&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['studentaccepted'] :""; ?></b>
                </p>

                <p class="p_item">Student comment</p>
                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['mod']['studentcomment'])? $milestone_array['metadata']['mod']['studentcomment']:""; ?>
                </div>

            </div>

            <div class = "contentdiv">
                <h4 class = "title_topic">MOD: Faculty</h4>
                <p class="p_item">
                    Faculty determination
                    <b><?php echo isset($milestone_array['metadata']['mod']['facultydetermination']) ?'&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['mod']['facultydetermination'] :""; ?></b>
                </p>

                <p class="p_item">Faculty comment</p>
                <div class="div_html">
                    <?php echo isset($milestone_array['metadata']['mod']['facultycomment'])? $milestone_array['metadata']['mod']['facultycomment']:""; ?>
                </div>

            </div>
        </div>
    </div>

</body>
</html>