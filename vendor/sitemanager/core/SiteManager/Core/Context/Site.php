<?php
/**
 * @file Contains SiteManager\Core\Site.
 */

namespace SiteManager\Core\Context;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Context;
use SiteManager\Core\UpcastableDataBase;

/**
 * @Context(
 *   id = "site",
 *   base_table = "sites",
 *   primary_key = "sid"
 * )
 */
class Site extends UpcastableDataBase {

  protected $sid;

  protected $url;

  protected $status;

}
