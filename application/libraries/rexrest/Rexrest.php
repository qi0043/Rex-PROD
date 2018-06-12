<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'oauth_client/oauth_client.php';
require 'oauth_client/httpclient/http.php';

/**
 * Class to support EQUELLA REST API with OAuth 2.0 login.
 *
 * Reference:
 *      EQUELLA REST API Guide
 *      http://oauth.net/2/
 *      http://tools.ietf.org/html/rfc6749#section-4.4
 */

class Rexrest extends oauth_client_class
{
    protected $ci;
    protected $institute_url;
    protected $prefer_curl;

    function __construct($params=null)
    {
        #parent::__construct();

    	$this->ci =& get_instance();
		$this->ci->load->config('rex');

		$this->server = '';
        $this->oauth_version = '2.0';
        $this->scope = '';

        $this->institute_url = $this->ci->config->item('institute_url');
        #$this->redirect_uri = $this->ci->config->item('redirect_uri');
		#$this->dialog_url = $this->ci->config->item('dialog_url');
		$this->access_token_url = $this->ci->config->item('access_token_url');

		#$this->client_id = $this->ci->config->item('client_id');
		#$this->client_secret = $this->ci->config->item('client_secret');

        if($params==null)
            $oauth_client_config_name = 'default';
        else
            $oauth_client_config_name = $params['oauth_client_config_name'];
        $oauth_clients = $this->ci->config->item('oauth_clients');
        if(!isset($oauth_clients[$oauth_client_config_name]))
        {
            log_message('error', 'OAuth client name is not configured.');
            exit();
        }
        $this->client_flex_user = $oauth_clients[$oauth_client_config_name]["client_flex_user"];
        $this->client_id = $oauth_clients[$oauth_client_config_name]['client_id'];
        $this->client_secret = $oauth_clients[$oauth_client_config_name]['client_secret'];

        $this->debug = $this->ci->config->item('rest_debug');
	    $this->debug_http = $this->ci->config->item('rest_debug_http');
        $this->prefer_curl = $this->ci->config->item('prefer_curl');

        if(substr($this->institute_url, -1, 1) != '/')
	{
            $this->institute_url .= '/';
	}

        if (session_id() != '')
        {
            $this->session_started = true;
        }

        #$this->processClientCredentialToken();

    }

    /**
     * Perform GET request
     *
     * @param string $url
     * @param array $response
     *
     * @return boolean
     */
    protected function get($url, &$response)
    {

        return($this->restCall($url, 'GET', null, null, $response));
    }

    /**
     * Perform DELETE request
     *
     * @param string $url
     * @param array $response
     *
     * @return boolean
     */
    protected function delete($url, &$response)
    {

        return($this->restCall($url, 'DELETE', null, null, $response));
    }

    /**
     * Perform POST request
     *
     * @param string $url
     * @param array $data
     * @param array $response
     *
     * @return boolean
     */
    protected function post($url, $parameters, $options, &$response)
    {
        return($this->restCall($url, 'POST', $parameters, $options, $response));
    }

    /**
     * Perform PUT request
     *
     * @param string $url
     * @param array $data
     * @param array $response
     *
     * @return boolean
     */
    protected function put($url, $parameters, $options, &$response)
    {
        return($this->restCall($url, 'PUT', $parameters, $options, $response));
    }

	public function newFileArea(&$response)
    {
        $url = $this->institute_url . 'api/file/';
        return($this->post($url, $parameters=null, $options=null, $response));

    }


	public function submitForModeration($item_uuid, $item_version, &$response)
	{
		$url = $this->institute_url . 'api/item/' . $item_uuid. '/'. $item_version.'/action/submit';
		return($this->post($url, $parameters=null, $options=null, $response));
	}

	/**
     * Get a taxonomy
     *
     * @param string $uuid
     * @param array $response
     *
     * @return boolean
     */

    public function getTaxonomy($uuid, &$response)
    {
        $url = $this->institute_url . 'api/taxonomy/' . $uuid . '/term' ;

        return($this->get($url, $response));
    }

