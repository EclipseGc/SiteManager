<?php
/**
 * Contains SiteManager\Core\Route\SiteCommands.
 */

namespace SiteManager\Core\Route;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Route;
use SiteManager\Core\RouteBase;
// Remove these after you're done testing.
use SiteManager\Core\TableManager;
use SiteManager\Core\ContextManager;

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
    print '<pre>' . print_r($site->all(), TRUE) . '</pre>';
    // Updating
    $site->url = 'test.com';
    $test = $site->save();
    print '<pre>' . print_r($site->all(), TRUE) . '</pre>';
    // Creating: This requires a bunch of bs code we shouldn't have to do because this class isn't meant for this.
    $loader = sitemanager_autoloader();
    $tableManager = new TableManager($loader);
    $contextManager = new ContextManager($loader, $tableManager);
    $newsite = $contextManager->createInstance('site');
    $newsite->url = 'mynewsite.com';
    $newsite->status = 'inactive';
    $newsite->save();
    print '<pre>' . print_r($newsite->all(), TRUE) . '</pre>';
  }
}
