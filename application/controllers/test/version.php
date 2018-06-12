<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Version extends CI_Controller 
{
	 public function __construct()
     {
		parent::__construct();
		
		echo '<pre> PHP version';
		print_r(phpversion());  
		echo '</pre>';
	 }
	 
	 public function index()
	 {
		 
	 }
}