<?php

namespace Drupal\rokka;

use Drupal\rokka\RokkaAdapter\RokkaStackInterface;

class RokkaStackController extends \EntityAPIController {

  /**
   * @param array $values
   * @return RokkaStackInterface
   */
  public function create(array $values = array()) {
    $values += array(
      'image_style' => NULL,
      'created' => NULL,
      'options' => NULL,
    );

    return parent::create($values);
  }

  /**
   * @param RokkaStackInterface $entity
   * @param \DatabaseTransaction|NULL $transaction
   * @throws \Exception
   * @return bool|int
   */
  public function save($entity, \DatabaseTransaction $transaction = NULL) {
    $ret = parent::save($entity, $transaction);
    return $ret;
  }
}
