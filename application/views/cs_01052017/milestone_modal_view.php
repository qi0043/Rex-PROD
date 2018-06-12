<script type="text/javascript">
	$(function() {
		$("#btn_new_milestone").click(function(){
			var milestone = $("[name='milestone_type']").val();
			var milestone_id = $("[name='milestone_id']").val();
			var candidate_fan = $("[name='candidate_fan']").val();
			var candidate_given_name = $("[name='candidate_given_name']").val();
			var candidate_pref_name = $("[name='candidate_pref_name']").val();
			var candidate_family_name = $("[name='candidate_family_name']").val();
			var candidate_topic_code = $("[name='candidate_topic_code']").val();
			var candidate_course_code = $("[name='candidate_course_code']").val();
			var candidate_course_title = $("[name='candidate_course_title']").val();
			var candidate_course_attendance = $("[name='candidate_attendance']").val();
			var candidate_topic_status = $("[name='candidate_topic_status']").val();
			var candidate_id = $("[name='candidate_id']").val();
			var candidate_email = $("[name='candidate_email']").val();
			var candidate_thesis_title = $("[name='candidate_thesis_title']").val();
			var candidate_school = $("[name='candidate_school_org_num']").val();
			var candidate_school_name = $("[name='candidate_school']").val();
			var candidate_load = $("[name='candidate_load']").val();
			
			var tcs = [];
			$("[name='candidate_sups_role[]']").each(function(i){
				tcs[i] = {
					role : $(this).val(),
					title: $("[name='candidate_sups_title[]']:eq(" + i + ")").val(),
					given_name: $("[name='candidate_sups_given_name[]']:eq(" + i + ")").val(),
					family_name: $("[name='candidate_sups_family_name[]']:eq(" + i + ")").val(),
					fan: $("[name='candidate_sups_fan[]']:eq(" + i + ")").val()
				}; 
			});
			
			var candidate_commence_date = $("[name='candidate_commence_date']").val();
			var candidate_sub_date = $("[name='candidate_sub_date']").val();
			var candidate_residency = $("[name='candidate_residency']").val();
			var candidate_rts_end_date = $("[name='candidate_rts_end_date']").val();
			
			var candidate_eftsl = $("[name='candidate_eftsl']").val();
			var candidate_unreported_eftsl = $("[name='candidate_unreported_eftsl']").val();
			var candidate_total_eftsl = $("[name='candidate_total_eftsl']").val();
			
			var data = {
				milestone_id: milestone_id,
				milestone: milestone,
				candidate_fan: candidate_fan,
				candidate_given_name: candidate_given_name,
				candidate_pref_name: candidate_pref_name,
				candidate_family_name: candidate_family_name,
				candidate_topic_code: candidate_topic_code,
				candidate_course_code: candidate_course_code,
				candidate_course_title: candidate_course_title,
				candidate_course_attendance: candidate_course_attendance,
				candidate_topic_status: candidate_topic_status,
				candidate_id: candidate_id,
				candidate_email: candidate_email,
				candidate_thesis_title: candidate_thesis_title,
				candidate_school: candidate_school,
				candidate_school_name: candidate_school_name,
				candidate_load: candidate_load,
				tcs : tcs,
				candidate_commence_date: candidate_commence_date,
				candidate_sub_date: candidate_sub_date,
				candidate_residency: candidate_residency,
				candidate_rts_end_date: candidate_rts_end_date,
				candidate_eftsl: candidate_eftsl,
				candidate_unreported_eftsl: candidate_unreported_eftsl,
				candidate_total_eftsl: candidate_total_eftsl
			};
			
			console.log(data);
			
			var url = <?php echo json_encode(base_url('index.php/cs/dashboard/create_milestones')); ?>;
			
			var posting = $.post(url, data);	
			 $(".spinner-wave").show();
			posting.done(function(data, status) {
				if(status == 'success')
				{
					var resultobj = jQuery.parseJSON(data);
					
					if(resultobj.status == 'success')
					{
						$(".spinner-wave").fadeOut('slow');
						
						var uuid = resultobj.uuid;
						var version = resultobj.version;
						var token = resultobj.token;
						var redirect_url = 'https://rex-dev.flinders.edu.au/items/' + uuid + '/' + version + '/?token=' + token;
						console.log(redirect_url);
					 	var win = window.open(redirect_url);
  						win.focus();
						
						$("#milestone_form").hide();
						$(".modal-footer").hide();
						
						//$('#milestoneModal').modal('hide');
						$("#msg_display").html("<h3>A new page has been opened in a new tab. If it is not, please click on the link below to continue.</h3><a href='"+redirect_url+"' target='_blank'> Redirect me to the Research Excellence</a>");
					}
				}
			});
			posting.fail(function(xhr, status, error) {
				$(".spinner-wave").fadeOut('slow');
				alert("Error: " + xhr.status + " " + error);
			});
			
			
		});
	});
	
