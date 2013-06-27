<?php
/**
 * @file Contains SiteManager\Core\Derivative\ContextTables.
 */

namespace SiteManager\Core\Derivative;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use SiteManager\Core\Container;

class ContextTables extends DerivativeBase {

  public function getDerivativeDefinitions(array $base_plugin_definition) {
    $contextManager = Container::get('plugin.manager.context');

    foreach ($contextManager->getDefinitions() as $plugin_id => $definition) {
      $this->derivatives[$plugin_id] = $base_plugin_definition;
      $this->derivatives[$plugin_id]['tables'] = array($definition['base_table']);
    }
    return $this->derivatives;
  }
}