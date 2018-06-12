$(function() {
	    $(".draft-edit-button").mouseover(function() {
    		$( this ).parent().prev().fadeOut( 100 );
  			$( this ).parent().prev().fadeIn( 500 );
		});
		$( "#btn_contribute" ).click(function(){
			 //$( "#myResources" ).html("Thesis Submission - basic information");
			 $( ".submissionForm" ).show();
			 $( "#btn_contribute" ).prop("disabled", true);
			 $( "#btn_contribute" ).hide();
			// $( ".button_group" ).hide();
			 //$( ".button_group" ).css("display", "none");
			// $( ".deposit_des" ).hide();
			 //$( ".deposit_des" ).css("display", "none");
			// $( ".about_area" ).hide();
			 //$( ".about_area" ).css("display", "none");
			 //$( ".status_area").hide();
			 //$( ".status_area").css("display", "none");
		}); 
		
		$( "#cancel_thesis_btn" ).click(function(){
			 $(".spinner-wave").fadeOut('slow');
			// $( "#myResources" ).html("My Resources");
			 $( ".submissionForm" ).hide();
			 $( "#btn_contribute" ).prop("disabled", false);
			 $( ".button_group" ).show();
			 $( ".deposit_des" ).show();
			 $( ".about_area" ).show();
			 $( "#btn_contribute" ).show();
			 $( "#submit_thesis_btn" ).prop("disabled", false);
			 
			// $( ".status_area").show();
		});
		
		$( "#submit_thesis_btn" ).click(function(){
			//check thesis type
			var thesisType = $("#selType option:selected").text();
			if(thesisType == "Master by Coursework")
			{
				alert("Masters by Coursework requires mandatory submission of print bound copies.\n\n"+
				"For instructions on submitting your final thesis for Masters by Coursework see: \n\n"+
				"\t\t\t\t\thttp://flinders.libguides.com/thesisdeposit. \n\n"+
				"Please cancel this submission using the cancel button.");
				return false;
			}

			var stuID = $.trim($("#stuID").val());
		    var prfStuName = $.trim($("#prfStuName").val());
			var prfStuLastName = $.trim($("#prfStuLastName").val());
		    var stuEmail = $.trim($("#stuEmail").val());
		    var supName = $.trim($("#supName").val());
		    var thesisType = $("#selType option:selected").text();
			var school = $("#selSchool option:selected").text();
		    var title = $.trim($("#thesisTitle").val());
			var compYear = $.trim($("#compYear").val())
		  
		    if(stuID.length <= 0 || prfStuName.length <= 0 || prfStuLastName.length <= 0 || stuEmail.length <= 0 || supName.length <= 0 ||thesisType.length <= 0 ||title.length <= 0 ||school.length <= 0)
		    {
			  alert("Please fill in the requested information.");
			  $( "#submit_thesis_btn" ).prop("disabled", false);
			  return false;
		    }
			//check email 
			var stuEmail = $.trim($("#stuEmail").val());
			var supEmail = $.trim($("#supEmail").val());
			if(!validateEmail(stuEmail))
			{
				alert('Preferred Email Contact is not valid');
				$("#stuEmail").focus();
				return false;
		    }
			
			if(supEmail!= '' && !validateEmail(supEmail))
			{
				alert('Principal Supervisor Email is not valid');
				$("#supEmail").focus();
				return false;
			}
			
			if(compYear != '')
			{
				var subYear = compYear.toString().substring(0,2);
				if( subYear!= '20' || Math.floor(compYear) != compYear || $.isNumeric(compYear) == false || compYear.length != 4)
				{
					//var currentYear = new Date().getFullYear();
					alert('Year of completion is not valid');
					$("#compYear").focus();
					return false;
				}
			}
			
			submit_request();
		}); 

		function submit_request()
    	{
          $( "#submit_thesis_btn" ).prop("disabled", true);
			var stuID = $.trim($("#stuID").val());
			var prfStuName = $.trim($("#prfStuName").val());
			var prfStuLastName = $.trim($("#prfStuLastName").val());
			var stuEmail = $.trim($("#stuEmail").val());
			var supName = $.trim($("#supName").val());
			var thesisType = $("#selType option:selected").text();
			var title = $.trim($("#thesisTitle").val());
			var compYear = $.trim($("#compYear").val())+'-01-01';
			var supEmail = $.trim($("#supEmail").val());
			var school = $.trim($("#selSchool option:selected").text());
		 	var url = <?php echo json_encode(base_url('rhd/rhdMgt/createRHD')); ?>;
			
          	var posting = $.post( url, 
                    { "stuID" : stuID,
                      "prfStuName" : prfStuName,
					  "prfStuLastName" : prfStuLastName,
                      "stuEmail" : stuEmail,
                      "supName" : supName,
                      "thesisType" : thesisType,
					  "title": title,
					  "compYear": compYear,
					  "supEmail": supEmail,
					  "school": school
                     }
                );
		   $(".spinner-wave").show();
		   posting.done(function( data,status ) {
			   var resultobj = jQuery.parseJSON(data);
              if(status == 'success')
              {
				   var resultobj = jQuery.parseJSON(data);
             	   var result_status = resultobj.status;
				   switch(result_status)
				   {
					   case 'itemExists':
					   		$(".spinner-wave").fadeOut('slow');
					   		alert( "Thesis title already exists" );
							$( "#submit_thesis_btn" ).attr("disabled", false);
					   break;
					   case 'success':
					       var uuid = resultobj.uuid;
						   var version = resultobj.version;
						   var title = resultobj.itemName;
						   var token = resultobj.token;
						   
						   $( ".submissionForm" ).hide();
						   $( "#submit_thesis_btn" ).hide();
						   $( ".deposit_des" ).hide();
						   $( ".about_area" ).show();
						   var href = 'https://flex.flinders.edu.au/items/'+ uuid + '/' + version + '?token='+token;
						   var equellaLink = $('<a>',{
							class: 'equellaLink',
							id:'equellaLink',
    						text: title,
							title: title,
    						href: href
						  }).appendTo('.responseLi');
						 
						  var ul_drafts = $('<ul>', {
							  class: 'ul_drafts',
							  id: 'temp_ul'
						  }).appendTo('.responseLi');
							
						  var date = new Date();
						  
						  var li_drafts = $('<li>', {
							  class: 'li_drafts',
							  text: ' Created date: ' + date
						  }).appendTo('#temp_ul');
						  
						  var li_draft = $('<li>', {
							  class: 'li_drafts',
							  text: 'Last modified date: ' + date 
						  }).appendTo('#temp_ul');
						  
						  var li_draft = $('<li>', {
							  class: 'li_drafts',
							  html: ' <strong> Status: Registration complete - no thesis deposit </strong>' 
						  }).appendTo('#temp_ul');
							
							
						  var equellaButton = $('<a>',{
							class: 'btn btn-primary',
							id:'equellaButton',
    						text: ' Thesis Deposit ',
							title:' Thesis Deposit ',
    						href: href
						  }).appendTo('.responseButton');

						   $('.responseArea').show();
						
						   $(".spinner-wave").fadeOut('slow');
						  // location.reload(true);
					   break;
				   }
              }
			  else
              {
				  $(".spinner-wave").fadeOut('slow');
				  //$( "#myResources" ).html("My Resources");
                  $( "#submit_thesis_btn" ).prop("disabled", false);
              }
              
          });
          posting.fail(function(xhr, status, error) {
			  $(".spinner-wave").fadeOut('slow');
			 // $( "#myResources" ).html("My Resources");
			alert("Error: " + xhr.status + " " + error);
			$( "#submit_thesis_btn" ).attr("disabled", false);
          });
    	}
	
	
		 function validateEmail($email) {
 			 var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
  			return emailReg.test( $email );
		 }
	});