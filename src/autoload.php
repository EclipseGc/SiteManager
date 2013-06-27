<?php
 
require_once __DIR__ . '/../vendor/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';
 
use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use SiteManager\Core\Container;

$map = require __DIR__ . '/../vendor/composer/autoload_namespaces.php';
$loader = new UniversalClassLoader();
$loader->register();
foreach ($map as $namespace => $dir) {
  if (substr($namespace, -1) == '\\') {
    $namespace = substr($namespace, 0, -1);
  }
  $loader->registerNamespace($namespace, $dir);
}
return $loader;
