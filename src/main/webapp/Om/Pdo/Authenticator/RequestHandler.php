<?php
/**
 * User: ballmann
 * Date: 4/15/16
 */

namespace Om\Pdo\Authenticator;


class RequestHandler
{
    // success values
    const RESULT_OK = true;
    const RESULT_FAIL = false;

    // field names of json response
    const RESULT_FIELD = 'result';
    const RESULT_SALT = 'salt';
    const RESULT_CHARSET = 'charset';

    // action names to process
    const ACTION_GETSALT = 'getsalt';
    const ACTION_LOGIN = 'login';

    /**
     * @var DatabaseConfiguration
     */
    protected $dbConfiguration;

    /**
     * RequestHandler constructor.
     * @param DatabaseConfiguration $dbConfiguration
     */
    public function __construct($dbConfiguration)
    {
        $this->dbConfiguration = $dbConfiguration;
    }

    /**
     * Process all known actions.
     * @param string $action
     * @param string $username
     * @param string $password
     * @return array - with keys RESULT_FIELD/RESULT_SALT
     */
    public function process($action, $username, $password)
    {
        $authenticator = new PdoAuthenticator($this->dbConfiguration);

        $this->fixParametersCharset($username, $password, $authenticator, $tableCharset);

        $result = [];
        $result[self::RESULT_CHARSET] = $tableCharset;

        $this->handleActions($action, $username, $password, $authenticator, $result);

        return $result;
    }

    /**
     * Process one action.
     * @param string $action (required)
     * @param string $username (required)
     * @param string $password (optional)
     * @param PdoAuthenticator $authenticator (required)
     * @param array $result
     */
    protected function handleActions($action, $username, $password, $authenticator, &$result)
    {
        $result[self::RESULT_FIELD] = self::RESULT_FAIL;

        switch ($action) {
            case self::ACTION_GETSALT:
                $this->processGetSalt($username, $authenticator, $result);
                break;
            case self::ACTION_LOGIN:
                $this->processLoginCheck($username, $password, $authenticator, $result);
                break;
            default:
                // NOP
        }
    }

    /**
     * Get a user's salt.
     * @param string $username (required)
     * @param PdoAuthenticator $authenticator (required)
     * @param array $result
     */
    protected function processGetSalt($username, $authenticator, &$result)
    {
        $salt = $authenticator->getUsersSalt($username);
        $result[self::RESULT_FIELD] = ($salt != null) ? self::RESULT_OK : self::RESULT_FAIL;
        $result[self::RESULT_SALT] = $salt;
    }

    /**
     * Check a user's login.
     * @param string $username
     * @param string $password
     * @param PdoAuthenticator $authenticator
     * @param array $result
     */
    protected function processLoginCheck($username, $password, $authenticator, &$result)
    {
        $result[self::RESULT_FIELD] = $authenticator->checkUserLogin($username, $password) ? self::RESULT_OK : self::RESULT_FAIL;
    }

    /**
     * Extract the request's charset.
     * @return string
     */
    protected function getRequestCharset()
    {
        $requestHeaders = getallheaders();
        $contentType = $requestHeaders['Content-Type'];
        preg_match('/charset=([^ ]+)/i', $contentType, $match);
        $requestCharset = $match[1];
        return $requestCharset;
    }

    /**
     * Convert the request paramaters values if necessary.
     * 
     * @param string $username - from the current request
     * @param string $password - from the current request
     * @param PdoAuthenticator $authenticator
     * @param string $tableCharset
     */
    protected function fixParametersCharset(&$username, &$password, $authenticator, &$tableCharset)
    {
        $tableCharset = $authenticator->getTableCharset();

        $requestCharset = $this->getRequestCharset();

        // if database charset is different than the request charset, convert username and password
        if ($requestCharset && $requestCharset != $tableCharset) {
            // mbstring is a default package in debian/ubuntu
            $username = mb_convert_encoding($username, $tableCharset, $requestCharset);
            $password = mb_convert_encoding($password, $tableCharset, $requestCharset);
        }
    }
}