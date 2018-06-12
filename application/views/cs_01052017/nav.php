<!DOCTYPE html>
<html lang="en">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<head>
	<title>Flinders University - Research Excellent</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
   
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>js/jquery-1.11.1.min.js" ></script>
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>bootstrap-3.3.4-dist/js/bootstrap.min.js" ></script>
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>js/jquery.matchHeight.js" ></script>
    
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/milestone/bootstrap.min.css" media="all">
<!--    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/milestone/bootstrap.css" media="all">
-->    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/flextra-er.css" media="all">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>css/rhd.css" media="all">
    
<style type="text/css">
.container .navbar-brand{
    padding: 15px 0px !important;
}
.navbar .brand{
	padding-top:15px;
}
.container .navbar .brand{
    padding: 15px 15px !important;
}


.navbar-center{
	position: absolute;
    left: 85%;
    transform: translatex(-50%);
	z-index:1000;
	
}

.button-padding
{
	padding: 10px 15px;
}

.link-padding{
	padding: 10px 5px;
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

.milestone-link
{
	cursor:pointer;
}

.detail_div
{
	padding: 5px 5px;
	
}
.help_link
{
	font-size:10px;
	cursor:pointer;
	font-weight:bold;
}

.help_link:hover
{
	text-decoration: underline;
}
.hr_help
{
	margin-bottom: 0px;
}

.container-gap
{
	margin-top: 85px;
}


</style>


<script type="text/javascript">
	function handleResize() {
		var width = $(window).width();
	
		if (width <= 991) {
			$("#nav-banner").toggleClass("container", false);
			$("#nav-banner").toggleClass("container-fluid", true);
			
			$("p.mobile").each(function () {
				$(this).show();
			});
	
			$("p.desktop").each(function () {
				$(this).hide();
			});
			
			$(".role-mobile").each(function () {
				$(this).show();
			});
		
			$(".role-desktop").each(function () {
				$(this).hide();
			});
		
		} 
		else {
			$("#nav-banner").toggleClass("container-fluid", false);
			$("#nav-banner").toggleClass("container", true);
			$("p.mobile").each(function () {
				$(this).hide();
			});
	
			$("p.desktop").each(function () {
				$(this).show();
			});
			
			$(".role-mobile").each(function () {
				$(this).hide();
			});
		
			$(".role-desktop").each(function () {
				$(this).show();
			});
			
		}
	}
	
	function logoutMsg()
	{
		alert("To log out of the RHD Research Excellence, please close all windows for this browser OR if your are using a Mac quit the browser");	
	}
	
	$(document).ready(function() 
	{
		handleResize();
	
		$(window).resize(handleResize);
		
		$(".panel.panel-default").matchHeight();
	
		$(".collapse.desktop").on("show.bs.collapse", function () {
			$(".collapse.in").each(function(){
				$(this).collapse("hide");
			});
		});
		
		$('.create_milestone').click(function(e) {
			var data = $.parseJSON($(this).attr('data-button'));
			//alert(data.stu_id);
			console.log('milestone_id:'+ data.milestone);
			
			var pass_data = {
				milestone_id: data.name_id,
				milestone: data.milestone,
				milestone_due_date: data.due_date,
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
						var redirect_url = 'https://rex.flinders.edu.au/items/' + uuid + '/' + version + '/?token=' + token;
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
			
			var url = <?php echo json_encode(base_url('cs/dashboard/create_examination')); ?>;
			
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
						var redirect_url = 'https://rex.flinders.edu.au/items/' + uuid + '/' + version + '/?token=' + token;
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

			var newURL = 'https://flextra.flinders.edu.au/rex/index.php/cs/dashboard/rexLink/' + uuid+'/'+ version;
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

<!--<nav class="navbar navbar-inverse navbar-collapse">
-->
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div id="nav-banner">
        <div class="navbar-header">
            <a class="navbar-brand" href="<?php echo base_url('cs/main'); ?>">RHD Research Excellence</a>
        </div>
     
			<?php
                if(isset($this->session->userdata['user_role']) && $this->session->userdata['user_role'] == 'rhd_stu_and_sup')
                { ?>
                
                    <ul class="nav navbar-nav navbar-center role-desktop">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                               aria-expanded="false">Switch Role <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo base_url('cs/student'). '/'. $this->session->userdata['stu_id'] .'/'; ?>">Student</a></li>
                                <li><a href="<?php echo base_url('cs/supervisor'). '/'. $this->session->userdata['sup_id'] .'/'; ?>">Supervisor</a></li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right role-desktop">
                    	<li><a href="#" onclick = "javascript:logoutMsg();"><i class="glyphicon glyphicon-off"></i> &nbsp;Log out</a></li>
                    </ul>
            <?php 	
                }
                else
                { ?>
                        
                    <ul class="nav navbar-nav navbar-center role-desktop">
                         <li class="brand"><i class="glyphicon glyphicon-user"></i> <span class="right"><?php if(isset($this->session->userdata['fan'])){echo $this->session->userdata['fan'];} else {echo 'You are not logged in';} ?></span></li>
                    </ul>
                     <ul class="nav navbar-nav navbar-right role-desktop">
                    	<li><a href="#" onclick = "javascript:logoutMsg();"><i class="glyphicon glyphicon-off"></i> &nbsp;Log out</a></li>
                    </ul>
            <?php }
            ?>
    
    		<?php
			if(isset($this->session->userdata['user_role']) && $this->session->userdata['user_role'] == 'rhd_stu_and_sup')
			{ ?>
                <ul class="nav navbar-nav navbar-right role-mobile">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                           aria-expanded="false"><span class="glyphicon glyphicon-menu-hamburger"></span></a>
                        <ul class="dropdown-menu">
                                    <li><a href="<?php echo base_url('cs/student'). '/'. $this->session->userdata['stu_id'] .'/'; ?>">Student</a></li>
                                    <li><a href="<?php echo base_url('cs/supervisor'). '/'. $this->session->userdata['sup_id'] .'/'; ?>">Supervisor</a></li>
                                    <li><a href="#" onclick = "javascript:logoutMsg();">Log out</a></li>
                        </ul>
                    </li>
                </ul>
            <?php 	
                }
                else
                { ?>
                        
                    <ul class="nav navbar-nav navbar-right role-mobile">
                         <li class="brand"><i class="glyphicon glyphicon-user"></i> <span class="right"><?php if(isset($this->session->userdata['fan'])){echo $this->session->userdata['fan'];} else {echo 'You are not logged in';} ?></span></li>
                    </ul>
                    
            <?php }
            ?>
         
    </div>
</nav>

   