	/**
     * Get a taxonomy
     *
     * @param string $uuid
     * @param array $response
     *
     * @return boolean
     */

    public function getTaxonomyTerm($uuid, $termuuid, &$response)
    {
        $url = $this->institute_url . 'api/taxonomy/' . $uuid . '/term/' . $termuuid . '/data' ;

        return($this->get($url, $response));
    }


	public function getTerms($uuid, $path, &$response)
    {
	    $url = '';
		if($path == '')
		{
        	$url = $this->institute_url . 'api/taxonomy/' . $uuid . '/term' ;
		}
		else
		{
			$url = $this->institute_url . 'api/taxonomy/' . $uuid . '/term?path='.$path;
		}
        return($this->get($url, $response));
    }

	public function searchTerm($uuid, $term, &$response)
	{
		$url = $this->institute_url . 'api/taxonomy/' . $uuid . '/search?q='.$term.'&restriction=TOP_LEVEL_ONLY&limit=1&searchfullterm=false';
		return($this->get($url, $response['available']));
	}

	public function getTermData($taxUuid, $termUuid, $datakey, &$response)
	{
		$url = $this->institute_url . 'api/taxonomy/' . $taxUuid. '/term/'. $termUuid . '/data/' . $datakey;
		return($this->get($url, $response));
	}


    /**
     * Get an item
     *
     * @param string $uuid
     * @param string $version
     * @param array $response
     *
     * @return boolean
     */

    public function getItem ($uuid, $version, &$response)
    {
        $url = $this->institute_url . 'api/item/' . $uuid . '/'. $version . '/' ;
        
        return($this->get($url, $response));
    }

	public function getItemAll ($uuid, $version, &$response)
    {
        $url = $this->institute_url . 'api/item/' . $uuid . '/'. $version . '?info=all' ;

        return($this->get($url, $response));
    }

    public function getAllComment($uuid, $version, &$response)
    {
        $url = $this->institute_url . 'api/item/' . $uuid . '/'. $version . '/comment' ;

        return($this->get($url, $response));
    }


    public function getItemHistory($uuid, $version, &$response)
    {
        $url = $this->institute_url . 'api/item/' . $uuid . '/'. $version . '/history' ;

        return($this->get($url, $response));
    }


	 /**
     * Get an item's attachements
     *
     * @param string $uuid
     * @param string $version

     * @param array $response
     *
     * @return boolean
     */

    public function getItemAttachments ($uuid, $version, &$response)
    {
        $url = $this->institute_url . 'api/item/' . $uuid . '/'. $version . '/?info=attachment' ;

        return($this->get($url, $response));
    }


	/**
     * Get an item's basic info
     *
     * @param string $uuid
     * @param string $version

     * @param array $response
     *
     * @return boolean
     */

    public function getItemBasic ($uuid, $version, &$response)
    {
        $url = $this->institute_url . 'api/item/' . $uuid . '/'. $version . '/?info=basic' ;


        return($this->get($url, $response));
    }

    /**
     * Redraft an ITEM
     *
     * @param string $item_uuid
     * @param integer $item_version
     *
     * @return boolean
     */

    public function item_redraft($item_uuid, $item_version, &$response)
    {
	$url = $this->institute_url . 'api/item/' . $item_uuid. '/'. $item_version.'/action/redraft';
	return($this->post($url, $parameters=null, $options=null, $response));
    }


    /**
     * Search items
     *
     * @param
     * @param
     * @param array $response
     *
     * @return boolean
     */

