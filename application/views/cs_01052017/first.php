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
	var url = <?php echo json_encode(base_url('index.php/cs/dashboard/screen_detecter')); ?>;
	var pass_data = {
			width: screen.width,
			height: screen.height
	};	
	var posting = $.post(url, pass_data);	
	posting.done(function(data, status) {
		if(status == 'success')
		{
			//alert(data);
			var resultobj = jQuery.parseJSON(data);
			
			if(resultobj.status == 'success')
			{
				
			}
		}
	});
	posting.fail(function(xhr, status, error) {
		alert("Error: " + xhr.status + " " + error);
	});
	

	
	window.onresize = function(event) {
		var url = <?php echo json_encode(base_url('index.php/cs/dashboard/screen_detecter')); ?>;
		var pass_data = {
				width: screen.width,
				height: screen.height
		};	
		var posting = $.post(url, pass_data);	
		posting.done(function(data, status) {
			//alert(data);
			if(status == 'success')
			{
				var resultobj = jQuery.parseJSON(data);
				
				if(resultobj.status == 'success')
				{
					
				}
			}
		});
		posting.fail(function(xhr, status, error) {
			alert("Error: " + xhr.status + " " + error);
		});
    	
	};
});

</script>
</head>