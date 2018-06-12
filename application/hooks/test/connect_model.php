<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class connect_model extends CI_Model {
    
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    
		//flextra-dev database
		$this->postgre_db = $this->load->database('postgre', true);
		
		//equella database
		$this->eq_db = $this->load->database('equella', true);
		
    }
    
    function db_get_rhd_student_info()
    {
		$sql_get_thesesInfo ="SELECT * FROM tbl_rhd_students";
			
        $query = $this->postgre_db->query($sql_get_thesesInfo);

        if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        }
	}
	
}