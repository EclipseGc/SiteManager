<?php
/**
 * @file Contains SiteManager\Core\RouteBase;
 */

namespace SiteManager\Core;

use Drupal\Component\Plugin\ContextAwarePluginBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines an abstract base class for route plugins.
 */
abstract class RouteBase extends ContextAwarePluginBase implements RouteInterface {

  /**
   * The current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request;
   */
  protected $request;

  /**
   * Defines the type of route.
   *
   * @var string
   */
  protected $type;

  public function getType() {
    return $this->type;
  }

  public function setRequest(Request $request) {
    $this->request = $request;
  }
}