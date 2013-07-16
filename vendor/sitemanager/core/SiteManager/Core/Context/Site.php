<?php
/**
 * @file
 * Contains SiteManager\Core\Context\Site.
 */

namespace SiteManager\Core\Context;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Context;
use SiteManager\Core\UpcastableDataBase;

/**
 * @Context(
 *   id = "site",
 *   primary_key = "sid",
 *   storage = "sql.controller"
 * )
 */
class Site extends UpcastableDataBase {

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

}
