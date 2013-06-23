<?php
/**
 * @file Contains SiteManager\Core\Controller\SqlStorageControler.
 */

namespace SiteManager\Core\Controller;

use Drupal\Core\Database\Database;
use SiteManager\Core\TableManager;

class SqlStorageController implements StorageInterface {

  /**
   * Return status for saving which involved creating a new item.
   */
  const SAVED_NEW = 1;

  /**
   * Return status for saving which involved an update to an existing item.
   */
  const SAVED_UPDATED = 2;

  /**
   * Return status for saving which deleted an existing item.
   */
  const SAVED_DELETED = 3;

  /**
   * The plugin definition for the context we are working with.
   *
   * @var array
   */
  protected $definition;

  /**
   * @var \SiteManager\Core\TableManager.
   */
  protected $manager;

  public function __construct(array $definition, TableManager $manager) {
    $this->definition = $definition;
    $this->manager = $manager;
  }

  public function load($id) {
    return Database::getConnection()
      ->select($this->definition['base_table'], 'base_table')
      ->fields('base_table')
      ->condition('base_table.' . $this->definition['primary_key'], $id)
      ->execute()
      ->fetchObject($this->definition['class'], array($this));
  }

  public function create(array $values) {
    $this->write($values);
    return $values;
  }

  public function update($id, array $values) {
    $this->write($values, array($id));
    return $values;
  }

  public function delete($id) {}

  protected function write(array &$record, array $primary_keys = array()) {
    $schema = $this->manager->getSchema($this->definition['base_table']);
    if (empty($schema)) {
      return FALSE;
    }

    $object = new $this->definition['class']();
    foreach ($record as $key => $value) {
      $object->$key = $value;
    }
    $fields = array();
    $default_fields = array();

    // Go through the schema to determine fields to write.
    foreach ($schema['fields'] as $field => $info) {
      if ($info['type'] == 'serial') {
        // Skip serial types if we are updating.
        if (!empty($primary_keys)) {
          continue;
        }
        // Track serial field so we can helpfully populate them after the query.
        // NOTE: Each table should come with one serial field only.
        $serial = $field;
      }

      // Skip field if it is in $primary_keys as it is unnecessary to update a
      // field to the value it is already set to.
      if (in_array($field, $primary_keys)) {
        continue;
      }

      // Skip fields that are not provided, default values are already known
      // by the database. property_exists() allows to explicitly set a value to
      // NULL.
      if (!property_exists($object, $field)) {
        $default_fields[] = $field;
        continue;
      }

      // If $field is a serial type and the value is NULL, skip it.
      // @see http://php.net/manual/en/function.property-exists.php
      if ($info['type'] == 'serial' && !isset($object->$field)) {
        $default_fields[] = $field;
        continue;
      }

      // Build array of fields to update or insert.
      if (empty($info['serialize'])) {
        $fields[$field] = $object->$field;
      }
      else {
        $fields[$field] = serialize($object->$field);
      }

      // Type cast to proper datatype, except when the value is NULL and the
      // column allows this.
      if (isset($object->$field) || !empty($info['not null'])) {
        $fields[$field] = $this->getSchemaFieldValue($info, $fields[$field]);
      }
    }

    // Build the SQL.
    if (empty($primary_keys)) {
      // We are doing an insert.
      $options = array('return' => Database::RETURN_INSERT_ID);
      if (isset($serial) && isset($fields[$serial])) {
        // If the serial column has been explicitly set with an ID, then we don't
        // require the database to return the last insert id.
        if ($fields[$serial]) {
          $options['return'] = Database::RETURN_AFFECTED;
        }
        // If a serial column does exist with no value (i.e. 0) then remove it as
        // the database will insert the correct value for us.
        else {
          unset($fields[$serial]);
        }
      }
      // Create an INSERT query. useDefaults() is necessary for the SQL to be
      // valid when $fields is empty.
      $query = Database::getConnection()->insert($this->definition['base_table'])->fields($fields)->useDefaults($default_fields);

      $return = SqlStorageController::SAVED_NEW;
    }
    else {
      // Create an UPDATE query.
      $query = Database::getConnection()->update($this->definition['base_table'])->fields($fields);
      foreach ($primary_keys as $key) {
        $query->condition($key, $object->$key);
      }
      $return = SqlStorageController::SAVED_UPDATED;
    }

    // Execute the SQL.
    if ($query_return = $query->execute()) {
      if (isset($serial)) {
        // If the database was not told to return the last insert id, it will be
        // because we already know it.
        if (isset($options) && $options['return'] != Database::RETURN_INSERT_ID) {
          $object->$serial = $fields[$serial];
        }
        else {
          $object->$serial = $query_return;
        }
      }
    }
    // If we have a single-field primary key but got no insert ID, the
    // query failed. Note that we explicitly check for FALSE, because
    // a valid update query which doesn't change any values will return
    // zero (0) affected rows.
    elseif ($query_return === FALSE && count($primary_keys) == 1) {
      $return = FALSE;
    }

    // If we are inserting, populate empty fields with default values.
    if (empty($primary_keys)) {
      foreach ($schema['fields'] as $field => $info) {
        if (isset($info['default']) && !property_exists($object, $field)) {
          $object->$field = $info['default'];
        }
      }
    }


    $record = (array) $object;

    return $return;
  }

  protected function getSchemaFieldValue(array $info, $value) {
    if ($info['type'] == 'int' || $info['type'] == 'serial') {
      $value = (int) $value;
    }
    elseif ($info['type'] == 'float') {
      $value = (float) $value;
    }
    else {
      $value = (string) $value;
    }
    return $value;
  }
}