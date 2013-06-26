<?php
/**
 * @file Contains SiteManager\Core\ContextManager
 */

namespace SiteManager\Core;

use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\PluginManagerBase;
use Symfony\Component\ClassLoader\UniversalClassLoader;
use SiteManager\Core\DataInterface;

class ContextManager extends PluginManagerBase {

  /**
   * The TableManager for dealing with tables.
   *
   * @var SiteManager\Core\TableManager.
   */
  protected $manager;

  /**
   * Construct a context manager.
   *
   * @param UniversalClassLoader $loader
   *   An autoloader
   * @param TableManager $manager
   */
  public function __construct(UniversalClassLoader $loader, TableManager $manager) {
    $this->manager = $manager;
    $namespaces = $loader->getNamespaces();
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
  public function createInstance($plugin_id, array $configuration = array()) {
    $definition = $this->getDefinition($plugin_id);
    // Context plugins use a StorageInterface object to instantiate themselves
    // so we proxy to that.
    $storageController = $this->getStorage($plugin_id);
    if (isset($configuration['id'])) {
      return $storageController->load($configuration['id']);
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
   * Populate any values in a data object.
   *
   * @param DataInterface $instance
   *   The data object to populate.
   * @param array $values
   *   The values with which to populate the data object.
   */
  protected function populateValues(DataInterface $instance, array $values) {
    $class_name = get_class($instance);
    $class = new \ReflectionClass($class_name);
    $properties = $class->getProperties();
    foreach ($properties as $property) {
      if ($property->class == $class_name && isset($values[$property->name])) {
        $instance->{$property->name} = $values[$property->name];
      }
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
        $configuration = array(
          'id' => $options['id'],
        );
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
      return new $definition['storage']($definition, $this->manager);
    }
  }
}
