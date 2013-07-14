<?php


$container = require_once __DIR__ . '/../src/container.php';

use Symfony\Component\HttpFoundation\Response;

$routeManager = $container->get('plugin.manager.routes');

$routeManager->clearCachedDefinitions();
$storage = $container->get('plugin.manager.context')->getStorage('route');
$routes = $storage->loadMultiple('route');
$output = '';
if (!count($routes)) {
  $output .= "All routes have been deleted.<br />";
}
$routeManager->buildCachedDefinitions();
$new_routes = $storage->loadMultiple('route');
if (!count($routes) && count($new_routes)) {
  $output .= "Routes have been rebuilt.<br />";
}

$response = new Response($output);

$response->send();

