<?php

require_once __DIR__.'/autoload.php';
require_once __DIR__.'/dbconnection.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Database\Database;
use SiteManager\Core\RouteManager;
use SiteManager\Core\ContextManager;
use SiteManager\Core\TableManager;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

$loader = sitemanager_autoloader();

$request = Request::createFromGlobals();
$tableManager = new TableManager($loader);
$contextManager = new ContextManager($loader, $tableManager);
$routeManager = new RouteManager($loader, $contextManager);

try {
  $route = $routeManager->matchRoute($request);
  $routePlugin = $routeManager->createInstance($route['_route'], array('request' => $request, 'route' => $route));
  $response = new Response($routePlugin->render());
}
catch (ResourceNotFoundException $e) {
  $response = new Response();
  $response->setStatusCode(404);
  $response->setContent('Not Found');
}

$response->send();

