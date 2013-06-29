<?php

$container = require_once __DIR__ . '/../src/container.php';
require_once __DIR__ . '/../dbconnection.php';

use Symfony\Component\HttpFoundation\Response;

$response = new Response($container->get('plugin.manager.tables')->installSchemas());
$response->send();

