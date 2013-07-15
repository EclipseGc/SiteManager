<?php
/**
 * @file
 * Contains SiteManager\Core\Route\CreateSite.
 */

namespace SiteManager\Core\Route;

use Drupal\Core\Database\Database;
use SiteManager\Core\Annotation\Route;
use SiteManager\Core\RouteBase;
use Symfony\Component\Form\Forms;

/**
 * @Route(
 *   id = "site_create",
 *   path = "/site/add",
 *   context = {
 *     "site" = {
 *       "class" = "SiteManager\Core\Context\Site"
 *     }
 *   }
 * )
 */
class CreateSite extends RouteBase {
  protected $type = 'html';

  public function render() {
    $factory = Forms::createFormFactory();
    $builder = $factory->createNamedBuilder('site_create');
    $site = $this->getContextValue('site');
    $definition = $site->getPluginDefinition();
    foreach (array_keys($site->all()) as $property) {
      if ($property != $definition['primary_key']) {
        $builder->add($property, 'text');
      }
    }
    $form = $builder->getForm();
    return $this->engine->renderBlock($form->createView(), 'form_widget', array('form' => $form->createView()));
  }
}
