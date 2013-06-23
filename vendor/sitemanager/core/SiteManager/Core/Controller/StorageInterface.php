<?php
/**
 * @file Contains SiteManager\Core\Controller\StorageInterface.
 */

namespace SiteManager\Core\Controller;


interface StorageInterface {

  public function __construct(array $definition);

  public function load($id);

  public function create(array $values);

  public function update($id, array $values);

  public function delete($id);

}