<?php
/**
 * Created by IntelliJ IDEA.
 * User: kris
 * Date: 7/11/13
 * Time: 9:11 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SiteManager\Core\Tables;

use Drupal\Component\Plugin\PluginBase;
use SiteManager\Core\Annotation\Table;
use SiteManager\Core\TableSchemaInterface;
use SiteManager\Core\Service;


/**
 * @Table(
 *   id = "context",
 *   group = "Context Tables",
 *   derivative = "SiteManager\Core\Derivative\ContextTables"
 * )
 */
class Contexts extends PluginBase implements TableSchemaInterface {

  public function getSchema() {
    list($plugin_id, $context) = explode(':', $this->getPluginId());
    $contextManager = Service::get('plugin.manager.context');
    $definition = $contextManager->getDefinition($context);
    if (isset($definition['schema'])) {
      return array($context => $definition['schema']);
    }
    if (in_array('SiteManager\Core\TableSchemaInterface', class_implements($definition['class']))) {
      $context = $contextManager->createInstance($context);
      return $context->getSchema();
    }
    return array();
  }
}