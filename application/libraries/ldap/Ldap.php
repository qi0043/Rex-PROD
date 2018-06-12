<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
/**
 * library Ldap
 *
 * used to store all code relating to connecting to Flinders LDAP server
 */

if(!isset($_SESSION)){session_start();}


class Ldap {
	protected $ci, $server;
	protected $dn = "ou=people,o=flinders";
	protected $connect_id = null;
        protected $admin_dn;
        protected $admin_pwd;
	protected $bind_id = null;
	protected $fan;
        public   $success=true, $error_info='';

        function __construct($auto_bind = true)
        {
            $this->ci =& get_instance();
            $this->ci->load->config('rex');
            $this->server = $this->ci->config->item('ldap_server');
            $this->admin_dn = $this->ci->config->item('ldap_dn');
            $this->admin_pwd = $this->ci->config->item('ldap_pwd');
            
            if($auto_bind == true)
            {
                $this->connect_id = ldap_connect($this->server);
                try {
			$this->connect_id = ldap_connect($this->server);
		} catch (Exception $e) {
                        $this->success = false;
                        $this->error_info = 'LDAP connect failed!';
                        log_message('error', 'LDAP connect failed!');
			return false;
		}
                
                $rdn = $this->admin_dn;
		$this->bind_id = @ldap_bind($this->connect_id, $rdn, $this->admin_pwd);
		if (!$this->bind_id) {
                        $this->success = false;
                        $this->error_info = 'LDAP bind failed!';
			log_message('error', 'LDAP bind failed!');
		        return false;
		}
            }
        }
        
	function connect() {
		try {
			$this->connect_id = ldap_connect($this->server);
			if ($this->connect_id) {
				return true;
			}
		} catch (Exception $e) {
                        log_message('error', 'LDAP connect failed!');
			return false;
		}
		return false;
	}

	function bind() {
		$rdn = $this->admin_dn;
		$this->bind_id = @ldap_bind($this->connect_id, $rdn, $this->admin_pwd);
		if ($this->bind_id) {
			return true;
		}
                log_message('error', 'LDAP bind failed!');
		return false;
	}
        
        public function conn_bind()
        {
            if(!$this->connect())
            {
                $this->success = false;
                $this->error_info = 'LDAP connect failed!';
            }
            
            if(!$this->bind())
            {
                $this->success = false;
                $this->error_info = 'LDAP bind failed!';
            }
        }

	function get_attributes($fan) {
            
		$filter = "cn=" . $fan;
		//$filter .= " (&(flindersPersonType=H)(ou=School of Medicine)";
		$search_id = ldap_search($this->connect_id, $this->dn, $filter);
                if($search_id === false)
                {
                    $this->success = false;
                    $this->error_info = 'LDAP ldap_search failed!';
                    return false;
                }
                    
		$result_array = ldap_first_entry($this->connect_id, $search_id);
                if($result_array === false)
                {
                    $this->success = false;
                    $this->error_info = 'LDAP ldap_first_entry failed!';
                    return false;
                }
		$user_info = ldap_get_attributes($this->connect_id, $result_array);
                if($user_info === false)
                {
                    $this->success = false;
                    $this->error_info = 'LDAP ldap_get_attributes failed!';
                    return false;
                }

		$data['name'] = $user_info['fullName'][0];
		$data['givenname'] = $user_info['givenName'][0];
		$data['sn'] = $user_info['sn'][0];
		$data['email'] = $user_info['mail'][0];
                $data['flindersPersonType'] = $user_info['flindersPersonType'][0];
                
                
                #$data['ou'] = $user_info['ou'];
		 
		 
		 

		// get whether a member of group 
		$group_array = ldap_get_values($this->connect_id, $result_array, "groupMembership");
                if($group_array === false)
                {
                    #$this->success = false;
                    #$this->error_info = 'LDAP ldap_get_values failed!';
                    #return false;
                }
                
                $data['groups'] = $group_array;
                
                $data['user_info'] = $user_info;

		ldap_free_result($search_id);

		return $data;
	}
	
	
	function get_groups_of_member($fan) {
            
		//echo "ldap.php";
		
		$filter = "uniqueMember=cn=" . $fan . ',ou=people,o=flinders';
                $dn = "ou=groups,o=flinders";
                $attributes = array("dn");
		
		$search_id = ldap_search($this->connect_id, $dn, $filter, $attributes);
                if($search_id === false)
                {
                    $this->success = false;
                    $this->error_info = 'LDAP ldap_search failed!';
                    return false;
                }
    
                $groups = ldap_get_entries($this->connect_id, $search_id); 
                if($groups === false)
                {
                    $this->success = false;
                    $this->error_info = 'LDAP ldap_get_entries failed!';
                    return false;
                }
                
                if(isset($groups['count']))
                    unset($groups['count']);
                foreach($groups as $key=>$grp)
                {
                    if(isset($grp['dn']))
                        $groups[$key] = $grp['dn'];
                    
                }

		return $groups;
	}
	
	
	function findLDAPgroup($groups)
	
	
	{
		
	
		/*
		
		$ldapgroups = $this->ci->config->item('ocf_ldap_groups');
		
	
		
		$_SESSION['ocf_validgrouplist'] = '';
	
		$ctr = 0;
		$grouparray = array();
		$i = 0;
		
		foreach($groups as $group) {
			
			
			$ctr++;
		
			$exploded = explode(',', $group);
			
			
			$g[substr($exploded[0], 3)] = $ldapgroups[substr($exploded[0], 3)];

		}
		
		
		//print_r($g);
		
		
		$tmp['auth'] = false;
		
		$ldapgrouplistcount = count(array_intersect_key($g, $ldapgroups));
		
		
		
			if (count(array_intersect_key($g, $ldapgroups)) > 0) 
			
			{  

					
				$tmpgroups = array_intersect_key($g, $ldapgroups);
				
				//print_r($tmpgroups);
				
			
				$numkeys = count($tmpgroups);
				

				
				if ($numkeys == 1) { 
				
				
				$groupkey = array_keys($tmpgroups); 
				
				//echo $groupkey;
				
	
				
				
				
				$courselist = array_values($tmpgroups);
				
				$grouplist = array();
				
				foreach ($courselist[0] as $course) {
					
					
					$i++;
					$grouplist[$i] = $course;
	
					
					}
				}	
				
				
				
				//echo $validgroups;
				$tmp['ldapgroups']= $grouplist;
				$tmp['auth'] = true;
				
			

				
				
				$_SESSION['ocf_ldapauth'] = true;
				$_SESSION['ocf_validgrouplist'] = $grouplist;
				
				
				
	
				}
			
		
	
	
	
	return $tmp;
	
	*/
		
	}
	


	function close() {
		#ldap_close($this->connect_id);
	}
        
        function __destruct()
        {
            if ($this->connect_id) {
                ldap_close($this->connect_id);
            }
            #echo 'destructed';
        }

}
