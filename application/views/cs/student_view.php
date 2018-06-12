<?php
include 'header.php';
?>

<?php
    /*echo '<pre>';
	print_r($this->session->userdata);
	print_r($milestones);

    echo '</pre>';*/
   
$access = false;

if(!isset($this->session->userdata['user_role']) || !isset($this->session->userdata['fan']))
{ 
	header('Location: ' . 'https://flextra-dev.flinders.edu.au/rex/cs/dashboard', true, 303);
  	die();
}
elseif($this->session->userdata['user_role'] != 'rhd_stu_and_sup' && $this->session->userdata['user_role'] != 'rhd_stu' && $this->session->userdata['user_role'] != 'rhd_sup')
{?>
	<div>
    	<h3>You do not have permission to view this page</h3>
    </div>
<?php }
else
{
	if($this->session->userdata['user_role'] == 'rhd_stu_and_sup' || $this->session->userdata['user_role'] == 'rhd_sup')
	{
		$stu_fan = $this->session->userdata['stu_fan']; 
		if( strpos($stu_fan, $student['stu_fan']) !== false ) 
		{
   				$user_role = $this->session->userdata['user_role']; 
	  			$user_fan = $this->session->userdata['fan']; 
				$access = true;
		}
		else
		{?>
			<div>
                <h3>Only student supervisors have permissions to view this page</h3>
            </div>
		<?php 
		}
	}
	elseif($this->session->userdata['user_role'] == 'rhd_stu')
	{
		if($this->session->userdata['fan'] ==  $student['stu_fan'])
		{
			$user_role = $this->session->userdata['user_role']; 
			$user_fan = $this->session->userdata['fan']; 
			$access = true;
		}
	}
}

