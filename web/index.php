<?php

$container = require_once __DIR__ . '/../src/container.php';
require_once __DIR__ . '/../dbconnection.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$request->attributes->add($container->get('plugin.manager.routes')->matchRoute($request));
$response = $container->get('kernel.cache')->handle($request)->send();

