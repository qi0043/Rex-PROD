<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Check LDAP user roles.
 * 
 */

class Ldap extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$ci->load->config('flex');
		$this->load->helper('url');
		$this->load->library('flexrest/flexrest');
		$this->load->library('ldap/ldap');
		
	
	
		$fan = $_SERVER['REMOTE_USER'];
		
		echo $fan;
		/*if(!$this->ladp->success)
		{   
		    echo 'LDAP error!';
			exit;
		}
		
		$fan = 'qi0043';
		
		$ldap_user = $this->ldap->get_attributes($fan);
		
		$ldap_groups = $this->ldap->get_groups_of_member($fan);
		
		if(!$this->ldap->success)
		{    
			echo 'LDAP CI error!';
			exit;
		}
		
		echo '<pre> LDAP USER';
		print_r($ldap_user);  
		echo '</pre>'; 
		
		echo '<pre> LDAP group';
		print_r($ldap_groups);  
		echo '</pre>';
	*/
	}
	
}

?>
