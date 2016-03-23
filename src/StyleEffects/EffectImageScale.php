<?php

namespace Drupal\rokka\StyleEffects;

use Rokka\Client\Core\StackOperation;

class EffectImageScale extends EffectImageResize {

  public static function buildRokkaStackOperation($data) {
    $options = array(
      'height' => ''. static::normalizeSize($data['height']),
      'width'  => ''. static::normalizeSize($data['width']),
    );
    return array(new StackOperation('resize', $options));
  }
}