    public function search (&$response, $q=null, $collections=null, $where=null,
                    $start=null, $length=null, $order=null, $reverse='true', $info=null, $showall='true')
    {
        $url = $this->institute_url . 'api/search?';

        if ($q != null)
            $url .= 'q=' . $q;

        if ($collections != null)
        {
            if(substr($url, -1, 1) != '?')
            {
                $url .= '&';
            }
            $url .= 'collections=' . $collections;


        }
        if ($where != null)
        {
            if(substr($url, -1, 1) != '?')
            {
                $url .= '&';
            }
            $url .= 'where=' . $where;


        }

        if(substr($url, -1, 1) != '?')
        {
            $url .= '&';
        }
        $url .= 'start=' . $start;

        $url .= '&length=' . $length;

        if ($order != null)
            $url .= '&order=' . $order;

        $url .= '&reverse=' . $reverse;

        if ($info != null)
            $url .= '&info=' . $info;

        $url .= '&showall=' . $showall;

        return($this->get($url, $response));
        #print_r($response);

    }

    /**
     * Edit an item
     *
     * @param string $uuid
     * @param string $version
     * @param array $item_bean
     * @param array $response
     * @param string $filearea_uuid
     *
     * @return boolean
     */

    public function editItem ($uuid, $version, $item_bean, &$response, $filearea_uuid=null)
    {
        $url = $this->institute_url . 'api/item/' . $uuid . '/'. $version ;
        if ($filearea_uuid != null)
            $url .= '?file=' . $filearea_uuid;
        $options['RequestContentType'] = 'application/json';
        $json_str = json_encode($item_bean);
		//log_message('error', $json_str);
        $json_str = str_replace('\\/', '/', $json_str);
        $json_str = str_replace('"drm":[]', '"drm":{}', $json_str);
        $options['RequestBody'] = $json_str;

        return($this->put($url, $parameters=null, $options, $response));
        #print_r($response);
    }

    public function editLockedItem($uuid, $version, $item_bean, $lock_uuid, &$response)
    {
		  $url = $this->institute_url . 'api/item/' . $uuid . '/'. $version.'?lock='.$lock_uuid.'&keeplocked=true&waitforindex=0';

        $options['RequestContentType'] = 'application/json';
        $json_str = json_encode($item_bean);
        $json_str = str_replace('\\/', '/', $json_str);
        $json_str = str_replace('"drm":[]', '"drm":{}', $json_str);
        $options['RequestBody'] = $json_str;
        return($this->put($url, $parameters=null, $options, $response));

        //print_r($response);
    }
    /**
     * Upload a file
     *
     * @param string $uuid
     * @param string $filepath
     * @param string $data (binary)
     * @param array $response
     *
     * @return boolean
     */
    public function fileUpload ($uuid, $filepath, $data, &$response)
    {
        $url = $this->institute_url . 'api/file/' . $uuid . '/content/' . $filepath ;
        $options['RequestContentType'] = 'application/octet-stream';
        $options['RequestBody'] = $data;
        return($this->put($url, $parameters=null, $options, $response));
        #print_r($response);
    }

    /**
     * Copy the files of an item to get a file area
     *
     * @param string $uuid
     * @param string $version
     * @param array $response
     *
     * @return boolean
     */

    public function filesCopy ($uuid, $version, &$response)
    {
        $url = $this->institute_url . 'api/file/copy?uuid=' . $uuid . '&version=' . $version ;
        return($this->post($url, $parameters=null, $options=null, $response));
        #print_r($response);
    }

    /**
     * Download a file
     *
     * @param string $uuid
     * @param string $filepath
     * @param array $response
     *
     * @return boolean
     */
    public function fileDownload ($uuid, $filepath, &$response)
    {
        $url = $this->institute_url . 'api/file/' . $uuid . '/content/' . $filepath ;
        return($this->get($url, $response));
        #print_r($response);
    }

	public function fileDelete($fileuuid, $filepath, &$response)
	{
		$url = $this->institute_url . 'api/file/' . $fileuuid . '/content/' . $filepath ;
		return($this->delete($url, $response));
		#print_r($response);
	}

    /**
     * Create (Contribute) an item
     *
     * @param boolean $draft
     * @param boolean $waitforindex
     * @param array $item_bean
     * @param array $response
     * @param string $filearea_uuid
     *
     * @return boolean
     */

