<?php
/**
 * @file Contains SiteManager\Core\Controller\StorageInterface.
 */

namespace SiteManager\Core\Controller;

use SiteManager\Core\DataInterface;
use SiteManager\Core\TableManager;

interface StorageInterface {

  public function __construct(array $definition, TableManager $manager);

  public function load($id);

  public function create(DataInterface $values);

  public function update($id, DataInterface $values);

  public function delete($id);

}