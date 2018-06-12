<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cs_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

		//flextra-dev database
		$this->postgre_db = $this->load->database('postgre', true);

        //equella database
        // Code to fix: Not addressed in model.
		//$this->eq_db = $this->load->database('equella', true);

		// rex database
		$this->rex_db = $this->load->database('rex', true);
    }

    function db_chk_notice()
    {
        $sql_get_notice = "SELECT * from tbl_notices
                           WHERE tbl_notices.context = 'REX'
                           AND tbl_notices.start_ts < now() AND tbl_notices.end_ts > now()";

        $query = $this->postgre_db->query($sql_get_notice);

        if ($query->num_rows() > 0)
        {
           $result = $query->result_array();
           return $result[0];
        }
        else
        {
            return false;
        }
    }

    function db_get_rhd_student_info($id)
    {
		$sql_get_stu_info ="SELECT * FROM tbl_rhd_students WHERE id = ?";

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
		$sql_get_stu_info ="SELECT id, stu_fan FROM tbl_rhd_students WHERE stu_fan LIKE ? AND stusys_active = true";
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

	function db_get_rhd_student_info_by_fan($fan)
    {
        $sql_get_stu_info ="SELECT * FROM tbl_rhd_students WHERE stu_fan LIKE ?";
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
		$sql_get_sup_info ="SELECT id, supv_fan FROM tbl_rhd_supervisors WHERE supv_fan LIKE ? AND stusys_active = true";
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
        $sql = "select * from tbl_rhd_stud_supv where rhd_stu_id = ? AND stusys_active = true AND current_timestamp between start_date AND COALESCE(end_date, current_timestamp) ";

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

    function db_get_stu_sup_info_by_fan($stu_fan)
    {
        $sql = "select * from vw_rhd_student_supv where stu_fan = ?";

        $query = $this->postgre_db->query($sql, $stu_fan);

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
		$sql = "SELECT * FROM tbl_rhd_stud_supv WHERE rhd_supv_id = ? ";

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

	function db_get_sup_id_by_fan($fan){
        $sql = "SELECT id FROM tbl_rhd_supervisors WHERE supv_fan LIKE ?";
        $query = $this->postgre_db->query($sql, $fan);
        if($query->num_rows() > 0){
            $result = $query->result_arrary();
            return $result[0];
        }else{
            return false;
        }
    }

	function db_get_sup_details($id)
	{
		$sql = "SELECT * FROM tbl_rhd_stud_supv, tbl_rhd_students WHERE tbl_rhd_stud_supv.rhd_stu_id = tbl_rhd_students.id AND tbl_rhd_stud_supv.stusys_active = true AND rhd_supv_id = ? ORDER BY tbl_rhd_stud_supv.supv_role desc, tbl_rhd_students.family_name";

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

    function db_get_all_sup_info_by_fan($supv_fan)
    {
        $sql_get_sup_info ="SELECT * FROM tbl_rhd_supervisors WHERE supv_fan = ?";
        $query = $this->postgre_db->query($sql_get_sup_info, $supv_fan);
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

    function db_get_stu_milestone_by_fan($stu_fan)
    {
        $sql = "SELECT * from vw_rhd_student_milestone WHERE stu_fan = ?";
        $query = $this->postgre_db->query($sql, $stu_fan);
        if ($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        else
        {
            return 0;
        }
    }

	public function db_get_postgrad_students($fan) {
        $sql = "SELECT * FROM vw_rhd_postgradcoord_students WHERE postgradcoord_fan = ?";
        $query = $this->postgre_db->query($sql, $fan);

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

    public function db_get_postgrad_coordinator_students($fan) {
        $sql = "SELECT
    CAST((xpath('//xml/item/people/candidate/last_names/text()', XMLPARSE(CONTENT item_xml.XML)))[1] AS TEXT) || ', ' ||
        CAST((xpath('//xml/item/people/candidate/first_names/text()', XMLPARSE (CONTENT item_xml.XML)))[1] AS TEXT) \"student_name\",
    CAST((xpath('//xml/item/curriculum/courses/course/code/text()', XMLPARSE(CONTENT item_xml.XML)))[1] AS text) \"course_code\",
     CAST((xpath('//xml/item/people/candidate/fan/text()', XMLPARSE(CONTENT item_xml.XML)))[1] AS text) \"stu_fan\",
    (
        CASE UPPER(CAST((xpath('//xml/item/candidature/load/FTE_type/text()', XMLPARSE(CONTENT
                item_xml.XML)) )[1] AS TEXT))
            WHEN 'FULL TIME'
            THEN 'FT'
            WHEN 'PART TIME'
            THEN 'PT'
            ELSE NULL
        END) \"load\",
    TO_CHAR(CAST((xpath('//xml/item/candidature/@start_date', XMLPARSE(CONTENT item_xml.XML)))[1] AS TEXT)::DATE, 'DD Mon YYYY') \"start_date\",
    TO_CHAR(CAST((xpath('//xml/item/thesis/expected_submission_date/text()', XMLPARSE(CONTENT item_xml.XML)))[1] AS TEXT)::DATE, 'DD Mon YYYY') \"expected_submission_date\",
    (CASE
            WHEN CAST((xpath('//xml/item/sys_workflow/postgraduate_coordinator/text()', XMLPARSE
                (CONTENT item_xml.xml)))[1] AS TEXT) = CAST((xpath
                ('//xml/item/sys_workflow/principal_supervisor/text()', XMLPARSE(CONTENT
                item_xml.xml)))[1] AS TEXT)
            THEN TRUE
            ELSE FALSE
        END) is_principal_supv,
    CAST((xpath('//xml/item/sys_workflow/principal_supervisor_name/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"principal_supervisor_name\",
    substr(CAST((xpath('//xml/item/itembody/name/text()', XMLPARSE(CONTENT item_xml.XML)))[1] AS text),position('::' in CAST((xpath('//xml/item/itembody/name/text()', XMLPARSE(CONTENT item_xml.XML)))[1] AS text))+3) \"milestone_name\",
    (CASE item.status WHEN 'LIVE' then 'finalised' ELSE LOWER(item.status) END) \"status\",
    (SELECT TEXT FROM language_string WHERE bundle_id = workflow_node.name_id) \"stage\",
    (SELECT url FROM institution) \"rex_url\",
    (SELECT url FROM institution) || 'items/' || item.uuid || '/' || item.version \"rex_item_url\",
    item.uuid \"item_uuid\",
    item.version  \"item_version\",
    workflow_node.uuid \"mod_uuid\"
FROM item
JOIN item_xml ON item.item_xml_id = item_xml.id
JOIN item_definition ON item.item_definition_id = item_definition.id
JOIN base_entity ON base_entity.id = item_definition.id
LEFT JOIN task_history ON task_history.item_id = item.id AND task_history.exit_date IS NULL
LEFT JOIN workflow_node ON workflow_node.id = task_history.task_id
WHERE base_entity.uuid = '1ef133a1-7853-422d-8562-ccb47fec39c6'
  AND item.status <> 'DELETED'
  AND CAST((xpath('//xml/item/sys_workflow/postgraduate_coordinator/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) = ?
ORDER BY student_name, CAST((xpath('//xml/item/progression/milestone/@name_id', XMLPARSE(CONTENT item_xml.xml)) )[1] AS TEXT)::int desc
";
        $query = $this->rex_db->query($sql, $fan);

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

    public function db_get_match_coordinator_fan($fan) {
        $sql = "SELECT public.tbl_rhd_users.*, tbl_rhd_roles.role_name FROM public.tbl_rhd_users
                INNER JOIN public.tbl_rhd_user_roles
                ON tbl_rhd_users.id = tbl_rhd_user_roles.rhd_user_id
                INNER JOIN public.tbl_rhd_roles
                ON tbl_rhd_user_roles.rhd_role_id = tbl_rhd_roles.id
                WHERE (tbl_rhd_roles.role_name = 'RHD Coordinator' OR tbl_rhd_roles.role_name = 'College Coordinator')
                AND tbl_rhd_user_roles.active = TRUE
                AND tbl_rhd_users.fan = ?";

        $query = $this->postgre_db->query($sql, $fan);
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

	/************* Update data in RHD milestones *********************/
    public function get_vw_rhd_mod_milestone($fan)
    {
        $sql = "SELECT * FROM vw_rhd_mods_milestones WHERE stu_fan = ?";
        $query = $this->postgre_db->query($sql, $fan);
        return $query->result_array();
    }

    public function get_vw_rhd_mod_student($fan)
    {
        $sql = "SELECT * FROM vw_rhd_mods_students WHERE stu_fan = ?";
        $query = $this->postgre_db->query($sql, $fan);
        return $query->result_array();
        
    }

    public function get_vw_rhd_mod_stusupv($fan)
    {
        $sql = "SELECT * FROM vw_rhd_mods_stusupv WHERE stu_fan = ?";
        
              
        $query = $this->postgre_db->query($sql, $fan);

   
        return $query->result_array();
    }

    public function get_vw_rhd_mod_stusupv_all()
    {
        $sql = "SELECT * FROM vw_rhd_mods_stusupv";
        $query = $this->postgre_db->query($sql);
   
        return $query->result_array();
    }

    public function get_vw_rhd_postgradcoord_students($fan)
    {
        $sql = "SELECT * FROM vw_rhd_postgradcoord_students_tmp WHERE stu_fan = ?";

              
        $query = $this->postgre_db->query($sql, $fan);

              
        return $query->result_array();
    }

    public function get_vw_rhd_postgradcoord_students_all()
    {
        $sql = "SELECT * FROM vw_rhd_postgradcoord_students_tmp";

              
        $query = $this->postgre_db->query($sql, $fan);

              
        return $query->result_array();
    }


    public function vw_rhd_postgrad_coord()
	{
        $sql = "SELECT * FROM vw_rhd_postgrad_coord";

        $sql = "SELECT * FROM vw_rhd_postgradcoord_students WHERE stu_fan = ?";
        echo "<pre>";
        echo "<h3>SQL</h3>";
        $query = $this->postgre_db->query($sql);
        $qs = $this->postgre_db->last_query();
        echo $qs; 

        echo "</pre>";
		return $query->result_array();
	}

	public function vw_rhd_mod_milestones()
	{
		$sql = "SELECT * FROM vw_rhd_mods_milestones";
		$query = $this->postgre_db->query($sql);
		return $query->result_array();
	}

	public function vw_rhd_mod_students()
	{
		$sql = "SELECT * FROM vw_rhd_mods_students";
		$query = $this->postgre_db->query($sql);
        return $query->result_array();
	}

	public function vw_rhd_mod_stusupv()
	{
		$sql = "SELECT * FROM vw_rhd_mods_stusupv";
		$query = $this->postgre_db->query($sql);
        return $query->result_array();
	}

	public function get_stu_milestone($uuid, $fan)
	{
        $sql = "SELECT item.uuid \"item_uuid\", item.version  \"item_version\", item.status \"item_status\",
                CAST((xpath('//xml/item/thesis/title/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"title\",
                CAST((xpath('//xml/item/thesis/expected_submission_date/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"expected_submission_date\",
                CAST((xpath('//xml/item/data_update/student/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"student_update\",
                CAST((xpath('//xml/item/data_update/supervisors/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"supervisor_update\",
                CAST((xpath('//xml/item/people/candidate/fan/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"fan\",
                CAST((xpath('//xml/item/itembody/name/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"item_name\"
				FROM item
				JOIN item_xml ON item.item_xml_id = item_xml.id
				JOIN item_definition ON item.item_definition_id = item_definition.id
				JOIN base_entity ON base_entity.id = item_definition.id
				WHERE base_entity.uuid = ?
  				AND item.status = 'DRAFT'
  				AND CAST((xpath('//xml/item/people/candidate/fan/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) = ?
				";
        $query = $this->rex_db->query($sql, array($uuid, $fan));
        
        if ($query->num_rows() > 0) {

            return $query->result_array();
            


        } else {
            return 0;
        }
    }
    
    public function get_sup_milestone($uuid, $fan)
	{
        $sql = "SELECT item.uuid \"item_uuid\", item.version  \"item_version\", item.status \"item_status\",
                CAST((xpath('//xml/item/thesis/title/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"title\",
                CAST((xpath('//xml/item/thesis/expected_submission_date/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"expected_submission_date\",
                CAST((xpath('//xml/item/data_update/supervisor/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"supervisor_update\",
                CAST((xpath('//xml/item/people/candidate/fan/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"fan\",
                CAST((xpath('//xml/item/itembody/name/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"item_name\"
				FROM item
				JOIN item_xml ON item.item_xml_id = item_xml.id
				JOIN item_definition ON item.item_definition_id = item_definition.id
				JOIN base_entity ON base_entity.id = item_definition.id
				WHERE base_entity.uuid = ?
  				AND item.status = 'DRAFT'
  				AND CAST((xpath('//xml/item/people/candidate/fan/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) = ?
				";
        $query = $this->rex_db->query($sql, array($uuid, $fan));
        

        if ($query->num_rows() > 0) {

            return $query->result_array();
            


        } else {
            return 0;
        }
	}

    public function get_drafted_stu_milestone($uuid)
    {
        $sql = "SELECT item.uuid \"item_uuid\", item.version  \"item_version\", item.status \"item_status\",
                CAST((xpath('//xml/item/thesis/title/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"title\",
                CAST((xpath('//xml/item/thesis/expected_submission_date/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"expected_submission_date\",
                CAST((xpath('//xml/item/people/candidate/fan/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"fan\"
				FROM item
				JOIN item_xml ON item.item_xml_id = item_xml.id
				JOIN item_definition ON item.item_definition_id = item_definition.id
				JOIN base_entity ON base_entity.id = item_definition.id
				WHERE base_entity.uuid = ?
  				AND item.status = 'DRAFT'";
        $query = $this->rex_db->query($sql, $uuid);
        return $query->result_array();
    }


   

    public function get_duplicated_principal()
    {
        $sql = "SELECT stu_fan, supv_role, COUNT(1)
                FROM public.vw_rhd_mods_stusupv
                WHERE supv_role = 'Principal Supervisor'
                GROUP BY stu_fan,supv_role
                HAVING COUNT(1) > 1";
        $query = $this->postgre_db->query($sql);
        return $query->result_array();
    }

    public function get_duplicated_principal_by_fan($fan)
    {
        $sql = "SELECT stu_fan, supv_role, COUNT(1)
                FROM public.vw_rhd_mods_stusupv
                WHERE supv_role = 'Principal Supervisor'
                AND stu_fan = ?
                GROUP BY stu_fan,supv_role
                HAVING COUNT(1) > 1";
        $query = $this->postgre_db->query($sql, $fan);
        return $query->result_array();
    }

    public function get_principal($fan)
    {
        $sql = "SELECT * FROM public.vw_rhd_mods_stusupv
                WHERE supv_role = 'Principal Supervisor'
                AND stu_fan = ?";
        $query = $this->postgre_db->query($sql, $fan);
        return $query->result_array();
    }

    public function populate_daily_import($process_name, $update_status, $message)
    {
        $p_import_process = 'rex_rhd_' . $process_name;
        $sql = "SELECT * FROM f_admin_daily_import('" . $p_import_process . "', '" . date("Y-m-d H:i:s") . "', '"
            . $update_status . "', '" . $message . "')";
        $this->postgre_db->query($sql);
    }

    public function insert_error_item($name, $status, $fan, $uuid, $version, $base_uuid, $is_fixed, $error_type, $error_message)
    {
        $result = $this->get_collection_item($uuid, $error_type);
        if (count($result) === 0) {
            $sql = "INSERT INTO tbl_rhd_error_items (name, status, fan, uuid, version, base_uuid, is_fixed, error_type, error_message, last_created, last_modified)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $this->postgre_db->query($sql, array($name, $status, $fan, $uuid, $version, $base_uuid, $is_fixed, $error_type, $error_message));
        }
    }

    public function get_collection_item($uuid, $error_type)
    {
        $sql = "SELECT * FROM tbl_rhd_error_items WHERE uuid = ? AND error_type = ?";
        $query = $this->postgre_db->query($sql, array($uuid, $error_type));
        return $query->result_array();
    }

    public function get_error_items()
    {
        $sql = "SELECT name, status, fan, uuid, version, base_uuid, error_message, last_created
                FROM tbl_rhd_error_items
                WHERE is_fixed = 'N'";
        $query = $this->postgre_db->query($sql);
        return $query->result_array();
    }

    public function get_all_tables()
    {
        $sql = "SELECT * FROM pg_catalog.pg_tables";
        $query = $this->postgre_db->query($sql);
        return $query->result_array();
    }


    public function get_all_views()
    {
        $sql = "SELECT * FROM pg_catalog.pg_views";
        $query = $this->postgre_db->query($sql);
        return $query->result_array();
    }


    public function get_stu_milestone_not_live()
	{
        $sql = "SELECT item.uuid \"item_uuid\", item.version  \"item_version\", item.status \"item_status\",
                CAST((xpath('//xml/item/thesis/title/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"title\",
                CAST((xpath('//xml/item/thesis/expected_submission_date/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"expected_submission_date\",
                CAST((xpath('//xml/item/people/candidate/fan/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"fan\",
                CAST((xpath('//xml/item/itembody/name/text()', XMLPARSE(CONTENT item_xml.xml)))[1] AS TEXT) \"item_name\"
				FROM item
				
				WHERE item.status <> 'LIVE'
  				
				";
        $query = $this->rex_db->query($sql);
        if ($query->num_rows() > 0) {

            echo "<pre>";
        echo "<h3>SQL</h3>";
       

        $qs = $this->rex_db->last_query();
        echo $qs; 

        echo "</pre>";
            return $query->result_array();
        } else {
            return 0;
        }
	}





}
