<?php
/**
 * @file
 * Contains SiteManager\Core\Context\Route.
 */

namespace SiteManager\Core\Context;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Context;
use SiteManager\Core\UpcastableDataBase;

/**
 * @Context(
 *   id = "route",
 *   primary_key = "name",
 *   storage = "sql.controller"
 * )
 */
class Route extends UpcastableDataBase {

  /**
   * The route name.
   *
   * @var string
   */
  protected $name;

  /**
   * The path pattern to match.
   *
   * @var string
   * @index
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

  /**
   * The portion of this path preceding any arguments.
   *
   * @var string
   * @index
   */
  protected $path_root;

  /**
   * The class of this route plugins.
   *
   * @var string
   */
  protected $class;

  /**
   * Any contextual settings for this route.
   *
   * @var array
   */
  protected $context = array();

}
