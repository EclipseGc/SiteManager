<?php

$container = require_once __DIR__ . '/src/container.php';
require_once __DIR__.'/dbconnection.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Database\Database;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

$request = Request::createFromGlobals();
$routeManager = $container->get('plugin.manager.routes');

try {
  $route = $routeManager->matchRoute($request);
  $routePlugin = $routeManager->createInstance($route['_route'], array('request' => $request, 'route' => $route));
  $response = $routePlugin->getResponse();
}
catch (ResourceNotFoundException $e) {
  $response = new Response();
  $response->setStatusCode(404);
  $response->setContent('Not Found');
}

$response->send();

