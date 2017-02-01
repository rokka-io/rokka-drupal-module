<?php

namespace Drupal\rokka\StyleEffects;

use Drupal\rokka\ImageStyleHelper;
use Rokka\Client\Core\StackOperation;

class EffectImageScaleAndCrop implements InterfaceEffectImage {

  public static function buildRokkaStackOperation($data) {
    $options = array(
      'height' => ImageStyleHelper::operationNormalizeSize($data['height']),
      'width'  => ImageStyleHelper::operationNormalizeSize($data['width']),
    );

    return array(
      new StackOperation('resize', array_merge($options, array('mode' => 'fill'))),
      new StackOperation('crop', $options)
    );
  }
}
