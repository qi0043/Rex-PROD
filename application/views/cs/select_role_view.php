<?php 
include 'nav.php';
?>

<?php $user_role = $this->session->userdata['user_role']; 
	  $user_fan = $this->session->userdata['fan']; 
?>

<?php
if(!isset($user_fan) || !isset($user_role))
{?>
    <div class="container container-gap">
    	<h3>Session Time Out, page will redirect you to the dashboard</h3>
    </div>
<?php }
elseif($user_role != 'rhd_stu_and_sup')
{?>
    <div class="container container-gap">
    	<h3>You do not have permission to view this dashboard</h3>
    </div>
<?php }
else
{
?>
<div class="container-fluid">
	<div class="col-md-12 col-sm-12 col-xs-12">
    	<div class="panel panel-default">
        	<div class="panel-heading">
            	<h3 class="panel-title"> Please Select Your User Role </h3>
            </div>
            <div class="panel-body">
            	<ul class="panel-ul">
                	<li class="panel-li"><a  href="<?php echo base_url() . 'cs/dashboard/candidate/'. $student['id']. '/' . $user_fan;?>">RHD Candidate</a></li>
                    <li class="panel-li"><a  href="<?php echo base_url() . 'cs/dashboard/supervisor/'. $supervisor['id'];?>">RHD Supervisor</a>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php 
}
include 'footer.php';
?>
