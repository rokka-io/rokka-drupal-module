<?php

namespace Drupal\rokka;

use Drupal\rokka\StyleEffects\InterfaceEffectImage;
use Rokka\Client\Core\StackOperation;

class ImageStyleHelper {

  /**
   * @param array $effects
   * @return StackOperation[]
   */
  public static function buildStackOperationCollection($effects) {
    if (empty($effects)) {
      $effects = array(array(
        'name' => 'noop',
        'data' => NULL,
      ));
    }

    $operations = array();
    $currentId = 0;
    foreach($effects as $effect) {
      $ops = static::buildStackOperation($effect);
      if (!empty($ops)) {
        foreach($ops as $op) {
          $operations[$currentId++] = $op;
        }
      }
    }

    if (empty($operations))
      return NULL;

    ksort($operations);
    return $operations;
  }

  /**
   * @param array $effect
   * @return StackOperation[]
   */
  public static function buildStackOperation(array $effect) {
    $name = $effect['name'];
    $className = 'Drupal\rokka\StyleEffects\Effect' . static::camelCase($name, TRUE);

    $ret = array();
    if (class_exists($className) && in_array('Drupal\rokka\StyleEffects\InterfaceEffectImage', class_implements($className))) {
      /** @var InterfaceEffectImage $className */
      $ret = $className::buildRokkaStackOperation($effect['data']);
    }
    else {
      watchdog('rokka', 'Can not convert effect "%effect" to Rokka.io StackOperation: "%class" Class missing!', array(
        '%effect' => $name,
        '%class'  => $className,
      ));
    }

    return $ret;
  }

  /**
   * @param string $str
   * @return string
   */
  public static function camelCase($str, $classCase = FALSE) {
    // non-alpha and non-numeric characters become spaces
    $str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
    $str = trim($str);
    // uppercase the first character of each word
    $str = ucwords($str);
    $str = str_replace(' ', '', $str);
    if (!$classCase) {
      $str = lcfirst($str);
    }
    return $str;
  }

}
