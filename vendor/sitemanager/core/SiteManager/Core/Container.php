<?php
/**
 * Created by IntelliJ IDEA.
 * User: kris
 * Date: 7/17/13
 * Time: 4:41 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SiteManager\Core;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Container {

  static protected $container;

  static function setContainer(ContainerInterface $container) {
    static::$container = $container;
  }

  static function get($service_id) {
    return static::$container->get($service_id);
  }
}