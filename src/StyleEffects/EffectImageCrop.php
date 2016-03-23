<?php

namespace Drupal\rokka\StyleEffects;

use Rokka\Client\Core\StackOperation;

class EffectImageCrop implements InterfaceEffectImage {

  public static function buildRokkaStackOperation($data) {
    $options = array(
      'height' => ''. static::normalizeSize($data['height']),
      'width'  => ''. static::normalizeSize($data['width']),
      'anchor' => $data['anchor'],
    );
    return array(new StackOperation('crop', $options));
  }

  /**
   * @param $value
   * @return mixed
   */
  protected static function normalizeSize($value) {
    $value = $value ? $value : PHP_INT_MAX;
    return min(10000, max(1, $value));
  }
}