</script>

<style type="text/css">
/**********loading spinner***************/
.spinner-wave {
    margin: 0 auto;
    width: 100px;
    height: 100px;
    text-align: center;
	
}
.spinner-wave > div {
    background-color: #333;
    height: 50%;
    width: 6px;
    display: inline-block;
    -webkit-animation: wave 1.2s infinite ease-in-out;
    animation: wave 1.2s infinite ease-in-out;
}

.spinner-wave div:nth-child(2) {
    -webkit-animation-delay: -1.1s;
    animation-delay: -1.1s;
}

.spinner-wave div:nth-child(3) {
    -webkit-animation-delay: -1.0s;
    animation-delay: -1.0s;
}

.spinner-wave div:nth-child(4) {
    -webkit-animation-delay: -0.9s;
    animation-delay: -0.9s;
}

.spinner-wave div:nth-child(5) {
    -webkit-animation-delay: -0.8s;
    animation-delay: -0.8s;
}
@-webkit-keyframes wave {
    0%, 40%, 100% { -webkit-transform: scaleY(0.4) }
    20% { -webkit-transform: scaleY(1.0) }
}

@keyframes wave {
    0%, 40%, 100% { transform: scaleY(0.4); }
    20% { transform: scaleY(1.0); }
}
/******************************************/
</style>

