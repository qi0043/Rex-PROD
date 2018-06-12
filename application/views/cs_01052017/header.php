<!DOCTYPE html>
<html lang="en">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<head>
	<title>Flinders University - Research Excellent - Development Environment</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>js/jquery-1.11.1.min.js" ></script>
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>bootstrap-3.3.4-dist/js/bootstrap.min.js" ></script>
    
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>bootstrap-3.3.4-dist/css/bootstrap.css" media="all">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/flextra-er.css" media="all">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/rhd.css" media="all">
    
<style type="text/css">
.button-padding
{
	padding: 6px 12px;
}

.modal-body
{
	min-height:200px;
}
.modal-title
{
	text-align:center;
}

.bg-grey
{
	background-color: #f1f1f1;
}
</style>


<script type="text/javascript">
	$(document).ready(function() 
	{
		$('.create_milestone').click(function(e) {
			var data = $.parseJSON($(this).attr('data-button'));
			//alert(data.stu_id);
			
			var pass_data = {
				milestone_id: data.name_id,
				milestone: data.milestone,
				id: data.stu_id
			};
			
			//console.log(pass_data);
			
			var url = <?php echo json_encode(base_url('index.php/cs/dashboard/stundentData')); ?>;
			
			var posting = $.post(url, pass_data);	
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
						//console.log(redirect_url);
						location.reload();
					 	var win = window.open(redirect_url);
  						win.focus();
						
						
						//$("#milestone_form").hide();
						//$(".modal-footer").hide();
						
						//$('#milestoneModal').modal('hide');
						//$("#msg_display").html("<h3>A new page has been opened in a new tab. If it is not, please click on the link below to continue.</h3><a href='"+redirect_url+"' target='_blank'> Redirect me to the Research Excellence</a>");
					}
				}
			});
			posting.fail(function(xhr, status, error) {
				$(".spinner-wave").fadeOut('slow');
				alert("Error: " + xhr.status + " " + error);
			});
		});
		
		$('.create_examination').click(function(e) {
			var data = $.parseJSON($(this).attr('data-button'));
			//alert(data.stu_id);
			
			var pass_data = {
				id: data.stu_id
			};
			
			//console.log(pass_data);
			
			var url = <?php echo json_encode(base_url('index.php/cs/dashboard/create_examination')); ?>;
			
			var posting = $.post(url, pass_data);	
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
						//console.log(redirect_url);
						location.reload();
					 	var win = window.open(redirect_url);
  						win.focus();
						
						
						//$("#milestone_form").hide();
						//$(".modal-footer").hide();
						
						//$('#milestoneModal').modal('hide');
						//$("#msg_display").html("<h3>A new page has been opened in a new tab. If it is not, please click on the link below to continue.</h3><a href='"+redirect_url+"' target='_blank'> Redirect me to the Research Excellence</a>");
					}
				}
			});
			posting.fail(function(xhr, status, error) {
				$(".spinner-wave").fadeOut('slow');
				alert("Error: " + xhr.status + " " + error);
			});
		});
		
		$('.milestone-link').click(function(e) {
			//return;
			//e.preventDefault(); // do not follow link
			
			var url = $(this).attr('id');
			var index = url.indexOf('/');
			var uuid = url.substring(0,index);
			var version = url.substr(index+1, 1);

			var newURL = 'https://flextra-dev.flinders.edu.au/rex/index.php/cs/dashboard/rexLink/' + uuid+'/'+ version;
			window.open(newURL);
		});
	});
	
	/************for modal, not in use*******************************/
	/*$(document).on("hidden.bs.modal", function (e) {
		 $(e.target).removeData("bs.modal").find(".modal-body").empty();
		 $(e.target).removeData("bs.modal").find(".modal-title").empty(); 
		 $(".modal-body").html('<p>Loading…</p>'); $(".modal-title").html('');
		 location.reload();
  	});*/

</script>
</head>

<body>
<div class="wrap">
    <div id="header" role="banner">
        <div id="header-inner-coursework" class="header-inner-coursework">
            <div class="banner-text"><h2>RHD Research Excellence</h2></div>
   
            <div class="banner"><?php echo $heading;?></div>
        </div>
    </div>
    
    <nav class="navbar navbar-inverse navbar-collapse">
    <div class="container">
        <div class="navbar-header col-xs-5 col-md-3">
            <a class="navbar-brand" href="#">RHD Research Excellence</a>
        </div>
        <ul class="nav navbar-nav navbar-left col-xs-5 col-md-7">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                   aria-expanded="false">Role <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="#">Candidate</a></li>
                    <li><a href="#">Supervisor</a></li>
                </ul>
            </li>
        </ul>
         <div class="navbar-header col-xs-1 col-md-2">
                <ul class="nav">
                     <li class="brand"><i class="glyphicon glyphicon-user"></i> <span class="right"><?php if(isset($this->session->userdata['fan'])){echo $this->session->userdata['fan'];} else {echo 'You are not logged in';} ?></span></li>
                </ul>
            </div>
    </div>
</nav>
   