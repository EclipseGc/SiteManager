<?php
/**
 * @file Contains SiteManager\Core\UpcastInterface.
 */

namespace SiteManager\Core;

use SiteManager\Core\Controller\StorageInterface;

interface UpcastInterface {

  /**
   * Upcasts an identifier to a full class representation of that unique id.
   *
   * @param $id
   *   The unique identifier for a class of this type.
   * @param \SiteManager\Core\Controller\StorageInterface $controller
   *   An optional storage controller if necessary.
   *
   *
   * @return mixed
   *   Returns a full class instance based on the id value.
   */
  public static function upcast($id, StorageInterface $controller = NULL);

}