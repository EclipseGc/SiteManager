<?php
/**
 * Created by IntelliJ IDEA.
 * User: kris
 * Date: 7/17/13
 * Time: 6:25 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SiteManager\Core;

use Symfony\Component\HttpKernel\Controller\ControllerResolver as ControllerResolverBase;
use SiteManager\Core\RouteManager;
use Symfony\Component\HttpFoundation\Request;

class ControllerResolver extends ControllerResolverBase {

  protected $manager;

  /**
   * Constructor.
   *
   * @param RouteManager $manager
   *   The Route plugin manager.
   * @param LoggerInterface $logger
   *   A LoggerInterface instance
   */
  public function __construct(RouteManager $manager, LoggerInterface $logger = null) {
    $this->manager = $manager;
    parent::__construct($logger);
  }

  public function getController(Request $request) {
    if (!$route = $request->attributes->get('_route')) {
      return parent::getController($request);
    }

    $definition = $this->manager->getDefinition($route);
    if ($definition && strpos($request->attributes->get('_controller'), $definition['class']) === 0) {
      return array($this->manager->createInstance($route, array('request' => $request)), 'getResponse');
    }
    return parent::getController($request);
  }

}
