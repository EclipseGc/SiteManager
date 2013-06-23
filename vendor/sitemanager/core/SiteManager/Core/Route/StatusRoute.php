<?php
/**
 * Contains SiteManager\Core\Route\StatusRoute.
 */

namespace SiteManager\Core\Route;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Route;
use SiteManager\Core\RouteBase;

/**
 * @Route(
 *   id = "status_route"
 * )
 */
class StatusRoute extends RouteBase {
  protected $type = 'html';

  public function render() {
    $query = Database::getConnection()
      ->select('sites', 's')
      ->fields('s', array('sid', 'url', 'status'))
      ->condition('s.status', 'disabled', '<>')
      ->execute();
    foreach ($query as $id => $row) {
      print print_r($row, TRUE) . '<br />';
    }

  }
}
