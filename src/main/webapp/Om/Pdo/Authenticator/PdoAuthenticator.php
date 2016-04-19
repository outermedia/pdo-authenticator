<?php

namespace Om\Pdo\Authenticator;

use \PDO;
use \PDOException;

/**
 * User: ballmann
 * Date: 4/12/16
 */
class PdoAuthenticator
{
    /**
     * @var DatabaseConfiguration
     */
    protected $dbConfiguration;

    /**
     * PdoAuthenticator constructor.
     * @param DatabaseConfiguration $dbConfiguration (required)
     */
    public function __construct(DatabaseConfiguration $dbConfiguration)
    {
        $this->dbConfiguration = $dbConfiguration;
    }

    /**
     * Retrieve a user's salt.
     * @param string $username - required String
     * @return string - null (not found) or a String
     */
    public function getUsersSalt($username)
    {
        $salt = null;
        try {
            $dbConfig = $this->dbConfiguration;
            $stmt = DatabaseQueryBuilder::newBuilder()
                ->table($dbConfig->getTableName())
                ->ifEqual($dbConfig->getUsernameColumnName(), $username)
                ->buildSql()
                ->buildPdoStatement($this->pdoHandle());
            if ($stmt->execute()) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $salt = $this->extractSalt($row[$dbConfig->getPasswordColumnName()]);
                }
            }
            $stmt = null;
        } catch (PDOException $e) {
            error_log("Can't get user's salt ($username) from database: " . $e->getMessage());
        }
        $dbh = null;

        return $salt;
    }

    /**
     * Extracts the database table's charset. The table name is configured in 
     * {@see PdoAuthenticator::dbConfiguration}. The implementation uses the MYSQL specific "SHOW CREATE TABLE"
     * statement.
     *
     * @return string
     */
    public function getTableCharset()
    {
        $charset = null;
        $dbConfig = $this->dbConfiguration;
        $tableName = $dbConfig->getTableName();
        try {
            $stmt = $this->pdoHandle()->query('SHOW CREATE TABLE ' . $tableName);
            foreach ($stmt as $row) {
                $createStmt = $row['Create Table'];
                preg_match('/CHARSET=([^ ]+)/', $createStmt, $match);
                $charset = $match[1];
            }
            $stmt = null;
        } catch (PDOException $e) {
            error_log("Can't get table's charset ($tableName) from database: " . $e->getMessage());
        }
        return $charset;
    }

    /**
     * Check a user's login. The given password is directly compared to the stored one.
     * @param string $username required String
     * @param string $password required String
     * @return bool
     */
    public function checkUserLogin($username, $password)
    {
        $result = false;
        try {
            $dbConfig = $this->dbConfiguration;
            $stmt = DatabaseQueryBuilder::newBuilder()
                ->table($dbConfig->getTableName())
                ->returnField($dbConfig->getUsernameColumnName())
                ->ifEqual($dbConfig->getUsernameColumnName(), $username)
                ->ifEqual($dbConfig->getPasswordColumnName(), $password)
                ->buildSql()
                ->buildPdoStatement($this->pdoHandle());
            if ($stmt->execute()) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $result = true;
                }
            }
            $stmt = null;
        } catch (PDOException $e) {
            error_log("Can't check login of user $username in database: " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Create a pdo handle.
     * @return PDO
     */
    public function pdoHandle()
    {
        $dbConfig = $this->dbConfiguration;
        return new PDO($dbConfig->getPdoUrl(), $dbConfig->getDbUser(), $dbConfig->getDbPassword());
    }

    /**
     * Get the salt from the password value.
     * @param string $password - has the format $&lt;Type&gt;$&lt;Salt&gt;$&lt;Hash&gt;
     * @return string
     */
    public function extractSalt($password)
    {
        $salt = null;
        $tok = explode('$', $password);
        if (count($tok) == 4) {
            $salt = '$' . $tok[1] . '$' . $tok[2];
            return $salt;
        }
        return $salt;
    }
}