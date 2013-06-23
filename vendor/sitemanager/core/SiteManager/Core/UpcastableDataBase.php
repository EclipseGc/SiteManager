<?php
/**
 * @file Contains SiteManager\Core\UpcastableDataBase.
 */

namespace SiteManager\Core;

use SiteManager\Core\Controller\StorageInterface;

class UpcastableDataBase implements UpcastInterface, DataInterface {

  public static function upcast($id, StorageInterface $controller = NULL) {
    return $controller->load($id);
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

}