<?php
/**
 * @file
 * Contains SiteManager\Core\PluginRouterListener.
 */

namespace SiteManager\Core;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use SiteManager\Core\RouteManager;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class PluginRouterListener implements EventSubscriberInterface, ControllerResolverInterface {

  /**
   * @var \SiteManager\Core\RouteManager
   */
  protected $manager;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

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
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function getController(Request $request) {
    // If we don't have a _route, this definitely isn't a route plugin. BAIL!
    if (!$route = $request->attributes->get('_route')) {
      return;
    }
    $definition = $this->manager->getDefinition($route);
    if ($definition && strpos($request->attributes->get('_controller'), $definition['class']) === 0) {
      return array($this->manager->createInstance($route, array('request' => $request)), 'getResponse');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getArguments(Request $request, $controller) {
    return array();
  }

  public function onKernelRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    // The attempts to stay very similar to Symfony's RouterListener and builds
    // up the Request object in much the same way.
    try {
      $parameters = $this->manager->matchRoute($request);
      $request->attributes->add($parameters);
      unset($parameters['_route']);
      unset($parameters['_controller']);
      $request->attributes->set('_route_params', $parameters);
    } catch (ResourceNotFoundException $e) {
      $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

      throw new NotFoundHttpException($message, $e);
    } catch (MethodNotAllowedException $e) {
      $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), strtoupper(implode(', ', $e->getAllowedMethods())));

      throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
    }
    if ($controller = $this->getController($request)) {
      $arguments = $this->getArguments($request, $controller);
      $event->setResponse(call_user_func_array($controller, $arguments));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return array(
      KernelEvents::REQUEST => array(array('onKernelRequest', 33)),
    );
  }

}
