<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notification extends CI_Controller {
	
	
	public function __construct()
    {
		
		parent::__construct();
		$ci = & get_instance();
		
	}
	
	public function auto_email($uuid, $version)
	{
		$this->load->library('email');
		
		$this->email->from('DoNotReply@flinders.edu.au');
		$this->email->to('qi0043@flinders.edu.au'); 
		$this->email->subject('Coursework thesis release date is not set');
	
		$mes = 'A coursework thesis release date is not set, uuid:' . $uuid . ', version: '. $version;
		
		$this->email->message($mes);	
		$this->email->send();
		return $mes;
		
	}
}
?>

