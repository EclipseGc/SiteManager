<?php
/**
 * @file Contains SiteManager\Core\Controller\SqlStorageControler.
 */

namespace SiteManager\Core\Controller;

use Drupal\Core\Database\Database;

class SqlStorageController implements StorageInterface {

  /**
   * The plugin definition for the context we are working with.
   *
   * @var array
   */
  protected $definition;

  public function __construct(array $definition) {
    $this->definition = $definition;
  }

  public function load($id) {
    return Database::getConnection()
      ->select($this->definition['base_table'], 'base_table')
      ->fields('base_table')
      ->condition('base_table.' . $this->definition['primary_key'], $id)
      ->execute()
      ->fetchObject($this->definition['class']);
  }

  public function create(array $values) {

  }

  public function update($id, array $values) {}

  public function delete($id) {}
}