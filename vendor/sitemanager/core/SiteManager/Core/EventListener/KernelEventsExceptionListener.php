<?php

namespace SiteManager\Core\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelEventsExceptionListener implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return array(
      KernelEvents::EXCEPTION => array('onKernelException'),
    );
  }

  public function onKernelException(GetResponseForExceptionEvent $event) {
    $exception = $event->getException();
    $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
    $response = new Response($exception->getMessage(), $code);
    $event->setResponse($response);
  }

}
