<?php

$loader = require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/twig/twig/lib/Twig/Autoloader.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Bridge\Twig\Extension\FormExtension;
use SiteManager\Core\Service;
use Drupal\Core\Database\Database;

$container = new ContainerBuilder();

$reflected = new \ReflectionClass('Symfony\Bridge\Twig\Extension\FormExtension');
$path = dirname($reflected->getFileName()).'/../Resources/views/Form';

$container->setParameter('loader', $loader);
$container->setParameter('twig.autoloader', Twig_Autoloader::register());
$container->setParameter('twig.form.extension.resources', $path);
$container->setParameter('twig.i18n', new \Twig_Extensions_Extension_I18n());

$container->register('twig.loader', '\Twig_Loader_Filesystem')
  ->setArguments(array(array(__DIR__.'/../templates', '%twig.form.extension.resources%')));
$container->register('twig.environment', '\Twig_Environment')
  ->setArguments(array(new Reference('twig.loader'), array('cache' => __DIR__.'/../compiled', 'autoescape' => FALSE,)))
  ->addMethodCall('addExtension', array(new Reference('form.extension')))
  ->addMethodCall('addExtension', array('%twig.i18n%'));
$container->register('twig.renderer.engine', 'Symfony\Bridge\Twig\Form\TwigRendererEngine')
  ->setArguments(array(array('form_div_layout.html.twig')))
  ->addMethodCall('setEnvironment', array(new Reference('twig.environment')));
$container->register('twig.renderer', 'Symfony\Bridge\Twig\Form\TwigRenderer')
  ->setArguments(array(new Reference('twig.renderer.engine')));

$container->register('form.extension', 'Symfony\Bridge\Twig\Extension\FormExtension')
  ->setArguments(array(new Reference('twig.renderer')));

// Connections
$connection = require_once __DIR__ . '/../sites/default/dbconnection.php';
Database::addConnectionInfo('default', 'default', $connection);
$container->register('database.default', 'Drupal\Core\Database\Connection')
  ->setFactoryClass('Drupal\Core\Database\Database')
  ->setFactoryMethod('getConnection')
  ->setArguments(array('default', 'default'));
$container->register('sql.controller', 'SiteManager\Core\Controller\SqlStorageController')
  ->setArguments(array(new Reference('plugin.manager.context'), new Reference('database.default')));

$container->setParameter('yml.storage', dir('/var/www/sframework.drupal-testing.com/configuration'));
$container->register('yml.controller', 'SiteManager\Core\Controller\YamlStorageController')
  ->setArguments(array(new Reference('plugin.manager.context'), '%yml.storage%'));

$container->register('plugin.manager.tables', 'SiteManager\Core\TableManager')
  ->setArguments(array(new Reference('namespaces')));
$container->register('plugin.manager.context', 'SiteManager\Core\ContextManager')
  ->setArguments(array(new Reference('namespaces'), new Reference('container')));
$container->register('plugin.manager.routes', 'SiteManager\Core\RouteManager')
  ->setArguments(array(new Reference('namespaces'), new Reference('plugin.manager.context'), new Reference('twig.environment'), new Reference('twig.renderer')));

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

$container->setDefinition('namespaces', new Definition())
  ->setSynthetic(TRUE);
$container->set('namespaces', $loader->getPrefixes());
$container->setDefinition('container', new Definition())
  ->setSynthetic(TRUE);
$container->set('container', $container);

Service::setContainer($container);

return $container;