    public function createItem ($item_bean, &$response, $filearea_uuid=null, $draft=false, $waitforindex=false)
    {
        $url = $this->institute_url . 'api/item/';
        $url .= '?draft=' . $draft;
        $url .= '&waitforindex=' . $waitforindex;
        if ($filearea_uuid != null)
            $url .= '&file=' . $filearea_uuid;
        $options['RequestContentType'] = 'application/json';
        $json_str = json_encode($item_bean);
        $json_str = str_replace('\\/', '/', $json_str);
        #$json_str = str_replace('"drm":[]', '"drm":{}', $json_str);
        $options['RequestBody'] = $json_str;
        #print_r($response);
        return($this->post($url, $parameters=null, $options, $response));
    }

	/**
     * Create (Contribute) a draft item
     *
     * @param boolean $draft
     * @param boolean $waitforindex
     * @param array $item_bean
     * @param array $response
     * @param string $filearea_uuid
     *
     * @return boolean
     */
	public function createDraftItem ($item_bean, &$response)
    {
        $url = $this->institute_url . 'api/item/';
        $url .= '?draft=true';
        $url .= '&waitforindex=0';
        $options['RequestContentType'] = 'application/json';
        $json_str = json_encode($item_bean);
        $json_str = str_replace('\\/', '/', $json_str);
        #$json_str = str_replace('"drm":[]', '"drm":{}', $json_str);
        $options['RequestBody'] = $json_str;
        #print_r($response);
        return($this->post($url, $parameters=null, $options, $response));
    }

    /**
     * Create an activation
     *
     * @param array $activation_bean
     * @param array $response
     *
     * @return boolean
     */

    public function createActivation ($activation_bean, &$response)
    {
        $url = $this->institute_url . 'api/activation';
        $options['RequestContentType'] = 'application/json';
        $json_str = json_encode($activation_bean);
        $json_str = str_replace('\\/', '/', $json_str);
        #$json_str = str_replace('"drm":[]', '"drm":{}', $json_str);
        $options['RequestBody'] = $json_str;
        #print_r($response);
        return($this->post($url, $parameters=null, $options, $response));
    }

    /**
     * Create an equella course
     *
     * @param array $course_bean
     * @param array $response
     *
     * @return boolean
     */

    public function createCourse ($course_bean, &$response)
    {
        $url = $this->institute_url . 'api/course';
        $options['RequestContentType'] = 'application/json';
        $json_str = json_encode($course_bean);
        $json_str = str_replace('\\/', '/', $json_str);
        #$json_str = str_replace('"drm":[]', '"drm":{}', $json_str);
        $options['RequestBody'] = $json_str;
        #print_r($response);
        return($this->post($url, $parameters=null, $options, $response));
    }



	 /**
     * Get all moderating tasks under current user's account
     * @param string $filter (all, assignedme, assignedothers, assignednone, mustmoderate)
	 * @param int    $start
	 * @param int    $length
     * @param string $collection_uuid
     * @param array  $response
     *
     * @return boolean
     */
	//http://flex-test.flinders.edu.au:80/api/task/?filter=all&start=0&length=100&collections=1bb07d74-be3c-41b9-8467-3096c2d21f25&reverse=false
	public function getCurrentTasks($filter='all', $start = 0, $length = 100, $collection_uuid, &$response)
	{
		$url = $this->institute_url . 'api/task?filter='.$filter.'&start='.$start.'&length='.$length.'&collections='.$collection_uuid.'&reverse=false';
        return($this->get($url, $response));

	}

	 /**
     * Reject an item back to the original contributor
     * @param string $uuid (item uuid)
	 * @param string $version (item version)
	 * @param string $taskUuid (task uuid, get this uuid from getCurrentTasks)
     * @param string $msg (message that sends to rejected item receiver)
     *
     * @return boolean
     */
	 		   //https://rex-test.flinders.edu.au:443/api/item/e31a1578-db8f-45b3-abe9-036daa765e26/1/task/b3774398-66bb-49ea-a2df-4f2be3fd4066/reject?message=please%20finish%20all%20parts

