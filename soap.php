<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2018 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

/**
 * @abstract    This is Head include file for Splash PHP Module on WebService Request
 * @author      B. Paquier <contact@splashsync.com>
 */

use Splash\Core\SplashCore      as Splash;
use Splash\Server\SplashServer;

//====================================================================//
//   INCLUDES
//====================================================================//

//====================================================================//
// Splash Module & Dependecies Autoloader
require_once(dirname(dirname(dirname(__FILE__))) . "/autoload.php");
require_once(dirname(__FILE__) . "/inc/fatal.inc.php");

//====================================================================//
// Setup Php Specific Settings
ini_set('display_errors', 0);
error_reporting(E_ERROR);

//====================================================================//
// Notice internal routines we are in server request mode
define("SPLASH_SERVER_MODE", 1);
    
//====================================================================//
//  SERVER MODE - Answer NuSOAP Requests
//====================================================================//
// Detect NuSOAP requests send by Splash Server
$userAgent  =   Splash::input("HTTP_USER_AGENT");
if ($userAgent && (false !== strpos($userAgent, "SOAP"))) {
    Splash::log()->deb("Splash Started In Server Mode");
    //====================================================================//
    //   Declare WebService Available Functions
    require_once(dirname(__FILE__) . "/inc/server.inc.php");
    //====================================================================//
    // Build SOAP Server & Register a method available for clients
    Splash::com()->buildServer();
    //====================================================================//
    // Register shuttdown method available for fatal errors reteival
    register_shutdown_function(__NAMESPACE__ . '\fatal_handler');
    //====================================================================//
    // Turn on output buffering
    ob_start();
    //====================================================================//
    // Process methods & Return the results.
    Splash::com()->handle();
} elseif (Splash::input("node", INPUT_GET) === Splash::configuration()->WsIdentifier) {
    Splash::log()->deb("Splash Started In System Debug Mode");
    //====================================================================//
    // Setup Php Errors Settings
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    //====================================================================//
    // Output Server Analyze & Debug
    echo SplashServer::getStatusInformations();
    //====================================================================//
    // Output Module Complete Log
    echo Splash::log()->getHtmlLogList();
} else {
    echo "This WebService Provide no Description";
}
