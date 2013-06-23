<?php
 
// framework/autoload.php
 
require_once __DIR__.'/vendor/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';
 
use Symfony\Component\ClassLoader\UniversalClassLoader;

function sitemanager_autoloader() {
  // Fix this once we have a Dependency Injection Container.
  static $loader;
  if (!$loader) {
    $loader = new UniversalClassLoader();
    $loader->register();
    $loader->registerNamespace('Symfony\\Component\\HttpFoundation', __DIR__.'/vendor/symfony/http-foundation');
    $loader->registerNamespace('Symfony\\Component\\Routing', __DIR__.'/vendor/symfony/routing');
    $loader->registerNamespace('Symfony\\Component\\Config', __DIR__.'/vendor/symfony/config');
    $loader->registerNamespace('Symfony\\Component\\Validator', __DIR__.'/vendor/symfony/validator');
    $loader->registerNamespace('Symfony\\Component\\Translation', __DIR__.'/vendor/symfony/translation');
    $loader->registerNamespace('Drupal\\Core\\Database', __DIR__.'/vendor/drupal/database');
    $loader->registerNamespace('Drupal\\Component\\Annotation', __DIR__.'/vendor/drupal/annotation');
    $loader->registerNamespace('Drupal\\Component\\Plugin', __DIR__.'/vendor/drupal/plugin');
    $loader->registerNamespace('Drupal\\Component\\Reflection', __DIR__.'/vendor/drupal/reflection');
    $loader->registerNamespace('Drupal\\Component\\Utility', __DIR__.'/vendor/drupal/utility');
    $loader->registerNamespace('Doctrine\\Common', __DIR__.'/vendor/doctrine/common/lib');
    $loader->registerNamespace('SiteManager\\Core', __DIR__.'/vendor/sitemanager/core');
  }
  return $loader;
}
