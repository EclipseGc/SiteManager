<?php
/**
 * @file Contains SiteManager\Core\TableSchemaInterface.
 */

namespace SiteManager\Core;

interface TableSchemaInterface {

  /**
   * Defines a Drupal style table schema array.
   *
   * @return array
   */
  public function getSchema();

}