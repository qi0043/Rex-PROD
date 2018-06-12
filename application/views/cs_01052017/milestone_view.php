<!DOCTYPE html>
<!-- saved from url=(0104)https://flextra.flinders.edu.au/rex/rhd/showmilestone/index/html/dad5adbb-afcf-456d-9ca4-5a8b6b559519/1/ -->
<html lang="en-au">
<?php
	$post_wording = '';
	if($milestone_array['metadata']['name'] == 'Interim Review')
	{
		if(isset($milestone_array['metadata']['nameid']) && $milestone_array['metadata']['nameid']!= '')
		{
			if(intval($milestone_array['metadata']['nameid']) >= 3010)
			{
				$post_wording = '&nbsp;&mdash;&nbsp;post-Final';
			}
			else if(intval($milestone_array['metadata']['nameid']) >= 2010 && intval($milestone_array['metadata']['nameid']) < 3000)
			{
				$post_wording = '&nbsp;&mdash;&nbsp;pre-Final';
			}
			else if(intval($milestone_array['metadata']['nameid']) >= 1010 && intval($milestone_array['metadata']['nameid']) < 2000)
			{
				$post_wording = '&nbsp;&mdash;&nbsp;pre-Mid';
			}
			else if(intval($milestone_array['metadata']['nameid']) >= 10 && intval($milestone_array['metadata']['nameid']) < 1000)
			{
				$post_wording = '&nbsp;&mdash;&nbsp;pre-Confirmation';
			}
			else
			{
				$post_wording = $milestone_array['metadata']['nameid'];
			}
		}
	}
 
?>
<head>
    <meta charset="UTF-8" />

    <!-- Styles -->
    <style type="text/css">
        body {
            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
            background-color: white;
            color: black;
            font-size: 1em;
        }

        h1{
            text-align:center;
            font-size: 1.5em;
        }

        td {
            vertical-align: top;
        }

        .table_content tr,
        .table_content td,
        .table_content {
            margin-bottom: 1em;
            margin-top: 0em;
            padding: 0.5em;
            padding-left: 0em;
            outline: 0;
            border-spacing: 0;
            text-align: left;
        }

        .sectionhead {
            color: white;
            background-color: black;
            padding: 0.75em;
            font-size: 1em;
        }

        .sectioncontentdiv {
            padding: 0.75em;
            padding-top: 0em;
        }

        .contentdiv {
            padding: 0.75em;
            padding-top: 0.5em;
        }

        .title_topic {
            color: black;
            background-color: gainsboro;
            padding: 0.75em;
            font-size: 1em;
        }

        .li_item,
        .ul_item {
            list-style:none;
        }

        .p_item {
            font-size: medium;
            line-height: 150%;
            padding-top: 1.2em;
        }

        .question {
            color: LimeGreen;
        }

        .comments {
            padding-left: 3em;
        }

        .importantText{
            font-size: large;
            color:red;
            float: right;
        }
    </style>

    <title><?php echo isset($milestone_array['metadata']['name'])?$milestone_array['metadata']['name']:""; ?> <?php echo $post_wording;?></title>
</head>

