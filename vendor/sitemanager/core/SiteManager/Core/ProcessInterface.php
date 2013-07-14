<?php
/**
 * @file
 * Contains SiteManager\Core\ProcessInterface.
 */

namespace SiteManager\Core;

/**
 * A simple interface for processing plugin definitions.
 */
interface ProcessInterface {

  /**
   * Process plugin definitions.
   *
   * @param $definition
   *   The plugin definition to process. Passed by reference.
   * @param $plugin_id
   *   The plugin id of the definition that is being processed.
   *
   * @return NULL
   */
  public function processDefinition(&$definition, $plugin_id);

}