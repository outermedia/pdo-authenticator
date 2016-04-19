<?php
/**
 * User: ballmann
 * Date: 4/15/16
 */

namespace Om\Pdo\Authenticator;


/**
 * Class DatabaseConfiguration
 * @package Om\Pdo\Authenticator
 * 
 * This class saves the database configuration of the authenticator.
 *
 */
class DatabaseConfiguration
{
    /**
     * @var string - a PDO url
     */
    protected $pdoUrl;
    /**
     * @var string - the username for the database login
     */
    protected $dbUser;
    /**
     * @var string - the password for the database login
     */
    protected $dbPassword;

    /**
     * @var string - the database table to query
     */
    protected $tableName;

    /**
     * @var string - the table column's name which contains the username
     */
    protected $usernameColumnName;

    /**
     * @var string - the table column's name which contains the hashed password
     */
    protected $passwordColumnName;


    /**
     * DatabaseConfiguration constructor.
     * @param $config - contains the database options.
     * Example usage:
     * <pre>
     *  new DatabaseConfiguration([
     *      'pdoUrl' =&gt; 'mysql:host=localhost;dbname=myproject',
     *      'dbUser' =&gt; 'admin',
     *      'dbPassword' =&gt; 'secure',
     *      'table' =&gt; 'users',
     *      'usernameColumn' =&gt; 'name',
     *      'passwordColumn' =&gt; 'pwd',
     *  ]);
     * </pre>     
     */
    public function __construct($config)
    {
        $this->pdoUrl = $config['pdoUrl'];
        $this->dbUser = $config['dbUser'];
        $this->dbPassword = $config['dbPassword'];
        $this->tableName = $config['table'];
        $this->usernameColumnName = $config['usernameColumn'];
        $this->passwordColumnName = $config['passwordColumn'];
    }

    /**
     * Get the configured PDO URL.
     * @return string
     */
    public function getPdoUrl()
    {
        return $this->pdoUrl;
    }

    /**
     * Get the configured database login username.
     * @return string
     */
    public function getDbUser()
    {
        return $this->dbUser;
    }

    /**
     * Get the configured database login password.
     * @return string
     */
    public function getDbPassword()
    {
        return $this->dbPassword;
    }

    /**
     * Get the database table name which contains the user information.
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get the column name of the user table which contains the username.
     * @return string
     */
    public function getUsernameColumnName()
    {
        return $this->usernameColumnName;
    }

    /**
     * Get the column name of the user table which contains a user's password.
     * @return string
     */
    public function getPasswordColumnName()
    {
        return $this->passwordColumnName;
    }
    
}