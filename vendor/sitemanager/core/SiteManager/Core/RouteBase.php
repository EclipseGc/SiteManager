<?php
/**
 * @file Contains SiteManager\Core\RouteBase;
 */

namespace SiteManager\Core;

use Drupal\Component\Plugin\ContextAwarePluginBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormRendererInterface;

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
   * The twig environment to run templating through.
   *
   * @var Twig_Environment
   */
  protected $environment;

  /**
   * @var \Symfony\Component\Form\FormRendererInterface
   */
  protected $engine;

  /**
   * Defines the type of route.
   *
   * @var string
   */
  protected $type;

  /**
   * Provides the Content-type response for this route.
   *
   * @return string
   */
  public function getType() {
    $content_types = $this->getContentType();
    return isset($content_types[$this->type]) ?: $content_types['html'];
  }

  /**
   * A setter for the current request object.
   *
   * @param Request $request
   */
  public function setRequest(Request $request) {
    $this->request = $request;
  }

  public function setTwigEnvironment(\Twig_Environment $environment) {
    $this->environment = $environment;
  }

  public function setEngine(FormRendererInterface $engine) {
    $this->engine = $engine;
  }

  /**
   * Render the output of the route.
   *
   * @return string
   */
  abstract public function render();

  /**
   * A generic method for setting sane defaults on the returned response.
   *
   * @return Symfony\Component\HttpFoundation\Response|Response
   */
  public function getResponse() {
    $response = new Response();
    $response->headers->set('Content-Type', $this->getType());
    switch ($this->type) {
      case 'html':
        $response->setContent($this->environment->render('html.html.twig', array('page' => $this->render(), 'head_title' => 'This is a test')));
        break;
    }
    return $response;
  }

  /**
   * A list of supported Content-Type headers.
   *
   * @return array
   */
  protected function getContentType() {
    return array(
      'atom' => 'application/atom+xml',
      'json' => 'application/json',
      'binary' => 'application/octet-stream',
      'ogg' => 'application/ogg',
      'pdf' => 'application/pdf',
      'postscript' => 'application/postscript',
      'rdf' => 'application/rdf+xml',
      'rss' => 'application/rss+xml',
      'soap' => 'application/soap+xml',
      'xhtml' => 'application/xhtml+xml',
      'xml' => 'application/xml',
      'dtd' => 'application/xml-dtd',
      'xop' => 'application/xop+xml',
      'zip' => 'application/zip',
      'gzip' => 'application/gzip',
      'svg' => 'image/svg+xml',
      'form' => 'multipart/form-data',
      'css' => 'text/css',
      'csv' => 'text/csv',
      'html' => 'text/html',
      'plain' => 'text/plain',
    );
  }
}
