<?php

namespace Drupal\rokka\StyleEffects;

use Rokka\Client\Core\StackOperation;

class EffectFocalPointCrop extends EffectImageCrop {

  public static function buildRokkaStackOperation($data) {
    $options = array(
      'height' => ''. static::normalizeSize($data['height']),
      'width'  => ''. static::normalizeSize($data['width']),
      'anchor' => 'auto',
    );
    return array(new StackOperation('crop', $options));
  }
}
