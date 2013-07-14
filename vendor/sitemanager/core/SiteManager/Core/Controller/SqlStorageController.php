<?php
/**
 * @file
 * Contains SiteManager\Core\Controller\SqlStorageControler.
 */

namespace SiteManager\Core\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use SiteManager\Core\DataInterface;
use SiteManager\Core\ContextManager;
use SiteManager\Core\ProcessInterface;

class SqlStorageController implements StorageInterface, ProcessInterface {

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
   * @var \SiteManager\Core\TableManager.
   */
  protected $manager;

  /**
   * The database connection to use.
   *
   * @var \Drupal\Core\Database\Connection.
   */
  protected $connection;

  public function __construct(ContextManager $manager, Connection $connection) {
    $this->manager = $manager;
    $this->connection = $connection;
  }

  public function load($plugin_id, $id) {
    if (!$this->connection->schema()->tableExists($plugin_id)) {
      return;
    }
    $definition = $this->manager->getDefinition($plugin_id);
    $object = $this->connection
      ->select($plugin_id, 'base_table')
      ->fields('base_table')
      ->condition('base_table.' . $definition['primary_key'], $id)
      ->execute()
      ->fetchObject($definition['class'], array($this, array('id' => $id), $plugin_id, $definition));
    if ($object) {
      $this->resultUnserialize($definition, $object);
      return $object;
    }
  }

  public function loadMultiple($plugin_id, array $ids = array(), array $conditions = array()) {
    if (!$this->connection->schema()->tableExists($plugin_id)) {
      return array();
    }
    $definition = $this->manager->getDefinition($plugin_id);
    $query = $this->connection
      ->select($plugin_id, 'base_table')
      ->fields('base_table');
    if ($ids) {
      $query->condition('base_table.' . $definition['primary_key'], $ids, 'IN');
    }
    foreach ($conditions as $key => $value) {
      $query->condition('base_table.' . $key, $value);
    }
    $results = $query->execute();
    $results->setFetchMode(\PDO::FETCH_CLASS, $definition['class'], array($this, array(), $plugin_id, $definition));
    $objects = $results->fetchAllAssoc($definition['primary_key']);
    foreach ($objects as $id => $object) {
      $this->resultUnserialize($definition, $object);
    }
    return $objects;
  }

  protected function resultUnserialize(array $definition, DataInterface $data) {
    $schema = $definition['schema'];
    if (empty($schema)) {
      return FALSE;
    }

    foreach ($schema['fields'] as $field => $info) {
      if (isset($info['serialize']) && $info['serialize'] && is_string($data->$field)) {
        $data->$field = unserialize($data->$field);
      }
    }
  }

  public function create($plugin_id, DataInterface $values) {
    return $this->write($plugin_id, $values);
  }

  public function update($plugin_id, $id, DataInterface $values) {
    return $this->write($plugin_id, $values, array($id));
  }

  public function delete($plugin_id, $id) {}

  public function deleteMultiple($plugin_id, array $ids = array()) {
    if (!$ids) {
      $this->connection->truncate($plugin_id)->execute();
    }
  }

  protected function write($plugin_id, DataInterface $object, array $primary_keys = array()) {
    $definition = $this->manager->getDefinition($plugin_id);
    $schema = $definition['schema'];
    if (empty($schema)) {
      return FALSE;
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
    if (empty($primary_keys) || (!empty($primary_keys) && $object->is_new)) {
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
      $query = $this->connection->insert($plugin_id)->fields($fields)->useDefaults($default_fields);

      $return = SqlStorageController::SAVED_NEW;
    }
    else {
      // Create an UPDATE query.
      $query = $this->connection->update($plugin_id)->fields($fields);
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

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    $class = new \ReflectionClass($definition['class']);
    $properties = array();
    foreach ($class->getProperties() as $property) {
      if ($property->getDeclaringClass()->name == $definition['class']) {
        $properties[$property->name] = $property;
        $documentation = $property->getDocComment();
        $matches = array();
        if (preg_match('(@var+[\sa-zA-Z]*)', $documentation, $matches)) {
          list( , $type) = explode(' ', array_shift($matches));
          $type = trim($type);
          $definition['schema']['fields'][$property->name] = $this->getSchemaDefaults($type);
          foreach ($this->getSchemaFieldProperties() as $schema_property) {
            $schema_doc = array();
            if (preg_match('(@' . $schema_property . '+[\sa-zA-Z0-9]*)', $documentation, $schema_doc)) {
              list( , $property_value) = explode('@' . $schema_property, array_shift($schema_doc));
              $property_value = trim($property_value);
              $definition['schema']['fields'][$property->name][$schema_property] = $property_value;
            }
          }
          $index = array();
          if (preg_match('(@index)', $documentation, $index)) {
            $definition['schema']['indexes'][$property->name] = array($property->name);
          }
        }
      }
    }
    $definition['schema']['primary key'] = array($definition['primary_key']);
  }

  protected function getSchemaDefaults($type) {
    $types = array(
      'string' => array(
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
        'default' => '',
      ),
      'array' => array(
        'type' => 'blob',
        'not null' => FALSE,
        'serialize' => TRUE,
      ),
      'serial' => array(
        'type' => 'serial',
        'unsigned' => TRUE,
        'not null' => TRUE,
      ),
      'int' => array(
        'type' => 'int',
        'not null' => TRUE,
        'default' => 0,
      )
    );
    if (isset($types[$type])) {
      return $types[$type];
    }
  }

  protected function getSchemaFieldProperties() {
    return array(
      'type',
      'size',
      'not null',
      'default',
      'length',
      'unsigned',
      'precision',
      'scale',
      'serialize'
    );
  }

}