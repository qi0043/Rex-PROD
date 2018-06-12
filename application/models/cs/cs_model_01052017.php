<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cs_model extends CI_Model {
    
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    
		//flextra-dev database
		$this->postgre_db = $this->load->database('postgre', true);
		
		//equella database
		$this->eq_db = $this->load->database('equella', true);
		
    }
    
    function db_get_rhd_student_info($id)
    {
		$sql_get_stu_info ="SELECT * FROM tbl_rhd_students WHERE id = ?  AND stusys_active = 'true'";
			
        $query = $this->postgre_db->query($sql_get_stu_info, $id);

        if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        }
	}
	
	function db_match_stu_fan($fan)
	{
		$sql_get_stu_info ="SELECT id FROM tbl_rhd_students WHERE stu_fan LIKE ? AND stusys_active = 'true'";
		$query = $this->postgre_db->query($sql_get_stu_info, $fan);
		if ($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        else
        {
            return 0;
        }

	}
	
	function db_match_sup_fan($fan)
	{
		$sql_get_sup_info ="SELECT id FROM tbl_rhd_supervisors WHERE supv_fan LIKE ? AND stusys_active = 'true'";
		$query = $this->postgre_db->query($sql_get_sup_info, $fan);
		if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        }
	}
	
	function db_get_all_stu_info($id)
	{
		$sql = "SELECT * FROM tbl_rhd_students, tbl_rhd_stud_supv WHERE tbl_rhd_students.id = tbl_rhd_stud_supv.rhd_stu_id AND tbl_rhd_students.id = ?";
		
		$query = $this->postgre_db->query($sql, $id);
		
		if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        }
	}
	
	
	
	function db_get_stu_sup_info($id)
	{
		$sql = "SELECT * FROM tbl_rhd_stud_supv WHERE rhd_stu_id = ?";
		
		$query = $this->postgre_db->query($sql, $id);
		
		if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        }
	}
	
	function db_get_sup_info($id)
	{
		$sql = "SELECT * FROM tbl_rhd_stud_supv WHERE rhd_supv_id = ? AND stusys_active = 'true'";
		
		$query = $this->postgre_db->query($sql, $id);
		
		if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        } 
	}
	
	function db_get_sup_details($id)
	{
		$sql = "SELECT * FROM tbl_rhd_stud_supv, tbl_rhd_students WHERE tbl_rhd_stud_supv.rhd_stu_id = tbl_rhd_students.id AND rhd_supv_id = ? ORDER BY tbl_rhd_stud_supv.supv_role desc, tbl_rhd_students.family_name";
		
		$query = $this->postgre_db->query($sql, $id);
		
		if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        } 
	}
	
	function db_get_all_sup_info($id)
	{
		$sql_get_sup_info ="SELECT * FROM tbl_rhd_supervisors WHERE id = ?";
		$query = $this->postgre_db->query($sql_get_sup_info, $id);
		if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        }
	}
	
	
	function db_get_postgrd_coord($discipline)
	{
		$sql = "SELECT * from vw_rhd_postgrad_coord WHERE discipline = ?";
		$query = $this->postgre_db->query($sql, $discipline);
		if ($query->num_rows() > 0)
        {
           return $query->result_array();
        }
        else
        {
            return 0;
        }
	}
	
	function db_get_stu_milestone($id)
	{
		$sql = "SELECT * from tbl_rhd_milestones WHERE rhd_stu_id = ?";
		$query = $this->postgre_db->query($sql, $id);
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