	public function rejectItem($uuid, $version, $taskUuid, $msg, &$response)
	{
		$url = $this->institute_url . 'api/item/'.$uuid.'/'.$version.'/task/'. $taskUuid. '/reject?message='.$msg;
		return($this->post($url, $parameters=null, $options=null, $response));
	}

    /**
     * Get an item
     *
     * @param string $uuid
     * @param string $version
     * @param array $response
     *
     * @return boolean
     */

    public function listGroups (&$response, $user=null, $q=null, $allParents='true', $name=null)
    {
        $url = $this->institute_url . 'api/usermanagement/local/group/?';

        if ($q != null)
        {
            $url .= 'q=' . $q;
        }

        if ($user != null)
        {
            if($q != null)
            {
                $url .= '&';
            }
            $url .= 'user=' . $user;
        }

        {
            if(substr($url, -1, 1) != '?')
            {
                $url .= '&';
            }
            $url .= 'allParents=' . $allParents;
        }

        if ($name != null)
        {
            if(substr($url, -1, 1) != '?')
            {
                $url .= '&';
            }
            $url .= 'name=' . $name;
        }

        return($this->get($url, $response));
    }

	/***
	item_lock
	***/

	//get an existing lock for an item
	public function getLock($uuid, $version, &$response)
	{
		$url = $this->institute_url . 'api/item/' . $uuid .'/' . $version . '/lock';

		return($this->get($url, $response));
	}

	//create a lock for an item
	public function createLock($uuid, $version, &$response)
	{
		$url = $this->institute_url . 'api/item/' . $uuid .'/' . $version . '/lock';

		#return($this->post($url, $response));
		return($this->post($url, $parameters=null, $options=null, $response));
	}

	//delete an existing lock for an item
	public function deleteLock($uuid, $version, &$response)
	{
		$url = $this->institute_url . 'api/item/' . $uuid .'/' . $version . '/lock';

		return($this->delete($url, $response));
	}








    /**
     * OAuth 2.0 Client Credentials Grant login.
     *
     * Reference:
     *      EQUELLA REST API Guide
     *      Client Credentials Grant http://tools.ietf.org/html/rfc6749#section-4.4
     *
     */
    public function processClientCredentialToken()
    {

        if(!$this->RetrieveToken($valid))
                return false;
        if($valid)
        {
            if(strlen($this->access_token_expiry)
            && strcmp($this->access_token_expiry, gmstrftime('%Y-%m-%d %H:%M:%S')) > 0)
            {
                if($this->debug)
                {
                        $this->OutputDebug('The access token in session is still valid');
                }
                return true;
            }
        }

        $values = array(
                'client_id'=>$this->client_id,
                'client_secret'=>($this->get_token_with_api_key ? $this->api_key : $this->client_secret),
                'redirect_uri'=>'default',
                'grant_type'=>'client_credentials'
        );

        if(!$this->GetAccessTokenURL($access_token_url))
                return false;
        #if(!$this->SendAPIRequest($access_token_url, 'POST', $values, null, array('Resource'=>'OAuth '.('access').' token', 'ConvertObjects'=>true, 'FailOnAccessError'=>true), $response))
        if(!$this->sendRestAPI($access_token_url, 'POST', $values, array('Resource'=>'OAuth '.('access').' token', 'ConvertObjects'=>true, 'FailOnAccessError'=>true), $response))
                return false;
        if(strlen($this->access_token_error))
        {
                $this->authorization_error = $this->access_token_error;
                return true;
        }
        if(!IsSet($response['access_token']))
        {
                if(IsSet($response['error']))
                {
                        $this->authorization_error = 'it was not possible to retrieve the access token: it was returned the error: '.$response['error'];
                        return true;
                }
                return($this->SetError('OAuth server did not return the access token'));
        }
        $access_token = array(
                'value'=>($this->access_token = $response['access_token']),
                'authorized'=>true,
        );
        if($this->store_access_token_response)
                $access_token['response'] = $this->access_token_response = $response;
        if($this->debug)
                $this->OutputDebug('Access token: '.$this->access_token);
        if(IsSet($response['expires_in'])
        && $response['expires_in'] == 0)
        {
                if($this->debug)
                        $this->OutputDebug('Ignoring access token expiry set to 0');
                $this->access_token_expiry = '';
        }
        elseif(IsSet($response['expires'])
        || IsSet($response['expires_in']))
        {
                $expires = (IsSet($response['expires']) ? $response['expires'] : $response['expires_in']);
                if(strval($expires) !== strval(intval($expires))
                || $expires <= 0)
                        return($this->SetError('OAuth server did not return a supported type of access token expiry time'));
                if(intval($expires) > 86400) #### one day
                        $expires = 86400;
                $this->access_token_expiry = gmstrftime('%Y-%m-%d %H:%M:%S', time() + $expires);
                if($this->debug)
                        $this->OutputDebug('Access token expiry: '.$this->access_token_expiry.' UTC');
                $access_token['expiry'] = $this->access_token_expiry;
        }
        else
                $this->access_token_expiry = '';
        if(IsSet($response['token_type']))
        {
                $this->access_token_type = $response['token_type'];
                if(strlen($this->access_token_type)
                && $this->debug)
                        $this->OutputDebug('Access token type: '.$this->access_token_type);
                $access_token['type'] = $this->access_token_type;
        }
        else
        {
                $this->access_token_type = $this->default_access_token_type;
                if(strlen($this->access_token_type)
                && $this->debug)
                        $this->OutputDebug('Assumed the default for OAuth access token type which is '.$this->access_token_type);
        }
        /*if(IsSet($response['refresh_token']))
        {
                $this->refresh_token = $response['refresh_token'];
                if($this->debug)
                        $this->OutputDebug('New refresh token: '.$this->refresh_token);
                $access_token['refresh'] = $this->refresh_token;
        }
        elseif(strlen($this->refresh_token))
        {
                if($this->debug)
                        $this->OutputDebug('Reusing previous refresh token: '.$this->refresh_token);
                $access_token['refresh'] = $this->refresh_token;
        }*/
        if(!$this->StoreAccessToken($access_token))
                return false;

        return true;
    }

