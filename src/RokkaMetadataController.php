<?php

namespace Drupal\rokka;

class RokkaMetadataController extends \EntityAPIController {

  /**
   * @param array $values
   * @return RokkaMetadata
   */
  public function create(array $values = array()) {
    $values += array(
      'uri' => NULL,
      'hash' => NULL,
      'filesize' => NULL,
      'created' => NULL,
      'fid' => NULL,
    );

    return parent::create($values);
  }

  /**
   * @param RokkaMetadata $entity
   * @param \DatabaseTransaction|NULL $transaction
   * @throws \Exception
   * @return bool|int
   */
  public function save($entity, \DatabaseTransaction $transaction = NULL) {
    $ret = parent::save($entity, $transaction);
    return $ret;
  }
}
