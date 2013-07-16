<?php

$container = require_once __DIR__ . '/../src/container.php';

use Symfony\Component\HttpFoundation\Response;

$response = new Response($container->get('plugin.manager.tables')->installSchemas());
$response->send();