    /**
     * Perform REST request, check token expiration time first
     *
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $options
     * @param array $response
     * @return boolean
     *
     * refer to function sendRestAPI()for values of $options
     */
    protected function restCall($url, $method, $parameters, $options, &$response)
    {
        #$urlargs = null, $postdata = null
        if(strlen($this->access_token) === 0)
        {
                if(!$this->RetrieveToken($valid))
                        return false;
                if(!$valid)
                        return $this->SetError('the access token is not set to a valid value');
        }

        if(strlen($this->access_token_expiry)
        && strcmp($this->access_token_expiry, gmstrftime('%Y-%m-%d %H:%M:%S')) <= 0)
        {
                if($this->debug)
                {
                        $this->OutputDebug('The access token expired on '.$this->access_token_expiry);
                        $this->OutputDebug('Refreshing the access token');
                }
                if(!$this->processClientCredentialToken())
                        return false;
        }

        #$url .= (strcspn($url, '?') < strlen($url) ? '&' : '?').(strlen($this->access_token_parameter) ? $this->access_token_parameter : 'access_token').'='.UrlEncode($this->access_token);

        /*if(isSet($urlargs))
        {
            foreach ($urlargs as $argname => $argvalue)
            {
                $url .= '&'.$argname.'='.urlencode($argvalue);
            }
        }*/

        return($this->sendRestAPI($url, $method, $parameters, $options, $response));

    }

