<?php
/**
 * @file Contains SiteManager\Core\Annotation\Table.
 */

namespace SiteManager\Core\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * An annotation for table schema plugins.
 *
 * @Annotation
 */
class Table extends Plugin {

  /**
   * The group to which these tables belong.
   *
   * @var string
   */
  protected $group;

  /**
   * The tables defined by this plugin.
   *
   * @var array
   */
  protected $tables;

}