<?php

$loader = require_once __DIR__ . '/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use SiteManager\Core\Service;

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
$container->register('event.dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher');
$container->register('framework.base', 'Symfony\Component\HttpKernel\HttpKernel')
  ->setArguments(array(new Reference('event.dispatcher'), new Reference('controller.resolver')));
$container->register('framework', 'Symfony\Component\HttpKernel\HttpCache\HttpCache')
  ->setArguments(array(new Reference('framework.base'), new Reference('framework.cache.store')));
$container->register('framework.cache.store', 'Symfony\Component\HttpKernel\HttpCache\Store')
  ->setArguments(array('%framework.cache.dir%'));
$container->setParameter('framework.cache.dir', __DIR__.'/../cache');

Service::setContainer($container);

return $container;
