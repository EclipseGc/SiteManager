<?php

$container = require_once __DIR__ . '/src/container.php';
require_once __DIR__.'/dbconnection.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Database\Database;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

$request = Request::createFromGlobals();
$routeManager = $container->get('plugin.manager.routes');
$resolver = $container->get('controller.resolver');

try {
  $request->attributes->add($routeManager->matchRoute($request));

  $controller = $resolver->getController($request);
  $arguments = $resolver->getArguments($request, $controller);

  $response = call_user_func_array($controller, $arguments);
}
catch (ResourceNotFoundException $e) {
  $response = new Response();
  $response->setStatusCode(404);
  $response->setContent('Not Found');
}

$response->send();

