<?php
/**
 * @file
 * Contains SiteManager\Core\Context\Site.
 */

namespace SiteManager\Core\Context;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Context;
use SiteManager\Core\TableSchemaInterface;
use SiteManager\Core\UpcastableDataBase;

/**
 * @Context(
 *   id = "site",
 *   primary_key = "sid",
 *   storage = "sql.controller"
 * )
 */
class Site extends UpcastableDataBase implements TableSchemaInterface {

  /**
   * @var serial
   */
  protected $sid;

  /**
   * @var string
   * @index
   */
  protected $url;

  /**
   * @var string
   */
  protected $status;

  public function getSchema() {
    $schema = array();
    $schema['site'] = array(
      'description' => 'The site context base table.',
      'fields' => array(
        'sid' => array(
          'description' => 'The primary identifier for a site.',
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'url' => array(
          'description' => 'The url of this site.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'status' => array(
          'description' => 'The status of this site.',
          'type' => 'varchar',
          'length' => 128,
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'url' => array('url')
      ),
      'primary key' => array('sid'),
    );
    return $schema;
  }
}
