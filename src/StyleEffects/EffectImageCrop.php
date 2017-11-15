<?php

namespace Drupal\rokka\StyleEffects;

use Drupal\rokka\ImageStyleHelper;
use Rokka\Client\Core\StackOperation;

class EffectImageCrop extends EffectRokkaCrop {

  public static function buildRokkaStackOperation($data) {
    $data = array_merge($data, array(
      'background_color' => '#000000',
      'background_opacity' => 100,
    ));

    return parent::buildRokkaStackOperation($data);
  }
}
