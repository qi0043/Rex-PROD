<?php include 'nav.php'; ?>
<?php
if(!isset($this->session->userdata['user_role']) || !isset($this->session->userdata['fan']))
{?>
	<div class="container container-gap">
    	<h3>Session Time Out, Please click <a href="<?php echo base_url('cs/dashboard'); ?>">here</a> to return to the dashboard</h3>
    </div>
<?php 
}
elseif($this->session->userdata['user_role'] != 'rhd_stu_and_sup' && $this->session->userdata['user_role'] != 'rhd_sup' )
{?>
	<div>
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

<div class="container-fluid container-gap">
	<div class="col-md-12 col-sm-12 col-xs-12">
    	<?php if($supervisor['students'] != 0)
    	{?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Progression</h3>
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
                     
                                <li class="panel-li">
                                    <div class="col-sm-3 col-md-3">
                                    <?php echo $student['family_name'] . ' , ' .$student['given_name']; ?>
                                    </div>
                                    
                                    <div class="col-sm-2 col-md-2">
                                    <?php if(isset($student['milestones']['current_milestone_code']) && $student['milestones']['current_milestone_code'] != '')
									{
										if($uuid != '' && $version != '')
										{?>
											 <a class="milestone-link" id="<?php echo $uuid. "/" .$version;?>"><?php echo $student['milestones']['current_milestone'] ; ?></a>
										<?php }
										else
										{
											echo $student['milestones']['current_milestone'];
										}
									}
									else
									{
										echo '';
									}
									?>
                                    </div>
                                    
                                    <div class="col-sm-3 col-md-3">
                                    <?php 
									switch ($status)
									{
										case 'draft';
											if($stu_sign_off == 'Yes')
											{
												echo 'status: student input complete';
											}
											else
											{
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
								    ?>
                                    </div>
                                    
                                    <div class="col-sm-2 col-md-2">
                                    <?php if(isset($student['milestones']['current_milestone_due_date']) && $student['milestones']['current_milestone_due_date'] != '')
									{
										//echo $student['milestones']['current_milestone_due_date'];
										echo date_format(date_create($student['milestones']['current_milestone_due_date']), 'd M Y');
									}
									else
									{
										echo '';
										
									}
									?>
                                    </div>
                                   
                                    <div class="col-sm-2 col-md-2">
                                    <?php echo $student['supv_role']; ?>
                                    </div>
                                </li>
                                
                        <?php }}?>
                        </ul>
                        </div>
            </div>
            
             <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Examination</h3>
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
											//echo $student['est_submit_date'];
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
		<?php }
		else{?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"> You don't have any student under your supervision.</h3>
			</div>
		</div>         
	<?php }?>
   </div>		
</div>
<?php 
}

 include 'footer.php';
?>