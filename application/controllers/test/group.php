<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Group extends CI_Controller
{
    public function index($fan)
    {
        $this->CI =& get_instance();
        $this->CI->load->library('ldap/ldap');

        $ldap_user = $this->CI->ldap->get_attributes($fan);
        $_SESSION['username'] = $ldap_user['name'];

        $ldap_groups = $this->CI->ldap->get_groups_of_member($fan);

        echo '<pre>';
        print_r($ldap_groups);
        echo '</pre>';
    }
}