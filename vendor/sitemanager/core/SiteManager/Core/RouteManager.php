<?php

namespace SiteManager\Core;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Database\Database;

class RouteManager extends PluginManagerBase {

  /**
   * @var ContextManager
   */
  protected $manager;

  public function __construct(UniversalClassLoader $loader, ContextManager $manager) {
    $this->manager = $manager;
    $namespaces = $loader->getNamespaces();
    $annotation_dir = $namespaces['SiteManager\Core'];
    foreach ($namespaces as $namespace => $dir) {
      unset($namespaces[$namespace]);
      $namespaces[$namespace . '\Route'] = $dir;
    }
    $this->discovery = new AnnotatedClassDiscovery($namespaces, array('SiteManager\Core\Annotation\Route' => $annotation_dir));
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
    $this->factory = new DefaultFactory($this);
  }

  public function getDefinition($plugin_id) {
    if (Database::getConnection()->schema()->tableExists('route')) {
      $definition = Database::getConnection()
        ->select('route', 'r')
        ->fields('r')
        ->condition('r.name', $plugin_id)
        ->execute()
        ->fetchAssoc();
      $this->unserialize($definition);
    }
    if (empty($definition)) {
      $definition = parent::getDefinition($plugin_id);
    }
    $this->processDefinition($definition, $plugin_id);
    return $definition;
  }

  public function getDefinitions() {
    if (Database::getConnection()->schema()->tableExists('route')) {
      $query = Database::getConnection()
        ->select('route', 'r')
        ->fields('r')
        ->execute();
    }
    if (isset($query) && $query->rowCount()) {
      $definitions = array();
      foreach ($query as $result) {
        $definitions[$result->name] = (array) $result;
        $this->unserialize($definitions[$result->name]);
      }
    }
    else {
      $definitions = parent::getDefinitions();
      foreach ($definitions as $plugin_id => &$definition) {
        $this->processDefinition($definition, $plugin_id);
        if (Database::getConnection()->schema()->tableExists('route')) {
          Database::getConnection()
            ->insert('route')
            ->fields(array('name', 'path', 'defaults', 'requirements', 'options', 'host', 'schemes', 'methods', 'path_root', 'class', 'context'))
            ->values(array(
              'name' => $definition['name'],
              'path' => $definition['path'],
              'defaults' => serialize($definition['defaults']),
              'requirements' => serialize($definition['requirements']),
              'options' => serialize($definition['options']),
              'host' => $definition['host'],
              'schemes' => serialize($definition['schemes']),
              'methods' => serialize($definition['methods']),
              'path_root' => $definition['path_root'],
              'class' => $definition['class'],
              'context' => serialize($definition['context'])
            ))
            ->execute();
        }
        print '<pre>' . print_r($definition, TRUE) . '</pre>';
      }
    }
    return $definitions;
  }

  public function createInstance($plugin_id, array $configuration = array()) {
    $instance = parent::createInstance($plugin_id, $configuration);
    if (isset($configuration['request'])) {
      $instance->setRequest($configuration['request']);
    }
    if (isset($configuration['route'])) {
      $contexts = $instance->getContextDefinitions();
      foreach ($contexts as $argument => $definition) {
        $options = array(
          'id' => $configuration['route'][$argument],
          'definition' => $definition,
        );
        $context = $this->manager->getInstance($options);
        $instance->setContextValue($argument, $context);
      }
    }
    return $instance;
  }

  public function getRoute($plugin_id, array $definition = array()) {
    if (!$definition) {
      $definition = $this->getDefinition($plugin_id);
    }
    if (isset($definition['class'])) {
      $class = new \ReflectionClass('Symfony\Component\Routing\Route');
      $args = array();
      foreach ($class->getMethod('__construct')->getParameters() as $param) {
        $param_name = $param->getName();
        if (array_key_exists($param_name, $definition)) {
          $args[$param_name] = $definition[$param_name];
        }
      }
      $route = $class->newInstanceArgs($args);
      return $route;
    }
  }

  public function getRouteCollection() {
    $collection = new RouteCollection();
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      $collection->add($plugin_id, $this->getRoute($plugin_id, $definition));
    }
    return $collection;
  }

  public function processDefinition(&$definition, $plugin_id) {
    $definition['defaults']['_controller'] = $definition['class'] . '::render';
    if (!isset($definition['path_root'])) {
      $path = explode('/', $definition['path']);
      $path_root = array();
      foreach ($path as $position => $value) {
        if (substr($value, 0, 1) != '{') {
          $path_root[] = $value;
        }
        else {
          break;
        }
      }
      $definition['path_root'] = implode("/", $path_root);
    }
    if (!isset($definition['name'])) {
      $definition['name'] = $definition['id'];
    }
    if (!isset($definition['context'])) {
      $definition['context'] = array();
    }
    parent::processDefinition($definition, $plugin_id);
  }

  public function getInstance(array $options) {
    $path = $options['path_info'];
    if (Database::getConnection()->schema()->tableExists('route')) {
      $definition = Database::getConnection()
        ->select('route', 'r')
        ->fields('r')
        ->condition('r.path', $path)
        ->execute()
        ->fetchAssoc();
      if ($definition) {
        $this->unserialize($definition);
        return $this->createInstance($definition['name']);
      }
    }
  }

  public function matchRoute(Request $request) {
    $context = new RequestContext();
    $context->fromRequest($request);

    $collection = new RouteCollection();
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      $collection->add($plugin_id, $this->getRoute($plugin_id, $definition));
    }

    $instance = $this->getInstance(array('path_info' => $request->getPathInfo()));
    if ($instance) {
      $collection->add($instance->getPluginId(), $this->getRoute($instance->getPluginId(), $instance->getPluginDefinition()));
    }
    else if (Database::getConnection()->schema()->tableExists('route')) {
      $path = $request->getPathInfo();
      $path = explode('/', $path);
      $path = array_reverse($path);
      foreach ($path as $arg => $element) {
        $path_root = array_reverse($path);
        array_pop($path_root);
        $path_root = implode('/', $path_root);
        $results = Database::getConnection()
          ->select('route', 'r')
          ->fields('r')
          ->condition('r.path_root', $path_root)
          ->execute();
        if ($results && $results->rowCount()) {
          foreach ($results as $result) {
            $definition = (array) $result;
            $this->unserialize($definition);
            $collection->add($definition['name'], $this->getRoute($definition['name'], $definition));
          }
          break;
        }
      }
    }
    else {
      $collection = $this->getRouteCollection();
    }
    $matcher = new UrlMatcher($collection, $context);
    return $matcher->match($request->getPathInfo());
  }

  protected function unserialize(array &$definition) {
    $definition['defaults'] = unserialize($definition['defaults']);
    $definition['requirements'] = unserialize($definition['requirements']);
    $definition['options'] = unserialize($definition['options']);
    $definition['schemes'] = unserialize($definition['schemes']);
    $definition['methods'] = unserialize($definition['methods']);
    $definition['context'] = unserialize($definition['context']);
  }
}