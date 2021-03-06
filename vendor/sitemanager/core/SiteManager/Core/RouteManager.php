<?php

namespace SiteManager\Core;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Database\Database;
use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Form\FormRendererInterface;

class RouteManager extends PluginManagerBase {

  /**
   * The context manager for context resolution.
   *
   * @var ContextManager
   */
  protected $manager;

  /**
   * The twig environment to run templating through.
   *
   * @var Twig_Environment
   */
  protected $environment;

  /**
   * @var \Symfony\Component\Form\FormRendererInterface
   */
  protected $engine;

  /**
   * Previously loaded route definitions.
   *
   * @var array();
   */
  protected $definitions;

  /**
   * Sets up the discovery & factory for route plugins.
   *
   * This consumes the ContextManager in addition to
   * @param UniversalClassLoader $loader
   * @param ContextManager $manager
   */
  public function __construct(array $namespaces, ContextManager $manager, \Twig_Environment $environment, FormRendererInterface $engine) {
    $this->manager = $manager;
    $this->environment = $environment;
    $this->engine = $engine;
    $annotation_dir = $namespaces['SiteManager\Core'];
    foreach ($namespaces as $namespace => $dir) {
      unset($namespaces[$namespace]);
      $namespaces[$namespace . '\Route'] = $dir;
    }
    $this->discovery = new AnnotatedClassDiscovery($namespaces, array('SiteManager\Core\Annotation\Route' => $annotation_dir));
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
    $this->factory = new DefaultFactory($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id) {
    if (isset($this->definitions[$plugin_id])) {
      return $this->definitions[$plugin_id];
    }
    // Routes are cached in their own context table, so the storage controller
    // for the context is loaded in order to attempt to load requested route.
    $storage = $this->manager->getStorage('route');
    $route = $storage->loadMultiple('route', array(), array('name' => $plugin_id));
    // Name is unique, it's just not the primary key.
    $route = array_pop($route);
    // If we have a route, return its values.
    if ($route) {
      $this->definitions[$plugin_id] = $route->all();
      return $this->definitions[$plugin_id];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    if ($this->definitions) {
      return $this->definitions;
    }
    $controller = $this->manager->getStorage('route');
    $routes = $controller->loadMultiple('route');
    foreach ($routes as $route) {
      $this->definitions[$route->name] = $route->all();
    }
    return $this->definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    $instance = parent::createInstance($plugin_id, $configuration);
    if (isset($configuration['request'])) {
      $instance->setRequest($configuration['request']);
    }
    $instance->setTwigEnvironment($this->environment);
    $instance->setEngine($this->engine);
    $this->setRouteContext($instance, $configuration['request']->attributes->all());
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition($definition, $plugin_id) {
    $definition->defaults += array('_controller' => $definition->class . '::getResponse');
    $path = explode('/', $definition->path);
    $path_root = array();
    foreach ($path as $position => $value) {
      if (substr($value, 0, 1) != '{') {
        $path_root[] = $value;
      }
      else {
        break;
      }
    }
    $definition->path_root = implode("/", $path_root);

    if (!isset($definition->name)) {
      $definition->name = $plugin_id;
    }
    if (!$definition->context) {
      $definition->context = array();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    $storage = $this->manager->getStorage('route');
    $storage->deleteMultiple('route');
  }

  /**
   * Retrieve definitions from annotations and cache them in the route table.
   */
  public function buildCachedDefinitions() {
    $definitions = parent::getDefinitions();
    foreach ($definitions as $plugin_id => &$definition) {
      $route = $this->manager->createInstance('route', array('values' => $definition));
      $this->processDefinition($route, $plugin_id);
      $route->is_new = TRUE;
      $route->save();
    }
  }

  /**
   * Set any available contexts from the request into the route plugin.
   *
   * @param RouteInterface $instance
   *   The instantiated Route plugin.
   * @param array $route_info
   *   The route information as generated by UrlMatcherInterface::match().
   */
  protected function setRouteContext(RouteInterface $instance, array $route_info) {
    if (!$route_info) {
      return;
    }
    $contexts = $instance->getContextDefinitions();
    foreach ($contexts as $argument => $definition) {
      $options = array(
        'definition' => $definition,
      );
      if (isset($route_info[$argument])) {
        $options['id'] = $route_info[$argument];
      }
      $context = $this->manager->getInstance($options);
      $instance->setContextValue($argument, $context);
    }
  }

  /**
   * Generate a Symfony Route object from the plugin definition.
   *
   * @param $plugin_id
   *   The plugin id to generate the route from.
   *
   * @return Symfony\Component\Routing\Route
   */
  public function getRoute($plugin_id) {
    $definition = $this->getDefinition($plugin_id);
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

  /**
   * Generate a route collection from all route plugins.
   *
   * @return Symfony\Component\Routing\RouteCollection
   */
  public function getRouteCollection() {
    $collection = new RouteCollection();
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      $collection->add($plugin_id, $this->getRoute($plugin_id));
    }
    return $collection;
  }

  /**
   * Matches the current request with a route and returns route information.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return array
   */
  public function matchRoute(Request $request) {
    $context = new RequestContext();
    $context->fromRequest($request);

    $collection = new RouteCollection();

    // Attempt to build a route collection by the current path.
    $storage = $this->manager->getStorage('route');
    foreach ($storage->loadMultiple('route', array(), array('path' => $request->getPathInfo())) as $route) {
      $collection->add($route->name, $this->getRoute($route->name));
    }
    // Attempt to build a route collection by path_root.
    if (!$collection->count()) {
      $path = $request->getPathInfo();
      $path = explode('/', $path);
      $path = array_reverse($path);
      foreach ($path as $arg => $element) {
        $path_root = array_reverse($path);
        array_pop($path_root);
        $path_root = implode('/', $path_root);
        $routes = $storage->loadMultiple('route', array(), array('path_root' => $path_root));
        if ($routes) {
          foreach ($routes as $route) {
            $collection->add($route->name, $this->getRoute($route->name));
          }
          break;
        }
      }
    }
    $matcher = new UrlMatcher($collection, $context);
    return $matcher->match($request->getPathInfo());
  }

}
