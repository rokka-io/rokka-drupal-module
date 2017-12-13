<?php

namespace Drupal\rokka\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for content_entity_example_contact entity.
 *
 * @ingroup content_entity_example
 */
class RokkaMetadataListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['description'] = [
      '#markup' => $this->t('Content Entity Example implements a Contacts model. These contacts are fieldable entities. You can manage the fields on the <a href="@adminlink">Contacts admin page</a>.', [
        '@adminlink' => \Drupal::urlGenerator()
          ->generateFromRoute('rokka.rokka_metadata_settings'),
      ]),
    ];

    $build += parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the contact list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['hash'] = $this->t('Hash');
    $header['filesize'] = $this->t('File size');
    $header['uri'] = $this->t('Uri');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\rokka\Entity\RokkaMetadata */
    $row['id'] = $entity->id();
    $row['hash'] = $entity->getHash();
    $row['filesize'] = $entity->getFilesize();
    $row['uri'] = $entity->getUri();
    return $row + parent::buildRow($entity);
  }

}
