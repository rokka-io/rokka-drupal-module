<?php

namespace Drupal\rokka\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Rokka stack entity.
 *
 * @ConfigEntityType(
 *   id = "rokka_stack",
 *   label = @Translation("Rokka stack"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\rokka\RokkaStackListBuilder",
 *     "form" = {
 *       "add" = "Drupal\rokka\Form\RokkaStackForm",
 *       "edit" = "Drupal\rokka\Form\RokkaStackForm",
 *       "delete" = "Drupal\rokka\Form\RokkaStackDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\rokka\RokkaStackHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "rokka_stack",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/rokka_stack/{rokka_stack}",
 *     "add-form" = "/admin/structure/rokka_stack/add",
 *     "edit-form" = "/admin/structure/rokka_stack/{rokka_stack}/edit",
 *     "delete-form" = "/admin/structure/rokka_stack/{rokka_stack}/delete",
 *     "collection" = "/admin/structure/rokka_stack"
 *   }
 * )
 */
class RokkaStack extends ConfigEntityBase implements RokkaStackInterface {

  /**
   * The Rokka stack ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Rokka stack label.
   *
   * @var string
   */
  protected $label;

}
