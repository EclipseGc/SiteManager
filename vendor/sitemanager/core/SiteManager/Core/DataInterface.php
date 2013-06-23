<?php
/**
 * @file Contains SiteManager\Core\DataInterface.
 */

namespace SiteManager\Core;


interface DataInterface {

  public function __get($name);

  public function __set($name, $value);

}