<?php
/**
 * @file
 * Contains SiteManager\Core\Controller\StorageInterface.
 */

namespace SiteManager\Core\Controller;

use SiteManager\Core\DataInterface;

interface StorageInterface {

  public function load($plugin_id, $id);

  public function loadMultiple($plugin_id, array $ids = array(), array $conditions = array());

  public function create($plugin_id, DataInterface $values);

  public function update($plugin_id, $id, DataInterface $values);

  public function delete($plugin_id, $id);

  public function deleteMultiple($plugin_id, array $ids = array());

}