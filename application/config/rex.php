<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Institute URL
|--------------------------------------------------------------------------
|
| Must be HTTPS
|
*/

$config['institute_url'] = 'https://rex.flinders.edu.au/';

$config['flextra_url'] = 'https://flextra.flinders.edu.au/rex/index.php/cs/dashboard/rexLink/';

// 17 APR 15 - connect to prod for testing/development AC
#$config['institute_url'] = 'https://flex.flinders.edu.au/'; 

/*
|--------------------------------------------------------------------------
| Oauth Access Token URL
|--------------------------------------------------------------------------
|
| URL to get OAuth Token
|
*/
$config['access_token_url'] = $config['institute_url'] . 'oauth/access_token';

/*Obsoleted
|--------------------------------------------------------------------------
| OAuth client identifier
|--------------------------------------------------------------------------
|
| OAuth client identifier configured in Equella
|
*/
#$config['client_id'] = '80416726-a0bb-44f5-917e-a913dd6eeeea';

/*Obsoleted
|--------------------------------------------------------------------------
| OAuth client secret
|--------------------------------------------------------------------------
|
| OAuth client secret configured in Equella
|
*/
#$config['client_secret'] = 'e09ed33e-09d9-4d55-b498-856d78efdfc4';
// client secret for prod
#$config['client_secret'] = 'ef8b8ffc-4d8c-4bbd-9a8e-b74fcfefecd5';

//client secret for dev
#$config['client_secret'] = '6887c51b-f566-4960-9447-7f8919e1c1b3';

/*
|--------------------------------------------------------------------------
| OAuth client ids and secrets
|--------------------------------------------------------------------------
|
| OAuth client ids and secrets configured in Equella for Flextra PHP REST
|
*/
$config['oauth_clients'] = array(
    'default' => array(
        'client_flex_user' => 'flex.webservice',
        'client_id' => '80416726-a0bb-44f5-917e-a913dd6eeeea',
        'client_secret' => 'ef8b8ffc-4d8c-4bbd-9a8e-b74fcfefecd5'
    ),
    'rhd' => array(
        'client_flex_user' => 'rhd.webservice',
        'client_id' => '94b8a51b-bcc8-442e-9ff6-c0ea954681e6',
        'client_secret' => '8de295c0-b114-481b-b7c6-5899bebbaaca'
    )
);

/*
|--------------------------------------------------------------------------
| REST debug log
|--------------------------------------------------------------------------
|
| Whether to turn on REST debug log, log messages are printing to PHP system log
|
*/
$config['rest_debug'] = false;

/*
|--------------------------------------------------------------------------
| REST HTTP debug log
|--------------------------------------------------------------------------
|
| Whether to turn on REST HTTP debug log, log messages are printing to PHP system log
|
*/
$config['rest_debug_http'] = false;

/*
|--------------------------------------------------------------------------
| Prefer CURL library
|--------------------------------------------------------------------------
|
| Whether to ure OPENSSL or CURL for REST HTTPS connection
| 0 - OPENSSL, 1 - CURL
|
*/
$config['prefer_curl'] = 0;


#$config['dialog_url'] = $config['institute_url'] . 'oauth/access_token';
#$config['redirect_uri'] = 'https://'.$_SERVER['HTTP_HOST'].
#		dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/test_oauth_client.php';


/*
|--------------------------------------------------------------------------
| Soap interfaces
|--------------------------------------------------------------------------
|
| Standard, Taxonomy, Course, Activation
| 
|
*/
$config['soap_standard_intf'] = 'services/SoapService51';
$config['soap_taxonomy_intf'] = 'services/taxonomyTerm.service';
$config['soap_course_intf'] = 'services/calcourses.service';
$config['soap_activation_intf'] = 'services/calactivation.service';

/*
|--------------------------------------------------------------------------
| Soap institute url, login username and password
|--------------------------------------------------------------------------
|
| Soap institute url, login username and password
| 
|
*/
$config['institute_url_soap'] = $config['institute_url'];
#$config['institute_url_soap'] = 'https://flex-dev.flinders.edu.au/';

$config['soap_username'] = 'flex.designer';
$config['soap_password'] = 'QiuDouble8';

#for ereadings activation only:
$config['soap_activation_username'] = 'lib.activate';
$config['soap_activation_password'] = 'sunshine14coast';

#for ereadings course management:
$config['soap_coursemgt_username'] = 'lib.sys';
$config['soap_coursemgt_password'] = 'gold13coast';


/*
|--------------------------------------------------------------------------
| TEST Logging
|--------------------------------------------------------------------------
|
| Separate logging path/files for reading rollover/activation
|
| Log levels: CRITICAL, ERROR, WARNING, INFO, DEBUG.
*/

