<?php
class Ncstate_Service_Remedy
{
    const URI_BASE = 'https://ars00srv.unity.ncsu.edu/arsys/WSDL/public/ars00srv';

    protected $_username = null;

    protected $_password = null;

    /**
	 * Stores the soap client
   	 *
   	 * @var stdClass
   	 */
    protected $_soapClient = null;

    /**
     * Constructor
     *
     * @param string $username - Remedy Username
     * @param string $password - Remedy Password
     */
    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     * Retrieve a specific call specified by call-id.
     *
     * @param int $callId
     */
    public function callGet($callId)
    {
        $args = array(
            'call_id' => (int)$callId
        );

        $result = $this->_request('calls', 'get-entry', $args);

        $result->{'problem_text'} = $this->_parseDigest($result->{'problem_text'});

        return $result;
    }

    /**
     * Retrieve a list of calls for all calls matching the specified qualification.
     *
     * @param string $qualification
     * @param (null|string) $startRecord
     * @param (null|string) $maxLimit
     */
    public function callList($qualification, $startRecord = '', $maxLimit = '')
    {
        $args = array(
            'qualification' => $qualification,
            'start_record'  => $startRecord,
            'max_limit'     => $maxLimit,
        );

        return $this->_request('calls', 'get-list', $args);
    }

    /**
     * Update the call specified by call-id.
     *
     * @param int $callId
     * @param array $data
     */
    public function callUpdate($callId, Array $data)
    {
        $defaults = array(
            'action'           => null,     // action
            'call_id'          => $callId,  // call ID to update
            'customer_id'      => null,     // customer CID number
            'date_nextcontact' => null,     // date-time field, call next-contact
            'impact'           => null,     // impact
            'on_site_visit'    => null,     // Yes/No/NULL
            'origin'           => null,     // origin
            'owner_id'         => null,     // owner id number
            'owner'            => null,     // call owner (Remedy login)
            'priority'         => null,     // priority
            'problem_text'     => null,     // single diary entry
            'problem'          => null,     // problem description
            'product_id'       => null,     // product id number
            'product'          => null,     // product name
            'solution_id'      => null,     // solution id number
            'status'           => null,     // status
            'time_spent'       => null,     // time spent, in seconds
            'workgroup_id'     => null,     // workgroup id number
            'workgroup'        => null,     // workgroup name
        );

        $args = array();

        foreach ($defaults as $key => $value) {
            if (isset($data[$key])) {
                $args[$key] = $data[$key];
            } elseif (!is_null($value)) {
                $args[$key] = $value;
            }
        }

        return $this->_request('calls', 'update-entry', $args);
    }

