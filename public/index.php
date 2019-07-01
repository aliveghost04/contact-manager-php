<?php

require('../app.php');

use \App\RouteNotFoundException;
use \App\Controllers\BaseController;

try {
    $router->run();
} catch (RouteNotFoundException $e) {
    $baseController = new BaseController();

    $baseController->setJSON();
    $baseController->response([
        'message' => 'No matching route found'
    ], 404);
} catch (\Exception $e) {
    $baseController = new BaseController();
    
    // TODO: Log errors in some log file
    $baseController->setJSON();
    $baseController->response([
        'message' => 'SERVER ERROR. Please try again later'
    ], 500);
}
