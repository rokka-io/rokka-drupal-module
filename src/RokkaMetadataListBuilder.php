<?php

namespace Drupal\rokka;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Rokka Metadata entities.
 *
 * @ingroup rokka
 */
class RokkaMetadataListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Rokka Metadata ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\rokka\Entity\RokkaMetadata */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.rokka_metadata.edit_form',
      ['rokka_metadata' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
