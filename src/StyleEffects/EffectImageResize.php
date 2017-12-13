<?php

namespace Drupal\rokka\StyleEffects;

use Drupal\rokka\ImageStyleHelper;
use Rokka\Client\Core\StackOperation;

/**
 *
 */
class EffectImageResize implements InterfaceEffectImage {

  /**
   *
   */
  public static function buildRokkaStackOperation($data) {

    $options = [
      'upscale' => boolval($data['upscale']),
      'height' => ImageStyleHelper::operationNormalizeSize($data['height']),
      'width' => ImageStyleHelper::operationNormalizeSize($data['width']),
    ];
    return [new StackOperation('resize', $options)];
  }

}
