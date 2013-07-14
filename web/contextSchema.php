<?php

$container = require_once __DIR__ . '/../src/container.php';
require_once __DIR__ . '/../dbconnection.php';

$contexts = $container->get('plugin.manager.context')->getDefinitions();

print '<pre>' . print_r($contexts, TRUE) . '</pre>';

