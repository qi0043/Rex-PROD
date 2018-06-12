<?php include 'nav.php'; ?>
<?php
if(!isset($this->session->userdata['user_role']) || !isset($this->session->userdata['fan']))
{?>
	<div class="container container-gap">
    	<h3>Session Time Out, Please click <a href="<?php echo base_url('cs/dashboard'); ?>">here</a> to return to the dashboard</h3>
    </div>
<?php 
}
elseif($this->session->userdata['user_role'] != 'rhd_stu_and_sup' && $this->session->userdata['user_role'] != 'rhd_sup'
    && $this->session->userdata['user_role'] != 'supervisor_coordinator'
    && $this->session->userdata['user_role'] != 'student_supervisor_coordinator'
    && !$this->session->userdata['admin'])
{?>
	<div class="container container-gap">
    	<h3>You do not have permission to view this dashboard</h3>
    </div>
<?php }
else
{?>
<?php $user_role = $this->session->userdata['user_role']; 
	  $user_fan = $this->session->userdata['fan']; 
	
/*echo '<pre>';
print_r($this->session->userdata);
echo '</pre>';*/
			
?>
<div class="container container-gap">
    <div class="col-md-12">
        <div class="row">
            <div id="milestone-help-desktop" class="collapse desktop">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Help - Progression</h3>
                    </div>

                    <div class="panel-body">
                        <div class="row">
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
</div>

<div class="container-fluid">
    <div class="row">
	    <div class="col-md-12">
    	<?php if($supervisor['students'] != 0)
    	{?>
            <div class="panel panel-default">
                <div class="panel-heading">Progression &nbsp;:: &nbsp;<?php echo $supervisor['given_name'].' '.$supervisor['family_name']; ?> &nbsp; &nbsp;
                    <?php
                    if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                        ?>
                        <a class="help_link text-center" data-toggle="collapse" data-target="#milestone-help-desktop"><i class="material-icons" style="font-size: 24px">&#xE887;</i></a>
                    <?php }else {
                        ?>
                        <a class="help_link text-center" data-toggle="collapse" data-target="#milestone-help-desktop"><i class="material-icons" style="font-size: 24px">help</i></a>
                        <?php
                    }
                    ?>

                    </div>
                <div class="panel-body">
                    
                        <ul class="panel-ul">
                        <?php 
							foreach($supervisor['students'] as $student)
                            {
                                if($student['stusys_active'] == 't')
                                {
                                    $uuid = '';
                                    $version = '';
                                    $milestone_code = '';
                                    $status = '';
                                    $stu_sign_off = '';
                                    if(isset($student['milestones']['milestone']))
                                    {
                                        /*if(isset($milestones['current_milestone']))
                                        {
                                            switch ($milestones['current_milestone'])
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
                                                case 'Interim Milestone':
                                                    $milestone_code = 'ir';
                                                    break;
                                                default:
                                                    $milestone_code = '';
                                                    break;
                                            }
                                            
                                        }*/
                        
                                        for($j=1; $j<=count($student['milestones']['milestone']); $j++)
                                        {
                                            if(isset($student['milestones']['current_milestone_code']) && $student['milestones']['current_milestone_code'] != '')
                                            {
                                                if($student['milestones']['milestone'][$j]['name_id'] == $student['milestones']['current_milestone_code'])
                                                {
                                                    $uuid = $student['milestones']['milestone'][$j]['uuid'];
                                                    $version = $student['milestones']['milestone'][$j]['version'];
                                                    $status = $student['milestones']['milestone'][$j]['status'];
													$stu_sign_off = $student['milestones']['milestone'][$j]['stu_sign_off'];
                                                }
                                            }
                                            else if(isset($student['milestones']['current_milestone']) && $student['milestones']['current_milestone'] != '')
                                            {
                                                if($student['milestones']['milestone'][$j]['name'] == $student['milestones']['current_milestone'])
                                                {
                                                    $uuid = $student['milestones']['milestone'][$j]['uuid'];
                                                    $version = $student['milestones']['milestone'][$j]['version'];
                                                    $status = $student['milestones']['milestone'][$j]['status'];
													$stu_sign_off = $student['milestones']['milestone'][$j]['stu_sign_off'];
                                                }
                                            }
                                        }
                                    }
                                ?>
                                    <div class = "row">
                                <li class="panel-li">

                                    <div class="col-sm-2 col-md-2">
                                        <?php echo $student['family_name'] . ' , ' .$student['given_name'] . '<br/>'; ?>
                                    </div>
                                    
                                    <div class="col-sm-3 col-md-3">
                                        <?php if(isset($student['milestones']['milestone']) && $student['milestones']['milestone'] != ''){
                                            foreach ($student['milestones']['milestone'] as $stu_milestone) {
                                                if ($stu_milestone['uuid'] != '' && $stu_milestone['version'] != '') {
                                        ?>
                                        <a class="milestone-link" id="<?php echo $stu_milestone['uuid'] . "/" . $stu_milestone['version']; ?>"><?php echo $stu_milestone['name']; ?></a>
                                            <?php } else {
                                                echo $stu_milestone['name'];
                                            }
                                            echo '<br/>';
                                            }
                                            }
                                            else{
                                            echo '';
                                            }
                                            ?>
                                        </div>

                                        <div class="col-sm-3 col-md-3">
                                            <?php
                                            if(isset($student['milestones']['milestone']) && $student['milestones']['milestone'] != ''){
                                                foreach ($student['milestones']['milestone'] as $stu_milestone) {
                                                    if ($stu_milestone['status'] != '' ) {
                                                        switch ($stu_milestone['status']) {
                                                            case 'draft';
                                                                if ($stu_sign_off == 'Yes') {
                                                                    echo 'status: student input complete';
                                                                    echo '<br/>';
                                                                } else {
                                                                    echo 'status: student in progress';
                                                                }
                                                                break;
                                                            case 'moderating';
                                                                echo 'status: moderating';
                                                                break;
                                                            case 'live';
                                                                echo 'status: finalised';
                                                                break;
                                                            case 'rejected';
                                                                echo 'status: rejected';
                                                                break;
                                                            default:
                                                                echo '';
                                                                break;

                                                        }
                                                    }
                                                    echo '<br/>';
                                                }
                                            }
                                            ?>
                                        </div>

                                        <div class="col-sm-2 col-md-2">
                                            <?php
                                    if(isset($student['milestones']['milestone']) && $student['milestones']['milestone'] != '') {
                                        foreach ($student['milestones']['milestone'] as $stu_milestone) {
                                            if (isset($stu_milestone['due_date']) && $stu_milestone['due_date'] != '') {
                                                echo date_format(date_create($stu_milestone['due_date']), 'd M Y');
                                            } else {
                                                echo '';
                                            }
                                            echo '<br/>';
                                        }
                                    }
                                            ?>
                                        </div>

                                    <div class="col-sm-2 col-md-2">
                                    <?php echo $student['supv_role']; ?>
                                    </div>

                                </li>
                                    </div>  
                        <?php }}?>
                        </ul>
                        </div>
            </div>
			
			<div class="container container-gap">
				<div class="col-md-12">
					<div class="row">
						<div id="examination-help-desktop" class="collapse desktop">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title">Help - Examination</h3>
								</div>

								<div class="panel-body">
									<div class="row">
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
			</div>

             <div class="panel panel-default">
                 <div class="panel-heading">Examination &nbsp;:: &nbsp;<?php echo $supervisor['given_name'].' '.$supervisor['family_name']; ?> &nbsp; &nbsp;
                    <?php
                    if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") or strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                        ?>
                        <a class="help_link text-center" data-toggle="collapse" data-target="#examination-help-desktop"><i class="material-icons" style="font-size: 24px">&#xE887;</i></a>
                    <?php }else {
                        ?>
                        <a class="help_link text-center" data-toggle="collapse" data-target="#examination-help-desktop"><i class="material-icons" style="font-size: 24px">help</i></a>
                        <?php
                    }
                    ?>
                </div>
                <div class="panel-body">
                    
                        <ul class="panel-ul">
                        <?php foreach($supervisor['students'] as $student)
                            {
                                if($student['stusys_active'] == 't')
                                {
                                    $uuid = '';
                                    $version = '';
                                    $milestone_code = '';
                                    $status = '';
                                    
                                    if(isset($student['examination']) && isset($student['examination']['uuid']))
                                    {
										$uuid = $student['examination']['uuid'];
                                        $version = $student['examination']['version'];
                                        $status = $student['examination']['status'];
                                    }
                                ?>
                     
                                <li class="panel-li">
                                    <div class="col-sm-3 col-md-3">
                                    <?php echo $student['family_name'] . ' , ' .$student['given_name']; ?>
                                    </div>
                                    <div class="col-sm-2 col-md-2">
                                    <?php
                                        if(isset($student['est_submit_date']) && $student['est_submit_date'] != '')
										{
                                            echo date_format(date_create($student['est_submit_date']), 'd M Y');
											
										}
									?>
                                    </div>
                                    <div class="col-sm-3 col-md-3">
                                    <?php 
										$status_display = '';
										switch($status)
										{
											case 'draft':
											$status_display = 'status: draft';
											break;
											
											case 'moderating':
											$status_display = 'status: being check';
											break;
											
											case 'live':
											$status_display = 'status: under examination';
											break;
											
											case 'rejected':
											$status_display = 'status: update required';
											break;
											
											case 'deleted':
											$status_display = '';
											break;
											
											default:
											$status_display = '';
											break;
											
										}
										
									echo $status_display; ?>
                                    </div>
                                    <div class="col-sm-3 col-md-3">
                                    <?php echo $student['supv_role']; ?>
                                    </div>
                                </li>
                        <?php }}?>
                        </ul>
                </div>
         	 </div>
        </div>
    </div>
</div>

		<?php }
		else{?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"> You don't have any student under your supervision.</h3>
			</div>
		</div>         
	<?php }?>

<?php 
}

 include 'footer.php';
?>