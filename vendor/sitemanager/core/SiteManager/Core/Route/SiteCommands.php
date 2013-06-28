<?php
/**
 * Contains SiteManager\Core\Route\SiteCommands.
 */

namespace SiteManager\Core\Route;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Route;
use SiteManager\Core\RouteBase;
use SiteManager\Core\Service;

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
  protected $type = 'xhtml';

  public function render() {
    $output = '';
    $site = $this->getContextValue('site');
    $output .= print_r($site->all(), TRUE);
    // Updating
    $site->url = 'bigfattest.com';
    $test = $site->save();
    $output .= print_r($site->all(), TRUE);

    $contextManager = Service::get('plugin.manager.context');
    $newsite = $contextManager->createInstance('site');
    $newsite->url = 'mynewsite.com';
    $newsite->status = 'inactive';
    $newsite->save();
    $output .= print_r($newsite->all(), TRUE);
    $storage = $contextManager->getStorage('route');
    $route = $storage->loadMultiple(array(), array('name' => 'site_commands'));
    $route = array_pop($route);
    $output .= print_r($route->all(), TRUE);
    return $output;
  }
}