    /**
     * API to send REST request
     *
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $options
     * @param array $response
     * @return boolean
     *
     * $options values:
     *   'RequestContentType': content type that should be used to send the request values. It can be either 'application/x-www-form-urlencoded' for sending values like from Web forms, or 'application/json' for sending the values encoded in JSON format. Other types are accepted if the 'RequestBody' option is specified. The default value is 'application/x-www-form-urlencoded'.
     *   'RequestBody': request body data of a custom type. The 'RequestContentType' option must be specified, so the 'RequestBody' option is considered.
     *   'FollowRedirection': limit number of times that HTTP response redirects will be followed. If it is set to 0, redirection responses fail in error. The default value is 0.
     *   'PostValuesInURI': boolean option to determine that a POST request should pass the request values in the URI. The default value is 0.
     *   'Files': associative array with details of the parameters that must be passed as file uploads. The array indexes must have the same name of the parameters to be sent as files. The respective array entry values must also be associative arrays with the parameters for each file. Currently it supports the following parameters:
     *       - Type - defines how the parameter value should be treated. It can be 'FileName' if the parameter value is is the name of a local file to be uploaded. It may also be 'Data' if the parameter value is the actual data of the file to be uploaded.
     *             Default: 'FileName'
     *       - ContentType - MIME value of the content type of the file. It can be 'automatic/name' if the content type should be determine from the file name extension.
     *             Default: 'automatic/name'
     *
     */

    protected function sendRestAPI($url, $method, $parameters, $options, &$response)
    {
		$this->response_status = 0;
		$http = new http_class;
		$http->debug = ($this->debug && $this->debug_http);
		$http->log_debug = true;
		$http->sasl_authenticate = 0;
		#$http->user_agent = $this->oauth_user_agent;
                $http->user_agent = '';
		$http->redirection_limit = (IsSet($options['FollowRedirection']) ? intval($options['FollowRedirection']) : 0);
		$http->follow_redirect = ($http->redirection_limit != 0);
                $http->prefer_curl = $this->prefer_curl;
                $options['Resource'] = 'API call';
		if($this->debug)
			$this->OutputDebug('Accessing the '.$options['Resource'].' at '.$url);
		$post_files = array();
		$method = strtoupper($method);

		$type = (IsSet($options['RequestContentType']) ? strtolower(trim(strtok($options['RequestContentType'], ';'))) : 'application/x-www-form-urlencoded');

                $files = (IsSet($options['Files']) ? $options['Files'] : array());
                if(count($files))
                {
                        foreach($files as $name => $value)
                        {
                                if(!IsSet($parameters[$name]))
                                        return($this->SetError('it was specified an file parameters named '.$name));
                                $file = array();
                                switch(IsSet($value['Type']) ? $value['Type'] : 'FileName')
                                {
                                        case 'FileName':
                                                $file['FileName'] = $parameters[$name];
                                                break;
                                        case 'Data':
                                                $file['Data'] = $parameters[$name];
                                                break;
                                        default:
                                                return($this->SetError($value['Type'].' is not a valid type for file '.$name));
                                }
                                $file['ContentType'] = (IsSet($value['Content-Type']) ? $value['Content-Type'] : 'automatic/name');
                                $post_files[$name] = $file;
                        }
                        UnSet($parameters[$name]);
                        if($method !== 'POST')
                        {
                                $this->OutputDebug('For uploading files the method should be POST not '.$method);
                                $method = 'POST';
                        }
                        if($type !== 'multipart/form-data')
                        {
                                if(IsSet($options['RequestContentType']))
                                        return($this->SetError('the request content type for uploading files should be multipart/form-data'));
                                $type = 'multipart/form-data';
                        }
                        #$value_parameters = array();
                }

		if(strlen($error = $http->GetRequestArguments($url, $arguments)))
			return($this->SetError('it was not possible to open the '.$options['Resource'].' URL: '.$error));
		if(strlen($error = $http->Open($arguments)))
			return($this->SetError('it was not possible to open the '.$options['Resource'].' URL: '.$error));
		if(count($post_files))
			$arguments['PostFiles'] = $post_files;
		$arguments['RequestMethod'] = $method;
		switch($type)
		{
			case 'application/x-www-form-urlencoded':
			case 'multipart/form-data':
				if(IsSet($options['RequestBody']))
					return($this->SetError('the request body is defined automatically from the parameters'));
				$arguments['PostValues'] = $parameters;
				break;
			case 'application/json':
				$arguments['Headers']['Content-Type'] = $options['RequestContentType'];
				if(!IsSet($options['RequestBody']))
				{
					$arguments['Body'] = json_encode($parameters);
					break;
				}
			default:
				if(!IsSet($options['RequestBody']))
					return($this->SetError('it was not specified the body value of the of the API call request'));
				$arguments['Headers']['Content-Type'] = $options['RequestContentType'];
				$arguments['Body'] = $options['RequestBody'];
				break;
		}
		$arguments['Headers']['Accept'] = (IsSet($options['Accept']) ? $options['Accept'] : '*/*');

                if(strlen($this->access_token) != 0)
                    $arguments['Headers']['X-Authorization'] = 'access_token=' . $this->access_token;

		if(strlen($error = $http->SendRequest($arguments))
		|| strlen($error = $http->ReadReplyHeaders($headers)))
		{
			$http->Close();
                        log_message('error', $error);
			return($this->SetError('it was not possible to retrieve the '.$options['Resource'].': '.$error));
		}
		$error = $http->ReadWholeReplyBody($data);
		$http->Close();
		if(strlen($error))
		{
			return($this->SetError('it was not possible to access the '.$options['Resource'].': '.$error));
		}
		$this->response_status = intval($http->response_status);
		$content_type = (IsSet($options['ResponseContentType']) ? $options['ResponseContentType'] : (IsSet($headers['content-type']) ? strtolower(trim(strtok($headers['content-type'], ';'))) : 'unspecified'));
		switch($content_type)
		{
			case 'text/javascript':
			case 'application/json':
				if(!function_exists('json_decode'))
					return($this->SetError('the JSON extension is not available in this PHP setup'));
				$response = json_decode($data, true);
				break;
			case 'application/x-www-form-urlencoded':
			case 'text/plain':
			case 'text/html':
				parse_str($data, $response);
				break;
			default:
				$response = $data;
				break;
		}
		if(!($this->response_status >= 200
		&& $this->response_status < 300))
		{
			$this->error = 'Response status '.$http->response_status.' Response: '.$data;
			if($this->debug)
				$this->OutputDebug($this->error);
                        log_message('error', $this->error);
                        return false;
		}
		if(is_array($response))
                    $response['headers'] = $headers;

		return true;
	}


