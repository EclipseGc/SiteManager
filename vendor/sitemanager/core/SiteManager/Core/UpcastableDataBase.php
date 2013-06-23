<?php
/**
 * @file Contains SiteManager\Core\UpcastableDataBase.
 */

namespace SiteManager\Core;

use SiteManager\Core\Controller\StorageInterface;

class UpcastableDataBase implements UpcastInterface, DataInterface {

  /**
   * The storage controller for this context.
   *
   * @var Controller\StorageInterface
   */
  protected $controller;

  public function __construct(StorageInterface $controller = NULL) {
    $this->controller = $controller;
  }

  public function load($id) {
    return $this->controller->load($id);
  }

  public function update($id, $values) {
    return $this->controller->update($id, $values);
  }

  public function __set($name, $value) {
    if (property_exists($this, $name)) {
      $this->$name = $value;
    }
  }

  public function __get($name) {
    if (property_exists($this, $name)) {
      return $this->$name;
    }
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

}