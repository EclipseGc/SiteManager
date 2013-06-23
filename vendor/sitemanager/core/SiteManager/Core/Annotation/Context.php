<?php
/**
 * @file Contains SiteManager\Core\Annotation\Context.
 */

namespace SiteManager\Core\Annotation;

use Drupal\Component\Annotation\Plugin;

  /**
   * Defines a SiteManager Context.
   *
   * @Annotation
   */
class Context extends Plugin {

  /**
   * The base table for storage of this context.
   *
   * @var string
   */
  protected $base_table;

  /**
   * The primary key of the base table.
   *
   * @var string
   */
  protected $primary_key;

  /**
   * The storage controller for the context.
   *
   * @var string
   */
  protected $storage = '';

}
