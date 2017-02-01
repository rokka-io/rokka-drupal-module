<?php

namespace Drupal\rokka\StyleEffects;

use Drupal\rokka\ImageStyleHelper;
use Rokka\Client\Core\StackOperation;

class EffectImageCrop implements InterfaceEffectImage {

  public static function buildRokkaStackOperation($data) {
    $options = array(
      'height' => ImageStyleHelper::operationNormalizeSize($data['height']),
      'width'  => ImageStyleHelper::operationNormalizeSize($data['width']),
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
