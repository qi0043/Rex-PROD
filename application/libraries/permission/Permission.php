<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 


/**
 * Check user permssions.
 * 
 */

class Permission {

    protected $CI;
    public $success = true, $error_info = '';

    public function get_rex_permission($fan)
    {
        #LDAP groups.
        $this->CI =& get_instance();
        $this->CI->load->config('rex');
        $this->CI->load->library('ldap/ldap');
        if (!$this->CI->ldap->success) {
            $this->success = false;
            $this->error_info = 'LDAP error!';
            return;
        }

        $config_ldap_groups = $this->CI->config->item('rex_ldap_groups');
        $ldap_groups = $this->CI->ldap->get_groups_of_member($fan);
        if (!$this->CI->ldap->success) {
            $this->success = false;
            $this->error_info = 'LDAP error!';
            return;
        }

        foreach ($config_ldap_groups as $group => $permission) {
            foreach ($ldap_groups as $ldap_grp) {
                if (strpos($ldap_grp, 'cn=' . $group) !== false) {
                    log_message('error', $fan . ' in LDAP group: ' . $ldap_grp);
                    return true;
                }
            }
        }

        #FLEX internal groups.
        $config_ocf_chk_flex_groups = $this->CI->config->item('rex_chk_flex_groups');
        if ($config_ocf_chk_flex_groups == true)
        {
            $config_flex_groups = $this->CI->config->item('rex_flex_groups');
            $this->CI->load->library('rexrest/rexrest');
            $success = $this->CI->rexrest->processClientCredentialToken();
            if (!$success) {
                $this->success = false;
                $this->error_info = $this->CI->rexrest->error;
                log_message('error', 'Failed to get permission of REX' . ', error: ' . $this->error_info);
                return;
            }

            $success = $this->CI->rexrest->listGroups($response, $fan);
            if (!$success) {
                $this->success = false;
                $this->error_info = $this->CI->rexrest->error;
                log_message('error', 'Failed to get permission of REX' . ', error: ' . $this->error_info);
                return;
            }

            $group_count = intval($response['available']);
            if ($group_count > 0) {
                foreach ($config_flex_groups as $group => $permission) {
                    foreach ($response['results'] as $flex_group) {
                        log_message('error', $fan . ' in flex group: ' . $flex_group['name']);
                        if (strpos($flex_group['name'], $group) !== false) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
