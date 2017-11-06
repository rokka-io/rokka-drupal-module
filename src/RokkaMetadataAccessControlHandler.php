<?php

namespace Drupal\rokka;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Rokka Metadata entity.
 *
 * @see \Drupal\rokka\Entity\RokkaMetadata.
 */
class RokkaMetadataAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\rokka\Entity\RokkaMetadataInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished rokka metadata entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published rokka metadata entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit rokka metadata entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete rokka metadata entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add rokka metadata entities');
  }

}
