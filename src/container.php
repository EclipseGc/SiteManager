<?php

$loader = require_once __DIR__ . '/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use SiteManager\Core\Container;

$container = new ContainerBuilder();

$container->setParameter('loader', $loader);

$container->register('plugin.manager.tables', 'SiteManager\Core\TableManager')
  ->setArguments(array('%loader%'));
$container->register('plugin.manager.context', 'SiteManager\Core\ContextManager')
  ->setArguments(array('%loader%', new Reference('plugin.manager.tables')));
$container->register('plugin.manager.routes', 'SiteManager\Core\RouteManager')
  ->setArguments(array('%loader%', new Reference('plugin.manager.context')));

$container->register('controller.resolver', 'SiteManager\Core\ControllerResolver')
  ->setArguments(array(new Reference('plugin.manager.routes')));

Container::setContainer($container);

return $container;
