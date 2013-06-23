<?php
/**
 * Contains SiteManager\Core\Route\SiteCommands.
 */

namespace SiteManager\Core\Route;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Route;
use SiteManager\Core\RouteBase;

/**
 * @Route(
 *   id = "site_commands",
 *   path = "/site/{site}",
 *   context = {
 *     "site" = {
 *       "class" = "SiteManager\Core\Context\Site"
 *     }
 *   }
 * )
 */
class SiteCommands extends RouteBase {
  protected $type = 'html';

  public function render() {
    $site = $this->getContextValue('site');
    print '<pre>' . print_r($site, TRUE) . '</pre>';
  }
}