    /**
     * Create a new call using the specified fields.
     *
     * @param array $data
     */
    public function callCreate(Array $data)
    {
        $defaults = array(
            'action'           => null,              // action
            'agent'            => $this->_username,  // required, agent
            'comments'         => null,              // call ID to update
            'customer-id'      => null,              // customer CID number
            'date-nextcontact' => null,              // date-time field, call next-contact
            'impact'           => null,              // impact
            'on-site-visit'    => null,              // Yes/No/NULL
            'origin'           => null,              // origin
            'owner-id'         => null,              // owner id number
            'owner'            => null,              // call owner (Remedy login)
            'priority'         => null,              // priority
            'problem-text'     => null,              // single diary entry
            'problem'          => null,              // problem description
            'product-id'       => null,              // product id number
            'product'          => null,              // product name
            'solution-id'      => null,              // solution id number
            'status'           => null,              // status
            'time-spent'       => null,              // time spent, in seconds
            'workgroup-id'     => null,              // workgroup id number
            'workgroup'        => null,              // workgroup name
        );

        $args = array();

        foreach ($defaults as $key => $value) {
            if (isset($data[$key])) {
                $args[$key] = $data[$key];
            } elseif (!is_null($value)) {
                $args[$key] = $value;
            }
        }

        if (!isset($args['impact'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "impact" is required and not set');
        }

        if (!isset($args['origin'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "origin" is required and not set');
        }

        if (!isset($args['priority'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "priority" is required and not set');
        }

        if (!isset($args['problem'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "problem" is required and not set');
        }

        if (!isset($args['status'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "status" is required and not set');
        }

        if (!isset($args['workgroup-id']) && !isset($args['workgroup'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "workgroup" or "workgroup-id" is required and not set');
        }

        return $this->_request('calls', 'create-entry', $args);
    }

    /**
     * Retrieve call and related customer data for the specified call-id.
     *
     * @param string $entryId
     */
    public function callAttachmentGet($entryId)
    {
        $args = array(
            'entry_id' => $entryId
        );

		try{
			$result = $this->_request('calls-attachments', 'get-entry', $args);
		} catch (Ncstate_Service_Exception $e) {
			throw $e;
		}

		return $result;
    }

    /**
     * Retrieve the attachment records associated with the specified call-id.
     *
     * @param int $callId
     */
    public function callAttachmentList($callId)
    {
        $args = array(
            'call_id' 		=> $callId,
        	'start_record'	=> null,
        	'max_limit'		=> null,
        );

        return $this->_request('calls-attachments', 'get-list-entry', $args);
    }

    /**
     * Create an attachment associated with the specified call-id.
     *
     * @param string $callId
     * @param Array $data
     */
    public function callAttachmentCreate($callId, Array $data)
    {
        $defaults = array(
            'attachment_data' => null,     // required, Base64 encoded attachment
            'attachment_name' => null,     //required, name of the attachment
            'attachment_size' => null,     //required, size of the attachment, in bytes
            'call_id'         => $callId,  //required, id number of the call that this attachment is to be associated with. Value must be zero-padded on the left to a length of 8 characters
            'type'            => null,     //required, Email / Solution
            'status'          => null,     //required, Received / Outgoing / Sent / Hold / Solution
        );

        $args = array();

        foreach ($defaults as $key => $value) {
            if (isset($data[$key])) {
                $args[$key] = $data[$key];
            } elseif (!is_null($value)) {
                $args[$key] = $value;
            } else {
                require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Field for "' . $key . '" is required and not set');
            }
        }

        return $this->_request('calls-attachments', 'create-entry', $args);
    }

    /**
     * Retrieves the specified history entry.
     *
     * @param string $entryId
     */
    public function callHistoryGet($entryId)
    {
        $args = array(
            'entry_id' => $entryId,
        );

		return $this->_request('calls-history', 'get-entry', $args);
    }

    /**
     * Retrieves all of the history entries associated with the specified call-id.
     *
     * @param int $callId
     */
    public function callHistoryList($callId, $max_limit = null, $start_record = null)
    {
        $args = array(
            'call_id'		=> $callId,
        	'max_limit' 	=> $max_limit,
        	'start_record'	=> $start_record,
        );

        return $this->_request('calls-history', 'get-list-entry', $args);
    }

    /**
     * Retrieves information about a specified customer by Campus ID
     *
     * @param string $cid
     */
    public function customerGetByCID($cid)
    {
        $args = array(
            'cid' => $cid,
        	'login' => null,
        );

        return $this->_request('customers', 'get-entry', $args);
    }

    /**
     * Retrieves information about a specified customer by Unity ID
     *
     * @param string $login
     */
    public function customerGetByLogin($login)
    {
        $args = array(
            'login' => $login,
        	'cid' => null,
        );

        return $this->_request('customers', 'get-entry', $args);
    }

    /**
     * Retrieve information about the specified group by Workgroup ID
     *
     * @param string $workgroupId
     */
    public function workgroupGetByID($workgroupId)
    {
        $args = array(
            'group_id'	=> $workgroupId,
        	'group_name'=> null,
        );

        return $this->_request('workgroups', 'get-entry', $args);
    }

    /**
     * Retrieve information about the specified group by Workgroup Name
     *
     * @param string $workgroupName
     */
    public function workgroupGetByName($workgroupName)
    {
        $args = array(
            'group_name'	=> $workgroupName,
        	'group_id'		=> null,
        );

        return $this->_request('workgroups', 'get-entry', $args);
    }

    /**
     * Retrieve fields from group entries matching the specified qualification.
     *
     * @param string $qualification
     * @param (null|int) $startRecord
     * @param (null|int) $maxLimit
     */
    public function workgroupList($qualification, $startRecord = null, $maxLimit = null)
    {
        $args = array(
            'qualification' => $qualification,
        );

        if (!is_null($startRecord)) {
            $args['start-record'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['max-limit'] = $maxLimit;
        }

        return $this->_request('workgroups', 'get-list-entry', $args);
    }

    /**
     * Retrieve information about the specified user by user ID number
     *
     * @param int $userId
     */
    public function userGetByUserID($userId)
    {
        $args = array(
            'user_id' => $userId,
        );

        return $this->_request('users', 'get-entry', $args);
    }

    /**
     * Retrieve information about the specified user by Remedy login name
     *
     * @param string $login
     */
    public function userGetByLogin($login)
    {
        $args = array(
            'login_name' => $login,
        );

        return $this->_request('users', 'get-entry', $args);
    }

    /**
     * Validates the given credentials
     *
     * @param array $credentials
     */
    public function validateCredentials($credentials) {
        $args = array(
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        );

        return $this->_request('users', 'validate-credentials', $args);
    }

    /**
     * Retrieve user-id of user entries matching the specified qualification.
     *
     * @param string $qualification
     * @param (int|null) $startRecord
     * @param (int|null) $maxLimit
     */
    public function userList($qualification, $startRecord = null, $maxLimit = null)
    {
        $args = array(
            'qualification' => $qualification,
            'start_record' => $startRecord,
            'max_limit' => $maxLimit,
        );

        if (!is_null($startRecord)) {
            $args['start-record'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['max-limit'] = $maxLimit;
        }

        return $this->_request('users', 'get-list-entry', $args);
    }

    /**
     * Update the user record specified by user-id
     *
     * @param string $userId
     * @param array $data
     */
    public function userUpdate($userId, Array $data)
    {
        $defaults = array(
            'availability'             => null,     // is the user available/not/retired?
            'default-notify-mechanism' => null,     // user’s default notify mechanism
            'email-address'            => null,     // 'users email address for notifications, etc.
            'email-signature'          => null,     // user’s email sig.
            'initial-query'            => null,     // query to issue automatically issue at login
            'pager-address'            => null,     // email address of user’s pager
            'pager-template'           => null,     // user’s preferred page format template
            'password'                 => null,     // Remedy login password
            'products-count'           => null,     // count of products in user’s personal products menu
            'solutions-count'          => null,     // count of solutions in user’s personal solutions menu
            'user-id'                  => $userId,  // required, user’s id number
        );

        $args = array();

        foreach ($defaults as $key => $value) {
            if (isset($data[$key])) {
                $args[$key] = $data[$key];
            } elseif (!is_null($value)) {
                $args[$key] = $value;
            }
        }

        if (!isset($args['user-id'])) {
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Field for "user-id" is required and not set');
        }

        return $this->_request('calls', 'update-entry', $args);
    }

    /**
     * Retrieve a single solution specified by solution-id
     *
     * @param string $solutionId
     */
    public function solutionGet($solutionId)
    {
        $args = array(
            'solution_id' => $solutionId,
        );

        return $this->_request('solutions', 'get-entry', $args);
    }

    /**
     * Retrieve a list of solution-ids that match the specified qualification.
     *
     * Note that queries against this form may return the same solution-id
     * multiple times if the qualification includes multiple keywords.
     *
     * @param string $qualification
     * @param (null|int) $startRecord
     * @param (null|int) $maxLimit
     */
    public function solutionList($qualification, $startRecord = null, $maxLimit = null)
    {
        $args = array(
            'qualification' => $qualification,
        );

        if (!is_null($startRecord)) {
            $args['startRecord'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['maxLimit'] = $maxLimit;
        }

        return $this->_request('solutions', 'get-list-entry', $args);
    }

    /**
     * Retrieve entries matching the specified qualification.
     *
     * @param string $qualification
     * @param (null|int) $startRecord
     * @param (null|int) $maxLimit
     */
    public function surveyList($qualification, $startRecord = null, $maxLimit = null)
    {
        $args = array(
            'qualification' => $qualification,
        );

        if (!is_null($startRecord)) {
            $args['startRecord'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['maxLimit'] = $maxLimit;
        }

        return $this->_request('survey', 'get-list', $args);
    }

    /**
   	 * Sends a request using curl to the required URI
   	 *
   	 * @param string $method Untappd method to call
   	 * @param array $args key value array or arguments
   	 *
   	 * @throws Awsm_Service_Untappd_Exception
   	 *
   	 * @return stdClass object
   	 */
    protected function _request($wsdlEndpoint, $method, $args)
    {
        $soapArgs = new stdClass();

        // remove any unnecessary args from the query string
        foreach ($args as $key => $a) {
            $soapArgs->$key = ($a != '' && !is_null($a)) ? $a : '';
        }
        $wsdl = self::URI_BASE . '/' . $wsdlEndpoint;

        $this->_soapClient = new SoapClient($wsdl, array('trace' => true));

        $headers = array();

        $authHeader = new stdClass();
        $authHeader->userName = $this->_username;
        $authHeader->password = $this->_password;

        $authInfoHeader = new SoapVar($authHeader, SOAP_ENC_OBJECT, 'AuthenticationInfo');

        $headers[] = new SoapHeader('AuthenticationInfo', 'AuthenticationInfo', $authInfoHeader);

        $this->_soapClient->__setSoapHeaders($headers);

        try {
            $result = $this->_soapClient->{$method}($soapArgs);
        } catch (Exception $e) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('SOAP Error: ' . $e->getMessage());
        }
        return $result;
    }

    protected function _parseDigest($digest)
    {
        $entries = preg_split('/\x{f8e2}/u', $digest);

        $parsed = array();
        foreach ($entries as $e) {
            if (strlen(trim($e)) != 0) {
                $attributes = preg_split('/\x{f8e3}/u', $e);

                $entry = new stdClass();
                $entry->timestamp = $attributes[0];
                $entry->userName  = $attributes[1];
                $entry->entry     = $attributes[2];

                $parsed[] = $entry;
            }
        }

        return $parsed;
    }

    /**
     * Gets the soap client used in the service
     *
     * @return null|SoapClient object
     */
    public function getSoapClient()
    {
        return $this->_soapClient;
    }
}