<?php
/**
 * @file Contains SiteManager\Core\UpcastableDataBase.
 */

namespace SiteManager\Core;

use SiteManager\Core\Controller\StorageInterface;
use Drupal\Component\Plugin\PluginBase;

class UpcastableDataBase extends PluginBase implements DataInterface {

  /**
   * The storage controller for this context.
   *
   * @var Controller\StorageInterface
   */
  protected $controller;

  public function __construct(StorageInterface $controller = NULL, array $configuration, $plugin_id, array $plugin_definition) {
    $this->controller = $controller;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public function all() {
    $class = get_class($this);
    $reflection = new \ReflectionClass($class);
    $values = array();
    foreach ($reflection->getProperties() as $property) {
      if ($property->class == $class) {
        $values[$property->name] = $this->{$property->name};
      }
    }
    return $values;
  }

  public function __get($name) {
    if (property_exists($this, $name)) {
      return $this->$name;
    }
  }

  public function __set($name, $value) {
    if (property_exists($this, $name)) {
      $this->$name = $value;
    }
  }

  public function save() {
    return isset($this->{$this->pluginDefinition['primary_key']}) ? $this->controller->update($this->pluginDefinition['primary_key'], $this) : $this->controller->create($this);
  }

}