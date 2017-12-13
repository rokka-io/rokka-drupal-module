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
 *     "uuid" = "uuid",
 *     "label" = "label"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/rokka_stack/{rokka_stack}",
 *     "add-form" = "/admin/structure/rokka_stack/add",
 *     "edit-form" = "/admin/structure/rokka_stack/{rokka_stack}/edit",
 *     "delete-form" = "/admin/structure/rokka_stack/{rokka_stack}/delete",
 *     "collection" = "/admin/structure/rokka_stack"
 *   },
 *   config_export = {
 *     "organization",
 *     "stackOptions",
 *     "id",
 *     "label"
 *   }
 * )
 */
class RokkaStack extends ConfigEntityBase implements RokkaStackInterface {

  /**
   * The Rokka stack name.
   *
   * @var string
   */
  protected $id;

  /**
   * The Rokka stack $organization.
   *
   * @var string
   */
  protected $organization;

  /**
   * The Rokka stack options.
   *
   * @var array
   */
  protected $stackOptions;

  /**
   * The Rokka stack operations.
   *
   * @var \Rokka\Client\Core\StackOperation[]
   */
  protected $stackOperations;

  /**
   * The Rokka stack uuid.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The label of this stack.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public static function create(array $values = []) {
    if (isset($values['stackOptions'])) {
      $values['stackOptions'] = self::deDotStackOptions($values['stackOptions']);
    }
    return parent::create($values);
  }

  /**
   * Replace . with __ in stackOptions.
   *
   * @param array $values
   *   The to be replaces values.
   *
   * @return array
   *   The replaced array
   */
  protected static function deDotStackOptions(array $values): array {
    foreach ($values as $key => $value) {
      if (strpos($key, ".") !== FALSE) {
        $values[str_replace(".", "__", $key)] = $value;
        unset($values[$key]);
      }
    }
    return $values;
  }

  /**
   * Replace __ with . in stackOptions.
   *
   * @param array $values
   *   The to be replaces values.
   *
   * @return array
   *   The replaced array
   */
  protected static function dotStackOptions(array $values): array {
    foreach ($values as $key => $value) {
      if (strpos($key, "__") !== FALSE) {
        $values[str_replace("__", ".", $key)] = $value;
        unset($values[$key]);
      }
    }
    return $values;
  }

  /**
   * Set Stack options.
   *
   * @param array $options
   *   The options.
   */
  public function setStackOptions(array $options) {
    $this->stackOptions = self::deDotStackOptions($options);
  }

  /**
   * Get stack options.
   *
   * @return array
   *   The options.
   */
  public function getStackOptions(): array {
    return self::dotStackOptions($this->stackOptions);
  }

  /**
   * Get organization.
   *
   * @return string
   *   The organization
   */
  public function getOrganization(): string {
    return $this->organization;
  }

  /**
   * Set organization.
   *
   * @param string $organization
   *   The organization.
   */
  public function setOrganization(string $organization) {
    $this->organization = $organization;
  }

}
