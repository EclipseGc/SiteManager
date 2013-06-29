<?php
/**
 * Created by IntelliJ IDEA.
 * User: kris
 * Date: 7/18/13
 * Time: 5:25 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SiteManager\Core;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class PluginRouterListener implements EventSubscriberInterface {

  protected $resolver;

  public function __construct(ControllerResolverInterface $resolver) {
    $this->resolver = $resolver;
  }

  public function onResponse(GetResponseEvent $event) {
    $request = $event->getRequest();
    if ($controller = $this->resolver->getController($request)) {
      $arguments = $this->resolver->getArguments($request, $controller);
      $event->setResponse(call_user_func_array($controller, $arguments));
    }
  }

  public static function getSubscribedEvents() {
    return array(
      'kernel.request' => 'onResponse',
    );
  }

}
