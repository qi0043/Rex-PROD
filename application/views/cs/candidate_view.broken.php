<?php include 'nav.php'; ?>

<?php
if(!isset($this->session->userdata['user_role']) || !isset($this->session->userdata['fan']) || ! isset($this->session->userdata['stu_fan']))
{?>
	<div class="container container-gap">
    	<h3>Session Time Out, Please click <a href="<?php echo base_url('cs/dashboard'); ?>">here</a> to return to the dashboard</h3>
    </div>
<?php
}
elseif($this->session->userdata['user_role'] != 'rhd_stu_and_sup'
    && $this->session->userdata['user_role'] != 'rhd_stu'
    && $this->session->userdata['user_role'] != 'student_supervisor_coordinator'
    && $this->session->userdata['user_role'] != 'student_coordinator'
    && !$this->session->userdata['admin'])

{?>
    <div class="container container-gap">
    	<h3>You do not have permission to view this dashboard</h3>
    </div>
<?php }
else
{?>
<div class="container container-gap">
	<div class="spinner-wave" style="display:none; position:fixed;top:30%; left:40%;z-index:100; overflow:auto">
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <p style="z-index:100;" id="txt_loading">&nbsp;Loading...</p>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Progression</h3>
                </div>
                <?php
					$uuid = '';
					$version = '';
					$milestone_code = '';
					$status = '';


					if(isset($milestones['current_milestone']))
					{
						switch (trim($milestones['current_milestone']))
						{
							case 'Confirmation of Candidature':
								$milestone_code = 'coc';
								break;
							case 'Mid-Candidature Review':
								$milestone_code = 'mcr';
								break;
							case 'Final Thesis Review':
								$milestone_code = 'ftr';
								break;
							case 'Interim Candidature Review':
								$milestone_code = 'ir';
								break;
							case 'Interim Review':
								$milestone_code = 'ir';
								break;
						}


		                if(isset($milestones['milestone']))
						{
						    $milestones['completed_milestone'] = array();
							for($i=1; $i<=count($milestones['milestone']); $i++)
							{
								if(isset($milestones['current_milestone_code']) && $milestones['current_milestone_code'] != '')
								{
									if($milestones['milestone'][$i]['name_id'] == $milestones['current_milestone_code'] && $milestones['milestone'][$i]['status']!= 'deleted')
									{
										$uuid = $milestones['milestone'][$i]['uuid'];
										$version = $milestones['milestone'][$i]['version'];
										$status = $milestones['milestone'][$i]['status'];
									}
								}
								else if(isset($milestones['current_milestone']) && $milestones['current_milestone'] != '' && $milestones['milestone'][$i]['status'] != 'deleted' )
								{
									if($milestones['milestone'][$i]['name'] == $milestones['current_milestone'])
									{
										$uuid = $milestones['milestone'][$i]['uuid'];
										$version = $milestones['milestone'][$i]['version'];
										$status = $milestones['milestone'][$i]['status'];
									}
								}

								if($milestones['milestone'][$i]['status'] == 'live'){
                                    array_push($milestones['completed_milestone'],$milestones['milestone'][$i]);
                                }
							}
						}
					}
					if(count($milestones['completed_milestone'])>0) {
                        ?>
                        <div class="panel-body" style="padding-bottom: 0;">

                            <p class='text-left center-block link-padding'>Completed:&nbsp;&nbsp;&nbsp;&nbsp;
                                <?php
                                $count = 1;
                                for ($i = 0; $i < count($milestones['completed_milestone']); $i++) {
                                    if ($milestones['completed_milestone'][$i]['status'] == 'live') {
                                        $temp_uuid = $milestones['completed_milestone'][$i]['uuid'];
                                        $temp_version = $milestones['completed_milestone'][$i]['version'];
                                        echo "<a class='milestone-link' id=" . $temp_uuid . "/" . $temp_version . ">" . $milestones['completed_milestone'][$i]['name'] . "</a>";
                                        if($count<count($milestones['completed_milestone'])){
                                            echo "<br/>";
                                        }
                                        $count++;
                                    }
                                }

                                ?>
                            </p>
                            <hr class="hr_help"/>
                        </div>

                        <?php
                    }
                ?>
					<div class="panel-body">
						<?php
                        if($uuid != '' && $version != '')
                        { ?>
                        	<div class="detail_div">Milestone: <?php echo isset($milestones['current_milestone']) ? $milestones['current_milestone'] : '<i>not available yet</i>'; ?></div>
                        	<div class="detail_div">Due: <?php echo (isset($milestones['current_milestone_due_date']) && $milestones['current_milestone_due_date'] != '') ? date_format(date_create($milestones['current_milestone_due_date']), 'd M Y') : '<i>not available yet</i>'; ?></div>
                            <div class="row">
                               <div class="col-md-6 col-sm-6 col-xs-6">

                                <?php // if($temp_uuid == $uuid && $temp_version == $version) {$version = 0;}




                                    <p class="text-left center-block link-padding"><a class="milestone-link" id="<?php echo $uuid. "/" .$version;?>">View Milestone</a></p>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6">
                                    <p class="text-center center-block button-padding"> status: <?php echo $status; ?> </p>
                                   </div>
                            </div>
                  <?php }
                        else if(isset($milestones['current_milestone']) && $milestones['current_milestone'] != '' && isset($milestones['current_milestone_code']) && $milestones['current_milestone_code'] != '' && isset($milestones['current_milestone_due_date']) && $milestones['current_milestone_due_date']!='')
                        { ?>
                        	<div class="detail_div">Milestone: <?php echo isset($milestones['current_milestone']) ? $milestones['current_milestone'] : '<i>not available yet</i>'; ?></div>
                        	<div class="detail_div">Due: <?php echo isset($milestones['current_milestone_due_date']) ? date_format(date_create($milestones['current_milestone_due_date']), 'd M Y') : '<i>not available yet</i>'; ?></div>
                            <div>
                                <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "<?php echo $milestone_code; ?>", "stu_id": "<?php echo $student['id']; ?>", "name_id": "<?php echo isset($milestones['current_milestone_code'])?$milestones['current_milestone_code'] : ''; ?>", "due_date": "<?php echo isset($milestones['current_milestone_due_date'])?$milestones['current_milestone_due_date'] : ''; ?>"}'>Create Milestone</button>
                            </div>
                <?php	}
						else
						{?>
							<div class="detail_div">Milestone: <i>not available yet</i></div>
                        	<div class="detail_div">Due: <i>not available yet</i></div>
				<?php		}
				?>

                        <hr class="hr_help" />
                        <?php
                            if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                        ?>
                        <p class="text-right center-block button-padding desktop"> <a class="help_link text-center" data-toggle="collapse" data-target="#milestone-help-desktop"><i class="material-icons dp24">&#xE887;</i></a></p>
                        <p class="text-right center-block button-padding mobile"> <a class="help_link text-center" data-toggle="collapse" data-target="#milestone-help-mobile"><i class="material-icons dp24">&#xE887;</i></a></p>
                            <?php }else {
                                ?>
                                <p class="text-right center-block button-padding desktop"><a
                                            class="help_link text-center" data-toggle="collapse"
                                            data-target="#milestone-help-desktop"><i
                                                class="material-icons dp24">help</i></a></p>
                                <p class="text-right center-block button-padding mobile"><a
                                            class="help_link text-center" data-toggle="collapse"
                                            data-target="#milestone-help-mobile"><i class="material-icons dp24">help</i></a>
                                </p>
                                <?php
                            }
                        ?>

               			<div id="milestone-help-mobile" class="collapse">
               			 <?php include 'progression_help_a.php'; ?>
                         <?php include 'progression_help_b.php'; ?>
            			</div>
        			</div>
            	</div>
        </div>


        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Examination</h3>
                </div>
                <div class="panel-body">
                	<div class="detail_div">Expected Submission Date : <?php echo isset($student['est_submit_date']) ? date_format(date_create($student['est_submit_date']), 'd M Y') : ''; ?></div>
                    <div class="detail_div"> &nbsp;&nbsp;  </div>

                	 <?php if(isset($examination) && isset($examination['status']))
        			{
						$status = '';
						switch($examination['status'])
						{
							case 'draft':
							$status = 'draft';
							break;

							case 'moderating':
							$status = 'being check';
							break;

							case 'live':
							$status = 'under examination';
							break;

							case 'rejected':
							$status = 'update required';
							break;

							case 'deleted':
							$status = '';
							break;

						}

					?>

                    	<div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <p class="text-left center-block link-padding"><a class="milestone-link" id="<?php echo $examination['uuid']. "/" .$examination['version'];?>">View Submission</a></p>
                            </div>

                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <p class="text-center center-block button-padding"> status: <?php echo $status; ?> </p>
                            </div>
                        </div>
                	<?php }
					else
					{ ?>
                        <div>
                        	<button class='lead text-center btn-primary btn text-info center-block create_examination' data-button='{"stu_id": "<?php echo $student['id']; ?>"}'> Submit Thesis </button>
                        </div>


                    <?php }
					 ?>

                    <div>
                        <hr class="hr_help" />
                        <?php
                        if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                            ?>
                            <p class="text-right center-block button-padding desktop"> <a class="help_link text-center" data-toggle="collapse" data-target="#milestone-help-desktop"><i class="material-icons dp24">&#xE887;</i></a></p>
                            <p class="text-right center-block button-padding mobile"> <a class="help_link text-center" data-toggle="collapse" data-target="#milestone-help-mobile"><i class="material-icons dp24">&#xE887;</i></a></p>
                        <?php }else {
                            ?>
                            <p class="text-right center-block button-padding desktop"><a
                                        class="help_link text-center" data-toggle="collapse"
                                        data-target="#exam-help-desktop"><i
                                            class="material-icons dp24">help</i></a></p>
                            <p class="text-right center-block button-padding mobile"><a
                                        class="help_link text-center" data-toggle="collapse"
                                        data-target="#exam-help-mobile"><i class="material-icons dp24">help</i></a>
                            </p>
                            <?php
                        }
                        ?>

                    </div>

                    <div id="exam-help-mobile" class="collapse">

                         <?php include 'examination_help_a.php'; ?>
                         <?php include 'examination_help_b.php'; ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">My Details</h3>
                </div>
                <div class="panel-body">
                	<div class="detail_div">Thesis Title: <?php echo isset($student['thesis_title']) ? $student['thesis_title'] : ''; ?></div>

                    <div>
                        <hr class="hr_help" />
                        	<p class="text-right center-block button-padding desktop"> <a class="help_link text-center" data-toggle="collapse" data-target="#roles-desktop">More Details</a></p>
                        	<p class="text-right center-block button-padding mobile"> <a class="help_link text-center" data-toggle="collapse" data-target="#roles-mobile">More Details</a></p>

                    </div>

                    <div id="roles-mobile" class="collapse">

                             <div>Supervisors:
                                <ul>
                                    <?php for($i=0; $i<count($student['supervisors']); $i++)
                                          {
                                              echo "<li>" .$student['supervisors'][$i]['supervisor_name']. "&nbsp;::&nbsp;" . $student['supervisors'][$i]['supv_role'] ."</li>";
                                        }?>
                                </ul>
                            </div>


                            <div class="detail_div">RHD Coordinator: <?php echo isset($student['postgra_coord'][0]['fan']) ? $student['postgra_coord'][0]['first_name'] . ' ' . $student['postgra_coord'][0]['last_name'] : ''; ?></div>
                            <div class="detail_div">RHD Admin: </div>
                            <hr/>
                            <div class="detail_div">Enrolment:
                                <ul>
                                    <li> Degree: <?php echo $student['course_code'] . ' ' . $student['course_title'];?></li>
                                    <li> Candidature start date: <?php echo (isset($student['start_date']) && $student['start_date']!= '') ? date_format(date_create($student['start_date']), 'd M Y') : '';?></li>
                                    <li> FT or PT: <?php echo $student['load'];?></li>
                                    <li> Student Type: <?php echo $student['residency'];?></li>
                                    <li> RTP fee offset end date: <?php echo $student['liability_category'] . ' - ' . date_format(date_create($student['fec_date']), 'd M Y');?></li>
                                </ul>
                            </div>

                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="col-md-12">
        <div class="row">
            <div id="milestone-help-desktop" class="collapse desktop">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Help - Progression</h3>
                    </div>

                    <div class="panel-body">
                        <div class="col-md-6">
                            <?php include 'progression_help_a.php'; ?>
                        </div>
                        <div class="col-md-6">
                            <?php include 'progression_help_b.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="exam-help-desktop" class="collapse desktop">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Help - Examination</h3>
                    </div>

                    <div class="panel-body">
                        <div class="col-md-6">
                            <?php include 'examination_help_a.php'; ?>
                        </div>
                        <div class="col-md-6">
                            <?php include 'examination_help_b.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="roles-desktop" class="collapse desktop">
                 <div>Supervisors:
                                <ul>
                                    <?php for($i=0; $i<count($student['supervisors']); $i++)
                                          {
                                              echo "<li>" .$student['supervisors'][$i]['supervisor_name']. "&nbsp;::&nbsp;" . $student['supervisors'][$i]['supv_role'] ."</li>";
                                        }?>
                                </ul>
                            </div>


                            <div class="detail_div">RHD Coordinator: <?php echo isset($student['postgra_coord'][0]['fan']) ? $student['postgra_coord'][0]['first_name'] . ' ' . $student['postgra_coord'][0]['last_name'] : ''; ?></div>
                            <div class="detail_div">RHD Admin: </div>

                            <div class="detail_div">Enrolment:
                                <ul>
                                    <li> Degree: <?php echo $student['course_code'] . ' ' . $student['course_title'];?></li>
                                    <li> Candidature start date: <?php echo date_format(date_create($student['start_date']), 'd M Y');?></li>
                                    <li> FT or PT: <?php echo $student['load'];?></li>
                                    <li> Student Type: <?php echo $student['residency'];?></li>
                                    <li> RTP fee offset end date: <?php echo $student['liability_category'] . '-' . date_format(date_create($student['fec_date']), 'd M Y');?></li>
                                </ul>
                            </div>
            </div>
        </div>
    </div>
</div>
<?php
}
include 'footer.php';
?>
