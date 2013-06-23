<?php
/**
 * Contains SiteManager\Core\Route\StatusRoute.
 */

namespace SiteManager\Core\Route;

use SiteManager\Core\Annotation\Route;
use SiteManager\Core\RouteBase;
use SiteManager\Core\TableManager;

/**
 * @Route(
 *   id = "install",
 *   path = "/install"
 * )
 */
class Install extends RouteBase {
  protected $type = 'html';

  public function render() {
    $loader = sitemanager_autoloader();
    $tableManager = new TableManager($loader);
    $tableManager->installSchemas();
  }
}
