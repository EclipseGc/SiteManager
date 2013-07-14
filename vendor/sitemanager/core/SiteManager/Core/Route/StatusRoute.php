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
    $output = '';
    $query = Database::getConnection()
      ->select('site', 's')
      ->fields('s', array('sid', 'url', 'status'))
      ->condition('s.status', 'disabled', '<>')
      ->execute();
    foreach ($query as $id => $row) {
      $output .= print_r($row, TRUE);
    }
    return $output;
  }
}
