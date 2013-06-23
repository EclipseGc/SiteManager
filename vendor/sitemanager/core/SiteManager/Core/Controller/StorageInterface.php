<?php
/**
 * @file Contains SiteManager\Core\Controller\StorageInterface.
 */

namespace SiteManager\Core\Controller;

use SiteManager\Core\TableManager;

interface StorageInterface {

  public function __construct(array $definition, TableManager $manager);

  public function load($id);

  public function create(array $values);

  public function update($id, array $values);

  public function delete($id);

}