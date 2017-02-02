<?php

namespace Drupal\rokka\StyleEffects;

use Drupal\rokka\ImageStyleHelper;
use Rokka\Client\Core\StackOperation;

class EffectRokkaCrop implements InterfaceEffectImage {

  public static function buildRokkaStackOperation($data) {
    $crop_options = array(
      'height' => ImageStyleHelper::operationNormalizeSize($data['height']),
      'width'  => ImageStyleHelper::operationNormalizeSize($data['width']),
      'anchor' => $data['anchor'],
    );

    $composite_options = array_merge($crop_options, array(
      'mode' => 'foreground',
      'secondary_color' => ImageStyleHelper::operationNormalizeColor($data['background_color']),
      'secondary_opacity' => (int) $data['background_opacity'],
    ));

    return array(
      new StackOperation('crop', $crop_options),
      new StackOperation('composition', $composite_options),
    );
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
