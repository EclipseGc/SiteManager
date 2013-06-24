<?php
/**
 * @file Contains SiteManager\Core\ContextManager
 */

namespace SiteManager\Core;

use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\PluginManagerBase;
use Symfony\Component\ClassLoader\UniversalClassLoader;

class ContextManager extends PluginManagerBase {

  /**
   * The TableManager for dealing with tables.
   *
   * @var SiteManager\Core\TableManager.
   */
  protected $manager;

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
   * Context plugins use a StorageInterface object to instantiate themselves so
   * we proxy to that.
   *
   * @param string $plugin_id
   * @param array $configuration
   * @return object
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    $definition = $this->getDefinition($plugin_id);
    if ($definition) {
      $storageController = new $definition['storage']($definition, $this->manager);
      $context = new $definition['class']($storageController, $configuration, $plugin_id, $definition);
      if (isset($configuration['id'])) {
        return $context->load($configuration['id']);
      }
      else {
        return $context;
      }
    }
  }

  /**
   * Find a plugin by class name and instantiate it for the provided id.
   *
   * @param array $options
   * @return \Drupal\Component\Plugin\Mapper\false|object
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
}