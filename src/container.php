<?php

$loader = require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/../vendor/twig/twig/lib/Twig/Autoloader.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use SiteManager\Core\Service;

$container = new ContainerBuilder();

$container->setParameter('loader', $loader);
$container->setParameter('twig.autoloader', Twig_Autoloader::register());

$container->register('twig.loader', '\Twig_Loader_Filesystem')
  ->setArguments(array(__DIR__.'/../templates'));
$container->register('twig.environment', '\Twig_Environment')
  ->setArguments(array(new Reference('twig.loader'), array('cache' => __DIR__.'/../compiled')));


$container->register('plugin.manager.tables', 'SiteManager\Core\TableManager')
  ->setArguments(array('%loader%'));
$container->register('plugin.manager.context', 'SiteManager\Core\ContextManager')
  ->setArguments(array('%loader%', new Reference('plugin.manager.tables')));
$container->register('plugin.manager.routes', 'SiteManager\Core\RouteManager')
  ->setArguments(array('%loader%', new Reference('plugin.manager.context'), new Reference('twig.environment')));

$container->register('controller.resolver', 'SiteManager\Core\ControllerResolver')
  ->setArguments(array(new Reference('plugin.manager.routes')));
$container->register('symfony.resolver', 'Symfony\Component\HttpKernel\Controller\ControllerResolver');

$container->register('event.dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
  ->addMethodCall('addSubscriber', array(new Reference('plugin.router')))
  ->addMethodCall('addSubscriber', array(new Reference('response.listener')))
  ->addMethodCall('addSubscriber', array(new Reference('streamed.listener')));

$container->register('response.listener', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')
  ->setArguments(array('UTF-8'));
$container->register('streamed.listener', 'Symfony\Component\HttpKernel\EventListener\StreamedResponseListener');
$container->register('plugin.router', 'SiteManager\Core\PluginRouterListener')
  ->setArguments(array(new Reference('controller.resolver')));

$container->register('kernel', 'Symfony\Component\HttpKernel\HttpKernel')
  ->setArguments(array(new Reference('event.dispatcher'), new Reference('symfony.resolver')));
$container->register('kernel.cache', 'Symfony\Component\HttpKernel\HttpCache\HttpCache')
  ->setArguments(array(new Reference('kernel'), new Reference('kernel.cache.store')));
$container->register('kernel.cache.store', 'Symfony\Component\HttpKernel\HttpCache\Store')
  ->setArguments(array('%kernel.cache.dir%'));
$container->setParameter('kernel.cache.dir', __DIR__.'/../cache');

Service::setContainer($container);

return $container;
