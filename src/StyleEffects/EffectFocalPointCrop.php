<?php

namespace Drupal\rokka\StyleEffects;

use Drupal\rokka\ImageStyleHelper;
use Rokka\Client\Core\StackOperation;

class EffectFocalPointCrop implements InterfaceEffectImage {

  public static function buildRokkaStackOperation($data) {
    $options = array(
      'height' => ImageStyleHelper::operationNormalizeSize($data['height']),
      'width' => ImageStyleHelper::operationNormalizeSize($data['width']),
      'anchor' => 'auto',
    );
    return array(new StackOperation('crop', $options));
  }
}
