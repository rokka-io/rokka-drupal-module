<?php

namespace Drupal\rokka\StyleEffects;

use Drupal\rokka\ImageStyleHelper;
use Rokka\Client\Core\StackOperation;

/**
 *
 */
class EffectImageEffectsSetCanvas implements InterfaceEffectImage {

  /**
   *
   */
  public static function buildRokkaStackOperation($data) {
    $options = [
      'mode' => 'foreground',
      'width' => ImageStyleHelper::operationNormalizeSize($data['exact']['width']),
      'height' => ImageStyleHelper::operationNormalizeSize($data['exact']['height']),
      'secondary_color' => substr(str_replace('#', '', $data['canvas_color']), 0, 6),
    ];
    return [new StackOperation('composition', $options)];
  }

}
