<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__.'/dbconnection.php';

use Symfony\Component\HttpFoundation\Response;
use SiteManager\Core\TableManager;

$loader = sitemanager_autoloader();

$tableManager = new TableManager($loader);

$response = new Response($tableManager->installSchemas());
$response->send();

