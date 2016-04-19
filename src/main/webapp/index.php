<?php
/**
 * User: ballmann
 * Date: 4/12/16
 *
 * Example Calls:
 *
 * Get a user's salt:
 * http://localhost/pdo-auth/index.php?action=getsalt&login=sb
 * returns:
 * {"result":true,"salt":"$1$rasmusl1"}
 *
 * Check a user's login:
 * http://localhost/pdo-auth/index.php?action=login&login=sb&pwd=$1$rasmusl1$2ASuKCrDVFQspP8.yIzVl.
 * returns:
 * {"result":true}
 */

// get the database settings
require_once 'dbconf.php';

require_once dirname(__FILE__) . "/Om/Pdo/Authenticator/PdoAuthenticator.php";
require_once dirname(__FILE__) . "/Om/Pdo/Authenticator/DatabaseQueryBuilder.php";
require_once dirname(__FILE__) . "/Om/Pdo/Authenticator/DatabaseConfiguration.php";
require_once dirname(__FILE__) . "/Om/Pdo/Authenticator/RequestHandler.php";

use \Om\Pdo\Authenticator\RequestHandler;
use \Om\Pdo\Authenticator\DatabaseConfiguration;

// evaluate request parameters ...
$login = $_POST['login'];
$pwd = $_POST['pwd'];
$action = $_POST['action'];

$dbConfig = new DatabaseConfiguration($rawDbConfig);
$requestHandler = new RequestHandler($dbConfig);
$result = $requestHandler->process($action, $login, $pwd);

echo json_encode($result);
echo "\n";

?>