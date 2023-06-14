<?php

/**
 * Front controller
 */

use App\Libs\SessionSecurityHandler;

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('xdebug.var_display_max_depth', 10);
$sessionSecurityHandler = new SessionSecurityHandler();
$sessionSecurityHandler->startSession();

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('{controller}/{action}');
$router->add('{controller}', ['action' => 'index']);
$router->add('', ['controller' => 'Home', 'action' => 'index']);
    
$router->dispatch($_SERVER['REQUEST_URI']);