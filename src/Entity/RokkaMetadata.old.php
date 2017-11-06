<?php
/**
 * Defines the Rokka Metadata entity.
 *
 * @RokkaMetadataEntityType(
 *   id = "rokka_metadata",
 *   label = @Translation("Rokka Metadata"),
 *   base_table = "rokka_metadata",
 *   entity_keys = {
 *     "hash" = "hash",
 *     "uri" = "uri",
 *     "filesize" = "filesize",
 *   },
 * )
 */

namespace Drupal\rokka\Entity;

use Drupal\rokka\RokkaAdapter\SourceImageMetadata;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\rokka\RokkaMetaDataInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

class RokkaMetadata extends ContentEntityBase implements RokkaMetaDataInterface {

  public $hash;

  public $created;

  public $filesize;

  public $uri;

  public $fid;

  /**
   * @inheritDoc
   */
  public function getChangedTimeAcrossTranslations() {
    $changed = $this->getUntranslated()->getChangedTime();
    foreach ($this->getTranslationLanguages(FALSE) as $language)    {
      $translation_changed = $this->getTranslation($language->getId())->getChangedTime();
      $changed = max($translation_changed, $changed);
    }
    return $changed;
  }

  /**
   * @inheritDoc
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * @inheritDoc
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * @inheritDoc
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  public function __construct($values = []) {
    parent::__construct($values, 'rokka_metadata');
  }

  /**
   * @return string
   */
  public function getHash() {
    return $this->get('hash')->value;
  }

  /**
   * @return int
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setChangedTime($timestamp) {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * @return int
   */
  public function getFilesize() {
    return $this->get('filesize')->value;
  }

  /**
   * @return string
   */
  public function getUri() {
    return $this->get('uri')->value;
  }

  // @ TODO do we need this???
  /**
   * @return int
   */
  public function getFid() {
    return $this->get('fid')->value;
  }


  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Name field for the contact.
    // We set display options for the view as well as the form.
    // Users with correct privileges can change the view and edit configuration.

    $fields['hash'] = BaseFieldDefinition::create('varchar')
      ->setLabel(t('Hash'))
      ->setDescription(t('The Rokka.io hash (SHA1, 40 chars) of the file.'))
      ->setSettings([
        'max_length' => 40,
        'not null'   => TRUE,
      ]);

    $fields['uri'] = BaseFieldDefinition::create('varchar')
      ->setLabel(t('Uri'))
      ->setDescription(t('The original file URI.'))
      ->setSettings([
        'default_value' => '',
        'max_length'    => 255,
        'not null'      => TRUE,
      ]);

    $fields['filesize'] = BaseFieldDefinition::create('int')
      ->setLabel(t('File size'))
      ->setDescription(t('The original file size.'))
      ->setSettings([
        'size'        => 'big',
        'not null'    => TRUE,
        'unsigned'    => TRUE,
      ]);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Contact entity.'));
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
