<?php

namespace Drupal\rokka\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\ConfigEntityType;
use Rokka\Client\Core\StackOperation;

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
 *     "id"
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
   * @var StackOperation[]
   */
  protected $stackOperations;

  /**
   * The Rokka stack uuid.
   *
   * @var string
   */
  protected $uuid;


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
   * @param array $values
   * @return array
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

  protected static function dotStackOptions(array $values): array {
    foreach ($values as $key => $value) {
      if (strpos($key, "__") !== FALSE) {
        $values[str_replace("__", ".", $key)] = $value;
        unset($values[$key]);
      }
    }
    return $values;
  }

  public function setStackOptions($options) {
    $this->stackOptions = self::deDotStackOptions($options);
  }

  /**
   * @return array
   */
  public function getStackOptions(): array {
    return self::dotStackOptions($this->stackOptions);
  }

  /**
   * @return string
   */
  public function getOrganization(): string {
    return $this->organization;
  }

  /**
   * @param string $organization
   */
  public function setOrganization(string $organization) {
    $this->organization = $organization;
  }
}
