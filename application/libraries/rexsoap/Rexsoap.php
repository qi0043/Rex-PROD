<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
	
    define('STANDARD_INTERFACE', 1);
    define('TAXONOMY_INTERFACE', 2);
    define('COURSE_INTERFACE', 3);
    define('ACTIVATION_INTERFACE', 4);

    /**
     * Class to support EQUELLA SOAP API.
     *
     * Reference: 
     *      EQUELLA SOAP API Guide
     */
    
    class Rexsoap{
        
        protected $ci, $institute_url, $taxonomy_intf_url, $course_intf_url, $activation_intf_url,
                 $standard_intf_client, $taxonomy_intf_client,$course_intf_client, $activation_intf_client,
                 $username, $password; 
        public   $success, $error_info, $return_value;       
        
        /**
         * Constructor
         * 
         * Login to Equella Soap interface with username/password
         */
        public function __construct($soapparams=null)
        {
            $this->ci =& get_instance();
            $this->ci->load->config('rex');
        
            #$this->institute_url = $this->ci->config->item('institute_url');
            $this->institute_url = $this->ci->config->item('institute_url_soap');
            if(substr($this->institute_url, -1, 1) != '/')
            {
                $this->institute_url .= '/';
            }
            
            $this->standard_intf_url = $this->institute_url . $this->ci->config->item('soap_standard_intf');
            $this->taxonomy_intf_url = $this->institute_url . $this->ci->config->item('soap_taxonomy_intf');
            $this->course_intf_url = $this->institute_url . $this->ci->config->item('soap_course_intf');
            $this->activation_intf_url = $this->institute_url . $this->ci->config->item('soap_activation_intf');
            
            $this->standard_intf_client = null;
            $this->taxonomy_intf_client = null;
            $this->course_intf_client = null;
            $this->activation_intf_client = null;
            #$this->activation_intf_client = null;
            
            if($soapparams==null)
            {
                $this->username = $this->ci->config->item('soap_username');
                $this->password = $this->ci->config->item('soap_password');
            }
            else
            {
                $this->username = $soapparams['username'];
                $this->password = $soapparams['password'];
            }
            
            #$this->proxyHost = $this->ci->config->item('proxyHost');
            #$this->proxyPort = $this->ci->config->item('proxyPort');
            #$this->proxyUsername = $this->ci->config->item('proxyUsername');
            #$this->proxyPassword = $this->ci->config->item('proxyPassword');
            #$this->taxonID = $this->ci->config->item('taxonID');

            $this->success = true;
            $this->error_info = '';
            
             $requiredSoapOptions = array('location' => $this->standard_intf_url, 'trace' => 1);
            try{
                $this->standard_intf_client = new SoapClient($this->standard_intf_url.'?wsdl', $requiredSoapOptions);
                $this->standard_intf_client->login(array('in0' => $this->username, 'in1' => $this->password));
            }
            catch(SoapFault  $fault){
                $this->success = false;
                $this->error_info .= "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
                log_message('error', $this->error_info);
            }
            #log_message('debug', ($this->standard_intf_client->__getLastRequest()));
            #log_message('debug', ($this->standard_intf_client->__getLastResponse()));
            #echo (htmlentities($this->standard_intf_client->__getLastResponse()));
        }
        
        /**
         * Perform Soap API Call
         *
         * @param string $function
         * @param array $args
         * @param integer $interface
         *
         * Soap return result in $this->return_value
         */
        public function soapCall($function, $args=array(), $interface=STANDARD_INTERFACE)
        {
            switch ($interface)
            {
                case STANDARD_INTERFACE:
                    $endpoint = $this->standard_intf_url;
                    $client = $this->standard_intf_client;
                    break;
                case TAXONOMY_INTERFACE:
                    $endpoint = $this->taxonomy_intf_url;
                    $client = $this->taxonomy_intf_client;
                    break;
                case COURSE_INTERFACE:
                    $endpoint = $this->course_intf_url;
                    $client = $this->course_intf_client;
                    break;
                case ACTIVATION_INTERFACE:
                    $endpoint = $this->activation_intf_url;
                    $client = $this->activation_intf_client;
                    break;
                default:
                    $this->success = false;
                    $this->error_info .= "Wrong interface argument";
                    log_message('error', $this->error_info);
                    return;
            }
            
            if ($interface != STANDARD_INTERFACE && $client == null)
            {
                $requiredSoapOptions = array('location' => $endpoint, 'trace' => 1, 'keep_alive'=>true);
                $client = new SoapClient($endpoint.'?wsdl', $requiredSoapOptions);
                $client->__setCookie('JSESSIONID', $this->standard_intf_client->_cookies['JSESSIONID'][0]);
                switch ($interface)
                {
                    case TAXONOMY_INTERFACE:
                        $this->taxonomy_intf_client = $client;
                        break;
                    case COURSE_INTERFACE:
                        $this->course_intf_client = $client;
                        break;
                    case ACTIVATION_INTERFACE:
                        $this->activation_intf_client = $client;
                        break;
                    default:
                        $this->success = false;
                        $this->error_info .= "Wrong interface argument";
                        log_message('error', $this->error_info);
                        return;
                }
            }
			
           	try{
				
				$retval = null;
				$retval = $client->__soapCall($function, array($args));
				if($retval != null)
					$this->return_value = isset($retval->out) ? $retval->out : null;
				else
					$this->return_value = null;
			}
                #var_dump($this->return_value);
                #log_message('info', ($client->__getLastRequest()));
                #log_message('info', ($client->__getLastResponse()));
                #log_message('error', ($client->__getLastRequest()));
                #log_message('error', ($client->__getLastResponse()));
			  
			catch(SoapFault  $fault){
			  $this->success = false;
			  $this->error_info = "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
			  log_message('error', $this->error_info);
			  
			  log_message('error', ($client->__getLastRequest()));
			  log_message('error', ($client->__getLastResponse()));  
            }

            return $this->return_value;
        }
        
        /**
         * Destructor
         * 
         * Logout from Equella Soap interface
         */
        public function __destruct()
        {
            try{
                $this->standard_intf_client->logout();
                #$this->soapCall('logout');
            }
            catch(SoapFault  $fault){
                #echo 'fault';
                $this->success = false;
                $this->error_info .= "SOAP Fault when logout: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
                log_message('error', $this->error_info);
            }
            #log_message('debug', ($this->standard_intf_client->__getLastRequest()));
            #log_message('debug', ($this->standard_intf_client->__getLastResponse()));
            #echo ('<br>logout:<br>');
            #echo (htmlentities($this->standard_intf_client->__getLastRequest()));
            #echo ('<br>');
            #echo (htmlentities($this->standard_intf_client->__getLastResponse()));
        }
        
        /**
         * Activate Item Attachment
         *
         * @param string $uuid
         * @param integer $version
         * @param string $coursecode
         * @param array $attachments
         */
        public function activateItemAttachments($uuid, $version, $coursecode, $attachments)
        {

            $this->soapCall('activateItemAttachments',
                array('in0' => $uuid, 'in1' => $version, 'in2' => $coursecode, 'in3' => $attachments), ACTIVATION_INTERFACE);

        }
        
        /**
         * Finds all the internal EQUELLA groups that the user is a member of
         *
         * @param string $userUuid
         */
        public function getGroupsByUser($userUuid)
        {

            return $this->soapCall('getGroupsByUser', array('in0' => $userUuid));

        }
        
        /**
         * Is the user (external or internal) with id of userId directly in 
         * (that is, not in a subgroup of) the internal EQUELLA group with the id of groupId. 
         *
         * @param string $userUuid
         * @param string $groupId
         */
        public function isUserInGroup($userId, $groupId)
        {

            $this->soapCall('isUserInGroup', array('in0' => $userId, 'in1'=> $groupId));

        }
        
        public function searchGroups($searchString)
        {

            return $this->soapCall('searchGroups', array('in0' => $searchString));

	}

	/**
         * Bulk Import courses
         *
         * @param string $csvText
         */
        public function bulkImportCourses($csvText)
        {

            $this->soapCall('bulkImport',
                array('in0' => $csvText), COURSE_INTERFACE);

        }
	
	/**
         * get Searchable Collections
         *
         * @param
         */
        public function getSearchableCollections()
        {

            return $this->soapCall('getSearchableCollections');

        }
	
	/**
         * get query Counts
         *
         * @param
         */
        public function queryCounts($collectionUuids, $wheres)
        {

            return $this->soapCall('queryCounts', array('in0' => $collectionUuids, 'in1'=> $wheres));

        }
			
		/*************************************************************
		 *                    TermSoapService functions *
		 *************************************************************/
		 
		//Before terms can be edited, this method must be invoked to aquire an editing lock on the taxonomy. An error will be raised if the lock cannot be acquired, most likely because it has already been locked by a different user.
        public function lockTaxonomyForEditing($taxonID)
		{
			 $this->soapCall('lockTaxonomyForEditing',
                array('in0' =>$taxonID), TAXONOMY_INTERFACE);
		}
		
		// Unlock a taxonomy that you previously acquired a lock on.
		// taxonID	- The UUID of the taxonomy that you have finished editing.
		// force	- true if the taxonomy should be unlocked, even if your session is not the lock owner. It is recommended that you should you used false in nearly all case.

		public function unlockTaxonomy($taxonID, $force) 
		{
			$this->soapCall('unlockTaxonomy',
                array('in0' => $taxonID, 'in1' => $force), TAXONOMY_INTERFACE);
		}
		
		/* Insert a new term into the taxonomy.
			This method requires an editing lock to have been acquired for the taxonomy.
			Parameters:
				taxonID	-	The UUID of the taxonomy.
				parentFullPath	-	The full path of the parent term the new term will be added to, eg, Mammalia\Felidae\Panthera.
				index	-	The index the term should be inserted into in relation to its siblings. 
							Zero inserts it as the first sibling, one as the second sibling, and so on. 
							If the index is less than zero or greater than the number of siblings minus one, then the term will be added as the last sibling.
				termValue - The term to be added, eg, Tiger.
		*/
		public function insertTerm($taxonID, $parentFullPath, $term, $index)
		{
			$this->soapCall('insertTerm',
                array('in0' => $taxonID, 'in1' => $parentFullPath, 'in2' => $term, 'in3' => $index), TAXONOMY_INTERFACE);
		}
		
		
		/* Delete a term from the taxonomy. Child terms will also be deleted.
		    This method requires an editing lock to have been acquired for the taxonomy.
			Parameters:
			taxonID	-	The UUID of the taxonomy.
			termFullPath	-	The full path of the term to delete, eg, Mammalia\Felidae\Panthera\Tiger.
		*/
		public function deleteTerm($taxonID, $termFullPath)
		{
			ini_set('max_execution_time', 300);
			ini_set("default_socket_timeout", 300); //5 mins
			ini_set('soap.wsdl_cache_enabled',0);
			ini_set('soap.wsdl_cache_ttl',0);
			$this->soapCall('deleteTerm',
                array('in0' => $taxonID, 'in1' => $termFullPath), TAXONOMY_INTERFACE);
			sleep(1);
		}
		
		/* Retrieve an stored data value for a key against a term.
		   Parameters:
				taxonomyUuid	-	The UUID of the taxonomy.
				termFullPath	-	The full path of the term to retrieve the data for, eg, Mammalia\Felidae\Panthera\Tiger.
				dataKey	-	The key for the data to be retrieved.
			Returns: The data stored for that key and term.
		*/
		public function getTermData($taxonID, $termFullPath, $dataKey)
		{
			$this->soapCall('getData',
                array('in0' => $taxonID, 'in1' => $termFullPath, 'in2' => $dataKey), TAXONOMY_INTERFACE);
		}
		
		public function setTermData($taxonID, $termFullPath, $dataKey, $dataValue)
		{ 
			ini_set('max_execution_time', 300);
			ini_set("default_socket_timeout", 300); //5 mins
			ini_set('soap.wsdl_cache_enabled',0);
			ini_set('soap.wsdl_cache_ttl',0);
			#$this->standard_intf_client->keepAlive();
			$this->soapCall('setData', array('in0' => $taxonID, 'in1' => $termFullPath, 'in2' => $dataKey, 'in3' => $dataValue), TAXONOMY_INTERFACE);	
		}
		
		/***List the child terms for a parent term. This will only list the immediate children of the parent, ie, not grand-children, great grand-children, etc...
         Parameters:
         	taxonomyUuid	-	The UUID of the taxonomy.
         	parentFullPath	-	The full path of the parent term the new term will be added to, eg, Mammalia\Felidae\Panthera. 
								Passing in an empty string will list the root terms.
         Returns:
        	 An array of immediate child terms, eg, [Tiger, Lion, Jaguar, Leopard]***/

		public function listTerms($taxonID, $parentFullPath)
		{
			return $this->soapCall('listTerms',
                array('in0' => $taxonID, 'in1' => $parentFullPath), TAXONOMY_INTERFACE);
		}
		
		
		//log out
		public function logOut()
        {
            try{
                $this->standard_intf_client->logout();
                #$this->soapCall('logout');
				log_message('error', 'logout');
            }
            catch(SoapFault  $fault){
                #echo 'fault';
                $this->success = false;
                $this->error_info .= "SOAP Fault when logout: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
                log_message('error', $this->error_info);
            }
        }
		
        
    }


	
	
