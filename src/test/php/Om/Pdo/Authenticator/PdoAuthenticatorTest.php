<?php
/**
 * User: ballmann
 * Date: 4/12/16
 *
 * Please note that the Apache PHP module php5-sqlite is required.
 * Composer is required too (see Readme.md).
 */

namespace Om\Pdo\Authenticator;

require dirname(__FILE__) . "/../../../../../../vendor/autoload.php";

use \PDO;

/**
 * Class PdoAuthenticatorTest
 * @package Om\Pdo\Authenticator
 */
class PdoAuthenticatorTest extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var PdoAuthenticator - an instance of the class under test
     */
    protected $authenticator;

    /**
     * @var PDO - database connection
     */
    protected $pdo;

    /**
     * Test database configuration. Please note that test test mocks the retrieval of the database connection.
     * Therefore the values of pdoUrl/dbUser/dbPassword below are not used.
     * @var array
     */
    protected $rawDbConfig = [
        'pdoUrl' => 'not used',
        'dbUser' => 'no used',
        'dbPassword' => 'not used',
        'table' => 'users',
        'usernameColumn' => 'name',
        'passwordColumn' => 'password',
    ];

    /**
     * @before
     */
    public function before()
    {
        // NOP
    }

    /**
     * @after
     */
    public function after()
    {
        $this->authenticator = null;
        $this->pdo = null;
    }

    /**
     * Prepare the in-memory database and return a new connection.
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $this->pdo = new PDO('sqlite::memory:');

        $dbConfig = new DatabaseConfiguration($this->rawDbConfig);

        // mock the authenticator in order to stub the pdoHandle() method
        $pdoHandleMock = 'pdoHandle';
        $this->authenticator = $this->getMockBuilder('Om\Pdo\Authenticator\PdoAuthenticator')
            ->enableOriginalConstructor()
            ->setConstructorArgs(array($dbConfig))
            ->setMethods(array($pdoHandleMock))
            ->getMock();

        // authenticator shall use the provided database connection and shall not create a new one
        $this->authenticator->method($pdoHandleMock)
            ->willReturn($this->pdo);

        // create the database table
        $this->pdo->exec("CREATE TABLE " . $dbConfig->getTableName() . " (" 
            . $dbConfig->getUsernameColumnName() . " varchar(50), " 
            . $dbConfig->getPasswordColumnName() . " varchar(50))");

        return $this->createDefaultDBConnection($this->pdo, ':memory:');
    }

    /**
     * Return the initial data of the database.
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/../../../../resources/test-data.xml');
    }
    
    // Tests ----------------------------

    public function testGetUnknownUsersSalt()
    {
        $salt = $this->authenticator->getUsersSalt('unknownuser');
        $this->assertEmpty($salt, "Expected no salt");
    }

    public function testGetKnownUsersSalt()
    {
        $salt = $this->authenticator->getUsersSalt('sb');
        $this->assertEquals('$1$rasmusl1', $salt);
    }

    public function testLoginUnknownUser()
    {
        $ok = $this->authenticator->checkUserLogin('unknownuser', '');
        $this->assertFalse($ok, "Login should fail for unknown user");
    }

    public function testLoginKnownUsersWrongPassword()
    {
        $ok = $this->authenticator->checkUserLogin('sb','dummy');
        $this->assertFalse($ok, "Login should fail for known user, but wrong hash");
    }

    public function testLoginKnownUsersRightPassword()
    {
        $ok = $this->authenticator->checkUserLogin('sb','$1$rasmusl1$2ASuKCrDVFQspP8.yIzVl.');
        $this->assertTrue($ok, "Login should succeed for known user, but wrong hash");
    }

    public function testExtractSaltFromInvalidHash()
    {
        $salt = $this->authenticator->extractSalt("");
        $this->assertNull($salt, "Expected null for invalid hash format");
    }

    public function testExtractSaltFromValidHash()
    {
        $salt = $this->authenticator->extractSalt('$1$abc$xxxxx');
        $this->assertEquals('$1$abc', $salt, "Got different salt than expected");
    }
}
