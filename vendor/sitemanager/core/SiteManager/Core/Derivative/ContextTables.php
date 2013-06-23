<?php
/**
 * @file Contains SiteManager\Core\Derivative\ContextTables.
 */

namespace SiteManager\Core\Derivative;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use SiteManager\Core\TableManager;
use SiteManager\Core\ContextManager;

class ContextTables extends DerivativeBase {

  public function getDerivativeDefinitions(array $base_plugin_definition) {
    $loader = sitemanager_autoloader();
    $tableManager = new TableManager($loader);
    $contextManager = new ContextManager($loader, $tableManager);

    foreach ($contextManager->getDefinitions() as $plugin_id => $definition) {
      $this->derivatives[$plugin_id] = $base_plugin_definition;
      $this->derivatives[$plugin_id]['tables'] = array($definition['base_table']);
    }
    return $this->derivatives;
  }
}