$config['testlog'] = array(
    'test' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => "{date}: {message}",
        'file_path' => 'test' // Will save log files under application/logs/test
    )
);

$config['log'] = array(
    'cs' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => "{date}: {message}",
        'file_path' => 'cs' // Will save log files under application/logs/cs
    ),
	'rhd' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => "{date}: {message}",
        'file_path' => 'rhd' // Will save log files under application/logs/rhd
	)
);





/* Obsoleted
|--------------------------------------------------------------------------
| User group requierd for the eReading rollover and activation function
|--------------------------------------------------------------------------
|
| Equella User group requierd for the eReading rollover and activation function
|
|
*/
#$config['usergrp1_activation'] = 'EQ contributor';
#$config['usergrp1_activation'] = 'LIB-eReadings sup-contributor';
#$config['usergrp2_activation'] = 'LIB Test eReadings';



/*
|--------------------------------------------------------------------------
| Shared Secret for SAM
|--------------------------------------------------------------------------
|
| Used to get the signature picture in SAM PDF
|
*/
/*
|--------------------------------------------------------------------------
| Shared Secret for SAM
|--------------------------------------------------------------------------
|
| Used to get the signature picture in SAM PDF
|
*/
$config['sam_shared_secret_username'] = 'flex.webservice';
$config['sam_shared_secret_id'] = 'SAMWeb';
$config['sam_shared_secret_value'] = 'MetGq3Resu';


/*
|--------------------------------------------------------------------------
| Shared Secret for RHD
|--------------------------------------------------------------------------
|
| Used to get the signature picture in SAM PDF
|
*/
$config['rhd_shared_secret_username'] = 'rhd.webservice';
$config['rhd_shared_secret_id'] = 'RHDWeb';
$config['rhd_shared_secret_value'] = 'R1hD3wEbsFlex';


/*
|--------------------------------------------------------------------------
| Milesstone Collection ID
|--------------------------------------------------------------------------
|
| Milesstone Collection ID
|
*/
$config['milestone_collection'] = '1ef133a1-7853-422d-8562-ccb47fec39c6';
$config['examination_collection'] = '27f6749b-da4a-4e18-aeea-86ce652780ae';


/*
|--------------------------------------------------------------------------
| LDAP configuration
|--------------------------------------------------------------------------
|
| LDAP common read only DN and password
|
*/
$config['ldap_server'] = 'ldapauth.flinders.edu.au';
$config['ldap_dn'] = 'cn=cedict_ro,ou=isd,o=flinders';
$config['ldap_pwd'] = 'sit-yYxF8';

/*
|--------------------------------------------------------------------------
| Google Youtube API
|--------------------------------------------------------------------------
|
*/
$config['google_oauth2_client_id'] = '643418898119-30eda321onv1fs2lss9vmue1397pe9p9.apps.googleusercontent.com';
$config['google_oauth2_client_secret'] = 'cK5x1YKO9ajtPnrF-4Kk47aL';
#$config['google_oauth2_client_key'] = '{"access_token":"1ya29.OQKLdZ8VmdpJfE6ljERoBqR4f6WTKgXaYV0Te7yxyN4JJbuZKsXzsgjpMpKi-VX5jjw1","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/nOGtKNskF6k7_JYX0VXEbqR0L2Rw3yO8v6Y6rlzuoc1IgOrJDtdun6zK6XiATCKT","created":1448595620}';
$config['google_oauth2_client_key'] = '{"access_token":"ya29.iQJrLMGmI8ewRjTRfkEKT0I4rWy06wGtBrvAbrvpB7eMtGSs5NLoqUp7Q1Q7JNgT77iO","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/AKgky-8YY1bvALIzA00MWJ2cadIQKzYq_Dq9j_YzxiJIgOrJDtdun6zK6XiATCKT","created":1455499284}';

/*
|--------------------------------------------------------------------------
| Equella Taxonomies
|--------------------------------------------------------------------------
|
*/
$config['rhd_schools_taxonomy_uuid'] = 'c5331878-7bc2-434b-b37b-47204079342d';

/*
|--------------------------------------------------------------------------
| the privileges of ldap group and rex internal group
|--------------------------------------------------------------------------
|
*/
$config['rex_chk_flex_groups'] = true;
$config['rex_ldap_groups'] = array(

    "flextra" => array(
        0 => "cs"
    )
);

$config['rex_flex_groups'] = array(
    "RHD College admin grp" => array(
        0 => "cs"
    ),

    "RHD Central admin" => array(
        0 => "cs"
    ),
);