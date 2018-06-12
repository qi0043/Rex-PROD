<!DOCTYPE html>
<html lang="en">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<head>
	<title>Flinders University Topic Avalabilities</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="<?php echo base_url();?>resource/ocf/js/jquery-1.10.2.min.js"></script> 
    <script type="text/javascript" src="<?php echo base_url() . 'resource/rhd/';?>bootstrap-3.3.4-dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>bootstrap-3.3.4-dist/css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() . 'resource/rhd/';?>bootstrap-3.3.4-dist/css/bootstrap-theme.min.css" media="all">
   
    <style>
		.header
		{
			margin: 0 auto;
			/*background: #f2f2f2;*/
			padding-top: 20px;
    		padding-bottom: 20px;
			
		}	
		.header-inner
		{
			/*background: #f2f2f2;*/
			padding-top: 20px;
			padding-bottom: 20px;
			width:65%;
			margin: 0 auto;
			text-align:left;
		}
    </style>
</head>
<body>
<div class="wrap">
    <div class="container">
        <div class="header">
            <div class="header-inner">
            	<h1>SAMs Reports Dashboard - Prototype</h1>
            </div>
            <div>
            	Please let us know what you think about this report <a href="mailto:flex.help@flinders.edu.au?Subject=SAMs reports dashboard: Feedback" target="_top">flex.help@flinders.edu.au</a>
            <ul style="list-style-type: none; float:right">
            <li class="brand"><i class="glyphicon glyphicon-user"></i> <span class="right"><?php if(isset($_SESSION['fan'])){echo $_SESSION['fan'];}?></span></li>
            </ul>
            <hr/>
            </div>
        </div>
        
             
   
   