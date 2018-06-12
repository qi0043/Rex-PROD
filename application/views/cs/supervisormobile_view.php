
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>RHD Milestones</title>
</head>
<body>

<!--<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">RHD Research Excellence</a>
        </div>
    </div>
</nav>-->

<?php
include 'header.php';
?>

<?php
    $user_role = $this->session->userdata['user_role'];
    $user_fan = $this->session->userdata['fan'];
    $stu_fan = $this->session->userdata['stu_fan'];
/*echo '<pre>';
print_r($this->session->userdata);
echo '</pre>';*/
?>

<?php
if(!isset($user_fan) || !isset($user_role))
{?>
    <div class="container container-gap">
        <h3>Session Time Out, page will redirect you to the dashboard</h3>
    </div>
<?php }
elseif($user_role != 'rhd_stu_and_sup' && $user_role != 'rhd_sup')
{?>
    <div class="container container-gap">
        <h3>You do not have permission to view this dashboard</h3>
    </div>
<?php }
else
{
?>

<div class="container" style="margin-top:80px">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Milestones</h3>
                </div>

                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-stripped table-bordered">
                            <thead>
                                <th>Name</th>
                                <th>Link</th>
                                <th>Role</th>
                            </thead>
                            <tbody>
                            <?php

                            if($supervisor['students'] != 0)
                            {   $studentCount = count($supervisor['students']);
                                for($i=0; $i<$studentCount; $i++)
                                #foreach($supervisor['students'] as $student)
                                {
                                ?>
                                    <tr>
                                        <td><?php echo isset($supervisor['students'][$i]['details']['given_name'])? $supervisor['students'][$i]['details']['given_name']:"";
                                                  echo ", ";
                                                  echo isset($supervisor['students'][$i]['details']['family_name'])? $supervisor['students'][$i]['details']['family_name']:""; ?></td>
                                        <?php if (!(isset($supervisor['students'][$i]['currentmilestone']))){echo "<td>No current milestone</td>";} else{?>
                                            <td><a class="milestone-link" id="<?php echo $supervisor['students'][$i]['currentmilestone']['uuid']. "/" .$supervisor['students'][$i]['currentmilestone']['version'];?>">View Milestones</a></td>
                                        <?php } ?>
                                        <td><?php echo isset($supervisor['students'][$i]['supv_role'])? $supervisor['students'][$i]['supv_role']:"";?></td>
                                    </tr>
                                 <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php }
            else
            {?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"> You don't have any student under your supervision.</h3>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="css/bootstrap.min.css">
<script src="jquery/3.1.1/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>

</body>
</html>
<?php
}

include 'footer.php';
?>