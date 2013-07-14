<?php
/**
 * @file
 * Contains SiteManager\Core\ContextManager
 */

namespace SiteManager\Core;

use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\PluginManagerBase;
use SiteManager\Core\DataInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContextManager extends PluginManagerBase {

  /**
   * @var array
   */
  protected $definitions = array();

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder.
   */
  protected $container;

  /**
   * Construct a context manager.
   *
   * @param array $namespaces
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *
   * @return \SiteManager\Core\ContextManager
   */
  public function __construct(array $namespaces, ContainerBuilder $container) {
    $this->container = $container;
    $annotation_dir = $namespaces['SiteManager\Core'];
    foreach ($namespaces as $namespace => $dir) {
      unset($namespaces[$namespace]);
      $namespaces[$namespace . '\Context'] = $dir;
    }
    $this->discovery = new AnnotatedClassDiscovery($namespaces, array('SiteManager\Core\Annotation\Context' => $annotation_dir));
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id) {
    if (!$this->definitions) {
      $this->getDefinitions();
    }
    if (isset($this->definitions[$plugin_id])) {
      return $this->definitions[$plugin_id];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    if (!$this->definitions) {
      $definitions = parent::getDefinitions();
      foreach ($definitions as $plugin_id => &$definition) {
        $this->processDefinition($definition, $plugin_id);
      }
      $this->definitions = $definitions;
    }
    return $this->definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    // Context plugins use a StorageInterface object to instantiate themselves
    // so we proxy to that.
    $storageController = $this->getStorage($plugin_id);
    // The storageController is a service, so we must tell it which context we
    // are working with.
    $definition = $this->getDefinition($plugin_id);
    if (isset($configuration['id'])) {
      return $storageController->load($plugin_id, $configuration['id']);
    }
    else {
      $instance = new $definition['class']($storageController, $configuration, $plugin_id, $definition);
      if (isset($configuration['values'])) {
        $this->populateValues($instance, $configuration['values']);
      }
      return $instance;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    $storage = $this->container->get($definition['storage']);
    if (in_array('SiteManager\Core\ProcessInterface', class_implements($storage))) {
      $storage->processDefinition($definition, $plugin_id);
    }
  }

  /**
   * Find a plugin by class name and instantiate it for the provided id.
   *
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    // Temporary hack until we have a cached index of context classes.
    $definitions = $this->getDefinitions();
    foreach ($definitions as $plugin_id => $definition) {
      if ($definition['class'] == $options['definition']['class']) {
        $configuration = array();
        if (isset($options['id'])) {
          $configuration['id'] = $options['id'];
        }
        return $this->createInstance($plugin_id, $configuration);
      }
    }
  }

  /**
   * Retrieve the storage class for a specific context.
   *
   * @param string $plugin_id
   *   The desire plugin for which to retrieve a storage class.
   *
   * @return SiteManager\Core\Controller\StorageInterface
   */
  public function getStorage($plugin_id) {
    $definition = $this->getDefinition($plugin_id);
    if ($definition['storage']) {
      return $this->container->get($definition['storage']);
    }
  }

  /**
   * Populate any values in a data object.
   *
   * @param DataInterface $instance
   *   The data object to populate.
   * @param array $values
   *   The values with which to populate the data object.
   */
  public function populateValues(DataInterface $instance, array $values) {
    $class_name = get_class($instance);
    $class = new \ReflectionClass($class_name);
    $properties = $class->getProperties();
    foreach ($properties as $property) {
      if ($property->class == $class_name && isset($values[$property->name])) {
        $instance->{$property->name} = $values[$property->name];
      }
    }
  }

}