<body id="bodytag">
	<?php
    $refValue = new DateTime("now");
    $displayDate = $refValue->format('c');
    ?>
    <p style="font-size: smaller; color:DimGrey; float: left">Flinders University:
        <?php if($milestone_array['status'] == 'live' && isset($milestone_array['metadata']['approval_date']))
        {
            echo '<i>approved on&nbsp;'. $milestone_array['metadata']['approval_date']. '</i>';
        }
        else
        {
            echo '<i>generated on&nbsp;' . $displayDate.'</i>';
        }?>
        <span class="importantText">
               <?php  $status = $milestone_array['status'];
               if($status == "draft" || $status == "rejected" || $status == "moderating"){
                   echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DRAFT ONLY';
               }else echo '';
               ?>
        </span>
	</p>

  <h1>
  <?php echo isset($milestone_array['metadata']['candidatename'])?$milestone_array['metadata']['candidatename']: "";?>
  <br /> 
  <?php echo isset($milestone_array['metadata']['name'])?$milestone_array['metadata']['name']. $post_wording: "";?>

  </h1>

  	<?php if($milestone_array['metadata']['studentsignoff'] != 'Yes')
	{ ?>
		<p style="text-align:center; color: red">
		Note: the student has not signed off on their input yet.
		</p>
	<?php }?>

    <div class="sectiondiv">
        <div class="sectionheaddiv">
            <h2 class="sectionhead">&nbsp;A. &nbsp;&nbsp; STUDENT DETAILS</h2>
        </div>
        <div class="sectioncontentdiv">
            <table class="table_content">
                <tbody>
                    <tr>
                        <td>
                            Student Name:
                        </td>
                        <td><?php echo isset($milestone_array['metadata']['candidatename'])?$milestone_array['metadata']['candidatename']:"";?></td>
                    </tr>
                    <tr>
                        <td>
                            Student ID:
                        </td>
                        <td><?php echo isset($milestone_array['metadata']['studentid'])?$milestone_array['metadata']['studentid']:""; ?></td>
                    </tr>
                    <tr>
                        <td>Student FAN:</td>
                        <td><?php echo isset($milestone_array['metadata']['studenfan'])? $milestone_array['metadata']['studenfan']:""; ?></td>
                    </tr>
                    <tr>
                        <td>Degree:</td>
                        <td><?php echo isset($milestone_array['metadata']['degree'])? $milestone_array['metadata']['degree']:""; ?></td>
                    </tr>
                    <tr>
                        <td>Topic Code:</td>
                        <td><?php echo isset($milestone_array['metadata']['topiccode'])? $milestone_array['metadata']['topiccode']:""; ?></td>
                    </tr>
                    <tr>
                        <td>School or Dept/Unit:</td>
                        <td><?php echo isset($milestone_array['metadata']['schoolname'])? $milestone_array['metadata']['schoolname']:""; ?></td>
                    </tr>
                    <tr>
                        <td>
                            Supervisors:
                        </td>
                        <td> <?php
                            if(isset($milestone_array['metadata']['supervisorsdetail'])){
                                echo "<ul class='ul_item'>";
                                $supervisorsList = $milestone_array['metadata']['supervisorsdetail'];
                                foreach ($supervisorsList as $supervisor){
                                    echo "<li class='li_item'>". $supervisor['role'] . ": " . $supervisor['title'] . "&nbsp;" . $supervisor['name'] . "</li>";
                                }
                                echo "</ul>";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Full/Part Time:</td>
                        <td><?php echo isset($milestone_array['metadata']['fullorparttime'])? $milestone_array['metadata']['fullorparttime']:""; ?></td>
                    </tr>
                    <tr>
                        <td>Start Date:
                        </td>
                        <td><?php echo (isset($milestone_array['metadata']['commercedate']) && $milestone_array['metadata']['commercedate'] != '')? date_format(date_create($milestone_array['metadata']['commercedate']), 'j M Y'):""; ?></td>
                    </tr>
                    <tr>
                        <td>RTP fee offset end date:</td>
                        <td><?php echo (isset($milestone_array['metadata']['rtsenddate']) && isset($milestone_array['metadata']['rtscategory'])) ? $milestone_array['metadata']['rtscategory']. '&nbsp;&mdash;&nbsp;' . date_format(date_create($milestone_array['metadata']['rtsenddate']), 'j M Y'):""; ?></td>
                    </tr>
                    <tr>
                        <td>Total EFTSL: </td>
                        <td> <?php echo isset($milestone_array['metadata']['eftsl'])?$milestone_array['metadata']['eftsl']:""; ?></td>
                    </tr>
                    <tr>
                        <td>Intermissions: </td>
                        <td>
                        <?php 
							if(isset($milestone_array['metadata']['intermission']) && $milestone_array['metadata']['intermission']['status'] == 'Yes')
							{
								for($i = 0; $i< count($milestone_array['metadata']['intermission']); $i++)
								{
									if(isset($milestone_array['metadata']['intermission'][$i]['start']) && $milestone_array['metadata']['intermission'][$i]['start'] != '')
									{
										if($i >1)
										{
											echo '<br/>';
										}
										echo date_format(date_create($milestone_array['metadata']['intermission'][$i]['start']), 'j M Y') .'&nbsp;&mdash;&nbsp;' . date_format(date_create($milestone_array['metadata']['intermission'][$i]['end']), 'j M Y');
										echo '<div style= "text-indent:50px;">' . $milestone_array['metadata']['intermission'][$i]['reason'] . '</div>';
									}
								}
							}else
							    echo 'nil';
						?>
                      
                        </td>
                    </tr>
            </table>
        </div>
    </div>
    <div class="sectiondiv">
        <div class="sectionheaddiv">
            <h2 class="sectionhead">&nbsp;B. &nbsp;&nbsp; RESEARCH DETAILS</h2>
        </div>
        <div class="sectioncontentdiv">
            <table class="table_content">
                <tbody>
                    <tr>
                        <td>Thesis Title:</td>
                        <td><?php echo isset($milestone_array['metadata']['thesistitle'])?$milestone_array['metadata']['thesistitle']:''; ?></td>
                    </tr>
                    <tr>
                        <td>Expected Submission Date:</td>
                        <td><?php echo (isset($milestone_array['metadata']['submissiondate']) && $milestone_array['metadata']['submissiondate'] != '')? date_format(date_create($milestone_array['metadata']['submissiondate']), 'j M Y'):""; ?></td>
                    </tr>
                    <tr>
                        <td>
                            Ethics Approval:
                        </td>
                        <td>
                        <?php
							switch ($milestone_array['metadata']['ethics']['status'])
							{
								case 'N/A':
									echo $milestone_array['metadata']['ethics']['status'];
								break;
								
								case 'Not applied yet':
									echo $milestone_array['metadata']['ethics']['status'];
								break;
								case 'Approved':
								    echo 'Approved &nbsp;';
									if(isset($milestone_array['metadata']['ethics']['number']) && $milestone_array['metadata']['ethics']['number']!= '')
									{
										echo $milestone_array['metadata']['ethics']['number'] . ' &mdash; ';
									}
									else
									{
										echo '<i>Not provided - </i>';
									}
									
									if(isset($milestone_array['metadata']['ethics']['committee']) && $milestone_array['metadata']['ethics']['committee']!= '')
									{
										echo $milestone_array['metadata']['ethics']['committee'];
									}
									else
									{
										echo '<i>Not provided </i>';
									}
								break;
								
								case 'Applied':
									if(isset($milestone_array['metadata']['ethics']['committee']) && $milestone_array['metadata']['ethics']['committee']!= '')
									{
										echo 'Applied &nbsp;&mdash;&nbsp;' . $milestone_array['metadata']['ethics']['committee'];
									}
									else
									{
										echo '<i>Applied &nbsp;&mdash;&nbsp; Not provided</i>';
									}
									
								break;
								
								
								default:
									echo '<i>Not provided</i>';
								break;
								
							}
						?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Export Control:
                        </td>
                        <td><?php echo isset($milestone_array['metadata']['exportcontrol'])&&$milestone_array['metadata']['exportcontrol'] !='' ?$milestone_array['metadata']['exportcontrol']:'<i>Not provided</i>'; ?> </td>
                    </tr>
                    <tr>
                        <td>
                            Formal, Third-party Agreement:
                        </td>
                        <td> <?php echo isset($milestone_array['metadata']['tpagreement'])&& $milestone_array['metadata']['tpagreement']!= ''?$milestone_array['metadata']['tpagreement']:'<i>Not provided</i>'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="sectiondiv">
        <div class="sectionheaddiv">
            <h2 class="sectionhead">&nbsp;C. &nbsp;&nbsp;&nbsp;&nbsp;ASSESSMENT CRITERIA</h2>
        </div>
        <div class="sectioncontentdiv">

            <!--- Progress Report --->
            <div class="contentdiv">
                <h3 class="title_topic">Progress Report</h3>
                <p class="p_item"><span class="question">Student input:</span> Documentation to support milestone determination</p>
                <?php
                if(isset($milestone_array['metadata']['overallreport']) || isset($milestone_array['metadata']['overallreport']['uuid'])) {
                    echo "<div class=\"comments\"><ul>";
                    for ($j = 1; $j <= count($milestone_array['metadata']['overallreport']['uuid']); $j++) {
                        echo "<li><a href='".$milestone_array['metadata']['overallreport']['link'][$j]."'>" . $milestone_array['metadata']['overallreport']['name'][$j] . "</a></li>";
                    }
                    echo "</ul></div>";
                }else
                    echo "<div class=\"comments\"><i> No input provided</i></div>";
                ?>
            </div>

            <!--- Professional Development --->
            <div class="contentdiv">
                <h3 class="title_topic">Professional Development </h3>
                <p class="p_item">
                    <span class="question">Student question A:</span> Have you completed any professional development activities since your last review? &nbsp; <?php echo isset($milestone_array['metadata']['professionaldevelopment']['completed']) && !empty($milestone_array['metadata']['professionaldevelopment']['completed'])? '<b>&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['professionaldevelopment']['completed']. '</b>' : '<i>&nbsp;&nbsp;&nbsp;&nbsp;No input provided</i>'; ?>
                </p>
                	<div class="comments"><?php
                    if (strnatcasecmp($milestone_array['metadata']['professionaldevelopment']['completed'], 'Yes') == 0){
                        if(empty($milestone_array['metadata']['professionaldevelopment']['comment']) && !isset($milestone_array['metadata']['professionaldevelopment']['inputs']['uuid'])){
                            echo "<i>No input provided</i>";
                        }else {
                            echo !empty($milestone_array['metadata']['professionaldevelopment']['comment']) ? $milestone_array['metadata']['professionaldevelopment']['comment'] : "";
                            if (isset($milestone_array['metadata']['professionaldevelopment']['inputs']['uuid'])) {
                                echo "<ul>";
                                for ($j = 1; $j <= count($milestone_array['metadata']['professionaldevelopment']['inputs']['uuid']); $j++) {
                                    echo "<li><a href='" . $milestone_array['metadata']['professionaldevelopment']['inputs']['link'][$j] . "'>" . $milestone_array['metadata']['professionaldevelopment']['inputs']['name'][$j] . "</a></li>";
                                }
                                echo "</ul>";
                            }
                        }
                    }?>
                </div>
                <p class="p_item">
                    <span class="question">Student question B:</span> Do you require additional education and/or training to ensure timely completion? &nbsp; <?php echo isset($milestone_array['metadata']['additionaleducation']['requested']) && !empty($milestone_array['metadata']['additionaleducation']['requested'])? '<b>&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['additionaleducation']['requested']. '</b>' : '<i>&nbsp;&nbsp;&nbsp;&nbsp;No input provided</i>'; ?>
                </p>
                <div class="comments">
                	<?php
                    if (strnatcasecmp($milestone_array['metadata']['additionaleducation']['requested'], 'Yes') == 0){
                         if(!empty($milestone_array['metadata']['additionaleducation']['inputs']['text']) || isset($milestone_array['metadata']['additionaleducation']['inputs']['uuid'])) {
                            echo isset($milestone_array['metadata']['additionaleducation']['inputs']['text']) && !empty($milestone_array['metadata']['additionaleducation']['inputs']['text']) ? $milestone_array['metadata']['additionaleducation']['inputs']['text'] : "<i> No input provided</i>";
                            if (isset($milestone_array['metadata']['additionaleducation']['inputs']['uuid'])) {
                                echo "<ul>";
                                for ($j = 1; $j <= count($milestone_array['metadata']['additionaleducation']['inputs']['uuid']); $j++) {
                                    echo "<li><a href='" . $milestone_array['metadata']['additionaleducation']['inputs']['link'][$j] . "'>" . $milestone_array['metadata']['additionaleducation']['inputs']['name'][$j] . "</a></li>";
                                }
                                echo "</ul>";
                            }
                         }else
                            echo "<i> No input provided</i>";
                    }
                    ?>
                </div>
                <p class="p_item"><b>Supervisor response</b></p>
                <div class="comments">
                    <?php echo (isset($milestone_array['metadata']['additionaleducation']['comment']) && !empty($milestone_array['metadata']['additionaleducation']['comment']))? $milestone_array['metadata']['additionaleducation']['comment'] : "<i>No response</i>"; ?>
                </div>
            </div>

            <!--- Resources --->
            <div class="contentdiv">
                <h3 class="title_topic">Resources </h3>
                <p class="p_item">
                    <span class="question">Student question:</span> Do you require additional resources to complete the project, other than those noted at admission? &nbsp; <?php echo isset($milestone_array['metadata']['additionalresource']['requested']) && !empty($milestone_array['metadata']['additionalresource']['requested']) ? '<b>&nbsp;&nbsp;&nbsp;&nbsp;' .$milestone_array['metadata']['additionalresource']['requested'] .'</b>' : '<i>&nbsp;&nbsp;&nbsp;&nbsp;No input provided</i>'?>
                </p>
                <div class="comments">
                	<?php
                    if (strnatcasecmp($milestone_array['metadata']['additionalresource']['requested'], 'Yes') == 0) {
                        if (!empty($milestone_array['metadata']['additionalresource']['inputs']['text']) || isset($milestone_array['metadata']['additionalresource']['inputs']['uuid'])) {
                            echo isset($milestone_array['metadata']['additionalresource']['inputs']['text']) ? $milestone_array['metadata']['additionalresource']['inputs']['text'] : "";
                            if (isset($milestone_array['metadata']['additionalresource']['inputs']['uuid'])) {
                                echo "<ul>";
                                for ($j = 1; $j <= count($milestone_array['metadata']['additionalresource']['inputs']['uuid']); $j++) {
                                    echo "<li><a href='" . $milestone_array['metadata']['additionalresource']['inputs']['link'][$j] . "'>" . $milestone_array['metadata']['additionalresource']['inputs']['name'][$j] . "</a></li>";
                                }
                                echo "</ul>";
                            }
                        } else
                            echo "<i> No input provided</i>";
                    }
                    ?>
                </div>
                <p class="p_item"><b>Supervisor response</b></p>
                <div class="comments"> <?php echo (isset($milestone_array['metadata']['additionalresource']['comment']) && !empty($milestone_array['metadata']['additionalresource']['comment'])) ? $milestone_array['metadata']['additionalresource']['comment']: '<i>No response</i>'; ?>
                </div>
            </div>
            
            <?php 
			if(isset($milestone_array['metadata']['nameid']) && $milestone_array['metadata']['nameid']!= '')
			{
				if(intval($milestone_array['metadata']['nameid']) <= 1000)
				{
		    ?>
                <!--- Research Question --->
                <div class="contentdiv">
                    <h3 class="title_topic">Research Question</h3>
    
                    <p class="p_item">
                        <span class="question">Supervisor question:</span> Has the student articulated a suitable research problem or question(s)?
                    </p>
    
                    <p class="p_item"><b>Student input</b></p>
                    <div class="comments">
                    <?php
                        if(isset($milestone_array['metadata']['researchproblem']['inputs']['uuid']) || !empty($milestoneArray['metadata']['researchproblem']['inputs']['text'])) {
                            echo !empty($milestoneArray['metadata']['researchproblem']['inputs']['text'])? $milestoneArray['metadata']['researchproblem']['inputs']['text']:"";
                            if (isset($milestone_array['metadata']['researchproblem']['inputs']['uuid'])) {
                                echo "<ul>";
                                for ($j = 1; $j <= count($milestone_array['metadata']['researchproblem']['inputs']['uuid']); $j++) {
                                    echo "<li><a href='" . $milestone_array['metadata']['researchproblem']['inputs']['link'][$j] . "'>" . $milestone_array['metadata']['researchproblem']['inputs']['name'][$j] . "</a></li>";
                                }
                                echo "</ul>";
                            }
                        }else
                            echo "<i> No input provided</i>";
						?>
                    
                    </div>
                    
    
                    <p class="p_item"><b>Supervisor response </b></p>
                    <p>
                        <span class="question">Q:</span> Has the student articulated a suitable research problem or question(s)? &nbsp; <?php echo isset($milestone_array['metadata']['researchproblem']['accepted']) && !empty($milestone_array['metadata']['researchproblem']['accepted']) ? '<b>&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['researchproblem']['accepted'].'</b>' : '<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>'; ?>
                    </p>
                    <div class="comments">
                        <?php echo (isset($milestone_array['metadata']['researchproblem']['comment']) && !empty($milestone_array['metadata']['researchproblem']['comment'])) ?$milestone_array['metadata']['researchproblem']['comment']:''; ?>
                    </div>
                </div>
            <?php
				}
			}
			?>

            <!--- Plan --->
            <div class="contentdiv">
                <h3 class="title_topic">Plan </h3>
                <p><span class="question">Supervisor question A:</span> Has the student provided a detailed and achievable plan?</p>
                <div class="comments">That satisfies the requirements for their proposed research project as outlined in Section 17.5 of the
                    <a href="http://www.flinders.edu.au/ppmanual/student/research-higher-degrees.cfm" target="_blank">RHD Policies and Procedures</a>?
                </div>

                <p><span class="question">Supervisor question B:</span> Is the timeframe realistic/feasible?</p>

                <p class="p_item"><b>Student input</b></p>

                <div class="comments">
                <?php
                    if(empty($milestone_array['metadata']['plan']['inputs']['text']) && !isset($milestone_array['metadata']['plan']['inputs']['uuid']))
                        echo "<i> No input provided</i>";
                    else {
                        echo isset($milestone_array['metadata']['plan']['inputs']['text']) && !empty($milestone_array['metadata']['plan']['inputs']['text'])?  $milestone_array['metadata']['plan']['inputs']['text'] : "";

                        if (isset($milestone_array['metadata']['plan']['inputs']['uuid'])) {
                            echo "<ul>";
                            for ($j = 1; $j <= count($milestone_array['metadata']['plan']['inputs']['uuid']); $j++) {
                                echo "<li><a href='" . $milestone_array['metadata']['plan']['inputs']['link'][$j] . "'>" . $milestone_array['metadata']['plan']['inputs']['name'][$j] . "</a></li>";
                            }
                            echo "</ul>";
                        }
                    }
                 ?>
                </div>

                <p class="p_item"><b>Supervisor response </b></p>

                <p>
                    <span class="question">Q-A:</span> Has the student provided a detailed and achievable plan? &nbsp; <?php echo isset($milestone_array['metadata']['plan']['supervisor']['review']) && $milestone_array['metadata']['plan']['supervisor']['review'] != '' ? '<b>&nbsp;&nbsp;&nbsp;&nbsp;'. $milestone_array['metadata']['plan']['supervisor']['review'] . '</b>' : '<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>' ?>
                </p>
                <p class="p_item">
                    <span class="question">Q-B:</span> Is the timeframe realistic/feasible? &nbsp;<?php echo isset($milestone_array['metadata']['plan']['supervisor']['timeframe']) && $milestone_array['metadata']['plan']['supervisor']['timeframe'] != '' ? ' <b>&nbsp;&nbsp;&nbsp;&nbsp;'. $milestone_array['metadata']['plan']['supervisor']['timeframe'] . '</b>' : '<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>' ?>
                </p>
                <div class="comments">
                    <?php echo isset($milestone_array['metadata']['plan']['supervisor']['comment']) && $milestone_array['metadata']['plan']['supervisor']['comment'] != '' ? $milestone_array['metadata']['plan']['supervisor']['comment'] : '' ?>
                </div>
            </div>

            <!--- Methodology --->
            <div class="contentdiv">
                <h3 class="title_topic">Methodology</h3>
                <p class="p_item">
                   <?php 
					if(isset($milestone_array['metadata']['nameid']) && $milestone_array['metadata']['nameid']!= '')
					{
						if(intval($milestone_array['metadata']['nameid']) <= 1010)
						{   $supQuestion = "Appropriate to the discipline, has the student identified a research methodology/method and type of data collection, or equivalent, for the project?";
					?>
                   			<span class="question">Supervisor question:</span> Appropriate to the discipline, has the student identified a research methodology/method and type of data collection, or equivalent, for the project?
                   <?php
						}
				   		else if(intval($milestone_array['metadata']['nameid']) > 1010 && intval($milestone_array['metadata']['nameid']) <3000)
						{   $supQuestion = "Has the data collection (or equivalent) been completed?";
				   ?>
                			<span class="question">Supervisor question:</span> Has the data collection (or equivalent) been completed?
                  <?php
						}
						else if(intval($milestone_array['metadata']['nameid']) >= 3000)
						{   $supQuestion = "Has the student fully defined and developed the argument or fully tested/resolved the hypothesis according to the discipline?";
				  ?> 		<span class="question">Supervisor question:</span> Has the student fully defined and developed the argument or fully tested/resolved the hypothesis according to the discipline?
                  <?php }
					}?>
                </p>

                <p class="p_item"><b>Student input</b></p>
                <div class="comments">
                    <?php
                    if(empty($milestone_array['metadata']['methodology']['inputs']['text']) && !isset($milestone_array['metadata']['methodology']['inputs']['uuid'])){
                        echo "<i> No input provided</i>";
                    }else{
                    ?>
                        <?php echo isset($milestone_array['metadata']['methodology']['inputs']['text']) &&$milestone_array['metadata']['methodology']['inputs']['text']!= '' ? $milestone_array['metadata']['methodology']['inputs']['text']:''; ?>
                         <?php
                        if(isset($milestone_array['metadata']['methodology']['inputs']['uuid'])) {
                            echo "<ul>";
                            for ($j = 1; $j <= count($milestone_array['metadata']['methodology']['inputs']['uuid']); $j++) {
                                echo "<li><a href='".$milestone_array['metadata']['methodology']['inputs']['link'][$j]."'>" . $milestone_array['metadata']['methodology']['inputs']['name'][$j] . "</a></li>";
                            }
                            echo "</ul>";
                        }
                        ?>
                    <?php } ?>
                </div>
                

                <p class="p_item"><b>Supervisor response </b></p>

                <p><span class="question">Q:</span> <?php echo $supQuestion;?> &nbsp;
                 <?php echo isset($milestone_array['metadata']['methodology']['accepted']) && $milestone_array['metadata']['methodology']['accepted']!= '' ? '<b>&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['methodology']['accepted'] . '</b>' :"<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>"; ?>
                </p>
                <div class="comments">
                    <?php echo isset($milestone_array['metadata']['methodology']['comment']) && $milestone_array['metadata']['methodology']['comment'] != '' ? $milestone_array['metadata']['methodology']['comment']:''; ?>
                </div>
            </div>

            <!--- ethics --->

                <div class = "contentdiv">
                    <h3 class="title_topic">Ethics</h3>
                    <p class="p_item">
                        <span class="question">Supervisor question:</span> Has the appropriate ethics approval been sought?
                    </p>

                    <p class="p_item"><b>Ethics approval</b></p>
                    <div class="comments">
                        <?php
                        switch ($milestone_array['metadata']['ethics']['status'])
                        {
                            case 'N/A':
                                echo '<p>Status:&nbsp;&nbsp;<b>'. $milestone_array['metadata']['ethics']['status']. '</b></p>';
                                break;

                            case 'Not applied yet':
                                echo '<p>Status:&nbsp;&nbsp;<b>'. $milestone_array['metadata']['ethics']['status']. '</b></p>';
                                break;

                            case 'Approved':
                                echo '<p>Status:&nbsp;&nbsp;<b>'. $milestone_array['metadata']['ethics']['status']. '</b></p>';

                                if(isset($milestone_array['metadata']['ethics']['committee']) && $milestone_array['metadata']['ethics']['committee']!= '')
                                {
                                    echo '<p>Ethics Committee:&nbsp;&nbsp;'. $milestone_array['metadata']['ethics']['committee']. '</p>';
                                }
                                else
                                {
                                    echo '<p>Ethics Committee:&nbsp;&nbsp;<i>Not provided</i></p>';
                                }

                                if(isset($milestone_array['metadata']['ethics']['number']) && $milestone_array['metadata']['ethics']['number']!= '')
                                {
                                    echo '<p>Approval Number:&nbsp;&nbsp;'. $milestone_array['metadata']['ethics']['number'] . '</p>';
                                }
                                else
                                {
                                    echo '<p>Approval Number:&nbsp;&nbsp;<i>Not provided</i></p>';
                                }

                                break;

                            case 'Applied':
                                echo '<p>Status:&nbsp;&nbsp;<b>'. $milestone_array['metadata']['ethics']['status']. '</b></p>';
                                if(isset($milestone_array['metadata']['ethics']['committee']) && $milestone_array['metadata']['ethics']['committee']!= '')
                                {
                                    echo '<p>Ethics Committee:&nbsp;&nbsp;'. $milestone_array['metadata']['ethics']['committee']. '</p>';
                                }
                                else
                                {
                                    echo '<p>Ethics Committee:&nbsp;&nbsp;<i>Not provided</i></p>';
                                }

                                break;


                            default:
                                echo '<i>Not provided</i>';
                                break;

                        }
                        ?>
                    </div>

                    <p class="p_item"><b>Student input</b></p>
                    <div class="comments">
                        <?php
                        if(empty($milestone_array['metadata']['ethics']['inputs']['text']) && !isset($milestone_array['metadata']['ethics']['inputs']['uuid']))
                            echo "<i> No input provided</i>";
                        else {
                            echo !empty($milestone_array['metadata']['ethics']['inputs']['text']) ?  $milestone_array['metadata']['ethics']['inputs']['text'] : "";

                            if (isset($milestone_array['metadata']['ethics']['inputs']['uuid'])) {
                                echo "<ul>";
                                for ($j = 1; $j <= count($milestone_array['metadata']['ethics']['inputs']['uuid']); $j++) {
                                    echo "<li><a href='" . $milestone_array['metadata']['ethics']['inputs']['link'][$j] . "'>" . $milestone_array['metadata']['ethics']['inputs']['name'][$j] . "</a></li>";
                                }
                                echo "</ul>";
                            }
                        }
                        ?>
                    </div>

                    <p class="p_item"><b>Supervisor response </b></p>
                    <p>
                        <span class="question">Q:</span> Has the appropriate ethics approval been sought?&nbsp; <?php echo (isset($milestone_array['metadata']['ethics']['accepted']) && !empty($milestone_array['metadata']['ethics']['accepted']))? '<b>&nbsp;&nbsp;&nbsp;&nbsp;'. $milestone_array['metadata']['ethics']['accepted']. '</b>':"<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>"; ?>
                    </p>

                    <div class="comments">
                        <?php echo (isset($milestone_array['metadata']['ethics']['comment']) && !empty($milestone_array['metadata']['ethics']['comment']))? $milestone_array['metadata']['ethics']['comment']:""; ?>
                    </div>

                </div>

            <!--- Oral Communication --->
            <div class="contentdiv">
                <h3 class="title_topic">Oral Communication </h3>

                <p><span class="question">Supervisor question:</span> Has the student displayed good oral communication skills?</p>

                <p class="p_item"><b>Student input</b></p>
                <div class="comments">
                    <?php
                        if(empty($milestone_array['metadata']['oralskill']['inputs']['text']) && !isset($milestone_array['metadata']['oralskill']['inputs']['uuid'])){
                            echo "<i> No input provided</i>";
                        }
                        else {
                            echo isset($milestone_array['metadata']['oralskill']['inputs']['text']) ? $milestone_array['metadata']['oralskill']['inputs']['text'] : "";
                            if (isset($milestone_array['metadata']['oralskill']['inputs']['uuid'])) {
                                echo "<ul>";
                                for ($j = 1; $j <= count($milestone_array['metadata']['oralskill']['inputs']['uuid']); $j++) {
                                    echo "<li><a href='" . $milestone_array['metadata']['oralskill']['inputs']['link'][$j] . "'>" . $milestone_array['metadata']['oralskill']['inputs']['name'][$j] . "</a></li>";
                                }
                                echo "</ul>";
                            }
                        }
                    ?>
                </div>

                <p class="p_item"><b>Supervisor response </b></p>

                <p>
                    <span class="question">Q:</span> Has the student displayed good oral communication skills? &nbsp; <?php echo isset($milestone_array['metadata']['oralskill']['accepted']) && $milestone_array['metadata']['oralskill']['accepted']!= ''?  '<b>&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['oralskill']['accepted'] . '</b>' :"<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>"; ?>
                </p>
                <div class="comments">
                    <?php echo isset($milestone_array['metadata']['oralskill']['comment'])? $milestone_array['metadata']['oralskill']['comment']:""; ?>
                </div>
            </div>

            <!--- Written Communication --->
            <div class="contentdiv">
                <h3 class="title_topic">Written Communication </h3>

                <p><span class="question">Supervisor question:</span> Can the student write in an appropriate format and express themselves clearly? </p>

                <p class="p_item"><b>Student input</b></p>
                <div class="comments">
					<?php
                    if(empty($milestone_array['metadata']['writtenskill']['inputs']['text']) && !isset($milestone_array['metadata']['writtenskill']['inputs']['uuid'])){
                        echo "<i> No input provided</i>";
                    }else {
                        echo isset($milestone_array['metadata']['writtenskill']['inputs']['text']) ? $milestone_array['metadata']['writtenskill']['inputs']['text'] : "";
                        if (isset($milestone_array['metadata']['writtenskill']['inputs']['uuid'])) {
                            echo "<ul>";
                            for ($j = 1; $j <= count($milestone_array['metadata']['writtenskill']['inputs']['uuid']); $j++) {
                                echo "<li><a href='" . $milestone_array['metadata']['writtenskill']['inputs']['link'][$j] . "'>" . $milestone_array['metadata']['writtenskill']['inputs']['name'][$j] . "</a></li>";
                            }
                            echo "</ul>";
                        }
                    }
					?>
                </div>

                <p class="p_item"><b>Supervisor response </b></p>

                <p>
                    <span class="question">Q:</span> Can the student write in an appropriate format and express themselves clearly? &nbsp; <?php echo isset($milestone_array['metadata']['writtenskill']['accepted']) && $milestone_array['metadata']['writtenskill']['accepted']!=''? '<b>&nbsp;&nbsp;&nbsp;&nbsp;' . $milestone_array['metadata']['writtenskill']['accepted'] . '</b>':"<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>"; ?>
                </p>
                <div class="comments">
                    <?php echo isset($milestone_array['metadata']['writtenskill']['comment'] )? $milestone_array['metadata']['writtenskill']['comment'] :""; ?>
                </div>
            </div>

            <!--- Publishing --->
            <?php 
			if(isset($milestone_array['metadata']['nameid']) && $milestone_array['metadata']['nameid']!= '')
			{
				if(intval($milestone_array['metadata']['nameid']) >= 3000)
				{
					?>
                    <div class="contentdiv">
                    <h3 class="title_topic">Publishing</h3>
    
                    <p class="p_item">
                        <span class="question">Supervisor question:</span> Have publishing opportunities been discussed?
                    </p>
    
                    <p class="p_item"><b>Student input</b></p>
                    <div class="comments"> <?php echo !empty($milestone_array['metadata']['publish']['inputs']['text'])? $milestone_array['metadata']['publish']['inputs']['text'] : "<i>No input provided</i>"; ?></div>
    
                    <p class="p_item"><b>Supervisor response </b></p>
                    <p>
                        <span class="question">Q:</span> Have publishing opportunities been discussed? &nbsp; <?php echo isset($milestone_array['metadata']['publish']['accepted']) && $milestone_array['metadata']['publish']['accepted']!= '' ? '<b>&nbsp;&nbsp;&nbsp;&nbsp;' . $milestone_array['metadata']['publish']['accepted'].'</b>' :'<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>'; ?>
                    </p>
                    <div class="comments">
                        <?php echo isset($milestone_array['metadata']['publish']['comment']) && !empty($milestone_array['metadata']['publish']['comment'])? $milestone_array['metadata']['publish']['comment']:"<i>No response</i>"; ?>
                    </div>
            <?php 
					}
			}?>

            <!--- Text-matching --->
            <div class="contentdiv">
                <h3 class="title_topic">Text-matching </h3>

                <p><span class="question">Supervisor question A:</span> Has the student’s written submission for this Milestone been subjected to text-matching? </p>
                <p><span class="question">Supervisor question B:</span> Does the student’s written submission comply with the University’s Academic Integrity Policy?</p>

                <p class="p_item"><b>Student input</b></p>
                <div class="comments"> <?php echo !empty($milestone_array['metadata']['textmatching']['inputs']['text'])? $milestone_array['metadata']['textmatching']['inputs']['text'] : "<i>No input provided</i>"; ?></div>

                <p class="p_item"><b>Supervisor response </b></p>

                <p>
                    <span class="question">Q-A:</span> Has the student’s written submission for this Milestone been subjected to text-matching? &nbsp; <?php echo isset($milestone_array['metadata']['textmatching']['subjected']) && $milestone_array['metadata']['textmatching']['subjected']!= ''? '<b>&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['textmatching']['subjected'].'</b>':"<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>"; ?>
                </p>
                <p class="p_item">
                    <span class="question">Q-B:</span> Does the student’s written submission comply with the University’s Academic Integrity Policy? &nbsp; <?php echo isset($milestone_array['metadata']['textmatching']['integrity']) && $milestone_array['metadata']['textmatching']['integrity']!=''? '<b>&nbsp;&nbsp;&nbsp;&nbsp;'.$milestone_array['metadata']['textmatching']['integrity'].'</b>':"<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>"; ?>
                </p>
                <div class="comments"><?php echo isset($milestone_array['metadata']['textmatching']['comment']) && !empty($milestone_array['metadata']['textmatching']['comment'])? $milestone_array['metadata']['textmatching']['comment']:""; ?>
                </div>
            </div>

            <!--- Goals for next Milestone --->
            <div class="contentdiv">
                <h3 class="title_topic">Goals for next Milestone</h3>
                <p><span class="question">Supervisor question:</span> List the recommended goals for the next Milestone Review</p>
                <p class="p_item"><b>Student input</b></p>
                  <?php if(isset($milestone_array['metadata']['studentgoal'][1]['timeframe']) && !empty($milestone_array['metadata']['studentgoal'][1]['timeframe'])){
                        //echo "<div class=\"comments\">";

                        foreach($milestone_array['metadata']['studentgoal'] as $goal){
                            echo "<ul class = 'ul_item'>";
                            echo "<li class='li_item'>". $goal['timeframe'];
                            echo "<ul class= 'ul_item'>";
                            echo "<li class='li_item'>". $goal['goalcontent'] . "</li>";
                            echo "</ul></li>";
                            echo "</ul>";
                        }
                        //echo "</div>";
                    }
                    else
                        echo "<div class=\"comments\"><i> No input provided</i></div>";
                    ?>
                
                <p class="p_item"><b>Supervisor response</b></p>
                
                	 <?php
                     if(isset($milestone_array['metadata']['agreedgoal']) && !empty($milestone_array['metadata']['agreedgoal'][1]['timeframe'])){
                        //echo "<div class=\"comments\">";

                        foreach($milestone_array['metadata']['agreedgoal'] as $goal){
                            echo "<ul class = 'ul_item'>";
                            echo "<li class='li_item'>". $goal['timeframe'];
                            echo "<ul class= 'ul_item'>";
                            echo "<li class='li_item'>". $goal['comment'] . "</li>";
                            echo "</ul></li>";
                            echo "</ul>";
                        }
                        //echo "</div>";
                    }
                     else
                         echo "<div class=\"comments\"><i> No response</i></div>";
                    ?>
            </div>
        </div>
    </div>
    
    <div class="sectiondiv">
        <div class="sectionheaddiv">
            <h2 class="sectionhead">&nbsp;D. &nbsp;&nbsp;&nbsp;&nbsp;RECOMMENDATION</h2>
        </div>
        <div class="sectioncontentdiv">
            <div class="contentdiv">
                <h3 class="title_topic">Principal Supervisor </h3>
                <p class="p_item">
                    <b>Milestone</b>
                </p>
					<?php switch($milestone_array['metadata']['supervisor']['recommendation'])
                    {
                        case 'Satisfactory';
                        echo '<p class="p_item"><b>Satisfactory Progress:</b> Continue with the project presented for this review.</p>';
                        echo '<div class="comments">'.$milestone_array['metadata']['supervisor']['recommendationcomment'].'</div>';
                        break;
                        
                        case 'Resubmit';
                        echo '<p class="p_item"><b>Unsatisfactory Progress: the student must resubmit this Milestone</b> following completion of further work in the timeframe detailed in this report.</p>';
                        echo '<div class="comments">'.$milestone_array['metadata']['supervisor']['resubmit'].'</div>';
                        break;
                        
                        case 'Show cause';
                        echo "<p class=\"p_item\"><b>Unsatisfactory Progress: the student is required to show cause</b>. Refer to the RHD Policies and Procedures 18.1(iii) or (iv) in <a href='http://www.flinders.edu.au/ppmanual/student/research-higher-degrees.cfm' target='_blank'>Research Higher Degrees</a> policy.</p>";
                        echo "<div class=\"comments\">".$milestone_array['metadata']['supervisor']['showcause']."</div>";
                        break;

                        default:
                            echo "<div class=\"comments\"><i>No response</i></div>";
                    }
                    ?>
                    
           		 <?php if($milestone_array['metadata']['supervisor']['recommendation'] == 'Satisfactory' && $milestone_array['metadata']['award_type']=='Masters'){?>
                    <p class="p_item">
                        <b>Course Change</b>
                        <?php if($milestone_array['metadata']['requestPhD'] == 'Masters to PhD')
                                            echo '<br /><i>Note: the student has requested a course change to PhD. </i>' ?>
                    </p>
                    <p class="p_item">
                        Do you recommend that the student be considered by the Faculty for approval for a change of candidature from Masters by Research to PhD ? &nbsp; <b><?php echo isset($milestone_array['metadata']['supervisor']['judgement']) && $milestone_array['metadata']['supervisor']['judgement'] != ''?$milestone_array['metadata']['supervisor']['judgement'] : '' ?></b>
                    </p>
            		<div class="comments"><?php echo isset($milestone_array['metadata']['supervisor']['comment']) && $milestone_array['metadata']['supervisor']['comment'] != ''?$milestone_array['metadata']['supervisor']['comment'] : '' ?>
                    </div>
                <?php } ?>
				<p class="p_item">
                	Signed: 
            		<?php if($milestone_array['status'] != 'draft' && $milestone_array['status'] != 'rejected')
					        echo isset($milestone_array['metadata']['prinsupervisorsdetail']['name'])? '<b>' . $milestone_array['metadata']['prinsupervisorsdetail']['name'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        else
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            		?>
            		Date:
               		<?php if($milestone_array['status'] != 'draft' && $milestone_array['status'] != 'rejected')
                            echo (isset($milestone_array['metadata']['supervisor_approval']['date']) && $milestone_array['metadata']['supervisor_approval']['date'] != '')? date_format(date_create($milestone_array['metadata']['supervisor_approval']['date']), 'j M Y'):""; ?>
            	</p>
               
            </div>
        </div>
    </div>
    
    <?php if($milestone_array['status'] == 'moderating' || $milestone_array['status'] == 'live')
	{
	?>
        <div class="sectiondiv ">
            <div class="sectionheaddiv ">
                <h2 class="sectionhead ">&nbsp;E. &nbsp;&nbsp;&nbsp;&nbsp;SIGN OFF</h2>
            </div>
            <div class="sectioncontentdiv ">
                <div class="contentdiv">
                    <h3 class="title_topic ">Student</h3>
                    <!--<p class="p_item ">If you have any concerns that you would like to raise, then:</p>
                    <ul>
                        <li>You may raise concerns in confidence and independently of this Milestone with the Chair, Faculty Research Higher Degrees Committee, Dean of School, Research Higher Degrees Contact Officers, or any of the student support services.</li>
                        <li>Or, if you wish, you can outline concerns with the Postgraduate Coordinator (Dean of School’s nominee).</li>
                        <li>For further information, refer to: Appendix G: Conciliation and Arbitration Procedures relating to Supervised Higher Degree Research in the <a href="http://www.flinders.edu.au/ppmanual/student/research-higher-degrees.cfm
                                " target="_blank ">Research Higher Degrees</a> policy.</li>
                        <li>For contact details refer to: <a href="http://www.flinders.edu.au/graduate-research/about/academic-support.cfm " target="_blank ">Academic and Administrative support</a></li>
                    </ul>-->
    
                    <p class="p_item ">
                        Have you noted and do you support this assessment? &nbsp;<?php
                        if(isset($milestone_array['metadata']['mod']['studentaccepted']) && $milestone_array['metadata']['mod']['studentaccepted']!=''){
                            if($milestone_array['metadata']['mod']['studentaccepted'] == 'Yes')
                                echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;Yes I do</b>";
                            else
                                echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;No I don't</b>";
                        } else
                            echo '<i>&nbsp;&nbsp;&nbsp;&nbsp;No input provided</i>';
                        ?>
                    </p>
                    <div class="comments">
                        <?php echo isset($milestone_array['metadata']['mod']['studentcomment']) && !empty($milestone_array['metadata']['mod']['studentcomment'])? $milestone_array['metadata']['mod']['studentcomment']: ''; ?>
                    </div>
                    
                    <p class="p_item">
                        Signed: 
                        <?php 
                        echo isset($milestone_array['metadata']['mod']['studentname'])? '<b>'.$milestone_array['metadata']['mod']['studentname'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?>

                        Date:
                        <?php echo (isset($milestone_array['metadata']['mod']['studentdate']) && $milestone_array['metadata']['mod']['studentdate'] != '')? date_format(date_create($milestone_array['metadata']['mod']['studentdate']), 'j M Y'):""; ?>
                    </p>
                </div>
    
                <div class="contentdiv">
                    <h3 class="title_topic ">Postgraduate Coordinator</h3>
                    <?php if($milestone_array['metadata']['supervisor']['recommendation'] == 'Satisfactory' && $milestone_array['metadata']['award_type']=='Masters'){?>
                        <p class="p_item ">
                            Do you support the Milestone and Course change recommendations?
                            <?php
                            if(isset($milestone_array['metadata']['mod']['coordinatorsupport']) && $milestone_array['metadata']['mod']['coordinatorsupport']!=''){
                                if($milestone_array['metadata']['mod']['coordinatorsupport'] == 'Yes')
                                    echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;Yes I do</b>";
                                else
                                    echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;No I don't</b>";
                            } else
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;<i>No response</i>';
                            ?>
                        </p>
                    <?php } else{ ?>
                        <p class="p_item ">
                            Do you support the Milestone recommendation?
                            <?php
                            if(isset($milestone_array['metadata']['mod']['coordinatorsupport']) && $milestone_array['metadata']['mod']['coordinatorsupport']!=''){
                                if($milestone_array['metadata']['mod']['coordinatorsupport'] == 'Yes')
                                    echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;Yes I do</b>";
                                else
                                    echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;No I don't</b>";
                            } else
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;<i>No response</i>';
                            ?>
                        </p>
                    <?php } ?>
                    <div class="comments">
                        <?php echo isset($milestone_array['metadata']['mod']['coordinatorcomment']) && !empty($milestone_array['metadata']['mod']['coordinatorcomment'])? $milestone_array['metadata']['mod']['coordinatorcomment']: ''; ?>
                    </div>
                    <p class="p_item">
                        Signed: 
                        <?php
                        if(isset($milestone_array['metadata']['mod']['coordinatordate']) && $milestone_array['metadata']['mod']['coordinatordate'] != '')
                            echo isset($milestone_array['metadata']['mod']['coordinatorname'])? '<b>'.$milestone_array['metadata']['mod']['coordinatorname'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        else
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        ?>
                        Date:
                        <?php echo (isset($milestone_array['metadata']['mod']['coordinatordate']) && $milestone_array['metadata']['mod']['coordinatordate'] != '')? date_format(date_create($milestone_array['metadata']['mod']['coordinatordate']), 'j M Y'):""; ?>
                    </p>
                </div>
    
                <div class="contentdiv ">
                    <h3 class="title_topic ">Faculty Determination</h3>

                    <?php if($milestone_array['metadata']['requestPhD'] == 'Masters to PhD' || $milestone_array['metadata']['requestPhDsupervisoraccept'] == 'Yes'){?>
                        <p class="p_item ">
                            Does the Faculty approve the course change recommendation? &nbsp;<?php
                            switch($milestone_array['metadata']['mod']['facultyphddetermination']){
                                case 'Yes':
                                    echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;Yes, the recommended change of course to PhD is approved</b>";
                                    break;
                                case 'No':
                                    echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;No, the recommended change of course to PhD is not approved</b>";
                                    break;
                                default:
                                    echo "<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>";
                            }
                            ?>
                        </p>
                        <div class="comments ">
                            <?php echo (isset($milestone_array['metadata']['mod']['facultyphdcomment']) && !empty($milestone_array['metadata']['mod']['facultyphdcomment']))? $milestone_array['metadata']['mod']['facultyphdcomment']: "" ?>
                        </div>
                    <?php } ?>


                    <p class="p_item ">
                        Does the Faculty approve the Milestone recommendation? &nbsp;
                        <?php
                        switch($milestone_array['metadata']['mod']['facultydetermination']){
                            case 'Yes':
                                echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;Yes, the milestone recommendation is approved</b>";
                                break;
                            case 'No':
                                echo "<b>&nbsp;&nbsp;&nbsp;&nbsp;No, the milestone recommendations is not approved</b>";
                                break;
                            default:
                                echo "<i>&nbsp;&nbsp;&nbsp;&nbsp;No response</i>";
                        }
                        ?>
                    </p>
                    <div class="comments">
                        <?php echo (isset($milestone_array['metadata']['mod']['facultycomment']) && !empty($milestone_array['metadata']['mod']['facultycomment']))? $milestone_array['metadata']['mod']['facultycomment']: "" ?>
                    </div>
                    
					<p class="p_item">
                        Signed: 
                        <?php 
                        echo isset($milestone_array['metadata']['mod']['facultyname'])? '<b>'.$milestone_array['metadata']['mod']['facultyname'] . '</b>' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                ?>
               		(on behalf of Chair, Faculty RHD Committee)
                    </p>
                   <p>
                        Date:
                        <?php echo (isset($milestone_array['metadata']['mod']['facultydate']) && $milestone_array['metadata']['mod']['facultydate'] != '')? date_format(date_create($milestone_array['metadata']['mod']['facultydate']), 'j M Y'):""; ?>
                    </p>
                </div>
            </div>
        </div>
        
    <?php }?>
</body>

</html>
