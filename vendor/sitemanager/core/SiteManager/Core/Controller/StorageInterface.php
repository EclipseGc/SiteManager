<?php
/**
 * @file Contains SiteManager\Core\Controller\StorageInterface.
 */

namespace SiteManager\Core\Controller;

use SiteManager\Core\DataInterface;

interface StorageInterface {

  public function load($id);

  public function loadMultiple(array $ids = array(), array $conditions = array());

  public function create(DataInterface $values);

  public function update($id, DataInterface $values);

  public function delete($id);

  public function deleteMultiple(array $ids = array());

}