<?php
/**
 * @file
 * Contains SiteManager\Core\TableManager.
 */

namespace SiteManager\Core;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Symfony\Component\ClassLoader\UniversalClassLoader;
use Drupal\Core\Database\SchemaObjectDoesNotExistException;
use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\Core\Database\Database;
use Drupal\Component\Utility\String;

class TableManager extends PluginManagerBase {

  public function __construct(UniversalClassLoader $loader) {
    $namespaces = $loader->getNamespaces();
    $annotation_dir = $namespaces['SiteManager\Core'];
    foreach ($namespaces as $namespace => $dir) {
      unset($namespaces[$namespace]);
      $namespaces[$namespace . '\Tables'] = $dir;
    }
    $this->discovery = new AnnotatedClassDiscovery($namespaces, array('SiteManager\Core\Annotation\Table' => $annotation_dir));
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
    $this->factory = new DefaultFactory($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    $definitions = $this->getDefinitions();
    foreach ($definitions as $plugin_id => $definition) {
      if (in_array($options['table'], $definition['tables'])) {
        return $this->createInstance($plugin_id);
      }
    }
  }

  public function getSchema($table) {
    $instance = $this->getInstance(array('table' => $table));
    $schema = $instance->getSchema();
    if (isset($schema[$table])) {
      return $schema[$table];
    }
    throw new SchemaObjectDoesNotExistException("Table does not exist.");
  }

  public function getSchemas() {
    $definitions = $this->getDefinitions();
    $schema = array();
    foreach ($definitions as $plugin_id => $definition) {
      $instance = $this->createInstance($plugin_id);
      foreach ($instance->getSchema() as $name => $table) {
        if (!isset($schema[$name])) {
          $schema[$name] = $table;
        }
      }
    }
    return $schema;
  }

  public function installSchemas() {
    $schema = $this->getSchemas();
    $output = '';
    foreach ($schema as $name => $table) {
      try {
        Database::getConnection()->schema()->createTable($name, $table);
        $output .= String::format('The @name table has been successfully created.', array('@name' => $name));
      }
      catch (SchemaObjectExistsException $e) {
        $output .= String::format('@message It has been skipped.', array('@message' => $e->getMessage()));
      }
    }
    return $output;
  }
}