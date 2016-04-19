<?php
/**
 * User: ballmann
 * Date: 4/15/16
 */

namespace Om\Pdo\Authenticator;

require dirname(__FILE__) . "/../../../../../../vendor/autoload.php";

// overwrite getallheaders() in this namespace only
function getallheaders()
{
    return ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'];
}

class RequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    // methods to test ...
    const METHOD_CHECK_USER_LOGIN = 'checkUserLogin';
    const METHOD_GET_USERS_SALT = 'getUsersSalt';
    const METHOD_PROCESS_LOGIN_CHECK = 'processLoginCheck';
    const METHOD_PROCESS_GET_SALT = 'processGetSalt';
    const METHOD_HANDLE_ACTIONS = 'handleActions';
    const METHOD_GET_REQUEST_CHARSET = 'getRequestCharset';
    const METHOD_GET_TABLE_CHARSET = 'getTableCharset';
    
    /**
     * @var PdoAuthenticator
     */
    protected $authenticator;

    /**
     * @var RequestHandler
     */
    protected $handler;

    /**
     * @var array
     */
    protected $resultData;

    /**
     * Call a protected method.
     * @param $obj
     * @param $method
     * @param array $args
     * @return mixed
     */
    protected function runProtectedMethod($obj, $method, $args = array())
    {
        $method = new \ReflectionMethod(get_class($obj), $method);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * @before
     */
    protected function before()
    {
        // mock the PdoAuthenticator ...
        $this->authenticator = $this->getMockBuilder('Om\Pdo\Authenticator\PdoAuthenticator')
            ->disableOriginalConstructor()
            ->setMethods(array(self::METHOD_CHECK_USER_LOGIN, self::METHOD_GET_USERS_SALT, self::METHOD_GET_TABLE_CHARSET))
            ->getMock();
        $this->handler = new RequestHandler(null);
        $this->resultData = [];
    }

    public function testProcessLoginOkCheck()
    {
        $this->authenticator->method(self::METHOD_CHECK_USER_LOGIN)
            ->willReturn(RequestHandler::RESULT_OK);
        $this->runProtectedMethod($this->handler, self::METHOD_PROCESS_LOGIN_CHECK,
            array('any', 'any', $this->authenticator, &$this->resultData));
        $this->assertEquals(RequestHandler::RESULT_OK, $this->resultData[RequestHandler::RESULT_FIELD],
            "Successful login should set result to OK");
    }

    public function testProcessLoginFailCheck()
    {
        $this->authenticator->method(self::METHOD_CHECK_USER_LOGIN)
            ->willReturn(RequestHandler::RESULT_FAIL);
        $this->runProtectedMethod($this->handler, self::METHOD_PROCESS_LOGIN_CHECK,
            array('any', 'any', $this->authenticator, &$this->resultData));
        $this->assertEquals(RequestHandler::RESULT_FAIL, $this->resultData[RequestHandler::RESULT_FIELD],
            "Fail login should set result to FAIL");
    }

    public function testGetSaltFails()
    {
        $this->authenticator->method(self::METHOD_GET_USERS_SALT)
            ->willReturn(null);
        $this->runProtectedMethod($this->handler, self::METHOD_PROCESS_GET_SALT,
            array('any', $this->authenticator, &$this->resultData));
        $this->assertEquals(RequestHandler::RESULT_FAIL, $this->resultData[RequestHandler::RESULT_FIELD],
            "Fail login should set result to FAIL");
    }

    public function testGetSaltSucceeds()
    {
        $salt = '$1$abc123';
        $this->authenticator->method(self::METHOD_GET_USERS_SALT)
            ->willReturn($salt);
        $this->runProtectedMethod($this->handler, self::METHOD_PROCESS_GET_SALT,
            array('any', $this->authenticator, &$this->resultData));
        $this->assertEquals(RequestHandler::RESULT_OK, $this->resultData[RequestHandler::RESULT_FIELD],
            "Successful login should set result to OK");
        $this->assertEquals($salt, $this->resultData[RequestHandler::RESULT_SALT], "Got other salt than expected");
    }

    public function testGetSaltHandleActions()
    {
        $this->authenticator->method(self::METHOD_GET_USERS_SALT)
            ->willReturn('any');

        $this->runProtectedMethod($this->handler, self::METHOD_HANDLE_ACTIONS,
            array(RequestHandler::ACTION_GETSALT, 'any', 'any', $this->authenticator, &$this->resultData));
        $this->assertEquals(RequestHandler::RESULT_OK, $this->resultData[RequestHandler::RESULT_FIELD],
            "Successful getsalt action should set result to OK");
    }

    public function testLoginHandleActions()
    {
        $this->authenticator->method(self::METHOD_CHECK_USER_LOGIN)
            ->willReturn(RequestHandler::RESULT_OK);

        $this->runProtectedMethod($this->handler, self::METHOD_HANDLE_ACTIONS,
            array(RequestHandler::ACTION_LOGIN, 'any', 'any', $this->authenticator, &$this->resultData));
        $this->assertEquals(RequestHandler::RESULT_OK, $this->resultData[RequestHandler::RESULT_FIELD],
            "Successful login action should set result to OK");
    }

    public function testUnknownHandleActions()
    {
        $this->runProtectedMethod($this->handler, self::METHOD_HANDLE_ACTIONS,
            array('unknown', 'any', 'any', $this->authenticator, &$this->resultData));
        $this->assertEquals(RequestHandler::RESULT_FAIL, $this->resultData[RequestHandler::RESULT_FIELD],
            "Unknown action should set result to FAIL");
    }

    public function testGetRequestCharset()
    {
        $charset = $this->runProtectedMethod($this->handler, self::METHOD_GET_REQUEST_CHARSET, []);
        $this->assertEquals('utf-8', $charset, "Expected other charset");
    }
    
    public function testCharsetFix()
    {
        $this->authenticator->method(self::METHOD_GET_TABLE_CHARSET)
            ->willReturn('latin1');
        $user = "sb\xc3\xbc"; // sbü utf-8
        $pwd = "\xc3\xa4\xc3\xb6\xc3\xbc123"; // äöü123 utf-8
        $tableCharset = '';
        $this->runProtectedMethod($this->handler, 'fixParametersCharset', [&$user, &$pwd, $this->authenticator, &$tableCharset]);
        $this->assertEquals("sb\xfc", $user, "Expected username conversion from utf-8 to latin1");
        $this->assertEquals("\xe4\xf6\xfc123", $pwd, "Expected password conversion from utf-8 to latin1");
    }
}