<?php
/**
 * @file
 * Contains SiteManager\Core\Controller\YamlStorageController.
 */

namespace SiteManager\Core\Controller;

use SiteManager\Core\ContextManager;
use SiteManager\Core\DataInterface;
use Symfony\Component\Yaml\Yaml;

class YamlStorageController implements StorageInterface {

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
   * @var \Directory
   */
  protected $directory;

  /**
   * @var \SiteManager\Core\ContextManager
   */
  protected $manager;

  /**
   * @param ContextManager $manager
   * @param \Directory $directory
   */
  public function __construct(ContextManager $manager, \Directory $directory) {
    $this->directory = $directory;
    $this->manager = $manager;
  }

  public function load($plugin_id, $id) {
    $file = $this->directory->path . '/' . "{$plugin_id}.$id" . '.yml';
    if (file_exists($file)) {
      $instance = $this->manager->createInstance($plugin_id);
      $this->manager->populateValues($instance, Yaml::parse($file));
      return $instance;
    }
  }

  public function loadMultiple($plugin_id, array $ids = array(), array $conditions = array()) {
    if (!file_exists($this->directory->path)) {
//      throw new StorageException($this->directory . '/ not found.');
    }
    $records = array();
    if (!$ids) {
      $extension = '.yml';
      $files = glob($this->directory->path . '/' . $plugin_id . '*' . $extension);
      $clean_name = function ($value) use ($extension) {
        return basename($value, $extension);
      };
      $ids = array_map($clean_name, $files);
    }
    foreach ($ids as $key => $id) {
      unset($ids[$key]);
      $components = explode('.', $id);
      if ($components[0] == $plugin_id) {
        array_shift($components);
      }
      $components = implode('.', $components);
      $record = $this->load($plugin_id, $components);
      if ($conditions) {
        foreach ($conditions as $key => $value) {
          if (property_exists($record, $key) && $record->$key == $value) {
            $records[$components] = $record;
          }
        }
      }
      else {
        $records[$components] = $record;
      }
    }
    return $records;
  }

  public function create($plugin_id, DataInterface $values) {
    return $this->write($plugin_id, $values);
  }

  public function update($plugin_id, $id, DataInterface $values) {
    return $this->write($plugin_id, $values);
  }

  public function delete($plugin_id, $id) {
    $file = $this->directory->path . '/' . "{$plugin_id}.$id" . '.yml';
    if (file_exists($file)) {
      unlink($file);
      return !file_exists($file) ? YamlStorageController::SAVED_DELETED : FALSE;
    }
  }

  public function deleteMultiple($plugin_id, array $ids = array()) {
    if (!$ids) {
      $extension = '.yml';
      $files = glob($this->directory->path . '/' . $plugin_id . '*' . $extension);
      $clean_name = function ($value) use ($extension) {
        return basename($value, $extension);
      };
      $ids = array_map($clean_name, $files);

    }
    foreach ($ids as $key => $id) {
      unset($ids[$key]);
      $components = explode('.', $id);
      if ($components[0] == $plugin_id) {
        array_shift($components);
      }
      $components = implode('.', $components);
      $ids[$components] = $this->delete($plugin_id, $components);
    }
    return $ids;
  }

  protected function write($plugin_id, DataInterface $object) {
    $yaml = Yaml::dump($object->all(), 4, 2);
    $file = $this->directory->path . '/' . "{$plugin_id}.{$object->getId()}" . '.yml';
    if (file_exists($file)) {
      $return = YamlStorageController::SAVED_UPDATED;
    }
    else {
      $return = YamlStorageController::SAVED_NEW;
    }
    return file_put_contents($file, $yaml) ? $return : FALSE;
  }

}