if($access)
{  
?>
<!-- for modal, not in use -->
<!--<div class="modal fade" id="milestoneModal" tabindex="-1" role="dialog" aria-labelledby="mileStoneModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
             	<h4 class="modal-title" id="mileStoneModalLabel"></h4>
            </div>
            <div class="modal-body"><p>Loading…</p></div>
            </div></div>
            </div>
        </div>
    </div>
</div>-->

<div class="container">
	<h3 class="page-header"><?php echo $student['given_name'] .' '.$student['family_name'];?> - Milestones for <?php echo $student['course_title'] . ' :: ' . $student['load'];?></h3>
    
    <div class="spinner-wave" style="display:none; position:fixed;top:30%; left:40%;z-index:100; overflow:auto">
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <p style="z-index:100;" id="txt_loading">&nbsp;Loading...</p>
    </div>
    <div class="row">
    
    <?php if(isset($student['load']))
	{
    	if(strtoupper($student['load']) == 'FULL TIME')
		{ 
			if($student['course_type'] == 'PHD')
			{
		?>
        		<div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">12 months</p>
                </div>
                
                <?php if(isset($milestones) && isset($milestones['Confirmation of Candidature']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Confirmation of Candidature']['uuid']. "/" .$milestones['Confirmation of Candidature']['version'];?>">Confirmation of Candidature</a></p>
                    </div>
                    
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Confirmation of Candidature']['status']; ?> </p>
                    </div>
				<?php }
                else
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-grey btn text-info center-block"> Confirmation of Candidature </p>
                    </div>
                     <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "coc", "stu_id": "<?php echo $student['id']; ?>", "name_id": "1000"}'>Create Milestone</button>
                    </div>
                    	
               <?php } ?>
               
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">24 months</p>
                </div>
                <?php if(isset($milestones) && isset($milestones['Mid-Candidature Review']))
                {?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Mid-Candidature Review']['uuid']. "/" .$milestones['Mid-Candidature Review']['version'];?>">Mid-Candidature Review</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Mid-Candidature Review']['status']; ?> </p>
                    </div>

                <?php }
                else
                { ?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-grey btn text-info center-block"> Mid-Candidature Review</p>
                    </div>
                     <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "mcr", "stu_id": "<?php echo $student['id']; ?>", "name_id": "2000"}'>Create Milestone</button>
                    </div>
              <?php }?>
              
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">36 months</p>
                </div>
                
                 <?php if(isset($milestones) && isset($milestones['Final Thesis Review']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                    	<p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Final Thesis Review']['uuid']. "/" .$milestones['Final Thesis Review']['version'];?>">Final Thesis Review</a></p>
                	</div>
                	<div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Final Thesis Review']['status']; ?> </p>
                    </div>
                <?php } 
				else{
				?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-grey btn text-info center-block">Final Thesis Review</p>
                    </div>
                     <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ftr", "stu_id": "<?php echo $student['id']; ?>", "name_id": "3000"}'>Create Milestone</button>
                    </div>
             <?php } ?>
   <?php 	}
   			if($student['course_type'] == 'Masters')
			{ ?>
            	<div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">6-9 months</p>
                </div>
                
                 <?php if(isset($milestones) && isset($milestones['Confirmation of Candidature']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Confirmation of Candidature']['uuid']. "/" .$milestones['Confirmation of Candidature']['version'];?>">Confirmation of Candidature</a></p>
                    </div>
                    
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Confirmation of Candidature']['status']; ?> </p>
                    </div>
				<?php }
                else
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-grey btn text-info center-block"> Confirmation of Candidature </p>
                    </div>
                     <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "coc", "stu_id": "<?php echo $student['id']; ?>", "name_id": "1000"}'>Create Milestone</button>
                    </div>
                    	
               <?php } ?>
               
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
               
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">12 months</p>
                </div>

                <?php if(isset($milestones) && isset($milestones['Mid-Candidature Review']))
                {?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Mid-Candidature Review']['uuid']. "/" .$milestones['Mid-Candidature Review']['version'];?>">Mid-Candidature Review</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Mid-Candidature Review']['status']; ?> </p>
                    </div>

                <?php }
                else
                { ?>
                   <div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-grey btn text-info center-block"> Mid-Candidature Review </p>
                    </div>
                     <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "mcr", "stu_id": "<?php echo $student['id']; ?>", "name_id": "2000"}'>Create Milestone</button>
                    </div>
              <?php }?>
 
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                
                 <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">18 months</p>
                </div>
                
                <?php if(isset($milestones) && isset($milestones['Final Thesis Review']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                    	<p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Final Thesis Review']['uuid']. "/" .$milestones['Final Thesis Review']['version'];?>">Final Thesis Review</a></p>
                	</div>
                	<div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Final Thesis Review']['status']; ?> </p>
                    </div>
                <?php } 
				else{
				?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Final Thesis Review </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ftr", "stu_id": "<?php echo $student['id']; ?>", "name_id": "3000"}'>Create Milestone</button>
                    </div>
             <?php }
			}
		}
		
		if(strtoupper($student['load']) == 'PART TIME')
        { 
        	if($student['course_type'] == 'PHD')
			{ ?>
				<div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">12 months</p>
            	</div>
                <?php if(isset($milestones) && isset($milestones['10']) && isset($milestones['10']['Interim Milestone']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['10']['Interim Milestone']['uuid']. "/" .$milestones['10']['Interim Milestone']['version'];?>">Interim Milestone</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                    	<p class="text-center center-block button-padding"> status: <?php echo $milestones['10']['Interim Milestone']['status']; ?> </p>
                	</div>
                <?php }
				else
				{?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Interim Milestone </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ir", "stu_id": "<?php echo $student['id']; ?>", "name_id": "10"}'>Create Milestone</button>
                    </div>
               <?php } ?>
             
                
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
				
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">24 months</p>
            	</div>
                
				 <?php if(isset($milestones) && isset($milestones['Confirmation of Candidature']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Confirmation of Candidature']['uuid']. "/" .$milestones['Confirmation of Candidature']['version'];?>">Confirmation of Candidature</a></p>
                    </div>
                    
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Confirmation of Candidature']['status']; ?> </p>
                    </div>
				<?php }
                else
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-grey btn text-info center-block"> Confirmation of Candidature </p>
                    </div>
                     <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "coc", "stu_id": "<?php echo $student['id']; ?>", "name_id": "1000"}'>Create Milestone</button>
                    </div>
                    	
               <?php } ?>
                
				
				<div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                 <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">36 months</p>
            	</div>
                <?php if(isset($milestones) && isset($milestones['1010']) && isset($milestones['1010']['Interim Milestone']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['1010']['Interim Milestone']['uuid']. "/" .$milestones['1010']['Interim Milestone']['version'];?>">Interim Milestone</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                    	<p class="text-center center-block button-padding"> status: <?php echo $milestones['1010']['Interim Milestone']['status']; ?> </p>
                	</div>
                <?php }
				else
				{?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Interim Milestone </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ir", "stu_id": "<?php echo $student['id']; ?>", "name_id": "1010"}'>Create Milestone</button>
                    </div>
               <?php } ?>
                
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                 <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">48 months</p>
            	</div>
				
                 <?php if(isset($milestones) && isset($milestones['Mid-Candidature Review']))
                {?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Mid-Candidature Review']['uuid']. "/" .$milestones['Mid-Candidature Review']['version'];?>">Mid-Candidature Review</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Mid-Candidature Review']['status']; ?> </p>
                    </div>

                <?php }
                else
                { ?>
                     <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Mid-Candidature Review </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "mcr", "stu_id": "<?php echo $student['id']; ?>", "name_id": "2000"}'>Create Milestone</button>
                    </div>
              <?php }?>
              
              <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                 <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">60 months</p>
            	</div>
                
                  <?php if(isset($milestones) && isset($milestones['2010']) && isset($milestones['2010']['Interim Milestone']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['2010']['Interim Milestone']['uuid']. "/" .$milestones['2010']['Interim Milestone']['version'];?>">Interim Milestone</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                    	<p class="text-center center-block button-padding"> status: <?php echo $milestones['2010']['Interim Milestone']['status']; ?> </p>
                	</div>
                <?php }
				else
				{?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Interim Milestone </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ir", "stu_id": "<?php echo $student['id']; ?>", "name_id": "2010"}'>Create Milestone</button>
                    </div>
               <?php } ?>
                
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                 <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">72 months</p>
            	</div>
                
                 <?php if(isset($milestones) && isset($milestones['Final Thesis Review']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                    	<p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Final Thesis Review']['uuid']. "/" .$milestones['Final Thesis Review']['version'];?>">Final Thesis Review</a></p>
                	</div>
                	<div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Final Thesis Review']['status']; ?> </p>
                    </div>
                <?php } 
				else{
				?>
                	 <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block">Final Thesis Review</p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ftr", "stu_id": "<?php echo $student['id']; ?>", "name_id": "3000"}'>Create Milestone</button>
                    </div>
             <?php } ?>
             
             	<div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                 <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">84 months</p>
            	</div>
                
                 <?php if(isset($milestones) && isset($milestones['3010']) && isset($milestones['3010']['Interim Milestone']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['3010']['Interim Milestone']['uuid']. "/" .$milestones['3010']['Interim Milestone']['version'];?>">Interim Milestone</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                    	<p class="text-center center-block button-padding"> status: <?php echo $milestones['3010']['Interim Milestone']['status']; ?> </p>
                	</div>
                <?php }
				else
				{?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Interim Milestone </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ir", "stu_id": "<?php echo $student['id']; ?>", "name_id": "3010"}'>Create Milestone</button>
                    </div>
               <?php } ?>
               
                
            <?php }
			
			if($student['course_type'] == 'Masters')
			{?>
				<div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">12 months</p>
            	</div>
                
                <?php if(isset($milestones) && isset($milestones['Confirmation of Candidature']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Confirmation of Candidature']['uuid']. "/" .$milestones['Confirmation of Candidature']['version'];?>">Confirmation of Candidature</a></p>
                    </div>
                    
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Confirmation of Candidature']['status']; ?> </p>
                    </div>
				<?php }
                else
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
						<p class="lead text-center bg-grey btn text-info center-block"> Confirmation of Candidature </p>
                    </div>
                     <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "coc", "stu_id": "<?php echo $student['id']; ?>", "name_id": "1000"}'>Create Milestone</button>
                    </div>
                    	
               <?php } ?>
                
				
				<div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">18 months</p>
            	</div>
                
                <?php if(isset($milestones) && isset($milestones['1010']) && isset($milestones['1010']['Interim Milestone']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['1010']['Interim Milestone']['uuid']. "/" .$milestones['1010']['Interim Milestone']['version'];?>">Interim Milestone</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                    	<p class="text-center center-block button-padding"> status: <?php echo $milestones['1010']['Interim Milestone']['status']; ?> </p>
                	</div>
                <?php }
				else
				{?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Interim Milestone </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ir", "stu_id": "<?php echo $student['id']; ?>", "name_id": "1010"}'>Create Milestone</button>
                    </div>
               <?php } ?>
               
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">24 months</p>
            	</div>
				 <?php if(isset($milestones) && isset($milestones['Mid-Candidature Review']))
                {?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Mid-Candidature Review']['uuid']. "/" .$milestones['Mid-Candidature Review']['version'];?>">Mid-Candidature Review</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Mid-Candidature Review']['status']; ?> </p>
                    </div>

                <?php }
                else
                { ?>
                     <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block">Mid-Candidature Review</p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "mcr", "stu_id": "<?php echo $student['id']; ?>", "name_id": "2000"}'>Create Milestone</button>
                    </div>
              <?php }?>
              
              <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">30 months</p>
            	</div>
                
                 <?php if(isset($milestones) && isset($milestones['2010']) && isset($milestones['2010']['Interim Milestone']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['2010']['Interim Milestone']['uuid']. "/" .$milestones['2010']['Interim Milestone']['version'];?>">Interim Milestone</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                    	<p class="text-center center-block button-padding"> status: <?php echo $milestones['2010']['Interim Milestone']['status']; ?> </p>
                	</div>
                <?php }
				else
				{?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Interim Milestone </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ir", "stu_id": "<?php echo $student['id']; ?>", "name_id": "2010"}'>Create Milestone</button>
                    </div>
               <?php } ?>
                
                
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">36 months</p>
            	</div>
                
                <?php if(isset($milestones) && isset($milestones['3010']) && isset($milestones['1010']['Interim Milestone']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['3010']['Interim Milestone']['uuid']. "/" .$milestones['3010']['Interim Milestone']['version'];?>">Interim Milestone</a></p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                    	<p class="text-center center-block button-padding"> status: <?php echo $milestones['3010']['Interim Milestone']['status']; ?> </p>
                	</div>
                <?php }
				else
				{?>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Interim Milestone </p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ir", "stu_id": "<?php echo $student['id']; ?>", "name_id": "3010"}'>Create Milestone</button>
                    </div>
               <?php } ?>
                
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                        <p class="btn"><span class="glyphicon glyphicon-arrow-down"></span>
                    </div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-2">
                	<p class="text-center center-block button-padding">48 months</p>
            	</div>
                 <?php if(isset($milestones) && isset($milestones['Final Thesis Review']))
                {?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                    	<p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $milestones['Final Thesis Review']['uuid']. "/" .$milestones['Final Thesis Review']['version'];?>">Final Thesis Review</a></p>
                	</div>
                	<div class="col-md-2 col-sm-2 col-xs-2">
                        <p class="text-center center-block button-padding"> status: <?php echo $milestones['Final Thesis Review']['status']; ?> </p>
                    </div>
                <?php } 
				else{
				?>
                	<div class="col-md-8 col-sm-8 col-xs-8">
                        <p class="lead text-center bg-grey btn text-info center-block"> Final Thesis Review</p>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                       <button type='button' class='btn btn-primary create_milestone' data-button='{"milestone": "ftr", "stu_id": "<?php echo $student['id']; ?>", "name_id": "3000"}'>Create Milestone</button>
             <?php } ?>
			<?php }
		} 
	} 
	?>
    </div>
</div>
</div>

<div class="container">
	<h3 class="page-header">Submit thesis for examination</h3>
    	<div class="row">
   		<div class="col-md-2 col-sm-2 col-xs-2">
            <p class="text-center center-block button-padding"></p>
        </div>
        
        <?php if(isset($examination) && isset($examination['status']))
        {?>
            <div class="col-md-8 col-sm-8 col-xs-8">
                <p class="lead text-center bg-info btn text-info center-block"><a class="milestone-link" id="<?php echo $examination['uuid']. "/" .$examination['version'];?>"><?php echo $examination['thesis_title'];?></a></p>
            </div>
            
            <div class="col-md-2 col-sm-2 col-xs-2">
                <p class="text-center center-block button-padding"> status: <?php echo $examination['status']; ?> </p>
            </div>
        <?php }
        else
        {?>
            <div class="col-md-8 col-sm-8 col-xs-8">
                <button class='lead text-center btn-primary btn text-info center-block create_examination' data-button='{"stu_id": "<?php echo $student['id']; ?>"}'> Submit Thesis </button>
            </div>
             <div class="col-md-2 col-sm-2 col-xs-2">
            </div>
                
       <?php } ?>
    	</div>
    </div>
<?php 
}

 include 'footer.php';
?>