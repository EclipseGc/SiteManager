<?php
/**
 * @file Contains SiteManager\Core\Annotation\Route;
 */

namespace SiteManager\Core\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a SiteManager Route.
 *
 * @Annotation
 */
class Route extends Plugin {

  /**
   * The path pattern to match.
   *
   * @var string
   */
  protected $path = '/';

  /**
   * An array of default parameter values.
   *
   * @var array
   */
  protected $defaults = array();

  /**
   * An array of requirements for parameters (regexes).
   *
   * @var array
   */
  protected $requirements = array();

  /**
   * An array of options.
   *
   * @var array
   */
  protected $options = array();

  /**
   * The host pattern to match.
   *
   * @var string
   */
  protected $host = '';

  /**
   * A required URI scheme or an array of restricted schemes.
   *
   * @var array
   */
  protected $schemes = array();

  /**
   * A required HTTP method or an array of restricted methods.
   *
   * @var array
   */
  protected $methods = array();

}