<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div>
            <h4 class="modal-title"><?php echo $milestone; ?></h4>     
        </div>
    </div>
    <div class="modal-body">
    	 <!-- spinner -->
        <div class="spinner-wave" style="display:none; position:fixed;top:30%; left:40%;z-index:100; overflow:auto">
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <div style="z-index:100;"></div>
            <p style="z-index:100;" id="txt_loading">&nbsp;Loading...</p>
        </div>
    	<div id="msg_display">
        	
        </div>
        <form role="form" id="milestone_form">
        
        	<input type="hidden" name="milestone_type" value="<?php echo $milestone; ?>"></input>
            <input type="hidden" name="milestone_id" value="<?php echo $milestone_id; ?>"></input>
            <input type="hidden" name="candidate_fan" value="<?php echo $student['stu_fan']; ?>"></input>
            <input type="hidden" name="candidate_email" value="<?php echo $student['email']; ?>"></input>
            <input type="hidden" name="candidate_topic_code" value="<?php echo $student['topic_code']; ?>"></input>
            <input type="hidden" name="candidate_topic_status" value="<?php echo $student['topic_status']; ?>"></input>
            <input type="hidden" name="candidate_course_code" value="<?php echo $student['course_code']; ?>"></input>
            <input type="hidden" name="candidate_course_title" value="<?php echo $student['course_title']; ?>"></input>
            <input type="hidden" name="candidate_attendance" value="<?php echo $student['attendance']; ?>"></input>
            
            
            <h5 style="color:red">* Please check the information below before creating this milestone</h5>
            <div class="form-group row">
                <label for="candidate_name" class="control-label col-md-2 col-sm-2 col-xs-2">Candidate Name: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_name" value="<?php echo $student['given_name'] . ' ' . $student['family_name'] ; ?>" readonly></input>
                    <input type="hidden" name="candidate_given_name" value="<?php echo $student['given_name']; ?>"></input>
                    <input type="hidden" name="candidate_pref_name" value="<?php echo $student['pref_name']; ?>"></input>
                    <input type="hidden" name="candidate_family_name" value="<?php echo $student['family_name']; ?>"></input>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="candidate_id" class="control-label col-md-2 col-sm-2 col-xs-2">Student ID: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_id" value="<?php echo $student['stu_id']; ?>" readonly></input>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="candidate_fan" class="control-label col-md-2 col-sm-2 col-xs-2">FAN: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_fan" value="<?php echo $student['stu_fan']; ?>" readonly></input>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="candidate_degree" class="control-label col-md-2 col-sm-2 col-xs-2">Degree: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_degree" value="<?php echo $student['course_title']; ?>" readonly></input>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="candidate_topic_code" class="control-label col-md-2 col-sm-2 col-xs-2">Topic code: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_topic_code" value="<?php echo $student['topic_code']; ?>" readonly></input>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="candidate_thesis_title" class="control-label col-md-2 col-sm-2 col-xs-2">Thesis title: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_thesis_title" value="<?php echo $student['thesis_title']; ?>" readonly></input>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="candidate_school" class="control-label col-md-2 col-sm-2 col-xs-2">School (Dept or Unit): </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_school" value="<?php echo $student['school_name']; ?>" readonly></input>
                    <input type="hidden" value="<?php echo $student['school_org_num']; ?>" name="candidate_school_org_num" ></input>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="candidate_load" class="control-label col-md-2 col-sm-2 col-xs-2">Full-Time/Part-Time Candidature: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_load" value="<?php echo $student['load']; ?>" readonly></input>
                </div>
            </div>
            
            <div class="form-group row">
                <?php 
                $index = 0;
                foreach($student['supervisors'] as $sup)
                { 
                    $index++;
                ?>
                    <label for="candidate_sups_role" class="control-label col-md-2 col-sm-2 col-xs-2"><?php if($index == 1){echo 'Supervisors:' ;}?> </label>
                    <div class="col-md-10 col-sm-10 col-xs-10">
                        <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <input type="text" class="form-control" name="candidate_sups_role[]" value="<?php echo $sup['supv_role']; ?>" readonly></input>
                                </div>
                                <div class="col-md-8 col-sm-8 col-xs-8">
                                    <input type="text" class="form-control" name="candidate_sups_name[]" value="<?php echo $sup['title'] . ' ' . $sup['given_name'] . ' '.$sup['family_name']; ?>" readonly></input>
                                </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="candidate_sups_title[]" value="<?php echo $sup['title'];?>"></input>
                    <input type="hidden" name="candidate_sups_given_name[]" value="<?php echo $sup['given_name'];?>"></input>
                    <input type="hidden" name="candidate_sups_family_name[]" value="<?php echo $sup['family_name'];?>"></input>
                    <input type="hidden" name="candidate_sups_fan[]" value="<?php echo $sup['supv_fan'];?>"></input>
                    
                    
                <?php } ?>
            </div>
         
            <div class="form-group row">
                <label for="candidate_commence_date" class="control-label col-md-2 col-sm-2 col-xs-2">Candidature Commencement Date: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_commence_date" value="<?php echo $student['start_date']; ?>" readonly></input>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="candidate_sub_date" class="control-label col-md-2 col-sm-2 col-xs-2">Expected Submission Date: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_sub_date" value="<?php echo $student['est_submit_date']; ?>" readonly></input>
                </div>
            </div>
            
            <input type="hidden" name="candidate_residency" value="<?php echo $student['residency']; ?>"></input>
            
            <div class="form-group row">
                <label for="candidate_rts_end_date" class="control-label col-md-2 col-sm-2 col-xs-2">Research Training Scheme (RTS) End Date: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_rts_end_date" value="<?php echo $student['fec_date']; ?>" readonly></input>
                </div>
            </div>  
         
            
            <div class="form-group row">
                <label for="candidate_eftsl" class="control-label col-md-2 col-sm-2 col-xs-2">EFTSL to end of current reporting period:</label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <input type="text" class="form-control" name="candidate_eftsl" value="<?php echo $student['reported_eftsl']; ?>" readonly></input>
                    <input type="hidden" name="candidate_unreported_eftsl" value="<?php echo $student['unreported_eftsl']; ?>" readonly></input>
                    <input type="hidden" name="candidate_total_eftsl" value="<?php echo $student['total_eftsl']; ?>" readonly></input>
                </div>
            </div> 
            <p></p>
        </form>
        <div class="modal-footer">
        	<button type="button" class="btn btn-primary" id="btn_new_milestone">Create Milestone</button>
        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div> 
        </form>
    </div>
</div>