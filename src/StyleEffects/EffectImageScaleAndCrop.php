<?php

namespace Drupal\rokka\StyleEffects;

use Rokka\Client\Core\StackOperation;

class EffectImageScaleAndCrop extends EffectImageCrop {

  public static function buildRokkaStackOperation($data) {
    $options = array(
      'height' => ''. static::normalizeSize($data['height']),
      'width'  => ''. static::normalizeSize($data['width']),
    );
    return array(
      new StackOperation('resize', array_merge($options, array('mode' => 'fill'))),
      new StackOperation('crop', $options)
    );
  }
}