        /*Function GetClientFlexUser(&$client_flex_user)
	{
		$access_token_url = $this->client_flex_user;
		return(true);
	}*/
        /*
         * Below get/store token funcitons are added to allow multiple token for multiple flex users.
         */
        Function GetAccessToken(&$access_token)
	{
		if(!$this->session_started)
		{
			if(!function_exists('session_start'))
				return $this->SetError('Session variables are not accessible in this PHP environment');
			#if(!session_start())
			#	return($this->SetPHPError('it was not possible to start the PHP session', $php_error_message));
			if(session_id() == '')
			    session_start();
			$this->session_started = true;
		}
		if(!$this->GetAccessTokenURL($access_token_url))
			return false;
		if(IsSet($_SESSION['OAUTH_ACCESS_TOKEN'][$access_token_url][$this->client_flex_user]))
			$access_token = $_SESSION['OAUTH_ACCESS_TOKEN'][$access_token_url][$this->client_flex_user];
		else
			$access_token = array();
		return true;
	}

        Function StoreAccessToken($access_token)
	{
		if(!$this->session_started)
		{
			if(!function_exists('session_start'))
				return $this->SetError('Session variables are not accessible in this PHP environment');
		}
		if(!$this->GetAccessTokenURL($access_token_url))
			return false;
		$_SESSION['OAUTH_ACCESS_TOKEN'][$access_token_url][$this->client_flex_user] = $access_token;
		return true;
	}

    /**
     * override function to write messages in CI logs
     *
     * @param string $message
     */
    /*
    protected function OutputDebug($message)
    {
            if($this->debug)
            {
                    $message = $this->debug_prefix.$message;
                    $this->debug_output .= $message."\n";;
                    log_message('debug', $message);
            }
            return(true);
    }*/
}


/* End of file */
