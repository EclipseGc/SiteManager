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

$routeManager->clearCachedDefinitions();
$storage = $contextManager->getStorage('route');
$routes = $storage->loadMultiple();
$output = '';
if (!count($routes)) {
  $output .= "All routes have been deleted.<br />";
}
$routeManager->buildCachedDefinitions();
$new_routes = $storage->loadMultiple();
if (!count($routes) && count($new_routes)) {
  $output .= "Routes have been rebuilt.<br />";
}

$response = new Response($output);

$response->send();

