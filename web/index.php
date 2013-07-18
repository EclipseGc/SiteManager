<?php

$container = require_once __DIR__ . '/../src/container.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$response = $container->get('kernel.cache')->handle($request)->send();

