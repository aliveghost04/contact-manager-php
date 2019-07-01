<?php

use \App\Router;

$router = new Router();

$router->get('/', 'ContactController', 'getAll');
$router->get('/:id', 'ContactController', 'get');
$router->post('/', 'ContactController', 'create');
$router->delete('/:id', 'ContactController', 'delete');
