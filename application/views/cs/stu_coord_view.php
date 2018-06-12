<?php 
include 'nav.php';
?>

<?php
if(!isset($this->session->userdata['user_role']) || !isset($this->session->userdata['fan']))
{?>
	<div class="container container-gap">
    	<h3>Session Time Out, Please click <a href="<?php echo base_url('cs/dashboard'); ?>">here</a> to return to the dashboard</h3>
    </div>
<?php 
}
elseif($this->session->userdata['user_role'] != 'student_coordinator')
{?>
	<div class="container container-gap">
    	<h3>You do not have permission to view this dashboard</h3>
    </div>
<?php }
else
{
?>
<div class="container-fluid container-gap">
	<div class="col-md-12 col-sm-12 col-xs-12">
    	<div class="panel panel-default">
        	<div class="panel-heading">
            	<h3 class="panel-title"> Please Select Your User Role </h3>
            </div>
            <div class="panel-body">
            	<ul class="panel-ul">
                	<li class="panel-li"><a  href="<?php echo base_url('cs/student'). '/'. $this->session->userdata['fan'] .'/'; ?>">RHD Student</a></li>
                    <li class="panel-li"><a  href="<?php echo base_url('cs/coordinator'). '/'. $this->session->userdata['fan'] .'/'; ?>">RHD Coordinator</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php 
}
include 'footer.php';
?>
