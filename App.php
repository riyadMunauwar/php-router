<?php 

require_once('Router.php');

// Define routes
$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/about', 'AboutController@index');
$router->get('/products/{id}', 'ProductController@show');
$router->post('/products', 'ProductController@store');

// Define middleware
$router->middleware(new AuthMiddleware());

// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$router->dispatch($method, $uri);