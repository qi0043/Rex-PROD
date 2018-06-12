<?php include 'nav.php'; ?>
<?php
if (!isset($this->session->userdata['user_role']) || !isset($this->session->userdata['fan'])) {
    ?>
    <div class="container container-gap">
        <h3>Session Time Out, Please click <a href="<?php echo base_url('cs/dashboard'); ?>">here</a> to return to the
            dashboard</h3>
    </div>
    <?php

} else if ($this->session->userdata['user_role'] != 'coordinator'
    && $this->session->userdata['user_role'] != 'supervisor_coordinator'
    && $this->session->userdata['user_role'] != 'student_supervisor_coordinator'
    && $this->session->userdata['user_role'] != 'student_coordinator'
    && !$this->session->userdata['admin']) {

    ?>
    <div class="container container-gap">
        <h3>You do not have permission to view this dashboard</h3>
    </div>
<?php } else {
    ?>
    <?php
        $user_role = $this->session->userdata['user_role'];
        $user_fan = $this->session->userdata['fan'];
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

    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <?php if (count($students) < 1) { ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><br/><br/>You are accessing this page in your <b>RHD Coordinator role</b>.<br/><br/>
                            There are currently no student milestones allocated to you in this role.<br/><br/>
                            For help, please contact your RHD Administrator or <a href="mailto: flex.help@flinders.edu.au?Subject=RHD Coordinator Query - RHD Research Excellence" target="_top">flex.help@flinders.edu.au</a>.
                            <br/><br/>
                        </h3>
                    </div>
                </div>
                <?php } else { ?>
                <div class="panel panel-default table-responsive">
                    <div class="panel-heading">Progression&nbsp;::&nbsp;assigned to&nbsp;<?php echo $coordinator['first_name'].' '.$coordinator['last_name']; ?>&nbsp;&nbsp;

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
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>View</th>
                                <th>Status</th>
                                <th></th>
                                <th>Course Code</th>
                                <th>Load</th>
                                <th>Commencement</th>
                                <th>Principal Supervisor</th>
                            </tr>
                        </thead>

                        <?php
                        foreach($students as $student) { ?>
                        <tr>
                            <td><?php echo $student[0]['student_name']; ?></td>
                            <td>
                            <?php
                            for($i=0; $i<(count($student) - 4); $i++) {
                                if ($student[$i]['is_principal_supv'] === 't') { ?>
                                    <?php echo $student[$i]['milestone_name']; ?><br/>
                                <?php } else { ?>
                                    <a class="milestone-link"
                                       id="<?php echo $student[$i]['item_uuid'] . "/" . $student[$i]['item_version']; ?>"><?php echo $student[$i]['milestone_name']; ?></a>
                                    <br/>
                                <?php }
                            }
                            ?>
                            </td>
                            <td>
                                <?php
                                for($i=0; $i<(count($student) - 4); $i++) {
                                    echo $student[$i]['status'] . "<br/>";
                                }?>

                            </td>

                            <td>
                                <?php
                                for($i=0; $i<(count($student) - 4); $i++) {
                                if ($student[$i]['mod_uuid'] === 'a08b3eb2-457b-46ea-94b0-29a82de3fc06') { ?>
                            <a class="milestone-link" target="_blank"
                                   href="<?php echo $student[$i]['rex_url'] . 'access/tasklist.do?taskId=' . $student[$i]['item_uuid'] . '.' . $student[$i]['mod_uuid'] . '%2F' . $student[$i]['item_version']; ?>"><?php echo $student[$i]['stage']; ?></a><br/>

                            <?php } else { ?>
                                <?php echo $student[$i]['stage']; ?><br/>
                            <?php }
                            }?>
                            </td>

                            <td>
                                <?php
                                    echo $student['course_code_list'];
                                ?>
                            </td>
                            <td>
                                <?php
                                    echo $student['load_list'];
                                ?>
                            </td>
                            <td>
                                <?php
                                    echo $student['start_date_list'];
                                ?>
                            </td>
                            <td>
                                <?php
                                    echo $student['principal_supervisor_name_list'];
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php

                } ?>
<?php } include 'footer.php'; ?>
