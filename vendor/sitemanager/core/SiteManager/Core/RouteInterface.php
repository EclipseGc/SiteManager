<?php
/**
 * @file Contains SiteManager\Core\RouteInterface.
 */

namespace SiteManager\Core;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * An interface for Route plugins.
 */
interface RouteInterface extends ContextAwarePluginInterface {

  /**
   * Retrieves the type of route item.
   *
   * @return string form|html|xml|json|etc
   */
  public function getType();

  /**
   * A method for setting the current request.
   *
   * We don't want to specify a request context on every single route, so a
   * generic setter is used instead and the instantiation process in the
   * manager will call this to supply the current route.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  public function setRequest(Request $request);

  /**
   * Render the output of the route.
   *
   * @return mixed
   */
  public function render();

  /**
   * Generate a response object for this route.
   *
   * @return Symfony\Component\HttpFoundation\Response
   */
  public function getResponse();
}