<?php
/**
 * @file Contains SiteManager\Core\Site.
 */

namespace SiteManager\Core\Context;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Context;
use SiteManager\Core\TableSchemaInterface;
use SiteManager\Core\UpcastableDataBase;

/**
 * @Context(
 *   id = "route",
 *   base_table = "route",
 *   primary_key = "name",
 *   storage = "SiteManager\Core\Controller\SqlStorageController"
 * )
 */
class Route extends UpcastableDataBase implements TableSchemaInterface {

  /**
   * The route name.
   *
   * @var string
   */
  protected $name;

  /**
   * The path pattern to match.
   *
   * @var string
   */
  protected $path = '/';

  /**
   * An array of default parameter values.
   *
   * @var array
   */
  protected $defaults = array();

  /**
   * An array of requirements for parameters (regexes).
   *
   * @var array
   */
  protected $requirements = array();

  /**
   * An array of options.
   *
   * @var array
   */
  protected $options = array();

  /**
   * The host pattern to match.
   *
   * @var string
   */
  protected $host = '';

  /**
   * A required URI scheme or an array of restricted schemes.
   *
   * @var array
   */
  protected $schemes = array();

  /**
   * A required HTTP method or an array of restricted methods.
   *
   * @var array
   */
  protected $methods = array();

  public function getSchema() {
    $schema = array();
    $schema['route'] = array(
      'description' => 'Maps paths to various callbacks (access, page and title)',
      'fields' => array(
        'name' => array(
          'description' => 'Primary Key: the name of the route.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'path' => array(
          'description' => 'The path pattern this entry describes',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'defaults' => array(
          'description' => 'An array of default parameter values.',
          'type' => 'blob',
          'not null' => FALSE,
        ),
        'requirements' => array(
          'description' => 'An array of requirements for parameters (regexes).',
          'type' => 'blob',
          'not null' => FALSE,
        ),
        'options' => array(
          'description' => 'An array of options.',
          'type' => 'blob',
          'not null' => FALSE,
        ),
        'host' => array(
          'description' => 'The host pattern to match.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'schemes' => array(
          'description' => 'A required URI scheme or an array of restricted schemes.',
          'type' => 'blob',
          'not null' => FALSE,
        ),
        'methods' => array(
          'description' => 'A required HTTP method or an array of restricted methods.',
          'type' => 'blob',
          'not null' => FALSE,
        ),
        'path_root' => array(
          'description' => 'The root of this path with any variable parameters stripped out.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'class' => array(
          'description' => 'The plugin class.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'context' => array(
          'description' => 'The plugin contexts, if any.',
          'type' => 'blob',
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'path_root' => array('path_root'),
        'path' => array('path'),
      ),
      'primary key' => array('name'),
    );
    return $schema;
  }
}
